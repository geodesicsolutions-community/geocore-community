<?php

//addons/exporter/info.php

/**
 * Exporter Addon
 *
 * Allows admins to export listings, and possibly (at a later time) users, and
 * other data.
 */

class addon_exporter_info
{
    public $name = 'exporter';
    public $version = '3.3.3';
    public $core_version_minimum = '17.01.0';
    public $title = 'Listing Exporter';
    public $author = "Geodesic Solutions LLC.";
    public $icon_image = 'menu_listing_export.gif';
    public $description = "Allows admins to export listings, and possibly (at a later time) users, and other data.";
    public $auth_tag = 'geo_addons';

    const SETTINGS_TABLE = '`geodesic_addon_exporter_settings`';
}


/**
 * Listing Exporter Changelog
 *
 * v3.2.3 - Geo 18.02.0
 *  - Ensure image data is always present in the export feed, when called for
 *
 * v3.3.2 - Geo 17.05.0
 *  - Fixed date entries being improperly linked together
 *
 * v3.3.1 - Geo 17.03.0
 *  - Fixed broken Export button
 *
 * v3.3.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * v3.2.1 - Geo 16.05.0
 *  - Added Quantity/Remaining Quantity as exportable data fields
 *
 * v3.2.0 - Geo 7.4.7
 *  - Fixed a query error that would return the incorrect listings when selecting multiple categories.
 *
 * v3.1.9 - Geo 7.4.4
 *  - Remove a few old references to geoUtil js in favor of newer gjUtil
 *
 * v3.1.8 - Geo 7.4.3
 *  - Fixed choosing multiple categories results in no data being exported
 *
 * v3.1.7 - Geo 7.4.0
 *  - Changed how categories work for new version
 *
 * v3.1.6 - Geo 7.3.2
 *  - Get rid of old calendar icons for date input
 *
 * v3.1.5 - Geo 7.2.3
 *  - Fixed broken buttons on save/load settings form
 *  - Added Duration as an exportable field
 *
 * v3.1.4 - Geo 7.0.4
 *  - Added High Bidder ID to list of exportable fields
 *  - Fixed generating the feed, was overly complex and was not working
 *
 * v3.1.3 - Geo 7.0.0
 *  - Changes for 7.0 license compatibility
 *  - Changes to recognize new mapping_location field
 *
 * v3.1.2 - Geo 6.0.2
 *  - Fixed a bug that caused no results to return if no date ranges were entered
 *
 * v3.1.1 - Geo 6.0.0
 *  - Changes for Smarty 3.0
 *  - Changes for how listing feed class works now
 *  - Use new geoTabs JS to let it do work of changing tabs
 *  - Make it not show data not actually requested (id, title, description, etc.)
 *  - Changes for leased license
 *  - Fix to use ob_end_clean() to prevent headers from being sent early when exporting
 *
 * v3.1.0 - Geo 5.2.0
 *  - Cleaned up settings HTML some
 *  - Added load/save ability for export settings
 *  - Added ability to save exports to the server
 *
 * v3.0.0 - Geo 5.1.2
 *  - First version using changelog block
 *  - Re-wrote interface to remove dependence on YUI.
 *  - Re-wrote entire back-end to use geoListingFeed class and Smarty template files
 *  - Requires at least 5.1.2 since it uses stuff only available in that version
 *  - Removed (or rather, didn't re-implement) Save/Restore functionality, plan
 *    to re-implement much more rhobust system in future release.
 */
