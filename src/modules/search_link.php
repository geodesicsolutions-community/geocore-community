<?php 
//module_display_search_link.php	
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
## ##    6.0.7-2-gc953682
## 
##################################

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
	//browsing disabled, do not show module contents
	return;
}
//un-comment next line for testing category link
//$page->site_category = 12;
if ($page->site_category) {	
	$category_name = geoCategory::getName($page->site_category);
	$tpl_vars = array (
		'href' => $page->configuration_data['classifieds_file_name']."?a=19&amp;c=".$page->site_category,
		'class' => 'search_link',
		'label' => $page->messages[1470].geoString::fromDB(($category_name->CATEGORY_NAME))
	);
} else {
	$tpl_vars = array (
		'href' => $page->configuration_data['classifieds_file_name']."?a=19",
		'class' => 'search_link',
		'label' => $page->messages[1470].$page->messages[1469]
	);
}

$view->setModuleTpl($show_module['module_replace_tag'],'index')
	->setModuleVar($show_module['module_replace_tag'],$tpl_vars);
