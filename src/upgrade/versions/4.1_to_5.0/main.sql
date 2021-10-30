#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


CREATE TABLE IF NOT EXISTS `geodesic_fields` (
  `group_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `field_name` varchar(128) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `can_edit` tinyint(1) NOT NULL,
  `field_type` enum('text','textarea','url','email','number','cost','dropdown','other') NOT NULL,
  `type_data` varchar(255) NOT NULL default '0',
  `text_length` int(11) NOT NULL,
  `display_locations` text NOT NULL,
  PRIMARY KEY  (`group_id`,`category_id`,`field_name`),
  KEY `is_enabled` (`is_enabled`),
  KEY `is_required` (`is_required`),
  KEY `can_edit` (`can_edit`)
);

# Allow longer feedbacks (upgrading from TINYTEXT)
ALTER TABLE `geodesic_auctions_feedbacks` CHANGE `feedback` `feedback` TEXT;

# Set defaults for how large text box is for description if it is not set already
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'desc_wysiwyg_width', `value` = '700';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'desc_wysiwyg_height', `value` = '280';