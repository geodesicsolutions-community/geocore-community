<?php

//register_class.php
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
## ##    16.09.0-33-g98df70b
##
##################################

require_once(CLASSES_DIR . 'site_class.php');

class Register extends geoSite
{

    var $debug_register = 0;

    var $registered_variables;
    var $error_found;
    var $error;
    var $username;
    var $password;
    var $hash;
    var $personal_info_check = 0;
    var $bad_registration_code = 0;
    var $registration_code_checked = 0;
    var $registration_code_use = 0;
    var $registration_group;
    var $registration_id;
    var $session_id;
    var $setup_error;
    var $registration_configuration;
    var $initial_account_balance_given = 0;
    var $filter_level_array = array();
    var $user_id;
    var $userAttachmentUsed = 0;
    var $userAttachmentSet = 0;

    var $api_error;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function __construct($language_id = 0, $session_id = 0, $product_configuration = 0)
    {
        parent::__construct();
        $this->session_id = ($session_id == 0) ? geoSession::getInstance()->getSessionId() : $session_id;
        if ($this->debug_register) {
            echo $this->session_id . " is the session_id in the constructor<br />\n";
        }

        $this->setup_registration_session();

        //check to see if there is a group with a registration code
        $this->check_groups_for_registration_code_use();

        $this->get_registration_configuration_data();

        $this->checkUserAttachmentCookie();

        //delete expired registration sessions (after 24 hours)
        $this->remove_old_sell_sessions();
    } //end of function Register

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function setup_registration_session()
    {
        if ($this->session_id) {
            $sql = "select * from " . $this->registration_table . " where session = \"" . $this->session_id . "\"";
            $setup_registration_result = $this->db->Execute($sql);
            trigger_error('DEBUG SQL REG: Query:' . $sql);
            if (!$setup_registration_result) {
                trigger_error('ERROR SQL REG: Query:' . $sql . ' ERROR: ' . $this->db->ErrorMsg());
                //$this->body .="no select reg<br />\n";
                $this->setup_error = 1;
                return false;
            } elseif ($setup_registration_result->RecordCount() == 1) {
                //get variables from db and save in local variables
                $show = $setup_registration_result->FetchNextObject();

                $this->registration_group = $show->REGISTRATION_GROUP;
                $this->registration_code_checked = $show->REGISTRATION_CODE_CHECKED;
                $this->personal_info_check = $show->PERSONAL_INFO_CHECK;
                $this->registration_code_use = $show->REGISTRATION_CODE_USE;

                $this->registered_variables["email"] = $show->EMAIL;
                $this->registered_variables["email2"] = $show->EMAIL2;
                $this->registered_variables["email_verifier"] = $show->EMAIL_VERIFIER;
                $this->registered_variables["email_verifier2"] = $show->EMAIL_VERIFIER2;
                $this->registered_variables["username"] = $show->USERNAME;
                $this->registered_variables["password"] = $show->PASSWORD;
                $this->registered_variables["agreement"] = $show->AGREEMENT;
                $this->registered_variables["company_name"] = stripslashes(urldecode($show->COMPANY_NAME));
                $this->registered_variables["business_type"] = $show->BUSINESS_TYPE;
                $this->registered_variables["firstname"] = stripslashes(urldecode($show->FIRSTNAME));
                $this->registered_variables["lastname"] = stripslashes(urldecode($show->LASTNAME));
                $this->registered_variables["address"] = stripslashes(urldecode($show->ADDRESS));
                $this->registered_variables["address_2"] = stripslashes(urldecode($show->ADDRESS_2));
                $this->registered_variables["city"] = stripslashes(urldecode($show->CITY));
                $this->registered_variables["state"] = $show->STATE;
                $this->registered_variables["country"] = $show->COUNTRY;
                $this->registered_variables["zip"] = stripslashes(urldecode($show->ZIP));
                $this->registered_variables["phone"] = stripslashes(urldecode($show->PHONE));
                $this->registered_variables["phone_2"] = stripslashes(urldecode($show->PHONE_2));
                $this->registered_variables["fax"] = stripslashes(urldecode($show->FAX));
                $this->registered_variables["url"] = stripslashes(urldecode($show->URL));
                $this->registered_variables["registration_code"] = $show->REGISTRATION_CODE;

                $this->registered_variables["optional_field_1"] = stripslashes(urldecode($show->OPTIONAL_FIELD_1));
                $this->registered_variables["optional_field_2"] = stripslashes(urldecode($show->OPTIONAL_FIELD_2));
                $this->registered_variables["optional_field_3"] = stripslashes(urldecode($show->OPTIONAL_FIELD_3));
                $this->registered_variables["optional_field_4"] = stripslashes(urldecode($show->OPTIONAL_FIELD_4));
                $this->registered_variables["optional_field_5"] = stripslashes(urldecode($show->OPTIONAL_FIELD_5));
                $this->registered_variables["optional_field_6"] = stripslashes(urldecode($show->OPTIONAL_FIELD_6));
                $this->registered_variables["optional_field_7"] = stripslashes(urldecode($show->OPTIONAL_FIELD_7));
                $this->registered_variables["optional_field_8"] = stripslashes(urldecode($show->OPTIONAL_FIELD_8));
                $this->registered_variables["optional_field_9"] = stripslashes(urldecode($show->OPTIONAL_FIELD_9));
                $this->registered_variables["optional_field_10"] = stripslashes(urldecode($show->OPTIONAL_FIELD_10));
            } else {
                //create new sell session
                $sql = "insert into " . $this->registration_table . "
					(session,time_started) values (\"" . $this->session_id . "\"," . geoUtil::time() . ")";
                $insert_sell_result = $this->db->Execute($sql);
                trigger_error('DEBUG SQL REG: Query: ' . $sql);
                if (!$insert_sell_result) {
                    //$this->body .="no insert<br />\n";
                    trigger_error('ERROR SQL REG: Query: ' . $sql . ' ERROR: ' . $this->db->ErrorMsg());
                    $this->setup_error = 1;
                    return false;
                }
            }
        } else {
            trigger_error('DEBUG REG: No session_id');
            return false;
        }
    } //end of funciton setup_registration_session

//####################################################################

    public function remove_old_sell_sessions()
    {
        $sql = "select * from " . $this->registration_table . " where time_started < " . (geoUtil::time() - (24 * 60 * 60));
        $get_old_sell_result = $this->db->Execute($sql);
        //echo $sql." is the query<br />\n";
        if (!$get_old_sell_result) {
            return false;
        } elseif ($get_old_sell_result->RecordCount() > 0) {
            while ($show_old = $get_old_sell_result->FetchNextObject()) {
                $this->remove_registration_session($show_old->SESSION);
            }
        }

        $sql = "delete from " . $this->confirm_table . " where date < " . (geoUtil::time() - (24 * 60 * 60 * 30));
        //$this->body .=$sql." is the query<br />\n";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->site_error($sql, $this->db->ErrorMsg());
            ////$this->body .=$sql." is the query<br />\n";
            $this->error['confirm'] = urldecode($this->messages[229]);
            return false;
        }
    } //end of function remove_old_sell_sessions

//####################################################################

    public function remove_registration_session($delete_session_id = 0)
    {
        $current_session_id = 0;
        if ($delete_session_id) {
            $current_session_id = $delete_session_id;
        } elseif ($this->session_id) {
            $current_session_id = $this->session_id;
        }
        if ($current_session_id) {
            $sql = "delete from " . $this->registration_table . " where session = \"" . $current_session_id . "\"";
            $delete_registration_result = $this->db->Execute($sql);
            if (!$delete_registration_result) {
                return false;
            }
        }
    } //end of funciton remove_registration_session

//####################################################################

    public function save_form_variables()
    {
        $sql = "UPDATE " . geoTables::registration_table . " SET
		`email` = ?, `email_verifier` = ?, `email2` = ?, `email_verifier2` = ?, `username` = ?, `password` = ?, 
		`company_name` = ?, `firstname` = ?, `lastname` = ?, `address` = ?, `address_2` = ?, `city` = ?, `state` = ?, 
		`country` = ?, `zip` = ?, `phone` = ?, `phone_2` = ?, `fax` = ?, `business_type` = ?, `agreement` = ?, 
		`optional_field_1` = ?, `optional_field_2` = ?, `optional_field_3` = ?, `optional_field_4` = ?, 
		`optional_field_5` = ?, `optional_field_6` = ?, `optional_field_7` = ?, `optional_field_8` = ?, 
		`optional_field_9` = ?, `optional_field_10` = ?, `url` = ?
		WHERE `session` = ?";
        $queryData = array(
            $this->registered_variables["email"],
            $this->registered_variables["email_verifier"],
            $this->registered_variables["email2"],
            $this->registered_variables["email_verifier2"],
            $this->registered_variables["username"],
            $this->registered_variables["password"],
            geoString::toDB($this->registered_variables["company_name"]),
            geoString::toDB($this->registered_variables["firstname"]),
            geoString::toDB($this->registered_variables["lastname"]),
            geoString::toDB($this->registered_variables["address"]),
            geoString::toDB($this->registered_variables["address_2"]),
            geoString::toDB($this->registered_variables["city"]),
            geoString::toDB($this->registered_variables["state"]),
            geoString::toDB($this->registered_variables["country"]),
            geoString::toDB($this->registered_variables["zip"]),
            geoString::toDB($this->registered_variables["phone"]),
            geoString::toDB($this->registered_variables["phone_2"]),
            geoString::toDB($this->registered_variables["fax"]),
            geoString::toDB($this->registered_variables["business_type"]),
            geoString::toDB($this->registered_variables["agreement"]),
            geoString::toDB($this->registered_variables["optional_field_1"]),
            geoString::toDB($this->registered_variables["optional_field_2"]),
            geoString::toDB($this->registered_variables["optional_field_3"]),
            geoString::toDB($this->registered_variables["optional_field_4"]),
            geoString::toDB($this->registered_variables["optional_field_5"]),
            geoString::toDB($this->registered_variables["optional_field_6"]),
            geoString::toDB($this->registered_variables["optional_field_7"]),
            geoString::toDB($this->registered_variables["optional_field_8"]),
            geoString::toDB($this->registered_variables["optional_field_9"]),
            geoString::toDB($this->registered_variables["optional_field_10"]),
            geoString::toDB($this->registered_variables["url"]),
            $this->session_id
        );
        $save_registered_result = DataAccess::getInstance()->Execute($sql, $queryData);
        if (!$save_registered_result) {
            return false;
        }
    }

//####################################################################

