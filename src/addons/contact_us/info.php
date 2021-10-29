<?php

//addons/contact_us/info.php
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

# Contact Us Form
class addon_contact_us_info
{
    public $name = 'contact_us';
    public $title = 'Contact Us Form';
    public $version = '1.3.0';
    public $core_version_minimum = '17.01.0';
    public $description = 'Allows adding a contact us form on any page, that sends an e-mail to the specified e-mail address in the admin panel.';
    public $author = 'Geodesic Solutions LLC.';
    public $auth_tag = 'geo_addons';
    public $author_url = 'http://geodesicsolutions.com';

    public $pages = array (
        'main'
    );

    public $pages_info = array (
        'main' => array ('main_page' => 'basic_page.tpl', 'title' => 'Contact Us'),
    );

    public $tags = array ('contact_form');
}

/**
 * Contact Us Form Changelog
 *
 * 1.3.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 1.2.0 - Geo 16.01.0
 *  - Modified default text and templates to support new design
 *
 * 1.1.0 - Geo 7.1.0
 *  - Added ability to pass in "reportAbuse" listing ID to report abuse for a
 *    particular listing, so that it pre-fills the subject and contents
 *  - Made it only show the contact template if it is ajax, to allow using lightbox
 *  - Changes to text to use "text sections" added in 7.1
 *
 * 1.0.4 - Geo 6.0.0
 *  - Fixed "department" label missing in email
 *  - Fixed tags to work in 6.0
 *
 * 1.0.3 - Geo 5.2.3
 *  - Corrected a bug that caused text to not display on the "success" page
 *
 * 1.0.2 - Geo 5.2.2
 *  - Changes needed for Smarty 3.0
 *
 * 1.0.1 - Geo 5.1.3
 *  - Changed e-mail to not use <pre> for message.
 *
 * 1.0.0 - Geo 5.0.0
 *  - Addon Created
 *
 */
