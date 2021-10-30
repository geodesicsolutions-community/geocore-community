<?php

//listing_extra_featured_level4.php


require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';

class listing_extra_featured_level4OrderItem extends geoOrderItem
{
    var $defaultProcessOrder = 38;
    protected $type = 'listing_extra_featured_level4';
    const type = 'listing_extra_featured_level4';
    /**
     * Update Functions : called from main software using geoOrderItem::callUpdate(), and that
     * function calls the one here if the function exists.  To avoid name conflicts, if you need
     * custom functions specific for this orderItem, prepend the var or function name with an
     * underscore.
     */
    public function displayInAdmin()
    {
        return false;
    }

    /**
     * used in admin to show which upgrades are attached to a Listing Renewal item
     *
     * @return String "user-friendly" name of this item
     */
    public function friendlyName()
    {
        return 'Featured (Level 4)';
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
        $db = DataAccess::getInstance();
        if (!$db->get_site_setting('use_featured_feature_4')) {
            //do not show thingy for bolding
            return false;
        }

        return true; //this item has stuff to display on other_details step.
    }

    public static function geoCart_other_detailsCheckVars($c_var = false)
    {
        $cart = geoCart::getInstance();
        if (isset($_POST['c']) || $c_var) {
            $use_featured_ad_4 = ((isset($_POST['c']['featured_ad_4']) && $_POST['c']['featured_ad_4']) ? 1 : 0);
            $use_featured_ad_4 = (($use_featured_ad_4 || (isset($c_var['featured_ad_4']) && $c_var['featured_ad_4'])) ? 1 : 0);

            $cart->site->session_variables['featured_ad_4'] = $use_featured_ad_4;
            $cart->item->set('session_variables', $cart->site->session_variables);
            $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());
            //get current attached featured_ad_4, if exists..
            $items = $cart->order->getItem('listing_extra_featured_level4');
            $order_item = false;
            if (is_array($items)) {
                foreach ($items as $i => $val) {
                    if (is_object($val) && is_object($val->getParent())) {
                        $p = $val->getParent();
                        if ($p->getId() == $cart->item->getId()) {
                            //parent is main item, the type is featured_ad, so whoohoo...
                            $order_item = $val;
                            break;
                        }
                    }
                }
            }
            if (!$use_featured_ad_4) {
                if ($order_item) {
                    $id = $order_item->getId();
                    geoOrderItem::remove($id);
                    $cart->order->detachItem($id);
                }
            } else {
                if (!$order_item) {
                    $order_item = new listing_extra_featured_level4OrderItem();
                    $order_item->setParent($cart->item);//this is a child of the parent
                    $order_item->setOrder($cart->order);

                    $order_item->save();//make sure it's serialized
                    $cart->order->addItem($order_item);
                    trigger_error('DEBUG CART: Adding featured_level4: <pre>' . print_r($order_item, 1) . '</pre>');
                } else {
                    trigger_error('DEBUG CART: featured_ad_4 already attached: <pre>' . print_r($order_item, 1) . '</pre>');
                    $cart->order->addItem($order_item);
                }
                //get the price for featured_ad_4
                $cost = (!geoMaster::is('site_fees')) ? 0 : $cart->price_plan['featured_ad_price_4'];
                $order_item->setCost($cost);
                $order_item->setCreated($cart->order->getCreated());

                //set details specific to featured_ad_4

                //set id of listing, if known
                if (isset($cart->site->classified_id) && $cart->site->classified_id > 0) {
                    $order_item->set('listing_id', $cart->site->classified_id);
                }

                //serialize so it will be available right away.
                //$order_item->serialize();
            }
            trigger_error('DEBUG CART: featured_ad_4: ' . $cart->site->session_variables['featured_ad_4']);
        }

