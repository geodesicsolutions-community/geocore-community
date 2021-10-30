<?php

//authenticate_class.php


require_once CLASSES_DIR . 'site_class.php';

class Auth extends geoSite
{

       var $error;
       var $login_cookie_time;
       var $classified_user_id;
       var $username;
       var $classified_level;
       var $auth_messages;
       var $error_messages;
       var $error_found;

       var $messages = array();

       var $notify_data;

       var $debug_auth = 0;

//#############################################################################

    function login_form($db, $username = 0, $password = 0, $encode = 0, $must_login = 0)
    {
        $this->page_id = 39;
        $this->get_text();
        $tpl_vars = array();

        if ($encode) {
            $session_timeout = false;
            //check to see if stopped in middle of something
            if (isset($_GET['set_type']) || isset($_GET['set_cat']) || isset($_GET['set_details']) || isset($_GET['set_images']) || isset($_GET['set_trans_details'])) {
                //on one of the first steps when forced to log in
                $session_timeout = true;
            }
            if (isset($_GET['b'])) {
                if ($_GET['b'] == 'ad_accepted' || $_GET['b'] == 'edit_category' || $_GET['b'] == 'edit_details' || $_GET['b'] == 'edit_image' || $_GET['b'] == 'billing_accepted') {
                    //clicked edit button when forced to log in
                    $session_timeout = true;
                }
            }

            if (!isset($this->error_messages['cookie']) && $session_timeout) {
                //show timeout message
                $tpl_vars['error'] = $this->messages[500158];
            }
        }
        if ($this->error_messages['cookie']) {
            $tpl_vars['error'] .= urldecode($this->error_messages['cookie']);
            $session = true;
            include(GEO_BASE_DIR . 'get_common_vars.php');
            if ($session->getStatus() == 'changed') {
                //only display the error message, nothing else.
                $tpl_vars['only_show_error'] = true;
            }
        }

        if ($this->db->get_site_setting('use_ssl_in_login')) {
            $tpl_vars['form_target'] = $this->db->get_site_setting('classifieds_ssl_url') . "?a=10";
        } else {
            $tpl_vars['form_target'] = $this->db->get_site_setting('classifieds_file_name') . "?a=10";
        }

        if ($encode) {
            //special case: since $encode is sent urlencoded through user input, an encoded XSS payload would bypass normal input checking
            //a "better" fix might be to re-tool the way that information gets passed around entirely
            //but manually running specialchars() should be enough to close the vulnerability for now
            $encode = geoString::specialChars($encode);

            $tpl_vars['encode'] = $encode;
        }

        $tpl_vars['auth_messages'] = $this->auth_messages;
        $tpl_vars['error_messages'] = $this->error_messages;

        if ($must_login == 1) {
            //contact seller
            $must_login = $this->messages[2343];
        } elseif ($must_login == 2) {
            //message friend
            $must_login = $this->messages[2344];
        } elseif ($must_login == 3) {
            //view listings
            $must_login = $this->messages[3266];
        } else {
            $must_login = '';
        }
        $tpl_vars['must_login'] = $must_login;

        if ($username) {
            $tpl_vars['username'] = geoString::specialChars($username);
        }
        //TODO: Move this to a generic addon hook!
        $secure = geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('login')) {
            $security_text =& geoAddon::getText('geo_addons', 'security_image');
            $error = $this->error_messages['securityCode'];
            $section = "login";
            $tpl_vars['securityImageHTML'] = $secure->getHTML($error, $security_text, $section, false);
            geoView::getInstance()->addTop($secure->getJs());
        }

        if ($this->db->get_site_setting('forgot_password')) {
            //only show forgot password link if the feature is turned on.
            $tpl_vars['forgotPasswordLink'] = $this->db->get_site_setting('classifieds_file_name') . "?a=18";
        }

        $tpl_vars['registrationLink'] = ($this->db->get_site_setting('use_ssl_in_registration')) ? $this->db->get_site_setting('registration_ssl_url') : $this->db->get_site_setting('registration_url');

        $this->auth_messages["login"] = 0;
        $this->error_messages["username"] = 0;
        $this->error_messages["password"] = 0;
        $this->error_messages["securityCode"] = 0;

