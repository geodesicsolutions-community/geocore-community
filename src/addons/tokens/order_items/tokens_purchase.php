<?php

//addons/tokens/order_items/tokens_purchase.php

# tokens Addon

class tokens_purchaseOrderItem extends geoOrderItem
{
    protected $type = "tokens_purchase";
    const type = 'tokens_purchase';
    //Order it to be below add to balance (25)...
    protected $defaultProcessOrder = 30;
    const defaultProcessOrder = 30;


    /**
     * Required by order item system.
     *
     * See {@link _templateOrderItem::displayInAdmin()} for full documentation.
     *
     * @return bool
     */
    public function displayInAdmin()
    {
        if ($_GET['page'] == 'pricing_category_costs') {
            //not a category specific setting!
            return false;
        }
        //We do display this item in the admin.
        return true;
    }

    /**
     * displays admin plan item settings
     * @param geoPlanItem $planItem
     */
    public function adminPlanItemConfigDisplay($planItem)
    {
        $db = DataAccess::getInstance();

        $tpl_vars = array();

        $price_plan_id = (int)$planItem->getPricePlan();

        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');

        //display in user-friendly format
        $tpl_vars['day'] = 86400;
        $tpl_vars['year'] = 31536000;

        $util = geoAddon::getUtil('tokens');

        $tpl_vars['options'] = $util->getPriceOptions($price_plan_id, true);
        $tpl = new geoTemplate(geoTemplate::ADDON, 'tokens');
        $tpl->assign($tpl_vars);

        return $tpl->fetch('addon/tokens/admin/tokens_purchase_prices.tpl');
    }

    /**
     * This saves the plan item settings.
     *
     * See {@link _templateOrderItem::adminPlanItemConfigUpdate()} for full documentation.
     *
     * @param geoPlanItem $planItem The geoPlanItem object that holds the settings for this item and price plan
     * @return bool If return true, message "settings saved" will be displayed, if return
     *  false, message "settings not saved" will be displayed.
     */
    public function adminPlanItemConfigUpdate($planItem)
    {
        $settings = $_POST['tokens_purchase'];

        if (is_array($settings)) {
            $util = geoAddon::getUtil('tokens');

            $db = DataAccess::getInstance();

            $price_plan_id = $planItem->getPricePlan();

            $db->Execute("DELETE FROM " . addon_tokens_info::TOKENS_PRICE_TABLE . " WHERE `price_plan_id`=?", array($price_plan_id));

            $tokens_used = array();
            $day = 86400;
            $year = 31536000;

            foreach ($settings['options'] as $existing => $option) {
                if ($existing == 'new' && !$option['tokens']) {
                    //tokens not set
                    continue;
                }
                //stop duplicates
                if (in_array($option['tokens'], $tokens_used)) {
                    geoAdmin::m("Cannot have multiple options with same number of tokens.", geoAdmin::ERROR);
                    continue;
                }
                $tokens = (int)$option['tokens'];
                $price = geoNumber::deformat($option['price']);
                $expire_period = intval($option['expire_period'] * $option['expire_period_units']);
                if (!$tokens) {
                    geoAdmin::m("Must specify number of tokens.", geoAdmin::ERROR);
                    continue;
                }
                if (!$expire_period) {
                    geoAdmin::m("Expire Period Required.", geoAdmin::ERROR);
                    continue;
                }
                $tokens_used[] = $tokens;
                $db->Execute("INSERT INTO " . addon_tokens_info::TOKENS_PRICE_TABLE . " SET `price_plan_id`=?, `tokens`=?, `price`=?, `expire_period`=?", array ($price_plan_id, $tokens, $price, $expire_period));
            }
        }

        return true;
    }


    public function adminDetails()
    {
        //This is to display the details when this item shows up in a list of
        //other items.
        return array(
            'type' => 'Tokens Purchased',
            'title' => $this->get('tokens') . ' Tokens Purchased.'
        );
    }

