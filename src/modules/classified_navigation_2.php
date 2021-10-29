<?php

//module_display_category_navigation_2.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.0.2-4-gf4d2a43
##
##################################

$tpl_vars = array();

$tpl_vars['parent_category'] = (int)$page->site_category;

$tpl_vars['no_subcategory_text'] = $page->messages[1518];

$tpl_vars['column_css'] = 'classified_navigation_2';
if (($show_module['module_display_ad_description']) && ($tpl_vars['parent_category'])) {
    //set up "back to parent category" link
    $targetCategory = (int)$this->GetOne('SELECT `parent_id` FROM ' . geoTables::categories_table . ' WHERE `category_id` = ?', array($tpl_vars['parent_category']));
    $tpl_vars['parent_category_text'] = $page->messages[1891] . geoCategory::getName($targetCategory, true);
    $tpl_vars['parent_category_url'] = $this->get_site_setting('classifieds_file_name') . '?a=5&amp;b=' . $targetCategory;
}

require MODULES_DIR . 'shared/category_navigation.php';
