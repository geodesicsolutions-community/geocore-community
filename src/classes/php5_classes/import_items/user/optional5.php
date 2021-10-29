<?php

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
##
##    7.5.3-36-gea36ae7
##
##################################

class optional5ImportItem extends geoImportItem
{
	protected $_name = "Registration Optional Field 5";
	protected $_description; //set in constructor (field label from DB)
	private $_fieldNum = 5;
	
	public function __construct()
	{
		$this->_description = DataAccess::getInstance()->GetOne("SELECT `registration_optional_{$this->_fieldNum}_field_name` FROM ".geoTables::registration_configuration_table);
		$this->displayOrder = $this->_fieldNum;
	}
	
	protected $_fieldGroup = self::USER_OPTIONAL_FIELDGROUP;
	
	protected final function _cleanValue($value)
	{
		$value = addslashes(trim($value));
		return $value;
	}
	
	protected final function _updateDB($value, $groupId)
	{
		geoImport::$tableChanges['userdata']['optional_field_'.$this->_fieldNum] = " `optional_field_{$this->_fieldNum}` = '{$value}' ";
		return true;
	}
	 
}