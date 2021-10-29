<?php

//module_featured_ads_pic_1_level_5.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

$tpl_vars = array();
$col_name = 'featured_ad_5';

//--new
$tpl_vars['header_title'] = $page->messages[2255];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501163];
$txt_vars['module_display_business'] = $page->messages[501174];
$txt_vars['module_display_title'] = $page->messages[501185];
$txt_vars['module_display_ad_description'] = $page->messages[501196];

$txt_vars['module_display_tags'] = $page->messages[501207];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501207];
}

$txt_vars['module_display_address'] = $page->messages[501441];
$txt_vars['module_display_city'] = $page->messages[501452];
$txt_vars['module_display_location'] = $page->messages[501650];
$txt_vars['module_display_zip'] = $page->messages[501485];
$txt_vars['module_display_number_bids'] = $page->messages[501496];
$txt_vars['module_display_price'] = $page->messages[501507];
$txt_vars['module_display_entry_date'] = $page->messages[501518];
$txt_vars['module_display_time_left'] = $page->messages[501529];

$txt_vars['item_type_1'] = $page->messages[200045];
$txt_vars['item_type_2'] = $page->messages[200046];
$txt_vars['business_type_1'] = $page->messages[501540];
$txt_vars['business_type_2'] = $page->messages[501551];

$txt_vars['weeks'] = $page->messages[501562];
$txt_vars['days'] = $page->messages[501573];
$txt_vars['hours'] = $page->messages[501584];
$txt_vars['minutes'] = $page->messages[501595];
$txt_vars['seconds'] = $page->messages[501606];
$txt_vars['closed'] = $page->messages[501617];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
