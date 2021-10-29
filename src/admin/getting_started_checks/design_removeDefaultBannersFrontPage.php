<?php
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
##    7.5.3-36-gea36ae7
##
##################################

require_once ADMIN_DIR . 'getting_started.php';

class design_removeDefaultBannersFrontPage extends geoGettingStartedCheck
{
	/**
	 * User-readable name/title for this check
	 * @var String
	 */
	public $name = 'Remove or Replace Example Banner Ads: Front Page';
	/**
	 * Name of the section this check belongs in
	 * @var String
	 */
	public $section = 'Design';
	/**
	 * Descriptive text that explains the check and how to resolve it
	 * @var String
	 */
	public $description = 'Remove the "example" ad banners on the front page, or replace them with your own ads';
	
	/**
	 * Value that represents how important this check is towards final completion.
	 * Most will use a value of 1. A check with a weight of 2 should be roughly twice as important as normal.
	 * @var float
	 */
	public $weight = 1;
	
	/**
	 * Accessor for user-selected state of checkbox for this item
	 * @var bool
	 */
	public $isChecked;
	
	/**
	 * Just a constructor.
	 */
	public function __construct()
	{
		$this->isChecked = (bool)DataAccess::getInstance()->get_site_setting('gettingstarted_'.$this->name.'_isChecked');
	}
	
	/**
	 * This function should return a bool based on whether the checked item "appears" to be complete. 
	 * @return bool
	 */
	public function isComplete()
	{	
		$name = 'front_page.tpl';
		$custom = file_get_contents(geoTemplate::getFilePath(geoTemplate::MAIN_PAGE,'',$name));
		return (strpos($custom, '<a href="#"><img src="{external file=\'images/banners/banner2.jpg\'}" alt="" /></a>') === false);
	}
	
}