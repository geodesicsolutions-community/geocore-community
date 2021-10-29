<?php
//addons/geographic_navigation/tags.php
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
## ##    17.01.0-18-g45cdc81
## 
##################################

//Extends info class, double check to make sure it is included already
require_once ADDON_DIR . 'geographic_navigation/info.php';

class addon_geographic_navigation_tags extends addon_geographic_navigation_info
{
	private function _checkBrowsingEnabled ()
	{
		$util = geoAddon::getUtil($this->name);
		return $util->checkBrowsingEnabled();
	}
	
	private function _addMainTop ()
	{
		$util = geoAddon::getUtil($this->name);
		$util->addMainTop();
		return '';
	}
	
	public function insert_head_auto_add_head ()
	{
		return $this->_addMainTop();
	}
	
	public function insert_head ()
	{
		//work done in auto add head thingy
		return '';
	}
	
	public function current_region ($params, Smarty_Internal_Template $smarty)
	{
		$region_id = geoView::getInstance()->geographic_navigation_region;
		
		if (!$region_id) {
			//nothing currently selected
			return '';
		}
		$util = geoAddon::getUtil($this->name);
		
		$label = $util->getLabelFor($region_id);
		
		if (isset($params['assign'])) {
			$smarty->assign($params['assign'], $label);
			return '';
		}
		
		return $label;
	}
	
	public function breadcrumb_auto_add_head ()
	{
		//breadcrumb requires stuff to be inserted in head
		$this->_addMainTop();
		return '';
	}
	
	public function breadcrumb ($params, Smarty_Internal_Template $smarty)
	{
		if (!$this->_checkBrowsingEnabled()) {
			//browsing disabled, no breadcrumb to display
			return '';
		}
		
		$reg = geoAddon::getRegistry('geographic_navigation');
		$view = geoView::getInstance();
		$util = geoAddon::getUtil('geographic_navigation');
		
		$tpl_vars = array();
		
		$region_id = (isset($_COOKIE['region']))? trim($_COOKIE['region']) : 0;
		if(!$region_id) {
			//no region explicitly set -- if there is only one top-level region, assume that
			$db = DataAccess::getInstance();
			if($db->GetOne("SELECT COUNT(id) FROM ".geoTables::region." WHERE `level` = 1 AND `enabled` = 'yes'") == 1) {
				$region_id = $db->GetOne("SELECT `id` FROM ".geoTables::region." WHERE `level` = 1 AND `enabled` = 'yes'");
			} else {
				//not enough specifity to show a breadcrumb
				return '';
			}
		}
		
		$tpl_vars['breadcrumb'] = $util->getBreadcrumbFor($region_id);
		$tpl_vars['base_url'] = $util->getBaseUrl();
		$tpl_vars ['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'breadcrumb.tpl',
				geoTemplate::ADDON, $this->name, $tpl_vars);
	}
	
	public function navigation_auto_add_head ()
	{
		//unlike others, navigation only needs CSS file inserted.
		geoView::getInstance()->addCssFile(geoTemplate::getURL('css','addon/geographic_navigation/navigation.css'));
	}
	
