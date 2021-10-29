<?php

//edit.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//check to see if a specified user or e-mail exists already in the Geo software.

if (!isset($args['username']) && !isset($args['email'])) {
    //username or email not specified?
    return $this->failure('Error:  No username or email specified.  Please provide either a username or an e-mail address to check to see if it exists or not.', 1000, 5);
}

if (isset($args['username']) && strlen(trim($args['username'])) == 0) {
    //string length 0
    return $this->failure('Invalid username.');
} elseif (isset($args['email']) && strlen(trim($args['email'])) == 0) {
    return $this->failure('Invalid email.');
}

$field_name = (isset($args['username'])) ? 'username' : 'email';
$sql = 'SELECT `id` FROM `geodesic_userdata` WHERE `' . $field_name . '` = ? AND `id` != 1 LIMIT 1';
$result = $this->db->Execute($sql, array(trim($args[$field_name])));
if (!$result) {
    //db error:
    return $this->failure('DB Error!  Please try again.');
}

if ($result->RecordCount() == 1) {
    //user does exist
    return true;
}
//user does not exist.
return false;
