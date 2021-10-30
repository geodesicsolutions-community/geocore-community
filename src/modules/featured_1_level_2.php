<?php

//featured_1_level_2.php


$tpl_vars = array();

$col_name = 'featured_ad_2';
$tpl_vars['css_prepend'] = 'featured_1_level_2_';
$tpl_vars['header_title'] = $page->messages[2222];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200070];
    $txt_vars['module_display_photo_icon'] = $page->messages[2223];
    $txt_vars['module_display_business'] = $page->messages[501091];
    $txt_vars['module_display_title'] = $page->messages[2224];
    $txt_vars['module_display_ad_description'] = $page->messages[2225];

    $txt_vars['module_display_tags'] = $page->messages[501092];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2227 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501093];
    $txt_vars['module_display_city'] = $page->messages[2248];
    $txt_vars['module_display_location'] = $page->messages[501632];
    $txt_vars['module_display_zip'] = $page->messages[2251];
    $txt_vars['module_display_number_bids'] = $page->messages[102585];
    $txt_vars['module_display_price'] = $page->messages[2226];
    $txt_vars['module_display_entry_date'] = $page->messages[2227];
    $txt_vars['module_display_time_left'] = $page->messages[102586];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200071];
$txt_vars['item_type_2'] = $page->messages[200072];
$txt_vars['business_type_1'] = $page->messages[501094];
$txt_vars['business_type_2'] = $page->messages[501095];

$txt_vars['weeks'] = $page->messages[102589];
$txt_vars['days'] = $page->messages[102590];
$txt_vars['hours'] = $page->messages[102592];
$txt_vars['minutes'] = $page->messages[102594];
$txt_vars['seconds'] = $page->messages[102595];
$txt_vars['closed'] = $page->messages[501096];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
