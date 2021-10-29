<?php 
//module_featured_ads_pic_1_level_2.php
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
$col_name = 'featured_ad_2';

//--new
$tpl_vars['header_title'] = $page->messages[2252];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501157];
$txt_vars['module_display_business'] = $page->messages[501168];
$txt_vars['module_display_title'] = $page->messages[501179];
$txt_vars['module_display_ad_description'] = $page->messages[501190];

$txt_vars['module_display_tags'] = $page->messages[501201];

for ($index = 1; $index <= 20; $index ++){
	$txt_vars['module_display_optional_field_'.$index] = $page->messages[(11*$index)+501201];
}

$txt_vars['module_display_address'] = $page->messages[501435];
$txt_vars['module_display_city'] = $page->messages[501446];
$txt_vars['module_display_location'] = $page->messages[501647];
$txt_vars['module_display_zip'] = $page->messages[501479];
$txt_vars['module_display_number_bids'] = $page->messages[501490];
$txt_vars['module_display_price'] = $page->messages[501501];
$txt_vars['module_display_entry_date'] = $page->messages[501512];
$txt_vars['module_display_time_left'] = $page->messages[501523];

$txt_vars['item_type_1'] = $page->messages[200033];
$txt_vars['item_type_2'] = $page->messages[200034];
$txt_vars['business_type_1'] = $page->messages[501534];
$txt_vars['business_type_2'] = $page->messages[501545];

$txt_vars['weeks'] = $page->messages[501556];
$txt_vars['days'] = $page->messages[501567];
$txt_vars['hours'] = $page->messages[501578];
$txt_vars['minutes'] = $page->messages[501589];
$txt_vars['seconds'] = $page->messages[501600];
$txt_vars['closed'] = $page->messages[501611];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) require MODULES_DIR . 'shared/browsing_pic.php';
