ALTER TABLE `geodesic_auctions` RENAME TO `geodesic_classifieds`;
ALTER TABLE `geodesic_auctions_ad_configuration` RENAME TO `geodesic_classifieds_ad_configuration`;
ALTER TABLE `geodesic_auctions_ads_extra` RENAME TO `geodesic_classifieds_ads_extra`;
ALTER TABLE `geodesic_auctions_configuration` RENAME TO `geodesic_classifieds_configuration`;
ALTER TABLE `geodesic_auctions_credit_choices` RENAME TO `geodesic_classifieds_credit_choices`;
ALTER TABLE `geodesic_auctions_expirations` RENAME TO `geodesic_classifieds_expirations`;
ALTER TABLE `geodesic_auctions_expired` RENAME TO `geodesic_classifieds_expired`;
ALTER TABLE `geodesic_auctions_filters` RENAME TO `geodesic_classifieds_filters`;
ALTER TABLE `geodesic_auctions_price_plans` RENAME TO `geodesic_classifieds_price_plans`;
ALTER TABLE `geodesic_auctions_price_plans_categories` RENAME TO `geodesic_classifieds_price_plans_categories`;
ALTER TABLE `geodesic_auctions_price_plans_extras` RENAME TO `geodesic_classifieds_price_plans_extras`;
ALTER TABLE `geodesic_auctions_sell_question_choices` RENAME TO `geodesic_classifieds_sell_question_choices`;
ALTER TABLE `geodesic_auctions_sell_question_types` RENAME TO `geodesic_classifieds_sell_question_types`;
ALTER TABLE `geodesic_auctions_sell_questions` RENAME TO `geodesic_classifieds_sell_questions`;
ALTER TABLE `geodesic_auctions_sell_session` RENAME TO `geodesic_classifieds_sell_session`;
ALTER TABLE `geodesic_auctions_sell_session_images` RENAME TO `geodesic_classifieds_sell_session_images`;
ALTER TABLE `geodesic_auctions_sell_session_questions` RENAME TO `geodesic_classifieds_sell_session_questions`;
ALTER TABLE `geodesic_auctions_subscription_choices` RENAME TO `geodesic_classifieds_subscription_choices`;
ALTER TABLE `geodesic_auctions_user_credits` RENAME TO `geodesic_classifieds_user_credits`;
ALTER TABLE `geodesic_auctions_user_subscriptions` RENAME TO `geodesic_classifieds_user_subscriptions`;
ALTER TABLE `geodesic_auctions_user_subscriptions_holds` RENAME TO `geodesic_classifieds_user_subscriptions_holds`;
ALTER TABLE `geodesic_categories_languages` RENAME TO `geodesic_classifieds_categories_languages`;
ALTER TABLE `geodesic_discount_codes` RENAME TO `geodesic_classifieds_discount_codes`;
ALTER TABLE `geodesic_images` RENAME TO `geodesic_classifieds_images`;
ALTER TABLE `geodesic_images_urls` RENAME TO `geodesic_classifieds_images_urls`;
ALTER TABLE `geodesic_messages_form` RENAME TO `geodesic_classifieds_messages_form`;
ALTER TABLE `geodesic_messages_past` RENAME TO `geodesic_classifieds_messages_past`;
ALTER TABLE `geodesic_messages_past_recipients` RENAME TO `geodesic_classifieds_messages_past_recipients`;
ALTER TABLE `geodesic_price_plan_auction_lengths` RENAME TO `geodesic_price_plan_ad_lengths`;
ALTER TABLE `geodesic_categories` CHANGE COLUMN `url_link_1` `use_url_link_1` INTEGER NOT NULL DEFAULT 0,  /* DANGEROUS */
	CHANGE COLUMN `url_link_2` `use_url_link_2` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `url_link_3` `use_url_link_3` INTEGER NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_classifieds_ads_extra` CHANGE COLUMN `auction_id` `classified_id` INTEGER NOT NULL DEFAULT 0;  /* DOESN'T EXIST? */
ALTER TABLE `geodesic_classifieds_ad_configuration` CHANGE COLUMN `user_auction_template` `user_ad_template` INTEGER NOT NULL DEFAULT 0; /* DANGEROUS */
ALTER TABLE `geodesic_classifieds_categories_languages` CHANGE COLUMN `auction_template_id` `template_id` INTEGER NOT NULL DEFAULT 0,  /* DANGEROUS */
	CHANGE COLUMN `auction_secondary_template_id` `secondary_template_id` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `auction_display_template_id` `ad_display_template_id` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `auction_detail_full_image_display_template_id` `ad_detail_full_image_display_template_id` INTEGER NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_classifieds_configuration` CHANGE COLUMN `auctions_url` `classifieds_url` TINYTEXT NOT NULL DEFAULT '',  /* DOESN'T EXIST? */
	CHANGE COLUMN `auctions_file_name` `classifieds_file_name` TINYTEXT NOT NULL DEFAULT '',
	CHANGE COLUMN `auctions_ssl_url` `classifieds_ssl_url` TINYTEXT NOT NULL DEFAULT '',
	CHANGE COLUMN `all_auctions_are_free` `all_ads_are_free` TINYINT(4) NOT NULL DEFAULT 0,
	CHANGE COLUMN `send_auction_expire_email` `send_ad_expire_email` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `featured_pic_auction_column_count` `featured_pic_ad_column_count` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `featured_auction_page_count` `featured_ad_page_count` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `admin_approves_all_auctions` `admin_approves_all_ads` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `subscription_to_view_or_bid_auctions` `subscription_to_view_or_bid_ads` INT(1) NOT NULL DEFAULT 0,
	CHANGE COLUMN `category_new_auction_image` `category_new_ad_image` TINYTEXT NOT NULL DEFAULT '',
	CHANGE COLUMN `category_new_auction_limit` `category_new_ad_limit` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `BUY_NOW_RESERVE` `buy_now_reserve` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `EDIT_BEGIN` `edit_begin` INTEGER NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_classifieds_images` CHANGE COLUMN `auction_id` `classified_id` INTEGER NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_classifieds_images_urls` CHANGE COLUMN `auction_id` `classified_id` INTEGER NOT NULL DEFAULT 0; /* DANGEROUS */