    public function check_info($info = 0, $api = false)
    {
        $session = geoSession::getInstance();
        if ($info) {
            $this->save_variables($info);
        }

        if (!$api) {
            $this->save_form_variables();
        }

        $this->error = array();
        $this->error_found = 0;

        $this->page_id = 15;
        if (!$api) {
            //if not an api call, check session
            $passedSessionId = (isset($info['sessionId'])) ? $info['sessionId'] : false;
            $sessionId = $session->getSessionId();

            $cookie_status = $session->getStatus();
            if ($cookie_status != 'confirmed') {
                //something is wrong with cookie??
                $this->get_text();
                if ($cookie_status == 'new') {
                    $this->error['cookie'] = $this->messages[500152]; //seems to be no cookies
                } else {
                    //must be that cookie could not be updated...
                    $this->error['cookie'] = $this->messages[500153]; //error updating message
                }
                $this->error_found ++;
            }
        }
        if (!$api || ($api && $info['skip_reqs'] != 1)) { //skip_reqs allows the API to bypass admin required field settings
            $fieldsToCheck = array ('company_name', 'firstname', 'lastname', 'address',
            'address2', 'business_type', 'zip', 'phone',
            'phone2', 'fax', 'url');

            //see if we're using the new or old city field
            $overrides = geoRegion::getLevelsForOverrides();
            if (!$overrides['city']) {
                //NOT using the new region-based city, so check it the old way
                $fieldsToCheck[] = 'city';
            }

            $zeroFields = array('business_type');

            foreach ($fieldsToCheck as $field) {
                $use = strtoupper("use_registration_{$field}_field");
                $require = strtoupper("require_registration_{$field}_field");
                $err = false;
                //stupid fields with 2 in them and not being consistent
                $index = str_replace('2', '_2', $field);
                if (($this->registration_configuration->$use) && ($this->registration_configuration->$require)) {
                    if (in_array($field, $zeroFields)) {
                        if ($this->registered_variables[$index] == 0) {
                            $err = true;
                        }
                    }
                    if ($err || strlen(trim($this->registered_variables[$index])) == 0) {
                        $this->api_error = "$index is required";
                        $this->error[$index] = "error";
                        $this->error_found++;
                    }
                }
            }
            //check regions
            $locations = $_REQUEST['locations'];
            $lowestEnabledRegion = false;
            for ($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
                if ($this->db->get_site_setting('registration_use_region_level_' . $r)) {
                    $lowestEnabledRegion = $r;
                    break;
                }
            }
            if ($lowestEnabledRegion) {
                //regions are in use. see if any are required
                $lowestRequiredRegion = false;
                for ($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
                    if ($this->db->get_site_setting('registration_require_region_level_' . $r)) {
                        $lowestRequiredRegion = $r;
                        break;
                    }
                }
                if ($lowestRequiredRegion) {
                    if (!$locations) {
                        //at least one region level is required, but there are no regions here!
                        $this->error_found++;
                        $this->error['location'] = 'error';
                    }
                    //check for branches that don't extend all the way down to the lowest-level required
                    //(i.e. if level 3 is required, but some level 2 region has no children, that's okay -- behave as if level 2 is required)
                    for ($i = $lowestRequiredRegion; $i > 0; $i--) {
                        if (isset($locations[$i]) && !$locations[$i]) {
                            //this level is present but not set, and is the lowest required or higher. generate an error.
                            $this->error_found++;
                            $this->error['location'] = 'error';
                            //no need to keep going once we have at least one error
                            break;
                        }
                    }
                }
            }
        }

        //special checks for email
        if (strlen(trim($this->registered_variables['email'])) > 0) {
            if (geoString::isEmail($this->registered_variables['email'])) {
                if (!geoString::emailDomainCanRegister($this->registered_variables['email'])) {
                    //email domain is blocked
                    $this->api_error = 'email blocked';
                    $this->error['email'] = "error5";
                    $this->error_found++;
                }

                //check if email address is already in use
                $sql = "select * from " . $this->userdata_table . " where email = \"" . $this->registered_variables['email'] . "\"";
                $userDataEmailResult = $this->db->Execute($sql);
                $sql = "select * from " . $this->confirm_table . " where email = \"" . $this->registered_variables['email'] . "\"";
                $confirmEmailResult = $this->db->Execute($sql);
                if ($this->debug_register) {
                    echo $sql . "<br />\n";
                }
                if (!$userDataEmailResult || !$confirmEmailResult) {
                    $this->api_error = 'registration error';
                    $this->error["registration"] = "error";
                    return false;
                } elseif ($userDataEmailResult->RecordCount() > 0 || $confirmEmailResult->RecordCount() > 0) {
                    //email already in use
                    $this->api_error = 'email(err 3)';
                    $this->error['email'] = "error3";
                    $this->error_found++;
                }
            } else {
                $this->api_error = 'email (err 2)';
                $this->error['email'] = "error2";
                $this->error_found++;
            }
        } else {
            $this->api_error = 'email (err 1)';
            $this->error['email'] = "error1";
            $this->error_found++;
        }
        //more special checks for e-mail
        if ((strlen(trim($this->registered_variables["email"])) > 0) && (strlen(trim($this->registered_variables["email_verifier"])) > 0)) {
            if (strcmp(trim($this->registered_variables["email"]), trim($this->registered_variables["email_verifier"])) !== 0) {
                $this->api_error = 'email (err 4)';
                $this->error['email'] = "error4";
                $this->error_found++;
            }
        } else {
            $this->api_error = 'email (err 4)';
            $this->error['email'] = "error4";
            $this->error_found++;
        }

        if (!$api || ($api && $info['skip_reqs'] != 1)) { //skip_reqs allows the API to bypass admin required field settings
            //special checks for email 2
            if (($this->registration_configuration->USE_REGISTRATION_EMAIL2_FIELD) && ($this->registration_configuration->REQUIRE_REGISTRATION_EMAIL2_FIELD)) {
                if (strlen(trim($this->registered_variables['email2'])) > 0) {
                    if (geoString::isEmail($this->registered_variables['email2'])) {
                        $sql = "select * from " . $this->userdata_table . " where email = \"" . $this->registered_variables['email2'] . "\"";
                        $userDataEmailResult = $this->db->Execute($sql);
                        $sql = "select * from " . $this->confirm_table . " where email = \"" . $this->registered_variables['email2'] . "\"";
                        $confirmEmailResult = $this->db->Execute($sql);
                        if ($this->debug_register) {
                            echo $sql . "<br />\n";
                        }
                        if (!$userDataEmailResult || !$confirmEmailResult) {
                            $this->api_error = 'registration error';
                            $this->error["registration"] = "error";
                            return false;
                        } elseif ($userDataEmailResult->RecordCount() > 0 || $confirmEmailResult->RecordCount() > 0) {
                            //email already in use
                            $this->api_error = 'email2 already in use';
                            $this->error['email2'] = "error3";
                            $this->error_found++;
                        }
                    } else {
                        $this->api_error = 'email2 (err 2)';
                        $this->error['email2'] = "error2";
                        $this->error_found++;
                    }
                } else {
                    $this->api_error = 'email2 (err 1)';
                    $this->error['email2'] = "error1";
                    $this->error_found++;
                }
                if (strlen(trim($this->registered_variables['email2'])) != strlen(trim($this->registered_variables['email_verifier2']))) {
                    $this->api_error = 'email2 (err4)';
                    $this->error['email2'] = "error4";
                    $this->error_found++;
                }
            }


            if (geoPC::is_ent()) {
                for ($i = 1; $i <= 10; $i++) {
                    $c = "OPTIONAL_{$i}_MAXLENGTH";
                    $max = (int)$this->registration_configuration->$c;
                    $index = "optional_field_$i";
                    if ($max > 0) {
                        $this->registered_variables[$index] = substr($this->registered_variables[$index], 0, $max);
                    }

                    $use = "USE_REGISTRATION_OPTIONAL_{$i}_FIELD";
                    $req = "REQUIRE_REGISTRATION_OPTIONAL_{$i}_FIELD";
                    $req2 = "REQUIRE_REGISTRATION_OPTIONAL_{$i}_FIELD_DEP";
                    if (($this->registration_configuration->$use)) {
                        if ($this->registration_configuration->$req || ($this->registration_configuration->$req2 && $this->registered_variables["business_type"] == 2)) {
                            if (strlen(trim($this->registered_variables[$index])) == 0) {
                                $this->error[$index] = "error";
                                $this->api_error = "$index error";
                                $this->error_found++;
                            }
                        }
                    }
                }
            }
            //Addon call to allow addons to thrown their own errors
            $addonData = array ('info' => $info, 'this' => $this, 'api' => $api);
            geoAddon::triggerUpdate('registration_check_info', $addonData);
        }

        $this->check_username($this->registered_variables["username"]);
        if ($api && isset($info['use_blank_password']) && $info['use_blank_password']) {
            //API called, and it wants to use blank password...
            $this->registered_variables['password'] = $this->registered_variables['password_confirm'] = $info['password'] = '';
            $this->registered_variables['set_api_token'] = $this->registered_variables['use_blank_password'] = 1;
        } else {
            //check the password!
            $this->check_password($this->registered_variables['username'], $this->registered_variables["password"], $this->registered_variables["password_confirm"]);
        }
        if (!$api) {
            $this->check_agreement($this->registered_variables["agreement"]);
        }
        if ($this->debug_register) {
            echo $this->error_found . " is error in check<br />\n";
            reset($this->error);
            foreach ($this->error as $key => $value) {
                echo $key . " is key to " . $value . "<br />\n";
            }
        }
        if ($this->error_found > 0) {
            return false;
        } else {
            $this->update_personal_info_check(1);
            return true;
        }
        if (isset($this->error['confirm'])) {
            $this->confirmation_error();
        }
        return false;
    } //end of function check_info($info)

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function save_variables($info)
    {
        //first, lets set default values, since we do not know which ones will be already set.
        $company_name = null;
        $business_type = null;
        $phone = null;
        $phone_2 = null;
        $zip = null;
        $state = null;
        $city = null;
        $email = null;
        $email_verifier = null;
        $email_verifier2 = null;
        $email2 = null;
        $address = null;
        $address_2 = null;
        $firstname = null;
        $lastname = null;
        $fax = null;
        $url = null;
        $country = null;
        $username = null;
        $password = null;
        $password_confirm = null;
        $agreement = null;
        $user_attachment_id = null;

        //now replace defaults with any values that are set.
        if (isset($info['company_name'])) {
            $company_name = $info['company_name'];
        }
        if (isset($info['business_type'])) {
            $business_type = $info['business_type'];
        }
        if (isset($info['phone'])) {
            $phone = $info['phone'];
        }
        if (isset($info['phone_2'])) {
            $phone_2 = $info['phone_2'];
        }
        if (isset($info['zip'])) {
            $zip = $info['zip'];
        }
        if (isset($info['state'])) {
            $state = $info['state'];
        }
        if (isset($info['city'])) {
            $city = $info['city'];
        }
        if (isset($info['email'])) {
            $email = $info['email'];
        }
        if (isset($info['email_verifier'])) {
            $email_verifier = $info['email_verifier'];
        }
        if (isset($info['email2'])) {
            $email2 = $info['email2'];
        }
        if (isset($info['email_verifier2'])) {
            $email_verifier2 = $info['email_verifier2'];
        }
        if (isset($info['address'])) {
            $address = $info['address'];
        }
        if (isset($info['address_2'])) {
            $address_2 = $info['address_2'];
        }
        if (isset($info['firstname'])) {
            $firstname = $info['firstname'];
        }
        if (isset($info['lastname'])) {
            $lastname = $info['lastname'];
        }
        if (isset($info['fax'])) {
            $fax = $info['fax'];
        }
        if (isset($info['url'])) {
            $url = $info['url'];
        }
        if (isset($info['country'])) {
            $country = $info['country'];
        }
        if (isset($info['username'])) {
            $username = $info['username'];
        }
        if (isset($info['password'])) {
            $password = $info['password'];
        }
        if (isset($info['password_confirm'])) {
            $password_confirm = $info['password_confirm'];
        }
        if (isset($info['agreement'])) {
            $agreement = $info['agreement'];
        }
        if (isset($info['user_attachment_id'])) {
            $user_attachment_id = $info['user_attachment_id'];
        }

        //username and password are always decoded
        $username = geoString::specialCharsDecode($username);
        $password = geoString::specialCharsDecode($password);
        $password_confirm = geoString::specialCharsDecode($password_confirm);

        //Reg. optional fields
        for ($i = 1; $i <= 10; $i++) {
            //set it all here
            $this->registered_variables['optional_field_' . $i] = null;
            if (isset($info['optional_field_' . $i . '_other']) && strlen(trim($info['optional_field_' . $i . '_other'])) > 0) {
                //use other box
                $this->registered_variables['optional_field_' . $i] = $info['optional_field_' . $i . '_other'];
            } elseif (isset($info['optional_field_' . $i])) {
                //use normal value
                $this->registered_variables['optional_field_' . $i] = $info['optional_field_' . $i];
            }
        }

        //region info is in its own array and needs special care
        $geographicOverrides = geoRegion::getLevelsForOverrides();
        $geographicRegions = $_REQUEST['locations'];
        //if any specific levels are in use (city/state/country), swap in those values
        if ($geographicOverrides['country']) {
            $country = geoRegion::getNameForRegion($geographicRegions[$geographicOverrides['country']]);
        }
        if ($geographicOverrides['state']) {
            //the old state field stores the abbreviation
            $state = geoRegion::getAbbreviationForRegion($geographicRegions[$geographicOverrides['state']]);
        }
        if ($geographicOverrides['city']) {
            $city = geoRegion::getNameForRegion($geographicRegions[$geographicOverrides['city']]);
        }

        //see if there is a default usergroup to set based on the business_type switch
        if ($business_type) {
            //if there is a registration code in use, do nothing
            $code = DataAccess::getInstance()->GetOne("SELECT `registration_code` FROM " . geoTables::registration_table . " WHERE `session` = ?", array($this->session_id));
            if (!$code) {
                switch ($business_type) {
                    case '1': //individual
                        $default_type = 2;
                        break;
                    case '2': //business
                        $default_type = 3;
                        break;
                    default:
                        $default_type = 1;
                }
                $this->set_default_group(false, $default_type);
            }
        }


        //and finally, set the corresponding registered_variables.
        $this->registered_variables["company_name"] = stripslashes($company_name);
        $this->registered_variables["business_type"] = stripslashes($business_type);
        $this->registered_variables["phone"] = stripslashes($phone);
        $this->registered_variables["phone_2"] = stripslashes($phone_2);
        $this->registered_variables["zip"] = stripslashes($zip);
        $this->registered_variables["state"] = stripslashes($state);
        $this->registered_variables["city"] = stripslashes($city);
        $this->registered_variables["email"] = trim(stripslashes($email));
        $this->registered_variables["email_verifier"] = trim(stripslashes($email_verifier));
        $this->registered_variables["email2"] = stripslashes($email2);
        $this->registered_variables["email_verifier2"] = stripslashes($email_verifier2);
        $this->registered_variables["address"] = stripslashes($address);
        $this->registered_variables["address_2"] = stripslashes($address_2);
        $this->registered_variables["firstname"] = stripslashes($firstname);
        $this->registered_variables["lastname"] = stripslashes($lastname);
        $this->registered_variables["fax"] = stripslashes($fax);
        $this->registered_variables["url"] = stripslashes($url);
        $this->registered_variables["country"] = stripslashes($country);
        $this->registered_variables["username"] = trim($username);
        $this->registered_variables["password"] = $password;
        $this->registered_variables["password_confirm"] = $password_confirm;
        $this->registered_variables["agreement"] = $agreement;
        $this->registered_variables["user_attachment_id"] = $this->userAttachmentSet = $user_attachment_id;
        //optional fields already set above.

        //allow addons to set their own registration variables
        $addonVars = geoAddon::triggerDisplay('registration_add_variable', $info, geoAddon::ARRAY_ARRAY);
        foreach ($addonVars as $varsList) {
            if (isset($varsList['name'])) {
                $varsList = array ($varsList);
            }
            foreach ($varsList as $var) {
                if (isset($var['name'])) {
                    $this->registered_variables[$var['name']] = $var['value'];
                }
            }
        }
    } //end of function save_variables() {

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    public function getInputsFromArray($data, $pre = '')
    {
        $return = '';
        //recaptcha fields get special treatment
        $recaptchaFields = array ('g-recaptcha-response');
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $return .= $this->getInputsFromArray($val, $pre . "[$key]");
            } else {
                $val = geoString::specialCharsDecode($val);
                $name = (in_array($key, $recaptchaFields)) ? $key : "{$pre}[$key]";
                $return .= "<input type='hidden' name='$name' value=\"" . geoString::specialChars($val) . "\" />\n";
            }
        }
        return $return;
    }

    public function basic_validation($info, $txt, $registration_url)
    {
        $session = geoSession::getInstance();

        if (!isset($info['g-recaptcha-response']) && isset($_POST['g-recaptcha-response'])) {
            //silly recaptcha isn't able to change field names...
            $info['g-recaptcha-response'] = $_POST['g-recaptcha-response'];
        }

        $this->body .=  "<form action=\"" . $registration_url . "\" method=\"post\" id=\"validate_register\">\n";
        $this->body .= $this->getInputsFromArray($info, 'c');
        $this->body .= $this->getInputsFromArray($_POST['geoRegion_user_location'], 'locations');

        $this->body .= "<input type=\"hidden\" name=\"c[sessionId]\" value=\"" . $session->getSessionId() . "\" />";
        $this->body .=  $txt . "</form>\n"; //text is inside form...

        $view = geoView::getInstance();
        $view->allowEmail = 1;
        $view->addTop(
            "
<script type=\"text/javascript\">
	//<![CDATA[
	//2 seconds after page is done loading, auto submit the form.
	gjUtil.autoSubmitForm ('validate_register', '?b=2&back=no');
	//]]>
</script>
"
        );

        $this->display_page();
        return true;
    }

    public function validate_register_form($info)
    {
        $registration_url = ($this->db->get_site_setting('use_ssl_in_registration')) ? $this->db->get_site_setting('registration_ssl_url') : $this->db->get_site_setting('registration_url');
        $registration_url .= '?b=1';

        $this->page_id = 15;
        $this->get_text();
        $txt = $this->messages[500154];
        return $this->basic_validation($info, $txt, $registration_url);
    }
    public function validate_registration_code($info)
    {
        $this->page_id = 19;
        $this->get_text();

        $txt = $this->messages[500157];
        $registration_url = ($this->db->get_site_setting('use_ssl_in_registration')) ? $this->db->get_site_setting('registration_ssl_url') : $this->db->get_site_setting('registration_url');
        return $this->basic_validation($info, $txt, $registration_url);
    }

    public function registration_form_1()
    {
        //make sure the js needed to auto-submit the loading thingy is cached
        $view = geoView::getInstance();
        $view->allowEmail = 1;
        $view->addTop(
            "
<script type=\"text/javascript\">
	//<![CDATA[
	//2 seconds after page is done loading, auto submit the form.
	gjUtil.autoSubmitForm ('validate_register', '?b=2&back=no');
	//]]>
</script>
"
        );

        $this->page_id = 15;
        $msgs = $this->db->get_text(true, $this->page_id);

        if ($this->error['cookie']) {
            $this->body .= urldecode($this->error['cookie']);
            $session = true;
            include(GEO_BASE_DIR . 'get_common_vars.php');
            if ($session->getStatus() == 'changed') {
                //only display the error message, nothing else.
                $this->display_page();
                return true;
            }
        }
        $tpl_vars = array();

        $tpl_vars['registration_url'] = $registration_url = ($this->db->get_site_setting('use_ssl_in_registration')) ? $this->db->get_site_setting('registration_ssl_url') : $this->db->get_site_setting('registration_url');

        $fields = array();
        $f = 0; //fields index

        if ($this->registration_configuration->USE_REGISTRATION_FIRSTNAME_FIELD) {
            $f = 'firstname';
            $fields[$f]['label'] = $msgs[258];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_FIRSTNAME_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[firstname]';
            $fields[$f]['value'] = $this->registered_variables["firstname"];
            $fields[$f]['size'] = $this->registration_configuration->FIRSTNAME_MAXLENGTH;

            if (isset($this->error['firstname'])) {
                $fields[$f]['error'] = $msgs[267];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_LASTNAME_FIELD) {
            $f = 'lastname';
            $fields[$f]['label'] = $msgs[259];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_LASTNAME_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[lastname]';
            $fields[$f]['value'] = $this->registered_variables["lastname"];
            $fields[$f]['size'] = $this->registration_configuration->LASTNAME_MAXLENGTH;

            if (isset($this->error['lastname'])) {
                $fields[$f]['error'] = $msgs[268];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_COMPANY_NAME_FIELD) {
            $f = 'company_name';
            $fields[$f]['label'] = $msgs[248];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_COMPANY_NAME_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[company_name]';
            $fields[$f]['value'] = $this->registered_variables["company_name"];
            $fields[$f]['size'] = $this->registration_configuration->COMPANY_NAME_MAXLENGTH;

            if (isset($this->error['company_name'])) {
                $fields[$f]['error'] = $msgs[266];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_BUSINESS_TYPE_FIELD) {
            $f = 'business_type';
            $fields[$f]['label'] = $msgs[769];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_BUSINESS_TYPE_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'radio';
            $fields[$f]['name'] = 'c[business_type]';
            $fields[$f]['options'][1]['text'] = $msgs[247];
            if ($this->registered_variables['business_type'] == 1) {
                $fields[$f]['options'][1]['checked'] = true;
            }
            $fields[$f]['options'][2]['text'] = $msgs[246];
            if ($this->registered_variables['business_type'] == 2) {
                $fields[$f]['options'][2]['checked'] = true;
            }

            if (isset($this->error['business_type'])) {
                $fields[$f]['error'] = $msgs[772];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_ADDRESS_FIELD) {
            $f = 'address';
            $fields[$f]['label'] = $msgs[249];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_ADDRESS_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[address]';
            $fields[$f]['value'] = $this->registered_variables["address"];
            $fields[$f]['size'] = $this->registration_configuration->ADDRESS_MAXLENGTH;

            if (isset($this->error['address'])) {
                $fields[$f]['error'] = $msgs[269];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_ADDRESS2_FIELD) {
            $f = 'address_2';
            $fields[$f]['label'] = $msgs[250];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_ADDRESS2_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[address_2]';
            $fields[$f]['value'] = $this->registered_variables["address_2"];
            $fields[$f]['size'] = $this->registration_configuration->ADDRESS2_MAXLENGTH;

            if (isset($this->error['address_2'])) {
                $fields[$f]['error'] = $msgs[269];
            }
        }

        $regionOverrides = geoRegion::getLevelsForOverrides();
        $maxLocationDepth = 0;
        $regionRequired = false;
        $lowestLevel = geoRegion::getLowestLevel();
        for ($r = 1; $r <= $lowestLevel; $r++) {
            if ($this->db->get_site_setting('registration_use_region_level_' . $r)) {
                $maxLocationDepth = $r;
                if ($this->db->get_site_setting('registration_require_region_level_' . $r)) {
                    $regionRequired = true;
                }
            }
        }
        if ($maxLocationDepth) {
            $fields['regions']['value'] = geoRegion::regionSelector('geoRegion_user_location', $_REQUEST['locations'], $maxLocationDepth, $regionRequired);
            if (isset($this->error['location'])) {
                $fields['regions']['error'] = $msgs[501629];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_CITY_FIELD && !$regionOverrides['city']) {
            $f = 'city';
            $fields[$f]['label'] = $msgs[251];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_CITY_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[city]';
            $fields[$f]['value'] = $this->registered_variables["city"];
            $fields[$f]['size'] = $this->registration_configuration->CITY_MAXLENGTH;

            if (isset($this->error['city'])) {
                $fields[$f]['error'] = $msgs[265];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_ZIP_FIELD) {
            $f = 'zip';
            $fields[$f]['label'] = $msgs[254];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_ZIP_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[zip]';
            $fields[$f]['value'] = $this->registered_variables["zip"];
            $fields[$f]['size'] = $this->registration_configuration->ZIP_MAXLENGTH;

            if (isset($this->error['zip'])) {
                $fields[$f]['error'] = $msgs[273];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_PHONE_FIELD) {
            $f = 'phone';
            $fields[$f]['label'] = $msgs[255];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_PHONE_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[phone]';
            $fields[$f]['value'] = $this->registered_variables["phone"];
            $fields[$f]['size'] = $this->registration_configuration->PHONE_MAXLENGTH;

            if (isset($this->error['phone'])) {
                $fields[$f]['error'] = $msgs[274];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_PHONE2_FIELD) {
            $f = 'phone_2';
            $fields[$f]['label'] = $msgs[256];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_PHONE2_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[phone_2]';
            $fields[$f]['value'] = $this->registered_variables["phone_2"];
            $fields[$f]['size'] = $this->registration_configuration->PHONE_2_MAXLENGTH;

            if (isset($this->error['phone_2'])) {
                $fields[$f]['error'] = $msgs[274];
            }
        }

        if ($this->registration_configuration->USE_REGISTRATION_FAX_FIELD) {
            $f = 'fax';
            $fields[$f]['label'] = $msgs[257];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_FAX_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[fax]';
            $fields[$f]['value'] = $this->registered_variables["fax"];
            $fields[$f]['size'] = $this->registration_configuration->FAX_MAXLENGTH;

            if (isset($this->error['fax'])) {
                $fields[$f]['error'] = $msgs[276];
            }
        }

        //Email Field
        $f = 'email';
        $fields[$f]['label'] = $msgs[260];
        $fields[$f]['required'] = true;
        $fields[$f]['type'] = 'text';
        $fields[$f]['name'] = 'c[email]';
        $fields[$f]['value'] = $this->registered_variables["email"];

        if (isset($this->error['email'])) {
            switch ($this->error['email']) {
                case "error1":
                    $emailError = $msgs[264];
                    break;
                case "error2":
                    $emailError = $msgs[271];
                    break;
                case "error3":
                    $emailError = $msgs[270];
                    break;
                case "error4":
                    $emailError = $msgs[781];
                    break;
                case "error5":
                    $emailError = $msgs[500081];
                    break;
            }
            $fields[$f]['error'] = $emailError;
        }

        //Email Verifier Field
        $f = 'email_verifier';
        $fields[$f]['label'] = $msgs[761];
        $fields[$f]['required'] = true;
        $fields[$f]['type'] = 'text';
        $fields[$f]['name'] = 'c[email_verifier]';
        $fields[$f]['value'] = $this->registered_variables["email_verifier"];


        if ($this->registration_configuration->USE_REGISTRATION_EMAIL2_FIELD) {
            $f = 'email2';
            $fields[$f]['label'] = $msgs[1240];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_EMAIL2_FIELD) {
                $fields[$f]['required'] = true;
            }
            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[email2]';
            $fields[$f]['value'] = $this->registered_variables["email2"];

            if (isset($this->error['email2'])) {
                switch ($this->error['email2']) {
                    case "error1":
                        $emailError = $msgs[264];
                        break;
                    case "error2":
                        $emailError = $msgs[271];
                        break;
                    case "error3":
                        $emailError = $msgs[270];
                        break;
                    case "error4":
                        $emailError = $msgs[781];
                        break;
                    case "error5":
                        $emailError = $msgs[500081];
                        break;
                }
                $fields[$f]['error'] = $emailError;
            }
            //Email Verifier Field
            $f = 'email_verifier2';
            $fields[$f]['label'] = $msgs[761];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_EMAIL2_FIELD) {
                $fields[$f]['required'] = true;
            }
            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[email_verifier2]';
            $fields[$f]['value'] = $this->registered_variables["email_verifier2"];
        }

        if ($this->registration_configuration->USE_REGISTRATION_URL_FIELD) {
            $f = 'url';
            $fields[$f]['label'] = $msgs[261];
            if ($this->registration_configuration->REQUIRE_REGISTRATION_URL_FIELD) {
                $fields[$f]['required'] = true;
            }

            $fields[$f]['type'] = 'text';
            $fields[$f]['name'] = 'c[url]';
            $fields[$f]['value'] = $this->registered_variables["url"];
            $fields[$f]['size'] = $this->registration_configuration->URL_MAXLENGTH;

            if (isset($this->error['url'])) {
                $fields[$f]['error'] = $msgs[277];
            }
        }
        $itemFields = geoAddon::triggerDisplay('registration_add_field_display', $this->registered_variables, geoAddon::ARRAY_ARRAY);
        foreach ($itemFields as $addonName => $addonFields) {
            if (isset($addonFields['value'])) {
                //shove it into an array
                if (isset($this->error[$addonName]) && !isset($addonFields['error'])) {
                    //set error if a single item...  If addon adds multiple fields
                    //will need to add error themselves
                    $addonFields['error'] = $this->error[$addonName];
                }
                $addonFields = array ($addonName => $addonFields);
            }
            foreach ($addonFields as $fieldName => $field) {
                if (isset($field['value'])) {
                    //add/overwrite the field specified by the addon
                    $fields[$fieldName] = $field;
                }
            }
        }

        $tpl_vars['fields'] = $fields;

        if (geoPC::is_ent()) {
            if (strlen(urldecode($msgs[1217])) > 0) {
                $tpl_vars['optionalFieldInstructions'] = $msgs[1217];
            }

            $optionals = array();
            $rc = $this->registration_configuration; //to make things smaller/easier to read
            for ($i = 1; $i <= 10; $i++) {
                //all these capital letters are ugly and take up lots of space
                //let's make prettier variable names for them :)
                $name = "REGISTRATION_OPTIONAL_" . $i;
                $use = "USE_" . $name . "_FIELD";
                $require = "REQUIRE_" . $name . "_FIELD";
                $type = $name . "_FIELD_TYPE";
                $maxlen = 'OPTIONAL_' . $i . '_MAXLENGTH';
                $other = $name . "_OTHER_BOX";

                if ($rc->$use) {
                    $optionals[$i]['label'] = $msgs[1218 + (2 * $i)];
                    if ($rc->$require) {
                        $optionals[$i]['required'] = true;
                    }
                    $optionals[$i]['name'] = "c[optional_field_$i]";
                    if (!$rc->$type) {
                        $optionals[$i]['type'] = 'text';
                        $optionals[$i]['value'] = $this->registered_variables["optional_field_$i"];
                        $optionals[$i]['maxlen'] = $rc->$maxlen;
                    } elseif ($rc->$type == 1) {
                        $optionals[$i]['type'] = 'area';
                        $optionals[$i]['value'] = $this->registered_variables["optional_field_$i"];
                    } else {
                        $matched = 0;
                        $optionals[$i]['type'] = 'select';
                        $sql = "select * from " . $this->registration_choices_table . " where type_id = " . $rc->$type . " order by display_order, value";
                        $type_result = $this->db->Execute($sql);
                        if (!$type_result) {
                            return false;
                        } elseif ($type_result->RecordCount() > 0) {
                            for ($d = 0; $show_dropdown = $type_result->FetchRow(); $d++) {
                                $optionals[$i]['dropdown'][$d]['value'] = $show_dropdown['value'];
                                if (!$matched && $this->registered_variables["optional_field_$i"] == $show_dropdown['value']) {
                                    $optionals[$i]['dropdown'][$d]['selected'] = true;
                                    $matched = 1;
                                }
                            }
                        } else {
                            //no option data from query -- make this a text input
                            $optionals[$i]['type'] = 'text';
                            $optionals[$i]['value'] = $this->registered_variables["optional_field_$i"];
                            $optionals[$i]['maxlen'] = $rc->$maxlen;
                        }
                        if ($rc->$other && $rc->$type) {
                            $optionals[$i]['other_name'] = "c[optional_field_" . $i . "_other]";
                            if (!$matched) {
                                $optionals[$i]['other_value'] = $this->registered_variables["optional_field_$i"];
                            }
                            $optionals[$i]['maxlen'] = $rc->$maxlen;
                        }
                    }


                    if (isset($this->error["optional_field_$i"])) {
                        $optionals[$i]['error'] = $msgs[1219 + (2 * $i)];
                    }
                }
            }
            $tpl_vars['optionals'] = $optionals;
        }

        $secure = geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('registration')) {
            $security_text =& geoAddon::getText('geo_addons', 'security_image');
            $error = $this->error['securityCode'];
            $section = "registration";
            $security_image_html = $secure->getHTML($error, $security_text, $section, false);
            $tpl_vars['security_image'] = $security_image_html;
            geoView::getInstance()->addTop($secure->getJs());
        }

        $username['value'] = $this->registered_variables["username"];
        $username['maxlen'] = $this->db->get_site_setting('max_user_length');

        if (isset($this->error['username']) && $this->error['username']) {
            if ($this->error['username'] == "error1") {
                $err = urldecode($msgs[773]);
            } elseif ($this->error['username'] == "error2") {
                $err = urldecode($msgs[775]);
            }
            $username['error'] = $err;
        }
        $tpl_vars['username'] = $username;


        $password['maxlen'] = $this->db->get_site_setting('max_pass_length');


        if (isset($this->error['password'])) {
            $err = '';
            if ($this->error['password'] == 'confirm_not_match') {
                //password not same as password confirm
                $err = $msgs[776];
            } elseif ($this->error['password'] == 'strlen') {
                //password less than 6 or greater than 12 characters
                $err = $msgs[777];
            } elseif ($this->error['password'] == 'username_match') {
                //password is same as username
                $err = $msgs[500231];
            }
            $password['error'] = $err;
        }
        $tpl_vars['password'] = $password;

        if ($this->registration_configuration->USE_USER_AGREEMENT_FIELD) {
            $eula['checked'] = strlen($this->registered_variables['agreement']) ? $this->registered_variables['agreement'] : "no";
            if ($this->error['yes_to_agreement']) {
                $eula['error'] = true;
            }
            $eula['text'] = geoString::fromDB($msgs[768]);
            if (strlen($eula['text']) == 0) {
                //actual text of EULA is blank, but still using the EULA field.
                //most commonly, this is done when the "label" contains a link to the Terms page
                //e.g. "I agree to the <a>terms of use</a>"
                $eula['type'] = 'hide';
            } elseif (preg_match('/\<[^>]+\>/', $eula['text'])) {
                $eula['type'] = 'div';
            } else {
                $eula['type'] = 'area';
            }
            $tpl_vars['eula'] = $eula;
        }

        $share_fees = geoAddon::getUtil('share_fees');
        if (($share_fees) && $share_fees->active) {
            $share_fee_text =& geoAddon::getText('geo_addons', 'share_fees');

            $tpl_vars["feeshareattachmentlabel"] = $share_fee_text['share_fees_registration_choice_label'];
            //display the users that can be attached to in alphabetical dropdown
            $users_can_attach_to = $share_fees->attachableUsers();
            $tpl_vars['sharefeeattachmentchoices'] = $users_can_attach_to;
            if ($this->error['feeshareattachment'] == "error") {
                $tpl_vars['feeshareattachmenterror'] = $share_fee_text['share_fees_registration_attachment_error'];
            } elseif ($this->error['feeshareattachment'] == "required") {
                $tpl_vars['feeshareattachmenterror'] = $share_fee_text['share_fees_registration_choice_required'];
            } else {
                $tpl_vars['feeshareattachmenterror'] = 0;
            }
            $tpl_vars['user_attachment_id'] = $this->userAttachmentSet;
            $tpl_vars['feeshareattachment_required'] = $share_fees->required;
        } else {
            //no user attachment choices
            $tpl_vars['sharefeeattachmentchoices'] = 0;
            $tpl_vars['feeshareattachmenterror'] = 0;
            $tpl_vars['user_attachment_id'] = 0;
        }

        //give access to registered_variables in template
        $tpl_vars['registered_variables'] = $this->registered_variables;
        $tpl_vars['addons_top'] = geoAddon::triggerDisplay('display_registration_form_top', null, geoAddon::RETURN_STRING);

        $view->setBodyTpl('registration_form.tpl', '', 'registration')
            ->setBodyVar($tpl_vars);

        $this->display_page();
        return true;
    } //end of function registration_form_1()

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function insert_user($api = false, $skip_addon = false)
    {
        trigger_error('DEBUG REGISTER: TOP OF INSERT_USER');
        if (!isset($this->product_configuration) || !is_object($this->product_configuration)) {
            $this->product_configuration = geoPC::getInstance();
        }
        $this->page_id = 18;
        $this->get_text();
        if ($api) {
            $this->error = false;
        }
        //there are no error in the final part of the form so enter everything into the database
        //get unique id

        if (
            !$api && ($this->db->get_site_setting('use_email_verification_at_registration') ||
            $this->db->get_site_setting('admin_approves_all_registration'))
        ) {
            do {
                //TODO:  make this not suck so much, just to set ID in confirm table...
                //really ID column should be an autoincrement int field, not a varchar...
                $id = md5(uniqid(rand()));
                $id = preg_replace("/[a-f]/i", "", $id);
                $id = (int)substr($id, 0, 6);
                $sql = "SELECT * FROM " . $this->confirm_email_table . " WHERE id = \"" . $id . "\"";
                //$this->body .=$sql." is the query<br />\n";
                $result = $this->db->Execute($sql);
                if ($this->debug_register) {
                    echo $sql . "<br />\n";
                }
                if (!$result) {
                    if ($this->debug_register) {
                        echo $sql . "<br />\n";
                    }
                    $this->error["registration"] = urldecode($this->messages[324]);
                    return false;
                }
            } while ($id && $result->RecordCount() > 0);

            $this->username = trim($this->username);
            //insert into the confirm_email table and get an id
            $this->hash = md5($this->db->get_site_setting('secret_for_hash') . $this->username);

            $time = geoUtil::time();
            //$sql = "insert into ".$this->confirm_email_table."
            //  (id,email,mdhash,date)
            //  VALUES
            //  (\"".$id."\",\"".$this->registered_variables["email"]."\",\"".$this->hash."\",\"".$time."\")";
            $sql = "insert into " . $this->confirm_email_table . "
				(id,email,mdhash,date)
				VALUES (?, ?, ?, ?)";
            $query_data = array ($id,$this->registered_variables["email"],$id,$time);

            if ($this->debug_register) {
                echo $sql . "<br />\n";
            }
            $email_confirm_result = $this->db->Execute($sql, $query_data);
            if (!$email_confirm_result) {
                if ($this->debug_register) {
                    echo $sql . "<br />\n";
                }
                $this->error["registration"] = urldecode($this->messages[324]);
                return false;
            }

            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
                if ($ip == '') {
                    $ip = getenv('REMOTE_ADDR');
                }
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
            $user_ip = urlencode($ip);

            $terminal_region_id = is_array($_REQUEST['locations']) ? intval($_REQUEST['locations'][count($_REQUEST['locations'])]) : 0;

            $share_fees = geoAddon::getUtil('share_fees');
            if ((!$share_fees) || !$share_fees->active) {
                $this->registered_variables['user_attachment_id'] = 0;
            }

            //need to finish all inserts
            $newsletter = isset($newsletter) ? $newsletter : '';
            $this->registered_variables['registration_code'] = isset($this->registered_variables['registration_code']) ? $this->registered_variables['registration_code'] : '';
            $sql = "INSERT INTO " . $this->confirm_table . "
				(mdhash, id, username, password,date,firstname, lastname,
				address, address_2, city, state, country, zip, phone,phone_2,
				fax, email, email2, company_name,business_type, url,
				optional_field_1,optional_field_2,optional_field_3,optional_field_4,optional_field_5,
				optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10,
				newsletter,group_id,registration_code,user_ip, terminal_region_id,feeshareattachment)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $query_data = array($this->hash,$id,$this->username, $this->password,
                $time,
                $this->registered_variables['firstname'],
                $this->registered_variables['lastname'],
                $this->registered_variables['address'] . '',
                $this->registered_variables['address_2'] . '',
                $this->registered_variables['city'] . '',
                $this->registered_variables['state'] . '',
                $this->registered_variables['country'] . '',
                $this->registered_variables['zip'] . '',
                $this->registered_variables['phone'] . '',
                $this->registered_variables['phone_2'] . '',
                $this->registered_variables['fax'] . '',
                $this->registered_variables['email'] . '',
                $this->registered_variables['email2'] . '',
                $this->registered_variables['company_name'] . '',
                $this->registered_variables['business_type'] . '',
                $this->registered_variables['url'] . '',
                $this->registered_variables['optional_field_1'] . '',
                $this->registered_variables['optional_field_2'] . '',
                $this->registered_variables['optional_field_3'] . '',
                $this->registered_variables['optional_field_4'] . '',
                $this->registered_variables['optional_field_5'] . '',
                $this->registered_variables['optional_field_6'] . '',
                $this->registered_variables['optional_field_7'] . '',
                $this->registered_variables['optional_field_8'] . '',
                $this->registered_variables['optional_field_9'] . '',
                $this->registered_variables['optional_field_10'] . '',
                $newsletter,
                $this->registration_group,
                $this->registered_variables['registration_code'] . '',
                $user_ip,
                $terminal_region_id,
                $this->registered_variables['user_attachment_id']);

            //echo substr_count($sql, '?').' '.count($query_data).' '.$sql;
            $result = $this->db->Execute($sql, $query_data);
            if ($this->debug_register) {
                echo $sql . "<br />\n";
            }
            if (!$result) {
                trigger_error('ERROR REGISTER SQL: Insert into confirm table failed. query: ' . $sql . ' db reports: ' . $this->db->ErrorMsg());
                $this->error["registration"] = urldecode($this->messages[324]);
                return false;
            }
            if ($this->db->get_site_setting('use_email_verification_at_registration')) {
                //if using e-mail verification, then admin approves all registration
                //must be turned off.
                $this->page_id = 20;
                $this->get_text();

                if ($this->db->get_site_setting('use_ssl_in_registration')) {
                    $return_url = trim($this->db->get_site_setting('registration_ssl_url'));
                } else {
                    $return_url =  trim($this->db->get_site_setting('registration_url'));
                }
                $confirmurl = ($return_url . "?b=3&hash=" . "$id" . "&username=" . urlencode($this->username));

                $mailto = $this->registered_variables["email"];
                $subject = urldecode($this->messages[228]);

                $tpl = new geoTemplate('system', 'emails');
                $tpl->assign('introduction', $this->messages[672]);
                $tpl->assign('salutation', $this->get_salutation($this->registered_variables));
                $tpl->assign('messageBody', $this->messages[229]);
                $tpl->assign('usernameLabel', $this->messages[1329]);
                $tpl->assign('username', $this->username);
                $tpl->assign('emailLabel', $this->messages[1331]);
                $tpl->assign('email', $this->registered_variables['email']);
                $tpl->assign('confirmLinkInstructions', $this->messages[230]);
                $tpl->assign('confirmLink', $confirmurl);
                $tpl->assign('finalInstructions', $this->messages[231]);
                $message = $tpl->fetch('registration/registration_verification.tpl');

                $from = $this->db->get_site_setting('registration_admin_email');

                trigger_error('DEBUG STATS: Sending Verification E-Mail: PRE');
                geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/html');
                trigger_error('DEBUG STATS: Sending Verification E-Mail: POST');
            }
            if ($this->db->get_site_setting('send_register_attempt_email_admin')) {
                $mailto = $this->db->get_site_setting('registration_admin_email');
                $subject = "NOTIFY " . $this->site_name . " Registration attempt";

                $from = $this->db->get_site_setting('registration_admin_email');

                $message = "Username : " . $this->username . "\nE-Mail : " . $this->registered_variables['email'] . "\n\n";
                $message .= "registration code: " . $this->registered_variables["registration_code"] . "\n";
                $message .= "just registered: " . $this->registered_variables["username"] . "\n";
                $message .= "user_id: " . $this->user_id . "\n";
                $message .= "username: " . $this->registered_variables["username"] . "\n";
                $message .= "email: " . $this->registered_variables["email"] . "\n";
                $message .= "email2: " . $this->registered_variables["email2"] . "\n";
                $message .= "company name: " . $this->registered_variables["company_name"] . "\n";
                $message .= "business type: " . $business_type . "\n";
                $message .= "first name: " . $this->registered_variables["firstname"] . "\n";
                $message .= "last name: " . $this->registered_variables["lastname"] . "\n";
                $message .= "address: " . $this->registered_variables["address"] . "\n";
                $message .= "address line 2: " . $this->registered_variables["address_2"] . "\n";
                $message .= "city: " . $this->registered_variables["city"] . "\n";
                $message .= "state: " . $this->registered_variables["state"] . "\n";
                $message .= "zip: " . $this->registered_variables["zip"] . "\n";
                $message .= "country: " . $this->registered_variables["country"] . "\n";
                $message .= "phone: " . $this->registered_variables["phone"] . "\n";
                $message .= "phone 2: " . $this->registered_variables["phone_2"] . "\n";
                $message .= "fax: " . $this->registered_variables["fax"] . "\n";
                $message .= "url: " . $this->registered_variables["url"] . "\n";
                $message .= "optional field 1: " . $this->registered_variables["optional_field_1"] . "\n";
                $message .= "optional field 2: " . $this->registered_variables["optional_field_2"] . "\n";
                $message .= "optional field 3: " . $this->registered_variables["optional_field_3"] . "\n";
                $message .= "optional field 4: " . $this->registered_variables["optional_field_4"] . "\n";
                $message .= "optional field 5: " . $this->registered_variables["optional_field_5"] . "\n";
                $message .= "optional field 6: " . $this->registered_variables["optional_field_6"] . "\n";
                $message .= "optional field 7: " . $this->registered_variables["optional_field_7"] . "\n";
                $message .= "optional field 8: " . $this->registered_variables["optional_field_8"] . "\n";
                $message .= "optional field 9: " . $this->registered_variables["optional_field_9"] . "\n";
                $message .= "optional field 10: " . $this->registered_variables["optional_field_10"] . "\n";

                $ip = $_SERVER['REMOTE_ADDR'];
                $host = @gethostbyaddr($ip);
                //$host = preg_replace("/^[^.]+./", "*.", $host);

                $message .= "\nregistered from this ip and host: " . $_SERVER["REMOTE_ADDR"] . " : " . $host;
                trigger_error('DEBUG STATS: Sending Admin E-Mail: PRE');
                geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/plain');
                trigger_error('DEBUG STATS: Sending Admin E-Mail: POST');
            }

            //if any addons are adding registration variables, let them know we're doing the confirmation step
            $addonVars = array('user_id' => $id, 'confirmation_step' => 1, 'registration_variables' => $this->registered_variables);
            geoAddon::triggerUpdate('registration_add_field_update', $addonVars);
        } else {
            if (!$api && $this->db->get_site_setting('registration_approval')) {
                $current_status = 3;
            } else {
                $current_status = 1;
            }

            $this->registered_variables["username"] = trim($this->registered_variables["username"]);
            $sql = "insert into " . $this->db->geoTables->logins_table . " (username, password, hash_type, salt, status, api_token)
		  		values (?, ?, ?, ?, ?, ?)";
            $hash_type = '';
            $salt = '';
            if ($api && $this->registered_variables['use_blank_password']) {
                $hashed_password = '';
            } else {
                $hash_type = $this->db->get_site_setting('client_pass_hash');
                $hashed_password = $this->product_configuration->get_hashed_password($this->registered_variables["username"], $this->registered_variables["password"], $hash_type);
                if (is_array($hashed_password)) {
                    $salt = '' . $hashed_password['salt'];
                    $hashed_password = '' . $hashed_password['password'];
                }
            }
            $api_token = '';
            if ($api && $this->registered_variables['set_api_token']) {
                do {
                    //generate random key
                    $api_token = sha1(rand());

                    $sql_token = 'SELECT `api_token` FROM `geodesic_logins` WHERE `api_token` = ?';
                    $result = $this->db->Execute($sql_token, array($api_token));
                } while ($result && $result->RecordCount() > 0);
            }

            $query_data = array($this->registered_variables["username"], $hashed_password, $hash_type, $salt, $current_status, $api_token);
            if ($this->debug_register) {
                echo $sql . "<br />\n";
            }
            $login_result = $this->db->Execute($sql, $query_data);
            if (!$login_result) {
                if ($api) {
                    $this->error = 'Insert into login table failed. Debug error msg: ' . $this->db->ErrorMsg();
                    return false;
                }

                if ($this->debug_register) {
                    echo $this->db->ErrorMsg() . "<br />\n";
                    echo $sql . "<br />\n";
                }
                $this->error['confirm'] = "error1";
                return false;
            } else {
                $this->user_id = $this->db->Insert_ID();
                //insert login data into the login table

                //if (!defined('DEFAULT_COMMUNICATION_SETTING'))
                    //$default_communication_setting = 1;
                //else
                    //$default_communication_setting = DEFAULT_COMMUNICATION_SETTING;
                $default_communication_setting = $this->db->get_site_setting('default_communication_setting');

                if ((strlen(trim($default_communication_setting)) == 0) || ($default_communication_setting == 0)) {
                    $default_communication_setting = 1;
                }

                $currentIP = getenv('REMOTE_ADDR');

                $sql = "insert into " . $this->userdata_table . " (id,username,email,email2,newsletter,level,company_name,
				business_type,firstname,lastname,address,address_2,zip,city,state,country,phone,phone2,fax,url,date_joined,
				communication_type,rate_sum,rate_num,optional_field_1,optional_field_2,optional_field_3,optional_field_4,
				optional_field_5,optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10, last_login_time, last_login_ip, new_listing_alert_last_sent) values
					(" . $this->user_id . ",\"" . $this->registered_variables["username"] . "\",\"" . $this->registered_variables["email"] . "\",
					\"" . $this->registered_variables["email2"] . "\",
					\"0\", 0,\"" . addslashes($this->registered_variables["company_name"]) . "\",
					\"" . $this->registered_variables["business_type"] . "\",\"" . addslashes($this->registered_variables["firstname"]) . "\",
					\"" . addslashes($this->registered_variables["lastname"]) . "\",
					\"" . addslashes($this->registered_variables["address"]) . "\",\"" . addslashes($this->registered_variables["address_2"]) . "\",
					\"" . addslashes($this->registered_variables["zip"]) . "\",
					\"" . addslashes($this->registered_variables["city"]) . "\",\"" . $this->registered_variables["state"] . "\",
					\"" . $this->registered_variables["country"] . "\",
		  			\"" . addslashes($this->registered_variables["phone"]) . "\",\"" . addslashes($this->registered_variables["phone_2"]) . "\",
		  			\"" . addslashes($this->registered_variables["fax"]) . "\",\"" . addslashes($this->registered_variables["url"]) . "\"," . geoUtil::time() . ",\"" . $default_communication_setting . "\",0,0,
					\"" . addslashes($this->registered_variables["optional_field_1"]) . "\",\"" . addslashes($this->registered_variables["optional_field_2"]) . "\",
					\"" . addslashes($this->registered_variables["optional_field_3"]) . "\",\"" . addslashes($this->registered_variables["optional_field_4"]) . "\",
					\"" . addslashes($this->registered_variables["optional_field_5"]) . "\",\"" . addslashes($this->registered_variables["optional_field_6"]) . "\",
					\"" . addslashes($this->registered_variables["optional_field_7"]) . "\",\"" . addslashes($this->registered_variables["optional_field_8"]) . "\",
					\"" . addslashes($this->registered_variables["optional_field_9"]) . "\",\"" . addslashes($this->registered_variables["optional_field_10"]) . "\",
					NOW(), \"{$currentIP}\", " . geoUtil::time() . ")";
                if ($this->debug_register) {
                    echo $sql . "<br />\n";
                }
                $userdata_result = $this->db->Execute($sql);
                if ($this->debug_register) {
                    echo $this->registered_variables["business_type"] . " is business type<br />\n";
                }
                if (!$userdata_result) {
                    if ($api) {
                        $this->error = 'Insert into userdata table failed. error msg: ' . $this->db->ErrorMsg();
                        return false;
                    }
                    $this->site_error($sql, $this->db->ErrorMsg());
                    if ($this->debug_register) {
                        echo $sql . " is the query<br />\n";
                    }
                    $this->error['confirm'] = "error";
                    return false;
                }
                //insert into users_group_price_plans table
                if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                    $price_plan = $this->get_price_plan_from_group(0, $this->registration_group);
                    $auction_price_plan = $this->get_price_plan_from_group(0, $this->registration_group, 1);
                } elseif (geoMaster::is('auctions')) {
                    $price_plan = $this->get_price_plan_from_group(0, $this->registration_group, 1);
                    $auction_price_plan = $price_plan;
                } elseif (geoMaster::is('classifieds')) {
                    $price_plan = $this->get_price_plan_from_group(0, $this->registration_group);
                    $auction_price_plan = $price_plan;
                }

                $sql = "insert into " . $this->user_groups_price_plans_table . "
					(id,group_id,price_plan_id,auction_price_plan_id)
					values
					(" . $this->user_id . "," . $this->registration_group . ",\"" . $price_plan->PRICE_PLAN_ID . "\",\"" . $auction_price_plan->PRICE_PLAN_ID . "\")";
                if ($this->debug_register) {
                    echo $sql . " is the query<br />\n";
                }
                $group_result = $this->db->Execute($sql);
                if (!$group_result) {
                    if ($api) {
                        $this->error = 'Insert into user group table failed. error msg: ' . $this->db->ErrorMsg();
                        return false;
                    }
                    $this->site_error($sql, $this->db->ErrorMsg());
                    if ($this->debug_register) {
                        echo $sql . " is the query<br />\n";
                    }
                    $this->error['confirm'] = "error";
                    return false;
                }

                //check for expiration of price plans
                if ($price_plan->EXPIRATION_TYPE == 2) {
                    //dynamic expiration of this price plan from the date of registration
                    $expiration_date = (geoUtil::time() + ($price_plan->EXPIRATION_FROM_REGISTRATION * 84600));

                    $sql = "insert into " . $this->expirations_table . "
		  				(type,user_id,expires,type_id)
		  				values
		  				(2," . $this->user_id . "," . $expiration_date . "," . $price_plan->PRICE_PLAN_ID . ")";
                    $plan_expiration_result = $this->db->Execute($sql);
                    if ($this->debug_register) {
                        echo $sql . "<br />\n";
                    }
                    if (!$plan_expiration_result) {
                        if ($api) {
                            $this->error = 'Insert into expirations table failed. error msg: ' . $this->db->ErrorMsg();
                            return false;
                        }
                        $this->site_error($sql, $this->db->ErrorMsg());
                        if ($this->debug_register) {
                            echo $sql . "<br />\n";
                        }
                        $this->error['confirm'] = "error";
                        return false;
                    }
                }

                if ($auction_price_plan->EXPIRATION_TYPE == 2) {
                    //dynamic expiration of this price plan from the date of registration
                    $expiration_date = (geoUtil::time() + ($auction_price_plan->EXPIRATION_FROM_REGISTRATION * 84600));

                    $sql = "insert into " . $this->expirations_table . "
		  				(type,user_id,expires,type_id)
		  				values
		  				(2," . $this->user_id . "," . $expiration_date . "," . $auction_price_plan->PRICE_PLAN_ID . ")";
                    $plan_expiration_result = $this->db->Execute($sql);
                    if ($this->debug_register) {
                        echo $sql . "<br />\n";
                    }
                    if (!$plan_expiration_result) {
                        if ($api) {
                            $this->error = 'Insert into plan expirations table failed. error msg: ' . $this->db->ErrorMsg();
                            return false;
                        }
                        $this->site_error($sql, $this->db->ErrorMsg());
                        if ($this->debug_register) {
                            echo $sql . "<br />\n";
                        }
                        $this->error['confirm'] = "error";
                        return false;
                    }
                }

                //check to see if registration credits or free subscription period
                if ($price_plan->TYPE_OF_BILLING == 1) {
                    // Insert initial site balance

                    if ($this->debug_register) {
                        echo "about to check for initial balance<br />\n";
                        echo $this->db->get_site_setting('positive_balances_only') . " is positive_balances_only<br />\n";
                        echo $this->db->get_site_setting('use_account_balance') . " is use_account_balance<br />\n";
                        echo $price_plan->INITIAL_SITE_BALANCE . " is INITIAL_SITE_BALANCE for price plan<br />";
                    }

                    if ((!geoMaster::is('classifieds')) && (geoMaster::is('auctions'))) {
                        $this->add_initial_site_balance($auction_price_plan);
                    } else {
                        $this->add_initial_site_balance($price_plan);
                    }
                } elseif ($price_plan->TYPE_OF_BILLING == 2) {
                    if ((geoPC::is_ent() || geoPC::is_premier()) && $price_plan->FREE_SUBSCRIPTION_PERIOD_UPON_REGISTRATION > 0) {
                        //get expiration from now
                        $expiration = (($price_plan->FREE_SUBSCRIPTION_PERIOD_UPON_REGISTRATION * 86400) + geoUtil::time());

                        $sql = "insert into " . $this->user_subscriptions_table . "	(price_plan_id, user_id, subscription_expire) VALUES (?,?,?)";
                        $free_subscription_result = $this->db->Execute($sql, array($price_plan->PRICE_PLAN_ID, $this->user_id, $expiration));
                        if (!$free_subscription_result) {
                            if ($api) {
                                $this->error = 'Insert into user subscriptions table failed. error msg: ' . $this->db->ErrorMsg();
                                return false;
                            }
                            $this->site_error($sql, $this->db->ErrorMsg());
                            if ($this->debug_register) {
                                echo $sql . "<br />\n";
                            }
                            $this->error['confirm'] = "error";
                            return false;
                        }
                    }
                }

                //set regions as selected
                $geographicRegions = $_REQUEST['locations'];

                if ($geographicRegions) {
                    geoRegion::setUserRegions($this->user_id, $geographicRegions);
                }

                $share_fees = geoAddon::getUtil('share_fees');
                if (($share_fees) && $share_fees->active) {
                    //insert user attachment if there is an attachment
                    if ($this->registered_variables["user_attachment_id"]) {
                        //if attachment check valid user attached to and that registrant can be attached to attached user
                        if (!$share_fees->insertUserAttachment($this->user_id, $this->registered_variables["user_attachment_id"])) {
                        }
                    } elseif ($share_fees->required) {
                        //there is no attachment and this attachment is required
                        return false;
                    }
                }

                //send email saying registration is complete
                if (!$api && $this->db->get_site_setting('send_register_complete_email_client')) {
                    $this->page_id = 21;
                    $this->get_text();
                    $mailto = $this->registered_variables["email"];
                    $subject = urldecode($this->messages[678]);

                    $tpl = new geoTemplate('system', 'emails');
                    $tpl->assign('userdata', geoUser::getUser($this->user_id)->toArray());
                    $tpl->assign('introduction', $this->messages[676]);
                    $tpl->assign('salutation', $this->get_salutation($this->registered_variables));
                    $tpl->assign('messageBody', $this->messages[677]);
                    $message = $tpl->fetch('registration/registration_complete.tpl');

                    $from = $this->db->get_site_setting('registration_admin_email');

                    geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/html');
                }

                if ($this->db->get_site_setting('send_register_complete_email_admin')) {
                    if ($this->registered_variables["business_type"] == 1) {
                        $business_type = "individual";
                    } elseif ($this->registered_variables["business_type"] == 2) {
                        $business_type = "business";
                    } else {
                        $business_type = "none";
                    }
                    $mailto = $this->db->get_site_setting('registration_admin_email');
                    $subject = "registration complete for " . $this->registered_variables["username"];
                    $message = "registration code: " . $this->registered_variables["registration_code"] . "\n";
                    $message .= "just registered: " . $this->registered_variables["username"] . "\n";
                    $message .= "user_id: " . $this->user_id . "\n";
                    $message .= "username: " . $this->registered_variables["username"] . "\n";
                    $message .= "email: " . $this->registered_variables["email"] . "\n";
                    $message .= "email2: " . $this->registered_variables["email2"] . "\n";
                    $message .= "company name: " . $this->registered_variables["company_name"] . "\n";
                    $message .= "business type: " . $business_type . "\n";
                    $message .= "first name: " . $this->registered_variables["firstname"] . "\n";
                    $message .= "last name: " . $this->registered_variables["lastname"] . "\n";
                    $message .= "address: " . $this->registered_variables["address"] . "\n";
                    $message .= "address line 2: " . $this->registered_variables["address_2"] . "\n";
                    $message .= "city: " . $this->registered_variables["city"] . "\n";
                    $message .= "state: " . $this->registered_variables["state"] . "\n";
                    $message .= "zip: " . $this->registered_variables["zip"] . "\n";
                    $message .= "country: " . $this->registered_variables["country"] . "\n";
                    $message .= "phone: " . $this->registered_variables["phone"] . "\n";
                    $message .= "phone 2: " . $this->registered_variables["phone_2"] . "\n";
                    $message .= "fax: " . $this->registered_variables["fax"] . "\n";
                    $message .= "url: " . $this->registered_variables["url"] . "\n";
                    $message .= "optional field 1: " . $this->registered_variables["optional_field_1"] . "\n";
                    $message .= "optional field 2: " . $this->registered_variables["optional_field_2"] . "\n";
                    $message .= "optional field 3: " . $this->registered_variables["optional_field_3"] . "\n";
                    $message .= "optional field 4: " . $this->registered_variables["optional_field_4"] . "\n";
                    $message .= "optional field 5: " . $this->registered_variables["optional_field_5"] . "\n";
                    $message .= "optional field 6: " . $this->registered_variables["optional_field_6"] . "\n";
                    $message .= "optional field 7: " . $this->registered_variables["optional_field_7"] . "\n";
                    $message .= "optional field 8: " . $this->registered_variables["optional_field_8"] . "\n";
                    $message .= "optional field 9: " . $this->registered_variables["optional_field_9"] . "\n";
                    $message .= "optional field 10: " . $this->registered_variables["optional_field_10"] . "\n";
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $host = @gethostbyaddr($ip);
                    $message .= "\n" . $_SERVER["REMOTE_ADDR"] . " : " . $host;
                    $from = $this->db->get_site_setting('registration_admin_email');
                    geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/plain');
                }


                if (!$skip_addon) {
                    $this->registered_variables['user_id'] = $this->user_id;
                    geoAddon::triggerUpdate('user_register', $this->registered_variables);
                }

                //set the current session user_id to this new user_id
                $this->set_new_user_id_in_current_session();

                //if any addons are adding registration variables, let them know we're doing the registration step
                $addonVars = array('user_id' => $this->user_id, 'confirmation_step' => 0, 'registration_variables' => $this->registered_variables);
                geoAddon::triggerUpdate('registration_add_field_update', $addonVars);

                //special case: AFTER *all* the other addon stuff is done and everything is saved to DB, check with Subscription Pricing to see if we need to forward this user to its buy process
                $subby = geoAddon::getUtil('subscription_pricing');
                if ($subby) {
                    $subby->tryForceSubscriptionBuy($this->user_id);
                }
            }
        }
        return true;
    } //end of function insert_user

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function check_username($username = 0)
    {
        //$this->body .="hello from check_username<br />\n";
        $this->username = trim($username);
        $this->username = trim(preg_replace('#\s+#si', ' ', $this->username));
        $this->error['username'] = "";
        $username_length = strlen(trim($username));
        if (($username_length == 0 ) || ($username_length > $this->db->get_site_setting('max_user_length')) || ($username_length < $this->db->get_site_setting('min_user_length'))) {
            $this->error['username'] = "error1";
            $this->error_found++;
        }
        if (!preg_match('/^[-a-zA-Z0-9_. ]+$/', $this->username)) {
            $this->error['username'] = "error1";
            $this->error_found++;
        } else {
            $sql = "SELECT * FROM " . $this->logins_table . " WHERE username = ?";
            $loginResult = $this->db->Execute($sql, array($this->username));
            $sql = "SELECT * FROM " . $this->confirm_table . " WHERE username = ?";
            $confirmResult = $this->db->Execute($sql, array($this->username));
            if (!$loginResult || !$confirmResult) {
                $this->error["registration"] = "error";
                return false;
            }

            if ($loginResult->RecordCount() > 0 || $confirmResult->RecordCount() > 0) {
                $this->error['username'] = "error2";
                $this->error_found++;
            }
        }
         return true;
    } //end of function check_username($username)

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function check_password($username = null, $password = 0, $password_confirm = 0)
    {
        $this->password = trim($password);
        if ($password_confirm != $this->password) {
            //password not same as password confirm
            $this->error['password'] = 'confirm_not_match';
            $this->error_found++;
            return false;
        }
        $password_length = strlen($this->password);

        if ($password_length < $this->db->get_site_setting('min_pass_length') || $password_length > $this->db->get_site_setting('max_pass_length')) {
            //password less than the min or greater than the max characters
            $this->error['password'] = 'strlen';
            $this->error_found++;
            return false;
        }

        if ($this->password == trim($username)) {
            $this->error['password'] = 'username_match';
            $this->error_found++;
            return false;
        }
        return true;
    } //end of function check_password


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function check_agreement($agreement)
    {
        if ($this->registration_configuration->USE_USER_AGREEMENT_FIELD) {
            if ((empty($agreement)) || ($agreement != "yes")) {
                $this->error['yes_to_agreement'] = "error";
                $this->error_found++;
            }
        }
        return true;
    } //end of function check_agreement($agreement)

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //$admin only needs to be set (to 1) if the admin is the one doing the confirm
    public function confirm($hash = 0, $username = 0, $admin = 0)
    {
        $this->page_id = 21;
        $this->get_text();
        if (!$this->expire_confirmations()) {
            $this->error['confirm'] = "error";
            return false;
        }

        $username = trim(urldecode($username));
        if (($hash) && ($username)) {
            $sql = "select * from " . $this->confirm_table . " where id = ? AND username = ?";
            $confirm_result = $this->db->Execute($sql, array($hash, $username));
            if (!$confirm_result) {
                $this->site_error($sql, $this->db->ErrorMsg());
                $this->error['confirm'] = "error";
                return false;
            } elseif (($confirm_result->RecordCount() == 0) || ($confirm_result->RecordCount() > 1)) {
                //bad return or not in the confirm table
                if ($this->debug_register) {
                    echo 'count = ' . $confirm_result->RecordCount() . '<br/>';
                }
                $this->error['confirm'] = "error1";
                return false;
            } elseif ($confirm_result->RecordCount() == 1) {
                $show = $confirm_result->FetchNextObject();

                //double check the username again to make sure
                $this->error_found = 0;
                $this->check_username($show->USERNAME);
                if ($this->error_found > 1) {
                    //the username has been taken since trying to register the first time
                    $this->error['confirm'] = "error2";
                    return false;
                }

                if ($this->db->get_site_setting('registration_approval')) {
                    $current_status = 3;
                } else {
                    $current_status = 1;
                }

                $sql = "insert into " . $this->db->geoTables->logins_table . " (username, password,status)
		  			values (?, ?, ?)";
                //password is not yet hashed, so hash it.
                if (!isset($this->product_configuration) || !is_object($this->product_configuration)) {
                    $this->product_configuration = geoPC::getInstance();
                }
                $hashed_pass = $this->product_configuration->get_hashed_password($show->USERNAME, $show->PASSWORD, $this->db->get_site_setting('client_pass_hash'));
                $query_data = array($show->USERNAME, $hashed_pass,$current_status);
                $login_result = $this->db->Execute($sql, $query_data);
                if (!$login_result) {
                    $this->site_error($sql, $this->db->ErrorMsg());
                    $this->error['confirm'] = "error";
                    return false;
                } else {
                    $this->user_id = $this->db->Insert_ID();
                    //insert login data into the login table

                    $default_communication_setting = $this->db->get_site_setting('default_communication_setting');

                    if ((strlen(trim($default_communication_setting)) == 0) || ($default_communication_setting == 0)) {
                        $default_communication_setting = 1;
                    }

                    $sql = "insert into " . $this->userdata_table . "
						(id,username,email,email2,newsletter,level,company_name,
						business_type,firstname,lastname,address,address_2,zip,city,state,country,phone,phone2,fax,url,date_joined,
						communication_type,rate_sum,rate_num,optional_field_1,optional_field_2,optional_field_3,optional_field_4,
						optional_field_5,optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10, new_listing_alert_last_sent) values
						(" . $this->user_id . ",\"" . $show->USERNAME . "\",\"" . $show->EMAIL . "\",
						\"" . $show->EMAIL2 . "\",
						\"0\", 0,\"" . addslashes($show->COMPANY_NAME) . "\",
						\"" . $show->BUSINESS_TYPE . "\",\"" . addslashes($show->FIRSTNAME) . "\",
						\"" . addslashes($show->LASTNAME) . "\",
						\"" . addslashes($show->ADDRESS) . "\",\"" . addslashes($show->ADDRESS_2) . "\",
						\"" . addslashes($show->ZIP) . "\",
						\"" . addslashes($show->CITY) . "\",\"" . $show->STATE . "\",
						\"" . $show->COUNTRY . "\",
						\"" . addslashes($show->PHONE) . "\",\"" . addslashes($show->PHONE_2) . "\",
						\"" . addslashes($show->FAX) . "\",\"" . addslashes($show->URL) . "\"," . geoUtil::time() . "," . $default_communication_setting . ",0,0,
						\"" . addslashes($show->OPTIONAL_FIELD_1) . "\",\"" . addslashes($show->OPTIONAL_FIELD_2) . "\",
						\"" . addslashes($show->OPTIONAL_FIELD_3) . "\",\"" . addslashes($show->OPTIONAL_FIELD_4) . "\",
						\"" . addslashes($show->OPTIONAL_FIELD_5) . "\",\"" . addslashes($show->OPTIONAL_FIELD_6) . "\",
						\"" . addslashes($show->OPTIONAL_FIELD_7) . "\",\"" . addslashes($show->OPTIONAL_FIELD_8) . "\",
						\"" . addslashes($show->OPTIONAL_FIELD_9) . "\",\"" . addslashes($show->OPTIONAL_FIELD_10) . "\",
						" . geoUtil::time() . ")";

                    $userdata_result = $this->db->Execute($sql);
                    if (!$userdata_result) {
                        $this->site_error($sql, $this->db->ErrorMsg());
                        $this->error['confirm'] = "error";
                        return false;
                    } else {
                        //insert regions
                        $terminalRegion = $show->TERMINAL_REGION_ID;
                        if ($terminalRegion) {
                            $allRegions = geoRegion::getRegionWithParents($terminalRegion);
                            geoRegion::setUserRegions($this->user_id, $allRegions);
                        }


                        //insert into users_group_price_plans table
                        if (geoMaster::is('classifieds')) {
                            $price_plan = $this->get_price_plan_from_group(0, $show->GROUP_ID);
                        }
                        if (geoMaster::is('auctions')) {
                            $auction_price_plan = $this->get_price_plan_from_group(0, $show->GROUP_ID, 1);
                        }

                        //check for expiration of price plans
                        if ($price_plan->EXPIRATION_TYPE == 2) {
                            //dynamic expiration of this price plan from the date of registration
                            $expiration_date = (geoUtil::time() + ($price_plan->EXPIRATION_FROM_REGISTRATION * 84600));

                            $sql = "insert into " . $this->expirations_table . "
								(type,user_id,expires,type_id)
								values
								(2," . $this->user_id . "," . $expiration_date . "," . $price_plan->PRICE_PLAN_ID . ")";
                            $plan_expiration_result = $this->db->Execute($sql);
                            if (!$plan_expiration_result) {
                                $this->site_error($sql, $this->db->ErrorMsg());
                                $this->error['confirm'] = "error";
                                return false;
                            }
                        }

                        if ($auction_price_plan->EXPIRATION_TYPE == 2) {
                            //dynamic expiration of this price plan from the date of registration
                            $expiration_date = (geoUtil::time() + ($auction_price_plan->EXPIRATION_FROM_REGISTRATION * 84600));

                            $sql = "insert into " . $this->expirations_table . "
								(type,user_id,expires,type_id)
								values
								(2," . $this->user_id . "," . $expiration_date . "," . $auction_price_plan->PRICE_PLAN_ID . ")";
                            $plan_expiration_result = $this->db->Execute($sql);
                            if ($this->debug_register) {
                                echo $sql . " is the query<br />\n";
                            }
                            if (!$plan_expiration_result) {
                                $this->site_error($sql, $this->db->ErrorMsg());
                                if ($this->debug_register) {
                                    echo $sql . " is the query<br />\n";
                                }
                                $this->error['confirm'] = "error";
                                return false;
                            }
                        }
                        //ALWAYS have both price plan ID's set, so it doesn't break when upgrading from Classifieds (or auctions) to ClassAuctions.
                        $c_id = 1; //default classifieds ID
                        $a_id = 2; //default auctions ID

                        if (geoMaster::is('classifieds') && $price_plan->PRICE_PLAN_ID) {
                            $c_id = (int)$price_plan->PRICE_PLAN_ID;
                        }
                        if (geoMaster::is('auctions') && $auction_price_plan->PRICE_PLAN_ID) {
                            $a_id = (int)$auction_price_plan->PRICE_PLAN_ID;
                        }
                        $sql = "INSERT INTO " . geoTables::user_groups_price_plans_table . "
								(id, group_id, price_plan_id, auction_price_plan_id)
									values
									({$this->user_id}, {$show->GROUP_ID}, {$c_id}, {$a_id})";

                        $group_result = $this->db->Execute($sql);
                        if (!$group_result) {
                            $this->site_error($sql, $this->db->ErrorMsg());
                            if ($this->debug_register) {
                                echo $sql . " is the query<br />\n";
                            }
                            $this->error['confirm'] = "error";
                            return false;
                        }

                        $initial_account_balance_given = 0;
                        if (geoMaster::is('classifieds')) {
                            //check to see if registration credits or free subscription period
                            if ($price_plan->TYPE_OF_BILLING == 1) {
                                //fee based subscriptions

                                // Insert initial site balance
                                if ($this->debug_register) {
                                    echo "about to check for initial balance<br />\n";
                                    echo $this->db->get_site_setting('positive_balances_only') . " is positive_balances_only<br />\n";
                                    echo $this->db->get_site_setting('use_account_balance') . " is use_account_balance<br />\n";
                                }
                                if ((!geoMaster::is('classifieds')) && (geoMaster::is('auctions'))) {
                                    $this->add_initial_site_balance($auction_price_plan);
                                } else {
                                    $this->add_initial_site_balance($price_plan);
                                }
                            } elseif ($price_plan->TYPE_OF_BILLING == 2) {
                                //subscription based
                                if ($price_plan->FREE_SUBSCRIPTION_PERIOD_UPON_REGISTRATION > 0) {
                                    //get expiration from now
                                    $expiration = (($price_plan->FREE_SUBSCRIPTION_PERIOD_UPON_REGISTRATION * 86400) + geoUtil::time());

                                    $sql = "insert into " . $this->user_subscriptions_table . "	(price_plan_id, user_id, subscription_expire) VALUES (?,?,?)";
                                    $free_subscription_result = $this->db->Execute($sql, array($price_plan->PRICE_PLAN_ID, $this->user_id, $expiration));
                                    if (!$free_subscription_result) {
                                        $this->site_error($sql, $this->db->ErrorMsg());
                                        $this->error['confirm'] = "error";
                                        return false;
                                    }
                                }
                            }
                        } else {
                            //check to see if free subscription period
                            if ($auction_price_plan->TYPE_OF_BILLING == 1) {
                                //fee based subscriptions

                                // Insert initial site balance
                                if ($this->debug_register) {
                                    echo "about to check for initial balance<br />\n";
                                    echo $this->db->get_site_setting('positive_balances_only') . " is positive_balances_only<br />\n";
                                    echo $this->db->get_site_setting('use_account_balance') . " is use_account_balance<br />\n";
                                }
                                if ((!geoMaster::is('classifieds')) && (geoMaster::is('auctions'))) {
                                    $this->add_initial_site_balance($auction_price_plan);
                                } else {
                                    $this->add_initial_site_balance($price_plan);
                                }
                            } elseif ($auction_price_plan->TYPE_OF_BILLING == 2) {
                                //subscription based
                                if ($auction_price_plan->FREE_SUBSCRIPTION_PERIOD_UPON_REGISTRATION > 0) {
                                    //get expiration from now
                                    $expiration = (($auction_price_plan->FREE_SUBSCRIPTION_PERIOD_UPON_REGISTRATION * 86400) + geoUtil::time());

                                    $sql = "insert into " . $this->user_subscriptions_table . "	(price_plan_id, user_id, subscription_expire) VALUES (?,?,?)";
                                    $free_subscription_result = $this->db->Execute($sql, array($auction_price_plan->PRICE_PLAN_ID, $this->user_id, $expiration));
                                    if (!$free_subscription_result) {
                                        $this->site_error($sql, $this->db->ErrorMsg());
                                        $this->error['confirm'] = "error";
                                        return false;
                                    }
                                }
                            }
                        }

                        $share_fees = geoAddon::getUtil('share_fees');
                        if (($share_fees) && ($share_fees->active) && $show->FEESHAREATTACHMENT) {
                            //insert user attachment if there is an attachment attachment active
                            if (!$share_fees->insertUserAttachment($this->user_id, $show->FEESHAREATTACHMENT)) {
                                //echo "could not insert user attachment:".$this->user_id.", ".$show->FEESHAREATTACHMENT."<br>";
                                return false;
                            }
                        }

                        //delete from the confirm table
                        $sql = "delete from " . $this->confirm_table . " where username = \"" . $username . "\"";
                        $delete_result = $this->db->Execute($sql);
                        if (!$delete_result) {
                            $this->site_error($sql, $this->db->ErrorMsg());
                            $this->error['confirm'] = "error";
                            return false;
                        }

                        $sql = "delete from " . $this->confirm_email_table . " where mdhash = \"" . $hash . "\"";
                        $email_result = $this->db->Execute($sql);
                        if (!$email_result) {
                            $this->site_error($sql, $this->db->ErrorMsg());
                            $this->error['confirm'] = "error";
                            return false;
                        }

                        //send email saying registration is complete
                        if ($this->db->get_site_setting('send_register_complete_email_client')) {
                            $mailto = $show->EMAIL;
                            $subject = urldecode($this->messages[678]);

                            $tpl = new geoTemplate('system', 'emails');
                            $tpl->assign('userdata', geoUser::getUser($this->user_id)->toArray());
                            $tpl->assign('introduction', $this->messages[676]);
                            $tpl->assign('salutation', $this->get_salutation($show));
                            $tpl->assign('messageBody', $this->messages[677]);
                            $message = $tpl->fetch('registration/registration_complete.tpl');

                            $from = $this->db->get_site_setting('registration_admin_email');

                            geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/html');
                        }

                        if ($this->db->get_site_setting('send_register_complete_email_admin') && !$admin) {
                            if ($show->BUSINESS_TYPE == 1) {
                                $business_type = "individual";
                            } elseif ($show->BUSINESS_TYPE == 2) {
                                $business_type = "business";
                            } else {
                                $business_type = "none";
                            }
                            $mailto = $this->db->get_site_setting('registration_admin_email');
                            $subject = urldecode($this->messages[679]);

                            $this->page_id = 15;
                            $this->get_text();
                            $message = "just registered: " . $show->USERNAME . "\n";
                            $message .= "registration code: " . $show->REGISTRATION_CODE . "\n";
                            $message .= "user_id: " . $this->user_id . "\n";
                            $message .= "email: " . $show->EMAIL . "\n";
                            $message .= "email2: " . $show->EMAIL2 . "\n";
                            $message .= "company name: " . $show->COMPANY_NAME . "\n";
                            $message .= "business type: " . $business_type . "\n";
                            $message .= "first name: " . $show->FIRSTNAME . "\n";
                            $message .= "last name: " . $show->LASTNAME . "\n";
                            $message .= "address: " . $show->ADDRESS . "\n";
                            $message .= "address line 2: " . $show->ADDRESS_2 . "\n";
                            $message .= "city: " . $show->CITY . "\n";
                            $message .= "state: " . $show->STATE . "\n";
                            $message .= "zip: " . $show->ZIP . "\n";
                            $message .= "country: " . $show->COUNTRY . "\n";
                            $message .= "phone: " . $show->PHONE . "\n";
                            $message .= "phone 2: " . $show->PHONE_2 . "\n";
                            $message .= "fax: " . $show->FAX . "\n";
                            $message .= "url: " . $show->URL . "\n";
                            $message .= "optional field 1: " . $show->OPTIONAL_FIELD_1 . "\n";
                            $message .= "optional field 2: " . $show->OPTIONAL_FIELD_2 . "\n";
                            $message .= "optional field 3: " . $show->OPTIONAL_FIELD_3 . "\n";
                            $message .= "optional field 4: " . $show->OPTIONAL_FIELD_4 . "\n";
                            $message .= "optional field 5: " . $show->OPTIONAL_FIELD_5 . "\n";
                            $message .= "optional field 6: " . $show->OPTIONAL_FIELD_6 . "\n";
                            $message .= "optional field 7: " . $show->OPTIONAL_FIELD_7 . "\n";
                            $message .= "optional field 8: " . $show->OPTIONAL_FIELD_8 . "\n";
                            $message .= "optional field 9: " . $show->OPTIONAL_FIELD_9 . "\n";
                            $message .= "optional field 10: " . $show->OPTIONAL_FIELD_10 . "\n";

                            $from = $this->db->get_site_setting('registration_admin_email');
                            $ip = $_SERVER['REMOTE_ADDR'];
                            $host = @gethostbyaddr($ip);
                            $message .= "\n" . $_SERVER["REMOTE_ADDR"] . " : " . $host;
                            geoEmail::sendMail($mailto, $subject, $message, $from, $from, 0, 'text/plain');
                        }

                        $this->registered_variables["username"] = $show->USERNAME;
                        $this->registered_variables["password"] = $show->PASSWORD;
                        $this->registered_variables["email"] = stripslashes(urldecode($show->EMAIL));
                        $this->registered_variables["company_name"] = stripslashes(urldecode($show->COMPANY_NAME));
                        $this->registered_variables["business_type"] = stripslashes(urldecode($show->BUSINESS_TYPE));
                        $this->registered_variables["firstname"] = stripslashes(urldecode($show->FIRSTNAME));
                        $this->registered_variables["lastname"] = stripslashes(urldecode($show->LASTNAME));
                        $this->registered_variables["address"] = stripslashes(urldecode($show->ADDRESS));
                        $this->registered_variables["address_2"] = stripslashes(urldecode($show->ADDRESS_2));
                        $this->registered_variables["zip"] = stripslashes(urldecode($show->ZIP));
                        $this->registered_variables["city"] = stripslashes(urldecode($show->CITY));
                        $this->registered_variables["state"] = stripslashes(urldecode($show->STATE));
                        $this->registered_variables["country"] = stripslashes(urldecode($show->COUNTRY));
                        $this->registered_variables["phone"] = stripslashes(urldecode($show->PHONE));
                        $this->registered_variables["phone_2"] = stripslashes(urldecode($show->PHONE_2));
                        $this->registered_variables["fax"] = stripslashes(urldecode($show->FAX));
                        $this->registered_variables["url"] = stripslashes(urldecode($show->URL));
                        $this->registered_variables["optional_field_1"] = stripslashes(urldecode($show->OPTIONAL_FIELD_1));
                        $this->registered_variables["optional_field_2"] = stripslashes(urldecode($show->OPTIONAL_FIELD_2));
                        $this->registered_variables["optional_field_3"] = stripslashes(urldecode($show->OPTIONAL_FIELD_3));
                        $this->registered_variables["optional_field_4"] = stripslashes(urldecode($show->OPTIONAL_FIELD_4));
                        $this->registered_variables["optional_field_5"] = stripslashes(urldecode($show->OPTIONAL_FIELD_5));
                        $this->registered_variables["optional_field_6"] = stripslashes(urldecode($show->OPTIONAL_FIELD_6));
                        $this->registered_variables["optional_field_7"] = stripslashes(urldecode($show->OPTIONAL_FIELD_7));
                        $this->registered_variables["optional_field_8"] = stripslashes(urldecode($show->OPTIONAL_FIELD_8));
                        $this->registered_variables["optional_field_9"] = stripslashes(urldecode($show->OPTIONAL_FIELD_9));
                        $this->registered_variables["optional_field_10"] = stripslashes(urldecode($show->OPTIONAL_FIELD_10));
                        $this->registered_variables["registration_code"] = stripslashes(urldecode($show->REGISTRATION_CODE));
                        if ($this->debug_register) {
                            foreach ($this->registered_variables as $key => $value) {
                                echo $key . " - " . $value . "<br />\n";
                            }
                        }

                        $this->registered_variables['user_id'] = $this->user_id;
                        geoAddon::triggerUpdate('user_register', $this->registered_variables);

                        //if any addons are adding registration variables, let them know we're finishing the confirmation step
                        $addonVars = array('user_id' => $this->user_id, 'confirmation_step' => 2, 'registration_variables' => $this->registered_variables, 'confirmation_id' => $hash);

                        //I know it's silly to have 2 addon calls in a row.  Oh well, slap a sticker on it and call it a feature!
                        geoAddon::triggerUpdate('registration_add_field_update', $addonVars);



                        return true;
                    }
                }
            }
        } else {
            $this->error['confirm'] = "error";
            return false;
        }
    } //end of function confirm

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function confirmation_instructions()
    {
        $this->page_id = 17;
        $this->get_text();
        geoView::getInstance()->setBodyTpl('confirmation_instructions.tpl', '', 'registration');
        $this->display_page();
        return true;
    } //end of function confirm

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function registration_confirmation_success()
    {
        //special case: AFTER *all* the other addon stuff is done and everything is saved to DB, check with Subscription Pricing to see if we need to forward this user to its buy process
        $subby = geoAddon::getUtil('subscription_pricing');
        if ($subby) {
            $subby->tryForceSubscriptionBuy();
        }
        //registration confirmation was successful
        $this->page_id = 18;
        $this->get_text();
        geoView::getInstance()->setBodyTpl('confirmation_success.tpl', '', 'registration');
        $this->display_page();
        return true;
    } //end of function registration_confirmation_success()

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function confirmation_error()
    {
        // confirmation was unsuccessfull
        //display the error message
        $this->page_id = 18;
        $this->get_text();
        $msgs = DataAccess::getInstance()->get_text(true, $this->page_id);

        if ($this->error['disabled'] == 1) {
            $error = $this->messages[500884];
        } elseif ($this->error['confirm'] == "error") {
            $error = $this->messages[326];
        } elseif ($this->error['confirm'] == "error1") {
            $error = $this->messages[323];
        } else {
            $error = $this->messages[324];
        }
        geoView::getInstance()->setBodyTpl('confirmation_error.tpl', '', 'registration')
            ->setBodyVar('error_msg', $error);
        $this->display_page();
    } //end of function confirmation_error()

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function expire_confirmations()
    {
        //expire the confirmation in the database that exceed the admin limit in days
        $time = geoUtil::time();
        //$expire_time = $time - (86400 * $this->confirm_expiration_in_days);
        $expire_time = $time - (86400 * 5); //5 days before expiring

        $sql = "select * from " . $this->confirm_email_table . " where date < " . $expire_time;
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->error['confirm'] = "error";
            return false;
        } elseif ($result->RecordCount() > 0) {
            //get all the ids to delete from the confirm table
            while ($show = $result->FetchNExtObject()) {
                $sql = "delete from " . $this->confirm_table . " where id = " . $show->ID;
                if ($this->debug_register) {
                    echo 'DELETED CONFIRM ON LINE ' . __LINE__ . '<br/>';
                }
                $delete_result = $this->db->Execute($sql);
                if (!$delete_result) {
                    $this->error['confirm'] = "error";
                    return false;
                }

                $sql = "delete from " . $this->confirm_email_table . " where id = " . $show->ID;
                $delete_result = $this->db->Execute($sql);
                if (!$delete_result) {
                    $this->error['confirm'] = "error";
                    return false;
                }
            }
        }
        return true;
    } //end of function expire_confirmations()

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function end_registration()
    {
        $this->page_id = 15;
        $this->get_text();

        $tpl_vars = array (
            'registration_url' => $this->db->get_site_setting('registration_url'),
            'alreadyRegistered',$this->already_registered
        );

        geoView::getInstance()->setBodyTpl('end_registration.tpl', '', 'registration')
            ->setBodyVar($tpl_vars);
        $this->remove_registration_session();
        $this->display_page();
        return true;
    } //end of function end_registration

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function check_groups_for_registration_code_use()
    {
        $sql = "select * from " . $this->groups_table . " where registration_code != \"\"";
        $registration_check_result = $this->db->Execute($sql);
        //echo $sql."<br />\n";
        if (!$registration_check_result) {
            return false;
        } elseif ($registration_check_result->RecordCount() > 0) {
            $this->update_registration_code_use(1);
            return true;
        } else {
            $this->update_registration_code_use(0);
            $this->update_registration_code_checked(1);
            $this->set_default_group();
            return true;
        }
    } //end of check_groups_for_registration_code_use

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function registration_code_form()
    {
        if (!(geoPC::is_ent() || geoPC::is_premier())) {
            return true;
        }
        //make sure the js needed to auto-submit the loading thingy is cached
        $view = geoView::getInstance();
        $view->addTop(
            "
<script type=\"text/javascript\">
	//<![CDATA[
	//2 seconds after page is done loading, auto submit the form.
	gjUtil.autoSubmitForm ('validate_register', '?b=2&back=no');
	//]]>
</script>
"
        );
        $this->page_id = 19;
        //echo $this->page_id." is the page id<br />\n";
        $this->get_text();
        $session_reset = false;
        //check to see if it appears the session has been reset..
        if ((isset($_POST['c']) && !isset($_POST['c']['registration_code']))) {
            //registration info was submitted, but we are at the registration code
            //step, so the session must have been reset.
            if (!isset($this->error['cookie']) || !$this->error['cookie']) {
                $this->error['cookie'] = $this->messages[500159];
                $session_reset = true;
            }
        }
        if ($this->error['cookie']) {
            $this->body .= urldecode($this->error['cookie']);
            $session = true;
            include(GEO_BASE_DIR . 'get_common_vars.php');
            $status = $session->getStatus();
            if (!$session_reset && $status == 'changed') {
                //only display the error message, nothing else.
                $this->display_page();
                return true;
            }
        }

        $registration_url = ($this->db->get_site_setting('use_ssl_in_registration')) ? $this->db->get_site_setting('registration_ssl_url') : $this->db->get_site_setting('registration_url');

        $tpl = new geoTemplate('system', 'registration');
        $tpl->assign('msgs', $this->messages);
        $tpl->assign('registration_url', $registration_url);
        $tpl->assign('addons_top', geoAddon::triggerDisplay('display_registration_code_form_top', null, geoAddon::RETURN_STRING));

        if ($this->error["registration_code"]) {
            $tpl->assign('error_msg', $this->messages[234]);
        }
        if (isset($this->bad_registration_code) && $this->bad_registration_code) {
            $tpl->assign('badCode', $this->bad_registration_code);
        }
        $this->body .= $tpl->fetch('registration_code_form.tpl');

        $this->display_page();
        return true;
    } //end of function registration_code_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function check_registration_code($registration_code = 0, $api = 0)
    {
        if (!$registration_code) {
            return 0;
        }

        if (!$api && !isset($_GET['registration_code'])) {
            //this is not an api call, and the registration code wasn't sent through
            //the URL
            //make sure the session is all good
            $session = true;
            include(GEO_BASE_DIR . 'get_common_vars.php');

            $passedSessionId = (isset($info['sessionId'])) ? $info['sessionId'] : false;
            $sessionId = $session->getSessionId();
            $this->page_id = 19;
            $cookie_status = $session->getStatus();
            if ($cookie_status != 'confirmed') {
                //something is wrong with cookie??
                $this->get_text();

                if ($cookie_status == 'new') {
                    $this->error['cookie'] = $this->messages[500155]; //seems to be no cookies
                } else {
                    //must be that cookie could not be updated...
                    $this->error['cookie'] = $this->messages[500156]; //error updating message
                }
                $this->error_found ++;
                //save the entered registration code
                $this->bad_registration_code = $registration_code;
                return false;
            }
        }

        $sql = "SELECT `group_id` FROM " . $this->db->geoTables->groups_table . " WHERE `registration_code` = ?";
        $code_result = $this->db->Execute($sql, array($registration_code));
        if ($this->debug_register) {
            echo $sql . "<br />\n";
        }
        if (!$code_result) {
            $this->error['confirm'] = "error";
            return false;
        } elseif ($code_result->RecordCount() == 1) {
            if ($this->debug_register) {
                echo "registration code is good<br />\n";
            }
            $show = $code_result->FetchNextObject();
            $this->registration_code = $registration_code;
            $this->update_registration_code($registration_code);
            $this->update_registration_group($show->GROUP_ID);
            $this->update_registration_code_checked(1);
            return true;
        } else {
            $this->error["registration_code"] = 1;
            $this->bad_registration_code = $registration_code;
            return false;
        }
    } //end of function check_registration_code

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function update_registration_code($registration_code)
    {
        $this->registration_code = $registration_code;
        $this->registered_variables["registration_code"] = $registration_code;
        $sql = "update " . $this->registration_table . " set
			registration_code = \"" . $registration_code . "\"
			where session=\"" . $this->session_id . "\"";
        $registration_code_checked_result = $this->db->Execute($sql);
        if ($this->register_debug) {
            echo $sql . "<br />\n";
        }
        if (!$registration_code_checked_result) {
            return false;
        }
        return true;
    } //end of function update_registration_code

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function update_registration_code_checked($registration_code_checked, $api = false)
    {
        $this->registration_code_checked = $registration_code_checked;
        if (!$api) {
            $sql = "update " . $this->registration_table . " set
			registration_code_checked = " . $registration_code_checked . "
			where session=\"" . $this->session_id . "\"";
            $registration_code_checked_result = $this->db->Execute($sql);
            //echo $sql."<br />\n";
            if (!$registration_code_checked_result) {
                return false;
            }
        }
        return true;
    } //end of function update_registration_code_checked

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function update_registration_code_use($registration_code_use)
    {
        $this->registration_code_use = $registration_code_use;
        $sql = "update " . $this->registration_table . " set
			registration_code_use = " . $registration_code_use . "
			where session=\"" . $this->session_id . "\"";
        $registration_code_use_result = $this->db->Execute($sql);
        if (!$registration_code_use_result) {
            return false;
        }
        return true;
    } //end of function update_registration_code_use

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function update_personal_info_check($personal_info_check)
    {
        if ($this->debug_register) {
            echo "<br />TOP OF UPDATE_PERSONAL_INFO_CHECK<br />\n";
        }
        $this->personal_info_check = $personal_info_check;
        $sql = "update " . $this->registration_table . " set
			personal_info_check = " . $personal_info_check . "
			where session=\"" . $this->session_id . "\"";
        $personal_info_check_result = $this->db->Execute($sql);
        if ($this->debug_register) {
            echo $sql . "<br />\n";
        }
        if (!$personal_info_check_result) {
            return false;
        }
        return true;
    } //end of function update_personal_info_check

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function update_registration_group($registration_group, $api = 0)
    {
        $this->registration_group = $registration_group;
        if (!$api) {
            $sql = "update " . $this->registration_table . " set
			registration_group = " . $registration_group . "
			where session=\"" . $this->session_id . "\"";
            $registration_group_result = $this->db->Execute($sql);
            //echo $sql."<br />\n";
            if (!$registration_group_result) {
                return false;
            }
        }
        return true;
    } //end of function update_registration_code

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function set_default_group($api = false, $default_type = 1)
    {
        //sanity check
        if (!in_array($default_type, array(1,2,3))) {
            $default_type = 1;
        }
        $db = DataAccess::getInstance();
        $default_group = $db->GetOne("SELECT `group_id` FROM " . $this->db->geoTables->groups_table . " WHERE `default_group` = ?", array($default_type));
        if (!$default_group) {
            //no default for this type -- use the main one
            $default_group = $db->GetOne("SELECT `group_id` FROM " . $this->db->geoTables->groups_table . " WHERE `default_group` = ?", array(1));
        }
        $this->update_registration_group($default_group, $api);
        $this->update_registration_code_checked(1, $api);
    } //end of function set_default_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function group_splash_page()
    {
        if (!$this->registration_group || !geoPC::is_ent()) {
            return false;
        }
        $registration_url = ($this->db->get_site_setting('use_ssl_in_registration')) ? $this->db->get_site_setting('registration_ssl_url') : $this->db->get_site_setting('registration_url');
        $sql = "select registration_splash_code from " . $this->groups_table . " where group_id = " . $this->registration_group;
        $splash_page = $this->db->GetRow($sql);
        if (isset($splash_page['registration_splash_code']) && strlen(trim($splash_page['registration_splash_code'])) > 0) {
            $this->page_id = 19;
            $this->get_text();

            $tpl_vars = array();
            $tpl_vars['registration_splash_code'] = geoString::fromDB($splash_page['registration_splash_code']);
            $tpl_vars['registration_url'] = $registration_url;

            geoView::getInstance()->setBodyTpl('splash.tpl', '', 'registration')
                ->setBodyVar($tpl_vars);
            $this->display_page();
            include GEO_BASE_DIR . 'app_bottom.php';
            exit;
        }
        return true;
    } // end of function group_splash_page

//########################################################################

    public function set_new_user_id_in_current_session()
    {
        trigger_error("DEBUG REGISTER: TOP OF SET_NEW_USER_ID_IN_CURRENT_SESSION");
        if (isset($_COOKIE['classified_session']) && $this->session_id == 0) {
            //set the session id.
            $this->session_id = $_COOKIE['classified_session'];
        }
        if ($this->user_id) {
            $this->userid = $this->user_id;
            $sql = "select * from geodesic_sessions where classified_session = ?";
            $check_session_result = $this->db->Execute($sql, array($this->session_id));
            trigger_error('DEBUG REGISTER SQL: ' . $sql . " is the query, $this->session_id is the session id.");
            if (!$check_session_result) {
                //$this->body .=  $sql." is the query<br />\n";
                trigger_error('ERROR REGISTER SQL: Error: Query: ' . $sql . ' ERROR MESSAGE: ' . $this->db->ErrorMsg());

                $this->auth_messages["login"] = $this->messages[132];
                return false;
            } elseif ($check_session_result->RecordCount() == 1) {
                $sql = "update geodesic_sessions set
					user_id = " . $this->user_id . "
					where classified_session = \"" . $this->session_id . "\"";
                $session_result = $this->db->Execute($sql);
                trigger_error('DEBUG REGISTER SQL: ' . $sql . " is the query");
                if (!$session_result) {
                    trigger_error('ERROR REGISTER SQL: Query: ' . $sql . ' ERROR MESSAGE: ' . $this->db->ErrorMsg());
                    $this->auth_messages["login"] = $this->messages[132];
                    trigger_error('DEBUG REGISTER Query failed.  BOTTOM OF SET_NEW_USER_ID_IN_CURRENT_SESSION');
                    return false;
                }
                trigger_error('DEBUG REGISTER: Session correctly set.  BOTTOM OF SET_NEW_USER_ID_IN_CURRENT_SESSION');

                //log ip and time
                $sql = "UPDATE " . geoTables::userdata_table . " SET `last_login_time` = NOW(), `last_login_ip` = ? WHERE `id`=?";
                $this->db->Execute($sql, array(getenv('REMOTE_ADDR'),$this->user_id));

                //now get session to re-init itself
                $session = geoSession::getInstance();
                $session->initSession(true);
                //specify password as "null" if password not known
                $pass = (isset($this->registered_variables["password"]) && strlen($this->registered_variables["password"])) ? $this->registered_variables["password"] : null;
                geoAddon::triggerUpdate('session_login', array('userid' => $this->userid, 'username' => $session->getUserName(),  'password' => $pass));

                return true;
            } else {
                //session does not exist yet
                trigger_error('DEBUG REGISTER: Session does not exist yet. BOTTOM OF SET_NEW_USER_ID_IN_CURRENT_SESSION');
                return true;
            }
        } else {
            trigger_error('DEBUG REGISTER: Session does not exist yet. BOTTOM OF SET_NEW_USER_ID_IN_CURRENT_SESSION');
            return false;
        }
    } //end of function set_new_user_id_in_current_session

//########################################################################

    public function get_registration_configuration_data()
    {
        $sql = "SELECT * FROM " . $this->registration_configuration_table;
        //echo $sql." is the query<br />\n";
        $result = $this->db->Execute($sql);
        if (!$result) {
            trigger_error('ERROR SQL: Query:' . $sql . ' Error Message:' . $this->db->ErrorMsg());
            return false;
        } else {
            $this->registration_configuration = $result->FetchNextObject();
        }
        return true;
    } //end of function get_registration_configuration_data

//########################################################################

    public function add_initial_site_balance($price_plan = 0)
    {
        if ($this->debug_register) {
            echo "<br />TOP OF ADD_INITIAL_SITE_BALANCE<br />\n";
        }
        if (!$price_plan || $this->initial_account_balance_given) {
            return false;
        }

        $this->initial_account_balance_given = 1;
        $sql = "update " . $this->userdata_table . " set account_balance = " . $price_plan->INITIAL_SITE_BALANCE . " where id = " . $this->user_id;
        $result = $this->db->Execute($sql);
        if ($this->debug_register) {
            echo $sql . "<br />\n";
        }
        if (!$result) {
            if ($this->debug_register) {
                echo $this->db->ErrorMsg() . "<br />\n";
                echo $sql . "<br />\n";
            }
        }
        return true;
    } //end of function add_initial_site_balance

//########################################################################

    public function uniqueTimeStamp()
    {
        $milliseconds = microtime();
        $timestring = explode(" ", $milliseconds);
        $sg = $timestring[1];
        $mlsg = substr($timestring[0], 2, 4);
        $timestamp = $sg . $mlsg;
        return $timestamp;
    }

    /**
     * Gets the salutation for the person.  Use geoUser::getSalutation
     * anywhere outside of registration class.
     *
     * @param Mixed $person Either object, or array containing user's data.
     * @return String The salutation for the person.
     * @deprecated
     */
    public function get_salutation($person)
    {
        if (is_numeric($person)) {
            //this might be a user id..
            $person = $this->get_user_data(intval($person));
        }

        if (is_object($person)) {
            //Use object notation.
            $person = array (
                'username' => $person->USERNAME,
                'firstname' => $person->FIRSTNAME,
                'lastname' => $person->LASTNAME,
                'email' => $person->EMAIL,
            );
        }
        switch ($this->db->get_site_setting('email_salutation_type')) {
            case 2:
                //display firstname
                return $person['firstname'];
                break;

            case 3:
                //display firstname and lastname
                return $person['firstname'] . " " . $seller['lastname'];
                break;

            case 4:
                //display lastname and firstname
                return $seller['lastname'] . " " . $person['firstname'];
                break;

            case 5:
                //display email address
                return $person['email'];
                break;

            case 6:
                // firstname lastname (username)
                return "{$person['firstname']} {$person['lastname']} ({$person['username']})";
                break;

            case 1:
                //break ommited on purpose

            default:
                //display username
                return $person['username'];
                break;
        }
    }

    function checkUserAttachmentCookie()
    {
        //check first to see is shared fees is active
        $share_fees = geoAddon::getUtil('share_fees');
        if (($share_fees) && ($share_fees->active) && ($this->userAttachmentSet == 0)) {
            //check to see if anything passed in the query string
            if (isset($_REQUEST["sharewith"])) {
                //check the sharewith variable passed within the query string.
                //a username will be passed so get the id to attach to
                $user_to_attach_to = $share_fees->getIdByUsername(urldecode($_REQUEST['sharewith']));
                if ($user_to_attach_to) {
                    if (!$share_fees->checkUserToAttachmentRegistration($this->registration_group, $user_to_attach_to)) {
                        $share_fee_text =& geoAddon::getText('geo_addons', 'share_fees');
                        $this->error_found++;
                        $this->error['feeshareattachment'] = $share_fee_text['share_fees_registration_attachment_error'];
                        //do not userAttachmentSet as what passed in query string does not exist or cannot be attached to
                    } else {
                        //can attach to this user.  Set within this registration session
                        //set within form
                        $this->userAttachmentSet = $user_to_attach_to;
                    }
                } else {
                    //user does not exist in the system
                }
            } else {
                //there is no attachment to attach this registration to
            }
        } else {
            //user attachment type 1 is inactive or already set within class
        }
    }
} //end of class Register
