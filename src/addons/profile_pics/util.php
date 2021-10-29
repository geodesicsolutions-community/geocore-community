<?php
//addons/profile_pics/util.php
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
## ##    16.09.0-81-gdb71652
## 
##################################

require_once ADDON_DIR . 'profile_pics/info.php';

class addon_profile_pics_util extends addon_profile_pics_info {

	public function showUploader($user_id=0)
	{
		if(!$user_id) {
			$user_id = geoSession::getInstance()->getUserId();
		}
		$pre = defined('IN_ADMIN') ? "../" : "";
		$view = geoView::getInstance();
		$view->addCssFile($pre.geoTemplate::getUrl('css','addon/profile_pics/croppie.css'));
		$view->addJScript(array($pre.geoTemplate::getUrl('js','addon/profile_pics/croppie.js'), $pre.geoTemplate::getUrl('js','addon/profile_pics/exif.js')));
		
		
		$db = DataAccess::getInstance();
		
		$picData = $db->GetOne("SELECT `pic_data` FROM `geodesic_addon_profile_pics` WHERE `user_id` = ?", array($user_id));
		if(!$picData || !strlen($picData)) {
			//no saved profile pic? use the default
			$picData = $pre.geoTemplate::getUrl('images','icons/User-Profile-300.png');
		}
		
		
		$tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
		$tpl_vars = array();
		
		$tpl_vars['original_profile_pic'] = $picData;
		$tpl_vars['spinner'] = $pre.geoTemplate::getUrl('images','loading.gif');
		$tpl_vars['msgs'] = geoAddon::getText($this->auth_tag, $this->name);
		$tpl_vars['user_id'] = $user_id;
		
		$reg = geoAddon::getRegistry($this->name);
		$tpl_vars['viewport_width'] = $reg->viewport_width;
		$tpl_vars['viewport_height'] = $reg->viewport_height;
		$tpl_vars['boundary_width'] = $reg->boundary_width;
		$tpl_vars['boundary_height'] = $reg->boundary_height;
		
		
		$tpl->assign($tpl_vars);
		return $tpl->fetch('uploader.tpl');
	}
	
	public function core_User_management_information_display_user_data()
	{
		$msgs = geoAddon::getText($this->auth_tag, $this->name);
		$return = array(
			'label' => $msgs['my_account_uploader_label'],
			'value' => $this->showUploader()
		);
		return $return;
	}

	public function core_Admin_site_display_user_data($user_id)
	{
		/* 
		 * the original plan was to call $this->showUploader($user_id) here
		 * but it doesn't seem to play nice with the admin
		 * so instead, just show the pic and a link for the admin to delete it if needed
		 */
		
		$html = $this->get_img_tag($user_id);
		$removeLink = "<br /><a href='#' onclick=\"jQuery.post('../AJAX.php?controller=addon_profile_pics&action=savePic',{pic_data: '', user_id: {$user_id}}, function(){jQuery('.profile-pic-admin-preview').prop('src','{$default}');}); return false;\">[Remove]</a>";
		
		if(defined('DEMO_MODE')) {
			//no remove link on the main demo!
			$removeLink = '';
		}
		
		return geoHTML::addOption("Profile Picture$removeLink<br />(Log into front-end to edit)",$html);
	}
	
	public function get_img_tag($user_id)
	{
		$db = DataAccess::getInstance();
		
		$default = "../".geoTemplate::getUrl('images','icons/User-Profile-300.png');
		$picData = $db->GetOne("SELECT `pic_data` FROM `geodesic_addon_profile_pics` WHERE `user_id` = ?", array($user_id));
		if(!$picData || !strlen($picData)) {
			//no saved profile pic? show the default
			$picData = $default;
		}
		return "<img src='$picData' class='profile-pic-admin-preview' />";
	}
}