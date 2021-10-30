<?php

//featured_ads_5.php


$tpl_vars = array();

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_5_';
$tpl_vars['header_title'] = $page->messages[705];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200061];
    $txt_vars['module_display_photo_icon'] = $page->messages[700];
    $txt_vars['module_display_business'] = $page->messages[501073];
    $txt_vars['module_display_title'] = $page->messages[701];
    $txt_vars['module_display_ad_description'] = $page->messages[702];

    $txt_vars['module_display_tags'] = $page->messages[501074];

    for ($index = 1; $index <= 10; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1018 + $index];
    }
    for ($index = 11; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1776 - 11 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501075];
    $txt_vars['module_display_city'] = $page->messages[1386];
    $txt_vars['module_display_location'] = $page->messages[501644];
    $txt_vars['module_display_zip'] = $page->messages[1389];
    $txt_vars['module_display_number_bids'] = $page->messages[102583];
    $txt_vars['module_display_price'] = $page->messages[703];
    $txt_vars['module_display_entry_date'] = $page->messages[704];
    $txt_vars['module_display_time_left'] = $page->messages[102584];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200062];
$txt_vars['item_type_2'] = $page->messages[200063];
$txt_vars['business_type_1'] = $page->messages[501076];
$txt_vars['business_type_2'] = $page->messages[501077];

$txt_vars['weeks'] = $page->messages[102587];
$txt_vars['days'] = $page->messages[102588];
$txt_vars['hours'] = $page->messages[102591];
$txt_vars['minutes'] = $page->messages[102593];
$txt_vars['seconds'] = $page->messages[102596];
$txt_vars['closed'] = $page->messages[501078];

$txt_vars['empty_category'] = $page->messages[687];

require MODULES_DIR . 'shared/browsing.php';
