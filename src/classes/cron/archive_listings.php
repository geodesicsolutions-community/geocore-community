<?php

//archive_listings.php


if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$current_time = $this->time();
$util = archive_listings_util::getInstance();

$delay_time = $this->db->get_site_setting('archive_listing_delay');
if (!$delay_time) {
    //set default
    $delay_time = 2592000;
    $this->db->set_site_setting('archive_listing_delay', $delay_time);
}

$removal_date = intval($current_time - $delay_time);

//$removal_date is clean since intval is done on it above.
$sql = "select * from " . $this->db->geoTables->classifieds_table . " where ends < $removal_date and live = 0";
$this->log($sql, __line__);
$this->log("Delay time before moving closed listings to expired listing table: <strong>$delay_time seconds</strong>. (set in Admin -> Listing Setup -> General Settings -> Archive Listing )", __line__);

$select_result = $this->db->Execute($sql);
if (!$select_result) {
    $this->log("Error in " . $sql . "\n" . $this->db->ErrorMsg(), __line__);
    return false;
}

//move expired ads to expired table
while ($show = $select_result->FetchRow()) {
    //check to see if there is a renewal that is pending admin approval
    $sql = "SELECT count(oi.id) as count FROM " . geoTables::order_item . " as oi, " . geoTables::order . " as o,
			" . geoTables::order_item_registry . " as oir
			WHERE 
			oi.status = 'pending' AND oi.type = 'listing_renew_upgrade'
			AND o.id = oi.`order` AND (o.status IN ('active', 'pending_admin', 'pending') )
			AND u.id = o.buyer AND oir.order_item = oi.id
			AND oir.index_key = 'listing_id' AND oir.val_string = '{$show['id']}'";
    $row = $this->db->GetRow($sql);

    if (isset($row['count']) && $row['count'] > 0) {
        //there is a pending renewal/upgrade for this listing, so
        //don't archive.
        continue;
    }

        $this->log($show['duration'] . " is show[duration]", __line__);
    if ((strlen(trim($show['duration'])) == 0) || (is_null($show['duration']))) {
        $this->log("show[duration] is null or empty", __line__);
        $show['duration'] = 0;
    }
        $this->log($show['duration'] . " is show[duration] 2", __line__);

        $insert_expired_result = $util->insertInExpired($show);

    if (!$insert_expired_result) {
        //insertion failed...  method already logged a message, just finish
        //the cron early.
        return false;
    }

    if ($show["item_type"] == 2) {
        //get high bidder for auction
        if (isset($show['high_bidder'])) {
            //if the high bidder is already set, get the already defined high bidder.
            $high_bidder = $show['high_bidder'];
        } else {
            //otherwise get the high bidder from the auction table.
            $high_bidder = $util->get_high_bidder($show["id"]);
            $high_bidder = $high_bidder['bidder'];
        }
        $sql = "update " . $this->db->geoTables->classifieds_expired_table . " set
				high_bidder = ?
				where id = ?";
        $update_bidder_result = $this->db->Execute($sql, array($high_bidder, $show['id']));
        $this->log($sql, __line__);
        if (!$update_bidder_result) {
            //add high_bidder to expired table and try again
            $sql = "ALTER TABLE " . $this->db->geoTables->classifieds_expired_table . " ADD high_bidder INT NOT NULL";
            $alter_expired_result = $this->db->Execute($sql);
            $this->log($sql, __line__);
            $sql = "update " . $this->db->geoTables->classifieds_expired_table . " set
					high_bidder = ?
					where id = ?";
            $update_bidder_result = $this->db->Execute($sql, array($high_bidder["bidder"], $show["id"]));
            $this->log($sql, __line__);
        }
    }
        //let geoListing do rest of work for us, but let it know it is archived
        //so that feedback isn't also removed.
        $removeResult = geoListing::remove($show['id'], true);
    if (!$removeResult) {
        $this->log('Removal of listing failed in geoListing::remove() for listing ID ' . $show['id'], __line__);
        return false;
    }
} //end of while

//NOTE:  we return true AFTER the class declaration, otherwise on some servers
//the class is never parsed

