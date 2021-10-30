<?php
//conditional_sql.php


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


//add verified
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` ADD `verified` ENUM( 'no', 'yes' ) NOT NULL DEFAULT 'no' AFTER `last_login_ip`";

//header_html in categories
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD `which_header_html` ENUM( 'parent', 'cat', 'default', 'cat+default' ) NOT NULL DEFAULT 'parent'";
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD INDEX `which_header_html` ( `which_header_html` )";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_categories_languages` ADD `header_html` TEXT NOT NULL AFTER `description` ";

//little bit of optimization
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD INDEX `viewed` (`viewed`)";
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD INDEX `date` (`date`)";

//Convert field locations to be saved in new spot in the DB
$rows = $this->_db->Execute("SELECT * FROM `geodesic_fields` WHERE `display_locations`!=''");
if ($rows) {
	foreach ($rows as $row) {
		$locations = unserialize($row['display_locations']);
		if (!is_array($locations)) {
			continue;
		}
		$group_id = (int)$row['group_id'];
		$category_id = (int)$row['category_id'];
		$field_name = $this->_db->qstr(''.$row['field_name']);
		
		if ($field_name!='email') {
			//don't copy over e-mail locations!
			foreach ($locations as $location) {
				$display_location = $this->_db->qstr(''.$location);
				$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('$group_id', '$category_id', $field_name, $display_location)";
			}
		}
		$sql_not_strict[] = "UPDATE `geodesic_fields` SET `display_locations`='' WHERE `group_id`='$group_id' AND `category_id`='$category_id' AND `field_name`=$field_name";
	}
	//also get rid of display_locations column
	$sql_not_strict[] = "ALTER TABLE `geodesic_fields` DROP `display_locations`";
}

