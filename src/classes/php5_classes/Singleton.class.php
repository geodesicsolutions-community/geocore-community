<?php
//Singleton.class.php
/**
 * Singleton method to make sure duplicate objects do not happen.  Note that a
 * lot of the System classes have their own getInstance() methods that should
 * be used (if they exist) instead of using the Singleton class.
 * 
 * @package System
 * @since Forever
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
## ##    7.4.3-11-g745410f
## 
##################################

/**
 * Singleton method class
 * 
 * @package System
 */
class Singleton {
	
	/**
	 * Internal use.
	 * @internal
	 */
	private static $registry = array();
	
	/**
	 * Get instance of class name
	 *
	 * @param string $class
	 * @return Object
	 */
	public static function getInstance ($class){
		if ($class=='ADONewConnection'){
			$class='DataAccess';
		}
		if ($class == 'DataAccess'){
			return DataAccess::getInstance();
		}
		if ($class == 'Session') {
			return geoSession::getInstance();
		}
		if ($class == 'product_configuration') {
			//for old addons, compatibility
			return geoPC::getInstance();
		}
		if (!isset(self::$registry[$class]) || !is_object(self::$registry[$class])){
			//special case, check to see if the class wants a new one of it made automatically.
			if (defined($class.'_no_new_instance')){
				return null;
			}
			if (class_exists($class)){
				self::$registry[$class] = new $class;
			} 
//un-comment following block for trace stack to figure out what is calling invalid class name.			
/*
			else {
				throw new Exception('Error: Class does not exist: '.$class);
				//return false;
			} */
		}
		return self::$registry[$class];
	}
	
	/**
	 * Sees if the given class name is already instantiated.
	 *
	 * @param string $class
	 * @return boolean
	 */
	public static function isInstance($class){
		if ($class=='ADONewConnection')
			$class='DataAccess';
		return (isset(self::$registry[$class])&&is_object(self::$registry[$class]));
	}
}
