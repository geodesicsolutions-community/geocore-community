<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

//Loads the menu for site setup

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- SITE SETUP

menu_category::addMenuCategory('getting_started', $parent_key, 'Getting Started', 'fa-flag', '', '', $head_key);

    menu_page::addPage('checklist', 'getting_started', 'Checklist', 'fa-flag', 'getting_started.php', 'adminGettingStarted');
