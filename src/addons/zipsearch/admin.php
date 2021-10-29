<?php
//addons/zipsearch/setup.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    16.09.0-106-ge989d1f
## 
##################################

# Zip/Postal Code Search
require_once ADDON_DIR . 'zipsearch/info.php';

class addon_zipsearch_admin extends addon_zipsearch_info
{
	public function init_pages ()
	{
		menu_page::addonAddPage('zipsearch_settings','','Settings',$this->name, 'fa-globe');
		menu_page::addonAddPage('insertZipData','','Import Zip Data',$this->name, 'fa-globe');
	}
	
	public function init_text($language_id)
	{
		$return_var['default_distance_mi'] = array (
			'name' => 'Distance dropdown label (miles)',
			'desc' => '',
			'type' => 'input',
			'default' => 'distance (in miles)'
		);
		$return_var['default_distance_km'] = array (
			'name' => 'Distance dropdown label (kilometers)',
			'desc' => '',
			'type' => 'input',
			'default' => 'distance (in kilometers)'
		);
		$return_var['tbl_head_distance_mi'] = array (
			'name' => 'Distance table header label (miles)',
			'desc' => '',
			'type' => 'input',
			'default' => 'distance (in miles)'
		);
		$return_var['tbl_head_distance_km'] = array (
			'name' => 'Distance table header label (kilometers)',
			'desc' => '',
			'type' => 'input',
			'default' => 'distance (in kilometers)'
		);
		$return_var['browse_label_distance_mi'] = array (
			'name' => 'Distance label for gallery/list view (miles)',
			'desc' => '',
			'type' => 'input',
			'default' => 'distance (in miles): '
		);
		$return_var['browse_label_distance_km'] = array (
			'name' => 'Distance label for gallery/list view (kilometers)',
			'desc' => '',
			'type' => 'input',
			'default' => 'distance (in kilometers): '
		);
		$return_var['listing_alert_within'] = array (
			'name' => 'Show listing alerts - "within"',
			'desc' => '',
			'type' => 'input',
			'default' => 'Within'
		);
		$return_var['listing_alert_of'] = array (
			'name' => 'Show listing alerts - "of"',
			'desc' => '',
			'type' => 'input',
			'default' => 'of'
		);
		$return_var['listing_alert_mi'] = array (
			'name' => 'Show listing alerts - "miles"',
			'desc' => '',
			'type' => 'input',
			'default' => 'miles'
		);
		$return_var['listing_alert_km'] = array (
			'name' => 'Show listing alerts - "kilometers"',
			'desc' => '',
			'type' => 'input',
			'default' => 'kilometers'
		);
		$return_var['listing_alert_basic_distance_header'] = array (
			'name' => 'Show listing alerts - distance label',
			'desc' => '',
			'type' => 'input',
			'default' => 'Distance'
		);
		
		return $return_var;
	}
	
	public function getOrderedTypes ()
	{
		$file = geoFile::getInstance('zipsearch');
		
		$file->jailTo(ADDON_DIR.'zipsearch/import_data/');
		
		$types_raw = $file->scandir('.',false,false,true);
		$types_ordered = $types = array();
		foreach ($types_raw as $type) {
			if (file_exists(ADDON_DIR.'zipsearch/import_data/'.$type.'/import.php')) {
				require_once ADDON_DIR.'zipsearch/import_data/'.$type.'/import.php';
				$className = "zipsearch_import_$type";
				if (class_exists($className, false)) {
					$typeObj = new $className;
					
					$order = (int)$typeObj->getOrder();
					
					$types_ordered[$order][$type] = $typeObj;
				} 
			}
		}
		
		ksort ($types_ordered);
		$types = array();
		foreach ($types_ordered as $this_types) {
			foreach ($this_types as $type => $obj) {
				$types[$type] = $obj;
			}
		}
		
		return $types;
	}
	
	public function display_zipsearch_settings ()
	{
		$admin = geoAdmin::getInstance();
		$reg = geoAddon::getRegistry($this->name);
		
		$tpl_vars = array();
		
		$tpl_vars['adminMsgs'] = geoAdmin::m();
		
		$tpl_vars['lastRun'] = $reg->lastRun;
		$tpl_vars['alreadyExisting'] = DataAccess::getInstance()->GetOne("SELECT count(*) FROM geodesic_zip_codes");
		
		$tpl_vars['enabled'] = $reg->enabled;
		$tpl_vars['units'] = $reg->units;
		$tpl_vars['search_method'] = $reg->search_method;
		$tpl_vars['hierarchical_trim'] = $reg->get('hierarchical_trim',3);
		
		$admin->setBodyTpl('admin/settings.tpl',$this->name)
			->v()->setBodyVar($tpl_vars);
	}
	
