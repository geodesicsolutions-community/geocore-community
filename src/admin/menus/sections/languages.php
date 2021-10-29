<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.07.0-13-g1b8edf9
##
##################################

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- LANGUAGES

menu_category::addMenuCategory('languages', $parent_key, 'Languages', 'fa-language', '', '', $head_key);

    menu_page::addPage('languages_home', 'languages', 'Manage Languages', 'fa-language', 'admin_text_management_class.php', 'Text_management');
        menu_page::addPage('languages_edit', 'languages_home', 'Edit Language', 'fa-language', 'admin_text_management_class.php', 'Text_management', 'sub_page');
        menu_page::addPage('languages_delete', 'languages_home', 'Delete Language', 'fa-language', 'admin_text_management_class.php', 'Text_management', 'sub_page');
    menu_page::addPage('languages_new', 'languages', 'Add New Language', 'fa-language', 'admin_text_management_class.php', 'Text_management');
    menu_page::addPage('languages_export', 'languages', 'Export Language Data', 'fa-language', 'admin_text_management_class.php', 'Text_management');
    menu_page::addPage('languages_import', 'languages', 'Import Language Data', 'fa-language', 'admin_text_management_class.php', 'Text_management');
        menu_page::addPage('languages_import_legacy', 'languages_import', 'Import Language (Legacy)', 'fa-language', 'admin_text_management_class.php', 'Text_management', 'sub_page');
