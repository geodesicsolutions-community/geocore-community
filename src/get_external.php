<?php
//get_external.php
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
## ##    6.0.7-2-gc953682
## 
##################################

/**
 * This file figures out the URL based on the specified file, then do a re-direct
 * to that file.
 */

require_once 'app_top.common.php';

$file = geoFile::cleanPath($_GET['file']);

if (!$file) {
	echo "Invalid File!";
} else {
	$url = geoTemplate::getUrl('', $file);
	//do a 301 redirect
	
	$baseUrl = geoTemplate::getBaseUrl();
	
	header('Location: '.$baseUrl.$url, true, 303);
}

include GEO_BASE_DIR . 'app_bottom.php';
