<?php

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
## ##    16.09.0-79-gb63e5d8
##
##################################

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- LISTING SETUP
menu_category::addMenuCategory('listing_setup', $parent_key, 'Listing Setup', 'fa-puzzle-piece', '', '', $head_key);

    menu_page::addPage('listing_general_settings', 'listing_setup', 'General Settings', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');

    menu_page::addPage('fields_to_use', 'listing_setup', 'Fields to Use', 'fa-puzzle-piece', 'fields_to_use.php', 'FieldsManage');

    menu_page::addPage('listing_hide_fields', 'listing_setup', 'Hide Fields', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');

    menu_page::addPage('leveled_fields', 'listing_setup', 'Multi-Level Fields', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage');
        menu_page::addPage('leveled_fields_add', 'leveled_fields', 'Add', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_edit', 'leveled_fields', 'Edit', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_fields_delete', 'leveled_fields', 'Delete', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_levels', 'leveled_fields', 'Levels', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_values', 'leveled_fields', 'Values', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_create', 'leveled_fields', 'Add Value', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_edit', 'leveled_fields', 'Edit Value', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_create_bulk', 'leveled_fields', 'Bulk Add Values', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_delete', 'leveled_fields', 'Delete Value', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_edit_bulk', 'leveled_fields', 'Mass Edit Value', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_enabled', 'leveled_fields', 'Enable/Disable Value', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_move', 'leveled_fields', 'Move Values', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');
        menu_page::addPage('leveled_field_value_copy', 'leveled_fields', 'Copy Values', 'fa-puzzle-piece', 'leveled_fields.php', 'LeveledFieldsManage', 'sub_page');

    menu_page::addPage('listing_placement_steps', 'listing_setup', 'Listing Placement Steps', 'fa-puzzle-piece', 'listing_steps.php', 'listingStepsManage');

    menu_page::addPage('listing_extras', 'listing_setup', 'Listing Extras', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');

if (geoMaster::is('auctions')) {
    menu_page::addPage('listing_bid_increments', 'listing_setup', 'Bid Increments', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
}

    menu_page::addPage('listing_payment_types', 'listing_setup', 'Payment Types', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');

    menu_page::addPage('listing_listing_durations', 'listing_setup', 'Listing Durations', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');

    menu_page::addPage('listing_allowed_uploads', 'listing_setup', 'Allowed Uploads', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
        menu_page::addPage('uploads_new_type', 'listing_allowed_uploads', 'New File Type', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration', 'sub_page');

    menu_page::addPage('listing_photo_upload_settings', 'listing_setup', 'File Upload &amp; Display Settings', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');

    menu_page::addPage('listing_currency_types', 'listing_setup', 'Currency Types', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
        menu_page::addPage('listing_currency_types_delete', 'listing_currency_types', 'Delete', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration', 'sub_page');
        menu_page::addPage('listing_currency_types_add', 'listing_currency_types', 'Add', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration', 'sub_page');
        menu_page::addPage('listing_currency_types_edit', 'listing_currency_types', 'Edit', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration', 'sub_page');

    menu_page::addPage('dropdowns', 'listing_setup', 'Pre-Valued Dropdowns', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions');
        menu_page::addPage('edit_dropdown', 'dropdowns', 'Edit', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('delete_dropdown', 'dropdowns', 'View Dropdowns', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('delete_dropdown_value', 'dropdowns', 'Edit', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('delete_dropdown_int', 'dropdowns', 'Confirm Deletion', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('new_dropdown', 'dropdowns', 'New Dropdown', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
