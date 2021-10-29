<?php
//addons/pedigree_tree/setup.php
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

# Pedigree Tree

require_once ADDON_DIR . 'pedigree_tree/info.php';

class addon_pedigree_tree_setup extends addon_pedigree_tree_info
{
	public function install ()
	{
		$db = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		
		$sqls [] = "CREATE TABLE IF NOT EXISTS `geodesic_addon_pedigree_tree_listings` (
  `id` int(11) NOT NULL auto_increment,
  `listing_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `gender` enum('sire','dam') NOT NULL,
  `generation` int(11) NOT NULL,
  `child` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `name` (`name`),
  KEY `generation` (`generation`),
  KEY `child` (`child`)
) AUTO_INCREMENT=1";
		
		foreach ($sqls as $sql) {
			if (!$db->Execute($sql)) {
				geoAdmin::m('DB Error: '.$db->ErrorMsg(), geoAdmin::ERROR);
				return false;
			}
		}
		
		$reg = geoAddon::getRegistry($this->name,true);
		//set defaults
		$reg->maxGens = 3;
		$reg->maxReqGens = 3;
		$reg->iconSet = 'horse';
		$reg->save();
		
		return true;
	}
	
	public function upgrade ()
	{
		$reg = geoAddon::getRegistry($this->name,true);
		//set defaults for anything not set
		if (!isset($reg->iconSet)) {
			$reg->iconSet = 'horse';
		}
		$reg->save();
		$this->showAdminNotes();
		return true;
	}
	
	
	public function enable ()
	{
		$this->showAdminNotes();
		return true;
	}
	
	public function showAdminNotes ()
	{
		geoAdmin::m('<strong>Fields to Use Settings:</strong>  Note that you will need to enable the Pedigree Tree fields on the page <a href="index.php?page=fields_to_use">Listing Setup > Fields to Use</a> in the admin panel,
		either site-wide or for a specific category/user group.',geoAdmin::NOTICE);
		geoAdmin::m('<strong>To Display in Listing:</strong>  Don\'t forget to add the addon tag to your listing details template(s) so that the pedigree tree information displays for each listing.  The tag to add will be:<br />
			<br />
			<div class="center">{addon author=\'geo_addons\' addon=\'pedigree_tree\' tag=\'listing_tree\'}</div>', geoAdmin::NOTICE);
		
	}
	
	public function uninstall ()
	{
		$db = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		
		$sqls[] = "DROP TABLE IF EXISTS `geodesic_addon_pedigree_tree_listings`";
		
		foreach ($sqls as $sql) {
			if (!$db->Execute($sql)) {
				geoAdmin::m('DB Error: '.$db->ErrorMsg(), geoAdmin::ERROR);
			}
		}
		return true;
	}
}
