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
## ##    6.0.7-2-gc953682
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


//insert strong and em into default allowed HTML
//add it to this array to have it auto-added and auto-allowed
$toAdd = array ('strong','em');
foreach ($toAdd as $tag) {
	$row = $this->_db->GetRow("SELECT `tag_id` FROM `geodesic_html_allowed` WHERE `tag_name`='$tag' LIMIT 1");
	if ($row) {
		$sql_not_strict[] = "UPDATE `geodesic_html_allowed` SET `strongly_recommended` = '0' WHERE `tag_id`='{$row['tag_id']}'";
	} else {
		$sql_not_strict[] = "INSERT INTO `geodesic_html_allowed` (`tag_name` ,`tag_status` ,`display` ,
				`replace_with`, `use_search_string`, `strongly_recommended`)
			VALUES ('{$tag}', '0', '1', '', '1', '0')";
	}
}

//Remove the "auction_price_plan_id" column from the "geodesic_group_attached_price_plans"
//since it is not used
$sql_not_strict[] = "ALTER TABLE `geodesic_group_attached_price_plans` DROP `auction_price_plan_id`";

//adds ability to charge by starting price for reverse auctions, re-running in this
//update since SQL snapshot pre 4.0.4 did not include changes
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_price_increments` ADD `item_type` INT( 11 ) NOT NULL DEFAULT '1'";

//Change title/description for certain text fields
$txtMod = array (
	167 => array ('title' => 'Legacy image upload form instructions', 'desc' => 'instructions at the top of the legacy image collection form explaining the ways to display images on their listing'),
	500374 => array ('title' => 'Legacy Edit Listing Page Description', 'desc' => ''),
	500381 => array ('title' => 'Legacy Auction Page Description', 'desc' => ''),
	
);
foreach ($txtMod as $txtId => $data) {
	$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name` = '".urlencode($data['title'])."', `description`='".urlencode($data['desc'])."' WHERE `message_id`=$txtId";
}

$truecolor = $this->_db->GetOne("SELECT `imagecreatetruecolor_switch` FROM `geodesic_classifieds_ad_configuration`");
if ($truecolor) {
	$sql_not_strict [] = "REPLACE INTO `geodesic_site_settings` SET `setting` = 'imagecreatetruecolor_switch', `value` = 1";
} else {
	$sql_not_strict [] = '';
}
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_ad_configuration` DROP `imagecreatetruecolor_switch`";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_ad_configuration` DROP `maximum_photos`";

//Fix transactions that were set without gateway when admin adjusted account balance
$sql_not_strict [] = "UPDATE `geodesic_transaction` SET `gateway`='account_balance' WHERE `description`='Add+to+balance' AND `gateway`=''";

//Fix orders that are set to incomplete but should really be pending
$fixRows = $this->_db->GetAll("SELECT `geodesic_order`.`id`, `geodesic_invoice`.`id` as invoice FROM `geodesic_order`, `geodesic_invoice` WHERE `geodesic_invoice`.`order`=`geodesic_order`.`id` AND `geodesic_order`.`status`='incomplete'");
if ($fixRows && count($fixRows)) {
	foreach ($fixRows as $row) {
		//see if there is a matching session
		$sessionMatch = $this->_db->GetRow("SELECT count(`id`) as count FROM `geodesic_cart` WHERE `order`=?", array ((int)$row['id']));
		if ($sessionMatch && $sessionMatch['count'] == 0) {
			//fix it!  First have to find what payment gateway it is using...
			$gatewayName = $this->_db->GetOne("SELECT `gateway` FROM `geodesic_transaction` WHERE `invoice`=".(int)$row['invoice']." AND `gateway`!='site_fee'");
			if ($gatewayName) {
				//insert order registry item
				$sql_not_strict[] = "INSERT INTO `geodesic_order_registry` (`index_key`, `order`, `val_string`)
					VALUES ('payment_type', ".(int)$row['id'].", '{$gatewayName}')";
				$sql_not_strict[] = "UPDATE `geodesic_order` SET `status`='pending_admin' WHERE `id`=".(int)$row['id'];
			}
		}
	}
}

//fix subscriptions not bound to a price plan
$subscriptions = $this->_db->Execute('SELECT * FROM geodesic_classifieds_user_subscriptions WHERE price_plan_id = 0');
while($subscription = $subscriptions->FetchRow()) {
	$price_plans = $this->_db->GetRow('SELECT price_plan_id, auction_price_plan_id FROM geodesic_user_groups_price_plans where id = ?', array($subscription['user_id']));
	$price_plan_id = ($price_plans['price_plan_id']) ? $price_plans['price_plan_id'] : $price_plans['auction_price_plan_id'];
	//update from here so we can use ?'s
	$update_sql = "UPDATE geodesic_classifieds_user_subscriptions SET price_plan_id = ? where user_id = ?"; 
	$this->_db->Execute($update_sql, array($price_plan_id, $subscription['user_id']));
}

# Add recurring column to transaction
$sql_not_strict [] = "ALTER TABLE `geodesic_transaction` ADD `recurring_billing` INT(14) NOT NULL AFTER `invoice`";
$sql_not_strict [] = "ALTER TABLE `geodesic_transaction` ADD INDEX `recurring_billing` ( `recurring_billing` )";

# Make the price in the expired table match same size as that in classifieds table
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_expired` CHANGE `final_price` `final_price` DOUBLE( 10, 2 ) NOT NULL DEFAULT '0.00'";

//Add recurring billing column to subscriptions table
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_user_subscriptions` ADD `recurring_billing` INT( 11 ) NOT NULL default '0'";
$sql_not_strict [] = " ALTER TABLE `geodesic_classifieds_user_subscriptions` ADD INDEX `recurring_billing` ( `recurring_billing` )";
//New settings for my account
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_table_rows', `value` = '5'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_show_new_messages', `value` = '1'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_show_account_balance', `value` = '1'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_show_auctions', `value` = '1'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_show_classifieds', `value` = '1'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_show_recently_sold', `value` = '1'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'my_account_recently_sold_time', `value` = '30'";


//Remove un-used fields from sessions table
$removeFields = array ('affiliate_id','affiliate_group_id','bulk_upload_text','bulk_upload_listing_id_list');
foreach ($removeFields as $field) {
	//drop each one in it's own query so that if one is already removed, it doesn't stop the others
	$sql_not_strict[] = "ALTER TABLE `geodesic_sessions` DROP `$field`";
}
unset ($removeFields);

//this field was added in the 3.x->4.0 upgrade, but got left out of fresh installs -- adding it again to make sure it's here
$sql_not_strict[] = "ALTER TABLE `geodesic_user_communications` ADD `read` TINYINT(4) NOT NULL DEFAULT '0'";

//correct a minor typo in the name of a text field
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name` = 'Normal Results Header Auctions' WHERE `message_id` = '200110'";

//Improve db indexes
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_images_urls` ADD INDEX `classified_id` ( `classified_id` )";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_images_urls` ADD INDEX `display_order` ( `display_order` )";

//fix a bug in the fresh install sql that prevents this text field from being edited
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `text`='', `page_id`='39' WHERE `message_id`='3266'";

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_sell_questions` CHANGE `display_order` `display_order` INT( 3 ) NOT NULL DEFAULT '0'";
