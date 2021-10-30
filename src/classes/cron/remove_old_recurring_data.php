<?php

//remove_old_recurring_data.php


if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$this->log('Top of remove_old_recurring_data!', __line__);
if (!geoPC::is_ent()) {
    $this->log('Not Enterprise, nothing to do.');
    return true;
}
//figure out how old are we talkin
$age = $this->db->get_site_setting('recurring_billing_data_age');

if (!$age) {
    $this->log('Removing old recurring data is disabled (time is set to 0), not removing any old recurring data.', __line__);
    return true;
}
//now find orders that are older than that
$age = geoUtil::time() - $age;

$all = $this->db->GetAll("SELECT `id` FROM " . geoTables::recurring_billing . " WHERE `paid_until` < $age");
if (count($all)) {
    //theres work to be done
    $this->log('Found ' . count($all) . ' unpaid recurring billing data to be removed.  Working on it.', __line__);
    foreach ($all as $row) {
        geoRecurringBilling::remove($row['id']);
    }
    $this->log('Finished removing all recurring billing.', __line__);
} else {
    $this->log('No old recurring billing found.', __line__);
}

return true;
