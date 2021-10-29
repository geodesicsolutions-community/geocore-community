<?php

//addons/subscription_pricing/setup.php
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

# subscription_pricing Addon
require_once ADDON_DIR . 'subscription_pricing/info.php';

class addon_subscription_pricing_setup extends addon_subscription_pricing_info
{

    public function install()
    {
        //check to see if a set of subscription-based price plans already exist
        //if not, create them (may need to create ONLY a classifieds or auctions plan if coming from non-GCA)
        $db = DataAccess::getInstance();

        $sql = "SELECT `price_plan_id` FROM " . geoTables::price_plans_table . " WHERE `type_of_billing` = '2' AND `applies_to` = ?";
        $classResult = $db->GetOne($sql, array(1));
        $aucResult = $db->GetOne($sql, array(2));

        $sql = "INSERT INTO " . geoTables::price_plans_table . " (`name`,`description`,`type_of_billing`,`max_ads_allowed`,`applies_to`) VALUES (?,?,?,?,?)";
        $addPlan = $db->Prepare($sql);
        if (!$classResult) {
            //no classified subscription price plan exists -- make one
            $db->Execute($addPlan, array('Default Classifieds Subscription Plan', 'This plan was created automatically by the Subscription Pricing addon.', '2', '1000', '1'));
            geoAdmin::m("No previous Subscription-based Classifieds Price Plan found -- creating one.", geoAdmin::NOTICE);
        }

        if (!$aucResult) {
            //no auction subscription price plan exists -- make one
            $db->Execute($addPlan, array('Default Auctions Subscription Plan', 'This plan was created automatically by the Subscription Pricing addon.', '2', '1000', '2'));
            geoAdmin::m("No previous Subscription-based Auctions Price Plan found -- creating one.", geoAdmin::NOTICE);
        }

        if (!$classResult || !$aucResult) {
            geoAdmin::m("One or more Subscription Price Plans have been automatically created for you. You still need to <a href='index.php?page=pricing_price_plans&mc=pricing'>configure</a> them and then attach them to a User Group for use.", geoAdmin::NOTICE);
        }

        //add the cron to expire subscriptions.
        $cron_add = geoCron::getInstance()->set('subscription_pricing:expire_subscriptions', 'addon', 3600);
        if (!$cron_add) {
            geoAdmin::m('Cron Install Failed.', geoAdmin::ERROR);
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        geoCron::getInstance()->rem('subscription_pricing:expire_subscriptions');
        return true;
    }
}
