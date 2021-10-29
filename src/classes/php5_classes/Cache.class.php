<?php
//Cache.class.php
/**
 * This holds all the tools for using Geo Cache in it.
 * 
 * @package System
 * @since Version 3.1.0
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
## ##    16.02.1-22-g120279e
## 
##################################

/**
 * A hard-coded setting, forget what it's for at the moment.
 * @var bool
 */
define('GEO_NO_COMPRESSION',false);

/**
 * This is the main part of the Geo cache system, it does all the "low level"
 * stuff like writing files and escaping cache data.
 * 
 * @package System
 * @since Version 3.1.0
 */
class geoCache {
	
	/**
	 * Cache settings that can be used by get() and set(),
	 * and are saved to cache settings file.
	 *
	 * @var Array
	 */
	public static $settings;
	
	/**
	 * Array of files that will be written on page end
	 * @var array
	 */
	public static $files;
	
	/**
	 * Used internally
	 * @internal
	 */
	private static $memcache;

	/**
	 * Initializes the cache settings, for use in get() and set().
	 *
	 */
	public static function initSettings()
	{
		if (!isset(self::$settings) || !is_array(self::$settings)){
			//only run if settings not already set.
			$setting = geoCache::inc('_cacheSettings.php');
			if (is_array($setting)){
				//include a success!
				self::$settings = $setting;
			} else {
				//including cache settings failed!
				self::$settings = self::getDefaultSettings();
				//force it to write the settings.
				self::set('use_cache', false);
			}
		}
	}
	
	/**
	 * Gets a set of default cache settings
	 *
	 * @return Array of default settings for cache
	 */
	public static function getDefaultSettings(){
		$min1 = 60;
		$min5 = 60*5;
		$min30 = 60*30;
		$hour1= 60*60;
		$day1 = 60*60*24;
		$settings = array (	
			'use_cache' => true, //main on/off
			'cache_page' => true, //cache page/module output
			'cache_module' => true, //cache module data (not the output)
			'cache_setting' => true, //cache site settings data
			'cache_text' => true, //cache page/module messages
		
			//settings for age of cache:
			// settof of -1 = no cache at all, setting of 0 = cache forever.
			'age_page_all' => $day1, //default time: cache lasts 24 hours.

			//settings for modules - needs to be on module per module basis.
 	 		//syntax for cache age: age_page_module_replace_tag => time in seconds to cache.  0=forever, -1=do not cache
			//syntax for cache with filters: cache_filter_page_module_replace_tag => true or false, if true will use
				//cache even if state, zip, or site wide filters are in effect.
			'age_page_featured_ads_1'  => $min5, //(46)Featured Listing Module - 1
				'cache_filter_page_featured_ads_1'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_ads_1' => true, // do NOT use the cache for this module for admin users
			'age_page_featured_ads_2'  => $min5, //(47)Featured Listing Module - 2 - All Categories
				'cache_filter_page_featured_ads_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_ads_2' => true, // do NOT use the cache for this module for admin users
			'age_page_featured_ads_3'  => $min5, //(48)Featured Listing Module - 3
				'cache_filter_page_featured_ads_3'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_ads_3' => true, // do NOT use the cache for this module for admin users
			'age_page_featured_ads_4'  => $min5, //(49)Featured Listing Module - 4
				'cache_filter_page_featured_ads_4'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_ads_4' => true, // do NOT use the cache for this module for admin users
			'age_page_featured_ads_5'  => $min5, //(50)Featured Listing Module - 5
				'cache_filter_page_featured_ads_5'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_ads_5' => true, // do NOT use the cache for this module for admin users
			'age_page_display_username'  => -1, //(53)Display User Identifier
				'cache_filter_page_display_username'  =>  true,  //do cache when using filter
			'age_page_login_register_link'  => 0, //(54)Display Login/Register
				'cache_filter_page_login_register_link'  =>  true,  //do cache when using filter
			'age_page_newest_ads_1'  => $min5, //(60)Newest Listings 1
				'cache_filter_page_newest_ads_1'  =>  false,  //do NOT cache when using filter
				'nocache_admin_newest_ads_1' => true, // do NOT use the cache for this module for admin users
			'age_page_newest_ads_2'  => $min5, //(61)Newest Listings 2
				'cache_filter_page_newest_ads_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_newest_ads_2' => true, // do NOT use the cache for this module for admin users
			'age_page_newest_ads_link'  => 0, //(66)Link to Newest in Last 24 hrs
				'cache_filter_page_newest_ads_link'  =>  true,  //do cache when using filter
			'age_page_featured_pic_link'  => 0, //(67)Link to Featured Picture Listings Page
				'cache_filter_page_featured_pic_link'  =>  true,  //do cache when using filter
			'age_page_featured_text_link'  => 0, //(68)Link to Featured Text Listings
				'cache_filter_page_featured_text_link'  =>  true,  //do cache when using filter
			'age_page_newest_ads_link_1'  => 0, //(78)Link to Newest in Last Week
				'cache_filter_page_newest_ads_link_1'  =>  true,  //do cache when using filter
			'age_page_newest_ads_link_2'  => 0, //(79)Link to Newest in Last 2 Weeks
				'cache_filter_page_newest_ads_link_2'  =>  true,  //do cache when using filter
			'age_page_newest_ads_link_3'  => 0, //(80)Link to Newest in Last 3 Weeks
				'cache_filter_page_newest_ads_link_3'  =>  true,  //do cache when using filter
			'age_page_search_link'  => -1, //(88)Link to Search (category dynamic)
				'cache_filter_page_search_link'  =>  false,  //do NOT cache when using filter
			'age_page_module_featured_pic_1'  => $min5, //(89)Featured Listing Module - Pic Display - 1
				'cache_filter_page_module_featured_pic_1'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_1' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_2'  => $min5, //(90)Featured Listing Module - Pic Display - 2
				'cache_filter_page_module_featured_pic_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_2' => true, // do NOT use the cache for this module for admin users
			'age_page_classified_navigation_1'  => $min30, //(94)Category Navigation
				'cache_filter_page_classified_navigation_1'  =>  false,  //do NOT cache when using filter
			'age_page_classified_navigation_2'  => $min30, //(95)Category Navigation 2
				'cache_filter_page_classified_navigation_2'  =>  false,  //do NOT cache when using filter
			'age_page_classified_navigation_3'  => $min30, //(96)Category Navigation 3
				'cache_filter_page_classified_navigation_3'  =>  false,  //do NOT cache when using filter
			'age_page_category_tree_1'  => $min30, //(97)Category Tree Navigation 1
				'cache_filter_page_category_tree_1'  =>  false,  //do NOT cache when using filter
			'age_page_category_tree_2'  => $min30, //(98)Category Tree Navigation 2
				'cache_filter_page_category_tree_2'  =>  false,  //do NOT cache when using filter
			'age_page_category_tree_3'  => $min30, //(99)Category Tree Navigation 3
				'cache_filter_page_category_tree_3'  =>  false,  //do NOT cache when using filter
			'age_page_main_classified_navigation_1'  => $min30, //(100)Main Category Navigation 1
				'cache_filter_page_main_classified_navigation_1'  =>  false,  //do NOT cache when using filter
			'age_page_module_featured_pic_3'  => $min5, //(102)Featured Listing Module - Pic Display - 3
				'cache_filter_page_module_featured_pic_3'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_3' => true, // do NOT use the cache for this module for admin users
			'age_page_main_classified_level_navigation_1'  => $min30, //(114)Category Level Navigation 1
				'cache_filter_page_main_classified_level_navigation_1'  =>  false,  //do NOT cache when using filter
			'age_page_module_featured_pic_1_level_2'  => $min5, //(117)Featured Listing Module - Pic Display - 1
				'cache_filter_page_module_featured_pic_1_level_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_1_level_2' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_2_level_2'  => $min5, //(118)Featured Listing Module - Pic Display - 2
				'cache_filter_page_module_featured_pic_2_level_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_2_level_2' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_1_level_3'  => $min5, //(119)Featured Listing Module - Pic Display - 1
				'cache_filter_page_module_featured_pic_1_level_3'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_1_level_3' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_2_level_3'  => $min5, //(120)Featured Listing Module - Pic Display - 2
				'cache_filter_page_module_featured_pic_2_level_3'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_2_level_3' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_1_level_4'  => $min5, //(121)Featured Listing Module - Pic Display - 1
				'cache_filter_page_module_featured_pic_1_level_4'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_1_level_4' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_2_level_4'  => $min5, //(122)Featured Listing Module - Pic Display - 2
				'cache_filter_page_module_featured_pic_2_level_4'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_2_level_4' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_1_level_5'  => $min5, //(123)Featured Listing Module - Pic Display - 1
				'cache_filter_page_module_featured_pic_1_level_5'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_1_level_5' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_pic_2_level_5'  => $min5, //(124)Featured Listing Module - Pic Display - 2
				'cache_filter_page_module_featured_pic_2_level_5'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_pic_2_level_5' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_1_level_2'  => $min5, //(125)Featured Listing Module - 1
				'cache_filter_page_module_featured_1_level_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_1_level_2' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_2_level_2'  => $min5, //(126)Featured Listing Module - 2
				'cache_filter_page_module_featured_2_level_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_2_level_2' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_1_level_3'  => $min5, //(127)Featured Listing Module - 1
				'cache_filter_page_module_featured_1_level_3'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_1_level_3' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_2_level_3'  => $min5, //(128)Featured Listing Module - 2
				'cache_filter_page_module_featured_2_level_3'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_2_level_3' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_1_level_4'  => $min5, //(129)Featured Listing Module - 1
				'cache_filter_page_module_featured_1_level_4'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_1_level_4' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_2_level_4'  => $min5, //(130)Featured Listing Module - 2
				'cache_filter_page_module_featured_2_level_4'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_2_level_4' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_1_level_5'  => $min5, //(131)Featured Listing Module - 1
				'cache_filter_page_module_featured_1_level_5'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_1_level_5' => true, // do NOT use the cache for this module for admin users
			'age_page_module_featured_2_level_5'  => $min5, //(132)Featured Listing Module - 2
				'cache_filter_page_module_featured_2_level_5'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_featured_2_level_5' => true, // do NOT use the cache for this module for admin users
			'age_page_module_zip_filter_1'  => 0, //(133)Browsing Zip Filter
				'cache_filter_page_module_zip_filter_1'  =>  false,  //do NOT cache when using filter
			'age_page_module_state_filter_1'  => 0, //(134)Browsing State Filter
				'cache_filter_page_module_state_filter_1'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_1'  => $min30, //(158)Fixed Category Navigation 1
				'cache_filter_page_subcategory_navigation_1'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_2'  => $min30, //(159)Fixed Category Navigation 2
				'cache_filter_page_subcategory_navigation_2'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_3'  => $min30, //(160)Fixed Category Navigation 3
				'cache_filter_page_subcategory_navigation_3'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_4'  => $min30, //(161)Fixed Category Navigation 4
				'cache_filter_page_subcategory_navigation_4'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_5'  => $min30, //(162)Fixed Category Navigation 5
				'cache_filter_page_subcategory_navigation_5'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_6'  => $min30, //(163)Fixed Category Navigation 6
				'cache_filter_page_subcategory_navigation_6'  =>  false,  //do NOT cache when using filter
			'age_page_subcategory_navigation_7'  => $min30, //(164)Fixed Category Navigation 7
				'cache_filter_page_subcategory_navigation_7'  =>  false,  //do NOT cache when using filter
			'age_page_module_total_live_users'  => $min5, //(170)Total Live Users
				'cache_filter_page_module_total_live_users'  =>  true,  //do cache when using filter
			'age_page_module_total_registered_users'  => $min30, //(169)Total Number of registered users
				'cache_filter_page_module_total_registered_users'  =>  true,  //do cache when using filter
			'age_page_module_title'  => -1, //(171)Title Module
				'cache_filter_page_module_title'  =>  true,  //do cache when using filter
			'age_page_module_hottest_ads'  => $min30, //(172)Hottest Listings Module
				'cache_filter_page_module_hottest_ads'  =>  false,  //do NOT cache when using filter
				'nocache_admin_module_hottest_ads' => true, // do NOT use the cache for this module for admin users
			'age_page_featured_category_1'  => $min5, //(155)Featured Listing Module 1 - Specific Category Only
				'cache_filter_page_featured_category_1'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_category_1' => true, // do NOT use the cache for this module for admin users
			'age_page_featured_category_2'  => $min5, //(156)Featured Listing Module 2 - Specific Category Only
				'cache_filter_page_featured_category_2'  =>  false,  //do NOT cache when using filter
				'nocache_admin_featured_category_2' => true, // do NOT use the cache for this module for admin users
			'age_page_category_dropdown'  => 0,  //(10199)Category Dropdown Box
				'cache_filter_page_category_dropdown'  =>  true,  //do cache when using filter
			'age_page_category_browsing_options'  => -1,  //(10200)Category Browsing Options
				'cache_filter_page_category_browsing_options'  =>  true,  //do cache when using filter
			'age_page_my_account_links'  => -1,  //(10208)My account links module
				'cache_filter_page_my_account_links'  =>  true,  //do cache when using filter
			'age_page_module_search_box_1'  => -1,  //(10200)Category Browsing Options
				'cache_filter_page_module_search_box_1'  =>  false,  //do NOT cache when using filter
			'age_page_tag_search'  => -1,  //tag search
				'cache_filter_page_tag_search'  =>  false,  //do NOT cache when using filter
		);
		return $settings;
	}
	/**
	 * Gets the value for the given setting index
	 *
	 * @param String $index The index for the setting
	 * @return Mixed The value of the given index, or false if index not found.
	 */
	public static function get($index){
		//get the settings
		self::initSettings();
		//if use_cache is false, or the setting is not set, return false, otherwise return value of index.
		if (!self::$settings['use_cache']){
			return false;
		}
		if (isset(self::$settings[$index])){
			return self::$settings[$index];
		}
			
		return false;
	}
	
