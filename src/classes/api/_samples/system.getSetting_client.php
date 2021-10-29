<?php
//_samples/system.getSetting_client.php
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
## ##    6.0.7-2-gc953682
## 
##################################

//Sample API client, that uses the core.system.getSetting api call to get a
//setting just as if calling $db->get_site_setting($setting) within Geo environment


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

//the setting name to get
$setting = 'setting_name';

//  ----  END Required Settings  ----  //




if ($api_key == 'my_site_api_key'){
	//settings probably not set!
	die ('<strong style="color:red;">ERROR: Settings not set!</strong><br /><br /><em>This is a sample api client script, that requires the settings at the top of this script to be changed to match your site.</em>');
}
require_once ($xmlrpc_location);
$client = new IXR_Client( $website );
//un-comment next line to turn debug output on for the client
//$client->debug = true;


$data = array();
//api key, required for every api call.
$data['api_key'] = $api_key;
$data['setting'] = $setting;

//call the user list api.
if (!$client->query('core.misc.echo',$data)){
	die('<span style="color: red;">An error occurred</span> :<br /><strong>'.$client->getErrorCode()."</strong> : ".$client->getErrorMessage().'<br /><br /><em>Check the settings at the top of this sample script to make sure they are correct.</em>');
}

//get the response
$details = $client->getResponse();

echo '<strong>Result of API call:</strong><pre>'.print_r($details,1).'</pre>';