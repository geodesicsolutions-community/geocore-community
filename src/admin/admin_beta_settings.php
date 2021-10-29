<?php
// admin_beta_settings.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################


class Beta_configuration {
	
	
	var $admin_site;
	var $messages;
	var $db;
	var $settings;
	var $dev_settings;
	
	function initSettings(){
		$this->settings = array();
		$this->dev_settings = array();
		//this is where you specify a new setting.  Syntax is simple:
		//$this->settings['var name'] = 'description';
		
		//switched.
		$this->settings['default_communication_setting'] = 'This controls the default users communication
configuration setting at the time of registration.  The default setting of 1 is the public communication configuration for all new registrants at the time
of registration.  To change to the completely private setting at time of registration for all new clients
change this to 3.  The client can always change their configuration after they have finished
registration.  The only possible setting for this are 1 or 3.
<br><strong>Valid Settings:</strong>
<br>Default Setting = 1
<br>Alternate Setting = 3
<br><strong>Default:</strong> 1
';
		//switched.
		//someone document this one better!  I didn't even know what it is for!
		$this->settings['display_email_invite_black_list'] = '
If set to 1, it displays the email address of user search results within the
black list and invited list features of the client side admin tool.
<br><strong>Valid Settings:</strong>
<br>Set to 1 to turn on.
<br>Leave blank to turn off.
<br><strong>Default:</strong> Off (blank)';
		//switched.
		$this->settings['encode_search_terms'] = '
With the use of some character set encoding of search terms is not needed.  This beta switch
will turn on/off encoding of the search term so that results could be returned using search terms
<br><strong>Valid Settings:</strong>
<br>Set to 1 to encode search terms
<br>Leave blank to turn off, to NOT encode search terms.
<br><strong>Default:</strong> 1';
		$this->settings['number_of_active_ads_to_display'] = '
This is number of active ads to display on the "My active ads" page.  This defaults to the number
of active ads.
<br><strong>Valid Settings:</strong>
<br>A numerical value
<br><strong>Default:</strong> Same # as number of ads.
';
		$this->settings['admin_messaging_send_limit'] = '
This is number of e-mails to add to the e-mail queue at once, when sending mass e-mails using the admin messaging system.
<br><strong>Valid Settings:</strong>
<br>A numerical value, or blank (OFF) to default to 2000
<br><strong>Default:</strong> OFF (which defaults to 2000)
';
		$this->settings['admin_messaging_refresh_delay'] = '
When using the admin messaging tool, if sending to more recipients than specified by admin_messaging_send_limit, they will be added to the e-mail queue in batches.  This setting is the amount of time in seconds between adding each batch of e-mails.
<br><strong>Valid Settings:</strong>
<br>A numerical value, or blank (OFF) to default to 10 Seconds
<br><strong>Default:</strong> OFF (which defaults to 10 seconds)
';
		
		$this->settings['use_textarea_in_title'] = '
If set, will use a text area for the title, instead of the normal input field.
<br /><br /><strong>Valid Settings:</strong>
<br>1 to turn on
<br>Leave blank to not use setting
<br><strong>Default:</strong> Off (blank)';
		
		$this->settings['always_use_user_price_plan_when_renewing'] = 'Within the renewal process the cost
for the renewal is always derived from the price plan the listing itself is attached to.  Setting this to
"1" would force the script to always use the default price plan attached to the user that placed the listing
to derive the cost of the listing renewal.  This only affects the cost of renewal.<br>
<strong>Valid Settings:</strong>
<br>Set to 1 to use the default price plan attached to the user.
<br>Leave blank to turn off and use the price plan attached to the listing.
<br><strong>Default:</strong> Off (blank)
';	
		if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
			$this->settings['disable_browsing_tabs'] = '
			If set, disable browsing tabs.  We ask that you please let us know
			if you are disabling tabs for "usability" reasons rather than "personal preference" reasons,
			so that we may improve how the browsing tabs work based on your feedback.
			<br /><br /><strong>Valid Settings:</strong>
			<br />1 to turn on
			<br />Leave blank to not use setting
			<br /><strong>Default:</strong> Off (blank)';
		}
		
		#@#$$@@@ END OF NORMAL BETA SETTINGS -- ADD NEW BETA SETTINGS ABOVE THIS LINE!!
		
		
		
		
		//UN-DOCUMENTED SETTINGS:  These settings are only meant for internal
		//use, to help in the development of geodesic software.  Well, except
		//for the demo mode, that one is to make it easy to set up a demo.
		
		//NOTE TO GEO-DEVELOPERS:  Do not forget to document these un-documented
		//  settings in the internal docs!  And no, I am not contradicting myself.
		
		//syntax: $this->dev_settings[] = 'setting_name';
		
		//demo mode.
		$this->dev_settings[] = 'demo_mode';
		
		//turn off notifications in admin - not recommended when testing software!
		$this->dev_settings[] = 'developer_supress_notify';
		
		$this->dev_settings[] = 'expand_template_modules';
		$this->dev_settings[] = 'template_module_expando_pants';
		$this->dev_settings[] = 'je_search_setting';
		
		//zip search by city location : does not work with new imported data!
		$this->dev_settings[] = 'zipsearch_by_location_name';
		
