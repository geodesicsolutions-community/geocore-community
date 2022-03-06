<?php

//Registry.class.php
/**
 * Holds the geoRegistry class.
 *
 * @package System
 * @since Version 4.0.0
 */


/**
 * Handy little object, can be used to set registry type settings.
 *
 * @package System
 * @since Version 4.0.0
 */
class geoRegistry
{
    /**
     * Used internally
     * @internal
     */
    private $_registry, $_name, $_id;

    /**
     * Constructer
     * @param string $name
     * @param string $id
     */
    public function __construct($name = '', $id = '')
    {
        $this->_registry = array();
        if (strlen($name) > 0) {
            $this->setName($name);
            $this->setId($id);
            $this->unSerialize();
        }
    }
    /**
     * Sets the name, this should match the table name "geodesic_$name_registry" and
     * also the column name "$name" which holds the ID key.
     *
     * @param string $value
     */
    public function setName($value)
    {
        $this->_name = $value;
    }

    /**
     * Sets the ID value, this is how it will identify this "set" of settings, for
     * instance if this was a registry for payment gateways, the ID might be the
     * payment gateway name.
     *
     * @param mixed $value
     */
    public function setId($value)
    {
        $this->_id = $value;
    }

    /**
     * Gets the specified item from the registry, or if item is one of the "main" items it gets
     *  that instead.
     *
     * @param string $item
     * @param mixed $default What to return if the item is not set.
     * @return Mixed the specified item, or false if item is not found.
     */
    public function get($item, $default = false)
    {
        if (isset($this->_registry[$item])) {
            return $this->_registry[$item];
        }
        return $default;
    }

    /**
     * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
     *  of something from the registry.
     *
     * @param string $item
     * @param mixed $value
     */
    public function set($item, $value)
    {
        if ($value === false) {
            if (isset($this->_registry[$item])) {
                unset($this->_registry[$item]);
            }
            return;
        }
        $this->_registry[$item] = $value;
    }

    /**
     * Saves the registry values.
     * @todo Speed this method up, it runs too slow.
     */
    public function serialize()
    {
        //check input
        if ($this->_id === 0 || strlen(trim($this->_name)) == 0) {
            //oops
            trigger_error('ERROR REGISTRY: oops, _id: ' . $this->_id);
            return false;
        }

        $table = '`geodesic_' . $this->_name . '_registry`';
        $index_field = '`' . $this->_name . '`';
        $id = $this->_id;
        $db = DataAccess::getInstance();

        //first clear all current registry entries
        $sql = "DELETE FROM $table WHERE $index_field = ?";
        $result = $db->Execute($sql, array($id));
        if (!$result) {
            trigger_error('ERROR REGISTRY SQL: Sql error when attempting to remove old values, sql: ' . $sql . ' error: ' . $db->ErrorMsg());
        }

        //Now, go through each registry item, determine what type it should be saved as, and save it.
        $array_keys = array_keys($this->_registry);
        foreach ($array_keys as $key) {
            if ($this->_registry[$key] === false) {
                continue; //do not store false values
            }
            if (is_array($this->_registry[$key])) {
                //it's an array, serialize it and stuff it in the val_complex field.
                $val_type = '`val_complex`';
                $value = geoString::toDB(serialize($this->_registry[$key]));
            } else {
                //it's not an array, assume it's a string.
                $value = geoString::toDB($this->_registry[$key]);
                if (strlen($value) > 250) {
                    $val_type = '`val_text`';
                } else {
                    $val_type = '`val_string`';
                }
            }
            //add it to db
            $sql = "INSERT INTO $table (`index_key`, $index_field, $val_type) VALUES (?, ?, ?)";
            $query_data = array($key, $id, $value);
            $result = $db->Execute($sql, $query_data);
            if (!$result) {
                trigger_error('ERROR SQL: error attempting to add data to registry for ' . $this->_name . ': ' . $db->ErrorMsg());
                return false;
            }
        }
    }

    /**
     * The name and ID must be set before calling this, this method will populate
     * the registry object with the saved vars for the given name and ID.
     *
     */
    public function unSerialize()
    {
        $db = DataAccess::getInstance();
        $table = '`geodesic_' . $this->_name . '_registry`';
        $index_field = '`' . $this->_name . '`';
        $id = $this->_id;

        //reset registry
        $this->_registry = array();
        if ($this->_id === 0 || strlen($this->_name) == 0) {
            //not enough info to get info
            return false;
        }
        $sql = "SELECT `index_key`, `val_string`, `val_text`, `val_complex` FROM $table WHERE $index_field = ?";
        $result = $db->Execute($sql, array($id));
        if (!$result) {
            trigger_error('ERROR SQL: Error unserializing registry data, error: ' . $db->ErrorMsg());
            return false;
        }
        while ($row = $result->FetchRow()) {
            $key = $row['index_key'];
            $value = '';
            if (strlen($row['val_string']) > 0) {
                $value = geoString::fromDB($row['val_string']);
            } elseif (strlen($row['val_text']) > 0) {
                $value = geoString::fromDB($row['val_text']);
            } elseif (strlen($row['val_complex']) > 0) {
                $value = unserialize(geoString::fromDB($row['val_complex']));
            }
            $this->_registry[$key] = $value;
        }
    }

    /**
     * Alias of geoRegistry::serialize() - see that method for details.
     *
     */
    public function save()
    {
        $this->serialize();
    }

    /**
     * Static function that removes all registry items as specified by registry type and ID,
     *
     * @param string $registry_name
     * @param int|string $id
     */
    public static function remove($registry_name, $id)
    {
        if (strlen(trim($registry_name)) == 0) {
            return;
        }
        if (!$id) {
            return;
        }
        $table = '`geodesic_' . $registry_name . '_registry`';

        $db = DataAccess::getInstance();
        //first, remove the main order.
        $sql = "DELETE FROM $table WHERE `$registry_name` = ?";
        $result = $db->Execute($sql, array($id));
        if (!$result) {
            trigger_error('ERROR SQL: Error trying to remove registry items for ' . $id . ' - error: ' . $db->ErrorMsg());
        }
    }

    /**
     * Converts all the settings to an associative array, handy for going through all the
     * settings in a registry for whatever reason.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_registry;
    }
    /**
     * Magic method called to use syntax $reg->var_name to get value
     * @param string $var
     * @return Mixed
     */
    public function __get($var)
    {
        return $this->get($var);
    }
    /**
     * Magic method called to set value using OOP
     * @param string $var
     * @param Mixed $value
     * @return Mixed
     */
    public function __set($var, $value)
    {
        return $this->set($var, $value);
    }
}
