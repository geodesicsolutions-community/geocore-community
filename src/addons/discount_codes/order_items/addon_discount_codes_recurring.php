<?php

//discount_codes/order_items/addon_discount_codes.php

# discount_codes addon

class addon_discount_codes_recurringOrderItem extends geoOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = 'addon_discount_codes_recurring';
    const type = 'addon_discount_codes_recurring';

    /**
     * Needs to be the order that this item will be processed.
     * Making it so that it appears after the "subtotal" item,
     * and before the "tax" item.
     *
     * @var int
     */
    protected $defaultProcessOrder = 51;
    const defaultProcessOrder = 51;

    public function displayInAdmin()
    {
        return false;
    }

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
        //doesn't matter much since not a parent
        return 'Subscription Discount for a user.';
    }

    /**
     * Required by interface.
     * Used: in geoCart::initSteps() (and possibly other locations)
     *
     *
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        //discount code doesn't have it's own page.

        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    /**
     * Required by interface.
     * Used: in geoCart::initItem()
     *
     * Whether or not a seperate cart should be used just for this order
     * item or not.  The alternate cart would be in addition to a "primary" cart
     * that may have things in it already, and this item would be the ONLY thing
     * in the cart.
     *
     * It is typical to not use this (return false), an example of when this may want
     * to be used, is to allow adding to a site balance so that a user can pay for the
     * rest of their cart.
     *
     * @return boolean True to force creating "parellel" cart just
     *  for this item, false otherwise.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
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

        //make sure there is at least one discount code
        if (!self::_check_discount_code_use($cart->order)) {
            self::_removeSelf($cart->order);
            return;
        }

        $items = $cart->order->getItem(self::type);
        $item = ($items) ? array_pop($items) : null;

        if (!$item) {
            //create a new item so it displays
            $item = geoOrderItem::getOrderItem(self::type);
            $item->setOrder($cart->order);
            $cart->order->addItem($item);
        }
        $item->setParent($cart->item);

        self::_init(false, $cart->order);
    }
    /**
     * Required by interface.
     * Used: various locations.
     *
     * This should return an array of the different order items that this
     * order item is a child of.  If this is a main order item type, it
     * should return an empty array.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        //is is a child...
        return array('subscription');
    }
    private static $_discount_code_error = '';
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $order = $this->getOrder();
        $cartVars = array();
        if ($inCart) {
            $cart = geoCart::getInstance();
            $cartVars = $cart->cart_variables;
        } else {
            if (!$this->get('discount_code')) {
                return false;
            }
        }
        self::_init(true, $order, $cartVars); //make sure amount is still good

        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => 'Discount Code',//text that is displayed for this item in list of items purchased.
            'canEdit' => false, //show edit button for item?
            'canDelete' => false, //show delete button for item?
            'canPreview' => false, //show preview button for item?
            'priceDisplay' => geoString::displayPrice($this->getCost()), //Price as it is displayed
            'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
            'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        $tpl = new geoTemplate('addon', 'discount_codes');
        $tpl->assign('msgs', self::$_msgs);
        $tpl->assign('percent', floatval($this->get('discount_percentage')));
        $tpl->assign('static', floatval($this->get('discount_static')));
        $tpl->assign('cart_total', $order->getOrderTotal());
        $tpl->assign('cost', $this->getCost());
        $tpl->assign('error', self::$_discount_code_error);
        $tpl->assign('inCart', $inCart);
        //Note: Don't need to fromDB or toDB stuff when using get and set methods, as those do it for you
        $tpl->assign('discount_code', geoString::specialChars($this->get('discount_code')));

        $return['priceDisplay'] = $tpl->fetch('price.tpl');
        $return['title'] = $tpl->fetch('title_recurring.tpl');

        //go through children...
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
        return false;
    }

    private static $_msgs = array();

    private static $_init_run = false;
    private static function _init($force_run = false, $order = null)
    {
        if (self::$_init_run && !$force_run) {
            return;
        }
        if (!$order) {
            return;
        }
        self::$_init_run = true;

        $db = DataAccess::getInstance();

        $items = $order->getItem(self::type);

        self::$_msgs = geoAddon::getText('geo_addons', 'discount_codes');
        $discount_item = ($items) ? array_pop($items) : null;

        if (!is_object($discount_item)) {
            //no need to go on
            return;
        }

        //make sure amount is correct
        if (isset($_REQUEST['discount_code'])) {
            if (strlen($_REQUEST['discount_code']) == 0) {
                $discount_item->set('discount_code', '');
                $discount_item->set('discount_id', false);
            } else {
                $code = geoString::specialCharsDecode($_REQUEST['discount_code']);
                $data = self::_getData($code, $order);
                if (isset($data['discount_code']) && geoString::fromDB($data['discount_code']) == $code) {
                    //user specified a discount code to use
                    $discount_item->set('discount_code', geoString::fromDB($data['discount_code']));
                    $discount_item->set('discount_id', $data['discount_id']);
                } else {
                    //error retrieving discount code
                    self::$_discount_code_error = true;
                }
                unset($data);
            }
        }
        $data = null;

        if ($discount_item->get('discount_code')) {
            $data = self::_getData($discount_item->get('discount_code'), $order);
        }
        if ($data) {
            //might could use this later to allow admin-specified static discounts instead of percentages
            $discount_static = 0;

            //calculate cost, make sure it is updated whenever price is changed.

            //be sure to not apply percentage discount to the portion of the total removed by the static discount
            $discount_percentage = ($order->getOrderTotal(self::defaultProcessOrder) - $discount_static) * (.01 * $data['discount_percentage']);

            $discount_amount = ($discount_static + $discount_percentage) * -1;

            $discount_item->setCost($discount_amount);
            $discount_item->set('discount_percentage', $data['discount_percentage']); //save as a displayable percentage
            $discount_item->set('discount_static', $discount_static);
        } else {
            //no discount!
            $discount_item->setCost(0);
            $discount_item->set('discount_percentage', 0);
            $discount_item->set('discount_static', 0);
            $discount_item->set('discount_code', false);
            $discount_item->set('discount_id', false);
        }
        $discount_item->save();
    }

    private static $_check_discount_code_use;
    private static function _check_discount_code_use($order)
    {
        if (!isset(self::$_check_discount_code_use)) {
            $db = DataAccess::getInstance();

            $groupId = self::_getGroupId($order);

            $sql = "SELECT `discount_id`, `is_group_specific` FROM " . addon_discount_codes_info::DISCOUNT_TABLE . " WHERE `active` = 1 AND `starts`<=? AND (`ends`=0 OR `ends`>=?) AND `apply_recurring`=1";
            if (!$groupId) {
                $sql .= " AND `is_group_specific`=0";
            }
            $all = $db->GetAll($sql, array(geoUtil::time(), geoUtil::time()));

            foreach ($all as $row) {
                if ($row['is_group_specific'] == 0 || self::_inAttachedGroup($groupId, $row['discount_id'])) {
                    //all good
                    self::$_check_discount_code_use = 1;
                    break;
                }
            }
            if (!isset(self::$_check_discount_code_use)) {
                //didn't find any
                self::$_check_discount_code_use = 0;
            }
        }
        return self::$_check_discount_code_use;
    }

    private static function _inAttachedGroup($groupId, $discount_id)
    {
        $db = DataAccess::getInstance();
        $groupId = (int)$groupId;
        $discount_id = (int)$discount_id;
        if (!$groupId || !$discount_id) {
            //nope
            return false;
        }
        $count = $db->GetOne("SELECT COUNT(*) FROM " . addon_discount_codes_info::DISCOUNT_GROUPS_TABLE . "
			WHERE `group_id`=$groupId AND `discount_id`={$discount_id}");

        return ($count > 0);
    }

    private static function _getGroupId($order)
    {
        $groupId = 0;
        $userId = $order->getBuyer();


        if (!$userId) {
            $anon = geoAddon::getUtil('anonymous_listing');
            if ($anon) {
                $anonReg = geoAddon::getRegistry('anonymous_listing');
                if ($anonReg) {
                    $userId = $anonReg->get('anon_user_id', 0);
                }
            }
        }

        if ($userId) {
            $user = geoUser::getUser($userId);

            if ($user) {
                $groupId = (int)$user->group_id;
            }
        }
        return $groupId;
    }

    private static $_code_data = array();
    private static function _getData($discount_code, $order)
    {
        if (!isset(self::$_code_data[$discount_code])) {
            $db = DataAccess::getInstance();

            $sql = "SELECT * FROM " . addon_discount_codes_info::DISCOUNT_TABLE . " WHERE `discount_code` = ? AND `active`=1 AND `starts`<=? AND (`ends`=0 OR `ends`>=?) AND `apply_recurring`=1 LIMIT 1";
            $data = $db->GetRow($sql, array(geoString::toDB($discount_code), geoUtil::time(), geoUtil::time()));

            if ($data['is_group_specific'] == 1) {
                //check group
                $groupId = self::_getGroupId($order);
                if (!self::_inAttachedGroup($groupId, $data['discount_id'])) {
                    //oops, group no matchy, not able to use this discount code!
                    $data = null;
                }
            }
            self::$_code_data[$discount_code] = $data;
        }

        return self::$_code_data[$discount_code];
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
}
