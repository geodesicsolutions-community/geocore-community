ALTER TABLE `geodesic_classifieds_configuration` ADD `admin_approves_all_registration` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `subscription_to_view_or_bid_ads` INT( 11 ) NOT NULL;
ALTER TABLE `geodesic_classifieds_price_plans` ADD `invoice_max` FLOAT( 5, 2 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `checkbox_columns` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `charset` VARCHAR( 28 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_price_plans` ADD `initial_site_balance` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_price_plans` ADD `buy_now_only` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `module_text_type` VARCHAR( 50 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `send_ad_expire_frequency` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `geodesic_classifieds` ADD `expiration_last_sent` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `upgrade_time` INT( 1 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `send_admin_end_email` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `use_buy_now` TINYINT( 11 ) DEFAULT '0' NOT NULL , ADD `editable_buy_now` TINYINT( 11 ) DEFAULT '0' NOT NULL , ADD `require_buy_now` TINYINT( 11 ) DEFAULT '0' NOT NULL ;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_votes` (
	`classified_id` int(11) NOT NULL default '0',
	`userID` int(11) NOT NULL default '0',
	`voter_ip` varchar(20) NOT NULL default '',
	`vote` int(11) NOT NULL default '0',
	`vote_title` tinytext NOT NULL default '',
	`vote_comments` tinytext NOT NULL default '',
	`date_entered` int(14) NOT NULL default '0'
	);
ALTER TABLE `geodesic_classifieds`
	ADD `type` INT DEFAULT '2' NOT NULL ,
	ADD `auction_type` INT NOT NULL ,
	ADD `auction_length` INT NOT NULL ,
	ADD `quantity` INT NOT NULL ,
	ADD `final_fee` INT NOT NULL ,
	ADD `final_fee_transaction_number` INT NOT NULL ,
	ADD `minimum_bid` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `starting_bid` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `reserve_price` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `buy_now` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `current_bid` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `final_price` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `high_bidder` DOUBLE( 10, 2 ) DEFAULT '0.00' NOT NULL ,
	ADD `start_time` INT( 14 ) NOT NULL ,
	ADD `payment_options` TINYTEXT NOT NULL ,
	ADD `end_time` INT( 14 ) NOT NULL ,
	ADD `buy_now_only` INT NOT NULL ,
	ADD `item_type` INT NOT NULL ;
CREATE TABLE IF NOT EXISTS `geodesic_payment_types` (
	`type_id` int( 11 ) NOT NULL AUTO_INCREMENT ,
	`type_name` tinytext NOT NULL ,
	`display_order` int( 11 ) NOT NULL default '0',
	KEY `type_id` ( `type_id` )
	) ;
ALTER TABLE `geodesic_classifieds_sell_session` ADD `auction_type` TINYINT( 1 ) DEFAULT '1' NOT NULL,
	ADD `auction_quantity` INT DEFAULT '1' NOT NULL ,
	ADD `auction_minimum` INT NOT NULL ,
	ADD `auction_reserve` INT NOT NULL ,
	ADD `auction_buy_now` INT NOT NULL ,
	ADD `payment_options` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds` ADD `auction_type` TINYINT( 1 ) DEFAULT '1' NOT NULL,
	ADD `auction_quantity` INT DEFAULT '1' NOT NULL ,
	ADD `auction_minimum` INT NOT NULL ,
	ADD `auction_reserve` INT NOT NULL ,
	ADD `auction_buy_now` INT NOT NULL  ,
	ADD `payment_options` VARCHAR( 255 ) NOT NULL ,
	ADD `type` TINYINT( 1 ) DEFAULT '1' NOT NULL ;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_bids` (
	`auction_id` int(11) NOT NULL default '0',
	`bidder` int(11) NOT NULL default '0',
	`bid` double(16,2) NOT NULL default '0.00',
	`time_of_bid` int(14) NOT NULL default '0',
	`quantity` int(11) NOT NULL default '0',
	`buy_now_bid` int(11) NOT NULL default '0'
	);
ALTER TABLE `geodesic_pages_messages` ADD `type` INT DEFAULT '0' NOT NULL, ADD `subtype` INT DEFAULT '0' NOT NULL;
UPDATE `geodesic_pages_messages` SET `subtype` = '1' WHERE `name` LIKE '%label%';
UPDATE `geodesic_pages_messages` SET `subtype` = '2' WHERE `name` LIKE '%header%';
UPDATE `geodesic_pages_messages` SET `subtype` = '3' WHERE `name` LIKE '%error%';
UPDATE `geodesic_pages_messages` SET `type` = '1' WHERE `name` LIKE '%page%title%';
UPDATE `geodesic_pages_messages` SET `type` = '2' WHERE `name` LIKE '%section%title%';
UPDATE `geodesic_pages_messages` SET `type` = '12' WHERE `name` LIKE '%phone%';
UPDATE `geodesic_pages_messages` SET `type` = '8' WHERE `name` LIKE '%city%';
UPDATE `geodesic_pages_messages` SET `type` = '9' WHERE `name` LIKE '%state%';
UPDATE `geodesic_pages_messages` SET `type` = '10' WHERE `name` LIKE '%country%';
UPDATE `geodesic_pages_messages` SET `type` = '11' WHERE `name` LIKE '%zip%';
UPDATE `geodesic_pages_messages` SET `type` = '17' WHERE `name` LIKE '%mapping%city%';
UPDATE `geodesic_pages_messages` SET `type` = '18' WHERE `name` LIKE '%mapping%state%';
UPDATE `geodesic_pages_messages` SET `type` = '19' WHERE `name` LIKE '%mapping%country%';
UPDATE `geodesic_pages_messages` SET `type` = '20' WHERE `name` LIKE '%mapping%zip%';
UPDATE `geodesic_pages_messages` SET `type` = '21' WHERE `name` LIKE '%mapping%link%';
UPDATE `geodesic_pages_messages` SET `type` = '22' WHERE `name` LIKE '%url%link%';
UPDATE `geodesic_pages_messages` SET `type` = '29' WHERE `name` LIKE '%username%';
UPDATE `geodesic_pages_messages` SET `type` = '30' WHERE `name` LIKE '%password%';
UPDATE `geodesic_pages_messages` SET `type` = '33' WHERE `name` LIKE '%bolding%';
UPDATE `geodesic_pages_messages` SET `type` = '34' WHERE `name` LIKE '%better%placement%';
UPDATE `geodesic_pages_messages` SET `type` = '35' WHERE `name` LIKE '%feature%';
UPDATE `geodesic_pages_messages` SET `type` = '36' WHERE `name` LIKE '%attention%getter%';
UPDATE `geodesic_pages_messages` SET `type` = '41' WHERE `name` LIKE '%optional%+1+%' OR `name` LIKE '%optional% 1 %';
UPDATE `geodesic_pages_messages` SET `type` = '42' WHERE `name` LIKE '%optional%+2+%' OR `name` LIKE '%optional% 2 %';
UPDATE `geodesic_pages_messages` SET `type` = '43' WHERE `name` LIKE '%optional%+3+%' OR `name` LIKE '%optional% 3 %';
UPDATE `geodesic_pages_messages` SET `type` = '44' WHERE `name` LIKE '%optional%+4+%' OR `name` LIKE '%optional% 4 %';
UPDATE `geodesic_pages_messages` SET `type` = '45' WHERE `name` LIKE '%optional%+5+%' OR `name` LIKE '%optional% 5 %';
UPDATE `geodesic_pages_messages` SET `type` = '46' WHERE `name` LIKE '%optional%+6+%' OR `name` LIKE '%optional% 6 %';
UPDATE `geodesic_pages_messages` SET `type` = '47' WHERE `name` LIKE '%optional%+7+%' OR `name` LIKE '%optional% 7 %';
UPDATE `geodesic_pages_messages` SET `type` = '48' WHERE `name` LIKE '%optional%+8+%' OR `name` LIKE '%optional% 8 %';
UPDATE `geodesic_pages_messages` SET `type` = '49' WHERE `name` LIKE '%optional%+9+%' OR `name` LIKE '%optional% 9 %';
UPDATE `geodesic_pages_messages` SET `type` = '50' WHERE `name` LIKE '%optional%+10+%' OR `name` LIKE '%optional% 10 %';
UPDATE `geodesic_pages_messages` SET `type` = '51' WHERE `name` LIKE '%optional%+11+%' OR `name` LIKE '%optional% 11 %';
UPDATE `geodesic_pages_messages` SET `type` = '52' WHERE `name` LIKE '%optional%+12+%' OR `name` LIKE '%optional% 12 %';
UPDATE `geodesic_pages_messages` SET `type` = '53' WHERE `name` LIKE '%optional%+13+%' OR `name` LIKE '%optional% 13 %';
UPDATE `geodesic_pages_messages` SET `type` = '54' WHERE `name` LIKE '%optional%+14+%' OR `name` LIKE '%optional% 14 %';
UPDATE `geodesic_pages_messages` SET `type` = '55' WHERE `name` LIKE '%optional%+15+%' OR `name` LIKE '%optional% 15 %';
UPDATE `geodesic_pages_messages` SET `type` = '56' WHERE `name` LIKE '%optional%+16+%' OR `name` LIKE '%optional% 16 %';
UPDATE `geodesic_pages_messages` SET `type` = '57' WHERE `name` LIKE '%optional%+17+%' OR `name` LIKE '%optional% 17 %';
UPDATE `geodesic_pages_messages` SET `type` = '58' WHERE `name` LIKE '%optional%+18+%' OR `name` LIKE '%optional% 18 %';
UPDATE `geodesic_pages_messages` SET `type` = '59' WHERE `name` LIKE '%optional%+19+%' OR `name` LIKE '%optional% 19 %';
UPDATE `geodesic_pages_messages` SET `type` = '60' WHERE `name` LIKE '%optional%+20+%' OR `name` LIKE '%optional% 20 %';
UPDATE `geodesic_pages_messages` SET `type` = '41', `subtype` = '1' WHERE `message_id` = '912';
UPDATE `geodesic_pages_messages` SET `type` = '6', `subtype` = '1' WHERE `message_id` = '6';
ALTER TABLE `geodesic_pages_messages` ADD `classauctions` INT DEFAULT '0' NOT NULL;
UPDATE `geodesic_pages` SET `display_unselected_subfilters` = '1';

ALTER TABLE `geodesic_classifieds_sell_session` ADD `cvv2_code` varchar( 4 ) NOT NULL default '';
ALTER TABLE `geodesic_cc_paypal_transactions` ADD `cvv2_code` varchar( 4 ) NOT NULL default '';
ALTER TABLE `geodesic_classifieds_sell_session` ADD `paypal_id` TINYTEXT NOT NULL;
ALTER TABLE `geodesic_classifieds` ADD `paypal_id` TINYTEXT NOT NULL;
ALTER TABLE `geodesic_classifieds_sell_session` ADD `type` INT( 1 ) NOT NULL ;

ALTER TABLE `geodesic_categories`
	ADD `display_number_bids` int(11) NOT NULL default '0',
	ADD `display_time_left` int(11) NOT NULL default '0';

ALTER TABLE `geodesic_classifieds_configuration`
 ADD `display_number_bids` int(11) NOT NULL default '0',
 ADD `display_time_left` int(11) NOT NULL default '0',
 ADD `email_salutation_type` int(11) NOT NULL default '0',
 ADD `debug_admin` int(11) NOT NULL default '0',
 ADD `debug_browse` int(11) NOT NULL default '0',
 ADD `debug_register` int(11) NOT NULL default '0',
 ADD `debug_feedback` int(11) NOT NULL default '0',
 ADD `debug_user_management` int(11) NOT NULL default '0',
 ADD `debug_images` int(11) NOT NULL default '0',
 ADD `debug_sell` int(11) NOT NULL default '0',
 ADD `debug_site` int(11) NOT NULL default '0',
 ADD `debug_affiliate` int(11) NOT NULL default '0',
 ADD `debug_renew` int(11) NOT NULL default '0',
 ADD `debug_bid` int(11) NOT NULL default '0',
 ADD `debug_authenticate` int(11) NOT NULL default '0',
 ADD `debug_modules` int(11) NOT NULL default '0',
 ADD `user_set_auction_end_times` int(1) NOT NULL default '0',
 ADD `user_set_auction_start_times` int(1) NOT NULL default '0',
 ADD `display_before_start` int(11) NOT NULL default '0',
 ADD `auction_extension_check` int(11) NOT NULL default '0',
 ADD `auction_extension` int(11) NOT NULL default '0',
 ADD `black_list_of_buyers` int(11) NOT NULL default '0',
 ADD `invited_list_of_buyers` int(11) NOT NULL default '0',
 ADD `payment_types` tinyint(4) NOT NULL default '0',
 ADD `payment_types_use` int(11) NOT NULL default '0',
 ADD `title_module_text` text NOT NULL default '',
 ADD `buy_now_image` tinytext NOT NULL default '',
 ADD `reserve_met_image` tinytext NOT NULL default '',
 ADD `allow_standard` int(11) NOT NULL default '1',
 ADD `allow_dutch` int(11) NOT NULL default '1',
 ADD `no_reserve_image` tinytext NOT NULL default '',
 ADD `buy_now_reserve` int(11) NOT NULL default '0',
 ADD `edit_begin` int(11) NOT NULL default '0',
 ADD `admin_only_removes_auctions` int(11) NOT NULL default '0',
 ADD `number_format` int(11) NOT NULL default '0';

CREATE TABLE IF NOT EXISTS `geodesic_feedbacks` (
  `rated_user_id` int(11) default NULL,
  `rater_user_id` int(11) default NULL,
  `feedback` tinytext,
  `rate` int(11) default '0',
  `date` int(14) default NULL,
  `auction_id` int(11) NOT NULL default '0',
  `done` tinyint(4) NOT NULL default '0'
);

CREATE TABLE IF NOT EXISTS `geodesic_auctions_increments` (
	`low` double(16,2) default NULL,
	`high` double(16,2) default NULL,
	`increment` double(16,2) default NULL
);
ALTER TABLE `geodesic_classifieds_configuration` ADD `edit_reset_date` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `bid_history_link_live` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `payment_types` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `payment_types_use` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `display_time_left` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `display_number_bids` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `editable_bid_start_time_field` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_price_plans` ADD `charge_percentage_at_auction_end` INT( 11 ) DEFAULT '0' NOT NULL , ADD `roll_final_fee_into_future` INT( 11 ) DEFAULT '0' NOT NULL;
ALTER TABLE `geodesic_classifieds_price_plans` ADD `applies_to` INT( 1 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_group_attached_price_plans` ADD `applies_to` INT( 1 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_groups` ADD `auction_price_plan_id` INT( 11 ) DEFAULT '0' NOT NULL ;
INSERT INTO `geodesic_pages` (page_id,section_id,name,description,maxNodeDepth) VALUES (199,2,'Choose sell type page','Page in place an add process where user chooses what kind of item he is placing.',2);
UPDATE `geodesic_pages_messages` SET `name` = 'Price+Plan+Title' WHERE `message_id` =730 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Price+Plan+Description' WHERE `message_id` =745 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Price+Plan+-+Subscription+Type' WHERE `message_id` =731 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Price+Plan+-+Fee+Type' WHERE `message_id` =732 LIMIT 1 ;
INSERT INTO `geodesic_pages_sections` VALUES (13, 'Client Side Auction Feedback', 'This sub-section allows the user to view feedback about themselves and leave feedback for others.', 4, 0);
INSERT INTO `geodesic_pages_sections` VALUES (14, 'Bid On An Auction', 'This section contains all of the pages allowing the user to place bids on auctions', 0, 6);
ALTER TABLE `geodesic_pages` ADD `email` INT( 11 ) DEFAULT '0' NOT NULL ;
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10157, 13, 'Feedback Home', 'Clients can view their current feedback rating and check if they can leave feedback for someone else.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10158, 13, 'View Feedback About Current Client', 'This page allows the current user to view feedback about themselves.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10159, 13, 'List Open Feedback for Current Client', 'This page displays the current open feedbacks they can leave for other clients they have completed an auction with.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10160, 13, 'Leave Feedback For Another Client', 'This page allows the client to leave feedback for another client that they have completed a transaction with.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10161, 13, 'Feedback Thank You Page', 'This page displays a thank you message to the client once they have successfully left a feedback for another user.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10162, 13, 'Feedback Error Page', 'This displays the error message if there was an error in leaving a feedback for another client.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10163, 14, 'Bid Setup Page', 'This page verifies the clients bid allowing them to enter the amount of their bid', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10164, 14, 'Bid Error Page', 'This page displays the errors with a given bid and gives the client the ability to adjust their bid.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10165, 14, 'Bid Successful Page', 'This page displays the message that the bid was successful and the specifics about the bid', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10166, 14, 'Dutch Bid Successful/Unsuccessful Emails', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10167, 14, 'Buy Now Successful Emails', 'This page displays the messages displayed used within the email sent to successful buy now buyers and the buy now auction sellers.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10168, 14, 'Current High Bidder Email', 'Contains all the text used in the email sent to new high bidders.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10169, 14, 'Outbid Email', 'This contains the text appearing within the outbid email sent to clients when they have been outbid by another client.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10170, 14, 'Auction Specs Used Within Emails', 'This is the text used within the successful bid and outbid emails to label the specs of the auction the email is concerning.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10171, 14, 'Bid History Page', 'This page displays the bid history for a specific auction after it has closed.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10172, 14, 'Ending Email To Seller', 'This controls the text for the email sent to the seller at the end of an auction', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10174, 14, 'Ending Email To High Bidders', 'This controls the email sent at the end of an auction to the highest bidder.  This includes ending messages to dutch bidders.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);

ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `auctions_user_ad_template` INT( 11 ) NOT NULL,
	ADD `auctions_user_extra_template` INT( 11 ) NOT NULL,
	ADD `auctions_user_checkbox_template` INT( 11 ) NOT NULL ;

CREATE TABLE IF NOT EXISTS `geodesic_auctions_blacklisted_users` (
  `seller_id` int(11) NOT NULL default '0',
  `userID` int(11) NOT NULL default '0',
  KEY `seller_id` (`seller_id`),
  KEY `userID` (`userID`)
);

CREATE TABLE IF NOT EXISTS `geodesic_auctions_invited_users` (
  `seller_id` int(11) NOT NULL default '0',
  `userID` int(11) NOT NULL default '0',
  KEY `seller_id` (`seller_id`,`userID`)
);

INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10183, 4, 'Sellers Blacklist', 'This page allows the seller to blacklist buyers from their auctions.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10184, 4, 'Sellers Invited List', 'This page allows the sellers to allow certain buyers access to bid on their auctions.  Only buyers on this list will be able to bid on the sellers auctions.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);
INSERT INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_auction_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_number_bids`, `module_display_time_left`, `module_type`, `email`, `photo_or_icon`) VALUES (10175, 4, 'Users Current Bids Page', 'This page displays the current bids a user has on an auction.  It also displays the success or failure of the bid.', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 2);

ALTER TABLE `geodesic_userdata` ADD `feedback_score` INT( 11 ) DEFAULT '0' NOT NULL , ADD `feedback_count` INT( 11 ) DEFAULT '0' NOT NULL , ADD `feedback_positive_count` INT( 11 ) DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_classifieds_categories_languages` ADD `auction_detail_display_template_id` INT( 11 ) NOT NULL ,
	ADD `auction_detail_extra_display_template_id` INT( 11 ) NOT NULL ,
	ADD `auction_detail_checkbox_display_template_id` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `auction_detail_print_friendly_template` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_categories_languages` ADD `auction_detail_print_friendly_template` INT( 11 ) DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_pages_templates_affiliates` ADD `auctions_display_template_id` INT( 11 ) NOT NULL ,
	ADD `auctions_extra_question_template_id` INT( 11 ) NOT NULL ,
	ADD `auctions_checkbox_question_template_id` INT( 11 ) NOT NULL ;

ALTER TABLE `geodesic_classifieds_sell_session` CHANGE `auction_minimum` `auction_minimum` DECIMAL( 10, 2 ) DEFAULT '0' NOT NULL;
ALTER TABLE `geodesic_classifieds_sell_session` CHANGE `auction_reserve` `auction_reserve` DECIMAL( 10, 2 ) DEFAULT '0' NOT NULL;
ALTER TABLE `geodesic_classifieds_sell_session` CHANGE `auction_buy_now` `auction_buy_now` DECIMAL( 10, 2 ) DEFAULT '0' NOT NULL;

CREATE TABLE IF NOT EXISTS `geodesic_auctions_autobids` (
  `auction_id` int(11) NOT NULL default '0',
  `bidder` int(11) NOT NULL default '0',
  `maxbid` double(10,2) NOT NULL default '0.00',
  `time_of_bid` int(14) NOT NULL default '0',
  `quantity` int(11) NOT NULL default '0'
);

UPDATE `geodesic_pages_modules_sections` SET `name` = 'Featured Modules',
	`description` = 'Modules that are designed to configure display and browsing with respect to featured listings.' WHERE `section_id` = '2' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Newest Modules',
	`description` = 'Modules that are designed to configure display and browsing with respect to newest listings.' WHERE `section_id` = '3' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Miscellaneous Display Modules',
	`description` = 'Modules of various characteristics related to listing display for use on your site.' WHERE `section_id` = '7' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Featured Modules - Level 1',
	`description` = 'Modules for displaying featured listings on your site. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ' WHERE `section_id` = '12' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Featured Modules - Level 2',
	`description` = 'Modules for displaying featured listings on your site that are of a level status 2 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ' WHERE `section_id` = '13' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Featured Modules - Level 3',
	`description` = 'Modules for displaying featured listings on your site that are of a level status 3 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ' WHERE `section_id` = '14' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Featured Modules - Level 4',
	`description` = 'Modules for displaying featured listings on your site that are of a level status 4 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ' WHERE `section_id` = '15' LIMIT 1 ;
UPDATE `geodesic_pages_modules_sections` SET `name` = 'Featured Modules - Level 5',
	`description` = 'Modules for displaying featured listings on your site that are of a level status 2 only. NOTE: If you are not using different levels of featured listings then by default all listings placed are Level 1. ' WHERE `section_id` = '16' LIMIT 1 ;

CREATE TABLE IF NOT EXISTS `geodesic_auctions_feedbacks` (
  `rated_user_id` int(11) default NULL,
  `rater_user_id` int(11) default NULL,
  `feedback` tinytext,
  `rate` int(11) default '0',
  `date` int(14) default NULL,
  `auction_id` int(11) NOT NULL default '0',
  `done` tinyint(4) NOT NULL default '0'
);

CREATE TABLE IF NOT EXISTS `geodesic_auctions_feedback_icons` (
  `filename` varchar(255) NOT NULL default '',
  `icon_num` int(11) NOT NULL default '0',
  `begin` int(11) default NULL,
  `end` int(11) default NULL
);

ALTER TABLE `geodesic_classifieds_sell_session` ADD `start_time` INT( 14 ) NOT NULL;
ALTER TABLE `geodesic_classifieds_sell_session` ADD `end_time` INT( 14 ) NOT NULL ;

ALTER TABLE `geodesic_pages` CHANGE `module_number_of_ads_to_display` `module_number_of_ads_to_display` TINYINT( 4 ) DEFAULT '10' NOT NULL;
ALTER TABLE `geodesic_pages` ADD `module_display_type_listing` INT NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `module_display_type_text` INT NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `module_display_listing_column` INT NOT NULL ;
ALTER TABLE `geodesic_classifieds_expired` ADD `auction_type` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_expired` ADD `final_fee` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_expired` ADD `final_fee_transaction_number` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_expired` ADD `final_price` DOUBLE( 7, 2 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_expired` ADD `high_bidder` INT( 11 ) NOT NULL;
ALTER TABLE `geodesic_userdata` ADD `feedback_icon` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `geodesic_classifieds_sell_session` ADD `final_fee` INT( 11 ) NOT NULL ;
ALTER TABLE `geodesic_categories` ADD `use_buy_now` INT ( 11 ) NOT NULL default '0' ;
ALTER TABLE `geodesic_categories` ADD `payment_types` INT ( 11 ) NOT NULL default '0' ;
ALTER TABLE `geodesic_categories` ADD `editable_bid_start_time_field` INT ( 11 ) NOT NULL default '0' ;
ALTER TABLE `geodesic_classifieds_configuration` 	ADD `site_on_off` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration`	ADD `disable_site_url` TINYTEXT NOT NULL ;

CREATE TABLE IF NOT EXISTS `geodesic_auctions_final_fee_price_increments` (
  `price_plan_id` int(11) NOT NULL default '0',
  `low` double(16,2) default NULL,
  `high` double(16,2) default NULL,
  `charge` double(16,2) default NULL
);

DROP TABLE `geodesic_classifieds_final_fee_price_increments`;

ALTER TABLE `geodesic_categories` ADD `auction_category_count` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `browsing_count_format` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `browsing_count_format` INT( 11 ) DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_classifieds_configuration` ADD `time_shift` INT( 2 ) DEFAULT '0' NOT NULL ;

UPDATE `geodesic_pages` SET `name` = 'Listing Display Page' WHERE `page_id` = '1' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Listing Detail Collection' WHERE `page_id` = '9' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Listing Approval' WHERE `page_id` = '11' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Users Current Listings' WHERE `page_id` = '22' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Users Expired Listings' WHERE `page_id` = '23' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Current Filters Page' WHERE `page_id` = '27' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Add New Filter Form' WHERE `page_id` = '28' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Filter Match Email' WHERE `page_id` = '29' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Edit Listings Home' WHERE `page_id` = '31' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Edit Listings Details' WHERE `page_id` = '32' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Edit Listing Images' WHERE `page_id` = '33' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Edit Listing Category' WHERE `page_id` = '34' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'View Expired Listing Detail' WHERE `page_id` = '35' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Verify Listing Removal Page' WHERE `page_id` = '36' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 1' WHERE `page_id` = '46' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 2 - All Categories' WHERE `page_id` = '47' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 3' WHERE `page_id` = '48' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 4' WHERE `page_id` = '49' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 5' WHERE `page_id` = '50' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Successful Listing Placement Email' WHERE `page_id` = '51' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Listing Expires Soon Email' WHERE `page_id` = '52' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Sellers Other Listings' WHERE `page_id` = '55' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Renew/Upgrade a Listing' WHERE `page_id` = '56' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Approve Listing Upgrade/Renewal' WHERE `page_id` = '57' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Newest Listings 1' WHERE `page_id` = '60' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Newest Listings 2' WHERE `page_id` = '61' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Browse Featured Listings by Picture' WHERE `page_id` = '62' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Browse Featured Listings Text Only' WHERE `page_id` = '63' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Newest Listings Page' WHERE `page_id` = '64' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Newest in Last 24 hrs' WHERE `page_id` = '66' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Featured Picture Listings Page ' WHERE `page_id` = '67' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Featured Text Listings' WHERE `page_id` = '68' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Printer Friendly Display' WHERE `page_id` = '69' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Newest in Last Week' WHERE `page_id` = '78' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Newest in Last 2 Weeks' WHERE `page_id` = '79' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Newest in Last 3 Weeks' WHERE `page_id` = '80' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Full Size Image Display Page' WHERE `page_id` = '84' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 1' WHERE `page_id` = '89' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 2' WHERE `page_id` = '90' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Category Navigation' WHERE `page_id` = '94' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Category Navigation 2' WHERE `page_id` = '95' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Category Navigation 3' WHERE `page_id` = '96' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Main Category Navigation 1' WHERE `page_id` = '100' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 3' WHERE `page_id` = '102' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Voting Form' WHERE `page_id` = '116' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Voting Comments View' WHERE `page_id` = '115' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 1' WHERE `page_id` = '117' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 2' WHERE `page_id` = '118' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 1' WHERE `page_id` = '119' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 2' WHERE `page_id` = '120' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 1' WHERE `page_id` = '121' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 2' WHERE `page_id` = '122' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 1' WHERE `page_id` = '123' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - Pic Display - 2' WHERE `page_id` = '124' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 1' WHERE `page_id` = '125' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 2' WHERE `page_id` = '126' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 1' WHERE `page_id` = '127' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 2' WHERE `page_id` = '128' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 1' WHERE `page_id` = '129' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 2' WHERE `page_id` = '130' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 1' WHERE `page_id` = '131' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module - 2' WHERE `page_id` = '132' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Hottest Listings Module' WHERE `page_id` = '172' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module 1 - Specific Category Only' WHERE `page_id` = '155' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Featured Listing Module 2 - Specific Category Only' WHERE `page_id` = '156' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Choose Listing Type Page' WHERE `page_id` = '199' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Link to Search (category dynamic)' WHERE `page_id` = '88' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Filter Dropdown Display 1' WHERE `page_id` = '91' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Filter Dropdown Display 2' WHERE `page_id` = '101' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Display Logged In/Out HTML - 1' WHERE `page_id` = '75' LIMIT 1 ;

UPDATE `geodesic_pages_sections` SET `name` = 'Browsing Listings' WHERE `section_id` = '1' LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Place New Listing' WHERE `section_id` = '2' LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Users Filter Sub-Section' WHERE `section_id` = '8' LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Users Expired Listings Sub-Section' WHERE `section_id` = '9' LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Users Current Listings' WHERE `section_id` = '10' LIMIT 1 ;

DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2551' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2552' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2553' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2554' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2555' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2556' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '2557' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3066' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3067' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3068' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3069' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3070' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3071' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3072' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3073' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3074' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3075' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3076' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3077' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3078' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3079' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3080' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3081' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3082' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3083' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3084' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3085' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3086' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3087' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3088' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3089' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3090' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3091' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3092' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3093' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3094' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3095' LIMIT 1;
DELETE FROM `geodesic_pages_messages` WHERE `message_id` = '3096' LIMIT 1;

DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2551' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2552' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2553' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2554' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2555' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2556' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '2557' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '9' AND `text_id` = '3066' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '9' AND `text_id` = '3067' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '9' AND `text_id` = '3068' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '9' AND `text_id` = '3069' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '9' AND `text_id` = '3070' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '9' AND `text_id` = '3071' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '11' AND `text_id` = '3072' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '11' AND `text_id` = '3073' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3074' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3075' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3076' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3077' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3078' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3079' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3080' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3081' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3082' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3083' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3084' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3085' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3086' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3087' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3088' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3089' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3090' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '176' AND `text_id` = '3091' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3092' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3093' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '11' AND `text_id` = '3094' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '12' AND `text_id` = '3095' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '13' AND `text_id` = '3096' LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE text_id = 1813;
DELETE FROM `geodesic_pages_messages` WHERE message_id = 1813;

DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1545' LIMIT 1;
DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1546' LIMIT 1;
DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1547' LIMIT 1;
DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1548' LIMIT 1;
DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1549' LIMIT 1;
DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1550' LIMIT 1;
DELETE FROM `geodesic_pages_fonts` WHERE `element_id` = '1617' LIMIT 1;

DELETE FROM `geodesic_pages` WHERE `page_id` = '176' LIMIT 1;

ALTER TABLE `geodesic_classifieds_price_plans` CHANGE `credits_upon_registration` `credits_upon_registration` INT( 11 ) NOT NULL DEFAULT '0';

UPDATE `geodesic_pages` SET `name` = 'Success/Failure Page' WHERE `page_id` = '14' LIMIT 1 ;
UPDATE `geodesic_pages` SET `name` = 'Renew Subscription Success/Failure Page' WHERE `page_id` = '109' LIMIT 1 ;
ALTER TABLE `geodesic_balance_transactions` ADD `final_fee` INT( 11 ) DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_pages` CHANGE `module_number_of_ads_to_display` `module_number_of_ads_to_display` INT( 11 ) NOT NULL DEFAULT '0' ;

ALTER TABLE `geodesic_classifieds_price_plans` CHANGE `initial_site_balance` `initial_site_balance` DOUBLE( 5, 2 ) NOT NULL DEFAULT '0' ;


ALTER TABLE `geodesic_classifieds_configuration` ADD `auction_entry_date` INT( 11 ) NOT NULL DEFAULT '0' ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `classified_time_left` INT( 11 ) NOT NULL DEFAULT '0' ;

UPDATE `geodesic_classifieds_price_plans` SET `charge_per_ad_type` = '0' WHERE `applies_to` = 2;

UPDATE geodesic_pages_messages SET page_id = 4 WHERE page_id = 10004;
UPDATE geodesic_pages_messages SET page_id = 69 WHERE page_id = 10069;
UPDATE geodesic_pages_messages SET page_id = 9 WHERE page_id = 10009;
UPDATE geodesic_pages_messages SET page_id = 6 WHERE page_id = 10006;
UPDATE geodesic_pages_messages SET page_id = 11 WHERE page_id = 10011;
UPDATE geodesic_pages_messages SET page_id = 23 WHERE page_id = 10023;
UPDATE geodesic_pages_messages SET page_id = 8 WHERE page_id = 10008;
UPDATE geodesic_pages_messages SET page_id = 15 WHERE page_id = 10015;
UPDATE geodesic_pages_messages SET page_id = 12 WHERE page_id = 10012;
UPDATE geodesic_pages_messages SET page_id = 35 WHERE page_id = 10035;
UPDATE geodesic_pages_messages_languages SET page_id = 69 WHERE page_id = 10069;
UPDATE geodesic_pages_messages_languages SET page_id = 6 WHERE page_id = 10006;
UPDATE geodesic_pages_messages_languages SET page_id = 11 WHERE page_id = 10011;
UPDATE geodesic_pages_messages_languages SET page_id = 23 WHERE page_id = 10023;
UPDATE geodesic_pages_messages_languages SET page_id = 8 WHERE page_id = 10008;
UPDATE geodesic_pages_messages_languages SET page_id = 15 WHERE page_id = 10015;
UPDATE geodesic_pages_messages_languages SET page_id = 12 WHERE page_id = 10012;
UPDATE geodesic_pages_messages_languages SET page_id = 35 WHERE page_id = 10035;

ALTER TABLE `geodesic_classifieds_configuration` ADD `listing_type_allowed` INT DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_classifieds_expired` ADD `item_type` INT( 11 ) NOT NULL ;


UPDATE `geodesic_pages_fonts` SET `element` = 'final_fee_header', `name` = 'Final Fee Header' WHERE `element_id` =444 LIMIT 1 ;

ALTER TABLE `geodesic_classifieds_configuration` ADD `url_rewrite` TINYINT NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration`
	ADD `optional_field_1_name` VARCHAR( 50 ) DEFAULT 'Optional Field 1' NOT NULL ,
	ADD `optional_field_2_name` VARCHAR( 50 ) DEFAULT 'Optional Field 2' NOT NULL ,
	ADD `optional_field_3_name` VARCHAR( 50 ) DEFAULT 'Optional Field 3' NOT NULL ,
	ADD `optional_field_4_name` VARCHAR( 50 ) DEFAULT 'Optional Field 4' NOT NULL ,
	ADD `optional_field_5_name` VARCHAR( 50 ) DEFAULT 'Optional Field 5' NOT NULL ,
	ADD `optional_field_6_name` VARCHAR( 50 ) DEFAULT 'Optional Field 6' NOT NULL ,
	ADD `optional_field_7_name` VARCHAR( 50 ) DEFAULT 'Optional Field 7' NOT NULL ,
	ADD `optional_field_8_name` VARCHAR( 50 ) DEFAULT 'Optional Field 8' NOT NULL ,
	ADD `optional_field_9_name` VARCHAR( 50 ) DEFAULT 'Optional Field 9' NOT NULL ,
	ADD `optional_field_10_name` VARCHAR( 50 ) DEFAULT 'Optional Field 10' NOT NULL ,
	ADD `optional_field_11_name` VARCHAR( 50 ) DEFAULT 'Optional Field 11' NOT NULL ,
	ADD `optional_field_12_name` VARCHAR( 50 ) DEFAULT 'Optional Field 12' NOT NULL ,
	ADD `optional_field_13_name` VARCHAR( 50 ) DEFAULT 'Optional Field 13' NOT NULL ,
	ADD `optional_field_14_name` VARCHAR( 50 ) DEFAULT 'Optional Field 14' NOT NULL ,
	ADD `optional_field_15_name` VARCHAR( 50 ) DEFAULT 'Optional Field 15' NOT NULL ,
	ADD `optional_field_16_name` VARCHAR( 50 ) DEFAULT 'Optional Field 16' NOT NULL ,
	ADD `optional_field_17_name` VARCHAR( 50 ) DEFAULT 'Optional Field 17' NOT NULL ,
	ADD `optional_field_18_name` VARCHAR( 50 ) DEFAULT 'Optional Field 18' NOT NULL ,
	ADD `optional_field_19_name` VARCHAR( 50 ) DEFAULT 'Optional Field 19' NOT NULL ,
	ADD `optional_field_20_name` VARCHAR( 50 ) DEFAULT 'Optional Field 20' NOT NULL ;
ALTER TABLE `geodesic_registration_configuration`
	ADD `registration_optional_1_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 1' NOT NULL ,
	ADD `registration_optional_2_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 2' NOT NULL ,
	ADD `registration_optional_3_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 3' NOT NULL ,
	ADD `registration_optional_4_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 4' NOT NULL ,
	ADD `registration_optional_5_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 5' NOT NULL ,
	ADD `registration_optional_6_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 6' NOT NULL ,
	ADD `registration_optional_7_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 7' NOT NULL ,
	ADD `registration_optional_8_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 8' NOT NULL ,
	ADD `registration_optional_9_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 9' NOT NULL ,
	ADD `registration_optional_10_field_name` VARCHAR( 50 ) DEFAULT 'Reg Optional Field 10' NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `admin_label` VARCHAR( 50 ) NOT NULL ;
ALTER TABLE `geodesic_states`
	ADD `tax` DOUBLE( 4, 4 ) DEFAULT '0' NOT NULL ,
	ADD `tax_type` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_countries`
	ADD `tax` DOUBLE( 4, 4 ) DEFAULT '0' NOT NULL ,
	ADD `tax_type` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_classifieds_images_urls` ADD `mime_type` VARCHAR( 30 ) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds_images` ADD `mime_type` VARCHAR( 30 ) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_sessions` CHANGE `affiliate_id` `affiliate_id` tinytext NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds_configuration` ADD `default_display_order_while_browsing` INT( 11 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_categories` ADD `default_display_order_while_browsing_category` INT DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_states` CHANGE `tax` `tax` DOUBLE NOT NULL DEFAULT '0.0000' ;
ALTER TABLE `geodesic_countries` CHANGE `tax` `tax` DOUBLE NOT NULL DEFAULT '0.0000' ;

CREATE TABLE IF NOT EXISTS `geodesic_banned_ips` (
	`ip_id` INT NOT NULL AUTO_INCREMENT ,
	`ip` VARCHAR( 25 ) NOT NULL ,
	PRIMARY KEY ( `ip_id` )
);

ALTER TABLE `geodesic_classifieds_configuration` ADD `ip_ban_check` INT( 11 ) DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_pages_sections` ADD `applies_to` INT( 11 ) NOT NULL DEFAULT '0';
UPDATE `geodesic_pages_sections` SET `applies_to` = '2' WHERE `section_id` = 13 LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `applies_to` = '2' WHERE `section_id` = 14 LIMIT 1 ;

ALTER TABLE `geodesic_pages` ADD `applies_to` INT( 11 ) NOT NULL DEFAULT '0';
UPDATE `geodesic_pages` SET `applies_to` = '2' WHERE `section_id` = 13;
UPDATE `geodesic_pages` SET `applies_to` = '2' WHERE `section_id` = 14;
UPDATE `geodesic_pages` SET `applies_to` = '2' WHERE `page_id` = 10175 LIMIT 1 ;
UPDATE `geodesic_pages` SET `applies_to` = '4' WHERE `page_id` = 199 LIMIT 1 ;
UPDATE `geodesic_pages` SET `applies_to` = '2' WHERE `page_id` = 10183 LIMIT 1 ;
UPDATE `geodesic_pages` SET `applies_to` = '2' WHERE `page_id` = 10184 LIMIT 1 ;

DELETE FROM `geodesic_pages_messages` WHERE `message_id` = 9 LIMIT 1;
DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = 1 AND `text_id` = 9 LIMIT 1;

UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 1 Label' WHERE `message_id` = 1588 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 2 Label' WHERE `message_id` = 1589 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 3 Label' WHERE `message_id` = 1590 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 4 Label' WHERE `message_id` = 1591 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 5 Label' WHERE `message_id` = 1592 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 6 Label' WHERE `message_id` = 1593 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 7 Label' WHERE `message_id` = 1594 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 8 Label' WHERE `message_id` = 1595 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 9 Label' WHERE `message_id` = 1596 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Exposed Registration Optional Field 10 Label' WHERE `message_id` = 1597 LIMIT 1 ;

UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 1 Label' WHERE `message_id` = 1965 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 2 Label' WHERE `message_id` = 1966 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 3 Label' WHERE `message_id` = 1967 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 4 Label' WHERE `message_id` = 1968 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 5 Label' WHERE `message_id` = 1969 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 6 Label' WHERE `message_id` = 1970 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 7 Label' WHERE `message_id` = 1971 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 8 Label' WHERE `message_id` = 1972 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 9 Label' WHERE `message_id` = 1973 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'Registration Optional Field 10 Label' WHERE `message_id` = 1974 LIMIT 1 ;
ALTER TABLE `geodesic_cc_manual_transactions` ADD `cvv2_code` VARCHAR( 4 ) NOT NULL ;

ALTER TABLE `geodesic_categories` ADD `listing_types_allowed` INT( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE `geodesic_pages` ADD `extra_page_text` TEXT NOT NULL ;

ALTER TABLE `geodesic_templates` ADD `applies_to` INT NOT NULL DEFAULT '0';

ALTER TABLE `geodesic_classifieds_configuration` ADD `number_of_feedbacks_to_display` INT( 11 ) DEFAULT '1' NOT NULL ;

DELETE FROM `geodesic_pages_fonts` WHERE element_id = 385;
DELETE FROM `geodesic_pages_fonts` WHERE element_id = 378;
DELETE FROM `geodesic_pages_fonts` WHERE element_id = 440;
DELETE FROM `geodesic_pages_fonts` WHERE element_id = 562;

ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `clientside_image_uploader_view` TINYINT NOT NULL;
ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `image_uploader_default` TINYINT NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `maxNodeDepth` INT NOT NULL ;
ALTER TABLE `geodesic_sessions` ADD `securityString` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `maxNodeDepth` INT NOT NULL ;
ALTER TABLE `geodesic_pages` CHANGE `extra_page_text` `extra_page_text` LONGTEXT NOT NULL ;
ALTER TABLE `geodesic_classifieds_configuration` ADD `member_since_date_configuration` VARCHAR( 20 ) DEFAULT 'F Y' NOT NULL ;
ALTER TABLE `geodesic_pages` ADD `module_display_company_name` INT( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE `geodesic_classifieds_configuration` ADD `popup_image_while_browsing` INT( 11 ) NOT NULL;
ALTER TABLE `geodesic_cc_twocheckout_transactions` CHANGE `address` `street_address` TINYTEXT NOT NULL;

UPDATE `geodesic_pages_messages` SET `page_id` = '12' WHERE (
	`message_id`='103064' OR
	`message_id`='103065' OR
	`message_id`='103066' OR
	`message_id`='103067' OR
	`message_id`='103068')
	AND `page_id` = '23' ;
UPDATE `geodesic_pages_messages_languages` SET `page_id` = '12' WHERE (
	`text_id`='103064' OR
	`text_id`='103065' OR
	`text_id`='103066' OR
	`text_id`='103067' OR
	`text_id`='103068')
	AND `page_id` = '23' ;

ALTER TABLE `geodesic_classifieds_sell_session` ADD `auction_price_plan_id` INT( 11 ) NOT NULL ;
        
ALTER TABLE `geodesic_classifieds_configuration` CHANGE `length_of_description` `length_of_description` INT( 11 ) NOT NULL DEFAULT '0';

INSERT INTO `geodesic_html_allowed` VALUES (60, 'P', 0, 1, '', 1, 0);

ALTER TABLE `geodesic_choices` ADD `language_id` INT( 11 ) NOT NULL DEFAULT '1';
UPDATE `geodesic_pages_messages` SET `page_id` = '10172' WHERE `message_id` =102777 LIMIT 1 ;
UPDATE `geodesic_pages_messages_languages` SET `page_id` = '10172' WHERE `page_id` =10166 AND `text_id` =102777 LIMIT 1 ;
UPDATE `geodesic_payment_choices` SET `explanation` = 'This payment type comes with two settings. You can either require your users to pay for each listing, renewal, etc. through a running account balance (Account Balance System) that they have with your site, or they can pay for their accumulated listings, renewals, etc. through an invoice that is generated by this software (Invoice Balance System) when you manually activate the invoicing process.' WHERE `payment_choice_id` =7 LIMIT 1 ;
UPDATE `geodesic_payment_choices` SET `name` = 'Site Balance System' WHERE `payment_choice_id` =7 LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Listing Process',
	`description` = 'This section includes all the pages displayed when a registered user places a listing.' WHERE `section_id` =2 LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Extra Pages',
	`description` = 'This section contains the extra pages you can use for site supporting documents but still take advantage of module placement' WHERE `section_id` =12 LIMIT 1 ;
UPDATE `geodesic_pages_sections` SET `name` = 'Bidding',
	`description` = 'This section contains all of the pages allowing the user to place bids on auctions.' WHERE `section_id` =14 LIMIT 1 ;

ALTER TABLE `geodesic_pages` ADD `module_display_sub_category_nav_links` TINYINT( 1 ) NOT NULL DEFAULT 0,
	ADD `module_sub_category_nav_prefix` VARCHAR( 64 ) NULL ,
	ADD `module_sub_category_nav_separator` VARCHAR( 64 ) NULL DEFAULT ', ',
	ADD `module_sub_category_nav_surrounding` VARCHAR( 128 ) NULL DEFAULT ' &nbsp; >> sub|cat|list ';

ALTER TABLE `geodesic_classifieds_sell_session` ADD `buy_now_only` INT NOT NULL ;
ALTER TABLE `geodesic_currency_types` ADD `conversion_rate` DOUBLE NOT NULL DEFAULT '1' AFTER `postcurrency` ;
ALTER TABLE `geodesic_classifieds` ADD `conversion_rate` DOUBLE NOT NULL DEFAULT '1' AFTER `postcurrency` ;
ALTER TABLE `geodesic_classifieds_expired` ADD `conversion_rate` DOUBLE NOT NULL DEFAULT '1' AFTER `postcurrency` ;

DELETE FROM `geodesic_pages_messages` WHERE `message_id` = 129 LIMIT 1;

DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = 9 AND `text_id` = 129 LIMIT 1;

UPDATE `geodesic_pages` SET `name` = 'Registration Information Collection/Cancellation Page' WHERE `page_id` = 15 LIMIT 1;

CREATE TABLE IF NOT EXISTS `geodesic_site_settings_long` (
	`setting` VARCHAR( 255 ) NOT NULL ,
	`value` TEXT NOT NULL ,
	PRIMARY KEY ( `setting` )
);

ALTER TABLE `geodesic_classifieds_categories_languages` ADD `search_template_id` INT NOT NULL DEFAULT '0';




ALTER TABLE `geodesic_userdata` ADD `storefront_header` TEXT NOT NULL ,
		ADD `storefront_template_id` INT NOT NULL ,
		ADD `storefront_welcome_message` TEXT NOT NULL ,
		ADD `storefront_on_hold` INT NOT NULL default '1';

ALTER TABLE `geodesic_templates` ADD `storefront_template` TINYINT NOT NULL ,
	ADD `storefront_template_default` TINYINT NOT NULL ;

ALTER TABLE `geodesic_categories` ADD `display_storefront_link` INT NOT NULL ;

ALTER TABLE `geodesic_groups` ADD `storefront` INT DEFAULT '0' NOT NULL ;

CREATE TABLE IF NOT EXISTS `geodesic_storefront_pages` (
	`page_id` INT NOT NULL AUTO_INCREMENT ,
	`user_id` INT NOT NULL ,
	`page_link_text` TEXT NOT NULL ,
	`page_name` TEXT NOT NULL ,
	`page_body` TEXT NOT NULL ,
	PRIMARY KEY ( `page_id` )
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_categories` (
	`category_id` INT NOT NULL AUTO_INCREMENT ,
	`user_id` INT NOT NULL ,
	`category_name` TEXT NOT NULL ,
	`display_order` INT NOT NULL ,
	PRIMARY KEY ( `category_id` )
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_users` (
	`store_id` INT NOT NULL ,
	`user_id` INT NOT NULL
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_subscriptions` (
	`subscription_id` INT NOT NULL AUTO_INCREMENT ,
	`expiration` INT NOT NULL ,
	`user_id` INT NOT NULL ,
	PRIMARY KEY ( `subscription_id` )
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_subscriptions_choices` (
	`period_id` int(14) NOT NULL auto_increment,
	`display_value` tinytext NOT NULL,
	`value` int(11) NOT NULL default '0',
	`amount` double(5,2) NOT NULL default '0.00',
	PRIMARY KEY  (`period_id`),
	KEY `id` (`period_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_group_subscriptions_choices` (
	`group_id` INT NOT NULL ,
	`choice_id` INT NOT NULL
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_display` (
	`display_business_type` int(11) NOT NULL default '0',
	`use_site_default` int(11) NOT NULL default '0',
	`display_photo_icon` int(11) NOT NULL default '0',
	`display_price` int(11) NOT NULL default '0',
	`display_browsing_zip_field` int(11) NOT NULL default '0',
	`display_browsing_city_field` int(11) NOT NULL default '0',
	`display_browsing_state_field` int(11) NOT NULL default '0',
	`display_browsing_country_field` int(11) NOT NULL default '0',
	`display_entry_date` int(11) NOT NULL default '0',
	`display_optional_field_1` int(11) NOT NULL default '0',
	`display_optional_field_2` int(11) NOT NULL default '0',
	`display_optional_field_3` int(11) NOT NULL default '0',
	`display_optional_field_4` int(11) NOT NULL default '0',
	`display_optional_field_5` int(11) NOT NULL default '0',
	`display_optional_field_6` int(11) NOT NULL default '0',
	`display_optional_field_7` int(11) NOT NULL default '0',
	`display_optional_field_8` int(11) NOT NULL default '0',
	`display_optional_field_9` int(11) NOT NULL default '0',
	`display_optional_field_10` int(11) NOT NULL default '0',
	`display_optional_field_11` int(11) NOT NULL default '0',
	`display_optional_field_12` int(11) NOT NULL default '0',
	`display_optional_field_13` int(11) NOT NULL default '0',
	`display_optional_field_14` int(11) NOT NULL default '0',
	`display_optional_field_15` int(11) NOT NULL default '0',
	`display_optional_field_16` int(11) NOT NULL default '0',
	`display_optional_field_17` int(11) NOT NULL default '0',
	`display_optional_field_18` int(11) NOT NULL default '0',
	`display_optional_field_19` int(11) NOT NULL default '0',
	`display_optional_field_20` int(11) NOT NULL default '0',
	`display_ad_description` int(11) NOT NULL default '0',
	`display_ad_description_where` int(11) NOT NULL default '0',
	`display_all_of_description` int(11) NOT NULL default '0',
	`display_ad_title` int(11) NOT NULL default '1',
	`display_number_bids` int(11) NOT NULL default '0',
	`display_time_left` int(11) NOT NULL default '0'
);

ALTER TABLE `geodesic_classifieds` ADD `storefront_category` INT DEFAULT '0' NOT NULL ;

ALTER TABLE `geodesic_classifieds_sell_session` ADD `storefront` TINYINT NOT NULL ;

ALTER TABLE `geodesic_classifieds_user_subscriptions_holds` ADD `storefront` TINYINT NOT NULL ;

ALTER TABLE `geodesic_paypal_transactions` ADD `storefront` TINYINT NOT NULL ;

ALTER TABLE `geodesic_classifieds_sell_session` ADD `storefront_category` INT NOT NULL ;

ALTER TABLE `geodesic_classifieds_configuration` ADD `display_storefront_link` INT NOT NULL ;

ALTER TABLE `geodesic_classifieds_configuration` ADD `storefront_url` TINYTEXT NOT NULL ;

ALTER TABLE `geodesic_userdata` ADD `storefront_home_link` VARCHAR( 255 ) NOT NULL ;

CREATE TABLE IF NOT EXISTS `geodesic_storefront_template_modules` (
	`module_id` INT NOT NULL ,
	`template_id` INT NOT NULL ,
	`connection_time` INT NOT NULL
);

ALTER TABLE `geodesic_storefront_subscriptions` ADD `onholdStartTime` INT NOT NULL ;
ALTER TABLE `geodesic_storefront_display` ADD `auction_entry_date` TINYINT NOT NULL ,
	ADD `classified_time_left` TINYINT NOT NULL ;
ALTER TABLE `geodesic_storefront_users` CHANGE `user_id` `user_email` VARCHAR( 255 ) DEFAULT '0' NOT NULL ;
ALTER TABLE `geodesic_userdata` ADD `storefront_traffic_processed_at` INT NOT NULL ;
CREATE TABLE IF NOT EXISTS `geodesic_storefront_traffic_cache` (
	`logId` INT NOT NULL AUTO_INCREMENT ,
	`storeId` INT NOT NULL ,
	`ip` VARCHAR( 16 ) NOT NULL ,
	`time` INT NOT NULL ,
	PRIMARY KEY ( `logId` )
);
CREATE TABLE IF NOT EXISTS `geodesic_storefront_traffic_dailyreport` (
	`logId` INT NOT NULL AUTO_INCREMENT ,
	`storeId` INT NOT NULL ,
	`time` INT NOT NULL ,
	`uVisits` INT NOT NULL ,
	`tVisits` INT NOT NULL ,
	PRIMARY KEY ( `logId` )
);



CREATE TABLE IF NOT EXISTS `geodesic_bulk_uploader_profiles` (
	profile_id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	profile_name VARCHAR(255) NOT NULL,
	profile_description TEXT NOT NULL,
	user_id INTEGER UNSIGNED NOT NULL,
	listing_type INTEGER UNSIGNED NOT NULL,
	category_id INTEGER UNSIGNED NOT NULL,
	delimeter VARCHAR(5) NOT NULL,
	encapsulation VARCHAR(5) NOT NULL,
	duration_type VARCHAR(16) NOT NULL,
	fixed_duration_value INTEGER NOT NULL,
	prevalues TEXT NOT NULL,
	title_fields TEXT NOT NULL,
	display_size INTEGER UNSIGNED NOT NULL,
	field_mappings TEXT NOT NULL,
	PRIMARY KEY(profile_id)
);
ALTER TABLE `geodesic_sessions` ADD `bulk_upload_text` TEXT NOT NULL ;
ALTER TABLE `geodesic_sessions` ADD `bulk_upload_listing_id_list` TEXT NOT NULL ;
ALTER TABLE `geodesic_userdata`	ADD `last_login_time` DATETIME NOT NULL;
ALTER TABLE `geodesic_userdata` ADD `last_login_ip` VARCHAR(15) NULL ;
CREATE TABLE IF NOT EXISTS `geodesic_bulk_uploader_log` (
	`log_id` int(10) unsigned NOT NULL auto_increment,
	`listing_id_list` text NOT NULL,
	`user_id_list` text NOT NULL,
	`insert_time` int(10) unsigned NOT NULL default '0',
	PRIMARY KEY  (`log_id`)
);
CREATE TABLE IF NOT EXISTS `geodesic_bulk_uploader_session` (
	`id` varchar(32) NOT NULL default '',
	`name` varchar(32) NOT NULL default '',
	`value` text NOT NULL,
	`vid` int(11) NOT NULL default '0',
	`time` int(11) NOT NULL default '0',
	KEY `id` (`id`)
);


CREATE TABLE IF NOT EXISTS geodesic_api_installation_info (
	installation_id int(11) NOT NULL auto_increment,
	installation_name tinytext NOT NULL,
	db_host tinytext NOT NULL,
	db_username tinytext NOT NULL,
	db_password tinytext NOT NULL,
	db_name tinytext NOT NULL,
	installation_type int(11) NOT NULL default '0',
	active int(11) NOT NULL default '1',
	admin_email tinytext NOT NULL,
	synchronous_login int(11) NOT NULL default '0',
	vbulletin_config_path tinytext NOT NULL,
	cookie_path tinytext NOT NULL,
	cookie_domain tinytext NOT NULL,
	phorum_database_table_prefix tinytext NOT NULL,
	vbulletin_license_key tinytext NOT NULL,
	cerberus_publicgui_path tinytext NOT NULL,
	cerberus_directory_path tinytext NOT NULL,
	PRIMARY KEY  (installation_id)
);

UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 1 label' WHERE `message_id` =1241 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 2 label' WHERE `message_id` =1242 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 3 label' WHERE `message_id` =1243 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 4 label' WHERE `message_id` =1244 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 5 label' WHERE `message_id` =1245 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 6 label' WHERE `message_id` =1246 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 7 label' WHERE `message_id` =1247 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 8 label' WHERE `message_id` =1248 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 9 label' WHERE `message_id` =1249 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 10 label' WHERE `message_id` =1250 LIMIT 1 ;
		
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 1 label' WHERE `message_id` =1251 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 2 label' WHERE `message_id` =1252 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 3 label' WHERE `message_id` =1253 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 4 label' WHERE `message_id` =1254 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 5 label' WHERE `message_id` =1255 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 6 label' WHERE `message_id` =1256 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 7 label' WHERE `message_id` =1257 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 8 label' WHERE `message_id` =1258 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 9 label' WHERE `message_id` =1259 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 10 label' WHERE `message_id` =1260 LIMIT 1 ;

UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 1 label' WHERE `message_id` =1251 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 2 label' WHERE `message_id` =1252 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 3 label' WHERE `message_id` =1253 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 4 label' WHERE `message_id` =1254 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 5 label' WHERE `message_id` =1255 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 6 label' WHERE `message_id` =1256 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 7 label' WHERE `message_id` =1257 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 8 label' WHERE `message_id` =1258 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 9 label' WHERE `message_id` =1259 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 10 label' WHERE `message_id` =1260 LIMIT 1 ;

UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 1 error message' WHERE `message_id` =1266 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 2 error message' WHERE `message_id` =1267 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 3 error message' WHERE `message_id` =1268 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 4 error message' WHERE `message_id` =1269 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 5 error message' WHERE `message_id` =1270 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 6 error message' WHERE `message_id` =1271 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 7 error message' WHERE `message_id` =1272 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 8 error message' WHERE `message_id` =1273 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 9 error message' WHERE `message_id` =1274 LIMIT 1 ;
UPDATE `geodesic_pages_messages` SET `name` = 'registration optional field 10 error message' WHERE `message_id` =1275 LIMIT 1 ;

ALTER TABLE `geodesic_classifieds_subscription_choices` CHANGE `amount` `amount` DOUBLE( 10, 2 ) NOT NULL DEFAULT '0.00';

	CREATE TABLE IF NOT EXISTS `geodesic_email_queue` (
			`email_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
			`to` VARCHAR( 64 ) NOT NULL ,
			`subject` VARCHAR( 128 ) NOT NULL ,
			`content` TEXT NULL ,
			`from` VARCHAR( 64 ) NULL ,
			`replyto` VARCHAR( 64 ) NULL ,
			`type` VARCHAR( 64 ) NOT NULL ,
			`status` ENUM( 'sent', 'not_sent', 'error' ) NOT NULL ,
			INDEX ( `email_id` )
	);
	ALTER TABLE `geodesic_classifieds_expired` CHANGE `high_bidder` `high_bidder` VARCHAR( 64 ) NOT NULL DEFAULT '0';
	ALTER TABLE `geodesic_classifieds` CHANGE `high_bidder` `high_bidder` VARCHAR( 64 ) NOT NULL DEFAULT '0';
	ALTER TABLE `geodesic_sessions` CHANGE `ip` `ip` VARCHAR( 40 ) NOT NULL;
	ALTER TABLE `geodesic_sessions` ADD PRIMARY KEY ( `classified_session` ) ;
	DELETE FROM `geodesic_pages_modules` WHERE `module_id`=114 AND `page_id`=199 ;
	
	ALTER TABLE `geodesic_classifieds_expired` ADD COLUMN hide int(1) NULL DEFAULT '0';
	ALTER TABLE `geodesic_classifieds` ADD COLUMN hide int(1) NULL DEFAULT '0';
	ALTER TABLE `geodesic_classifieds` ADD COLUMN reason_ad_ended varchar(255) NULL DEFAULT '';
