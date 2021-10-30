#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

CREATE TABLE IF NOT EXISTS `geodesic_category_exclude_list_types` (
  `category_id` int(11) NOT NULL,
  `listing_type` varchar(128) NOT NULL,
  PRIMARY KEY (`category_id`,`listing_type`)
);

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

INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'add_nofollow_user_links', `value` = '1';


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

CREATE TABLE IF NOT EXISTS `geodesic_listing_cost_options_quantity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing` int(11) NOT NULL DEFAULT '0',
  `quantity_remaining` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `listing` (`listing`)
) AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `geodesic_listing_cost_options_q_option` (
  `combo_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`combo_id`,`option_id`),
  KEY `combo_id` (`combo_id`)
) ;

CREATE TABLE IF NOT EXISTS `geodesic_jit_confirmations` (
	`email` VARCHAR(255) NOT NULL,
	`code` VARCHAR(10) NOT NULL,
	PRIMARY KEY (`email`)
);

CREATE TABLE IF NOT EXISTS `geodesic_user_ratings` (
	`about` int(1) NOT NULL,
	`from` int(1) NOT NULL,
	`rating` int(1) NOT NULL,
	PRIMARY KEY (`about`, `from`)
);

CREATE TABLE IF NOT EXISTS `geodesic_user_ratings_averages` (
	`about` int(1) NOT NULL,
	`average` double(3,2) NOT NULL,
	`notified` int(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`about`)
);