<?php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
## 
##################################

abstract class geoImportItem
{
	protected $_name = '(User-Friendly Field Name goes here)';
	protected $_description = '(User-Friendly Field Description goes here [incl. formatting options e.g. "1 - on; 0 - off"])';
	
	const NOT_USED_FIELDGROUP = 1; //should only hold the "unused" item type
	const USER_GENERAL_FIELDGROUP = 2;
	const USER_OPTIONAL_FIELDGROUP = 3;
	const USER_LOCATION_FIELDGROUP = 4;
	const USER_LOGIN_FIELDGROUP = 5; //data from geodesic_logins table
	const USER_UGPP_FIELDGROUP = 6; //data from geodesic_user_groups_price_plans table
	const USER_ADDON_FIELDGROUP = 7; //data from addons
	
	protected $_fieldGroup = self::NOT_USED_FIELDGROUP;
	
	public $requires = false; //a way for an importitem to specify that another item must be set first
	public $displayOrder = 0; //change if needing to sort a fieldGroup by something other than alphabetical

	public function processToken($value, $groupId)
	{
		//validate and clean value
		$value = $this->_cleanValue($value);
		if($value === false) {
			//only die on hard-false (so items can still use a value of "" or 0 or something)
			return false;
		}
		
		//do whatever is needed to update the db for this value
		if(!$this->_updateDB($value, $groupId)) {
			return false;
		}
		return true;
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function getDescription()
	{
		return $this->_description;
	}
	
	public function getFieldGroup()
	{
		return $this->_fieldGroup;
	}
	
	public function getSaveName()
	{
		//convenience method to get the first part of the class name, for easily storing a list of items to the registry
		$className = get_class($this);
		$saveName = substr($className, 0, strpos($className, 'ImportItem')); // e.g. "geo" for "geoImportItem"
		return $saveName;
	}
	
	protected function _cleanValue($value)
	{
		//do whatever is needed to prep this specific type of item for addition to the DB (geoString::toDB(), etc)
		
		//also keep in mind that this may support user imput in the future -- here would be the place to validate it
		
		//on a critical error, return false (but remember it may be more helpful to simply blank out a value in some cases)
		
		return $cleanedValue;
	}
	
	protected function _updateDB($value, $groupId)
	{
		$sql = "UPDATE `some_table` SET `some_value` = '{$value}' WHERE `id` = '{$groupId}'";
		return $success;
	}
}