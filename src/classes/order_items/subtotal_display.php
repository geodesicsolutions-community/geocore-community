<?php

//order_items/subtotal_display.php



class subtotal_displayOrderItem extends geoOrderItem
{
    protected $type = "subtotal_display";
    const type = 'subtotal_display';
    protected $defaultProcessOrder = 10000; //subtotal is 10,000, tax is 20,000, total is handled by system.
    const defaultProcessOrder = 10000;

    public function displayInAdmin()
    {
        return false;
    }

    public static function geoCart_initSteps($allPossible = false)
    {
        //required for interface
    }

    public static function geoCart_initItem_forceOutsideCart()
    {
        return false;
    }
    public static function geoCart_getCartItemDetails()
    {
        self::_initSubtotal();
    }

    public static function getParentTypes()
    {
        //for "parent" order item, returne empty string.
        return array();
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $order = $this->getOrder();
        $subtotal = $order->getOrderTotal($this->getProcessOrder());
        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        $return = array (
            'css_class' => 'subtotal_cart_item', //css class
            'title' => $msgs[500332],
            'canEdit' => false, //show edit button for item?
            'canDelete' => false, //show delete button for item?
            'canPreview' => false, //show preview button for item?
            'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($subtotal), //Price as it is displayed
            'cost' => $subtotal, //amount this adds to the total, what getCost returns
            'total' => $subtotal, //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        return $return;
    }

    public function getCostDetails()
    {
        return false;
    }

    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
    }
    private static $_initSubtotal_called = false;
    private static function _initSubtotal()
    {
        if (self::$_initSubtotal_called) {
            return;
        }

        //only call once
        self::$_initSubtotal_called = true;

        $cart = geoCart::getInstance();
        $items = $cart->order->getItem(self::type);
        $allFree = !geoMaster::is('site_fees');
        if ($allFree || $cart->cart_variables['order_item'] == -1 || $cart->getCartTotal(self::defaultProcessOrder) == 0) {
            //this is a stand-alone cart, don't auto-add ourself to it!
            if (is_array($items)) {
                foreach ($items as $item) {
                    //remove the sub-total from the order!
                    $id = $item->getId();
                    geoOrderItem::remove($id);
                    $cart->order->detachItem($id);
                }
            }
            return;
        }


        if (!is_array($items) || !count($items)) {
            //no subtotal attached yet, attach one
            $item = new subtotal_displayOrderItem();
            $item->setOrder($cart->order);
            $cart->order->addItem($item);
            $cart->order->save();
        } elseif (count($items) > 1) {
            //more than 1 subtotal, how did that happen?
            $c = 0;
            foreach ($items as $k => $item) {
                if (!$c) {
                    $c++;
                    continue;//leave the first one
                }
                $id = $item->getId();
                geoOrderItem::remove($id);
                $cart->order->detachItem($id);
                unset($items[$k]);
            }
        }
    }

    /**
     * Optional (if not defined here, parent will return getTypeTitle() - price)
     * Used: in geoOrder::processStatusChange
     *
     * Use this to display info about each main item, in the e-mail sent saying the
     * order has been approved.  To keep consistent, use this format:
     *
     * ITEM TITLE - $COSTUSD
     *
     * Be sure you also add up any costs of sub-items of this item.
     *
     * @return string
     */
    public function geoOrder_processStatusChange_emailItemInfo($overrideTitle = '')
    {
        return '';
    }
}
