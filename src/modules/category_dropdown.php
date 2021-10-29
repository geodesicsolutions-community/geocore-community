<?php

//module_display_category_quick_navigation.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.6.3-76-ga85fd85
##
##################################

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

if (!isset($page->quick_nav_id)) {
    $page->quick_nav_id = 0;
} else {
    $page->quick_nav_id++;
}

$cat = ($show_module['module_display_sub_category_nav_links']) ? $show_module['number_of_browsing_columns'] : 0;
$tpl_vars = array();

if (!defined('MADE_CATEGORY_DROPDOWN')) {
    //something in the really old site-class code is setting these cache vars incorrectly before it should. Clear them here on only the first pass to make sure we're getting the right dropdown
    $page->category_dropdown_settings_array = $page->category_dropdown_id_array = $page->category_dropdown_name_array = array();
    define('MADE_CATEGORY_DROPDOWN', 1);
}

$tpl_vars['options'] = $page->get_category_dropdown('category_quick_nav', 0, 0, 0, $page->messages[500819], 3, $cat);
$tpl_vars['nav_id'] = $page->quick_nav_id;

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
