<?php

//addons/social_connect/util.php
# Facebook Connect

require_once ADDON_DIR . 'social_connect/info.php';

class addon_social_connect_util extends addon_social_connect_info
{
    public $facebook, $messages, $user, $login_user, $user_profile;

    private $_loginUrl;

    public function core_display_login_bottom($vars)
    {
        if ($this->user) {
            //already logged in?
            return;
        }
        if (!$this->facebook) {
            //not initialized?
            return;
        }

        $encode = $vars['encode'];

        $redirect_uri = '';
        if ($encode) {
            //generate redirect URI, add c to allow re-directing to wherever it should.
            //$base = geoFilter::getBaseHref().DataAccess::getInstance()->get_site_setting('classifieds_file_name');
            $redirect_uri_end = '?c=' . urlencode($encode);
        }
        geoView::getInstance()->addCssFile("addons/social_connect/facebook_button.css");
        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign('loginUrl', $this->loginUrl() . $redirect_uri_end);
        $tpl->assign('msgs', geoAddon::getText($this->auth_tag, $this->name));
        return $tpl->fetch('core_events/login_bottom.tpl');
    }

    public function core_display_registration_code_form_top()
    {
        if ($this->user) {
            //already logged in?
            return;
        }
        if (!$this->facebook) {
            //not initialized?
            return;
        }
        geoView::getInstance()->addCssFile("addons/social_connect/facebook_button.css");
        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign('loginUrl', $this->loginUrl());
        $tpl->assign('msgs', geoAddon::getText($this->auth_tag, $this->name));
        return $tpl->fetch('core_events/registration_top.tpl');
    }

    public function core_display_registration_form_top()
    {
        //use same as reg code page
        return $this->core_display_registration_code_form_top();
    }

    public function core_User_management_information_display_user_data()
    {
        if (!$this->facebook) {
            //not linked up with Facebook
            return;
        }

        $data = array();

        $msgs = geoAddon::getText($this->auth_tag, $this->name);

        $data['label'] = $msgs['fb_usr_info_label'];
        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);

        $userInfo = $this->getUserInfo(geoSession::getInstance()->getUserId());

        $tpl->assign('facebook_id', $userInfo['facebook_id']);

        if (!$userInfo['facebook_id']) {
            //login link
            $tpl->assign('loginUrl', $this->loginUrl());
        }
        $tpl->assign('msgs', $msgs);

        $data['value'] = $tpl->fetch('core_events/user_info.tpl');

