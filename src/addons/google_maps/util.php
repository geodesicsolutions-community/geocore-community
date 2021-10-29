<?php
//addons/google_maps/util.php

/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    17.10.0-23-g40dab80
## 
##################################

# google_maps Addon
require_once ADDON_DIR . 'google_maps/info.php';

class addon_google_maps_util extends addon_google_maps_info
{
	public $coordinates , $location, $locationLong, $listingId, $adminPreview, $adminGoogleResponse, $adminGoogleJSonResponse;
	
	public function initHead ($adminPreview = false)
	{
		if ($adminPreview) {
			$this->adminPreview = true;
		}
		$reg = geoAddon::getRegistry($this->name);
		$db = DataAccess::getInstance();
		if (!$db->get_site_setting('googleApiKey') || (!defined('IN_ADMIN') && $reg->off)) {
			return;
		}
		$view = geoView::getInstance();
		
		//Add script tag by hand so it is not combined... google maps does not work combined, must be
		//referenced directly.
		$url = 'https://maps.googleapis.com/maps/api/js?key='.urlencode($db->get_site_setting('googleApiKey'));
		
		$view->addTop("<script src='{$url}'></script> ");
		
		$pre = (defined('IN_ADMIN'))? '../':'';
		$urls[] = $pre.'addons/google_maps/maps.js';
		
		$view->addJScript($urls);
	}
	
	private function _getCoodinates($params)
	{
		if (isset($this->coordinates)) {
			return $this->coordinates;
		}
		if (defined('IN_ADMIN') && $this->adminPreview) {
			//keep from re-running geocode
			//TODO: FOR NOW make it continue to re-look-up the coords so this can be
			//a test to make sure geocoding works on the server, in future need to
			//change it so that it only tests geocode lookup when that test is
			//specifically requested.  Should probably be done at the time that listings
			//save the lat/long, as without that feature, keeping it from doing a geocode lookup
			//for the rare times it is in the admin is rather mute
			//$this->coordinates = '37.786921,-122.448505';
		}
		
		$location = $this->_getLocation($params);
		
		if (!$location) {
			return;
		}
		if (!geoString::isUtf8($location)) {
			//attempt to convert location to UTF-8 or it won't work with google maps
			$location = utf8_encode($location);
		}
		
		$reg = geoAddon::getRegistry($this->name);
		$db = DataAccess::getInstance();
		
		$googleApiKey = $db->get_site_setting('googleApiKey');
		if (!$googleApiKey || ($reg->off && !defined('IN_ADMIN'))) {
			//api key not set and not in admin, or turned off
			return;
		}
		if (!function_exists('curl_init')) {
			//not able to do anything w/o curl_init
			trigger_error('DEBUG MAP: curl_init does not exist!  Could not get map info.');
			return;
		}
		
		$location = urlencode($location);
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address=$location";
		
		$response = geoPC::urlGetContents($url);
		$this->adminGoogleResponse = $response;

		if (!$response) {
			trigger_error('DEBUG MAP: No response when getting location info.  URL used: '.$url);
			return;
		}
		$info = json_decode($response);
		$this->adminGoogleJSonResponse = $info->status;
		//die ('info: <pre>'.print_r($info,1));
		if (!$info || $info->status !== 'OK') {
			return;
		}
		
		if (!isset($info->results[0]->geometry->location)) {
			//couldn't get the coords
			return;
		}
		
		$points = $info->results[0]->geometry->location;
		
		if (!$points ) {
			return;
		}
		//there's an extra number from the coords so get rid of it
		
		$longitude = $points->lng;
		$latitude = $points->lat;
		
		//sometimes google tries to be fancy and read server locales for number format, but this breaks the javascript!
		if(strpos($longitude,',') !== false) $longitude = str_replace(",",".",$longitude);
		if(strpos($latitude,',') !== false) $latitude = str_replace(",",".",$latitude);
		
		$this->coordinates = $latitude.','.$longitude;
		
		return $this->coordinates;
	}
	
	private function _getLocation($params)
	{
		if (defined('IN_ADMIN') && $this->adminPreview) {
			$this->location = '3333 California St San Francisco CA 94118';
			$this->locationLong = "<strong>Admin Map Preview Listing Title</strong><br />
			3333 California St<br />
			San Francisco CA 94118<br />
			United States of America";
		}
		if (isset($this->location)) {
			//already got it
			return $this->location;
		}
		
		$reg = geoAddon::getRegistry($this->name);
		$db = DataAccess::getInstance();
		
		if (!$db->get_site_setting('googleApiKey')) {
			return;
		}
		
		$listingId = $this->listingId = (int)((isset($params['listing_id']))? $params['listing_id'] : geoView::getInstance()->classified_id);
		if (!$listingId){
			return;
		}
		
		$listing  = geoListing::getListing($listingId);
		if(!$listing) {
			return;
		}
		
		$loc = $listing->mapping_location;
		
		$loc = $this->_quoteFilter($loc);
		$this->location = $loc;
		
		$this->locationLong = "<strong>".$this->_quoteFilter($listing->title)."</strong><br />
			".$this->_quoteFilter($listing->mapping_location);
		
		return $loc; 	
	}
	
	/**
	 * allows use of quotes in a string without opening the whole thing up to HTML injection
	 * @param unknown_type $str
	 * @return unknown_type
	 */
	private function _quoteFilter($str)
	{
		$str = geoString::fromDB($str);
		$str = geoString::specialCharsDecode($str); //undo filtering of quotes
		$str = str_replace('<','&lt;',$str); //but re-do filtering for < to prevent HTML-injection (addresses shouldn't have <, anyway)
		return $str;
	}
	
	/**
	 * Gets the HTML necessary for displaying google map for a listing.
	 * 
	 * @return string
	 */
	public function getMap ($params = array(), $smarty=null)
	{
		$reg = geoAddon::getRegistry($this->name);
		if(!defined('IN_ADMIN') && $reg->off) {
			return false;
		}
		$this->_getCoodinates($params);
		if (!$this->coordinates) {
			//something went wrong when getting coords
			$this->location = $this->locationLong = $this->coordinates = $this->listingId = null;
			return '';
		}
		
		$tpl_vars = array();
		
		$tpl_vars['msgs'] = geoAddon::getText('geo_addons','google_maps');
		$tpl_vars['location'] = ($this->locationLong)? $this->locationLong : $this->location;
		$tpl_vars['listing_id'] = $this->listingId;
		$tpl_vars['coords'] = $this->coordinates;
		$tpl_vars['googleResponse'] = $this->adminGoogleResponse;
		$tpl_vars['jsonResponse'] = $this->adminGoogleJSonResponse;
		
		$this->location = $this->locationLong = $this->coordinates = null;
		
		if ($smarty) {
			return geoTemplate::loadInternalTemplate($params, $smarty, 'map.tpl',
					geoTemplate::ADDON, $this->name, $tpl_vars);
		} else {
			//do it the old way...
			$tpl = new geoTemplate('addon',$this->name);
			$tpl->assign($tpl_vars);
			
			return $tpl->fetch('map.tpl');
		}
	}
}