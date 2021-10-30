#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


# Add new page for seller buyer transactions
INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `maxNodeDepth`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`)
	VALUES ('10201', '14', 'Seller to Buyer Transaction Page', 'This page displays the results of transactions between sellers and buyers, and is also used to display applicable error messages.', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '0', '2', '0', '0', '0', '0', '0', '0', '0', '', '2', '0', '0', '0', NULL, ',', ' &nbsp; >> sub|cat|list');

# Tables for new order & invoice system

CREATE TABLE IF NOT EXISTS `geodesic_order` (
  `id` int(14) NOT NULL auto_increment,
  `status` varchar(16) NOT NULL,
  `parent` int(14) NOT NULL,
  `buyer` int(14) NOT NULL,
  `seller` int(14) NOT NULL,
  `created` int(14) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `parent` (`parent`),
  KEY `buyer` (`buyer`),
  KEY `seller` (`seller`),
  KEY `created` (`created`)
) AUTO_INCREMENT=152 ;

CREATE TABLE IF NOT EXISTS `geodesic_order_registry` (
  `index_key` varchar(255) NOT NULL,
  `order` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `order` (`order`),
  KEY `val_string` (`val_string`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_order_item` (
  `id` int(14) NOT NULL auto_increment,
  `status` varchar(16) NOT NULL,
  `order` int(14) NOT NULL,
  `parent` int(14) NOT NULL,
  `type` varchar(255) NOT NULL,
  `price_plan` int(14) NOT NULL,
  `category` int(14) NOT NULL,
  `cost` decimal(14,4) NOT NULL,
  `created` int(14) NOT NULL,
  `process_order` int(14) NOT NULL default '10',
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `order` (`order`),
  KEY `type` (`type`),
  KEY `price_plan` (`price_plan`),
  KEY `category` (`category`),
  KEY `created` (`created`),
  KEY `parent` (`parent`),
  KEY `process_order` (`process_order`)
) AUTO_INCREMENT=186 ;

