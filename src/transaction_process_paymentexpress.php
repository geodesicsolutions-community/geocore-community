<?php

//transaction_process.php


/*
 * PaymentExpress can't handle parameters (like ?gateway=paymentexpress) in the return URL.
 * As a workaround, we point returns from PaymentExpress to this file, which simply sets $_GET['gateway'] and
 * includes the main transaction_process file
 *
 */

$_GET['gateway'] = 'paymentexpress';
require_once 'transaction_process.php';
