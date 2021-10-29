<?php
//LeveledField.class.php
/**
 * Holds the geoLeveledField object.
 *
 * @package System
 * @since Version 7.1.0
 */
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.3.6-12-ga7d9248
##
##################################

/**
 * Class for doing common things with leveled fields.
 * 
 * @package System
 * @since Version 7.1.0
 */
class geoLeveledField
{
	/**
	 * Used internally
	 * @internal
	 */
	private static $_instance;
	/**
	 * Used internally
	 * @internal
	 */
	private $_levels;
	
	/**
	 * This is changed in the constructor
	 * @var int
	 */
	private $_values_per_page = 100;
	
	/**
	 * The constructor, don't call directly, use geoLeveledField::getInstance()
	 */
	private function __construct ()
	{
		$db = DataAccess::getInstance();
		
		$this->_values_per_page = $db->get_site_setting('leveled_max_vals_per_page');
		if (!$this->_values_per_page) {
			//set a default of 100
			$this->_values_per_page = 100;
		}
	}
	
	/**
	 * Gets instance of the class.
	 * 
	 * @return geoLeveledField
	 */
	public static function getInstance ()
	{
		if (!is_object(self::$_instance)) {
			$c = __class__;
			self::$_instance = new $c;
		}
		return self::$_instance;
	}
	
	/**
	 * Gets the number of values to show on a single page.
	 * @return int
	 */
	public function getValuesPerPage ()
	{
		return $this->_values_per_page;
	}
	
	/**
	 * Get the parent values for the specified value ID in an array.
	 * 
	 * @param int $value_id
	 * @param bool $use_bottom If true, will also return the requested value info
	 *   as part of the array of parent values, handy for things like displaying
	 *   the full breadcrumb.
	 * @return boolean|array Returns an array with all the parent value info, the
	 *   array index is the parent value level and the info is same as returned
	 *   by {@see geoLeveledField::getValueInfo()}
	 */
	public function getParents ($value_id, $use_bottom = false)
	{
		//Note: we don't ask for the leveled field ID here since that is unique
		//to the value ID.
		$value_id = (int)$value_id;
		if (!$value_id) {
			//failsafe
			return false;
		}
		
		$values = array();
		$valueInfo = $this->getValueInfo($value_id);
		
		while ($valueInfo) {
			if ($valueInfo['id']!=$value_id || $use_bottom) {
				//only add parents
				$values[] = $valueInfo;
			}
			if ($valueInfo['parent']) {
				$valueInfo = $this->getValueInfo($valueInfo['parent']);
			} else {
				$valueInfo = false;
			}
		}
		
		return array_reverse($values);
	}
	
	/**
	 * Gets info about a leveled field value
	 * 
	 * @param int $value_id
	 * @param bool $includeLevelInfo If true, will include level_info, which is
	 * 	the level info from $this->getLevel()
	 * @return array|bool Returns info about value, or false on error.
	 */
	public function getValueInfo ($value_id, $includeLevelInfo = false)
	{
		//Note: we don't ask for the leveled field ID here since that is unique
		//to the value ID.
		$db = DataAccess::getInstance();
		$value_id = (int)$value_id;
		$language_id = (int)geoSession::getInstance()->getLanguage();
		
		$row = $db->GetRow("SELECT * FROM ".geoTables::leveled_field_value." v, ".geoTables::leveled_field_value_languages." l WHERE l.id=v.id AND l.language_id={$language_id} AND v.id=?",array($value_id));
	
		//unescape name
		$row['name'] = geoString::fromDB($row['name']);
		if ($includeLevelInfo) {
			$row['level_info'] = $this->getLevel($row['leveled_field'], $row['level'], $language_id);
		}
	
		return $row;
	}
	
	/**
	 * Gets a specific level, this is like calling getLevels but only returns a
	 * specific level.
	 * 
	 * @param int $leveled_field
	 * @param int $level
	 * @param int $language_id Will fill in the label with the one for the specified
	 *   language ID, if the level id labeled.
	 * @return boolean|array The array of level information or bool false on error.
	 */
	public function getLevel ($leveled_field, $level, $language_id=1)
	{
		//make sure levels are populated
		$this->getLevels($leveled_field);
		
		if (!isset($this->_levels[$leveled_field][$level])) {
			//not a good level
			return false;
		}
		
		$level = $this->_levels[$leveled_field][$level];
		if (isset($level['labels'][$language_id])) {
			$level['label'] = $level['labels'][$language_id];
		}
		return $level;
	}
	
