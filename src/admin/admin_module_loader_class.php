<?php

/**
 * This class used as a wrapper, to pull in older modules into the new admin page loader,
 * that have not yet been converted to use the new Addon system.
 *
 */
class module_loader extends Admin_site
{
    function display_main_api_integration()
    {
        //should only be displayed if they still have the old API files intact.
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $menu_loader->userNotice('The Geo API has been replaced by the Bridge Addon.');
        $menu_loader->userNotice('These API installations are for <em>viewing only</em>.  The installations listed below are set in the old Geo API and are not used in 3.1 until you have created each installation in the Bridge Addon.');
        $menu_loader->userNotice('Note that any Geodesic Solutions product must be updated to 3.1 to allow linking to this one, as the bridge is only compatible with 3.1 or higher.  See user manual for more information.');
        ob_start();
        $admin_api = new Api_admin_site();
        if ($admin_api->connected) {
            $admin_api->list_installations();
        }
        $this->body .= $menu_loader->getUserMessages() . $admin_api->list_installations();

        $this->display_page();
    }
}


class Api_admin_site extends Admin_site
{
    var $api_db;
    var $installations;
    var $installation_info;
    var $installation_configuration_data;
    var $sql_query;
    var $error_message;
    var $row_count;
    var $row_color1 = "ffffff";
    var $row_color2 = "f0faff";
    var $row_color_red = "659ACC";
    var $row_color_black = "659ACC";
    var $very_large_font_tag_light = "<font face=arial,helvetica size=4 color=#FFFFFF>";
    var $medium_font_tag_light = "<font face=arial,helvetica size=2 color=#FFFFFF>";
    var $medium_font_tag = "<font face=arial,helvetica size=2 color=#000000>";
    var $small_font_tag = "<font face=arial,helvetica size=1 color=#000000>";
    var $debug_admin_site = 0;
    var $connected = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function showDisabledAlert()
    {
        return 'javascript:alert(\'DISABLED: This is for VIEWING ONLY, so that you can migrate existing API installations to the new Bridge Addon.  See the user manual for more details.\')';
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_all_installation_info()
    {
        //select all live installations
        $this->sql_query = "select * from geodesic_api_installation_info";
        $get_installation_result = $this->api_db->Execute($this->sql_query);
        if ($this->debug_admin_site) {
            echo $this->sql_query . "<br>\n";
        }
        if (!$get_installation_result) {
            if ($this->debug_admin_site) {
                echo $this->sql_query . "<br>";
                echo "<br><br>Database Error:<br>" . $this->api_db->ErrorMsg() . "<BR><Br>\n";
            }
            return false;
        } elseif ($get_installation_result->RecordCount() > 0) {
            //put in an array?
            $this->installations = $get_installation_result;
            return true;
        } else {
            $this->installations = 0;
            return true;
        }
    } //end of function get_all_installation_info


    function list_installations()
    {
        $html = "<fieldset>
				<legend>Installations</legend>
				<table cellpadding=3 cellspacing=2 border=0 align=center class='row_color1' width=\"100%\">
			<tr>
				<td class=col_hdr_left>Name</td>
				<td class=col_hdr_left>API Installation Type" . $this->show_tooltip(1, 1) . "</td>
				<td class=col_hdr_left>Compatible Bridge Type" . $this->show_tooltip(2, 1) . "</td>
				<td class=col_hdr_left>Compatibility Notes" . $this->show_tooltip(3, 1) . "</td>
				<td class=col_hdr_left>Status" . $this->show_tooltip(4, 1) . "</td>
				<td class=col_hdr_left>Admin Email</td>
				<td class=col_hdr_left>DB Host</td>
				<td class=col_hdr_left>DB User</td>
				<td class=col_hdr_left>DB Pass</td>
				<td class=col_hdr_left>DB Name</td>
				<td class=col_hdr_left>Cookie path</td>
				<td class=col_hdr_left>Cookie domain</td>
				<td class=col_hdr_left>VBulletin: Path to config.php</td>
				<td class=col_hdr_left>VBulletin: License</td>
				<td class=col_hdr_left>Phorum: Table prefix</td>
				<td class=col_hdr_left>Edit</td>
			</tr>";
        $this->get_all_installation_info();
        if ($this->installations) {
            $this->row_count = 0;
            while ($this->installation_info = $this->installations->FetchRow()) {
                //figure out type and compatibility messages
                $bridge_type = 'Geo Software 3.1+';
                $comp = 'Update installation to 3.1';
                switch ($this->installation_info["installation_type"]) {
                    case 1:
                        $current_type = 'Enterprise Classified 2.0.5.3';
                        break;
                    case 2:
                        $current_type = 'Premier Classifieds 2.0.4';
                        break;
                    case 3:
                        $current_type = "Full Classifieds 2.0.3";
                        break;
                    case 4:
                        $current_type = "Basic Classifieds 2.0.4";
                        break;
                    case 5:
                        $current_type = "Premier Auctions 2.0.3";
                        break;
                    case 6:
                        $current_type = "Enterprise Auctions 1.0.6";
                        break;
                    case 7:
                        $current_type = "GeoCore";
                        $comp = 'Contact Geo Support';
                        $bridge_type = 'N/A';
                        break;
                    case 8:
                        $current_type = "vBulletin";
                        $bridge_type = 'vBulletin';
                        $comp = 'Bridge fully tested using vBulletin 3.6.8';
                        break;
                    case 9:
                        $current_type = "Phorum";
                        $comp = 'Contact Geo Support for this bridge type';
                        $bridge_type = 'Phorum';
                        break;
                    case 10:
                        $current_type = "ClassAuctions 1.0.5b";
                        break;
                    case 16:
                        $current_type = 'Geo Software 3.0+';
                        break;
                    default:
                        $current_type = 'Unkown';
                        $comp = 'Contact Geo Support';
                        $bridge_type = 'N/A';
                }

                $current_status = ($this->installation_info["active"]) ? 'Active' : 'Inactive';
                $html .= "
			<tr class=" . $this->get_row_color() . ">
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["installation_name"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$current_type}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$bridge_type}</td>
				<td class=\"medium_font\">{$comp}</td>
				<td class=\"medium_font\">{$current_status}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["admin_email"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["db_host"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["db_username"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["db_password"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["db_name"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["cookie_path"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["cookie_domain"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["vbulletin_config_path"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["vbulletin_license_key"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\">{$this->installation_info["phorum_database_table_prefix"]}</td>
				<td class=\"medium_font\" style=\"white-space: nowrap;\"><a href=\"javascript:void(0)\" onclick=\"" . $this->showDisabledAlert() . "\">Edit</a></td>
			</tr>";
                $this->row_count++;
            } // end of while
        } else {
            $html .= "<tr><td colspan=7 style='font-style: italic; text-align: left;'>\n\t" . $this->medium_font_tag . "No installations\n\t</td>\n\t</tr>\n";
        }
        $html .= "<tr><td colspan=7 align=center class=medium_font>\n\t<a href=\"javascript:void(0)\" onclick=\"" . $this->showDisabledAlert() . "\"><strong>Add New Installation</strong></a>\n\t\n\t</td>\n\t</tr>\n";
        $html .= "</table>
			</fieldset>";

        $html .= "
		<script type=\"text/javascript\">
			Text[1] = [\"API Installation Type\", \"This is the installation type, as set in the old Geo API.  If version is specified, it may not reflect the actual current version of that installation.\"]\n
			Text[2] = [\"Compatible Bridge Type\", \"This is the &quot;Bridge Type&quot; to use when entering the installation in the new Bridge Addon.<br /><br />Note that the Bridge Type name may vary slightly.<br /><br />In your Bridge Addon, if you do not see the correct Bridge Type, contact Geo Support.\"]\n
			Text[3] = [\"Compatibility Notes\", \"For more information and details on compatibility, see the information about the applicable &quot;bridge type&quot; within the new Bridge Addon.\"]\n
			Text[4] = [\"Status\", \"Only shows active/inactive status as set in the API configuration.<br /><br />This setting is equivalent to the enabled/disabled setting in the Bridge Addon.<br /><br />Since the Geo API has been replaced with the Bridge Addon, even if the status is Active in this view, the installation will not be syncronized until it is added to the new Bridge Addon.\"]\n

		</script>";

        return $html;
    } // end of function list_installations

    function get_row_color()
    {
        if (($this->row_count % 2) == 0) {
            $row_color = 'row_color2';
        } else {
            $row_color = 'row_color1';
        }
        return $row_color;
    } //end of function get_row_color
}
