<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.0-16-gf5139d7
##
##################################

require_once ADMIN_DIR . 'getting_started.php';

class design_readDesignTutorial extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Review the User Manual\'s Configuration and Startup Checklist';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Design';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Many common configurations and settings are covered in the <a href="http://geodesicsolutions.com/support/geocore-wiki/doku.php/id,startup_tutorial_and_checklist;start/">Configuration and Startup Checklist</a> section of the User Manual. 
							The User Manual is an excellent resource for answers to common questions relating to the GeoCore software. Take a few moments to read over and familiarize yourself with this section of the manual.';

    /**
     * Value that represents how important this check is towards final completion.
     * Most will use a value of 1. A check with a weight of 2 should be roughly twice as important as normal.
     * @var float
     */
    public $weight = 2;

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
            $this->description = 'Many common configurations and settings are covered in the User Manual. 
							The User Manual is an excellent resource for answers to common questions relating to the software. Take a few moments to read over and familiarize yourself with this section of the manual.';
        }
    }

    /**
     * This function should return a bool based on whether the checked item "appears" to be complete.
     * @return bool
     */
    public function isComplete()
    {
        //there's nothing to check in the software on this one, so return whatever isChecked is set to, so that the nag messages don't show up
        return (bool)$this->isChecked;
    }
}
