<?php
//app_top.main.php
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
## ##    17.10.0
## 
##################################

require_once "app_top.common.php";

//Anything that needs to be initiallized, started, or whatever at the beginning needs to go in here.

//error_reporting  (E_ERROR | E_WARNING | E_PARSE);
//error_reporting(E_ALL);


trigger_error('DEBUG STATS: Before Product Configuration initialized.');
if (!isset($product_configuration) || !is_object($product_configuration)) {
	$product_configuration = geoPC::getInstance();
}

trigger_error('DEBUG STATS: After Product Configuration initialized.');

$session = geoSession::getInstance();

$session->cleanSessions();
$classified_session = $session->initSession();
$user_id = $session->getUserId();
$db = DataAccess::getInstance();
if ($db->get_site_setting('site_on_off')) {
	//get valid IP's
	if ($session->getUserId() != 1 && !geoUtil::isAllowedIp()) {
		header("Location: ".$db->get_site_setting("disable_site_url"));
		include GEO_BASE_DIR . 'app_bottom.php';
		exit;
	}
}


if (isset($_GET['c']) && is_numeric($_GET['c']) && $_GET['c'] > 0 && $db->get_site_setting('noindex_sorted')) {
	//add noindex meta info if this is a page with sorted results
	geoView::getInstance()->addTop('<meta name="robots" content="noindex" />');
}

//check for if we are in SSL mode right now
$isSsl = geoSession::isSSL();
$checkOldMethod = true; //whether to also look at the old SSL settings (useful for dealing with subdomains)

if(!defined('IN_ADMIN') && $db->get_site_setting('use_ssl_only')) {
	//force ALL pages and links to SSL
	if($isSsl) {
		//loaded this page as SSL, so no need to redirect, but make sure any links created (especially in emails) use the SSL url by overriding it on a per-pageload basis
		$db->set_site_setting('classifieds_url', $db->get_site_setting('classifieds_ssl_url'),false,false);
		$db->set_site_setting('registration_url', $db->get_site_setting('registration_ssl_url'),false,false);
		$checkOldMethod = false;
	} else {
		//this page was loaded unsecured, so redirect to the secure version
		if(stripos($db->get_site_setting('classifieds_ssl_url'), $_SERVER['HTTP_HOST']) !== false) {
			//do NOT redirect if using a subdomain (HTTP_HOST not found in url setting), because the SSL certificate won't be valid
			redirectToSSL(true);
			$checkOldMethod = false;
		}
	}
}
if ($checkOldMethod  && !isset($_GET['no_ssl_force']) && $_SERVER['REQUEST_METHOD'] == 'GET' && $db->get_site_setting('force_ssl_url') && isset($_SERVER['REQUEST_URI'])) {
	//this is the older way to only redirect certain pages into SSL
	
	$sslChecks = (isset($sslChecks))? $sslChecks: array();
	$useSsl = (isset($useSsl))? $useSsl: false;
	//Add SSL Checks...
	
	if (defined('IN_REGISTRATION') && $db->get_site_setting('use_ssl_in_registration')) {
		//we're in registration process, and we're supposed to use SSL for registration...
		$useSsl = true;
	}
	
	if ($db->get_site_setting('use_ssl_in_sell_process')) {
		//if in any part of the cart 
		$sslChecks [] = array ('a' => 'cart');
	}
	
	if ($db->get_site_setting('use_ssl_in_login')) {
		//ssl for user login
		$sslChecks [] = array ('a' => '10');
	}
	
	if ($db->get_site_setting('use_ssl_in_user_manage')) {
		//user management pages
		$sslChecks [] = array ('a' => '4');
	}
	
	//add future checks here...
	
	//get any checks from addons
	$sslChecks = geoAddon::triggerDisplay('filter_ssl_url_checks', $sslChecks, geoAddon::FILTER);
	
	//allow for special case, where the addon returns "true":
	if ($sslChecks === true) $useSsl = true;
	
	//clean up so it doesn't throw errors
	if (!is_array($sslChecks)) $sslChecks = array();
	
	if (count($sslChecks) || $useSsl) {
		//Only do checks if there is at least one SSL url
		
		foreach ($sslChecks as $check) {
			if ($useSsl) {
				//found one that matches all the checks, don't do more of the checks
				break;
			}
			foreach ($check as $key => $value) {
				if (isset($_GET[$key]) && $_GET[$key] == $value) {
					//this check matches, so continue on
					$useSsl = true;
				} else if (!isset($_GET[$key]) && $value === null) {
					//special case, if value is null, then the key doesn't
					//have to be set
					$useSsl = true;
				} else {
					//found a check that did not match up, go on to the next
					//url checks
					$useSsl = false;
					break;
				}
			}
		}
		if ($isSsl !== $useSsl) {
			//need to switch from ssl to non, or visa versa
			redirectToSSL($useSsl);
		}
	}
}

