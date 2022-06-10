<?php

class geoAdminHome
{
    public function display_home()
    {
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        //toggle switch to hide the Getting Started section from everywhere in the admin
        if ($_GET['dismiss_gs'] === 'yes') {
            $db->set_site_setting('hide_getting_started', 1);
            $db->set_site_setting('adminLandingPage', 'home');
        } elseif ($_GET['dismiss_gs'] === 'no') {
            //no UI uses this, but it's an easy way to undo this setting for development
            $db->set_site_setting('hide_getting_started', 0);
        }

        //$admin->v()->hide_side_menu = 1; //make it not load side menu
        $admin->v()->hide_title = 1; //make true to not show title

        $admin->v()->hide_notifications = false; // make true to not show notifications
        //get notifications as HTML so we can move them around on the page
        //$admin->v()->notify = Notifications::getNotificationsAsHTML();

        //find software versions so we know which stats to show
        $isAuctions = geoMaster::is('auctions');
        $isClassifieds = geoMaster::is('classifieds');
        $isLeased = geoPC::is_leased();

        $settings = array();
        $settings['product'] = array(
                                    'auctions' => $isAuctions,
                                    'classifieds' => $isClassifieds,
                                    'leased' => $isLeased,
                                    );

        //get "getting started" completion percentage
        require_once('getting_started.php');
        $gs = new adminGettingStarted();
        $settings['getting_started_completion'] = $gs->getCompletionPercentage();
        $settings['hide_getting_started'] = $db->get_site_setting('hide_getting_started');

        //get stats
        $stats['users'] = $this->getUsersStats();

        if ($isAuctions) {
            $stats['auctions'] = $this->getAuctionStats();
        }
        if ($isClassifieds) {
            $stats['classifieds'] = $this->getClassifiedStats();
        }
        $stats['groupsplans'] = $this->getGroupsPlansStats();
        $stats['orders'] = $this->getOrderStats();
        $stats['other'] = $this->getOtherStats();
        $stats['extras'] = $this->getExtrasStats();

        //To "test" having expired support/downloads, swap this out for value of either one below:
        //geoUtil::time()-1000;//

        $stats['supportExpire'] = geoPC::getSupportExpire();
        if (!$isLeased) {
            $stats['downloadExpire'] = geoPC::getDownloadExpire();
        }

        $stats['packageId'] = geoPC::getPackageId();

        $currentTime = geoUtil::time();
        //die ("stats:<pre>".print_r($stats,1));

        if ($stats['supportExpire'] !== 'never' && $stats['supportExpire'] > $currentTime) {
            $stats['supportLeft'] = $this->_timeLeft($stats['supportExpire']);
        }

        if (!$isLeased && $stats['downloadExpire'] !== 'never' && $stats['downloadExpire'] > $currentTime) {
            $stats['downloadLeft'] = $this->_timeLeft($stats['downloadExpire']);
        }

        if ($isLeased) {
            $stats['licenseExpire'] = geoPC::getLicenseExpire();
            $stats['localLicenseExpire'] = geoPC::getLocalLicenseExpire();
            if ($stats['localLicenseExpire'] > $currentTime) {
                $stats['licenseLeft'] = $this->_timeLeft($stats['localLicenseExpire']);
            }
            $stats['licenseKey'] = $db->get_site_setting('license');
        }

        //make numbers look pretty
        foreach ($stats as $type => $s) {
            if (is_array($s)) {
                foreach ($s as $key => $val) {
                    if ((int)$val == $val) {
                        //this is a number
                        if ($val >= 10000000) {
                            //more than 10 million; show as millions
                            $stats[$type][$key] = round($val / 1000000, 1) . 'M';
                        } elseif ($val >= 10000) {
                            //between 10 thousand and 10 million; show as thousands
                            $stats[$type][$key] = round($val / 1000, 1) . 'k';
                        }
                        //otherwise just print as normal
                    }
                }
            }
        }

        $settings['stats'] = $stats;

        //landing page
        $settings['landingPage'] = $db->get_site_setting('adminLandingPage');

        $settings['adminMsgs'] = geoAdmin::m();

        if (geoPC::is_trial()) {
            $settings['is_trial_demo'] = true;
            $settings['demo_deletion'] = $this->_timeLeft($stats['downloadExpire'] + 86400 * 14);
        }

        //get stats for current users' device types
        //uses Google Charts; options: https://developers.google.com/chart/interactive/docs/gallery/piechart
        $result = $db->Execute("SELECT * FROM `geodesic_sessions_registry` WHERE `index_key` = 'device_type'");
        $settings['devices']['total'] = (int)$result->RecordCount();
        if ($settings['devices']['total']) {
            foreach ($result as $types) {
                if (in_array($types['val_string'], array('desktop','phone','tablet'))) {
                    $settings['devices'][$types['val_string']]++;
                }
            }
        }

        //same for browser types
        $result = $db->Execute("SELECT * FROM `geodesic_sessions_registry` WHERE `index_key` = 'browser_type'");
        $settings['browsers']['total'] = (int)$result->RecordCount();
        if ($settings['browsers']['total']) {
            foreach ($result as $types) {
                if (in_array($types['val_string'], array('iPhone','Android','Firefox','IE','Chrome'))) {
                    $settings['browsers'][$types['val_string']]++;
                }
            }
        }

        //get stats of new users and revenue over last 7 days
        //start by figuring out the ticktime equivalent for midnight each day
        $midnight = strtotime('midnight', geoUtil::time());
        $targetTimes = array(
            $midnight - (86400 * 7),
            $midnight - (86400 * 6),
            $midnight - (86400 * 5),
            $midnight - (86400 * 4),
            $midnight - (86400 * 3),
            $midnight - (86400 * 2),
            $midnight - (86400 * 1),
            $midnight,
            $midnight + (86400 * 1),
        );
        //users registered in date range
        $newUsers = $db->Prepare("SELECT count(id) FROM `geodesic_userdata` as u WHERE `date_joined` >= ? AND `date_joined` <= ?");
        for ($i = 0; $i <= 7; $i++) { //NOTE: do not loop over the final array value, because $i+1 does not exist there!
            $settings['newUsers'][date("M d", $targetTimes[$i])] = $db->GetOne($newUsers, array($targetTimes[$i], $targetTimes[$i + 1]));
        }
        //transactions completed in date range
        if (geoMaster::is('site_fees')) {
            $transactions = $db->Prepare("SELECT `amount` FROM `geodesic_transaction` as t WHERE t.gateway <> 'site_fee' AND t.status = 1 AND t.date >= ? AND t.date <= ?");
            for ($i = 0; $i <= 7; $i++) { //NOTE: do not loop over the final array value, because $i+1 does not exist there!
                $result = $db->Execute($transactions, array($targetTimes[$i], $targetTimes[$i + 1]));
                $total = 0;
                foreach ($result as $t) {
                    $total += $t['amount'];
                }
                $settings['dailyTransactions'][date("M d", $targetTimes[$i])] = $total;
            }
        } else {
            //site fees off? don't show transaction graph
            $settings['dailyTransactions'] = false;
        }


        //call template and show page
        $settings['geoturbo_status'] = geoPC::geoturbo_status();
        $settings['white_label'] = geoPC::is_whitelabel();
        $admin->setBodyTpl('home/index.tpl')
            ->v()->setBodyVar($settings);
    }

