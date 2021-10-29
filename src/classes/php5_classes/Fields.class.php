<?php
//Fields.class.php
/**
 * Holds the geoFields and geoFieldsField classes.
 * 
 * @package System
 * @since Version 5.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.09.0-15-gc419d04
## 
##################################

/**
 * Class that holds settings information for fields to use.
 * 
 * @package System
 * @since Version 5.0.0
 */
class geoFields
{
	/**
	 * Internal
	 * @internal
	 */
	private static $_instances;
	
	/**
	 * The "requested" category ID
	 * @var int
	 */
	private $_requestedCategory;
	
	/**
	 * The "requested" group ID
	 * @var int
	 */
	private $_requestedGroup;
	
	/**
	 * The category used for the fields to use, if the requested category was
	 * not found, it "bubbles up" until one with category specific settings ARE
	 * found.
	 * 
	 * @var int
	 */
	private $_category;
	
	/**
	 * The group used for the fields to use, or 0 if using site-wide
	 * @var int
	 */
	private $_group;
	
	/**
	 * The field's settings for each of the different fields it could find.
	 * @var array
	 */
	private $_fields;
	
	/**
	 * Whether or not there are pending changes in the fields we have
	 * @var bool
	 */
	private $_pendingChanges = false;
	
	/**
	 * Internal
	 * @internal
	 */
	private static $_xmlMetaFields;
	
	/**
	 * Gets an instance based on the group and category ID.
	 * 
	 * @param int $groupId
	 * @param int $categoryId
	 * @return geoFields
	 */
	public static function getInstance ($groupId = 0, $categoryId = 0)
	{
		$categoryId = (geoPC::is_ent())? (int)$categoryId : 0;
		$groupId = (geoPC::is_ent())? (int)$groupId : 0;
		
		if (!isset(self::$_instances[$categoryId][$groupId]) || !is_object(self::$_instances[$categoryId][$groupId])) {
			$class = __class__;
			self::$_instances[$categoryId][$groupId] = new $class ($groupId, $categoryId);
		}
		return self::$_instances[$categoryId][$groupId];
	}
	/**
	 * Gets the geoFieldsField object for the specified field name.
	 * 
	 * @param string $fieldName
	 * @return geoFieldsField|null
	 */
	public function getField ($fieldName)
	{
		if (!isset($this->_fields[$fieldName])) {
			//problem
			return null;
		}
		if (!is_object($this->_fields[$fieldName]) && is_array($this->_fields[$fieldName])) {
			$this->_fields[$fieldName] = new geoFieldsField ($this->_fields[$fieldName], $this);
		}
		return $this->_fields[$fieldName];
	}
	
	/**
	 * Gets an associative array of which fields are configured to display the
	 * given location, each key is the field name and the value is whether it
	 * is configured to display in the given location.
	 * 
	 * @param string $locationName
	 * @param string $addon If wanting to get location added by specific addon,
	 *   enter the addon folder name in this var.  This is the equivelent
	 *   of calling getDisplayLocationFields ('addon-'.$addon.'-'.$locationName)
	 *   without specifying addon.
	 * @return array
	 */
	public function getDisplayLocationFields ($locationName, $addon = '')
	{
		$locationName = trim($locationName);
		if (!$locationName) {
			//invalid input, return empty array
			return array();
		}
		$addon = trim($addon);
		if ($addon) {
			$locationName = "addon-$addon-$locationName";
		}
		$fields = array();
		foreach ($this->_fields as $fieldName => $field) {
			if ((is_object($field) && in_array($locationName, $field->display_locations))
				|| (is_array($field) && $field['is_enabled'] && in_array($locationName, $field['display_locations']))) {
				$fields[$fieldName] = 1;
			} else {
				$fields[$fieldName] = 0;
			}
		}
		return $fields;
	}
	
	/**
	 * Gets module display fields for given module name
	 * 
	 * @param string $module
	 * @return array
	 * @since Version 6.0.0
	 */
	public function getModuleFields ($module)
	{
		return $this->getDisplayLocationFields('module-'.$module);
	}
	
	/**
	 * Gets the group ID for the fields object.
	 * @return int
	 */
	public function getGroupId ()
	{
		return $this->_group;
	}
	
	/**
	 * Gets the category ID for this fields object.
	 * @return int
	 */
	public function getCategoryId ()
	{
		return $this->_category;
	}
	
