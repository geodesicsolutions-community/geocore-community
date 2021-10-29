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

$sql_not_strict[] = "ALTER TABLE `geodesic_user_communications`
ADD COLUMN `public_question` tinyint(1) NOT NULL default '0',
ADD COLUMN `body_text` text NOT NULL default '',
ADD COLUMN `public_answer` tinyint(1) NOT NULL default '0'";

$sql_not_strict[] = "INSERT INTO `geodesic_site_settings` (`setting`, `value`) VALUES ('public_questions_to_show', '5')";

//For new admin_session
$sql_not_strict[] = "ALTER TABLE `geodesic_sessions` ADD `admin_session` ENUM( 'Yes', 'No' ) NOT NULL DEFAULT 'No' AFTER `level` ";
$sql_not_strict[] = "ALTER TABLE `geodesic_sessions` ADD INDEX `admin_session` ( `admin_session` ) ";

# optimize geodesic_states table
$sql_not_strict[] = "ALTER TABLE `geodesic_states` ADD INDEX `parent_id` ( `parent_id` ) ";

# Add admin_id to cart table
$sql_not_strict[] = "ALTER TABLE `geodesic_cart` ADD `admin_id` INT NOT NULL DEFAULT '0' AFTER `user_id`";
$sql_not_strict[] = "ALTER TABLE `geodesic_cart` ADD INDEX `admin_id` ( `admin_id` )";

# add admin to order table
$sql_not_strict[] = "ALTER TABLE `geodesic_order` ADD `admin` INT NOT NULL DEFAULT '0' AFTER `seller`";
$sql_not_strict[] = "ALTER TABLE `geodesic_order` ADD INDEX `admin` ( `admin` )";

#add primary key to voting table
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_votes` ADD `vote_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";

//Add column to classifieds table for number of offsite videos purchased
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD `offsite_videos_purchased` INT NOT NULL DEFAULT '0' AFTER `image`";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD INDEX `offsite_videos_purchased` ( `offsite_videos_purchased` )";
