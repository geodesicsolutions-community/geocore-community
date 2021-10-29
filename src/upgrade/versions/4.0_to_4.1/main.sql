#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


# Remove old data not used anymore
DELETE FROM `geodesic_order_registry` WHERE `index_key` = 'billing_info';

CREATE TABLE IF NOT EXISTS `geodesic_recurring_billing` (
  `id` int(14) NOT NULL auto_increment,
  `secondary_id` varchar(255) NOT NULL,
  `gateway` varchar(128) NOT NULL,
  `start_date` int(14) NOT NULL,
  `paid_until` int(14) NOT NULL,
  `status` varchar(128) NOT NULL,
  `order_id` int(14) NOT NULL,
  `user_id` int(14) NOT NULL,
  `cycle_duration` int(14) NOT NULL,
  `price_per_cycle` decimal(14,4) NOT NULL,
  `item_type` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  KEY `secondary_id` (`secondary_id`),
  KEY `gateway` (`gateway`),
  KEY `paid_until` (`paid_until`),
  KEY `status` (`status`),
  KEY `user_id` (`user_id`),
  KEY `item_type` (`item_type`)
) AUTO_INCREMENT=27 ;

CREATE TABLE IF NOT EXISTS `geodesic_recurring_billing_registry` (
  `index_key` varchar(255) NOT NULL,
  `recurring_billing` int(14) NOT NULL,
  `val_string` varchar(255) NOT NULL,
  `val_text` text NOT NULL,
  `val_complex` text NOT NULL,
  KEY `index_key` (`index_key`),
  KEY `recurring_billing` (`recurring_billing`),
  KEY `val_string` (`val_string`)
);
# run cron job to remove old recurring data once a day (cron job runs once a day, that is not the duration)
INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `last_run`, `running`, `interval`) VALUES 
('remove_old_recurring_data', 'main', 0, 0, 86400),
('recurring_billing_update_status', 'main', 0, 0, 86400);

#Add default time before recurring billing expires to be 90 days (7776000 seconds)
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'recurring_billing_data_age', `value` = '7776000';

# Update cron jobs to run once a day instead of once every 30 days
UPDATE `geodesic_cron` SET `interval`=86400 
  WHERE `task` IN ('remove_old_order_data', 'remove_old_invoices', 'remove_messages', 'remove_archived_listings');
#default slideshow settings
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'useSlideshow', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'startSlideshow', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'useStandardUploader', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'useLightboxAnimations', `value` = '1';

# Change name of popup image box page to Lightbox Image Slideshow
UPDATE `geodesic_pages` SET `name` = 'Image Lightbox Slideshow',
`description` = 'This is the full-sized image slide show, displayed inside a "lightbox" without leaving the page currently being viewed. This can be turned on and off within ad configuration' WHERE `page_id` = 157 LIMIT 1;

ALTER TABLE `geodesic_price_plan_ad_lengths` CHANGE `length_charge` `length_charge` DOUBLE( 8, 2 ) NOT NULL DEFAULT '0.00',
CHANGE `renewal_charge` `renewal_charge` DOUBLE( 8, 2 ) NOT NULL DEFAULT '0.00';
