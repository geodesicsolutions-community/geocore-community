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

class emailImportItem extends geoImportItem
{
    protected $_name = "Email Address";
    protected $_description = "The user's main contact email address";
    protected $_fieldGroup = self::USER_GENERAL_FIELDGROUP;

    public $displayOrder = 4;

    private $prep_emailExists;
    final protected function _cleanValue($value)
    {
        $value = trim($value);

        if (!geoString::isEmail($value)) {
            trigger_error('ERROR IMPORT: invalid email address');
            return false;
        }
        $db = DataAccess::getInstance();
        if (!$this->prep_emailExists) {
            $this->prep_emailExists = $db->Prepare("SELECT `id` FROM " . geoTables::userdata_table . " WHERE `email` = ?");
        }
        if ($db->GetOne($prep_emailExists, array($value))) {
            trigger_error('ERROR IMPORT: email address already exists!');
            return false;
        }
        return $value;
    }

    final protected function _updateDB($value, $groupId)
    {
        geoImport::$tableChanges['userdata']['email'] = " `email` = '{$value}' ";
        return true;
    }
}
