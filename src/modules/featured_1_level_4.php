<?php

//featured_1_level_4.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();

$col_name = 'featured_ad_4';
$tpl_vars['css_prepend'] = 'featured_1_level_4_';
$tpl_vars['header_title'] = $page->messages[2102];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200082];
    $txt_vars['module_display_photo_icon'] = $page->messages[2103];
    $txt_vars['module_display_business'] = $page->messages[501115];
    $txt_vars['module_display_title'] = $page->messages[2104];
    $txt_vars['module_display_ad_description'] = $page->messages[2105];

    $txt_vars['module_display_tags'] = $page->messages[501116];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2107 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501117];
    $txt_vars['module_display_city'] = $page->messages[2128];
    $txt_vars['module_display_location'] = $page->messages[501634];
    $txt_vars['module_display_zip'] = $page->messages[2131];
    $txt_vars['module_display_number_bids'] = $page->messages[102613];
    $txt_vars['module_display_price'] = $page->messages[2106];
    $txt_vars['module_display_entry_date'] = $page->messages[2107];
    $txt_vars['module_display_time_left'] = $page->messages[102614];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200083];
$txt_vars['item_type_2'] = $page->messages[200084];
$txt_vars['business_type_1'] = $page->messages[501118];
$txt_vars['business_type_2'] = $page->messages[501119];

$txt_vars['weeks'] = $page->messages[102615];
$txt_vars['days'] = $page->messages[102616];
$txt_vars['hours'] = $page->messages[102617];
$txt_vars['minutes'] = $page->messages[102618];
$txt_vars['seconds'] = $page->messages[102620];
$txt_vars['closed'] = $page->messages[501120];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
