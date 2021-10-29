<?php

//site_fee.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
##
##################################

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Internal gateway, used for adding charge transactions.

class site_feePaymentGateway extends geoPaymentGateway
{
    var $name = 'site_fee';
    public $type = 'site_fee';
}
