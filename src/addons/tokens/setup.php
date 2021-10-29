<?php
//addons/tokens/setup.php
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
## ##    7.0.0
## 
##################################

# tokens Addon
require_once ADDON_DIR . 'tokens/info.php';

class addon_tokens_setup extends addon_tokens_info
{
	public function install ()
	{
		$db = DataAccess::getInstance();
		
		$sqls[] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_tokens_prices` (
  `price_plan_id` int(11) NOT NULL,
  `tokens` int(11) NOT NULL,
  `price` float NOT NULL,
  `expire_period` int(11) NOT NULL,
  PRIMARY KEY (`price_plan_id`,`tokens`),
  KEY `price_plan_id` (`price_plan_id`)
)";
		
		foreach ($sqls as $sql) {
			$db->Execute($sql);
		}
		
		return true;
	}
	
	public function uninstall ()
	{
		$db = DataAccess::getInstance();
		
		//get rid of table
		$db->Execute("DROP TABLE `geodesic_addon_tokens_prices`");
		
		return true;
	}
}