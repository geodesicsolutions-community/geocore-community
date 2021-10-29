<?php

//session/get.php
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
 * A simple api-call, that gets array of session data based on session ID, IP, and user agent
 */

$session_id = (isset($args['session_id'])) ? $args['session_id'] : 0;

$ip = (isset($args['ip'])) ? $args['ip'] : 0;

$ssl_ip = (isset($args['ssl_ip'])) ? $args['ssl_ip'] : 0;

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

$data = array();
$data['session_id'] = $this->session->initSession(true);
$data['user_id'] = $this->session->getUserId();
$data['session_status'] = $this->session->getStatus();
$data['username'] = $this->session->getUserName();
//return the session data.
return $data;
