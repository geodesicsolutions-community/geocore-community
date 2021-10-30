<?php

class cityImportItem extends geoImportItem
{
    protected $_name = "City (Static Field)";
    protected $_description = "The user's city -- do not use if assigning cities via the Location field";
    protected $_fieldGroup = self::USER_LOCATION_FIELDGROUP;

    public $displayOrder = 4;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        $levels = geoRegion::getLevelsForOverrides();
        if ($levels['city']) {
            //city geoRegion is in use. silently skip this field, as it shouldn't have been used.
            trigger_error('DEBUG IMPORT: skipping "city" because geoRegions in use');
            return true;
        }

        geoImport::$tableChanges['userdata']['city'] = " `city` = '{$value}' ";
        return true;
    }
}
