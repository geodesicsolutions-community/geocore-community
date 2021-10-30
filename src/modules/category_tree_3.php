<?php

//category_tree_3.php


$tpl_vars = array();
$tpl_vars['css_append'] = '_3';
$tpl_vars['link_label'] = $page->messages[1525];
$tpl_vars['link_text'] = $page->messages[1526];

require MODULES_DIR . 'shared/category_tree.php';
