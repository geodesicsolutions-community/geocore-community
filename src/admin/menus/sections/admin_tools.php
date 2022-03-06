<?php

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- ADMIN TOOLS & SETTINGS
menu_category::addMenuCategory('admin_tools_settings', $parent_key, 'Admin Tools', 'fa-gears', '', '', $head_key);

    menu_category::addMenuCategory('admin_messaging', 'admin_tools_settings', 'Messaging', '', '', '');
        menu_page::addPage('admin_messaging_send', 'admin_messaging', 'Send Message', 'fa-gears', 'admin_messaging_class.php', 'Admin_messaging');
        menu_page::addPage('admin_messaging_form', 'admin_messaging', 'Form Messages', 'fa-gears', 'admin_messaging_class.php', 'Admin_messaging');
            menu_page::addPage('admin_messaging_form_new', 'admin_messaging_form', 'New Form Message', 'fa-gears', 'admin_messaging_class.php', 'Admin_messaging');
            menu_page::addPage('admin_messaging_form_delete', 'admin_messaging_form', 'Delete Form Message', 'fa-gears', 'admin_messaging_class.php', 'Admin_messaging');
            menu_page::addPage('admin_messaging_form_edit', 'admin_messaging_form', 'Edit Form Message', 'fa-gears', 'admin_messaging_class.php', 'Admin_messaging');
        menu_page::addPage('admin_messaging_history', 'admin_messaging', 'Message History', 'fa-gears', 'admin_messaging_class.php', 'Admin_messaging');

    menu_page::addPage('admin_tools_view_ads', 'admin_tools_settings', 'View Expired Ads', 'fa-gears', 'admin_classauction_tools.php', 'Admin_classauction_tools');

    menu_page::addPage('admin_tools_password', 'admin_tools_settings', 'Change Password', 'fa-gears', 'admin_authentication_class.php', 'Admin_auth');

    menu_page::addPage('admin_tools_clean_images', 'admin_tools_settings', 'Remove Orphaned Images', 'fa-gears', 'admin_classauction_tools.php', 'Admin_classauction_tools');

if (!geoPC::is_whitelabel()) {
    menu_category::addMenuCategory('beta_tools', 'admin_tools_settings', 'BETA Tools', '', '', '');
        menu_page::addPage('beta_general_settings', 'beta_tools', 'BETA Settings', 'fa-gears', 'admin_beta_settings.php', 'Beta_configuration');
}

    menu_category::addMenuCategory('security_center', 'admin_tools_settings', 'Security Settings', '', '', '');
        menu_page::addPage('general_settings', 'security_center', 'General Security Settings', 'fa-gears', 'security_settings.php', 'securitySettings');

    menu_page::addPage('wysiwyg_general_config', 'admin_tools_settings', 'Editor Settings', 'fa-gears', 'admin_wysiwyg_config.php', 'wysiwyg_configuration');
