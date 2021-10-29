<?php

//site_fee.php
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

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Internal gateway, used for adding charge transactions.

class site_feePaymentGateway extends geoPaymentGateway
{
    var $name = 'site_fee';
    public $type = 'site_fee';
}
