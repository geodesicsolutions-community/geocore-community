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

// ----------------- PAGES MANAGEMENT
menu_category::addMenuCategory('pages_management', $parent_key, 'Pages Management', 'fa-file', 'admin_pages_class.php', 'Admin_pages', $head_key);

    menu_page::addPage('sections_home', 'pages_management', 'Pages Home', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_page', 'sections_home', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_edit_text', 'sections_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_tools', 'sections_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
        menu_page::addPage('sections_show', 'sections_home', 'Sub Section', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub-page');

    menu_page::addPage('sections_browsing', 'pages_management', 'Browsing Listings', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_browsing_page', 'sections_browsing', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_browsing_edit_text', 'sections_browsing_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_browsing_tools', 'sections_browsing_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');

    menu_page::addPage('sections_listing_process', 'pages_management', 'Listing Process', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_listing_process_page', 'sections_listing_process', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_listing_process_edit_text', 'sections_listing_process_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_listing_process_tools', 'sections_listing_process_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');

    menu_page::addPage('sections_registration', 'pages_management', 'Registration', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_registration_page', 'sections_registration', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_registration_edit_text', 'sections_registration_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_registration_tools', 'sections_registration_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');

    menu_page::addPage('sections_user_mgmt', 'pages_management', 'User Management', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_user_mgmt_page', 'sections_user_mgmt', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_user_mgmt_edit_text', 'sections_user_mgmt_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_user_mgmt_tools', 'sections_user_mgmt_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
        menu_page::addPage('sections_user_mgmt_show', 'sections_user_mgmt', 'Sub Section', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub-page');

    menu_page::addPage('sections_login_languages', 'pages_management', 'Login and Languages', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_login_languages_page', 'sections_login_languages', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_login_languages_edit_text', 'sections_login_languages_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_login_languages_tools', 'sections_login_languages_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');

    menu_page::addPage('sections_extra_pages', 'pages_management', 'Extra Pages', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_extra_pages_page', 'sections_extra_pages', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_extra_pages_edit_text', 'sections_extra_pages_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_extra_pages_tools', 'sections_extra_pages_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');

if (geoMaster::is('auctions')) {
    menu_page::addPage('sections_bidding', 'pages_management', 'Bidding', 'fa-file', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('sections_bidding_page', 'sections_bidding', 'Page Details', 'fa-file', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
            menu_page::addPage('sections_bidding_edit_text', 'sections_bidding_page', 'Edit Text', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
            menu_page::addPage('text_dev_bidding_tools', 'sections_bidding_page', 'Text Dev Tools', 'fa-file', 'admin_text_management_class.php', 'Text_management', 'sub_page');
}

    menu_page::addPage('sections_general_text', 'pages_management', 'General Template Text', 'fa-file', 'admin_pages_class.php', 'Admin_pages');

    menu_page::addPage('text_search', 'pages_management', 'Text Search', 'fa-file', 'search_text.php', 'SearchText');
