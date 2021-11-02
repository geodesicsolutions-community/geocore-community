<?php
/**
 * Main file for addon infrastructure, this is what makes addons go!
 *
 * @package System
 * @since Version 2.0.6b (just showing, addons have been around since then, although not as powerful as they are now)
 */


/**
 * This is the class that handles anything and everything to do with addons.
 *
 * @package System
 */
class geoAddon {
	/**
	 * Used internally
	 * @internal
	 */
	private $db, $installedAddons, $enabledAddons, $addonCoreEvents, $addonAdmins,
		$addonRegs;
	/**
	 * Used Internally
	 * @internal
	 */
	private static $instance, $addonText;

	const RETURN_STRING = 'return_string';
	const FILTER = 'filter';
	const ARRAY_ARRAY = 'array_array';
	const ARRAY_STRING = 'array_string';
	const BOOL_TRUE = 'bool_true';
	const BOOL_FALSE = 'bool_false';
	const NOT_NULL = 'not_null';
	const OVERLOAD = 'overload';

	const NO_OVERLOAD = 'no_overload';

	/**
	 * Constructor.  Do not create new geoAddon class, use geoAddon::getInstance()
	 * instead.  This constructor is private to prevent creating new geoAddon objects
	 * all willy nilly.
	 */
	private function __construct ()
	{
		$this->db = DataAccess::getInstance();
	}
	/**
     * Gets an instance of the addon class, to keep from creating
     * multiple instances when we only need one.
     *
     * @return geoAddon
     */
    public static function getInstance(){
    	if (!(isset(self::$instance) && is_object(self::$instance))){
    		$c = __class__;
    		self::$instance = new $c();
    	}
    	return self::$instance;
    }

	/**
	 * Loads the installed addons installed through the database.  This does
	 * not check if the addon files exist.  This is mainly used by the
	 * loadEnabledAddons() function.  If you are getting data using
	 * the other addon functions, this is automatically called by those
	 * functions.
	 *
	 * @param (Optional)Boolean $force_refresh if true, will get always get the
	 *  settings from the database, even if they have already been retrieved and
	 *  stored.
	 */
	public function loadInstalled($force_refresh = false){
		if (is_array($this->installedAddons) && !$force_refresh){
			//only load them if they havn't been loaded yet.
			return true;
		}
		$settingCache = geoCacheSetting::getInstance();
		$this->installedAddons = $settingCache->process('addons_installed');
		if ($this->installedAddons === false){
			$sql = 'SELECT `name`, `version`, `enabled` FROM '.geoTables::addon_table.' ORDER BY `name`';
			$result = $this->db->GetAll($sql);
			if (!$result){
				$this->installedAddons = array();
				trigger_error('ERROR ADDON: Error getting addons installed from database. SQL: '.$sql.' Error: '.$this->db->ErrorMsg());
				return false;
			}
			$this->installedAddons = array();
			foreach ($result as $row){
				$this->installedAddons[$row['name']] = $row;
			}
			$settingCache->update('addons_installed',$this->installedAddons);
		}
	}
	/**
	 * Used internally to remember if addon updates are required.
	 * @internal
	 */
	private static $_needAddonUpdates = false, $_needAddonUpdatesMsg = false;

