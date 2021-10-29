<?php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.0-13-g6eae54b
## 
##################################

class geoImport
{
	private $_importType;
	const USER_IMPORTTYPE = 1;
	const LISTING_IMPORTTYPE = 2;
	
	private $_importItemList = array();
	private $_importItemNames = array();
	
	private $_sourceFile;
	const CSV_FILETYPE = 'csv';
	const XML_FILETYPE = 'xml';
	
	private $_fileHandle = null;
	private $_filename;
	private $_filetype;
	private $_fileIsOpen = false;
	
	public $settings = array();
	
	private $_defaultValues = array();
	
	public static $crosstalk = array(); //a way for individual ImportItems to communicate with each other (for instance, because password needs to know the username for hashing)
	
	public function __construct($filename, $filetype)
	{
		$this->_importType = self::USER_IMPORTTYPE; // only user imports...for now!
		if(!is_readable($filename)) {
			//TODO: whatever other checks are needed to make sure this is a valid file and can be opened
			throw new Exception('cannot read file: '.$filename);
			return;
		}
		$this->_filename = $filename;
		
		if(!in_array($filetype, array(self::CSV_FILETYPE, self::XML_FILETYPE))) {
			throw new Exception('bad file type: '.$filetype);
			return;
		}
		if(pathinfo($filename, PATHINFO_EXTENSION) !== $filetype) {
			throw new Exception('File extension does not match chosen file type.');
			return;
		}
		$this->_filetype = $filetype;
	}
	
	private function _openFile()
	{
		if($this->_fileIsOpen) {
			return true;
		}
		
		$this->_fileHandle = fopen($this->_filename, 'r');
		if($this->_fileHandle) {
			$this->_fileIsOpen = true;
			return true;
		} else {
			$this->_fileIsOpen = false;
			trigger_error('ERROR IMPORT: could not fopen source file: '.$this->_filename);
			return false;
		}
	}
	
	private function _closeFile()
	{
		if(!$this->_fileIsOpen) {
			return true;
		}
		
		fclose($this->_fileHandle);
		$this->_fileIsOpen = false;
		return true;
	}
	
	public function debug()
	{
		foreach($this->_importItemList as $index => $name) {
			$ret .= $index . ': '.$this->_importItemNames[$index].' || default: '.$this->_defaultValues[$index].'<br />';
		}
		return $ret;
	}
	
	public function addImportItem($importItem, $defaultValue='', $index=false)
	{
		if(is_numeric($index)) {
			$this->_importItemList[intval($index)] = $importItem;
			$this->_importItemNames[intval($index)] = $importItem->getSaveName();
			$this->_defaultValues[intval($index)] = $defaultValue;
		} else {
			$this->_importItemList[] = $importItem;
			$this->_importItemNames[] = $importItem->getSaveName();
			$this->_defaultValues[] = $defaultValue;
		}
	}
	
	public function getImportItemAtIndex($index)
	{
		return $this->_importItemList[$index];
	}
	
	
	public function processFile()
	{
		//get rid of PHP's time limits, since this may take a while
		set_time_limit(0);
		
		
		if($this->_filetype === self::CSV_FILETYPE && $this->settings['csv_skipfirst']) {
			//skip first row
			$this->_tokenizeGroup();
		}
		
		$groupsCompleted = 0;
		while($result = $this->_processNextGroup()) {
			if($result === 'EOF') {
				break;
			} elseif ($result === 'SKIP') {
				continue;
			}
			$groupsCompleted++;
		}
		return $groupsCompleted;
	}
	
	public static $tableChanges = array(); //speed-up array to combine all item updates into a single SQL query
	private function _processNextGroup()
	{
		//clear crosstalk array for the new group
		self::$crosstalk = array();
		self::$tableChanges = array();
		
		$tokens = $this->_tokenizeGroup();
		if($tokens === 'EOF') {
			return 'EOF';
		}
		
		//make sure this group isn't entirely empty (like a junk csv row of entirely commas left over at the bottom of an Excel file)
		$validGroup = false;
		foreach($tokens as $value) {
			if(strlen(trim($value)) > 0) {
				//at least one value from this group contains SOMETHING. carry on
				$validGroup = true;
				break;
			}
		}
		if(!$validGroup) {
			//this is a blank group (probably csv trash). skip it.
			return 'SKIP';
		}
		
		$groupId = $this->_readyDatabaseForGroup();
		if(!$groupId) {
			geoAdmin::m('readyDB() failed with sql error: '.DataAccess::getInstance()->ErrorMsg());
			return false;
		}
		
		foreach($tokens as $key => $value) {
			//get the correct ImportItem and pass this value through it for processing
			$importItem = $this->getImportItemAtIndex($key);
			if(!$importItem) {
				geoAdmin::m('could not get ImportItem',geoAdmin::ERROR);
				trigger_error('ERROR IMPORT: could not retrieve ImportItem');
				return false;
			}
			
			if(!$value) {
				$value = $this->_defaultValues[$key];
			}
			
			if(!$importItem->processToken($value, $groupId)) {
				trigger_error('ERROR IMPORT: failed processToken. skipping group id: '.$groupId);
				$this->_unReadyDatabaseForGroup($groupId);
				return 'SKIP';
			}
		}
		
		return $this->_updateDatabaseForGroup($groupId);
		
	}
	
