<?php
//addons/google_maps/setup.php

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
## ##    7.1.2-31-gb349ac2
## 
##################################

# google_maps Addon

require_once ADDON_DIR . 'google_maps/info.php';

class addon_google_maps_setup extends addon_google_maps_info
{	
	public function upgrade ($oldVersion)
	{
		$db = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		
		$reg = geoAddon::getRegistry('google_maps',true);
		
		if ($oldVersion == '1.0.0') {
			//transfer over settings and remove old table, now we use addon registry.
			$all = $db->GetAll("SELECT * FROM `geodesic_addon_google_maps`");
			
			foreach ($all as $row) {
				$reg->set($row['setting'], $row['value']);
			}
			$db->Execute('DROP TABLE IF EXISTS `geodesic_addon_google_maps`');
		}
		if (version_compare($oldVersion, '2.0.0','<')) {
			//updating from before 2.0.0...
			geoAdmin::m("You will need to update your google maps license to work with Google Maps API v3.  See the instructions linked in the addon settings page.",geoAdmin::NOTICE);
		}
		
		if ($reg->apikey) {
			//move where it is saved
			$db->set_site_setting('googleApiKey', $reg->apikey);
			//un-set it so it doesn't do this next update
			$reg->apikey = false;
		}
		$reg->save();
		
		return true;
	}
}