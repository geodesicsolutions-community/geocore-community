<?php

require_once ADMIN_DIR . 'getting_started.php';

class pricing_configureUserGroupsPricePlans extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Configure User Groups and Price Plans';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Pricing';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Configure your site\'s User Groups and Price Plans to your liking. Review the <a href="https://geodesicsolutions.org/wiki/how_this_software_works/user_groups_price_plans/start/">User Groups / Price Plans Relationship</a> page in the user manual for more information';

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
        if (geoPC::is_whitelabel()) {
            $this->description = 'Configure your site\'s User Groups and Price Plans to your liking. Review the user manual for more information';
        }
    }

    /**
     * This function should return a bool based on whether the checked item "appears" to be complete.
     * @return bool
     */
    public function isComplete()
    {
        //no real way to do an automated check for this one, so just return self checked state to prevent nag messages.
        return $this->isChecked;
    }
}