	/**
	* Gets a "group" from the source file and turns it into an array of "tokens" (one "token" is a discreet data point).
	* For example, a single row of a CSV would be a "group," and each specific value in that group becomes a "token"
	* @return array a list of the tokens from the next valid group 
	*/
	private function _tokenizeGroup()
	{
		if(!$this->_openFile()) {
			//something is wrong...
			trigger_error('ERROR IMPORT: could not open file for tokenizing');
			return false;
		}
		
		if($this->_filetype === self::CSV_FILETYPE) {
			$tokens = fgetcsv($this->_fileHandle, null, $this->settings['csv_delimiter'], $this->settings['csv_encapsulation']);
		} elseif (($this->_filetype === self::XML_FILETYPE)) {
			//TODO: implement
		} else {
			trigger_error('ERROR IMPORT: did not recognize filetype while tokenizing');
			return false;
		}
		if(!$tokens) {
			//End of File
			return 'EOF';
		}
		
		return $tokens;
	}
	
	public $csvHeaders = false;
	public function getDemoTokens()
	{
		if($this->_filetype === self::CSV_FILETYPE && $this->settings['csv_skipfirst']) {
			//throw away the first tokenized group -- it is a header row
			$this->csvHeaders = $this->_tokenizeGroup();
		}
		return $this->_tokenizeGroup();
	}
	
	private $_defaultUserGroup = array();
	/**
	* Do the initial INSERT query for a group, with minimal/placeholder data, so that the actual ImportItems can just UPDATE it
	* @return int $groupId the ID of the inserted DB row
	*/
	private function _readyDatabaseForGroup()
	{
		$db = DataAccess::getInstance();
		
		switch($this->_importType) {
			case self::USER_IMPORTTYPE:
				//first clean any "pending" users from previous uploads
				$cleanMe = $db->Execute("SELECT `id` FROM `geodesic_logins` WHERE `username` = 'IMPORT_PENDING'");
				foreach($cleanMe as $row) {
					$db->Execute("DELETE FROM `geodesic_logins` WHERE `id` = ?", array($row['id']));
					$db->Execute("DELETE FROM `geodesic_userdata` WHERE `id` = ?", array($row['id']));
					$db->Execute("DELETE FROM `geodesic_user_groups_price_plans` WHERE `id` = ?", array($row['id']));
				}
			
				if(!$db->Execute("INSERT INTO `geodesic_logins` (`username`) VALUES ('IMPORT_PENDING')")) return false;
				$id = $db->Insert_ID();
				if(!$db->Execute("INSERT INTO `geodesic_userdata` (`id`, `username`, `date_joined`) VALUES (?, 'IMPORT_PENDING', ?)", array($id, geoUtil::time()))) return false;
				
				if(!$this->_defaultUserGroup['id']) {
					//get the site default user group
					$dg = $db->GetRow("SELECT * FROM `geodesic_groups` WHERE `default_group` = 1");
					$this->_defaultUserGroup['id'] = $dg['group_id'];
					$this->_defaultUserGroup['class_pp'] = $dg['price_plan_id'];
					$this->_defaultUserGroup['auc_pp'] = $dg['auction_price_plan_id'];
				}
				if(!$db->Execute("INSERT INTO `geodesic_user_groups_price_plans` (`id`, `group_id`, `price_plan_id`, `auction_price_plan_id`) VALUES (?,?,?,?)",
						 array($id, $this->_defaultUserGroup['id'], $this->_defaultUserGroup['class_pp'], $this->_defaultUserGroup['auc_pp']))) return false;
				break;
			case self::LISTING_IMPORTTYPE:
				trigger_error('DEBUG IMPORT: WARNING: LISTING import type not implemented yet');
				if(!$db->Execute("INSERT INTO `geodesic_classifieds` (`title`) VALUES ('IMPORT_PENDING')")) return false;
				$id = $db->Insert_ID();
				break;
			default:
				trigger_error('ERROR IMPORT: not a valid import type');
				return false;
		}
		return $id;
	}

