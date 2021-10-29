<?php

//order_items/tax.php
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
## ##    7.6.3-73-gda0dfd6
##
##################################


class taxOrderItem extends geoOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = "tax";

    /**
     * Optional, use this as a hassle-free way to determine the type without having to hard-code
     * the type everywhere else, instead use self::type
     *
     */
    const type = 'tax';

    /**
     * Needs to be the order that this item will be processed.
     *
     * @var int
     */
    protected $defaultProcessOrder = 20000; //subtotal is 10,000, tax is 20,000, total is handled by system.
    const defaultProcessOrder = 20000; //needs to be same as normal process order.

    public function isRecurring()
    {
        return true;
    }

    public function getRecurringInterval()
    {
        //won't actually be called, parent item will do that
        return 0;
    }

    public function getRecurringPrice()
    {
        return $this->getCost();
    }

    public function getRecurringDescription()
    {
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        return $msgs[500333];
    }

    public function displayInAdmin()
    {
        return false;
    }

    public static function geoCart_initSteps($allPossible = false)
    {
    }

    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }
    public static function geoCart_getCartItemDetails()
    {
        self::_initTax();
    }

    public static function getParentTypes()
    {
        //no parents for tax!
        return array();
    }


    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $taxes = $this->getCost();
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $return = array (
            'css_class' => 'tax_cart_item', //css class
            'title' => $msgs[500333],
            'canEdit' => false, //show edit button for item?
            'canDelete' => false, //show delete button for item?
            'canPreview' => false, //show preview button for item?
            'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($taxes), //Price as it is displayed
            'cost' => $taxes, //amount this adds to the total, what getCost returns
            'total' => $taxes,
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        if ($this->getParent()) {
            //if it is recurring tax, don't use tax_cart_item css
            $return['css_class'] = '';
        }
        //do not bother with children, items should not be attaching themself to tax
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

    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
    }

    /**
     * Optional.
     * Used: In auction_final_fees order item (auction_final_feesOrderItem::cron_close_listings()
     *
     * NOT part of built-in cart system.
     *
     * Special case, functionality built into individual order items.
     *
     * This can be used to do stuff at the same time that an auction final
     * fee is added to the order.
     *
     * @param array $vars See docs in function
     */
    public static function auction_final_feesOrderItem_cron_close_listings($vars)
    {
        $listing = $vars['listing'];
        $order = $vars['order'];

        //just init the tax, passing in the order, it will do the rest.
        self::_initTax($order);
    }

    private static $_initTax_called = false;

    private static function _initTax($order = false)
    {
        if (!geoMaster::is('site_fees')) {
            return;
        }
        if (!$order) {
            //if false, assume this is called when the cart has been inited
            if (self::$_initTax_called) {
                //when being called from the cart, only init once
                return;
            }
            self::$_initTax_called = true;

            $cart = geoCart::getInstance();
            if ($cart->cart_variables['order_item'] == -1 && !$cart->isRecurringCart()) {
                //this is a stand-alone cart, don't auto-add ourself to it!
                return;
            }
            $order = $cart->order;
        }

        $items = $order->getItem(self::type);
        if (!is_array($items)) {
            $items = array();
        }
        $tax_item = null;
        $total = $order->getOrderTotal(self::defaultProcessOrder);
        foreach ($items as $k => $item) {
            if (is_object($item)) {
                if (is_object($tax_item)) {
                    //multiple tax items?  remove this one
                    $id = $item->getId();
                    geoOrderItem::remove($id);
                    $order->detachItem($id);
                } else {
                    $tax_item = $item;
                }
            }
        }

        $taxes = self::_getTaxAmount($total, geoUser::getUser($order->getBuyer()));
        if ($taxes > 0) {
            //make sure to add tax item to order, and set the price on it.
            if (!is_object($tax_item)) {
                $tax_item = new taxOrderItem();
                $tax_item->setOrder($order);
                $order->addItem($tax_item);
            }
            $tax_item->setCost($taxes);
            $cart = geoCart::getInstance();
            if ($cart && $cart->order && $cart->isRecurringCart() && $cart->item) {
                $tax_item->setParent($cart->item);
            }
            $tax_item->save();
        } elseif (is_object($tax_item)) {
            //taxes are none, so remove the tax item if it exists
            $id = $tax_item->getId();
            geoOrderItem::remove($id);
            $order->detachItem($id);
        }
    }

    private static function _getTaxAmount($total, $user_data)
    {
        $db = DataAccess::getInstance();
        if (!$total || !$db->get_site_setting('charge_tax_by') || !is_object($user_data)) {
            //no charge for tax!
            return 0;
        }

        //get this user's region hierarchy
        $regions = geoRegion::getRegionsForUser($user_data->id);
        //go through the regions and get their tax percentage and flat amounts, and add those to the totalTax
        //be sure to base percentages off the original subtotal -- don't want to tax the taxes!
        $taxableSubtotal = $total;
        $totalTax = 0;
        $getTaxes = $db->Prepare("SELECT `tax_percent`, `tax_flat` FROM " . geoTables::region . " WHERE `id` = ?");
        foreach ($regions as $region) {
            $regionTaxes = $db->GetRow($getTaxes, array($region));
            $totalTax += ($regionTaxes['tax_percent'] / 100) * $taxableSubtotal;
            $totalTax += $regionTaxes['tax_flat'];
        }
        return $totalTax;
    }

    /**
     * Optional (if not defined here, parent will return getTypeTitle() - price)
     * Used: in geoOrder::processStatusChange
     *
     * Use this to display info about each main item, in the e-mail sent saying the
     * order has been approved.  To keep consistent, use this format:
     *
     * ITEM TITLE [STATUS] - $COST
     *
     * Be sure you also add up any costs of sub-items of this item.
     *
     * @return string
     */
    public function geoOrder_processStatusChange_emailItemInfo($overrideTitle = '')
    {
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, 10207);
        return "________\n{$msgs[500723]} " . geoString::displayPrice($this->getCost());
    }
}
