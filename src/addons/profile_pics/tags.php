<?php

//addons/profile_pics/tags.php
/**
 * @author Geodesic Solutions, LLC
 * @package storefront_addon
 */


require_once ADDON_DIR . 'profile_pics/info.php';

class addon_profile_pics_tags extends addon_profile_pics_info
{

    public function show_pic($params, Smarty_Internal_Template $smarty)
    {
        $uid = 0;
        if (isset($params['user']) && $params['user']) {
            //looking for a specific user's profile pic (allow looking by username or ID)
            $user = geoUser::getUser($params['user']);
            $uid = (int)$user->id;
        } elseif (isset($params['listing_id']) && (int)$params['listing_id']) {
            //called as a {listing} tag, so use the seller's pic
            $listing = geoListing::getListing($params['listing_id']);
            if (!$listing) {
                return false;
            }
            $uid = $listing->seller;
        } else {
            //no parameters? then show the current user's pic
            $uid = geoSession::getInstance()->getUserId();
        }

        if (!$uid) {
            //nothing to show
            return;
        }
        $picData = DataAccess::getInstance()->GetOne("SELECT `pic_data` FROM `geodesic_addon_profile_pics` WHERE `user_id` = ?", array($uid));
        if (!$picData || !strlen($picData)) {
            //no saved profile pic? use the default
            $picData = ((defined('IN_ADMIN')) ? '../' : '') . geoTemplate::getUrl('images', 'icons/User-Profile-300.png');
        }
        $tpl_vars['pic_data'] = $picData;
        $tpl_vars['pic_name'] = geoUser::userName($uid);

        $tpl_vars['width'] = (int)$params['width'];
        $tpl_vars['height'] = (int)$params['height'];

        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'show_pic.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
