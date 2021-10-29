<?php

//FILE_NAME.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.4.2-17-g87440d1
##
##################################

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
//setting prefix
$prefix = 'BetPla_';

if (!$this->db->get_site_setting($prefix . 'rotate')) {
    $this->log('Better placement rotation currently disabled, nothing to do.', __line__);
    return true;
}


//raise everyone's number (or more accurately, lower the number)
//OR if recalculateBPR is set, basically reset everyone's better placement so they
//show up in order of date placed.
$recalc = (isset($_GET['recalculateBPR']) && $_GET['recalculateBPR']);

if ($recalc) {
    //let them know what is going on
    $this->log('recalculateBPR Enabled!  Resetting better placement rotation, by Recalculating Better Placement Rotation indexes for ALL listings.', __line__);
}

if ($this->db->get_site_setting($prefix . 'perCategory') && !$recalc) {
    //OK got to do this the hard way, in order to ensure each category moves up
    //the correct number of spots.  Can't just move all up one slot because there
    //will be "blanks" that need to be skipped over from expires listings and the like.

    $this->log("Rotating per category instead of site wide, so going through each category to rotate.", __line__);

    $cats = $this->db->GetAll("SELECT DISTINCT(l.`category`) cat FROM " . geoTables::classifieds_table . " as c, " . geoTables::listing_categories . " as l WHERE c.`id`=l.`listing` AND c.`better_placement`>1 AND l.`is_terminal`='yes'");

    foreach ($cats as $row) {
        $cat = (int)$row['cat'];
        $min = (int)$this->db->GetOne("SELECT MIN(`better_placement`) FROM " . geoTables::classifieds_table . " as c, " . geoTables::listing_categories . " as l WHERE c.`id`=l.`listing` AND l.`category`=$cat AND `better_placement` > 1 AND `live`=1 AND l.`is_terminal`='yes'");

        $sql = "SELECT DISTINCT(c.`id`) as id FROM " . geoTables::classifieds_table . " as c, " . geoTables::listing_categories . " as l WHERE c.`id`=l.`listing` AND l.`category`=$cat AND `better_placement` > 1 AND `live`=1 AND l.`is_terminal`='yes'";
        $listings = $this->db->GetAll($sql);
        $ids = array();
        foreach ($listings as $l) {
            $ids[] = $l['id'];
        }
        $this->log('listing ids to update array in cat# ' . $cat . ': ' . print_r($ids, 1), __LINE__);

        if ($min < 2) {
            $min = 2;
        }
        $min -= 1;
        $changeSql = "(`better_placement`-{$min})";

        if (count($ids) > 0) {
            $sql = "UPDATE " . geoTables::classifieds_table . " SET `better_placement`=$changeSql WHERE `better_placement` > 1 AND `live`=1 AND `id` IN (" . implode(',', $ids) . ")";
            $r = $this->db->Execute($sql);
        }
        $this->log("Moved " . count($ids) . " listings up a slot for category $cat", __line__);
    }
} else {
    //rotate site wide, this is a lot less work than per category (obviously)
    if ($recalc) {
        $changeSql = '1';
    } else {
        $min = (int)$this->db->GetOne("SELECT MIN(`better_placement`) FROM " . geoTables::classifieds_table . " WHERE `better_placement` > 1 AND `live`=1");
        if ($min < 2) {
            $min = 2;
        }
        $min -= 1;
        $changeSql = "(`better_placement`-{$min})";
    }

    $this->db->Execute("UPDATE " . geoTables::classifieds_table . " SET `better_placement`=$changeSql WHERE `better_placement` > 1 AND `live`=1");
    if (!$recalc) {
        $this->log('Rotated all listings up one slot.', __line__);
    }
}

//now get all that need to be bumped back to top
$rows = $this->db->GetAll("SELECT * FROM " . geoTables::classifieds_table . " WHERE `live`=1 AND `better_placement`=1 ORDER BY `date`");

if (count($rows)) {
    //there are some to update
    geoListing::addDataSet($rows);
    $message = ($recalc) ? ' Listings\' Rotation Indexes are about to be recalculated' : ' Listings at "bottom" are about to be moved to "top"';
    $this->log('------- ' . count($rows) . $message . ' -------<br />', __line__);

    foreach ($rows as $row) {
        $listing = geoListing::getListing($row['id']);
        if (!$listing) {
            //something wrong with this one
            continue;
        }
        //Yes, we re-get MAX better placement each iteration of the loop, in case
        //a new listing happens to be added in middle of processing a group of listings

        if ($this->db->get_site_setting($prefix . 'perCategory')) {
            $catMsg = ' in category #' . $listing->category;
            $booth = (int)$this->db->GetOne("SELECT MAX(`better_placement`) FROM " . geoTables::classifieds_table . " as c, " . geoTables::listing_categories . " as l WHERE c.`id`=l.`listing` AND `live`=1 AND l.`category`={$listing->category} AND l.`is_terminal`='yes'");
        } else {
            $catMsg = '';
            $booth = (int)$this->db->GetOne("SELECT MAX(`better_placement`) FROM " . geoTables::classifieds_table . " WHERE `live`=1");
        }
        $booth += 1;
        $booth = ($booth >= 2) ? $booth : 2;

        $this->log("Rotating listing #{$row['id']}$catMsg to top, with better placement rotation index of #$booth", __line__);

        $listing->better_placement = $booth;
    }
}

//finished!
return true;
