<?php

//module_featured_ads_pic_2_level_4.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();
$col_name = 'featured_ad_4';

//--new
$tpl_vars['header_title'] = $page->messages[2258];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501162];
$txt_vars['module_display_business'] = $page->messages[501173];
$txt_vars['module_display_title'] = $page->messages[501184];
$txt_vars['module_display_ad_description'] = $page->messages[501195];

$txt_vars['module_display_tags'] = $page->messages[501206];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501206];
}

$txt_vars['module_display_address'] = $page->messages[501440];
$txt_vars['module_display_city'] = $page->messages[501451];
$txt_vars['module_display_location'] = $page->messages[501654];
$txt_vars['module_display_zip'] = $page->messages[501484];
$txt_vars['module_display_number_bids'] = $page->messages[501495];
$txt_vars['module_display_price'] = $page->messages[501506];
$txt_vars['module_display_entry_date'] = $page->messages[501517];
$txt_vars['module_display_time_left'] = $page->messages[501528];

$txt_vars['item_type_1'] = $page->messages[200043];
$txt_vars['item_type_2'] = $page->messages[200044];
$txt_vars['business_type_1'] = $page->messages[501539];
$txt_vars['business_type_2'] = $page->messages[501550];

$txt_vars['weeks'] = $page->messages[501561];
$txt_vars['days'] = $page->messages[501572];
$txt_vars['hours'] = $page->messages[501583];
$txt_vars['minutes'] = $page->messages[501594];
$txt_vars['seconds'] = $page->messages[501605];
$txt_vars['closed'] = $page->messages[501616];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
