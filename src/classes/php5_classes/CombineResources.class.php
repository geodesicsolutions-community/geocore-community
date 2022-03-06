<?php

//CombineResources.class.php
/**
 * File containing the main class that helps to combine / compress CSS and JS files.
 *
 * @package System
 * @since Version 7.3.0
 */




/**
 * This class helps to combine CSS and JS files, minify them, and optionally compress them.
 *
 * @package System
 * @since Version 7.3.0
 */
class geoCombineResources
{
    /**
     * Use this type for CSS files.  Each type will minify files a little differently.
     * @var string
     */
    const TYPE_CSS = 'css';
    /**
     * Use this type for JS files.  Each type will minify files a little differently.
     * @var string
     */
    const TYPE_JS = 'js';

    private $_files = array();

    private $_type, $_resource_id, $_contents, $_working_path;

    private static $instances = array();

    private static $_tables = array (
        self::TYPE_CSS => geoTables::combined_css_list,
        self::TYPE_JS => geoTables::combined_js_list,
        );
    private static $_externalDomain, $_externalPath;

    /**
     * Class is private to prevent creating an object directly, use getInstance instead.
     * @param string $type
     */
    private function __construct($type)
    {
        if (in_array($type, array (self::TYPE_CSS, self::TYPE_JS))) {
            $this->_type = $type;
        }
    }

    /**
     * Get a new instance for the combined resource
     *
     * @param string $type Either geoCombineResources::TYPE_CSS or geoCombineResources::TYPE_JS
     *   to specify the type of files that are being combined
     * @return NULL|geoCombineResources
     */
    public static function getInstance($type = self::TYPE_CSS)
    {
        if (!self::_isValidType($type)) {
            return null;
        }
        if (!isset(self::$_instances[$type]['new'])) {
            $c = __class__;
            self::$_instances[$type]['new'] = new $c($type);
        }
        return self::$_instances[$type]['new'];
    }

    /**
     * Get instance based on list of files and type of list.
     *
     * @param array $file_list List of files
     * @param string $type
     * @return boolgeoCombineResources
     */
    public static function getListInstance($file_list, $type = self::TYPE_CSS)
    {
        if (!self::_isValidType($type)) {
            return false;
        }

        $file_list = self::_cleanList($file_list);
        if (!$file_list) {
            //file list is invalid
            return false;
        }

        $resource_hash = self::generateFilelistHash($file_list);
        if ($resource_hash === false) {
            //some problem with it...
            return false;
        }

        if (isset(self::$instances[$type][$resource_hash])) {
            //loop through lists with matching hash, see if any match..
            foreach (self::$instances[$type][$resource_hash] as $resource_id => $list) {
                if ($list->getFiles() === $file_list) {
                    //this is the one they need
                    return $list;
                }
            }
            unset($list);
        }
        //none are already retrieved!

        //create the new object and set the values
        $list = new geoCombineResources($type);
        $list->addFiles($file_list);

        //see if it is found in the database
        $row = self::_findExistingListRow($file_list, $type);
        if ($row) {
            //we already know the resource ID!
            $list->setResourceId($row['id']);
            //save list in local cache so we don't have to retrieve again
            self::_saveInstance($list, $type);
        }
        return $list;
    }

    /**
     * Add a list (array) of files to the list.
     * @param array $file_list
     * @return boolean True if files were added successfully, false if invalid input
     */
    public function addFiles($file_list)
    {
        if (!$file_list || !is_array($file_list)) {
            return false;
        }
        if ($this->_resource_id) {
            //if resource ID is already set, we need to unset it if adding files
            //results in the files getting changed...
            $before = $this->_files;
        }
        foreach ($file_list as $filename) {
            //this way makes sure to preserve the file order, also lets us clean it
            //since the files were originally destined to be used in HTML, it may
            //have HTML entities which we need to un-do.
            $filename = geoString::specialCharsDecode($filename);
            if (!in_array($filename, $this->_files)) {
                //we only add once...
                $this->_files[] = $filename;
            }
        }
        if ($this->_resource_id && $this->_files !== $before) {
            //there were changes, the resource ID is no longer valid
            $this->_resource_id = null;
        }
        return true;
    }

    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Get the resource hash based on the currently set files and the version of the software.
     *
     * @return string
     */
    public function getResourceHash()
    {
        return self::generateFilelistHash($this->_files);
    }

    /**
     * Gets the resource ID number for this item (saving to database if required), or false on error.
     *
     * Note that this will serialize the object (save it to the database) if it is not already, in
     * order to generate a resource ID.  So do NOT call this if intending to add
     * additional files as it will result in multiple un-used entries in the database.
     * If you need to find whether there is already a match saved in the database
     * for these values, use {@see geoCombineResources::exists()} instead.
     *
     * @return int|bool Either the resource integer number, or false on error.
     */
    public function getResourceId()
    {
        //serialize it first (Note: will not re-serialize if resource_id is already set)
        $this->serialize();

        if (isset($this->_resource_id)) {
            return $this->_resource_id;
        }
        //for some reason, was not able to even save it and resource ID is not set.
        return false;
    }

    /**
     * Whether or not the entry is already in the database or not.  This is provided
     * mostly for completion purposes, since this cannot be determined using any other method.
     * All other methods will actually save it to the database automatically if it
     * is not already found, this one only "checks" it without attempting to change anything.
     *
     * @return boolean
     */
    public function exists()
    {
        if ($this->_resource_id) {
            //resource ID set, so yes it exists...
            return true;
        }
        $row = self::_findExistingListRow($this->_files, $this->_type);
        if ($row) {
            //found matching entry in database!
            return true;
        }
        return false;
    }

