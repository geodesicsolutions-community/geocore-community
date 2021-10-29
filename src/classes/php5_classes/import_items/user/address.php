<?php

/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################

class addressImportItem extends geoImportItem
{
    protected $_name = "Address";
    protected $_description = "The user's street address";
    protected $_fieldGroup = self::USER_LOCATION_FIELDGROUP;

    public $displayOrder = 0;

    final protected function _cleanValue($value)
    {
        $value = addslashes(trim($value));
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['address'] = " `address` = '{$value}' ";
        return true;
    }
}