class archive_listings_util extends geoCron
{
    var $category_tree_array;
    var $messages;
    var $db;
    var $verbose;
    private static $_instance;
    /**
     * Get an instance of the cron task.
     *
     * @return archive_listings_util
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            $c = __class__;
            self::$_instance = new $c();
        }
        return self::$_instance;
    }

    protected function __construct()
    {
        $cron = geoCron::getInstance();
        foreach ($cron as $setting => $val) {
            $this->$setting = $val;
        }
    }

    /**
     * Needed by archive_listings()
     *
     * @param unknown_type $category
     */
    function get_category_string($category)
    {
        $category_tree = geoCategory::getTree($category);

        if ($category_tree) {
            //category tree

            $category_string = "";
            if (is_array($category_tree)) {
                $i = 0;
                foreach ($category_tree as $cat) {
                    //display all the categories
                    $category_string .= $cat[$i]["category_name"];
                    if (++$i != count($category_tree)) {
                        $category_string .= " > ";
                    }
                }
            } else {
                $category_string .= 'Unknown';
            }
        }
        return $category_string;
    }


    /**
     * Needed by archive_listings()
     */
    function get_high_bidder($auction_id = 0)
    {
        $this->log('Top of get_high_bidder', __line__);
        $sql = "select * from " . $this->db->geoTables->bid_table . " where auction_id=" . $auction_id . " order by bid desc,time_of_bid asc limit 1";
        $high_bid_result = $this->db->Execute($sql);
        $this->log($sql, __line__);
        if (!$high_bid_result) {
            $this->log(__line__ . 'DB Error, sql: ' . $sql . " Error: " . $this->db->ErrorMsg(), __line__);
            return false;
        } elseif ($high_bid_result->RecordCount() == 1) {
            $show_high_bidder = $high_bid_result->FetchRow();
            return $show_high_bidder;
        } else {
            return 0;
        }
    }
    /**
     * Inserts the listing in expired listing table
     * @param array $show array of the live listing data
     */
    public function insertInExpired($show)
    {
        //first make sure to remove in case there is already one in there...
        $sql = "DELETE FROM " . geoTables::classifieds_expired_table . " WHERE `id` = ?";
        $delete_bad_result = $this->db->Execute($sql, array($show['id']));

        //now insert it...
        $columnsToCopy = array (
            'id','seller','title','date','description','category','duration',
            'location_zip','ends','search_text','ad_ended','reason_ad_ended',
            'viewed','bolding','better_placement','featured_ad','precurrency',
            'price','postcurrency','price_applies','sold_displayed','business_type','optional_field_1',
            'optional_field_2','optional_field_3','optional_field_4','optional_field_5',
            'optional_field_6','optional_field_7','optional_field_8','optional_field_9',
            'optional_field_10','optional_field_11','optional_field_12','optional_field_13',
            'optional_field_14','optional_field_15','optional_field_16','optional_field_17',
            'optional_field_18','optional_field_19','optional_field_20','phone',
            'phone2','fax','email','auction_type','quantity','quantity_remaining',
            'final_fee','final_price','item_type','hide'
            );

        //generate the big list of question marks...
        $marks = str_repeat('?, ', count($columnsToCopy));
        //remove end ,
        $marks = rtrim($marks, ', ');

        $sql = "INSERT INTO " . geoTables::classifieds_expired_table . " (" . implode(', ', $columnsToCopy) . ") VALUES ($marks)";

        //set category to the category string
        $show['category'] = $this->get_category_string($show['category']);
        //this listing expired naturally, so its ad_ended time is the same as the 'ends' field
        $show['ad_ended'] = $show['ends'];
        //reason ad ended is that it expired...
        $show['reason_ad_ended'] = 'expired';

        $query_data = array();
        foreach ($columnsToCopy as $column) {
            $query_data[] = $show[$column];
        }

        $insert_expired_result = $this->db->Execute($sql, $query_data);

        if (!$insert_expired_result) {
            $this->log('Error inserting in expired table, stopping cron. ' . $this->db->ErrorMsg() . " is the error message." . $sql, __line__);
            return false;
        }
        return $insert_expired_result;
    }
}

return true; //finished task all the way through.
