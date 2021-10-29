<?php
//cache_manage.php
/**
 * This is the Geo file cache system.
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
## ##    6.0.7-2-gc953682
## 
##################################

/**
 * Manages the General cache settings
 * 
 */
class AdminCacheManage {
	
	function display_cache_config(){
		geoAdmin::display_page($this->general_settings_form());
	}
	function update_cache_config(){
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();
		
		
		//save cache system on/off setting
		$use_cache = (isset($_POST['use_cache']) && $_POST['use_cache'])? true: false;
		$current = geoCache::get('use_cache');
		
		if ($use_cache != $current){
			//clear all cache when enabling OR disabling,
			//to keep old stuff from staying around, and to make
			//sure changes to cache system are "fixed"
			geoCache::clearCache();
		}
		//turn cache system off
		geoCache::set('use_cache',$use_cache);
		
		//save cache level
		
		$menu_loader->userSuccess('Settings Saved.');
		
		return true;
	}
	
	function display_clear_cache(){
		$this->display_cache_config();
	}
	function update_clear_cache(){
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();
		$txt = 'Cleared ';
		//clear the cache..
		switch($_POST['auto_save']){
			case 'Clear Data Cache':
				geoCache::clearCache('modules');
				geoCache::clearCache('settings');
				geoCache::clearCache('templates');
				geoCache::clearCache('text');
				$txt .= 'Cache for Module/Page Data, Setting Data, Template Data, and Text Data.';
				break;
			case 'Clear Output Cache':
				geoCache::clearCache('fonts');
				geoCache::clearCache('pages');
				$txt .= 'Cache for CSS Font Output, and Module/Page Output.';
				break;
			default:
				geoCache::clearCache();
				$txt .= 'All Cache.';
				break;
		}
		
		$menu_loader->userSuccess($txt);
		
		return true;
	}
	
	function general_settings_form(){
		if( !defined('INCLUDE_PROTOTYPE') ) define( 'INCLUDE_PROTOTYPE', true );
		
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();
		
		$db = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$html = $menu_loader->getUserMessages();
		$html .= $this->get_cache_stats();
	
		return $html;
	}
	function countFiles ($dirname) {
		$dirname = CACHE_DIR . $dirname;
		if (file_exists($dirname)){
			$files = scandir($dirname);
			$skipList = array ('.','..','index.php');
			foreach ($files as $key => $file) {
				if (in_array($file,$skipList) || strpos($file,'.EXPIRE') !== false) {
					unset ($files[$key]);
				}
			}
			
			return count($files);
		}
		return 0;
	}
	function get_cache_stats(){
		$tpl = new geoTemplate('admin');
		
		$row_color[] = geoHTML::adminGetRowColor();
		
		if (GEO_CACHE_STORAGE == 'filesystem'){
			//clean the cache files
			geoCache::cleanUp();
			
			$row_color[] = geoHTML::adminGetRowColor();
			if (!geoCache::is_writable(CACHE_DIR)){
				$geoCache_is_not_writable = 1;
				$row_color[] = geoHTML::adminGetRowColor();
				//still show usage stats, if cache is turned on...
				if (!geoCache::get('use_cache')) 
				{$use_storage_cache = 0;}else {$use_storage_cache = 1;}
			}
			
		
			//count up all the files...
			$countM = $this->countFiles('_modules/');
			$countP = $this->countFiles('_pages/');
			$countS = $this->countFiles('_settings/');
			$countTXT = $this->countFiles('_text/');
			
			$row_color[] = geoHTML::adminGetRowColor();
			$countTOT = $countM + $countP + $countS + $countTXT + $countTOT;
		} elseif (GEO_CACHE_STORAGE == 'memcache') {
			$row_color[] = geoHTML::adminGetRowColor();
			
			if (geoCache::memcache_exists()){
				
				$row_color['memcache_exists'] = geoHTML::adminGetRowColor();
				$memcache_exists = 1;
			}
			else
			{
				$memcache_exists = 0;
			}
				
			$files = geoCache::read('_geoCachedItems');
			
			$countTOT = $countM = $countP = $countS = $countTXT = $countTOT = 0;
			foreach ($files as $filename => $val){
				if (strpos($filename,GEO_MEMCACHE_SETTING_PREFIX.'_modules/') === 0){
					$countM ++;
					continue;
				}
				if (strpos($filename,GEO_MEMCACHE_SETTING_PREFIX.'_pages/') === 0){
					$countP ++;
					continue;
				}
				if (strpos($filename,GEO_MEMCACHE_SETTING_PREFIX.'_settings/') === 0){
					$countS ++;
					continue;
				}
				if (strpos($filename,GEO_MEMCACHE_SETTING_PREFIX.'_text/') === 0){
					$countTXT ++;
					continue;
				}
			}
			$countTOT = $countM + $countP + $countS + $countTXT + $countTOT;
		}
		
		
		$tpl->assign('memcache_exists',$memcache_exists);
		$tpl->assign('geoCache_is_not_writable',$geoCache_is_not_writable);
		$tpl->assign('use_storage_cache',$use_storage_cache);
		$tpl->assign('row_color',$row_color);
		$tpl->assign('GEO_CACHE_STORAGE',GEO_CACHE_STORAGE);
		if (defined('DEMO_MODE')) {
			//display fake location
			$tpl->assign('CACHE_DIR','/var/www/html/demo/_geocache/');
		} else {
			//display real location
			$tpl->assign('CACHE_DIR',CACHE_DIR);
		}
		$tpl->assign('cache_stats',1);
		$tpl->assign('countM',($countM)? $countM:0);
		$tpl->assign('countP',($countP)? $countP:0);
		$tpl->assign('countS',($countS)? $countS:0);
		$tpl->assign('countTXT',($countTXT)? $countTXT:0);
		$tpl->assign('countTOT',($countTOT)? $countTOT:0);
		$tpl->assign('use_cache',geoCache::get('use_cache'));
		
		$txt = $tpl->fetch('cache_manage.general_settings_form.tpl');
		
		
		return $txt;
	}
}
