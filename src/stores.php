<?php

//__file__.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
##
##################################

//something like:
//index.php?a=ap&addon=storefront&page=???
require 'app_top.common.php';
$gets = array();
foreach ($_GET as $k => $v) {
    $gets[] = "$k=$v";
}
header("Location: {$db->get_site_setting('classifieds_url')}?a=ap&addon=storefront&page=home&" . implode('&', $gets), true, 301);
