<?php

//register.php
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
## ##    7.3beta4-188-gdac6616
##
##################################
//use special app top, for the register.php page.
require_once('app_top.register.php');

if (strlen($register->debug_email) > 0) {
    $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
}
if ($user_id !== 0) {
    if (strlen($register->debug_email) > 0) {
        $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
    }
    //strip off 'register.php' from url then redirect
    $home_page = substr($_SERVER['PHP_SELF'], 0, -12);
    header("Location: " . $db->get_site_setting('classifieds_url'));
}

if ($db->get_site_setting('disable_registration')) {
    //registration currently disabled
    $register->error['disabled'] = 1;
    $register->confirmation_error();
} elseif (isset($_REQUEST['b']) && $_REQUEST["b"] == 3) {
    //the user has clicked the confirmation sent in the email sent to him
    //process the confirmation and put the user in the
    if ($debug_register) {
        echo "about to confirm this user<BR>\n";
    }
    if (strlen($register->debug_email) > 0) {
        $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
    }
    if ($register->confirm($_REQUEST["hash"], $_REQUEST["username"])) {
        if ($debug_register) {
            echo "user is confirmed<BR>\n";
        }
        //display the registration confirmation completion
        $register->set_new_user_id_in_current_session();
        if (strlen($register->debug_email) > 0) {
            $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
        }
        $register->registration_confirmation_success();
        if (strlen($register->debug_email) > 0) {
            $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
        }
        $register->remove_registration_session();
        exit;
    } else {
        if (strlen($register->debug_email) > 0) {
            $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
        }
        //display the error message from confirmation
        $register->confirmation_error();
    }
} elseif (isset($_REQUEST['b']) && $_REQUEST["b"] == 4) {
    if (strlen($register->debug_email) > 0) {
        $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
    }
    $register->end_registration();
} elseif (isset($_REQUEST['b']) && $_REQUEST["b"] == 5) {
    if (strlen($register->debug_email) > 0) {
        $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
    }
    //reset filter
    //***old "filter" functionality removed in 7.3.0***
    //this probably isn't needed anymore, but left pointing at the main registration form for now, just in case
    $register->registration_form_1($db);
} else {
    if (strlen($register->debug_email) > 0) {
        $register->sendMail($register->debug_email, 'DEBUG ' . substr($register->uniqueTimeStamp(), -6), 'LINE = ' . __LINE__, 'FILE = register.php');
    }
    //show the basic form to register
    $register->error_found = 0;
    if (!$register->registration_code_checked) {
        if ($register->registration_code_use && (geoPC::is_ent() || geoPC::is_premier())) {
            if (!isset($_POST['c']['sessionId']) && isset($_POST['c'])) {
                //show in between stage
                $register->validate_registration_code($_POST['c']);
            } elseif (isset($_REQUEST["registration_code"]) && $_REQUEST['registration_code']) {
                if ($register->check_registration_code($_REQUEST["registration_code"])) {
                    $register->group_splash_page();
                    $register->registration_form_1($db);
                } else {
                    $register->registration_code_form();
                }
            } elseif (isset($_REQUEST['c']['bypass_registration_code']) && strlen(trim($_REQUEST["c"]["bypass_registration_code"])) > 0) {
                $register->update_registration_code_checked(1);
                $register->set_default_group();
                $register->registration_form_1($db);
            } elseif (isset($_REQUEST["c"]['submit_registration_code']) && strlen(trim($_REQUEST["c"]['submit_registration_code'])) > 0) {
                if ($register->check_registration_code($_REQUEST["c"]["registration_code"])) {
                    //check for group splash page
                    $register->group_splash_page();
                    $register->registration_form_1($db);
                } else {
                    //display error messages
                    $register->registration_code_form();
                }
            } else {
                $register->registration_code_form();
            }
        } else {
            $register->update_registration_code_checked(1);
            $register->set_default_group();
            $register->registration_form_1($db);
        }
    } elseif (!$register->personal_info_check) {
        if (isset($_POST['c']) && $_POST["c"]) {
            if (!isset($_POST['c']['sessionId'])) {
                $register->validate_register_form($_POST['c']);
            } elseif ($register->check_info($_POST["c"])) {
                $register->update_personal_info_check(1);
                $register->insert_user();

                $register->set_new_user_id_in_current_session();
                if (
                    $db->get_site_setting('use_email_verification_at_registration') ||
                    $db->get_site_setting('admin_approves_all_registration')
                ) {
                    //do the confirmation
                    $register->confirmation_instructions();
                    $register->remove_registration_session();
                } else {
                    $register->registration_confirmation_success();
                    $register->remove_registration_session();
                }
            } else {
                $register->registration_form_1($db);
            }
        } else {
            $register->registration_form_1($db);
        }
    } else {
        $register->registration_form_1($db);
    }
}

require GEO_BASE_DIR . 'app_bottom.php';
