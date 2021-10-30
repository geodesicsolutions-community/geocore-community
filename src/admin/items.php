<?php

class OrderItemManagement
{
    private static $_validStatusItem = array (
        'all',
        'active',
        'pending',
        'declined',
        'pending_alter'
    );

    private static $_validSortBy = array (
        'oi.id','oi.type','username','created','oi.status',
    );

    public function display_orders_list_items()
    {
        $admin = geoAdmin::getInstance();

        $admin->setBodyTpl('orders/list_items');
        $status = (isset($_GET['narrow_item_status'])) ? $_GET['narrow_item_status'] : 'all';
        $item_type = (isset($_GET['narrow_item_type'])) ? $_GET['narrow_item_type'] : 'all';
        $start = (isset($_GET['current_page'])) ? (int)$_GET['current_page'] - 1 : 0;
        $start = max($start, 0);

        //get the types
        $all_types = geoOrderItem::getOrderItemTypes();
        $types = array('all' => 'Any Type');
        foreach ($all_types as $k => $val) {
            if (count($val['parents']) == 0) {
                $this_item = Singleton::getInstance($val['class_name']);
                if ($this_item->displayInAdmin()) {
                    if (method_exists($this_item, 'getTypeTitle')) {
                        $title = $this_item->getTypeTitle();
                    } else {
                        $title = ucwords(str_replace('_', ' ', $k));
                    }
                    $types[$k] = $title;
                }
            }
        }
        if (!array_key_exists($item_type, $types)) {
            $item_type = 'all';
        }
        if (!in_array($status, self::$_validStatusItem)) {
            $status = 'pending';
        }

        $date['low'] = (isset($_GET['date']['low']) && strlen($_GET['date']['low'])) ? $_GET['date']['low'] : '';
        $date['high'] = (isset($_GET['date']['high']) && strlen($_GET['date']['high'])) ? $_GET['date']['high'] : '';
        $date_urls = $date;

        if ($date['low']) {
            $parts = explode('-', trim($date['low']));
            $date['low'] = (int)mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);
        }
        if ($date['high']) {
            $parts = explode('-', trim($date['high']));
            $date['high'] = (int)mktime(23, 59, 59, $parts[1], $parts[2], $parts[0]);
        }
        $sortBy = (in_array($_GET['sortBy'], self::$_validSortBy)) ? $_GET['sortBy'] : 'oi.id';
        $sortOrder = (in_array($_GET['sortOrder'], array ('up','down'))) ? $_GET['sortOrder'] : 'up';

        $narrow_username = (isset($_GET['narrow_username']) && strlen(trim($_GET['narrow_username']))) ? trim($_GET['narrow_username']) : '';

        $items = $this->_getItems($status, $item_type, $date, $narrow_username, $sortBy, $sortOrder, $start);

        $count = $items['count'];
        unset($items['count']);
        $admin->v()->items = $items;

        $admin->v()->types = $types;
        $admin->v()->narrow_type = $item_type;
        $admin->v()->narrow_status = $status;
        //'index.php?page=orders_list_items&amp;narrow_item_status='.$status.'&amp;narrow_item_type='.$item_type.'&amp;current_page='
        $link = $admin->v()->sort_link = "index.php?page=orders_list_items&amp;narrow_item_status=$status&amp;narrow_item_type=$item_type&amp;date[low]=" . htmlspecialchars($date_urls['low']) . "&amp;date[high]=" . htmlspecialchars($date_urls['high']) . "&amp;narrow_username=" . htmlspecialchars($narrow_username);
        $admin->v()->date = $date_urls;
        $admin->v()->narrow_username = $narrow_username;
        $admin->v()->sortBy = $sortBy;
        $admin->v()->sortOrder = $sortOrder;

        $cjax = geoCJAX::getInstance();
        $item_status = $cjax->value('item_status_val##');

        $admin->v()->set_status_link = $cjax->call('AJAX.php?controller=Item&action=changeItemStatus&refresh_after_delete=1&item_id=##&send_email=1&item_status=' . $item_status);
        $cjax->link = false;
        $admin->v()->apply_url = $cjax->form('AJAX.php?controller=Item&action=submit_values', 'items_parent');

        $admin->v()->legacy = $this->getUnapprovedLegacyListings();

