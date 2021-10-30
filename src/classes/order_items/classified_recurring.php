<?php

//order_items/classified.php

//TODO for future version: include a way to turn off "normal" classifieds and only allow Recurring ones to be created

require_once CLASSES_DIR . 'order_items/classified.php';


class classified_recurringOrderItem extends classifiedOrderItem
{
    var $defaultProcessOrder = 11;
    protected $type = 'classified_recurring';
    const type = 'classified_recurring';

    public function displayInAdmin()
    {
        if (!geoMaster::is('classifieds')) {
            return false;
        }
        return true;
    }

    public function getTypeTitle()
    {
        return 'BETA / EXPERIMENTAL: Recurring Classified';
    }


    /**
     * admin plan item settings
     * @param geoPlanItem $planItem
     * @return String html
     */
    public function adminPlanItemConfigDisplay($planItem)
    {
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('enabled', $planItem->getEnabled());
        $tpl->assign('recurring_billing_available', geoRecurringBilling::RecurringBillingIsAvailable());

        $db = DataAccess::getInstance();
        $sql = "select * from " . geoTables::listing_subscription_lengths . " where price_plan = ? and category = ? order by period asc";
        $result = $db->Execute($sql, array($planItem->getPricePlan(), $planItem->getCategory()));
        $lengths = array();
        foreach ($result as $row) {
            $lengths[] = $row;
        }
        $tpl->assign('price_plan_id', $planItem->getPricePlan());
        $tpl->assign('category_id', $planItem->getCategory());
        $tpl->assign('lengths', $lengths);

        return $tpl->fetch('order_items/classified_recurring/plan_item_settings.tpl');
    }

    public function adminPlanItemConfigUpdate($planItem)
    {
        //check for at least one subscription period before letting this go live
        $sql = "select * from " . geoTables::listing_subscription_lengths . " where price_plan = ? and category = ? order by period asc";
        $result = DataAccess::getInstance()->Execute($sql, array($planItem->getPricePlan(), $planItem->getCategory()));
        if (!$result || $result->RecordCount() == 0) {
            geoAdmin::m('You must create at least one subscription period before enabling', geoAdmin::ERROR);
            return false;
        }

        $settings = $_POST['classified_recurring'];
        $planItem->setEnabled($settings['enabled'] == 1 ? true : false);

        return true;
    }

    public static function geoCart_initSteps($allPossible = false, $subtype = null)
    {
        parent::geoCart_initSteps($allPossible, self::type);
    }

    public function geoCart_initItem_new($item_type = null, $subtype = null)
    {
        return parent::geoCart_initItem_new(1, self::type);
    }

    public function geoCart_initItem_restore()
    {
        return parent::geoCart_initItem_restore();
    }

