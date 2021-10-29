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

class phone2ImportItem extends geoImportItem
{
	protected $_name = "Phone Number 2";
	protected $_description = "The user's secondary phone number";
	protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;
	
	public $displayOrder = 7;
	
	protected final function _cleanValue($value)
	{
		$value = addslashes(trim($value));
		return $value;
	}
	
	protected final function _updateDB($value, $groupId)
	{
		geoImport::$tableChanges['userdata']['phone2'] = " `phone2` = '{$value}' ";
		return true;
	}
	 
}