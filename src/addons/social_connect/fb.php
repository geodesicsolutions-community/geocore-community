<?php

//addons/social_connect/fb.php

# Facebook Connect class that overloads the main thingy

require_once ADDON_DIR . 'social_connect/lib/facebook/php-sdk/src/base_facebook.php';

/**
 * This next class based on the main Facebook class that is part of the Facebook
 * PHP-SDK.  But instead of using PHP sessions it saves data in a table tied
 * to the geo session table.
 */
class geoFacebook extends BaseFacebook
{
    /**
     * Identical to the parent constructor, except that
     * we start a PHP session to store the user ID and
     * access token if during the course of execution
     * we discover them.  NOT!  Really we use the geoSession
     * registry to save that data...
     *
     * @param Array $config the application configuration.
     * @see BaseFacebook::__construct in facebook.php
     */
    public function __construct($config)
    {
        //make sure session is initialized or get/set won't work.
        geoSession::getInstance()->initSession();
        //let parent do the rest of the work.
        parent::__construct($config);
    }

    protected static $kSupportedKeys =
        array('state', 'code', 'access_token', 'user_id', 'IE_UA');

    /**
     * Provides the implementations of the inherited abstract
     * methods. The implementation uses PHP sessions to maintain
     * a store for authorization codes, user ids, CSRF states, and
     * access tokens.
     */
    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');
            return;
        }

        $session_var_name = $this->constructSessionVariableName($key);
        geoSession::getInstance()->set($session_var_name, $value);
    }

    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');
            return $default;
        }

        $session_var_name = $this->constructSessionVariableName($key);
        return geoSession::getInstance()->get($session_var_name, $default);
    }

    protected function clearPersistentData($key)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');
            return;
        }

        $session_var_name = $this->constructSessionVariableName($key);
        geoSession::getInstance()->set($session_var_name, false);
    }

    public function clearAllPersistentData()
    {
        foreach (self::$kSupportedKeys as $key) {
            $this->clearPersistentData($key);
        }
    }

    protected function constructSessionVariableName($key)
    {
        //This was originally intended for saving in PHP session so it uses following
        //to help prevent namespace collisions...  Since we are using geoSession registry,
        //we'll keep this logic intact to ensure no namespace collisions within the
        //geo session registry, since doing it this way doesn't harm anything.
        return implode('_', array('fb', $this->getAppId(), $key));
    }
}
