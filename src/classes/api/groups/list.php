<?php

//groups/list.php


if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//Gets a list of users, with data requested
$sql = "SELECT * FROM " . geoTables::groups_table;


return $this->db->GetAll($sql);