        //pagination
        $max = ($count) ? ceil($count / 20) : 1;
        if ($start > $max) {
            //fix for when they try to view a page larger than they should,
            //it will still display no results but the links will be valid.
            $start = $max;
        }

        $link .= "&amp;sortBy={$sortBy}&amp;sortOrder={$sortOrder}&amp;current_page=";

        if ($max > 1) {
            $admin->v()->pagination = geoPagination::getHTML($max, ($start + 1), $link, '', '', false, false);
        }

        $admin->v()->addTop($cjax->init());
        return true;
    }

    public function update_orders_list_items()
    {
        $approve = $_POST['approve'];
        $delete = $_POST['delete'];

        $db = DataAccess::getInstance();
        $approveThese = $deleteThese = array();
        if (count($approve) > 0) {
            $approveThese = array_keys($approve, 1);
            $approveIn = implode(', ', $approveThese);
            //Note:  customer_approved will not be in newer installed sites, but in those
            //sites they would never reach this part of code.
            $sql = "UPDATE `geodesic_classifieds` SET `date` = '" . geoUtil::time() . "', `live` = 1, `customer_approved`=0, `renewal_payment_expected`=0 WHERE `id` IN (" . $approveIn . ")";
            $result = $db->Execute($sql);
            if (!$result) {
                trigger_error('DEBUG SQL: Query Fail. SQL: ' . $sql . ' || Error message: ' . $db->ErrorMsg());
                return false;
            }
        }
        if (count($delete) > 0) {
            $deleteThese = array_keys($delete, 1);
            $deleteIn = implode(', ', $deleteThese);
            //Note:  customer_approved will not be in newer installed sites, but in those
            //sites they would never reach this part of code.
            $sql = "UPDATE `geodesic_classifieds` SET `live` = 0, `customer_approved`=0, `renewal_payment_expected`=0 WHERE `id` IN (" . $deleteIn . ")";
            $result = $db->Execute($sql);
            if (!$result) {
                trigger_error('DEBUG SQL: Query Fail. SQL: ' . $sql . ' || Error message: ' . $db->ErrorMsg());
                return false;
            }
        }
        return true;
    }

    /**
    * gets HTML to show old listings that were unapproved before upgrading to 4.0
    * @return string HTML for fieldset/form
    *
    */
    public function getUnapprovedLegacyListings()
    {
        $db = DataAccess::getInstance();

        if (!$db->tableColumnExists(geoTables::classifieds_table, 'customer_approved')) {
            //newer installation, won't have old columns and won't have legacy listings
            return '';
        }

        $sql = "SELECT * FROM `geodesic_classifieds`
			WHERE ((`live` = 0 and `ends` > " . geoUtil::time() . " and `customer_approved` = 1) or `renewal_payment_expected` != 0 or `live` = 2)
			AND (`order_item_id` = 0) limit 50";
        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error('DEBUG SQL: Query fail. MySQL said: ' . $db->ErrorMsg());
            return '';
        }
        if ($result->RecordCount() == 0) {
            //no unapproved legacy listings
            return '';
        }
        while ($line = $result->FetchRow()) {
            $legacy[] = $line;
        }

        return $legacy;
    }

    public function display_orders_list_items_item_unlock()
    {
        $admin = geoAdmin::getInstance();

        $item_id = intval($_GET['item_id']);
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item)) {
            $admin->userError('Item not valid.');
            $admin->v()->addBody($admin->message());
            return;
        }

        $listing_id = $item->get('listing_id');
        if (!is_numeric($listing_id)) {
            $admin->userError('No listing attached to this item');
            $admin->v()->addBody($admin->message());
            return;
        }

        geoListing::getListing($listing_id)->setLocked(false);

        $admin->message("Forced Unlock on Listing #" . $listing_id, geoAdmin::SUCCESS);

        $this->display_orders_list_items_item_details();
    }

    public function display_orders_list_items_item_details()
    {
        $admin = geoAdmin::getInstance();
        $item_id = intval($_GET['item_id']);
        if (!$item_id) {
            $admin->userError('Item not specified.');
            $admin->v()->addBody($admin->message());
            return;
        }

        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item)) {
            $admin->userError('Item not valid.');
            $admin->v()->addBody($admin->message());
            return;
        }
        $db = DataAccess::getInstance();
        $cjax = geoCJAX::getInstance();

        $admin->v()->itemDetails = geoOrderItem::callDisplay('adminItemDisplay', $item_id);
        $itemVars = array();
        $itemVars['id'] = $item_id;
        $itemOrder = $item->getOrder();
        $itemVars['order_id'] = ($itemOrder) ? $itemOrder->getId() : 0;
        $itemVars['orderStatus'] = ($itemOrder) ? $itemOrder->getStatus() : 0;
        $itemVars['date'] = $item->getCreated();

        //get the username
        if ($itemOrder) {
            $sql = "SELECT `username` FROM " . geoTables::userdata_table . " WHERE `id`=? LIMIT 1";
            $user_info = $db->GetRow($sql, array($item->getOrder()->getBuyer()));

            $itemVars['username'] = $user_info['username'];
            $itemVars['user_id'] = $item->getOrder()->getBuyer();
        } else {
            $itemVars['username'] = 'unknown (not enough data)';
            $itemVars['user_id'] = 0;
        }
        if (method_exists($item, 'getTypeTitle')) {
            $title = $item->getTypeTitle();
        } else {
            $title = ucwords(str_replace('_', ' ', $item->getType()));
        }
        $itemVars['type'] = $title;
        $itemVars['status'] = $item->getStatus();
        $item_status = $cjax->value('item_status_val' . $item_id);
        $send_email = $cjax->value('send_email');

        $itemVars['set_status_link'] = geoHTML::addButton('Apply', $cjax->call('AJAX.php?controller=Item&action=changeItemStatus&item_id=' . $item_id . '&item_status=' . $item_status . '&send_email=' . $send_email), 1);
        $planItem = geoPlanItem::getPlanItem($item->getType(), $item->getPricePlan(), $item->getCategory());

        $pricePlanId = $planItem->getPricePlan();
        $row = $db->GetRow("SELECT `name` FROM " . geoTables::price_plans_table . " WHERE `price_plan_id`=?", array($pricePlanId));

        $itemVars['pricePlan'] = $row['name'];
        $itemVars['pricePlanUrl'] = "index.php?mc=pricing&amp;page=pricing_edit_plans&amp;f=3&amp;g={$pricePlanId}#price_plan_items";

        if ($planItem->getCategory()) {
            //category is set
            $cat = $planItem->getCategory();
            //find the cat price plan ID
            $row = $db->GetRow("SELECT p.`category_price_plan_id`, c.`category_name` FROM " . geoTables::price_plans_categories_table . " p, " . geoTables::categories_table . " c WHERE p.`price_plan_id` = ? AND p.`category_id` = ? AND c.`category_id` = ?", array ($pricePlanId, $cat, $cat));
            $catId = $row['category_price_plan_id'];
            if ($catId) {
                //index.php?mc=pricing&page=pricing_category_costs&d=196&e=1&x=1&y=8
                $itemVars['pricePlanUrl'] = "index.php?mc=pricing&amp;page=pricing_category_costs&amp;d={$cat}&amp;e=1&amp;x={$pricePlanId}&amp;y={$catId}#price_plan_items";
                $itemVars['categoryId'] = $planItem->getCategory();
                $itemVars['pricePlan'] .= " &gt; Category Pricing > {$row['category_name']} ({$catId})";
            }
        }


        $admin->v()->item = $itemVars;

        $admin->v()->adminMessages = $admin->message();

        $admin->setBodyTpl('orders/display_item.tpl');
        $admin->v()->addTop($cjax->init());
        //$admin->v()->addBody('Item details for item: <pre>'.print_r($item,1).'</pre>');
    }

    /**
     * Gets all items that match given criteria.
     *
     * @param string $status
     * @param int $item_type
     * @param int $start
     * @param int $num_results
     * @return array
     */
    public function _getItems($status = null, $item_type = null, $date_range = array(), $username = '', $sortBy = 'oi.id', $sortOrder = 'up', $start = 0, $num_results = 20, $results = null)
    {
        $db = DataAccess::getInstance();

        $start = intval($start);
        $num_results = intval($num_results);

        $start = $start * $num_results;
        if ($results === null) {
            $query_data = array();
            $whereClauses = array();
            if ($status === null || !in_array($status, self::$_validStatusItem)) {
                $status = 'pending';
            }
            if ($status != 'all') {
                $whereClauses [] = "oi.status = '$status'";
            }

            if (strlen($item_type) > 0 && $item_type != 'all') {
                $whereClauses[] = "oi.type = ?";
                $query_data[] = trim($item_type);
            } else {
                //get list of valid order item types, so it doesn't pull types
                //that do not typically show info in admin panel and mess up the
                //page count
                $types = geoOrderItem::getOrderItemTypes();
                $typesUse = array ();
                foreach ($types as $type => $typeInfo) {
                    if (method_exists($typeInfo['class_name'], 'adminDetails')) {
                        $typesUse[] = '?';
                        $query_data[] = $type;
                    }
                }
                $whereClauses[] = "oi.type IN (" . implode(', ', $typesUse) . ")";
            }

            if (isset($date_range['low']) && $date_range['low'] > 0) {
                $whereClauses[] = "o.created >= " . (int)$date_range['low'];
            }
            if (isset($date_range['high']) && $date_range['high'] > 0) {
                $whereClauses[] = "o.created <= " . (int)$date_range['high'];
            }

            if (strlen($username) > 0) {
                $whereClauses[] = "u.username=?";
                $query_data[] = $username;
            }

            $whereClauses [] = "oi.`parent` = 0";//only order items that are main order items
            $whereClauses [] = "o.id = oi.`order`";
            if ($item_type !== 'auction_final_fees') {
                //SPECIAL CASE: show auction final fees even if the attached order is pending,
                //otherwise there is no way for admin to find "outstanding" final fees in the system
                $whereClauses [] = "o.status = 'active'";//only order items on active orders
            }
            $whereClauses [] = "u.id = o.buyer";//user data specific for buyer on order
            $whereClauses = implode(' AND ', $whereClauses);
            $query_data = (count($query_data) > 0) ? $query_data : false;

            $sortBy = (in_array($sortBy, self::$_validSortBy)) ? $sortBy : 'oi.id';
            $orderBy = "ORDER BY $sortBy " . (($sortOrder == 'up') ? 'ASC' : 'DESC');

            $sql = "SELECT oi.id, u.username, u.id as user_id, o.created, o.status as order_status FROM " . geoTables::order_item . " as oi, " . geoTables::logins_table . " as u, " . geoTables::order . " as o
				WHERE
				$whereClauses
			GROUP BY oi.id
			$orderBy
			LIMIT $start, $num_results";

            $r = $db->GetAll($sql, $query_data);
            if ($r === false) {
                trigger_error("DEBUG SQL: " . $db->ErrorMsg());
            }

            $data = array();

            //figure out count
            $sql = "SELECT count(oi.id) as count
			FROM " . geoTables::order_item . " as oi, " . geoTables::logins_table . " as u, " . geoTables::order . " as o
			WHERE $whereClauses";
            $count = $db->GetRow($sql, $query_data);
            if (isset($count['count'])) {
                $data['count'] = $count['count'];
            }
        } else {
            $r = $results;
        }

        foreach ($r as $row) {
            $item = geoOrderItem::getOrderItem($row['id']);
            //echo '<pre>';var_dump($item); echo '</pre>';

            if (!is_object($item)) {
                continue;
            }

            if (method_exists($item, 'adminDetails')) {
                $item_data = $item->adminDetails();
            } else {
                continue;
            }
            if ($results && $item->getOrder()) {
                $order = $item->getOrder();
                $row['user_id'] = $order->getBuyer();
                $row['username'] = geoUser::userName($row['user_id']);
            }
            $data[$row['id']]['id'] = $item->getId();
            $data[$row['id']]['type'] = (isset($item_data['type'])) ? $item_data['type'] : $item->getType();
            $data[$row['id']]['title'] = $item_data['title'];
            $data[$row['id']]['status'] = $item->getStatus();
            $data[$row['id']]['order_status'] = $row['order_status'];
            $data[$row['id']]['user_id'] = $row['user_id'];
            $data[$row['id']]['username'] = $row['username'];
            $data[$row['id']]['date'] = $item->getCreated();
        }
        return $data;
    }
}
