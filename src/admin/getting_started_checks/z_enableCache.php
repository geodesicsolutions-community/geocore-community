<?php

require_once ADMIN_DIR . 'getting_started.php';

class z_enableCache extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Enable Cache';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Final Steps';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'If your host is capable, turn on the <a href="index.php?page=cache_config&mc=site_setup">Site Cache</a> to improve page load times. 
	Technical information about the site cache is available in the <a href="http://geodesicsolutions.com/support/geocore-wiki/doku.php/id,admin_menu;site_setup;cache;start">User Manual</a>';

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
        return (bool) geoCache::get('use_cache');
    }
}