	public function update_zipsearch_settings ()
	{
		$reg = geoAddon::getRegistry($this->name);
		
		$reg->enabled = ($_POST['enabled'] == 1) ? 1 : 0;
		$reg->units = (isset($_POST['units']) && in_array($_POST['units'], array('M','km')))? $_POST['units'] : 'M';
		$reg->search_method = (isset($_POST['search_method']) && in_array($_POST['search_method'], array('exact','hierarchical')))? $_POST['search_method'] : 'exact';
		
		$reg->hierarchical_trim = ($_POST['hierarchical_trim'] > 0) ? intval($_POST['hierarchical_trim']) : 3;
		
		$reg->save();
		return true;
	}
	
	public function display_insertZipData()
	{
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$reg = geoAddon::getRegistry($this->name);
		
		$step = (isset($_GET['step']))? (int)$_GET['step'] : -1;
		
		$types = $this->getOrderedTypes();
		
		$currentType = (isset($_GET['currentType']) && isset($types[$_GET['currentType']]))? $_GET['currentType'] : '';
		
		$selectedTypes = (isset($_GET['selectedTypes']))? $_GET['selectedTypes'] : array();
		if (!is_array($selectedTypes)) {
			$selectedTypes = explode('|',$selectedTypes);
		}
		$selectedTypes = array_intersect($selectedTypes, array_keys($types));
		
		if (!$currentType && $selectedTypes) {
			$currentType = array_pop($selectedTypes);
		}
		
		$steps = $tpl_vars = array();
		if ($currentType) {
			$steps = $types[$currentType]->getSteps();
		}
		
		$tpl_vars['continue'] = 'Continue &rsaquo;&rsaquo;';
		$tpl_vars['lastRun'] = $reg->lastRun;
		$tpl_vars['currentType'] = $currentType;
		$tpl_vars['selectedTypes'] = implode('|', $selectedTypes);
		
		if ($step == -1) {
			$tpl_vars['nextStep'] = 0;
			$tpl_vars['choose'] = 1;
			$tpl_vars['continue'] = 'Start Import';
			$tpl_vars['types'] = $types;
			$tpl_vars['alreadyExisting'] = $db->GetOne("SELECT count(*) FROM geodesic_zip_codes");
		} else if ($step == 0) {
			$db->Execute("DROP TABLE IF EXISTS `geodesic_zip_codes`");
			//create table
			
			$sql = "
				CREATE TABLE `geodesic_zip_codes` (
				  `zipcode` varchar(5) NOT NULL default '',
				  `latitude` double NOT NULL default '0',
				  `longitude` double NOT NULL default '0',
				  KEY `zipcode` (`zipcode`),
				  KEY `latitude` (`latitude`),
				  KEY `longitude` (`longitude`)
				)";
			$db->Execute($sql);
			$tpl_vars['data'] = 'Zipcode Table Created.  Next it will import the zipcode data selected.';
			$tpl_vars['nextStep'] = 1;
		} else {
			//set timeout to not time out
			set_time_limit(0);
			if (!isset($steps[$step]) && $selectedTypes) {
				//go on to next type
				$tpl_vars['currentType'] = $currentType = array_pop($selectedTypes);
				$tpl_vars['selectedTypes'] = implode('|',$selectedTypes);
				$steps = $types[$currentType]->getSteps();
				$step = 1;
			}
			
			if (!isset($steps[$step])) {
				$tpl_vars['data'] = 'Zipcode Data Import is finished.  You do not need to run this import again unless updated zip data becomes available, or you wish to import different set of data.  If you need to remove the zip data from the database, simply uninstall the addon.';
				geoAdmin::m('Import of data finished successfully!', geoAdmin::SUCCESS);
				$tpl_vars['continue'] = false;
				$tpl_vars['alreadyExisting'] = $db->GetOne("SELECT count(*) FROM geodesic_zip_codes");
				$reg->lastRun = time();
				$reg->save();
			} else {
				$info = $steps[$step];
				$tpl_vars['type'] = $types[$currentType];
				//If db type is innodb, this will keep it from writing to disk every
				//single import, which should speed it up significantly
				$db->Execute('SET autocommit=0');
				$tpl_vars['data'] = 'Processing data from step <strong>'.$step.' of '.(count($steps)).'</strong> for '.$types[$currentType]->getLabel().'...'.$types[$currentType]->processStep($info);
				//For innodb, this will commit the changes
				$db->Execute('COMMIT');
				//and set it back to 1 for rest of the page
				$db->Execute('SET autocommit=1');
				$tpl_vars['nextStep'] = $step+1;
			}
		}
		$tpl_vars['adminMsgs'] = geoAdmin::m();
		$admin->setBodyTpl('admin/import.tpl', 'zipsearch')
			->v()->setBodyVar($tpl_vars);
	}
}

