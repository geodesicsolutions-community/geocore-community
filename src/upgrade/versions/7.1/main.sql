#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'default_browse_view', `value` = 'grid';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'display_browse_view_link_grid', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'display_browse_view_link_list', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'display_browse_view_link_gallery', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'browse_sort_dropdown_display', `value` = 'always';

# turn on pre_populate_listing_tags for backwards compatibility
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'pre_populate_listing_tags', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'codemirrorAutotab', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'codemirrorSearch', `value` = '1';

CREATE TABLE IF NOT EXISTS `geodesic_browsing_filters` (
  `session_id` varchar(32) NOT NULL,
  `target` varchar(255) NOT NULL,
  `value_scalar` varchar(255) DEFAULT NULL,
  `value_range_low` int(11) DEFAULT NULL,
  `value_range_high` int(11) DEFAULT NULL,
  PRIMARY KEY (`session_id`,`target`)
);
CREATE TABLE IF NOT EXISTS `geodesic_browsing_filters_settings` (
	`category` int(14) NOT NULL DEFAULT '0',
	`field` varchar(255) NOT NULL,
	`enabled` tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`category`,`field`)
);
CREATE TABLE IF NOT EXISTS `geodesic_browsing_filters_settings_languages` (
	`category` int(14) NOT NULL DEFAULT '0',
	`field` varchar(255) NOT NULL,
	`language` int(14) NOT NULL DEFAULT '1',
	`name` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY  (`category`,`field`,`language`)
);




# Leveled Fields
CREATE TABLE IF NOT EXISTS `geodesic_leveled_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `leveled_field` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `display_order` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `leveled_field` (`leveled_field`),
  KEY `parent` (`parent`),
  KEY `level` (`level`),
  KEY `enabled` (`enabled`),
  KEY `display_order` (`display_order`)
) AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_value_languages` (
  `id` int(11) NOT NULL COMMENT 'corresponds to id in geodesic_leveled_field_value',
  `language_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`language_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_level` (
  `level` int(11) NOT NULL,
  `leveled_field` int(11) NOT NULL,
  `always_show` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`level`,`leveled_field`)
);

CREATE TABLE IF NOT EXISTS `geodesic_leveled_field_level_labels` (
  `level` int(11) NOT NULL,
  `leveled_field` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`level`,`leveled_field`,`language_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_listing_leveled_fields` (
  `listing` int(11) NOT NULL,
  `leveled_field` int(11) NOT NULL,
  `field_value` int(11) NOT NULL COMMENT 'ID for geodesic_leveled_field_values',
  `level` int(11) NOT NULL,
  `default_name` varchar(255) NOT NULL,
  PRIMARY KEY (`listing`,`leveled_field`,`field_value`),
  KEY `level` (`level`)
);

INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'leveled_max_vals_per_page', `value` = '100';
