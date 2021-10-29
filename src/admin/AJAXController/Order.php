<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-79-gb63e5d8
##
##################################

// DON'T FORGET THIS
if (class_exists('admin_AJAX') or die()) {
}

class ADMIN_AJAXController_Order extends admin_AJAX
{
    function takeaction()
    {
        $CJAX = geoCJAX::getInstance();
        $order_options = $CJAX->get('order_options');
        $order_id = $order_options['order_id'];
        if ($order_id) {
            $this->changeOrderStatus($order_id, $order_options['status'], $order_options['apply_to_all'], $order_options['email_notifications']);
        }


        $admin = geoAdmin::getInstance();
        $admin->message("Settings saved&nbsp;&nbsp;");
        $CJAX->message($admin->message(null));
    }

    function orderSave()
    {
        $order_id = CJAX::get('order_id');
        $status = CJAX::get('status');

        if (!$order_id || !$status) {
            return false;
        }

        $this->changeOrderStatus($order_id, $status);
    }

    function submit_values()
    {
        $CJAX = geoCJAX::getInstance();

        $orders = $CJAX->get('batch_order');

        $status = $CJAX->get('batch_status');

        if ($status == '--Choose--') {
            $CJAX->alert('Please choose a status.');
            return;
        }

        if (!is_array($orders) || !in_array(1, $orders)) {
            $CJAX->alert('No orders selected, please select at least one order.');
            return;
        }
        foreach ($orders as $order_id => $selected) {
            if ($selected) {
                $this->changeOrderStatus($order_id, $status);
            }
        }
    }

