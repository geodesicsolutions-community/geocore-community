<?php

//manual_payment.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    7.4.2-2-g07b2c84
##
##################################

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

# Manual CC payment gateway handler

class manual_paymentPaymentGateway extends _ccPaymentGateway
{

    var $name = 'manual_payment';//make it so that name is known.
    const gateway_name = 'manual_payment';
    public $type = 'cc';

    /**
     * Expects to return an array:
     * array (
     *  '' => ''
     * )
     *
     */
    function admin_display_payment_gateways()
    {
        $return = array (
            'name' => $this->name,
            'title' => 'CC - Manual Payment',
        );

        return $return;
    }

    public static function geoCart_payment_choicesDisplay($gateway = null)
    {
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
        //everybody uses the cvv2 nowadays...may as well just force it ;)
        $gateway->set('use_cvv2', 1);
        $gateway->save();
        return parent::geoCart_payment_choicesDisplay($gateway);
    }

    public static function geoCart_payment_choicesCheckVars($gateway = null, $skip_checks = null)
    {
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
        return parent::geoCart_payment_choicesCheckVars($gateway);
    }

    public function getCcNumber($transaction = false)
    {
        if (!$transaction) {
            return false;
        }
        return self::_getCcNumber($transaction);
    }

    /**
     * Optional.
     * Used: in geoCart::payment_choicesProcess()
     *
     * This function is where any processing is done, and is also where things like re-directing to an external
     * payment site would be done, or updating account balance, etc.
     *
     * Note that this is only called if this payment gateway is the one that was chosen, and there were no errors
     * generated by geoCart_payment_choicesCheckVars().
     *
     * This is where you would create a transaction that would pay for the order, into the invoice.
     *
     */

    public static function geoCart_payment_choicesProcess()
    {
        //get the cart
        $cart = geoCart::getInstance();

        //get the gateway since this is a static function
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);

        //get invoice on the order
        $invoice = $cart->order->getInvoice();
        $invoice_total = $invoice->getInvoiceTotal();

        if ($invoice_total >= 0) {
            //DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
            return ;
        }
        //BUILD DATA TO SEND TO GATEWAY TO COMPLETE THE TRANSACTION
        $info = parent::_getInfo();

        //create initial transaction
        try {
            //let parent create a new transaction, since it does all that common stuff for us.
            //(including encrypting CC data)
            $transaction = self::_createNewTransaction($cart->order, $gateway, $info, false, true);

            //Add the transaction to the invoice
            $transaction->setInvoice($invoice);
            $invoice->addTransaction($transaction);

            //save it so there is an id
            $transaction->save();
        } catch (Exception $e) {
            //catch any error thrown by _createNewTransaction
            trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: Exception thrown when attempting to create new transaction.');
            return;
        }


        $cart->order->processStatusChange('pending_admin');
    }



    /**
     * Optional.
     * Used: in geoCart::process_orderDisplay()
     *
     * This is a good place to do things like display a message that the listing has been placed on hold until
     * payment is received, or place to display other similar messages.
     *
     * Note that there is no process_orderCheckVars() or process_orderProcess() since this page is only meant
     * for display purposes, for any processing that needs to be done, needs to go in geoCart::payment_choicesProcess()
     *
     */
    public static function geoCart_process_orderDisplay()
    {
        $cart = geoCart::getInstance();

        self::_successFailurePage(true, $cart->order->getStatus(), true, $cart->order->getInvoice());

        //send email to admin if he wants it
        if ($cart->db->get_site_setting('user_set_hold_email')) {
            //echo $item_sell_class->db->get_site_setting('user_set_hold_email')." is the setting for hold email<br />";
            //echo "email should be sent for ad on hold<br />";
            $subject = "An order has been placed!!";
            $message = "Admin,\n\n";
            $message .= "An order has been placed and is on hold because a " . self::gateway_name . " type was chosen. See the unapproved orders section of the admin.\n\n";
            $message .= "Additional orders may be in the unapproved ads section that you were not sent an email. These will be failed auto pay attempts or if you are approving all ads.\n\n";
            $cart->db->sendMail($cart->db->get_site_setting('site_email'), $subject, $message);
        }

        //gateway is last thing to be called, so it needs to be the one that clears the session...
        $cart->removeSession();
    }

    /**
     * Used in the admin to show the cc number attached to a given order
     *
     */
    public static function admin_show_cc_number($order)
    {
        $transaction = self::_getCcTransactionFromOrder($order);

        $ccNum = self::_getCcNumber($transaction);
        $return = array();
        $return['cc_number'] = $ccNum;
        $return['exp_date'] = $transaction->get('exp_date');
        $return['cvv2_code'] = $transaction->get('cvv2_code');
        return $return;
    }

    public static function admin_clear_cc_number($order)
    {
        $transaction = self::_getCcTransactionFromOrder($order);
        $transaction->set('card_num', '');
        $transaction->set('decryption_key', '');
        $transaction->set('exp_date', '');
        $transaction->set('cvv2_code', '');
        $transaction->save();
    }

    private static function _getCcTransactionFromOrder($order)
    {
        if (!defined('IN_ADMIN')) {
            return false;
        }

        if (!geoSession::isSSL()) {
            //sanity check -- done in the caller, too, but just to make sure...
            return false;
        }

        $invoice = $order->getInvoice(); // get invoice for this order
        $allTransactions = $invoice->getTransaction(); //get all transactions on this invoice
        foreach ($allTransactions as $t) {
            //find the transaction for manual_payment
            if ($t->getGateway()->getName() == self::gateway_name) {
                $transaction = $t;
                break;
            }
        }

        if (!is_object($transaction)) {
            trigger_error('ERROR TRANSACTION: did not find the transaction we were looking for');
            return false;
        }
        return $transaction;
    }
}
