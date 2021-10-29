<?php

//this is encoded, don't need a header.

require_once(CLASSES_DIR . 'rpc/XMLRPC.class.php');

/**
 * @package Update
 * @todo Add backup/restore functionality
 */
class geoUpdateFactory
{
    public static function getReleaseDate()
    {
        //When you update versions.php be sure to update the version number here and the date of release!

        $latest = '18.02.0';
        //set to time for release.
        $released = 1519771709;

        require 'versions/versions.php';

        if (!isset($versions[$latest]) || $versions[$latest]['to'] != 'latest') {
            //So we don't forget to update the above
            if (defined('IAMDEVELOPER')) {
                die('<strong style="color: red;">ERROR!!!!</strong>  You forgot to update <strong>$latest</strong> and <strong>$released</strong> in <strong>updateFactory.php</strong>!
					Search that file for those vars (will be near top) and update them.<br /><br />
					If the version is released today, the $released should be set to <strong>' . time() . '</strong>
					');
            }
            die(
                '<strong>Config Error:</strong>  The versions.php and updateFactory.php files have a file version mismatch. Please contact Support.<br /><br /><strong>' . time() . '</strong>'
            );
        }
        return $released;
    }


    private $_db, $pc;
    private $_oldProduct;
    private $_upgrades = array();
    private $_currentUpgradeIndex;
    private $_queries = array();
    private $_interHTML = '';
    public $tplVars = array();
    public $step_text;
    public $header_text; //add additional text to the head tag in the template.

    const licenseChecksDev = false;//DO NOT TOUCH!  Auto-changed by demo update
    //const licenseChecksDev = 'internal_coolness';//DO NOT TOUCH!  Auto-changed by demo update

    /**
     * Constructor.. don't bother with Singleton, since update is so simple, no
     * need for singleton stuff.
     */
    public function __construct()
    {
        //set up main template.
        $this->step_text = '';
        $this->header_text = '';
        require_once '../config.default.php';
        if (!defined('PHP5_DIR')) {
            define('PHP5_DIR', 'php5_classes/');
        }
        require_once(CLASSES_DIR . 'adodb/adodb.inc.php');
        require_once CLASSES_DIR . PHP5_DIR . 'products.php';
        $this->pc = geoPC::getInstance('geoUpdateFactory');
    }
    /**
     * Should only be used internaly.  If a database connection
     * has not been made yet, it makes one.
     */
    private function _connectDB()
    {
        if (!(isset($this->_db) && is_object($this->_db))) {
            include_once(CLASSES_DIR . 'adodb/adodb.inc.php');
            include(GEO_BASE_DIR . 'config.default.php');
            $this->_db =& ADONewConnection($db_type);

            if (isset($persistent_connections) && $persistent_connections) {
                if (!$this->_db->PConnect($db_host, $db_username, $db_password, $database)) {
                    echo 'Could not connect to database (persistent connection).';
                    exit;
                }
            } else {
                if (!$this->_db->Connect($db_host, $db_username, $db_password, $database)) {
                    echo "Could not connect to database.";
                    exit;
                }
            }
            //fix SQL strict mode.
            if (isset($strict_mode) && $strict_mode) {
                $this->_db->Execute('SET SESSION sql_mode=\'\'');
            }
            //fix db connection charset
            if (isset($force_db_connection_charset) && strlen(trim($force_db_connection_charset)) > 0) {
                $this->_db->Execute("SET NAMES '$force_db_connection_charset'");
                //$this->_db->Execute("SET CHARACTER SET $force_db_connection_charset");
            }
        }
    }

    /**
     * Checks to see if the given table exists.
     * @param String $tableName
     * @return Boolean true if table exists, false otherwise.
     */
    public function tableExists($tableName)
    {
        $this->_connectDB();
        $result = $this->_db->Execute("show tables");
        while ($row = $result->FetchRow()) {
            if (in_array($tableName, $row)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check to see if the given field exists in the given table.
     * @param String $tableName
     * @param String $fieldName
     * @return Boolean true if field exists in the table, false otherwise.
     */
    public function fieldExists($tableName, $fieldName)
    {
        $this->_connectDB();
        $result = $this->_db->Execute("show columns from $tableName");
        if (!$result) {
            return false;
        } else {
            while ($row = $result->FetchRow()) {
                if (in_array($fieldName, $row)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return String Current version as stored in the database.
     */
    public function getCurrentVersion()
    {
        $this->_connectDB();
        $sql = 'SELECT `db_version` FROM `geodesic_version`';
        $result = $this->_db->Execute($sql);
        if (!$result) {
            //probably really old version
            return 'unknown';
        }
        $row = $result->FetchRow();
        return $row['db_version'];
    }
    /**
     * Stores all the needed data for the progress of the upgrade
     * into the database, thus serializing the upgrade progress...
     */
    public function serialize()
    {
        $this->_connectDB();
        $incomplete_upgrade = false;
        if (count($this->_upgrades)) {
            $current = $this->getCurrentVersion();
            $current_i = 0;
            if (!$this->tableExists('geodesic_upgrade_progress')) {
                $this->createUpgradeTables();
            }
            foreach ($this->_upgrades as $index => $data) {
                if ($data['status'] == 2 && $index == ($current_i + 1)) {
                    //successfully updated to this version.
                    $from = $data['from'];
                    $to = $data['to'];
                    $current_order_id = $index;
                    $current = $data['to'];
                    $current_i = $index;
                }
                if ($data['status'] == 1 || $data['status'] == -1) {
                    $incomplete_upgrade = true;
                }
                $sql = "REPLACE INTO `geodesic_upgrade_progress` SET `order_id` = $index, `from` = '{$data['from']}', `to` = '{$data['to']}', `status` = '{$data['status']}'";
                //$query_data = array ($index, $data['to'],$data['status']);
                $result =& $this->_db->Execute($sql);
                if (!$result) {
                    $message = 'Could not update the upgrade progress in the table `geodesic_upgrade_progress`.  <strong>DB Error</strong>: ' . $this->_db->ErrorMsg();
                    $this->criticalError($message, __line__);
                }
            }
            if ($current !== $this->getCurrentVersion()) {
                //update current version
                $this->updateCurrentVersion($current);
            }
        }

        if ($incomplete_upgrade && count($this->_queries)) {
            //record queries in database.

            //make sure query table exists
            if (!$this->tableExists('geodesic_upgrade_queries')) {
                $this->createUpgradeTables();
            }
            //clear the old entries
            /*$sql = 'TRUNCATE TABLE `geodesic_upgrade_progress`';
            $result = $this->_db->Execute($sql);
            if (!$result){
                $message = 'Could not truncate the table `geodesic_upgrade_queries`.  <strong>DB Error</strong>: '.$this->_db->ErrorMsg();
                $this->criticalError($message, __line__);
            }*/
            foreach ($this->_queries[$current_order_id] as $order_id => $queries) {
                foreach ($queries as $query_id => $data) {
                    $sql = 'REPLACE INTO `geodesic_upgrade_queries` SET `query_id` = ?, `order_id` = ?, strict` = ?, `status` = ?, `sql` = ?';
                    $query_data = array ($query_id, $order_id, $data['strict'], $data['status'], $data['sql']);
                    $result = $this->_db->Execute($sql, $query_data);
                    if (!$result) {
                        $message = 'Could not update the upgrade query progress in the table `geodesic_upgrade_queries`.  <strong>DB Error</strong>: ' . $this->_db->ErrorMsg();
                        $this->criticalError($message, __line__);
                    }
                }
            }
        } elseif (!$incomplete_upgrade && $this->tableExists('geodesic_upgrade_queries')) {
            //the upgrade looks like it completed, so empty out the queries table.
            $sql = 'TRUNCATE `geodesic_upgrade_queries`';
            $result = $this->_db->Execute($sql);
            if (!$result) {
                //its probably ok that it messed up... probably...
                //but still report it
                echo 'Internal Notice: The upgrade queries table `geodesic_upgrade_queries` was unable to be emptied.  DB Error Message: ' . $this->_db->ErrorMsg() . '<br />';
            }
        }
    }
    /**
     * Gets the upgrade progress from the database and re-constructs the
     * upgrade progress data, thus un serializing the data..
     */
    public function unSerialize()
    {
        if (!$this->tableExists('geodesic_upgrade_progress') || !$this->tableExists('geodesic_upgrade_queries')) {
            return false;
        }
        include('versions/versions.php');
        //get versions from database.
        $this->_connectDB();
        $sql = 'SELECT * FROM `geodesic_upgrade_progress` ORDER BY `order_id`';
        $result = $this->_db->Execute($sql);
        if (!$result) {
            $message = 'Error:  Could not retrieve upgrade progress from the database.  <strong>DB Error</strong>: ' . $this->_db->ErrorMsg();
            $this->criticalError($message, __line__);
        }
        if ($result->RecordCount() == 0) {
            //no upgrades in the database...
            return false;
        }
        $this->_upgrades = array();
        $incomplete = false;
        $highest_i = 0;
        $current_version = $this->getCurrentVersion();
        //make sure it is not already up to date.
        if ($versions[$current_version]['to'] == 'latest') {
            $finished_upgrades = true;
        } else {
            $finished_upgrades = false;
        }
        while ($row = $result->FetchRow()) {
            //verify that the from does go to the correct to.
            if (!$finished_upgrades && !(isset($versions[$row['from']] ['to']) && $versions[$row['from']]['to'] == $row['to'])) {
                $message = 'Progress data in database is corrupt (entries in `geodesic_upgrade_progress` do not match upgrades array).  Please contact Geodesic Support.';
                $this->criticalError($message, __line__);
            }
            if ($row['status'] == 1 || $row['status'] == -1) {
                //upgrade is incomplete, so also get the
                //sql queries.
                $incomplete = true;
            }
            if ($row['from'] == $current_version) {
                $this->_currentUpgradeIndex = $row['order_id'];
            }
            $this->_upgrades[$row['order_id']] = array (
                'from' => $row['from'],
                'to' => $row['to'],
                'folder' => $versions[$row['from']]['folder'],
                'status' => $row['status']
            );
        }

        $sql = 'SELECT * FROM `geodesic_upgrade_queries` ORDER BY `query_id`';
        $result = $this->_db->Execute($sql);
        if (!$result) {
            $message = 'Could not get incomplete query data from the database table `geodesic_upgrade_queries`.  <strong>DB Error</strong>: ' . $this->_db->ErrorMsg();
            $this->criticalError($message, __line__);
        }
        while ($row = $result->FetchRow()) {
            $this->_queries [$row['order_id']] [$row['query_id']] = array (
                'strict' => $row['strict'],
                'status' => $row['status'],
                'sql' => $row['sql']    //at this point, sql query is not known.
            );
        }
        if (isset($this->_currentUpgradeIndex) && !$finished_upgrades) {
            $this->_upgrades[$this->_currentUpgradeIndex]['status'] = 1;
        }

        return true;
    }
    /**
     * Not used or implemented yet.
     */
    public function prereqsMet()
    {
        //not fully implemented, and may not finish
        //unless needed for later version.
        if (count($this->_upgrades)) {
            while (list(, $obj) = each($this->_upgrades)) {
                if (!$obj->prereqsMet()) {
                    return false;
                }
            }
        }
    }
    /**
     * Not used or fully implemented yet.
     */
    public function getPrereqs()
    {
        //not fully implemented, and may not finish
        //unless needed for later version.
        if (count($this->_upgrades)) {
            $prereqs = array();
            while (list(, $obj) = each($this->_upgrades)) {
                $prereqs = array_merge($prereqs, $obj->getPrereqs());
            }
        }
    }
    /**
     * Removes the tables used for storing upgrade progress, for use
     * when an upgrade is finished.
     */
    public function removeUpgradeTables()
    {
        $this->_connectDB();
        $sql = 'DROP TABLE IF EXISTS `geodesic_upgrade_progress`';
        $result =& $this->_db->Execute($sql);
        if (!$result) {
            $error = 'Table `geodesic_upgrade_progress` not able to be dropped.  <strong>DB Error Message:</strong>' . $this->_db->ErrorMsg();
            $this->criticalError($error);
        }
        $sql = 'DROP TABLE IF EXISTS `geodesic_upgrade_queries`';
        $result =& $this->_db->Execute($sql);
        if (!$result) {
            $error = 'Table `geodesic_upgrade_queries` not able to be dropped.  <strong>DB Error Message:</strong>' . $this->_db->ErrorMsg();
            $this->criticalError($error);
        }
    }

    public function clearCache($allCache = false)
    {
        //clear templates_c first, use Smarty to do it.
        $smarty = new Smarty();
        $smarty->compile_dir = GEO_BASE_DIR . 'templates_c';
        $smarty->template_dir = GEO_BASE_DIR . 'upgrade/templates';

        //clear the compiled templates
        $smarty->clearCompiledTemplate();
        unset($smarty);
        if (!$allCache) {
            //don't clear the rest of the cache yet...
            return;
        }
        //clear the _geocache/ folder
        //require file since autoload doesn't run on updates
        require_once CLASSES_DIR . PHP5_DIR . 'Cache.class.php';

        geoCache::initSettings();
        geoCache::clearCache();
        //write the settings file
        geoCache::writeCache();
    }

    /**
     * Creates tables in database necessary for serializing the
     * upgrade progress data
     * @param (optional)Boolean $start_fresh If set to true, will first
     *  drop the upgrade tables before creating them.
     */
    public function createUpgradeTables($start_fresh = false)
    {
        $this->_connectDB();

        $error_message = 'A table was not able to be created, please check settings in config.php and
make sure the database user has sufficient privilages to create, alter, and drop tables for the given database.
<br /><br />
Once you have made the needed changes, come back to this page and refresh.  Contact Geodesic Support with the error reported below, if you need further assistance.
<br /><br />';
        $error = false;

        if ($start_fresh) {
            //drop the tables before creating them.
            $sql = 'DROP TABLE IF EXISTS `geodesic_upgrade_progress`';
            $result =& $this->_db->Execute($sql);
            if (!$result) {
                $error = __line__;
                $error_message .= 'Error when attempting to drop table `geodesic_upgrade_progress`.  <strong>DB Error Message:</strong>' . $this->_db->ErrorMsg();
            }
            if (!$error) {
                $sql = 'DROP TABLE IF EXISTS `geodesic_upgrade_queries`';
                $result =& $this->_db->Execute($sql);
                if (!$result) {
                    $error = __line__;
                    $error_message .= 'Error when attempting to drop table `geodesic_upgrade_queries`.  <strong>DB Error Message:</strong>' . $this->_db->ErrorMsg();
                }
            }
        }
        if (!$error) {
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_upgrade_progress` (
				`order_id` INT( 5 ) NOT NULL DEFAULT '0',
				`from` VARCHAR( 128 ) NOT NULL DEFAULT '1.0',
				`to` VARCHAR( 128 ) NOT NULL DEFAULT '2.0',
				`status` ENUM( '0', '1', '2', '-1' ) NOT NULL DEFAULT '0',
				UNIQUE (
					`order_id`
				)
			)";

            $result = $this->_db->Execute($sql);
            if (!$result) {
                //that aint good!
                $error = __line__;
                $error_message .= 'Error when attempting to create `geodesic_upgrade_progress` table.  <strong>DB Error Message:</strong> ' . $this->_db->ErrorMsg();
            }
        }
        if (!$error) {
            $sql = "CREATE TABLE IF NOT EXISTS `geodesic_upgrade_queries` (
				`query_id` INT( 5 ) NOT NULL DEFAULT '0',
				`order_id` INT( 5 ) NOT NULL DEFAULT '0',
				`strict` TINYINT( 3 ) NOT NULL DEFAULT '0',
				`status` ENUM( '-1', '0', '1' ) NOT NULL DEFAULT '0',
				`sql` TEXT NOT NULL,
				UNIQUE (
					`query_id`
				)
			)";
            $result = $this->_db->Execute($sql);
            if (!$result) {
                $error = __line__;
                $error_message .= 'Error when attempting to create `geodesic_upgrade_queries` table.  <strong>DB Error Message:</strong> ' . $this->_db->ErrorMsg();
            }
        }

        //now check to make sure tables were actually created.
        if (!$error && (!$this->tableExists('geodesic_upgrade_progress') || !$this->tableExists('geodesic_upgrade_queries'))) {
            //there were no errors reported, but one of the tables do not exist...
            $error = __line__;
            $error_message .= 'Upgrade tables were not able to be created, however no DB errors were reported when attempting to create the tables.';
        }
        if ($error) {
            //there was an error!
            $this->criticalError($error_message, $error);
        }
        return true;
    }
    /**
     * This does the guts of the work.  It does pretty much everything except output
     * the page (unless an error is thrown)
     * @return Boolean false if any problems came up, true otherwise.
     */
    final public function factory()
    {
        //Make sure license key is good
        $licenseKey = (isset($_POST['licenseKey'])) ? trim($_POST['licenseKey']) : '';

        $this->tplVars['licenseKey'] = htmlspecialchars(($licenseKey) ? $licenseKey : $this->getLicenseKey());

        //see if we are already at latest.
        include('versions/versions.php');
        $current_version = $this->getCurrentVersion();
        if (!$licenseKey || (self::licenseChecksDev !== 'internal_coolness' && !$this->pc->verifyLicenseForUpdate($licenseKey))) {
            //show license key page
            $this->tplVars['body_tpl'] = 'licenseKey.tpl';
            $this->tplVars['install'] = $this->pc->get_installation_info();
            $this->tplVars['licenseError'] = $this->pc->errors();
            $this->tplVars['must_agree'] = $this->pc->mustAgree();
            $this->step_text = 'License Key';

            //special: save old listing types here (before overwriting the old license key) when upgrading to GeoCore
            $this->geoCore_init_listingTypes();
            return;
        } elseif ($licenseKey && isset($versions[$current_version])) {
            //be sure to save license key, or at least attempt to
            $this->_connectDB();
            $this->_db->Execute("DELETE FROM `geodesic_site_settings` WHERE `setting`='license' OR `setting` = 'license_data'");
            $this->_db->Execute("DELETE FROM `geodesic_site_settings_long` WHERE `setting`='license_data'");

            //insert the new key
            $this->_db->Execute("INSERT INTO `geodesic_site_settings` (`setting`, `value`) VALUES ('license', ?)", array($licenseKey));
        }

        if (isset($_POST['licenseKeyEntered']) && $_POST['licenseKeyEntered'] && $this->tableExists('geodesic_license_log')) {
            //license key was just entered and is valid, so record in the license table
            $this->_db->Execute("INSERT INTO `geodesic_license_log` SET `time`=" . time() . ", `log_type`='notice_remote', `message`='Software Update in progress (from version $current_version), resetting and validating license data for license key ($licenseKey).'");
        }

        if (isset($versions[$current_version]) && $versions[$current_version]['to'] == 'latest' && !($_GET['run'] != 'finish' && isset($versions['beta']) && is_array($versions['beta']))) {
            //This is the cleanup step...
            $this->_upgrades = 'none';
            $this->removeUpgradeTables();
            $this->clearCache(true);
            return true;
        }

        //check to see if the current version is a new version or not.
        if (!isset($versions[$current_version]) && !(isset($versions['beta']) && in_array($current_version, $versions['beta']['beta_versions']))) {
            //this is an older version, so redirect them to the
            //old upgrader.
            //first, make sure the site settings table is created.
            $this->_db->Execute('CREATE TABLE IF NOT EXISTS `geodesic_site_settings` (
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`setting`)
)');
            $this->header_text = '<meta http-equiv="refresh" content="0;url=versions/pre_2.0.10/" />';
            $ver = ($current_version == 'pre') ? 'Unknown' : $current_version;
            $message = 'Loading <a href="versions/pre_2.0.10/">pre - 2.0.10 upgrade routine...</a>.';
            $this->tplVars['body'] = $message;
            $this->display_page();
            exit;
        }
        //get the data from the database.
        $serial = $this->unSerialize();
        //First, see if in the process of doing an upgrade
        if (!$serial) {
            //see if we should be running all of the beta versions...
            if (isset($versions['beta']['beta_versions']) && in_array($current_version, $versions['beta']['beta_versions'])) {
                //this is one of the versions that is part of upgrading to beta,
                //but this is starting a brand new upgrade, so start them from the start.
                $current_version = $versions['beta']['start'];
                //set the current version in db
                $this->updateCurrentVersion($current_version);
            }

            //need to initialize the upgrade.

            $this_version = $versions[$current_version];
            if (strtolower($this_version['to']) == 'latest') {
                $message = 'Software is already upgraded, to version ' . $this_version . '
<br />' . $this->getFinishedLinks();
                $this->removeUpgradeTables();
                $this->clearCache(true);
                //do not go any further, no upgrades to do.
                return true;
            }

            //create the upgrade tables.
            $this->createUpgradeTables(true);

            //go through each upgrade and add it to the upgrades needed.
            $version_index = 1;
            $this_version['from'] = $current_version;
            $this_version['status'] = 0;
            //See if we need to re-run certain updates, if beta
            if (isset($versions[$current_version]['beta'])) {
            }
            while ($this_version['to'] !== 'latest' && key_exists($this_version['to'], $versions)) {
                //add the version.
                $this->_upgrades[$version_index] = $this_version;
                //remember the from version
                $from = $this_version['to'];
                //set the next version
                $this_version = $versions[$from];
                //record the from version, since it isn't part of the
                //array already
                $this_version['from'] = $from;
                $this_version['status'] = 0;
                $version_index++;
            }
            //now serialize, to store them in the database.
            $this->serialize();
            return true;
        }
        //start running upgrades

        $interStatus = true;
        $interFile = 'versions/' . $this->_upgrades[$this->_currentUpgradeIndex]['folder'] . '/interactive.php';
        if (file_exists($interFile)) {
            //include the interactive file, anything it outputs we put in a box to display.
            //whatever it returns, if it's true then we proceed to next step, if false then we do not proceed to next step yet.

            ob_start();
            $interStatus = include($interFile);
            $this->tplVars['interHTML'] = ob_get_contents();
            ob_end_clean();
        }
        if ($interStatus) {
            //Load the queries to be run.
            $this->getQueries();

            //execute the queries.
            $overall = $this->runQueries();

            if ($overall) {
                //if run queries was successful, then update the upgrade.
                $this->_upgrades[$this->_currentUpgradeIndex]['status'] = 2;
            } else {
                //there was some error...
                $this->_upgrades[$this->_currentUpgradeIndex]['status'] = -1;
            }
        } else {
            //set status to 1 indicating we are curently doing stuff
            $this->_upgrades[$this->_currentUpgradeIndex]['status'] = 1;
        }

        //store the results.
        $this->serialize();

        return $overall;
    }
    /**
     * Used in addQuery to keep track of the query number.
     * @var int
     */
    private $_q_index = 1;

    /**
     * Adds a query to the list of queries that should be run for the update.
     *
     * @param string $sql The sql query to run
     * @param $strict If 1 the update will fail if the query fails.
     */
    public function addQuery($sql, $strict)
    {
        $this->_queries[$this->_currentUpgradeIndex][$this->_q_index]['sql'] = $sql;
        $this->_queries[$this->_currentUpgradeIndex][$this->_q_index]['strict'] = (isset($this->_queries[$this->_q_index]['strict'])) ? $this->_queries[$this->_q_index]['strict'] : $strict;
        //set status to 0 (not run), if it is not set already
        $this->_queries[$this->_currentUpgradeIndex][$this->_q_index]['status'] = (isset($this->_queries[$this->_q_index]['status'])) ? $this->_queries[$this->_q_index]['status'] : 0;

        $this->_q_index++;
    }

    /**
     * Loads the queries for the current upgrade into the _queries array.
     * @return Boolean true if it gets through the whole thing. If any critical
     *  errors occur, it stops the script and displays an error message.
     */
    final public function getQueries()
    {
        if (!count($this->_upgrades) || !isset($this->_currentUpgradeIndex)) {
            //there was an error, display the error message and exit.
            $error_message = 'Current upgrade not known in function getQueries(). Debug Info: Upgrade Count: ' . count($this->_upgrades) . ' Current Upgrade Index: ' . print_r($this->_currentUpgradeIndex, 1);
            $this->criticalError($error_message, __line__);
        }
        //if folder=none, then there are not any queries to run for that upgrade.
        if ($this->_upgrades[$this->_currentUpgradeIndex]['folder'] == 'none') {
            $this->_queries[$this->_currentUpgradeIndex] = array();
            return true;
        }

        //first, need to get queries from main.sql
        $main_sql_filename = 'versions/' . $this->_upgrades[$this->_currentUpgradeIndex]['folder'] . '/main.sql';
        if (is_file($main_sql_filename)) {
            //main.sql file exists, so load queries for it.
            $this->splitSqlFile($main_sql_filename);
        } else {
            echo 'File not exist: ' . $main_sql_filename . ' <br />';
        }

        //now, see if there is conditional_sql
        $conditional_sql_filename = 'versions/' . $this->_upgrades[$this->_currentUpgradeIndex]['folder'] . '/conditional_sql.php';
        if (is_file($conditional_sql_filename)) {
            //conditional_sql.php exists, so load queries from it.
            include($conditional_sql_filename);
            if (isset($sql_strict) && is_array($sql_strict)) {
                //array of strict sql queries, meaning if one fails, it stops execution.
                foreach ($sql_strict as $sql) {
                    $this->addQuery($sql, 1);
                }
                //free up memory
                unset($sql_strict);
            }

            if (isset($sql_not_strict) && is_array($sql_not_strict)) {
                //the sql_not_strict array is set
                foreach ($sql_not_strict as $sql) {
                    $this->addQuery($sql, 0);
                }
                //free up memory
                unset($sql_not_strict);
            }
        }

        //now load up text arrays and stuff.
        $arrays_filename = 'versions/' . $this->_upgrades[$this->_currentUpgradeIndex]['folder'] . '/arrays.php';
        if (is_file($arrays_filename)) {
            include $arrays_filename;

            //$insert_font_array array
            if (isset($insert_font_array) && is_array($insert_font_array)) {
                //FONT MANAGEMENT NO MORE!

                //free up memory
                unset($insert_font_array);
            }

            //$upgrade_array array
            if (isset($upgrade_array) && is_array($upgrade_array)) {
                $sql_query = "SELECT `language_id` FROM `geodesic_pages_languages`";
                $language_result = $this->_db->Execute($sql_query);
                if (!$language_result) {
                    die("Error on " . __LINE__ . $this->_db->ErrorMsg());
                }
                $array_keys = array_keys($insert_text_array);
                foreach ($array_keys as $key) {
                    $test_sql_query = "SELECT * FROM `geodesic_pages_messages` WHERE `message_id` = " . $insert_text_array[$key][0];
                    $test_result = $this->_db->Execute($test_sql_query);
                    if (!$test_result) {
                        die("Error on " . __LINE__);
                    } elseif ($test_result->RecordCount() == 0) {
                        if (@strlen(trim($insert_text_array[$key][7])) == 0) {
                            $insert_text_array[$key][7] = 0;
                        }
                        $sql_query = "INSERT INTO `geodesic_pages_messages`
							(`message_id`,`name`,`description`,`text`,`page_id`,`display_order`,`classauctions`)
							VALUES
							(" . $insert_text_array[$key][0] . ",\"" . $insert_text_array[$key][1] . "\",\"" . $insert_text_array[$key][2] . "\",\"" . $insert_text_array[$key][3] . "\",
							\"" . $insert_text_array[$key][4] . "\",\"" . $insert_text_array[$key][5] . "\",\"" . $insert_text_array[$key][6] . "\")";
                    } else {
                        $sql_query = '';
                    }
                    //add query to query list.
                    $this->addQuery($sql_query, 0);
                }
                while ($show_language = $language_result->FetchRow()) {
                    reset($upgrade_array);
                    foreach (array_keys($upgrade_array) as $key) {
                        $sql_query = "SELECT `text_id` FROM `geodesic_pages_messages_languages` WHERE `text_id` = " . $upgrade_array[$key][1] . " AND `language_id` = " . $show_language["language_id"];
                        $test_result = $this->_db->Execute($sql_query);
                        if (!$test_result) {
                            die("Error on " . __LINE__);
                        }
                        if ($test_result->RecordCount() == 0) {
                            $sql_query = "INSERT INTO `geodesic_pages_messages_languages` (`page_id`, `text_id`,`language_id`,`text`) VALUES (" . $upgrade_array[$key][0] . "," . $upgrade_array[$key][1] . ",\"" . $show_language["language_id"] . "\",\"" . $upgrade_array[$key][3] . "\")";
                        } else {
                            //still hold the place, so the query index isn't messed up
                            $sql_query = '';
                        }
                        //add query to query list.
                        $this->addQuery($sql_query, 0);
                    }
                }
                //free up memory
                unset($upgrade_array);
            }
            if (isset($remove_old_array) && is_array($remove_old_array) && count($remove_old_array) > 0) {
                //add query to query list.
                $this->addQuery("DELETE FROM `geodesic_pages_messages_languages` WHERE `text_id` in (" . implode(', ', $remove_old_array) . ")", 0);
                //add query to query list.
                $this->addQuery("DELETE FROM `geodesic_pages_messages` WHERE `message_id` in (" . implode(', ', $remove_old_array) . ")", 0);
                //free up memory
                unset($remove_old_array);
            }

            if (isset($remove_old_pages_array) && is_array($remove_old_pages_array) && count($remove_old_pages_array) > 0) {
                //remove old pages
                $page_in = '`page_id` IN (' . implode(', ', $remove_old_pages_array) . ')';
                //remove from geodesic_pages
                $this->addQuery("DELETE FROM `ca_ent_trunk`.`geodesic_pages` WHERE $page_in", 0);
                //delete texts for that page
                $this->addQuery("DELETE FROM `geodesic_pages_messages_languages` WHERE $page_in", 0);
                //add query to query list.
                $this->addQuery("DELETE FROM `geodesic_pages_messages` WHERE $page_in", 0);
                //free up memory
                unset($remove_old_pages_array);
            }
        }
        return true;
    }

    /**
     * Runs all the queries stored in _queries.
     * @return Boolean If a strict query has an error, it stops execution and
     *  returns false.  If the query is not strict, it does not care if an error
     *  is thrown, but it does report it in the database.
     */
    public function runQueries()
    {
        //run the queries.
        $keys = array_keys($this->_queries[$this->_currentUpgradeIndex]);
        //make sure the keys are in order...
        sort($keys);

        if (count($keys) > 500) {
            //lots of queries -- raise PHP's execution time to make sure this completes
            include_once('../ini_tools.php');
            geoRaiseExecutionTime(60);
        }

        foreach ($keys as $key) {
            if ($this->_queries[$this->_currentUpgradeIndex][$key]['status'] == 1) {
                //if already run, don't run again
                //echo 'Skipping query, cuz status says already run.<br />';
                continue;
            } elseif ($this->_queries[$this->_currentUpgradeIndex][$key]['status'] == -1 && !$this->_queries[$this->_currentUpgradeIndex][$key]['strict']) {
                //if already run, but error, and it wasn't strict
                //don't run again (but if it is strict, it will attempt to run query again)
                //echo 'Skipping query, cuz already run with error.<br />';
                continue;
            }

            if (strlen(trim($this->_queries[$this->_currentUpgradeIndex][$key]['sql'])) == 0) {
                //query is blank, don't attempt to run
                //but do pretend it was run successfully.
                $this->_queries[$this->_currentUpgradeIndex][$key]['status'] = '1';
                //echo 'Skipping query, cuz strlen is 0<br />';
                continue;
            }

            //run the sql!
            $sql = $this->_queries[$this->_currentUpgradeIndex][$key]['sql'];
            $result = $this->_db->Execute($sql);
            //record the error message now
            $err_msg = '';
            if (!$result) {
                $err_msg = $this->_db->ErrorMsg();
            }
            //echo 'Just executed query: '.$sql.'<br />';
            $this->_queries[$this->_currentUpgradeIndex][$key]['status'] = (($result !== false) ? '1' : '-1');
            $result_sql = 'REPLACE INTO `geodesic_upgrade_queries` SET `query_id`=' . $key . ', `strict`=' . $this->_queries[$this->_currentUpgradeIndex][$key]['strict'] . ',`status`=' . $this->_queries[$this->_currentUpgradeIndex][$key]['status'];


            $result_result = $this->_db->Execute($result_sql);
            if (!$result_result) {
                $this->criticalError('Error when saving query progress. <strong>DB Error Msg:</strong>' . $this->_db->ErrorMsg(), __line__);
            }
            if (!$result && $this->_queries[$this->_currentUpgradeIndex][$key]['strict']) {
                //this is a strict query, and the query failed!
                //stop executing queries...
                echo '<span style="color:red; font-weight:bold;">Critical Upgrade Error:</span> The upgrade query below produced an error.<br />
<strong>Query:</strong> ' . $sql . '<br />
<strong>DB Error Message: </strong>' . $err_msg . '<br /><br />';
                return false;
            }
        }

        return true;
    }
    /**
     * Takes a file and splits it into queries, then stores each query in _queries
     * @param String $filename
     *  the _queries array.
     */
    public function splitSqlFile($filename)
    {
        $handle = fopen($filename, 'r');
        if ($handle) {
            $buffer = '';
            while (!feof($handle)) {
                $this_buffer = fgets($handle, 4096);
                //$this_buffer = rtrim($buffer);
                if (substr(ltrim($this_buffer), 0, 1) == '#' || substr(ltrim($this_buffer), 0, 2) == '--') {
                    //comment line
                    continue;
                }
                $buffer .= $this_buffer;
                //$buffer = rtrim($buffer);
                if (substr(rtrim($buffer), -1) == ';') {
                    //end of query, add query
                    $this->addQuery(trim($buffer), 1);
                    $buffer = '';
                }
            }
        }
    }

    /**
     * Takes the template, does tag substitution, and echos it.
     */
    public function display_page()
    {
        //replace the upgrade step with the text.
        $this->tplVars['upgrade_step'] = $this->step_text;
        //replace the header text
        $this->tplVars['header'] = $this->header_text;

        $tpl = new Smarty();
        $tpl->compile_dir = GEO_BASE_DIR . 'templates_c';
        $tpl->template_dir = GEO_BASE_DIR . 'upgrade/templates';
        $tpl->assign($this->tplVars);

        $tpl->display('index.tpl');
    }
    /**
     * Change the version stored in the database.  This is the same
     * one that is accessed by getCurrentVersion()
     * @param String $new_version
     * @return Boolean false if execute throws error, true otherwise.
     */
    public function updateCurrentVersion($new_version)
    {
        $sql = 'UPDATE `geodesic_version` SET `db_version` = ? LIMIT 1';
        $this->_connectDB();
        $result =& $this->_db->Execute($sql, array($new_version));
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * Takes the update progress data and adds it all nice and pretty to the template main body.
     * Also does appropriate messages and stuff when its needed.
     */
    public function show_results()
    {
        $body = '';
        $button = '';
        if (!$this->_upgrades) {
            return;
        }
        if ($this->_upgrades == 'none') {
            //no upgrades to run!
            $body = '<br /><span style="color: #6B9133; font-weight: bold;"><span style="font-size: 32px;">Congratulations!</span><br />Software upgrade to ' . $this->getCurrentVersion() . ' is complete.</span><br /><br /><br />
<br /><br />' . $this->getFinishedLinks();
        } else {
            $this->tplVars['body_tpl'] = 'body.tpl';
            $this->tplVars['upgradeIndex'] = $this->_currentUpgradeIndex;
            $this->tplVars['upgradeStatus'] = $this->_upgrades[$this->_currentUpgradeIndex]['status'];
            $this->tplVars['upgrades'] = $this->_upgrades;

            if ($this->_currentUpgradeIndex == 0) {
                //first stage, just show all stages.

                $this->step_text .= 'Review Main Upgrades';
            } elseif ($this->_upgrades[$this->_currentUpgradeIndex]['status'] == '-1') {
                //there were errors for one of the upgrades.
                $this->step_text .= 'Error Running Main Upgrades';
            } elseif (isset($this->_upgrades[$this->_currentUpgradeIndex + 1])) {
                //Do more upgrades...
                $this->tplVars['moreUpdates'] = 1;
            } elseif ($this->_upgrades[$this->_currentUpgradeIndex]['status'] == '2' && $this->_upgrades[$this->_currentUpgradeIndex]['to'] == $this->getCurrentVersion()) {
                //all upgrades are complete
                if ($_POST['cleanup']) {
                    $this->tplVars['finishedAll'] = 1;
                    $this->step_text .= 'Final Upgrade Step';
                } else {
                    $this->tplVars['cleanup'] = 1;
                    $this->step_text .= 'Cleanup Step';
                }
            }
        }
        $this->tplVars['body'] = $body;
    }
    /**
     * Shows an "Internal Critical Error", and stops script execution.
     * @param String $message
     * @param (Optional)String $line line number for critical error.
     */
    public function criticalError($message, $line = 'n/a')
    {
        $message = "<strong>Internal Critical Error ($line):</strong>$message<br /><br />
Please contact Geodesic Support.";
        $this->tplVars['body'] = $message;
        $this->display_page();
        exit;
    }
    /**
     * Shows the log (in template) in a textarea.
     */
    public function showLog()
    {
        //shows all current data.
        $body = 'Upgrade Log - Generated ' . date('F d, Y - G:i:s') . '

';

        //show upgrades
        $body .= 'Upgrades:
';
        if (count($this->_upgrades)) {
            foreach ($this->_upgrades as $upgrade) {
                switch ($upgrade['status']) {
                    case -1:
                        $status = 'Critical Errors';
                        break;
                    case 0:
                        $status = 'Not Started';
                        break;
                    case 1:
                        $status = 'Next Upgrade to run.';
                        break;
                    case 2:
                        $status = 'Upgrade Completed.';
                        break;
                }
                $body .= "Upgrade from {$upgrade['from']} to {$upgrade['to']} --- Status: {$status}
";
            }
        }
        if (count($this->_queries)) {
            $body .= '
Queries Recorded:
';
            foreach ($this->_queries as $index => $data) {
                $strict = ($data['strict']) ? 'yes' : 'no';
                switch ($data['status']) {
                    case -1:
                        $status = 'Critical Errors';
                        break;
                    case 0:
                        $status = 'Not Executed';
                        break;
                    case 1:
                        $status = 'Executed successfully';
                        break;
                }
                $body .= "query_id: {$index} - Strict: $strict - Status: $status - SQL Query: {$data['sql']}
";
            }
        }
        if (!(count($this->_queries) || count($this->_upgrades))) {
            $body .= 'No upgrade progress data stored in database.  Did the upgrade complete already?
Note that on successful upgrade completion, the log is cleared.
The log is only preserved if errors occurr during the upgrade process.';
        }
        $next = (isset($_GET['next'])) ? $_GET['next'] : 'continue';
        $body = "Upgrade Log: <br /><textarea cols=\"90\" rows=\"10\" readonly=\"readonly\">" . htmlspecialchars($body) . "</textarea>
<br />
<form method=\"POST\" action=\"index.php?run=$next\">
	<input type=\"submit\" value=\"Continue or Finish Upgrade >>\" />
</form>";
        $this->tplVars['body'] = $body;
    }
    /**
     * @return String HTML links used for the upgrade complete pages.
     */
    public function getFinishedLinks()
    {
        $links = '<p style="text-align:center;">
					Take me to my:<br>
						<a href="../admin/index.php" class="login_link">Admin Home Page</a><br>
						<a href="../index.php" class="login_link">Front End Home Page</a><br>
				</p>';
        return $links;
    }

    public function getLicenseKey()
    {
        $this->_connectDB();
        $row = $this->_db->GetRow("SELECT `value` FROM `geodesic_site_settings` WHERE `setting`='license'");
        return (isset($row['value'])) ? $row['value'] : '';
    }

    private $configuration_data;

    /**
     * Set up GeoCore master switches so that they mirror the version in use before the update, when coming from the old split products
     */
    private function geoCore_init_listingTypes()
    {
        $oldKey = $this->getLicenseKey();
        $type = 0;
        if (stripos('classauctions', $oldKey) !== false) {
            //coming from classauctions -- use existing settings
            return true;
        } elseif (stripos('classifieds', $oldKey) !== false) {
            $type = 1;
        } elseif (stripos('auctions', $oldKey) !== false) {
            $type = 2;
        } else {
            //not an old product -- skip this step
            return true;
        }

        //if we're coming from classauctions, leave things as they are -- the upgrade sorts that later
        //if coming from something else, set it up to look like the old classauctions switch is set, so that the upgrade will sort it later
        $this->_connectDB();
        $sql = "UPDATE `geodesic_classifieds_configuration` SET `listing_type_allowed` = ?";
        $result = $this->_db->Execute($sql, array($type));
        return ($result) ? true : false;
    }

    /**
     * Handy to get "site settings" merged with "configuration data" when such info is needed
     * during an update.  Acts just like the same-named method in main DataAccess class.
     *
     * @param bool $return_table
     * @return array
     */
    private function get_site_settings()
    {
        //force_fresh_get is no longer needed, since we update the config table automatically.
        if (isset($this->configuration_data)) {
            //dont get the data twice if we already have it.
            return $this->_filterSettings();
        }

        $sql = "SELECT * FROM `geodesic_classifieds_configuration`";
        $this->configuration_data = $this->_db->GetRow($sql);

        //to get the new site settings.
        $sql = 'SELECT `setting`, `value` FROM `geodesic_site_settings`';

        $rows = $this->_db->GetAll($sql);

        foreach ($rows as $row) {
            //side effect: any settings duplicated in configuration data and sit config tables,
            //will be overridden by the newer table.
            $this->configuration_data[$row['setting']] = $row['value'];
        }
        return $this->configuration_data;
    }

    /**
     * Should be called in conditional_sql.php to set defaults for a new location
     * on fields to use, and mimic settings for pre-existing location.  Adds
     * needed queries to query array.
     *
     * @param string $locationName
     * @param string $mimicLocation
     */
    public function addFieldLocationDefaults($locationName, $mimicLocation = 'browsing')
    {
        //make sure new location does not already have settings on it
        $count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_fields` WHERE `display_locations` LIKE ?", array ('%"' . $locationName . '"%'));

        //first figure out if there are any fields that have browsing turned on..
        $rows = $this->_db->GetAll("SELECT * FROM `geodesic_fields` WHERE `display_locations` LIKE ?", array ('%"' . $mimicLocation . '"%'));

        foreach ($rows as $row) {
            $locations = unserialize($row['display_locations']);
            $locations = (is_array($locations)) ? $locations : array ();
            if (in_array($mimicLocation, $locations)) {
                $locations[] = $locationName;

                $locations = $this->_db->qstr(serialize($locations));
                $sql = ($count) ? '' : "UPDATE `geodesic_fields` SET `display_locations`=$locations WHERE `group_id`='{$row['group_id']}' AND `category_id`='{$row['category_id']}' AND `field_name`='{$row['field_name']}' LIMIT 1";
                $this->addQuery($sql, 0);
            }
        }
    }
}
