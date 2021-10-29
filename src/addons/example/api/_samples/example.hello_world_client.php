<?php
//addons/example/api/_samples/example.hello_world.php
/**
 * This is a sample "hello world" stand-alone Remote API client script, that uses
 * the {@link hello_world.php}.
 * 
 * Note: This is intended for people that are familiar with editing PHP files.
 * 
 * Instructions for using this as a stand-alone API client:
 * 1.	Edit this file:  Look for the line that starts with "$xmlrpc_location".
 *  	Change it to:
 *  	$xmlrpc_location = "XMLRPC.class.php";
 * 2.	Set the rest of the "Required Settings" as needed (like $website, $api_key, etc)
 *  	Each setting has it's own instructions right above it.
 *  	There may be optional settings as well, those can be set by
 *  	un-commenting them and set them as instructed.
 * 3.	Upload the modified file to a location that you can access from the web.
 *  	It does not have to be on the same website as the Geo software.
 * 4.	Upload the file "XMLRPC.class.php" to the same location that you uploaded
 *  	this file to.  The file is located in the Geo software at:
 *   	classes/rpc/XMLRPC.class.php
 * 5.	In a web browser, visit the file you uploaded in step 3.  You should see
 * 	the results of the API call.
 * 
 * @author Geodesic Solutions, LLC
 * @package ExampleAddon
 */

/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2013 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    ccda4ac
## 
##################################

// ---- Required settings ---- //

/**
 * Required setting, this is the location of the XMLRPC.class.php file. If
 * calling the script from the
 * addons/example/api/_samples directory, leave this setting at the default.
 * @var string
 */
$xmlrpc_location = '../../../../classes/rpc/XMLRPC.class.php';

/**
 * url of geo_api.php file, something like "https://mysite.com/geo_api.php"
 * Note: recommended to use https for a secure connection, but if your 
 * server does not have SSL, you can use http instead.
 * @var string
 */
$website = "https://mysite.com/geo_api.php";

/**
 * Site's API key.  You can find the site's API key on the home page in the admin.
 * @var string
 */
$api_key = 'my_site_api_key';

//un-comment the line below and set the name to your name, to see a message
// specifically for you.
//$name = "Your Name";

//  ----  END Required Settings  ----  //





if ($api_key == 'my_site_api_key'){
	//settings probably not set!
	die ('<strong style="color:red;">ERROR: Settings not set!</strong><br /><br /><em>This is a stand-alone sample api client script, that requires the settings at the top of this script to be changed to match your site.</em>');
}
require_once $xmlrpc_location;
$client = new IXR_Client( $website );
//un-comment next line to turn debug output on for the client
//$client->debug = true;


$data = array();
//api key, required for every api call.
$data['api_key'] = $api_key;
//the fields to pass, which will be echoed back to us.
if (isset($name) && strlen($name) > 0) {
	//send the name as one of the arguments to the hello world
	//api call.
	$data['name'] = $name;
}

//call the user list api.
if (!$client->query('addon.example.hello_world',$data)){
	die('<span style="color: red;">An error occurred</span> :<br /><strong>'.$client->getErrorCode()."</strong> : ".$client->getErrorMessage().'
	<br /><br />
	<em>Check the settings at the top of this sample script to make sure they are correct.
	<br /><br /><strong>Note that the Example addon needs to be installed and enabled</strong> to be able to make this API call.</em>');
}

//get the response
$details = $client->getResponse();

echo '<strong>Result of API call:</strong><pre>'.print_r($details,1).'</pre>';