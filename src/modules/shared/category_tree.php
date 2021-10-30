<?php

//category_tree.php


//Shared stuff that happens in all 3 category tree modules.

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

$tpl_vars['base_url'] = $page->configuration_data['classifieds_file_name'] . "?a=5";
if ($browse_type && is_numeric($browse_type)) {
    $tpl_vars['base_url'] .= "&amp;c=" . $browse_type;
}
if (!$page->site_category && !$view->category_tree_pre) {
    //$page->site_category = 12; //un-comment this for testing
    return false; //comment this out for testing
}

$category_tree = $page->category_tree_array = geoCategory::getTree($page->site_category);

if ($category_tree) {
    if (is_array($page->category_tree_array)) {
        $categories = array();
        $c = 0; //categories index
        for ($i = 0; $i < count($page->category_tree_array); $i++) {
            $categories[$c]['label'] = $page->category_tree_array[$i]["category_name"];
            //display all the categories
            if ($i < count($page->category_tree_array) - 1) {
                //don't do the link if $i is 0
                $categories[$c]['id'] = $page->category_tree_array[$i]["category_id"];
            }
            $c++;
        }
        $tpl_vars['categories'] = $categories;
    } else {
        $tpl_vars['fallback_tree_display'] = $category_tree;
    }
}

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
