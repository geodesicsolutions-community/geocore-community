<?php

//module_display_main_category_navigation_1.php



if (isset($page->category_configuration->CATEGORY_NAME)) {
    $current_category_name = $page->category_configuration->CATEGORY_NAME;
}

$tpl_vars = array();

//get the categories inside of this category

$tpl_vars['parent_category'] = 0;

$tpl_vars['no_subcategory_text'] = $page->messages[1516];

$tpl_vars['column_css'] = 'main_classified_navigation';

require MODULES_DIR . 'shared/category_navigation.php';
