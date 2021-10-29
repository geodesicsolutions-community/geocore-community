<?php

//addons/sharing/tags.php
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
##    7.6.3-59-ge30e65b
##
##################################

# sharing Addon

require_once ADDON_DIR . 'sharing/info.php';

class addon_sharing_tags extends addon_sharing_info
{
    public function listing_social_buttons($params, Smarty_Internal_Template $smarty)
    {
        //add CSS to page
        $view = geoView::getInstance();
        if ($view->preview_listing) {
            //do not show on preview listing page, it can mess things up
            return '';
        }
        $util = geoAddon::getUtil($this->name);

        $listing_id = (int)$params['listing_id'];

        $tpl_vars = array ();

        $tpl_vars['activeMethods'] = $util->getActiveMethods(false, true);

        //specify which image should be used for methods that pick thumbnails (like Facebook and Digg)
        $imgUrl = DataAccess::getInstance()->GetOne("SELECT `image_url` FROM `geodesic_classifieds_images_urls` WHERE `classified_id` = ? ORDER BY `display_order`", array($listing_id));
        if ($imgUrl) {
            $img = (stripos($imgUrl, '://') === false) ? geoFilter::getBaseHref() : '';
            $img .= $imgUrl;
            $tpl_vars['lead_image'] = $img;
        }

        $tpl_vars['text'] = geoAddon::getText('geo_addons', 'sharing');
        $tpl_vars['shortLinks'] = $util->getShortLinks($listing_id);

        $tpl_vars['showMoreLink'] = false; //set this later when number of total methods exceeds what we want to show in the popup
        //$tpl_vars['numMethods'] = count($util->_methods);

        $listing = geoListing::getListing($listing_id);

        $tpl_vars['shareButtonImage'] = geoTemplate::getUrl('images', 'addon/sharing/icon_share.png');
        $tpl_vars['forListing'] = $listing_id;
        $tpl_vars['file_name'] = $fileName = DataAccess::getInstance()->get_site_setting('classifieds_file_name');
        $tpl_vars['listing_url_unencoded'] = $listing->getFullUrl();
        $tpl_vars['listing_url'] = urlencode($tpl_vars['listing_url_unencoded']);


        $tpl_vars['listing_data'] = $listing->toArray();
        $tpl_vars['listing_data']['description'] = geoString::specialChars(geoFilter::listingDescription($listing->description, true));

        $tpl_vars['social_buttons'] = array (
            'twitter.tpl',
            'google_plus.tpl',
            'pinterest.tpl',
            'linkedin.tpl',
            'facebook.tpl',
            );

        //set up formatted price as a separate var
        if ($listing->item_type == 1) {
            $tpl_vars['price'] = geoString::displayPrice($listing->price, $listing->precurrency, $listing->postcurrency);
        } elseif ($listing->item_type == 2) {
            if ($listing->buy_now_only == 1) {
                $auction_price = $listing->buy_now;
            } elseif ($listing->current_bid < $listing->starting_bid) {
                $auction_price = $listing->starting_bid;
            } else {
                $auction_price = $listing->current_bid;
            }
            $tpl_vars['price'] = geoString::displayPrice($auction_price, $listing->precurrency, $listing->postcurrency);
        }

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'button_listing_display.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    public function listing_social_simple_icons($params, Smarty_Internal_Template $smarty)
    {
        $view = geoView::getInstance();
        if ($view->preview_listing) {
            //do not show on preview listing page, it can mess things up
            return '';
        }
        $util = geoAddon::getUtil($this->name);

        $listing_id = (int)$params['listing_id'];

        $tpl_vars = array ();
        $tpl_vars['icons'] = $util->getShortLinks($listing_id, true);
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'listing_simple_icons.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