    /**
     * This will display info when viewing an item's details.  This is when
     * the admin clicks on an item in the admin at "Orders > Manage Items"
     *
     * See {@link _templateOrderItem::adminItemDisplay()} for full documentation.
     *
     * @param int $item_id
     * @return string What is going to be displayed
     */
    public static function adminItemDisplay($item_id)
    {
        if (!$item_id) {
            return '';
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() != self::type) {
            return '';
        }

        $text = geoHTML::addOption("Number Tokens Purchased", $item->get('tokens'));

        return $text;
    }

    public function getTypeTitle()
    {
        //used all over the place (in the admin) when displaying info about items.
        return 'Tokens Purchase';
    }

    /**
     * This is where we define what steps there are for adding an tokens_purchase.
     *
     * See {@link _templateOrderItem::geoCart_initSteps()} for full documentation.
     *
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        //Get the cart to add some steps to it
        $cart = geoCart::getInstance();

        //add page to choose amount of tokens to purchase at once
        $cart->addStep('tokens_purchase:chooseTokens');

        //get steps from children as well.  Children items are not called
        //automatically, to allow parent items to have more control over
        //their "children" items.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    /**
     * Since we added a "youAreCool" step, we need to have 3 methods that handle
     * that step: a display, a check vars, and a process.  This one is the
     * display.
     *
     */
    public static function chooseTokensDisplay()
    {
        //This is responsible for all the nitty gritty details of displaying
        //a page, like setting up the page id and everything.  Because of this,
        //this method has a higher likelyhood of needing to be updated in new
        //releases if how a page is displayed changes.

        //get an instance of the cart, it will have a site class in it we can
        //use to display the page.
        $cart = geoCart::getInstance();

        //make sure price plan is set
        if ($cart->price_plan['price_plan_id'] && is_object($cart->item) && $cart->item->getType() == self::type && !$cart->item->getPricePlan()) {
            //set price plan to default to keep from looking it up each time
            $cart->item->setPricePlan($cart->price_plan['price_plan_id']);
        }

        //get the util class, we'll be using a few methods in it.
        $util = geoAddon::getUtil('tokens');

        //To display this step, we're going to use an example page.
        $cart->site->page_id = "addons/tokens/chooseTokens";
        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->language_id = $cart->db->getLanguage();
        $cart->site->addon_name = 'Tokens';

        $tpl_vars = $cart->getCommonTemplateVars();

        $tpl_vars['tokenChoices'] = $util->getPriceOptions($cart->item->getPricePlan(), true);
        $tpl_vars['tokens'] = $cart->item->get('tokens');
        $tpl_vars['cancel_url'] = $cart->getCartBaseUrl() . '&amp;action=cancel';
        $tpl_vars['error_msgs'] = $cart->getErrorMsgs();
        $tpl_vars['msgs'] = self::_getText();

        //now set the tpl to use
        geoView::getInstance()->setBodyTpl('order_items/chooseTokens.tpl', 'tokens')
            ->setBodyVar($tpl_vars);
        //now display the page, must call this way to have all the stuff set properly
        $cart->site->display_page();
    }

    /**
     * This checks all the input variables submitted as part of the youAreCool
     * step.  If there are any problems, this method will raid an error with
     * the cart and the cart will know not to go on.
     *
     */
    public static function chooseTokensCheckVars()
    {
        $cart = geoCart::getInstance();
        //check the settings!

        if (!$_POST['token_choice']) {
            $msgs = self::_getText();

            $cart->addError()
                ->addErrorMsg('tokens_purchase', $msgs['error_choose_tokens']);
            return;
        }

        $util = geoAddon::getUtil('tokens');
        $token = $util->getTokenInfo($cart->item->getPricePlan(), $_POST['token_choice']);

        if (!$token) {
            $msgs = self::_getText();

            $cart->addError()
                ->addErrorMsg('tokens_purchase', $msgs['error_invalid_selection']);
        }
        //if it got this far, everything is AOK!
    }

