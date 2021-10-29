<?php
//addons/charity_tools/setup.php
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
## 
##    7.5.3-36-gea36ae7
## 
##################################

# Charity Tools

require_once ADDON_DIR . 'charity_tools/info.php';

class addon_charity_tools_setup extends addon_charity_tools_info
{
	public function install()
	{
		$sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_charity_tools_neighborly` (
				  `user` int(11) NOT NULL,
				  `active_until` int(11) NOT NULL,
				  PRIMARY KEY (`user`)
				)";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_charity_tools_charitable` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(255) NOT NULL,
					`image` varchar(255) NOT NULL,
					`region` int(11) NOT NULL DEFAULT '0',
					`zipcode` varchar(15) NOT NULL DEFAULT '',
					`description` mediumtext NOT NULL DEFAULT '',
					PRIMARY KEY (`id`)
				)";
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_charity_tools_charitable_purchases` (
					`listing` int(11) NOT NULL,
					`time` int(11) NOT NULL,
					`purchased_badge` varchar(255) NOT NULL,
					`price` float(10,2) NOT NULL,
					PRIMARY KEY (`listing`)
				)";
		
		$db = DataAccess::getInstance();
		foreach($sqls as $sql) {
			$db->Execute($sql);
		}
		
		$reg = geoAddon::getRegistry($this->name, true);
		$reg->neighborly_duration = 12;
		$reg->neighborly_image = 'good-neighbor.png';
		$reg->save();
		
		return true;
	}
	
	public function upgrade($old_version)
	{
		$db = DataAccess::getInstance();
		if(version_compare($old_version, '1.2.0', '<')) {
			if(!$db->Execute("ALTER TABLE `geodesic_addon_charity_tools_charitable` ADD `zipcode` varchar(15) NOT NULL DEFAULT ''")) {
				return false;
			}
		}
		if(version_compare($old_version, '1.3.0', '<')) {
			if(!$db->Execute("ALTER TABLE `geodesic_addon_charity_tools_charitable` ADD `description` mediumtext NOT NULL DEFAULT ''")) {
				return false;
			}
		}
		return true;
	}
	
	public function uninstall()
	{
		$sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_charity_tools_neighborly`";
		$sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_charity_tools_charitable`";
		$sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_charity_tools_charitable_purchases`";
		
		$db = DataAccess::getInstance();
		foreach($sqls as $sql) {
			$db->Execute($sql);
		}
		
		return true;
	}
}