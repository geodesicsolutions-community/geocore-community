<?php 
//addons/social_connect/admin.php
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
## ##    67d0e9c
## 
##################################

# Facebook Connect

//APP Top...

if (defined('IN_ADMIN')||defined('AJAX')) {
	//don't do this stuff in admin panel
	return;
}

$util = geoAddon::getUtil('social_connect');

//let the init do the work
$util->init();

//done with vars
unset ($util);
