<?php

//session_handler.php
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
## ##    7.5.3-36-gea36ae7
##
##################################

# BulkUploader
class geoBulkUploaderSessionHandler
{
    var $sessionTable = 'geodesic_addon_bulk_uploader_session';
    var $id;
    var $db;

    /**
     * easily keeps track of session data
     *
     * @param int $id
     * @return session
     */
    function __construct($id = 'bulk_uploader')
    {
        $this->id = $id;
        $this->db = DataAccess::getInstance();
        $this->clear();
        $this->update();
    }

    /**
     * removed a configuration setting
     *
     * @param string $setting
     */
    function remove($setting)
    {
        $this->config($setting, '');
    }

    function config($setting, $value = null)
    {
        if (!$setting) {
            return false;
        }
        $sql = "SELECT `value` FROM $this->sessionTable WHERE id='config' AND name='$setting' LIMIT 1";
        $r = $this->db->GetRow($sql);
        if ($r === false) {
            die($this->db->ErrorMsg() . "<br /> $sql");
        }

        if ($value === null) {
            //no 'setter' value passed in -- just get current value and return
            return (isset($r['value']) ? $r['value'] : '');
        }

        if (isset($r['value'])) {
            $sql = "UPDATE $this->sessionTable 
			SET	`value` = ?, `time` = ? 
			WHERE `name` = ? AND `id` = 'config'";
            $r = $this->db->Execute($sql, array($value, geoUtil::time(), $setting));
        } else {
            $sql = "INSERT INTO $this->sessionTable 
			SET	`id` = 'config', `name` = ?, `value` = ?, `time` = ?";
            $r = $this->db->Execute($sql, array($setting, $value, geoUtil::time()));
        }

        return (($r === false) ?  die($this->db->ErrorMsg() . "<br /> $sql") : true);
    }

    public function configArray()
    {
        return $this->db->GetAssoc("SELECT `name`, `value` FROM $this->sessionTable WHERE id='config'");
    }

    /**
     * sets a session variable
     *
     * @param string $name
     * @param string $value
     * @return boolean
     */
    function set($name, $value, $valueId = 0)
    {
        $value = addslashes($value);
        $sql = "SELECT id FROM " . $this->sessionTable . " WHERE 
		id = '" . $this->id . "' AND name = '" . $name . "' AND vid = '" . $valueId . "'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        }
        if ($result->RecordCount() > 0) {
            $sql = "UPDATE $this->sessionTable SET 
			value = '$value' WHERE 
			id = '$this->id' AND 
			vid = '$valueId' AND 
			name = '$name'";
            $result = $this->db->Execute($sql);
            if (!$result) {
                return false;
            }
        } else {
            $sql = "insert into " . $this->sessionTable . " 
			(id,name,value,vid,time)
			values
			('" . $this->id . "','" . $name . "','" . $value . "','" . $valueId . "','" . time() . "')";
            $result = $this->db->Execute($sql);
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    /**
     * returns a session variable
     *
     * @param string $name
     * @return string
     */
    function get($name, $valueId = 0)
    {
        $sql_query = "select value from " . $this->sessionTable . " where 
		id = '$this->id' and 
		name = '$name' and 
		vid = '$valueId' 
		limit 1";
        $result = $this->db->Execute($sql_query);
        if (!$result) {
            return '';
        } else {
            $resultRow = $result->FetchRow();
            return stripslashes($resultRow["value"]);
        }
    }

    /**
     * returns an array of session variables
     *
     * @param string $name
     * @return string
     */
    function getArray($name)
    {
        $sql = "SELECT * FROM {$this->sessionTable} WHERE
		id = '$this->id' AND 
		name = '$name'
		ORDER BY vid ASC";
        $r = $this->db->Execute($sql);
        if ($r === false) {
            die('database error!:' . $this->db->ErrorMsg());
        }

        $resultArray = array();
        while ($resultRow = $r->FetchRow()) {
            $resultArray[$resultRow["vid"]] = stripslashes($resultRow["value"]);
        }
        return $resultArray;
    }

    /**
     * unsets session variables
     *
     * @param string $name,... unlimited session names can be passed, if no parameters are passed all session data is cleared.
     * @return boolean
     */
    function free($id = 0)
    {
        if (!$id) {
            $id = $this->id;
        }
        if (func_num_args() > 0) {
            $arguments = func_get_args();
            $sql = "delete from " . $this->sessionTable . " where 
			id = '" . $this->id . "' and (";
            foreach ($arguments as $key => $value) {
                $sql .= " name = '" . $value . "' or";
            }
            $sql = trim($sql, " or");
            $sql .= ")";
            $result = $this->db->Execute($sql);
            if (!$result) {
                return false;
            }
        } else {
            $sql = "delete from " . $this->sessionTable . " where 
			id = '" . $this->id . "'";
            $result = $this->db->Execute($sql);
            if (!$result) {
                return false;
            }
        }
    }


    function freeRowById($id = 0)
    {
        if (!$id) {
            $id = $this->id;
        }

        $sql = "delete from " . $this->sessionTable . " where 
		id = '$id'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * updates session variable time stamps
     * to current time
     *
     * @return boolean
     */
    function update()
    {
        $sql = "update " . $this->sessionTable . " set 
		time = " . time() . " where 
		id = '" . $this->id . "'";
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        }
    }

    /**
     * clears out of date session variables
     *
     * @return boolean
     */
    function clear($id = null)
    {

        $sql = "delete from " . $this->sessionTable . " where 
		time < " . (time() - (60 * 60 * 24));
        if ($id != null) {
            $sql .= " AND id='$id'";
        }
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        }
    }
}
