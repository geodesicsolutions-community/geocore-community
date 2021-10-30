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

//change name of error page
$sql_not_strict[] = "UPDATE `geodesic_pages` SET `name` = 'Site Error Page &amp; Common Browsing Text',
`description` = 'Error page displayed when an error took place on the site that was unrecoverable.\n\nAlso used for common browsing text fields that are used on different browsing pages.' WHERE `page_id`=59 LIMIT 1";


//Change title/description for certain text fields
$txtMod = array (
	500655 => array ('title' => 'View Cart link', 'desc' => 'Can be an image tag, or just normal text.'),
	
);
foreach ($txtMod as $txtId => $data) {
	$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name` = '".urlencode($data['title'])."', `description`='".urlencode($data['desc'])."' WHERE `message_id`=$txtId";
}


//Fields to use changes
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD `what_fields_to_use` ENUM( 'site', 'parent', 'own' ) NOT NULL DEFAULT 'parent' AFTER `use_site_default`";
$sql_not_strict[] = "ALTER TABLE `geodesic_categories` ADD INDEX `what_fields_to_use` ( `what_fields_to_use` ) ";
//duplicate pre-existing functionality where nothing was inherited...
$sql_not_strict[] = "UPDATE `geodesic_categories` SET `what_fields_to_use`='parent' WHERE `use_site_default`=0";
$sql_not_strict[] = "UPDATE `geodesic_categories` SET `what_fields_to_use`='own' WHERE `use_site_default`=1";

//add setting for groups
$sql_not_strict[] = "ALTER TABLE `geodesic_groups` ADD `what_fields_to_use` ENUM( 'site', 'own' ) NOT NULL DEFAULT 'site'";
$sql_not_strict[] = "ALTER TABLE `geodesic_groups` ADD INDEX `what_fields_to_use` ( `what_fields_to_use` ) ";



# Allow expired table to track whether a classified was marked as sold or not
$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_expired` ADD `sold_displayed` INT( 11 ) NOT NULL AFTER `total`";

//figure out if the "old" fields to use settings have already been converted to
//be in new geodesic_fields table
$fieldsApplied = $this->tableExists('geodesic_fields');
if ($fieldsApplied) {
	//ok the fields table exists, see if the settings have been moved into the fields table
	//if the column display_photo_icon exists in classifieds configuration, then fields have not
	//been imported completely.
	$fieldsApplied = !$this->fieldExists('geodesic_classifieds_configuration', 'display_photo_icon');
}

if (!$fieldsApplied) {
	//Set fields to use based on current settings
	
	$defaultSettings = array (
		'display_photo_icon'=>0,
		'display_ad_title'=>0,
		'display_ad_description'=>0,
		'require_price'=>0,
		'display_price'=>0,
		'require_address_field'=>0,
		'display_browsing_address_field'=>0,
		'require_city_field'=>0,
		'display_browsing_city_field'=>0,
		'require_state_field'=>0,
		'display_browsing_state_field'=>0,
		'require_country_field'=>0,
		'display_browsing_country_field'=>0,
		'require_zip_field'=>0,
		'display_browsing_zip_field'=>0,
		'require_phone_1_override'=>0,
		'require_phone_2_override'=>0,
		'require_fax_override'=>0,
		'require_url_link_1'=>0,
		'require_url_link_2'=>0,
		'require_url_link_3'=>0,
		'require_email'=>0,
		'require_mapping_address_field'=>0,
		'require_mapping_city_field'=>0,
		'require_mapping_state_field'=>0,
		'require_mapping_country_field'=>0,
		'require_mapping_zip_field'=>0,
		'payment_types'=>0,
		'payment_types_use'=>0,
		'display_entry_date'=>0,
		'classified_time_left'=>0,
		'auction_entry_date'=>0,
		'display_time_left'=>0,
		'display_number_bids'=>0,
		'use_optional_field_1'=>0,
		'require_optional_field_1'=>0,
		'display_optional_field_1'=>0,
		'use_optional_field_2'=>0,
		'require_optional_field_2'=>0,
		'display_optional_field_2'=>0,
		'use_optional_field_3'=>0,
		'require_optional_field_3'=>0,
		'display_optional_field_3'=>0,
		'use_optional_field_4'=>0,
		'require_optional_field_4'=>0,
		'display_optional_field_4'=>0,
		'use_optional_field_5'=>0,
		'require_optional_field_5'=>0,
		'display_optional_field_5'=>0,
		'use_optional_field_6'=>0,
		'require_optional_field_6'=>0,
		'display_optional_field_6'=>0,
		'use_optional_field_7'=>0,
		'require_optional_field_7'=>0,
		'display_optional_field_7'=>0,
		'use_optional_field_8'=>0,
		'require_optional_field_8'=>0,
		'display_optional_field_8'=>0,
		'use_optional_field_9'=>0,
		'require_optional_field_9'=>0,
		'display_optional_field_9'=>0,
		'use_optional_field_10'=>0,
		'require_optional_field_10'=>0,
		'display_optional_field_10'=>0,
		'use_optional_field_11'=>0,
		'require_optional_field_11'=>0,
		'display_optional_field_11'=>0,
		'use_optional_field_12'=>0,
		'require_optional_field_12'=>0,
		'display_optional_field_12'=>0,
		'use_optional_field_13'=>0,
		'require_optional_field_13'=>0,
		'display_optional_field_13'=>0,
		'use_optional_field_14'=>0,
		'require_optional_field_14'=>0,
		'display_optional_field_14'=>0,
		'use_optional_field_15'=>0,
		'require_optional_field_15'=>0,
		'display_optional_field_15'=>0,
		'use_optional_field_16'=>0,
		'require_optional_field_16'=>0,
		'display_optional_field_16'=>0,
		'use_optional_field_17'=>0,
		'require_optional_field_17'=>0,
		'display_optional_field_17'=>0,
		'use_optional_field_18'=>0,
		'require_optional_field_18'=>0,
		'display_optional_field_18'=>0,
		'use_optional_field_19'=>0,
		'require_optional_field_19'=>0,
		'display_optional_field_19'=>0,
		'use_optional_field_20'=>0,
		'require_optional_field_20'=>0,
		'display_optional_field_20'=>0,
	);
	$oldSettings = array_intersect_key($this->get_site_settings(), $defaultSettings);
	$adSettings = $this->_db->GetRow("SELECT * FROM `geodesic_classifieds_ad_configuration`");
	
	$mergedSettings = array_merge($defaultSettings, $oldSettings, $adSettings);
	//clear up memory, we'll need all we can get on sites with tons of categories
	//with cat specific fields
	unset($oldSettings, $adSettings, $defaultSettings);
	
	function getQueriesFieldsToUseImport ($settings, &$sql_not_strict) {
		//up the time limit each time for sites with tons of category settings
		set_time_limit(600);
		$displayFieldsEnabled = serialize(array('browsing','search_fields','search_results'));
		$displayFieldsDisabled = serialize(array());
		
		$categoryId = (int)$settings['category_id'];
		//echo "Cat: $categoryId<br />Settings: <pre>".print_r($settings,1)."</pre>";
		
		/*
		 * Photo/icon field
		 */
		
		if (isset($settings['display_photo_icon']) && $settings['display_photo_icon']) {
			//photo turned "on" for site
			$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
				(0, $categoryId, 'photo', 1, 0, 0, 'other', '', 0, 'a:2:{i:0;s:8:\"browsing\";i:1;s:14:\"search_results\";}')";
		} else {
			$sql_not_strict[] = '';
		}
		/*
		 * Title field
		 */
		$titleDisplay = (isset($settings['display_ad_title']) && $settings['display_ad_title']);
		$titleEdit = (int)((bool)$settings['editable_title_field']);
		$titleLength = (int)$settings['title_length'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, 'title', 1, 1, $titleEdit, 'text', '', $titleLength, '".(($titleDisplay)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * Description field
		 */
		$descDisplay = (isset($settings['display_ad_description']) && $settings['display_ad_description']);
		$descEdit = (int)((bool)$settings['editable_description_field']);
		$descLength = (int)$settings['maximum_description_length'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, 'description', 1, 1, $descEdit, 'textarea', '', $descLength, '".(($descDisplay)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * Price field
		 */
		$name = 'price';
		$type = 'cost';
		$enabled = (int)((bool)$settings['use_price_field']);
		$required = (int)((bool)$settings['require_price']);
		$edit = (int)((bool)$settings['editable_price_field']);
		$length = (int)$settings['price_length'];
		$display = (bool)$settings['display_price'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * Address field
		 */
		$name = 'address';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_address_field']);
		$required = (int)((bool)$settings['require_address_field']);
		$edit = (int)((bool)$settings['editable_address_field']);
		$length = (int)$settings['address_length'];
		$display = (bool)$settings['display_browsing_address_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		
		/*
		 * City field
		 */
		$name = 'city';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_city_field']);
		$required = (int)((bool)$settings['require_city_field']);
		$edit = (int)((bool)$settings['editable_city_field']);
		$length = (int)$settings['city_length'];
		$display = (bool)$settings['display_browsing_city_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * State field
		 */
		$name = 'state';
		$type = 'dropdown';
		$enabled = (int)((bool)$settings['use_state_field']);
		$required = (int)((bool)$settings['require_state_field']);
		$edit = (int)((bool)$settings['editable_state_field']);
		$length = 0;//(int)$settings['city_length'];
		$display = (bool)$settings['display_browsing_state_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * country field
		 */
		$name = 'country';
		$type = 'dropdown';
		$enabled = (int)((bool)$settings['use_country_field']);
		$required = (int)((bool)$settings['require_country_field']);
		$edit = (int)((bool)$settings['editable_country_field']);
		$length = 0;//(int)$settings['city_length'];
		$display = (bool)$settings['display_browsing_country_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * zip field
		 */
		$name = 'zip';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_zip_field']);
		$required = (int)((bool)$settings['require_zip_field']);
		$edit = (int)((bool)$settings['editable_zip_field']);
		$length = (int)$settings['zip_length'];
		$display = (bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * phone_1 field
		 */
		$name = 'phone_1';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_phone_1_option_field']);
		$required = (int)((bool)$settings['require_phone_1_override']);
		$edit = (int)((bool)$settings['allow_phone_1_override']);
		$length = (int)$settings['phone_1_length'];
		$display = false;//(bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * phone_2 field
		 */
		$name = 'phone_2';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_phone_2_option_field']);
		$required = (int)((bool)$settings['require_phone_2_override']);
		$edit = (int)((bool)$settings['allow_phone_2_override']);
		$length = (int)$settings['phone_2_length'];
		$display = false;//(bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * fax field
		 */
		$name = 'fax';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_fax_field_option']);
		$required = (int)((bool)$settings['require_fax_override']);
		$edit = (int)((bool)$settings['allow_fax_override']);
		$length = (int)$settings['fax_length'];
		$display = false;//(bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * url_link_1 field
		 */
		$name = 'url_link_1';
		$type = 'url';
		$enabled = (int)((bool)$settings['use_url_link_1']);
		$required = (int)((bool)$settings['require_url_link_1']);
		$edit = (int)((bool)$settings['editable_url_link_1']);
		$length = (int)$settings['url_link_1_length'];
		$display = $enabled;//(bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * url_link_2 field
		 */
		$name = 'url_link_2';
		$type = 'url';
		$enabled = (int)((bool)$settings['use_url_link_2']);
		$required = (int)((bool)$settings['require_url_link_2']);
		$edit = (int)((bool)$settings['editable_url_link_2']);
		$length = (int)$settings['url_link_2_length'];
		$display = $enabled;//(bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * url_link_3 field
		 */
		$name = 'url_link_3';
		$type = 'url';
		$enabled = (int)((bool)$settings['use_url_link_3']);
		$required = (int)((bool)$settings['require_url_link_3']);
		$edit = (int)((bool)$settings['editable_url_link_3']);
		$length = (int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['display_browsing_zip_field'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * email field
		 */
		$name = 'email';
		$type = 'email';
		$enabled = (int)((bool)$settings['use_email_option_field']);
		$required = (int)((bool)$settings['require_email']);
		$edit = (int)((bool)$settings['use_email_override']);
		$length = 150;//(int)$settings['url_link_3_length'];
		$display = (bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * mapping_address field
		 */
		$name = 'mapping_address';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_mapping_address_field']);
		$required = (int)((bool)$settings['require_mapping_address_field']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 50;//(int)$settings['url_link_3_length'];
		$display = 0;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * mapping_city field
		 */
		$name = 'mapping_city';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_mapping_city_field']);
		$required = (int)((bool)$settings['require_mapping_city_field']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 20;//(int)$settings['url_link_3_length'];
		$display = 0;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * mapping_state field
		 */
		$name = 'mapping_state';
		$type = 'dropdown';
		$enabled = (int)((bool)$settings['use_mapping_state_field']);
		$required = (int)((bool)$settings['require_mapping_state_field']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = 0;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * mapping_country field
		 */
		$name = 'mapping_country';
		$type = 'dropdown';
		$enabled = (int)((bool)$settings['use_mapping_country_field']);
		$required = (int)((bool)$settings['require_mapping_country_field']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = 0;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * mapping_zip field
		 */
		$name = 'mapping_zip';
		$type = 'text';
		$enabled = (int)((bool)$settings['use_mapping_zip_field']);
		$required = (int)((bool)$settings['require_mapping_zip_field']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = 0;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * business_type field
		 */
		$name = 'business_type';
		$type = 'other';
		$enabled = (int)((bool)$settings['display_business_type']);
		$required = 0;//(int)((bool)$settings['require_mapping_zip_field']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * payment_types field
		 */
		$name = 'payment_types';
		$type = 'other';
		$enabled = (int)((bool)$settings['payment_types']);
		$required = (int)((bool)$settings['payment_types_use']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * classified_start field
		 */
		$name = 'classified_start';
		$type = 'other';
		$enabled = (int)((bool)$settings['display_entry_date']);
		$required = 0;//(int)((bool)$settings['payment_types_use']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * classified_time_left field
		 */
		$name = 'classified_time_left';
		$type = 'other';
		$enabled = (int)((bool)$settings['classified_time_left']);
		$required = 0;//(int)((bool)$settings['payment_types_use']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * auction_start field
		 */
		$name = 'auction_start';
		$type = 'other';
		$enabled = (int)((bool)$settings['auction_entry_date']);
		$required = 0;//(int)((bool)$settings['payment_types_use']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * auction_time_left field
		 */
		$name = 'auction_time_left';
		$type = 'other';
		$enabled = (int)((bool)$settings['display_time_left']);
		$required = 0;//(int)((bool)$settings['payment_types_use']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		/*
		 * num_bids field
		 */
		$name = 'num_bids';
		$type = 'other';
		$enabled = (int)((bool)$settings['display_number_bids']);
		$required = 0;//(int)((bool)$settings['payment_types_use']);
		$edit = 0;//(int)((bool)$settings['use_email_override']);
		$length = 0;//(int)$settings['url_link_3_length'];
		$display = $enabled;//(bool)$settings['publically_expose_email'];
		
		$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
		(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
		
		for ($i=1; $i<=20; $i++) {
			/*
			 * optional_field_$i field
			 */
			$name = 'optional_field_'.$i;
			$type = ($settings['optional_'.$i.'_number_only'])? 'number' : 'text';
			$typeData = '';
			$use = $settings['use_optional_field_'.$i];
			$enabled = (int)((bool)$use);
			if ($use == 2) {
				$type = 'cost';
			}
			if ($type == 'text') {
				//figure out real type, if it's a dropdown
				$fieldType = $settings['optional_'.$i.'_field_type'];
				if ($fieldType == 0) {
					//just normal text field
					
				} else if ($fieldType == -1) {
					//textarea
					$type = 'textarea';
				} else if ($fieldType > 0) {
					$type = 'dropdown';
					$typeData = $fieldType;
					if ($settings['optional_'.$i.'_other_box']) {
						$typeData .= ':use_other';
					}
				}
			}
			$required = (int)((bool)$settings['require_optional_field_'.$i]);
			$edit = (int)((bool)$settings['optional_'.$i.'_field_editable']);
			$length = (int)$settings['optional_'.$i.'_length'];
			$display = (bool)$settings['display_optional_field_'.$i];
			
			$sql_not_strict[] = "INSERT INTO `geodesic_fields` (`group_id`, `category_id`, `field_name`, `is_enabled`, `is_required`, `can_edit`, `field_type`, `type_data`, `text_length`, `display_locations`) VALUES
			(0, $categoryId, '$name', $enabled, $required, $edit, '$type', '$typeData', $length, '".(($display)? $displayFieldsEnabled : $displayFieldsDisabled)."')";
			
		}
	}
	
	//get all category settings, plus a dummy one to set "site wide" settings at the same time
	//NOTE:  use_site_default = 0 means use site default **NEGATIVE LOGIC** - the
	//below query is correct, it is not a bug...
	$catResult = $this->_db->Execute("SELECT * FROM `geodesic_categories` WHERE `use_site_default`=1");
	
	while ($catSettings = $catResult->FetchRow()) {
		$settings = array_merge($mergedSettings, $catSettings);
		getQueriesFieldsToUseImport($settings, $sql_not_strict);
		unset($settings);
	}
	$settings = $mergedSettings;
	$settings['category_id'] = 0;
	//do it for base settings
	getQueriesFieldsToUseImport($settings, $sql_not_strict);
	
	//Get rid of columns/settings no longer used
	$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_configuration`
  DROP `use_state_field`, DROP `use_country_field`, DROP `require_address_field`, DROP `require_city_field`,
  DROP `require_state_field`, DROP `require_country_field`, DROP `require_zip_field`, DROP `display_ad_description`,
  DROP `display_browsing_city_field`, DROP `display_browsing_state_field`, DROP `display_browsing_country_field`, DROP `display_browsing_zip_field`,
  DROP `display_price`, DROP `display_business_type`, DROP `display_entry_date`, DROP `display_photo_icon`,
  DROP `use_optional_field_1`, DROP `display_optional_field_1`, DROP `require_optional_field_1`, DROP `use_optional_field_2`,
  DROP `display_optional_field_2`, DROP `require_optional_field_2`, DROP `use_optional_field_3`, DROP `display_optional_field_3`,
  DROP `require_optional_field_3`, DROP `use_optional_field_4`, DROP `display_optional_field_4`, DROP `require_optional_field_4`,
  DROP `use_optional_field_5`, DROP `display_optional_field_5`, DROP `require_optional_field_5`, DROP `use_optional_field_6`,
  DROP `display_optional_field_6`, DROP `require_optional_field_6`, DROP `use_optional_field_7`, DROP `display_optional_field_7`,
  DROP `require_optional_field_7`, DROP `use_optional_field_8`, DROP `display_optional_field_8`, DROP `require_optional_field_8`,
  DROP `use_optional_field_9`, DROP `display_optional_field_9`, DROP `require_optional_field_9`,
  DROP `use_optional_field_10`, DROP `display_optional_field_10`, DROP `require_optional_field_10`,
  DROP `require_optional_field_11`, DROP `use_optional_field_11`, DROP `display_optional_field_11`,
  DROP `require_optional_field_12`, DROP `use_optional_field_12`, DROP `display_optional_field_12`,
  DROP `require_optional_field_13`, DROP `use_optional_field_13`, DROP `display_optional_field_13`,
  DROP `require_optional_field_14`, DROP `use_optional_field_14`, DROP `display_optional_field_14`,
  DROP `require_optional_field_15`, DROP `use_optional_field_15`, DROP `display_optional_field_15`,
  DROP `require_optional_field_16`, DROP `use_optional_field_16`, DROP `display_optional_field_16`,
  DROP `require_optional_field_17`, DROP `use_optional_field_17`, DROP `display_optional_field_17`,
  DROP `require_optional_field_18`, DROP `use_optional_field_18`, DROP `display_optional_field_18`,
  DROP `require_optional_field_19`, DROP `use_optional_field_19`, DROP `display_optional_field_19`,
  DROP `require_optional_field_20`, DROP `use_optional_field_20`, DROP `display_optional_field_20`,
  DROP `use_url_link_1`, DROP `require_url_link_1`, DROP `use_url_link_2`, DROP `require_url_link_2`,
  DROP `use_url_link_3`, DROP `require_url_link_3`, DROP `display_ad_title`, DROP `display_number_bids`,
  DROP `display_time_left`, DROP `payment_types`, DROP `payment_types_use`, DROP `auction_entry_date`,
  DROP `classified_time_left`";
	
	$sql_not_strict[] = "ALTER TABLE `geodesic_classifieds_ad_configuration`
  DROP `use_price_field`, DROP `use_city_field`, DROP `use_zip_field`, DROP `use_state_field`,
  DROP `use_country_field`, DROP `maximum_title_length`, DROP `maximum_description_length`,
  DROP `optional_1_other_box`, DROP `optional_1_field_type`, DROP `optional_1_length`,
  DROP `optional_1_number_only`, DROP `optional_2_field_type`, DROP `optional_2_other_box`,
  DROP `optional_2_length`, DROP `optional_2_number_only`, DROP `optional_3_field_type`,
  DROP `optional_3_other_box`, DROP `optional_3_length`, DROP `optional_3_number_only`,
  DROP `optional_4_field_type`, DROP `optional_4_other_box`, DROP `optional_4_length`,
  DROP `optional_4_number_only`, DROP `optional_5_field_type`, DROP `optional_5_other_box`,
  DROP `optional_5_length`, DROP `optional_5_number_only`, DROP `optional_6_field_type`,
  DROP `optional_6_other_box`, DROP `optional_6_length`, DROP `optional_6_number_only`,
  DROP `optional_7_field_type`, DROP `optional_7_other_box`, DROP `optional_7_length`,
  DROP `optional_7_number_only`, DROP `optional_8_field_type`, DROP `optional_8_other_box`,
  DROP `optional_8_length`, DROP `optional_8_number_only`, DROP `optional_9_field_type`,
  DROP `optional_9_other_box`, DROP `optional_9_length`, DROP `optional_9_number_only`,
  DROP `optional_10_field_type`, DROP `optional_10_other_box`, DROP `optional_10_length`,
  DROP `optional_10_number_only`, DROP `use_email_option_field`, DROP `use_email_override`,
  DROP `use_phone_1_option_field`, DROP `allow_phone_1_override`, DROP `use_phone_2_option_field`,
  DROP `allow_phone_2_override`, DROP `use_fax_field_option`, DROP `allow_fax_override`,
  DROP `publically_expose_email`, DROP `editable_price_field`, DROP `editable_zip_field`,
  DROP `editable_city_field`, DROP `editable_state_field`, DROP `editable_country_field`,
  DROP `editable_title_field`, DROP `editable_description_field`, DROP `optional_1_field_editable`,
  DROP `optional_2_field_editable`, DROP `optional_3_field_editable`, DROP `optional_4_field_editable`,
  DROP `optional_5_field_editable`, DROP `optional_6_field_editable`, DROP `optional_7_field_editable`,
  DROP `optional_8_field_editable`, DROP `optional_9_field_editable`, DROP `optional_10_field_editable`,
  DROP `display_business_type`, DROP `use_mapping_address_field`, DROP `use_mapping_city_field`,
  DROP `use_mapping_state_field`, DROP `use_mapping_country_field`, DROP `use_mapping_zip_field`,
  DROP `optional_11_length`, DROP `optional_11_other_box`, DROP `optional_11_field_type`,
  DROP `optional_11_number_only`, DROP `optional_11_field_editable`, DROP `optional_12_length`,
  DROP `optional_12_other_box`, DROP `optional_12_field_type`, DROP `optional_12_number_only`,
  DROP `optional_12_field_editable`, DROP `optional_13_length`, DROP `optional_13_other_box`,
  DROP `optional_13_field_type`, DROP `optional_13_number_only`, DROP `optional_13_field_editable`,
  DROP `optional_14_length`, DROP `optional_14_other_box`, DROP `optional_14_field_type`,
  DROP `optional_14_number_only`, DROP `optional_14_field_editable`, DROP `optional_15_length`,
  DROP `optional_15_other_box`, DROP `optional_15_field_type`, DROP `optional_15_number_only`,
  DROP `optional_15_field_editable`, DROP `optional_16_length`, DROP `optional_16_other_box`,
  DROP `optional_16_field_type`, DROP `optional_16_number_only`, DROP `optional_16_field_editable`,
  DROP `optional_17_length`, DROP `optional_17_other_box`, DROP `optional_17_field_type`,
  DROP `optional_17_number_only`, DROP `optional_17_field_editable`, DROP `optional_18_length`,
  DROP `optional_18_other_box`, DROP `optional_18_field_type`, DROP `optional_18_number_only`,
  DROP `optional_18_field_editable`, DROP `optional_19_length`, DROP `optional_19_other_box`,
  DROP `optional_19_field_type`, DROP `optional_19_number_only`, DROP `optional_19_field_editable`,
  DROP `optional_20_length`, DROP `optional_20_other_box`, DROP `optional_20_field_type`,
  DROP `optional_20_number_only`, DROP `optional_20_field_editable`, DROP `title_length`,
  DROP `price_length`, DROP `city_length`, DROP `zip_length`, DROP `phone_1_length`,
  DROP `phone_2_length`, DROP `fax_length`, DROP `use_url_link_1`, DROP `editable_url_link_1`,
  DROP `url_link_1_length`, DROP `use_url_link_2`, DROP `editable_url_link_2`, DROP `url_link_2_length`,
  DROP `use_url_link_3`, DROP `editable_url_link_3`, DROP `url_link_3_length`, DROP `use_address_field`,
  DROP `editable_address_field`, DROP `address_length`";
	$sql_not_strict[] = "ALTER TABLE `geodesic_categories`
  DROP `display_business_type`, DROP `display_photo_icon`, DROP `display_price`, DROP `use_price_field`,
  DROP `display_browsing_zip_field`, DROP `use_zip_field`, DROP `display_browsing_city_field`,
  DROP `use_city_field`, DROP `display_browsing_state_field`, DROP `use_state_field`,
  DROP `display_browsing_country_field`, DROP `use_country_field`, DROP `display_entry_date`,
  DROP `use_email_option_field`, DROP `use_email_override`, DROP `publically_expose_email`,
  DROP `use_phone_1_option_field`, DROP `allow_phone_1_override`, DROP `use_phone_2_option_field`,
  DROP `allow_phone_2_override`, DROP `use_fax_field_option`, DROP `allow_fax_override`,
  DROP `use_optional_field_1`, DROP `display_optional_field_1`, DROP `use_optional_field_2`,
  DROP `display_optional_field_2`, DROP `use_optional_field_3`, DROP `display_optional_field_3`,
  DROP `use_optional_field_4`, DROP `display_optional_field_4`, DROP `use_optional_field_5`,
  DROP `display_optional_field_5`, DROP `use_optional_field_6`, DROP `display_optional_field_6`,
  DROP `use_optional_field_7`, DROP `display_optional_field_7`, DROP `use_optional_field_8`,
  DROP `display_optional_field_8`, DROP `use_optional_field_9`, DROP `display_optional_field_9`,
  DROP `use_optional_field_10`, DROP `display_optional_field_10`, DROP `use_mapping_address_field`,
  DROP `use_mapping_city_field`, DROP `use_mapping_state_field`, DROP `use_mapping_country_field`,
  DROP `use_mapping_zip_field`, DROP `use_optional_field_11`, DROP `display_optional_field_11`,
  DROP `use_optional_field_12`, DROP `display_optional_field_12`, DROP `use_optional_field_13`,
  DROP `display_optional_field_13`, DROP `use_optional_field_14`, DROP `display_optional_field_14`,
  DROP `use_optional_field_15`, DROP `display_optional_field_15`, DROP `use_optional_field_16`,
  DROP `display_optional_field_16`, DROP `use_optional_field_17`, DROP `display_optional_field_17`,
  DROP `use_optional_field_18`, DROP `display_optional_field_18`, DROP `use_optional_field_19`,
  DROP `display_optional_field_19`, DROP `use_optional_field_20`, DROP `display_optional_field_20`,
  DROP `display_ad_description`, DROP `use_url_link_1`, DROP `use_url_link_2`, DROP `use_url_link_3`,
  DROP `display_ad_title`, DROP `display_number_bids`, DROP `display_time_left`,
  DROP `payment_types`, DROP `use_address_field`, DROP `display_browsing_address_field`,
  DROP `auction_entry_date`, DROP `classified_time_left`, DROP `use_site_default`";
	
	//remove from geodesic_site_settings
	$siteSettings = array (
		'display_photo_icon',
		'display_ad_title',
		'display_ad_description',
		'display_price',
		'require_address_field',
		'require_city_field',
		'display_browsing_city_field',
		'use_state_field',
		'require_state_field',
		'display_browsing_state_field',
		'use_country_field',
		'require_country_field',
		'display_browsing_country_field',
		'require_zip_field',
		'display_browsing_zip_field',
		'use_url_link_1',
		'use_url_link_2',
		'use_url_link_3',
		'require_url_link_1',
		'require_url_link_2',
		'require_url_link_3',
		'display_business_type',
		'payment_types',
		'payment_types_use',
		'display_entry_date',
		'classified_time_left',
		'auction_entry_date',
		'display_time_left',
		'display_number_bids',
		'require_price',
		'display_browsing_address_field',
		'require_phone_1_override',
		'require_phone_2_override',
		'require_fax_override',
		'require_email',
		'require_mapping_address_field',
		'require_mapping_city_field',
		'require_mapping_state_field',
		'require_mapping_country_field',
		'require_mapping_zip_field',
	);
	for ($i=1; $i<=20; $i++) {
		$siteSettings[] = 'use_optional_field_'.$i;
		$siteSettings[] = 'require_optional_field_'.$i;
		$siteSettings[] = 'display_optional_field_'.$i;
	}
	$sql_not_strict[] = "DELETE FROM `geodesic_site_settings` WHERE `setting` IN ('".implode("', '", $siteSettings)."')";
}

//default to turn on use CHMOD
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` (`setting`,`value`) VALUES ('useCHMOD','1')";

//convert all currently referenced (!TAG_NAME!) in db to not do that.
$tagNames = $this->_db->GetAll("SELECT `page_id`, `module_replace_tag` FROM `geodesic_pages` WHERE `module`=1");
foreach ($tagNames as $row) {
	$search = array ('(!','!)');
	$tag = trim(str_replace($search, '', $row['module_replace_tag']));
	if ($tag) {
		$tag = strtolower($tag);
		$sql_not_strict[] = "UPDATE `geodesic_pages` SET `module_replace_tag`='$tag' WHERE `page_id`={$row['page_id']}";
	}
}

$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` (`setting`,`value`) VALUES ('notify_seller_unsuccessful_auction','1')";

//default turn on show thingy in billing info
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'populate_billing_info', `value` = '1'";

//new image settings
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'gallery_main_width', `value` = '500'";
$sql_not_strict[] = "INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = 'gallery_main_height', `value` = '500'";

//sanity check to make sure group default settings are in place (reports of some trouble with that when upgrading from very old versions)
$sql_not_strict[] = "UPDATE `geodesic_groups` SET `price_plan_id` = '1' WHERE `price_plan_id` = '0'";
$sql_not_strict[] = "UPDATE `geodesic_groups` SET `auction_price_plan_id` = '5' WHERE `auction_price_plan_id` = '0'";
$default = $this->_db->GetOne("SELECT `group_id` FROM `geodesic_groups` WHERE `default_group` = '1'");
if(!$default) {
	//need to have a default group...let's set it on the lowest id
	$low = $this->_db->GetOne("SELECT `group_id` FROM `geodesic_groups` ORDER BY `group_id` ASC LIMIT 1");
	$sql_not_strict[] = "UPDATE `geodesic_groups` SET `default_group` = '1' WHERE `group_id` = '".$low."'";
}

//change a couple text entries' titles to read 'listings' instead of 'auctions,' since they're used for classifieds, too
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name` = 'Renewed+Listings+Awaiting+Admininstration+Approval+header', description` = 'Labels+the+table+showing+listings+that+have+been+renewed+but+are+waiting+for+admin+approval' WHERE `message_id` = 102854";
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name` = 'Renewed+listing+instructions', description` = 'Description+of+renewed+listings+awaiting+approval' WHERE `message_id` = 102855";

//correct another mis-labeled message
$sql_not_strict[] = "UPDATE `geodesic_pages_messages` SET `name` = 'Winning+Dutch+Seller+Email+Footer' WHERE `message_id` = 102780";
