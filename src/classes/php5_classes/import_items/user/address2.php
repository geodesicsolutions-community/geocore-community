<?php

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.0-13-g6eae54b
##
##################################

class address2ImportItem extends geoImportItem
{
    protected $_name = "Address 2";
    protected $_description = "The user's street address (second line)";
    protected $_fieldGroup = self::USER_LOCATION_FIELDGROUP;

    public $displayOrder = 1;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }


    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['address_2'] = " `address_2` = '{$value}' ";
        return true;
    }
}
