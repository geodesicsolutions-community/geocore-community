<?php

//addons/account_balance/setup.php
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

# account_balance Addon
require_once ADDON_DIR . 'account_balance/info.php';

class addon_account_balance_setup extends addon_account_balance_info
{
    public function install()
    {
        //add the cron to send negative balance emails.
        $cron_add = geoCron::getInstance()->set('account_balance:send_negative_account_balance_emails', 'addon', 2592000);
        if (!$cron_add) {
            geoAdmin::m('Cron Install Failed.', geoAdmin::ERROR);
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        geoCron::getInstance()->rem('account_balance:send_negative_account_balance_emails');
        return true;
    }
}
