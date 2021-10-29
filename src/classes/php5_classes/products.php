<?php

// Begin Christmas ornaments code

/**
 * Encoded file, used for license validation, database access, and other core-level functionality.  Source code view
 * of this file is not available.
 *
 * @package System
 */

/**
 * Make sure the DataAccess class is included, so we have a db connection.
 */
require_once(CLASSES_DIR . PHP5_DIR . 'DataAccess.class' . ENCODE_EXT . '.php');
/**
 * Needed for communicating with license server.
 */
require_once(CLASSES_DIR . 'rpc/XMLRPC.class.php');

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
    private $errors, $license_key, $api_server, $remote_port, $remote_timeout,
        $local_key_storage, $read_query, $update_query, $local_key_path,
        $local_key_name, $local_key_transport_order, $validate_download_access,
        $status_messages, $valid_for_product_tiers, $db, $geo, $add;

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
            if ($type == 'geoPC') {
                //Call discover_type here, or it can cause infinite recursion
                //if done as part of __construct()
                self::$_instance[$type]->discover_type();
            }
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
        //used for internal settings that we don't want to show in print_r
        $this->geo = new _geoInternalSettings();

        //The secret is in the key...  And the secret key is to be secret!
        $this->geo->secret_key = 'SECRET';

        $this->geo->classType = $type;

        $this->geo->errors = false;
        $this->remote_port = 80;
        $this->remote_timeout = 10;
        $this->geo->valid_local_key_types = array('spbas');
        $this->geo->local_key_type = 'spbas';
        //database or filesystem
        $this->local_key_storage = 'database';
        $this->read_query = false;
        $this->update_query = false;
        $this->local_key_path = './';
        $this->local_key_name = 'license.txt';
        $this->local_key_transport_order = 'scf';
        $this->validate_download_access = false;
        $this->valid_for_product_tiers = false;

        $this->geo->key_data = array(
                        'custom_fields' => array('seats' => -1),
                        'download_access_expires' => 0,
                        'license_expires' => 0,
                        'local_key_expires' => 0,
                        'status' => 'Invalid',
                        );

        $this->status_messages = array(
                        'active' => 'This license is active.',
                        'suspended' => 'Error: This license has been suspended.<br /><br />
							If you have a leased license, this typically means your payments have lapsed for more than 15 days; in which case, once you have paid the outstanding amount due the license will be activated.',
                        'expired' => 'Error: This license has expired.',
                        'pending' => 'Error: This license is pending review.',
                        'download_access_expired' => 'Error: This version of the software was released ' .
                                                     'after your download access expired.',
                        'old_key' => 'Invalid or outdated license key entered!',
                        );


        $this->status_messages['download_access_expired'] .= "
					<br /><br />Please Renew your support &amp; updates package
					from the <a href='https://geodesicsolutions.com/geo_store/customers'>Client Area</a> to be able to proceed with the update.
					<br /><br />If you have any questions or think there may be an error in our records, please feel free to
					contact us through <a href='http://geodesicsolutions.com'>our website</a>.";

        //Set all the stuff for this site
        if ($this->geo->classType == 'geoPC') {
            //do things the easy way and just use whatever is in config
            $this->db = DataAccess::getInstance();
        } else {
            $this->db = function_exists('mysqli_connect') ? ADONewConnection('mysqli') : ADONewConnection('mysql');
        }

        $this->api_server = 'http://geodesicsolutions.com/geo_store/api/index.php';

        if (defined('IN_ADMIN')) {
            //allow license cache to be cleared using URL
            $this->clear_cache_local_key();
        }

        if ($this->geo->classType == 'geoPC') {
            $this->license_key = '' . $this->db->get_site_setting('license');

            if ($this->license_key && !$this->_keyHasValidPrefix($this->license_key)) {
                //first, this shouldn't normally happen as it would have updated
                //the license key during update, so first do what update does
                $this->_geoCore_init_listingTypes($this->license_key);

                //old key saved in system, clear it out!
                $this->license_key = '';
                self::clearLicenseKey();
            }

            //NOTE:  discover_type will be called from getInstance() to prevent
            //infinite loops when using developer type
        } else {
            //this is update...
            //make sure it validates download access
            $this->validate_download_access = true;

            $this->geo->release_date = geoUpdateFactory::getReleaseDate();
        }
    }

    /**
     * Set up GeoCore master switches so that they mirror the version in use
     * before the update, when coming from the old split products
     *
     * @param string $oldKey
     * @internal
     */
    private function _geoCore_init_listingTypes($oldKey)
    {
        $type = 0;
        if (stripos('classauctions', $oldKey) !== false) {
            //coming from classauctions -- use existing settings
            return true;
        } elseif (stripos('classifieds', $oldKey) !== false) {
            $type = 1;
        } elseif (stripos('auctions', $oldKey) !== false) {
            $type = 2;
        } else {
            //not an old product -- skip this step
            return true;
        }

        //if we're coming from classauctions, leave things as they are -- the upgrade sorts that later
        //if coming from something else, set it up to look like the old classauctions switch is set, so that the upgrade will sort it later
        //***NB: even though this setting isn't used in the software anymore, this next bit is important from the upgrade from pre-v7***
        $sql = "UPDATE `geodesic_classifieds_configuration` SET `listing_type_allowed` = ? WHERE `listing_type_allowed`=0";
        return $this->db->Execute($sql, array($type));
    }

    /**
     * Validate the license
     *
     * @param bool $forceRemote
     * @param string $addon
     * @return string
     * @internal
     */
    private function validate($forceRemote = false, $addon = '')
    {
        $geo = ($addon) ? $this->add[$addon] : $this->geo;
        // Make sure we have a license key.
        if (!$this->license_key) {
            trigger_error('DEBUG LICENSE: LOCAL: local license key empty');
            return $geo->errors = 'Error: The license key variable is empty';
        }
        if ($addon && !$geo->license_key) {
            //no valid license key
            trigger_error('DEBUG LICENSE: LOCAL: local license key for addon not set');
            return $geo->errors = 'Error: The addon license key variable is empty';
        }

        // Make sure we have a valid local key type.
        if (!in_array(strtolower($geo->local_key_type), $geo->valid_local_key_types)) {
            return $geo->errors = 'Error: An unknown type of local key validation was requested.';
        }

        // Read in the local key.
        $local_key = $this->db_read_local_key($forceRemote, $addon);

        // Did reading in the local key go ok?
        if ($geo->errors) {
            return $geo->errors;
        }

        // Validate the local key.
        return $this->validate_local_key($local_key, $forceRemote, $addon);
    }

    /**
     * Performs maintenance actions specific to GeoTurbo.
     * @internal
     */
    public static function GTMaint()
    {
        $status = self::geoturbo_status();
        if (!$status) {
            //this is not GeoTurbo
            return;
        }
        if ($status === 'on') {
            //license set to 'only' a single listing type, which means this is not GT Plus, which means it doesn't get to charge for things.
            geoMaster::getInstance()->set('site_fees', false);
        }

        $db = DataAccess::getInstance();
        //GT phones home on license checks daily; see if that needs to be done, and then do it
        //NOTE: we only do this when NOT in the admin, because revalidating in admin logs the admin user out (even on a success)

        //SKIPPING THE PHONE HOME FOR NOW, UNTIL WE GET MORE SUBSCRIBERS
        if (false && !defined('IN_ADMIN')) {
            //retrieve the last-run time from the db
            //last check time is stored scrambled (XOR'd against a particular integer) to further obfuscate its purpose. Undo that now.
            //if the data is missing, then assume we haven't checked at all yet, and check!
            $scramble = 2450557518; //chosen at random
            $lastCheck = $db->get_site_setting('gt_data') ? ((int)$db->get_site_setting('gt_data') ^ $scramble) : 0;

            if ($lastCheck + 86400 < time()) { //not using geoUtil::time here, because that can be manipulated externally
                //re-validate the license
                $pc = self::getInstance();
                $pc->validate(true);
                if ($pc->geo->errors) {
                    //validation failed

                    //make a note to show the admin
                    $db->set_site_setting('gt_license_notify', 1);

                    //show site_off and exit.
                    $pc->show_site_off();
                    exit;
                }
                //license is OK -- store check time (scrambled)
                $db->set_site_setting('gt_license_notify', 0); //turn off admin-notification for failed license, if it is on
                $db->set_site_setting('gt_data', (time() ^ $scramble));
            } else {
                //it's not time to check the license yet, so do nothing
            }
        } else {
            //in the admin, so don't do the license check BUT if it has already failed, show a message about it
            if ($db->get_site_setting('gt_license_notify') == 1) {
                geoAdmin::m('ATTENTION: Your license has been suspended for non-payment of hosting fees. Contact <a href="mailto:sales@geodesicsolutions.com">Geodesic Solutions</a> to re-activate', geoAdmin::NOTICE);
            }
        }
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
        $info = new _geoInternalSettings();

        $info->local_key_type = 'spbas';
        $info->valid_local_key_types = $this->geo->valid_local_key_types;
        $info->secret_key = $secret;
        $info->prefix = $prefix;
        $info->reg = geoAddon::getRegistry($addon);
        $change = false;
        if ($license_key == '') {
            $license_key = $info->reg->get($prefix . 'license_key');
        } else {
            //note: we're only going to save the reg if it validates...
            $change = true;
            if ($license_key != $info->reg->get($prefix . 'license_key')) {
                //different license key, clear the license data
                $info->reg->set($prefix . 'license_data', false);
            }
        }
        $info->license_key = $license_key;

        $this->add[$addon] = $info;
        $this->validate(false, $addon);


        if ($info->errors) {
            trigger_error("DEBUG LICENSE: LOCAL: Errors when trying to validate addon: " . $info->errors);
            return false;
        }
        if ($change) {
            //it is valid, save settings
            $info->reg->set($prefix . 'license_key', $license_key);
            $info->reg->save();
        }
        //no errors, return true
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
        if (!isset($this->add[$addon])) {
            return false;
        }
        if ($this->add[$addon]->secret_key != $secret) {
            //secret not match, don't give out info
            return false;
        }
        return $this->add[$addon]->key_data['custom_fields'];
    }

    /**
     * Used to verify license for the update process.
     *
     * @param string $license
     * @return boolean
     * @internal
     */
    public function verifyLicenseForUpdate($license)
    {
        if (!$this->geo->classType == 'geoUpdateFactory') {
            //don't work for anyone but updates
            return false;
        }

        $this->license_key = $license;
        $this->validate();

        if (!$this->geo->errors) {
            //save it to DB
            $this->db->Execute("REPLACE INTO `geodesic_site_settings` SET `setting`='license', `value`=?", array($license));
            //remove any settings so they can be re-retrieved
            $this->db->Execute("DELETE FROM `geodesic_site_settings_long` WHERE `setting`='license_data'");
            $this->db->Execute("DELETE FROM `geodesic_site_settings` WHERE `setting` IN ('supportCheck', 'lastSupportCheck', 'packageId')");
            return true;
        }
        return false;
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
        $geo = ($addon) ? $this->add[$addon] : $this->geo;
        if (!$geo->mustAgree) {
            return false;
        }
        //the text for must agree
        $mustAgree = <<<agreement
<div id="license_agree">
	<strong>Important:</strong>  According to our records, this license key is
	already in use on another location.  Please note that each license may be
	installed on <strong>only one "live" location</strong>. A second installation may be
	installed for "testing" purposes only. Using a single license on multiple
	"live" locations without express permission from Geodesic Solutions, LLC.
	is a violation of the license agreement and can void the license key.
	<br /><br />
	<label><input type="checkbox" name="agreed" value="1" /> I understand the above. This license is only being used on one "live" installation. Any other installations are being used for testing purposes only. </label>
</div>
agreement;
        return $mustAgree;
    }
    /**
     * gets license errors for display.
     * @param string $addon
     * @return string|array
     */
    public function errors($addon = '')
    {
        return ($addon) ? $this->add[$addon]->errors : $this->geo->errors;
    }

    private function _keyHasValidPrefix($key)
    {
        $validPrefixes = array('GeoCore','GeoTurbo','GWL','DAV','DEMO');
        foreach ($validPrefixes as $p) {
            if (strpos($key, $p) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validate the local license key.
     *
     * @param string $local_key
     * @param bool $forceRemote Force it to validate key remotely
     * @param string $addon
     * @param bool $recurse
     * @return string
     * @internal
     */
    private function validate_local_key($local_key, $forceRemote = false, $addon = '', $recurse = false)
    {
        $geo = ($addon) ? $this->add[$addon] : $this->geo;

        if ($geo->save_local_key || $forceRemote) {
            $raw_local_key = $local_key;
        }
        $key_data = $geo->key_data = array(
            'custom_fields' => array(),
            'download_access_expires' => 0,
            'license_expires' => 0,
            'local_key_expires' => 0,
            'status' => 'Invalid',
        );
        if ($local_key == 'must agree') {
            //special case, they must agree before it will work.
            $geo->mustAgree = true;
            return $geo->errors = 'This license is already being used on at least one other installation location.  You must agree to the terms before continuing.';
        }

        // Convert the license into a usable form.
        $local_key = base64_decode(str_replace("\n", '', urldecode($local_key)));

        // Break the key into parts.
        $parts = explode('{spbas}', $local_key);

        // If we don't have all the required parts then we can't validate the key.
        if (!isset($parts[1])) {
            if (defined('IN_ADMIN') && !$recurse) {
                //when in admin panel, allow it to attempt to validate remotely
                //when the local data is totally messed up.
                $local_key = $this->fetch_new_local_key($addon);
                $geo->save_local_key = true;
                return $this->validate_local_key($local_key, $forceRemote, $addon, true);
            }
            if (!$addon && $this->license_key && !$this->_keyHasValidPrefix($this->license_key)) {
                return $geo->errors = $this->status_messages['old_key'];
            }
            return $geo->errors = 'Error: The local license key has been tampered with or is invalid. (spbas: -1)';
        }

        // Make sure the data wasn't forged.
        if (md5($geo->secret_key . $parts[0]) != $parts[1]) {
            if (!$addon && defined('IN_ADMIN')) {
                //see if product type is incorrect before checking for "forged"
                $key_data = unserialize($parts[0]);
                if ($key_data && isset($key_data['custom_fields']['product_type']) && $key_data['custom_fields']['product_type'] !== 'core') {
                    //old key data found in system, clear it out!
                    return $geo->errors = $this->status_messages['old_key'];
                }
            }
            if (!$addon) {
                //give a specialized message...
                return $geo->errors = $this->_tamperedCoreLicenseError();
            }
            return $geo->errors = 'Error: The local license key has been tampered with or is otherwise invalid. (spbas: 0)';
        }

        // The local key data in usable form.
        $key_data = unserialize($parts[0]);
        $instance = $key_data['instance'];
        unset($key_data['instance']);
        $enforce = $key_data['enforce'];
        unset($key_data['enforce']);

        $host_array = array();
        foreach ($instance['domain'] as $domain) {
            $host_array[] = md5($geo->secret_key . $domain);
        }
        $key_data['host_array'] = $host_array;

        $license_key = ($addon) ? $geo->license_key : $this->license_key;

        // Make sure we are dealing with an active license.
        if ((string)$key_data['status'] != 'active') {
            if ($forceRemote) {
                //clear local key, since it is expired, and new status on license is not active.
                //Since we only re-check expired license data when in admin panel, this will
                //make client side not validate any more either. (ONLY done when status is not active on license)
                //This will result in the local data not getting set if status is not active,
                //so don't need to worry about local data getting "stuck" with inactive status
                $this->clear_cache_local_key(true, $addon);
                trigger_error("ERROR LICENSE: LOCAL: License status for ({$license_key}) is '{$key_data['status']}', not active!  Clearing old local key data.");
            } else {
                trigger_error("ERROR LICENSE: LOCAL: License status for ({$license_key}) is '{$key_data['status']}', not active!");
            }
            return $geo->errors = $this->status_messages[$key_data['status']];
        }

        // License string expiration check
        if ((string)$key_data['license_expires'] != 'never' && (int)$key_data['license_expires'] < time()) {
            if (defined('IN_ADMIN') && !$forceRemote) {
                //if in admin panel, let it try to re-validate remotely
                trigger_error('DEBUG LICENSE: LOCAL: License is expired, will attempt to re-validate the license remotely since in admin panel.');
                return $this->validate(true, $addon);
            } elseif (defined('IN_ADMIN')) {
                //this shouldn't happen, since it should have changed license
                //status remotely to "expired", so the re-check should have returned
                //a license with status set to expired, not with active license with expired date,
                //most likely server date is wrong.
                trigger_error('DEBUG LICENSE: LOCAL: License is expired, even after getting fresh copy from license server.  Check to make sure server\'s date is accurate.');
            }
            return $geo->errors = $this->status_messages['expired'];
        }

        if ($addon && !$key_data['custom_fields']['addon_' . $addon]) {
            trigger_error('DEBUG LICENSE: LOCAL: License key does not seem to be valid for the addon.');
            return $geo->errors = 'License not valid for this addon.';
        }

        // Local key expiration check

        //test for expired local...
        //if (!$forceRemote) $key_data['local_key_expires'] = time() - 100;

        //die ("key data: <pre>".print_r($key_data,1));

        $leased = (isset($key_data['custom_fields']['leased']) && $key_data['custom_fields']['leased']);

        if ($leased) {
            //local key data works slightly different for leased..
            $expires = (int)$key_data['local_key_expires'];
            if (!defined('IN_ADMIN')) {
                //give front side 16 day buffer
                $expires += 86400 * 16;
            }
            if ($expires < time()) {
                //oops, expired!
                if (!$forceRemote) {
                    trigger_error("DEBUG LICENSE: LOCAL: Local license data is expired on leased license " . date('M d Y', $expires) . ", will now attempt to validate remotely.");
                    return $this->validate(true, $addon);
                } else {
                    //this shouldn't happen, we already forced to get remotely, and
                    //remote data returned is already expired??? Perhaps site's time is off by a few months or years...
                    trigger_error("ERROR LICENSE: LOCAL: Local license data is expired on leased license, even after getting fresh copy from license server.  Check to make sure server's date is accurate.");
                    return false;
                }
            }
        } elseif (defined('IN_ADMIN') && (string)$key_data['local_key_expires'] != 'never' && (int)$key_data['local_key_expires'] < time()) {
            // It's expired, go remote for a new key! (only if in admin panel though)
            if (!$forceRemote) {
                trigger_error("DEBUG LICENSE: LOCAL: Local license data is expired, will now attempt to validate remotely.");
                return $this->validate(true, $addon);
            } else {
                //this shouldn't happen, we already forced to get remotely, and
                //remote data returned is already expired??? Perhaps site's time is off by a few months or years...
                trigger_error("ERROR LICENSE: LOCAL: Local license data is expired, even after getting fresh copy from license server.  Check to make sure server's date is accurate.");
                return false;
            }
        }

        // Download access check
        if ($this->validate_download_access && (int)$key_data['download_access_expires'] < $geo->release_date && !($key_data['custom_fields']['leased'] && !$key_data['download_access_expires'])) {
            //only if validate download access, and NOT a leased license with download access set to 0
            return $geo->errors = $this->status_messages['download_access_expired'];
        }

        // Loop all instances until we find one that's valid.
        $access_details = $this->access_details();
        $is_valid_for_location = true;
        $fail_logs = array();
        foreach ((array)$enforce as $key) {
            if (!isset($access_details[$key]) || !isset($instance[$key])) {
                $is_valid_for_location = false;
                break;
            }

            $match_found = $ip_range = array();
            if (in_array($key, array('ip','server_ip'))) {
                $ip_range[] = $access_details[$key];
                $octets = explode('.', $access_details[$key]);
                for ($i = 1; $i <= 4; $i++) {
                    array_pop($octets);
                    $ip_range[] = implode('.', $octets) . '.*';
                }

                foreach ($ip_range as $try) {
                    if (in_array($try, $instance[$key])) {
                        $match_found = true;
                        break;
                    }
                }
            } elseif ($key == 'domain') {
                $host = preg_replace('/[^-a-zA-Z0-9.]*/', '', $access_details[$key]);

                foreach ($instance[$key] as $host_check) {
                    if (strpos($host_check, '*') !== false) {
                        $host_check = str_replace(array('.','*'), array ('\.', '[-a-zA-Z0-9.]+'), $host_check);

                        if (preg_match('/^' . $host_check . '$/', $host)) {
                            $match_found = true;
                            break;
                        }
                    }
                }
            }

            if (!in_array($access_details[$key], (array)$instance[$key]) && is_array($match_found)) {
                $subdomain = $servergood = false;
                if ($key == 'domain' && (strlen(trim($access_details[$key])) == 0 || $access_details[$key] == 'Unknown')) {
                    //see if this is being called from command line:
                    if (isset($_SERVER['argv'][0]) && ($_SERVER['argv'][0] == 'cron.php' || $_SERVER['argv'][0] == $this->path_translated() . '/cron.php')) {
                        //called from command line, don't fail on host being bad.
                        $command_line = true;
                        $servergood = true;
                    }

                    //Weird anomoly, where the $_SERVER['HTTP_HOST'] is not set.
                    //We tested and verified that this is normally only if weird headers are
                    //sent, or if accessed from the command line.  Either case it should be safe
                    //to display an error and exit.
                    if (!$servergood) {
                        die('Invalid headers.  Response code 242A1-b.'); //give a made up code so it's easy for us to find,
                        //in case this ever gets seen by human eyes.
                    }
                } elseif ($key == 'domain') {
                    $host = $access_details[$key];

                    if (isset($key_data['custom_fields']['ignore_domain']) && $key_data['custom_fields']['ignore_domain']) {
                        //we ignore domain matching problems.
                        $subdomain = true;
                    }

                    if (!$subdomain && !defined('IN_ADMIN')) {
                        //check to see if subdomain is set in region
                        //requires key data to be populated
                        $dummy = $geo->key_data;
                        $geo->key_data = $key_data;
                        $subdomain = $this->_checkSubdomain($addon);
                        //reset back to un-verified key data since it isn't verified yet
                        $geo->key_data = $dummy;
                    }

                    if ($subdomain === 'redirect_false') {
                        //main part of domain matches, but it is a "not-legit" sub-domain
                        //do 301 re-direct
                        $to = $this->db->GetOne("SELECT `value` FROM `geodesic_site_settings` WHERE `setting`='classifieds_url'");
                        $to = ($to) ? $to : $this->db->GetOne("SELECT `classifieds_url` FROM `geodesic_classifieds_configuration`");
                        if ($to) {
                            header('Location: ' . $to);
                        } else {
                            echo "Invalid Domain.";
                        }
                        require GEO_BASE_DIR . 'app_bottom.php';
                        exit;
                    }
                }
                if (!$subdomain && !$servergood) {
                    //The geo nav addon sub-domain checks failed, so this is def.
                    //not a valid domain (or other check failed)
                    $is_valid_for_location = false;
                    $fail_logs[] = "$key did not match. Detected {$access_details[$key]} instead of " . implode(' or ', $instance[$key]);
                    break;
                }
            }
        }

        // Is the local key valid for this location?
        if (!$is_valid_for_location) {
            if (defined('IN_ADMIN') && !$recurse) {
                //when in admin panel, allow it to attempt to validate remotely
                $local_key = $this->fetch_new_local_key($addon);
                if ($local_key) {
                    $geo->save_local_key = true;
                    return $this->validate_local_key($local_key, $forceRemote, $addon, true);
                }
                return $geo->errors = 'Error: The local key is invalid for this location, and the remote validation failed.<br /><strong>Location Failure Reason(s):</strong><br />-' . implode('<br /> -', $fail_logs);
            }

            return $geo->errors = 'Error: The local license key is invalid for this location.';
        }

        //go ahead and save key data locally, since it all checks out
        $geo->key_data = $key_data;

        if ($geo->classType == 'geoPC' && !$geo->errors && isset($key_data['custom_fields']['only']) && $key_data['custom_fields']['only']) {
            //this license has "only" set... see if it has an upgrade to remove that...
            if (self::_checkMaxUpgrade()) {
                unset($key_data['custom_fields']['only']);
                $geo->key_data = $key_data;
            }
        }

        if (!$geo->errors && ($geo->save_local_key || $forceRemote) && $raw_local_key) {
            if ($forceRemote) {
                //clear local key before saving
                $this->clear_cache_local_key(true, $addon);
            }
            // Write the new local key.

            switch ($this->local_key_storage) {
                case 'database':
                    $this->db_write_local_key($raw_local_key, $addon);
                    break;

                case 'filesystem':
                    $path = "{$this->local_key_path}{$this->local_key_name}";
                    $this->write_local_key($raw_local_key, $path);
                    break;
            }
        }
    }

    /**
     * Internal method, it looks at the license key entered and sees if perhaps they
     * did not enter the correct key
     *
     * @return string
     * @internal
     */
    private function _tamperedCoreLicenseError()
    {
        $generic = 'Error: The local license key has been tampered with or is otherwise invalid. (spbas: 0)';
        $hint = 'Enter your GeoCore license key, it will start with "GeoCore-".';
        if (!$this->license_key) {
            return 'Error: Invalid license key or no key entered.';
        }
        if ($this->_keyHasValidPrefix($this->license_key)) {
            //this is a standard geocore license key, show normal message
            return $generic;
        }
        if (strpos($this->license_key, 'Geo-IPhoneApp') !== false) {
            //iphone license key
            return 'Error: This key is only valid for the mobile API addon, not the main GeoCore product. ' . $hint;
        }
        if (strpos($this->license_key, 'ClassAuctions')) {
            //classauctions license
            return 'Error: This key is for the older GeoClassAuctions Enterprise product, is it not valid for Geocore. ' . $hint;
        }
        if (strpos($this->license_key, 'Classifieds')) {
            //classifieds license
            return 'Error: This key is for the older Geo Classifieds product, is it not valid for Geocore. ' . $hint;
        }
        if (strpos($this->license_key, 'Auctions')) {
            //auctions license
            return 'Error: This key is for the older Geo Auctions product, is it not valid for Geocore. ' . $hint;
        }

        //key didn't match any of the ones we know what looks like
        return $generic;
    }

    /**
     * Read in a new local key from the database.
     *
     * @param bool $forceRemote
     * @param string $addon
     * @return string
     * @internal
     */
    public function db_read_local_key($forceRemote = false, $addon = '')
    {
        $geo = ($addon) ? $this->add[$addon] : $this->geo;
        if ($addon && !$forceRemote) {
            $reg = geoAddon::getRegistry($addon);
            $local_key = $reg->get($geo->prefix . 'license_data', false);
        } elseif (!$forceRemote && $geo->classType == 'geoPC') {
            $local_key = $this->db->get_site_setting('license_data', true);
        } else {
            $local_key = false;
        }

        // is the local key empty?
        if (!$local_key || strpos($local_key, '<') !== false) {
            // Yes, fetch a new local key, the one we have is blank or has < in there, which
            //is never a good sign.
            $local_key = $this->fetch_new_local_key($addon);

            // did fetching the new key go ok?
            if ($geo->errors) {
                return $geo->errors;
            }

            //don't actually save it until it has been validated!
            if ($local_key) {
                $geo->save_local_key = true;
            }
        }

         // return the local key
        return $local_key;
    }

    /**
     * Write the local key to the database.
     *
     * @param string $local_key The local key to write
     * @param string $addon
     * @return string|boolean string on error; boolean true on success
     * @internal
     */
    public function db_write_local_key($local_key, $addon = '')
    {
        $geo = ($addon) ? $this->add[$addon] : $this->geo;
        if ($geo->classType == 'geoPC') {
            $doSave = true;
            if ($this->is_leased($addon)) {
                //make sure they don't have any unpaid invoices
                $api_handler = $this->api_server;
                // enter your API Key from step 1
                $api_key = 'a0577ab162abeb0384dfa0618b28e00f';

                // the module & task name
                $mod = 'geodesic';
                $task = 'overdue_invoice_check';
                $data               = array();

                $data['license_key'] = ($addon) ? $geo->license_key : $this->license_key;

                $result = geoPCAPI::query($api_handler, $api_key, $mod, $task, $data);

                if (isset($result['status']) && $result['status'] == 'due') {
                    $doSave = false;
                    $geo->overdue_invoice = $result;
                } else {
                    trigger_error("DEBUG LICENSE: LOCAL: No invoices due were found for license,
						so saving local license data.");
                }
            }
            if ($doSave) {
                if ($addon) {
                    $geo->reg->set($geo->prefix . 'license_data', $local_key);
                    $geo->reg->save();
                } else {
                    $this->db->set_site_setting('license_data', $local_key, true);
                }
            } else {
                trigger_error("DEBUG LICENSE: LOCAL:  Local key data not saved, as this is a leased
					license and there is a lease payment due.{$addon}");
            }
        }

        return true;
    }

    /**
     * Gets data about overdue leased license invoice, or null if not
     * valid for this license, or not overdue.
     *
     * @return array
     * @since Version 6.0.0
     */
    public static function getOverdueInvoice()
    {
        //print_r(self::getInstance()->geo->overdue_invoice);
        return self::getInstance()->geo->overdue_invoice;
    }

    /**
     * Read in the local license key using local file.  NOT IMPLEMENTED FULLY
     *
     * @param bool $forceRemote
     * @return string
     * @internal
     */
    public function read_local_key($forceRemote = false)
    {
        $geo = $this->geo;
        if (!file_exists($path = "{$this->local_key_path}{$this->local_key_name}")) {
            return $this -> errors = "Error: Please create the following file (and directories if they don't exist already):<br />\r\n<br />\r\n{$path}" ;
        }

        if (!is_writable($path)) {
            return $this -> errors = "Error: Please make the following path writable:<br />{$path}";
        }

        // is the local key empty?
        if ($forceRemote || !$local_key = file_get_contents($path)) {
            // Yes, fetch a new local key.
            $local_key = $this->fetch_new_local_key();

            // did fetching the new key go ok?
            if ($geo->errors) {
                return $geo->errors;
            }

            // Write the new local key.
            if ($local_key) {
                $geo->save_local_key = true;
            }
        }

         // return the local key
        return $local_key;
    }

    /**
     * Clear the local key file cache by passing in ?clear_local_key_cache=y
     *
     * @param boolean $clear
     * @param string $addon
     * @return string on error
     */
    public function clear_cache_local_key($clear = false, $addon = '')
    {
        if (isset($_GET['clear_local_key_cache']) || isset($_POST['clear_local_key_cache']) || $clear) {
            $result = self::clearLicenseKey(true, $addon);

            if (!$addon && isset($_GET['clear_local_key_cache']) && defined('IN_ADMIN')) {
                trigger_error('DEBUG LICENSE: LOCAL: Local license data is being cleared manually by the admin user.');

                header('Location: index.php?page=home');
                //note:  cannot exit at this time, or license thingy won't get logged,
                //just let the rest of the page load up
            }

            return $result;
        }
    }

    /**
     * Write the local key to a file for caching.
     *
     * @param string $local_key
     * @param string $path
     * @return string|boolean string on error; boolean true on success
     * @internal
     */
    public function write_local_key($local_key, $path)
    {
        $fp = @fopen($path, 'w');
        if (!$fp) {
            return $this -> errors = "Error: I could not save the local license key.";
        }
        @fwrite($fp, $local_key);
        @fclose($fp);

        return true;
    }

    /**
     * Query the API for a new local key
     *
     * @param string $addon
     * @return string|false string local key on success; boolean false on failure.
     * @internal
     */
    public function fetch_new_local_key($addon = '')
    {
        $geo = ($addon) ? $this->add[$addon] : $this->geo;

        if ($geo->raw_remote_new_key) {
            //we already retrieved!  Stop it from getting license data
            //multiple times in same page load.

            return $geo->raw_remote_new_key;
        }

        // build a querystring
        $license_key = ($addon) ? $geo->license_key : $this->license_key;
        $querystring = "mod=license&task=SPBAS_validate_license&license_key={$license_key}&";
        if (isset($_POST['agreed']) && $_POST['agreed']) {
            $querystring .= "agreed=1&";
        }
        $querystring .= $this->build_querystring($this->access_details());

        // was there an error building the access details?
        if ($geo->errors) {
            return false;
        }

        $priority = $this->local_key_transport_order;
        while (strlen($priority)) {
            $use = substr($priority, 0, 1);

            // try fsockopen()
            if ($use == 's') {
                if ($result = $this->use_fsockopen($this->api_server, $querystring)) {
                    break;
                }
            }

            // try curl()
            if ($use == 'c') {
                if ($result = $this->use_curl($this->api_server, $querystring)) {
                    break;
                }
            }

            // try fopen()
            if ($use == 'f') {
                if ($result = $this->use_fopen($this->api_server, $querystring)) {
                    break;
                }
            }

            $priority = substr($priority, 1);
        }

        if (!$result) {
            $geo->errors = 'Error: I could not obtain a new local license key.';
            trigger_error("ERROR LICENSE: REMOTE: Failed to retrieve license data from remote license server. Connection could not be established, or there was no response from the license server.");
            return false;
        }

        if (substr($result, 0, 7) == 'Invalid') {
            $geo->errors = str_replace('Invalid', 'Error', $result);
            trigger_error("ERROR LICENSE: REMOTE: Invalid license key, response message from server: $result");
            return false;
        }

        if (substr($result, 0, 5) == 'Error') {
            $geo->errors = $result;
            trigger_error("ERROR LICENSE: REMOTE: Error retrieving license key, response from server: $result");
            return false;
        }
        trigger_error("DEBUG LICENSE: REMOTE: Successfully retrieved and validated license data for ({$license_key}) from license server.");
        $geo->raw_remote_new_key = $result;
        return $result;
    }

    /**
     * Convert an array to querystring key/value pairs
     *
     * @param array $array
     * @return string
     */
    public function build_querystring($array)
    {
        $buffer = '';
        foreach ((array)$array as $key => $value) {
            if ($buffer) {
                $buffer .= '&';
            }
            $buffer .= "{$key}={$value}";
        }

        return $buffer;
    }

    /**
     * Build an array of access details
     *
     * @return array
     */
    public function access_details()
    {
        ob_start();
        phpinfo(INFO_GENERAL | INFO_ENVIRONMENT);
        $phpinfo = ob_get_contents();
        ob_end_clean();

        $list = strip_tags($phpinfo);

        $access_details = array();

        // Try phpinfo().
        $access_details['domain'] = $this->scrape_phpinfo($list, 'HTTP_HOST');
        $access_details['ip'] = $this->scrape_phpinfo($list, 'SERVER_ADDR');
        $access_details['directory'] = $this->path_translated();//$this->scrape_phpinfo($list, 'SCRIPT_FILENAME');
        $access_details['server_hostname'] = $this->scrape_phpinfo($list, 'System');
        $access_details['server_ip'] = @gethostbyname($access_details['server_hostname']);

        // Try legacy.
        $access_details['domain'] = ($access_details['domain']) ? $access_details['domain'] : geoPC::cleanHostName($_SERVER['HTTP_HOST']);
        $access_details['ip'] = ($access_details['ip']) ? $access_details['ip'] : $this->server_addr();
        $access_details['directory'] = ($access_details['directory']) ? $access_details['directory'] : $this->path_translated();

        //clean domain name
        $access_details['domain'] = $this->cleanHostName($access_details['domain']);

        // Last resort, send something in...
        foreach ($access_details as $key => $value) {
            $access_details[$key] = ($access_details[$key]) ? $access_details[$key] : 'Unknown';
        }

        // enforce product IDs
        if ($this->valid_for_product_tiers) {
            $access_details['valid_for_product_tiers'] = $this->valid_for_product_tiers;
        }

        return $access_details;
    }

    /**
     * Get the directory path
     *
     * @return string|boolean string on success; boolean on failure
     */
    public function path_translated()
    {
        //use static var so only calculate path once
        static $path = '';

        if (strlen($path) > 0) {
            //echo "path from static: $path<br />";
            return $path;
        }

        $settingNames = array (
            'SCRIPT_FILENAME',
            'ORIG_PATH_TRANSLATED',
            'PATH_TRANSLATED',
            'DOCUMENT_ROOT',
            'APPL_PHYSICAL_PATH'
        );
        //figure out if windows box or not
        $isWin = geoPC::is_windows();

        //see if any of the settings are set, if so use it for path
        foreach ($settingNames as $setting) {
            if (!isset($_SERVER[$setting]) || strlen(trim($_SERVER[$setting])) <= 0) {
                //not using setting, skip it
                continue;
            }
            //echo "using setting: \$_SERVER['$setting']={$_SERVER[$setting]}<br />";
            $path = $_SERVER[$setting];
            if ($isWin) {
                $path = str_replace('\\', '/', $path);
            }
            $path = substr($path, 0, strrpos($path, "/"));
            break;
        }

        //echo 'before: '.$path.'<br>';
        if (!$isWin) {
            //only try realpath on non-iis... too inconsistent to use on IIS servers
            $path_rp = realpath($path);

            if (!($path_rp === false || (strlen($path_rp) == 0 && strlen($path) > 0))) {
                $path = $path_rp;
            } else {
                //oops, realpath didn't work??  Oh well no biggie
            }
        }

        //echo "After: $path<br>\n";

        $remove_part = '';
        if ($this->geo->classType == 'geoUpdateFactory') {
            $remove_part = 'upgrade/';
        } elseif (defined('IN_ADMIN')) {
            $remove_part = ADMIN_LOCAL_DIR;
        }

        if ($remove_part) {
            //remove  part of path (if in admin or upgrade).
            //remove end slash
            $remove_part = substr($remove_part, 0, strrpos($remove_part, '/'));
            if ($path != '/' . $remove_part) {
                //normal way, remove /admin from end of path
                $remove_part = preg_quote($remove_part, '/');
                $new_path = preg_replace('/\/' . $remove_part . '$/', '', $path);
            } else {
                //special case, crazy site "appears" to have software installed
                //at base location "/".
                $new_path = '/';
            }
            //don't need to do realpath on it, we did realpath on original version
            if (strpos($path, $new_path) !== false) {
                //can only have admin in a sub directory of main folder.
                //echo $new_path. ' :: '.$path.'<br>';
                $path = $new_path;
            }
        }
        //echo "path: $path<br />";
        return $path;
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
     * Get access details from phpinfo()
     *
     * @param array $all
     * @param string $target
     * @return string|boolean string on success; boolean on failure
     */
    public function scrape_phpinfo($all, $target)
    {
        $all = explode($target, $all);
        $all = explode("\n", $all[1]);
        $all = trim($all[0]);

        if ($target == 'System') {
            $all = explode(" ", $all);
            $all = trim($all[(strtolower($all[0]) == 'windows' && strtolower($all[1]) == 'nt') ? 2 : 1]);
        }

        if ($target == 'SCRIPT_FILENAME') {
            $slash = (geoPC::is_windows() ? '\\' : '/');

            $all = explode($slash, $all);
            array_pop($all);
            $all = implode($slash, $all);
        } elseif ($target == 'HTTP_HOST') {
            //clean it
            $all = geoPC::cleanHostName($all);
        }

        if (substr($all, 1, 1) == ']') {
            return false;
        }

        if (substr($all, 0, 1) == ':') {
            //fix for info provided in HTTP_ALL having : at front, don't use scraped info
            //for those times
            return false;
        }

        return $all;
    }

    /**
     * Pass the access details in using fsockopen
     *
     * @param string $url
     * @param string $querystring
     * @return string|boolean string on success; boolean on failure
     * @internal
     */
    private function use_fsockopen($url, $querystring)
    {
        if (!function_exists('fsockopen')) {
            return false;
        }

        $url = parse_url($url);

        $fp = @fsockopen($url['host'], $this->remote_port, $errno, $errstr, $this->remote_timeout);
        if (!$fp) {
            return false;
        }

        $header = "POST {$url['path']} HTTP/1.0\r\n";
        $header .= "Host: {$url['host']}\r\n";
        $header .= "Content-type: application/x-www-form-urlencoded\r\n";
        $header .= "User-Agent: SPBAS (http://www.spbas.com)\r\n";
        $header .= "Content-length: " . @strlen($querystring) . "\r\n";
        $header .= "Connection: close\r\n\r\n";
        $header .= $querystring;

        $result = false;
        fputs($fp, $header);
        while (!feof($fp)) {
            $result .= fgets($fp, 1024);
        }
        fclose($fp);

        if (strpos($result, '200') === false) {
            return false;
        }

        $result = explode("\r\n\r\n", $result, 2);

        if (!$result[1]) {
            return false;
        }

        return $result[1];
    }

    /**
     * Pass the access details in using cURL
     *
     * @param string $url
     * @param string $querystring
     * @return string|boolean string on success; boolean on failure
     * @internal
     */
    private function use_curl($url, $querystring)
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $curl = curl_init();

        $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
        $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 300";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Accept-Language: en-us,en;q=0.5";
        $header[] = "Pragma: ";

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'SPBAS (http://www.spbas.com)');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $querystring);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->remote_timeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->remote_timeout); // 60

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ((int)$info['http_code'] != 200) {
            return false;
        }

        return $result;
    }

    /**
     * Pass the access details in using the fopen wrapper file_get_contents()
     *
     * @param string $url
     * @param string $querystring
     * @return string|boolean string on success; boolean on failure
     * @internal
     */
    private function use_fopen($url, $querystring)
    {
        if (!function_exists('file_get_contents')) {
            return false;
        }

        return file_get_contents("{$url}?{$querystring}");
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
     * Checks the subdomain to see if it is valid or not (for geographic nav addon)
     *
     * @param string $addon
     * @return bool
     * @internal
     */
    private function _checkSubdomain($addon = '')
    {
        //now find what region, country, or state matches that subdomain

        //figure out the main part minus subdomain
        $host = geoPC::cleanHostName($_SERVER['HTTP_HOST']);
        //remove www if at start, to allow silly stuff like www.region.example.com
        $host = preg_replace('/^www\./', '', $host);

        //get "main part" of domain name, minus "not attached to license" part of subdomain
        $mainHost = $this->validateMainDomain($host, $addon);

        if (!$host || !$mainHost || $host == $mainHost) {
            //no valid host or main host
            return false;
        }

        //now figure out what subdomain is
        $subdomain = ($host != $mainHost) ? substr($host, 0, strpos($host, $mainHost)) : '';
        $subdomain = trim($subdomain, '.');

        if (!strlen($subdomain)) {
            return false;
        }
        //if it gets ths far, the main part of domain matches, see if sub-domain
        //is "valid".  If it is not valid, return redirect_false to indicate
        //valid main domain but invalid sub-domain

        //make sure Geo Nav Addon is installed & enabled as best we can tell
        if (!file_exists(ADDON_DIR . 'geographic_navigation/info.php')) {
            //no info for geographic navigation
            return 'redirect_false';
        }
        require_once ADDON_DIR . 'geographic_navigation/info.php';

        $db = DataAccess::getInstance();
        //Only if geographic navigation addon is enabled
        $enabled = $db->GetOne("SELECT `enabled` FROM " . geoTables::addon_table . " WHERE `name`='geographic_navigation'");

        if ($enabled !== '1') {
            //not enabled or installed
            return 'redirect_false';
        }

        //only check the enabled regions
        $count = (int)$db->GetOne("SELECT COUNT(*) FROM " . geoTables::region . " WHERE `unique_name`=? AND `enabled`='yes'", array ($subdomain));

        if ($count > 0) {
            //found a region with matching subdomain
            return true;
        }

        return 'redirect_false';
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
        if (strpos($fullDomain, 'geodesicsolutions.com') !== false) {
            //simple hack to get demo to recognize domain correctly
            return 'geodesicsolutions.com';
        }
        $geo = ($addon) ? $this->add[$addon] : $this->geo;
        if (!$geo->key_data) {
            //local data not able to be validated, and this only works on local data
            return '';
        }

        $host_array = $geo->key_data['host_array'];
        $host = geoPC::cleanHostName($fullDomain);
        if (!$host) {
            //not valid host, nothing more to do
            return '';
        }

        $allParts = explode('.', $host);

        while (is_array($allParts) && count($allParts)) {
            $domain = implode('.', $allParts);
            if (in_array(md5($geo->secret_key . $domain), $host_array)) {
                //this one matches!  return it
                return $domain;
            }
            //take off one of the parts at the beginning (one of the sub-domains)
            array_shift($allParts);
        }
        //if get here, not any part of it matched
        return '';
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
     * Whether this is demo trial or not
     * @return boolean
     * @internal
     */
    private function _isDemoTrial()
    {
        //DO NOT TOUCH THIS NEXT LINE!  It is replaced by a script for building demo trials...
        $demoTrial = false;
        return $demoTrial;
    }

    /**
     * Detects the license product type, if license validation fails and on
     * client side, it calles {@link geoPC::show_site_off()} and
     * halts execution.
     *
     * @param string $license a license key to try to validate, only needed
     *  if the license key is not already validated.
     * @internal
     */
    public function discover_type($license = false)
    {
        //This method only used for "built in" license
        $geo = $this->geo;

        //keep from running multiple times in single page load un-necessarily
        if (!$geo->errors && isset($geo->key_data['status']) && $geo->key_data['status'] == 'active') {
            //we already got this info and validated the license!
            return true;
        }

        //index to use for storing the product type in the static array

        if (isset($geo->key_data) && $geo->key_data['status'] == 'active' && $geo->key_data['custom_fields']['product_type'] === 'core') {
            //product already done

            return true;
        }
        //DO NOT TOUCH THIS NEXT LINE!  It is used by a script for building demo trials...
        $demoTrial = $this->_isDemoTrial();

        //To fake being the main demo, change the if statement below to true
        if (false) {
            $demoTrial = true;
        }

        if ($demoTrial) {
            //let the rest of the app know that we have determined the product type.
            define('DISCOVERED', 1);
            define('DEMO_MODE_TEXT', '[TRIAL INSTALL]');
            if ($demoTrial === true) {
                $expires = time() + (60 * 60 * 24 * 90);
            } else {
                $expires = self::urlGetContents('http://geodesicsolutions.com/trial_demo_self_service/expires.php?trial=#TRIAL_FOLDER#');
            }

            $geo->key_data = array (
                'custom_fields' => array(
                    'product_type' => 'core',
                    'seats' => -1,
                ),
                'download_access_expires' => $expires,//DEMO_MODE_TEXT,
                'license_expires' => 'never',//DEMO_MODE_TEXT,
                'support_access_expires' => $expires,
                'local_key_expires' => 'never',//DEMO_MODE_TEXT,
                'status' => 'active',
            );
            if ($demoTrial !== true) {
                //individual trial, mark as trial so it is treated as such elsewhere
                $g = $geo->key_data;
                $g['custom_fields']['trial'] = 1;
                $g['support_access_expires'] = time() - (60 * 60);
                $geo->key_data = $g;
            } else {
                //This is the "main trial"...  do a few things fancy
                $this->_mainDemo();
            }

            return true;
        }

        $saveLicenseKey = false;
        if ($license !== false && strlen(trim($license)) > 10) {
            $this->license_key = '' . $license;
            $saveLicenseKey = true;
            trigger_error('DEBUG LICENSE: LOCAL: Validating new license key entered (' . $license . ')...');
            $this->clear_cache_local_key();
            //starting with fresh slate
            $geo->errors = false;
        }

        if ($this->license_key == false) {
            //need to get the license!
            if (!defined('IN_ADMIN')) {
                //show site_off and exit.
                $this->show_site_off();
                exit;
            }
            $geo->errors = 'No license key entered.';
            return false;
        }
        $this->validate();

        if (!$geo->errors && $geo->key_data['status'] == 'active' && $geo->key_data['custom_fields']['product_type'] === 'core') {
            $key_data = $geo->key_data;

            //set how many seats, -1 for unlimited
            $key_data['custom_fields']['seats'] = (isset($geo->key_data['custom_fields']['seats']) && $geo->key_data['custom_fields']['seats']) ? (int)$geo->key_data['custom_fields']['seats'] : -1;

            $geo->key_data = $key_data;

            define('DISCOVERED', 1);
            if ($saveLicenseKey && $geo->classType == 'geoPC') {
                $this->db->set_site_setting('license', $this->license_key);
            }
            return true;
        } else {
            //not valid

            if (!defined('IN_ADMIN')) {
                //show site_off and exit.
                $this->show_site_off();
                exit;
            }

            return false;
        }
    }

    /**
     * Sets up the top section on the main demo
     * @internal
     */
    private function _mainDemo()
    {
        //define ('DEMO_MODE',true);
        $err = '';

        if (defined('IN_ADMIN')) {
            $image_folder = 'admin_images/';
        } else {
            $image_folder = 'admin/admin_images/';
        }
        /*
         * List of restricted master switches, of "only" one is on the rest are
         * forced to be off.  Also update this in DataAccess::_masters_restricted()
         * and in admin/masters.php.
         */
        $restricted = array('classifieds','auctions');

        /*
         * List of supported products...  MAX being "special case" in that it is
         * the one meaning everything is enabled, the rest will "force on" the
         * same-named master switch
         */
        $valid_products = array('classifieds','auctions','MAX',);

        //see if the force type should change.
        $developer_force_type = 'MAX';

        if (isset($_COOKIE['developer_force_type']) || isset($_GET['developer_force_type']) || isset($_POST['developer_force_type'])) {
            if (isset($_GET['developer_force_type']) || isset($_POST['developer_force_type'])) {
                $type = trim((isset($_POST['developer_force_type'])) ? $_POST['developer_force_type'] : $_GET['developer_force_type']);
                if (in_array($type, $valid_products)) {
                    setcookie('developer_force_type', $type, 0, '/');
                } else {
                    $err .= 'Invalid Product Selection!';
                }
            } else {
                $type = trim($_COOKIE['developer_force_type']);
                if (!in_array($type, $valid_products)) {
                    $err .= 'Invalid Product Cookie!';
                }
            }
            if (in_array($type, $valid_products)) {
                //type is valid, so set the force type to it.
                $developer_force_type = $type;
            }
        }
        $geo = $this->geo;
        $g = $geo->key_data;

        //set it as main demo
        $g['custom_fields']['main_demo'] = 1;

        if ($developer_force_type != 'MAX' && in_array($developer_force_type, $valid_products)) {
            //set it as "only" that

            $g['custom_fields']['only'] = $developer_force_type;
        }
        $geo->key_data = $g;

        $master = geoMaster::getInstance();
        $only = ($developer_force_type == 'MAX') ? '' : $developer_force_type;

        foreach ($master->getAll() as $name => $value) {
            if ($only && in_array($name, $restricted)) {
                continue;
            }
            $switchName = 'master_' . $name;
            if (isset($_COOKIE[$switchName]) || isset($_GET[$switchName]) || isset($_POST[$switchName])) {
                if (isset($_GET[$switchName]) || isset($_POST[$switchName])) {
                    $switch = (isset($_POST[$switchName])) ? $_POST[$switchName] : $_GET[$switchName];
                    $switch = ($switch !== 'on') ? 'off' : 'on'; //make sure this is a valid value. If not, leave it unchanged
                    setcookie($switchName, $switch, 0, '/');
                } else {
                    $switch = $_COOKIE[$switchName];
                    $switch = ($switch !== 'on') ? 'off' : 'on'; //make sure this is a valid value. If not, leave it unchanged
                }
                //now set this switch to the selected state, but only for this pageload!
                $master->set($name, $switch, false);
            }
        }
        if (!geoMaster::is('classifieds') && !geoMaster::is('auctions')) {
            //now make sure the user hasn't turned off both classifieds and auctions, as that's just silly!
            //if they have...turn both back on!
            $master->set('classifieds', 'on', false);
            setcookie('classifieds', 'on', 0, '/');
            $master->set('auctions', 'on', false);
            setcookie('auctions', 'on', 0, '/');
        }
        unset($master);

        //see if CSS is set
        if (!defined('IN_ADMIN')) {
            //generate list of primary/secondary t-sets
            $primary_tset = $default_primary_tset = 'blue_primary';
            $secondary_tset = $default_secondary_tset = 'green_secondary';

            $ignore = array ('.','..','_temp','default','my_templates','t_sets.php');
            $valid_tsets = array_diff(scandir(GEO_TEMPLATE_DIR), $ignore);

            $valid_primary = $valid_secondary = array();
            foreach ($valid_tsets as $tset) {
                $title = str_replace(array('_primary','_secondary','_'), array('','',' '), $tset);
                $title = ucwords($title);
                if (strpos($tset, '_primary') !== false) {
                    if ($tset == $default_primary_tset) {
                        $title .= ' (Default)';
                    }
                    $valid_primary[$tset] = $title;
                } elseif (strpos($tset, '_secondary') !== false) {
                    if ($tset == $default_secondary_tset) {
                        $title .= ' (Default)';
                    }
                    $valid_secondary[$tset] = $title;
                }
            }
            unset($tset, $valid_tsets);

            //figure out what primary tset to use
            if (isset($_COOKIE['css_primary_tset']) || isset($_GET['css_primary_tset']) || isset($_POST['css_primary_tset'])) {
                $tset = '';
                if (isset($_GET['css_primary_tset']) || isset($_POST['css_primary_tset'])) {
                    $tset = trim((isset($_POST['css_primary_tset'])) ? $_POST['css_primary_tset'] : $_GET['css_primary_tset']);
                    if (isset($valid_primary[$tset])) {
                        setcookie('css_primary_tset', $tset, 0, '/');
                        $_COOKIE['css_primary_tset'] = $tset;
                    } else {
                        $err .= 'Invalid Primary Theme Selection!';
                    }
                } else {
                    $tset = trim($_COOKIE['css_primary_tset']);
                    if (!isset($valid_primary[$tset])) {
                        $err .= 'Invalid Primary Theme Selection!';
                        unset($_COOKIE['css_primary_tset']);
                    }
                }
                if (isset($valid_primary[$tset])) {
                    //type is valid, so set the force type to it.
                    $primary_tset = $tset;
                    define('THEME_PRIMARY', $primary_tset);
                }
            }
            //figure out what secondary tset to use
            if (isset($_COOKIE['css_secondary_tset']) || isset($_GET['css_secondary_tset']) || isset($_POST['css_secondary_tset'])) {
                $tset = '';
                if (isset($_GET['css_secondary_tset']) || isset($_POST['css_secondary_tset'])) {
                    $tset = trim((isset($_POST['css_secondary_tset'])) ? $_POST['css_secondary_tset'] : $_GET['css_secondary_tset']);
                    if (isset($valid_secondary[$tset])) {
                        setcookie('css_secondary_tset', $tset, 0, '/');
                        $_COOKIE['css_secondary_tset'] = $tset;
                    } else {
                        $err .= 'Invalid Secondary Theme Selection!';
                    }
                } else {
                    $tset = trim($_COOKIE['css_secondary_tset']);
                    if (!isset($valid_secondary[$tset])) {
                        $err .= 'Invalid Secondary Theme Selection!';
                        unset($_COOKIE['css_secondary_tset']);
                    }
                }
                if (isset($valid_secondary[$tset])) {
                    //type is valid, so set the force type to it.
                    $secondary_tset = $tset;
                    define('THEME_SECONDARY', $secondary_tset);
                }
            }
        }

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('err', $err);

        $tpl->assign('ctrl_msg', '');

        $tpl->assign('prod_image', '<img src="' . $image_folder . 'admin_demo_logo_c.gif" style="vertical-align: middle;" width="55" height="25" alt="" />');
        if (!defined('DEMO_MODE')) {
            $tpl->assign('ctrl_msg', 'DEV ');
        }
        $cssFile = (defined('IN_ADMIN')) ? '' : 'admin/';
        //NOTE: If CSS changes, change the v= to higher number so it doesn't try
        //to load cached version
        geoView::getInstance()->addCssFile($cssFile . 'css/demo_box.css?v=703');

        $masters = geoMaster::getInstance()->getAll();
        if ($only) {
            $remove = array_diff($restricted, array($only));
            foreach ($remove as $key) {
                unset($masters[$key]);
            }
        }
        $tpl->assign('masters', $masters);
        $tpl->assign('valid_products', $valid_products);
        $tpl->assign('only', $only);
        $tpl->assign('current_type', $developer_force_type);
        $tpl->assign('in_admin', defined('IN_ADMIN'));
        if (!defined('IN_ADMIN')) {
            $tpl->assign('primary_tsets', $valid_primary);
            $tpl->assign('secondary_tsets', $valid_secondary);
            $tpl->assign('primary', $primary_tset);
            $tpl->assign('secondary', $secondary_tset);
            $tpl->assign('colors', array (
                    'black_primary' => '#000000',
                    'black_secondary' => '#000000',
                    'red_primary' => '#ec0000',
                    'red_secondary' => '#ec0000',
                    'pink_primary' => '#fa297d',
                    'pink_secondary' => '#fa297d',
                    'orange_primary' => '#f9761d',
                    'orange_secondary' => '#f9761d',
                    'yellow_primary' => '#ffd222',
                    'yellow_secondary' => '#ffd222',
                    'green_primary' => '#7ca93a',
                    'green_secondary' => '#7ca93a',
                    'blue_primary' => '#4987c5',
                    'blue_secondary' => '#4987c5',
                    'purple_primary' => '#892ea7',
                    'purple_secondary' => '#892ea7',
                    'brown_primary' => '#b33a00',
                    'brown_secondary' => '#b33a00',
                    'gray_primary' => '#575757',
                    'gray_secondary' => '#575757',
                    'red_lite_primary' => '#ff2b2b',
                    'red_lite_secondary' => '#ff2b2b',
                    'pink_lite_primary' => '#fc69a4',
                    'pink_lite_secondary' => '#fc69a4',
                    'orange_lite_primary' => '#fb8e46',
                    'orange_lite_secondary' => '#fb8e46',
                    'yellow_lite_primary' => '#ffdb48',
                    'yellow_lite_secondary' => '#ffdb48',
                    'green_lite_primary' => '#98c556',
                    'green_lite_secondary' => '#98c556',
                    'blue_lite_primary' => '#1c9cb8',
                    'blue_lite_secondary' => '#1c9cb8',
                    'purple_lite_primary' => '#ab48cc',
                    'purple_lite_secondary' => '#ab48cc',
                    'brown_lite_primary' => '#bf580d',
                    'brown_lite_secondary' => '#bf580d',
            ));
        }

        //Tell the display_page to show the message at the top.
        define('DEVELOPER_MODE', $tpl->fetch('HTML/demo_box.tpl'));
        //for cache, to let cache know to add product type...
        define('CACHE_FILE_PREFIX', $developer_force_type);


        return true;
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
        return geoMaster::is('print');
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
        return geoMaster::is('classifieds');
    }

    /**
     * Returns true if license is a trial license, or false if not.
     *
     * @return bool
     */
    public static function is_trial()
    {
        $pc = self::getInstance();
        return (isset($pc->geo->key_data['custom_fields']['trial']) && $pc->geo->key_data['custom_fields']['trial']);
    }

    /**
     * Returns true if license is the MAIN demo, or false if not.
     *
     * @return bool
     * @since Version 7.0.4
     */
    public static function is_main_demo()
    {
        $pc = self::getInstance();
        return (isset($pc->geo->key_data['custom_fields']['main_demo']) && $pc->geo->key_data['custom_fields']['main_demo']);
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
        $pc = self::getInstance();
        $geo = ($addon) ? $pc->add[$addon] : $pc->geo;
        return (isset($geo->key_data['custom_fields']['leased']) && $geo->key_data['custom_fields']['leased']);
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
        $pc = self::getInstance();
        return (isset($pc->geo->key_data['custom_fields']['only'])) ? $pc->geo->key_data['custom_fields']['only'] : false;
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
        if (defined('IAMDEVELOPER')) {
            //if IAMDEVELOPER is set, show everything
            return false;
        }
        $pc = self::getInstance();
        return (bool)($pc->geo->key_data['custom_fields']['restrictions'] === 'adplotter');
    }

    public static function geoturbo_status()
    {
        $pc = self::getInstance();
        if ($pc->geo->key_data['custom_fields']['restrictions'] === 'adplotter') {
            if ($pc->geo->key_data['custom_fields']['only'] === 'classifieds') {
                //GT Normal
                return 'on';
            } else {
                //GT Plus
                return 'plus';
            }
        } else {
            //not geoturbo
            return false;
        }
    }

    /**
     * Returns true if using a "white label" license that should remove mentions of Geo.
     * Not fully implemented yet, on a license level. To activate, define a PHP constant WHITE_LABEL in config.php
     * @return boolean
     * @internal
     */
    public static function is_whitelabel()
    {
        $pc = self::getInstance();
        return (bool)($pc->geo->key_data['custom_fields']['restrictions'] === 'whitelabel');
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
        if (self::is_leased() || self::is_trial()) {
            //leased, has all of the addons attached
            //or trial, can possibly have any of the addons

            return true;
        }
        $pc = self::getInstance();
        $geo = ($addon) ? $pc->add[$addon] : $pc->geo;


        if (!is_array($addon_product_ids)) {
            $addon_product_ids = array((int)$addon_product_ids);
        }

        if (!$addon_product_ids) {
            //invalid input
            return false;
        }

        $addons = $geo->key_data['addons'];
        if (!$addons) {
            //no addons attached, return false
            return false;
        }
        foreach ($addons as $addon) {
            if (in_array($addon['product_id'], $addon_product_ids)) {
                //found!
                return true;
            }
        }
        return false;
    }
    /**
     * Checks whether thingy is upgraded to max or not
     * @return boolean
     * @internal
     */
    private static function _checkMaxUpgrade()
    {
        //the product ID for the max upgrade
        $max_id = 80;

        $addons = self::getInstance()->geo->key_data['addons'];
        if (!$addons) {
            //no addons attached, return false
            return false;
        }
        foreach ($addons as $addon) {
            if ($addon['product_id'] == $max_id) {
                //found!
                return true;
            }
        }
        return false;
    }
    /**
     * Whether to force powered by or not.
     *
     * @deprecated 7.1
     * @return boolean
     */
    public static function force_powered_by()
    {
        if (!self::is_leased()) {
            //only leased licenses force powered by to be added
            return false;
        }
        $pc = self::getInstance();
        return !(isset($pc->geo->key_data['custom_fields']['remove_branding']) && $pc->geo->key_data['custom_fields']['remove_branding']);
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
        return (int)self::getInstance()->geo->key_data['custom_fields']['seats'];
    }

    /**
     * Returns message to display when something in the admin panel has been restricted
     * due to being in a trial demo.
     *
     * @param string $type
     * @param string $extension
     * @return string
     * @internal
     */
    public static function adminTrialMessage($type = 'action', $extension = '')
    {
        $message = '';
        switch ($type) {
            case 'tpl_security':
                //used when editing template file, to give a notice that Smarty "security" is turned on
                //for trial demos
                $message = '<strong>Trial Demo Notice</strong>:  For the safety of our servers,
				certain security measures are in place on all trial
				demos which disables certain capabilities in templates.
				This will not affect the majority of template changes made,
				but if you notice that something does not seem to work, it may be because
				it is being stopped by the security measures.';
                break;

            case 'invalid_ext':
                //used when they try to do a file with invalid extension
                $message = 'Invalid file extension (.' . trim($extension, '.') . '), due to security concerns only the following extensions are allowed
				when making design changes on trial demos (.tpl .html .js .css .jpg .png .gif .jpeg).';
                break;

            case 'action':
                //break ommited on purpose
            default:
                $message = 'This action has been disabled in trial demos, due to
				the security risks involved from the power of this feature.';
        }

        //Always add the following sentance to any message
        $message .= '<br /><br />Please <a href="http://geodesicsolutions.com/contact-us.html" onclick="window.open(this.href); return false;">contact us</a>
		 if you have any questions.';

        return $message;
    }

    /**
     * Gets the domain name and path, used for license validation.
     *
     * @return array An associative array, following the format array ( 'domain' => $domain, 'path' => $install_path)
     */
    public function get_installation_info()
    {
        $info = array();
        //remove the :80 and similar from end of host
        $host = self::cleanHostName($_SERVER['HTTP_HOST']);
        $info['domain'] = $host;
        $info['path'] = $this->path_translated();
        return $info;
    }

    /**
     * NOT ANYTHING TO DO WITH REDIRECT CHECKING!!!
     *
     * NOT ANYTHING TO DO WITH REDIRECT CHECKING!!!
     *
     * NOT ANYTHING TO DO WITH REDIRECT CHECKING!!!
     *
     * This is a function to be used by DataAccess to make sure that
     * the products.php file is valid.
     * It is just named redirect_check so that it is not obvious
     * what the function is for to outside users who might try to
     * get information about geoPC class.
     *
     * @internal
     */
    public static function redirect_check()
    {
        //NOT ANYTHING TO DO WITH REDIRECT CHECKING!!!
        //NOT ANYTHING TO DO WITH REDIRECT CHECKING!!!
        //NOT ANYTHING TO DO WITH REDIRECT CHECKING!!!
        //This is a function to be used by DataAccess to make sure that
        //the products.php file is valid.
        //It is just named redirect_check so that it is not obvious
        //what the function is for to outside users who might try to
        //get information about geoPC class.

        $num_args = func_num_args();
        if ($num_args !== 2) {
            //expecting 2 args!
            //die('Wrong number of args!  expected 2, got '.$num_args);
            die('File Version Mismatch');
        }
        $secret = func_get_arg(0); //expecting first var to be a secret.
        $encrypt_me = func_get_arg(1); //expecting second var to be string
                                        //that is going to be hashed.
        if ($secret !== sha1('secret-redacted')) {
            //the secret is not correct!
            //die ('Secret does not match!');
            die('File Version Mismatch');
        }
        //secret matches, so return the encrypt_me in super duper secret hashed form.
        $hashed = $secret . base64_encode($encrypt_me) . 'secret-redacted';
        return sha1($hashed);
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
    public function verify_credentials($username, $password, $license = false, $check_email_as_user = true, $checkAdmin = false, $verifyStatus = true)
    {
        //this method currently only used for built in license
        $geo = $this->geo;
        //verify inputs.

        if (!(strlen($username) > 0 && strlen($password) > 0)) {
            //if either the username or password are empty, return false.
            trigger_error('DEBUG SESSION:' . 'verify_credentials(\'' . $username . '\', \'' . $password . '\') = false, username or password is empty.', __line__);
            return false;
        }

        //get the password from the database.
        $sql = 'SELECT * FROM ' . geoTables::logins_table . ' WHERE `username` LIKE ?';
        $result = $this->db->Execute($sql, array($username));
        if (!$result) {
            //database error
            trigger_error('DEBUG SESSION:' . '[ERROR] - verify_credentials() SQL error: SQL= ' . $sql . ' Error=' . $this->db->ErrorMsg(), __line__);
            return false;
        }
        if ($result->RecordCount() == 0 && (strpos($username, '@') !== false) && $check_email_as_user && !defined('IN_ADMIN')) {
            //there are no users by that username.  Try the email.
            $sql = 'SELECT * FROM ' . geoTables::userdata_table . ' WHERE email = ?';
            $userdata_result = $this->db->Execute($sql, array($username));
            if (!$userdata_result) {
                //database error
                return false;
            }
            if ($userdata_result->RecordCount() != 1) {
                //we do not try to verify if multiple users w/ same email are used.
                trigger_error('DEBUG SESSION:' . '[ERROR] - multiple users with same e-mail - invalid login for user: ' . $username . ' pass: ' . $pass . ' username/email: ' . $username, __line__);
                return false;
            }
            $user_data = $userdata_result->FetchRow();
            if (!is_array($user_data)) {
                //something went wrong...
                return false;
            }
            if ($user_data['id'] == 1) {
                //do not allow admin user to log in using e-mail!
                trigger_error('DEBUG SESSION:' . '[ERROR] - verify_credentials() - Admin user is not allowed to log in using e-mail! username=' . $username . ' pass=' . $password);
                return false;
            }
            //now try again, this time using the actual username, and force no e-mail
            // verification, to prevent infinite recursive calls.
            trigger_error('DEBUG SESSION: [NOTICE] - verify_credentials() - appears they entered their e-mail, so re-validating using their username.  Details used: user=' . $user_data['username'] . ' pass=[HIDDEN]');//.$password);
            return $this->verify_credentials($user_data['username'], $password, $license, false);
        }

        if ($result->RecordCount() != 1) {
            //there are more than one user by that username?  do not bother verifying,
            //there is an error.
            trigger_error('DEBUG SESSION:' . '[ERROR] - multiple users with same username - invalid login for user: ' . $username . ' pass: ' . $pass);
            return false;
        }

        $login_data = $result->FetchRow();

        //make sure the username is valid, since we are using like, want to elimitate
        //the use of %
        //allow different case for username.
        if (!(strlen($login_data['username']) == strlen($username) && strlen(stristr($login_data['username'], $username)) == strlen($login_data['username']))) {
            //Seems that username is not matching up.
            trigger_error('DEBUG SESSION:' . '[ERROR] - invalid login, username not found. user: ' . $username . ' pass: ' . $pass);
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
            $adminType = $this->db->GetOne("SELECT `hash_type` FROM " . geoTables::logins_table . " WHERE `username`=?", array($this->getAdminUser($_COOKIE['admin_classified_session'])));
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
                    $adminInfo = $this->db->GetRow("SELECT `password`,`salt` FROM " . geoTables::logins_table . " WHERE `username` = ?", array($userCheck));
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

                    //make sure license is ok.
                    //nope, we do it differently now... Only do something
                    //with the license if a license key is passed in.

                    if ($license && $geo->errors) {
                        //license is currently not set, the user is an admin user, and the license
                        //appears to be valid, so set the license.
                        $this->discover_type($license);
                    }


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
                            trigger_error('DEBUG SESSION:[NOTICE] verify_credentials() - user/pass BAD for admin login: username: ' . $username . ' password: ' . $password);
                            return false;
                        }
                    }
                    //if not admin, discover type must be ok.
                    if ($geo->key_data['status'] != 'active') {
                        //Can not verify any credentials if product type is bad.
                        return false;
                    }
                    //valid login, now just check the status.
                    if (!$verifyStatus || $login_data['status'] == 1) {
                        //either NOT verifying status, or we are and the status is good...
                        //means login passed all the other checks!
                        unset($login_data['password'], $login_data['salt']);
                        return $login_data;
                    } else {
                        trigger_error('DEBUG SESSION:[NOTICE] verify_credentials() - user/pass verified successfully but STATUS failed, so returning false: username: ' . $username . ' password: ******');
                        return false;
                    }
                }
                trigger_error("DEBUG SESSION:[NOTICE] hashed passwords or salts don't match.");
            }
        }
        //Just went through each different hash type, and none of them matched up,
        //so the password must not be valid.
        trigger_error('DEBUG SESSION:' . '[NOTICE] verify_credentials() - user/pass FAILED - user & pass do not match: username: ' . $username . ' password: ' . $password);
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
        $session = $db->GetRow("SELECT `user_id` FROM " . geoTables::session_table . " WHERE `classified_session`=? AND `admin_session`='Yes'", array($sessionId));

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
     * @param unknown_type $key
     * @param unknown_type $iv_len
     * @return unknown
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
     *   'saltLength'=> the fixed length for how long salt values are, 0 if not use salt, or -1 for variable length salt,
     *   'name' => the name, used to set this hash type as default hash used in admin. If blank, this will not show as option in admin.
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
        $sql = 'UPDATE ' . $this->db->geoTables->logins_table . ' SET password = ?, salt = ?, hash_type = ? WHERE username = ?';
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
     * Gets the latest version as reported by geodesicsolutions.com
     * @return string|bool Will return the latest version, or bool false if it
     *  could not get the latest version.
     *  @since Version 4.1.0
     */
    public static function getLatestVersion()
    {
        if (isset(self::$_latestVersion)) {
            //already got it once this page load.
            return self::$_latestVersion;
        }
        $versionUrl = 'http://geodesicsolutions.com/support/updates/latest_version.txt';
        if (strpos(self::getVersion(), 'beta') !== false) {
            //this is beta version, get the latest beta version
            $versionUrl = 'http://geodesicsolutions.com/support/updates/latest_version.beta.txt';
        }
        $version = self::urlGetContents($versionUrl);
        if (!$version) {
            //something wrong when getting version
            self::$_latestVersion = false;
            return false;
        }
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
        $geo = ($addon) ? geoPC::getInstance()->add[$addon] : geoPC::getInstance()->geo;
        return $geo->key_data['local_key_expires'];
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
        $geo = ($addon) ? geoPC::getInstance()->add[$addon] : geoPC::getInstance()->geo;
        return $geo->key_data['license_expires'];
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
        $geo = ($addon) ? geoPC::getInstance()->add[$addon] : geoPC::getInstance()->geo;
        return $geo->key_data['support_access_expires'];
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
        $geo = ($addon) ? geoPC::getInstance()->add[$addon] : geoPC::getInstance()->geo;
        return $geo->key_data['download_access_expires'];
    }

    /**
     * Gets the package ID that the license is installed in (used for links in
     * admin panel)
     */
    public static function getPackageId()
    {
        $geo = ($addon) ? geoPC::getInstance()->add[$addon] : geoPC::getInstance()->geo;

        return $geo->key_data['package_id'];
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
        if (!defined('IN_ADMIN')) {
            return false;
        }

        if ($addon) {
            $geo = $this->add[$addon];
            if (!$onlyClearData) {
                $geo->reg->set($geo->prefix . 'license_key', false);
            }
            $geo->reg->set($geo->prefix . 'license_data', false);
            $geo->reg->save();
        } else {
            $db = DataAccess::getInstance();
            //clear the settings cache
            geoCacheSetting::expire('configuration_data');
            //also clear it from the new site settings table.
            if (!$onlyClearData) {
                $db->set_site_setting('license', false);
            }
            $db->set_site_setting('license_data', false, true);
            $db->set_site_setting('supportCheck', false);
            $db->set_site_setting('lastSupportCheck', false);
            $db->set_site_setting('packageId', false);
        }
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
                $context = stream_context_create(array('http' => array('method' => 'GET', 'header' => implode("\r\n", $additionalHeaders))));
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
        if (defined('GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN') && GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN) {
            //ONLY turn off verifypeer / verify host if set to
            curl_setopt($link, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($link, CURLOPT_SSL_VERIFYHOST, false);
        } elseif (defined('GEO_CURL_CAINFO') && strpos(GEO_CURL_CAINFO, '.pem') !== false) {
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
     * @param array $additionalHeaders an array of additional HTTP headers to send with the request, beyond the normal ones
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
                $fp = fsockopen($parts['host'], 80, $errno, $errstr, timeout);
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
            if (defined('GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN') && GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN) {
                //ONLY turn off verifypeer / verify host if set to
                curl_setopt($link, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($link, CURLOPT_SSL_VERIFYHOST, false);
            } elseif (defined('GEO_CURL_CAINFO') && strpos(GEO_CURL_CAINFO, '.pem') !== false) {
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
    /**
     * Print debug, only works if debuging
     * @internal
     */
    public function printDebug()
    {
        $printDebugOn = false; //set to true to show debug info when function is called

        if (!$printDebugOn) {
            return;
        }

        echo "license fields:  <pre>" . print_r($this->geo->key_data, 1) . "</pre><br />";
    }

    /**
     * Adds a message to be shown when attempting to Enable an addon not attached to the license
     * @internal
     */
    public static function addonNotAttachedText()
    {
        geoAdmin::m("This addon is not attached to your license and cannot be enabled. If you feel this is in error, please contact Geodesic Support.", geoAdmin::ERROR);
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
    private $db, $languageId, $device, $userId, $userName, $sessionId, $ip, $cookie_name, $_pendingChanges;
    /**
     * Internal use
     * @internal
     * @var string
     */
    private $site_configuration_table = 'geodesic_classifieds_configuration';
    /**
     * Internal use
     * @internal
     */
    private static $_seatMinutes = 20;

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
     * Note that Mobile_Detect does not do its own internal caching, so be sure to cache the result and only call it once per pageload
     * Also note that a given device being a "tablet" does not preclude any particular result from isMobile() (that is, either a Desktop or Mobile could also be a Tablet)
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
     * Get the number of admin seats that currently have someone sitting in them,
     * which is determined by any admin session that had any activity less than
     * 20 minutes ago.
     *
     * @return int
     */
    public static function currentAdminSeats()
    {
        if (!defined('IN_ADMIN')) {
            //only run from admin panel, should have no need to use on front side
            return 0;
        }
        //count a seat as someone that has been active in last 20 minutes
        $since = time() - (60 * self::$_seatMinutes);
        return (int)DataAccess::getInstance()->GetOne("SELECT COUNT(*) FROM " . geoTables::session_table . " WHERE `user_id`>0 AND `admin_session`='Yes' AND `last_time`>$since");
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
                -- this is much faster than the old way (which was to delete the sessions, and then delete from other tables where the IDs were missing)
                    because searching by negation gets super slow with lots of records, especially because the keys are hashes and thus index don't help a whole lot...
                -- there's also the added benefit of skipping a couple queries entirely if there are no sessions to remove right now

            A better way to do this would be to create a trigger on the main session table to delete from the others, but that requires MYSQL 5.0.2, which is beyond our current min-reqs

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
            trigger_error('DEBUG SESSION:' . '[ERROR] -- closeSession() - session id stringlen is < 30, so not removing.  $sessionId = ' . $sessionId);
            return false;
        }
        trigger_error('DEBUG SESSION:' . '[NOTICE] -- closeSession() - removing session for $sessionId = ' . $sessionId);
        $sql_query = "DELETE FROM " . geoTables::session_table . " WHERE " . geoTables::field_session_id . " = ? LIMIT 1";
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
            trigger_error('DEBUG SESSION:' . '[ERROR] -- logOut() - session id stringlen is < 32, so not loging out.  $sessionId = ' . $sessionId);
            return false;
        }
        //kill the session
        trigger_error('DEBUG SESSION:' . '[NOTICE] -- logOut() - Logging out session id = ' . $sid . ', username = ' . $this->getUserName());
        $sql = "delete from {$this->db->geoTables->session_table} where {$this->db->geoTables->field_session_id} = ? LIMIT 1";
        $result = $this->db->Execute($sql, array($sid));
        if (!$result) {
            trigger_error('DEBUG SESSION:' . '[ERROR] -- logOut() - SQL Execute Error: SQL=' . $sql . ' Error Reported: ' . $this->db->ErrorMsg());
            return false;
        }

        //delete registry
        geoRegistry::remove('sessions', $sid);
        unset($this->_registry);
        $this->_pendingChanges = false;

        if (!defined('IN_GEO_API')) {
            geoAddon::triggerUpdate('session_logout', array('userid' => $this->getUserId(), 'username' => $this->getUserName()));
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

            //attempt to unset older saved cookies
            //header("Set-Cookie: ".$this->cookie_name."=0; path=/; domain=".$domain."; expires=".gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT",$expires));

            //unset the cookie w/o the .
            $domain = preg_replace('/^\./', '', $domain);
            setcookie($this->cookie_name, false, 0, '/', $domain);
            if ('.' . $domain != $realDomain) {
                $realDomain = preg_replace('/^\./', '', $realDomain);
                setcookie($this->cookie_name, false, 0, '/', $realDomain);
            }
            //unset the cookie with www added
            //setcookie($this->cookie_name, false, 0, '/', 'www.'.$domain);
            //unset with .www added
            //setcookie($this->cookie_name, false, 0, '/', '.www.'.$domain);
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
            $query = "select {$this->db->geoTables->field_session_id} from " . $this->db->geoTables->session_table . " where " . $this->db->geoTables->field_session_id . " = '" . $sid . "'";
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

        $sql_query = "UPDATE " . geoTables::session_table . " SET `last_time` = ? WHERE " . geoTables::field_session_id . " = ? AND `admin_session`='$admin_session'";
        $data = array ($current_time, $sessionId);
        if ($this->db->Execute($sql_query, $data) === false) {
            trigger_error("SQL ERROR: Couldn't update session. " . $this->db->ErrorMsg());
            trigger_error('FLUSH MESSAGES');
            if (defined('IN_ADMIN')) {
                die("Database query error.
				<br /><br />The most common cause is that strict mode may need to be turned on in your config.php file.  To fix, in your <strong>config.php</strong> try setting <strong>\$config_mode = 1;</strong> if it is currently set to 0.");
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
        } elseif (defined('COOKIE_DOMAIN')) {
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
        $sql_query = "INSERT INTO " . geoTables::session_table . " (" . geoTables::field_session_id . ", `user_id`, `last_time`, $ip_field, `admin_session`) values (?,?,?,?,?)";
        $data = array($custom_id, 0, $current_time, $ip, $admin_session);

        if (!$this->db->Execute($sql_query, $data)) {
            trigger_error("ERROR SESSIONS: Couldn't insert session. " . $this->db->ErrorMsg());
            die("We're sorry, our site is experiencing problems. Please come back later."); //DB Query error, don't give
                                                                                            //client any info, in case this is
                                                                                            //a hacking attempt.
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
            /*/header("Set-Cookie: ".$this->cookie_name."=".$custom_id."; path=/; domain=".$domain."; expires=".gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT",$expires));*/
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
        return 'ip_ssl';
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
        //do not run, breaks the security image
        //$product_configuration = geoPC::getInstance();
        //$product_configuration->discover_type(); //make sure discover type is run

        if ($force) {
            //forcing a different session from what may have already happened on this pageload, so clear any stale status
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
        $sql_query = "SELECT " . geoTables::field_session_id . " AS `sid`, `user_id`, `last_time`, `$ip_field` as `ip` FROM " . geoTables::session_table . " WHERE " . geoTables::field_session_id . " = ? AND `admin_session`='$admin_session'";
        $result = $this->db->Execute($sql_query, array($_COOKIE[$this->cookie_name]));

        if ($result === false) {
            trigger_error('ERROR SQL SESSION: Attempt to get session data from the database failed.  Error reported by mysql:' . $this->db->ErrorMsg() . '
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
            trigger_error('DEBUG SESSION:' . '[ERROR] initSession() - Amount of results for session is: ' . $result->RecordCount() . ', when it should be 1. going to create new session.
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
				SET `$ip_field` = ? WHERE " . $this->db->geoTables->field_session_id . " = ? AND `admin_session`='$admin_session' LIMIT 1";
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
                trigger_error('ERROR SESSION:' . '[NOTICE] initSession() : The user\'s session data does not match.  Saved Session Data: ' . $credentials['ip'] . ' -- getUniqueUserInfo() says Session Data: ' . $this->getUniqueUserInfo($sid) . ' -- Session ID: ' . $sid . '
status=change (user-agent changed)');
            }
            if (($current_time - $credentials['last_time']) > $this->_getSessionTimeout($user_id)) {
                trigger_error('DEBUG SESSION:' . '[NOTICE] initSession() : The user\'s session has timed out.  Last visit: ' . ($current_time - $credentials['last_time']) . ' Session Time Out: ' . $this->_getSessionTimeout($user_id) . ' -- Session ID: ' . $sid . '
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
                    echo '<span style="color:red; align:center;">ADMIN LOGIN ERROR:  Session table in the database may need to be updated.  Please contact Geodesic Support.</span><br>';
                }
            }
            //cookie is invalid, so start a new session.
            if (!isset(self::$status)) {
                self::$status = 'changed';
            }
            return ($this->_newSession());
        }

        if (defined('IN_ADMIN') && $user_id > 0) {
            $maxSeats = geoPC::maxSeats();
            $addSeat = ((time() - $credentials['last_time']) > (60 * self::$_seatMinutes)) ? 1 : 0;
            if ($maxSeats >= 0 && (self::currentAdminSeats() + $addSeat) > $maxSeats) {
                //if we let this session continue, will have more people logged in,
                //so log user out and create new session
                $this->logOut();
                return ($this->_newSession());
            }
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
            //and also do some rudimentary browser testing. There's probably lots of room to improve this, but just a quick baseline...
            $ua = getenv('HTTP_USER_AGENT');
            $browser_type = 'Unknown';
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

        //un-comment this to see count of how big each list has gotten (shown in debug messages)
        //trigger_error("DEBUG ROBOT:  Checking against ".count($robots)." \"full\" user agents and ".count($robotP)." \"partial\" user agents...");

        if (in_array($user_agent, $robots)) {
            //this is one of the "full" user agent matches...
            trigger_error("DEBUG ROBOT SESSION: Full robot user agent match!  If this is NOT a bot, let us know the following info, send to sales@geodesicsolutions.com:\nUser agent: \n$user_agent\n");
            define('IS_ROBOT', true);
            return true;
        }

        //now loop through all $robotP which are "partial" strings
        foreach ($robotP as $partial) {
            if (strpos($user_agent, $partial) !== false) {
                //matches part of string so we don't have to include every single
                //variation of a user-agent that a particular robot uses
                trigger_error("DEBUG ROBOT SESSION:  Partial robot user agent match! If this is NOT a bot, let us know the following info, send to sales@geodesicsolutions.com:\nPartial Match:\n$partial\nuser agent:\n$user_agent\n");
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

        trigger_error("DEBUG ROBOT SESSION: No robot user agent match, this is most likely a 'real visitor', or a bot that is not yet on the list!  If you think this is a bot, let us know at sales@geodesicsolutions.com - the User agent:\n$user_agent\n");
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

/** The following is embedded according to instructions for SPBAS */

/**
* Copyright 2009 SolidPHP, Inc. All Rights Reserved.
* Author: Andy Rockwell - support@solidphp.com
* Website: www.spbas.com
*
* RESTful API Handler v1.0 created on 09-18-2009
* @internal
*/
class geoPCAPI
{
    /**
    * Fetch data from the API
    *
    * @param array $request
    * @param boolean $debug
    * @param boolean $clean_response
    * @return string
    */
    function fetch($request, $debug = false, $clean_response = false)
    {
        if (!function_exists('curl_init')) {
            //use fsockopen version
            return self::fetch_fsockopen($request, $debug, $clean_response);
        }
        $api_handler = $request['api_handler'];
        unset($request['api_handler']);
        $request = geoPCAPI::commands_to_string($request);

        $link = curl_init();
        curl_setopt($link, CURLOPT_URL, $api_handler);
        curl_setopt($link, CURLOPT_POSTFIELDS, $request);
        curl_setopt($link, CURLOPT_VERBOSE, 0);
        curl_setopt($link, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($link, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($link, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($link, CURLOPT_MAXREDIRS, 6);
        curl_setopt($link, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($link, CURLOPT_TIMEOUT, 30); // 60
        $results = curl_exec($link);

        if ($debug) {
            curl_close($link);
            echo "<pre>" . htmlspecialchars($results) . "</pre>";
        }

        if ($clean_response) {
            curl_close($link);
            return $results;
        }

        if (curl_errno($link) > 0) {
            curl_close($link);
            return false;
        }
        curl_close($link);

        return $results;
    }

    /**
     * Fetch data from the API
     *
     * @param array $request
     * @param boolean $debug
     * @param boolean $clean_response
     * @return string
     */
    function fetch_fsockopen($request, $debug = false, $clean_response = false)
    {
        $parts = parse_url($request['api_handler']);

        $fp = @fsockopen($parts['host'], 80, $errno, $errstr, 10); // was 5
        if (!$fp) {
            return '';
        }

        $request = geoPCAPI::commands_to_string($request);

        $header = "POST {$parts['path']} HTTP/1.0\r\n";
        $header .= "Host: {$parts['host']}\r\n";
        $header .= "Content-type: application/x-www-form-urlencoded\r\n";
        $header .= "User-Agent: SPBAS (http://www.spbas.com)\r\n";
        $header .= "Content-length: " . @strlen($request) . "\r\n";
        $header .= "Connection: close\r\n\r\n";
        $header .= $request;

        $results = '';
        @fputs($fp, $header);
        while (!@feof($fp)) {
            $results .= @fgets($fp, 1024);
        }
        @fclose($fp);

        $results = explode("\r\n\r\n", $results);

        return $results[1];
    }

    /**
     * Turn an array into a string suitable for fetching data.
     *
     * @param array $commands
     * @return string
     */
    function commands_to_string($commands)
    {
        if (!is_array($commands)) {
            return false;
        }

        $string = '';
        foreach ($commands as $key => $value) {
            if ($string) {
                $string .= '&';
            }

            $string .= "{$key}={$value}";
        }

        return $string;
    }

    /**
     * Base64 decode the data and unserialize it back to an array.
     *
     * @param string $xml
     * @return array
     */
    function unwrap_package($xml)
    {
        $xml = @geoPCAPI::parse_xml_by_eval($xml);
        if ($xml['response']['error_count'] > 0) {
            $result = unserialize(base64_decode($xml['response']['data'])); // geoPCAPI::pr($result);
            return (is_array($result['errors'])) ? implode("\n<br />", $result['errors']) : $result['errors'];
        }

        return unserialize(base64_decode($xml['response']['data']));
    }

    /**
     * Debug helper - prints a formatted array
     *
     * @param array $stack The array to display
     * @param boolean $stop_execution
     * @return string
     */
    function pr($stack, $stop_execution = true)
    {
        $formatted = '<pre>' . var_export((array)$stack, 1) . '</pre>';

        if ($stop_execution) {
            die($formatted);
        }

        return $formatted;
    }

    /**
     * Display the local key in a textarea
     *
     * @param string $local_key
     * @return string
     */
    function view_local_key($local_key)
    {
        echo "<textarea style='width:350px;height:350px;'>{$local_key}</textarea>";
    }

    /**
     * Parse XML using eval() to compile the resulting array
     *
     * @param string XML to parse
     * @return array parsed XML
     */
    function parse_xml_by_eval($xml)
    {
        $parser = @xml_parser_create('');
        @xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        @xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        @xml_parse_into_struct($parser, $xml, $values, $tags);
        @xml_parser_free($parser);

        $hash_stack = array();

        foreach ($values as $key => $val) {
            switch ($val['type']) {
                case "open":
                    array_push($hash_stack, $val['tag']);
                    break;

                case "close":
                    array_pop($hash_stack);
                    break;

                case "complete":
                    array_push($hash_stack, $val['tag']);

                # uncomment to see what this function is doing
                # echo("\$ret[" . implode($hash_stack, "][") . "] = '{$val[value]}';\n");

                    eval("\$ret[" . implode($hash_stack, "][") . "]='{$val[value]}';");
                    array_pop($hash_stack);
                    break;
            }
        }

        return $ret;
    }

    /**
     * Issue a command via the API
     *
     * @param string $api_handler
     * @param string $api_key
     * @param string $mod
     * @param string $task
     * @param array $extras
     * @param boolean $no_xml
     * @param boolean $debug
     * @return array
     */
    function query($api_handler, $api_key, $mod, $task, $extras = array(), $no_xml = false, $debug = false)
    {
        $request = array();
        $request['api_handler'] = $api_handler;
        $request['api_key'] = $api_key;
        $request['mod'] = $mod;
        $request['task'] = $task;
        $request = array_merge($request, (array)$extras);

        $xml = geoPCAPI::fetch($request, $debug);

        return ($no_xml) ? $xml : geoPCAPI::unwrap_package($xml); // pr($request);
    }
}

/**
 * Simple internal class, for making certain vars not show with print_r or by
 * other means, to help protect the license.
 *
 * @internal
 */
class _geoInternalSettings
{
    /**
     * Internal use
     *
     * @internal
     */
    private $index;
    /**
     * constructor
     */
    public function __construct()
    {
        //set random $index that has not been used for another "secret" object yet
        do {
            $this->index = sha1(rand());
        } while ($this->_getSetVar(null, null, '__construct'));
    }
    /**
     * Magic method!
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        return $this->_getSetVar($var, null, '__get');
    }
    /**
     * Magic method!
     * @param unknown $var
     * @param unknown $value
     * @return NULL
     */
    public function __set($var, $value)
    {
        return $this->_getSetVar($var, $value, '__set');
    }
    /**
     * Magic method!
     * @param unknown $var
     * @return NULL
     */
    public function __isset($var)
    {
        return $this->_getSetVar($var, null, '__isset');
    }
    /**
     * Magic method!
     * @param unknown $var
     * @return NULL
     */
    public function __unset($var)
    {
        return $this->_getSetVar($var, null, '__unset');
    }
    /**
     * thingy that does all the work
     * @param unknown $var
     * @param unknown $val
     * @param unknown $action
     * @return NULL
     */
    private function _getSetVar($var, $val, $action)
    {
        static $vars = array();


        switch ($action) {
            case '__construct':
                //contruct needs to know whether the index has been used yet
                return isset($vars[$this->index]);
                break;

            case '__get':
                return (isset($vars[$this->index][$var])) ? $vars[$this->index][$var] : null;
                break;

            case '__set':
                $vars[$this->index][$var] = $val;
                break;

            case '__isset':
                return isset($vars[$this->index][$var]);
                break;

            case '__unset':
                unset($vars[$this->index][$var]);
                break;
        }
    }
}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

// WARNING DO NOT EDIT BELOW THIS LINE!!!!!!!!!!!!!!!
//--------------------------------------------------------------
/**
 * Don't use this.  We'll remove it eventually
 * @param unknown $enc_text
 * @param number $iv_len
 * @param unknown $server_name
 * @return mixed
 * @internal
 */
function decrypt($enc_text, $iv_len = 32, $server_name)
{
    //echo $server_name." is server_name<bR>\n";
    $key = $server_name;

    $enc_text = base64_decode($enc_text);
    $n = strlen($enc_text);
    $i = $iv_len;
    $plain_text = '';
    $iv = substr($key ^ substr($enc_text, 0, $iv_len), 0, 512);

    while ($i < $n) {
        $block = substr($enc_text, $i, 16);
        $plain_text .= $block ^ pack('H*', md5($iv));
        $iv = substr($block . $iv, 0, 512) ^ $key;
        $i += 16;
    }

    return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}
/**
 * do nothing, this method is used to temporarily turn off error handling
 * @param unknown $errno
 * @param unknown $errstr
 * @param unknown $errfile
 * @param unknown $errline
 * @param unknown $errcontext
 */
function geo_empty_errorhandle($errno, $errstr, $errfile, $errline, $errcontext)
{
    //do nothing, this method is used to temporarily turn off error handling
}
