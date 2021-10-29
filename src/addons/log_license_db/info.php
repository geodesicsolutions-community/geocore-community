<?php

//addons/log_license_db/info.php
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
## ##    6.0.7-2-gc953682
##
##################################

# Log license activities Addon


class addon_log_license_db_info
{
    //The following are required variables
    var $name = 'log_license_db';
    var $version = '1.0.0';
    var $title = 'License Activity Log';
    var $author = "Geodesic Solutions LLC.";
    var $icon_image = 'menu_license_act.gif';
    var $description = 'This addon logs whenever anything "of interest" happens dealing
with the license.  It is recommended to keep this addon installed for the first 
few weeks, to make sure everything is going smoothly.  It will let you know of any
license problems that might be slowing down your site.<br />
<br />
Like any logging addon, keeping this addon enabled will add some overhead to 
every page load.  Once you are sure that everything is going smoothly with the
license validation, you should disable this addon to increase site performance, 
especially on a high traffic site.';
    //used in referencing tags, and maybe other uses in the future.
    var $auth_tag = 'geo_addons';


    ##Optional Vars##
    //if these vars are included, they will be used.

    //URL's.  If any of these exist, they will be linked to where appropriate in the
    //admin page.  Note that you can link to your own site, or to a relative page.
    //Keep in mind, if using a relative link, the link will not work when
    //the addon is disabled.
    var $upgrade_url = 'http://geodesicsolutions.com'; //[ Check For Upgrades ] link
    var $author_url = 'http://geodesicsolutions.com'; //[ Author's Site ] link
    var $info_url = 'http://geodesicsolutions.com'; //[ More Info ] link

    /*Core events so far:
    filter_display_page
    email
    errorhandle
    */
    var $core_events = array (
    'errorhandle'
    );
}

class addon_log_license_db_tables
{
    var $license_log_table = '`geodesic_license_log`';
}

/* CHANGELOG
 *
 * v1.1.0 -- Geo 5.0.0
 *  - Changed pagination to match the new admin design
 *  - Oops we don't update the version number since this is a core addon that
 *    does not need update script to be run, so version kept at 1.0
*/
//leave trailing whitespace after comment, so Eclipse doesn't die