	/**
	 * undoes everything done in _readyDatabaseForGroup(). Useful for cleaning up after an error.
	 * @param int $groupId
	 */
	private function _unReadyDatabaseForGroup($groupId)
	{
		$db = DataAccess::getInstance();
		
		switch($this->_importType) {
			case self::USER_IMPORTTYPE:
				$db->Execute("DELETE FROM `geodesic_logins` WHERE `id` = ?", array($groupId));
				$db->Execute("DELETE FROM `geodesic_userdata` WHERE `id` = ?", array($groupId));
				$db->Execute("DELETE FROM `geodesic_user_groups_price_plans` WHERE `id` = ?", array($groupId));
				break;
			case self::LISTING_IMPORTTYPE:
				trigger_error('DEBUG IMPORT: WARNING: LISTING import type not implemented yet');
				$db->Execute("DELETE FROM `geodesic_classifieds` WHERE `title` = 'IMPORT_PENDING'");
				break;
			default:
				break;
		}
		return true;
	}
	
	/**
	 * Most ImportItems populate a variable that is referenced here to create a single UPDATE query per user/listing
	 * @param int $groupId
	 * @return bool success
	 */
	private function _updateDatabaseForGroup($groupId)
	{
		$db = DataAccess::getInstance();
		if($this->_importType === self::USER_IMPORTTYPE) {
			if(self::$tableChanges['userdata']) {
				$updateUserdata = "UPDATE `geodesic_userdata` SET ";
				$sqls = array();
				foreach(self::$tableChanges['userdata'] as $item => $sql) {
					$sqls[] = $sql;
				}
				$updateUserdata .= implode(',', $sqls)." WHERE `id` = ".$groupId;
				if(!$db->Execute($updateUserdata)) {
					trigger_error('ERROR IMPORT: userdata query failed and the query is:<br>'.$updateUserdata);
					return false;
				}
			}
			
			if(self::$tableChanges['logins']) {
				$updateLogins = "UPDATE `geodesic_logins` SET ";
				$sqls = array();
				foreach(self::$tableChanges['logins'] as $item => $sql) {
					$sqls[] = $sql;
				}
				$updateLogins .= implode(',', $sqls)." WHERE `id` = ".$groupId;
				if(!$db->Execute($updateLogins)) {
					trigger_error('ERROR IMPORT: logins query failed');
					return false;
				}
			}
			
			if(self::$tableChanges['ugpp']) {
				$updateGroupsPlans = "UPDATE `geodesic_user_groups_price_plans` SET ";
				$sqls = array();
				foreach(self::$tableChanges['ugpp'] as $item => $sql) {
					$sqls[] = $sql;
				}
				$updateGroupsPlans .= implode(',', $sqls)." WHERE `id` = ".$groupId;
				if(!$db->Execute($updateGroupsPlans)) {
					trigger_error('ERROR IMPORT: ugpp query failed and the query is:<br>'.$updateGroupsPlans);
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * save information about this import to the DB so it can be recalled on a future pageload.
	 * Note that this cleans up after the import object, so it should typically be the last import-related thing done on a pageload
	 */
	public function store()
	{
		$session = geoSession::getInstance();
		
		//save filename, settings array, crosstalk array to session registry
		$session->set('importer_filename', $this->_filename);
		$session->set('importer_filetype', $this->_filetype);
		$session->set('importer_settings', $this->settings);
		$session->set('importer_crosstalk', self::$crosstalk);
		$session->set('importer_items', $this->_importItemNames);
		$session->set('importer_defaults', $this->_defaultValues);
		$session->serialize();
		
		//clean up file handle
		$this->_closeFile();
		
		//invalidate singleton cache, to make sure object is reinitialized if called again
		self::$_import = null;
	}
	
	/**
	 * remove information about this Import from session db
	 */
	public function purge()
	{
		$session = geoSession::getInstance();
		$session->set('importer_filename', false);
		$session->set('importer_filetype', false);
		$session->set('importer_settings', false);
		$session->set('importer_crosstalk', false);
		$session->set('importer_items', false);
		$session->set('importer_defaults', false);
		$session->serialize();
	}

	/**
	 * Singleton cache var
	 * @var geoImport
	 * @internal
	 */
	private static $_import;
	
	/**
	 * get a saved import
	 */
	public static function getInstance($filename='', $filetype='')
	{
		if(self::$_import) {
			return self::$_import;
		}
		
		$session = geoSession::getInstance();
		
		//if retreiving a saved session, get filename/type from registry
		if(!$filename) {
			$filename = $session->get('importer_filename');
		}
		if(!$filetype) {
			$filetype = $session->get('importer_filetype');
		}
		if(!$filename || !$filetype) {
			trigger_error('ERROR IMPORT: missing filename -- could not get Import object');
			return false;
		}
		
		//make a new geoImport object or die trying
		try {
			$import = new geoImport($filename, $filetype);
		} catch(Exception $e) {
			trigger_error('Could not open Import object, with error: '.$e->getMessage());
			if(defined('IN_ADMIN')) {
				geoAdmin::m('Error getting import object: '.$e->getMessage(), geoAdmin::ERROR);
				
			}
			return false;
		}
		
		//add settings, crosstalk, and import items back in, if saved
		$settings = $session->get('importer_settings');
		$crosstalk = $session->get('importer_crosstalk');
		$itemNames = $session->get('importer_items');
		$defaults = $session->get('importer_defaults');
		
		if($settings) {
			$import->settings = $settings;
		}
		if($crosstalk) {
			self::$crosstalk = $crosstalk;
		}
		if($itemNames) {
			foreach($itemNames as $name) {
				$import->addImportItem($import->getImportItemByName($name));
			}
		}
		if($defaults) {
			$import->_defaultValues = $defaults;
		}
		
		
		
		//Singleton caching
		self::$_import = $import;
		return self::$_import;
	}


	/**
	 * finds all the valid import item files throughout the system and loads them up for use
	 */
	public function getAllImportItems()
	{
		//first, set up the directory names to look at
		$type_dir = ($this->_importType == self::USER_IMPORTTYPE) ? 'user/' : 'listing/';
		$main_dir = CLASSES_DIR.'php5_classes/import_items/'.$type_dir;
		
		//use geoFile fanciness to grab filenames from the folder
		$f = geoFile::getInstance('import_main');
		$dir_contents = $f->scandir($main_dir);
		foreach($dir_contents as $filename) {
			$files[] = $main_dir.$filename;
		}
		
		//allow addons to include their own import items
		$addon = geoAddon::getInstance();
		$enabledAddons = $addon->getEnabledList();
		foreach($enabledAddons as $name => $info) {
			$addon_dir = ADDON_DIR.$name.'/import_items/'.$type_dir;
			if(is_dir($addon_dir)) {
				$dir_contents = $f->scandir($addon_dir);
				foreach($dir_contents as $filename) {
					$files[] = $addon_dir.$filename;
				}
			}
		}
		
		//create ImportItems from valid files in the list (and throw out any invalid ones)
		$importItems = array();
		foreach($files as $filename) {
			include_once($filename);
			$typename = pathinfo($filename, PATHINFO_FILENAME);
			$classname = $typename.'ImportItem';
			$item = new $classname;
			
			if(!is_object($item)) {
				trigger_error('DEBUG IMPORT: tried to use an ImportItem with a bad class: '.$filename);
				continue;
			}
			$importItems[$typename] = $item;
		}
		
		//sort items acording to their internal displayOrder properties
		uasort($importItems, array($this, "itemSort"));
		return $importItems;
	}
	
	/**
	 * custom sort function for importItems
	 * @param geoImportItem $a
	 * @param geoImportItem $b
	 */
	private function itemSort($a, $b) {
		$one = $a->displayOrder;
		$two = $b->displayOrder;
		if($one == $two) {
			return 0;
		} 
		elseif($one < $two) {
			return -1;
		} else {
			return 1;
		}
	}

	public function getImportItemByName($name)
	{
		//first, check the main directory
		$type_dir = ($this->_importType == self::USER_IMPORTTYPE) ? 'user/' : 'listing/';
		$main_dir = CLASSES_DIR.'php5_classes/import_items/'.$type_dir;
		$filename = $name.'.php';
		if(is_file($main_dir.$filename)) {
			include_once($main_dir.$filename);
			$classname = $name.'ImportItem';
			$item = new $classname;
			return $item;
		}
		//if not found yet, look through addons
		$addon = geoAddon::getInstance();
		$enabledAddons = $addon->getEnabledList();
		foreach($enabledAddons as $addonName => $info) {
			$addon_dir = ADDON_DIR.$addonName.'/import_items/'.$type_dir;
			if(is_file($addon_dir.$filename)) {
				include_once($addon_dir.$filename);
				$classname = $name.'ImportItem';
				$item = new $classname;
				return $item;
			}
		}
		//have not found an item by the requested name
		trigger_error('ERROR IMPORT: asked for ImportItem with name '.$name.' but didn\'t find one');
		return false;
	}
}