	/**
	 * Gets the fields to use based on the category set in $this->_category.
	 * 
	 * Not normal to call this directly, as it is called when the geoFields
	 * is first created, but this can still be called if needed to refresh
	 * settings.
	 * 
	 * 
	 */
	public function unserialize ()
	{
		$groupId = (geoPC::is_ent())? (int)$this->_group : 0;
		$categoryId = (geoPC::is_ent())? (int)$this->_category : 0;
		
		$db = DataAccess::getInstance();
		
		$groupExtra = ($groupId)? ' OR `group_id`=0' : '';
		$categoryExtra = ($categoryId)? ' OR `category_id`=0' : '';
		
		$sql = "SELECT * FROM ".geoTables::fields." WHERE
			(`group_id`=?$groupExtra) AND
			(`category_id`=?$categoryExtra)
			ORDER BY `group_id` DESC, `category_id` DESC";
		
		//This will get the group/category specific settings, as well as site
		//site defaults (if not specifically getting site defaults)
		$rows = $db->GetAll($sql, array($groupId, $categoryId));
		
		$fields = array();
		foreach ($rows as $row) {
			$fieldName = $row['field_name'];
			if (isset($fields[$fieldName])) {
				//we already have settings for this one from "closer" to the target!
				continue;
			}
			
			//Get the display locations
			$displayLocationsRows = $db->GetAll("SELECT `display_location` FROM ".geoTables::field_locations." WHERE `group_id`=? AND `category_id`=? AND `field_name`=?",
				array ($groupId, $categoryId, ''.$row['field_name']));
			
			if (!$displayLocationsRows || !is_array($displayLocationsRows)) {
				//should be an array, nothing else!
				$displayLocationsRows = array ();
			}
			$displayLocations = array();
			foreach ($displayLocationsRows as $lrow) {
				if (!in_array($lrow['display_location'], $displayLocations)) {
					$displayLocations[] = $lrow['display_location'];
				}
			}
			
			$row ['display_locations'] = $displayLocations;
			
			$fields [$fieldName] = $row;
			if ((int)$row['group_id'] !== $this->_group || (int)$row['category_id'] !== $this->_category) {
				//have one that got from cat/group 0 when we're not using 0
				$this->touch();
			}
		}
		
		$defaultFields = self::getDefaultFields($groupId, $categoryId);
		
		foreach ($defaultFields as $sectionName => $section) {
			foreach ($section['fields'] as $fieldIndex => $data) {
				if (!isset($fields[$fieldIndex])) {
					//lets set some defaults!
					$fields[$fieldIndex] = array (
						'group_id' => $this->_groupId,
						'category_id' => $this->_categoryId,
						'field_name' => $fieldIndex,
						'is_enabled' => 0,
						'is_required' => 0,
						'can_edit' => 0,
						'field_type' => ''.$data['type'],
						'type_data' => '',
						'text_length' => 0,
						'display_locations' => array(),
						'using_defaults' => 1,
					);
					//we are running at least one field off of defaults, mark that
					//there are changes!
					$this->touch();
				}
			}
		}
		
		$this->_fields = $fields;
	}
	
	/**
	 * Serializes the geoFields data, saved to the database.
	 * @return bool
	 */
	public function serialize ()
	{
		if (!$this->_pendingChanges) {
			//no changes to save!
			trigger_error('DEBUG FIELDS: No pending changes, nothing to save!');
			return true;
		}
		trigger_error('DEBUG FIELDS: At top of serialize!');
		foreach ($this->_fields as $key => $field) {
			if (is_array($field) && isset($field['using_defaults']) && $field['using_defaults']) {
				//using defaults for this one, go ahead and set it up and save it
				//so it won't be running off of defaults any more
				$field = new geoFieldsField ($field, $this);
				$field->touch();
				$this->_fields[$key] = $field;
			}
			if (is_array($field) && ((int)$field['group_id'] !== $this->_group || (int)$field['category_id'] !== $this->_category)) {
				//this one got picked up from group 0 or cat 0, so need to save it
				$field = new geoFieldsField ($field, $this);
				$this->_fields[$key] = $field;
			}
			//trigger_error('DEBUG FIELDS: Serializing field:  <pre>'.print_r($field,1).'</pre>');
			if (is_object($field)) {
				$result = $field->serialize();
				if (!$result) {
					//one bad seed can ruine the whole...  err... whatever that saying is
					return false;
				}
			}
		}
		$this->_pendingChanges = false;
		return true;
	}
	
	/**
	 * Called internally to signify that changes have been made to the settings
	 * that may need to be saved to the database.
	 */
	public function touch ()
	{
		$this->_pendingChanges = true;
	}
	
	/**
	 * Converts the fields to an array.
	 * 
	 * @return array Associative array of geoFieldsField objects, one array entry
	 *   for each field.
	 */
	public function toArray ()
	{
		$return = array ();
		$return['group_id'] = $groupId = $this->_group;
		$return['category_id'] = $categoryId = $this->_category;
		$fields = array ();
		foreach ($this->_fields as $field) {
			if (is_object($field)) {
				$field = $field->toArray();
			}
			$fields[$field['field_name']] = $field;
		}
		return $fields;
	}
	
	/**
	 * Allows using $fields->name that returns a value based on:
	 * 
	 * If default field is already set:  returns the permission for that field,
	 *   for example $fields->is_required
	 *   
	 * If default field is NOT set: acts just like $fields->getField() was called.
	 * 
	 * @param string $name
	 * @return mixed
	 */
	public function __get ($name)
	{
		return $this->getField($name);
	}
	
	/**
	 * NOT IMPLEMENTED!  Do not use $fields->name = ... as it won't work.  This
	 * is here for completeness only, if need to change a field edit it through
	 * the geoFieldsField object.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set ($name, $value) {
		//not implemented on purpose.
	}
	
	/**
	 * Magic method to allow something like isset($fields->field_name) to see if
	 * a given field even exists at all.
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function __isset ($name) {
		return isset($this->_fields[$name]);
	}
	
	/**
	 * Magic method to allow un-setting a field using unset($fields->field_name).
	 * It is not recommended to use this except internally.
	 * 
	 * @param string $name
	 */
	public function __unset($name) {
		//TODO: Implement
		unset($this->_fields[$name]);
	}
	