class zipsearch_import_parent
{
	/**
	 * The order in which to display this import type.
	 * @return int
	 */
	public function getOrder ()
	{
		return 0;
	}
	
	/**
	 * Whether or not to disable the check-box on the list of imports to run.
	 * @return bool
	 */
	public function disableCheck ()
	{
		return false;
	}
	
	/**
	 * Takes a file and splits it into queries, then stores each query in _queries
	 * @param String $filename
	 *  the _queries array.
	 */
	public function splitSqlFile($filename)
	{
		$handle = fopen($filename,'r');
		$queries = 0;
		$db = DataAccess::getInstance();
		if ($handle){
			$buffer = '';
			while (!feof($handle)){
				$this_buffer = fgets($handle, 4096);
				//$this_buffer = rtrim($buffer);
				if (substr(ltrim($this_buffer),0,1) == '#' || substr(ltrim($this_buffer),0,2) == '--'){
					//comment line
					continue;
				}
				$buffer .= $this_buffer;
				//$buffer = rtrim($buffer);
				if (substr(rtrim($buffer),-1) == ';'){
					//end of query, add query
					$db->Execute($buffer);
					$queries++;
					$buffer = '';
				}
			}
		}
		return $queries;
	}
	
	/**
	 * Get an array of steps, the index is important..  must be numeric indexes,
	 * start at 1 (NOT 0, step 0 would be skipped), and value should be useful
	 * to the processStep() function (for instance, the file name to import for
	 * this step, if there are multiple files used for the import)
	 * 
	 * @return array
	 */
	public function getSteps ()
	{
		//index for each step...  0 is used by system
		$file = geoFile::getInstance('zipsearch');
		
		$list = $file->scandir($this->getType().'/data/', false);
		
		$steps = array();
		$i = 1;
		foreach ($list as $step) {
			$steps[$i] = $step;
			$i++;
		}
		return $steps;
	}
	
	/**
	 * Process the given step, importing all the data for that step.
	 * 
	 * @param mixed $step The value for the current step, the value used in the
	 *   array returned by getSteps()
	 * @return string Extra info to display for this step, such as number of
	 *   entries imported on this step or something similar.
	 */
	public function processStep ($step)
	{
		$file = geoFile::getInstance('zipsearch');
		$sql_filename = $file->absolutize($this->getType().'/data/'.$step);
		
		$count = $this->splitSqlFile($sql_filename);
		
		return '<br /><br />Imported '.$count.' zip data entries.';
	}
	
	private $_insertCodeQuery;
	
	public $newCodes, $dupCodes;
	
	/**
	 * Utility function to add a postcode to the system, adding single post code at a time.
	 * 
	 * Checks for duplicates before adding zipcode, does not add duplicates.
	 * 
	 * @param string $postcode
	 * @param float $latitude
	 * @param float $longitude
	 */
	public function addPostcode ($postcode, $latitude, $longitude)
	{
		$db = DataAccess::getInstance();
		if (!isset($this->_insertCodeQuery)) {
			$this->_insertCodeQuery = $db->Prepare("INSERT INTO `geodesic_zip_codes` (`zipcode`, `latitude`, `longitude`) VALUES (?, ?, ?)");
			$this->newCodes = $this->dupCodes = 0;
		}
		
		$postcode = trim($postcode);
		$latitude = $latitude;
		$longitude = $longitude;
		
		if (!$postcode || !$latitude || !$longitude) {
			//invalid data?
			return false;
		}
		
		//make sure there are not any duplicates
		$existing = (int)$db->GetOne("SELECT count(*) FROM `geodesic_zip_codes` WHERE `zipcode`=?", array($postcode));
		if ($existing > 0) {
			$this->dupCodes++;
			return false;
		}
		
		$db->Execute($this->_insertCodeQuery, array ($postcode, $latitude, $longitude));
		$this->newCodes++;
	}
}