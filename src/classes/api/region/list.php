<?php

//region/list.php


if (!defined('IN_GEO_API')) {
    exit('No access.');
}
//get a list of all regions starting from a given top-level region (the 'parent')

if (isset($args['parent']) && !is_numeric($args['parent'])) {
    return $this->failure('Error:  not a valid parent region');
}


$db = DataAccess::getInstance();
$parent = ($args['parent']) ? $args['parent'] : 0;

$sql = "SELECT * FROM " . geoTables::region . " AS r, " . geoTables::region_languages . " AS l WHERE r.parent = ? AND r.enabled='yes' AND r.id=l.id AND l.language_id = ?";
$result = $db->Execute($sql, array($parent, $db->getLanguage()));
if (!$result) {
    return $this->failure('Error: database error');
}

$regions = array();
foreach ($result as $region) {
    $regions[] = array(
        'name' => $region['name'],
        'id' => $region['id'],
        'billing_abbreviation' => $region['billing_abbreviation'],
        'subdomain' => $region['unique_name']
    );
}
return $regions;
