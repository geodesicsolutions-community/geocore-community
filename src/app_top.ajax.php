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
## ##    16.09.0-42-g0c0c953
## 
##################################

header('Cache-Control: no-cache');
header('Expires: -1');
header('Pragma: no-cache');

define('AJAX', 1);

require_once "app_top.common.php";

//set header for charset, otherwise it won't show up right for weird charsets..
$charset = geoString::getCharsetTo();
if (!$charset){
	//if not using charsetTo, then use the charsetclean setting.
	$charset = geoString::getCharset();
}
//Necessary for weird charsets like arabian, do not change!  (part that is important, setting the charset)
header('Content-Type: text/html; charset='.$charset);

if (isset ($HTTP_SERVER_VARS))
{
	$_SERVER = $HTTP_SERVER_VARS;
}
/*if (!isset($session)){
	$session = true; include GEO_BASE_DIR.'get_common_vars.php';
}
$session->initSession();*/
	
// tableselect filters. generally, copied here from app_top.main, so that they affect module pagination and anything else in the future that uses ajax to get a set of listings.


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
	$language_id = (int)$_COOKIE['language_id'] ? (int)$_COOKIE['language_id'] : $db->getLanguage(true); // NOTE: get this direct from cookie or the db. do NOT instantiate a geoSession
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