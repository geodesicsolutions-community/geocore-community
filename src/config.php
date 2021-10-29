<?php
//config.php
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
## 
##    16.09.0-20-g94994d8
## 
##################################

/*
 Welcome to the Configuration File. This file is the link
 between the php files that run the functions of the software and your
 database which stores all of the information.

We have organized the file in sections. Please follow the instructions below carefully to
ensure a successful installation.
*/

################### Step 1 ########################################
################### MySQL Database Settings #######################
/*
REQUIRED
The information below is required to connect the software to your MySQL
database.
*/

$db_host = "your_database_hostname";//location of sql host - usually localhost
$db_username = "your_database_user";//username used to connect to database
$db_password = "your_database_password";//password used to connect to database
$database = "your_database_name";//name of database

//MySQL "strict mode" - set to 1 if you have MySQL 5.0 or higher, that runs in "strict mode".
// In most cases, this should be on by default.
// Set it to 0 if needed for compatibility with very old versions of MySQL
$strict_mode = 1;

################### Step 2 #########################################
################### Advanced Database Settings #####################
/*
OPTIONAL
Only change the advanced DB settings below if you are sure they need
to be changed, or if you are instructed to do so by Geo Support.
*/

//Uncomment the below line($persistent_connections = 1) if you want to use
//persistent database connections.
//Some hosts do not allow this so only do so if you are sure that you want
//to use this type of connection. To uncomment, remove the #.

#$persistent_connections = 1;



//Database Type. Most will never need to change this.
//defaults to "mysqli," but the older "mysql" should work fine if needed for compatibility on super-old servers
//"pdo_mysql" might work if needed, but is entirely untested/unsupported.
$db_type ='mysqli';

//DB Connection Charset - This charset setting is used when connecting to the database,
//to force the connection charset to be different than the charset setting used at
//the server level.  This is not often needed, so before enabling this setting see
//the documentation from: http://dev.mysql.com/doc/refman/4.1/en/charset-connection.html

// To use, un-comment the line below (remove the #) and change the 'charset_name' to the
// charset needed.
#$force_db_connection_charset = 'charset_name';

################### Step 3 ##########################################
################### Misc. Advanced Settings #########################
/*
=====MOST CONFIGURATIONS DO NOT NEED TO EDIT BELOW SETTINGS======

These advanced settings are broken into 4 sub-sections.  Read the notes on each 
sub-section for information about settings in that sub-section.
*/


/*
  ----CHARSET Settings----
 
 The settings below are used for various operations that are charset sensitive,
 for instance cleaning "user input".  The settings with # in front will need
 to be un-commented (remove the #) to use.  

 For "input cleaning", and anywhere else the PHP function htmlspecialchars()
 would normally be used, there is a 3 step process (below) to ensure that the
 data is not corrupted due to differences in charsets.  Note that step
 1 and 3 are skipped if the appropriate settings are not specified (Most sites
 will only need to set CHARSET_CLEAN, step 2):

 1. (Optional step, only run if CHARSET_FROM is set): The input's charset is
	converted from the CHARSET_FROM setting to the CHARSET_CLEAN setting.  It
	is converted either using mb_convert_string() or iconv(), according 
	to CLEAN_METHOD setting.
    
    See http://www.php.net/mb_convert_encoding for more information on setting
	CLEAN_METHOD to mb_convert_encoding.  CHARSET_FROM is used as the 3rd var passed
	to that function.  If CLEAN_METHOD is not set, and the function exists, 
	mb_convert_encoding is the default method used to convert the charset.
	
	See http://www.php.net/iconv for more information on setting CLEAN_METHOD
	to iconv.  CHARSET_FROM is used as the 1st var passed to that function.
	
	This step, and optionally step 3, are necessary in order to be able to
	clean any charset that is not compatible with the function 
	htmlspecialchars() (see step 2)
    
 2. (Always run): The input is "cleaned" using the PHP function htmlspecialchars()
    This step will use the CHARSET_CLEAN setting for the charset, that charset must
    be compatible with htmlspecialchars().
	
	This step is always run for security reasons, to prevent a certain type of
	hacking called "Cross Site Scripting" or XSS attack.  If the charset is not
	specified, or is not a compatible charset, the default of ISO-8859-1 is used.
    
    See http://www.php.net/htmlspecialchars for a list of compatible charsets you can
    use.
    
 3. (Optional step, only run if CHARSET_TO is set):  The cleaned input's charset 
	is converted from the CHARSET_CLEAN setting to the CHARSET_TO setting. It
	is converted either using mb_convert_string() or iconv(), according 
	to CLEAN_METHOD setting.
	
    See http://www.php.net/mb_convert_encoding for more information on setting
	CLEAN_METHOD to mb_convert_encoding.  CHARSET_TO is used as the 2nd var passed
	to that function, at this step.  If CLEAN_METHOD is not set, and the function exists, 
	mb_convert_encoding is the default method used to convert the charset.
	
	See http://www.php.net/iconv for more information on setting CLEAN_METHOD
	to iconv.  CHARSET_TO is used as the 2nd var passed to that function during
	this step.
*/

