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

//new columns in categories table
$sql_not_strict[] = "ALTER TABLE  `geodesic_categories` ADD  `level` INT NOT NULL DEFAULT  '1' AFTER  `parent_id` ,
	ADD  `enabled` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'yes' AFTER  `level`";
$sql_not_strict[] = "ALTER TABLE  `geodesic_categories` ADD INDEX `level` (  `level` )";
$sql_not_strict[] = "ALTER TABLE  `geodesic_categories` ADD INDEX `enabled` (  `enabled` )";
//even though default is 1, set all existing to level of 0 so that they can be set by update script
$sql_not_strict[] = "UPDATE `geodesic_categories` SET `level`=0 WHERE `level`=1 AND `parent_id` != 0";


//rename geodesic_categories_languages to geodesic_categories_languages
$sql_not_strict[] = "RENAME TABLE `geodesic_classifieds_categories_languages` TO `geodesic_categories_languages`";


//add new classified fields
$sql_not_strict[] = "ALTER TABLE  `geodesic_classifieds` ADD  `show_contact_seller` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'yes',
ADD  `show_other_ads` ENUM(  'yes',  'no' ) NOT NULL DEFAULT  'yes'";

//Fix structure for addon text
$sql_not_strict[] = "ALTER TABLE  `geodesic_addon_text` CHANGE  `language_id`  `language_id` INT( 11 ) NOT NULL";

//Add new charge per word
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD COLUMN `charge_per_word_count` INT(11) DEFAULT 0";


//Add new column for cost options
$sql_not_strict[] = "ALTER TABLE `geodesic_auctions_bids` ADD `cost_options` VARCHAR( 255 ) NOT NULL";
$sql_not_strict[] = "ALTER TABLE `geodesic_auctions_autobids` ADD `cost_options` VARCHAR( 255 ) NOT NULL";

//fix values of browsing filters to allow floats
$sql_not_strict[] = "ALTER TABLE `geodesic_browsing_filters` CHANGE `value_range_low` `value_range_low` DOUBLE( 10, 2 ) NULL DEFAULT NULL,
	CHANGE `value_range_high` `value_range_high` DOUBLE( 10, 2 ) NULL DEFAULT NULL";

//allow longer ad filters (particularly for multibyte languages)
$sql_not_strict[] = "ALTER TABLE `geodesic_ad_filter` CHANGE `search_terms` `search_terms` varchar(255) NOT NULL DEFAULT ''";

//add column to control display of categories in default {Body_html} category navigation
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD `front_page_display` ENUM('yes', 'no') NOT NULL DEFAULT 'yes'";
