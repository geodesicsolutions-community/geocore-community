<?php

//admin_user_management_class.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.10.0-15-gf172108
##
##################################

class Admin_user_management extends Admin_site
{
    //used to keep track of what 'order by' the previous search took place in
    //if current is different from this one the search will respond with the first 25 of the
    //returned search set
    var $order_by_switch = 0;
    var $search_group = 0;
    var $user_management_error;
    var $filter_dropdown_id_array = array();
    var $filter_dropdown_name_array = array();
    var $debug_user = 0;
    var $updated = 0;

    function list_users($list_info = 0)
    {
        //list_info will contain
        //order by = list_info[order_by]
        //limit = list_info[limit]
        //search_group = list_info[search_group]
        //only prints 25 users at a time
        //shows username,first name, last name, #current listings, locked, edit, remove

        // U+25B2
        // U+25BC
        require_once(ADMIN_DIR . 'AJAX.php');
        require_once(ADMIN_DIR . 'AJAXController/ListUsers.php');

        geoAdmin::getInstance()->v()->addTop("<script type='text/javascript'>" . ADMIN_AJAXController_ListUsers::getJavascript() . "</script>");

        $this->body .= "<div class='x-content search-users-input'>
							<form action='index.php?mc=users&page=users_search' class='form-horizontal form-label-left' method='post'>
							  <div class='form-group'>
							    <div class='input-group'>
								  <input type='text' name='b[text_to_search]' class='form-control selected-border' placeholder='Search Users' style='border-right:0;'/>
								  <input type='hidden' name='b[search_group]' value='0' />
								  <input type='hidden' name='b[search_type]' value='1' />
								  <span class='input-group-btn'><input type='submit' value='Go' class='btn btn-primary' /></span>
							    </div>
							  </div>
							</form>
						</div>";

        $this->body .= "
		<div class='table-responsive'>
		<table cellpadding=2 cellspacing=0 width=100% class=\"table table-hover table-striped table-bordered\">
			<thead>
				<tr class=\"col_hdr_top\">
					<th width=15% class=\"sorting_col\" id='username' style='text-align:left; min-width:100px;'><a href=\"javascript:geo_sortTable('username', 'body');\">Username</a>&nbsp;<img src='admin_images/admin_arrow_up.gif' id='dir_arrow'></th>
					<th width=10% id='lastname' style='text-align:left; min-width:60px;'><a href=\"javascript:geo_sortTable('lastname', 'body');\">Last</a>&nbsp;</th>
					<th width=10% id='firstname' style='text-align:left; min-width:65px;'><a href=\"javascript:geo_sortTable('firstname', 'body');\">First</a>&nbsp;</th>
					<th width=10% id='status' style='min-width:75px;'><a href=\"javascript:geo_sortTable('status', 'body');\">Status</a>&nbsp;</th>";
        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            $this->body .= "
					<th width=15% class=\"col_hdr\" id='price_plan_id' style='min-width:150px;'><a href=\"javascript:geo_sortTable('price_plan_id', 'body');\">Classifieds Pricing</a>&nbsp;</th>
					<th width=15% class=\"col_hdr\" id='auction_price_plan_id' style='min-width:150px;'><a href=\"javascript:geo_sortTable('auction_price_plan_id', 'body');\">Auctions Pricing</a>&nbsp;</th>";
        } elseif (geoMaster::is('auctions')) {
            if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
                $this->body .= "
					<th width=15% class=\"col_hdr\" id='auction_price_plan_id'><a href=\"javascript:geo_sortTable('auction_price_plan_id', 'body');\">Price Plan</a>&nbsp;</th>";
            }
        } elseif (geoMaster::is('classifieds')) {
            if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
                $this->body .= "
					<th width=15% class=\"col_hdr\" id='price_plan_id'><a href=\"javascript:geo_sortTable('price_plan_id', 'body');\">Price Plan</a>&nbsp;</th>";
            }
        }
        $this->body .= "
					<th width=10% class=\"col_hdr\" id='date_joined' style='min-width:80px;'><a href=\"javascript:geo_sortTable('date_joined', 'body');\">Joined</a>&nbsp;</th>
					<th width=15% class=\"col_hdr\">
						Display <select name='limit_by' id='limit_by' onchange='geo_setUsers(this.options[this.selectedIndex].value)'>
							<option value='10'>10</option>
							<option value='25' selected>25</option>
							<option value='50'>50</option>
							<option value='75'>75</option>
							<option value='100'>100</option>
						</select> Users
					</th>
				</tr>
			</thead>";

        $this->body .= "


			<tbody id='result_body'>
				<tr>
					<td valign=center align=center colspan=\"100%\"><img src='admin_images/loading.gif'><br />Loading...</td>
				</tr>
			</tbody>

			<tfoot>
				<tr>
					<td id='result_footer' class='col_ftr2' style='text-align: right; padding: 4px;' colspan='9'></td>
				</tr>
			</tfoot>

			</table></div>

			<div id='result_pagination'></div>
		";

        $this->body .= "
			<br /><br />
			<table width=\"100%\">
				<tr>
					<td colspan='100%' align=center>
						<a href=index.php?mc=users&page=users_add class='mini_button'>Add New User</a>
					</td>
				</tr>";


        $currentUsers = $this->current_logged_in_users($db);

        if ($currentUsers == false) {
            $currentUsers = "<em>none</em>";
        }
        $this->body .= "
				<tr class=\"medium_font\">
					<td colspan='100%' style='text-align: left;'>Users currently logged in: " . $currentUsers  . "</td>
				</tr>
			</table>";
    } //end of list_user

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function list_user_order_by_box($db)
    {
        $this->body .= "<form action=index.php?mc=users&page=users_list id=sortForm method=post>\n";
        $this->body .= "<input type='hidden' name='auto_save' value='1'>";
        $this->body .= "<fieldset id='SortUsers'><legend>Sort Users</legend><table width=600 cellpadding=5 cellspacing=1 border=0 align=center>\n";
        $this->body .= "<tr>\n\t<td class=medium_font align=center>\n\t<br><input onClick=\"document.getElementById('sortForm').submit();\" type=radio name=b[order_by]";
        if ($this->order_by_switch == 1) {
            $this->body .= " checked";
        }
        $this->body .= " value=1><strong>by Username&nbsp;&nbsp;</strong>\n\t<input onClick=\"document.getElementById('sortForm').submit();\" type=radio name=b[order_by]";
        if ($this->order_by_switch == 2) {
            $this->body .= " checked";
        }
        $this->body .= " value=2><strong>by Last Name&nbsp;&nbsp;</strong>\n\t<input onClick=\"document.getElementById('sortForm').submit();\" type=radio name=b[order_by]";
        if ($this->order_by_switch == 3) {
            $this->body .= " checked";
        }
        $this->body .= " value=3><strong>by Date Joined (latest first)&nbsp;&nbsp;</strong>\n\t<input onClick=\"document.getElementById('sortForm').submit();\" type=radio name=b[order_by]";
        if ($this->order_by_switch == 4) {
            $this->body .= " checked";
        }
        $this->body .= " value=4><strong>by Date Joined (earliest first)&nbsp;&nbsp;</strong><br><br>\n\t\n\t";

        $this->body .= $this->group_dropdown($db);

        $this->body .= "</td>\n</tr>\n";
        $this->body .= "</table></fieldset>\n";
        $this->body .= "</form>\n";
    } //end of function list_user_order_by_box

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_user_line($db, $show)
    {
        $current_status = $this->get_current_status($db, $show["id"]);
        $this->body .= "<tr class=" . $this->get_row_color() . ">\n\t\t<td class=small_font>{$show["username"]} {$show['verify_icon']}
			<input type=hidden name=b[" . $show["id"] . "] value=" . $show["username"] . ">\n\t\t</td>\n\t\t";
        $this->body .= "<td class=small_font>" . stripslashes($show["lastname"]) . "</font>\n\t\t</td>\n\t\t";
        $this->body .= "<td class=small_font>" . stripslashes($show["firstname"]) . "</font>\n\t\t</td>\n\t\t";
        $this->body .= "<td class=small_font align=center>";
        $anonR = geoAddon::getRegistry('anonymous_listing');
        if ($anonR) {
            $anonUser = $anonR->anon_user_id;
        }
        if ($anonUser == $show['id']) {
            $this->body .= "System User";
        } elseif ($current_status == 1) {
            $this->body .= "Active";
        } else {
            $this->body .= "Suspended";
        }
        $this->body .= "</font>\n\t\t</td>\n\t\t";

        if (geoMaster::is('classifieds')) {
            if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
                $this->body .= "<td class='small_font' align='center'>" . $this->get_price_plan_name($db, $show['price_plan_id']) . "</td>";
            }
        }
        if (geoMaster::is('auctions')) {
            if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
                $this->body .= "<td class='small_font' align='center'>" . $this->get_price_plan_name($db, $show['auction_price_plan_id']) . "</td>";
            }
        }
        $view_button = geoHTML::addButton('View', 'index.php?mc=users&page=users_view&b=' . $show["id"]);
        if ($anonUser != $show['id']) {
            $remove_button = geoHTML::addButton('Remove', 'index.php?mc=users&page=users_remove&b=' . $show["id"], false, '', 'mini_cancel');
        }
        $this->body .= "<td align=center>" . $view_button . " " . $remove_button . "</td>";
        $this->body .= "</tr>\n";
    } //end of function show_user_line

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function current_logged_in_users($db)
    {
        $userList = "";
        $query = "SELECT DISTINCT user_id FROM " . $this->db->geoTables->session_table . " WHERE user_id > 1";
        $currentUsers = $this->db->Execute($query);
        if (!$currentUsers || $currentUsers->RecordCount() == 0) {
            return false;
        }
        while ($line = $currentUsers->FetchRow()) {
            $sql = "SELECT * FROM " . $this->db->geoTables->userdata_table . " WHERE id = " . $line['user_id'];
            $result = $this->db->Execute($sql);
            $userdata = $result->FetchRow();

            $userList .= '<span style="white-space:nowrap"><strong>' . $userdata['username'] . '</strong> (' . $userdata['firstname'] . ' ' . $userdata['lastname'] . ')</span> ';
        }
        return $userList;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function edit_user_form($db, $user_id = 0)
    {
        $menu_loader = geoAdmin::getInstance();

        $this->body .= "<script>";
        // Set title and text for tooltip
        $this->body .= "Text[1] = [\"Password\", \"Edit the user's password here. The min and max allowed length is set in Admin Tools & Settings > Security Settings > General Security Settings.  Leave blank to keep the same password (unless changing the username).\"]\n";
        $this->body .= 'Text[2] = ["Username","If you change the username, make sure the password fields are also filled in (even if the password stays the same), because of the way the passwords are stored."]' . "\n";

        $this->body .= "</script>";
        $sql = "SELECT * FROM " . $this->site_configuration_table;
        $result = $this->db->Execute($sql);

        if (!$result) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        } else {
            $this->configuration_data = $result->FetchRow();
        }

        $sql = "SELECT * FROM " . $this->registration_configuration_table;

        $result = $this->db->Execute($sql);

        if (!$result) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        } else {
            $this->registration_configuration = $result->FetchRow();
        }

        if (!$user_id) {
            //no user id
            return false;
        }

        $user_data = $this->get_user_data($user_id);
        //highlight_string(print_r($user_data,1));
        $sql_query = "select * from " . $this->db->geoTables->logins_table . "	where id = " . $user_id;
        $password_result = $this->db->Execute($sql_query);
        if (!$password_result) {
            return false;
        } elseif ($password_result->RecordCount() == 1) {
            $show_password = $password_result->FetchRow();
            if ($user_data) {
                if ($this->user_management_error) {
                    $menu_loader->userError($this->user_management_error);
                }

                $this->body .= $menu_loader->getUserMessages();
                //display the form to edit the userdata
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=users&page=users_edit&b=" . $user_id . " class='form-horizontal form-label-left' method=post>\n";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }

                $this->body .= "<div class=\"page-title1\">User: <span class='color-primary-two'><span style='font-weight: bold; white-space:nowrap;'>" . stripslashes($user_data["firstname"]) . " " . stripslashes($user_data["lastname"]) . "</span>	| " . $user_data["username"] . "</span> <span class='color-primary-six' style='font-size: 0.8em; white-space:nowrap;'>(User ID#: " . $user_id . ")</span></div>";
                $this->body .= "<fieldset id='PersonalUserData'><legend>Personal Data</legend>";
                $this->body .= "<div class='x_content'>";

                if ($user_id == 1) {
                    $this->body .= "<div class='page_note'>ADMIN USER: Change username and password with <a href='index.php?page=admin_tools_password&mc=admin_tools_settings'>this form</a></div>";
                }

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>User ID:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>" . $user_data["id"] . "</span></div>";
                $this->body .= "</div>";

                $current_status = $this->get_current_status($db, $user_id);

                //find out if this is the Anonymous user
                $anon = geoAddon::getRegistry('anonymous_listing');
                if ($anon) {
                    $anon_user = $anon->get('anon_user_id', 0);
                } else {
                    $anon_user = 0;
                }
                $isAnon = ($user_id == $anon_user) ? true : false;

                if ($user_id != 1 && !$isAnon) { // can't change username/password/status for admin/anon users from this form
                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Current Status:</label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'> ";
                    $this->body .= "<input type=radio name=c[status] value=1 ";
                    if ($current_status == 1) {
                        $this->body .= " checked";
                    }
                    $this->body .= "> Active <br><input type=radio name=c[status] value=2 ";
                    if ($current_status == 2) {
                        $this->body .= " checked";
                    }
                    $this->body .= "> Suspended ";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    if (strlen($show_password['password']) == 40 || $this->db->get_site_setting('client_pass_hash') !== 'core:plain') {
                        //most likely, password is hashed...
                        $pass_req_text = 'Required if Username Edited Above';
                        $new_pass_text = 'New ';
                        $pass = '';
                    } else {
                        $pass_req_text = '';
                        $new_pass_text = '';
                        $pass = $show_password['password'];
                    }

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Username: " . $this->show_tooltip(2, 1) . "</label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[username] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["username"] . "\" />";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>{$new_pass_text}Password: " . $this->show_tooltip(1, 1) . "</label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=\"password\" name=\"c[password]\" class='form-control col-md-7 col-xs-12' placeholder='{$pass_req_text}' value=\"{$pass}\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>{$new_pass_text}Password Verifier: " . $this->show_tooltip(1, 1) . "</label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=\"password\" name=\"c[password_verifier]\" class='form-control col-md-7 col-xs-12' value=\"{$pass}\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";
                }

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>First Name: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[firstname] class='form-control col-md-7 col-xs-12' value=\"" . stripslashes($user_data["firstname"]) . "\">";
                $this->body .= "</div>";
                $this->body .= "</div>";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Last Name: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[lastname] class='form-control col-md-7 col-xs-12' value=\"" . stripslashes($user_data["lastname"]) . "\">";
                $this->body .= "</div>";
                $this->body .= "</div>";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Date Registered: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'>";
                if ($user_data["date_joined"] == 0) {
                    $this->body .= " Not Available ";
                } else {
                    $this->body .= date("M d,Y G:i - l", $user_data["date_joined"]);
                }
                $this->body .= "</span></div>";
                $this->body .= "</div>";

                $this->body .= "<div class='form-group'>";
                $tooltip = geoHTML::showTooltip('Admin Note', "Here, you can save a short, private note about this user, viewable only in the admin");
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Admin Note: {$tooltip}</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><textarea name='c[admin_note]' class='form-control'>" . geoString::fromDB($user_data['admin_note']) . '</textarea>';
                $this->body .= "</div>";
                $this->body .= "</div>";

                $this->body .= "</div></fieldset>";


                $this->body .= "<fieldset id='BusinessData'><legend>Business Data</legend>";
                $this->body .= "<div class='x_content'>";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Company Name: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[company_name] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["company_name"] . "\">";
                $this->body .= "</div>";
                $this->body .= "</div>";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Business Type: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=radio name=c[business_type] value=1 ";
                if ($user_data["business_type"] == 1) {
                    $this->body .= " checked";
                }
                $this->body .= "> Individual <br><input type=radio name=c[business_type] value=2 ";
                if ($user_data["business_type"] == 2) {
                    $this->body .= " checked";
                }
                $this->body .= "> Business ";
                $this->body .= "</div>";
                $this->body .= "</div>";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>URL: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[url] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["url"] . "\">";
                $this->body .= "</div>";
                $this->body .= "</div>";

                $this->body .= "</div></fieldset>";


                $this->body .= "<fieldset><legend>Address Information</legend>";
                $this->body .= "<div class='x_content'>";

                if ($user_id == 1) {
                    $this->body .= "<div class='page_note'>ADMIN USER: Set Company Address on <a href='index.php?page=main_general_settings&mc=site_setup'>Site Setup > General Settings</a> page</div>";
                } else {
                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Address: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[address] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["address"] . "\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Address Line 2: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[address_2] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["address_2"] . "\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $regionOverrides = geoRegion::getLevelsForOverrides();
                    $regionSelector = geoRegion::regionSelector('locations', geoRegion::getRegionsForUser($user_id));
                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Location: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>.$regionSelector.";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    if (!$regionOverrides['city']) {
                        $this->body .= "<div class='form-group'>";
                        $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>City: </label>";
                        $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[city] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["city"] . "\">";
                        $this->body .= "</div>";
                        $this->body .= "</div>";
                    }

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Zip/Postal Code: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[zip] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["zip"] . "\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";
                }

                $this->body .= "</div></fieldset>";


                $this->body .= "<fieldset><legend>Contact Information</legend>";
                $this->body .= "<div class='x_content'>";

                if ($user_id == 1) {
                    $contactLink = geoAddon::getInstance()->isEnabled('contact_us') ? 'index.php?page=addon_contact_us_main&mc=addon_cat_contact_us' : 'index.php?page=addon_tools&mc=addon_management';
                    $this->body .= "<div class='page_note'>ADMIN USER: Set Contact Information on <a href='index.php?page=email_general_config&mc=email_setup'>Email Setup</a> page and <a href='$contactLink'>Contact Us Addon</a>.</div>";
                } else {
                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Email: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[email] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["email"] . "\"><br /><input type='checkbox' name='c[apply_to_all_email]' value='1'> Apply to all Listings";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Phone: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[phone] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["phone"] . "\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Phone 2: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[phone2] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["phone2"] . "\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $this->body .= "<div class='form-group'>";
                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Fax: </label>";
                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=c[fax] class='form-control col-md-7 col-xs-12' value=\"" . $user_data["fax"] . "\">";
                    $this->body .= "</div>";
                    $this->body .= "</div>";

                    $sql_query = "select * from " . $this->registration_configuration_table;
                    $result = $this->db->Execute($sql_query);
                    if (!$result) {
                        $this->site_error($this->db->ErrorMsg());
                        return false;
                    } elseif ($result->RecordCount() == 1) {
                        $registration_configuration = $result->FetchRow();
                        for ($i = 1; $i < 11; $i++) {
                            if ($this->registration_configuration["use_registration_optional_{$i}_field"]) {
                                $this->body .= "<div class='form-group'>";
                                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>" . $this->registration_configuration['registration_optional_' . $i . '_field_name'] . ":</label>";
                                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";

                                $sql = "select * from " . $this->registration_choices_table . " where type_id = " . $this->registration_configuration["registration_optional_{$i}_field_type"] . " order by display_order, value";
                                $type_result = $this->db->Execute($sql);
                                if (!$type_result) {
                                    return false;
                                } elseif ($type_result->RecordCount() > 0) {
                                    $this->body .= "<select name='c[optional_field_{$i}]' class='form-control col-md-7 col-xs-12'>";
                                    for ($d = 0; $show_dropdown = $type_result->FetchRow(); $d++) {
                                        $sel = ($user_data['optional_field_' . $i] == $show_dropdown['value']) ? "selected='selected'" : '';
                                        $this->body .= "<option $sel>" . $show_dropdown['value'] . "</option>";
                                    }
                                    $this->body .= "</select>";
                                } else {
                                    //no option data from query -- make this a text input
                                    $this->body .= "<input type='text' name='c[optional_field_{$i}]' value='" . $user_data["optional_field_" . $i] . "' class='form-control col-md-7 col-xs-12'> ";
                                }
                                if ($this->registration_configuration["require_registration_optional_{$i}_field"]) {
                                    $this->body .= '*';
                                }
                                if (isset($this->error["optional_field_{$i}"])) {
                                    $this->body .= "<font color=#880000 size=1 face=arial>optional field {$i} required</font>";
                                }
                                $this->body .= "</div>";
                                $this->body .= "</div>";
                            }
                        }
                    }
                }

                $this->body .= "</div></fieldset>";


                if ($user_id != 1) { //admin user can't change these settings
                    if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
                        $this->body .= "<fieldset id='AccountInfo'><legend>Account Information</legend>";
                        $this->body .= "<div class='x_content'>";

                        $sql = "select * from " . $this->user_groups_price_plans_table . " where id = " . $user_id;
                        $user_group_result = $this->db->Execute($sql);
                        if ($this->debug_user) {
                            echo $sql_query . " is the query<br>\n";
                        }
                        if (!$user_group_result) {
                            //echo $sql."<br>\n";
                            //do nothing
                        } elseif ($user_group_result->RecordCount() == 1) {
                            $show_user_stuff = $user_group_result->FetchRow();
                            $group_name = $this->get_group_name($db, $show_user_stuff["group_id"]);
                            if ($group_name) {
                                //change group

                                $this->body .= "<div class='form-group'>";
                                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>User Group: </label>";
                                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
								<select name=c[group] class='form-control col-md-7 col-xs-12'>";
                                $sql = "select * from " . $this->classified_groups_table;
                                $all_groups_result = $this->db->Execute($sql);
                                if ($this->debug_user) {
                                    echo $sql_query . " is the query<br>\n";
                                }
                                if (!$all_groups_result) {
                                    //echo $sql."<br>\n";
                                    //do nothing
                                } elseif ($all_groups_result->RecordCount() > 0) {
                                    while ($show_groups = $all_groups_result->FetchRow()) {
                                        $this->body .= "<option value=" . $show_groups["group_id"];
                                        if ($show_groups["group_id"] == $show_user_stuff["group_id"]) {
                                            $this->body .= " selected";
                                            $this->body .= ">" . $this->get_group_name($db, $show_groups["group_id"]) . " (current)</option>\n\t\t";
                                        } else {
                                            $this->body .= ">" . $this->get_group_name($db, $show_groups["group_id"]) . "</option>\n\t\t";
                                        }
                                    }
                                }
                                $recurringText = (geoPC::is_ent()) ? ", cancel all recurring billings for the user (if possible)," : '';
                                $this->body .= "</select><br><b>NOTE:</b> Changing the User Group will delete this user's current subscriptions$recurringText and also
									move the user into the price plan attached to the new User Group.  The credits or subscriptions can then be
									manually added back.";
                                $this->body .= "</div>";
                                $this->body .= "</div>";
                            }


                            //change expiration or credits
                            if (geoMaster::is('auctions')) {
                                $auction_price_plan = $this->get_price_plan($db, $show_user_stuff["auction_price_plan_id"]);
                            }
                            if (geoMaster::is('classifieds')) {
                                $classified_price_plan = $this->get_price_plan($db, $show_user_stuff["price_plan_id"]);
                            }
                            if ($auction_price_plan || $classified_price_plan) {
                                //current price plan
                                if (geoMaster::is('auctions')) {
                                    $this->body .= "<div class='form-group'>";
                                }
                                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Auction Price Plan Attached: </label>";
                                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'><a href=index.php?mc=pricing&page=pricing_edit_plans&g=" . $show_user_stuff["auction_price_plan_id"] . ">" . $auction_price_plan["name"] . "</a></span>";
                                    $this->body .= "</div>";
                                    $this->body .= "</div>";

                                if (geoMaster::is('classifieds')) {
                                    $this->body .= "<div class='form-group'>";
                                }
                                    $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Classified Price Plan Attached: </label>";
                                    $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'><span class='vertical-form-fix'><a href=index.php?mc=pricing&page=pricing_edit_plans&g=" . $show_user_stuff["price_plan_id"] . ">" . $classified_price_plan["name"] . "</a></span>";
                                    $this->body .= "</div>";
                                    $this->body .= "</div>";

                                if ($auction_price_plan["type_of_billing"] == 2 || $classified_price_plan["type_of_billing"] == 2) {
                                    //charge by subscription -- display when expire
                                    $this->body .= "<tr class=row_color" . (($color++ % 2) + 1) . ">\n\t<td align=right class=medium_font><strong>Subscription Expires: </strong></td>\n\t";
                                    $this->body .= "<td class=medium_font>\n\t";
                                    $sql = "select * from " . $this->classified_user_subscriptions_table . " where user_id = " . $user_id;
                                    //echo $sql."<br>\n";
                                    $get_subscription_result = $this->db->Execute($sql);
                                    if ($this->debug_user) {
                                        echo $sql_query . " is the query<br>\n";
                                    }
                                    if (!$get_subscription_result) {
                                        return false;
                                    } elseif ($get_subscription_result->RecordCount() == 1) {
                                        $show_subscription = $get_subscription_result->FetchRow();
                                        $this->body .= "expires on " . date("M d, Y H:i:s", $show_subscription["subscription_expire"]);
                                        $this->body .= " - - <a href='index.php?mc=users&amp;page=users_subs_delete&amp;b=$user_id&amp;auto_save=1' class='lightUpLink'>Delete Subscription</a>";
                                    } else {
                                        $this->body .= "expired";
                                    }
                                    $this->body .= "<br><a href=index.php?mc=users&page=users_subs_change&b=" . $user_id . "&c=" . $show_subscription["subscription_id"] . ">Change Expiration</a>";
                                    $this->body .= "</td></tr>\n";
                                }
                            }
                        }
                        $this->body .= '<tr><td colspan="2">' . geoOrderItem::callDisplay('Admin_user_management_edit_user_form', $user_id) . '</td></tr>';
                        $this->body .= "</div></fieldset>";
                    } else {
                        $this->body .= "<input type='hidden' name='c[group]' value='1'>";
                    }
                }

                if (!$this->admin_demo()) {
                    $this->body .= "<div style='text-align: center;' class='medium_font'>
						<input type=submit name='auto_save' value=\"Save\">
						</div></form>";
                } else {
                    $this->body .= "</div>";
                }
                return true;
            } else {
                //no user exists
                return false;
            }
        }
    } //end of function edit_user_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function change_subscription_form($db, $user_id = 0, $subscription_id = 0)
    {
        if ($user_id) {
            if ($subscription_id) {
                $sql = "select * from " . $this->classified_user_subscriptions_table . " where user_id = " . $user_id;
                //echo $sql."<br>\n";
                $get_subscription_result = $this->db->Execute($sql);
                if ($this->debug_user) {
                    echo $sql_query . " is the query<br>\n";
                }
                if (!$get_subscription_result) {
                    return false;
                } elseif ($get_subscription_result->RecordCount() == 1) {
                    $show = $get_subscription_result->FetchRow();
                    $current_expiration = $show["subscription_expire"];
                    //echo $current_expiration."<br>\n";
                } else {
                    $current_expiration = geoUtil::time();
                }
            } else {
                $current_expiration = geoUtil::time();
            }
            $user = $this->get_user_data($user_id);

            if (!$this->admin_demo()) {
                $this->body .= "<form action=index.php?mc=users&page=users_subs_change&b=" . $user_id . "&c=" . $subscription_id . " method=post>\n";
            }
            $this->body .= "<table cellpadding=2 cellspacing=1 border=0 align=center width=100% class=row_color1>\n";
            //$this->title = "Edit This Users Subscription Expiration";
            $this->description = "Below is this users data current subscription expiration.
				Make any necessary changes you need and click the \"save changes\" button.";
            $this->body .= "<tr class=row_color1>\n\t<td colspan=2 class=group_price_hdr style='color: white; font-size: 10pt; font-weight: bold;'>\n\tSubscription Expiration for " . $user["firstname"] . " " . $user["lastname"] . " <BR>
				USERNAME: " . $user["username"] . "</font>\n\t</td>\n</tr></table>\n";
            $this->body .= "<div>&nbsp;<br></div>";
            $this->body .= "<fieldset id='ExpDetails'><legend>Subscription Expiration Details</legend><table cellpadding=2 cellspacing=1 border=0 align=center width=\"100%\">\n";
            $this->body .= "<tr class=row_color2>\n\t<td class=medium_font width=40% align=right><strong>Date To Expire:</strong></td>\n";
            $this->body .= "<td class=medium_font>";
            $now = geoUtil::time();
            $this->body .= $this->get_date_select("d[year]", "d[month]", "d[day]", date("Y", $current_expiration), date("n", $current_expiration), date("j", $current_expiration), date("Y", $now));
            $this->body .= "<br><strong>Note: </strong>New subscription expiration \"time\" will be set to the end of the day you specify above.</td>\n</tr>\n";
            if (!$this->admin_demo()) {
                $this->body .= "<tr>\n\t<td colspan=2 align=center><input type=submit name='auto_save' value=\"Save\"></td></tr>\n";
            }
            $this->body .= "<tr>\n\t<td colspan=2 class=medium_font align=center><br><a href=index.php?mc=users&page=users_view&b=" . $user_id . "><strong>Back to User Data</strong></a></td></tr>\n";
            $this->body .= "</table></fieldset>\n";
            return true;
        } else {
            return false;
        }
    } //end of function change_subscription_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_subscription($db, $user_id = 0, $subscription_id = 0, $subscription_info = 0)
    {

        if (($user_id) && ($subscription_id) && ($subscription_info)) {
            //update subscription
            $timestamp = mktime(23, 59, 59, $subscription_info["month"], $subscription_info["day"], $subscription_info["year"]);
             //set to end of day
            $sql_query = "update " . $this->classified_user_subscriptions_table . " set
				subscription_expire = \"" . $timestamp . "\"
				where user_id = " . $user_id . " and subscription_id = " . $subscription_id;
            $update_time_result = $this->db->Execute($sql_query);
            if ($this->debug_user) {
                echo $sql_query . " is the query<br>\n";
            }

            if (!$update_time_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } else {
                return true;
            }
        } elseif (($user_id) && ($subscription_info)) {
            //insert new subscription
            $sql_query = "delete from " . $this->classified_user_subscriptions_table . "
				where user_id = " . $user_id;
            $delete_subscription_result = $this->db->Execute($sql_query);
            if ($this->debug_user) {
                echo $sql_query . " is the query<br>\n";
            }

            if (!$delete_subscription_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            $timestamp = mktime(23, 59, 59, $subscription_info["month"], $subscription_info["day"], $subscription_info["year"]);

            $user = geoUser::getUser($user_id);
            $price_plan_id = ($user->price_plan_id) ? $user->price_plan_id : $user->auction_price_plan_id;

            $sql_query = "insert into " . $this->classified_user_subscriptions_table . "
				(price_plan_id, user_id,subscription_expire)
				values
				(" . $price_plan_id . "," . $user_id . "," . $timestamp . ")";
            $insert_expiration_result = $this->db->Execute($sql_query);
            if ($this->debug_user) {
                echo $sql_query . " is the query<br>\n";
            }

            if (!$insert_expiration_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_user_info($db, $user_id = 0, $user_info = 0)
    {
        //make sure authorization is loaded.
        if (!isset($this->product_configuration) || !is_object($this->product_configuration)) {
            $this->product_configuration = geoPC::getInstance();
        }

        if (!$user_id || !$user_info) {
            //not enough info to go on
            geoAdmin::m('Not enough info supplied to complete request.', geoAdmin::ERROR);
            return false;
        }

        $sql_query = "select * from " . $this->db->geoTables->logins_table . "
			where id = ?";
        $result = $this->db->Execute($sql_query, array($user_id));
        if ($this->debug_user) {
            echo $sql_query . " is the query<br>\n";
        }

        if (!$result) {
            if ($this->debug_user) {
                echo $this->db->ErrorMsg . "<br>";
                echo $sql_query . " is the query<br>\n";
            }
            $this->site_error($this->db->ErrorMsg());
            return false;
        }
        if ($result->RecordCount() != 1) {
            //user not found in DB?
            geoAdmin::m('Error applying changes: Could not find user in DB (or duplicate entries), number of results for user: ' . $result->RecordCount(), geoAdmin::ERROR);
            return false;
        }

        //trim all inputs
        foreach ($user_info as $key => $val) {
            $user_info[$key] = trim($val);
        }

        //find anon id
        $anon = geoAddon::getRegistry('anonymous_listing');
        if ($anon) {
            $anon_user = $anon->get('anon_user_id');
        }
        $isAnon = ($anon_user && $user_id == $anon_user) ? true : false;

        if ($user_id != 1 && !$isAnon) {
            // admin/anon cannot change user/pass here
            $show_username = $result->FetchRow();
            if ($user_info["username"] != $show_username["username"]) {
                if (strlen($user_info['password']) == 0) {
                    $this->user_management_error = 'Password cannot be blank to update username.  If current user\'s password seems to be hashed, the software cannot retrieve it, so you will need to supply a new password.';
                }
                //check if username already exists for another user
                $sql_query = "select * from " . $this->logins_table . "
					where username = \"" . $user_info["username"] . "\" and id != " . $user_id;
                $username_result = $this->db->Execute($sql_query);
                if ($this->debug_user) {
                    echo $sql_query . " is the query<br>\n";
                }

                if (!$username_result) {
                    if ($this->debug_user) {
                        echo $this->db->ErrorMsg . "<br>";
                        echo $sql_query . " is the query<br>\n";
                    }
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } elseif ($username_result->RecordCount() > 0) {
                    //this username already exists
                    $this->user_management_error .= "Cannot change username to requested username.
						That username already exists in the database.";
                } elseif (strlen(trim($user_info['username'])) < $this->db->get_site_setting('min_user_length') || strlen(trim($user_info['username'])) > $this->db->get_site_setting('max_user_length')) {
                    //username not proper length.
                    $this->user_management_error .= 'Invalid username length, username must follow length guidelines set in Admin Tools & Settings > Security Settings > General Security Settings.';
                }
            }
            $hash_type = (strlen($show_username['hash_type'])) ? $show_username['hash_type'] : $this->db->get_site_setting('client_pass_hash');
            $salt = '';
            $hash_pass = $this->product_configuration->get_hashed_password($user_info['username'], trim($user_info['password']), $hash_type, $show_username['salt']);
            if (is_array($hash_pass)) {
                $salt = $hash_pass['salt'];
                $hash_pass = $hash_pass['password'];
            }
            if ((strlen($this->user_management_error) == 0) && strlen(trim($user_info['password'])) == 0 && $user_info["username"] != $show_username["username"]) {
                //cannot change username w/o pass
                $this->user_management_error .= 'Password cannot be blank to update username.  If current user\'s password seems to be hashed, the software cannot retrieve it, so you will need to supply a new password.';
            } elseif ((strlen($this->user_management_error) == 0) && strlen($user_info['password']) && (($show_username['password'] != $hash_pass) && ((strlen($user_info["password"]) < $this->db->get_site_setting('min_pass_length')) || (strlen($user_info["password"]) > $this->db->get_site_setting('max_pass_length'))))) {
                //$this->user_management_error = "Password must be at least 6 characters but not more than 12";
                $this->user_management_error .= "Password character length must be between " . $this->db->get_site_setting('min_pass_length') . " and " . $this->db->get_site_setting('max_pass_length') . " (Set in Admin Tools & Settings > Security Settings > General Security Settings)";
            }
            if (strlen($this->user_management_error) == 0 && $show_username['password'] != $hash_pass && trim($user_info['password']) == trim($show_username['username'])) {
                $this->user_management_error .= "Username and password must be different.";
            }
            if (strlen($this->user_management_error) == 0 && strlen($user_info['password']) && ($show_username['password'] != $hash_pass || $user_info["username"] != $show_username["username"])) {
                //either username, or password has changed, and there are no errors to do with user & pass.  Need to update both at once.
                if ($user_info["password"] == $user_info["password_verifier"]) {
                    //before we were just "verifying" the password, this time we aim to
                    //generate a new password, so do not supply the salt.
                    $hash_type = $this->db->get_site_setting('client_pass_hash');
                    $hash_pass = $this->product_configuration->get_hashed_password($user_info['username'], trim($user_info['password']), $hash_type);
                    $salt = '';
                    if (is_array($hash_pass)) {
                        //salt provided with return
                        $salt = $hash_pass['salt'];
                        $hash_pass = $hash_pass['password'];
                    }
                    $sql_query = "update " . $this->db->geoTables->logins_table . " set
						username = ?,
						password = ?,
						hash_type = ?,
						salt = ?
						where id = ?";
                    $result = $this->db->Execute($sql_query, array($user_info['username'], $hash_pass, $hash_type, $salt, $user_id));
                    if ($this->debug_password) {
                        echo $sql_query . " is the query<br>\n";
                    }
                    if ($this->configuration_data["debug_admin"]) {
                        $this->debug_display($db, $this->filename, $this->function_name, "logins_table", "update logins data");
                    }
                    if (!$result) {
                        if ($this->debug_user) {
                            echo $this->db->ErrorMsg . "<br>";
                            echo $sql_query . " is the query<br>\n";
                        }
                        $this->site_error($this->db->ErrorMsg());
                        return false;
                    }

                    //update the userdata table username value also
                    $sql_query = "update " . $this->userdata_table . " set username = ? where id = ?";
                    $result = $this->db->Execute($sql_query, array($user_info['username'], $user_id));
                    if ($this->configuration_data["debug_admin"]) {
                        $this->debug_display($db, $this->filename, $this->function_name, "userdata_table", "update username data");
                    }
                    if (!$result) {
                        if ($this->debug_user) {
                            echo $this->db->ErrorMsg . "<br>";
                            echo $sql_query . " is the query<br>\n";
                        }
                        $this->site_error($this->db->ErrorMsg());
                        return false;
                    }
                } else {
                    $this->user_management_error = "Password and Password Verifier did not match";
                }
            }
        }

        if ($user_id != 1 && (!$user_info['email'] || !geoString::isEmail($user_info['email']))) {
            //need an email address to continue, but not if this is the admin!
            geoAdmin::m('A valid email address is required.', geoAdmin::ERROR);
            return false;
        }

        //update locations and override old fields as needed
        $geographicOverrides = geoRegion::getLevelsForOverrides();
        $locations = $_REQUEST['locations'];
        geoRegion::setUserRegions($user_id, $locations);
        $user_info['city'] = ($regionOverrides['city']) ? geoRegion::getNameForRegion($locations[$regionOverrides['city']]) : $user_info['city'];
        $user_info['state'] = ($regionOverrides['state']) ? geoRegion::getAbbreviationForRegion($locations[$regionOverrides['state']]) : $user_info['state'];
        $user_info['country'] = ($regionOverrides['country']) ? geoRegion::getNameForRegion($locations[$regionOverrides['country']]) : $user_info['country'];

        $sql_query = "update " . $this->userdata_table . " set
			firstname = \"" . addslashes($user_info["firstname"]) . "\",
			lastname = \"" . addslashes($user_info["lastname"]) . "\",
			company_name = \"" . addslashes($user_info["company_name"]) . "\",
			business_type = \"" . addslashes($user_info["business_type"]) . "\",
			url = \"" . addslashes($user_info["url"]) . "\",
			address = \"" . addslashes($user_info["address"]) . "\",
			address_2 = \"" . addslashes($user_info["address_2"]) . "\",
			city = \"" . addslashes($user_info["city"]) . "\",
			state = \"" . $user_info["state"] . "\",
			zip = \"" . addslashes($user_info["zip"]) . "\",
			country = \"" . $user_info["country"] . "\",
			" . (($user_id != 1) ? "email = \"" . addslashes($user_info["email"]) . "\"," : "") . "
			phone = \"" . addslashes($user_info["phone"]) . "\",
			phone2 = \"" . addslashes($user_info["phone2"]) . "\",
			fax = \"" . addslashes($user_info["fax"]) . "\",
			optional_field_1 = \"" . addslashes($user_info["optional_field_1"]) . "\",
			optional_field_2 = \"" . addslashes($user_info["optional_field_2"]) . "\",
			optional_field_3 = \"" . addslashes($user_info["optional_field_3"]) . "\",
			optional_field_4 = \"" . addslashes($user_info["optional_field_4"]) . "\",
			optional_field_5 = \"" . addslashes($user_info["optional_field_5"]) . "\",
			optional_field_6 = \"" . addslashes($user_info["optional_field_6"]) . "\",
			optional_field_7 = \"" . addslashes($user_info["optional_field_7"]) . "\",
			optional_field_8 = \"" . addslashes($user_info["optional_field_8"]) . "\",
			optional_field_9 = \"" . addslashes($user_info["optional_field_9"]) . "\",
			optional_field_10 = \"" . addslashes($user_info["optional_field_10"]) . "\",
			admin_note = \"" . geoString::toDB($user_info['admin_note']) . "\"
			where id = " . $user_id;

        $result = $this->db->Execute($sql_query);

        if ($user_info['apply_to_all_email']) {
            $class_sql_query = "UPDATE " . $this->classifieds_table . " SET email = \"" . $user_info['email'] . "\" WHERE seller = " . $user_id;
            $class_result = $this->db->Execute($class_sql_query);
            if (!$class_result) {
                return false;
            }
        }

        if (!$result) {
            if ($this->debug_user) {
                echo $this->db->ErrorMsg() . "<br>";
                echo $sql_query . " is the query<br>\n";
            }
            $this->site_error($this->db->ErrorMsg());
            return false;
        } else {
            if ($user_id != 1 && !$isAnon) {
                // admin user cannot suspend itself!
                $sql_query = "update " . $this->logins_table . " set
					status = " . (int)$user_info["status"] . "
					where id = " . $user_id;

                $result = $this->db->Execute($sql_query);
                if (!$result) {
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                }
                if ($user_info['status'] == 2) {
                    //kill any sessions
                    $this->db->Execute("UPDATE " . geoTables::session_table . " SET `user_id`=0 WHERE `user_id`=?", array ($user_id));
                }
            }
        }

        if ($user_id != 1) {
            // admin doesn't really have a group
            //get the current group
            $sql_query = "select * from " . $this->db->geoTables->user_groups_price_plans_table . "
				where id = " . $user_id;
            $group_result = $this->db->Execute($sql_query);
            if ($this->debug_user) {
                echo $sql_query . " is the query<br>\n";
            }

            if (!$group_result) {
                if ($this->debug_user) {
                    echo $this->db->ErrorMsg . "<br>";
                    echo $sql_query . " is the query<br>\n";
                }
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($group_result->RecordCount() == 1) {
                $show_group = $group_result->FetchRow();

                if ($this->debug_user) {
                    echo $user_info["group"] . " is user_info[group]<br>\n";
                    echo $show_group["group_id"] . " is show_group[group_id]<br>\n";
                }

                if ($user_info["group"] != $show_group["group_id"]) {
                    //get price plan attached to this group
                    $sql = "select * from " . $this->classified_groups_table . " where group_id = " . $user_info["group"];
                    $result = $this->db->Execute($sql);
                    if ($this->debug_user) {
                        echo $sql_query . " is the query<br>\n";
                    }
                    if (!$result) {
                        if ($this->debug_user) {
                            echo $this->db->ErrorMsg . "<br>";
                            echo $sql_query . " is the query<br>\n";
                        }
                        $this->error_message = $this->internal_error_message;
                        $this->site_error($this->db->ErrorMsg());
                    } elseif ($result->RecordCount() == 1) {
                        $show_group = $result->FetchRow();
                        if (geoMaster::is('classifieds')) {
                            $sql_query = "update " . $this->user_groups_price_plans_table . " set
								group_id = " . $user_info["group"] . ",
								price_plan_id = " . $show_group["price_plan_id"] . "
								where id = " . $user_id;
                            $update_group_result = $this->db->Execute($sql_query);
                            if ($this->debug_user) {
                                echo $sql_query . " is the query<br>\n";
                            }
                            if (!$update_group_result) {
                                if ($this->debug_user) {
                                    echo $this->db->ErrorMsg . "<br>";
                                    echo $sql_query . " is the query<br>\n";
                                }
                                $this->site_error($this->db->ErrorMsg());
                                return false;
                            }
                        }
                        if (geoMaster::is('auctions')) {
                            $sql_query = "update " . $this->user_groups_price_plans_table . " set
								group_id = " . $user_info["group"] . ",
								auction_price_plan_id = " . $show_group["auction_price_plan_id"] . "
								where id = " . $user_id;
                            $update_group_result = $this->db->Execute($sql_query);
                            if ($this->debug_user) {
                                echo $sql_query . " is the query<br>\n";
                            }
                            if (!$update_group_result) {
                                if ($this->debug_user) {
                                    echo $this->db->ErrorMsg . "<br>";
                                    echo $sql_query . " is the query<br>\n";
                                }
                                $this->site_error($this->db->ErrorMsg());
                                return false;
                            }
                        }

                        $sql_query = "delete from " . $this->classified_user_subscriptions_table . "
							where user_id = " . $user_id;
                        $delete_subscriptions_result = $this->db->Execute($sql_query);
                        if ($this->debug_user) {
                            echo $sql_query . " is the query<br>\n";
                        }
                        if (!$delete_subscriptions_result) {
                            if ($this->debug_user) {
                                echo $this->db->ErrorMsg . "<br>";
                                echo $sql_query . " is the query<br>\n";
                            }
                            $this->site_error($this->db->ErrorMsg());
                            return false;
                        }
                        if (geoPC::is_ent()) {
                            //remove all recurring billings for this user
                            $allRecurring = geoRecurringBilling::getAllForUser($user_id);
                            foreach ($allRecurring as $recurring) {
                                if ($recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
                                    //cancel each one that is not already canceled.
                                    //TODO: Text!?
                                    $result = $recurring->cancel('User group changed for user by admin.');
                                    if (!$result) {
                                        //don't stop, just let admin know it failed so they can take action if needed
                                        geoAdmin::m("Recurring billing with ID {$recurring->getId()} was not able to be canceled!  Be sure to cancel this recurring billing manually.", geoAdmin::NOTICE);
                                    }
                                }
                            }
                        }
                    } else {
                        return "no name";
                    }
                }
            }
        }
        geoOrderItem::callUpdate('Admin_user_management_update_user_info', $user_id);

        //Update the user info with the bridge.
        $addon = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $user_info["old_username"] = $show_username["username"];
        $user_info["old_password"] = $show_username["password"];
        geoAddon::triggerUpdate('user_edit', $user_info);
        if ($this->debug_user) {
            echo "returning true<br>\n";
        }
        return true;
    } //end of function update_user_info

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function lock_unlock_user($db, $user_id)
    {
    } //end of function update_user_info

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function remove_user_verify($user_id)
    {
        $user_id = intval($user_id);

        $anonR = geoAddon::getRegistry('anonymous_listing');
        if ($anonR) {
            $anonUser = $anonR->anon_user_id;
        }

        if ($user_id == 1 || $user_id == $anonUser) {
            geoAdmin::m('This User cannot be removed!', geoAdmin::ERROR);
            $this->body .= geoAdmin::m();
            return true;
        }
        $user = geoUser::getUser($user_id);
        if (!is_object($user)) {
            $this->list_users();
            return true;
        }

        $this->body .= "
		<div class=\"page-title1\">User: <span class='color-primary-two'>" . $user->username . "</span> <span class='color-primary-six' style='font-size: 0.8em;'>(User ID#: " . $user_id . ")</span></div>

		<fieldset>
		<legend>Verify User Removal</legend>
		<div class='center'>
			<strong>Warning:</strong> Removing a user will remove everything associated with that user, including all listings, orders, invoices, etc.<br />
			<a href='index.php?mc=users&page=users_view&b={$user_id}' class='button' onclick='window.open(this.href); return false;'>View User Data</a><br /><br />
			<span style='font-size: 1.2em; color: #FF0000; font-weight: bold; text-transform: uppercase;'>This action cannot be undone after clicking the button below!</span><br /><br />

			<form action='index.php?mc=users&amp;page=users_remove&amp;b=$user_id' method='post'>
				 <input type='submit' name='auto_save' value=\"Remove User\" class='mini_cancel' />
			</form>
		</div>
		</fieldset>";
        //echo "</table>\n";


        return true;
    } //end of function remove_user_verify

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function remove_user($user_id, $silentSuccess = false)
    {
        $user_id = intval($user_id);
        $admin = geoAdmin::getInstance();
        if ($user_id == 1 || !$user_id) {
            return true;
        }

        //must remove from userdata, login, as well as all listings data

        //delete userdata history
        $sql = "DELETE FROM " . geoTables::userdata_history_table . " WHERE `id` = $user_id";

        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        //communications message_to
        $sql = "DELETE FROM " . geoTables::user_communications_table . " WHERE `message_to` = $user_id";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        if (!$this->remove_ad_filters($this->db, $user_id)) {
            //die($this->db->ErrorMsg());
            return false;
        }

        //delete expired
        $sql = "DELETE FROM " . geoTables::classifieds_expired_table . " WHERE `seller` = $user_id";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        //get all orders user has placed
        $sql = "SELECT `id` FROM " . geoTables::order . " WHERE `buyer` = $user_id";
        $rows = $this->db->GetAll($sql);

        foreach ($rows as $row) {
            //this will remove all orders and attached order items, registry, etc. for this user.
            geoOrder::remove($row['id']);
        }

        //get current listings
        $sql = "SELECT * FROM " . geoTables::classifieds_table . " WHERE `seller` = $user_id";
        //echo $sql." is the query<br>\n";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        if ($result->RecordCount() > 0) {
            $cats = array();
            while ($show = $result->FetchRow()) {
                if (!geoListing::remove($show['id'])) {
                    $admin->userError('DB Error when trying to delete user info, when removing user listings.');
                    return false;
                }
                $cats[$show['category']] = $show['category'];
            }
            foreach ($cats as $cat_id) {
                //update category count after all listings are processed, to keep from updating the same categories multiple times.
                geoCategory::updateListingCount($cat_id);
            }
        }

        //delete from subscriptions expiration
        $sql = "DELETE FROM " . geoTables::user_subscriptions_table . " WHERE `user_id` = $user_id";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        if (geoPC::is_ent()) {
            //remove canceled recurring billings
            $allRecurrings = geoRecurringBilling::getAllForUser($user_id, false);
            foreach ($allRecurrings as $recurringId) {
                geoRecurringBilling::remove($recurringId);
            }
        }

        //Delete user sessions
        $sql = "DELETE FROM " . geoTables::session_table . " WHERE `user_id` = $user_id";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        if (geoMaster::is('auctions')) {
            //delete user's bids
            $sql = "DELETE FROM " . geoTables::bid_table . " WHERE `bidder` = $user_id";
            $result = $this->db->Execute($sql);
            //echo $sql." is the query<br>\n";
            if (!$result) {
                $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }

            //delete user's autobids
            $sql = "DELETE FROM " . geoTables::autobid_table . " WHERE `bidder` = $user_id";
            $result = $this->db->Execute($sql);
            //echo $sql." is the query<br>\n";
            if (!$result) {
                $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }

            //delete user's feedbacks
            $sql = "DELETE FROM " . geoTables::auctions_feedbacks_table . " WHERE `rated_user_id` = $user_id OR `rater_user_id` = $user_id";
            $result = $this->db->Execute($sql);
            //echo $sql." is the query<br>\n";
            if (!$result) {
                $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
                return false;
            }
        }

        $result = $this->db->Execute("DELETE FROM " . geoTables::user_regions . " WHERE `user` = ?", array($user_id));
        if (!$result) {
            $admin->message('DB Error deleting user regions.');
            return false;
        }

        //give addons a chance to delete stuff, done right before "critical" user data removed
        geoAddon::triggerUpdate('notify_user_remove', $user_id);

        //delete group information
        $sql = "DELETE FROM " . geoTables::user_groups_price_plans_table . " WHERE `id` = $user_id";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        //delete login information - do this and userdata last, in case there is so much
        //that not everything is deleted in one go, they can go through the delete process
        //until everything is able to be removed.
        $sql = "DELETE FROM " . geoTables::logins_table . " WHERE `id` = $user_id";
        //echo $sql." is the query<br>\n";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        //delete userdata
        $sql = "DELETE FROM " . geoTables::userdata_table . " WHERE `id` = $user_id";
        //echo $sql." is the query<br>\n";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $admin->message('DB Error when trying to delete user info.  Debug: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }

        if (!$silentSuccess) {
            $admin->message('User Removed.');
        }
        return true;
    } //end of function remove_user

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function remove_ad_filters($db, $user_id)
    {
        if ($user_id) {
            $sql = "select * from " . $this->classified_ad_filter_table . " where user_id = " . $user_id;
            $filter_result = $this->db->Execute($sql);
            //echo $sql."<br>\n";
            if (!$filter_result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($filter_result->RecordCount() > 0) {
                while ($show = $filter_result->FetchRow()) {
                    $sql = "delete from " . $this->classified_ad_filter_categories_table . " where filter_id = " . $show["filter_id"];
                    $categories_filters_result = $this->db->Execute($sql);
                    if (!$categories_filters_result) {
                        $this->error_message = $this->internal_error_message;
                        return false;
                    }
                }
            }

            $sql = "delete from " . $this->classified_ad_filter_table . " where user_id = " . $user_id;
            $result = $this->db->Execute($sql);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            }
            return true;
        } else {
            return false;
        }
    } //end of function remove_ad_filters

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function user_search_box()
    {
        $this->body .= "<form action=index.php?mc=users&page=users_search method=post>\n";
        $this->body .= "<table cellpadding=2 cellspacing=1 border=0 align=center>\n";
        $this->body .= "<tr>\n\t<td colspan=2>\n\t" . $this->medium_font . "Search for user by</font>\n\t</td>\n</tr>\n";
        $this->body .= "<tr>\n\t<td>\n\t" . $this->medium_font . "by username<input type=radio name=b[username_by] value=1></font><br>\n\t
			" . $this->medium_font . "by first or last name<input type=radio name=b[search_user_by] value=2></font><br>\n\t</td>\n\t";
        $this->body .= "<td>" . $this->medium_font . "<input type=text size=30 maxsize=30 name=b[search_by_text]></font>\n\t</td>\n</tr>\n";
        $this->body .= "</table>\n";
        $this->body .= "</form>\n";
    } //end of function user_search_box

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function search_users($db, $search_info = 0)
    {
        $db = DataAccess::getInstance();
        if ($search_info) {
            $sql = "";
            switch ($search_info["search_type"]) {
                case 1:
                    //search by text
                    $sql = "select * from " . $this->userdata_table . ", " . $this->logins_table . "," . $this->user_groups_price_plans_table . "
						where " . $this->userdata_table . ".id = " . $this->logins_table . ".id and " . $this->user_groups_price_plans_table . ".id = " . $this->logins_table . ".id and ";
                    switch ($search_info["field_type"]) {
                        case 1:
                            $sql .= $this->logins_table . ".username ";
                            break;
                        case 2:
                            $sql .= $this->userdata_table . ".lastname ";
                            break;
                        case 3:
                            $sql .= $this->userdata_table . ".firstname ";
                            break;
                        case 4:
                            $sql .= $this->userdata_table . ".email ";
                            break;
                        case 5:
                            $sql .= $this->userdata_table . ".company_name ";
                            break;
                        case 6:
                            $sql .= $this->userdata_table . ".url ";
                            break;
                        case 7:
                            $sql .= $this->userdata_table . ".city ";
                            break;
                        case 8:
                            $sql .= $this->userdata_table . ".phone ";
                            break;
                        default:
                            //no search field specified, so search all of them
                            $all = ' (' . $this->logins_table . ".username LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".lastname LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".firstname LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".email LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".company_name LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".url LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".city LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . " OR " .
                                    $this->userdata_table . ".phone LIKE " . $db->qstr('%' . $search_info["text_to_search"] . '%') . ") ";
                    } //end of switch
                    $sql .= ($all) ? $all : " like \"%" . $db->qstr($search_info["text_to_search"]) . "%\" ";
                    break;

                case 2:
                    //display suspended users
                    $sql = "select * from " . $this->userdata_table . ", " . $this->logins_table . "," . $this->user_groups_price_plans_table . "
						where " . $this->userdata_table . ".id = " . $this->logins_table . ".id and " . $this->user_groups_price_plans_table . ".id = " . $this->logins_table . ".id and status=2 ";

                    break;

                case 3:
                    //joined before or after a date
                    //11/07/2016: this is functionally identical to case 4, and has been merged into it
                    break;

                case 4:
                    //joined between dates
                    //check if first date is less than second date
                    $begin_date = $begin_date ? strtotime($search_info['join_begin_date']) : 0;
                    $end_date = $end_date ? strtotime($search_info['join_end_date']) + 86399 : 0;  //+86399 because we want the END of the day

                    $sql = "select * from " . $this->userdata_table . ", " . $this->logins_table . "," . $this->user_groups_price_plans_table . "
							where " . $this->userdata_table . ".id = " . $this->logins_table . ".id and " . $this->user_groups_price_plans_table . ".id = " . $this->logins_table . ".id ";

                    if ($begin_date) {
                        $sql .= " AND `date_joined` >= " . $db->qstr($begin_date) . ' ';
                    }
                    if ($end_date) {
                        $sql .= " AND `date_joined` <= " . $db->qstr($end_date) . ' ';
                    }

                    break;

                case 5:
                    //users that have at least one listing expiring in the date range
                    //check if first date is less than second date
                    $begin_date = $begin_date ? strtotime($search_info['expire_begin_date']) : 0;
                    $end_date = $end_date ? strtotime($search_info['expire_end_date']) + 86399 : 0; //+86399 because we want the END of the day

                    //get seller of selected listings
                    $listingSql = "SELECT `seller` FROM " . geoTables::classifieds_table . " WHERE 1=1 "; //1=1 here is just an easy hack to allow using AND in the bits below
                    if ($begin_date) {
                        $listingSql .= " AND `ends` >= " . $db->qstr($begin_date) . ' ';
                    }
                    if ($end_date) {
                        $listingSql .= " AND `ends` <= " . $db->qstr($end_date) . ' ';
                    }

                    $listingResult = $db->Execute($listingSql);
                    $sellers = array();
                    while ($s = $listingResult->FetchRow()) {
                        $sellers[] = $s['seller'];
                    }
                    if (count($sellers) > 0) {
                        $sellers_in = implode(',', $sellers);
                        //do the search
                        $sql = "select * from " . $this->userdata_table . " as ud, " . $this->logins_table . " as l," . $this->user_groups_price_plans_table . " as pp
							where ud.id = l.id and pp.id = l.id and ud.id IN (" . $sellers_in . ")";
                    } else {
                        //no results to show -- bogus query so it uses the common error thingy
                        $sql = "select * from " . geoTables::userdata_table . " where false";
                    }
                    break;

                case 'id_in':
                    $list_raw = explode(',', $search_info['id_in']);

                    $list = array();
                    foreach ($list_raw as $id) {
                        $id = (int)trim($id);
                        $list[$id] = $id;
                    }

                    $sql = "SELECT * FROM " . geoTables::userdata_table . " as ud, " . geoTables::logins_table . " as l, " . geoTables::user_groups_price_plans_table . " as pp
						WHERE ud.id=l.id AND pp.id=l.id AND ud.id IN (" . implode(', ', $list) . ")";

                    break;

                case 'id_not_in':
                    $list_raw = explode(',', $search_info['id_not_in']);
                    //never include admin user...
                    $list_raw[] = 1;

                    $list = array();
                    foreach ($list_raw as $id) {
                        $id = (int)trim($id);
                        $list[$id] = $id;
                    }
                    $group = '';
                    if (isset($search_info['group_in'])) {
                        $groups_raw = explode(',', $search_info['group_in']);
                        $groups = array();
                        foreach ($groups_raw as $group) {
                            $group = (int)trim($group);
                            $groups[$group] = $group;
                        }
                        $group = " AND pp.group_id IN (" . implode(',', $groups) . ")";
                    }

                    $sql = "SELECT * FROM " . geoTables::userdata_table . " as ud, " . geoTables::logins_table . " as l, " . geoTables::user_groups_price_plans_table . " as pp
						WHERE ud.id=l.id AND pp.id=l.id AND ud.id NOT IN (" . implode(', ', $list) . ") $group";

                    break;
            } //end of switch

            if ($search_info["search_group"]) {
                $sql .= " and group_id = " . $db->qstr($search_info["search_group"]);
            }

            switch ($search_info["sort_type"]) {
                case 1:
                    $order = geoTables::userdata_table . ".username,lastname,firstname ";
                    break;
                case 2:
                    $order = " lastname,firstname ";
                    break;
                case 3:
                    $order = " firstname,lastname ";
                    break;
                case 4:
                    $order = " email ";
                    break;
                case 5:
                    $order = " company_name,lastname,firstname ";
                    break;
                case 6:
                    $order = " url,lastname,firstname ";
                    break;
                default:
                    $order = false;
            }
            if ($order) {
                $sql .= ' ORDER BY ' . $order . ' ';
            }

            if (strlen(trim($sql)) == 0) {
                //no query to search with
                return false;
            } else {
                $this->display_search_results($db, urlencode($sql));
                return true;
            }
        } else {
            //no search info to search by
            return false;
        }
    } //end of function user_search_box

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_search_results($db, $sql_query = 0)
    {
        if ($sql_query) {
            $sql_query = urldecode($sql_query);
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() > 0) {
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=admin_messaging&page=admin_messaging_send method=post>\n";
                }

                $this->body .= "<fieldset id='SearchResults'>
								<legend>Search Results</legend>
							<div class='table-responsive'>
							<table cellpadding=2 cellspacing=1 border=0 align=center class=\"table table-hover table-striped table-bordered\" width=\"100%\">";

                $this->body .= "<thead>
								<tr class=\"col_hdr_top\">
									<td class=\"col_hdr\" style='text-align: left;'>Username</td>
									<td class=\"col_hdr\" style='text-align: left;'>Last</td>
									<td class=\"col_hdr\" style='text-align: left;'>First</td>

									<td class=\"col_hdr\">Status</td>";

                if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
                    if (geoMaster::is('classifieds')) {
                        $this->body .= "
									<td class=\"col_hdr\">Classifieds Price Plan</td>";
                    }

                    if (geoMaster::is('auctions')) {
                        $this->body .= "
									<td class=\"col_hdr\">Auctions Price Plan</td>";
                    }
                }


                $this->body .= "
									<td class=\"col_hdr\"></td>
								</tr></thead>";

                $verify_button = '';
                if ($this->db->get_site_setting('verify_accounts')) {
                    $txt = $this->db->get_text(true, 59);
                    $verify_icon = " <img src=\"../" . geoTemplate::getUrl('', $txt[500952]) . "\" alt='' />";
                }

                $this->row_count = 0;
                while ($show = $result->FetchRow()) {
                    if ($show["level"] != 1) {
                        $show['verify_icon'] = '';
                        if ($verify_icon && $show['verified'] == 'yes') {
                            $show['verify_icon'] = $verify_icon;
                        }
                        $this->show_user_line($db, $show);
                        $this->row_count++;
                    }
                }
                $this->body .= "</table>";

                if (!$this->admin_demo()) {
                    $this->body .= "
						<table>
							<tr>
								<td colspan=100% class=medium_font align=center>
									<input type=submit value=\"Send a Message to These Users\">
								</td>
							</tr>";
                }
                $this->body .= "</table></fieldset>\n</form>\n";
                return true;
            } else {
                //no users in the database

                $this->body .= "<div class='x-content search-users-input'>
									<form action='index.php?mc=users&page=users_search' class='form-horizontal form-label-left' method='post'>
									  <div class='form-group'>
										<div class='input-group'>
										  <input type='text' name='b[text_to_search]' class='form-control selected-border' placeholder='Search Users' style='border-right:0;'/>
										  <input type='hidden' name='b[search_group]' value='0' />
										  <input type='hidden' name='b[search_type]' value='1' />
										  <span class='input-group-btn'><input type='submit' value='Go' class='btn btn-primary' /></span>
										</div>
									  </div>
									</form>
								</div>";

                $this->body .= "<fieldset id='SearchResults'><legend>Search Results</legend><table cellpadding=2 cellspacing=1 border=0 align=center class=row_color1 width=\"100%\">\n";
                $this->body .= "<tr>\n\t<td colspan=6 class=medium_font align=center><b><br>No users matched your search. Please redefine your search.<br><br></b></td>\n\t</tr>\n\t";
                $this->body .= "</table></div></fieldset>\n";
            }
        } else {
            //no query to search with
            return false;
        }
    }//end of display_search_results

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function advanced_user_search()
    {
        $db = DataAccess::getInstance();
        $groups = $db->Execute("SELECT * FROM " . geoTables::classified_groups_table);
        foreach ($groups as $g) {
            $tpl_vars['groups'][] = array('id' => $g['group_id'], 'name' => $g['name']);
        }
        geoView::getInstance()->setBodyTpl('search_users.tpl')->setBodyVar($tpl_vars);
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_range_of_users($db, $sql_query = 0, $limit_by)
    {
        if ($sql_query) {
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() > 0) {
                $total_count = ($result->RecordCount() - 1);
                //echo $total_count." is the total count<bR>\n";
                $counter = 1;
                $number_of_times = 0;
                if ($total_count > 25) {
                    $this->body .= "<table cellpadding=2 cellspacing=1 border=0 align=center width=\"100%\">\n";
                    $this->body .= "<tr>\n\t<td>\n\t";
                    while ($number_of_times < 6) {
                        $this->body .= "<a href=index.php?mc=users&page=users_list&b[limit]=" . $counter . "&b[order_by]=" . $limit_by . "><span class=medium_font>" . $counter . "-" . ($counter + 25) . "</span></a> | ";

                        $counter = $counter + 25;
                        $number_of_times++;
                    }
                    if ($number_of_times == 6) {
                        $this->body .= "<a href=index.php?mc=users&page=users_list&b[limit]=" . ($total_count - 25) . "&b[order_by]=" . $limit_by;
                    }
                    $this->body .= "</td>\n</tr>\n</table>\n";
                }
            }
            return true;
        }
    } //end of function show_range_of_users

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function group_dropdown($db)
    {

        if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
            $this->function_name = "group_dropdown";
            $body = "";
            $sql_query = "select * from " . $this->classified_groups_table;
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() > 1) {
                $body .= "<span class=medium_font><b>by User Group:</b>&nbsp&nbsp
				<select onChange=\"document.getElementById('sortForm').submit();\" name=b[search_group]>\n\t\t";
                $body .= "<option value=0>all groups</option></span>\n\t\t";
                while ($show = $result->FetchRow()) {
                    $body .= "<option value=" . $show["group_id"];
                    if ($this->search_group == $show["group_id"]) {
                        $body .= " selected";
                    }
                    $body .= ">" . $show["name"] . "</option>\n\t\t";
                }
                $body .= "</select>\n\t";
            } elseif ($result->RecordCount() == 1) {
                $body .= "<input type=hidden name=b[search_group] value=0>\n\t";
            }

            return  $body;
        } else {
            return "<input type=hidden name=b[search_group] value='1'>\n\t";
        }
    } //end of function group_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_new_user_form($db)
    {
        $sql = "SELECT * FROM " . $this->site_configuration_table;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } else {
            $this->configuration_data = $result->FetchRow();
        }

        $sql = "SELECT * FROM " . $this->registration_configuration_table;

        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } else {
            $this->registration_configuration = $result->FetchRow();
        }


        //$this->title .= "Users > User Groups > Add New User";
        $this->description .= "Add a new user manually through the form below.";
        if (!$this->admin_demo()) {
            $this->body .= "<form action='index.php?mc=users&page=users_add&z=1' method='post' class='form-horizontal form-label-left'>";
        } else {
            $this->body .= "<div class='form-horizontal'>";
        }
        $this->body .= "<fieldset id='NewUserDetails'><legend>New User Details</legend><div class='x_content'>";


            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_firstname_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>First Name:</label>";
            if ($this->registration_configuration["require_registration_firstname_field"]) {
                $this->body .= "*";
            }
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[firstname] value=\"" . $this->classified_variables["firstname"] . "\" class='form-control col-md-7 col-xs-12'>";
                $this->body .= "</div>";
            if (isset($this->error[firstname])) {
                $this->body .= "<font color=#880000 size=1 face=arial>first name required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_lastname_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Last Name:</label>";
            if ($this->registration_configuration["require_registration_lastname_field"]) {
                $this->body .= "*";
            }
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[lastname] value=\"" . $this->classified_variables["lastname"] . "\" class='form-control col-md-7 col-xs-12'>";
                $this->body .= "</div>";
            if (isset($this->error[lastname])) {
                $this->body .= "<font color=#880000 size=1 face=arial face=arial>last name required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_company_name_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Company Name:</label>";
            if ($this->registration_configuration["require_registration_company_name_field"]) {
                $this->body .= "*";
            }
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[company_name] value=\"" . $this->classified_variables["company_name"] . "\" class='form-control col-md-7 col-xs-12'>";
                $this->body .= "</div>";
            if (isset($this->error[company_name])) {
                $this->body .= "<font color=#880000 size=1 face=arial>company error</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_business_type_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Business Type:</label>";
            if ($this->registration_configuration["require_registration_business_type_field"]) {
                $this->body .= "*";
            }
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=radio name=c[business_type] class='flat' value=1";
            if ($this->registered_variables[business_type] == 1) {
                $this->body .= " checked";
            }
                $this->body .= "> Individual<br>
					<input type=radio name=c[business_type] value=2 ";
            if ($this->registered_variables[business_type] == 2) {
                $this->body .= " checked";
            }
                $this->body .= "> Business";
                $this->body .= "</div>";
            if (isset($this->error[business_type])) {
                $this->body .= "<font class=error_message>please choose a business type</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_address_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Address:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[address] value=\"";
                $this->body .= $this->classified_variables["address"];
                $this->body .= "\" class='form-control col-md-7 col-xs-12'>";
            if ($this->registration_configuration["require_registration_address_field"]) {
                $this->body .= "*";
            }
                $this->body .= "</div>";

            if (isset($this->error[address])) {
                $this->body .= "<font color=#880000 size=1 face=arial>address required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_address2_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Address 2:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[address_2] value=\"";
                $this->body .= $this->classified_variables["address_2"];
                $this->body .= "\" class='form-control col-md-7 col-xs-12'>";
            if ($this->registration_configuration["require_registration_address2_field"]) {
                $this->body .= "*";
            }
                $this->body .= "</div>";

            if (isset($this->error[address_2])) {
                $this->body .= "<font color=#880000 size=1 face=arial>address 2 required</font>";
            }
            $this->body .= "</div>";
        }

        $regionOverrides = geoRegion::getLevelsForOverrides();
        $regionSelector = geoRegion::regionSelector('locations');
        $this->body .= "<div class='form-group'>";
        $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Location:</label>";
        $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";
        $this->body .= $regionSelector . '</div></div>';


            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_city_field"] && !$regionOverrides['city']) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>City:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[city] value=\"" . $this->classified_variables["city"] . "\" class='form-control col-md-7 col-xs-12'> ";
            if ($this->registration_configuration["require_registration_city_field"]) {
                $this->body .= "*";
            }
                    $this->body .= "</div>";
            if (isset($this->error[city])) {
                $this->body .= "<font color=#880000 size=1 face=arial>city required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_zip_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Zip Code:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[zip] value=\"" . $this->classified_variables["zip"] . "\" class='form-control col-md-7 col-xs-12'> ";
            if ($this->registration_configuration["require_registration_zip_field"]) {
                $this->body .= "*";
            }
                    $this->body .= "</div>";
            if (isset($this->error[zip])) {
                $this->body .= "<font color=#880000 size=1 face=arial>zip required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_phone_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Phone 1:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[phone] value=\"" . $this->classified_variables["phone"] . "\" class='form-control col-md-7 col-xs-12'> ";
            if ($this->registration_configuration["require_registration_phone_field"]) {
                $this->body .= "*";
            }
                    $this->body .= "</div>";
            if (isset($this->error[phone])) {
                $this->body .= "<font color=#880000 size=1 face=arial>first contact number required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_phone2_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Phone 2:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[phone_2] value=\"" . $this->classified_variables["phone_2"] . "\" class='form-control col-md-7 col-xs-12'> ";
            if ($this->registration_configuration["require_registration_phone2_field"]) {
                $this->body .= "*";
            }
                    $this->body .= "</div>";
            if (isset($this->error[phone_2])) {
                $this->body .= "<font color=#880000 size=1 face=arial>second contact number required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_fax_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Fax:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[fax] value=\"" . $this->classified_variables["fax"] . "\" class='form-control col-md-7 col-xs-12'> ";
            if ($this->registration_configuration["require_registration_fax_field"]) {
                $this->body .= "*";
            }
                    $this->body .= "</div>";

            if (isset($this->error[fax])) {
                $this->body .= "<font color=#880000 size=1 face=arial>fax required</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Email Address:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[email] value=\"" . $this->classified_variables["email"] . "\" class='form-control col-md-7 col-xs-12'> ";
                    $this->body .= "</div>";

        if (isset($this->error[email])) {
            $this->body .= "<font color=#880000 size=1 face=arial>" . $this->error[email] . "</font>";
        }
            $this->body .= "</div>";

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_email2_field"]) {
            $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Email Address 2:</label>";
            $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[email2] value=\"" . $this->classified_variables["email2"] . "\" class='form-control col-md-7 col-xs-12'> ";
                $this->body .= "</div>";

            if (isset($this->error[email])) {
                $this->body .= "<font color=#880000 size=1 face=arial>" . $this->error[email2] . "</font>";
            }
            $this->body .= "</div>";
        }

            $this->body .= "<div class='form-group'>";
        if ($this->registration_configuration["use_registration_url_field"]) {
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>URL:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[url] value=\"" . $this->classified_variables["url"] . "\" class='form-control col-md-7 col-xs-12'> ";
            if ($this->registration_configuration["require_registration_url_field"]) {
                $this->body .= "*";
            }
                $this->body .= "</div>";
            if (isset($this->error[url])) {
                $this->body .= "<font color=#880000 size=1 face=arial><span class=medium_font>url required</font>";
            }
            $this->body .= "</div>";
        }

        for ($i = 1; $i <= 10; $i++) {
            if ($this->registration_configuration["use_registration_optional_{$i}_field"]) {
                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>" . $this->registration_configuration["registration_optional_{$i}_field_name"] . ":</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";

                $sql = "select * from " . $this->registration_choices_table . " where type_id = " . $this->registration_configuration["registration_optional_{$i}_field_type"] . " order by display_order, value";
                $type_result = $this->db->Execute($sql);
                if (!$type_result) {
                    return false;
                } elseif ($type_result->RecordCount() > 0) {
                    $this->body .= "<select name='c[optional_field_{$i}]' class='form-control col-md-7 col-xs-12'>";
                    for ($d = 0; $show_dropdown = $type_result->FetchRow(); $d++) {
                        $this->body .= "<option>" . $show_dropdown['value'] . "</option>";
                    }
                    $this->body .= "</select>";
                } else {
                    //no option data from query -- make this a text input
                    $this->body .= "<input type='text' name='c[optional_field_{$i}]' value='" . $this->classified_variables["optional_field_{$i}"] . "' class='form-control col-md-7 col-xs-12'> ";
                }
                if ($this->registration_configuration["require_registration_optional_{$i}_field"]) {
                    $this->body .= '*';
                }
                if (isset($this->error["optional_field_{$i}"])) {
                    $this->body .= "<font color=#880000 size=1 face=arial>optional field {$i} required</font>";
                }
                $this->body .= "</div>";
                $this->body .= "</div>";
            }
        }

            $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Username:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=text name=c[username] class='form-control col-md-7 col-xs-12' style='width:auto;' size=15 maxsize=15 value=\"" . $this->classified_variables["username"] . "\"";
        if (isset($this->error[username])) {
            $this->body .= "<font color=#880000 size=1 face=arial>" . urldecode($this->error[username]) . "</font>";
        } else {
            $this->body .= "<font color=#000000 size=1 face=arial></font>";
        }
                $this->body .= "</div>";
            $this->body .= "</div>";

            $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Password:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=password name=c[password] class='form-control col-md-7 col-xs-12' style='width:auto;' size=15 maxsize=15> ";
        if (isset($this->error[password])) {
            $this->body .= "<font color=#880000 size=1 face=arial>" . urldecode($this->error[password]) . "</font>";
        }
                $this->body .= "</div>";
            $this->body .= "</div>";

            $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Password Verifier:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
					<input type=password name=c[password_confirm] class='form-control col-md-7 col-xs-12' style='width:auto;' size=15 maxsize=15> ";
        if ($this->error[repeat_password]) {
            $this->body .= "<font color=#880000 size=1 face=arial>your password verifier did not match the password field</font>";
        }
                $this->body .= "</div>";
            $this->body .= "</div>";

        if (geoAddon::getInstance()->isEnabled('enterprise_pricing')) {
            $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>User Group:</label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>";
                    $sql = "select name,group_id from " . geoTables::groups_table . " order by name";
                    $group_result = $this->db->Execute($sql);
            if (!$group_result) {
                $this->error_message = $this->internal_error_message;
                return false;
            }

                    $this->body .= "<select class='form-control' name=c[group_id]>";
            while ($show = $group_result->FetchRow()) {
                $this->body .= "<option value=" . $show["group_id"] . ">" . $show["name"] . "</option>";
            }
                    $this->body .= "</select>";

                    $this->body .= "</div>";
                    $this->body .= "</div>";
        } else {
            $this->body .= "<input type='hidden' name='c[group_id]' value='1'>";
        }

            $this->body .= "<div class='center'>";
        if (!$this->admin_demo()) {
            $this->body .= "<input type=submit name='auto_save' value=\"Save\">";
        }
            $this->body .= "</div>";

        $this->body .= "</div>";

        $this->body .= "</fieldset>";
        $this->body .= ($this->admin_demo()) ? '</div>' : "</form>";
        return true;
    } //end of function insert_new_user_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function check_info($db)
    {
        $sql = "SELECT * FROM " . $this->site_configuration_table;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } else {
            $this->configuration_data = $result->FetchRow();
        }

        $sql = "SELECT * FROM " . $this->registration_configuration_table;

        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } else {
            $this->registration_configuration = $result->FetchRow();
        }

        //$this->error = array();
        $this->error_found = 0;
        if ($this->classified_user_id == 0) {
            //echo "checking user info<br>\n";
            if ($this->registration_configuration["use_company_name_field"]) {
                if ($this->configuration_data["require_company_name_field"]) {
                    if (strlen(trim($this->classified_variables[company_name])) == 0) {
                        $this->error[company_name] = "missing company name";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_firstname_field"] && $this->registration_configuration["require_registration_firstname_field"]) {
                if (strlen(trim($this->classified_variables[firstname])) == 0) {
                    $this->error[firstname] = "please fill in the firstname";
                    $this->error_found++;
                }
            }

            if ($this->registration_configuration["use_registration_lastname_field"] && $this->registration_configuration["require_registration_lastname_field"]) {
                if (strlen(trim($this->classified_variables[lastname])) == 0) {
                    $this->error[lastname] = "please fill in the lastname";
                    $this->error_found++;
                }
            }

            if ($this->registration_configuration["use_registration_address_field"] && $this->registration_configuration["require_registration_address_field"]) {
                if (strlen(trim($this->classified_variables[address])) == 0) {
                    $this->error[address] = "please fill in the address";
                    $this->error_found++;
                }
            }
            if (($this->registration_configuration['use_registration_address2_field']) && ($this->registration_configuration['require_registration_address2_field'])) {
                if (strlen(trim($this->classified_variables[address_2])) == 0) {
                    $this->error[address_2] = "please fill in the address 2";
                    $this->error_found++;
                }
            }

            if (strlen(trim($this->classified_variables['email'])) > 0) {
                if (geoString::isEmail($this->classified_variables['email'])) {
                    $sql = "select id from " . $this->userdata_table . " where email = ?";
                    $result = $this->db->Execute($sql, array($this->classified_variables['email']));
                    if (!$result) {
                        //echo $sql." is the id check query<br>\n";
                        $this->error["registration"] = urldecode($this->messages[230]);
                        return false;
                    } elseif ($result->RecordCount() > 0) {
                        //email already in use
                        $this->error[email] = "email address already exists";
                        $this->error_found++;
                    }
                } else {
                    $this->error[email] = "please re-enter the email address";
                    $this->error_found++;
                }
            } else {
                $this->error[email] = "please enter an email address";
                $this->error_found++;
            }
            //$this->error[email] = "does not check now - remove before release";

            if ($this->registration_configuration["require_city_field"]) {
                if (strlen(trim($this->classified_variables[city])) == 0) {
                    $this->error[city] = "please fill in the city";
                    $this->error_found++;
                }
            }

            if ($this->registration_configuration["use_zip_field"]) {
                if ($this->configuration_data["require_zip_field"]) {
                    if (strlen(trim($this->classified_variables[zip])) == 0) {
                        $this->error[zip] = "please fill in the zip";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_phone_field"]) {
                if ($this->configuration_data["require_phone_field"]) {
                    if (strlen(trim($this->classified_variables[phone])) == 0) {
                        $this->error[phone] = "please fill in the first contact field";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_phone2_field"]) {
                if ($this->configuration_data["require_phone2_field"]) {
                    if (strlen(trim($this->classified_variables[phone_2])) == 0) {
                        $this->error[phone_2] = "please fill in the second contact field";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["require_fax_field"]) {
                if (strlen(trim($this->classified_variables[fax])) == 0) {
                    $this->error[fax] = "please fill in the fax";
                    $this->error_found++;
                }
            }

            if ($this->registration_configuration["use_url_field"]) {
                if ($this->configuration_data["require_url_field"]) {
                    if (strlen(trim($this->classified_variables[url])) == 0) {
                        $this->error[url] = "please fill in the url";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_1_field"]) {
                if ($this->registration_configuration["require_registration_optional_1_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_1])) == 0) {
                        $this->error[optional_field_1] = "please fill in the optional field 1";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_2_field"]) {
                if ($this->registration_configuration["require_registration_optional_2_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_2])) == 0) {
                        $this->error[optional_field_2] = "please fill in the optional field 2";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_3_field"]) {
                if ($this->registration_configuration["require_registration_optional_3_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_3])) == 0) {
                        $this->error[optional_field_3] = "please fill in the optional field 3";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_4_field"]) {
                if ($this->registration_configuration["require_registration_optional_4_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_4])) == 0) {
                        $this->error[optional_field_4] = "please fill in the optional field 4";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_5_field"]) {
                if ($this->registration_configuration["require_registration_optional_5_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_5])) == 0) {
                        $this->error[optional_field_5] = "please fill in the optional field 5";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_6_field"]) {
                if ($this->registration_configuration["require_registration_optional_6_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_6])) == 0) {
                        $this->error[optional_field_6] = "please fill in the optional field 6";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_7_field"]) {
                if ($this->registration_configuration["require_registration_optional_7_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_7])) == 0) {
                        $this->error[optional_field_7] = "please fill in the optional field 7";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_8_field"]) {
                if ($this->registration_configuration["require_registration_optional_8_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_8])) == 0) {
                        $this->error[optional_field_8] = "please fill in the optional field 8";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_9_field"]) {
                if ($this->registration_configuration["require_registration_optional_9_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_9])) == 0) {
                        $this->error[optional_field_9] = "please fill in the optional field 9";
                        $this->error_found++;
                    }
                }
            }

            if ($this->registration_configuration["use_registration_optional_10_field"]) {
                if ($this->registration_configuration["require_registration_optional_10_field"]) {
                    if (strlen(trim($this->classified_variables[optional_field_10])) == 0) {
                        $this->error[optional_field_10] = "please fill in the optional field 10";
                        $this->error_found++;
                    }
                }
            }

            $this->check_username($db);
            $this->check_password();
        }
        //echo $this->error_found." is error_found<bR>\n";

        if ($this->error_found > 0) {
            return false;
        } else {
            return true;
        }
    } //end of function check_info($info)

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function check_username($db)
    {
        //echo "hello from check_username<br>\n";
        $this->classified_variables["username"] = trim($this->classified_variables["username"]);
        $this->error['username'] = "";
        $username_length = strlen($this->classified_variables["username"]);
        if (($username_length == 0 ) || ($username_length > $this->db->get_site_setting('max_user_length')) || ($username_length < $this->db->get_site_setting('min_user_length')) || (!preg_match('/^[-a-zA-Z0-9_ ]+$/', $this->classified_variables["username"]))) {
            $this->error['username'] = "username character length must be between " . $this->db->get_site_setting('min_user_length') . " and " . $this->db->get_site_setting('max_user_length') . " (set in Admin Tools & Settings > Security Settings > General Security Settings). Only numbers, letters, spaces, _ and - characters are acceptable.";
            $this->error_found++;
        } else {
            $sql = "select id from " . $this->logins_table . " where username = \"" . $this->classified_variables["username"] . "\"";
            $result = $this->db->Execute($sql);
            //echo $sql."<br>\n";
            if (!$result) {
                $this->error["registration"] = urldecode($this->messages[230]);
                return false;
            }

            if ($result->RecordCount() > 0) {
                $this->error[username] = "username already exists";
                $this->error_found++;
            } else {
                $sql = "select * from " . $this->confirm_table . " where username = \"" . $this->classified_variables["username"] . "\"";
                $result = $this->db->Execute($sql);
                //echo $sql."<br>\n";
                if (!$result) {
                    $this->error["registration"] = urldecode($this->messages[230]);
                    return false;
                }
                if ($result->RecordCount() > 0) {
                    $this->error[username] = "username currently in the registration confirmation queue";
                    $this->error_found++;
                }
            }
        }
         return true;
    } //end of function check_username($username)

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_form_variables($info)
    {
        //get the variables from the form and save them
        if (is_array($info)) {
            reset($info);
            foreach ($info as $key => $value) {
            //while (list($key,$value) = each($info))
                if ($value != "none") {
                    $this->classified_variables[$key] = $value;
                }
                //echo $key." is the key and this is the value - ".$this->classified_variables[$key]."<br>\n";
            }
        }
    } //end of function get_sell_form_variables ($info)

//#####################################################################

    function check_password()
    {
        $this->classified_variables["password"] = trim($this->classified_variables["password"]);
        $this->classified_variables["password_confirm"] = trim($this->classified_variables["password_confirm"]);
        $password_length = strlen($this->classified_variables["password"]);
        if ((($password_length == 0 ) || ($password_length > $this->db->get_site_setting('max_pass_length')) || ($password_length < $this->db->get_site_setting('min_pass_length')))) {
            $this->error['password'] = "Password character length must be between " . $this->db->get_site_setting('min_pass_length') . " and " . $this->db->get_site_setting('max_pass_length') . " (Set in Admin Tools & Settings > Security Settings > General Security Settings)";
            $this->error_found++;
        }
        if (trim($this->classified_variables['username']) == $this->classified_variables["password"]) {
            $this->error['password'] =  "Username and password must be different.";
            $this->error_found++;
        }
        if ($this->classified_variables["password_confirm"] != $this->classified_variables["password"]) {
            $this->error['repeat_password'] = "your password confirmation did not match the password you entered";
            $this->error_found++;
        }
        return true;
    } //end of function check_password


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_new_user($db)
    {
        //make sure authorization is loaded.
        if (!isset($this->product_configuration) || !is_object($this->product_configuration)) {
            $this->product_configuration = geoPC::getInstance();
        }
        $sql = "insert into " . $this->db->geoTables->logins_table . " (username, password, hash_type, salt, status)
			values (?, ?, ?, ?, ?)";
        $hash_type = $this->db->get_site_setting('client_pass_hash');
        //Note: do not pass in salt since generating new password, not verifying existing
        $hash_pass = $this->product_configuration->get_hashed_password($this->classified_variables["username"], $this->classified_variables["password"], $hash_type);
        $salt = '';
        if (is_array($hash_pass)) {
            $salt = $hash_pass['salt'];
            $hash_pass = $hash_pass['password'];
        }
        $query_data = array($this->classified_variables["username"], $hash_pass, $hash_type, $salt, 1);
        //echo $sql." is the query<br>\n";
        $login_result = $this->db->Execute($sql, $query_data);
        if (!$login_result) {
            $this->site_error($this->db->ErrorMsg());
            $this->error["confirm"] = urldecode($this->messages[229]);
            return false;
        }
        $user_id = $this->db->Insert_ID();

        $locations = $_REQUEST['locations'];
        $geographicOverrides = geoRegion::getLevelsForOverrides();
        geoRegion::setUserRegions($user_id, $locations);
        //override main fields with region selections where necessary
        if ($geographicOverrides['city']) {
            $this->classified_variables['city'] = $locations[$geographicOverrides['city']];
        }
        if ($geographicOverrides['state']) {
            $this->classified_variables['state'] = $locations[$geographicOverrides['state']];
        }
        if ($geographicOverrides['country']) {
            $this->classified_variables['country'] = $locations[$geographicOverrides['country']];
        }

        //insert login data into the login table
        $sql = "insert into " . $this->userdata_table . "
			(id,username,email,newsletter,level,company_name,business_type,firstname,lastname,address,address_2,
			zip,city,state,country,phone,phone2,fax,url,date_joined,communication_type,rate_sum,rate_num,
			optional_field_1,optional_field_2,optional_field_3,optional_field_4,optional_field_5,
			optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10)
			values
			(" . $user_id . ",'" . $this->classified_variables["username"] . "','" . $this->classified_variables["email"] . "',
			'0', 0,'" . addslashes($this->classified_variables["company_name"]) . "',
			'" . addslashes($this->classified_variables["business_type"]) . "','" . addslashes($this->classified_variables["firstname"]) . "','" . addslashes($this->classified_variables["lastname"]) . "',
			'" . addslashes($this->classified_variables["address"]) . "','" . addslashes($this->classified_variables["address_2"]) . "','" . addslashes($this->classified_variables["zip"]) . "',
			'" . addslashes($this->classified_variables["city"]) . "','" . $this->classified_variables["state"] . "','" . $this->classified_variables["country"] . "',
			'" . addslashes($this->classified_variables["phone"]) . "','" . addslashes($this->classified_variables["phone_2"]) . "','" . addslashes($this->classified_variables["fax"]) . "','" . addslashes($this->classified_variables["url"]) . "'," . geoUtil::time() . ",1,0,0,
			'" . addslashes($this->classified_variables["optional_field_1"]) . "','" . addslashes($this->classified_variables["optional_field_2"]) . "',
			'" . addslashes($this->classified_variables["optional_field_3"]) . "','" . addslashes($this->classified_variables["optional_field_4"]) . "',
			'" . addslashes($this->classified_variables["optional_field_5"]) . "','" . addslashes($this->classified_variables["optional_field_6"]) . "',
			'" . addslashes($this->classified_variables["optional_field_7"]) . "','" . addslashes($this->classified_variables["optional_field_8"]) . "',
			'" . addslashes($this->classified_variables["optional_field_9"]) . "','" . addslashes($this->classified_variables["optional_field_10"]) . "')";

        $userdata_result = $this->db->Execute($sql);
        //echo $sql." is the query<br>\n";
        if (!$userdata_result) {
            $this->site_error($this->db->ErrorMsg());
            $this->error["confirm"] = urldecode($this->messages[229]);
            return false;
        } else {
            //insert into users_group_price_plans table
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $class_price_plan = $this->get_price_plan_from_group($db, $this->classified_variables["group_id"]);
                $auction_price_plan = $this->get_price_plan_from_group($db, $this->classified_variables["group_id"], 1);
                $sql = "insert into " . $this->user_groups_price_plans_table . "
  					(id,group_id,price_plan_id,auction_price_plan_id)
  					values
  					(" . $user_id . ","
                    . $this->classified_variables["group_id"] . ","
                    . $class_price_plan["price_plan_id"] . ","
                    . $auction_price_plan["price_plan_id"] . ")";
            } elseif (geoMaster::is('auctions')) {
                $auction_price_plan = $this->get_price_plan_from_group($db, $this->classified_variables["group_id"], 1);
                $sql = "insert into " . $this->user_groups_price_plans_table . "
    				(id,group_id,auction_price_plan_id)
    				values
    				(" . $user_id . "," . $this->classified_variables["group_id"] . "," . $auction_price_plan["price_plan_id"] . ")";
            } elseif (geoMaster::is('classifieds')) {
                $class_price_plan = $this->get_price_plan_from_group($db, $this->classified_variables["group_id"]);
                $sql = "insert into " . $this->user_groups_price_plans_table . "
    				(id,group_id,price_plan_id)
    				values
    				(" . $user_id . "," . $this->classified_variables["group_id"] . "," . $class_price_plan["price_plan_id"] . ")";
            } else {
                return false;
            }
            $group_result = $this->db->Execute($sql);
            //echo $sql." is the query<br>\n";
            if (!$group_result) {
                //echo $sql;
                $this->site_error($this->db->ErrorMsg());
                $this->error["confirm"] = urldecode($this->messages[229]);
                return false;
            }

            //let any order items who care know we're registering a new user from the admin
            $this->classified_variables['user_id'] = $user_id;
            geoOrderItem::callUpdate('admin_register_new_user_update', $this->classified_variables);
        }

        geoAddon::triggerUpdate('user_register', $this->classified_variables);
        $this->new_user_id = $user_id;
        return true;
    } //end of function insert_new_user

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function get_price_plan_from_group($db, $group_id = 0, $item = 0)
    {
        if (!$group_id) {
            $this->error_message = $this->internal_error_message;
            return false;
        }
        $sql = "select * from " . $this->classified_groups_table . " where group_id = " . $group_id;
        $group_price_plan_result = $this->db->Execute($sql);

        //echo $sql." is get_price_plan query<br>\n";
        if (!$group_price_plan_result) {
            $this->error_message = $this->internal_error_message;
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($group_price_plan_result->RecordCount() == 1) {
            $show_group_price_plan = $group_price_plan_result->FetchRow();
            if ($item) {
                //GET AUCTION PRICE PLAN
                $sql = "select * from " . $this->price_plan_table . " where price_plan_id = " . $show_group_price_plan["auction_price_plan_id"];
                $auction_price_plan_result = $this->db->Execute($sql);

                //$sql." is get_price_plan query<br>\n";
                if (!$auction_price_plan_result) {
                    $this->error_message = $this->internal_error_message;
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } elseif ($auction_price_plan_result->RecordCount() == 1) {
                    $show_price_plan = $auction_price_plan_result->FetchRow();
                } else {
                    return false;
                }
            } else {
                //GET CLASSIFIED PRICE PLAN
                $sql = "select * from " . $this->price_plan_table . " where price_plan_id = " . $show_group_price_plan["price_plan_id"];

                $price_plan_result = $this->db->Execute($sql);

                //$sql." is get_price_plan query<br>\n";
                if (!$price_plan_result) {
                    $this->error_message = $this->internal_error_message;
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } elseif ($price_plan_result->RecordCount() == 1) {
                    $show_price_plan = $price_plan_result->FetchRow();
                } else {
                    return false;
                }
            }
            return $show_price_plan;
        } else {
            //just display the user_id
            return false;
        }
    } //end of function get_price_plan_from_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function increase_image_count($db, $classified_id = 0)
    {
        if ($classified_id) {
            $sql_query = "select image from " . $this->classifieds_table . " where id = " . $classified_id;
            $result = $this->db->Execute($sql_query);
            //echo $sql_query." is the bracket display query<br>\n";
            if (!$result) {
                echo $sql_query . " is the bracket display query<br>\n";
                return false;
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
            }
            $new_image_count = ($show["image"] + 1);

            $sql = "update " . $this->classifieds_table . " set
				image = " . $new_image_count . "
				where id = " . $classified_id;
            $result = $this->db->Execute($sql);

            if (!$result) {
                echo $sql . " is the query<br>\n";
                return false;
            }

            return true;
        } else {
            return false;
        }
    } //end of function increase_image_count

//##################################################################################

    function update_account_balance($db, $user_id = 0, $amount = 0)
    {
        if ($user_id) {
            $sql = "SELECT * FROM " . $this->userdata_table . " WHERE id = " . $user_id;
            $previous_balance = $this->db->Execute($sql);

            if (!$previous_balance) {
                return false;
            } else {
                if ($row = $previous_balance->FetchRow()) {
                    $previous = $row['account_balance'];

                    $difference = $amount - $previous;

                    if ($difference) {
                        $sql = "INSERT INTO " . $this->balance_transactions . "
											(user_id,amount,date,approved) VALUES (" . $user_id . "," . $difference . "," . time() . ",1)";
                        $result = $this->db->Execute($sql);
                        if (!$result) {
                            return false;
                        }
                    }
                }
            }

            $sql = "update " . $this->userdata_table . " set
				account_balance = " . $amount . "
				where id = " . $user_id;
            $balance_result =  $this->db->Execute($sql);

            if (!$balance_result) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    } //end of function update_account_balance

//##################################################################################
    function display_users_list()
    {
        $this->body .= geoAdmin::m();

        if ($_REQUEST["b"] && is_array($_REQUEST["b"]) && !isset($_REQUEST['b']['password'])) {
            //search users
            $this->list_users($_REQUEST["b"]);
        } else {
            //display the simple and advanced search box
            $this->list_users();
        }
        $this->display_page();
    }
    function update_users_list()
    {
    }

//##################################################################################
    function display_users_view()
    {
        if ($_REQUEST["b"]) {
            //search users
            if ($_REQUEST["c"]) {
                $page = $_REQUEST["c"];
            } else {
                $page = "";
            }

            if ($_REQUEST["d"]) {
                $type = $_REQUEST["d"];
            } else {
                $type = "";
            }

            if (!$this->display_user_data($this->db, $_REQUEST['b'], $page, $type)) {
                return false;
            }
        } else {
            //display the simple and advanced search box
            if (!$this->list_users()) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_users_view()
    {
        //update info from order items
        geoOrderItem::callUpdate('Admin_user_management_update_users_view', intval($_GET['b']));
        geoAddon::triggerUpdate('Admin_user_management_update_users_view', intval($_GET['b']));
        return true;
    }
//##################################################################################
    function display_users_edit()
    {
        if ($_REQUEST["b"]) {
            //edit user form
            if (!$this->edit_user_form($this->db, $_REQUEST["b"])) {
                return false;
            }
        } else {
            //display the simple and advanced search box
            if (!$this->list_users()) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_users_edit()
    {
        if ($_REQUEST["b"] && $_REQUEST["c"]) {
            return $this->update_user_info($this->db, $_REQUEST["b"], $_REQUEST["c"]);
        }
        return false;
    }
//##################################################################################
    function display_users_remove()
    {
        $this->body .= geoAdmin::m();
        if ($_REQUEST["b"]) {
            //search users
            $this->remove_user_verify($_REQUEST["b"]);
        } else {
            $this->list_users();
        }
        $this->display_page();
    }
    function update_users_remove()
    {
        if ($_REQUEST["b"] && $this->remove_user($_REQUEST["b"])) {
            $_REQUEST["b"] = 0;
            return true;
        }
        return false;
    }
//##################################################################################
    function display_users_add()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $this->body .= $menu_loader->getUserMessages();

        if ($this->updated) {
            $this->list_users($_REQUEST["b"]);
        } else {
            $this->insert_new_user_form($this->db);
        }
        $this->display_page();
    }
    function update_users_add()
    {
        if ($_REQUEST["c"]) {
            $this->get_form_variables($_REQUEST["c"]);
            if ($this->check_info($this->db)) {
                $this->updated = 1;
                return $this->insert_new_user($this->db, $_REQUEST["c"]);
            }
        }
        return false; //if it gets this far, user not added.
    }
//##################################################################################
    function display_users_search()
    {
        if ($_REQUEST["b"]) {
            $this->search_users($this->db, $_REQUEST["b"]);
        } else {
            $this->advanced_user_search($this->db);
        }

        $this->display_page();
    }
    function update_users_search()
    {
    }
//##################################################################################
    function display_users_subs_change()
    {
        if (($_REQUEST["b"]) && ($_POST["d"])) {
            $this->display_user_data($this->db, $_REQUEST["b"]);
        } elseif ($_REQUEST["b"]) {
            $this->change_subscription_form($this->db, $_REQUEST["b"], $_REQUEST["c"]);
        } else {
            $this->list_users();
        }

        $this->display_page();
    }
    function update_users_subs_change()
    {
        if (($_REQUEST["b"]) && ($_POST["d"])) {
            if (!$this->update_subscription($this->db, $_REQUEST["b"], $_REQUEST["c"], $_POST["d"])) {
                return false;
            }
            return true;
        }
        return false;
    }
//##################################################################################
    function display_users_subs_delete()
    {
        if ($_REQUEST["b"]) {
            $this->display_user_data($this->db, $_REQUEST["b"]);
        } else {
            $this->list_users();
        }
        $this->display_page();
    }
    function update_users_subs_delete()
    {
        $userId = (int)$_REQUEST['b'];
        if (!$userId) {
            return false;
        }
        //first see if there is a recurring billing.
        $sql = "SELECT * FROM " . geoTables::user_subscriptions_table . "
			WHERE `user_id`=$userId";
        $row = $this->db->GetRow($sql);

        if ($row['recurring_billing']) {
            $recurring = geoRecurringBilling::getRecurringBilling($row['recurring_billing']);
            if ($recurring && $recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
                //TODO: Text?
                $result = $recurring->cancel('Subscription deleted by admin');
                if (!$result) {
                    geoAdmin::m('Error removing recurring billing for deleted subscription.  Recurring billing
						may need to be canceled manually through the payment gateway.', geoAdmin::ERROR);
                    //still proceed with removal of subscription.
                }
            }
            if (isset($_REQUEST['only_cancel_recurring']) && $_REQUEST['only_cancel_recurring']) {
                return true;
            }
        }

        $sql = "DELETE FROM " . geoTables::user_subscriptions_table . "
			WHERE `user_id` = $userId";
        $delete_result = $this->db->Execute($sql);

        if (!$delete_result) {
            geoAdmin::m('DB Error, deletion of subscription failed.  Error message: ' . $this->db->ErrorMsg(), geoAdmin::ERROR);
            return false;
        }
        return true;
    }
//##################################################################################
    function display_users_restart_ad()
    {
        $listingId = intval($_REQUEST["b"]);
        if (!$listingId) {
            geoAdmin::m('Listing ID not specified, cannot restart/upgrade listing!  Please try again.', geoAdmin::ERROR);
            $this->body .= geoAdmin::m();
            $this->display_page();
            return;
        }

        //force getting a fresh copy of the data from the listing class, because the updater manipulates data directly instead of using the class
        $listing = geoListing::getListing($listingId, true, false, true);

        if (!$listing) {
            geoAdmin::m('Error retrieving listing details.', geoAdmin::ERROR);
            $this->body .= geoAdmin::m();
            $this->display_page();
            return;
        }
        $agCheck = geoAddon::getUtil('attention_getters');

        $tpl_vars = array();
        $tpl_vars['listing'] = $listing->toArray();
        if (isset($tpl_vars['listing']['start_time']) && $tpl_vars['listing']['start_time'] < $tpl_vars['listing']['date']) {
            //make start time start at same time as date, to prevent error
            $tpl_vars['listing']['start_time'] = $tpl_vars['listing']['date'];
        }
        //need username to send message
        $tpl_vars['username'] = geoUser::userName($listing->seller);

        $tpl_vars['is_ent'] = geoPC::is_ent();
        $tpl_vars['agCheck'] = ($agCheck) ? 1 : 0;

        if ($agCheck) {
            $sql = "select * from " . geoTables::choices_table . " where type_of_choice = 10";
            //echo $sql."<br>\n";
            $tpl_vars['agChoices'] = $this->db->GetAll($sql);
        }
        $tpl_vars['error_messages'] = geoAdmin::m();

        geoView::getInstance()->setBodyTpl('upgrade_listing.tpl')
            ->setBodyVar($tpl_vars);
    }

    function update_users_restart_ad()
    {
        if (!isset($_REQUEST['b']) || !isset($_REQUEST['c'])) {
            return false;
        }
        $listingId = intval($_REQUEST['b']);
        $choices = $_REQUEST['c'];

        if (!$listingId || !$choices) {
            return false;
        }

        $listing = geoListing::getListing($listingId);

        if (!$listing) {
            return false;
        }
        if ($choices['live'] != $listing->live) {
            //listing has expired (or renewed) between time they viewed the page, and time they submitted
            geoAdmin::m('The listing data has changed since you first viewed this page.  To prevent listing corruption,
				your changes have been canceled.  Please double-check the current status of the listing, and re-apply your
				changes if they are still needed.  (The listing
				has recently ' . (($listing->live) ? 'been renewed)' : 'expired)'), geoAdmin::ERROR);
            return false;
        }
        //un-hide listing in case it is hidden
        $listing->hide = 0;
        $isLive = $listing->live;

        //set up the session vars
        $session_variables = array();

        //figure out the end time
        //convert hour to military time
        $ends_hour = $choices['ends']['Hour'];
        if ($ends_hour == 12) {
            $ends_hour = ($choices['ends']['Meridian'] == 'pm') ? 12 : 0;
        } elseif ($choices['ends']['Meridian'] == 'pm') {
            $ends_hour += 12;
        }

        if (isset($choices['start_time'])) {
            //convert hour to military time
            $start_time_hour = $choices['start_time']['Hour'];
            if ($start_time_hour == 12) {
                $start_time_hour = ($choices['start_time']['Meridian'] == 'pm') ? 12 : 0;
            } elseif ($choices['start_time']['Meridian'] == 'pm') {
                $start_time_hour += 12;
            }
        }

        //convert hour to military time
        $date_hour = $choices['date']['Hour'];
        if ($date_hour == 12) {
            $date_hour = ($choices['date']['Meridian'] == 'pm') ? 12 : 0;
        } elseif ($choices['date']['Meridian'] == 'pm') {
            $date_hour += 12;
        }

        if ($choices['unlimited_duration'] == 1) {
            $ends = 0;
        } else {
            $ends = mktime($ends_hour, $choices['ends']["Minute"], $choices['ends']["Second"], $choices['ends']["Month"], $choices['ends']["Day"], $choices['ends']["Year"]);
        }

        $date = mktime($date_hour, $choices['date']["Minute"], $choices['date']["Second"], $choices['date']["Month"], $choices['date']["Day"], $choices['date']["Year"]);

        if (isset($choices['start_time'])) {
            $start_time = mktime($start_time_hour, $choices['start_time']["Minute"], $choices['start_time']["Second"], $choices['start_time']["Month"], $choices['start_time']["Day"], $choices['start_time']["Year"]);
            if ($date > $start_time) {
                geoAdmin::m('The bidding must start some time after the start date, not before.', geoAdmin::ERROR);
                return false;
            }
            if ($start_time > $ends && $ends > 0) {
                geoAdmin::m('The bidding must start before the listing has ended.', geoAdmin::ERROR);
                return false;
            }
        }

        if ($ends <= $date && $ends > 0) {
            geoAdmin::m('The listing cannot end until some time after the start date.', geoAdmin::ERROR);
            return false;
        }
        $session_variables['date'] = $date;
        $session_variables['ends'] = $ends;
        if (isset($choices['start_time'])) {
            $session_variables['start_time'] = $start_time;
        }

        //loop through all the simple on/off extras
        $possible_extras = array (
            'featured_ad',
            'featured_ad_2',
            'featured_ad_3',
            'featured_ad_4',
            'featured_ad_5',
            'bolding',
            'better_placement'
        );
        foreach ($possible_extras as $extra) {
            $session_variables[$extra] = (isset($choices[$extra]) && $choices[$extra]) ? 1 : 0;
        }
        //now handle special-case extras
        if ($choices['attention_getter'] && $choices["attention_getter_choice"]) {
            //get url of chosen attention getter
            $ag_sql = "SELECT * FROM " . geoTables::choices_table . " WHERE `choice_id` = " . intval($choices["attention_getter_choice"]);
            $line = $this->db->GetRow($ag_sql);
            $ag_url = $line['value'];
            if ($ag_url) {
                $session_variables['attention_getter'] = 1;
                $session_variables['attention_getter_url'] = $ag_url;
            } else {
                $session_variables['attention_getter'] = 0;
                $session_variables['attention_getter_url'] = '';
            }
        } elseif (isset($choices['attention_getter'])) {
            $session_variables['attention_getter'] = 0;
            $session_variables['attention_getter_url'] = '';
        }

        //classified-specific stuff
        if ($listing->item_type == 1) {
            //well there really isn't any classified specific stuff...
        }

        //auction-specific stuff
        if ($listing->item_type == 2 && $choices['remove_current_bids'] == 1) {
            //we will be removing the bids in the order item itself

            //Change values for prices
            $session_variables['current_bid'] = 0.00;
            $session_variables['final_price'] = 0.00;

            if ($listing->auction_type != 2 && $choices['buy_now_only']) {
                //min bid and reserve is 0 if buy now only listing
                if (geoNumber::deformat($choices['buy_now']) >= 0.01) {
                    $session_variables['auction_buy_now'] = geoNumber::deformat($choices['buy_now']);
                } else {
                    //buy now price not valid
                    geoAdmin::m('Buy now price specified is not valid.', geoAdmin::ERROR);
                    return false;
                }

                $session_variables['buy_now_only'] = 1;
                $session_variables['auction_minimum'] = 0.00;
                $session_variables['auction_reserve'] = 0.00;
            } else {
                $session_variables['buy_now_only'] = 0;
                if (geoNumber::deformat($choices['starting_bid']) >= 0.01) {
                    $session_variables['auction_minimum'] = geoNumber::deformat($choices['starting_bid']);
                } else {
                    geoAdmin::m('Invalid minimum bid specified, it must be at least 0.01.', geoAdmin::NOTICE);
                    //make sure current setting is good
                    if (!$listing->minimum_bid) {
                        $session_variables['auction_minimum'] = 0.01;
                    } else {
                        //give something for other checks to check against
                        $session_variables['auction_minimum'] = $listing->minimum_bid;
                    }
                }
                if (geoNumber::deformat($choices['reserve_price']) > $session_variables['auction_minimum']) {
                    $session_variables['auction_reserve'] = geoNumber::deformat($choices['reserve_price']);
                } else {
                    //set reserve to 0 if not valid
                    $session_variables['auction_reserve'] = 0.00;
                    if (geoNumber::deformat($choices['reserve_price']) > 0) {
                        //admin tried to set reserve to something invalid.
                        geoAdmin::m('Reserve must be greater than starting (minimum) bid to take effect.', geoAdmin::NOTICE);
                    }
                }
                if ($listing->auction_type != 2 && geoNumber::deformat($choices['buy_now']) >= $listing->reserve_price) {
                    $session_variables['auction_buy_now'] = geoNumber::deformat($choices['buy_now']);
                } else {
                    $session_variables['auction_buy_now'] = 0.00;
                    if ($listing->auction_type != 2 && geoNumber::deformat($choices['buy_now']) > 0) {
                        geoAdmin::m('Buy Now price must be greater than reserve price to take effect.', geoAdmin::NOTICE);
                    }
                }
            }

            if ($listing->auction_type == 2) {
                //dutch auction, quantity needs to be at least 2
                $session_variables['auction_quantity'] = ((int)$choices['quantity'] > 1) ? (int)$choices['quantity'] : 1;
            } else {
                //normal auction, quantity needs to be at least 1
                $session_variables['auction_quantity'] = ((int)$choices['quantity'] > 0) ? (int)$choices['quantity'] : 1;
            }

            if ((int)$choices['quantity'] != $session_variables['auction_quantity']) {
                //let admin know the quantity needs to be over a certain amount
                geoAdmin::m('The quantity needs to be at least ' . $session_variables['auction_quantity'] . ' for this type of auction.', geoAdmin::NOTICE);
            }
        }

        //the shared code always re-sets the category. make sure it's in the sessvars now so that it doesn't get lost
        $session_variables['category'] = $listing->category;

        //now let the listing change admin order item do all the work for us
        require_once(CLASSES_DIR . 'order_items/listing_change_admin.php');
        $item = listing_change_adminOrderItem::init($listing, $session_variables, $choices);
        if (!$item) {
            return false;
        }
        return true;
    }

    /**
     * Displays the listing
     * @todo Convert this to a smarty template so it's easier to maintain
     */
    public function display_users_view_ad()
    {
        //form at the top of the admin submits to this page under a different name, because conflicts with other searches
        $listingId = $_REQUEST['search_top'] ? (int)$_REQUEST['search_top'] : (int)$_REQUEST['b'];
        $admin = geoAdmin::getInstance();

        if (!$listingId) {
            $admin->message('Invalid listing id!', geoAdmin::ERROR);
            $admin->v()->addBody($admin->message());
            return;
        }

        $listing = geoListing::getListing($listingId, true, true);
        if (!$listing) {
            $admin->message('Could not find specified listing.', geoAdmin::ERROR);
            $admin->v()->addBody($admin->message());
            return;
        }

        $tpl_vars = array();

        $sql = "SELECT bid.*, user.username FROM " . geoTables::bid_table . " as bid, " . geoTables::logins_table . " as user WHERE bid.bidder=user.id AND bid.auction_id = " . $listingId;
        $tpl_vars['bid_history'] = $this->db->GetAll($sql);

        $sql = "SELECT bid.*, user.username FROM " . geoTables::autobid_table . " as bid, " . geoTables::logins_table . " as user WHERE bid.bidder=user.id AND bid.auction_id = " . $listingId;
        $tpl_vars['proxy_bid_history'] = $this->db->GetAll($sql);

        $user = geoUser::getUser($listing->seller);

        $tpl_vars['category_tree'] = geoCategory::getTree($listing->category);
        $tpl_vars['userId'] = $listing->seller;
        $tpl_vars['user'] = $user->toArray();
        $tpl_vars['listingId'] = $listingId;
        $tpl_vars['listing'] = $listing->toArray();
        $tpl_vars['listing']['location'] = geoRegion::displayRegionsForListing($listingId);
        $tpl_vars['listing']['locations'] = geoListing::getRegionTrees($listingId);
        $tpl_vars['entry_date_configuration'] = $this->db->get_site_setting('entry_date_configuration');
        $tpl_vars['price_plan_name'] = $this->get_price_plan_name($db, $listing->price_plan_id);
        $tpl_vars['is_expired'] = $listing->isExpired();
        $plans = $this->db->GetAll("SELECT `price_plan_id`, `name` FROM " . geoTables::price_plans_table . " WHERE `applies_to` = " . (int)$listing->item_type);
        $tpl_vars['plan_choices'] = (count($plans) > 1) ? $plans : false;

        if ($listing->live) {
            $tpl_vars['listing_link'] = $listing->getFullUrl();
        }

        $pre = $listing->precurrency;
        $post = $listing->postcurrency;

        $fields = geoFields::getInstance((int)$user->group_id, (int)$listing->category);
        $optionals = array();
        if (geoPC::is_ent()) {
            for ($i = 1; $i < 21; $i++) {
                $field = "optional_field_$i";
                if (strlen(trim($listing->$field)) > 0) {
                    $optional_field_name = $this->db->get_site_setting('optional_field_' . $i . '_name');

                    if ($optional_field_name != 'Optional Field ' . $i) {
                        $optional_field_name .= " (Optional Field $i)";
                    }

                    $value = geoString::fromDB($listing->$field);

                    if ($fields->$field->field_type == 'cost') {
                        $value = geoString::displayPrice($value, $pre, $post);
                    }
                    $optionals[$i] = array (
                        'label' => $optional_field_name,
                        'value' => $value,
                    );
                }
            }
        }
        $tpl_vars['optionals'] = $optionals;

        $tpl_vars['extras'] = $this->db->GetAll("SELECT * FROM " . geoTables::classified_extra_table . " WHERE `classified_id` = {$listingId} ORDER BY `display_order`");
        $tpl_vars['tags'] = geoListing::getTags($listingId);

        $tpl_vars['images'] = $this->db->GetAll("SELECT * FROM " . geoTables::images_urls_table . " WHERE `classified_id`={$listingId} ORDER BY `display_order`");

        foreach ($tpl_vars['images'] as $key => $image) {
            //figure out if each one is absolute link or not...
            $tpl_vars['images'][$key]['is_abs_url'] = (preg_match('|^https?://|', $image['image_url']));
        }

        $tpl_vars['order_items'] = false;
        $allItems = $listing->getAllOrderItems();
        if ($allItems) {
            $data = array();
            foreach ($allItems as $itemId) {
                $data[] = array ('id' => $itemId);
            }
            require_once(ADMIN_DIR . 'items.php');
            $adminItemClass = Singleton::getInstance('OrderItemManagement');
            $tpl_vars['order_items'] = $adminItemClass->_getItems(null, null, array(), '', 'oi.id', 'up', 0, 20, $data);
        }

        //get all the offiste videos (if any)
        $tpl_vars['offsite_videos'] = $this->db->GetAll("SELECT * FROM " . geoTables::offsite_videos . " WHERE `listing_id`=$listingId ORDER BY `slot`");

        if ($this->db->get_site_setting('verify_accounts') && $user && $user->verified == 'yes') {
            $txt = $this->db->get_text(true, 59);
            $tpl_vars['verify_img'] = $txt[500952];
        }

        $tpl_vars['adminMsgs'] = geoAdmin::m();

        $admin->setBodyTpl('listing_details/index.tpl')
            ->v()->setBodyVar($tpl_vars);
    }

    function update_users_view_ad()
    {
        $listingId = (int)$_GET['b'];
        $pricePlanId = (int)$_POST['c'];
        if (!$listingId || !$pricePlanId) {
            geoAdmin::m('Invalid data for updating price plan.', geoAdmin::ERROR);
            return false;
        }
        $listing = geoListing::getListing($listingId);
        if ($listing) {
            //should we check the price plan ID specified? for now don't check it..
            $listing->price_plan_id = $pricePlanId;
            return true;
        }
        geoAdmin::m('Could not update price plan, could not find specified listing to update.');
        return false;
    }

    function display_users_max_photos()
    {
        if (($_REQUEST["b"]) && ($_REQUEST["c"])) {
            if (!$this->increase_image_count($this->db, $_REQUEST["b"])) {
                return false;
            } elseif (!$this->display_user_data($this->db, $_REQUEST["c"])) {
                return false;
            }
        } else {
            return false;
        }
        $this->display_page();
    }
    function update_users_max_photos()
    {
    }
//##################################################################################

    public function display_users_ratings_detail()
    {
        $about = (int)$_GET['b'];
        $page = ($_GET['p']) ? (int)$_GET['p'] : 1;
        $resultsPerPage = 30;

        $db = DataAccess::getInstance();

        $sql = "SELECT `from` FROM " . geoTables::user_ratings . " WHERE `about` = ? ORDER BY `from` ASC LIMIT " . ($resultsPerPage * ($page - 1)) . ", " . $resultsPerPage;
        $countSql = "SELECT COUNT(`from`) FROM " . geoTables::user_ratings . " WHERE `about` = ?";

        $ratings = $db->Execute($sql, array($about));
        $numRatings = $db->GetOne($countSql, array($about));

        $tpl_vars = array();
        foreach ($ratings as $rating) {
            $tpl_vars['rendered'][$rating['from']] = geoUserRating::render($about, $rating['from']);
        }
        $tpl_vars['average_rating_raw'] = geoUserRating::getAverageRating($about);
        $tpl_vars['average_rating_rendered'] = geoUserRating::render($about);

        if ($numRatings > $resultsPerPage) {
            $totalPages = ceil($numRatings / $resultsPerPage);
            $link = "index.php?mc=users&page=users_ratings_detail&b=" . $about . "&p=";
            $tpl_vars['pagination'] = geoPagination::getHTML($totalPages, $page, $link);
        }

        $tpl_vars['adminMsgs'] = geoAdmin::m();
        geoView::getInstance()->setBodyTpl('user_ratings_details.tpl')->setBodyVar($tpl_vars);
    }

    public function display_users_purge()
    {
        $tpl_vars = array();
        $tpl_vars['admin_msgs'] = geoAdmin::m();

        geoView::getInstance()->setBodyTpl('purge_users.tpl')
        ->setBodyVar($tpl_vars)
        ->addCssFile('css/calendarview.css')
        ->addJScript('../js/calendarview.js');
    }
    public function display_users_confirm_purge()
    {
        if (!$_POST['purgeBefore']) {
            //likely just did a purge (or something went wrong on the first form)
            //go back to the original input page instead of this one
            return $this->display_users_purge();
        }

        $tpl_vars = array();
        $tpl_vars['admin_msgs'] = geoAdmin::m();
        $tpl_vars['confirm'] = 1;

        $db = DataAccess::getInstance();

        //db last_login_time column uses the format Y-m-d H:i:s
        //as long as we translate the chosen time to that format, they should be directly comparable in the db
        $tpl_vars['purgeBefore'] = strtotime($_POST['purgeBefore']);
        $targetDate = date("Y-m-d H:i:s", $tpl_vars['purgeBefore']);

        //also do not purge the "admin" or "anonymous" users
        if (geoAddon::getInstance()->isEnabled('anonymous_listing')) {
            $reg = geoAddon::getRegistry('anonymous_listing');
            $anonymous_username = $reg->get('anon_user_name', 'Anonymous');
        } else {
            $anonymous_username = "";
        }

        $sql = "SELECT `id`, `username`, `firstname`, `lastname` FROM " . geoTables::userdata_table . " WHERE `last_login_time` < ? AND `id` != 1 AND `username` != ?";
        $result = $db->Execute($sql, array($targetDate, $anonymous_username));
        if (!$result || $result->RecordCount() < 1) {
            geoAdmin::m('Found no users to purge. Try a different date.', geoAdmin::NOTICE);
            return $this->display_users_purge();
        }
        foreach ($result as $u) {
            $tpl_vars['to_purge'][] = $u;
        }

        geoView::getInstance()->setBodyTpl('purge_users.tpl')->setBodyVar($tpl_vars);
    }
    public function update_users_confirm_purge()
    {
        $db = DataAccess::getInstance();
        $purgeBefore = (int)$_POST['doPurgeOn'];
        if (!$purgeBefore) {
            geoAdmin::m('Invalid date input', geoAdmin::ERROR);
            return false;
        }

        //re-query the users to remove
        $targetDate = date("Y-m-d H:i:s", $purgeBefore);
        if (geoAddon::getInstance()->isEnabled('anonymous_listing')) {
            $reg = geoAddon::getRegistry('anonymous_listing');
            $anonymous_username = $reg->get('anon_user_name', 'Anonymous');
        } else {
            $anonymous_username = "";
        }
        $sql = "SELECT `id` FROM " . geoTables::userdata_table . " WHERE `last_login_time` < ? AND `id` != 1 AND `username` != ?";
        $result = $db->Execute($sql, array($targetDate, $anonymous_username));

        //now remove any in that list
        $removed = 0;
        foreach ($result as $u) {
            if ($this->remove_user($u['id'], true)) {
                $removed++;
            }
        }
        geoAdmin::m('Purged ' . $removed . ' users', geoAdmin::SUCCESS);
        return true;
    }
} //end of class Admin_user_management
