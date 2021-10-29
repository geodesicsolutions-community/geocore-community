<?php

//newest_ads_1.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();

$col_name = '';
$order_by = '';
if ($show_module['alt_order_by']) {
    $order_by = geoTables::classifieds_table . ".`ends` ASC";
} else {
    $order_by = geoTables::classifieds_table . ".`date` DESC";
}
$tpl_vars['css_prepend'] = 'newest_1_';
//"newest" modules use a different naming scheme for optional field header CSS
$tpl_vars['css_alternate_optional_class'] = true;
$tpl_vars['header_title'] = $page->messages[1066];
$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200097];
    $txt_vars['module_display_photo_icon'] = $page->messages[1065];
    $txt_vars['module_display_business'] = $page->messages[501139];
    $txt_vars['module_display_title'] = $page->messages[1067];
    $txt_vars['module_display_ad_description'] = $page->messages[1068];

    $txt_vars['module_display_tags'] = $page->messages[501140];

    for ($index = 1; $index <= 20; $index++) {
        $key = ($index <= 10) ? (1028 + $index) : (1775 + $index);
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[$key];
    }

    $txt_vars['module_display_address'] = $page->messages[501141];
    $txt_vars['module_display_city'] = $page->messages[1390];
    $txt_vars['module_display_location'] = $page->messages[501659];
    $txt_vars['module_display_zip'] = $page->messages[1393];
    $txt_vars['module_display_number_bids'] = $page->messages[102639];
    $txt_vars['module_display_price'] = $page->messages[1069];
    $txt_vars['module_display_entry_date'] = $page->messages[1070];
    $txt_vars['module_display_time_left'] = $page->messages[102640];
}
$txt_vars['item_type_1'] = $page->messages[200098];
$txt_vars['item_type_2'] = $page->messages[200099];
$txt_vars['business_type_1'] = $page->messages[501142];
$txt_vars['business_type_2'] = $page->messages[501143];

$txt_vars['weeks'] = $page->messages[102643];
$txt_vars['days'] = $page->messages[102644];
$txt_vars['hours'] = $page->messages[102645];
$txt_vars['minutes'] = $page->messages[102646];
$txt_vars['seconds'] = $page->messages[102648];
$txt_vars['closed'] = $page->messages[501144];

$txt_vars['empty_category'] = $page->messages[1071];

require MODULES_DIR . 'shared/browsing.php';
