<?php

//geo_api.php


require_once 'app_top.common.php';
require_once CLASSES_DIR . PHP5_DIR . 'API.class.php';

//initialize API server.
$api = new geoAPI();
//Start the server, it does the rest.
$api->serve();
