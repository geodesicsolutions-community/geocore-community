<?php

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}

//Get all the storefront subscriptions that should be expired by now

//Give them a grace period of 3 days.  Might make this into a setting if anyone requests it.
$grace = 3;
$grace = (int)60 * 60 * 24 * $grace;

if (geoPC::is_ent()) {
    $sql = "SELECT * FROM `geodesic_addon_storefront_subscriptions` WHERE
		`expiration` < " . (geoUtil::time() - $grace) . " AND `recurring_billing`!=0";
    $rows = $this->db->GetAll($sql);
    if ($rows === false) {
        $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
        return false;
    }

    $this->log('First, seeing if any expiring storefront subscriptions have recurring billing that
	could be checked against.  Count found like this: ' . count($rows), __line__);
    foreach ($rows as $row) {
        if ($row['recurring_billing'] && geoPC::is_ent()) {
            //there is a recurring billing!
            $recurring = geoRecurringBilling::getRecurringBilling((int)$row['recurring_billing']);
            if ($recurring && $recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
                //recurring object found, let it update it's status
                $this->log('Updating status for recurring billing used for subscription, for recurring ID ' . $row['recurring_billing'], __line__);
                $recurring->updateStatus();
                if ($recurring->getPaidUntil() > geoUtil::time()) {
                    //it's paid past now, continue to next person
                    $this->log('Subscription extended by way of recurring billing.', __line__);
                    continue;
                }
            }
        }
    }
}

$this->log('Deleting all storefront subscriptions that should be expired already.', __line__);
$sql = "DELETE FROM `geodesic_addon_storefront_subscriptions`
	WHERE `expiration` < " . (geoUtil::time() - $grace) . " AND `onhold_start_time` = 0";
$expire_subscriptions_results = $this->db->Execute($sql, array((int)$planRow['price_plan_id']));

if (!$expire_subscriptions_results) {
    $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
    return false;
}
$this->log('Number of storefront subscriptions expired: ' . $this->db->Affected_Rows(), __line__);


return true;
