<?php
//addons/anonymous_listing/setup.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

# Anonymous Listing Addon

require_once ADDON_DIR . 'anonymous_listing/info.php';

class addon_anonymous_listing_setup extends addon_anonymous_listing_info
{
		
	function install () {
				
		//get $db connection - use get_common_vars.php to be forward compatible
		$db = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		
		$sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_anonymous_listing` (
  				`listing_id` int(14) NOT NULL,
				`password` varchar(255) NOT NULL,
				`ip_address` varchar(32) NOT NULL DEFAULT '')";
		$result = $db->Execute($sql);
		if (!$result){
			//query failed, return false.
			return false;
		}
		
		$success = $this->addAnonUser();
		if(!$success) {
			return false;
		}
				
		//execute successful, install worked.
		return true;
	}
	
	function upgrade($old_version){
		$db = DataAccess::getInstance();
		switch($old_version) {
			case '1.0.0':
				//first release didn't have text in db by default
				//check to see if text for this addon exists in db
				$sql = "SELECT `auth_tag` FROM `geodesic_addon_text` WHERE `auth_tag` = 'geo_addons' AND `addon` = 'anonymous_listing'";
				$result = $db->Execute($sql);
				if($result->RecordCount() == 0) {
					//no text yet -- insert the defaults
					$sql = "INSERT INTO `geodesic_addon_text` (`auth_tag`, `addon`, `text_id`, `language_id`, `text`) VALUES 
					('geo_addons', 'anonymous_listing', 'passwordLabel', 1, 'Input the password to edit this listing: '),
					('geo_addons', 'anonymous_listing', 'passwordButtonText', 1, 'Submit'),
					('geo_addons', 'anonymous_listing', 'passwordError', 1, 'Incorrect password. Please try again.'),
					('geo_addons', 'anonymous_listing', 'passwordCancelLink', 1, 'Cancel Edit'),
					('geo_addons', 'anonymous_listing', 'placementText1', 1, 'You are placing this listing anonymously.'),
					('geo_addons', 'anonymous_listing', 'placementText2', 1, 'You will be able to edit it later by using the following password:'),
					('geo_addons', 'anonymous_listing', 'placementContinueLink', 1, 'Continue placing this listing'),
					('geo_addons', 'anonymous_listing', 'placementCancelLink', 1, 'Cancel Listing'),
					('geo_addons', 'anonymous_listing', 'emailText', 1, 'You have placed this listing anonymously. To edit it in the future, you will need to input this password:')";
					if(!$db->Execute($sql)) return false;
				}
				//break intentionally omitted
				
			case '1.1.0':
				//break intentionally omitted
				
			case '1.2.0':				
				$sql = "ALTER TABLE geodesic_addon_anonymous_listing ADD COLUMN
				(`ip_address` varchar(32) NOT NULL DEFAULT '')";
				if(!$db->Execute($sql))	return false;
				//break intentionally omitted
				
			case '1.3.0':
				//add the anonymous user
				if(!$this->addAnonUser()) return false;
				//break intentionally omitted
				
			case '1.4.0':
				//add default for anonymous user name
				$registry = geoAddon::getRegistry('anonymous_listing', true);
				$registry->set('anon_user_name','Anonymous');
				$registry->save();
				break;
				
			default:
				break;
		}
		return true;
	}
	
	function uninstall () {
		$db = true;
		include GEO_BASE_DIR . 'get_common_vars.php';
		
		$sql = 'DELETE TABLE `geodesic_addon_anonymous_listing`';
		$result = $db->Execute($sql);
		
		$registry = geoAddon::getRegistry('anonymous_listing', true);
		if($registry){
			$anon_id = $registry->get('anon_user_id',false);
			if($anon_id) {
				//delete anonymous user
				$sql = "delete from geodesic_logins where id = ".$anon_id;
				$db->Execute($sql);
				$sql = "delete from geodesic_userdata where id = ".$anon_id;
				$db->Execute($sql);
				$sql = "delete from geodesic_user_groups_price_plans where id = ".$anon_id;
				$db->Execute($sql);
				$registry->set('anon_user_id','');
				$registry->save();
				
				//attach any remaining anonymous ads to seller 0,
				//so they get re-assigned to new anon user on reinstallation
				$sql = "update `geodesic_classifieds` set seller = 0 WHERE seller = ".$anon_id;
				$db->Execute($sql);
			}
		}
		
		
		
		return true;
	}
	
	/**
	 * creates an anonymous "user" so that
	 * anonymous listings can be shown in the Admin
	 *
	 * @return bool success
	 */
	function addAnonUser()
	{
		$db = DataAccess::getInstance();
		$registry = geoAddon::getRegistry('anonymous_listing', true);
		
		if($registry->get('anon_user_id')) {
			//already set -- skip
			return true;
		}
		
		//create a new user, reserved for anonymous
		$sql = "INSERT IGNORE INTO `geodesic_logins` (username, password, status) VALUES 
					('Anonymous', '', 0)";
		$result = $db->Execute($sql);
		if(!$result) {
			return false;
		}
		$id = $db->Insert_Id();
		
		//also add it to userdata table, because referential integrity is a good thing
		$sql = "INSERT IGNORE INTO `geodesic_userdata` (id, username) VALUES ('".$id."', 'Anonymous')";
		$result = $db->Execute($sql);
		if(!$result) {
			return false;
		}
		
		$registry->set('anon_user_id', $id);
		$registry->save();
		
		//change any pre-existing anonymous listings (with seller = 0) to use new Anonymous user
		$sql = "UPDATE `geodesic_classifieds` SET `seller` = ".$id." WHERE `seller` = 0";
		$result = $db->Execute($sql);
		if(!$result) {
			return false;
		}
		
		//add to user groups price plans table
		//cheat and pull default values from Admin user
		$sql = "select * from geodesic_user_groups_price_plans where id=1";
		$defaults = $db->GetRow($sql);
		$sql = "INSERT INTO geodesic_user_groups_price_plans (id, group_id, price_plan_id, auction_price_plan_id) VALUES
				('".$id."','".$defaults['group_id']."','".$defaults['price_plan_id']."','".$defaults['auction_price_plan_id']."')";
		$result = $db->Execute($sql);
		if(!$result) {
			return false;
		}
		
		
		return true;
	}
}