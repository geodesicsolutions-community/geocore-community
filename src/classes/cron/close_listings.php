<?php

//close_listings.php
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
## ##    7.5.1-4-g0257fb3
##
##################################

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$current_time = geoUtil::time();

//check for ads to take down for possible renewal
//"ends = 0" means a listing has unlimited duration, and should not be closed by this process
$sql = "SELECT `id` FROM " . geoTables::classifieds_table . " WHERE `ends` < $current_time AND `ends` != 0 AND `live` = 1 AND `delayed_start` = 0";
$this->log('Running: ' . $sql, __line__);
$rows = $this->db->GetAll($sql);
if ($rows === false) {
    $this->log('DB Error, sql: ' . $sql . " Error: " . $this->db->ErrorMsg(), __line__);
    return false;
}

//move expired ads to expired table
$this->log(count($rows) . " is the number of classifieds to close", __line__);
$cats_update = array();
foreach ($rows as $row) {
    $listing = geoListing::getListing($row['id']);
    if (!is_object($listing)) {
        $this->log('Listing not object, possibly id not valid.  Row results: ' . print_r($row, 1), __line__);
        continue;
    }
    if (geoAddon::triggerDisplay('cron_close_listings_skip_listing', array ('listing' => $listing), geoAddon::BOOL_TRUE)) {
        //addon says to skip closing this listing for now
        continue;
    }
    $this->log($listing->item_type . " is listing->item_type", __line__);
    $vars = array (
        'listing' => $listing
    );
    geoOrderItem::callUpdate('cron_close_listings', $vars);

    $cats_update[$listing->category] = $listing->category;

    if ($listing->item_type != 2) {
        $sql = "UPDATE " . geoTables::classifieds_table . "
			SET `live` = 0
			WHERE `id` = " . $listing->id;
        $update_result = $this->db->Execute($sql);
        $this->log($sql . "<br/>\n", __line__);
        if (!$update_result) {
            $this->log('DB Error, sql: ' . $sql . ' Error: ' . $this->db->ErrorMsg() . "<br/>\n", __line__);
            return false;
        }
    }

    //remove from all favorites
    $sql = "delete from " . $this->db->geoTables->favorites_table . "
		where classified_id = " . $listing->id;
    $delete_result = $this->db->Execute($sql);
    $this->log($sql . "<br/>\n", __line__);
    if (!$delete_result) {
        $this->log(__line__ . 'DB Error, sql: ' . $sql . ' Error: ' . $this->db->ErrorMsg() . "<br/>\n", __line__);
        return false;
    }
}

foreach ($cats_update as $cat_id) {
    //do it this way, to keep from updating the same catgory over and over, if a bunch of listings
    //from the same category are closing at once.
    geoCategory::updateListingCount($cat_id);
}

return true; //finished task without a hitch