		//custom discount codes thingy for joe edwards
		//hijacks email addy sent to auth.net or nochex gateway based on given disc code
		$this->dev_settings[] = 'joe_edwards_discountLink';
				
	}
	
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	/**
	 * Email configuration constructor.  This is responsible for loading the appropriate page, and
	 * then running site->display_page().
	 */
	function Beta_configuration()
	{
		$this->admin_site = Singleton::getInstance('Admin_site');
		$this->db = DataAccess::getInstance();
	} //end of function Site_configuration
	
	function display_beta_general_settings(){
		$this->initSettings();
		if (isset($this->admin_site->site_configuration_message))
				$this->admin_site->body .= "<div class=medium_error_font>\n\t".$this->admin_site->site_configuration_message." \n\t</div>\n";
		
		$this->admin_site->body .= '<div style="text-align: left;">
<form action="" method="POST">
<h3 style="color: red;">Welcome to the Geodesic Beta Feature Set!!</h3>
These settings are for features that <strong>may not be fully functional</strong> 
yet, but are implemented in the software. Please read these notes about settings found in this section:
<ol>
	<li>Most of these settings are here because they are not fully tested yet,
		but we wanted to make them available to you as a client.  A few of these
		settings are here because we have not decided whether to make them part
		of the main application	or not.  These settings are only available to Enterprise level
		products.</li>	
	<li>Any beta settings (or features turned on by these settings) are subject to be changed
		drastically, or even removed in future versions.</li>
	<li><strong>These settings are BETA.</strong>  Changing them might have unexpected consequences,
		and might even break parts of your site.  We welcome feedback and bug reports
		on these features, and will try to help you fix your site if something
		does break, but you must be prepared to restore from a back-up if it comes to that.</li>
	<li>Always take the proper precautions when changing these
		settings.  Back up your database and files regularly.  If on a live site with
		heavy traffic, test the changes on a test site first.</li>
</ol>
<strong>DO NOT CHANGE these settings unless you are willing to use as Beta</strong>.<br><br>
To turn a setting to off, enter the text OFF or leave the field blank.  No input checking is done, it is up to you to make sure you enter the value as specified by the description.<br><br>';
		foreach ($this->settings as $setting => $desc){
			$current_setting = geoString::specialChars($this->db->get_site_setting($setting,1));
			
			$this->admin_site->body .= "<div style=\"border: thin solid black; padding: 5px;\"><strong>$setting</strong> = <input name=\"$setting\" value=\"$current_setting\" /><br>";
			$this->admin_site->body .= $desc."</div><br>\n";
		}
		
		//place to enter random settings.
		if (defined('IAMDEVELOPER')){

			
			$this->admin_site->body .= '<div style="border: thin solid black; padding: 5px;"><strong style="color:red;">INTERNAL USE ONLY!!!</strong>
<br><span class="medium_font">(If you can see this section, Geo Support might be working on your site.  If they are finished working on your site, please notify them that you can still see this section.)</span>
<br><strong>Change un-documented setting:</strong><br>
Setting = <input name="developer_setting" type="text" /><br>
Value = <input name="developer_value" type="text" />
<br><strong>Current Un-Documented setting values:</strong>
';
			foreach ($this->dev_settings as $setting){
				$value = $this->db->get_site_setting($setting);
				if (!$value) $value = 'FALSE (not set)';
				$value=geoString::specialChars($value);
				$this->admin_site->body .= "<br>$setting = $value
";
			}
			$this->admin_site->body .= '
</div><br>';
		}
		$this->admin_site->body .= '
<div style="text-align: center;"><input type="submit" name="auto_save" value="Save Settings" /></div>
</form></div>';
		$this->admin_site->display_page();
	}
	
	function update_beta_general_settings(){
		$this->initSettings();
		if (isset($_POST['developer_setting']) && strlen($_POST['developer_setting']) > 0){
			//save the custom setting.
			$this->settings[$_POST['developer_setting']] = 'No Desc.';
			$_POST[$_POST['developer_setting']] = $_POST['developer_value'];
			
			//let them know how to add the setting...
			$update_text = '
<div style="text-align:left;"><strong>To Add Default Setting To Upgrade:</strong>
';
			$setting = $_POST['developer_setting'];
			$value = (isset($_POST['developer_value']))? addslashes($_POST['developer_value']) : false;
			if (strlen($value)==0 || $value == 'OFF'){
				$value = false;
			}
			if (!$value){
				$update_text .= '
<br><strong>NOT NEEDED</strong>: Settings with default value of false or off do not need to be set in upgrade.';
			}
			else {
				$update_text .= "
<br>In <strong>upgrade/versions/newest_version_folder/main.sql</strong>, add:
<br><textarea rows='5' cols='80'>
INSERT IGNORE INTO `geodesic_site_settings` SET `setting` = '$setting', `value` = '$value';</textarea>
<br>to the file somewhere.  Make sure you are not adding it to a version
that has already been released!  Also be sure that the values are properly escaped.
<br>";
			}
			$update_text .= '</div>';
			$this->admin_site->body .= $update_text;
		}
		foreach ($this->settings as $setting => $desc){
			$value = (isset($_POST[$setting]))? $_POST[$setting] : false;
			if (strlen($value)==0 || $value == 'OFF'){
				$value = false;
			}
			$this->db->set_site_setting($setting, $value,1);
		}
		return true;
		/*if (isset($_POST['developer_setting']) && strlen($_POST['developer_setting']) > 0){
			//save the custom setting.
			$value = (isset($_POST['developer_value']))? $_POST['developer_value'] : false;
			if (strlen($value)==0 || $value == 'OFF'){
				$value = false;
			}
			$this->db->save_site_setting($_POST['developer_setting'], $value);
		}*/
	}
}
