#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


CREATE TABLE IF NOT EXISTS `geodesic_combined_css_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(15) NOT NULL,
  `file_list` text NOT NULL,
  `resource_hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `version` (`version`),
  KEY `resource_hash` (`resource_hash`)
) AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `geodesic_combined_js_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(15) NOT NULL,
  `file_list` text NOT NULL,
  `resource_hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `version` (`version`),
  KEY `resource_hash` (`resource_hash`)
) AUTO_INCREMENT=1 ;

--change optional fields in geodesic_confirm from tinytext to text to match other uses of those fields
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_1` `optional_field_1` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_2` `optional_field_2` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_3` `optional_field_3` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_4` `optional_field_4` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_5` `optional_field_5` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_6` `optional_field_6` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_7` `optional_field_7` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_8` `optional_field_8` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_9` `optional_field_9` text NOT NULL;
ALTER TABLE `geodesic_confirm` CHANGE `optional_field_10` `optional_field_10` text NOT NULL;

-- new setting
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'starting_image_title', `value` = 'filename';

--change text size of mail queue to match larger size of stored admin messages
ALTER TABLE `geodesic_email_queue` CHANGE `content` `content` MEDIUMTEXT;