ALTER TABLE `geodesic_classifieds_sell_session` CHANGE COLUMN `auction_id` `classified_id` INTEGER NOT NULL DEFAULT 0,
	CHANGE COLUMN `auction_length` `classified_length` DOUBLE(8,2) NOT NULL DEFAULT 0.00,  /* DOESN'T EXIST? */
	CHANGE COLUMN `auction_details_collected` `classified_details_collected` TINYINT(4) NOT NULL DEFAULT 0,
	CHANGE COLUMN `auction_images_collected` `classified_images_collected` TINYINT(4) NOT NULL DEFAULT 0,
	CHANGE COLUMN `auction_approved` `classified_approved` TINYINT(4) NOT NULL DEFAULT 0,
	CHANGE COLUMN `auction_title` `classified_title` TINYTEXT NOT NULL DEFAULT '';
ALTER TABLE `geodesic_user_communications` CHANGE COLUMN `regarding_auction` `regarding_ad` INTEGER NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_choices` MODIFY COLUMN `numeric_value` SMALLINT(6) NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_classifieds` MODIFY COLUMN `subtotal` DOUBLE(5,2) NOT NULL DEFAULT 0.00,
	MODIFY COLUMN `tax` DOUBLE(5,2) NOT NULL DEFAULT 0.00,
	MODIFY COLUMN `total` DOUBLE(5,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `geodesic_classifieds_ad_configuration` MODIFY COLUMN `title_length` INTEGER NOT NULL DEFAULT 50,  /* DANGEROUS */
	MODIFY COLUMN `price_length` INTEGER NOT NULL DEFAULT 12,
	MODIFY COLUMN `city_length` INTEGER NOT NULL DEFAULT 20,
	MODIFY COLUMN `zip_length` INTEGER NOT NULL DEFAULT 10,
	MODIFY COLUMN `phone_1_length` INTEGER NOT NULL DEFAULT 12,
	MODIFY COLUMN `phone_2_length` INTEGER NOT NULL DEFAULT 12,
	MODIFY COLUMN `fax_length` INTEGER NOT NULL DEFAULT 12;
ALTER TABLE `geodesic_classifieds_configuration` MODIFY COLUMN `send_ad_expire_email` TINYINT(4) NOT NULL DEFAULT 0,  /* DANGEROUS */
	MODIFY COLUMN `bid_history_link_live` INTEGER(11) NOT NULL DEFAULT 0,
	MODIFY COLUMN `subscription_to_view_or_bid_ads` INT(11) NOT NULL DEFAULT 0,
	MODIFY COLUMN `user_set_hold_email` INT(11) NOT NULL DEFAULT 0;
ALTER TABLE `geodesic_classifieds_price_plans` MODIFY COLUMN `credits_upon_registration` INTEGER(11) NOT NULL DEFAULT 0,  /* DANGEROUS */
	MODIFY COLUMN `initial_site_balance` DOUBLE(5,2) NOT NULL DEFAULT 0.00;
ALTER TABLE `geodesic_classifieds_sell_session` MODIFY COLUMN `auction_type` TINYINT(1) NOT NULL DEFAULT 1,
	MODIFY COLUMN `auction_quantity` INTEGER NOT NULL DEFAULT 1,
	MODIFY COLUMN `classified_length` INTEGER(11) NOT NULL DEFAULT 0,
	MODIFY COLUMN `auction_reserve` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
	MODIFY COLUMN `auction_minimum` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
	MODIFY COLUMN `auction_buy_now` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
	MODIFY COLUMN `payment_options` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_pages` MODIFY COLUMN `module_number_of_ads_to_display` INTEGER(11) NOT NULL DEFAULT 0;  /* DANGEROUS */
ALTER TABLE `geodesic_registration_configuration` MODIFY COLUMN `firstname_maxlength` INTEGER,  /* DANGEROUS */
	MODIFY COLUMN `lastname_maxlength` INTEGER,
	MODIFY COLUMN `company_name_maxlength` INTEGER,
	MODIFY COLUMN `address_maxlength` INTEGER,
	MODIFY COLUMN `address_2_maxlength` INTEGER,
	MODIFY COLUMN `phone_maxlength` INTEGER,
	MODIFY COLUMN `phone_2_maxlength` INTEGER,
	MODIFY COLUMN `fax_maxlength` INTEGER,
	MODIFY COLUMN `city_maxlength` INTEGER,
	MODIFY COLUMN `zip_maxlength` INTEGER,
	MODIFY COLUMN `url_maxlength` INTEGER,
	MODIFY COLUMN `optional_1_maxlength` INTEGER,
	MODIFY COLUMN `optional_2_maxlength` INTEGER,
	MODIFY COLUMN `optional_3_maxlength` INTEGER,
	MODIFY COLUMN `optional_4_maxlength` INTEGER,
	MODIFY COLUMN `optional_5_maxlength` INTEGER,
	MODIFY COLUMN `optional_6_maxlength` INTEGER,
	MODIFY COLUMN `optional_7_maxlength` INTEGER,
	MODIFY COLUMN `optional_8_maxlength` INTEGER,
	MODIFY COLUMN `optional_9_maxlength` INTEGER,
	MODIFY COLUMN `optional_10_maxlength` INTEGER;
ALTER TABLE `geodesic_templates` MODIFY COLUMN `last_template` LONGTEXT NOT NULL DEFAULT ''; /* DANGEROUS */
ALTER TABLE `geodesic_userdata` MODIFY COLUMN `feedback_icon` VARCHAR(255) NOT NULL DEFAULT ''; /* DANGEROUS */
ALTER TABLE `geodesic_classifieds` DROP INDEX `description`;
CREATE TABLE `geodesic_banned_ips` (
	`ip_id` int(11) NOT NULL auto_increment,
	`ip` varchar(25) NOT NULL default '',
	PRIMARY KEY  (`ip_id`)
);
CREATE TABLE `geodesic_cc_internetsecure` (
	`merchantnumber` tinytext NOT NULL,
	`language` tinytext NOT NULL,
	`demo_mode` tinytext NOT NULL,
	`canadian_tax_method` tinytext NOT NULL
);
CREATE TABLE `geodesic_cc_internetsecure_transactions` (
	`internetsecure_transaction_id` int(11) NOT NULL auto_increment,
	`classified_id` int(11) NOT NULL default '0',
	`user_id` int(11) NOT NULL default '0',
	`credit_card_processed` int(11) NOT NULL default '0',
	`first_name` tinytext NOT NULL,
	`last_name` tinytext NOT NULL,
	`address` tinytext NOT NULL,
	`city` tinytext NOT NULL,
	`state` tinytext NOT NULL,
	`country` tinytext NOT NULL,
	`zip` varchar(15) NOT NULL default '',
	`email` tinytext NOT NULL,
	`card_num` varchar(25) NOT NULL default '',
	`decryption_key` varchar(25) NOT NULL default '',
	`exp_date` varchar(10) NOT NULL default '',
	`cvv2_code` varchar(4) NOT NULL default '',
	`tax` double(5,2) NOT NULL default '0.00',
	`amount` double(5,2) NOT NULL default '0.00',
	`phone` varchar(20) NOT NULL default '',
	`fax` varchar(20) NOT NULL default '',
	`company` tinytext NOT NULL,
	`description` tinytext NOT NULL,
	`merchantnumber` tinytext NOT NULL,
	`currency` tinytext NOT NULL,
	`salesordernumber` tinytext NOT NULL,
	`receipt_number` tinytext NOT NULL,
	`approvalcode` tinytext NOT NULL,
	`verbage` tinytext NOT NULL,
	`niceverbage` tinytext NOT NULL,
	`cvv2result` tinytext NOT NULL,
	`avsresponsecode` tinytext NOT NULL,
	`products` tinytext NOT NULL,
	`doublecolonproducts` tinytext NOT NULL,
	`language` tinytext NOT NULL,
	`keysize` tinytext NOT NULL,
	`secretkeysize` tinytext NOT NULL,
	`useragent` tinytext NOT NULL,
	`entrytimestamp` tinytext NOT NULL,
	`unixtimestamp` tinytext NOT NULL,
	`timestamp` tinytext NOT NULL,
	`live` tinytext NOT NULL,
	`refererurl` tinytext NOT NULL,
	`ipaddress` tinytext NOT NULL,
	`returnurl` tinytext NOT NULL,
	`returncgi` tinytext NOT NULL,
	`var1` tinytext NOT NULL,
	`var2` tinytext NOT NULL,
	`var3` tinytext NOT NULL,
	`var4` tinytext NOT NULL,
	`var5` tinytext NOT NULL,
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
	UNIQUE KEY `internetsecure_transaction_id` (`internetsecure_transaction_id`)
);
CREATE TABLE `geodesic_cc_manual_transactions` (
	`manual_transaction_id` int(11) NOT NULL auto_increment,
	`classified_id` int(11) NOT NULL default '0',
	`user_id` int(11) NOT NULL default '0',
	`card_num` varchar(25) NOT NULL default '',
	`decryption_key` varchar(25) NOT NULL default '',
	`exp_date` varchar(10) NOT NULL default '',
	`first_name` tinytext NOT NULL,
	`last_name` tinytext NOT NULL,
	`address` tinytext NOT NULL,
	`city` tinytext NOT NULL,
	`state` tinytext NOT NULL,
	`country` tinytext NOT NULL,
	`zip` varchar(15) NOT NULL default '',
	`email` tinytext NOT NULL,
	`tax` double(5,2) NOT NULL default '0.00',
	`amount` double(5,2) NOT NULL default '0.00',
	`fax` varchar(20) NOT NULL default '',
	`company` tinytext NOT NULL,
	`description` tinytext NOT NULL,
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
	`cvv2_code` varchar(4) NOT NULL default '',
	UNIQUE KEY `manual_transaction_id` (`manual_transaction_id`)
);
CREATE TABLE `geodesic_cc_payflow_pro` (
	`partner` tinytext NOT NULL,
	`vendor` tinytext NOT NULL,
	`user` tinytext NOT NULL,
	`password` tinytext NOT NULL,
	`demo_mode` int(11) NOT NULL default '0'
);
CREATE TABLE `geodesic_cc_payflow_pro_transactions` (
	`payflow_pro_transaction_id` int(11) NOT NULL auto_increment,
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
	`decryption_key` varchar(25) NOT NULL default '',
	`exp_date` varchar(10) NOT NULL default '',
	`tax` double(5,2) NOT NULL default '0.00',
	`amount` double(5,2) NOT NULL default '0.00',
	`fax` varchar(20) NOT NULL default '',
	`company` tinytext NOT NULL,
	`description` tinytext NOT NULL,
	`pnref` tinytext NOT NULL,
	`result` int(11) NOT NULL default '0',
	`respmsg` tinytext NOT NULL,
	`authcode` tinytext NOT NULL,
	`avsaddr` tinytext NOT NULL,
	`avszip` tinytext NOT NULL,
	`trans_id` tinytext NOT NULL,
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
	UNIQUE KEY `payflow_pro_transaction_id` (`payflow_pro_transaction_id`)
);
CREATE TABLE `geodesic_cc_paypal` (
	`api_username` tinytext NOT NULL,
	`api_password` tinytext NOT NULL,
	`certfile` tinytext NOT NULL,
	`currency_id` tinytext NOT NULL,
	`charset` tinytext NOT NULL
);
CREATE TABLE `geodesic_cc_paypal_transactions` (
	`transaction_id` int(11) NOT NULL auto_increment,
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
	`decryption_key` varchar(25) NOT NULL default '',
	`exp_date` varchar(10) NOT NULL default '',
	`tax` double(5,2) NOT NULL default '0.00',
	`fax` varchar(20) NOT NULL default '',
	`company` tinytext NOT NULL,
	`description` tinytext NOT NULL,
	`amount` double(5,2) NOT NULL default '0.00',
	`avs_code` varchar(4) NOT NULL default '',
	`cvv2_code` varchar(4) NOT NULL default '',
	`trans_id` tinytext NOT NULL,
	`timestamp` tinytext NOT NULL,
	`ack` tinytext NOT NULL,
	`version` tinytext NOT NULL,
	`build` tinytext NOT NULL,
	`error_short_msg` tinytext NOT NULL,
	`error_long_msg` mediumtext NOT NULL,
	`error_code` tinytext NOT NULL,
	`error_severity_code` tinytext NOT NULL,
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
	UNIQUE KEY `transaction_id` (`transaction_id`)
);
CREATE TABLE `geodesic_classifieds_filters_languages` (
	`filter_id` int(11) NOT NULL default '0',
	`filter_name` tinytext NOT NULL,
	`language_id` int(11) NOT NULL default '0'
);
CREATE TABLE `geodesic_classifieds_price_increments` (
	`price_plan_id` int(11) NOT NULL default '0',
	`category_id` int(11) NOT NULL default '0',
	`low` double(16,2) default NULL,
	`high` double(16,2) default NULL,
	`charge` double(16,2) default NULL,
	`renewal_charge` double(16,2) NOT NULL default '0.00'
);
CREATE TABLE `geodesic_classifieds_transactions` (
	`transaction_id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default '0',
	`classified_id` int(11) NOT NULL default '0',
	`payment_type` tinyint(4) NOT NULL default '0',
	`type_of_billing` tinyint(4) NOT NULL default '0',
	`bolding_choice` tinyint(4) NOT NULL default '0',
	`better_placement_choice` tinyint(4) NOT NULL default '0',
	`featured_ad_choice` tinyint(4) NOT NULL default '0',
	`bolding_price` double(5,2) NOT NULL default '0.00',
	`better_placement_charge` double(5,2) NOT NULL default '0.00',
	`featured_ad_price` double(5,2) NOT NULL default '0.00',
	`tax` double(5,2) NOT NULL default '0.00',
	`total` double(5,2) NOT NULL default '0.00',
	UNIQUE KEY `transaction_id` (`transaction_id`)
);
CREATE TABLE `geodesic_classifieds_user_configuration` (
	`user_id` int(11) NOT NULL default '0',
	`communication_type` tinyint(4) NOT NULL default '0',
	PRIMARY KEY  (`user_id`),
	KEY `user_id_2` (`user_id`)
);
CREATE TABLE `geodesic_classifieds_votes` (
	`classified_id` int(11) NOT NULL default '0',
	`user_id` int(11) NOT NULL default '0',
	`voter_ip` varchar(20) NOT NULL default '',
	`vote` int(11) NOT NULL default '0',
	`vote_title` tinytext NOT NULL,
	`vote_comments` tinytext NOT NULL,
	`date_entered` int(14) NOT NULL default '0'
);
CREATE TABLE `geodesic_feedbacks` (
	`rated_user_id` int(11) default NULL,
	`rater_user_id` int(11) default NULL,
	`feedback` tinytext,
	`rate` int(11) default '0',
	`date` int(14) default NULL,
	`auction_id` int(11) NOT NULL default '0',
	`done` tinyint(4) NOT NULL default '0'
);
CREATE TABLE `geodesic_nochex` (
	`demo_mode` int(1) NOT NULL default '0',
	`logo_path` tinytext NOT NULL,
	`email` tinytext NOT NULL
);
CREATE TABLE `geodesic_nochex_transactions` (
	`nochex_transaction_id` int(11) NOT NULL auto_increment,
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
	`tax` double(5,2) NOT NULL default '0.00',
	`amount` double(5,2) NOT NULL default '0.00',
	`fax` varchar(20) NOT NULL default '',
	`company` tinytext NOT NULL,
	`description` tinytext NOT NULL,
	`response` tinytext NOT NULL,
	`security_key` tinytext NOT NULL,
	`transaction_date` tinytext NOT NULL,
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
	UNIQUE KEY `nochex_transaction_id` (`nochex_transaction_id`)
);
CREATE TABLE `geodesic_storefront_categories` (
	`category_id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default '0',
	`category_name` text NOT NULL,
	`display_order` int(11) NOT NULL default '0',
	PRIMARY KEY  (`category_id`)
);
CREATE TABLE `geodesic_storefront_display` (
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
CREATE TABLE `geodesic_storefront_pages` (
	`page_id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default '0',
	`page_link_text` text NOT NULL,
	`page_name` text NOT NULL,
	`page_body` text NOT NULL,
	PRIMARY KEY  (`page_id`)
);
CREATE TABLE `geodesic_storefront_subscriptions` (
	`subscription_id` int(11) NOT NULL auto_increment,
	`expiration` int(11) NOT NULL default '0',
	`user_id` int(11) NOT NULL default '0',
	PRIMARY KEY  (`subscription_id`)
);
CREATE TABLE `geodesic_storefront_subscriptions_choices` (
	`period_id` int(14) NOT NULL auto_increment,
	`display_value` tinytext NOT NULL,
	`value` int(11) NOT NULL default '0',
	`amount` double(5,2) NOT NULL default '0.00',
	PRIMARY KEY  (`period_id`),
	KEY `id` (`period_id`)
);
CREATE TABLE `geodesic_storefront_users` (
	`store_id` int(11) NOT NULL default '0',
	`user_id` int(11) NOT NULL default '0'
);
ALTER TABLE `geodesic_categories` ADD `auction_category_count` INT(11) NOT NULL DEFAULT '0',
	ADD `display_ad_title` INT(11) NOT NULL DEFAULT '1',
	ADD `use_buy_now` INT(11) NOT NULL DEFAULT '0',
	ADD `payment_types` INT(11) NOT NULL DEFAULT '0',
	ADD `editable_bid_start_time_field` INT(11) NOT NULL DEFAULT '0',
	ADD `default_display_order_while_browsing_category` INT(11) NOT NULL DEFAULT '0',
	ADD `listing_types_allowed` INT(11) NOT NULL DEFAULT '0',
	ADD `display_storefront_link` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_cc_authorizenet_transactions` ADD `decryption_key` TINYTEXT NOT NULL;
ALTER TABLE `geodesic_cc_bitel_transactions` ADD `decryption_key` TINYTEXT NOT NULL;
ALTER TABLE `geodesic_cc_linkpoint_transactions` ADD `decryption_key` TINYTEXT NOT NULL;
ALTER TABLE `geodesic_classifieds` ADD `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
	ADD `expiration_last_sent` INT(11) NOT NULL DEFAULT '0',
	ADD `paypal_id` TINYTEXT NOT NULL,
	ADD `print_title` TINYTEXT NOT NULL,
	ADD `print_description` MEDIUMTEXT NOT NULL,
	ADD `print` INT(11) NOT NULL DEFAULT '0',
	ADD `item_type` INT(11) NOT NULL DEFAULT '2',
	ADD `storefront_category` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_ad_configuration` ADD `auctions_user_ad_template` INT(11) NOT NULL DEFAULT '0',
	ADD `auctions_user_extra_template` INT(11) NOT NULL DEFAULT '0',
	ADD `auctions_user_checkbox_template` INT(11) NOT NULL DEFAULT '0',
	ADD `ad_detail_print_friendly_template` INT(11) NOT NULL DEFAULT '0',
	ADD `print_title_length` INT(11) NOT NULL DEFAULT '100',
	ADD `title_module_text` TEXT NOT NULL ,
	ADD `use_buy_now` TINYINT(11) NOT NULL DEFAULT '0',
	ADD `editable_buy_now` TINYINT(11) NOT NULL DEFAULT '0',
	ADD `require_buy_now` TINYINT(11) NOT NULL DEFAULT '0',
	ADD `clientside_image_uploader_view` TINYINT(4) NOT NULL DEFAULT '0',
	ADD `image_uploader_default` TINYINT(4) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_categories_languages` ADD `ad_detail_display_template_id` INT(11) NOT NULL DEFAULT '0',
	ADD `ad_detail_extra_display_template_id` INT(11) NOT NULL DEFAULT '0',
	ADD `ad_detail_checkbox_display_template_id` INT(11) NOT NULL DEFAULT '0',
	ADD `ad_detail_print_friendly_template` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_configuration` ADD `browsing_count_format` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `sold_image` TINYTEXT NOT NULL ,
	ADD `send_ad_expire_frequency` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `upgrade_time` INT( 1 ) NOT NULL DEFAULT '0',
	ADD `maximum_print_description_length` INT( 11 ) NOT NULL DEFAULT '1000',
	ADD `no_image_url` TINYTEXT NOT NULL ,
	ADD `display_ad_title` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `edit_reset_date` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `site_on_off` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `disable_site_url` TINYTEXT NOT NULL ,
	ADD `time_shift` INT( 2 ) NOT NULL DEFAULT '0',
	ADD `listing_type_allowed` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `auction_entry_date` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `classified_time_left` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `url_rewrite` TINYINT( 4 ) NOT NULL DEFAULT '0',
	ADD `optional_field_1_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 1',
	ADD `optional_field_2_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 2',
	ADD `optional_field_3_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 3',
	ADD `optional_field_4_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 4',
	ADD `optional_field_5_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 5',
	ADD `optional_field_6_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 6',
	ADD `optional_field_7_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 7',
	ADD `optional_field_8_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 8',
	ADD `optional_field_9_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 9',
	ADD `optional_field_10_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 10',
	ADD `optional_field_11_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 11',
	ADD `optional_field_12_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 12',
	ADD `optional_field_13_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 13',
	ADD `optional_field_14_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 14',
	ADD `optional_field_15_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 15',
	ADD `optional_field_16_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 16',
	ADD `optional_field_17_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 17',
	ADD `optional_field_18_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 18',
	ADD `optional_field_19_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 19',
	ADD `optional_field_20_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Optional Field 20',
	ADD `default_display_order_while_browsing` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `ip_ban_check` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `number_of_feedbacks_to_display` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `display_storefront_link` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `storefront_url` TINYTEXT NOT NULL ,
	ADD `member_since_date_configuration` VARCHAR( 20 ) NOT NULL DEFAULT 'F Y';
ALTER TABLE `geodesic_classifieds_discount_codes` ADD `user_id` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_expired` ADD `high_bidder` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `item_type` INT( 11 ) NOT NULL DEFAULT '2';
ALTER TABLE `geodesic_classifieds_images` ADD `mime_type` VARCHAR( 30 ) NOT NULL;
ALTER TABLE `geodesic_classifieds_images_urls` ADD `mime_type` VARCHAR( 30 ) NOT NULL;
ALTER TABLE `geodesic_classifieds_price_plans` ADD `use_featured_ads` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_2` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_3` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_4` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_5` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_bolding` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_better_placement` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_attention_getters` INT( 11 ) NOT NULL DEFAULT '1';
ALTER TABLE `geodesic_classifieds_sell_session` ADD `decryption_key` TINYTEXT NOT NULL ,
	ADD `paypal_id` TINYTEXT NOT NULL ,
	ADD `print` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `print_title` TINYTEXT NOT NULL ,
	ADD `print_description` MEDIUMTEXT NOT NULL ,
	ADD `print_web_approved` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `cvv2_code` VARCHAR( 4 ) NOT NULL ,
	ADD `type` INT( 1 ) NOT NULL DEFAULT '0',
	ADD `storefront` TINYINT( 4 ) NOT NULL DEFAULT '0',
	ADD `storefront_category` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_user_subscriptions` ADD `price_plan_id` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_user_subscriptions_holds` ADD `storefront` TINYINT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_countries` ADD `tax` DOUBLE NOT NULL DEFAULT '0',
	ADD `tax_type` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_favorites` ADD `classified_id` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_group_attached_price_plans` ADD `applies_to` INT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_classifieds_price_plans` ADD `applies_to` INT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_groups` ADD `storefront` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_pages`
	CHANGE `module_display_auction_id` `module_display_classified_id` INT( 11 ) NOT NULL DEFAULT '0' AFTER `module_use_image`,
	ADD `browsing_count_format` INT( 11 ) NOT NULL DEFAULT '0' AFTER `display_category_count`,
	ADD `module_display_new_ad_icon` INT( 11 ) NOT NULL DEFAULT '0' AFTER `module_category`,
	ADD `module_display_type_listing` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `module_display_type_text` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `module_text_type` varchar(50) NOT NULL after `module_display_title`,
	ADD `module_display_listing_column` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `admin_label` VARCHAR( 50 ) NOT NULL ,
	ADD `extra_page_text` LONGTEXT NOT NULL ,
	ADD `applies_to` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `maxNodeDepth` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `module_display_company_name` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_pages_sections` ADD `applies_to` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_pages_templates_affiliates` ADD `auctions_display_template_id` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `auctions_extra_question_template_id` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `auctions_checkbox_question_template_id` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_paypal_transactions` ADD `storefront` TINYINT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_registration_configuration` ADD `registration_optional_1_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 1',
	ADD `registration_optional_2_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 2',
	ADD `registration_optional_3_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 3',
	ADD `registration_optional_4_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 4',
	ADD `registration_optional_5_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 5',
	ADD `registration_optional_6_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 6',
	ADD `registration_optional_7_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 7',
	ADD `registration_optional_8_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 8',
	ADD `registration_optional_9_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 9',
	ADD `registration_optional_10_field_name` VARCHAR( 50 ) NOT NULL DEFAULT 'Reg Optional Field 10';
ALTER TABLE `geodesic_sessions` ADD `securityString` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `geodesic_states` ADD `tax` DOUBLE NOT NULL DEFAULT '0',
	ADD `tax_type` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_templates` ADD `applies_to` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `storefront_template` TINYINT( 4 ) NOT NULL DEFAULT '0',
	ADD `storefront_template_default` TINYINT( 4 ) NOT NULL DEFAULT '0';
ALTER TABLE `geodesic_userdata` ADD `rate_sum` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `rate_num` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `storefront_header` TEXT NOT NULL ,
	ADD `storefront_template_id` INT( 11 ) NOT NULL DEFAULT '0',
	ADD `storefront_welcome_message` TEXT NOT NULL ,
	ADD `storefront_on_hold` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `storefront_home_link` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `geodesic_classifieds_price_plans_categories` ADD `use_featured_ads` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_2` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_3` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_4` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_featured_ads_level_5` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_bolding` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_better_placement` INT( 11 ) NOT NULL DEFAULT '1',
	ADD `use_attention_getters` INT( 11 ) NOT NULL DEFAULT '1';