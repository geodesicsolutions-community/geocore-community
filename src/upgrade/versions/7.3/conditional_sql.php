<?php
//conditional_sql.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.5.3-36-gea36ae7
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

//Using head_html instead of header_html now days
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` CHANGE `which_header_html` `which_head_html` ENUM( 'parent', 'cat', 'default', 'cat+default' ) NOT NULL DEFAULT 'parent'";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_categories_languages` CHANGE `header_html` `head_html` TEXT NOT NULL";
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name`='Default+head_html+text', `description`='Text+automatically+added+to+end+of+%7Bhead_html%7D' WHERE `message_id`=500961";

//add cron job - send final fees due reminder every 30 days
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_cron` (`task`, `type`, `last_run`, `running`, `interval`) VALUES ('send_final_fees_emails', 'main', '0', '0', '2592000')";

//add new page for final fees
$sql_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES
(10213, 2, 'Final Fees Due E-Mail Notice', 'This is the e-mail sent to the user every 30 days to notify them that they have outstanding final fees due.')";
