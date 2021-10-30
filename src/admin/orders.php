<?php

class OrdersManagement
{
    public function display_orders_list()
    {
        $admin = geoAdmin::getInstance();

        $status = (isset($_GET['narrow_order_status'])) ? $_GET['narrow_order_status'] : 'all';
        $gateway_type = (isset($_GET['narrow_gateway_type'])) ? $_GET['narrow_gateway_type'] : 'all';
        $start = (isset($_GET['current_page'])) ? (int)$_GET['current_page'] - 1 : 0;
        $start = max($start, 0);

        $gateways = geoPaymentGateway::getPaymentGatewayOfType('all');
        $types = array('all' => 'Any Gateway');
        foreach ($gateways as $name => $gateway) {
            if ($gateway->getType() != 'site_fee') {
                $types[$name] = $gateway->getTitle();
            }
        }

        $validSortBy = array (
            'order_id','username','created','gateway_type','status', 'invoice_id',
            'admin',
        );

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
        $sortBy = (in_array($_GET['sortBy'], $validSortBy)) ? $_GET['sortBy'] : 'order_id';
        $sortOrder = (in_array($_GET['sortOrder'], array ('up','down'))) ? $_GET['sortOrder'] : 'down';

        $narrow_username = (isset($_GET['narrow_username']) && strlen(trim($_GET['narrow_username']))) ? trim($_GET['narrow_username']) : '';

        $narrow_admin = (isset($_GET['narrow_admin']) && strlen(trim($_GET['narrow_admin']))) ? trim($_GET['narrow_admin']) : '';

        if (strlen($narrow_admin) && !is_numeric($narrow_admin)) {
            //change it into an ID
            $user = geoUser::getUser($narrow_admin);
            if ($user) {
                $narrow_admin = (int)$user->id;
            } else {
                geoAdmin::m('Note:  Admin username specified could not be found, so clearing admin creator filter selection.', geoAdmin::NOTICE);
                $narrow_admin = '';
            }
        }
        if (strlen($narrow_admin)) {
            $narrow_admin = (int)$narrow_admin;
        }

        $orders = $this->_getOrders($status, $gateway_type, $date, $narrow_username, $narrow_admin, $sortBy, $sortOrder, $start);
        $count = $orders['count'];
        unset($orders['count']);

        $admin->v()->orders = $orders;

        $admin->v()->types = $types;
        $admin->v()->narrow_order_status = $status;
        $admin->v()->narrow_gateway_type = $gateway_type;

        $admin->v()->ent = geoPC::is_ent();
        $link = $admin->v()->sort_link = "index.php?page=orders_list&amp;narrow_order_status=$status&amp;narrow_gateway_type=$gateway_type&amp;date[low]=" . htmlspecialchars($date_urls['low']) . "&amp;date[high]=" . htmlspecialchars($date_urls['high']) . "&amp;narrow_username=" . htmlspecialchars($narrow_username)
            . "&amp;narrow_admin={$narrow_admin}";
        $admin->v()->date = $date_urls;
        $admin->v()->narrow_username = $narrow_username;
        $admin->v()->narrow_admin = $narrow_admin;
        if (strlen($narrow_admin)) {
            if ($narrow_admin == 0) {
                $admin->v()->narrow_admin_text = 'N/A (Show Only User Created)';
            } else {
                $user = geoUser::getUser($narrow_admin);
                $admin_username = 'Unknown';
                if ($user) {
                    $admin_username = $user->username;
                }
                $admin->v()->narrow_admin_text = "$admin_username (#$narrow_admin)";
            }
        }
        $admin->v()->sortBy = $sortBy;
        $admin->v()->sortOrder = $sortOrder;
        $CJAX = geoCJAX::getInstance();

        $CJAX->link = true;
        $admin->v()->display_order_link = 'index.php?page=orders_list_order_details&order_id=##';//$CJAX->call('AJAX.php?controller=Order&action=displayOrder&order_id=##');

        //$admin->v()->display_order_link = $CJAX->call('AJAX.php?controller=Order&action=displayOrder&order_id=##');
        $CJAX->link = false;
        $admin->v()->approve_link = geoHTML::addButton('Approve', $CJAX->call('AJAX.php?controller=Order&action=changeOrderStatus&order_status=active&order_id=##'), 1);
        $admin->v()->invoice_link = 'AJAX.php?controller=Invoice&action=getInvoice&invoice_id=';


        $order_status = $CJAX->value('order_status_val##');
        $admin->v()->set_status_link = $CJAX->call('AJAX.php?controller=Order&action=changeOrderStatus&refresh_after_delete=1&order_id=##&order_status=' . $order_status);

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

        //$tpl->assign('apply_url', $CJAX->submit_checkboxes('AJAX.php?controller=Order&action=submit_values','orders_parent'));
        $admin->v()->apply_url = $CJAX->form('AJAX.php?controller=Order&action=submit_values&refresh_after_delete=1', 'orders_parent');
        $admin->v()->addTop($CJAX->init());
        $admin->setBodyTpl('orders/list_orders');
    }

