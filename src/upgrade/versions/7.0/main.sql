#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


CREATE TABLE IF NOT EXISTS `geodesic_region_level` (
  `level` int(11) NOT NULL,
  `region_type` enum('country','state/province','city','other') NOT NULL DEFAULT 'other',
  `use_label` enum('yes','no') NOT NULL DEFAULT 'no',
  `always_show` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`level`)
);


CREATE TABLE IF NOT EXISTS `geodesic_region_level_labels` (
  `level` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`level`,`language_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_user_regions` (
  `user` int(11) NOT NULL,
  `region` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `default_name` varchar(255) NOT NULL,
  PRIMARY KEY (`user`,`region`),
  KEY `level` (`level`)
);

--assign some default price plans so things don't completely break when switching between master classifieds and auctions modes 
ALTER TABLE `geodesic_groups` CHANGE `auction_price_plan_id` `auction_price_plan_id` INT( 11 ) NOT NULL DEFAULT '5';
ALTER TABLE `geodesic_user_groups_price_plans` CHANGE `price_plan_id` `price_plan_id` INT( 11 ) NOT NULL DEFAULT '1';
ALTER TABLE `geodesic_user_groups_price_plans` CHANGE `auction_price_plan_id` `auction_price_plan_id` INT( 11 ) NOT NULL DEFAULT '5';
