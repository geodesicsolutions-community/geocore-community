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

class user_page_2_titleImportItem extends geoImportItem
{
	private $_pageNum = 2;
	protected $_name = 'Storefront: User Page 2 Title';
	protected $_description = 'The name and link text for user page 2';
	
	public $requires = 'subscription_duration';
	public $displayOrder = 5;
		
	protected $_fieldGroup = self::USER_ADDON_FIELDGROUP;	
	
	protected function _cleanValue($value)
	{
		$value = trim($value);
		//make sure there's a storefront subscription in place first!
		if($value && !geoImport::$crosstalk['storefront_subscription_active']) {
			trigger_error('ERROR IMPORT: cannot add storefront data without a storefront subscription');
			return false;
		}
		return geoString::specialChars($value);
	}
	
	private $_settings;
	protected function _updateDB($value, $groupId)
	{
		if(!geoImport::$crosstalk['storefront_subscription_active']) {
			//no storefront active here, so nothing to do
			//if it got this far, this is likely not an error, just a user without a storefront, so return true
			return true;
		}
		if(!geoImport::$crosstalk['storefront_subscription_active']) {
			trigger_error('ERROR IMPORT: no storefront sub active before doing page '.$this->_pageNum);
			return false;
		}
		$db = DataAccess::getInstance();
		
		if(!$this->_settings) {
			$this->_settings = $db->Prepare("INSERT INTO geodesic_addon_storefront_pages (`owner`, `page_link_text`, `page_name`, `display_order`) VALUES (?,?,?,?)");
		}
		
		if(!$db->Execute($this->_settings, array($groupId, $value, $value, $this->_pageNum))) {
			trigger_error('ERROR IMPORT: error adding storefront page '.$this->_pageNum);
			return false;
		}
		geoImport::$crosstalk['storefront_user_page_'.$this->_pageNum] = true;
		return true;
	}
}