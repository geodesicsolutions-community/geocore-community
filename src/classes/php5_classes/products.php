<?php

/**
 * Used for database access, and other core-level functionality.
 *
 * @package System
 */

/**
 * Make sure the DataAccess class is included, so we have a db connection.
 */
require_once(CLASSES_DIR . PHP5_DIR . 'DataAccess.class.php');

/**
 * Used for login credential validation, password hashing, etc. along with
 * software licensing.
 *
 * Works directly with {@link geoSession} and {@link DataAccess}, and requires (like
 * most geo classes) that the software is initialized using app_top.common.php.
 *
 * Since this class is encoded, if you have any questions at all about any of the
 * methods contained in here, send in a support ticket and we will answer if possible.
 *
 * @package System
 */
final class geoPC
{
    /**
     * Used internally.
     *
     * NOTE:  Any vars that are not initialized here, are stored secretly inside
     * a static var inside a method to prevent showing the values with print_r
     *
     * @internal
     */
    private $db;

    /**
     * Used to store singleton instance of geoPC
     * @var geoPC
     * @internal
     */
    private static $_instance;

    /**
     * Gets instance of the geoPC class
     * @param string|null $type used internally.
     * @return geoPC
     */
    final public static function getInstance($type = null)
    {
        if ($type !== 'geoUpdateFactory') {
            $type = 'geoPC';
        }
        if (!isset(self::$_instance[$type])) {
            $c = __class__;

            self::$_instance[$type] = new $c($type);
        }
        return self::$_instance[$type];
    }
    /**
     * Starts up the geoPC.  Private on purpose to prevent creating a new geoPC
     * object outside of getInstance()
     *
     * @param string $type
     * @internal
     */
    private function __construct($type)
    {
        // GUTTED
        $this->db = DataAccess::getInstance();
    }

    public function discover_type()
    {
        // GUTTED
        return true;
    }

    /**
     * Performs maintenance actions specific to GeoTurbo.
     * @internal
     */
    public static function GTMaint()
    {
        // GUTTED
        return true;
    }

    /**
     * Validates a Geo addon license.  This is not usable by 3rd party addons.
     *
     * @param string $addon
     * @param string $secret
     * @param string $prefix
     * @param string $license_key
     * @return bool
     * @since Version 6.0.0
     * @deprecated Since version 7.0.3, since all addons are now included
     * @internal
     */
    public function validateAddon($addon, $secret, $prefix = '', $license_key = '')
    {
        // GUTTED
        return true;
    }

    /**
     * Get addon custom fields for license key data for addon.  Should only be used
     * after a call to validateAddon.  Only used internally by Geo addons.
     * @param string $addon
     * @param string $secret
     * @return array
     * @since Version 6.0.0
     * @internal
     */
    public function getAddonFields($addon, $secret)
    {
        // GUTTED
        return [];
    }

    /**
     * Used to display text when needing to agree that license is only used on one place
     *
     * @param string $addon
     * @return boolean|string
     * @internal
     */
    public function mustAgree($addon = '')
    {
        // GUTTED
        return false;
    }

    /**
     * gets license errors for display.
     * @param string $addon
     * @return string|array
     */
    public function errors($addon = '')
    {
        // GUTTED
        return [];
    }

