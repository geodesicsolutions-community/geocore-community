<?php

//featured_ads_1.php


$tpl_vars = array();

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_1_';
$tpl_vars['header_title'] = $page->messages[670];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200049];
    $txt_vars['module_display_photo_icon'] = $page->messages[664];
    $txt_vars['module_display_business'] = $page->messages[501049];
    $txt_vars['module_display_title'] = $page->messages[665];
    $txt_vars['module_display_ad_description'] = $page->messages[666];

    $txt_vars['module_display_tags'] = $page->messages[501050];

    for ($index = 1; $index <= 10; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[978 + $index];
    }
    for ($index = 11; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1736 - 11 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501051];
    $txt_vars['module_display_city'] = $page->messages[1370];
    $txt_vars['module_display_location'] = $page->messages[501640];
    $txt_vars['module_display_zip'] = $page->messages[1373];
    $txt_vars['module_display_number_bids'] = $page->messages[102569];
    $txt_vars['module_display_price'] = $page->messages[667];
    $txt_vars['module_display_entry_date'] = $page->messages[668];
    $txt_vars['module_display_time_left'] = $page->messages[102570];
}
$txt_vars['item_type_1'] = $page->messages[200050];
$txt_vars['item_type_2'] = $page->messages[200051];
$txt_vars['business_type_1'] = $page->messages[501052];
$txt_vars['business_type_2'] = $page->messages[501053];

$txt_vars['weeks'] = $page->messages[102573];
$txt_vars['days'] = $page->messages[102574];
$txt_vars['hours'] = $page->messages[102575];
$txt_vars['minutes'] = $page->messages[102576];
$txt_vars['seconds'] = $page->messages[102577];
$txt_vars['closed'] = $page->messages[501054];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
