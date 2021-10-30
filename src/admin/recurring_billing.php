<?php

//recurring_billing.php


class RecurringBillingManagement
{
    public function display_recurring_billing_list()
    {
        $admin = geoAdmin::getInstance();
        $view = $admin->v();

        $status = (isset($_GET['narrow_status'])) ? $_GET['narrow_status'] : 'active';
        $gateway_type = (isset($_GET['narrow_gateway_type'])) ? $_GET['narrow_gateway_type'] : 'all';
        $start = (isset($_GET['current_page'])) ? (int)$_GET['current_page'] - 1 : 0;
        $start = max($start, 0);

        $gateways = geoPaymentGateway::getPaymentGatewayOfType('all');
        $types = array('all' => 'Any Gateway');
        foreach ($gateways as $name => $gateway) {
            //temp change setting to be on, so that those gateways that are at all
            //capable of being recurring, are displayed.
            $recurring = $gateway->get('recurring');
            $gateway->set('recurring', 1);
            if ($gateway->isRecurring()) {
                if ($gateway->getType() != 'site_fee') {
                    $types[$name] = $gateway->getTitle();
                }
            }
            //restore setting
            $gateway->set('recurring', $recurring);
        }

        $recurringBillings = $this->_getRecurringBillings($status, $gateway_type, $start);
        $count = $recurringBillings['count'];
        unset($recurringBillings['count']);

        $view->recurringBillings = $recurringBillings;

        $view->types = $types;
        $view->narrow_order_status = $status;
        $view->narrow_gateway_type = $gateway_type;
        $this->_setLinks();

        //pagination
        $max = ($count) ? ceil($count / 20) : 1;
        if ($start > $max) {
            //fix for when they try to view a page larger than they should,
            //it will still display no results but the links will be valid.
            $start = $max;
        }

        $link = 'index.php?page=recurring_billing_list&amp;narrow_status=' . $status . '&amp;narrow_gateway_type=' . $gateway_type . '&amp;current_page=';

        if ($max > 1) {
            $admin->v()->pagination = geoPagination::getHTML($max, ($start + 1), $link, '', '', false, false);
        }

        $admin->setBodyTpl('orders/list_recurring_billings');
    }

