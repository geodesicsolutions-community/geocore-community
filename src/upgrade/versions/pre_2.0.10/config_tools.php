<?php
/**
 * For help with updating the config.php file.
 */

class config_tools {
	function config_tools(){
		
	}
	/*
	 * Checks the config file to make sure it is up to date.
	 * Returns true if config appears to be up to date.
	 */
	function check_config_file(){
		//they seem to have needed newer vars, so check the xss files.
		return true;
	}
	/**
	 * Makes sure the old xss_filter_inputs.php file is not included.
	 */
	function check_xss_compat(){
		include_once('../../../config.default.php');
		if (!function_exists('get_included_files')){
			//cant find if it is included, so just return true.
			return true;
		}
		
		$included_files = @get_included_files();
		if (!isset($included_files) || !is_array($included_files)){
			//something went wrong, just pretend everything is ok
			return true;
		}
		$xss_check_files = true;
		
		//make sure not using the old xss_filter_inputs.php anywhere.
		foreach ($included_files as $filename){
			if (strpos($filename, 'xss_filter_inputs.php')){
				//var_dump ($filename);
				//The file xss_filter_inputs.php is included, and that isn't good...
				$xss_check_files = false;
			}
		}
		
		return $xss_check_files;
	}
	
	/**
	 * Generates a new config.php using var settings from the original config.php 
	 * and the default config.default.tpl template.
	 * @param Boolean $only_use_db_settings if set to true, will ignore all settings
	 *  except for database settings from the current config.php file.
	 */
	function generate_updated_config($only_use_db_settings = false){
		include ('../../../config.php');
		$use_extra_settings = !$only_use_db_settings;
		
		$settings = array (
			'<<db_host>>' => (isset($db_host)? $db_host : 'your_database_hostname'),
			'<<db_username>>' => (isset($db_username) ? $db_username : 'your_database_user'),
			'<<db_password>>' => (isset($db_password) ? $db_password : 'your_database_password'),
			'<<database>>' => (isset($database) ? $database : 'your_database_name'),
			'<<persistent_connections_comment>>' => (isset($persistent_connections) ? '' : '//'),
			'<<persistent_connections>>' => (isset($persistent_connections) ? $persistent_connections : '1'),
			'<<api_db_host>>' => (($use_extra_settings && isset($api_db_host)) ? $api_db_host : ''),
			'<<api_db_username>>' => (($use_extra_settings && isset($api_db_username)) ? $api_db_username : ''),
			'<<api_db_password>>' => (($use_extra_settings && isset($api_db_password)) ? $api_db_password : ''),
			'<<api_database>>' => (($use_extra_settings && isset($api_database)) ? $api_database : ''),
			'<<product_type>>' => (($use_extra_settings && isset($product_type)) ? $product_type : '4'),
			'<<MUST_HAVE_SUBSCRIPTION_TO_VIEW_AD_DETAIL>>' => (($use_extra_settings && defined ('MUST_HAVE_SUBSCRIPTION_TO_VIEW_AD_DETAIL')) ? MUST_HAVE_SUBSCRIPTION_TO_VIEW_AD_DETAIL : '0'),
			'<<DEFAULT_COMMUNICATION_SETTING>>' => (($use_extra_settings && defined ('DEFAULT_COMMUNICATION_SETTING')) ? DEFAULT_COMMUNICATION_SETTING : '1'),
			'<<ALLOW_BIDDING_AGAINST_SELF>>' => (($use_extra_settings && defined ('ALLOW_BIDDING_AGAINST_SELF')) ? ALLOW_BIDDING_AGAINST_SELF : '0'),
			'<<ALLOW_COPYING_NEW_LISTING>>' => (($use_extra_settings && defined ('ALLOW_BIDDING_AGAINST_SELF')) ? ALLOW_BIDDING_AGAINST_SELF : '0'),
			'<<SECURE_REGISTRATION>>' => (($use_extra_settings && defined ('SECURE_REGISTRATION')) ? SECURE_REGISTRATION : '1'),
			'<<SECURE_LOGIN>>' => (($use_extra_settings && defined ('SECURE_LOGIN')) ? SECURE_LOGIN : '1'),
			'<<SECURE_MESSAGING>>' => (($use_extra_settings && defined ('SECURE_MESSAGING')) ? SECURE_MESSAGING : '1'),
			'<<CS_MIN>>' => (($use_extra_settings && defined ('CS_MIN')) ? CS_MIN : '65'),
			'<<CS_MAX>>' => (($use_extra_settings && defined ('CS_MAX')) ? CS_MAX : '90'),
			'<<DESC_BOX_WIDTH>>' => (($use_extra_settings && defined ('DESC_BOX_WIDTH')) ? DESC_BOX_WIDTH : '0'),
			'<<DESC_BOX_HEIGTH>>' => (($use_extra_settings && defined ('DESC_BOX_HEIGTH')) ? DESC_BOX_HEIGTH : '0'),
			'<<VIEW_EMAIL_AFTER_AUCTION_OVER>>' => (($use_extra_settings && defined ('VIEW_EMAIL_AFTER_AUCTION_OVER')) ? VIEW_EMAIL_AFTER_AUCTION_OVER : '0'),
			'<<USE_TEXTAREA_IN_TITLE>>' => (($use_extra_settings && defined ('USE_TEXTAREA_IN_TITLE')) ? USE_TEXTAREA_IN_TITLE : '0'),
			'<<ALLOWED_IPS_WHEN_SITE_DISABLED>>' => (($use_extra_settings && defined ('ALLOWED_IPS_WHEN_SITE_DISABLED')) ? ALLOWED_IPS_WHEN_SITE_DISABLED : ''),
			'<<DISPLAY_DESCRIPTION_LAST_IN_FORM>>' => (($use_extra_settings && defined ('DISPLAY_DESCRIPTION_LAST_IN_FORM')) ? DISPLAY_DESCRIPTION_LAST_IN_FORM : '0'),
			'<<DISPLAY_EMAIL_INVITE_BLACK_LIST>>' => (($use_extra_settings && defined ('DISPLAY_EMAIL_INVITE_BLACK_LIST')) ? DISPLAY_EMAIL_INVITE_BLACK_LIST : '0'),
			'<<ENCODE_SEARCH_TERMS>>' => (($use_extra_settings && defined ('ENCODE_SEARCH_TERMS')) ? ENCODE_SEARCH_TERMS : '1')
		);
		
		$contents = file_get_contents('config.default.tpl');
		
		//replace the template vars with the correct values.
		foreach ($settings as $key => $value){
			$contents = str_replace($key, $value, $contents);
		}
		if ($this->check_config_file()){
			$contents = preg_replace('/\/\*
The following are controls for beta testing features[^}]+}/','',$contents);
		}
		//if config is valid except for beta switches, remove the beta switches.
		return $contents;
	}
	
	function update_beta_settings(){
		//to avoid complications, do not automatically import beta settings.	
		return true;
		if (!$this->check_config_file()){
			//don't update if config file is not up to date.
			return false;
		}
		if (defined('BETA_SWITCHES')){
			//get a $db connection.
			include ('../../../app_top.common.php');
			if (!$db->get_site_setting('beta_switches_updated')){
				//update the settings in the admin with the settings from
				//the config file.
				include_once ('../../../admin/admin_beta_settings.php');
				$beta_config = new Beta_configuration();
				$beta_config->initSettings();
				foreach ($beta_config->settings as $setting => $desc){
					if (defined(strtoupper($setting))){
						$value = constant(strtoupper($setting));
						if (isset($value) && $value && strlen($value)){
							//only add the setting if the constant evaluates to not false-like.
							$db->set_site_setting($setting, $value);
						}
					}
				}
				$db->set_site_setting('beta_switches_updated',1);
			}
		} else {
			//they must have an updated config.php file.
			include ('../../../app_top.common.php');
			
			if (!$db->get_site_setting('beta_switches_updated')){
				//set the defaults, for items that have a default other than off.
				//DEFAULT BETA SWITCHES
				$db->set_site_setting('default_communication_setting',1);
				$db->set_site_setting('secure_login',1);
				$db->set_site_setting('secure_messaging',1);
				$db->set_site_setting('secure_registration',1);
				$db->set_site_setting('cs_min',65);
				$db->set_site_setting('cs_max',90);
				$db->set_site_setting('encode_search_terms',1);
					
			}
		}
		if (defined('BETA_SWITCHES')){
			return false;
		}
		return true;
	}
}