    private static $_validStatusOrder = array (
        'all',
        'active',
        'pending',
        'pending_admin',
        'incomplete',
        'canceled',
        'suspended',
        'fraud',
    );

    /**
     * Gets all the orders according to criteria set
     *
     * @param string $status Narrow by what status (set to all to show all statuses)
     * @param string $gateway_type if specified, will narrow by specific gateway type
     * @param int $start the start of the result set
     * @param int $num_results The number of results to return.
     * @return an array with all orders matching criteria
     */
    private function _getOrders($status = null, $gateway_type = null, $date_range = array(), $username = '', $admin = '', $sortBy = 'order_id', $sortOrder = 'up', $start = 0, $num_results = 20)
    {
        $db = DataAccess::getInstance();

        $start = intval($start);
        $num_results = intval($num_results);

        $start = $start * $num_results;

        $query_data = array();
        $whereClauses = array();
        if ($status === null || !in_array($status, self::$_validStatusOrder)) {
            $status = 'pending';
        }
        if ($status != 'all') {
            $whereClauses [] = "o.status = '$status'";
        }

        if (strlen($gateway_type) > 0 && $gateway_type != 'all') {
            $whereClauses[] = "o_r.val_string = ?";
            $query_data[] = trim($gateway_type);
        }
        $whereClauses[] = 'i.order = o.id';
        $whereClauses[] = 'o_r.order = o.id';
        $whereClauses[] = "o_r.`index_key` = 'payment_type'";
        $whereClauses[] = "u.id = o.buyer";
        $whereClauses[] = "o.seller = 0";

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

        if (strlen($admin) > 0) {
            $whereClauses[] = "o.admin=?";
            $query_data[] = (int)$admin;
        }

        $whereClauses = implode(' AND ', $whereClauses);
        $query_data = (count($query_data) > 0) ? $query_data : false;

        $orderBy = "ORDER BY $sortBy " . (($sortOrder == 'up') ? 'ASC' : 'DESC');

        $sql = "SELECT
		o.id as order_id, o.status, o.buyer, o.created, o.admin,
		i.id AS invoice_id, u.id as user_id,
		u.username,
		o_r.val_string as gateway_type
		FROM " . geoTables::order . " AS o," . geoTables::invoice . " AS i,
			" . geoTables::order_registry . " as o_r, " . geoTables::logins_table . " as u
		WHERE $whereClauses
		GROUP BY o.id DESC
		$orderBy
		LIMIT $start, $num_results";

        //echo $sql.'<br /><br />';
        $r = $db->GetAll($sql, $query_data);
        if ($r === false) {
            geoView::getInstance()->addBody('<div style="color:red; white-space:pre;">error: ' . $sql . "\n\n" . print_r($query_data) . "\n\n" . $db->ErrorMsg() . '</div><br /><br />');
        }
        $data = array();

        //figure out count
        $sql = "SELECT count(o.id) as count
		FROM {$db->geoTables->order} AS o," . geoTables::invoice . " AS i,
			" . geoTables::order_registry . " as o_r, " . geoTables::logins_table . " as u
		WHERE $whereClauses";
        $count = $db->GetRow($sql, $query_data);
        if (isset($count['count'])) {
            $data['count'] = $count['count'];
        }

        foreach ($r as $row) {
            $data[$row['order_id']] = $row;


            //get order amount
            $order = geoOrder::getOrder($row['order_id']);
            if (is_object($order)) {
                $data[$row['order_id']]['order_total'] = $order->getOrderTotal();
            }

            //get invoice
            $invoice = geoInvoice::getInvoice($row['invoice_id']);
            if (is_object($invoice)) {
                //set amount due
                $data[$row['order_id']]['due'] = -1 * $invoice->getInvoiceTotal();
                //get transactions attached to invoice
                $transactions = $invoice->getTransaction();
                //get latest transaction
                if (is_array($transactions)) {
                    $trans = array_pop($transactions);
                    if (is_object($trans)) {
                        $data[$row['order_id']]['description'] = $trans->getDescription();
                    }
                }
            }
            //get gateway's display name
            $gateway = geoPaymentGateway::getPaymentGateway($row['gateway_type']);
            if (is_object($gateway)) {
                $data[$row['order_id']]['gateway'] = $gateway->getTitle();
            }
            if (!$data[$row['order_id']]['gateway']) {
                if ($data[$row['order_id']]['order_total'] == 0) {
                    //free, so no gateway
                    $data[$row['order_id']]['gateway'] = "Free";
                } else {
                    //not free, but gateway not known?
                    $data[$row['order_id']]['gateway'] = "Unknown";
                }
            }
            if ($row['admin']) {
                $admin_user = geoUser::getUser($row['admin']);
                if ($admin_user) {
                    $data[$row['order_id']]['admin_username'] = $admin_user->username;
                }
            }
        }

        return $data;
    }

