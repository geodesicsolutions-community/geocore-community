<?php

//addons/sharing/methods/pinterest.php
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
## ##    17.10.0-10-g338411a
##
##################################




class addon_sharing_method_pinterest
{

    public $name = 'pinterest';


    /**
     * Gets the name of any methods that want to be used for this listing id.
     * Note that this function being called in the first place implies that the listing in question is live and belongs to the current user
     * @param int $listingId
     * @return String the name of any available method, sans any formatting
     */
    public function getMethodsForListing($listingId)
    {
        return '';
    }

    /**
     * Gets the full HTML to show in the "options" block of the main addon page.
     * This function is responsible for any needed templatization to generate that HTML.
     * @return String HTML
     */
    public function displayOptions()
    {
        return '';
    }


    public function getShortLink($listingId, $iconOnly = false)
    {
        if (!$iconOnly) {
            //only in the new display (for now?)
            return '';
        }
        $tpl = new geoTemplate('addon', 'sharing');
        $tpl->assign('iconUrl', geoTemplate::getUrl('images', 'addon/sharing/icon_pinterest.png'));
        $msgs = geoAddon::getText('geo_addons', 'sharing');
        //$tpl->assign('text', $msgs['shortlink_twitter']);

        $listing = geoListing::getListing($listingId);
        $urlToListing = urlencode($listing->getFullUrl());
        $imgUrl = DataAccess::getInstance()->GetOne("SELECT `image_url` FROM `geodesic_classifieds_images_urls` WHERE `classified_id` = ? ORDER BY `display_order`", array($listingId));
        if ($imgUrl) {
            $img = (stripos($imgUrl, '://') === false) ? geoFilter::getBaseHref() : '';
            $img .= $imgUrl;
            $img = urlencode($img);
        } else {
            //cannot post to pinterest without an image
            return '';
        }
        $description = strip_tags($listing->description);

        if ($listing->item_type == 1) {
            $price = $listing->price;
        } elseif ($listing->item_type == 2) {
            if ($listing->buy_now_only == 1) {
                $auction_price = $listing->buy_now;
            } elseif ($listing->current_bid < $listing->starting_bid) {
                $auction_price = $listing->starting_bid;
            } else {
                $auction_price = $listing->current_bid;
            }
            $price = $auction_price;
        }
        if ($price > 0) {
            //if price > 0, add it to the text of the description
            $description .= ' ' . geoString::displayPrice($price, $listing->precurrency, $listing->postcurrency);
        }

        //pinterest seems to cap out (and throw nasty 502 errors) at a description length of 500 unencoded characters...if we're over that for this listing, do some truncating!
        if (strlen($description) > 500) {
            $description = substr($description, 0, 497) . '...';
        }

        $description = urlencode($description);

        $tpl->assign('link', 'http://pinterest.com/pin/create/button/?url=' . $urlToListing . '&amp;media=' . $img . '&amp;description=' . $description);
        $tpl->assign('iconOnly', $iconOnly);

        return $tpl->fetch('shortLink.tpl');
    }
}
