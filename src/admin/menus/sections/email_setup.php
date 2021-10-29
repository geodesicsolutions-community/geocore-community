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

//----------------- E-MAIL SETUP
menu_category::addMenuCategory('email_setup', $parent_key, 'Email Setup', 'fa-envelope', '', '', $head_key);

    menu_page::addPage('email_general_config', 'email_setup', 'General Email Settings', 'fa-envelope', 'admin_email_config.php', 'Email_configuration');

    menu_page::addPage('email_notify_config', 'email_setup', 'Notification Email Settings', 'fa-envelope', 'admin_email_config.php', 'Email_configuration');
