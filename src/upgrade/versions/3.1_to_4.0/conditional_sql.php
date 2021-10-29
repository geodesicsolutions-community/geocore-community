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

//add api_token column to logins table, for use by the api
$sql_not_strict [] = "ALTER TABLE `geodesic_logins` ADD `api_token` VARCHAR( 40 ) NOT NULL";
//add index
$sql_not_strict [] = "ALTER TABLE `geodesic_logins` ADD INDEX `api_token` ( `api_token` )";

// Parent -> child relationship field for geography
$sql_not_strict [] = "ALTER TABLE `geodesic_states` ADD `parent_id` INT( 11 ) NOT NULL ;";
// User ip field for new registrations
$sql_not_strict [] = "ALTER TABLE `geodesic_confirm` ADD `user_ip` varchar( 20 ) NOT NULL ;";

$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds` ADD `order_item_id` INT( 14 ) NOT NULL DEFAULT '0'";
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds` ADD INDEX `order_item_id` ( `order_item_id` )";

$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_expired` ADD `order_item_id` INT( 14 ) NOT NULL DEFAULT '0'";
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_expired` ADD INDEX `order_item_id` ( `order_item_id` )";

# Sell session no longer used
$sql_not_strict [] = "DROP TABLE `geodesic_classifieds_sell_session`";
# Sell photo session no longer used
$sql_not_strict [] = "DROP TABLE `geodesic_classifieds_sell_session_images`";
# sell questions session no longer used
$sql_not_strict [] = "DROP TABLE `geodesic_classifieds_sell_session_questions`";

$sql_not_strict [] = "ALTER TABLE `geodesic_pages` ADD `alpha_across_columns` TINYINT( 1 ) NOT NULL";

// Setting up the new (proper) style print friendly templates for "site default"
$template_sql = "SELECT `ad_detail_print_friendly_template`,`auction_detail_print_friendly_template` FROM `geodesic_classifieds_ad_configuration`";
$template_rs = $this->_db->Execute( $template_sql );
$languages_sql = "SELECT `language_id` FROM `geodesic_pages_languages`";
$languages_rs = $this->_db->Execute( $languages_sql );

if ( $languages_rs && $template_rs )
{
	$templates = $template_rs->FetchRow();
	while ( $language = $languages_rs->FetchRow() )
	{
		$check_rs = $this->_db->Execute( "SELECT * FROM `geodesic_classifieds_categories_languages` WHERE `category_id` = 0 AND `language_id` = ".$language['language_id'] );
		if ( !$check_rs || $check_rs->RecordCount() == 0 )
		{
			$sql_not_strict [] = "INSERT IGNORE INTO `geodesic_classifieds_categories_languages` (`category_id`, `template_id`, `ad_detail_print_friendly_template`, `auction_detail_print_friendly_template`, `language_id` ) 	VALUES  ('0', '0', '".$templates['ad_detail_print_friendly_template']."', '".$templates['auction_detail_print_friendly_template']."', '".$language['language_id']."')";
		}
	}
}

//User credits - changed name to account tokens..
$sql = "SHOW TABLES LIKE 'geodesic_classifieds_user_credits'";
if (count($this->_db->GetAll($sql)) > 0){
	$sql_strict[] = "RENAME TABLE `geodesic_classifieds_user_credits` TO `geodesic_user_tokens`";
	$sql_strict[] = "ALTER TABLE `geodesic_user_tokens` CHANGE `credits_id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
	$sql_strict[] = "ALTER TABLE `geodesic_user_tokens` CHANGE `credit_count` `token_count` INT( 11 ) NOT NULL DEFAULT '0'";
	$sql_strict[] = "ALTER TABLE `geodesic_user_tokens` CHANGE `credits_expire` `expire` INT( 14 ) NOT NULL DEFAULT '0'";
	$sql_strict[] = "ALTER TABLE `geodesic_user_tokens` ADD INDEX `expire` ( `expire` )";
	
} else {
	//always need same number of queries.
	$sql_strict[] = '';
	$sql_strict[] = '';
	$sql_strict[] = '';
	$sql_strict[] = '';
	$sql_strict[] = '';
}

//also update text, if text is the same as default still
$credit_replace = array(
	196 => 'The current listing is free due to a credit given by the site administrator',
	741 => 'current credit count',
	742 => 'credits expire',
	1615 => 'use a credit to renew your listing',
	1616 => 'Using a credit for renewal',
	
);

foreach ($credit_replace as $i => $txt){
	$replace_txt = str_replace('credit','token',$txt);
	$txt_enc = urlencode($txt);
	$sql_strict[] = "UPDATE `geodesic_pages_messages_languages` SET `text` = '$replace_txt' WHERE `text_id` = $i AND (`text` = '$txt' OR `text` = '$txt_enc')";
}

//Remove the un-used "auction_session" column from the geodesic_sessions table
$sql_not_strict[] = "ALTER TABLE `geodesic_sessions` DROP `auction_session`";

//update userdata table to hold stuff we need
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` CHANGE `account_balance` `account_balance` DECIMAL( 14, 4 ) NOT NULL DEFAULT '0.00'"; 
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` ADD `date_balance_negative` INT( 14 ) NOT NULL AFTER `account_balance`";
$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` ADD `balance_freeze` INT( 3 ) NOT NULL DEFAULT '0' AFTER `date_balance_negative`";


//Need to un-addslashes all templates
$sql = "SELECT `template_code`, `template_id` FROM `geodesic_templates` WHERE `template_code`!=''";
$result_template = $this->_db->Execute($sql);
if (!$result_template){
	echo 'ERROR: '.$this->_db->ErrorMsg();
	return;
}

while ($row = $result_template->FetchRow()){
	if (strpos($row['template_code'],' ') !== false){
		//pretty good bet that a plain-text template will have a space somewhere in it, so if template does not have one
		//it's probably already urlencoded...
		$txt = stripslashes($row['template_code']); //undo addslashes
		$txt = urlencode($txt); //equivelant of geoString::toDB()
		$sql_not_strict[] = "UPDATE `geodesic_templates` SET `template_code` = '$txt' WHERE `template_id`={$row['template_id']}";
	}
}

//Need to un-addslashes logged in/out
$sql = "SELECT `module_logged_in_html`, `module_logged_out_html`, `php_code`, `page_id` FROM `geodesic_pages` WHERE (`module_logged_in_html`!='' OR `module_logged_out_html`!='' OR `php_code`!='')";
$result_template = $this->_db->Execute($sql);
if (!$result_template){
	echo 'ERROR: '.$this->_db->ErrorMsg();
	return;
}

while ($row = $result_template->FetchRow()){
	$set = array();
	if (strpos($row['module_logged_in_html'],' ') !== false){
		//pretty good bet that a plain-text template will have a space somewhere in it, so if template does not have one
		//it's probably already urlencoded...
		$txt = stripslashes($row['module_logged_in_html']); //undo addslashes
		$txt = urlencode($txt); //equivelant of geoString::toDB()
		$set[] = "`module_logged_in_html` = '$txt'";
	}
	if (strpos($row['module_logged_out_html'],' ') !== false){
		//pretty good bet that a plain-text template will have a space somewhere in it, so if template does not have one
		//it's probably already urlencoded...
		$txt = stripslashes($row['module_logged_out_html']); //undo addslashes
		$txt = urlencode($txt); //equivelant of geoString::toDB()
		$set[] = "`module_logged_out_html` = '$txt'";
	}
	if (strpos($row['php_code'],' ') !== false){
		//pretty good bet that a plain-text php code will have a space somewhere in it, so if code does not have one
		//it's probably already urlencoded...
		$txt = stripslashes($row['php_code']); //undo addslashes
		$txt = urlencode($txt); //equivelant of geoString::toDB()
		$set[] = "`php_code` = '$txt'";
	}
	if (count($set) > 0){
		$sql_not_strict[] = "UPDATE `geodesic_pages` SET ".implode(', ',$set)." WHERE `page_id`={$row['page_id']}";
	}
}

// copy body code to new extra pages registry table.

$check_arr = $this->_db->MetaColumnNames( 'geodesic_pages' );
if ( in_array( 'extra_page_text', $check_arr ) )
{
	$lang_sql = "SELECT language_id FROM `geodesic_pages_languages`";
	$lang_rs = $this->_db->Execute( $lang_sql );
	if (!$lang_rs){
		echo 'ERROR: '.$this->_db->ErrorMsg();
		return;
	}
	
	$langs = Array();
	while ( $lang_row = $lang_rs->FetchRow() )
	{
		$langs[] = $lang_row['language_id'];	
	}
	
	$page_sql = "SELECT page_id, extra_page_text FROM `geodesic_pages` WHERE page_id >= 135 AND page_id <= 154";
	$page_rs = $this->_db->Execute( $page_sql );
	
	if (!$page_rs){
		echo 'ERROR: '.$this->_db->ErrorMsg();
		return;
	}
	
	while ( $page_row = $page_rs->FetchRow() )
	{
		foreach ( $langs as $lang_id )
		{
			$sql_strict[] = "INSERT INTO `geodesic_extra_pages_registry` VALUES ( 'body_code', '{$page_row['page_id']}:{$lang_id}','','{$page_row['extra_page_text']}','' )";
		}
	}
	
	$sql_strict[] = "ALTER TABLE `geodesic_pages` DROP `extra_page_text`";
}

###  OPTIMIZE Structure!

// Add index for category, be sure to name it category, that way it's denied if there is already
//and index named that.
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds` ADD INDEX `category` ( `category` )";


