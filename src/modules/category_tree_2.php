<?php

//category_tree_2.php


$tpl_vars = array();
$tpl_vars['css_append'] = '_2';
$tpl_vars['link_label'] = $page->messages[1523];
$tpl_vars['link_text'] = $page->messages[1524];

require MODULES_DIR . 'shared/category_tree.php';
