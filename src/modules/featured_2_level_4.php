<?php

//featured_2_level_4.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();

$col_name = 'featured_ad_4';
$tpl_vars['css_prepend'] = 'featured_2_level_4_';
$tpl_vars['header_title'] = $page->messages[2132];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200085];
    $txt_vars['module_display_photo_icon'] = $page->messages[2133];
    $txt_vars['module_display_business'] = $page->messages[501121];
    $txt_vars['module_display_title'] = $page->messages[2134];
    $txt_vars['module_display_ad_description'] = $page->messages[2135];

    $txt_vars['module_display_tags'] = $page->messages[501122];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2137 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501123];
    $txt_vars['module_display_city'] = $page->messages[2158];
    $txt_vars['module_display_location'] = $page->messages[501638];
    $txt_vars['module_display_zip'] = $page->messages[2161];
    $txt_vars['module_display_number_bids'] = $page->messages[102654];
    $txt_vars['module_display_price'] = $page->messages[2136];
    $txt_vars['module_display_entry_date'] = $page->messages[2137];
    $txt_vars['module_display_time_left'] = $page->messages[102656];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200086];
$txt_vars['item_type_2'] = $page->messages[200087];
$txt_vars['business_type_1'] = $page->messages[501124];
$txt_vars['business_type_2'] = $page->messages[501125];

$txt_vars['weeks'] = $page->messages[102659];
$txt_vars['days'] = $page->messages[102661];
$txt_vars['hours'] = $page->messages[102663];
$txt_vars['minutes'] = $page->messages[102665];
$txt_vars['seconds'] = $page->messages[102666];
$txt_vars['closed'] = $page->messages[501126];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
