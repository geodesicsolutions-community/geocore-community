<?php

//featured_ads_2.php
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

$tpl_vars['ignoreCategory'] = true; //special case for FEATURED_ADS_2

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_2_';
$tpl_vars['header_title'] = $page->messages[686];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200052];
    $txt_vars['module_display_photo_icon'] = $page->messages[681];
    $txt_vars['module_display_business'] = $page->messages[501055];
    $txt_vars['module_display_title'] = $page->messages[682];
    $txt_vars['module_display_ad_description'] = $page->messages[683];

    $txt_vars['module_display_tags'] = $page->messages[501056];

    for ($index = 1; $index <= 10; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[988 + $index];
    }
    for ($index = 11; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1746 - 11 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501057];
    $txt_vars['module_display_city'] = $page->messages[1374];
    $txt_vars['module_display_location'] = $page->messages[501641];
    $txt_vars['module_display_zip'] = $page->messages[1377];
    $txt_vars['module_display_number_bids'] = $page->messages[102553];
    $txt_vars['module_display_price'] = $page->messages[684];
    $txt_vars['module_display_entry_date'] = $page->messages[685];
    $txt_vars['module_display_time_left'] = $page->messages[102554];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200053];
$txt_vars['item_type_2'] = $page->messages[200054];
$txt_vars['business_type_1'] = $page->messages[501058];
$txt_vars['business_type_2'] = $page->messages[501059];

$txt_vars['weeks'] = $page->messages[102556];
$txt_vars['days'] = $page->messages[102555];
$txt_vars['hours'] = $page->messages[102557];
$txt_vars['minutes'] = $page->messages[102558];
$txt_vars['seconds'] = $page->messages[102559];
$txt_vars['closed'] = $page->messages[501060];

$txt_vars['empty_category'] = $page->messages[687];

require MODULES_DIR . 'shared/browsing.php';
