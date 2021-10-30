<?php

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Internal gateway, used for adding charge transactions.

class site_feePaymentGateway extends geoPaymentGateway
{
    var $name = 'site_fee';
    public $type = 'site_fee';
}