define('CHARSET_CLEAN', 'UTF-8');			//Required, see notes above (step 2)

#define('CHARSET_FROM', 'UTF-8'); 				//optional, un-comment and modify 'UTF-8' as needed
												//to use.  See notes above (step 1)

#define('CHARSET_TO','UTF-8');					//optional, un-comment and modify 'UTF-8' as needed
												//to use.  See notes above (step 3)

#define('CLEAN_METHOD', 'mb_convert_string');	//optional, un-comment to use mb_convert_string() 
												//in steps 1 and 3 above, or un-comment and change
												//the 'mb_convert_string' to 'iconv' to use iconv() 
												//instead.  Valid settings are 'mb_convert_string'
												//and 'iconv'.  See notes above (steps 1 and 3)

//
/*
 ----Directory Settings----
 
 Most of these are automatically detected, usually only the first 3 settings
 needs to be modified, and even then only if changing the admin directory
 name to something other than "admin/" or changing the geo_templates folder
 name.  The rest of the settings you will need to un-comment to modify.  Note
 that changing the actual folder name, besides changing those first 3 folders,
 is not fully supported.
*/

//The relative location of your admin directory, relative to GEO_BASE_DIR setting.
//NOTE: It is important that this is properly set, or your license 
// validation will fail.
define ('ADMIN_LOCAL_DIR', 'admin/');

//The relative location of your geo_templates directory, relative to GEO_BASE_DIR setting.
//NOTE: It is important that this is properly set, or the website may not display
//properly.
define ('GEO_TEMPLATE_LOCAL_DIR', 'geo_templates/');

//The relative location of your js library directory, relative to GEO_BASE_DIR setting.
//NOTE: It is important that this is properly set, or things will not work properly
define ('GEO_JS_LIB_LOCAL_DIR', 'js/');

//The rest of these settings are automatically detected and do not normally need to be set
//or changed, and changing them here is not recommended unless directed to do so by support.

//the absolute location of the api directory. (un-comment to change)
#define ('API_DIR','/ABSOLUTE/PATH/TO/API/DIR');

//the absolute location of the addon directory. (This is for 
// the addon directory, not admin side directory)  (un-comment to change)
#define ('ADDON_DIR',"/ABSOLUTE/PATH/TO/ADDON/DIR/");

//the absolute location of your admin directory.  (This is for
// the admin-side directory, not the addon directory) (un-comment to change)
#define ('ADMIN_DIR', '/ABSOLUTE/PATH/TO/ADMIN/DIR/');

//The absolute base folder for the software.  (un-comment to change)
//Note:  This setting has changed from previous versions.
#define ('GEO_BASE_DIR',"/ABSOLUTE/PATH/TO/BASE/DIR/");

//The absolute location of your classes directory. (un-comment to change)
#define("CLASSES_DIR", '/ABSOLUTE/PATH/TO/CLASSES/DIR/');

//The absolute location of your modules directory. (un-comment to change)
#define("MODULES_DIR", '/ABSOLUTE/PATH/TO/MODULES/DIR/');

//The absolute location of the cron tasks directory. (un-comment to change)
#define('CRON_DIR','/ABSOLUTE/PATH/TO/CRON/DIR/');

//The absolute location of the main templates directory (not the template
// set directory, but the one above it that holds all template sets)
#define ('GEO_TEMPLATE_DIR','/ABSOLUTE/PATH/TO/geo_templates/');


//The absolute location of the template "compile" directory - this 
//directory must be writable
#define ('GEO_TEMPLATE_COMPILE_DIR', 'templates_c/');

/*
 ----Cookie Settings----
 
 All cookie settings are automatically detected if not set here. To
 modify, un-comment the setting.
 */
 
//If your server does not properly set the domain name for cookies, 
//un-comment the following line, and replace the domain name with 
//the proper setting.  DO NOT CHANGE unless necessary, or instructed
//by Geodesic Support to do so.
// (un-comment to change)
#define ('COOKIE_DOMAIN','.YourClassifiesSite.com');

/*
 ----Cache Settings----
 The cache system is able to use different methods to store the cache, the 
 settings below are the controls to how they are stored.
*/

// Cache "storage" method: either "filesystem" or "memcache".  If set to
// "memcache", the system must be configured to use memcache, and the memcache
// extension must be installed for PHP.