    private static $_validStatusOrder = array (
        'all',
        'active',
        'canceled',
        'other',
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
    private function _getRecurringBillings($status = null, $gateway_type = null, $start = 0, $num_results = 20)
    {
        $db = DataAccess::getInstance();

        $start = intval($start);
        $num_results = intval($num_results);

        $start = $start * $num_results;

        $query_data = array();
        $whereClauses = array();
        if ($status === null || !in_array($status, self::$_validStatusOrder)) {
            $status = 'active';
        }
        if ($status == 'other') {
            $whereClauses [] = "r.status != 'active' AND r.status != 'canceled'";
        } elseif ($status != 'all') {
            $whereClauses [] = "r.status = '$status'";
        }

        if (strlen($gateway_type) > 0 && $gateway_type != 'all') {
            $whereClauses[] = "r.gateway = ?";
            $query_data[] = trim($gateway_type);
        }
        $whereClauses = implode(' AND ', $whereClauses);
        $where = ($whereClauses) ? "WHERE $whereClauses" : '';
        $query_data = (count($query_data) > 0) ? $query_data : false;
        $sql = "SELECT 
		r.*, 
		u.username
		FROM " . geoTables::recurring_billing . " AS r LEFT JOIN " . geoTables::logins_table . " as u
		ON r.user_id=u.id
		$where
		ORDER BY r.id DESC
		LIMIT $start, $num_results";

        //echo $sql.'<br /><br />';
        $r = $db->GetAll($sql, $query_data);
        if ($r === false) {
            geoView::getInstance()->addBody('<div style="color:red; white-space:pre;">error: ' . $sql . "\n\n" . print_r($query_data) . "\n\n" . $db->ErrorMsg() . '</div><br /><br />');
        }
        $data = array();

        //figure out count
        $sql = "SELECT count(r.id) as count
		FROM " . geoTables::recurring_billing . " AS r LEFT JOIN " . geoTables::logins_table . " as u
		ON r.user_id=u.id
		$where";
        $count = $db->GetRow($sql, $query_data);
        if (isset($count['count'])) {
            $data['count'] = $count['count'];
        }

        foreach ($r as $row) {
            //get gateway's display name
            $gateway = ($row['gateway']) ? geoPaymentGateway::getPaymentGateway($row['gateway']) : null;
            if (is_object($gateway)) {
                $row['gateway'] = $gateway->getTitle();
            }
            if (!$row['gateway']) {
                $row['gateway'] = "Unknown";
            }
            if ($row['cycle_duration']) {
                $row['cycle_duration'] = floor($row['cycle_duration'] / (60 * 60 * 24));
            }

            $row['canCancel'] = true;
            if ($row['order_id']) {
                //see if order is active
                $order = geoOrder::getOrder($row['order_id']);
                if ($order && $order->getStatus() != 'active') {
                    //order not active!
                    $row['status_extra'] = "<a href='index.php?page=orders_list_order_details&amp;order_id={$order->getId()}'>Order [#{$order->getId()}]</a> not active";
                    //cannot cancel if order's status is not active
                    $row['canCancel'] = false;
                }
            }

            $data[$row['id']] = $row;
        }

        return $data;
    }
    private function _setLinks()
    {
        $links = array();
        $links['nowork'] = "onclick=\"recurring.noWork();\" ";
        $links['user'] = "index.php?page=users_view&amp;b=";
        $links['order'] = "index.php?page=orders_list_order_details&amp;order_id=";
        $links['invoice'] = 'AJAX.php?controller=Invoice&amp;action=getInvoice&amp;invoice_id=';

        $links['refresh'] = 'onclick="recurring.statusRefresh(0);"';
        $links['cancel'] = 'onclick="recurring.statusCancel(0);"';

        $links['applySelected'] = 'onclick="recurring.applySelectedChanges();"';
        $links['applyUrl'] = 'AJAX.php?controller=Recurring&amp;action=batch';

        $links['recurring_details'] = "index.php?page=recurring_billing_details&amp;id=";

        geoView::getInstance()->links = $links;
    }
    public function display_recurring_billing_details()
    {
        $admin = geoAdmin::getInstance();
        $view = $admin->v();
        $id = intval($_GET['id']);
        if (!$id) {
            $id = $this->findId();
        }

        $recurring = ($id) ? geoRecurringBilling::getRecurringBilling($id) : false;

        $view->recurring = $recurring;
        if ($recurring) {
            $view->username = geoUser::userName($recurring->getUserId());
            $gateway = $recurring->getGateway();
            $view->gateway = (is_object($gateway)) ? $gateway->getTitle() : 'Unknown';
            if (is_object($gateway)) {
                //get details specific to gateway
                $gatewayType = $gateway->getType();
                $view->gatewayDetails = geoPaymentGateway::callDisplay('adminRecurringDisplay', $recurring, 'array', $gatewayType);
            }
            $view->days = floor($recurring->getCycleDuration() / (60 * 60 * 24));
            $order = $recurring->getOrder();
            $canCancel = true;
            if ($order) {
                if ($order->getStatus() != 'active') {
                    //order not active!
                    $view->altStatus = "<a href='index.php?page=orders_list_order_details&amp;order_id={$order->getId()}'>Order [#{$order->getId()}]</a> not active";
                    //cannot cancel if order's status is not active
                    $canCancel = false;
                } else {
                    //get more details to share about the order
                    $invoice = $order->getInvoice();
                    $view->invoice = ($invoice) ? $invoice->getId() : 0;
                }
            }
            $view->canCancel = $canCancel;
            $itemType = $recurring->getItemType();
            if ($itemType) {
                $this_item = geoOrderItem::getOrderItem($itemType);
                if ($this_item && $this_item->displayInAdmin()) {
                    if (method_exists($this_item, 'getTypeTitle')) {
                        $title = $this_item->getTypeTitle();
                    } else {
                        $title = ucwords(str_replace('_', ' ', $k));
                    }
                    $view->itemType = $title;
                }

                //Get details specific to each order item
                $view->typeDetails = geoOrderItem::callDisplay('adminRecurringDisplay', $recurring, 'array', $itemType);
            }
        }

        //get the recurring transactions
        $transactions = ($recurring) ? $recurring->getTransaction() : array();
        $tVals = array();
        foreach ($transactions as $transaction) {
            if (is_object($transaction)) {
                //$transaction = geoTransaction::getTransaction($transaction->getId());
                $id = $transaction->getId();
                if ($transaction->getStatus()) {
                    $class = ($transaction->getAmount() > 0) ? 'payment' : 'due';
                } else {
                    $class = 'pending';
                }
                $tVals[$id] = array (
                    'desc' => $transaction->getDescription(),
                    'date' => $transaction->getDate(),
                    'status' => $transaction->getStatus(),
                    'amount' => $transaction->getAmount(),
                    'amount_class' => $class
                );
            }
        }
        $view->transactions = $tVals;

        $this->_setLinks();

        $admin->setBodyTpl('orders/display_recurring');
    }

    public function findId($return = false)
    {
        $id = (isset($_POST['id']) && $_POST['id']) ? (int)$_POST['id'] : 0;
        $altId = (isset($_POST['altId']) && $_POST['altId']) ? trim($_POST['altId']) : '';

        if (!$id && !$altId) {
            return 0;
        }

        if ($altId && !$id) {
            //see if we can find the ID:
            $recurring = geoRecurringBilling::getRecurringBilling($altId);
            if ($recurring && $recurring->getId()) {
                $id = (int)$recurring->getId();
            }
        }

        if (!$return && $id) {
            //redirect to order page
            header('Location: index.php?page=recurring_billing_details&id=' . $id);
            require GEO_BASE_DIR . 'app_bottom.php';
            exit;
        }
        return $id;
    }
}
