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

class subscription_durationImportItem extends geoImportItem
{
    protected $_name = 'Storefront: Subscription Duration';
    protected $_description = 'Setting a number of days here will activate a Storefront Subscription of that length for a user';

    public $displayOrder = 0;

    protected $_fieldGroup = self::USER_ADDON_FIELDGROUP;

    protected function _cleanValue($value)
    {
        return intval($value);
    }

    private $_sub, $_settings;
    protected function _updateDB($value, $groupId)
    {
        if (!$value) {
            //duration of 0 days. do nothing.
            return true;
        }
        $db = DataAccess::getInstance();

        if (!$this->_sub) {
            $this->_sub = $db->Prepare("INSERT INTO `geodesic_addon_storefront_subscriptions` (`expiration`, `user_id`) VALUES (?,?)");
        }

        if (!$this->_settings) {
            $this->_settings = $db->Prepare("INSERT INTO geodesic_addon_storefront_user_settings (owner) VALUES (?)");
        }

        $expiration = geoUtil::time() + ($value * 86400); //expire $value days from now
        if (!$db->Execute($this->_sub, array($expiration, $groupId))) {
            trigger_error('ERROR IMPORT: error adding storefront subscription');
            return false;
        }
        if (!$db->Execute($this->_settings, array($groupId))) {
            trigger_error('ERROR IMPORT: error adding storefront settings');
            return false;
        }
        geoImport::$crosstalk['storefront_subscription_active'] = true;
        return true;
    }
}
