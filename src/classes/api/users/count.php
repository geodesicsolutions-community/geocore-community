<?php
//users/count.php
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

//Gets count of users

$query = "SELECT count(*) from ".geoTables::userdata_table;
//get number in userdata table, subtract 1 for admin user

//since there are 2 tables, make sure those 2 tables are synced up
$userdata = $this->db->GetOne($query)-1;


$query = "SELECT count(*) from ".geoTables::logins_table;

//get number in logins table, subtract 1 for admin user
$logins = $this->db->GetOne($query)-1;

if ($logins != $userdata) {
	//have to do it slow way, table counts don't match up
	$query = "SELECT count(*) from ".geoTables::logins_table." l, ".geoTables::userdata_table." ud WHERE l.id=ud.id AND l.id!=1";
	
	//already accounted for admin user in query so don't need to subtract 1 here
	$logins = $this->db->GetOne($query);
}

return (int)$logins;
