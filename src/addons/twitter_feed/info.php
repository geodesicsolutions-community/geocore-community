<?php

//addons/twitter_feed/info.php

# Twitter Feed
class addon_twitter_feed_info
{
    public $name = 'twitter_feed';
    public $title = 'Twitter Feed';
    public $version = '2.1.0';
    public $core_version_minimum = '17.01.0';
    public $description = 'Allows sellers to add Twitter "Embedabble Timelines" to their listings.';
    public $author = 'Geodesic Solutions LLC.';
    public $auth_tag = 'geo_addons';

    public $tags = array('show_feed');
    public $listing_tags = array ('show_feed');
}

/**
 * Twitter Feed Changelog
 *
 * 2.1.0 - REQUIRES 17.01.0
 *  - Implemented new admin design
 *
 * 2.0.5 - Geo 16.07.0
 *  - Twitter changed their stuff again. Updating the addon to reflect that the data_id parameter is no longer required (but is sometimes present)
 *
 * 2.0.4 - Geo 16.02.0
 * - Fixed order item not reporting price correctly in some cases
 * - Fixed pasted data being rejected for extra, surrounding whitespace
 * - Default template/text changes to support new design
 *
 * 2.0.3 - Geo 7.3.5
 *  - Fixed using Twitter Feed in non-combined listing steps breaks listing preview popup
 *
 * 2.0.2 - Geo 7.3.4
 *  - Fixed inability to add widget code after selecting a category
 *
 * 2.0.1 - Geo 7.3.1
 *  - Corrected template to properly support color-change admin options
 *  - Fixed a couple cosmetic bugs in the admin
 *
 * 2.0.0 - Geo 7.3.0
 *  - Changes for compatibility with Twitter API v1.1
 *    - Now works with any "Embeddable Timeline," not just a user's most recent tweets, but the timeline widgets must be created on twitter.com
 *
 * 1.0.8 - Geo 7.1.3
 *  - Change to make sure stuff is done correctly when copying a listing
 *
 * 1.0.7 - Geo 7.1.0
 *  - Updated to work with {listing} tags, and load template internally
 *
 * 1.0.6 - Geo 7.0.1
 * - Added new parameter to getDisplayDetails() in order items
 *
 * 1.0.5 - Geo 7.0.0
 *  - Compatibility changes for 7.0 licensing
 *
 * 1.0.4 - Geo 6.0.2
 *  - Update to reflect changes made by Twitter
 *  - Allow twitter feed to appear in the Storefront version of listing display pages, as well
 *
 * 1.0.3 - Geo 6.0.0
 *  - Changes for Smarty 3.0
 *  - Changes for leased license
 *  - Order item changes for 6.0
 *  - Added color swatches to Theme section of admin settings page
 *
 * 1.0.2 - Geo 5.1.5
 *  - Fixed a bug that prevented the twitter feed from appearing in IE7
 *
 * 1.0.1 - Geo 5.1.3
 *  - Fixed a bug that caused the Alternate display method to not display
 *
 * 1.0.0 - Geo 5.1.3
 *  - Addon Created
 *
 */