    public function update_home()
    {
        //change if show last page viewed, or home page, after admin login
        $valid = array('home','checklist');
        $landingPage = (isset($_POST['landingPage']) && in_array($_POST['landingPage'], $valid)) ? $_POST['landingPage'] : false;

        $db = DataAccess::getInstance();
        $db->set_site_setting('adminLandingPage', $landingPage);

        return true;
    }

    public function getUsersStats()
    {
        $db = DataAccess::getInstance();

        //number of active users
        $sql = "select count(geodesic_logins.id) from geodesic_logins,geodesic_userdata where geodesic_logins.id > 1 and geodesic_logins.status = 1 and geodesic_logins.id = geodesic_userdata.id";
        $user_stats['total'] = $db->GetOne($sql);

        //number of unapproved registrations
        $sql = "select count(`id`) from geodesic_confirm";
        $user_stats['registrations'] = $db->GetOne($sql);

        //sessions active within the last half-hour
        $current_time_to_use = (geoUtil::time() - 1800) + (3600 * $db->get_site_setting('time_shift'));
        $sql = "SELECT COUNT(`classified_session`) FROM `geodesic_sessions` WHERE `admin_session` = 'No' AND `last_time` >= " . $current_time_to_use;
        $user_stats['current'] = $db->GetOne($sql);

        //number of new registrations in last 1/7/30 days
        $sql = "select count(geodesic_logins.id) from geodesic_logins,geodesic_userdata where geodesic_logins.id > 1 and geodesic_logins.status = 1 and geodesic_logins.id = geodesic_userdata.id and geodesic_userdata.date_joined > " . (geoUtil::time() - 86400);
        $user_stats['last1'] = $db->GetOne($sql);
        $sql = "select count(geodesic_logins.id) from geodesic_logins,geodesic_userdata where geodesic_logins.id > 1 and geodesic_logins.status = 1 and geodesic_logins.id = geodesic_userdata.id and geodesic_userdata.date_joined > " . (geoUtil::time() - 604800);
        $user_stats['last7'] = $db->GetOne($sql);
        $sql = "select count(geodesic_logins.id) from geodesic_logins,geodesic_userdata where geodesic_logins.id > 1 and geodesic_logins.status = 1 and geodesic_logins.id = geodesic_userdata.id and geodesic_userdata.date_joined > " . (geoUtil::time() - 2592000);
        $user_stats['last30'] = $db->GetOne($sql);

        $sql = "SELECT u.`username`, u.`id`, `email`, `date_joined`, l.`status` FROM `geodesic_userdata` as u, `geodesic_logins` as l WHERE u.`id`=l.`id` AND u.id <> 1 ORDER BY `date_joined` DESC, u.`id` DESC LIMIT 5";
        $result = $db->Execute($sql);
        foreach ($result as $user) {
            $user_stats['recent'][] = array(
                'username' => $user['username'],
                'email' => $user['email'],
                'id' => $user['id'],
                'joined' => date("M j, Y", $user['date_joined']),
                'status' => ($user['status'] == 1) ? 'Active' : 'Suspended'
            );
        }

        return $user_stats;
    }

