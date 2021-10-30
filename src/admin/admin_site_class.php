<?php

if (!function_exists("array_combine")) {
    function array_combine($keys, $values)
    {
        if (count($keys) != count($values) || !is_array($keys) || !is_array($values)) {
            return false;
        }
        $combined = array();
        for ($i = count($keys); $i > 0; $i--) {
            $combined[current($keys)] = current($values);
            next($keys);
            next($values);
        }
        return $combined;
    }
}

class Admin_site
{

    var $admin_site_name = "GeoClassAuctions Administration";

    //e-mail pages, keep here for safe keeping
    var $email_pages = array(5,7,20,21,41,51,52,87,157,177,10166,10167,10168,10169,10170,10172,10174,10206,10207,10212,10213);

    //tables within the database
    var $block_email_domains = "geodesic_email_domains";
    var $classifieds_table = "geodesic_classifieds";
    var $classifieds_expired_table = "geodesic_classifieds_expired";
    var $sell_questions_table = "geodesic_classifieds_sell_questions";
    var $sell_types_table = "geodesic_classifieds_sell_question_types";
    var $classified_extra_table = "geodesic_classifieds_ads_extra";
    var $classified_categories_table = "geodesic_categories";
    var $classified_categories_languages_table = "geodesic_categories_languages";
    var $logins_table = "geodesic_logins";
    var $classified_sell_choices_table = "geodesic_classifieds_sell_question_choices";
    var $sell_choices_types_table = "geodesic_classifieds_sell_question_types";
    var $classified_questions_table = "geodesic_classifieds_sell_questions";
    var $questions_table = "geodesic_classifieds_sell_questions";
    var $states_table = "geodesic_states";
    var $text_message_table = "geodesic_text_messages";
    var $text_languages_table = "geodesic_text_languages";
    var $text_languages_messages_table = "geodesic_text_languages_messages";
    var $text_page_table = "geodesic_text_pages";
    var $text_subpages_table = "geodesic_text_subpages";
    var $confirm_table = "geodesic_confirm";
    var $confirm_email_table = "geodesic_confirm_email";
    var $userdata_table = "geodesic_userdata";
    var $userdata_history_table = "geodesic_userdata_history";
    var $badwords_table = "geodesic_text_badwords";
    var $countries_table = "geodesic_countries";
    var $ad_configuration_table = "geodesic_classifieds_ad_configuration";
    var $choices_table = "geodesic_choices";
    var $html_allowed_table = "geodesic_html_allowed";
    var $form_messages_table = "geodesic_classifieds_messages_form";
    var $past_messages_table = "geodesic_classifieds_messages_past";
    var $past_messages_recipients_table = "geodesic_classifieds_messages_past_recipients";
    var $site_configuration_table = "geodesic_classifieds_configuration";
    var $classified_ad_filter_table = "geodesic_ad_filter";
    var $classified_ad_filter_categories_table = "geodesic_ad_filter_categories";
    var $user_communications_table = "geodesic_user_communications";
    var $file_types_table = "geodesic_file_types";
    var $images_urls_table = "geodesic_classifieds_images_urls";
    var $classified_groups_table = "geodesic_groups";
    var $user_groups_price_plans_table = "geodesic_user_groups_price_plans";
    var $classified_expirations_table = "geodesic_classifieds_expirations";
    var $credit_choices = "geodesic_classifieds_credit_choices";
    var $classified_user_subscriptions_table = "geodesic_classifieds_user_subscriptions";
    var $classified_subscription_choices_table = "geodesic_classifieds_subscription_choices";
    var $price_plan_table = "geodesic_classifieds_price_plans";
    var $classified_price_plans_categories_table = "geodesic_classifieds_price_plans_categories";
    var $price_plans_increments_table = "geodesic_classifieds_price_increments";
    var $price_plans_extras_table = "geodesic_classifieds_price_plans_extras";
    var $font_page_table = "geodesic_font_pages";
    var $font_sub_page_table = "geodesic_font_subpages";
    var $font_element_table = "geodesic_font_elements";
    var $paypal_transaction_table = "geodesic_paypal_transactions";
    var $worldpay_configuration_table = "geodesic_worldpay_settings";
    var $worldpay_transaction_table = "geodesic_worldpay_transactions";
    var $cc_choices = "geodesic_credit_card_choices";
    var $banners_table = "geodesic_banners";
    var $banner_category_zones_table = "geodesic_banners_category_zones";
    var $registration_configuration_table = "geodesic_registration_configuration";
    var $registration_choices_table = "geodesic_registration_question_choices";
    var $registration_choices_types_table = "geodesic_registration_question_types";
    var $currency_types_table = "geodesic_currency_types";
    var $classified_price_plan_lengths_table = "geodesic_price_plan_ad_lengths";
    var $classified_subscription_holds_table = "geodesic_classifieds_user_subscriptions_holds";
    var $attached_price_plans = "geodesic_group_attached_price_plans";
    var $balance_transactions = "geodesic_balance_transactions";
    var $invoices_table = "geodesic_invoices";
    var $version_table = "geodesic_version";
    var $sessions_table = "geodesic_sessions";
    var $nochex_transaction_table = "geodesic_nochex_transactions";
    var $nochex_settings_table = "geodesic_nochex";
    var $auction_payment_types_table = "geodesic_payment_types";
    var $final_fee_table = "geodesic_auctions_final_fee_price_increments";

    var $pages_table = "geodesic_pages";
    var $pages_sections_table = "geodesic_pages_sections";
    var $pages_text_table = "geodesic_pages_messages";
    var $pages_text_languages_table = "geodesic_pages_messages_languages";
    var $pages_languages_table = "geodesic_pages_languages";
    var $pages_modules_sections_table = "geodesic_pages_modules_sections";

    var $bid_table = "geodesic_auctions_bids";
    var $autobid_table = "geodesic_auctions_autobids";

    var $feedback_icons_table = "geodesic_auctions_feedback_icons";
    var $ip_ban_table = "geodesic_banned_ips";

    var $email_queue_table = "geodesic_email_queue";

    var $site_settings_table = "geodesic_site_settings";

    var $large_font;
    var $medium_font;
    var $small_font;
    var $font;
    var $font_color1;
    var $font_color2;

    var $extremely_large_font_tag = "<font face=arial,helvetica size=6 color=#000000>";
    var $very_large_font_tag = "<font face=arial,helvetica size=4 color=#000000>";
    var $large_font_tag = "<font face=arial,helvetica size=3 color=#000000>";
    var $medium_error_font_tag = "<font face=arial,helvetica size=2 color=#880000>";
    var $medium_font_tag = "<font face=arial,helvetica size=2 color=#000000>";
    var $small_font_tag = "<font face=arial,helvetica size=1 color=#000000>";
    var $very_large_font_tag_light = "<font face=arial,helvetica size=4 color=#FFFFFF>";
    var $large_font_tag_light = "<font face=arial,helvetica size=3 color=#FFFFFF>";
    var $medium_font_tag_light = "<font face=arial,helvetica size=2 color=#FFFFFF>";
    var $small_font_tag_light = "<font face=arial,helvetica size=1 color=#FFFFFF>";

    //var $row_count;

    // Template data
    var $head_html = "";
    var $additional_head_html = "";
    public static $headtag = '';
    var $additional_body_tag_attributes = " style='margin:0;'";
    var $footer_html = "";
    var $template = "";
    var $title = "";
    var $description = "";
    var $body = "";
    var $header_image = "";

    var $messages = array();
    var $data_missing_error_message = "Your request could not be completed: missing data";
    var $internal_error_message = "There was an internal error";
    var $data_error_message = "Not enough data to complete request";
    var $page_text_error_message = "No text connected to this page";
    var $no_pages_message = "No pages to list";
    var $row_count = 0;
    var $sql_query;
    var $category_dropdown_name_array = array();
    var $category_dropdown_id_array = array();
    var $page_id;

    var $error_message;

    var $page_widths = array(600,760,980,1110);

    var $debug = 0;
    var $debug_attach_modules = 0;

    var $category_tree_array = array();
    var $subcategory_array = array();
    var $images_to_display = array();
    var $dropdown_body;
    var $configuration_data;
    var $ad_configuration_data;
    var $stage = 0;
    var $time_shift = 0;
    var $time_shift_i = 0;

    var $product_configuration;

    var $admin_icon;

    var $site_result_message;

    //  *****************************************
    //  *****************************************
    //  *****************************************
    //  *****************************************
    //  TODO Fix this later
    var $auctions_table = "geodesic_classifieds";
    //  *****************************************
    //  *****************************************
    //  *****************************************
    /**
     * The DataAccess object
     * @var DataAccess
     */
    var $db;
//########################################################################

    public function __construct()
    {
        $this->db = DataAccess::getInstance();
        $this->configuration_data = Admin_site::getConfigurationData();

        $this->product_configuration = geoPC::getInstance();
    } //end of function Admin_site

//########################################################################

    function getConfigurationData()
    {
        if (PHP5_DIR) {
            $db = DataAccess :: getInstance();
        } else {
            $db = & DataAccess :: getInstance();
        }
        return ($db->get_site_settings(true));
    }
//########################################################################
    /**
     * A handy and generic way to add stuff to the head of the page
     *
     * @param string $add
     */
    function header($add)
    {
        if ($add) {
            $view = geoView::getInstance();
            $view->headHtml .= $add;
        }
    }

//########################################################################

    function admin_footer($db)
    {
        include_once("admin_footer.php");
        return true;
    } //end of function admin_footer

//########################################################################

