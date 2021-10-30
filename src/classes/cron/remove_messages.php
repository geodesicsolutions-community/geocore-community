<?php

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$this->log('Top of remove_messages!', __line__);

//figure out how old are we talkin
$age = $this->db->get_site_setting('messages_age');

if (!$age) {
    $this->log('Removing old messages is disabled (time is set to 0), not removing any old messages.', __line__);
    return true;
}
//now find orders that are older than that
$age = geoUtil::time() - $age;

$this->db->Execute("DELETE FROM `geodesic_user_communications` WHERE `date_sent` < $age");

$this->log("Just removed " . $this->db->Affected_Rows() . " old messages, that were placed before $age", __line__);

return true;
