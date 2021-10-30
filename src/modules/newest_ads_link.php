<?php

//module_display_newest_link.php


if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

if (is_array($page->site_category)) {
    $page->site_category = 0;
}
$tpl_vars = array (
    'href' => $page->configuration_data['classifieds_file_name'] . "?a=11&amp;b=" . $page->site_category . "&amp;c=65&amp;d=4",
    'class' => 'newest_ad_link_text',
    'label' => $page->messages[1060]
);

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
