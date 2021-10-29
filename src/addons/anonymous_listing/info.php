<?php
//addons/anonymous_listing/info.php
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
## ##    16.09.0-93-g6a003de
## 
##################################

# Anonymous Listing Addon

class addon_anonymous_listing_info{
	//The following are required variables
	var $name = 'anonymous_listing';
	var $version = '1.7.0';
	var $core_version_minimum = '17.01.0';
	var $title = 'Anonymous Listing';
	var $author = "Geodesic Solutions LLC.";
	var $icon_image = 'menu_anonymous.gif';
	var $info_url = 'http://geodesicsolutions.com/component/content/article/55-miscellaneous/77-anonymous-listing.html?directory=64';
	var $description = 'This addon enables the posting of anonymous listings, to be used on 4.0.0 or higher.';
	var $auth_tag = 'geo_addons';
	var $core_events = array(
		'Browse_ads_display_browse_result_addHeader',
		'Browse_ads_display_browse_result_addRow'
	);
	public $pages = array ('anon_pass');
	public $pages_info = array(
		'anon_pass' => array ('main_page' => 'cart_page.tpl', 'title' => 'Listing Process - Anon Password Step'),
	);

}

/*
 * CHANGELOG - Anonymous Listing
 *
 * v1.7.0 - REQUIRES 17.01.0
 * - Implemented new admin design
 *
 * v1.6.2 - Geo 7.5.3
 *  - Update default text and template to support condensed/linked EULA
 *
 * v1.6.1 - Geo 7.1.0
 *  - Compatibility changes for 7.1 which are backwards compatible with 7.0
 *  
 * v1.6.0 - Requires Geo 7.0.3
 *  - Added the ability to require anonymous listers to agree to the site EULA during listing placement.
 *
 * v1.5.1 - Geo 5.0.3
 *  - Added page for anon pass step in listing process, requires changes in Geo 5.0.3
 *  
 * v1.5.0 - Geo 5.0.0
 *  - Added text needed for new design
 *  
 * v1.4.5 - Geo 4.0.9
 *  - fixed anonymous edit column showing when it shouldn't have 
 * 
 * v1.4.4 - Geo 4.0.7
 *  - Fix applied for addon license checks
 * 
 * v1.4.3 - Geo 4.0.6
 *  - Added license checks
 * 
 * v1.4.2 - Geo 4.0.0RC11
 *  - new text added for steps displayed in cart
 * 
 * v1.4.1 - Geo 4.0.0RC9
 *  - fixes for anonymous "user" displaying in listing data
 *  - enable admin to specify username shown for anonymous user
 * 
 * v1.4.0 - Geo 4.0.0RC8
 *  - added Anonymous "user" so that listings appear in admin and priceplan may be chosen
 * 
 * v1.3.0 - Geo 4.0.0RC7
 *  - enable logging of IP addresses for anonymous posts
 * 
 * v1.2.0 - Geo 4.0.0RC5
 *  - add text for edit column header to db
 * 
 * v1.1.0 - Geo 4.0.0b3 
 *  - workaround for a beta bug involving missing text
 * 
 * v1.0.0 - Geo 4.0.0b3
 *  - initial creation
 */
//leave whitespace at the end of this, or Eclipse dies
