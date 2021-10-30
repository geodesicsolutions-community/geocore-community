<?php

//session_handler.php

/*

Due to the massive amounts of potential data, the bulk uploader requires a more optimized version of the geoRegistry construct

NOTE: for ease of refactoring, this is meant to replicate the functionality of the standard geoRegistry as used within the Bulk Uploader (and so most field/var names are identical)

The main difference is that the BulkUploaderRegistry will purge certain values from its internal cache before each get() -- this prevents overflowing the available variable space

By extension, BulkUploaderRegistry's set() automatically saves data to the db, instead of waiting on serialize()/save()

*/

# BulkUploader
class geoBulkUploaderRegistry
{
    private $_cache = array();

    /* This is an array of fields that should not be "purged" with each new get().
     * This should only contain registry fields that are common across all uploads.
     * The "normal" case is to purge memory of old records before getting a new one, except for things mentioned here
     */
    private $_doNotPurge = array(
        '_failedUserCheck',
        '_savedSeller',
        '_savedUserCheck',
        '_savedDefaults',
        '_savedUseDefaults',
        '_savedTitle',
        '_savedColumns',
        '_savedDuration',
        '_savedUpgrades',
    );

    public function get($setting)
    {
        if (isset($this->_cache[$setting])) {
            return $this->_cache[$setting];
        }
        $this->_purge();

        $sql = "SELECT * FROM `geodesic_addon_bulk_uploader_registry` WHERE `index_key` = ?";
        $row = DataAccess::getInstance()->GetRow($sql, array($setting));

        if (strlen($row['val_string']) > 0) {
            $this->_cache[$setting] = geoString::fromDB($row['val_string']);
        } elseif (strlen($row['val_text']) > 0) {
            $this->_cache[$setting] = geoString::fromDB($row['val_text']);
        } elseif (strlen($row['val_complex']) > 0) {
            $this->_cache[$setting] = unserialize(geoString::fromDB($row['val_complex']));
        } else {
            return false;
        }

        return $this->_cache[$setting];
    }
    public function __get($setting)
    {
        return $this->get($setting);
    }

    private function _purge()
    {
        foreach ($this->_cache as $key => $value) {
            if (!in_array($this->_doNotPurge, $key)) {
                unset($this->_cache[$key]);
            }
        }
    }

    public function set($setting, $value)
    {
        if ($value === false) {
            //deleting something, if it exists
            DataAccess::getInstance()->Execute("DELETE FROM `geodesic_addon_bulk_uploader_registry` WHERE `index_key` = ?", array($setting));
            unset($this->_cache[$setting]);
            return;
        }

        if (is_array($value)) {
            //it's an array, serialize it and stuff it in the val_complex field.
            $val_type = '`val_complex`';
            $value = geoString::toDB(serialize($value));
        } else {
            //it's not an array, assume it's a string.
            $value = geoString::toDB($value);
            if (strlen($value) > 250) {
                $val_type = '`val_text`';
            } else {
                $val_type = '`val_string`';
            }
        }
        $sql = "REPLACE INTO `geodesic_addon_bulk_uploader_registry` SET " . $val_type . " = ?, `index_key` = ?";
        DataAccess::getInstance()->Execute($sql, array($value, $setting));
    }
    public function __set($setting, $value)
    {
        $this->set($setting, $value);
    }

    //*************** these functions just here for completeness/sanity *******************
    public function save()
    {
        //do nothing! this version of the registry serializes and saves automatically on set()!
    }
    public function serialize()
    {
        //do nothing! this version of the registry serializes and saves automatically on set()!
    }
}
