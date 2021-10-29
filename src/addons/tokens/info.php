<?php 
//addons/tokens/info.php
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
## ##    16.09.0-96-gf3bd8a1
## 
##################################

# Tokens
class addon_tokens_info
{
	public $name = 'tokens';
	public $title = 'Tokens';
	public $version = '1.1.0';
	public $core_version_minimum = '17.01.0';
	public $description = 'Allow users to use a token that covers the main cost of a listing (NOT cover the cost of listing extras).';
	public $author = 'Geodesic Solutions LLC.';
	public $auth_tag = 'geo_addons';
	public $author_url = 'http://geodesicsolutions.com';
	
	public $pages = array (
		'chooseTokens',
	);
	public $pages_info = array (
		'chooseTokens' => array ('main_page' => 'cart_page.tpl', 'title' => 'Choose Tokens'),
	);
	
	public $core_events = array (
		'Admin_site_display_user_data',
		'Admin_user_management_update_users_view',
		'notify_user_remove',
		'User_management_information_display_user_data',
		'Admin_Group_Management_edit_group_display',
		'Admin_Group_Management_edit_group_update',
		'registration_add_field_update',
	);
	
	
	const TOKENS_PRICE_TABLE = '`geodesic_addon_tokens_prices`';
}

/**
 * Tokens Changelog
 * 
 * 1.1.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 1.0.7 - Geo 16.01.0
 * - Fixed order items not reporting price correctly in some cases
 * 
 * 1.0.6 - Geo 7.4.6
 *  - Fixed a fatal error in Tokens Purchase order item interface
 * 
 * 1.0.5 - Geo 7.3.2
 * - Get rid of old calendar icons for date input
 * 
 * 1.0.4 - Geo 7.1.0
 * - Bug 646 - account for pending tokens when getting count of tokens available
 * 
 * 1.0.3 - Geo 7.0.1
 * - Added new parameter to getDisplayDetails() in order items
 * 
 * 1.0.2 - Geo 6.0.3
 *  - Changed settings to not show "purchase tokens" configuration on cat specific
 *    plan settings since it would never use cat specific settings for purchasing tokens
 * 
 * 1.0.1 - Geo 6.0.2
 *  - Fixed issue where it wouldn't show token selections at first, bug #241
 *  
 * 1.0.0 - Geo 6.0.0 
 *  - Addon Created
 * 
 */

