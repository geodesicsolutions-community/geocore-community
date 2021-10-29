<?php
//users/list.php
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

//Gets a list of users, with data requested

$sql = "SELECT `id`, `username`, `email` FROM ".geoTables::userdata_table." WHERE `id`!=1";

if (isset($args['limit'])) {
	//allow to specify certain range
	$start = (int)$args['start'];
	$limit = (int)$args['limit'];
	$sql .= " LIMIT $start, $limit";
}

return $this->db->GetAssoc($sql);
