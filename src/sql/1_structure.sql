-- STRUCTURE SQL for all GEO CA Tables

--
-- Table structure for table `geodesic_addons`
--

DROP TABLE IF EXISTS `geodesic_addons`;
CREATE TABLE IF NOT EXISTS `geodesic_addons` (
  `name` varchar(128) NOT NULL,
  `version` varchar(64) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY  (`name`),
  KEY `enabled` (`enabled`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_addon_registry`
--

DROP TABLE IF EXISTS `geodesic_addon_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_addon_registry` (
  `index_key` varchar(255) NOT NULL,
  `addon` varchar(255) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` longtext NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `addon` (`addon`),
  KEY `val_string` (`val_string`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_addon_text`
--

DROP TABLE IF EXISTS `geodesic_addon_text`;
CREATE TABLE IF NOT EXISTS `geodesic_addon_text` (
  `auth_tag` varchar(128) NOT NULL,
  `addon` varchar(128) NOT NULL,
  `text_id` varchar(128) NOT NULL,
  `language_id` int(11) NOT NULL,
  `text` mediumtext NOT NULL,
  KEY `auth_tag` (`auth_tag`),
  KEY `addon` (`addon`),
  KEY `text_id` (`text_id`),
  KEY `language_id` (`language_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_ad_filter`
--

DROP TABLE IF EXISTS `geodesic_ad_filter`;
CREATE TABLE IF NOT EXISTS `geodesic_ad_filter` (
  `filter_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `search_terms` varchar(255) NOT NULL default '',
  `date_started` int(14) NOT NULL default '0',
  `category_id` smallint(6) NOT NULL default '0',
  `sub_category_check` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`filter_id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
)   ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_ad_filter_categories`
--

DROP TABLE IF EXISTS `geodesic_ad_filter_categories`;
CREATE TABLE IF NOT EXISTS `geodesic_ad_filter_categories` (
  `filter_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  KEY `filter_id` (`filter_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_autobids`
--

DROP TABLE IF EXISTS `geodesic_auctions_autobids`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_autobids` (
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `bidder` int(11) NOT NULL DEFAULT '0',
  `maxbid` double(10,2) NOT NULL DEFAULT '0.00',
  `time_of_bid` int(14) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `cost_options` varchar(255) NOT NULL,
  KEY `auction_id` (`auction_id`),
  KEY `bidder` (`bidder`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_bids`
--

DROP TABLE IF EXISTS `geodesic_auctions_bids`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_bids` (
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `bidder` int(11) NOT NULL DEFAULT '0',
  `bid` double(16,2) NOT NULL DEFAULT '0.00',
  `time_of_bid` int(14) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `buy_now_bid` int(11) NOT NULL DEFAULT '0',
  `cost_options` varchar(255) NOT NULL,
  KEY `auction_id` (`auction_id`),
  KEY `bidder` (`bidder`),
  KEY `time_of_bid` (`time_of_bid`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_blacklisted_users`
--

DROP TABLE IF EXISTS `geodesic_auctions_blacklisted_users`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_blacklisted_users` (
  `seller_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  KEY `seller_id` (`seller_id`),
  KEY `user_id` (`user_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_feedbacks`
--

DROP TABLE IF EXISTS `geodesic_auctions_feedbacks`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_feedbacks` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `rated_user_id` int(11) default NULL,
  `rater_user_id` int(11) default NULL,
  `feedback` text,
  `rate` int(11) default '0',
  `date` int(14) default NULL,
  `auction_id` int(11) NOT NULL default '0',
  `done` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `rated_user_id` (`rated_user_id`),
  KEY `rater_user_id` (`rater_user_id`)
) AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_feedback_icons`
--

DROP TABLE IF EXISTS `geodesic_auctions_feedback_icons`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_feedback_icons` (
  `filename` varchar(255) NOT NULL default '',
  `icon_num` int(11) NOT NULL default '0',
  `begin` int(11) default NULL,
  `end` int(11) default NULL,
  KEY `icon_num` (`icon_num`),
  KEY `begin` (`begin`),
  KEY `end` (`end`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_final_fee_price_increments`
--

DROP TABLE IF EXISTS `geodesic_auctions_final_fee_price_increments`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_final_fee_price_increments` (
  `price_plan_id` int(11) NOT NULL default '0',
  `low` double(16,2) default NULL,
  `high` double(16,2) default NULL,
  `charge` double(16,2) NOT NULL default '0.00',
  `charge_fixed` double(16,2) NOT NULL default '0.00',
  KEY `price_plan_id` (`price_plan_id`),
  KEY `low` (`low`),
  KEY `high` (`high`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_increments`
--

DROP TABLE IF EXISTS `geodesic_auctions_increments`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_increments` (
  `low` double(16,2) default NULL,
  `increment` double(16,2) default NULL,
  KEY `low` (`low`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_auctions_invited_users`
--

DROP TABLE IF EXISTS `geodesic_auctions_invited_users`;
CREATE TABLE IF NOT EXISTS `geodesic_auctions_invited_users` (
  `seller_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  KEY `seller_id` (`seller_id`,`user_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_banned_ips`
--

DROP TABLE IF EXISTS `geodesic_banned_ips`;
CREATE TABLE IF NOT EXISTS `geodesic_banned_ips` (
  `ip_id` int(11) NOT NULL auto_increment,
  `ip` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`ip_id`)
) AUTO_INCREMENT=1 ;

--
-- Table structure for table `geodesic_browsing_filters`
--

DROP TABLE IF EXISTS `geodesic_browsing_filters`;
CREATE TABLE IF NOT EXISTS `geodesic_browsing_filters` (
  `session_id` varchar(32) NOT NULL,
  `target` varchar(200) NOT NULL,
  `value_scalar` varchar(250) DEFAULT NULL,
  `value_range_low` double(10,2) DEFAULT NULL,
  `value_range_high` double(10,2) DEFAULT NULL,
  `category` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`,`target`)
);

--
-- Table structure for table `geodesic_browsing_filters_settings`
--
DROP TABLE IF EXISTS `geodesic_browsing_filters_settings`;
CREATE TABLE IF NOT EXISTS `geodesic_browsing_filters_settings` (
	`category` int(14) NOT NULL DEFAULT '0',
	`field` varchar(240) NOT NULL,
	`enabled` tinyint(1) NOT NULL DEFAULT '0',
	`dependency` varchar(255) NOT NULL DEFAULT '',
	`display_order` int(1) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`category`,`field`)
);

--
-- Table structure for table `geodesic_browsing_filters_settings_languages`
--
DROP TABLE IF EXISTS `geodesic_browsing_filters_settings_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_browsing_filters_settings_languages` (
	`category` int(14) NOT NULL DEFAULT '0',
	`field` varchar(220) NOT NULL,
	`language` int(14) NOT NULL DEFAULT '1',
	`name` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY  (`category`,`field`,`language`)
);

--
-- Table structure for table `geodesic_cart`
--

DROP TABLE IF EXISTS `geodesic_cart`;
CREATE TABLE IF NOT EXISTS `geodesic_cart` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `session` varchar(32) NOT NULL,
  `user_id` int(14) NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '0',
  `order` int(14) NOT NULL,
  `main_type` varchar(128) NOT NULL,
  `order_item` int(14) NOT NULL,
  `last_time` int(14) NOT NULL,
  `step` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session` (`session`),
  KEY `main_type` (`main_type`),
  KEY `last_time` (`last_time`),
  KEY `order_item` (`order_item`),
  KEY `user_id` (`user_id`),
  KEY `admin_id` (`admin_id`)
)  AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_cart_registry`
--

DROP TABLE IF EXISTS `geodesic_cart_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_cart_registry` (
  `index_key` varchar(255) NOT NULL,
  `cart` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `cart` (`cart`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_categories`
--

DROP TABLE IF EXISTS `geodesic_categories`;
CREATE TABLE IF NOT EXISTS `geodesic_categories` (
  `category_id` int(4) NOT NULL AUTO_INCREMENT,
  `parent_id` int(4) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT '1',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `display_order` int(11) DEFAULT NULL,
  `category_count` int(11) NOT NULL DEFAULT '0',
  `auction_category_count` int(11) NOT NULL DEFAULT '0',
  `what_fields_to_use` enum('site','parent','own') NOT NULL DEFAULT 'parent',
  `display_ad_description_where` int(11) NOT NULL DEFAULT '0',
  `display_all_of_description` int(11) NOT NULL DEFAULT '0',
  `length_of_description` int(11) NOT NULL DEFAULT '0',
  `default_display_order_while_browsing_category` int(11) NOT NULL DEFAULT '0',
  `listing_types_allowed` int(11) NOT NULL DEFAULT '0',
  `use_auto_title` tinyint(1) NOT NULL DEFAULT '0',
  `auto_title` varchar(255) NOT NULL DEFAULT '0',
  `which_head_html` enum('parent','cat','default','cat+default') NOT NULL DEFAULT 'parent',
  `front_page_display` ENUM('yes', 'no') NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`),
  KEY `display_order` (`display_order`),
  KEY `what_fields_to_use` (`what_fields_to_use`),
  KEY `which_head_html` (`which_head_html`),
  KEY `level` (`level`),
  KEY `enabled` (`enabled`)
) AUTO_INCREMENT=304 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_categories_excluded_per_price_plan`
--

DROP TABLE IF EXISTS `geodesic_categories_excluded_per_price_plan`;
CREATE TABLE IF NOT EXISTS `geodesic_categories_excluded_per_price_plan` (
  `price_plan_id` int(11) NOT NULL,
  `main_category_id_banned` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_categories_languages`
--

DROP TABLE IF EXISTS `geodesic_categories_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_categories_languages` (
  `category_id` int(4) NOT NULL DEFAULT '0',
  `category_name` tinytext,
  `description` tinytext,
  `category_image` varchar(255) NOT NULL DEFAULT '',
  `head_html` text NOT NULL,
  `title_module` text NOT NULL,
  `seo_url_contents` text NOT NULL,
  `category_image_alt` text NOT NULL,
  `language_id` int(11) DEFAULT '1',
  `category_cache` mediumtext NOT NULL,
  `newest_category_cache` mediumtext NOT NULL,
  `newest_cache_expire` int(11) NOT NULL DEFAULT '0',
  `featured_pic_category_cache` mediumtext NOT NULL,
  `featured_pic_cache_expire` int(11) NOT NULL DEFAULT '0',
  `featured_text_category_cache` mediumtext NOT NULL,
  `featured_text_cache_expire` int(11) NOT NULL DEFAULT '0',
  `cache_expire` int(11) NOT NULL DEFAULT '0',
  `seller_category_cache` mediumtext NOT NULL,
  `seller_cache_expire` int(11) NOT NULL DEFAULT '0',
  KEY `category_id` (`category_id`),
  KEY `language_id` (`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_category_exclude_list_types`
--

DROP TABLE IF EXISTS `geodesic_category_exclude_list_types`;
CREATE TABLE IF NOT EXISTS `geodesic_category_exclude_list_types` (
  `category_id` int(11) NOT NULL,
  `listing_type` varchar(128) NOT NULL,
  PRIMARY KEY (`category_id`,`listing_type`)
);

--
-- Table structure for table `geodesic_chain_payments`
--

DROP TABLE IF EXISTS `geodesic_chain_payments`;
CREATE TABLE IF NOT EXISTS `geodesic_chain_payments`(
	`id` int(1) AUTO_INCREMENT,
	`payKey` varchar(255) NOT NULL default '',
	`listing_id` int(1) NOT NULL,
	`sender` int(1) NOT NULL,
	`primary_receiver` int(1) NOT NULL,
	`total` float(11,2) NOT NULL,
	`secondary_receiver_data` text NOT NULL DEFAULT '',
	`creation_time` int(1) NOT NULL DEFAULT 0,
	`status` varchar(255) NOT NULL DEFAULT 'INITIATED',
	PRIMARY KEY (`id`),
	KEY `payKey` (`payKey`)
);


--
-- Table structure for table `geodesic_choices`
--

DROP TABLE IF EXISTS `geodesic_choices`;
CREATE TABLE IF NOT EXISTS `geodesic_choices` (
  `choice_id` int(11) NOT NULL auto_increment,
  `type_of_choice` tinyint(4) NOT NULL default '0',
  `display_value` tinytext NOT NULL,
  `value` tinytext NOT NULL,
  `numeric_value` smallint(6) NOT NULL default '0',
  `display_order` tinyint(4) NOT NULL default '0',
  `language_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`choice_id`),
  KEY `language_id` (`language_id`)
)  AUTO_INCREMENT=620 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds`
--

DROP TABLE IF EXISTS `geodesic_classifieds`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller` int(11) DEFAULT NULL,
  `live` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `title` text,
  `date` int(11) unsigned DEFAULT NULL,
  `description` text,
  `language_id` int(11) NOT NULL DEFAULT '0',
  `precurrency` varchar(252) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `postcurrency` varchar(252) NOT NULL DEFAULT '',
  `conversion_rate` double NOT NULL DEFAULT '1',
  `price_applies` enum('lot','item') NOT NULL DEFAULT 'lot',
  `image` tinyint(4) unsigned DEFAULT '0',
  `offsite_videos_purchased` int(11) NOT NULL DEFAULT '0',
  `additional_regions_purchased` int(11) NOT NULL DEFAULT '0',
  `category` int(11) unsigned DEFAULT NULL,
  `duration` int(11) unsigned DEFAULT NULL,
  `location_city` tinytext NOT NULL,
  `location_zip` varchar(10) DEFAULT NULL,
  `ends` int(11) unsigned DEFAULT NULL,
  `search_text` mediumtext NOT NULL,
  `viewed` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `responded` smallint(5) unsigned NOT NULL DEFAULT '0',
  `forwarded` smallint(5) unsigned NOT NULL DEFAULT '0',
  `bolding` tinyint(1) NOT NULL DEFAULT '0',
  `better_placement` int(11) NOT NULL DEFAULT '0',
  `featured_ad` tinyint(1) NOT NULL DEFAULT '0',
  `featured_ad_2` tinyint(1) NOT NULL DEFAULT '0',
  `featured_ad_3` tinyint(1) NOT NULL DEFAULT '0',
  `featured_ad_4` tinyint(1) NOT NULL DEFAULT '0',
  `featured_ad_5` tinyint(1) NOT NULL DEFAULT '0',
  `attention_getter` tinyint(1) NOT NULL DEFAULT '0',
  `attention_getter_url` tinytext NOT NULL,
  `expiration_notice` tinyint(1) NOT NULL DEFAULT '0',
  `expiration_last_sent` int(11) NOT NULL DEFAULT '0',
  `sold_displayed` tinyint(1) NOT NULL DEFAULT '0',
  `business_type` tinyint(1) NOT NULL DEFAULT '0',
  `optional_field_1` text NOT NULL,
  `optional_field_2` text NOT NULL,
  `optional_field_3` text NOT NULL,
  `optional_field_4` text NOT NULL,
  `optional_field_5` text NOT NULL,
  `optional_field_6` text NOT NULL,
  `optional_field_7` text NOT NULL,
  `optional_field_8` text NOT NULL,
  `optional_field_9` text NOT NULL,
  `optional_field_10` text NOT NULL,
  `one_votes` int(11) NOT NULL DEFAULT '0',
  `two_votes` int(11) NOT NULL DEFAULT '0',
  `three_votes` int(11) NOT NULL DEFAULT '0',
  `vote_total` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `expose_email` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(50) NOT NULL,
  `phone2` varchar(50) NOT NULL,
  `fax` varchar(50) NOT NULL,
  `filter_id` int(11) NOT NULL DEFAULT '0',
  `mapping_location` text NOT NULL,
  `paypal_id` tinytext NOT NULL,
  `renewal_length` int(11) NOT NULL DEFAULT '0',
  `optional_field_11` text NOT NULL,
  `optional_field_12` text NOT NULL,
  `optional_field_13` text NOT NULL,
  `optional_field_14` text NOT NULL,
  `optional_field_15` text NOT NULL,
  `optional_field_16` text NOT NULL,
  `optional_field_17` text NOT NULL,
  `optional_field_18` text NOT NULL,
  `optional_field_19` text NOT NULL,
  `optional_field_20` text NOT NULL,
  `discount_id` int(11) NOT NULL DEFAULT '0',
  `discount_amount` double(7,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` double(7,2) NOT NULL DEFAULT '0.00',
  `url_link_1` tinytext NOT NULL,
  `url_link_2` tinytext NOT NULL,
  `url_link_3` tinytext NOT NULL,
  `price_plan_id` int(11) NOT NULL DEFAULT '0',
  `auction_type` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `quantity_remaining` int(11) NOT NULL DEFAULT '0',
  `final_fee` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_bid` double(10,2) NOT NULL DEFAULT '0.00',
  `starting_bid` double(10,2) NOT NULL DEFAULT '0.00',
  `reserve_price` double(10,2) NOT NULL DEFAULT '0.00',
  `buy_now` double(10,2) NOT NULL DEFAULT '0.00',
  `current_bid` double(10,2) NOT NULL DEFAULT '0.00',
  `final_price` double(10,2) NOT NULL DEFAULT '0.00',
  `high_bidder` varchar(64) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `payment_options` tinytext NOT NULL,
  `end_time` int(11) NOT NULL DEFAULT '0',
  `buy_now_only` tinyint(1) NOT NULL DEFAULT '0',
  `item_type` tinyint(4) NOT NULL DEFAULT '0',
  `storefront_category` int(11) NOT NULL DEFAULT '0',
  `hide` tinyint(1) DEFAULT '0',
  `reason_ad_ended` varchar(255) DEFAULT '',
  `location_address` varchar(255) DEFAULT NULL,
  `order_item_id` int(11) NOT NULL DEFAULT '0',
  `delayed_start` int(11) NOT NULL DEFAULT '0',
  `show_contact_seller` enum('yes','no') NOT NULL DEFAULT 'yes',
  `show_other_ads` enum('yes','no') NOT NULL DEFAULT 'yes',
  `charge_per_word_count` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `live` (`live`),
  KEY `ends` (`ends`),
  KEY `seller` (`seller`),
  KEY `expiration_notice` (`expiration_notice`),
  KEY `expiration_last_sent` (`expiration_last_sent`),
  KEY `better_placement` (`better_placement`),
  KEY `featured_ad` (`featured_ad`),
  KEY `featured_ad_2` (`featured_ad_2`),
  KEY `featured_ad_3` (`featured_ad_3`),
  KEY `featured_ad_4` (`featured_ad_4`),
  KEY `featured_ad_5` (`featured_ad_5`),
  KEY `item_type` (`item_type`),
  KEY `order_item_id` (`order_item_id`),
  KEY `category` (`category`),
  KEY `offsite_videos_purchased` (`offsite_videos_purchased`),
  KEY `viewed` (`viewed`),
  KEY `date` (`date`),
  KEY `language_id` (`language_id`)
)  AUTO_INCREMENT=111 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_ads_extra`
--

DROP TABLE IF EXISTS `geodesic_classifieds_ads_extra`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_ads_extra` (
  `classified_id` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `question_id` int(11) NOT NULL default '0',
  `value` text NOT NULL,
  `explanation` tinytext NOT NULL,
  `checkbox` tinyint(4) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `display_order` int(11) NOT NULL default '0',
  KEY `classified_id` (`classified_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_ad_configuration`
--

DROP TABLE IF EXISTS `geodesic_classifieds_ad_configuration`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_ad_configuration` (
  `number_of_photos_in_detail` int(11) NOT NULL default '0',
  `maximum_image_description` int(11) NOT NULL default '0',
  `display_url_field` tinyint(4) NOT NULL default '0',
  `maximum_upload_size` int(11) NOT NULL default '100000',
  `maximum_image_width` smallint(6) NOT NULL default '0',
  `maximum_image_height` smallint(6) NOT NULL default '0',
  `maximum_top_image_width` int(11) NOT NULL default '0',
  `maximum_top_image_height` int(11) NOT NULL default '0',
  `photo_columns` tinyint(4) NOT NULL default '0',
  `user_ad_template` int(11) NOT NULL default '0',
  `user_extra_template` int(11) NOT NULL default '0',
  `user_checkbox_template` int(11) NOT NULL default '0',
  `auctions_user_ad_template` int(11) NOT NULL default '0',
  `auctions_user_extra_template` int(11) NOT NULL default '0',
  `auctions_user_checkbox_template` int(11) NOT NULL default '0',
  `full_size_image_template` int(11) NOT NULL default '0',
  `ad_detail_print_friendly_template` int(11) NOT NULL default '0',
  `auction_detail_print_friendly_template` int(11) NOT NULL default '0',
  `image_upload_type` tinyint(4) NOT NULL default '0',
  `image_upload_path` tinytext NOT NULL,
  `image_upload_save_type` tinyint(4) NOT NULL default '0',
  `url_image_directory` tinytext NOT NULL,
  `length_of_description` int(11) NOT NULL default '0',
  `sign_maximum_image_width` int(11) NOT NULL default '0',
  `sign_maximum_image_height` int(11) NOT NULL default '0',
  `flyer_maximum_image_width` int(11) NOT NULL default '0',
  `flyer_maximum_image_height` int(11) NOT NULL default '0',
  `editable_category_specific` int(11) NOT NULL default '0',
  `photo_quality` int(11) NOT NULL default '0',
  `maximum_full_image_height` int(11) NOT NULL default '0',
  `maximum_full_image_width` int(11) NOT NULL default '0',
  `popup_image_template_id` int(11) NOT NULL default '0',
  `popup_image_extra_width` int(11) NOT NULL default '0',
  `popup_image_extra_height` int(11) NOT NULL default '0',
  `print_title_length` int(11) NOT NULL default '100',
  `textarea_wrap` tinyint(4) NOT NULL default '0',
  `title_module_text` text NOT NULL,
  `title_module_language_display` int(11) NOT NULL DEFAULT '0',
  `lead_picture_width` int(11) NOT NULL default '0',
  `lead_picture_height` int(11) NOT NULL default '0',
  `use_buy_now` tinyint(11) NOT NULL default '0',
  `editable_buy_now` tinyint(11) NOT NULL default '0',
  `require_buy_now` tinyint(11) NOT NULL default '0',
  `editable_bid_start_time_field` int(11) NOT NULL default '0',
  `clientside_image_uploader_view` tinyint(4) NOT NULL default '0',
  `image_uploader_default` tinyint(4) NOT NULL default '0'
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_configuration`
--

DROP TABLE IF EXISTS `geodesic_classifieds_configuration`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_configuration` (
  `classifieds_url` tinytext NOT NULL,
  `classifieds_file_name` tinytext NOT NULL,
  `classifieds_ssl_url` tinytext NOT NULL,
  `affiliate_url` tinytext NOT NULL,
  `use_ssl_in_sell_process` tinyint(4) NOT NULL default '0',
  `site_name` varchar(100) NOT NULL default '',
  `title_bar_description` tinytext NOT NULL,
  `site_backup_dir` tinytext NOT NULL,
  `width_of_pages` smallint(4) NOT NULL default '0',
  `site_page_align` varchar(10) NOT NULL default '',
  `minimum_page_height` smallint(4) NOT NULL default '0',
  `font_type` varchar(25) NOT NULL default '',
  `send_register_attempt_email_admin` tinyint(4) NOT NULL default '1',
  `secret_for_hash` varchar(20) NOT NULL default '',
  `use_email_verification_at_registration` int(11) NOT NULL default '0',
  `admin_approves_all_registration` int(11) NOT NULL default '0',
  `registration_url` tinytext NOT NULL,
  `registration_ssl_url` tinytext NOT NULL,
  `use_ssl_in_registration` tinyint(4) NOT NULL default '0',
  `additional_shipping_charge` tinyint(4) NOT NULL default '1',
  `use_company_name_field` tinyint(4) NOT NULL default '1',
  `use_phone_field` tinyint(4) NOT NULL default '1',
  `use_phone2_field` tinyint(4) NOT NULL default '1',
  `use_fax_field` tinyint(4) NOT NULL default '1',
  `use_url_field` tinyint(4) NOT NULL default '1',
  `use_business_type_field` int(11) NOT NULL default '0',
  `use_user_agreement_field` tinyint(4) NOT NULL default '1',
  `require_company_name_field` tinyint(4) NOT NULL default '1',
  `require_business_type` int(11) NOT NULL default '0',
  `require_phone_field` tinyint(4) NOT NULL default '1',
  `require_phone2_field` tinyint(4) NOT NULL default '1',
  `require_fax_field` tinyint(4) NOT NULL default '1',
  `require_url_field` tinyint(4) NOT NULL default '1',
  `number_of_browsing_columns` tinyint(4) NOT NULL default '1',
  `number_of_browsing_subcategory_columns` int(11) NOT NULL default '0',
  `display_category_description` tinyint(4) NOT NULL default '1',
  `display_no_subcategory_message` tinyint(4) NOT NULL default '1',
  `display_ad_description_where` tinyint(4) NOT NULL default '0',
  `row_color1` varchar(10) NOT NULL default '',
  `row_color2` varchar(10) NOT NULL default '',
  `row_color_black` varchar(10) NOT NULL default '',
  `photo_icon_url` tinytext NOT NULL,
  `display_sub_category_ads` tinyint(4) NOT NULL default '1',
  `display_category_count` tinyint(4) NOT NULL default '1',
  `browsing_count_format` int(11) NOT NULL default '0',
  `length_of_description` int(11) NOT NULL default '0',
  `display_all_of_description` tinyint(4) NOT NULL default '0',
  `number_of_ads_to_display` smallint(6) NOT NULL default '20',
  `max_word_width` tinyint(4) UNSIGNED NOT NULL default '0',
  `footer_powered_by_link` tinyint(4) NOT NULL default '1',
  `help_image` tinytext NOT NULL,
  `sold_image` tinytext NOT NULL,
  `use_featured_feature` tinyint(4) NOT NULL default '0',
  `use_featured_feature_2` int(11) NOT NULL default '0',
  `use_featured_feature_3` int(11) NOT NULL default '0',
  `use_featured_feature_4` int(11) NOT NULL default '0',
  `use_featured_feature_5` int(11) NOT NULL default '0',
  `use_bolding_feature` tinyint(4) NOT NULL default '0',
  `use_better_placement_feature` tinyint(4) NOT NULL default '0',
  `use_attention_getters` tinyint(4) NOT NULL default '0',
  `all_ads_are_free` tinyint(4) NOT NULL default '0',
  `all_requests_are_free` int(11) NOT NULL default '0',
  `expire_unfinished_period` tinyint(4) NOT NULL default '30',
  `charge_tax_by` tinyint(4) NOT NULL default '0',
  `default_tax_rate` double(5,2) NOT NULL default '0.00',
  `paypal_id` tinytext NOT NULL,
  `paypal_currency_rate` double(5,4) NOT NULL default '0.0000',
  `paypal_currency` varchar(15) NOT NULL default '',
  `precurrency` varchar(252) NOT NULL default '',
  `postcurrency` varchar(252) NOT NULL default '',
  `payment_waiting_period` tinyint(4) NOT NULL default '0',
  `photo_or_icon` tinyint(4) NOT NULL default '0',
  `thumbnail_max_height` int(11) NOT NULL default '0',
  `thumbnail_max_width` int(11) NOT NULL default '0',
  `featured_ad_count` tinyint(4) NOT NULL default '0',
  `number_of_new_ads_to_display` tinyint(4) NOT NULL default '0',
  `show_country_dropdown` tinyint(4) NOT NULL default '0',
  `show_state_dropdown` tinyint(4) NOT NULL default '0',
  `days_can_upgrade` int(11) NOT NULL default '0',
  `upgrade_time` int(1) NOT NULL default '0',
  `days_to_renew` int(11) NOT NULL default '0',
  `featured_pic_ad_column_count` int(11) NOT NULL default '0',
  `featured_ad_page_count` int(11) NOT NULL default '0',
  `use_category_cache` int(11) NOT NULL default '0',
  `category_cache_time` int(11) NOT NULL default '0',
  `send_successful_placement_email` int(11) NOT NULL default '0',
  `number_of_sellers_to_display` int(11) NOT NULL default '0',
  `display_sub_category_sellers` int(11) NOT NULL default '0',
  `voting_system` int(11) NOT NULL default '0',
  `number_of_vote_comments_to_display` int(11) NOT NULL default '0',
  `post_login_page` int(11) NOT NULL default '0',
  `category_tree_display` int(11) NOT NULL default '0',
  `place_ads_only_in_terminal_categories` int(11) NOT NULL default '0',
  `entry_date_configuration` varchar(20) NOT NULL default '',
  `number_of_featured_ads_to_display` int(11) NOT NULL default '0',
  `featured_thumbnail_max_height` int(11) NOT NULL default '0',
  `featured_thumbnail_max_width` int(11) NOT NULL default '0',
  `image_link_destination_type` int(11) NOT NULL default '0',
  `display_category_tree` int(11) NOT NULL default '1',
  `display_category_navigation` int(11) NOT NULL default '1',
  `admin_approves_all_ads` int(11) NOT NULL default '0',
  `subscription_expire_period_notice` int(11) NOT NULL default '0',

  `use_rte` int(11) NOT NULL default '0',
  `email_configuration_type` int(11) NOT NULL default '0',
  `levels_of_categories_displayed` int(11) NOT NULL default '0',
  `use_zip_distance_calculator` int(11) NOT NULL default '0',
  `use_api` int(11) NOT NULL default '0',
  `use_search_form` int(11) NOT NULL default '0',
  `order_choose_category_by_alpha` int(11) NOT NULL default '0',
  `popup_while_browsing` int(11) NOT NULL default '0',
  `popup_while_browsing_width` int(11) NOT NULL default '0',
  `popup_while_browsing_height` int(11) NOT NULL default '0',
  `paypal_image_url` tinytext NOT NULL,
  `paypal_item_label` tinytext NOT NULL,
  `seller_contact` int(11) NOT NULL default '0',
  `admin_email_edit` int(11) NOT NULL default '0',
  `category_new_ad_limit` int(11) NOT NULL default '0',
  `category_new_ad_image` tinytext NOT NULL,
  `password_key` varchar(32) NOT NULL default '',
  `use_css` int(11) NOT NULL default '1',
  `user_set_hold_email` int(11) NOT NULL default '0',
  `use_account_balance` int(11) NOT NULL default '0',
  `positive_balances_only` int(11) NOT NULL default '1',
  `maximum_print_description_length` int(11) NOT NULL default '1000',
  `no_image_url` tinytext NOT NULL,
  `home_template` int(11) NOT NULL default '0',
  `invoice_cutoff` int(11) NOT NULL default '0',
  `send_admin_placement_email` int(11) NOT NULL default '0',
  `send_admin_end_email` int(11) NOT NULL default '0',
  `subscription_to_view_or_bid_ads` int(11) NOT NULL default '0',
  `site_balance_override` int(11) NOT NULL default '0',
  `idevaffiliate` int(11) NOT NULL default '0',
  `idev_renewal` int(11) NOT NULL default '0',
  `idev_upgrade` int(11) NOT NULL default '0',
  `idev_path` varchar(128) NOT NULL default '',
  `sell_category_column_count` int(11) NOT NULL default '0',
  `levels_of_categories_displayed_admin` int(11) NOT NULL default '5',
  `checkbox_columns` int(11) NOT NULL default '0',
  `charset` varchar(28) NOT NULL default '',
  `email_salutation_type` int(11) NOT NULL default '0',
  `debug_admin` int(11) NOT NULL default '0',
  `debug_browse` int(11) NOT NULL default '0',
  `debug_register` int(11) NOT NULL default '0',
  `debug_feedback` int(11) NOT NULL default '0',
  `debug_user_management` int(11) NOT NULL default '0',
  `debug_images` int(11) NOT NULL default '0',
  `debug_sell` int(11) NOT NULL default '0',
  `debug_site` int(11) NOT NULL default '0',
  `debug_affiliate` int(11) NOT NULL default '0',
  `debug_renew` int(11) NOT NULL default '0',
  `debug_bid` int(11) NOT NULL default '0',
  `debug_authenticate` int(11) NOT NULL default '0',
  `debug_modules` int(11) NOT NULL default '0',
  `user_set_auction_end_times` int(1) NOT NULL default '0',
  `user_set_auction_start_times` int(1) NOT NULL default '0',
  `display_before_start` int(11) NOT NULL default '0',
  `auction_extension_check` int(11) NOT NULL default '0',
  `auction_extension` int(11) NOT NULL default '0',
  `black_list_of_buyers` int(11) NOT NULL default '0',
  `invited_list_of_buyers` int(11) NOT NULL default '0',
  `title_module_text` text NOT NULL,
  `buy_now_image` tinytext NOT NULL,
  `reserve_met_image` tinytext NOT NULL,
  `allow_standard` int(11) NOT NULL default '1',
  `allow_dutch` int(11) NOT NULL default '1',
  `no_reserve_image` tinytext NOT NULL,
  `buy_now_reserve` int(11) NOT NULL default '0',
  `edit_begin` int(11) NOT NULL default '0',
  `admin_only_removes_auctions` int(11) NOT NULL default '0',
  `number_format` int(11) NOT NULL default '0',
  `edit_reset_date` int(11) NOT NULL default '0',
  `bid_history_link_live` int(11) NOT NULL default '0',
  `site_on_off` int(11) NOT NULL default '0',
  `disable_site_url` tinytext NOT NULL,
  `time_shift` int(2) NOT NULL default '0',
  `listing_type_allowed` int(11) NOT NULL default '0',
  `url_rewrite` tinyint(4) NOT NULL default '0',
  `optional_field_1_name` varchar(50) NOT NULL default 'Optional Field 1',
  `optional_field_2_name` varchar(50) NOT NULL default 'Optional Field 2',
  `optional_field_3_name` varchar(50) NOT NULL default 'Optional Field 3',
  `optional_field_4_name` varchar(50) NOT NULL default 'Optional Field 4',
  `optional_field_5_name` varchar(50) NOT NULL default 'Optional Field 5',
  `optional_field_6_name` varchar(50) NOT NULL default 'Optional Field 6',
  `optional_field_7_name` varchar(50) NOT NULL default 'Optional Field 7',
  `optional_field_8_name` varchar(50) NOT NULL default 'Optional Field 8',
  `optional_field_9_name` varchar(50) NOT NULL default 'Optional Field 9',
  `optional_field_10_name` varchar(50) NOT NULL default 'Optional Field 10',
  `optional_field_11_name` varchar(50) NOT NULL default 'Optional Field 11',
  `optional_field_12_name` varchar(50) NOT NULL default 'Optional Field 12',
  `optional_field_13_name` varchar(50) NOT NULL default 'Optional Field 13',
  `optional_field_14_name` varchar(50) NOT NULL default 'Optional Field 14',
  `optional_field_15_name` varchar(50) NOT NULL default 'Optional Field 15',
  `optional_field_16_name` varchar(50) NOT NULL default 'Optional Field 16',
  `optional_field_17_name` varchar(50) NOT NULL default 'Optional Field 17',
  `optional_field_18_name` varchar(50) NOT NULL default 'Optional Field 18',
  `optional_field_19_name` varchar(50) NOT NULL default 'Optional Field 19',
  `optional_field_20_name` varchar(50) NOT NULL default 'Optional Field 20',
  `default_display_order_while_browsing` int(11) NOT NULL default '0',
  `ip_ban_check` int(11) NOT NULL default '0',
  `number_of_feedbacks_to_display` int(11) NOT NULL default '1',
  `display_storefront_link` int(11) NOT NULL default '0',
  `storefront_url` tinytext NOT NULL,
  `member_since_date_configuration` varchar(20) NOT NULL default 'F Y',
  `popup_image_while_browsing` int(11) NOT NULL default '0'
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_credit_choices`
--

DROP TABLE IF EXISTS `geodesic_classifieds_credit_choices`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_credit_choices` (
  `credit_id` int(14) NOT NULL auto_increment,
  `price_plan_id` int(11) NOT NULL default '0',
  `display_value` tinytext NOT NULL,
  `value` int(11) NOT NULL default '0',
  `amount` double(5,2) NOT NULL default '0.00',
  PRIMARY KEY  (`credit_id`),
  KEY `price_plan_id` (`price_plan_id`)
) AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_expirations`
--

DROP TABLE IF EXISTS `geodesic_classifieds_expirations`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_expirations` (
  `expiration_id` int(11) NOT NULL auto_increment,
  `type` tinyint(4) NOT NULL default '0',
  `expires` int(14) NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `expiration_warning` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`expiration_id`),
  KEY `type_id` (`type_id`),
  KEY `user_id` (`user_id`)
)  AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_expired`
--

DROP TABLE IF EXISTS `geodesic_classifieds_expired`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_expired` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seller` int(11) DEFAULT NULL,
  `title` text,
  `date` int(11) DEFAULT NULL,
  `precurrency` varchar(252) NOT NULL DEFAULT '',
  `price` int(11) NOT NULL DEFAULT '0',
  `postcurrency` varchar(252) NOT NULL DEFAULT '',
  `conversion_rate` double NOT NULL DEFAULT '1',
  `price_applies` enum('lot','item') NOT NULL DEFAULT 'lot',
  `description` text,
  `image_url` tinytext,
  `category` tinytext,
  `duration` tinyint(4) DEFAULT NULL,
  `location_zip` varchar(10) DEFAULT NULL,
  `ends` int(11) DEFAULT NULL,
  `search_text` mediumtext NOT NULL,
  `ad_ended` int(11) NOT NULL DEFAULT '0',
  `reason_ad_ended` tinytext NOT NULL,
  `viewed` int(11) NOT NULL DEFAULT '0',
  `bolding` tinyint(4) NOT NULL DEFAULT '0',
  `better_placement` tinyint(4) NOT NULL DEFAULT '0',
  `featured_ad` tinyint(4) NOT NULL DEFAULT '0',
  `attention_getter` int(11) NOT NULL DEFAULT '0',
  `sold_displayed` int(11) NOT NULL DEFAULT '0',
  `business_type` int(11) NOT NULL DEFAULT '0',
  `optional_field_1` text NOT NULL,
  `optional_field_2` text NOT NULL,
  `optional_field_3` text NOT NULL,
  `optional_field_4` text NOT NULL,
  `optional_field_5` text NOT NULL,
  `optional_field_6` text NOT NULL,
  `optional_field_7` text NOT NULL,
  `optional_field_8` text NOT NULL,
  `optional_field_9` text NOT NULL,
  `optional_field_10` text NOT NULL,
  `email` tinytext NOT NULL,
  `phone` tinytext NOT NULL,
  `phone2` tinytext NOT NULL,
  `fax` tinytext NOT NULL,
  `optional_field_11` text NOT NULL,
  `optional_field_12` text NOT NULL,
  `optional_field_13` text NOT NULL,
  `optional_field_14` text NOT NULL,
  `optional_field_15` text NOT NULL,
  `optional_field_16` text NOT NULL,
  `optional_field_17` text NOT NULL,
  `optional_field_18` text NOT NULL,
  `optional_field_19` text NOT NULL,
  `optional_field_20` text NOT NULL,
  `url_link_1` tinytext NOT NULL,
  `url_link_2` tinytext NOT NULL,
  `url_link_3` tinytext NOT NULL,
  `auction_type` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `quantity_remaining` int(11) NOT NULL DEFAULT '0',
  `final_fee` int(11) NOT NULL DEFAULT '0',
  `final_fee_transaction_number` int(11) NOT NULL DEFAULT '0',
  `final_price` double(10,2) NOT NULL DEFAULT '0.00',
  `high_bidder` varchar(64) NOT NULL DEFAULT '0',
  `item_type` int(11) NOT NULL DEFAULT '0',
  `hide` int(1) DEFAULT '0',
  `order_item_id` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `seller` (`seller`),
  KEY `date` (`date`),
  KEY `ad_ended` (`ad_ended`),
  KEY `order_item_id` (`order_item_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_images_urls`
--

DROP TABLE IF EXISTS `geodesic_classifieds_images_urls`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_images_urls` (
  `image_id` int(11) NOT NULL auto_increment,
  `classified_id` int(11) NOT NULL default '0',
  `image_url` tinytext NOT NULL,
  `full_filename` tinytext NOT NULL,
  `image_text` tinytext NOT NULL,
  `thumb_url` tinytext NOT NULL,
  `thumb_filename` tinytext NOT NULL,
  `file_path` tinytext NOT NULL,
  `image_width` smallint(6) NOT NULL default '0',
  `image_height` smallint(6) NOT NULL default '0',
  `original_image_width` smallint(6) NOT NULL default '0',
  `original_image_height` smallint(6) NOT NULL default '0',
  `date_entered` int(12) NOT NULL default '0',
  `display_order` tinyint(4) NOT NULL default '0',
  `filesize` int(11) NOT NULL default '0',
  `filesize_displayed` varchar(20) NOT NULL default '',
  `icon` tinytext NOT NULL,
  `mime_type` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`image_id`),
  KEY `classified_id` (`classified_id`),
  KEY `display_order` (`display_order`)
)  AUTO_INCREMENT=88 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_messages_form`
--

DROP TABLE IF EXISTS `geodesic_classifieds_messages_form`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_messages_form` (
  `message_id` int(11) NOT NULL auto_increment,
  `message_name` varchar(50) NOT NULL default '',
  `message` mediumtext NOT NULL,
  `subject` varchar(100) NOT NULL default '',
  `content_type` varchar(20) NOT NULL default 'text/plain',
  PRIMARY KEY  (`message_id`)
)  AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_messages_past`
--

DROP TABLE IF EXISTS `geodesic_classifieds_messages_past`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_messages_past` (
  `message_id` int(11) NOT NULL auto_increment,
  `date_sent` int(14) NOT NULL default '0',
  `message` mediumtext NOT NULL,
  `all_sent` tinyint(4) NOT NULL default '0',
  `message_name` varchar(50) NOT NULL default '',
  `subject` varchar(100) NOT NULL default '',
  `content_type` varchar(20) NOT NULL default 'text/plain',
  PRIMARY KEY  (`message_id`)
)  AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_messages_past_recipients`
--

DROP TABLE IF EXISTS `geodesic_classifieds_messages_past_recipients`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_messages_past_recipients` (
  `user_id` int(11) NOT NULL default '0',
  `message_id` int(11) NOT NULL default '0',
  KEY `user_id` (`user_id`),
  KEY `message_id` (`message_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_price_increments`
--

DROP TABLE IF EXISTS `geodesic_classifieds_price_increments`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_price_increments` (
  `price_plan_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `low` double(16,2) default NULL,
  `high` double(16,2) default NULL,
  `charge` double(16,2) default NULL,
  `renewal_charge` double(16,2) NOT NULL default '0.00',
  `item_type` int(11) NOT NULL default '1',
  KEY `price_plan_id` (`price_plan_id`),
  KEY `category_id` (`category_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_price_plans`
--

DROP TABLE IF EXISTS `geodesic_classifieds_price_plans`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_price_plans` (
  `price_plan_id` int(11) NOT NULL auto_increment,
  `charge_per_ad_type` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `price_plan_expires_into` int(11) NOT NULL default '0',
  `type_of_billing` tinyint(4) NOT NULL default '0',
  `charge_per_ad` double(11,2) NOT NULL default '0.00',
  `featured_ad_price` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_2` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_3` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_4` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_5` double(11,2) NOT NULL default '0.00',
  `bolding_price` double(11,2) NOT NULL default '0.00',
  `attention_getter_price` double(11,2) NOT NULL default '0.00',
  `charge_per_picture` double(11,2) NOT NULL default '0.00',
  `better_placement_charge` double(11,2) NOT NULL default '0.00',
  `ad_renewal_cost` double(11,2) NOT NULL default '0.00',
  `subscription_billing_period` int(11) NOT NULL default '1',
  `subscription_billing_charge_per_period` double(11,2) NOT NULL default '0.00',
  `free_subscription_period_upon_registration` int(11) NOT NULL default '0',
  `expiration_type` tinyint(4) NOT NULL default '0',
  `expiration_from_registration` int(14) NOT NULL default '0',
  `max_ads_allowed` int(11) NOT NULL default '0',
  `ad_and_subscription_expiration` int(11) NOT NULL default '0',
  `instant_cash_renewals` int(11) NOT NULL default '0',
  `instant_money_order_renewals` int(11) NOT NULL default '0',
  `instant_check_renewals` int(11) NOT NULL default '0',
  `allow_credits_for_renewals` int(11) NOT NULL default '0',
  `use_featured_ads` int(11) NOT NULL default '1',
  `use_featured_ads_level_2` int(11) NOT NULL default '1',
  `use_featured_ads_level_3` int(11) NOT NULL default '1',
  `use_featured_ads_level_4` int(11) NOT NULL default '1',
  `use_featured_ads_level_5` int(11) NOT NULL default '1',
  `use_bolding` int(11) NOT NULL default '1',
  `use_better_placement` int(11) NOT NULL default '1',
  `use_attention_getters` int(11) NOT NULL default '1',
  `num_free_pics` int(11) NOT NULL default '0',
  `invoice_max` float(11,2) NOT NULL default '0.00',
  `initial_site_balance` double(11,2) NOT NULL default '0.00',
  `buy_now_only` int(11) NOT NULL default '0',
  `charge_percentage_at_auction_end` int(11) NOT NULL default '0',
  `roll_final_fee_into_future` int(11) NOT NULL default '1',
  `applies_to` int(1) NOT NULL default '0',
  `delayed_start_auction` int(11) NOT NULL default '0',
  PRIMARY KEY  (`price_plan_id`)
)  AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_price_plans_categories`
--

DROP TABLE IF EXISTS `geodesic_classifieds_price_plans_categories`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_price_plans_categories` (
  `category_price_plan_id` int(11) NOT NULL auto_increment,
  `price_plan_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `charge_per_ad_type` int(11) NOT NULL default '0',
  `charge_per_ad` double(11,2) NOT NULL default '0.00',
  `featured_ad_price` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_2` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_3` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_4` double(11,2) NOT NULL default '0.00',
  `featured_ad_price_5` double(11,2) NOT NULL default '0.00',
  `bolding_price` double(11,2) NOT NULL default '0.00',
  `attention_getter_price` double(11,2) NOT NULL default '0.00',
  `charge_per_picture` double(11,2) NOT NULL default '0.00',
  `better_placement_charge` double(11,2) NOT NULL default '0.00',
  `ad_renewal_cost` double(11,2) NOT NULL default '0.00',
  `use_featured_ads` int(11) NOT NULL default '1',
  `use_featured_ads_level_2` int(11) NOT NULL default '1',
  `use_featured_ads_level_3` int(11) NOT NULL default '1',
  `use_featured_ads_level_4` int(11) NOT NULL default '1',
  `use_featured_ads_level_5` int(11) NOT NULL default '1',
  `use_bolding` int(11) NOT NULL default '1',
  `use_better_placement` int(11) NOT NULL default '1',
  `use_attention_getters` int(11) NOT NULL default '1',
  `num_free_pics` int(11) NOT NULL default '0',
  PRIMARY KEY  (`category_price_plan_id`),
  KEY `price_plan_id` (`price_plan_id`),
  KEY `category_id` (`category_id`)
)  AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_price_plans_extras`
--

DROP TABLE IF EXISTS `geodesic_classifieds_price_plans_extras`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_price_plans_extras` (
  `price_plan_id` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `cost` decimal(10,2) NOT NULL default '0.00',
  KEY `price_plan_id` (`price_plan_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_sell_questions`
--

DROP TABLE IF EXISTS `geodesic_classifieds_sell_questions`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_sell_questions` (
  `question_id` int(11) NOT NULL auto_increment,
  `category_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `explanation` tinytext NOT NULL,
  `choices` varchar(40) NOT NULL default '',
  `other_input` tinyint(4) NOT NULL default '0',
  `display_order` int(3) NOT NULL default '0',
  PRIMARY KEY  (`question_id`),
  KEY `category_id` (`category_id`),
  KEY `group_id` (`group_id`)
) AUTO_INCREMENT=167 ;



-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_sell_question_choices`
--

DROP TABLE IF EXISTS `geodesic_classifieds_sell_question_choices`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_sell_question_choices` (
  `value_id` int(11) NOT NULL auto_increment,
  `type_id` int(11) NOT NULL default '0',
  `value` tinytext NOT NULL,
  `display_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`value_id`),
  KEY `type_id` (`type_id`),
  KEY `display_order` (`display_order`)
) AUTO_INCREMENT=273 ;

--
-- Table structure for table `geodesic_classifieds_sell_questions_languages`
--

DROP TABLE IF EXISTS `geodesic_classifieds_sell_questions_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_sell_questions_languages` (
  `question_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `explanation` tinytext NOT NULL,
  `choices` varchar(40) NOT NULL,
  `search_as_numbers` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`question_id`,`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_sell_question_types`
--

DROP TABLE IF EXISTS `geodesic_classifieds_sell_question_types`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_sell_question_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(50) NOT NULL default '',
  `explanation` tinytext NOT NULL,
  PRIMARY KEY  (`type_id`)
) AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_subscription_choices`
--

DROP TABLE IF EXISTS `geodesic_classifieds_subscription_choices`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_subscription_choices` (
  `period_id` int(14) NOT NULL auto_increment,
  `price_plan_id` int(11) NOT NULL default '0',
  `display_value` tinytext NOT NULL,
  `value` int(11) NOT NULL default '0',
  `amount` double(11,2) NOT NULL default '0.00',
  PRIMARY KEY  (`period_id`),
  KEY `price_plan_id` (`price_plan_id`)
)  AUTO_INCREMENT=28 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_user_subscriptions`
--

DROP TABLE IF EXISTS `geodesic_classifieds_user_subscriptions`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_user_subscriptions` (
  `subscription_id` int(11) NOT NULL auto_increment,
  `price_plan_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `subscription_expire` int(14) NOT NULL default '0',
  `notice_sent` int(11) NOT NULL default '0',
  `recurring_billing` int(11) NOT NULL default '0',
  PRIMARY KEY  (`subscription_id`),
  KEY `price_plan_id` (`price_plan_id`),
  KEY `user_id` (`user_id`),
  KEY `recurring_billing` (`recurring_billing`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_user_subscriptions_holds`
--

DROP TABLE IF EXISTS `geodesic_classifieds_user_subscriptions_holds`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_user_subscriptions_holds` (
  `renewal_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `subscription_choice` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `storefront` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`renewal_id`),
  KEY `user_id` (`user_id`)
)  AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_classifieds_votes`
--

DROP TABLE IF EXISTS `geodesic_classifieds_votes`;
CREATE TABLE IF NOT EXISTS `geodesic_classifieds_votes` (
  `vote_id` int(11) NOT NULL AUTO_INCREMENT,
  `classified_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `voter_ip` varchar(20) NOT NULL default '',
  `vote` int(11) NOT NULL default '0',
  `vote_title` text NOT NULL,
  `vote_comments` text NOT NULL,
  `date_entered` int(14) NOT NULL default '0',
  PRIMARY KEY (`vote_id`),
  KEY `classified_id` (`classified_id`),
  KEY `user_id` (`user_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_combined_css_list`
--

DROP TABLE IF EXISTS `geodesic_combined_css_list`;
CREATE TABLE IF NOT EXISTS `geodesic_combined_css_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(15) NOT NULL,
  `file_list` text NOT NULL,
  `resource_hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `version` (`version`),
  KEY `resource_hash` (`resource_hash`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_combined_js_list`
--

DROP TABLE IF EXISTS `geodesic_combined_js_list`;
CREATE TABLE IF NOT EXISTS `geodesic_combined_js_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(15) NOT NULL,
  `file_list` text NOT NULL,
  `resource_hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `version` (`version`),
  KEY `resource_hash` (`resource_hash`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_confirm`
--

DROP TABLE IF EXISTS `geodesic_confirm`;
CREATE TABLE IF NOT EXISTS `geodesic_confirm` (
  `id` varchar(30) NOT NULL default '',
  `mdhash` varchar(100) default NULL,
  `username` varchar(25) default NULL,
  `password` varchar(25) default NULL,
  `email` varchar(50) default NULL,
  `email2` tinytext NOT NULL,
  `date` int(14) NOT NULL default '0',
  `firstname` varchar(30) NOT NULL default '',
  `lastname` varchar(50) NOT NULL default '',
  `address` varchar(50) NOT NULL default '',
  `address_2` varchar(50) default NULL,
  `city` varchar(50) NOT NULL default '',
  `state` varchar(30) NOT NULL default '',
  `country` varchar(50) NOT NULL default '',
  `zip` varchar(15) NOT NULL default '',
  `phone` varchar(25) NOT NULL default '',
  `phone_2` varchar(25) default NULL,
  `fax` varchar(25) default NULL,
  `company_name` varchar(50) NOT NULL default '',
  `business_type` varchar(30) NOT NULL default '',
  `url` varchar(75) default NULL,
  `newsletter` varchar(10) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `optional_field_1` text NOT NULL,
  `optional_field_2` text NOT NULL,
  `optional_field_3` text NOT NULL,
  `optional_field_4` text NOT NULL,
  `optional_field_5` text NOT NULL,
  `optional_field_6` text NOT NULL,
  `optional_field_7` text NOT NULL,
  `optional_field_8` text NOT NULL,
  `optional_field_9` text NOT NULL,
  `optional_field_10` text NOT NULL,
  `filter_id` int(11) NOT NULL default '0',
  `mapping_location` text NOT NULL,
  `registration_code` tinytext NOT NULL,
  `optional_field_11` tinytext NOT NULL,
  `optional_field_12` tinytext NOT NULL,
  `optional_field_13` tinytext NOT NULL,
  `optional_field_14` tinytext NOT NULL,
  `optional_field_15` tinytext NOT NULL,
  `optional_field_16` tinytext NOT NULL,
  `optional_field_17` tinytext NOT NULL,
  `optional_field_18` tinytext NOT NULL,
  `optional_field_19` tinytext NOT NULL,
  `optional_field_20` tinytext NOT NULL,
  `user_ip` varchar(20) NOT NULL,
  `terminal_region_id` int(11) NOT NULL default '0',
  `feeshareattachment` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_confirm_email`
--

DROP TABLE IF EXISTS `geodesic_confirm_email`;
CREATE TABLE IF NOT EXISTS `geodesic_confirm_email` (
  `id` int(8) NOT NULL default '0',
  `email` varchar(50) default NULL,
  `mdhash` varchar(100) default NULL,
  `date` int(14) NOT NULL default '0',
  KEY `id` (`id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_countries`
--

DROP TABLE IF EXISTS `geodesic_countries`;
CREATE TABLE IF NOT EXISTS `geodesic_countries` (
  `country_id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `abbreviation` varchar(255) NOT NULL,
  `tax_rate` double(5,2) NOT NULL default '0.00',
  `display_order` int(11) NOT NULL default '0',
  `tax` double NOT NULL default '0',
  `tax_type` int(11) NOT NULL default '0',
  PRIMARY KEY  (`country_id`)
)  AUTO_INCREMENT=9 ;

--
-- Table structure for table `geodesic_cron`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_currency_types`
--

DROP TABLE IF EXISTS `geodesic_currency_types`;
CREATE TABLE IF NOT EXISTS `geodesic_currency_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` tinytext NOT NULL,
  `precurrency` varchar(252) NOT NULL,
  `postcurrency` varchar(252) NOT NULL,
  `conversion_rate` double NOT NULL default '1',
  `display_order` int(11) NOT NULL default '0',
  UNIQUE KEY `type_id` (`type_id`)
)  AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_email_domains`
--

DROP TABLE IF EXISTS `geodesic_email_domains`;
CREATE TABLE IF NOT EXISTS `geodesic_email_domains` (
  `serial_id` mediumint(9) NOT NULL auto_increment,
  `domain` tinytext NOT NULL,
  PRIMARY KEY  (`serial_id`)
) AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `geodesic_email_queue`
--

DROP TABLE IF EXISTS `geodesic_email_queue`;
CREATE TABLE IF NOT EXISTS `geodesic_email_queue` (
  `email_id` int(11) NOT NULL auto_increment,
  `to_array` varchar(255) NOT NULL,
  `subject` varchar(128) NOT NULL,
  `content` MEDIUMTEXT,
  `from_array` varchar(255) default NULL,
  `replyto_array` varchar(255) default NULL,
  `content_type` varchar(64) NOT NULL,
  `status` enum('sent','not_sent','error') NOT NULL,
  `sent` int(11) NOT NULL,
  PRIMARY KEY (`email_id`),
  KEY `status` (`status`),
  KEY `sent` (`sent`)
) AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_favorites`
--

DROP TABLE IF EXISTS `geodesic_favorites`;
CREATE TABLE IF NOT EXISTS `geodesic_favorites` (
  `favorite_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `classified_id` int(11) NOT NULL DEFAULT '0',
  `date_inserted` int(14) NOT NULL DEFAULT '0',
  `auction_id` int(11) NOT NULL DEFAULT '0',
  `expiration_notice` tinyint(1) NOT NULL DEFAULT '0',
  `expiration_last_sent` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`favorite_id`),
  KEY `user_id` (`user_id`),
  KEY `classified_id` (`classified_id`),
  KEY `auction_id` (`auction_id`),
  KEY `expiration_notice` (`expiration_notice`),
  KEY `expiration_last_sent` (`expiration_last_sent`)
)  AUTO_INCREMENT=9 ;

-- --------------------------------------------------------


--
-- Table structure for table `geodesic_fields`
--

DROP TABLE IF EXISTS `geodesic_fields`;
CREATE TABLE IF NOT EXISTS `geodesic_fields` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `field_name` varchar(128) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `can_edit` tinyint(1) NOT NULL,
  `field_type` enum('text','textarea','url','email','number','cost','date','dropdown','other') NOT NULL,
  `type_data` varchar(255) NOT NULL DEFAULT '0',
  `text_length` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`category_id`,`field_name`),
  KEY `is_enabled` (`is_enabled`),
  KEY `is_required` (`is_required`),
  KEY `can_edit` (`can_edit`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_field_locations`
--

DROP TABLE IF EXISTS `geodesic_field_locations`;
CREATE TABLE IF NOT EXISTS `geodesic_field_locations` (
  `group_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `field_name` varchar(128) NOT NULL,
  `display_location` varchar(128) NOT NULL,
  KEY `group_id` (`group_id`,`category_id`,`field_name`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_file_types`
--

DROP TABLE IF EXISTS `geodesic_file_types`;
CREATE TABLE IF NOT EXISTS `geodesic_file_types` (
  `file_type_id` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `mime_type` varchar(128) NOT NULL default '',
  `accept` tinyint(4) NOT NULL default '0',
  `icon_to_use` tinytext NOT NULL,
  `extension` tinytext NOT NULL,
  PRIMARY KEY  (`file_type_id`)
)  AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_groups`
--

DROP TABLE IF EXISTS `geodesic_groups`;
CREATE TABLE IF NOT EXISTS `geodesic_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `default_group` tinyint(4) NOT NULL default '0',
  `price_plan_id` int(11) NOT NULL default '1',
  `auction_price_plan_id` int(11) NOT NULL default '5',
  `registration_code` varchar(30) NOT NULL default '',
  `group_expires_into` int(11) NOT NULL default '0',
  `registration_splash_code` mediumtext NOT NULL,
  `place_an_ad_splash_code` mediumtext NOT NULL,
  `sponsored_by_code` mediumtext NOT NULL,
  `affiliate` tinyint(4) NOT NULL default '0',
  `storefront` int(11) NOT NULL default '0',
  `restrictions_bitmask` int(4) NOT NULL default '63',
  `allow_site_balance` tinyint(1) NOT NULL default '1',
  `what_fields_to_use` enum('site','own') NOT NULL default 'site',
  PRIMARY KEY  (`group_id`),
  KEY `what_fields_to_use` (`what_fields_to_use`)
)  AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_group_attached_price_plans`
--

DROP TABLE IF EXISTS `geodesic_group_attached_price_plans`;
CREATE TABLE IF NOT EXISTS `geodesic_group_attached_price_plans` (
  `group_id` int(11) NOT NULL default '0',
  `price_plan_id` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `applies_to` int(1) NOT NULL default '0',
  KEY `group_id` (`group_id`),
  KEY `price_plan_id` (`price_plan_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_html_allowed`
--

DROP TABLE IF EXISTS `geodesic_html_allowed`;
CREATE TABLE IF NOT EXISTS `geodesic_html_allowed` (
  `tag_id` int(11) NOT NULL auto_increment,
  `tag_name` varchar(20) NOT NULL default '',
  `tag_status` tinyint(4) NOT NULL default '0',
  `display` tinyint(4) NOT NULL default '0',
  `replace_with` varchar(50) NOT NULL default '',
  `use_search_string` tinyint(4) NOT NULL default '0',
  `strongly_recommended` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`tag_id`),
  KEY `tag_name` (`tag_name`)
)  AUTO_INCREMENT=61 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_invoice`
--

DROP TABLE IF EXISTS `geodesic_invoice`;
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
)  AUTO_INCREMENT=194 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_invoice_registry`
--

DROP TABLE IF EXISTS `geodesic_invoice_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_invoice_registry` (
  `index_key` varchar(255) NOT NULL,
  `invoice` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `invoice` (`invoice`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------
--
-- Table structure for table `geodesic_jit_confirmations`
--

DROP TABLE IF EXISTS `geodesic_jit_confirmations`;
CREATE TABLE IF NOT EXISTS `geodesic_jit_confirmations` (
	`email` VARCHAR(230) NOT NULL,
	`code` VARCHAR(10) NOT NULL,
	PRIMARY KEY (`email`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_leveled_field_level`
--

DROP TABLE IF EXISTS `geodesic_leveled_field_level`;
CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_level` (
  `level` int(11) NOT NULL,
  `leveled_field` int(11) NOT NULL,
  `always_show` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`level`,`leveled_field`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_leveled_field_level_labels`
--

DROP TABLE IF EXISTS `geodesic_leveled_field_level_labels`;
CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_level_labels` (
  `level` int(11) NOT NULL,
  `leveled_field` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`level`,`leveled_field`,`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_leveled_field_value`
--

DROP TABLE IF EXISTS `geodesic_leveled_field_value`;
CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leveled_field` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `display_order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `leveled_field` (`leveled_field`),
  KEY `parent` (`parent`),
  KEY `level` (`level`),
  KEY `enabled` (`enabled`),
  KEY `display_order` (`display_order`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_leveled_field_value_languages`
--

DROP TABLE IF EXISTS `geodesic_leveled_field_value_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_value_languages` (
  `id` int(11) NOT NULL COMMENT 'corresponds to id in geodesic_leveled_field_value',
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_leveled_fields`
--

DROP TABLE IF EXISTS `geodesic_leveled_fields`;
CREATE TABLE IF NOT EXISTS `geodesic_leveled_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_license_log`
--

DROP TABLE IF EXISTS `geodesic_license_log`;
CREATE TABLE IF NOT EXISTS `geodesic_license_log` (
  `log_id` int(8) NOT NULL auto_increment,
  `time` int(14) NOT NULL default '0',
  `log_type` enum('error_local','error_remote','notice_local','notice_remote') NOT NULL,
  `message` text NOT NULL,
  `need_attention` tinyint(3) NOT NULL default '1',
  PRIMARY KEY  (`log_id`),
  KEY `time` (`time`,`log_type`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listingextra_duration_prices`
--

DROP TABLE IF EXISTS `geodesic_listingextra_duration_prices`;
CREATE TABLE IF NOT EXISTS `geodesic_listingextra_duration_prices` (
	`id` int(1) NOT NULL AUTO_INCREMENT,
	`price_plan_id` int(1) NOT NULL DEFAULT 0,
	`category` int(1) NOT NULL DEFAULT 0 COMMENT 'for category-specific price plans',
	`extra_type` varchar(255) NOT NULL DEFAULT '',
	`days` int(1) NOT NULL DEFAULT 0,
	`price` double(10,2) NOT NULL DEFAULT 0.00,
	PRIMARY KEY(`id`),
	KEY (`extra_type`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listingextra_duration_languages`
--

DROP TABLE IF EXISTS `geodesic_listingextra_duration_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_listingextra_duration_languages` (
	`id` int(1) NOT NULL COMMENT 'corresponds to geodesic_listingextra_duration_prices id',
	`language_id` int(1) NOT NULL,
	`label` tinytext NOT NULL DEFAULT '',
	PRIMARY KEY(`id`, `language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listingextra_expirations`
--

DROP TABLE IF EXISTS `geodesic_listingextra_expirations`;
CREATE TABLE IF NOT EXISTS `geodesic_listingextra_expirations` (
	`listing_id` int(1) NOT NULL,
	`extra_type` varchar(240) NOT NULL DEFAULT '',
	`expires` int(1) NOT NULL DEFAULT 0 COMMENT 'unix timestamp',
	PRIMARY KEY (`listing_id`, `extra_type`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_categories`
--

DROP TABLE IF EXISTS `geodesic_listing_categories`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_categories` (
  `listing` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `category_order` int(11) NOT NULL DEFAULT '0',
  `default_name` varchar(255) NOT NULL,
  `is_terminal` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`listing`,`category`),
  KEY `level` (`level`),
  KEY `category_order` (`category_order`),
  KEY `is_terminal` (`is_terminal`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_cost_option_group`
--

DROP TABLE IF EXISTS `geodesic_listing_cost_option_group`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_cost_option_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `seller` int(11) NOT NULL,
  `quantity_type` enum('none','individual','combined') NOT NULL DEFAULT 'none',
  `display_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `listing` (`listing`),
  KEY `seller` (`seller`),
  KEY `display_order` (`display_order`)
) AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_cost_option`
--

DROP TABLE IF EXISTS `geodesic_listing_cost_option`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_cost_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `cost_added` double(10,2) NOT NULL DEFAULT '0.00',
  `file_slot` varchar(255) NOT NULL,
  `ind_quantity_remaining` int(11) NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group` (`group`),
  KEY `display_order` (`display_order`)
) AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_cost_options_q_option`
--

DROP TABLE IF EXISTS `geodesic_listing_cost_options_q_option`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_cost_options_q_option` (
  `combo_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`combo_id`,`option_id`),
  KEY `combo_id` (`combo_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_cost_options_quantity`
--

DROP TABLE IF EXISTS `geodesic_listing_cost_options_quantity`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_cost_options_quantity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing` int(11) NOT NULL DEFAULT '0',
  `quantity_remaining` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `listing` (`listing`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_leveled_fields`
--

DROP TABLE IF EXISTS `geodesic_listing_leveled_fields`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_leveled_fields` (
  `listing` int(11) NOT NULL,
  `leveled_field` int(11) NOT NULL,
  `field_value` int(11) NOT NULL COMMENT 'ID for geodesic_leveled_field_values',
  `level` int(11) NOT NULL,
  `default_name` varchar(255) NOT NULL,
  PRIMARY KEY (`listing`,`leveled_field`,`field_value`),
  KEY `level` (`level`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_regions`
--

DROP TABLE IF EXISTS `geodesic_listing_regions`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_regions` (
  `listing` int(11) NOT NULL,
  `region` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `region_order` int(3) NOT NULL DEFAULT '0',
  `default_name` varchar(255) NOT NULL,
  PRIMARY KEY (`listing`,`region`),
  KEY `level` (`level`),
  KEY `region_order` (`region_order`),
  KEY `listing` (`listing`),
  KEY `region` (`region`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_subscription`
--

DROP TABLE IF EXISTS `geodesic_listing_subscription`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_subscription` (
  `recurring_id` int(1) NOT NULL,
  `listing_id` int(1) NOT NULL,
  PRIMARY KEY (`recurring_id`),
  KEY `listing_id` (`listing_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_subscription_lengths`
--

DROP TABLE IF EXISTS `geodesic_listing_subscription_lengths`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_subscription_lengths` (
  `id` int(1) NOT NULL auto_increment,
  `price_plan` int(1) NOT NULL default '0',
  `category` int(1) NOT NULL default '0',
  `period` int(1) NOT NULL default '0',
  `period_display` tinytext NOT NULL,
  `price` double(8,2) NOT NULL default '0.00',
  PRIMARY KEY (`id`),
  KEY `price_plan` (`price_plan`),
  KEY `category` (`category`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_listing_tags`
--

DROP TABLE IF EXISTS `geodesic_listing_tags`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_tags` (
  `listing_id` int(11) NOT NULL,
  `tag` varchar(128) NOT NULL,
  PRIMARY KEY  (`listing_id`,`tag`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_logins`
--

DROP TABLE IF EXISTS `geodesic_logins`;
CREATE TABLE IF NOT EXISTS `geodesic_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(25) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `hash_type` varchar(128) NOT NULL DEFAULT '',
  `salt` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `api_token` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `api_token` (`api_token`)
)  AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_master`
--

DROP TABLE IF EXISTS `geodesic_master`;
CREATE TABLE IF NOT EXISTS `geodesic_master` (
  `setting` varchar(128) NOT NULL,
  `switch` enum('on','off') NOT NULL DEFAULT 'off',
  PRIMARY KEY (`setting`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_order`
--

DROP TABLE IF EXISTS `geodesic_order`;
CREATE TABLE IF NOT EXISTS `geodesic_order` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `status` varchar(16) NOT NULL,
  `parent` int(14) NOT NULL,
  `buyer` int(14) NOT NULL,
  `seller` int(14) NOT NULL,
  `admin` int(11) NOT NULL DEFAULT '0',
  `created` int(14) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `parent` (`parent`),
  KEY `buyer` (`buyer`),
  KEY `seller` (`seller`),
  KEY `admin` (`admin`),
  KEY `created` (`created`)
) AUTO_INCREMENT=152 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_order_item`
--

DROP TABLE IF EXISTS `geodesic_order_item`;
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
  `paid_out` int(11) DEFAULT NULL,
  `paid_out_to` int(11) DEFAULT NULL,
  `paid_out_date` int(11) DEFAULT NULL,
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

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_order_item_registry`
--

DROP TABLE IF EXISTS `geodesic_order_item_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_order_item_registry` (
  `index_key` varchar(255) NOT NULL,
  `order_item` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `order_item` (`order_item`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_order_registry`
--

DROP TABLE IF EXISTS `geodesic_order_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_order_registry` (
  `index_key` varchar(255) NOT NULL,
  `order` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `order` (`order`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_pages`
--

DROP TABLE IF EXISTS `geodesic_pages`;
CREATE TABLE IF NOT EXISTS `geodesic_pages` (
  `page_id` int(11) NOT NULL auto_increment,
  `section_id` int(11) NOT NULL default '0',
  `name` tinytext NOT NULL,
  `description` mediumtext NOT NULL,
  `special_instructions` mediumtext NOT NULL,
  `internal_template` tinyint(4) NOT NULL default '0',
  `module` tinyint(4) NOT NULL default '0',
  `module_number_of_ads_to_display` int(11) NOT NULL default '0',
  `module_display_header_row` tinyint(4) NOT NULL default '0',
  `module_display_business_type` int(11) NOT NULL default '0',
  `module_display_photo_icon` tinyint(4) NOT NULL default '0',
  `module_display_ad_description` tinyint(4) NOT NULL default '0',
  `module_display_ad_description_where` tinyint(4) NOT NULL default '0',
  `module_display_price` tinyint(4) NOT NULL default '0',
  `module_display_entry_date` tinyint(4) NOT NULL default '0',
  `display_all_of_description` tinyint(4) NOT NULL default '0',
  `length_of_description` mediumint(9) NOT NULL default '0',
  `module_file_name` tinytext NOT NULL,
  `module_replace_tag` varchar(128) NOT NULL,
  `module_display_username` tinyint(4) NOT NULL default '0',
  `module_display_title` int(11) NOT NULL default '0',
  `module_text_type` varchar(50) NOT NULL default '',
  `module_display_contact` int(11) NOT NULL default '0',
  `module_display_phone1` int(11) NOT NULL default '0',
  `module_display_phone2` int(11) NOT NULL default '0',
  `module_display_address` int(11) NOT NULL default '0',
  `module_display_optional_field_1` int(11) NOT NULL default '0',
  `module_display_optional_field_2` int(11) NOT NULL default '0',
  `module_display_optional_field_3` int(11) NOT NULL default '0',
  `module_display_optional_field_4` int(11) NOT NULL default '0',
  `module_display_optional_field_5` int(11) NOT NULL default '0',
  `module_display_optional_field_6` int(11) NOT NULL default '0',
  `module_display_optional_field_7` int(11) NOT NULL default '0',
  `module_display_optional_field_8` int(11) NOT NULL default '0',
  `module_display_optional_field_9` int(11) NOT NULL default '0',
  `module_display_optional_field_10` int(11) NOT NULL default '0',
  `module_display_optional_field_11` int(11) NOT NULL default '0',
  `module_display_optional_field_12` int(11) NOT NULL default '0',
  `module_display_optional_field_13` int(11) NOT NULL default '0',
  `module_display_optional_field_14` int(11) NOT NULL default '0',
  `module_display_optional_field_15` int(11) NOT NULL default '0',
  `module_display_optional_field_16` int(11) NOT NULL default '0',
  `module_display_optional_field_17` int(11) NOT NULL default '0',
  `module_display_optional_field_18` int(11) NOT NULL default '0',
  `module_display_optional_field_19` int(11) NOT NULL default '0',
  `module_display_optional_field_20` int(11) NOT NULL default '0',
  `module_display_city` int(11) NOT NULL default '0',
  `module_display_state` int(11) NOT NULL default '0',
  `module_display_country` int(11) NOT NULL default '0',
  `module_display_zip` int(11) NOT NULL default '0',
  `module_display_name` int(11) NOT NULL default '0',
  `module_use_image` int(11) NOT NULL default '0',
  `module_display_classified_id` int(11) NOT NULL default '0',
  `module_thumb_width` int(11) NOT NULL default '0',
  `module_thumb_height` int(11) NOT NULL default '0',
  `module_display_attention_getter` int(11) NOT NULL default '0',
  `module_number_of_columns` int(11) NOT NULL default '0',
  `module_display_filter_in_row` int(11) NOT NULL default '0',
  `cache_expire` int(11) NOT NULL default '0',
  `use_category_cache` int(11) NOT NULL default '0',
  `category_cache` mediumtext NOT NULL,
  `number_of_browsing_columns` int(11) NOT NULL default '0',
  `display_category_count` int(11) NOT NULL default '0',
  `browsing_count_format` int(11) NOT NULL default '0',
  `display_category_description` int(11) NOT NULL default '0',
  `display_no_subcategory_message` int(11) NOT NULL default '0',
  `display_category_image` int(11) NOT NULL default '0',
  `display_unselected_subfilters` int(11) NOT NULL default '0',
  `display_empty_message` mediumtext NOT NULL,
  `module_category_level_to_display` int(11) NOT NULL default '0',
  `module_category` int(11) NOT NULL default '0',
  `module_display_new_ad_icon` int(11) NOT NULL default '0',
  `photo_or_icon` int(11) NOT NULL default '2',
  `module_type` int(11) NOT NULL default '0',
  `module_display_number_bids` int(11) NOT NULL default '0',
  `module_display_time_left` int(11) NOT NULL default '0',
  `email` int(11) NOT NULL default '0',
  `module_display_type_listing` int(11) NOT NULL default '0',
  `module_display_type_text` int(11) NOT NULL default '0',
  `module_display_listing_column` int(11) NOT NULL default '0',
  `admin_label` varchar(50) NOT NULL default '',
  `applies_to` int(11) NOT NULL default '0',
  `module_display_company_name` int(11) NOT NULL default '0',
  `module_display_sub_category_nav_links` tinyint(1) NOT NULL default '0',
  `module_sub_category_nav_prefix` varchar(64) default NULL,
  `module_sub_category_nav_separator` varchar(64) default ',',
  `module_sub_category_nav_surrounding` varchar(128) default ' &nbsp; >> sub|cat|list',
  `alpha_across_columns` tinyint(1) NOT NULL,
  `alt_order_by` int(2) NOT NULL default '0',
  PRIMARY KEY  (`page_id`),
  KEY `section_id` (`section_id`),
  KEY `module` (`module`),
  KEY `module_replace_tag` (`module_replace_tag`)
) AUTO_INCREMENT=10210 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_pages_languages`
--

DROP TABLE IF EXISTS `geodesic_pages_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_pages_languages` (
  `language_id` int(11) NOT NULL auto_increment,
  `language` varchar(50) NOT NULL default '',
  `browser_label` varchar(50) NOT NULL default '',
  `default_language` tinyint(4) NOT NULL default '0',
  `active` tinyint(4) NOT NULL default '0',
  `charset` enum('ISO-8859-1','ISO-8859-15','UTF-8','cp866','cp1251','cp1252','KOI8-R','BIG5','GB2312','BIG5-HKSCS','Shift_JIS','EUC-JP') NOT NULL default 'ISO-8859-1',
  PRIMARY KEY  (`language_id`),
  KEY `default_language` (`default_language`)
)  AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_pages_messages`
--

DROP TABLE IF EXISTS `geodesic_pages_messages`;
CREATE TABLE IF NOT EXISTS `geodesic_pages_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `text` tinytext NOT NULL,
  `page_id` int(11) NOT NULL default '0',
  `display_order` int(11) NOT NULL default '0',
  `classauctions` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `subtype` int(11) NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `page_id` (`page_id`),
  KEY `type` (`type`),
  KEY `display_order` (`display_order`)
)  AUTO_INCREMENT=500217 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_pages_messages_languages`
--

DROP TABLE IF EXISTS `geodesic_pages_messages_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_pages_messages_languages` (
  `page_id` int(11) NOT NULL default '0',
  `text_id` int(11) NOT NULL default '0',
  `language_id` tinyint(4) NOT NULL default '0',
  `text` mediumtext NOT NULL,
  KEY `page_id` (`page_id`),
  KEY `text_id` (`text_id`),
  KEY `language_id` (`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_pages_modules_sections`
--

DROP TABLE IF EXISTS `geodesic_pages_modules_sections`;
CREATE TABLE IF NOT EXISTS `geodesic_pages_modules_sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `parent_section` int(11) NOT NULL default '0',
  `display_order` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`section_id`),
  KEY `parent_section` (`parent_section`),
  KEY `display_order` (`display_order`)
)  AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_pages_sections`
--

DROP TABLE IF EXISTS `geodesic_pages_sections`;
CREATE TABLE IF NOT EXISTS `geodesic_pages_sections` (
  `section_id` int(11) NOT NULL auto_increment,
  `name` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `parent_section` int(11) NOT NULL default '0',
  `display_order` tinyint(4) NOT NULL default '0',
  `applies_to` int(11) NOT NULL default '0',
  PRIMARY KEY  (`section_id`),
  KEY `parent_section` (`parent_section`),
  KEY `display_order` (`display_order`),
  KEY `applies_to` (`applies_to`)
)  AUTO_INCREMENT=15 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_payment_gateway`
--

DROP TABLE IF EXISTS `geodesic_payment_gateway`;
CREATE TABLE IF NOT EXISTS `geodesic_payment_gateway` (
  `name` varchar(128) NOT NULL,
  `gateway_type` varchar(64) NOT NULL,
  `display_order` int(4) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL,
  `group` int(11) NOT NULL default '0',
  PRIMARY KEY  (`name`,`group`),
  UNIQUE KEY `display_order` (`display_order`, `group`),
  KEY `gateway_type` (`gateway_type`),
  KEY `enabled` (`enabled`),
  KEY `default` (`default`),
  KEY `group` (`group`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_payment_gateway_registry`
--

DROP TABLE IF EXISTS `geodesic_payment_gateway_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_payment_gateway_registry` (
  `index_key` varchar(255) NOT NULL,
  `payment_gateway` varchar(128) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `payment_gateway` (`payment_gateway`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_payment_types`
--

DROP TABLE IF EXISTS `geodesic_payment_types`;
CREATE TABLE IF NOT EXISTS `geodesic_payment_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` tinytext NOT NULL,
  `display_order` int(11) NOT NULL default '0',
  KEY `type_id` (`type_id`)
)  AUTO_INCREMENT=13 ;

DROP TABLE IF EXISTS `geodesic_plan_item`;
CREATE TABLE IF NOT EXISTS `geodesic_plan_item` (
  `order_item` varchar(200) NOT NULL,
  `price_plan` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `process_order` int(14) NOT NULL,
  `need_admin_approval` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`order_item`,`price_plan`,`category`),
  KEY `process_order` (`process_order`),
  KEY `enabled` (`enabled`)
) ;

DROP TABLE IF EXISTS `geodesic_plan_item_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_plan_item_registry` (
  `index_key` varchar(255) NOT NULL,
  `plan_item` varchar(255) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `plan_item` (`plan_item`),
  KEY `val_string` (`val_string`)
);

--
-- Table structure for table `geodesic_price_plan_ad_lengths`
--

DROP TABLE IF EXISTS `geodesic_price_plan_ad_lengths`;
CREATE TABLE IF NOT EXISTS `geodesic_price_plan_ad_lengths` (
  `length_id` int(11) NOT NULL auto_increment,
  `price_plan_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `length_of_ad` int(11) NOT NULL default '0',
  `display_length_of_ad` tinytext NOT NULL,
  `length_charge` double(8,2) NOT NULL default '0.00',
  `renewal_charge` double(8,2) NOT NULL default '0.00',
  UNIQUE KEY `length_id` (`length_id`)
)  AUTO_INCREMENT=19 ;

DROP TABLE IF EXISTS `geodesic_recurring_billing`;
CREATE TABLE IF NOT EXISTS `geodesic_recurring_billing` (
  `id` int(14) NOT NULL auto_increment,
  `secondary_id` varchar(255) NOT NULL,
  `gateway` varchar(128) NOT NULL,
  `start_date` int(14) NOT NULL,
  `paid_until` int(14) NOT NULL,
  `status` varchar(128) NOT NULL,
  `order_id` int(14) NOT NULL,
  `user_id` int(14) NOT NULL,
  `cycle_duration` int(14) NOT NULL,
  `price_per_cycle` decimal(14,4) NOT NULL,
  `item_type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `secondary_id` (`secondary_id`),
  KEY `gateway` (`gateway`),
  KEY `paid_until` (`paid_until`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`),
  KEY `item_type` (`item_type`)
) AUTO_INCREMENT=27 ;

DROP TABLE IF EXISTS `geodesic_recurring_billing_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_recurring_billing_registry` (
  `index_key` varchar(255) NOT NULL,
  `recurring_billing` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `recurring_billing` (`recurring_billing`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_region`
--

DROP TABLE IF EXISTS `geodesic_region`;
CREATE TABLE IF NOT EXISTS `geodesic_region` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL,
  `level` int(2) NOT NULL,
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `billing_abbreviation` varchar(255) NOT NULL,
  `unique_name` varchar(255) NOT NULL,
  `tax_percent` double NOT NULL DEFAULT '0',
  `tax_flat` double NOT NULL DEFAULT '0',
  `display_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `level` (`level`),
  KEY `enabled` (`enabled`),
  KEY `unique_name` (`unique_name`),
  KEY `display_order` (`display_order`)
) AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_region_languages`
--

DROP TABLE IF EXISTS `geodesic_region_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_region_languages` (
  `id` int(11) NOT NULL COMMENT 'corresponds to id in geodesic_region',
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_region_level`
--

DROP TABLE IF EXISTS `geodesic_region_level`;
CREATE TABLE IF NOT EXISTS `geodesic_region_level` (
  `level` int(11) NOT NULL,
  `region_type` enum('country','state/province','city','other') NOT NULL DEFAULT 'other',
  `use_label` enum('yes','no') NOT NULL DEFAULT 'no',
  `always_show` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`level`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_region_level_languages`
--

DROP TABLE IF EXISTS `geodesic_region_level_labels`;
CREATE TABLE IF NOT EXISTS `geodesic_region_level_labels` (
  `level` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`level`,`language_id`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_registration_configuration`
--

DROP TABLE IF EXISTS `geodesic_registration_configuration`;
CREATE TABLE IF NOT EXISTS `geodesic_registration_configuration` (
  `use_registration_email2_field` int(11) NOT NULL default '0',
  `require_registration_email2_field` int(11) NOT NULL default '0',
  `use_registration_company_name_field` int(11) NOT NULL default '0',
  `require_registration_company_name_field` int(11) NOT NULL default '0',
  `use_registration_phone_field` int(11) NOT NULL default '0',
  `require_registration_phone_field` int(11) NOT NULL default '0',
  `use_registration_phone2_field` int(11) NOT NULL default '0',
  `require_registration_phone2_field` int(11) NOT NULL default '0',
  `use_registration_fax_field` int(11) NOT NULL default '0',
  `require_registration_fax_field` int(11) NOT NULL default '0',
  `use_registration_url_field` int(11) NOT NULL default '0',
  `require_registration_url_field` int(11) NOT NULL default '0',
  `use_registration_city_field` int(11) NOT NULL default '0',
  `require_registration_city_field` int(11) NOT NULL default '0',
  `use_registration_state_field` int(11) NOT NULL default '0',
  `require_registration_state_field` int(11) NOT NULL default '0',
  `use_registration_zip_field` int(11) NOT NULL default '0',
  `require_registration_zip_field` int(11) NOT NULL default '0',
  `use_registration_country_field` int(11) NOT NULL default '0',
  `require_registration_country_field` int(11) NOT NULL default '0',
  `use_registration_address_field` int(11) NOT NULL default '0',
  `require_registration_address_field` int(11) NOT NULL default '0',
  `use_registration_address2_field` int(11) NOT NULL default '0',
  `require_registration_address2_field` int(11) NOT NULL default '0',
  `use_registration_business_type_field` int(11) NOT NULL default '0',
  `require_registration_business_type_field` int(11) NOT NULL default '0',
  `use_user_agreement_field` int(11) NOT NULL default '0',
  `use_registration_optional_1_field` int(11) NOT NULL default '0',
  `require_registration_optional_1_field` int(11) NOT NULL default '0',
  `require_registration_optional_1_field_dep` int(11) NOT NULL default '0',
  `registration_optional_1_field_type` int(11) NOT NULL default '0',
  `registration_optional_1_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_2_field` int(11) NOT NULL default '0',
  `require_registration_optional_2_field` int(11) NOT NULL default '0',
  `require_registration_optional_2_field_dep` int(11) NOT NULL default '0',
  `registration_optional_2_field_type` int(11) NOT NULL default '0',
  `registration_optional_2_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_3_field` int(11) NOT NULL default '0',
  `require_registration_optional_3_field` int(11) NOT NULL default '0',
  `require_registration_optional_3_field_dep` int(11) NOT NULL default '0',
  `registration_optional_3_field_type` int(11) NOT NULL default '0',
  `registration_optional_3_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_4_field` int(11) NOT NULL default '0',
  `require_registration_optional_4_field` int(11) NOT NULL default '0',
  `require_registration_optional_4_field_dep` int(11) NOT NULL default '0',
  `registration_optional_4_field_type` int(11) NOT NULL default '0',
  `registration_optional_4_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_5_field` int(11) NOT NULL default '0',
  `require_registration_optional_5_field` int(11) NOT NULL default '0',
  `require_registration_optional_5_field_dep` int(11) NOT NULL default '0',
  `registration_optional_5_field_type` int(11) NOT NULL default '0',
  `registration_optional_5_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_6_field` int(11) NOT NULL default '0',
  `require_registration_optional_6_field` int(11) NOT NULL default '0',
  `require_registration_optional_6_field_dep` int(11) NOT NULL default '0',
  `registration_optional_6_field_type` int(11) NOT NULL default '0',
  `registration_optional_6_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_7_field` int(11) NOT NULL default '0',
  `require_registration_optional_7_field` int(11) NOT NULL default '0',
  `require_registration_optional_7_field_dep` int(11) NOT NULL default '0',
  `registration_optional_7_field_type` int(11) NOT NULL default '0',
  `registration_optional_7_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_8_field` int(11) NOT NULL default '0',
  `require_registration_optional_8_field` int(11) NOT NULL default '0',
  `require_registration_optional_8_field_dep` int(11) NOT NULL default '0',
  `registration_optional_8_field_type` int(11) NOT NULL default '0',
  `registration_optional_8_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_9_field` int(11) NOT NULL default '0',
  `require_registration_optional_9_field` int(11) NOT NULL default '0',
  `require_registration_optional_9_field_dep` int(11) NOT NULL default '0',
  `registration_optional_9_field_type` int(11) NOT NULL default '0',
  `registration_optional_9_other_box` int(11) NOT NULL default '0',
  `use_registration_optional_10_field` int(11) NOT NULL default '0',
  `require_registration_optional_10_field` int(11) NOT NULL default '0',
  `require_registration_optional_10_field_dep` int(11) NOT NULL default '0',
  `registration_optional_10_field_type` int(11) NOT NULL default '0',
  `registration_optional_10_other_box` int(11) NOT NULL default '0',
  `use_registration_firstname_field` int(11) NOT NULL default '0',
  `require_registration_firstname_field` int(11) NOT NULL default '0',
  `use_registration_lastname_field` int(11) NOT NULL default '0',
  `require_registration_lastname_field` int(11) NOT NULL default '0',
  `firstname_maxlength` int(11) default '50',
  `lastname_maxlength` int(11) default '50',
  `company_name_maxlength` int(11) default '50',
  `address_maxlength` int(11) default '50',
  `address_2_maxlength` int(11) default '50',
  `phone_maxlength` int(11) default '50',
  `phone_2_maxlength` int(11) default '50',
  `fax_maxlength` int(11) default '50',
  `city_maxlength` int(11) default '50',
  `zip_maxlength` int(11) default '50',
  `url_maxlength` int(11) default '50',
  `optional_1_maxlength` int(11) default '50',
  `optional_2_maxlength` int(11) default '50',
  `optional_3_maxlength` int(11) default '50',
  `optional_4_maxlength` int(11) default '50',
  `optional_5_maxlength` int(11) default '50',
  `optional_6_maxlength` int(11) default '50',
  `optional_7_maxlength` int(11) default '50',
  `optional_8_maxlength` int(11) default '50',
  `optional_9_maxlength` int(11) default '50',
  `optional_10_maxlength` int(11) default '50',
  `registration_optional_1_field_name` varchar(50) NOT NULL default 'Reg Optional Field 1',
  `registration_optional_2_field_name` varchar(50) NOT NULL default 'Reg Optional Field 2',
  `registration_optional_3_field_name` varchar(50) NOT NULL default 'Reg Optional Field 3',
  `registration_optional_4_field_name` varchar(50) NOT NULL default 'Reg Optional Field 4',
  `registration_optional_5_field_name` varchar(50) NOT NULL default 'Reg Optional Field 5',
  `registration_optional_6_field_name` varchar(50) NOT NULL default 'Reg Optional Field 6',
  `registration_optional_7_field_name` varchar(50) NOT NULL default 'Reg Optional Field 7',
  `registration_optional_8_field_name` varchar(50) NOT NULL default 'Reg Optional Field 8',
  `registration_optional_9_field_name` varchar(50) NOT NULL default 'Reg Optional Field 9',
  `registration_optional_10_field_name` varchar(50) NOT NULL default 'Reg Optional Field 10'
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_registration_question_choices`
--

DROP TABLE IF EXISTS `geodesic_registration_question_choices`;
CREATE TABLE IF NOT EXISTS `geodesic_registration_question_choices` (
  `value_id` int(11) NOT NULL auto_increment,
  `type_id` int(11) NOT NULL default '0',
  `value` tinytext NOT NULL,
  `display_order` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`value_id`)
)  AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_registration_question_types`
--

DROP TABLE IF EXISTS `geodesic_registration_question_types`;
CREATE TABLE IF NOT EXISTS `geodesic_registration_question_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(50) NOT NULL default '',
  `explanation` tinytext NOT NULL,
  PRIMARY KEY  (`type_id`)
)  AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_registration_session`
--

DROP TABLE IF EXISTS `geodesic_registration_session`;
CREATE TABLE IF NOT EXISTS `geodesic_registration_session` (
  `session` varchar(32) NOT NULL default '',
  `time_started` int(14) NOT NULL default '0',
  `email` tinytext NOT NULL,
  `email_verifier` tinytext NOT NULL,
  `email2` tinytext NOT NULL,
  `email_verifier2` tinytext NOT NULL,
  `username` tinytext NOT NULL,
  `password` tinytext NOT NULL,
  `agreement` tinytext NOT NULL,
  `company_name` tinytext NOT NULL,
  `business_type` int(11) NOT NULL default '0',
  `firstname` tinytext NOT NULL,
  `lastname` tinytext NOT NULL,
  `address` tinytext NOT NULL,
  `address_2` tinytext NOT NULL,
  `city` tinytext NOT NULL,
  `state` tinytext NOT NULL,
  `country` tinytext NOT NULL,
  `zip` tinytext NOT NULL,
  `phone` tinytext NOT NULL,
  `phone_2` tinytext NOT NULL,
  `fax` tinytext NOT NULL,
  `url` tinytext NOT NULL,
  `registration_group` int(11) NOT NULL default '0',
  `registration_code_checked` tinyint(4) NOT NULL default '0',
  `personal_info_check` tinyint(4) NOT NULL default '0',
  `registration_code_use` tinyint(4) NOT NULL default '0',
  `registration_id` tinytext NOT NULL,
  `optional_field_1` text NOT NULL,
  `optional_field_2` text NOT NULL,
  `optional_field_3` text NOT NULL,
  `optional_field_4` text NOT NULL,
  `optional_field_5` text NOT NULL,
  `optional_field_6` text NOT NULL,
  `optional_field_7` text NOT NULL,
  `optional_field_8` text NOT NULL,
  `optional_field_9` text NOT NULL,
  `optional_field_10` text NOT NULL,
  `filter_id` int(11) NOT NULL default '0',
  `registration_code` tinytext NOT NULL,
  `feeshareattachment` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`session`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_sessions`
--

DROP TABLE IF EXISTS `geodesic_sessions`;
CREATE TABLE IF NOT EXISTS `geodesic_sessions` (
  `classified_session` varchar(32) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `last_time` int(14) NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '0',
  `ip_ssl` varchar(40) NOT NULL DEFAULT '0',
  `level` tinyint(4) NOT NULL DEFAULT '0',
  `admin_session` enum('Yes','No') NOT NULL DEFAULT 'No',
  `securityString` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`classified_session`),
  KEY `user_id` (`user_id`),
  KEY `last_time` (`last_time`),
  KEY `admin_session` (`admin_session`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_sessions_registry`
--

DROP TABLE IF EXISTS `geodesic_sessions_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_sessions_registry` (
  `index_key` varchar(255) NOT NULL default '',
  `sessions` varchar(32) NOT NULL default '',
  `val_string` varchar(255) NOT NULL default '',
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `sessions` (`sessions`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_site_settings`
--

DROP TABLE IF EXISTS `geodesic_site_settings`;
CREATE TABLE IF NOT EXISTS `geodesic_site_settings` (
  `setting` varchar(250) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`setting`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_site_settings_long`
--

DROP TABLE IF EXISTS `geodesic_site_settings_long`;
CREATE TABLE IF NOT EXISTS `geodesic_site_settings_long` (
  `setting` varchar(250) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY  (`setting`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_states`
--

DROP TABLE IF EXISTS `geodesic_states`;
CREATE TABLE IF NOT EXISTS `geodesic_states` (
  `state_id` int(11) NOT NULL auto_increment,
  `abbreviation` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL,
  `tax_rate` double(4,2) NOT NULL default '0.00',
  `display_order` int(11) NOT NULL default '0',
  `tax` double NOT NULL default '0',
  `tax_type` int(11) NOT NULL default '0',
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY  (`state_id`),
  KEY `abbreviation` (`abbreviation`),
  KEY `display_order` (`display_order`),
  KEY `parent_id` (`parent_id`)
)  AUTO_INCREMENT=168 ;


-- --------------------------------------------------------

--
-- Table structure for table `geodesic_text_badwords`
--

DROP TABLE IF EXISTS `geodesic_text_badwords`;
CREATE TABLE IF NOT EXISTS `geodesic_text_badwords` (
  `badword_id` int(11) NOT NULL auto_increment,
  `badword` varchar(30) NOT NULL default '',
  `badword_replacement` varchar(30) NOT NULL default '',
  `entire_word` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`badword_id`)
)  AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_transaction`
--

DROP TABLE IF EXISTS `geodesic_transaction`;
CREATE TABLE IF NOT EXISTS `geodesic_transaction` (
  `id` int(14) NOT NULL auto_increment,
  `invoice` int(14) NOT NULL,
  `recurring_billing` int(14) NOT NULL,
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
  KEY `invoice` (`invoice`),
  KEY `recurring_billing` (`recurring_billing`)
)  AUTO_INCREMENT=220 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_transaction_registry`
--

DROP TABLE IF EXISTS `geodesic_transaction_registry`;
CREATE TABLE IF NOT EXISTS `geodesic_transaction_registry` (
  `index_key` varchar(255) NOT NULL,
  `transaction` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `transaction` (`transaction`),
  KEY `val_string` (`val_string`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_userdata`
--

DROP TABLE IF EXISTS `geodesic_userdata`;
CREATE TABLE IF NOT EXISTS `geodesic_userdata` (
  `id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `email2` tinytext NOT NULL,
  `newsletter` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `company_name` varchar(50) NOT NULL DEFAULT '',
  `business_type` int(11) NOT NULL DEFAULT '0',
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(50) NOT NULL DEFAULT '',
  `address_2` varchar(50) DEFAULT NULL,
  `zip` varchar(12) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `state` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(50) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `phone2` varchar(50) NOT NULL DEFAULT '',
  `fax` varchar(50) DEFAULT NULL,
  `url` tinytext,
  `date_joined` int(14) NOT NULL DEFAULT '0',
  `communication_type` int(11) NOT NULL DEFAULT '3',
  `feedback_score` int(11) NOT NULL DEFAULT '0',
  `feedback_count` int(11) NOT NULL DEFAULT '0',
  `feedback_positive_count` int(11) NOT NULL DEFAULT '0',
  `rate_sum` int(11) NOT NULL DEFAULT '0',
  `rate_num` int(11) NOT NULL DEFAULT '0',
  `optional_field_1` text NOT NULL,
  `optional_field_2` text NOT NULL,
  `optional_field_3` text NOT NULL,
  `optional_field_4` text NOT NULL,
  `optional_field_5` text NOT NULL,
  `optional_field_6` text NOT NULL,
  `optional_field_7` text NOT NULL,
  `optional_field_8` text NOT NULL,
  `optional_field_9` text NOT NULL,
  `optional_field_10` text NOT NULL,
  `affiliate_html` mediumtext NOT NULL,
  `filter_id` int(11) NOT NULL DEFAULT '0',
  `expose_email` int(11) NOT NULL DEFAULT '0',
  `expose_company_name` int(11) NOT NULL DEFAULT '0',
  `expose_firstname` int(11) NOT NULL DEFAULT '0',
  `expose_lastname` int(11) NOT NULL DEFAULT '0',
  `expose_address` int(11) NOT NULL DEFAULT '0',
  `expose_city` int(11) NOT NULL DEFAULT '0',
  `expose_state` int(11) NOT NULL DEFAULT '0',
  `expose_country` int(11) NOT NULL DEFAULT '0',
  `expose_zip` int(11) NOT NULL DEFAULT '0',
  `expose_phone` int(11) NOT NULL DEFAULT '0',
  `expose_phone2` int(11) NOT NULL DEFAULT '0',
  `expose_fax` int(11) NOT NULL DEFAULT '0',
  `expose_url` int(11) NOT NULL DEFAULT '0',
  `expose_optional_1` int(11) NOT NULL DEFAULT '0',
  `expose_optional_2` int(11) NOT NULL DEFAULT '0',
  `expose_optional_3` int(11) NOT NULL DEFAULT '0',
  `expose_optional_4` int(11) NOT NULL DEFAULT '0',
  `expose_optional_5` int(11) NOT NULL DEFAULT '0',
  `expose_optional_6` int(11) NOT NULL DEFAULT '0',
  `expose_optional_7` int(11) NOT NULL DEFAULT '0',
  `expose_optional_8` int(11) NOT NULL DEFAULT '0',
  `expose_optional_9` int(11) NOT NULL DEFAULT '0',
  `expose_optional_10` int(11) NOT NULL DEFAULT '0',
  `account_balance` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `date_balance_negative` int(14) NOT NULL,
  `balance_freeze` int(3) NOT NULL DEFAULT '0',
  `feedback_icon` varchar(255) NOT NULL DEFAULT '',
  `storefront_header` text NOT NULL,
  `storefront_template_id` int(11) NOT NULL DEFAULT '0',
  `storefront_welcome_message` text NOT NULL,
  `storefront_on_hold` int(11) NOT NULL DEFAULT '1',
  `storefront_home_link` varchar(255) NOT NULL DEFAULT '',
  `storefront_traffic_processed_at` int(11) NOT NULL,
  `last_login_time` datetime NOT NULL,
  `last_login_ip` varchar(15) DEFAULT NULL,
  `verified` enum('no','yes') NOT NULL DEFAULT 'no',
  `storefront_trials_used` text NOT NULL,
  `seller_buyer_data` text,
  `new_listing_alert_last_sent` INT(11) NOT NULL DEFAULT '0',
  `new_listing_alert_gap` INT(11) NOT NULL DEFAULT '86400',
  `attached_user_message` text NOT NULL,
  `admin_note` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_userdata_history`
--

DROP TABLE IF EXISTS `geodesic_userdata_history`;
CREATE TABLE IF NOT EXISTS `geodesic_userdata_history` (
  `history_id` int(11) NOT NULL auto_increment,
  `date_of_change` int(14) NOT NULL default '0',
  `id` int(11) NOT NULL default '0',
  `username` varchar(25) NOT NULL default '',
  `email` tinytext NOT NULL,
  `email2` tinytext NOT NULL,
  `company_name` varchar(50) NOT NULL default '',
  `business_type` varchar(30) NOT NULL default '',
  `firstname` varchar(50) NOT NULL default '',
  `lastname` varchar(50) NOT NULL default '',
  `address` varchar(50) NOT NULL default '',
  `address_2` varchar(50) default NULL,
  `zip` varchar(30) NOT NULL default '',
  `city` varchar(50) NOT NULL default '',
  `state` varchar(50) NOT NULL default '',
  `country` varchar(50) NOT NULL default '',
  `phone` varchar(50) NOT NULL default '',
  `phone2` varchar(50) NOT NULL default '',
  `fax` varchar(50) default NULL,
  `url` tinytext,
  `optional_field_1` tinytext NOT NULL,
  `optional_field_2` tinytext NOT NULL,
  `optional_field_3` tinytext NOT NULL,
  `optional_field_4` tinytext NOT NULL,
  `optional_field_5` tinytext NOT NULL,
  `optional_field_6` tinytext NOT NULL,
  `optional_field_7` tinytext NOT NULL,
  `optional_field_8` tinytext NOT NULL,
  `optional_field_9` tinytext NOT NULL,
  `optional_field_10` tinytext NOT NULL,
  `affiliate_html` mediumtext NOT NULL,
  PRIMARY KEY  (`history_id`),
  KEY `id` (`id`),
  KEY `username` (`username`)
)  AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_user_communications`
--

DROP TABLE IF EXISTS `geodesic_user_communications`;
CREATE TABLE IF NOT EXISTS `geodesic_user_communications` (
  `message_id` int(11) NOT NULL auto_increment,
  `message_to` int(11) NOT NULL default '0',
  `message_from` int(11) NOT NULL default '0',
  `message_from_non_user` mediumtext NOT NULL,
  `regarding_ad` int(11) NOT NULL default '0',
  `date_sent` int(14) NOT NULL default '0',
  `message` mediumtext NOT NULL,
  `replied_to_this_message` int(11) NOT NULL default '0',
  `read` tinyint(4) NOT NULL default '0',
  `public_question` tinyint(1) NOT NULL default '0',
  `body_text` text NOT NULL,
  `public_answer` tinyint(1) NOT NULL default '0',
  `sender_deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `receiver_deleted` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`message_id`)
)  AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_user_groups_price_plans`
--

DROP TABLE IF EXISTS `geodesic_user_groups_price_plans`;
CREATE TABLE IF NOT EXISTS `geodesic_user_groups_price_plans` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(4) NOT NULL default '1',
  `price_plan_id` int(11) NOT NULL default '1',
  `auction_price_plan_id` int(11) NOT NULL default '5',
  PRIMARY KEY  (`id`)
)  AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_user_ratings`
--

DROP TABLE IF EXISTS `geodesic_user_ratings`;
CREATE TABLE IF NOT EXISTS `geodesic_user_ratings` (
	`about` int(1) NOT NULL,
	`from` int(1) NOT NULL,
	`rating` int(1) NOT NULL,
	PRIMARY KEY (`about`, `from`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_user_ratings`
--

DROP TABLE IF EXISTS `geodesic_user_ratings_averages`;
CREATE TABLE IF NOT EXISTS `geodesic_user_ratings_averages` (
	`about` int(1) NOT NULL,
	`average` double(3,2) NOT NULL,
	`notified` int(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`about`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_user_regions`
--

DROP TABLE IF EXISTS `geodesic_user_regions`;
CREATE TABLE IF NOT EXISTS `geodesic_user_regions` (
  `user` int(11) NOT NULL,
  `region` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `default_name` varchar(255) NOT NULL,
  PRIMARY KEY (`user`,`region`),
  KEY `level` (`level`)
);

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_user_tokens`
--

DROP TABLE IF EXISTS `geodesic_user_tokens`;
CREATE TABLE IF NOT EXISTS `geodesic_user_tokens` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `token_count` int(11) NOT NULL default '0',
  `expire` int(14) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `expire` (`expire`)
)  AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `geodesic_version`
--

DROP TABLE IF EXISTS `geodesic_version`;
CREATE TABLE IF NOT EXISTS `geodesic_version` (
  `db_version` tinytext NOT NULL
);

--
-- Table structure for table `geodesic_listing_offsite_videos`
--

DROP TABLE IF EXISTS `geodesic_listing_offsite_videos`;
CREATE TABLE IF NOT EXISTS `geodesic_listing_offsite_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) NOT NULL,
  `slot` int(11) NOT NULL,
  `video_type` varchar (32) NOT NULL,
  `video_id` varchar(32) NOT NULL,
  `media_content_url` varchar(128) NOT NULL,
  `media_content_type` varchar (32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `slot` (`slot`)
) AUTO_INCREMENT=1 ;

--
-- Table structure for table `geodesic_print_publications`
--

DROP TABLE IF EXISTS `geodesic_print_publication`;
CREATE TABLE IF NOT EXISTS `geodesic_print_publication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`),
  KEY `status` (`status`)
) AUTO_INCREMENT=1 ;

--
-- Table structure for table `geodesic_print_publications_languages`
--

DROP TABLE IF EXISTS `geodesic_print_publication_languages`;
CREATE TABLE IF NOT EXISTS `geodesic_print_publication_languages` (
  `id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`language_id`)
);

--
-- Table structure for table `geodesic_print_publish_days`
--

DROP TABLE IF EXISTS `geodesic_print_publish_days`;
CREATE TABLE IF NOT EXISTS `geodesic_print_publish_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publication_id` int(11) NOT NULL,
  `label` varchar(128) NOT NULL,
  `day_of_week` enum('Sun','Mon','Tue','Wed','Thur','Fri','Sat') DEFAULT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `publication_id` (`publication_id`),
  KEY `status` (`status`),
  KEY `sort_order` (`sort_order`)
) AUTO_INCREMENT=1 ;

