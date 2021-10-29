<?php
//addons/price_drop_auctions/info.php
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
## ##    16.09.0-99-gba74dac
## 
##################################

class addon_price_drop_auctions_info
{
	public $name = 'price_drop_auctions';
	public $title = 'Price Drop Auctions';
	public $version = '1.1.0';
	public $core_version_minimum = '17.01.0';
	public $description = 'Allows for the creation of "Price Drop" Buy Now Only auctions, which automatically lower their prices over time if not purchased';
	public $author = 'Geodesic Solutions LLC.';
	public $icon_image = '';
	public $auth_tag = 'geo_addons';
	public $author_url = 'http://geodesicsolutions.com';
	
	public $core_events = array (
		'listing_placement_moreDetailsPricing_append',
		'listing_placement_moreDetailsLocation_append_checkVars',
		'listing_placement_processStatusChange',
		'notify_geoListing_remove'
	);
}

/**
 * Changelog
 * 
 * 1.1.0 - REQUIRES 17.01.0
 *  - Added static drop amounts
 *  - Implemented new admin design
 * 
 * 1.0.0 - Geo 16.09.0 
 *  - Addon Created
 * 
 */

