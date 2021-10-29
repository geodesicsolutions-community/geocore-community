<?php

//addons/multi_admin/util.php
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
## ##    9c85b30
##
##################################

# multi_admin Addon

class addon_multi_admin_util
{
    var $user_id;
    var $group_id;
    var $display;
    var $update;

    function init($user_id = 0)
    {
        if (is_array($this->display) && !$user_id) {
            //already inited.
            return true;
        }
        //get objects that we need.
        $db = true;
        $session = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $this->user_id = ($user_id) ? $user_id : $session->getUserId();
        $this->display = array();   //start whitelist off as an empty array.
        $this->update = array();
        if ($this->user_id <= 1) {
            //user id 1, this is main admin.
            return false;
        }
        //get data for this user.
        $sql = 'SELECT * FROM `geodesic_addon_multi_admin_users` WHERE `user_id`=' . $this->user_id;
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error('ERROR SQL ADDON: Sql: ' . $sql . ' Err Msg: ' . $db->ErrorMsg());

            return false;
        }
        if ($result->RecordCount() == 0) {
            //user not in db.
            return false;
        }
        $user_data = $result->FetchRow();

        $this->group_id = $user_data['group_id'];

        //Get group data.
        if ($this->group_id > 0) {
            $sql = 'SELECT * FROM `geodesic_addon_multi_admin_groups` WHERE `group_id`=' . $this->group_id;
            $result = $db->Execute($sql);
            if (!$result) {
                trigger_error('ERROR SQL ADDON: Sql: ' . $sql . ' Err Msg: ' . $db->ErrorMsg());
                return false;
            }

            $group_data = $result->FetchRow();
            $this->display = unserialize($group_data['display']);
            $this->update = unserialize($group_data['update']);
        }

        //set permissions from user.
        $this->display = array_merge($this->display, unserialize($user_data['display']));
        $this->update = array_merge($this->update, unserialize($user_data['update']));
    }
    /**
     * If return true, page is allowed to display.  If return false,
     * page is not allowed to display.
     *
     * @param String $page
     */
    function core_auth_admin_display_page($page)
    {
        $this->init();
        //special cases
        if (isset($this->display['SPECIAL_su']) && $this->display['SPECIAL_su']) {
            //super user, can view any page.
            return true;
        }
        if (isset($this->display['SPECIAL_demo']) && $this->display['SPECIAL_demo']) {
            //demo mode, always return true for displaying
            return true;
        }
        return $this->checkAuth($page, $this->display);
    }

    /**
     * If return true, page is allowed to update.  If return false,
     * page is not allowed to update.
     *
     * @param String $page
     */
    function core_auth_admin_update_page($page)
    {
        $this->init();
        //special cases
        if (isset($this->display['SPECIAL_su']) && $this->display['SPECIAL_su']) {
            //super user, can update any page.
            return true;
        }
        if (isset($this->display['SPECIAL_demo']) && $this->display['SPECIAL_demo']) {
            //demo mode, always return false for updating.
            return false;
        }

        return $this->checkAuth($page, $this->update);
    }

    public function core_auth_admin_user_login($vars)
    {
        $userId = $vars['userId'];
        $this->init($userId);

        $return = null;

        if (isset($this->display['SPECIAL_su']) && $this->display['SPECIAL_su']) {
            //super user, can log in as any user.
            $return = true;
        }
        if (isset($this->display['SPECIAL_admin_user_login']) && $this->display['SPECIAL_admin_user_login']) {
            $return = true;
        }

        //since this is checking a specific user, not the current user in the
        //current page load, reset the init details for later core events in this
        //page load
        $this->display = null;

        return $return;
    }

    /**
     * If return true, page is allowed to be added.  If return false,
     * page is not allowed to be added.
     *
     * @param Array $login_data
     */
    function core_auth_admin_login($login_data)
    {
        $this->init($login_data['id']);
        if (is_array($this->display) && count($this->display) > 0) {
            return true;    //user has permission to view at least one page.
        }
        return null;    //This user is not authorized to log in, according to this addon.
                        //But return null, to allow other addons to be able to grant permission
                        //for user to log in.
    }
    function core_auth_listing_edit()
    {
        $this->init();
        if (isset($this->display['SPECIAL_edit_listings_client_side'])) {
            return $this->display['SPECIAL_edit_listings_client_side'];
        }
        if (isset($this->display['SPECIAL_su'])) {
            return $this->display['SPECIAL_su'];
        }
        return null;
    }
    function core_auth_listing_delete()
    {
        $this->init();
        if (isset($this->display['SPECIAL_delete_listings_client_side'])) {
            return $this->display['SPECIAL_delete_listings_client_side'];
        }
        if (isset($this->display['SPECIAL_su'])) {
            return $this->display['SPECIAL_su'];
        }
        return null;
    }
    function checkAuth($page, $auth_array)
    {
        //different parts of authentication broken up to be easier to debug.
        if ($this->user_id == 1) {
            //user id 1 has full access.
            return true;
        }

        if (isset($auth_array[$page])) {
            //Access granted/denied specifically.
            return $auth_array[$page];
        }
        //no rules for this page.  permissions are on white list basis (except for special case of "all"), so
        //deny access.
        return false;
    }
}
