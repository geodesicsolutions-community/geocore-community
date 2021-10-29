#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

CREATE TABLE IF NOT EXISTS `geodesic_addons` (
`name` VARCHAR( 128 ) NOT NULL ,
`version` VARCHAR( 64 ) NOT NULL ,
`enabled` BOOL NOT NULL ,
PRIMARY KEY ( `name` ) ,
INDEX ( `enabled` )
) ;
CREATE TABLE IF NOT EXISTS `geodesic_addon_text` (
  `auth_tag` varchar(128) NOT NULL,
  `addon` varchar(128) NOT NULL,
  `text_id` varchar(128) NOT NULL,
  `language_id` tinyint(4) NOT NULL,
  `text` mediumtext NOT NULL,
  KEY `auth_tag` (`auth_tag`),
  KEY `addon` (`addon`),
  KEY `text_id` (`text_id`),
  KEY `language_id` (`language_id`)
);

CREATE TABLE IF NOT EXISTS `geodesic_storefront_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `storeId` int(11) NOT NULL default '0',
  `subject` text NOT NULL,
  `content` text NOT NULL,
  `time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

# Make all html modules use the new file
UPDATE `geodesic_pages` SET `module_file_name` = 'module_display_login_logout_html.php' 
 WHERE `module_file_name` LIKE 'module\_display\_login\_logout\_html\_%';

UPDATE `geodesic_pages` SET `module_file_name` = 'module_display_php.php' 
 WHERE `module_file_name` LIKE 'module\_display\_php\_%';

# Set default settings
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'min_user_length', `value` = '6';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'max_user_length', `value` = '12';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'min_pass_length', `value` = '6';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'max_pass_length', `value` = '12';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'admin_pass_hash', `value` = '0';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'client_pass_hash', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'forgot_password', `value` = '1';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'uploading_image', `value` = 'images/loading.gif';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'uploading_image_placeholder', `value` = 'images/loading_placeholder.gif';
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'use_wysiwyg_compression', `value` = '1';

# Log for license
CREATE TABLE IF NOT EXISTS `geodesic_license_log` (
`log_id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
`time` INT( 14 ) NOT NULL DEFAULT '0',
`log_type` ENUM( 'error_local', 'error_remote', 'notice_local', 'notice_remote' ) NOT NULL ,
`message` TEXT NOT NULL ,
`need_attention` TINYINT( 3 ) NOT NULL DEFAULT '1',
INDEX ( `time` , `log_type` )
) ;
# Add addon for license log
INSERT IGNORE INTO `geodesic_addons` SET `name` = 'log_license_db', `version`='1.0.0', `enabled`=1;

#Install & Enable email addon
INSERT IGNORE INTO `geodesic_addons` SET `name` = 'email_sendDirect', `version`='1.0.0', `enabled`=1;

# Fix for broken "Report Abuse" button on the bottom of ads. 
# Replaces the "Site Abuse Report - Item:" with "Site Abuse Report :: Item:" 
# because the '-' breaks the email link
UPDATE `geodesic_pages_messages_languages` SET `text` = 'Site Abuse Report :: Item:' WHERE `page_id` = 1 AND `text_id` = 500060 AND `language_id` = 1 AND `text` = 'Site Abuse Report - Item:' LIMIT 1;


#Un-comment the following line to test the error handling...
#This should thow an error!;