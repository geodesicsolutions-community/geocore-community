<?php

//make sure loading in admin
defined('IN_ADMIN') or die('No Access.');

//Set parent key and head key to defaults if not set
$parent_key = (isset($parent_key)) ? $parent_key : 0;
$head_key = (isset($head_key)) ? $head_key : 0;

// ----------------- FEEDBACK
if (geoMaster::is('auctions')) {
    menu_category::addMenuCategory('feedback', $parent_key, 'Feedback', 'fa-star', '', '', $head_key);

        menu_page::addPage('GlobalSettings', 'feedback', 'Feedback Management', 'fa-star', 'Admin_Feedback.class.php', 'Admin_Feedback');

        menu_page::addPage('IncrementSettings', 'feedback', 'Edit Feedback Increments', 'fa-star', 'Admin_Feedback.class.php', 'Admin_Feedback');

        menu_page::addPage('feedback_show', 'GlobalSettings', 'Feedback', 'fa-star', 'Admin_Feedback.class.php', 'Admin_Feedback', 'sub_page');
}
