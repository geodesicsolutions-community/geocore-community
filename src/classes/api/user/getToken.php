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


if (!defined('IN_GEO_API')){
	exit('No access.');
}

$generic_error = 'Username or Password not correct.';

if (!isset($args['username']) || (!isset($args['password']) && !isset($args['password_bypass']))){
	//user or pass not sent?
	return $this->failure($generic_error.__line__,1000,5);
}
if (!isset($args['password_bypass'])) $args['password_bypass'] = false;

if (strlen(trim($args['username'])) == 0 || (strlen(trim($args['password'])) == 0 && !$args['password_bypass'])){
	//string length 0
	return $this->failure($generic_error.__line__,1000,5);
}

if (!$args['password_bypass'] && !$this->product_configuration->verify_credentials($args['username'], $args['password'])){
	return $this->failure($generic_error.__line__,1000,5);
}

//user and pass check out, get token
$sql = 'SELECT `api_token` FROM `geodesic_logins` WHERE `username` = ? AND `id` != 1 LIMIT 1';
$result = $this->db->Execute($sql, array(trim($args['username'])));
if (!$result || $result->RecordCount() != 1){
	return $this->failure('Error getting token for use, DB error.',1000,5);
}
$row = $result->FetchRow();
$token = $row['api_key'];
if (strlen(trim($token)) == 0){
	$token = $this->resetUserToken($args['username']);
}
//return token
return $token;