	/**
	 * Gets all the value levels and their settings in associative array for the
	 * given leveled field
	 * 
	 * @param int $leveled_field
	 * @return array
	 */
	public function getLevels ($leveled_field)
	{
		$leveled_field = (int)$leveled_field;
		if (!$leveled_field) {
			//failsafe
			return false;
		}
		if (!is_array($this->_levels)) {
			$this->_levels = array();
		}
		if (!isset($this->_levels[$leveled_field])) {
			$db = DataAccess::getInstance();
		
			//first get the deepest level that is in existance!
			$maxLevels = $this->getMaxLevel($leveled_field);
			trigger_error('DEBUG STATS: After getting max levels, which is '.$maxLevels);
			
			if (defined('IN_ADMIN')) {
				//now randomly select a value from that lowest level
				//TODO: if this becomes real slow for large numbers of values, there are faster ways to randomize than ORDER BY RAND()
				$value = $db->GetRow("SELECT * FROM ".geoTables::leveled_field_value." v, ".geoTables::leveled_field_value_languages." l WHERE l.id=v.id AND l.language_id=1 AND v.level = ? AND v.leveled_field=? ORDER BY RAND()",array($maxLevels, $leveled_field));
				
				if (!$value && $maxLevels == 1) {
					//no values yet, set one
					$levels = array (1 => array ('level' => 1, 'sample' => ''));
				} else {
					//then get all the parents
					$values = $this->getParents($value['id'], true);
					
					//now loop through those parents and set up defaults for each level
					$levels = array();
					foreach ($values as $row) {
						$levels[$row['level']] = array(
							'level' => $row['level'], 'sample'=>$row['name'],
						);
					}
					unset ($values);
				}
			} else {
				//Not in the admin panel, do not bother populating the sample, just set up some defaults
				$levels = array();
				for ($level=1; $level<=$maxLevels; $level++) {
					$levels[$level] = array (
						'level' => $level, 'sample' => '',
					);
				}
			}
		
			//Now go get all the real settings, and over-write the defaults
			$rows = $db->GetAll("SELECT * FROM ".geoTables::leveled_field_level." WHERE `leveled_field`=? ORDER BY `level`", array($leveled_field));
			foreach ($rows as $row) {
				if ($row['level'] > $maxLevels) {
					//there are levels defined that don't have values, don't bother displaying
					break;
				}
				
				//get the labels
				$row['labels'] = $this->getLevelLabels($leveled_field, $row['level']);
				//set the language 1 as the "label" for easy access
				$row['label'] = $row['labels'][1];
				
				$row['sample'] = $levels[$row['level']]['sample'];
				
				$levels[$row['level']] = $row;
			}
			$this->_levels[$leveled_field] = $levels;
		}
		return $this->_levels[$leveled_field];
	}
	
	/**
	 * Gets the labels for all the different languages for the given level.  Note
	 * that this takes care of DB encoding, all labels are in un-encoded format.
	 * 
	 * @param int $leveled_field
	 * @param int $level
	 * @return boolean|array The array of labels, or false on error.
	 */
	public function getLevelLabels ($leveled_field, $level)
	{
		$db = DataAccess::getInstance();
		$level = (int)$level;
		$leveled_field = (int)$leveled_field;
		if (!$level || !$leveled_field) {
			//level / leveled_field not valid
			return false;
		}
		$rows = $db->GetAll("SELECT * FROM ".geoTables::leveled_field_level_labels." WHERE `leveled_field`=? AND `level`=?",array($leveled_field, $level));
		//format it so index is the level, plus pre-un-fromdb it
		$labels = array();
		foreach ($rows as $row) {
			$labels[$row['language_id']] = geoString::fromDB($row['label']);
		}
		return $labels;
	}
	
	/**
	 * Gets the maximum level for leveled values found in the system.
	 * 
	 * @param int $leveled_field
	 * @param bool $onlyEnabled If true, gets the max level when you take into
	 *   account if it is enabled or not.
	 * @return int Max number of levels found with leveled values in them.
	 */
	public function getMaxLevel ($leveled_field, $onlyEnabled = false)
	{
		$db = DataAccess::getInstance();
		$leveled_field = (int)$leveled_field;
		
		$query = "SELECT `level` FROM ".geoTables::leveled_field_value." WHERE `leveled_field`=$leveled_field";
		if ($onlyEnabled) {
			//do a different way
			$query .= " AND `enabled`='yes'";
		}
		$query .= " ORDER BY `level` DESC";
		return max(1,(int)$db->GetOne($query));
	}
	
