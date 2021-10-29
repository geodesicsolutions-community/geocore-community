<?php

//session/init.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.2.1-18-g1cb105e
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

/*
 * Creates, or validates a session and returns the session id.
 */

$session_id = (isset($args['session_id'])) ? $args['session_id'] : 0;

$force_session_id = ($session_id && isset($args['force_session_id']) && $args['force_session_id']);

$ip = (isset($args['ip'])) ? $args['ip'] : 0;

$ssl_ip = (isset($args['ssl_ip'])) ? $args['ssl_ip'] : 0;
if (!$ssl_ip && isset($args['ip_ssl'])) {
    //make it work with ip_ssl or ssl_ip for forward/backward compatibility...
    //technically ip_ssl is correct, since that is what is used in DB...
    $ssl_ip = $args['ip_ssl'];
}

$user_agent = (isset($args['user_agent'])) ? $args['user_agent'] : 0;

$username = (isset($args['username'])) ? $args['username'] : 0;

$token = (isset($args['user_token'])) ? $args['user_token'] : 0;

$pass = (isset($args['user_pass'])) ? $args['user_pass'] : 0;

if (!(($ip || $ssl_ip) && (strlen($session_id) == 32 || !$session_id) && $user_agent)) {
    return $this->failure('Fields session_id, ip (or) ssl_ip, and user_agent are all required.');
}

//Set server vars for the Session::initSession() to use
//If some server's don't like this, we may need to re-write the initSession
//to pass in the values instead.
$_COOKIE['classified_session'] = $session_id;

$_SERVER['HTTP_USER_AGENT'] = $user_agent;

if ($ssl_ip) {
    $_SERVER['HTTPS'] = 'on';
    $ip = $ssl_ip;
} elseif (isset($_SERVER['HTTPS'])) {
    //the session is not https, but the API connection might be,
    //make sure the session created reflects what is requested, not what
    //is used for the api connection.
    unset($_SERVER['HTTPS']);
}

//IP address not currently used in session security (due to AOL using revolving proxy servers)
//but may be used in future
$_SERVER["REMOTE_ADDR"] = $ip;

$session_id = $this->session->initSession(true, $force_session_id);
if (strlen($session_id) !== 32) {
    return $this->failure('Internal error, session ID returned by system not 32 char length.');
}
if (!$username) {
    //not interested in makeing sure someone is logged in, just wanted to create a session
    return $session_id;
}

if ($pass) {
    //verify password
    if (!$this->product_configuration->verify_credentials($username, $pass)) {
        //user or pass does not match.
        return $this->failure('User/token not valid.', 1000, 5);
    }
} else {
    //verify the token
    if (!$this->checkUserToken($username, $token)) {
        //token is not valid
        return $this->failure('User/token not valid.', 1000, 5);
    }
}
//got this far, the user/pass/token is valid.

//get the user's id from their username.
$sql = "SELECT `id`,`level` FROM " . geoTables::userdata_table . " WHERE `username` = ? AND `id` != 1 LIMIT 1";
$row = $this->db->GetRow($sql, array($username));

if (isset($row['id']) && isset($row['level'])) {
    $sql = "UPDATE " . geoTables::session_table . " SET `user_id`=?, `level`=? WHERE `classified_session`=? LIMIT 1";
    $this->db->Execute($sql, array($row['id'],$row['level'], $session_id));

    return $session_id;
}
//weird, must be they tried to do admin user...
return $this->failure('User/token not valid.', 1000, 5);
