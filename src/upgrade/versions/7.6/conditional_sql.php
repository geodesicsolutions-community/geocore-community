<?php
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

//display order for browsing filters
$sql_not_strict[] = "ALTER TABLE `geodesic_browsing_filters_settings` ADD COLUMN `display_order` int(1) NOT NULL DEFAULT '0'";

//add new category specific settings for SEO features
$sql_not_strict[] ="ALTER TABLE `geodesic_categories_languages` ADD `title_module` TEXT NOT NULL AFTER `head_html`, ADD `seo_url_contents` TEXT NOT NULL AFTER `title_module`, ADD `category_image_alt` TEXT NOT NULL AFTER `seo_url_contents`";

//add ability to display language at end of title in title module
$sql_not_strict[] ="ALTER TABLE  `geodesic_classifieds_ad_configuration` ADD  `title_module_language_display` TINYINT NOT NULL DEFAULT  '0' AFTER  `title_module_text`";

//****core db adjustments to support "share_fees" addon****
//add column to registration process to save fee sharing user attachment choice
$sql_not_strict[] = "ALTER TABLE `geodesic_registration_session` ADD `feeshareattachment` INT NOT NULL DEFAULT '0'" ;
$sql_not_strict[] = "ALTER TABLE `geodesic_confirm` ADD `feeshareattachment` INT NOT NULL DEFAULT '0'";
//add field in user data to hold message for shared fee addon
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` ADD `attached_user_message` TEXT NOT NULL";
//add field to order item db table to note whether an item has been paid out within the shared fee addon
$sql_not_strict[] = "ALTER TABLE  `geodesic_order_item` ADD  `paid_out` INT NULL DEFAULT NULL";
$sql_not_strict[] = "ALTER TABLE  `geodesic_order_item` ADD  `paid_out_to` INT NULL DEFAULT NULL";
$sql_not_strict[] = "ALTER TABLE  `geodesic_order_item` ADD  `paid_out_date` INT NULL DEFAULT NULL";