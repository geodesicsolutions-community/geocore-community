<?php

//addons/site_stats/info.php


# Site Stats

class addon_site_stats_info
{
    public $name = 'site_stats';
    public $title = 'Site Stats';
    public $version = '1.0.0';
    public $core_version_minimum = '7.1.0';
    public $description = 'Allow users to place stats about users and listings within their site';
    public $author = 'Geodesic Solutions LLC';
    public $auth_tag = 'geo_addons';
    public $author_url = 'http://geodesicsolutions.com';

    public $tags = array (
            'number_listings_all_types',
            'number_classifieds',
            'number_auctions',
            'number_listings_all_types_last_24hrs',
            'number_classifieds_last_24hrs',
            'number_auctions_last_24hrs',
            'number_listings_all_types_last_7days',
            'number_classifieds_last_7days',
            'number_auctions_last_7days',
            'number_listings_all_types_last_30days',
            'number_classifieds_last_30days',
            'number_auctions_ending_next_24hrs',
            'number_auctions_ending_next_7days',
            'number_auctions_last_30days',
            'total_views',
            'number_of_registrants_24hrs',
            'number_of_registrants_7days',
            'number_of_registrants_30days',
            'number_of_registrants_last',
            'number_of_logins_last',
            'number_of_users_place_listing_in_last',
            'number_listings_language',
            'number_listings_category',
            'number_listings_region_category'
    );

    /*
    * CHANGELOG
    * v1.0.0 - Geo 7.1.0beta1
    *  - Addon created
    *
    */
}
