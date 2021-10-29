<?php
//_samples/user.register_client.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.5.3-36-gea36ae7
## 
##################################

//Sample API client, that uses the core.user.edit api call to edit a user's info


/*
Note: This is intended for people that are familiar with editing PHP
	files.

Instructions for using this as a stand-alone API client:
1.	Edit this file:  Look for the line that starts with "$xmlrpc_location".
	Change it to:
	$xmlrpc_location = "XMLRPC.class.php";
2.	Set the rest of the "Required Settings" as needed (like $website, $api_key, etc)
	Each setting has it's own instructions right above it.

	There may be optional settings as well, those can be set by
	un-commenting them and set them as instructed.
3.	Upload the modified file to a location that you can access from the web.
	It does not have to be on the same website as the Geo software.
4.	Upload the file "XMLRPC.class.php" to the same location that you uploaded
	this file to.  The file is located in the Geo software at:
	classes/rpc/XMLRPC.class.php
5.	In a web browser, visit the file you uploaded in step 3.  You should see
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

//User's Info you want to edit, this is required according to your settings set in registration setup:

//new username or original username if username is not changing
$user_info['username'] = '';

$user_info['password'] = '';

//If you want to use registration code, un-comment below and change:
//$user_info['registration_code'] = '';

//OR if you happen to know a user group ID you want to place them in, un-comment below and change: (Var added in 5.1.0)
//$user_info['force_user_group_id'] = 0;

//If password is not available, can un-comment line below to allow registring 
//with blank pass, the user won't be able to login until the password is set
//later using user edit
//$user_info['use_blank_password'] = 1;

$user_info['email'] = '';
$user_info['company_name']='';
$user_info['business_type'] = 1;//1 for personal or 2 for business
$user_info['firstname'] = '';
$user_info['lastname'] = '';
$user_info['address'] = '';
$user_info['address_2'] = '';
$user_info['city'] = '';
$user_info['state'] = '';
$user_info['country'] = '';
$user_info['zip'] = '';
$user_info['phone']='';
$user_info['phone2']='';
$user_info['fax'] = '';
$user_info['url'] = '';
$user_info['affiliate_html'] = '';
$user_info['optional_field_1'] = '';
$user_info['optional_field_2'] = '';
$user_info['optional_field_3'] = '';
$user_info['optional_field_4'] = '';
$user_info['optional_field_5'] = '';
$user_info['optional_field_6'] = '';
$user_info['optional_field_7'] = '';
$user_info['optional_field_8'] = '';
$user_info['optional_field_9'] = '';
$user_info['optional_field_10'] = '';

//make it skip the addon call, you'll need to use this if you
//are making this api call from inside a bridge, to prevent infinite
//circular bridge calls between sites
//$user_info['skip_addon_call'] = 1;

//Uncomment to skip requirement checks on non-essential fields (i.e. NOT username/password/email)
//That data will still be stored if given, but will not throw an error if missing
//$user_info['skip_reqs'] = 1;

//Un-comment the next line to make the "successful registration" return the 
//contents of {body_html} for the normal register success page, in an array
//key 'body_html'.
//$user_info['success_body_html'] = 1;


//Un-comment the next line to make the "successful registration" return the
//normal full page contents for the normal register success page, in an array
//key 'success_full_page'.  Note though that it will not auto-login like it might
//normally do for normal registration.  Note that BOTH of the success_* options
//can be used if you have a reason to need both.
//$user_info['success_full_page'] = 1;


//  ----  END Required Settings  ----  //


if ($api_key == 'my_site_api_key'){
	//settings probably not set!
	die ('<strong style="color:red;">ERROR: Settings not set!</strong><br /><br /><em>This is a sample api client script, that requires the settings at the top of this script to be changed to match your site.</em>');
}
require_once ($xmlrpc_location);
$client = new IXR_Client( $website );
//un-comment next line to turn debug output on for the client
//$client->debug = true;


$data = $user_info;
//api key, required for every api call.
$data['api_key'] = $api_key;

//call the user list api.
if (!$client->query('core.user.register',$data)){
	die('<span style="color: red;">An error occurred</span> :<br /><strong>'.$client->getErrorCode()."</strong> : ".$client->getErrorMessage().'<br /><br /><em>Check the settings at the top of this sample script to make sure they are correct.</em>');
}

//get the response
$details = $client->getResponse();

echo '<strong>Result of API call:</strong><pre>'.htmlspecialchars(print_r($details,1)).'</pre>';