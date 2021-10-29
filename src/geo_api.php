<?php

//geo_api.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
##
##################################

require_once 'app_top.common.php';
require_once CLASSES_DIR . PHP5_DIR . 'API.class.php';

//initialize API server.
$api = new geoAPI();
//Start the server, it does the rest.
$api->serve();
