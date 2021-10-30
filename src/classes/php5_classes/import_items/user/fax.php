<?php

class faxImportItem extends geoImportItem
{
    protected $_name = "Fax Number";
    protected $_description = "The user's fax number";
    protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;

    public $displayOrder = 8;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['fax'] = " `fax` = '{$value}' ";
        return true;
    }
}
