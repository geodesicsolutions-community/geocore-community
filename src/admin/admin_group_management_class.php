<?php

// admin_group_management_class.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.09.0-109-g68fca00
##
##################################

class Group_management extends Admin_site
{

    var $debug_groups = 0;
    var $new_group_error = "";
    var $deletedGroup = 0;
    function display_group_list()
    {
        $menu_loader = geoAdmin::getInstance();

        $this->sql_query = "select * from " . $this->classified_groups_table . " order by name";
        $result = $this->db->Execute($this->sql_query);
        if ($result === false) {
            trigger_error("ERROR SQL: " . $this->db->ErrorMsg());
            $menu_loader->userError("Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.");
            $this->body .= $menu_loader->getUserMessages();
            return false;
        }

        $this->body .= "<script type=\"text/javascript\">";
        $this->body .= "
			Text[16] = [\"Default Registration Setting\", \"<strong>None</strong> - New users will NOT be placed into this group by default. <br /><strong>All</strong> - All new users that do not match any of the other 'Default Registration Setting' criteria will be placed into this group.<br /><strong>Individual</strong> - New users who select an Account Type of 'Individual' during registration will be placed into this group.<br /><strong>Business</strong> - New users who select an Account Type of 'Business' during registration will be placed into this group. \"]\n
			";
        $this->body .= "</script>";

        $this->body .= $menu_loader->getUserMessages();
        //whether enterprise pricing addon is enabled
        $is_ent = geoAddon::getInstance()->isEnabled('enterprise_pricing');
        //whether subscription addon is enabled
        $is_sub = geoAddon::getInstance()->isEnabled('subscription_pricing');

        if (!$this->admin_demo()) {
            $this->body .= "<form action=index.php?mc=users&page=users_groups method=post onsubmit='if(countDefaults() != 1){alert(\"You must have exactly one Default set to \\\"all\\\"\");return false;}'>";
        }
        $this->body .= "
			<fieldset id='CurrentGroups'>
				<legend>Current User Groups</legend>
				<div class=\"table-responsive\">";
        if (!$is_ent) {
            $this->body .= "<p class='page_note'><strong>Need more user groups?</strong>
					Just <strong>install</strong> and <strong>enable</strong> the <strong>Enterprise Pricing Addon</strong>, in the admin at <strong>Addons &gt; Addon Management</strong>.  This will activate multiple user groups, along with other advanced setting options for user groups and price plans.</p>";
        }
        $this->body .= "
				<table cellpadding=3 cellspacing=1 border=0 width=\"100%\" class=\"table table-hover table-striped table-bordered\">
				<thead>
				<tr align=center class='col_hdr_top'>
					<td class='col_hdr_left' width='40%'><b>User Group Name</b></td>
					<td class='col_hdr'><i class='fa fa-users' style='font-size: 2em; margin-top:4px;'></i></td>";
        if (geoMaster::is('classifieds')) {
            $this->body .= "
					<td align=center class='col_hdr' width=\"15%\"><b><i class='fa fa-newspaper-o'></i> Classifieds<br>Price Plan</b></td>";
        }
        if (geoMaster::is('auctions')) {
            $this->body .= "
					<td align=center class='col_hdr' width=\"15%\"><b><i class='fa fa-gavel'></i> Auctions<br>Price Plan</b></td>";
        }
        if ($is_ent || $is_sub) {
            $this->body .= "
					<td class='col_hdr'><span class='color-primary-four'><b>Default</b> " . $this->show_tooltip(16, 1) . "</span></td>";
        }
        $this->body .= "
					<td colspan=3 class='col_hdr' width=\"30%\">&nbsp;</td>
				</tr>
				</thead>";

        $this->row_count = 0;
        while ($show = $result->FetchRow()) {
            $this->sql_query = "select count(*) as group_total from " . $this->user_groups_price_plans_table . " where group_id = " . $show["group_id"] . " and id!=1";
            $group_count_result = $this->db->Execute($this->sql_query);
            if (!$group_count_result) {
                //echo $this->sql_query."<br>\n";
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($group_count_result->RecordCount() == 1) {
                $show_group_count = $group_count_result->FetchRow();
            }
            $this->body .= "
				<tr style='height:4.5em;'>
					<td class=medium_font><span class='chart-header1 group-color'><a href=index.php?mc=users&page=users_group_edit&c=" . $show["group_id"] . ">" . $show["name"] . "</a></span><br>" . $show["description"] . "</td>
					<td align=center class=medium_font>" . $show_group_count["group_total"] . "</td>";
            if (geoMaster::is('classifieds')) {
                $this->body .= "
					<td align=center class=medium_font><a href=index.php?mc=pricing&page=pricing_edit_plans&g=" . $show["price_plan_id"] . ">
						<span class='medium_font plan-color'>" . $this->get_price_plan_name($db, $show["price_plan_id"]) . "</span></a>
					</td>";
            }
            if (geoMaster::is('auctions')) {
                $this->body .= "
						<td align=center class=medium_font>";
                if ($show["auction_price_plan_id"]) {
                    $this->body .= "
								<a href=index.php?mc=pricing&page=pricing_edit_plans&g=" . $show["auction_price_plan_id"] . " class='plan-color'>
								<span class='medium_font plan-color'>" . $this->get_price_plan_name($db, $show["auction_price_plan_id"]) . "</span></a>";
                } else {
                    $this->body .= "
								<span class=medium_error_font>no default</span>";
                }
            }
            if ($is_ent || $is_sub) {
                $this->body .= "
						</td>
						<td align=center class=medium_font>

							<select name='e[{$show['group_id']}]' id='default_for_{$show['group_id']}' onchange='checkDefaults({$show['group_id']});'>
								<option value='0' " . ($show['default_group'] == 0 ? 'selected="selected"' : '') . ">None</option>
								<option value='1' " . ($show['default_group'] == 1 ? 'selected="selected"' : '') . ">All</option>
								<option value='2' " . ($show['default_group'] == 2 ? 'selected="selected"' : '') . ">Individual</option>
								<option value='3' " . ($show['default_group'] == 3 ? 'selected="selected"' : '') . ">Business</option>
							</select>";
            } else {
                //use hidden input in the td above
                $this->body .= "
						<input type='hidden' name='e[{$show['group_id']}]' id='default_for_{$show['group_id']}' value='1' />";
            }
            $this->body .= "
						</td>
						<td align=center>";
            if ($is_ent || $is_sub) {
                        $this->body .= "
						<a href='index.php?mc=users&page=users_group_move&g={$show["group_id"]}' class='btn btn-primary btn-xs' style='margin:0 0 3px 0'><i class='fa fa-exchange'></i> Move Users</a>
						";
            }
                        $this->body .= "
						<a href='index.php?mc=users&page=users_group_edit&c={$show["group_id"]}' class='btn btn-info btn-xs' style='margin:0 0 3px 0'><i class='fa fa-pencil'></i> Edit</a>
						";
            if ($show["group_id"] != 1) {
                        $this->body .= "
						<a href='index.php?mc=users&page=users_group_delete&c={$show["group_id"]}' class='btn btn-danger btn-xs' style='margin:0 0 3px 0'><i class='fa fa-trash'></i> Delete</a>
						";
            } else {
                $this->body .= "&nbsp;";
            }
            $this->body .= "
						</td>
					</tr>";
            $this->row_count++;
        }
        //javascript to make sure there are no duplicate default types
        $this->body .= '<script type="text/javascript">
							checkDefaults = function(d) {
								var check = $F("default_for_"+d);
								if(check == 0) {
									//group not set to any default -- nothing to check
									return true;
								}

								$$("select[id^=\'default_for\']").each(function(sel) {

									if(sel.identify() != "default_for_"+d && check == $F(sel)) {
										alert("That default type already exists. Please choose another.");
										$("default_for_"+d).value = 0;
										$break;
									}
								});
								return true;
							}

							countDefaults = function() {
								count = 0;
								$$("select[id^=\'default_for\']").each(function(sel) {
									if($F(sel) == 1) {
										count++;
									}
								});
								return count;
							}
						</script>
					<tr>';

        $this->body .= "
				</table></div>";

        if (!$this->admin_demo() && ($is_ent || $is_sub)) {
            $this->body .= "<div class='center warning'><input type=submit name='auto_save' value=\"Save Default Settings\" class=\"btn btn-warning btn-xs\"></div>";
        }

        $this->body .= "</fieldset>";

        if (geoAddon::getInstance()->isEnabled('enterprise_pricing')) {
            $this->body .= "
							<div class='center'>
								<a href=index.php?mc=users&page=users_new_group class='btn btn-success'><i class='fa fa-plus-circle'></i> New User Group</a>
							</div>";
        } else {
            $this->body .= "<div class='center'>The ability to add additional user groups is disabled by default, for simplicity. To add more User Groups, <a href='index.php?page=addon_tools&mc=addon_management'>Enable the Enterprise Pricing addon</a>.</div>";
        }

            $this->body .= "</form>";

        return true;
    } //end of function display_group_list

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_group($db, $group_info = 0)
    {
        if (!geoPC::is_ent() && !geoPC::is_premier()) {
            return false;
        }

        $addon = $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';


        if (!$group_info) {
            if ($this->debug_groups) {
                $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
            }
            return false;
        }

        if (!$this->check_registration_code($db, 0, $group_info["registration_code"])) {
            $this->new_group_error = "That registration code already exists, please try again.";
            if ($this->debug_groups) {
                $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
            }
            return false;
        }

        if (geoMaster::is('auctions')) {
            $new_auc_price_plan_id = $group_info["price_plan_type"] == 1 ? $group_info["new_auc_price_plan_fee"] : $group_info["new_auc_price_plan_sub"];
        }
        if (geoMaster::is('classifieds')) {
            $new_class_price_plan_id = $group_info["price_plan_type"] == 1 ? $group_info["new_class_price_plan_fee"] : $group_info["new_class_price_plan_sub"];
        }


        if ($group_info['price_plan_type'] == 2 && !geoAddon::getInstance()->isEnabled('subscription_pricing')) {
            geoAdmin::m('Invalid Price Plan Type', geoAdmin::ERROR);
            return false;
        }

        $num_products = (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? 2 : 1;
        for ($i = $num_products; $i > 0; $i--) {
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                //initialize variables and then switch 2nd time through the loop
                $price_plan_column = ($i == 2) ? "price_plan_id" : "auction_price_plan_id";
                $other_price_plan_column = ($i == 2) ? "auction_price_plan_id" : "price_plan_id";
            } else {
                //only gonna do this loop once, so we need to specify which product type we're using
                if (geoMaster::is('auctions') && !geoMaster::is('classifieds')) {
                    $price_plan_column = "auction_price_plan_id";
                } elseif (geoMaster::is('classifieds')) {
                    $price_plan_column = "price_plan_id";
                }
                $other_price_plan_column = "";
            }
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                if (!$group_info["price_plan_type"]) {
                    return false;
                }
                if ($group_info["price_plan_type"] == 2) {
                    if (!$other_price_plan_column) {
                        return false;
                    }
                    $new_id = "";
                    if ($price_plan_column == "auction_price_plan_id" && $group_info["sub_period_choice"] == 1) {
                        $this->sql_query = "SELECT * FROM $this->classified_subscription_choices_table
							WHERE
								price_plan_id = $new_auc_price_plan_id";
                        $new_id = $new_auc_price_plan_id;
                        $other_id = $new_class_price_plan_id;
                    } elseif ($price_plan_column == "price_plan_id" && $group_info["sub_period_choice"] == 2) {
                        $this->sql_query = "SELECT * FROM $this->classified_subscription_choices_table
							WHERE
								price_plan_id = $new_class_price_plan_id";
                        $new_id = $new_class_price_plan_id;
                        $other_id = $new_auc_price_plan_id;
                    }
                    if (strlen($new_id) > 0) {
                        $new_sub_result = $this->db->Execute($this->sql_query);
                        if ($new_sub_result === false) {
                            return false;
                        }
                        if ($other_id) {
                            //only attempt to delete if there is stuff to delete.
                            $this->sql_query = "DELETE FROM $this->classified_subscription_choices_table WHERE
								price_plan_id = $other_id";
                            if ($this->db->Execute($this->sql_query) === false) {
                                return false;
                            }
                        }
                        if ($new_sub_result->RecordCount() > 0) {
                            while ($new_subscription = $new_sub_result->FetchRow()) {
                                $this->sql_query = "INSERT INTO $this->classified_subscription_choices_table
								(display_value,value,amount,price_plan_id)
								VALUES
								(
									'" . $new_subscription["display_value"] . "',
									'" . $new_subscription["value"] . "',
									'" . $new_subscription["amount"] . "',
									'" . $other_id . "'
								)";
                                if ($this->db->Execute($this->sql_query) === false) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (strlen(trim($group_info["name"])) > 0) {
            $restrictions = 0;
            if (!geoPC::is_ent()) {
                $restrictions += 1 + 2 + 4 + 8 + 16 + 32;
            }
            if ($group_info["restrict_1"] == 'on') {
                $restrictions += 1; // create listing
            }
            if ($group_info["restrict_2"] == 'on') {
                $restrictions += 2; // messaging
            }
            if ($group_info["restrict_4"] == 'on') {
                $restrictions += 4; // favorites
            }
            if ($group_info["restrict_8"] == 'on') {
                $restrictions += 8; // filters
            }
            if ($group_info["restrict_16"] == 'on') {
                $restrictions += 16; // white/blacklists
            }
            if ($group_info["restrict_32"] == 'on') {
                $restrictions += 32; // feedback
            }
            if (isset($group_info["registration_splash_code"]) && isset($group_info["place_an_ad_splash_code"]) && isset($group_info["sponsored_by_code"])) {
                $this->sql_query = "INSERT INTO " . $this->classified_groups_table . "
					(
						name,
						description,
						price_plan_id,
						auction_price_plan_id,
						registration_code,
						registration_splash_code,
						place_an_ad_splash_code,
						sponsored_by_code,
						restrictions_bitmask
					)
					VALUES
					(
						\"" . $group_info["name"] . "\",
						\"" . $group_info["description"] . "\",
						\"" . $new_class_price_plan_id . "\",
						\"" . $new_auc_price_plan_id . "\",
						\"" . $group_info["registration_code"] . "\",
						\"" . trim(geoString::toDB($group_info["registration_splash_code"])) . "\",
						\"" . trim(geoString::toDB($group_info["place_an_ad_splash_code"])) . "\",
						\"" . trim(geoString::toDB($group_info["sponsored_by_code"])) . "\",
						\"" . $restrictions . "\"
					)";
            } else {
                $this->sql_query = "INSERT INTO " . $this->classified_groups_table . "
					(
						name,
						description,
						price_plan_id,
						auction_price_plan_id,
						registration_code,
						restrictions_bitmask
					)
					VALUES
					(
						\"" . $group_info["name"] . "\",
						\"" . $group_info["description"] . "\",
						\"" . $new_class_price_plan_id . "\",
						\"" . $new_auc_price_plan_id . "\",
						\"" . $group_info["registration_code"] . "\",
						\"" . $restrictions . "\"
					)";
            }
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->debug_groups = 1;
                if ($this->debug_groups) {
                    $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
                }
                return false;
            }
            $group_id = $this->db->Insert_Id();

            //allow addons to do their own thing
            geoAddon::triggerUpdate('admin_update_insert_group', array(
                'group_id' => $group_id,
                'group_info' => $group_info
            ));


            //TODO: Change this to use the above triggerUpdate
            $ebayUtil =& geoAddon::getUtil('ebay');
            if ($ebayUtil) {
                if (isset($group_info["ebay_priceplan"])) {
                    $sql = "insert into geodesic_ebay_groups_price_plans
						(group_id, plan_id)
						values
						(?, ?)";
                        $result = $this->db->Execute($sql, array($group_id,$group_info["ebay_priceplan"]));
                    if (!$result) {
                        //echo $this->sql_query."<br>\n";
                        $this->new_group_error = "eBay price plan was not attached.<br />";
                        return false;
                    }
                }
            }
            $admin->userSuccess('New user group added.');

            return true;
        } else {
            //means that there was no name entered.
            $this->new_group_error .= "<br />Group Name is a required field.<br />";
            if ($this->debug_groups) {
                $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
            }
            return false;
        }
    } //end of function insert_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_group($db, $group_id = 0)
    {
        if ($group_id) {
            if ($group_id == 1) {
                $this->body .= "<font size=4 color=red>cannot delete this group <br>
					<font size=2 color=red>This is the group users are placed in if errors occur among the group structure ";
                return true;
            }
            $this->sql_query = "select * from " . $this->classified_groups_table . "
				where group_id = " . $group_id;
            $group_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$group_result) {
                //echo $this->sql_query."<br>\n";
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($group_result->RecordCount == 1) {
                $show_group = $group_result->FetchRow();
                if ($show_group["default_group"] == 1) {
                    $this->body .= "<font size=4 color=red>cannot delete default group <br>
						<font size=2 color=red>make a different group the default group and try deleting again ";
                    return true;
                }
            }

            $this->sql_query = "delete from " . $this->classified_groups_table . "
				where group_id = " . $group_id;
            //echo $this->sql_query."<br>\n";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                //echo $this->sql_query."<br>\n";
                $this->error_message = $this->internal_error_message;
                return false;
            }

            // Remove group-specific questions
            if (false === $this->db->Execute("delete from " . $this->classified_questions_table . " where group_id = '" . $group_id . "'")) {
                $this->error_message = $this->internal_error_message;
                return false;
            }
            if (!$result) {
                //echo $this->sql_query."<br>\n";
                $this->error_message = $this->internal_error_message;
                return false;
            } else {
                return true;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function delete_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function set_default_group($defaults)
    {
        if (!$defaults) {
            return false;
        }
        $db = DataAccess::getInstance();
        foreach ($defaults as $group_id => $default_type) {
            $sql = "UPDATE " . geoTables::classified_groups_table . " SET `default_group` = ? WHERE `group_id` = ?";
            $result = $db->Execute($sql, array($default_type, $group_id));
            if (!$result) {
                return false;
            }
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function move_to_group($db, $group_from = 0, $group_to = 0)
    {
        if ($group_from && $group_to && ($group_from != $group_to)) {
            $this->sql_query = "select price_plan_id, auction_price_plan_id from " . $this->classified_groups_table . " where group_id = " . $group_to;
            $result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query.'<br>';
            if (!$result) {
                if ($this->debug_groups) {
                    echo $this->sql_query . "<br>\n";
                }
                $this->error_message = $this->internal_error_message;
                return false;
            } else {
                $show_group = $result->FetchRow();
            }
            $result = $this->db->Execute("update geodesic_registration_session set registration_group = '" . $group_to . "' where registration_group = '" . $group_from . "'");
            if (false === $result) {
                $this->error_message = $this->internal_error_message;
                return false;
            }

            $this->sql_query = "update " . $this->user_groups_price_plans_table . " set
				group_id = " . $group_to . ",
				price_plan_id = " . $show_group["price_plan_id"] . ",
				auction_price_plan_id = " . $show_group["auction_price_plan_id"] . "
				where group_id = " . $group_from;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } else {
                return true;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function move_to_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function move_group_price_plan_form($db, $group_id)
    {
        if (!$group_id) {
            return false;
        }
        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
        // Set title and text for tooltip
        $this->body .= "Text[1] = [\"move current group members to new price plan also\", \"There are currently live members in this group. Choose \\\"yes\\\" from the selection to move the current users to the new price plan you choose above. Choose \\\"no\\\" to leave the current group members on the price plans they currently have.\"]\n";

        //".$this->show_tooltip(1,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
        $this->body .= "</script>";

        $this->sql_query = "SELECT * FROM " . $this->classified_groups_table . " WHERE group_id = $group_id";
        $group_name = $this->get_group_name($db, $group_id);
        $this->body .= "<div class='page-title1'>User Group: <span class='group-color'>" . $group_name . "</span></div>";

        $this->body .= "<fieldset><legend>Edit Price Plan</legend>";

        $group_result = $this->db->Execute($this->sql_query);
        //echo $this->sql_query."<br>\n";
        if (!$group_result) {
            $this->error_message = $this->internal_error_message;
            return false;
        } elseif ($group_result->RecordCount() == 1) {
            $show_group = $group_result->FetchRow();
            //$this->title = "Users / User Groups > Edit Details > Edit Price Plan";
            $this->description = "Use the form below to specify a new Price Plan for this User Group.";

            //get all price plans for dropdown boxes
            $this->sql_query = "SELECT * FROM $this->price_plan_table ORDER BY name";

            $price_plan_result = $this->db->Execute($this->sql_query);
            if (!$price_plan_result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($price_plan_result->RecordCount() > 0) {
                while ($show = $price_plan_result->FetchRow()) {
                    if ($show["type_of_billing"] == 1) {
                        if ((geoMaster::is('auctions')) && $show["applies_to"] == 2) {
                            if ($show["price_plan_id"] == $show_group["auction_price_plan_id"]) {
                                $auc_fee_based_options .= "
									<option value=" . $show["price_plan_id"] . " selected><b>" . $show["name"] . " - (current)</b></option>";
                                $selected_price_plan_type = 0;
                            } else {
                                $auc_fee_based_options .= "
									<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                            }
                        }
                        if (geoMaster::is('classifieds') && $show["applies_to"] == 1) {
                            if ($show["price_plan_id"] == $show_group["price_plan_id"]) {
                                $class_fee_based_options .= "
									<option value=" . $show["price_plan_id"] . " selected><b>" . $show["name"] . " - (current)</b></option>";
                                $selected_price_plan_type = 0;
                            } else {
                                $class_fee_based_options .= "
									<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                            }
                        }
                    } elseif ($show["type_of_billing"] == 2) {
                        $this->sql_query = "
							SELECT * FROM
								$this->classified_subscription_choices_table
							WHERE
								price_plan_id = " . $show["price_plan_id"];
                        $sub_result = $this->db->Execute($this->sql_query);
                        if (!$sub_result) {
                            $this->error_message = $this->internal_error_message;
                            return false;
                        } elseif ($sub_result->RecordCount() > 0) {
                            if ((geoMaster::is('auctions')) && $show["applies_to"] == 2) {
                                if ($show["price_plan_id"] == $show_group["auction_price_plan_id"]) {
                                    $auc_sub_based_options .= "
										<option value=" . $show["price_plan_id"] . " selected><b>" . $show["name"] . " - (current)</b></option>";
                                    $selected_price_plan_type = 1;
                                } else {
                                    $auc_sub_based_options .= "
										<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                                }
                            }
                            if ((geoMaster::is('classifieds')) && $show["applies_to"] == 1) {
                                if ($show["price_plan_id"] == $show_group["price_plan_id"]) {
                                    $class_sub_based_options .= "
										<option value=" . $show["price_plan_id"] . " selected><b>" . $show["name"] . " - (current)</b></option>";
                                    $selected_price_plan_type = 1;
                                } else {
                                    $class_sub_based_options .= "
										<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                                }
                            }
                        }
                    }
                }

                $this->additional_head_html .= "
						<script>
							function show_types()
							{
								if(jQuery('#fee_plan_type').prop('checked')) {
									jQuery('#fee').show();
									jQuery('#sub').hide();
								} else if (jQuery('#sub_plan_type').prop('checked')) {
									jQuery('#sub').show();
									jQuery('#fee').hide();
								}
							}
							jQuery(document).ready(function(){ show_types(); });
							function check_sub_period_choice()
							{
								if (jQuery('#sub_plan_type').prop('checked') &&
									!jQuery('#choice_1').prop('checked') &&
									!jQuery('#choice_2').prop('checked'))
								{
									alert('You must choose which price plan\'s subscription periods will overwrite the other.');
									return false;
								}
								else
								{
									return true;
								}
							}

						</script>";

                $canSubscription = geoAddon::getInstance()->isEnabled('subscription_pricing');

                $this->body .= "
				<form onsubmit='return check_sub_period_choice()' action=index.php?mc=users&page=users_group_price_edit&g=" . $group_id . " class='form-horizontal form-label-left' method=post name=price_plans>
				<div class='x_content'>
				<table cellpadding=3 cellspacing=0 border=0 align=center width=100%>

					<div class='form-group'>
					<label class='control-label col-md-4 col-sm-4 col-xs-12'>Price Plan Type: </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'>
						<input id='fee_plan_type' type=radio name=k[price_plan_type] value=1 onclick='javascript:show_types();'" . (!$selected_price_plan_type ? " checked='checked'" : "") . "> Fee-Based<br />
						<input id='sub_plan_type' type=radio name=k[price_plan_type] value=2 onclick='javascript:show_types();'" . ($selected_price_plan_type ? " checked='checked'" : "") . (!$canSubscription ? " disabled='disabled'" : "") . "> Subscription-Based" . (!$canSubscription ? " - <span style='color: red; font-weight: bold;'>Requires the Subscription Pricing addon</span>" : "") . "
					  </div>
					</div>

					<tr id=fee style='display:none;'>
						<td colspan=\"100%\">
							<table align=center border=0>";


                if (geoMaster::is('auctions')) {
                    $this->body .= "
										<div class='form-group'>
										<label class='control-label col-md-4 col-sm-4 col-xs-12'>Auction Price Plan: </label>
										  <div class='col-md-6 col-sm-6 col-xs-12'>
											<select name=k[new_auc_price_plan_fee] class='form-control col-md-7 col-xs-12'>
												$auc_fee_based_options
											</select>
										  </div>
										</div>";
                }
                if (geoMaster::is('classifieds')) {
                    $this->body .= "
										<div class='form-group'>
										<label class='control-label col-md-4 col-sm-4 col-xs-12'>Classified Price Plan: </label>
										  <div class='col-md-6 col-sm-6 col-xs-12'>
											<select name=k[new_class_price_plan_fee] class='form-control col-md-7 col-xs-12'>
												$class_fee_based_options
											</select>
										  </div>
										</div>";
                }
                $this->body .= "
								</table>
							</td>
						</tr>";
                if (geoPC::is_ent() || geoPC::is_premier()) {
                    $this->body .= "
						<tr id=sub style='display:none;'>
							<td colspan=\"100%\">
								<table align=center border=0>";
                    if (geoMaster::is('auctions')) {
                        if (strlen($auc_sub_based_options) > 0) {
                            $this->body .= "
											<div class='form-group'>
											<label class='control-label col-md-4 col-sm-4 col-xs-12'>Auction Price Plan: </label>
											  <div class='col-md-6 col-sm-6 col-xs-12'>
												<select name=k[new_auc_price_plan_sub] class='form-control col-md-7 col-xs-12'>
													$auc_sub_based_options
												</select>
												<input onclick=\"javascript:alert('NOTE: This will permanently overwrite subscription periods belonging to the classified subscription chosen in the pull down box below');\"
												id=choice_1 name=k[sub_period_choice] type=radio value=1> Use this price plan's subscription periods
											  </div>
											</div>";
                        } else {
                            $this->body .= "
											<tr>
												<td align=left valign=center class=medium_error_font>
													You currently do not have an auction subscription price plan that has at least one subscription period.
												</td>
											</tr>";
                        }
                    }
                    if (geoMaster::is('classifieds')) {
                        if (strlen($class_sub_based_options) > 0) {
                            $this->body .= "
										<div class='form-group'>
										<label class='control-label col-md-4 col-sm-4 col-xs-12'>Classified Price Plan: </label>
										  <div class='col-md-6 col-sm-6 col-xs-12'>
											<select name=k[new_class_price_plan_sub] class='form-control col-md-7 col-xs-12'>
												$class_sub_based_options
											</select>
											<input onclick=\"javascript:alert('NOTE: This will permanently overwrite subscription periods belonging to the auction subscription chosen in the pull down box above');\"
											id=choice_2 name=k[sub_period_choice] type=radio value=2> Use this price plan's subscription periods.
										  </div>
										</div>";
                        } else {
                            $this->body .= "
											<tr>
												<td align=left valign=center class=medium_error_font>
													You currently do not have a classified subscription price plan that has at least one subscription period.
												</td>
											</tr>";
                        }
                    }

                    $this->body .= "</table>
									<script type='text/javascript'>
										" . ((!$canSubscription) ? "$('fee_plan_type').checked=true" : "") . "
										Event.observe(window,'load',show_types );
									</script>
								</td>
							</tr>";
                }
                if (!$this->admin_demo()) {
                    $this->body .= "
						<tr>
							<td align=center colspan=2><input type=submit value=\"Save\" name='auto_save'></td>
						</tr>";
                }
                $this->body .= "
					</table>
					</form>";
            }

            $this->body .= '</fieldset>';

            $this->body .= "
				<div style='padding: 5px;'><a href=index.php?mc=users&page=users_group_edit&c=$group_id class='back_to'>
				<i class='fa fa-backward'></i> Back to Edit Details for <b>" . $show_group["name"] . "</b></a></div>
			";
            return true;
        } else {
            return false;
        }
    } //end of function move_group_price_plan_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function move_group_price_plan($db, $group_id, $group_info)
    {
        if (!$group_info || !$group_id) {
            return false;
        }

        if ($group_info['price_plan_type'] == 2 && !geoAddon::getInstance()->isEnabled('subscription_pricing')) {
            geoAdmin::m('Invalid Price Plan Type.', geoAdmin::ERROR);
            return false;
        }

        if (geoMaster::is('auctions')) {
            $new_auc_price_plan_id = $group_info["price_plan_type"] == 1 ? $group_info["new_auc_price_plan_fee"] : $group_info["new_auc_price_plan_sub"];
        }
        if (geoMaster::is('classifieds')) {
            $new_class_price_plan_id = $group_info["price_plan_type"] == 1 ? $group_info["new_class_price_plan_fee"] : $group_info["new_class_price_plan_sub"];
        }



        // grab group data
        $this->sql_query = "SELECT * FROM " . $this->classified_groups_table . " WHERE group_id = $group_id";
        if ($this->debug_groups) {
            echo $this->sql_query . "<br>\n";
        }
        $group_result = $this->db->Execute($this->sql_query);
        if ($group_result === false) {
            return false;
        }
        if ($group_result->RecordCount() != 1) {
            return false;
        }
        //the group exists
        $show_group = $group_result->FetchRow();
        //set loop counter
        $num_products = (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? 2 : 1; // One iteration per product (classifieds/auctions)
        for ($i = $num_products; $i > 0; $i--) {
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                //initialize variables and then switch 2nd time through the loop
                $price_plan_column = ($i == 2) ? "price_plan_id" : "auction_price_plan_id";
                $other_price_plan_column = ($i == 2) ? "auction_price_plan_id" : "price_plan_id";
            } else {
                //only gonna do this loop once, so we need to specify which product type we're using
                if (geoMaster::is('auctions') && !geoMaster::is('classifieds')) {
                    $price_plan_column = "auction_price_plan_id";
                } elseif (geoMaster::is('classifieds')) {
                    $price_plan_column = "price_plan_id";
                }
                $other_price_plan_column = "";
            }
            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                if (!$group_info["price_plan_type"]) {
                    return false;
                }
                if ($group_info["price_plan_type"] == 2) {
                    // Subscription-based
                    if (!$other_price_plan_column) {
                        return false;
                    }
                    $new_id = "";
                    if ($price_plan_column == "auction_price_plan_id" && $group_info["sub_period_choice"] == 1) {
                        $this->sql_query = "SELECT * FROM $this->classified_subscription_choices_table
							WHERE
								price_plan_id = $new_auc_price_plan_id";
                        $new_id = $new_auc_price_plan_id;
                        $other_id = $new_class_price_plan_id;
                    } elseif ($price_plan_column == "price_plan_id" && $group_info["sub_period_choice"] == 2) {
                        $this->sql_query = "SELECT * FROM $this->classified_subscription_choices_table
							WHERE
								price_plan_id = $new_class_price_plan_id";
                        $new_id = $new_class_price_plan_id;
                        $other_id = $new_auc_price_plan_id;
                    }
                    if (strlen($new_id) > 0) {
                        $new_sub_result = $this->db->Execute($this->sql_query);
                        if ($new_sub_result === false) {
                            return false;
                        }
                        $this->sql_query = "DELETE FROM $this->classified_subscription_choices_table WHERE
							price_plan_id = $other_id";
                        if ($this->db->Execute($this->sql_query) === false) {
                            return false;
                        }

                        if ($new_sub_result->RecordCount() > 0) {
                            while ($new_subscription = $new_sub_result->FetchRow()) {
                                $this->sql_query = "INSERT INTO $this->classified_subscription_choices_table
								(display_value,value,amount,price_plan_id)
								VALUES
								(
									'" . $new_subscription["display_value"] . "',
									'" . $new_subscription["value"] . "',
									'" . $new_subscription["amount"] . "',
									'" . $other_id . "'
								)";
                                if ($this->db->Execute($this->sql_query) === false) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
            if ($price_plan_column == "price_plan_id") {
                $this->sql_query = "UPDATE $this->classified_groups_table
					SET
						price_plan_id = $new_class_price_plan_id
					WHERE
						group_id = $group_id";

                $update_group_result = $this->db->Execute($this->sql_query);
                if ($update_group_result === false) {
                    return false;
                }

                //move the current users to the new price plan
                $this->sql_query = "UPDATE $this->user_groups_price_plans_table
					SET
						price_plan_id = $new_class_price_plan_id
					WHERE group_id = $group_id";
            } elseif ($price_plan_column == "auction_price_plan_id") {
                $this->sql_query = "UPDATE $this->classified_groups_table
					SET
						auction_price_plan_id = $new_auc_price_plan_id
					WHERE
						group_id = $group_id";

                $update_group_result = $this->db->Execute($this->sql_query);
                if ($update_group_result === false) {
                    return false;
                }

                //move the current users to the new price plan
                $this->sql_query = "UPDATE $this->user_groups_price_plans_table
					SET
						auction_price_plan_id = $new_auc_price_plan_id
					WHERE group_id = $group_id";
            }
            $update_current_result = $this->db->Execute($this->sql_query);
            if ($update_current_result === false) {
                return false;
            }
        }
        return true;
    } //end of function update_group_info

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function group_form($db, $group_id = 0)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $this->body .= "<script type=\"text/javascript\">";
        // Set title and text for tooltip
        $this->body .= "
			Text[1] = [\"Affiliate Privileges\", \"When checked this will give affiliate privileges to users in this group.  Affiliate privileges allow users of this group to have access to the affiliate page.  This is a special page where (if linked to properly) a page will be displayed with only their own listings.  There will be no direct link to the main site unless you place one within the template.  The search within this section will only return listings from this user.  To find the direct link to the affiliate&#039;s site for this user you must find the user you wish to send the link to through the list users or search users part of the admin and display that users information.  The link to the affiliate section will be with the other group information for that user.\"]\n
			Text[2] = [\"Affiliate URL Page\", \"Choose a template used on this group affiliate site within the home page using this language.  If no template is chosen, the default template set in the PAGES > BROWSING PAGES > BROWSE CATEGORIES will be used.  Enter all templates through the template administration.\"]\n
			Text[3] = [\"Affiliate URL Browsing Page\", \"Choose a template used on this group affiliate site within the secondary browsing page using this language.  If no template is chosen for this category the default template set in the PAGES > BROWSING PAGES > BROWSE CATEGORIES will be used.  Enter all templates through the template administration.\"]\n
			Text[4] = [\"Listing Display Details\", \"Choose a template used on this group affiliate site within the listing using this language.  If no template is chosen for this category, the default template set in the PAGES > BROWSING PAGES > AD DISPLAY PAGE will be used.  Enter all templates through the template administration.\"]\n
			Text[5] = [\"Display Listing Extra Question Template\", \"Choose a template used on this group affiliate site within the listing display template to display the extra category specific questions using this language.  If no template is chosen for this category the default template set in the PAGES > BROWSING PAGES > EXTRA QUESTIONS will be used.  Enter all templates through the template administration.\"]\n
			Text[6] = [\"Display Listing Checkbox Template\", \"Choose a template used on this group affiliate site within the listing display template to display the checkbox based category specific questions using this language.  If no template is chosen for this category the default template set in the PAGES > BROWSING PAGES > EXTRA CHECKBOXES will be used.  Enter all templates through the template administration.\"]\n
			Text[10] = [\"Create Listing\", \"Uncheck this box to remove New Listing, My Active Listings, and My Expired Listings from this usergroup's User Management Home Page.\"]\n
			Text[11] = [\"Messaging\", \"Uncheck this box to remove My Messages and Message Settings from this usergroup's User Management Home Page.\"]\n
			Text[12] = [\"Favorites\", \"Uncheck this box to remove My Favorites from this usergroup's User Management Home Page.\"]\n
			Text[13] = [\"Filters\", \"Uncheck this box to remove My Listing Filters from this usergroup's User Management Home Page.\"]\n
			Text[14] = [\"Black List/Invited List\", \"Uncheck this box to remove Black Listed Buyers and Invited List of Buyers from this usergroup's User Management Home Page.\"]\n
			Text[15] = [\"Feedback\", \"Uncheck this box to remove Feedback Management from this usergroup's User Management Home Page.\"]\n
			Text[16] = [\"Default Setting\", \"The Default Setting is used for...\"]\n
			";
        if ($this->db->get_site_setting('use_admin_wysiwyg')) {
            $this->body .= "
			Text[7] = [\"Splash Page During Registration\", \"Enter below what you wish to display after a user has registered into this group.  This html will be displayed within the registration page template and will appear once this user has entered the registration code for this group.  This will not display if the user is \\\"defaulted\\\" into this group by not entering a registration code.\"]\n
			Text[8] = [\"Splash Page During Listing Process\", \"Enter below what you wish to display to a user of this group when they click the link to place a new listing.  This will be displayed within the \\\"choose category\\\" page template before the user chooses a category to place their listing in.\"]\n
			Text[9] = [\"\\\"Sponsored by\\\" HTML placed on Listing Display Page\", \"Enter below what you wish to display within the listing display page of sellers belonging to to this group. This can be any message you wish to attach to users listings placed by sellers within this group.  You must place the {\$sponsored_by} tag within the listing display template for this code to display.  On sellers that do not have sponsored by html attached to their group nothing will appear.\"]\n
			";
        } else {
            $this->body .= "
			Text[7] = [\"Splash Page During Registration\", \"Cut and paste html you wish to display after a user has registered into this group.  This html will be displayed within the registration page template and will appear once this user has entered the registration code for this group.  This will not display if the user is \\\"defaulted\\\" into this group by not entering a registration code.\"]\n
			Text[8] = [\"Splash Page During Listing Process\", \"Cut and paste html you wish to display to a user of this group when they click the link to place a new ad.  This html will be displayed within the \\\"choose category\\\" page template before the user chooses a category to place their listing in.\"]\n
			Text[9] = [\"\\\"Sponsored by\\\" HTML placed on Listing Display Page\", \"Cut and paste html you wish to display within the listing display page of sellers belonging to to this group. This can be any message you wish to attach to users listings placed by sellers within this group.  You must place the {\$sponsored_by} tag within the listing display template for this code to display.  On sellers that do not have sponsored by html attached to their group nothing will appear.\"]\n
			";
        }


        //".$this->show_tooltip(9,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
        $this->body .= "</script>";
        $group_id = (int)$group_id;

        if ($group_id) {
            $this->sql_query = "select * from " . $this->db->geoTables->classified_groups_table . " where group_id = " . $group_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
            } else {
                return false;
            }

            if ($show) {
                //$this->title = "Users / User Groups > Edit Details";
                $tpl_vars = array();

                $tpl_vars['group'] = $show;
                $tpl_vars['group_id'] = $group_id;
                $tpl_vars['is_ent'] = geoPC::is_ent();
                $tpl_vars['is_premier'] = geoPC::is_premier();

                //allow addons to add settings to be saved in user group details page
                $addon_vars = array ('group_id' => $group_id, 'this' => $this);
                $tpl_vars['addonSettings'] = geoAddon::triggerDisplay('Admin_Group_Management_edit_group_display', $addon_vars);

                if (geoPC::is_ent()) {
                    $sql = "SELECT * FROM " . geoTables::questions_table . " WHERE `group_id` = $group_id";
                    $tpl_vars['questions'] = $this->db->Execute($sql);
                }

                $tpl_vars['admin_msgs'] = geoAdmin::m();

                $this->description = "Edit this User Group's details through this admin tool.  Make your changes then click the \"save\" button at the bottom of the form.";
                $this->row_count = 0;

                //TODO: add link here
                if (geoPC::is_ent() || geoPC::is_premier()) {
                    //figure out if using group payment gateway settings
                    geoPaymentGateway::setGroup($group_id);
                    $this->body .= "
			<fieldset>
				<legend><i class='fa fa-credit-card'></i> Group Specific Payment Gateway Settings</legend>
				<div class='center'>";
                    if ($group_id == geoPaymentGateway::getGroup()) {
                        //cool, using group specific payment gateway settings
                        $this->body .= "
				<i class='fa fa-pencil'></i> Edit: <a href='?page=payment_gateways&group=$group_id'>Group-Specific Settings</a><br /><br />
				<i class='fa fa-exchange'></i> Switch To: <a href='?page=users_group_edit&c=$group_id&pg=turn_off&auto_save=2' class='lightUpLink'>Site-Wide Settings</a>";
                    } else {
                        $this->body .= "
				Currently Using: <a href='?page=payment_gateways'>Site-Wide Settings</a><br /><br />
				<i class='fa fa-exchange'></i> Switch To: <a href='?page=users_group_edit&c=$group_id&pg=turn_on&auto_save=2' class='lightUpLink'>Group-Specific Settings</a>";
                    }
                    $this->body .= "
				</div>
				</fieldset>";
                    $this->row_count++;
                }
                $default_auction_price_plan = $this->get_price_plan($db, $show["auction_price_plan_id"]);
                $default_class_price_plan = $this->get_price_plan($db, $show["price_plan_id"]);
                if (geoPC::is_ent() || geoPC::is_premier()) {
                    $this->body .= "
			<fieldset>
				<legend>Price Plan Attachments</legend>
				<div class='x_content'>
				<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">
					<tr>
						<td colspan=\"100%\">
							<table valign=center cellspacing=0 cellpadding=0 width=\"100%\">";

                    $this->body .= "
								<tr>
									<td colspan=100% class=col_hdr align=center><b>Price Plan" . (geoPC::is_ent() ? 's' : '') . " Attached to \"" . $show["name"] . "\"</b></td>
								</tr>
								<tr>";
                    if (geoMaster::is('classifieds')) {
                        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                            $this->body .= "
									<td style='vertical-align: top;' width=\"50%\">
										<table cellpadding=0 width=\"100%\">
											<tr>
												<td align=center class=sec_hdr2 colspan=3><b>Classifieds</b></td>
											</tr>";
                        } else {
                            $this->body .= "
									<td>
										<table cellpadding=0 width=\"100%\">";
                        }

                        $this->body .= "
										<tr>
											<td align=left class=medium_font width=\"90%\"><b>Default:</b></td>
											<td style='text-align: center;'>
												" . geoHTML::addButton('Edit', "index.php?mc=users&page=users_group_price_edit&g=" . $group_id) . "
											</td>
										</tr>
										<tr>
											<td colspan='2' align=left valign=top class=medium_font>&nbsp;&nbsp;&nbsp;
														" . $default_class_price_plan["name"] . "
											</td>
										</tr>";
                        if (geoAddon::getInstance()->isEnabled('enterprise_pricing')) {
                            $this->body .= "<tr style='display:" . (($default_class_price_plan["type_of_billing"] == 2) ? "none" : "") . "'>
											<td align=left class=medium_font width=\"90%\"><b>Additional:</b></td>
											<td style='text-align: center;'>
												" . geoHTML::addButton('Edit', "index.php?mc=users&page=users_group_add_plan&g=" . $group_id . "&t=1") . "
											</td>
										</tr>";

                            $this->sql_query  = "SELECT price_plan_id FROM " . $this->attached_price_plans . " WHERE
							group_id = " . $group_id . " AND
							applies_to = 1";
                            $addition_class_price_plan_result = $this->db->Execute($this->sql_query);
                            if (!$addition_class_price_plan_result) {
                                $this->error_message = $this->internal_error_message;
                                return false;
                            } else {
                                if ($addition_class_price_plan_result->RecordCount() > 0) {
                                    //display extra attached price plans
                                    while ($show_price_plan = $addition_class_price_plan_result->FetchRow()) {
                                        $price_plan_name = $this->get_price_plan_name($db, $show_price_plan["price_plan_id"]);
                                        $this->body .= "
											<tr style='display:" . (($default_class_price_plan["type_of_billing"] == 2) ? "none" : "") . "'>
												<td align=left valign=top class=medium_font colspan=2>&nbsp;&nbsp;&nbsp;
														" . $price_plan_name . "
												</td>
											</tr>";
                                    }
                                } else {
                                    $this->body .= "
											<tr style='display:" . (($default_class_price_plan["type_of_billing"] == 2) ? "none" : "") . "'>
												<td align=left valign=top class=medium_error_font colspan=2>&nbsp;&nbsp;&nbsp;
													none
												</td>
											</tr>";
                                }
                            }
                        }

                        $this->body .= "</table></td>";
                    }
                    if (geoMaster::is('auctions')) {
                        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                            $this->body .= "
									<td style='vertical-align: top;' width=\"50%\">
										<table cellpadding=0 width=\"100%\">
											<tr>
												<td align=center class=sec_hdr2 colspan=2><b>Auctions</b></td>
											</tr>";
                        } else {
                            $this->body .= "
									<td>
										<table cellpadding=0 width=\"100%\">";
                        }

                        $this->body .= "
										<tr>
											<td align=left class=medium_font width=\"90%\"><b>Default:</b></td>
											<td>
												" . geoHTML::addButton('Edit', "index.php?mc=users&page=users_group_price_edit&g=" . $group_id) . "
											</td>
										</tr>
										<tr>
											<td colspan=2 align=left valign=top class=medium_font>&nbsp;&nbsp;&nbsp;
												" . $default_auction_price_plan["name"] . "
											</td>
										</tr>";
                        if (geoAddon::getInstance()->isEnabled('enterprise_pricing')) {
                            $this->body .= "
										<tr style='display:" . (($default_auction_price_plan["type_of_billing"] == 2) ? "none" : "") . "'>
											<td align=left class=medium_font width=\"90%\"><b>Additional:</b></td>
											<td>
												" . geoHTML::addButton('Edit', "index.php?mc=users&page=users_group_add_plan&g=" . $group_id . "&t=2") . "
											</td>
										</tr>";
                            $this->sql_query  = "SELECT price_plan_id FROM " . $this->attached_price_plans . " WHERE
							group_id = " . $group_id . " AND
							applies_to = 2";
                            $addition_auction_price_plan_result = $this->db->Execute($this->sql_query);
                            if (!$addition_auction_price_plan_result) {
                                $this->error_message = $this->internal_error_message;
                                return false;
                            } else {
                                if ($addition_auction_price_plan_result->RecordCount() > 0) {
                                    //display extra attached price plans
                                    while ($show_price_plan = $addition_auction_price_plan_result->FetchRow()) {
                                        $price_plan_name = $this->get_price_plan_name($db, $show_price_plan["price_plan_id"]);
                                        $this->body .= "
											<tr style='display:" . (($default_auction_price_plan["type_of_billing"] == 2) ? "none" : "") . "'>
												<td align=left valign=top class=medium_font colspan=2>&nbsp;&nbsp;&nbsp;
															" . $price_plan_name . "
												</td>
											</tr>";
                                    }
                                } else {
                                    $this->body .= "
											<tr style='display:" . (($default_auction_price_plan["type_of_billing"] == 2) ? "none" : "") . "'>
												<td align=left valign=top class=medium_error_font colspan=2>&nbsp;&nbsp;&nbsp;
													none
												</td>
											</tr>";
                                }
                            }
                        }

                        $this->body .= "</table></td>";
                    }
                    $this->body .= "</tr>";


                    // Group registration freebies
                    $addons = geoAddon::getInstance();
                    if ($addons->isEnabled('subscription_pricing') || $addons->isEnabled('account_balance')) {
                        //nothing to show behind this button without one of those addons
                        $this->body .= "
						<tr>
							<td colspan=100% align=center>
								<div style='margin-top: 10px;'><a href=index.php?mc=users&page=users_group_registration&g=" . $group_id . " class='mini_button'>Group Price Plan Registration Specifics</a></div>
							</td>
						</tr>";
                    }


                    $this->body .= "
							</table>
						</td>
					</tr>
				</table>
				<div style=\"margin-left: 5px; margin-right: auto; text-align: right;\"><input type=\"submit\" name=\"auto_save\" class=\"mini_button\" value=\"Quick Save\" onClick=\"return (validate_inputs()); \"></div>
			</div>
			</fieldset>";
                }
                if (geoPC::is_ent()) {
                    // Registration splash pages
                    if ($this->db->get_site_setting("use_admin_wysiwyg")) {
                        //add wysiwyg text to header.
                        $template_code_text = ' Edit with WYSIWYG Editor (<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">Add/Remove editor</a>)<br />';
                        $wysiwyg = true;
                        require_once('admin_wysiwyg_config.php');
                        $this->head_html .= wysiwyg_configuration::getHeaderText('textManager', false);
                    } else {
                        //wysiwyg turned off.
                        $wysiwyg = false;
                        $template_code_text = ' Edit with WYSIWYG OFF  (<a href="index.php?mc=admin_tools_settings&page=wysiwyg_general_config">Change Editor Settings</a>)<br />
		Cut and paste your html template into the space below.';
                    }
                    $this->body .= "
			<fieldset id='SplashPages'>
				<legend>Spash Pages HTML / 'Sponsored By' HTML</legend>
				<div>
				<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">
					<tr>
						<td align=center valign=top id=\"splash-registration\" colspan=\"100%\">
							" . $this->show_tooltip(7, 1) . " Splash Page during Registration - $template_code_text
						</td>
					</tr>
					<tr>
						<td class=medium_font colspan=\"100%\">
							<textarea name=d[registration_splash_code] style=\"width:100%\" cols=50 rows=20 class=\"textManager\">"
                                . geoString::specialChars(geoString::fromDB($show["registration_splash_code"])) .
                            "</textarea><br /><br />
						</td>
					</tr>
					<tr>
						<td align=center valign=top id=\"splash-listing\" colspan=\"100%\">
							" . $this->show_tooltip(8, 1) . " Splash Page during Listing Process - $template_code_text
						</td>
					</tr>
					<tr>
						<td class=medium_font colspan=\"100%\">
							<textarea name=d[place_an_ad_splash_code]  style=\"width:100%\" cols=50 rows=20 class=\"textManager\">"
                                . geoString::specialChars(geoString::fromDB($show["place_an_ad_splash_code"])) .
                            "</textarea><br /><br />
						</td>
					</tr>
					<tr>
						<td align=center valign=top id=\"sponsored-by\" colspan=\"100%\">
							" . $this->show_tooltip(9, 1) . " \"Sponsored by\" HTML placed on Listing Display Page - $template_code_text
						</td>
					</tr>
					<tr>
						<td class=medium_font colspan=\"100%\">
							<textarea name=d[sponsored_by_code]  style=\"width:100%\" cols=50 rows=20 class=\"textManager\">"
                                . geoString::specialChars(geoString::fromDB($show["sponsored_by_code"])) .
                            "</textarea><br />
						</td>
					</tr>
				</table>
				<div style=\"margin-left: 5px; margin-right: auto; text-align: right;\"><input type=\"submit\" name=\"auto_save\" value=\"Quick Save\" class=\"mini_button\" onClick=\"return (validate_inputs()); \"></div>
				</div>
			</fieldset>";
                    //idev


                    $bitmask = $show['restrictions_bitmask'];
                    $restrictions = array();
                    $restrictions['create_listing'] = (($bitmask & 1) == 1) ? 'checked="checked"' : '';
                    $restrictions['messaging'] = (($bitmask & 2) == 2) ? 'checked="checked"' : '';
                    $restrictions['favorites'] = (($bitmask & 4) == 4) ? 'checked="checked"' : '';
                    $restrictions['filters'] = (($bitmask & 8) == 8) ? 'checked="checked"' : '';
                    $restrictions['black_white'] = (($bitmask & 16) == 16) ? 'checked="checked"' : '';
                    $restrictions['feedback'] = (($bitmask & 32) == 32) ? 'checked="checked"' : '';
                    $this->body .= "
			<fieldset id='UserRestric'>
				<legend>User Restrictions</legend>
				<div class='x_content'>

				<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">

					<p>You can use this section to allow or disallow this user group access to certain features. Unchecking a box here will remove the appropriate functionality from this user group's My Account Home Page.</p>
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Create Listing: " . $this->show_tooltip(10, 1) . " </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'><input type=\"checkbox\" name=\"d[restrict_1]\" " . $restrictions['create_listing'] . " />
					  </div>
					</div>
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Messaging: " . $this->show_tooltip(11, 1) . " </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'><input type=\"checkbox\" name=\"d[restrict_2]\" " . $restrictions['messaging'] . " />
					  </div>
					</div>
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Favorites: " . $this->show_tooltip(12, 1) . " </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'><input type=\"checkbox\" name=\"d[restrict_4]\" " . $restrictions['favorites'] . " />
					  </div>
					</div>
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Filters: " . $this->show_tooltip(13, 1) . " </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'><input type=\"checkbox\" name=\"d[restrict_8]\" " . $restrictions['filters'] . " />
					  </div>
					</div>
					";
                    if (geoMaster::is('auctions')) {
                        $this->body .= "
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Black List/Invited List: " . $this->show_tooltip(14, 1) . " </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'><input type=\"checkbox\" name=\"d[restrict_16]\" " . $restrictions['black_white'] . " />
					  </div>
					</div>
					<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Feedback: " . $this->show_tooltip(15, 1) . " </label>
					  <div class='col-md-6 col-sm-6 col-xs-12'><input type=\"checkbox\" name=\"d[restrict_32]\" " . $restrictions['feedback'] . " />
					  </div>
					</div>
					";
                    }
                    $this->body .= "
				</table>
				<div style=\"margin-left: 5px; margin-right: auto; text-align: right;\"><input type=\"submit\" name=\"auto_save\" value=\"Quick Save\" class=\"mini_button\" onClick=\"return (validate_inputs()); \"></div>
				</div>
			</fieldset>";
                }

                $ebayUtil = geoAddon::getUtil('ebay');
                if ($ebayUtil) {
                    $sql = "select plan_id from geodesic_ebay_groups_price_plans where group_id = " . $group_id;
                    $result = $this->db->Execute($sql);
                    if ($result) {
                        $planId = $result->FetchRow();
                    }
                    $planId = (is_array($planId)) ? $planId["plan_id"] : 0;

                    $this->row_count++;
                    $this->body .= "
			<fieldset>
				<legend>eBay Privileges</legend>
				<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">
					<tr bgcolor=000066>
						<td colspan=2 class=medium_font_light align=center><b>eBay Privileges</b></td>
					</tr>
					<tr class=" . $this->get_row_color() . ">
						<td align=right width=50% class=medium_font>
							<b>eBay Price Plan:</b>
						</td>
						<td width=50% valign=top class=medium_font>
							<select name=d[ebay_priceplan]>
								<option value='0'>none</option>";
                    foreach ($ebayUtil->getPricePlans() as $id => $plan) {
                        $this->body .= "
								<option value='$id' " . (($planId == $id) ? "selected" : "") . ">" . $plan['name'] . "</option>";
                    }
                    $this->body .= "
							</select>
						</td>
					</tr>
				</table>
				<div style=\"margin-left: 5px; margin-right: auto; text-align: right;\"><input type=\"submit\" name=\"auto_save\" value=\"Quick Save\" onClick=\"return (validate_inputs()); \"></div>
			</fieldset>";
                }

                if (!$this->admin_demo()) {
                    $this->body .= "
			<table valign=center cellspacing=0 cellpadding=3 width=\"100%\">
				<tr>
					<td colspan=2 align=center>
						<div style='text-align: center;'><input type=submit name='auto_save' value=\"Save All\" onClick=\"return (validate_inputs()); \"></div>
					</td>
				</tr>";
                    $this->additional_head_html .= "
<script language=\"Javascript\">
function validate_inputs()
{
	if (document.getElementById('d[name]').value==''){ alert ('The name field is required for new groups.'); return false; } return true;
}
</script>
";
                }
                $this->body .= "
			</table>
		</form>";
                geoView::getInstance()->setBodyTpl('settings/edit_group.tpl')
                    ->setBodyVar($tpl_vars);
                return true;
            } else {
                if ($this->debug_groups) {
                    echo "<br><font color=red>LINE " . __LINE__ . "<BR>ERROR IN QUERY - " . $this->sql_query . "</font><BR>";
                }
                return false;
            }
        } else {
            if ($this->debug_groups) {
                echo "<br><font color=red>LINE " . __LINE__ . "<BR>ERROR - No Group Id</font><BR>";
            }
            return false;
        }
    } //end of function group_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function new_group_form()
    {
        if (!geoPC::is_ent() && !geoPC::is_premier()) {
            $this->body .= "
				<span class=medium_font>
						The ability to create additional User Groups is not a feature included in this
						edition of the software. If you determine that you are in need of additional
						User Groups, please consider <a target=\"blank\" href=\"http://www.geodesicsolutions.com/products/index.htm\">upgrading</a> your software package.
				</span>";
            return true;
        }
        //Lets remember any values entered, in case there was an error.
        if (isset($_POST['d'])) {
            foreach ($_POST['d'] as $key => $value) {
            //to keep user inputed tags from parsing in the HTML
                if (!is_array($value)) {
                    $d_vars [$key] = geoString::specialChars($value);
                }
            }
            $restore_values = true;
        } else {
            $restore_values = false;
        }

        $this->body .= "<SCRIPT language=\"JavaScript1.2\">";
        // Set title and text for tooltip
        $this->body .= "
			Text[1] = [\"affiliate privileges\", \"When checked this will give affiliate privileges to users in this group.  Affiliate privileges allow users of this group to have access to the affiliate page.  This is a special page where (if linked to properly) a page will be displayed with only their own listings.  There will be no direct link to the main site unless you place one within the template.  The search within this section will only return listings from this user.  To find the direct link to the affiliate's site for this user you must find the user you wish to send the link to through the list users or search users part of the admin and display that users information.  The link to the affiliate section will be with the other group information for that user.\"]\n
			Text[2] = [\"Affiliate URL Page\", \"Choose a template used on this group affiliate site within the home page using this language.  If no template is chosen, the default template set in the PAGES > BROWSING PAGES > BROWSE CATEGORIES will be used.  Enter all templates through the template administration.\"]\n
			Text[3] = [\"Affiliate URL Browsing Page\", \"Choose a template used on this group affiliate site within the secondary browsing page using this language.  If no template is chosen for this category the default template set in the PAGES > BROWSING PAGES > BROWSE CATEGORIES will be used.  Enter all templates through the template administration.\"]\n
			Text[4] = [\"Listing Display Details\", \"Choose a template used on this group affiliate site within ad display page using this language.  If no template is chosen for this category, the default template set in the PAGES > BROWSING PAGES > AD DISPLAY PAGE will be used.  Enter all templates through the template administration.\"]\n
			Text[5] = [\"display listing extra question template\", \"Choose a template used on this group affiliate site within the listing display template to display the extra category specific questions using this language.  If no template is chosen for this category the default template set in the PAGES > BROWSING PAGES > EXTRA QUESTIONS will be used.  Enter all templates through the template administration.\"]\n
			Text[6] = [\"display listing checkbox template\", \"Choose a template used on this group affiliate site within the listing display template to display the checkbox based category specific questions using this language.  If no template is chosen for this category the default template set in the PAGES > BROWSING PAGES > EXTRA CHECKBOXES will be used.  Enter all templates through the template administration.\"]\n
			Text[10] = [\"Create Listing\", \"Uncheck this box to remove New Listing, My Active Listings, and My Expired Listings from this usergroup's User Management Home Page.\"]\n
			Text[11] = [\"Messaging\", \"Uncheck this box to remove My Messages and Message Settings from this usergroup's User Management Home Page.\"]\n
			Text[12] = [\"Favorites\", \"Uncheck this box to remove My Favorites from this usergroup's User Management Home Page.\"]\n
			Text[13] = [\"Filters\", \"Uncheck this box to remove My Listing Filters from this usergroup's User Management Home Page.\"]\n
			Text[14] = [\"Black List/Invited List\", \"Uncheck this box to remove Black Listed Buyers and Invited List of Buyers from this usergroup's User Management Home Page.\"]\n
			Text[15] = [\"Feedback\", \"Uncheck this box to remove Feedback Management from this usergroup's User Management Home Page.\"]\n
			";
        if ($this->db->get_site_setting('use_admin_wysiwyg')) {
            $this->body .= "
			Text[7] = [\"Splash Page During Registration\", \"Enter below what you wish to display after a user has registered into this group.  This html will be displayed within the registration page template and will appear once this user has entered the registration code for this group.  This will not display if the user is \\\"defaulted\\\" into this group by not entering a registration code.\"]\n
			Text[8] = [\"Splash Page during Listing Process\", \"Enter below what you wish to display to a user of this group when they click the link to place a new listing.  This will be displayed within the \\\"choose category\\\" page template before the user chooses a category to place their listing in.\"]\n
			Text[9] = [\"\\\"Sponsored by\\\" HTML placed on Listing Display Page\", \"Enter below what you wish to display within the listing display page of sellers belonging to to this group. This can be any message you wish to attach to users listings placed by sellers within this group.  You must place the {\$sponsored_by} tag within the listing display template for this code to display.  On sellers that do not have sponsored by html attached to their group nothing will appear.\"]\n
			";
        } else {
            $this->body .= "
			Text[7] = [\"Splash Page During Registration\", \"Cut and paste html you wish to display after a user has registered into this group.  This html will be displayed within the registration page template and will appear once this user has entered the registration code for this group.  This will not display if the user is \\\"defaulted\\\" into this group by not entering a registration code.\"]\n
			Text[8] = [\"Splash Page during Listing Process\", \"Cut and paste html you wish to display to a user of this group when they click the link to place a new ad.  This html will be displayed within the \\\"choose category\\\" page template before the user chooses a category to place their listing in.\"]\n
			Text[9] = [\"\\\"Sponsored by\\\" HTML placed on Listing Display Page\", \"Cut and paste html you wish to display within the listing display page of sellers belonging to to this group. This can be any message you wish to attach to users listings placed by sellers within this group.  You must place the {\$sponsored_by} tag within the listing display template for this code to display.  On sellers that do not have sponsored by html attached to their group nothing will appear.\"]\n
			";
        }
        //".$this->show_tooltip(5,1)."

        // Set style for tooltip
        //echo "Style[0] = [\"white\",\"\",\"\",\"\",\"\",,\"black\",\"#ffffcc\",\"\",\"\",\"\",,,,2,\"#b22222\",2,24,0.5,0,2,\"gray\",,2,,13]\n";
        $this->body .= "</script>";

        //get all fee-based price plans for dropdown boxes

        /*
        was not working in classifieds only mode and no subscription based price plans
        James
        $this->sql_query = "SELECT DISTINCT p.applies_to,p.name,p.price_plan_id,p.type_of_billing
            FROM ".$this->price_plan_table." AS p, geodesic_classifieds_subscription_choices AS s
            WHERE
                p.price_plan_id = s.price_plan_id or p.type_of_billing=1
            ORDER BY p.name";
        */

        $this->sql_query = "SELECT * FROM " . $this->price_plan_table . " WHERE type_of_billing=1 ORDER BY name";

        $price_plan_result = $this->db->Execute($this->sql_query);
        if ($this->debug_groups) {
            echo $this->new_group_error . '<br>';
            echo $this->sql_query . "<br>\n";
            echo $price_plan_result->RecordCount() . " is the price_plan_result->RecordCount<Br>\n";
        }
        if (!$price_plan_result) {
            $this->error_message = $this->internal_error_message;
            if ($this->debug_groups) {
                echo $this->sql_query . "<br>\n";
            }
            return false;
        } elseif ($price_plan_result->RecordCount() > 0) {
            while ($show = $price_plan_result->FetchRow()) {
                if ((geoMaster::is('auctions')) && $show["applies_to"] == 2) {
                    $auc_fee_based_options .= "
						<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                }
                if ((geoMaster::is('classifieds')) && $show["applies_to"] == 1) {
                    $class_fee_based_options .= "
						<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                }
            }

            $this->sql_query = "SELECT DISTINCT p.applies_to,p.name,p.price_plan_id,p.type_of_billing
				FROM " . $this->price_plan_table . " AS p, geodesic_classifieds_subscription_choices AS s
				WHERE
					p.price_plan_id = s.price_plan_id or p.type_of_billing=2
				ORDER BY p.name";
            $price_plan_result = $this->db->Execute($this->sql_query);
            if ($this->debug_groups) {
                echo $price_plan_result->RecordCount() . " is the price_plan_result->RecordCount<Br>\n";
                echo $this->sql_query . "<br>\n";
            }
            if (!$price_plan_result) {
                if ($this->debug_groups) {
                    echo $this->sql_query . "<br>\n";
                }
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($price_plan_result->RecordCount() > 0) {
                while ($show = $price_plan_result->FetchRow()) {
                    $this->sql_query = "SELECT * FROM $this->classified_subscription_choices_table
						WHERE price_plan_id = " . $show["price_plan_id"];
                    if ($this->debug_groups) {
                        echo $this->sql_query . "<br>\n";
                    }
                    $sub_result = $this->db->Execute($this->sql_query);
                    if (!$sub_result) {
                        if ($this->debug_groups) {
                            echo $this->sql_query . "<br>\n";
                        }
                        $this->error_message = $this->internal_error_message;
                        return false;
                    } elseif ($sub_result->RecordCount() > 0) {
                        //echo "sub_result had some count<br>\n";
                        if ((geoMaster::is('auctions')) && $show["applies_to"] == 2) {
                            $auc_sub_based_options .= "
								<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                        }
                        if ((geoMaster::is('classifieds')) && $show["applies_to"] == 1) {
                            $class_sub_based_options .= "
								<option value=" . $show["price_plan_id"] . ">" . $show["name"] . "</option>";
                        }
                    } else {
                        //echo "sub_result had no count<br>\n";
                        if ((geoMaster::is('auctions')) && $show["applies_to"] == 2) {
                            $auc_sub_based_options_error .= "
								<br><font color=red>you must add subscription lengths to your auction subscription based price plans</font>";
                        }
                        if ((geoMaster::is('classifieds')) && $show["applies_to"] == 1) {
                            $class_sub_based_options_error .= "
								<br><font color=red>you must add subscription lengths to your classified subscription based price plans</font>";
                        }
                    }
                }
            }

            $this->additional_body_tag_attributes .= " onload='javascript:show_types();javascript:hide_affiliate_section(" . $show["affiliate"] . ");'";
            $this->additional_head_html .= "
			<script type='text/javascript'>
				function show_types()
				{
					if(jQuery('#fee_plan_type').prop('checked')) {
						jQuery('#fee').show();
						jQuery('#sub').hide();
					} else if (jQuery('#sub_plan_type').prop('checked')) {
						jQuery('#sub').show();
						jQuery('#fee').hide();
					}
				}
				jQuery(document).ready(function(){ show_types(); });
				function check_sub_period_choice()
				{
					if (jQuery('#sub_plan_type').prop('checked') &&
						!jQuery('#choice_1').prop('checked') &&
						!jQuery('#choice_2').prop('checked'))
					{
						alert('You must choose which price plan\'s subscription periods will overwrite the other.');
						return false;
					}
					else
					{
						return true;
					}
				}
				function validate_inputs()
				{
					if (document.getElementById('d[name]').value==''){ alert ('Please enter the name for the group.'); return false; } return true;
				}
			</script>";
            if (strlen($this->new_group_error) > 0) {
                $this->body .= "
				<table cellpadding=3 cellspacing=0 border=0 align=center width=100%>
					<tr>
						<td align=center width=100% class=medium_error_font>
							$this->new_group_error
						</td>
					</tr>
				</table>";
            }
            if (!$this->admin_demo()) {
                $this->body .= "<form class='form-horizontal form-label-left' onSubmit='return check_sub_period_choice()' action=index.php?mc=users&page=users_new_group method=post name=price_plans>";
            } else {
                $this->body .= "<div class='form-horizontal'>";
            }

            $this->body .= '
			<fieldset>
				<legend>Group Details</legend>
				<div class="x_content">';
            $this->body .= "
				  <div class='form-group'>
				  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Group Name:* </label>
					<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=d[name] id='d[name]' class='form-control col-md-7 col-xs-12'";
            if ($restore_values) {
                $this->body .= " value = \"" . $d_vars['name'] . "\"";
            }
            $this->body .= " />
					</div>
				  </div>

				  <div class='form-group'>
				  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Group Description: </label>
					<div class='col-md-6 col-sm-6 col-xs-12'><textarea name=d[description] class='form-control'>";
            if ($restore_values) {
                $this->body .= geoString::specialChars($d_vars['description']);
            }
            $this->body .= "</textarea>
					</div>
				  </div>

				  <div class='form-group'>
				  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Registration Code: </label>
					<div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=d[registration_code] class='form-control col-md-7 col-xs-12'";
            if ($restore_values) {
                $this->body .= " value=\"" . $d_vars['registration_code'] . "\"";
            }
            $this->body .= ' />
					</div>
				  </div>

				</div>
			</fieldset>';

            $sub_disable = $sub_notify = false;
            if (geoMaster::is('classifieds')) {
                if (strlen(trim($class_sub_based_options)) == 0) {
                    $sub_disable = true;
                }
            }
            if (geoMaster::is('auctions')) {
                if (strlen(trim($auc_sub_based_options)) == 0) {
                    $sub_disable = true;
                }
            }
            if ($sub_disable) {
                $sub_notify = " - <span style='color: red; font-weight: bold;'>You must first <a href='index.php?page=pricing_price_plans'>attach subscription periods</a> to your Subscription-based Price Plan(s)</span>";
            }

            if (!geoAddon::getInstance()->isEnabled('subscription_pricing')) {
                $sub_disable = true;
                $sub_notify = " - Requires the <span style='color: red; font-weight: bold'>Subscription Pricing</span> Addon Activation";
            }

            $this->body .= '<fieldset>
				<legend>Price Plan Attachment</legend>
				<div class="x_content">
				<table cellpadding=3 cellspacing=0 border=0 align=center width=100%>

					<div class="col_hdr">Price Plan(s) for this User Group
					</div>

					<div class="form-group">
					  <label class="control-label col-md-5 col-sm-5 col-xs-12">Price Plan Type: </label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<input type="radio" name="d[price_plan_type]" value="1" id="fee_plan_type" onclick="show_types();" ' . ((!$restore_values || $d_vars['price_plan_type'] != 2) ? 'checked="checked"' : '') . ' /> Fee-Based<br />
							<input type="radio" name="d[price_plan_type]" value="2" id="sub_plan_type" onclick="show_types();" ' . (($restore_values && $d_vars['price_plan_type'] == 2) ? 'checked="checked"' : '') . ' ' . ($sub_disable ? 'disabled="disabled"' : '') . '/> Subscription-Based' . $sub_notify . '
						</div>
					</div>

					<tr id="fee" style="display:none;">
						<td colspan="100%">';

            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $level = "an Auction Price Plan and a Classified Price Plan";
            } elseif (geoMaster::is('classifieds')) {
                $level = "a Classified Price Plan";
            } elseif (geoMaster::is('auctions')) {
                $level = "an Auction Price Plan";
            }

            $this->body .= "<p class=\"medium_font plan-color\" style=\"text-align: center; font-weight:bold;\">Select which Fee-Based Price Plan(s) to Attach:</p>";

            if (geoMaster::is('auctions')) {
                $this->body .= "
						  <div class='form-group'>
							  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Auction Price Plan:</label>
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<select name=d[new_auc_price_plan_fee] class='form-control col-md-7 col-xs-12'>
										$auc_fee_based_options
									</select>
								</div>
							</div>";
            }
            if (geoMaster::is('classifieds')) {
                $this->body .= "
						  <div class='form-group'>
							  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Classified Price Plan:</label>
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<select name=d[new_class_price_plan_fee] class='form-control col-md-7 col-xs-12'>
										$class_fee_based_options
									</select>
								</div>
						  </div>";
            }
            $this->body .= "

						</td>
					</tr>
					<tr id=sub style='display:none;'>
						<td colspan=\"100%\">";

            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                //instructions to make this page a little less confusing in ClassAuc
                $this->body .= "<p class=\"medium_font plan-color\" style=\"text-align: center; font-weight:bold;\">Select which Subscription Based price plans to attach, and which of the Price Plan's subscription periods will be used:</p>";
            } else {
                $this->body .= "<p class=\"medium_font plan-color\" style=\"text-align: center; font-weight:bold;\">Select which Subscription Based price plan to attach:</p>";
            }

            if (geoMaster::is('auctions')) {
                $this->body .= "
						  <div class='form-group'>
							  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Auction Price Plan:</label>" . $auc_sub_based_options_error . "
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<select name=d[new_auc_price_plan_sub] class='form-control col-md-7 col-xs-12'>
										$auc_sub_based_options
									</select>
									&nbsp;&nbsp;<input " . ((geoMaster::is('classifieds') && geoMaster::is('auctions')) ? "onclick=\"javascript:alert('NOTE: Subscription periods must be the same in both price plans attached to a User Group.  Therefore, this will permanently overwrite subscription periods belonging to the classified subscription chosen in the pull down box below');\" " : " ") . "
												id=choice_1 name=d[sub_period_choice] type=radio value=1><span style='font-weight: bold;'> Use this price plan's subscription periods</span>
								</div>
						  </div>";
            }
            if (geoMaster::is('classifieds')) {
                $this->body .= "
						  <div class='form-group'>
							  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Classified Price Plan:</label>" . $class_sub_based_options_error . "
								<div class='col-md-6 col-sm-6 col-xs-12'>
									<select name=d[new_class_price_plan_sub] class='form-control col-md-7 col-xs-12'>
										$class_sub_based_options
									</select>
									&nbsp;&nbsp;<input " . ((geoMaster::is('classifieds') && geoMaster::is('auctions')) ? "onclick=\"javascript:alert('NOTE:  Subscription periods must be the same in both price plans attached to a User Group.  Therefore, this will permanently overwrite subscription periods belonging to the auction subscription chosen in the pull down box above.');\" " : " ") . "
												id=choice_2 name=d[sub_period_choice] type=radio value=2><span style='font-weight: bold;'> Use this price plan's subscription periods</span>
								</div>
						  </div>";
            }
            $this->body .= "

							<script type='text/javascript'>
							" . (($sub_disable) ? "$('fee_plan_type').checked=true" : "") . "
							Event.observe('window','load',show_types);
							</script>
						</td>
					</tr>";
        } else {
            $this->body .= "
					<tr>
						<td class=medium_error_font>You do not have any listing price plans to attach.</td>
					</tr>";
        }

        $this->additional_head_html .= "
				<script type=\"text/javascript\">
					function hide_affiliate_section(refresh_value)
					{
						if (refresh_value==0 || refresh_value==1)
							check = refresh_value;
						else
							check = (document.getElementById('cbox').checked == true) ? 1 : 0;
						if (check==1)
						{
							document.getElementById('cbox').checked = true;
							document.getElementById('aff').style.display = '';

						}
						else
						{
							document.getElementById('cbox').checked = false;
							document.getElementById('aff').style.display = 'none';
						}
					}
				</script>";
        $this->body .= "
				</table>
			</div>
			</fieldset>";
        if (geoPC::is_ent()) {
            if ($this->db->get_site_setting("use_admin_wysiwyg")) {
                //add wysiwyg text to header.
                $template_code_text = ' Edit with WYSIWYG Editor (<a href="#" onclick="gjWysiwyg.toggleTinyEditors(); return false;">Add/Remove Editor</a>)<br />';
                $wysiwyg = true;
                require_once('admin_wysiwyg_config.php');
                $this->head_html .= wysiwyg_configuration::getHeaderText('textManager', false);
            } else {
                //wysiwyg turned off.
                $wysiwyg = false;
                $template_code_text = ' Edit with WYSIWYG OFF  (<a href="index.php?mc=admin_tools_settings&page=wysiwyg_general_config">Change Editor Settings</a>)<br />
Cut and paste your html template into the space below.';
            }
            $this->body .= "
			<fieldset>
				<legend>Splash Pages HTML / 'Sponsored By' HTML</legend>
				<div style='height: auto;' class='medium_font'>
					<div class='col_hdr_solid'>
						" . $this->show_tooltip(7, 1) . " Splash Page during Registration - $template_code_text
					</div>
					<textarea name='d[registration_splash_code]' id='d[registration_splash_code]' style='width: 100%;' class='textManager'>";
            if ($restore_values) {
                $this->body .= geoString::specialChars($d_vars['registration_splash_code']);
            } else {
                $this->body .= '&nbsp;';
            }

            $this->body .= "</textarea>
					<div class='col_hdr_solid' style='margin-top: 15px'>
						" . $this->show_tooltip(8, 1) . " Splash Page during Listing Process - $template_code_text
					</div>
					<textarea name='d[place_an_ad_splash_code]'  style=\"width:100%\" class=\"textManager\">";
            if ($restore_values) {
                $this->body .= geoString::specialChars($d_vars['place_an_ad_splash_code']);
            }
            $this->body .= "</textarea>
					<div class='col_hdr_solid' style='margin-top: 15px'>
						" . $this->show_tooltip(9, 1) . " \"Sponsored By\" HTML placed on Listing Display Page - $template_code_text
					</div>
					<textarea name='d[sponsored_by_code]''  style=\"width:100%\" class=\"textManager\">";
            if ($restore_values) {
                $this->body .= geoString::specialChars($d_vars['sponsored_by_code']);
            }
            $this->body .= "</textarea>
				</div>
			</fieldset>";

            $this->body .= "
			<fieldset>
				<legend>User Restrictions</legend>
				<div class='x_content'>";
            $this->body .= "
				<div class=\"form-group\">
					<div class=\"control-label col-md-5 col-sm-5 col-xs-12\"><label for=\"d[restrict_1]\">Create &amp; Edit Listings</label></div>
					<div class=\"col-md-6 col-sm-6 col-xs-12\"><input type=\"checkbox\" id=\"d[restrict_1]\" name=\"d[restrict_1]\" checked=\"checked\" /></div>
					<div style=\"clear:both; height: 1px;\"></div>
				</div>";
            $this->row_count++;
            $this->body .= "
				<div class=\"form-group\">
					<div class=\"control-label col-md-5 col-sm-5 col-xs-12\"><label for=\"d[restrict_2]\">Messaging</label></div>
					<div class=\"col-md-6 col-sm-6 col-xs-12\"><input type=\"checkbox\" id=\"d[restrict_2]\" name=\"d[restrict_2]\" checked=\"checked\" /></div>
					<div style=\"clear:both; height: 1px;\"></div>
				</div>";
            $this->row_count++;
            $this->body .= "
				<div class=\"form-group\">
					<div class=\"control-label col-md-5 col-sm-5 col-xs-12\"><label for=\"d[restrict_4]\">Favorites</label></div>
					<div class=\"col-md-6 col-sm-6 col-xs-12\"><input type=\"checkbox\" id=\"d[restrict_4]\" name=\"d[restrict_4]\" checked=\"checked\" /></div>
					<div style=\"clear:both; height: 1px;\"></div>
				</div>";
            $this->row_count++;
            $this->body .= "
				<div class=\"form-group\">
					<div class=\"control-label col-md-5 col-sm-5 col-xs-12\"><label for=\"d[restrict_8]\">Listing Filters</label></div>
					<div class=\"col-md-6 col-sm-6 col-xs-12\"><input type=\"checkbox\" id=\"d[restrict_8]\" name=\"d[restrict_8]\" checked=\"checked\" /></div>
					<div style=\"clear:both; height: 1px;\"></div>
				</div>";
            $this->row_count++;
            if (geoMaster::is('auctions')) {
                $this->body .= "
				<div class=\"form-group\">
					<div class=\"control-label col-md-5 col-sm-5 col-xs-12\"><label for=\"d[restrict_16]\">Black List &amp; Invited List</label></div>
					<div class=\"col-md-6 col-sm-6 col-xs-12\"><input type=\"checkbox\" id=\"d[restrict_16]\" name=\"d[restrict_16]\" checked=\"checked\" /></div>
					<div style=\"clear:both; height: 1px;\"></div>
				</div>";
                $this->row_count++;
                $this->body .= "
				<div class=\"form-group\">
					<div class=\"control-label col-md-5 col-sm-5 col-xs-12\"><label for=\"d[restrict_32]\">Feedback</label></div>
					<div class=\"col-md-6 col-sm-6 col-xs-12\"><input type=\"checkbox\" id=\"d[restrict_32]\" name=\"d[restrict_32]\" checked=\"checked\" /></div>
					<div style=\"clear:both; height: 1px;\"></div>
				</div>";
                $this->row_count++;
            }
            $this->body .= "
			</div>
			</fieldset>";
        }

        $ebayUtil =& geoAddon::getUtil('ebay');
        if ($ebayUtil) {
            $this->row_count++;
                $this->body .= "
			<fieldset>
				<legend>eBay Privileges</legend>
				<div>
				<table cellpadding=3 cellspacing=0 border=0 align=center width=100%>
					<tr class=" . $this->get_row_color() . ">
						<td align=right width=50% class=medium_font>
							<b>eBay Price Plan:</b>
						</td>
						<td width=50% valign=top class=medium_font>
							<select name=d[ebay_priceplan]>
								<option value='0'>none</option>";
            foreach ($ebayUtil->getPricePlans() as $id => $plan) {
                $this->body .= "
								<option value='$id'>" . $plan['name'] . "</option>";
            }
            $this->body .= "
							</select>
						</td>
					</tr>
				</table>
				</div>
			</fieldset>";
        }

        if (!$this->admin_demo()) {
            $this->body .= "<div style='text-align:center;'><input type=submit name='auto_save' value=\"Save\" onClick=\"javascript:return (validate_inputs());\"></div></form>";
        } else {
            $this->body .= '</div>';
        }
        return true;
    } //end of function new_group_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function move_group_form($db, $group_id = 0)
    {
        if ($group_id) {
            $this->sql_query = "select name,group_id from " . $this->classified_groups_table . " where group_id != " . $group_id . " order by name";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() > 0) {
                $group_name = $this->get_group_name($db, $group_id);
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=users&page=users_group_move&g=" . $group_id . " class='form-horizontal form-label-left' method=post>";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }
                $this->body .= "
				<div class='page-title1'>User Group: <span class='group-color'>" . $group_name . "</span></div>
				<fieldset id='MoveUsers'>
				<legend>Move Users</legend><div class='x_content'>";
                //$this->title .= "Users / User Groups > Move Users";
                $this->description .= "Move users from this User Group to
					another User Group using this admin tool.  Choose the User Group you wish to move the users into by selecting it from the dropdown list below.
					This operation cannot be reversed.  If you change your mind, you will have to move users back individually once this action is taken.";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Move this Group's Users to: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<select name=h class='form-control col-md-7 col-xs-12'>";

                while ($show = $result->FetchRow()) {
                    if ($show["group_id"] != $group_id) {
                        $this->body .= "<option value=" . $show["group_id"] . ">" . $show["name"] . "</option>";
                    }
                }
                $this->body .= "</select>";
                $this->body .= "</div>";
                $this->body .= "</div>";

                if (!$this->admin_demo()) {
                    $this->body .= "<div class='center'><input type=submit value=\"Save\" name='auto_save'></div>";
                }
                $this->body .= "</div></fieldset>";
                if (!$this->admin_demo()) {
                    $this->body .= "</form>";
                } else {
                    $this->body .= '</div>';
                }
                return true;
            } else {
                $this->body .= "<div class='center'>";
                //$this->title .= "Users / User Groups > Move Users";
                $this->body .= "<p class='center'><b>There are no other User Groups to move users to.</b></p>";
                $this->body .= "</div>";
                return true;
            }
        } else {
            return false;
        }
    } //end of function move_group_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_group_form($db, $group_id)
    {
        if (($group_id) && ($group_id != 1)) {
            $this->sql_query = "select name,group_id from " . $this->classified_groups_table . " order by name";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() > 0) {
                $delete_group_name = $this->get_group_name($db, $group_id);
                if (!$this->admin_demo()) {
                    $this->body .= "<form action=index.php?mc=users&page=users_group_delete&c=" . $group_id . " class='form-horizontal form-label-left' method=post>\n";
                } else {
                    $this->body .= "<div class='form-horizontal'>";
                }
                $this->body .= "<div class='x_content'><table cellpadding=3 cellspacing=0 border=0 align=center width=100%>\n";
                //$this->title = "Users / User Groups > Group Management > Delete";
                $this->description = "To delete a user group you must choose
					which group to move that groups users to.  Every user must be a part of a group.  In the dropdown list of users groups below choose
					the group the new users will be a part of. Once you have made a choice the changes will be made by clicking the \"delete\"
					button at the bottom.";
                $this->body .= "<div class='page-title1'>User Group: <span class='group-color'>" . $delete_group_name . "</span></div>";

                $this->body .= "<div class='center large_font' style='margin:10px 0;'>Select a new User Group to move this User Group&#039;s existing users to.</div>";

                $this->body .= "<div class='form-group'>";
                $this->body .= "<label class='control-label col-md-4 col-sm-4 col-xs-12'>Move users to: </label>";
                $this->body .= "<div class='col-md-6 col-sm-6 col-xs-12'>
						<select name=d class='form-control col-md-7 col-xs-12'>";
                while ($show = $result->FetchRow()) {
                    if ($show["group_id"] != $group_id) {
                        $this->body .= "<option value=" . $show["group_id"] . ">" . $show["name"] . "</option>";
                    }
                }
                $this->body .= "</select>";
                $this->body .= "</div>";
                $this->body .= "</div>";

                if (!$this->admin_demo()) {
                    $this->body .= "<div class='center'><input type=submit name='auto_save' value=\"Save\"></div>";
                }
                $this->body .= "</table></div>";
                $this->body .= ($this->admin_demo()) ? '</div>' : "</form>";
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function delete_group_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_group_info($db, $group_id = 0, $group_info = 0)
    {
        $group_id = (int)$group_id;
        if (($group_info) && ($group_id)) {
            $this->sql_query = "select * from " . $this->pages_languages_table;
            $language_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query." is the query<br>\n";
            if (!$language_result) {
                //echo $this->sql_query." is the query<br>\n";
                $this->error_message = $this->messages[3500];
                return false;
            } elseif ($language_result->RecordCount() > 0) {
                if ($this->check_registration_code($db, $group_id, $group_info["registration_code"])) {
                    $restrictions = 0;
                    if (!geoPC::is_ent()) {
                        $restrictions += 1 + 2 + 4 + 8 + 16 + 32;
                    }
                    if ($group_info["restrict_1"] == 'on') {
                        $restrictions += 1; // create listing
                    }
                    if ($group_info["restrict_2"] == 'on') {
                        $restrictions += 2; // messaging
                    }
                    if ($group_info["restrict_4"] == 'on') {
                        $restrictions += 4; // favorites
                    }
                    if ($group_info["restrict_8"] == 'on') {
                        $restrictions += 8; // filters
                    }
                    if ($group_info["restrict_16"] == 'on') {
                        $restrictions += 16; // white/blacklists
                    }
                    if ($group_info["restrict_32"] == 'on') {
                        $restrictions += 32; // feedback
                    }

                    $group_info["affiliate"] = ($group_info["affiliate"]) ? 1 : 0;
                    if (isset($group_info["registration_splash_code"]) && isset($group_info["place_an_ad_splash_code"]) && isset($group_info["sponsored_by_code"])) {
                        $this->sql_query = "update " . $this->classified_groups_table . " set
							name = ?, description = ?, registration_splash_code = ?, place_an_ad_splash_code = ?, sponsored_by_code = ?,
							affiliate = ?, registration_code = ?, restrictions_bitmask = ?	where group_id = ?";
                        $args = array($group_info["name"],$group_info["description"],geoString::toDB(trim($group_info["registration_splash_code"])),
                                geoString::toDB(trim($group_info["place_an_ad_splash_code"])),geoString::toDB(trim($group_info["sponsored_by_code"])),
                                $group_info["affiliate"],$group_info["registration_code"],$restrictions,$group_id);
                    } else {
                        $this->sql_query = "update " . $this->classified_groups_table . " set
							name = ?, description = ?, affiliate = ?, registration_code = ?,
							restrictions_bitmask = ? where group_id = ?";
                        $args = array($group_info["name"],$group_info["description"],$group_info["affiliate"],$group_info["registration_code"],
                                $restrictions,$group_id);
                    }
                    //echo $this->sql_query."<br>\n";
                    $result = $this->db->Execute($this->sql_query, $args);
                    if (!$result) {
                        //echo $this->sql_query."<br>\n";
                        $this->error_message = $this->internal_error_message;
                        return false;
                    }
                    $addon_vars = array ('group_id' => $group_id, 'this' => $this);
                    geoAddon::triggerUpdate('Admin_Group_Management_edit_group_update', $addon_vars);
                    $ebayUtil =& geoAddon::getUtil('ebay');
                    if ($ebayUtil) {
                        if (isset($group_info["ebay_priceplan"])) {
                            $sql = "update geodesic_ebay_groups_price_plans set
								plan_id = ?
								where group_id = ?";
                            $result = $this->db->Execute($sql, array($group_info["ebay_priceplan"],$group_id));
                            if (!$result || ($this->db->Affected_Rows() == 0)) {
                                $sql = "insert into geodesic_ebay_groups_price_plans
									(group_id, plan_id)
									values
									(?, ?)";
                                    $result = $this->db->Execute($sql, array($group_id,$group_info["ebay_priceplan"]));
                                if (!$result) {
                                    $this->error_message = "eBay price plan was not attached.<br />";
                                    return false;
                                }
                            }
                        }
                    }
                } else {
                    $this->body .= "<span class=medium_error_font>That registration code already exists, please try again</span><br>\n";
                }

                return true;
            } else {
                return false;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_group_info

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function check_registration_code($db, $group_id, $registration_code)
    {
        if (strlen(trim($registration_code)) > 0) {
            $this->sql_query = "select * from " . $this->classified_groups_table . " where group_id != " . $group_id . " and registration_code = \"" . $registration_code . "\"";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() > 0) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    } //end of function check_registration_code

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function group_multiple_price_plan_form($db, $group_id, $product_type)
    {
        if (!geoPC::is_ent()) {
            return false;
        }
        if ($group_id && $product_type) {
            $item_name = ($product_type == 1) ? "Classified Ad" : "Auction";

            $sql_query = "select * from " . $this->classified_groups_table . " where group_id = " . $group_id;
            $group_result = $this->db->Execute($sql_query);
            if ((!$group_result) && ($group_result->RecordCount() != 1)) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            $group_data = $group_result->FetchRow();

            $sql_query = "select * from " . $this->attached_price_plans . " where group_id = " . $group_id . " and applies_to = " . $product_type;
            $attached_result = $this->db->Execute($sql_query);
            if (!$attached_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            $group_name = $this->get_group_name($db, $group_id);
            $this->title = " ({$item_name})";
            $this->description = "The price plans listed below will become the only choices within the listing process when the user
				<b>in this User Group</b> is listing <b>$item_name(s)</b>.  If no additional price plans (beyond the one originally attached)
				are added below the price plan originally attached to this group will remain the default price plan.  However, if there
				<b>\"are\"</b> additional Price Plan choices specified below, when a user lists an item these \"Additional Price Plans\" will
				become choices for the user to select from for that $item_name. In this case, <b>\"only\"</b> the price plans listed below will
				become choices.  Therefore, if you still want the \"default\" User Group Price Plan to apply, you should also attach the default
				Price Plan below.<br><br><b>IMPORTANT:</b> The text that you enter within the <b>\"Name\"</b> and <b>\"Description\"</b> fields
				below is the text that your users will see as Price Plan choices to select from during the listing process.";

            $this->additional_head_html .= "
				<script type=text/javascript>
					function check_name_length()
					{
						if (document.getElementById('name').value.length <= 0)
						{
							alert('Name field required !');
							return false;
						}
						else
							return true;
					}
				</script>";

            if (!$this->admin_demo()) {
                $this->body .= "<form onSubmit='return check_name_length();' action=index.php?mc=users&page=users_group_add_plan&g=" . $group_id . "&t=" . $product_type . " method=post>";
            }

            $this->body .= "<table width=100% cellpadding=3 cellspacing=1 border=0 align=center>";
            if (strlen($this->error)) {
                $this->body .= "<tr><td>&nbsp;</td><td align=center class=medium_error_font>" . $this->error . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
            }
            $this->body .= "
				<tr>
					<td colspan=4 align=center class=group_price_hdr>
						<b>sssUser Group: " . $group_name . "</b>
					</td>
				</tr>
				<tr class=col_hdr>
					<td align=center>
						<b>Additional Price Plan(s)</b>
					</td>
					<td align=center>
						<b>Name</b>
					</td>
					<td align=center>
						<b>Description</b>
					</td>
					<td>
						&nbsp;
					</td>
				</tr>\n";

            if ($attached_result->RecordCount() > 0) {
                $this->row_count = 0;
                $in_already_listed = "";
                while ($show_attached = $attached_result->FetchRow()) {
                    $price_plan_name = $this->get_price_plan_name($db, $show_attached["price_plan_id"]);
                    if (strlen($in_already_listed) > 0) {
                        $in_already_listed .= "," . $show_attached["price_plan_id"];
                    } else {
                        $in_already_listed = $show_attached["price_plan_id"];
                    }
                    $this->body .= "
						<tr class=" . $this->get_row_color() . ">
							<td align=center class=medium_font>
								" . $price_plan_name . "
							</td>
							<td align=center class=medium_font>
								" . $show_attached["name"] . "
							</td>
							<td align=center class=medium_font>
								" . $show_attached["description"] . "
							</td>
							<td align=center width=100>
								" . geoHTML::addButton('delete', "index.php?mc=users&page=users_group_add_plan&g=" . $group_id . "&p=" . $show_attached["price_plan_id"] . "&t=" . $product_type . "&amp;delete=1&auto_save=1", false, '', 'lightUpLink mini_cancel') . "
							</td>
						</tr>";
                    $this->row_count++;
                }
            } else {
                    $this->body .= "
						<tr>
							<td align=center colspan=4>
								<div class=page_note_error>No additional Price Plans are currently attached to this User Group (the \"default\" Price Plan will apply).</div>
							</td>
						</tr>";
            }

            $in_already_listed = ($in_already_listed) ? $in_already_listed : "NULL";
            //get list of price plans
            $sql_query = "SELECT * FROM $this->price_plan_table
				WHERE
					type_of_billing != 2 AND
					applies_to = $product_type";
            if ($in_already_listed != "NULL") {
                $sql_query .= " AND price_plan_id NOT IN(" . $in_already_listed . ")";
            }
            $price_plan_result = $this->db->Execute($sql_query);
            if (!$price_plan_result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($price_plan_result->RecordCount() > 0) {
                $this->body .= "
						<tr bgcolor=#DDDDDD>
							<td align=center>
								<select name=p[price_plan_id]>";
                while ($price_plan = $price_plan_result->FetchRow()) {
                    $this->body .= "<option value=" . $price_plan["price_plan_id"] . ">
										" . $price_plan["name"] . "
									</option>";
                }
                $this->body .= "</select>
							</td>
							<td align=center class=medium_font>
								<input id=name type=text name=p[name]>
							</td>
							<td align=center class=medium_font>
								<textarea name=p[description]></textarea>
							</td>
							<td align=center>";
                if (!$this->admin_demo()) {
                    $this->body .= "<input type=submit name='auto_save' value=\"attach plan\">";
                }
                $this->body .= "</td>
						</tr>";
            }
            $this->body .= "
					</table>
				</form>
				<tr><td align='left'>
				<div class='page_note'><strong>IMPORTANT:</strong> In order for this option to be available to users, there must be at least <strong>two</strong> plans specified in the table above.</div>
				<div style='padding: 5px;'><a href=index.php?mc=users&page=users_group_edit&c=$group_id class='back_to'>
				<img src='admin_images/design/icon_back.gif' alt='' class='back_to'>Back to Edit Details for this User Group</a></div>
			    </td></tr>";
            return true;
        }
    } //end of function group_multiple_price_plan_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function add_attached_price_plan($db, $group_id, $price_plan, $product_type)
    {
        if (!geoPC::is_ent()) {
            return false;
        }

        if ($group_id && $price_plan && $product_type) {
            if (!$price_plan['name']) {
                $this->error = 'Name field required !';
                return true;
            }
            $sql_query = "select * from  " . $this->attached_price_plans . " where group_id = " . $group_id . " and price_plan_id = " . $price_plan["price_plan_id"];
            $result = $this->db->Execute($sql_query);
            //echo $sql_query."<br>\n";
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($result->RecordCount() == 0) {
                $sql_query = "insert into " . $this->attached_price_plans . "
					(group_id,price_plan_id,name,description,applies_to)
					values
					(?, ?, ?, ?, ?)";
                $query_data = array($group_id, $price_plan["price_plan_id"], '' . $price_plan["name"], '' . $price_plan["description"],$product_type);
                $result = $this->db->Execute($sql_query, $query_data);
                //echo $sql_query."<br>\n";
                if (!$result) {
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } else {
                    return true;
                }
            } else {
                $this->ad_configuration_message = "That value already exists";
                return true;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_ad_configuration

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_attached_price_plan($db, $group_id, $price_plan_id)
    {
        if ($group_id &&  $price_plan_id) {
            $sql_query = "delete from  " . $this->attached_price_plans . " where group_id = " . $group_id . " and price_plan_id = " . $price_plan_id;
            $result = $this->db->Execute($sql_query);
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }
            return true;
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function delete_attached_price_plan

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function price_plan_registration_freebies_form($db, $group_id = 0)
    {
        if (!$group_id) {
            return false;
        }
        if (!geoPC::is_ent() && !geoPC::is_premier()) {
            return false;
        }

        $group = $this->get_group($db, $group_id);
        if (geoMaster::is('classifieds')) {
            $show_price_plan = $this->get_price_plan($db, $group['price_plan_id']);
        } else {
            $show_price_plan = $this->get_price_plan($db, $group['auction_price_plan_id']);
        }

        if (!$show_price_plan) {
            return false;
        }


        $this->body .= "
<form action='' method='post' class='form-horizontal form-label-left'>
<div class='page-title1'>User Group: <span class='group-color'>{$group['name']}</span></div>
<fieldset>
	<legend>User Group Registration Specifics</legend><div>";


        if (($show_price_plan["type_of_billing"] == 1) || $different) {
            //fee based price plans

            if (geoPC::is_ent()) {
                // Show Initial Site Balance
                $precurrency = $this->db->get_site_setting('precurrency');
                $postcurrency = $this->db->get_site_setting('postcurrency');
                if (geoAddon::getInstance()->isEnabled('account_balance')) {
                    $this->body .= "
					<div class='x_content'>

					 <div class='form-group'>
					  <label class='control-label col-md-5 col-sm-5 col-xs-12'>Initial Account Balance: </label>
						<div class='col-md-6 col-sm-6 col-xs-12'>
						  <div class='input-group'>
							<div class='input-group-addon'>$precurrency</div>
							<input type=\"text\" name=\"h[initial_site_balance]\" class='form-control col-md-7 col-xs-12' value=\"{$show_price_plan["initial_site_balance"]}\" size=\"6\" />
							<div class='input-group-addon'>$postcurrency</div>
						  </div>
						</div>
					  </div>

					</div>";
                    $this->row_count ++;
                } else {
                    $this->body .= '<p class="page_note">Nothing to do here for this User Group.</p>';
                    $noSaveBtn = true;
                }
            }
        } elseif ($show_price_plan["type_of_billing"] == 2) {
            //subscription based price plans
            //free subscription period from registration
            $this->body .= "
			<div class='row_color1'>
				<div class='leftColumn'>Free Subscription Period upon Registration</div>
				<div class='rightColumn'>
					";
            $this->subscription_period_dropdown($db, $show_price_plan["free_subscription_period_upon_registration"], "h[free_subscription_period_upon_registration]");
            $this->body .= "
				</div>
				<div class='clearColumn'></div>
			</div>";
        }

        $this->body .= "</div>
</fieldset>";
        $this->body .= "<table>";
        if (!$this->admin_demo() && !$noSaveBtn) {
            $this->body .= "<div class='center'><input type=submit name='auto_save' value=\"Save\"></div>";
        }
        $this->body .= "<tr>\n\t<td colspan=2>\n\t
		<div style='padding: 5px;'><a href=index.php?mc=users&page=users_groups class='back_to'>
		<i class='fa fa-backward'> </i> Back to User Groups Home</a></div>
		<div style='padding: 5px;'><a href=index.php?mc=users&page=users_group_edit&c=" . $group_id . " class='back_to'>
		<i class='fa fa-backward'> </i> Back to " . $group['name'] . " Details</a></div>
		</td>\n</tr>\n";
        $this->body .= "</table>\n";
        $this->body .= "</form>\n";
        return true;
    } //end of function price_plan_registration_freebies_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_price_plan_registration_freebies($db, $group_id = 0, $group_info = 0)
    {
        if (!geoPC::is_ent() && !geoPC::is_premier()) {
            return false;
        }

        if (($group_id) && ($group_info)) {
            $group = $this->get_group($db, $group_id);
            if (!$group) {
                $this->error_message = $this->internal_error_message;
                return false;
            }

            $this->sql_query = "update " . $this->price_plan_table . " set ";
            $expiration_date = mktime(0, 0, 0, $group_info["credits_expire_date"]["month"], $group_info["credits_expire_date"]["day"], $group_info["credits_expire_date"]["year"]);
            $this->sql_query .= "initial_site_balance = " . geoNumber::deformat($group_info["initial_site_balance"]) . ",
					free_subscription_period_upon_registration = \"" . $group_info["free_subscription_period_upon_registration"] . "\"";

            if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                $this->sql_query .= " where price_plan_id in (" . $group['price_plan_id'] . ", " . $group['auction_price_plan_id'] . ")";
            } elseif (geoMaster::is('auctions')) {
                $this->sql_query .= " where price_plan_id = " . $group['auction_price_plan_id'];
            } elseif (geoMaster::is('classifieds')) {
                $this->sql_query .= " where price_plan_id = " . $group['price_plan_id'];
            }

            //echo $this->sql_query.'<Br>';
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } else {
                return true;
            }
        } else {
            $this->error_message = $this->internal_error_message;
            return false;
        }
    } //end of function update_price_plan_registration_freebies

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function compare_price_plan_types($db, $auction_price_plan_id = 0, $classified_price_plan_id = 0)
    {
        $this->sql_query = "SELECT type_of_billing FROM " . $this->price_plan_table . " WHERE price_plan_id = " . $auction_price_plan_id;
        if ($this->debug_groups) {
            echo "<BR>QUERY(LINE " . __LINE__ . ") - " . $this->sql_query;
        }
        $auc_result = $this->db->Execute($this->sql_query);
        if (!$auc_result) {
            if ($this->debug_groups) {
                $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
            }
            $this->error_message = $this->internal_error_message;
            return false;
        }
        $this->sql_query = "SELECT type_of_billing FROM " . $this->price_plan_table . " WHERE price_plan_id = " . $classified_price_plan_id;
        if ($this->debug_groups) {
            echo "<BR>QUERY(LINE " . __LINE__ . ") - " . $this->sql_query;
        }
        $class_result = $this->db->Execute($this->sql_query);
        if (!$class_result) {
            $this->error_message = $this->internal_error_message;
            return false;
        }
        if ($auc_result->RecordCount() == 1 && $class_result->RecordCount() == 1) {
            $auc = $auc_result->FetchRow();
            $class = $class_result->FetchRow();
            if ($auc["type_of_billing"] == $class["type_of_billing"]) {
                return true;
            } else {
                if ($this->debug_groups) {
                    $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
                }
                return false;
            }
        } else {
            if ($this->debug_groups) {
                $this->new_group_error .= "<br>admin_group_management.php LINE " . __LINE__ . "<br>";
            }
            $this->error_message = $this->internal_error_message;
            return false;
        }
    }//end of function compare_price_plan_types

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_groups()
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        if ($_REQUEST['new_group']) {
            $menu_loader->userSuccess("New Group Inserted");
        }


        $this->display_group_list($this->db);
        $this->display_page();
    }
    function update_users_groups()
    {
        if ($_REQUEST['e']) {
            return $this->set_default_group($_REQUEST["e"]);
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_new_group()
    {
        if (!isset($_POST['d'])) {
            $this->new_group_form();
            $this->display_page();
        } else {
            $this->display_users_groups();
        }
    }
    function update_users_new_group()
    {
        return $this->insert_group($this->db, $_POST["d"]);
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_move()
    {
        if ($_REQUEST["g"]) {
            if (!$this->move_group_form($this->db, $_REQUEST["g"])) {
                $this->site_error(0, __FILE__, __LINE__);
            }
        } else {
            if (!$this->display_group_list($this->db)) {
                $this->site_error(0, __FILE__, __LINE__);
            }
        }
        $this->display_page();
    }
    function update_users_group_move()
    {
        if (($_REQUEST["g"]) && ($_REQUEST["h"])) {
            return $this->move_to_group($this->db, $_REQUEST["g"], $_REQUEST["h"]);
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_edit()
    {
        if ($_REQUEST["c"]) {
            if (!$this->group_form($this->db, $_REQUEST["c"])) {
                return false;
            }
        } else {
            if (!$this->display_group_list($this->db)) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_users_group_edit()
    {
        if (($_REQUEST["c"]) && ($_POST["d"])) {
            return $this->update_group_info($this->db, $_REQUEST["c"], $_POST["d"]);
        } elseif ($_REQUEST['c'] && isset($_REQUEST['pg'])) {
            $action = $_REQUEST['pg'];
            $group = intval($_REQUEST['c']);
            //make sure group is valid
            $sql = "SELECT `name`, `group_id` FROM " . geoTables::groups_table . " WHERE `group_id` = ? LIMIT 1";
            $db = DataAccess::getInstance();
            $row = $db->GetRow($sql, array($group));
            if (is_array($row) && isset($row['group_id']) && $row['group_id'] == $group) {
                if ($action == 'turn_on') {
                    geoPaymentGateway::setGroup($group, 1);
                    geoPaymentGateway::loadGateways();
                } elseif ($action == 'turn_off') {
                    geoPaymentGateway::removeGroupPaymentGateways($group);
                }
            }
            return true;
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_delete()
    {
        if ($_REQUEST["c"] && !$this->deletedGroup) {
            if (!$this->delete_group_form($this->db, $_REQUEST["c"])) {
                return false;
            }
        } else {
            if (!$this->display_group_list($this->db)) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_users_group_delete()
    {
        if (($_REQUEST["c"]) && ($_POST["d"])) {
            //move current users from group
            if ($this->move_to_group($this->db, $_REQUEST["c"], $_POST["d"])) {
                //delete group
                $this->deletedGroup = 1;
                return $this->delete_group($this->db, $_REQUEST["c"]);
            } else {
                return false;
            }
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_price_edit()
    {
        if ($_REQUEST["g"]) {
            if (!$this->move_group_price_plan_form($this->db, $_REQUEST["g"])) {
                return false;
            }
        } else {
            if (!$this->display_group_list($this->db)) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_users_group_price_edit()
    {
        if ($_REQUEST["g"] && $_REQUEST["k"]) {
            return $this->move_group_price_plan($this->db, $_REQUEST["g"], $_REQUEST["k"]);
        }
        return false;
    }
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_registration()
    {
        if ($_REQUEST["g"]) {
            if (!$this->price_plan_registration_freebies_form($this->db, $_REQUEST["g"])) {
                return false;
            }
        }
        $this->display_page();
    }
    function update_users_group_registration()
    {
        if (($_REQUEST["g"]) && ($_REQUEST["h"])) {
            return $this->update_price_plan_registration_freebies($this->db, $_REQUEST["g"], $_REQUEST["h"]);
        }
        return false;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function display_users_group_add_plan()
    {
        if ($_REQUEST["g"] && $_REQUEST["p"] && $_REQUEST["t"] && $_GET["delete"]) {
            if (!$this->delete_attached_price_plan($this->db, $_REQUEST["g"], $_REQUEST["p"])) {
                return false;
            } elseif (!$this->group_multiple_price_plan_form($this->db, $_REQUEST["g"], $_REQUEST["t"])) {
                return false;
            }
        } else {
            if (!$this->group_multiple_price_plan_form($this->db, $_REQUEST["g"], $_REQUEST["t"])) {
                return false;
            }
        }

        $this->display_page();
    }
    function update_users_group_add_plan()
    {
        return $this->add_attached_price_plan($this->db, $_REQUEST["g"], $_REQUEST["p"], $_REQUEST["t"]);
    }
} //end of class Group_management
