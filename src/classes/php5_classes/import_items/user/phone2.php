<?php

class phone2ImportItem extends geoImportItem
{
    protected $_name = "Phone Number 2";
    protected $_description = "The user's secondary phone number";
    protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;

    public $displayOrder = 7;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['phone2'] = " `phone2` = '{$value}' ";
        return true;
    }
}