    public function getAuctionStats()
    {
        $db = DataAccess::getInstance();

        $sql_query = "select count(*) as total_ads from " . $db->geoTables->auctions_table . " where live=1 and item_type=2";
        $result = $db->Execute($sql_query);
        if (!$result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        }
        $show_stats = $result->FetchRow();
        $auction_stats['count'] = $show_stats['total_ads'];


        $query = "select count(distinct(geodesic_logins.id)) as total_users_with_ads from geodesic_logins,geodesic_classifieds where geodesic_logins.id = geodesic_classifieds.seller and geodesic_classifieds.live = 1 and item_type=2";
        $result = $db->Execute($query);
        if (!$result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        }
        $show_stats = $result->FetchRow();
        $auction_stats['users'] = $show_stats['total_users_with_ads'];


        $query = "select sum(viewed) as total_viewed from " . $db->geoTables->auctions_table . " where live=1 and item_type=2";
        $result = $db->Execute($query);
        if (!$result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        }
        $show_stats = $result->FetchRow();
        $auction_stats['viewed'] = (!$show_stats['total_viewed']) ? 0 : $show_stats['total_viewed'];
        if ($db->tableColumnExists(geoTables::classifieds_table, 'customer_approved')) {
            $sql = "SELECT count(*) as count FROM " . geoTables::classifieds_table . " WHERE ((live = 0 and ends > " . geoUtil::time() . " and customer_approved = 1) or renewal_payment_expected != 0 or live = 2) AND (`order_item_id` = 0 OR `order_item_id` = '') AND `item_type` = 2";
            $row = $db->GetRow($sql);
            if ($row === false) {
                trigger_error("ERROR DB: " . $db->ErrorMsg());
                $row = array('count' => '0');
            }
            $count = intval($row['count']);
        } else {
            $count = 0;
        }
        //figure out count from new system

        $sql = "SELECT count(oi.id) as count
		FROM " . geoTables::order_item . " as oi, " . geoTables::logins_table . " as u, " . geoTables::order . " as o
		WHERE oi.status IN ('pending', 'pending_edit') AND oi.type='auction'
		AND oi.`parent` = 0 AND o.id = oi.`order` AND o.status = 'active' AND u.id = o.buyer";
        $row = $db->GetRow($sql);
        if ($row === false) {
            trigger_error("ERROR DB: " . $db->ErrorMsg());
            $row = array('count' => '0');
        }
        $count += intval($row['count']);

        $auction_stats['unapproved'] = $count;

        return $auction_stats;
    }

