<?php

class logoImportItem extends geoImportItem
{
    protected $_name = 'Storefront: Logo';
    protected $_description = 'Logo image to be used for this storefront. Accepts a fully-qualified URL (e.g. http://www.example.com/image.jpg) or local file path (e.g. /var/www/images/image.jpg)';

    public $requires = 'subscription_duration';
    public $displayOrder = 1;

    protected $_fieldGroup = self::USER_ADDON_FIELDGROUP;

    protected function _cleanValue($value)
    {
        $value = trim($value);
        if (!$value) {
            return '';
        }

        //make sure there's a storefront subscription in place first!
        if (!geoImport::$crosstalk['storefront_subscription_active']) {
            trigger_error('ERROR IMPORT: cannot add storefront logo without a storefront subscription');
            return false;
        }

        //check for a base path
        $import = geoImport::getInstance();
        $basePath = $import->settings['base_image_path'];
        if ($basePath) {
            $value = $basePath . $value;
        }
        return $value;
    }

    private $_settings, $_maxH, $_maxW;
    protected function _updateDB($value, $groupId)
    {
        $db = DataAccess::getInstance();

        if (!$this->_maxH || !$this->_maxW) {
            $reg = geoAddon::getRegistry('storefront');
            $this->_maxW = $reg->max_logo_width;
            $this->_maxH = $reg->max_logo_height;
        }

        //use geoImage to grab the specified image and constrain it to admin-set dimensions
        $resized = geoImage::resize($value, $this->_maxW, $this->_maxH);

        if (!$resized) {
            //no image. nothing to do!
            return true;
        }

        //get filename for new image
        $images_dir = ADDON_DIR . 'storefront/images/';
        $seed = 1;
        do {
            $seed = $seed * 10 + rand(0, 9); //add another digit to the end of the random portion of the filename
            $filename = 'logo' . $groupId . '_' . $seed . "." . pathinfo($value, PATHINFO_EXTENSION);
        } while (is_file($images_dir . $filename));

        //write it to disk
        $fullCreate = imagejpeg($resized['image'], $images_dir . $filename);

        //kill the temp images to free memory
        imagedestroy($resized);


        if (!$this->_settings) {
            $this->_settings = $db->Prepare("UPDATE `geodesic_addon_storefront_user_settings` SET 
					`logo` = ?, `logo_width` = ?,`logo_height` = ?,`logo_list_width` = ?,`logo_list_height` = ? WHERE `owner` = ?");
        }

        if (!$db->Execute($this->_settings, array($filename, $resized['width'], $resized['height'], $resized['width'], $resized['height'], $groupId))) {
            trigger_error('ERROR IMPORT: error adding storefront logo');
            return false;
        }
        return true;
    }
}
