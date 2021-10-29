<?php

//addons/log_license_db/setup.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
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

# Log license activities Addon

class addon_log_license_db_setup
{
    function install()
    {
        //script to install a fresh copy.

        //get $db connection - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $sql = "CREATE TABLE IF NOT EXISTS `geodesic_license_log` (
`log_id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
`time` INT( 14 ) NOT NULL DEFAULT '0',
`log_type` ENUM( 'error_local', 'error_remote', 'notice_local', 'notice_remote' ) NOT NULL ,
`message` TEXT NOT NULL ,
`need_attention` TINYINT( 3 ) NOT NULL DEFAULT '1',
INDEX ( `time` , `log_type` )
) ";
        $result = $db->Execute($sql);
        if (!$result) {
            //query failed, return false.
            return false;
        } else {
            //execute successful, install worked.
            return true;
        }
    }

    function uninstall()
    {
        //get $db connection - use get_common_vars.php to be forward compatible
        //see that file for documentation.
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $sql = 'DROP TABLE IF EXISTS `geodesic_license_log`';
        $result = $db->Execute($sql);
        if (!$result) {
            //query failed, return false
            return false;
        } else {
            //query executed good, should be un-installed now.
            return true;
        }
    }
}