function redirectToSSL($useSsl) {
	//Do NOT preserve sub-domain when going between SSL/non-SSL, as SSL cert
	//will not be valid for sub-domains.
	
	$db = DataAccess::getInstance();
	$setting = ($useSsl)? 'classifieds_ssl_url': 'classifieds_url';
	$url = $db->get_site_setting($setting);
	if ($url) {
		//only do it if set correctly
		$to_url = $_SERVER['REQUEST_URI'];
		$parts = explode('/',dirname($url));
		//I hope they have their url settings set correctly!
	
		//Get rid of the first three parts in a "correctly set" url setting, the "http:", "", and "example.com"
		unset ($parts[0], $parts[1], $parts[2]);
		if (count($parts)) {
			//Geo is installed in a sub-directory, remove the sub-directory from the beginning
			//since it will be added back later down
			$beginning = '/'.implode('/',$parts);
			if (strpos($to_url,$beginning) === 0) {
				$to_url = substr($to_url, strlen($beginning));
			}
		}
		//now figure out the full "before" URL as it was re-written
		$to_url = dirname($url).$to_url;
	
		if ($to_url) {
			header ("Location: $to_url");
			require GEO_BASE_DIR . 'app_bottom.php';
			exit;
		}
	}
}

$session->setLanguage();
$language_id = (int)$session->getLanguage();

if (isset($_GET['forceDevice'])) {
	//call setDevice to allow it to be forced to something different...  We don't
	//do this on every page load as it may not always be needed.
	$session->setDevice();
}

$current_time = geoUtil::time();

/***************************************
 *    ---FILTERS---
 * Init all the different built-in filters
 * Addon Developers: Note that you can do 
 * stuff like this in an app_top.php file 
 * in your addon.
 * ************************************/

//NOTE: filters are things like the state filter, the zip filter, etc.
//where it filters what listings are displayed according to a filter...

//first figure out if we are to use any built-in filters, and set any cookies
//for any freshly set filters.


if ((isset($_POST['set_state_filter']) && $_POST["set_state_filter"]) || (isset($_GET['set_state_filter']) && $_GET['set_state_filter'])) {
	$set_state_filter = (isset($_POST['set_state_filter']))? $_POST['set_state_filter'] : $_GET['set_state_filter'];
	
	if ($set_state_filter != "clear state" && $_POST["clear_zip_filter"] != "clear localizer") {
		//set state filter
		$expires = time() + 31536000;
		setcookie("state_filter",$set_state_filter,$expires,'/');
		$state_filter = $set_state_filter;
	} else {
		//clear state filter
		setcookie("state_filter","",0,'/');
		$state_filter = "";
	}
} else if (isset($_COOKIE["state_filter"]) && $_COOKIE["state_filter"]) {
	$state_filter = $_COOKIE["state_filter"];
} else {
	$state_filter = 0;
}

//****** IF FILTERING WITH A DB TABLESELECT OBJECT, be sure to also put the filter in app_top.ajax.php if applicable ********

if ($state_filter) {
	//set state filter (different than the region and sub region addon)
	//add state to end of sql_query
	$state_filter = intval($state_filter); //this is a numerical region ID
	$overrides = geoRegion::getLevelsForOverrides();
	$stateLevel = $overrides['state'];
	$tbl = geoTables::listing_regions;
	$db->getTableSelect(DataAccess::SELECT_BROWSE)->join($tbl,"$tbl.`listing` = ".geoTables::classifieds_table.".`id`")->where("$tbl.`region` = '$state_filter'", 'state');
}

//language filter
if ($db->get_site_setting('filter_by_language')) {
	//filter: only show listings in user's currently selected language OR listings with no language set
	//(meaning listings that pre-date when language_id is set for listing)
	$part = geoTables::classifieds_table.".`language_id`='{$language_id}' OR ".geoTables::classifieds_table.".`language_id`='0'";
	$db->getTableSelect(DataAccess::SELECT_BROWSE)
		->where($part, 'language');
	
	//Also do it for search results...
	$db->getTableSelect(DataAccess::SELECT_SEARCH)
		->where($part, 'language');
	
	//Not for the feed though..
	
	unset($part);
}

//don't show sold listings
if ($db->get_site_setting('hide_sold')) {
	$part = geoTables::classifieds_table.".`sold_displayed`=0";
	$db->getTableSelect(DataAccess::SELECT_BROWSE)
		->where($part,'sold');
	//also add it for search query
	$db->getTableSelect(DataAccess::SELECT_SEARCH)
		->where($part,'sold');
	//also for listing feeds!
	$db->getTableSelect(DataAccess::SELECT_FEED)
		->where($part,'sold');
	
	unset($part);
}



//Make sure "common" text is available to all pages: (for instance, for the "reserve met" image location,
//which is referenced from tons of different places)
$db->get_text(false, 59); 

//Since most of the front side still uses site class, include it
include_once (CLASSES_DIR . 'site_class.php');