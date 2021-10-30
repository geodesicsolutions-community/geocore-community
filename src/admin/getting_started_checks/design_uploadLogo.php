<?php

require_once ADMIN_DIR . 'getting_started.php';

class design_uploadLogo extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Upload a Logo';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Design';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Upload your logo to <strong>external/images/logo.jpg</strong> in your template set.';

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
        $activeLogo = getimagesize(geoTemplate::getFilePath(geoTemplate::EXTERNAL, 'images', 'logo.jpg'));
        $defaultLogo = getimagesize(GEO_TEMPLATE_DIR . 'default/external/images/logo.jpg');
        return !($activeLogo === $defaultLogo);
    }
}