	/**
	 * Gets the label for the leveled field ID specified
	 * @param int $leveled_field
	 * @return string
	 */
	public function getLeveledFieldLabel ($leveled_field)
	{
		$db = DataAccess::getInstance();
		$leveled_field = (int)$leveled_field;
		
		return geoString::fromDB($db->GetOne("SELECT `label` FROM ".geoTables::leveled_fields." WHERE `id`=?", array($leveled_field)));
	}
	
	/**
	 * Gets an array of leveled field ID's, useful if need to loop through the
	 * "possible" leveled fields in the system.
	 * 
	 * @return boolean|array Array of IDs, or false on error
	 */
	public function getLeveledFieldIds ()
	{
		$db = DataAccess::getInstance();
		
		$result = $db->Execute("SELECT `id` FROM ".geoTables::leveled_fields);
		
		if (!$result) {
			//that's not good...
			trigger_error("ERROR SQL: Db error getting leveled fields, error reported: ".$db->ErrorMsg());
			return false;
		}
		$ids = array();
		foreach ($result as $row) {
			$ids[] = $row['id'];
		}
		return $ids;
	}
	
	/**
	 * Gets the enabled values for the given leveled field and level
	 * @param int $leveled_field
	 * @param int $parent Parent value ID or 0 for first level
	 * @param int $selected If specified, the value that matches the ID will
	 *   have index 'selected'=>true set
	 * @param string $page Leveled field values are paginated, this can be the
	 *   page number, OR the string "all" to get all.
	 * @param int $language_id If specified, will use that for the language, otherwise
	 *   will use the currently active language on the session.
	 * @return array|bool If specified leveled_field and level are valid, returns
	 *   Array with index "values" as array (id# => 'value', maxValues => count),
	 *   otherwise returns false
	 */
	public function getValues ($leveled_field, $parent, $selected = 0, $page=1, $language_id=null)
	{
		$db = DataAccess::getInstance();
		$leveled_field = (int)$leveled_field;
		$parent = (int)$parent;
		$selected = (int)$selected;
		$page = ($page=='all')? 'all' : (int)max(1,$page);
		
		if (!$leveled_field || $parent<0) {
			//invalid input
			return false;
		}
		$return = array('values'=>array(), 'maxValues'=>0, 'page'=>1, 'maxPages'=>1,
			'level' => 1);
		
		$language_id = (int)$language_id;
		if (!$language_id) {
			//get the current language id
			$language_id = (int)geoSession::getInstance()->getLanguage();
		}
		
		//figure out the level
		if (!$parent) {
			$return['level'] = 1;
		} else {
			//get parent info and base level on that
			$pInfo = $this->getValueInfo($parent);
			if ($pInfo) {
				$return['level'] = $pInfo['level']+1;
			}
			unset($pInfo);
		}
		
		$valT = geoTables::leveled_field_value;
		$langT = geoTables::leveled_field_value_languages;
		
		$query = new geoTableSelect($valT);
		$query->join($langT, "$valT.`id`=$langT.`id`", "`name`")
			->where("$valT.`leveled_field`={$leveled_field}")
			->where("$valT.`parent`={$parent}")
			->where("$valT.`enabled`='yes'")
			->where("$langT.`language_id`={$language_id}");
		
		$return['maxValues'] = (int)$db->GetOne($query->getCountQuery());
		if (!$return['maxValues']) {
			//no use in running the normal query, we already know count is 0
			return $return;
		}
		
		
		$query->order("$valT.`display_order`, $langT.`name`");
		
		if ($return['maxValues'] > $this->_values_per_page) {
			//calculate number of pages
			$return['maxPages'] = ceil($return['maxValues']/$this->_values_per_page);
		
			if ($page!=='all' && $page <= $return['maxPages']) {
				//add limit
				$start = ($page-1) * $this->_values_per_page;
				
				$query->limit($start, $this->_values_per_page);
				//this is the "actual" page we are on
				$return['page'] = $page;
			} else if ($page === 'all' && $return['maxPages']>1) {
				//set the returned page to 'all'
				$return['page'] = 'all';
			}
		}
		$result = $db->Execute($query);
		if (!$result) {
			//error?
			trigger_error('ERROR SQL: Error getting leveled field values!');
			return false;
		}
		foreach ($result as $row) {
			//unescape name
			$row['name'] = geoString::fromDB($row['name']);
			if ($selected) {
				$row['selected'] = ($selected==$row['id']);
			}
			$return['values'][$row['id']] = $row;
		}
		
		return $return;
	}
	
