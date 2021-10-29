<?php
//conditional_sql.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.07.0-73-g60bad20
## 
##################################

/*
 * This is where conditional queries go.
 * For cases where an sql query might not be run, in the
 * case that it is not run, add an empty string
 * for the query.
 */

/*
 * There needs to be the same number of sql queries generated, no
 * matter what, otherwise the sql index will be off from the database.
 * That is the reason to use an empty string in cases where an "optional" query
 * is not run.
 */

//conditional sql queries.
$sql_strict = array (
//array of sql queries, if one of these fail, it
//does not continue!

);

$sql_not_strict = array (
//array of sql queries, if one of these fail, it
//just ignores it and keeps chugin along.

);

//Add queries like this...
#$sql_not_strict[] = "SQL QUERY";
#$sql_strict[] = "SQL QUERY";

//new page and page-section for holding common template text
$sql_not_strict[] = "INSERT INTO `geodesic_pages_sections` (`section_id`,`name`,`description`,`parent_section`,`display_order`,`applies_to`) VALUES (15, 'General Template Text', '', 0, 7, 0)";

$sql_not_strict[] = "INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`, `alpha_across_columns`, `alt_order_by`) VALUES
(10214, 15, 'Common Template Text', 'This is a repository of text used in main_page templates. This page is not intended to be displayed directly.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 1, '', 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, NULL, ',', ' &nbsp; >> sub|cat|list', 0, 0)";

//move "common browsing text and error messages" page to new general text section
$sql_not_strict[] = "UPDATE `geodesic_pages` SET `section_id` = 15 WHERE `page_id` = 59 OR `page_id` = 10214";