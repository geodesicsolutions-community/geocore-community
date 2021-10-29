#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

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

CREATE TABLE IF NOT EXISTS `geodesic_listingextra_duration_languages` (
	`id` int(1) NOT NULL COMMENT 'corresponds to geodesic_listingextra_duration_prices id',
	`language_id` int(1) NOT NULL,
	`label` tinytext NOT NULL DEFAULT '',
	PRIMARY KEY(`id`, `language_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_listingextra_expirations` (
	`listing_id` int(1) NOT NULL,
	`extra_type` varchar(255) NOT NULL DEFAULT '',
	`expires` int(1) NOT NULL DEFAULT 0 COMMENT 'unix timestamp',
	PRIMARY KEY (`listing_id`, `extra_type`)
);

#increase storage capacity of `title` from tinytext
ALTER TABLE `geodesic_classifieds` CHANGE `title` `title` text;
ALTER TABLE `geodesic_classifieds_expired` CHANGE `title` `title` text;