<?php

//module_display_zip_filters.php


if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

$zipSettings = geoAddon::getRegistry('zipsearch');
if (!$zipSettings && $zipSettings->enabled == 1) {
    //zipsearch addon not found. cannot show zip module
    return;
}

if (isset($_POST['clear_zip_filter'])) {
    //zip filters just cleared -- don't show anything
    $zip_filter = false;
    $zip_distance_filter = false;
} elseif (strlen(trim($_POST["submit_zip_filter"])) > 0) {
    //zip filters just submitted -- grab new data
    $zip_filter = $_POST['set_zip_filter'];
    $zip_distance_filter = $_POST['set_zip_filter_distance'];
} else {
    //grab "stale" zip filter data from cookies
    $zip_filter = $_COOKIE['zip_filter'];
    $zip_distance_filter = $_COOKIE['zip_distance_filter'];
}

$tpl_vars['local_zip_filter'] = $zip_filter;

if ($zipSettings->search_method == 'hierarchical') {
    $tpl_vars['input_size'] = 8;
} else {
    $tpl_vars['input_size'] = 5;
}

$distance_array = array(5,10,15,20,25,30,40,50,75,100,200,300,400,500);
$opts = array();
$i = 0;
foreach ($distance_array as $distance) {
    $opts[$i]['distance'] = $distance;
    if ($zip_distance_filter == $distance) {
        $opts[$i]['sel'] = true;
    }

    $i++;
}
$tpl_vars['opts'] = $opts;
$text = geoAddon::getText('geo_addons', 'zipsearch');
$tpl_vars['default_distance_text'] = ($zipSettings->units == 'M') ? $text['default_distance_mi'] : $text['default_distance_km'];

$view->setModuleTpl($show_module['module_replace_tag'], 'index')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