        $tpl_vars['addons_bottom'] = geoAddon::triggerDisplay('display_login_bottom', array('encode' => $encode), geoAddon::RETURN_STRING);

        //make sure the javascript needed for the cookie validate step is cached
        $view = geoView::getInstance();
        $view->allowEmail = 1;
        $view->addTop("
<script type=\"text/javascript\">
	//<![CDATA[
	gjUtil.autoSubmitForm ('validate_login', '?a=10&back=no');
	//]]>
</script>
");
        $view->setBodyTpl('login_form.tpl', '', 'authentication')->setBodyVar($tpl_vars);

        $this->display_page();
        return true;
    } //end of function login_form

//#############################################################################
    function validate_login_form($info, $encode)
    {
        $session = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $username = (isset($info['username'])) ? geoString::specialCharsDecode($info['username']) : '';
        $password = (isset($info['password'])) ? geoString::specialCharsDecode($info['password']) : '';

        //trim inputs for excess whitespace
        $username = trim($username);
        $password = trim($password);

        $this->page_id = 39;
        $this->get_text();
        $this->body .=  "<form action=\"" . $this->db->get_site_setting('classifieds_file_name') . "?a=10\" method=\"post\" id=\"validate_login\">\n";

        if ($encode) {
            //special case: since $encode is sent urlencoded through user input, an encoded XSS payload would bypass normal input checking
            //a "better" fix might be to re-tool the way that information gets passed around entirely
            //but manually running specialchars() should be enough to close the vulnerability for now
            $encode = geoString::specialChars($encode);

            $this->body .=  "<input type=\"hidden\" name=\"c\" value=\"" . urlencode($encode) . "\" />\n";
        }
        $this->body .=  "<input type=\"hidden\" name=\"b[username]\" value=\"" . geoString::specialChars($username) . "\" />\n";
        $this->body .=  "<input type=\"hidden\" name=\"b[pvalidate]\" value=\"" . geoString::specialChars($password) . "\" />\n";

        $secure = geoAddon::getUtil('security_image');

        if ($secure && $secure->check_setting('login') && (isset($info['securityCode']) || isset($_POST['g-recaptcha-response']))) {
            if (isset($_POST['g-recaptcha-response'])) {
                //silly recaptcha isn't able to change field names...
                $this->body .= "<input type='hidden' name='g-recaptcha-response' value=\"" . geoString::specialChars($_POST['g-recaptcha-response']) . "\" />\n";
            } else {
                $this->body .=  "<input type=\"hidden\" name=\"b[securityCode]\" value=\"" . geoString::specialChars($info['securityCode']) . "\" />\n";
            }
        }
        $this->body .= "<input type=\"hidden\" name=\"b[sessionId]\" value=\"" . $session->getSessionId() . "\" />";
        $this->body .=  urldecode($this->messages[500151]) . "</form>\n"; //text is inside form...

        $view = geoView::getInstance();
        $view->allowEmail = 1;
        $view->addTop(
            "
<script type=\"text/javascript\">
	//<![CDATA[
	//2 seconds after page is done loading, auto submit the form.
	gjUtil.autoSubmitForm ('validate_login', '?a=10&back=no');
	//]]>
</script>
"
        );

        $this->display_page();
        return true;
    }