    /**
     * Get the server IP address
     *
     * @return string|boolean string on success; boolean on failure
     */
    public function server_addr()
    {
        $options = array('SERVER_ADDR', 'LOCAL_ADDR');
        foreach ($options as $key) {
            if (isset($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }

        return false;
    }

    /**
     * Determine if we are running windows or not.
     *
     * @return boolean
     */
    public static function is_windows()
    {
        //store statically so don't need to keep getting over and over
        static $isWin = null;
        if ($isWin === null) {
            $isWin = (strtolower(substr(php_uname(), 0, 7)) == 'windows');
        }
        return $isWin;
    }

    /**
     * Debug - prints a formatted array
     *
     * @param array $stack The array to display
     * @param boolean $stop_execution
     * @return string
     */
    public function pr($stack, $stop_execution = true)
    {
        $formatted = '<pre>' . var_export((array)$stack, 1) . '</pre>';

        if ($stop_execution) {
            die($formatted);
        }

        return $formatted;
    }

    /**
     * You give it a "full domain" and it gives you back the part that validates
     * on the license key.
     *
     * @param string $fullDomain The full domain, minus http and all that jazz
     * @param string $addon
     * @return string The right-most part that matches a domain on the license
     * @since Version 5.0.3
     * @internal
     */
    public function validateMainDomain($fullDomain, $addon = '')
    {
        // GUTTED
        // domain not part of license, so use a config setting:
        require(GEO_BASE_DIR . 'config.default.php');
        if (!empty($domain)) {
            // Should be like:
            // $domain = 'example.com'
            return $domain;
        }
        // no domain setting, guess by using top 2 parts of full domain
        // (if this does not work for your domain, set the $domain in config.php)
        $parts = array_reverse(explode('.', $fullDomain));
        // cut off anything more than 2 parts
        $parts = array_slice($parts, 0, 2);
        return implode('.', $parts);
    }

    /**
     * Gets host name, and cleans it up to be suitable for license checks.  Mostly
     * used internally, but has public access so can be used by outside for whatever
     * reason.
     *
     * @param string $host
     * @return string
     * @since Versio 5.0.3
     */
    public static function cleanHostName($host, $preservePort = false)
    {
        if (!$preservePort) {
            //remove the :80 and similar from end of host
            $host = preg_replace('/\:[0-9]{2,4}$/', '', $host);
        }
        //remove . at end of domain name if it is there.
        $host = (substr($host, -1) == '.') ? substr($host, 0, -1) : $host;
        //make lowercase to prevent MyDomain from being invalidated.
        $host = strtolower($host);
        return $host;
    }

    /**
     * Shows the contents of site_off.htm, or if that file is not found,
     * shows the message "Site is under Maintenence, please check back later."
     */
    public function show_site_off()
    {
        $page = file_get_contents('site_off.htm');
        if (!$page) {
            echo 'Site is under Maintenence, please check back later.';
        } else {
            echo $page;
        }
    }

    /**
     * Returns true if Print is enabled
     *
     * @return boolean
     * @deprecated 7.0
     */
    public static function is_print()
    {
        // GUTTED
        // I think this is defunct mode but leaving in, you probably don't want to be print product
        return false;
    }

    /**
     * Returns true if both classifieds and auctions are enabled
     *
     * @deprecated by GeoCore
     * @return boolean
     * @deprecated 7.0
     */
    public static function is_class_auctions()
    {
        // NOTE: uses site settings
        return (geoMaster::is('classifieds') && geoMaster::is('auctions'));
    }

    /**
     * Returns true if Auctions are enabled
     *
     * @return boolean
     * @deprecated 7.0
     */
    public static function is_auctions()
    {
        // NOTE: uses site settings
        return geoMaster::is('auctions');
    }

    /**
     * Returns true if Classifieds are enabled
     *
     * @return boolean
     * @deprecated 7.0
     */
    public static function is_classifieds()
    {
        // NOTE: uses site settings
        return geoMaster::is('classifieds');
    }

    /**
     * Returns true if license is a trial license, or false if not.
     *
     * @return bool
     */
    public static function is_trial()
    {
        // GUTTED
        return false;
    }

    /**
     * Returns true if license is the MAIN demo, or false if not.
     *
     * @return bool
     * @since Version 7.0.4
     */
    public static function is_main_demo()
    {
        // GUTTED
        return false;
    }

    /**
     * Returns true if license is a leased license.
     *
     * @param string $addon
     * @return bool
     * @since Version 6.0.0
     */
    public static function is_leased($addon = '')
    {
        // GUTTED
        return false;
    }

    /**
     * Used internally to figure out if license is restricted to only work for
     * certain types.  Will return the "master" setting for the listing
     * type that the license can "only" be used for, or false if no restriction
     * in place.  For instance, if a "classifieds only" license, this would
     * return "classifieds"
     *
     * @return boolean
     * @since Version 7.0.3
     * @internal
     */
    public static function license_only()
    {
        // GUTTED
        return false;
    }

    /**
     * Returns true if using a license from the AdPlotter Edition, which has fewer admin options available
     *
     * @return boolean
     * @since Version 7.5.0
     * @internal
     */
    public static function is_adplotter()
    {
        // GUTTED
        return false;
    }

    public static function geoturbo_status()
    {
        // GUTTED
        //not geoturbo
        return false;
    }

    /**
     * Returns true if using a "white label" license that should remove mentions of Geo.
     * Not fully implemented yet, on a license level. To activate, define a PHP constant WHITE_LABEL in config.php
     * @return boolean
     * @internal
     */
    public static function is_whitelabel()
    {
        // GUTTED
        // not sure what this does but does not seem like much
        return false;
    }

    /**
     * Checks to see if the given addon's product ID is attached to the current
     * license or not.
     *
     * @param array $addon_product_ids The array of product IDs that the addon
     *   is part of.  Namely the main product ID and any "combo" product ID's.
     * @return bool True if found on license, false otherwise.
     * @since Version 7.0.0
     */
    public static function is_addon_attached($addon_product_ids)
    {
        //All addons are free these days!
        return true;
    }

    /**
     * Whether to force powered by or not.
     *
     * @deprecated 7.1
     * @return boolean
     */
    public static function force_powered_by()
    {
        // GUTTED
        return false;
    }

    /**
     * Returns true if license product edition is Enterprise.
     *
     * @deprecated by GeoCore -- everything is now treated as Enterprise
     * @return true
     */
    public static function is_ent()
    {
        return true;
    }

    /**
     * Returns true if license product edition is Premier.
     *
     * @deprecated by GeoCore -- everything is now treated as Enterprise
     * @return false
     */
    public static function is_premier()
    {
        return false;
    }

    /**
     * Returns true if license product edition is Basic.
     *
     * @deprecated by GeoCore -- everything is now treated as Enterprise
     * @return false
     */
    public static function is_basic()
    {
        return false;
    }
    /**
     * Not really used at the moment, in the future we may offer limited licenses
     * that have set number of admin logins allowed.
     * @return number
     */
    public static function maxSeats()
    {
        // GUTTED
        return -1;
    }

    /**
     * Gets the domain name and path, used for license validation.
     *
     * @return array An associative array, following the format array ( 'domain' => $domain, 'path' => $install_path)
     */
    public function get_installation_info()
    {
        // GUTTED
        return ['domain' => 'example.com', 'path' => '/does/not/matter/just/enter/what/you/want'];
    }

    /**
     * Validates the username and password from the database.  Password should
     * be in plain text form.  Takes into account different password storage
     * types set in the admin (Enterprise Only) under the Security Settings page.
     *
     * @param string $username The username, in plain text.
     * @param string $password The password, in plain text.  This can NOT be the hashed password, it must
     *  be in plaintext.
     * @param string $license Only used when entering a new license key
     * @param bool $check_email_as_user if set to false, will NOT try to match
     *  by the e-mail if the username is not found.
     * @param bool $checkAdmin If set to true, and product is Enterprise, if
     *  normal password check does not match, and user is logged into admin
     *  panel already, will check to see if it matches the logged-in admin
     *  user's password.
     * @param bool $verifyStatus If false, do not validate if user status is active
     *   or not.  Added in version 7.2.6.
     * @return Mixed false if credentials do not match, or an array with user data if verified.
     * @since The $checkAdmin param added in Version 4.1.0.
     */
    public function verify_credentials(
        $username,
        $password,
        $license = false,
        $check_email_as_user = true,
        $checkAdmin = false,
        $verifyStatus = true
    ) {
        //verify inputs.
        // GUTTED (had some license checks mixed in)

        if (!(strlen($username) > 0 && strlen($password) > 0)) {
            //if either the username or password are empty, return false.
            trigger_error('DEBUG SESSION:' . 'verify_credentials(\'' . $username . '\', \'' . $password
                . '\') = false, username or password is empty.', __line__);
            return false;
        }

        //get the password from the database.
        $sql = 'SELECT * FROM ' . geoTables::logins_table . ' WHERE `username` LIKE ?';
        $result = $this->db->Execute($sql, array($username));
        if (!$result) {
            //database error
            trigger_error('DEBUG SESSION:' . '[ERROR] - verify_credentials() SQL error: SQL= ' . $sql . ' Error='
                . $this->db->ErrorMsg(), __line__);
            return false;
        }
        if (
            $result->RecordCount() == 0
            && (strpos($username, '@') !== false)
            && $check_email_as_user
            && !defined('IN_ADMIN')
        ) {
            //there are no users by that username.  Try the email.
            $sql = 'SELECT * FROM ' . geoTables::userdata_table . ' WHERE email = ?';
            $userdata_result = $this->db->Execute($sql, array($username));
            if (!$userdata_result) {
                //database error
                return false;
            }
            if ($userdata_result->RecordCount() != 1) {
                //we do not try to verify if multiple users w/ same email are used.
                trigger_error('DEBUG SESSION:' . '[ERROR] - multiple users with same e-mail - invalid login for user: '
                    . $username, __line__);
                return false;
            }
            $user_data = $userdata_result->FetchRow();
            if (!is_array($user_data)) {
                //something went wrong...
                return false;
            }
            if ($user_data['id'] == 1) {
                //do not allow admin user to log in using e-mail!
                trigger_error('DEBUG SESSION:' . '[ERROR] - verify_credentials() - Admin user is not allowed to log in
                    using e-mail! username=' . $username . ' pass=[HIDDEN]');
                return false;
            }
            //now try again, this time using the actual username, and force no e-mail
            // verification, to prevent infinite recursive calls.
            trigger_error('DEBUG SESSION: [NOTICE] - verify_credentials() - appears they entered their e-mail,
                so re-validating using their username.  Details used: user=' . $user_data['username']
                . ' pass=[HIDDEN]');
            return $this->verify_credentials($user_data['username'], $password, $license, false);
        }

        if ($result->RecordCount() != 1) {
            //there are more than one user by that username?  do not bother verifying,
            //there is an error.
            trigger_error('DEBUG SESSION:' . '[ERROR] - multiple users with same username - invalid login for user: '
                . $username);
            return false;
        }

        $login_data = $result->FetchRow();

        //make sure the username is valid, since we are using like, want to elimitate
        //the use of %
        //allow different case for username.
        if (
            !(
                strlen($login_data['username']) == strlen($username)
                && strlen(stristr($login_data['username'], $username)) == strlen($login_data['username'])
            )
        ) {
            //Seems that username is not matching up.
            trigger_error('DEBUG SESSION:' . '[ERROR] - invalid login, username not found. user: ' . $username);
            return false;
        }

        //change the username to the proper case-sesitive version.
        $username = $login_data['username'];
        $hash_type = $login_data['hash_type'];

        $hash_setting = ($login_data['id'] == 1) ? 'admin_pass_hash' : 'client_pass_hash';
        $preferred_hash_type = $this->db->get_site_setting($hash_setting);

        //we have the username and password from the database.
        $allTypes = $this->get_hash_types();
        $userTypes = $adminTypes = array();

        if ($hash_type && isset($allTypes[$hash_type])) {
            //make the hash type set the only one checked...
            $userTypes = array_intersect_key($allTypes, array($hash_type => ''));
        } else {
            //hash type isn't set in DB, so check them all
            $userTypes = $allTypes;
        }
        $hash_types = array ('normal' => $userTypes);
        if (!defined('IN_ADMIN') && $checkAdmin && isset($_COOKIE['admin_classified_session'])) {
            //add on hash types for admin to check
            //get admin hash type
            $adminType = $this->db->GetOne(
                "SELECT `hash_type` FROM " . geoTables::logins_table . " WHERE `username`=?",
                array($this->getAdminUser($_COOKIE['admin_classified_session']))
            );
            if ($adminType && isset($allTypes[$adminType])) {
                $adminTypes = array_intersect_key($allTypes, array($adminType => ''));
            } else {
                //hash type isn't set in DB, so check them all
                $adminTypes = $allTypes;
            }

            $hash_types['admin'] = $adminTypes;
        }
        unset($allTypes, $userTypes, $adminTypes);

        foreach ($hash_types as $checkType => $types) {
            if ($checkType == 'admin') {
                //switch over and check user/pass for admin, this is to check if
                //logged into admin panel, and attempting to log into user side
                //using a normal user's ID and the admin pass.
                $userCheck = $this->getAdminUser($_COOKIE['admin_classified_session']);
                if ($userCheck) {
                    //need to get the hassed pass for admin
                    $adminInfo = $this->db->GetRow(
                        "SELECT `password`,`salt` FROM " . geoTables::logins_table . " WHERE `username` = ?",
                        array($userCheck)
                    );
                    $passCheck = $adminInfo['password'];
                    $saltCheck = $adminInfo['salt'];
                    unset($adminInfo);
                }
            } else {
                //This is the "normal" check.
                $userCheck = $username;
                $passCheck = $login_data['password'];
                $saltCheck = $login_data['salt'];
            }

            if (!$userCheck || !$passCheck) {
                //not for this one?  This might happen when checking for admin pass
                continue;
            }
            foreach ($types as $key => $info) {
                //go through each hash type and see if it matches.
                trigger_error("DEBUG SESSION: Trying hash type $key");
                if ($info['saltLength'] !== -1 && $info['saltLength'] !== strlen($saltCheck)) {
                    //salt lengths do not match, and salt length is not variable,
                    //so this cannot be a match for this hash type.
                    trigger_error("DEBUG SESSION: salt length doesn't match for this hash type.");
                    continue;
                }
                if ($info['length'] !== -1 && strlen($passCheck) !== $info['length']) {
                    //hashed password lengths do not match, and hashed password length
                    //is not variable, so this cannot be a match for this hash type
                    trigger_error("DEBUG SESSION: hashed length doesn't match for this hash type.");
                    continue;
                }

                //The lengths of the hashed password and salt are the "right length", so
                //continue with the checks

                $validNativePass = false;
                if ($key === 'core:php_native' && function_exists('password_verify')) {
                    //use password_verify() to verify the hash instead of just checking for equality
                    $validNativePass = password_verify($password, $passCheck);
                } else {
                    $salt = '';
                    $hashed_pass = $this->get_hashed_password($userCheck, $password, $key, $saltCheck);

                    if (is_array($hashed_pass)) {
                        //the hash requires a password
                        $salt = $hashed_pass['salt'];
                        $hashed_pass = $hashed_pass['password'];
                    }
                }


                if (($hashed_pass === $passCheck && $salt === $saltCheck) || $validNativePass) {
                    //user and pass match up for this hash type!!!
                    //it is a valid password.
                    if ($checkType == 'normal' && ($key !== $preferred_hash_type || !$login_data['hash_type'])) {
                        //if the password matches up, but the hash type is not the default,
                        //OR the hash type is not set in the database,
                        //then convert the password to use the prefered hash type.
                        $this->convert_hash_pass($username, $password, $preferred_hash_type);
                    }
                    //password matches up!

                    // GUTTED HERE

                    if (defined('IN_ADMIN')) {
                        //this will only happen if $checkType = 'normal' since
                        //that is always the case when coming from admin side.
                        $login_data['password'] = $password; //set un-hashed password
                        $core_auth = geoAddon::triggerDisplay('auth_admin_login', $login_data, geoAddon::NOT_NULL);
                        //if in admin, only return true if the user is an admin user.
                        if (($login_data['id'] == 1 && $core_auth === null) || $core_auth === true) {
                            //valid admin login.
                            unset($login_data['password'], $login_data['salt']);
                            return $login_data;
                        } else {
                            //trying to log in w/o admin user login.
                            trigger_error('DEBUG SESSION:[NOTICE] verify_credentials() - user/pass BAD for admin login:
                                username: ' . $username . ' password: [HIDDEN]');
                            return false;
                        }
                    }
                    //valid login, now just check the status.
                    if (!$verifyStatus || $login_data['status'] == 1) {
                        //either NOT verifying status, or we are and the status is good...
                        //means login passed all the other checks!
                        unset($login_data['password'], $login_data['salt']);
                        return $login_data;
                    } else {
                        trigger_error('DEBUG SESSION:[NOTICE] verify_credentials() - user/pass verified successfully
                            but STATUS failed, so returning false: username: ' . $username . ' password: [HIDDEN]');
                        return false;
                    }
                }
                trigger_error("DEBUG SESSION:[NOTICE] hashed passwords or salts don't match.");
            }
        }
        //Just went through each different hash type, and none of them matched up,
        //so the password must not be valid.
        trigger_error('DEBUG SESSION:' . '[NOTICE] verify_credentials() - user/pass FAILED - user & pass do not match:
            username: ' . $username . ' password: [HIDDEN]');
        return false;
    }

    /**
     * Gets the admin username given the session ID, returns false on failure.
     *
     * @param string $adminSessionId The admin's session ID, as would normally
     *  be set in $_COOKIE['admin_classified_session']
     * @return string|bool The admin's username, or false on failure (if not
     *  Enterprise product, or if admin session not found, or if session does
     *  not coorospond to an admin user)
     * @since Version 4.1.0
     * @internal
     */
    private function getAdminUser($adminSessionId)
    {
        $sessionId = trim($adminSessionId);
        if (strlen($sessionId) != 32) {
            //length of session ID wrong
            return false;
        }

        //look for a session
        $db = DataAccess::getInstance();
        $session = $db->GetRow(
            "SELECT `user_id` FROM " . geoTables::session_table . "
                WHERE `classified_session`=? AND `admin_session`='Yes'",
            array($sessionId)
        );

        if (!$session) {
            //no such session found
            return false;
        }

        $userId = $session['user_id'];

        $addonVars = array ('userId' => $userId, 'session' => $sessionId);

        $addonCheck = geoAddon::triggerDisplay('auth_admin_user_login', $addonVars, geoAddon::NOT_NULL);

        if ($userId == 1 || $addonCheck === true) {
            //An admin user, return the username
            return geoUser::userName($userId);
        }

        //this is not an admin user!
        return false;
    }

    /**
     * Used for older type of password encryption for old auctions.
     *
     * @param string $key
     * @param int $iv_len
     * @return string
     * @internal
     */
    public function get_iv($key, $iv_len)
    {
        $iv = '';
        for ($i = 0; $i < $iv_len; $i++) {
            $iv .= chr($key[0] & 0xff);
        }

        return $iv;
    }

    /**
     * Generates a hashed password from the given plain text username and password.
     *
     * @param String $username
     * @param String $password The password, in plain text.
     * @param string $hash_type the hash type to use, as returned by get_hash_types()
     * @param string $salt The salt, if the hash type uses a salt value.  Supply existing
     *   salt value if verifying existing hashed password, otherwise leave blank if
     *   generating a new hashed password
     * @return Mixed String hashed password if success, or boolean false if error occures.
     */
    public function get_hashed_password($username, $password, $hash_type = 0, $salt = '')
    {
        //perform some hashing...

        if (is_numeric($hash_type)) {
            //convert from the "old" values to new
            $map = array(0 => 'core:sha1', 1 => 'core:plain', 2 => 'core:old_auctions');
            if (isset($map[$hash_type])) {
                $hash_type = $map[$hash_type];
            }
        }

        $hash = explode(':', $hash_type);

        $pass = false;
        if ($hash[0] == 'core') {
            //this is one of the "core" (built in) hash types
            switch ($hash[1]) {
                case 'sha1':
                    //default hash type, lets do strong hashing.
                    //Don't just hash the password, hash a combination of the password along
                    //with the username.  Also add extra "salt" to the hash, to make it even
                    //better.
                    $pre_hash = "$username:Some Extra GeoSalt@$# $password";
                    $pass = sha1($pre_hash);
                    break;

                case 'plain':
                    //don't really hash, just return the password in plaintext.
                    return $password;
                    break;

                case 'old_auctions':
                    /**
                     * Encryption function for use when trying login for old auction type
                     * password encryption.  Passwords should ideally be converted
                     * during the upgrade process, since the auction type of passwords
                     * can be decrypted (unlike the new hash methods)
                     */
                    $key = $this->db->get_site_setting('password_key');
                    $iv_len = 32;

                    if ($key === null) {
                        //password key does not exist, so probably not using this
                        //type of encryption.
                        return false;
                    }
                    $plain_text = $password . "\x13";
                    $n = strlen($plain_text);
                    if ($n % 16) {
                        $plain_text .= str_repeat("\0", 16 - ($n % 16));
                    }

                    $enc_text = $this->get_iv($key, $iv_len);
                    $iv = substr($key ^ $enc_text, 0, 512);

                    $i = 0;
                    while ($i < $n) {
                        $block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
                        $enc_text .= $block;
                        $iv = substr($block . $iv, 0, 512) ^ $key;
                        $i += 16;
                    }
                    $pass = base64_encode($enc_text);
                    break;
                case 'php_native':
                    //use PHP's new built-in password_* functions!
                    if (function_exists('password_hash')) { //does not exist on PHP5.4
                        $pass = password_hash($password, PASSWORD_BCRYPT, array('cost' => 10));
                    } else {
                        $pass = false;
                    }
                    break;
                default:
                    //type is not known, return false.
                    $pass = false;
            }
        } elseif ($hash[0] == 'addon') {
            //addon hash type
            $addon_name = $hash[1];
            $type = $hash[2];
            $util = geoAddon::getUtil($addon_name);
            $function = "auth_" . $type;
            if ($util && is_callable(array($util, $function))) {
                $pass = $util->$function($username, $password, $salt);
            }
        }
        return $pass;
    }
    /**
     * Used internally
     * @var array
     * @internal
     */
    private $_hashTypes;

    /**
     * Gets an array of strings, one for each type of hash used for the get_hashed_password
     * function.  Note that addons can register their own ways to generate hashed
     * passwords, see the hook notify_geoPC_get_hash_types called in this method,
     * and the method register_hash_type() in this class.
     *
     * @return Array An array of arrays using the form:  key = hash type, value is an array
     *   with 'length' => either fixed length that all hashed passwords will be, or -1 if variable length,
     *   'saltLength'=> the fixed length for how long salt values are, 0 if not use salt, or -1 for variable length
     *     salt,
     *   'name' => the name, used to set this hash type as default hash used in admin. If blank, this will not show as
     *     option in admin.
     */
    public function get_hash_types()
    {
        if (!isset($this->_hashTypes)) {
            $types = array();
            //Current hash type:
            //Current hash type:
            $types['core:sha1'] = array (
                'length' => 40,
                'saltLength' => 0,
                'name' => 'Salted SHA-1 Hash',

                );

            //plain-text (no hash):
            $types['core:plain'] = array (
                'length' => -1,
                'saltLength' => 0,
                'name' => 'Plain Text',

                );

            //For old encryption...
            $types['core:old_auctions'] = array (
                'length' => -1,
                'saltLength' => 0,
                //no name, so it doesn't show as a choice
                );

            //PHP native password hashing (uses BCRYPT, requires PHP 5.5 or higher)
            if (function_exists('password_hash')) {
                $types['core:php_native'] = array(
                    'length' => 60,
                    'saltLength' => -1,
                    'name' => 'BCRYPT Hash (most secure)'
                );
            }

            $this->_hashTypes = $types;
            //allow addons to register their own types
            geoAddon::triggerUpdate('notify_geoPC_get_hash_types');
        }

        return $this->_hashTypes;
    }

    /**
     * Register a new password hash type, used by addons to add custom hash types
     * for passwords.
     *
     * @param string $addon The addon name
     * @param string $type The
     * @param string $name If set, will allow this hash method to be selected as
     *   the "default" hash method to use in the admin, and the name set here will
     *   be the name used to reffer to this hash method.  Note that it will
     *   automatically append the addon's title
     * @param int $length If set, this is the fixed length that the has type produces
     *   once hashed.  For instance, if using SHA1 the length will always be 32.
     *   Set value to -1 for "variable length"
     * @param int $saltLength If the hash uses a salt, need to set the salt length
     *   to the length that all salt values will be, or -1 for variable length.
     *   Leave at default 0 length if the hash does not use a salt.
     * @return string The generated index type as it would be referenced in logins
     *   table, or empty on invalid input.
     * @since Version 7.1.0
     */
    public function register_hash_type($addon, $type, $name = '', $length = -1, $saltLength = 0)
    {
        $addon = trim($addon);
        $type = trim($type);
        $name = trim($name);
        $length = (int)$length;
        $saltLength = (int)$saltLength;

        if (!$addon || !$type) {
            return '';
        }
        if ($name) {
            //include addon in part of the name
            $info = geoAddon::getInfoClass($addon);
            if ($info) {
                $name .= ' [Using Addon: ' . $info->title . ']';
            }
        }
        $this->_hashTypes["addon:$addon:$type"] = array (
            'length' => $length,
            'saltLength' => $saltLength,
            'addon' => $addon,
            'name' => $name,
            );
        return "addon:$addon:$type";
    }

    /**
     * Changes the storage hash type for a users password in the database.
     *
     * @param String $username
     * @param String $password_plaintext
     * @param int $type Hash type to convert to.
     * @return Boolean Returns true if it worked, false otherwise.
     */
    public function convert_hash_pass($username, $password_plaintext, $type = 'core:sha1')
    {
        $password_hashed = $this->get_hashed_password($username, $password_plaintext, $type);
        if ($password_hashed === false) {
            //password hash failed.
            return false;
        }
        $salt = '';
        if (is_array($password_hashed)) {
            $salt = '' . $password_hashed['salt'];
            $password_hashed = $password_hashed['password'];
        }
        $sql = 'UPDATE ' . $this->db->geoTables->logins_table
            . ' SET password = ?, salt = ?, hash_type = ? WHERE username = ?';
        $result = $this->db->Execute($sql, array($password_hashed, $salt, $type, $username));
        if (!$result) {
            //query failed.
            return false;
        }
        return true;
    }

    /**
     * Generates a random password.  Note:  Does NOT hash the password, or insert the password
     * into the database.
     *
     * Note: As of version 7.1.0, this generates random password with many more
     * possible characters (a-zA-Z0-9).  Before that it was limited characters.
     *
     * @param int $pass_length Password length (must be more than 0, if not it will default
     *   to 6)
     * @param bool $includeSpecial If true, will include special characters in the
     *   possible characters used to generate the string.  Param added in version 7.1.0
     * @return String A string of length given by pass_length, that consists of numbers and letters
     *   and optionally special characters.
     */
    public function generate_new_pass($pass_length, $includeSpecial = false)
    {
        //make sure password length is valid.
        $pass_length = intval($pass_length);
        //make sure it is a positive value.
        if ($pass_length < 1) {
            $pass_length = 6;
        }

        $valid = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($includeSpecial) {
            //also include special characters
            $valid .= '/?-~!@#%^&*()_+=|][}{;:,<>';
        }
        //split up the string
        $valid = str_split($valid);
        $max = count($valid) - 1;
        $pass = '';
        for ($i = 0; $i < $pass_length; $i++) {
            $pass .= $valid[mt_rand(0, $max)];
        }

        //now return our new password!
        return $pass;
    }
    /**
     * Used internally
     * @var string
     * @internal
     */
    private static $_currentVersion;

    /**
     * Gets the current version of the software, as set in the database.
     *
     * @return string
     */
    public static function getVersion()
    {
        if (!isset(self::$_currentVersion)) {
            self::$_currentVersion = DataAccess::getInstance()->GetOne("SELECT `db_version` FROM `geodesic_version`");
        }
        return self::$_currentVersion;
    }
    /**
     * USed internally
     * @var string
     * @internal
     */
    private static $_latestVersion;
    /**
     * Gets the latest version as reported by geodesicsolutions.org
     * @return string|bool Will return the latest version, or bool false if it
     *  could not get the latest version.
     *  @since Version 4.1.0
     */
    public static function getLatestVersion()
    {
        // GUTTED
        // updated - use the new community website so will know when a new community release is out, IF that ever
        // does happen...

        if (isset(self::$_latestVersion)) {
            //already got it once this page load.
            return self::$_latestVersion;
        }
        $versionUrl = 'https://geodesicsolutions.org/latest-version.php';
        if (strpos(self::getVersion(), 'beta') !== false) {
            //this is beta version, get the latest beta version
            $versionUrl = 'https://geodesicsolutions.org/latest-version.beta.txt';
        }
        $version = self::urlGetContents($versionUrl);
        if (!$version) {
            //something wrong when getting version - just use 18.02.0
            $version = '18.02.0';
        }
        // do NOT trust it!
        $version = htmlspecialchars(trim($version));
        self::$_latestVersion = $version;
        return $version;
    }

    /**
     * Gets the unix timestamp for when the license data expires
     *
     * @param string $addon
     * @return int|string Unix timestamp for when local license expires, or the
     *  string 'never' if it never expires.
     * @since Version 4.1.0
     */
    public static function getLocalLicenseExpire($addon = '')
    {
        // GUTTED
        return 'never';
    }

    /**
     * Gets the unix timestamp for when the license expires
     *
     * @param string $addon
     * @return int|string Unix timestamp for when local license expires, or the
     *   string 'never' if it never expires.
     * @since Version 4.1.0
     */
    public static function getLicenseExpire($addon = '')
    {
        // GUTTED
        return 'never';
    }

    /**
     * Gets the date for support & updates expiration (specifically, the timestamp
     * for when download control expires)
     *
     * @param string $addon
     * @return int|string timestamp for when download access expires, or "never"
     *   if never expires
     * @since Version 4.1.0
     */
    public static function getSupportExpire($addon = '')
    {
        // GUTTED
        return 'never';
    }

    /**
     * Gets the expiration time for download access for the license.
     *
     * @param string $addon
     * @return int|string timestamp for when download access expires, or "never"
     *   if never expires
     * @since Version 7.0.0
     */
    public static function getDownloadExpire($addon = '')
    {
        // GUTTED
        return 'never';
    }

    /**
     * Gets the package ID that the license is installed in (used for links in
     * admin panel)
     */
    public static function getPackageId()
    {
        // GUTTED
        // @todo: in main software, remove call

        return '1';
    }

    /**
     * Used by admin panel to clear the license key, clears all the saved license
     * key and saved license data so that a new license key can be entered.
     *
     * If not called from admin, it will NOT clear the license key.
     *
     * @param bool $onlyClearData If true, will only clear the license data, not
     *  the license key.  Since version 4.1.0.
     * @param string $addon
     * @since Version 4.0.7
     */
    public static function clearLicenseKey($onlyClearData = false, $addon = '')
    {
        // GUTTED
    }

    /**
     * Gets contents of given URL.  NO CLEANING is done on URL,
     * if it is from user input it must be properly cleaned prior
     * to calling this method.
     *
     * @param string $url
     * @param int $timeout Number of seconds for connection timeout. Param added
     *   in version 7.0.4
     * @since Version 5.0.3
     */
    public static function urlGetContents($url, $timeout = 0, $additionalHeaders = null)
    {
        trigger_error("DEBUG STATS: Top of urlGetContents, about to get contents of URL $url");
        if (!function_exists('curl_init')) {
            //they don't have curl?  what an antiquated system!
            trigger_error("DEBUG STATS: Server does not seem to have CURL, falling back to attempt
                    using file_get_contents()");
            if ($additionalHeaders) {
                $context = stream_context_create([
                    'http' => ['method' => 'GET', 'header' => implode("\r\n", $additionalHeaders)]
                ]);
                $results = file_get_contents($url, false, $context);
            } else {
                $results = file_get_contents($url);
            }

            trigger_error("DEBUG STATS: Finished getting URL contents (using file_get_contents).");
            return $results;
        }

        $link = curl_init();
        curl_setopt($link, CURLOPT_URL, $url);
        if (is_array($additionalHeaders) && $additionalHeaders) {
            curl_setopt($link, CURLOPT_HTTPHEADER, $additionalHeaders);
        }

        curl_setopt($link, CURLOPT_HEADER, 0);
        curl_setopt($link, CURLOPT_RETURNTRANSFER, true);
        if (GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN) {
            //ONLY turn off verifypeer / verify host if set to
            curl_setopt($link, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($link, CURLOPT_SSL_VERIFYHOST, false);
        } elseif (strpos(GEO_CURL_CAINFO, '.pem') !== false) {
            //Specifying an alternate location for CA cert bundle
            curl_setopt($link, CURLOPT_CAINFO, GEO_CURL_CAINFO);
        }
        if ($timeout) {
            curl_setopt($link, CURLOPT_CONNECTTIMEOUT, $timeout);
        }


        $response = curl_exec($link);

        curl_close($link);
        trigger_error("DEBUG STATS: Finished getting contents (using CURL).");
        return $response;
    }

    /**
     * Sends POST to given URL with the given post parameters, and returns the
     * response.  Accounts for if server has CURL or not.  If you need to just
     * get response of URL without posting data, see {@link geoPC::urlGetContents()}
     *
     * As of version 6.0.0, now accepts https:// URL's even when using fsockopen to connect,
     * as long as open SSL is installed on server.  Otherwise just returns empty string.
     *
     * As of version 7.0.5, now uses HTTP/1.1 protocol instead of HTTP/1.0 that was
     * used in previous versions.
     *
     * @param string $url The post URL to post to.
     * @param array|string $params The array of post parameters or query string
     * @param int $timeout Number of seconds for connection timeout. Param added
     *   in version 7.0.4
     * @param array $additionalHeaders an array of additional HTTP headers to send with the request, beyond the normal
     *   ones
     * @since Version 5.1.2
     */
    public static function urlPostContents($url, $params, $timeout = 30, $additionalHeaders = null)
    {
        trigger_error('DEBUG STATS: Top of geoPC::urlPostContents(), getting URL of ' . $url);
        if (!function_exists('curl_init')) {
            //use fsockopen
            trigger_error('DEBUG STATS: Did not find CURL, falling back to use fsockopen');
            $parts = parse_url($url);

            if (is_array($params)) {
                $request = array();
                foreach ($params as $key => $value) {
                    $request[] = "$key=" . urlencode($value);
                }
                $request = implode('&', $request);
            } else {
                //must already be parsed into a string
                $request = '' . $params;
            }

            $header = "POST {$parts['path']} HTTP/1.1\r\n";
            $header .= "Host: {$parts['host']}\r\n";
            $header .= "Content-type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-length: " . strlen($request) . "\r\n";
            if (is_array($additionalHeaders) && count($additionalHeaders) > 0) {
                foreach ($additionalHeaders as $additional) {
                    $header .= "$additional\r\n";
                }
            }
            $header .= "Connection: close\r\n\r\n";
            $header .= $request;

            $results = '';
            trigger_error('DEBUG STATS: Right before opening connection and posting (using fsock)');
            if (strpos($url, 'https://') !== false) {
                $fp = fsockopen('ssl://' . $parts['host'], 443, $errno, $errstr, $timeout);
            } else {
                $fp = fsockopen($parts['host'], 80, $errno, $errstr, $timeout);
            }
            if (!$fp) {
                return '';
            }
            fputs($fp, $header);
            while (!feof($fp)) {
                $results .= fgets($fp, 1024);
            }
            fclose($fp);

            $results = explode("\r\n\r\n", $results);
            trigger_error('DEBUG STATS: Finished getting POST contents (using fsock)');
            return $results[1];
        } else {
            $link = curl_init($url);
            curl_setopt($link, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($link, CURLOPT_POST, true);
            curl_setopt($link, CURLOPT_POSTFIELDS, $params);
            curl_setopt($link, CURLOPT_VERBOSE, false);
            if (GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN) {
                //ONLY turn off verifypeer / verify host if set to
                curl_setopt($link, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($link, CURLOPT_SSL_VERIFYHOST, false);
            } elseif (strpos(GEO_CURL_CAINFO, '.pem') !== false) {
                //Specifying an alternate location for CA cert bundle
                curl_setopt($link, CURLOPT_CAINFO, GEO_CURL_CAINFO);
            }
            $headers = (is_array($additionalHeaders) && count($additionalHeaders) > 0) ? $additionalHeaders : array();
            $headers[] = 'Connection: close';
            curl_setopt($link, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($link, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($link, CURLOPT_MAXREDIRS, 6);
            curl_setopt($link, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($link, CURLOPT_TIMEOUT, 15); // 60
            $results = curl_exec($link);
            curl_close($link);
            trigger_error('DEBUG STATS: Finished getting POST contents (using CURL).');
            return $results;
        }
    }
}

/**
 * Manages session storage and session cookies for user sessions.
 *
 * Works directly with {@link geoPC} and {@link DataAccess}, and requires (like
 * most geo classes) that the software is initialized using app_top.common.php.
 *
 * Since this class is encoded, if you have any questions at all about any of the
 * methods contained in here, send in a support ticket and we will answer if possible.
 *
 * @package System
 */
class geoSession
{
    /**
     * This is used to indicate a mobile device was detected.
     * @var string
     * @since Version 7.3.0
     */
    const DEVICE_MOBILE = 'mobile';

    /**
     * This is used to indicate a desktop device was detected.
     * @var string
     * @since Version 7.3.0
     */
    const DEVICE_DESKTOP = 'desktop';

    /**
     * Internal use
     * @internal
     */
    private $db, $languageId, $device, $userId, $userName, $sessionId, $cookie_name, $_pendingChanges;

    /**
     * Session registry object
     * @var geoRegistry
     */
    private $_registry;

    /**
     * Instance
     * @internal
     */
    private static $_instance, $_md, $_isTablet;
    /**
     * Constructor, sets up settings, but does not init the
     * session.
     *
     */
    private function __construct()
    {
        trigger_error('DEBUG SESSION:' . '[NOTICE] -- New Session ()');
        if (PHP5_DIR) {
            $this->db = DataAccess::getInstance();
        } else {
            $this->db =& DataAccess::getInstance();
        }

        if (isset($HTTP_COOKIE_VARS)) {
            $_COOKIE = $HTTP_COOKIE_VARS;
        }
        if (defined('IN_ADMIN')) {
            //admin sessions.
            $this->cookie_name = 'admin_classified_session';
        } else {
            $this->cookie_name = 'classified_session';
        }
    }

    /**
     * Gets an instance of the geoSession class.
     *
     * @return geoSession
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance) || !is_object(self::$_instance)) {
            self::$_instance = new geoSession();
        }
        return self::$_instance;
    }

    /**
     * Get instance of the Mobile_Detect class, handy for detecting info about
     * the device used
     *
     * @return Mobile_Detect
     * @since Version 7.3.0
     */
    public static function getMobileDetect()
    {
        if (!isset(self::$_md) || !is_object(self::$_md)) {
            require_once CLASSES_DIR . 'Mobile-Detect/Mobile_Detect.php';
            self::$_md = new Mobile_Detect();
        }
        return self::$_md;
    }

    /**
     * Convenience method for accessing whether the current device is mobile
     * @return boolean
     */
    public static function isMobile()
    {
        //result of getDevice() is cached, and is either Mobile or Desktop
        return (bool)(self::getInstance()->getDevice() == geoSession::DEVICE_MOBILE);
    }

    /**
     * Allow detecting whether the current device is a tablet.
     * Note that Mobile_Detect does not do its own internal caching, so be sure to cache the result and only call it
     * once per pageload
     * Also note that a given device being a "tablet" does not preclude any particular result from isMobile() (that is,
     * either a Desktop or Mobile could also be a Tablet)
     */
    public static function isTablet()
    {
        if (!isset(self::$_isTablet)) {
            $md = self::getMobileDetect();
            self::$_isTablet = $md->isTablet();
        }
        return self::$_isTablet;
    }

    /**
     * Returns the classified_session id
     *
     * @return string session ID
     */
    public function getSessionId()
    {
        return isset($this->sessionId) ? $this->sessionId : false;
    }

    /**
     * Purge the database of sessions that are more than an hour old (or if
     * Enterprise, whatever the setting is in security settings page in admin)
     *
     * @todo If this turns out to take a long time on high traffic sites, consider
     *   moving this into a dedicated cron job.
     */
    public function cleanSessions()
    {
        $currentTime = time();

        $timeOutAdmin = $this->_getSessionTimeout('admin');
        $timeOutClient = $this->_getSessionTimeout('client');

        $sTable = geoTables::session_table;
        $rTable = geoTables::session_registry;
        $bTable = geoTables::browsing_filters;

        trigger_error('DEBUG SESSION:' . '[NOTICE] -- cleanSessions() - Removing old sessions.');
        /*
             IMPORTANT: select the session IDs to delete, then remove specifically those from ALL tables
                -- this is much faster than the old way (which was to delete the sessions, and then delete from other
                    tables where the IDs were missing)
                    because searching by negation gets super slow with lots of records, especially because the keys are
                    hashes and thus index don't help a whole lot...
                -- there's also the added benefit of skipping a couple queries entirely if there are no sessions to
                    remove right now

            A better way to do this would be to create a trigger on the main session table to delete from the others,
            but that requires MYSQL 5.0.2, which is beyond our current min-reqs

        */

        $delete = $this->db->Execute("SELECT `classified_session` FROM $sTable WHERE
            (`admin_session` = 'Yes' AND `last_time` < " . ($currentTime - $timeOutAdmin) . " )
            OR ( `admin_session` = 'No' AND `last_time` < " . ($currentTime - $timeOutClient) . " )");

        $ids_to_delete = array();
        foreach ($delete as $sessionId) {
            $ids_to_delete[] = "'{$sessionId['classified_session']}'";
        }
        if (count($ids_to_delete) > 0) {
            $del = implode(',', $ids_to_delete);
            $queries = array();
            $queries[] = "DELETE FROM $sTable WHERE `classified_session` IN ($del)";
            $queries[] = "DELETE FROM $rTable WHERE `sessions` IN ($del)";
            $queries[] = "DELETE FROM $bTable WHERE `session_id` IN ($del)";
            foreach ($queries as $sql) {
                $this->db->Execute($sql);
            }
        }
    }

    /**
     * Manually close a session, delete the session out of the database, and
     * remove any sell or registration sessions.
     *
     * @param string $sessionId If not specified, will use current session id.
     */
    public function closeSession($sessionId = null)
    {
        if ($sessionId === null) {
            $sessionId = isset($this->sessionId) ? $this->sessionId : $_COOKIE[$this->cookie_name];
        }
        if (strlen($sessionId) < 30) {
            //something is wrong with this session id.
            trigger_error('DEBUG SESSION:' . '[ERROR] -- closeSession() - session id stringlen is < 30, so not removing.
                $sessionId = ' . $sessionId);
            return false;
        }
        trigger_error('DEBUG SESSION:' . '[NOTICE] -- closeSession() - removing session for $sessionId = '
            . $sessionId);
        $sql_query = "DELETE FROM " . geoTables::session_table . " WHERE " . geoTables::field_session_id
            . " = ? LIMIT 1";
        $this->db->Execute($sql_query, array ($sessionId));

        //delete registry
        geoRegistry::remove('sessions', $sessionId);
        if (isset($this->sessionId) && isset($this->_registry) && $this->sessionId == $sessionId) {
            //make sure we don't save it after it's just been closed
            unset($this->_registry);
            $this->_pendingChanges = false;
        }
    }

    /**
     * Logs the current user out, and destroys all session data.  Note that
     * this will also redirect back to the main page, and if that fails, a white
     * screen will be displayed.
     */
    public function logOut()
    {
        //log out the current user.
        $sid = $this->getSessionId();
        if (!$sid || strlen($sid) < 32) {
            //something is wrong with this sid!
            trigger_error('DEBUG SESSION:' . '[ERROR] -- logOut() - session id stringlen is < 32, so not loging out.
                $sid = ' . $sid);
            return false;
        }
        //kill the session
        trigger_error('DEBUG SESSION:' . '[NOTICE] -- logOut() - Logging out session id = ' . $sid . ', username = '
            . $this->getUserName());
        $sql = "delete from {$this->db->geoTables->session_table} where
            {$this->db->geoTables->field_session_id} = ? LIMIT 1";
        $result = $this->db->Execute($sql, array($sid));
        if (!$result) {
            trigger_error('DEBUG SESSION:' . '[ERROR] -- logOut() - SQL Execute Error: SQL=' . $sql
                . ' Error Reported: ' . $this->db->ErrorMsg());
            return false;
        }

        //delete registry
        geoRegistry::remove('sessions', $sid);
        unset($this->_registry);
        $this->_pendingChanges = false;

        if (!defined('IN_GEO_API')) {
            geoAddon::triggerUpdate(
                'session_logout',
                array('userid' => $this->getUserId(), 'username' => $this->getUserName())
            );
        }

        //clear the user's cookie
        $this->unsetSessionCookies(true);
        //let the rest be done by the main part of the software.
    }
    /**
     * Simple function to unset a user's cookie.  Attempts to account for all
     * the weird browsers out there.
     *
     * @param (Optional)Boolean $agressive If set to true, will also attempt to
     *  unset other common cookies.
     * @param (Optional)String $domain The domain name to set the cookie for.
     */
    public function unsetSessionCookies($agressive = false, $domain = false)
    {
        //make it expire a year ago.
        $domain = $this->_getCookieDomain();
        //unset w/o specifying domain.
        setcookie($this->cookie_name, false, 0, '/', $domain);
        $realDomain = $this->_getCookieDomain(true);
        if ($domain != $realDomain) {
            //also attempt to unset for the detected real domain.
            setcookie($this->cookie_name, false, 0, '/', $realDomain);

            //take care of browsers like konqueror and safari
            $realDomain = preg_replace('/^\./', '', $realDomain);
            setcookie($this->cookie_name, false, 0, '/', $realDomain);
        }
        if ($agressive) {
            //attempt to unset odd cookies that aren't normally set.

            //unset w/o domain.
            setcookie($this->cookie_name, false, 0, '/');
            //also unset any cookies specific to this folder, just in case...
            setcookie($this->cookie_name, false, 0);

            //unset the cookie w/o the .
            $domain = preg_replace('/^\./', '', $domain);
            setcookie($this->cookie_name, false, 0, '/', $domain);
            if ('.' . $domain != $realDomain) {
                $realDomain = preg_replace('/^\./', '', $realDomain);
                setcookie($this->cookie_name, false, 0, '/', $realDomain);
            }
        }
        if (isset($_COOKIE[$this->cookie_name])) {
            unset($_COOKIE[$this->cookie_name]);
        }
    }
    /**
     * Return a unique session ID
     *
     * @return string The session ID
     */
    public function uniqueSessionId()
    {
        $sid = null;
        do {
            $sid = md5(uniqid(rand(), 1));
            $sid = substr($sid, 0, 32);
            $query = "select {$this->db->geoTables->field_session_id} from " . $this->db->geoTables->session_table
                . " where " . $this->db->geoTables->field_session_id . " = '" . $sid . "'";
            $result = $this->db->Execute($query) or die($this->db->ErrorMsg());
        } while ($result->RecordCount() > 0);
        return $sid;
    }
    /**
     * Update the timestamp for a user's session
     * This is similar to Unix's touch command
     *
     * @param string $sessionId
     */
    public function touchSession($sessionId = null)
    {
        if ($sessionId === null) {
            $sessionId = $this->sessionId;
        }
        $current_time = time();//$this->shiftedTime();
        $admin_session = (defined('IN_ADMIN')) ? 'Yes' : 'No';

        $sql_query = "UPDATE " . geoTables::session_table . " SET `last_time` = ? WHERE " . geoTables::field_session_id
            . " = ? AND `admin_session`='$admin_session'";
        $data = array ($current_time, $sessionId);
        if ($this->db->Execute($sql_query, $data) === false) {
            trigger_error("SQL ERROR: Couldn't update session. " . $this->db->ErrorMsg());
            trigger_error('FLUSH MESSAGES');
            if (defined('IN_ADMIN')) {
                die("Database query error.
                <br /><br />The most common cause is that strict mode may need to be turned on in your config.php file.
                To fix, in your <strong>config.php</strong> try setting <strong>\$config_mode = 1;</strong> if it is
                currently set to 0.");
            }
            die("We're sorry, our site is experiencing problems. Please come back later.");
        }
        if (!(defined('IN_ADMIN') || defined('IN_GEO_API'))) {
            geoAddon::triggerUpdate('session_touch', $sessionId);
        }
    }
    /**
     * Get the cookie domain to use.
     *
     * @param boolean $get_real ignore settings set in config file
     * @return string the cookie domain to use.
     * @internal
     */
    private function _getCookieDomain($get_real = false)
    {
        $addDot = false;
        if ($get_real) {
            $domain = geoPC::cleanHostName($_SERVER['HTTP_HOST']);
            if (strpos($domain, '.') === false) {
                //can't use domain w/o dots in it
                return '';
            }
            $subby = geoPC::getInstance()->validateMainDomain($domain);
            if ($subby && strpos($domain, $subby) !== false && $subby !== $domain) {
                //use main domain, not sub-domain, for the cookie
                $domain = $subby;
            }
            $parts = explode('.', $domain);
            if (count($parts) != 4 || !is_numeric($parts[3])) {
                $addDot = true;
            }
        } elseif (COOKIE_DOMAIN !== null) {
            $domain = COOKIE_DOMAIN;
        } else {
            $domain = geoPC::cleanHostName($_SERVER['HTTP_HOST']);
            $subby = geoPC::getInstance()->validateMainDomain($domain);
            if ($subby && strpos($domain, $subby) !== false && $subby !== $domain) {
                //use main domain, not sub-domain, for the cookie
                $domain = $subby;
            }
            //remove the www if it exists.
            $domain = preg_replace('/^www\./', '', $domain);
            if (strpos($domain, '.') === false) {
                //can't use domain w/o dots in it
                return '';
            }
            $parts = explode('.', $domain);
            if (count($parts) != 4 || !is_numeric($parts[3])) {
                $addDot = true;
            }
        }
        //add a . to the beginning to make it work for all sub-domains, if it is not IP
        return (($addDot) ? '.' : '') . $domain;
    }
    /**
     * Get the cookie domain name
     */
    public function getCookieDomainName()
    {
        return $this->_getCookieDomain();
    }
    /**
     * Establishes a new session, and sets the class vars to the settings.
     * ONLY to be used if a cookie does not already exist for the session,
     * or to make a new session in the event that something does not match.
     *
     * @param string $session_id
     * @internal
     */
    private function _newSession($session_id = null)
    {
        //first, check to make sure this is not a crawler.
        if ($this->is_robot()) {
            return false;
        }

        //when an hour has gone by, again using the server's time and not time_shift.
        $current_time = time();
        $custom_id = ($session_id === null) ? $this->uniqueSessionId() : $session_id;
        $ip = $this->getUniqueUserInfo($custom_id);
        $ip_field = $this->getIpField();
        $admin_session = (defined('IN_ADMIN')) ? 'Yes' : 'No';
        $sql_query = "INSERT INTO " . geoTables::session_table . " (" . geoTables::field_session_id
            . ", `user_id`, `last_time`, $ip_field, `admin_session`) values (?,?,?,?,?)";
        $data = array($custom_id, 0, $current_time, $ip, $admin_session);

        if (!$this->db->Execute($sql_query, $data)) {
            trigger_error("ERROR SESSIONS: Couldn't insert session. " . $this->db->ErrorMsg());
            //DB Query error, don't give
            //client any info, in case this is
            //a hacking attempt.
            die("We're sorry, our site is experiencing problems. Please come back later.");
        }

        $this->userId = 0;
        $this->sessionId = $custom_id;
        //manually set the cookie var for other parts that still
        //use _COOKIE - may not work on all server configurations.
        $_COOKIE[$this->cookie_name] = $this->sessionId;

        $this->ip = $ip;
        $user_level = 0;

        if (!defined('IN_GEO_API')) {
            $domain = $this->_getCookieDomain();
            $realDomain = $this->_getCookieDomain(true);
            // This cookie will be unavailable until the next page load
            setcookie($this->cookie_name, $custom_id, 0, '/', $domain);

            if (!defined('COOKIE_DOMAIN') && $domain != $realDomain) {
                //also set the cookie for the "real domain", for stupid crappy browsers.
                //Used when the domain has a www in it.
                setcookie($this->cookie_name, $custom_id, 0, '/', $realDomain);
            }
            geoAddon::triggerUpdate('session_create', $this->sessionId);
        }
        $this->_initRegistry();
        return ($this->sessionId);
    }

    /**
     * Whether or not current page load used SSL connection or not.
     *
     * @return bool
     * @since Version 4.1.0
     */
    public static function isSSL()
    {
        //This is not reduced on purpose, to make easier to maintain/read
        if (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == 1)) {
            //Standard indication that in SSL
            return true;
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            //server is behind a reverse proxy, and protocol used was HTTPS
            return true;
        }
        //also check a couple of non-standard variables that are used on some weird servers
        if (isset($_SERVER['HTTP_SSL']) && (strtolower($_SERVER['HTTP_SSL']) == 'on' || $_SERVER['HTTP_SSL'] == 1)) {
            //Standard indication that in SSL
            return true;
        }
        if (isset($_SERVER['SSL']) && (strtolower($_SERVER['SSL']) == 'on' || $_SERVER['SSL'] == 1)) {
            //Standard indication that in SSL
            return true;
        }


        //not using SSL
        return false;
    }
    /**
     * Detects what field to use depending on if the connection is ssl or not.
     *
     * @return String 'ip' if not on SSL connection, or 'ip_ssl' if on SSL connection.
     */
    public function getIpField()
    {
        return (self::isSSL()) ? 'ip_ssl' : 'ip';
    }
    /**
     * Internal
     * @internal
     */
    private static $status;
    /**
     * Initializes a session, stores a user ID and returns the session ID
     * Use {@link geoSession::getUserId()} to retrieve the user ID associated with the session (if any)
     *
     * @param bool $force If true, even if session was already initialized this page load,
     *   it will re-initialize it.
     * @param bool $force_session_id If true, and ONLY if called in an API call,
     *   if the session does not exist that matches the session cookie, it will
     *   create a session in the DB that matches.  Var added in version 5.2.4
     * @return string The session ID of the new session
     */
    public function initSession($force = false, $force_session_id = false)
    {
        if ($force) {
            //forcing a different session from what may have already happened on this pageload, so clear any stale
            //status
            self::$status = null;
        }

        if (!$force && $this->getSessionId()) {
            //already initialized
            return $this->getSessionId();
        }

        $sid = null;
        //do not use shifted time.  use normal time.
        $current_time = time();
        if (!isset($_COOKIE[$this->cookie_name]) || strlen(trim($_COOKIE[$this->cookie_name])) < 5) {
            //set up a new session.
            trigger_error('DEBUG SESSION:[NOTICE] initSession() - Cookie Not Set, calling _newSession()
status=new');
            if (!isset(self::$status)) {
                self::$status = 'new';
            }
            return ($this->_newSession());
        }
        //we do not set $this->sessionId until we have verified it.
        //get session information
        $ip_field = $this->getIpField();
        $admin_session = (defined('IN_ADMIN')) ? 'Yes' : 'No';
        $sql_query = "SELECT " . geoTables::field_session_id . " AS `sid`, `user_id`, `last_time`, `$ip_field` as `ip`
            FROM " . geoTables::session_table . " WHERE " . geoTables::field_session_id
            . " = ? AND `admin_session`='$admin_session'";
        $result = $this->db->Execute($sql_query, array($_COOKIE[$this->cookie_name]));

        if ($result === false) {
            trigger_error('ERROR SQL SESSION: Attempt to get session data from the database failed.  Error reported by
                mysql:' . $this->db->ErrorMsg() . '
                status=error (db error)');
            if (!isset(self::$status)) {
                self::$status = 'error';
            }
            return false;
        }

        if ($result->RecordCount() != 1) {
            if (strlen($_COOKIE[$this->cookie_name]) == 32 && $force_session_id && defined('IN_GEO_API')) {
                //special case, use the same session id as specified in the
                //cookie, this is used by remote API.  Should not be used normally
                //as it makes session hijacking easy!
                trigger_error("DEBUG SESSION: Session does not exist, and force session id is on, so
                    creating new session that matches the cookie.");
                return $this->_newSession($_COOKIE[$this->cookie_name]);
            }

            //if it is not found, must be an old cookie, or potential hacker
            trigger_error('DEBUG SESSION:' . '[ERROR] initSession() - Amount of results for session is: '
                . $result->RecordCount() . ', when it should be 1. going to create new session.
status=changed (record count for session is wrong)');
            if (!isset(self::$status)) {
                self::$status = 'changed';
            }
            return ($this->_newSession());
        }

        //found a session in the db that matches, so validate that session.
        $credentials = $result->FetchRow();
        $sid = $credentials['sid'];
        $ip = $credentials['ip'];
        $user_id = $credentials['user_id'];
        if ($credentials['ip'] == '0') {
            //ip field has not be set yet for this connection type, so set it.
            $ip = $this->getUniqueUserInfo($sid);
            $sql_query = "UPDATE " . $this->db->geoTables->session_table . "
                SET `$ip_field` = ? WHERE " . $this->db->geoTables->field_session_id
                . " = ? AND `admin_session`='$admin_session' LIMIT 1";
            $data = array ($ip, $sid);
            $result = $this->db->Execute($sql_query, $data);
            if (!$result) {
                trigger_error('ERROR SQL SESSION: Error in SQL when updating ip.');
            } else {
                $credentials['ip'] = $ip;
            }
        }
        if (
            $credentials['ip'] !== $this->getUniqueUserInfo($sid)
            || ($current_time - $credentials["last_time"]) > $this->_getSessionTimeout($user_id)
        ) {
            // Cookie is invalid

            //do some debugging output
            if ($credentials['ip'] !== $this->getUniqueUserInfo($sid)) {
                trigger_error('ERROR SESSION:' . '[NOTICE] initSession() : The user\'s session data does not match.
                    Saved Session Data: ' . $credentials['ip'] . ' -- getUniqueUserInfo() says Session Data: '
                    . $this->getUniqueUserInfo($sid) . ' -- Session ID: ' . $sid . '
status=change (user-agent changed)');
            }
            if (($current_time - $credentials['last_time']) > $this->_getSessionTimeout($user_id)) {
                trigger_error('DEBUG SESSION:' . '[NOTICE] initSession() : The user\'s session has timed out.
                    Last visit: ' . ($current_time - $credentials['last_time']) . ' Session Time Out: '
                    . $this->_getSessionTimeout($user_id) . ' -- Session ID: ' . $sid . '
status=change (session timed out)');
            }
            trigger_error('ERROR SESSION: The session that was stored (or not stored) in the
database is not valid.  Here is the data returned from the database:<br>' . print_r($credentials, 1));
            //end of debug output.

            if (strlen($credentials['ip']) !== 40 && strlen($credentials['ip']) > 0) {
                //most likely, the upgrade script did not take.
                trigger_error('ERROR SESSION: The classified session string length is ' . strlen($credentials['ip']) .
                    ' when it should be 40.  Usually this is fixed by running the upgrade.');
                if ($this->cookie_name == 'admin_classified_session') {
                    //they are on admin side... print out an error so they contact us.
                    //solution is to run the upgrade script again. the ip column needs to be varchar(40)
                    echo '<span style="color:red; align:center;">ADMIN LOGIN ERROR:  Session table in the database may
                        need to be updated.  Please contact Geodesic Support.</span><br>';
                }
            }
            //cookie is invalid, so start a new session.
            if (!isset(self::$status)) {
                self::$status = 'changed';
            }
            return ($this->_newSession());
        }

        //if it makes it this far, the session is valid.
        $this->sessionId = $sid;
        $this->ip = $ip;
        $this->userId = intval($user_id);


        //their classified id matches up, and it appears to be coming from the
        //same computer and network, so let them proceed.

        //make sure the session time is updated.
        if (isset($this->sessionId) && strlen($this->sessionId)) {
            //only touch session if session id is good.
            $this->touchSession();
        } else {
            return 0;
        }
        if (!isset(self::$status)) {
            self::$status = 'confirmed';
        }

        $this->_initRegistry();

        return $this->sessionId;
    }
    /**
     * Initialize the session registry
     *
     */
    private function _initRegistry()
    {
        if (!$this->sessionId) {
            //can't do a thang if we don't have an ID
            return;
        }
        //the registry..
        //Unserialize registry
        if (!is_object($this->_registry)) {
            $this->_registry = new geoRegistry();
        }
        $this->_registry->setName('sessions');
        $this->_registry->setId($this->sessionId);
        $this->_registry->unSerialize();

        if (!$this->is_robot()) {
            //while we're here, let's snag the visitor's device type and save it
            $mobile = self::isMobile();
            if ($mobile) {
                $mobile = self::isTablet() ? 'tablet' : 'phone';
            } else {
                $mobile = 'desktop';
            }
            $this->_registry->set('device_type', $mobile);
            //and also do some rudimentary browser testing. There's probably lots of room to improve this, but just a
            //quick baseline...
            $ua = getenv('HTTP_USER_AGENT');
            $browser_type = 'Unknown';
            // phpcs:disable Generic.Files.LineLength.TooLong
            if (strpos($ua, 'iPhone') !== false) {
                $browser_type = 'iPhone';
            } elseif (strpos($ua, 'Android') !== false) {
                $browser_type = 'Android';
            } elseif (strpos($ua, 'Firefox/') !== false) {
                //e.g. Mozilla/5.0 (Windows NT 10.0; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0
                $browser_type = 'Firefox';
            } elseif (strpos($ua, 'Edge/') !== false) {
                //Edge
                //e.g. Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586
                $browser_type = 'IE';
            } elseif (strpos($ua, 'Trident/') !== false) {
                //iexplore.exe
                //e.g. Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko
                $browser_type = 'IE';
            } elseif (strpos($ua, 'Chrome/') !== false) {
                //e.g. Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36
                //note: check this AFTER Edge
                $browser_type = 'Chrome';
            }
            if ($browser_type !== 'Unknown') {
                //don't store browser type if we're not sure what it is from the above -- it's probably a bot
                $this->_registry->set('browser_type', $browser_type);
            }
            // phpcs:enable Generic.Files.LineLength.TooLong
        }


        $this->_registry->serialize();
    }
    /**
     * Gets the session status
     * @return string
     */
    public function getStatus()
    {
        if (!isset(self::$status)) {
            $this->initSession();
        }
        return (self::$status);
    }

    /**
     * Get session timeout
     *
     * @param string $who either "client" or "admin"
     * @return int the session timeout in seconds.
     * @internal
     */
    private function _getSessionTimeout($who = 'client')
    {
        $var_name = ($who == 'admin' || $who == '1') ? 'session_timeout_admin' : 'session_timeout_client';

        $timeout = intval($this->db->get_site_setting($var_name));

        if ($timeout < (5 * 60)) {
            //do not allow timeout of less than 5 min.
            $timeout = 3600;
            $this->db->set_site_setting($var_name, $timeout);
        }

        return $timeout;
    }
    /**
     * Return the user ID stored in the Session object
     *
     * @return integer User ID
     */
    public function getUserId()
    {
        return intval(isset($this->userId) ? $this->userId : 0);
    }

    /**
     * Gets the username.
     *
     * @return string Return the user name according to the current user ID of the current session.
     */
    public function getUserName()
    {
        if ((!isset($this->userName) || !strlen($this->userName)) && $this->getUserId() > 0) {
            $userId = $this->getUserId();
            $user = geoUser::getUser($userId);
            $this->userName = (is_object($user)) ? $user->username : '';
        }

        return $this->userName;
    }
    /**
     * Return a unix timestamp representing the time-shifted time
     * The time is adjusted using the server's time zone offset
     *
     * @return int
     */
    public function shiftedTime()
    {
        $time = time() + ($this->db->get_site_setting('time_shift') * 3600);
        return $time;
    }
    /**
     * Sets the language in a cookie
     * If a language ID is passed in, it takes precedence
     *
     * @param int $languageId
     */
    public function setLanguage($languageId = null)
    {
        $expires = time() + 31536000;
        $check_id = true;
        if (isset($_GET['set_language_cookie']) && !isset($_POST['set_language_cookie'])) {
            //allow language to be set via a link.
            $_POST['set_language_cookie'] = $_GET['set_language_cookie'];
        }

        if (isset($languageId) && $languageId !== null) {
            setcookie("language_id", $languageId, $expires);
            //$languageId = $languageId;
        } elseif (isset($_POST['set_language_cookie']) && $_POST["set_language_cookie"]) {
            //make sure language set is a number.
            $_POST['set_language_cookie'] = intval($_POST['set_language_cookie']);
            setcookie("language_id", $_POST["set_language_cookie"], $expires);
            $languageId = $_POST["set_language_cookie"];
            //$auth->reset_language($db,$_REQUEST["set_language_cookie"]);
        } elseif (isset($_COOKIE["language_id"]) && $_COOKIE['language_id']) {
            //make sure language id is number.
            $_COOKIE['language_id'] = intval($_COOKIE['language_id']);
            $languageId = $_COOKIE["language_id"];
        } else {
            //get the language from the data accessor
            $languageId = $this->db->getLanguage(true);
            //no need to check id, we know it is all good...
            $check_id = false;
        }
        if ($check_id) {
            //make sure language is valid.
            $sql = 'SELECT `language_id` FROM `geodesic_pages_languages` where `active` = 1 AND `language_id` = ?';
            $result = $this->db->Execute($sql, array($languageId));
            if (!$result) {
                //wuaaa??  bad db execute!?!
                trigger_error('ERROR SQL: Sql: ' . $sql . ' Error Msg: ' . $this->db->ErrorMsg());
            } elseif ($result->RecordCount() == 0) {
                //bad language id!
                //get the default language id.
                $languageId = $this->db->getLanguage(true);
            }
        }
        $this->languageId = (int)$languageId;
    }

    /**
     * Returns the language ID stored in the Session object
     *
     * @return int
     */
    public function getLanguage()
    {
        if (!isset($this->languageId)) {
            $this->setLanguage();
        }
        return isset($this->languageId) ? $this->languageId : 1;
    }

    /**
     * Get the device detected (or manually set) for the current session.
     *
     * @return string Will be one of the geoSession::DEVICE_* constants
     * @since Version 7.3.0
     */
    public function getDevice()
    {
        if (!isset($this->device)) {
            $this->setDevice();
        }
        return $this->device;
    }

    /**
     * Usually used in app top, this is used to detect and store the device for later
     * use in the software.
     *
     * @param string $forceDevice If specified, will make the device match it
     * @since Version 7.3.0
     */
    public function setDevice($forceDevice = null)
    {
        if (!$forceDevice && isset($this->device)) {
            //no need to call again...
            return;
        }

        //For now, limit the possible devices to either mobile or desktop.
        $validDevices = array (self::DEVICE_MOBILE, self::DEVICE_DESKTOP);

        if (!in_array($forceDevice, $validDevices)) {
            //specified device not valid
            $forceDevice = null;
        }

        $mobileDetect = self::getMobileDetect();

        //first see what is detected...
        $detected = self::DEVICE_DESKTOP;
        if ($mobileDetect->isMobile()) {
            //detected a mobile device!
            $detected = self::DEVICE_MOBILE;
        }


        if (!$forceDevice && isset($_GET['forceDevice']) && in_array($_GET['forceDevice'], $validDevices)) {
            $forceDevice = trim($_GET['forceDevice']);

            if ($forceDevice === $detected) {
                //it is same as what was detected...
                if ($this->get('forceDevice')) {
                    //and it was previously forced... so un-force
                    $this->set('forceDevice', false);
                }
            } else {
                //this is different than what is detected, so save it in session
                //var and use it instead...
                $this->set('forceDevice', $forceDevice);
            }
        } elseif (!$forceDevice) {
            //see if it was previously set in session
            $forceDevice = $this->get('forceDevice');
        }
        //now set the device, either to the forced device (if set) or the detected device
        $this->device = ($forceDevice) ? $forceDevice : $detected;
    }

    /**
     * Gets a unique sha1 hash for the current user
     *
     * @param String $salt Used to salt the unique hash.  When used for ip column,
     *  needs to be the session id.
     * @return String alpha numeric string (HEX string, which is 0-9, A-F) 40 chars long
     */
    public function getUniqueUserInfo($salt)
    {
        //this does not totally stop session stealing, but it makes it a LOT harder.

        $data = '';
        //no longer lock to IP, since IP can change for people
        //behind rotating proxies (like AOL Users)

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            //lock to user-agent string
            //Some apps don't play nice, they like to change the user-agent
            //between page loads, even when the SSL has not changed.  For those,
            //the offending change needs to be stripped from the user agent.
            $bad_patterns = array();

            ###Add patterns to strip here - be sure to document each pattern###

            //According to stuff found online, yplus is from Yahoo Parent Controls.
            //For some reason, the yplus part of the user-agent is present in some page loads,
            //and not in others, and it does not have to do with HTTP/HTTPS...
            $bad_patterns [] = '/\;[\s]*yplus[^;)]+/'; //match: "; yplus 5.1.04b"

            //only one bad pattern so far... hopefully won't need to have too many more.

            $data .= preg_replace($bad_patterns, '', $_SERVER['HTTP_USER_AGENT']);
        }
        //do not lock to encoding, seems the encoding changes for IE7 for some reason...
        //salt it for fun.
        $data .= $salt;
        //sha1 it
        $before = $data;
        $data = sha1($data);
        trigger_error('DEBUG SESSION:' . '[NOTICE] getUniqueUserInfo() - salt=' . $salt . '
---- Un-Hashed=
' . $before . ' ---- Hashed=' . $data);
        return $data;
    }

    /**
     * Check to see if the current session is a detected robot.
     *
     * @return boolean Returns true if user agent is in list of known user agents.
     */
    public function is_robot()
    {
        if (defined('IS_ROBOT')) {
            return IS_ROBOT;
        }
        //$robots is array of "full user agents", $robotP is array of "partial user agents".
        //Both are set in robot_list.php.
        $robots = $robotP = array();
        //get the initial array of known robot's user agent string
        require(GEO_BASE_DIR . 'robot_list.php');
        //see if there are additional robots list specified by beta tools.
        $additional_robots = $this->db->get_site_setting('additional_robots_list', true);
        if ($additional_robots) {
            //split up the list by the || delimiter, and add the entries to the main array.
            $additional = explode('||', $additional_robots);
            $robots = array_merge($robots, $additional);
            //echo 'additional robots to do';
            //var_dump($additional_robots);
        }
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        //un-comment these lines to see count of how big each list has gotten (shown in debug messages)
        //trigger_error("DEBUG ROBOT:  Checking against ".count($robots)." \"full\" user agents and ".count($robotP)
        //." \"partial\" user agents...");

        if (in_array($user_agent, $robots)) {
            //this is one of the "full" user agent matches...
            trigger_error("DEBUG ROBOT SESSION: Full robot user agent match!  If this is NOT a bot, let us know the
                following info:\nUser agent: \n$user_agent\n");
            define('IS_ROBOT', true);
            return true;
        }

        //now loop through all $robotP which are "partial" strings
        foreach ($robotP as $partial) {
            if (strpos($user_agent, $partial) !== false) {
                //matches part of string so we don't have to include every single
                //variation of a user-agent that a particular robot uses
                trigger_error("DEBUG ROBOT SESSION:  Partial robot user agent match! If this is NOT a bot, let us know
                    the following info:\nPartial Match:\n$partial\nuser agent:\n$user_agent\n");
                define('IS_ROBOT', true);
                return true;
            }
        }

        if (isset($_SERVER['HTTP_X_GOOG_SOURCE'])) {
            //special case: this is google's +1 scraper, which can't be detected by UA
            //see http://stackoverflow.com/questions/10815935/google-button-for-page-that-requires-redirect
            //and https://groups.google.com/d/msg/google-plus-developers/BlqkRILGWDs/qmNUz66FCW8J
            trigger_error('DEBUG ROBOT SESSION: this is one of the weird google bots (likely a +1 scrape)');
            define('IS_ROBOT', true);
            return true;
        }

        trigger_error("DEBUG ROBOT SESSION: No robot user agent match, this is most likely a 'real visitor', or a bot
            that is not yet on the list!  If you think this is a bot, let us know - the User agent:\n$user_agent\n");
        define('IS_ROBOT', false);
        return false;
    }

    /**
     * Gets the specified item from the session registry, or if item is one of the "main"
     * items it gets that instead.
     *
     * @param string $item
     * @param mixed $default What to return if the item is not set.
     * @return Mixed the specified item, or false if item is not found.
     */
    public function get($item, $default = false)
    {
        if (method_exists($this, 'get' . ucfirst($item))) {
            $methodName = 'get' . ucfirst($item);
            return $this->$methodName();
        }
        if (!$this->_registry) {
            //registry not initialized yet, return default.
            return $default;
        }
        return $this->_registry->get($item, $default);
    }

    /**
     * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
     *  of something from the registry.
     *
     * @param string $item
     * @param mixed $value
     */
    public function set($item, $value)
    {
        if (method_exists($this, 'set' . ucfirst($item))) {
            $methodName = 'set' . ucfirst($item);
            return $this->$methodName($value);
        }
        if (method_exists($this, 'get' . ucfirst($item))) {
            /**
             * this has a get method for it, but not a set method...
             * if we were to save it in the registry it would be locked
             * in there since the get method would not use the registry
             * to get the value.
             */
            return;
        }
        if (!$this->_registry) {
            //registry not initialized yet...
            return;
        }
        //there are now pending changes..
        $this->touch();
        return $this->_registry->set($item, $value);
    }

    /**
     * Use when this object has been changed, so that when it is serialized, it
     * will know there are changes that need to be serialized.
     *
     * Note that this is automatically called internally when any of the set functions are used.
     *
     */
    private function touch()
    {
        $this->_pendingChanges = true; //there are now pending changes
    }

    /**
     * Serializes the current session (saves changes in the database)
     *
     */
    public function serialize()
    {
        if (!$this->_pendingChanges) {
            //nothing to serialize
            return;
        }
        if ($this->_registry && $this->sessionId) {
            //Serialize registry, go ahead and re-set the "registry identity" in case anything
            //goofed around with it before now.
            $this->_registry->setId($this->sessionId);
            $this->_registry->setName('sessions');//make sure name did not get lost or something
            $this->_registry->serialize();//serialize registry
        }

        $this->_pendingChanges = false;
    }
}
