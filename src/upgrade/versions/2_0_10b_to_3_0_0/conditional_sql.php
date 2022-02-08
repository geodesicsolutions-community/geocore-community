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

// phpcs:disable Generic.Files.LineLength.TooLong

$sql_not_strict [] = 'ALTER TABLE `geodesic_classifieds_sell_session` ADD COLUMN new_pictures int(3) NOT NULL DEFAULT 0';
$sql_not_strict [] = 'ALTER TABLE `geodesic_auctions_feedbacks` ADD COLUMN `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY(`id`)';
$sql_not_strict [] = 'ALTER TABLE `geodesic_groups` ADD COLUMN `restrictions_bitmask` int(4) NOT NULL DEFAULT 63';
#add new allow_site_balance column, and make default to 1.
$sql_not_strict [] = 'ALTER TABLE `geodesic_groups` ADD COLUMN `allow_site_balance` TINYINT(1) NOT NULL DEFAULT 1';
//Fix default for current sites that already have default set to 0
$sql_not_strict [] = 'ALTER TABLE `geodesic_groups` CHANGE `allow_site_balance` `allow_site_balance` TINYINT( 1 ) NOT NULL DEFAULT 1';

#Add content_type to allow setting content to text/html for messages.
$sql_not_strict [] = "ALTER IGNORE TABLE `geodesic_classifieds_messages_form` ADD `content_type` VARCHAR( 20 ) NOT NULL DEFAULT 'text/plain'";
$sql_not_strict [] = "ALTER IGNORE TABLE `geodesic_classifieds_messages_past` ADD `content_type` VARCHAR( 20 ) NOT NULL DEFAULT 'text/plain'";

//Add column for ssl_ip
$sql_not_strict [] = "ALTER TABLE `geodesic_sessions` ADD `ip_ssl` VARCHAR( 40 ) NOT NULL DEFAULT '0' AFTER `ip`";
$sql_not_strict [] = "ALTER TABLE `geodesic_sessions` CHANGE `ip` `ip` VARCHAR( 40 ) NOT NULL DEFAULT '0'";

//Add fullpage option to template db.
$sql_not_strict [] = "ALTER TABLE `geodesic_templates` ADD `full_page` TINYINT( 3 ) NOT NULL DEFAULT '1'";
//make templates that do not appear to be full pages, partial pages.
$sql_not_strict [] = "UPDATE `geodesic_templates` SET `full_page` = 0 WHERE `template_code` NOT LIKE '%<head%' OR `template_code` NOT LIKE '%<html%'";

//Adding more PHP modules
$page_id = 10185;
$php_mod_num = 7;
while ($php_mod_num < 21) {
    $sql_not_strict [] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_auction_id`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `extra_page_text`, `applies_to`, `maxNodeDepth`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`) VALUES ({$page_id}, '0', 'PHP Code Module {$php_mod_num}', 'Insert your own php to be executed within this module', '', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 'module_display_php.php', '(!MODULE_PHP_INSERT_{$php_mod_num}!)', '0', '0', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '', '0', '0', '0', '0', '0', '0', '1', '', '', '0', '0', '0', '0', '5', '0', '0', '0', '0', '0', '0', '0', '', '', '0', '0', '0', '0', NULL, ',', '   >> sub|cat|list')";
    $php_mod_num++;
    $page_id++;
}

//enable free pics in category-specific price plans
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_price_plans_categories` ADD COLUMN `num_free_pics` INT(11) NOT NULL DEFAULT 0";

//allow larger site balances for different currencies
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_price_plans` CHANGE `initial_site_balance` `initial_site_balance` DOUBLE( 11, 2 ) DEFAULT '0.00' NOT NULL";
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` CHANGE `account_balance` `account_balance` DOUBLE( 11, 2 ) DEFAULT '0.00' NOT NULL";

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_subscription_choices` CHANGE `amount` `amount` DOUBLE( 11, 2 ) DEFAULT '0.00' NOT NULL";


