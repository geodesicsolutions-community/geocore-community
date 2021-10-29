<?php 
//module_hottest_ads.php
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
## ##    6.0.7-304-g6ae40c9
## 
##################################


$tpl_vars = array();

$col_name = '';
$order_by = geoTables::classifieds_table.'.`viewed` DESC';

$tpl_vars['css_prepend'] = 'hottest_1_';
$tpl_vars['header_title'] = $page->messages[2466];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
	$txt_vars['module_display_listing_column'] = $page->messages[200094];
	$txt_vars['module_display_photo_icon'] = $page->messages[2467];
	$txt_vars['module_display_business'] = $page->messages[501043];
	$txt_vars['module_display_title'] = $page->messages[2468];
	$txt_vars['module_display_ad_description'] = $page->messages[2469];
	
	$txt_vars['module_display_tags'] = $page->messages[501044];
	
	for ($index = 1; $index <= 20; $index ++){
		$txt_vars['module_display_optional_field_'.$index] = $page->messages[2469+$index];
	}
	
	$txt_vars['module_display_address'] = $page->messages[501045];
	$txt_vars['module_display_city'] = $page->messages[2490];
	$txt_vars['module_display_location'] = $page->messages[501658];
	$txt_vars['module_display_zip'] = $page->messages[2493];
	$txt_vars['module_display_number_bids'] = $page->messages[103037];
	$txt_vars['module_display_price'] = $page->messages[2494];
	$txt_vars['module_display_entry_date'] = $page->messages[2495];
	$txt_vars['module_display_time_left'] = $page->messages[103039];
}
$txt_vars['item_type_1'] = $page->messages[200095];
$txt_vars['item_type_2'] = $page->messages[200096];
$txt_vars['business_type_1'] = $page->messages[501046];
$txt_vars['business_type_2'] = $page->messages[501047];

$txt_vars['weeks'] = $page->messages[103048];
$txt_vars['days'] = $page->messages[103049];
$txt_vars['hours'] = $page->messages[103050];
$txt_vars['minutes'] = $page->messages[103047];
$txt_vars['seconds'] = $page->messages[103046];
$txt_vars['closed'] = $page->messages[501048];

$txt_vars['empty_category'] = $page->messages[500073];

//set up the query
require MODULES_DIR . 'shared/browsing.php';
