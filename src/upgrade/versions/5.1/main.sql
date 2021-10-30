#All queries in this file will be run.  Comments should be
# on lines by themselves, and start with # or --
# Queries should end with ;
# All queries in this file are considered required, if it
# fails, the upgrade will stop.  If you dont want this,
# see the conditional_sql.php file.

# NOTE: These queries should be able to be imported to PHPMyAdmin
# if a manual upgrade is needed.


# Add tags table

CREATE TABLE IF NOT EXISTS `geodesic_listing_tags` (
  `listing_id` int(11) NOT NULL,
  `tag` varchar(128) NOT NULL,
  PRIMARY KEY  (`listing_id`,`tag`)
);
