<?php

//do not modify the next 2 lines:
require_once 'app_top.common.php';
$feed = new geoListingFeed();

/**
 * NOTICE:  If you want to change any defaults on this file, we recommend
 * to either use a config file rss_listings.config.php (see notes below), or
 * you can rename this file first so it will not be over-written by future
 * updates.
 *
 * Also note that you can have more than one copy of this file in use, for example
 * one to display newest listings and another for expiring soon listings.  Just
 * create 2 copies of the file, named differently.
 */

/**
 * This setting is for the RSS config filename, which if found, the file will
 * be "included" so that you can change settings in that file instead of
 * directly in the RSS file.  If the file does not exist, no worries, the
 * settings in this RSS file will be used.  Also if the config file does exist,
 * but a particular setting is not set in that file, it will be set by this
 * RSS file.  So if you wanted to keep all the default RSS settings except
 * make a change to only 1 setting, you only have to put the setting you want
 * to change in the config file.
 *
 * In other words, instead of making RSS setting changes directly to the RSS
 * file, this gives you the option to make changes in a seperate "config" file
 * instead, that will not be over-written when you update the software.
 *
 * To do so, create the file "rss_listings.config.php" (or named how you want)
 * and copy/paste the first part of this file, up until the "END SETTINGS" line.
 *
 * Examples:
 * detect - let the script detect what the config filename is, which will be
 *          the same as this filename, but with .php replaced with .config.php
 * my_rss_config.php - use the file named "my_rss_config.php" for the file name.
 */
$RSSconfigFilename = 'detect';

/**
 * This is the description used for the feed channel.  Default is empty string.
 *
 * Make sure this is valid text for XML document, or it will invalidate the Feed.
 * For example, NO html, and be sure to use HTML entities
 */
$feed->description = "";

/**
 * This is the title used for the feed
 *
 * Make sure this is valid text for XML document, or it will invalidate the Feed.
 * For example, NO html, and be sure to use HTML entities
 */
$feed->title = "Newest Listings Feed";

/**
 * This should be the full URL for this feed, un-comment to set.  If not set,
 * it will automatically detect the feed's URL.  If the detection is not working
 * for your site, you may need to manually specify the URL here by un-commenting
 * the line and setting the URL.  Note that the
 * detection should work even if you have re-named the file (as the instructions
 * recommend)
 *
 * Note: this URL will be used for the atom link in the RSS feed.
 *
 * Examples:
 * http://example.com/rss_listings.php - manually set the full URL
 *
 * Warning: inproperly setting this may result in an invalid RSS feed.
 */
//$feed->atomLink = 'http://example.com/rss_listings.php';

/**
 * The max number of listings to pull, this is required and will default to 10 if
 * not set to a valid number.
 *
 * Special value:
 * geoListingFeed::URL_SET : default display 10 listings, but can specify a
 *      different number in the URL, for example
 *      "rss_listings.php?maxListings=50" would display a maximum of 50
 *      listings.  If using this, it will also use the "forceMaxListings"
 *      setting below.
 */
$feed->maxListings = 20; //geoListingFeed::URL_SET;

//used if $feed->maxListings set to "set", this will be the max allowed set in the URL.
$feed->forceMaxListings = 100;

/**
 * the category id you want to display from...
 *
 * Special values if specific category ID not specified:
 * 0 : (numeric zero) - display from all categories & sub-categories.
 * geoListingFeed::URL_SET : default display from all categories, but can specify a specific category
 *      in the URL, for example "rss_listings.php?catId=67" would display
 *      all listings from category ID of 67 (or sub-categories), if invalid category
 *      it defaults to show from all categories
 */
$feed->catId = 0;

/**
 * Restrict feed contents according to Geographic Navigation?
 *
 * possible values:
 * 0 - do not filter on geo nav region
 * geoListingFeed::COOKIE_SET - use the current browser's active "region" selection (cookie)
 * geoListingFeed::URL_SET - default from all regions, but can specify in the URL
 *      for example: "rss_listings.php?geoNavRegion=52" to display only listings in region 52
 * specific region value (e.g. "52") - display only listings from the given region
 *
 *
 */
