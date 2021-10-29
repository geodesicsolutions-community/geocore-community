<?php

//featured_2_level_2.php
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
$tpl_vars['css_prepend'] = 'featured_2_level_2_';
$tpl_vars['header_title'] = $page->messages[2013];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200073];
    $txt_vars['module_display_photo_icon'] = $page->messages[2014];
    $txt_vars['module_display_business'] = $page->messages[501097];
    $txt_vars['module_display_title'] = $page->messages[2015];
    $txt_vars['module_display_ad_description'] = $page->messages[2016];

    $txt_vars['module_display_tags'] = $page->messages[501098];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2018 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501099];
    $txt_vars['module_display_city'] = $page->messages[2038];
    $txt_vars['module_display_location'] = $page->messages[501636];
    $txt_vars['module_display_zip'] = $page->messages[2041];
    $txt_vars['module_display_number_bids'] = $page->messages[102632];
    $txt_vars['module_display_price'] = $page->messages[2017];
    $txt_vars['module_display_entry_date'] = $page->messages[2018];
    $txt_vars['module_display_time_left'] = $page->messages[102633];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200074];
$txt_vars['item_type_2'] = $page->messages[200075];
$txt_vars['business_type_1'] = $page->messages[501100];
$txt_vars['business_type_2'] = $page->messages[501101];

$txt_vars['weeks'] = $page->messages[102634];
$txt_vars['days'] = $page->messages[102635];
$txt_vars['hours'] = $page->messages[102636];
$txt_vars['minutes'] = $page->messages[102637];
$txt_vars['seconds'] = $page->messages[102638];
$txt_vars['closed'] = $page->messages[501102];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
