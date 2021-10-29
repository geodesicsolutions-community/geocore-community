<?php

//addons/zipsearch/info.php
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

# Zip/Postal Code Search
class addon_zipsearch_info
{
    public $name = 'zipsearch';
    public $title = 'Zip/Postal Code Search';
    public $version = '2.2.0';
    public $core_version_minimum = '17.01.0';
    public $icon_image = 'menu_zip_search.gif';

    public $core_events = array(
        'Search_classifieds_generate_query',
        'Search_classifieds_BuildResults_addHeader',
        'Search_classifieds_BuildResults_addRow',
        'show_listing_alerts_table_headers',
        'show_listing_alerts_table_body',
        'display_add_listing_alert_field',
        'update_add_listing_alert_field',
        'delete_listing_alert',
        'check_listing_alert',
        'show_listing_alert_filter_data'
    );

    public $description = 'This addon inserts zip/postal code data, which can then be used to allow searching "by proximity" of a given zip or postal code.
<br /><br />
<strong>Note:</strong>  Un-install this addon will remove the zip data from the system.  Installing the addon will add it back.';
    public $author = 'Geodesic Solutions LLC.';
    public $auth_tag = 'geo_addons';
    public $author_url = 'http://geodesicsolutions.com';
    public $info_url = 'http://geodesicsolutions.com/component/content/article/50-browsing-enhancements/62-zip-postal-code.html?directory=64';
}

/**
 * Zip/Postal Code Search Changelog
 *
 * 2.2.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 2.1.4 - Geo 16.01.0
 *  - Text change to support new design
 *
 * 2.1.3 - Geo 7.4.5
 *  - New setting to allow things to work right in certain foreign countries.
 *
 * 2.1.2 - Geo 7.4.0
 *  - Minor tweak to make things a bit easier in Sweden
 *
 * 2.1.1 - Geo 7.3.1
 *  - Fixed issue with showing wrong distance on search results, when search distance set to 0
 *
 * 2.1.0 - Geo 7.3beta3
 *  - Add Listing Alert integration
 *
 * 2.0.6 - Geo 7.2.4
 *  - Add label to zip distance on search results' gallery/list views
 *
 * 2.0.5 - Geo 7.1.1
 *  - Speed improvements for import process, when importing zip data to INNODB
 *    database type.
 *
 * 2.0.4 - Geo 7.0.0
 *  - Compatibility changes for 7.0 licensing
 *
 * 2.0.3 - Geo 6.0.2
 *  - Fixed SQL error when only zip is specified and not distance
 *
 * 2.0.2 - Geo 6.0.0
 *  - Changed to not use the now deprecated DataAccess->addBrowsingWhereClause()
 *  - Moved search stuff out of base code and into addon code
 *  - Fixed problem with detecting zip distance when narrowing search by zip
 *  - Made the UK-like postcode a little smarter in how it strips a code down
 *    to just the outward part of the code (part before the space).
 *  - Changes for leased license
 *  - Fixed a bug in install/upgrade that caused settings to not be set.
 *  - Restored the ability of the search to fall back on filter settings if primary fields not set
 *
 * 2.0.1 - Geo 5.2.1
 *  - Fixed searches to work across the north/south pole when distance causes it
 *    to pass over a pole.
 *  - Fixed issue with it enabled but no filter active, where it caused empty
 *    result set.
 *
 * 2.0.0 - Geo 5.2.0
 *  - Moved most of the zipsearch calculation code out of the core software's files
 *  - Added ability and addon settings to use either miles or KM for distances
 *  - Improved the math to be more accurate when not standing on the Equator.
 *  - Since code was moved from core files to the addon, done in 5.2, it now requires Geo 5.2.0
 *
 * 1.2.1 - Geo 5.1.3
 *  - Change timeout to last 5 minutes when running import step, to avoid timeout issues.
 *
 * 1.2.0 - Geo 5.1.2
 *  - Able to sort import types on list of imports
 *  - Able to disable check-boxes for imports if needed
 *  - Added CSV import for geopostcodes.com and geodatas.net
 *  - Duplicate entry checks added for non-sql imports
 *
 * 1.1.0 - Geo 5.1.2
 *  - Changed how it works, so that can easily import from multiple data sources
 *  - Changed data for US to only include single entry for each zip code
 *  - DB now only records zip code, latitude, and longitude, no other data
 *  - Added: Australia, UK, Canada, Germany postal data
 *
 * 1.0.0 - Geo 4.1.2
 *  - Addon Created
 *
 */
