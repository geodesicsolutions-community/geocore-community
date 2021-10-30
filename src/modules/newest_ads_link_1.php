<?php

//module_display_newest_link_1_week.php


if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}
if (is_array($page->site_category)) {
    $page->site_category = 0;
}

$tpl_vars = array (
    'href' => $page->configuration_data['classifieds_file_name'] . "?a=11&amp;b=" . $page->site_category . "&amp;c=65&amp;d=1",
    'class' => 'newest_last_week_link',
    'label' => $page->messages[1210]
);

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
