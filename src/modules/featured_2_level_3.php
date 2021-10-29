<?php

//featured_2_level_3.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();

$col_name = 'featured_ad_3';
$tpl_vars['css_prepend'] = 'featured_2_level_3_';
$tpl_vars['header_title'] = $page->messages[2072];

$txt_vars = array();
if ($show_module['module_display_header_row']) {
    $txt_vars['module_display_listing_column'] = $page->messages[200079];
    $txt_vars['module_display_photo_icon'] = $page->messages[2073];
    $txt_vars['module_display_business'] = $page->messages[501109];
    $txt_vars['module_display_title'] = $page->messages[2074];
    $txt_vars['module_display_ad_description'] = $page->messages[2075];

    $txt_vars['module_display_tags'] = $page->messages[501110];

    for ($index = 1; $index <= 20; $index++) {
        $txt_vars['module_display_optional_field_' . $index] = $page->messages[2077 + $index];
    }

    $txt_vars['module_display_address'] = $page->messages[501111];
    $txt_vars['module_display_city'] = $page->messages[2098];
    $txt_vars['module_display_location'] = $page->messages[501637];
    $txt_vars['module_display_zip'] = $page->messages[2101];
    $txt_vars['module_display_number_bids'] = $page->messages[102641];
    $txt_vars['module_display_price'] = $page->messages[2076];
    $txt_vars['module_display_entry_date'] = $page->messages[2077];
    $txt_vars['module_display_time_left'] = $page->messages[102642];
} if (!geoPC::is_ent()) {
    return;
}
$txt_vars['item_type_1'] = $page->messages[200080];
$txt_vars['item_type_2'] = $page->messages[200081];
$txt_vars['business_type_1'] = $page->messages[501112];
$txt_vars['business_type_2'] = $page->messages[501113];

$txt_vars['weeks'] = $page->messages[102647];
$txt_vars['days'] = $page->messages[102649];
$txt_vars['hours'] = $page->messages[102650];
$txt_vars['minutes'] = $page->messages[102651];
$txt_vars['seconds'] = $page->messages[102652];
$txt_vars['closed'] = $page->messages[501114];

$txt_vars['empty_category'] = $page->messages[17];

require MODULES_DIR . 'shared/browsing.php';
