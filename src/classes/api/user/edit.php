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
## ##    6.0.7-2-gc953682
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}
require_once(CLASSES_DIR . 'user_management_information.php');

//generic error to give in any situation where user/pass/token is not working.
$generic_error = 'Username or old password or user token was incorrect or was not specified, or user is not active, request to edit user info has failed.';

if (!(isset($args['old_username']) || isset($args['username'])) || !(isset($args['old_password']) || isset($args['token']))) {
    //give same generic error whether it's not sent, username not found, or password doesn't match.
    //Error needs to be the same for security reasons, to make it harder to guess user/pass combinations.
    return $this->failure(__line__ . $generic_error);
}

$username = (isset($args['old_username'])) ? $args['old_username'] : $args['username'];

if (strlen(trim($username)) == 0) {
    return $this->failure($generic_error);
}

//check the user/password/token first...
if (isset($args['old_password'])) {
    //verify using user/pass
    if (!$this->product_configuration->verify_credentials($username, $args['old_password'])) {
        //user or pass does not match.
        return $this->failure($generic_error);
    }
} else {
    //verify using token
    $token = (isset($args['token'])) ? $args['token'] : '';
    if (!$this->checkUserToken($username, $token)) {
        //token is not valid
        return $this->failure($generic_error);
    }
    //made it this far, the token checks out.
}


//old user/pass or token matches at this point.
$sql = 'SELECT `id`, `status`, `password` from `geodesic_logins` where `username` = ?';
$result = $this->db->Execute($sql, array($username));
if (!$result) {
    //doh!
    return $this->failure($generic_error);
}
if ($result->RecordCount() != 1) {
    return $this->failure($generic_error);
}
$row = $result->FetchRow();

$args['id'] = $row['id'];
$args['status'] = $row['status'];
$args['db_password'] = $row['password'];

if (isset($args['password']) && strlen(trim($args['password'])) > 0) {
    $args['password_verify'] = $args['password'];
}

if (!isset($args['id']) || $args['id'] <= 1 || !$args['status']) {
    //something went wrong, or user is inactive, or this is admin user.  either way deny user edit.
    return $this->failure($generic_error);
}

//ensure that if any data is not specified, it defaults to what it was before.
$sql = 'SELECT * FROM `geodesic_userdata` WHERE `id`=? LIMIT 1';
$result = $this->db->Execute($sql, array($args['id']));
if (!$result || $result->RecordCount() == 0) {
    //doh!
    return $this->failure($generic_error);
}
$old_data = $result->FetchRow();
$expose_vals = array(
    'expose_email' => $old_data['expose_email'],
    'expose_company_name' => $old_data['expose_company_name'],
    'expose_firstname' => $old_data['expose_firstname'],
    'expose_lastname' => $old_data['expose_lastname'],
    'expose_address' => $old_data['expose_address'],
    'expose_city' => $old_data['expose_city'],
    'expose_state' => $old_data['expose_state'],
    'expose_country' => $old_data['expose_country'],
    'expose_zip' => $old_data['expose_zip'],
    'expose_phone' => $old_data['expose_phone'],
    'expose_phone2' => $old_data['expose_phone2'],
    'expose_fax' => $old_data['expose_fax'],
    'expose_url' => $old_data['expose_url'],
    'expose_optional_1' => $old_data['expose_optional_1'],
    'expose_optional_2' => $old_data['expose_optional_2'],
    'expose_optional_3' => $old_data['expose_optional_3'],
    'expose_optional_4' => $old_data['expose_optional_4'],
    'expose_optional_5' => $old_data['expose_optional_5'],
    'expose_optional_6' => $old_data['expose_optional_6'],
    'expose_optional_7' => $old_data['expose_optional_7'],
    'expose_optional_8' => $old_data['expose_optional_8'],
    'expose_optional_9' => $old_data['expose_optional_9'],
    'expose_optional_10' => $old_data['expose_optional_10']
);
foreach ($old_data as $col => $info) {
    if (!isset($args[$col])) {
        //not set, so set it to existing data.
        $args[$col] = $info;
        if (array_key_exists($col, $expose_vals)) {
            $expose_vals[$col] = ($info) ? 1 : 0;
        }
    }
}

$user_mgmt_class = new User_management_information();
$user_mgmt_class->classified_user_id = $user_mgmt_class->userid = $args['id'];

$check = $user_mgmt_class->check_info(0, $args);

if ($user_mgmt->error_found > 0) {
    return $this->failure('User data check failed, make sure all required fields are provided, and have valid data.', 1001);
}
$skip_addon_call = (isset($args['skip_addon_call']) && $args['skip_addon_call']) ? true : false;

$result = $user_mgmt_class->update_user(0, $args, $expose_vals, $skip_addon_call);

if (!$result) {
    return $this->failure('Error updating user data.', 1002);
}
return 'Update user info was a success.';