    /**
     * Log the user in and update the user's session in the database.
     *
     * @param array $info Associative array:
     *  array('username'=>'user','pvalidate'=>'plaintext_pass','sessionId'=>'session_id') (sessionId optional)
     * @param boolean $ignore_detected_cookie_problems If true, will still allow login even if there are potential cookie problems. (mostly for use
     *  by custom login routines)
     * @return int|boolean The user's id if login was successful, false otherwise.  If false, the reason should be stored
     *  in associative array in $auth->auth_messages and/or $auth->error_messages
     */
    function login($info, $ignore_detected_cookie_problems = false)
    {
        $session = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $status = $session->getStatus();
        $username = (isset($info['username'])) ? geoString::specialCharsDecode($info['username']) : '';
        $password = (isset($info['pvalidate'])) ? geoString::specialCharsDecode($info['pvalidate']) : '';
        $passedSessionId = (isset($info['sessionId'])) ? $info['sessionId'] : false;
        $sessionId = $session->getSessionId();

        trigger_error('DEBUG AUTH STATS: Top of login function.');

        //make sure the product configuration is loaded.
        //and the db connection.
        if (!isset($this->db) || !is_object($this->db)) {
            $this->db = DataAccess::getInstance();
        }

        //make sure authorization is loaded.
        if (!isset($this->product_configuration) || !is_object($this->product_configuration)) {
            $this->product_configuration = geoPC::getInstance();
        }

        $this->page_id = 39;
        $this->get_text();
        if (!$sessionId) {
            trigger_error('DEBUG AUTH STATS: Login failed, problem with session id.');
            return false;
        }
        $this->error_found = 0;
        $this->auth_messages["login"] = 0;
        $this->error_messages["username"] = 0;
        $this->error_messages["password"] = 0;
        $this->error_messages["securityCode"] = 0;
        $this->error_messages["cookie"] = 0;

        $cookie_status = $session->getStatus();
        if ($cookie_status != 'confirmed' || strlen(trim($sessionId)) == 0) {
            //something wrong with cookie??
            if ($cookie_status == 'new') {
                $this->error_messages['cookie'] = $this->messages[500149]; //seems to be no cookies
            } else {
                //must be that cookie could not be updated...
                $this->error_messages['cookie'] = $this->messages[500150]; //error updating message
            }
            if (!$ignore_detected_cookie_problems) {
                //Allow outside to force login even if there are potential cookie problems.
                $this->error_found ++;
            }
        }

        if (strlen(trim($username)) == 0) {
            $this->error_messages["username"] = $this->messages[337];
            $this->error_found++;
        }

        if (strlen(trim($password)) == 0) {
            $this->error_messages["password"] = $this->messages[338];
            $this->error_found++;
        }

        $secure_image = geoAddon::getUtil('security_image');
        if ($secure_image && $secure_image->check_setting('login')) {
            if (!$secure_image->check_security_code($_POST["b"]["securityCode"])) {
                $this->error_messages['securityCode'] = "error";
                $this->error_found++;
            }
        }


        if ($this->error_found > 0) {
            $this->auth_messages["login"] = $this->messages[341];
            return false;
        }



        if ($this->debug_auth) {
            echo $this->error_found . " is the error_found<br />\n";
        }

        //Note: verifying status further down so don't have the verify_credentials
        //verify the status
        $login_data = $this->product_configuration->verify_credentials($username, $password, false, true, true, false);
        if ($login_data === false) {
            //username and password do not match!
            $this->auth_messages["login"] = $this->messages[341];
            return false;
        }
        //To fix str case of username
        $username = $login_data['username'];
        if ($login_data['status'] == 1) {
            $sql = "select level,email,firstname,lastname from " . $this->db->geoTables->userdata_table . " where id = ?";
            if ($this->debug_auth) {
                echo $sql . "<br />\n";
            }
            $level_result = $this->db->Execute($sql, array($login_data['id']));
            if (!$level_result) {
                //$this->body .=  $sql." is the query<br />\n";
                $this->auth_messages["login"] = $this->messages[341];
                return false;
            } elseif (($level_result->RecordCount() == 0) || ($level_result->RecordCount() > 1)) {
                //$this->body .=  $sql." is the query<br />\n";
                $this->auth_messages["login"] = $this->messages[341];
                return false;
            } else {
                $show_level = $level_result->FetchNextObject();
                $ip_field = $session->getIpField();
                $sql = "update geodesic_sessions set
					user_id = ?,
					level = ?
					where `classified_session` = ? and `$ip_field` = ? AND `admin_session`='No'";
                //get ip
                $ip = $session->getUniqueUserInfo($sessionId);
                $session_result = $this->db->Execute($sql, array($login_data['id'], $show_level->LEVEL, $sessionId, $ip));
                if ($this->debug_auth) {
                    echo $sql . "<br />\n";
                }
                if (!$session_result) {
                    $this->body .=  $sql . " is the query<br />\n";
                    $this->auth_messages["login"] = $this->messages[341];
                    return false;
                }

                $this->userid = $login_data['id'];
                $this->level = $show_level->LEVEL;
                $this->email_address = $show_level->EMAIL;
                $this->firstname = $show_level->FIRSTNAME;
                $this->lastname = $show_level->LASTNAME;

                //grab ip and time
                $sql = "UPDATE " . geoTables::userdata_table . " SET last_login_time = NOW(), last_login_ip = ? WHERE id=?";
                $this->db->Execute($sql, array(getenv('REMOTE_ADDR'), $login_data['id']));

                geoAddon::triggerUpdate('session_login', array('userid' => $login_data['id'], 'username' => $username,    'password' => $password ));

                Auth::jitTransferCart($login_data['id']);

                return $login_data['id'];
            }
        } else {
            $this->auth_messages["login"] = $this->messages[345];
            return false;
        }
    } //end of function login

