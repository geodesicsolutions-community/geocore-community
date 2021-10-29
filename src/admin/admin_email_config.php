<?php

// admin_email_config.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

class Email_configuration
{


    var $admin_site;
    var $messages;

    /**
     * Email configuration constructor.  This is responsible for loading the appropriate page, and
     * then running site->display_page().
     */
    function Email_configuration()
    {
        $this->admin_site = Singleton::getInstance('Admin_site');

        //$this->Admin_site($db, $product_configuration);
        $this->messages['error_no_host'] = "Error:  No SMTP host name given.  The host name is required for SMTP connections.  If you are unsure what the SMTP host name is, contact your host provider, or use the \"Standard Connection\". ";

        //This is where you would do any special case loaders or whatever, that get run before the display function gets called.
    } //end of function Site_configuration

    /**
     * Display general settings for email
     */
    function display_email_general_config()
    {
        //Functionality has been moved to the addon.
        $this->admin_site->body .= '<h1>Error: No email addon installed & enabled.</h1>
<p>An email addon needs to be installed and enabled in the addon management, in order for
emails to be sent.</p>';
        $this->admin_site->display_page();
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    public function display_email_notify_config()
    {
        //get the instance of the db.
        $db = DataAccess::getInstance();
        $admin = geoAdmin::getInstance();
        $view = $admin->v();

        //carry over values from old expire setting
        if ($db->get_site_setting("send_ad_expire_email") != 0) {
            $exp = (int)$db->get_site_setting("send_ad_expire_email");
            $exp = $exp * 86400;
            $db->set_site_setting("classified_expire_email", $exp);
            $db->set_site_setting("auction_expire_email", $exp);
            $db->set_site_setting("send_ad_expire_email", 0);
        }

        $tpl_vars = $db->get_site_settings(true);

        $tpl_vars['admin_messages'] = $admin->message();
        $tpl_vars['is_e'] = geoPC::is_ent();
        $tpl_vars['is_order_notify'] = (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic());
        $tpl_vars['is_c'] = geoMaster::is('classifieds');
        $tpl_vars['is_a'] = geoMaster::is('auctions');
        $tpl_vars['is_prem'] = geoPC::is_premier();

        $tooltips = array();
        $tooltips['order'] = geoHTML::showTooltip('Notify on Order Approval', 'When an order has been approved by the admin, or when payment has been received and the order is 
			automatically approved by the system, send a notification to the user.
			<br /><br />
			This will be <em>in addition to</em> any notifications about listings that have been placed.
			<br /><br />
			This is meant as a way to notify the user that their payment for an order has been verified &amp; received.  Because of that,
			the user will <em>only be notified when the order total is greater than $0</em>.');


        $tooltips['flood_contact_seller'] = geoHTML::showTooltip('Prevent Contact Seller email Flooding', 'This is the number of times a user can contact any seller on the site, per hour.  This is to prevent manual spamming of sellers.
		<br /><br />Note that if using the security image addon, it will already prevent communication flooding by bots, this feature is to prevent someone from manually spamming without the use of a <em>bot</em>.
		<br /><br />This is per sender (the person sending the messages to the sellers), per hour.');

        $tpl_vars['tooltips'] = $tooltips;
        //frequency to email seller:
        $tpl_vars['email_expire_frequencies'] = array (
            0 => 'Send only once',
            21600 => 'Every 6 Hours',
            43200 => 'Every 12 Hours',
            86400 => 'Every Day',
            172800 => 'Every 2 Days',
            259200 => 'Every 3 Days',
            604800 => 'Every Week',
            1209600 => 'Every 2 Weeks',
            1814400 => 'Every 3 Weeks',
            2592000 => 'Every 30 Days'
        );

        if (geoAddon::getInstance()->isEnabled('account_balance')) {
            $cron_key = geoString::specialChars($db->get_site_setting('cron_key'));

            $cron_url = substr($db->get_site_setting('classifieds_url'), 0, strpos($db->get_site_setting('classifieds_url'), $db->get_site_setting('classifieds_file_name')));
            $cron_url .= 'cron.php?action=cron&cron_key=' . $cron_key . '&tasks=account_balance:send_negative_account_balance_emails';
            $cron_url .= '&running_now=1';
            $cron_url = "onclick=\"window.open('$cron_url'); return false;\"";
            $tpl_vars['send_balance_reminder_button'] = geoHTML::addButton('Manually Send Emails Now', $cron_url, 1);
            //figure out what current setting is
            $cron = geoCron::getInstance();
            $data = $cron->getTaskInfo('account_balance:send_negative_account_balance_emails');
            if ($data['interval'] == -1) {
                $days = 0;
            } else {
                $days = ceil($data['interval'] / (60 * 60 * 24));
            }
        } else {
            $tpl_vars['send_balance_reminder_button'] = false;
        }
        $tpl_vars['negative_balance_reminder'] = $days;
        $tpl_vars['admin_notice_item_approval'] = $db->get_site_setting('admin_notice_item_approval');

        if (geoMaster::is('auctions')) {
            //final fees due reminder - final_fees_due_reminder
            $cron_key = geoString::specialChars($db->get_site_setting('cron_key'));

            $cron_url = dirname($db->get_site_setting('classifieds_url')) . '/cron.php?action=cron&cron_key=' . $cron_key . '&tasks=send_final_fees_emails';
            $cron_url .= '&running_now=1';

            $tpl_vars['send_final_fees_reminder_link'] = $cron_url;
            //figure out what current setting is
            $cron = geoCron::getInstance();
            $data = $cron->getTaskInfo('send_final_fees_emails');
            if ($data['interval'] == -1) {
                $days = 0;
            } else {
                $days = ceil($data['interval'] / (60 * 60 * 24));
            }
            $tpl_vars['final_fees_due_reminder'] = $days;
        }

        //the notices
        $exp_settings = array ();

        if (geoMaster::is('classifieds')) {
            $exp_settings['classified_expire_email'] = array (
                'label' => 'Classified Expires Soon',
                'page_id' => 52,
            );
        }
        if (geoMaster::is('auctions')) {
            $exp_settings['auction_expire_email'] = array (
                'label' => 'Auction Expires Soon',
                'page_id' => 52,
            );
        }
        $exp_settings['fav_expire_email'] = array (
            'label' => 'Favorite Expires Soon',
            'page_id' => 10212,
        );

        //display in user-friendly format
        $day = $tpl_vars['day'] = 86400;
        $hour = $tpl_vars['hour'] = 3600;
        $minute = $tpl_vars['minute'] = 60;

        foreach ($exp_settings as $setting => $info) {
            $info['exp'] = $exp = (int)$db->get_site_setting($setting);

            $info['timeUnit'] = 1;
            if ($exp >= $day && $exp % $day == 0) {
                $info['timeUnit'] = $day;
            } elseif ($exp >= $hour && $exp % $hour == 0) {
                $info['timeUnit'] = $hour;
            } elseif ($exp >= $minute && $exp % $minute == 0) {
                $info['timeUnit'] = $minute;
            }

            $info['adjustedExpire'] = $exp / $info['timeUnit'];
            $exp_settings[$setting] = $info;
        }
        $tpl_vars['exp_settings'] = $exp_settings;

        //die ('data: <pre>'.print_r($data,1));
        $view->setBodyTpl('email_notifications.tpl')
            ->setBodyVar($tpl_vars);
    }
    /**
     * update general settings for email
     */
    function update_email_general_config()
    {
        return false;
    }
    function update_email_notify_config()
    {
        //get the instance of the db.
        $db = DataAccess::getInstance();
        //set the notify stuff up.
        if (isset($_POST['email_verify_system']) && $_POST['email_verify_system'] == 'admin_approve') {
            $db->set_site_setting('admin_approves_all_registration', 1);
            $db->set_site_setting('use_email_verification_at_registration', false);
            $db->set_site_setting('send_register_attempt_email_admin', 1);
        } else {
            $db->set_site_setting('admin_approves_all_registration', false);
            $reg_verify_system = explode('|', $_POST['email_verify_system']);
            $db->set_site_setting('use_email_verification_at_registration', ($reg_verify_system[0] == 'enabled') ? 1 : false);
            $db->set_site_setting('send_register_attempt_email_admin', ($reg_verify_system[1] == 'enabled') ? 1 : false);
        }


        //$db->set_site_setting('registration_admin_email', isset($_POST['registration_admin_email']) ? $_POST['registration_admin_email'] : '');


        $db->set_site_setting('send_register_complete_email_client', ((isset($_POST['send_register_complete_email_client']) && $_POST['send_register_complete_email_client']) ? 1 : false));
        $db->set_site_setting('send_register_complete_email_admin', ((isset($_POST['send_register_complete_email_admin']) && $_POST['send_register_complete_email_admin']) ? 1 : false));
        $db->set_site_setting('send_admin_placement_email', ((isset($_POST['send_admin_placement_email']) && $_POST['send_admin_placement_email']) ? 1 : false));
        $db->set_site_setting('user_set_hold_email', ((isset($_POST['user_set_hold_email']) && $_POST['user_set_hold_email']) ? 1 : false));
        $db->set_site_setting('send_successful_placement_email', ((isset($_POST['send_successful_placement_email']) && $_POST['send_successful_placement_email']) ? 1 : false));
        $db->set_site_setting('admin_notice_item_approval', ((isset($_POST['admin_notice_item_approval']) && $_POST['admin_notice_item_approval']) ? 1 : false));

        //the notices
        $exp_settings = array ('fav_expire_email');

        if (geoMaster::is('classifieds')) {
            $exp_settings[] = 'classified_expire_email';
        }
        if (geoMaster::is('auctions')) {
            $exp_settings[] = 'auction_expire_email';
        }
        foreach ($exp_settings as $setting) {
            $interval = (isset($_POST[$setting . '_unit'])) ? (int)$_POST[$setting . '_unit'] : 1;
            //make sure it is at least 1
            $interval = max(1, $interval);

            $value = (isset($_POST[$setting])) ? (int)$_POST[$setting] : 0;
            $value = intval($value * $interval);
            $db->set_site_setting($setting, $value);
        }

        $db->set_site_setting('send_ad_expire_frequency', ((isset($_POST['send_ad_expire_frequency'])) ? $_POST['send_ad_expire_frequency'] : 0));
        $db->set_site_setting('subscription_expire_period_notice', ((isset($_POST['subscription_expire_period_notice'])) ? intval($_POST['subscription_expire_period_notice']) : 0));
        $db->set_site_setting('contact_seller_limit', ((isset($_POST['contact_seller_limit']) && $_POST['contact_seller_limit']) ? intval($_POST['contact_seller_limit']) : 0));
        $db->set_site_setting('notify_user_edit_approved', ((isset($_POST['notify_user_edit_approved']) && $_POST['notify_user_edit_approved']) ? 1 : false));
        $db->set_site_setting('notify_user_order_approved', ((isset($_POST['notify_user_order_approved']) && $_POST['notify_user_order_approved']) ? 1 : false));
        if (geoPC::is_ent()) {
            $db->set_site_setting('send_admin_end_email', ((isset($_POST['send_admin_end_email']) && $_POST['send_admin_end_email']) ? 1 : false));
            $db->set_site_setting('admin_email_edit', ((isset($_POST['admin_email_edit']) && $_POST['admin_email_edit']) ? 1 : false));
        }

        $db->set_site_setting('notify_seller_unsuccessful_auction', (($_POST['notify_seller_unsuccessful_auction'] == 1) ? 1 : false));

        //refactor fix to clear old settings.
        //remove once old configuration_table is completly removed.
        $sql = 'UPDATE ' . $db->geoTables->site_configuration_table . ' SET registration_admin_email=\'\', use_email_verification_at_registration=0, send_register_complete_email_client=0, send_register_complete_email_admin=0,
				send_admin_placement_email=0, user_set_hold_email=0, send_successful_placement_email=0, send_ad_expire_email=0, send_ad_expire_frequency=0, subscription_expire_period_notice=0, send_admin_end_email=0,
				admin_approves_all_registration=0, send_register_attempt_email_admin=0, admin_email_edit=0';
        $result = $db->Execute($sql);

        if (geoAddon::getInstance()->isEnabled('account_balance')) {
            //set new interval for negative_balance_reminder
            $cron = geoCron::getInstance();
            $days = intval($_POST['negative_balance_reminder']);
            if (!$days) {
                $interval = -1;
            } else {
                $interval = $days * (60 * 60 * 24);
            }
            $cron->set('account_balance:send_negative_account_balance_emails', 'addon', $interval);
        }
        if (geoMaster::is('auctions')) {
            $cron = geoCron::getInstance();
            $days = (int)$_POST['final_fees_due_reminder'];
            $interval = ($days) ? ($days * (60 * 60 * 24)) : -1;
            $cron->set('send_final_fees_emails', geoCron::TYPE_MAIN, $interval);
        }

        //clear the settings cache
        geoCacheSetting::expire('configuration_data');
        return true;
    }

    function display_sample_sub_page()
    {
        $html = 'This is a sample sub page. Notice how on the side menu, my parents page is highlighted, and the title is auto generated.';
        $this->admin_site->display_page();
    }
    function display_email_config()
    {
        $html = 'This is the category page!  It should probably have links to the sub pages or something...';
        $this->admin_site->display_page();
    }
}
