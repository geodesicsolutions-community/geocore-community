<?php

//module_display_category_navigation_1.php
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
## ##    7.5.3-36-gea36ae7
##
##################################

$tpl_vars = array();

//get the categories inside of this category

$tpl_vars['parent_category'] = (int)$page->site_category;

$tpl_vars['no_subcategory_text'] = $page->messages[1516];

$tpl_vars['column_css'] = 'classified_navigation_1';
if (($show_module['module_display_ad_description']) && ($tpl_vars['parent_category'])) {
    //set up "back to parent category" link
    $targetCategory = (int)$this->GetOne('SELECT `parent_id` FROM ' . geoTables::categories_table . ' WHERE `category_id` = ?', array($tpl_vars['parent_category']));
    $tpl_vars['parent_category_text'] = $page->messages[1890] . geoCategory::getName($targetCategory, true);
    $tpl_vars['parent_category_url'] = $this->get_site_setting('classifieds_file_name') . '?a=5&amp;b=' . $targetCategory;
}

require MODULES_DIR . 'shared/category_navigation.php';
