<?php

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

// ----------------- Top-Level Pages
//Put stuff like home page and all in this "hidden" category, basically
//anything that is linked to directly, and you want the breadcrumb to be
//such that that page is the only thing there, with no parent categories displayed.


menu_category::addMenuCategory('top_level', 0, '', '', '', '');

    menu_page::addPage('site_map', 'top_level', 'Admin Map', 'fa-home', 'map.php', 'geoAdminMap', 'sub_page');

    menu_page::addPage('home', 'top_level', 'Admin Home', 'fa-home', 'home.php', 'geoAdminHome', 'sub_page');

    menu_page::addPage('quick_find', 'top_level', 'Quick Find', 'fa-home', 'home.php', 'geoAdminHome', 'sub_page');
