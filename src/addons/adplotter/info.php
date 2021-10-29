<?php
//addons/adplotter/info.php
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
## ##    16.09.0-92-gefaf632
## 
##################################

# Adplotter Link Addon

class addon_adplotter_info{
	//The following are required variables
	var $name = 'adplotter';
	var $version = '1.4.0';
	var $core_version_minimum = '17.01.0';
	var $title = 'AdPlotter';
	var $author = "Geodesic Solutions LLC.";
	//var $icon_image = 'menu_anonymous.gif';
	//var $info_url = 'http://geodesicsolutions.com/component/content/article/55-miscellaneous/77-anonymous-listing.html?directory=64';
	var $description = 'Registers your site with the adplotter.com network and allows adplotter users to create listings';
	var $auth_tag = 'geo_addons';
	var $core_events = array('sell_success_email_content');
	var $tags = array('aff_id');
}

/*
 * CHANGELOG - Anonymous Listing
 *
 * v1.4.0 - REQUIRES 17.01.0
 * - Implemented new admin design
 *
 * v1.3.3 - Geo 16.01.0
 * - Improve display when region data is missing
 *
 * v1.3.2 - Geo 7.6.3
 *  - Switch to hotlinking images from remote adplotter server
 *
 * v1.3.1 - Geo 7.6.2
 *  - Add user's name to information sent to AdPlotter affiliate registration API
 *  - Add pingback to confirm for AdPlotter the success of an API listing creation
 *  - Upload images asynchronously from the main API, to improve reliability
 *  - Add new AdPlotter categories
 *
 * v1.3.0 - Geo 7.6.0
 *  - Added ability to pick a default user group specifically for API registrants
 *  - Changed text of Listing Success email
 *
 * v1.2.0 - Geo 7.5.3
 *  - Added addon tag to allow easily writing aff code into templates
 *
 * v1.1.0 - Geo 7.5.2
 *  - Added affiliate links for sell success emails
 *  - Fixed "no image" image not appearing for listings without images
 *
 * v1.0.0 - Geo 7.5.0
 *  - Addon created.
*/ 
//leave whitespace at the end of this, or Eclipse dies
