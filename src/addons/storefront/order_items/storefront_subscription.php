<?php

//order_items/storefront_subscription.php

# storefront addon

class storefront_subscriptionOrderItem extends geoOrderItem
{
    var $defaultProcessOrder = 25;
    private $table; //holds all the tables names used by the storefront
    protected $type = 'storefront_subscription';
    const type = 'storefront_subscription';

    /**
     * Optional.
     * Used: In admin, during ajax call to display config settings for a particular
     * price plan item.
     *
     * If this method exists, a config button will be displayed beside the item, and when
     * the config button is pressed, whatever this function returns will be displayed
     * below the item using an ajax call.
     *
     * @param geoPlanItem $planItem
     * @return string
     */
    public function adminPlanItemConfigDisplay($planItem)
    {
        $admin = $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $tpl = new geoTemplate('addon', 'storefront');
        $tpl->assign('groupChoices', $planItem->get('groupChoices'));

        $table = geoAddon::getUtil('storefront')->tables();
        $sql = "SELECT * FROM $table->subscriptions_choices ORDER BY `value`";
        $tpl->assign('choices', $db->GetAll($sql));
        $tpl->assign('tooltip_subscriptions', geoHTML::showTooltip("Subscription Periods", "These subscription periods can be added and edited through the storefront tool, at <em>Addon Management > Storefront Addon > Subscription Choices</em>."));
        $tpl->assign('tooltip_allowed', geoHTML::showTooltip("Storefront Allowed", "Allow users to purchase storefront subscriptions."));
        $tpl->assign('tooltip_free', geoHTML::showTooltip("Free Storefronts", "If set, all users in this price plan will automatically have a Storefront, with no need to purchase a subscription"));

        $tpl->assign('enabled', $planItem->getEnabled());
        $tpl->assign('free_storefronts', $planItem->get('free_storefronts'));
        $tpl->assign('periods', $planItem->get('periods'));
        $tpl->assign('periodCheck', (bool)($planItem->get('free_storefronts') || count($planItem->get('periods')) > 0));
        return $tpl->fetch('plan_settings.tpl');
    }

    /**
     * Optional.
     * Used: In admin, during ajax call to update config settings for a particular
     * price plan item.
     *
     * This is only used if adminPlanItemConfigDisplay() is used.
     *
     * @param geoPlanItem $planItem
     * @return bool If return true, message "settings saved" will be displayed, if return
     *  false, message "settings not saved" will be displayed.
     */
    public function adminPlanItemConfigUpdate($planItem)
    {
        $settings = (isset($_GET['storefront_subscription'])) ? $_GET['storefront_subscription'] : $_POST['storefront_subscription'];
        $enabled = (isset($settings['enabled']) && $settings['enabled']) ? 1 : false;

        if (is_array($settings)) {
            $attached_periods = array();
            if ($enabled) {
                //also save period attachments..

                if ($settings['free_storefronts']) {
                    $planItem->set('periods', false); //setting free-for-all; turn off all periods
                    $planItem->set('free_storefronts', $settings['free_storefronts']);
                } else {
                    if (is_array($settings['storefront_periods'])) {
                        $periods = $settings['storefront_periods'];
                        foreach ($periods as $key => $value) {
                            if ($value) {
                                $attached_periods [$key] = $key;
                            }
                        }
                    }
                    if (count($attached_periods) > 0) {
                        $planItem->set('periods', $attached_periods);
                        $planItem->set('free_storefronts', false); //setting one or more periods; turn off free-for-all
                    } else {
                        //don't save anything, they don't have valid settings set.
                        echo 'You must enable at least one storefront period.<hr />';
                        return false;
                    }
                }
            } else {
                //not enabled...make sure free storefronts isn't left on...
                $planItem->set('free_storefronts', false);
            }
        }
        $planItem->setEnabled($enabled);
        return true;
    }

