<?php
//CJAX.class.php
/**
 * Holds the geoCJAX class which is a wrapper for the CJAX_FRAMEWORK, a little
 * 3rd party library we started using to make ajaxy type stuff a little easier.
 * 
 * @package System
 * @since Version 4.0.0
 */
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
## ##    7.1.0-23-gd52c4c7
## 
##################################

# This is a wrapper class for the CJAX stuff, to make our lives easy
if (defined('IN_ADMIN') && !defined('JSDIR')) {
define('JSDIR','../classes/cjax/core/js/');
}
require_once CLASSES_DIR . 'cjax/cjax.php';

/**
 * Class that wraps the CJAX_FRAMEWORK, used to get the CJAX class.
 * 
 * @package System
 * @since Version 4.0.0
 */
abstract class geoCJAX extends CJAX_FRAMEWORK {
	/**
	 * Gets an instance of geoCJAX
	 *
	 * @return geoCJAX
	 */
	public static function getInstance()
	{
		return CJAX::initciate();
	}
}