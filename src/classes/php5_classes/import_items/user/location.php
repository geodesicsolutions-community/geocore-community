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

class locationImportItem extends geoImportItem
{
    protected $_name = "Location (Terminal Region)";
    protected $_description = "Country, State, City, etc -- enter the name or ID of the lowest-level Region to use";
    protected $_fieldGroup = self::USER_LOCATION_FIELDGROUP;

    public $displayOrder = 3;

    final protected function _cleanValue($value)
    {
        $value = trim($value);
        $id = $this->_getTerminalRegionId($value);
        if (!$id) {
            trigger_error('DEBUG IMPORT: Did not recognize region identifier: ' . $value . '. skipping region assignment.');
            return '';
        }
        return $id;
    }

    final protected function _updateDB($value, $groupId)
    {
        if (!$value) {
            //nothing to set. move along.
            return true;
        }
        //thanks to _cleanValue(), $value is the lowest-level region to use, translated from the input
        //get its parents, then set the whole array into the correct places
        $levels = geoRegion::getLevelsForOverrides();
        $regions = geoRegion::getRegionWithParents($value);
        //set the main regions using the geoRegion class function
        geoRegion::setUserRegions($groupId, $regions);

        //regions should also be reflected in the userdata table
        if ($levels['city'] && $regions[$levels['city']]) {
            geoImport::$tableChanges['userdata']['location_city'] = " `city` = '" . addslashes(geoRegion::getNameForRegion($regions[$levels['city']])) . "' ";
        }
        if ($levels['state'] && $regions[$levels['state']]) {
            geoImport::$tableChanges['userdata']['location_state'] = " `state` = '" . addslashes(geoRegion::getNameForRegion($regions[$levels['state']])) . "' ";
        }
        if ($levels['country'] && $regions[$levels['country']]) {
            geoImport::$tableChanges['userdata']['location_country'] = " `country` = '" . addslashes(geoRegion::getNameForRegion($regions[$levels['country']])) . "' ";
        }
        return true;
    }

    private $_regionCache;
    private function _getTerminalRegionId($location)
    {
        if (!$location) {
            //nothing to find!
            return false;
        }

        if (isset($this->_regionCache[$location])) {
            return $this->_regionCache[$location];
        }

        if (is_numeric($location)) {
            //this is already a region id -- nothing to do here!
            return $location;
        }
        $db = DataAccess::getInstance();

        //first, check the "unique name" field
        //special case: also replace spaces with hyphens and check that
        $id = (int)$db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE unique_name=? OR unique_name=?", array(geoString::toDB($location), geoString::toDB(str_replace(' ', '-', $location))));
        if ($id) {
            $this->_regionCache[$location] = $id;
            return $id;
        }

        //now check abbreviations (since the pre-GeoCore State field required uploading by abbreviation)
        //since this is legacy and abbreviations are non-unique, only look in the "State" level
        $levels = geoRegion::getLevelsForOverrides();
        if ($levels['state']) {
            $id = (int)$db->GetOne("SELECT `id` FROM " . geoTables::region . " WHERE billing_abbreviation=? AND level=?", array(geoString::toDB($location), $levels['state']));
            if ($id) {
                $this->_regionCache[$location] = $id;
                return $id;
            }
        }

        //if nothing found there, try looking for the name directly
        $id = (int)$db->GetOne("SELECT `id` FROM " . geoTables::region_languages . " WHERE name=?", array(geoString::toDB($location)));
        if ($id) {
            $this->_regionCache[$location] = $id;
            return $id;
        }

        //found nothing
        return false;
    }
}
