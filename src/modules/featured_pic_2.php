<?php

//module_featured_ads_pic_2.php
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
$tpl_vars['header_title'] = $page->messages[1472];

$txt_vars = array();

$txt_vars['module_display_listing_column'] = $page->messages[501155];
$txt_vars['module_display_business'] = $page->messages[501166];
$txt_vars['module_display_title'] = $page->messages[501177];
$txt_vars['module_display_ad_description'] = $page->messages[501188];

$txt_vars['module_display_tags'] = $page->messages[501199];

for ($index = 1; $index <= 20; $index++) {
    $txt_vars['module_display_optional_field_' . $index] = $page->messages[(11 * $index) + 501199];
}

$txt_vars['module_display_address'] = $page->messages[501433];
$txt_vars['module_display_city'] = $page->messages[501444];
$txt_vars['module_display_location'] = $page->messages[501656];
$txt_vars['module_display_zip'] = $page->messages[501477];
$txt_vars['module_display_number_bids'] = $page->messages[501488];
$txt_vars['module_display_price'] = $page->messages[501499];
$txt_vars['module_display_entry_date'] = $page->messages[501510];
$txt_vars['module_display_time_left'] = $page->messages[501521];

$txt_vars['item_type_1'] = $page->messages[200029];
$txt_vars['item_type_2'] = $page->messages[200030];
$txt_vars['business_type_1'] = $page->messages[501532];
$txt_vars['business_type_2'] = $page->messages[501543];

$txt_vars['weeks'] = $page->messages[501554];
$txt_vars['days'] = $page->messages[501565];
$txt_vars['hours'] = $page->messages[501576];
$txt_vars['minutes'] = $page->messages[501587];
$txt_vars['seconds'] = $page->messages[501598];
$txt_vars['closed'] = $page->messages[501609];

$txt_vars['empty_category'] = $page->messages[200191];

if (geoPC::is_ent()) {
    require MODULES_DIR . 'shared/browsing_pic.php';
}
