<?php
//addons/multi_admin/setup.php
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
## ##    7.2beta3-72-g9718307
## 
##################################

# multi_admin Addon
require_once ADDON_DIR . 'multi_admin/info.php';

class addon_multi_admin_setup extends addon_multi_admin_info
{
	public function install () {
		$db = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		
		//To avoid table name conflicts, make sure to prefix any tables with
		//the module name.
		$sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_multi_admin_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default 'group',
  `display` text NOT NULL,
  `update` text NOT NULL,
  PRIMARY KEY  (`group_id`),
  UNIQUE KEY `name` (`name`)
)";
		
		$result = $db->Execute($sql);
		if (!$result){
			//query failed, return false.
			return false;
		} 
		$sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_multi_admin_users` (
`user_id` INT( 11 ) NOT NULL DEFAULT '0',
`group_id` INT( 11 ) NOT NULL DEFAULT '0',
`display` TEXT NOT NULL ,
`update` TEXT NOT NULL ,
PRIMARY KEY ( `user_id` )
)";
		$result = $db->Execute($sql);
		if (!$result){
			//query failed, return false.
			return false;
		}
		
		return true;
	}
	
	public function uninstall (){
		//script to uninstall the multi_admin addon.
		
		//get $db connection - use get_common_vars.php to be forward compatible
		//see that file for documentation.
		$db = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		
		
		$sql = 'DROP TABLE IF EXISTS `geodesic_addon_multi_admin_groups`';
		$result = $db->Execute($sql);
		if (!$result){
			//query failed, return false
			return false;
		}
		$sql = 'DROP TABLE IF EXISTS `geodesic_addon_multi_admin_users`';
		$result = $db->Execute($sql);
		if (!$result){
			//query failed, return false
			return false;
		}
		return true;
	}
	
	public function upgrade ($from_version = false) {
		return true;
	}
}