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

class welcome_noteImportItem extends geoImportItem
{
    protected $_name = 'Storefront: Welcome Note';
    protected $_description = 'Message displayed at the top of user\'s storefront pages. HTML is allowed, but conforms to Allowed HTML settings.';

    public $requires = 'subscription_duration';
    public $displayOrder = 2;

    protected $_fieldGroup = self::USER_ADDON_FIELDGROUP;

    protected function _cleanValue($value)
    {
        $value = trim($value);
        //make sure there's a storefront subscription in place first!
        if ($value && !geoImport::$crosstalk['storefront_subscription_active']) {
            trigger_error('ERROR IMPORT: cannot add storefront data without a storefront subscription');
            return false;
        }
        $value = geoFilter::replaceDisallowedHtml($value);
        return $value;
    }

    private $_settings;
    protected function _updateDB($value, $groupId)
    {
        if (!geoImport::$crosstalk['storefront_subscription_active']) {
            //no storefront active here, so nothing to do
            //if it got this far, this is likely not an error, just a user without a storefront, so return true
            return true;
        }
        $db = DataAccess::getInstance();

        if (!$this->_settings) {
            $this->_settings = $db->Prepare("UPDATE geodesic_addon_storefront_user_settings SET `welcome_message` = ? WHERE `owner` = ?");
        }

        if (!$db->Execute($this->_settings, array($value, $groupId))) {
            trigger_error('ERROR IMPORT: error adding storefront welcome note');
            return false;
        }
        return true;
    }
}
