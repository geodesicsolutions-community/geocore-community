<?php

//addons/enterprise_pricing/setup.php
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

# enterprise_pricing Addon
require_once ADDON_DIR . 'enterprise_pricing/info.php';

class addon_enterprise_pricing_setup extends addon_enterprise_pricing_info
{
    public function install()
    {
        //add the cron to expire groups and plans.
        $cron_add = geoCron::getInstance()->set('enterprise_pricing:expire_groups_and_plans', 'addon', 3600);
        if (!$cron_add) {
            geoAdmin::m('Cron Install Failed.', geoAdmin::ERROR);
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        geoCron::getInstance()->rem('enterprise_pricing:expire_groups_and_plans');
        return true;
    }
}
