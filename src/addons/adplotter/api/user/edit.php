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
## ##    7.5.3-36-gea36ae7
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

if (!$this->product_configuration->verify_credentials($args['username'], $args['password'])) {
    //user or pass does not match.
    return $this->failure('Invalid user credentials', -1, 5);
}

$id = $this->db->GetOne('SELECT `id` FROM ' . geoTables::logins_table . ' WHERE `username` = ?', array($args['username']));

$status = ''; //track each changed element for reporting (esp. during partial failures)

if ($args['new_username'] && strlen($args['new_username'])) {
    $username = $args['new_username'];
    $result = $this->db->Execute('UPDATE ' . geoTables::logins_table . ' SET `username` = ? WHERE `id` = ?', array($username,$id));
    if (!$result) {
        return $this->failure('Failed to update username', 1);
    }
    $result = $this->db->Execute('UPDATE ' . geoTables::userdata_table . ' SET `username` = ? WHERE `id` = ?', array($username,$id));
    if (!$result) {
        return $this->failure('Failed to update username', 2);
    }
    $status .= "Username changed successfully. ";
} else {
    //not changing the username, so use the old one for computing pass hash
    $username = $args['username'];
}

if ($args['new_password'] && strlen($args['new_password'])) {
    $hash_type = $this->db->get_site_setting('client_pass_hash');
    $salt = '';
    $hashed_password = $this->product_configuration->get_hashed_password($username, $password, $hash_type);
    if (!$hashed_password) {
        return $this->failure($status . 'Failed to hash password.', 3);
    }
    if (is_array($hashed_password)) {
        $salt = '' . $hashed_password['salt'];
        $hashed_password = '' . $hashed_password['password'];
    }
    $sql = "UPDATE " . geoTables::logins_table . " SET	`password` = ?, `hash_type`=?, `salt`=?	WHERE `id` = ?";
    $result = $this->db->Execute($sql, array($hashed_password, $hash_type, $salt, $this->userid));
    if (!$result) {
        return $this->failure($status . 'Failed to update password.', 4);
    }
    $status .= "Password changed successfully. ";
}

if ($args['new_email'] && strlen($args['new_email'])) {
    $email = $args['new_email'];
    if (!geoString::isEmail($email)) {
        return $this->failure($status . 'Email NOT changed -- invalid format', 5);
    }
    if (!$this->db->Execute("UPDATE " . geoTables::userdata_table . " SET `email` = ? WHERE `id` = ?", array($email, $id))) {
        return $this->failure($status . 'Failed to update email', 6);
    }
    $status = "Email changed successfully. ";
}
return array('success' => 1, 'message' => $status);