	/**
	 * Set the given index to the given value, in the cache
	 * settings, and update the cache settings file.
	 *
	 * @param String $index
	 * @param Mixed $value Can be any type, except Objects.
	 */
	public static function set ($index, $value){
		//force it to refresh if changing use_cache
		$force = ($index == 'use_cache')? true: false;
		
		//make sure system is inited..
		self::initSettings();

		self::$settings[$index] = $value;
		
		//write to settings file.
		//first, create text to write.
		$settingTxt = '<?php return('.self::quoteVal(self::$settings).');';
		if ($force){
			//update the init file system,
			//force it to re-do checks.
			geoCache::initCacheFileSystem($force);	
		}
		//now write it to file.
		self::write('_cacheSettings.php',$settingTxt);
	}
	
	/**
	 * Reads the contents of the given filename, as long as the file has not expired,
	 * and is in a sub-folder of the CACHE_DIR.
	 * 
	 * The filename must be one that was previously written to using geoString::write()
	 *
	 * @param String $filename The filename, relative to the main cache dir CACHE_DIR
	 */
	public static function read($filename){
		//check input vars
		if (strlen(trim($filename)) == 0){
			trigger_error('ERROR CACHE: Filename string length is 0, invalid filename specified.');
			return false;
		}
		
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			if (!self::_memcacheInit()){
				return false;
			}
			$recordItem = ($filename == '_geoCachedItems')? false: true;
			
			$filename = GEO_MEMCACHE_SETTING_PREFIX.$filename;
			$storage = self::$memcache->get($filename);
			if ($recordItem && !$storage){
				$current_cached = self::$memcache->get(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems');
				if (!is_array($current_cached)){
					$current_cached = array();
				}
				if (isset($current_cached[$filename])){
					unset($current_cached[$filename]);
				}
				self::$memcache->set(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems',$current_cached,GEO_NO_COMPRESSION,0);
			}
			return $storage;
		}
		
		
		$file = CACHE_DIR.$filename;
		
		if (strpos(dirname($file),dirname(CACHE_DIR)) !== 0){
			//file is outside of CACHE_DIR location, block reading this file
			trigger_error('ERROR CACHE: Read attempt failed!  Debug info: Attempt to read outside of CACHE_DIR directory!  $filename='.geoString::specialChars($filename).' CACHE_DIR='.CACHE_DIR.' CACHE_DIR.$filename='.$file);
			return false;
		}
		if (isset(self::$files[$filename])){
			//this one has not been written to file yet
			$txt = self::$files[$filename]['contents'];
			
			trigger_error('DEBUG STATS CACHE: Read called for a file that has not been written yet, but is
				already set internally in the queue to be written.');
			return $txt;
		}
		
		if (!file_exists($file)){
			//file is so new, the file does not exist yet!
			trigger_error('DEBUG STATS CACHE: geoCache::read - file ('.$file.') does not exist, return false');
			return false;
		}
		if (!file_exists($file.'.EXPIRE')) {
			//this is old school cache file, delete it
			trigger_error('DEBUG CACHE: Encountered a file without an expiration file, so deleting file.');
			unlink ($file);
			return false;
		}
		//file should be so small, don't need to worry about locking.
		$age = file_get_contents($file.'.EXPIRE');
		if ($age === false) {
			trigger_error('ERROR CACHE: getting expire for file failed!');
			return false;
		}
		
		if (intval($age) != $age || strlen($age)>12){
			//some kind of weird age, not a number
			trigger_error('DEBUG CACHE: geoCache::read() - age is either too long, or not an integer, probably this is an older cache file.');
			return false;
		}
		$age = intval($age);
		if ($age > 0){
			//this file expires, make sure it has not already expired.
			$currentTime = time();
			if ($age < $currentTime){
				//this file has expired!
				trigger_error('DEBUG CACHE: $age < $currentTime (file has expired) so returning false.');
				return false;
			}
		}
		
		$handle = fopen($file, 'r');
		if (!$handle){
			//open for read did not work
			trigger_error('ERROR CACHE: fopen returned false when attempting to read.');
			return false;
		}
		//use shared lock
		if (!flock($handle,LOCK_SH)){
			//lock failed
			trigger_error('ERROR CACHE: flock() failed when attempting to obtain a shared lock to read a file.');
			fclose($handle);
			return false;
		}
		
		//read the contents of the file.
		trigger_error('DEBUG STATS CACHE: geoCache::read('.$filename.') - before main read');
		$string = fread($handle, filesize($file));
		
		flock($handle, LOCK_UN);
		fclose($handle);
		trigger_error('DEBUG STATS CACHE: geoCache::read('.$filename.') - end');
		return trim($string);
	}
	

	/**
	 * returns true of false depending on wether memcache is enabled or not
	 *
	 */
	public static function memcache_exists()
	{
		return (class_exists('Memcache'))? true: false;
	}
	/**
	 * Initialize memcache
	 * @return boolean
	 */
	private static function _memcacheInit(){
		if (!self::memcache_exists()){
			return false;
		}
		if (!is_object(self::$memcache)) {
			//make sure config file has added any servers that it might want to
			include GEO_BASE_DIR.'config.default.php';
		}
		if (!is_object(self::$memcache)){
			self::$memcache = new Memcache;
			if (!self::$memcache->connect('localhost', 11211)){
				trigger_error('ERROR CACHE: Could not connect to memcache, cannot init memcache.');
				self::$memcache = null;
				return false;
			}
		}
		return true;
	}
	
	/**
	 * If memcache is turned on, use this to get the memcache object to do
	 * fancy things like connecting to several different servers, or changing
	 * server settings or something.
	 * 
	 * Note:  If this is called before memcache is initialized, you MUST 
	 * make a connection to the server yourself using memcache->connect() or
	 * memcache->pconnect()
	 *
	 * @return Memcache
	 */
	public static function getMemcacheObj(){
		if (self::memcache_exists()){
			if (!is_object(self::$memcache)){
				self::$memcache = new Memcache;
			}
			return self::$memcache;
		}
		return false;
	}
	/**
	 * Add some file to registry
	 * @param string $filename
	 * @return boolean
	 */
	private static function _memcacheAddToRegistry($filename=''){
		if (!self::_memcacheInit()){
			return false;
		}
		for ($i = 0; $i < 15; $i++){
			//method of ensuring we are the only one messing with the key registry..
			//attempt to get a "lock" (that expires after 60 seconds), if we fail,
			//wait a little and try again (up to 15 times)
			if (self::$memcache->add(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems_LOCK',1,0,60)){
				//we now have a "lock"
				$current_cached = self::$memcache->get(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems');
				if (!is_array($current_cached)){
					$current_cached = array();
				}
				if ($filename == ''){
					foreach (self::$files as $filename => $v){
						$current_cached[$filename] = 1;
					}
				} else {
					$current_cached[$filename] = 1;
				}
				self::$memcache->set(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems',$current_cached,GEO_NO_COMPRESSION,0);
				self::$memcache->delete(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems_LOCK');
				break; //finished, so exit out of the loop
			}
			//did not get the lock, sleep for a little then try again.
			usleep(100000);
		}
	}
	/**
	 * Remove the file from memcache registry
	 * @param string $filename
	 */
	private static function _memcacheRemoveFromRegistry($filename){
		for ($i = 0; $i < 15; $i++){
			//method of ensuring we are the only one messing with the key registry..
			//attempt to get a "lock" (that expires after 60 seconds), if we fail,
			//wait a little and try again (up to 15 times)
			if (self::$memcache->add(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems_LOCK',1,0,60)){
				//lock for 60 seconds (in case script fails in middle of doing this, don't lock forever)
				$current_cached = self::$memcache->get(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems');
				if (!is_array($current_cached)){
					$current_cached = array();
				}
				if (isset($current_cached[$filename])){
					unset($current_cached[$filename]);
					self::$memcache->set(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems',$current_cached,GEO_NO_COMPRESSION,0);
				}
				
				self::$memcache->delete(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems_LOCK');
				break;//finished, so exit out of the loop.
			}
			//did not get the lock, sleep for a little then try again.
			usleep(100000);
		}
	}
	
	/**
	 * Writes the given text to the given file, and records
	 * the amount of time to keep the file around.
	 *
	 * @param String $filename relative to cache dir
	 * @param String $txt text to write to file (binary safe)
	 * @param Int $expire_time Amount of time, in seconds, to 
	 *  keep the file around before expireing it.  If 0, file will
	 *  never expire automatically
	 * @return Boolean True if write was successful, false otherwise.
	 */
	public static function write($filename, $txt, $expire_time = 0){
		//check input vars
		$expire_time = intval($expire_time);
		if (strlen(trim($filename)) == 0){
			trigger_error('ERROR CACHE: Filename string length is 0, invalid filename specified.');
			return false;
		}
		
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			$ignore = array ('_cacheSettings.php', '_geoCachedItems');
			$recordItem = (in_array($filename,$ignore))? false: true;
			
			$filename = GEO_MEMCACHE_SETTING_PREFIX.$filename;
			if (!self::_memcacheInit()){
				return false;
			}
			if ($recordItem){
				self::$files[$filename]=1;
			}
			return (self::$memcache->set($filename, $txt, GEO_NO_COMPRESSION, $expire_time));
		}
		
		self::initCacheFilesystem();
		$file = CACHE_DIR . $filename;
		trigger_error('DEBUG STATS CACHE: Top of write() for file '.$file);
		
		//add the expiration time to the beginning of the file.
		if ($expire_time > 0){
			//if expire time is > 0, then add it to current time
			//we record when the file expires, not how long till it expires
			$expire_time = ($expire_time + time());
		}
		
		//write the cache to the file.
		if (!is_array(self::$files)){
			self::$files = array();
		}
		self::$files[$filename]['contents'] = $txt;
		self::$files[$filename]['expire'] = $expire_time;
		
		trigger_error('DEBUG STATS CACHE: geoCache::write() - end');
		//it got this far, it should have been successful.
		return true;
	}
	
	/**
	 * Expires a certain file from existance (or if using memcache, removes
	 * the given index)
	 *
	 * @param string $filename
	 */
	public static function expire ($filename)
	{
		//check input vars
		if (strlen(trim($filename)) == 0){
			trigger_error('ERROR CACHE: Filename string length is 0, invalid filename specified.');
			return false;
		}
		
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			$filename = GEO_MEMCACHE_SETTING_PREFIX.$filename;
			if (!self::_memcacheInit()){
				return false;
			}
			self::_memcacheRemoveFromRegistry($filename);
			
			return (self::$memcache->delete($filename));
		}
		
		$filename = CACHE_DIR.$filename;
		if (file_exists($filename)){
			unlink($filename);
		}
		if (file_exists($filename.'.EXPIRE')) {
			unlink($filename.'.EXPIRE');
		}
		return false;
	}
	
	/**
	 * Writes all the pending files in one go.  This is so that this process can be done in app_bottom, so that
	 * it is done after the page is already sent to the client.
	 * 
	 * This is meant to be called from app_bottom.
	 */
	public static function writeCache () {
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			//All writes when using memcache are done at that time, so there
			//is nothing to do here.
			
			//But, we can take advantage of this time to close our connection, so
			//that we are a friendly script.
			self::_memcacheAddToRegistry();
			if (is_object(self::$memcache)){
				self::$memcache->close();
				self::$memcache = null;
			}
			return true;
		}
		
		if (!is_array(geoCache::$files) || count(geoCache::$files) == 0){
			//no files to write.
			trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - top - no files to write.');
		
			return false;
		}
		trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - start');
		clearstatcache();
		$files = array_keys(geoCache::$files);
		foreach ($files as $filename) {
			//we loop through index keys, to cut down on amount of duplicate data since contents of cache can get quite large.
			
			$file = CACHE_DIR . $filename;
			if (strpos(dirname($file),dirname(CACHE_DIR)) !== 0){
				//file is outside of CACHE_DIR location, block writing this file
				trigger_error('ERROR CACHE: geoCache::writeCache() - invalid filename, cannot write a file outside
				of the '.CACHE_DIR.' directory.');
				continue;
			}
			if (!file_exists(dirname($file))){
				trigger_error('DEBUG STATS CACHE: geoCache::write() - directory does not exist, so creating it.');
				//create the folder if it does not exist yet.
				mkdir(dirname($file),0777,true);
				//make sure the directory has an index file in it
				//to prevent index listings
				touch(dirname($file).'/index.php');
			}
			trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - writting file '.$filename);
			
			//write the file contents, be sure to obtain exclusive lock to prevent race conditions
			$results = file_put_contents ($file, self::$files[$filename]['contents'], LOCK_EX);
			
			if ($results === false) {
				//something went wrong with writting
				trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - error writting cache file, file_put_contents returned false.');
				continue;
			}
			
			//write info about expire time
			$expire_result = file_put_contents($file.'.EXPIRE',self::$files[$filename]['expire'], LOCK_EX);
			if ($expire_result === false) {
				//something went wrong with writting
				trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - error writting expiration of cache file, file_put_contents returned false.');
			}
			
			trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - finished writting file.');
			
			unset($file);
			unset(self::$files[$filename]);
		}
		trigger_error('DEBUG STATS CACHE: geoCache::writeCache() - finished writting all files.');
	}
	
	/**
	 * Simulate including a cache file previously created using geoCache::write().
	 * Since the cache file will have expiration data on the first line,
	 * it cannot be included directly, so this function must be used instead
	 * to simulate including the file.
	 * 
	 * Note: when using cache, never design it so that the cache file returns the
	 *  boolean false, otherwise you will not be able to tell the difference between
	 *  a cache miss and it just returning false.  Instead, wrap the false var in
	 *  an array or something.
	 *
	 * @param string $filename
	 * @return Mixed Boolean false if the read failed, or the results of 
	 *  what would happen if you included the file.
	 */
	public static function inc ($filename){
		$result = include CLASSES_DIR . PHP5_DIR . 'Cache.inc.php';
		return $result;
	}
	
	/**
	 * Sees if the given filename exists or not.
	 * Filename should be relative to
	 * the GEO_CACHE directory (and not allowed to escape that directory)
	 * @param $filename The filename, relative to the cache directory.
	 * @return bool
	 */
	public static function file_exists ($filename)
	{
		//check input vars
		if (strlen(trim($filename)) == 0){
			trigger_error('ERROR CACHE: Filename string length is 0, invalid filename specified.');
			return false;
		}
		
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			if (!self::_memcacheInit()){
				return false;
			}
			
			$filename = GEO_MEMCACHE_SETTING_PREFIX.$filename;
			if (self::$memcache->get($filename) === false) {
				return false;
			} else {
				return true;
			}
		}
		
		$file = CACHE_DIR.$filename;
		
		if (strpos(dirname($file),dirname(CACHE_DIR)) !== 0){
			//file is outside of CACHE_DIR location, block reading this file
			trigger_error('ERROR CACHE: Read attempt failed!  Debug info: Attempt to read outside of CACHE_DIR directory!  $filename='.geoString::specialChars($filename).' CACHE_DIR='.CACHE_DIR.' CACHE_DIR.$filename='.$file);
			return false;
		}
		if (isset(self::$files[$filename])){
			return true;
		}
		
		if (!file_exists($file)){
			//file is so new, the file does not exist yet!
			trigger_error('DEBUG STATS CACHE: geoCache::read - file ('.$file.') does not exist, return false');
			return false;
		}
		if (!file_exists($file.'.EXPIRE')) {
			//this is old school cache file, delete it
			trigger_error('DEBUG CACHE: Encountered a file without an expiration file, so deleting file.');
			unlink ($file);
			return false;
		}
		//file should be so small, don't need to worry about locking.
		$age = (int)file_get_contents($file.'.EXPIRE');
		
		if ($age > 0){
			//this file expires, make sure it has not already expired.
			$currentTime = time();
			if ($age < $currentTime){
				//this file has expired!
				trigger_error('DEBUG CACHE: $age < $currentTime (file has expired) so returning false.');
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Goes through all the cache files, and looks for any that should expire, and delete's
	 * them.
	 * 
	 * @param string $dir
	 */
	public static function cleanUp ($dir = CACHE_DIR)
	{
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			//memcache does it's own cleaning, don't need to do anything
			return;
		}
		
		if (strpos(dirname($dir),dirname(CACHE_DIR)) !== 0){
			//file is outside of CACHE_DIR location, block cleaning this dir.
			return false;
		}
		if(!$dh = opendir($dir)) return;
		$dir = dirname($dir.'/test').'/';
		$currentTime = time();
		$skipFiles = array ('.','..','index.php');
		while (($obj = readdir($dh))) {
			if(in_array($obj,$skipFiles) || strpos($obj, '.EXPIRE') !== false) continue;
			if (is_dir($dir.$obj)){
				//this is a directory, recurse into it
				geoCache::cleanUp($dir.$obj);
				continue;
			}
			if (!file_exists($dir.$obj.'.EXPIRE')) {
				//file not having expire, remove it
				unlink($dir.$obj);
			}
			$handle = fopen($dir.$obj,'r');
			//lock file while reading
			if (!$handle || !flock($handle, LOCK_EX)){
				fclose($handle);
				continue;
			}
			//get the age
			$age = (int)file_get_contents($dir.$obj.'.EXPIRE');
			if ($age > 0){
				//this file expires, make sure it has not already expired.
				if ($age < $currentTime){
					//this file has expired!
					flock($handle, LOCK_UN);
					fclose($handle);
					//so remove it!
					unlink($dir.$obj);
					unlink($dir.$obj.'.EXPIRE');
					continue;
				}
			}
			flock($handle, LOCK_UN);
			fclose($handle);
			unset($handle);
		}
		closedir($dh);
		clearstatcache();
	}
	
	
	/**
	 * Removes all of the cache files from the _geocache sub-folders.
	 *
	 * @param string $type If only want to clear one type of cache
	 * @param int $trys Used internally if using memcache, leave this at default of 0
	 *  modules, settings, text, pages, templates, or all(default)
	 */
	public static function clearCache($type = 'all', $trys = 0){
		//check vars:
		$accepted_types = array ('all',
			'modules',
			'settings',
			'text',
			'pages',
			'templates',
			'temp');
		if (!in_array($type, $accepted_types)){
			trigger_error('ERROR CACHE: Cannot clear cache of type '.$type.', that type is not known.  (security precaution, only known types can be cleared)');
		}
		
		if (GEO_CACHE_STORAGE == 'memcache'){
			//using memcache to store
			if (!self::_memcacheInit()){
				return false;
			}
			for ($i = 0; $i < 15; $i++){
				//method of ensuring we are the only one messing with the key registry..
				//attempt to get a "lock" (that expires after 60 seconds), if we fail,
				//wait a little and try again (up to 15 times)
				if (self::$memcache->add(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems_LOCK',1,0,60)){
					$current_cached = self::$memcache->get(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems');
					if (!is_array($current_cached)){
						return;
					}
					foreach ($current_cached as $key => $val){
						if ($type == 'all' || strpos($key,GEO_MEMCACHE_SETTING_PREFIX.'_'.$type.'/') === 0){
							//delete cache
							self::$memcache->delete($key);
							unset($current_cached[$key]);
						}
					}
					self::$memcache->set(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems',$current_cached,GEO_NO_COMPRESSION,0);
					self::$memcache->delete(GEO_MEMCACHE_SETTING_PREFIX.'_geoCachedItems_LOCK');
					break; //finished, exit out of the loop
				}
				//did not get the lock, sleep a little then try again.
				usleep(100000);
			}
			return ;
		}
		
		//delete from filesystem
		switch ($type){
			case 'all':
				//going to also remove all cache settings
				$use_cache = (isset(self::$settings['use_cache']) && self::$settings['use_cache'])? true: false;
				//reset the module time to cache settings
				self::$settings = self::getDefaultSettings();
				//force it to write the settings.
				self::set('use_cache', $use_cache);
				//break ommited on purpose
				
			case 'modules':
				//remove all files in _modules
				geoCache::rUnlink(CACHE_DIR.'_modules', false);
				if ($type != 'all') {break 1;}
				
			case 'settings':
				//remove all files in _settings
				geoCache::rUnlink(CACHE_DIR.'_settings', false);
				if ($type != 'all') {break 1;}
				
			case 'text':
				//remove all files in _text
				geoCache::rUnlink(CACHE_DIR.'_text', false);
				if ($type != 'all') {break 1;}
				
			case 'pages':
				//remove all files in _pages
				geoCache::rUnlink(CACHE_DIR.'_pages', false);
				if ($type != 'all') {break 1;}
				
			case 'templates':
				//remove all files in _templates
				geoCache::rUnlink(CACHE_DIR.'_templates', false);
				if ($type != 'all') {break 1;}
				
			case 'temp':
				//remove all files in _temp
				geoCache::rUnlink(CACHE_DIR.'_temp', false);
		}
	}
	
	/**
	 * Used by clearCache() to clear all the sub-folders of the cache system.
	 * This is DANGEROUS!  Carefull what dir you are sicking this thing on!
	 * This blocks deleting if the dir is not inside the cache_dir directory.
	 *
	 * @param string $dir
	 * @param boolean $DeleteMe
	 */
	private static function rUnlink($dir, $DeleteMe) {
		if (strpos(dirname($dir),dirname(CACHE_DIR)) !== 0){
			//file is outside of CACHE_DIR location, block reading this file
			return;
		}
		
		if(!$dh = opendir($dir)) return;
		while (($obj = readdir($dh))) {
			if($obj=='.' || $obj=='..') continue;
			if (!unlink($dir.'/'.$obj)) geoCache::rUnlink($dir.'/'.$obj, true);
		}
		closedir($dh);
		if ($DeleteMe){
			rmdir($dir);
		}
	}
	
	
	/**
	 * Initializes the cache filesystem, creating files
	 *  and folders when needed, but only if the cache
	 *  file system is turned on.
	 * 
	 * @param bool $force If true, force it to init even if it already inited in
	 *   this page load.
	 * @return Boolean true if cache file system was 
	 *  initialized, false otherwise.
	 */
	public static function initCacheFilesystem($force = false){
		static $cache_system = null;
		
		if (!$force && !is_null($cache_system)){
			return $cache_system;
		}
		
		if (GEO_CACHE_STORAGE == 'memcache'){
			if (self::_memcacheInit() === false){
				$cache_system = false;
				return $cache_system;
			}
		} else {
			//Using filesystem, do filesystem checks
			
			if (!geoCache::is_writable(CACHE_DIR)){
				trigger_error('DEBUG STATS CACHE: initCacheFilesystem() failed.');
				$cache_system = false;
				return false;
			}
			//make sure index.php exists to prevent directory listings
			if (!file_exists(CACHE_DIR.'index.php')){
				if (!touch (CACHE_DIR.'index.php')) {
					trigger_error('DEBUG STATS CACHE: initCacheFilesystem() failed.');
					$cache_system = false;
					return false;
				}
			}
		}
		
		
		//make sure cacheing is turned on:
		if (!geoCache::get('use_cache')){
			trigger_error('DEBUG STATS CACHE: Cache System Turned Off');
			$cache_system = false;
			return false;
		}
		
		//If it gets this far, all systems are go!
		$cache_system = true;
		return true;
	}
	
	/**
	 * Should be over-written by class, this function
	 * should take in variable specific to the type of
	 * cache (settings, page, template, etc), and save
	 * the cache to a file to be used later by the matching
	 * process() function.
	 * 
	 * @return Boolean false if update failed.
	 *
	 */
	public function update ()
	{
		
	}
	/**
	 * Should be over-written by class, this function
	 * should take in variables specific to the type of
	 * cache (settings, page, template, etc), and do whatever
	 * is needed to use the cache.
	 *
	 * @return Mixed return false if process failed.
	 */
	public function process ()
	{
		
	}
	
	
	/**
	 * Correctly checks to see if file or folder is writable.
	 * This accounts for Windows ACLs bug.
	 * see http://bugs.php.net/bug.php?id=27609
	 * see http://bugs.php.net/bug.php?id=30931
	 *
	 * @param String $path Be sure to use trailing slash for folders!
	 * @return Boolean true if file is writable, false otherwise.
	 */
	public static function is_writable($path) {
		if (GEO_CACHE_STORAGE == 'memcache'){
			//memcache is always writable...
			return true;
		}
		
		//will work in despite of Windows ACLs bug
		//NOTE: use a trailing slash for folders!!!
		//see http://bugs.php.net/bug.php?id=27609
		//see http://bugs.php.net/bug.php?id=30931
		if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
		    return self::is_writable($path.uniqid(mt_rand()).'.tmp');
		else if (is_dir($path))
		    return self::is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
		// check tmp file for read/write capabilities
		$rm = file_exists($path);
		$f = @fopen($path, 'a');
		if ($f===false)
		    return false;
		fclose($f);
		if (!$rm)
		    unlink($path);
		return true;
	}
	
	/**
	 * Quotes given text to make it safe to use in php file, 
	 * to prevent code injection.  See contents of function to
	 * see what is quoted and how.
	 *
	 * @param String $text
	 * @return String quoted text, with any potential open or close converted to be echoed.
	 */
	public static function quotePhp($text){
		/*
		 * Escapes by capturing the following and replacing with <?php echo $text ?>
		 * <?
		 * <?php
		 * ?>
		 * <% (asp style tags turned on)
		 * %> (asp style tags turned on)
		 * <script language="php" (prevent use of <script language="php">//php code</script>, see php.net manual
		 *   for details.
		 */
		return preg_replace('/((\<[?%]{1}(php)?)|([?%]{1}\>)|(\<script language\=\"?php\"?))/i','<'.'?php echo \'$1\'; ?'.'>',$text);
	}
	
	/**
	 * Quotes a given variable, converting it to a string
	 *  that can be used in a php file to re-create
	 *  the variable.
	 *
	 * @param Mixed $var Can be any variable type, except object
	 * @return String that can be eval'd or in php file, to
	 *  re-create the variable.
	 */
	public static function quoteVal ($var)
	{
		if (is_object($var)) {
			//this isn't meant to really handle objects, change it into a string
			$var = ''.$var;
		}
		return var_export($var, true);
	}
	
	/**
	 * Serializes params passed in smarty tags, so that can be used as part of
	 * cache file name.
	 * 
	 * @param array $params
	 * @return string
	 * @since Version 7.1.1
	 */
	public function serializeParams ($params)
	{
		$params = (array)$params;
		unset($params['assign']);
		
		//sort by key so that they are always in same order
		ksort($params);
		if (!$params) {
			//nothing to serialize
			return '';
		}
		
		$serial = '';
		foreach ($params as $key => $val) {
			$key = preg_replace('/[^-a-zA-Z0-9_]*/','',$key);
			$val = preg_replace('/[^-a-zA-Z0-9_]*/','',$key);
			$serial .= "($key)$val";
		}
		if (strlen($serial)>100) {
			//Failsafe, just to make sure file name doesn't get too long...
			return 'TOO_LONG';
		}
		return $serial;
	}
}

/**
 * Part of Geo Cache system, this part specifically tuned to work best with cacheing language specific messages.
 * 
 * @package System
 * @since Version 3.1.0
 */
class geoCacheText extends geoCache
{
	/**
	 * Instance of instance
	 * @internal
	 */
	private static $instance;
	/**
	 * private constructor to keep from creating new outside of getInstance()
	 */
	private function __construct ()
	{
		
	}
	/**
	 * Gets an instance of the cache object.
	 * 
	 * @return geoCacheText
	 */
	public static function getInstance(){
		if (!isset(self::$instance) || !is_object(self::$instance)){
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	/**
	 * Inits the cache file system, specific for files needed
	 * for text cache.
	 * 
	 * @param bool $force
	 * @return Boolean result of init
	 */
	public static function initCacheFilesystem($force = false){
		static $cache_system = null;
		
		if (!$force && !is_null($cache_system)){
			return $cache_system;
		}
		
		if (!geoCache::get('cache_text')){
			trigger_error('DEBUG STATS CACHE: Text cache turned off, return false.');
			$cache_system = false;
			return false;
		}
		if (!parent::initCacheFilesystem()) {
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() failed.');
			$cache_system = false;
			return false;
		}
		//directories should now be set up and ready to go.
		$cache_system = true;
		return true;
	}
	
	/**
	 * Updates the text cache for the given language and page
	 *
	 * @param Int $language_id
	 * @param Int $page_id
	 * @param Array $text_array Array of text, similar to what would
	 *  be stored in the site_class::messages var.
	 * @return Boolean result of updating the message data cache.
	 */
	public function update ($language_id=0, $page_id=0, $text_array=array())
	{
		if (defined('THEME_PRIMARY') || defined('THEME_SECONDARY')) {
			//Using primary/secondary cache, do not use text cache
			return false;
		}
		//check inputs
		trigger_error('DEBUG STATS CACHE: Top of geoCacheText::update');
		$language_id = intval($language_id);
		$page_id = intval($page_id);
		
		if (!$language_id || !$page_id) return false;
		
		//initialize cache, in case this is the first time the cache
		//is being used.
		if (!self::initCacheFilesystem()){
			//something ain't right
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() returned false');
			return false;
		}
		$filename = '_text/_text_l'.$language_id.'p'.$page_id.'.php';
		//make sure it is an array.
		if (!is_array($text_array)){
			
			$text_array = array();
		}
		$cache_text = '<?php return('.geoCache::quoteVal($text_array).');';
		
		//write to file, never expire since data is expired when text is changed...
		return (geoCache::write($filename, $cache_text, 0));
	}
	
	/**
	 * Removes the text data cache for a given language and page
	 * 
	 * @param Int $language_id
	 * @param Int $page_id
	 * 
	 * @return Boolean result of expire attempt.
	 */
	public static function expire ($language_id, $page_id=0)
	{
		//validate input
		$language_id = intval($language_id);
		$page_id = intval($page_id);
		if (!geoCache::get('cache_text')){
			trigger_error('DEBUG STATS CACHE: Text cache turned off, return false.');
			return false;
		}
		$filename = '_text/_text_l'.$language_id.'p'.$page_id.'.php';
		return geoCache::expire($filename);
	}
	
	/**
	 * Gets the data for the given language and page from
	 *  the cache.
	 *
	 * @param int $language_id
	 * @param int $page_id
	 * 
	 * @return Mixed The message data for the given page, or
	 *  false if cache miss.
	 */
	public function process ($language_id=0, $page_id=0)
	{
		trigger_error('DEBUG STATS CACHE: Top of geoCacheText::process');
		//check input:
		if (!geoCache::get('cache_text')){
			trigger_error('DEBUG STATS CACHE: Text cache turned off, return false.');
			return false;
		}
		if (defined('THEME_PRIMARY') || defined('THEME_SECONDARY')) {
			//Using primary/secondary cache, do not use text cache
			return false;
		}
		$language_id = intval($language_id);
		$page_id = intval($page_id);
		
		if (!$language_id || !$page_id) return false;
		
		self::initCacheFilesystem();
		
		$filename = '_text/_text_l'.$language_id.'p'.$page_id.'.php';
		$text = geoCache::inc ($filename);
		if (!is_array($text)){
			//text is not an array, it may just be empty.
			return false;
		}
		return $text;
	}
}

/**
 * Part of Geo Cache system, specifically tuned to work best with cacheing module data (which
 * will be an array).
 * 
 * @package System
 * @since Version 3.1.0
 */
class geoCacheModule extends geoCache {
	/**
	 * Holds instance of class
	 * @var geoCacheModule
	 */
	private static $instance;
	/**
	 * Constructor, private on purpose to preven creating new one without using
	 * getInstance()
	 */
	private function __construct()
	{
		
	}
	/**
	 * Gets an instance of the cache object.
	 * 
	 * @return geoCacheModule
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance) || !is_object(self::$instance)){
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	/**
	 * Inits the cache filesystem, specific for module cache
	 *  needs.
	 * 
	 * @param bool $force
	 * @return Result of cache init.
	 */
	public static function initCacheFilesystem($force = false){
		static $cache_system = null;
		
		if (!$force && !is_null($cache_system)){
			return $cache_system;
		}
		
		if (!geoCache::get('cache_module')){
			trigger_error('DEBUG STATS CACHE: Module cache turned off, return false.');
			$cache_system = false;
			return false;
		}
		
		if (!parent::initCacheFilesystem()) {
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() failed.');
			$cache_system = false;
			return false;
		}
		
		//directories should now be set up and ready to go.
		$cache_system = true;
		return true;
	}
	
	/**
	 * Updates the cache for the given module, or creates the cache
	 * if it does not exist yet.
	 *
	 * @param Mixed $module_id Either module_id, or module_replace_tag.
	 * @param Mixed $data Data to cache for module, non-quoted
	 * @return The result of the update.
	 */
	public function update ($module_id=0, $data=null)
	{
		//check inputs
		trigger_error('DEBUG STATS CACHE: Top of geoCacheModule::update');
		
		if (!$module_id) {
			trigger_error('DEBUG STATS CACHE: update failed, $module_id invalid.');
			return false;
		}
		
		//initialize cache, in case this is the first time the cache
		//is being used.
		if (!self::initCacheFilesystem()){
			//something ain't right
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() returned false');
			return false;
		}
		$filename = '_modules/_module'.$module_id.'.php';
		$cache_text = '<?php return('.geoCache::quoteVal($data).');';
		//write to cache, expire of never since this is just data, it is auto updated on change
		return (geoCache::write($filename, $cache_text, 0));
	}
	
	/**
	 * Removes the module data cache for a given module.
	 * 
	 * @param Int $module_id
	 */
	public static function expire ($module_id)
	{
		//validate input
		if (!$module_id) return false;
		$filename = '_modules/_module'.$module_id.'.php';
		return geoCache::expire($filename);
	}
	
	/**
	 * Processes a given module, and returns the data for
	 *  that module.
	 *
	 * @param Mixed $module_id Either the module id, or the module replace tag.
	 * @return False if cache miss, or the cached data if cache hit.
	 */
	public function process ($module_id='')
	{
		trigger_error('DEBUG STATS CACHE: Top of geoCacheModule::process');
		if (!geoCache::get('cache_module')){
			trigger_error('DEBUG STATS CACHE: Module cache turned off, return false.');
			return false;
		}
		//check input:
		if (!$module_id) {
			trigger_error('DEBUG STATS CACHE: Invalid $module_id!  Cant continue');
			return false;
		}
		
		self::initCacheFilesystem();
		$filename = '_modules/_module'.$module_id.'.php';
		if (GEO_CACHE_STORAGE == 'filesystem' && !file_exists(CACHE_DIR.$filename)){
			trigger_error('DEBUG STATS CACHE: geoCacheModule::process - file does not exist, return false. filename:'.$filename);
			return false;
		}
		$data = geoCache::inc ($filename);
		if (!is_array($data)){
			return false;
		}
		return $data;
	}
}

/**
 * Part of Geo Cache system, specifically tuned to work best with cacheing site-wide
 * key=value type settings.
 * 
 * @package System
 * @since Version 3.1.0
 */
class geoCacheSetting extends geoCache {
	/**
	 * Instance of class
	 * @internal
	 */
	private static $instance;
	/**
	 * Private constructor to keep new thingy from being created outside of getInstance()
	 */
	private function __construct(){
		
	}
	/**
	 * Gets an instance of the cache object.
	 * 
	 * @return geoCacheSetting
	 */
	public static function getInstance(){
		if (!isset(self::$instance) || !is_object(self::$instance)){
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	/**
	 * Inits the cache filesystem, specific for files
	 * needed for settings cache
	 * 
	 * @param bool $force
	 * @return result of init.
	 */
	public static function initCacheFilesystem($force = false){
		static $cache_system = null;
		
		if (!$force && !is_null($cache_system)){
			trigger_error('DEBUG STATS CACHE: geoCacheSetting::initCacheFilesystem - already done');
			return $cache_system;
		}
		if (!geoCache::get('cache_setting')){
			trigger_error('DEBUG STATS CACHE: Setting cache turned off, return false.');
			$cache_system = false;
			return false;
		}
		
		if (!parent::initCacheFilesystem()) {
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() failed.');
			$cache_system = false;
			return false;
		}
		
		//directories should now be set up and ready to go.
		$cache_system = true;
		return true;
	}
	
	/**
	 * Updates the cache for the given template, or creates the cache
	 * if it does not exist yet.
	 *
	 * @param String $setting_type
	 * @param Mixed $data The data to store for the settings, can be any
	 *  variable type except for object, and the same var will be returned
	 *  by process.
	 */
	public function update ($setting_type='', $data=null)
	{
		//check inputs
		trigger_error('DEBUG STATS CACHE: Top of geoCacheSetting::update '.$setting_type);
		if (strlen($setting_type) == 0 || !is_array($data)) {
			trigger_error('DEBUG STATS CACHE: update failed, invalid input.');
			return false;
		}
		
		//initialize cache, in case this is the first time the cache
		//is being used.
		if (!self::initCacheFilesystem()){
			//something ain't right
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() returned false');
			return false;
		}
		$filename = '_settings/_'.$setting_type.'.php';
		$cache_text = '<?php return('.geoCache::quoteVal($data).');';
		//write to file system, expires never since it's data
		return (geoCache::write($filename,$cache_text,0));
	}
	
	/**
	 * Removes the cache for a given setting type.
	 * 
	 * @param String $type Type of setting
	 */
	public static function expire ($type)
	{
		$type = str_replace(array('/','\\'),'',$type);//prevent ability to move to alternate folder
		$filename = '_settings/_'.$type.'.php';
		return geoCache::expire($filename);
	}
	
	/**
	 * Processes a given setting type cache
	 *
	 * @param String $setting_type
	 * @return Settings for given setting type, or false if
	 *  cache miss.
	 */
	public function process ($setting_type='')
	{
		trigger_error('DEBUG STATS CACHE: geoCacheSetting::process('.$setting_type.') - top');
		if (!geoCache::get('cache_setting')){
			trigger_error('DEBUG STATS CACHE: Setting cache turned off, return false.');
			return false;
		}
		//check input:
		if (strlen($setting_type) == 0) {
			trigger_error('DEBUG STATS CACHE: Invalid input!  Cant continue');
			return false;
		}
		trigger_error('DEBUG STATS CACHE: geoCacheSetting::process('.$setting_type.') - 1');
		self::initCacheFilesystem();
		$filename = '_settings/_'.$setting_type.'.php';
		//trigger_error('DEBUG STATS CACHE: geoCacheSetting::process('.$setting_type.') - 2');
		$data = geoCache::inc ($filename);
		if (is_array($data)){
			trigger_error('DEBUG STATS CACHE: geoCacheSetting::process('.$setting_type.') - 3');
			return $data;
		}
		trigger_error('DEBUG STATS CACHE: geoCacheSetting::process('.$setting_type.') - 4<pre>'.htmlspecialchars(print_r($data,1)).'</pre>s');
		return false;
	}
}

/**
 * Cache system specifically tuned to work best with cacheing output of pages and modules
 * 
 * @package System
 * @since Version 3.1.0
 */
class geoCachePage extends geoCache {
	/**
	 * Instance of class
	 * @internal
	 */
	private static $instance;
	
	/**
	 * Array of data for use by the cached page.
	 *
	 * @var Array
	 */
	private $pageData;
	/**
	 * Constructor, private so it isn't created outside of getInstance()
	 */
	private function __construct()
	{
		
	}
	/**
	 * Gets an instance of the cache object.
	 * 
	 * @return geoCachePage
	 */
	public static function getInstance(){
		if (!isset(self::$instance) || !is_object(self::$instance)){
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	/**
	 * Inits the cache file system specific for storeing
	 * page cache.
	 * 
	 * @param bool $force
	 * @return Boolean results of init
	 */
	public static function initCacheFilesystem($force = false){
		static $cache_system = null;
		
		if (!$force && !is_null($cache_system)){
			return $cache_system;
		}
		
		if (!geoCache::get('cache_page')){
			trigger_error('DEBUG STATS CACHE: Page cache turned off, return false.');
			$cache_system = false;
			return false;
		}
		
		if (!parent::initCacheFilesystem()) {
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() failed.');
			$cache_system = false;
			return false;
		}
		
		//directories should now be set up and ready to go.
		$cache_system = true;
		return true;
	}
	
	/**
	 * Add new data to be accessible by the cache, stuff
	 *  like mainbody.
	 *
	 * @param Mixed $data
	 */
	public function register($data){
		if (!geoCache::get('cache_page')){
			trigger_error('DEBUG STATS CACHE: Page cache turned off, return false.');
			return false;
		}
		if (!is_array($data)){
			return false; //error!
		}
		if (count($data) == 0){
			return false; //nothing to register?
		}
		$keys = array_keys($data);
		foreach ($keys as $key){
			$this->pageData[$key] = $data[$key];
		}
	}
		
	/**
	 * Updates the page cache, given the page, language, category,
	 * and logged in status.
	 *
	 * @param Mixed $page_id page id or module replace tag
	 * @param Int $language_id
	 * @param Int $cat_id
	 * @param Boolean $logged_in
	 * @param String $pageCode
	 * @param array $params The array of parameters passed by smarty tag, added in version 7.1.1
	 * @return True if success, false otherwise.
	 */
	public function update ($page_id=0, $language_id=0, $cat_id=0, $logged_in=false, $pageCode='', $params = array())
	{
		trigger_error('DEBUG STATS CACHE: Top of geoCachePage::update'); 
		$language_id = intval($language_id);
		$logged_in = ($logged_in)? 'in': 'out';
		$cat_id = intval($cat_id);
		$params = (array)$params;

		trigger_error('DEBUG STATS CACHE: Top of geoCachePage::update _pages/_p'.$page_id.'l'.$language_id.'c'.$cat_id.'logged_'.$logged_in.'.php');
		
		if (!$page_id || !$language_id) {
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() returned false');
			return false;
		}
		
		if($logged_in === 'in' && geoCache::get('nocache_admin_'.$page_id)) {
			//for this module, we do not use the cache for admin users (to prevent edit/delete buttons from sticking around for others)
			$user_id = geoSession::getInstance()->getUserId();
			$canEdit = geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL) ? true : false;
			$canDelete = geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL) ? true : false;
			if($user_id == 1 || $canEdit || $canDelete) {
				trigger_error('DEBUG STATS CACHE: NOT CACHING {'.$page_id.'} because this is an admin user (update)');
				return false;
			}
		}
		
		$params = $this->serializeParams($params);
		if ($params==='TOO_LONG') {
			//don't use cache for this one...
			trigger_error('DEBUG STATS CACHE: geoCachePage::update - params too long, not caching this one.');
			return false;
		}
		
		$filename = '_pages/_p'.$page_id.'l'.$language_id.'c'.$cat_id.'logged_'.$logged_in.$params.'.php';
		$index = 'age_page_'.$page_id;
		if (geoCache::get($index) === false){
			$index = 'age_page_all';
		}
		if (geoCache::get($index) == -1) return false; //do not cache if -1

		//check for filters
		if (!$this->checkForFilters($page_id)){
			return false;
		}
		
		//make sure the page is allowed to be updated
		
		//initialize cache, in case this is the first time the cache
		//is being used.
		if (!self::initCacheFilesystem()){
			//something ain't right
			trigger_error('DEBUG STATS CACHE: initCacheFilesystem() returned false');
			return false;
		}
		return (geoCache::write($filename,$pageCode,geoCache::get($index)));
	}
	
	/**
	 * Whether or not the given page id should be cached or not.
	 *
	 * @param int|string $page_id Page ID or module replace tag
	 * @return bool
	 */
	public function canCache($page_id)
	{
		$index = 'age_page_'.$page_id;
		if (geoCache::get($index) === false){
			$index = 'age_page_all';
			if (geoCache::get($index)===false) {
				//age page all is false, that probably means cache is off
				return false;
			}
		}
		if (geoCache::get($index) == -1) return false;
		return true;
	}
	
	/**
	 * Checks to see if user is using filters, and if so, checks
	 *  to see if the given page can still use cache when filters
	 *  are in effect.
	 *
	 * @param Mixed $page_id Either page_id or module_replace_tag
	 * @return Boolean true if it is ok to cache the given page taking into
	 *  account any filters, or false otherwise
	 */
	public function checkForFilters($page_id){
		//Do not cache affiliate pages, affiliate system is not compatible with output cache
		if (defined('IN_AFF')){
			return false;
		}
		if (defined('THEME_PRIMARY') || defined('THEME_SECONDARY')) {
			//Using primary/secondary cache, do not use output cache
			return false;
		}
		if (!geoCache::get('cache_page')){
			trigger_error('DEBUG STATS CACHE: Page cache turned off, return false.');
			return false;
		}
		
		$db = DataAccess::getInstance();
		if ($db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere()) {
			//some filter or another is turned on
			return geoCache::get('cache_filter_page_'.$page_id);
		}
		//no filters, 
		return true;
	}
	
	/**
	 * Removes the cache for the given page, language, cat, and logged in
	 *  status.
	 *
	 * @param Mixed $page_id page id or module replace tag
	 * @param Int $language_id
	 * @param Int $cat_id
	 * @param Boolean $logged_in
	 * @return boolean results of expire.
	 */
	public static function expire ($page_id, $language_id=0, $cat_id=0, $logged_in=false)
	{
		//validate input
		trigger_error('DEBUG STATS CACHE: Top of geoCachePage::expire');
		$language_id = intval($language_id);
		$logged_in = ($logged_in)? 'in': 'out';
		
		if (!$page_id || !$language_id) return false;
		$filename = '_pages/_p'.$page_id.'l'.$language_id.'c'.$cat_id.'logged_'.$logged_in.'.php';
		return geoCache::expire($filename);
	}
	
	/**
	 * Process cache, echos or returns the cached page, given the
	 *  page, language, category, and loged in status.
	 *
	 * @param Mixed $page_id page id, or module replace tag
	 * @param Int $language_id
	 * @param Int $cat_id
	 * @param Boolean $logged_in
	 * @param (Optional)Boolean $return if set to true, will return the 
	 *  cache instead of echoing, this should correspond to how the cache
	 *  was stored when using update()
	 * @param array $params The array of parameters passed by smarty tag, added in version 7.1.1
	 * @return Mixed if $return, will return require filename, otherwise will
	 *  return true if a cache hit, false otherwise.
	 */
	public function process ($page_id=0, $language_id=0, $cat_id=0, $logged_in=false, $return = false, $params = array())
	{
		trigger_error('DEBUG STATS CACHE: Top of geoCachePage::process $page_id = '.$page_id);
		//check input:
		if (!geoCache::get('cache_page')){
			trigger_error('DEBUG STATS CACHE: Page cache turned off, return false.');
			return false;
		}
		
		$language_id = intval($language_id);
		$cat_id = intval($cat_id);
		$logged_in = ($logged_in)? 'in': 'out';
		$params = (array)$params;
		
		if (!$page_id || !$language_id) {
			trigger_error('DEBUG STATS CACHE: geoCachePage::process - input check failed.');
			return false;
		}
		
		if($logged_in === 'in' && geoCache::get('nocache_admin_'.$page_id)) {
			//for this module, we do not use the cache for admin users (to prevent edit/delete buttons from sticking around for others)
			$user_id = geoSession::getInstance()->getUserId();
			$canEdit = geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL) ? true : false;
			$canDelete = geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL) ? true : false;
			if($user_id == 1 || $canEdit || $canDelete) {
				trigger_error('DEBUG STATS CACHE: NOT CACHING {'.$page_id.'} because this is an admin user (process)');
				return false;
			}
		}
		
		//check for filters
		if (!$this->checkForFilters($page_id)){
			trigger_error('DEBUG STATS CACHE: geoCachePage::process - checkForFilters returned false.');
			return false;
		}
		
		self::initCacheFilesystem();
		
		$params = $this->serializeParams($params);
		if ($params==='TOO_LONG') {
			//don't use cache for this one...
			trigger_error('DEBUG STATS CACHE: geoCachePage::process - params too long, not caching this one.');
			return false;
		}
		
		$filename = '_pages/_p'.$page_id.'l'.$language_id.'c'.$cat_id.'logged_'.$logged_in.$params.'.php';
		
		//vars available to page
		$addon = geoAddon::getInstance();
		$db = DataAccess::getInstance();
		
		if (!geoCache::file_exists($filename)) {
			return false;
		}
		//have to buffer output if forcing nocache filter_display_page...
		if (is_numeric($page_id) && $addon->coreEventCount('filter_display_page_nocache') > 0) {
			//getting page id is numeric, meaning it's an overall page.  Overall
			//pages like this just echo stuff out, so need to capture output and filter it
			ob_start();
		}
		$result = require CLASSES_DIR . PHP5_DIR . 'Cache.inc.php';
		if ($addon->coreEventCount('filter_display_page_nocache') > 0) {
			if (is_numeric($page_id)) {
				$result = ob_get_contents();
				ob_end_clean();
			}
			
			$result = geoAddon::triggerDisplay('filter_display_page_nocache',$result,geoAddon::FILTER);
		}
		if ($return) {
			return $result;
		}
		//If you do ever want cache to display, make sure it's more than 1 char...
		if (strlen($result) > 1) echo $result;
		
		//this was a cache hit, if we get this far stuff was probably echoed
		//because thats how this type of cache works.
		return true;
	}
	
	/**
	 * Quotes the given text to be used as contents of php cache file, to be
	 * used for update()
	 * 
	 * @param String $pageCode Non quoted string
	 * @return String suitable for use in a php cache file.
	 */
	public static function quotePage($pageCode)
	{
		//filter text
		$pageCode = geoAddon::triggerDisplay('filter_display_page',$pageCode,geoAddon::FILTER);
		
		//to be used as part of return statement, so
		//slash single quotes.
		$pageCode = addcslashes($pageCode, '\'\\');
		
		//the page cache must return the text, so add the return statement
		$pageCode = '<?php return (\''.$pageCode.'\');';
		
		return $pageCode;
	}
}