	/**
	 * Constructor, this cannot be run by itself, must get an instance using
	 * geoFields::getInstance($categoryId).  This constructor finds the
	 * category to pull the fields to use from, then gets the settings.
	 * 
	 * @param int $groupId
	 * @param int $categoryId
	 */
	private function __construct ($groupId, $categoryId)
	{
		//clean and set the "requested" category and group
		$this->_requestedCategory = $categoryId = (int)$categoryId;
		$this->_requestedGroup = $groupId = (int)$groupId;
		
		if (!geoPC::is_ent()) {
			//category and group are ent specific.
			$categoryId = $groupId = 0;
		}
		
		$db = DataAccess::getInstance();
		
		if ($categoryId) {
			//Bubble up to the first "parent" with what_fields_to_use not set to "parent" or until parent is 0
			$stmt = $db->Prepare("SELECT `category_id`, `parent_id`, `what_fields_to_use` FROM ".geoTables::categories_table." WHERE `category_id`=?");
			//figure out which category to use
			$parentId = $categoryId;
			do {
				$catInfo = $db->GetRow($stmt, array($parentId));
				$parentId = (int)((isset($catInfo['parent_id']))? $catInfo['parent_id'] : 0);
			} while ($catInfo && $parentId && $catInfo['what_fields_to_use'] == 'parent');
			
			//here the catInfo will either be invalid, or will be for a category
			//that has what_fiels_to_use set to "site" or "own"
			
			if ($catInfo && $catInfo['what_fields_to_use'] == 'own') {
				//use our own fields from the current category retrieved.
				$categoryId = (int)$catInfo['category_id'];
			} else {
				//we bubbled up as far as we can go, but no categories set to "own", so use site
				$categoryId = 0;
			}
		}
		$this->_category = $categoryId;
		
		//figure out which group to use
		if ($groupId) {
			//this one is a little easier, since no "parent groups" to worry about
			//so we don't have to bubble up, there is no "parent" possible
			$groupInfo = $db->GetRow("SELECT `what_fields_to_use` FROM ".geoTables::groups_table." WHERE `group_id`=?", array($groupId));
			if (!$groupInfo || $groupInfo['what_fields_to_use'] != 'own') {
				//use site default
				$groupId = 0;
			}
		}
		$this->_group = $groupId;
		trigger_error('DEBUG STATS: Getting field settings for '.$this->_category.' category and '.$this->_group.' group.');
		$this->unserialize();
	}
	
	/**
	 * Gets the default "locations" in an array of "reference" => "admin label"
	 * entries.
	 * 
	 * @param int $groupId Does not affect core locations returned but it may
	 *   affect addon locations depending on addon.
	 * @param int $categoryId Does not affect core locations returned but it may
	 *   affect addon locations depending on addon.
	 * @param string $type Either 'all', 'page', 'module', 'pic_module', or 'addon', for what type of
	 *   locations to return.  Param added in version 6.0.0
	 * @return array
	 */
	public static function getDefaultLocations ($groupId = 0, $categoryId = 0, $type='all')
	{
		$locations = array();
		
		if ($type=='all'||$type=='page') {
			$locations['browsing'] = 'Browse Category';
			$locations['tags'] = 'Browse Tag';
			$locations['search_fields'] = 'Search By';
			$locations['search_results'] = 'Search Results';
		}
		
		if ($type=='all'||$type=='module') {
			//module locations
			$locations['module-module_hottest_ads'] = array('short'=>'Hot','long'=>'Hottest Listings');
			$locations['module-newest_ads_1'] = array('short'=>'New 1','long'=>'Newest/Ending Soonest Listings 1');
			$locations['module-newest_ads_2'] = array('short'=>'New 2','long'=>'Newest/Ending Soonest Listings 2');
			$locations['module-featured_ads_1'] = array('short'=>'F1','long'=>'Featured Listings 1');
			$locations['module-featured_ads_2'] = array('short'=>'F2','long'=>'Featured Listings 2');
			$locations['module-featured_ads_3'] = array('short'=>'F3','long'=>'Featured Listings 3');
			$locations['module-featured_ads_4'] = array('short'=>'F4','long'=>'Featured Listings 4');
			$locations['module-featured_ads_5'] = array('short'=>'F5','long'=>'Featured Listings 5');
			$locations['module-featured_category_1'] = array('short'=>'F1 Cat','long'=>'Feature Category Listings 1');
			$locations['module-featured_category_2'] = array('short'=>'F2 Cat','long'=>'Feature Category Listings 2');
			$locations['module-module_featured_1_level_2'] = array('short'=>'F1 L2', 'long'=>'Featured Listings 1 Level 2');
			$locations['module-module_featured_2_level_2'] = array('short'=>'F2 L2', 'long'=>'Featured Listings 2 Level 2');
			$locations['module-module_featured_1_level_3'] = array('short'=>'F1 L3', 'long'=>'Featured Listings 1 Level 3');
			$locations['module-module_featured_2_level_3'] = array('short'=>'F2 L3', 'long'=>'Featured Listings 2 Level 3');
			$locations['module-module_featured_1_level_4'] = array('short'=>'F1 L4', 'long'=>'Featured Listings 1 Level 4');
			$locations['module-module_featured_2_level_4'] = array('short'=>'F2 L4', 'long'=>'Featured Listings 2 Level 4');
			$locations['module-module_featured_1_level_5'] = array('short'=>'F1 L5', 'long'=>'Featured Listings 1 Level 5');
			$locations['module-module_featured_2_level_5'] = array('short'=>'F2 L5', 'long'=>'Featured Listings 2 Level 5');
		}
		
		if ($type=='all'||$type=='pic_module') {
			//pic module locations
			$locations['module-module_featured_pic_1'] = array('short'=>'F1', 'long'=>'Featured Listings Pic Display 1');
			$locations['module-module_featured_pic_2'] = array('short'=>'F2', 'long'=>'Featured Listings Pic Display 2');
			$locations['module-module_featured_pic_3'] = array('short'=>'F3', 'long'=>'Featured Listings Pic Display 3');
			$locations['module-module_featured_pic_1_level_2'] = array('short'=>'F1 L2', 'long'=>'Featured Listings Pic 1 Level 2');
			$locations['module-module_featured_pic_2_level_2'] = array('short'=>'F2 L2', 'long'=>'Featured Listings Pic 2 Level 2');
			$locations['module-module_featured_pic_1_level_3'] = array('short'=>'F1 L3', 'long'=>'Featured Listings Pic 1 Level 3');
			$locations['module-module_featured_pic_2_level_3'] = array('short'=>'F2 L3', 'long'=>'Featured Listings Pic 2 Level 3');
			$locations['module-module_featured_pic_1_level_4'] = array('short'=>'F1 L4', 'long'=>'Featured Listings Pic 1 Level 4');
			$locations['module-module_featured_pic_2_level_4'] = array('short'=>'F2 L4', 'long'=>'Featured Listings Pic 2 Level 4');
			$locations['module-module_featured_pic_1_level_5'] = array('short'=>'F1 L5', 'long'=>'Featured Listings Pic 1 Level 5');
			$locations['module-module_featured_pic_2_level_5'] = array('short'=>'F2 L5', 'long'=>'Featured Listings Pic 2 Level 5');
		}
		
		if ($type=='all'||$type=='addon') {
			/**
			 * Each addon expected to return an array formated exactly like one above,
			 * with 'index' => 'Admin Label' for each entry.  Optionally, if need to have
			 * longer "label", can use array ('short'=>'short','long'=>'longer hover label')
			 * for the label, as it uses for modules above to conserve vertical space
			 */
			$addonLocations = geoAddon::triggerDisplay('geoFields_getDefaultLocations', array('groupId' => $groupId, 'categoryId' => $categoryId), geoAddon::ARRAY_ARRAY);
			foreach ($addonLocations as $addon => $thisLocations) {
				foreach ($thisLocations as $index => $location) {
					$locations["addon-$addon-$index"] = $location;
				}
			}
		}
		
		return $locations;
	}
	