    /**
     * This only happens if there are no errors raised when we checked the vars
     * before. This method should save any values that need to be saved for
     * the youAreCool step.
     *
     * This step can raise an error with the cart to make the cart not proceed
     * to the next step.  Just because you can, doesn't mean you should though.
     * It is considered bad practice to raise an error in this step, except for
     * special cases where raising an error in checkvars is not possible.
     * If at all possible, you should detect any problems
     * in the check vars stage and raised an error then.
     */
    public static function chooseTokensProcess()
    {

        $cart = geoCart::getInstance();
        $item = $cart->item;

        $util = geoAddon::getUtil('tokens');
        $token = $util->getTokenInfo($item->getPricePlan(), $_POST['token_choice']);

        if (!$token) {
            //should not get here, should be caught in checkVars, this is just
            //extra failsafe
            $cart->addError();
            return;
        }
        $item->setCost($token['price']);
        $item->set('tokens', (int)$token['tokens']);
    }

    /**
     * What is displayed for the chooseTokens label
     *
     * @return string
     */
    public static function chooseTokensLabel()
    {
        $msgs = self::_getText();
        return $msgs['chooseTokens_step_label'];
    }

    /**
     * This guy is required by the system.  Like most order items, we'll just
     * be returning false here.
     *
     * @return bool True to force creating "parellel" cart just
     *  for this item, false otherwise.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }

    /**
     * This is a parent, so return empty array
     *
     * @return array
     */
    public static function getParentTypes()
    {
        return array();
    }

    /**
     * Used to display the item in various places, primarily in the main Cart
     * view on the client side.
     *
     * See {@link _templateOrderItem::getDisplayDetails()} for full documentation.
     *
     * @param bool $inCart True if this is being called from inside the cart, false otherwise. Note: do NOT
     *  try to use the geoCart object if $inCart is false.
     * @return array|bool Either an associative array as documented above, or boolean false to hide this
     *  item from view.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $price = $this->getCost();
        $tokens = $this->get('tokens');
        $msgs = self::_getText();

        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => $tokens . ' ' . $msgs['cart_token_purchase_item_label'],//text that is displayed for this item in list of items purchased.
            'canEdit' => true, //show edit button for item, if displaying in cart?
            'canDelete' => true, //show delete button for item, if displaying in cart?
            'canPreview' => false, //show preview button for item, if displaying in cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //Price as it is displayed
            'cost' => $price,
            'total' => $price,
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );





        //THIS PART IMPORTANT:  Need to keep this part to make the item is able to have children.
        //You don't want your item to be sterile do you?

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
     * Required by system, to tell if this item uses the other details step.
     *
     * See {@link _templateOrderItem::geoCart_initSteps_addOtherDetails()} for full documentation.
     *
     * @return bool we'll say false.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
    }

    /**
     * Used to display the "add new button" down there under the "add to cart"
     * box.
     *
     * See {@link _templateOrderItem::geoCart_cartDisplay_newButton()} for full documentation.
     *
     */
    public static function geoCart_cartDisplay_newButton($inModule = false)
    {
        if (self::isAnonymous()) {
            //it is anon or verify accounts disabled
            return;
        }
        $cart = geoCart::getInstance();

        $util = geoAddon::getUtil('tokens');
        $pricePlanId = ((geoMaster::is('classifieds')) ? $cart->user_data["price_plan_id"] : $cart->user_data["auction_price_plan_id"]);
        $priceOptions = $util->getPriceOptions($pricePlanId);

        if (!$priceOptions) {
            //no price options to choose from, don't show button
            return;
        }

        //go ahead and display the button...
        $msgs = self::_getText();
        if ($inModule) {
            //return module style
            return array ('label' => $msgs['purchase_token_button_module']);
        }
        return $msgs['purchase_token_button_cart'];
    }

    /**
     * Used to display the Add new tokens_purchase button in my account links module.
     *
     * See {@link _templateOrderItem::my_account_links_newButton()} for full documentation.
     *
     * @return array
     */
    public static function my_account_links_newButton()
    {
        return self::geoCart_cartDisplay_newButton(true);
    }

