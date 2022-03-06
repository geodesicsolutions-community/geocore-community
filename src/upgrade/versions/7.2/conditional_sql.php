<?php

//This is where conditional queries go.
//For cases where an sql query might not be run, in the
//case that it is not run, add an empty string
//for the query.

//There needs to be the same number of sql queries generated, no
//matter what, otherwise the sql index will be off from the database.
//That is the reason to use an empty string in cases where an "optional" query
//is not run.

//conditional sql queries.
$sql_strict = array (
//array of sql queries, if one of these fail, it
//does not continue!

);

$sql_not_strict = array (
//array of sql queries, if one of these fail, it
//just ignores it and keeps chugin along.

);

//set up userddata table to track new alert filters
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` ADD `new_listing_alert_last_sent` INT(11) NOT NULL DEFAULT 0,
ADD `new_listing_alert_gap` INT(11) NOT NULL DEFAULT 86400";

//set existing users to last-sent of now, so that they don't get alerted of extant listings
$timeShift = $this->_db->GetOne("SELECT `value` FROM `geodesic_site_settings` WHERE `setting` = 'time_shift'"); //emulate geoUtil::time()
$sql_not_strict[] = "UPDATE `geodesic_userdata` SET `new_listing_alert_last_sent` = " . (time() + 3600 * $timeShift + 1) . " WHERE `new_listing_alert_last_sent` = 0"; //add one second so it doesn't re-send the last alert

//add price_applies column
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD `price_applies` ENUM( 'lot', 'item' ) NOT NULL DEFAULT 'lot' AFTER `conversion_rate`";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_expired` ADD `price_applies` ENUM( 'lot', 'item' ) NOT NULL DEFAULT 'lot' AFTER `conversion_rate`";

if (!$this->fieldExists('geodesic_classifieds', 'quantity_remaining')) {
    //add quantity remaining
    $sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD `quantity_remaining` INT NOT NULL DEFAULT '0' AFTER `quantity` ";
    //set value to same thingy - only done if column doesn't already exist
    $sql_not_strict[] = "UPDATE `geodesic_classifieds` SET `quantity_remaining`=`quantity` WHERE `quantity_remaining`=0 AND `live`=1";
} else {
    //do not add fields...  we don't want to re-run the query that sets the quantity remaining
    //as it could reset listings if this upgrade it re-run
    $sql_not_strict[] = '';
    $sql_not_strict[] = '';
}

//add quantity to expired table since that has become more important...
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_expired` ADD `quantity` INT NOT NULL DEFAULT '0' AFTER `auction_type` ";
//and also add quantity_remaining to expired table
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_expired` ADD `quantity_remaining` INT NOT NULL DEFAULT '0' AFTER `quantity` ";


//New page for notify when favorite is going to expire
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`, `alpha_across_columns`, `alt_order_by`) VALUES (10212, '4', 'Favorite Expires Soon E-mail', 'This is the e-mail sent to the user when a listing on their favorites list is going to expire soon.', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '2', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', NULL, ',', ' &nbsp; >> sub|cat|list', '', '0')";

//convert old style of e-mail settings to new...
$exps = array ('send_classified_expire_email' => 'classified_expire_email', 'send_auction_expire_email' => 'auction_expire_email');
foreach ($exps as $setting => $newSetting) {
    $val = $this->_db->GetOne("SELECT `value` FROM `geodesic_site_settings` WHERE `setting`='{$setting}'");
    if ($val > 0) {
        //before would allow setting to decimal for "part of day"...  force it to
        //the closest second now.
        $exp = floor(86400 * $val);
        $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = '{$newSetting}', `value` = '{$exp}'";
    }
}

//Changes for favorite expiration
$sql_not_strict[] = "ALTER TABLE `geodesic_favorites` ADD `expiration_notice` TINYINT( 1 ) NOT NULL DEFAULT '0',
ADD `expiration_last_sent` INT NOT NULL DEFAULT '0'";
$sql_not_strict[] = "ALTER TABLE `geodesic_favorites` ADD INDEX `expiration_notice` ( `expiration_notice` )";
$sql_not_strict[] = "ALTER TABLE `geodesic_favorites` ADD INDEX `expiration_last_sent` ( `expiration_last_sent` )";
