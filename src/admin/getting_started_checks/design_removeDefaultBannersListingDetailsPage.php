<?php

require_once ADMIN_DIR . 'getting_started.php';

class design_removeDefaultBannersListingDetailsPage extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Remove or Replace Example Banner Ads: Listing Details Page';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Design';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Remove the "example" ad banners on the front page, or replace them with your own ads. Make this change in <strong>listing_auction.tpl</strong> and/or <strong>listing_classified.tpl</strong>';

    /**
     * Value that represents how important this check is towards final completion.
     * Most will use a value of 1. A check with a weight of 2 should be roughly twice as important as normal.
     * @var float
     */
    public $weight = 1;

    /**
     * Accessor for user-selected state of checkbox for this item
     * @var bool
     */
    public $isChecked;

    /**
     * Just a constructor.
     */
    public function __construct()
    {
        $this->isChecked = (bool)DataAccess::getInstance()->get_site_setting('gettingstarted_' . $this->name . '_isChecked');
    }

    /**
     * This function should return a bool based on whether the checked item "appears" to be complete.
     * @return bool
     */
    public function isComplete()
    {
        if (geoMaster::is('auctions')) {
            $name = 'listing_auction.tpl';
            $custom = file_get_contents(geoTemplate::getFilePath(geoTemplate::MAIN_PAGE, '', $name));
            if (strpos($custom, 'sample_300x100.jpg') || strpos($custom, '1and1_300x100.jpg')) {
                return false;
            }
        }
        if (geoMaster::is('classifieds')) {
            $name = 'listing_classified.tpl';
            $custom = file_get_contents(geoTemplate::getFilePath(geoTemplate::MAIN_PAGE, '', $name));
            if (strpos($custom, 'sample_300x100.jpg') || strpos($custom, '1and1_300x100.jpg')) {
                return false;
            }
        }
        return true;
    }
}
