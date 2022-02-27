<?php

//Loads the menu for site setup

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- SITE SETUP

menu_category::addMenuCategory('site_setup', $parent_key, 'Site Setup', 'fa-gear', '', '', $head_key);

    menu_page::addPage('master_switches', 'site_setup', 'Master Switches', 'fa-gear', 'master.php', 'manageMaster');

    menu_page::addPage('main_general_settings', 'site_setup', 'General Settings', 'fa-gear', 'admin_site_configuration_class.php', 'Site_configuration');

    menu_page::addPage('main_browsing_settings', 'site_setup', 'Browsing Settings', 'fa-gear', 'admin_site_configuration_class.php', 'Site_configuration');

    menu_page::addPage('user_account_settings', 'site_setup', 'User Account Settings', 'fa-gear', 'admin_user_account_settings.php', 'admin_user_account_settings');

    //Detect if the old API is being used
    require(GEO_BASE_DIR . 'config.default.php');
if (isset($api_db_host) && strlen($api_db_host) > 0) {
    //old API settings are set in the config.php, so show the page
    //that displays the old API installations so that it is easy
    //to migrate settings to the new Bridge Addon.
    menu_page::addPage('main_api_integration', 'site_setup', 'API Integration', 'fa-gear', 'admin_module_loader_class.php', 'module_loader');
    //2 birds with one stone: Also show an alert in the admin, to make sure
    //they realize that the API has been replaced by the Bridge Addon:
    Notifications::addCheck(function () {
        return '<strong>Compatibility Alert:</strong> You still have the API database settings configured in your
            config.php, however the Geo API has been replaced by the new Bridge Addon.  After you have migrated the
            API installations over to use the new Bridge Addon, you can turn this notice off by removing the API
            settings from your config.php file.  Consult the user manual for more information about the Bridge Addon
            and how to migrate your current API installations.';
    });
}

    menu_page::addPage('main_html_allowed', 'site_setup', 'Allowed HTML', 'fa-gear', 'admin_html_allowed_class.php', 'HTML_allowed');

    menu_page::addPage('main_badwords', 'site_setup', 'Badwords', 'fa-gear', 'admin_text_badwords_class.php', 'Text_badwords_management');

    menu_page::addPage('main_ip_banning', 'site_setup', 'IP Banning', 'fa-gear', 'admin_site_configuration_class.php', 'Site_configuration');

    menu_page::addPage('cache_config', 'site_setup', 'Cache', 'fa-gear', 'cache_manage.php', 'AdminCacheManage');
        menu_page::addPage('clear_cache', 'cache_config', 'Clear Cache', 'fa-gear', 'cache_manage.php', 'AdminCacheManage');


    menu_page::addPage('cron_config', 'site_setup', 'Cron Jobs', 'fa-gear', 'cron_manage.php', 'AdminCronManage');

    menu_page::addPage('api_keys', 'site_setup', 'Remote API Security Keys', 'fa-gear', 'api.php', 'AdminAPIManage');
