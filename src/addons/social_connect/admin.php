<?php

//addons/social_connect/admin.php
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
## ##    17.04.0-3-g481bf8b
##
##################################

# Facebook Connect

require_once ADDON_DIR . 'social_connect/info.php';

class addon_social_connect_admin extends addon_social_connect_info
{
    public function init_pages($menuName)
    {
        //initialize settings page
        menu_page::addonAddPage('addon_FB_settings', '', 'Settings', $this->name, $this->icon_image);
            menu_page::addonAddPage('addon_social_FB_unlink', 'addon_FB_settings', 'Unlink Facebook Profile', $this->name, $this->icon_image, 'sub_page');
    }

    public function _testApp($app_id, $secret)
    {
        $app_id = trim($app_id);
        $secret = trim($secret);
        if (!$app_id || !$secret) {
            //nothing to test
            return;
        }
        //use FB php-sdk library
        require_once ADDON_DIR . 'social_connect/fb.php';

        $fb =  new geoFacebook(array(
            'appId' => $app_id,
            'secret' => $secret,
        ));
        try {
            $test = $fb->api($app_id . '/roles');
            //geoAdmin::m('Result of test call: <pre>'.print_r($test,1).'</pre>',geoAdmin::NOTICE);
            if (isset($test['data'])) {
                geoAdmin::m('Facebook App ID and secret appear to be valid.', geoAdmin::SUCCESS);
            } else {
                geoAdmin::m('Unexpected response when testing the Facebook App ID and secret, double check that the values are set correctly.');
            }
        } catch (FacebookApiException $e) {
            geoAdmin::m('Please double check the Facebook App ID and Facebook App Secret as they may not be set correctly.', geoAdmin::ERROR);
        }
        unset($test);
    }

    public function display_addon_FB_settings()
    {
        $reg = geoAddon::getRegistry($this->name);
        $db = DataAccess::getInstance();

        $app_id = $reg->get('fb_app_id');
        $secret = $reg->get('fb_app_secret');

        $tpl_vars = array (
            'fb_app_id' => $app_id,
            'fb_app_secret' => $secret,
            'admin_msgs' => geoAdmin::m(),
        );
        if (defined('DEMO_MODE')) {
            //demo mode, don't reveal app secret or id
            $tpl_vars['fb_app_id'] = 'DEMO';
            $tpl_vars['fb_app_secret'] = 'DEMO';
        }
        $tpl_vars['default_group'] = $reg->get('default_group');
        $tpl_vars['groups'] = $db->GetAll("SELECT `group_id`, `name`, `default_group` FROM " . geoTables::groups_table . " ORDER BY `default_group`, `group_id`");
        $tpl_vars['fb_logout'] = $reg->get('fb_logout');

        $view = geoView::getInstance();
        $view->setBodyTpl('admin/settings.tpl', $this->name)
            ->setBodyVar($tpl_vars);
    }

    public function update_addon_FB_settings()
    {
        $reg = geoAddon::getRegistry($this->name);

        $fb_app_id = trim($_POST['fb_app_id']);
        $fb_app_secret = trim($_POST['fb_app_secret']);

        if (!$fb_app_id || !$fb_app_secret) {
            geoAdmin::m("Both the app ID and App Secret are required to use Facebook Connect.", geoAdmin::ERROR);
            return false;
        }
        $reg->set('fb_app_id', $fb_app_id);
        $reg->set('fb_app_secret', $fb_app_secret);
        $reg->set('default_group', (int)$_POST['default_group']);
        $reg->set('fb_logout', ((isset($_POST['fb_logout']) && $_POST['fb_logout']) ? 1 : false));
        $reg->save();

        $this->_testApp($fb_app_id, $fb_app_secret);
        //since the test adds it's own messages, add one saying setting saved.
        geoAdmin::m('Settings saved.');

        return true;
    }

    public function display_addon_social_FB_unlink()
    {
        header("Location: index.php?mc=users&page=users_view&b=" . (int)$_POST['user_id']);
        require GEO_BASE_DIR . 'app_bottom.php';
        die('redirecting..');
    }

    public function update_addon_social_FB_unlink()
    {
        $user_id = (int)$_POST['user_id'];
        if ($user_id <= 1) {
            //user ID not specified
            return false;
        }
        $db = DataAccess::getInstance();

        //unset the user
        $db->Execute("UPDATE " . geoTables::logins_table . " SET `facebook_id`='' WHERE `id`=$user_id");
        //unset any fb session junk
        $rows = $db->GetAll("SELECT `classified_session` FROM " . geoTables::session_table . " WHERE `user_id`=$user_id AND `admin_session`='No'");
        foreach ($rows as $row) {
            $db->Execute("DELETE FROM " . geoTables::session_registry . " WHERE `sessions`='{$row['classified_session']}'");
        }
    }