    /**
     * If this were a normal order item, most likely something would be done
     * here, something that is involved when activating or de-activating an
     * tokens_purchase.
     *
     * See {@link _templateOrderItem::processStatusChange()} for full documentation.
     *
     * @param string $newStatus a string of what the new status for the item should be.  The statuses
     *  built into the system are active, pending, and pending_alter.
     * @param bool $sendEmailNotifications If set to false, you should not send any e-mail notifications
     *  like might be normally done.  (if it's false, it will be because this is called
     *  from admin and admin said don't send e-mails)
     */
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

        if ($activate) {
            //order item is activating, so add the tokens to the user's account
            if ($this->get('tokens_issued')) {
                //don't keep re-adding them if it is activated multiple times
                return;
            }
            $util = geoAddon::getUtil('tokens');

            $token = $util->getTokenInfo($this->getPricePlan(), $this->get('tokens'));

            if (!$token) {
                //not good!  couldn't find info for this token..
                return;
            }

            $expires = intval(geoUtil::time() + $token['expire_period']);
            //get user ID from order
            $order = $this->getOrder();
            if (!$order) {
                //could not get order!  Can't add tokens as we can't get user
                return;
            }
            $user_id = (int)$order->getBuyer();
            if (!$user_id) {
                //no user?  this shouldn't happen...
                return;
            }
            $tokens = (int)$token['tokens'];

            $sql = "INSERT INTO `geodesic_user_tokens` SET `user_id`=?, `token_count`=?, `expire`=?";
            $query_data = array($user_id, $tokens, $expires);

            $db = DataAccess::getInstance();
            $db->Execute($sql, $query_data);
            //remember that tokens have been issued.
            $this->set('tokens_issued', 1);
        } elseif ($already_active) {
            //do de-activate actions here, such as setting listing to not be live any more.
            //This is what would happen if an admin changes their mind
            //and later decides to change an item from being active to being pending.
        }
        //NOTE: do NOT need to call children, parent does that for us :)
    }

    /**
     * Optional.
     * Used: in geoCart and my_account_links module
     *
     * This is used to display what the action is if this order item is the main type.  It should return
     * something like "adding new listing" or "editing images".
     *
     * @return string
     */
    public static function getActionName($vars)
    {
        $msgs = self::_getText();
        return $msgs['cart_token_purchase_action_label'];
    }


/**
     * Show the number of available tokens in my account links module
     * @param $vars
     */
    public static function User_management_home_body($vars)
    {
        $view = geoView::getInstance();
        $orderItemLinks = $view->orderItemLinks;

        $tokens['link'] = false;
        $tokens['icon'] = false;

        $user = geoSession::getInstance()->getUserId();
        $numTokens = 0;
        if ($user) {
            $util = geoAddon::getUtil('tokens');
            $tokenData = $util->getTokensFor($user);
            foreach ($tokenData as $data) {
                if ($data['expire'] >= geoUtil::time()) {
                    $numTokens += $data['token_count'];
                }
            }
        }
        if (!$numTokens) {
            //no tokens for this user -- nothing to show here
            return false;
        }
        $text = geoAddon::getText('geo_addons', 'tokens');
        $tokens['label'] = $text['my_account_links_current_tokens_label'] . $numTokens;

        //also add a button to allow buying new tokens
        //(same as button in mini-cart, so use that to get label)
        $buyTokens = self::my_account_links_newButton();
        $buyTokens['link'] = DataAccess::getInstance()->get_site_setting('classifieds_file_name') . '?a=cart&action=new&main_type=tokens_purchase';
        $buyTokens['icon'] = false;

        $orderItemLinks[] = $tokens;
        $orderItemLinks[] = $buyTokens;

        $view->orderItemLinks = $orderItemLinks;
    }

    /**
     * Shortcut method to get the text for the tokens addon
     */
    private static function _getText()
    {
        return geoAddon::getText('geo_addons', 'tokens');
    }
}