    /**
     * Optional, but required if displayInAdmin() returns true.
     * Used: in admin, display items awaiting approval (only for main items, not for sub-items)
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        $title = 'Storefront Subscription (' . $this->get('period_display') . ')';
        //$title = $this->getId() . ' - '.$title;

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    public static function Admin_site_display_user_data($user_id)
    {

        $has_storefront_subscription = geoAddon::getUtil("storefront")->userHasCurrentSubscription($user_id);

        if ($has_storefront_subscription == 1) {
            //user is attached to price plan with free storefronts
            $html = geoHTML::addOption('Current Storefront Subscription <br>(Attached to Price Plan with free storefronts)', 'no expiration');
        } elseif ($has_storefront_subscription > 0) {
            //$has_storefront_subscription has the current expiration date
            $daysLeft = ($has_storefront_subscription > 0) ? ceil($has_storefront_subscription / (60 * 60 * 24)) : 0;
            $html = geoHTML::addOption('Current Storefront Subscription Length', $daysLeft . ' Days');
        }
        return $html;
    }

    public function geoCart_initItem_new()
    {
        $cart = geoCart::getInstance();
        $planItem = geoPlanItem::getPlanItem(self::type, $cart->price_plan['price_plan_id'], 0);
        if (!$planItem->getEnabled()) {
            //err what?  user should not be doing this; his price plan doesn't allow it!
            return false;
        }
        //make sure there are not any already added
        if (count($cart->order->getItem(self::type)) > 0) {
            $msgs = geoAddon::getText('geo_addons', 'storefront');
            $cart->addErrorMsg('subscription', $msgs['error_sub_one_sub_at_time']);
            return false;
        }
        //make sure there are no subscriptions pending from another order
        $userid = $cart->user_data['id'];
        $sql = "SELECT count(oi.id) as count FROM " . geoTables::order_item . " as oi, " . geoTables::order . " as o WHERE o.buyer = $userid AND o.id = oi.order AND o.status in ('active','pending','pending_admin') AND oi.`type` = '" . self::type . "' AND oi.`status` = 'pending'";
        $row = $cart->db->GetRow($sql);
        if ($row === false) {
            trigger_error('ERROR SQL CART: Error, sql: ' . $sql . ' Error Msg: ' . $cart->db->ErrorMsg());
            return false;
        }
        if (isset($row['count']) && $row['count'] > 0) {
            $msgs = geoAddon::getText('geo_addons', 'storefront');
            $cart->addErrorMsg('subscription', $msgs['error_sub_pending']);
            return false;
        }

        //if this user already has an active Recurring subscription, don't let him start a new one!
        $recurringCheck = $cart->db->GetOne("SELECT `recurring_billing` FROM `geodesic_addon_storefront_subscriptions` WHERE `user_id` = ?", array($userid));
        if ($recurringCheck && $recurringCheck > 0) {
            //user needs to cancel current subscription before being able to begin a new one
            $msgs = geoAddon::getText('geo_addons', 'storefront');
            $cart->addErrorMsg('subscription', $msgs['error_existing_sub']);
            return false;
        }

        //make sure the price plan is set...
        $this->setPricePlan($cart->price_plan['price_plan_id']);

        //set the previous subscription, to be able to undo a subscription purchase.
        $this->set('prev_storefront_expiration', $cart->user_data['storefront_expiration']);
        $this->set('prev_storefront_onhold', $cart->user_data['storefront_onhold']);
        return true;
    }

    public function displayInAdmin()
    {
        if ($_GET['page'] == 'pricing_category_costs') {
            //not a category specific setting!
            return false;
        }
        return true;
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $price = $this->getCost(); //people expect numbers to be positive...
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        $return = array (
            'title' => $msgs['sub_title_in_cart'],
            'canEdit' => true, //whether can edit it or not
            'canDelete' => true, //whether can remove from cart or not
            'canPreview' => false, //whether can preview the item or not
            'canAdminEditPrice' => true, //whether price can be edited in admin panel cart
            'priceDisplay' => geoString::displayPrice($price), //price to display
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
        $prev_exp = $this->get('prev_storefront_expiration');
        $prev_onhold = $this->get('prev_storefront_onhold');

        if ($duration == 0) {
            //err duration not known...
            trigger_error('ERROR TRANSACTION ORDER: Duration not known, not able to add subscription length...  User must have hit refresh or something.');
            return ;
        }
        $db = DataAccess::getInstance();

        //check to see if currently subscribed
        $table = geoAddon::getutil("storefront")->tables();
        $sql = "SELECT `subscription_id`,`expiration` FROM `$table->subscriptions` WHERE `user_id` = ? ORDER BY `expiration` LIMIT 1";
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
            if ($order && $order->getRecurringBilling() && !$this->get('trial')) {
                $recurring = $order->getRecurringBilling();
                //save the user id in case it's not set already
                $recurring->setUserId($order->getBuyer());
                $recurring->save();
                $recurringId = $recurring->getId();
            }

            if ($check_subscriptions_results->RecordCount() > 0) {
                //extend subscription period
                $show_subscription = $check_subscriptions_results->FetchRow();

                if ($show_subscription["expiration"] > geoUtil::time()) {
                    $new_expire = intval($show_subscription["expiration"] + ($duration * 86400));
                } else {
                    $new_expire = intval(geoUtil::time() + ($duration * 86400));
                }
                $table = geoAddon::getUtil("storefront")->tables();
                $sql = "UPDATE `$table->subscriptions` SET `expiration` = ?, `recurring_billing`=? WHERE `subscription_id` = ? LIMIT 1";
                $update_subscriptions_results = $db->Execute($sql, array($new_expire, $recurringId, $show_subscription["subscription_id"]));

                if (!$update_subscriptions_results) {
                    trigger_error('ERROR SQL: sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
                }
            } else {
                //no previous entries, add a new one if we are activating

                //enter new subscription period
                $table = geoAddon::getUtil("storefront")->tables();
                $new_expire = intval(geoUtil::time() + ($duration * 86400));
                $sql = "INSERT INTO `$table->subscriptions` (user_id,expiration, `recurring_billing`)	VALUES (?, ?, ?)";
                $query_data = array ($this->getOrder()->getBuyer(), $new_expire, $recurringId);
                $insert_subscriptions_results = $db->Execute($sql, $query_data);

                if (!$insert_subscriptions_results) {
                    trigger_error('ERROR TRANSACTION SQL: paypal:transaction_process() - sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
                }
            }

            $trial = ($this->get('trial') == 1) ? true : false;
            if ($trial) {
                //this is a trial period -- mark it as used in userdata

                //get list of already used trials
                $sql = "INSERT INTO `geodesic_addon_storefront_trials_used` (`user_id`, `trial_id`) VALUES (?,?)";
                $trial_result = $db->Execute($sql, array($this->getOrder()->getBuyer(), $this->get('period_id')));
                if (!$trial_result) {
                    trigger_error('ERROR SQL: sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
                }
            }

            //do initial config
            $util = geoAddon::getUtil('storefront');
            $util->addStoreDataIfNeeded($this->getOrder()->getBuyer());
        } elseif (!$activate && $already_active && $check_subscriptions_results->RecordCount() > 0) {
            //making inactive
            $show_subscription = $check_subscriptions_results->FetchRow();
            $table = geoAddon::getUtil("storefront")->tables();
            if ($prev_exp < geoUtil::time()) {
                //previous expiration time is 0 or less than current time, so remove the expiration.
                $sql = "DELETE FROM `$table->subscriptions` WHERE `subscription_id` = ? LIMIT 1";
                $result = $db->Execute($sql, array($show_subscription["subscription_id"]));
            } else {
                $sql = "UPDATE `$table->subscriptions` SET `expiration` = ? WHERE `subscription_id` = ? LIMIT 1";
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
        $msgs = geoAddon::getText('geo_addons', 'storefront');

        if (isset($_POST['c'])) {
            $item = $cart->item;

            $cart->setPricePlan($item->get('price_plan'), $item->get('category'));
            $selection_id = intval($_POST['c']['subscription_choice']);

            $planItem = geoPlanItem::getPlanItem(self::type, $item->getPricePlan(), 0);
            $periods = $planItem->get('periods');
            if (!$selection_id || !in_array($selection_id, $periods)) {
                $cart->addError()
                    ->addErrorMsg('storefront', $msgs['invalid_subscription_choice']);
                return;
            }

            //get list of already used trials

            $sql = "SELECT `trial_id` FROM `geodesic_addon_storefront_trials_used` WHERE `user_id` = ?";
            $result = $cart->db->Execute($sql, array($cart->order->getBuyer()));
            while ($line = $result->FetchRow()) {
                $trialsUsed[] = $line['trial_id'];
            }

            //sanity check to make sure these numbers actually identify trial periods
            foreach ($trialsUsed as $key => $val) {
                $val = trim($val);
                $sql = "select `trial` from `geodesic_addon_storefront_subscriptions_choices` where `period_id` = ?";
                $isTrial = $cart->db->GetOne($sql, array($val));
                if (!$isTrial) {
                    //easiest way to clear this is just to zero it...could array_splice, but that just gets messy
                    $trialsUsed[$key] = 0;
                }
            }
            $trialsUsed = implode(',', $trialsUsed);

            $sql_trials = (strlen($trialsUsed)) ? "AND `period_id` NOT IN (" . $trialsUsed . ")" : "";
            $table = geoAddon::getUtil("storefront")->tables();
            $sql = "SELECT * FROM `$table->subscriptions_choices` WHERE `period_id` = $selection_id " . $sql_trials;

            $row = $cart->db->GetRow($sql);
            if (empty($row)) {
                //none selected or valid
                $cart->addError()
                    ->addErrorMsg('storefront', $msgs['invalid_subscription_choice']);
            }
        } else {
            $cart->addError()
                ->addErrorMsg('subscription', $msgs['invalid_subscription_choice']);
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
        $table = geoAddon::getUtil("storefront")->tables();
        $sql = "SELECT * FROM `$table->subscriptions_choices` WHERE `period_id` = $selection_id";
        $row = $cart->db->GetRow($sql);
        if (empty($row)) {
            //none selected or valid - should not get here since already checked in checkVars
            $msgs = geoAddon::getText('geo_addons', 'storefront');
            $cart->addError()
                ->addErrorMsg('storefront', $msgs['invalid_subscription_choice']);
            return;
        }
        $cart->item->set('period_id', $selection_id);
        $cart->item->set('period_display', $row['display_value']);
        $cart->item->set('duration', $row['value']);
        if (!geoMaster::is('site_fees')) {
            $row['amount'] = 0;
        }
        $cart->item->setCost($row['amount']);
        $cart->item->set('trial', $row['trial']);

        //but children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
    }

    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        if ($_GET['storefront_need_sub']) {
            $cart->addErrorMsg("cart_error", $msgs['subscription_expired_explain']);
        }
        if ($cart->main_type != self::type) {
            //not adding a subscription
            return;
        }
        $planItem = geoPlanItem::getPlanItem(self::type, $cart->price_plan['price_plan_id'], 0);

        if (!$planItem->getEnabled()) {
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
        $periods = $planItem->get('periods');

        //get list of already used trials
        $sql = "SELECT `trial_id` FROM `geodesic_addon_storefront_trials_used` WHERE `user_id` = ?";
        $result = $cart->db->Execute($sql, array($cart->order->getBuyer()));
        while ($line = $result->FetchRow()) {
            $trialsUsed[] = $line['trial_id'];
        }

        //sanity check to make sure these numbers actually identify trial periods
        foreach ($trialsUsed as $key => $val) {
            $val = trim($val);
            $sql = "select `trial` from `geodesic_addon_storefront_subscriptions_choices` where `period_id` = ?";
            $isTrial = $cart->db->GetOne($sql, array($val));
            if (!$isTrial) {
                //easiest way to clear this is just to zero it...could array_splice, but that just gets messy
                $trialsUsed[$key] = 0;
            }
        }
        $trialsUsed = implode(',', $trialsUsed);

        $sql_trials = (strlen($trialsUsed) > 0) ? "AND `period_id` NOT IN (" . $trialsUsed . ")" : "";

        $table = geoAddon::getUtil("storefront")->tables();

        $sql = "SELECT * FROM `$table->subscriptions_choices` WHERE `period_id` in (" . implode(', ', $periods) . ") " . $sql_trials . " ORDER BY `value`, `amount` ASC";
        $choices_result = $cart->db->GetAll($sql);
        if ($choices_result === false) {
            trigger_error('ERROR CART SQL: Sql: ' . $sql . ' Msg: ' . $cart->db->ErrorMsg());
            return false;
        }

        $tpl = new geoTemplate('addon', 'storefront');
        $tpl->assign('storefront_messages', $msgs);
        $tpl->assign('choices', $choices_result);
        $tpl->assign('error', $cart->getErrorMsg('storefront'));

        //set selected
        $tpl->assign('selected', ($cart->item->get('period_id')) ? $cart->item->get('period_id') : 0);
        $tpl->assign('allFree', !geoMaster::is('site_fees'));
        $return['entire_box'] = $tpl->fetch('other_details.tpl');

        //text on page
        $return ['page_title1'] = $msgs['renew_purchase_title'];
        $return ['page_title2'] = $msgs['renew_purchase_sub_title'];
        $return ['page_desc'] = $msgs['renew_purchase_desc'];
        $return ['submit_button_text'] = $msgs['renew_purchase_submit_button_text'];
        $return ['cancel_text'] = $msgs['renew_purchase_cancel_text'];

        return $return;
    }

    public static function geoCart_other_detailsLabel()
    {
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        return $msgs['storefront_subscription_step'];
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
        if (isset($cart->user_data['storefront_expiration'])) {
            //already did this, no need to do again
            return;
        }
        //add it to user's data
        $table = geoAddon::getUtil('storefront')->tables();
        $row = $cart->db->GetRow(
            "SELECT `expiration`, `onhold_start_time` FROM `$table->subscriptions` WHERE `user_id`=? ORDER BY `expiration` LIMIT 1",
            array($cart->user_data['id'])
        );


        if ($row === false) {
            trigger_error('ERROR SQL: Error Msg: ' . $cart->db->ErrorMsg());
            $cart->user_data['storefront_expiration'] = 0;
            $cart->user_data['storefront_onhold'] = 0;
            die($cart->db->ErrorMsg() . "<br /> $sql");
            return;
        }
        if (!empty($row)) {
            $cart->user_data['storefront_expiration'] = $row['expiration'];
            $cart->user_data['storefront_onhold'] = $row['onhold_start_time'];
        } else {
            $cart->user_data['storefront_expiration'] = 0;
            $cart->user_data['storefront_onhold'] = 0;
        }
    }

    public static function getActionName($vars)
    {
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        if ($vars['step'] == 'my_account_links') {
            //short version
            return $msgs['extend_storefront_action_short'];
        } else {
            //action interupted text
            return $msgs['extend_storefront_action'];
        }
    }
    public static function geoCart_cartDisplay_newButton($inModule = false)
    {
        //NOTE: $inModule var is only used "internally" to signal this was called
        //from my_account_links_newButton - we do it that way to reduce code duplication

        if (self::isAnonymous()) {
            return ''; //anon never allowed for storefront!
        }
        $cart = geoCart::getInstance();

        $userId = (int)$cart->user_data['id'];
        if (!$userId) {
            return false;
        }
        $user = geoUser::getUser($userId);
        if (!$user) {
            return false;
        }

        $pricePlanId = (geoMaster::is('classifieds')) ? $user->price_plan_id : $user->auction_price_plan_id;

        $planItem = geoPlanItem::getPlanItem(self::type, $pricePlanId, 0);

        $share_fees = geoAddon::getUtil('share_fees');
        $price_plans_with_free_storefronts = geoAddon::getUtil('storefront')->getPricePlansWithFreeStorefronts();
        //Display the button, only when user is outside of their storefront expiration.
        if ($planItem->getEnabled() && (!$cart->order || ($cart->order && count($cart->order->getItem(self::type)) == 0)) && (!in_array($pricePlanId, $price_plans_with_free_storefronts))) {
            $msgs = geoAddon::getText('geo_addons', 'storefront');
            if ($inModule) {
                //really being called by my_account_links_newButton - same logic, different return value
                return array (
                    'label' => $msgs['extend_storefront_label']
                );
            } else {
                if ($cart->user_data['storefront_expiration']) {
                    return $msgs['extend_subscription_button'];
                } else {
                    return $msgs['add_subscription_button'];
                }
            }
        }
        return '';
    }

    public static function my_account_links_newButton()
    {
        return self::geoCart_cartDisplay_newButton(true);
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
     * Optional
     * Used: in User_management_information::display_user_data()
     *
     * Use this to display info on the user info page.  Stuff like displaying
     * current account balance, tokens remaining, etc.  This will appear below
     * the price plan info.
     *
     * @return array Associative array, as array ('label' => 'label (left side)', 'value' => 'value (right side)')
     */
    public static function User_management_information_display_user_data()
    {
        $userId = geoSession::getInstance()->getUserId();
        $util = geoAddon::getUtil('storefront');
        if ($util && $util->userHasCurrentSubscription($userId)) {
            $db = DataAccess::getInstance();
            //show link to edit the html placed on affiliate site
            $msgs = geoAddon::getText('geo_addons', 'storefront');
            $label = $msgs['mal_storefront_link'];

            $link = $db->get_site_setting('classifieds_url') . "?a=ap&amp;addon=storefront&amp;page=home&amp;store={$userId}";

            //let SEO (or any other addon) rewrite this link
            $link = geoAddon::triggerDisplay('rewrite_single_url', array('url' => $link, 'forceNoSSL' => true), geoAddon::FILTER);
            if (is_array($link)) {
                //addon call returned the input array instead of a url string (meaning no addon chose to rewrite the url)
                //make sure to only keep the important part
                $link = $link['url'];
            }

            $text_link = $link;
            $val = "<a href='{$link}' id='storeLink' class='data_values'>$text_link</a>";


            return array('label' => $label, 'value' => $val);
        }
    }

