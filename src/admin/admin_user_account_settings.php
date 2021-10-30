<?php

// admin_user_account_settings.php


/**
 * Switches, toggles, and the like for options relating to the User Account Home Page.
 *
 *
 * @package geo_admin
 */

class admin_user_account_settings extends Admin_site
{

    public function display_user_account_settings()
    {
        $db = DataAccess::getInstance();
        $admin = geoAdmin::getInstance();
        $view = $admin->v();
        $view->admin_messages = $admin->message();
        $settings = $tooltips = array();

        $settings['post_login_page'] = $db->get_site_setting('post_login_page') ? $db->get_site_setting('post_login_page') : 0;
        $settings['post_login_url'] = $db->get_site_setting('post_login_url') ? $db->get_site_setting('post_login_url') : '';
        $tooltips['post_login_page'] = geoHTML::showTooltip('Post-Login Landing Page', 'Choose the page you would like to appear once a user has successfully logged in.');


        $settings['my_account_table_rows'] = ($db->get_site_setting('my_account_table_rows')) ? $db->get_site_setting('my_account_table_rows') : 5;
        $tooltips['my_account_table_rows'] = geoHTML::showTooltip('Number of rows per table', 'Choose the maximum number of rows you would like to appear in information tables on the My Account Page.');

        $settings['show_addon_icons'] = $db->get_site_setting('show_addon_icons') ? $db->get_site_setting('show_addon_icons') : 0;
        $tooltips['show_addon_icons'] = geoHTML::showTooltip('Show icons for addons in "My Account Links"', 'The icons that appear alongside entires for addons in the My Account Links module are disabled by default. You can turn them on here.');

        //New Messages box
        $boxes = array();
        $boxes['new_messages'] = array('name' => 'New Messages', 'setting' => $db->get_site_setting('my_account_show_new_messages'));

        //Account Balance box
        //(only show if using Account Balance)
        //TODO: move this code into the AB-gateway file?
        $gateway = geoPaymentGateway::getPaymentGateway('account_balance');
        if ($gateway && $gateway->getEnabled()) {
            $boxes['account_balance'] = array('name' => 'Account Balance', 'setting' => $db->get_site_setting('my_account_show_account_balance'));
        }

        //Auctions box
        if (geoMaster::is('auctions')) {
            $boxes['auctions'] = array('name' => 'Auctions', 'setting' => $db->get_site_setting('my_account_show_auctions'));
        }

        //Classifieds box
        if (geoMaster::is('classifieds')) {
            $boxes['classifieds'] = array('name' => 'Classifieds', 'setting' => $db->get_site_setting('my_account_show_classifieds'));
        }

        //Recently sold box
        //available in all Auctions, but only Enterprise Classifieds
        if (geoMaster::is('auctions') || geoPC::is_ent()) {
            $settings['my_account_recently_sold_time'] = $db->get_site_setting('my_account_recently_sold_time');
            $boxes['recently_sold'] = array('name' => 'Recently Sold', 'setting' => $db->get_site_setting('my_account_show_recently_sold'));
        }

        if (geoAddon::getInstance()->isEnabled('storefront')) {
            $reg = geoAddon::getRegistry('storefront');
            $setting = $reg->get('my_account_show_storefront', 1);
            $boxes['storefront'] = array('name' => 'Storefront', 'setting' => $setting);
        }

        $tpl_vars = array();
        $tpl_vars['verify_accounts'] = $db->get_site_setting('verify_accounts');
        $tpl_vars['nonverified_require_approval'] = $db->get_site_setting('nonverified_require_approval');
        $tpl_vars['auto_verify_with_payment'] = $db->get_site_setting('auto_verify_with_payment');

        $tpl_vars['user_rating_low_threshold'] = $db->get_site_setting('user_rating_low_threshold') ? $db->get_site_setting('user_rating_low_threshold') : 0;
        $tpl_vars['user_rating_low_notify_user'] = $db->get_site_setting('user_rating_low_notify_user') ? 1 : 0;
        $tpl_vars['user_rating_low_notify_admin'] = $db->get_site_setting('user_rating_low_notify_admin') ? 1 : 0;

        $view->boxes = $boxes;
        $view->tooltips = $tooltips;
        $view->settings = $settings;
        $view->setBodyTpl('user_account_settings.tpl')
            ->setBodyVar($tpl_vars);
        return true;
    }


