<?php

//addons/account_balance/setup.php


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
