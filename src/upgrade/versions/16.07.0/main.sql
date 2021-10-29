#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

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