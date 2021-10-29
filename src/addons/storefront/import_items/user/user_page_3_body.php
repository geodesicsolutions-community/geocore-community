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

class user_page_3_bodyImportItem extends geoImportItem
{
    private $_pageNum = 3;
    protected $_name = 'Storefront: User Page 3 Body';
    protected $_description = 'The page body for user page 3. HTML is allowed.';

    public $requires; // set in consstructor
    public $displayOrder = 8;

    protected $_fieldGroup = self::USER_ADDON_FIELDGROUP;

    public function __construct()
    {
        $this->requires = 'user_page_' . $this->_pageNum . '_title';
    }

    protected function _cleanValue($value)
    {
        $value = trim($value);
        //make sure there's a storefront subscription in place first!
        if ($value && !geoImport::$crosstalk['storefront_subscription_active']) {
            trigger_error('ERROR IMPORT: cannot add storefront data without a storefront subscription');
            return false;
        }
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
        if (!geoImport::$crosstalk['storefront_user_page_' . $this->_pageNum]) {
            trigger_error('ERROR IMPORT: no storefront page title before body on page number ' . $this->_pageNum);
            return false;
        }
        $db = DataAccess::getInstance();

        if (!$this->_settings) {
            $this->_settings = $db->Prepare("UPDATE geodesic_addon_storefront_pages SET `page_body` = ? WHERE `owner` = ? AND `display_order` = ?");
        }

        if (!$db->Execute($this->_settings, array($value, $groupId, $this->_pageNum))) {
            trigger_error('ERROR IMPORT: error adding storefront page body ' . $this->_pageNum);
            return false;
        }
        return true;
    }
}