    public static function Admin_user_management_edit_user_form($user_id)
    {
        $user_id = intval($user_id);
        if (!$user_id) {
            return;
        }
        $db = DataAccess::getInstance();

        $sql = "select expiration from geodesic_addon_storefront_subscriptions where user_id = ?";
        $expiration = $db->GetOne($sql, array($user_id));

        if (!$expiration) {
            //no active subscription -- nothing to do here
            return;
        }

        $fields = array (
            'year' => 'storefront_subscription[expiration_year]',
            'month' => 'storefront_subscription[expiration_month]',
            'day' => 'storefront_subscription[expiration_day]',
            'hour' => 'storefront_subscription[expiration_hour]',
            'minute' => 'storefront_subscription[expiration_minute]'
        );

        $labels = array (
            'year' => 'Year',
            'month' => 'Month',
            'day' => 'Day',
            'hour' => 'Hour',
            'minute' => 'Minute'
        );
        $html = '<div class="form-group">
					<label class="control-label col-xs-12 col-sm-4">Storefront Subscription Expiration</label>
					<div class="col-xs-12 col-sm-6">
					' . geoHTML::dateSelect($fields, $labels, $expiration) . '
					</div>
				</div>';

        return $html;
    }

    public static function Admin_user_management_update_user_info($user_id)
    {
        $user_id = intval($user_id);
        if (!$user_id) {
            return;
        }
        if (!isset($_POST['storefront_subscription']) || !is_array($_POST['storefront_subscription'])) {
            //fields we're updating weren't on the page (user probably didn't have an active subscription)
            //nothing to do here. move along.
            return;
        }

        $t = $_POST['storefront_subscription']; //one-letter variable, since we're gonna use it a lot on the next couple lines ;)



        $new_expiration_time = mktime($t['expiration_hour'], $t['expiration_minute'], 0, $t['expiration_month'], $t['expiration_day'], $t['expiration_year']);

        $db = DataAccess::getInstance();

        $sql = "UPDATE geodesic_addon_storefront_subscriptions set expiration = ? where user_id = ?";
        $result = $db->Execute($sql, array($new_expiration_time, $user_id));

        if (!$result) {
            return false;
        }

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
        //only recurring so long as it is not a trial.
        return !$this->get('trial');
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
        if ($this->get('trial') == 1) {
            //no recurring for trials!
            return 0;
        }
        $duration = (int)$this->get('duration');
        $duration = $duration * 60 * 60 * 24;
        return $duration;
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
    public function getRecurringPrice()
    {
        if ($this->get('trial') == 1) {
            //no recurring for trials!
            return 0;
        }
        //use getOrderTotal to get recurring amount, so that special recurring children can alter
        //recurring price
        return $this->getOrder()->getOrderTotal();
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
        if ($this->get('trial') == 1) {
            //no recurring for trials!
            return 0;
        }
        $userId = $this->getOrder()->getBuyer();
        $userName = geoUser::userName($userId);
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        return "{$msgs['recurring_desc']} $userName (#$userId)";
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
        if ($this->get('trial') == 1) {
            //no recurring for trials!
            return 0;
        }

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
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $table = geoAddon::getutil("storefront")->tables();
        $sql = "SELECT `expiration` FROM `$table->subscriptions` WHERE `user_id` = ? ORDER BY `expiration` LIMIT 1";
        $row = $db->GetRow($sql, array($userId));
        $expire = (isset($row['expiration'])) ? (int)$row['expiration'] : 0;
        if ($expire > geoUtil::time()) {
            return $expire;
        }

        //could not find any subscription, or subscription is already expired,
        //so return recurring start date.
        return parent::getRecurringStartDate();
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

        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $table = geoAddon::getutil("storefront")->tables();
        $sql = "SELECT `subscription_id`, `expiration` FROM `$table->subscriptions` WHERE `user_id` = ? ORDER BY `expiration` LIMIT 1";
        $row = $db->GetRow($sql, array($userId));

        if (isset($row['subscription_id'])) {
            //extend subscription period
            if ($row['expiration'] > $paidUntil) {
                //the currently set expiration is ahead of what we are changing it
                //to, so keep it
                return;
            }
            $sql = "UPDATE `$table->subscriptions` SET `expiration` = ?, `recurring_billing`=? WHERE `subscription_id` = ? LIMIT 1";
            $update_subscriptions_results = $db->Execute($sql, array($paidUntil, $recurring->getId(), $row["subscription_id"]));

            if (!$update_subscriptions_results) {
                trigger_error('ERROR SQL: sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            }
        } else {
            //no previous entries, add a new one

            //enter new subscription period
            $sql = "INSERT INTO `$table->subscriptions` (user_id,expiration, `recurring_billing`) VALUES (?, ?, ?)";
            $query_data = array ($userId, $paidUntil, $recurring->getId());
            $insert_subscriptions_results = $db->Execute($sql, $query_data);

            if (!$insert_subscriptions_results) {
                trigger_error('ERROR TRANSACTION SQL: paypal:transaction_process() - sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
            }
        }

        //add row for this user to settings table, if it doesn't already exist
        $sql = "SELECT owner FROM geodesic_addon_storefront_user_settings WHERE owner = ?";
        $owner = $db->GetOne($sql, array($userId));
        if ($owner > 0 && $owner == $userId) {
            //user's settings row already exists -- nothing to do here
        } else {
            //create user settings row, so we have some place to save settings
            $sql = "INSERT INTO geodesic_addon_storefront_user_settings (owner) VALUES (?)";
            $result = $db->Execute($sql, array($userId));
            if (!$result) {
                trigger_error("ERROR SQL: failed to create settings row. MySQL said: " . $db->ErrorMsg());
            }
        }
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
        //Actually we don't really need to do that much here.  Just let the subscription
        //expire naturally since the recurring billing will no longer be paid.

        //well, except do update the recurring_billing on teh subscription
        $recurringId = (int)$recurring->getId();
        if ($recurringId) {
            $db = 1;
            include GEO_BASE_DIR . 'get_common_vars.php';
            $sql = "UPDATE `geodesic_addon_storefront_subscriptions` SET `recurring_billing`=0 WHERE `recurring_billing`=?";
            $db->Execute($sql, array($recurringId));
        }
    }
}
