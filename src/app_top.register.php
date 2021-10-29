<?php
//app_top.register.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

//let rest of app know it's doing something with registration...
define ('IN_REGISTRATION', 1);

require_once "app_top.main.php";
require_once (CLASSES_DIR."register_class.php");

//error_reporting(E_ERROR | E_WARNING | E_PARSE);
//error_reporting (E_ALL);

$register = Singleton::getInstance('Register');

$register->language_id = $language_id;