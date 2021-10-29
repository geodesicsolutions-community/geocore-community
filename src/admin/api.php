<?php
//api.php
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
## ##    16.09.0-83-g57c732f
## 
##################################

require_once CLASSES_DIR . PHP5_DIR . 'API.class.php';

class AdminAPIManage {
	function display_api_keys($display_page = true){
		$db = true;
		include GEO_BASE_DIR.'get_common_vars.php';

		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();

		$base = 'classifieds_url';
		if (strlen($db->get_site_setting('classifieds_ssl_url')) > 0 && $db->get_site_setting('use_ssl_in_sell_process')){
			$base = 'classifieds_ssl_url';
		}
		$api =& Singleton::getInstance('geoAPI');
		$api_url = substr($db->get_site_setting($base),0,strpos($db->get_site_setting($base),$db->get_site_setting('classifieds_file_name')));

		$api_url .= 'geo_api.php';
		if (defined('DEMO_MODE')) {
			$menu_loader->userNotice('Demo Mode: The Remote API system has been disabled on the demo, for security reasons.');
		}
		$master_key = (defined('DEMO_MODE'))? 'MASTER KEY HIDDEN FOR DEMO': $api->getKeyFor();

		$html = $menu_loader->getUserMessages();
		$html .= '<div class="page_note_error">Be sure to read the user manual concerning the information contained on this page.</div>';

		$html .= "
<fieldset>
<div class='form-horizontal form-label-left'>
	<legend>Remote API URL</legend>
	<div class='x_content'>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Remote API URL: </label>
          <div class='col-md-6 col-sm-6 col-xs-12' style=\"border: thin solid black; font-size: 0.8em; padding: 1px; padding-left: 5px; margin-top: 6px;\">
          	$api_url
          </div>
        </div>

	</div>
</div>
</fieldset>

<div class='clearColumn'></div>

<fieldset>
<div class='form-horizontal form-label-left'>
	<legend>Remote API Security Keys</legend>

	<div class='x_content'>

		<div class=\"header-color-primary-mute\">Master Key - Will Work for Any API Call</div>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Master API Key: </label>
          <div class='col-md-6 col-sm-6 col-xs-12' style=\"border: thin solid black; font-size: 0.8em; padding: 1px; padding-left: 5px; margin-top: 6px;\">
          	".$master_key."
          </div>
        </div>

		<div class=\"header-color-primary-mute\">Keys to use Specific API Calls</div>


        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Remote API Call Name: </label>
          <div class='col-md-6 col-sm-6 col-xs-12' style=\"border: thin solid black; font-size: 0.8em; padding: 1px; padding-left: 5px; margin-top: 6px;\">
          	Key for Remote API Call - will only work for specific call
          </div>
        </div>

	</div>
	";
		
		$callbacks = $api->getCallBacks();
		$methods = array_keys($callbacks);
		$row = 'row_color1';
		foreach ($methods as $methodname){
			$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
			$master_key = (defined('DEMO_MODE'))? $methodname.' KEY HIDDEN FOR DEMO': $api->getKeyFor($methodname);
			$html .= "


	<div class='x_content'>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>$methodname: </label>
          <div class='col-md-6 col-sm-6 col-xs-12' style=\"border: thin solid black; font-size: 0.8em; padding: 1px; padding-left: 5px; margin-top: 6px;\">
          	".$master_key."
          </div>
        </div>

	</div>";
		}
		
		$html .= "
	</div>

    </fieldset><div class='clearColumn'></div>";


		geoAdmin::display_page($html);
	}
}