<?php

require_once ADMIN_DIR . 'getting_started.php';

class addons_enableSeo extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Enable Common Addons: SEO';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Addons';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Enable the SEO addon, and complete the First-Time Setup Wizard.';

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
        $seo = geoAddon::getUtil('SEO');
        return ($seo && $seo->isON());
    }
}
