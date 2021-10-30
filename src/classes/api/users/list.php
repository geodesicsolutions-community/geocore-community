<?php

//users/list.php


if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//Gets a list of users, with data requested

$sql = "SELECT `id`, `username`, `email` FROM " . geoTables::userdata_table . " WHERE `id`!=1";

if (isset($args['limit'])) {
    //allow to specify certain range
    $start = (int)$args['start'];
    $limit = (int)$args['limit'];
    $sql .= " LIMIT $start, $limit";
}

return $this->db->GetAssoc($sql);
