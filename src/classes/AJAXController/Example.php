<?php

/**
 * This file shows how to write server-side code for AJAX requests
 * Use this file as a template for new
 *
 * AJAX requests should be sent to admin/AJAX.php in the following form:
 *   admin/AJAX.php?controller=Example&action=foo&data=oof
 *
 * This request will do the following:
 *  1. Include AJAXController/Example.php which defines the
 *      CLASSES_AJAXController_Example class.
 *  2. Creates an object from CLASSES_AJAXController_Example.
 *  3. Passes $GET, minus controller and action, to the 'foo' method
 *      of CLASSES_AJAXController_Example.
 *
 * Note on class names and directory locations:
 * The class name usually goes with the directory location, with / replaced with _, but
 * there are 2 special cases:  starting class name with CLASSES or ADMIN:
 * ADMIN: means it is a sub-directory of whatever the admin directory is.  Allows site owner
 *  to change admin directory name.
 * CLASSES: means it is a sub-directory of whatever the classes dir is.  Also allows site owner
 *  to change the classes directory name.
 *
 * See the admin_AJAX::dispatch() function in AJAX.php for more details.
 *
 */

// DON'T FORGET THIS
if (class_exists('classes_AJAX') or die()) {
}

class CLASSES_AJAXController_Example extends classes_AJAX
{

    function foo($data)
    {
        // Get a $db object just for demonstration
        $db = true;
        include GEO_BASE_DIR . "get_common_vars.php";

        var_dump($data);

        // It is also possible to dispatch requests manually
        // This would be the equivalent of AJAX.php?controller=Example&action=bar&data=rab
        classses_AJAX::dispatch('Example', 'bar', array( 'data' => 'rab' ));
    }

    function bar($data)
    {
        var_dump($data);
    }
}
