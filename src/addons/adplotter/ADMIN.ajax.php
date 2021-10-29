<?php
// addons/exporter/ADMIN.ajax.php
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


if(class_exists( 'admin_AJAX' ) or die());

class addon_adplotter_ADMIN_ajax extends admin_AJAX
{
	public function getSubcategories()
	{
		$util = geoAddon::getUtil('adplotter');
		//get parent key array, then find out which parent category we're looking for
		$parent_key = (int)$_GET['parent_key'];
		$parentKeys = $util->getParentKeys();
		$for = array_search($parent_key, $parentKeys);
		
		if(!$for) {
			echo 'ERROR: could not find subcategories';
			return;
		}		
		
		//get local mapping
		$db = DataAccess::getInstance();
		$search = substr($for,0,3)."%"; //search for the first three letters of the parent category, just to narrow things down a bit for speed
		$result = $db->Execute("SELECT * FROM `geodesic_addon_adplotter_category_map` WHERE `adplotter_name` LIKE ?", array($search));
		$ap_geo_cat_map = array();
		foreach($result as $c) {
			$ap_geo_cat_map[geoString::fromDB($c['adplotter_name'])] = $c['geo_id'];
		}
		//NOTE: do not iterate over $ap_geo_cat_map directly, as it may contain extra data (as in that for "Musical Instruments" when looking for just "Music"
		
		//get foreign mapping
		
		
		$ap_cats = $util->getAdPlotterCategories($for);
		require_once(CLASSES_DIR.'site_class.php');
		$site = Singleton::getInstance('geoSite');
		$depth = $db->get_site_setting('levels_of_categories_displayed_admin') ? $db->get_site_setting('levels_of_categories_displayed_admin') : 3;
		foreach($ap_cats as $parent => $subcat) {
			$selected = $ap_geo_cat_map[$subcat] ? $ap_geo_cat_map[$subcat] : 0;
			$tpl_vars['subs'][] = array('selected' => $selected, 'name' => geoString::toDB($subcat), 'ddl' => $site->get_category_dropdown("cat_map[".geoString::toDB($subcat)."]", $selected, 0, 0, 'Do Not Use', 2, $depth));
		}
		$tpl = new geoTemplate('addon','adplotter');
		$tpl->assign($tpl_vars);
		echo $tpl->fetch('admin/ajax_subs.tpl');
	}
}