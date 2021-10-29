<?php

//edit.php
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
## ##    7.5.3-140-g8c431f6
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}
require_once(CLASSES_DIR . 'register_class.php');

$generic_error = 'Registration failed.';


$register = Singleton::getInstance('Register');

$register->language_id = $language_id;

//First, handle registration code, if set
if (isset($args['registration_code']) && strlen(trim($args['registration_code'])) > 0) {
    $registration_code = $args['registration_code'];
    if (!$register->check_registration_code($registration_code, true)) {
        //error returned.
        return $this->failure('Registration code not valid.');
    }
    //returned true, registration code must be good
} elseif (isset($args['force_user_group_id']) && (int)$args['force_user_group_id']) {
    $force_user_group_id = (int)$args['force_user_group_id'];
    //make sure it is valid
    $count = $this->db->GetOne("SELECT count(*) FROM " . geoTables::groups_table . " WHERE `group_id`=?", array($force_user_group_id));
    if ($count == 1) {
        //valid group, use it
        $register->update_registration_group($force_user_group_id, true);
    } else {
        //not valid group, use default
        $register->set_default_group(true);
    }
} elseif (geoAddon::getInstance()->isEnabled('adplotter')) {
    //see if a default adplotter group exists
    $reg = geoAddon::getRegistry('adplotter');
    $group = $reg->default_group;
    if ($group && $group > 1) {
        //use selected group
        $register->update_registration_group($group, true);
    } else {
        //use software default
        $register->set_default_group(true);
    }
} else {
    $register->set_default_group(true);
}

//TODO:  Validate filters, if specified.
// Filters not currently possible with API, may be added in future if there is a need

if (!isset($args['password_confirm'])) {
    $args['password_confirm'] = $args['password'];
}
if (!isset($args['email_verifier'])) {
    $args['email_verifier'] = $args['email'];
}
if (isset($args['zipcode']) && !empty($args['zipcode'])) {
    $args['zip'] = $args['zipcode'];
}

$register->check_info($args, true);

if ($register->error_found > 0) {
    //errors when registering!
    $msgs = "Error when registering new user({$register->api_error}): ";
    if (isset($register->error['username']) && $register->error['username'] == 'error1') {
        return $this->failure($msgs . 'Username not valid.  Check username string length, and that there are no illegal charecters in the username.', 1000, 5);
    }
    if (isset($register->error['username']) && $register->error['username'] == 'error2') {
        return $this->failure($msgs . 'Duplicate username.', 1001, 5);
    }
    if ($register->error2['password'] || $register->error3['password']) {
        return $this->failure('Password not valid.', 1002, 5);
    }

    if ($register->api_error) {
        return $this->failure("Error registering new user.  Debug: <pre>" . print_r($msgs, 1) . "</pre>", 1004, 5);
    }
    //Not a common error.
    return $this->failure("Error registering new user.  Debug: {$msgs}<pre>" . print_r($register, 1) . "</pre>", 1004, 5);
}
//no errors found, so insert the new user!
$skip_addon = (isset($args['skip_addon_call'])) ? $args['skip_addon_call'] : false;
$register->insert_user(1, $skip_addon);

if ($register->error) {
    $msg = (is_array($register->error)) ? implode(' ', $register->error) : $register->error;
    return $this->failure("Error registering new user.  Debug: {$msg}", 1004, 5);
}

//Gets this far, registration was good.
$return = array('success' => 1);
$return['user_id'] = $register->user_id;

if ($args['success_body_html']) {
    //return the body that would be in {body_html}
    $this->db->get_text(false, 18);
    $tpl = new geoTemplate(geoTemplate::SYSTEM, 'registration');
    $return['body_html'] = $tpl->fetch('confirmation_success.tpl');
}
if ($args['success_full_page']) {
    //wants the full page
    $register->page_id = 18;
    $register->get_text();

    geoView::getInstance()->setBodyTpl('confirmation_success.tpl', '', 'registration');
    ob_start();
    $register->display_page();
    $return['full_page'] = ob_get_clean();
}


return $return;