//Change the tags to (!!)
$tagsOld = array(
"<<MAINBODY>>",
"<<CSSSTYLESHEET>>",
"<<FULL_SIZE_IMAGE>>",
"<<FULL_SIZE_TEXT>>",
"<<CATEGORY_TREE>>",
"<<TITLE>>",
"<<VIEWED_COUNT_LABEL>>",
"<<VIEWED_COUNT>>",
"<<SELLER_LABEL>>",
"<<SELLER>>",
"<<MEMBER_SINCE>>",
"<<DATE_STARTED_LABEL>>",
"<<DATE_STARTED>>",
"<<CITY_LABEL>>",
"<<CITY_DATA>>",
"<<STATE_LABEL>>",
"<<STATE_DATA>>",
"<<COUNTRY_LABEL>>",
"<<COUNTRY_DATA>>",
"<<ZIP_LABEL>>",
"<<ZIP_DATA>>",
"<<PRICE_LABEL>>",
"<<PRICE>>",
"<<PUBLIC_EMAIL_LABEL>>",
"<<PUBLIC_EMAIL>>",
"<<PHONE_LABEL>>",
"<<PHONE_DATA>>",
"<<PHONE2_LABEL>>",
"<<PHONE2_DATA>>",
"<<FAX_LABEL>>",
"<<FAX_DATA>>",
"<<URL_LINK_1>>",
"<<URL_LINK_2>>",
"<<URL_LINK_3>>",
"<<DESCRIPTION_LABEL>>",
"<<DESCRIPTION>>",
"<<EXTRA_QUESTION_BLOCK>>",
"<<EXTRA_CHECKBOX_BLOCK>>",
"<<IMAGE_BLOCK>>",
"<<LEAD_PICTURE>>",
"<<NOTIFY_FRIEND_LINK>>",
"<<MESSAGE_TO_SELLER_LINK>>",
"<<FAVORITES_LINK>>",
"<<SELLERS_OTHER_ADS_LINK>>",
"<<FULL_IMAGES_LINK>>",
"<<PRINT_FRIENDLY_LINK>>",
"<<SPONSORED_BY>>",
"<<PREVIOUS_AD_LINK>>",
"<<NEXT_AD_LINK>>",
"<<RETURN_TO_LIST_LINK>>",
"<<MAPPING_LINK>>",
"<<MAPPING_ADDRESS>>",
"<<MAPPING_ZIP>>",
"<<MAPPING_STATE>>",
"<<MAPPING_CITY>>",
"<<MAPPING_COUNTRY>>",
"<<STOREFRONT_LINK>>",
"<<ADDITIONAL_TEXT_1>>",
"<<ADDITIONAL_TEXT_2>>",
"<<ADDITIONAL_TEXT_3>>",
"<<ADDITIONAL_TEXT_4>>",
"<<ADDITIONAL_TEXT_5>>",
"<<ADDITIONAL_TEXT_6>>",
"<<ADDITIONAL_TEXT_7>>",
"<<ADDITIONAL_TEXT_8>>",
"<<ADDITIONAL_TEXT_9>>",
"<<ADDITIONAL_TEXT_10>>",
"<<ADDITIONAL_TEXT_11>>",
"<<ADDITIONAL_TEXT_12>>",
"<<ADDITIONAL_TEXT_13>>",
"<<ADDITIONAL_TEXT_14>>",
"<<ADDITIONAL_TEXT_15>>",
"<<ADDITIONAL_TEXT_16>>",
"<<ADDITIONAL_TEXT_17>>",
"<<ADDITIONAL_TEXT_18>>",
"<<ADDITIONAL_TEXT_19>>",
"<<ADDITIONAL_TEXT_20>>",
"<<VOTE_ON_AD_LINK>>",
"<<SHOW_AD_VOTE_COMMENTS_LINK>>",
"<<OPTIONAL_FIELD_1_LABEL>>",
"<<OPTIONAL_FIELD_1>>",
"<<OPTIONAL_FIELD_2_LABEL>>",
"<<OPTIONAL_FIELD_2>>",
"<<OPTIONAL_FIELD_3_LABEL>>",
"<<OPTIONAL_FIELD_3>>",
"<<OPTIONAL_FIELD_4_LABEL>>",
"<<OPTIONAL_FIELD_4>>",
"<<OPTIONAL_FIELD_5_LABEL>>",
"<<OPTIONAL_FIELD_5>>",
"<<OPTIONAL_FIELD_6_LABEL>>",
"<<OPTIONAL_FIELD_6>>",
"<<OPTIONAL_FIELD_7_LABEL>>",
"<<OPTIONAL_FIELD_7>>",
"<<OPTIONAL_FIELD_8_LABEL>>",
"<<OPTIONAL_FIELD_8>>",
"<<OPTIONAL_FIELD_9_LABEL>>",
"<<OPTIONAL_FIELD_9>>",
"<<OPTIONAL_FIELD_10_LABEL>>",
"<<OPTIONAL_FIELD_10>>",
"<<OPTIONAL_FIELD_11_LABEL>>",
"<<OPTIONAL_FIELD_11>>",
"<<OPTIONAL_FIELD_12_LABEL>>",
"<<OPTIONAL_FIELD_12>>",
"<<OPTIONAL_FIELD_13_LABEL>>",
"<<OPTIONAL_FIELD_13>>",
"<<OPTIONAL_FIELD_14_LABEL>>",
"<<OPTIONAL_FIELD_14>>",
"<<OPTIONAL_FIELD_15_LABEL>>",
"<<OPTIONAL_FIELD_15>>",
"<<OPTIONAL_FIELD_16_LABEL>>",
"<<OPTIONAL_FIELD_16>>",
"<<OPTIONAL_FIELD_17_LABEL>>",
"<<OPTIONAL_FIELD_17>>",
"<<OPTIONAL_FIELD_18_LABEL>>",
"<<OPTIONAL_FIELD_18>>",
"<<OPTIONAL_FIELD_19_LABEL>>",
"<<OPTIONAL_FIELD_19>>",
"<<OPTIONAL_FIELD_20_LABEL>>",
"<<OPTIONAL_FIELD_20>>",
"<<BID_HISTORY_LINK>>",
"<<PAYMENT_OPTIONS_LABEL>>",
"<<RESERVE>>",
"<<HIGH_BIDDER_LABEL>>",
"<<HIGH_BIDDER>>",
"<<WINNING_DUTCH_BIDDERS_LABEL>>",
"<<WINNING_DUTCH_BIDDERS>>",
"<<NUM_BIDS>>",
"<<NUM_BIDS_LABEL>>",
"<<QUANTITY_LABEL>>",
"<<QUANTITY>>",
"<<AUCTION_TYPE_LABEL>>",
"<<AUCTION_TYPE_DATA>>",
"<<AUCTION_TYPE_HELP>>",
"<<BUY_NOW_LABEL>>",
"<<BUY_NOW_DATA>>",
"<<BUY_NOW_LINK>>",
"<<DATE_ENDED_LABEL>>",
"<<DATE_ENDED>>",
"<<STATS_REMAINING_LABEL>>",
"<<STATS_REMAINING>>",
"<<MINIMUM_LABEL>>",
"<<MINIMUM_BID>>",
"<<STARTING_LABEL>>",
"<<STARTING_BID>>",
"<<SELLER_RATING_LABEL>>",
"<<SELLER_RATING>>",
"<<FEEDBACK_LINK>>",
"<<SELLER_NUMBER_RATES_LABEL>>",
"<<SELLER_NUMBER_RATES>>",
"<<SELLER_RATING_SCALE_EXPLANATION>>",
"<<BID_START_DATE_LABEL>>",
"<<BID_START_DATE>>",
"<<MAKE_BID_LINK>>",
"<<SELLER_FIRST_NAME>>",
"<<SELLER_LAST_NAME>>",
"<<SELLER_URL>>",
"<<SELLER_ADDRESS>>",
"<<SELLER_CITY>>",
"<<SELLER_STATE>>",
"<<SELLER_COUNTRY>>",
"<<SELLER_ZIP>>",
"<<SELLER_PHONE>>",
"<<SELLER_PHONE2>>",
"<<SELLER_FAX>>",
"<<SELLER_COMPANY_NAME>>",
"<<SELLER_OPTIONAL_1>>",
"<<SELLER_OPTIONAL_2>>",
"<<SELLER_OPTIONAL_3>>",
"<<SELLER_OPTIONAL_4>>",
"<<SELLER_OPTIONAL_5>>",
"<<SELLER_OPTIONAL_6>>",
"<<SELLER_OPTIONAL_7>>",
"<<SELLER_OPTIONAL_8>>",
"<<SELLER_OPTIONAL_9>>",
"<<SELLER_OPTIONAL_10>>",
"<<EXTRA_QUESTION_NAME>>",
"<<EXTRA_QUESTION_VALUE>>",
"<<EXTRA_CHECKBOX_NAME>>",
"<<SECTION_TITLE>>",
"<<PAGE_TITLE>>",
"<<DESCRIPTION>>",
"<<ACTIVE_ADS>>",
"<<EXPIRED_ADS>>",
"<<CURRENT_INFO>>",
"<<PLACE_AD>>",
"<<FAVORITES>>",
"<<COMMUNICATIONS>>",
"<<COMMUNICATIONS_CONFIG>>",
"<<SIGNS_AND_FLYERS>>",
"<<RENEW_EXTEND_SUBSCRIPTION>>",
"<<ADD_MONEY_WITH_BALANCE>>",
"<<ADD_MONEY>>",
"<<BALANCE_TRANSACTIONS>>",
"<<PAID_INVOICES>>",
"<<UNPAID_INVOICES>>",
"<<FEEDBACK>>",
"<<CURRENT_BIDS>>",
"<<BLACKLIST_BUYERS>>",
"<<INVITED_BUYERS>>",
"<<TITLE>>",
"<<IMAGE>>",
"<<ADDRESS>>",
"<<CITY>>",
"<<STATE>>",
"<<ZIP>>",
"<<PRICE>>",
"<<AUCTION_ID>>",
"<<CLASSIFIED_ID>>",
"<<DESCRIPTION>>",
"<<PHONE_1>>",
"<<PHONE_2>>",
"<<CONTACT>>",
"<<BUY_NOW_PRICE>>",
"<<STARTING_BID>>",
"<<CLASSIFIED_ID_LABEL>>",
"<<TIME_REMAINING_LABEL>>",
"<<TIME_REMAINING>>",
"<<PAYMENT_OPTIONS>>",
"<<SELLERS_OTHER_AUCTIONS_LINK>>",
"<<AD_FILTERS>>",
"<<PREVIOUS_IMAGE_LINK>>",
"<<NEXT_IMAGE_LINK>>",
"<<DISPLAY_IMAGE>>");
$tagsNew = array(
"(!MAINBODY!)",
"(!CSSSTYLESHEET!)",
"(!FULL_SIZE_IMAGE!)",
"(!FULL_SIZE_TEXT!)",
"(!CATEGORY_TREE!)",
"(!TITLE!)",
"(!VIEWED_COUNT_LABEL!)",
"(!VIEWED_COUNT!)",
"(!SELLER_LABEL!)",
"(!SELLER!)",
"(!MEMBER_SINCE!)",
"(!DATE_STARTED_LABEL!)",
"(!DATE_STARTED!)",
"(!CITY_LABEL!)",
"(!CITY_DATA!)",
"(!STATE_LABEL!)",
"(!STATE_DATA!)",
"(!COUNTRY_LABEL!)",
"(!COUNTRY_DATA!)",
"(!ZIP_LABEL!)",
"(!ZIP_DATA!)",
"(!PRICE_LABEL!)",
"(!PRICE!)",
"(!PUBLIC_EMAIL_LABEL!)",
"(!PUBLIC_EMAIL!)",
"(!PHONE_LABEL!)",
"(!PHONE_DATA!)",
"(!PHONE2_LABEL!)",
"(!PHONE2_DATA!)",
"(!FAX_LABEL!)",
"(!FAX_DATA!)",
"(!URL_LINK_1!)",
"(!URL_LINK_2!)",
"(!URL_LINK_3!)",
"(!DESCRIPTION_LABEL!)",
"(!DESCRIPTION!)",
"(!EXTRA_QUESTION_BLOCK!)",
"(!EXTRA_CHECKBOX_BLOCK!)",
"(!IMAGE_BLOCK!)",
"(!LEAD_PICTURE!)",
"(!NOTIFY_FRIEND_LINK!)",
"(!MESSAGE_TO_SELLER_LINK!)",
"(!FAVORITES_LINK!)",
"(!SELLERS_OTHER_ADS_LINK!)",
"(!FULL_IMAGES_LINK!)",
"(!PRINT_FRIENDLY_LINK!)",
"(!SPONSORED_BY!)",
"(!PREVIOUS_AD_LINK!)",
"(!NEXT_AD_LINK!)",
"(!RETURN_TO_LIST_LINK!)",
"(!MAPPING_LINK!)",
"(!MAPPING_ADDRESS!)",
"(!MAPPING_ZIP!)",
"(!MAPPING_STATE!)",
"(!MAPPING_CITY!)",
"(!MAPPING_COUNTRY!)",
"(!STOREFRONT_LINK!)",
"(!ADDITIONAL_TEXT_1!)",
"(!ADDITIONAL_TEXT_2!)",
"(!ADDITIONAL_TEXT_3!)",
"(!ADDITIONAL_TEXT_4!)",
"(!ADDITIONAL_TEXT_5!)",
"(!ADDITIONAL_TEXT_6!)",
"(!ADDITIONAL_TEXT_7!)",
"(!ADDITIONAL_TEXT_8!)",
"(!ADDITIONAL_TEXT_9!)",
"(!ADDITIONAL_TEXT_10!)",
"(!ADDITIONAL_TEXT_11!)",
"(!ADDITIONAL_TEXT_12!)",
"(!ADDITIONAL_TEXT_13!)",
"(!ADDITIONAL_TEXT_14!)",
"(!ADDITIONAL_TEXT_15!)",
"(!ADDITIONAL_TEXT_16!)",
"(!ADDITIONAL_TEXT_17!)",
"(!ADDITIONAL_TEXT_18!)",
"(!ADDITIONAL_TEXT_19!)",
"(!ADDITIONAL_TEXT_20!)",
"(!VOTE_ON_AD_LINK!)",
"(!SHOW_AD_VOTE_COMMENTS_LINK!)",
"(!OPTIONAL_FIELD_1_LABEL!)",
"(!OPTIONAL_FIELD_1!)",
"(!OPTIONAL_FIELD_2_LABEL!)",
"(!OPTIONAL_FIELD_2!)",
"(!OPTIONAL_FIELD_3_LABEL!)",
"(!OPTIONAL_FIELD_3!)",
"(!OPTIONAL_FIELD_4_LABEL!)",
"(!OPTIONAL_FIELD_4!)",
"(!OPTIONAL_FIELD_5_LABEL!)",
"(!OPTIONAL_FIELD_5!)",
"(!OPTIONAL_FIELD_6_LABEL!)",
"(!OPTIONAL_FIELD_6!)",
"(!OPTIONAL_FIELD_7_LABEL!)",
"(!OPTIONAL_FIELD_7!)",
"(!OPTIONAL_FIELD_8_LABEL!)",
"(!OPTIONAL_FIELD_8!)",
"(!OPTIONAL_FIELD_9_LABEL!)",
"(!OPTIONAL_FIELD_9!)",
"(!OPTIONAL_FIELD_10_LABEL!)",
"(!OPTIONAL_FIELD_10!)",
"(!OPTIONAL_FIELD_11_LABEL!)",
"(!OPTIONAL_FIELD_11!)",
"(!OPTIONAL_FIELD_12_LABEL!)",
"(!OPTIONAL_FIELD_12!)",
"(!OPTIONAL_FIELD_13_LABEL!)",
"(!OPTIONAL_FIELD_13!)",
"(!OPTIONAL_FIELD_14_LABEL!)",
"(!OPTIONAL_FIELD_14!)",
"(!OPTIONAL_FIELD_15_LABEL!)",
"(!OPTIONAL_FIELD_15!)",
"(!OPTIONAL_FIELD_16_LABEL!)",
"(!OPTIONAL_FIELD_16!)",
"(!OPTIONAL_FIELD_17_LABEL!)",
"(!OPTIONAL_FIELD_17!)",
"(!OPTIONAL_FIELD_18_LABEL!)",
"(!OPTIONAL_FIELD_18!)",
"(!OPTIONAL_FIELD_19_LABEL!)",
"(!OPTIONAL_FIELD_19!)",
"(!OPTIONAL_FIELD_20_LABEL!)",
"(!OPTIONAL_FIELD_20!)",
"(!BID_HISTORY_LINK!)",
"(!PAYMENT_OPTIONS_LABEL!)",
"(!RESERVE!)",
"(!HIGH_BIDDER_LABEL!)",
"(!HIGH_BIDDER!)",
"(!WINNING_DUTCH_BIDDERS_LABEL!)",
"(!WINNING_DUTCH_BIDDERS!)",
"(!NUM_BIDS!)",
"(!NUM_BIDS_LABEL!)",
"(!QUANTITY_LABEL!)",
"(!QUANTITY!)",
"(!AUCTION_TYPE_LABEL!)",
"(!AUCTION_TYPE_DATA!)",
"(!AUCTION_TYPE_HELP!)",
"(!BUY_NOW_LABEL!)",
"(!BUY_NOW_DATA!)",
"(!BUY_NOW_LINK!)",
"(!DATE_ENDED_LABEL!)",
"(!DATE_ENDED!)",
"(!STATS_REMAINING_LABEL!)",
"(!STATS_REMAINING!)",
"(!MINIMUM_LABEL!)",
"(!MINIMUM_BID!)",
"(!STARTING_LABEL!)",
"(!STARTING_BID!)",
"(!SELLER_RATING_LABEL!)",
"(!SELLER_RATING!)",
"(!FEEDBACK_LINK!)",
"(!SELLER_NUMBER_RATES_LABEL!)",
"(!SELLER_NUMBER_RATES!)",
"(!SELLER_RATING_SCALE_EXPLANATION!)",
"(!BID_START_DATE_LABEL!)",
"(!BID_START_DATE!)",
"(!MAKE_BID_LINK!)",
"(!SELLER_FIRST_NAME!)",
"(!SELLER_LAST_NAME!)",
"(!SELLER_URL!)",
"(!SELLER_ADDRESS!)",
"(!SELLER_CITY!)",
"(!SELLER_STATE!)",
"(!SELLER_COUNTRY!)",
"(!SELLER_ZIP!)",
"(!SELLER_PHONE!)",
"(!SELLER_PHONE2!)",
"(!SELLER_FAX!)",
"(!SELLER_COMPANY_NAME!)",
"(!SELLER_OPTIONAL_1!)",
"(!SELLER_OPTIONAL_2!)",
"(!SELLER_OPTIONAL_3!)",
"(!SELLER_OPTIONAL_4!)",
"(!SELLER_OPTIONAL_5!)",
"(!SELLER_OPTIONAL_6!)",
"(!SELLER_OPTIONAL_7!)",
"(!SELLER_OPTIONAL_8!)",
"(!SELLER_OPTIONAL_9!)",
"(!SELLER_OPTIONAL_10!)",
"(!EXTRA_QUESTION_NAME!)",
"(!EXTRA_QUESTION_VALUE!)",
"(!EXTRA_CHECKBOX_NAME!)",
"(!SECTION_TITLE!)",
"(!PAGE_TITLE!)",
"(!DESCRIPTION!)",
"(!ACTIVE_ADS!)",
"(!EXPIRED_ADS!)",
"(!CURRENT_INFO!)",
"(!PLACE_AD!)",
"(!FAVORITES!)",
"(!COMMUNICATIONS!)",
"(!COMMUNICATIONS_CONFIG!)",
"(!SIGNS_AND_FLYERS!)",
"(!RENEW_EXTEND_SUBSCRIPTION!)",
"(!ADD_MONEY_WITH_BALANCE!)",
"(!ADD_MONEY!)",
"(!BALANCE_TRANSACTIONS!)",
"(!PAID_INVOICES!)",
"(!UNPAID_INVOICES!)",
"(!FEEDBACK!)",
"(!CURRENT_BIDS!)",
"(!BLACKLIST_BUYERS!)",
"(!INVITED_BUYERS!)",
"(!TITLE!)",
"(!IMAGE!)",
"(!ADDRESS!)",
"(!CITY!)",
"(!STATE!)",
"(!ZIP!)",
"(!PRICE!)",
"(!AUCTION_ID!)",
"(!CLASSIFIED_ID!)",
"(!DESCRIPTION!)",
"(!PHONE_1!)",
"(!PHONE_2!)",
"(!CONTACT!)",
"(!BUY_NOW_PRICE!)",
"(!STARTING_BID!)",
"(!CLASSIFIED_ID_LABEL!)",
"(!TIME_REMAINING_LABEL!)",
"(!TIME_REMAINING!)",
"(!PAYMENT_OPTIONS!)",
"(!SELLERS_OTHER_AUCTIONS_LINK!)",
"(!AD_FILTERS!)",
"(!PREVIOUS_IMAGE_LINK!)",
"(!NEXT_IMAGE_LINK!)",
"(!DISPLAY_IMAGE!)");
// $moduleHTMLCode[$pageId][0] = logged_in_html
// $moduleHTMLCode[$pageId][1] = logged_out_html

