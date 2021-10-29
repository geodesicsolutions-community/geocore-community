<?php

//module_featured_ads_pic_2_level_5.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-304-g6ae40c9
##
##################################

$tpl_vars = array();
$col_name = 'featured_ad_5';

//--new
$tpl_vars['header_title'] = $page->messages[2259];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501164];
$txt_vars['module_display_business'] = $page->messages[501175];
$txt_vars['module_display_title'] = $page->messages[501186];
$txt_vars['module_display_ad_description'] = $page->messages[501197];

$txt_vars['module_display_tags'] = $page->messages[501208];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501208];
}

$txt_vars['module_display_address'] = $page->messages[501442];
$txt_vars['module_display_city'] = $page->messages[501453];
$txt_vars['module_display_location'] = $page->messages[501655];
$txt_vars['module_display_zip'] = $page->messages[501486];
$txt_vars['module_display_number_bids'] = $page->messages[501497];
$txt_vars['module_display_price'] = $page->messages[501508];
$txt_vars['module_display_entry_date'] = $page->messages[501519];
$txt_vars['module_display_time_left'] = $page->messages[501530];

$txt_vars['item_type_1'] = $page->messages[200047];
$txt_vars['item_type_2'] = $page->messages[200048];
$txt_vars['business_type_1'] = $page->messages[501541];
$txt_vars['business_type_2'] = $page->messages[501552];

$txt_vars['weeks'] = $page->messages[501563];
$txt_vars['days'] = $page->messages[501574];
$txt_vars['hours'] = $page->messages[501585];
$txt_vars['minutes'] = $page->messages[501596];
$txt_vars['seconds'] = $page->messages[501607];
$txt_vars['closed'] = $page->messages[501618];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
