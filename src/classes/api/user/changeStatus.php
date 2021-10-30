<?php

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//generic error to give in any situation where user/pass/token is not working.
$generic_error = 'Username or password or user token was incorrect or was not specified, request to change status has failed.';

if (!isset($args['username']) || !(isset($args['password']) || isset($args['token']))) {
    //give same generic error whether it's not sent, username not found, or password doesn't match.
    //Error needs to be the same for security reasons, to make it harder to guess user/pass combinations.
    return $this->failure(__line__ . $generic_error, 1000, 5);
}

$username = $args['username'];

if (strlen(trim($username)) == 0) {
    return $this->failure($generic_error, 1000, 5);
}

//check the user/password/token first...
if (isset($args['password'])) {
    //verify using user/pass
    if (!$this->product_configuration->verify_credentials($username, $args['password'])) {
        //user or pass does not match.
        return $this->failure($generic_error, 1000, 5);
    }
} else {
    //verify using token
    $token = (isset($args['token'])) ? $args['token'] : '';
    if (!$this->checkUserToken($username, $token)) {
        //token is not valid
        return $this->failure($generic_error, 1000, 5);
    }
    //made it this far, the token checks out.
}

if (!isset($args['status'])) {
    return $this->failure('New status (0 for suspended, 1 for active) not specified.', 1000, 5);
}
$status = ($args['status']) ? 1 : 0;

//set status for user
$sql = "UPDATE `geodesic_logins` SET `status`=? WHERE `username` = ? LIMIT 1";
$result = $this->db->Execute($sql, array($status, $username));
if (!$result) {
    //doh!
    return $this->failure('DB Error updating status.', 1000, 5);//.$this->db->ErrorMsg());
}

return 'Update user status was a success.';
