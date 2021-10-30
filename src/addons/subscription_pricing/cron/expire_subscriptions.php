<?php

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}
$this->log('Top of expire_subscriptions!', __line__);

if ($this->db->get_site_setting('subscription_expire_period_notice')) {
    //TODO: Change that setting to be set per plan item setting...
    $msgs = $this->db->get_text(true, 87);

    $notice_time = intval(geoUtil::time() + (86400 * $this->db->get_site_setting('subscription_expire_period_notice')));
    //TODO: Change that setting to be set per plan item setting...

    $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, s.subscription_expire, s.subscription_id FROM " . geoTables::user_subscriptions_table . " as s, " . geoTables::userdata_table . " as u 
		WHERE u.id = s.user_id AND s.`subscription_expire` < $notice_time AND s.`notice_sent` = 0
		AND s.recurring_billing = 0";
    //echo $sql."<br />";
    $rows = $this->db->GetAll($sql);
    if ($rows === false) {
        $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
        return false;
    }
    foreach ($rows as $row) {
        $message_data["subject"] =  $msgs[1435];
        $user = geoUser::getUser($row['id']);
        if (!$user) {
            continue; //sanity check
        }

        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('salutation', $user->getSalutation());
        $tpl->assign('bodyText', $msgs[1436]);
        $tpl->assign('expirationDate', date($this->db->get_site_setting('entry_date_configuration'), $row['subscription_expire']));
        $tpl->assign('siteURL', $this->db->get_site_setting('classifieds_url'));
        $messageBody = $tpl->fetch('subscription_expires_soon.tpl');

        //send e-mail to end user
        $this->log('Sending subscription expire soon e-mail to ' . $row['email'], __line__);
        geoEmail::sendMail($row['email'], $message_data["subject"], $messageBody, 0, 0, 0, 'text/html');
        if ($this->db->get_site_setting('send_admin_end_email') && geoPC::is_ent()) {
            //send e-mail to admin
            geoEmail::sendMail($this->db->get_site_setting('site_email'), $message_data["subject"], $messageBody, 0, 0, 0, 'text/html');
        }
        $this->log('Setting notice_sent to 1 for subscription id:' . $row['subscription_id'], __line__);
        $sql = "UPDATE " . geoTables::user_subscriptions_table . " SET `notice_sent`=1 WHERE `subscription_id`=" . intval($row['subscription_id']);
        //echo $sql."<br />";
        $update_result = $this->db->Execute($sql);
        if (!$update_result) {
            $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
            return false;
        }
    }
}

//process expirations by price plan to account for price-plan specific grace periods
$sql = "SELECT `price_plan_id` FROM " . geoTables::price_plans_table . " WHERE `type_of_billing`=2";

$allPricePlans = $this->db->GetAll($sql);
$expiredCategories = array();
foreach ($allPricePlans as $planRow) {
    $planItem = geoPlanItem::getPlanItem('subscription', $planRow['price_plan_id']);

    if (!$planItem) {
        continue;
    }

    $grace = (int)$planItem->get('expireBuffer', 60 * 60 * 24 * 3);//grace period, default 3 days

    $sql = "SELECT p.ad_and_subscription_expiration, s.user_id, s.recurring_billing FROM " . geoTables::user_subscriptions_table . " as s, " . geoTables::price_plans_table . " as p
		WHERE p.price_plan_id = s.price_plan_id AND p.price_plan_id=? AND s.`subscription_expire` < " . (geoUtil::time() - $grace);
    $rows = $this->db->GetAll($sql, array((int)$planRow['price_plan_id']));
    if ($rows === false) {
        $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
        return false;
    }
    foreach ($rows as $row) {
        if ($row['recurring_billing']) {
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


        if ($row['ad_and_subscription_expiration']) {
            //expire the ads also
            $this->log('Expiring listings for user ' . $row['user_id'] . " since their subscription expired.", __line__);

            //first, get affected categories, so we can make them re-count
            $sql = "SELECT `category` FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `seller` = " . $row['user_id'];
            $categoryResult = $this->db->Execute($sql);
            while ($categoryResult && $line = $categoryResult->FetchRow()) {
                //just log affected categories for now. re-count down below, outside the loop
                $expiredCategories[$line['category']]++;
            }

            $sql = "UPDATE " . geoTables::classifieds_table . " SET
				`live` = 0,
				`ends` = " . geoUtil::time() . ",
				`reason_ad_ended` = \"expired listings because user subscription expired\"
				WHERE `seller` = " . $row['user_id'];
            $expire_ads_also_result = $this->db->Execute($sql);
            //echo $sql."<br />\n";
            if (!$expire_ads_also_result) {
                $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
                return false;
            }
            $this->log('Number of listings expired: ' . $this->db->Affected_Rows(), __line__);
        }
    }

    $this->log('Deleting all subscriptions that should be expired already for price plan ' . $planRow['price_plan_id'] . '.', __line__);
    $sql = "DELETE FROM " . geoTables::user_subscriptions_table . " WHERE `price_plan_id` = ? AND `subscription_expire` < " . (geoUtil::time() - $grace);
    $expire_subscriptions_results = $this->db->Execute($sql, array((int)$planRow['price_plan_id']));

    if (!$expire_subscriptions_results) {
        $this->log('Error running query, cron job failed.  query: ' . $sql . ' - Error Msg: ' . $this->db->ErrorMsg(), __line__);
        return false;
    }
    $this->log('Number of subscriptions expired: ' . $this->db->Affected_Rows(), __line__);
}

foreach ($expiredCategories as $catId => $count) {
    if ($count > 0) {
        geoCategory::updateListingCount($catId);
    }
}

return true;
