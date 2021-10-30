<?php

class meta_not_usedImportItem extends geoImportItem
{
    protected $_name = 'Field Not Used';
    protected $_description = 'Select this for fields in the source file that do not map to datapoints within GeoCore';
    protected $_fieldGroup = self::NOT_USED_FIELDGROUP;

    protected function _cleanValue($value)
    {
        //do nothing!
        return '';
    }

    protected function _updateDB($value, $groupId)
    {
        //do nothing!
        return true;
    }
}