    public function getClassifiedStats()
    {
        $db = DataAccess::getInstance();

        $sql_query = "select count(*) as total_ads from " . $db->geoTables->classifieds_table . " where live=1 and item_type=1";
        $result = $db->Execute($sql_query);
        if (!$result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        }
        $show_stats = $result->FetchRow();
        $ad_stats['count'] = $show_stats['total_ads'];


        $query = "select count(distinct(geodesic_logins.id)) as total_users_with_ads from geodesic_logins,geodesic_classifieds where geodesic_logins.id = geodesic_classifieds.seller and geodesic_classifieds.live = 1 and item_type=1";
        $result = $db->Execute($query);
        if (!$result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        }
        $show_stats = $result->FetchRow();
        $ad_stats['users'] = $show_stats['total_users_with_ads'];


        $query = "select sum(viewed) as total_viewed from " . $db->geoTables->classifieds_table . " where live=1 and item_type=1";
        $result = $db->Execute($query);
        if (!$result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        }
        $show_stats = $result->FetchRow();
        $ad_stats['viewed'] = (!$show_stats['total_viewed']) ? 0 : $show_stats['total_viewed'];

        if ($db->tableColumnExists(geoTables::classifieds_table, 'customer_approved')) {
            $sql = "SELECT count(*) as count FROM " . geoTables::classifieds_table . " WHERE ((live = 0 and ends > " . geoUtil::time() . " and customer_approved = 1) or renewal_payment_expected != 0 or live = 2) AND (`order_item_id` = 0 OR `order_item_id` = '') AND `item_type` = 1";
            $row = $db->GetRow($sql);
            if ($row === false) {
                //normal for this to fail on newer installations
                trigger_error("DEBUG DB: " . $db->ErrorMsg());
                $row = array('count' => '100');
            }
            $count = intval($row['count']);
        } else {
            $count = 0;
        }
        //figure out count from new system

        $sql = "SELECT count(oi.id) as count
		FROM " . geoTables::order_item . " as oi, " . geoTables::logins_table . " as u, " . geoTables::order . " as o
		WHERE oi.status IN ('pending', 'pending_edit') AND oi.type='classified'
		AND oi.`parent` = 0 AND o.id = oi.`order` AND o.status = 'active' AND u.id = o.buyer";
        $row = $db->GetRow($sql);
        if ($row === false) {
            trigger_error("ERROR DB: " . $db->ErrorMsg());
            $row = array('count' => '100');
        }
        $count += intval($row['count']);
        $ad_stats['unapproved'] = $count;

        return $ad_stats;
    }

    public function getGroupsPlansStats()
    {
        $db = DataAccess::getInstance();

        $query = "SELECT * FROM geodesic_groups";
        $group_result = $db->Execute($query);
        if (!$group_result) {
            trigger_error("ERROR: " . $db->ErrorMsg());
            return false;
        } elseif ($group_result->RecordCount() > 0) {
            while ($show_group = $group_result->FetchRow()) {
                $query = "select count(*) as group_total from " . $db->geoTables->user_groups_price_plans_table . " where group_id = " . $show_group['group_id'] . " and id!=1";
                $group_count_result = $db->Execute($query);
                if (!$group_count_result) {
                    trigger_error("ERROR: " . $db->ErrorMsg());
                    return false;
                } elseif ($group_count_result->RecordCount() == 1) {
                    $show_group_count = $group_count_result->FetchRow();
                } else {
                    return false;
                }

                $user_groups_stats[$show_group['group_id']]['name'] = $show_group['name'];
                $user_groups_stats[$show_group['group_id']]['count'] = $show_group_count['group_total'];
            }
        }


        // Auction Price Plans
        if (geoMaster::is('auctions')) {
            $query = "SELECT * FROM " . $db->geoTables->price_plans_table . " where applies_to = 2";
            $price_plan_result = $db->Execute($query);
            if (!$price_plan_result) {
                trigger_error("ERROR: " . $db->ErrorMsg());
                return false;
            } elseif ($price_plan_result->RecordCount() > 0) {
                while ($show_price_plan = $price_plan_result->FetchRow()) {
                    $query = "select count(*) as price_plan_total from " . $db->geoTables->user_groups_price_plans_table . " where auction_price_plan_id = " . $show_price_plan['price_plan_id'] . " and id != 1";
                    $plan_count_result = $db->Execute($query);
                    if (!$plan_count_result) {
                        trigger_error("ERROR: " . $db->ErrorMsg());
                        return false;
                    } elseif ($plan_count_result->RecordCount() == 1) {
                        $show_plan_count = $plan_count_result->FetchRow();
                    }
                    $price_plans_stats[$show_price_plan['price_plan_id']]['name'] = $show_price_plan['name'];
                    $price_plans_stats[$show_price_plan['price_plan_id']]['count'] = $show_plan_count['price_plan_total'];
                }
            }
        }

        // Classified Price Plans
        if (geoMaster::is('classifieds')) {
            $query = "SELECT * FROM " . $db->geoTables->price_plans_table . " where applies_to = 1";
            $price_plan_result = $db->Execute($query);
            if (!$price_plan_result) {
                trigger_error("ERROR: " . $db->ErrorMsg());
                return false;
            } elseif ($price_plan_result->RecordCount() > 0) {
                while ($show_price_plan = $price_plan_result->FetchRow()) {
                    $query = "select count(*) as price_plan_total from " . $db->geoTables->user_groups_price_plans_table . " where price_plan_id = " . $show_price_plan['price_plan_id'] . " and id != 1";
                    //echo $this->sql_query." is the query <bR>";
                    $plan_count_result = $db->Execute($query);
                    if (!$plan_count_result) {
                        trigger_error("ERROR: " . $db->ErrorMsg());
                        return false;
                    } elseif ($plan_count_result->RecordCount() == 1) {
                        $show_plan_count = $plan_count_result->FetchRow();
                    }

                    $price_plans_stats[$show_price_plan['price_plan_id']]['name'] = $show_price_plan['name'];
                    $price_plans_stats[$show_price_plan['price_plan_id']]['count'] = $show_plan_count['price_plan_total'];
                }
            }
        }

        $groups_plans_stats = array('groups' => $user_groups_stats, 'plans' => $price_plans_stats);
        return $groups_plans_stats;
    }

