<?php
//app_top.common.php



//set the error handler
set_error_handler('geo_default_debug_error_handler');

//set up error handling.
function geo_default_debug_error_handler($errno, $errstr, $errfile, $errline, $errcontext){
	static $queue = array();

	$data = array ('errno' => $errno,
		'errstr' => $errstr,
		'errfile' => $errfile,
		'errline' => $errline,
		'errcontext' => $errcontext);

	//un-comment next line for easy way to display errors if addon
	//error handling is not working
	//if (1) echo "$errline $errfile $errstr<br />\n\n"; else
	if (defined('GEO_ADDONS_ENABLED')) {
		if (count($queue)) {
			//errors were previously queued up before
			//addons were fully initialized.  Push them through now
			//that addons are initialized.
			foreach ($queue as $key => $val) {
				geoAddon::triggerUpdate('errorhandle',$val);
			}
			//reset the queue.
			$queue = array();
		}
		geoAddon::triggerUpdate('errorhandle',$data);
	} else {
		//queue it to be reported once all the addons are enabled.
		$queue[] = $data;
	}
	return true;
}

//Auto-load geo classes
function Geo__autoload ($classname)
{
	if (!defined('GEO_DIRS_DEFINED')) {
		return;
	}

	$classname = (strpos($classname,'geo')===0)? substr($classname,3): $classname;
	$filename = CLASSES_DIR . PHP5_DIR . $classname . '.class.php';
	if (file_exists($filename)){
		require_once($filename);
		return;
	}
	$filename = CLASSES_DIR . $classname . '.class.php';
	if (file_exists($filename)){
		require_once($filename);
		return;
	}
	$filename = CLASSES_DIR . strtolower( $classname) .'/'.$classname. '.class.php';
	if (file_exists($filename)){
		require_once($filename);
		return;
	}
}

spl_autoload_register('Geo__autoload');

//Define custom exception handler, that prints "entire" stack trace if needed...
function geo_exception_handler($exception) {
	//Just show the full exception...  Why do we do this?  Because if allowed to
	//fatal error, the stack trace gets cut off.  If echoing, it prints full
	//stack trace which can be very useful.
	echo '<pre>'.$exception;
}

set_exception_handler('geo_exception_handler');

//Fix for stupid sites that have magic_quotes_runtime turned on...  Must turn it off!
if (function_exists('set_magic_quotes_runtime') && get_magic_quotes_runtime()) {
	//must check for function first, since function will be removed from PHP in
	//future, along with ability to turn this stupid setting on.  Hooray!
	set_magic_quotes_runtime(false);
}

if (!defined('PHP5_DIR') && version_compare('5.2.0', phpversion()) < 1) {
	//we are in a php 5 enviroment, use php5 clases where applicable
	//trigger_error('DEBUG STATS: Using php5 classes.');
	define('PHP5_DIR', 'php5_classes/');
} elseif (!defined('PHP5_DIR')) {
	//trigger_error('DEBUG STATS: Using php4 classes.');
	define('PHP5_DIR', '');

	//If you want to allow using PHP 4 version, un-comment the following line.  Note that many systems will be "broken" because there
	//are no PHP 4 versions of those systems.
	die ('<h1 style="color: red">Error:  Minimum Server Requirements not met.</h1>

	<strong>Required: </strong> PHP 5.2.0<br />
	<strong>Your Server: </strong> <span style="color: red">'.phpversion().'</span>');
}

require "config.default.php";

//Make sure that the script doesn't just stop in the middle
//if user hits stop or refreshes or something, set to false to
//turn this feature off.
//Note: Not documented in config.php, but if this needs to be
//changed, just copy/paste line to config.php and change to false.
if (GEO_IGNORE_USER_ABORT) {
	ignore_user_abort(true);
}

//set default time zone
if (GEO_TIMEZONE_SET_GUESS) {
	date_default_timezone_set(date_default_timezone_get());
}

//make sure pages are not cached
//To keep aol proxies from caching pages... to keep things like login, logout,
//and registration from breaking.
//also to stop caching of pages since content is always changing.
if (GEO_CACHE_CONTROL) {
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}

define('GEO_DIRS_DEFINED',1);
//require core functionality files
require_once CLASSES_DIR . 'adodb/adodb.inc.php';
require_once CLASSES_DIR . PHP5_DIR . 'Utility.class.php';
require_once CLASSES_DIR . PHP5_DIR .'products.php';
require_once CLASSES_DIR . PHP5_DIR . 'DataAccess.class.php';
require_once CLASSES_DIR . PHP5_DIR . 'Singleton.class.php';
require_once CLASSES_DIR . PHP5_DIR . 'Addon.class.php';
require_once CLASSES_DIR . PHP5_DIR . 'Cache.class.php';
if (defined('IN_ADMIN')) {
	require_once(ADMIN_DIR.PHP5_DIR.'Notifications.class.php');
}
//Clean inputs, and correct for magic quotes
require GEO_BASE_DIR . 'clean_inputs.php';


//Instantiate the $db and $addon objects.
$db = DataAccess::getInstance();
$addon = geoAddon::getInstance();

if (!defined('IN_UPGRADE')){

	if(geoPC::geoturbo_status()) {
		//do maintenance tasks specific to GeoTurbo
		geoPC::GTMaint();
	}

	//load any app_top's for addons.  Calling this will also make it init enabled addons.
	if (isset($demo_location) || defined('IAMDEVELOPER')) {
		//if in main demo, need to init license before calling app_top on addons
		//to prevent problems with master
		geoPC::license_only();
	}
	$enabled_addons = $addon->getAppTopAddons();
	foreach ($enabled_addons as $addon_name) {
		require_once (ADDON_DIR . $addon_name . '/app_top.php');
	}
	//make sure to take care of banned ips
	if (!defined('IN_ADMIN'))
		$db->checkBannedIp();
}

trigger_error('DEBUG STATS: Start of App (core addon events loaded, db object created)');

/**
 * If experiencing a problem where addons are not being called for error
 * handling, un-comment the following line to "force" a triggered event.  This
 * will force addons to be loaded, and thus allow error handle core events
 * to be called on page loads that never trigger an addon even otherwise.
 */
//geoAddon::triggerUpdate('forceInit');

//If getting a white screen, un-comment the following line to force echoing
//debug messages as soon as they are generated, instead of inserting them
//into the template.  See the show debug messages addon for more info.
//trigger_error('FLUSH MESSAGES');
