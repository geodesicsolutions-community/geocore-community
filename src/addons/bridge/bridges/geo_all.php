<?php

//addons/bridge/bridges/geo_all.php
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
## ##    7.6.3-54-g2a04c6e
##
##################################

# Bridge Installation Type - Used for connecting to any Geo product
# 3.1 or higher, to share users.
require_once ADDON_DIR . 'bridge/util.php';
class bridge_geo_all extends addon_bridge_util
{
    var $name = 'Geo Software 3.1+';
    var $settings = array(
        'db_host' => 'input',
        'db_user' => 'input',
        'db_pass' => 'input',
        'db_name' => 'input',
        'db_strict' => 'checkbox',
        'cookie_expire' => 'input',
        'cookie_domain' => 'input',
        'cookie_path' => 'input',
    );
    var $setting_desc = array (
        'db_host' => array (
                'name' => 'Database Host',
                'desc' => 'DB Host name for bridged software, usually <em>localhost</em>'
            ),
        'db_user' => array (
                'name' => 'Database Username',
                'desc' => 'DB Username for bridged software'
            ),
        'db_pass' => array (
                'name' => 'Database Password',
                'desc' => 'DB Password for bridged software'
            ),
        'db_name' => array (
                'name' => 'Database Name',
                'desc' => 'DB Name for bridged software'
            ),
        'db_strict' => array(
                'name' => 'MySQL Strict Mode',
                'desc' => 'Check this box if MySQL Strict mode is turned on by default. (Leave checked if not sure)',
                'value' => '1',
                'checked' => 1 //default to be turned on
            ),
        'cookie_expire' => array (
                'name' => 'Cookie Expiration',
                'desc' => 'Amount of time in seconds, before login cookie expires (set to 0 to expire when browser is closed)',
                'value' => '259200'
            ),
        'cookie_domain' => array (
                'name' => 'Cookie Domain',
                'desc' => 'Domain to use when setting the login cookie. (leave blank if not sure what to use)'
            ),
        'cookie_path' => array (
                'name' => 'Cookie Path',
                'desc' => 'Path to use when setting the login cookie. (leave blank if not sure what to use)'
            )

    );
    var $db; //connection to bridge db
    var $use_local_db = false;
    var $user_info = array(); //user info for logging in and stuff
    var $old_user_data = array(); //used for updating user
    var $install_settings; //used to store the bridge's settings...

    function getDescription()
    {
        $description = '
		<div class="page_note">This bridge allows for users to be shared <strong>locally</strong> between this <em>Geodesic 3.1+</em> installation, and other <em>Geodesic 3.1+</em> installations.</div>
		<div class="page_note">
		<strong>Caveats/Important Notes:</strong><br />
		<ul>
			<li>
				The other Geo installation(s) must reside on the <strong>same domain name</strong> for login and logout to work properly between installations.
			</li>
			<li>Since this is a <em>local bridge</em>, the other Geo installation(s) must be on the <strong>same server as this Geo installation.</strong>
			</li>
			<li>You must update any Geo installations to 3.1 that you want to bridge with this one.
				This bridge is <strong>not compatible with versions before Geo 3.1.0</strong>, if you attempt to bridge to earlier versions before 3.1.0 you can get errors in the installation\'s database.
			</li>
			<li>On every 3.1 Geo installation you want bridged, you need to create a bridge to each of the other Geo 3.1 installations.
				Otherwise, new users or changes to users made the other installation will not be shared with this one.
			</li>
			<li>The Bridge addon does not work the same way as the old Geo API Addon did.  <strong>Do Not create a bridge from an installation to itself</strong>
				Creating a bridge from an installation to itself is the equivelent of creating a normal "road bridge" from one side of the river, back to the same side.
				<strong>You only need to bridge to <em>other</em> Geo installations, you do not create a bridge back to itself.</strong>
			</li>
			<li>If you have a lot of installations, to avoid setting up the same bridge installations over and over, you can manually copy the table <em>geodesic_bridge_installations</em> from the first database to the other databases.  Then, on each installation, disable the bridge to itself (see note above).
				As always, be sure you back up all databases before making manual changes to them.
			</li>
		</ul></div>';
        return $description;
    }
    function setSettings($settings)
    {
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
        //set the user id to 0 to start..
        $this->user_info['id'] = (isset($this->user_info['id'])) ? $this->user_info['id'] : 0;
    }

    function importUsers()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        if (!$this->connect()) {
            $admin->userError('Error getting users from Bridge installation.');
            return false;
        }

        //determine what page we are on.
        $import_step = (isset($_GET['import_step'])) ? intval($_GET['import_step']) : 0;
        $users_at_once = 100;

        $start = ($import_step * $users_at_once);
        $next_meta = '';
        if (!define('GEO_USE_META')) {
            define('GEO_USE_META', 1);
            //$next_meta = '<meta http-equiv="refresh" content="5;url=index.php?page=bridge_sync&amp;import_step='.($import_step+1).'">';
        }
        //Get uses from bridge DB

        $sql = 'SELECT * FROM `geodesic_userdata` as ud,`geodesic_logins` as login WHERE ud.id = login.id AND ud.id != 1 AND ud.username = login.username';// LIMIT '.$start.', '.$users_at_once;
        $bridge_result = $this->db->Execute($sql);
        if (!$bridge_result) {
            $admin->userError('DB Error, please try again.  Debug: ' . $this->db->ErrorMsg());
            return false;
        }
        //$admin_page_loader->userNotice('Number of users going to sync from Bridge Install to Local: '.$bridge_result->RecordCount());
        $this->ignore_critical_logs = true;
        $this->cleanUp();
        $this->use_local_db = true;
        $this->connect();//connect to local connection
        $g = $f = 0;
        while ($user_data = $bridge_result->FetchRow()) {
            if ($this->user_register($user_data, false)) {
                $g++;
            } else {
                $f++;
            }
            //$admin_page_loader->userNotice('Attempted to sync user: '.$user_data['username'].'( '.$user_data['id'].' )');
        }
        $t = $g + $f;
        $admin->userNotice("Just imported $g of $t users.$next_meta");
        $this->use_local_db = false; //switch it back
        $this->cleanUp();

