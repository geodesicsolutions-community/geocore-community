<?php

//addons/price_drop_auctions/util.php
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
##    16.07.0-76-g6fe5871
##
##################################

# Pedigree Tree

require_once ADDON_DIR . 'price_drop_auctions/info.php';

class addon_price_drop_auctions_util extends addon_price_drop_auctions_info
{
    public function core_listing_placement_moreDetailsPricing_append($listingType)
    {
        if ($listingType !== 'auction') {
            //nothing to do here on non-auctions
            return;
        }

        $msgs = geoAddon::getText('geo_addons', $this->name);
        //$return['section_sub_head'] = $msgs['listing_placement_auction_header'];


        //note, since using "full" display, also responsible for showing errors on return or anything like that
        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);

        $tpl->assign('msgs', $msgs);
        $iconText = DataAccess::getInstance()->get_text(true, 59);
        $tpl->assign('helpIcon', $iconText[500797]);

        //prepopulate selections from a previous attempt
        $cart = geoCart::getInstance();

        $tpl->assign('session_variables', $cart->site->session_variables);



        $return['full'] = $tpl->fetch('listing_placement.tpl');

        return $return;
    }

    public function core_listing_placement_moreDetailsLocation_append_checkVars($listingType)
    {
        if ($listingType !== 'auction') {
            //nothing to do here on non-auctions
            return;
        }
        $cart = geoCart::getInstance();
        $price_drop = (int)$_POST['price_drop'] == 1 ? 1 : 0;
        $minimum = (float)$_POST['price_drop_minimum'];

        if (!$price_drop) {
            //not dropping the price
            $cart->site->session_variables['price_drop'] = 0;
            unset($cart->site->session_variables['price_drop_minimum']);
            return;
        }

        if ($minimum <= 0 || $minimum >= $cart->site->session_variables['auction_buy_now']) {
            $cart->addError()->addErrorMsg('price_drop_oob', 'Minimum Price Out-Of-Bounds');
            return false;
        }

        //valid, so save data to sessvars
        $cart->site->session_variables['price_drop'] = $price_drop;
        $cart->site->session_variables['price_drop_minimum'] = $minimum;
        return true;
    }

    public function core_listing_placement_processStatusChange($vars)
    {
        $listing = $vars['listing'];
        if ($listing->live != 1) {
            //doing something other than making this listing be live, so do nothing here
            return;
        }
        if ($listing->buy_now_only != 1) {
            //this is not a buy now only auction, so there's nothing for us to do
            return;
        }
        $toggle = $vars['session_variables']['price_drop'];
        if ($toggle != 1) {
            //not enabling Price Drop for this
            return;
        }
        $minimum = $vars['session_variables']['price_drop_minimum'];

        //calculate time of first drop
        $reg = geoAddon::getRegistry($this->name);
        $delay = mt_rand($reg->delay_low * 3600, $reg->delay_high * 3600);
        $nextDrop = geoUtil::time() + $delay;


        $db = DataAccess::getInstance();
        $sql = "INSERT INTO `geodesic_addon_price_drop_auctions` (`listing_id`,`starting_price`,`current_price`,
		`minimum_price`,`last_drop`,`next_drop`) VALUES (?,?,?,?,?,?)";
        $result = $db->Execute($sql, array($listing->id, $listing->buy_now, $listing->buy_now, $minimum, 0, $nextDrop));
    }
}
