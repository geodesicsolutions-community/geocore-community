<?php

//addons/twitter_feed/tags.php
/**
 * @author Geodesic Solutions, LLC
 * @package storefront_addon
 */

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
## ##    16.05.0-17-g5113ae1
##
##################################

class addon_twitter_feed_tags extends addon_twitter_feed_info
{

    public function show_feed_auto_add_head()
    {
        geoView::getInstance()->addCssFile(geoTemplate::getUrl('css', 'addon/twitter_feed/twitter_feed.css'));
    }

    public function show_feed($params, Smarty_Internal_Template $smarty)
    {

        $listingId = (isset($params['listing_id'])) ? (int)$params['listing_id'] : 0;

        if (!$listingId) {
            //allow working as a normal {addon} tag
            $view = geoView::getInstance();

            $listingId = (int)$view->classified_id;
        }
        if (!$listingId) {
            //something wrong
            return '';
        }

        $reg = geoAddon::getRegistry('twitter_feed');
        $config = $reg->config;

        $db = DataAccess::getInstance();
        $sql = "SELECT `href`, `data_id` FROM `geodesic_addon_twitter_feed_timelines` WHERE `active` = 1 AND `listing_id` = ?";
        $result = $db->GetRow($sql, array($listingId));
        if ($result) {
            $href = geoString::fromDB($result['href']);
            $data_id = $result['data_id'];
        }
        if (!$href) {
            //nothing to show for this listing. see if a site default timeline exists
            if ($config['default_href']) {
                $href = $config['default_href'];
            } else {
                //no default -- don't show the timeline
                return '';
            }

            if ($config['default_data_id']) {
                $data_id = $config['default_data_id'];
            } else {
                //we no longer fail without data_id
            }
        }

        $tpl_vars = array ();

        $tpl_vars['href'] = $href;
        $tpl_vars['data_id'] = $data_id;
        $tpl_vars['config'] = $config;

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'twitter_feed.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
