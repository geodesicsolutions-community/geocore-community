<?php

//addons/social_connect/admin.php

# Facebook Connect

//APP Top...

if (defined('IN_ADMIN') || defined('AJAX')) {
    //don't do this stuff in admin panel
    return;
}

$util = geoAddon::getUtil('social_connect');

//let the init do the work
$util->init();

//done with vars
unset($util);
