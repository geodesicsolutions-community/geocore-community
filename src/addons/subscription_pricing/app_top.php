<?php

//addons/subscription_pricing/app_top.php


defined('GEO_BASE_DIR') or die('No Access.');

//at the top of each pageload, see if we need to force this user to buy a subscription, then do it if neccessary
$u = geoAddon::getUtil('subscription_pricing');
if ($u) {
    $u->tryForceSubscriptionBuy();
}
