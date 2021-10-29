<?php

//get_common_vars.php
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

//Handy file that sets up common vars for you.
//just make sure the var(s) you want to set up are
//initialized, then include this file to have them
//set as a reference to the respective object.

require_once 'app_top.common.php';

//Possible variables: $db, $session, $product_configuration,
// $addon, $cron, $admin


if (isset($db)) {
    require_once CLASSES_DIR . PHP5_DIR . "DataAccess.class" . ENCODE_EXT . ".php";
    $db = DataAccess :: getInstance();
}
if (isset($session)) {
    require_once CLASSES_DIR . PHP5_DIR . "products" . ENCODE_EXT . ".php";
    $session = geoSession::getInstance();
}
if (isset($product_configuration)) {
    require_once CLASSES_DIR . PHP5_DIR . "products" . ENCODE_EXT . ".php";
    $product_configuration = geoPC::getInstance();
}
if (isset($addon)) {
    require_once CLASSES_DIR . PHP5_DIR . 'Addon.class.php';
    $addon = geoAddon::getInstance();
}
if (isset($cron)) {
    require_once CLASSES_DIR . PHP5_DIR . 'Cron.class.php';
    $cron = geoCron::getInstance();
}
if (defined('IN_ADMIN') &&  isset($admin)) {
    require_once ADMIN_DIR . PHP5_DIR . 'Admin.class.php';
    $admin = geoAdmin::getInstance();
}