    /**
     * Method that will transfer a "JIT cart" (just in time) to the given USER Id,
     * based on the current session.  The updates to change the user ID on the session
     * in the database should be called prior to this.
     *
     * @param int $user_id The user ID to transfer the cart to
     * @since Version 7.2.2
     */
    public static function jitTransferCart($user_id)
    {
        $currentSession = geoSession::getInstance();
        $user_id = (int)$user_id;
        if (!$currentSession->get('jit_suspend') || !$user_id) {
            //nothing to do, current session does not have a pending JIT thingy
            //Or problem with user_id passed in.
            return;
        }
        $db = DataAccess::getInstance();
        //restore suspended cart / switch it to this user
        $cart_id = (int)$currentSession->get('jit_suspend');

        //figure out which cart step we're going to!
        $new_step = 'cart'; //fallback -- if nothing more important happens, send user to main cart page

        //see if this user is eligible to list under multiple price plans
        //if yes, roll back the step to pp selection
        $userObj = geoUser::getUser($user_id);
        if (!$userObj) {
            //problem with user, can't continue...
            trigger_error("ERROR LOGIN: could not get user object for user ID $user_id, so cannot transfer the JIT cart");
            return;
        }
        $group = $userObj->group_id;
        $sql = "SELECT `main_type` FROM " . geoTables::cart . " WHERE `id`=?";
        $item_type = $db->GetOne($sql, array($cart_id));

        if ($item_type) {
            //use "jit_after" - that is step that will basically make it
            //proceed to the next step after the JIT is logged in

            $new_step = $item_type . ':jit_after';
        }
        //NOTE: figuring out "what to do next" is job of jit_after step...

        //now grab the suspended cart and assign it to the current user
        $sql = "UPDATE " . geoTables::cart . " SET `session` = 0, `user_id` = ?, `step` = ? WHERE `id` = ?";
        $result = $db->Execute($sql, array($user_id, $new_step, $cart_id));

        //NOTE: The user ID for the order is updated automatically by the cart when
        //the cart session is initialized on the next page load.  We just need to update
        //the main cart info to attach to the new user, the cart *should* automatically do the rest.

        //delete any "extra" cart sessions this user has
        //such as if he started a cart, logged out, then started a JIT cart
        //in that case, he's prolly forgotten about the old one, so supercede it with this
        $sql = "DELETE FROM " . geoTables::cart . " WHERE `user_id` = ? AND `id` <> ?";
        $result = $db->Execute($sql, array($user_id, $cart_id));
    }

    function lostpassword($db, $info = 0)
    {
        //make sure the product configuration is loaded.
        //and the db connection.
        if (!isset($this->db) || !is_object($this->db)) {
            if (strlen(PHP5_DIR) > 0) {
                $this->db = DataAccess::getInstance();
            } else {
                $this->db =& DataAccess::getInstance();
            }
        }
        if (!$this->db->get_site_setting('forgot_password')) {
            //this feature disabled.
            return false;
        }
        //make sure authorization is loaded.
        if (!isset($this->product_configuration) || !is_object($this->product_configuration)) {
            if (strlen(PHP5_DIR) > 0) {
                $this->product_configuration = geoPC::getInstance();
            } else {
                $this->product_configuration =& geoPC::getInstance();
            }
        }

        $secure = geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('forgot_pass')) {
            if (!$secure->check_security_code($info["securityCode"])) {
                $security_text =& geoAddon::getText('geo_addons', 'security_image');
                $this->error_message = $security_text['error'];
                return false;
            }
        }

