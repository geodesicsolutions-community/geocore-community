<?php 
//addons/subscription_pricing/app_top.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.4.3-65-gda5b666
## 
##################################

defined('GEO_BASE_DIR') or die('No Access.');

//at the top of each pageload, see if we need to force this user to buy a subscription, then do it if neccessary
$u = geoAddon::getUtil('subscription_pricing');
if($u) {
	$u->tryForceSubscriptionBuy();
}

 