    public function serialize()
    {
        if (isset($this->_resource_id)) {
            //it is already serialized...
            return;
        }

        if (!isset($this->_files, $this->_type)) {
            //files and type required before can serialize
            return;
        }
        //first, do a little checking to prevent duplicate entries...
        $existing = self::_findExistingListRow($this->_files, $this->_type);
        if ($existing) {
            //there is already existing entry!  Use that resource ID
            $this->_resource_id = $existing['id'];
            return;
        }
        unset($existing);

        //entry was not found in DB so add it!

        $hash = $this->getResourceHash();
        $version = geoPC::getVersion();
        $file_list = self::_serializeList($this->_files);
        if (!$hash || !$version || !$file_list) {
            //do not attempt to serialize if one of the values is empty
            return;
        }
        $db = DataAccess::getInstance();
        $table = self::$_tables[$this->_type];

        $sql = "INSERT INTO $table (`version`,`file_list`,`resource_hash`) VALUES (?, ?, ?)";
        $result = $db->Execute($sql, array($version, $file_list, $hash));
        if (!$result) {
            trigger_error('ERROR SQL: Oops, query error when attempting to insert new combined entry.  SQL: ' . $sql . ' - DB error: ' . $db->ErrorMsg());
            return;
        }
        $this->setResourceId($db->Insert_Id());
    }

    public function setResourceId($resource_id)
    {
        if ($resource_id === true) {
            //prevent invalid input of "true" as being used as ID of 1.
            $resource_id = null;
        }
        if ($resource_id !== null) {
            //force it to be either null or an integer.
            $resource_id = (int)$resource_id;
            if (!$resource_id) {
                //don't set resource_id to 0, set it to null so that isset() comes back as false
                $resource_id = null;
            }
        }
        $this->_resource_id = $resource_id;
    }

    /**
     * Get the resource hash based on the list of files passed in.
     *
     * @param array $file_list
     * @return boolean|string
     */
    public static function generateFilelistHash($file_list)
    {
        $file_list = self::_cleanList($file_list);
        if (!$file_list) {
            //invalid input
            return false;
        }

        $file_list['version'] = geoPC::getVersion();
        $file_list = serialize($file_list);
        return sha1($file_list);
    }

    /**
     * Cleans up the array of files passed in, suitable for using internally
     * @param array $file_list
     * @return boolean|array The cleaned up array, or false on invalid input.
     */
    private static function _cleanList($file_list)
    {
        if (!is_array($file_list)) {
            //oops, invalid input
            return false;
        }
        //make sure version is not set in it
        unset($file_list['version']);

        //get rid of any empty values...
        $file_list = array_filter(array_map('trim', $file_list), 'strlen');

        //Make sure array is always indexed numerically and sequentially so it always
        //produces same hash value
        $file_list = array_values($file_list);

        //clean the values, since the url's originally were meant for HTML, need
        //to decode html chars
        $file_list = array_map(array('geoString', 'specialCharsDecode'), $file_list);

        return $file_list;
    }

    /**
     * Serialze the list of files
     *
     * @param array $file_list
     * @return boolean|string The serialized value, or false on error
     */
    private static function _serializeList($file_list)
    {
        $file_list = self::_cleanList($file_list);
        if (!$file_list) {
            //invalid input
            return false;
        }
        //serialize the list
        $file_list = serialize($file_list);
        //base64 encode it to make safe for storing in db
        return base64_encode($file_list);
    }

    private static function _findExistingListRow($file_list, $type)
    {
        if (!self::_isValidType($type)) {
            return false;
        }

        $file_list = self::_cleanList($file_list);
        if (!$file_list) {
            //file list is invalid
            return false;
        }

        $resource_hash = self::generateFilelistHash($file_list);
        if ($resource_hash === false) {
            //some problem with it...
            return false;
        }

        //see if it is found in the database
        $db = DataAccess::getInstance();

        $serialized_list = self::_serializeList($file_list);
        if (!$serialized_list) {
            //invalid input
            return false;
        }
        $version = geoPC::getVersion();

        $table = self::$_tables[$type];

        $sql = "SELECT * FROM $table WHERE `resource_hash`=?";
        $result = $db->Execute($sql, array($resource_hash));
        if (!$result) {
            //DB error...
            trigger_error('ERROR SQL: Error running query, unable to retrieve combined list. sql ' . $sql . ' : error message: ' . $db->ErrorMsg());
            return false;
        }

        foreach ($result as $row) {
            //make sure the list actually matches...
            if ($row['version'] === $version && trim($row['file_list']) === $serialized_list) {
                //it matches!  return the row
                return $row;
            }
        }
    }

    private static function _isValidType($type)
    {
        $valid = array(self::TYPE_CSS, self::TYPE_JS);
        return in_array($type, $valid);
    }

    /**
     * Save instance in local parameters so can get it later.
     *
     * @param geoCombineResource $instance
     */
    private static function _saveInstance($instance)
    {
        if (!$instance || !$instance->getResourceId() || !$instance->getResourceHash()) {
            //just failsafe, these type of checks should already be done prior to
            //getting this far...
            return;
        }
        //save it based on hash
        self::$instances[$instance->getResourceHash()][$instance->getResourceId()] = $instance;
        //also save based on resource id
        self::$instances[$instance->getResourceId()] = $instance;
    }
}
