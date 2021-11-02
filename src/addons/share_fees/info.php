<?php

# Share Fees Addon
class addon_share_fees_info
{

    public $name = 'share_fees';
    public $version = '1.1.0';
    public $core_version_minimum = '17.01.0';
    public $title = 'Share Fees';
    public $author = "Geodesic Solutions LLC.";
    public $description = 'This addon is used internally to share auction final fees paid by an auction seller to another user in the system';
    public $auth_tag = 'geo_addons';

    public $core_events = array (
            'registration_check_info',
            'auction_final_feesOrderItem_cron_close_listings'
        );
}

/**
* Share Fees Changelog
*
* 1.1.0 - REQUIRES 17.01.0
*  - Implemented new admin design
*
* 1.0.0 - Geo 7.4.4
*  - Addon Created
*
*/
