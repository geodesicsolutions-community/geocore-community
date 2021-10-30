<?php

class user_page_1_titleImportItem extends geoImportItem
{
    private $_pageNum = 1;
    protected $_name = 'Storefront: User Page 1 Title';
    protected $_description = 'The name and link text for user page 1';

    public $requires = 'subscription_duration';
    public $displayOrder = 3;

    protected $_fieldGroup = self::USER_ADDON_FIELDGROUP;

    protected function _cleanValue($value)
    {
        $value = trim($value);
        //make sure there's a storefront subscription in place first!
        if ($value && !geoImport::$crosstalk['storefront_subscription_active']) {
            trigger_error('ERROR IMPORT: cannot add storefront data without a storefront subscription');
            return false;
        }
        return geoString::specialChars($value);
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
            $this->_settings = $db->Prepare("INSERT INTO geodesic_addon_storefront_pages (`owner`, `page_link_text`, `page_name`, `display_order`) VALUES (?,?,?,?)");
        }

        if (!$db->Execute($this->_settings, array($groupId, $value, $value, $this->_pageNum))) {
            trigger_error('ERROR IMPORT: error adding storefront page ' . $this->_pageNum);
            return false;
        }
        geoImport::$crosstalk['storefront_user_page_' . $this->_pageNum] = true;
        return true;
    }
}
