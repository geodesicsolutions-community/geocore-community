<?php

//featured_1_level_5.php


$tpl_vars = array();

$col_name = 'featured_ad_5';
$tpl_vars['css_prepend'] = 'featured_1_level_5_';
$tpl_vars['header_title'] = $page->messages[2162];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200088];
    $txt_vars['module_display_photo_icon'] = $page->messages[2163];
    $txt_vars['module_display_business'] = $page->messages[501127];
    $txt_vars['module_display_title'] = $page->messages[2164];
    $txt_vars['module_display_ad_description'] = $page->messages[2165];

    $txt_vars['module_display_tags'] = $page->messages[501128];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2167 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501129];
    $txt_vars['module_display_city'] = $page->messages[2188];
    $txt_vars['module_display_location'] = $page->messages[501635];
    $txt_vars['module_display_zip'] = $page->messages[2191];
    $txt_vars['module_display_number_bids'] = $page->messages[102625];
    $txt_vars['module_display_price'] = $page->messages[2166];
    $txt_vars['module_display_entry_date'] = $page->messages[2167];
    $txt_vars['module_display_time_left'] = $page->messages[102626];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200089];
$txt_vars['item_type_2'] = $page->messages[200090];
$txt_vars['business_type_1'] = $page->messages[501130];
$txt_vars['business_type_2'] = $page->messages[501131];

$txt_vars['weeks'] = $page->messages[102627];
$txt_vars['days'] = $page->messages[102628];
$txt_vars['hours'] = $page->messages[102629];
$txt_vars['minutes'] = $page->messages[102630];
$txt_vars['seconds'] = $page->messages[102631];
$txt_vars['closed'] = $page->messages[501132];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