//copy category specific questions "display text" to category specific languages table
//making category specific questions language specific

//first check to see if there are any category specific questions
$check_question_table_sql = "SELECT * FROM `geodesic_classifieds_sell_questions`";
$check_question_table_sql_result = $this->_db->Execute( $check_question_table_sql );
//echo $check_question_table_sql."<br>\n";
if (!$check_question_table_sql_result) {
	$this->criticalError('ERROR getting existing sell questions, debug info: '.$this->_db->ErrorMsg(),__line__);
	return;
} elseif ($check_question_table_sql_result->RecordCount() > 0) {
	//category specific questions exist
	//now check to see if they have already been moved to the languages table
	$langsR = $this->_db->GetAll("SELECT `language_id` FROM `geodesic_pages_languages`");
	$langs = array();
	foreach ($langsR as $row) {
		$langs[] = $row['language_id'];
	}
	if (!$langs || !in_array(1, $langs)) {
		//ensure that at least language 1 is in there somewhere
		$langs[] = 1;
	}
	$all = $this->_db->GetAll("SELECT * FROM `geodesic_classifieds_sell_questions`");
	if ($all === false) {
		$this->criticalError('ERROR getting existing sell questions, debug info: '.$this->_db->ErrorMsg(),__line__);
		return;
	}
	foreach ($all as $row) {
		//make sure it's not there already
		foreach ($langs as $lang_id) {
			$check = $this->_db->GetRow("SELECT `question_id` FROM `geodesic_classifieds_sell_questions_languages` WHERE `question_id`={$row['question_id']} AND `language_id`=$lang_id");
			if (!$check) {
				//insert into languages table.
				$sql_strict[] = "INSERT INTO `geodesic_classifieds_sell_questions_languages` (`question_id`, `language_id`, `name`, `explanation`, `choices`) 
				VALUES ({$row['question_id']}, $lang_id, ".$this->_db->qstr($row['name'].'').", 
				".$this->_db->qstr($row['explanation'].'').", ".$this->_db->qstr($row['choices'].'').")";
			} else {
				//to keep sql count the same, so resume upgrade works properly
				$sql_strict[] = "";
			}
		}
	}
}

//Add ability to change order by
$sql_not_strict [] = "ALTER TABLE `geodesic_pages` ADD `alt_order_by` INT( 2 ) NOT NULL DEFAULT '0'";

#add delayed start auction functionality
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_price_plans` ADD `delayed_start_auction` INT NOT NULL DEFAULT '0'";
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds` ADD `delayed_start` INT NOT NULL DEFAULT '0'";

#----------------------------------------------------------------------------------------------------------------------------
#Storefront upgrade
#
# Note: After this version, Storefront 1.0.0, ALL storefront db updates
# will be done in storefront addon upgrade script, now
# that the Storefront is a true addon
#----------------------

