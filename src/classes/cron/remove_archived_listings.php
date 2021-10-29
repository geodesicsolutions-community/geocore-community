<?php

//remove_archived_listings.php
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

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$this->log('Top of remove_archived_listings!', __line__);

//figure out how old are we talkin
$age = $this->db->get_site_setting('archive_age');

if (!$age) {
    $this->log('Removing archived listings is disabled (time is set to 0), not removing any archived listings.', __line__);
    return true;
}
//now find orders that are older than that
$age = geoUtil::time() - $age;

$this->db->Execute("DELETE FROM `geodesic_classifieds_expired` WHERE `date` < $age");

$this->log("Just removed " . $this->db->Affected_Rows() . " archived listings, that were archived before $age", __line__);

return true;
