<?php
//module_featured_ads_pic_2_level_3.php
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
$col_name = 'featured_ad_3';

//--new
$tpl_vars['header_title'] = $page->messages[2257];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501160];
$txt_vars['module_display_business'] = $page->messages[501171];
$txt_vars['module_display_title'] = $page->messages[501182];
$txt_vars['module_display_ad_description'] = $page->messages[501193];

$txt_vars['module_display_tags'] = $page->messages[501204];

for ($index = 1; $index <= 20; $index ++){
	$txt_vars['module_display_optional_field_'.$index] = $page->messages[(11*$index)+501204];
}

$txt_vars['module_display_address'] = $page->messages[501438];
$txt_vars['module_display_city'] = $page->messages[501449];
$txt_vars['module_display_location'] = $page->messages[501653];
$txt_vars['module_display_zip'] = $page->messages[501482];
$txt_vars['module_display_number_bids'] = $page->messages[501493];
$txt_vars['module_display_price'] = $page->messages[501504];
$txt_vars['module_display_entry_date'] = $page->messages[501515];
$txt_vars['module_display_time_left'] = $page->messages[501526];

$txt_vars['item_type_1'] = $page->messages[200039];
$txt_vars['item_type_2'] = $page->messages[200040];
$txt_vars['business_type_1'] = $page->messages[501537];
$txt_vars['business_type_2'] = $page->messages[501548];

$txt_vars['weeks'] = $page->messages[501559];
$txt_vars['days'] = $page->messages[501570];
$txt_vars['hours'] = $page->messages[501581];
$txt_vars['minutes'] = $page->messages[501592];
$txt_vars['seconds'] = $page->messages[501603];
$txt_vars['closed'] = $page->messages[501614];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) require MODULES_DIR . 'shared/browsing_pic.php';