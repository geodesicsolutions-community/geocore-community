<?php

//transaction_process.php
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
## ##    6.0.7-2-gc953682
##
##################################

//Make sure that clean inputs only un-does magic quotes
define('CLEAN_INPUTS_MAGIC_ONLY', 1);

require 'app_top.common.php';

trigger_error('DEBUG TRANSACTION: transaction_process.php - top');

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

trigger_error('DEBUG TRANSACTION: transaction_process.php - after require');

$gateway_name = ((isset($_GET['gateway']) && strlen($_GET['gateway']) > 0)) ? trim($_GET['gateway']) : '';

//because 2checkout is weird and does things wrong.
$gateway_name = (!$gateway_name && (isset($_POST['gateway']) && strlen($_POST['gateway']) > 0)) ? trim($_POST['gateway']) : $gateway_name;

trigger_error('DEBUG TRANSACTION: transaction_process.php - gateway_name: ' . $gateway_name);

if (strlen(trim($gateway_name)) == 0) {
    trigger_error('ERROR TRANSACTION: transaction_process.php - no gateway specified!  $_GET=' . print_r($_GET, 1) . "\n\n\$_POST=" . print_r($_POST, 1));
    include GEO_BASE_DIR . 'app_bottom.php';
    exit;
}

$gateway = geoPaymentGateway::getPaymentGateway($gateway_name);

trigger_error('DEBUG TRANSACTION: transaction_process.php - gateway: ' . print_r($gateway, 1));


if (!is_object($gateway)) {
    trigger_error('ERROR TRANSACTION: transaction_process.php - gateway not object!');
    include GEO_BASE_DIR . 'app_bottom.php';
    exit;
}


//let the gateway do it's thing.
if (method_exists($gateway, 'transaction_process') || method_exists($gateway, '__call')) {
    $gateway->transaction_process();
}
include GEO_BASE_DIR . 'app_bottom.php';
