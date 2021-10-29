<?php

//send_new_listing_alert_emails.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.6.3-18-g45cfbcb
##
##################################

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}

require_once CLASSES_DIR . 'site_class.php';
require_once CLASSES_DIR . 'user_management_ad_filters.php';
$filters = new User_management_ad_filters();

//get a list of users to check filters for (users who have at least one filter and haven't been checked recently)
$sql = "SELECT `id` FROM " . geoTables::userdata_table . " 
		WHERE (`new_listing_alert_last_sent` + `new_listing_alert_gap`) <= ?
		AND `id` IN (
			SELECT DISTINCT `user_id` FROM " . geoTables::ad_filter_table . "
		)";
$time = geoUtil::time(); // let's save time by only getting the time one time
$this->log('master query is: ' . $sql, __LINE__);
$this->log('using time: ' . $time, __LINE__);
$result = $this->db->Execute($sql, array($time));
foreach ($result as $u) {
    $this->log('queried user: ' . $u['id'], __LINE__);
    $user = geoUser::getUser($u['id']);
    $filters->checkUserFilters($user->id);
    $user->new_listing_alert_last_sent = $time;
    //write email queue to the db per-user, so that if the cron times out, its next run will start more or less where it left off without losing data or getting stuck in a loop
    geoEmail::getInstance()->saveQueue();
}

$this->log('task complete', __LINE__);
return true;
