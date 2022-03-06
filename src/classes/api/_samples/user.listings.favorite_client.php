<?php

//_samples/user.listings.favorite_client.php


//Sample API client, that uses the core.user.listings.favorite api call to get a
//list of favorite listings for a specific user


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

//User's credentials that you want to get list of active listings for

//username, required
$params['username'] = '';

//user's api token (see the api call core.user.getToken)
$params['token'] = '';
//OR - set the var below to make it "get the token" based on plain-text password
//by making a secondary API call to core.user.getToken
//Should leave this blank if token is used above
$get_token_from_pass = '';


//  ----  END Required Settings  ----  //

//Pagination - if not specified, will return first 50 listings only

//How many listings to return "per page" of listing results.  If not specified,
//will return 50 listings at a time.  You can specify 0 for no limit, but is
//not recommended, sellers with tons of listings may time out when trying to return
//all of their active listings at once.
#$params['limit'] = 20;

//What "page" of results to return, starting with 1 and going up to however many
//pages of results there may be. If not specified, will return "page 1".
#$params['page'] = 2;

//Do you want to "format" the results like they would be formatted in preparation
//to display in a browsing template?  Set this to 1 to do so, default is no formatting
//as it will get better performance.
#$params['format_results'] = 0;

//IF you use option above to format listing results, you can specify custom text
//to use for some of the formatted values, this is in a block quote since it spans several lines.
/*
$params['text'] = array(
    'item_type' => array (
        'classified' => 'classified',
        'auction' => 'auction',
    ),
    'business_type' => array(
        1 => 'ind',
        2 => 'bus',
    ),
    'time_left' => array(
        'weeks' => 'weeks',
        'days' => 'days',
        'hours' => 'hours',
        'minutes' => 'minutes',
        'seconds' => 'seconds',
        'closed' => 'closed',
    )
);
 */

//  ----  END Optional Settings  ----  //




if ($api_key == 'my_site_api_key') {
    //settings probably not set!
    die('<strong style="color:red;">ERROR: Settings not set!</strong><br /><br /><em>This is a sample api client script, that requires the settings at the top of this script to be changed to match your site.</em>');
}
require_once($xmlrpc_location);
$client = new IXR_Client($website);
//un-comment next line to turn debug output on for the client
//$client->debug = true;

if (!trim($params['token']) && trim($get_token_from_pass)) {
    //Get the token based on username/password using the core.user.getToken API
    //call.  If you are storing the user's info somewhere, it is "bad practice" to
    //store the password and re-get the token each time, instead store the retrieved
    //token and use that
    $data = array (
        'username' => $params['username'],
        'password' => $get_token_from_pass,
        'api_key' => $api_key
    );
    if ($client->query('core.user.getToken', $data)) {
        $params['token'] = $client->getResponse();
    }
    unset($data);
}

$data = $params;
//api key, required for every api call.
$data['api_key'] = $api_key;

//call the user list api.
if (!$client->query('core.user.listings.favorite', $data)) {
    die('<span style="color: red;">An error occurred</span> :<br /><strong>' . $client->getErrorCode() . "</strong> : " . $client->getErrorMessage() . '<br /><br /><em>Check the settings at the top of this sample script to make sure they are correct.</em>');
}

//get the response
$details = $client->getResponse();

echo '<strong>Result of API call:</strong><pre>' . print_r($details, 1) . '</pre>';