        $this->page_id = 40;
        $this->get_text();
        $this->page_id = 41;
        $this->get_text();

        if (!strlen($info['email'])) {
            //something went wrong. email not given or not found in database
            $this->error_message = $this->messages[351];
            return false;
        }

        $sql = "SELECT `id` FROM " . geoTables::userdata_table . " WHERE `email` = ? AND id != 1";
        $user_id = (int)$this->db->GetOne($sql, array($info["email"]));
        if (!$user_id || $user_id === 1) {
            trigger_error('ERROR SQL AUTH: Could not get user info.  sql:' . $sql . ' db reported:' . $this->db->ErrorMsg());
            $this->error_message = urldecode($this->messages[351]);
            return false;
        }

        $sql = "SELECT username,password,hash_type,salt FROM " . geoTables::logins_table . " WHERE id = ?";
        $userInfo = $this->db->GetRow($sql, array($user_id));
        if (!$userInfo) {
            $this->error_message = urldecode($this->messages[351]);
            return false;
        }
        if (strlen($userInfo['password']) == 0) {
            //cannot reset password...  probably fb connect, but could be anythign...
            $this->error_message = $this->messages[502066];
            return false;
        }
        if ($userInfo['hash_type'] !== 'core:plain') {
            //replace the password with a generated password.
            $pass = $this->product_configuration->generate_new_pass(8);
            $hash_type = $this->db->get_site_setting('client_pass_hash');
            $hashed_pass = $this->product_configuration->get_hashed_password($userInfo['username'], $pass, $hash_type);
            $salt = '';
            if (is_array($hashed_pass)) {
                $salt = $hashed_pass['salt'];
                $hashed_pass = $hashed_pass['password'];
            }
            $sql = 'UPDATE ' . geoTables::logins_table . ' SET password = ?, hash_type = ?, salt = ? WHERE id = ? LIMIT 1';
            trigger_error('DEBUG SQL: Query: ' . $sql . ' username:' . $userInfo['username']);
            $pass_update_result = $this->db->Execute($sql, array($hashed_pass, $hash_type, $salt, $user_id));
            if (!$pass_update_result) {
                //if this happens, we are in trouble!
                return false;
            }
            //changing user info...

            geoAddon::triggerUpdate('user_edit', array('old_username' => $userInfo['username'], 'username' => $userInfo['username'], 'old_password' => $userInfo['password'], 'password' => $pass));
        } else {
            //it seems the password is in plaintext, so just send the password.
            $pass = $userInfo['password'];
        }
        $mailto = $info["email"];
        $subject = $this->messages[707];
        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('introduction', $this->messages[708]);
        $tpl->assign('usernameLabel', $this->messages[709]);
        $tpl->assign('username', $userInfo['username']);
        $tpl->assign('passwordLabel', $this->messages[710]);
        $tpl->assign('password', $pass);
        $message = $tpl->fetch('lost_password.tpl');
        geoEmail::sendMail($mailto, $subject, $message, 0, 0, 0, 'text/html');

