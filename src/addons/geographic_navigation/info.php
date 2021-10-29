<?php

//addons/geographic_navigation/info.php
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
##    17.10.0-5-g8bc0dc0
##
##################################

class addon_geographic_navigation_info
{
    #### Required Vars ####

    public $name = 'geographic_navigation';
    public $version = '5.3.1';
    public $core_version_minimum = '17.01.0';
    public $title = 'Geographic Navigation';
    public $author = "Geodesic Solutions LLC.";
    public $description = 'This is a Geographic Navigation addon, it uses the 
	geographic regions and "filters" the listings displayed on the site by the 
	selected region or sub-region.';

    public $auth_tag = 'geo_addons';
    public $icon_image = 'menu_geo_navigation.gif';
    public $author_url = 'http://geodesicsolutions.com';
    public $info_url = 'http://geodesicsolutions.com/component/content/article/50-browsing-enhancements/76-geographic-navigation.html?directory=64';

    public $tags = array (
        'navigation',
        'navigation_top',
        'change_region_link',
        'breadcrumb',
        'listing_regions',
        'current_region',
        'insert_head',
    );

    public $listing_tags = array (
        'listing_regions',
        );
    public $core_events = array (
        'module_search_box_add_search_fields',
        'module_title_prepend_text',
        'notify_ListingFeed_generateSql',
        'notify_modules_preload',
        'notify_Display_ad_display_classified_after_vars_set',
        'show_listing_alerts_table_headers',
        'show_listing_alerts_table_body',
        'display_add_listing_alert_field',
        'update_add_listing_alert_field',
        'delete_listing_alert',
        'check_listing_alert',
        'show_listing_alert_filter_data'
    );

    //Used internally
    const COLUMN_NAME = 'addon_geographic_navigation_used';

    const REGION_TABLE = "`geodesic_addon_geographic_regions`";
    const LISTING_TABLE = "`geodesic_addon_geographic_listings`";
    const USER_TABLE = "`geodesic_addon_geographic_users`";
}

