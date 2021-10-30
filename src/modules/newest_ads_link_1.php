<?php

//module_display_newest_link_1_week.php

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}
if (is_array($page->site_category)) {
    $page->site_category = 0;
}

//pass the number of days in if set
if ((ctype_digit($show_module['days_to_display'])) && ($show_module['days_to_display'] != 0)) {
    $pass_in_days_to_display = "&days=" . $show_module['days_to_display'];
} else {
    $pass_in_days_to_display = "";
}

$tpl_vars = array (
    'href' => $page->configuration_data['classifieds_file_name'] . "?a=11&amp;b=" . $page->site_category . "&amp;c=65&amp;d=1" . $pass_in_days_to_display,
    'class' => 'newest_last_week_link',
    'label' => $page->messages[1210]
);

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