	/**
	 * Loads the enabled addons and their info classes into local class vars,
	 * so that they can be used internally.  This is called automatically from
	 * the other addon functions, so it is not required to be called.
	 *
	 * @param bool $force If true, will force re-loading enabled even if they were
	 *   already loaded for the page load
	 */
	public function loadEnabled ($force = false)
	{
		if (self::$_needAddonUpdates && !self::$_needAddonUpdatesMsg && class_exists('Notifications',false)) {
			self::$_needAddonUpdatesMsg = true;
			Notifications::addCheck( create_function( "", "
	return '<strong style=\"color:red\">Addon(s) Require Upgrade:</strong> The addon(s) will be inactive until they have been updated in the <a href=\"index.php?page=addon_tools\">Manage Addons Page</a>.';"));
		}
		if (!$force && isset($this->enabledAddons) && is_array($this->enabledAddons)){
			//already got the enabled addons!
			return true;
		}
		$this->loadInstalled($force);
		$this->enabledAddons = $this->addonCoreEvents = array();
		foreach ($this->installedAddons as $name => $addon){
			if ($addon['enabled']) {
				//only if enabled
				if ( file_exists(ADDON_DIR.$name.'/info.php')){
					//only if file exists
					include_once(ADDON_DIR."$name/info.php");
					if (class_exists("addon_{$name}_info")){
						$this->enabledAddons[$name] = Singleton::getInstance("addon_{$name}_info");
						$enableCheck = true;
						if (method_exists($this->enabledAddons[$name], 'enableCheck')) {
							$enableCheck = $this->enabledAddons[$name]->enableCheck();
						}

						if (!$enableCheck || $addon['version'] != $this->enabledAddons[$name]->version) {
							unset ($this->enabledAddons[$name]); //only include if versions match.
							if ($enableCheck && !self::$_needAddonUpdates && defined('IN_ADMIN')){
								self::$_needAddonUpdates = true;
								if (class_exists('Notifications',false)) {
									self::$_needAddonUpdatesMsg = true;
									Notifications::addCheck( create_function( "", "
	return '<strong style=\"color:red\">Addon(s) Require Upgrade:</strong> The addon(s) will be inactive until they have been updated in the <a href=\"index.php?page=addon_tools\">Manage Addons Page</a>.';"));
								}
							}
							if (!$enableCheck) {
								//disable in cached installed addons
								//set it in installed info as well
								$this->installedAddons[$name]['enabled'] = 0;
							}
						}
					}
				}
			}
		}
		//reset back to null so that next time loadCoreEvents is called it properly loads core events.
		$this->addonCoreEvents = null;
	}

	/**
	 * Loads the addons with core events into a local class var (their util classes)
	 * for use by other addon functions dealing with core events.  This is automatically
	 * run by other addon functions that need to access core events.
	 */
	public function loadCoreEvents(){
		//see if actions need to be added.
		if (isset($this->addonCoreEvents) && is_array($this->addonCoreEvents)){
			//already loaded
			return true;
		}
		$this->loadEnabled();
		$this->addonCoreEvents = array();
		foreach ($this->enabledAddons as $name => $addon) {
			if (isset($addon->core_events) && is_array($addon->core_events) && count($addon->core_events)) {
				//go through each event, and make sure it is actually set up.
				if (file_exists(ADDON_DIR.$name.'/util.php')) {
					include_once(ADDON_DIR."$name/util.php");
					if (class_exists("addon_{$name}_util")){
						$thisutil = Singleton::getInstance("addon_{$name}_util");
						foreach ($addon->core_events as $event_name){
							if (method_exists($thisutil, "core_{$event_name}") || method_exists($thisutil, '__call')) {
								//addon is cool
								$this->addonCoreEvents[$event_name][$addon->name] = $thisutil;
							}
						}
					}
				}
			}
		}
		//let system know addons are enabled and core events are loaded.
		if (!defined('GEO_ADDONS_ENABLED')) define ('GEO_ADDONS_ENABLED',1);
	}

	/**
	 * Determines if an addon is installed in the database.  Note that this does
	 * NOT check to see if the files for the addon exist or not.
	 *
	 * @param String $addon_name The name of the addon to check.
	 * @return Boolean true if the addon is installed in the database, false
	 *  otherwise.
	 */
	public function isInstalled($addon_name){
		$this->loadInstalled();
		//account for servers that have all lower case filenames
		$addon_name = $this->getRealName($addon_name);

		if (isset($this->installedAddons[$addon_name])){
			//it is in the database, so must have been installed.
			return true;
		}
		return false;
	}
	/**
	 * Determines if an addon is installed, and enabled in the database, and that
	 * the addon info file and class exist.
	 *
	 * @param String $addon_name
	 * @return Boolean true if the addon is installed, enabled, and info file & class
	 *  exist
	 */
	public function isEnabled ($addon_name){
		$this->loadEnabled();
		//account for servers that have all lower case filenames
		$addon_name = $this->getRealName($addon_name);

		return (isset($this->enabledAddons[$addon_name]));
	}

	/**
	 * Disable a specific addon, either just for this page load, or by also
	 * disabling it in the DB.  Using this to disable an addon in the DB will
	 * bypass the normal enable() method call for the addon, so should only be
	 * used when the particular addon being disabled is known.
	 *
	 * This must be called AFTER {@link geoAddon::loadEnabledAddons()} is called,
	 * otherwise it will not work.
	 *
	 * @param string $addon_name
	 * @param bool $temporary If false, will change the DB entry to disable the
	 *  addon in the dB as well, bypassing the addon's normal disable() method call.
	 * @since Version 4.0.7
	 */
	public function disableAddon($addon_name, $temporary = true)
	{
		if (!defined('GEO_ADDONS_ENABLED')) {
			//Don't call this BEFORE loading enabled addons, it won't work
			return;
		}
		$addon_name = $this->getRealName($addon_name);

		if (isset($this->enabledAddons[$addon_name])) {
			unset ($this->enabledAddons[$addon_name]);

			//set it in installed info as well, so it "appears" disabled in
			//the addon managment.
			$this->installedAddons[$name]['enabled'] = 0;

			if (!$temporary) {
				$db = DataAccess::getInstance();
				$db->Execute("UPDATE ".geoTables::addon_table." SET `enabled`=0 WHERE `name`='$addon_name'");
				geoCacheSetting::expire('addons_installed');
			}
		}
	}

	/**
	 * Gets the real name, if the addon is already installed.  This is
	 * necessary to get addon management working on some Windows servers,
	 * where all file names are all lowercase.
	 *
	 * @param String $addon_name Lowercase addon string
	 * @return String The corrected addon name.  If addon is not installed,
	 *  returns the same name as passed.
	 */
	public function getRealName($addon_name){
		$this->loadEnabled();
		if (is_object($addon_name)) {
			throw new Exception('$addon_name must be a string!');
		}
		if (isset($this->installedAddons[$addon_name])){
			//name is already good...
			return $addon_name;
		}

		$all_names = array_keys($this->installedAddons);

		foreach ($all_names as $this_name){
			if (strtolower($this_name) == strtolower($addon_name)){
				//account for servers that only use lowercase in the name
				$addon_name = $this_name;
				break;
			}
		}
		return $addon_name;
	}
	/**
	 * Gets the info about an installed addon.  Only gets data from the
	 * database, does not check to see if addon files exist.
	 *
	 * @param String $addon_name
	 * @return Mixed An associative array containing the data from the database
	 *  if the addon is installed in the database, or false otherwise.
	 */
	public function getInstalledInfo($addon_name){
		//account for servers that have all lower case filenames
		$addon_name = $this->getRealName($addon_name);
		if (!$this->isInstalled($addon_name)){
			return false;
		}
		return $this->installedAddons[$addon_name];
	}
	/**
	 * Second most used addon function, this is used to get the utility object for a
	 * given addon.
	 *
	 * @param String $addon_name
	 * @param boolean $force forces a non-enabled addon to be callable, for example, in the setup
	 * @return Mixed The util object for the given addon, or false if the addon
	 *  is not enabled, installed, or the util file or class doesn't exists.
	 */
	public static function getUtil($addon_name,$force=false){
		$addon = self::getInstance();
		$addon->loadEnabled();
		//account for upper/lower case on servers
		//that have only lower case
		$addon_name = $addon->getRealName($addon_name);

		if (!isset($addon->enabledAddons[$addon_name])  && !$force){
			//don't do it if the addon isn't enabled.
			return false;
		}
		$class_name = "addon_{$addon_name}_util";
		//if util class is already existant, no need to include
		if (class_exists($class_name)) {
			if(property_exists($class_name,'url')) {
				if(defined('IN_ADMIN')) {
					$url = '../addons/'.$addon_name.'/';
				} else {
					$url = 'addons/'.$addon_name.'/';
				}
				Singleton::getInstance($class_name)->url = $url;
			}
			return Singleton::getInstance($class_name);
		}
		//need to include the proper file.
		$addon_info = $addon->enabledAddons[$addon_name];

		if (!file_exists(ADDON_DIR.$addon_name."/util.php")){
			return false;
		}
		include_once(ADDON_DIR.$addon_name."/util.php");
		if (!class_exists($class_name)){
			//class is not in file?
			return false;
		}

		$util = Singleton::getInstance($class_name);
		return $util;
	}
	/**
	 * Gets the info class for the specified addon, or false if not enabled addon
	 * or not a valid addon.
	 *
	 * @param string $addon_name
	 * @param bool $force If true, will get the info class even if adon is not enabled.
	 * @return Mixed The info class for the addon, or false if the addon
	 *   is not enabled, installed.
	 * @since Version 5.0.0
	 */
	public static function getInfoClass ($addon_name, $force=false)
	{
		$addon = self::getInstance();
		$addon->loadEnabled();
		//account for upper/lower case on servers
		//that have only lower case
		$addon_name = $addon->getRealName($addon_name);

		if (!isset($addon->enabledAddons[$addon_name]) && !$force){
			//don't do it if the addon isn't enabled.
			return false;
		}
		if (!isset($addon->installedAddons[$addon_name])) {
			//could not find
			return false;
		}
		if (isset($addon->enabledAddons[$addon_name])) {
			return $addon->enabledAddons[$addon_name];
		}
		//not enabled, so have to get class now
		$class_name = "addon_{$addon_name}_info";
		if (!class_exists($class_name)) {
			include_once ADDON_DIR . $addon_name . '/info.php';
		}
		if (!class_exists($class_name)) {
			//could not find class!
			return false;
		}
		return Singleton::getInstance($class_name);
	}

	/**
	 * This is used to get the pages object for a given addon.
	 *
	 * @param String $addon_name
	 * @return Object|bool The pages object for the given addon, or false if the addon
	 *  is not enabled, installed, or the pages file or class doesn't exists.
	 */
	public function getPagesClass($addon_name){
		$this->loadEnabled();
		//account for upper/lower case on servers
		//that have only lower case
		$addon_name = $this->getRealName($addon_name);

		if (!isset($this->enabledAddons[$addon_name])){
			//don't do it if the addon isn't enabled.
			return false;
		}
		//if util class is already existant, no need to include
		if (class_exists('addon_'.$addon_name.'_pages',false)){
			return Singleton::getInstance('addon_'.$addon_name.'_pages');
		}

		if (!file_exists(ADDON_DIR.$addon_name."/pages.php")){
			return false;
		}
		include_once(ADDON_DIR.$addon_name."/pages.php");
		if (!class_exists('addon_'.$addon_name.'_pages')){
			//class is not in file?
			return false;
		}

		return Singleton::getInstance('addon_'.$addon_name.'_pages');
	}

	/**
	 * Gets the tag object for a given addon name, if it exists, and is installed
	 * and enabled in the database.
	 *
	 * @param String $addon_name
	 * @return Mixed The tags class object for the given addon, or false if the addon
	 *  is not enabled, installed, or the tag file or class doesn't exists.
	 */
	public function getTags ($addon_name) {
		$this->loadEnabled();
		//account for servers that have all lower case filenames
		$addon_name = $this->getRealName($addon_name);

		if (!isset($this->enabledAddons[$addon_name])){
			//don't do it if the addon isn't enabled.
			return false;
		}
		//if tag class is already existant, no need to include
		if (class_exists('addon_'.$addon_name.'_tags')){
			return Singleton::getInstance('addon_'.$addon_name.'_tags');
		}

		if (!file_exists(ADDON_DIR.$addon_name."/tags.php")){
			return false;
		}
		include_once(ADDON_DIR.$addon_name."/tags.php");
		if (!class_exists('addon_'.$addon_name.'_tags')){
			//class is not in file?
			return false;
		}
		$tags = Singleton::getInstance('addon_'.$addon_name.'_tags');

		return $tags;
	}

	/**
	 * Gets an array of all the tags for all the currently enabled templates,
	 * mostly useful for admin panel purposes.  Does NOT bother to check for
	 * valid method being defined in each tags class, only goes by what is defined
	 * in the info class.
	 *
	 * @param string $tagType The type of tag, addon means standard tag defined in
	 *   $tags array in the info.  Parameter added in version 7.1.0
	 * @return array
	 * @since Version 5.0.0
	 */
	public function getTagList ($tagType = 'addon')
	{
		$this->loadEnabled();
		$addonNames = array_keys($this->enabledAddons);

		$list = array ();

		$tagType = ($tagType=='addon')? 'tags' : $tagType.'_tags';

		foreach ($addonNames as $name) {
			$info = self::getInfoClass($name);
			if (!$info || !isset($info->$tagType) || !is_array($info->$tagType) || !count($info->$tagType)) {
				//info could not be retrieved, or no tags for this addon, skip this one
				continue;
			}
			$list[$name] = array (
				'name' => $info->name,
				'title' => $info->title,
				'auth_tag' => $info->auth_tag,
				'tags' => $info->$tagType,
			);
		}
		return $list;
	}

	/**
	 * Gets an array of all the pages for all the currently enabled templates,
	 * mostly useful for admin panel purposes.  Does NOT bother to check for
	 * valid method being defined in each pages class, only goes by what is defined
	 * in the info class.
	 *
	 * @return array
	 * @since Version 5.0.0
	 */
	public function getPageList ()
	{
		$this->loadEnabled();
		$addonNames = array_keys($this->enabledAddons);

		$list = array ();
		foreach ($addonNames as $name) {
			$info = self::getInfoClass($name);
			if (!$info || !isset($info->pages) || !is_array($info->pages) || !count($info->pages)) {
				//info could not be retrieved, or no tags for this addon, skip this one
				continue;
			}
			$pages_info = (isset($info->pages_info))? $info->pages_info : array();
			$list[$name] = array (
				'name' => $info->name,
				'title' => $info->title,
				'auth_tag' => $info->auth_tag,
				'pages' => $info->pages,
				'pages_info' => $pages_info,
			);
		}
		return $list;
	}

	/**
	 * Get an array of enabled addons, array will be an associative array of
	 * arrays like this:
	 *
	 * array (
	 * 	'addon_name' =>
	 * 			array ('name' => 'addon_name', 'title' => 'addon title', 'info' => $info_class),
	 * 	...
	 * )
	 *
	 * @return array
	 * @since Version 5.0.1
	 */
	public function getEnabledList ()
	{
		$this->loadEnabled();
		$addonNames = array_keys($this->enabledAddons);

		$list = array ();
		foreach ($addonNames as $name) {
			$info = self::getInfoClass($name);
			if (!$info) {
				//info could not be retrieved
				continue;
			}
			$list[$name] = array (
				'name' => $info->name,
				'title' => $info->title,
				'info' => $info
			);
		}
		return $list;
	}


	/**
	 * Used in the admin to go through each installed & enabled addon, which
	 * has an admin file and class, and has the init function defined.
	 *
	 * Used to load each addon's admin menu item into memory, for page loading
	 * and to create the dynamic menu.
	 *
	 * @param string $menuName The main menu that is being loaded.
	 */
	public function initAdmin ($menuName)
	{
		//init all the addon's admin sections
		$this->loadEnabled();

		//do not need to block doing twice.

		foreach ($this->enabledAddons as $name => $addon) {
			if (file_exists(ADDON_DIR.$name.'/admin.php')) {
				include_once(ADDON_DIR."$name/admin.php");
				if (class_exists('addon_'.$name.'_admin')){
					$this->addonAdmins[$name] = Singleton::getInstance("addon_{$name}_admin");
					if (method_exists($this->addonAdmins[$name],'init_pages')){
						$this->addonAdmins[$name]->init_pages($menuName);
					} else {
						trigger_error('DEBUG ADDON: Addon '.$name.' does not have init_pages method.');
					}

					if (method_exists($this->addonAdmins[$name],'init_text')){
						//add text page
						menu_page::addonAddPage('edit_addon_text&amp;addon='.$name,'','Edit Text',$name);
					}
					//see if there are pages
					if (isset($addon->pages) && is_array($addon->pages) && count($addon->pages) > 0){
						//add text page
						menu_page::addonAddPage('page_attachments&amp;addon='.$name,'','Edit Pages',$name);
					}
				}
			}
		}
	}

	/**
	 * Whether or not core events have loaded or not.
	 *
	 * @return Boolean true if the core events have been initialized, or false
	 *  otherwise.
	 */
	public function coreEventsLoaded(){
		if (isset($this->addonCoreEvents) && is_array($this->addonCoreEvents)){
			return true;
		}
		return false;
	}

	/**
	 * Gets the number of addons using the given core event.
	 *
	 * @param string $core_event_name
	 * @return int
	 */
	public function coreEventCount($core_event_name){
		$this->loadCoreEvents();
		if (!isset($this->addonCoreEvents[$core_event_name])){
			return 0;
		}
		return count($this->addonCoreEvents[$core_event_name]);
	}

	/**
	 * Gets the text for the addon.  This is most useful to addons, to get
	 * the text for their addon.
	 *
	 * @param string $auth_tag
	 * @param string $addon_name
	 * @param int $language_id If set, will use this instead of current
	 *  language id.
	 * @param bool $force_refresh True to force it to retrieve text even if the text
	 *   has already been retrieved this page load
	 * @return array An associative array of messages for the given addon, following the
	 *  syntax: array ( 'text_index1' => 'text value 1', 'text_index2' => 'text value 2')
	 */
	public static function getText($auth_tag, $addon_name, $language_id = 0, $force_refresh=false){
		if (isset(self::$addonText[$auth_tag][$addon_name][$language_id]) && !$force_refresh){
			return self::$addonText[$auth_tag][$addon_name][$language_id];
		}
		if (!isset(self::$addonText) || !is_array(self::$addonText)){
			self::$addonText = array();
		}
		//get a non static copy of ourself, so we can access non-static vars.
		$addon = self::getInstance();

		//get the language_id
		if (!$language_id){
			$language_id = $addon->db->getLanguage();
		}
		if (geoCache::get('cache_setting')){
			$settingCache = geoCacheSetting::getInstance();
			self::$addonText = $settingCache->process('addon_text');
		}
		if (!geoCache::get('cache_setting') || !isset(self::$addonText[$auth_tag][$addon_name][$language_id])){
			if (!is_array(self::$addonText)){
				self::$addonText = array();
			}
			//load it up!
			$messages = self::getTextRaw($auth_tag, $addon_name, $language_id);

			foreach ($messages as $text_id => $message) {
				//parse the text for any {external...} tags, and fromDB it
				$message = geoTemplate::parseExternalTags(geoString::fromDB($message));
				//make it in a nice easy to access array...
				self::$addonText[$auth_tag][$addon_name][$language_id][$text_id] = $message;
			}
			if (geoCache::get('cache_setting')) $settingCache->update('addon_text',self::$addonText);
		}

		return self::$addonText[$auth_tag][$addon_name][$language_id];
	}

	/**
	 * Gets the "raw" addon text for given addon and language, it doesn't even
	 * fromDB the results.  Useful mostly in the admin where need to access the
	 * values before they are parsed for {external} tags
	 *
	 * @param string $auth_tag
	 * @param string $addon_name
	 * @param number $language_id
	 * @return array
	 * @since Version 7.1.0
	 */
	public static function getTextRaw ($auth_tag, $addon_name, $language_id = 0)
	{
		//get a non static copy of ourself, so we can access non-static vars.
		$addon = self::getInstance();

		//get the language_id
		if (!$language_id){
			$language_id = $addon->db->getLanguage();
		}
		//load it up!
		$sql = "SELECT text_id, text FROM ".geoTables::addon_text_table
		." WHERE `auth_tag` = ? AND `addon` = ? AND `language_id` = ?";

		$result = $addon->db->Execute($sql, array($auth_tag, $addon_name, $language_id));
		if (!$result){
			trigger_error('DEBUG ADDON: There was a db error when trying to get text.  Error:'.$addon->db->ErrorMsg());
			return false;
		}
		$messages = array();
		while ($row = $result->FetchRow()){
			//make it in a nice easy to access array...
			$messages[$row['text_id']] = $row['text'];
		}
		return $messages;
	}

	/**
	 * Sets the text for the addon, should only need to be set in the admin addon
	 * text management.
	 *
	 * @param String $auth_tag
	 * @param String $addon
	 * @param String $text_id max length is 128
	 * @param String $text
	 * @param Int(optional) $language_id If set, will use this language id.  Defaults to 1.
	 */
	public function setText($auth_tag, $addon, $text_id, $text, $language_id = 1){
		//see if the text is already set, or this is new text.
		$pre_text = geoAddon::getText($auth_tag,$addon, $language_id);
		//encode text to prevent corruption in the DB
		$text = geoString::toDB($text);
		if (isset($pre_text[$text_id])){
			//this is an update.
			$sql = 'UPDATE '.$this->db->geoTables->addon_text_table.' SET text = ? WHERE auth_tag = ? AND addon = ? AND language_id = ? AND text_id = ? LIMIT 1';
			$query_data = array($text, $auth_tag, $addon, $language_id, $text_id);
		} else {
			//inserting new text
			$sql = 'INSERT INTO '.$this->db->geoTables->addon_text_table.' SET auth_tag = ?, addon = ?, text_id = ?, language_id = ?, text = ?';
			$query_data = array($auth_tag, $addon, $text_id, $language_id, $text);
		}
		$result = $this->db->Execute($sql, $query_data);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Error in sql, error: '.$this->db->ErrorMsg());
			return false;
		}
		if (geoCache::get('cache_setting')){
			$settingCache = geoCacheSetting::getInstance();
			$settingCache->expire('addon_text');
		}
		return true;
	}
	/**
	 * Used to get addon admin objects
	 *
	 * @param (optional)String $addon_name
	 * @param bool $includeDisabled True to get even disabled addons
	 * @return Array If $addon_name is supplied, will return the admin object
	 *  for that addon, so that init_text can be called for it.  If no addon_name
	 *  is supplied, returns an array of info objects where the admins have init_text functions.  If
	 *  there are no admin objects, returns false.
	 */
	public function getTextAddons($addon_name=0, $includeDisabled=false){
		//we are assuming that we are in the admin, so init admins
		//will all ready be called.
		if (!isset($this->addonAdmins) || !is_array($this->addonAdmins) || !count($this->addonAdmins)){
			return false;
		}
		if ($addon_name && isset($this->addonAdmins[$addon_name])){
			if (method_exists($this->addonAdmins[$addon_name], 'init_text')){
				//return the addon admin object, for teh addon specified.
				return $this->addonAdmins[$addon_name];
			} else {
				//the init_text function doesn't exist, so return false.
				return false;
			}
		} elseif ($addon_name){
			//addon name was passed in, but that object doesn't exist for the
			//addon.
			return false;
		}

		//no addon_name was specified, so return all admin addons with init_text.
		$return_addons = array();
		if($includeDisabled) {
			//get list of addons that are installed but NOT enabled
			$installed = array_keys($this->installedAddons);
			$enabled = array_keys($this->enabledAddons);
			$disabled = array_diff($installed, $enabled);

			//now find out which ones have text and add them to the array
			foreach($disabled as $name) {
				//not enabled, so the admin class won't be included yet
				include_once(ADDON_DIR.'/'.$name.'/info.php');
				include_once(ADDON_DIR.'/'.$name.'/admin.php');
				$adminClass = "addon_{$name}_admin";
				if(!class_exists($adminClass)) {
					//this addon does not have an admin class -- move along
					continue;
				}
				if(method_exists($adminClass, 'init_text')) {
					//found the init_text function. add this to the array of addons to return
					$return_addons[$name] = $this->installedAddons[$name];
				}
			}
		}
		foreach (array_keys($this->addonAdmins) as $name){
			if (method_exists($this->addonAdmins[$name],'init_text')){
				//the init_text method exists for this addon admin object,
				//so add it to the thingy.
				$return_addons[$name] = $this->enabledAddons[$name];
			}
		}
		return $return_addons;
	}

	/**
	 * Get page addons for use in admin
	 * @param string $addon_name If specified, the addon name
	 * @return boolean|array The array of pages, or bool false on error or nonoe
	 *   found
	 */
	public function getPageAddons($addon_name=0){
		//we are assuming that we are in the admin, so init admins
		//will all ready be called.
		if (!isset($this->enabledAddons) || !is_array($this->enabledAddons) || !count($this->enabledAddons)){
			return false;
		}

		if ($addon_name && isset($this->enabledAddons[$addon_name])) {
			$return = array();
			if (isset($this->enabledAddons[$addon_name]->pages)) {
				$return = $this->enabledAddons[$addon_name]->pages;
			}
			return $return;
		} else if ($addon_name){
			//addon name was passed in, but that object doesn't exist for the
			//addon.
			return false;
		}
		$return = array();
		foreach ($this->enabledAddons as $name => $addon) {
			if (isset($addon->pages) && is_array($addon->pages) && count($addon->pages) > 0) {
				$return[$name] = $addon->pages;
			}
		}
		return $return;
	}
	/**
	 * Get api addons for use in admin
	 *
	 * @return boolean|array The array of pages, or bool false on error or nonoe
	 *   found
	 */
	public function getApiAddons ()
	{
		$this->loadEnabled();
		if (count($this->enabledAddons)==0){
			return false; //no addons
		}
		$array_keys = array_keys($this->enabledAddons);
		$addons = array();
		foreach ($array_keys as $name){
			if (is_dir(ADDON_DIR.$name.'/api')){
				$addons[] = $name;
			}
		}
		return $addons;
	}
	/**
	 * Get addons with an app top in them
	 *
	 * @return boolean|array Array of addons, or bool false on error
	 */
	public function getAppTopAddons(){
		$this->loadEnabled();
		if (count($this->enabledAddons)==0){
			return false; //no addons
		}
		$array_keys = array_keys($this->enabledAddons);
		$addons = array();
		foreach ($array_keys as $name){
			if (file_exists(ADDON_DIR.$name.'/app_top.php')){
				$addons[] = $name;
			}
		}
		return $addons;
	}

	/**
	 * Get addons that have seller/buyer gateways in them
	 * @return array The array of addons
	 */
	public function getSellerBuyerAddons ()
	{
		$this->loadEnabled();
		if (count($this->enabledAddons)==0){
			return array(); //no addons
		}
		$array_keys = array_keys($this->enabledAddons);
		$addons = array();
		foreach ($array_keys as $name){
			if (is_dir(ADDON_DIR.$name.'/payment_gateways/seller_buyer')){
				$addons[] = $name;
			}
		}
		return $addons;
	}

	/**
	 * Get addons that have payment gateways in them
	 * @return array The array of addons
	 */
	public function getPaymentGatewayAddons ()
	{
		$this->loadEnabled();
		if (count($this->enabledAddons)==0){
			return array(); //no addons
		}
		$array_keys = array_keys($this->enabledAddons);
		$addons = array();
		foreach ($array_keys as $name){
			if (is_dir(ADDON_DIR.$name.'/payment_gateways')){
				$addons[] = $name;
			}
		}
		return $addons;
	}

	/**
	 * Get addons that have order items in them
	 * @return array The array of addons
	 */
	public function getOrderTypeAddons(){
		$this->loadEnabled();
		if (count($this->enabledAddons)==0 && !defined('GEO_ADDON_SETUP')){
			return array(); //no addons
		}
		$array_keys = array_keys($this->enabledAddons);
		if (defined('GEO_ADDON_SETUP') && !in_array(GEO_ADDON_SETUP, $array_keys)) {
			//allow this order item type to be accessible during addon setup
			//operations even if addon not yet enabled
			$array_keys[] = GEO_ADDON_SETUP;
		}
		$addons = array();
		foreach ($array_keys as $name){
			if (is_dir(ADDON_DIR.$name.'/order_items')){
				$addons[] = $name;
			}
		}
		return $addons;
	}


	/**
	 * Calls the specified display function, and seperates the returned
	 * responses using $seperator (or other special case uses of seperator,
	 * see below)
	 *
	 * @param string $core_event_name
	 * @param mixed $vars
	 * @param string $trigger_type One of:
	 *  geoAddon::RETURN_STRING - call returns string, this returns all strings separated by $separator
	 *  geoAddon::FILTER - call returns $var filtered, this returns $var after being filtered
	 *  geoAddon::ARRAY_ARRAY - call returns non-empty array, this returns array of arrays
	 *  geoAddon::ARRAY_STRING - call returns string, this returns array of strings.
	 *  geoAddon::BOOL_TRUE - This returns true if any call returns true, false otherwise.  Once a true value is returned, no other
	 *   addon calls are made.
	 *  geoAddon::BOOL_FALSE - This returns false if any call returns false, true otherwise.  Once a false value is returned, no other
	 *   addon calls are made.
	 *  geoAddon::NOT_NULL - call returns null, or anything else.  this returns the first non-null value, or null (main use: can be used as
	 *   a combination of BOOL_TRUE and BOOL_FALSE
	 *  geoAddon::OVERLOAD - call returns geoAddon::NO_OVERLOAD or anything else, upon first addon not returning geoAddon::NO_OVERLOAD, it will
	 *   stop calling any more addons and return that value.
	 * @param string $separator Used if trigger_type is geoAddon::RETURN_STRING
	 * @return mixed Depends on what trigger_type is.
	 */
	public static function triggerDisplay($core_event_name, $vars=null, $trigger_type = geoAddon::RETURN_STRING, $separator = ''){
		$addon = geoAddon::getInstance();
		$addon->loadCoreEvents();
		$function_name = "core_$core_event_name";
		$items = (isset($addon->addonCoreEvents[$core_event_name]))? $addon->addonCoreEvents[$core_event_name]: array();
		$parts = array();
		foreach ($items as $key => $item) {
			if (method_exists($item,$function_name) || method_exists($item, '__call')) {
				//call it statically
				//trigger_error('DEBUG CART: calling display object '.self::$orderTypes[$key]['class_name'].' method '.$function_name);
				$this_html = $item->$function_name($vars);
				switch ($trigger_type) {
					case self::FILTER:
						//special case, each addon takes the input, and filters it, and returns the results.
						$vars = $this_html;
						break;

					case self::ARRAY_ARRAY:
						//special case, it should return a non-empty array.
						if (is_array($this_html) && count($this_html) > 0){
							$parts[$key] = $this_html;
						}
						break;

					case self::ARRAY_STRING:
						//special case, it should return a string, which is then returned to the calling function
						//as an array of strings.
						if (strlen($this_html) > 0) {
							$parts[$key] = $this_html;
						}
						break;

					case self::BOOL_TRUE:
						//bool_true special case: if any results are true, return true
						if ($this_html === true) {
							return true;
						}
						break;

					case self::BOOL_FALSE:
						//bool_false special case: if any results are false, return false
						if ($this_html === false) {
							return false;
						}
						break;
					case self::NOT_NULL:
						//not_null: if any results are not null, return that result.
						if ($this_html !== NULL) {
							//strict, if it's not null, return it.
							return $this_html;
						}
						break;

					case self::OVERLOAD:
						if ($this_html !== self::NO_OVERLOAD) {
							return $this_html;
						}
						break;

					case self::RETURN_STRING:
						//break ommited on purpose
					default:

						if (strlen($this_html) > 0) {
							$parts[] = $this_html;
						}
						break;
				}
			}
		}
		switch ($trigger_type) {
			case self::FILTER:
				//special case, each addon takes the input, and filters it, and returns the results.
				return $vars;
				break;

			case self::ARRAY_ARRAY:
				//break ommited on purpose
			case self::ARRAY_STRING:
				return $parts;
				break;

			case self::BOOL_TRUE:
				//nothing specifically returned true, so return false
				return false;
				break;

			case self::BOOL_FALSE:
				//nothing specifically returned false, so return true
				return true;
				break;

			case self::NOT_NULL:
				//return null
				return null;
				break;

			case self::OVERLOAD:
				return self::NO_OVERLOAD;
				break;

			case self::RETURN_STRING:
				//break ommited on purpose
			default:
				//implode all the responses and seperate them according
				//to seperator.
				$html = '';
				if (count($parts) > 0) {
					$html .= implode($separator,$parts);
				}
				return $html;
				break;
		}
	}

	/**
	 * Calls the specified core update function.  Does not expect any return values from the calls.
	 *
	 * @param string $core_event_name
	 * @param mixed $vars
	 * @return int Count of the number of addons called.
	 */
	public static function triggerUpdate($core_event_name, $vars=null){
		$addon = geoAddon::getInstance();
		$addon->loadCoreEvents();
		$function_name = "core_$core_event_name";
		$items = (isset($addon->addonCoreEvents[$core_event_name]))? $addon->addonCoreEvents[$core_event_name]: array();
		$count = 0;
		foreach ($items as $key => $item) {
			if (method_exists($item,$function_name) || method_exists($item, '__call')) {
				$count++;
				$item->$function_name($vars);
			}
		}
		return $count;
	}

	/**
	 * Gets a geoRegistry item for the specified addon, to allow getting and setting
	 * values specific to a particular addon.
	 *
	 * @param string $addon_name
	 * @param bool $force set to true to get registry even if addon is not enabled.
	 * @return geoRegistry|null Will return null if invalid or disabled addon
	 */
	public static function getRegistry ($addon_name, $force = false)
	{
		$addon = geoAddon::getInstance();
		$addon->loadCoreEvents();

		//clean addon name
		$addon_name = $addon->getRealName($addon_name);

		if (!isset($addon->enabledAddons[$addon_name]) && !$force){
			//don't do it if the addon isn't enabled.
			return false;
		}

		if (!isset($addon->addonRegs)) {
			$addon->addonRegs = array();
		}

		if (!isset($addon->addonRegs[$addon_name])) {
			$addon->addonRegs[$addon_name] = new geoRegistry('addon',$addon_name);
		}
		if (!isset($addon->addonRegs[$addon_name])) {
			//still no registry?  somethign went wrong...
			return false;
		}

		return $addon->addonRegs[$addon_name];
	}

	/**
	 * Used internally by Geo addons, just a message saying the addon is not
	 * attached to the license, used so text only needs to be edited in one place
	 * and not hard coded into every licensed addon.
	 *
	 * @return string
     * @deprecated
	 */
	public static function textNoLicense ()
	{
		return "";
	}

	/**
	 * Returns an entire section to be displayed in the admin, containing links
	 * to each price plan, which will have the given item type's configuration
	 * button automatically "selected".
	 *
	 * @param string $itemType The item type to have already open (configuration "clicked")
	 * @param bool $skipCategory if true, will not display category specific links
	 * @return string A bit of HTML perfect for displaying in the admin.
	 * @since Version 4.1.0
	 */
	public static function adminDisplayPlanItemLinks ($itemType = '', $skipCategory = false)
	{
		$db = DataAccess::getInstance();
		//get all the price plans available
		$sql = "SELECT `price_plan_id`, `name` FROM ".geoTables::price_plans_table;
		if (!geoMaster::is('classifieds')) {
			//auctions only
			$sql .= " WHERE `applies_to`=2";
		} else if (!geoMaster::is('auctions')) {
			//classifieds only
			$sql .= " WHERE `applies_to`=1";
		}
		$all = $db->GetAll($sql);
		$plans = array ();
		$itemType = ($itemType)? '&amp;planItem='.$itemType: '';
		foreach ($all as $row) {
			$plans[$row['price_plan_id']] = array (
				'link' => "index.php?page=pricing_edit_plans&amp;f=3&amp;g={$row['price_plan_id']}{$itemType}#price_plan_items",
				'name' => $row['name']." (Plan #{$row['price_plan_id']})",
			);
		}

		if (!$skipCategory && (geoPC::is_ent() || geoPC::is_premier())) {
			//now get all the category specific settings

			//but make sure there is not too much to handle
			$sql = "SELECT count(*) as count FROM ".geoTables::price_plans_categories_table;
			$count = $db->GetRow($sql);
			if ($count && $count['count'] > 0 && $count['count'] < 26) {
				//only display if there are between 0 and 25 cat specific pricing
				$sql = "SELECT p.*, c.category_name  FROM ".geoTables::price_plans_categories_table." p, ".geoTables::categories_table." c
					WHERE c.category_id = p.category_id ORDER BY p.price_plan_id";
				$all = $db->GetAll($sql);

				foreach ($all as $row) {
					$plans[$row['price_plan_id']]['cats'][$row['category_price_plan_id']] = array (
						'link' => "index.php?page=pricing_category_costs&amp;d={$row['category_id']}&amp;e=1&amp;x={$row['price_plan_id']}&amp;y={$row['category_price_plan_id']}{$itemType}#price_plan_items",
						'name' => "{$row['category_name']} (Category #{$row['category_id']})",
					);
				}
			}
		}


		$tpl = new geoTemplate('admin');
		$tpl->assign('plans', $plans);
		$tpl->assign('itemType', $itemType);
		return $tpl->fetch('addon_manage/planItemLinks.tpl');
	}

	/**
	 * Allows addons to set the default page template used for an addon page,
	 * that will be set inside the default template set, so that the default
	 * will always be there.
	 *
	 * We recommend to now use this, since there is
	 * already built-in functionality to do this for you, using the
	 * $info->pages_info array.  Using that instead will ensure the default
	 * template attachment is set when re-scanning default template set as well.
	 *
	 * @param string $addon The addon name
	 * @param string $page The page name
	 * @param string $tplName The template file name - this can be a template
	 *   in the default template set, or can be a new template file
	 * @param string|null $tplContents If not null, the tpl file will be created
	 *   and the tpl contents will be inserted.
	 * @param array $altTplNames Alternate/Secondary templates that should also be attached
	 * @return bool True if success, false otherwise.  If in admin, upon failure
	 *   this will automatically add admin error messages.
	 * @since Version 5.0.0
	 */
	public function setDefaultPageTemplate ($addon, $page, $tplName, $tplContents = null, $altTplNames=array())
	{
		$this->loadEnabled();

		$addon = $this->getRealName($addon);
		if (!$addon || !$page || !$tplName) {
			if (defined('IN_ADMIN')) {
				geoAdmin::m('Invalid data specified, could not set the default page template for the addon.');
			}
			return false;
		}
		$File = geoFile::getInstance(geoFile::TEMPLATES);
		//addon may or may not be installed at this time, so cannot check that way...

		//create the settings file and assign the default
		$tpl = new geoTemplate(geoTemplate::ADMIN);

		$attachments = array();
		$attachments[0] = $tplName; //make certain the main page is element 0
		foreach($altTplNames as $alt) {
			$attachments[] = $alt;
		}

		$tpl->assign('page_attachments', array(
			1 => $attachments
		));
		$attachResult = $File->fwrite("default/main_page/attachments/templates_to_page/addons/$addon/$page.php",
			$tpl->fetch('design/files/templates_to_page.tpl'));

		unset($tpl);
		if (!$attachResult) {
			if (defined('IN_ADMIN')) {
				geoAdmin::m('Could not attach default template for addon.', geoAdmin::ERROR);
			}
			return false;
		}
		if ($tplContents !== null) {
			//write the file
			$tplContents = trim($tplContents);
			$tplResult = $File->fwrite("default/main_page/$tplName", $tplContents);
			if (!$tplResult) {
				if (defined('IN_ADMIN')) {
					geoAdmin::m('Creating default template for addon page failed.',geoAdmin::ERROR);
				}
				return false;
			}
			//now do the attachment file
			require_once ADMIN_DIR . 'design.php';
			$tpl = new geoTemplate(geoTemplate::ADMIN);
			$tpl->assign(DesignManage::scanForAttachments($tplContents));
			$tplAttachResult = $File->fwrite("default/main_page/attachments/modules_to_template/$tplFile.php",
				$tpl->fetch('design/files/modules_to_template.tpl'));
			if (!$tplAttachResult) {
				if (defined('IN_ADMIN')) {
					geoAdmin::m('Creating template attachments file for addon page failed.',geoAdmin::ERROR);
				}
				return false;
			}
		}
		return true;
	}

	/**
	 * Used mostly by admin panel, this will scann an addon and copy over any
	 * templates to the default template set, and set the attachments for the
	 * addon pages if $info->pages_info is used properly.
	 *
	 * @param string $addonName
	 * @return bool
	 * @since Version 5.0.1
	 */
	public function updateTemplates ($addonName)
	{
		$this->loadEnabled();

		$addon = $this->getRealName($addonName);
		$admin = geoAdmin::getInstance();

		$templateFile = geoFile::getInstance(geoFile::TEMPLATES);

		if (!is_writable($templateFile->absolutize('default/'))) {
			geoAdmin::m('Could not create the default templates or template attachments for the addon, check the permissions on the default template set.  You may need to use FTP to CHMOD 777 the folder ('.$admin->geo_templatesDir().'default/) and <strong>All folder\'s contents, recursively</strong>.', geoAdmin::ERROR);
			return false;
		}
		//all file operations jailed to addon dir
		$addonFile = geoFile::getInstance(geoFile::ADDON);
		//file operations going from addon folder to addon folder, would need to
		//be jailed to base dir
		$file = geoFile::getInstance(geoFile::BASE);

		//clear out any current default addon templates
		$templateFile->unlink("default/addon/$addonName/");

		//clear out any attachments
		$templateFile->unlink("default/main_page/attachments/templates_to_page/addons/$addonName/");

		if (is_dir($addonFile->absolutize($addonName.'/templates/'))) {
			//copy over all templates from the addon's templates folder, to the default
			//template set, except for main_page or external (special cases) and admin
			$skip = array ('.','..','main_page', 'external', 'admin');

			$list = array_diff(scandir($addonFile->absolutize($addonName.'/templates/')), $skip);
			foreach ($list as $entry) {
				//copy each file over
				$from = $addonFile->absolutize("$addonName/templates/$entry");
				$to = $templateFile->absolutize("default/addon/$addonName/$entry");
				if (!$file->copy($from, $to)) {
					//problem with copying one of the files/folders...
					return false;
				}
			}

			if (is_dir($addonFile->absolutize("$addonName/templates/main_page/"))) {
				//special case, see if there is a folder named main_page and if so, copy the stuff in it over
				if (!$this->_templateCopy($addonName,'main_page')) {
					//something went wrong when copying main_page templates
					return false;
				}
			}
			if (is_dir($addonFile->absolutize("$addonName/templates/external/"))) {
				//special case, see if there is a folder named external and if so, copy the stuff in it over
				if (!$this->_templateCopy($addonName, 'external')) {
					//something went wrong when copying external templates
					return false;
				}
			}
		}

		$info = Singleton::getInstance('addon_'.$addonName.'_info');

		//See if the addon has pages, if it does, auto-assign templates
		if (!isset($info->pages) && count($info->pages) == 0) {
			//no addon pages
			return true;
		}

		if (!isset($info->pages_info) || count($info->pages_info) == 0) {
			//Addon has pages, let the user know
			geoAdmin::m('Note: This Addon has pages that may need to be assigned templates.  To set or change the templates assigned to pages for this addon, click <a href="index.php?page=page_attachments&amp;addon='.$info->name.'">Edit Page</a> next to the addon.',geoAdmin::NOTICE);
			return true;
		}
		$addon = geoAddon::getInstance();

		foreach ($info->pages_info as $page => $data) {
			if (!in_array($page, $info->pages)) {
				geoAdmin::m('Addon mis-configured, there is no page matching ('.$page.') in the addon\'s main $info->pages array!', geoAdmin::NOTICE);
				continue;
			}
			if (isset($data['main_page']) && file_exists($templateFile->absolutize('default/main_page/'.$data['main_page']))) {
				if (!$addon->setDefaultPageTemplate($addonName, $page, $data['main_page'], null, $data['alternate_templates'])){
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Loads up displaying an addon tag for smarty.
	 *
	 * @param array $params The params as passed in for the smarty function.
	 * @param Smarty_Internal_Template $smarty The smarty object as passed in for the function.
	 * @param string $addonName
	 * @param string $tag
	 * @param string $tagType
	 */
	public function smartyDisplayTag ($params, Smarty_Internal_Template $smarty, $addonName, $tag, $tagType = 'addon')
	{
		$this->loadEnabled();
		if ($addonName=='core' && in_array($tagType, array ('core','listing'))) {
			//loop through all the core tag types and call the tag for each one
			$addonNames = array_keys($this->enabledAddons);

			$_return = '';

			$fakeParams = $params;
			//make sure it does not assign contents to something before we are ready
			unset($fakeParams['assign']);

			$tagType = 'core_tags';//($tagType=='addon')? 'tags' : $tagType.'_tags';
			foreach ($addonNames as $name) {
				$info = self::getInfoClass($name);
				if (!$info || !isset($info->$tagType) || !is_array($info->$tagType) || !count($info->$tagType)) {
					//info could not be retrieved, or no tags for this addon, skip this one
					continue;
				}
				$_return .= $this->smartyDisplayTag($fakeParams, $smarty, $name, $tag, 'core');
			}
			if ($params['assign'] && strlen($_return)) {
				//make sure to only "assign" if the return is not empty..  don't want
				//to end up double-assigning something, which would result in it clearing
				//the first assignment..
				$smarty->assign($params['assign'], $_return);
				return '';
			}

			return $_return;
		}

		//make sure tag is registered by this addon.
		$info = geoAddon::getInfoClass($addonName);
		if (!$info) {
			//not a good addon
			return '';
		}

		$tagType = ($tagType=='addon')? 'tags' : $tagType.'_tags';

		if (!isset($info->$tagType) || !in_array($tag, $info->$tagType)) {
			//not one of the registered tags.
			return '';
			//return 'not a registered tag';
		}

		if (isset($params['headOnly']) && $params['headOnly']) {
			//Special case!  not meant to parse this, it's just here to have the
			//head stuff loaded
			return '';
		}
		//get the tag object.
		$tagObj = $this->getTags($addonName);
		if (!is_object($tagObj) || !is_callable(array($tagObj, $tag))) {
			//method for the gat doesnt exist
			return '';
			//return 'method no exists: '.$tag_name.' tag: '.$tag;
		}
		//return the tag replacement thingy.
		$_return = $tagObj->$tag($params, $smarty);

		$view = geoView::getInstance();

		if (isset($view->geo_inc_files['addons'][$info->auth_tag][$addonName][$tag])) {
			//backwards compatibility from back when addon tags were "pre-loaded"
			$file = $view->geo_inc_files['addons'][$info->auth_tag][$addonName][$tag];

			$tpl_vars = (array)$view->addon_vars[$info->auth_tag][$addonName][$tag];
			$g_type = geoTemplate::ADDON;
			$g_resource = $addonName;

			//now let loadInternalTemplate() do rest of the work for us!
			$_return .= geoTemplate::loadInternalTemplate($params, $smarty, $file, $g_type, $g_resource, $tpl_vars);
		}
		if ($params['assign'] && strlen($_return)) {
			//make sure to only "assign" if the return is not empty..  don't want
			//to end up double-assigning something, which would result in it clearing
			//the first assignment..
			$smarty->assign($params['assign'], $_return);
			return '';
		}

		return $_return;
	}

	/**
	 * Used by updateTemplates() to copy over templates for given addon.
	 *
	 * @param string $name the addon name.
	 * @param string $type The type like main_page or external or whatever
	 * @param string $sub for recurive calls
	 * @since Version 5.0.1
	 */
	private function _templateCopy ($name, $type='main_page', $sub = '')
	{
		$templateFile = geoFile::getInstance(geoFile::TEMPLATES);
		$addonFile = geoFile::getInstance(geoFile::ADDON);
		$file = geoFile::getInstance(geoFile::BASE);

		$list = array_diff(scandir($addonFile->absolutize("$name/templates/$type/$sub")), array('.','..','attachments'));
		require_once ADMIN_DIR.'design.php';

		foreach ($list as $entry) {
			//copy each file over
			$from = $addonFile->absolutize("$name/templates/$type/{$sub}$entry");
			$to = $templateFile->absolutize("default/$type/{$sub}$entry");

			//create attachments
			if ($type == 'main_page' && is_dir($from)) {
				//it is folder, need to scan contents of folder and do the same
				return $this->_templateCopy($name, $type, $sub.$entry.'/');
			} else if ($type == 'main_page') {
				//it's a file!  scan it for attachments
				$tplContents = file_get_contents($from);
				$tpl_vars = DesignManage::scanForAttachments($tplContents);
				$tpl_vars['filename'] = $sub.$entry;

				$tpl = new geoTemplate(geoTemplate::ADMIN);
				$tpl->assign($tpl_vars);

				$contents = $tpl->fetch('design/files/modules_to_template.tpl');

				if (!$templateFile->fwrite("default/main_page/attachments/modules_to_template/{$sub}$entry.php",$contents)) {
					//error with saving default module to template attachments
					return false;
				}
			}
			//copy the file over
			if (!$file->copy($from, $to)) {
				//problem with copying one of the files/folders...
				return false;
			}
		}
		return true;
	}
}

