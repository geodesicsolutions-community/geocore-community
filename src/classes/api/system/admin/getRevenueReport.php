<?php

//system/admin/getRevenueReport.php
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
## ##    7.3beta4-55-g4f44a5e
##
##################################

if (!defined('IN_GEO_API')) {
    //exit('No access.');
}
//This is a simple API function to call the admin's revenue report functionality. It will always return a CSV file to the caller when successful

$apiData = array(
        'start_date' => $args['start_date'], //YYYY-MM-DD (assumes (NOW - 30 days) if missing)
        'end_date' => $args['end_date'], //YYYY-MM-DD (assumes NOW if missing)
        'usergroups' => $args['usergroups'], //Array of IDs (assumes all groups if missing)
        'as_csv' => 1,
    );


require_once ADMIN_DIR . 'admin_site_class.php';
require_once ADMIN_DIR . 'admin_payment_management_class.php';
$pm = new Payment_management();
return $pm->display_payments_revenue_report($apiData);
