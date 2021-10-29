<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    6.0.7-2-gc953682
##
##################################

//Admin bootstrap file

require_once('app_top.admin.php');

//see if they want to log out
if ($session->getUserId() && isset($_POST['page_action']) && $_POST['page_action'] == 'logout') {
    $session->logOut();
    $auth->admin_login_form();

    return false;
}

//let the geoAdmin do the work
geoAdmin::getInstance()->load_page();

require GEO_BASE_DIR . 'app_bottom.php';