// defaults for templates
$defaultAdExtraQuestions = null;
$defaultAdCheckboxes = null;
$defaultAuctionExtraQuestions = null;
$defaultAuctionCheckboxes = null;
$detailTemplateArray = array();

//move extra question/check box templates to ad display template
if ($templateIdResult = $this->_db->Execute("SELECT user_ad_template, user_extra_template, user_checkbox_template, auctions_user_ad_template, auctions_user_extra_template, auctions_user_checkbox_template, ad_detail_print_friendly_template, auction_detail_print_friendly_template FROM `geodesic_classifieds_ad_configuration` LIMIT 1")) {
    $templateIdResult = $templateIdResult->FetchRow();
    //classifieds
    $classifiedsTemplates =  array();
    if (
        $templateCodeResults = $this->_db->Execute("SELECT template_id, template_code FROM `geodesic_templates` WHERE
	template_id = " . $templateIdResult["user_ad_template"] . " OR
	template_id = " . $templateIdResult["user_extra_template"] . " OR
	template_id = " . $templateIdResult["user_checkbox_template"] . " OR
	template_id = " . $templateIdResult["ad_detail_print_friendly_template"])
    ) {
        while ($templateCodeResult = $templateCodeResults->FetchRow()) {
            $classifiedsTemplates[$templateCodeResult["template_id"]] = $templateCodeResult["template_code"];
        }
        $defaultAdExtraQuestions = $classifiedsTemplates[$templateIdResult["user_extra_template"]];
        $defaultAdCheckboxes = str_replace('<img src="images/checkbox_arw.gif" width="10" height="11">', "", $classifiedsTemplates[$templateIdResult["user_checkbox_template"]]);
        $classifiedsTemplates[$templateIdResult["user_ad_template"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $classifiedsTemplates[$templateIdResult["user_extra_template"]], $classifiedsTemplates[$templateIdResult["user_ad_template"]]);
        $classifiedsTemplates[$templateIdResult["user_ad_template"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $classifiedsTemplates[$templateIdResult["user_checkbox_template"]], $classifiedsTemplates[$templateIdResult["user_ad_template"]]);
        $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $classifiedsTemplates[$templateIdResult["user_extra_template"]], $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]]);
        $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $classifiedsTemplates[$templateIdResult["user_checkbox_template"]], $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]]);
        $detailTemplateArray[$templateIdResult["user_ad_template"]] = $classifiedsTemplates[$templateIdResult["user_ad_template"]];
        $detailTemplateArray[$templateIdResult["ad_detail_print_friendly_template"]] = $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]];
    }

    //auctions
    $auctionsTemplates =  array();
    if (
        $templateCodeResults = $this->_db->Execute("SELECT template_id, template_code FROM `geodesic_templates` WHERE
	template_id = " . $templateIdResult["auctions_user_ad_template"] . " OR
	template_id = " . $templateIdResult["auctions_user_extra_template"] . " OR
	template_id = " . $templateIdResult["auctions_user_checkbox_template"] . " OR
	template_id = " . $templateIdResult["auction_detail_print_friendly_template"])
    ) {
        while ($templateCodeResult = $templateCodeResults->FetchRow()) {
            $auctionsTemplates[$templateCodeResult["template_id"]] = $templateCodeResult["template_code"];
        }
        $defaultAuctionExtraQuestions = $auctionsTemplates[$templateIdResult["auctions_user_extra_template"]];
        $defaultAuctionCheckboxes = str_replace('<img src="images/checkbox_arw.gif" width="10" height="11">', "", $auctionsTemplates[$templateIdResult["auctions_user_checkbox_template"]]);
        $auctionsTemplates[$templateIdResult["auctions_user_ad_template"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $auctionsTemplates[$templateIdResult["auctions_user_extra_template"]], $auctionsTemplates[$templateIdResult["auctions_user_ad_template"]]);
        $auctionsTemplates[$templateIdResult["auctions_user_ad_template"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $auctionsTemplates[$templateIdResult["auctions_user_checkbox_template"]], $auctionsTemplates[$templateIdResult["auctions_user_ad_template"]]);
        $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $auctionsTemplates[$templateIdResult["auctions_user_extra_template"]], $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]]);
        $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $auctionsTemplates[$templateIdResult["auctions_user_checkbox_template"]], $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]]);
        $detailTemplateArray[$templateIdResult["auctions_user_ad_template"]] = $auctionsTemplates[$templateIdResult["auctions_user_ad_template"]];
        $detailTemplateArray[$templateIdResult["auction_detail_print_friendly_template"]] = $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]];
    }
}

