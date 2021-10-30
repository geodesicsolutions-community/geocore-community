<?php

//module_display_category_tree_1.php


$tpl_vars = array();
$tpl_vars['css_append'] = '_1';
$tpl_vars['link_label'] = $page->messages[1522];
$tpl_vars['link_text'] = $page->messages[1521];

require MODULES_DIR . 'shared/category_tree.php';
