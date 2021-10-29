<?php

//addons/bridge/bridges/_template.php
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
## ##    6.0.7-2-gc953682
##
##################################

# Bridge Installation Type - TEMPLATE
# Use this as a starting point for creating a new bridge.  Search/replace "template" with what the file
# name is going to be.

require_once ADDON_DIR . 'bridge/util.php';

class bridge_template extends addon_bridge_util
{
    //What is shown in the admin for the bridge type
    var $name = 'Template';

    //Settings to save, syntax is array ( setting_name => input OR checkbox
    //if input, on the settings page, it will use an input field.  If checkbox,
    //well you get the idea.
    var $settings = array(
        'setting_1' => 'input',
        'setting_2' => 'checkbox'
    );

    //Description & other various info about each setting.
    var $setting_desc = array (
        'setting_1' => array (
                'name' => 'First Setting',
                'desc' => 'This is the <em>Description</em> for the setting, displayed on the settings page right
under the setting name.'
            ),
        'setting_2' => array(
                'name' => 'An on/off setting',
                'desc' => 'Check this box, and whatever value is set to will be set for this setting.  Uncheck it, and the
					setting will be set to an empty string "".',
                'value' => 'some_value', //if checked, what the value will be.
                'checked' => 1 //set this to 1 or 0, for whether the default is to be turned on or off.
            )
    );

    /**
     * This function is called to display additional information about the bridge type.  Special instructions or caveats need to be placed here.
     *
     * @return string The description of this installation type.
     */
    function getDescription()
    {
        $description = '
		This is the template installation type.  Change this text within the file for this installation type.';
        return $description;
    }

    /**
     * This function is called automatically during initialization of the bridge addon.  It should
     * be used to store settings passed, to a local var or something.
     *
     * @param array $settings Associative array, like array (setting_name => setting_value)
     */
    function setSettings($settings)
    {
        //You can use any method to keep track of settings during this session,
        //below is an example of re-using the member variable "$settings" to store
        //the settings, but you can just as easily store them in a var named config or
        //whatever.
        if (!is_array($settings)) {
            return false; //oops, settings are no good
        }
        foreach ($settings as $key => $value) {
            //set each setting, checking to make sure its all good first..
            if (isset($this->settings[$key])) {
                //valid setting, so set that setting.
                $this->settings[$key] = $value;
            }
        }
    }

    /**
     * Optional, function to import users from the bridge install to the local install.
     *
     * @return boolean True if successful, false otherwise.
     */
    function importUsers()
    {
        geoAdmin::m('Fake import run, all users imported from bridge to local.', geoAdmin::NOTICE);
        return true;
    }

    /**
     * Optional, function to export users from the local install, to the bridge install.
     *
     * @return boolean True if successful, false otherwise.
     */
    function exportUsers()
    {
        //get all users in the given range, return array of user data.
        geoAdmin::m('Fake export run, all users exported from local to bridge.', geoAdmin::NOTICE);
        return true;
    }

    /**
     * Optional, function that calculates the number of users that are on the bridge install, but not
     * the local install.
     *
     * @return int
     */
    function importUserCount()
    {
        //pretend there are 5 users on the bridge install that were not found on the local.
        return 5;
    }

    /**
     * Optional, function that calculates the number of users that are on the local install, but not
     * the bridge install.
     *
     * @return int
     */
    function exportUserCount()
    {
        //pretend there are 0 users on the local install that were not found on the bridge.
        return 0;
    }

    /**
     * Optional, function to log a user in.  Optional because logging in a user may not be
     * possible in some circumstances.
     *
     * @param array $vars Associative array containing info about user logging in
     */
    function session_login($vars)
    {
    }

    /**
     * Optional, function to touch a session, in other words update the last active timestamp
     * for the user.
     *
     * @param string $sessionId The session ID, same as what is stored in $_COOKIE['classified_session']
     */
    function session_touch($sessionId)
    {
    }

    /**
     * Optional, function to create a new session.
     *
     * @param string $sessionId The session ID, same as what is stored in $_COOKIE['classified_session']
     */
    function session_create($sessionId)
    {
    }

    /**
     * Optional, function to log a particular user out of the system.
     *
     * @param array $user_info
     */
    function session_logout($user_info)
    {
    }

    /**
     * Optional, function that updates a user's info, stuff like changing password, changing e-mail, etc.
     *
     * @param array $user_info
     */
    function user_edit($user_info)
    {
    }

    /**
     * Optional, function to register a new user in the system.
     *
     * @param unknown_type $vars
     * @return unknown
     */
    function user_register($user_info)
    {
    }

    /**
     * Optional, NOT IMPLEMENTED, Remove a user from the site.  This is not implemented in the
     * software, but if it ever was, this would be the function that would do it at the bridge level.
     *
     * @param array $user_info
     */
    function user_remove($user_info)
    {
    }

    /**
     * Function used in admin, associative array of settings are passed in and the function should
     * test those settings, and return true or false depending on whether they are legit or not.
     *
     * @param array $settings
     * @return boolean
     */
    function test_settings($settings)
    {
        //can even register results, so you can give errors or whatever if you want..
        geoAdmin::m('I\'m sure the settings are just fine.  (CHANGE THIS! FOR DEMONSTRATION ONLY)');

        return true;
    }

    /**
     * Optional, Can over-ride the function from the main util, if you want to do different
     * logging than the main way
     */
    function log($message)
    {
        echo $message; //just echo it out!  Yee haw! (I would suggest taking this function out once
        //your bridge is fully tested, and just let the backend handle logging...
    }
}