$feed->geoNavRegion = geoListingFeed::COOKIE_SET;

/**
 * the user id you want to display listings for
 *
 * Special values if specific user ID not specified:
 * 0 : (numeric zero) - do not narrow by specific seller
 * geoListingFeed::URL_SET : default display from all users, but can specify a specific user ID
 *      in the URL, for example "rss_listings.php?userId=67" would display
 *      all listings from user ID of 67, if invalid user ID
 *      it defaults to show from all users
 */
$feed->userId = 0;

/**
 * Change order of listings.  Default is "new".  Note that the RSS is not affected by
 * filters.
 *
 * possible settings:
 * new : Order by starting date, newest first.
 * old : order by starting date, oldest first
 * expiring : order by closing date, those closing soonest first.
 * hottest : order like hottest listings module
 * featured_# : Display featured listings using featured level specified by #.  Do
 *              not actually set it to "featured_#", you would set it to "featured_1"
 *              for example, to display featured level 1.  As with main featured
 *              modules, this sorts randomely.
 * geoListingFeed::URL_SET : Can be set in URL using orderBy, for example "rss_listings.php?orderBy=old", if
 *       invalid value specified, it defaults to "new".
 */
$feed->orderBy = "new";


/**
 * Type of listings to display, default is "all".
 *
 * Possible settings:
 * all : Show any type of listing.
 * all_auction : only show auctions (only valid on auction products)
 * buy_now : only show buy now auctions (only valid on auction products)
 * buy_now_only : only show buy now only auctions (only valid on auction products)
 * dutch : only show dutch auctions (only valid on auction products)
 * reverse : only show reverse auctions (not typical)
 * classified : only show classified listings (only valid on classified products)
 * geoListingFeed::URL_SET : can be set in URL using type, for example
 *      "rss_listings.php?type=auction".  If invalid type is specified, will
 *      default to all.
 */
$feed->type = "all";

/**
 * The charset to use for the feed.  Default is 'UTF-8', some sites may need
 * to change this.
 */
$feed->charset = 'UTF-8';

###  Content Display Settings  ###

/**
 * This is the character limit for how much of the listing title to display.
 * If set to 0, it will display the entire title, otherwise it will cut off
 * the title at the appropriate length set here.
 *
 * Default is 0 (no limit)
 */
$feed->titleCharLimit = 0;

/**
 * This is the character limit for how much of the listing description to display.
 * If set to 0, it will display the entire description, leaving HTML formatting intact.
 * If NOT set to 0, it will strip all HTML tags (for instance, if there was bolded
 * text, it would no longer be bolded) and truncate after the # chars specified.
 *
 * If you want to remove all HTML formatting, but don't actually want to limit how
 * much is displayed, just set this to something realy large like 1000000.
 *
 * Default is 0 (no limit, display description as-is)
 */
$feed->descriptionCharLimit = 0;

/**
 * Clean description of possible "character based issues" within the description
 *
 * One of the things this setting does is tell the software to exchange some
 * characters (like �, �,...etc) for their html entity equivalents
 *
 */
$feed->clean_description = true;

/**
 * If $show['image'] (further below) is set to 1, this next setting determines how
 * many images are displayed. Setting to 0 makes it display all images with no limit.
 *
 * Default is 1 (only show first image)
 */
$feed->imageCount = 1;

/**
 * If $show['image'] is set to 1, the following 2 settings determine the max width
 * and height size in pixels.  Setting to 0 makes it use un-altered image size.
 *
 * Default is 100x100 max dimensions.
 */
$feed->imageWidth = 100;
$feed->imageHeight = 100;

/**
 * Setting this to 1 will turn on display of lead image, with rest of the description
 * of the item flowing around it (that part depends on RSS reader though).
 *
 * Default value is 1. Disable by setting to 0
 */
$feed->leadImage = 1;

/**
 * Used as CSS value for float for the lead image.  This can be changed to
 * "left" (default), "right", or "none", or "" (to not use float).  Note that
 * some RSS feed readers will ignore any "in-line" style set, so this may have no
 * effect, depending on the RSS feed reader.
 *
 * Default is 'left'
 */
$feed->leadImageFloat = 'left';