    public function changeOrderStatus($order_id = null, $status = null, $pushToItems = true, $sendEmailNotices = true)
    {
        $CJAX = geoCJAX::getInstance();
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        if (is_array($order_id)) {
            $order_id = (int)$_GET['order_id'];
        }
        $order = $this->_getOrder($order_id);

        if (!is_object($order)) {
            return false;
        }
        if ($status === null) {
            $status = trim($_GET['order_status']);
        }

        if (strlen($status) == 0) {
            $admin->userError('Invalid status specified!');
            $CJAX->message($admin->getUserMessages(), 5);
            return false;
        }
        if ($status == 'delete') {
            //special case, delete it!
            geoOrder::remove($order_id);
            $admin->userSuccess('Order ' . $order_id . ' was permanentely deleted.');
            $CJAX->message($admin->getUserMessages());
            $CJAX->wait(5);
            if ($_GET['refresh_after_delete']) {
                $CJAX->load_function('geoUtil.refreshPage');
            } else {
                $CJAX->location('?page=orders_list');
            }
            return;
        }
        //make sure the invoice totals up
        $invoice = $order->getInvoice();
        if ($status == 'active' && $invoice && $invoice->getInvoiceTotal() < 0) {
            //If changing to active, and currently the invoice is negative, do what it takes to
            //total out the invoice:
            //Create a new transaction that totals out everything, or use
            // an exising transaction that is currently inactive and set it to
            // active, if one exists.
            $transactions = $invoice->getTransaction();
            $latest_trans = 0;
            $trans_balance_amount = (-1 * $invoice->getInvoiceTotal());
            foreach ($transactions as $transaction) {
                if (is_object($transaction) && !$transaction->getStatus() && $transaction->getAmount() == $trans_balance_amount) {
                    //this transaction is not active, and the amount would total out the invoice to be 0
                    if (!is_object($latest_trans) || $transaction->getId() > $latest_trans->getId()) {
                        //This is the latest transaction created that meet the criteria
                        $latest_trans = $transaction;
                    }
                }
            }
            $transaction = null;
            if (is_object($latest_trans)) {
                $transaction = $latest_trans;
                $transaction->setStatus(1);//turn on
                $transaction->save();
            } else {
                //need to create a transaction
                $gateway = geoPaymentGateway::getPaymentGateway('site_fee');
                if (!is_object($gateway)) {
                    $admin->userError('Error: Could not get the gateway for site fees, not able to process.');
                    return false;
                }
                $transaction = new geoTransaction();
                $transaction->setGateway($gateway);
                $transaction->setInvoice($invoice);
                $transaction->setAmount($trans_balance_amount);
                $transaction->setDate(geoUtil::time());
                //TODO: make this text
                $transaction->setDescription('Order set to active by admin.');
                $transaction->setStatus(1);//turn on
                $transaction->setUser($order->getBuyer());
                $transaction->save();
                $invoice->addTransaction($transaction);
            }

            $due_amount = geoString::displayPrice($invoice->getInvoiceTotal());
            //due amount should be 0, so make it black and 0
            $due_display = "<span style='color: black'>{$due_amount}</span>";
            $CJAX->update('order_due_amount' . $order_id, $due_display);
        }
        if ($status === 'active') {
            //work around a case where the admin manually approves an order that still has an active Cart session (typically because it was abandoned as incomplete)
            //without this, the "expire carts" cron task will destroy the newly-approved order
            //so here, look for a Cart with this order ID, and kill it if it exists
            $cartId = DataAccess::getInstance()->getOne("SELECT `id` FROM " . geoTables::cart . " WHERE `order` = ?", array($order_id));
            if ($cartId) {
                geoCart::remove($cartId);
            }
        }
        $order->processStatusChange($status, $pushToItems, $sendEmailNotices);

        $pretty_status = $status;
        if ($pretty_status === 'pending') {
            $pretty_status = 'Pending Payment';
        } elseif ($pretty_status === 'pending_admin') {
            $pretty_status = 'Pending';
        } else {
            $pretty_status = ucwords($pretty_status);
        }
        $admin->userSuccess('Order status for order #' . $order_id . ' changed to ' . $pretty_status . '.');
        $CJAX->message($admin->getUserMessages(), 3);
        //Don't save the order till the end, in case something fails
        $order->save();

        $html = '
		<select name="order_status" id="order_status_val' . $order_id . '" class="form-control">
			<option value="active"' . (($status == "active") ? ' selected="selected"' : '') . '>Active' . (($status == "active") ? ' &#42;' : '') . '</option>
			<option value="pending"' . (($status == "pending") ? ' selected="selected"' : '') . '>Pending Payment' . (($status == "pending") ? ' &#42;' : '') . '</option>
			<option value="pending_admin"' . (($status == 'pending_admin') ? ' selected="selected"' : '') . '>Pending' . (($status == 'pending_admin') ? ' &#42;' : '') . '</option>
			<option value="incomplete"' . (($status == "incomplete") ? ' selected="selected"' : '') . '>Incomplete' . (($status == "incomplete") ? ' &#42;' : '') . '</option>
			<option value="canceled"' . (($status == "canceled") ? ' selected="selected"' : '') . '>Canceled' . (($status == "canceled") ? ' &#42;' : '') . '</option>
			<option value="suspended"' . (($status == "suspended") ? ' selected="selected"' : '') . '>Suspended' . (($status == "suspended") ? ' &#42;' : '') . '</option>
			<option value="fraud"' . (($status == "fraud") ? ' selected="selected"' : '') . '>Fraud' . (($status == "fraud") ? ' &#42;' : '') . '</option>
			<option disabled="disabled">---------</option>
			<option value="delete">Delete</option>
		</select>';

        $CJAX->update('order_status' . $order_id, $html);
    }

    /**
     * Makes sure the order id is valid, if it is it returns the order object for it.
     *
     * @param int $order_id
     * @return geoOrder|bool
     */
    private function _getOrder($order_id)
    {
        $CJAX = geoCJAX::getInstance();
        $admin = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $order_id = intval($order_id);

        if (!$order_id) {
            $admin->userError('Invalid order ID specified!');
            $CJAX->message($admin->getUserMessages(), 5);
            return false;
        }
        $order = geoOrder::getOrder($order_id);
        if (!is_object($order) || intval($order->getId()) !== $order_id) {
            $admin->userError('Invalid order ID specified! ' . $order_id . print_r($order, 1));
            $CJAX->message($admin->getUserMessages(), 5);
            return false;
        }
        return $order;
    }
}
