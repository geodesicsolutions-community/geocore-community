<?php
//addons/sharing/methods/google_plus.php
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
## ##    7.6.3-59-ge30e65b
## 
##################################




class addon_sharing_method_google_plus {
	
	public $name = 'google_plus';
	
	/**
	 * Gets the name of any methods that want to be used for this listing id.
	 * Note that this function being called in the first place implies that the listing in question is live and belongs to the current user
	 * @param int $listingId
	 * @return String the name of any available method, sans any formatting
	 */
	public function getMethodsForListing($listingId)
	{
		return '';
	}
	
	/**
	 * Gets the full HTML to show in the "options" block of the main addon page.
	 * This function is responsible for any needed templatization to generate that HTML.
	 * @return String HTML
	 */
	public function displayOptions()
	{
		return '';
	}

	public function getShortLink($listingId, $iconOnly=false)
	{
		if(!$iconOnly) {
			//only in the new display (for now?)
			return '';
		}
		$tpl = new geoTemplate('addon','sharing');
		$tpl->assign('iconUrl', geoTemplate::getUrl('images','addon/sharing/icon_google_plus.png')); 
		$msgs = geoAddon::getText('geo_addons','sharing'); 
		//$tpl->assign('text', $msgs['shortlink_twitter']);
		
		$listing = geoListing::getListing($listingId);
		$urlToListing = urlencode($listing->getFullUrl());
		
		$tpl->assign('link', 'https://plus.google.com/share?url='.$urlToListing);
		$tpl->assign('iconOnly',$iconOnly);
		
		return $tpl->fetch('shortLink.tpl');
	}

}