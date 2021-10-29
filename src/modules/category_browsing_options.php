<?php

//module_display_browsing_options.php
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

if (!$page->site_category) {
    //this only works on pages with a category!
    return false;
}

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
    //browsing disabled, do not show module contents
    return;
}

$current_choice = $_REQUEST['o'];

$uri_end = $_SERVER['QUERY_STRING'];
if (strlen($uri_end) == 0 && count($_GET) > 0) {
    //add all get params to end
    $parts = array();
    foreach ($_GET as $key => $val) {
        $parts[] = $key . '=' . $val;
    }
    $uri_end = implode('&', $parts);
}
if (strlen($uri_end) > 0) {
    $uri_end = '?' . $uri_end;
}
$uri = geoFilter::getBaseHref() . $this->get_site_setting('classifieds_file_name') . $uri_end;

//Does NOT work with SEO: need to use above method instead...
//$uri = $_SERVER["REQUEST_URI"];

//remove any pre-existing options from current uri
$uri = preg_replace("/&o=[0-9]*/", "", $uri);
//start at page 1 when a new option is selected
$uri = preg_replace("/&page=[0-9]*/", "", $uri);
//replace & by &amp; for w3c compliance
$uri = str_replace('&', '&amp;', $uri);

/*
 * TO ADD A NEW OPTION:
 * -Add a setting/textmessage to this module
 * -Add the setting to the admin control
 * -Add a case to the switch statement in browse_ads.php using the value of 'o' from here
 */


$tpl_vars = array();

$tpl_vars['headerText'] =  $page->messages[500131];
$tpl_vars['delimeter'] =  $page->messages[500132];
$tpl_vars['uri'] =  $uri;


//map QS parameter to settings
//only set the options to be checked -- we foreach this later
$map = array(
    0 => array( 'setting' => 'cat_browse_all_listings', 'text' => 500138),
    1 => array( 'setting' => 'cat_browse_end_today', 'text' => 500133),
    6 => array( 'setting' => 'cat_browse_has_pics', 'text' => 500139),
);
if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
    $map[7] = array( 'setting' => 'cat_browse_class_only', 'text' => 500140);
    $map[8] = array( 'setting' => 'cat_browse_auc_only', 'text' => 500141);
}
if (geoMaster::is('auctions')) {
    $map[2] = array( 'setting' => 'cat_browse_buy_now', 'text' => 500134);
    $map[3] = array( 'setting' => 'cat_browse_buy_now_only', 'text' => 500135);
    $map[4] = array( 'setting' => 'cat_browse_auc_bids', 'text' => 500136);
    $map[5] = array( 'setting' => 'cat_browse_auc_no_bids', 'text' => 500137);
}
$option_data = array();
foreach ($map as $key => $value) {
    if ($this->get_site_setting($value['setting'])) {
        $option_data[] = array(
            'selected' => (($current_choice == $key) ? true : false),
            'text' => $page->messages[$value['text']],
            'param' => $key
        );
    }
}
$tpl_vars['option_data'] =  $option_data;

//this can be either a list of text links or a javascripted dropdown box
//inputs are the same for each, but they have different .tpl files
//yay for reusable code! :)
$templateToUse = $this->get_site_setting('cat_browse_opts_as_ddl') ? 'dropdown.tpl' : 'links.tpl';

$view->setModuleTpl($show_module['module_replace_tag'], $templateToUse)//'display_main_category_navigation_1')
    ->setModuleVar($show_module['module_replace_tag'], $tpl_vars);
