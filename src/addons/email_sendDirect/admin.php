<?php
//addons/email_sendDirect/admin.php
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
## ##    17.10.0-14-gb801f95
## 
##################################

# Email Send Direct Addon (Main email sender)

class addon_email_sendDirect_admin extends addon_email_sendDirect_info {

	public $admin_site;
	public $messages;

	/**
	 * Email configuration constructor.  This is responsible for loading the appropriate page, and
	 * then running site->display_page().
	 */
	public function __construct()
	{
		if (Singleton::isInstance('Admin_site')){
			$this->admin_site = Singleton::getInstance('Admin_site');
		} else { //if we cant find the admin site object, we cant do squat!
			return false;
		}
		$this->messages['error_no_host']= "Error:  No SMTP host name given.  The host name is required for SMTP connections.  If you are unsure what the SMTP host name is, contact your host provider, or use the \"Standard Connection\". ";
	}


	//function to initialize pages, to let the page loader know the pages exist.
	//this will only get run if the addon is installed and enabled.
	public function init_pages () {
		//take over the email general pages.
		menu_page::addonAddPage('email_general_config','email_setup','General Email Settings','email_sendDirect',$this->icon_image,'main_page',true);
	}
	/**
	 * Display general settings for email
	 */
	public function display_email_general_config(){
		//get the instance of the db.
		$db = $admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		//add the tooltips javascript page
		$html = $admin->getUserMessages();
		$row = 'row_color2';
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		//email server settings
		$html .= '<form action="index.php?mc=email_setup&page=email_general_config" method="post" class="form-horizontal form-label-left">
<fieldset>
<legend>Email Method Used</legend>
<table align="center" border="0" cellpadding="3" cellspacing="0" width="100%">
<tbody>';
		//email config settings

		//if standard email options are disabled
		if ($db->get_site_setting('email_server_type')=='sendmail'){
			$smtp_disabled=' disabled=true';
			$smtp_email_style = 'class="disabled_text"';
			$sendmail_checked = 'checked="checked" ';
			$mail_checked = ' ';
			$smtp_checked = ' ';
		} elseif ($db->get_site_setting('email_server_type')=='mail') {
			$smtp_disabled=' disabled=true';
			$smtp_email_style = 'class="disabled_text"';
			$sendmail_checked = ' ';
			$mail_checked = 'checked="checked" ';
			$smtp_checked = ' ';
		} else {
			$smtp_disabled = '';
			$smtp_email_style = 'class="enabled_text"';
			$sendmail_checked = ' ';
			$mail_checked = ' ';
			$smtp_checked = 'checked="checked" ';
		}

		$standard_email = "<tr class=".$row."><td colspan=\"2\" class=\"medium_font\"><label><input type=\"radio\" name=\"email_server_type\" id=\"email_server_type_sendmail\" class='mail-method' value=\"sendmail\"$sendmail_checked";

		$standard_email .= " /> <strong>SendMail Method</strong>".$this->admin_site->show_tooltip(2,1)."</label></td></tr>
";
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		$smtp_email = "<tr class=".$row."><td colspan=\"2\" class=\"medium_font\">
						<label><input type=\"radio\" name=\"email_server_type\" id=\"email_server_type_smtp\" class='mail-method' value=\"smtp_\" $smtp_checked/>
						<strong>SMTP Server Connection Method</strong>".$this->admin_site->show_tooltip(3,1)."</label></td></tr>";
		$smtp_host=$db->get_site_setting('email_SMTP_server');
		$smtp_port=$db->get_site_setting('email_SMTP_port');
		$detected_host = ini_get('SMTP');
		$detected_port = ini_get('smtp_port');
		
		//figure out which security setting to check.
		switch ($db->get_site_setting('email_server_type')) {
			case 'smtp_auth_standard':
			case 'smtp_standard':
			case 'sendmail':
				//no encryption
				$none_checked = ' checked=true';
				$tls_checked = '';
				$ssl_checked = '';
				break;
			case 'smtp_auth_tls':
			case 'smtp_tls':
				//tls encryption
				$none_checked = '';
				$tls_checked = ' checked=true';
				$ssl_checked = '';
				break;
			default:
				//ssl encryption
				$none_checked = '';
				$tls_checked = '';
				$ssl_checked = ' checked=true';
		}
		
		if ($db->get_site_setting('email_server_type')== 'smtp_auth_standard'||$db->get_site_setting('email_server_type')=='smtp_auth_tls'||$db->get_site_setting('email_server_type')=='smtp_auth_ssl'){
			//$smtp_email_checked .= 'checked=true ';
			$user_pass_checked = ' checked=true';
		} else{
			$user_pass_checked = '';
		}
		
		$smtp_email .= "<tr id='smtp-settings'><td colspan='2'>
							<div class='x_content'>
								<div class='form-group'>
									<label class='control-label col-xs-12 col-sm-5'>SMTP Host: ".$this->admin_site->show_tooltip(8,1)."</label>
									<div class='col-xs-12 col-sm-6'>
										<input type='text' name='smtp_host_name' value='$smtp_host' class='form-control col-xs-12 col-sm-7' placeholder='default: $detected_host' />
									</div>
								</div>
								<div class='form-group'>
									<label class='control-label col-xs-12 col-sm-5'>SMTP Port: </label>
									<div class='col-xs-12 col-sm-6'>
										<input type='text' name='smtp_port' value='$smtp_port' class='form-control col-xs-12 col-sm-7' placeholder='default: $detected_port' />
									</div>
								</div>
								<div class='form-group'>
									<label class='control-label col-xs-12 col-sm-5'>Connection Security: </label>
									<div class='col-xs-12 col-sm-6'>
										<input type='radio' name='email_server_type_security' class='sec-type' value='standard'$none_checked /> None<br />
										<input type='radio' name='email_server_type_security' class='sec-type' value='tls'$tls_checked /> TLS<br />
										<input type='radio' name='email_server_type_security' class='sec-type' value='ssl'$ssl_checked /> SSL<br />
									</div>
								</div>
								<div id='email-credentials-wrapper'>
									<div class='form-group'>
										<label class='control-label col-xs-12 col-sm-5'>Connection requires username and password: </label>
										<div class='col-xs-12 col-sm-6'>
											<input type='checkbox' name='email_authentication' $user_pass_checked id='email-auth' value='true' />
										</div>
									</div>
									<div id='email-credentials'>
										<div class='form-group'>
											<label class='control-label col-xs-12 col-sm-5'>SMTP Username: </label>
											<div class='col-xs-12 col-sm-6'>
												<input type='text' name='smtp_user' class='form-control col-xs-12 col-sm-7' value='".geoString::specialChars($db->get_site_setting('email_username'))."' />
											</div>
										</div>
										<div class='form-group'>
											<label class='control-label col-xs-12 col-sm-5'>SMTP Password: </label>
											<div class='col-xs-12 col-sm-6'>
												<input type='password' name='smtp_pass' class='form-control col-xs-12 col-sm-7' value='".geoString::specialChars($db->get_site_setting('email_password'))."' />
											</div>
										</div>
									</div>
								</div>
							</div>
						</td></tr>";

				
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		$mail_email = "<tr class=".$row."><td colspan=\"2\" class=\"medium_font\"><label><input type=\"radio\" name=\"email_server_type\" id=\"email_server_type_mail\" class='mail-method' value=\"mail\" $mail_checked";

		$mail_email .= " /> <strong>Native mail() Method (For Compatibility)</strong>".$this->admin_site->show_tooltip(9,1)."</label></td></tr>
";

		$html .= $standard_email;
		$html .= $smtp_email;
		$html .= $mail_email;
		
		$html .= '
				<script>
					jQuery(".mail-method").click(function() {
						var method = jQuery(this).val();
						if(method == "sendmail" || method == "mail") {
							jQuery("#smtp-settings").hide();
						} else if (method == "smtp_") {
							jQuery("#smtp-settings").show();
						}
					});
					
					jQuery(".sec-type").click(function() {
						var method = jQuery(this).val();
						if(method == "standard") {
							jQuery("#email-credentials-wrapper").hide();
						} else if (method == "tls" || method == "ssl") {
							jQuery("#email-credentials-wrapper").show();
						}
					});
					
					jQuery("#email-auth").click(function() {
						if(jQuery("#email-auth").prop("checked")) {
							jQuery("#email-credentials").show();
						} else {
							jQuery("#email-credentials").hide();
						}
					});
					
					jQuery(document).ready(function() {
						jQuery(".mail-method:checked").click();
						jQuery(".sec-type:checked").click();
						if(jQuery("#email-auth").prop("checked")) {
							jQuery("#email-credentials").show();
						} else {
							jQuery("#email-credentials").hide();
						}
					});
				</script>';
		
		$row = 'row_color2';//reset row color, start on white
		//Main admin email reply address
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';

		$html .= "
</table>
</fieldset>
<fieldset>
	<legend>Email Addresses</legend>
	<div class='x_content'>

		<div class='page_note'>
			Available format: <strong>Friendly Name &lt;actual address&gt;</strong> :: Example: \"Widgets-R-Us &lt;widgets@example.com&gt;\"
		</div>
		
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Admin Communication Reply-to Address:".$this->admin_site->show_tooltip(4,1)."</label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"site_email\"  class=\"form-control col-md-7 col-xs-12\" size=30 value=\"".$db->get_site_setting("site_email")."\">
		  </div>
		</div>";

		//Registration from address
		$html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Registration Notify Address:".$this->admin_site->show_tooltip(6,1)."</label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"registration_admin_email\" class=\"form-control col-md-7 col-xs-12\" size=30 value=\"".$db->get_site_setting("registration_admin_email")."\">
		  </div>
		</div>";

		//Force Contact Seller address
		$tooltip = geoHTML::showTooltip('Force Contact Seller Emails','
		<strong>Affects:</strong> Emails sent to the seller of a specific listing through the contact seller feature<br />
		<strong>Used:</strong> To send all contact seller emails to this email instead of to the seller of the listing.<br />
		<strong>Leave blank</strong> to send contact seller emails normally to the seller.<br />
		<strong>More Info:</strong> If used, all emails sent by the system intended for the seller from the contact seller feature will be sent to this email address instead.');
		$html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Force All Contact Seller Emails to This Email Address: ".$tooltip."</label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"force_contact_seller_to_email\" class=\"form-control col-md-7 col-xs-12\" size=30 value=\"".$db->get_site_setting("force_contact_seller_to_email")."\">
		  </div>
		</div>";

		//Admin BCC address
		if(geoPC::is_ent()) {
			$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
			$html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>BCC admin on user communication:".$this->admin_site->show_tooltip(1,1)."</label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"admin_email_bcc\" class=\"form-control col-md-7 col-xs-12\" size=30 value=\"".$db->get_site_setting("admin_email_bcc")."\">
		  </div>
		</div>";
		}

		//Admin front side address
		$sql = 'SELECT email FROM '.$db->geoTables->userdata_table.' WHERE id=1';
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL: Query: '.$sql.' ERROR: '.$db->ErrorMsg());
		} else {
			$user_data = $result->FetchRow();
			$user_email=geoString::specialChars($user_data['email']);
		}
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		$html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Client Side Admin Email:".$this->admin_site->show_tooltip(5,1)."</label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"admin_user_email\" class=\"form-control col-md-7 col-xs-12\" size=30 value=\"".$user_email."\">
		  </div>
		</div>";

		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		$tooltip = geoHTML::showTooltip('Send all outbound mail as "From:" this address:','
		<strong>Affects:</strong> From: field and ReplyTo: field<br />
		<strong>Used:</strong> On all emails sent by the system.
		<br /><br />
		If <strong>left blank</strong>, the server will impersonate mail senders as the original sender of each message. This can cause problems with modern DMARC-based spam filters.
		<br /><br />
		<strong>To ensure delivery of all emails</strong> from your site, populate this field with an address that uses <em>your domain name</em>.
		<br /><br />
		<strong>More Info:</strong>
		If used, all emails sent by the system will have the From: field set to this email address, and the ReplyTo: field set to what would have otherwise been the From: email.');
		$html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>Send all outbound mail as \"From:\" this Address: $tooltip<br><span class=\"small_font\">(Use this to avoid DMARC bouncebacks)</span></label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"force_admin_email_from\" class=\"form-control col-md-7 col-xs-12\" size='30' value=\"".geoString::specialChars($db->get_site_setting('force_admin_email_from'))."\" />
		  </div>
		</div>";

		//BCC email address for all email sent, for testing
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		$bcc_all_email = $db->get_site_setting('bcc_all_email');
		$html .= "
		<div class='form-group'>
		<label class='control-label col-md-5 col-sm-5 col-xs-12'>BCC For ALL Email Sent:".$this->admin_site->show_tooltip(10,1)."<br><span class=\"small_font\">(For Testing purposes)</span></label>
		  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"bcc_all_email\" class=\"form-control col-md-7 col-xs-12\" size=30 value=\"$bcc_all_email\">
		  </div>
		</div>";

		//end email address section
		$html .= '</div></fieldset>';

		$row = 'row_color2';//reset row color, start on white
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';
		$salutation = $db->get_site_setting('email_salutation_type');
		$html .= "
<fieldset>
	<legend>Site Wide Email Settings</legend>
	<div class='x_content'>
        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Site-Wide Email Header: ".$this->admin_site->show_tooltip(11,1)."</label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
            <textarea name=\"site_email_header\" rows=10 cols=50 class=\"form-control\">".geoString::specialChars($db->get_site_setting("site_email_header",1))."</textarea>
          </div>
        </div>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Site-Wide Email Footer: ".$this->admin_site->show_tooltip(12,1)."</label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
            <textarea name=\"site_email_footer\" rows=10 cols=50 class=\"form-control\">".geoString::specialChars($db->get_site_setting("site_email_footer",1))."</textarea>
          </div>
        </div>


        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>User Salutation: </label>
          <div class='col-md-6 col-sm-6 col-xs-12'>
				<label><input type='radio' name='salutation' value='1'".((!$salutation||$salutation==1)? ' checked="checked"' : '')." /> Username</label><br />
				<label><input type='radio' name='salutation' value='2'".(($salutation==2)? ' checked="checked"' : '')." /> Firstname</label><br />
				<label><input type='radio' name='salutation' value='3'".(($salutation==3)? ' checked="checked"' : '')." /> Firstname Lastname</label><br />
				<label><input type='radio' name='salutation' value='4'".(($salutation==4)? ' checked="checked"' : '')." /> Lastname Firstname</label><br />
				<label><input type='radio' name='salutation' value='5'".(($salutation==5)? ' checked="checked"' : '')." /> Email</label><br />
				<label><input type='radio' name='salutation' value='6'".(($salutation==6)? ' checked="checked"' : '')." /> Firstname Lastname (Username)</label>
          </div>
        </div>

	</div>
	</fieldset>";

		$row = 'row_color2';//reset row color, start on white
		$row = ($row == 'row_color1')? 'row_color2': 'row_color1';


		

		//test settings
		$html .= "
<fieldset>
<legend>Test Email Settings</legend>
<div class='x_content'>

        <div class='form-group'>
        <label class='control-label col-md-5 col-sm-5 col-xs-12'>Send test email to: </label>
          <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name='email_test_from' class='form-control col-md-7 col-xs-12' /><input type='submit' name='auto_save' value='Save & Send Test Email'>
          </div>
        </div>

</div>
</fieldset>
";
		//save button

		$html .= "<div style='text-align:center;'><input type=submit value=\"Save Settings\" name=\"auto_save\"></div>";

		$html .= "</form>";
		$html .= "<script type=\"text/javascript\" src='../addons/email_sendDirect/main.js'></script>";
		$admin->v()->addBody($html)->addCssFile('css/email_section.css');
	}

	/**
	 * update general settings for email
	 */
	public function update_email_general_config(){
		//get the instance of the db.
		$db = $admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		//no input verification needed, since it is all done by ado db for us!

		//set the email server type.  I guess this part we do need to verify inputs.
		$sql = 'UPDATE '.$this->admin_site->site_configuration_table.' SET ';
		$sql_vars[0] = '';
		if ((!isset($_POST['email_server_type']))||(isset($_POST['email_server_type'])&&$_POST['email_server_type']=='sendmail')){
			//server type is normal sendmail...
			$db->set_site_setting('email_server_type', 'sendmail');
		} elseif ($_POST['email_server_type'] == 'mail'){
			$db->set_site_setting('email_server_type','mail');
		} else if ($_POST['email_server_type']=='smtp_'){

			if (isset($_POST['smtp_host_name'])&&$_POST['smtp_host_name']!=''){
				$db->set_site_setting('email_SMTP_server',$_POST['smtp_host_name']);
			} else {
				$admin->userError('SMTP Host field is required for SMTP connections.');
				return false;
			}

			//server type is one of the smtp connections.
			$server_type = 'smtp';
			//now figure out which smtp type it is
			if (isset($_POST['email_authentication']) && $_POST['email_authentication']==true){
				//connection needs authentication, so add the auth thingy
				$server_type .= '_auth';
				//while we're at it, remember the entered user and pass

				$db->set_site_setting('email_username', $_POST['smtp_user']);
				$db->set_site_setting('email_password', $_POST['smtp_pass']);
			}
			//now figure out what connection security to use
			if ($_POST['email_server_type_security']=='standard'){
				//standard connection.
				$server_type .= '_standard';
			} else if ($_POST['email_server_type_security']=='tls'){
				$server_type .= '_tls';
			} else if ($_POST['email_server_type_security']=='ssl'){
				$server_type .= '_ssl';
			} else {
				//either someone tampered with the post vars, or (more likely) they did not click any of the radios,
				//so default to the standard connection
				$server_type .= '_standard';
			}
			//now do the rest of the vars.
			$db->set_site_setting('email_server_type',$server_type);



			if (isset($_POST['smtp_port'])&&$_POST['smtp_port']!=''){
				//we are defining our own port.
				$db->set_site_setting('email_SMTP_port', $_POST['smtp_port']);
			} else {
				//use default, by setting port to 0.
				$db->set_site_setting('email_SMTP_port', 0);
			}
		}
		$db->set_site_setting('site_email', trim($_POST['site_email']));
		if(geoPC::is_ent()) {
			$db->set_site_setting('admin_email_bcc', trim($_POST['admin_email_bcc']));
		}
		$db->set_site_setting('registration_admin_email', trim($_POST['registration_admin_email']));
		$db->set_site_setting('force_contact_seller_to_email', trim($_POST['force_contact_seller_to_email']));
		$db->set_site_setting('bcc_all_email', trim($_POST['bcc_all_email']));
		$db->set_site_setting('force_admin_email_from', trim($_POST['force_admin_email_from']));
		$db->set_site_setting('site_email_header', $_POST['site_email_header'],1);
		$db->set_site_setting('site_email_footer', $_POST['site_email_footer'],1);
		$salutation = (isset($_POST['salutation']))? (int)$_POST['salutation'] : 1;
		$db->set_site_setting('email_salutation_type', $salutation);

		//update the client side email address.
		$sql = 'UPDATE '.$db->geoTables->userdata_table.' SET email=? WHERE id=1';
		$client_email = array( (isset($_POST['admin_user_email']) ? trim($_POST['admin_user_email']) : trim($db->get_site_setting('site_email')))  );
		$result = $db->Execute($sql, $client_email);

		//save advanced settings
		/* not used for now (9/13/11).
		 * might come back into use if sending dual-type text/html emails is implemented


		if ($_POST['email_convert_plain_to']!='plain'){
			$convert_to = 'html';
			$convert_to_link = (isset($_POST['email_convert_url_to_link']) && $_POST['email_convert_url_to_link'])? true: false;
			$db->set_site_setting('email_convert_url_to_link',$convert_to_link);
		} else {
			$convert_to = 'plain';
		}
		$db->set_site_setting('email_convert_plain_to',$convert_to);
		*/

		$admin->userSuccess('Email Settings Saved.');
		if (isset($_POST['email_test_from']) && strlen($_POST['email_test_from'])>0){
			$date=$this->send_test_email($_POST['email_test_from']);
			$admin->userNotice('Just attempted to send email to address: '.$_POST['email_test_from'].'<br />Timestamp in email will be: '.$date.'<br />If you get the test email with matching timestamp, then the settings below worked.');
		}
		return true;
	}

	/**
	 * Function to test sending an email.
	 */
	public function send_test_email($to_address){
		$db = DataAccess::getInstance();

		$to = $to_address;
		$subject = 'Testing the Email Configuration.';
		$message = 'This is a test of the emailing system.  Below are the email settings that were used at the time this email was sent: ';
		$date = date('M d, Y G:i:s');
		$email_settings = array();
		$connection_type = $db->get_site_setting('email_server_type');
		if ($connection_type=='sendmail'){
			$email_settings['Connection Type: ']='Standard SendMail Connection';
		} elseif ($connection_type == 'mail'){
			$email_settings['Connection Type: ']='Native mail() Connection';
		} else {
			$email_settings['Connection Type: ']= 'SMTP Connection';
			$email_settings['SMTP Host: ']= $db->get_site_setting('email_SMTP_server');
			$email_settings['SMTP Port: ']= $db->get_site_setting('email_SMTP_port');
			$connection_type = $db->get_site_setting('email_server_type');
			if (strstr($connection_type, 'ssl')){
				$email_settings['Connection Security: ']= 'SSL';
			} else if (strstr($connection_type, 'tls')){
				$email_settings['Connection Security: ']= 'TLS';
			} else {
				$email_settings['Connection Security: ']= 'NONE';
			}

			if (strstr($connection_type, 'auth')){
				$email_settings['Connection Requires username and password: '] = 'ON';
				$email_settings['SMTP User: ']= $db->get_site_setting('email_username');
				$email_settings['SMTP Pass: ']= '[PASSWORD HIDDEN]';
			}else {
				$email_settings['Connection Requires username and password: '] = 'OFF';
			}
		}

		$email_settings['Admin Communication Reply-to Address (should be from address in this email): ']= $db->get_site_setting('site_email');
		$email_settings['Send "text/plain" email as:']=$db->get_site_setting('email_convert_plain_to');
		$convert_url = ($db->get_site_setting('email_convert_plain_to')=='html' && $db->get_site_setting('email_convert_url_to_link'));
		$email_settings['Convert URL\'s into HTML links:']=($convert_url)? 'On': 'Off';
		$email_settings['Content-Transfer-Encoding Header: '] = ($db->get_site_setting('email_encoding_type'))? $db->get_site_setting('email_encoding_type'): 'Auto Detect';
		$message .= "

Time Email Sent:  $date

";
		if ($convert_url) {
			$message .= "
Test URL Link in email:
http://geodesicsolutions.com
";
		}
		$message .= "
Email Settings:

";
		foreach ($email_settings as $key => $value){
			$message .= "$key $value
";
		}
		$this->admin_site->sendMail($to, $subject, $message);
		//return the date string sent with the message.
		return $date;
	}
}