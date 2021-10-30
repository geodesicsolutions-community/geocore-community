<?php

//featured_category_1.php


$tpl_vars = array();

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_category_1_';
$tpl_vars['header_title'] = $page->messages[2379];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200064];
    $txt_vars['module_display_photo_icon'] = $page->messages[2351];
    $txt_vars['module_display_business'] = $page->messages[501079];
    $txt_vars['module_display_title'] = $page->messages[2360];
    $txt_vars['module_display_ad_description'] = $page->messages[2352];

    $txt_vars['module_display_tags'] = $page->messages[501080];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2352 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501081];
    $txt_vars['module_display_city'] = $page->messages[2373];
    $txt_vars['module_display_location'] = $page->messages[501645];
    $txt_vars['module_display_zip'] = $page->messages[2376];
    $txt_vars['module_display_number_bids'] = $page->messages[102597];
    $txt_vars['module_display_price'] = $page->messages[2377];
    $txt_vars['module_display_entry_date'] = $page->messages[2378];
    $txt_vars['module_display_time_left'] = $page->messages[102599];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200065];
$txt_vars['item_type_2'] = $page->messages[200066];
$txt_vars['business_type_1'] = $page->messages[501082];
$txt_vars['business_type_2'] = $page->messages[501083];

$txt_vars['weeks'] = $page->messages[102606];
$txt_vars['days'] = $page->messages[102607];
$txt_vars['hours'] = $page->messages[102608];
$txt_vars['minutes'] = $page->messages[102609];
$txt_vars['seconds'] = $page->messages[102610];
$txt_vars['closed'] = $page->messages[501084];

$txt_vars['empty_category'] = $page->messages[687];

//special for featured category:
$tpl_vars['is_featured_category'] = 1;

require MODULES_DIR . 'shared/browsing.php';
