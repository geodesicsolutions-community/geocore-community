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

//New tables
$sql_strict[] = "CREATE TABLE IF NOT EXISTS `geodesic_master` (
  `setting` varchar(128) NOT NULL,
  `switch` enum('on','off') NOT NULL DEFAULT 'off',
  PRIMARY KEY (`setting`)
)";

//Add keys
$sql_not_strict[] = "ALTER TABLE `geodesic_countries` ADD INDEX `display_order` ( `display_order` )";
$sql_not_strict[] = "ALTER TABLE `geodesic_countries` ADD INDEX `name` ( `name` )";
$sql_not_strict[] = "ALTER TABLE `geodesic_countries` ADD INDEX `abbreviation` ( `abbreviation` )";
//for states
$sql_not_strict[] = "ALTER TABLE `geodesic_states` ADD INDEX `name` ( `name` )";


//Add language names for each language and country/state
//New tables are added in main.sql which are run before this so we're good

$languages = $this->_db->GetAll("SELECT `language_id` FROM `geodesic_pages_languages`");

//add some default levels
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_region_level` (`level`,`region_type`,`use_label`,`always_show`) VALUES (1, 'country','yes','no'), (2, 'state/province','yes','no')";
//get text for each level's label per-language
//cart checkout billing info label seems like a safe place to pull it from...
foreach ($languages as $lang) {
    $country = $this->_db->GetOne("SELECT `text` FROM `geodesic_pages_messages_languages` WHERE `text_id` = 500273 AND `language_id` = " . $lang['language_id']);
    $state = $this->_db->GetOne("SELECT `text` FROM `geodesic_pages_messages_languages` WHERE `text_id` = 500274 AND `language_id` = " . $lang['language_id']);
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_region_level_labels` (`level`,`language_id`,`label`) VALUES ('1','{$lang['language_id']}','{$country}'), ('2','{$lang['language_id']}','{$state}')";
}
unset($languages, $countries, $states);

//move old state/country Fields settings to the new way
$sql_not_strict[] = "UPDATE `geodesic_fields` SET `field_name` = 'region_level_1' WHERE `field_name` = 'country'";
$sql_not_strict[] = "UPDATE `geodesic_fields` SET `field_name` = 'region_level_2' WHERE `field_name` = 'state'";
//and also registration settings!
$regi = $this->_db->GetRow("SELECT `use_registration_country_field` AS uc, `require_registration_country_field` AS rc, `use_registration_state_field` AS us, `require_registration_state_field` AS rs FROM `geodesic_registration_configuration`");
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` (`setting`, `value`) VALUES ('registration_use_region_level_1', '" . ($regi['uc'] ? '1' : '0') . "'),
 ('registration_require_region_level_1', '" . ($regi['rc'] ? '1' : '0') . "'),
 ('registration_use_region_level_2', '" . ($regi['us'] ? '1' : '0') . "'),
 ('registration_require_region_level_2', '" . ($regi['rs'] ? '1' : '0') . "')";
$sql_not_strict[] = "UPDATE `geodesic_registration_configuration` SET `use_registration_country_field` = '0', `require_registration_country_field` = '0', `use_registration_state_field` = '0', `require_registration_state_field` = '0'";

//move listing type settings to the new G1 way of doing things
//get the old setting (note: old setting will be 0 for non-classauctions products)
$oldListingTypes = $this->_db->GetOne("SELECT `listing_type_allowed` FROM `geodesic_classifieds_configuration`");
//don't need that old one anymore -- set it to 0 to make sure it doesn't screw with stuff later
$sql_not_strict[] = "UPDATE `geodesic_classifieds_configuration` SET `listing_type_allowed` = 0";
$class = $auction = $site_fees = 'off';
if ($oldListingTypes == 1 || $oldListingTypes == 0) {
    //enable classifieds
    $class = 'on';
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` (`setting`, `value`) VALUES ('allow_new_classifieds', 1)";
}
if ($oldListingTypes == 2 || $oldListingTypes == 0) {
    //enable auctions
    $auction = 'on';
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` (`setting`, `value`) VALUES ('allow_new_auctions', 1)";
}

$allAdsFree = $this->_db->GetOne("SELECT `value` FROM `geodesic_site_settings` WHERE `setting` = 'all_ads_are_free'");
$site_fees = ($allAdsFree) ? 'off' : 'on';

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_master` (`setting`, `switch`) VALUES
('auctions', '{$auction}'),
('classifieds', '{$class}'),
('site_fees', '$site_fees');";

//remove old cron jobs that are changing to addons
$sql_not_strict[] = "DELETE FROM `geodesic_cron` WHERE `task` IN ('expire_groups_and_plans', 'expire_subscriptions', 'send_negative_account_balance_emails') AND `type` = 'main'";

//now auto-install any of the new addons that are found
if (is_file(ADDON_DIR . "account_balance/info.php")) {
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_addons` (`name`, `version`, `enabled`) VALUES ('account_balance','1.0.0','1')";
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `interval`) VALUES ('account_balance:send_negative_account_balance_emails', 'addon', '2592000')";
}
if (is_file(ADDON_DIR . "enterprise_pricing/info.php")) {
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_addons` (`name`, `version`, `enabled`) VALUES ('enterprise_pricing','1.0.0','1')";
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `interval`) VALUES ('enterprise_pricing:expire_groups_and_plans', 'addon', '3600')";
}
if (is_file(ADDON_DIR . "subscription_pricing/info.php")) {
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_addons` (`name`, `version`, `enabled`) VALUES ('subscription_pricing','1.0.0','1')";
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `interval`) VALUES ('subscription_pricing:expire_subscriptions', 'addon', '3600')";
}
if (is_file(ADDON_DIR . "featured_levels/info.php")) {
    $sql_not_strict[] = "INSERT IGNORE INTO `geodesic_addons` (`name`, `version`, `enabled`) VALUES ('featured_levels','1.0.0','1')";
}

//activate new mapping location field according to old mapping address field
$sql_not_strict[] = "UPDATE `geodesic_fields` SET `field_name` = 'mapping_location' WHERE `field_name` = 'mapping_address'";
$sql_not_strict[] = "DELETE FROM `geodesic_fields` WHERE `field_name` IN ('mapping_address','mapping_city','mapping_state','mapping_country','mapping_zip')";

$sql_not_strict[] = "ALTER TABLE `geodesic_confirm` ADD COLUMN `terminal_region_id` int(11) NOT NULL default '0'";