/**
 * Geographic Navigation Addon Changelog
 *
 * v5.3.1 - Geo 11.12.0
 *  - Fixed breadcrumb breaking expando-box in mobile view
 *
 * v5.3.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * v5.2.5 - Geo 16.01.1
 *  - Cleaned up cookie handling across subdomains
 *
 * v5.2.4 - Geo 7.5.2
 *  - Fixed Change Region selector box returning to blank page when triggered from inside some addon pages
 *
 * v5.2.3 - Geo 7.4.3
 *  - Added "Clear Selection" button to main navigation
 *
 * v5.2.2 - Geo 7.4.1
 *  - Restored absolutized "floating" functionality to change_region_link's js nav box
 *
 * v5.2.1 - Geo 7.4.0
 *  - Add new setting to force sub-domain set for listings using 301 redirect
 *  - Refactored JS to use jQuery instead of Prototype
 *
 * v5.2.0 - Geo 7.3.5
 *  - Fixed blank screen when selecting a region from on another addon's addon page
 *  - Not a feature release, but 5.1.10 confuses people :P
 *
 * v5.1.9 - Geo 7.3.0
 *  - Adjusted CSS for new fluid layout in 7.3
 *  - Bumping version to have CSS re-applied for RC updates
 *
 * v5.1.8 - Geo 7.3.0
 *  - Changes to add noindex when on page with region=0 to keep search engines from
 *    indexing the page which can lead to a lot of duplicate content
 *  - Add Listing Alert integration
 *  - Entering a region from a "secondary" browsing page (i.e. "page 2" and beyond) will now
 *    reset the browsing to page 1 (since page 2+ might not exist with the filter in place)
 *  - Fixed possible redirect loop in IP locator
 *
 * v5.1.7 - Geo 7.2.5
 *  - Fixed the re-direct from "old region location" to use proper methods to exit
 *    gracefully by calling app_bottom.php first.
 *  - Only allow subdomains work if the region is enabled
 *
 * v5.1.6 - Geo 7.2.3
 *  - Fixed issue where it tried to correct region for showing a listing even when
 *    it was just previewing that listing.
 *
 * v5.1.5 - Geo 7.2.2
 *  - Trim the api key entered when saving settings
 *  - Use geoPC::urlGetContents() instead of file_get_contents for better compatibility
 *
 * v5.1.4 - Geo 7.2.0
 *  - Improvements for multiple "top" levels with single regions
 *  - Fix for some social scrapers being infinitely redirected
 *
 * v5.1.3 - Geo 7.2beta
 *  - Changes to default navigation CSS so that it scrolls when the list is long enough
 *  - Changes to "change location" so that it adjusts the search URL to select different location
 *
 * v5.1.2 - Geo 7.1.3
 *  - Fixed problem with preserving "array" values when using "change location"
 *    ajax, see bug 790
 *
 * v5.1.1 - Geo 7.1.1
 *  - Prevented an infinite redirect loop
 *
 * v5.1.0 - Requires Geo 7.1.0
 *  - Made the listing_regions tag work as listing tag
 *  - Changed all tags to use new way of including internal template
 *  - Added experimental feature to automatically assign region based on visitor's IP
 *  - Hid "use legacy URLs" setting for non-pre-7.0.0 sites that don't have the legacy tables
 *
 * v5.0.3 - Geo 7.0.4
 *  - Fixed w3c validation problem in template, was using <[CDATA[ instead
 *    of <![CDATA[ in one of the templates.
 *  - Fixed problem with auto-set subdomain values, it wasn't setting the parent region
 *    as part of the subdomain
 *
 * v5.0.2 - Geo 7.0.2
 *  - Added ability to show sibling regions of the current region when at the lowest level of navigation (instead of the "there are no subregions here" message)
 *  - Fixed navigation_top to include the CSS needed in the head automatically.
 *  - Fixed user filter returning broken SQL
 *  - Improved handling of navigation levels with only a single region
 *
 * v5.0.1 - Geo 7.0.1
 *  - Removed "skip if single" setting -- replaced with code to add a CSS class for optional hiding of single regions
 *
 * v5.0.0 - Requires Geo 7.0.0
 *  - Added back the "across the column" option as there was a client that actually
 *    used that option.  To use it, in the navigation addon tag, add parameter
 *    of across_columns=1 and it will order across columns using table.  Will
 *    cause weird behavior when trying to use in conjunction with showing sub-regions.
 *  - Fixed error in subdomain-clearing redirect
 *  - Changes to use the new GeoCore region system
 *
 * v4.0.6 - Geo 6.0.7
 *  - Added tag parameter use_cat_count to navigation tag, which will make the
 *    listing category counts narrowed by currently selected category.
 *  - Added a check to make sure listing display pages only show up in the correct region/subdomain
 *  - Fixed weird issues when user's country/state disabled in geo nav
 *
 * v4.0.5 - Geo 6.0.6
 *  - With changes to base software, fixed listing counts for geo nav regions
 *  - Added new parameter to getDisplayDetails() in order item
 *
 * v4.0.4 - Geo 6.0.5
 *  - Fixed more places when geographic nav was "required" and skip single parents
 *    is on
 *
 * v4.0.3 - Geo 6.0.4
 *  - changes to make it easier for other addons to manipulate the filters
 *    generated by this addon.
 *  - Added hook to allow manipulation of the query that counts listings found
 *    in each region, used by navigation tag
 *  - Fixed error with "required registration" checks when skip single parents is on
 *
 * v4.0.2 - Geo 6.0.2
 *  - Made insert_head have auto_add_head method to do what it needs.
 *  - Added using the module hooks so that geo nav stuff can display for modules
 *  - Use new hook for notify_modules_preload to load JS needed for search box 1
 *  - Moved actual functionality of a few private methods in tags file to be in
 *    util instead, as some were needed in new core hook
 *
 * v4.0.1 - Geo 6.0.0
 *  - Changes needed for smarty 3.1 now that tags are loaded on the fly..
 *
 * v4.0.0 - Geo 6.0.0
 *  - Changes for Smarty 3.0
 *  - Changed to not use addBrowsingWhereClause(), to use newer methods for
 *    changing the browsing query
 *  - Optimized the JS used for editing regions, to work slightly better when
 *    dealing with hundreds or thousands of regions at once.
 *  - Made it use addon text for [clear] instead of hard-coding in template
 *  - Added more info for when registration fails, used by API
 *  - Don't show "none" as part of location breadcrumb. (that is what country/state
 *    on listing is set to if no value is selected)
 *  - Added new tag "current_region" which displays the current region selected.
 *  - Updated setup to use new way field locations are stored in the DB.
 *  - Added new tag navigation_top that is duplicate of navigation, but always
 *    starts out at the top level region
 *  - Making navigation display settings able to be specified in tag call
 *  - Removing "sort across columns" setting
 *  - Added new tag change_region_link which displays a "change location" link,
 *    that when clicked, shows fancy location selection based on "mega-dropdowns".
 *  - Added new tag insert_head which inserts the required stuff into the head section
 *    of the page, so that the navigation JS is loaded even when no tags using it
 *    are loaded on the page.  Allows user to assign class "geographic_navigation_changeLink"
 *    to any element, to turn it into a "change location" link.
 *  - Preserve language cookies across subdomain changes
 *  - Order item changed for 6.0
 *  - Add setting to allow skiping over country/state if there is only single selection
 *    available.
 *  - Add integration with storefronts so that geo nav filter filters storefront list
 *
 * v3.2.3 - Geo 5.2.1
 *  - Made the "require full location depth" able to work on countries with no
 *    state/providences attached.
 *  - Fixed minor issue where it didn't properly list ALL the domains in the system
 *    under the sub-domains page.
 *
 * v3.2.2 - Geo 5.2.0
 *  - Made it turn off display if browsing is turned off (Print only setting)
 *  - Fixed sorting issues caused by countries with same ID
 *  - Fixed problem where un-checked states would still display in navigation
 *  - Changes to allow it to be usable in cart in admin
 *  - Added checks to make sure region exists and is enabled before adding
 *    filter for the page in app_top.
 *  - Improved install/uninstall script to be little smarter
 *
 * v3.2.1 - Geo 5.1.4
 *  - Made the autoset feature for subdomains go other direction
 *  - Fixed issue with not showing classifieds counts
 *
 * v3.2.0 - Geo 5.1.2
 *  - Made it able to display geographic navigation fields in search box module.
 *  - Made it able to add text to title module.
 *
 * v3.1.1 - Geo 5.1.1
 *  - Fixed a bug that could cause the addon to not install properly
 *
 * v3.1.0 - Geo 5.1.0
 *  - Updated navigation view to match 5.0 category navigation design
 *  - Replaced the old "display_regions" tag with new "navigation" tag, which is
 *    much more intuitive what the tag is used for.
 *  - It now uses new fields to use system instead of saving fields to use settings
 *    in planItem settings.
 *  - Now able to display geographic navigation column when browsing listings,
 *    according to fields to use settings set.
 *  - Requires 5.1 now for changes to fields to use (using on_off for type data)
 *  - Added breadcrumb tag
 *  - Added way to combine geo nav breadcrumb with cat nav breadcrumb
 *  - Can now show "no sub-region" message in geographic navigation
 *
 * v3.0.0 - Geo 5.0.3/5.1.0
 *  - Added sub-domain abilities, licensing changes require 5.0.3
 *  - Changes for updated license system
 *  - In search, make it work to not select sub-region and still filter by top region selected.
 *
 * v2.2.1 - Geo 5.0.3
 *  - Fixed issue where it was escaping "too much" when editing region value,
 *    resulting in certain text to appear "scrambled".
 *
 * v2.2.0 - Geo 5.0.2
 *  - Added tag for current_location that displays current location.
 *
 * v2.1.0 - Geo 5.0.0
 *  - Changes for new Geo admin design
 *
 * v2.0.4 - Geo 4.1.3
 *  - Added ability to search by location in advanced search
 *  - Fixed the registration requirement checks to actually block from continuing
 *    if requirements were not met.
 *
 * v2.0.3 - Geo 4.1.3
 *  - Removed debug output on the page.
 *  - Fixed fatal error on registration confirm.
 *  - Converted the listing_regions tag to use smarty template.
 *  - Fixed problem with links when arrays are in the vars in the link.
 *  - Fixed it to properly force the state and country fields on the listing
 *
 * v2.0.2 - Geo 4.1.2
 *  - Made it so that when user edits user data, and selects the "apply to all listings"
 *    then it applies to all the user's listings.
 *  - Now requires 4.1.2 because of change to order item behavior.
 *  - Fixed issue when region is set for user but that region does not exist.
 *
 * v2.0.1 - Geo 4.1.2
 *  - Fixed problem causing region data to not save when using country/state info
 *
 * v2.0.0 - Geo 4.1.0
 *  - Added unlimited sub-regions
 *
 * v1.0.5 - Geo 4.0.7
 *  - Fix applied for addon license checks
 *
 * v1.0.4 - Geo 4.0.6
 *  - Fixed problem with naming of column changed, fix requires changes in base
 *    code for 4.0.6.
 *  - Added license checks.
 *  - Made the columns default to 1 for if to use that region or not, in the DB
 *
 * v1.0.3 - Geo 4.0.5
 *  - Re-named to Geographic Navigation Addon (used to be regions filter)
 *
 * v1.0.2 - Geo 4.0.4
 *  - Changed how general settings were saved, to use the addon registry.
 *  - Made the main settings use "normal" save instead of ajax save.  (Ajax save
 *    is nice, but not needed in this case, the AJAX part of it just makes
 *    maintenence a huge pain for adding new settings)
 *  - Changed it so that if there is only 1 main region, it has that region
 *    auto-selected.
 *  - Converted text in template into "addon text" that can be set in the admin
 *    panel.
 *
 * v1.0.1 - Geo 4.0.0RC11
 *  - First version using changelog block
 *  - In admin settings, display the tags on that page to make it more clear they need to
 *    insert a tag somewhere for it to display.
 *  - On fresh install, made number of columns default to 1
 *  - On fresh install, made "display full tree" default to on.
 *  - Fixed template to not set a:link style globally, overriding the preferred styles on the page.
 *
 */
