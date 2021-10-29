<?php

//module_featured_ads_pic_3.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();
$col_name = 'featured_ad';

//--new
$tpl_vars['header_title'] = $page->messages[1598];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501156];
$txt_vars['module_display_business'] = $page->messages[501167];
$txt_vars['module_display_title'] = $page->messages[501178];
$txt_vars['module_display_ad_description'] = $page->messages[501189];

$txt_vars['module_display_tags'] = $page->messages[501200];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501200];
}

$txt_vars['module_display_address'] = $page->messages[501434];
$txt_vars['module_display_city'] = $page->messages[501445];
$txt_vars['module_display_location'] = $page->messages[501657];
$txt_vars['module_display_zip'] = $page->messages[501478];
$txt_vars['module_display_number_bids'] = $page->messages[501489];
$txt_vars['module_display_price'] = $page->messages[501500];
$txt_vars['module_display_entry_date'] = $page->messages[501511];
$txt_vars['module_display_time_left'] = $page->messages[501522];

$txt_vars['item_type_1'] = $page->messages[200031];
$txt_vars['item_type_2'] = $page->messages[200032];
$txt_vars['business_type_1'] = $page->messages[501533];
$txt_vars['business_type_2'] = $page->messages[501544];

$txt_vars['weeks'] = $page->messages[501555];
$txt_vars['days'] = $page->messages[501566];
$txt_vars['hours'] = $page->messages[501577];
$txt_vars['minutes'] = $page->messages[501588];
$txt_vars['seconds'] = $page->messages[501599];
$txt_vars['closed'] = $page->messages[501610];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
