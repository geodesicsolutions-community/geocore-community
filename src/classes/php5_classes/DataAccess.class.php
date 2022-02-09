<?php

/**
 * Used for license validation, database access, and other
 * core-level functionality.
 *
 * Formerly used for license validation, those parts have been gutted.
 *
 * @package System
 */

/**
 * we are storing the db names in a different file, to keep this file un-cluttered.
 */
require_once(CLASSES_DIR . PHP5_DIR . 'meta/DbTables.php');

/**
 * This is the main Database access object.
 *
 * This class provides database access, and core functionality used throughout
 * the entire software.
 *
 * Works directly with {@link geoSession} and {@link geoPC}, and requires (like
 * most geo classes) that the software is initialized using app_top.common.php.
 *
 * @package System
 */
class DataAccess
{
    const TYPE_STRING = 'string';
    const TYPE_STRING_TODB = 'toDB';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';

    const SELECT_BROWSE = 'browse';
    const SELECT_SEARCH = 'search';
    const SELECT_FEED = 'feed';


    /**
     * Set this to 1 to use ADODB's built-in debugging, to help optimize stuff.  Do NOT
     * ship with this turned on!
     *
     */
    const ADODB_DEBUG = 0;

    /**
     * Instance of this class.
     *
     * @internal
     */
    private static $instance;

    /**
     * The real db accessor, no files should access this directly!
     *
     * @internal
     */
    private $db;

    /**
     * The performance object.  Only used if appropriate sections in init()
     *  and showStats() are un-commented.  The software should ship with
     *  those areas commented, as it should only ever need to be used internally.
     *
     * @internal
     */
    private $perf;

    /**
     * Execute time
     * @internal
     */
    private $executeTime;
    /**
     * Number of executions
     * @internal
     */
    private $numExecutes;
    /**
     * Saved result sets
     * @internal
     */
    private $result_sets;
    /**
     * The configuration data
     * @internal
     */
    private $configuration_data;
    /**
     * Array of errors
     * @internal
     */
    private $errors;
    /**
     * Array of sql queries
     * @internal
     */
    private $queries;

    /**
     * Instance of {@link geoTables}
     *
     * @var geoTables
     */
    public $geoTables;

    /**
     * var to hold info about the database connection settings.
     *
     * @internal
     */
    private $db_info;

    /**
     * Gets an instance of the data access class, to keep from creating multiple instances when
     * we only need one.
     *
     * @return DataAccess
     */
    public static function getInstance()
    {
        if (!(isset(self::$instance) && is_object(self::$instance))) {
            $c = __class__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    /**
     * Constructor for DataAccess.  DataAccess should never be called using new DataAccess();
     * Instead, use {@link DataAccess::getInstance()}
     */
    private function __construct()
    {
        $this->results = array();
        $this->current_result_index = 0;
        $this->geoTables = new geoTables();
    }

    /**
     * Prevents DataAccess from being cloned.
     *
     * @throws Exception
     */
    private function __clone()
    {
        //this should never be cloned
        throw new Exception('No cloning permitted!');
    }

    /**
     * Private function that initializes the database, this is called
     * internally the first time a database connection is needed.
     */
    private function init()
    {
        //create a connection to the database.
        if (!$this->IsConnected()) {
            //get the database settings.
            require(GEO_BASE_DIR . 'config.default.php');
            $this->db_info = [
                'db_host' => $db_host,
                'db_name' => $database
            ];
            try {
                $this->db = ADONewConnection($db_type);

                if (isset($persistent_connections) && $persistent_connections) {
                    if (!$this->db->PConnect($db_host, $db_username, $db_password, $database)) {
                        echo 'Could not connect to database.';
                        exit;
                    }
                } else {
                    if (!$this->db->Connect($db_host, $db_username, $db_password, $database)) {
                        echo "Could not connect to database.";
                        exit;
                    }
                }
            } catch (exception $e) {
                echo 'Could not connect to database.';
                exit;
            }
            if (defined('IAMDEVELOPER') && self::ADODB_DEBUG) {
                //This is to show stats from adoDB to help optimize
                //queries.  Should not ship with the ADODB_DEBUG on.

                session_start(); //session variables required for monitoring
                $this->db->LogSQL();
                $this->perf =& NewPerfMonitor($this->db);
            }
            //See if we need to turn off sql strict mode.  $strict_mode to be set in config.php.
            if (isset($strict_mode) && $strict_mode) {
                //10/19/2016 -- strict_mode=1 is now a config.php default, so this always happens unless explicitly
                //turned off
                $this->Execute('SET SESSION sql_mode=\'\'');
            }
            //Manually force the database connection to use a different charset than what
            //is set in the server's configuration.
            if (isset($force_db_connection_charset) && strlen(trim($force_db_connection_charset))) {
                $this->db->Execute("SET NAMES '$force_db_connection_charset'");
                //$this->db->Execute("SET CHARACTER SET $force_db_connection_charset");
            }
            //If we ever get to the point where no scripts rely on result set to be
            //returned numerically, un-comment this (requires huge massive across the board
            //testing)
            $this->db->SetFetchMode(ADODB_FETCH_ASSOC) ;
        }
        //should be connected to the database at this point.
    }

    /**
     * Function to see if we are currently connected to the database.
     *
     * @return bool
     */
    public function IsConnected()
    {
        if (isset($this->db) && is_object($this->db) && $this->db->IsConnected()) {
            return true;
        }
        return false;
    }

    /**
     * Returns number of rows affected by previous query.
     */
    function Affected_Rows()
    {
        $this->init();
        return ($this->db->Affected_Rows());
    }

    /**
     * Execute function wraps the ado db execute funtion, and does additional
     * error checking, profiling, and debugging, accessed through an error
     * handler addon
     *
     * @param String $sql
     * @param (Optional)Array $inputs see documentation for ADODB Execute
     * @return Mixed result set for executed query
     */
    public function Execute($sql, $inputs = false)
    {
        //first time something is executed, we connect.  That way, if nothing is ever executed,
        //then no connection is ever made. (init function already checks to see if we are connected)
        $this->init();

        trigger_error('DEBUG STATS_EXTRA: Using Execute wrapper!  Query: ' . $sql);
        $sqlI = "Execute: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        //lets execute!
        try {
            //this is php5 mode, which adodb supports error catching, so lets use it.
            if (is_array($inputs)) {
                $results = $this->db->Execute($sql, $inputs);
            } else {
                $results = $this->db->Execute($sql);
            }
            $execution_time = ($this->microtime_float() - $start);
            $this->executeTime += $execution_time;
            $this->numExecutes ++;
            $this->queries[$sqlI]['time'][] = $execution_time;
            if ($results === false) {
                //there was an execute error.
                trigger_error('ERROR SQL: Sql Query: ' . $sql . ' Error Reported: ' . $this->db->ErrorMsg());
                return false;
            }
        } catch (Exception $e) {
            trigger_error('ERROR SQL STATS: Sql Query: ' . $sql . ' Error Caught: ' . print_r($e, 1));
            return false;
        }
        return $results;
    }

    /**
     * Wrapper for outputing any db error messages.
     * @return String Error message string.
     */
    public function ErrorMsg()
    {
        if (isset($this->errors) && strlen($this->errors)) {
            return $this->errors;
        }
        $this->init();
        return ($this->db->ErrorMsg());
    }

    /**
     * Wraps ADODB::qstr, see adodb documentation
     *
     * @param Mixed $var_1
     * @param Mixed $var_2
     * @return Mixed
     */
    public function qstr($var_1, $var_2 = false)
    {
        $this->init();
        return $this->db->qstr($var_1, $var_2);
    }

    /**
     * Wraps ADODB::Prepare(), see adodb documentation.
     *
     * @param string $sql
     * @return Mixed
     */
    public function Prepare($sql)
    {
        $this->init();

        $sqlI = "Prepare: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        //execute
        $statement = $this->db->Prepare($sql);

        $execution_time = ($this->microtime_float() - $start);
        $this->executeTime += $execution_time;
        $this->numExecutes ++;
        $this->queries[$sqlI]['time'][] = $execution_time;

        if ($statement === false) {
            trigger_error('ERROR SQL: Error Running db->Prepare(), query: ' . $sql
                . ' Error Message: ' . $this->db->ErrorMsg());
            return false;
        }
        return $statement;
    }

    /**
     * Wraps ADODB::GetArray(), see adodb documentation.
     *
     * @param string $sql
     * @return Mixed
     */
    public function GetArray($sql)
    {
        $this->init();

        $sqlI = "GetArray: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        try {
            $result = $this->db->GetArray($sql);
            $execution_time = ($this->microtime_float() - $start);
            $this->executeTime += $execution_time;
            $this->numExecutes ++;
            $this->queries[$sqlI]['time'][] = $execution_time;

            if ($result === false) {
                trigger_error('ERROR SQL: Error Running db->GetArray(), query: ' . $sql . ' Error Message: '
                    . $this->db->ErrorMsg());
                return false;
            }
            return $result;
        } catch (Exception $e) {
            trigger_error('ERROR SQL STATS: Sql Query: ' . $sql . ' Error Caught: ' . print_r($e, 1));
            return false;
        }
    }

    /**
     * Wraps ADODB::GetOne(), see adodb documentation
     *
     * @param string $sql
     * @param Mixed $inputarr
     * @return unknown
     */
    public function GetOne($sql, $inputarr = false)
    {
        $this->init();

        $sqlI = "GetOne: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        try {
            $result = $this->db->GetOne($sql, $inputarr);
            $execution_time = ($this->microtime_float() - $start);
            $this->executeTime += $execution_time;
            $this->numExecutes ++;
            $this->queries[$sqlI]['time'][] = $execution_time;
        } catch (Exception $e) {
            trigger_error('ERROR SQL STATS: Error Running db->GetOne(), query: ' . $sql
                . ' Error Message: ' . $this->db->ErrorMsg());
            return false;
        }
        return $result;
    }

