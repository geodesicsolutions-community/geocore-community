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
## ##    17.01.0-25-g70691e1
##
##################################

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- USERS / USER GROUPS
menu_category::addMenuCategory('users', $parent_key, 'Users / User Groups', 'fa-users', '', '', $head_key);

if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
    menu_page::addPage('users_groups', 'users', 'User Groups Home', 'fa-users', 'admin_group_management_class.php', 'Group_management');
        menu_page::addPage('users_group_move', 'users_groups', 'Move Users', 'fa-users', 'admin_group_management_class.php', 'Group_management', 'sub_page');
        menu_page::addPage('users_group_edit', 'users_groups', 'Edit Group', 'fa-users', 'admin_group_management_class.php', 'Group_management', 'sub_page');
        menu_page::addPage('users_group_delete', 'users_groups', 'Delete Group', 'fa-users', 'admin_group_management_class.php', 'Group_management', 'sub_page');
        menu_page::addPage('users_group_price_edit', 'users_groups', 'Edit Price Plan', 'fa-users', 'admin_group_management_class.php', 'Group_management', 'sub_page');

        menu_page::addPage('users_group_registration', 'users_groups', 'Edit Registration Specifics', 'fa-users', 'admin_group_management_class.php', 'Group_management', 'sub_page');
        menu_page::addPage('users_group_questions', 'users_groups', 'Edit Group Questions', 'fa-users', 'admin_group_questions_class.php', 'Admin_category_questions', 'sub_page');
        menu_page::addPage('users_group_questions_new', 'users_groups', 'New Group Question', 'fa-users', 'admin_group_questions_class.php', 'Admin_category_questions', 'sub_page');
        menu_page::addPage('users_group_questions_edit', 'users_groups', 'Edit Group Question', 'fa-users', 'admin_group_questions_class.php', 'Admin_category_questions', 'sub_page');
        menu_page::addPage('users_group_questions_delete', 'users_groups', 'Delete Group Question', 'fa-users', 'admin_group_questions_class.php', 'Admin_category_questions', 'sub_page');
}

    menu_page::addPage('users_list', 'users', 'List Users', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management');
        menu_page::addPage('users_view', 'users_list', 'User Profile Display', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_remove', 'users_list', 'Remove User', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_edit', 'users_list', 'Edit User Profile', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_add', 'users_list', 'Add User', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_subs_change', 'users_list', 'Edit User Subscriptions', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_subs_delete', 'users_list', 'User Subscriptions Delete', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_restart_ad', 'users_list', 'Restart Ad', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_view_ad', 'users_list', 'View Ad', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_max_photos', 'users_list', 'Max Photos', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
        menu_page::addPage('users_ratings_detail', 'users_list', 'User Ratings Details', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');



    menu_page::addPage('users_search', 'users', 'Search Users', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management');

if (geoPC::is_ent()) {
    menu_page::addPage('user_export', 'users', 'Export Users', 'fa-users', 'admin_user_export.php', 'user_export');
    menu_page::addPage('importer', 'users', 'Import Users', 'fa-users', 'importer.php', 'admin_importer', 'main_page_nosave');
        menu_page::addPage('map_import', 'importer', 'Import Users', 'fa-users', 'importer.php', 'admin_importer', 'sub_page');
        menu_page::addPage('do_import', 'importer', 'Import Users', 'fa-users', 'importer.php', 'admin_importer', 'sub_page');
}

    menu_page::addPage('users_purge', 'users', 'Purge Inactive Users', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management');
    menu_page::addPage('users_confirm_purge', 'users_purge', 'Confirm User Purge', 'fa-users', 'admin_user_management_class.php', 'Admin_user_management', 'sub_page');
