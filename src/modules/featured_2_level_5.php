<?php 
//featured_2_level_5.php
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

$col_name = 'featured_ad_5';
$tpl_vars['css_prepend'] = 'featured_2_level_5_';
$tpl_vars['header_title'] = $page->messages[2345];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
	$txt_vars['module_display_listing_column'] = $page->messages[200094];
	$txt_vars['module_display_photo_icon'] = $page->messages[2315];
	$txt_vars['module_display_business'] = $page->messages[501133];
	$txt_vars['module_display_title'] = $page->messages[2314];
	$txt_vars['module_display_ad_description'] = $page->messages[2316];
	
	$txt_vars['module_display_tags'] = $page->messages[501134];
	
	for ($index = 1; $index <= 20; $index ++){
		$txt_vars['module_display_optional_field_'.$index] = $page->messages[2316+$index];
	}
	
	$txt_vars['module_display_address'] = $page->messages[501135];
	$txt_vars['module_display_city'] = $page->messages[2337];
	$txt_vars['module_display_location'] = $page->messages[501639];
	$txt_vars['module_display_zip'] = $page->messages[2340];
	$txt_vars['module_display_number_bids'] = $page->messages[102667];
	$txt_vars['module_display_price'] = $page->messages[2341];
	$txt_vars['module_display_entry_date'] = $page->messages[2342];
	$txt_vars['module_display_time_left'] = $page->messages[102668];
} if (!geoPC::is_ent()) { return; }
$txt_vars['item_type_1'] = $page->messages[200095];
$txt_vars['item_type_2'] = $page->messages[200096];
$txt_vars['business_type_1'] = $page->messages[501136];
$txt_vars['business_type_2'] = $page->messages[501137];

$txt_vars['weeks'] = $page->messages[102669];
$txt_vars['days'] = $page->messages[102670];
$txt_vars['hours'] = $page->messages[102671];
$txt_vars['minutes'] = $page->messages[102672];
$txt_vars['seconds'] = $page->messages[102673];
$txt_vars['closed'] = $page->messages[501138];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