//get rid of unused pricing columns for tokens
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_price_plans` DROP `credits_upon_registration` ,
DROP `credits_expire_type` ,
DROP `credits_expire_date` ,
DROP `credits_expire_period`";

//add more room for mime type string
$sql_not_strict[] = "ALTER TABLE  `geodesic_file_types` CHANGE  `mime_type`  `mime_type` VARCHAR( 128 ) NOT NULL";
$sql_not_strict[] = "ALTER TABLE  `geodesic_classifieds_images_urls` CHANGE  `mime_type`  `mime_type` VARCHAR( 128 ) NOT NULL";

//convert old reverse auctions to new
$sql_not_strict[] = "UPDATE `geodesic_classifieds` SET `item_type`=2, `auction_type`=3 WHERE `item_type`=4";

//Proxy bidding setting
if ($this->_db->GetOne("SELECT `value` FROM `geodesic_site_settings` WHERE `setting`='less_than_reserve_straight_bid'")) {
	//must have reserve met
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'allow_proxy_bids', `value` = 'reserve_met'";
} else {
	//fully enable proxy bids
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'allow_proxy_bids', `value` = 'all'";
}


//New date type
$sql_not_strict[] = "ALTER TABLE `geodesic_fields` CHANGE `field_type` `field_type` ENUM( 'text', 'textarea', 'url', 'email', 'number', 'cost', 'date', 'dropdown', 'other' ) NOT NULL";

//Browsing type modules now use fields to use settings
$mods = $this->_db->Execute("SELECT * FROM `geodesic_pages` WHERE `page_id` IN (125,126,127,128,129,130,131,132,46,47,48,49,50,60,61,172,155,156)");
if ($mods) {
	foreach ($mods as $row) {
		$display_location = $this->_db->qstr('module-'.$row['module_replace_tag']);
		if (!isset($row['module_display_title']) || $row['module_display_title']=='2') {
			//this location already processed...
			continue;
		}
		$colsToClear = array();
		if ($row['module_display_photo_icon']) {
			$field_name = $this->_db->qstr('photo');
			$colsToClear[] = "`module_display_photo_icon`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if (!$row['module_display_title']) {
			//this one is backwards, if module_display_title is 1, it hides title, 0 shows title...
			$field_name = $this->_db->qstr('title');
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_ad_description']) {
			$field_name = $this->_db->qstr('description');
			$colsToClear[] = "`module_display_ad_description`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_address']) {
			$field_name = $this->_db->qstr('address');
			$colsToClear[] = "`module_display_address`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_city']) {
			$field_name = $this->_db->qstr('city');
			$colsToClear[] = "`module_display_city`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_state']) {
			$field_name = $this->_db->qstr('state');
			$colsToClear[] = "`module_display_state`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_country']) {
			$field_name = $this->_db->qstr('country');
			$colsToClear[] = "`module_display_country`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_zip']) {
			$field_name = $this->_db->qstr('zip');
			$colsToClear[] = "`module_display_zip`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_number_bids']) {
			$field_name = $this->_db->qstr('num_bids');
			$colsToClear[] = "`module_display_number_bids`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_price']) {
			$field_name = $this->_db->qstr('price');
			$colsToClear[] = "`module_display_price`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_entry_date']) {
			$field_name = $this->_db->qstr('classified_start');
			$colsToClear[] = "`module_display_entry_date`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		if ($row['module_display_time_left']) {
			//in this instance, module_display_time_left only was option for auctions
			$field_name = $this->_db->qstr('auction_time_left');
			$colsToClear[] = "`module_display_time_left`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		for ($i=1;$i<21;$i++) {
			if ($row['module_display_optional_field_'.$i]) {
				$field_name = $this->_db->qstr('optional_field_'.$i);
				$colsToClear[] = "`module_display_optional_field_$i`='0'";
				$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
			}
		}
		$colsToClear[] = "`module_display_title`='2'";
		//this is how we keep track of what settings were already proccessed, set module_display_title = 2
		$sql_not_strict[] = "UPDATE `geodesic_pages` SET ".implode(', ', $colsToClear)." WHERE `page_id`='{$row['page_id']}'";
		unset($colsToClear);
	}
}

//For pic modules
$mods = $this->_db->Execute("SELECT * FROM `geodesic_pages` WHERE `page_id` IN (89,90,102,117,118,119,120,121,122,123,124)");
if ($mods) {
	foreach ($mods as $row) {
		$display_location = $this->_db->qstr('module-'.$row['module_replace_tag']);
		if (!isset($row['module_display_title']) || $row['module_display_title']=='2') {
			//this location already processed...
			continue;
		}
		$colsToClear = array();
		if ($row['module_display_price']) {
			$field_name = $this->_db->qstr('price');
			$colsToClear[] = "`module_display_time_left`='0'";
			$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
		}
		
		if (!$row['module_display_title']) {
			//this one is backwards, if module_display_title is 1, it hides title, 0 shows title...
			
			//figure out what field to turn "on"
			$module_text_type = str_replace('location_','',$row['module_text_type']);
			if ($module_text_type) {
				$field_name = $this->_db->qstr($module_text_type);
				$sql_not_strict[] = "INSERT INTO `geodesic_field_locations` (`group_id`, `category_id`, `field_name`, `display_location`) VALUES ('0', '0', $field_name, $display_location)";
			}
		}
		$colsToClear[] = "`module_display_title`='2'";
		//this is how we keep track of what settings were already proccessed, set module_display_title = 2
		$sql_not_strict[] = "UPDATE `geodesic_pages` SET ".implode(', ', $colsToClear)." WHERE `page_id`='{$row['page_id']}'";
		unset($colsToClear);
	}
}

//Add language ID for listing
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD `language_id` INT NOT NULL DEFAULT '0' AFTER `description`";

$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds` ADD INDEX `language_id` ( `language_id` ) ";

//Fix netcash page for text
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `page_id`=183 WHERE `message_id`=500782 AND `page_id`=10203";
$sql_not_strict[] = "UPDATE `geodesic_pages_messages_languages` SET `page_id`=183 WHERE `text_id`=500782 AND `page_id`=10203";