    public function init_text($language_id)
    {
        return array (
            //Tags - login page
            'fb_tag_login_login' => array (
                'name' => 'Login button tag - login link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Login with Facebook'
            ),
            'fb_tag_login_reconnect' => array (
                'name' => 'Login button tag - reconnect link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Reconnect to Facebook'
            ),
            'fb_tag_login_link' => array (
                'name' => 'Login button tag - link account link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Link Account to Facebook'
            ),
            //Login page text
            'login_page_section_title' => array (
                'name' => 'Login page section title',
                'desc' => '',
                'type' => 'input',
                'default' => 'Or Automatically Login With...'
            ),
            'fb_login_page_button' => array (
                'name' => 'Login page button text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Login with Facebook'
            ),
            //Registration page text
            'reg_section_title' => array (
                'name' => 'Registration page section title',
                'desc' => '',
                'type' => 'input',
                'default' => 'Automatically Register with...'
            ),
            'fb_reg_page_button' => array (
                'name' => 'Registration page button text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Login with Facebook'
            ),
            //User info page text
            'fb_usr_info_label' => array (
                'name' => 'User info Facebook Profile label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Facebook Profile'
            ),
            'fb_usr_info_link_button' => array (
                'name' => 'User info Facebook link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Link with Facebook'
            ),

            //edit user info page text
            'fb_usr_info_edit_label' => array (
                'name' => 'Edit User info Facebook Profile label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Facebook Profile'
            ),
            'fb_usr_info_reveal_label' => array (
                'name' => 'Edit User info - show pic on listings label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Show Facebook Profile Pic in Listings'
            ),

            //Merge accounts page
            'fb_merge_page_title' => array (
                'name' => 'User Merge page - title',
                'desc' => '',
                'type' => 'input',
                'default' => 'Link Account to Facebook In Progress...'
            ),
            'fb_merge_page_subtitle' => array (
                'name' => 'User Merge page - subtitle',
                'desc' => '',
                'type' => 'input',
                'default' => 'Facebook Account already Linked'
            ),
            'fb_merge_page_instructions' => array (
                'name' => 'User Merge page - instructions',
                'desc' => '',
                'type' => 'textarea',
                'default' => 'The Facebook account is already linked to another user in the system. You will not be able to continue until you choose to merge the accounts together, cancel the link, or <a href="index.php?a=17" class="mini_button">log out</a>.'
            ),
            'fb_merge_page_section_title' => array (
                'name' => 'User Merge page - Facebook section title',
                'desc' => '',
                'type' => 'input',
                'default' => 'Facebook Account'
            ),
            'fb_merge_page_profile_label' => array (
                'name' => 'User Merge page - Facebook profile label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Facebook Profile:'
            ),
            'fb_merge_page_merge_section_title' => array (
                'name' => 'User Merge page - Merge section title',
                'desc' => '',
                'type' => 'input',
                'default' => 'Merge Accounts'
            ),
            'fb_merge_page_linked_username_label' => array (
                'name' => 'User Merge page - Linked Username Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Already Linked Username:'
            ),
            'fb_merge_page_linked_password_label' => array (
                'name' => 'User Merge page - Linked Password Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Verify Linked Password:'
            ),
            'fb_merge_page_linked_password_value' => array (
                'name' => 'User Merge page - Linked Password Value',
                'desc' => '',
                'type' => 'input',
                'default' => '[Already Verified with Facebook Login]'
            ),
            'fb_merge_page_unlinked_username_label' => array (
                'name' => 'User Merge page - Un-Linked Username Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Non-Linked Username:'
            ),
            'fb_merge_page_unlinked_password_label' => array (
                'name' => 'User Merge page - Un-Linked Password Label',
                'desc' => '',
                'type' => 'input',
                'default' => 'Verify Non-Linked Password:'
            ),
            'fb_merge_page_submit_text' => array (
                'name' => 'User Merge page - submit button text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Merge These 2 Accounts Together'
            ),
            'fb_merge_page_cancel_link_text' => array (
                'name' => 'User Merge page - cancel link text',
                'desc' => '',
                'type' => 'input',
                'default' => 'Cancel Facebook Link'
            ),

            //user management

            //ERRORS
            'error_fb_usr_merge_invalid_pass' => array (
                'name' => 'Invalid password when merging accounts',
                'desc' => '',
                'type' => 'textarea',
                'default' => 'Invalid password specified.  Note that this is your normal login password, NOT your Facebook login password.'
            ),
            'error_fb_merge_internal' => array (
                'name' => 'Internal error when attempting to merge accounts',
                'desc' => '',
                'type' => 'textarea',
                'default' => 'Internal error merging accounts.'
            ),
        );
    }
}
