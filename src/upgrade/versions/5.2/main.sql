#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.

# Remove "special case" tags from allowed HTML

DELETE FROM `geodesic_html_allowed` WHERE `use_search_string`=0;


#upgrade voting table from tinytext to text
ALTER TABLE `geodesic_classifieds_votes` CHANGE `vote_title` `vote_title` TEXT NOT NULL;
ALTER TABLE `geodesic_classifieds_votes` CHANGE `vote_comments` `vote_comments` TEXT NOT NULL;

#offsite video URL table
CREATE TABLE IF NOT EXISTS `geodesic_listing_offsite_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) NOT NULL,
  `slot` int(11) NOT NULL,
  `video_type` varchar (32) NOT NULL,
  `video_id` varchar(32) NOT NULL,
  `media_content_url` varchar(128) NOT NULL,
  `media_content_type` varchar (32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `slot` (`slot`)
) AUTO_INCREMENT=1 ;

# changes since image upload page changing name to media upload page

UPDATE `geodesic_pages` SET `name` = 'Media Collection' WHERE `page_id`=10;
UPDATE `geodesic_pages_messages` SET `name` = 'STEP+LABEL%3A+media+collection' WHERE `message_id`=500501;

UPDATE `geodesic_pages_messages` SET `name` = 'Edit+Listing+Legacy+Image+Upload+Description' WHERE `message_id`=500374;
UPDATE `geodesic_pages_messages` SET `name` = 'New+Auction+Legacy+Image+Upload+Description' WHERE `message_id`=500381;
UPDATE `geodesic_pages_messages` SET `name` = 'New+Classified+Legacy+Image+Upload+Description' WHERE `message_id`=167;
UPDATE `geodesic_pages_messages` SET `name` = 'session+error%3A+no+longer+on+media+collection+step' WHERE `message_id`=500694;

