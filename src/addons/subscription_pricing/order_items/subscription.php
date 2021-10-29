<?php

//order_items/subscription.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    7.6.3-73-gda0dfd6
##
##################################

class subscriptionOrderItem extends geoOrderItem
{
    var $defaultProcessOrder = 25;
    protected $type = 'subscription';
    const type = 'subscription';

    /**
     * Optional, but required if displayInAdmin() returns true.
     * Used: in admin, display items awaiting approval (only for main items, not for sub-items)
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        $title = 'Subscription Renewal (' . $this->get('period_display') . ')';
        $title = $this->getId() . ' - ' . $title;

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    public function adminPlanItemConfigDisplay($planItem)
    {
        $html = "";
        $graceSecs = $planItem->get('expireBuffer', 60 * 60 * 24 * 3); //setting stored in seconds. convert to days before showing to user
        $graceDays = $graceSecs / 60 / 60 / 24;
        $html .= geoHTML::addOption('Grace Period', "<label>Extend subscriptions for <input type='text' name='subscription[expireBuffer]' id='expireBuffer' value='$graceDays' size='5' /> days after they would otherwise expire</label>");
        return $html;
    }

    public function adminPlanItemConfigUpdate($planItem)
    {
        $settings = $_POST['subscription'];

        if (is_array($settings)) {
            $expireBuffer = intval($settings['expireBuffer']) * 60 * 60 * 24;
            $planItem->set('expireBuffer', $expireBuffer); //be sure to allow for a setting of 0
        }

        return true;
    }

    public function geoCart_initItem_new()
    {
        $cart = geoCart::getInstance();

        if ($cart->price_plan['type_of_billing'] != 2) {
            //err what?  they should not be doing this, they aren't a subscription based user..
            return false;
        }
        //make sure there are not any already added
        if (count($cart->order->getItem(self::type)) > 0) {
            //existing subscription found in cart, don't allow a new one to be added.
            $cart->site->page_id = 10202;
            $cart->site->get_text();
            $msg = (geoMaster::is('site_fees')) ? $cart->site->messages[500413] : $cart->site->messages[500414];
            $cart->addErrorMsg('subscription', $msg);
            return false;
        }
        //make sure there are no subscriptions pending from another order
        $userid = (int)$cart->user_data['id'];
        if (!$userid) {
            //can't do subscription for nobody
            return false;
        }
        $sql = "SELECT count(oi.id) as count FROM " . geoTables::order_item . " as oi, " . geoTables::order . " as o WHERE o.buyer = $userid AND o.id = oi.order AND o.status in ('active','pending','pending_admin') AND oi.`type` = '" . self::type . "' AND oi.`status` != 'active'";
        $row = $cart->db->GetRow($sql);
        if ($row === false) {
            trigger_error('ERROR SQL CART: Error, sql: ' . $sql . ' Error Msg: ' . $cart->db->ErrorMsg());
            return false;
        }

        if (isset($row['count']) && $row['count'] > 0) {
            $cart->site->page_id = 10202;
            $cart->site->get_text();

            $cart->addErrorMsg('subscription', $cart->site->messages[500741]);
            return false;
        }

        if (geoPC::is_ent()) {
            //none pending found, see if user already has recurring set up for a subscription
            $sql = "SELECT `id` FROM " . geoTables::recurring_billing . " 
				WHERE `user_id`=? AND `item_type`=? AND `status`!=?";
            $all = $cart->db->GetAll($sql, array($userid, self::type, geoRecurringBilling::STATUS_CANCELED));

            if ($all) {
                foreach ($all as $rrow) {
                    $recurring = geoRecurringBilling::getRecurringBilling($rrow['id']);
                    if ($recurring) {
                        //run an update to see if it's still active and kicking,
                        //if so, don't allow recurring billing
                        $recurring->updateStatus();
                        if ($recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
                            //stop it from allowing, found a recurring billing that is still potentially active.
                            $cart->site->page_id = 10202;
                            $cart->site->get_text();

                            $cart->addErrorMsg('subscription', $cart->site->messages[500742]);
                            return false;
                        }
                    }
                }
            }
        }

        //make sure the price plan it set...
        $this->setPricePlan($cart->price_plan['price_plan_id']);

        //set the previous subscription, to be able to undo a subscription purchase.
        $this->set('prev_subscription_expire', $cart->user_data['subscription_expire']);
        return true;
    }

    public function displayInAdmin()
    {
        return true;
    }

    public static function geoCart_cartCheckVars()
    {
        $cart = geoCart::getInstance();

        //special case: if this is a listing edit (and that edit is the only item in the cart), we don't care about anything else below!
        $justAnEdit = false;
        $items = $cart->order->getItem();
        if (count($items) == 1) {
            $onlyItem = array_pop($items);
            if ($onlyItem->getType() === 'listing_edit') {
                //if the only item on this order is a listing edit, it doesn't matter if the user has a subscription or not. Skip the checks below and allow editing.
                $justAnEdit = true;
            }
        }

        if (!$justAnEdit && $cart->cart_variables['order_item'] != -1 && $cart->price_plan['type_of_billing'] == 2 && (!isset($cart->user_data['subscription_expire']) || $cart->user_data['subscription_expire'] < geoUtil::time())) {
            //not a stand-alone cart, and using subscription-based user group,
            //and subscription expire seems to have expired.
            $items = $cart->order->getItem(self::type);

            if (count($items) == 0) {
                //no subscriptions in order, throw an error
                $cart->addError();
                $newOrRenew = 'renew';
                if (!isset($cart->user_data['subscription_expire']) || $cart->user_data['subscription_expire'] == 0) {
                    //no subscription in the system

                    $newOrRenew = self::newOrRenewSubscription();
                }
                $url = $cart->db->get_site_setting('classifieds_url') . '?a=cart&amp;action=new&amp;main_type=subscription';
                if ($newOrRenew == 'new') {
                    //show message that the user does not have a subscription yet
                    //note that this is best on an educated guess, if it goes here the likelyhood this is there first
                    //renewal is very high.
                    $cart->addErrorMsg('subscription', $cart->site->messages[500409]
                        . '<a href="' . $url . '">' . $cart->site->messages[500410] . '</a>');
                } else {
                    //show the message that they need to extend their subscription.
                    //note that this is best on an educated guess, if it goes here the likelyhood they have
                    //previously purchased a subscription is somewhat high.

                    //This error should be worded for both people that have never purchased, and also people that this
                    //is a renewal for them.
                    $cart->addErrorMsg('subscription', $cart->site->messages[500411]
                        . '<a href="' . $url . '">' . $cart->site->messages[500412] . '</a>');
                }
            }
        }
    }

    protected static function newOrRenewSubscription()
    {
        $cart = geoCart::getInstance();

        $row = $cart->db->GetRow("SELECT `value` FROM " . geoTables::subscription_choices . " WHERE `price_plan_id` = ? ORDER BY `value` ASC LIMIT 1", array($cart->price_plan['price_plan_id']));

        if (($cart->user_data['date_joined'] + ($row['value'] * 24 * 60 * 60)) > geoUtil::time()) {
            //this is probably a new subscription for them
            return 'new';
        } else {
            return 'renew';
        }
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $price = $this->getCost(); //people expect numbers to be positive...
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $return = array (
            'title' => $msgs[500331],
            'canEdit' => true, //whether can edit it or not
            'canDelete' => true, //whether can remove from cart or not
            'canPreview' => false, //whether can preview the item or not
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price,
            'children' => false
        );

        $return['title'] .= " ({$this->get('period_display')})";
        //go through children...
        $items = $this->getOrder()->getItem();
        $children = array();
        foreach ($items as $i => $val) {
            if (is_object($val) && is_object($val->getParent())) {
                $p = $val->getParent();
                if ($p->getId() == $this->getId()) {
                    //This is a child of mine...
                    $displayResult = $val->getDisplayDetails($inCart, $inEmail);
                    if ($displayResult !== false) {
                        //only add if they do not return bool false
                        $children[$val->getId()] = $displayResult;
                        $return['total'] += $children[$val->getId()]['total']; //add to total we are returning.
                    }
                }
            }
        }
        if (count($children)) {
            $return['children'] = $children;
        }
        return $return;
    }

    public function getCostDetails()
    {
        //Most use this exactly AS-IS...

        $return = array (
                    'type' => $this->getType(),
                    'extra' => null,
                    'cost' => $this->getCost(),
                    'total' => $this->getCost(),
                    'children' => array(),
        );

        //call the children and populate 'children'
        $order = $this->getOrder();//get the order
        $items = $order->getItem();//get all the items in the order
        $children = array();
        foreach ($items as $i => $item) {
            if (is_object($item) && $item->getType() != $this->getType() && is_object($item->getParent())) {
                $p = $item->getParent();//get parent
                if ($p->getId() == $this->getId()) {
                    //Parent is same as me, so this is a child of mine, add it to the array of children.
                    //remember the function is not static, so cannot use callDisplay() or callUpdate(), need to call
                    //the method directly.
                    $costResult = $item->getCostDetails();
                    if ($costResult !== false) {
                        //only add if they do not return bool false
                        $children[$item->getId()] = $costResult;
                        $return['total'] += $costResult['total']; //add to total we are returning.
                    }
                }
            }
        }
        if ($return['total'] == 0) {
            //total is 0, even after going through children!  no cost details to return
            return false;
        }
        if (count($children)) {
            //add children to the array
            $return['children'] = $children;
        }
        return $return;
    }

    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
    {
        if ($newStatus == $this->getStatus()) {
            //the status hasn't actually changed, so nothing to do
            return;
        }
        $activate = ($newStatus == 'active') ? true : false;

        $already_active = ($this->getStatus() == 'active') ? true : false;

        //allow parent to do common things, like set the status and
        //call children items
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);

        $duration = intval($this->get('duration'));
        $prev_exp = $this->get('prev_subscription_expire');

        if ($duration == 0) {
            //err duration not known...
            trigger_error('ERROR TRANSACTION ORDER: Duration not known, not able to add subscription length...  User must have hit refresh or something.');
            return ;
        }
        $db = DataAccess::getInstance();

        //check to see if currently subscribed
        $sql = "SELECT `subscription_id`,`subscription_expire` FROM " . geoTables::user_subscriptions_table . " WHERE `user_id` = ? LIMIT 1";
        $check_subscriptions_results = $db->Execute($sql, array($this->getOrder()->getBuyer()));

        if (!$check_subscriptions_results) {
            trigger_error('ERROR TRANSACTION SQL: paypal:transaction_process() - sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            return ;
        }

        if ($activate) {
            //do activate actions here, such as setting listing to live

            //save data for recurring billing
            $order = $this->getOrder();
            $recurringId = 0;
            if ($order && $order->getRecurringBilling()) {
                $recurring = $order->getRecurringBilling();
                //save the user id
                $recurring->setUserId($order->getBuyer());
                $recurring->save();
                $recurringId = $recurring->getId();
            }

            if ($check_subscriptions_results->RecordCount() > 0) {
                //extend subscription period
                $show_subscription = $check_subscriptions_results->FetchRow();

                if ($show_subscription["subscription_expire"] > geoUtil::time()) {
                    $new_expire = intval($show_subscription["subscription_expire"] + ($duration * 86400));
                } else {
                    $new_expire = intval(geoUtil::time() + ($duration * 86400));
                }
                $sql = "UPDATE " . geoTables::user_subscriptions_table . " SET `subscription_expire` = ?, `recurring_billing`=?, `notice_sent`='0' WHERE `subscription_id` = ? LIMIT 1";
                $update_subscriptions_results = $db->Execute($sql, array($new_expire,$recurringId, $show_subscription["subscription_id"]));

                if (!$update_subscriptions_results) {
                    trigger_error('ERROR SQL: sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
                }
            } else {
                //no previous entries, add a new one if we are activating

                //enter new subscription period
                $new_expire = intval(geoUtil::time() + ($duration * 86400));
                $sql = "INSERT INTO " . geoTables::user_subscriptions_table . " (user_id, price_plan_id, subscription_expire, `recurring_billing`)	VALUES (?, ?, ?, ?)";
                $query_data = array ($order->getBuyer(), $this->getPricePlan(), $new_expire, $recurringId);
                $insert_subscriptions_results = $db->Execute($sql, $query_data);

                if (!$insert_subscriptions_results) {
                    trigger_error('ERROR TRANSACTION SQL: paypal:transaction_process() - sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
                }
            }
        } elseif (!$activate && $already_active && $check_subscriptions_results->RecordCount() > 0) {
            //making inactive
            $show_subscription = $check_subscriptions_results->FetchRow();
            if ($prev_exp < geoUtil::time()) {
                //previous expiration time is 0 or less than current time, so remove the expiration.
                $sql = "DELETE FROM " . geoTables::user_subscriptions_table . " WHERE `subscription_id` = ? LIMIT 1";
                $result = $db->Execute($sql, array($show_subscription["subscription_id"]));
            } else {
                $sql = "UPDATE " . geoTables::user_subscriptions_table . " SET `subscription_expire` = ? WHERE `subscription_id` = ? LIMIT 1";
                $result = $db->Execute($sql, array($prev_exp, $show_subscription["subscription_id"]));
            }
            if (!$result) {
                trigger_error('ERROR SQL: sql: ' . $sql . ' Error msg: ' . $db->ErrorMsg());
            }
        }
    }


    public static function getParentTypes()
    {
        //this is main order item, no parent types
        //return array(0, 'classified', 'auction', 'dutch_auction');
        return array ();
    }
    private static $_userSubscriptions = array();

    public static function geoCart_initSteps($allPossible = false)
    {
    }

    public static function geoCart_initItem_forceOutsideCart()
    {
        //for subscription, should be stand-alone if recurring is possible
        $cart = geoCart::getInstance();
        return $cart->isRecurringPossible();
    }

    public function isRecurring()
    {
        //this is a recurring item
        return true;
    }

    public function getRecurringInterval()
    {
        $duration = (int)$this->get('duration', 0);
        if (!$duration) {
            //no duration?
            return 0;
        }
        //duration is in days, convert it to seconds
        $duration = $duration * 24 * 60 * 60;
        return $duration;
    }

    public function getRecurringPrice()
    {
        //use getOrderTotal to get recurring amount, so that special recurring children can alter
        //recurring price
        return $this->getOrder()->getOrderTotal();
    }

    public function getRecurringDescription()
    {
        $userId = $this->getOrder()->getBuyer();
        $userName = geoUser::userName($userId);
        $msgs = DataAccess::getInstance()->get_text(true, 10203);
        return "$msgs[500740] $userName (#$userId)";
    }

    /**
     * Optional, used if isRecurring() returns true, if order item does not implement
     * the current time will always be returned.
     *
     * @return int Unix timestamp for when recurring start date should be.
     */
    public function getRecurringStartDate()
    {
        //OK see if there is currently a subscription for the user.
        $order = $this->getOrder();
        if (!$order) {
            //no order, can't look up user for this order item
            return parent::getRecurringStartDate();
        }
        $userId = (int)$order->getBuyer();
        if (!$userId) {
            //no user, can't look up user for this order item.
            return parent::getRecurringStartDate();
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT `subscription_expire` FROM " . geoTables::user_subscriptions_table . "
			WHERE `user_id` = ?";
        $row = $db->GetRow($sql, array($userId));
        if ($row && (int)$row['subscription_expire'] > geoUtil::time()) {
            return (int)$row['subscription_expire'];
        }
        //could not find any subscription, or subscription is already expired,
        //so return recurring start date.
        return parent::getRecurringStartDate();
    }

    /**
     * update status
     * @param geoRecurringBilling $recurring
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
            //umm it's not paid up?  don't extend the subscription
            return;
        }

        $db = DataAccess::getInstance();

        $sql = "SELECT `subscription_id` FROM " . geoTables::user_subscriptions_table . " WHERE `user_id` = ? LIMIT 1";
        $row = $db->GetRow($sql, array($userId));

        if (isset($row['subscription_id'])) {
            //extend subscription period

            $sql = "UPDATE " . geoTables::user_subscriptions_table . " SET `subscription_expire` = ?, `recurring_billing`=? WHERE `subscription_id` = ? LIMIT 1";
            $results = $db->Execute($sql, array($paidUntil,$recurring->getId(), $row["subscription_id"]));

            if (!$results) {
                trigger_error('ERROR SQL: sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            }
        } else {
            //no previous entries, add a new one if we are activating

            //need to get the price plan ID
            $user = geoUser::getUser($userId);
            if (!$user) {
                //could not get user?
                trigger_error('ERROR RECURRING: Could not get user to get price plan ID');
                return;
            }
            $pricePlanId = (int)((geoMaster::is('classifieds')) ? $user->price_plan_id : $user->auction_price_plan_id);
            if (!$pricePlanId) {
                //could not get price plan ID?
                trigger_error('ERROR RECURRING: Could not get price plan ID');
                return;
            }
            $sql = "INSERT INTO " . geoTables::user_subscriptions_table . " (user_id, price_plan_id, subscription_expire, `recurring_billing`) VALUES (?, ?, ?, ?)";
            $query_data = array ($userId, $pricePlanId, $paidUntil, $recurring->getId());
            $results = $db->Execute($sql, $query_data);

            if (!$results) {
                trigger_error('ERROR TRANSACTION RECURRING SQL: recurring update status failed - sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            }
        }
    }

    public static function recurringBilling_cancel($recurring)
    {
        //Actually we don't really need to do that much here.  Just let the subscription
        //expire naturally since the recurring billing will no longer be paid.
    }

    /**
     * Required by interface.
     * Used: in geoCart::initSteps()
     *
     * Determine whether or not the other_details step should be added to the steps of adding this item
     * to the cart.  This should also check any child items if it does not need other_details itself.
     *
     * @return boolean True to add other_details to steps, false otherwise.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        return true; //this item has stuff to display on other_details step.
    }

    public static function geoCart_other_detailsCheckVars()
    {
        $cart = geoCart::getInstance();
        if ($cart->main_type != self::type || !is_object($cart->item) || $cart->item->getType() != self::type) {
            //do not show
            return ;
        }
        if (isset($_POST['c'])) {
            $item = $cart->item;

            $cart->setPricePlan($item->getPricePlan(), $item->getCategory());
            $selection_id = intval($_POST['c']['subscription_choice']);

            $sql = "SELECT * FROM " . geoTables::subscription_choices . " WHERE price_plan_id = " . $cart->item->getPricePlan() . " AND `period_id` = $selection_id";
            $row = $cart->db->GetRow($sql);
            if (empty($row)) {
                //none selected or valid
                $cart->site->page_id = 10205;
                $cart->site->get_text();
                //valid subscription choice required.
                $cart->addError()
                    ->addErrorMsg('subscription', $cart->site->messages[500415]);
            }
        } else {
            $cart->site->page_id = 10205;
            $cart->site->get_text();
            //valid subscription choice required.
            $cart->addError()
                ->addErrorMsg('subscription', $cart->site->messages[500415]);
        }

        //but children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);
    }

    public static function geoCart_other_detailsProcess()
    {
        $cart = geoCart::getInstance();
        if ($cart->main_type != self::type || !is_object($cart->item) || $cart->item->getType() != self::type) {
            //do not show
            return ;
        }
        $selection_id = intval($_POST['c']['subscription_choice']);
        $sql = "SELECT * FROM " . geoTables::subscription_choices . " WHERE price_plan_id = " . $cart->item->getPricePlan() . " AND `period_id` = $selection_id";
        $row = $cart->db->GetRow($sql);
        if (empty($row)) {
            //none selected or valid - should not get here since already checked in checkVars
            $cart->addError()
                ->addErrorMsg('subscription', 'Valid subscription choice required.');
            return;
        }
        if (!geoMaster::is('site_fees')) {
            $row['amount'] = 0;
        }
        $cart->item->set('period_id', $selection_id);
        $cart->item->set('period_display', $row['display_value']);
        $cart->item->set('duration', $row['value']);
        $cart->item->setCost($row['amount']);

        //but children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
    }

    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();

        if ($cart->main_type != self::type || $cart->price_plan['type_of_billing'] != 2) {
            //not adding a subscription
            return;
        }

        $return = array (
            'checkbox_name' => '', //no checkbox display
            'title' => 'Subscription',
            'display_help_link' => '',//if 0, will display no help icon thingy
            'price_display' => '',
            //templates - over-write mini-template to do things like set margine or something:
            'entire_box' => '',
            'left' => '',
            'right' => '',
            'checkbox' => '',
            'checkbox_hidden' => ''
        );
        $sql = "SELECT * FROM " . geoTables::subscription_choices . " WHERE price_plan_id = " . $cart->item->getPricePlan() . " ORDER BY `value` ASC";
        $choices_result = $cart->db->GetAll($sql);
        if (!$choices_result) {
            trigger_error('ERROR CART SQL: Sql: ' . $sql . ' Msg: ' . $cart->db->ErrorMsg());
            return false;
        }

        $tpl = new geoTemplate('system', 'order_items');
        $tpl->assign('choices', $choices_result);
        $tpl->assign('error', $cart->getErrorMsg('subscription'));
        $tpl->assign('allFree', !geoMaster::is('site_fees'));
        //set selected
        $tpl->assign('selected', ($cart->item->get('subscription_choice')) ? $cart->item->get('subscription_choice') : 0);
        $return['entire_box'] = $tpl->fetch('subscription/other_details.tpl');

        $return ['page_title1'] = $cart->site->messages[500442];
        $return ['page_title2'] = $cart->site->messages[500443];
        $return ['page_desc'] = $cart->site->messages[500444];
        $return ['submit_button_text'] = $cart->site->messages[500445];
        $return ['cancel_text'] = $cart->site->messages[500446];

        return $return;
    }

    public static function geoCart_other_detailsLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500507];
    }

    /**
     * Used to set subscription_expire setting in user_data whenever the price plan is set, as a convenience
     * to be used by rest of item.
     *
     * @param array $vars
     */
    public static function geoCart_setPricePlan($vars)
    {
        $cart = geoCart::getInstance();
        if ($cart->price_plan['type_of_billing'] != 2) {
            //not a subscription
            return;
        }

        //add it to user's data
        $row = $cart->db->GetRow(
            "SELECT `subscription_expire` FROM " . geoTables::user_subscriptions_table . " WHERE `user_id`=? ORDER BY `subscription_expire` LIMIT 1",
            array($cart->user_data['id'])
        );
        if ($row === false) {
            trigger_error('ERROR SQL: Error Msg: ' . $db->ErrorMsg());
            $cart->user_data['subscription_expire'] = 0;
            return;
        }
        if (!empty($row)) {
            $cart->user_data['subscription_expire'] = $row['subscription_expire'];
        } else {
            $cart->user_data['subscription_expire'] = 0;
        }
    }
    public static function my_account_links_newButton()
    {
        return self::geoCart_cartDisplay_newButton(true);
    }
    public static function geoCart_cartDisplay_newButton($inModule = false)
    {
        if (self::isAnonymous()) {
            return '';
        }

        $cart = geoCart::getInstance();
        //Display the button, only when this is a subscription based user and they need a subscription.
        if (!self::_userHasRecurringSubscription($cart->user_data['id']) && $cart->price_plan['type_of_billing'] == 2 && $cart->order && count($cart->order->getItem(self::type)) == 0) {
            if ($inModule) {
                //really being called by my_account_links_newButton - same logic, different return value
                return array (
                    'icon' => $cart->site->messages[500643],
                    'label' => $cart->site->messages[500644]
                );
            } else {
                if (!$msgs) {
                    //haven't gotten text for this page yet -- get it explicitly from cart main
                    $msgs = DataAccess::getInstance()->get_text(true, 10202);
                }
                return $cart->site->messages[500253];
            }
        }
        return '';
    }

    /**
     * Optional
     * Used: in User_management_home::menu()
     *
     * Use this to do stuff to display info on the user info home page.  Note that
     * in order to display anything, you would need to work with the geoView class.
     */
    public static function User_management_home_body($vars)
    {
        $user_id = geoSession::getInstance()->getUserId();
        $user = geoUser::getUser($user_id);
        $db = DataAccess::getInstance();

        $field = (geoMaster::is('classifieds')) ? 'price_plan_id' : 'auction_price_plan_id';

        //get price plan
        $sql = "SELECT `type_of_billing` FROM " . geoTables::price_plans_table . " WHERE `$field` = ? LIMIT 1";
        $price_plan = $db->GetRow($sql, array($user->price_plan_id));

        if (!isset($price_plan['type_of_billing']) || $price_plan['type_of_billing'] != 2) {
            //not subscription
            return;
        }
        $view = geoView::getInstance();
        $msgs = $db->get_text(true);

        if (self::_userHasRecurringSubscription($user_id)) {
            //they already have an active subscription, with recurring billing,
            //so do not show info about subscription expiring.
            return;
        }

        if (!isset($msgs[500491])) {
            //this is the old home page, without the links module
            $link = $msgs[1695];
            $view->renew_extend_subscription = $link;
            return true;
        }

        $subscription = array();
        $subscription['link'] = $vars['url_base'] . '?a=24';
        $subscription['label'] = $msgs[500491];
        $subscription['icon'] = $msgs[500492];
        $subscription['active'] = ($_REQUEST['a'] == 24) ? true : false;

        //find expiration
        $sql = "SELECT `subscription_expire`, `recurring_billing` FROM " . geoTables::user_subscriptions_table . " WHERE `user_id` = $user_id";
        $expiration_time = $db->GetOne($sql);

        $expiration = array();
        $dateFormat = $db->get_site_setting('entry_date_configuration');
        $expiration['label'] = ($expiration_time < geoUtil::time()) ? $msgs[500657] : $msgs[500658] . date($dateFormat, $expiration_time);

        $orderItemLinks = $view->orderItemLinks;

        $orderItemLinks[] = $subscription;
        $orderItemLinks[] = $expiration;
        $view->orderItemLinks = $orderItemLinks;
    }


    public function processRemove()
    {
        if ($this->getStatus() == 'active') {
            //it's active!  de-activate it!
            $this->processStatusChange('pending');
        }
        return true;
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
        $msgs = DataAccess::getInstance()->get_text(true);
        if ($vars['step'] == 'my_account_links') {
            //short version
            return $msgs[500645];
        } else {
            //action interupted text
            //text "renewing subscription"
            return $msgs[500396];
        }
    }

    private static function _userHasRecurringSubscription($user_id)
    {
        $db = DataAccess::getInstance();
        $sql = "SELECT `subscription_expire`, `recurring_billing` FROM " . geoTables::user_subscriptions_table . " WHERE `user_id` = $user_id";
        $subData = $db->GetRow($sql);
        if ($subData && $subData['recurring_billing']) {
            $recurring = geoRecurringBilling::getRecurringBilling($subData['recurring_billing']);
            if ($recurring && $recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
                //they do have an active subscription, with recurring billing,
                //so return true
                return true;
            }
        }
        //no recurring billing found
        return false;
    }
}
