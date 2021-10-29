<?php

//addons/sharing/methods/reddit.php
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
## ##    7.6.3-59-ge30e65b
##
##################################

class addon_sharing_method_reddit
{

    public $name = 'reddit';

    /**
     * Gets the name of any methods that want to be used for this listing id.
     * Note that this function being called in the first place implies that the listing in question is live and belongs to the current user
     * @param int $listingId
     * @return String the name of any available method, sans any formatting
     */
    public function getMethodsForListing($listingId)
    {

        //we want this to be available for all listings, so simply return the name to show
        $msgs = geoAddon::getText('geo_addons', 'sharing');
        return $msgs['method_btn_reddit'];
    }

    /**
     * Gets the full HTML to show in the "options" block of the main addon page.
     * This function is responsible for any needed templatization to generate that HTML.
     * @return String HTML
     */
    public function displayOptions()
    {
        $data = $_POST;
        $listing = geoListing::getListing($data['listing']);
        $urlToListing = $listing->getFullUrl();

        $tpl = new geoTemplate('addon', 'sharing');
        $tpl->assign('listing_url', urlencode($urlToListing));
        $tpl->assign('title', $listing->title); //already urlencoded in the database, and needs to stay that way
        $tpl->assign('description', urlencode(strip_tags(geoString::fromDB($listing->description))));
        $tpl->assign('msgs', geoAddon::getText('geo_addons', 'sharing'));
        $html = $tpl->fetch('methods/reddit_options.tpl');
        return $html;
    }

    public function getShortLink($listingId, $iconOnly = false)
    {
        $tpl = new geoTemplate('addon', 'sharing');
        $tpl->assign('iconUrl', geoTemplate::getUrl('images', 'addon/sharing/icon_reddit.png'));
        $msgs = geoAddon::getText('geo_addons', 'sharing');
        $tpl->assign('text', $msgs['shortlink_reddit']);

        $urlToListing = urlencode(geoListing::getListing($listingId)->getFullUrl());
        $tpl->assign('link', 'http://reddit.com/' . $urlToListing);
        $tpl->assign('iconOnly', $iconOnly);

        return $tpl->fetch('shortLink.tpl');
    }
}
