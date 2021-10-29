<?php

//featured_category_2.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_category_2_';
$tpl_vars['header_title'] = $page->messages[2409];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200067];
    $txt_vars['module_display_photo_icon'] = $page->messages[2381];
    $txt_vars['module_display_business'] = $page->messages[501085];
    $txt_vars['module_display_title'] = $page->messages[2380];
    $txt_vars['module_display_ad_description'] = $page->messages[2382];

    $txt_vars['module_display_tags'] = $page->messages[501086];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2382 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501087];
    $txt_vars['module_display_city'] = $page->messages[2403];
    $txt_vars['module_display_location'] = $page->messages[501646];
    $txt_vars['module_display_zip'] = $page->messages[2406];
    $txt_vars['module_display_number_bids'] = $page->messages[102611];
    $txt_vars['module_display_price'] = $page->messages[2407];
    $txt_vars['module_display_entry_date'] = $page->messages[2408];
    $txt_vars['module_display_time_left'] = $page->messages[102612];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200068];
$txt_vars['item_type_2'] = $page->messages[200069];
$txt_vars['business_type_1'] = $page->messages[501088];
$txt_vars['business_type_2'] = $page->messages[501089];

$txt_vars['weeks'] = $page->messages[102619];
$txt_vars['days'] = $page->messages[102621];
$txt_vars['hours'] = $page->messages[102622];
$txt_vars['minutes'] = $page->messages[102623];
$txt_vars['seconds'] = $page->messages[102624];
$txt_vars['closed'] = $page->messages[501090];

$txt_vars['empty_category'] = $page->messages[687];

//special for featured category:
$tpl_vars['is_featured_category'] = 1;

require MODULES_DIR . 'shared/browsing.php';
