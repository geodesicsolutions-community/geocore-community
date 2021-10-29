<?php

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################

class companyNameImportItem extends geoImportItem
{
    protected $_name = "Company Name";
    protected $_description = "The name of the user's company";
    protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;

    public $displayOrder = 3;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['company_name'] = " `company_name` = '{$value}' ";
        return true;
    }
}