    /**
     * Get a specific order's details
     *
     * @param integer $order_id
     * @return Array with specific order details
     */
    private function _getOrderDetails($order_id)
    {
        //$transaction = $this->_getTransaction();
        $order_id = (int)$order_id;
        if (!$order_id) {
            trigger_error("ERROR ORDER: Attempting to use order ID of 0.");
            return false;
        }
        $order = geoOrder::getOrder($order_id);
        if (!$order || $order->getId() != $order_id) {
            trigger_error('ERROR ORDER: Could not find order for ID ' . $order_id);
            return false;
        }

        $return = array ();
        $return['order_id'] = $order->getId();
        $return['status'] = $order->getStatus();
        $return['buyer'] = $order->getBuyer();
        $return['admin'] = $order->getAdmin();
        if ($return['admin']) {
            $admin_user = geoUser::getUser($return['admin']);
            if ($admin_user) {
                $return['admin_username'] = $admin_user->username;
            }
        }
        $return['seller'] = $order->getSeller();
        $return['created'] = $order->getCreated();
        $invoice = $order->getInvoice();
        $return['invoice_id'] = ($invoice) ? $invoice->getId() : 0;
        $return['user_id'] = $order->getBuyer();
        if ($return['user_id']) {
            $return['username'] = geoUser::username($return['user_id']);
        }
        $return['gateway_type'] = $order->get('payment_type');

        if (is_object($invoice)) {
            $return['due'] = (-1 * $invoice->getInvoiceTotal());
        }
        $return['username'] = "<a href='?mc=users&page=users_view&b={$return['user_id']}'>{$return['username']}</a>";// ({$r['user_id']})";
        if ($return['user_id'] == 0) {
            $return['username'] = 'Anonymous';
        }
        $return['date'] = geoDate::toString($return['created']);
        $return['total'] = geoString::displayPrice($order->getOrderTotal());

        //if using manual gateway (and connected securely), show credit card number
        if ($return['gateway_type'] == 'manual_payment') {
            if ($_GET['clear_cc'] == 1) {
                //admin pushed the "clear cc number" button
                geoPaymentGateway::callUpdate('admin_clear_cc_number', $order, 'manual_payment');
            }

            if (geoSession::isSSL()) {
                $cc_data = geoPaymentGateway::callDisplay('admin_show_cc_number', $order, 'array', 'manual_payment');
                $manual_cc = $cc_data['manual_payment'];
                if (!$manual_cc) {
                    //failed to get cc data
                    $return['cc_number'] = 'Error! Could not retrieve CC number, or you are not connected via SSL.';
                } else {
                    $return['can_delete_cc'] = ($manual_cc['cc_number']) ? true : false;
                    $return['cc_number'] = ($manual_cc['cc_number']) ? $manual_cc['cc_number'] : 'Deleted';
                    $return['exp_date'] = $manual_cc['exp_date'];
                    $return['cvv2_code'] = $manual_cc['cvv2_code'];
                }
            } else {
                $return['cc_number'] = 'SSL connection required to view.';
            }
        } else {
            //non-manual gateway. no need to show CC number, if we even have it
            $return['cc_number'] = false;
        }

        //get gateway's display name
        $gateway = geoPaymentGateway::getPaymentGateway($return['gateway_type']);
        if (is_object($gateway)) {
            $return['gateway_type'] = $gateway->getTitle();
        }

        return $return;
    }