        return true;
    } //end of function lostpassword

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function lostpassword_form($db, $display_success = 0)
    {
        $db = DataAccess::getInstance();

        $this->page_id = 40;
        $this->get_text();

        $view = geoView::getInstance();

        $tpl_vars = array();

        if (!$this->db->get_site_setting('forgot_password')) {
            //password recovery tool turned off
            $tpl_vars['no_recovery'] = $this->messages[500105];
        } else {
            $tpl_vars['formTarget'] = $db->get_site_setting('classifieds_file_name') . "?a=18";
            if (strlen($this->error_message) > 0) {
                $tpl_vars['error_message'] = $this->error_message;
            }
            $tpl_vars['display_success'] = $display_success;
        }

        $secure =& geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('forgot_pass')) {
            $tpl_vars['security_image'] = $secure->getHTML($this->error_message, null, 'forgot_pass', false);
            $view->addTop($secure->getJs());
        }

        $view->setBodyTpl('lost_password.tpl', '', 'authentication')->setBodyVar($tpl_vars);
        $this->display_page();
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function already_logged_in($db, $user_id = 0)
    {
        $this->page_id = 39;
        $this->get_text();
        $tpl = new geoTemplate('system', 'authentication');

        $tpl->assign('title', $this->messages[343]);
        $tpl->assign('css', 'page_description');
        $tpl->assign('link', $this->db->get_site_setting('classifieds_url') . "?a=17");
        $tpl->assign('error', $this->messages[344]);

        $this->body .=  $tpl->fetch('error.tpl');
        $this->userid = $user_id;
        $this->display_page();
    } //end of function auth_error

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function reset_language($db, $language_id = 0)
    {
        if ($language_id) {
            $this->language_id = $language_id;
        } else {
            $this->language_id = 1;
        }
    }

    /**
     * Handy little method that takes an array and converts it into a string
     * expected by {@link Auth::login_form} 4th parameter (the URL to return to)
     *
     * Note that this does NOT do any cleanup.
     *
     * @param array $vars Array to convert, if not specified, will use $_GET
     * @return string
     */
    public static function generateEncodedVars($vars = null)
    {
        if ($vars === null) {
            $vars = $_GET;
        }
        if (count($vars) == 0) {
            return '';
        }
        return implode('*and*', self::_encodeRecursive($vars));
    }

    /**
     * Does a re-direct after the user has logged into the software.
     *
     * @since Version 6.0.2
     */
    public static function redirectAfterLogin($user_to_redirect_to = 0, $redirect_to_storefront = 0)
    {
        $db = DataAccess::getInstance();
        $session = geoSession::getInstance();
        $base = geoFilter::getBaseHref() . $db->get_site_setting('classifieds_file_name');
        if (isset($_REQUEST["c"]) && $_REQUEST["c"]) {
            //take them to some location
            $find = array('*is*','*and*');
            $replace = array ('=','&');
            $c = str_replace($find, $replace, urldecode($_REQUEST["c"]));
            header("Location: {$base}?$c");
        } else {
            if ($_COOKIE['login_trackback']) {
                //going back to the page that referred user to login process
                $destination = geoString::fromDB($_COOKIE['login_trackback']);
                setcookie('login_trackback', '', 1, '/'); //kill trackback cookie
                header("Location: " . $destination);
            } elseif ($session->get('jit_suspend')) {
                //kill value
                $session->set('jit_suspend', false);
                //redirect to cart
                header('Location: ' . $base . '?a=cart');
            } elseif ($user_to_redirect_to) {
                //redirect to the storefront for the user attached to
                //first get the user id of the user attached to
                //echo "inside user_to_redirect_to which is: ".$user_to_redirect_to."<br>\n";
                if ($redirect_to_storefront) {
                    //redirect to storefront
                    header("Location: " . $base . "?a=ap&addon=storefront&page=home&store=" . $user_to_redirect_to);
                } else {
                    header("Location: " . $base . "?a=6&b=" . $user_to_redirect_to);
                }
            } elseif ($db->get_site_setting('post_login_page') == 2 && $db->get_site_setting('post_login_url')) {
                //URL to go to is specified.
                header("Location: {$db->get_site_setting('post_login_url')}");
            } elseif ($db->get_site_setting('post_login_page') == 0) {
                //redirect to my account
                if ($db->get_site_setting('use_ssl_in_user_manage')) {
                    //must be non-sub-domain as SSL cert will fail otherwise
                    $base = $db->get_site_setting('classifieds_ssl_url');
                }
                header("Location: {$base}?a=4");
            } elseif ($db->get_site_setting('post_login_page') == 1) {
                //redirect to home page
                header("Location: $base");
            }
        }
    }

    /**
     * Used by {@link Auth::urlEncodeVars()}
     *
     * @param array $vars Assoc. array of vars
     * @param string $parentKey the parents key
     * @param array $parts the parts already encoded so far.
     * @return array
     */
    private static function _encodeRecursive($vars, $parentKey = null, $parts = array())
    {
        foreach ($vars as $key => $value) {
            if ($parentKey) {
                $key = "{$parentKey}[$key]";
            }
            if (is_array($value)) {
                $parts = self::_encodeRecursive($value, $key, $parts);
            } elseif (strlen($value . '')) {
                $parts[$key] = "$key*is*$value";
            }
        }
        return $parts;
    }
} //end of class Auth
