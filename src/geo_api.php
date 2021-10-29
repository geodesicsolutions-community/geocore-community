<?php

//geo_api.php
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

require_once 'app_top.common.php';
require_once CLASSES_DIR . PHP5_DIR . 'API.class.php';

//initialize API server.
$api = new geoAPI();
//Start the server, it does the rest.
$api->serve();
