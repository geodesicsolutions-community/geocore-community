<?php
//addons/sharing/methods/myspace.php
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

class addon_sharing_method_myspace {
	
	//myspace is no longer a social network or something that can be "shared to"
	
	public $name = 'myspace';
	
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
		return '';
	}
}