# First, see if storefront is even used.
# Best way to find that out, look in the templates for a storefront template.
$tpls = $this->_db->GetAll("SELECT `template_id`, `template_code` FROM `geodesic_templates` WHERE `storefront_template` = 1 OR `storefront_template_default` = 1 OR `applies_to`=3");
if (count($tpls) > 0) {
	
	//Re-name all tables to be pre-pended with geodesic_addon_ so that they are consistent with other addons
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_categories` TO `geodesic_addon_storefront_categories`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_display` TO `geodesic_addon_storefront_display`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_group_subscriptions_choices`  TO `geodesic_addon_storefront_group_subscriptions_choices`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_newsletter` TO `geodesic_addon_storefront_newsletter`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_pages` TO `geodesic_addon_storefront_pages`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_subscriptions` TO `geodesic_addon_storefront_subscriptions`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_subscriptions_choices`  TO `geodesic_addon_storefront_subscriptions_choices`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_template_modules`  TO `geodesic_addon_storefront_template_modules`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_traffic_dailyReport`  TO `geodesic_addon_storefront_traffic`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_traffic_cache`  TO `geodesic_addon_storefront_traffic_cache`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_users` TO `geodesic_addon_storefront_users`";
	$sql_not_strict [] = "RENAME TABLE `geodesic_storefront_traffic_dailyreport` TO `geodesic_addon_storefront_traffic`";
	
	//rename field name on storefront to avoid possible problems
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_subscriptions` CHANGE `onholdStartTime` `onhold_start_time` INT( 11 ) NOT NULL";
	
	//rename field name on storefront to avoid possible problems
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_traffic` CHANGE `storeId` `store_id` INT( 11 ) NOT NULL";
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_traffic_cache` CHANGE `storeId` `store_id` INT( 11 ) NOT NULL";
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_traffic` CHANGE `logId` `log_id` INT( 11 ) NOT NULL AUTO_INCREMENT";
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_traffic_cache` CHANGE `logId` `log_id` INT( 11 ) NOT NULL AUTO_INCREMENT";
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_traffic` CHANGE `uVisits` `uvisits` INT( 11 ) NOT NULL";
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_traffic` CHANGE `tVisits` `tvisits` INT( 11 ) NOT NULL";
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_newsletter` CHANGE `storeid` `store_id` INT( 11 ) NOT NULL";//TODO: Remove this before release
	$sql_not_strict [] = "ALTER TABLE `geodesic_addon_storefront_newsletter` CHANGE `storeId` `store_id` INT( 11 ) NOT NULL DEFAULT '0'";
	
	//add trial periods to storefront
	$sql_not_strict[] = "ALTER TABLE `geodesic_addon_storefront_subscriptions_choices` ADD COLUMN `trial` int(1) NOT NULL default '0'";
	$sql_not_strict[] = "ALTER TABLE `geodesic_userdata` ADD COLUMN `storefront_trials_used` text NOT NULL DEFAULT ''";
	//change storefront amount so it can be larger for pricing
	$sql_not_strict [] = " ALTER TABLE `geodesic_addon_storefront_subscriptions_choices` CHANGE `amount` `amount` DECIMAL( 14, 4 ) NOT NULL DEFAULT '0.00'";
	
	//convert templates
	foreach ($tpls as $tpl) {
		$tpl_code = urldecode($tpl['template_code']);
		//convert head thingy to use standard one
		$tpl_code = str_replace ('(!STOREFRONT_HEAD!)', '(!CSSSTYLESHEET!)', $tpl_code);
		//convert body to use standard as well
		$tpl_code = str_replace ('(!STOREFRONT_LISTINGS!)', '(!MAINBODY!)', $tpl_code);
		$tpl_code = urlencode($tpl_code);
		$sql_not_strict[] = "UPDATE `geodesic_templates` SET `template_code` = '$tpl_code', `storefront_template`=1 WHERE `template_id`={$tpl['template_id']}";
	}
	$row = $this->_db->GetRow("SELECT * FROM `geodesic_addons` WHERE `name`='storefront'");
	if (!count($row)) {
		//Any updates done after 1.0.0 are NOT done in overall upgrade!
		$sql_not_strict [] = "INSERT INTO `geodesic_addons` (`name`, `version`, `enabled`) VALUES ('storefront','1.0.0',0)";
	}
	
} else {
	# Storefront must not be in use, or there would be template errors if it was...
	# So kill any storefront tables
	$storefront_tables = array (
		'geodesic_storefront_categories','geodesic_addon_storefront_categories',
		'geodesic_storefront_display','geodesic_addon_storefront_display',
		'geodesic_storefront_group_subscriptions_choices','geodesic_addon_storefront_group_subscriptions_choices',
		'geodesic_storefront_newsletter','geodesic_addon_storefront_newsletter',
		'geodesic_storefront_pages','geodesic_addon_storefront_pages',
		'geodesic_storefront_subscriptions','geodesic_addon_storefront_subscriptions',
		'geodesic_storefront_subscriptions_choices','geodesic_addon_storefront_subscriptions_choices',
		'geodesic_storefront_template_modules','geodesic_addon_storefront_template_modules',
		'geodesic_storefront_traffic_dailyReport','geodesic_addon_storefront_traffic',
		'geodesic_storefront_traffic_cache','geodesic_addon_storefront_traffic_cache',
		'geodesic_storefront_users','geodesic_addon_storefront_users',
		'geodesic_storefront_traffic_dailyreport','geodesic_addon_storefront_traffic',
	);
	foreach ($storefront_tables as $storefront_table) {
		$sql_not_strict[] = "DROP TABLE IF EXISTS `$storefront_table`";
	}
	//make sure it is not in the database.
	$sql_not_strict[] = "DELETE FROM `geodesic_addons` WHERE `name`='storefront' LIMIT 1";
}

#  End Storefront upgrade
#----------------------------------------------------------------------------------------------------------------------------

//populate new payment gateway tables

//*****geodesic_payment_gateway table

	//get enabled status out of old table where we can
	$sql = "SELECT `name`, `accepted` from `geodesic_payment_choices`";
	$result = $this->_db->Execute($sql);
	
	while($line = $result->FetchRow()){
		$cleanedName = str_replace(' ', '_', strtolower($line['name']));
		$gatewayEnabled[] = ($line['accepted'] == 1) ? 1 : 0;
	}
	
		
	//CC gateways first
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('authorizenet', 'cc', '1', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('linkpoint', 'cc', '2', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('moneris', 'cc', '3', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('payflow_pro', 'cc', '4', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('paypal_pro', 'cc', '5', '0', '0', '0')";
	//Remote-site gateways next
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('internetsecure', 'internetsecure', '6', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('nochex', 'nochex', '7', '".$gatewayEnabled['nochex']."', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('paymentexpress', 'paymentexpress', '8', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('paypal', 'paypal', '9', '".$gatewayEnabled['paypal']."', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('twocheckout', 'twocheckout', '10', '0', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('worldpay', 'worldpay', '11', '".$gatewayEnabled['worldpay']."', '0', '0')";
	//now the manual payments
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('account_balance', 'account_balance', '12', '".$gatewayEnabled['site_balance_system']."', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('cash', 'cash', '13', '".$gatewayEnabled['cash']."', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('check', 'check', '14', '".$gatewayEnabled['check']."', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('money_order', 'money_order', '15', '".$gatewayEnabled['money_order']."', '0', '0')";
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('manual_payment', 'manual_payment', '16', '0', '0', '0')";
	//site fee -- internal use
	$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_payment_gateway` (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) VALUES ('site_fee', 'site_fee', '17', '1', '0', '0')";

//*****geodesic_payment_gateway_registry table
	
	//if table is un-empty, we've already done this import, or there are custom settings
	//don't import again, so we don't overwrite stuff
	$sql = "SELECT `payment_gateway` from `geodesic_payment_gateway_registry` LIMIT 1";
	$result = $this->_db->Execute($sql);
	if(!$result || $result->RecordCount() == 0) {
	
	//***linkpoint
	
		$sql = "SELECT * FROM `geodesic_cc_linkpoint`";
		$result = $this->_db->GetRow($sql);
		
		if ($result['store_number'] || $result['ssl_path'] || $result['demo_mode']) {
			$linkpoint_data['store_number'] = $this->_db->qstr(''.$result['store_number']);
			$linkpoint_data['ssl_path'] = $this->_db->qstr(''.$result['ssl_path']);
			$linkpoint_data['demo_mode'] = $this->_db->qstr(''.$result['demo_mode']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('linkpoint:0', 'store_number', {$linkpoint_data['store_number']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('linkpoint:0', 'cert_path', {$linkpoint_data['ssl_path']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('linkpoint:0', 'testing_mode', {$linkpoint_data['demo_mode']})";
		}
		

	//***moneris

		$sql = "SELECT  `setting`, `value` FROM `geodesic_site_settings` WHERE `setting` = 'moneris_store_id' OR `setting` = 'moneris_api_token' OR `setting` = 'moneris_crypttype'";
		$result = $this->_db->GetAll($sql);
		if ($result) {
			foreach ($result as $line) {
				$temp[$line['setting']] = $line['value'];
			}
			$moneris_data['store_id'] = $this->_db->qstr(''. $temp['moneris_store_id']);
			$moneris_data['api_token'] = $this->_db->qstr(''.$temp['moneris_api_token']);
			$moneris_data['crypttype'] = $this->_db->qstr(''.$temp['moneris_crypttype']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('moneris:0', 'store_id', {$moneris_data['store_id']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('moneris:0', 'api_token', {$moneris_data['api_token']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('moneris:0', 'crypttype', {$moneris_data['crypttype']})";
		}		
		
	//***payflowpro

		$sql = "SELECT * FROM `geodesic_cc_payflow_pro`";
		$result = $this->_db->GetRow($sql);
		
		if ($result) {
			$payflowpro_data['partner'] = $this->_db->qstr(''.$result['partner']);
			$payflowpro_data['vendor'] = $this->_db->qstr(''.$result['vendor']);
			$payflowpro_data['user'] = $this->_db->qstr(''.$result['user']);
			$payflowpro_data['password'] = $this->_db->qstr(''.$result['password']);
			$payflowpro_data['demo_mode'] = $this->_db->qstr(''.$result['demo_mode']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('payflowpro:0', 'partner', {$payflowpro_data['partner']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('payflowpro:0', 'vendor', {$payflowpro_data['vendor']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('payflowpro:0', 'user', {$payflowpro_data['user']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('payflowpro:0', 'password', {$payflowpro_data['password']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('payflowpro:0', 'testing_mode', {$payflowpro_data['demo_mode']})";		
		}
		
	//***authorizenet

		$sql = "select * from `geodesic_cc_authorizenet`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$authorizenet_data['merchant_login'] = $this->_db->qstr(''.$result['merchant_login']);
			$authorizenet_data['transaction_key'] = $this->_db->qstr(''.$result['transaction_key']);
			$authorizenet_data['currency_code'] = $this->_db->qstr(''.$result['currency_code']);
			$authorizenet_data['merchant_password'] = $this->_db->qstr(''.$result['merchant_password']);
			$authorizenet_data['merchant_type'] = $this->_db->qstr(''.$result['merchant_type']);
			$authorizenet_data['connection_type'] = $this->_db->qstr(''.$result['connection_type']);
			$authorizenet_data['send_email_customer'] = $this->_db->qstr(''.$result['send_email_customer']);
			$authorizenet_data['send_email_merchant'] = $this->_db->qstr(''.$result['send_email_merchant']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'merchant_type', {$authorizenet_data['merchant_type']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'connection_type', {$authorizenet_data['connection_type']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'merchant_login', {$authorizenet_data['merchant_login']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'merchant_password', {$authorizenet_data['merchant_password']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'currency_code', {$authorizenet_data['currency_code']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'email_customer', {$authorizenet_data['send_email_customer']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('authorizenet:0', 'email_merchant', {$authorizenet_data['send_email_merchant']})";
		}
		
			
	//***internetsecure
	
		$sql = "select * from `geodesic_cc_internetsecure`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$internetsecure_data['merchantnumber'] = $this->_db->qstr(''.$result['merchantnumber']);
			$internetsecure_data['language'] = $this->_db->qstr(''.$result['language']);
			$internetsecure_data['demo_mode'] = $this->_db->qstr(''.$result['demo_mode']);
			$internetsecure_data['canadian_tax_method'] = $this->_db->qstr(''.$result['canadian_tax_method']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('internetsecure:0', 'account_num', {$internetsecure_data['merchantnumber']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('internetsecure:0', 'tax_method', {$internetsecure_data['canadian_tax_method']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('internetsecure:0', 'language', {$internetsecure_data['language']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('internetsecure:0', 'testing_mode', {$internetsecure_data['demo_mode']})";
		}
		
	//***paypal_pro
	
		$sql = "select * from `geodesic_cc_paypal`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$paypal_pro_data['api_username'] = $this->_db->qstr(''.$result['api_username']);
			$paypal_pro_data['api_password'] = $this->_db->qstr(''.$result['api_password']);
			$paypal_pro_data['certfile'] = $this->_db->qstr(''.$result['certfile']);
			$paypal_pro_data['currency_id'] = $this->_db->qstr(''.$result['currency_id']);
			$paypal_pro_data['charset'] = $this->_db->qstr(''.$result['charset']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal_pro:0', 'api_username', {$paypal_pro_data['api_username']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal_pro:0', 'api_password', {$paypal_pro_data['api_password']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal_pro:0', 'certfile', {$paypal_pro_data['certfile']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal_pro:0', 'currency_id', {$paypal_pro_data['currency_id']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal_pro:0', 'charset', {$paypal_pro_data['charset']})";
		}
				
	//***nochex
	
		$sql = "select * from `geodesic_nochex`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$nochex_data['demo_mode'] = $this->_db->qstr(''.$result['demo_mode']);
			$nochex_data['logo_path'] = $this->_db->qstr(''.$result['logo_path']);
			$nochex_data['email'] = $this->_db->qstr(''.$result['email']);
	
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('nochex:0', 'logo_path', {$nochex_data['logo_path']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('nochex:0', 'email', {$nochex_data['email']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('nochex:0', 'testing_mode', {$nochex_data['demo_mode']})";
		}
				
	//***paymentexpress
	
		$sql = "select * from `geodesic_cc_paymentexpress`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$paymentexpress_data['userid'] = $this->_db->qstr(''.$result['userid']);
			$paymentexpress_data['access_key'] = $this->_db->qstr(''.$result['access_key']);
			$paymentexpress_data['mac_key'] = $this->_db->qstr(''.$result['mac_key']);
			$paymentexpress_data['currency_type'] = $this->_db->qstr(''.$result['currency_type']);
			$paymentexpress_data['email_address'] = $this->_db->qstr(''.$result['email_address']);
	
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paymentexpress:0', 'user_id', {$paymentexpress_data['userid']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paymentexpress:0', 'access_key', {$paymentexpress_data['access_key']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paymentexpress:0', 'mac_key', {$paymentexpress_data['mac_key']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paymentexpress:0', 'receipt_email', {$paymentexpress_data['email_address']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paymentexpress:0', 'currency_codes', {$paymentexpress_data['currency_type']})";
		}
		
	
	//***paypal
		
		$sql = "select `paypal_id`, `paypal_currency_rate`, `paypal_currency`, `paypal_image_url`, `paypal_item_label` from `geodesic_classifieds_configuration`";
		$result = $this->_db->GetRow($sql);
		if ($result && strlen($result['paypal_id']) > 0 && $result['paypal_id'] != 'paypal@geodesicsolutions.com') {
			$paypal_data['paypal_id'] = $this->_db->qstr(''.$result['paypal_id']);
			$paypal_data['paypal_currency_rate'] = $this->_db->qstr(''.$result['paypal_currency_rate']);
			$paypal_data['paypal_currency_type'] = $this->_db->qstr(''.$result['paypal_currency']);
			$paypal_data['paypal_image_url'] = $this->_db->qstr(''.$result['paypal_image_url']);
			$paypal_data['paypal_item_label'] = $this->_db->qstr(''.$result['paypal_item_label']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal:0', 'paypal_id', {$paypal_data['paypal_id']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal:0', 'paypal_currency_rate', {$paypal_data['paypal_currency_rate']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal:0', 'paypal_currency_type', {$paypal_data['paypal_currency_type']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal:0', 'paypal_image_url', {$paypal_data['paypal_image_url']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('paypal:0', 'paypal_item_label', {$paypal_data['paypal_item_label']})";
		}
		
	//***twocheckout

		$sql = "select * from `geodesic_cc_twocheckout`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$twocheckout_data['sid'] = $this->_db->qstr(''.$result['sid']);
			$twocheckout_data['demo_mode'] = $this->_db->qstr(''.$result['demo_mode']);
				
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('twocheckout:0', 'sid', {$twocheckout_data['sid']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('twocheckout:0', 'testing_mode', {$twocheckout_data['demo_mode']})";
		}
		
	//***worldpay

		$sql = "select * from `geodesic_worldpay_settings`";
		$result = $this->_db->GetRow($sql);
		if ($result) {
			$worldpay_data['worldpay_installation_id'] = $this->_db->qstr(''.$result['worldpay_installation_id']);
			$worldpay_data['currency_type'] = $this->_db->qstr(''.$result['currency_type']);
			$worldpay_data['test_mode'] = $this->_db->qstr(''.$result['test_mode']);
			$worldpay_data['callback_password'] = $this->_db->qstr(''.$result['callback_password']);
			
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('worldpay:0', 'account_num', {$worldpay_data['merchantnumber']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('worldpay:0', 'tax_method', {$worldpay_data['canadian_tax_method']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('worldpay:0', 'language', {$worldpay_data['language']})";
			$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('worldpay:0', 'testing_mode', {$worldpay_data['demo_mode']})";
		}
	}
	
//end of geodesic_payment_gateway_registry table*****


//change text for editing user info, from Password: to New Password:
$sql_not_strict[] = "UPDATE `geodesic_pages_messages_languages` SET `text`='New+Password:' WHERE `text_id`=517 AND `text` like 'Password_'";
//change text for editing user info, from Verify Password: to Verify New Password:
$sql_not_strict[] = "UPDATE `geodesic_pages_messages_languages` SET `text`='Verify+New+Password:' WHERE `text_id`=518 AND `text` like 'Verify_Password_'";

# Fix it so that order shows up correctly for buy now auctions, when
# sorting by price.
$sql_not_strict[] = "UPDATE `geodesic_classifieds` SET `minimum_bid` = `buy_now` WHERE `buy_now_only` = 1 AND `item_type` = 2";

# Copy max photo setting over to plan item settings
$setting = $this->_db->GetRow("SELECT `maximum_photos` FROM `geodesic_classifieds_ad_configuration`");

if (isset($setting['maximum_photos'])) {
	//get all the price plans
	$price_plans = $this->_db->GetAll("SELECT `price_plan_id` FROM `geodesic_classifieds_price_plans`");
	foreach ($price_plans as $plan) {
		//insert max photos setting into each one
		$sql_strict[] = "INSERT IGNORE INTO `geodesic_plan_item` (`order_item`,`price_plan`,`category`,`process_order`,`need_admin_approval`) VALUES ('images','{$plan['price_plan_id']}','0','30','0')";
		$row = $this->_db->GetRow("SELECT count(index_key) as count FROM geodesic_plan_item_registry WHERE `index_key`= 'max_uploads' AND plan_item='images:{$plan['price_plan_id']}:0'");
		if (!$row || $row['count'] == 0) {
			$sql_strict[] = "INSERT INTO `geodesic_plan_item_registry` (`index_key`,`plan_item`,`val_string`,`val_text`,`val_complex`) VALUES ('max_uploads','images:{$plan['price_plan_id']}:0','{$setting['maximum_photos']}','','')";
		} else {
			$sql_strict[] = '';
		}
	}
}

# Fix the tinyMCE setting, no longer need ../ in front of CSS files as it breaks on
# the latest version of tinyMCE
$row = $this->_db->GetRow("SELECT `value` FROM `geodesic_site_settings` WHERE `setting` = 'wysiwyg_css_uri'");
$csses = explode(',',((isset($row['value']))? $row['value']: ''));
$new = array();
foreach ($csses as $file) {
	if (substr($file, 0, 3) == '../') {
		$file = substr($file,3);
	}
	$new[] = $file;
}
$new = implode(',',$new);
if (strlen($new) && $new != $row['value']) {
	$sql_not_strict[] = "REPLACE INTO `geodesic_site_settings` SET `setting`='wysiwyg_css_uri', `value`=".$this->_db->qstr($new);
} else {
	//always make sql count the same
	$sql_not_strict[] = '';
}

# Allow auction start time column display for category specific
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD `auction_entry_date` tinyint(4) NOT NULL DEFAULT '0'";

# Allow auction start time column display for category specific
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD `classified_time_left` tinyint(4) NOT NULL DEFAULT '0'";


#create pages for new cart text
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES ('10202', '2', 'Cart Main', 'This is the main page for the Cart system')";
$sql_not_strict[] = "UPDATE `geodesic_pages_fonts` SET `page_id` = '10202' WHERE `page_id` = '199' AND `element` = 'page_title'";

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES ('10203', '2', 'Cart Checkout', 'This is the page for collecting billing data and payment information')";
$sql_not_strict[] = "UPDATE `geodesic_pages_fonts` SET `page_id` = '10203' WHERE `page_id` = '13' AND (`element` = 'page_title' OR `element` = 'page_description')";

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES ('10204', '2', 'Cart Success/Failure', 'This is the page shown after collecting payment data, confirming success or failure of the gateway process')";
$sql_not_strict[] = "UPDATE `geodesic_pages_fonts` SET `page_id` = '10204' WHERE `page_id` = '14' AND (`element` = 'page_title' OR `element` = 'page_description')";

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES ('10205', '2', 'Cart Listing Extras', 'This is the page that presents choices of listing extras to the user, and shows the current subtotal')";
$sql_not_strict[] = "UPDATE `geodesic_pages_fonts` SET `page_id` = '10205' WHERE `page_id` = '12' AND (`element` = 'ad_cost_features_header' OR `element` = 'ad_cost_features_description' or `element` = 'transaction_details_header_formatting')";
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `page_id` = '10205' WHERE `page_id` = '12' AND ((`message_id` >= '197' AND `message_id` <= '201') OR (`message_id` >= '215' AND `message_id` <= '218') OR (`message_id` >= '2260' AND `message_id` <= '2267'))";
$sql_not_strict[] = "UPDATE `geodesic_pages_messages_languages` SET `page_id` = '10205' WHERE `page_id` = '12' AND ((`text_id` >= '197' AND `text_id` <= '201') OR (`text_id` >= '215' AND `text_id` <= '218') OR (`text_id` >= '2260' AND `text_id` <= '2267'))";

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES ('10206', '10', 'Edit Approved Email', 'Email sent to a user when his Listing Edit process is approved.')";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`) VALUES ('10207', '10', 'Order Active Email', 'Email sent to a user when his Order is approved.')";

//oops. set these new pages to have different section_id's between here and the clean install .sql files
//run this query to unify, and maybe not too many people will notice. (should affect v4.0beta3 only)
$sql_not_strict[] = "UPDATE `geodesic_pages` SET `section_id` = '10' WHERE `section_id` = '2' AND (`page_id` = '10206' OR `page_id` = '10207')";

//bugfix for RC2
$sql_not_strict[] = "UPDATE `geodesic_pages` SET `applies_to` = '0' WHERE `applies_to` = '2' AND (`page_id` >= '10202' AND `page_id` <= '10205')";

//TODO: Update text links:
// a=24


		
//***********convert old invoices to new negative balances

//First, see if using positive balances (if so, there's nothing to convert over)

$pos_balance_setting = $this->_db->GetRow("SELECT `value` FROM `geodesic_site_settings` WHERE `setting` = 'positive_balances_only'");
if ($pos_balance_setting === false || !isset($pos_balance_setting['value'])) {
	$pos_balance_setting = $this->_db->GetRow("SELECT `positive_balances_only` as `value` FROM `geodesic_classifieds_configuration`");
}
if (!$pos_balance_setting['value']) {
	//get totals from unpaid invoices
	$invoices = array();
	$query = "SELECT bt.user_id, sum(bt.amount) as unpaid_invoice_total FROM `geodesic_balance_transactions` as bt, `geodesic_invoices` as i where i.date_paid=0 and bt.invoice_id=i.invoice_id group by bt.user_id";
	$result = $this->_db->Execute($query);
	if($result) {
		while($line = $result->FetchRow()) {
			$invoices[$line['user_id']]['unpaid'] = $line['unpaid_invoice_total'];
		}
	}
	//get totals from uninvoiced transactions
	$query = "select user_id, sum(amount) as uninvoiced from geodesic_balance_transactions where invoice_id = 0 AND (ad_id <> 0 OR auction_id <> 0) group by user_id";
	$result = $this->_db->Execute($query);
	if($result) {
		while($line = $result->FetchRow()) {
			$invoices[$line['user_id']]['uninvoiced'] = $line['uninvoiced'];
		}
	}
	
	//add to get current negative balance
	$total = array();
	foreach($invoices as $id => $user) {
		$total[$id] = -1 * ($user['unpaid'] + $user['uninvoiced']);
	}
	if(count($total) > 0) {
		//there are things that need carrying over -- force negative balance to be allowed
		$sql_not_strict[] = "INSERT INTO `geodesic_payment_gateway_registry` (`payment_gateway`, `index_key`, `val_string`) VALUES ('account_balance:0', 'allow_negative', '1')";
		foreach($total as $id => $value) {
			//consider also setting date_balance_negative to now?
			$sql_not_strict[] = "UPDATE `geodesic_userdata` SET `account_balance` = '".$value."' WHERE `id` = '".$id."'";
		}
		//mark imported values with -1, so they don't get imported again if upgrade is run a second time
		$sql_not_strict[] = "UPDATE `geodesic_balance_transactions` SET invoice_id = '-1' WHERE invoice_id = 0 AND (ad_id <> 0 OR auction_id <> 0)";
		$sql_not_strict[] = "UPDATE `geodesic_invoices` SET date_paid = '-1' WHERE date_paid = 0"; 
	} else {
		$sql_not_strict[] = '';
		$sql_not_strict[] = '';
		$sql_not_strict[] = '';
	}
} else {
	$sql_not_strict[] = '';
	$sql_not_strict[] = '';
	$sql_not_strict[] = '';
}

$module_rename = array(
	'module_featured_ads_1_level_2.php' => 'featured_1_level_2.php',
	'module_featured_ads_1_level_3.php' => 'featured_1_level_3.php',
	'module_featured_ads_1_level_4.php' => 'featured_1_level_4.php',
	'module_featured_ads_1_level_5.php' => 'featured_1_level_5.php',
	'module_featured_ads_2_level_2.php' => 'featured_2_level_2.php',
	'module_featured_ads_2_level_3.php' => 'featured_2_level_3.php',
	'module_featured_ads_2_level_4.php' => 'featured_2_level_4.php',
	'module_featured_ads_2_level_5.php' => 'featured_2_level_5.php',
	'module_hottest_ads.php' => 'hottest_ads.php',
	'module_newest_ads1.php' => 'newest_ads_1.php',
	'module_newest_ads2.php' => 'newest_ads_2.php',
	'module_featured_ads.php' => 'featured_ads_1.php',
	'module_featured_ads2.php' => 'featured_ads_2.php',
	'module_featured_ads3.php' => 'featured_ads_3.php',
	'module_featured_ads4.php' => 'featured_ads_4.php',
	'module_featured_ads5.php' => 'featured_ads_5.php',
	'module_featured_ads_from_category_1.php' => 'featured_category_1.php',
	'module_featured_ads_from_category_2.php' => 'featured_category_2.php',
	'module_display_category_browsing_options.php' => 'category_browsing_options.php',
	'module_display_category_level_navigation_1.php' => 'main_classified_level_navigation_1.php',
	'module_display_featured_pic_link.php' => 'featured_pic_link.php',
	'module_display_featured_text_link.php' => 'featured_text_link.php',
	'module_display_login_logout_html.php' => 'logged_in_out_html.php',
	'module_display_login_register.php' => 'login_register_link.php',
	'module_display_main_category_navigation_1.php' => 'main_classified_navigation_1.php',
	'module_display_newest_link_1_week.php' => 'newest_ads_link_1.php',
	'module_display_newest_link_2_week.php' => 'newest_ads_link_2.php',
	'module_display_newest_link_3_week.php' => 'newest_ads_link_3.php',
	'module_display_newest_link.php' => 'newest_ads_link.php',
	'module_display_php.php' => 'php_insert.php',
	'module_display_search_link.php' => 'search_link.php',
	'module_display_username.php' => 'display_username.php',
	'module_title_ads.php' => 'title.php',
	'module_total_live_users.php' => 'total_live_users.php',
	'module_featured_ads_pic_1.php' => 'featured_pic_1.php',
	'module_featured_ads_pic_1_level_2.php' => 'featured_pic_1_level_2.php',
	'module_featured_ads_pic_1_level_3.php' => 'featured_pic_1_level_3.php',
	'module_featured_ads_pic_1_level_4.php' => 'featured_pic_1_level_4.php',
	'module_featured_ads_pic_1_level_5.php' => 'featured_pic_1_level_5.php',
	'module_featured_ads_pic_2.php' => 'featured_pic_2.php',
	'module_featured_ads_pic_2_level_2.php' => 'featured_pic_2_level_2.php',
	'module_featured_ads_pic_2_level_3.php' => 'featured_pic_2_level_3.php',
	'module_featured_ads_pic_2_level_4.php' => 'featured_pic_2_level_4.php',
	'module_featured_ads_pic_2_level_5.php' => 'featured_pic_2_level_5.php',
	'module_featured_ads_pic_3.php' => 'featured_pic_3.php',
	'module_display_category_quick_navigation.php' => 'category_dropdown.php',
	'module_display_search_box_1.php' => 'module_search_box_1.php',
	'module_display_state_filters.php' => 'module_state_filter_1.php',
	'module_display_zip_filters.php' => 'module_zip_filter_1.php',
	'module_display_subcategory_navigation_1.php' => 'subcategory_navigation_1.php',
	'module_display_subcategory_navigation_2.php' => 'subcategory_navigation_2.php',
	'module_display_subcategory_navigation_3.php' => 'subcategory_navigation_3.php',
	'module_display_subcategory_navigation_4.php' => 'subcategory_navigation_4.php',
	'module_display_subcategory_navigation_5.php' => 'subcategory_navigation_5.php',
	'module_display_subcategory_navigation_6.php' => 'subcategory_navigation_6.php',
	'module_display_subcategory_navigation_7.php' => 'subcategory_navigation_7.php',
	'module_display_filters_1.php' => 'filter_display_1.php',
	'module_display_filters_2.php' => 'filter_display_2.php',
	'module_display_category_tree_1.php' => 'category_tree_1.php',
	'module_display_category_tree_2.php' => 'category_tree_2.php',
	'module_display_category_tree_3.php' => 'category_tree_3.php',
	'module_display_category_navigation_1.php' => 'classified_navigation_1.php',
	'module_display_category_navigation_2.php' => 'classified_navigation_2.php',
	'module_display_category_navigation_3.php' => 'classified_navigation_3.php' 
);

foreach($module_rename as $from => $to) {
	$sql_strict[] = "UPDATE `geodesic_pages` SET `module_file_name` = '".$to."' WHERE `module_file_name` = '".$from."'";
}


//Change "back to approval" text to "No more Photos"
$sql_not_strict [] = "UPDATE `geodesic_pages_messages_languages` SET `text`='No+more+photos' WHERE `text_id`=174 AND (`text`='Back to Approval Form' OR `text` = 'Back+to+Approval+Form')";

//Fix extra pages text to be longtext
$sql_not_strict [] = "ALTER TABLE `geodesic_extra_pages_registry` CHANGE `val_text` `val_text` LONGTEXT NOT NULL";

//adds ability to charge by starting price for reverse auctions
$sql_not_strict [] = "ALTER TABLE `geodesic_classifieds_price_increments` ADD `item_type` INT( 11 ) NOT NULL DEFAULT '1'";

//remove old listing process pages and their data
$where = " WHERE page_id in(12,13,14,65,173,174,175,178,179,180,181,182,199)";
$sql_not_strict[] = "DELETE FROM geodesic_pages ".$where;
$sql_not_strict[] = "DELETE FROM geodesic_pages_messages ".$where;
$sql_not_strict[] = "DELETE FROM geodesic_pages_messages_languages ".$where;
$sql_not_strict[] = "DELETE FROM geodesic_pages_fonts ".$where;
$sql_not_strict[] = "DELETE FROM geodesic_pages_templates ".$where;


//NEW e-mail queue table
//first, get rid of current one as it's probably wrong structure and will be empty
$e_result = $this->_db->GetRow('SELECT count(*) as count FROM `geodesic_email_queue`');
if (!$e_result || !isset($e_result['count']) || $e_result['count'] == 0) {
	$sql_not_strict[] = "DROP TABLE `geodesic_email_queue`";
}

//now add it back
$sql_not_strict[] = "
CREATE TABLE IF NOT EXISTS `geodesic_email_queue` (
  `email_id` int(11) NOT NULL auto_increment,
  `to_array` varchar(255) NOT NULL,
  `subject` varchar(128) NOT NULL,
  `content` text,
  `from_array` varchar(255) default NULL,
  `replyto_array` varchar(255) default NULL,
  `content_type` varchar(64) NOT NULL,
  `status` enum('sent','not_sent','error') NOT NULL,
  `sent` int(11) NOT NULL,
  PRIMARY KEY (`email_id`),
  KEY `status` (`status`),
  KEY `sent` (`sent`)
) AUTO_INCREMENT=1";


# Change html allowed to lower case
$tags = $this->_db->GetAll("SELECT * FROM `geodesic_html_allowed`");
foreach ($tags as $tag) {
	$tag_name = strtolower($tag['tag_name']);
	$sql_not_strict[] = "UPDATE `geodesic_html_allowed` SET `tag_name` = ".$this->_db->qstr($tag_name)." WHERE `tag_id` = {$tag['tag_id']} LIMIT 1";
}

$sql_not_strict[] = "ALTER TABLE `geodesic_user_communications` ADD `read` TINYINT(4) NOT NULL DEFAULT '0'";

//Add enabled column for anyone that doesn't have it yet.
$sql_not_strict [] = "ALTER TABLE `geodesic_plan_item` ADD `enabled` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$sql_not_strict [] = "ALTER TABLE `geodesic_plan_item` ADD INDEX `enabled` ( `enabled` )";

//add new template for use with My Account module
$sql = "select * from `geodesic_templates` where name='Basic Page Template with My Account Links'";
$result = $this->_db->Execute($sql);
if($result->RecordCount() == 0) {
	$sql_not_strict[] = "INSERT INTO `geodesic_templates` (`name`, `description`, `language_id`, `location`, `template_code`, `last_template`, `applies_to`, `storefront_template`, `storefront_template_default`, `full_page`) VALUES
	('Basic Page Template with My Account Links', 'Replaces the left column with the My Account Links module, allowing easier navigation of related pages', 1, '', '%3C%3Fxml+version%3D%221.0%22+encoding%3D%22utf-8%22%3F%3E%0D%0A%3C%21DOCTYPE+html+PUBLIC+%22-%2F%2FW3C%2F%2FDTD+XHTML+1.1%2F%2FEN%22+%22http%3A%2F%2Fwww.w3.org%2FTR%2Fxhtml11%2FDTD%2Fxhtml11.dtd%22%3E%0D%0A%3Chtml+xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F1999%2Fxhtml%22+xml%3Alang%3D%22en%22%3E%0D%0A%3Chead%3E%0D%0A%09%3Ctitle%3E%28%21MODULE_TITLE%21%29%3C%2Ftitle%3E%0D%0A%09%3Cscript+type%3D%22text%2Fjavascript%22%3E%0D%0A%09var+showDateShort+%3D+1%3B%0D%0A%09var+showDate%3D+2%3B%0D%0A%09var+showDateTime%3D+3%3B%0D%0A%09var+showTime%3D+4%3B%0D%0A%09function+ShowDateTime%28dateStyle%29%0D%0A%09%7B%0D%0A%09var+today+%3D+new+Date%28%29%3B%0D%0A%09var+dStr+%3D+%22%22%3B%0D%0A%09switch+%28dateStyle%29%0D%0A%09%7B%0D%0A%09case+showDateShort%3A%0D%0A%09dStr+%3D+today.toDateString%28%29%3B%0D%0A%09break%3B%0D%0A%09case+showDateTime%3A%0D%0A%09dStr+%3D+today.toLocaleString%28%29%3B%0D%0A%09break%3B%0D%0A%09case+showTime%3A%0D%0A%09dStr+%3D+today.toLocaleTimeString%28%29%3B%0D%0A%09break%3B%0D%0A%09case+showDate%3A%0D%0A%09default%3A%0D%0A%09dStr+%3D+today.toLocaleDateString%28%29%3B%0D%0A%09break%3B%0D%0A%09%7D%0D%0A%09document.write%28dStr%29%3B%0D%0A%09%7D%0D%0A%09%3C%2Fscript%3E%0D%0A%09%3Clink+href%3D%22geostyle.css%22+rel%3D%22stylesheet%22+type%3D%22text%2Fcss%22+%2F%3E%0D%0A%09%28%21CSSSTYLESHEET%21%29%0D%0A%3C%2Fhead%3E%0D%0A%3Cbody%3E%0D%0A%3Ctable+class%3D%22bodytable%22%3E%0D%0A%09%3Ctbody%3E%0D%0A%09%09%3Ctr%3E%0D%0A%09%09%09%3Ctd%3E%0D%0A%09%09%09%3C%21--+%23+BEGIN+HEADER+-+CHANGE+THIS+HTML+IN+THE+MODULES+AREA+OF+THE+ADMIN+--%3E%0D%0A%09%09%09%28%21LOGGED_IN_OUT_HTML%21%29+%0D%0A%09%09%09%3C%21--+END+HEADER+--%3E%0D%0A%09%09%09%3C%2Ftd%3E%0D%0A%09%09%3C%2Ftr%3E%0D%0A%09%09%3Ctr%3E%0D%0A%09%09%09%3Ctd%3E%0D%0A%09%09%09%3C%21--+%23+BEGIN+TOP+MENU+BAR+-+CHANGE+THIS+HTML+IN+THE+MODULES+AREA+OF+THE+ADMIN+--%3E%0D%0A%09%09%09%28%21LOGGED_IN_OUT_HTML_2%21%29+%0D%0A%09%09%09%3C%21--+%23+END+TOP+MENU+BAR+--%3E%0D%0A%09%09%09%3C%21--+%23+BEGIN+SUBMENU+BAR+-+CHANGE+THIS+HTML+IN+THE+MODULES+AREA+OF+THE+ADMIN+--%3E%0D%0A%09%09%09%28%21LOGGED_IN_OUT_HTML_3%21%29+%0D%0A%09%09%09%3C%21--+%23+END+SUBMENU+BAR+--%3E%0D%0A%09%09%09%3C%2Ftd%3E%0D%0A%09%09%3C%2Ftr%3E%0D%0A%09%09%3Ctr%3E%0D%0A%09%09%09%3Ctd%3E%3C%21--+MAIN+3+COLUMN+BODY+TABLE+START--%3E%0D%0A%09%09%09%3Ctable+class%3D%22maintable%22%3E%0D%0A%09%09%09%09%3Ctbody%3E%0D%0A%09%09%09%09%09%3Ctr+valign%3D%22top%22%3E%0D%0A%09%09%09%09%09%09%3Ctd+class%3D%22leftcolumn%22%3E%3C%21--+LEFT+COLUMN+-+MAIN+TABLE+START--%3E%0D%0A%09%09%09%09%09%09%28%21MY_ACCOUNT_LINKS%21%29%0D%0A%09%09%09%09%09%09%3C%21--+LEFT+COLUMN+-+MAIN+TABLE+END--%3E%3C%2Ftd%3E%0D%0A%09%09%09%09%09%09%3Ctd+class%3D%22centercolumn%22%3E%3C%21--+CENTER+COLUMN+-+MAIN+TABLE+START--%3E%0D%0A%09%09%09%09%09%09%28%21MAINBODY%21%29+%3C%21--+CENTER+COLUMN+-+MAIN+TABLE+END--%3E%3C%2Ftd%3E%0D%0A%09%09%09%09%09%3C%2Ftr%3E%0D%0A%09%09%09%09%3C%2Ftbody%3E%0D%0A%09%09%09%3C%2Ftable%3E%0D%0A%09%09%09%3C%21--+MAIN+3+COLUMN+BODY+TABLE+END--%3E%3C%2Ftd%3E%0D%0A%09%09%3C%2Ftr%3E%0D%0A%09%09%3Ctr%3E%0D%0A%09%09%09%3Ctd%3E%0D%0A%09%09%09%28%21LOGGED_IN_OUT_HTML_8%21%29%0D%0A%09%09%09%3C%2Ftd%3E%0D%0A%09%09%3C%2Ftr%3E%0D%0A%09%3C%2Ftbody%3E%0D%0A%3C%2Ftable%3E%0D%0A%0D%0A%3C%2Fbody%3E%0D%0A%3C%2Fhtml%3E%0D%0A', '', 0, 0, 0, 1)";
} 

//Add span to the allowed HTML table
$row = $this->_db->GetRow("SELECT `tag_id` FROM `geodesic_html_allowed` WHERE `tag_name`='span' LIMIT 1");
if ($row) {
	$sql_not_strict[] = "UPDATE `geodesic_html_allowed` SET `strongly_recommended` = '0' WHERE `tag_id`='{$row['tag_id']}'";
} else {
	$sql_not_strict[] = "INSERT INTO `geodesic_html_allowed` (`tag_name` ,`tag_status` ,`display` ,
			`replace_with`, `use_search_string`, `strongly_recommended`)
		VALUES ('span', '0', '1', '', '1', '0')";
}

//add pages for new My Account Home page
//note: would add these in main.sql, or at least in $sql_strict, but the queries rely on columns added earlier in sql_not_strict (which gets run last)

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `maxNodeDepth`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`, `alpha_across_columns`, `alt_order_by`) VALUES
(10208, 0, 'My Account Links Module', 'Displays links to User Account Management tools', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'my_account_links.php', '(!MY_ACCOUNT_LINKS!)', 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 1, '', '', 0, 0, 0, 1, 7, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, NULL, ',', ' &nbsp; >> sub|cat|list', 0, 0);";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_pages` (`page_id`, `section_id`, `name`, `description`, `special_instructions`, `internal_template`, `module`, `module_number_of_ads_to_display`, `module_display_header_row`, `module_display_business_type`, `module_display_photo_icon`, `module_display_ad_description`, `module_display_ad_description_where`, `module_display_price`, `module_display_entry_date`, `display_all_of_description`, `length_of_description`, `module_file_name`, `module_replace_tag`, `module_display_username`, `module_display_title`, `module_text_type`, `module_display_contact`, `module_display_phone1`, `module_display_phone2`, `module_display_address`, `module_display_optional_field_1`, `module_display_optional_field_2`, `module_display_optional_field_3`, `module_display_optional_field_4`, `module_display_optional_field_5`, `module_display_optional_field_6`, `module_display_optional_field_7`, `module_display_optional_field_8`, `module_display_optional_field_9`, `module_display_optional_field_10`, `module_display_optional_field_11`, `module_display_optional_field_12`, `module_display_optional_field_13`, `module_display_optional_field_14`, `module_display_optional_field_15`, `module_display_optional_field_16`, `module_display_optional_field_17`, `module_display_optional_field_18`, `module_display_optional_field_19`, `module_display_optional_field_20`, `module_display_city`, `module_display_state`, `module_display_country`, `module_display_zip`, `module_logged_in_html`, `module_logged_out_html`, `module_display_name`, `module_use_image`, `module_display_classified_id`, `module_thumb_width`, `module_thumb_height`, `module_display_attention_getter`, `module_number_of_columns`, `module_display_filter_in_row`, `cache_expire`, `use_category_cache`, `category_cache`, `number_of_browsing_columns`, `display_category_count`, `browsing_count_format`, `display_category_description`, `display_no_subcategory_message`, `display_category_image`, `display_unselected_subfilters`, `php_code`, `display_empty_message`, `module_category_level_to_display`, `module_category`, `module_display_new_ad_icon`, `photo_or_icon`, `module_type`, `module_display_number_bids`, `module_display_time_left`, `email`, `module_display_type_listing`, `module_display_type_text`, `module_display_listing_column`, `admin_label`, `applies_to`, `maxNodeDepth`, `module_display_company_name`, `module_display_sub_category_nav_links`, `module_sub_category_nav_prefix`, `module_sub_category_nav_separator`, `module_sub_category_nav_surrounding`, `alpha_across_columns`, `alt_order_by`) VALUES
(10209, 4, 'My Account Home Page', 'Replaces the User Management Home page, for use with the User Account Link Module', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 1, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'Use with User Account Link module', 0, 3, 0, 0, NULL, ',', ' &nbsp; >> sub|cat|list', 0, 0);";

//fix a duplicated text id (half-entered duplicate is never actually used in the software, so just pull it out of the DB)
$sql_not_strict[] = "DELETE FROM `geodesic_pages_messages_languages` WHERE `page_id` = '10163' AND text_id = '500235'";