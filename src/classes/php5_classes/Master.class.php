<?php
//Master.class.php
/**
 * This file holds the geoMaster class.
 *
 * @package System
 * @since Version 7.0.0
 */


/**
 * This is dedicated class for getting the value of, and setting, the various
 * "master switches" within the software.  Since this is meant as an easy way to
 * get the values of master switches, most of the methods are static.
 * 
 * The main usage will be geoMaster::is('setting').  If the situation makes it easier,
 * can use object notation as well like:
 * $master = geoMaster::getInstance();
 * if ($master->setting) {}
 * //can also set value using object notation
 * $master->setting=true;
 * 
 * @package System
 * @since Version 7.0.0
 */
class geoMaster
{
	/**
	 * Used internally
	 * @internal
	 */
	private static $_instance;
	
	/**
	 * Used to store settings for rest of page load.
	 * @internal
	 */
	private $_settings;

	/**
	 * Gets instance of the geoMaster class.
	 * @return geoMaster
	 */
	public static final function getInstance ()
	{
		if (!isset(self::$_instance) || !is_object(self::$_instance)) {
			$c = __class__;
			self::$_instance = new $c ();
		}
		return self::$_instance;
	}
	/**
	 * Constructor.  should not create geoMaster object directly, use getInstance()
	 * instead.  This is private on purpose to prevent new objects created outside
	 * of the class.
	 */
	private function __construct ()
	{
		$this->_settings = DataAccess::getInstance()->getMasters();
	}
	
	/**
	 * This is the primary method that will be used throughout the software to
	 * check whether a setting is on or not.  If multiple parameters are passed in,
	 * all must be turned on for it to return true.
	 * 
	 * @param string $setting The master switch setting name.  Note that this
	 *   method allows passing in multiple strings as additional function
	 *   parameters, to check multiple master switches at once and return false
	 *   if any of them are off.
	 * @return bool True if it is turned on, false if turned off or not set.  
	 *   If multiple parameters are passed in, returns true if ALL settings 
	 *   passed in are on, false otherwise
	 */
	public static function is ($setting)
	{
		$master = self::getInstance();
		if (func_num_args() > 1) {
			//Fancy stuff to let any number of parameters to be passed in, and
			//it will check to make sure ALL of them are on
			foreach (func_get_args() as $setting) {
				if (!self::is($setting)) {
					//return false if any of the passed thingies are off
					return false;
				}
			}
			//all of the passed settings are on
			return true;
		}
		//make sure it's a string...
		$setting = trim($setting);
		
		return (isset($master->_settings[$setting]) && $master->_settings[$setting]==='on');
	}
	
	/**
	 * Returns an array of all the master switches.  Note that the values will be
	 * either on or off as that is how they are stored in the DB.
	 * 
	 * @return array
	 */
	public function getAll ()
	{
		return $this->_settings;
	}
	
	
	/**
	 * Set a value.  Value must be "on" or bool true to turn on, "off" or bool false
	 * to turn off.  Other values would make it evaluate the value as a bool and turn on/off
	 * accordingly.
	 * 
	 * @param string $setting The setting to set.
	 * @param string|bool $value Either on/off or bool true/false
	 * @param bool $save If false, will only change it for this page load, will
	 *   not apply the change to the database.
	 * @since Version 7.0.1
	 */
	public function set ($setting, $value, $save = true)
	{
		$db = DataAccess::getInstance();
		
		//make sure value is "on" or "off" (if it is bool true/false, gets
		//changed to on/off instead)
		$switch = $db->cleanMasterValue($setting, $value);
		
		if ($save) {
			if (isset($this->_settings[$setting])) {
				//update...
				$result = $db->Execute("UPDATE ".geoTables::master." SET `switch`=? WHERE `setting`=?", array ($switch, $setting));
			} else {
				//insert...
				$result = $db->Execute("INSERT INTO ".geoTables::master." SET `switch`=?, `setting`=?", array ($switch, $setting));
			}
			if (!$result) {
				trigger_error("ERROR SQL: Error inserting/updating master setting $setting to value $switch, DB error: ".$db->ErrorMsg());
			}
		}
		$this->_settings[$setting] = $switch;
	}
	
	/**
	 * Magically gets a setting
	 * 
	 * @param string $setting
	 * @return boolean True if setting is on, false if setting not set or turned
	 *   off
	 */
	public function __get ($setting)
	{
		return (isset($this->_settings[$setting]) && $this->_settings[$setting]==='on');
	}
	
	/**
	 * Magic method to allow setting master switch using magic
	 * 
	 * @param string $setting
	 * @param string|bool $value Either on/off or bool true/false
	 */
	public function __set ($setting, $value)
	{
		$this->set($setting,$value);
	}
	
	/**
	 * Allows magically seeing if master switch is even set or not
	 * @param string $setting
	 * @return boolean
	 */
	public function __isset ($setting)
	{
		return ($setting && isset($this->_settings[$setting]));
	}
	
	/**
	 * Magically unset (delete) setting both from the DB and from this class's cached settings
	 * @param string $setting
	 */
	public function __unset($setting)
	{
		if ($setting && isset($this->_settings[$setting])) {
			$db = DataAccess::getInstance();
			$db->Execute("DELETE FROM ".geoTables::master." WHERE `setting`=?",array(''.$setting));
			unset($this->_settings[$setting]);
		}
	}
	/**
	 * Gets a quote from the Master.  Yes, that Master.
	 * 
	 * @return string
	 */
	public static function quote ()
	{
		return 'We meet at last, Doctor!';
	}
}
