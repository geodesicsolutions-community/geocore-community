<?php

//remove_messages.php
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
