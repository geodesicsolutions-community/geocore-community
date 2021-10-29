<?php

//getListing.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    7.5.3-36-gea36ae7
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//check to see if a specified user or e-mail exists already in the Geo software.

$listingId = $args['listingId'];
if (!$listingId || !is_numeric($listingId)) {
    return $this->failure('Error: bad listing id');
}

$listing = geoListing::getListing($listingId);
if (!$listing) {
    return $this->failure('Error: no listing by that id');
}

if ($listing->item_type == 1) {
    $price = $listing->price;
} else {
    $price = ($listing->buy_now_only == 1) ? $listing->buy_now : max($listing->minimum_bid, $listing->starting_bid);
}
$price = geoString::displayPrice($price);

//get all images for this listing
$db = DataAccess::getInstance();
$sql = "SELECT image_url, thumb_url, image_text FROM geodesic_classifieds_images_urls WHERE classified_id = ? ORDER BY display_order ASC";
$imgResult = $db->Execute($sql, array($listing->id));
$images = array();
for ($i = 0; $imgResult && $img = $imgResult->FetchRow(); $i++) {
    $url = ($img['image_url']) ? $img['image_url'] : $img['thumb_url']; //prefer main pic, use thumbnail if needed
    $thumbURL = ($img['thumb_url']) ? $img['thumb_url'] : $img['image_url']; //prefer thumbnail, use main pic if needed
    $caption = ($img['image_text']) ? $img['image_text'] : '';

    if ($i == 0) {
        //$thumbnail is the main listing thumbnail -- stored separate from the main images array for use on main listing details page
        $thumbnail = geoImage::absoluteUrl($thumbURL);
    }

    $images[] = array(
                'image' => geoImage::absoluteUrl($url),
                'thumb' => geoImage::absoluteUrl($thumbURL),
                'caption' => geoString::specialCharsDecode($caption)
    );
}

$regions = geoRegion::getRegionsForListing($listing->id);
$levels = geoRegion::getLevelsForOverrides();
$state = geoRegion::getAbbreviationForRegion($regions[$levels['state']]);
if ($levels['city']) {
    $city = geoRegion::getNameForRegion($regions[$levels['city']]);
} elseif ($listing->city) {
    $city = geoString::fromDB($listing->city);
} else {
    //no city, so use the full state name
    $city = false;
    $state = geoRegion::getNameForRegion($regions[$levels['state']]);
}

$terminalRegionName = ($city) ? "$city, $state" : $state;


$return = array(
            'listing' => array(
                'listingId' => $listing->id,
                'title' => geoString::fromDB($listing->title),
                'description' => geoString::fromDB($listing->description),
                'price' => $price,
                'thumbnail' => $thumbnail,
                'category' => geoString::fromDB(geoCategory::getName($listing->category, true)),
                'date' => date('M j, Y', $listing->date)
),
            'seller' => array(
                'id' => geoUser::userName($listing->seller),
                'email' => (geoString::isEmail(geoString::fromDB($listing->email))) ? geoString::fromDB($listing->email) : '',
                'phone' => geoString::fromDB($listing->phone),
),
            'mapping' => array(
                //for back-compatibility with the old mapping method (so as not to break live apps), keep the old field names, at least for now...
                'address' => geoString::fromDB($listing->mapping_location),
                'city' => $terminalRegionName, //this is not necessarily the actual city, but the value populates the "location" field in iOS apps
                'state' => '',
                'country' => '',
                'zip' => '',
),
            'images' => $images
);

return $return;
