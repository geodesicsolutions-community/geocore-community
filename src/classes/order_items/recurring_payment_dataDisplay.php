<?php

//order_items/recurring_payment_dataDisplay.php



class recurring_payment_dataDisplayOrderItem extends geoOrderItem
{
    protected $type = "recurring_payment_dataDisplay";
    const type = 'recurring_payment_dataDisplay';
    //make it 1 more than recurring tax item so it shows below the tax
    protected $defaultProcessOrder = 20001;
    const defaultProcessOrder = 20001;

    /**
     * Required.
     *
     * @return bool
     */
    public function displayInAdmin()
    {
        return true;
    }
    public function adminDetails()
    {
        $title = 'Recurring Payment Info';

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    public static function adminItemDisplay($item_id)
    {
        $html = '';
        //TODO: Admin display
        return $html;

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

    private static function _removeSelf($order)
    {
        $items = $order->getItem(self::type);
        $item = ($items) ? array_pop($items) : null;

        if (!$item) {
            //nothing to do
            return;
        }
        //kill item
        $id = $item->getId();

        geoOrderItem::remove($id);
        $order->detachItem($id);
    }

    public static function geoCart_getCartItemDetails()
    {
        //attach/un-attach here
        $cart = geoCart::getInstance();

        if (!$cart->order) {
            //shouldn't get here...
            //can't do what we need w/o order, plus that's weird a cart with no order...

            return;
        }
        if (!$cart->isRecurringCart()) {
            //this is not a recurring cart, which is the only type of cart
            //we are interested in.
            self::_removeSelf($cart->order);
            return;
        }



        //now then, this IS a recurring cart, so go through all payment gateways
        //that are enabled and see if any of them are to be displayed.
        $gateways = geoPaymentGateway::getPaymentGatewayOfType('recurring');
        if (!count($gateways)) {
            //none of the enabled gateways seem to be recurring, so this won't
            //turn out to be a recurring cart after all.
            self::_removeSelf($cart->order);
            return;
        }

        $price = $cart->item->getRecurringPrice();
        $interval = $cart->item->getRecurringInterval();

        if (!$price || !$interval) {
            //no cost or interval not set
            self::_removeSelf($cart->order);
            return;
        }

        //now see if there are any items in the order that might be getting final fees:
        $items = $cart->order->getItem(self::type);
        $item = ($items) ? array_pop($items) : null;

        if (!$item) {
            //create a new item so it displays
            $item = geoOrderItem::getOrderItem(self::type);
            $item->setOrder($cart->order);
            $cart->order->addItem($item);
        }
        $item->setParent($cart->item);
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
        return array('subscription');
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
            'css_class' => '',
            'title' => $msgs[500743],//text that is displayed for this item in list of items purchased.
            'canEdit' => false, //show edit button for item?
            'canDelete' => false, //show delete button for item?
            'canPreview' => false, //show preview button for item?
            'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => '', //Price as it is displayed
            'cost' => 0, //amount this adds to the total, what getCost returns
            'total' => 0, //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        $order = $this->getOrder();
        if (!$order) {
            //should not happen
            return false;
        }
        $item = $order->getItem('recurring');
        if (!$item) {
            //no recurring item found?

            return false;
        }

        $price = $item->getRecurringPrice();
        $interval = $item->getRecurringInterval();
        $startDate = $item->getRecurringStartDate();
        $description = $item->getRecurringDescription();

        $price = geoString::displayPrice($price, false, false, 'cart');
        $interval = floor($interval / (60 * 60 * 24));

        if ($startDate > geoUtil::time() && $msgs[500744]) {
            //add info about start date, but only if the text msg is not "blanked"
            $dateFormat = $db->get_site_setting('entry_date_configuration');
            $return['title'] .= $msgs[500744] . ' ' . date($dateFormat, $startDate);
        }
        $return['priceDisplay'] = "{$msgs[500745]}$price {$msgs[500746]} $interval {$msgs[500747]}";

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
        //Possible enhancement: perhaps show final fee table on other detail page?
        return false;
    }
}
