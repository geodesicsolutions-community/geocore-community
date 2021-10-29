<?php

//addons/subscription_pricing/info.php
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
##    7.5.3-36-gea36ae7
##
##################################

# Subscription Pricing
class addon_subscription_pricing_util extends addon_subscription_pricing_info
{
    /**
     * Call this function to see if a user should be forced into the "buy subscription" process, and then if yes, force the user there
     */
    public function tryForceSubscriptionBuy($forUser = 0)
    {
        if (defined('IN_ADMIN')) {
            //in the admin, so don't do anything
            return;
        }
        $db = DataAccess::getInstance();
        $skipForceCheckForA = array(10, 17, 'cart');
        if (stripos(getenv('SCRIPT_NAME'), $db->get_site_setting('classifieds_file_name')) !== false && !isset($_GET['a']) || in_array($_GET['a'], $skipForceCheckForA)) {
            //never force subscription on login, logout, home, or cart pages
            return;
        }

        if (stripos(getenv('SCRIPT_NAME'), 'transaction_process') !== false || strpos(getenv('SCRIPT_NAME'), 'AJAX.php')) {
            //this is one of the transaction processing files, or an AJAX call. don't do redirect stuff here
            return;
        }

        $reg = geoAddon::getRegistry('subscription_pricing');
        if (!($reg && $reg->require_sub_all_users)) {
            //either couldn't get the registry, or did and the redirect setting is off. nothing more to do here.
            return;
        }

        if ($forUser) {
            //input specified which user we're looking at (likely a new registration who may not be fully logged in yet)
            $user_id = (int)$forUser;
        } else {
            $session = geoSession::getInstance();
            $session->initSession(); //this is usually called pretty early on in the page, so make sure the session is initialized and all that jazz
            $user_id = $session->getUserId();
        }

        if (!$user_id || $user_id <= 1) {
            //not a valid user, or this is the admin
            return;
        }

        //check the "type" of this user's price plans
        $db = DataAccess::getInstance();
        $classType = $db->GetOne('SELECT `type_of_billing` FROM ' . geoTables::price_plans_table . ' WHERE `price_plan_id` = ?', array(geoUser::getData($user_id, 'price_plan_id')));
        $aucType = $db->GetOne('SELECT `type_of_billing` FROM ' . geoTables::price_plans_table . ' WHERE `price_plan_id` = ?', array(geoUser::getData($user_id, 'auction_price_plan_id')));
        if ($classType == 2 || $aucType == 2) {
            //OK, this *is* a subscription-based user, and if we've gotten this far, it MUST have an active subscription to proceed on this page
            $expires = $db->GetOne("SELECT `subscription_expire` FROM " . geoTables::user_subscriptions_table . " WHERE `user_id` = ?", array($user_id));
            if (!$expires || $expires < geoUtil::time()) {
                //user does not have a subscription, or subscription is expired. redirect to Extend Subscription page
                header('Location: ' . $db->get_site_setting('classifieds_url') . '?a=cart&action=new&main_type=subscription');
                include_once GEO_BASE_DIR . 'app_bottom.php';
                exit();
            }
        }
        //if here, NOT doing the redirect. proceed as normal.
    }
}
