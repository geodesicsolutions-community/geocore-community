<?php

//recurring_billing_update_status.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}

if (!geoPC::is_ent()) {
    return true;
}
$this->log('Top of recurring_billing_update_status! current timestamp: ' . geoUtil::time(), __line__);

$sql = "SELECT `id` FROM " . geoTables::recurring_billing . " WHERE `paid_until`<? AND `status`!=?";

$rows = $this->db->GetAll($sql, array(geoUtil::time(), geoRecurringBilling::STATUS_CANCELED));

if (!$rows || !count($rows)) {
    if ($rows === false) {
        $this->log('DB Error!  sql: ' . $sql . ' Error msg: ' . $this->db->ErrorMsg(), __line__);
    }
    $this->log('Did not find any recurring billing to auto update, so finished.', __line__);
    return true;
}
$this->log('Found ' . count($rows) . ' recurring billings to check status on, starting.', __line__);
foreach ($rows as $row) {
    $recurring = geoRecurringBilling::getRecurringBilling($row['id']);
    if (!$recurring) {
        $this->log('Could not get recurring object for ID # ' . $row['id'], __line__);
        continue;
    }
    $this->log("Updating status for recurring billing # {$row['id']} ", __line__);
    $recurring->updateStatus();
}
$this->log('Finished processing recurring billings.', __line__);
return true;
