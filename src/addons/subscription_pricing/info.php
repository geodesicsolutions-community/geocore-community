<?php 
//addons/subscription_pricing/info.php
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
##    16.09.0-96-gf3bd8a1
## 
##################################

# Subscription Pricing
class addon_subscription_pricing_info
{
	public $name = 'subscription_pricing';
	public $title = 'Subscription Pricing';
	public $version = '1.2.0';
	public $core_version_minimum = '17.01.0';
	public $description = 'Enables the use of subscription-based pricing and related features.';
	public $author = 'Geodesic Solutions LLC.';
	public $auth_tag = 'geo_addons';
	public $author_url = 'http://geodesicsolutions.com';
}

/**
 * Subscription Pricing Changelog
 * 
 * v1.2.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 * 
 * v1.1.2 - Geo 16.01.0
 * - Fixed order item not reporting price correctly in some cases
 * 
 * v1.1.1 - Geo 7.5.2
 *  - Fix force-subscription switch to not redirect AJAX calls
 * 
 * v1.1.0 - Geo 7.5.0
 *  - Added switch to enable forcing users through subscription purchase
 * 
 * 1.0.0 - Geo 7.0.0
 *  - Addon Created
 * 
 */

