<?php

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

require_once('../classes/Ajax.class.php'); //must include the error handler from this file

class admin_Ajax extends geoAjax
{

    //ADMIN is special case, dir gets replaced by ADMIN_DIR to account for changed admin directory
    public $directory = 'ADMIN';

    /**
     * See whether or not the admin user is allowed to either display or update a given
     * page.
     *
     * @param string $page
     * @param string $access Either "display" or "update"
     * @return bool
     */
    protected function isAllowed($page, $access = 'display')
    {
        return geoAdmin::getInstance()->isAllowed($page, $access);
    }
    /**
     * Convienience function, to see whether the admin user is allowed to update
     * a particular page.
     *
     * @param string $page
     * @return bool
     */
    protected function canUpdate($page)
    {
        return $this->isAllowed($page, 'update');
    }

    /**
     * Convienience function, to see whether the admin user is allowed to display
     * a particular page.
     *
     * @param string $page
     * @return bool
     */
    protected function canDisplay($page)
    {
        return $this->isAllowed($page, 'display');
    }
}

if (!defined('IN_ADMIN')) {
    set_error_handler('AJAXErrorHandler'); //set for header calls

    header('Cache-Control: no-cache');
    header('Expires: -1');
    header('Pragma: no-cache');

    if (!defined('AJAX_REQUEST')) {
        define('AJAX_REQUEST', 1);
    }
    require_once('app_top.admin.php');
    require_once('../app_top.ajax.php');

    set_error_handler('AJAXErrorHandler'); //reset after app_top.common

    $controller = $_REQUEST['controller'];
    $action = $_REQUEST['action'];
    unset($_REQUEST['controller'], $_REQUEST['action']);

    $ajax = new admin_Ajax();
    $ajax->dispatch($controller, $action, $_REQUEST);
}