    public function getOrderStats()
    {
        $db = DataAccess::getInstance();

        //Need this complex query to weed out orders without things in them,
        //and orders that have not yet gotten to the stage of having an invoice.
        $sql_base = "SELECT count(o.id) as count FROM `geodesic_order` AS o,`geodesic_invoice` AS i, `geodesic_order_registry` as o_r, `geodesic_logins` as u WHERE i.order = o.id AND o_r.order = o.id AND o_r.`index_key` = 'payment_type' AND u.id = o.buyer AND o.seller = 0";
        $order_stats['total'] = $db->GetOne($sql_base);

        //Prepare the statement, to be better optimized since the only thing changing is status.
        $stmt = $db->Prepare($sql_base . " AND o.`status` = ?");

        $order_stats['pending'] = $db->GetOne($stmt, array('pending'));
        $order_stats['pending_admin'] = $db->GetOne($stmt, array('pending_admin'));
        $order_stats['active'] = $db->GetOne($stmt, array('active'));
        $order_stats['suspended'] = $db->GetOne($stmt, array('suspended'));
        $order_stats['canceled'] = $db->GetOne($stmt, array('canceled'));
        $order_stats['fraud'] = $db->GetOne($stmt, array('fraud'));
        $order_stats['incomplete'] = $db->GetOne($stmt, array('incomplete'));


        //get a table of data about the 5 most recent orders
        $sql = "SELECT o.id, o.buyer, o.created, u.username, u.id as user_id FROM `geodesic_order` AS o,`geodesic_invoice` AS i, `geodesic_order_registry` as o_r, `geodesic_userdata` as u
			WHERE i.order = o.id AND o_r.order = o.id AND o_r.`index_key` = 'payment_type' AND u.id = o.buyer AND o.seller = 0 ORDER BY o.created DESC LIMIT 5";
        $result = $db->Execute($sql);
        $getOrderContents = $db->Prepare("SELECT `type`, `parent` FROM `geodesic_order_item` WHERE `order` = ?");
        foreach ($result as $order) {
            //get total price of this order from all its transactions
            $o = geoOrder::getOrder($order['id']);
            $transactions = $o->getInvoice()->getTransaction();
            $amount = 0;
            foreach ($transactions as $t) {
                $amount += max($t->getAmount(), 0); //ignore negative transaction amounts for this purpose
            }

            $status = $o->getStatus();
            $tooltip = "<strong>Status: " . ucwords($status) . "</strong><br />";
            //get the name of main item types on this order
            $mainItemTypes = $db->Execute($getOrderContents, array($order['id']));
            $formatted = array();
            foreach ($mainItemTypes as $mt) {
                //brute force the names to be a bit prettier
                //ideal might be to get a friendly name from the actual Order Items classes, but this is a lot faster/more generic
                if (in_array($mt['type'], array('subtotal_display'))) {
                    //some items aren't worth showing here
                    continue;
                }
                $formatted = str_replace('_', ' ', $mt['type']);
                $formatted = str_replace('listing extra', '', $formatted);
                $formatted = str_replace('addon', '', $formatted);
                $formatted = str_replace('featured level', 'featured level ', $formatted);
                $formatted = str_replace('discount codes', 'discount code used ', $formatted);
                $formatted = ucwords($formatted);
                if ($mt['parent'] != 0) {
                    $formatted = "<span style='font-size: 8pt;'><i class='fa fa-check-square-o' style='margin-left:7px;'></i> $formatted</span>";
                }
                $tooltip .= $formatted . '<br />';
            }

            $order_stats['recent'][] = array(
                'id' => $order['id'],
                'contents' => geoHTML::showTooltip('Order Contents', $tooltip),
                'username' => $order['username'],
                'user_id' => $order['user_id'],
                'amount' => geoString::displayPrice($amount),
                'date' => date("M j, Y", $order['created'])
            );
        }

        //order items

        //find the list of item types typically shown in the admin
        $types = geoOrderItem::getOrderItemTypes();
        $typesUse = array ();
        foreach ($types as $type => $typeInfo) {
            if (method_exists($typeInfo['class_name'], 'adminDetails')) {
                $typesUse[] = $type;
            }
        }


        $sql = "SELECT count(oi.id) FROM " . geoTables::order_item . " as oi, " . geoTables::logins_table . " as u, " . geoTables::order . " as o
		WHERE oi.`type` IN ('" . implode("', '", $typesUse) . "') AND oi.`parent` = 0 AND o.id = oi.`order` AND o.status = 'active' AND u.id = o.buyer";
        $order_stats['total_items'] = $db->GetOne($sql);
        $sql .= " AND oi.`status` = 'pending'";
        $order_stats['waiting_items'] = $db->GetOne($sql);
        return $order_stats;
    }

