<?php

//module_featured_ads_pic_1_level_4.php
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
## ##    7.5.3-36-gea36ae7
##
##################################

$tpl_vars = array();
$col_name = 'featured_ad_4';

//--new
$tpl_vars['header_title'] = $page->messages[2254];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501161];
$txt_vars['module_display_business'] = $page->messages[501172];
$txt_vars['module_display_title'] = $page->messages[501183];
$txt_vars['module_display_ad_description'] = $page->messages[501194];

$txt_vars['module_display_tags'] = $page->messages[501205];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501205];
}

$txt_vars['module_display_address'] = $page->messages[501439];
$txt_vars['module_display_city'] = $page->messages[501450];
$txt_vars['module_display_location'] = $page->messages[501649];
$txt_vars['module_display_number_bids'] = $page->messages[501494];
$txt_vars['module_display_price'] = $page->messages[501505];
$txt_vars['module_display_entry_date'] = $page->messages[501516];
$txt_vars['module_display_time_left'] = $page->messages[501527];

$txt_vars['item_type_1'] = $page->messages[200041];
$txt_vars['item_type_2'] = $page->messages[200042];
$txt_vars['business_type_1'] = $page->messages[501538];
$txt_vars['business_type_2'] = $page->messages[501549];

$txt_vars['weeks'] = $page->messages[501560];
$txt_vars['days'] = $page->messages[501571];
$txt_vars['hours'] = $page->messages[501582];
$txt_vars['minutes'] = $page->messages[501593];
$txt_vars['seconds'] = $page->messages[501604];
$txt_vars['closed'] = $page->messages[501615];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
