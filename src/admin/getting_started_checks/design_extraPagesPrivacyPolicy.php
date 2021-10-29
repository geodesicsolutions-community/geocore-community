<?php


##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.5.3-36-gea36ae7
##
##################################

require_once ADMIN_DIR . 'getting_started.php';

class design_extraPagesPrivacyPolicy extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Update Extra Pages text: Privacy Policy';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Design';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'Replace the default text of this Extra Page with information relevant to your site.';

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
        $name = 'privacy_policy.tpl';
        $custom = file_get_contents(geoTemplate::getFilePath(geoTemplate::MAIN_PAGE, 'extra_pages', $name));
        $default = file_get_contents(GEO_TEMPLATE_DIR . 'default/main_page/extra_pages/' . $name);
        return !($custom === $default);
    }
}