    function securityCheck()
    {
        $children = array();

        if (strlen(PHP5_DIR) > 0) {
            $db = DataAccess :: getInstance();
            $product_configuration = geoPC::getInstance();
        } else {
            $db = & DataAccess :: getInstance();
            $product_configuration =& geoPC::getInstance();
        }
        $deleted_files = array(
            'geodiagnostic.php' => 'Used by Geo Support for diagnostic purposes, delete if Geo Support is finished working on your site.',
            'add_storefront_template.php' => 'Delete, this file no longer used.',
            'reqtest.php' => 'Delete after you have finished the Storefront installation.',
            'security_image.php' => 'File no longer used from this directory, delete the file to remove this message.',
            'geodesic_zip_codes1.sql' => 'Delete once GeoZipSearch installation is complete.',
            'geodesic_zip_codes2.sql' => 'Delete once GeoZipSearch installation is complete.',
            'zip_code_search_redirect1.php' => 'Delete once GeoZipSearch installation is complete.',
            'zip_code_search_redirect2.php' => 'Delete once GeoZipSearch installation is complete.',
            'zip_code_search_upgrade1.php' => 'Delete once GeoZipSearch installation is complete.',
            'zip_code_search_upgrade2.php' => 'Delete once GeoZipSearch installation is complete.',
            'geodatas_import.php' => 'Delete once import of geodatas.net data is complete.',
            'upgrade_classified.php' => 'Delete once the update process is complete.',
            'upgrade_classauction.php' => 'Delete once the update process is complete.',
            'upgrade_enterprise_classified.php' => 'Delete once the update process is complete.',
            'upgrade_auctions_enterprise.php' => 'Delete once the update process is complete.',
            'upgrade_auctions_premier.php' => 'Delete once the update process is complete.',
            'upgrade_basic_classifieds.php' => 'Delete once the update process is complete.',
            'upgrade_premier_classifieds.php' => 'Delete once the update process is complete.',
            'classes/storefront/' => 'Delete, this belongs to old version of Storefront.  (Make sure you have updated Storefront addon to latest version)',
            'admin/storefront/' => 'Delete, this belongs to old version of Storefront.  (Make sure you have updated Storefront addon to latest version)',
            'classes/swfupload/' => 'Delete, this old 3rd party library is no longer used, and poses a potential security risk.',
            'classes/php5_classes/swift/' => 'Delete, this old 3rd party library is no longer used, and may contain potential security risks.',
            'addons/security_image/color_palette.php' => 'Delete, no longer used and contains possible security vulnerabilities.',

            //ADD NEW FILES AND INSTRUCTIONS HERE!!
            //'file_or_folder_name' => 'instructions'
            //filename relative to base dir.
        );
        if (!defined('IAMDEVELOPER')) {
            //Add files that are typical for fresh install/update, but should
            //be skipped on development installations
            $deleted_files = array_merge(
                array (
                    'sql/' => 'Delete once software installation or update is complete.',
                    'setup/' => 'Delete once software installation or update is complete.',
                    'upgrade/' => 'Delete once software installation or update is complete.',
                    'pre_setup/' => 'Delete once software installation or update is complete.'
                ),
                $deleted_files
            );
        }
        $needs_deleted = array();
        $keys = array_keys($deleted_files);
        foreach ($keys as $filename) {
            if (file_exists(GEO_BASE_DIR . $filename)) {
                $needs_deleted[$filename] = $deleted_files[$filename];
            }
        }
        if (count($needs_deleted) > 0) {
            $children = array();
            foreach ($needs_deleted as $filename => $reason) {
                $children[GEO_BASE_DIR . "<strong>$filename</strong>"] = $reason;
            }
            Notifications::addSecurityAlert("The files or directories listed below may pose a security risk to leave on your site after they are no longer needed:", $children);
        }

        //check if config still has beta switches.  requires upgrade folder
        //to fix, so putting it right before the upgrade folder check.
        if (defined('BETA_SWITCHES')) {
            Notifications::addNoticeAlert('Your config.php file may still contains "Beta Switches" which are no longer used.  All beta switches are now controlled from the admin, in <strong>Admin Tools & Settings > BETA Tools > BETA Settings</strong>.  You can remove the "BETA SETTINGS" section from your config.php file to remove this notice.');
        }

        //see if username and pass are set to default values.
        $sql = 'SELECT * FROM ' . $db->geoTables->logins_table . ' WHERE id=1';
        $result = $db->GetRow($sql);
        $hashed_pass = $product_configuration->get_hashed_password('admin', 'geodesic', $db->get_site_setting('admin_pass_hash'), $result['salt']);
        $salt = '';
        if (is_array($hashed_pass)) {
            $salt = $hashed_pass['salt'];
            $hashed_pass = $hashed_pass['password'];
        }
        if (!defined('IAMDEVELOPER') && is_array($result) && $result['username'] == 'admin' && $result['password'] == $hashed_pass && $result['salt'] == $salt) {
            //still using default user and pass?
            Notifications::addSecurityAlert("Please change this administration's <b>username</b> and <b>password</b>. They are currently set to the installation defaults, and may be changed <a href='index.php?page=admin_tools_password&mc=admin_tools_settings'>here</a>");
        }

        //Make sure 2CO has the secret word entered
        $twoco = geoPaymentGateway::getPaymentGateway('twocheckout');
        if ($twoco && $twoco->getEnabled()) {
            if (!$twoco->get('secret')) {
                Notifications::addNoticeAlert("<strong>2Checkout Payment Gateway
				needs your attention!</strong>  As of version 6.0.0, 2Checkout
				has newer setup instructions, and new gateway settings that
				need to be filled out.
				<br /><br />
				In the admin panel, at <a href='index.php?page=payment_gateways'>Payments > Payment Gateways</a>,
				click <strong>Configure</strong> for the 2Checkout payment gateway.
				Read and follow the updated setup instructions below the 2Checkout
				settings.
				<br /><br />
				This notice will be removed once you have entered
				the <strong>2Checkout Secret Word</strong> setting.");
            }
        }
        unset($twoco);
    }


    function strip_tags($info)
    {
        $info = str_replace("'", "''", $info);
        $info = stripslashes($info);
        $info = strip_tags($info);
        return $info;
    }

//########################################################################

    function push_messages_into_array($result)
    {
        //take the database message result and push the contents into an array
        while ($show = $result->FetchRow()) {
            $this->messages[$show["message_id"]] = $show["display"];
        }
    } //end of function push_messages_into_array

//########################################################################

    function push_configuration_into_array($result)
    {
        //take the database message result and push the contents into an array
        while ($show = $result->FetchRow()) {
            $configuration[$show["reference"]] = $show["value"];
        }
        return $configuration;
    } //end of function push_messages_into_array

//########################################################################

    function site_error($db_error = 0, $file = 0, $line = 0)
    {
        //check to see if debugging
        if ($this->debug) {
            highlight_string(print_r(debug_backtrace(), 1));
            echo "<table cellpadding=3 cellspacing=1 border=0>
					<tr>
						<td class=very_large_font>
							There has been a database error
						</td>
					</tr>
					<tr>
						<td class=medium_error_font>";
            if ($db_error) {
                echo "		With the following sql error:" . $db_error . "<br>";
            }
            if ($file || $line) {
                echo "		This <b>site_error()</b> was called from . . .
							" . (($file) ? "<br>FILE = <b>$file</b>" : "") . "
							" . (($line) ? "<br>LINE = <b>$line</b>" : "");
            }
            echo "		</td>
					</tr>
				</table>";
        } else {
            echo "<table cellpadding=3 cellspacing=1 border=0>\n";
            echo "<tr>\n\t<td class=very_large_font>There has been a error.<br>
				Please try again. \n\t</td>\n</tr>\n";
            echo "</table>\n";
        }
    } //end of function site_error

//#########################################################################

    function buildSelectOptions($values, $captions, $selectedValue = null)
    {
        $options = array_combine($values, $captions);
        if (false === $options) {
            Admin_site::error("Internal error", __LINE__, __FILE__);
        }
        $html = "";
        if (null === $selectedValue) {
            foreach ($options as $value => $caption) {
                $html .= "
					<option value='{$value}'>{$caption}</option>";
            }
        } elseif (defined('DEMO_MODE')) {
            $this->body .= "<div style='width: 100%; color: red;font-weight: bold; font-size: 75%;'>
				DEMO MODE, We have removed the ability to actually
				save changes in this software demonstration.<br> This has been
				done to help maintain the integrity of the actual presentation
				of the demo.</div>\n";
        } else {
            foreach ($options as $value => $caption) {
                $selected = ($value == $selectedValue) ? " selected" : "";
                $html .= "
					<option value='{$value}'{$selected}>{$caption}</option>";
            }
        }
        return $html;
    }

//#########################################################################

    function inputError($message = "Invalid input", $line, $file)
    {
        Admin_site::error($message, $line, $file);
    }

//#########################################################################

    function requestError($message = "Invalid request", $line, $file)
    {
        Admin_site::error($message, $line, $file);
    }

//#########################################################################

    function error($message = "Error", $line, $file, $isUserFriendly = true)
    {
        if ($isUserFriendly) {
            echo "
				<div style='width: 100%; color: red;font-weight: bold; font-size: 150%;'>
					" . $message . ' : ' . $file . ' ' . $line . "
				</div>";
        } else {
            echo "
				<div style='width: 100%; color: red;font-weight: bold; font-size: 150%;'>
					An error has occurred
				</div>";
        }
        trigger_error("ERROR SQL: " . $message . " on line " . $line . " of file " . $file);
        include_once(GEO_BASE_DIR . 'app_bottom.php');
        exit;
    }

    function basic_input_box($input_title, $explanation, $input_name, $input_value = "", $error = "")
    {
        echo "<tr>\n\t<td valign=top align=right class=medium_font>" . $input_title . "<br>";
        if (strlen(trim($error)) > 0) {
            echo $error . "<br>\n\t";
        }
        echo "<span class=small_font>" . $explanation . "</span>\n\t</td>\n\t";
        echo "<td valign=top class=medium_font>\n\t<input type=text name=" . $input_name . " length=30 ";
        if (strlen(trim($input_value)) > 0) {
            echo "value=\"" . $input_value . "\"";
        }
        echo " maxlength=100>\n\t";
        echo "</td>\n</tr>\n";
    }


//########################################################################

    function get_category_name($db, $category_id = 0)
    {
        if ($category_id) {
            $this->sql_query = "select category_name from " . $this->classified_categories_languages_table . " where language_id = 1 and category_id = " . $category_id;
            $category_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$category_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($category_result->RecordCount() == 1) {
                $show = $category_result->FetchRow();
                return urldecode(stripslashes($show["category_name"]));
            } else {
                //just display the user_id
                return false;
            }
        } else {
            return "Main";
        }
    } //end of function get_category_name

//########################################################################

    function get_category_description($db, $category_id = 0)
    {
        if ($category_id) {
            $this->sql_query = "select description from " . $this->classified_categories_languages_table . " where language_id = 1 and category_id = " . $category_id;
            $category_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$category_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($category_result->RecordCount() == 1) {
                $show = $category_result->FetchRow();
                return urldecode(stripslashes($show["description"]));
            } else {
                //just display the user_id
                return false;
            }
        } else {
            return "Main";
        }
    } //end of function get_category_description

//########################################################################

    function get_section($db, $section_id = 0)
    {
        if ($section_id) {
            $this->sql_query = "select * from " . $this->pages_sections_table . " where section_id = " . $section_id;
            $section_result = $this->db->Execute($this->sql_query);
            if (!$section_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($section_result->RecordCount() == 1) {
                $show = $section_result->FetchRow();
                return $show;
            } else {
                //just display the user_id
                return false;
            }
        }
        return true;
    } //end of function get_section

//########################################################################

    function get_page(&$db, $page_id = 0)
    {
        $pages = array(91,101);
        if ($page_id && (geoPC::is_ent() || !in_array($page_id, $pages))) {
            $this->sql_query = "select * from " . $this->pages_table . " where page_id = " . $page_id;
            $page_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$page_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($page_result->RecordCount() == 1) {
                $show = $page_result->FetchRow();
                return $show;
            } else {
                //just display the user_id
                return false;
            }
        }
        return true;
    } //end of function get_page

//########################################################################

    function get_price_plan_name($db, $price_plan_id = 0)
    {
        if ($price_plan_id) {
            $this->sql_query = "select name from " . $this->price_plan_table . " where price_plan_id = " . $price_plan_id;
            $price_plan_result = $this->db->Execute($this->sql_query);
            if (!$price_plan_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($price_plan_result->RecordCount() == 1) {
                $show = $price_plan_result->FetchRow();
                return $show["name"];
            } else {
                //just display the user_id
                return false;
            }
        } else {
            return false;
        }
    } //end of function get_price_plan_name

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_price_plan($db, $price_plan_id = 0, $category_id = 0)
    {
        //echo "TOP OF GET_PRICE_PLAN<BR>\n";
        //echo $price_plan_id." is \$price_plan_id<bR>\n";
        //echo $category_id." is the \$category_id<br>\n";
        if ($price_plan_id) {
            $this->sql_query = "select * from " . $this->price_plan_table . " where price_plan_id = " . $price_plan_id;
            $price_plan_result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$price_plan_result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
                return false;
            } elseif ($price_plan_result->RecordCount() == 1) {
                $show = $price_plan_result->FetchRow();

                if (geoPC::is_ent() || geoPC::is_premier()) {
                    //echo "checking for category specific price plan<Br>\n";
                    //check for category specific
                    $category_next = $category_id;
                    if ($category_next) {
                        do {
                            $this->sql_query = "select category_id,parent_id from " . $this->classified_categories_table . "
								where category_id = " . $category_next;
                            $category_result =  $this->db->Execute($this->sql_query);
                            //echo $this->sql_query."<br>\n";
                            if (!$category_result) {
                                if ($this->debug_sell) {
                                    echo $this->sql_query . " is the query<br>\n";
                                }
                                $this->error_message = $this->messages[3501];
                                return false;
                            } elseif ($category_result->RecordCount() == 1) {
                                $show_category = $category_result->FetchRow();
                                $this->sql_query = "select * from " . $this->classified_price_plans_categories_table . "
									where category_id = " . $show_category["category_id"] . " and price_plan_id = " . $price_plan_id;
                                $category_price_plan_result =  $this->db->Execute($this->sql_query);
                                //echo $this->sql_query."<br>\n";
                                if ($category_price_plan_result->RecordCount() == 1) {
                                    $overriding_category = $show_category["category_id"];
                                    $show_category_price_plan = $category_price_plan_result->FetchRow();
                                }
                                $category_next = $show_category["parent_id"];
                            } else {
                                return false;
                            }
                        } while (($show_category["parent_id"] != 0 ) && ($overriding_category == 0));
                    }
                }

                if ($overriding_category != 0) {
                    if ($show["type_of_billing"] == 2 && geoPC::is_ent() || geoPC::is_premier()) {
                        //there is an overriding category specific price plan
                        //overwrite the returns from the base price plan with these
                        $show["featured_ad_price"] = (geoPC::is_ent()) ? $show_category_price_plan["featured_ad_price"] : 0;
                        $show["featured_ad_price_2"] = (geoPC::is_ent()) ? $show_category_price_plan["featured_ad_price_2"] : 0;
                        $show["featured_ad_price_3"] = (geoPC::is_ent()) ? $show_category_price_plan["featured_ad_price_3"] : 0;
                        $show["featured_ad_price_4"] = (geoPC::is_ent()) ? $show_category_price_plan["featured_ad_price_4"] : 0;
                        $show["featured_ad_price_5"] = (geoPC::is_ent()) ? $show_category_price_plan["featured_ad_price_5"] : 0;
                        $show["bolding_price"] = (geoPC::is_ent()) ? $show_category_price_plan["bolding_price"] : 0;
                        if ($this->ag) {
                            $show["attention_getter_price"] = $show_category_price_plan["attention_getter_price"];
                        }
                        $show["charge_per_picture"] = $show_category_price_plan["charge_per_picture"];
                        $show["better_placement_charge"] = (geoPC::is_ent()) ? $show_category_price_plan["better_placement_charge"] : 0;
                        //echo "returning category specific not flat fee<BR>\n";
                        return $show;
                    } else {
                        //this is a fee type
                        $show_category_price_plan["type_of_billing"] = 1;
                        $show_category_price_plan["num_free_pics"] = (geoPC::is_ent()) ? $show_category_price_plan["num_free_pics"] : 0;
                        //$show_category_price_plan["charge_per_picture"] = $show["charge_per_picture"];
                        $show_category_price_plan["charge_percentage_at_auction_end"] = $show["charge_percentage_at_auction_end"];
                        //echo "returning category specific flat fee<BR>\n";
                        return $show_category_price_plan;
                    }
                } else {
                    //echo "returning base price plan<bR>\n";
                    return $show;
                }
            } else {
                //just display the user_id
                return false;
            }
        } else {
            return false;
        }
    } //end of function get_price_plan

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_current_status($db, $user_id = 0)
    {
        if ($user_id) {
            $this->sql_query = "select status from " . $this->logins_table . " where id = " . $user_id;
            $result = $this->db->Execute($this->sql_query);

            if (!$result) {
                //echo $this->sql_query." is the state query<br>\n";
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() == 1) {
                $show_status = $result->FetchRow();
                return $show_status["status"];
            } else {
                $this->error_message = $this->data_error_message;
                return false;
            }
        } else {
            //no user id
            return false;
        }
    } //end of function get_current_status

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_user_data($user_id = 0, $userid = 0)
    {
        if (is_object($user_id)) {
            $user_id = $userid;
        }
        if ($user_id) {
            $db = DataAccess::getInstance();
            $this->sql_query = "select * from " . $this->userdata_table . " where id = " . $user_id;
            $result = $this->db->Execute($this->sql_query);

            if (!$result) {
                //echo $this->sql_query." is the state query<br>\n";
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() == 1) {
                $show_user = $result->FetchRow();
                return $show_user;
            } else {
                $this->error_message = $this->data_error_message;
                return false;
            }
        } else {
            //no user id
            return false;
        }
    } //end of function get_user_data

//########################################################################

    // $page is the page you would like the table to be on, $table_id number corresponding with $table_num
    function display_user_data($db, $user_id = 0, $page = 1, $table_id = 0)
    {
        if (!$user_id) {
            return false;
        }

        $user_data = $this->get_user_data($user_id);
        if (!$user_data) {
            return false;
        }
        $this->body .= geoAdmin::m();
        $username = htmlspecialchars($user_data['username']);

        //display this users information
        $this->body .= "
		<div class=\"page-title1\">User: <span class='color-primary-two'><span style='font-weight: bold; white-space:nowrap;'>" . $user_data["firstname"] . " " . $user_data["lastname"] . "</span>	| " . $user_data["username"] . "</span> <span class='color-primary-six' style='font-size: 0.8em; white-space:nowrap;'>(User ID#: " . $user_id . ")</span>
		<a href='index.php?mc=users&amp;page=users_edit&amp;b={$user_data["id"]}' title='Edit User' alt='Edit User'><i class=\"fa fa-pencil edit-pencil\"></i></a></div>
			";

        $this->body .= "
			<fieldset id='ViewUserData'><legend>User Profile</legend>

                  <div class='x_content'>
                    <div class='col-md-2 col-sm-2 col-xs-12 profile_left'>
                      <div class='profile_img'>
                        <div id='crop-avatar'>";

        $profile = geoAddon::getUtil('profile_pics');
        if ($profile) {
            $this->body .= $profile->get_img_tag($user_id);
        }

        $this->body .= "
                        </div>
                      </div>
                      <h3>" . $user_data["firstname"] . " " . $user_data["lastname"] . "</h3>

                      <div class='color-primary-two' style='font-size: 1.7rem;'>" . $user_data["username"] . "</div>

					  <div class='color-primary-six' style='font-size: 1.2rem; margin: 5px 0;'>User ID#: " . $user_id . "</div>";

        if ($user_id != 1) {
            $this->body .= "<div class='user_rating' style='margin: 5px;'>
	                      	" . geoUserRating::render($user_id) . "
	                      	 <a href='index.php?mc=users&page=users_ratings_detail&b=$user_id' style='font-size: 0.9em;'>Rating Details</a>
	                      </div>
	
						  <div style='font-size: 1.1rem; padding: 10px 0;'>";

            $address = ((strlen(trim($user_data["address"])) > 0) ? $user_data["address"] : "") .
                ((strlen(trim($user_data["address_2"])) > 0) ? "&nbsp;(address 1)<br />" . $user_data["address_2"] . "&nbsp;(address 2)" : "") .
                ((strlen(trim($user_data["city"])) > 0) ? "<br />" . $user_data["city"] : "") .
                ((strlen(trim($user_data["state"])) > 0) ? ", " . $user_data["state"] : "") .
                ((strlen(trim($user_data["zip"])) > 0) ? " " . $user_data["zip"] : "") .
                ((strlen(trim($user_data["country"])) > 0) ? "<br />" . $user_data["country"] : "");
            if ($address) {
                $this->body .= "
							<div style='margin:3px 0;'>
								<i class='fa fa-map-marker user-profile-icon'></i>
								$address
							</div>";
            }


            $this->body .= "<div style='margin:3px 0;'>
								" . ($user_data['phone'] ? "<i class='fa fa-phone user-profile-icon'></i> {$user_data['phone']}" : "") . "
								" . ($user_data['phone2'] ? "<br /><i class='fa fa-phone user-profile-icon'></i> {$user_data['phone2']}" : "") . "
								" . ($user_data['fax'] ? "<br /><i class='fa fa-phone user-profile-icon'></i> f: {$user_data['fax']}" : "") . "
						  	</div>";

            if (strlen(trim($user_data["url"])) > 0) {
                $url = (strpos($user_data['url'], 'http') === 0) ? $user_data['url'] : 'http://' . $user_data['url'];
                $this->body .= "<div style='margin:3px 0;'><i class='fa fa-external-link user-profile-icon'></i> <a href='$url' onclick='window.open(this.href); return false;'>$url</a></div>";
            }
            $this->body .= "</div>";
        }


        $this->body .= "
					  <div style='text-align: center; margin: 5px 0;'>
						  <a href='index.php?mc=users&amp;page=users_edit&amp;b={$user_data["id"]}' class='btn btn-success btn-xs' style='margin:0;'><i class='fa fa-edit m-right-xs'></i> Edit Profile</a>
						  <br /><br />

						  <a href='index.php?page=admin_messaging_send&amp;b[{$user_data['id']}]={$username}'><i class='fa fa-envelope'></i> Send Message</a>
						  <br /><br />

						  <a href='index.php?page=orders_list&amp;narrow_order_status=all&amp;narrow_username={$username}'><i class=\"fa fa-edit\"></i> View All Orders</a>
						  <br /><br />

						  <a href='index.php?page=orders_list_items&amp;narrow_item_status=all&amp;narrow_username={$username}'><i class=\"fa fa-sliders\"></i> View All Order Items</a>
						  <br /><br />

						  <a href='index.php?mc=users&page=users_remove&b={$user_data["id"]}' class='btn btn-danger btn-xs' style='margin:0;'><i class=\"fa fa-trash-o\"></i> Remove User</a>
					  </div>

                    </div>
                    <div class='col-md-10 col-sm-10 col-xs-12'>


							<ul class='tabList'>
								<li id='profileTab' class='activeTab'>Profile</li>
								<li id='listingsTab'>Listings</li>
							</ul>
							<div class='tabContents'>
								<div id='profileTabContents' style='background-color: #FFF; border: 1px solid #DDD; padding-top:2px;' class='table-responsive'>";

        $this->body .= geoHTML::addOption('Name:', $user_data['firstname'] . ' ' . $user_data['lastname']);
        $this->body .= geoHTML::addOption('E-Mail:', $user_data['email']);
        if (strlen(trim($user_data['email2'])) > 0) {
            $this->body .= geoHTML::addOption('2nd E-Mail:', $user_data['email2']);
        }
        $this->body .= geoHTML::addOption('Username:', $user_data['username']);
        $this->body .= geoHTML::addOption('User ID:', $user_data['id']);

        $current_status = ($this->get_current_status($db, $user_id) == 1) ? "Active" : "Suspended";
        $this->body .= geoHTML::addOption('Status:', $current_status);

        if ($user_data['admin_note']) {
            $this->body .= geoHTML::addOption('Admin Note: ', nl2br(geoString::fromDB($user_data['admin_note'])));
        }

        if (strlen(trim($user_data["company_name"])) > 0) {
            $this->body .= geoHTML::addOption('Company Name:', $user_data['company_name']);
        }
        if ($user_data["business_type"] == 1) {
            $showBusType = "Individual";
        } elseif ($user_data["business_type"] == 2) {
            $showBusType = "Business";
        } else {
            $showBusType = "none";
        }
        $this->body .= geoHTML::addOption('Registration Type:', $showBusType);






        $sql = "select * from " . $this->registration_configuration_table;
        if ($this->debug) {
            echo $sql . "<br>\n";
        }
        $registration_configuration = $this->db->GetRow($sql);

        for ($i = 1; $i < 11; $i++) {
            if (strlen(trim($user_data["optional_field_" . $i])) > 0) {
                $val = $user_data["optional_field_" . $i];
                $this->body .= geoHTML::addOption($registration_configuration['registration_optional_' . $i . '_field_name'], $val);
            }
        }

        $this->body .= geoHTML::addOption('Date Registered:', (($user_data["date_joined"] != 0) ? date("M d,Y G:i - l", $user_data["date_joined"]) : "not available"));
        $this->body .= geoHTML::addOption('Time of Last Login:', (($user_data["last_login_time"] != 0) ? $user_data["last_login_time"] : "Never"));
        $this->body .= geoHTML::addOption('Last known IP Address:', (($user_data["last_login_ip"]) ? $user_data["last_login_ip"] : "Unknown"));

        if (geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic()) {
            $sql = "select * from " . $this->user_groups_price_plans_table . " where id = " . $user_id;
            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $show_user_stuff = $this->db->GetRow($sql);

            $group_stuff = $this->get_group($db, $show_user_stuff["group_id"]);
            if ($group_stuff) {
                //current group
                $this->body .= geoHTML::addOption('User Group Attached to:', $group_stuff["name"]);
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
                if ($auction_price_plan) {
                    $this->body .= geoHTML::addOption('Auction Price Plan:', "<a href='index.php?mc=pricing&page=pricing_edit_plans&g={$show_user_stuff["auction_price_plan_id"]}'>{$auction_price_plan["name"]}</a>");
                }
                if ($classified_price_plan) {
                    $this->body .= geoHTML::addOption('Classified Price Plan:', "<a href='index.php?mc=pricing&page=pricing_edit_plans&g={$show_user_stuff["price_plan_id"]}'>{$classified_price_plan["name"]}</a>");
                }

                if ($auction_price_plan["type_of_billing"] == 2 || $classified_price_plan["type_of_billing"] == 2) {
                    //charge by subscription -- display when expire
                    $sql = "select * from " . $this->classified_user_subscriptions_table . " where user_id = " . $user_id;
                    //echo $sql."<br>\n";
                    $show_subscription = $this->db->GetRow($sql);
                    $subR = array();
                    if (!$show_subscription) {
                        $sub = 'expired';
                    } else {
                        $sub = date("M d, Y H:i:s", $show_subscription["subscription_expire"]) . " - - <a href='index.php?mc=users&amp;page=users_subs_delete&amp;b=$user_id&amp;auto_save=1' class='lightUpLink'>delete subscription</a>";

                        if ($show_subscription['recurring_billing']) {
                            $recurring = geoRecurringBilling::getRecurringBilling($show_subscription['recurring_billing']);
                            if ($recurring && $recurring->getId()) {
                                $status = $recurring->getStatus();
                                if ($status != geoRecurringBilling::STATUS_CANCELED) {
                                    $status .= " - - <a href='index.php?mc=users&amp;page=users_subs_delete&amp;b=$user_id&amp;only_cancel_recurring=1&amp;auto_save=1' class='lightUpLink'>Cancel Recurring Payments</a>";
                                }

                                $subR[] = "<strong>Status:</strong>: " . $status;
                                $gateway = $recurring->getGateway();
                                $gatewayTitle = ($gateway) ? $gateway->getTitle() : 'Unknown';
                                $subR[] = "<strong>Gateway:</strong>: " . $gatewayTitle;
                                //calculate duration in days
                                $duration = floor($recurring->getCycleDuration() / (60 * 60 * 24));
                                $subR[] = "<strong>Cost:</strong>: " . geoString::displayPrice($recurring->getPricePerCycle()) . " every $duration days";
                            }
                        }
                    }
                    if (!count($subR)) {
                        $subR[] = 'None configured.';
                    }
                    $sub .= "<br /><a href='index.php?mc=users&page=users_subs_change&b=$user_id&c={$show_subscription["subscription_id"]}'>change expiration</a>";
                    $this->body .= geoHTML::addOption('Subscription Expires', $sub);
                    $this->body .= geoHTML::addOption('Subscription Recurring Payments', implode('<br />', $subR));
                }
            }
            //Let addons add stuff
            $this->body .= geoAddon::triggerDisplay('Admin_site_display_user_data', $user_id);
            //show info from order items
            $this->body .= geoOrderItem::callDisplay('Admin_site_display_user_data', $user_id);
        }


        $this->body .= "
								</div>
								<div id='listingsTabContents' class='table-responsive'>";
        $listingDataToShow = false;
                                $this->body .= "<table cellpadding=2 cellspacing=1 border=0 align=center width=\"100%\">
		";


        $limit_phrase = array();
        $page_array = array();

        for ($x = 1; $x < 7; $x++) {
            if ($page && ($x == $table_id)) {
                if ($page > 1) {
                    $limit = ((($page - 1) * 25));
                    $limit_phrase[$x] = " " . ($limit) . ",25 ";
                    $page_array[$x] = $page;
                } else {
                    $page_array[$x] = 1;
                    $limit_phrase[$x] = " 0,25";
                }
            } else {
                $page_array[$x] = 1;
                $limit_phrase[$x] = " 0,25";
            }
        }

        if (geoMaster::is('classifieds')) {
            // Current Classifieds
            $table_num = 1;
            $total_transactions = 0;
            $sql = "select * from " . $this->classifieds_table . " where seller = " . $user_id . " and live = 1 and item_type = 1 order by date desc limit" . $limit_phrase[$table_num];
            $sql_count_query = "SELECT COUNT(*) AS `total_transactions` FROM " . $this->db->geoTables->classifieds_table . " WHERE `seller` = " . $user_id . " AND `live` = 1 AND `item_type` = 1";

            $total_result = $this->db->Execute($sql_count_query);
            if ($this->debug_transactions) {
                echo $sql_count_query . "<br>\n";
            }
            if (!$total_result) {
                if ($this->debug_transactions) {
                    echo $sql_count_query . "<br>\n";
                }
                $this->site_error($db->ErrorMsg());
                return false;
            }
            $total_result_row = $total_result->FetchRow();
            $total_transactions = $total_result_row['total_transactions'];

            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $current_result = $this->db->Execute($sql);

            if (!$current_result) {
                if ($this->debug) {
                    echo $sql . "<br>\n";
                    echo $this->db->ErrorMsg() . "<br>\n";
                }
                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->display_current_item($current_result, "<b>Current Classifieds</b>");
                if ($total_transactions > 25) {
                    $this->display_page_numbers($table_num, $total_transactions, $user_id, $page_array[$table_num]);
                }
                $this->body .= "
			<tr>
				<td colspan=8>&nbsp;</td>
			</tr>";
            }
        }


        if (geoMaster::is('auctions')) {
            // Current Auctions
            $table_num = 2;
            $total_transactions = 0;
            $sql = "select * from " . $this->classifieds_table . " where seller = " . $user_id . " and live = 1 and item_type = 2 order by date desc limit" . $limit_phrase[$table_num];
            $sql_count_query = "SELECT COUNT(*) AS `total_transactions` FROM " . $this->db->geoTables->classifieds_table . " WHERE `seller` = " . $user_id . " AND `live` = 1 AND `item_type` = 2";

            $total_result = $this->db->Execute($sql_count_query);
            if ($this->debug_transactions) {
                echo $sql_count_query . "<br>\n";
            }
            if (!$total_result) {
                if ($this->debug_transactions) {
                    echo $sql_count_query . "<br>\n";
                }
                $this->site_error($db->ErrorMsg());
                return false;
            }
            $total_result_row = $total_result->FetchRow();
            $total_transactions = $total_result_row['total_transactions'];

            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $current_result = $this->db->Execute($sql);

            if (!$current_result) {
                if ($this->debug) {
                    echo $sql . "<br>\n";
                    echo $this->db->ErrorMsg() . "<br>\n";
                }
                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->display_current_item($current_result, "<b>Current Auctions</b>");

                if ($total_transactions > 25) {
                    $this->display_page_numbers($table_num, $total_transactions, $user_id, $page_array[$table_num]);
                }
                $this->body .= "
			<tr>
				<td colspan=8>&nbsp;</td>
			</tr>";
            }
        }

        if (geoMaster::is('classifieds')) {
            // Expired ads
            $table_num = 3;
            $total_transactions = 0;
            $sql = "select * from " . $this->classifieds_table . " where seller = " . $user_id . " and live = 0 and ends < " . geoUtil::time() . " and item_type = 1 order by date desc limit" . $limit_phrase[$table_num];
            $sql_count_query = "SELECT COUNT(*) AS total_transactions FROM " . $this->classifieds_table . " WHERE seller = " . $user_id . " and live = 0 and ends < " . geoUtil::time() . " and item_type = 1";

            $total_result = $this->db->Execute($sql_count_query);
            if ($this->debug) {
                echo $sql_count_query . "<br>\n";
            }
            if (!$total_result) {
                if ($this->debug_transactions) {
                    echo $sql_count_query . "<br>\n";
                }
                $this->site_error($db->ErrorMsg());
                return false;
            }
            $total_result_row = $total_result->FetchRow();
            $total_transactions = $total_result_row['total_transactions'];

            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $current_result = $this->db->Execute($sql);
            if (!$current_result) {
                if ($this->debug) {
                    echo $sql . "<br>\n";
                    echo $this->db->ErrorMsg() . "<br>\n";
                }

                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->display_expired_item($current_result, "<b>Classifieds Recently Expired</b>");
                if ($total_transactions > 25) {
                    $this->display_page_numbers($table_num, $total_transactions, $user_id, $page_array[$table_num]);
                }
                $this->body .= "
				<tr>
					<td colspan=8>&nbsp;</td>
				</tr>";
            }

            // Expired ads (archived)
            $table_num = 4;
            $total_transactions = 0;
            $sql = "select * from " . $this->classifieds_expired_table . " where seller = " . $user_id . " and item_type = 1 order by date desc limit" . $limit_phrase[$table_num];
            $sql_count_query = "SELECT COUNT(*) AS total_transactions FROM " . $this->classifieds_expired_table . " WHERE seller = " . $user_id . " and item_type = 1";

            $total_result = $this->db->Execute($sql_count_query);
            if ($this->debug) {
                echo $sql_count_query . "<br>\n";
            }
            if (!$total_result) {
                if ($this->debug_transactions) {
                    echo $sql_count_query . "<br>\n";
                }
                $this->site_error($db->ErrorMsg());
                return false;
            }
            $total_result_row = $total_result->FetchRow();
            $total_transactions = $total_result_row['total_transactions'];

            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $current_result = $this->db->Execute($sql);
            if (!$current_result) {
                if ($this->debug) {
                    echo $sql . "<br>\n";
                    echo $this->db->ErrorMsg() . "<br>\n";
                }

                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->display_archived_item($current_result, "<b>Classifieds that are Archived</b>");
                if ($total_transactions > 25) {
                    $this->display_page_numbers($table_num, $total_transactions, $user_id, $page_array[$table_num]);
                }

                $this->body .= "
				<tr>
					<td colspan=8>&nbsp;</td>
				</tr>";
            }
        }

        if (geoMaster::is('auctions')) {
            // Expired auctions
            $table_num = 5;
            $total_transactions = 0;
            $sql = "select * from " . $this->classifieds_table . " where seller = " . $user_id . " and live = 0 and ends < " . geoUtil::time() . " and item_type = 2 order by date desc limit" . $limit_phrase[$table_num];
            $sql_count_query = "SELECT COUNT(*) AS `total_transactions` FROM " . $this->db->geoTables->classifieds_table . " where `seller` = " . $user_id . " AND `live` = 0 AND `ends` < " . geoUtil::time() . " AND `item_type` = 2";

            $total_result = $this->db->Execute($sql_count_query);
            if ($this->debug) {
                echo $sql_count_query . "<br>\n";
            }
            if (!$total_result) {
                if ($this->debug_transactions) {
                    echo $sql_count_query . "<br>\n";
                }
                $this->site_error($db->ErrorMsg());
                return false;
            }
            $total_result_row = $total_result->FetchRow();
            $total_transactions = $total_result_row['total_transactions'];

            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $current_result = $this->db->Execute($sql);
            if (!$current_result) {
                if ($this->debug) {
                    echo $sql . "<br>\n";
                    echo $this->db->ErrorMsg() . "<br>\n";
                }

                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->display_expired_item($current_result, "<b>Auctions Recently Expired</b>");
                if ($total_transactions > 25) {
                    $this->display_page_numbers($table_num, $total_transactions, $user_id, $page_array[$table_num]);
                }
                $this->body .= "
				<tr>
					<td colspan=8>&nbsp;</td>
				</tr>";
            }

            // archived auctions
            $table_num = 6;
            $total_transactions = 0;
            $sql = "select * from " . $this->classifieds_expired_table . " where seller = " . $user_id . " and item_type = 2 order by date desc limit" . $limit_phrase[$table_num];
            $sql_count_query = "SELECT COUNT(*) AS `total_transactions` FROM " . $this->db->geoTables->classifieds_expired_table . " where `seller` = " . $user_id . " AND `item_type` = 2";

            $total_result = $this->db->Execute($sql_count_query);
            if ($this->debug) {
                echo $sql_count_query . "<br>\n";
            }
            if (!$total_result) {
                if ($this->debug_transactions) {
                    echo $sql_count_query . "<br>\n";
                }
                $this->site_error($db->ErrorMsg());
                return false;
            }
            $total_result_row = $total_result->FetchRow();
            $total_transactions = $total_result_row['total_transactions'];

            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            $current_result = $this->db->Execute($sql);
            if (!$current_result) {
                if ($this->debug) {
                    echo $sql . "<br>\n";
                    echo $this->db->ErrorMsg() . "<br>\n";
                }

                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->display_archived_item($current_result, "<b>Auctions that are Archived</b>");
                if ($total_transactions > 25) {
                    $this->display_page_numbers($table_num, $total_transactions, $user_id, $page_array[$table_num]);
                }
                $this->body .= "
				<tr>
					<td colspan=8>&nbsp;</td>
				</tr>";
            }
        }

        if (geoMaster::is('auctions')) {
            // Display feedbacks
            $feedbackTable = geoTables::auctions_feedbacks_table;
            $userdataTable = geoTables::userdata_table;

            $sql = "select feedback.*,userdata.username from " . $feedbackTable . " as feedback, " . $userdataTable . " as userdata where feedback.rated_user_id = " . $user_id . " and done = 1 and userdata.id = feedback.rater_user_id order by date desc limit" . $limit_phrase[$table_num];
            $current_result = $this->db->Execute($sql);
            if (!$current_result) {
                trigger_error('ERROR: Couldn\t fetch user\'s feedbacks. ' . $this->db->ErrorMsg());
                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->body .= "
				<tr>
					<td colspan=2>
						<table class=\"table table-hover table-striped table-bordered\" style='background-color: #FFFFFF;'>
							<tr class='col_hdr'>
								<td colspan='6'>
									Feedbacks rating this user
								</td>
							</tr>
							<thead>
								<tr>
									<td>Rater</td>
									<td>Date</td>
									<td>Feedback</td>
									<td>Rate</td>
									<td>Edit Feedback</td>
									<td>Delete Feedback</td>
								</tr>
							</thead><tbody>";

                //$this->row_count = 0;
                while ($show = $current_result->FetchRow()) {
                    $this->body .= "
							<tr>
								<td><a href=index.php?mc=users&page=users_view&b=" . $show['rater_user_id'] . " class=small_font>" . urldecode($show['username']) . "</a></td>
								<td>" . date("M j, Y", $show['date']) . "</td>
								<td>" . stripslashes(urldecode($show['feedback'])) . "</font></td>
								<td>" . urldecode($show['rate']) . "</font></td>
								<td align=center>" . geoHTML::addButton('edit', "index.php?mc=feedback&page=feedback_show&feedbackId=" . $show['id']) . "</td>
								<td align=center>" . geoHTML::addButton('delete', "index.php?mc=feedback&page=feedback_show&delete=" . $show['id'] . "&userId=" . $user_id . "&auto_save=1", false, '', 'lightUpLink mini_cancel') . "</td>
							</tr>";

                    //renew/upgrade
                    $this->row_count++;
                }// end of while

                $this->body .= "
						</tbody></table>
					</td>
				</tr>";
            }

            // Display current bids
            $sql = "select * from " . $this->bid_table . ", " . $this->auctions_table . " where bidder = " . $user_id . " and auction_id = id and ends > " . geoUtil::time();
            $current_result = $this->db->Execute($sql);
            if ($this->debug) {
                echo $sql . "<br>\n";
            }
            if (!$current_result) {
                return false;
            } elseif ($current_result->RecordCount() > 0) {
                $listingDataToShow = true;
                $this->body .= "<tr><td colspan=2>";
                $this->body .= "<table class=\"table table-hover table-striped table-bordered\" style='background-color: #FFFFFF;'>";
                $this->body .= "<thead><tr class='col_hdr'><td valign=top width=100% colspan=100% class=medium_font_light><b>Current bids by this User</b></td></tr>";
                $this->body .= "<tr><td><b>Auction ID - Title</b></td>";
                $this->body .= "<td><b>Date</b></td>";
                $this->body .= "<td><b>Bid</b></td>";
                $this->body .= "<td align=center><b>Quantity</b></td>";
                $this->body .= "</tr></thead><tbody>";

                while ($show = $current_result->FetchNextObject()) {
                    $this->body .= "<tr>";
                    $this->body .= "<td><a href=index.php?mc=users&page=users_view_ad&b=" . $show->AUCTION_ID . " class=small_font>" . stripslashes(urldecode($show->AUCTION_ID)) . " - " . stripslashes(urldecode($show->TITLE)) . "</a></td>";
                    $this->body .= "<td class=small_font>" . date("M j, Y", $show->TIME_OF_BID) . "</td>";
                    $this->body .= "<td class=small_font>" . urldecode($show->BID) . "</td>";
                    $this->body .= "<td class=small_font align=center>\n\t" . urldecode($show->QUANTITY) . "</td>";
                    $this->body .= "</tr>\n\t";

                    //renew/upgrade
                    $this->row_count++;
                }// end of while

                $this->body .= "</td></tr>";
                $this->body .= "</tbody></table>";
                $this->body .= "</td></tr>";
            }
        }
        if (!$listingDataToShow) {
            //out of all those possibilities, none had data
            $this->body .= "<tr><td style='width: 100%; text-align: center;'>No listing data to show.</td></tr>";
        }
        $this->body .= "</table>";

        $this->body .= "
								</div>
							</div>

                    </div>
                  </div>
			<!-- NEW PROFILE DATA END -->

			<div>";



        $this->body .= "</div></fieldset><div class='clearColumn'></div>";

        return true;
    } //end of function display_user_data

//########################################################################
    function display_page_numbers($type, $total_transactions, $user_id, $page = 1)
    {
        $number_of_page_results = ceil($total_transactions / 25);
        $link = "index.php?mc=users&page=users_view&b=$user_id&d=$type&c=";
        $this->body .= "<tr><td colspan='8'>" . geoPagination::getHTML($number_of_page_results, $page, $link) . '</td></tr>';
        return;
    }
    function display_current_item($result, $title)
    {
        $this->body .= "
			<tr>
				<td colspan=\"100%\">
					<table class=\"table table-hover table-striped table-bordered\" style='background-color: #FFFFFF;'>

						<thead>
						<tr>
							<td valign=top width=100% colspan=8 class=col_hdr  style='text-align: center;'>
								<b>" . $title . "</b>
							</td>
						</tr>
						<tr>
							<td>
								<b>ID - Title</b>
							</td>
							<td>
								<b>Starts</b>
							</td>
							<td>
								<b>Ends</b>
							</td>
							<td align=center>
								<b>Forwarded</b>
							</td>
							<td align=center>
								<b>Responded</b>
							</td>
							<td align=center>
								<b>Viewed</b>
							</td>
							<td align=center>
								<b>Upgrade/Extend</b>
							</td>
							<td align=center>
								<b>Photo(s)</b>
							</td>
						</tr></thead><tbody>";

        $this->row_count = 0;
        while ($show = $result ->FetchRow()) {
            $this->body .= "
						<tr>
							<td>
								<a href=index.php?mc=users&page=users_view_ad&b=" . $show["id"] . ">
									<span class=small_font>" . $show["id"] . " - " . urldecode($show["title"]) . "</span>
								</a>
							</td>
							<td class=small_font>
								" . date("M j, Y", $show["date"]) . "
							</td>
							<td class=small_font>
								";
            if ($show['ends'] == 0) {
                $this->body .= "Never";
            } elseif ($show["delayed_start"] != 0) {
                $this->body .= "starts on first bid";
            } else {
                $this->body .= date("M j, Y", $show["ends"]);
            }
            $this->body .= "
							</td>
							<td class=small_font align=center>
								" . $show["forwarded"] . "
							</td>
							<td class=small_font align=center>
								" . $show["responded"] . "
							</td>
							<td class=small_font align=center>
								" . $show["viewed"] . "
							</td>
							<td align=center>
								<a href=index.php?mc=users&page=users_restart_ad&b=" . $show["id"] . ">
									<span class=small_font>change</span>
								</a>
							</td>
							<td class=small_font align=center>
								" . $show["image"] . " -
								<a href=index.php?mc=users&page=users_max_photos&b=" . $show["id"] . "&c=" . $show["seller"] . ">
									<span class=small_font>increase</span>
								</a>
							</td>
						</tr>";
            $this->row_count++;
        }// end of while
        $this->body .= "
					</tbody></table>
				</td>
			</tr>";
    }

//########################################################################
    public function display_archived_item($result, $title)
    {
        $this->body .= "
			<tr>
				<td colspan=\"100%\">
					<table class=\"table table-hover table-striped table-bordered\" style='background-color: #FFFFFF;'>
						<thead>
						<tr>
							<td valign=top width=100% colspan=7 class=col_hdr style='text-align: center;'>
								" . $title . "
							</td>
						</tr>
						<tr>
							<td>
								<b>ID - Title</b>
							</td>
							<td>
								<b>Started</b>
							</td>
							<td>
								<b>Ended</b>
							</td>
							<td align=center>
								<b>Reason Ended</b>
							</td>
							<td align=center>
								<b>Viewed</b>
							</td>
						</tr>
						</thead><tbody>";

        $this->row_count = 0;
        while ($show = $result->FetchRow()) {
            $this->body .= "
						<tr>
							<td>
								<a href=index.php?mc=users&page=users_view_ad&b=" . $show["id"] . ">
									<span class=small_font>" . $show["id"] . " - " . urldecode($show["title"]) . "</span>
								</a>
							</td>
							<td class=small_font>
								" . date("M j, Y", $show["date"]) . "
							</td>
							<td class=small_font>
								" . ($show['ends'] > 0 ? date("M j, Y", $show["ends"]) : 'Canceled (Unlimited Duration)') . "
							</td>
							<td align=center class=small_font>
								" . $show["reason_ad_ended"] . "
							</td>
							<td align=center class=small_font>
								" . $show["viewed"] . "
							</td>
						</tr>";
            $this->row_count++;
        }// end of while
        $this->body .= "
					</tbody></table>
				</td>
			</tr>";
    }
    function display_expired_item($result, $title)
    {
        $this->body .= "
			<tr>
				<td colspan=\"100%\">
					<table class=\"table table-hover table-striped table-bordered\" style='background-color: #FFFFFF;'>
						<thead>
						<tr>
							<td valign=top width=100% colspan=7 class=col_hdr style='text-align: center;'>
								" . $title . "
							</td>
						</tr>
						<tr>
							<td>
								<b>ID - Title</b>
							</td>
							<td>
								<b>Starts</b>
							</td>
							<td>
								<b>Ends</b>
							</td>
							<td align=center>
								<b>Forwarded</b>
							</td>
							<td align=center>
								<b>Responded</b>
							</td>
							<td align=center>
								<b>Viewed</b>
							</td>
							<td align=center>
								<b>Restart</b>
							</td>
						</tr></thead><tbody>";

        $this->row_count = 0;
        while ($show = $result->FetchRow()) {
            $this->body .= "
						<tr>
							<td>
								<a href=index.php?mc=users&page=users_view_ad&b=" . $show["id"] . ">
									<span class=small_font>" . $show["id"] . " - " . urldecode($show["title"]) . "</span>
								</a>
							</td>
							<td class=small_font>
								" . date("M j, Y", $show["date"]) . "
							</td>
							<td class=small_font>
								" . ($show['ends'] > 0 ? date("M j, Y", $show["ends"]) : 'Canceled (Unlimited Duration)') . "
							</td>
							<td align=center class=small_font>
								" . $show["forwarded"] . "
							</td>
							<td align=center class=small_font>
								" . $show["responded"] . "
							</td>
							<td align=center class=small_font>
								" . $show["viewed"] . "
							</td>
							<td align=center>
								<a href=index.php?mc=users&page=users_restart_ad&b=" . $show["id"] . ">
									<span class=small_font>restart</span>
								</a>
							</td>
						</tr>";
            $this->row_count++;
        }// end of while
        $this->body .= "
					</tbody></table>
				</td>
			</tr>";
    }

//########################################################################

    function get_row_color()
    {
        if (($this->row_count % 2) == 0) {
            $row_color = "row_color1";
        } else {
            $row_color = "row_color2";
        }
        return $row_color;
    }

//########################################################################

    function display_font_type_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 3 order by display_order";
        $result = $this->db->Execute($sql);
        if ($this->debug) {
            echo $sql . "<br>\n";
        }
        if (!$result) {
            if ($this->debug) {
                echo $sql . "<br>\n";
                echo $this->db->ErrorMsg() . " is the error<BR>\n";
            }
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">\n\t\t";
            while ($show_style = $result->FetchNextObject()) {
                $this->body .= "<option ";
                if ($show_style->VALUE == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_style->VALUE . "</option>\n\t\t";
            } //end of while
            $this->body .= "</select>\n\t";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_font_type_select

//########################################################################


    function display_font_style_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 5 order by display_order";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">\n\t\t";
            while ($show_style = $result->FetchRow()) {
                $this->body .= "<option ";
                if ($show_style["value"] == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_style["value"] . "</option>\n\t\t";
            } //end of while
            $this->body .= "</select>\n\t";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_font_style_select

//########################################################################

    function display_font_size_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 4 order by display_value";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">\n\t\t";
            while ($show_size = $result->FetchRow()) {
                $this->body .= "<option ";
                if ($show_size["value"] == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_size["value"] . "</option>\n\t\t";
            } //end of while
            $this->body .= "</select>\n\t";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_font_size_select

//########################################################################

    function display_font_weight_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 6 order by display_order";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">";
            while ($show_weight = $result->FetchRow()) {
                $this->body .= "<option ";
                if ($show_weight["value"] == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_weight["value"] . "</option>";
            } //end of while
            $this->body .= "</select>";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_font_weight_select

//########################################################################
/**
 * Displays stuff
 *
 * @param na $db
 * @param na $name
 * @param na $current_value
 */
    function display_font_decoration_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 7 order by display_order";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">\n\t\t";
            $this->body .= "<option></option>\n\t\t";
            while ($show_decoration = $result->FetchRow()) {
                $this->body .= "<option ";
                if ($show_decoration["value"] == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_decoration["value"] . "</option>\n\t\t";
            } //end of while
            $this->body .= "</select>\n\t";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_font_decoration_select

//########################################################################

    function display_text_align_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 8 order by display_order";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">\n\t\t";
            $this->body .= "<option></option>\n\t\t";
            while ($show_text_align = $result->FetchRow()) {
                $this->body .= "<option ";
                if ($show_text_align["value"] == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_text_align["value"] . "</option>\n\t\t";
            } //end of while
            $this->body .= "</select>\n\t";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_text_align_select

//########################################################################

    function display_text_vertical_align_select($db, $name, $current_value)
    {
        $sql = "select * from " . $this->choices_table . " where type_of_choice = 20 order by display_order,display_value";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $this->body .= "<select name=" . $name . ">\n\t\t";
            $this->body .= "<option></option>\n\t\t";
            while ($show_text_align = $result->FetchRow()) {
                $this->body .= "<option ";
                if ($show_text_align["value"] == $current_value) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $show_text_align["value"] . "</option>\n\t\t";
            } //end of while
            $this->body .= "</select>\n\t";
        } else {
            $this->error_message = $this->data_error_message;
            return false;
        }
    } //end of function display_text_align_select

//##################################################################################

    public function get_languages()
    {
        return $this->db->GetAssoc("SELECT * FROM " . geoTables::pages_languages_table . " ORDER BY `language_id`");
    }

    function get_language_name($db, $language_id = 0)
    {
        if ($language_id) {
            $sql = "select language from " . $this->pages_languages_table . " where language_id = " . $language_id;
            $result = $this->db->Execute($sql);
            //echo $sql."<br>\n";
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
                return $show["language"];
            } else {
                return "no name";
            }
        } else {
            return "no name";
        }
    } //end of function get_language_name

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_group_name($db, $group_id = 0)
    {
        if ($group_id) {
            $sql = "select name from " . $this->classified_groups_table . " where group_id = " . $group_id;
            $result = $this->db->Execute($sql);
            //echo $sql."<br>\n";
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
                return $show["name"];
            } else {
                return "no name";
            }
        } else {
            return "no name";
        }
    } //end of function get_group_name

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_group($db, $group_id = 0)
    {
        if ($group_id) {
            $sql = "select * from " . $this->classified_groups_table . " where group_id = " . $group_id;
            $result = $this->db->Execute($sql);
            //echo $sql."<br>\n";
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                $this->site_error($this->db->ErrorMsg());
            } elseif ($result->RecordCount() == 1) {
                $show = $result->FetchRow();
                return $show;
            } else {
                return "no name";
            }
        } else {
            return "no name";
        }
    } //end of function get_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_date_select($year_name, $month_name, $day_name, $year = 0, $month = 0, $day = 0, $earliest_year = 0)
    {
        $date = "";

        $time = time() + $this->time_shift;
        if (!$year) {
            $year = date("Y", $time);
        }
        if (!$month) {
            $month = date("n", $time);
        }
        if (!$day) {
            $day = date("j", $time);
        }

        $date .= "Month <select name=" . $month_name . ">\n\t\t";
        for ($i = 1; $i < 13; $i++) {
            $date .= "<option";
            if ($month == $i) {
                $date .= " selected";
            }
            $date .= ">" . $i . "</option>\n\t\t";
        }
        $date .= "</select>\n\t\t";

        $date .= "Day <select name=" . $day_name . ">\n\t\t";
        for ($i = 1; $i < 32; $i++) {
            $date .= "<option";
            if ($day == $i) {
                $date .= " selected";
            }
            $date .= ">" . $i . "</option>\n\t\t";
        }
        $date .= "</select>\n\t\t";
        $date .= "Year <select name=" . $year_name . ">\n\t\t";
        if (!$earliest_year) {
            //echo "setting earliest_year to: ".$year."<bR>\n";
            $earliest_year = $year;
        }
        //echo $earliest_year." is the earliest_year<bR>\n";

        for ($i = $earliest_year; $i <= (5 + $year); $i++) {
            $date .= "<option";
            if ($year == $i) {
                $date .= " selected";
            }
            $date .= ">" . $i . "</option>\n\t\t";
        }
        $date .= "</select>\n\t\t";

        return $date;
    } //end of function get_date_select

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_fine_date_select(
        $year_name,
        $month_name,
        $day_name,
        $hour_name = 0,
        $minute_name = 0,
        $year_value = 0,
        $month_value = 0,
        $day_value = 0,
        $hour_value = 0,
        $minute_value = 0
    ) {
        $time = time() + $this->time_shift;
        if (!$year_value) {
            $year_value = date("Y", $time);
        }
        if (!$month_value) {
            $month_value = date("n", $time);
        }
        if (!$day_value) {
            $day_value = date("j", $time);
        }
        if (!$hour_value) {
            $hour_value = date("G", $time);
        }
        if (!$minute_value) {
            $minute_value = date("i", $time);
        }

        if ($minute_name) {
            $this->body .= "minute <select name=" . $minute_name . ">\n\t\t";
            for ($i = 0; $i <= 59; $i++) {
                $this->body .= "<option";
                if ($minute_value == $i) {
                    $this->body .= " selected";
                }
                $this->body .= ">" . sprintf("%02d", $i) . "</option>\n\t\t";
            }
            $this->body .= "</select>\n\t\t";
        }

        if ($minute_name) {
            $this->body .= "hour <select name=" . $hour_name . ">\n\t\t";
            for ($i = 0; $i <= 23; $i++) {
                $this->body .= "<option";
                if ($hour_value == $i) {
                    $this->body .= " selected";
                }
                $this->body .= ">" . sprintf("%02d", $i) . "</option>\n\t\t";
            }
            $this->body .= "</select>\n\t\t";
        }

        $this->body .= "day <select name=" . $day_name . ">\n\t\t";
        for ($i = 1; $i < 32; $i++) {
            $this->body .= "<option";
            if ($day_value == $i) {
                $this->body .= " selected";
            }
            $this->body .= ">" . $i . "</option>\n\t\t";
        }
        $this->body .= "</select>\n\t\t";

        $this->body .= "month <select name=" . $month_name . ">\n\t\t";
        for ($i = 1; $i < 13; $i++) {
            $this->body .= "<option";
            if ($month_value == $i) {
                $this->body .= " selected";
            }
            $this->body .= ">" . $i . "</option>\n\t\t";
        }
        $this->body .= "</select>\n\t\t";

        $this->body .= "year <select name=" . $year_name . ">\n\t\t";
        for ($i = ($year_value - 2); $i <= ($year_value + 2); $i++) {
            $this->body .= "<option";
            if ($year_value == $i) {
                $this->body .= " selected";
            }
            $this->body .= ">" . $i . "</option>\n\t\t";
        }
        $this->body .= "</select>\n\t\t";
    } //end of function get_date_select

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_ad_count_for_category($db, $category_id = 0)
    {
        if ($category_id) {
            //get the count for this category
            $count = 0;

            $this->sql_query = "select category_id from " . $this->classified_categories_table . " where parent_id = " . $category_id;
            $result = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>";
            if (!$result) {
                //echo $this->sql_query." is the query<br>\n";
                $this->error_message = $this->messages[2524];
                return false;
            } elseif ($result->RecordCount() > 0) {
                while ($show_category = $result->FetchRow()) {
                    $returned_count = $this->get_ad_count_for_category($db, $show_category["category_id"]);
                    if ($returned_count) {
                        $count += $returned_count;
                    }

                    //echo $count." is count returned for category ".$category_id."<br>\n";
                }
            }

            $count += $this->get_ad_count_this_category($db, $category_id);
            return $count;
        } else {
            //category_id is missing
            return false;
        }
    } //end of function get_ad_count_for_category

//##################################################################################

    function get_ad_count_this_category($db, $category_id = 0)
    {
        if ($category_id) {
            //get the count for this category
            $count = 0;

            $this->sql_query = "select count(*) as total from " . $this->classifieds_table . " where live = 1 and category = " . $category_id;
            $count_result = $this->db->Execute($this->sql_query);
            if (!$count_result) {
                //echo $this->sql_query." is the query<br>\n";
                $this->error_message = $this->messages[2524];
                return false;
            } elseif ($count_result->RecordCount() == 1) {
                $show = $count_result->FetchRow();
                return $show["total"];
            } else {
                return 0;
            }
        } else {
            //category_id is missing
            return false;
        }
    } //end of function get_ad_count_for_category

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_category_dropdown($name, $category_id = 0, $no_main = 0, $dropdown_limit = 0, $force_refresh = 0, $main_category_name = 'All Categories')
    {
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        if ($this->debug) {
            echo "TOP OF GET_CATEGORY_DROPDOWN<br>\n";
            echo $dropdown_limit . " is dropdown_limit inside get_category_dropdown<bR>\n";
            echo $category_id . " is the category id<BR>\n";
        }

        if ($force_refresh) {
            //this dropdown is probably different from others used earlier on the page, so clear the "cache"
            $this->category_dropdown_name_array = $this->category_dropdown_id_array = array();
        }

        if (count($this->category_dropdown_name_array) == 0) {
            if (!$no_main) {
                array_push($this->category_dropdown_name_array, $main_category_name);
                array_push($this->category_dropdown_id_array, 0);
            }

            $this->get_all_subcategories_for_dropdown($dropdown_limit);
        } else {
            reset($this->category_dropdown_name_array);
        }

        //build the select statement
        //array_reverse($this->category_dropdown_name_array);
        //array_reverse($this->category_dropdown_id_array);
        $this->dropdown_body = "<select name=" . $name . " class='form-control'>\n\t\t";
        foreach ($this->category_dropdown_name_array as $key => $value) {
            $this->dropdown_body .= "<option ";
            if ($this->category_dropdown_id_array[$key] == $category_id) {
                $this->dropdown_body .= "selected";
            }
            $this->dropdown_body .= " value=" . $this->category_dropdown_id_array[$key] . ">" . $this->category_dropdown_name_array[$key] . "</option>\n\t\t";
        }
        $this->dropdown_body .= "</select>\n\t";
        if ($this->debug) {
            echo "END OF GET_CATEGORY_DROPDOWN<br>\n";
        }
            return true;
    } //end of function get_category_dropdown

//##################################################################################

    /**
     * Gets all the categories in one swoop, instead of recursively getting each level of categories.
     */
    function get_all_subcategories_for_dropdown($dropdown_limit = 0, $category_id = 0)
    {
        trigger_error('DEBUG STATS ADMIN_SITE_CLASS: Top of get_all_subcategories_for_dropdown');
        $restrictParent = '';
        if ($this->db->get_site_setting('levels_of_categories_displayed_admin') == 1) {
            $restrictParent = "AND `parent_id`=" . (int)$category_id . ' ';
        }

        $this->sql_query = 'SELECT ' . $this->classified_categories_table . ".category_id as category_id,
			" . $this->classified_categories_table . ".parent_id as parent_id," . $this->classified_categories_languages_table . ".category_name as category_name
			FROM " . $this->classified_categories_table . "," . $this->classified_categories_languages_table .
            " WHERE " . $this->classified_categories_table . ".category_id = " . $this->classified_categories_languages_table . ".category_id " .
            "AND " . $this->classified_categories_languages_table . ".language_id = 1 " .
            $restrictParent .
            'ORDER BY ' . $this->classified_categories_table . '.parent_id, ' . $this->classified_categories_table . '.display_order, ' . $this->classified_categories_languages_table . ".category_name";
        $results = $this->db->Execute($this->sql_query);
        if (!$results) {
            trigger_error('ERROR SQL ADMIN_SITE_CLASS: Query: ' . $this->sql_query . ' Error: ' . $this->db->ErrorMsg());
            return false;
        }
        trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After sql executed, before data gotten.');
        $categories = array();
        while ($row = $results->FetchRow()) {
            $categories[$row['parent_id']][$row['category_id']]['category_name'] = $row['category_name'];
            //$categories[$row['parent_id']][$row['category_id']]['category_id']=$row['category_id'];
        }
        trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After data gotten, Before dropdown array generated.');
        $this->add_sub_categories_for_dropdown($categories, $category_id, $dropdown_limit);
        trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After dropdown array generated.');
    }
    function add_sub_categories_for_dropdown(&$show_category, $parent, $dropdown_limit = 0)
    {
        $ids = array_keys($show_category[$parent]);
        foreach ($ids as $id) {
            $pre_stage = "";
            for ($i = 1; $i <= $this->stage; $i++) {
                $pre_stage .= "&nbsp;&nbsp;&nbsp;";
            }
            array_push($this->category_dropdown_name_array, $pre_stage . urldecode(stripslashes($show_category[$parent][$id]["category_name"])));
            array_push($this->category_dropdown_id_array, $id);
            if (($this->stage + 1 <= $dropdown_limit) || ($dropdown_limit == 0)) {
                $this->stage++;
                $this->add_sub_categories_for_dropdown($show_category, $id, $dropdown_limit);
                $this->stage--;
            }
        }
    }
    function get_subcategories_for_dropdown(&$db, $category_id = 0, $dropdown_limit = 0)
    {
        //$stage++;
        //$this->sql_query = "select category_id,parent_id,category_name from ".$this->classified_categories_table."
        //  where parent_id = ".$category_id;
        trigger_error("DEBUG ADMIN_SITE_CLASS STATS: TOP OF GET_SUBCATEGORIES_FOR_DROPDOWN");
        trigger_error('DEBUG ADMIN_SITE_CLASS: ' . $dropdown_limit . " is dropdown limit before check");
        trigger_error('DEBUG ADMIN_SITE_CLASS: ' . $this->stage . " is this->stage");
        if ($dropdown_limit == 0) {
            $this->get_all_subcategories_for_dropdown();
            return;
        }

        trigger_error('DEBUG ADMIN_SITE_CLASS: ' . $dropdown_limit . " is dropdown_limit after check");

        if (($this->stage + 1) <= $dropdown_limit) {
            //echo $this->sql_query." is the query<br><br>\n";
            $this->sql_query = "select " . $this->classified_categories_table . ".category_id as category_id,
				" . $this->classified_categories_table . ".parent_id as parent_id," . $this->classified_categories_languages_table . ".category_name as category_name
				from " . $this->classified_categories_table . "," . $this->classified_categories_languages_table . "
				where " . $this->classified_categories_table . ".category_id = " . $this->classified_categories_languages_table . ".category_id
				and " . $this->classified_categories_table . ".parent_id = " . $category_id . "
				and " . $this->classified_categories_languages_table . ".language_id = 1 order by " . $this->classified_categories_table . ".display_order," . $this->classified_categories_languages_table . ".category_name";
            $category_result =  $this->db->Execute($this->sql_query);
            //if ($this->debug) echo $this->sql_query." is the query<br>\n";
            if (!$category_result) {
                trigger_error('ERROR SQL ADMIN_SITE_CLASS: Query: ' . $this->sql_query . " Error: " . $this->db->ErrorMsg());
                $this->error_message = $this->messages[2052];
                return false;
            } elseif ($category_result->RecordCount() > 0) {
                $this->stage++;
                while ($show_category = $category_result->FetchRow()) {
                    $pre_stage = "";
                    for ($i = 1; $i <= $this->stage; $i++) {
                        $pre_stage .= "&nbsp;&nbsp;&nbsp;";
                    }
                    if ($category_id != 0) {
                        array_push($this->category_dropdown_name_array, $pre_stage . urldecode(stripslashes($show_category["category_name"])));
                        array_push($this->category_dropdown_id_array, $show_category["category_id"]);
                    } else {
                        array_push($this->category_dropdown_name_array, urldecode(stripslashes($show_category["category_name"])));
                        array_push($this->category_dropdown_id_array, $show_category["category_id"]);
                    }
                    $this->get_subcategories_for_dropdown($db, $show_category["category_id"], $dropdown_limit);
                }
                $this->stage--;
            }
        }
        trigger_error('DEBUG ADMIN_SITE_CLASS: ' . "BOTTOM OF GET_SUBCATEGORIES_FOR_DROPDOWN");
        return;
    } //end of function get_subcategories_for_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function user_currently_subscribed($db, $user_id = 0)
    {
        if ($user_id) {
            $this->sql_query = "select * from " . $this->classified_user_subscriptions_table . " where subscription_expire > " . geoUtil::time() . " and user_id = " . $user_id;
            $get_subscriptions_results = $this->db->Execute($this->sql_query);
            //echo $this->sql_query."<br>\n";
            if (!$get_subscriptions_results) {
                return false;
            } elseif ($get_subscriptions_results->RecordCount() == 0) {
                return 0;
            } elseif ($get_subscriptions_results->RecordCount() > 0) {
                return 1;
            }
        } else {
            return false;
        }
    } // end of function check_user_subscription
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_text($current_page_id = 0)
    {
        $db = DataAccess::getInstance();
        //get default language
        $this->sql_query = "select language_id from " . $this->pages_languages_table . " where default_language = 1";
        $language_result = $db->Execute($this->sql_query);
        if (!$language_result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($language_result->RecordCount() == 1) {
            $show_language = $language_result->FetchRow();
            $language_id = $show_language["language_id"];
        } else {
            $language_id = 1;
        }
        if ($current_page_id) {
            $this->sql_query = "select text_id,text from " . $this->pages_text_languages_table . " where page_id = " . $current_page_id . " and language_id = " . $language_id;
        } else {
            $this->sql_query = "select text_id,text from " . $this->pages_text_languages_table . " where page_id = " . $this->page_id . " and language_id = " . $language_id;
        }
        //echo $this->sql_query."<br>\n";
        $result = $db->Execute($this->sql_query);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() > 0) {
            //take the database message result and push the contents into an array
            while ($show = $result->FetchRow()) {
                $this->messages[$show["text_id"]] = geoString::fromDB($show["text"]);
                //echo $show["text_id"]." - ".$show["text"]."<br>\n";
            }
        }
    } // end of function get_text

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_classified_data($classified_id = 0)
    {
        $db = DataAccess::getInstance();
        if ($classified_id) {
            $this->sql_query = "select * from " . $this->classifieds_table . " where id = " . $classified_id;
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                //$this->body .=$this->sql_query." is the query<br>\n";
                return false;
            } elseif ($result->RecordCount() > 1) {
                //more than one auction matches
                //$this->body .=$this->sql_query." is the query<br>\n";
                return false;
            } elseif ($result->RecordCount() <= 0) {
                //$this->body .=$this->sql_query." is the query<br>\n";
                return false;
            }
            $show = $result->FetchRow();
            return $show;
        } else {
            return false;
        }
    } //end of function get_classified_data

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_expired_classified_data($db, $classified_id = 0)
    {
        if ($classified_id) {
            $this->sql_query = "select * from " . $this->classifieds_expired_table . " where id = " . $classified_id;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                //$this->body .=$this->sql_query." is the query<br>\n";
                return false;
            } elseif ($result->RecordCount() > 1) {
                //more than one auction matches
                //$this->body .=$this->sql_query." is the query<br>\n";
                return false;
            } elseif ($result->RecordCount() <= 0) {
                //$this->body .=$this->sql_query." is the query<br>\n";
                return false;
            }
            $show = $result->FetchRow();
            return $show;
        } else {
            return false;
        }
    } //end of function get_classified_data

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_configuration_data(&$db)
    {
        $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        $this->sql_query = "SELECT * FROM " . $this->site_configuration_table;
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            trigger_error('ERROR SQL: Query: ' . $this->sql_query . ' ERROR: ' . $this->db->ErrorMsg());
            return false;
        } else {
            $this->configuration_data = $result->FetchRow();
        }
        $this->get_site_settings();
        return true;
    } //end of function get_configuration_data

//########################################################################

    function get_image_data($db, $classified_id = 0)
    {
        if ($classified_id) {
            $images_to_display = array();

            $this->sql_query = "select * from " . $this->images_urls_table . " where classified_id = " . $classified_id;
            $result = $this->db->Execute($this->sql_query);

            if (!$result) {
                $this->error_message = urldecode($this->messages[81]);
                return false;
            } elseif ($result->RecordCount() > 0) {
                while ($show_urls = $result->FetchRow()) {
                    $this->images_to_display[$show_urls["display_order"]]["type"] = 1;
                    $this->images_to_display[$show_urls["display_order"]]["id"] = $show_urls["image_id"];
                    $this->images_to_display[$show_urls["display_order"]]["image_width"] = $show_urls["image_width"];
                    $this->images_to_display[$show_urls["display_order"]]["image_height"] = $show_urls["image_height"];
                    $this->images_to_display[$show_urls["display_order"]]["original_image_width"] = $show_urls["original_image_width"];
                    $this->images_to_display[$show_urls["display_order"]]["original_image_height"] = $show_urls["original_image_height"];
                    $this->images_to_display[$show_urls["display_order"]]["url"] = $show_urls["image_url"];
                    $this->images_to_display[$show_urls["display_order"]]["classified_id"] = $show_urls["classified_id"];
                    $this->images_to_display[$show_urls["display_order"]]["icon"] = $show_urls["icon"];
                    $this->images_to_display[$show_urls["display_order"]]["mime_type"] = $show_urls["mime_type"];
                    $this->images_to_display[$show_urls["display_order"]]['image_text'] = $show_urls['image_text'];
                }
            }
        } else {
            return false;
        }
    } //end of function get_image_data

//##########################################################################################

    function display_ad_images($classified_id = 0)
    {
        if (!$classified_id) {
            return false;
        }

        $db = DataAccess::getInstance();

        $this->get_image_data($db, $classified_id);
        $count_images = count($this->images_to_display);
        if ((is_array($this->images_to_display)) && (count($this->images_to_display) > 0)) {
            reset($this->images_to_display);
            $image_table =  "<table cellpadding=2 cellspacing=1 border=0 align=center width=\"100%\">";
            $value = current($this->images_to_display);

            do {
                $image_table .= "<tr><td align=center valign=top width=" . $width_tag . ">";
                $image_table .= $this->display_image_tag($value);
                $image_table .= '<br />' . $value['image_text'];
                $image_table .= "</td>";
                $image_table .= "</tr>";
            } while ($value = next($this->images_to_display));

            $image_table .= "</table>\n";
        }
        return $image_table;
    }

//####################################################################################

    function display_image_tag($value)
    {
        if ($value["type"] == 1) {
        //display the url
            if (strlen(trim($value["icon"])) > 0) {
                $tag = "<a href=\"" . $value["url"] . "\">";
                $tag .=  "<img src=\"" . $value["icon"] . "\" border=0></a>";
            } else {
                // This is in case the image entered was a URL
                if (substr_count($value["url"], "http")) {
                    if ($value["image_width"] != $value["original_image_width"]) {
                        $tag = "<a href=\"javascript:winimage('../" . $value["url"] . "','" . ($value["original_image_width"] + 40) . "','" . ($value["original_image_height"] + 40) . "')\" class=browsing_image_links>";
                    }
                    $tag .=  "<img src=" . $value["url"] . " width=" . $value["image_width"] . " height=" . $value["image_height"] . " border=0>";
                    if ($value["image_width"] != $value["original_image_width"]) {
                        $tag .= "</a><br><a href=\"javascript:winimage('../" . $value["url"] . "','" . ($value["original_image_width"] + 40) . "','" . ($value["original_image_height"] + 40) . "')\" class=browsing_image_links>" . urldecode($this->messages[339]) . "</a>";
                    }
                } else {
                    if ($value["image_width"] != $value["original_image_width"]) {
                        $tag = "<a href=\"javascript:winimage('../" . $value["url"] . "','" . ($value["original_image_width"] + 40) . "','" . ($value["original_image_height"] + 40) . "')\" class=browsing_image_links>";
                    }
                    $tag .=  "<img src=../" . $value["url"] . " width=" . $value["image_width"] . " height=" . $value["image_height"] . " border=0>";
                    if ($value["image_width"] != $value["original_image_width"]) {
                        $tag .= "</a><br><a href=\"javascript:winimage('../" . $value["url"] . "','" . ($value["original_image_width"] + 40) . "','" . ($value["original_image_height"] + 40) . "')\" class=browsing_image_links>" . urldecode($this->messages[339]) . "</a>";
                    }
                }
            }
        } elseif ($value["type"] == 2) {
            //display the uploaded image
            if ($value["image_width"] != $value["original_image_width"]) {
                $tag = "<a href=\"javascript:winimage('../get_image.php?image=" . $value["id"] . "','" . ($value["original_image_width"] + 40) . "','" . ($value["original_image_height"] + 40) . "')\" class=browsing_image_links>";
            }
            $tag .=  "<img src=../get_image.php?image=" . $value["id"] . " width=" . $value["image_width"] . " height=" . $value["image_height"] . " border=0>";
            if ($value["image_width"] != $value["original_image_width"]) {
                $tag .= "</a><br><a href=\"javascript:winimage('../get_image.php?image=" . $value["id"] . "','" . ($value["original_image_width"] + 40) . "','" . ($value["original_image_height"] + 40) . "')\" class=browsing_image_links>" . urldecode($this->messages[339]) . "</a>";
            }
        }
        return $tag;
    } //end of function display_image_tag

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_tooltip($text, $style)
    {
        return '&nbsp;' . trim(geoHTML::showTooltip($text, ''));
    }

    /**
     * Generic alias for admin  - display_page
     *
     */
    function display_page()
    {
        $this->display();
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display()
    {
        geoAdmin::display_page(
            $this->body,
            $this->title,
            '',
            $this->head_html,
            $this->additional_head_html,
            $this->additional_body_tag_attributes
        );
    }
//########################################################################

    function is_class_auctions()
    {
        return (bool)geoMaster::is('classifieds') && geoMaster::is('auctions');
    }

//########################################################################

    function is_auctions()
    {
        return geoMaster::is('auctions');
    }

//########################################################################

    function is_classifieds()
    {
        return geoMaster::is('classifieds');
    }

//########################################################################

    function set_type($type)
    {
        $this->product_configuration->set_type($type);
    }

//#######################################################################

    function subscription_period_dropdown($db, $present_value = 0, $name = 0)
    {
        if ($name) {
            $this->sql_query = "select * from  " . $this->choices_table . " where type_of_choice = 9 order by display_order";
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->internal_error_message;
                return false;
            } elseif ($result->RecordCount() == 0) {
                $query = array();
                $query[0] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '4 days', '4', 4, 4)";
                $query[1] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '1 day', '1', 1, 1)";
                $query[2] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '2 days', '2', 2, 2)";
                $query[3] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '3 days', '3', 3, 3)";
                $query[4] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '5 days', '5', 5, 5)";
                $query[5] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '7 days', '7', 7, 7)";
                $query[6] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '10 days', '10', 10, 10)";
                $query[7] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '14 days', '14', 14, 14)";
                $query[8] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '28 days', '28', 28, 28)";
                $query[9] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '30 days', '30', 30, 30)";
                $query[10] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '45 days', '45', 45, 45)";
                $query[11] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '60 days', '60', 60, 60)";
                $query[12] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '90 days', '90', 90, 90)";
                $query[13] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '120 days', '120', 120, 120)";
                $query[14] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '150 days', '150', 150, 127)";
                $query[15] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '180 days', '180', 180, 127)";
                $query[16] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '270 days', '270', 270, 127)";
                $query[17] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '365 days', '365', 365, 127)";
                $query[18] = "INSERT INTO " . $this->choices_table . " ( type_of_choice, display_value, value, numeric_value, display_order) VALUES ( 9, '0 days', '0', 0, 0)";

                foreach ($query as $value) {
                    $result = $this->db->Execute($value);
                    //echo $value."<bR>\n";
                    if (!$result) {
                        //echo $value."<bR>\n";
                        $this->error_message = $this->internal_error_message;
                        return false;
                    }
                }

                $this->sql_query = "select * from  " . $this->choices_table . " where type_of_choice = 9 order by display_order";
                $result = $this->db->Execute($this->sql_query);
                if (!$result) {
                    $this->error_message = $this->internal_error_message;
                    return false;
                }
                $this->body .= "<select name=\"" . $name . "\">\n\t\t";
                $this->body .= "<option value=0>None</option>";
                while ($show = $result->FetchRow()) {
                    $this->body .= "<option value=\"" . $show["value"] . "\" ";
                    if ($show["value"] == $present_value) {
                        $this->body .= "selected";
                    }
                    $this->body .= ">" . $show["display_value"] . "</option>\n\t\t";
                }
                $this->body .= "</select>\n\t";
                return true;
            } elseif ($result->RecordCount() > 0) {
                $this->body .= "<select name=\"" . $name . "\">\n\t\t";

                while ($show = $result->FetchRow()) {
                    $this->body .= "<option value=\"" . $show["value"] . "\" ";
                    if ($show["value"] == $present_value) {
                        $this->body .= "selected";
                    }
                    $this->body .= ">" . $show["display_value"] . "</option>\n\t\t";
                }
                $this->body .= "</select>\n\t";
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } //end of function subscription_period_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function charge_select_box($present_value = 0, $name = 0)
    {
        if ($name) {
            if (strchr($present_value, ".")) {
                $split_value = explode(".", $present_value);
                $dollars = strlen($split_value[0]) > 0 ? $split_value[0] : 0;
                $cents = strlen($split_value[1]) > 0 ? $split_value[1] : 0;
            } else {
                $dollars = strlen($present_value) > 0 ? $present_value : 0;
                $cents = 0;
            }

            $this->body .= "<input align=right type=text name=\"" . $name . "[0]\" size=11 maxsize=11 value=" . $dollars . ">\n\t\t";
            $this->body .= ".<select name=\"" . $name . "[1]\">\n\t\t";
            for ($i = 0; $i < 100; $i++) {
                $this->body .= "<option ";
                if ($i == $cents) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . sprintf("%02d", $i) . "</option>\n\t\t";
            }
            $this->body .= "</select>\n\t";
            return true;
        } else {
            return false;
        }
    } //end of function charge_select_box

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function date_dropdown($current_date, $name = 0)
    {
        if ($name) {
            //echo $name." is the name<br>\n";
            // Take care of the case where $current_date isnt set
            if ($current_date > 0) {
                $date = getdate($current_date);
            } else {
                $date = getdate();
            }

            //get the current year
            $current_year = getdate();

            $this->body .= "<select name=" . $name . "[month]>\n\t\t";
            for ($i = 1; $i < 13; $i++) {
                $this->body .= "<option ";
                if ($date["mon"] == $i) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>\n\t\t";
            }
            $this->body .= "</select><select name=" . $name . "[day]>\n\t\t";
            for ($i = 1; $i < 32; $i++) {
                $this->body .= "<option ";
                if ($date["mday"] == $i) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>\n\t\t";
            }
            $this->body .= "</select>\n\t";
            $this->body .= "<select name=" . $name . "[year]>\n\t\t";
            for ($i = $current_year["year"]; $i < $date["year"] + 50; $i++) {
                $this->body .= "<option ";
                if ($date["year"] == $i) {
                    $this->body .= "selected";
                }
                $this->body .= ">" . $i . "</option>\n\t\t";
            }
            $this->body .= "</select>";

            return true;
        } else {
            return false;
        }
    } //end of function date_dropdown

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function admin_demo()
    {
        //changed so we don't need to include config a bunch
        if (defined('DEMO_MODE')) {
            return true;
        } else {
            return false;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function get_ad_configuration($db)
    {
        if (!$this->ad_configuration_data) {
            $this->sql_query = "select * from " . $this->ad_configuration_table;
            $result = $this->db->Execute($this->sql_query);
            if (!$result) {
                $this->error_message = $this->messages[57];
                return false;
            } elseif ($result->RecordCount() == 1) {
                if ($by_array == 0) {
                    $this->ad_configuration_data = $result->FetchNextObject();
                } else {
                    $this->ad_configuration_data = $result->FetchRow();
                }
                return true;
            } else {
                $this->html_disallowed_list = 0;
                return true;
            }
        }
    } //function get_ad_configuration

    /**
     * Alias of geoEmail::sendMail(), use that instead of this.
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @param string $from pass zero for site default
     * @param string $replyTo pass zero for site default
     * @param string $charset pass zero for site default
     * @param string $type pass zero for site default
     * @deprecated 03/23/2008
     */
    function sendMail($to, $subject, $content, $from = 0, $replyTo = 0, $charset = 0, $type = 0)
    {
        geoEmail::sendMail($to, $subject, $content, $from, $replyTo, $charset, $type);
        return true;
    }

    /**
     * Function to get all the site configuration settings. This uses the new site config
     * table.
     */
    function get_site_settings()
    {
        $this->configuration_data = $this->db->get_site_settings(true);
        return true;
    }

     /**
      * sets a site config setting.
      * @param string setting The setting name to set
      * @param string value The value to set the setting to.  If false, this will remove that setting from the table.
      * $return bool true is it appears the setting was saved, false otherwise.
      */
    function set_site_setting($setting, $value)
    {
        return $this->db->set_site_setting($setting, $value);
    }
    /**
     * Gets a particular setting, returns false if the setting is not found.
     * @param string setting The setting you wish to get.
     * @return mixed The value for the setting, or false if the setting is not set.
     */
    function get_site_setting($setting)
    {
        return $this->db->get_site_setting($setting);
    }

    /**
     * inverse of strip_tags
     *
     * @param $str string
     * @param $tags string
     * @param $stripContent boolean
     */
    function strip_selected_tags($str, $tags = "", $stripContent = false)
    {
        preg_match_all("/<([^>]+)>/i", $tags, $allTags, PREG_PATTERN_ORDER);
        foreach ($allTags[1] as $tag) {
            if ($stripContent) {
                $str = preg_replace("/<" . $tag . "[^>]*>.*<\/" . $tag . ">/iU", "", $str);
            }
            $str = preg_replace("/<\/?" . $tag . "[^>]*>/iU", "", $str);
        }
        return $str;
    }
    /**
     * Deprecated
     *
     * @return unknown
     * @deprecated
     */
    function demoCheck()
    {
        $warnings = array();
        if (defined('DEMO_MODE')) {
            $warnings[] = "<span style='color: red'><strong>NOTICE:</strong> The forms in this demo will not submit.</span>";
        }
        return $warnings;
    }

//**********************************************************
    function check_user_subscription($db, $user_id = 0)
    {
        if ($this->debug_sell) {
            echo "<BR>TOP OF CHECK_USER_SUBSCRIPTION<Br>\n";
        }
        if ($user_id) {
            $this->sql_query = "select * from " . $this->classified_user_subscriptions_table . " where subscription_expire > " . geoUtil::time() . " and user_id = " . $user_id;
            $get_subscriptions_results = $this->db->Execute($this->sql_query);
            if ($this->debug_sell) {
                echo $this->sql_query . "<br>\n";
            }
            if (!$get_subscriptions_results) {
                if ($this->debug_sell) {
                    echo $this->sql_query . "<br>\n";
                }
                return false;
            } elseif ($get_subscriptions_results->RecordCount() == 0) {
                return true;
            } elseif ($get_subscriptions_results->RecordCount() > 0) {
                return true;
            }
        } else {
            return false;
        }
    } // end of function check_user_subscription

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} //end of class Admin_site