    public function getOtherStats()
    {
        $db = DataAccess::getInstance();
        $addon = geoAddon::getInstance();

        $other_stats['addonsInstalled'] = $addon->installedAddonsCount();
        $other_stats['addonsEnabled'] = $addon->enabledAddonsCount();

        $sql = "select count(language_id) from `geodesic_pages_languages`";
        $other_stats['languagesInstalled'] = $db->GetOne($sql);

        $sql = "select count(language_id) from `geodesic_pages_languages` where `active` = 1";
        $other_stats['languagesEnabled'] = $db->GetOne($sql);

        return $other_stats;
    }

    public function getExtrasStats()
    {
        $db = DataAccess::getInstance();
        $addon = geoAddon::getInstance();
        $extras['bolding'] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `bolding` = 1");
        $extras['better_placement'] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `better_placement` >= 1");
        $extras['featured'][1] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `featured_ad` = 1");
        if ($addon->isEnabled('featured_levels')) {
            $extras['featured'][2] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `featured_ad_2` = 1");
            $extras['featured'][3] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `featured_ad_3` = 1");
            $extras['featured'][4] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `featured_ad_4` = 1");
            $extras['featured'][5] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `featured_ad_5` = 1");
        }
        if ($addon->isEnabled('attention_getters')) {
            $extras['attention_getter'] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " WHERE `live` = 1 AND `attention_getter` = 1");
        } else {
            $extras['attention_getter'] = false;
        }
        if ($addon->isEnabled('charity_tools')) {
            $extras['charitable'] = $db->GetOne("SELECT COUNT(`id`) FROM " . geoTables::classifieds_table . " AS class WHERE `live` = 1 AND EXISTS(
					SELECT `listing` FROM `geodesic_addon_charity_tools_charitable_purchases` AS charity WHERE charity.listing=class.id)");
        } else {
            $extras['charitable'] = false;
        }
        return $extras;
    }

    private function _timeLeft($exp)
    {
        $exp = (int)$exp;
        $left = $exp - geoUtil::time();
        $left = floor($left / (60 * 60 * 24));

        //convert to rough month/year
        if ($left > 365) {
            return round($left / 365, 1) . ' Years';
        } elseif ($left > 30) {
            return round($left / 30, 1) . ' Months';
        }

        //down to number of days
        return $left . ' Days';
    }
}
