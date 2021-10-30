#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


# Category Dropdown Quick Nav module
 INSERT IGNORE INTO `geodesic_pages` 
(`page_id` ,`section_id` ,`name` ,`description` ,`special_instructions` ,`internal_template` ,`module` ,`module_number_of_ads_to_display` ,`module_display_header_row` ,`module_display_business_type` ,`module_display_photo_icon` ,`module_display_ad_description` ,`module_display_ad_description_where` ,`module_display_price` ,`module_display_entry_date` ,`display_all_of_description` ,`length_of_description` ,`module_file_name` ,`module_replace_tag` ,`module_display_username` ,`module_display_title` ,`module_text_type` ,`module_display_contact` ,`module_display_phone1` ,`module_display_phone2` ,`module_display_address` ,`module_display_optional_field_1` ,`module_display_optional_field_2` ,`module_display_optional_field_3` ,`module_display_optional_field_4` ,`module_display_optional_field_5` ,`module_display_optional_field_6` ,`module_display_optional_field_7` ,`module_display_optional_field_8` ,`module_display_optional_field_9` ,`module_display_optional_field_10` ,`module_display_optional_field_11` ,`module_display_optional_field_12` ,`module_display_optional_field_13` ,`module_display_optional_field_14` ,`module_display_optional_field_15` ,`module_display_optional_field_16` ,`module_display_optional_field_17` ,`module_display_optional_field_18` ,`module_display_optional_field_19` ,`module_display_optional_field_20` ,`module_display_city` ,`module_display_state` ,`module_display_country` ,`module_display_zip` ,`module_logged_in_html` ,`module_logged_out_html` ,`module_display_name` ,`module_use_image` ,`module_display_classified_id` ,`module_thumb_width` ,`module_thumb_height` ,`module_display_attention_getter` ,`module_number_of_columns` ,`module_display_filter_in_row` ,`cache_expire` ,`use_category_cache` ,`category_cache` ,`number_of_browsing_columns` ,`display_category_count` ,`browsing_count_format` ,`display_category_description` ,`display_no_subcategory_message` ,`display_category_image` ,`display_unselected_subfilters` ,`php_code` ,`display_empty_message` ,`module_category_level_to_display` ,`module_category` ,`module_display_new_ad_icon` ,`photo_or_icon` ,`module_type` ,`module_display_number_bids` ,`module_display_time_left` ,`email` ,`module_display_type_listing` ,`module_display_type_text` ,`module_display_listing_column` ,`admin_label` ,`extra_page_text` ,`applies_to` ,`maxNodeDepth` ,`module_display_company_name` ,`module_display_sub_category_nav_links` ,`module_sub_category_nav_prefix` ,`module_sub_category_nav_separator` ,`module_sub_category_nav_surrounding`)
 VALUES (10199, 0, 'Category Dropdown Box', 'Displays a category dropdown list', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 'module_display_category_quick_navigation.php', '(!CATEGORY_DROPDOWN!)', 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 5, 1, 5, 0, 0, 0, 0, '', '', 1, 0, 0, 2, 9, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 1, '', ',', ' &nbsp; >> sub|cat|list ');
# Browsing Options module
INSERT IGNORE INTO `geodesic_pages` 
(`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `extra_page_text`, `applies_to`, `maxNodeDepth`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`)
 VALUES (10200, 0, 'Category Browsing Options', 'Displays a list of options for use in showing only certain ads while browsing', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'module_display_category_browsing_options.php', '(!CATEGORY_BROWSING_OPTIONS!)', 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 8, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, '', ',', ' &nbsp; >> sub|cat|list ');

INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'cat_browse_all_listings', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'cat_browse_end_today', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'cat_browse_has_pics', `value` = '1';

# Add new cron table, for keeping track of cron tasks.
DROP TABLE IF EXISTS `geodesic_cron`;
CREATE TABLE IF NOT EXISTS `geodesic_cron` (
  `task` varchar(128) NOT NULL,
  `type` enum('addon','main') NOT NULL,
  `last_run` int(14) NOT NULL,
  `running` int(14) NOT NULL,
  `interval` int(14) NOT NULL,
  PRIMARY KEY  (`task`),
  KEY `interval` (`interval`)
);
INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `last_run`, `running`, `interval`) VALUES 
('archive_listings', 'main', 0, 0, 86400),
('close_listings', 'main', 0, 0, 60),
('expire_groups_and_plans', 'main', 0, 0, 3600),
('send_listing_expiration_emails', 'main', 0, 0, 3600);

INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'cron_deadlock_time_limit', `value` = '1800';

# USED IN NEW IMAGE GALLERY
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'maximum_thumb_width', `value` = '75';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'maximum_thumb_height', `value` = '75';

#for moneris payment gateway
INSERT IGNORE INTO `geodesic_credit_card_choices` (cc_id, chosen_cc, name, explanation, cc_table, cc_transaction_table, cc_initiate_file, cc_process_file, cc_admin_file)
VALUES
('11', '0','Moneris','This allows the use of the Moneris payment gateway.', 'geodesic_cc_moneris', 'geodesic_cc_moneris_transactions', 'cc_initiate_moneris.php', '', 'admin_cc_moneris.php');

	
CREATE TABLE IF NOT EXISTS `geodesic_cc_moneris_transactions` (
	`moneris_transaction_id` int(11) NOT NULL auto_increment,
	`classified_id` int(11) NOT NULL default '0',
	`user_id` int(11) NOT NULL default '0',
	`first_name` tinytext NOT NULL,
	`last_name` tinytext NOT NULL,
	`address` tinytext NOT NULL,
	`city` tinytext NOT NULL,
	`state` tinytext NOT NULL,
	`country` tinytext NOT NULL,
	`zip` varchar(15) NOT NULL default '',
	`email` tinytext NOT NULL,
	`card_num` varchar(25) NOT NULL default '',
	`decryption_key` tinytext NOT NULL,
	`exp_date` varchar(10) NOT NULL default '',
	`tax` double(5,2) NOT NULL default '0.00',
	`amount` double(5,2) NOT NULL default '0.00',
	`fax` varchar(20) NOT NULL default '',
	`company` tinytext NOT NULL,
	`description` tinytext NOT NULL,
	`card_type` varchar(255),
	`trans_amount` double(11,2),
	`txn_number` varchar(255),
	`receipt_id` varchar(255),
	`trans_type` varchar(255),
	`reference_num` varchar(255),
	`response_code` int(4),
	`iso` varchar(255),
	`message` varchar(255),
	`auth_code` varchar(255),
	`complete` varchar(255),
	`trans_date` varchar(255),
	`trans_time` varchar(255),
	`ticket` varchar(255),
	`timed_out` varchar(255),
	`ad_placement` int(11) NOT NULL default '0',
	`renew` int(11) NOT NULL default '0',
	`bolding` int(11) NOT NULL default '0',
	`better_placement` int(11) NOT NULL default '0',
	`featured_ad` int(11) NOT NULL default '0',
	`featured_ad_2` int(11) NOT NULL default '0',
	`featured_ad_3` int(11) NOT NULL default '0',
	`featured_ad_4` int(11) NOT NULL default '0',
	`featured_ad_5` int(11) NOT NULL default '0',
	`attention_getter` int(11) NOT NULL default '0',
	`attention_getter_choice` int(11) NOT NULL default '0',
	`renewal_length` int(11) NOT NULL default '0',
	`use_credit_for_renewal` int(11) NOT NULL default '0',
	`subscription_renewal` int(11) NOT NULL default '0',
	`price_plan_id` int(11) NOT NULL default '0',
	`account_balance` int(11) NOT NULL default '0',
	`pay_invoice` int(11) NOT NULL default '0',
	`auction_id` int(11) NOT NULL default '0',
	PRIMARY KEY `moneris_transaction_id` (`moneris_transaction_id`)
	);

# Change default setting for price plans for roll_final_fee_into_transaction to 1
ALTER TABLE `geodesic_classifieds_price_plans` CHANGE `roll_final_fee_into_future` `roll_final_fee_into_future` INT( 11 ) NOT NULL DEFAULT '1';

# Change page title to section title
# 1628=subscription renewal
# 2497 = add to account balance form
# 2522 = add to account balance approval page
# 608 = transaction details


#UPDATE `geodesic_pages_messages` SET `name` = 'Section+Title' WHERE `message_id` in (1628, 2497, 2522,608) LIMIT 4;


