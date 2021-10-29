<?php
//newest_ads_2.php
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
$order_by = '';
if ($show_module['alt_order_by']){
	$order_by = geoTables::classifieds_table.".`ends` ASC";
}else{
	$order_by = geoTables::classifieds_table.".`date` DESC";
}
$tpl_vars['css_prepend'] = 'newest_2_';
//"newest" modules use a different naming scheme for optional field header CSS
$tpl_vars['css_alternate_optional_class'] = true;
$tpl_vars['header_title'] = $page->messages[1073];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
	$txt_vars['module_display_listing_column'] = $page->messages[200100];
	$txt_vars['module_display_photo_icon'] = $page->messages[1072];
	$txt_vars['module_display_business'] = $page->messages[501145];
	$txt_vars['module_display_title'] = $page->messages[1074];
	$txt_vars['module_display_ad_description'] = $page->messages[1075];
	
	$txt_vars['module_display_tags'] = $page->messages[501146];
	
	for ($index = 1; $index <= 20; $index ++){
		$key = ($index <= 10) ? (1038+$index) : (1785+$index);
		$txt_vars['module_display_optional_field_'.$index] = $page->messages[$key];
	}
	
	$txt_vars['module_display_address'] = $page->messages[501147];
	$txt_vars['module_display_city'] = $page->messages[1394];
	$txt_vars['module_display_location'] = $page->messages[501660];
	$txt_vars['module_display_zip'] = $page->messages[1397];
	$txt_vars['module_display_number_bids'] = $page->messages[102653];
	$txt_vars['module_display_price'] = $page->messages[1076];
	$txt_vars['module_display_entry_date'] = $page->messages[1077];
	$txt_vars['module_display_time_left'] = $page->messages[102655];
}
$txt_vars['item_type_1'] = $page->messages[200101];
$txt_vars['item_type_2'] = $page->messages[200102];
$txt_vars['business_type_1'] = $page->messages[501148];
$txt_vars['business_type_2'] = $page->messages[501149];

$txt_vars['weeks'] = $page->messages[102657];
$txt_vars['days'] = $page->messages[102658];
$txt_vars['hours'] = $page->messages[102660];
$txt_vars['minutes'] = $page->messages[102662];
$txt_vars['seconds'] = $page->messages[102664];
$txt_vars['closed'] = $page->messages[501150];

$txt_vars['empty_category'] = $page->messages[500953];

if (geoPC::is_ent()) require MODULES_DIR . 'shared/browsing.php';
