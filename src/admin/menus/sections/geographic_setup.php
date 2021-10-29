<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.09.0-79-gb63e5d8
##
##################################

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- GEOGRAPHIC SETUP
menu_category::addMenuCategory('geographic_setup', $parent_key, 'Geographic Setup', 'fa-globe', '', '', $head_key);

    menu_page::addPage('regions', 'geographic_setup', 'Manage Regions', 'fa-globe', 'regions.php', 'RegionsManagement');
        menu_page::addPage('region_create', 'regions', 'Add Region', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');
        menu_page::addPage('region_create_bulk', 'regions', 'Bulk Add Region', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');
        menu_page::addPage('region_edit', 'regions', 'Edit Region', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');
        menu_page::addPage('region_edit_bulk', 'regions', 'Mass Edit Regions', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');
        menu_page::addPage('region_move', 'regions', 'Move Regions', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');
        menu_page::addPage('region_delete', 'regions', 'Delete Regions', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');
        menu_page::addPage('region_enabled', 'regions', 'Enable/Disable Region', 'fa-globe', 'regions.php', 'RegionsManagement', 'sub_page');

    menu_page::addPage('region_levels', 'geographic_setup', 'Levels', 'fa-globe', 'regions.php', 'RegionsManagement');
