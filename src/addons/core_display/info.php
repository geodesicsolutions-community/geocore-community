<?php

//addons/core_display/info.php

# Storefront Addon
class addon_core_display_info
{

    public $name = 'core_display';
    public $version = '1.3.0';
    public $core_version_minimum = '17.01.0';
    public $title = '<strong style="font-style: italic;">Core</strong> Display';
    public $author = "Geodesic Solutions LLC.";
    public $description = 'This addon is used internally to easily display content on multiple pages';
    public $auth_tag = 'geo_addons';

    //public $icon_image = 'images/menu_storefront.gif';
    public $author_url = 'http://geodesicsolutions.com';

    public $tags = array (
        'display_browsing_filters',
        'browsing_featured_gallery',
    );
    public $core_tags = array (
        'browsing_before_listings_column',
        'browsing_before_listings',
        );

    public $core_events = array(
        'process_browsing_filters',
        'geoFields_getDefaultLocations',
        'admin_category_manage_add_links',
        'admin_category_list_specific_icons'
    );
}

/**
 * Core Display Changelog
 *
 * 1.3.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 1.2.1 - Geo 16.01.0
 *  - Fixed multiple bugs relating to date-type category-specific fields
 *  - Several changes to default templates/text to support new design
 *
 * 1.2.0 - Geo 7.6.0
 *  - Add ability to sort the display order of browsing filters
 *
 * 1.1.0 - Geo 7.5.0
 *  - Fixed featured gallery thumbnails using incorrect 'height' setting
 *  - Added option to use dynamically-sized thumbnails
 *
 * 1.0.4 - Geo 7.4.0
 *  - Add setting for max title / optional field length for featured gallery
 *  - Make some of featured gallery settings able to be overridden in tag params
 *  - Fixed being unable to set numerical range fields to non-integer values
 *  - Fixed non-integer filter values displaying incorrectly for non-American number formats
 *
 * 1.0.3 - Geo 7.3.2
 *  - Fix broken calendar inputs
 *
 * 1.0.2 - Geo 7.3.0
 *  - Changes to use add_footer_html
 *  - Made it only init geoCalendar when actually using calendar inputs
 *
 * 1.0.1 - Geo 7.1.3
 *  - Fixed problem where counts reflected category that the browsing filters were
 *    being used from instead of the actual browsing category.  See bug 766
 *
 * 1.0.0 - Geo 7.1.0
 *  - Addon Created
 *  - Used to allow display of Browsing Filters from multiple core pages
 *
 */
