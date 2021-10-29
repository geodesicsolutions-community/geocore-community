<?php

//order_items/auction_final_fees.php
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


class auction_final_feesOrderItem extends geoOrderItem
{
    protected $type = "auction_final_fees";
    const type = 'auction_final_fees';
    protected $defaultProcessOrder = 50;
    const defaultProcessOrder = 50;


    /**
     * Required.
     *
     * @return bool
     */
    public function displayInAdmin()
    {
        return geoMaster::is('auctions');
    }
    public function adminDetails()
    {
        $listing = geoListing::getListing($this->get('listing'));
        $title = 'Final Fees';
        $db = DataAccess::getInstance();
        if (is_object($listing)) {
            $title .= " (for Listing #{$listing->id}: " . geoString::fromDB($listing->title) . ")";
        } else {
            $title .= ' (for Listing #' . $this->get('listing') . ')';
        }

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    public static function adminItemDisplay($item_id)
    {
        $html = '';

        $item = geoOrderItem::getOrderItem($item_id);

        if (!is_object($item) || $item->getType() != self::type) {
            return '';
        }

        $listing_id = $item->get('listing');
        $listing = geoListing::getListing($listing_id);
        $l_info = ($listing) ? '# ' . $listing->id . ' : ' . geoString::fromDB($listing->title) : '# ' . $listing_id;

        $html .= geoHTML::addOption('Listing', $l_info);
        $final_bid = $item->get('final_bid');

        if ($listing) {
            $final_bid = geoString::displayPrice($final_bid, $listing->precurrency, $listing->postcurrency);
        } else {
            $final_bid = geoString::displayPrice($final_bid);
        }

        $html .= geoHTML::addOption('Final Bid', $final_bid);
        if ($item->get('conversion_rate') != 1) {
            //also show bid converted
            $converted = geoString::displayPrice($item->get('converted_final_bid'));
            $html .= geoHTML::addOption('Final Bid Converted to Site Currency', $converted);
        }
        $percent = $item->get('final_fee_percentage');
        if ($percent > 0) {
            $cost = ceil($percent * $item->get('converted_final_bid')) / 100;
            $cost = geoString::displayPrice($cost);
            $html .= geoHTML::addOption('Percentage Charge', "$cost ({$percent}%)");
        }
        $fixed = $item->get('final_fee_fixed');
        if ($fixed > 0) {
            $fixed = geoString::displayPrice($fixed);
            $html .= geoHTML::addOption('Fixed Charge', $fixed);
        }
        $total = geoString::displayPrice($item->getCost());
        $html .= geoHTML::addOption('Total Final Fee Charge', $total);

        //Call children and let them display info about themselves as well
        $children = geoOrderItem::getChildrenTypes(self::type);
        $html .= geoOrderItem::callDisplay('adminItemDisplay', $item_id, '', $children);

        return $html;
    }

    /**
     * Optional.
     * Used: in Admin_site::display_user_data() (in file admin/admin_site_class.php)
     *
     * Can be used to display or gather information for a specific user, when viewing the user's details
     * inside the admin.  Useful for things like displaying a site balance, for example.
     *
     * @param int $user_id
     * @return string Text to add to page.
     */
    public static function Admin_site_display_user_data($user_id)
    {
        $db = DataAccess::getInstance();

        //figure out if use has any final fees
        $sql = "SELECT DISTINCT o.`id` FROM " . geoTables::order . " as o, " . geoTables::order_item . " as oi WHERE oi.order=o.id AND oi.type='auction_final_fees' AND o.`buyer`=? AND o.status != 'active'";

        $rows = $db->GetAll($sql, array(intval($user_id)));
        if ($rows === false) {
            trigger_error('ERROR SQL: Sql: ' . $sql . ' Error msg: ' . $db->ErrorMsg());
            return '';
        }
        if (count($rows) == 0) {
            //nothing found
            return '';
        }
        $final_fees = 'Total (Fixed final fee + % of adjusted final bid) [link to auction]<br />';
        $base_url = $db->get_site_setting('classifieds_url') . '?a=2&amp;b=';
        foreach ($rows as $row) {
            $order = geoOrder::getOrder($row['id']);
            $items = $order->getItem(self::type);
            $allItems = $order->getItem();
            $moreInCart = (count($allItems) > count($items));
            foreach ($items as $item) {
                $listing = $item->get('listing');
                $total = geoString::displayPrice($item->getCost());
                $fixed = geoString::displayPrice($item->get('final_fee_fixed'));
                $percent = $item->get('final_fee_percentage') . '%';
                $final_bid = geoString::displayPrice($item->get('final_bid'));
                $conversion_rate = $item->get('conversion_rate');
                $adjusted_bid = geoString::displayPrice($item->get('converted_final_bid'));

                $listing = "<a href='$base_url{$listing}' target='_new'>[ View Auction ]</a>";

                $final_fees .= "$total ($fixed + $percent of $adjusted_bid) $listing<br />";
            }
        }
        //TODO: clean up and add some way to process final fees
        $html = geoHTML::addOption('Un-paid Auction Final Fees:', $final_fees);
        return $html;
    }

    public static function geoCart_cartProcess()
    {
        //this is where we are going to see if final fees are possible, if they
        //are then throw an error if cart total is 0, so that it still displays
        //checkout page
        $cart = geoCart::getInstance();
        if ($cart->getCartTotal() != 0) {
            //cart is not 0 so payment page will already display
            return;
        }
        if ($cart->cart_variables['order_item'] == -1) {
            //this is a stand-alone cart, don't auto-add ourself to it!
            return;
        }
        //see if there are any listing auctions that will have final fees on them
        $items = $cart->order->getItem('auction');
        if (count($items) == 0) {
            //no auctions!
            return;
        }
        $canAutoCharge = false;
        foreach ($items as $item) {
            $session_variables = $item->get('session_variables');
            if (isset($session_variables['final_fee']) && $session_variables['final_fee']) {
                //there are potential final fees!  Throw an error so that it still displays
                //payment page even though cart total is 0

                $canAutoCharge = geoPaymentGateway::callDisplay('auction_final_feesOrderItem_canAutoCharge', null, 'bool_true');
                break;
            }
        }
        if (!$canAutoCharge && $cart->get('no_free_cart') == 'auction_final_fees') {
            //be sure to un-set no free cart in case they removed the item that had potential final fees
            $cart->set('no_free_cart', false);
        } elseif ($canAutoCharge) {
            //do stuff so that billing info is still done
            $cart->set('no_free_cart', 'auction_final_fees');
            if ($cart->main_type == 'cart') {
                //Have to add payment choices back into steps, because that step
                //is not added if cart total is < 0
                $cart->addStep('payment_choices', geoCart::BEFORE_STEP, 'process_order');
            }
        }
    }

    /**
     * Required.
     */
    public static function geoCart_initSteps($allPossible = false)
    {
    }

    /**
     * Required.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }
    public function geoCart_initItem_new()
    {

        return false;
    }
    /**
     * Required.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        return array();
    }
    public static function geoCart_getCartItemDetails()
    {
        self::_initFinalFeeCart();
    }
    public static function geoCart_initSession_update()
    {
        //do stuff to make sure the final fees are added
        self::_initFinalFeeCart();
    }
    /**
     * Required.
     * Used: in geoCart::cartDisplay()
     *
     * Used to get display details about item, and any child items as well.  Should return an associative
     * array, that follows:
     * array(
     *  'css_class' => string,//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
     *  'title' => string, //text that is displayed for this item in list of items purchased.
     *  'canEdit' => bool, //whether can edit it or not
     *  'canDelete' => bool, //whether can remove from cart or not
     *  'canPreview' => bool, //whether can preview the item or not
     *  'priceDisplay' => string, //price to display, should have precurrency and all that
     *  'cost' => double, //amount this adds to the total, what getCost returns
     *  'total' => double, //amount this AND all children adds to the total
     *  'children' => array(), //should be array of child items, with the index
     *                          //being the item's ID, and the contents being associative array like
     *                          //this one.  If no children, it should be an empty array.  (Careful
     *                          //not to get into any infinite recursion)
     * )
     * @return array An associative array as described above.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, 10202);
        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => $msgs[500316],//text that is displayed for this item in list of items purchased.
            'canEdit' => false, //show edit button for item?
            'canDelete' => false, //show delete button for item?
            'canPreview' => false, //show preview button for item?
            'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($this->getCost()), //Price as it is displayed
            'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
            'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        $listing = geoListing::getListing($this->get('listing'));
        $return['title'] .= ' ' . $msgs[500334];
        if (is_object($listing)) {
            $return ['title'] .= " <a href='{$db->get_site_setting('classifieds_url')}?a=2&amp;b={$listing->id}'>{$listing->id} (" . geoString::fromDB($listing->title) . ")</a>";
        } else {
            $return ['title'] .= ' ' . $this->get('listing');
        }
        $return['title'] .= "<br />";
        $return['title'] .= $msgs[500335] . $this->get('final_fee_percentage', 0) . $msgs[500336] . geoString::displayPrice($this->get('converted_final_bid')) . $msgs[500337] . geoString::displayPrice($this->get('final_fee_fixed')) . $msgs[500338];

        if ($this->get('dutch_quantity')) {
            //add X total dutch quantity won : ##
            $return['title'] .= $msgs[500951] . $this->get('dutch_quantity');
        } elseif ($this->get('bid_quantity', 1) > 1) {
            //add x quantity purchased : ##
            $return['title'] .= $msgs[502145] . $this->get('bid_quantity');
        }

        //$return ['title'] .= '<pre>'.print_r($this,1).'</pre>';

        //THIS PART IMPORTANT:  Need to keep this part to make the item able to have children

        //go through children...
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
                    $displayResult = $item->getDisplayDetails($inCart, $inEmail);
                    if ($displayResult !== false) {
                        //only add if they do not return bool false
                        $children[$item->getId()] = $displayResult;
                        $return['total'] += $children[$item->getId()]['total']; //add to total we are returning.
                    }
                }
            }
        }
        if (count($children)) {
            //add children to the array
            $return['children'] = $children;
        }
        return $return;
    }

    /**
     * Required.
     * Used: By payment gateways to see what types of items are in the cart.
     *
     * This is very similar to {@see _templateOrderItem::getDisplayDetails()} except that the
     * information is used by payment gateways and is specifically for information about what
     * the "cost" of something is for.
     *
     * Should return an associative array, that follows:
     * array(
     *  'type' => string, //The order item type, should always be $this->getType()
     *  'extra' => mixed, //used to convey to payment gateways "custom information" that
     *                      may be needed by the gateway.  Most can set this to null.
     *  'cost' => double, //amount this adds to the total, what getCost returns
     *  'total' => double, //amount this AND all children adds to the total
     *  'children' => array(), //optional, should be array of child items, with the index
     *                          //being the item's ID, and the contents being associative array like
     *                          //this one.  Careful not to get into any infinite loops...
     * )
     *
     * @return array|bool Either an associative array as documented above, or boolean false if
     *   this item has no cost (positive or negative, including children).
     */
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

    /**
     * Required.
     * Used: in geoCart::initSteps()
     *
     * Determine whether or not the other_details step should be added to the steps of adding this item
     * to the cart.  This should also check any child items if it does not need other_details itself.
     *
     * @return boolean True to add other_details to steps, false otherwise.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
    }

    /**
     * Optional.
     * Used: from geoOrderItem::remove() when removing an order item.
     *
     * Use this function if you need to do things like remove a listing from the database, or delete
     * images or something.  Be sure to return true or the item will not be removed by the system.
     *
     * Note that normal back-end stuff like removing registry settings and removing the order item
     * from the DB are handled by the system, this function is primarily for special case stuff like
     * deleting files, or removing stuff from the DB that isn't part of normal order items.
     *
     * @return bool True to proceed with removing the item, false to stop the removal of the item.
     */
    public function processRemove()
    {
        //this should, in theory, move this item to a "final fee" specific order,
        //then next time the cart is attempting to be removed, it will be because
        //this item will no longer be attached.
        return $this->processRemoveData();
    }

    public function processRemoveData()
    {
        //Block removing final fees if this isn't paid for!

        $order = $this->getOrder();
        if (!$order) {
            //something wrong with order, go ahead and allow removal
            return true;
        }

        if ($order->getStatus() == 'active' || $this->getStatus() == 'active') {
            //order is active, so final fees paid for, so allow removal.
            return true;
        }

        if ($order->get('auction_final_fees')) {
            //this is special order, update the time on it
            $order->setCreated(geoUtil::time());

            $this->setCreated(geoUtil::time());

            $order->save();
        } else {
            //move this order item over to an auction final fee order item!
            //find an order that is specifically for final fees for this user.
            $db = DataAccess::getInstance();
            $sql = "SELECT o.`id` FROM " . geoTables::order . " as o, " . geoTables::order_registry . " as oreg WHERE o.`buyer` = ? AND o.`status` = 'pending' AND oreg.order=o.id AND oreg.index_key = 'auction_final_fees' AND oreg.val_string = '1' LIMIT 1";
            $row = $db->GetRow($sql, array($listing->seller));

            $nOrder = false;
            if (isset($row['id']) && $row['id']) {
                $nOrder = geoOrder::getOrder($row['id']);
            }

            if (!$nOrder || !is_object($nOrder)) {
                $nOrder = new geoOrder();
                //identify itself as being for final fees only
                $nOrder->set('auction_final_fees', '1');
                $nOrder->setSeller(0);
                $nOrder->setBuyer($order->getBuyer());
                $nOrder->setCreated(geoUtil::time());
                $nOrder->setStatus('pending');
                //make sure it has an id to attach to
                $nOrder->save();
            }
            $this->setOrder($nOrder);
            $nOrder->addItem($this);
            $nOrder->save();
        }
        return false;
    }

    /*
     * Special case of when buy now auction is being bid on
     *
     * @param $vars
     */
    public static function buy_now_decrease_quantity($vars)
    {
        $cron = geoCron::getInstance();
        //set up cron class to make sure nothing is displayed
        $cron->verbose = 0;
        //Let it know this is actually just decreasing quantity, it is not closed at this time
        $vars['buy_now_decrease_quantity'] = 1;
        self::cron_close_listings($vars);
    }

    /**
     * Optional.
     * Used: in file classes/cron/close_listings.php
     *
     * This is called for each listing that is being closed.  Note that the following things are
     * automatically done: the "live" column is set to 0 if listing_type != 2, and user favorites for the listing
     * are removed.  Anything beyond that is up to being done in this function.
     *
     * @param array $vars Associative array, array('listing_details' => array() object)
     */
    public static function cron_close_listings($vars)
    {
        $listing = $vars['listing']; //a geoListing item.  see that class for more details.
        $buy_now_decrease_quantity = (isset($vars['buy_now_decrease_quantity']) && $vars['buy_now_decrease_quantity']);
        $cron = geoCron::getInstance();
        $reverse = ($listing->auction_type == 3);

        $bid_quantity = (isset($vars['bid_quantity'])) ? (int)$vars['bid_quantity'] : 1;
        if (!$bid_quantity || $listing->price_applies === 'lot') {
            //in case bid quantity is not set...  Also when it applies to entire lot
            //then count it as 1 as far as affecting the bid amount.
            $bid_quantity = 1;
        }

        $current_bid = $listing->current_bid;
        if ($buy_now_decrease_quantity && $current_bid <= 0) {
            //if this is coming from buy_now_decrease_quantity, it is not the
            //normal close auction cron routine...  It is a buy now purchase of some
            //type, in which case the current_bid will not be set yet.  So use the
            //buy now amount for the price.
            $current_bid = $listing->buy_now;
        }

        //whether reserve is met depends on if it is reverse auction or not
        $reserve_met = ((!$reverse && $current_bid >= $listing->reserve_price) || ($reverse && $current_bid <= $listing->reserve_price));

        if (!($listing->final_fee == 1 && $current_bid > 0 && $reserve_met)) {
            //don't care about this one
            $cron->log("No final fees for this listing. Final fee: $listing->final_fee current bid: $current_bid reserve met: $reserve_met " . __file__ . ' - ' . __line__);
            return;
        }

        //Do anything specific to this type of item here.
        $db = DataAccess::getInstance();
        if (!geoMaster::is('site_fees')) {
            return;
        }
        if ($listing->price_applies === 'lot' && $listing->quantity > 1) {
            //Only going to be single "winner", check to make sure there aren't
            //any order items for this listing already:
            $sql = "SELECT oi.id FROM " . geoTables::order_item . " as oi, " . geoTables::order_item_registry . " as oig WHERE oig.order_item=oi.id AND oi.type='auction_final_fees' AND oig.index_key = 'listing' AND oig.val_string = ?";
            $existing = $db->GetAll($sql, array('' . $listing->id));
            if ($existing && count($existing) > 0) {
                $cron->log('Auction final fee item already exists for listing ' . $listing->id . ' - so not adding new one.', __line__);

                return;
            }
        }

        $cron->log('Top of auction final fees close listings.', __line__);

        //get the final_fee charge
        if ($listing->price_plan_id) {
            $auction_price_plan_id = $listing->price_plan_id;
        } else {
            //get the price plan attached to this seller
            $user = geoUser::getUser($listing->seller);
            if (!is_object($user)) {
                $cron->log('Error getting user object, user id: ' . $listing->seller . ' - returning false in auction final fees.', __line__);
                return;
            }
            $auction_price_plan_id = intval($user->auction_price_plan_id);
        }

        if ($reverse) {
            //find out if we should charge final fees for this reverse auction
            $planItem = geoPlanItem::getPlanItem('auction', $auction_price_plan_id);
            if (!$planItem->charge_reverse_final_fees) {
                //do not charge final fees for this!
                $cron->log('This is a reverse auction, and charging final fees for reverse auctions is disabled for this price plan.', __LINE__);
                return;
            }
        }

        $converted_price = $current_bid * $listing->conversion_rate;
        $sql = "SELECT `charge`, `charge_fixed` FROM " . geoTables::final_fee_table . " WHERE " .
            "(`low` <= $converted_price AND `high` >= $converted_price)" .
            " AND `price_plan_id` = $auction_price_plan_id ORDER BY `charge` DESC limit 1";
        //echo $sql."<br/>\n";
        $cron->log('running: ' . $sql, __line__ . ' - ' . __file__);
        $show_increment = $db->GetRow($sql);
        if (!isset($show_increment['charge'])) {
            $cron->log('Error, no valid increments for final fee!');
            return;
        }
        $final_fee_percentage = (is_numeric($show_increment['charge'])) ? $show_increment['charge'] : 0;
        $final_fee_fixed = (is_numeric($show_increment['charge_fixed'])) ? $show_increment['charge_fixed'] : 0;
        if ($final_fee_fixed <= 0 && $final_fee_percentage <= 0) {
            $cron->log('No charge for final fee.', __line__ . ' - ' . __file__);
            return;
        }

        if ($cron->verbose) {
            echo __file__ . " - " . __line__ . "::\n";
            echo $final_fee_percentage . " is the final_fee_percentage to charge\n";
            echo $final_fee_fixed . " is the final_fee_fixed to charge\n";
            echo $listing->auction_type . " is the auction_type\n";
        }
        if ($listing->auction_type == 1 || $reverse) {
            //Normal or reverse auction
            $final_fee_charge = sprintf("%01.2f", (($final_fee_percentage * $converted_price) / 100));
            $final_fee_charge += $final_fee_fixed;
            if ($listing->price_applies == 'item') {
                //buy now only and price applies to one item and auction is closing...

                $final_fee_charge = $final_fee_charge * $bid_quantity;
                $cron->log('Buy now only auction with quantity of items, number
						of items sold: ' . $bid_quantity . ', final adjusted cost: ' . $final_fee_charge, __file__ . ' - ' . __line__);
            }
        } else {
            //dutch auction type
            $dutch_bidders = self::_getDutchBidders($listing, $cron);
            //get total amount of final fees to charge
            $final_fee_charge = $dutch_quantity = 0;
            foreach ($dutch_bidders as $key => $value) {
                $this_fee = sprintf("%01.2f", (($final_fee_percentage * ($current_bid * $value["quantity"])) / 100));
                $this_fee += ($value["quantity"] * $final_fee_fixed);
                $dutch_quantity += $value['quantity'];
                $dutch_bidders[$key]["final_fee"] = $this_fee;
                $final_fee_charge += $this_fee;
            }
        }
        if ($final_fee_charge <= 0) {
            $cron->log('Final fee charge adds up to 0.', __file__ . ' - ' . __line__);
            return;
        }
        //find an order that is specifically for final fees for this user.
        $sql = "SELECT o.`id` FROM " . geoTables::order . " as o, " . geoTables::order_registry . " as oreg WHERE o.`buyer` = ? AND o.`status` = 'pending' AND oreg.order=o.id AND oreg.index_key = 'auction_final_fees' AND oreg.val_string = '1' LIMIT 1";
        $row = $db->GetRow($sql, array($listing->seller));
        if ($row === false) {
            $cron->log('Error: sql: ' . $sql . ' Error msg: ' . $db->ErrorMsg(), __file__ . ' - ' . __line__);
        }
        $order = false;
        if (isset($row['id']) && $row['id']) {
            $order = geoOrder::getOrder($row['id']);
        }

        if (!$order || !is_object($order)) {
            $order = new geoOrder();
            //identify itself as being for final fees only
            $order->set('auction_final_fees', '1');
            $order->setSeller(0);
            $order->setBuyer($listing->seller);
            $order->setCreated(geoUtil::time());
            $order->setStatus('pending');
            //make sure it has an id to attach to
            $order->save();
        }

        //make sure there is not already an order item for this listing
        $items = $order->getItem();
        $item = null;
        if ($listing->price_applies !== 'item') {
            //do NOT re-use order item if price applies to single item, as this
            //will be called multiple times for a single listing...
            foreach ($items as $itemObj) {
                if ($itemObj->getType() == self::type && $itemObj->get('listing') == $listing->id) {
                    $item = $itemObj;
                    break;
                }
            }
        }
        if (!$item) {
            $item = new auction_final_feesOrderItem();
        }
        $item->setCost($final_fee_charge);
        //For now, auction final fees are not cat specific.
        $item->setCategory(0);
        $item->setPricePlan($auction_price_plan_id);
        $item->setCreated(geoUtil::time());
        $item->setOrder($order->getId());
        $share_fees = geoAddon::getUtil('share_fees');
        if ($share_fees->active) {
            //check if this seller is attached so that paid_out_to can be set
            $paid_out_to = $share_fees->getUserAttachedTo($listing->seller);
            if ($paid_out_to != 0) {
                //set paid_out_to in the order item
                $item->setPaidOutTo($paid_out_to);
            }
        }
        $item->set('listing', $listing->id);
        $item->set('final_fee_percentage', $final_fee_percentage);
        $item->set('final_fee_fixed', $final_fee_fixed);
        if ($dutch_bidders) {
            //save dutch bidders as well for break-down of price ability.
            $item->set('dutch_bidders', $dutch_bidders);
            $item->set('dutch_quantity', $dutch_quantity);
        } elseif ($listing->price_applies == 'item') {
            $item->set('price_applies', 'item');
            $item->set('bid_quantity', $bid_quantity);
        }
        $item->set('final_bid', $current_bid);
        $item->set('conversion_rate', $listing->conversion_rate);
        $item->set('converted_final_bid', $converted_price);

        $order->addItem($item);

        $theseVars = array ('listing' => $listing,'order' => $order);

        //allow other items to do whatever, such as allow tax to add itself to the order
        geoOrderItem::callUpdate('auction_final_feesOrderItem_cron_close_listings', $theseVars);

        //now do the invoice
        $invoice = $order->getInvoice();
        if (!is_object($invoice)) {
            $invoice = new geoInvoice();
            $invoice->setCreated(geoUtil::time());
            $invoice->setDue(geoUtil::time());
            $order->setInvoice($invoice);
        }
        $gateway = geoPaymentGateway::getPaymentGateway('site_fee');
        if (!is_object($gateway)) {
            $cron->log('Error: Unable to get gateway for site fee, not able to process.', __file__ . ' - ' . __line__);
            return false;
        }
        //do the built-in transaction for the entire amount
        $trans = $invoice->getTransaction();
        $transaction = null;
        if (count($trans) > 0) {
            foreach ($trans as $tran) {
                if (is_object($tran) && $tran->getGateway()->getName() == 'site_fee') {
                    $transaction = $tran;
                    break;
                }
            }
        }
        if (!is_object($transaction)) {
            $transaction = new geoTransaction();
            $transaction->setGateway($gateway);
            $transaction->setInvoice($invoice);
            $transaction->save();
            $invoice->addTransaction($transaction);
        }

        //the cart total is a positive amount for how much the user owes us, so to convert to
        //a transaction amount it needs to be negative, kind of like taking away from a bank account
        $transaction->setAmount(-1 * $order->getOrderTotal());
        $transaction->setGateway($gateway);
        $transaction->setDate(geoUtil::time());
        $msgs = $db->get_text(true, 183);
        $transaction->setDescription($msgs[500619]);
        $transaction->setGateway($gateway);
        $transaction->setInvoice($invoice);
        $transaction->setStatus(1);//turn on
        $transaction->setUser($listing->seller);

        //save changes to everything
        $order->save();

        //allow gateways to auto pay if they wish...
        geoPaymentGateway::callUpdate('auction_final_feesOrderItem_cron_close_listings', $theseVars);

        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('cron_close_listings', $vars, $children);
    }

    private static function _getDutchBidders($listing, $cron)
    {
        $db = DataAccess::getInstance();

        $sql = "SELECT * FROM " . geoTables::bid_table . " WHERE `auction_id`=" . $listing->id . " ORDER BY `bid` DESC, `time_of_bid` ASC";
        $bid_result = $db->GetAll($sql);
        $cron->log($sql . "<br/>\n", __line__ . ' - ' . __file__);
        if ($bid_result === false) {
            $cron->log($sql . "<br/>\n", __line__ . ' - ' . __file__);
            return array();
        }
        if (count($bid_result) == 0) {
            $cron->log('Error, no dutch bidders.');
            return array();
        }
        $total_quantity = $listing->quantity;
        //echo "total items sold - ".$total_quantity."<br/>\n";
        $final_dutch_bid = 0;
        $seller_report = "";
        $dutch_bidders = array();

        foreach ($bid_result as $show_bidder) {
            if ($show_bidder['bid'] < $listing->reserve_price) {
                continue;
            }

            $quantity_bidder_receiving = 0;
            if ($show_bidder['quantity'] <= $total_quantity) {
                $quantity_bidder_receiving = $show_bidder['quantity'];
                $total_quantity = $total_quantity - $quantity_bidder_receiving;
            } else {
                $quantity_bidder_receiving = $total_quantity;
                $total_quantity = 0;
            }
            if ($quantity_bidder_receiving) {
                //send an email
                $local_key = count($dutch_bidders);
                $dutch_bidders[$local_key]["bidder"] = $show_bidder['bidder'];
                $dutch_bidders[$local_key]["quantity"] = $quantity_bidder_receiving;
                $dutch_bidders[$local_key]["bid"] = $show_bidder['bid'];
            }
            if ($total_quantity == 0) {
                //out of stuff to give away!
                break;
            }
        }
        return $dutch_bidders;
    }
    private static $_initFF_called = false;
    private static function _initFinalFeeCart()
    {
        if (self::$_initFF_called || defined('IN_ADMIN')) {
            //only init once
            return;
        }
        self::$_initFF_called = true;

        $cart = geoCart::getInstance();
        if ($cart->cart_variables['order_item'] == -1 || $cart->user_data['id'] == 0) {
            //this is a stand-alone cart, don't auto-add ourself to it!
            return;
        }

        //see if there are any orders for auction final fees
        $sql = "SELECT o.`id` FROM " . geoTables::order . " as o, " . geoTables::order_registry . " as oreg WHERE o.`buyer` = ? AND o.`admin` = 0 AND o.`status` = 'pending' AND oreg.order=o.id AND oreg.index_key = 'auction_final_fees' AND oreg.val_string = '1' LIMIT 1";
        $row = $cart->db->GetRow($sql, array($cart->user_data['id']));
        if ($row === false) {
            trigger_error('ERROR SQL: sql: ' . $sql . ' Error msg: ' . $cart->db->ErrorMsg());
            return;
        }
        $order = false;
        if (isset($row['id']) && $row['id']) {
            $order = geoOrder::getOrder($row['id']);
        }
        if (is_object($order) && count($order->getItem(self::type)) > 0) {
            //hi-jack the order items from the order and put it in here.
            $items = $order->getItem(self::type);
            foreach ($items as $item) {
                $item->setOrder($cart->order);
                $cart->order->addItem($item);
            }
            //push changes to the database now so things are not removed in next step
            $cart->order->save();
        }
        if (is_object($order)) {
            //see if the order is empty now, if it is then remove it...
            //This will reset things just in rare case that multiple orders were
            //created for an account

            //verify the old order is empty (it should be, but check just in case),
            //if it is then remove the old order
            $allItems = $order->getItem();
            $empty = true;
            $oldOrderId = $order->getId();
            foreach ($allItems as $item) {
                if ($item->getOrder() === $oldOrderId) {
                    //this item is still in the old order ID...  Meaning the
                    //order is not empty
                    $empty = false;
                    break;
                }
            }
            if ($empty) {
                //remove the old order as it is now empty...  still only remove data
                //"just in case" there are race conditions
                geoOrder::removeData($oldOrderId);
            }
        }

        //now see if there are any items in the order that might be getting final fees:
        $items = $cart->order->getItem('auction');
        if (count($items) == 0) {
            //no auctions found
            return;
        }
        //original price plan and cat
        $origP = $cart->price_plan['price_plan_id'];
        $origC = ($cart->price_plan['category_id']) ? $cart->price_plan['category_id'] : 0;

        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }
            $cart->setPricePlan($item->getPricePlan(), $item->getCategory());
            $session_variables = $item->get('session_variables');
            if ($cart->price_plan['charge_percentage_at_auction_end']) {
                $session_variables['final_fee'] = 1;
            } else {
                $session_variables['final_fee'] = 0;
            }
            $item->set('session_variables', $session_variables);
        }
        //restore original
        $cart->setPricePlan($origP, $origC);
    }
}
