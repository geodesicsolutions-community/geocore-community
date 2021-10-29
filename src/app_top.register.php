<?php
//app_top.register.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
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