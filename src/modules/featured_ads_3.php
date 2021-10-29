<?php

//featured_ads_3.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();

$col_name = 'featured_ad';
$tpl_vars['css_prepend'] = 'featured_3_';
$tpl_vars['header_title'] = $page->messages[693];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200055];
    $txt_vars['module_display_photo_icon'] = $page->messages[688];
    $txt_vars['module_display_business'] = $page->messages[501061];
    $txt_vars['module_display_title'] = $page->messages[689];
    $txt_vars['module_display_ad_description'] = $page->messages[690];

    $txt_vars['module_display_tags'] = $page->messages[501062];

    for ($index = 1; $index <= 10; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[998 + $index];
    }
    for ($index = 11; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[1756 - 11 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501063];
    $txt_vars['module_display_city'] = $page->messages[1378];
    $txt_vars['module_display_location'] = $page->messages[501642];
    $txt_vars['module_display_zip'] = $page->messages[1381];
    $txt_vars['module_display_number_bids'] = $page->messages[102562];
    $txt_vars['module_display_price'] = $page->messages[691];
    $txt_vars['module_display_entry_date'] = $page->messages[692];
    $txt_vars['module_display_time_left'] = $page->messages[102563];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200056];
$txt_vars['item_type_2'] = $page->messages[200057];
$txt_vars['business_type_1'] = $page->messages[501064];
$txt_vars['business_type_2'] = $page->messages[501065];

$txt_vars['weeks'] = $page->messages[102564];
$txt_vars['days'] = $page->messages[102565];
$txt_vars['hours'] = $page->messages[102566];
$txt_vars['minutes'] = $page->messages[102567];
$txt_vars['seconds'] = $page->messages[102568];
$txt_vars['closed'] = $page->messages[501066];

$txt_vars['empty_category'] = $page->messages[687];

require MODULES_DIR . 'shared/browsing.php';