    /**
     * Wraps ADODB::SetFetchMode(), see adodb documentation.
     *
     * @param Mixed $value
     * @return Mixed
     */
    public function SetFetchMode($value)
    {
        $this->init();
        try {
            return $this->db->SetFetchMode($value);
        } catch (Exception $e) {
            trigger_error('ERROR SQL STATS: Error Caught: ' . print_r($e, 1));
            return false;
        }
    }

    /**
     * Wraps ADODB::Insert_Id(), see adodb documentation
     *
     * @return Mixed
     */
    public function Insert_Id()
    {
        $this->init();
        return $this->db->Insert_Id();
    }


    /**
     * Wraps ADODB::GetRow(), see adodb documentation.
     *
     * @param string $sql
     * @param mixed $data
     * @return array|bool
     */
    public function GetRow($sql, $data = false)
    {
        //Need to add statistics stuff
        $this->init();

        $sqlI = "GetRow: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        try {
            $result = $this->db->GetRow($sql, $data);

            $execution_time = ($this->microtime_float() - $start);
            $this->executeTime += $execution_time;
            $this->numExecutes ++;
            $this->queries[$sqlI]['time'][] = $execution_time;
        } catch (Exception $e) {
            trigger_error('ERROR SQL STATS: Error Caught: ' . $this->ErrorMsg());
            return false;
        }
        return $result;
    }

    /**
     * Wraps ADODB::GetCol(), see adodb documentation.
     *
     * @param mixed $var1
     * @param mixed $var2
     * @return mixed
     */
    public function GetCol($var1, $var2 = false)
    {
        $this->init();

        $sqlI = "GetCol: $var1";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        try {
            $result = $this->db->GetCol($var1, $var2);

            $execution_time = ($this->microtime_float() - $start);
            $this->executeTime += $execution_time;
            $this->numExecutes ++;
            $this->queries[$sqlI]['time'][] = $execution_time;
        } catch (Exception $e) {
            trigger_error('ERROR SQL: Query: ' . $var1 . ' Error Caught: ' . $this->ErrorMsg());
        }
        return $result;
    }

    /**
     * Wraps ADODB::GetAssoc, see adodb documentation
     *
     * @param string $sql
     * @param mixed $data
     * @return array
     */
    public function GetAssoc($sql, $data = false)
    {
        $this->init();

        $sqlI = "GetAssoc: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();

        $result = $this->db->GetAssoc($sql, $data);

        $execution_time = ($this->microtime_float() - $start);
        $this->executeTime += $execution_time;
        $this->numExecutes ++;
        $this->queries[$sqlI]['time'][] = $execution_time;

        return $result;
    }

    /**
     * Wraps ADODB::GetAll, see adodb documentation
     *
     * @param string $sql
     * @param mixed $data
     * @return array|bool
     */
    public function GetAll($sql, $data = false)
    {
        $this->init();

        $sqlI = "GetAll: $sql";
        if (!isset($this->queries[$sqlI])) {
            $this->queries[$sqlI]['count'] = 1;
        } else {
            $this->queries[$sqlI]['count'] ++;
        }
        $start = $this->microtime_float();
        //exe
        $result = $this->db->GetAll($sql, $data);

        $execution_time = ($this->microtime_float() - $start);
        $this->executeTime += $execution_time;
        $this->numExecutes ++;
        $this->queries[$sqlI]['time'][] = $execution_time;
        if ($result === false) {
            trigger_error('ERROR SQL: in GetAll, sql: ' . $sql . ' : Error msg: ' . $this->ErrorMsg());
        }
        return $result;
    }
    /**
     * Wraps ADODB::MetaError, see adodb documentation
     */
    public function MetaError()
    {
        $this->init();
        return $this->db->MetaError();
    }

    /**
     *  Wrapper for db->Close()
     */
    public function Close()
    {
        if ($this->isConnected()) {
            $this->db->Close();
            $this->db = false;
        }
    }

    ##### --- Data Access Functionality: --- #####

    /**
     * Gets the current database usage statistics, in an HTML table.
     *
     * @return string Current db stats, in an html table format.
     */
    public function getStats()
    {
        //returns some stats.
        $stats = 'Num Queries: ' . $this->numExecutes . ' Time spent on queries: ' . $this->executeTime
            . ' sec.<br />' . "\n";
        $stats .= 'Query Stats:' . "\n";
        $stats .= "<table border=\"1\"><thead><tr><th>Time(s) each query took</th><th>Query</th>
            <th># times executed</th></thead><tbody>\n";
        foreach ($this->queries as $query => $q_stat) {
            $totalT = 0;
            if (count($q_stat['time']) > 2) {
                foreach ($q_stat['time'] as $t) {
                    $totalT += $t;
                }
                $q_stat['time'][] = "<br /><strong>Total</strong>:{$totalT}";
            }
            $stats .= "<tr><td>" . implode(', ', $q_stat['time'])
                . "</td><td>$query</td><td>{$q_stat['count']}</td></tr>\n";
        }
        $stats .= "</tbody></table>\n";
        if (defined('IAMDEVELOPER') && self::ADODB_DEBUG) {
            //use this for testing, to root out slow queiries and nix em
            //ADODB_DEBUG (at top) should be commented out for distrobution.
            $this->perf->UI($pollsecs = 5);
        }
        return $stats;
    }

    /**
     * Function to get all the site configuration settings. This uses the new site config
     * table.
     * @param boolean $return_table if set to true, will return the configuration settings.
     * @return mixed
     */
    public function get_site_settings($return_table = false)
    {
        //force_fresh_get is no longer needed, since we update the config table automatically.
        if (isset($this->configuration_data)) {
             //dont get the data twice if we already have it.
            if ($return_table) {
                return $this->configuration_data;
            }
             return true;
        }
         $use_cache = geoCache::get('cache_setting');
        if ($use_cache) {
            $cacheSettings = geoCacheSetting::getInstance();

            //to get the old config settings...
            $config_data = $cacheSettings->process('configuration_data');
        }
        if (!$use_cache || $config_data === false) {
            $sql = "SELECT * FROM " . geoTables::site_configuration_table;
            $result = $this->Execute($sql);
            if ($result === false) {
                return false;
            }
            $this->configuration_data = $result->FetchRow();
            if ($use_cache) {
                $cacheSettings->update('configuration_data', $this->configuration_data);
            }
        } else {
            $this->configuration_data = $config_data;
        }
        if ($use_cache) {
            $site_settings = $cacheSettings->process('site_settings');
        }
        if (!$use_cache || $site_settings === false) {
            //to get the new site settings.
            $sql = 'SELECT `setting`, `value` FROM ' . geoTables::site_settings_table;

             $rows = $this->GetAll($sql);

            if (false === $rows) {
                 trigger_error("Unable to query the site_settings_table. " . $this->ErrorMsg());
                 trigger_error("FLUSH MESSAGES");
                 die();
            }
            if ($use_cache) {
                $cacheAdd = array();
            }
            foreach ($rows as $row) {
                //side effect: any settings duplicated in configuration data and sit config tables,
                //will be overridden by the newer table.
                $this->configuration_data[$row['setting']] = $row['value'];
                if ($use_cache) {
                    $cacheAdd[$row['setting']] = $row['value'];
                }
            }
            if ($use_cache) {
                $cacheSettings->update('site_settings', $cacheAdd);
            }
        } else {
            foreach ($site_settings as $key => $val) {
                $this->configuration_data[$key] = $val;
            }
        }

        if ($return_table) {
            return $this->configuration_data;
        }
         return true;
    }

    /**
     * Function used to get master switches set.
     *
     * @return array
     * @since Version 7.0.3
     */
    public function getMasters()
    {
        $rows = $this->GetAll("SELECT * FROM " . geoTables::master);

        //List of master switches that are "forced off" if one of them is the restriction
        //in place for the license.
        $masters_restricted = self::_masters_restricted();

        $only = geoPC::getInstance()->license_only();

        $settings = array();

        foreach ($rows as $row) {
            if ($only && in_array($row['setting'], $masters_restricted)) {
                //one of the "potentially" restricted master switches.. force it on
                //if it's the "only" one, off otherwise...

                $settings[$row['setting']] = ($only === $row['setting']) ? 'on' : 'off';
                continue;
            }

            $settings[$row['setting']] = $row['switch'];
        }
        return $settings;
    }

    /**
     * Makes sure the "master value" is either "on" or "off".  If it is bool,
     * it will set to on for true, off for false.  Or if not on/off and not bool,
     * it will be evaluated as a bool and set to 'on' if true, 'off' if evaluates
     * to false.
     *
     * @param string $setting The master setting
     * @param string|bool $value Either on/off or true/false
     * @since Version 7.0.3
     */
    public function cleanMasterValue($setting, $value)
    {
        $masters_restricted = self::_masters_restricted();
        $only = geoPC::getInstance()->license_only();
        $setting = trim($setting);

        //Yes, this could have been done directly in the geoMaster class.  It is
        //here to make it harder to turn on master switches that are not allowed
        //for the license type.
        if ($only && in_array($setting, $masters_restricted)) {
            //this is possibly restricted...
            return ($only === $setting) ? 'on' : 'off';
        }
        //not restricted
        if ($value === true || $value === 'on') {
            $switch = 'on';
        } elseif ($value === false || $value === 'off') {
            $switch = 'off';
        } else {
            //if it's not on or off and not bool, treat it as a bool
            $switch = ((bool)$value) ? 'on' : 'off';
        }
        return $switch;
    }

    /**
     * Gets array of master switches that are restricted if the license has
     * something set for "only" meaning that it can "only" have that listing type
     *
     * @internal
     */
    private static function _masters_restricted()
    {
        //This is the array of master switches that are restricted if the license
        //has something set for "only" meaning that it can "only" have that listing
        //type.

        //NOTE:  ALSO add to the array it checks in:
        //admin/master.php
        //geoPC::_mainDemo()

        return array ('classifieds','auctions');
    }

    /**
     * Array of the columns from the old configuration table
     * @var array
     */
    private $old_config_columns;

    /**
     * Initializes the old config columns
     * @internal
     */
    private function init_old_config_columns()
    {
        if (!is_array($this->old_config_columns)) {
            $this->old_config_columns = array();
            $sql = 'SHOW COLUMNS FROM ' . geoTables::site_configuration_table;
            $result = $this->Execute($sql);
            if (!$result) {
                return false;
            }
            while ($row = $result->FetchRow()) {
                $this->old_config_columns[] = $row['Field'];
            }
        }
    }

     /**
      * Sets a site config setting.  Does NOT check whether we are in admin or not, so that type of check must be done
      * before this function is called.
      *
      * @param string $setting The setting name to set
      * @param string $value The value to set the setting to.  If false, this will remove that setting from the table.
      * @param bool $use_long Set to true to use the long database if the size is over 255 chars.
      * @param bool $pushToDb Set to false and it will not make the change to the setting
      *   in the database, so it is effective only for the rest of the page-load.
      *   This param added in version 6.0.0
      * @return bool true if it appears the setting was saved, false otherwise.
      */
    public function set_site_setting($setting, $value, $use_long = false, $pushToDb = true)
    {
        if (isset($this->configuration_data[$setting]) && $this->configuration_data[$setting] === $value) {
            //it's already set to the exact same thing, no need to re-set it.
            return true;
        }
        if ($value === false || $value === null) {
            //null not allowed, so if null, assume false is meant.
            if ($pushToDb) {
                $sql = 'DELETE FROM ' . geoTables::site_settings_table . ' WHERE `setting` = ? LIMIT 1';
                $result = $this->Execute($sql, array($setting));
                if (!$result) {
                    //don't need to show error message, the wrapper does that for us.
                    //trigger_error('ERROR SQL: Query: '.$sql_query.' ERROR: '.$this->db->ErrorMsg());
                    return false;
                }
                if ($use_long) {
                    //also delete from the long table
                    $sql = 'DELETE FROM ' . geoTables::site_settings_long_table . ' WHERE `setting` = ? LIMIT 1';
                    $result = $this->Execute($sql, array($setting));
                    if (!$result) {
                        trigger_error('ERROR SQL: Error deleting long setting ' . $setting . ' - Query: ' . $sql
                            . ' ERROR: ' . $this->db->ErrorMsg());
                        return false;
                    }
                }
            }
            $this->configuration_data[$setting] = false;
            //if the setting exists in the old table, set it to 0 in that old table as well, just to be safe.
            if ($pushToDb) {
                $this->init_old_config_columns();
                if (in_array($setting, $this->old_config_columns)) {
                    $sql = 'UPDATE ' . geoTables::site_configuration_table . ' SET `' . $setting . '` = 0 LIMIT 1';
                    $result = $this->Execute($sql);
                    if (!$result) {
                        return false;
                    }
                    geoCacheSetting::expire('configuration_data');
                }
                //clear normal cache, whether it's long or not, since long could be in
                //normal cache
                geoCacheSetting::expire('site_settings');
                if ($use_long) {
                    //clear cache specific to long setting
                    geoCacheSetting::expire('site_settings_long_' . $setting);
                }
            }
            return true;
        }
        if (strlen($value) < 255 || !$use_long) {
            $table_to_use = geoTables::site_settings_table;
            //where to delete setting from.
            $table_to_delete_from = geoTables::site_settings_long_table;
        } else {
            $table_to_use = geoTables::site_settings_long_table;
            //where to delete setting from.
            $table_to_delete_from = geoTables::site_settings_table;
        }
        if ($pushToDb) {
            trigger_error('DEBUG STATS_EXTRA: DataAccess::set_site_setting() - Setting ' . $setting . ' to ' . $value
                . ' in table ' . $table_to_use);
            $sql = 'REPLACE INTO ' . $table_to_use . ' SET `setting` = ?, `value` = ?';
            $result = $this->Execute($sql, array($setting, $value));
            if (!$result) {
                trigger_error('ERROR STATS_EXTRA SQL: DataAccess::set_site_setting() - Setting ' . $setting
                    . ' query failed!  Setting not set!');
                return false;
            }
            if ($use_long) {
                //delete the other setting just in case it was set already.
                trigger_error('DEBUG STATS_EXTRA: DataAccess::set_site_setting() - use_long so deleting ' . $setting
                    . ' from other table ' . $table_to_delete_from);
                $sql = 'DELETE FROM ' . $table_to_delete_from . ' WHERE `setting` = ? LIMIT 1';
                $result = $this->Execute($sql, array($setting));
                if (!$result) {
                    return false;
                }
            }
        }

        $this->configuration_data[$setting] = $value;
        if ($pushToDb) {
            //clear normal cache, whether it's long or not, since long could be in
            //normal cache
            geoCacheSetting::expire('site_settings');
            if ($use_long) {
                //clear cache specific to long setting
                geoCacheSetting::expire('site_settings_long_' . $setting);
            }
        }
        return true;
    }

    /**
     * Gets a particular site setting, returns false if the setting is not
     * found.  Uses the new geodesic_site_settings table, and the old
     * geodesic_classifieds_configuration table.  If a setting is
     * set in both geodesic_site_settings, and geodesic_classifieds_configuration,
     * the setting from geodesic_site_settings takes precedence. Note that
     * the table geodesic_classifieds_configuration will not be used any more,
     * once all the settings have been moved over to the new table.
     *
     * @param string setting The setting you wish to get.
     * @param [Optional]String $check_long Set to true if you want it to also check the long setting
     *  table.
     * @return mixed The value for the setting, or false if the setting is not
     *  set.
     */
    public function get_site_setting($setting, $check_long = false)
    {
        $this->get_site_settings();
        if (isset($this->configuration_data[$setting])) {
            return $this->configuration_data[$setting];
        }
        if (!$check_long) {
            return false;
        }
        //if the setting is not set, see if it is one of the longer settings
        if (strlen($setting) == 0) {
            return false;
        }
        $cacheSettings = geoCacheSetting::getInstance();
        $longCache = $cacheSettings->process('site_settings_long_' . $setting);
        if ($longCache === false) {
            try {
                $sql = 'SELECT `setting`,`value` FROM ' . geoTables::site_settings_long_table . ' WHERE `setting`=?';
                $results = $this->Execute($sql, array($setting));
                if (!$results) {
                    return false;
                }
                if ($results->NumRows() == 0) {
                    //no matching settings
                    $this->configuration_data[$setting] = false;
                    return false;
                }
                $row = $results->FetchRow();
                if (!$row) {
                    $this->configuration_data[$setting] = false;
                    return false;
                }
                $cacheSettings->update('site_settings_long_' . $setting, array ($setting => $row['value']));
                return $row['value'];
            } catch (Exception $e) {
                //db error.. should not get here, since Execute catches errors.
                return false;
            }
        } else {
            if (isset($longCache[$setting])) {
                return $longCache[$setting];
            }
            return false;
        }
    }

    /**
     * array of text stuff...
     *
     * @internal
     */
    private $messages;
    /**
     * private
     *
     * @internal
     */
    private $messages_pages;
    /**
     * The current language id
     * @internal
     */
    private $language_id;
    /**
     * Function to make sure the text for a certain page or pages are loaded.
     *
     * @param boolean $return_text set to true if you want the text array to
     *  be returned.
     * @param int $current_page_id The page id to get the text for.
     */
    public function get_text($return_text = false, $current_page_id = 0)
    {
        $this->init();
        if (!$current_page_id) {
            if ($return_text) {
                return $this->messages;
            }
            return false;
        }
        $in_array = array();
        $in_text = array();
        $args_list = (is_array($current_page_id)) ? $current_page_id : array($current_page_id);
        $cache_on = geoCache::get('cache_text');
        if ($cache_on) {
            $textCache = geoCacheText::getInstance();
        }
        $language_id = $this->getLanguage();
        $use_cache = true;
        foreach ($args_list as $page_id) {
            if (!isset($this->messages_pages[$page_id])) {
                if ($cache_on) {
                    $cacheTextArray = $textCache->process($language_id, $page_id);
                }
                if ($cache_on) {
                    trigger_error('DEBUG STATS: cache process: $language_id = ' . $language_id . ' page_id = '
                        . $page_id);
                }
                if (!$cache_on || $cacheTextArray === false) {
                    $use_cache = false;
                    $in_array[] = $page_id;
                    $in_text[] = '?';
                } else {
                    if (!is_array($this->messages)) {
                        $this->messages = array();
                    }
                    if (!is_array($cacheTextArray)) {
                        //echo 'NOT ARRAY: <pre>'.print_r($cacheTextArray).'</pre><br />';
                    }
                    $keys = array_keys($cacheTextArray);
                    foreach ($keys as $key) {
                        //loading messages from cache (which are not encoded or anything)
                        $this->messages[$key] = $cacheTextArray[$key];
                    }
                }
                $this->messages_pages[$page_id] = 1;
            }
        }
        unset($page_id);
        if (count($in_array) == 0) {
            //we already got all the pages!
            if ($return_text) {
                return $this->messages;
            } else {
                return true;
            }
        }
        if (!$cache_on || !$use_cache) {
            $where = '';
            //keep us from using "in" if there is only 1 page to get.
            if (count($in_array) == 1) {
                $where = 'page_id = ?';
                $page_id = $in_array[0];
            } else {
                $where = 'page_id in ( ' . implode(', ', $in_text) . ' )';
            }
            $sql = "SELECT `text_id`,`text`, `page_id` from " . geoTables::pages_text_languages_table
                . " WHERE $where AND `language_id` = '{$this->getLanguage()}'";
            //echo $sql."<br>\n";
            if (!is_array($this->messages)) {
                $this->messages = array();
            }
            $result = $this->GetAll($sql, $in_array);
            if ($result === false) {
                trigger_error('ERROR SQL: sql error, sql: ' . $sql . ' Error message: ' . $this->ErrorMsg());
                return false;
            }

            //take the database message result and push the contents into an array
            if ($cache_on) {
                $cacheArray = array();
            }

            //echo "<pre>result:".print_r($result,1)."</pre>";
            //while ($show = $result->FetchRow())

            foreach ($result as $show) {
                $message = geoString::fromDB($show['text']);

                //parse for any {external ...} in the text
                $message = geoTemplate::parseExternalTags($message);

                $this->messages[$show['text_id']] = $message;
                if ($cache_on) {
                    $cacheArray[$show['page_id']][$show['text_id']] = $message;
                }
            }

            if ($cache_on && isset($page_id) && $page_id && isset($cacheArray[$page_id])) {
                trigger_error('DEBUG STATS: Updating: $language_id: ' . $language_id . ' page_id = ' . $page_id);
                $textCache->update($language_id, $page_id, $cacheArray[$page_id]);
            } elseif ($cache_on) {
                foreach ($cacheArray as $page_id => $page_text) {
                    trigger_error('DEBUG STATS: Updating: $language_id: ' . $language_id . ' page_id = ' . $page_id
                        . ' $page_text = ' . htmlspecialchars($page_text));
                    $textCache->update($language_id, $page_id, $page_text);
                }
            }
        }
        if ($return_text) {
            return $this->messages;
        }
        return true;
    } // end of function get_text

    /**
     * Gets the  language for the session or gets the default language for the site.
     *
     * @param boolean $from_db If this is set to true, this will return the default language
     *  for the site.  If left to default value, it will return the language for the current user.
     * @return int the language_id of the session, or of the defualt language for the site, depending on what
     *  $from_db is set to.
     */
    public function getLanguage($from_db = false)
    {
        if (isset($this->language_id)) {
            return $this->language_id;
        }
        if ($from_db) {
            //get default language
            $sql = "SELECT language_id FROM geodesic_pages_languages where default_language = 1";
            $result = $this->Execute($sql);
            if ($result && $result->RecordCount() == 1) {
                $row = $result->FetchRow();
                $this->language_id = (int)$row['language_id'];
                return $this->language_id;
            }
            //if all else failed above, set language to 1.
            return 1;
        }
        //let the session class deal with getting the language, if they don't want to
        //get it from the db.
        $session = geoSession::getInstance();
        return $session->getLanguage();
    }

    /**
     * internal
     * @internal
     */
    private $num_new_ads;

    /**
     * Optimized version of the old site->num_new_ads_in_category function
     * that, if {@link DataAccess::preload_num_new_ads()} is used, gets the data from cached counts,
     * significantly speeding up page load times.
     *
     * @param int $category_id
     * @param int $ends_limit
     * @param int $placed_limit
     * @return int Number of ads in the category.
     */
    public function num_new_ads_in_category($category_id, $ends_limit, $placed_limit)
    {
        if (!isset($this->preloaded_num_new_ads)) {
            $this->preload_num_new_ads($ends_limit, $placed_limit);
        }

        if (!isset($this->num_new_ads[$category_id])) {
            return false;
        }
        return $this->num_new_ads[$category_id];
    }

    /**
     * pre-loaded count of categories with new ads, I think...
     * @internal
     */
    private $preloaded_num_new_ads;
    /**
     * This preloads the count of new ads for every category
     *
     * @param int $ends_limit Ending time for new ads must be greater than this.
     * @param int $placed_limit Time the ad is placed must be greater than this.
     */
    public function preload_num_new_ads($ends_limit, $placed_limit)
    {
        if ($this->preloaded_num_new_ads) {
            //already pre-loaded
            return;
        }

        $this->preloaded_num_new_ads = true;
        $query = $this->getTableSelect(self::SELECT_BROWSE, true);
        $ends_limit = (int)$ends_limit;
        $placed_limit = (int)$placed_limit;

        $classTable = geoTables::classifieds_table;
        $lclassTable = geoTables::listing_categories;

        $query->where("$classTable.`live`=1", 'live')
            ->where("$classTable.`ends` > $ends_limit OR $classTable.`ends` = 0")
            ->where("$classTable.`date` > $placed_limit")
            ->where("$lclassTable.`listing`=$classTable.`id`");

        $sql = "SELECT DISTINCT(`category`) FROM " . geoTables::listing_categories . " WHERE EXISTS($query)";

        trigger_error('DEBUG STATS: Starting preload_num_new_ads.');

        $result = $this->Execute($sql);
        if ($result === false) {
            $this->preloaded_num_new_ads = false;
            return false;
        }
        foreach ($result as $row) {
            if (isset($this->num_new_ads[$row['category']]) && $this->num_new_ads[$row['category']] > 0) {
                //already set, don't need to add more...  Although this shouldn't happen...
                continue;
            }

            $this->num_new_ads[$row['category']] = 1;
        }
        trigger_error('DEBUG STATS STATS: Finished preload_num_new_ads.');
    }

    /**
     * Array of allowed (valid) module tags
     * @var array
     * @internal
     */
    private $allowedTags = false;

    /**
     * Used by the template system to load the contents of {module ...} tags
     *
     * @param string $tag
     * @param array $params
     * @param Object $smarty
     * @return string
     * @since Version 6.0
     */
    public function moduleTag($tag, $params, $smarty)
    {
        trigger_error('DEBUG MODULE: DataAccess::moduleTag(' . $tag . ') - top');

        $view = geoView::getInstance();

        //set up vars local to module
        $language_id = $this->getLanguage();
        $cat_id = $view->getCategory();
        $logged_in = geoSession::getInstance()->getUserID();
        $pageCache = geoCachePage::getInstance();
        $settingsCache = geoCacheSetting::getInstance();

        if ($this->allowedTags === false) {
            $allowedTags = $settingsCache->process('module_tag_list');
            if ($allowedTags === false) {
                //going to have to get tags manually
                $sql = 'SELECT `module_replace_tag` FROM `geodesic_pages` WHERE `module` = 1';
                $tagResult = $this->Execute($sql);
                $allowedTags = array();
                if (!$tagResult) {
                    trigger_error('ERROR SQL: sql:' . $sql . ' Error:' . $this->ErrorMsg());
                    return '';
                }
                while ($row = $tagResult->FetchRow()) {
                    if (!in_array($row['module_replace_tag'], $allowedTags) && strlen($row['module_replace_tag']) > 0) {
                        $allowedTags[] = $row['module_replace_tag'];
                    }
                }
                $settingsCache->update('module_tag_list', $allowedTags);
            }
            $this->allowedTags = $allowedTags;
        }
        if (!in_array($tag, $this->allowedTags)) {
            //this aint a real tag.
            trigger_error('DEBUG MODULE: DataAccess::moduleTag(' . $tag . ') - not allowed tag');
            //return '';
        }
        trigger_error('DEBUG MODULE: DataAccess::moduleTag(' . $tag . ') - allowed!');
        $cacheResult = $pageCache->process($tag, $language_id, $cat_id, $logged_in, true, $params);
        if ($cacheResult === false) {
            //get page data.
            trigger_error('DEBUG MODULE: DataAccess::moduleTag(' . $tag . ') - not from cache');


            //echo('DEBUG STATS: db::replaceTag( '.$tag.', '.$language_id.', '.$cat_id.', '.$logged_in.')<br />');
            if (geoCache::get('cache_module')) {
                $moduleCache = geoCacheModule::getInstance();
                $show_module = $moduleCache->process($tag);
            } else {
                $show_module = false;
            }
            if (!$show_module) {
                $sql = 'SELECT * FROM `geodesic_pages` WHERE `module_replace_tag` = ?';
                $result = $this->Execute($sql, array($tag));
                if (!$result) {
                    trigger_error('ERROR SQL: sql:' . $sql . ' Error: ' . $this->ErrorMsg());
                    return false;
                }
                $show_module = $result->FetchRow();
                if (geoCache::get('cache_module')) {
                    $moduleCache->update($tag, $show_module);
                }
            }
            //let params passed in through the module tag to over-write default module settings
            $show_module = array_merge($show_module, $params);
            if (
                isset($show_module['module_file_name'])
                && (file_exists(MODULES_DIR . $show_module['module_file_name']))
            ) {
                //process module
                trigger_error('DEBUG MODULE: DataAccess::moduleTag(' . $tag . ') - processing!');
                //set vars used by module
                $addon = geoAddon::getInstance();
                $db = $this;
                //note: view set above...
                $page = $view->getPage();

                $page->messages = $this->get_text(true, $show_module['page_id']);
                $page->language_id = $language_id;

                require MODULES_DIR . $show_module['module_file_name'];

                $vars = (array)$view->module_vars[$tag];
                $file = $view->geo_inc_files['modules'][$tag];
                if (!$file) {
                    //nothing to do!
                    return '';
                }
                $g_type = geoTemplate::MODULE;
                $g_resource = $tag;

                $vars['messages'] = $this->messages;
                //since we may cache output, don't allow assigning it, at least not here...
                $tParams = $params;
                unset($tParams['assign']);
                $cacheResult = geoTemplate::loadInternalTemplate($tParams, $smarty, $file, $g_type, $g_resource, $vars);
                if ($pageCache->canCache($tag)) {
                    //do our own brand of caching, don't use smarty's as that would
                    //require yet another folder to be writable
                    $pageCache->update(
                        $tag,
                        $language_id,
                        $cat_id,
                        $logged_in,
                        geoCachePage::quotePage($cacheResult),
                        $params
                    );
                }
                trigger_error("DEBUG MODULE: DataAccess::moduleTag($tag) - $tag : $file");
            }
        }
        if ($params['assign'] && $cacheResult) {
            //Manually assign here (instead of letting geoTemplate::loadInternalTemplate() do
            //the work), to allow contents to be cached if needed.
            $smarty->assign($params['assign'], $cacheResult);
            return '';
        }
        //echo "result:<pre>".htmlspecialchars($cacheResult).'</pre><br />';
        return $cacheResult;
    }

    /**
     * Do not use this any more, instead use geoEmail::sendMail()
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param (optional)string $from pass zero for site default
     * @param (optional)string $replyTo pass zero for site default
     * @param (optional)string $charset pass zero for site default
     * @param (optional)string $type HTML, plain text, etc, use zero for site default
     * @deprecated 03/23/2009
     */
    public function sendMail($to, $subject, $content, $from = 0, $replyTo = 0, $charset = 0, $type = 0)
    {
        geoEmail::sendMail($to, $subject, $content, $from, $replyTo, $charset, $type);
        return true;
    }

    /**
     * Gets the current database settings.  Meant to be used for api integration.
     * @return array Array containing the db type and the database name.
     */
    public function getDbInfo()
    {
        $this->init();
        return $this->db_info;
    }

    //Utility functions
    /**
     * Utility function, gets the current microtime and returns it in float
     * format.
     */
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Whether IP has already been checked or not
     * @var bool
     * @internal
     */
    private $ipIsChecked;

    /**
     * Replaces the site_class check_ip.  This checks to see if ip banning is turned on, if it
     * is, it checks the current IP against the banned ip's.  If the current IP matches a banned
     * IP, it unsets all user-input data, like REQUEST, POST, GET, COOKIE, and treats the user like
     * a robot (no session, no redirect, etc).  That way, if someone is on IP ban list, the only
     * thing they can do is view the front page, nothing will work.
     *
     * Special: to test if an IP is banned or not, do ?check_ban_ip=1 in the URL, and will display
     *  a message of whether the IP is banned.  This only happens if there are IPs in the banned
     *  ip table.
     *
     * CLIENT SIDE
     *
     * @return boolean true to indicate it has checked the ip successfully, false
     *  if there was an error checking the IP.
     */
    public function checkBannedIp()
    {
        if (defined('IN_ADMIN') || (isset($this->ipIsChecked) && $this->ipIsChecked)) {
            //do not run multiple times, and do not run if not enterprise,
            //and do not run if in admin, so that admin can remove themselves
            //from the ban list.
            return true;
        }
        //code to check the current ip against a banned list in the database
        $ip_to_check = $_SERVER['REMOTE_ADDR'];

        $sql = "SELECT `ip` FROM " . $this->geoTables->ip_ban_table;
        //skip checking query, ip banning is all levels
        $this->init();
        $ip_result = $this->db->Execute($sql);
        if (!$ip_result) {
            trigger_error('ERROR SQL: SQL query failed for retrieving banned ips.  Query:' . $sql . ' Error: '
                . $this->ErrorMsg());
            return false;
        }
        if ($ip_result->RecordCount() > 0) {
            $ban_me = false;
            while (!$ban_me && $ip_banned = $ip_result->FetchRow()) {
                //turn the banned ip into a regular expression
                $ip_banned = str_replace('.', '\.', $ip_banned['ip']);
                //convert * to regular expression
                $ip_banned = '/^' . str_replace('*', '[0-9.]*', $ip_banned) . '$/';

                if (preg_match($ip_banned, $ip_to_check) == 1) {
                    $ban_me = true;
                }
            }
            if (isset($_GET['check_ban_ip']) && $_GET['check_ban_ip']) {
                //If there is a get ver check_ban_ip and it is true, display whether or not the ip is banned or not.
                //this will only happen if there are IPs to ban in the ip table.
                die($ip_to_check . ' - IP BANNED? ' . (($ban_me)
                    ? 'YES, this IP will only see front page of site.' : 'NO, this ip not banned.'));
            }
            if ($ban_me) {
                //this ip exists in the ip ban list
                //do not allow to view any pages, besides front page.

                //Do this by unsetting all user input values.
                //Note: using unset($_REQUEST,$_POST,$_GET,$_COOKIE) does not work
                // in PHP 4, so use following method instead, since it works on either PHP
                $_REQUEST = array();
                $_POST = array();
                $_GET = array();
                $_COOKIE = array();

                //also, make the user be treated like a robot, that is no sessions,
                //no cookies for sessions, and no redirecting.
                if (!defined('IS_ROBOT')) {
                    define('IS_ROBOT', 1);
                }
            }
        }
        $this->ipIsChecked = true;
        return true;
    }

    /**
     * Array of table select objects
     * @var array
     * @internal
     */
    private $_tableSelects = array();

    /**
     * Gets the table select object so that it can be configured by different
     * sources, such as built-in functionality and also allow addons to modify
     * functionality.
     *
     * @param string $for Either DataAccess::SELECT_BROWSE for the table select
     *   object used during browsing, or DataAccess::SELECT_SEARCH to affect the
     *   search query.
     * @param bool $copy If true, will return a copy instead of original, typically
     *   places that are using this to actually generate a query will pass in true
     * @return geoTableSelect
     * @since Version 6.0.0
     */
    public function getTableSelect($for = self::SELECT_BROWSE, $copy = false)
    {
        if (!$for) {
            //didn't specify what it was for...
            return null;
        }
        if (!isset($this->_tableSelects[$for])) {
            $tableSelect = new geoTableSelect();

            switch ($for) {
                case self::SELECT_BROWSE:
                    $tableSelect->from(geoTables::classifieds_table);
                    break;

                case self::SELECT_SEARCH:
                    $tableSelect->from(geoTables::classifieds_table);
                    break;

                case self::SELECT_FEED:
                    $columns = array('`id`','`title`','`description`','`date`','`category`');
                    $tableSelect->from(geoTables::classifieds_table, $columns);
                    break;

                default:
                    //give them back an empty table select, if not one of the
                    //"build in" "fors".

                    break;
            }
            $this->_tableSelects[$for] = $tableSelect;
        }
        return ($copy) ? clone $this->_tableSelects[$for] : $this->_tableSelects[$for];
    }

    /**
     * Checks to see if the given table exists.
     * @param String $tableName
     * @return Boolean true if table exists, false otherwise.
     */
    public function tableExists($tableName)
    {
        $this->init();
        $result = $this->Execute("show tables");
        while ($row = $result->FetchRow()) {
            if (in_array($tableName, $row)) {
                return true;
            }
        }
        return false;
    }

    /**
     * checks to seei f the given table column exists.
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     * @since Version 5.2.0
     */
    public function tableColumnExists($tableName, $columnName)
    {
        $this->init();
        $result = $this->Execute("SHOW COLUMNS FROM $tableName");
        if (!$result) {
            //probably table does not exist?
            return 0;
        }
        while ($row = $result->FetchRow()) {
            if (in_array($columnName, $row)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Quote values into a string, replacing the ? found in the string with the
     * quoted values, suitable to be used as part of a query
     *
     * @param string $query The query part to insert the quoted values into, something like "var1 = ?"
     * @param array|string $values The value(s) to be quoted
     * @param array|string $types The type(s) for each value, assumes
     *   DataAccess::TYPE_STRING if none is specified.
     * @return string The query with quoted values inserted
     * @since Version 5.2.0
     */
    public function quoteInto($query, $values, $types = null)
    {
        //force query to be string for sanity
        $query = '' . $query;

        //allow string to be passed in for values, will be turned into array
        if (!is_array($values)) {
            $values = array($values);
        }

        //allow string to be passed in for types, will be turned into array
        if ($types !== null && !is_array($types)) {
            $types = array($types);
        }

        if (strpos($query, '?') === false) {
            //noting to quote into it
            return $query;
        }
        //loop through each value and replace the next ? with the quoted value
        foreach ($values as $key => $value) {
            //force value
            $type = ($types !== null && isset($types[$key])) ? $types[$key] : self::TYPE_STRING;
            switch ($type) {
                case self::TYPE_INT:
                    $value = (int)$value;
                    break;

                case self::TYPE_BOOL:
                    $value = ($value) ? 1 : 0;
                    break;

                case self::TYPE_FLOAT:
                    //round it to get rid of weird floating point innacuracies
                    $value = round((float)$value, 6);
                    break;

                case self::TYPE_STRING_TODB:
                    //push it through todb
                    $value = geoString::toDB($value);
                    //break ommited on purpose

                case self::TYPE_STRING:
                    //break ommited on purpose

                default:
                    //force it to be string
                    $value = '' . $value;
                    //quote it
                    $value = $this->qstr($value);
                    break;
            }
            $pos = strpos($query, '?');
            if ($pos === false) {
                //no more ? found
                break;
            }
            //replace the next ? found with the quoted value
            $query = substr($query, 0, $pos) . $value . substr($query, ($pos + 1));
        }
        return $query;
    }
}

/**
 * NOT FULLY IMPLEMENTED!  Do not use this class yet as it is not finished and
 * may change drastically before we are done with it, or may even be removed.
 *
 * @package System
 * @since Version 5.2.0
 */
class geoTable
{
    /**
     * The table name
     * @var string
     */
    protected $_name;

    /**
     * Meta data about the table
     * @var array
     */
    protected $_metadata;

    /**
     * Get a select object specific for this table that can be used to get a result set.
     *
     * @return geoTableSelect
     */
    public function select()
    {
        return new geoTableSelect($this->_name);
    }

    /**
     * Get rowset for the select data specified.
     * @param geoTableSelect $select
     * @return geoTableRowset
     */
    public function fetchAll(geoTableSelect $select)
    {
        return new geoTableRowset($select);
    }

    /**
     * Not implemented!
     * @param geoTableSelect $select
     */
    public function fetchRow(geoTableSelect $select)
    {
    }
}

/**
 * NOT FULLY IMPLEMENTED!  Do not use this class yet as it is not finished and
 * may change drastically before we are done with it, or may even be removed.
 *
 * @package System
 * @since Version 5.2.0
 */
class geoTableRowset implements Iterator
{
    /**
     * Row set
     * @internal
     */
    private $_rs;

    /**
     * The class for the row
     * @var string
     * @internal
     */
    protected $_rowClass = 'geoTableRow';

    /**
     * Constructor for geoTableRowset
     * @param geoTableSelect $select
     */
    public function __construct(geoTableSelect $select)
    {
        if ($select) {
            $this->_rs = DataAccess::getInstance()->Execute('' . $select);
        }
    }

    /**
     * Fetch row.  Not fully implemented.
     *
     * @return boolean|Object
     */
    public function fetchRow()
    {
        if (!$this->_rs) {
            return false;
        }

        return new $this->_rowClass($this->_rs->FetchRow());
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        if (!$this->_rs) {
            return;
        }
        $this->_rs->MoveFirst();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        if (!$this->_rs) {
            return false;
        }

        return new $this->_rowClass($this->_rs->fields);
    }
    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->_rs->CurrentKey();
    }
    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next()
    {
        $this->_rs->MoveNext();
    }
    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid()
    {
        return !$this->_rs->EOF;
    }
    /**
     * Converts to string for display or whatever
     * @return string
     */
    public function __toString()
    {
        return '' . $this->_rs;
    }
}

/**
 * NOT FULLY IMPLEMENTED!  Do not use this class yet as it is not finished and
 * may change drastically before we are done with it, or may even be removed.
 *
 * @package System
 * @since Version 5.2.0
 */
class geoTableRow
{
    /**
     * internal
     * @internal
     */
    private $_data;

    /**
     * Constructor
     * @param unknown $data
     */
    public function __construct($data)
    {
        if (!isset($this->_data) && $data) {
            $this->_data = $data;
        }
    }

    /**
     * Not implemented
     */
    public function save()
    {
        //TODO: Implement
    }

    /**
     * Magic method
     * @return string
     */
    public function __toString()
    {
        return print_r($this->_data, 1);
    }
}

/**
 * Class used to generate a select query, useful when multiple areas want to
 * affect a single query, for instance when generating a search query and there
 * are addons involved.
 *
 * @package System
 * @since Version 5.2.0
 */
class geoTableSelect
{
    /**
     * Use INNER JOIN when joining a table, this is most common type.  Pass this
     * in for appropriate var in join() method.
     * @var string
     */
    const JOIN_INNER = 'inner';

    /**
     * Use LEFT JOIN when joining a table.  Pass this in for appropriate var in
     * join() method.
     * @var string
     */
    const JOIN_LEFT = 'left';

    /**
     * Vars used internally
     * @internal
     */
    private $_from = array(), $_where = array(), $_orWhere = array(),
        $_group = array(), $_order = array(), $_count = 0, $_offset = 0;

    /**
     * Create a new table select object
     * @param string $table The table the select is being performed on
     */
    public function __construct($table = null)
    {
        if ($table !== null) {
            $this->from($table);
        }
    }

    /**
     * Add another check to the WHERE on the table select, something like "var='value'".
     *
     * This can be called multiple times, each one will be joined together by AND.
     *
     * Note:  This does NOT quote the values for you, you must have everything quoted.
     *
     * @param string $expression
     * @param string $named (Optional) If specified, will use the name given to keep track of
     *   the particular where clause, so that it can be changed later, or even removed
     *   if needed.
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function where($expression, $named = null)
    {
        $expression = trim($expression);

        if (!strlen($expression)) {
            //if nothing being added, nothing to add
            if ($named !== null && isset($this->_where[$named])) {
                //remove a query that was previously named.
                unset($this->_where[$named]);
            }
            return $this;
        }

        //surround each where with ()
        $expression = '(' . $expression . ')';

        if (in_array($expression, $this->_where)) {
            //already added this exact where clause, don't need same one twice,
            //even if it is named
            return $this;
        }
        if ($named !== null) {
            //add the where to the array of wheres to use, with a named key
            $this->_where[$named] = $expression;
        } else {
            //add the where to the array of wheres to use
            $this->_where[] = $expression;
        }

        //return this to allow method chaining
        return $this;
    }

    /**
     * Whether or not the tableSelect has any where clauses added to it yet,
     * useful to see if something has added a filter to browsing listings for
     * example
     *
     * @return bool
     */
    public function hasWhere()
    {
        return $this->_has('_where');
    }

    /**
     * Gets the requested named where clause, or all where clauses if no specific
     * name is passed in, or false if requested named clause does not exist.
     *
     * Useful to make "manipulation" of queries easier.
     *
     * @param string $name If specified, the name of a where clause to retrieve
     * @return bool|string|array
     * @since Version 6.0.4
     */
    public function getWhere($name = null)
    {
        return $this->_get('_where', $name);
    }

    /**
     * Way to add several things that should be put together using "OR"...
     * For instance, could make this call:
     * $query->orWhere('featured_ad=1','featured')->orWhere('featured_ad_2=1','featured');
     *
     * The above would put all the same "named" OR's together like so:
     *  ... AND ((featured_ad=1) OR (featured_ad_2=1)) AND ...
     *
     * @param string $expression
     * @param string $named Unlike the normal where(), this one is required, in
     *   order to tell which "expressions" go together.
     * @param bool $reset (optional) If set to true, will remove any previously added
     *   OR statements that use the same named value before adding this new one
     */
    public function orWhere($expression, $named, $reset = false)
    {
        if ($named === null) {
            //can't add without knowing the "or" name...
            return $this;
        }

        if ($reset) {
            //reset the previously set ors
            unset($this->_orWhere[$named]);
        }

        $expression = trim($expression);

        if (!strlen($expression)) {
            //if nothing being added, nothing to add

            return $this;
        }

        //surround each where with ()
        $expression = '(' . $expression . ')';

        if (isset($this->_orWhere[$named]) && in_array($expression, $this->_orWhere[$named])) {
            //Don't need to add same exact expression multiple times
            return $this;
        }

        //add the where to the array of or wheres to use, with a named key
        $this->_orWhere[$named][] = $expression;


        //return this to allow method chaining
        return $this;
    }

    /**
     * Whether or not the tableSelect has any orWhere clauses added to it yet,
     * useful to see if something has added a filter to browsing listings for
     * example
     *
     * @return bool
     * @since Version 6.0.4
     */
    public function hasOrWhere()
    {
        return $this->_has('_orWhere');
    }

    /**
     * Gets the requested named orWhere clause, or all orWhere clauses if no specific
     * name is passed in, or false if requested named clause does not exist.
     *
     * Useful to make "manipulation" of queries easier.
     *
     * @param string $name If specified, the name of a where clause to retrieve
     * @return bool|array
     * @since Version 6.0.4
     */
    public function getOrWhere($name = null)
    {
        return $this->_get('_orWhere', $name);
    }

    /**
     * Add something to group by, for example "table_name.column".
     *
     * @param string $groupBy
     * @param bool $reset If true, will clear any group by's previously set.
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function group($groupBy, $reset = false)
    {
        $groupBy = trim($groupBy);

        if ($reset) {
            $this->_group = array();
        }

        if (!strlen($groupBy)) {
            //sanity check
            return $this;
        }
        //add the group to the array of groups to use
        $this->_group[] = $groupBy;
        //return this to allow method chaining
        return $this;
    }

    /**
     * Whether or not the tableSelect has any where clauses added to it yet,
     * useful to see if something has added a filter to browsing listings for
     * example
     *
     * @return bool
     * @since Version 6.0.4
     */
    public function hasGroup()
    {
        return $this->_has('_group');
    }

    /**
     * Gets the full array of "group by" parts of the query
     *
     * @return array
     * @since Version 6.0.4
     */
    public function getGroup()
    {
        //Note:  group parts are not "named" so it's all or nothing
        return $this->_get('_group');
    }

    /**
     * Add something to order by, for example "column ASC".
     *
     * @param string $orderBy
     * @param bool $reset If true, will clear any orders previously set.
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function order($orderBy, $reset = false)
    {
        $orderBy = trim($orderBy);

        if ($reset) {
            //reset order by
            $this->_order = array();
        }

        if (!strlen($orderBy)) {
            //sanity check
            return $this;
        }

        //add the where to the array of wheres to use
        $this->_order[] = $orderBy;
        //return this to allow method chaining
        return $this;
    }

    /**
     * Whether this table select has any order by's added to it yet.
     *
     * @return bool
     */
    public function hasOrder()
    {
        return $this->_has('_order');
    }

    /**
     * Gets the full array of order by parts added to the query so far.
     *
     * @return array
     * @since Version 6.0.4
     */
    public function getOrder()
    {
        //Note:  order by parts are not "named" so it's all or nothing
        return $this->_get('_order');
    }

    /**
     * Add limit to select, by specifying the number of rows to return and optionally
     * the row offset.  To "remove" a previously added limit, just specify 0 for
     * the count.
     *
     * If only the first var is passed in and not 0, it is used as the row count limit.  If
     * both vars are specified (and not 0), the first is used as the offset and the
     * second is used as the number of rows to return.
     *
     * @param int $var1 The count
     * @param int $var2 The offset
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function limit($var1, $var2 = 0)
    {
        $this->_count = (int)$var1;
        $this->_offset = (int)$var2;

        //return this to allow method chaining
        return $this;
    }

    /**
     * Whether or not there is a limit currently set on the query.
     *
     * @return boolean
     * @since Version 6.0.4
     */
    public function hasLimit()
    {
        //check is little different than other stuff
        return ($this->_count > 0 || $this->_offset > 0);
    }

    /**
     * Gets an array for what the limits are set to.
     *
     * @return array
     * @since Version 6.0.4
     */
    public function getLimit()
    {
        //a little different than normal gets...
        return array (
            $this->_count, $this->_offset,
        );
    }

    /**
     * Set what table and initial columns to select from
     *
     * @param string|array $table Either the string table name, or an associative
     *   array like array ($table_alias => $table_name)
     * @param string|array $columns  A string or array of strings for all the columns
     *   to select, additional columns can always be added later using {@see geoTableSelect::columns},
     *   be sure to use empty array if you plan to specify all columns later.  Can specify
     *   column "AS" by putting alias in array key like array(column_alias => column_name)
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function from($table, $columns = '*')
    {
        if (!$table) {
            //sanity check
            return $this;
        }

        if (!is_array($columns)) {
            $columns = array($columns);
        }

        //Index 0 is always the "primary" table, more are added via join()
        $this->_from [0] = array (
            'table' => $table,
            'columns' => $columns,
            'join_type' => 'primary',//doesn't matter on main one
            'on' => null//doesn't matter on main one
        );

        return $this;
    }

    /**
     * Gets the main table being used for this query, as passed into $query->from()
     *
     * @return string
     * @since Version 6.0.4
     */
    public function getTable()
    {
        return $this->_from[0]['table'];
    }

    /**
     * Add or replace a table join to the select.  If it finds another "join"
     * previously added, using the same table, it will replace that join with
     * the parameters.
     *
     * This will not have an effect on the "primary" table and will not work to
     * add a join that has a table matching the primary table.
     *
     * Note that "replace" behavior added in version 6.0.6, previously it would
     * add duplicate joined tables and result in database query error.
     *
     * @param string|array $table Either the string table name, or an associative
     *   array like array ($table_alias => $table_name)
     * @param string|array $on What to use for the ON clause, can be simple string
     *   or an array of strings (that will be stuck together using AND)
     * @param string|array $columns  A string or array of strings for all the columns
     *   to select, additional columns can always be added later using {@see geoTableSelect::columns},
     *   be sure to use empty array if you plan to specify all columns later.  Can specify
     *   column "AS" by putting alias in array key like array(column_alias => column_name)
     * @param string $joinType The type, use one of geoTableSelect::JOIN_* constants.
     * @param bool $remove If true, will remove the joined table (does NOT work on
     *   "main" table).  Param first *working* in version 7.1.0
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function join($table, $on, $columns = array(), $joinType = self::JOIN_INNER, $remove = false)
    {
        if (!$table || $table === $this->_from[0]['table']) {
            //sanity check, don't work with empty table or primary table.
            return $this;
        }

        if (!is_array($on)) {
            $on = array($on);
        }

        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $from = array (
            'table' => $table,
            'columns' => $columns,
            'join_type' => $joinType,
            'on' => $on
        );

        $newFrom = array();
        foreach ($this->_from as $key => $existing) {
            if ($existing['table'] === $table) {
                if ($remove) {
                    //actually, just removing the table, which we do by not
                    //adding it back to newFrom
                    continue;
                }
                //this is the one to replace...
                $this->_from[$key] = $from;
                //return now since we're done
                return $this;
            }
            if ($remove) {
                //re-add this table since it is not the one we want to remove
                $newFrom[] = $existing;
            }
        }
        if ($remove) {
            //We just re-assembled the _from minus the entry for the table that
            //should be removed.  So set the _from to the new from.
            $this->_from = $newFrom;
            return $this;
        }

        //Did not find any existing table joins with same table so add new join
        //Index 0 is always the "primary" table, more are added via join()
        $this->_from[] = $from;

        return $this;
    }

    /**
     * Add additional columns to be selected for the given table (or main table if
     * no table specified)
     *
     * @param string|array $columns A string or array of strings for all the columns
     *   to select, additional columns can always be added later using {@see geoTableSelect::columns},
     *   be sure to use empty array if you plan to specify all columns later.  Can specify
     *   column "AS" by putting alias in array key like array(column_alias => column_name)
     * @param string $table The table name getting the columns for
     * @param bool $reset If true, will clear any columns previously set for that table
     * @return geoTableSelect Returns itself to allow method chaining
     */
    public function columns($columns, $table = null, $reset = false)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
        $from_key = 0;
        if ($table !== null) {
            //figure out what from key to use
            foreach ($this->_from as $fkey => $from) {
                if (is_array($from['table']) && (key($from['table']) == $table || current($from['table']) == $table)) {
                    //we found it!
                    $from_key = $fkey;
                    break;
                } elseif ($from['table'] == $table) {
                    //found the matching table
                    $from_key = $fkey;
                    break;
                }
            }
        }
        $current_columns = ($reset) ? array() : (array)$this->_from[$from_key]['columns'];
        foreach ($columns as $column_key => $column_name) {
            if (geoString::isInt($column_key)) {
                $current_columns[] = $column_name;
            } else {
                $current_columns[$column_key] = $column_name;
            }
        }
        $this->_from[$from_key]['columns'] = $current_columns;
        return $this;
    }

    /**
     * Gets a copy of the current query, but altered specifically for getting
     * the count of rows:  limit is removed, and the only column it gets is
     * for COUNT(*)
     *
     * @return geoTableSelect
     */
    public function getCountQuery()
    {
        $query = clone $this;

        //reset any orders or limits previously set since don't want those for
        //a query that just counts the results.
        $query->order('', true)
            ->limit(0);

        //Set main column to get to "COUNT(*)", and remove rest of columns
        $keys = array_keys($query->_from);
        foreach ($keys as $key) {
            $columns = ($key == 0) ? array('COUNT(*)') : array();
            $query->_from[$key]['columns'] = $columns;
        }

        return $query;
    }

    /**
     * Magic method, when object is used as a string, it generates an SQL query
     * according to everything that has been set for the select object.
     * @return string Sql query
     */
    public function __toString()
    {
        if (!isset($this->_from[0]['table']) || !$this->_from[0]['table']) {
            //sanity check
            return '';
        }

        $sql = "SELECT ";

        $column_parts = array();
        $from_parts = array();

        foreach ($this->_from as $from) {
            $table_key = (is_array($from['table'])) ? key($from['table']) : $from['table'];

            foreach ($from['columns'] as $column_key => $column_name) {
                if (strpos($column_name, '(') === false && strpos($column_name, '.') === false) {
                    //This is NOT an expression and does not yet have ".", add tbl name
                    $column_name = $table_key . '.' . $column_name;
                }

                if (!geoString::isInt($column_key)) {
                    $column_name .= ' AS ' . $column_key;
                }
                $column_parts[] = $column_name;
            }

            //figure out from
            switch ($from['join_type']) {
                case 'primary':
                    //special case, this is first table, don't need to add join type
                    $from_str = '';
                    break;

                case self::JOIN_LEFT:
                    $from_str = 'LEFT JOIN ';
                    break;

                case self::JOIN_INNER:
                    //break ommitted on purpose
                default:
                    $from_str = 'INNER JOIN ';
                    break;
            }

            if (is_array($from['table'])) {
                //using array(table_alias => table_name)
                $table = $from['table'];
                reset($table);
                $from_str .= current($table) . ' AS ' . key($table);
            } else {
                //simple string for table
                $from_str .= $from['table'];
            }
            if ($from['on'] && is_array($from['on'])) {
                $from_str .= ' ON ' . implode(' AND ', $from['on']);
            } elseif ($from['on']) {
                $from_str .= ' ON ' . $from['on'];
            }
            $from_parts [] = $from_str;
        }
        $sql .= implode(', ', $column_parts) . "\n\tFROM " . implode("\n\t\t", $from_parts);

        $where = $this->_where;

        if ($this->_orWhere) {
            //add all the OR's to the thingy
            foreach ($this->_orWhere as $name => $orWhere) {
                $where[$name] = "(" . implode(' OR ', $orWhere) . ")";
            }
        }

        if ($where) {
            $sql .= "\n\tWHERE " . implode(' AND ', $where);
        }

        if ($this->_group) {
            $sql .= "\n\tGROUP BY " . implode(', ', $this->_group);
        }

        if ($this->_order) {
            $sql .= "\n\tORDER BY " . implode(', ', $this->_order);
        }

        if ($this->_count || $this->_offset) {
            $sql .= "\n\tLIMIT {$this->_count}";
            if ($this->_offset) {
                $sql .= ", {$this->_offset}";
            }
        }

        return $sql;
    }

    /**
     * Whether or not the tableSelect has any of the whatever's...
     *
     * @param string $param
     * @return bool
     * @internal
     */
    private function _has($param)
    {
        return !empty($this->$param);
    }

    /**
     * Gets the requested named thing, or thingy things if no specific
     * name is passed in, or false if requested named clause does not exist.
     *
     * Useful to make "manipulation" of queries easier.
     *
     * @param string $param
     * @param string $name If specified, the name of a where clause to retrieve
     * @return bool|string|array
     * @internal
     */
    private function _get($param, $name = null)
    {
        if ($name === null) {
            //return full array
            return $this->$param;
        }
        $v = $this->$param; //workaround for weird thing where php doesn't like $this->$param[$name]

        if (isset($v[$name])) {
            return $v[$name];
        }
        //could not find name requested, return boolean false
        return false;
    }
}
