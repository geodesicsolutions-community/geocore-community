<?php
//addons/twitter_feed/admin.php
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
## ##    7.6.3-30-gaab9755
## 
##################################

# twitter_feed Addon

class addon_twitter_feed_admin extends addon_twitter_feed_info {
	
	public function init_text($language_id) {
		
		$return_var['cart_title'] = array (
			'name' => 'Cart Title',
			'desc' => 'Label of Twitter Feed item in the Cart display',
			'type' => 'input',
			'default' => 'Twitter Feed',
		);
		$return_var['widget_code_label'] = array (
			'name' => 'Widget Code Input Label',
			'desc' => 'labels the Widget Code entry field',
			'type' => 'input',
			'default' => 'Twitter Widget Code',
		);
		$return_var['widget_help_box'] = array (
			'name' => 'Widget Help Box',
			'desc' => 'holds instructions for entering the widget code',
			'type' => 'textarea',
			'default' => '<ol><li>Log into your account on <a href="https://twitter.com">twitter.com</a></li>
							<li>Create a widget at <a href="https://twitter.com/settings/widgets">https://twitter.com/settings/widgets</a></li>
							<li>Copy and Paste the HTML code from the box on the widget creator page into this form</li></ol>',
		);
		$return_var['edit_sub_title'] = array (
			'name' => 'Sub-title for Edit Listing page',
			'desc' => '',
			'type' => 'input',
			'default' => 'Twitter Feed',
		);
		$return_var['edit_submit_button_text'] = array (
			'name' => 'Submit button on Edit Listing page',
			'desc' => '',
			'type' => 'input',
			'default' => 'Next &rsaquo;&rsaquo;',
		);
		$return_var['edit_cancel_text'] = array (
			'name' => 'Cancel button on Edit Listing page',
			'desc' => '',
			'type' => 'input',
			'default' => 'Cancel Listing Edit',
		);
		$return_var['default_link_label'] = array (
			'name' => 'Default Link Label',
			'desc' => 'Text of the link to Twitter that appears if the Timeline script fails',
			'type' => 'input',
			'default' => 'See more on Twitter.com',
		);
		$return_var['loading_message'] = array (
			'name' => 'Loading Message',
			'desc' => 'Shown while the server validates and parses widget code inputs',
			'type' => 'input',
			'default' => 'Loading...Please Wait!',
		);
		$return_var['saved_message'] = array (
			'name' => 'Saved notification',
			'desc' => 'Shown to indicate that the code input has parsed correctly',
			'type' => 'input',
			'default' => 'Widget Code Saved!',
		);
		$return_var['release_btn'] = array (
			'name' => 'Release Button',
			'desc' => 'Used to release saved widget data',
			'type' => 'input',
			'default' => 'Release',
		);
		$return_var['parse_error'] = array (
			'name' => 'Parse Error',
			'desc' => 'Shown when the widget code a user has entered is invalid',
			'type' => 'input',
			'default' => 'Sorry, we didn\'t recognize that widget code. Please try again.',
		);
		$return_var['edit_step_button_widget'] = array (
			'name' => 'Edit Step Selection Button',
			'desc' => 'Text for the "edit twitter name" button during the listing edit process',
			'type' => 'input',
			'default' => 'Edit Twitter Widget',
		);
		$return_var['edit_step_label_widget'] = array (
			'name' => 'Edit Step Breadcrumb Label',
			'desc' => '',
			'type' => 'input',
			'default' => 'Twitter Widget',
		);
		$return_var['edit_desc_widget'] = array (
			'name' => 'Description for Edit Listing page',
			'desc' => '',
			'type' => 'input',
			'default' => 'Enter the Twitter Widget Code for the timeline you wish to show on this listing',
		);
		return $return_var;
	}
	
	
	public function init_pages () {
		menu_page::addonAddPage('addon_twitter_feed_settings','','Settings',$this->name,$this->icon_image);		
	}
	
	public function display_addon_twitter_feed_settings()
	{
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		$v = $admin->v();		
		$reg = geoAddon::getRegistry('twitter_feed');
		$v->config = $reg->config;
		$v->adminMsgs = geoAdmin::m();		
		$v->setBodyTpl('admin/admin_settings.tpl','twitter_feed');
	}
	
	public function update_addon_twitter_feed_settings()
	{
		$data = $_REQUEST['d'];
		
		//sort chrome string
		$chrome = array();
		foreach($data['chrome'] as $key => $cr) {
			if($cr == 1) {
				$chrome[$key] = $key;
			}
		}
		$chrome_string = $chrome ? implode(' ', $chrome) : false;
		
		$config = array(
			'tweet_limit' => $data['tweet_limit'],
			'width' => $data['width'],
			'height' => $data['height'],
			'link_color' => $data['link_color'],
			'border_color' => $data['border_color'],			
			'chrome' => $chrome,
			'chrome_string' => $chrome_string,
			'default_href' => $data['default_href'],
			'default_data_id' => $data['default_data_id'],
		);
		$reg = geoAddon::getRegistry('twitter_feed');
		$reg->config = $config;
		$reg->save();
		return true;
	}
}