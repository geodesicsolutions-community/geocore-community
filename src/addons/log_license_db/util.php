<?php

//addons/log_license_db/util.php
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

class addon_log_license_db_util
{

    var $tables;
    function addon_log_license_db_util()
    {
        $this->tables = new addon_log_license_db_tables();
    }
    /**
     * Function called when the system generates an error, or debug message.
     * @param Array $error_data
     */
    function core_errorhandle($err_data)
    {
        $msg_data = explode(':', $err_data['errstr']);
        if (trim($msg_data[0]) == 'DEBUG LICENSE') {
            //this is a notice about the license.
            $type = 'notice_';
        } elseif (trim($msg_data[0]) == 'ERROR LICENSE') {
            //this is an error about the license.
            $type = 'error_';
        } else {
            //This message does not relate to the license, just ignore the msg.
            return false;
        }
        //stop output of any debug messages by the data accessor, since we are
        //already inside of the error handler, any debug messages from db would just
        //get echoed out.
        ob_start();
        //this message relates to the license, so procede w/ logging it in
        //the database.
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');
        unset($msg_data[0]);
        //license message expected to follow format, with no : in log message:
        //(DEBUG|ERROR) LICENSE:(LOCAL|REMOTE):Log Message
        $type .= strtolower(trim($msg_data[1]));
        unset($msg_data[1]);
        $msg = implode(':', $msg_data);

        $sql = 'INSERT INTO ' . $this->tables->license_log_table . ' SET `time`=?, `log_type`=?, `message`=?';
        $query_data = array(time(), $type, $msg);
        $result = $db->Execute($sql, $query_data);

        if (!$result) {
            //db error.
            //do not trigger error, since this is already inside of error handler
            //trigger_error('ERROR SQL: log_license_db addon: error when inserting data.  Sql:'.$sql.' Error Msg:'.$db->ErrorMsg());
        }
        ob_clean();
    }

    //where to add built-in hard coded addon functions
}
