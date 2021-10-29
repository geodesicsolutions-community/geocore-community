<?php

//module_featured_ads_pic_1.php
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
$col_name = 'featured_ad';

//--new
$tpl_vars['header_title'] = $page->messages[1471];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501154];
$txt_vars['module_display_business'] = $page->messages[501165];
$txt_vars['module_display_title'] = $page->messages[501176];
$txt_vars['module_display_ad_description'] = $page->messages[501187];

$txt_vars['module_display_tags'] = $page->messages[501198];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501198];
}

$txt_vars['module_display_address'] = $page->messages[501432];
$txt_vars['module_display_city'] = $page->messages[501443];
$txt_vars['module_display_location'] = $page->messages[501651];
$txt_vars['module_display_zip'] = $page->messages[501476];
$txt_vars['module_display_number_bids'] = $page->messages[501487];
$txt_vars['module_display_price'] = $page->messages[501498];
$txt_vars['module_display_entry_date'] = $page->messages[501509];
$txt_vars['module_display_time_left'] = $page->messages[501520];

$txt_vars['item_type_1'] = $page->messages[200027];
$txt_vars['item_type_2'] = $page->messages[200028];
$txt_vars['business_type_1'] = $page->messages[501531];
$txt_vars['business_type_2'] = $page->messages[501542];

$txt_vars['weeks'] = $page->messages[501553];
$txt_vars['days'] = $page->messages[501564];
$txt_vars['hours'] = $page->messages[501575];
$txt_vars['minutes'] = $page->messages[501586];
$txt_vars['seconds'] = $page->messages[501597];
$txt_vars['closed'] = $page->messages[501608];

$txt_vars['empty_category'] = $page->messages[200191];

require MODULES_DIR . 'shared/browsing_pic.php';
