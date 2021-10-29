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
## ##    16.09.0-84-g435bb22
##
##################################

//Loads a simplified version of the admin menu for the AdPlotter Edition

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');


//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');


include 'sections/top_level.php'; // hidden/standalone pages

$parent_key = $head_key = 0;

//include menu sections

//site setup, minus Master Switches
menu_category::addMenuCategory('site_setup', $parent_key, 'Site Setup', 'fa-gear', '', '', $head_key);
menu_page::addPage('main_general_settings', 'site_setup', 'General Settings', 'fa-gear', 'admin_site_configuration_class.php', 'Site_configuration');
menu_page::addPage('main_browsing_settings', 'site_setup', 'Browsing Settings', 'fa-gear', 'admin_site_configuration_class.php', 'Site_configuration');
menu_page::addPage('user_account_settings', 'site_setup', 'User Account Settings', 'fa-gear', 'admin_user_account_settings.php', 'admin_user_account_settings');
menu_page::addPage('main_html_allowed', 'site_setup', 'Allowed HTML', 'fa-gear', 'admin_html_allowed_class.php', 'HTML_allowed');
menu_page::addPage('main_badwords', 'site_setup', 'Badwords', 'fa-gear', 'admin_text_badwords_class.php', 'Text_badwords_management');
menu_page::addPage('main_ip_banning', 'site_setup', 'IP Banning', 'fa-gear', 'admin_site_configuration_class.php', 'Site_configuration');
menu_page::addPage('cache_config', 'site_setup', 'Cache', 'fa-gear', 'cache_manage.php', 'AdminCacheManage');
menu_page::addPage('clear_cache', 'cache_config', 'Clear Cache', 'fa-gear', 'cache_manage.php', 'AdminCacheManage');
menu_page::addPage('cron_config', 'site_setup', 'Cron Jobs', 'fa-gear', 'cron_manage.php', 'AdminCronManage');
menu_page::addPage('api_keys', 'site_setup', 'Remote API Security Keys', 'fa-gear', 'api.php', 'AdminAPIManage');

include 'sections/registration_setup.php';

// listing setup, minus stuff related to charging and listing extras
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
    menu_page::addPage('listing_payment_types', 'listing_setup', 'Payment Types', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
    menu_page::addPage('listing_listing_durations', 'listing_setup', 'Listing Durations', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
    menu_page::addPage('listing_allowed_uploads', 'listing_setup', 'Allowed Uploads', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
        menu_page::addPage('uploads_new_type', 'listing_allowed_uploads', 'New File Type', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration', 'sub_page');
    menu_page::addPage('listing_photo_upload_settings', 'listing_setup', 'File Upload &amp; Display Settings', 'fa-puzzle-piece', 'admin_ad_configuration_class.php', 'Ad_configuration');
    menu_page::addPage('dropdowns', 'listing_setup', 'Pre-Valued Dropdowns', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions');
        menu_page::addPage('edit_dropdown', 'dropdowns', 'Edit', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('delete_dropdown', 'dropdowns', 'View Dropdowns', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('delete_dropdown_value', 'dropdowns', 'Edit', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('delete_dropdown_int', 'dropdowns', 'Confirm Deletion', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');
        menu_page::addPage('new_dropdown', 'dropdowns', 'New Dropdown', 'fa-puzzle-piece', 'admin_extra_questions.php', 'admin_extra_questions', 'sub_page');


include 'sections/email_setup.php';
include 'sections/feedback.php';
include 'sections/categories.php';

$parent_key = $head_key = 1;

include 'sections/users_groups.php';
//PRICING intentionally omitted
//PAYMENTS intentionally omitted
include 'sections/orders.php';
include 'sections/geographic_setup.php';

$parent_key = $head_key = 2;

include 'sections/pages_management.php';
include 'sections/page_modules.php';
include 'sections/addons.php';
include 'sections/design.php';
include 'sections/languages.php';
include 'sections/admin_tools.php';

//let addons know we're using this menu
$addon = geoAddon::getInstance();
$addon->initAdmin('adplotter_admin');
