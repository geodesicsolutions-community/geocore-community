<?php

require_once ADMIN_DIR . 'getting_started.php';

class z_zSiteOn extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Turn the site on'; //optional name: "Main Screen Turn On!"
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Final Steps';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Once you\'re sure everything is ready to go, turn on the main <a href="index.php?page=main_general_settings&mc=site_setup">Site On/Off Switch</a> to make your site accessible to everyone!';
    //optional description: "We get signal!"

    /**
     * Value that represents how important this check is towards final completion.
     * Most will use a value of 1. A check with a weight of 2 should be roughly twice as important as normal.
     * @var float
     */
    public $weight = .5;

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
        //backwards switch is backwards. 0 means On.
        return DataAccess::getInstance()->get_site_setting('site_on_off') == 0;
    }
}
