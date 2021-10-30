#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


CREATE TABLE IF NOT EXISTS `geodesic_print_publication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`),
  KEY `status` (`status`)
) AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `geodesic_print_publication_languages` (
  `id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(128) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`,`language_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_print_publish_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publication_id` int(11) NOT NULL,
  `label` varchar(128) NOT NULL,
  `day_of_week` enum('Sun','Mon','Tue','Wed','Thur','Fri','Sat') DEFAULT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `publication_id` (`publication_id`),
  KEY `status` (`status`),
  KEY `sort_order` (`sort_order`)
) AUTO_INCREMENT=1 ;

#admin dropdown for this setting goes up to 200, so make it unsigned so that the db accepts values greater than 127
ALTER TABLE `geodesic_classifieds_configuration` CHANGE `max_word_width` `max_word_width` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0';

#increase addon registry capacity (mostly to support large revolving bulk uploads)
ALTER TABLE `geodesic_addon_registry` CHANGE `val_complex` `val_complex` LONGTEXT NOT NULL;

# Add field locations table
CREATE TABLE IF NOT EXISTS `geodesic_field_locations` (
  `group_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `field_name` varchar(128) NOT NULL,
  `display_location` varchar(128) NOT NULL,
  KEY `group_id` (`group_id`,`category_id`,`field_name`)
);

# sessions registry
CREATE TABLE IF NOT EXISTS `geodesic_sessions_registry` (
  `index_key` varchar(255) NOT NULL default '',
  `sessions` varchar(32) NOT NULL default '',
  `val_string` varchar(255) NOT NULL default '',
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `sessions` (`sessions`),
  KEY `val_string` (`val_string`)
);

#unify pre/postcurrency field lengths across tables
ALTER TABLE `geodesic_classifieds` CHANGE `precurrency` `precurrency` VARCHAR(252) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds` CHANGE `postcurrency` `postcurrency` VARCHAR(252) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds_configuration` CHANGE `precurrency` `precurrency` VARCHAR(252) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds_configuration` CHANGE `postcurrency` `postcurrency` VARCHAR(252) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds_expired` CHANGE `precurrency` `precurrency` VARCHAR(252) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_classifieds_expired` CHANGE `postcurrency` `postcurrency` VARCHAR(252) NOT NULL DEFAULT '';
ALTER TABLE `geodesic_currency_types` CHANGE `precurrency` `precurrency` VARCHAR(252) NOT NULL;
ALTER TABLE `geodesic_currency_types` CHANGE `postcurrency` `postcurrency` VARCHAR(252) NOT NULL;

INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'date_field_format', `value` = 'l, F j, Y';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'date_field_format_short', `value` = 'M j, Y';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'display_all_tab_browsing', `value` = '1';
