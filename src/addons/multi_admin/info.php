<?php

//addons/multi_admin/info.php
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
## ##    16.09.0-106-ge989d1f
##
##################################

# multi_admin Addon

class addon_multi_admin_info
{
    public $name = 'multi_admin';
    public $version = '2.3.0';
    public $core_version_minimum = '17.01.0';
    public $title = 'Multi-Admin';
    public $author = "Geodesic Solutions LLC.";
    public $icon_image = 'menu_multi_admin.gif';
    public $description = 'Adds the ability to allow multiple users to access certain admin pages, and gives ability to specify which users can access what.<br /><br /> If this addon has not yet been added to the user manual, you can find instructions
included with the addon package in the docs/ folder</a>.';
    public $auth_tag = 'geo_addons';
    public $upgrade_url = 'http://geodesicsolutions.com/component/content/article/54-access-security/61-multi-admin.html?directory=64'; //[ Check For Upgrades ] link
    public $author_url = 'http://geodesicsolutions.com'; //[ Author's Site ] link
    public $info_url = '../addons/multi_admin/docs/help.html'; //[ More Info ] link
    public $core_events = array (
        'auth_admin_login',
        'auth_admin_display_page',
        'auth_admin_update_page',
        'auth_admin_user_login',
        'auth_listing_edit',
        'auth_listing_delete'
    );
}

/**
 * Bridge Changelog
 *
 * 2.3.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 2.2.3 - Geo 7.0.0
 *  - Compatibility changes for 7.0 licensing
 *
 * 2.2.2 - Geo 6.0.0
 *  - Changes for Smarty 3.0
 *  - Changes for leased license
 *
 * 2.2.1 - Geo 5.1.4
 *  - Fixed issue where "shortcut" pages were not able to be enabled, such as
 *    the "edit text" or "edit pages" for certain addons.
 *
 * 2.2.0 - Geo 5.0.0
 *  - Design changes for new admin design in 5.0.0
 *
 * 2.1.0 - Geo 4.1.0
 *  - Added integration for new feature where the admin can log in as any user
 *    by logging using the admin pass, integrated it to add a "special permission"
 *    to also allow/deny multi-admin users to have that same ability.  Note that
 *    this ability is Enterprise only.
 *
 * 2.0.3 - Geo 4.0.5
 *  - Change display of permissions to account for when category has no title
 *    (This affects the "top level" pages such as admin map, release notes, and home)
 *
 * 2.0.2 - Geo 4.0.4
 *  - First version using changelog block for Bridge addon
 *  - Changed references to old geoAddon->triggerCoreEvent() to use geoAddon::triggerDisplay()
 *
 */
