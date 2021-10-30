<?php

//order_items/token_attach.php

# Tokens

/**
 * This guy is responsible for attaching itself to order items to cover the price,
 * in exchange for a token.
 */
class token_attachOrderItem extends geoOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = 'token_attach';
    const type = 'token_attach';

    /**
     * Needs to be the order that this item will be processed, only items with process order less than this will have a token applied to it.
     *
     * @var int
     */
    protected $defaultProcessOrder = 15;
    const defaultProcessOrder = 15; //change this to be same # as line above.

    public function displayInAdmin()
    {
        return false;
    }

    public static function geoCart_initSteps($allPossible = false)
    {
        $cart = geoCart::getInstance();

        //get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }
    public static function geoCart_getCartItemDetails()
    {
        self::_initTokens();
    }

    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
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
        //for "parent" order item, returne empty string.

        return array('classified','auction','job_posting');
    }


    public function getDisplayDetails($inCart, $inEmail = false)
    {
        if ($this->getCost() == 0 && !$inCart) {
            //token not applied, and not in cart view, do not display this item
            return false;
        }

        $msgs = self::_getText();

        $return = array (
            'css_class' => '',
            'title' => '',
            'canEdit' => false, //show edit button for item?
            'canDelete' => false, //show delete button for item?
            'canPreview' => false, //show preview button for item?
            'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($this->getCost(), false, false, 'cart'), //Price as it is displayed
            'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
            'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        if ($return['cost'] == 0 && $inCart) {
            $cart = geoCart::getInstance();
            $return['priceDisplay'] = '<a href="' . $cart->getCartBaseUrl() . '&amp;add_token=' . $this->getParent()->getId() . '" class="mini_button">' . $msgs['attach_use_token'] . '</a>';
            if ($msgs['attach_use_token_label_inactive']) {
                $return['title'] = $msgs['attach_use_token_label_inactive'] . ' ' . $cart->user_data['token_count'];
            }
        } elseif ($inCart) {
            $cart = geoCart::getInstance();
            if ($msgs['attach_use_token_label_active']) {
                $return['title'] = $msgs['attach_use_token_label_active'] . ' ' . $cart->user_data['token_count'];
            }
            $return['canDelete'] = true;
        } else {
            $return['title'] = $msgs['attach_use_token_label_outside_cart'];
        }
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

    public static function geoCart_deleteCheckVars()
    {
        $cart = geoCart::getInstance();
        trigger_error('DEBUG CART: Here');
        if (is_object($cart->item) && $cart->item->getType() == self::type && is_object($cart->item->getParent()) && $cart->item->getParent()->getCost() > 0) {
            //don't want to delete it, just want to set the cost to 0
            trigger_error('DEBUG CART: Here');
            $cart->item->setCost(0);
            $cart->addError();
        }
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

    /**
     * Changes the status on an order item.  Built-in statuses are active, pending, and
     * pending_alter.  Recommended to overwrite this function if the item needs to
     * do anything at the time it is activated or deactivated.  Even if this is overloaded,
     * it is recommended to still call the parent function to do common stuff.
     *
     * @param string $newStatus either "active", "pending", or "pending_alter"
     * @param bool $sendEmailNotices If set to false, no e-mail notifications will be
     *  sent, even if they are supposed to according to settings set in admin.
     * @param bool $updateCategoryCount If set to true, the category count for this item will
     *  be updated.  If false, it assumes whoever is calling this will do the updating all
     *  at once for efficiency.
     */
    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
    {
        if ($newStatus == $this->getStatus() || $this->getCost() == 0) {
            //the status hasn't actually changed, OR the cost is 0, so nothing to do
            return;
        }
        $activate = ($newStatus == 'active') ? true : false;

        $already_active = ($this->getStatus() == 'active') ? true : false;

        //allow parent to do common things, like set the status and
        //call children items
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);

        if ($activate) {
            //activating, so taking away a token...
            if ($this->get('token_paid')) {
                //token was already paid, nothing to do!
                return;
            }
            $db = DataAccess::getInstance();

            $sql = "SELECT `token_count`, `id` FROM " . geoTables::user_tokens . " WHERE `user_id` = ? ORDER BY `expire` ASC";
            $row = $db->GetRow($sql, array($this->getOrder()->getBuyer()));
            if ($row === false) {
                trigger_error('ERROR CART TRANSACTION SQL: Error getting tokens for user, sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg());
                return;
            }
            if ($row['token_count'] > 1) {
                $sql = "UPDATE " . geoTables::user_tokens . " SET `token_count` = " . intval($row["token_count"] - 1) . "
							WHERE `id` = ? LIMIT 1";
            } else {
                $sql = "DELETE FROM " . geoTables::user_tokens . " WHERE `id` = ? LIMIT 1";
            }
            if (!$db->Execute($sql, array($row['id']))) {
                trigger_error('ERROR SQL: ' . $sql . ' Msg: ' . $db->ErrorMsg());
                return;
            }
            $this->set('token_paid', 1);
        } elseif ($already_active) {
            //changing to inactive...  Don't do anything.
        }
    }

    private static $_initTokens_called = false;

    private static $_tokens_used_in_order = 0;

    private static function _initTokens()
    {
        if (self::$_initTokens_called) {
            return;
        }

        //only call once
        self::$_initTokens_called = true;
        self::_expireTokens();

        $cart = geoCart::getInstance();
        if (!geoMaster::is('site_fees')) {
            return;
        }
        if ($cart->cart_variables['order_item'] == -1) {
            //this is a stand-alone cart, don't auto-add ourself to it!
            return;
        }

        $cart->user_data['token_count'] = $token_count = self::_getUserTokenCount($cart->user_data['id'], $cart->order);
        trigger_error('DEBUG CART: Here in Tokens');
        if (!$token_count) {
            //remove any attached items, and do not add token item to order,
            //since user has no tokens.
            trigger_error('DEBUG CART: Here in tokens');
            $items = $cart->order->getItem(self::type);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (is_object($item)) {
                        $id = $item->getId();
                        geoOrderItem::remove($id);
                        $cart->order->detachItem($id);
                    }
                }
            }
            return;
        }

        //there are tokens, so attach token item to each parent item
        $parent_types = geoOrderItem::getParentTypesFor(self::type);

        self::$_tokens_used_in_order = 0;

        foreach ($parent_types as $p_type) {
            trigger_error('DEBUG CART: Here in tokens');
            $items = $cart->order->getItem($p_type);
            if (is_array($items) && count($items) > 0) {
                foreach ($items as $p_item) {
                    if (is_object($p_item)) {
                        $token_item = geoOrderItem::getOrderItemFromParent($p_item, self::type);
                        if (!is_object($token_item) && $p_item->getCost() > 0) {
                            //add it to item!
                            trigger_error('DEBUG CART: Here in tokens');
                            $token_item = new token_attachOrderItem();
                            $token_item->setOrder($cart->order);
                            $token_item->setCost(0); //set cost to 0, signifying no token is used.
                            $token_item->setParent($p_item);
                            $cart->order->addItem($token_item);
                            //$token_item->save();
                        } elseif (is_object($token_item) && ($p_item->getCost() <= 0 || self::$_tokens_used_in_order === $token_count)) {
                            //Either item cost has changed to 0, or we've already reached the limit of how many tokens can be used in this order.
                            //remove it from item!
                            $id = $token_item->getId();
                            geoOrderItem::remove($id);
                            $cart->order->detachItem($id);
                        } elseif (is_object($token_item) && $token_item->getCost() < 0) {
                            //token used on this item!  If we ever make it so that different items "cost" different
                            //token amounts, this needs to be changed here.
                            self::$_tokens_used_in_order++;
                            //make sure the cost still matches the amount for the item, in case the cost of the item has changed
                            //since it was added
                            if ($token_item->getCost() * -1 !== $p_item->getCost()) {
                                //cost of parent item has changed, so match cost of token to match!
                                $token_item->setCost($p_item->getCost() * -1);
                            }
                        }

                        if (
                            is_object($token_item) &&
                            $p_item->getCost() > 0 &&
                            $token_item->getCost() == 0 &&
                            isset($_GET['add_token']) &&
                            $_GET['add_token'] == $p_item->getId() &&
                            self::$_tokens_used_in_order < $token_count
                        ) {
                            //User clicked on "use token" link, and we are not yet at the limit of how many
                            //tokens can be used for this order, so make tokens be used by this item.
                            $token_item->setCost(-1 * $p_item->getCost());
                        }
                    }
                }
            }
        }
    }
    private static $_user_token_count = array();
    private static function _getUserTokenCount($user_id, $order = null)
    {
        $db = DataAccess::getInstance();
        $user_id = intval($user_id);
        if (!$user_id) {
            return false;
        }
        $sql = "SELECT SUM(`token_count`) as `count` FROM " . geoTables::user_tokens . " WHERE `user_id` = ?";
        $row = $db->GetRow($sql, array($user_id));
        if ($row === false) {
            trigger_error('ERROR SQL CART: Sql: ' . $sql . ' Error msg: ' . $db->ErrorMsg());
            return false;
        }

        if (!isset($row['count'])) {
            return 0;
        }

        $count = (int)$row['count'];

        if ($count > 0) {
            //account for tokens used in pending items

            //get all the pending items of this type
            $sql = "SELECT `oi`.`id` FROM " . geoTables::order_item . " oi, " . geoTables::order . " o WHERE `o`.`id`=`oi`.`order`
					AND `o`.`buyer`=? AND `o`.`seller`=0 AND `o`.`status` IN ('active','pending','pending_admin')
					AND `oi`.`type`='" . self::type . "'
					AND `oi`.`status`='pending'";
            if ($order && $order->getId()) {
                $sql .= " AND `o`.`id`!='" . (int)$order->getId() . "'";
            }

            $pending_items = $db->Execute($sql, array($user_id));

            if ($pending_items === false) {
                trigger_error("ERROR SQL: Query error getting pending tokens, sql: $sql error message: " . $db->ErrorMsg());
                return $count;
            }

            foreach ($pending_items as $row) {
                $order_item = geoOrderItem::getOrderItem($row['id']);
                if (!$order_item) {
                    //could not get order item, continue
                    echo "no order item object<br />";
                    continue;
                }
                if ($order_item->get('token_paid')) {
                    //this order item has already been subtracted from user's account!
                    echo "already subtracted token!<br />";
                    continue;
                }
                unset($order_item);
                //if it gets to here, this order item is taking up one of the tokens (potentially)
                trigger_error("DEBUG TOKEN: subtracting 1 from available tokens for a pending token found");
                $count--;
                if ($count <= 0) {
                    //we got to 0 available tokens, don't need to keep going
                    $count = 0;
                    break;
                }
            }
        }

        self::$_user_token_count [$user_id] = $count;
        return $count;
    }
    private static function _expireTokens()
    {
        $db = DataAccess::getInstance();

        $sql = "DELETE FROM " . geoTables::user_tokens . " WHERE `expire` < ? OR `token_count` = 0";
        $result = $db->Execute($sql, array(geoUtil::time()));
        if (!$result) {
            trigger_error('ERROR SQL CART: Sql: ' . $sql . ' Error msg: ' . $db->ErrorMsg());
            return false;
        }
        return true;
    }

    /**
     * Shortcut method to get the text for the tokens addon
     */
    private static function _getText()
    {
        return geoAddon::getText('geo_addons', 'tokens');
    }
}
