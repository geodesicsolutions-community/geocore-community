<?php

// admin_site_configuration_class.php
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
##
##    17.12.0-12-g1fa40c1
##
##################################

class Site_configuration extends Admin_site
{



    var $internal_error_message = "There was an internal error";
    var $data_error_message = "Not enough data to complete request";
    var $page_text_error_message = "No text connected to this page";
    var $no_pages_message = "No pages to list";
    var $site_configuration_message;
    var $debug_site = 0;
    function display_general_configuration_form($db = 0)
    {
        $admin = geoAdmin::getInstance();
        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
    // Set title and text for tooltip
        $this->body .= "Text[1] = [\"Site URL\", \"Enter the FULL URL to link directly to index.php (http://www.example.com/index.php).\"]\n
			Text[2] = [\"File Name\", \"Enter the FILE NAME ONLY of the base file.  This file was sent to you as index.php but you have the ability to rename this file if you like.  Just make sure you put the name of that file here.\"]\n
			Text[3] = [\"Secure SSL Site URL\", \"Enter the FULL SSL URL to link directly to index.php (https://www.example.com/index.php). IMPORTANT: You must have an SSL Certificate installed on your domain in order for this to work. If you are unsure, please contact your host and ask BEFORE USING ANY SSL SETTINGS.\"]\n
			Text[4] = [\"Use SSL Connection for 'Listing' Process\", \"If you are planning on accepting credit cards to place listings on your site this is STRONGLY RECOMMENDED (WE RECOMMEND THIS SO STRONGLY THAT WE SUGGEST YOU DO NOT ACCEPT CREDIT CARDS WITHOUT THIS PROTECTION FOR YOUR SITE). We also recommend a ssl certificate for your domain.  This protects the credit card information from getting stolen between the clients browser and your site.\"]\n
			Text[6] = [\"Email Configuration\", \"There are three different configurations to send email.  Your host determines which setting you need.  There are different levels of spam protection and then just different configurations on smtp servers that determine which setting is necessary.  Start with setting 1.  Try sending emails from anywhere within the site.  If no emails are sent go down to the next setting and try again until the email are being sent.  Configuration number 3 will be necessary for Yahoo hosting clients.  Other hosts vary in their configuration.\"]\n
			Text[7] = [\"Email header break configuration\", \"There are two different possibilities for the header \\\"divider\\\".  The official PHP/RFC position is to separate your email header with a return and newline characters.  But several servers do not recognize these very well.  The next configuration is to separate the email headers with just a newline character.\"]\n
			Text[8] = [\"BCC admin on user communication\", \"Enter an email address here to have a blind copy of all notify friend and notify seller email sent.  If this address is left empty no email will be sent.  This does not affect communications sent from within communication section.\"]\n
			Text[9] = [\"Admin Approves all Listings\", \"Set this control to yes and the admin will have to approve every listing before it will be exposed on the client side.\"]\n
			Text[10] = [\"Notify admin when an listing is edited\", \"Set this control to yes and the admin will be notified by email of a listing that has been modified by a user.\"]\n
			Text[11] = [\"Number of levels of categories to display in dropdown\", \" If you have a large number of categories you may experience page load problems on pages where the category dropdown appears (such as the search page or pages where the search module appears).  This makes for a very long dropdown and long page load times.  To alleviate these issues you can choose to display a certain number of levels of categories within the dropdown. This will shorten the page load times and shrink the dropdown to a manageable size.  If set to 0 all levels of categories will be displayed.\"]\n
			Text[12] = [\"Administration reply email\", \"This used in all email communications as the reply to email address.\"]\n
			Text[13] = [\"API Module Installed\", \"Choose \\\"yes\\\" ONLY if you have purchased and installed the API Module from Geodesic Solutions. The API Module allows you to synchronize user registrations between multiple installations of Geodesic Solutions software that are installed on the same domain. The \\\"API Module\\\" installation file will provide more instructions.\"]\n
			Text[14] = [\"Switch for use of built-in CSS\", \"Choose whether to use side wide built in css or use your own.  This will most of the time be set to yes unless you use your own CSS files.\"]\n
			Text[15] = [\"Activate IDevAffiliate Integration\", \"Turn on the IDevAffiliate software integration package.\"]\n
			Text[18] = [\"Absolute Path to IDevAffiliate sale.php\", \"The absolute path to sale.php included with IDevAffiliate(trailing slash required).<br><br>i.e. C:/Apache2/htdocs/IdevAffiliate/ or /var/www/idevaffiliate/\"]\n
			Text[19] = [\"Character Set Used\", \"Choose the same character set that you wish to use on the front side here.  This will set the character set on the admin side.  Then place the correct character set for the type of text used on the site in the templates on the front side.\"]\n
			Text[20] = [\"Site On/Off Switch\", \"Checking the \\\"Off\\\" switch will re-direct all visitors to the \\\"Under Maintenance\\\" page specified in the \\\"Site On/Off URL\\\" setting below. As the site administrator you can still access your site by one of two options: <br>1) Before setting to \\\"Off\\\" login to your site with your Admin username and password. <br>2) Add your IP Address to \\\"Allowed IPs When Site Disabled\\\" (Admin Tools & Settings > Security Settings > General Security Settings). \"]\n
			Text[21] = [\"Site On/Off Url\", \"Set the url to a page that will display a message that the site is disabled. The url set here must be to a file outside of the software(i.e. an external html file). The admin can still browse the site when it is disabled but must be logged into the client side as the admin prior to disabling. \"]\n
			Text[22] = [\"Time offset\", \"Set the offset from the server clock that the software will use. This is especially useful when you are located in a different time-zone than your host. \"]\n
			Text[23] = [\"Allow Creation Of\", \"Select the listing type(s) for which you want to enable new listings. If a listing type is turned off here, existing listings of that type will still be viewable, but no new ones may be created.\"]\n
			Text[24] = [\"Storefront Url\", \"Enter the url to link directly to stores.php (http://www.somesite.com/stores.php) - this is the original name of the file.  You should change here accordingly.\"]\n
			Text[25] = [\"External Media URL\", \"Enter the url to use for external media, such as images, CSS, and/or JS.  This is typically the same as the Site URL without the base file name (index.php) at the end.  For instance if your site URL is <em style='white-space: nowrap;'>http://example.com/listing/index.php</em>, then you set the external media URL to be <strong style='white-space: nowrap;'>http://example.com/listing/</strong>.<br /><br />If you wish, this can use a different domain from your main site, as long as the domain name document root is set to the same as your main website.  Using a configuration like this can help speed up page loads.  See the user manual for more information.\"]
			"
            ;
    //".$this->show_tooltip(14,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
        $this->body .= "</script>";
        $this->sql_query = "select * from " . $this->site_configuration_table;
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $admin->userError("Internal error. Please contact support</a>.");
            $this->body .= $admin->getUserMessages();
            return false;
        } elseif ($result->RecordCount() == 1) {
            $this->body .= $admin->getUserMessages();
            $show_configuration = $result->FetchRow();
            $this->body .= "<form action='index.php?mc=site_setup&page=main_general_settings' method='post' class='form-horizontal form-label-left'>";
            $this->body .= "<fieldset><legend>URL Settings</legend>";
            $this->body .= "<div class='x_content'>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Site URL: " . $this->show_tooltip(1, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[classifieds_url] class='form-control col-md-7 col-xs-12' value=\"" . $this->db->get_site_setting('classifieds_url') . "\">
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Base File Name: " . $this->show_tooltip(2, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[classifieds_file_name] class='form-control col-md-7 col-xs-12' value=\"" . $this->db->get_site_setting('classifieds_file_name') . "\">
				</div>
			</div>";

            $siteNameTip = geoHTML::showTooltip(
                'Site Name',
                'Enter a "human-readable" name for your site. This will automatically be used in several places, such as the site footer and the Terms of Use page<br />
					<br >If left blank, templates that make use of the {$site_name} variable will instead show the site\'s domain name.'
            );
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Site Name: " . $siteNameTip . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[friendly_site_name] class='form-control col-md-7 col-xs-12' value=\"" . $this->db->get_site_setting('friendly_site_name') . "\">
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Secure SSL Site URL: " . $this->show_tooltip(3, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[classifieds_ssl_url] class='form-control col-md-7 col-xs-12' value=\"" . $this->db->get_site_setting('classifieds_ssl_url') . "\">
				</div>
			</div>";
        //secure ssl during listing process
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Use SSL For: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
				<input type='checkbox' id='ssl_only' name='b[use_ssl_only]' value='1' ";
            if ($this->db->get_site_setting('use_ssl_only')) {
                $this->body .= "checked='checked'";
            }
                $this->body .= " /> <strong>All Pages</strong>
				<br />
				<input type='checkbox' class='ssl_check' name='b[use_ssl_in_sell_process]' value='1' ";
            if ($this->db->get_site_setting('use_ssl_in_sell_process')) {
                $this->body .= "checked='checked'";
            }
                $this->body .= " /> 'Listing' Process
				<br />
				<input type='checkbox' class='ssl_check' name='b[use_ssl_in_login]' value='1' ";
            if ($this->db->get_site_setting('use_ssl_in_login')) {
                $this->body .= "checked='checked'";
            }
                $this->body .= " /> User Login
				<br />
				<input type='checkbox' class='ssl_check' name='b[use_ssl_in_user_manage]' value='1' ";
            if ($this->db->get_site_setting('use_ssl_in_user_manage')) {
                $this->body .= "checked='checked'";
            }
                $this->body .= " /> User Management Pages
				</div>
			</div>";

                $this->body .= "
				<script>
					jQuery('#ssl_only').change(function(){
						if(jQuery('#ssl_only').prop('checked')) {
							jQuery('.ssl_check').prop('checked',true).prop('disabled',true);
						} else {
							jQuery('.ssl_check').prop('disabled',false);
						}
					});
					jQuery(document).ready(function(){
						jQuery('#ssl_only').change();
					});
				</script>";
        //force ssl
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Force SSL URL: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=checkbox class='ssl_check' name=b[force_ssl_url] value='1'" . (($this->db->get_site_setting('force_ssl_url')) ? ' checked="checked"' : '') . " />
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Site On/Off Switch: " . $this->show_tooltip(20, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[site_on_off] value=0 ";
            if ($show_configuration["site_on_off"] == 0) {
                $this->body .= "checked";
            }
                $this->body .= "> On<br><input type=radio name=b[site_on_off] value=1 ";
            if ($show_configuration["site_on_off"] == 1) {
                $this->body .= "checked";
            }
                $this->body .= "> Off
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Site On/Off URL: " . $this->show_tooltip(21, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=b[disable_site_url] class='form-control col-md-7 col-xs-12' value=\"" . $show_configuration["disable_site_url"] . "\">
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Use 404 status code: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type='checkbox' name='b[use_404]' value='1'" . (($this->db->get_site_setting('use_404')) ? ' checked="checked"' : '') . " />
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Add noindex to sorted pages: </label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input type='checkbox' name='b[noindex_sorted]' value='1'" . (($this->db->get_site_setting('noindex_sorted')) ? ' checked="checked"' : '') . " />
				</div>
			</div>";
            $this->body .= "</div></fieldset>";
            $this->body .= "<fieldset><legend>Miscellaneous Site Settings</legend>";
            $this->body .= "<div class='x_content'>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Allow Creation Of: " . geoHTML::showTooltip('Allow Creation Of', 'Select the listing type(s) for which you want to enable new listings. If a listing type is turned off here, existing listings of that type will still be viewable, but no new ones may be created.') . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'>";
            if (geoMaster::is('classifieds')) {
                $this->body .= '<input type="checkbox" name="b[allow_new_classifieds]" ' . ($this->db->get_site_setting('allow_new_classifieds') ? 'checked="checked"' : '') . ' value="1" /> Classifieds<br />';
            }
            if (geoMaster::is('auctions')) {
                $this->body .= '<input type="checkbox" name="b[allow_new_auctions]" ' . ($this->db->get_site_setting('allow_new_auctions') ? 'checked="checked"' : '') . ' value="1" /> Auctions<br />';
            }
                $this->body .= "</div>
			</div>";
        //Company address
            $tooltip = geoHTML::showTooltip('Company Address', "This is the company's address information (where payments should be sent, contact info, etc).<br /><br />
It will primarily be used to display in invoices generated by the software.<br /><br />
");
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Company Address: " . $tooltip . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><textarea name='b[company_address]' rows='5' cols='35'>" . geoString::specialChars($this->db->get_site_setting('company_address', 1)) . "</textarea>
				</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Server Time Offset: " . $this->show_tooltip(22, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12 input-group'><select class='form-control col-md-7 col-xs-12' name=b[time_shift]>";
            for ($i = -23; $i < 24; $i++) {
                $this->body .= "<option ";
                if ($i == $show_configuration["time_shift"]) {
                    $this->body .= "selected";
                }
                $this->body .= " value=" . $i . ">" . $i . "</option>";
            }
                    $this->body .= "</select> <div class='input-group-addon'>Hours</div>";
            $this->body .= "</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Levels of categories in Dropdown (client side): " . $this->show_tooltip(11, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><select class='form-control col-md-7 col-xs-12' name=b[levels_of_categories_displayed]>";
            for ($i = 0; $i < 50; $i++) {
                $this->body .= "<option ";
                if ($i == $show_configuration["levels_of_categories_displayed"]) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>";
            }
                    $this->body .= "</select>";
            $this->body .= "</div>
			</div>";
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Levels of categories in Dropdown (admin): " . $this->show_tooltip(11, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><select class='form-control col-md-7 col-xs-12' name=b[levels_of_categories_displayed_admin]>";
            for ($i = 0; $i < 10; $i++) {
                $this->body .= "<option ";
                if ($i == $show_configuration["levels_of_categories_displayed_admin"]) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>";
            }
                    $this->body .= "</select>";
            $this->body .= "</div>
			</div>";
        // Show Powered By in Site Footer
            $this->body .= "<div class='form-group'><label class='control-label col-md-5 col-sm-5 col-xs-12'>Show \"Powered By\":</label>";
            $show = ($this->db->get_site_setting('remove_powered_by') && !geoPC::force_powered_by()) ? '' : ' checked="checked"';
            $remove = ($show) ? '' : ' checked="checked"';
            $extra = '';
            if (geoPC::force_powered_by()) {
                $extra = ' ' . geoHTML::showTooltip('Remove Branding', 'Branding can be removed for an additional monthly fee.');
            } elseif (geoPC::is_trial()) {
                $extra = ' ' . geoHTML::showTooltip('Remove Branding', 'Branding cannot be removed for trial licenses.');
            }
                $extra_label = '';
            if ($extra) {
                $remove .= " disabled='disabled'";
                $extra_label = ' class="disabled"';
            }
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
				<input type='radio' name='b[remove_powered_by]' value='0'{$show} /> Show<br />
				<input type='radio' name='b[remove_powered_by]' value='1'{$remove} /> Remove Branding$extra
				</div>
			</div>";
            $this->body .= "</div></fieldset>";
            $this->body .= "<fieldset id='iDevControls'><legend>IDevAffiliate Integration</legend>";
            $this->body .= "<div>";
            if ($this->db->get_site_setting('idevaffiliate') == 1) {
                $useiDev = "checked='checked'";
                $iDevControlsDisplay = "";
            } else {
                $useiDev = "";
                $iDevControlsDisplay = "display: none";
            }
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Activated" . $this->show_tooltip(15, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type='hidden' name='b[idevaffiliate]' value='0' />
							<input type='checkbox' name='b[idevaffiliate]' value='1' $useiDev id='useiDev' onclick=\"if ($('useiDev').checked) $('iDevControl').show(); else $('iDevControl').hide();\" /> IDev Activated
							</div>
							</div>";
            $this->body .= "
			<div class='form-group' id='iDevControl' style='$iDevControlsDisplay'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Absolute Path to IDevAffiliate sale.php: " . $this->show_tooltip(18, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type='text' name='b[idev_path]' class='form-control col-md-7 col-xs-12' size='60' maxsize='100' value=\"" . geoString::specialChars($this->db->get_site_setting("idev_path")) . "\" />
				</div>
				<div class='clearColumn'></div>
			</div>
			";
            $this->body .= "</div></fieldset>

			<fieldset>
				<legend>Character Encoding</legend>
				<div class='x_content'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Character Set (CHARSET) Currently Used in Admin: " . $this->show_tooltip(19, 1) . "</label>";
            $known_charsets = array (
                'custom' => 'Custom',
                'utf-8' => 'utf-8',
                'iso-8859-1' => 'iso-8859-1',
                'iso-8859-2' => 'iso-8859-2 (latin2)',
                'iso-8859-3' => 'iso-8859-3 (latin3)',
                'iso-8859-4' => 'iso-8859-4',
                'iso-8859-6' => 'iso-8859-6 (asmo-708)',
                'iso-8859-7' => 'iso-8859-7 (latin/greek)',
                'iso-8859-8-i' => 'iso-8859-8-i',
                'iso-8859-9' => 'iso-8859-9',
                'iso-2022-jp' => 'iso-2022-jp',
                'windows-1251' => 'windows-1251',
                'euc-jp' => 'euc-jp',
                'euc-cn' => 'euc-cn',
                'x-mac-chinesesimp'  => 'x-mac-chinesesimp',
                'x-mac-chinesetrad' => 'x-mac-chinesetrad',
                'x-mac-japanese' => 'x-mac-japanese',
                'x-mac-korean' => 'x-mac-korean',
                'x-mac-turkish' => 'x-mac-turkish',
                'x-mac-cyrillic' => 'x-mac-cyrillic',
                'x-mac-arabic' => 'x-mac-arabic',
                'x-mac-icelandic' => 'x-mac-icelandic',
                'cp-936' => 'cp-936',
                'windows-1256' => 'windows-1256',
                'shift_jis euc-kr' => 'shift_jis euc-kr',
                'windows-1250' => 'windows-1250',
                'windows-1254' => 'windows-1254',
                'windows-1257' => 'windows-1257',
                'windows-1258' => 'windows-1258',
            );
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select id='select1' class='form-control col-md-7 col-xs-12' style='width:200px; margin-right:5px;' onchange=\" function obj(v){return document.getElementById(v);} function charset(x){  if(this.value != 'custom')  x.value=''; return ''; } obj('custom_charset').value = charset(obj('custom_charset'));\" name=b[charset]></div>";
            if (!in_array($show_configuration['charset'], array_keys($known_charsets))) {
                $charset_input_value = $show_configuration['charset'];
            }

            foreach ($known_charsets as $charset_name => $charset_display_name) {
                $this->body .= "<option value=\"$charset_name\"";
                if (($show_configuration["charset"] == $charset_name && $charset_display_name != 'custom') || ($charset_display_name == 'custom' && $charset_input_value)) {
                    $this->body .= " selected";
                }
                $this->body .= ">$charset_display_name</option>";
            }
            $this->body .= "</select>";
            $this->body .= "<input onblur=\" function obj(v){return document.getElementById(v)}; obj('select1').value ='custom';\" class='form-control col-md-7 col-xs-12' style=\"width:200px;\" placeholder=\"or Custom\" type=\"text\" id='custom_charset' name=\"b[charset_custom]\" value=\"$charset_input_value\" />";
            $this->row_count++;
            $this->body .= "
				</div>
			</fieldset>";
            if (!$this->admin_demo()) {
                $this->body .= "<div style=\"width: 100%; text-align: center;\"><input type=\"submit\" value=\"Save\" name=\"auto_save\" /></div>";
            }
            $this->body .= "</form>\n";
            return true;
        } else {
            $this->site_configuration_message = $this->internal_error_message;
            return false;
        }
    } //end of function display_general_configuration_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_general_configuration($configuration_info)
    {
        $admin = geoAdmin::getInstance();
        if ($configuration_info) {
            $classifieds_url = trim($configuration_info['classifieds_url']);
        //TODO: do some checks for classifieds_url
            $this->db->set_site_setting('classifieds_url', $classifieds_url);
            $this->db->set_site_setting('classifieds_ssl_url', trim($configuration_info['classifieds_ssl_url']));
            $this->db->set_site_setting('classifieds_file_name', trim($configuration_info['classifieds_file_name']));

            $this->db->set_site_setting('friendly_site_name', trim($configuration_info['friendly_site_name']));

            $configuration_info["idev_path"] = rtrim($configuration_info["idev_path"], "/\\ ") . '/';
        //make sure it includes ending slashy
            if ($configuration_info["idevaffiliate"] && !file_exists($configuration_info["idev_path"] . "sale.php")) {
                $admin->userError($configuration_info["idev_path"] . "sale.php does not exist. iDevAffiliate Integration de-activated.");
                $configuration_info["idevaffiliate"] = false;
            } else {
                $configuration_info["idevaffiliate"] = (isset($configuration_info["idevaffiliate"]) && $configuration_info["idevaffiliate"]) ? 1 : false;
            }
            $this->db->set_site_setting('idevaffiliate', $configuration_info["idevaffiliate"]);
            $this->db->set_site_setting('idev_path', $configuration_info["idev_path"]);
            $this->db->set_site_setting('company_address', trim($configuration_info["company_address"]), true);
            $use404 = (isset($configuration_info['use_404']) && $configuration_info['use_404']) ? 1 : false;
            $this->db->set_site_setting('use_404', $use404);
            $this->db->set_site_setting('noindex_sorted', (isset($configuration_info['noindex_sorted']) && $configuration_info['noindex_sorted']) ? 1 : false);
            if (isset($configuration_info['use_ssl_only']) && $configuration_info['use_ssl_only'] == 1) {
    //master SSL switch. Force everything on
                $this->db->set_site_setting('use_ssl_only', 1);
                $this->db->set_site_setting('force_ssl_url', 1);
                $this->db->set_site_setting('use_ssl_in_login', 1);
                $this->db->set_site_setting('use_ssl_in_user_manage', 1);
                $this->db->set_site_setting('use_ssl_in_sell_process', 1);
    //set SSL URL, if it's blank
                if (!$this->db->get_site_setting('classifieds_ssl_url')) {
                    $this->db->set_site_setting('classifieds_ssl_url', str_replace('http://', 'https://', $this->db->get_site_setting('classifieds_url')));
                }

                //also turn on SSL settings for registration, if they're not on already
                $this->db->set_site_setting('use_ssl_in_registration', 1);
                if (!$this->db->get_site_setting('registration_ssl_url')) {
                    $reg_http = $this->db->get_site_setting('registration_url');
                    $reg_https = str_replace('http://', 'https://', $reg_http);

                    $this->db->Execute("UPDATE " . geoTables::site_configuration_table . " SET `registration_ssl_url` = ?, `registration_url` = ?", array($reg_https, $reg_http));
                //eventually should move these to just the site_settings table
                    $this->db->set_site_setting('registration_ssl_url', $reg_https);
                    $this->db->set_site_setting('registration_url', $reg_http);
                }
            } else {
    //doing things the old way, with multiple settings

                $this->db->set_site_setting('use_ssl_only', 0);

                $forceSsl = (isset($configuration_info['force_ssl_url']) && $configuration_info['force_ssl_url']) ? 1 : false;
                $this->db->set_site_setting('force_ssl_url', $forceSsl);

                $sslLogin = (isset($configuration_info['use_ssl_in_login']) && $configuration_info['use_ssl_in_login']) ? 1 : false;
                $this->db->set_site_setting('use_ssl_in_login', $sslLogin);

                $sslUserManage = (isset($configuration_info['use_ssl_in_user_manage']) && $configuration_info['use_ssl_in_user_manage']) ? 1 : false;
                $this->db->set_site_setting('use_ssl_in_user_manage', $sslUserManage);

                $charset = (strlen(trim($configuration_info['charset_custom'])) > 0) ? $configuration_info['charset_custom'] : $configuration_info['charset'];
                $configuration_info['use_ssl_in_sell_process'] = (isset($configuration_info['use_ssl_in_sell_process']) && $configuration_info['use_ssl_in_sell_process']) ? 1 : 0;
                $use_ssl_in_sell_process = ($configuration_info['use_ssl_in_sell_process']) ? 1 : false;
                $this->db->set_site_setting('use_ssl_in_sell_process', $use_ssl_in_sell_process);
            }

            if (geoMaster::is('classifieds')) {
                $this->db->set_site_setting('allow_new_classifieds', $configuration_info['allow_new_classifieds']);
            }
            if (geoMaster::is('auctions')) {
                $this->db->set_site_setting('allow_new_auctions', $configuration_info['allow_new_auctions']);
            }

            if (!geoPC::force_powered_by() && !geoPC::is_trial()) {
                $this->db->set_site_setting('remove_powered_by', (isset($configuration_info['remove_powered_by']) && $configuration_info['remove_powered_by']));
            }

            if ($charset == 'custom') {
                $charset = 'utf-8';
            }
            $this->sql_query = "update " . $this->site_configuration_table . " set
				`classifieds_url`='" . trim($configuration_info['classifieds_url']) . "',
				`classifieds_ssl_url`='" . trim($configuration_info['classifieds_ssl_url']) . "',
				`classifieds_file_name`='" . trim($configuration_info['classifieds_file_name']) . "',
				site_on_off = \"" . trim($configuration_info["site_on_off"]) . "\",
				disable_site_url = \"" . trim($configuration_info["disable_site_url"]) . "\",
				levels_of_categories_displayed = \"" . trim($configuration_info["levels_of_categories_displayed"]) . "\",
				levels_of_categories_displayed_admin = \"" . trim($configuration_info["levels_of_categories_displayed_admin"]) . "\",
				use_ssl_in_sell_process = \"" . $configuration_info["use_ssl_in_sell_process"] . "\",
				charset = \"" . trim($charset) . "\",
				time_shift = \"" . $configuration_info["time_shift"] . "\"";
            $result = $this->db->Execute($this->sql_query);
//clear the settings cache
            geoCacheSetting::expire('configuration_data');
            if ($this->debug_site) {
                echo $this->sql_query . "<br>\n";
            }
            if (!$result) {
                if ($this->debug_site) {
                    echo $this->sql_query . "<br>\n";
                }
                $this->site_error($this->db->ErrorMsg());
                return false;
            } else {
                return true;
            }
        } else {
            $this->site_configuration_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_general_configuration

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_browse_configuration_form()
    {
        $admin = geoAdmin::getInstance();
        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
// Set title and text for tooltip
        $this->body .= "Text[1] = [\"Display Category Navigation\", \"Choosing \\\"yes\\\" will display the category navigation above the browsing results (according to the settings configured below). Choosing no will not display category navigation above the browsing results. The category navigation can then be displayed through any one of the category navigation display modules available.\"]\n
			Text[2] = [\"Display Category Count\", \"Choosing \\\"yes\\\" will display the number of listings a category has in it next to that categories name while browsing.\"]\n
			Text[3] = [\"Display Subcategory Listings\", \"Choosing \\\"yes\\\" will display the listings of a current category\'s subcategories. This will make each category appear more full, as subcategory listings will be filtered upward through parent categories.\"]\n
			Text[4] = [\"Category Display on Home Page\", \"Set the number of columns to display the categories on the home page. <br><br><strong>Important:</strong> This setting <strong>only</strong> affects categories that are displayed through the software\'s MAINBODY tag, not the software\'s Modules. The <strong>default</strong> FRONT PAGE TEMPLATE makes use of a Module to display categories, <strong>not</strong> the MAINBODY tag. \"]\n
			Text[5] = [\"Subcategory Display While Browsing Listings\", \"Set the number of columns to display the subcategories in while browsing the site\'s listings.\"]\n
			Text[6] = [\"Display Category Description While Browsing Listings\", \"Choosing \\\"yes\\\" will display the category description below the category name while browsing.\"]\n
			Text[7] = [\"Display the \\\"No Subcategory\\\" Message\", \"Choosing \\\"yes\\\" will display the \\\"There are no subcategories to...\\\" message when you are browsing a category that has no subcategories to enter.\"]\n
			Text[8] = [\"Display Subcategory Tree Above Normal Results\", \"Choosing \\\"yes\\\" will display the category tree above the normal browsing results (according to the configuration setting below). Choosing no will not display the category tree above the normal browsing results. The category tree can then be displayed using one of the category tree display modules available.\"]\n
			Text[9] = [\"Where and if You Want to Display the Category Tree\", \"This control allows you to control if you want and where the category tree is to be displayed when users are browsing the categories.  Above, below, above and below or not at all AROUND the category navigation links\"]\n
			Text[10] = [\"New Listing Time Limit\", \"This the time limit on whether to display the \\\"new listing\\\" icon or not. If a category has a new listing placed within it within the time limit you set below the \\\"new listing\\\" icon will appear next to that category's name when it appears within category navigation. If you do not want to display any new listings on the home page set this to 0\"]\n
			Text[11] = [\"\\\"New Listing\\\" in Category Icon to Use\", \"This is the url of the \\\"new listing\\\" icon used within the category navigation to indicate if a category has a new listing within it. The icon will appear immediately next to the category name within the category navigation around the site. This is the icon that will be used within all category navigation whether it is within a module or within normal category navigation display.\"]\n
			Text[12] = [\"Post Login Page\", \"From the list below choose the page you would like to appear once a user has successfully logged in.\"]\n
			Text[13] = [\"Display the Thumbnail Photo or Photo Icon\", \"If you choose to display the photo column while browsing you can choose to display the photo icon or a thumbnail of the first image connected to the listing.\"]\n
			Text[14] = [\"Image to use if No Image Uploaded by User\", \"If you choose to set an image location here that image will be shown in the photo column when browsing listings if the user did not upload an image for their listing.\"]\n
			Text[15] = [\"Photo Icon to Use\", \"This is the url of the photo icon used in the photo icon column. The photo icon will be displayed if you choose to display the photo icon only or when the thumbnail is chosen to be displayed and there is a problem displaying it.\"]\n
			Text[16] = [\"Browsing Yhumbnail Max Size\", \"Set the max width and height of the thumbnail photo displayed in the browsing results.\"]\n
			Text[17] = [\"Browsing Featured Thumbnail Max Size\", \"Set the max width and height of the featured thumbnail photo displayed in the browsing results. This also controls the size of the thumbnails in the browsing featured by picture only page.\"]\n
			Text[18] = [\"Help Icon to Use\", \"This is the url of the help icon used around the site to link to help popups.\"]\n
			Text[19] = [\"Sold Icon to Use\", \"This is the url of the sold icon used when user chooses to display the sold icon next to their listing while browsing.\"]\n
			Text[20] = [\"Number of Listings to Display on a Page\", \"Set the number of listings to display on any result page.  This will also affect the Active and Expired Listings pages also.\"]\n
			Text[21] = [\"Number of Featured Listings to Display on the Category Home Pages\", \"Set the number of featured listings you wish to display on each category's home page. The featured listings will only display on the first (home) page of every category and only shows the featured listings within that category. If you do not want to display any featured listings on these pages set this to 0.\"]\n
			Text[22] = [\"Time to Cache Category Listings While Browsing\", \"Set the length of time to cache the category listing when browsing. This is a time saving feature that could help the speed of your site. If you do not want to display any new listings on the home page set this to 0.\"]\n
			Text[23] = [\"ZipSearch Addon\", \"Choosing \\\"yes\\\" will allow your clients to search for listings within a certain distance from any zip/postal code they enter. <br><br><strong>US ZIP CODES</strong> - THIS REQUIRES THE ZIP SEARCH ADDON. <br><br>\"]\n
			Text[24] = [\"Search Page Setup\", \"You can choose to use the default search form within the search page or choose to create one of your own within the your own template if you wish. If you choose to use your own search form it will be used across all categories. The default form can contain different search fields based on the current category the client came from.\"]\n
			Text[25] = [\"User Must be Logged in to use the Contact Seller Feature\", \"Choosing \\\"yes\\\" will require the users to register and log in to use the contact the seller feature of the software.  This feature does NOT mask any fields displayed within the listing details page until the client is logged in\"]\n
			Text[26] = [\"User Must be Lgged in to View Listings\", \"Choosing \\\"yes\\\" will require the users to register and log in to view any listings.\"]\n
			Text[27] = [\"Choose how to Display Category Count Displays\", \"This decides how the category count will display.  Note that if you select no to display category count this setting has no effect.\"]\n
			Text[28] = [\"Buy Now Image\", \"By referencing an image here you will have this image display next to the title of an auction when the auction has a buy now price available. Reference the image relatively (ex:images/nameofyourimage.jpg).\"]\n
			Text[29] = [\"Reserve Met Image\", \"By referencing an image here you will have this image display next to the title of an auction when the auction has met the reserve price. Reference the image relatively (ex:images/nameofyourimage.jpg).\"]\n
			Text[40] = [\"Reserve NOT Met Image\",\"By referencing an image here you will have this image display next to the title of an auction when the auction has reserve set, but NOT met the reserve price yet. Note that the default is blank to make it not display an image in such cases, but an image 'images/reserve_not_met.gif' is available to use in default design if desired. Reference the image relatively (ex:images/nameofyourimage.jpg).\"]\n
			Text[30] = [\"No Reserve Image\", \"By referencing an image here you will have this image display next to the title of an auction when the auction has NO reserve price. Reference the image relatively (ex:images/nameofyourimage.jpg).\"]\n
			Text[31] = [\"Default Order of Classifieds While Browsing\", \"Choosing no order will let the ads display by newest first.  You can choose any of the other values in the dropdown to determine the default order of classifieds displayed while browsing.\"]\n
			Text[32] = [\"Rewrite URLs\", \"Rewrite URLs to make them look static (ex:index_a-28_b-141.htm). To use this function, make sure you have your .htaccess file set up correctly.\"]\n
			Text[33] = [\"Use American, European or Japanese Money Formatting?\", \"Choose how you would like to display money throughout your site.\"]\n
			Text[34] = [\"WYSIWYG Description Editor Size\", \"Change the width/height (in PIXELS) of the WYSIWYG editor used to edit item descriptions.\"]\n
			Text[35] = [\"TextArea Description Editor Size\", \"Change the width/height (in NUMBER OF COLUMNS/ROWS) of the text box used to edit item descriptions.\"]\n
			Text[36] = [\"Expired Listings Cutoff\", \"Expired listings older than this value in days will not appear under a user's Expired Listings, but will still be archived in the database. Set to 0 to disable this feature. The maximum value of this option is 365 days.\"]\n
			Text[37] = [\"Phone Number Format\", \"Selecting any option besides \\\"no formatting\\\" will enforce that formatting style on all phone and fax numbers in all listings.\"]\n
			Text[38] = [\"Phone Number Grouping\", \"This affects how phone numbers are constructed, to support non-US formats.<br><br>For example, US phone numbers have a 3-digit area code followed by 3 and then 4 digits, as in (123) 123-1234, which would be represented by 3 3 4 in these boxes.<br><br> Note that this affects phone and fax numbers contained in ALL listings on your site.\"]\n
			Text[39] = [\"Default Order of Auctions While Browsing\", \"Choosing no order will let the ads display by newest first.  You can choose any of the other values in the dropdown to determine the default order of auctions displayed while browsing.\"]\n";
//".$this->show_tooltip(27,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
        $this->body .= "</script>";
        $this->sql_query = "select * from " . $this->site_configuration_table;
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $admin->userError();
            $this->body .= $admin->getUserMessages();
            return false;
        } elseif ($result->RecordCount() == 1) {
            $this->body .= $admin->getUserMessages();
            $show_configuration = $result->FetchRow();
            if (!$this->admin_demo()) {
                $this->body .= "<form action=index.php?mc=site_setup&page=main_browsing_settings method=post class='form-horizontal form-label-left'>\n";
            } else {
                $this->body .= "<div class='form-horizontal'>";
            }

            if (geoPC::is_print()) {
            //add print settings
                if (!isset($this->printSettingsClass)) {
                    include_once ADMIN_DIR . 'print_settings.php';
                    $this->printSettingsClass = new printSettings();
                }
                $this->body .= $this->printSettingsClass->browse_settings_display();
            }

            $this->body .= "

			<fieldset id='PersonalUserData'><legend>Category Settings</legend>
				<div class='x_content'>

				<table cellpadding=3 cellspacing=0>";
//Show categories at all on home page
            //Setting no_home_bodyhtml to 1 "skips" normal {body_html} processing on home page
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Category/Subcategory Navigation: " . geoHTML::showTooltip('Display Category/Subcategory Navigation', "Check/uncheck the option for where you would like category navigation to display as part of the {body_html} tag.  Note that if you do NOT use {body_html} on the home page, un-check the home page option to increase the page speed.") . "</label>";
            $cat_nav_home = !$this->db->get_site_setting('no_home_bodyhtml');
            $cat_nav_browsing = $this->db->get_site_setting('display_category_navigation');
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type='checkbox' name='b[cat_nav_home]' value='1'" . (($cat_nav_home) ? ' checked="checked"' : '') . " /> Home Page<br />
				<input type='checkbox' name='b[cat_nav_browsing]' value='1'" . (($cat_nav_browsing) ? ' checked="checked"' : '') . " /> When Browsing Categories";
            $this->body .= "</div>";
            $this->body .= "</div>";
//display subcategory contents while browsing categories
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Category/Subcategory Counts: " . $this->show_tooltip(2, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[display_category_count] value=1 ";
            if ($show_configuration["display_category_count"] == 1) {
                $this->body .= "checked";
            }
            $this->body .= "> Yes<br><input type=radio name=b[display_category_count] value=0 ";
            if ($show_configuration["display_category_count"] == 0) {
                $this->body .= "checked";
            }
            $this->body .= "> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
// Choose how to display the category counts

            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Listing Type Order for Category Counts: " . $this->show_tooltip(27, 1) . "</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[browsing_count_format] class='form-control col-md-7 col-xs-12'>";
                $this->body .= "<option ";
                if ($show_configuration["browsing_count_format"] == 1) {
                    $this->body .= "selected";
                }
                $this->body .= " value=1>Auctions Count Only</option>";
                $this->body .= "<option ";
                if ($show_configuration["browsing_count_format"] == 2) {
                    $this->body .= "selected";
                }
                $this->body .= " value=2>Classifieds Count Only</option>";
                $this->body .= "<option ";
                if ($show_configuration["browsing_count_format"] == 3) {
                    $this->body .= "selected";
                }
                $this->body .= " value=3>Auctions then Classifieds</option>";
                $this->body .= "<option ";
                if ($show_configuration["browsing_count_format"] == 4) {
                    $this->body .= "selected";
                }
                $this->body .= " value=4>Classifieds then Auctions</option>";
                $this->body .= "<option ";
                if ($show_configuration["browsing_count_format"] == 5) {
                    $this->body .= "selected";
                }
                $this->body .= " value=5>Combined</option>";
                $this->body .= "</select>";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            //display category count next to category while browsing
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Include all Subcategory Listings: " . $this->show_tooltip(3, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[display_sub_category_ads] value=1 ";
            if ($show_configuration["display_sub_category_ads"] == 1) {
                $this->body .= "checked";
            }
            $this->body .= "> Yes<br><input type=radio name=b[display_sub_category_ads] value=0 ";
            if ($show_configuration["display_sub_category_ads"] == 0) {
                $this->body .= "checked";
            }
            $this->body .= "> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
//number of columns for browsing categories
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Display on Home Page: " . $this->show_tooltip(4, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12 input-group'><select name=b[number_of_browsing_columns] class='form-control col-md-7 col-xs-12'>";
            for ($i = 1; $i < 6; $i++) {
                $this->body .= "<option ";
                if ($i == $show_configuration["number_of_browsing_columns"]) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>";
            }
            $this->body .= "</select> <div class='input-group-addon'>columns</div>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//number of columns for browsing subcategories
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Subcategory Display while Browsing Listings: " . $this->show_tooltip(5, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12 input-group'><select name=b[number_of_browsing_subcategory_columns] class='form-control col-md-7 col-xs-12'>";
            for ($i = 1; $i < 6; $i++) {
                $this->body .= "<option ";
                if ($i == $show_configuration["number_of_browsing_subcategory_columns"]) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>";
            }
            $this->body .= "</select> <div class='input-group-addon'>columns</div>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//arrange categories
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Arrange Categories Alphabetically: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
								<input name='b[cat_alpha_across_columns]' value='1'" . (($this->db->get_site_setting('cat_alpha_across_columns')) ? "checked='checked'" : '') . " type='radio' /> Across the Column<br />
								<input name='b[cat_alpha_across_columns]' value='0'" . ((!$this->db->get_site_setting('cat_alpha_across_columns')) ? "checked='checked'" : '') . " type='radio' /> Down the Column
							";
            $this->body .= "</div>";
            $this->body .= "</div>";
//display listing descriptions when browsing categories
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Category Description while Browsing Listings: " . $this->show_tooltip(6, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[display_category_description] value=1 ";
            if ($show_configuration["display_category_description"] == 1) {
                $this->body .= "checked";
            }
            $this->body .= "> Yes<br><input type=radio name=b[display_category_description] value=0 ";
            if ($show_configuration["display_category_description"] == 0) {
                $this->body .= "checked";
            }
            $this->body .= "> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
//display no subcategories message
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display the 'No Subcategory' Message while Browsing Listings: " . $this->show_tooltip(7, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[display_no_subcategory_message] value=1 ";
            if ($show_configuration["display_no_subcategory_message"] == 1) {
                $this->body .= "checked";
            }
            $this->body .= "> Yes<br><input type=radio name=b[display_no_subcategory_message] value=0 ";
            if ($show_configuration["display_no_subcategory_message"] == 0) {
                $this->body .= "checked";
            }
            $this->body .= "> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
//display of category tree
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Category Tree (Breadcrumb) Location: " . $this->show_tooltip(9, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[category_tree_display] class='form-control col-md-7 col-xs-12'>";
            $this->body .= "<option value=0 ";
            if ($show_configuration["category_tree_display"] == 0) {
                $this->body .= "selected";
            }
            $this->body .= ">Below Subcategory Display</option>";
            $this->body .= "<option value=1 ";
            if ($show_configuration["category_tree_display"] == 1) {
                $this->body .= "selected";
            }
            $this->body .= ">Above Subcategory Display</option>";
            $this->body .= "<option value=2 ";
            if ($show_configuration["category_tree_display"] == 2) {
                $this->body .= "selected";
            }
            $this->body .= ">Above and Below Subcategory Display</option>";
            $this->body .= "<option value=3 ";
            if ($show_configuration["category_tree_display"] == 3) {
                $this->body .= "selected";
            }
            $this->body .= ">Do Not Display Category Tree</option>";
            $this->body .= "</select>";
            $this->body .= "</div>";
            $this->body .= "</div>";
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            //display "all" tab
                        $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display the 'All' tab while Browsing Listings: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[display_all_tab_browsing] value=1 ";
                if ($this->db->get_site_setting("display_all_tab_browsing")) {
                    $this->body .= "checked";
                }
                $this->body .= "> Yes<br /><input type=radio name=b[display_all_tab_browsing] value=0 ";
                if (!$this->db->get_site_setting("display_all_tab_browsing")) {
                    $this->body .= "checked";
                }
                $this->body .= "> No";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            //gallery/list/grid browse view default
            $mode = $this->db->get_site_setting('default_browse_view');
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default Browse View: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name='b[default_browse_view]' class='form-control col-md-7 col-xs-12'>";
            $this->body .= "<option value='grid'" . (($mode == 'grid') ? ' selected="selected"' : '') . ">Grid View</option>";
            $this->body .= "<option value='list'" . (($mode == 'list') ? ' selected="selected"' : '') . ">List View</option>";
            $this->body .= "<option value='gallery'" . (($mode == 'gallery') ? ' selected="selected"' : '') . ">Gallery View</option>";
            $this->body .= "</select>";
            $this->body .= "</div>";
            $this->body .= "</div>";
            $types = array ('grid','list','gallery');
//display subcategory contents while browsing categories
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display browse view links: <br /><span class='small_font'>(un-check all for no links)</span></label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";
            foreach ($types as $type) {
                $setting = 'display_browse_view_link_' . $type;
                $is_on = $this->db->get_site_setting($setting);
                $this->body .= "<label><input type='checkbox' name='b[{$setting}]' value='1'" . (($is_on) ? ' checked="checked"' : '') . " /> $type</label><br />";
            }
            $this->body .= "</div>";
            $this->body .= "</div>";
//number of columns for gallery browsing
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Gallery View number of columns: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[browse_gallery_number_columns] class='form-control col-md-7 col-xs-12'>";
            $browse_gallery_number_columns = $this->db->get_site_setting('browse_gallery_number_columns');
            for ($i = 1; $i <= 5; $i++) {
                $this->body .= "<option" . (($browse_gallery_number_columns == $i) ? ' selected="selected"' : '') . ">$i</option>";
            }
            $this->body .= "</select>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//show sort dropdown
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Show '<em>Sort By:</em>' dropdown: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name='b[browse_sort_dropdown_display]' class='form-control col-md-7 col-xs-12'>";
            $browse_sort_dropdown_display = $this->db->get_site_setting('browse_sort_dropdown_display');
            $this->body .= "<option value='always'" . (($browse_sort_dropdown_display == 'always') ? ' selected="selected"' : '') . ">Always</option>";
            $this->body .= "<option value='gallery_only'" . (($browse_sort_dropdown_display == 'gallery_only') ? ' selected="selected"' : '') . ">Only in Gallery View</option>";
            $this->body .= "<option value='never'" . (($browse_sort_dropdown_display == 'never') ? ' selected="selected"' : '') . ">Never</option>";
            $this->body .= "</select>";
            $this->body .= "</div>";
            $this->body .= "</div>";
            $this->body .= "
				</table>

			</div></fieldset>


			<fieldset><legend>Photo / Icon Settings</legend>
				<div class='x_content'>

				<table cellpadding=3 cellspacing=0>";
//photo or icon
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Thumbnail Type: " . $this->show_tooltip(13, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[photo_or_icon] value=1 ";
            if ($show_configuration["photo_or_icon"] == 1) {
                $this->body .= "checked";
            }
            $this->body .= "> Photo<br><input type=radio name=b[photo_or_icon] value=2 ";
            if ($show_configuration["photo_or_icon"] == 2) {
                $this->body .= "checked";
            }
            $this->body .= "> Icon";
            $this->body .= "</div>";
            $this->body .= "</div>";
//browsing photo thumbnail width and height
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Thumbnail Max Size: " . $this->show_tooltip(16, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
								<div class='input-group'>
									<input type=text name=b[thumbnail_max_width] class='form-control col-md-7 col-xs-12' size=5 value=\"" . $show_configuration["thumbnail_max_width"] . "\"> <div class='input-group-addon'>width (pixels)</div>
								</div>
								<div class='input-group'>
									<input type=text name=b[thumbnail_max_height] class='form-control col-md-7 col-xs-12' size=5 value=\"" . $show_configuration["thumbnail_max_height"] . "\"> <div class='input-group-addon'>height (pixels)</div>
								</div>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//browsing featured photo thumbnail width and height
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Featured Thumbnail Max Size: " . $this->show_tooltip(17, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
								<div class='input-group'>
									<input type=text name=b[featured_thumbnail_max_width] class='form-control col-md-7 col-xs-12' size=5 value=\"" . $show_configuration["featured_thumbnail_max_width"] . "\"> <div class='input-group-addon'>width (pixels)</div>
								</div>
								<div class='input-group'>
									<input type=text name=b[featured_thumbnail_max_height] class='form-control col-md-7 col-xs-12' size=5 value=\"" . $show_configuration["featured_thumbnail_max_height"] . "\"> <div class='input-group-addon'>height (pixels)</div>
								</div>";
            $this->body .= "</div>";
            $this->body .= "</div>";
// New listing limit
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"New Listing\" Time Limit: " . $this->show_tooltip(10, 1) . "</label>";
            $value_lengths = array(1,2,3,4,5,6,12,18,24,48,72,96,120,240);
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
				<select class='form-control col-md-7 col-xs-12' name=b[category_new_ad_limit]>";
            $this->body .= "<option value=0>no display of new listing icon</option>";
            reset($value_lengths);
            foreach ($value_lengths as $limit_time) {
                $plurality = ($limit_time == 1) ? "hour" : "hours";
                $this->body .= "<option value=" . $limit_time;
                if ($show_configuration["category_new_ad_limit"] == $limit_time) {
                    $this->body .= " selected";
                }
                $this->body .= ">" . $limit_time . " " . $plurality . "</option>";
            }
            $this->body .= "</select>";
            $this->body .= "</div>";
            $this->body .= "</div>";
            $this->body .= "
				</table>";
//icons are text in page 59, allow to set them here.
            $languages = $this->get_languages();
//$pre = "<div class='input-group-addon'>".$admin->geo_templatesDir()."<em title='The system will use the template set containing the image.'>[Template Set]</em>/external/</div>";
            $pre = "";
            foreach ($languages as $languageId => $lang) {
                $this->body .= "<div class='col_hdr' style='margin-bottom: 5px;'>Icons Used in <span style='text-transform: uppercase;'>{$lang['language']}</span><br />( paths relative to: " . $admin->geo_templatesDir() . "<em title='The system will use the template set containing the image.'>[Template Set]</em>/external/ )</div>";
                $txts = $this->db->GetAssoc("SELECT `text_id`, `text` FROM " . geoTables::pages_text_languages_table . " WHERE `page_id`=59 AND `language_id` = ?", array($languageId));
            //new listing icon url
                        $this->body .= "
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"New Listing\" Icon URL: " . $this->show_tooltip(11, 1) . "</label>
						<div class='input-group col-md-6 col-sm-6 col-xs-12'>
						$pre<input type='text' name='b[icons][$languageId][500794]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500794])) . "' />
						</div>
					</div>";
            //no image available image
                        $this->body .= "
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"No Photo\" Icon URL: " . $this->show_tooltip(14, 1) . "</label>
						<div class='input-group col-md-6 col-sm-6 col-xs-12'>
						$pre<input type='text' name='b[icons][$languageId][500795]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500795])) . "' />
						</div>
					</div>";
            //photo icon url
                        $this->body .= "
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Photo\" Icon URL: " . $this->show_tooltip(15, 1) . "</label>
						<div class='input-group col-md-6 col-sm-6 col-xs-12'>
						$pre<input type='text' name='b[icons][$languageId][500796]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500796])) . "' />
						</div>
					</div>";
            //help icon url
                        $this->body .= "
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Help\" Icon URL: " . $this->show_tooltip(18, 1) . "</label>
						<div class='input-group col-md-6 col-sm-6 col-xs-12'>
						$pre<input type='text' name='b[icons][$languageId][500797]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500797])) . "' />
						</div>
					</div>";
            //verified account icon url
                        $this->body .= "
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Verified Account\" Icon URL:</label>
						<div class='input-group col-md-6 col-sm-6 col-xs-12'>
						$pre<input type='text' name='b[icons][$languageId][500952]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500952])) . "' />
						</div>
					</div>";
            //verified account icon url listings
                        $this->body .= "
					<div class='form-group'>
						<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Verified Account\" Icon URL in Listing Details:</label>
						<div class='input-group col-md-6 col-sm-6 col-xs-12'>
						$pre<input type='text' name='b[icons][$languageId][500957]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500957])) . "' />
						</div>
					</div>";
                if (geoPC::is_ent()) {
        //sold icon url
                    $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Sold\" Icon URL: " . $this->show_tooltip(19, 1) . "</label>
							<div class='input-group col-md-6 col-sm-6 col-xs-12'>
							$pre<input type='text' name='b[icons][$languageId][500798]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500798])) . "' />
							</div>
						</div>";
                }
                if (geoMaster::is('auctions')) {
            //buy now image display
                    $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Buy Now\" Icon URL:" . $this->show_tooltip(28, 1) . "</label>
							<div class='input-group col-md-6 col-sm-6 col-xs-12'>
							$pre<input type='text' name='b[icons][$languageId][500799]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500799])) . "' />
							</div>
						</div>";
            //reserve image display
                    $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Reserve Met\" Icon URL:" . $this->show_tooltip(29, 1) . "</label>
							<div class='input-group col-md-6 col-sm-6 col-xs-12'>
							$pre<input type='text' name='b[icons][$languageId][500800]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500800])) . "' />
							</div>
						</div>";
            //reserve NOT MET image display
                    $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"Reserve NOT Met\" Icon URL:" . $this->show_tooltip(40, 1) . "</label>
							<div class='input-group col-md-6 col-sm-6 col-xs-12'>
							$pre<input type='text' name='b[icons][$languageId][501665]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[501665])) . "' />
							</div>
						</div>";
            //no reserve image display
                    $this->body .= "
						<div class='form-group'>
							<label class='control-label col-md-5 col-sm-5 col-xs-12'>\"No Reserve\" Icon URL:" . $this->show_tooltip(30, 1) . "</label>
							<div class='input-group col-md-6 col-sm-6 col-xs-12'>
							$pre<input type='text' name='b[icons][$languageId][500802]' class='form-control col-md-7 col-xs-12' value='" . htmlspecialchars(geoString::fromDB($txts[500802])) . "' />
							</div>
						</div>";
                }
            }

            $this->body .= "
			</div>
			</fieldset>



			<fieldset>
				<legend>Miscellaneous Settings</legend>

			<div class='x_content'>
				<table cellpadding=3 cellspacing=0>";
//number of listings or search returns to display on a page
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Number of Listings per Page: " . $this->show_tooltip(20, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'><input name='b[number_of_ads_to_display]' type='number' class='form-control col-md-7 col-xs-12' value='{$show_configuration["number_of_ads_to_display"]}' size='5' />
				</div>
			</div>";
//this setting no longer relevent
            /*
         //number of featured listings to display on the category home page
         $this->body .= "<tr class=".$this->get_row_color().">\n\t<td style='text-align: right;' class=medium_font>\n\t<strong>Number of Featured Listings while Browsing Listings:</strong>".$this->show_tooltip(21,1)."</td>\n\t";
            $this->body .= "<td class=medium_font>\n\t<select name=b[number_of_featured_ads_to_display]>\n\t\t";
           for ($i=0;$i<=20;$i++)
         {
              $this->body .= "<option ";
             if ($i == $show_configuration["number_of_featured_ads_to_display"])
                    $this->body .= "selected";
             $this->body .= ">".$i."</option>\n\t\t";
           }
          $this->body .= "</select>\n\t</td>\n</tr>\n";
          $this->row_count++;
            */
            //category caching
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Time to Cache Category Listings while Browsing Listings: " . $this->show_tooltip(22, 1) . "</label>";
            $value_lengths = array(1,2,3,4,5,6,12,18,24);
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[use_category_cache] class='form-control col-md-7 col-xs-12'>";
            $this->body .= "<option value=0>Never</option>";
            reset($value_lengths);
            foreach ($value_lengths as $cache_time) {
                $this->body .= "<option value=" . $cache_time;
                if ($show_configuration["use_category_cache"] == $cache_time) {
                    $this->body .= " selected";
                }
                $this->body .= ">" . $cache_time . " hour(s)</option>";
            }
            $this->body .= "</select>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//turn off use of mainbody tag within search page
            if (geoPC::is_ent()) {
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Search Page Setup: " . $this->show_tooltip(24, 1) . "</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=b[use_search_form] value=0 ";
                if ($show_configuration["use_search_form"] == 0) {
                    $this->body .= "checked";
                }
                $this->body .= "> Use default search form<br><input type=radio name=b[use_search_form] value=1 ";
                if ($show_configuration["use_search_form"] == 1) {
                    $this->body .= "checked";
                }
                $this->body .= "> Create my own search form";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            //hide regions with no listings from advanced search
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Advanced Search Region Selector: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type='radio' name='b[advSearch_skipEmptyRegions]' " . ($this->db->get_site_setting('advSearch_skipEmptyRegions') ? 'checked="checked"' : '') . " value='1' /> Show only regions that contain at least one listing<br />
					<input type='radio' name='b[advSearch_skipEmptyRegions]' " . ($this->db->get_site_setting('advSearch_skipEmptyRegions') ? '' : 'checked="checked"') . " value='0' /> Show all regions
							</div>";
            $this->body .= "</div>";
            if (geoPC::is_ent() || geoPC::is_premier()) {
            //log in to contact seller switch
                        $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Require Login to Use the Contact Seller Feature: " . $this->show_tooltip(25, 1) . "</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type=radio name=b[seller_contact] value=1 ";
                if ($show_configuration["seller_contact"] == 1) {
                    $this->body .= "checked";
                }
                        $this->body .= "> Yes<br><input type=radio name=b[seller_contact] value=0 ";
                if ($show_configuration["seller_contact"] == 0) {
                    $this->body .= "checked";
                }
                $this->body .= "> No";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            if (geoPC::is_ent()) {
            //log in to view listing switch
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Require Login to View Listings: " . $this->show_tooltip(26, 1) . "</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type=radio name=b[subscription_to_view_or_bid_ads] value=1 ";
                if ($show_configuration["subscription_to_view_or_bid_ads"] == 1) {
                    $this->body .= "checked";
                }
                $this->body .= "> Yes<br><input type=radio name=b[subscription_to_view_or_bid_ads] value=0 ";
                if ($show_configuration["subscription_to_view_or_bid_ads"] == 0) {
                    $this->body .= "checked";
                }
                $this->body .= "> No";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            if (geoAddon::getInstance()->isEnabled('subscription_pricing')) {
            //subscribe to view listing switch
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Require Subscription to View Listings: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type=radio name=b[must_have_subscription_to_view_ad_detail] value=1 ";
                if ($this->db->get_site_setting("must_have_subscription_to_view_ad_detail") == 1) {
                    $this->body .= "checked";
                }
                $this->body .= "> Yes<br><input type=radio name=b[must_have_subscription_to_view_ad_detail] value=0 ";
                if ($this->db->get_site_setting("must_have_subscription_to_view_ad_detail") == 0) {
                    $this->body .= "checked";
                }
                $this->body .= "> No";
                $this->body .= "</div>";
                $this->body .= "</div>";
//subscribe to bid switch
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Require Subscription to Bid on Auctions: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type=radio name=b[bidding_requires_subscription] value=1 ";
                if ($this->db->get_site_setting("bidding_requires_subscription") == 1) {
                    $this->body .= "checked";
                }
                $this->body .= "> Yes<br><input type=radio name=b[bidding_requires_subscription] value=0 ";
                if ($this->db->get_site_setting("bidding_requires_subscription") == 0) {
                    $this->body .= "checked";
                }
                $this->body .= "> No";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            //filter listings by language
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Filter Listings by Language: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type='radio' name='b[filter_by_language]' value='1'";
            if ($this->db->get_site_setting('filter_by_language')) {
                $this->body .= " checked='checked'";
            }
                            $this->body .= " /> Yes<br />";
            $this->body .= "<input type='radio' name='b[filter_by_language]' value='0'";
            if (!$this->db->get_site_setting('filter_by_language')) {
                $this->body .= " checked='checked'";
            }
                            $this->body .= " /> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
//default order by while browsing normally

            $order_by_array = array();
            $order_by_array[0] = "default";
            $order_by_array[1] = "price ascending";
            $order_by_array[2] = "price descending";
            $order_by_array[3] = "placement date ascending";
            $order_by_array[4] = "placement date descending";
            $order_by_array[5] = "title ascending (alphabetical)";
            $order_by_array[6] = "title descending";
            $order_by_array[7] = "city ascending (alphabetical)";
            $order_by_array[8] = "city descending";
            $order_by_array[9] = "state ascending";
            $order_by_array[10] = "state descending";
            $order_by_array[11] = "country ascending";
            $order_by_array[12] = "country descending";
            $order_by_array[13] = "zip ascending";
            $order_by_array[14] = "zip descending";
            $order_by_array[15] = "optional field 1 ascending";
            $order_by_array[16] = "optional field 1 descending";
            $order_by_array[17] = "optional field 2 ascending";
            $order_by_array[18] = "optional field 2 descending";
            $order_by_array[19] = "optional field 3 ascending";
            $order_by_array[20] = "optional field 3 descending";
            $order_by_array[21] = "optional field 4 ascending";
            $order_by_array[22] = "optional field 4 descending";
            $order_by_array[23] = "optional field 5 ascending";
            $order_by_array[24] = "optional field 5 descending";
            $order_by_array[25] = "optional field 6 ascending";
            $order_by_array[26] = "optional field 6 descending";
            $order_by_array[27] = "optional field 7 ascending";
            $order_by_array[28] = "optional field 7 descending";
            $order_by_array[29] = "optional field 8 ascending";
            $order_by_array[30] = "optional field 8 descending";
            $order_by_array[31] = "optional field 9 ascending";
            $order_by_array[32] = "optional field 9 descending";
            $order_by_array[33] = "optional field 10 ascending";
            $order_by_array[34] = "optional field 10 descending";
            $order_by_array[45] = "optional field 11 ascending";
            $order_by_array[46] = "optional field 11 descending";
            $order_by_array[47] = "optional field 12 ascending";
            $order_by_array[48] = "optional field 12 descending";
            $order_by_array[49] = "optional field 13 ascending";
            $order_by_array[50] = "optional field 13 descending";
            $order_by_array[51] = "optional field 14 ascending";
            $order_by_array[52] = "optional field 14 descending";
            $order_by_array[53] = "optional field 15 ascending";
            $order_by_array[54] = "optional field 15 descending";
            $order_by_array[55] = "optional field 16 ascending";
            $order_by_array[56] = "optional field 16 descending";
            $order_by_array[57] = "optional field 17 ascending";
            $order_by_array[58] = "optional field 17 descending";
            $order_by_array[59] = "optional field 18 ascending";
            $order_by_array[60] = "optional field 18 descending";
            $order_by_array[61] = "optional field 19 ascending";
            $order_by_array[62] = "optional field 19 descending";
            $order_by_array[63] = "optional field 20 ascending";
            $order_by_array[64] = "optional field 20 descending";
//** 65/66 reserved because of SEO addon
            $order_by_array[69] = "ending soonest";
            $order_by_array[70] = "reverse ending (farthest ending first)";
            $order_by_array[43] = "business type ascending";
            $order_by_array[44] = "business type descending";
            $order_by_array[71] = "listings with no images first";
            $order_by_array[72] = "listings with at least one image first";
            $legacy_orderBy = $this->get_site_setting("default_display_order_while_browsing");
            if (geoMaster::is('classifieds')) {
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default Order of \"Classifieds\" while Browsing Listings: " . $this->show_tooltip(31, 1) . "</label>";
                $value_lengths = array(1,2,3,4,5,6,12,18,24);
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[default_classified_order_while_browsing] class='form-control col-md-7 col-xs-12'>";
                reset($order_by_array);
                foreach ($order_by_array as $key => $value) {
                    $this->body .= "<option value=" . $key . " ";
                    if ($legacy_orderBy != -1 && $key == $legacy_orderBy) {
                        $this->body .= "selected=\"selected\"";
                    } elseif ($legacy_orderBy == -1 && $key == $this->get_site_setting("default_classified_order_while_browsing")) {
                        $this->body .= "selected=\"selected\"";
                    }
                    $this->body .= ">" . $value . "</option>";
                }
                $this->body .= "</select>";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            if (geoMaster::is('auctions')) {
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default Order of \"Auctions\" while Browsing Listings: " . $this->show_tooltip(39, 1) . "</label>";
                $value_lengths = array(1,2,3,4,5,6,12,18,24);
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[default_auction_order_while_browsing] class='form-control col-md-7 col-xs-12'>";
                reset($order_by_array);
            // set up to step through the population array again
                foreach ($order_by_array as $key => $value) {
                    $this->body .= "<option value=" . $key . " ";
                    if ($legacy_orderBy != -1 && $key == $legacy_orderBy) {
                        $this->body .= "selected=\"selected\"";
                    } elseif ($legacy_orderBy == -1 && $key == $this->get_site_setting("default_auction_order_while_browsing")) {
                        $this->body .= "selected=\"selected\"";
                    }
                    $this->body .= ">" . $value . "</option>";
                }
                $this->body .= "</select>";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            // Number formatting switch
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Currency Format: " . $this->show_tooltip(33, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[number_format] class='form-control col-md-7 col-xs-12'>";
            $this->body .= "<option value=0 ";
            if ($show_configuration["number_format"] == 0) {
                $this->body .= "selected";
            }
            $this->body .= ">American 10,000.00 </option>\n\t\t";
            $this->body .= "<option value=1 ";
            if ($show_configuration["number_format"] == 1) {
                $this->body .= "selected";
            }
            $this->body .= ">European 10.000,00 </option>\n\t\t";
            $this->body .= "<option value=2 ";
            if ($show_configuration["number_format"] == 2) {
                $this->body .= "selected";
            }
            $this->body .= ">Japanese 10,000 (no decimal)</option>\n\t\t";
            $this->body .= "<option value=3 ";
            if ($show_configuration["number_format"] == 3) {
                $this->body .= "selected";
            }
            $this->body .= ">None 10000 (no grouping or decimal)</option>\n\t\t";
            $hideCentsSelected = ($this->db->get_site_setting('hide_cents') ? 'checked="checked"' : '');
            $this->body .= "</select><br />
			<label>Hide cents if .00 <input type='checkbox' name='b[hide_cents]' value='1' $hideCentsSelected /></label>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//what to do with 0.00 cart costs
            $cart_replace_zero_cost = $this->db->get_site_setting('cart_replace_zero_cost');
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display 0.00 Cart Item Costs: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type='radio' name='b[cart_replace_zero_cost]' value='0'" . (($cart_replace_zero_cost) ? '' : " checked='checked'") . " /> " . geoString::displayPrice('0.00') . "
					<br />
					<input type='radio' name='b[cart_replace_zero_cost]' value='1'" . (($cart_replace_zero_cost) ? " checked='checked'" : '') . " /> Replace with Text: <br />
					<div style='padding-left: 35px;'>";
            foreach ($languages as $languageId => $lang) {
                $txts = $this->db->GetAssoc("SELECT `text_id`, `text` FROM " . geoTables::pages_text_languages_table . " WHERE `page_id`=10202 AND `text_id`=500995 AND `language_id` = ?", array($languageId));
                $this->body .= "<strong>{$lang['language']}:</strong><br /> <input type='text' name='b[icons][$languageId][500995]' value='" . geoString::specialChars(geoString::fromDB($txts[500995])) . "' /><br />";
            }
                $this->body .= "</div>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//what to do with 0.00 listing costs
            $listing_replace_zero_cost = $this->db->get_site_setting('listing_replace_zero_cost');
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display 0.00 Listing Prices: </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type='radio' name='b[listing_replace_zero_cost]' value='0'" . (($listing_replace_zero_cost) ? '' : " checked='checked'") . " /> Normal (e.g. $0.00 USD)
					<br />
					<input type='radio' name='b[listing_replace_zero_cost]' value='1'" . (($listing_replace_zero_cost) ? " checked='checked'" : '') . " /> Replace with Text: <br />
					<div style='padding-left: 35px;'>";
            foreach ($languages as $languageId => $lang) {
                $txts = $this->db->GetAssoc("SELECT `text_id`, `text` FROM " . geoTables::pages_text_languages_table . " WHERE `page_id`=59 AND `text_id`=500996 AND `language_id` = ?", array($languageId));
                $this->body .= "<strong>{$lang['language']}:</strong><br /> <input type='text' name='b[icons][$languageId][500996]' value='" . geoString::specialChars(geoString::fromDB($txts[500996])) . "' /><br />";
            }
                $this->body .= "</div>";
            $this->body .= "</div>";
            $this->body .= "</div>";
//allow hiding "postcurrency"
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Hide Post Currency Symbol: <br /><span class='small_font'>(Example: \"USD\")</span></label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type='radio' name='b[hide_postcurrency]' value='1'" . (($this->db->get_site_setting('hide_postcurrency')) ? " checked='checked'" : "") . " /> Yes
						<br />
						<input type='radio' name='b[hide_postcurrency]' value='0'" . (($this->db->get_site_setting('hide_postcurrency')) ? '' : " checked='checked'") . " /> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
//set up default formatting for next 2 switches
            $phone_regex = array();
            $phone_regex[1] = ($this->db->get_site_setting("phone_regex_piece1")) ? $this->db->get_site_setting("phone_regex_piece1") : 3;
            $phone_regex[2] = ($this->db->get_site_setting("phone_regex_piece2")) ? $this->db->get_site_setting("phone_regex_piece2") : 3;
            $phone_regex[3] = ($this->db->get_site_setting("phone_regex_piece3")) ? $this->db->get_site_setting("phone_regex_piece3") : 4;
            $phone_regex[0] = "^([0-9]{" . $phone_regex[1] . "})([0-9]{" . $phone_regex[2] . "})([0-9]{" . $phone_regex[3] . "})$";
//construct a dummy phone number
            $dummyNumber = array();
            for ($i = 1; $i <= 3; $i++) {
                for ($j = 1; $j <= $phone_regex[$i]; $j++) {
                    $dummyNumber[$i] .= $j;
                }
            }

            // international phone regex switch
            $this->body .= "<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Phone Number Grouping: " . $this->show_tooltip(38, 1) . "</label>
				<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=\"text\" class=\"form-control col-md-7 col-xs-12\" style=\"width:40px; margin-right:5px;\" name=b[phone_regex_piece1] size=2 value=" . $phone_regex[1] . ">
					<input type=\"text\" class=\"form-control col-md-7 col-xs-12\" style=\"width:40px; margin-right:5px;\" name=b[phone_regex_piece2] size=2 value=" . $phone_regex[2] . ">
					<input type=\"text\" class=\"form-control col-md-7 col-xs-12\" style=\"width:40px;\" name=b[phone_regex_piece3] size=2 value=" . $phone_regex[3] . ">
				</div>
			</div>";
// phone number formatting switch
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Phone Number Formatting: " . $this->show_tooltip(37, 1) . "</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><select name=b[phone_format] id=\"phoneFormat\" class='form-control col-md-7 col-xs-12'>";
            $this->body .= "<option value=\"1\">(" . $dummyNumber[1] . ") " . $dummyNumber[2] . "-" . $dummyNumber[3] . "</option>\n\t\t";
            $this->body .= "<option value=\"2\">" . $dummyNumber[1] . "-" . $dummyNumber[2] . "-" . $dummyNumber[3] . "</option>\n\t\t";
            $this->body .= "<option value=\"3\">" . $dummyNumber[1] . "." . $dummyNumber[2] . "." . $dummyNumber[3] . "</option>\n\t\t";
            $this->body .= "<option value=\"4\">(" . $dummyNumber[1] . ") " . $dummyNumber[2] . "." . $dummyNumber[3] . "</option>\n\t\t";
            $this->body .= "<option value=\"5\">" . $dummyNumber[1] . " " . $dummyNumber[2] . " " . $dummyNumber[3] . "</option>\n\t\t";
            $this->body .= "<option value=\"6\">" . $dummyNumber[1] . $dummyNumber[2] . $dummyNumber[3] . "</option>\n\t\t";
            $this->body .= "<option value=\"0\">No formatting</option>";
            $this->body .= "</select>";
//javascript to set phone format switch to proper default
            if (is_numeric($this->db->get_site_setting("phone_format"))) {
                $this->body .= "<script type=\"text/javascript\">document.getElementById('phoneFormat').value=" . $this->db->get_site_setting("phone_format") . ";</script>";
            }
            $this->body .= "</div>";
            $this->body .= "</div>";
//whether to hide listings marked as sold
            $hide_sold = (bool)$this->db->get_site_setting('hide_sold');
            $show_sold_sellers_other_ads = $hide_sold && $this->db->get_site_setting('show_sold_sellers_other_ads');
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Hide Listings Marked \"Sold\": </label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type='radio' name='b[hide_sold]' class='hide_sold' value='1'" . (($hide_sold) ? ' checked="checked"' : '') . " /> Yes
						<div id=\"hideSold\" style='display: inline-block;'>
						<input type='checkbox' name='b[show_sold_sellers_other_ads]' value='1'" . (($show_sold_sellers_other_ads) ? ' checked="checked"' : '') . " /> But DO show them on the Seller's Other Ads
						</div>
						<br />
						<input type='radio' name='b[hide_sold]' class='hide_sold' value='0'" . ((!$hide_sold) ? ' checked="checked"' : '') . " /> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
            $this->body .= '<script type="text/javascript">
					jQuery(function () {
						var hide_sold_clicked = function () {
							if (jQuery(".hide_sold:checked").val()==1) {
								jQuery("#hideSold").show();
							} else {
								jQuery("#hideSold").hide();
							}
						};
						hide_sold_clicked();
						jQuery(".hide_sold").click(hide_sold_clicked);
					});
					</script>';
            $this->row_count++;
            if (geoMaster::is('classifieds')) {
            //whether to show classifieds that expired
                $show_expired_classifieds = (bool)$this->db->get_site_setting('show_expired_classifieds');
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Show Expired Classified Listing Details: <br /><span class='small_font'>(Note: only with direct link)</span></label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
							<input type='radio' name='b[show_expired_classifieds]' value='1'" . (($show_expired_classifieds) ? ' checked="checked"' : '') . " /> Yes
							<br />
							<input type='radio' name='b[show_expired_classifieds]' value='0'" . ((!$show_expired_classifieds) ? ' checked="checked"' : '') . " /> No";
                $this->body .= "</div>";
                $this->body .= "</div>";
            }

            //display category specific header elements in listing details page
            $show_expired_classifieds = (bool)$this->db->get_site_setting('show_expired_classifieds');
            $this->body .= "<div class='form-group'>";
            $this->body .= "<label class='control-label col-md-5 col-sm-5 col-xs-12'>Display Category Specific Header Elements in Listing Details Page: <br /><span class='small_font'>(Note: Only refers to category specific html header elements set within the admin tool for it)</span></label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<input type='radio' name='b[show_category_head_element_in_details]' value='0'" . (($show_category_head_element_in_details == 0) ? ' checked="checked"' : '') . " /> Yes
						<br />
						<input type='radio' name='b[show_category_head_element_in_details]' value='1'" . (($show_category_head_element_in_details == 1) ? ' checked="checked"' : '') . " /> No";
            $this->body .= "</div>";
            $this->body .= "</div>";
            $this->body .= "
				</table>

				</div>
			</fieldset>";
            if (!$this->admin_demo()) {
                $this->body .= "<div style=\"text-align: center;\"><input type=submit value=\"Save\" name=\"auto_save\"></div></form>";
            } else {
                $this->body .= "</div>\n";
            }

            return true;
        } else {
            $this->site_configuration_message = $this->internal_error_message;
            return false;
        }
    } //end of function display_browse_configuration_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_browse_configuration($configuration_info)
    {
        //use new get/set methods to control new additions to config

        // sort categories alphabetically across or down the colums
        if (!$this->db->set_site_setting("cat_alpha_across_columns", $configuration_info["cat_alpha_across_columns"])) {
            return false;
        }
        //briank stop commiting debug output
        //echo "this->".$configuration_info["cat_alpha_across_columns"];

        //cutoff for displaying expired ads
        if (!$this->db->set_site_setting("expired_cutoff", $configuration_info["expired_cutoff"])) {
            return false;
        }

        //hide_sold
        $hide_sold = ($configuration_info['hide_sold'] == 1) ? 1 : false;
        $this->db->set_site_setting('hide_sold', $hide_sold);
//show_sold_sellers_other_ads
        $show_sold_sellers_other_ads = ($hide_sold && isset($configuration_info['show_sold_sellers_other_ads']) && $configuration_info['show_sold_sellers_other_ads'] == 1) ? 1 : false;
        $this->db->set_site_setting('show_sold_sellers_other_ads', $show_sold_sellers_other_ads);
        if (geoMaster::is('classifieds')) {
        //show_expired_classifieds
            $show_expired_classifieds = ($configuration_info['show_expired_classifieds'] == 1) ? 1 : false;
            $this->db->set_site_setting('show_expired_classifieds', $show_expired_classifieds);
        }

        //controls for how phone numbers are displayed
        if (!$this->db->set_site_setting("phone_format", $configuration_info["phone_format"])) {
            return false;
        }
        if (!$this->db->set_site_setting("phone_regex_piece1", $configuration_info["phone_regex_piece1"])) {
            return false;
        }
        if (!$this->db->set_site_setting("phone_regex_piece2", $configuration_info["phone_regex_piece2"])) {
            return false;
        }
        if (!$this->db->set_site_setting("phone_regex_piece3", $configuration_info["phone_regex_piece3"])) {
            return false;
        }

        //controls for display order during browsing
        if ((geoMaster::is('classifieds')) && (isset($configuration_info['default_classified_order_while_browsing']))) {
            if (!$this->db->set_site_setting("default_classified_order_while_browsing", $configuration_info["default_classified_order_while_browsing"])) {
                return false;
            }
        }
        if ((geoMaster::is('auctions')) && (isset($configuration_info['default_auction_order_while_browsing']))) {
            if (!$this->db->set_site_setting("default_auction_order_while_browsing", $configuration_info["default_auction_order_while_browsing"])) {
                return false;
            }
        }
        //this next line required because dropdown defaults aren't otherwise updating till the second time around
        //it's a bit of a dirty hack, but it works! :)
        if (!$this->db->set_site_setting("default_display_order_while_browsing", -1)) {
            return false;
        }

        //show hide html header elements set within the category specific header elements feature within the listing details page
        $this->db->set_site_setting('show_category_head_element_in_details', ((isset($configuration_info['show_category_head_element_in_details']) && $configuration_info['show_category_head_element_in_details']) ? 1 : 0));
//hide 00 cents?
        $this->db->set_site_setting('hide_cents', ((isset($configuration_info['hide_cents']) && $configuration_info['hide_cents']) ? 1 : false));
//replace cart cost 0.00...
        $this->db->set_site_setting('cart_replace_zero_cost', ((isset($configuration_info['cart_replace_zero_cost']) && $configuration_info['cart_replace_zero_cost']) ? 1 : false));
//replace listing cost 0.00...
        $this->db->set_site_setting('listing_replace_zero_cost', ((isset($configuration_info['listing_replace_zero_cost']) && $configuration_info['listing_replace_zero_cost']) ? 1 : false));
//hide postcurrency
        $this->db->set_site_setting('hide_postcurrency', ((isset($configuration_info['hide_postcurrency']) && $configuration_info['hide_postcurrency']) ? 1 : false));
//filter listings by language
        $this->db->set_site_setting('filter_by_language', ((isset($configuration_info['filter_by_language']) && $configuration_info['filter_by_language']) ? 1 : false));
//require subscription to view listings
        $this->db->set_site_setting('must_have_subscription_to_view_ad_detail', ((isset($configuration_info['must_have_subscription_to_view_ad_detail']) && $configuration_info['must_have_subscription_to_view_ad_detail']) ? 1 : false));
        $this->db->set_site_setting('bidding_requires_subscription', ((isset($configuration_info['bidding_requires_subscription']) && $configuration_info['bidding_requires_subscription']) ? 1 : false));
//don't show empty regions in advanced search
        $this->db->set_site_setting('advSearch_skipEmptyRegions', ((isset($configuration_info['advSearch_skipEmptyRegions']) && $configuration_info['advSearch_skipEmptyRegions']) ? 1 : false));
//display category navigation on home page / browsing pages
        $cat_nav_home = (isset($configuration_info['cat_nav_home']) && $configuration_info['cat_nav_home']);
        $cat_nav_browsing = (isset($configuration_info['cat_nav_browsing']) && $configuration_info['cat_nav_browsing']);
//Note: no_home_html = 1 means do NOT display categories on home page, using
        //adapted setting instead of making a new one...
        $this->db->set_site_setting('no_home_bodyhtml', ((!$cat_nav_home) ? 1 : false));
        $this->db->set_site_setting('display_category_navigation', (($cat_nav_browsing) ? 1 : false));
        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            $this->db->set_site_setting('display_all_tab_browsing', ((isset($configuration_info['display_all_tab_browsing']) && $configuration_info['display_all_tab_browsing'] == 1) ? 1 : false));
        }
        $browse_view = ((in_array($configuration_info['default_browse_view'], array('grid','list','gallery'))) ? $configuration_info['default_browse_view'] : 'grid');
        $this->db->set_site_setting('default_browse_view', $browse_view);
        $types = array('grid','list','gallery');
        foreach ($types as $type) {
            $setting = 'display_browse_view_link_' . $type;
            $value = (isset($configuration_info[$setting]) && $configuration_info[$setting]) ? 1 : false;
            $this->db->set_site_setting($setting, $value);
        }

        $browse_gallery_number_columns = (int)$configuration_info['browse_gallery_number_columns'];
//make sure it's not 0
        if (!$browse_gallery_number_columns) {
            $browse_gallery_number_columns = 1;
        }
        $this->db->set_site_setting('browse_gallery_number_columns', $browse_gallery_number_columns);
        $browse_sort_dropdown_display = $configuration_info['browse_sort_dropdown_display'];
        $browse_sort_dropdown_display = (in_array($browse_sort_dropdown_display, array ('always','never','gallery_only'))) ? $browse_sort_dropdown_display : 'always';
        $this->db->set_site_setting('browse_sort_dropdown_display', $browse_sort_dropdown_display);
//set text for icons
        foreach ($configuration_info['icons'] as $langId => $icons) {
            foreach ($icons as $textId => $text) {
                $text = geoString::toDB(trim($text));
                $queryData = array ($text, (int)$textId, (int)$langId);
                $result = $this->db->Execute("UPDATE " . geoTables::pages_text_languages_table . " SET `text`=? WHERE `text_id`=? AND `language_id`=?", $queryData);
                if (!$result) {
                    geoAdmin::m('DB error while updating icon locations. Debug info: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
                    return false;
                }
            }
        }

        if (geoPC::is_print()) {
            if (!isset($this->printSettingsClass)) {
                include_once ADMIN_DIR . 'print_settings.php';
                $this->printSettingsClass = new printSettings();
            }
            $this->printSettingsClass->browse_settings_update();
        }

        if ($configuration_info) {
            if ($configuration_info["number_of_ads_to_display"] != (int)$configuration_info["number_of_ads_to_display"]) {
                geoAdmin::m('Invalid entry for the number of listings per page.', geoAdmin::NOTICE);
            } elseif ($configuration_info["number_of_ads_to_display"] > 100) {
                geoAdmin::m('Warning: Displaying this many listings on a page may cause server timeouts or other errors, and will take a long time to load.  It is recommended to keep the number of ads to display less than 100.', geoAdmin::NOTICE);
            }
            $configuration_info["number_of_ads_to_display"] = (int)$configuration_info["number_of_ads_to_display"];
            $this->sql_query = "update " . $this->site_configuration_table . " set
				number_of_browsing_columns = " . $configuration_info["number_of_browsing_columns"] . ",
				number_of_browsing_subcategory_columns = " . $configuration_info["number_of_browsing_subcategory_columns"] . ",
				category_tree_display = " . $configuration_info["category_tree_display"] . ",
				display_category_description = " . $configuration_info["display_category_description"] . ",
				display_no_subcategory_message = " . $configuration_info["display_no_subcategory_message"] . ",
				photo_or_icon = \"" . $configuration_info["photo_or_icon"] . "\",
				thumbnail_max_height = \"" . $configuration_info["thumbnail_max_height"] . "\",
				thumbnail_max_width = \"" . $configuration_info["thumbnail_max_width"] . "\",
				featured_thumbnail_max_height = \"" . $configuration_info["featured_thumbnail_max_height"] . "\",
				featured_thumbnail_max_width = \"" . $configuration_info["featured_thumbnail_max_width"] . "\",
				display_sub_category_ads = \"" . $configuration_info["display_sub_category_ads"] . "\",
				display_category_count = \"" . $configuration_info["display_category_count"] . "\",
				browsing_count_format = \"" . $configuration_info["browsing_count_format"] . "\",
				number_of_ads_to_display = \"" . $configuration_info["number_of_ads_to_display"] . "\",
				" . //number_of_featured_ads_to_display = \"".$configuration_info["number_of_featured_ads_to_display"]."\",
                "number_of_new_ads_to_display = \"" . $configuration_info["number_of_new_ads_to_display"] . "\",";
            if (geoPC::is_ent() || geoPC::is_premier()) {
                $this->sql_query .= "use_search_form = \"" . $configuration_info["use_search_form"] . "\",
					seller_contact = \"" . $configuration_info["seller_contact"] . "\",";
            } else {
                $this->sql_query .= "use_search_form = \"0\",";
            }

            $this->sql_query .= "category_new_ad_limit = \"" . $configuration_info["category_new_ad_limit"] . "\",
				number_format = \"" . $configuration_info["number_format"] . "\",
				use_category_cache = \"" . $configuration_info["use_category_cache"] . "\",
				subscription_to_view_or_bid_ads = \"" . $configuration_info["subscription_to_view_or_bid_ads"] . "\",
				default_display_order_while_browsing = \"-1\", ";
//deprecated, but leaving it here for upgrades
                $this->sql_query .= "url_rewrite = \"" . $configuration_info["url_rewrite"] . "\"";
//set the number of active ads to display.
            if (!$this->db->get_site_setting('number_of_active_ads_to_display') || $this->db->get_site_setting('number_of_ads_to_display') == $this->db->get_site_setting('number_of_active_ads_to_display')) {
//only update number_of_active_ads_to_display if it is the same as the old value of number_of_ads_to_display, or
                //number_of_active_ads_to_display is false (not set)
                //Todo: Once number_of_active_ads_to_display is added to the main configuration,
                // can take this out...
                $this->db->set_site_setting('number_of_active_ads_to_display', intval($configuration_info['number_of_ads_to_display']));
            }

            $result = $this->db->Execute($this->sql_query);
//clear the settings cache
            geoCacheSetting::expire('configuration_data');
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } else {
                return true;
            }
        } else {
            $this->site_configuration_message = $this->internal_error_message;
            return false;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function configuration_home()
    {
                $this->body .= "<table cellpadding=3 cellspacing=0 width=100% border=0 align=center class=" . $this->get_row_color() . ">\n";
        $this->body .= "<tr class=row_color_red>\n\t\t<td colspan=2 class=very_large_font_light><strong>Site Setup</strong> \n\t\t</td>\n\t</tr>\n\t";
        $this->body .= "<tr class=row_color_red>\n\t\t<td colspan=2 class=medium_font_light>In this section you will configure the necessary functional elements
							as well as the applicable browsing settings of your site. \n\t\t</td>\n\t</tr>\n\t";
        $this->body .= "<tr>\n\t\t<td align=right valign=top><a href=index.php?mc=site_setup&page=main_general_settings><span class=medium_font><strong>general</strong></span></a>\n\t\t</td>\n\t\t
						<td class=medium_font>general site settings </a>\n\t\t</td>\n\t</tr>\n\t";
        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t\t<td align=right valign=top><a href=index.php?mc=site_setup&page=main_browsing_settings><span class=medium_font><strong>browsing</strong></span></a>\n\t\t</td>\n\t\t
						<td class=medium_font>configure the settings for your user's browsing </a>\n\t\t</td></tr>\n\t";
        $this->body .= "</table>\n";
        return true;
    } //end of function configuration_home

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function ban_ip_form()
    {
        $admin = geoAdmin::getInstance();
        $this->body = $admin->getUserMessages();
        $sql = "SELECT * FROM " . geoTables::ip_ban_table . " ORDER BY `ip`";
        $ip_result = $this->db->GetAll($sql);
        $this->body .= '<fieldset><legend>Ban New IP</legend>
						<div class="center">
						<form action="" method="post"><input type="text" name="b" /> <input type="submit" name="auto_save" value="Ban IP"></form>
						</div>
						</fieldset>';
        if ($ip_result === false) {
            $admin->userError('DB Error, please try again.  Debug info: ' . $this->db->ErrorMsg());
            return false;
        } elseif (count($ip_result) > 0) {
            $this->body .= "<fieldset><legend>Current Banned IPs</legend><div>";
            $count = count($ip_result);
            foreach ($ip_result as $show_ip) {
                $row_color = ($row_color == 'row_color1') ? 'row_color2' : 'row_color1';
                $delete_button = geoHTML::addButton('Delete', 'index.php?mc=site_setup&amp;page=main_ip_banning&amp;c=' . $show_ip['ip_id'] . '&amp;auto_save=1', false, '', 'lightUpLink mini_cancel');
                $this->body .= '<div class="' . $row_color . '" style="padding-bottom: 27px;"><span class="col-lg-6 col-sm-6 col-xs-6" style="text-align: right;">' . $show_ip['ip'] . '</span><span class="col-lg-6 col-sm-6 col-xs-6">' . $delete_button . '</span></div>';
            }
            $this->body .= "</div>
			</fieldset>";
        } else {
        //0 results
            $this->body .= "
			<fieldset>
				<legend>Current Banned IPs</legend>
				<div class=\"page_note_error\">There are currently no banned IPs.</div>
			</fieldset>";
        }



        return true;
    } //end of function ban_ip_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_ip_from_list($db, $ip_id = 0)
    {
        if (PHP5_DIR) {
            $admin = geoAdmin::getInstance();
        } else {
            $admin =& geoAdmin::getInstance();
        }

        if ($ip_id) {
            $sql_query = "delete from  " . $this->ip_ban_table . " where ip_id = " . $ip_id . " LIMIT 1";
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $admin->userError('DB Error, please try again.  Debug info: ' . $this->db->ErrorMsg());
                return false;
            } else {
                $admin->userSuccess('IP Removed from ban list.');
                return true;
            }
        } else {
            $admin->userError('Error, please try again.  Debug info: no id provided.');
            return false;
        }
    } //end of function delete_ip_from_list

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_ip_to_ban_list($db, $ip = 0)
    {
        if (PHP5_DIR) {
            $admin = geoAdmin::getInstance();
        } else {
            $admin =& geoAdmin::getInstance();
        }
        if ($ip) {
//check ip format
            $sql_query = "insert into " . $this->ip_ban_table . " (ip) values (?)";
            $result = $this->db->Execute($sql_query, array($ip));
            if ($this->debug_site) {
                echo $sql_query . "<br>\n";
            }
            if (!$result) {
                $admin->userError('DB Error, please try again.  Debug info: ' . $this->db->ErrorMsg());
                return false;
            } else {
                $admin->userSuccess('IP added to ban list.');
                return true;
            }
        } else {
            $admin->userError('Error, please try again.  Debug info: no id provided.');
            return false;
        }
    } //end of function insert_ip_to_ban_list


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_main_general_settings()
    {
        $this->display_general_configuration_form();
        $this->display_page();
    }
    function update_main_general_settings()
    {
        if ($_REQUEST["b"]) {
//update html allowed
            return $this->update_general_configuration($_REQUEST["b"]);
        } else {
            return false;
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_main_browsing_settings()
    {
        $this->display_browse_configuration_form();
        $this->display_page();
    }
    function update_main_browsing_settings()
    {
        if ($_REQUEST["b"]) {
            return $this->update_browse_configuration($_REQUEST["b"]);
        } else {
            return false;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_main_ip_banning()
    {
        $this->ban_ip_form();
        $this->display_page();
    }
    function update_main_ip_banning()
    {
        if ($_REQUEST["b"]) {
//insert an ip into the ban list
            return $this->insert_ip_to_ban_list($db, $_REQUEST["b"]);
        } elseif (isset($_REQUEST["c"]) && is_numeric($_REQUEST["c"])) {
            return $this->delete_ip_from_list($this->db, $_REQUEST["c"]);
        } else {
            if (PHP5_DIR) {
                $admin = geoAdmin::getInstance();
            } else {
                $admin =& geoAdmin::getInstance();
            }
            $admin->userError('Invalid entry, please try again.');
            return false;
        }
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
}
