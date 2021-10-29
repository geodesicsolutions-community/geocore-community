<?php

//featured_1_level_3.php
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
$tpl_vars['css_prepend'] = 'featured_1_level_3_';
$tpl_vars['header_title'] = $page->messages[2042];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200076];
    $txt_vars['module_display_photo_icon'] = $page->messages[2043];
    $txt_vars['module_display_business'] = $page->messages[501103];
    $txt_vars['module_display_title'] = $page->messages[2044];
    $txt_vars['module_display_ad_description'] = $page->messages[2045];

    $txt_vars['module_display_tags'] = $page->messages[501104];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2047 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501105];
    $txt_vars['module_display_city'] = $page->messages[2068];
    $txt_vars['module_display_location'] = $page->messages[501633];
    $txt_vars['module_display_zip'] = $page->messages[2071];
    $txt_vars['module_display_number_bids'] = $page->messages[102598];
    $txt_vars['module_display_price'] = $page->messages[2046];
    $txt_vars['module_display_entry_date'] = $page->messages[2047];
    $txt_vars['module_display_time_left'] = $page->messages[102600];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200077];
$txt_vars['item_type_2'] = $page->messages[200078];
$txt_vars['business_type_1'] = $page->messages[501106];
$txt_vars['business_type_2'] = $page->messages[501107];

$txt_vars['weeks'] = $page->messages[102601];
$txt_vars['days'] = $page->messages[102602];
$txt_vars['hours'] = $page->messages[102603];
$txt_vars['minutes'] = $page->messages[102604];
$txt_vars['seconds'] = $page->messages[102605];
$txt_vars['closed'] = $page->messages[501108];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
