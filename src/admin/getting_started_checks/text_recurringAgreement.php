<?php

require_once ADMIN_DIR . 'getting_started.php';

class text_recurringAgreement extends geoGettingStartedCheck
{
    /**
     * User-readable name/title for this check
     * @var String
     */
    public $name = 'Customize Recurring Billing User Agreements';
    /**
     * Name of the section this check belongs in
     * @var String
     */
    public $section = 'Text';
    /**
     * Descriptive text that explains the check and how to resolve it
     * @var String
     */
    public $description = 'If in use, edit the Recurring Billing user agreements for <a href="index.php?page=sections_edit_text&b=10203&c=500739&l=1">Paypal Pro</a> and <a href="index.php?page=sections_edit_text&b=10203&c=500765&l=1">Authorize.net</a>, and customize them for your site.
	<br />Note: the checklist will detect this item as complete if you are not using either of the above-mentioned Recurring Billing systems.';

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
        $ppp = geoPaymentGateway::getPaymentGateway('paypal_pro');
        if ($ppp && $ppp->getEnabled() && $ppp->isRecurring()) {
            $agree = $ppp->getRecurringAgreement();
            if (stripos($agree['text'], 'This text is changed in the admin') !== false) {
                return false;
            }
        }
        $authnet = geoPaymentGateway::getPaymentGateway('authorizenet');
        if ($authnet && $authnet->getEnabled() && $authnet->isRecurring()) {
            $agree = $authnet->getRecurringAgreement();
            if (stripos($agree['text'], 'This text is changed in the admin') !== false) {
                return false;
            }
        }
        return true;
    }
}
