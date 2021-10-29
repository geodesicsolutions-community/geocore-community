<?php
//addons/zipsearch/setup.php
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
## ##    7.4.4-22-g21b6325
## 
##################################

# Zip/Postal Code Search
require_once ADDON_DIR . 'zipsearch/info.php';

class addon_zipsearch_setup extends addon_zipsearch_info
{
	public function install()
	{
		//set up default settings
		$reg = geoAddon::getRegistry('zipsearch', true);
		$reg->enabled = 1;
		$reg->units = 'M';
		$reg->search_method = 'exact';
		$reg->save();
		return true;
	}
	
	
	public function enable ()
	{
		geoAdmin::m('You are not finished!  Make sure you import the zipsearch data in the admin at <a href="index.php?page=insertZipData">Addons > Zip/Postal Code Search > Import Zip Data</a>', geoAdmin::NOTICE);
		return true;
	}
	
	public function upgrade($oldVersion)
	{
		if(version_compare($oldVersion, '1.8.6', '<=')) {
			//coming from an older version of zipsearch before these settings were in the addon itself
			$oldSetting = DataAccess::getInstance()->get_site_setting('use_zip_distance_calculator');
			$reg = geoAddon::getRegistry('zipsearch', true);
			$reg->enabled = ($oldSetting == 1 || $oldSetting == 2) ? 1 : 0;
			$reg->units = 'M';
			$reg->search_method = $oldSetting == 2 ? 'hierarchical' : 'exact';
			$reg->save();
		}
		return true;
	}
	
	public function uninstall()
	{
		//Remove zipsearch table
		$db = DataAccess::getInstance();
		
		$db->Execute("DROP TABLE IF EXISTS `geodesic_zip_codes`");
		
		return true;
	}
}