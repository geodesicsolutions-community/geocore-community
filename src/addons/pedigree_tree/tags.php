<?php
//addons/pedigree_tree/tags.php
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
## ##    7.1beta1-836-g9f10868
## 
##################################

# Pedigree Tree
require_once ADDON_DIR . 'pedigree_tree/info.php';

class addon_pedigree_tree_tags extends addon_pedigree_tree_info
{
	public function listing_tree_auto_add_head ()
	{
		geoView::getInstance()->addCssFile(geoTemplate::getURL('css','addon/pedigree_tree/tree.css'));
	}
	public function listing_tree ($params, Smarty_Internal_Template $smarty)
	{
		$reg = geoAddon::getRegistry($this->name);
		
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
		$session_vars = geoListingDisplay::getSessionVars();
		$util = geoAddon::getUtil($this->name);
		if ($session_vars) {
			$tree = $session_vars['pedigreeTree'];
			$tree['maxGen'] = $util->getMaxGen($tree);
		} else {
			$tree = $util->getTreeFor($listingId);
		}
		
		if (!$tree['maxGen']) {
			//nothing to show
			return '';
		}
		$tpl_vars = array();
		$tpl_vars['maxGen'] = (int)$tree['maxGen'];
		$tpl_vars['currentGen']=1;
		$tpl_vars['gender'] = 'sire';
		$tpl_vars['data'] = $tree;
		$tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
		$tpl_vars['show_label'] = ($tpl_vars['msgs']['sire'] && $tpl_vars['msgs']['dam'] && $tpl_vars['msgs']['sires'] && $tpl_vars['msgs']['dams']);
		
		//get url's for icon set
		$tpl_vars['iconSet'] = $reg->iconSet;
		if ($reg->iconSet && $reg->iconSet != 'none') {
			$tpl_vars['icon_sire'] = "images/addon/pedigree_tree/icon_sets/{$reg->iconSet}/sire.gif";
			$tpl_vars['icon_dam'] = "images/addon/pedigree_tree/icon_sets/{$reg->iconSet}/dam.gif";
		}
		
		$tplFile = ($tpl_vars['maxGen'] > 4)? 'listing_details/tree_unlimited.tpl': 'listing_details/tree.tpl';
		
		return geoTemplate::loadInternalTemplate($params, $smarty, $tplFile,
				geoTemplate::ADDON, $this->name, $tpl_vars);
	}
}