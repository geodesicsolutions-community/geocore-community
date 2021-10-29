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
