<?php

//_samples/session.init_client.php


//Sample API client, that uses the core.session.init api call to create or update
//the session, and returns a session id to use.

/*
Note: This is intended for people that are familiar with editing PHP
    files.

Instructions for using this as a stand-alone API client:
1.  Edit this file:  Look for the line that starts with "$xmlrpc_location".
    Change it to:
    $xmlrpc_location = "XMLRPC.class.php";
2.  Set the rest of the "Required Settings" as needed (like $website, $api_key, etc)
    Each setting has it's own instructions right above it.

    There may be optional settings as well, those can be set by
    un-commenting them and set them as instructed.
3.  Upload the modified file to a location that you can access from the web.
    It does not have to be on the same website as the Geo software.
4.  Upload the file "XMLRPC.class.php" to the same location that you uploaded
    this file to.  The file is located in the Geo software at:
    classes/rpc/XMLRPC.class.php
5.  In a web browser, visit the file you uploaded in step 3.  You should see
    the results of the API call.
*/

//  ----  Required Settings:  ----  //

//location of the XMLRPC.class.php file.  If calling the script from the _samples folder, leave this setting at the default.
$xmlrpc_location = '../../rpc/XMLRPC.class.php';

//url of geo_api.php file, something like "https://mysite.com/geo_api.php"
//Note: recommended to use https for a secure connection, but if your server does not have SSL, you can use http instead.
$website = "https://mysite.com/geo_api.php";

//Site's API key.  You can find the site's API key on the home page in the admin.
$api_key = 'my_site_api_key';

//ip of client attached to this session (currently required, but not validated, may be validated in future version)
$user_ip = $_SERVER["REMOTE_ADDR"];

//user agent of client (currently required, but not validated, may be validated in future version)
$user_agent = $_SERVER['HTTP_USER_AGENT'];

//Whether or not the user is connected on ssl connection:
$use_ssl = 0; //((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'on' && $_SERVER['HTTPS'] != 1) || !isset($_SERVER['HTTPS']))? 0: 1;

//  ----  END Required Settings  ----  //

//  ----  Optional Settings  ----   //

//Un-comment to use a setting

//The session id, as returned by a previous call to core.session.init, use this as a way to validate or update a session id
//If the call returns the same session id, you know it was valid.  If not, you know it expired or was invalid, so update the
//user's session cookie to match what is returned by the call
//$session_id = '6ece1a782f9f7fea2471303c2ad79f4e';

//username - if used, either password or token is also required.  This will make the session
//be attached to the specified user.
//$username = 'user_name';

//password - use this OR token, not both
//$password = 'plain_text_pass';

//token - returned by core.user.getToken
//$token = 'user_token';

//force_session_id - bool - if true, and session_id is set, it will force using
//the session_id specified.  This is meant to allow multple installations on the
//same server share session ID's across installations.  Take care to protect against
//session hijacking.  Var added in version 5.2.4
//$force_session_id = true;

//  ----  END Optional Settings ---- //




if ($api_key == 'my_site_api_key') {
    //settings probably not set!
    die('<strong style="color:red;">ERROR: Settings not set!</strong><br /><br /><em>This is a sample api client script, that requires the settings at the top of this script to be changed to match your site.</em>');
}
require_once($xmlrpc_location);
$client = new IXR_Client($website);
//un-comment next line to turn debug output on for the client
//$client->debug = true;


$data = array();
//api key, required for every api call.
$data['api_key'] = $api_key;
$data['session_id'] = (isset($session_id) && strlen($session_id) == 32) ? $session_id : 0;
if ($use_ssl) {
    //if ssl, specify so by using ip_ssl to store the user's ip
    $data['ip_ssl'] = $user_ip;
} else {
    $data['ip'] = $user_ip;
}
$data['user_agent'] = $user_agent;
if (isset($username)) {
    $data['username'] = $username;
    $data['user_pass'] = (isset($password)) ? $password : 0;
    $data['user_token'] = (isset($token)) ? $token : 0;
}
$data['force_session_id'] = (isset($force_session_id) && $force_session_id && $data['session_id']) ? true : false;

//call the user list api.
if (!$client->query('core.session.init', $data)) {
    die('<span style="color: red;">An error occurred</span> :<br /><strong>' . $client->getErrorCode() . "</strong> : " . $client->getErrorMessage() . '<br /><br /><em>Check the settings at the top of this sample script to make sure they are correct.</em>');
}

//get the response
$details = $client->getResponse();

echo '<strong>Result of API call:</strong><pre>' . print_r($details, 1) . '</pre>';
