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

class z_minifyExternal extends geoGettingStartedCheck
{
	/**
	 * User-readable name/title for this check
	 * @var String
	 */
	public $name = 'Enable CSS/JS Optimizations';
	/**
	 * Name of the section this check belongs in
	 * @var String
	 */
	public $section = 'Final Steps';
	/**
	 * Descriptive text that explains the check and how to resolve it
	 * @var String
	 */
	public $description = 'If your host is capable, enable the <a href="index.php?page=design_settings&mc=design">Combine, Minify, and Compress CSS and JS</a> setting to improve page load times.';
	
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
		return (bool) DataAccess::getInstance()->get_site_setting('minifyEnabled');
	}
	
}