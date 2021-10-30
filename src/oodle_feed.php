<?php

//oodle_feed.php


/**
 * set the next line to 1 to "turn on" the oodle feed.  By default it is set to
 * 0 since the oodle feed can get pretty big and potentially take up a lot of
 * resources.
 */

$enabled = 0;

//do not modify the next 2 lines:
require_once 'app_top.common.php';
$feed = new geoListingFeed();

/**
 * NOTICE:  If you want to change any defaults on this file, we recommend
 * to either use a config file oodle_feed.config.php (see notes below), or
 * you can rename this file first so it will not be over-written by future
 * updates.
 *
 * Also note that you can have more than one copy of this file in use, if you
 * wish to create more than one feed which might contain different contents.
 */

/**
 * This setting is for the oodle config filename, which if found, the file will
 * be "included" so that you can change settings in that file instead of
 * directly in this oodle feed file.  If the file does not exist, no worries, the
 * settings in this main file will be used.  Also if the config file does exist,
 * but a particular setting is not set in that file, it will be set by this
 * file.  So if you wanted to keep all the default oodle settings except
 * make a change to only 1 setting, you only have to put the setting you want
 * to change in the config file.
 *
 * In other words, instead of making oodle setting changes directly to the
 * file, this gives you the option to make changes in a seperate "config" file
 * instead, that will not be over-written when you update the software.
 *
 * To do so, create the file "oodle_feed.config.php" (or named how you want)
 * and copy/paste the first part of this file, up until the "END SETTINGS" line.
 *
 * Examples:
 * detect - let the script detect what the config filename is, which will be
 *          the same as this filename, but with .php replaced with .config.php
 * my_oodle_config.php - use the file named "my_oodle_config.php" for the file name.
 */
$RSSconfigFilename = 'detect';

/**
 * This is the file that holds the "Geo category ID to Oodle category map" if
 * one exists.  If the file is not found or invalid, will use the default
 * oodle category for every listing.  See the next setting for mini-tool that
 * generates the map file.
 */
$oodleCatMapFile = 'oodleCatMap.php';

/**
 * Set this to 1 to make it generate the contents of a Geo category to Oodle category
 * map file.  You would take the contents and put them in the file.  If a map
 * file already exists, it will attempt to "start" from that and add any missing
 * categories.
 *
 * All categories will start out mapping to "sales" oodle category, you will need
 * to go through and modify them.
 */
$generateOodleCatMapFile = 0;

/**
 * The max number of listings to pull, this is required and will default to 500 if
 * not set to a valid number.  This limit is in place to reduce the amount of
 * resources it used to generate the feed.  If your site has a lot more listings,
 * try it out at a higher number to see if it can get to all of the listings.
 *
 * Some servers might have memory_limit problems, or "max execution time"
 * (timeout) problems when attempting to generate an oodle feed with a ton of
 * listings in it.
 */
$feed->maxListings = 500;

/**
 * the category id you want to display from...
 *
 * Special values if specific category ID not specified:
 * 0 : (numeric zero) - display from all categories & sub-categories. (be sure to set up oodle category map!)
 * set : default display from all categories, but can specify a specific category
 *      in the URL, for example "oodle_feed.php?catId=67" would display
 *      all listings from category ID of 67 (or sub-categories), if invalid category
 *      it defaults to show from all categories
 */
$feed->catId = 0;

/**
 * This allows you to specify whether you want the image URL in the feed, to
 * link to the thumbnail or full sized image.  Note that according to the
 * official oodle specifications, the image size cannot be over 30K, which is
 * why we recommend using thumbnail size to ensure it uses an image that does
 * not go over the 30K restriction.  More info at:
 *
 * http://www.oodle.com/info/feed/feed_faq/#image_url_used_for
 *
 * Valid values:
 * geoListingFeed::IMG_THUMB : use the thumbnail
 * geoListingFeed::IMG_FULL : use the full-sized image
 */
$feed->defaultImgType = geoListingFeed::IMG_THUMB;

/**
 * This is the default oodle category name, it is what will be used if there is
 * no category map found for a particular category ID assigned to a listing.
 *
 * A list of oodle categories can be found at the URL:
 * http://www.oodle.com/info/feed/category.html
 */
$feed->defaultOodleCat = 'sale';

/**
 * Change order of listings.  Default is "new" and is what is recommended for
 * oodle feeds.  Note that the oodle feed is not affected by filters like the
 * geographic navigation addon filter.
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
 * set : Can be set in URL using orderBy, for example "oodle_feed.php?orderBy=old", if
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
 *      "oodle_feed.php?type=auction".  If invalid type is specified, will
 *      default to all.
 */
$feed->type = "all";

/**
 * The charset to use for the feed.  Default is 'UTF-8', some sites may need
 * to change this.
 */
$feed->charset = 'UTF-8';


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

//Common fields that must be retrieved for every listing for the oodle feed
//DO NOT EDIT!  You are WAY past all the configuration settings, if you change
//any of the below, it will break the oodle feed!
$showFields = array ('id','category','seller','description','date','ends','price','buy_now',
    'location_address','location_city','location_state','location_country','location_zip');
foreach ($showFields as $f) {
    $show[$f] = 1;
}

$feed->show = $show;

//make sure feed is enabled
$enabled || die();

if ($oodleCatMapFile && file_exists($oodleCatMapFile)) {
    include $oodleCatMapFile;
}
$oodleCatMap = (isset($oodleCatMap) && is_array($oodleCatMap)) ? $oodleCatMap : array();

if ($generateOodleCatMapFile) {
    //don't generate an oodle feed!  generate a oodle category map file contents!
    $db = DataAccess::getInstance();
    $cats = $db->GetAll("SELECT `c`.`category_id`, `c`.`parent_id`, `l`.`category_name` FROM " . geoTables::categories_table
        . " c, " . geoTables::categories_languages_table . " l WHERE c.category_id=l.category_id AND l.language_id=1 ORDER BY `c`.`parent_id`, `c`.`display_order`");
    $orderedCats = array();
    foreach ($cats as $cat) {
        $pId = $cat['parent_id'];
        $name = trim(geoString::fromDB($cat['category_name']));
        //clean name up
        $name = preg_replace('/[\n\t\r]+/i', '', $name);

        if ($pId && isset($orderedCats[$pId])) {
            $name = $orderedCats[$pId]['category_name'] . ' > ' . $name;
        }

        $cat['category_name'] = $name;
        $cat['oodleCategory'] = (isset($oodleCatMap[$cat['category_id']])) ? $oodleCatMap[$cat['category_id']] : 'sales';
        $orderedCats[$cat['category_id']] = $cat;
    }
    $tpl = new geoTemplate('system', 'ListingFeed');
    $tpl->assign('cats', $orderedCats);
    $tpl->display('oodleCategoryMap_generator.tpl');

    include GEO_BASE_DIR . 'app_bottom.php';
    exit;
}

if ($feed->debug) {
    //turn on display of errors
    ini_set('display_errors', 'stdout');
}

$feed->oodleCatMap = $oodleCatMap;
//set a few defaults specific to Oodle feed
$feed->leadImage = 1;

//let addons know we're at top of oodle_feed
geoAddon::triggerUpdate('notify_oodle_feed');

//This is an oodle feed
$feed->setFeedType(geoListingFeed::OODLE_FEED);

$feed->generateSql();

$feed->generateResultSet();

//display the feed
echo $feed;

include GEO_BASE_DIR . 'app_bottom.php';