        //but children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes('listing_extra_featured_level4');
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);
    }

    public static function geoCart_other_detailsProcess()
    {
        //everything is done at checkvars step to prevent stuff

        //get steps from children as well.
        $children = geoOrderItem::getChildrenTypes('listing_extra_featured_level4');
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
    }
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $price = $this->getCost(); //people expect numbers to be positive...
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $return = array (
            'css_class' => '',
            'title' => $msgs[500327],
            'canEdit' => false, //whether can edit it or not
            'canDelete' => true, //whether can remove from cart or not
            'canPreview' => false, //whether can preview the item or not
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price, //amount this and all children adds to the total
            'children' => false
        );

        //go through children...
        $order = $this->getOrder();
        $items = $order->getItem();
        $children = array();
        foreach ($items as $i => $val) {
            if (is_object($items[$i]) && is_object($items[$i]->getParent())) {
                $p = $items[$i]->getParent();
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
        $parent = $this->getParent();
        if ($parent && $parent->getType() === 'listing_renew_upgrade') {
            $return = $parent->checkNoDowngrade($return, 'featured_ad_4');
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

    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();
        if (!in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type)) || !geoPC::is_ent() || !$cart->db->get_site_setting('use_featured_feature_4')) {
            //do not show thingy for bolding
            return '';
        }
        if (isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade == listing_renew_upgradeOrderItem::upgrade) {
            //this is an upgrade, need to see if parent already has item
            if ($cart->site->parent_session_variables['featured_ad_4']) {
                //already exists on parent, do not allow adding
                return '';
            }
        }
        $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());

        if (!$cart->price_plan['use_featured_ads_level_4']) {
            //not used in price plan
            return '';
        }
        $price = (!geoMaster::is('site_fees')) ? 0 : $cart->price_plan['featured_ad_price_4'];
        $return = array (
            'checkbox_name' => 'c[featured_ad_4]', //no checkbox display
            'title' => geoString::fromDB($cart->site->messages[2264]),
            'display_help_link' => $cart->site->display_help_link(2265),//if 0, will display no help icon thingy
            'price_display' => geoString::displayPrice($price, false, false, 'cart'),
            'checked' => (($cart->site->session_variables['featured_ad_4']) ? "checked=\"checked\" " : "")
        );
        if ($cart->item && $cart->item->getType() === 'listing_renew_upgrade') {
            //let it alter $return to make the box not un-checked if downgrade is disabled
            $return = $cart->item->checkNoDowngrade($return, 'featured_ad_4');
        }
        return $return;
    }

    public static function getParentTypes()
    {
        //this is an extra, attached to classifieds, auctions, and
        //dutch auctions.
        return array(
            'classified',
            'classified_recurring',
            'auction',
            'listing_renew_upgrade',
            'dutch_auction',
            'job_posting',
            'reverse_auctions',
        );
    }

    public function getRecurringSubCost()
    {
        $cart = geoCart::getInstance();
        return (!geoMaster::is('site_fees')) ? 0 : $cart->price_plan['featured_ad_price_4'];
    }

    public static function geoCart_initSteps($allPossible = false)
    {
    }
    public static function geoCart_initItem_forceOutsideCart()
    {
        return false;
    }
    public function geoCart_displayCart_canEdit()
    {
        return false;
    }

    public static function geoCart_deleteProcess()
    {
        //Remove from the session_variables
        $cart = geoCart::getInstance();
        //go through each child, and call deleteProcess
        $original_id = $cart->item->getId();
        $items = $cart->order->getItem();
        foreach ($items as $k => $item) {
            if (is_object($item) && $item->getId() != $cart->item->getId() && is_object($item->getParent()) && $item->getParent()->getId() == $cart->item->getId()) {
                //this is a child of this item...
                //Set the cart's main item to be this item, so that the deleteProcess gets
                //what it is expecting...
                $cart->initItem($item->getId(), false);
                geoOrderItem::callUpdate('geoCart_deleteProcess', null, $item->getType());
            }
        }
        if ($cart->item->getId() != $original_id) {
            //change the item back to what it was originally.
            $cart->initItem($original_id);
        }

        $parent = $cart->item->getParent();
        if (is_object($parent)) {
            $session_vars = $parent->get('session_variables');
            $session_vars['featured_ad_4'] = 0;
            $parent->set('session_variables', $session_vars);
            $parent->save();
        }
    }
    public static function copyListing()
    {
        $cart = geoCart::getInstance();

        if ($cart->site->session_variables['featured_ad_4']) {
            if (!in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type)) || !$cart->db->get_site_setting('use_featured_ad_4_feature')) {
                //do not show
                return '';
            }
            $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());
            if (geoPC::is_ent() && !$cart->price_plan['use_featured_ad_4']) {
                //turned off at the price plan level, don't show
                return '';
            }

            self::geoCart_other_detailsCheckVars($cart->site->session_variables);
        }
    }
}