CREATE TABLE IF NOT EXISTS `geodesic_order_item_registry` (
  `index_key` varchar(255) NOT NULL,
  `order_item` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `order_item` (`order_item`),
  KEY `val_string` (`val_string`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_plan_item` (
  `order_item` varchar(255) NOT NULL,
  `price_plan` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `process_order` int(14) NOT NULL,
  `need_admin_approval` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`order_item`,`price_plan`,`category`),
  KEY `process_order` (`process_order`),
  KEY `enabled` (`enabled`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_plan_item_registry` (
  `index_key` varchar(255) NOT NULL,
  `plan_item` varchar(255) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `plan_item` (`plan_item`),
  KEY `val_string` (`val_string`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_invoice` (
  `id` int(14) NOT NULL auto_increment,
  `parent` int(14) NOT NULL,
  `order` int(14) NOT NULL,
  `created` int(14) NOT NULL,
  `due` int(14) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `parent` (`parent`),
  KEY `order` (`order`),
  KEY `created` (`created`),
  KEY `due` (`due`)
) AUTO_INCREMENT=194 ;

CREATE TABLE IF NOT EXISTS `geodesic_invoice_registry` (
  `index_key` varchar(255) NOT NULL,
  `invoice` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `invoice` (`invoice`),
  KEY `val_string` (`val_string`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_transaction` (
  `id` int(14) NOT NULL auto_increment,
  `invoice` int(14) NOT NULL,
  `amount` decimal(14,4) NOT NULL,
  `description` text NOT NULL,
  `date` int(14) NOT NULL,
  `user` int(14) NOT NULL,
  `gateway` varchar(255) NOT NULL,
  `gateway_transaction` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `amount` (`amount`),
  KEY `date` (`date`),
  KEY `user` (`user`),
  KEY `gateway_transaction` (`gateway_transaction`),
  KEY `status` (`status`),
  KEY `invoice` (`invoice`)
) AUTO_INCREMENT=220 ;

CREATE TABLE IF NOT EXISTS `geodesic_transaction_registry` (
  `index_key` varchar(255) NOT NULL,
  `transaction` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `transaction` (`transaction`),
  KEY `val_string` (`val_string`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_payment_gateway` (
  `name` varchar(128) NOT NULL,
  `gateway_type` varchar(64) NOT NULL,
  `display_order` int(4) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL,
  `group` int(11) NOT NULL default '0',
  PRIMARY KEY  (`name`,`group`),
  UNIQUE KEY `display_order` (`display_order`,`group`),
  KEY `gateway_type` (`gateway_type`),
  KEY `enabled` (`enabled`),
  KEY `default` (`default`),
  KEY `group` (`group`)
);

ALTER TABLE `geodesic_payment_gateway` DROP INDEX `display_order` ,
  ADD UNIQUE `display_order` ( `display_order` , `group` );

CREATE TABLE IF NOT EXISTS `geodesic_payment_gateway_registry` (
  `index_key` varchar(255) NOT NULL,
  `payment_gateway` varchar(128) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `payment_gateway` (`payment_gateway`),
  KEY `val_string` (`val_string`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_cart` (
  `id` int(14) NOT NULL auto_increment,
  `session` varchar(32) NOT NULL,
  `user_id` int(14) NOT NULL,
  `order` int(14) NOT NULL,
  `main_type` varchar(128) NOT NULL,
  `order_item` int(14) NOT NULL,
  `last_time` int(14) NOT NULL,
  `step` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `session` (`session`),
  KEY `main_type` (`main_type`),
  KEY `last_time` (`last_time`),
  KEY `order_item` (`order_item`),
  KEY `user_id` (`user_id`)
) AUTO_INCREMENT=28 ;

CREATE TABLE IF NOT EXISTS `geodesic_cart_registry` (
  `index_key` varchar(255) NOT NULL,
  `cart` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `cart` (`cart`),
  KEY `val_string` (`val_string`)
) ;
# New feature for addons: store addon pages
# DROP TABLE IF EXISTS `geodesic_addon_pages`;
CREATE TABLE IF NOT EXISTS `geodesic_addon_pages` (
  `addon` varchar(128) NOT NULL,
  `auth_tag` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `language` int(11) NOT NULL,
  `template` int(11) NOT NULL,
  `attached_modules` text NOT NULL,
  PRIMARY KEY  (`addon`,`name`,`language`)
);

CREATE TABLE IF NOT EXISTS `geodesic_extra_pages_registry` (
  `index_key` varchar(255) NOT NULL,
  `extra_pages` varchar(255) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` longtext NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `extra_pages` (`extra_pages`),
  KEY `val_string` (`val_string`)
) ;


# add language specific table for category specific questions
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_sell_questions_languages` (
  `question_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `explanation` tinytext NOT NULL,
  `choices` varchar(40) NOT NULL,
  PRIMARY KEY  (`question_id`,`language_id`)
) ;

# For new addon settings
CREATE TABLE IF NOT EXISTS `geodesic_addon_registry` (
  `index_key` varchar(255) NOT NULL,
  `addon` varchar(255) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `addon` (`addon`),
  KEY `val_string` (`val_string`)
) ;

# Fix mime types for use in copy of listings
UPDATE `geodesic_file_types` SET `extension` = 'gif' WHERE `mime_type` = 'image/gif' AND `extension` = '';
UPDATE `geodesic_file_types` SET `extension` = 'jpg' WHERE (`mime_type` = 'image/jpeg' OR `mime_type` = 'image/pjpeg' ) AND `extension` = '';
UPDATE `geodesic_file_types` SET `extension` = 'bmp' WHERE `mime_type` = 'image/x-mx-bmp' AND `extension` = '';
UPDATE `geodesic_file_types` SET `extension` = 'png' WHERE `mime_type` = 'image/x-png' AND `extension` = '';
UPDATE `geodesic_file_types` SET `extension` = 'tif' WHERE `mime_type` = 'image/tiff' AND `extension` = '';

# Changes name of the Newest Listings Modules in the admin to reflect Ending Soonest feature
UPDATE `geodesic_pages` SET `name` = 'Newest/Ending Soonest Listings 1', `description` = 'Displays the listings from the current category and orders by either newest or ending soonest first' WHERE `page_id` = 60 LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Newest/Ending Soonest Listings 2', `description` = 'Displays the listings from the current category and orders by either newest or ending soonest first' WHERE `page_id` = 61 LIMIT 1 ;

# Move expire subscriptions to a cron task
INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `last_run`, `running`, `interval`) VALUES 
('expire_subscriptions', 'main', 0, 0, 3600);

# Set default of on for new "require password to edit user info"
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'info_edit_require_pass', `value` = '1';

# Fix duplicate text entry that is not used.
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = 28 AND `text_id` = 500126;


INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'cat_browse_opts_as_ddl', `value` = '1';

INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `last_run`, `running`, `interval`) VALUES 
('send_negative_account_balance_emails', 'main', 0, 0, 2592000),
('process_email_queue', 'main', 0, 0, 300),
('expire_inactive_carts', 'main', 0, 0, 3600),
('remove_old_order_data', 'main', 0, 0, 2592000),
('remove_old_invoices', 'main', 0, 0, 2595600),
('remove_messages', 'main', 0, 0, 2592000),
('remove_archived_listings', 'main', 0, 0, 2592000);

#Add default time before cart expires to be 1 week (604800 seconds)
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'cart_expire_user', `value` = '604800';

#Add default time before listing gets archived to 30 days (2592000 seconds)
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'archive_listing_delay', `value` = '2592000';

#Add default time before order gets removed to 365 days (31536000 seconds)
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'order_data_age', `value` = '31536000';

#Add default time before invoice gets removed to 365 days (31536000 seconds)
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'invoice_remove_age', `value` = '31536000';
