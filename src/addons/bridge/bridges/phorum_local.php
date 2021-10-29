<?php

//addons/bridge/bridges/phorum_local.php
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
## ##    f6acdc3
##
##################################

# Bridge Installation Type - Phorum

require_once ADDON_DIR . 'bridge/util.php';

class bridge_phorum_local extends addon_bridge_util
{
    //What is shown in the admin for the bridge type
    var $name = 'Phorum';

    var $table_prefix;
    var $config_path;

    var $settings = array(
        'config_path' => 'input',
    );
    var $setting_desc = array (
        'config_path' => array (
                'name' => 'Config path',
                'desc' => 'Server path to config.php
				Absolute path to the Phorum config.php file, example would be /absolute/path/to/phorum/includes/db/config.php. '
            ),
    );
    var $user_info = array(); //user info for logging in and stuff
    var $old_user_data = array(); //used for updating user
    var $install_settings; //used to store the bridge's settings...

    function getDescription()
    {
        //tested on versions 5.2.5 and 5.2.6 but don't need to list both...  maybe once there are more in the list we can do 5.2.5-5.2.10 or whatever.  or maybe not.
        $description = "
		<div class='page_note'>This bridge allows for users to be shared <strong>locally</strong> between <em>Geodesic 3.1+</em> and <strong><em>{$this->name} version 5.2.6</em></strong>.</div>
		
		<div class='page_note'>The bridge may be compatible with other versions of {$this->name}, but it has only been tested on the version(s) listed above.  If you have a previous
		version of {$this->name}, we recommend you update to the latest stable release.</div>
		<div class='page_note'><strong>Caveats/Important Notes:</strong><br />
		<ul>
			<li>
				The {$this->name} installation must reside on the <strong>same domain name</strong> for login and logout to work properly between installations.
			</li>
			<li>Since this is a <em>local bridge</em>, the {$this->name} installation must be on the <strong>same server as the Geo installation.</strong>
			</li>
			<li>To ensure best compatibility, make sure {$this->name} is updated to one of the versions listed above, as those are the versions we
				have tested with.
			</li>
			<li>Currently, this is a one-way bridge, meaning user creation and user detail changes made in the Geo installation will reflect in {$this->name}, but not the other way around.
				As such, we recommend that you force users to register or edit user details in the Geo installation, by <strong>turning off registration, and ability to change the username, passwords, or e-mail address within {$this->name}.</strong>
			</li>
			<li>There is currently no user import for this bridge.  That means that existing users in {$this->name} will need to be manually created in the Geo
				installation.  Existing users in the Geo installation may be exported to {$this->name} using the export tool.
			</li>
		</ul></div>";

        return ($description);
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
    //Not Implemented
    /*
    function importUsers(){

        return true;
    }
    */

    /**
     * Optional, function to export users from the local install, to the bridge install.
     *
     * @return boolean True if successful, false otherwise.
     */
    function exportUsers($users_data)
    {
        //get all users in the given range, return array of user data.
        $db =& DataAccess::getInstance();
        $local_sql = "SELECT *
					FROM geodesic_userdata
					JOIN geodesic_logins ON geodesic_userdata.id = geodesic_logins.id
					WHERE geodesic_userdata.id !=1 AND geodesic_logins.status = 1";
        $users_data = $db->getall($local_sql);
        $db->close();

        if (!empty($users_data)) {
            $g = 0;
            $b = 0;
            $keys = array_keys($users_data);
            foreach ($keys as $key) {
                if ($this->user_register($users_data[$key], false)) {
                    $g++;
                } else {
                    $b++;
                }
            }
            //If Import is ever imlemented, will need to change this or it will break when importing and exporting
            //at same time.
            $this->cleanUp();
            $admin = true;
            include GEO_BASE_DIR . 'get_common_vars.php';

            if ($g || $b) {
                $s = ($g == 1) ? '' : 's';
                $message = "$g User$s successfully exported to {$this->name}";
                if ($b > 0) {
                    $s = ($b > 1) ? 's' : '';
                    $message .= "<br />$b User$s not exported, probably because the user$s already existed in {$this->name}.";
                }
                $admin->userNotice($message);
            } else {
                $admin->userNotice('No users were found to export.');
            }
            return true;
        }
    }

    /**
     * Optional, function that calculates the number of users that are on the bridge install, but not
     * the local install.
     *
     * @return int
     */
    //Not Implemented
    /*
    function importUserCount(){
        //pretend there are 5 users on the bridge install that were not found on the local.
        return 5;
    }
    */

    /**
     * Optional, function that calculates the number of users that are on the local install, but not
     * the bridge install.
     *
     * @return int
     */
    //Not Implemented
    /*
    function exportUserCount(){
        //pretend there are 0 users on the local install that were not found on the bridge.
        return 0;
    }
    */

    /**
     * Optional, function to log a user in.  Optional because logging in a user may not be
     * possible in some circumstances.
     *
     * @param array $vars Associative array containing info about user logging in
     */
    function session_login($vars, $cleanup = true)
    {
        if (!$this->initPhorum()) {
            return false;
        }
        $PHORUM['use_cookies'] =  PHORUM_NO_COOKIES;
        // PHORUM_USE_COOKIES or PHORUM_REQUIRE_COOKIES.
        $PHORUM["DATA"]["ADMINISTRATOR"] = false;

        $user_id = phorum_api_user_authenticate(PHORUM_FORUM_SESSION, $vars['username'], $vars['password']);
        if ($user_id && phorum_api_user_set_active_user(PHORUM_FORUM_SESSION, $user_id) && phorum_api_user_session_create(PHORUM_FORUM_SESSION)) {
            if ($cleanup) {
                $this->cleanUp();
            }
            return true;
        }
        if ($cleanup) {
            $this->cleanUp();
        }
        return false;
    }

    /**
     * Optional, function to log a particular user out of the system.
     *
     * @param array $user_info
     */
    function session_logout($user_info, $cleanup = true)
    {
        if (!$this->initPhorum()) {
            return false;
        }
        phorum_api_user_session_destroy(PHORUM_FORUM_SESSION);
        if ($cleanup) {
            $this->cleanUp();
        }
    }

    /**
     * Optional, function that updates a user's info, stuff like changing password, changing e-mail, etc.
     *
     * @param array $user_info
     */
    function user_edit($user_info, $cleanup = true)
    {
        if (!$this->initPhorum()) {
            return false;
        }

        $user_id = phorum_api_user_search('username', $user_info['old_username']);
        //$user_id = phorum_api_user_authenticate(PHORUM_FORUM_SESSION, $user_info['old_username'], $user_info['old_password']);
        if (!$user_id || !is_numeric($user_id)) {
            $this->log('Error: could not get the Phorum user-id for username ' . $user_info['old_username'] . ' - so could not update user info.', 1);
            return false;
        }

        $user_info['user_id'] = $user_id;
        $user_info['real_name'] = $user_info['firstname'] . ' ' . $user_info['lastname'];
        $keys = array_keys($user_info);
        foreach ($keys as $key) {
            if (!isset($GLOBALS['PHORUM']['API']['user_fields'][$key])) {
                unset($user_info[$key]);
            }
        }
        phorum_api_user_save($user_info);
        if ($cleanup) {
            $this->cleanUp();
        }
    }

    /**
     * use session touch to test other events, since session touch is
     * executed on every page load no matter what.
     *
     */
    //un-comment to test registration of a new user on each page load
/*  function session_touch()
    {
        //simulate a user registration.  (for testing)
        $number = rand(1,1000);

        $myarray['email'] = 'test'.$number.'_@carlos.geo';
        $myarray['email2'] =  '';
        $myarray['email_verifier'] = "test'.$number.'_@carlos.geo";
        $myarray['email_verifier2'] = '';
        $myarray['username'] = 'test'.$number.'_';//'test27_';
        $myarray['password'] = 'test'.$number.'_';
        $myarray['agreement'] = 'yes';
        $myarray['company_name'] = 'test'.$number;
        $myarray['business_type'] = '1';
        $myarray['firstname'] = 'test'.$number;
        $myarray['lastname'] = 'test'.$number;
        $myarray['address'] = 'test'.$number;
        $myarray['address_2'] = 'test'.$number;
        $myarray['city'] = 'test'.$number;
        $myarray['state'] = 'MS';
        $myarray['country'] = 'United States';
        $myarray['zip'] = 'test27_';
        $myarray['phone'] = ' test27_';
        $myarray['phone_2'] = 'test27_';
        $myarray['fax'] = 'test27_';
        $myarray['url'] = 'carlos.geo';


        $this->user_register($myarray);
    }
    */


    /**
     * Optional, function to register a new user in the system.
     *
     * @param unknown_type $vars
     * @return unknown
     */
    function user_register($user_info, $also_login = true)
    {
        if (!$this->initPhorum()) {
            return false;
        }
        $username = phorum_api_user_search('username', $user_info['username']);

        if ($username) {
            //we don't need existing users.
            return false;
        }

        //format user info
        $user_info['user_id'] = null;
        $user_info['real_name'] = $user_info['firstname'] . ' ' . $user_info['lastname'];
        $user_info['active'] = 1;
        //echo '<pre>';print_r($user_info);
        $keys = array_keys($user_info);
        foreach ($keys as $key) {
            if (!isset($GLOBALS['PHORUM']['API']['user_fields'][$key])) {
                unset($user_info[$key]);
            }
        }

        if (phorum_api_user_save($user_info)) {
            if ($also_login) {
                $this->session_login($user_info, false);
                $this->cleanUp();
            }

            return true;
        }
        if ($also_login) {
            $this->cleanUp();
        }
        return false;
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
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        if (file_exists($this->settings['config_path'])) {
            $admin->userSuccess('Configuration file Successfully loaded');

            if ($this->initPhorum()) {
                $admin->userSuccess('Successful Connection to ' . $this->name . ' Bridge\'s interface.');
                $result = true;
            } else {
                $admin->userNotice('Problems connecting to ' . $this->name . ' interface. Check that the config file is correct, and that you are using the latest version of ' . $this->name . '(tested on version 5.2.5+).');
            }
        } else {
            $admin->userNotice('Phorum: Configuration file path specified was not found. Please check your settings.');
            //Note: may also mean that file permissions are set so that we do not have permission to access file...
        }
        //run cleanup to fix the directory
        $this->cleanUp();
        return $result;
    }

    //Bridge connection functions...
    /**
     * clean up the bridge connection and the main db connection.
     */
    function cleanUp()
    {
        //first disconnect bridge if it's still connected
        if (isset($this->db) && $this->db) {
            //close it
            mysql_close($this->db);
        }
        if (defined('IN_ADMIN')) {
            chdir(ADMIN_DIR);
        } else {
            chdir(GEO_BASE_DIR);
        }
        return true;
    }

    function initPhorum()
    {
        //make sure we are not already connected...
        if ($this->isConnected() || defined('PHORUM')) {
            return true;
        }
        //first, close connection to our own db
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $db->Close();

        //INIT PHORUM
        $this->config_path = $this->settings['config_path'];
        $ph_sessionID = $this->user_info["username"] . ":" . $phorumpassword;
        //cookie will be set for the present domain
        if ($this->config_path != '') {
            $cookie_path = dirname($this->config_path);
            $cookie_path = dirname($cookie_path);
            $cookie_path = dirname($cookie_path) . '/';
        } else {
            $cookie_path = '/';
        }
        chdir($cookie_path);
        $GLOBALS['PHORUM'] = array();
        //phorum has it's own even handler, so we have to suppress output
        //because it ends up spewing PHP notices all over the page
        ob_start();
        require_once $cookie_path . 'common.php';
        require_once $cookie_path . 'include/api/base.php';
        require_once $cookie_path . 'include/api/user.php';
        ob_end_clean();
        //Restore the Geo error handler so we can continue to do debugging the
        //Geo way
        if (function_exists('customErrorHandler')) {
            set_error_handler('customErrorHandler');
        } else {
            //must be the changed name
            set_error_handler('geo_default_debug_error_handler');
        }
        $this->db =& phorum_db_interact(DB_RETURN_CONN);
        //END INIT PHORUM
        return true;
    }

    function isConnected()
    {
        if (defined('PHORUM') && isset($GLOBALS['PHORUM']) && is_array($GLOBALS['PHORUM'])) {
            return true;
        }
        return false;
    }
}
