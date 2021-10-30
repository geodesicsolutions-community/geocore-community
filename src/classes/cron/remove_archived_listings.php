<?php

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
