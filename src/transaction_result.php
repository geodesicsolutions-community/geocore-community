<?php

//transaction_result.php
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
##
##    16.09.0-4-g881ff72
##
##################################

/* This is a way to show the success or failure page for a given transaction without affecting anything else
    it only allows showing transactions belonging to the current user
    and should be used for display ONLY

    mainly, this is used for some rare payment gateways like Paypal Advanced that like to put things in an iframe and leave them there,
        as a way to break out of that and show the results full-screen
*/


require_once 'app_top.main.php';

class geoFakeGateway extends geoPaymentGateway
{
    //just a cheap way to get access to geoPaymentGateway's protected function
    public static function show($success, $status, $render, $invoice, $transaction)
    {
        parent::_successFailurePage($success, $status, $render, $invoice, $transaction);
    }
}

$session = geoSession::getInstance();
$session->initSession();

$transaction_id = (int)$_GET['transaction'];
if (!$transaction_id || !$session->getUserId()) {
    //no transaction id given, or user isn't logged in. nothing to show.
    require GEO_BASE_DIR . 'app_bottom.php';
    die('INVALID');
}

$transaction = geoTransaction::getTransaction($transaction_id);
$invoice = $transaction->getInvoice();
if (!$invoice) {
    //no invoice associated with this transaction. nothing to do here.
    require GEO_BASE_DIR . 'app_bottom.php';
    die('INVALID');
}
$order = $invoice->getOrder();
if (!$order) {
    //no order associated with this transaction. nothing to do here.
    require GEO_BASE_DIR . 'app_bottom.php';
    die('INVALID');
}
$buyer = (int)$order->getBuyer();
if ($session->getUserId() != 1 && $session->getUserId() != $buyer) {
    //this isn't your transaction. go away.
    require GEO_BASE_DIR . 'app_bottom.php';
    die('INVALID');
}

$success = $transaction->getStatus() == 1 ? true : false;
$status = $order->getStatus();
geoFakeGateway::show($success, $status, true, ($success) ? $invoice : null, $transaction);
require_once GEO_BASE_DIR . 'app_bottom.php';
