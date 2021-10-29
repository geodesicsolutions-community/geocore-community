<?php

//send_ad_expiration_emails.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.07.0-82-g6d34ec1
##
##################################

if (!defined('GEO_CRON_RUN')) {
    die('NO ACCESS');
}

//send expiration notices
$noticeSettings = array ();
if (geoMaster::is('classifieds')) {
    $noticeSettings['classified_expire_email'] = array('item_type' => 1, 'fav' => false, 'page_id' => 52,);
}
if (geoMaster::is('auctions')) {
    $noticeSettings['auction_expire_email'] = array('item_type' => 2, 'fav' => false, 'page_id' => 52,);
}

//notices for fav's which work slightly differently
$noticeSettings['fav_expire_email'] = array ('fav' => true, 'page_id' => 10212);

$send_ad_expire_frequency = $this->db->get_site_setting('send_ad_expire_frequency');
foreach ($noticeSettings as $setting => $info) {
    $exp_time = $this->db->get_site_setting($setting);
    if (!$exp_time) {
        //this one disabled
        continue;
    }
    //find adjusted time...
    $adjusted_exp = geoUtil::time() + $exp_time;
    $this->log("Sending Expirations for $setting", __line__);
    $this->log("\$adjusted_exp is " . $adjusted_exp, __line__);

    $cTable = geoTables::classifieds_table;
    $query = new geoTableSelect($cTable);
    //don't show if it is sold
    $query->where("$cTable.`sold_displayed` = 0", 'sold_displayed');

    //only get item types that match
    if ($info['item_type']) {
        $query->where("$cTable.`item_type`={$info['item_type']}", 'item_type');
    }
    //only live
    $query->where("$cTable.`live`=1", 'live');
    //not having delayed start
    $query->where("$cTable.`delayed_start`=0");

    $noticeTable = $cTable;

    if ($info['fav']) {
        //reminder about a favorite...
        //combine with check in favorites
        $noticeTable = geoTables::favorites_table;
        $columns = array ('`favorite_id`','`user_id`','`expiration_notice`','`expiration_last_sent`');
        $query->join($noticeTable, "$noticeTable.`classified_id`=$cTable.`id` OR $noticeTable.`auction_id`=$cTable.`id`", $columns);
        //checking fav's, order by user id of favorite
        $query->order("$noticeTable.`user_id`");
    } else {
        //not checking fav's, order by seller
        $query->order("$cTable.`seller`");
    }

    //ALWAYS check the ends time and not endless listing
    $query->where("($cTable.`ends` < $adjusted_exp AND $cTable.`ends` != 0)", 'ends');

    $query->orWhere("$noticeTable.`expiration_notice` = 0", 'ends_notice');

    if ($send_ad_expire_frequency) {
        $query->orWhere("$noticeTable.`expiration_notice` = 1 AND $noticeTable.`expiration_last_sent` > 0 AND ($noticeTable.`expiration_last_sent` + {$send_ad_expire_frequency}) <= " . geoUtil::time(), 'ends_notice');
    }

    $this->log("Query: " . $query, __line__);

    $send_expiration_result = $this->db->Execute($query);
    if (!$send_expiration_result) {
        $this->log("QUERY FAILED. MySQL Said: " . $this->db->ErrorMsg());
        return false;
    }
    if ($send_expiration_result->RecordCount() == 0) {
        $this->log("None found, continuing", __LINE__);
        continue;
    }

    $this->log("Found " . $send_expiration_result->RecordCount() . " results.", __line__);
    $msgs = $this->db->get_text(true, $info['page_id']);
    $lastSeller = "";
    $admin_body = "";
    $ids = array();

    if ($info['fav']) {
        $subject = $msgs[502140];
    } else {
        $subject = $msgs[723];
    }
    $from = $this->db->get_site_setting('site_email');

    $sellers = $renewables = $listingURLs = array();
    //NOrmally we group by seller (sIndex = seller index)
    $sIndex = 'seller';
    if ($info['fav']) {
        //but if it is a favorite, we group by user id...  That is column from
        //the favorites table
        $sIndex = 'user_id';
    }
    while ($show = $send_expiration_result->FetchRow()) {
        //for each seller, store all his expiring listings in format id => title
        $sellers[$show[$sIndex]][$show['id']] = array (
            'title' => strip_tags(geoString::fromDB($show['title'])),
            'ends' => $show['ends'],
        );

        if (!$info['fav'] && $show['item_type'] == 1) {
            //find out if this classified can be renewed (auctions cannot be renewed)
            $renew_cutoff = ($show['ends'] - ($this->db->get_site_setting('days_to_renew') * 86400));
            $renew_postcutoff = ($show['ends'] + ($this->db->get_site_setting('days_to_renew') * 86400));
            if (($this->db->get_site_setting('days_to_renew')) && (geoUtil::time() > $renew_cutoff) && (geoUtil::time() < $renew_postcutoff)) {
                //can renew for this listing
                $renewables[$show['id']] = true;
            }
        }

        $listingURLs[$show['id']] = geoListing::getListing($show['id'])->getFullUrl();
    }

    $tpl = new geoTemplate('system', 'emails');
    if ($info['fav']) {
        $tpl->assign('introduction', $msgs[502141]);
        $tpl->assign('expirationMessage', $msgs[502142]);
        $tpl->assign('expireLabel', $msgs[502144]);
    } else {
        $tpl->assign('introduction', $msgs[724]);
        $tpl->assign('expirationMessage', $msgs[725]);
        $tpl->assign('expireLabel', $msgs[502143]);
    }
    $tpl->assign('listingURLs', $listingURLs);
    //let template know if this is a favorite listing notice or not, to allow
    //for easier customizations
    $tpl->assign('is_favorite_notice', $info['fav']);

    foreach ($sellers as $seller => $expiring) {
        //make sure to mark listings "at same time" as sending out each
        //e-mail, that way if one e-mail failed to send in middle it does
        //not try to re-send ones before it.
        $ids = array_keys($expiring);
        //just added check make sure seller is number...
        $seller = (int)$seller;

        $idIndex = ($info['fav']) ? '`classified_id`' : '`id`';

        $where = "$idIndex IN (" . implode(', ', $ids) . ")";
        if ($info['fav']) {
            $where = "($where OR `auction_id` IN (" . implode(', ', $ids) . "))";
            //need to also only update the rows for that user, otherwise other users
            //that have a listing favorited may not get their notice
            $where .= " AND `user_id` = $seller";
        }

        $sql = "UPDATE $noticeTable SET `expiration_notice` = 1, `expiration_last_sent` = " . geoUtil::time() . " WHERE $where";

        $update_expiration_result = $this->db->Execute($sql);
        if (!$update_expiration_result) {
            $this->log("update query failed. exiting. sql: $sql \n\nmysql said: " . $this->db->ErrorMsg(), __line__);
            return false;
        }

        $user = geoUser::getUser($seller);
        if ($user) {
            $tpl->assign('salutation', $user->getSalutation());

            $anonR = geoAddon::getRegistry('anonymous_listings');
            if ($anonR && $anonR->anon_user_id == $seller) {
                //these listings are owned by the anonymous user
                //need to send expiration to each of them separately

                foreach ($expiring as $id => $data) {
                    $listing_data = geoListing::getListing($id);
                    $target = geoString::fromDB($listing_data->email);
                    if ($target) {
                        $tpl->assign('expiringListings', array($id => $data)); //template is expecting to get this entire array for the normal case...simulate it here
                        $messageBody = $tpl->fetch('listing/listings_expire_soon.tpl');
                        geoEmail::sendMail($target, $subject, $messageBody, 0, 0, 0, 'text/html');
                        $this->log('sending this email to ' . $email_to_send_to . ':<br /><strong>' . $subject . '</strong><br /><div style="white-space: normal; border: 1px dashed black; padding: 5px;">' . $messageBody . '</div>', __LINE__);
                    }
                }
            } else {
                //these are normal, not-anonymous listings, and already grouped by seller
                //so we can just send one email to each seller with a list of expiring listings
                $tpl->assign('expiringListings', $expiring);

                if (!$info['fav']) {
                    $tpl->assign('renewLabel', $msgs[502046]);
                    $tpl->assign('renewables', $renewables);
                }
                $messageBody = $tpl->fetch('listing/listings_expire_soon.tpl');

                geoEmail::sendMail($user->email, $subject, $messageBody, 0, 0, 0, 'text/html');
                $this->log('sending this email to ' . $user->email . ':<br /><strong>' . $subject . '</strong><br /><div style="white-space: normal; border: 1px dashed black; padding: 5px;">' . $messageBody . '</div>', __LINE__);

                if (!$info['fav']) {
                    $admin_body .= "[User: " . $user->username . "]\n";
                    foreach ($expiring as $id => $title) {
                        $admin_body .= $title['title'] . "\n" . $this->db->get_site_setting('classifieds_url') . "?a=2&amp;b=" . $id . "\n\n";
                    }
                }
            }
        } else {
            $this->log('NOTICE: could not get user object to send email to: ' . $seller, __LINE__);
        }
    }


    if (!$info['fav'] && $this->db->get_site_setting("send_admin_end_email")) {
        $type = ($info['item_type'] == 1) ? "classifieds" : "auctions";
        $this->log("sending an email to admin (" . $type . ")", __line__);

        $adminMsg = "The following " . $type . " will expire soon\n\n";
        $adminMsg .= $admin_body;
        $subject = $type . " expiring soon";
        geoEmail::sendMail($this->db->get_site_setting('site_email'), $subject, $adminMsg, 0, 0, 0, 'text/plain');
    }

    $this->log("END CRON: of send ad expiration emails cron", __line__);
}
return true;