	/**
	 * Get the number of values for the given leveled field and parent.
	 * 
	 * @param int $leveled_field
	 * @param int $parent
	 * @return number
	 */
	public function getValueCount ($leveled_field, $parent)
	{
		$leveled_field = (int)$leveled_field;
		$parent = (int)$parent;
		
		if (!$leveled_field) {
			return 0;
		}
		$db = DataAccess::getInstance();
		
		return (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::leveled_field_value." WHERE
				`parent`=? AND `leveled_field`=? AND `enabled`='yes'", array($parent, $leveled_field));
	}
	
	/**
	 * Set listing values for the given listing ID and leveled fields specified
	 * 
	 * @param int $listingId
	 * @param array $leveled_fields Array of values, meant to be passed in from
	 *   values as they are set during listing placement selection.  Format is:
	 *   array ( LEVELED_FIELD_ID => array ( VALUE_ID, VALUE_ID...), ...)
	 *   Or in other words:
	 *   leveled_field[LEVELED_FIELD_ID][]=VALUE_ID
	 *   In the array of values, only the last in the array is used to determine
	 *   the "furthest down" selected value, the parents are auto-populated from
	 *   that.
	 * @return boolean true on success, false on failure.
	 */
	public static function setListingValues ($listingId, $leveled_fields)
	{
		$listingId = (int)$listingId;
		
		if (!$listingId) {
			//can't add to fake listing!
			return false;
		}
		$db = DataAccess::getInstance();
		
		//remove all the values set for this listing
		$sql = "DELETE FROM ".geoTables::listing_leveled_fields." WHERE `listing` = ?";
		$result = $db->Execute($sql, array($listingId));
		
		if (!$result) {
			trigger_error('ERROR SQL: error deleting old values');
			return false;
		}
		//need an instance of ourself to call non-static methods
		$lField = self::getInstance();
		
		$query = $db->Prepare("INSERT INTO ".geoTables::listing_leveled_fields." (listing,leveled_field,field_value,level,default_name) VALUES (?,?,?,?,?)");
		
		foreach ($leveled_fields as $lev_id => $values) {
			if ($lev_id === 'cat') {
				//special case... this is actually a category
				continue;
			}
			$values = (array)$values;
			$val = array_pop($values);
			if (!$val) {
				//something wrong with this one
				continue;
			}
			$valInfo = $lField->getValueInfo($val);
			if (!$valInfo) {
				//something wrong with this one
				continue;
			}
			//go through the parents and add each of them
			$parents = $lField->getParents($val, true);
			foreach ($parents as $valInfo) {
				$db->Execute($query, array ($listingId, $valInfo['leveled_field'], $valInfo['id'],
					$valInfo['level'], geoString::toDB($valInfo['name'])));
			}
		}
		
		//inserting regions was a success!
		return true;
	}
	
	/**
	 * Used by fields to use to get the default fields to use.  This isn't of much
	 * use outside of being used to populate the fields to use info.
	 * 
	 * @return array
	 */
	public function getFieldsToUse ()
	{
		$db = DataAccess::getInstance();
		
		//get all the leveled fields...
		$fields = array();
		
		
		$result = $db->Execute("SELECT * FROM ".geoTables::leveled_fields);
		if (!$result) {
			//that's not good...
			return false;
		}
		foreach ($result as $row) {
			//get all levels for this one
			$levels = $this->getLevels($row['id']);
			$dependencies = array();
			foreach ($levels as $level) {
				$index = 'leveled_'.intval($row['id']).'_'.intval($level['level']);
				$level_label = ($level['label'])? ' ('.$level['label'].')' : '';
				$fields[$index] = array(
					'label' => geoString::fromDB($row['label']).' - level '.$level['level'].$level_label,
					'type' => 'other',
					'type_label' => 'Drop-down Selection',
					
					);
				if ($level['sample']) {
					$fields[$index]['label'] .= " [Example: <strong>{$level['sample']}</strong>]";
				}
				if ($level['level'] > 1) {
					//only show "editable" for first one
					$fields[$index]['skipData'] = array('is_editable');
					$fields[$index]['dependencies'] = $dependencies;
				}
				$dependencies['enabled'][] = 'fields_leveled_'.$index.'_is_enabled';
				$dependencies['required'][] = 'fields_leveled_'.$index.'_is_required';
			}
			//For now, breadcrumb is DISABLED (and not fully coded).  Not sure if
			//this will be needed at all, if not just remove this next part altogether.
			if (false && count($levels)>1) {
				$fields['leveled_'.intval($row['id']).'_breadcrumb'] = array(
					'label' => geoString::fromDB($row['label']).' - Breadcrumb',
					'type' => 'other',
					'type_label' => 'Display Only',
					'skipData' => array ('is_required', 'is_editable'),
					'skipLocations' => array('search_fields'),
					);
			}
		}
		return $fields;
	}
	
	/**
	 * Removes the specified value and all sub-values.  Or if dryRun is true (which
	 * is default), will only do a dry-run and return an array indicating how many
	 * at each "level" would be affected by this deletion.
	 *
	 * This will remove sub-levels recursively as well.
	 *
	 * @param array|int $values array of values to remove, or single value ID to remove
	 * @param bool $dryRun Set to false to go forward with the removal.
	 * @return bool|array If dryRun is true, returns an array indicating how many
	 *   of each level of values will be removed. If dryRun is false, simply returns
	 *   bool true if removal successful, false otherwise.
	 */
	public static function removeValues ($values, $dryRun = true)
	{
		$db = DataAccess::getInstance();
		
		$info = array();
		
		if (!is_array($values)) {
			$values = array($values);
		}
		
		foreach ($values as $value_id) {
			self::_removeValue($value_id, $dryRun, $info);
		}
		
		if ($dryRun) {
			return array_reverse($info, true);
		} else {
			return true;
		}
	}
	
	/**
	 * Removes the specified leveled field and all values.
	 *
	 * @param int $leveled_field
	 * @return bool|array If dryRun is true, returns an array indicating how many
	 *   of each level of values will be removed. If dryRun is false, simply returns
	 *   bool true if removal successful, false otherwise.
	 */
	public static function remove ($leveled_field)
	{
		$db = DataAccess::getInstance();
		$leveled_field = (int)$leveled_field;
		
		//first delete value languages
		$query = "DELETE `val`, `lang` FROM ".geoTables::leveled_field_value." as `val`
			LEFT JOIN ".geoTables::leveled_field_value_languages." as `lang` ON `val`.`id`=`lang`.`id`
			WHERE
			`val`.`leveled_field`=?";
		$db->Execute($query, array($leveled_field));
		
		//delete levels
		$db->Execute("DELETE FROM ".geoTables::leveled_field_level." WHERE `leveled_field`=?",array($leveled_field));
		$db->Execute("DELETE FROM ".geoTables::leveled_field_level_labels." WHERE `leveled_field`=?",array($leveled_field));
		
		//delete the main entry
		$db->Execute("DELETE FROM ".geoTables::leveled_fields." WHERE `id`=?",array($leveled_field));
	}
	
	/**
	 * Used internally to recursively remove a value
	 *
	 * @param int $value_id
	 * @param bool $dryRun
	 * @param array $info
	 * @internal
	 */
	private static function _removeValue ($value_id, $dryRun, &$info)
	{
		$db = DataAccess::getInstance();
	
		$children = $db->Execute("SELECT `id` FROM ".geoTables::leveled_field_value." WHERE `parent`=?", array($value_id));
		foreach ($children as $row) {
			self::_removeValue($row['id'], $dryRun, $info);
		}
		if ($dryRun) {
			//just getting info...
			$value = $db->GetRow("SELECT * FROM ".geoTables::leveled_field_value." WHERE `id`=?", array($value_id));
			if ($value) {
				if (isset($info[$value['level']])) {
					$info[$value['level']]++;
				} else {
					$info[$value['level']]=1;
				}
			}
		} else {
			//just plain delete
			$db->Execute("DELETE FROM ".geoTables::leveled_field_value_languages." WHERE `id`=?",array($value_id));
			$db->Execute("DELETE FROM ".geoTables::leveled_field_value." WHERE `id`=?",array($value_id));
			$db->Execute("UPDATE ".geoTables::listing_leveled_fields." SET `field_value` = 0 WHERE `field_value` = ?", array($value_id));
		}
	}
}