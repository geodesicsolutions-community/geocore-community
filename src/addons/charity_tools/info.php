<?php

//addons/charity_tools/info.php
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
## ##    16.09.0-105-ga458f5f
##
##################################

# Charity Tools
class addon_charity_tools_info
{
    public $name = 'charity_tools';
    public $title = 'Charity Tools';
    public $version = '1.4.0';
    public $core_version_minimum = '17.01.0';
    public $description = 'Tools to assist in running a charity-based site';
    public $author = 'Geodesic Solutions LLC.';
    public $icon_image = '';
    public $auth_tag = 'geo_addons';
    public $author_url = 'http://geodesicsolutions.com';

    public $core_events = array(
        'Admin_site_display_user_data',
        'Admin_user_management_update_users_view',
        'add_listing_icons',
        'use_listing_icons'
    );
}

/**
 * Charity Tools Changelog
 *
 * 1.4.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 1.3.5 - Geo 16.01.0
 *  - Fixed order item not reporting price correctly in some cases
 *
 * 1.3.4 - Geo 7.5.3
 *  - Fixed being unable to add Charitable Badges via an Upgrade
 *
 * 1.3.3 - Geo 7.4.5
 *  - Fixed typo in a SQL query that could prevent icons from appearing where they should
 *
 * 1.3.2 - Geo 7.4.2
 *  - Fixed rare Fatal Error in admin
 *
 * 1.3.1 - Geo 7.4.1
 *  - Fixed geo-linked badges not appearing as buyable choices when they should
 *
 * 1.3.0 - Geo 7.4.0
 *  - Fixed charitable badges not appearing in listing placement
 *  - Added several tooltips to allow explaining the purpose of badges in various places
 *  - Added a global override for the Charitable badge that can be used to obscure the identity of the selected charity in listing display
 *
 * 1.2.1 - Geo 7.3.2
 *  - Get rid of old calendar icons for date input
 *
 * 1.2.0 - Geo 7.3.0
 *  - Added ability to use zipcodes instead of regions for charitable badge locations.
 *
 * 1.1.0 - Geo 7.3b3
 *  - Changes to use add_footer_html
 *
 * 1.0.3 - Geo 7.2.6
 *  - Fixed a bug that could cause Charitable Badges to not appear when they should
 *
 * 1.0.2 - Geo 7.2.5
 *  - Corrected some conflicts with the Attention Getters addon
 *
 * 1.0.1 - Geo 7.2.2
 *  - Corrected an issue that could make Charitable Badge selections not appear
 *
 * 1.0.0 - Geo 7.2.0
 *  - Addon Created
 *
 */
