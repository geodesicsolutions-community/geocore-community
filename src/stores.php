<?php

//__file__.php


//something like:
//index.php?a=ap&addon=storefront&page=???
require 'app_top.common.php';
$gets = array();
foreach ($_GET as $k => $v) {
    $gets[] = "$k=$v";
}
header("Location: {$db->get_site_setting('classifieds_url')}?a=ap&addon=storefront&page=home&" . implode('&', $gets), true, 301);
