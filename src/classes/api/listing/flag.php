<?php

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

//flags a listing as possibly inappropriate
//this is required by new Apple review guidelines for the mobile app, and will fail without the latest version of the Mobile API addon

//a more robust flagging system may be forthcoming -- this is very bare bones to meet apple's new guidelines quickly

$listingId = $args['listingId'];
if (!$listingId || !is_numeric($listingId)) {
    return $this->failure('Error: bad listing id');
}

//flag listing to addon db table
$db = DataAccess::getInstance();
$sql = "INSERT INTO `geodesic_addon_mobile_api_flags` (listing_id, time) VALUES (?,?)";
$result = $db->Execute($sql, array($listingId, geoUtil::time()));

return ($result) ? array('success' => 'ok') : $this->failure('Failed to flag listing (database error)');