	public function navigation ($params, $smarty, $topLevel = false)
	{
		if (!$this->_checkBrowsingEnabled()) {
			//browsing disabled, no navigation to display
			return '';
		}
		$reg = geoAddon::getRegistry('geographic_navigation');
		$view = geoView::getInstance();
		$util = geoAddon::getUtil('geographic_navigation');
		
		$tagName = ($topLevel) ? 'navigation_top' : 'navigation';
		
		$tpl_vars = array();
		
		$hideEmpty = $reg->hideEmpty;
		if (isset($params['hideEmpty'])) {
			//let hideEmpty be over-written by smarty tag parameter
			$hideEmpty = (bool)$params['hideEmpty'];
		}
		
		//get those things sorted!
		$regionId = ($topLevel===false && isset($_COOKIE['region']))? trim($_COOKIE['region']) : 0;
		
		$all = $util->getChildrenFor($regionId, $reg->terminalSiblings);
		if (!$regionId) {
			while(count($all) == 1) {
				//only one at this level, so go ahead and default to selecting it
				//(and repeat until there's a level with more than one region (or we run out of levels))
				$_COOKIE['region'] = $regionId = $all[0]['id'];
				$all = $util->getChildrenFor($regionId);
			}
		}
		
		//if narrowing count by category, get the current category
		$category = (isset($params['use_cat_counts']) && $params['use_cat_counts'])? $view->getCategory() : 0;
		
		$regionCount = $topCount = 0;
		$regions = array();
		
		foreach ($all as $row) {
			if ($reg->countFormat || $hideEmpty) {
				$row['listing_counts'] = $util->getListingCounts($row['id'], $category, $hideEmpty);
				if ($hideEmpty && array_sum($row['listing_counts']) <= 0) {
					//count is 0 and set to hide empty regions...
					continue;
				}
			}
			if ($reg->showSubs) {
				//get sub-regions
				$row['sub_regions'] = $util->getChildrenFor($row['id']);
				if ($row['sub_regions']) {
					$regionCount += count($row['sub_regions']);
					$row['subregion_count'] = count($row['sub_regions']);
				}
			}
				
			$regions[] = $row;
			$regionCount++;
			$topCount++;
		}
		
		if($regionCount <= 0) {
			//no regions to show. return here to prevent division-by-zero in the template
			return '';
		}
		
		$tpl_vars['regions'] = $regions;
		$tpl_vars['columns'] = $reg->columns;
		$tpl_vars['regionCount'] = $regionCount;
		$tpl_vars['topCount'] = $topCount;
		$tpl_vars['showSubs'] = $reg->showSubs;
				
		//always get the breadcrumb so it can show based on template tag var
		$tpl_vars['breadcrumb'] = $util->getBreadcrumbFor($regionId);
		$tpl_vars['current_region'] = $regionId;
		$tpl_vars['countFormat'] = $reg->countFormat;
		$tpl_vars['domain'] = $util->getDomain(true).'/'.DataAccess::getInstance()->get_site_setting('classifieds_file_name').rtrim($this->_getBaseUrl(true),'?&');
		$tpl_vars['base_url'] = $this->_getBaseUrl();
		
		$tpl_vars['subdomains'] = ($reg->subdomains == 'on');
		
		$tpl_vars['tree'] = $reg->tree;
		
		$tpl_vars ['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'navigation/standard.tpl',
				geoTemplate::ADDON, $this->name, $tpl_vars);
	}
	
	public function navigation_top_auto_add_head ()
	{
		return $this->navigation_auto_add_head();
	}
	
	public function navigation_top ($params, $smarty)
	{
		return $this->navigation($params, $smarty, true);
	}
	
	public function change_region_link_auto_add_head ()
	{
		$this->_addMainTop();
		return '';
	}
	
	public function change_region_link ($params, Smarty_Internal_Template $smarty)
	{
		$reg = geoAddon::getRegistry('geographic_navigation');
		$view = geoView::getInstance();
		
		$tagName = 'change_region_link';
		
		$tpl_vars = array('msgs' => geoAddon::getText($this->auth_tag, $this->name));
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'tags/change_region_link.tpl',
				geoTemplate::ADDON, $this->name, $tpl_vars);
	}
	
	public function listing_regions ($params, Smarty_Internal_Template $smarty)
	{
		if (!$this->_checkBrowsingEnabled()) {
			//browsing disabled, shouldn't be able to show this
			return '';
		}
		//figure out the listing ID
		$tpl_vars = array();
		
		//see if listing ID is being passed in... (to allow working as {listing} tag)
		$listingId = (isset($params['listing_id']))? (int)$params['listing_id'] : 0;
		
		if (!$listingId) {
			//allow working as a normal {addon} tag
			$view = geoView::getInstance();
			if (!$view->classified_id) {
				//id NOT set
				return '';
			}
			$listingId = (int)$view->classified_id;
		}
		$util = geoAddon::getUtil($this->name);
		$regions = array();
		$session_vars = geoListingDisplay::getSessionVars();
		
		if ($session_vars) {
			//must be a preview...  not active yet
			if ($session_vars['geographic_navigation_addon']) {
				foreach ($session_vars['geographic_navigation_addon'] as $region) {
					$regions[] = $util->getLabelFor($region);
				}
			}
		} else {
			$regions = $util->getRegionsForDisplay($listingId);
		}
		$tpl_vars['regions'] = $regions;
		
		return geoTemplate::loadInternalTemplate($params, $smarty, 'listing_regions.tpl',
			geoTemplate::ADDON, $this->name, $tpl_vars);
	}
	
	private function _getBaseUrl ($queryOnly = false)
	{
		$db = DataAccess::getInstance();
		$skip_list = array('region','subregion');
		$parts = array();
		foreach ($_GET as $key => $value) {
			if (!in_array($key,$skip_list)) {
				if (is_array($value)) {
					foreach ($value as $sub_i => $sub_v) {
						$parts[] = "{$key}[{$sub_i}]=$sub_v";
					}
				} else {
					$parts[] = "$key=$value";
				}
			}
		}
		$base = $db->get_site_setting('classifieds_url');
		if ($queryOnly) {
			$base = '';
		}
		if (count($parts)) {
			return $base.'?'.implode("&",$parts)."&";
		}
		return $base.'?';
	}
}