        $this->ignore_critical_log = false;

        return true;
    }

    function exportUsers()
    {
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $this->use_local_db = true;
        $this->cleanUp();
        if (!$this->connect()) {
            $admin->userError('Error getting users from Bridge installation.');
            return false;
        }

        //determine what page we are on.
        $import_step = (isset($_GET['export_step'])) ? intval($_GET['export_step']) : 0;
        $users_at_once = 100;

        $start = ($import_step * $users_at_once);
        $next_meta = '';
        if (!define('GEO_USE_META')) {
            define('GEO_USE_META', 1);
            //$next_meta = '<meta http-equiv="refresh" content="5;url=index.php?page=bridge_sync&amp;export_step='.($import_step+1).'&amp;install_id='.$_GET['install_id'].'">';
        }

        //Get uses from bridge DB

        $sql = 'SELECT * FROM `geodesic_userdata` as ud,`geodesic_logins` as login WHERE ud.id = login.id AND ud.id != 1 AND ud.username = login.username';// LIMIT '.$start.', '.$users_at_once;
        $bridge_result = $this->db->Execute($sql);
        if (!$bridge_result) {
            $admin->userError('DB Error, please try again.  Debug: ' . $this->db->ErrorMsg());
            return false;
        }
        //$admin->userNotice('Number of users going to sync from Local install to Bridge: '.$bridge_result->RecordCount());
        $this->ignore_critical_logs = true;
        $this->cleanUp();
        $this->use_local_db = false;

        $g = $f = 0;
        while ($user_data = $bridge_result->FetchRow()) {
            if ($this->user_register($user_data, false)) {
                $g++;
            } else {
                $f++;
            }
            //$admin->userNotice('Attempted to sync user: '.$user_data['username'].'( '.$user_data['id'].' )');
        }
        $t = $g + $f;
        $admin->userNotice("Just exported $g of $t users.$next_meta");
        $this->cleanUp();
        //$admin->userNotice('TEST:  row = <pre>'.print_r($bridge_result->FetchRow(),1).'</pre>');

        $this->ignore_critical_log = false;

        return true;
    }

    /*
    function importUserCount(){
        return 5;
    }

    function exportUserCount(){
        return 0;
    }
*/

    function session_login($vars)
    {
        if (!$this->connect()) {
            return false;
        }
        $this->user_info = $vars;
        //$this->log('Vars: <pre>'.print_r($vars,1).'</pre>');
        //connect to the installation database
        if ($this->get_user_info() && $this->user_info['status'] == 1) {
            if ($this->check_if_logged_in()) {
                //logged in, so just update session.
                $this->update_session();
            } else {
                //not logged in, so insert new session.
                if ($this->get_user_level()) {
                    $this->insert_session();
                }
            }
        }
        $this->cleanUp();
    }

    function session_touch($sessionId)
    {
        if (!$this->connect()) {
            return false;
        }

        if ($this->test) {
            for ($i = 5; $i < 5000; $i++) {
                $myarray['email'] = "local{$i}_@carlos.geo";
                $myarray['email2'] =  "";
                $myarray['email_verifier'] = "local{$i}_@carlos.geo";
                $myarray['email_verifier2'] = "";
                $myarray['username'] = "local{$i}";
                $myarray['password'] = "local{$i}";
                $myarray['agreement'] = 'yes';
                $myarray['company_name'] = "local{$i}";
                $myarray['business_type'] = '1';
                $myarray['firstname'] = "local{$i}";
                $myarray['lastname'] = "local{$i}";
                $myarray['address'] = "local{$i}";
                $myarray['address_2'] = "local{$i}";
                $myarray['city'] = "local{$i}";
                $myarray['state'] = 'TX';
                $myarray['country'] = 'United States';
                $myarray['zip'] = "local{$i}";
                $myarray['phone'] = "local{$i}";
                $myarray['phone_2'] = "local{$i}";
                $myarray['fax'] = "local{$i}";
                $myarray['url'] = "local{$i}";
                //DELETE FROM `geodesic_logins` WHERE `username` LIKE 'local%'
                //DELETE FROM `geodesic_userdata` WHERE `username` LIKE 'local%'
                $this->user_register($myarray, false);
            }
        }

        //first, see if session exists.
        $sql = 'SELECT `classified_session`, `user_id` FROM `geodesic_sessions` WHERE `classified_session` = ?';

        $check_session = $this->db->Execute($sql, array($sessionId));

        if (!$check_session) {
            //db error!
            $this->log('DB Error when touching session, check bridge settings.  debug info: ' . $this->db->ErrorMsg(), true);
            $this->cleanUp();
            return false;
        }
        if ($check_session->RecordCount() == 1) {
            //we update the session!
            $sql = "UPDATE `geodesic_sessions` SET `last_time` = ? where classified_session = ?";

            $update_session_time_result = $this->db->Execute($sql, array(time(),$sessionId));
        } elseif ($check_session->RecordCount() == 0) {
            //session does not exist, so create it.
            $this->insert_session($sessionId);
        }
        $this->cleanUp();
    }

    function session_create($sessionId)
    {
        if (!$this->connect()) {
            return false;
        }
        $this->create_new_session($sessionId);
        $this->cleanUp();
    }

    function session_logout($vars)
    {
        if (!$this->connect()) {
            return false;
        }
        $this->user_info['username'] = $vars['username'];
        $this->get_user_info();

        //$cookie_url = str_replace("classifieds.php", "",$this->installation_configuration_data->CLASSIFIEDS_URL);
        //setcookie("classified_session",$custom_id,time(),"/");
        //header("Set-Cookie: classified_session=".$custom_id."; path=/; domain=".$cookie_url."; expires=".gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT",$expires));
        $tables = array('geodesic_sessions' => 'classified_session',
                        'geodesic_classifieds_sell_session' => 'session',
                        'geodesic_classifieds_sell_session_questions' => 'session',
                        'geodesic_classifieds_sell_session_images' => 'session',
                        );
        //TODO: Remove images from file system...

        foreach ($tables as $table => $column) {
            $sql = "DELETE FROM `{$table}` WHERE `{$column}` = ? LIMIT 1";
            $result = $this->db->Execute($sql, array($_COOKIE['classified_session']));
            if (!$result && $table == 'geodesic_sessions') {
                //Only generate a log if this is the main geodesic_sessions table, as the rest are expected to fail on pre-4.0 install.

                $this->log('DB Error when logging out, check bridge settings.  Debug: failed removing sessions from table ' . $table . ', ' . $this->db->ErrorMsg(), true);
                //don't stop, keep trying to remove the rest of the sessions..
            }
        }

        if (isset($this->user_info['id']) && $this->user_info['id'] > 0) {
            $sql = "DELETE FROM `geodesic_sessions` WHERE `user_id` = ?";
            $result = $this->db->Execute($sql, array(intval($this->user_info['id'])));
            if (!$result) {
                $this->log('DB Error when logging out, check bridge settings.  Debug: failed removing sessions from main session table, remove by user_id, ' . $this->db->ErrorMsg(), true);
            }
        }
        $this->cleanUp();
        //clear user info so it doesn't try to auto re-log in
        $this->user_info = array();
        return true;
    }

    function user_edit($user_info)
    {
        if (!$this->connect()) {
            return false;
        }

        $this->user_info = $user_info;
        $only_update_logins = (isset($user_info['only_logins'])) ? $user_info['only_logins'] : false;
        $this->get_user_info(true);
        if (isset($this->user_info['id']) && $this->user_info['id']) {
            if ($this->get_current_userdata()) {
                if ($only_update_logins || $this->insert_into_userdata_history()) {
                    if ($only_update_logins || $this->update_current_userdata()) {
                        $this->update_logins_info();
                    }
                }
            }
        }

        $this->cleanUp();
        return true;
    }

    function user_register($vars, $login_user = true)
    {
        if (!$this->connect()) {
            return false;
        }
        //enter user info
        $result = false;
        $this->user_info = $vars;
        if ($this->duplicate_email()) {
            $this->log(__line__ . ' Here.');
            $this->get_user_group_and_price_plan();

            if ($this->insert_into_logins_table()) {
                $this->user_info['id'] = $this->db->Insert_ID();
                if ($this->insert_into_userdata_table()) {
                    //insert into users_group_price_plans table
                    $this->get_user_price_plan();

                    //check for expiration of price plans
                    if ($this->user_info['auction_price_plan']['expiration_type'] == 2) {
                        $this->insert_price_plan_expiration(1);
                    }
                    $result = true;
                }

                $this->insert_into_user_groups_and_price_plans();

                //check to see if registration credits or free subscription period
                if ($this->user_info['price_plan']['type_of_billing'] == 1) {
                    //fee based subscriptions
                    if ($this->user_info['price_plan']['credits_upon_registration'] > 0) {
                        $this->insert_credits_upon_registration();
                    }
                } elseif ($this->user_info['price_plan']['type_of_billing'] == 2) {
                    //subscription based
                    if ($this->user_info['price_plan']['free_subscription_period_upon_registration'] > 0) {
                        $this->insert_free_subscription_period();
                    }
                }
                if ($login_user) {
                    $this->insert_session();
                }
            }
        }
        $this->cleanUp();
        return $result;
    }

    function user_remove($vars)
    {
        if (!$this->connect()) {
            return false;
        }
        return false;
    }

    function test_settings($settings)
    {
        $this->cleanUp();
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        //should return true if ok, false otherwise...  useing userError or whatever..
        $test_db = ADONewConnection('mysql');
        if (!$test_db->PConnect($this->settings['db_host'], $this->settings['db_user'], $this->settings['db_pass'], $this->settings['db_name']) || !$test_db->isConnected()) {
            $admin->userError('Could not connect to database, check the db settings (' . implode(', ', $this->settings) . '); ' . $test_db->ErrorMsg());
            $result = false;
        } else {
            //TODO: Add more testing to make sure the database can actually be accessed, maybe test
            // to make sure the version is at least 3.1...

            //TODO: Also add tests for cookie expiration, and domain name/path settings
            $admin->userSuccess('Successful Connection to Bridge\'s database.');
            $result = true;
        }
        $test_db->Close();
        $this->cleanUp();
        return $result;
    }

    //custom functions
    function get_user_info($use_old_username = false)
    {
        if (!$this->connect()) {
            return false;
        }
        //get the user info
        $username = '' . (($use_old_username && isset($this->user_info['old_username'])) ? $this->user_info['old_username'] : $this->user_info['username']);
        if (!$username) {
            //can't be getting the user info without a username!
            return false;
        }
        $sql = 'SELECT `username`, `id`, `status`, `password` from `geodesic_logins` where `username` = ? LIMIT 1';
        $result = $this->db->Execute($sql, array($username));
        if (!$result) {
            //doh!
            $this->log('ERROR: Could not get user info from database, db query failed!  Check your DB settings
for this bridge.  Debug info, ' . $this->db->ErrorMsg(), true);
            return false;
        }
        if ($result->RecordCount() == 1) {
            $row = $result->FetchRow();
            if ($row['username'] == $username) {
                $this->user_info['id'] = $row['id'];
                $this->user_info['status'] = $row['status'];
                $this->user_info['db_password'] = $row['password'];
                return true;
            }
        }
        //either 0 or more than 0 users by this username...
        $this->log('Error getting user info for: ' . $username . ', you may need to sync users for this bridge.  Debug info: number of db results for user: ' . $result->RecordCount() . ' : row (if record count = 1): ' . print_r($row, 1), true);
        return false;
    }

    /**
     * Check to see if the user is currently logged in
     */
    function check_if_logged_in()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = 'SELECT `user_id` FROM `geodesic_sessions` WHERE `user_id` = ?';
        $check_login_result = $this->db->Execute($sql, array($this->user_info['id']));
        if (!$check_login_result) {
            $this->log('Error checking if user is logged in, check your settings for this bridge, ' . $this->db->ErrorMsg(), true);
            return false;
        }
        if ($check_login_result->RecordCount() > 0) {
            //the user is logged in
            return true;
        }
        //the user is not logged in.
        return false;
    }

    /**
     * updates the session.
     */
    function update_session()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = 'UPDATE `geodesic_sessions` SET `last_time` = ? WHERE `user_id` = ?';
        $result = $this->db->Execute($sql, array(time(), $this->user_info['id']));
        if (!$result) {
            $this->log('Error updating user session, check installation\'s settings, ' . $this->db->ErrorMsg(), true);
            return false;
        }
        //done
        return true;
    }

    /**
     * Gets the user level
     */
    function get_user_level()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = 'SELECT `level` FROM `geodesic_userdata` WHERE `id` = ?';//".$this->login_return->ID;
        $result = $this->db->Execute($sql, array($this->user_info['id']));
        if (!$result) {
            $this->log('DB Error when getting user level, check bridge settings.  debug info: ' . $this->db->ErrorMsg(), true);
            $this->user_info['level'] = 0;
            return false;
        }
        if ($result->RecordCount() == 1) {
            $show_level = $result->FetchRow();
            $this->user_info['level'] = $show_level['level'];
            return true;
        }
        $this->log('Error getting user level for user ' . $this->user_info['username'] . '(' . $this->user_info['id'] . '), you may need to sync users for this bridge.  Debug info: recourd count:' . $result->RecordCount(), true);
        return false;
    }

    /**
     * Insert new session for bridge
     */
    function insert_session($session_id = null)
    {
        if (!$this->connect()) {
            return false;
        }
        $session = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $ip = 0;

        //check to see if the current session id can be used within the other installation
        if (is_null($session_id)) {
            $session_id = $_COOKIE["classified_session"];
        }

        //if the session exists make sure it has the same user id
        //if the session user_id is 0 then set the user_id to the user_id within that installation
        //if the session user_id is not 0 then set a new session

        //see if a session of the same id already exists
        if ($session_id) {
            $ip = $session->getUniqueUserInfo($session_id);
            $sql = 'SELECT `classified_session`, `user_id` FROM `geodesic_sessions` WHERE `classified_session` = ?';//"'.$session_id.'"';// and ip = "'.$ip.'"';

            //compare user_info["installation_type"] and $this->installation_info["installation_type"]
            //if both use different cookies then try to set another cookie for the different type
            $check_session_result = $this->db->Execute($sql, array($session_id));
            if (!$check_session_result) {
                $this->log('DB Error when getting session id, check settings for this bridge.  Debug: ' . $this->db->ErrorMsg(), true);
                return false;
            }

            if ($check_session_result->RecordCount() == 1) {
                //the session id already exists
                //update the current session if the user id is 0
                //if session id has a user attached (user_id != 0) create a new session
                $current_session = $check_session_result->FetchRow();
                if ($current_session['user_id'] != 0) {
                    //user id does not equal 0...check to see if cookie matches for both on the same session id
                    //if it does then create another cookie for the same installation

                    $this->create_new_session();
                } else {
                    $ip_field = $session->getIpField();
                    $sql = "UPDATE `geodesic_sessions` SET `user_id` = ? WHERE `classified_session` = ? and `{$ip_field}` = ?";

                    $update_session_id_result = $this->db->Execute($sql, array($this->user_info['id'], $session_id, $ip));
                    if (!$update_session_id_result) {
                        $this->log('DB Error when inserting session, check settings for this bridge.  Debug: ' . $this->db->ErrorMsg(), true);
                        return false;
                    }
                }
                return true;
            } elseif ($check_session_result->RecordCount() == 0) {
                //the session id does not exist
                //insert the new session_id from the user_info array
                $sql = "INSERT INTO `geodesic_sessions`
							(classified_session,user_id,last_time,ip,level)
							values (?, ?, ?, ?, ?)";
                $this->user_info['id'] = (isset($this->user_info['id'])) ? $this->user_info['id'] : 0;
                $query_data = array($session_id,$this->user_info['id'],time(),$ip,0);

                $insert_new_session_result = $this->db->Execute($sql, $query_data);
                if (!$insert_new_session_result) {
                    $this->log('DB Error when inserting session, check settings for this bridge.  Debug: ' . $this->db->ErrorMsg(), true);
                    return false;
                }

                $expire_duration = intval($this->settings['cookie_expire']);
                $expires = (!$expire_duration) ? 0 : (time() + $expire_duration); //0 duration, expire when browser closes
                $domain = (strlen(trim($this->settings['cookie_domain'])) > 0) ? $this->settings['cookie_domain'] : $_SERVER["HTTP_HOST"];
                $path = (strlen(trim($this->settings['cookie_path'])) > 0) ? $this->settings['cookie_path'] : '/';

                setcookie("classified_session", $session_id, $expires, $path, $domain);
            } else {
                //goofy things are happening if it got here............
                $this->log('DB Error, debug info: line: ' . __line__ . ', unexpected result count returned, result count=' . $check_session_result->RecordCount(), true);
                return false;
            }
        } else {
            //no session_id was passed...or not found
            //create a session_id

            $this->create_new_session();
        }
        return true;
    }
    /**
     * Create a new session
     */
    function create_new_session($session_id = null)
    {
        if (!$this->connect()) {
            return false;
        }
        $session = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        if ($session_id == null) {
            do {
                $custom_id = md5(uniqid(rand(), 1));
                $custom_id = substr($custom_id, 0, 32);
                $ip = $session->getUniqueUserInfo($custom_id);
                $sql = "SELECT `classified_session` FROM `geodesic_sessions` WHERE `classified_session` = ?";

                $custom_id_result = $this->db->Execute($sql, array($custom_id));
                if (!$custom_id_result) {
                    $this->log('DB Error, check bridge settings.  Debug info: ' . $this->db->ErrorMsg(), true);
                    return false;
                }
            } while ($custom_id_result->RecordCount() > 0);
        } else {
            $custom_id = $session_id;
            $ip = $session->getUniqueUserInfo($custom_id);
        }
        $ip_field = $session->getIpField();
        $sql = "INSERT INTO `geodesic_sessions`
			(`classified_session`, `user_id`, `last_time`, `{$ip_field}`, `level`)
			values (?, ?, ?, ?, ?)";
        $query_data = array($custom_id,(int)$this->user_info['id'],time(),$ip,0);

        $session_result = $this->db->Execute($sql, $query_data);
        if (!$session_result) {
            $this->log('DB Error, check bridge settings.  Debug info: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * gets the current user data to insert into history
     */
    function get_current_userdata()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = "SELECT * FROM `geodesic_userdata` WHERE `id` = ?";

        $old_userdata_result = $this->db->Execute($sql, array($this->user_info['id']));
        if (!$old_userdata_result) {
            $this->log('Error getting user data, check settings for bridge.  Debug info: ' . $this->db->ErrorMsg(), true);
            return false;
        }

        if ($old_userdata_result->RecordCount() == 1) {
            $this->old_user_data = $old_userdata_result->FetchRow();
            return true;
        }
        return false;
    }

    /**
     * Inserts changes into history for userdata
     */
    function insert_into_userdata_history()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = "insert into geodesic_userdata_history
					(date_of_change,id,username,email,company_name,business_type,firstname,lastname,
					address,address_2,zip,city,state,country,phone,phone2,fax,url,optional_field_1,
					optional_field_2,optional_field_3,optional_field_4,optional_field_5,optional_field_6,optional_field_7,
					optional_field_8,optional_field_9,optional_field_10)
					values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query_data = array(time(),
                    $this->old_user_data['id'],
                    $this->old_user_data['username'],
                    $this->old_user_data['email'],
                    $this->old_user_data['company_name'],
                     $this->old_user_data['business_type'],
                    $this->old_user_data['firstname'],
                    $this->old_user_data['lastname'],
                    $this->old_user_data['address'],
                    $this->old_user_data['address_2'],
                     $this->old_user_data['zip'],
                    $this->old_user_data['city'],
                    $this->old_user_data['state'],
                    $this->old_user_data['country'],
                    $this->old_user_data['phone'],
                     $this->old_user_data['phone2'],
                    $this->old_user_data['fax'],
                    $this->old_user_data['url'],
                    $this->old_user_data['optional_field_1'],
                    $this->old_user_data['optional_field_2'],
                     $this->old_user_data['optional_field_3'],
                    $this->old_user_data['optional_field_4'],
                    $this->old_user_data['optional_field_5'],
                    $this->old_user_data['optional_field_6'],
                    $this->old_user_data['optional_field_7'],
                     $this->old_user_data['optional_field_8'],
                    $this->old_user_data['optional_field_9'],
                    $this->old_user_data['optional_field_10']);

        $history_result = $this->db->Execute($sql, $query_data);
        if (!$history_result) {
            $this->log('Error adding entry to userdata history, check bridge connections.  Debug: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * update user-data
     */
    function update_current_userdata()
    {
        if (!$this->connect()) {
            return false;
        }

        $sql = "UPDATE `geodesic_userdata` SET
			email = ?,
			company_name = ?,
			business_type = ?,
			firstname = ?,
			lastname = ?,
			address = ?,
			address_2 = ?,
			city = ?,
			state = ?,
			country = ?,
			zip = ?,
			phone = ?,
			phone2 = ?,
			fax = ?,
			url = ?,
			affiliate_html = ?,
			optional_field_1 = ?,
			optional_field_2 = ?,
			optional_field_3 = ?,
			optional_field_4 = ?,
			optional_field_5 = ?,
			optional_field_6 = ?,
			optional_field_7 = ?,
			optional_field_8 = ?,
			optional_field_9 = ?,
			optional_field_10 = ?
			WHERE `id` = ? LIMIT 1";
        $query_data = array(
            $this->user_info["email"],
            $this->user_info["company_name"],
            $this->user_info["business_type"],
            $this->user_info["firstname"],
            $this->user_info["lastname"],
            $this->user_info["address"],
            $this->user_info["address_2"],
            $this->user_info["city"],
            $this->user_info["state"],
            $this->user_info["country"],
            $this->user_info["zip"],
            $this->user_info["phone"],
            $this->user_info["phone_2"],
            $this->user_info["fax"],
            $this->user_info["url"],
            $this->user_info["affiliate_html"],
            $this->user_info["optional_field_1"],
            $this->user_info["optional_field_2"],
            $this->user_info["optional_field_3"],
            $this->user_info["optional_field_4"],
            $this->user_info["optional_field_5"],
            $this->user_info["optional_field_6"],
            $this->user_info["optional_field_7"],
            $this->user_info["optional_field_8"],
            $this->user_info["optional_field_9"],
            $this->user_info["optional_field_10"],
            $this->user_info['id']
        );
        $update_history_result = $this->db->Execute($sql, $query_data);
        if (!$update_history_result) {
            $this->log('Db Error when trying to update userdata!  Check bridge settings. Debug: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * updates login info
     */
    function update_logins_info()
    {
        if (!$this->connect()) {
            return false;
        }
        if (strlen(trim($this->user_info["password"])) > 0) {
            //this expects the password to be plaintext!
            //need to update the username and password, since the password hash uses
            //the username as part of the salt.
            $hash_pass = $this->user_info['password'];//keep it simple... don't bother hashing the pass
            $sql = "UPDATE `geodesic_logins` SET `username` = ?, `password` = ? WHERE `id` = ? LIMIT 1";
            $query_data = array($this->user_info['username'],$hash_pass, $this->user_info['id']);
            $result = $this->db->Execute($sql, $query_data);
            if (!$result) {
                $this->log('Error when updating user login info, check bridge settings!  Debug info: ' . $this->db->ErrorMsg(), true);
                return false;
            }
            //update the hash_type and salt as well, but use a different query and
            //don't log anything if there is an error.. so it will work with old versions
            $sql = "UPDATE `geodesic_logins` SET `hash_type`='core:plain', `salt`='' WHERE `id`=?";
            $this->db->Execute($sql, array($this->user_info['id']));
        }
        return true;
    }
    /**
     * gets a setting
     */
    function get_setting($setting)
    {
        if (!$this->connect()) {
            return false;
        }
        $setting = strtolower($setting);
        if (!isset($this->install_settings) || !is_array($this->install_settings)) {
            //dont get the data twice if we already have it.
            $this->install_settings = array();
            $sql = "SELECT * FROM `geodesic_classifieds_configuration`";
            $result = $this->db->Execute($sql);
            if ($result === false) {
                $this->log('Error getting data, check bridge settings.  Debug: ' . $this->db->ErrorMsg(), true);
                return false;
            }
            $this->install_settings = $result->FetchRow();
            $sql = 'SELECT * FROM `geodesic_site_settings`';

            $result = $this->db->Execute($sql);

            if (false === $result) {
                $this->log('Error getting data, check bridge settings.  Debug: ' . $this->db->ErrorMsg(), true);
                return false;
            }
            while ($row = $result->FetchRow()) {
                //side effect: any settings duplicated in configuration data and sit config tables,
                //will be overridden by the newer table.
                $this->install_settings[$row['setting']] = $row['value'];
            }
        }
        if (isset($this->install_settings[$setting])) {
            return $this->install_settings[$setting];
        }
        return false;
    }

    /**
     * Sees if there are duplicate e-mails
     */
    function duplicate_email()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = "SELECT * from `geodesic_userdata` WHERE `email` = ?";

        $duplicate_result = $this->db->Execute($sql, array($this->user_info['email']));

        if (!$duplicate_result) {
            $this->log('DB Error when checking for duplicate e-mails, debug: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        if ($duplicate_result->RecordCount() > 0) {
            //that email address already exists
            $this->log('Oops!  When trying to insert a new user, the e-mail used is already being used by another user!  Duplicate e-mail: ' . $this->user_info['email'] . '.  The user will not automatically be added to the bridge installation.', true);
            return false;
        }
        return true;
    }

    /**
     * gets the users group and price plan
     */
    function get_user_group_and_price_plan()
    {
        if (!$this->connect()) {
            return false;
        }
        if (strlen(trim($this->user_info["registration_code"])) > 0) {
            $sql = "SELECT `group_id`, `price_plan_id`, `auction_price_plan_id` FROM `geodesic_groups` WHERE `registration_code` = ?";

            $code_result = $this->db->Execute($sql, array(trim($this->user_info["registration_code"])));
            if (!$code_result) {
                $this->log('DB Error, check bridge\'s settings.  Debug info: ' . $this->db->ErrorMsg(), true);
                return false;
            }
            if ($code_result->RecordCount() == 1) {
                $show = $code_result->FetchRow();
                $this->user_info['user_group'] = $show['group_id'];
                $this->user_info['user_price_plan'] = $show['price_plan_id'];
                $this->user_info['user_auction_price_plan'] = $show['auction_price_plan_id'];
                return true;
            }
        }
        //get default group and price plan
        return $this->get_default_group();
    }

    /**
     * get the default price group for user
     */
    function get_default_group()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = "SELECT `group_id`, `price_plan_id`, `auction_price_plan_id` FROM `geodesic_groups` WHERE `default_group` = 1";
        $group_result = $this->db->Execute($sql);
        if (!$group_result) {
            $this->log('DB Error, check bridge settings.  Debug: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        if ($group_result->RecordCount() == 1) {
            $show_group = $group_result->FetchRow();
            $this->user_info['user_group'] = $show_group['group_id'];
            $this->user_info['user_price_plan'] = $show_group['price_plan_id'];
            $this->user_info['user_auction_price_plan'] = $show_group['auction_price_plan_id'];
            return true;
        }
        //if record count is 0 or > 1, the bridge user group configuration is messed up.  Need to have 1 and only 1 default user group.
        $this->log('ERROR: Could not find default user group and price plans!  The Bridge needs to have 1 and ONLY 1 default user group.  Debug: Record count:' . $group_result->RecordCount(), true);
        return false;
    }

    //inserts user into logins table
    function insert_into_logins_table()
    {
        if (!$this->connect()) {
            return false;
        }
        $hash_pass = $this->user_info['password']; //use plain text when inserting pass into alternate db
        $sql = 'INSERT INTO `geodesic_logins` (username, password, status) VALUES (?, ?, ?)';
        $status = (isset($this->user_info['status'])) ? $this->user_info['status'] : 1;
        $query_data = array($this->user_info['username'], $hash_pass, $status);

        $result = $this->db->Execute($sql, $query_data);
        if (!$result) {
            $this->log('DB Error creating new registration on alternate bridge!  Debug: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        //set hash_type and salt for versions > 7.1
        $user_id = $this->db->Insert_ID();
        if ($user_id) {
            //Note: do not check for error as we expect this to fail on versions < 7.1
            $this->db->Execute("UPDATE `geodesic_logins` SET `hash_type`='core:plain' WHERE `id`=?", array($user_id));
        }
        return true;
    }

    /**
     * Insert into userdata table for new registrations
     */
    function insert_into_userdata_table()
    {
        if (!$this->connect()) {
            return false;
        }

        $sql = "insert into geodesic_userdata (id,username,email,email2,newsletter,level,company_name,
			business_type,firstname,lastname,address,address_2,zip,city,state,country,phone,phone2,fax,url,date_joined,
			communication_type,rate_sum,rate_num,optional_field_1,optional_field_2,optional_field_3,optional_field_4,
			optional_field_5,optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10) 
		values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $query_data = array(
            $this->user_info['id'],
            $this->user_info["username"],
            $this->user_info["email"],
            ((strlen($this->user_info["email2"]) > 0) ? $this->user_info["email2"] : ''),
            ((strlen($this->user_info["newsletter"]) > 0) ? $this->user_info["newsletter"] : "0"),
            ((strlen($this->user_info["level"]) > 0) ? $this->user_info["level"] : '0'),
            ((strlen($this->user_info["company_name"]) > 0) ? $this->user_info["company_name"] : ''),
            ((strlen($this->user_info["business_type"]) > 0) ? $this->user_info["business_type"] : '0'),
            ((strlen($this->user_info["firstname"]) > 0) ? $this->user_info["firstname"] : ''),
            ((strlen($this->user_info["lastname"]) > 0) ? $this->user_info["lastname"] : ''),
            ((strlen($this->user_info["address"]) > 0) ? $this->user_info["address"] : ''),
            ((strlen($this->user_info["address2"]) > 0) ? $this->user_info["address2"] : ''),
            ((strlen($this->user_info["zip"]) > 0) ? $this->user_info["zip"] : ''),
            ((strlen($this->user_info["city"]) > 0) ? $this->user_info["city"] : ''),
            ((strlen($this->user_info["state"]) > 0) ? $this->user_info["state"] : ''),
            ((strlen($this->user_info["country"]) > 0) ? $this->user_info["country"] : ''),
            ((strlen($this->user_info["phone"]) > 0) ? $this->user_info["phone"] : ''),
            ((strlen($this->user_info["phone2"]) > 0) ? $this->user_info["phone2"] : ''),
            ((strlen($this->user_info["fax"]) > 0) ? $this->user_info["fax"] : ''),
            ((strlen($this->user_info["url"]) > 0) ? $this->user_info["url"] : ''),
            time(),
            1,
            0,
            0,
            ((strlen($this->user_info["optional_field_1"]) > 0) ? $this->user_info["optional_field_1"] : ''),
            ((strlen($this->user_info["optional_field_2"]) > 0) ? $this->user_info["optional_field_2"] : ''),
            ((strlen($this->user_info["optional_field_3"]) > 0) ? $this->user_info["optional_field_3"] : ''),
            ((strlen($this->user_info["optional_field_4"]) > 0) ? $this->user_info["optional_field_4"] : ''),
            ((strlen($this->user_info["optional_field_5"]) > 0) ? $this->user_info["optional_field_5"] : ''),
            ((strlen($this->user_info["optional_field_6"]) > 0) ? $this->user_info["optional_field_6"] : ''),
            ((strlen($this->user_info["optional_field_7"]) > 0) ? $this->user_info["optional_field_7"] : ''),
            ((strlen($this->user_info["optional_field_8"]) > 0) ? $this->user_info["optional_field_8"] : ''),
            ((strlen($this->user_info["optional_field_9"]) > 0) ? $this->user_info["optional_field_9"] : ''),
            ((strlen($this->user_info["optional_field_10"]) > 0) ? $this->user_info["optional_field_10"] : ''));


        //insert login data into the login table
        $userdata_result = $this->db->Execute($sql, $query_data);
        if (!$userdata_result) {
            $this->log('Error inserting user into userdata table!  Debug info: ' . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * Gets the user's price plan
     */
    function get_user_price_plan()
    {
        if (!$this->connect()) {
            return false;
        }
        if ($this->user_info['user_price_plan']) {
            $sql = "SELECT * FROM `geodesic_classifieds_price_plans` WHERE `price_plan_id` = ?";

            $price_plan_result = $this->db->Execute($sql, array($this->user_info['user_price_plan']));
            if (!$price_plan_result) {
                $this->log('DB Error, check bridge settings.  Debug: ' . $this->db->ErrorMsg(), true);
                $this->user_info['price_plan'] = 0;
                $this->user_info['auction_price_plan'] = 0;
                return false;
            }
            if ($price_plan_result->RecordCount() == 1) {
                $this->user_info['price_plan'] = $price_plan_result->FetchRow();
                if ($this->user_info['user_auction_price_plan']) {
                    $sql = "SELECT * FROM `geodesic_classifieds_price_plans` WHERE `price_plan_id` = ?";
                    $auction_price_plan_result = $this->db->Execute($sql, array($this->user_info['user_auction_price_plan']));
                    $this->user_info['auction_price_plan'] = $auction_price_plan_result->FetchRow();
                } else {
                    $this->user_info['auction_price_plan'] = 0;
                }
            } else {
                $this->user_info['price_plan'] = 0;
                $this->user_info['auction_price_plan'] = 0;
            }
        } else {
            $this->user_info['price_plan'] = 0;
            $this->user_info['auction_price_plan'] = 0;
        }
        return true;
    }

    /**
     * Insert price plan expiration
     */
    function insert_price_plan_expiration($auction_price_plan_check = 0)
    {
        if (!$this->connect()) {
            return false;
        }
        $expiration_date = (time() + ($this->user_info['price_plan']['expiration_from_registration'] * 84600));
        $price_plan = $this->user_info['user_auction_price_plan'];
        $price_plan = ((isset($price_plan) && $auction_price_plan_check) ? $price_plan :  $this->user_info['user_price_plan']);

        $sql = "INSERT INTO `geodesic_classifieds_expirations` (type,user_id,expires,type_id) values (?, ?, ?, ?)";
        $query_data = array(2, $this->user_info['id'],$expiration_date,$price_plan);

        $plan_expiration_result = $this->db->Execute($sql, $query_data);
        if (!$plan_expiration_result) {
            $this->log("DB ERROR - Check bridge configuration. Debug: INSERTING PRICE PLAN EXPIRATION FROM REGISTRATION, " . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * Insert into groups and price plans
     */
    function insert_into_user_groups_and_price_plans()
    {
        if (!$this->connect()) {
            return false;
        }
        $sql = "INSERT INTO `geodesic_user_groups_price_plans`
					(id,group_id,price_plan_id,auction_price_plan_id)
					values ( ?, ?, ?, ? )";
        $query_data = array($this->user_info['id'],$this->user_info['user_group'],$this->user_info['user_price_plan'],$this->user_info['user_auction_price_plan']);

        $group_result = $this->db->Execute($sql, $query_data);
        if (!$group_result) {
            $this->log("DB ERROR - Check bridge settings.  Debug: INSERTING GROUP/PRICE PLAN INFO IN REGISTRATION, " . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * Insert credits
     */
    function insert_credits_upon_registration()
    {
        if (!$this->connect()) {
            return false;
        }
        if ($this->user_info['price_plan']['credits_expire_type']['credits_expire_type'] == 1) {
            //expire on fixed days from registration
            $expiration = (($this->user_info['price_plan']->CREDITS_EXPIRE_PERIOD * 86400) + time());
        } elseif ($this->user_info['price_plan']['credits_expire_type'] == 2) {
            //expire on fixed date
            $expiration = $this->user_info['price_plan']['credits_expire_date'];
        }
        //Try using the "new" db table/field names, and the pre-4.0 names...
        $sqls[] = "INSERT INTO `geodesic_user_tokens` (user_id,token_count,expire) values ( ?, ?, ? )";
        $sqls[] = "INSERT INTO `geodesic_classifieds_user_credits` (user_id,credit_count,credits_expire) values ( ?, ?, ? )";

        $query_data = array($this->user_info['id'],$this->user_info['price_plan']['credits_upon_registration'],$expiration);
        foreach ($sqls as $sql) {
            $free_credits_result = $this->db->Execute($sql, $query_data);
            if ($free_credits_result) {
                break;
            }
        }
        if (!$free_credits_result) {
            $this->log("DB ERROR - Check bridge settings.  Debug: INSERTING CREDITS IN REGISTRATION, " . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }

    /**
     * insert subscription period
     */
    function insert_free_subscription_period()
    {
        if (!$this->connect()) {
            return false;
        }
        $expiration = (($this->user_info['price_plan']['free_subscription_period_upon_registraion'] * 86400) + time());
        $sql = "INSERT INTO `geodesic_classifieds_user_subscriptions` (user_id,subscription_expire) values (?, ?)";
        $free_subscription_result = $this->db->Execute($sql, array ($this->user_info['id'],$expiration));
        if (!$free_subscription_result) {
            $this->log("DB ERROR, check bridge settings.  Debug - INSERTING FREE SUBSCRIPTION IN REGISTRATION, " . $this->db->ErrorMsg(), true);
            return false;
        }
        return true;
    }
    //Bridge connection functions...
    /**
     * clean up the bridge connection and the main db connection.
     */
    function cleanUp()
    {
        //Close this connection
        if ($this->isConnected()) {
            $this->db->Close();
        }
        //make sure database connection is reset, to prevent weird stuff from happening...
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        $db->Close();
    }
    function connect()
    {
        //make sure we are not already connected...
        if ($this->isConnected()) {
            return true;
        }
        //make a connection..
        $this->db = ADONewConnection('mysql');
        if (!$this->use_local_db) {
            $db_host = $this->settings['db_host'];
            $db_user = $this->settings['db_user'];
            $db_pass = $this->settings['db_pass'];
            $db_name = $this->settings['db_name'];
        } else {
            include(GEO_BASE_DIR . 'config.default.php');
            $db_user = $db_username;
            $db_pass = $db_password;
            $db_name = $database;
        }
        $this->db->Connect($db_host, $db_user, $db_pass, $db_name);
        if (!$this->db->isConnected()) {
            $this->log("Error connecting to bridge database, check settings for this bridge.  DB error returned: " . $this->db->ErrorMsg(), true);//log it
            return false;
        }
        if ($this->settings['db_strict']) {
            if (!$this->db->Execute('SET SESSION sql_mode=\'\'')) {
                $this->log('Error when attempting to turn off strict mode, check your bridge settings.  May need to turn off the strict mode setting.  Debug info: ' . $this->db->ErrorMsg(), true);
                return false;
            }
        }
        return true;
    }
    function isConnected()
    {
        if (isset($this->db) && is_object($this->db)) {
            return $this->db->isConnected();
        }
        return false;
    }
}
