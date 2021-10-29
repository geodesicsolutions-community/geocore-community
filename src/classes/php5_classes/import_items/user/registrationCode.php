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

class registrationCodeImportItem extends geoImportItem
{
	protected $_name = "Registration Code";
	protected $_description = "The registration code corresponding to the User Group this user should be placed in (leave blank for no code)";
	protected $_fieldGroup = self::USER_UGPP_FIELDGROUP;
	
	public $displayOrder = 0;
	
	protected final function _cleanValue($value)
	{
		$value = trim($value);
		return $value;
	}
	
	private $_codes = array();
	protected final function _updateDB($value, $groupId)
	{
		if(!$value) {
			//no code, so nothing to do
			return true;
		}
		
		if(!$this->_codes[$value]) {
			$sql = "SELECT `group_id`, `price_plan_id`, `auction_price_plan_id` FROM `geodesic_groups` WHERE `registration_code` = ?";
			$result = DataAccess::getInstance()->GetRow($sql, array($value));
			if($result) {
				$this->_codes[$value]['group_id'] = $result['group_id'];
				$this->_codes[$value]['price_plan_id'] = $result['price_plan_id'];
				$this->_codes[$value]['auction_price_plan_id'] = $result['auction_price_plan_id'];
			} else {
				$this->_codes[$value] = 'NO_CODE'; 
			}
		}
		
		if($this->_codes[$value] === 'NO_CODE') {
			//given registration code doesn't match anything in the database
			trigger_error('DEBUG IMPORT: registration code not found in database: '.$value);
			return true;
		} else {
			geoImport::$tableChanges['ugpp']['group_id'] = " `group_id` = '{$this->_codes[$value]['group_id']}' ";
			geoImport::$tableChanges['ugpp']['price_plan_id'] = " `price_plan_id` = '{$this->_codes[$value]['price_plan_id']}' ";
			geoImport::$tableChanges['ugpp']['auction_price_plan_id'] = " `auction_price_plan_id` = '{$this->_codes[$value]['auction_price_plan_id']}' ";
		}
		return true;
	}
	 
}