    public function update_user_account_settings()
    {
        $db = DataAccess::getInstance();
        $settings = $_POST['b'];

        //save verify user stuff
        $this->db->set_site_setting('verify_accounts', ((isset($_POST['verify_accounts']) && $_POST['verify_accounts']) ? 1 : false));
        $this->db->set_site_setting('nonverified_require_approval', ((isset($_POST['nonverified_require_approval']) && $_POST['nonverified_require_approval']) ? 1 : false));
        $this->db->set_site_setting('auto_verify_with_payment', ((isset($_POST['auto_verify_with_payment']) && $_POST['auto_verify_with_payment']) ? 1 : false));

        //save main settings
        if (!$this->db->set_site_setting("my_account_table_rows", $settings["my_account_table_rows"])) {
            return false;
        }
        if (!$this->db->set_site_setting("show_addon_icons", $settings["show_addon_icons"])) {
            return false;
        }

        if (!$this->db->set_site_setting("post_login_page", $settings["post_login_page"])) {
            return false;
        }
        //only save post url if using the custom landing page setting
        $post_url = ($settings['post_login_page']  == 2) ? $settings['post_login_url'] : '';
        if (!$this->db->set_site_setting("post_login_url", $post_url)) {
            return false;
        }

        //save box toggles
        $setting = $settings["my_account_show_new_messages"] ? 1 : 0;
        if (!$this->db->set_site_setting("my_account_show_new_messages", $setting)) {
            return false;
        }

        $gateway = geoPaymentGateway::getPaymentGateway('account_balance');
        $setting = ($gateway && $gateway->getEnabled() && $settings["my_account_show_account_balance"]) ? 1 : 0;
        if (!$this->db->set_site_setting("my_account_show_account_balance", $setting)) {
            return false;
        }

        $setting = (geoMaster::is('auctions') && $settings["my_account_show_auctions"]) ? 1 : 0;
        if (!$this->db->set_site_setting("my_account_show_auctions", $setting)) {
            return false;
        }

        $setting = (geoMaster::is('classifieds') && $settings["my_account_show_classifieds"]) ? 1 : 0;
        if (!$this->db->set_site_setting("my_account_show_classifieds", $setting)) {
            return false;
        }

        $setting = ((geoMaster::is('auctions') || geoPC::is_ent()) && $settings["my_account_show_recently_sold"]) ? 1 : 0;
        if (!$this->db->set_site_setting("my_account_show_recently_sold", $setting)) {
            return false;
        }
        $setting = ((geoMaster::is('auctions') || geoPC::is_ent()) && $settings["my_account_recently_sold_time"]) ? $settings["my_account_recently_sold_time"] : 30;
        if (!$this->db->set_site_setting("my_account_recently_sold_time", $setting)) {
            return false;
        }

        $threshold = round($settings['user_rating_low_threshold'], 2);
        $threshold = ($threshold < 1 || $threshold > 5) ? 0 : $threshold;
        if (!$this->db->set_site_setting('user_rating_low_threshold', $threshold)) {
            return false;
        }
        if (!$this->db->set_site_setting('user_rating_low_notify_user', ((isset($settings['user_rating_low_notify_user']) && $settings['user_rating_low_notify_user']) ? 1 : false))) {
            return false;
        }
        if (!$this->db->set_site_setting('user_rating_low_notify_admin', ((isset($settings['user_rating_low_notify_admin']) && $settings['user_rating_low_notify_admin']) ? 1 : false))) {
            return false;
        }

        if (geoAddon::getInstance()->isEnabled('storefront')) {
            $setting = $settings['my_account_show_storefront'] ? 1 : 0;
            $reg = geoAddon::getRegistry('storefront');
            $reg->set('my_account_show_storefront', $setting);
            $reg->save();
        }

        return true;
    }
}