define ('GEO_CACHE_STORAGE','filesystem');

/* ---- FILESYSTEM CACHE SPECIFIC SETTINGS ---- */

// The absolute location of the cache dir, default 
// is GEO_BASE_DIR/_geocache/ (un-comment to change from default)
#define ('CACHE_DIR','/ABSOLUTE/PATH/TO/CACHE/DIR/');

/* ---- MEMCACHE CACHE SPECIFIC SETTINGS ---- */
// If you have multiple Geo installations, using the same cache, change this
// prefix to be different for each installation.  Otherwise, leave at default.
define ('GEO_MEMCACHE_SETTING_PREFIX','GEO');

/* By default, the cache system will use localhost and the default memcache port
    to connect to.  If you need to use something different, for instance you want
    to spread the load over a pool of servers, you can do something like this
    (See documentation for memcache at http://www.php.net/manual/en/ref.memcache.php)
    Example:

if (!defined('MEMCACHE_ALREADY_INITIALIZED_CUSTOM') && defined('GEO_DIRS_DEFINED')){
	define('MEMCACHE_ALREADY_INITIALIZED_CUSTOM',1);//keep from init multiple times, since config.php may be included multiple times
	$memcache = geoCache::getMemcacheObj();
	if ($memcache){
		$memcache->addServer('serverfarm.server20',12345); //servers in my farm use different port
		$memcache->addServer('serverfarm.server21',12345);
		$memcache->addServer('serverfarm.server22',12345);
		$memcache->addServer('serverfarm.server23',12345);
		$memcache->addServer('serverfarm.server24',12345);
		$memcache->addServer('serverfarm.server25',12345);
		$memcache->addServer('serverfarm.server26',12345);
		$memcache->addServer('serverfarm.server27',12345);
	}
}
//END EXAMPLE
*/

/*
  -- CURL / SSL Server Settings
  
 */

/* 
 * If the server has out-dated CA root certificate bundle, that can sometimes result
 * in CURL operations on SSL connections failing, as the "verify peer" option fails.
 * 
 * The "SSL verify peer" option for CURL is a GOOD THING, as it prevents what is
 * known as "man in the middle" attack.  If you suspect that your server may have an
 * out-dated CA root certificate bundle, you can temporarily "disable" the verify
 * peer by un-commenting the option below.  This will allow you to do further
 * tests, if CURL connections start to work then you know that you need to update your
 * CA certificate bundle in PHP.
 * 
 * If that is the case for your site, the "easiest" way is to contact your host,
 * and tell them that the CURL CA Certificate bundle used by PHP needs to be updated.
 * Or if you run your own server, you can download an updated CA certificate
 * bundle from:
 * 
 * http://curl.haxx.se/docs/caextract.html
 * 
 * Obtain an updated CA certificate bundle from that location, upload to your server,
 * then if you have at least PHP 5.3.7, update your php.ini file to let PHP know
 * the location of the new certificate.  See the documentation for that php.ini
 * setting at:
 * 
 * http://php.net/manual/en/curl.configuration.php
 * 
 * OR if you do not have at least PHP 5.3.7, you can specify the location using
 * the setting further down for GEO_CURL_CAINFO
 *   
 * Note that this should only be enabled on a TEMPORARY / TESTING basis ONLY - if
 * you leave this option enabled on a live production site, your site will be
 * vulnerable to "man in the middle" attacks!
 */
#define('GEO_CURL_SSL_CACERT_VERIFY_PEER_IS_BROKEN',true);

/*
 * If your "default" CA certificate bundle is out of date, and your host is not able
 * to update it for you on the server, you can obtain the latest CA cert from the site:
 * 
 * http://curl.haxx.se/docs/caextract.html
 * 
 * Then upload it to your site.  Then use the option below to specify the absolute
 * location for the CA certificate bundle.
 * 
 * Note that this is only needed if your host is not able to update the "default"
 * certificate used for CURL for some reason.  This is typically needed in WAMP
 * like environments.
 */
#define ('GEO_CURL_CAINFO', '/absolute/path/to/cacert.pem');

/*
 ----Encoder Method Settings----

 The software requires either Ioncube, or Zend Optimizer, to decode certain
 files dealing with the software license.  Normally, the software will detect
 if IonCube can be used, and if it can, use the IonCube encoded files.  If it
 detects that IonCube is not installed or cannot be used, it will instead
 use the Zend Optimizer encoded files automatically.

 If IonCube is detected, but for whatever reason IonCube will not work, you 
 can force it to use Zend Optimizer encoded files automatically using the setting
 below.
*/

//Un-comment the following line to force the use of Zend Optimizer,
//even if IonCube is detected on the server.

#define ('DEFAULT_ENCODE_METHOD','zend');