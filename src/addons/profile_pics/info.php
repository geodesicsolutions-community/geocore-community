<?php

//addons/profile_pics/info.php
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

class addon_profile_pics_info
{
    public $name = 'profile_pics';
    public $title = 'Profile Pictures';
    public $version = '1.0.0';
    public $core_version_minimum = '17.01.0';
    public $description = 'Allows the upload and display of a personal image for each user';
    public $author = 'Geodesic Solutions LLC.';
    public $icon_image = '';
    public $auth_tag = 'geo_addons';
    public $author_url = 'http://geodesicsolutions.com';

    public $core_events = array (
        'User_management_information_display_user_data',
        'Admin_site_display_user_data'
    );

    public $tags = array('show_pic');
    public $listing_tags = array('show_pic');
}

/**
 * Changelog
 *
 * 1.0.0 - Geo 17.01.0
 *  - Addon created
 *
 *
 */
