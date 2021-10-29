<?php 
//module_display_subscategory_navigation_2.php
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
## ##    7.0.2-4-gf4d2a43
## 
##################################


$tpl_vars = array();

//get the categories inside of this category

$tpl_vars['parent_category'] = (int)$show_module['module_category'];

$tpl_vars['no_subcategory_text'] = $page->messages[2417];

$tpl_vars['column_css'] = 'subcategory_navigation_2';
if (($show_module['module_display_ad_description']) && ($tpl_vars['parent_category'])) {
	//not sure wtf this is for, leaving in just in case it is used
	$parent_category_name = geoCategory::getName($tpl_vars['parent_category'], true);
	$tpl_vars['parent_category_text'] = $page->messages[2418] . $parent_category_name;
	$tpl_vars['parent_category_url'] = $this->get_site_setting('classifieds_file_name').'?a=5&amp;b='.$tpl_vars['parent_category'];
}

if (geoPC::is_ent()) require MODULES_DIR . 'shared/category_navigation.php';
