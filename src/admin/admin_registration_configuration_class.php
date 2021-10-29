<?php

// admin_registration_configuration_class.php
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
## ##    17.12.0-12-g1fa40c1
##
##################################

class Registration_configuration extends Admin_site
{

    var $internal_error_message = "There was an internal error";
    var $data_error_message = "Not enough data to complete request";
    var $page_text_error_message = "No text connected to this page";
    var $no_pages_message = "No pages to list";

    var $errors;

    var $registration_configuration_message;

    var $debug_registration_configuration = 0;

    function display_registration_configuration_form($db)
    {
        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
        // Set title and text for tooltip
        $this->body .= "
			Text[1] = [\"Use Field\", \"Checking this checkbox will display this field during the registration process.\"]\n
			Text[2] = [\"Require Field\", \"Checking this checkbox will require this field within the registration process. The fields that have this choice are required if used\"]\n
			Text[3] = [\"Field Length\", \"This value will determine maximum amount of characters or numbers a user can place into this field during registration. The maximum number of characters that can be placed in any field is 100.\"]\n
			Text[4] = [\"Email 2\", \"Choose whether to use and/require a 2nd email.  NOTE:The primary email is always a required field.\"]\n
			Text[5] = [\"Email Address of Admin\", \"This is the email address that will receive registration confirmation and success message sent to admin (if you choose to receive them by choosing so below).\"]\n
			Text[6] = [\"URL of Register.php File\", \"The file \\\"register.php\\\" can be placed where you like or completely eliminated if you want.\"]\n
			Text[7] = [\"Secure SSL URL to the Register.php file\", \"i.e. (https://www.somesite.com/register.php)<br><br>Entering a secure URL into this field will allow your registrants to register on a secure page. This requires a security certificate to be installed on your server. Contact your host for information on security certificates.\"]\n
			Text[8] = [\"Use SSL Connection for the Registration Process\", \"If you want to secure the registration process with a security certificate that is already installed on your server, please check here.\"]\n
			Text[9] = [\"Send a Register Complete Email to Admin\", \"An email with user information will be sent to the admin email listed above whenever a user completes the registration process.\"]\n
			Text[10] = [\"Send a Register Attempt Email to Admin\", \"An email with username,password and email will be sent to the admin email listed above whenever a user is sent the confirmation email to confirm their email address.\"]\n
			Text[11] = [\"Send Registration Complete Email to Registrant\", \"An email will be sent to the registrant when they complete the registration process welcoming them to your site.\"]\n
			Text[12] = [\"Use Email Verification System\", \"If you check yes an email will be sent to the registrants email address with a link back to the site once they have entered all of their registration information. They must then click the link within that email which brings them back to the site. If all information was returned correctly the registration is completed.\"]\n
			Text[13] = [\"Admin Approves All Registrations\", \"If yes then the admin will appove all registrations before they become active. Also note that you will need to edit the text displayed on the \\\"Registration Success Page\\\" stating that their registration will require admin approval.\"]\n
			Text[14] = [\"Secret Hash Word\", \"This is a string of characters used to generate the keys that registrants use to confirm their registration (if you use the email verification described in the first step). There is typically no need to change this unless you suspect registration manipulation by an automatic registration script of some kind.\"]\n

			Text[15] = [\"Other Box\", \"Checking the \\\"other box\\\" field will display an additional text field next to the this optional field for the user to enter their information.\"]\n
			Text[16] = [\"Dependent\", \"Checking this box will set this field to \\\"required\\\" if the user has chosen the business or company field earlier in the registration process.\"]\n
			Text[17] = [\"Type\", \"Choose the type of entry field you want the user to see when they enter their information.\"]\n
			Text[18] = [\"Registration Optional Field Admin Name\", \"Keep track of your Registration Optional Fields by giving them a name you choose.  This name will be visible throughout your admin, wherever the field is used. IMPORTANT: The name of each \\\"registration optional field admin name\\\" is only a tool for you to keep track of how you are using the field.  This name will ONLY be visible in your admin.  To change the name of the field actually being used, you must go to the particular page in \\\"Pages Management\\\" where the field is used and edit it there.\"]\n
			Text[19] = [\"Field Length\", \"This value will determine maximum amount of characters or numbers a user can place into this field during registration. The maximum number of characters that can be placed in any field is 65535.\"]\n
			";

        //".$this->show_tooltip(15,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
        $this->body .= "</script>";

        $sql_query = "select * from " . $this->site_configuration_table;
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() == 1) {
            $show_configuration = $result->FetchRow();
        }

        $sql_query = "select * from " . $this->registration_configuration_table;
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() == 1) {
            $registration_configuration = $result->FetchRow();

            //$this->title = "Registration Setup > General Settings";
            $this->description = "Use this page to specify which fields you want registrants to see during the registration process.
			The settings below will be applied on a site-wide basis.";

            $this->body .= "
				<script type=\"text/javascript\">
					function validate(field,max)
					{
						max=(max)?max:100;
						if (!(field.value>=0 && field.value<=max))
						{
							alert('Must be between 0 and '+max+'. Values outside this range as well as invalid characters will not be submitted.');
							field.value=\"\";
							field.focus();
						}
					}
					function check_all(elements,col)
					{
						for(x = 0; x < elements.length; x++)
						{
							if(elements[x].id == col && !elements[x].disabled)
								elements[x].checked=elements[col+'_all'].checked;
							if(elements[x].id == col+'_section' && !elements[x].disabled)
								elements[x].checked=elements[col+'_all'].checked;
						}
					}
				</script>";

            if (PHP5_DIR) {
                $menu_loader = geoAdmin::getInstance();
            } else {
                $menu_loader =& geoAdmin::getInstance();
            }


            if (!$result) {
                trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
                $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
                $this->body .= $menu_loader->getUserMessages();
                return false;
            } elseif ($result->RecordCount() > 0) {
                $this->body .= $menu_loader->getUserMessages();
                $this->body .= $body;
                $this->description .= $description;

                if (!$this->admin_demo()) {
                    $this->body .= "<form name=fields_to_use class='form-horizontal form-label-left' action=index.php?mc=registration_setup&page=register_general_settings method=post>";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }

                if ($this->registration_configuration_message) {
                    $this->body .= "
					<div class='center medium_error_font'>
						" . $this->registration_configuration_message . "
					</div>";
                }
            // Block of checkboxes for major settings
                $this->body .= "

			<fieldset>
				<legend>New Registrations</legend>
				<div class='x_content'>
					<div class='form-group center'>
					  <div>
					    <input type='checkbox' name='b[disable_registration]' value='1' " . (($this->db->get_site_setting('disable_registration')) ? 'checked="checked"' : '') . " />&nbsp;
					    <span style='font-size:1.2em;'>Disable Registrations?</span>
					  </div>
					</div>
					<div class='center'><input type='submit' name='auto_save' value='Save' /></div>

				</div>
			</fieldset>
			<fieldset id='RegStdFlds'>
				<legend>Standard Registration Fields</legend>
					<div class=\"table-responsive\">
                         <table width=100% cellpadding=5 cellspacing=1 class=\"table table-hover table-striped table-bordered\">
                         	<thead>
								<tr class=col_hdr_top>
									<td align=center class=col_hdr width=\"40%\"><b>Registration Field</b></td>
									<td align=center class=col_hdr width=\"20%\"><b>Use</b>&nbsp;" . $this->show_tooltip(1, 1) . "</td>
									<td align=center class=col_hdr width=\"20%\"><b>Require</b>&nbsp;" . $this->show_tooltip(2, 1) . "</td>
									<td align=center class=col_hdr width=\"20%\"><b>Length</b>&nbsp;" . $this->show_tooltip(3, 1) . "</td>
								</tr>
							</thead>";
                $this->row_count = 0;

            //First Name Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>First Name</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_firstname_field] value=1 "
                                    . (($registration_configuration['use_registration_firstname_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_firstname_field] value=1 "
                                    . (($registration_configuration['require_registration_firstname_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[firstname_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['firstname_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Last Name Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Last Name</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_lastname_field] value=1 "
                                    . (($registration_configuration['use_registration_lastname_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_lastname_field] value=1 "
                                    . (($registration_configuration['require_registration_lastname_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[lastname_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['lastname_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Company Name Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Company Name</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_company_name_field] value=1 "
                                    . (($registration_configuration['use_registration_company_name_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_company_name_field] value=1 "
                                    . (($registration_configuration['require_registration_company_name_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[company_name_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['company_name_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Business Type Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Account Type</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_business_type_field] value=1 "
                                    . (($registration_configuration['use_registration_business_type_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_business_type_field] value=1 "
                                    . (($registration_configuration['require_registration_business_type_field'] == 1) ? "checked" : "") . ">
								</td>
								<td>&nbsp;</td>
							</tr>";
                $this->row_count++;

            //Address 1 Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Address 1</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_address_field] value=1 "
                                    . (($registration_configuration['use_registration_address_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_address_field] value=1 "
                                    . (($registration_configuration['require_registration_address_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[address_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['address_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Address 2 Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Address 2</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_address2_field] value=1 "
                                    . (($registration_configuration['use_registration_address2_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_address2_field] value=1 "
                                    . (($registration_configuration['require_registration_address2_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[address_2_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['address_2_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Phone 1 Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Phone 1</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_phone_field] value=1 "
                                    . (($registration_configuration['use_registration_phone_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_phone_field] value=1 "
                                    . (($registration_configuration['require_registration_phone_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[phone_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['phone_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Phone 2 Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Phone 2</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_phone2_field] value=1 "
                                    . (($registration_configuration['use_registration_phone2_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_phone2_field] value=1 "
                                    . (($registration_configuration['require_registration_phone2_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[phone_2_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['phone_2_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Fax Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Fax</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_fax_field] value=1 "
                                    . (($registration_configuration['use_registration_fax_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_fax_field] value=1 "
                                    . (($registration_configuration['require_registration_fax_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[fax_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['fax_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

                $geographicOverrides = geoRegion::getLevelsForOverrides();
                $levels = geoRegion::getInstance()->getLevels();
                foreach ($levels as $level => $data) {
                    $this->body .= '<tr class="' . $this->get_row_color() . '">
									<td align="center" valign="top" class="medium_font"><strong>Region Level ' . $level . ' (' . $data['label'] . ')</strong></td>
									<td align="center" valign="top" class="medium_font">
										<input id="use" type="checkbox" name="b[use_region_level_' . $level . ']" value="1"
										' . ($this->db->get_site_setting('registration_use_region_level_' . $level) == 1 ? 'checked="checked"' : '') . ' />
									</td>
									<td align="center" valign="top" class="medium_font">
										<input id="require" type="checkbox" name="b[require_region_level_' . $level . ']" value="1"
										' . ($this->db->get_site_setting('registration_require_region_level_' . $level) == 1 ? 'checked="checked"' : '') . ' />
									</td>
									<td></td>
								</tr>';
                    $this->row_count++;
                }


            //City Field
                if (!$geographicOverrides['city']) {
                    //city is not in use in Regions, so do it separate
                    $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>City</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_city_field] value=1 "
                                    . (($registration_configuration['use_registration_city_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_city_field] value=1 "
                                    . (($registration_configuration['require_registration_city_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[city_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['city_maxlength'] . ">
								</td>
							</tr>";
                    $this->row_count++;
                }

            //Zip Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Zip</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_zip_field] value=1 "
                                    . (($registration_configuration['use_registration_zip_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_zip_field] value=1 "
                                    . (($registration_configuration['require_registration_zip_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[zip_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['zip_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //URL Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>URL</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_url_field] value=1 "
                                    . (($registration_configuration['use_registration_url_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_url_field] value=1 "
                                    . (($registration_configuration['require_registration_url_field'] == 1) ? "checked" : "") . ">
								</td>
								<td align=center valign=top>
									<input onkeyup=validate(this) type=text name=b[url_maxlength] size=3 maxsize=3 value="
                                    . $registration_configuration['url_maxlength'] . ">
								</td>
							</tr>";
                $this->row_count++;

            //Email 2 Field
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Email 2</b>&nbsp;" . $this->show_tooltip(4, 1) . "</td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_registration_email2_field] value=1 "
                                    . (($registration_configuration['use_registration_email2_field'] == 1) ? "checked" : "") . ">
								</td>
								<td valign=top align=center class=medium_font>
									<input id=require type=checkbox name=b[require_registration_email2_field] value=1 "
                                    . (($registration_configuration['require_registration_email2_field'] == 1) ? "checked" : "") . ">
								</td>
								<td>&nbsp;</td>
							</tr>";
                $this->row_count++;

            //Accept User Agreement
                $this->body .= "<tr class=" . $this->get_row_color() . ">
								<td align=center valign=top class=medium_font><b>Accept User Agreement</b></td>
								<td align=center valign=top class=medium_font>
									<input id=use type=checkbox name=b[use_user_agreement_field] value=1 "
                                    . (($registration_configuration['use_user_agreement_field'] == 1) ? "checked" : "") . ">
								</td>
								<td>&nbsp;</td>
								<td>&nbsp;</td>
							</tr>";
                $this->row_count++;

                $this->body .= "<tr>
								<td align=right class=col_ftr><b>Select All:&nbsp;&nbsp;</b></td>
								<td align=center class=col_ftr>
									<input id=use_all onclick=\"javascript:check_all(document.fields_to_use,'use');\" type=checkbox>
								</td>
								<td align=center class=col_ftr>
									<input id=require_all onclick=\"javascript:check_all(document.fields_to_use,'require');\" type=checkbox>
								</td>
								<td align=center class=col_ftr>
									<input type=\"button\" onclick=\"reset()\" value=\"reset form\">
								</td>
							</tr>
						</table>
						</div>
                       </fieldset>";
                $this->row_count = 0;


            //url of register.php
                $this->body .= "
					<fieldset id='RegURLs'>
						<legend>URL and SSL Settings</legend>
						<div class='x_content'>

						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>URL of register.php file: " . $this->show_tooltip(6, 1) . " </label>
							<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[registration_url] class='form-control col-md-7 col-xs-12' value=" . $show_configuration["registration_url"] . ">
							</div>
						</div>";

            //secure ssl url
                $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>SSL URL of register.php file: " . $this->show_tooltip(7, 1) . " </label>
							<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[registration_ssl_url] class='form-control col-md-7 col-xs-12' value=\""
                                . $show_configuration["registration_ssl_url"] . "\">
							</div>
						</div>";

            //use SSL in registration
                $sslOnly = $this->db->get_site_setting('use_ssl_only');
                $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>Use SSL for Registration: " . $this->show_tooltip(8, 1) . " </label>
							<div class='col-md-6 col-sm-6 col-xs-12'>
								<input type=radio name=b[use_ssl_in_registration] value=1 " . (($sslOnly || $show_configuration["use_ssl_in_registration"] == 1) ? "checked" : "") . "> yes";
                if ($sslOnly) {
                    $this->body .= ", because <a href='index.php?page=main_general_settings&mc=site_setup'>Use SSL For All Pages</a> is on";
                } else {
                    $this->body .= "<br />
								<input type=radio name=b[use_ssl_in_registration] value=0 " . (($show_configuration["use_ssl_in_registration"] == 0) ? "checked" : "") . "> no";
                }

                $this->body .= "
							</div>
						</div>";

            //just-in-time registration
                $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>Just-in-time Registration/Login: &nbsp;" .
                                geoHTML::showTooltip('Just-in-time Registration/Login', 'If enabled, this will allow users to complete most of a listing before being asked to register or login.')
                            . "</label>
							<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[jit_registration] value=1 "
                                . (($this->db->get_site_setting("jit_registration") == 1) ? "checked" : "") . "> on<br />
								<input type=radio name=b[jit_registration] value=0 "
                                . (($this->db->get_site_setting("jit_registration") == 0) ? "checked" : "") . "> off<br />
								<input type='checkbox' name='b[jit_allow_user_pass]' value='1' " . (($this->db->get_site_setting('jit_allow_user_pass') == 1) ? 'checked="checked"' : '') . "/> Allow users to pick a username/password&nbsp;" .
                                geoHTML::showTooltip('Allow users to pick a username/password', 'If this option is NOT checked, the Just-in-time system will automatically generate a randomized username and password for new just-in-time registrations.') .
                                "<br /><input type='checkbox' name='b[jit_require_email_confirmation]' value='1' " . (($this->db->get_site_setting('jit_require_email_confirmation') == 1) ? 'checked="checked"' : '') . "/> Require email confirmation&nbsp;" .
                                geoHTML::showTooltip('Require email confirmation', 'This will require new JIT registrants to confirm their email addresses via a confirmation code.')
                            . "
							</div>
						</div>";

            //Secret Hash Word
                $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>Secret Hash Word: " . $this->show_tooltip(14, 1) . " </label>
							<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[secret_for_hash] class='form-control col-md-7 col-xs-12' value=\"" . $show_configuration["secret_for_hash"] . "\">
							</div>
						</div>";

                $this->body .= "</div></fieldset>";


                if (geoPC::is_ent()) {
                    $this->body .= "
								<fieldset id='RegOptFlds'>
									<legend>Optional Registration Fields</legend>
									<div class='x_content'>
										<div class='table-responsive'>
											<table width=100% cellpadding=5 cellspacing=1 class='table table-hover table-striped table-bordered'>
												<thead>
												<tr class=col_hdr_top>
													<td align=center class=col_hdr><b>Admin Field Name (#)</b>" . $this->show_tooltip(18, 1) . "</td>
													<td align=center class=col_hdr><b>Use</b>" . $this->show_tooltip(1, 1) . "</td>
													<td align=center class=col_hdr><b>Require</b>" . $this->show_tooltip(2, 1) . "</td>
													<td align=center class=col_hdr><b>Other Box</b>" . $this->show_tooltip(15, 1) . "</td>
													<td align=center class=col_hdr><b>Dependent</b>" . $this->show_tooltip(16, 1) . "</td>
													<td align=center class=col_hdr><b>Length</b>" . $this->show_tooltip(19, 1) . "</td>
													<td align=center class=col_hdr><b>Type</b>" . $this->show_tooltip(17, 1) . "</td>
												</tr>
												</thead><tbody>";

                    $this->row_count = 0;
                    //Optional Fields
                    for ($i = 1; $i < 11; $i++) {
                        $this->body .= "	<tr class=" . $this->get_row_color() . ">
										<td valign=top align=left class=medium_font>
											<input type=text size=30 name=b[registration_optional_" . $i . "_field_name] value=\"" .
                                            $registration_configuration['registration_optional_' . $i . '_field_name'] . "\">($i)
										</td>
										<td valign=top align=center class=medium_font>
											<input id=optional_use type=checkbox name=b[use_registration_optional_" . $i . "_field] value=1 " .
                                            (($registration_configuration["use_registration_optional_" . $i . "_field"] == 1) ? "checked" : "") . ">
										</td>
										<td align=center valign=top class=medium_font>
											<input id=optional_require type=checkbox name=b[require_registration_optional_" . $i . "_field] value=1 " .
                                             (($registration_configuration["require_registration_optional_" . $i . "_field"] == 1) ? "checked" : "") . ">
										</td>
										<td align=center valign=top class=medium_font>
											<input id=optional_other_box type=checkbox name=b[registration_optional_" . $i . "_other_box] value=1 " .
                                            (($registration_configuration["registration_optional_" . $i . "_other_box"] == 1) ? "checked" : "") . ">
										</td>
										<td valign=top align=center class=medium_font>
											<input id=optional_dependent type=checkbox name=b[require_registration_optional_" . $i . "_field_dep] value=1 " .
                                            (($registration_configuration["require_registration_optional_" . $i . "_field_dep"] == 1) ? "checked" : "") . ">
										</td>
										<td align=center valign=top class=small_font>
											<input onkeyup=validate(this,65535) type=text name=b[optional_" . $i . "_maxlength] size=3 maxsize=3 value="
                                            . $registration_configuration['optional_' . $i . '_maxlength'] . ">
										</td>
										<td align=center valign=top class=small_font>
											<select name=b[registration_optional_" . $i . "_field_type]>
												<option value=0 " . (($registration_configuration["registration_optional_" . $i . "_field_type"] == 0) ? "selected" : "") . ">
													blank text box
												</option>
												<option value=1 " . (($registration_configuration["registration_optional_" . $i . "_field_type"] == 1) ? "selected" : "") . ">
													textarea
												</option>";
                        $this->sql_query = "select * from " . $this->registration_choices_types_table;
                        $types_result = $this->db->Execute($this->sql_query);
                        if (!$types_result) {
                            $this->error_message = $this->messages[5501];
                            $this->site_error($this->db->ErrorMsg());
                            return false;
                        } elseif ($types_result->RecordCount() > 0) {
                            while ($show_type = $types_result->FetchRow()) {
                                //show questions as drop down box
                                $this->body .= "		<option value=" . $show_type['type_id'] .
                                                        (($show_type['type_id'] == $registration_configuration['registration_optional_' . $i . '_field_type']) ? " selected" : "") . ">"
                                                        . $show_type['type_name'] . "
													</option>";
                            } //end of while
                        }
                        $this->body .= "			</select>
											</td>
										</tr>";
                        $this->row_count++;
                    }


                    $this->body .= "<tr>
								<td align=right class=col_ftr><b>Select All:&nbsp;&nbsp;</b></td>
								<td align=center class=col_ftr>
									<input id=optional_use_all onclick=\"javascript:check_all(document.fields_to_use,'optional_use');\" type=checkbox>
								</td>
								<td align=center class=col_ftr>
									<input id=optional_require_all onclick=\"javascript:check_all(document.fields_to_use,'optional_require');\" type=checkbox></td>
								<td align=center class=col_ftr>
									<input id=optional_other_box_all onclick=\"javascript:check_all(document.fields_to_use,'optional_other_box');\" type=checkbox>
								</td>
								<td align=center class=col_ftr>
									<input id=optional_dependent_all onclick=\"javascript:check_all(document.fields_to_use,'optional_dependent');\" type=checkbox>
								</td>
								<td class=col_ftr>&nbsp;</td>
								<td align=center class=col_ftr>
									<input onclick=\"reset()\" type=\"button\" value=\"reset form\">
								</td>
							</tr>
						</table></div</div></fieldset>";
                }
                if (!$this->admin_demo()) {
                    $this->body .= "
						<div class='center'>
									<input type=submit value=\"Save\" name=\"auto_save\">
						</div></form>";
                } else {
                    $this->body .= '</div>';
                }
            }
            return true;
        } else {
            //echo $sql_query." is the query<BR>\n";
            return false;
        }
    } //end of function display_registration_configuration_form
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function registration_configuration_home()
    {
        $this->body .= "<table cellpadding=3 cellspacing=0 width=100% border=0 align=center class=row_color1>\n";
        //$this->title = "Registration Configuration";
        $this->description = "In this section you will configure the necessary functional elements
			as well as the applicable registration settings of your site.";
        $this->body .= "<tr class=row_color2>\n\t\t<td align=right valign=top><a href=index.php?mc=registration_setup&page=register_general_settings><span class=medium_font><b>general</b></span></a>\n\t\t</td>\n\t\t
			<td class=medium_font>configure the settings for general settings for registration </a>\n\t\t</td></tr>\n\t";
        $this->body .= "<tr>\n\t\t<td align=right valign=top><a href=index.php?mc=registration_setup&page=register_block_email_domains><span class=medium_font><b>block email domains</b></span></a>\n\t\t</td>\n\t\t<td class=medium_font>blocks the email domains from which the user wants to stop registration </a>\n\t\t</td>\n\t</tr>\n\t";
        $this->body .= "<tr class=row_color2>\n\t\t<td align=right valign=top><a href=index.php?mc=registration_setup&page=register_unapproved><span class=medium_font><b>unapproved registrations</b></span></a>\n\t\t</td>\n\t\t<td class=medium_font>allows manual confirmation of a user's account </a>\n\t\t</td>\n\t</tr>\n\t";
        $this->body .= "</table>\n";
        return true;
    } //end of function registration_configuration_home

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_email_domains($db)
    {
        if (geoPC::is_ent()) {
            $sql_query = "select * from " . $this->block_email_domains;
            $type_result = $this->db->Execute($sql_query);

            if ($this->configuration_data["debug_admin"]) {
                $this->debug_display($db, $this->filename, $this->function_name, "block_email_domains", "get email domains from database");
            }

            if (PHP5_DIR) {
                $menu_loader = geoAdmin::getInstance();
            } else {
                $menu_loader =& geoAdmin::getInstance();
            }


            if (!$type_result) {
                trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
                $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
                $this->body .= $menu_loader->getUserMessages();
                return false;
            }

            $this->body .= $menu_loader->getUserMessages();
            $this->body .= $body;
            $this->description .= $description;

            $this->body .= "<fieldset id='EmailDomains'>
				<legend>Email Domains List</legend><div class='table-responsive'><table cellpadding=3 cellspacing=1 border=0 align=center class='table table-hover table-striped table-bordered'>\n";
            //$this->title = "Registration Setup > Allow / Block Email Domains";
            $this->description = "Control the domains from which the user may register based upon the \"email address\" they enter.";

            $this->body .= "<thead><tr class=col_hdr_top>\n\t<td class=col_hdr_left>\n\t<b>Email Domains ";
            $allow_only = $this->get_site_setting('email_restriction');
            if (! $allow_only) {
                //default to blocked, just in case it didn't get set in the upgrade
                $this->db->set_site_setting('email_restriction', 'blocked');
                $allow_only = 'blocked';
            }
            if ($allow_only == "allowed") {
                $this->body .= "Allowed ";
            } else {
                $this->body .= "Blocked ";
            }
            $this->body .= "</b>\n\t</td>\n\t";
            $this->body .= "<td class=col_hdr>&nbsp;\n\t\n\t</td>\n\t";
            $this->body .= "</tr></thead>\n";
            if ($type_result->RecordCount() == 0) {
                $this->body .= '<tr class=' . $this->get_row_color() . '><td colspan=2><div class="page_note_error">No domains ' . $allow_only . '. Use form below to add a domain.</div></td></tr>';
            }
            while ($show_types = $type_result->FetchRow()) {
                $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td class=medium_font>\n\t" . $show_types["domain"] . " \n\t</td>\n\t";

                $delete_button = geoHTML::addButton('Delete', 'index.php?mc=registration_setup&page=register_block_email_domains&x=' . $show_types["serial_id"] . '&auto_save=1', false, '', 'lightUpLink mini_cancel');
                $this->body .= "<td width=100 align=center>" . $delete_button . "</td>\n\t";

                $this->body .= "</tr>\n";
            }
            if ($show_types > 0) {
                if (!$this->admin_demo()) {
                    $this->body .= "<tr>\n\t<td align=center class=medium_font colspan=3>\n\t<input type=submit name=\"auto_save\" value=\"Save\">\n\t</td>\n</tr>\n";
                }
            }
            //  $this->body .= "<tr>\n\t<td colspan=2 align=center>\n\t";
            //  <a href=index.php?mc=registration_setup&page=block_email_add><span class=medium_font><b>add New Domain</b></span></a>\n\t</td>\n</tr>\n";

            $this->body .= "</td></tr></table></div></fieldset>\n";
            $this->email_domains_form($this->db);
            //$this->body .= "</form>\n";
            //Allow/Block form
            $this->body .= "<form action=index.php?mc=registration_setup&page=register_block_email_domains method='post'>\n";
            $this->body .= "<fieldset id='EmailDomainsSetting'>
				<legend>Allow or Block Setting</legend><div class='table-responsive'><table cellpadding=3 cellspacing=1 border=0 align=center class='table table-hover table-striped table-bordered'>\n";

            $this->body .= "<thead><tr class=col_hdr_top>\n\t";
            $this->body .= "<td class=col_hdr align=center>Allow ONLY<br>in List</td>\n\t<td class=col_hdr align=center>Block ALL<br>in List</td>\n\t<td class=col_hdr></td>\n";
            $this->body .= "</tr></thead>";
            $this->row_count++;
            $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t";
            $this->body .= "<td class=medium_font align=center><input type=radio name=b value=allowed";
            if ($allow_only == "allowed") {
                $this->body .= " checked";
            }
            $this->body .= "></td>\n\t<td class=medium_font align=center><input type=radio name=b value=blocked";
            if ($allow_only == "blocked") {
                $this->body .= " checked";
            }
            $this->body .= "></td>\n\t<td class=medium_font align=center><input type=submit value=\"Save\" name=\"auto_save\"></td>\n";
            $this->body .= "</tr>\n</table></div></fieldset></form>";
            return true;
        }
    } //end of function display_email_domains

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_email_domain($db, $serial_id = 0)
    {
        if ($serial_id) {
            $this->function_name = "update_email_domain";

            //email domain to be deleted
            $sql_query = "delete from " . $this->block_email_domains . " where
				serial_id = " . $serial_id . "";
            $type_result = $this->db->Execute($sql_query);
            if ($this->configuration_data["debug_admin"]) {
                $this->debug_display($db, $this->filename, $this->function_name, "block_email_domains", "delete email domains");
            }
            if (!$type_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            //if this was the last domain in the list, switch back to blacklist instead of whitelist blocking
            $sql = "select serial_id from " . $this->block_email_domains;
            $result = $db->Execute($sql);
            if ($result->RecordCount() == 0) {
                $db->set_site_setting('email_restriction', 'blocked');
            }
            return true;
        } else {
            return false;
        }
    } //end of function update_email_domain

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function email_domains_form($db)
    {
        if (geoPC::is_ent()) {
            $this->body .= "<form action=index.php?mc=registration_setup&page=block_email_add method=post enctype=multipart/form-data class=\"form-horizontal form-label-left\">\n";
            $this->body .= "<fieldset id='EmailDomainsForm'>
				<legend>Add New Domain</legend><div class='x_content'>";
            //$this->title = "Registration Setup > Allow / Block Email Domains > Add New Domain";
            $this->description = "Insert the email domain that needs to be blocked when the registrant uses that domain in the email field
				of the registration process.";

            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Domain to Add: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=\"domain\" class='form-control col-md-7 col-xs-12' placeholder='somedomain.com' size=25>";
            $this->body .= "</div>";
            $this->body .= "</div>";

            if (!$this->admin_demo()) {
                $this->body .= "<div class=\"center\"><input type=submit name=\"auto_save\" value=\"Add To List\"></div>";
            }
            $this->body .= "</div></fieldset>";
            $this->body .= "</form>";
            return true;
        }
    } // end of function email_domains_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_email_domain($db, $email_domain)
    {
        if ($email_domain) {
            $this->function_name = "insert_email_domain";
            $sql_query = "insert into " . $this->block_email_domains . "(domain)
						  values ('" . $email_domain . "')";
            $type_result = $this->db->Execute($sql_query);
            if ($this->configuration_data["debug_admin"]) {
                $this->debug_display($db, $this->filename, $this->function_name, "block_email_domains", "inserting email domain into database");
            }
            if (!$type_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            return true;
        } else {
            return false;
        }
    } // end of function insert_email_domain


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_registration_configuration($db, $config_info = 0)
    {
        //highlight_string(print_r($config_info,1));
        if ($config_info) {
            $site_config_fields = array(
                //"send_register_complete_email_admin",
                //"send_register_complete_email_client",
                //"send_register_attempt_email_admin",
                //"use_email_verification_at_registration",
                //"registration_admin_email",
                "registration_url",
                "use_ssl_in_registration",
                "registration_ssl_url",
                //"admin_approves_all_registration",
                "secret_for_hash"
                );

            //update these in site settings, too (eventually, probably move them there exclusively)
            $this->db->set_site_setting('registration_ssl_url', $config_info['registration_ssl_url']);
            $this->db->set_site_setting('registration_url', $config_info['registration_url']);

            $this->sql_query = "update " . $this->site_configuration_table . " set ";
            foreach ($site_config_fields as $value) {
                if ($value == "registration_url" || $value == "registration_ssl_url") {
                    $this->sql_query .= $value . " = \"" . ($config_info[$value] ? $config_info[$value] : "") . "\", ";
                } else {
                    $this->sql_query .= $value . " = \"" . ($config_info[$value] ? $config_info[$value] : 0) . "\", ";
                }
            }
            $this->sql_query = substr($this->sql_query, 0, -2);//strip off comma
            $result = $this->db->Execute($this->sql_query);
            //clear the settings cache
            geoCacheSetting::expire('configuration_data');
            if ($this->debug_registration_configuration) {
                echo $this->sql_query . "<bR>\n";
            }
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            $reg_config_fields = array(
                "use_registration_company_name_field",
                "require_registration_company_name_field",
                "use_registration_firstname_field",
                "require_registration_firstname_field",
                "use_registration_lastname_field",
                "require_registration_lastname_field",
                "use_registration_email2_field",
                "require_registration_email2_field",
                "use_registration_phone_field",
                "require_registration_phone_field",
                "use_registration_phone2_field",
                "require_registration_phone2_field",
                "use_registration_fax_field",
                "require_registration_fax_field",
                "use_registration_url_field",
                "require_registration_url_field",
                "use_registration_city_field",
                "require_registration_city_field",
                //"use_registration_state_field",
                //"require_registration_state_field",
                "use_registration_zip_field",
                "require_registration_zip_field",
                //"use_registration_country_field",
                //"require_registration_country_field",
                "use_registration_address_field",
                "require_registration_address_field",
                "use_registration_address2_field",
                "require_registration_address2_field",
                "use_registration_business_type_field",
                "require_registration_business_type_field",
                "use_user_agreement_field",
                "firstname_maxlength",
                "lastname_maxlength",
                "company_name_maxlength",
                "address_maxlength",
                "address_2_maxlength",
                "phone_maxlength",
                "phone_2_maxlength",
                "fax_maxlength",
                "city_maxlength",
                "zip_maxlength",
                "url_maxlength"
                );

            $prod_con = geoPC::is_ent();

            for ($i = 1; $i < 11 && $prod_con; $i++) {
                array_push($reg_config_fields, "registration_optional_" . $i . "_field_name");
                array_push($reg_config_fields, "use_registration_optional_" . $i . "_field");
                array_push($reg_config_fields, "require_registration_optional_" . $i . "_field");
                array_push($reg_config_fields, "require_registration_optional_" . $i . "_field_dep");
                array_push($reg_config_fields, "registration_optional_" . $i . "_field_type");
                array_push($reg_config_fields, "registration_optional_" . $i . "_other_box");
                array_push($reg_config_fields, "optional_" . $i . "_maxlength");
            }
            $this->sql_query = "update " . $this->registration_configuration_table . " set ";
            foreach ($reg_config_fields as $value) {
                $this->sql_query .= $value . " = \"" . ($config_info[$value] ? $config_info[$value] : 0) . "\", ";
            }
            $this->sql_query = rtrim($this->sql_query, ' ,');//strip off comma

            $result = $this->db->Execute($this->sql_query);
            if ($this->debug_registration_configuration) {
                echo $this->sql_query . "<bR>\n";
            }
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            $levels = geoRegion::getInstance()->getLevels();
            foreach ($levels as $level => $data) {
                $this->db->set_site_setting('registration_use_region_level_' . $level, $config_info['use_region_level_' . $level]);
                $this->db->set_site_setting('registration_require_region_level_' . $level, $config_info['require_region_level_' . $level]);
            }


            $this->db->set_site_setting('jit_registration', $config_info['jit_registration']);
            $this->db->set_site_setting('jit_allow_user_pass', $config_info['jit_allow_user_pass']);
            $this->db->set_site_setting('jit_require_email_confirmation', $config_info['jit_require_email_confirmation']);
            $this->db->set_site_setting('disable_registration', (isset($config_info['disable_registration']) && $config_info['disable_registration']) ? 1 : false);

            return true;
        }
    } //end of function update_registration_configuration

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_all_dropdowns($db)
    {
        $this->sql_query = "select * from " . $this->registration_choices_types_table . " order by type_name";
        $result = $this->db->Execute($this->sql_query);
        //echo $this->sql_query."<br>\n";

        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }


        if (!$result) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        }
        $this->body .= $menu_loader->getUserMessages();
        $this->body .= $body;
        $this->description .= $description;
        //$this->title = "Registration Setup > General Settings > Registration Pre-Valued Dropdowns";
/*      $this->description = "This is the current list of Pre-Valued Dropdown choices
            that can be used by any customized registration question. <br><br>
            <b>Note:</b>  These are Registration Pre-Valued Dropdown choices that you attach to registration questions that are then displayed with the category
            they are attached to.  These dropdowns will show up as a choice in the \"choices\" category of the add or edit registration question form.
            So create your dropdowns here first then they will then become a choice to attach to a registration question
            <br><br>All Registration Pre-Valued Dropdowns are administered on this page.";*/

        $this->body .= "
		<div class='table-responsive'>
			<table cellpadding=2 cellspacing=0 border=0 align=center width=100%>";

        if ($result->RecordCount() > 0) {
            $this->body .= "
				<tr>
					<td>
						<fieldset id='RegDropdowns'>
				<legend>Current Pre-Valued Dropdowns</legend><table cellspacing=1 cellpadding=2 border=0 align=center class='table table-hover table-striped table-bordered'>
						<thead>
							<tr class='col_hdr_top'>
								<td class=col_hdr_left width=200><b>Name</b> " . geoHTML::showTooltip("Registration Pre-Valued Dropdowns", "Dropdowns created here can be used during registration, used for an <strong>optional registration field</strong>.<br /><br />  To use a registration pre-valued drop down, in <em style=\"white-space: nowrap;\">Registration Setup > General Settings</em>, assign the \"type\" of an optional field to use (at the bottom of the page).") . "</td>
								<td class=col_hdr>&nbsp;</td>
								<td class=col_hdr>&nbsp;</td>
							</tr>
						</thead>";
            $this->row_count = 1;
            while ($show = $result->FetchRow()) {
                $edit_button = geoHTML::addButton('Edit', 'index.php?mc=registration_setup&page=register_pre_valued_edit&c=' . $show["type_id"]);
                $delete_button = geoHTML::addButton('Delete', 'index.php?mc=registration_setup&page=register_pre_valued_edit&d=' . $show["type_id"] . '&auto_save=1', false, '', 'lightUpLink mini_cancel');
                $this->body .= "
							<tr class=" . $this->get_row_color() . ">
								<td class=medium_font>" . $show["type_name"] . "</td>
									<td align=center width=100>" . $edit_button . "</td>
									<td align=center width=100>" . $delete_button . "</td>
							</tr>";
                $this->row_count++;
            }
            $this->body .= "
						</table></fieldset>
					<td>
				</tr>";
        } else {
            $this->body .= "
				<tr>
					<td><div class=\"page_note_error\">There are no current dropdowns.</div></td>
				</tr>";
        }
        $this->body .= "
				<tr>
					<td>";

        $this->body .= "</td>
				</tr>
			</table></div>";

        $this->new_dropdown_form();

        return true;
    } //end of function show_all_dropdowns

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function new_dropdown_form()
    {
        if (!$this->admin_demo()) {
            $this->body .= "<form action=index.php?mc=registration_setup&page=register_pre_valued_add method=post class=\"form-horizontal form-label-left\">\n";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }
        $this->body .= "<fieldset id='RegDropdownForm'>
				<legend>Add New Dropdown Form</legend><div class='x_content'>";
        //$this->title = "Registration Setup > General Settings > Registration Pre-Valued Dropdowns > New";
        $this->description = "Use this form to add a new dropdown to
			the dropdowns usable with the optional question fields in registration.  Type the name below and click \"enter\".  You will then be able to add values to
			the dropdown you have just created.";

        $this->body .= "
				<div class='form-group'>
					<label class='control-label col-md-3 col-sm-3 col-xs-12'>Dropdown Name: </label>
					<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[dropdown_label] class='form-control col-md-7 col-xs-12'>";
        if (isset($this->errors['dropdown_label'])) {
            $this->body .= "Please Enter a Dropdown Name";
        }
        if (!$this->admin_demo()) {
            $this->body .= "<div class=\"center\"><input type=submit name=\"auto_save\" value=\"Add Dropdown\"></div>";
        }
                $this->body .= "</div>
				</div>";

        $this->body .= "</div></fieldset>";
        $this->body .= ($this->admin_demo()) ? '</div>' : "</form>";
        return true;
    } //end of function new_dropdown_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_new_dropdown($db, $information = 0)
    {
        $this->errors = array();
        if (!$information || strlen(trim($information["dropdown_label"])) == 0) {
            $this->errors['dropdown_label'] = 1;
            return false;
        }

        $db = DataAccess::getInstance();

        $sql = "INSERT INTO " . geoTables::registration_choices_types_table . "	(type_name,explanation)	VALUES (?,?)";
        $result = $db->Execute($sql, array($information["dropdown_label"], ''));
        if (!$result) {
            return false;
        }
        $id = $db->Insert_ID();

        //special case: don't allow an ID of 1, since that is reserved for "textarea" fields
        //this table begins with AUTO_INCREMENT=101, so this shouldn't typically be needed
        //but this is done for sanity or of the db gets manually cleared somehow
        if ($id == 1) {
            //do the insert once more (the text is non-unique, so this will make sure we grab the next auto_increment value)
            $db->Execute($sql, array($information["dropdown_label"], ''));
            $id = $db->Insert_ID();
            //kill the old one
            $db->Execute('DELETE FROM ' . geoTables::registration_choices_types_table . ' WHERE `type_id` = 1');
        }

        return $id;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function edit_dropdown($db, $dropdown_id = 0)
    {
        if ($dropdown_id) {
            $this->sql_query = "select * from " . $this->registration_choices_types_table . " where type_id = " . $dropdown_id;

            $result = $this->db->Execute($this->sql_query);

            if (PHP5_DIR) {
                $menu_loader = geoAdmin::getInstance();
            } else {
                $menu_loader =& geoAdmin::getInstance();
            }


            if (!$result) {
                trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
                $this->body .= $menu_loader->getUserMessages();
                return false;
            } elseif ($result->RecordCount() == 1) {
                $this->body .= $menu_loader->getUserMessages();
                $this->body .= $body;
                $this->description .= $description;

                //this dropdown exists
                $show_dropdown = $result->FetchRow();
                $this->sql_query = "select * from " . $this->registration_choices_table . " where type_id = " . $dropdown_id . " order by display_order";
                $result = $this->db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }
                //show the form to edit this dropdown

                $this->body .= "<div class='page-title1'>Current Dropdown: <span class='color-primary-two'>{$show_dropdown['type_name']}</span></div>";

                $this->body .= "<fieldset id='EditRegDropdowns'><legend>Dropdown Values</legend>";
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=registration_setup&page=register_pre_valued_edit&c=" . $dropdown_id . " method=post class='form-horizontal'>";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }
                $this->body .= "<div class='table-responsive'><table class='table table-hover table-bordered table-striped'>";
                $this->body .= '<thead>';
                $this->body .= "<tr><th>Value</td>";
                $this->body .= "<th style='text-align: center;'>Display Order</td>";
                $this->body .= "<th></td></tr>";
                $this->body .= '</thead><tbody>';
                if ($result->RecordCount() > 0) {
                    //this dropdown exists
                    //show the value in a list
                    $this->row_count = 0;
                    while ($show = $result->FetchRow()) {
                        $this->body .= "<tr>\n\t\t<td class=medium_font align=left>\n\t" . $show["value"] . " \n\t\t</td>\n\t\t";
                        $this->body .= "<td class=medium_font align=center>\n\t" . $show["display_order"] . " \n\t\t</td>\n\t\t";
                        //$this->body .= "<form action=index.php?mc=registration_setup&page=register_pre_valued_edit&g=".$show["value_id"]."&c=".$dropdown_id." method=post>";
                        $this->body .= "<input type=hidden name=\"auto_save\" value=1>";
                        $this->body .= "<td align=center width=100>
						<a href='index.php?mc=registration_setup&page=register_pre_valued_edit&g={$show["value_id"]}&c={$dropdown_id}&auto_save=1' class='btn btn-xs btn-danger lightUpLink'><i class='fa fa-trash-o'></i> Delete</a></td>";
                    }
                }

                $this->body .= "<tr>
									<td class='col_ftr' style='text-align: center;'>
										<div class='form-group'>
											<label class='col-xs-12 col-sm-4 control-label'>Enter New Value: </label>
											<div class='col-xs-12 col-sm-7'><input type=text name=b[value] size=25 class='form-control'></div>
										</div>
									</td>
									<td class=col_ftr align=center>
										<div class='form-group'>
											<label class='col-xs-12 col-sm-4 control-label'>Display Order: </label>
											<div class='col-xs-12 col-sm-7'><input type='number' min='0' name=b[display_order] value='1' size=3 class='form-control'></div>
										</div>
									</td>";

                $this->body .= "<td align=center class=col_ftr>";
                if (!$this->admin_demo()) {
                    $this->body .= "<input type=submit name=\"auto_save\" value=\"Add\">";
                }
                $this->body .= "</td>";
                $this->body .= "</tr>\n\t";

                $this->body .= "</tbody></table>";
                $this->body .= "</div>" . (!$this->admin_demo() ? '</form>' : '</div>') . "</fieldset>\n";
                $this->body .= "
				<div style='padding: 5px;'><a href=index.php?mc=registration_setup&page=register_pre_valued class='back_to'>
				<i class='fa fa-backward'></i> Back to Registration Pre-Valued Dropdowns</a></div>";
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function edit_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function add_dropdown_value($db, $dropdown_id = 0, $information = 0)
    {
        if (($information) && ($dropdown_id) && geoPC::is_ent()) {
            if (strlen(trim($information["value"])) > 0) {
                $this->sql_query = "insert into " . $this->registration_choices_table . "
					(type_id,value,display_order)
					values
					(" . $dropdown_id . ",\"" . $information["value"] . "\"," . $information["display_order"] . ")";
                $result = $this->db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }
                $id = $this->db->Insert_ID();
                return $id;
            } else {
                $this->sql_query = "insert into " . $this->registration_choices_table . "
					(type_id,value,display_order)
					values
					(" . $dropdown_id . ",\"" . $information["value"] . "\"," . $information["display_order"] . ")";
                $result = $this->db->Execute($this->sql_query);
                if (!$result) {
                    return false;
                }
                $id = $this->db->Insert_ID();
                return $id;
                return false;
            }
        } else {
            return false;
        }
    } //end of function add_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown_value($db, $value_id = 0)
    {
        if ($value_id) {
            $this->sql_query = "delete from " . $this->registration_choices_table . " where value_id = " . $value_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    } //end of function delete_dropdown_value

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown_intermediate($db, $dropdown_id = 0)
    {
        if ($dropdown_id) {
            $this->sql_query = "select * from " . $this->registration_choices_types_table . " where type_id = " . $dropdown_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                return false;
            } elseif ($result->RecordCount() == 1) {
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=registration_setup&page=register_pre_valued_edit&d=" . $dropdown_id . " method=post>\n";
                }
                $this->body .= "<table cellpadding=2 cellspacing=0 border=0 class=row_color1 width=\"100%\">\n";
                //$this->title = "Registration Setup > General Settings > Registration Pre-Valued Dropdowns > Delete";
                $this->description = "If the registration question dropdown you are trying to delete
					is attached to existing questions you will need to re-attach them to another.";
                $show_dropdown = $result->FetchRow();
                if (!$this->admin_demo()) {
                    $this->body .= "<input type=hidden name=z[type_of_submit] value=\"delete dropdown\">";
                    $this->body .= "<tr>\n\t<td class=medium_font_light align=center>
						<input type=hidden name=z[type_of_submit] value=\"delete dropdown\">
						<input type=submit name=\"auto_save\"
						value=\"delete dropdown\"> \n\t</td>\n</tr>\n";
                }
                $this->body .= "</table>\n";

                //show the delete from db (and everywhere else
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function delete_dropdown_intermediate

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_dropdown($db, $dropdown_id = 0)
    {
        $this->sql_query = "delete from " . $this->registration_choices_table . " where type_id = " . $dropdown_id;
        //echo $this->sql_query."<br>\n";
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            return false;
        }

        $this->sql_query = "delete from " . $this->registration_choices_types_table . " where type_id = " . $dropdown_id;
        //echo $this->sql_query."<br>\n";
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            return false;
        }
        return true;
    } //end of function delete_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_registration_confirmation_form($db)
    {
        require_once(CLASSES_DIR . "site_class.php");
        require_once(CLASSES_DIR . "register_class.php");

        $language_id = $HTTP_COOKIE_VARS["language_id"];
        $register = new Register($language_id, $auction_session, $this->product_configuration);

        if ((isset($_GET['resend']) && isset($_GET['c'])) && $_GET['resend'] == 1) {
            $admin = true;
            include GEO_BASE_DIR . 'get_common_vars.php';

            $sql_query = "select * from " . $this->confirm_table . " WHERE username = ?";
            $result = $this->db->Execute($sql_query, array($_GET['c']));
            if ($result) {
                $row = $result->FetchNextObject();
                //if using e-mail verification, then admin approves all registration
                //must be turned off.
                $register->page_id = 20;
                $register->get_text();

                if ($this->db->get_site_setting('use_ssl_in_registration')) {
                    $return_url = trim($this->db->get_site_setting('registration_ssl_url'));
                } else {
                    $return_url = trim($this->db->get_site_setting('registration_url'));
                }
                $confirmurl = ($return_url . "?b=3&hash=" . "$row->ID" . "&username=" . urlencode($row->USERNAME));

                $mailto = $row->EMAIL;

                $tpl = new geoTemplate('system', 'emails');
                $tpl->assign('introduction', $register->messages[672]);
                $tpl->assign('salutation', $register->get_salutation($row));
                $tpl->assign('messageBody', $register->messages[229]);
                $tpl->assign('usernameLabel', $register->messages[1329]);
                $tpl->assign('username', $row->USERNAME);
                $tpl->assign('emailLabel', $register->messages[1331]);
                $tpl->assign('email', $row->EMAIL);
                $tpl->assign('confirmLinkInstructions', $register->messages[230]);
                $tpl->assign('confirmLink', $confirmurl);
                $tpl->assign('finalInstructions', $register->messages[231]);
                $message = $tpl->fetch('registration/registration_verification.tpl');
                $subject = urldecode($register->messages[228]);

                $from = $this->db->get_site_setting('registration_admin_email');
                trigger_error('DEBUG STATS: Sending Verification E-Mail: PRE');
                geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/html');
                trigger_error('DEBUG STATS: Sending Verification E-Mail: POST');

                $admin->userSuccess('Email Sent successfully');
                $this->body .= $admin->getUserMessages();
            } else {
                $admin->userFailure('Email could not be sent.');
                $this->body .= $admin->getUserMessages();
            }
        }


        $register->expire_confirmations();

        $sql_query = "select * from " . $this->confirm_table;
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        }
        $this->body .= "<fieldset id='RegNeedApprov'>
				<legend>Registrations Awaiting Approval</legend><table cellpadding=3 cellspacing=1 border=0 width=\"100%\">\n";
        //$this->title = "Registration Setup > Unapproved Registrations";
        $this->description = "The table below displays a list of those users who have registered but have not yet confirmed/finalized
				their registration process. This may be because they never received the registration confirmation email due to an invalid email
				address or due to spam filtering. You can manually confirm each user below individually.";

        if ($result->RecordCount() != 0) {
            $this->body .= "<tr><td align=center width=25% class=col_hdr>Name</td><td align=center width=25% class=col_hdr>E-mail Address</td><td align=center width=25% class=col_hdr>IP Address</td><td width=\"25%\" class=col_hdr>&nbsp;</td></tr>\n";

            $this->row_count = 0;
            // Start displaying data
            while ($show_confirmation = $result->FetchRow()) {
                $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t<td align=center width=33% class=medium_font>\n\t";
                $this->body .= $show_confirmation["firstname"] . " " . $show_confirmation["lastname"] . "<br>";
                $this->body .= "username: " . $show_confirmation["username"] . "<br>\n";
                $this->body .= "address: " . $show_confirmation["address"] . " " . $show_confirmation["address_2"] . "<br>\n";
                $this->body .= $show_confirmation["city"] . " " . $show_confirmation["state"] . " " . $show_confirmation["country"] . " " . $show_confirmation["zip"] . "<br>\n";
                if (strlen($show_confirmation["phone"]) > 0) {
                    $this->body .= "phone: " . $show_confirmation["phone"] . "<br>\n";
                }
                if (strlen($show_confirmation["phone_2"]) > 0) {
                    $this->body .= "phone 2: " . $show_confirmation["phone_2"] . "<br>\n";
                }
                if (strlen($show_confirmation["fax"]) > 0) {
                    $this->body .= "fax: " . $show_confirmation["fax"] . "<br>\n";
                }
                if (strlen($show_confirmation["company_name"]) > 0) {
                    $this->body .= "company name: " . $show_confirmation["company_name"] . "<br>\n";
                }
                if (strlen($show_confirmation["url"]) > 0) {
                    $this->body .= "url: " . $show_confirmation["url"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_1"]) > 0 && $show_confirmation["optional_field_1"] != "0") {
                    $this->body .= "optional field 1: " . $show_confirmation["optional_field_1"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_2"]) > 0 && $show_confirmation["optional_field_2"] != "0") {
                    $this->body .= "optional field 2: " . $show_confirmation["optional_field_2"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_3"]) > 0 && $show_confirmation["optional_field_3"] != "0") {
                    $this->body .= "optional field 3: " . $show_confirmation["optional_field_3"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_4"]) > 0 && $show_confirmation["optional_field_4"] != "0") {
                    $this->body .= "optional field 4: " . $show_confirmation["optional_field_4"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_5"]) > 0 && $show_confirmation["optional_field_5"] != "0") {
                    $this->body .= "optional field 5: " . $show_confirmation["optional_field_5"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_6"]) > 0 && $show_confirmation["optional_field_6"] != "0") {
                    $this->body .= "optional field 6: " . $show_confirmation["optional_field_6"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_7"]) > 0 && $show_confirmation["optional_field_7"] != "0") {
                    $this->body .= "optional field 7: " . $show_confirmation["optional_field_7"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_8"]) > 0 && $show_confirmation["optional_field_8"] != "0") {
                    $this->body .= "optional field 8: " . $show_confirmation["optional_field_8"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_9"]) > 0 && $show_confirmation["optional_field_9"] != "0") {
                    $this->body .= "optional field 9: " . $show_confirmation["optional_field_9"] . "<br>\n";
                }
                if (strlen($show_confirmation["optional_field_10"]) > 0 && $show_confirmation["optional_field_10"] != "0") {
                    $this->body .= "optional field 10: " . $show_confirmation["optional_field_10"] . "<br>\n";
                }

                $this->body .= "</td>\n\t";

                $approve_button = geoHTML::addButton('Approve', 'index.php?mc=registration_setup&page=register_confirm_user&c=' . $show_confirmation["username"] . '&auto_save=2', false, '', 'lightUpLink mini_button');
                $delete_button = geoHTML::addButton('Delete', 'index.php?mc=registration_setup&page=register_delete_user&c=' . $show_confirmation["id"] . '&auto_save=1', false, '', 'lightUpLink mini_cancel');

                $this->body .= "
					<td align=center width=25% class=medium_font>
						" . $show_confirmation["email"] . "
					</td>
					<td align=center width=25% class=medium_font>
						" . geoString::fromDB($show_confirmation["user_ip"]) . "
					</td>
					<td align=center width=25% class=medium_font>
						" . $approve_button . "
						" . $delete_button . "
					";
                if ($this->db->get_site_setting('use_email_verification_at_registration')) {
                    $this->body .= geoHTML::addButton('resend confirmation email', "index.php?mc=registration_setup&page=register_unapproved&resend=1&c=" . $show_confirmation["username"]);
                }

                $this->body .= "</td>
				</tr>";
                $this->row_count++;
            }

            $this->body .= "</table></fieldset>";
        } else {
            $this->body .= "<table cellpadding=3 cellspacing=0 border=0 width=\"100%\">\n";
            $this->body .= "<tr>\n\t<td align=center class=medium_font>\n\t";
            $this->body .= '<div class="page_note_error">No new registrations are waiting to be confirmed.</div>';
            $this->body .= '</td></tr></table>';
        }

        return true;
    } //end of function display_registration_configuration_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_registration_confirmation($db, $confirmation_info = 0)
    {
        if ($this->debug_registration_configuration) {
            echo "<bR>TOP OF UPDATE_REGISTRATION_CONFIRMATION<BR>\n";
        }
        if ($confirmation_info) {
            require_once(CLASSES_DIR . "register_class.php");

            if ($this->debug_registration_configuration) {
                echo $confirmation_info["id"] . " is id<BR>\n";
                echo $confirmation_info["username"] . " is username<BR>\n";
            }

            $language_id = $HTTP_COOKIE_VARS["language_id"];
            $register = new Register($language_id, $auction_session, $this->product_configuration);

            if (!$register->confirm($confirmation_info["id"], $confirmation_info["username"], '1')) {
                header("Location: " . $_SERVER["PHP_SELF"] . "?mc=registration_setup&page=register_unapproved");
                exit;
            }

            //$this->title = "Registration Confirmation";
            $sql_query = "delete from geodesic_confirm where username='" . $confirmation_info["username"] . "'";
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $this->body .= 'Error deleting from confirm table.<br>';
                echo $this->db->ErrorMsg() . "<BR>\n";
                echo $sql_query . "<bR>\n";
            }

            $this->body .= "<fieldset id='ConfirmReg'><legend>Registrations Awaiting Approval</legend><table cellpadding=3 cellspacing=0 border=0 width=\"100%\">\n";
            $this->body .= "<tr class=row_color1>\n\t<td align=center class=medium_font>\n\t";
            $this->body .= "<br><br>User <b>" . $confirmation_info["username"] . "</b> has been confirmed.<br><br><br>";
            $this->body .= "<a href=index.php?mc=registration_setup&page=register_unapproved><strong>Back to Unapproved Registrations</strong></a>";
            $this->body .= "</td></tr></table></fieldset>";
        } else {
            header("Location: " . $_SERVER["PHP_SELF"] . "?mc=registration_setup&page=register_unapproved");
            exit;
        }
    } //end of function update_registration_configuration

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_general_settings()
    {
        if (!$this->display_registration_configuration_form($this->db)) {
            return false;
        }
        $this->display_page();
    }
    function update_register_general_settings()
    {
        return $this->update_registration_configuration($this->db, $_REQUEST["b"]);
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_block_email_domains()
    {
        $this->display_email_domains($this->db);
        $this->display_page();
    }
    function update_register_block_email_domains()
    {
        if ($_REQUEST["b"] == "allowed" || $_REQUEST["b"] == "blocked") {
            if ($this->set_site_setting('email_restriction', $_REQUEST["b"])) {
                return true;
            } else {
                return false;
            }
        } else {
            return $this->update_email_domain($this->db, $_REQUEST["x"]);
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_block_email_add()
    {
        $this->display_register_block_email_domains($this->db);
    }
    function update_block_email_add()
    {
        if ($_REQUEST["domain"]) {
                //insert new email domain
            return $this->insert_email_domain($this->db, $_REQUEST["domain"]);
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_unapproved()
    {
        $this->display_registration_confirmation_form($this->db);
        $this->display_page();
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_pre_valued()
    {
        $this->show_all_dropdowns($this->db);
        $this->display_page();
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_pre_valued_add()
    {
        $this->display_register_pre_valued();
    }
    function update_register_pre_valued_add()
    {
        return $this->insert_new_dropdown($this->db, $_REQUEST["b"]) ;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_pre_valued_edit()
    {
        if ($_REQUEST["c"]) {
            $this->edit_dropdown($this->db, $_REQUEST["c"]);
            $this->display_page();
        } elseif ($_REQUEST["z"]) {
            $admin = $_SERVER['PHP_SELF'];
            header("Location: " . $admin . "?mc=registration_setup&page=register_pre_valued");
        } else {
            $this->display_register_pre_valued();
        }
    }
    function update_register_pre_valued_edit()
    {
        if ($_REQUEST["b"]) {
            return $this->add_dropdown_value($this->db, $_REQUEST["c"], $_REQUEST["b"]);
        }
        if ($_REQUEST["g"]) {
            return $this->delete_dropdown_value($this->db, $_REQUEST["g"]);
        }
        if ($_REQUEST["d"]) {
            return $this->delete_dropdown($this->db, $_REQUEST["d"]);
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_confirm_user()
    {
        $this->display_page();
    }
    function update_register_confirm_user()
    {
        $sql_query = "SELECT * FROM geodesic_confirm WHERE username='" . $_REQUEST["c"] . "'";
        $confirm_result = $this->db->Execute($sql_query);
        if (!$confirm_result) {
            return false;
        } else {
            $confirm_info = $confirm_result->FetchRow();
            $this->update_registration_confirmation($this->db, $confirm_info);
        }
        return true;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_register_delete_user()
    {
        $this->display_registration_confirmation_form($this->db);
        $this->display_page();
    }
    function update_register_delete_user()
    {
        $id = $_POST['c'];
        if ($id) {
            $sql_query = "DELETE FROM geodesic_confirm WHERE id=?";
            $delete_result = $this->db->Execute($sql_query, array($id));
            if (!$delete_result) {
                return false;
            }
            $id = (int)$id;
            $sql_query = "DELETE FROM geodesic_confirm_email WHERE id='$id'";
            $delete_result = $this->db->Execute($sql_query);
            if (!$delete_result) {
                return false;
            }

            return true;
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} //end of class Registration_configuration
