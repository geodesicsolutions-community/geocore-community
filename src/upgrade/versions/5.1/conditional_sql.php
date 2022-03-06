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

//insert page for new tags page
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`, `alpha_across_columns`, `alt_order_by`)
 VALUES ('10210', '1', 'Browse Tagged Listings', 'This page displays all listings that are tagged with a specific tag.', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '2', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', NULL, ',', ' &nbsp; >> sub|cat|list', '', '0')";

//insert module for new search tag module
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`, `alpha_across_columns`, `alt_order_by`)
 VALUES ('10211', '0', 'Tag Search', 'Display auto-complete listing tag search box.', '', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'tag_search.php', 'tag_search', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '2', '6', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', NULL, ',', ' &nbsp; >> sub|cat|list', '', '0')";

//add cron job
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `last_run`, `running`, `interval`) VALUES ('better_placement_rotation', 'main', '0', '0', '86400')";


//add new display setting location for tags, make it default to same columns displayed as browsing page does
$this->addFieldLocationDefaults('tags', 'browsing');

//change how gallery style setting is saved, now that there are 3 different ways
$currentView = $this->_db->GetOne("SELECT `value` FROM `geodesic_site_settings` WHERE `setting`='gallery_style'");
if (in_array($currentView, array ('classic','gallery','filmstrip'))) {
    //already using new settings
    $sql_not_strict[] = '';
} else {
    //if current setting is 1 then it uses "classic", if it's 0 or not set, it uses gallery.
    $galleryStyle = ($currentView) ? 'classic' : 'gallery';
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'gallery_style', `value` = '$galleryStyle'";
}

// Make better placement a larger field
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` CHANGE `better_placement` `better_placement` INT( 14 ) NOT NULL DEFAULT '0'";

//Simplify bid increments
$sql_not_strict[] = "ALTER TABLE `geodesic_auctions_increments` DROP `high`";

//make sure lowest bid increment starts at 0
$currentLowest = $this->_db->GetOne("SELECT MIN(`low`) FROM `geodesic_auctions_increments`");

if ($currentLowest === null || $currentLowest === false) {
    //insert increment, somehow the table is empty
    $sql_not_strict[] = "INSERT INTO `geodesic_auctions_increments` (`low`, `increment`) VALUES (0.00, 5.00)";
} else {
    $sql_not_strict[] = "UPDATE `geodesic_auctions_increments` SET `low`=0.00 WHERE `low`='{$currentLowest}' LIMIT 1";
}

//fix category questions languages
$languages = $this->_db->GetAll("SELECT `language_id` FROM `geodesic_pages_languages` WHERE `language_id`!=1");

if ($languages) {
    $sql = "SELECT * FROM `geodesic_classifieds_sell_questions_languages` WHERE language_id=1";
    $questions = $this->_db->Execute($sql);
    foreach ($questions as $question) {
        foreach ($languages as $lang) {
            $language_id = (int)$lang['language_id'];
            $count = (int)$this->_db->GetOne("SELECT COUNT(*) FROM `geodesic_classifieds_sell_questions_languages`
				WHERE language_id=? AND question_id=?", array($language_id, $question['question_id']));
            if (!$count) {
                //need to insert it

                $sql_not_strict[] = "INSERT INTO `geodesic_classifieds_sell_questions_languages` SET 
					question_id={$question['question_id']}, language_id={$language_id}, 
					name=" . $this->_db->qstr($question['name']) . ", explanation=" . $this->_db->qstr($question['explanation']) . ",
					choices=" . $this->_db->qstr($question['choices']);
            } else {
                //already in there...
                $sql_not_strict[] = '';
            }
        }
    }
}
