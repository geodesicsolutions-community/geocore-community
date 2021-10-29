<?php

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################

class firstnameImportItem extends geoImportItem
{
    protected $_name = "First Name";
    protected $_description = "The user's first name (proper name)";
    protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;

    public $displayOrder = 0;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['firstname'] = " `firstname` = '{$value}' ";
        return true;
    }
}
