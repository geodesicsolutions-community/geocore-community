<?php
//conditional_sql.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
## 
##################################

//This is where conditional queries go.
//For cases where an sql query might not be run, in the
//case that it is not run, add an empty string
//for the query.

//There needs to be the same number of sql queries generated, no
//matter what, otherwise the sql index will be off from the database.
//That is the reason to use an empty string in cases where an "optional" query
//is not run.

//conditional sql queries.
$sql_strict = array (
//array of sql queries, if one of these fail, it
//does not continue!

);

$sql_not_strict = array (
//array of sql queries, if one of these fail, it
//just ignores it and keeps chugin along.

);

//Add column to classifieds table for number of additional regions purchased
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD `additional_regions_purchased` INT NOT NULL DEFAULT '0' AFTER `offsite_videos_purchased`";

//column to keep track of regions
$sql_not_strict[] = "ALTER TABLE `geodesic_listing_regions` ADD `region_order` INT( 3 ) NOT NULL DEFAULT '0' AFTER `level`";
$sql_not_strict[] = "ALTER TABLE `geodesic_listing_regions` ADD INDEX `region_order` ( `region_order` )";
//now get rid of the no longer needed primary_region, as 0 designates a primary region
$sql_not_strict[] = "ALTER TABLE `geodesic_listing_regions` DROP `primary_region`";

//add dependencies to browsing filters
$sql_not_strict[] = "ALTER TABLE `geodesic_browsing_filters_settings` ADD COLUMN `dependency` varchar(255) NOT NULL DEFAULT ''";
$sql_not_strict[] = "ALTER TABLE `geodesic_browsing_filters` ADD COLUMN `category` int(4) NOT NULL DEFAULT '0'";

//For new hash stuff
$sql_not_strict[] = "ALTER TABLE `geodesic_logins` ADD `hash_type` VARCHAR( 128 ) NOT NULL DEFAULT '' AFTER `password` ,
ADD `salt` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `hash_type` ";
//change the hash type
$sql_not_strict[] = "UPDATE `geodesic_site_settings` SET `value`='core:sha1' WHERE `value`='0'
		AND (`setting`='admin_pass_hash' OR `setting`='client_pass_hash')";
$sql_not_strict[] = "UPDATE `geodesic_site_settings` SET `value`='core:plain' WHERE `value`='1'
		AND (`setting`='admin_pass_hash' OR `setting`='client_pass_hash')";