    public static function detailsDisplay($delayRender = false)
    {
        $cart = geoCart::getInstance();
        $cart->site->recurring_classified_details = true; //let the parent item know not to create a duration dropdown, since this has its own options
        $tpl_vars = parent::detailsDisplay(true); //pass true so that the parent doesn't do the actual page display. we'll take care of that below
        $tpl_vars['duration_dropdown'] = $cart->site->display_subscription_duration_dropdown(true);

        geoView::getInstance()->setBodyTpl('classified/listing_collect_details.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);

        $cart->site->display_page();

        return $tpl_vars; //because prior art does...this probably doesn't actually go anywhere
    }

    public static function detailsProcess($noSetCost = false)
    {
        $cart = geoCart::getInstance();
        $cart->item->set('is_recurring', 1);
        $cart->item->set('recurring_duration', $cart->site->session_variables['classified_length']);
        $sql = "SELECT `price` FROM " . geoTables::listing_subscription_lengths . " WHERE `category` = ? AND `price_plan` = ? AND `period` = ?";

        $category = $cart->price_plan['category_id'] ? $cart->price_plan['category_id'] : 0;
        $result = DataAccess::getInstance()->GetOne($sql, array($category,$cart->price_plan['price_plan_id'],$cart->site->session_variables['classified_length']));

        $cart->item->set('recurring_price', $result);
        $cart->item->setCost($result);

        return parent::detailsProcess(true);
    }

    public static function categoryDisplay($listing_type_allowed = null, $onlyRecurringClassifieds = false)
    {
        //just a slight modification to the parent call so that it knows to look at only those cats that have recurring enabled
        return parent::categoryDisplay(1, true);
    }

    public static function geoCart_other_detailsDisplay($subtype = null)
    {
        $return = parent::geoCart_other_detailsDisplay(self::type);
        //here we want to show the price of just the listing, with no children extras figured in
        $return['price_display'] = geoString::displayPrice(self::getListingCost(false), false, false, 'cart');

        return $return;
    }

    public static function geoCart_other_detailsCheckVars($subtype = null)
    {
        return parent::geoCart_other_detailsCheckVars(self::type);
    }

    public static function geoCart_other_detailsProcess($subtype = null)
    {
        return parent::geoCart_other_detailsProcess(self::type);
    }

    public static function adminItemDisplay($item_id, $subtype = null)
    {
        return parent::adminItemDisplay($item_id, self::type);
    }

    public static function geoCart_payment_choicesProcess($sell_type = null, $subtype = null)
    {
        return parent::geoCart_payment_choicesProcess(1, self::type);
    }
    /**
     * Optional.
     * Used: in geoCart::cartDisplay()
     *
     * Used only for "parent" items, this should return the text to use for the new button displayed
     * in the cart view, for instance something like "Add New Classified".
     *
     */
    public static function geoCart_cartDisplay_newButton($inModule = false)
    {
        if (!geoMaster::is('classifieds') || (self::isAnonymous())) {
            return '';
        }

        //see if allowed to by check
        $cart = geoCart::getInstance();
        if (!($cart->user_data['restrictions_bitmask'] & 1)) {
            //listing placement not allowed
            return '';
        }
        //check to make sure creating new classifieds is turned on (separate from the geoPC::is_classifieds master switch)
        if (!$cart->db->get_site_setting('allow_new_classifieds')) {
            return '';
        }
        if (!defined('IN_ADMIN') && geoPC::is_print() && $cart->db->get_site_setting('disableClientPlaceListings')) {
            //client-side listing placement is disabled
            return false;
        }
        //see if max ads allowed is 0
        $maxAllowed = $cart->db->GetOne("SELECT `max_ads_allowed` FROM " . geoTables::price_plans_table . " WHERE `price_plan_id`=" . (int)$cart->user_data['price_plan_id']);
        if ($cart->user_data['price_plan_id'] && $maxAllowed == 0) {
            //max number of listings is 0, don't display add button
            return '';
        }
        if (!geoRecurringBilling::RecurringBillingIsAvailable()) {
            //recurring billing is not enabled for at least one gateway
            return '';
        }


        //find out if recurring classifieds are enabled in this price plan (any category)
        $sql = "SELECT `enabled` FROM " . geoTables::plan_item . " WHERE `order_item` = 'classified_recurring' AND `price_plan` = ? AND `enabled` = 1";
        $result = $cart->db->Execute($sql, array($cart->user_data['price_plan_id']));
        if (!$result || $result->RecordCount() == 0) {
            return '';
        }

        //make sure there is at least one valid subscription period in this price plan
        $sql = "SELECT `category` FROM " . geoTables::listing_subscription_lengths . " WHERE `price_plan` = ?";
        $result = $cart->db->Execute($sql, array($cart->user_data['price_plan_id']));
        if (!$result || $result->RecordCount() == 0) {
            return '';
        }


        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        return $msgs[502386];
    }


    public static function my_account_links_newButton()
    {

        return array('label' => self::geoCart_cartDisplay_newButton(true));
    }


    /**
     * Optional.
     * Used: in geoCart
     *
     * This is used to display what the action is if this order item is the main type.  It should return
     * something like "adding new listing" or "editing images".
     *
     * @return string
     */
    public static function getActionName($vars)
    {
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        return $msgs['502388'];
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        //most of this is already done by the parent classified object
        $return = parent::getDisplayDetails($inCart, $inEmail);
        //but we want to override the item type

        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $return['title'] = $msgs[502387];

        //see whether our bologna has a first name yet. If so, add it to the display!
        $session_variables = $this->get('session_variables');
        $title = $this->_listingTitleDisplay($session_variables['classified_title'], $inEmail);
        if ($title) {
            $return['title'] .= " - $title";
        }

        //want to show the cost of the listing by itself, with no extras included
        $return['priceDisplay'] = geoString::displayPrice($this->getRecurringPrice(false), false, false, 'cart');

        return $return;
    }


    public static function geoCart_initItem_forceOutsideCart()
    {
        return true;
    }


    /**
     * Optional.
     * Used: throughout code.
     *
     * Specify whether or not this item is a recurring billing item or not, if
     * this method is not defined the superclass will return false.
     *
     * @return bool
     * @since Version 4.1.0
     */
    public function isRecurring()
    {
        return true;
    }

    /**
     * Optional.
     * Used: usually in recurring payment gateways.
     *
     * Required if isRecurring() returns true, otherwise it will
     * default to 0 (basically recurring being off).  This needs to return the
     * interval for the recurring billing, in seconds.
     *
     * @return int The recurring interval in seconds.
     * @since Version 4.1.0
     */
    public function getRecurringInterval()
    {
        if ($this->get('is_recurring') && $this->get('recurring_duration')) {
            //stored as a number of days
            return $this->get('recurring_duration') * 60 * 60 * 24;
        } else {
            return 0;
        }
    }
    /**
     * Optional.
     * Used: usually in recurring payment gateways.
     *
     * Required if isRecurring() returns true, otherwise it will
     * default to 0 (basically recurring being off).  This needs to return the
     * price for the recurring billing.
     *
     * @return float The recurring price
     * @since Version 4.1.0
     */
    public function getRecurringPrice($withChildren = true)
    {
        if (!$this->get('is_recurring')) {
            //not a recurring listing
            return 0;
        }
        //begin with the base recurring price for the listing
        $price = $this->get('recurring_price');

        if ($withChildren) {
            //now add the recurring price of any attached children that use getRecurringSubCost
            $order = $this->getOrder();
            $allItems = $order->getItem();
            foreach ($allItems as $i) {
                $parent = $i->getParent();
                if ($parent && $parent->getId() == $this->getId() && $i->getRecurringSubCost()) {
                    //found a a child item that has some price to add to the total recurring price
                    $price += $i->getRecurringSubCost();
                }
            }
        }

        return $price;
    }

    /**
     * Optional.
     * Used: usually in recurring payment gateways.
     *
     * Required if isRecurring() returns true, otherwise it will
     * default to "Recurring Item".  This needs to return the description for
     * this item that will be used in the payment gatway transaction and possibly
     * other places for the recurring billing.
     *
     * @return string
     * @since Version 4.1.0
     */
    public function getRecurringDescription()
    {
        if (!$this->get('is_recurring')) {
            //not a recurring listing
            return 0;
        }
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $desc = $msgs[502389];
        $session_variables = $this->get('session_variables');
        if ($session_variables) {
            $title = $this->_listingTitleDisplay($session_variables['classified_title']);
            if ($title) {
                $desc .= ": " . $title;
            }
        }
        return $desc;
    }

    /**
     * Optional.
     * Used: usually in recurring payment gateways.
     *
     * Only used if isRecurring() returns true, if not implemented the
     * geoOrderItem superclass will return the current time.  This is expected
     * to return the timestamp for when the initial recurring billing first payment
     * should be made.  This will make the system allow for if something is already
     * paid for through a certain date.
     *
     * @return int Unix timestamp for when recurring start date should be.
     */
    public function getRecurringStartDate()
    {
        if (!$this->get('is_recurring')) {
            return 0;
        }

        return parent::getRecurringStartDate();

        /* I don't think this needs to do anything fancy for right now...should always be starting when it starts....I think. */
    }

    /**
     * Optional.
     * Used: in {@link geoRecurringBilling::updateStatus()} after gateway has updated
     * status on the recurring billing.
     *
     * Use this to make changes when a recurring billing has been updated, such
     * as updating the expiration of an item or extending a subscription.
     *
     * @param geoRecurringBilling $recurring
     * @since Version 4.1.0
     */
    public static function recurringBilling_updateStatus($recurring)
    {
        //sanity check 123
        if (!$recurring) {
            //shouldn't ever get here except in an episode of the twilight zone.
            //A very short episode.
            return;
        }

        $userId = (int)$recurring->getUserId();
        if (!$userId) {
            //can't do anything
            return;
        }
        $paidUntil = (int)$recurring->getPaidUntil();

        if ($paidUntil < geoUtil::time()) {
            //subscription is not paid up for renewal. Call recurringBilling_cancel to un-link the recurring billing item, and then let the subscription expire naturally
            //NOTE: this will allow the user to buy a new recurring subscription, which will begin at the end of any time remaining on this old one
            self::recurringBilling_cancel($recurring);
            return;
        }

        //after checking recurring status, we know how long this listing is paid for -- bump the `ends` value to that moment!
        $recurringId = $recurring->getId();
        $listingId = DataAccess::getInstance()->GetOne("SELECT `listing_id` FROM " . geoTables::listing_subscription . " WHERE `recurring_id` = ?", array($recurringId));
        $listing = geoListing::getListing($listingId);
        $listing->ends = $paidUntil;
    }

    /**
     * Optional.
     * Used: in {@link geoRecurringBilling::cancel()} after gateway has processed
     * the cancelation.
     *
     * Use this to make changes when a recurring billing has been canceled, such
     * as updating the expiration of an item or removing a subscription.
     *
     * @param geoRecurringBilling $recurring
     * @since Version 4.1.0
     */
    public static function recurringBilling_cancel($recurring)
    {
        //not a whole lot to do other than let the listing expire naturally once its end time stops being updated
    }
}
