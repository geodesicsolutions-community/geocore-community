<?php

class phoneImportItem extends geoImportItem
{
    protected $_name = "Phone Number";
    protected $_description = "The user's primary phone number";
    protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;

    public $displayOrder = 6;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['phone'] = " `phone` = '{$value}' ";
        return true;
    }
}
