<?php

//featured_ads_4.php


$tpl_vars = array();

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_4_';
$tpl_vars['header_title'] = $page->messages[699];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200058];
    $txt_vars['module_display_photo_icon'] = $page->messages[694];
    $txt_vars['module_display_business'] = $page->messages[501067];
    $txt_vars['module_display_title'] = $page->messages[695];
    $txt_vars['module_display_ad_description'] = $page->messages[696];

    $txt_vars['module_display_tags'] = $page->messages[501068];

    for ($index = 1; $index <= 10; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1008 + $index];
    }
    for ($index = 11; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1766 - 11 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501069];
    $txt_vars['module_display_city'] = $page->messages[1382];
    $txt_vars['module_display_location'] = $page->messages[501643];
    $txt_vars['module_display_zip'] = $page->messages[1385];
    $txt_vars['module_display_number_bids'] = $page->messages[102571];
    $txt_vars['module_display_price'] = $page->messages[697];
    $txt_vars['module_display_entry_date'] = $page->messages[698];
    $txt_vars['module_display_time_left'] = $page->messages[102572];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200059];
$txt_vars['item_type_2'] = $page->messages[200060];
$txt_vars['business_type_1'] = $page->messages[501070];
$txt_vars['business_type_2'] = $page->messages[501071];

$txt_vars['weeks'] = $page->messages[102578];
$txt_vars['days'] = $page->messages[102579];
$txt_vars['hours'] = $page->messages[102580];
$txt_vars['minutes'] = $page->messages[102581];
$txt_vars['seconds'] = $page->messages[102582];
$txt_vars['closed'] = $page->messages[501072];

$txt_vars['empty_category'] = $page->messages[687];

require MODULES_DIR . 'shared/browsing.php';
