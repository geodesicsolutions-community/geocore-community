<?php

//Handy file that sets up common vars for you.
//just make sure the var(s) you want to set up are
//initialized, then include this file to have them
//set as a reference to the respective object.

require_once 'app_top.common.php';

//Possible variables: $db, $session, $product_configuration,
// $addon, $cron, $admin


if (isset($db)) {
    require_once CLASSES_DIR . PHP5_DIR . "DataAccess.class.php";
    $db = DataAccess :: getInstance();
}
if (isset($session)) {
    require_once CLASSES_DIR . PHP5_DIR . "products.php";
    $session = geoSession::getInstance();
}
if (isset($product_configuration)) {
    require_once CLASSES_DIR . PHP5_DIR . "products.php";
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