    public function display_orders_list_order_details()
    {
        $CJAX = geoCJAX::getInstance();
        $admin = geoAdmin::getInstance();
        $order_id = intval($_GET['order_id']);

        if (!$order_id) {
            $order_id = (int)$this->findOrderId();
        }

        $order = $this->_getOrderDetails($order_id);

        $admin->v()->attached_items = $this->getOrderItemInfo($order_id);
        $admin->v()->order = $order;
        $CJAX->link = true;
        $admin->v()->take_action = $CJAX->form("AJAX.php?controller=Order&action=takeaction&order_options[order_id]=$order_id", "frm_order_details");
        $CJAX->link = false;
        $admin->v()->invoice_link = 'AJAX.php?controller=Invoice&action=getInvoice&invoice_id=' . $order['invoice_id'];
        $admin->v()->ent = geoPC::is_ent();

        $admin->setBodyTpl('orders/display_order');
        $admin->v()->addTop($CJAX->init());
    }

    public function findOrderId($return = false)
    {
        $orderId = 0;
        if (isset($_POST['orderId']) && $_POST['orderId']) {
            $orderId = intval($_POST['orderId']);
        }
        $transactionId = 0;
        if (!$orderId && isset($_POST['invoiceId']) && strlen($_POST['invoiceId']) > 0) {
            //look up by invoice
            $invoiceId = $_POST['invoiceId'];
            if (is_numeric($invoiceId)) {
                $invoice = geoInvoice::getInvoice($invoiceId);
                if ($invoice) {
                    $order = $invoice->getOrder();
                    if ($order) {
                        $orderId = $order->getId();
                    }
                }
            } else {
                //see if it's really one of them generated transaction
                //ID's
                $transactionId = $invoiceId;
            }
        }
        if (!$orderId && isset($_POST['transactionId']) && $_POST['transactionId']) {
            $transactionId = $_POST['transactionId'];
        }

        if ($transactionId) {
            $transaction = geoTransaction::getTransaction($transactionId);
            if ($transaction && $transaction->getInvoice()) {
                $invoice = $transaction->getInvoice();
                $order = $invoice->getOrder();
                if ($order) {
                    $orderId = $order->getId();
                }
            }
        }

        if (!$return && $orderId) {
            //redirect to order page
            header('Location: index.php?page=orders_list_order_details&order_id=' . $orderId);
            require GEO_BASE_DIR . 'app_bottom.php';
            exit;
        }
        return $orderId;
    }

    public function getOrderItemInfo($orderId)
    {
        $items = geoOrder::getOrder($orderId)->getItem();
        $items_view = array();
        foreach ($items as $item) {
            $info = array();
            if (!$item) {
                continue;
            }
            if ($item->getCost() == 0 && !$item->displayInAdmin()) {
                //item does not cost anything, and item not
                //normally shown in admin
                continue;
            }

            if (is_callable(array($item, 'adminDetails'))) {
                $info = $item->adminDetails();
                if (!$info) {
                    //item doesn't want to be shown
                    continue;
                }
            } else {
                //get info based on name
                $info['title'] = $info['type'] = $item->getTypeTitle();
            }
            $info['displayInAdmin'] = $item->displayInAdmin();
            $info['status'] = $item->getStatus();
            $info['cost'] = $item->getCost();
            //now figure out where to put it
            if ($item->getParent()) {
                $items_view[$item->getParent()->getId()]['children'][$item->getId()] = $info;
            } else {
                $items_view[$item->getId()] = $info;
            }
        }

        return $items_view;
    }
}