/**
 * If $leadImage is set to 1, the following 2 settings determine the max width
 * and height size in pixels.  Setting to 0 makes it use un-altered image size.
 *
 * Default is 200x200 max dimensions.
 */
$feed->leadWidth = 200;
$feed->leadHeight = 200;

/**
 * If there are no listings to display, a "default item" can be displayed saying
 * whatever you want.  If you wish to use this item, change this setting to 1.
 * Otherwise change the setting to 0 to not use a default item when there are
 * no items to display.
 */
$feed->useEmptyItem = 1;

/**
 * The next 3 settings are used if $useEmptyItem = 1.  They are for what the
 * "default item" will look like.
 */
$emptyItem['title'] = "No Listings";
//Use 'detect' to make it use the site's URL.  Otherwise specify the URL you
//want the default item to link to (such as place a listing or something).
//If you specify something other than 'detect', it needs to be an absolute link.
$emptyItem['link'] = 'detect';
//Note that HTML IS allowed here.
$emptyItem['description'] = 'View our site to place new listings!';
//leave next line intact
$feed->emptyItem = $emptyItem;

/**
 * The next 3 settings are used if the site is turned off, for an item that will
 * be displayed when the site is off.
 */
$siteOffItem['title'] = 'Site Under Maintenance';
//Use 'detect' to make it use the site's URL.  Otherwise specify the URL you
//want the default item to link to (such as place a listing or something).
//If you specify something other than 'detect', it needs to be an absolute link.
$siteOffItem['link'] = 'detect';
//Note that HTML IS allowed here.
$siteOffItem['description'] = "<strong>We're sorry.  Our site is temporarily undergoing routine maintenance.  Please
    check back soon.</strong>";
//leave next line intact
$feed->siteOffItem = $siteOffItem;

/**
 * Zero-price format: controls the display of prices that are zero
 * Given a listing price of 0.00 with this setting on 1, it will show something like "N/A" (according to admin settings)
 * With this setting on 0, it will display normally, e.g. "$0.00 USD"
 */
$feed->formatZeroPriceAsText = 1;

/**
 * The following settings determine what fields will be displayed.  Note that
 * if the title will always be used, as the title of the "article" in the feed.
 *
 * - Set 0 if you DO NOT want to display
 * - Set 1 if you DO want to display
 *
 * Default is 0 for all fields.  Note that even if turned on, if a field is
 * blank for a listing, that field will not be displayed at all.
 *
 * Oh, and you can change the order of the settings below, and it will change
 * the order things are displayed in the feed.
 */
$show['description'] = 1;
$show['image'] = 0;
$show['location_address'] = 0;
$show['location_city'] = 0;
$show['location_state'] = 0;
$show['location_country'] = 0;
$show['location_zip'] = 0;
$show['price'] = 0;
$show['optional_field_1'] = 0;
$show['optional_field_2'] = 0;
$show['optional_field_3'] = 0;
$show['optional_field_4'] = 0;
$show['optional_field_5'] = 0;
$show['optional_field_6'] = 0;
$show['optional_field_7'] = 0;
$show['optional_field_8'] = 0;
$show['optional_field_9'] = 0;
$show['optional_field_10'] = 0;
$show['optional_field_11'] = 0;
$show['optional_field_12'] = 0;
$show['optional_field_13'] = 0;
$show['optional_field_14'] = 0;
$show['optional_field_15'] = 0;
$show['optional_field_16'] = 0;
$show['optional_field_17'] = 0;
$show['optional_field_18'] = 0;
$show['optional_field_19'] = 0;
$show['optional_field_20'] = 0;

/**
 * The following settings are for Labels used for each field.  Note that
 * these can be left blank.  They are not used if the "show" setting for that
 * field is turned off, or if the field on the listing is blank.
 */
