<?php

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- ADDON MANAGEMENT
menu_category::addMenuCategory('addon_management', $parent_key, 'Addons', 'fa-plug', '', '', $head_key);

    menu_page::addPage('addon_tools', 'addon_management', 'Manage Addons', 'fa-plug', 'addon_manage.php', 'Addon_Manage');
        menu_page::addPage('edit_addon_text', 'addon_tools', 'Edit Text', 'fa-plug', 'addon_manage.php', 'Addon_Manage', 'sub-page');
