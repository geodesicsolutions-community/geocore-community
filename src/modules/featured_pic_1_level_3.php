<?php

//module_featured_ads_pic_1_level_3.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

$tpl_vars = array();
$col_name = 'featured_ad_3';

//--new
$tpl_vars['header_title'] = $page->messages[2253];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501159];
$txt_vars['module_display_business'] = $page->messages[501170];
$txt_vars['module_display_title'] = $page->messages[501181];
$txt_vars['module_display_ad_description'] = $page->messages[501192];

$txt_vars['module_display_tags'] = $page->messages[501203];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501203];
}

$txt_vars['module_display_address'] = $page->messages[501437];
$txt_vars['module_display_city'] = $page->messages[501448];
$txt_vars['module_display_location'] = $page->messages[501648];
$txt_vars['module_display_zip'] = $page->messages[501481];
$txt_vars['module_display_number_bids'] = $page->messages[501492];
$txt_vars['module_display_price'] = $page->messages[501503];
$txt_vars['module_display_entry_date'] = $page->messages[501514];
$txt_vars['module_display_time_left'] = $page->messages[501524];

$txt_vars['item_type_1'] = $page->messages[200037];
$txt_vars['item_type_2'] = $page->messages[200038];
$txt_vars['business_type_1'] = $page->messages[501536];
$txt_vars['business_type_2'] = $page->messages[501547];

$txt_vars['weeks'] = $page->messages[501558];
$txt_vars['days'] = $page->messages[501569];
$txt_vars['hours'] = $page->messages[501580];
$txt_vars['minutes'] = $page->messages[501591];
$txt_vars['seconds'] = $page->messages[501602];
$txt_vars['closed'] = $page->messages[501613];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
