#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

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

CREATE TABLE IF NOT EXISTS `geodesic_listing_subscription` (
  `recurring_id` int(1) NOT NULL,
  `listing_id` int(1) NOT NULL,
  PRIMARY KEY (`recurring_id`),
  KEY `listing_id` (`listing_id`)
);