        return $data;
    }

    public function core_user_information_edit_form_display($vars)
    {
        if (!$this->facebook) {
            //nothing to do, not linked up
            return;
        }

        $fields = array();

        $userInfo = $this->getUserInfo($vars['user_id']);
        if (!$userInfo || !$userInfo['facebook_id']) {
            return;
        }

        $msgs = geoAddon::getText($this->auth_tag, $this->name);

        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign($userInfo);

        $fields[] = array (
            'label' => $msgs['fb_usr_info_edit_label'],
            'value' => $tpl->fetch('facebook/profile_picture.tpl'),
        );

        $fields[] = array (
            'name' => 'facebook_reveal',
            'checked' => ($userInfo['facebook_reveal'] == 'Yes'),
            'value' => $msgs['fb_usr_info_reveal_label'],
            'type' => 'single_checkbox',
        );

        return $fields;
    }
    public function core_user_information_edit_form_update($vars)
    {
        if (!$this->facebook) {
            //nothing to do, not linked up
            return;
        }
        $userInfo = $this->getUserInfo($vars['user_id']);
        if (!$userInfo || !$userInfo['facebook_id']) {
            return;
        }
        $newValue = (isset($_POST['facebook_reveal']) && $_POST['facebook_reveal']) ? 'Yes' : 'No';
        if ($userInfo['facebook_reveal'] != $newValue) {
            //update value
            $user = geoUser::getUser($vars['user_id']);
            $user->facebook_reveal = $newValue;
        }
    }

    public function core_Admin_site_display_user_data($user_id)
    {
        $reg = geoAddon::getRegistry($this->name);
        if (!$reg->get('fb_app_id') || !$reg->get('fb_app_secret')) {
            return '';
        }
        $label = 'Facebook Profile';
        $db = DataAccess::getInstance();
        $user_id = (int)$user_id;
        if ($user_id <= 1) {
            //nothing to show
            return '';
        }
        $user = $db->GetRow("SELECT * FROM " . geoTables::logins_table . " WHERE `id`=$user_id");
        if (!$user || !$user['facebook_id']) {
            //could not get user info
            return '';
        }
        $value = "<img src='https://graph.facebook.com/{$user['facebook_id']}/picture' alt='' />";
        $value .= "<br /><a href='index.php?page=addon_social_FB_unlink&amp;user_id={$user_id}&amp;auto_save=1' class='mini_cancel lightUpLink'>Force Facebook Profile Unlink</a>";
        return geoHTML::addOption($label, $value);
    }

    public function init()
    {
        if (isset($this->facebook)) {
            //already initilized
            return;
        }
        trigger_error("DEBUG FACEBOOK:  Top of init");
        $reg = geoAddon::getRegistry($this->name);
        $fb_app_id = $reg->get('fb_app_id');
        $fb_app_secret = $reg->get('fb_app_secret');
        if (!$fb_app_id || !$fb_app_secret) {
            //can't do it without app ID
            trigger_error("DEBUG FACEBOOK:  app id and/or secret not set, cannot continue.");
            $this->facebook = false;
            return;
        }

        /*
         * UGLY HACK!
         * TODO: Remove this hack once the UA does not change for IE11 when coming
         * back from FB.  What happens: facebook.com is on internal IE11 "compatibility list"
         * so when the browser goes to facebook.com it switches to compatibility mode.  That
         * actually changes the user agent.  Then when getting re-directed back
         * to the website, since it is a result of re-direct from facebook.com it
         * stays in compat mode even through the redirect to my account.  This resets
         * the session, making the login not work (since the user agent changed).
         * Then when user clicks anywhere, it goes back to "normal" which resets
         * the session again.
         *
         * UGLY HACK Fix:  If the UA looks like it might be for IE11, it saves it
         * in a session var.
         *
         * Then when coming back from facebook, the first time it sets the server
         * vars to match.  It is probably going to do one more redirect though and
         * we need to still fake the UA for that redirect as well, so it sets a cookie
         * to tell itself to fake the UA on the next run to.
         *
         * Hopefully this can be removed, it's a dirty hack and I don't like it but
         * was the cleanest way to do it without resorting to re-writing the sessions
         * and without compromising on security.
         */

        //breaking up logic a little to make things sane for maintenance...
        $iefix = false;
        if (isset($_COOKIE['IEFIX_UIFB']) && $_COOKIE['IEFIX_UIFB'] === '1') {
            $iefix = true;
        } elseif (isset($_GET['code']) && isset($_GET['state'])) {
            $iefix = true;
        }
        if ($iefix && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10') !== false) {
            //This is likely IE11...  stupid problem where it goes into compat mode
            //for the initial page load coming back from fb.  Remove this once
            //it is no longer a problem (test in IE11 and up)
            trigger_error('DEBUG FACEBOOK: going to see if we should fake the UA');
            $session_id = trim($_COOKIE['classified_session']);
            //get registry for this session without invoking session object
            $session_reg = new geoRegistry();
            $session_reg->setName('sessions');
            $session_reg->setId($session_id);
            $session_reg->unSerialize();

            $ua = $session_reg->get('fb_' . $fb_app_id . '_IE_UA');
            if ($ua && $ua !== $_SERVER['HTTP_USER_AGENT']) {
                //fake using the ie UA
                trigger_error('DEBUG FACEBOOK: Yes, going to fake the UA.  Faked UA is: ' . $ua);
                $_SERVER['HTTP_USER_AGENT'] = $ua;
                define('FB_IE_FIX_UA', 1);
            } else {
                trigger_error("DEBUG FACEBOOK: NOT faking the UA.");
            }
            if (isset($_COOKIE['IEFIX_UIFB'])) {
                //cookie set, unset it as we only need it for one iteration
                setcookie('IEFIX_UIFB', '', time() - 100000, '/');
            }
        }
        trigger_error("DEBUG FACEBOOK:  including fb");
        //use FB php-sdk library
        require_once ADDON_DIR . 'social_connect/fb.php';

        $this->facebook =  new geoFacebook(array(
            'appId' => $fb_app_id,
            'secret' => $fb_app_secret,
        ));

        //explicitly specify the https cert to use with curl (some server configs don't auto-detect this)
        geoFacebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = ADDON_DIR . "social_connect/lib/facebook/php-sdk/src/fb_ca_chain_bundle.crt";

        // Get User ID
        $this->user = $this->facebook->getUser();
        trigger_error("DEBUG FACEBOOK:  after getUser");

        // We may or may not have this data based on whether the user is logged in.
        //
        // If we have a $user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.

        if ($this->user) {
            trigger_error("DEBUG FACEBOOK:  there is a user");
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $this->user_profile = $this->facebook->api('/me/?fields=email,name,first_name,last_name,locale');
                trigger_error("DEBUG FACEBOOK:  was able to get facebook user profile, so session still good.");
            } catch (FacebookApiException $e) {
                //user token thingy must have expired
                trigger_error('DEBUG FACEBOOK: Exception thrown trying to get user profile');
                $this->user = $this->user_profile = null;
            }
        }

        if ($this->user && false) {
            //Playground for trying stuff out... remove the && false in if statement to use
            trigger_error("DEBUG FACEBOOK:  Entering the playground!  Muahaha!");
            try {
                $testcall = array('method' => 'friends.getAppUsers');//"/$fb_app_id";

                $test = $this->facebook->api($testcall);
                $users = '';
                foreach ($test as $id) {
                    $users .= "<img src='https://graph.facebook.com/$id/picture' alt='' style='width: 20px;' /><br />";
                }
                echo "result of facebook->api($testcall) :<pre>" . print_r($test, 1) . "</pre><br />your friends using this app:<br />$users";
            } catch (FacebookApiException $e) {
                //oops!
                echo "got an error! $e<br />";
            }
        }

        //OK the main Geo session is not initialized yet...  Lets go ahead and manually
        //get the entry here

        //Init session & get session ID
        $session = geoSession::getInstance();
        $session_id = $session->initSession();

        trigger_error("DEBUG FACEBOOK:  after initsession");

        //figure out user ID
        $user_id = $session->getUserId();
        if ($user_id == 1) {
            //don't do nothin with admin user
            trigger_error("DEBUG FACEBOOK:  Don't do a thing for main admin user.");
            $this->user = $this->user_profile = null;
            return;
        }

        $db = DataAccess::getInstance();
        trigger_error("DEBUG FACEBOOK:  user_id: " . $user_id);
        if ($user_id) {
            //if user ID for this session, get the user info for that USER ID and see if
            //the facebook ID is set for that user.
            trigger_error("DEBUG FACEBOOK:  User already logged into Geo.");

            $user = $this->login_user = $db->GetRow("SELECT * FROM " . geoTables::logins_table . " WHERE `id`=?", array($user_id));//geoUser::getUser($user_id);
            if ($user['facebook_id'] && !$this->user) {
                //this person has a facebook ID but is not logged in via facebook
                //so DO NOT log them out of the software
                trigger_error("DEBUG FACEBOOK:  Logged into Geo with linked user, but not into FB, so log out of geo.");
                /*
                $session->logOut();
                //then force session to re-init
                $session->initSession(true);
                //then we're done.
                 */

                if (!strlen($user['password']) && isset($_GET['a']) && ($_GET['a'] == 4 || $_GET['a'] == 'cart')) {
                    //do a redirect to FB login to make sure it is connected, but only
                    //when user does not have password set
                    $login = $this->loginUrl();
                    $this->_goTo($login);
                }

                return;
            }
            if (!$this->user) {
                //nothing more to do, logged in normal way
                trigger_error("DEBUG FACEBOOK:  Logged into Geo with non-linked account, not logged into FB");
                return;
            }

            if ($this->user_profile['id'] != $user['facebook_id']) {
                //This matches when either FB id is not set for user, OR is
                //different than what they are logged in as...  Either way let
                //the setFacebookId() handle it.
                trigger_error("DEBUG FACEBOOK:  setting FB id ({$this->user_profile['id']} != {$user['facebook_id']}) for user." . print_r($user, 1));
                return $this->setFacebookId($this->user_profile['id'], $user_id);
            }

            if ($reg->get('fb_logout') && isset($_GET['a']) && $_GET['a'] == 17) {
                //logging out...  Log them out of FB as well if set to do so in
                //admin settings.  (can cause endless re-directs on some sites)
                $logout_link = $this->facebook->getLogoutUrl();
                $this->_goTo($logout_link);
            }
            trigger_error("DEBUG FACEBOOK: User is already logged in, all systems are go.");
            //Get this far, that means user matches FB user and all is good.
            $this->_fbUserInit();
            return;
        }
        //When it gets this far, means there is no user ID for this session (yet).
        if (!$this->user) {
            //Scenario: not logged into FB or Geo.
            //nothing to do
            trigger_error("DEBUG FACEBOOK:  Not logged into FB or Geo, nothing to do.");
            return;
        }

        //figure out what user matches the FB id
        $row = $db->GetRow("SELECT * FROM " . geoTables::logins_table . " WHERE `facebook_id`=?", array($this->user_profile['id']));
        if ($row) {
            //Scenario: Facebook is logged in, geo is not, log the user into the Geo system!
            trigger_error('DEBUG FACEBOOK: Found user by facebook_id');
            return $this->userLogin($row['id'], $session_id);
        }

        //Gets here, that means there is no user matching...
        //Check if there is an e-mail matching though
        $row = $db->GetRow("SELECT `id` FROM " . geoTables::userdata_table . " WHERE `email`=?", array($this->user_profile['email']));
        if ($row) {
            //imagine that...  e-mail matched up, go ahead and set that user
            trigger_error('DEBUG FACEBOOK: found user by email');
            if (!$this->setFacebookId($this->user_profile['id'], $row['id'])) {
                //that didn't work...
                return;
            }
            //log the user in!
            return $this->userLogin($row['id'], $session_id);
        }

        //Scenario:  logged into FB but alas, no user found for that FB user!
        //SO register a user!
        $user_id = $this->userRegister();
        if ($user_id) {
            //log that user in!
            trigger_error('DEBUG FACEBOOK: registered new user, logging them in');
            $this->userLogin($user_id, $session_id);
        } else {
            trigger_error('DEBUG FACEBOOK: register user failed so could not log them in');
        }
        //and, we're done!
    }

    /**
     * Call this when the user is a FB user.
     */
    private function _fbUserInit()
    {
        $db = DataAccess::getInstance();
        //set temporary setting to NOT require pass to edit user info
        $db->set_site_setting('info_edit_require_pass', false, false, false);
        //also set temporary setting to NOT allow changing password
        $db->set_site_setting('info_edit_password_no_edit', 1, false, false);
    }

    public function userLogin($user_id, $session_id)
    {
        trigger_error("DEBUG FACEBOOK: Top of user login");
        $db = DataAccess::getInstance();

        $user_id = (int)$user_id;
        $session_id = trim($session_id);
        if (!$user_id || $user_id == 1) {
            //don't think so!
            trigger_error("DEBUG FACEBOOK:  User ID is blank or is #1 so can't login..");
            $this->user = $this->user_profile = null;
            return;
        }
        $user = geoUser::getUser($user_id);
        if (!$user) {
            //this shouldn't happen...
            trigger_error("ERROR FACEBOOK: Trying to log user $user_id in, but geoUser::getUser() returns false..");
            $this->user = $this->user_profile = null;
            return;
        }

        if (!$user->status || (int)$user->status === 2) {
            //status is 0 meaning user is disabled, do not allow
            trigger_error("DEBUG FACEBOOK:  User is disabled, cannot log that user in.");
            $this->user = $this->user_profile = null;
            return;
        }

        $sql_vars = array ($user_id,'' . $user->level,$session_id);
        $db->Execute("UPDATE " . geoTables::session_table . " SET `user_id`=?, `level`=? WHERE `classified_session`=? AND `admin_session`='no' LIMIT 1", $sql_vars);

        trigger_error("DEBUG FACEBOOK:  Everything checks out, logging user in ($user_id) and forcing session re-init for session ($session_id).");

        //but FIRST serialize session so session vars are saved properly!!!
        geoSession::getInstance()->serialize();

        //force re-init session
        geoSession::getInstance()->initSession(true);

        //update the last login time and IP
        $sql = "UPDATE " . geoTables::userdata_table . " SET last_login_time = NOW(), last_login_ip = ? WHERE id=?";
        $db->Execute($sql, array(getenv('REMOTE_ADDR'), $user_id));

        $this->_fbUserInit();

        //let addons know login just happened
        geoAddon::triggerUpdate('session_login', array('userid' => $user_id, 'username' => $user->username, 'password' => ''));

        //Now, do a re-direct like they just logged in...
        require_once(CLASSES_DIR . 'authenticate_class.php');
        //make sure to do anything related to JIT stuff...
        Auth::jitTransferCart($user_id);

        $this->_fixUaForRedirect();

        //let the Auth class handle re-directing to the proper URL
        Auth::redirectAfterLogin();
        require GEO_BASE_DIR . 'app_bottom.php';
        exit;
        //our work here, is done.
    }

    private function _fixUaForRedirect()
    {
        if (!defined('FB_IE_FIX_UA')) {
            return;
        }
        $session_id = geoSession::getInstance()->getSessionId();
        if (!$session_id) {
            return;
        }
        DataAccess::getInstance()->execute(
            "UPDATE " . geoTables::session_table . " SET `ip`='0', `ip_ssl`='0' WHERE `classified_session`=?",
            array($session_id)
        );
        setcookie('IEFIX_UIFB', 1, 0, '/');
    }

    public function setFacebookId($fb_id, $user_id)
    {
        trigger_error("DEBUG FACEBOOK:  Top of setting FB id for existing user.");
        $user_id = (int)$user_id;
        $fb_id = trim($fb_id);
        if ($user_id <= 1) {
            //don't do anything with admin (or anon)
            trigger_error("DEBUG FACEBOOK:  user ID not good ($user_id), can't set FB id for that user..");
            $this->user = $this->user_profile = null;
            return false;
        }
        $db = DataAccess::getInstance();
        $user = $db->GetRow("SELECT * FROM " . geoTables::logins_table . " WHERE `id`=?", array($user_id));
        if (!$user) {
            //error!
            trigger_error("DEBUG FACEBOOK:  Could not find requested user ID so could not set FB id for that user ($user_id)");
            $this->user = $this->user_profile = null;
            return false;
        }

        //make sure not already set for another user
        $user_fb = $db->GetRow("SELECT * FROM " . geoTables::logins_table . " WHERE `facebook_id`=? AND `id`!=?", array ($fb_id, $user_id));

        if ($user_fb) {
            //already another user found with that FB id?!
            if ($user['facebook_id']) {
                //The facebook ID for session'd user is set, but different than
                //one according to FB stuff, and another user matches the other
                //FB id...
                //something weird is going on...
                $this->user = $this->user_profile = null;
                return false;
            }
            //another user already has this FB id
            if (isset($_GET['cancel_fb_link']) && $_GET['cancel_fb_link'] == 'yes') {
                //cancel the process...  kill the session which will do it for us.
                $this->facebook->clearAllPersistentData();
                //now do a redirect
                $this->_goTo($db->get_site_setting('classifieds_file_name'));
            } elseif (!isset($_POST['merge'])) {
                //prompt user
                //whether they want to merge accounts.
                $this->_mergePageDisplay($user_fb, $user);
            } elseif (isset($_POST['merge']) && $_POST['merge'] = 'yes') {
                //If they do want to merge them, then merge the accounts.
                //check the password
                $msgs = geoAddon::getText($this->auth_tag, $this->name);
                if (!geoPC::getInstance()->verify_credentials($user['username'], $_POST['verify'], false, false, true)) {
                    $errors['verify'] = $msgs['error_fb_usr_merge_invalid_pass'];
                    $this->_mergePageDisplay($user_fb, $user, $errors);
                }
                //password is verified, merge accounts together...
                $keep_id = $this->_mergeAccounts($user_fb['id'], $user['id']);
                if ($keep_id) {
                    $db->Execute("UPDATE " . geoTables::logins_table . " SET `facebook_id`=? WHERE `id`=?", array($fb_id, $keep_id));
                    $this->userLogin($keep_id, geoSession::getInstance()->getSessionId());
                } else {
                    $errors['verify'] = $msgs['error_fb_merge_internal'];
                    $this->_mergePageDisplay($user_fb, $user, $errors);
                }
            }
            return;
        }

        //no other users found matching it, lets see what it is set to currently
        if ($user['facebook_id'] && $user['facebook_id'] != $fb_id) {
            //Scenario:  FB ID for this user is set already, and does not match
            //user that is logged in, and this current FB ID is not assigned to another user
            //TODO: implement
            return;
        }
        trigger_error("DEBUG FACEBOOK:  Setting FB id ($fb_id) for user ($user_id).");
        //we get here, everything is cool, go ahead and just set FB id to this user
        $db->Execute("UPDATE " . geoTables::logins_table . " SET `facebook_id`=? WHERE `id`=?", array($fb_id, $user_id));
        return true;
    }

    private function _cleanUsername($username)
    {
        return trim(preg_replace('/[^-a-zA-Z0-9_. ]+/', '', geoString::removeAccents($username)));
    }

    public function userRegister()
    {
        trigger_error("DEBUG FACEBOOK:  Top of user register.");
        if (!$this->user || !$this->user_profile || empty($this->user_profile['email'])) {
            //nothing to register by
            trigger_error('DEBUG FACEBOOK: nothing to register by, need email.');
            $this->user = $this->user_profile = null;
            return false;
        }

        $db = DataAccess::getInstance();
        $reg = geoAddon::getRegistry($this->name);
        //to keep easy track of it, lets keep all info in a single array
        $user_info = array();

        //double check that e-mail is not already in system
        $check = $db->GetRow("SELECT * FROM " . geoTables::userdata_table . " WHERE `email`=?", array(trim($this->user_profile['email'])));
        if ($check) {
            //found someone in system already!
            trigger_error("DEBUG FACEBOOK: Cannot register, found another account with same e-mail ({$this->user_profile['email']}).");
            return false;
        }
        if (isset($this->user_profile['username'])) {
            //use the username if it is available.
            $username = $this->_cleanUsername($this->user_profile['username']);
        } else {
            $lastname = trim($this->user_profile['last_name']);
            $lastname = substr($lastname, 0, 1);
            $lastname = ($lastname) ? ' ' . $lastname : '';

            $username = $this->_cleanUsername($this->user_profile['first_name'] . $lastname);
            if (!$username) {
                //try the full name
                $username = $this->_cleanUsername($this->user_profile['name']);
            }

            if (!$username) {
                //try the first part before @ in the e-mail as a last resort
                $username = $this->_cleanUsername(substr($this->user_profile['email'], 0, strpos($this->user_profile['email'], '@')));
            }
        }

        if (!$username) {
            trigger_error("DEBUG FACEBOOK:  Could not auto-generate username based on info available, will have to use generic username.");
            $username = "facebook_user";
        }

        //make sure username doesn't already exist in the DB
        $query = $db->Prepare("SELECT * FROM " . geoTables::logins_table . " WHERE `username` LIKE ?");
        $number = '';
        do {
            $username_try = trim($username . ' ' . $number);
            $number = (int)$number + 1;
            $result = $db->GetRow($query, array($username_try));
        } while ($result && $result['username'] == $username_try);

        //we should have a good username now
        $user_info['facebook_id'] = $this->user_profile['id'];
        $user_info['username'] = $username_try;
        $user_info['email'] = trim($this->user_profile['email']);
        $user_info['firstname'] = trim($this->user_profile['first_name']);
        $user_info['lastname'] = trim($this->user_profile['last_name']);
        $user_info['country'] = '';
        //see if can figure out country based on local
        $locale = trim($this->user_profile['locale']);
        $country = substr($locale, strrpos($locale, '_') + 1);
        if ($country) {
            //see if any countries match that abbreviation
            $foundRegion = $db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE `billing_abbreviation`=?", array($country));
            //will set the region into the user a little later on, after we have the user ID
        }
        $user_info['date_joined'] = geoUtil::time();
        $user_info['communication_setting'] = $db->get_site_setting('default_communication_setting');
        $user_info['last_login_ip'] = $_SERVER['REMOTE_ADDR'];

        //figure out the group ID
        $group_id = (int)$reg->get('default_group');
        if ($group_id) {
            $group = $db->GetRow("SELECT * FROM " . geoTables::groups_table . " WHERE `group_id`=?", array($group_id));
        } else {
            //no group specified -- fall back on software default
            $group = $db->GetRow("SELECT * FROM " . geoTables::groups_table . " WHERE `default_group`=1");
        }
        //figure out price plans
        $user_info['group_id'] = (int)$group['group_id'];
        $user_info['price_plan_id'] = (int)$group['price_plan_id'];
        $user_info['auction_price_plan_id'] = (int)$group['auction_price_plan_id'];

        //Let's go!!!
        $login_data = array ($user_info['username'], $user_info['facebook_id']);
        $login_result = $db->Execute("INSERT INTO " . geoTables::logins_table . " SET `username`=?, `password`='', `status`=1, `facebook_id`=?", $login_data);
        if (!$login_result) {
            //oops, boo boo happened
            trigger_error("ERROR FACEBOOK SQL: Error running sql, message: " . $db->ErrorMsg());
            return false;
        }
        $user_id = (int)$db->Insert_Id();
        if (!$user_id) {
            //weird, this shouldn't happen
            trigger_error("ERROR FACEBOOK:  Inserted new user in logins table, but could not get the ID!");
            return false;
        }

        $price_plan_id = (geoMaster::is('classifieds')) ? $user_info['price_plan_id'] : $user_info['auction_price_plan_id'];
        $price_plan = $db->GetRow("SELECT * FROM " . geoTables::price_plans_table . " WHERE `price_plan_id`=?", array((int)$price_plan_id));

        $account_balance = ($price_plan['type_of_billing'] == 1) ? $price_plan['initial_site_balance'] : 0;

        //insert in userdata next
        $userdata_data = array (
            $user_id, $user_info['username'], $user_info['email'], $user_info['firstname'],
            $user_info['lastname'], $user_info['country'], geoUtil::time(), $user_info['communication_setting'],
            geoUtil::time(), $user_info['last_login_ip'], $account_balance . ''
        );
        $userdata_result = $db->Execute("INSERT INTO " . geoTables::userdata_table . " SET
			`id`=?, `username`=?, `email`=?, `firstname`=?, `lastname`=?, `country`=?,
			`date_joined`=?, `communication_type`=?, `last_login_time`=?, `last_login_ip`=?, `account_balance`=?", $userdata_data);

        if (!$userdata_result) {
            //error inserting!
            trigger_error("ERROR FACEBOOK: Error during new FB register, inserting in userdata, message: " . $db->ErrorMsg());
            return false;
        }

        //insert in user groups thingy
        $groups_data = array (
            $user_id, $user_info['group_id'], $user_info['price_plan_id'],
            $user_info['auction_price_plan_id'],
        );
        $groups_result = $db->Execute("INSERT INTO " . geoTables::user_groups_price_plans_table . " SET
			`id`=?, `group_id`=?, `price_plan_id`=?, `auction_price_plan_id`=?", $groups_data);

        if (!$groups_result) {
            trigger_error("ERROR FACEBOOK SQL:  Error inserting in user price plans table during register, error msg: " . $db->ErrorMsg());
            return false;
        }

        if ($price_plan['type_of_billing'] == 2 && $price_plan['free_subscription_period_upon_registration']) {
            //add free subscription period
            $expiration = (($price_plan['free_subscription_period_upon_registration'] * 86400) + geoUtil::time());
            $sql = "INSERT INTO " . geoTables::user_subscriptions_table . "
														(user_id,subscription_expire)
														values
														(?,?)";
            $free_subscription_result = $db->Execute($sql, array($user_id, $expiration));
            if (!$free_subscription_result) {
                trigger_error("ERROR FACEBOOK SQL:  Error inserting initial subscription when registering new user for FB login.");
                return false;
            }
        }

        //see if there should be a price plan expiration date set
        $plan_expirations_to_check = array();
        if (geoMaster::is('classifieds')) {
            array_push($plan_expirations_to_check, $user_info['price_plan_id']);
        }
        if (geoMaster::is('auctions')) {
            array_push($plan_expirations_to_check, $user_info['auction_price_plan_id']);
        }
        reset($plan_expirations_to_check);
        foreach ($plan_expirations_to_check as $expiration_price_plan_id) {
            $price_plan_expiration = $db->GetRow("SELECT * FROM " . geoTables::price_plans_table . " WHERE `price_plan_id`=?", array((int)$expiration_price_plan_id));
            if ($price_plan_expiration['expiration_type'] == 2) {
                //dynamic expiration of this price plan from the date of registration
                $expiration_date = (geoUtil::time() + ($price_plan_expiration['expiration_from_registration'] * 84600));

                $sql = "insert into " . geoTables::expirations_table . " (type,user_id,expires,type_id) values (2,?,?,?)";
                $expiration_data = array($user_id,$expiration_date,$expiration_price_plan_id);
                $plan_expiration_result = $db->Execute($sql, $expiration_data);
                if (!$plan_expiration_result) {
                    trigger_error("ERROR FACEBOOK: Error during new FB register, inserting in price plan expiration from registration, message: " . $db->ErrorMsg());
                }
            }
        }


        if ($foundRegion) {
            geoRegion::setUserRegions($user_id, geoRegion::getRegionWithParents($foundRegion));
        }

        //let other addons do their thing
        geoAddon::triggerUpdate('user_register', $user_info);

        if ($db->get_site_setting('send_register_complete_email_admin')) {
            $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
            $tpl->assign('user_data', $user_info);

            geoEmail::sendMail($db->get_site_setting('registration_admin_email'), "registration complete for {$user_info['username']} (Facebook)", $tpl->fetch('admin/emails/register_complete.tpl'), 0, 0, 0, 'text/html');
        }

        //got this far?  Registered successfully!
        trigger_error("DEBUG FACEBOOK:  Just successfully registered new user, user ID is $user_id");
        return $user_id;
    }

    public function getAppFriends()
    {
        if (!$this->user) {
            //not logged in or something
            return array();
        }

        //Ok, get list of friends
        try {
            $app_friends = $this->facebook->api(array('method' => 'friends.getAppUsers'));
            if (!$app_friends) {
                //no friends use this app
                return array();
            }
            //get all friends so we have their names
            $all_friends = $this->facebook->api('/me/friends');
        } catch (FacebookApiException $e) {
            //error getting something
            return array();
        }
        //echo "debug:  app friends: <pre>".print_r($app_friends,1).'</pre><br /><br />all friends:<pre>'.print_r($all_friends,1).'</pre>';
        $friends = array();
        foreach ($all_friends['data'] as $friend) {
            if (in_array($friend['id'], $app_friends)) {
                $friends[$friend['id']] = $friend['name'];
            }
        }
        return $friends;
    }

    public function loginUrl($redirect_uri = '')
    {
        trigger_error("DEBUG FACEBOOK: top of loginUrl in util");
        if (!$this->facebook) {
            trigger_error("DEBUG FACEBOOK: no facebook instance in util");
            //no fb to get login from
            return '';
        }
        if (!defined('FB_IE_FIX_UA') && strpos($_SERVER['HTTP_USER_AGENT'], 'rv:11') !== false) {
            //save session thingy
            trigger_error("DEBUG FACEBOOK: Saving UA as it appears to be IE11 which will switch UA on us");
            $var_name = 'fb_' . $this->facebook->getAppId() . '_IE_UA';
            geoSession::getInstance()->set($var_name, $_SERVER['HTTP_USER_AGENT']);
        }
        /*if ($redirect_uri) {
            //specified a return address, manually get this one
            return $this->facebook->getLoginUrl(array('scope'=>'email','redirect_uri'=>$redirect_uri));
        }*/
        //if (!isset($this->_loginUrl)) {
        //$this->_loginUrl = $this->facebook->getLoginUrl(array('scope'=>'email'));
        $this->_loginUrl = $this->facebook->getLoginUrl(['scope' => 'email']);
        //}
        return $this->_loginUrl;
    }

    private function _mergePageDisplay($otherUser, $thisUser, $errors = null)
    {
        if (isset($_GET['a']) && $_GET['a'] == 17) {
            //don't display this, let user log out
            return;
        }

        $session = geoSession::getInstance();
        $db = DataAccess::getInstance();
        $view = geoView::getInstance();

        require_once CLASSES_DIR . 'site_class.php';

        $site = Singleton::getInstance('geoSite');
        $site->classified_user_id = $session->getUserId();
        $site->language_id = $db->getLanguage();

        // get the variables
        $addon_name = $site->addon_name = $this->name;

        $page = 'merge_accounts';
        $site->page_id = "addons/{$addon_name}/{$page}";

        $tpl_vars = array();

        unset($thisUser['password'], $otherUser['password']);

        $tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
        $tpl_vars['user'] = $this->user;
        $tpl_vars['user_profile'] = $this->user_profile;
        $tpl_vars['other_user'] = $otherUser;
        $tpl_vars['this_user'] = $thisUser;
        $tpl_vars['errors'] = $errors;

        //so that it does not show the junk like profile pic, clear it
        $this->user = $this->user_profile = null;

        $view->setBodyTpl('pages/merge_accounts.tpl', $this->name)
            ->setBodyVar($tpl_vars);

        $site->display_page();

        include GEO_BASE_DIR . 'app_bottom.php';
        //we're done here...
        exit;
    }

    private function _mergeAccounts($usera, $userb)
    {
        //we're just guessing if it turns out userb is older we'll swich em
        trigger_error("DEBUG FACEBOOK:  Merging accounts!");
        $usera = geoUser::getUser($usera);
        $userb = geoUser::getUser($userb);

        if (!$usera || !$userb) {
            //can't go on
            trigger_error("DEBUG FACEBOOK:  usera/userb not valid, not able to proceed.");
            return false;
        }

        if ($usera->date_joined > $userb->date_joined) {
            //user B is older, keep user B
            $keep_id = (int)$userb->id;
            $merged_id = (int)$usera->id;
        } else {
            //user A is older, keep user A
            $keep_id = (int)$usera->id;
            $merged_id = (int)$userb->id;
        }

        if ($keep_id <= 1 || $merged_id <= 1 || $merged_id == $keep_id) {
            //just a failsafe
            trigger_error("DEBUG FACEBOOK: Cannot merge accounts, merged id $merged_id or keep id $keep_id is <=1 or same as each other.");
            return false;
        }
        $sqls = array();

        //delete userdata history
        $sqls[] = "DELETE FROM " . geoTables::userdata_history_table . " WHERE `id` = $merged_id";

        //communications message_to
        $sqls[] = "UPDATE " . geoTables::user_communications_table . " SET `message_to`=$keep_id WHERE `message_to` = $merged_id";

        //add filters
        $sqls[] = "UPDATE " . geoTables::ad_filter_table . " SET `user_id`=$keep_id WHERE `user_id`=$merged_id";

        //expired
        $sqls[] = "UPDATE " . geoTables::classifieds_expired_table . " SET `seller`=$keep_id WHERE `seller` = $merged_id";

        //get all orders user has placed
        $sqls[] = "UPDATE " . geoTables::order . " SET `buyer`=$keep_id WHERE `buyer` = $merged_id";

        //invoices
        $sqls[] = "UPDATE " . geoTables::transaction . " SET `user`=$keep_id WHERE `user`=$merged_id";

        //get current listings
        $sqls[] = "UPDATE " . geoTables::classifieds_table . " SET `seller`=$keep_id WHERE `seller` = $merged_id";

        //subscriptions expiration
        $sqls[] = "UPDATE " . geoTables::user_subscriptions_table . " SET `user_id`=$keep_id WHERE `user_id` = $merged_id";

        //recurring billing
        $sqls[] = "UPDATE " . geoTables::recurring_billing . " SET `user_id`=$keep_id WHERE `user_id`=$merged_id";

        //user tokens
        $sqls[] = "UPDATE " . geoTables::user_tokens . " SET `user_id`=$keep_id WHERE `user_id` = $merged_id";

        //user sessions
        $sqls[] = "UPDATE " . geoTables::session_table . " SET `user_id`=$keep_id WHERE `user_id` = $merged_id";

        //carts
        $sqls[] = "UPDATE " . geoTables::cart . " SET `user_id`=$keep_id WHERE `user_id`=$merged_id";

        if (geoMaster::is('auctions')) {
            //user's bids
            $sqls[] = "UPDATE " . geoTables::bid_table . " SET `bidder`=$keep_id WHERE `bidder` = $merged_id";

            //user's autobids
            $sqls[] = "UPDATE " . geoTables::autobid_table . " SET `bidder`=$keep_id WHERE `bidder` = $merged_id";

            //user's feedbacks
            $sqls[] = "UPDATE " . geoTables::auctions_feedbacks_table . " SET `rated_user_id`=$keep_id WHERE `rated_user_id` = $merged_id";
            //do "rater" as well
            $sqls[] = "UPDATE " . geoTables::auctions_feedbacks_table . " SET `rater_user_id`=$keep_id WHERE `rater_user_id` = $merged_id";
        }

        //delete group information (this one we can't duplicate
        $sqls[] = "DELETE FROM " . geoTables::user_groups_price_plans_table . " WHERE `id` = $merged_id";

        //delete login information - do this and userdata last, in case there is so much
        //that not everything is deleted in one go, they can go through the delete process
        //until everything is able to be removed.
        $sqls[] = "DELETE FROM " . geoTables::logins_table . " WHERE `id` = $merged_id";

        //delete userdata
        $sqls[] = "DELETE FROM " . geoTables::userdata_table . " WHERE `id` = $merged_id";

        $db = DataAccess::getInstance();
        foreach ($sqls as $sql) {
            if (!$db->Execute($sql)) {
                //that's not good!
                trigger_error("ERROR FACEBOOK SQL:  Sql error, sql: $sql Error: " . $db->ErrorMsg());
                //there shouldn't be a reason for any queries to fail so if they do fail,
                //do not continue.
                return false;
            }
        }
        return $keep_id;
    }

    public function getUserInfo($userId)
    {
        $userId = (int)$userId;
        if ($userId <= 1) {
            return array();
        }
        return DataAccess::getInstance()->GetRow("SELECT l.facebook_id, u.facebook_reveal FROM " . geoTables::logins_table . " as l, " . geoTables::userdata_table . " as u
			WHERE l.id=u.id AND l.id='{$userId}'");
    }

    private function _goTo($url)
    {
        header("Location: $url");
        require GEO_BASE_DIR . 'app_bottom.php';
        die('Redirecting...');
    }
}
