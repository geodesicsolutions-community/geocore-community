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

// ----------------- PAGE MODULES
menu_category::addMenuCategory('page_modules', $parent_key, 'Page Modules', 'fa-cubes', '', '', $head_key);

    menu_page::addPage('modules_home', 'page_modules', 'Modules Home', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages');
        menu_page::addPage('modules_page', 'modules_home', 'Edit Module', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
        menu_page::addPage('modules_edit_text', 'modules_home', 'Edit Module Text', 'fa-cubes', 'admin_text_management_class.php', 'Text_management', 'sub_page');

    menu_page::addPage('modules_browse', 'page_modules', 'Browsing', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages');

    menu_page::addPage('modules_featured', 'page_modules', 'Featured', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages');

    menu_page::addPage('modules_newest', 'page_modules', 'Newest', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages');

    menu_page::addPage('modules_misc', 'page_modules', 'Misc.', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages');

    menu_page::addPage('modules_misc_display', 'page_modules', 'Misc. Display', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages');

    //catch all (same as pages management)
    menu_page::addPage('modules_show', 'modules_home', 'Display Module', 'fa-cubes', 'admin_pages_class.php', 'Admin_pages', 'sub_page');