$label['description'] = 'Description:<br />';
$label['image'] = 'Images:<br />';
$label['location_address'] = 'Street: ';
$label['location_city'] = 'City: ';
$label['location_state'] = 'State: ';
$label['location_country'] = 'Country: ';
$label['location_zip'] = 'Zip: ';
$label['price'] = 'Price: ';
$label['optional_field_1'] = 'Optional field 1: ';
$label['optional_field_2'] = 'Optional field 2: ';
$label['optional_field_3'] = 'Optional field 3: ';
$label['optional_field_4'] = 'Optional field 4: ';
$label['optional_field_5'] = 'Optional field 5: ';
$label['optional_field_6'] = 'Optional field 6: ';
$label['optional_field_7'] = 'Optional field 7: ';
$label['optional_field_8'] = 'Optional field 8: ';
$label['optional_field_9'] = 'Optional field 9: ';
$label['optional_field_10'] = 'Optional field 10: ';
$label['optional_field_11'] = 'Optional field 11: ';
$label['optional_field_12'] = 'Optional field 12: ';
$label['optional_field_13'] = 'Optional field 13: ';
$label['optional_field_14'] = 'Optional field 14: ';
$label['optional_field_15'] = 'Optional field 15: ';
$label['optional_field_16'] = 'Optional field 16: ';
$label['optional_field_17'] = 'Optional field 17: ';
$label['optional_field_18'] = 'Optional field 18: ';
$label['optional_field_19'] = 'Optional field 19: ';
$label['optional_field_20'] = 'Optional field 20: ';

###  Advanced Controls  ###
# The controls demonstrated below are more for PHP developers, to give them
# more control over what listings are displayed, without having
# to edit things below the "do not edit below this line" line.

/**
 * This is an example of how to add further "filter" criteria.  To use, you would
 * "un-comment" the following line by removing the # in front of it.  This will
 * add an additional "where field_name = 'field value'" to the SQL query that gets
 * the listings, so it lets you get fancy with things.  Remember, certain fields are
 * "url encoded", so keep that in mind when creating custom where clauses.
 *
 * If you need to have more control over the SQL query than just adding an
 * additional "where clause", you can get the geoTableSelect object by calling:
 *
 * $query = $feed->getTableSelect();
 *
 * Use it's methods to alter the query used when listings are retrieved.  See
 * the docs on geoTableSelect class for more info on what can be done.
 */
#$feed->where("`geodesic_classifieds`.`location_zip` = '12345'");

/**
 * Set debug to 1 to make it NOT send headers for RSS listing (so it displays in browser as non-rss feed)
 */
$feed->debug = false;

#####################################################################
###  END SETTINGS - do not modify anything below this line!  ########
#~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~#

//let whoever needs to know, this is being called in an RSS feed file...
define('IN_GEO_RSS_FEED', 1);

//Now we will see if a custom config file exists, and if it does, include
//that file to over-write the settings set in this one.
if ($RSSconfigFilename == 'detect') {
    $RSSconfigFilename = str_replace('.php', '.config.php', __file__);
}
if ($RSSconfigFilename && file_exists($RSSconfigFilename)) {
    //include the config file to over-write the settings in this file.
    include $RSSconfigFilename;
}

//assign show and label vars AFTER include of config file
$feed->label = $label;
$feed->show = $show;


//set a few defaults specific to RSS feed
if ($feed->titleCharLimit) {
    //add 3 chars to account for ...
    $feed->titleCharLimit += 3;
}
if ($feed->descriptionCharLimit) {
    $feed->descriptionCharLimit += 3;
}
if (!isset($feed->atomLink)) {
    //detect what the URL should be
    $dir = dirname(__file__) . '/';
    $file = str_replace($dir, '', __file__);
    $feed->atomLink = geoFilter::getBaseHref() . $file;
}

if (!isset($feed->forceMaxListings)) {
    //set default value
    $feed->forceMaxListings = 100;
}
//set the limit
if (
    $feed->maxListings == geoListingFeed::URL_SET
    && isset($_GET['maxListings'])
    && intval($_GET['maxListings']) <= $feed->forceMaxListings
) {
    $feed->maxListings = $_GET['maxListings'];
}

//let addons know we're at top of rss_listings
geoAddon::triggerUpdate('notify_rss_listings');

//This is a RSS feed
$feed->setFeedType(geoListingFeed::RSS_FEED);

$feed->generateSql();

$feed->generateResultSet();

//display the feed
echo $feed;

include GEO_BASE_DIR . 'app_bottom.php';