	/**
	 * Gets the default fields and info for each field.
	 * 
	 * Returns an array like this:
	 * array (
	 * 	'section_index' => array (
	 * 		'legend' => 'Section Admin title',
	 * 		'fields' => array (
	 * 			'field_index' => array (
	 * 				'label' => 'Admin label',
	 * 				'type' => 'text', //(one of the types built in)
	 * 				'type_label' => 'Type Admin Label',//optional
	 * 				'type_select' => false, //optional, if true, admin will be able to change type for the field
	 * 				'opt_name_set' => true, //optional, only used for optional site-wide fields
	 * 				'opt_num' => $i, //optional, only used for optional site-wide fields
	 * 				'skipData' => array, //optional, array of field settings to skip, such as 'can_edit'
	 * 				'skipLocations' => array, //optional, array of locations that should NOT show an option for this field, or boolean true to skip all locations
	 * 			),
	 * 			...
	 * 		),
	 * 	),
	 *  ...
	 * )
	 * 
	 * @param int $groupId Does not affect core fields returned but it may
	 *   affect addon fields depending on addon.
	 * @param int $categoryId Does not affect core fields returned but it may
	 *   affect addon fields depending on addon.
	 * @return array A multi-dimensional array in the format specified above.
	 */
	public static function getDefaultFields ($groupId = 0, $categoryId = 0)
	{
		$fields = array();
		$fields['standard']['legend'] = 'Standard Listing Fields';
		
		//array of featured pic modules for fields that do not want to work in any
		//pic modules
		$pic_modules = array ('module-module_featured_pic_1','module-module_featured_pic_2','module-module_featured_pic_3',
			'module-module_featured_pic_1_level_2','module-module_featured_pic_2_level_2',
			'module-module_featured_pic_1_level_3','module-module_featured_pic_2_level_3',
			'module-module_featured_pic_1_level_4','module-module_featured_pic_2_level_4',
			'module-module_featured_pic_1_level_5','module-module_featured_pic_2_level_5',
		);
		
		$geographicOverrides = geoRegion::getLevelsForOverrides();
				
		$fields['standard']['fields'] = array (
			'photo' => array (
				'label' => 'Photo Icon/Thumbnail',
				'type' => 'other',
				'type_label' => 'Image/Icon',
				'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
				'skipLocations' => array_merge(array('search_fields'),$pic_modules),
			),
			'title' => array (
				'label' => 'Title',
				'type' => 'text',
				'skipData' => array (),
			),
			'icons' => array (
				'label' => 'Listing Icons (When Title not Showing)',
				'type' => 'other',
				'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
				'skipLocations' => array('search_fields'),
			),
			'description' => array (
				'label' => 'Description',
				'type' => 'textarea',
				'skipData' => array ()
			),
			'tags' => array (
				'label' => 'Listing Tags',
				'type' => 'other',
				'type_extra' => 'tags',
				'skipData' => array (),
				'skipLocations' => array('search_fields'),
			),
			'price' => array (
				'label' => 'Price',
				'type' => 'cost',
				'skipData' => array (),
			),
			'address' => array (
				'label' => 'Address',
				'type' => 'text',
				'skipData' => array (),
				'skipLocations' => array('search_fields'),
			),
			'city' => array (
				'label' => 'City'.($geographicOverrides['city'] ? ' <strong>(overridden by region level '.$geographicOverrides['city'].')</strong>' : ''),
				'type' => 'text',
				'skipData' => array (),
			),
			'zip' => array (
				'label' => 'Zip/Postal Code',
				'type' => 'text',
				'skipData' => array (),
			),
			'phone_1' => array (
				'label' => 'Phone 1',
				'type' => 'text',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'phone_2' => array (
				'label' => 'Phone 2',
				'type' => 'text',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'fax' => array (
				'label' => 'Fax',
				'type' => 'text',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'url_link_1' => array (
				'label' => 'URL Link 1',
				'type' => 'url',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'url_link_2' => array (
				'label' => 'URL Link 2',
				'type' => 'url',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'url_link_3' => array (
				'label' => 'URL Link 3',
				'type' => 'url',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'email' => array (
				'label' => 'E-Mail',
				'type' => 'email',
				'skipData' => array (),
				'skipLocations' => true
			),
			'mapping_location' => array (
				'label' => 'Mapping Location',
				'type' => 'text',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'business_type' => array (
				'label' => 'Business Type',
				'type' => 'other',
				'type_label' => 'Individual or Business Selection',
				'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
			),
			'payment_types' => array (
				'label' => 'Payment Types Accepted',
				'type' => 'other',
				'type_label' => 'Payment Types Selection',
				'skipData' => array (),
				'skipLocations' => true,
			),
			'show_contact_seller' => array (
				'label' => 'Option to Hide Contact Seller Link',
				'type' => 'other',
				'type_label' => 'Checkbox',
				'skipData' => array ('is_required'),
				'skipLocations' => true,
			),
			'show_other_ads' => array (
				'label' => 'Option to Hide Seller\'s Other Ads Link',
				'type' => 'other',
				'type_label' => 'Checkbox',
				'skipData' => array ('is_required'),
				'skipLocations' => true,
			),
		);
		
		//region fields
		trigger_error('DEBUG STATS: Before getting region field settings');
		$fields['regions']['legend'] = 'Geographic Regions';
		$fields['regions']['fields'] = geoRegion::getInstance()->getRegionFields();
		trigger_error('DEBUG STATS: After getting region field settings');
		
		//multi-level fields
		$leveled = geoLeveledField::getInstance()->getFieldsToUse();
		if ($leveled) {
			//add multi-level settings
			$fields['leveled']['legend'] = 'Multi-Level Fields';
			$fields['leveled']['fields'] = $leveled;
		}
		
		if (geoMaster::is('classifieds')) {
			$fields['classifieds']['legend'] = 'Classified Fields';
			$fields['classifieds']['fields'] = array (
				'classified_start' => array (
					'label' => 'Classified Entry Date',
					'type' => 'other',
					'type_label' => 'Date/Time',
					'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
				),
				'classified_time_left' => array (
					'label' => 'Classified Time Left',
					'type' => 'other',
					'type_label' => 'Time',
					'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
				),
			);
		}
		if (geoMaster::is('auctions')) {
			$fields['auctions']['legend'] = 'Auction Fields';
			$fields['auctions']['fields'] = array (
				'auction_start' => array (
					'label' => 'Auction Entry Date',
					'type' => 'other',
					'type_label' => 'Date/Time',
					'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
				),
				'auction_time_left' => array (
					'label' => 'Auction Time Left',
					'type' => 'other',
					'type_label' => 'Time',
					'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
				),
				'num_bids' => array (
					'label' => 'Number of Bids',
					'type' => 'other',
					'type_label' => 'number',
					'skipData' => array ('is_enabled', 'is_required', 'is_editable'),
					'skipLocations' => array('search_fields'),
				),
				'cost_options' => array (
					'label' => 'Buyer-Selected Options',
					'type' => 'other',
					'type_extra' => 'cost_options',
					'type_label' => 'Adds Cost Options',
					'skipData' => array (),
					'skipLocations' => true,
				),
			);
		}
		
		//add optional fields
		$fields['optional_fields']['legend'] = 'Optional Fields';
		$db = DataAccess::getInstance();
		for ($i=1; $i<=20; $i++) {
			//figure out the label the admin has given it
			
			$fields['optional_fields']['fields']['optional_field_'.$i] = array (
				'label' => $db->get_site_setting('optional_field_'.$i.'_name'),
				'type' => 'text',
				'type_select' => true,
				'opt_name_set' => true,
				'opt_num' => $i,
				'skipData' => array()
			);
		}
		
		
		/**
		 * Each addon expected to return an array formated exactly like the arrays at the
		 * 'fields' level in the array above, in other words, something like this:
		 * 
		 * array (
		 * 		'field_index1' => array (
		 * 			'label' => 'Admin label',
		 * 			'type' => 'text', //(one of the types built in)
		 * 			'type_label' => 'Type Admin Label',//optional
		 * 			'type_select' => false, //optional, if true, admin will be able to change type for the field
		 * 			'opt_name_set' => true, //optional, only used for optional site-wide fields
		 * 			'opt_num' => $i, //optional, only used for optional site-wide fields
		 * 			'skipData' => array, //optional, array of field settings to skip, such as 'can_edit'
		 *			'skipLocations' => array, //optional, array of locations that should NOT show an option for this field, or boolean true to skip all locations
		 * 		),
		 * 		...
		 * 	)
		 * 
		 */
		$addonFields = geoAddon::triggerDisplay('geoFields_getDefaultFields', array('groupId' => $groupId, 'categoryId' => $categoryId), geoAddon::ARRAY_ARRAY);
		foreach ($addonFields as $addon => $thisFields) {
			$info = geoAddon::getInfoClass($addon);
			if ($info && count($thisFields)) {
				$fields ['addon-'.$addon]['legend'] = $info->title . ' Addon\'s Fields';
				$fields ['addon-'.$addon]['fields'] = $thisFields;
			}
		}
		return $fields;
	}
	
	/**
	 * Gets meta info for all the listing info that can be displayed by the {listing}
	 * tag, plus info about label fields only available on listing details page.
	 * 
	 * @param array $include_types Narrow which types of tags (field, label, or tag)
	 *   only the types included in the array are returned.
	 * @param bool $flatArray Whether to return flat array or not
	 * @return array
	 * @since Version 7.1.0
	 */
	public static function getListingTagsMeta ($include_types = array ('field','label','tag'), $flatArray = false)
	{
		$data = array ();
		if (!isset(self::$_xmlMetaFields)) {
			self::$_xmlMetaFields = simplexml_load_file(CLASSES_DIR.PHP5_DIR.'meta/ListingTags.xml');
		}
		if (!self::$_xmlMetaFields) {
			return array();
		}
			
		foreach (self::$_xmlMetaFields as $section => $nodes) {
			foreach ($nodes as $name => $node) {
				if (strlen($node->only) && !geoMaster::is($node->only)) {
					continue;
				}
				if (!in_array($node->type, $include_types)) {
					//not what we want
					continue;
				}
				if ($flatArray) {
					//just want a flat array of junk
					$data[$name] = $name;
				} else {
					//want all the info!
					$data[$section][$name] = array (
						'name' => $name,
						'desc' => trim($node->desc),
						'type' => $node->type,
						'only' => $node->only,
					);
				}
			}
		}
		return $data;
	}
	
	/**
	 * Remove all fields to use settings for specific group/category.  If group
	 * is null, it will remove for all that match the set category.  Same goes
	 * if group is null and category is set.  If both are set (even to 0), will
	 * ONLY remove settings that match both.
	 * 
	 * This will NOT work to remove group 0 and category 0.
	 * 
	 * This is meant primarily to be called by other methods, such as when removing
	 * a category from the system or removing a group from the system.  As such
	 * it is meant to be as optimized as possible.
	 * 
	 * @param int $group
	 * @param int $category
	 * @since Version 6.0.6
	 */
	public static function remove ($group=null, $category=null)
	{
		if (!geoPC::is_ent()) {
			//if not enterprise, there is no group/category specific settings
			return;
		}
		
		if (!(int)$group && !(int)$category) {
			//Both can't eval to false, that means either set to 0 or null.. and
			//both can't be 0 or null as it could result in "main" settings
			//being removed, which this function does not do.
			trigger_error("ERROR FIELDS: Could not remove when no group/category is specified.");
			return;
		}
		
		$db = DataAccess::getInstance();
		
		$parts = array();
		if ($group!==null) {
			$groupId = (int)$group;
			$parts[] = "`field`.`group_id`=$groupId";
		}
		
		if ($category!==null) {
			$catId = (int)$category;
			$parts[] = "`field`.`category_id`=$catId";
		}
		
		//first, do search to see if there are any matching fields, cuz if not no
		//need to do complicated query...
		$count = (int)$db->GetOne("SELECT count(*) FROM ".geoTables::fields." as `field` WHERE ".implode(' AND ',$parts));
		if (!$count) {
			trigger_error("DEBUG FIELDS: No fields for group $group and cat $category found");
			return;
		}
		
		$query = "DELETE `field`, `loc` FROM ".geoTables::fields." as `field`
		LEFT JOIN ".geoTables::field_locations." as `loc`
		ON (`field`.`group_id`=`loc`.`group_id` AND `field`.`category_id`=`loc`.`category_id`
			AND `field`.`field_name`=`loc`.`field_name`)
		WHERE
		".implode(' AND ', $parts);
		
		$result = $db->Execute($query);
		
		if (!$result) {
			trigger_error("ERROR SQL:  Error when attempting to delete fields, query: $query ; error: ".$db->ErrorMsg());
		}
	}
	
	/**
	 * Specifically used in category admin, when making a copy of a category..
	 * 
	 * @param int $from_category
	 * @param int $to_category
	 */
	public static function copy ($from_category, $to_category)
	{
		$db = DataAccess::getInstance();
		$from_category = (int)$from_category;
		$to_category = (int)$to_category;
		//just a failsafe
		if (!$from_category || !$to_category) {
			geoAdmin::m("Invalid from/to when copying fields to use!",geoAdmin::ERROR);
			return false;
		}
		$fields = $db->Execute("SELECT * FROM ".geoTables::fields." WHERE `category_id`=$from_category");
		
		foreach ($fields as $row) {
			//copy the field
			$query_data = array (
				$row['group_id'], $to_category, $row['field_name'], $row['is_enabled'],
				$row['is_required'], $row['can_edit'], $row['field_type'], $row['type_data'],
				$row['text_length']
			);
				
			$result = $db->Execute("INSERT INTO ".geoTables::fields." SET `group_id`=?, `category_id`=?, `field_name`=?,
					`is_enabled`=?, `is_required`=?, `can_edit`=?, `field_type`=?, `type_data`=?, `text_length`=?",
					$query_data);
				
			if (!$result) {
				geoAdmin::m("Error copying fields to use, error reported: ".$db->ErrorMsg(),geoAdmin::ERROR);
				return false;
			}
				
			//copy all the attached locations
			$locs = $db->Execute("SELECT * FROM ".geoTables::field_locations." WHERE `group_id`=? AND `category_id`=? AND `field_name`=?",
					array($row['group_id'], $from_category, $row['field_name']));
			foreach ($locs as $display_location) {
				$query_data = array(
					$row['group_id'], $to_category, $row['field_name'],
					$display_location['display_location']
				);
				$result = $db->Execute("INSERT INTO ".geoTables::field_locations." SET `group_id`=?, `category_id`=?,
						`field_name`=?, `display_location`=?", $query_data);
		
				if (!$result) {
					geoAdmin::m("Error copying fields to use locations, error reported: ".$db->ErrorMsg(),geoAdmin::ERROR);
					return false;
				}
			}
		}
		return true;
	}
}

/**
 * A mini object to hold data about a specific field.
 * 
 * @package System
 * @since Version 5.0.0
 */
class geoFieldsField
{
	/**
	 * Used internally
	 * @internal
	 */
	private $_parentFields, $_data, $_valid, $_pendingChanges = false, $_locationsStart;
	
	/**
	 * The column types.
	 * @var array
	 * @internal
	 */
	private $_columnTypes = array (
		'group_id' => 'int',
		'category_id' => 'int',
		'field_name' => 'string',
		'is_enabled' => 'bool',
		'is_required' => 'bool',
		'can_edit' => 'bool',
		'field_type' => 'string',
		'type_data' => 'string',
		'text_length' => 'int',
		'display_locations' => 'array'
	);
	
	/**
	 * Constructor, sets up field's data.  Note that geoFieldsField objects
	 * should normally not be created outside of the geoFields class.  If not done
	 * correctly, the object will be marked as "invalid" and will be non-functional.
	 * 
	 * @param $data
	 * @param $parent
	 */
	public function __construct ($data, $parent)
	{
		if (get_class($parent) !== 'geoFields' && !is_subclass_of($parent, 'geoFields')) {
			//Invalid parent specified!  Do not trust!
			trigger_error('ERROR FIELDS: Cannot create new geoFieldToUse like that!  Leave it up to geoFields class to create me!');
			$this->_valid = false;
			return;
		}
		
		$this->_valid = true;
		
		foreach ($data as $key => $val) {
			$data[$key] = $this->clean($key, $val);
		}
		$this->_data = $data;
		$this->_parentFields = $parent;
		
		if (isset($data['using_defaults']) && $data['using_defaults']) {
			//using defaults, so assume there are changes so they get saved.
			$this->_pendingChanges = true;
			$this->_locationsStart = array();
		}
		if ($parent->getCategoryId() !== $data['category_id'] || $parent->getGroupId() !== $data['group_id']) {
			//the group or category is different, so must have picked it up from defaults...  mark it
			//as having changes so this doesn't happen more
			$this->_pendingChanges = true;
			$this->_locationsStart = array();
		} else {
			//save the starting locations so we know what to update
			$this->_locationsStart = (is_array($data['display_locations']))? $data['display_locations'] : array();
		}
		
	}
	
	/**
	 * Lets the object know there are pending changes that might need to be serialized.
	 * 
	 */
	public function touch()
	{
		if (!$this->_valid) {
			//this object was not started correctly
			return;
		}
		
		$this->_pendingChanges = true;
		$this->_parentFields->touch();
	}
	
	/**
	 * Gets the geoFields object with all the fields in it including this one.
	 * 
	 * @return geoFields
	 */
	public function getAllFields ()
	{
		if (!$this->_valid) {
			//this object was not started correctly
			return;
		}
		return $this->_parentFields;
	}
	
	/**
	 * Serializes settings for this field to the database.
	 * 
	 * @return bool
	 */
	public function serialize ()
	{
		if (!$this->_valid) {
			//this object was not started correctly
			trigger_error('DEBUG FIELDS: NOT VALID!  Cannot serialize.');
			return;
		}
		//save to the DB!
		if (!$this->_pendingChanges) {
			//no changes to save!
			trigger_error('DEBUG FIELDS: No pending changes, nothing to save!');
			return true;
		}
		
		trigger_error('DEBUG FIELDS: Top of field serialize!');
		$groupId = (int)$this->_parentFields->getGroupId();
		$categoryId = (int)$this->_parentFields->getCategoryId();
		
		
		$sql = "REPLACE INTO ".geoTables::fields." SET `group_id`=?, `category_id`=?,
			`field_name`=?, `is_enabled`=?, `is_required`=?, `can_edit`=?,
			`field_type`=?, `type_data`=?, `text_length`=?";
		
		$qData = array($groupId, $categoryId, $this->_data['field_name'], $this->_data['is_enabled'],
			$this->_data['is_required'], $this->_data['can_edit'], $this->_data['field_type'],
			$this->_data['type_data'], $this->_data['text_length']);
		
		$db = DataAccess::getInstance();
		$result = $db->Execute($sql, $qData);
		if (!$result) {
			trigger_error('ERROR SQL FIELDS: Error with serializing <pre>'.print_r($this->_data,1).'</pre> one of the fields, DB error: '.$db->ErrorMsg());
			return false;
		}
		
		//save display_locations, figure out the differences
		$currentLocations = (is_array($this->_data['display_locations']))? $this->_data['display_locations'] : array();
		
		if (!count($currentLocations)) {
			//easy, just remove all locations for this name
			$db->Execute("DELETE FROM ".geoTables::field_locations." WHERE `group_id`=? AND `category_id`=? AND `field_name`=?",
				array($groupId, $categoryId, $this->_data['field_name']));
		} else {
			//not as easy..  figure out what fields to add and what ones to remove
			$sameLocations = array_intersect($currentLocations, $this->_locationsStart);
			
			$addLocations = array_diff($currentLocations, $sameLocations);
			$removeLocations = array_diff($this->_locationsStart, $sameLocations);
			//first add all the new locations
			foreach ($addLocations as $location) {
				if (!$location) {
					//something odd, empty string
					continue;
				}
				$db->Execute("INSERT INTO ".geoTables::field_locations." (`group_id`, `category_id`, `field_name`, `display_location`) VALUES (?, ?, ?, ?)",
					array($groupId, $categoryId, $this->_data['field_name'], ''.$location));
			}
			//now remove all the old locations
			foreach ($removeLocations as $location) {
				if (!$location) {
					//something odd, empty string
					continue;
				}
				$db->Execute("DELETE FROM ".geoTables::field_locations." WHERE `group_id`=? AND `category_id`=? AND `field_name`=? AND `display_location`=?",
					array($groupId, $categoryId, $this->_data['field_name'], ''.$location));
			}
		}
		$this->_locationsStart = $currentLocations;
		$this->_pendingChanges = false;
		return true;
	}
	
	/**
	 * Adds a location to the locations this field should be displayed in.
	 * @param string $name
	 */
	public function addLocation ($name)
	{
		if (!$name || in_array($name, $this->_data['display_locations'])) {
			return;
		}
		$this->_data['display_locations'][] = $name;
		$this->touch();
	}
	
	/**
	 * Removes all locations this field should be displayed in.
	 * 
	 */
	public function clearLocations ()
	{
		if ($this->_data['display_locations'] === array()) {
			//already cleared
			return;
		}
		$this->_data['display_locations'] = array();
		$this->touch();
	}
	
	/**
	 * Cleans a value according to what field the value is for.
	 * 
	 * @param string $name the field name that is being cleaned
	 * @param mixed $value
	 * @return mixed The cleaned value.
	 */
	public function clean ($name, $value)
	{
		$validNames = $this->_columnTypes;
		if (!isset($validNames[$name])) {
			//not a valid thing to be setting!
			return;
		}
		
		//clean input
		switch ($validNames[$name]) {
			case 'int':
				$value = (int)$value;
				break;
				
			case 'string':
				$value = ''.$value;
				break;
				
			case 'bool':
				//use 1 or 0 for DB saving reasons
				$value = ($value)? 1 : 0;
				break;
				
			case 'array':
				//force the value to be an array
				$value = (array)$value;
				break;
		}
		return $value;
	}
	
	/**
	 * Returns an array of the data for this field.
	 * 
	 * @return array
	 */
	public function toArray ()
	{
		return $this->_data;
	}
	
	
	/**
	 * Magic method, allows syntax like $field->is_enabled
	 * 
	 * @param string $name
	 */
	public function __get ($name)
	{
		if (!$this->_valid) {
			//this object was not started correctly
			return;
		}
		//enabled is more important, if it is not enabled, everything else doesn't matter
		if (!isset($this->_data['is_enabled']) || !$this->_data['is_enabled']) {
			//return depending on what type of thing was requested
			return $this->clean($name, null);
		}
		
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		//could not find what they were looking for!
		$trace = debug_backtrace();
		trigger_error ('ERROR FIELDS: Could not find property via __get(): '.$name.
			' in '.$trace[0]['file'].
			' on line '.$trace[0]['line']);
		
		return null;
	}
	/**
	 * Magic method, allows changing values using $field->is_enabled = true (for instance)
	 * to change the different settings for this field.
	 * 
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set ($name, $value)
	{
		if (!$this->_valid) {
			//this object was not started correctly
			return;
		}
		if (isset($this->_columnTypes[$name])) {
			$value = $this->clean($name, $value);
			
			if ($this->_data[$name] !== $value) {
				$this->_data[$name] = $value;
				
				$this->touch();
			}
		}
	}
	
	/**
	 * Magic method to see if the given setting is set or not using syntax
	 * like isset($field->is_enabled).
	 * 
	 * @param string $name
	 */
	public function __isset($name)
	{
		if (!$this->_valid) {
			//this object was not started correctly
			return false;
		}
		return (isset($this->_data[$name]));
	}
}
