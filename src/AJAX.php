<?php

require_once('classes/Ajax.class.php');

class classes_Ajax extends Ajax
{

    /**
     * @var string
     */
    var $directory = 'CLASSES';

    public function __construct($validate = false)
    {
        parent::__construct();
        if ($validate && !$this->isAuthorized()) {
            $this->notAuthorized(); // kills app with a user notice
        }
    }

    /**
     * checks to see if the client has a session
     *
     * @return boolean
     */
    function isAuthorized()
    {
        $session = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        if ($session->getUserId() !== 0) {
            return true;
        } else {
            return false;
        }
    }
}


if (!defined('GEO_BASE_DIR')) {
    set_error_handler('AJAXErrorHandler'); // set for app_top.ajax

    require_once('app_top.ajax.php');

    set_error_handler('AJAXErrorHandler'); // reset after app_top.common

    $controller = $_REQUEST['controller'];
    $action = $_REQUEST['action'];
    $validate = isset($_REQUEST['validate']) ? true : false;
    unset($_REQUEST['controller'], $_REQUEST['action'], $_REQUEST['validate']);

    $ajax = new classes_Ajax($validate);
    $ajax->dispatch($controller, $action, $_REQUEST);
}
