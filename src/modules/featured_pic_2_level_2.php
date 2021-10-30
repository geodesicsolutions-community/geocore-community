<?php

//module_featured_ads_pic_2_level_2.php


$tpl_vars = array();
$col_name = 'featured_ad_2';

//--new
$tpl_vars['header_title'] = $page->messages[2256];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501158];
$txt_vars['module_display_business'] = $page->messages[501169];
$txt_vars['module_display_title'] = $page->messages[501180];
$txt_vars['module_display_ad_description'] = $page->messages[501191];

$txt_vars['module_display_tags'] = $page->messages[501202];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501202];
}

$txt_vars['module_display_address'] = $page->messages[501436];
$txt_vars['module_display_city'] = $page->messages[501447];
$txt_vars['module_display_location'] = $page->messages[501652];
$txt_vars['module_display_zip'] = $page->messages[501480];
$txt_vars['module_display_number_bids'] = $page->messages[501491];
$txt_vars['module_display_price'] = $page->messages[501502];
$txt_vars['module_display_entry_date'] = $page->messages[501513];
$txt_vars['module_display_time_left'] = $page->messages[501524];

$txt_vars['item_type_1'] = $page->messages[200035];
$txt_vars['item_type_2'] = $page->messages[200036];
$txt_vars['business_type_1'] = $page->messages[501535];
$txt_vars['business_type_2'] = $page->messages[501546];

$txt_vars['weeks'] = $page->messages[501557];
$txt_vars['days'] = $page->messages[501568];
$txt_vars['hours'] = $page->messages[501579];
$txt_vars['minutes'] = $page->messages[501590];
$txt_vars['seconds'] = $page->messages[501601];
$txt_vars['closed'] = $page->messages[501612];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
