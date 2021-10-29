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

class businessTypeImportItem extends geoImportItem
{
	protected $_name = "Account Type (Business Type)";
	protected $_description = "0 - none; 1 - individual; 2 - business";
	protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;
	
	public $displayOrder = 2;
	
	protected final function _cleanValue($value)
	{
		$value = trim($value);
		$value = intval($value);
		if(!in_array($value, array(0,1,2))) {
			trigger_error('DEBUG IMPORT: invalid business type -- setting to 0');
			$value = 0;
		}
		geoImport::$crosstalk['business_type'] = $value; //save to crosstalk for use in assigning usergroups according to defaults
		return $value;
	}
	
	
	protected final function _updateDB($value, $groupId)
	{
		geoImport::$tableChanges['userdata']['business_type'] = " `business_type` = '{$value}' ";
		return true;
	}
	 
}