//move extra question/check box tmplates for categories
if ($templateIdResults = $this->_db->Execute("SELECT ad_detail_display_template_id, ad_detail_extra_display_template_id, ad_detail_checkbox_display_template_id, auction_detail_display_template_id, auction_detail_extra_display_template_id, auction_detail_checkbox_display_template_id, ad_detail_print_friendly_template, auction_detail_print_friendly_template FROM `geodesic_classifieds_categories_languages`")) {
    while ($templateIdResult = $templateIdResults->FetchRow()) {
        if ($templateIdResult["ad_detail_display_template_id"] != 0) {
            //classifieds
            $classifiedsTemplates =  array();
            if (
                $templateCodeResults = $this->_db->Execute("SELECT template_id, template_code FROM `geodesic_templates` WHERE
			template_id = " . $templateIdResult["ad_detail_display_template_id"] . " OR
			template_id = " . $templateIdResult["ad_detail_extra_display_template_id"] . " OR
			template_id = " . $templateIdResult["ad_detail_checkbox_display_template_id"] . " OR
			template_id = " . $templateIdResult["ad_detail_print_friendly_template"])
            ) {
                while ($templateCodeResult = $templateCodeResults->FetchRow()) {
                    $classifiedsTemplates[$templateCodeResult["template_id"]] = $templateCodeResult["template_code"];
                }
                $ad_detail_extra_display_template_id = $templateIdResult["ad_detail_extra_display_template_id"] ? $classifiedsTemplates[$templateIdResult["ad_detail_extra_display_template_id"]] : $defaultAdExtraQuestions;
                $ad_detail_checkbox_display_template_id = $templateIdResult["ad_detail_checkbox_display_template_id"] ? str_replace('<img src="images/checkbox_arw.gif" width="10" height="11">', "", $classifiedsTemplates[$templateIdResult["ad_detail_checkbox_display_template_id"]]) : $defaultAdCheckboxes;
                $classifiedsTemplates[$templateIdResult["ad_detail_display_template_id"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $ad_detail_extra_display_template_id, $classifiedsTemplates[$templateIdResult["ad_detail_display_template_id"]]);
                $classifiedsTemplates[$templateIdResult["ad_detail_display_template_id"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $ad_detail_checkbox_display_template_id, $classifiedsTemplates[$templateIdResult["ad_detail_display_template_id"]]);
                $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $ad_detail_extra_display_template_id, $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]]);
                $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $ad_detail_checkbox_display_template_id, $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]]);
                $detailTemplateArray[$templateIdResult["ad_detail_display_template_id"]] = $classifiedsTemplates[$templateIdResult["ad_detail_display_template_id"]];
                $detailTemplateArray[$templateIdResult["ad_detail_print_friendly_template"]] = $classifiedsTemplates[$templateIdResult["ad_detail_print_friendly_template"]];
            }
        }
        if ($templateIdResult["auction_detail_display_template_id"] != 0) {
            //auctions
            $auctionsTemplates =  array();
            if (
                $templateCodeResults = $this->_db->Execute("SELECT template_id, template_code FROM `geodesic_templates` WHERE
			template_id = " . $templateIdResult["auction_detail_display_template_id"] . " OR
			template_id = " . $templateIdResult["auction_detail_extra_display_template_id"] . " OR
			template_id = " . $templateIdResult["auction_detail_checkbox_display_template_id"] . " OR
			template_id = " . $templateIdResult["auction_detail_print_friendly_template"])
            ) {
                while ($templateCodeResult = $templateCodeResults->FetchRow()) {
                    $auctionsTemplates[$templateCodeResult["template_id"]] = $templateCodeResult["template_code"];
                }
                $auction_detail_extra_display_template_id = $templateIdResult["auction_detail_extra_display_template_id"] ? $auctionsTemplates[$templateIdResult["auction_detail_extra_display_template_id"]] : $defaultAuctionExtraQuestions;
                $auction_detail_checkbox_display_template_id = $templateIdResult["auction_detail_checkbox_display_template_id"] ? str_replace('<img src="images/checkbox_arw.gif" width="10" height="11">', "", $auctionsTemplates[$templateIdResult["auction_detail_checkbox_display_template_id"]]) : $defaultAuctionCheckboxes;
                $auctionsTemplates[$templateIdResult["auction_detail_display_template_id"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $auction_detail_extra_display_template_id, $auctionsTemplates[$templateIdResult["auction_detail_display_template_id"]]);
                $auctionsTemplates[$templateIdResult["auction_detail_display_template_id"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $auction_detail_checkbox_display_template_id, $auctionsTemplates[$templateIdResult["auction_detail_display_template_id"]]);
                $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]] = str_replace("<<EXTRA_QUESTION_BLOCK>>", $auction_detail_extra_display_template_id, $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]]);
                $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]] = str_replace("<<EXTRA_CHECKBOX_BLOCK>>", $auction_detail_checkbox_display_template_id, $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]]);
                $detailTemplateArray[$templateIdResult["auction_detail_display_template_id"]] = $auctionsTemplates[$templateIdResult["auction_detail_display_template_id"]];
                $detailTemplateArray[$templateIdResult["auction_detail_print_friendly_template"]] = $auctionsTemplates[$templateIdResult["auction_detail_print_friendly_template"]];
            }
        }
    }
}
$moduleHTMLCode = array();
if ($tagsResults = $this->_db->Execute("SELECT page_id, module_replace_tag, module_logged_in_html, module_logged_out_html FROM `geodesic_pages` WHERE `module` =1")) {
    while ($tagResult = $tagsResults->FetchRow()) {
        $tagsOld[] = $tagResult["module_replace_tag"];
        $tagText = str_replace(array("<",">",'(!','!)'), "", $tagResult["module_replace_tag"]);
        $newTag = "(!" . $tagText . "!)";
        $tagsNew[] = $newTag;
        $sql_strict [] = 'UPDATE `geodesic_pages` SET `module_replace_tag` = \'' . $newTag . '\' WHERE `page_id` = ' . $tagResult["page_id"];
        $moduleHTMLCode[$tagResult["page_id"]] = array($tagResult["module_logged_in_html"],$tagResult["module_logged_out_html"]);
    }
}
if (count($moduleHTMLCode) > 0) {
    foreach ($moduleHTMLCode as $pageId => $moduleCode) {
        $newLoggedInCode = str_replace($tagsOld, $tagsNew, $moduleCode[0]);
        $newLoggedOutCode = str_replace($tagsOld, $tagsNew, $moduleCode[1]);
        $sql_strict [] = 'UPDATE `geodesic_pages` set `module_logged_in_html` = ' . $this->_db->qstr($newLoggedInCode) . ', `module_logged_out_html` = ' . $this->_db->qstr($newLoggedOutCode) . ' WHERE `page_id` = ' . $pageId;
    }
}
if ((count($tagsOld) > 0) && (count($tagsOld) == count($tagsNew))) {
    if ($templateResults = $this->_db->Execute("SELECT template_id, template_code FROM `geodesic_templates` ORDER BY template_id DESC")) {
        while ($templateResult = $templateResults->FetchRow()) {
            if (isset($detailTemplateArray[$templateResult["template_id"]])) {
                $newTemplate = str_replace($tagsOld, $tagsNew, $detailTemplateArray[$templateResult["template_id"]]);
            } else {
                $newTemplate = str_replace($tagsOld, $tagsNew, $templateResult["template_code"]);
            }
            $sql_strict [] = 'UPDATE `geodesic_templates` set `template_code` = ' . $this->_db->qstr($newTemplate) . ' WHERE `template_id` = ' . $templateResult["template_id"];
        }
    }
}
unset($tagsOld);
unset($tagsNew);
unset($moduleHTMLCode);

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_price_plans` CHANGE `charge_per_ad` `charge_per_ad` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price` `featured_ad_price` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_2` `featured_ad_price_2` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_3` `featured_ad_price_3` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_4` `featured_ad_price_4` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_5` `featured_ad_price_5` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `bolding_price` `bolding_price` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `attention_getter_price` `attention_getter_price` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `charge_per_picture` `charge_per_picture` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `better_placement_charge` `better_placement_charge` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `ad_renewal_cost` `ad_renewal_cost` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `subscription_billing_charge_per_period` `subscription_billing_charge_per_period` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `invoice_max` `invoice_max` FLOAT(11,2) NOT NULL DEFAULT '0.00'";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_price_plans_categories` CHANGE `charge_per_ad` `charge_per_ad` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price` `featured_ad_price` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_2` `featured_ad_price_2` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_3` `featured_ad_price_3` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_4` `featured_ad_price_4` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `featured_ad_price_5` `featured_ad_price_5` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `bolding_price` `bolding_price` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `attention_getter_price` `attention_getter_price` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `charge_per_picture` `charge_per_picture` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `better_placement_charge` `better_placement_charge` DOUBLE(11,2) NOT NULL DEFAULT '0.00', CHANGE `ad_renewal_cost` `ad_renewal_cost` DOUBLE(11,2) NOT NULL DEFAULT '0.00'";

//use this file for things like, checking if some file exists to determine what the query will be, ect.
