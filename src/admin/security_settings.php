<?php

//security_settings.php


/**
 * Used for displaying/updating in the admin for security related stuff.
 */
class securitySettings
{


    public $db, $pc;
    var $body;
    var $header;
    var $error_msgs;
    function __construct()
    {
        //get instance of $db
        $this->db = DataAccess::getInstance();
        $this->pc = geoPC::getInstance();
    }
    /**
     * Displays the password settings.
     */
    function display_general_settings()
    {
        if (!$this->db->get_site_setting('min_pass_length')) {
//set the min pass length to old default, 6
            $this->db->set_site_setting('min_pass_length', 6);
        }
        if (!$this->db->get_site_setting('max_pass_length')) {
//set default max pass length to 50
            $this->db->set_site_setting('max_pass_length', 50);
        }
        if (!$this->db->get_site_setting('min_user_length')) {
//set default min user length to 6
            $this->db->set_site_setting('min_user_length', 6);
        }
        if (!$this->db->get_site_setting('max_user_length')) {
//set default max user length to 50
            $this->db->set_site_setting('max_user_length', 50);
        }
        $hash_type_explain = '<strong>Plaintext:</strong> Passwords are stored in the database in human readable format.
<br /><br />
<strong>Hashed:</strong> Passwords are stored in the database in hashed (or "scrambled") form.  The password cannot be retrieved if the password is lost or forgotten, it can only be reset to a new password.
<br /><br />
If you are not sure which settings to use, keep the default settings.
<br /><br />
<strong>Changing password storage methods:</strong> A user will be able to log in, no matter what storage method was used for their password.  After they log in, if the storage method used for their password is different from the setting here, it will be re-saved according to the setting.';
        $forgot_pass_explain = 'If turned off, users will not be able to have their password sent to them.';
        $this->body .= geoAdmin::m();
        $this->body .= '<fieldset><legend>Security Settings</legend>';
        $this->body .= '<form method="post" action="" class="form-horizontal form-label-left"><div class="x_content"><table width="100%">

					<div class="header-color-primary-mute">
						Username &amp; Password Settings
					</div>

					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Username Length: </label>
					  <div class="col-md-6 col-sm-6 col-xs-12 input-group">
					  		<div class="input-group-addon">Min:</div><input type="text" size="3" name="min_user_length" style="margin-right:5px; border-right: 1px solid #ccc; min-width: 50px;" class="form-control col-md-7 col-xs-12 input-group-width60" value="' . $this->db->get_site_setting('min_user_length') . '" />
							<div class="input-group-addon" style="border-right: 0;">Max:</div><input type="text" size="3" name="max_user_length" style="margin-right:5px; border-right: 1px solid #ccc; min-width: 50px;" class="form-control col-md-7 col-xs-12 input-group-width60" value="' . $this->db->get_site_setting('max_user_length') . '" />
					  </div>
					</div>

					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Password Length: </label>
					  <div class="col-md-6 col-sm-6 col-xs-12 input-group">
					  		<div class="input-group-addon">Min:</div><input type="text" size="3" name="min_pass_length" style="margin-right:5px; border-right: 1px solid #ccc; min-width: 50px;" class="form-control col-md-7 col-xs-12 input-group-width60" value="' . $this->db->get_site_setting('min_pass_length') . '" />
							<div class="input-group-addon" style="border-right: 0;">Max:</div><input type="text" size="3" name="max_pass_length" style="margin-right:5px; border-right: 1px solid #ccc; min-width: 50px;" class="form-control col-md-7 col-xs-12 input-group-width60" value="' . $this->db->get_site_setting('max_pass_length') . '" />
					  </div>
					</div>

					';
        $hash_types = $this->pc->get_hash_types();
        $this->body .= '
					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Admin Password Storage Method: ' . geoHTML::showTooltip('Admin Password Storage Method', $hash_type_explain, 1) . '</label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
						  <select name="admin_pass_hash" class="form-control col-md-7 col-xs-12">';
        foreach ($hash_types as $index => $hash_type) {
            if (!$hash_type['name']) {
        //name not set, not meant to be selected as type
                continue;
            }
            $name = $hash_type['name'];
            if ($index == 'core:sha1') {
                $name .= ' (default)';
            }
            $this->body .= '<option value="' . $index . '"';
            if ($this->db->get_site_setting('admin_pass_hash') == $index) {
                $this->body .= ' selected="selected"';
            }
            $this->body .= '>' . $name . '</option>';
        }
                          $this->body .= '</select>
					  </div>
					</div>
						';
        $this->body .= '
					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Client Password Storage Method: ' . geoHTML::showTooltip('Client Password Storage Method', $hash_type_explain, 1) . '</label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
						  <select name="client_pass_hash" class="form-control col-md-7 col-xs-12">';
        foreach ($hash_types as $index => $hash_type) {
            if (!$hash_type['name']) {
        //name not set, not meant to be selected as type
                continue;
            }
            $name = $hash_type['name'];
            if ($index == 'core:sha1') {
                $name .= ' (default)';
            }
            $this->body .= '<option value="' . $index . '"';
            if ($this->db->get_site_setting('client_pass_hash') == $index) {
                $this->body .= ' selected="selected"';
            }
            $this->body .= '>' . $name . '</option>';
        }
                          $this->body .= '</select>
					  </div>
					</div>
						';
        $this->body .= '
					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Forgot Password Tool: ' . geoHTML::showTooltip('Forgot Password Tool', $forgot_pass_explain, 1, true) . '<br /><span class="small_font">- <a href="index.php?page=sections_page&amp;b=40">Manage page for Lost Password Form</a></span>
							<br /><span class="small_font">- <a href="index.php?page=sections_page&amp;b=41">Manage page for Lost Password E-Mail</a>&nbsp;</span>
					</label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
						<input type="radio" name="forgot_password" value="1" ';
        if ($this->db->get_site_setting('forgot_password')) {
            $this->body .= 'checked="checked" ';
        }
                                $this->body .= '/> On
						<input type="radio" name="forgot_password" value="0" ';
        if (!$this->db->get_site_setting('forgot_password')) {
            $this->body .= 'checked="checked" ';
        }
        $this->body .= '/> Off
					  </div>
					</div>

					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Require pass: User-Info Edit: ' . geoHTML::showTooltip('Require pass: User-Info Edit', 'If set to Yes, this will require a user to enter his password before editing his contact information', 1) . '</label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
						<input type="radio" name="info_edit_require_pass" value="1" ';
        if ($this->db->get_site_setting('info_edit_require_pass')) {
            $this->body .= 'checked="checked" ';
        }
                                $this->body .= '/> Yes
						<input type="radio" name="info_edit_require_pass" value="0" ';
        if (!$this->db->get_site_setting('info_edit_require_pass')) {
            $this->body .= 'checked="checked" ';
        }
        $this->body .= '/> No
					  </div>
					</div>
					';
//Advanced Session & Cookie Settings
        $timeout_explain = 'The amount of time, in seconds, before a session is removed from the database due to inactivity.  If logged in, this is the amount of time before a user is logged out
due to inactivity.
<br /><br />
<strong>More Info:</strong> Sessions are used to keep track of how many active users are on the site, and to keep track of login details.';
        $robot_explain = 'Specify a list of user-agent strings separated by <strong>||</strong>, to add to the list in the file <strong>robot_list.php</strong>.
If the user-agent is already in that file, there is no need to add it here.

<br /><br />
<strong>More Info:</strong>This is a list of additional user-agents, in addition
to the ones defined in robot_list.php, that are known to be robots or search
engine crawlers. If a user-agent is detected to be on the robot list, it will not be
given any cookies, and will not be redirected. This should either be left blank
to just use the list in robot_list.php, or it should be a || separated list.
<br /><br />
The purpose of doing this, is to improve page ranks and search engine listings on search engines.';
        $cookie_url_explain = "The settings found below can be changed by modifying or adding the appropriate line(s)
in your config.php file. There is no admin switch for these settings, because changing these
settings while logged into the admin is not possible.
<br /><br />
For your reference, below are example lines from config.php that you would modify (or add if your config.php does not already have the settings):
<br />
<div style=\"border: thin black solid; white-space: pre;\">
//If your server does not properly set the domain name, un-comment the following<br />
//line, and replace the domain name with the proper setting.<br />
// (un-comment to change)<br />
//define (\'COOKIE_DOMAIN\',\'.YourClassifiesSite.com\');<br />
</pre></div>";
//$cookie_name_explain = '';
        $this->body .= '

					<div class="header-color-primary-mute">
						Advanced Session &amp; Cookie Settings
					</div>

					 <div class="form-group">
					  <label class="control-label col-md-5 col-sm-5 col-xs-12">Session Time-Out: ' . geoHTML::showTooltip('Session Time-Out', $timeout_explain, 1) . '</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
						  <div class="input-group">
							<div class="input-group-addon">Admin Side:</div>
							<input type="text" size="10" name="session_timeout_admin" class="form-control col-md-7 col-xs-12" style="min-width: 50px;" value="' . $this->db->get_site_setting('session_timeout_admin') . '" />
							<div class="input-group-addon">seconds</div>
						  </div>
						  <div class="input-group">
							<div class="input-group-addon">Client Side:</div>
							<input type="text" size="10" name="session_timeout_client" class="form-control col-md-7 col-xs-12" style="min-width: 50px;" value="' . $this->db->get_site_setting('session_timeout_client') . '" />
							<div class="input-group-addon">seconds</div>
						  </div>
						</div>
					  </div>
					';
//specifying cookie name not implemented yet.
/*                    <tr class="row_color1">
                        <td class="medium_font" tooltip="'.geoString::specialChars($cookie_name_explain).'"><strong>Cookie Name</strong></td>
                      <td class="medium_font">
                           Additional cookie and "re-direct" settings are set in config.php.
                      </td>
                  </tr>
*/
        $this->body .= '
					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Additional Robot User-Agents: ' . geoHTML::showTooltip('Additional Robot User-Agents', $robot_explain, 1) . '<br><span class="small_font">List separated by Double Pipe:</span> <span class="color-primary-one"><strong>||</strong></span></label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
					  <textarea rows="4" cols="80" name="additional_robots_list" class="form-control">' . $this->db->get_site_setting('additional_robots_list') . '</textarea>
					  </div>
					</div>

					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Additional Cookie Settings: ' . geoHTML::showTooltip('Additional Cookie Settings', $cookie_url_explain, 1) . '</label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
					  	<span class="vertical-form-fix">Additional cookie and redirect settings are set in config.php</span>
					  </div>
					</div>
';
//Misc Settings:
        $allowed_ips_explain = 'This setting can be used only in conjuction with the site on/off switch located
in <a href="index.php?mc=site_setup&page=main_general_settings">Site Setup > General Settings</a> within your admin.  This will allow you to disable your
website from public access, while at the same time allowing you (or any IPs you choose) to perform
maintenance such as placing test listings, etc.<br /><br />
You can place as many IPs as you wish.  You can use
partial IPs, but the software assumes you are leaving off the right-most octets (ex.  192.168 will be
interpreted as 192.168.x.x).  Separate each IP by a comma.
<br /><br />
<strong>NOTE:</strong> You must supply 3 digits for any given octet, or else end the octet with a period for an exact match.
For example, 10.0 would match 10.0.x.x AND 10.056.x.x, but 10.0. would only match 10.0.x.x
<br /><strong>EXAMPLE:</strong><br />
<span style="border: thin black solid; padding: 2px; font-weight:bold;">10.127., 192.168.0.1</span>
<br /> &nbsp;';
        $this->body .= '

					<div class="header-color-primary-one">
						Misc. Advanced Settings
					</div>

					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Allowed IPs When Site Disabled: ' . geoHTML::showTooltip('Allowed IPs When Site Disabled', $allowed_ips_explain, 1) . '<br><span class="small_font">List separated by comma:</span> <span class="color-primary-one"><strong>,</strong></span></label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
					  <textarea rows="4" cols="80" name="allowed_ips_when_site_disabled" class="form-control">' . $this->db->get_site_setting('allowed_ips_when_site_disabled') . '</textarea>
					  </div>
					</div>

';
//display IP address
                $this->body .= '
					<div class="form-group">
					<label class="control-label col-md-5 col-sm-5 col-xs-12">Your Current IP: </label>
					  <div class="col-md-6 col-sm-6 col-xs-12">
						<span class="vertical-form-fix color-primary-two" style="font-size: 1.2em;font-weight: bold;">' . geoUtil::getRemoteIp() . '</span>
					  </div>
					</div>

';
        $this->body .= '
				</table></div>
				<div style="text-align: center;"><input type="submit" name="auto_save" value="Save Changes" /></div>
				</form></fieldset><div class="clearColumn"></div>
';
        geoAdmin::display_page($this->body, '', '', '', $this->header);
    }
    /**
     * Saves the General Security Settings
     *
     */
    function update_general_settings()
    {
        $allowed_hash_types = array_keys($this->pc->get_hash_types());
//Username & Password Settings section save
        if (isset($_POST['min_pass_length'], $_POST['max_pass_length']) && is_numeric($_POST['min_pass_length']) && is_numeric($_POST['max_pass_length']) && $_POST['min_pass_length'] <= $_POST['max_pass_length']) {
//set the min & max pass length, but only if numerical and max >= min
            $this->db->set_site_setting('min_pass_length', intval($_POST['min_pass_length']));
            $this->db->set_site_setting('max_pass_length', intval($_POST['max_pass_length']));
        } else {
            return false;
        //setting save failed.
        }
        if (isset($_POST['min_user_length'], $_POST['max_user_length']) && is_numeric($_POST['min_user_length']) && is_numeric($_POST['max_user_length']) && $_POST['min_user_length'] <= $_POST['max_user_length']) {
//set the min & max user length, but only if numerical and max >= min
            $this->db->set_site_setting('min_user_length', intval($_POST['min_user_length']));
            $this->db->set_site_setting('max_user_length', intval($_POST['max_user_length']));
        } else {
            return false;
        //setting save failed.
        }

        if (isset($_POST['admin_pass_hash']) && in_array($_POST['admin_pass_hash'], $allowed_hash_types)) {
//make sure it is 1 or 0
            $this->db->set_site_setting('admin_pass_hash', $_POST['admin_pass_hash']);
        } else {
            return false;
        //setting save failed.
        }
        if (isset($_POST['client_pass_hash']) && in_array($_POST['client_pass_hash'], $allowed_hash_types)) {
//make sure it is 1 or 0
            $this->db->set_site_setting('client_pass_hash', $_POST['client_pass_hash']);
        } else {
            return false;
        //setting save failed.
        }
        if (isset($_POST['forgot_password'])) {
            if ($_POST['forgot_password']) {
                $this->db->set_site_setting('forgot_password', '1');
            } else {
                $this->db->set_site_setting('forgot_password', false);
            }
        }
        //require pass to edit user info
        if (isset($_POST['info_edit_require_pass'])) {
            $this->db->set_site_setting('info_edit_require_pass', (($_POST['info_edit_require_pass']) ? 1 : false));
        }
        //Session Time-Out
        if (isset($_POST['session_timeout_admin'], $_POST['session_timeout_client'])) {
//make sure valid
            $client_timeout = intval($_POST['session_timeout_client']);
            $admin_timeout = intval($_POST['session_timeout_admin']);
            if ($admin_timeout < 600) {
            //error
                $this->error_msgs['session_timeout_admin'] = 'Minimum value is 600 seconds (10 minutes).';
                $admin_timeout = false;
            }
            if ($client_timeout < 600) {
                $this->error_msgs['session_timeout_client'] = 'Minimum value is 600 seconds (10 minutes).';
                $client_timeout = false;
            }

            //save the settings.
            if ($admin_timeout) {
                $this->db->set_site_setting('session_timeout_admin', $admin_timeout);
            }
            if ($client_timeout) {
                $this->db->set_site_setting('session_timeout_client', $client_timeout);
            }
        }

        //Additional Robot User-Agents
        if (isset($_POST['additional_robots_list'])) {
            $additional_list = trim($_POST['additional_robots_list']);
            if (strlen($additional_list)) {
            //make sure items are not already in the existing list.
                $start_array = explode('||', $additional_list);
                $end_array = array();
                include(GEO_BASE_DIR . 'robots_list.php');
                foreach ($start_array as $agent) {
                    if (in_array($agent, $robots)) {
                            //already in robots_list.php...
                        $this->error_msgs['additional_robots_list'] = 'Some agents on the list not added, they were either duplicates, or are already in robots_list.php';
                    } elseif (in_array($agent, $end_array)) {
                                //duplicate entries...
                        $this->error_msgs['additional_robots_list'] = 'Some agents on the list not added, they were either duplicates, or are already in robots_list.php';
                    } elseif (strlen($agent)) {
                        //nothing wrong with entry, so add it to the list.
                        $end_array[] = $agent;
                    }
                }
                if (isset($this->error_msgs['additional_robots_list'])) {
            //the list was modified.
                    $additional_list = implode('||', $end_array);
                }
            }
            $this->db->set_site_setting('additional_robots_list', $additional_list);
        }
        //Allowed IPS When Site Disabled
        if (isset($_POST['allowed_ips_when_site_disabled'])) {
            $allowed_ips = trim($_POST['allowed_ips_when_site_disabled']);
            $this->db->set_site_setting('allowed_ips_when_site_disabled', $allowed_ips);
        }
        return true;
    }
}
