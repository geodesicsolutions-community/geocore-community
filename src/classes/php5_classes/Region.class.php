<?php
//Region.class.php
/**
 * Holds the geoRegion class.
 * 
 * @package System
 * @since Version 4.0.0
 */
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    16.09.0-54-gaa90d11
## 
##################################

/**
 * Stuff for regions and sub-regions.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoRegion
{
	/**
	 * Internal use
	 * @internal
	 * @var Array
	 */
	private $_levels;
	
	/**
	 * Removes the specified region and all sub-regions.  Or if dryRun is true (which
	 * is default), will only do a dry-run and return an array indicating how many
	 * at each "level" would be affected by this deletion.
	 * 
	 * This will remove sub-regions recursively as well.
	 * 
	 * @param array|int $regions array of regions to remove, or single region ID to remove
	 * @param bool $dryRun Set to false to go forward with the removal.
	 * @return bool|array If dryRun is true, returns an array indicating how many
	 *   of each level of regions will be removed. If dryRun is false, simply returns
	 *   bool true if removal successful, false otherwise.
	 * @since Version 7.0.0
	 */
	public static function remove ($regions, $dryRun = true)
	{
		$db = DataAccess::getInstance();
		
		$info = array();
		
		if (!is_array($regions)) {
			$regions = array($regions);
		}
		
		foreach ($regions as $region_id) {
			self::_remove($region_id, $dryRun, $info);
		}
		
		if ($dryRun) {
			return array_reverse($info, true);
		} else {
			//check for and remove any now-empty Levels (since they will be added anew when/if corresponding data is re-added)
			$newMax = $db->GetOne("SELECT MAX(`level`) FROM ".geoTables::region);
			if($newMax) {
				$db->Execute("DELETE FROM ".geoTables::region_level." WHERE `level` > ?", array($newMax));
				$db->Execute("DELETE FROM ".geoTables::region_level_labels." WHERE `level` > ?", array($newMax));
			}
			return true;
		}
	}
	
	/**
	 * Used internally to recursively remove a region
	 * 
	 * @param int $region_id
	 * @param bool $dryRun
	 * @param array $info
	 * @since Version 7.0.0
	 */
	private static function _remove ($region_id, $dryRun, &$info)
	{
		$db = DataAccess::getInstance();
		
		$children = $db->Execute("SELECT `id` FROM ".geoTables::region." WHERE `parent`=?", array($region_id));
		foreach ($children as $row) {
			self::_remove($row['id'], $dryRun, $info);
		}
		if ($dryRun) {
			//just getting info...
			$region = $db->GetRow("SELECT * FROM ".geoTables::region." WHERE `id`=?", array($region_id));
			if ($region) {
				if (isset($info[$region['level']])) {
					$info[$region['level']]++;
				} else {
					$info[$region['level']]=1;
				}
			}
		} else {
			//just plain delete
			$db->Execute("DELETE FROM ".geoTables::region_languages." WHERE `id`=?",array($region_id));
			$db->Execute("DELETE FROM ".geoTables::region." WHERE `id`=?",array($region_id));
			$db->Execute("UPDATE ".geoTables::listing_regions." SET `region` = 0 WHERE `region` = ?", array($region_id));
		}
	}
	
	/**
	 * Gets the fields as used in the geoFields class when getting default fields.
	 * 
	 * @return array
	 * @since Version 7.0.0
	 */
	public function getRegionFields ()
	{
		$levels = $this->getLevels();
		trigger_error('DEBUG STATS: After getting levels.');
		$fields = array();
		foreach ($levels as $level) {
			if (!$level['use_label']) {
				//if no label, nothing to put in header, so don't show as option
				continue;
			}
			
			$index = 'region_level_'.$level['level'];
			$label = "Region Level {$level['level']} ({$level['label']}) [Example: <strong>{$level['sample']}</strong>]";
			
			$dependencies = array();
			for($i = $level['level'] - 1; $i > 0; $i--) {
				$dependencies['enabled'][] = 'fields_regions_region_level_'.$i.'_is_enabled';
				$dependencies['required'][] = 'fields_regions_region_level_'.$i.'_is_required';
			}
			
			$fields[$index] = array (
				'label' => $label,
				'type' => 'dropdown',
				'skipData' => ($level['level'] == 1) ? array() : array ('is_editable'),
				'dependencies' => $dependencies
			);
		}
		$fields['location_breadcrumb'] = array(
			'label' => 'Location Breadcrumb',
			'type' => 'other',
			'type_label' => 'Display Only',
			'skipData' => array('is_required', 'is_editable'),
			'skipLocations' => array('search_fields')
		);
		return $fields;
	}
	
	/**
	 * Gets all the region levels and their settings in associative array
	 * 
	 * @return array
	 * @since Version 7.0.0
	 */
	public function getLevels ()
	{
		if (!isset($this->_levels)) {
			$db = DataAccess::getInstance();
		
			//first get the deepest level that is in existance!
			//$maxLevels = $db->GetOne("SELECT r.level FROM ".geoTables::region." r, ".geoTables::region_languages." l WHERE l.id=r.id AND l.language_id=1 ORDER BY r.level DESC, r.display_order ASC");
			$maxLevels = $this->getMaxLevel();
			trigger_error('DEBUG STATS: After getting max levels, which is '.$maxLevels);
			
			if (defined('IN_ADMIN')) {
				//now randomly select a region from that lowest level
				//TODO: if this becomes real slow for large numbers of regions, there are faster ways to randomize than ORDER BY RAND()
				$region = $db->GetRow("SELECT * FROM ".geoTables::region." r, ".geoTables::region_languages." l WHERE l.id=r.id AND l.language_id=1 AND r.level = ? ORDER BY RAND()",array($maxLevels));
			
				//then get all the parents
				$regions = $this->getParents($region['id'], true);
			
				//now loop through those parents and set up defaults for each level
				$levels = array();
				foreach ($regions as $row) {
					$levels[$row['level']] = array(
						'level' => $row['level'], 'region_type'=>'other', 'type_label' => 'Other', 'use_label'=>'no','sample'=>$row['name'],
					);
				}
				unset ($regions);
			} else {
				//Not in the admin panel, do not bother populating the sample, just set up some defaults
				$levels = array();
				for ($level=1; $level<=$maxLevels; $level++) {
					$levels[$level] = array (
						'level' => $level, 'region_type' => 'other', 'type_label' => 'Other', 'use_label' => 'no', 'sample' => '',
					);
				}
			}
		
			//Now go get all the real settings, and over-write the defaults
			$rows = $db->GetAll("SELECT * FROM ".geoTables::region_level." ORDER BY `level`");
			foreach ($rows as $row) {
				if ($row['level'] > $maxLevels) {
					//there are levels defined that don't have regions, don't bother displaying
					break;
				}
				if ($row['use_label']=='yes') {
					//get the labels
					$row['labels'] = $this->getLevelLabels($row['level']);
					//set the language 1 as the "label" for easy access
					$row['label'] = $row['labels'][1];
				}
				$row['sample'] = $levels[$row['level']]['sample'];
				$row['type_label'] = ($row['region_type']==='state/province')? 'State or Province' : ucwords($row['region_type']);
					
				$levels[$row['level']] = $row;
			}
			$this->_levels = $levels;
		}
		return $this->_levels;
	}
	
	/**
	 * Gets the labels for all the different languages for the given level.  Note
	 * that this takes care of DB encoding, all labels are in un-encoded format.
	 * 
	 * @param int $level
	 * @return boolean|array The array of labels, or false on error.
	 * @since Version 7.0.0
	 */
	public function getLevelLabels ($level)
	{
		$db = DataAccess::getInstance();
		$level = (int)$level;
		if (!$level) {
			//level not valid
			return false;
		}
		$rows = $db->GetAll("SELECT * FROM ".geoTables::region_level_labels." WHERE `level`=?",array($level));
		//format it so index is the level, plus pre-un-fromdb it
		$labels = array();
		foreach ($rows as $row) {
			$labels[$row['language_id']] = geoString::fromDB($row['label']);
		}
		return $labels;
	}
	
	/**
	 * Gets a specific level, this is like calling getLevels but only returns a
	 * specific level.
	 * 
	 * @param int $level
	 * @param int $language_id Will fill in the label with the one for the specified
	 *   language ID, if the level id labeled.
	 * @return boolean|array The array of level information or bool false on error.
	 * @since Version 7.0.0
	 */
	public function getLevel ($level, $language_id=1)
	{
		//make sure levels are populated
		$this->getLevels();
		
		if (!isset($this->_levels[$level])) {
			//not a good level
			return false;
		}
		
		$level = $this->_levels[$level];
		if (isset($level['labels'][$language_id])) {
			$level['label'] = $level['labels'][$language_id];
		}
		return $level;
	}
	
	/**
	 * Get the parent regions for the specified region ID in an array.
	 * 
	 * @param int $region_id
	 * @param bool $use_bottom If true, will also return the requested region info
	 *   as part of the array of parent regions, handy for things like displaying
	 *   the full breadcrumb.
	 * @return boolean|array Returns an array with all the parent region info, the
	 *   array index is the parent region level and the info is same as returned
	 *   by {@see geoRegion::getRegioninfo()}
	 * @since Version 7.0.0
	 */
	public function getParents ($region_id, $use_bottom = false)
	{
		$region_id = (int)$region_id;
		if (!$region_id) {
			//failsafe
			return false;
		}
		
		$regions = array();
		$regionInfo = $this->getRegionInfo($region_id);
		
		while ($regionInfo) {
			if ($regionInfo['id']!=$region_id || $use_bottom) {
				//only add parents
				$regions[] = $regionInfo;
			}
			if ($regionInfo['parent']) {
				$regionInfo = $this->getRegionInfo($regionInfo['parent']);
			} else {
				$regionInfo = false;
			}
		}
		
		return array_reverse($regions);
	}
	
	/**
	 * Gets info about a region
	 * 
	 * @param int $region_id
	 * @return array|bool Returns info about region, or false on error.
	 * @since Version 7.0.0
	 */
	public function getRegionInfo ($region_id)
	{
		$db = DataAccess::getInstance();
		$region_id = (int)$region_id;
		$language_id = (int)geoSession::getInstance()->getLanguage();
	
		$row = $db->GetRow("SELECT * FROM ".geoTables::region." r, ".geoTables::region_languages." l WHERE l.id=r.id AND l.language_id={$language_id} AND r.id=?",array($region_id));
	
		//unescape name
		$row['name'] = geoString::fromDB($row['name']);
	
		return $row;
	}
	
	/**
	 * Gets the maximum level for regions found in the system.
	 * 
	 * @param bool $onlyEnabled If true, gets the max level when you take into
	 *   account if it is enabled or not.
	 * @return int Max number of levels found with regions in them.
	 */
	public function getMaxLevel ($onlyEnabled = false)
	{
		$db = DataAccess::getInstance();
		$query = "SELECT `level` FROM ".geoTables::region;
		if ($onlyEnabled) {
			//do a different way
			$query .= " WHERE `enabled`='yes'";
		}
		$query .= " ORDER BY `level` DESC";
		return (int)$db->GetOne($query);
	}
	
	/**
	 * Holds results of RadiusAssistant()
	 * @var float $max_latitude
	 * @var float $min_latitude
	 * @var float $max_longitude
	 * @var float $min_longitude
	 */
	public static $max_latitude,$min_latitude,$max_longitude,$min_longitude;
	/**
	 * Used by the search class.
	 *
	 * @param float $Latitude
	 * @param float $Longitude
	 * @param int $distance
	 * @param string $units
	 */
	public static function RadiusAssistant ($Latitude, $Longitude, $distance, $units = geoNumber::UNITS_MILES)
	{		
		self::$max_latitude = geoNumber::lat2($Latitude, $Longitude, $distance, 0, $units);
		self::$min_latitude = geoNumber::lat2($Latitude, $Longitude, $distance, 180, $units);
		
		self::$max_longitude = geoNumber::long2($Latitude, $Longitude, $distance, 90, $units);
		self::$min_longitude = geoNumber::long2($Latitude, $Longitude, $distance, 270, $units);
		
		//check for pole crossings
		//if the search box crosses a pole, stop there
		
		if (round($Longitude - (geoNumber::long2($Latitude, $Longitude, $distance, 0, $units))) != 0 ) {
			//if long changes signs on a bearing of 0(north), we have crossed the north pole. set max lat to 90 (north pole)
			self::$max_latitude = 90;
		}
		if (round($Longitude - (geoNumber::long2($Latitude, $Longitude, $distance, 180, $units))) != 0 ) {
			//same thing, but in the other direction and for the south pole. set min lat to -90
			self::$min_latitude = -90;
		}
		
		return;
		
		//old way, not as accurate the further away from the equator it gets...
		/*
		$EQUATOR_LAT_MILE = 69.172; //distance in miles of one degree of latitude at the equator
		$EQUATOR_LAT_KM = 111.325; //the above measurement, in km
		$equatorLat = $EQUATOR_LAT_MILE; //use miles (might add a switch for this later)
		
		self::$max_latitude = $Latitude + $Miles / $equatorLat;
		self::$min_latitude = $Latitude - (self::$max_latitude - $Latitude);
		self::$max_longitude = $Longitude + $Miles / (cos(self::$min_latitude * M_PI / 180) * $equatorLat);
		self::$min_longitude = $Longitude - (self::$max_longitude - $Longitude);
		*/
	}
	
	/**
	 * Get instance of region class.
	 *
	 * @return geoRegion
	 */
	public static function getInstance ()
	{
		return Singleton::getInstance('geoRegion');
	}
	
	/**
	 * Whether or not the states will depend on what country is selected.
	 * 
	 *
	 * @deprecated 7.0.0 -- will always return false
	 * @return bool
	 */
	public function isFancy ()
	{
		return false;
	}
	
	/**
	 * Gets the number of sub-regions attached to the given region.
	 * 
	 * @param int $region_id
	 * @return int
	 */
	public function getSubRegionCount ($region_id)
	{
		$db = DataAccess::getInstance();
		$sql = "SELECT COUNT(*) amount FROM ".geoTables::region." WHERE `parent`=? AND `enabled` = 'yes'";
		$count = $db->GetOne($sql,array($region_id));
		if($count===false) {
			trigger_error('ERROR SQL: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return 0;
		}
		return $count;
	}
	
	/**
	 * Gets the number of main regions.
	 * @return int Number of regions found.
	 */
	public function getRegionCount ()
	{
		$db = DataAccess::getInstance();
		$sql = "SELECT COUNT(*) count FROM ".geoTables::region." WHERE `level` = 1 AND `enabled` = 'yes'";
		$count = $db->GetOne($sql);
		if($count===false) {
			trigger_error('ERROR SQL: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return 0;
		}
		return $count;
	}
	

	

	/**
	 * Gets an array of regions with parent=0. Mostly for use in / formated for GeoNav addon
	 * @param bool $withDisabled if true, also include disabled regions
	 * @return array
	 */
	public static function getTopLevelRegions($withDisabled=false)
	{
		$db = DataAccess::getInstance();
		$sql = "SELECT * FROM ".geoTables::region." as r, ".geoTables::region_languages." as l, where r.id=l.id AND r.parent=0";
		if(!$withDisabled) {
			$sql .= " AND r.enabled='yes' ";
		}
		$sql .= " AND l.language_id = ? ORDER BY r.display_order, l.name";
		$language = (defined('IN_ADMIN')) ? 1 : $db->getLanguage();
		$result = $db->Execute($sql, array($language));
		$regions = array();
		while($region = $result->FetchRow()) {
			$regions[$region['id']] = array(
				'name' => geoString::fromDB($region['name']),
				'level' => $region['level'],
				'enabled' => $region['enabled']
			);
		}
		return $regions;
	}
	
	
	
	/**
	 * Used to display a series of dropdowns that allow selecting a region
	 * @param String $fieldName name to use for form fields created by this method
	 * @param Array $prevalue Array of starting values for the dropdowns. Region IDs keyed by Level.
	 * @param int $maxLevel The highest level dropdown to show, based on settings specific to where selector is being used
	 * @param bool $required If true, "required field" CSS styling will be added to this selector
	 * @param bool $skipEmptyRegions If true, regions that do not contain at least one listing will be omitted from the selector
	 * @return String
	 */
	public static function regionSelector($fieldName, $prevalue=array(), $maxLevel=false, $required=false, $skipEmptyRegions=false)
	{
		if($maxLevel === 0) {
			//maxLevel IS set, and IS 0 -- there are no levels enabled! show nothing!
			return false;
		}
		$tpl_vars = self::getRegionsFromParent($fieldName, 0, $prevalue, $maxLevel, $skipEmptyRegions);
		if($tpl_vars === false) {
			//returned false from getting top-level regions. nothing to show
			return false;
		}
		$tpl = new geoTemplate('system','classes');
		$tpl->assign($tpl_vars);
		
		//figure out which levels to show "fake" boxes for  ("always_show" is on and does not have a prevalue)
		$db = DataAccess::getInstance();
		$fakeLevels = array();
		$sql = "SELECT r.`level` as lvl, `label`, `use_label` FROM ".geoTables::region_level." AS r, ".geoTables::region_level_labels." AS l WHERE r.level=l.level AND `always_show` = 'yes' AND r.`level` > 1 AND l.`language_id` = ?";
		if($maxLevel) {
			$sql .= " AND r.`level` <= '".$maxLevel."'";
		}
		$result = $db->Execute($sql, array($db->getLanguage()));
		if($result) {
			while($l = $result->FetchRow()) {
				if(!$prevalue[$l['lvl']]) {
					$fakeLevels[] = array('id' => $l['lvl'], 'use_label' => $l['use_label'], 'label' => geoString::fromDB($l['label']));
				}
			}
		}
		$tpl->assign('fakeLevels',$fakeLevels);
		
		if (strpos($fieldName, 'additional_regions')===false && $fieldName !== "locations") {
			//cheat and only use the label from the listing process
			$msgs = $db->get_text(true, 9);
			$tpl->assign('regionsLabel',$msgs[501664]);
		} else {
			//this is for additional regions or in the admin: skip the label
			$tpl->assign('regionsLabel',false);
		}
			
		$tpl->assign('required',($required?1:0));
		return $tpl->fetch('Region/ajax_region_select_main.tpl');
	}
	
	/**
	 * Convenience method to get both the country and state selectors for the Billing form
	 * @param String $name prefix for the HTML name of the created dropdowns
	 * @param array $prevalue array of starting regions
	 * @return array
	 */
	public static function billingRegionSelector($name, $prevalue=false)
	{
		$regions['countries'] = self::billingCountrySelector($name, $prevalue);
		$regions['states'] = self::billingStateSelector($name, 0, $prevalue);
		return $regions;
	}
	
	/**
	 * Gets the HTML for the country selector in the billing form
	 * @param String $name prefix for the HTML name of the created dropdowns
	 * @param array $prevalue array of starting regions
	 * @return array
	 */
	public static function billingCountrySelector($name, $prevalue=false)
	{
		$db = DataAccess::getInstance();
		//figure out which level to use
		$overrides = self::getLevelsForOverrides();
		if(!$overrides['country']) {
			//country level not set!
			return false;
		}
		//get all regions at this level
		$sql = "SELECT * FROM ".geoTables::region." AS r, ".geoTables::region_languages." AS l WHERE r.id=l.id AND r.level=? AND l.language_id=? AND r.enabled='yes' ORDER BY r.display_order ASC, l.name ASC";
		$result = $db->Execute($sql, array($overrides['country'], $db->getLanguage()));
		if(!$result || $result->RecordCount() == 0) {
			//could not get regions at this level, or there are none
			return false;
		}
		$countries = array();
		while($country = $result->FetchRow()) {
			$countries[$country['id']] = array(
				'abbreviation' => ($country['billing_abbreviation']) ? $country['billing_abbreviation'] : $country['name'],
				'name' => geoString::fromDB($country['name']),
				'selected' => ($country['id'] == $prevalue[$overrides['country']]) ? 1 : 0
			);
		}
		$tpl = new geoTemplate('system','classes');
		$tpl->assign('countries', $countries);
		$tpl->assign('name', $name);
		$tpl->assign('in_admin', (defined('IN_ADMIN') ? 1:0));
		return $tpl->fetch('Region/billing_country_selector.tpl');
	}
	
	/**
	 * Gets the HTML for the state selector in the billing form. NOTE: used by AJAX call to adjust state selections on-the-fly with country selections
	 * @param String $name prefix for the HTML name of the created dropdowns
	 * @param int $forCountry the ID of the country to get states for. if not set, checks $prevalue for a country level
	 * @param array $prevalue array of starting regions
	 * @return array
	 */
	public static function billingStateSelector($name, $forCountry=0, $prevalue=false)
	{
		$db = DataAccess::getInstance();
		$overrides = self::getLevelsForOverrides();
		if(!$overrides['state']) {
			//state level not set!
			return false;
		}
		
		if(!$forCountry) {
			//calling this without a country to look in. see if there's a prevalue and start from there if possible
			if($prevalue[$overrides['country']]) {
				$forCountry = $prevalue[$overrides['country']];
			}
		}
		
		$states = self::getStatesDescendedFromCountry($forCountry);
		
		$tpl = new geoTemplate('system','classes');
		$tpl->assign('name', $name);
		if(!$states) {
			//this country has no states!
			$stateData = false;
		} else {
			//now that we have the state IDs, get all the fun data from them
			$stateData = array();
			foreach($states as $state) {
				$sql = "SELECT * FROM ".geoTables::region." AS r, ".geoTables::region_languages." AS l WHERE r.id=l.id AND r.id=? AND l.language_id=?";
				$result = $db->Execute($sql, array($state, $db->getLanguage()));
				if(!$result || $result->RecordCount() == 0) {
					//could not get state data
					return false;
				}
				while($s = $result->FetchRow()) {
					$stateData[$s['id']] = array(
							'abbreviation' => ($s['billing_abbreviation']) ? $s['billing_abbreviation'] : $s['name'],
							'name' => geoString::fromDB($s['name']),
							'selected' => ($s['id'] == $prevalue[$overrides['state']]) ? 1 : 0
					);
				}
			}
		}
		$tpl->assign('states', $stateData);		
		return $tpl->fetch('Region/billing_state_selector.tpl');	
	}
	
	/**
	 * Converts a Billing Abbreviation into the corresponding Region ID Number
	 * @param String $abbreviation
	 * @return int
	 */
	public static function getRegionIdFromAbbreviation($abbreviation)
	{
		$db = DataAccess::getInstance();
		$id = $db->GetOne("SELECT `id` FROM ".geoTables::region." WHERE `billing_abbreviation` = ?", array($abbreviation));
		if(!$id) {
			//if no id was found, the "abbreviation" may be a full name
			$id = $db->GetOne("SELECT `id` FROM ".geoTables::region." as r, ".geoTables::region_languages." as l WHERE r.id=l.id AND l.name = ? AND l.language_id = ?", array($abbreviation, $db->getLanguage()));
		}
		return intval($id);
	}
	
	/**
	 * gets IDs for all states that are decendants of the chosen country.
	 * note that there may be other levels in the middle -- this gets ALL states underneath the country
	 * if country is not specified, simply gets all states from the db
	 * @param int $country ID of the country to get
	 * @return Array
	 */
	public static function getStatesDescendedFromCountry($country=0)
	{
		$db = DataAccess::getInstance();
		$overrides = self::getLevelsForOverrides();
		if(!$overrides['state'] || !$overrides['country']) {
			//state or country level not set!
			return array();
		}
		
		if(!$country) {
			//no country specified, so just get everything from the state level
			$result = $db->Execute("SELECT * FROM ".geoTables::region." as r, ".geoTables::region_languages." as l WHERE r.id=l.id AND level=? AND l.language_id = ? AND enabled='yes' ORDER BY display_order ASC, name ASC", array($overrides['state'],$db->getLanguage()));
			while($s = $result->FetchRow()) {
				$states[] = $s['id'];
			}
			return $states;
		}
		
		$regions = array($country);
		for($i = $overrides['country']; $i < $overrides['state']; $i++) {
			//get all applicable children from next level down
			$children = array();
			foreach($regions as $region) {
				$result = $db->Execute("SELECT * FROM ".geoTables::region." as r, ".geoTables::region_languages." as l WHERE r.id=l.id AND parent=? AND l.language_id = ? AND enabled='yes' ORDER BY display_order ASC", array($region, $db->getLanguage()));
				while($result && $child = $result->FetchRow()) {
					$children[] = $child['id'];
				}
			}
			//now assign children to the parent array for the next loop iteration, which will get their children
			$regions = $children;
		}
		//$regions should now contain the ID of all states that are however-many levels down from the starting country, regardless of intermediate levels
		return $regions;		
	}
	
	/**
	 * Internal use
	 * @internal
	 * @var Array
	 */
	private static $_overrides;
	/**
	 * Figures out which geographic levels are assigned to specific level types.
	 * For use in preventing those types of levels from appearing in individual form fields
	 * @return Array
	 */
	public static function getLevelsForOverrides()
	{
		if(self::$_overrides) {
			return self::$_overrides;
		}
		$db = DataAccess::getInstance();
		$levelForType = $db->Prepare("SELECT `level` FROM ".geoTables::region_level." WHERE region_type = ?");
		self::$_overrides = array(
			'country' => $db->GetOne($levelForType, array('country')),
			'state' => $db->GetOne($levelForType, array('state/province')),
			'city' => $db->GetOne($levelForType, array('city'))
		);
		return self::$_overrides;
	}
	
	/**
	 * Gets data about the enabled child regions of a region. With no parameter, gets top-level regions.
	 * Most commonly, this is used by regionSelector() and friends
	 * @param string $fieldName HTML "name" of the array of fields to build
	 * @param int $parent The ID of the region to aquire the children of
	 * @param Array $prevalue Array of starting values for the dropdowns. Region IDs keyed by Level.
	 * @param int $maxLevel The highest level dropdown to show, based on settings specific to where selector is being used
	 * @param bool $skipEmptyRegions If true, regions that do not contain at least one listing will be omitted
	 * @return Array
	 */
	public static function getRegionsFromParent($fieldName, $parent=0, $prevalue=array(), $maxLevel=false, $skipEmptyRegions=false)
	{
		$db = DataAccess::getInstance();

		//figure out which level the parent is on
		$parentLevel = $db->GetOne("SELECT `level` FROM ".geoTables::region." WHERE id = ?", array($parent));
		//also figure out what the lowest level is
		$bottomLevel = self::getInstance()->getMaxLevel();
		if(!$parentLevel) {
			$parentLevel = 0;
		}
		
		//mostly interested in what's one level down
		$level = $parentLevel + 1;
		
		if($maxLevel && $level > $maxLevel) {
			//though it may exist, this level would be beyond the current max level setting. show nothing.
			return false;
		}
		
		//get data on next level
		$sql = "SELECT * FROM ".geoTables::region_level." AS r, ".geoTables::region_level_labels." AS l WHERE r.level=l.level AND r.level = ? AND l.language_id = ?";
		$levelData = $db->GetRow($sql, array($level, $db->getLanguage()));
		if(!$levelData) {
			//no data for this level
			return false;
		}
		$level = array(
				'id' => $levelData['level'],
				'label' => geoString::fromDB($levelData['label']),
				'use_label' => $levelData['use_label'],
				'always_show' => $levelData['always_show']
		);
		
		
		
		//get regions from next level
		$sql = "SELECT * FROM ".geoTables::region." AS r, ".geoTables::region_languages." AS l WHERE r.id=l.id AND l.language_id = ? AND r.parent = ? AND r.enabled='yes' ORDER BY `display_order` ASC, `name` ASC";
		$result = $db->Execute($sql, array($db->getLanguage(), $parent));
		if(!$result || $result->RecordCount() == 0) {
			//no children found
			return false;
		}
		

		//query to see if a given region has listings
		//note: this currently will check expired (unarchived) listings as well as live ones 
		//      might want to restrict it to just live listings at some point, but that would make the query take longer
		$regionNumListings = $db->Prepare("SELECT COUNT(`listing`) FROM ".geoTables::listing_regions." WHERE `region` = ?");
		
		$regions = array();
		while($child = $result->FetchRow()) {
			if($skipEmptyRegions) {
				if($db->GetOne($regionNumListings, array($child['id'])) == 0) {
					//this region has no listings, and we want to skip it
					continue;
				}
			}
			$regions[] = array(
					'id' => $child['id'],
					'name' => geoString::fromDB($child['name']),
					'selected' => ($prevalue[$level['id']] == $child['id']),
					'unique_name' => $child['unique_name']
			);
		}
		

		//special case for when this level only has one region
		//print the region name instead of a dropdown, but still check for any children of THAT region
		$isScalarLevel = (count($regions) == 1) ? true : false; 
		
		$data = array(
					'level' => $level, 
					'regions' => $regions,
					'buildDown' => $db->get_site_setting('region_select_build_down'), 
					'bottomLevel' => $bottomLevel, 
					'isScalarLevel' => $isScalarLevel,
					'isPreValued' => ($prevalue[$level['id']] != 0),
					'prevalue' => geoAjax::getInstance()->encodeJSON($prevalue),
					'maxLevel' => $maxLevel,
					'fieldName' => $fieldName,
					//fieldName "usable" in CSS class name or id
					'fieldName_class' => str_replace(array('[',']'),'_',$fieldName),
					'in_admin' => (defined('IN_ADMIN') || $_POST['is_a'] == 1) ? 1 : 0,
					'skipEmptyRegions' => $skipEmptyRegions ? 1 : 0
				);
		return $data;
	}
	
	/**
	 * Gets the regions on the same level as the chosen region that have the same parent (returned array includes the original region)
	 * @param int $region_id
	 * @return array
	 */
	public static function getDirectSiblingsOfRegion($region_id)
	{
		$db = DataAccess::getInstance();
		//first get this region's parent
		$parent = $db->GetOne("SELECT `parent` FROM ".geoTables::region." WHERE `id` = ?", array($region_id));
		
		$sql = "SELECT `id` FROM ".geoTables::region." WHERE `parent` = ? AND `enabled` = 'yes' ORDER BY display_order, unique_name";
		$result = $db->Execute($sql, array($parent));
		$siblings = array();
		while($sibling = $result->FetchRow()) {
			$siblings[] = $sibling['id'];
		}
		return $siblings;
	}
	
	/**
	 * Gets the plain-text name for a given region in the current language.
	 * @param int $region_id
	 * @return string
	 */
	public static function getNameForRegion($region_id)
	{
		//this is acting directly on user input, so make sure it's clean!
		$region_id = intval($region_id);
		if(!$region_id) {
			return '';
		}
		$db = DataAccess::getInstance();
		$name = $db->GetOne("SELECT `name` FROM ".geoTables::region_languages." WHERE id = ? AND language_id = ?",array($region_id, $db->getLanguage()));
		return geoString::fromDB($name);
	}
	
	/**
	 * Gets the "default" name for a region. For use if getNameForRegion fails (for instance, if the region itself has been removed by the admin, but listings still use it)
	 * @param int $region ID of the region 
	 * @param int $listing_id If looking up a listing, the listing's id. 0 otherwise
	 * @param int $user_id If looking up a user, the user's id. 0 otherwise
	 * @return string
	 */
	public static function getDefaultNameForRegion($region, $listing_id=0, $user_id=0)
	{
		if((!$listing_id && !$user_id) || ($listing_id && $user_id)) {
			//need exactly one of these or we can't do anything
			return '';
		}
		$table = ($user_id) ? geoTables::user_regions : geoTables::listing_regions;
		$field = ($user_id) ? "`user`" : "`listing`";
		$data = ($user_id) ? $user_id : $listing_id;
		return geoString::fromDB(DataAccess::getInstance()->GetOne('SELECT `default_name` FROM '.$table.' WHERE `region` = ? AND '.$field.' = ?', array($region, $data)));
	}
	
	/**
	 * Gets the array of regions from the requested user's registration data
	 * @param int $user_id
	 * @return Array
	 */
	public static function getRegionsForUser($user_id)
	{
		$user_id = intval($user_id);
		$db = DataAccess::getInstance();
		$result = $db->Execute("SELECT * FROM ".geoTables::user_regions." WHERE `user` = ? ORDER BY `level` ASC", array($user_id));
		$return = array();
		while($region = $result->FetchRow()) {
			$return[$region['level']] = $region['region'];
		}
		return $return;
	}
	
	/**
	 * Gets the array of regions from the requested listing
	 * @param int $listing_id
	 * @param int $maxLevel
	 * @param int $regionOrder
	 * @return Array
	 */
	public static function getRegionsForListing($listing_id, $maxLevel=0, $regionOrder = 0)
	{
		$db = DataAccess::getInstance();
		$listing_id = intval($listing_id);
		$maxLevel = intval($maxLevel);
		$maxLevel = ($maxLevel) ? " AND `level` <= $maxLevel " : "";
		$result = $db->Execute("SELECT * FROM ".geoTables::listing_regions." WHERE `listing` = ? AND `region_order` = ? $maxLevel ORDER BY `level` ASC", array($listing_id, $regionOrder));
		$return = array();
		while ($region = $result->FetchRow()) {
			$return[$region['level']] = $region['region'];
		}
		return $return;
	}
	
	/**
	 * Returns the names of regions associated with a listing in a format suitable for display
	 * @param int $listing_id
	 * @param int $maxLevel The highest level region that should be shown in the list
	 * @param int $regionOrder
	 * @return String
	 */
	public static function displayRegionsForListing($listing_id, $maxLevel=0, $regionOrder = 0)
	{
		$regions = self::getRegionsForListing($listing_id, $maxLevel, $regionOrder);
		ksort($regions);
		$display = array();
		foreach($regions as $level => $region) {
			$display[$level] = ($region) ? self::getNameForRegion($region) : self::getDefaultNameForRegion(0, $listing_id);
		}
		$tpl = new geoTemplate('system','classes');
		$tpl->assign('regions',$display);
		return $tpl->fetch('Region/listing_region_breadcrumb.tpl');
	}
	
	/**
	 * returns an array of the chosen region and all its parents
	 * @param int $child Typically, this should be a terminal region
	 * @return Array
	 */
	public static function getRegionWithParents($child)
	{
		$db = DataAccess::getInstance();
		$regions = array();
		do {
			$data = $db->GetRow("SELECT `parent`, `level` FROM ".geoTables::region." WHERE `id` = ?",$child);
			if($data) {
				$regions[$data['level']] = $child;
				$child = $data['parent'];
			}
		} while ($data && $child);
		ksort($regions, SORT_NUMERIC);
		return $regions;
	} 
	
	
	/**
	 * Gets the (billing) abbreviation to use for a region, or its full name if no abbreviation is set
	 * @param int $region_id
	 * @return string
	 */
	public static function getAbbreviationForRegion($region_id)
	{
		$region_id = intval($region_id);
		$db = DataAccess::getInstance();
		$abbr = $db->GetOne("SELECT `billing_abbreviation` FROM ".geoTables::region." WHERE `id` = ?", array($region_id));
		return ($abbr) ? $abbr : self::getNameForRegion($region_id);
		
	}
	
	/**
	 * Assigns a set of primary regions to a given listing.  This is a shortcut
	 * to using {@see geoRegions::setListingEndRegions()} for only the primary
	 * regions.
	 * 
	 * @param int $listingId
	 * @param array $regions
	 * @return bool success
	 */
	public static function setListingRegions ($listingId, $regions)
	{
		$endRegions = self::getEndRegions(array ($regions));
		return self::setListingEndRegions($listingId, $endRegions);
	}
	
	/**
	 * Set regions for the listing according to array of "end regions", meaning
	 * the furthest down region ID for each selected region.
	 * 
	 * @param int $listingId
	 * @param array $endRegions Array of end regions, in the order that they should
	 *   be added, the first entry in the array should be the "primary" region.
	 *   Note that any "duplicate" end regions should already be removed before
	 *   calling this or it will stop at the duplicate.
	 * @param int $startOrder Can be used to start adding at a specific order, this
	 *   is useful if you know that the primary region has already been added
	 *   to a listing, can skip that one and start with additional regions
	 * @return boolean True if successful, false otherwise
	 * @since Version 7.1.0
	 */
	public static function setListingEndRegions ($listingId, $endRegions, $startOrder=0)
	{
		$listingId = (int)$listingId;
		$startOrder = (int)$startOrder;
		if (!$listingId) {
			//can't add to fake listing!
			return false;
		}
		$db = DataAccess::getInstance();
		
		if (!is_array($endRegions)) {
			//failsafe
			$endRegions = array($endRegions);
		}
		//make sure $endRegions has keys that are numeric starting at 0 for primary region
		$endRegions = array_values($endRegions);
		
		//remove all the reginos set for this listing
		$sql = "DELETE FROM ".geoTables::listing_regions." WHERE `listing` = ?";
		if ($startOrder) {
			$sql .= " AND `region_order` >= $startOrder";
		}
		$result = $db->Execute($sql, array($listingId));
		if (!$result) {
			trigger_error('ERROR SQL: error deleting old regions');
			return false;
		}
		//need an instance of ourself to call non-static methods 
		$region = self::getInstance();
		
		//each "end region" is the region end-point.  So we need to go through each
		//one and generate the tree, make sure not to insert things already in a "lower" tree
		
		$inserted = array();
		foreach ($endRegions as $region_order => $regionId) {
			//offset the region order by whatever the start order is set to
			$region_order += $startOrder;
			if (isset($inserted[$regionId])) {
				//This should not happen!  It should correct for this already!
				//But still need to check for it here...
				trigger_error("ERROR REGION: Duplicate region attempted to be inserted (region #$regionId in listing #$listingId - should have corrected for this prior to calling this function!");
				return false;
			}
			//get array with all the region info, including all the parents
			$parents = $region->getParents($regionId, true);
			foreach ($parents as $regionInfo) {
				//loop through each region and add it
				if (isset($inserted[$regionInfo['id']])) {
					//this one is already inserted, do not insert again
					continue;
				}
				$inserted[$regionInfo['id']] = $regionInfo['id'];
				$sql = "INSERT INTO ".geoTables::listing_regions." (listing,region,level,region_order,default_name) VALUES (?,?,?,?,?)";
				$qd = array($listingId, $regionInfo['id'], $regionInfo['level'], 
					$region_order, geoString::toDB(self::getNameForRegion($regionInfo['id'])));
				$result = $db->Execute($sql, $qd);
				if (!$result) {
					trigger_error('ERROR SQL: failed to save regions: '.$sql.' Error: '.$db->ErrorMsg().' :: qd: '.print_r($qd,1));
					return false;
				}
			}
		}
		//inserting regions was a success!
		return true;
	}
	
	/**
	 * Simple tool to take array of regions that are in the below example
	 * array structure, and returns a flat array for each "end region".  This
	 * does NOT check for duplicates.  This is useful to get end regions for use
	 * in method setListingEndRegions()
	 * 
	 * Sample array:
	 * array (
	 *   0 => array (
	 *      1 => 1,
	 *      2 => 245,
	 *      3 => 532,
	 *   ),
	 *   1 => array (
	 *      1 => 38,
	 *      2 => '' 
	 *   ),
	 *   2 => array (
	 *      1 => 1
	 *      2 => 287
	 *   )
	 * )
	 * 
	 * Sample return:
	 * array (
	 *   0 => 532, 1 => 38, 2 => 287
	 * )
	 * 
	 * @param array $regions
	 * @return array
	 * @since Version 7.1.0
	 */
	public static function getEndRegions ($regions)
	{
		//input check, make sure it is an array
		$regions = (array)$regions;
		
		//go through and get the end regions...
		$end_regions = array();
		foreach ($regions as $region) {
			//make sure region is an array
			$region = (array)$region;
			/*
			 * Will be an array something like
			 * array (1 => 1, 2 => 245, 3 => 532, 4 => '')
			 *
			 * The last one with either be an empty string (meaning there were option
			 * in that level but none selected), or will be the end region. So,
			 * just pop values off the end of the array until get to one that
			 * is not 0 when cast to an integer.  With the below code, and the
			 * above example array, end result should
			 * be that it adds 532 to the array of end_regions.
			 */
			$end_region = 0;
			while (count($region) && $end_region===0) {
				$end_region = (int)array_pop($region);
			}
			if ($end_region) {
				$end_regions[] = $end_region;
			}
		}
		return $end_regions;
	}
	
	/**
	 * Assigns a set of regions to a given user
	 * @param int $userId
	 * @param array $regions
	 * @return bool success
	 */
	public static function setUserRegions($userId, $regions)
	{
		$db = DataAccess::getInstance();
		//first, remove any regions already set for this user
		$sql = "DELETE FROM ".geoTables::user_regions." WHERE `user` = ?";
		$result = $db->Execute($sql, array($userId));
		if(!$result) {
			trigger_error('ERROR SQL: error deleting old regions');
			return false;
		}
		foreach($regions as $level => $region) {
			$sql = "INSERT INTO ".geoTables::user_regions." (`user`,`region`,`level`,`default_name`) VALUES (?,?,?,?)";
			$qd = array($userId, $region, $level, geoString::toDB(geoRegion::getNameForRegion($region)));
			$result = $db->Execute($sql, $qd);
			if(!$result) {
				trigger_error('ERROR SQL: failed to save regions: '.$sql.' Error: '.$db->ErrorMsg().' :: qd: '.print_r($qd,1));
				return false;
			}
		}
		return true;
	}

	/**
	 * Gets the number of the lowest active/enabled region level
	 * @return int
	 * @since 7.0.0
	 */
	public static function getLowestLevel()
	{
		return self::getInstance()->getMaxLevel(true);
	}
	
	/**
	 * Internal use
	 * @internal
	 * @var Array
	 */
	private static $_listingRegions;
	/**
	 * Gets the displayable (per-language) names for a chosen listing, sorted by region level.
	 * 
	 * @param int $listing_id
	 * @param int $region_order The region order, 0 for "main" region (as opposed to
	 *   an additional region).  {@since Version 7.1.3}
	 * @return array
	 */
	public static function getRegionNamesForListingByLevel($listing_id, $region_order = 0)
	{
		$region_order = (int)$region_order;
		if(isset(self::$_listingRegions[$listing_id][$region_order])) {
			return self::$_listingRegions[$listing_id][$region_order];
		}
		$db = DataAccess::getInstance();
		$sql = "SELECT * FROM ".geoTables::listing_regions." WHERE `listing` = ? AND `region_order`={$region_order} ORDER BY `level`";
		$result = $db->Execute($sql, array($listing_id));
		$regions = array();
		while($region = $result->FetchRow()) {
			$levels[$region['level']]['region'] = $region['region'];
			$levels[$region['level']]['name'] = self::getNameForRegion($region['region']);
			if(!$levels[$region['level']]['name']) {
				//try default name instead
				$levels[$region['level']]['name'] = geoString::fromDB($region['default_name']);
			}
		}
		self::$_listingRegions[$listing_id][$region_order] = $levels;
		return self::$_listingRegions[$listing_id][$region_order];
	}
	
	/**
	 * Convenience method to get the "state" name for a given user in the current language
	 * @param int $user_id
	 * @return String
	 */
	public static function getStateNameForUser($user_id)
	{
		$overrides = self::getLevelsForOverrides();
		$stateLevel = $overrides['state'];
		$userRegions = self::getRegionsForUser($user_id);
		$state = $userRegions[$stateLevel];
		return self::getNameForRegion($state);
	}
	
	/**
	 * Convenience method to get the "country" name for a given user in the current language
	 * @param int $user_id
	 * @return String
	 */
	public static function getCountryNameForUser($user_id)
	{
		$overrides = self::getLevelsForOverrides();
		$countryLevel = $overrides['country'];
		$userRegions = self::getRegionsForUser($user_id);
		$country = $userRegions[$countryLevel];
		return self::getNameForRegion($country);
	}
	
	/**
	 * Convenience method to get the "state" name for a given listing in the current language
	 * @param int $listing_id
	 * @return String
	 */
	public static function getStateNameForListing($listing_id)
	{
		$overrides = self::getLevelsForOverrides();
		$stateLevel = $overrides['state'];
		return self::getNameForListingLevel($listing_id, $stateLevel);
	}
	
	/**
	 * Convenience method to get the "country" name for a given listing in the current language
	 * @param int $listing_id
	 * @return String
	 */
	public static function getCountryNameForListing($listing_id)
	{
		$overrides = self::getLevelsForOverrides();
		$countryLevel = $overrides['country'];
		return self::getNameForListingLevel($listing_id, $countryLevel);
	}
	
	/**
	 * Convenience method to get the name for a generic region at a certain level for a given listing in the current language
	 * @param int $listing_id
	 * @param int $level
	 * @return String
	 */
	public static function getNameForListingLevel($listing_id, $level)
	{
		$listingRegions = self::getRegionNamesForListingByLevel($listing_id);
		return $listingRegions[$level]['name'];
	}
	
	/**
	 * Gets the label for the requested level in the language currently in use
	 * @param int $level
	 * @return string
	 * @since 7.0.0
	 */
	public static function getLabelForLevel($level)
	{
		//get all the labels
		$me = self::getInstance();
		$labels = $me->getLevelLabels($level);
		//return just the level for the current language
		$lang = DataAccess::getInstance()->getLanguage();
		return $labels[$lang];
		
	}

	/**
	 * Returns a region ID corresponding to a "best guess" at the input string. 
	 * Useful for api/bulk upload scenarios where a large set of import data may not match up exactly with configured regions.
	 * Note that if the input is an integer, it will be returned unchanged
	 * @param String $str
	 * @return int
	 */
	public static function getRegionIdByBestGuess($in)
	{
		if(!$in) {
			//bad input
			return false;
		}
		if((int)$in == $in && $in > 0) {
			//this is an integer input. no need to do anything fancy
			return $in;
		}
		$db = DataAccess::getInstance();
		
		//next, check for a direct text match, preferring lower levels
		$sql = "SELECT `id` FROM ".geoTables::region_languages." as l, ".geoTables::region." as r WHERE l.`name` = ? AND l.id=r.id ORDER BY r.level DESC";
		$result = $db->Execute($sql, array(geoString::toDB($in)));
		if($result && $result->RecordCount()) {
			$row = $result->FetchRow();
			return $row['id'];
		}
		
		//failing that, get everything that starts with the same letter, and look for the closest match
		$sql = "SELECT `id`, `name` FROM ".geoTables::region_languages." WHERE `name` LIKE '".(substr($in,0,1))."%'";
		$result = $db->GetAll($sql);
		if($result && count($result) > 0) {
			$oldDistance = false;
			//iterate over all possibles, and find the one with the smallest levenshtein difference from the input
			foreach($result as $check) {
				$newDistance = levenshtein($in, geoString::fromDB($check['name']));
				if($newDistance == -1) {
					//invalid (too long?) input. cannot check with this method
					return $in;
				}
				if($oldDistance === false || $newDistance < $oldDistance && $newDistance <= 5) {
					$bestGuess = $check['id'];
					$oldDistance = $newDistance;
				}
			}
			return ($bestGuess) ? $bestGuess : $in;
		}
		
		//didn't even find anything with the same first letter? just return the original input, then
		return $in;
	}
	
	/*////////////////////////////////////////////////////////////////////////////////
	                   OLD REGION METHODS. Deprecated in 7.0.0
	////////////////////////////////////////////////////////////////////////////////*/
	
	
	/**
	 * Gets the "name" for the given region ID.
	 * @param int|string $region_id The region ID OR abbreviation
	 * @return string|bool will return false if problem occurs, or the name (empty
	 *  string if region id not found)
	 *  @deprecated 7.0.0
	 */
	public function getRegionNameById ($region_id)
	{
		return self::getNameForRegion($region_id);
	}
	
	/**
	 * Gets the name for the given region and sub region ID.
	 *
	 * @param int|string $region_id sub-region ID OR abbriviation
	 * @return string|bool The name, or empty string if not found, or false if problem occured.
	 * @deprecated 7.0.0
	 */
	public function getSubRegionNameById ($region_id)
	{
		return self::getNameForRegion($region_id);
	}
	
	
	/**
	 * get the abbreviation of a state supplying the state id and region id
	 *
	 * @param int $subregion_id
	 * @return string
	 * @deprecated 7.0.0
	 */
	public function getSubRegionAbbreviationById ($subregion_id)
	{
		return self::getAbbreviationForRegion($subregion_id);
	}
	
	/**
	 * Get the number of listings in a given region
	 * @param int $region_id
	 * @return int
	 * @deprecated 7.0.0
	 */
	public function getListingCountByRegion($region_id)
	{
		$total = $db->GetOne("SELECT COUNT(listing) FROM ".geoTables::listing_regions." WHERE region = ?", array($subregion));
		if($total===false) {
			trigger_error('ERROR SQL: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return 0;
		}
		return $total;
	}
	
	/**
	 * Get the number of listings in a particular sub region.
	 * @param int $subregion
	 * @return int
	 * @deprecated 7.0.0
	 */
	public function getListingCountBySubRegion($subregion)
	{
		return self::getListingCountByRegion($subregion);
	}
}
