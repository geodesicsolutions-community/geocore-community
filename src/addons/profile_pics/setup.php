<?php
//addons/profile_pics/setup.php
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
##    16.09.0-44-g833ba7b
## 
##################################

require_once ADDON_DIR . 'profile_pics/info.php';

class addon_profile_pics_setup extends addon_profile_pics_info
{
	public function install()
	{
		
		$sql = "CREATE TABLE IF NOT EXISTS `geodesic_addon_profile_pics`(
			`user_id` INT(1) NOT NULL,
			`pic_data` LONGBLOB,
			PRIMARY KEY (`user_id`)
		)";
		$r = DataAccess::getInstance()->Execute($sql);
		
		//default config settings
		$reg = geoAddon::getRegistry($this->name, true);
		$reg->viewport_width = 172;
		$reg->viewport_height = 172;
		$reg->boundary_width = 250;
		$reg->boundary_height = 250;
		$reg->save();
		
		return true;
	}
	
	public function uninstall()
	{
		$sql = "DROP TABLE IF EXISTS `geodesic_addon_profile_pics`";
		$r = DataAccess::getInstance()->Execute($sql);
		return true;
	}
}