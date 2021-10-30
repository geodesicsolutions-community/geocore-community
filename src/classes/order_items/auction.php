<?php

//order_items/auction.php

require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';
require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';


class auctionOrderItem extends _listing_placement_commonOrderItem
{
    var $defaultProcessOrder = 5;
    protected $type = 'auction';
    const type = 'auction';
    public function displayInAdmin()
    {
        if (!geoMaster::is('auctions')) {
            return false;
        }
        return true;
    }

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
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $tpl_vars['allow_buy_now'] = $planItem->get('allow_buy_now', 1);
        $tpl_vars['allow_buy_now_only'] = $planItem->get('allow_buy_now_only', 1);
        //Note: valid options, "close" will set live = 0, "sold" will set it to be sold but left open
        $tpl_vars['buy_now_only_none_left'] = $planItem->get('buy_now_only_none_left', 'close');

        $tpl_vars['allow_reverse'] = $planItem->get('allow_reverse');
        $tpl_vars['allow_reverse_buy_now'] = $planItem->get('allow_reverse_buy_now');
        $tpl_vars['charge_reverse_final_fees'] = $planItem->get('charge_reverse_final_fees');

        $tpl_vars['force_single_quantity'] = $planItem->get('force_single_quantity');

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);
        return $tpl->fetch('order_items/auction/plan_item_settings.tpl');
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
        $settings = $_POST['auction'];

        if (is_array($settings) && isset($settings['form_submitted'])) {
            $allow_buy_now = (int)$settings['allow_buy_now'];
            $allow_buy_now_only = ($allow_buy_now) ? $settings['allow_buy_now_only'] : 0; // if allow_buy_now not on, force to be false
            $planItem->set('allow_buy_now', $allow_buy_now);
            $planItem->set('allow_buy_now_only', $allow_buy_now_only);

            $buy_now_only_none_left = (isset($settings['buy_now_only_none_left']) && in_array($settings['buy_now_only_none_left'], array('close','sold'))) ? $settings['buy_now_only_none_left'] : 'close';
            $planItem->set('buy_now_only_none_left', $buy_now_only_none_left);

            //reverse: note that can set to "false" since we do not default to be turned on
            $allow_reverse = (isset($settings['allow_reverse']) && $settings['allow_reverse']) ? 1 : false;
            $allow_reverse_buy_now = ($allow_reverse && isset($settings['allow_reverse_buy_now']) && $settings['allow_reverse_buy_now']) ? 1 : false;
            $charge_reverse_final_fees = ($allow_reverse && isset($settings['charge_reverse_final_fees']) && $settings['charge_reverse_final_fees']) ? 1 : false;

            $planItem->set('allow_reverse', $allow_reverse);
            $planItem->set('allow_reverse_buy_now', $allow_reverse_buy_now);
            $planItem->set('charge_reverse_final_fees', $charge_reverse_final_fees);

            $planItem->set('force_single_quantity', (int)$settings['force_single_quantity']);
        }

        return true;
    }
    public function getTypeTitle()
    {
        $type = parent::getTypeTitle();
        if (!geoMaster::is('auctions')) {
            //show warning
            $type .= ' <span style="color: red;">!Disabled by License or Master Switch!</span>';
        }
        return $type;
    }
    public static function geoCart_initSteps($allPossible = false)
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        parent::$_type = self::type;
        parent::geoCart_initSteps($allPossible);
        $cart = geoCart::getInstance();
        $db = DataAccess::getInstance();

        if ($db->get_site_setting('jit_registration') && !defined('IN_ADMIN')) {
            //JIT registration turned on...  either add jit or jit_process depending on if
            //already logged in or not.
            if (self::isAnonymous()) {
                $cart->addStep(self::type . ':jit', null, null, false);
            } else {
                //the "after finished" step
                $cart->addStep(self::type . ':jit_after', null, null, false);
            }
        }
    }

    public static function geoCart_initSteps_addOtherDetails()
    {
        $children = geoOrderItem::getChildrenTypes(self::type);
        //can call directly, since this function is required.
        if (geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails', null, 'bool_true', $children)) {
            //one of the children want to display it, so return true.
            return true;
        }

        return false; //nothing to show here. return false
    }

    public static function choose_planCheckVars($applies_to = null)
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        parent::choose_planCheckVars(2);
    }

    public static function choose_planDisplay($applies_to = null)
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        parent::$_type = self::type;
        parent::choose_planDisplay(2);
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        if (!geoMaster::is('auctions')) {
            return false;
        }
        $price = $this->getCost(); //people expect numbers to be positive...
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $return = array (
            'css_class' => '',
            'title' => $msgs[500317],
            'canEdit' => true, //whether can edit it or not
            'canDelete' => true, //whether can remove from cart or not
            'canPreview' => true, //whether can preview the item or not
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price, //amount this and all children adds to the total
            'children' => false
        );
        $session_variables = $this->get('session_variables');
        $title = $this->_listingTitleDisplay($session_variables['classified_title'], $inEmail);

        if ($title) {
            $return['title'] .= " - $title";
        }

        //go through children...
        $order = $this->getOrder();
        $items = $order->getItem();
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


    public function geoCart_initItem_new($item_type = null)
    {
        if (!geoMaster::is('auctions')) {
            return false;
        }
        parent::$_type = self::type;
        self::_initAuctionPricePlan();
        return parent::geoCart_initItem_new(2);
    }

    public function geoCart_initItem_restore()
    {
        self::initSessionVars(2);
        return true;
    }

    public static function categoryCheckVars($listing_types_allowed = null, $cat_id = 0)
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        return parent::categoryCheckVars(2);
    }


    public static function categoryDisplay($listing_types_allowed = null, $onlyRecurringClassifieds = false)
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        $tpl_vars = parent::categoryDisplay(2);

        $cart = geoCart::getInstance();
        $view = geoView::getInstance();

        //set text that is specific to auctions
        //500354 = "Place an Auction"
        $tpl_vars['title1'] = $tpl_vars['txt1'] = $cart->site->messages[500354];
        //Cancel link text
        $tpl_vars['cancel_txt'] = $cart->site->messages[500355];

        //TODO: Add setting to default to always use dropdowns
        $tpl = 'links.tpl';

        if ($cart->isCombinedStep()) {
            $tpl = 'dropdowns.tpl';
        }

        $view->setBodyTpl('auction/category_choose/' . $tpl, '', 'order_items')
            ->setBodyVar($tpl_vars);

        $cart->site->display_page();
    }

    public static function detailsDisplay()
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        $cart = geoCart::getInstance();
        $cart->site->sell_type = 2;
        $view = geoView::getInstance();

        $tpl_vars = parent::detailsDisplay();

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);
        if ($planItem) {
            $tpl_vars['allow_buy_now'] = $planItem->get('allow_buy_now', 1);
            $tpl_vars['allow_buy_now_only'] = $planItem->get('allow_buy_now_only', 1);
            $tpl_vars['allow_reverse'] = $planItem->get('allow_reverse');
            $tpl_vars['allow_reverse_buy_now'] = $planItem->get('allow_reverse_buy_now');
            $tpl_vars['force_single_quantity'] = $planItem->get('force_single_quantity');

            //figure out if there is a choice for auction type
            $choices = 0;
            if ($tpl_vars['field_config']['allow_standard'] && $tpl_vars['pricePlan']['buy_now_only']) {
                //only one choice if allow standard and buy now only...  and template already knows
                //what to do with it...
                $choices++;
            } else {
                if ($tpl_vars['field_config']['allow_standard']) {
                    //standard!
                    $choices++;
                }
                if ($tpl_vars['field_config']['allow_dutch']) {
                    //dutch!
                    $choices++;
                }
                if ($tpl_vars['allow_reverse']) {
                    //reverse!
                    $choices++;
                }
            }
            $tpl_vars['auction_choices_count'] = $choices;
        }

        //500360 = Place an Auction
        $tpl_vars['txt1'] = $cart->site->messages[500360];
        //108 = "Auction Details"
        $tpl_vars['title1'] = $cart->site->messages[500361];
        //500362 = ""
        $tpl_vars['desc1'] = $cart->site->messages[500362];

        // "Preview" button
        $tpl_vars['preview_button_txt'] = $cart->site->messages[502082];

        //500364 = "Next Step >>"
        $tpl_vars['submit_button_txt'] = $cart->site->messages[500364];
        //500363 = Cancel & Remove image
        $tpl_vars['cancel_txt'] = $cart->site->messages[500363];
        $tpl_vars['listing_process_count_columns'] = $cart->db->get_site_setting('listing_process_count_columns');

        $view->setBodyTpl('auction/listing_collect_details.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);

        $cart->site->display_page();
    }

    public static function detailsCheckVars($save_session_vars = null)
    {
        parent::detailsCheckVars();

        $cart = geoCart::getInstance();
        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        if ($planItem) {
            //shouldn't have to do anything here, since these settings modify the detailsDisplay form directly
            //but put sanity checks here for anticrackability
            $allow_buy_now = $planItem->get('allow_buy_now', 1);
            $allow_buy_now_only = $planItem->get('allow_buy_now_only', 1);
            $allow_reverse = $planItem->get('allow_reverse');
            $allow_reverse_buy_now = $planItem->get('allow_reverse_buy_now');

            $auction_type = (int)$cart->site->session_variables['auction_type'];
            //make sure it is always auction type of 1
            $auction_type = ($auction_type) ? $auction_type : 1;


            if ($auction_type == 1) {
                if ($cart->site->session_variables['buy_now'] > 0 && !$allow_buy_now) {
                    $cart->site->session_variables['buy_now'] = 0;
                }
                if ($cart->site->session_variables['buy_now_only'] == 1 && !$allow_buy_now_only) {
                    $cart->site->session_variables['buy_now_only'] = 0;
                }
            } elseif ($auction_type == 2) {
                //dutch auction checks
            } elseif ($auction_type == 3) {
                //reverse auction checks
                if (!$allow_reverse_buy_now && $cart->site->session_variables['buy_now'] > 0) {
                    $cart->site->session_variables['buy_now'] = 0;
                }
                //never allowed to have buy now only for reverse auctions
                if ($cart->site->session_variables['buy_now_only']) {
                    $cart->site->session_variables['buy_now_only'] = 0;
                }
            }
        }
        self::saveFormVariables();
    }

    public static function mediaCheckVars()
    {
        parent::$_type = self::type;
        parent::mediaCheckVars();
    }

    public static function mediaProcess()
    {
        parent::$_type = self::type;
        parent::mediaProcess();
    }

    public static function mediaDisplay()
    {
        $cart = geoCart::getInstance();

        $cart->site->page_id = 10;
        $cart->site->get_text();

        $tpl_vars = $cart->getCommonTemplateVars();
        $tpl_vars['title1'] = $cart->site->messages[500379];
        $tpl_vars['title2'] = $tpl_vars['txt1'] = $cart->site->messages[500380];
        $tpl_vars['page_description'] = $cart->site->messages[500905];
        // "Preview" button
        $tpl_vars['preview_button_txt'] = $cart->site->messages[502084];
        $tpl_vars['submit_button_txt'] = $cart->site->messages[500757];
        $tpl_vars['cancel_txt'] = $cart->site->messages[500385];

        geoView::getInstance()->setBodyVar($tpl_vars);

        parent::$_type = self::type;

        parent::mediaDisplay();
    }

    /**
     * Returns data to be displayed on listing cost and features section
     *
     * @return array of data that is processed and used to display the listing cost box
     */
    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();
        if (!$cart->item || $cart->item->getType() != self::type) {
            return '';
        }
        parent::$_type = self::type;
        $return = parent::geoCart_other_detailsDisplay();
        if (!$return) {
            //probably not supposed to show this item
            //but still need to set title and stuff if there
            //are others to display
            $return = array('entire_box' => ' ');
        }

        $return ['page_title1'] = $cart->site->messages[500419];
        $return ['page_title2'] = $cart->site->messages[500420];
        $return ['page_desc'] = $cart->site->messages[500421];
        $return ['submit_button_text'] = $cart->site->messages[500422];
        $return ['preview_button_txt'] = $cart->site->messages[502086];
        $return ['cancel_text'] = $cart->site->messages[500423];

        return $return;
    }

    public static function geoCart_other_detailsCheckVars()
    {
        parent::$_type = self::type;
        return parent::geoCart_other_detailsCheckVars();
    }

    public static function geoCart_other_detailsProcess()
    {
        parent::$_type = self::type;
        return parent::geoCart_other_detailsProcess();
    }
    /**
     * Optional.  Required if in getDisplayDetails() you returned true for the array index of canPreview
     *
     */
    public function geoCart_previewDisplay($sell_type = null)
    {
        return parent::geoCart_previewDisplay(2);
    }

    public static function geoCart_payment_choicesProcess($sell_type = null)
    {
        parent::$_type = self::type;
        parent::geoCart_payment_choicesProcess(2);
    }

    public static function splashLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500494];
    }

    public static function choose_planLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500496];
    }

    public static function categoryLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500498];
    }

    public static function detailsLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500500];
    }

    public static function geoCart_other_detailsLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500506];
    }

    function getTransactionDescription()
    {
        if (!geoMaster::is('auctions')) {
            return;
        }
        return 'Auction Listing';
    }


    public static function getParentTypes()
    {
        //this is main order item, no parent types
        return array();
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
        if (!geoMaster::is('auctions') || (self::isAnonymous() && !DataAccess::getInstance()->get_site_setting('jit_registration'))) {
            return '';
        }

        //see if allowed to by check
        $cart = geoCart::getInstance();
        if (!($cart->user_data['restrictions_bitmask'] & 1)) {
            //listing placement not allowed
            return '';
        }

        //check to make sure creating new auctions is turned on (separate from the geoPC::is_auctions master switch)
        if (!$cart->db->get_site_setting('allow_new_auctions')) {
            return '';
        }

        //see if max ads allowed is 0
        $maxAllowed = $cart->db->GetOne("SELECT `max_ads_allowed` FROM " . geoTables::price_plans_table . " WHERE `price_plan_id`=" . (int)$cart->user_data['auction_price_plan_id']);
        if ($cart->user_data['auction_price_plan_id'] && $maxAllowed == 0) {
            //max number of listings is 0, don't display add button
            return '';
        }
        $msgs = DataAccess::getInstance()->get_text(true);
        if ($inModule) {
            //really being called by my_account_links_newButton - same logic, different return value
            return array (
                'icon' => $msgs[500634],
                'label' => $msgs[500635]
            );
        } else {
            if (!$msgs) {
                //haven't gotten text for this page yet -- get it explicitly from cart main
                $msgs = DataAccess::getInstance()->get_text(true, 10202);
            }
            return $msgs[500251];
        }
    }

    public static function my_account_links_newButton()
    {
        return self::geoCart_cartDisplay_newButton(true);
    }

    public static function adminItemDisplay($item_id)
    {
        if (!$item_id) {
            return '';
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() != self::type) {
            return '';
        }

        $info = '';
        $info .= geoHTML::addOption('Item Type', 'Auction Listing');
        parent::$_type = self::type;
        $info .= parent::adminItemDisplay($item_id);
        return $info;
    }

    public static function _initAuctionPricePlan()
    {
        $cart = geoCart::getInstance();

        if ($cart->price_plan['applies_to'] == 2) {
            //nothing to do, price plan already is auction price plan.
            return;
        }
        if (!is_object($cart->item) || $cart->item->getType() !== self::type) {
            //don't do anything if the item is not set
            return;
        }
        $cat = (is_object($cart->item)) ? $cart->item->getCategory() : 0;
        $plan = (is_object($cart->item)) ? $cart->item->getPricePlan() : 0;
        if (!$plan) {
            $plan = $cart->user_data['auction_price_plan_id'];
        }
        $cart->setPricePlan($plan, $cat);
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
            return $msgs[500636];
        } else {
            //action interupted text
            //text "placing new auction"
            return $msgs[500391];
        }
    }
    /**
     * Optional.
     * Used: in file classes/cron/close_listings.php
     *
     * This is called for each listing that is being closed.  Note that the following things are
     * automatically done: the "live" column is set to 0, and user favorites for the listing
     * are removed.  Anything beyond that is up to being done in this function.
     *
     * @param array $vars Associative array, array('listing_details' => array(), 'cron_util' => close_listings_util object)
     */
    public static function cron_close_listings($vars)
    {
        $listing = $vars['listing']; //a geoListing item.  see that class for more details.
        $cron = geoCron::getInstance();

        if ($listing->item_type != 2) {
            //not an auction
            return;
        }
        $db = DataAccess::getInstance();
        $cron->log('Top of auction cron close listings.', __line__ . ' - ' . __file__);
        $current_time = geoUtil::time();
        $dutch_bidders = null;

        $reverse = ($listing->auction_type == 3);
        $reserve_met = ((!$reverse && $listing->current_bid >= $listing->reserve_price) || ($reverse && $listing->current_bid <= $listing->reserve_price));

        //Do anything specific to this type of item here.
        if ($cron->verbose) {
            echo __line__ . ' - ' . __file__ . ' - ' . $listing->current_bid . " is the current_bid<br/>\n";
            echo $listing->id . " is the id<br/>\n";
            echo $listing->title . " is the title<br/>\n";
            echo $listing->seller . " is the seller<br/>\n";
            echo $listing->auction_type . " is auction_type<br/>\n";
            echo $listing->reserve_price . " is reserver price<br/>\n";
            echo $current_time . " is current time and " . $listing->ends . " is auction ends<br/>\n";
        }
        $high_bidder = false;
        if ($listing->auction_type == 1 || $reverse) {
            //standard or reverse auction single winner, set high bidder
            $high_bidder = self::_getHighBidder($listing->id);
            $cron->log('Hi bidder: ' . print_r($high_bidder, 1), __line__ . ' - ' . __file__);
            if (isset($high_bidder['bidder']) && $high_bidder['bidder']) {
                $listing->high_bidder = $high_bidder['bidder'];
            }
        }

        $listing->live = 0;
        $listing->final_price = $listing->current_bid;

        if ($reserve_met && $high_bidder) {
            //insert into feedback table
            $sql = "SELECT * FROM " . geoTables::auctions_feedbacks_table . "
				where rated_user_id = " . $listing->seller . "
				and rater_user_id = " . $high_bidder['bidder'] . "
				and auction_id = " . $listing->id;
            $check_feedback_result = $db->Execute($sql);
            $cron->log($sql, __line__ . ' - ' . __file__);
            if (!$check_feedback_result) {
                $cron->log('DB Error, SQL: ' . $sql . " Error: " . $db->ErrorMsg(), __line__ . ' - ' . __file__);
                return false;
            } elseif ($check_feedback_result->RecordCount() == 0) {
                $sql = "insert into " . geoTables::auctions_feedbacks_table . "
					(rated_user_id,rater_user_id,date,auction_id)
					values
					(" . $listing->seller . "," . $high_bidder['bidder'] . "," . $current_time . "," . $listing->id . ")";
                $insert_feedback_result = $db->Execute($sql);
                $cron->log($sql, __line__ . ' - ' . __file__);
                if (!$insert_feedback_result) {
                    $cron->log('DB Error, SQL: ' . $sql . " Error: " . $db->ErrorMsg(), __line__ . ' - ' . __file__);
                    return false;
                }
            }

            $sql = "select * from " . geoTables::auctions_feedbacks_table . "
				where rated_user_id = " . $high_bidder['bidder'] . "
				and rater_user_id = " . $listing->seller . "
 				and auction_id = " . $listing->id;
            $check_feedback_result = $db->Execute($sql);
            $cron->log($sql, __line__ . ' - ' . __file__);
            if (!$check_feedback_result) {
                $cron->log('DB Error, sql: ' . $sql . ", error: " . $db->ErrorMsg(), __line__ . ' - ' . __file__);
                return false;
            } elseif ($check_feedback_result->RecordCount() == 0) {
                $sql = "insert into " . geoTables::auctions_feedbacks_table . "
					(rated_user_id,rater_user_id,date,auction_id)
					values
					(" . $high_bidder['bidder'] . "," . $listing->seller . "," . $current_time . "," . $listing->id . ")";
                $insert_feedback_result = $db->Execute($sql);
                $cron->log($sql, __line__ . ' - ' . __file__);
                if (!$insert_feedback_result) {
                    $cron->log('DB Error, sql: ' . $sql . ", error: " . $db->ErrorMsg(), __line__ . ' - ' . __file__);
                    return false;
                }
            }
        }

        $seller_info = self::_getUserData($listing->seller);
        $pre = $listing->precurrency;
        $post = $listing->postcurrency;
        if ($listing->auction_type == 1 || $reverse) {
            $msgs = $db->get_text(true, 10172);

            //standard or reverse single item auction
            if ($high_bidder) {
                $high_bidder_info = self::_getUserData($high_bidder['bidder']);
                if (geoPC::is_ent() && ($listing->current_bid >= $listing->reserve_price)) {
                    //hook for seller/buyer transactions, in case anything need be done here.
                    $sb = geoSellerBuyer::getInstance();

                    $vars = array (
                        'listing_id' => $listing->id,
                        'winning_bidder_id' => $high_bidder['bidder'],
                        'listing' => $listing,
                        'final_price' => $high_bidder['bid']
                    );

                    geoSellerBuyer::callUpdate('listingClosed', $vars);
                }
            } else {
                $high_bidder_info = false;
            }

            //send email to seller and winner
            //send to seller
            self::_emailBuyerAndSeller($listing, $high_bidder, $high_bidder_info, $seller_info);
        } else {
            $msgs = $db->get_text(true, 10166);

            //dutch auction
            //get all bids starting with highest first
            $sql = "select * from " . geoTables::bid_table . " where auction_id=" . $listing->id . " order by bid desc,time_of_bid asc";
            $bid_result = $db->Execute($sql);
            $cron->log($sql . "<br/>\n", __line__ . ' - ' . __file__);
            if (!$bid_result) {
                $cron->log($sql . "<br/>\n", __line__ . ' - ' . __file__);
                return false;
            } elseif ($bid_result->RecordCount() > 0) {
                $total_quantity = $listing->quantity;
                //echo "total items sold - ".$total_quantity."<br/>\n";
                $final_dutch_bid = 0;
                $seller_report = "";
                $dutch_bidders = array();

                $msgs = $db->get_text(true, 10172);

                $dutch_quantities = array();
                while (($show_bidder = $bid_result->FetchRow()) && ($total_quantity > 0)) {
                    if ($show_bidder['bid'] < $listing->reserve_price) {
                        //this bid didn't beat the reserve price -- skip it
                        continue;
                    }
                    $bidder = $show_bidder['bidder'];
                    $quantity_bidder_receiving = 0;
                    if ($show_bidder['quantity'] < $total_quantity) {
                        $dutch_quantities[$bidder] = $show_bidder['quantity'];
                        $total_quantity -= $show_bidder['quantity'];
                    } else {
                        $dutch_quantities[$bidder] = $total_quantity;
                        $total_quantity = 0;
                    }
                    $final_dutch_bid = $show_bidder['bid'];
                }
                $cron->log($final_dutch_bid . " is final_dutch_bid<br/>\n", __line__ . ' - ' . __file__);
                $dutch_bidders = array();
                $display_final_bid = geoString::displayPrice($final_dutch_bid, $pre, $post);
                $seller_report = '';
                $quantity_sold = 0;
                foreach ($dutch_quantities as $bidder => $quantity) {
                    $cron->log($bidder . " is bidder and " . $quantity . " is the quantity received<br/>\n", __line__ . ' - ' . __file__);
                    $quantity_sold += $quantity;
                    $local_key = count($dutch_bidders);
                    $dutch_bidders[$local_key]["bidder"] = $bidder;
                    $dutch_bidders[$local_key]["quantity"] = $quantity;
                    $dutch_bidders[$local_key]["bid"] = $final_dutch_bid;
                    $bidder_info = self::_getUserData($bidder);
                    $seller_report .= $msgs[102769] . $bidder_info['username'] . " ( " . $bidder_info['email'] . " )\n";
                    $seller_report .= $msgs[500049] . $quantity . " @ " . $display_final_bid . "\n";
                    $bid_total = $quantity * $final_dutch_bid;
                    $seller_report .= $msgs[102779] . geoString::displayPrice($bid_total, $pre, $post) . "\n";
                    $seller_report .= self::_getAdditionalFeeTextDutch($listing, $dutch_bidders[$local_key]) . "\n";
                    $seller_report .= $msgs[500050] . "\n";
                }
                $seller_report = "\n\n" . $msgs[102779] . geoString::displayPrice(($quantity_sold * $final_dutch_bid), $pre, $post) . "\n\n" . $seller_report;

                //save final dutch bid as final_price and the winning bidders.
                $dutch_bidder_users = array();
                foreach ($dutch_bidders as $key => $value) {
                    if ($listing->reserve_price <= $dutch_bidders[$key]["bid"]) {
                        $dutch_bidder_users[] = $dutch_bidders[$key]['bidder'];
                    }
                }
                $sql = "update " . geoTables::classifieds_table . "
					set final_price = " . $final_dutch_bid;
                if (count($dutch_bidder_users) > 0) {
                    $sql .= ', high_bidder = \'' . implode('|', $dutch_bidder_users) . '\' ';
                }
                $sql .= "
					where id = " . $listing->id;
                $update_dutch_bid_result = $db->Execute($sql);
                $cron->log($sql . "<br/>\n", __line__ . ' - ' . __file__);
                if (!$update_dutch_bid_result) {
                    $cron->log('Error running query: ' . $sql . " error: " . $db->ErrorMsg(), __line__ . ' - ' . __file__);
                    return false;
                }
                //send email to winning dutch bidder(s)
                self::_emailWinningDutchBidders($listing, $final_dutch_bid, $seller_info, $dutch_bidders);
                //send email to seller
                self::_emailDutchAuctionSeller($listing, $seller_report, $seller_info);
            } else {
                //no bids for this dutch auction
                //send email to seller
                self::_emailDutchAuctionSellerNoBidders($listing);
            }
        }
        return true;
    }

    /**
     * Needed by archive_listings()
     */
    private static function _getHighBidder($auction_id = 0)
    {
        $db = DataAccess::getInstance();
        $auction_id = intval($auction_id);

        $listing = geoListing::getListing($auction_id);

        //figure out what order to go by based on if reverse auction or not
        $sort_order = ($listing && $listing->auction_type == 3) ? 'ASC' : 'DESC';

        $sql = "select * from " . geoTables::bid_table . " where auction_id=$auction_id order by bid $sort_order,time_of_bid asc limit 1";
        return $db->GetRow($sql);
    }

    private static function _getUserData($user_id = 0)
    {
        return geoUser::getUser($user_id)->toArray();
    }

    /**
     * Function to e-mail the buyer and seller.
     */
    private static function _emailBuyerAndSeller($listing, $high_bidder, $high_bidder_info, $seller_info)
    {
        //send email to seller and winner
        //send to seller
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true);
        $pre = $listing->precurrency;
        $post = $listing->postcurrency;
        $toSeller = new geoTemplate('system', 'emails');
        $toSeller->assign('salutation', geoUser::getUser($seller_info['id'])->getSalutation());
        $reverse = ($listing->auction_type == 3);

        $additional_fees = geoListing::getAuctionAdditionalFees($listing->id);
        if ($additional_fees) {
            //figure out the grand total
            $total = $additional_fees['raw']['total'] + $listing->current_bid;
            $additional_fees['grandTotal'] = geoString::displayPrice($total, $listing->precurrency, $listing->postcurrency);
        }

        if (($listing->reserve_price == 0 || ((!$reverse && $listing->reserve_price <= $listing->current_bid) || ($reverse && $listing->reserve_price >= $listing->current_bid))) && $high_bidder && $listing->current_bid != 0) {
            //successful body
            $reserve_met = true;
            if ($reverse) {
                $toSeller->assign('messageBody', $msgs[501008]);
            } else {
                $toSeller->assign('messageBody', $msgs[102764]);
            }

            $toSeller->assign('sellerInfo', $seller_info);
            $toSeller->assign('auction', $listing->toArray());

            $toSeller->assign('highBidderInfo', $high_bidder_info);

            $toSeller->assign('finalBid', geoString::displayPrice($listing->current_bid, $pre, $post));
            $toSeller->assign('finalBidLabel', $msgs[102779]);
            $toSeller->assign('additionalFees', $additional_fees);
            $toSeller->assign('auctionSuccess', true);
        } else {
            //auction unsuccessful -- check to see if we want to skip sending this email
            if (!$db->get_site_setting('notify_seller_unsuccessful_auction')) {
                return true;
            }
            $toSeller->assign('messageBody', $msgs[102765]);
        }
        $toSeller->assign('listingURL', $listing->getFullUrl());
        $toSeller->assign('listingTitle', geoString::fromDB($listing->title));

        if ($reverse) {
            $message_data["subject"] = $msgs[501009];
        } else {
            $message_data["subject"] = $msgs[102766];
        }
        $message_data["message"] = $toSeller->fetch('auctions/auction_complete_seller.tpl');

        $cron = geoCron::getInstance();
        if ($cron->verbose) {
            echo "SENDING SELLER CLOSE EMAIL REG - " . $listing->id . " - " . $listing->seller . " - <br/><br/><pre>";
            var_dump($message_data);
            echo "</pre>\n";
        }
        geoEmail::sendMail($seller_info['email'], $message_data["subject"], $message_data["message"], 0, 0, 0, 'text/html');

        $toBuyer = new geoTemplate('system', 'emails');
        $msgs = $db->get_text(true, 10174);

        if ($reserve_met) {
            //send to winning bidder

            $toBuyer->assign('salutation', ($high_bidder_info) ? geoUser::getUser($high_bidder_info['id'])->getSalutation() : '');
            if ($reverse) {
                $toBuyer->assign('messageBody', $msgs[501010]);
            } else {
                $toBuyer->assign('messageBody', $msgs[102767]);
            }
            $toBuyer->assign('auction', $listing->toArray());

            $toBuyer->assign('highBidderInfo', $high_bidder_info);
            $toBuyer->assign('sellerInfo', $seller_info);
            $toBuyer->assign('listingTitle', geoString::fromDB($listing->title));
            $toBuyer->assign('listingURL', $listing->getFullUrl());
            $toBuyer->assign('finalBid', geoString::displayPrice($listing->current_bid, $pre, $post));
            $toBuyer->assign('finalBidLabel', $msgs[102770]);
            $toBuyer->assign('additionalFees', $additional_fees);

            if (geoPC::is_ent()) {
                //see if there should be seller to buyer text
                $vars = array(
                    'listing_id' => $listing->id,
                    'winning_bidder_id' => $high_bidder['bidder'],
                    'listing' => $listing,
                    'final_price' => $listing->current_bid
                );
                $sb_links = geoSellerBuyer::callDisplay('displayPaymentLinkWinningBidderEmail', $vars);
                if (strlen($sb_links) > 0) {
                    $toBuyer->assign('sellerBuyerInfo', $sb_links);
                }
            }

            $message_data["subject"] = $msgs[102768];
            $message_data["message"] = $toBuyer->fetch('auctions/auction_complete_buyer.tpl');

            geoEmail::sendMail($high_bidder_info['email'], $message_data["subject"], $message_data["message"], 0, 0, 0, 'text/html');
        }
    }

    /**
     * Function to get additional fee text for seller.
     *
     * @deprecated Version 7.2.0 (april 3 2013), will be removed in future release
     */
    private static function _getAdditionalFeeTextSeller($listing)
    {
        //display any optional fields that add to the cost.
        $additional_fees = array ( 'total' => 0);
        $message_data = '';
        $db = DataAccess::getInstance();
        $pre = $listing->precurrency;
        $post = $listing->postcurrency;
        if (geoPC::is_ent()) {
            $userId = $listing->seller;
            $groupId = ($userId) ? geoUser::getUser($userId)->group_id : 0;

            $fields = geoFields::getInstance($groupId, $listing->category);
            for ($i = 1; $i < 21; $i++) {
                //go through all the optional fields, see if they add cost, and if they do,
                //see if the value actually adds any cost (not 0 or blank field)
                $option = 'optional_field_' . $i;

                if ($fields->$option->field_type == 'cost' && $listing->$option > 0) {
                    //this optional field needs to be displayed.
                    $additional_fees[$i] = geoString::displayPrice($listing->$option, $pre, $post);
                    $additional_fees['total'] += $listing->$option;
                }
            }
        }
        if ($additional_fees['total'] > 0) {
            $msgs = $db->get_text(true);
            //there are additional costs to display!
            $message_data .= $msgs[500037] . "\n";
            foreach ($additional_fees as $key => $cost) {
                //go through all the additional costs and display them
                if ($key != 'total') {
                    //don't display the total twice!
                    $message_data .= $cost . "\n";
                }
            }
            //display the additional fee total.
            $message_data .= $msgs[500040] . geoString::displayPrice($additional_fees['total'], $pre, $post) . "\n\n";
            //display the grand total
            $grand_total = $listing->current_bid + $additional_fees['total'];
            $grand_total = geoString::displayPrice($grand_total, $pre, $post);
            $message_data .= $msgs[500038] . $grand_total . "\n\n";
            //display the additional fee disclaimer
            $message_data .= $msgs[500039] . "\n\n";
        }
        return $message_data;
    }



    /**
     * Function to get additional fee text for bidder.
     *
     * @deprecated Version 7.2.0 (april 3 2013), will be removed in future release
     */
    private static function _getAdditionalFeeTextBidder($listing)
    {
        //display any optional fields that add to the cost.
        $additional_fees = array ( 'total' => 0);
        $message_data = '';
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true);
        $pre = $listing->precurrency;
        $post = $listing->postcurrency;
        for ($i = 1; $i < 21; $i++) {
            //go through all the optional fields, see if they add cost, and if they do,
            //see if the value actually adds any cost (not 0 or blank field)
            $option = 'optional_field_' . $i;
            $userId = $listing->seller;
            $groupId = ($userId) ? geoUser::getUser($userId)->group_id : 0;

            $fields = geoFields::getInstance($groupId, $listing->category);
            if ($fields->$option->field_type == 'cost' && $listing->$option > 0) {
                //this optional field needs to be displayed.
                $additional_fees[$i] = geoString::displayPrice($listing->$option, $pre, $post);
                $additional_fees['total'] += $listing->$option;
            }
        }
        if ($additional_fees['total'] > 0) {
            //there are additional costs to display!
            $message_data .= $msgs[500041] . "\n";
            foreach ($additional_fees as $key => $cost) {
                //go through all the additional costs and display them
                if ($key != 'total') {
                    //don't display the total twice!
                    $message_data .= $cost . "\n";
                }
            }
            //display the additional fee total.
            $message_data .= $msgs[500042] . geoString::displayPrice($additional_fees['total'], $pre, $post) . "\n\n";
            //display the grand total
            $grand_total = $listing->current_bid + $additional_fees['total'];
            $grand_total = geoString::displayPrice($grand_total, $pre, $post);
            $message_data .= $msgs[500043] . $grand_total . "\n\n";
            //display the additional fee disclaimer
            $message_data .= $msgs[500044] . "\n\n";
        }
        return $message_data;
    }

    /**
     * Function to get additional fee text for dutch end auction
     *
     * NOTE: This is NOT deprecated, because it is still used for seller report
     * which is generated in PHP, not with template
     */
    private static function _getAdditionalFeeTextDutch($listing, $value)
    {
        $additional_fees = geoListing::getAuctionAdditionalFees($listing->id);
        $message_data = '';
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true);
        $pre = $listing->precurrency;
        $post = $listing->postcurrency;

        if ($additional_fees && $additional_fees['raw']['total'] > 0) {
            //there are additional costs to display!
            $message_data .= $msgs[500045] . "\n";
            foreach ($additional_fees['formatted'] as $key => $cost) {
                //go through all the additional costs and display them
                if ($key != 'total') {
                    //don't display the total twice!
                    $message_data .= $cost . "\n";
                }
            }
            //display the additional fee total.
            $message_data .= $msgs[500046] . ' ' . $additional_fees['formatted']['total'] . "\n";
            //display the grand total
            $total_per_item = $value["bid"] + $additional_fees['raw']['total'];
            $grand_total = $total_per_item * $value["quantity"];
            $total_per_item = geoString::displayPrice($total_per_item, $pre, $post);
            $grand_total = geoString::displayPrice($grand_total, $pre, $post);
            $message_data .= $msgs[500052] . $total_per_item . "\n\n";
            $message_data .= $msgs[500047] . $grand_total . "\n\n";
            //display the additional fee disclaimer
            $message_data .= $msgs[500048] . "\n\n";
        }
        return $message_data;
    }

    /**
     * Function to e-mail the winning dutch bidders, and also sets up feedback so they can do feedback
     */
    private static function _emailWinningDutchBidders($listing, $final_dutch_bid, $seller_info, $dutch_bidders)
    {
        if (!is_array($dutch_bidders) || !count($dutch_bidders)) {
            return;
        }
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true);

        $tpl = new geoTemplate('system', 'emails');
        $subject = $msgs[102771] . geoString::fromDB($listing->title);

        $pre = $listing->precurrency;
        $post = $listing->postcurrency;
        $display_final_bid = geoString::displayPrice($final_dutch_bid, $pre, $post);
        $tpl->assign('finalBid', $display_final_bid);
        $tpl->assign('messageBody', $msgs[102772]);
        $tpl->assign('quantityWonLabel', $msgs[102773]);
        $tpl->assign('totalBidLabel', $msgs[102774]);
        $tpl->assign('sellerInfoLabel', $msgs[102775]);
        $tpl->assign('footerText', $msgs[102776]);
        $tpl->assign('sellerInfo', $seller_info);
        $tpl->assign('listingURL', $listing->getFullUrl());

        $additional_fees = geoListing::getAuctionAdditionalFees($listing->id);

        foreach ($dutch_bidders as $key => $value) {
            if ($listing->reserve_price > $dutch_bidders[$key]['bid']) {
                //this one doesnt get an e-mail for winning
                continue;
            }

            $bidder_info = self::_getUserData($dutch_bidders[$key]["bidder"]);
            $tpl->assign('salutation', geoUser::getUser($bidder_info['id'])->getSalutation());
            $tpl->assign('quantityWon', $value['quantity']);
            $bid_total = $value['quantity'] * $final_dutch_bid;
            $tpl->assign('totalBid', geoString::displayPrice($bid_total, $pre, $post));

            if ($additional_fees) {
                $total_per_item = $value["bid"] + $additional_fees['raw']['total'];
                $grand_total = $total_per_item * $value["quantity"];
                $total_per_item = geoString::displayPrice($total_per_item, $pre, $post);
                $grand_total = geoString::displayPrice($grand_total, $pre, $post);
                $additional_fees['total_per_item'] = $total_per_item;
                $additional_fees['grandTotal'] = $grand_total;
                $tpl->assign('additionalFees', $additional_fees);
            }

            $message = $tpl->fetch('auctions/dutch/auction_complete_dutch_winners.tpl');


            geoEmail::sendMail($bidder_info['email'], $subject, $message, 0, 0, 0, 'text/html');


            //enter ability to make feedback

            $sql = "select * from " . geoTables::auctions_feedbacks_table . "
				where rated_user_id = " . $dutch_bidders[$key]["bidder"] . "
				and rater_user_id = " . $listing->seller . "
				and auction_id = " . $listing->id;
            $check_feedback_result = $db->Execute($sql);

            if (!$check_feedback_result) {
                trigger_error('ERROR SQL: DB Error, sql: ' . $sql . " Error: " . $db->ErrorMsg());
                return false;
            }
            if ($check_feedback_result->RecordCount() == 0) {
                $sql = "insert into " . geoTables::auctions_feedbacks_table . "
					(rated_user_id,rater_user_id,date,auction_id)
					values
					(" . $dutch_bidders[$key]["bidder"] . "," . $listing->seller . "," . geoUtil::time() . "," . $listing->id . ")";
                $insert_feedback_result = $db->Execute($sql);

                if (!$insert_feedback_result) {
                    trigger_error('ERROR SQL: DB Error, sql: ' . $sql . " Error: " . $db->ErrorMsg());
                    return false;
                }
            }

            $sql = "select * from " . geoTables::auctions_feedbacks_table . "
				where rated_user_id = " . $listing->seller . "
				and rater_user_id = " . $dutch_bidders[$key]["bidder"] . "
				and auction_id = " . $listing->id;
            $check_feedback_result = $db->Execute($sql);

            if (!$check_feedback_result) {
                trigger_error('ERROR SQL: DB Error, sql: ' . $sql . " Error: " . $db->ErrorMsg());
                return false;
            }
            if ($check_feedback_result->RecordCount() == 0) {
                $sql = "insert into " . geoTables::auctions_feedbacks_table . "
					(rated_user_id,rater_user_id,date,auction_id)
					values
					(" . $listing->seller . "," . $dutch_bidders[$key]["bidder"] . "," . geoUtil::time() . "," . $listing->id . ")";
                $insert_feedback_result = $db->Execute($sql);

                if (!$insert_feedback_result) {
                    trigger_error('ERROR SQL: DB Error, sql: ' . $sql . " Error: " . $db->ErrorMsg());
                    return false;
                }
            }
        }
    }



    /**
     * Function to e-mail the dutch auction seller when there are winning bidders.
     */
    private static function _emailDutchAuctionSeller($listing, $seller_report, $seller_info)
    {
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true);

        $toSeller = new geoTemplate('system', 'emails');
        $toSeller->assign('salutation', geoUser::getUser($listing->seller)->getSalutation());
        $toSeller->assign('messageBody', $msgs[102778]);
        $toSeller->assign('isDutch', true);
        $toSeller->assign('dutchSeparator', $msgs[500050]);
        $toSeller->assign('dutchResults', $seller_report);
        $toSeller->assign('dutchFooter', $msgs[102780]);
        $toSeller->assign('listingURL', $listing->getFullUrl());

        $toSeller->assign('seller', $seller_info);
        $toSeller->assign('auction', $listing);


        $subject = $msgs[102777] . geoString::fromDB($listing->title);
        $message = $toSeller->fetch('auctions/auction_complete_seller.tpl');

        geoEmail::sendMail($seller_info['email'], $subject, $message, 0, 0, 0, 'text/html');
    }

    /**
     * Function to e-mail the winning dutch bidders.
     */
    private static function _emailDutchAuctionSellerNoBidders($listing)
    {
        //no bids for this dutch auction
        //send email to seller
        $db = DataAccess::getInstance();
        if (!$db->get_site_setting('notify_seller_unsuccessful_auction')) {
            //don't inform seller of unsuccessful auction
            return true;
        }

        $seller = geoUser::getUser($listing->seller);

        $msgs = $db->get_text(true);
        $toSeller = new geoTemplate('system', 'emails');
        $toSeller->assign('salutation', geoUser::getUser($listing->seller)->getSalutation());
        $toSeller->assign('messageBody', $msgs[102781]);
        $toSeller->assign('listingURL', $listing->getFullUrl());
        $toSeller->assign('seller', $seller);
        $toSeller->assign('auction', $listing);

        $subject = $msgs[102777] . geoString::fromDB($listing->title);
        $message = $toSeller->fetch('auctions/auction_complete_seller.tpl');

        geoEmail::sendMail($seller_info['email'], $subject, $message, 0, 0, 0, 'text/html');
    }

    /**
     * Easy way to identify items that are Listing parent items
     * (i.e. classified and auction items)
     *
     * superclass returns false, so only items that directly represent listings need this function
     */
    public function isListingItem()
    {
        return true;
    }

    protected static function anonymousAllowed()
    {
        if (!geoMaster::is('auctions')) {
            return false;
        }

        $db = DataAccess::getInstance();

        if ($db->get_site_setting('jit_registration')) {
            return true;
        }

        return false;
    }

    public static function geoCart_canCombineSteps()
    {
        return geoMaster::is('auctions');
    }
}
