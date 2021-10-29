<?php

//order_items/verify_account.php
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


class verify_accountOrderItem extends geoOrderItem
{
    protected $type = "verify_account";
    const type = 'verify_account';
    protected $defaultProcessOrder = 12;
    const defaultProcessOrder = 12;


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
        if (!self::_enabled()) {
            //account verify not enabled
            return;
        }
        $user_id = (int)$user_id;
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, 59);

        if (geoUser::isVerified($user_id)) {
            $value = "<img src=\"../" . geoTemplate::getUrl('', $msgs[500957]) . "\" alt='' /><br />";
            $value .= "<a href=\"index.php?page=users_view&amp;auto_save=1&amp;verify_account=no&amp;b={$user_id}\" class='mini_button lightUpLink'>Un-Verify Account</a>";
        } else {
            $value = "Not verified<br /><a href=\"index.php?page=users_view&amp;auto_save=2&amp;verify_account=yes&amp;b={$user_id}\" class='mini_button lightUpLink'>Verify Account</a>";
        }

        $html = geoHTML::addOption('Verified Account', $value);
        return $html;
    }

    /**
     * Optional.
     * Used: in Admin_user_management::update_users_view() (in file admin/admin_user_management_class.php)
     *
     * Used to update information about a user that may have been collected in Admin_site_display_user_data()
     *
     * @param int $user_id ID NOT VERIFIED at time this is called!
     */
    public static function Admin_user_management_update_users_view($user_id)
    {
        if (!isset($_POST['verify_account'])) {
            //nothing to do
            return;
        }
        $verify_account = $_POST['verify_account'];
        if (!in_array($verify_account, array('yes','no'))) {
            //invalid!
            geoAdmin::m("Invalid selection for user.", geoAdmin::ERROR);
            return;
        }
        $user_id = (int)$user_id;
        $user = geoUser::getUser($user_id);
        if (!$user || $user_id <= 1) {
            //nope can't do it
            return;
        }
        $user->verified = $verify_account;
        geoAdmin::m("User Account " . (($verify_account == 'no') ? 'Un-' : '') . "Verified.");
        return true;
    }

    /**
     * Required.
     * Used: in admin, PricePlanItemManage class in various places.
     *
     * Return true to display this order item planItem settings in the admin,
     * or false to hide it in the admin.
     *
     * @return bool
     */
    public function displayInAdmin()
    {
        return self::_enabled();
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
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $db = DataAccess::getInstance();

        $tpl_vars = array();
        $tpl_vars['enabled'] = $planItem->getEnabled();
        $tpl_vars['require_for_listing'] = $planItem->get('require_for_listing');
        $tpl_vars['require_for_bid'] = $planItem->get('require_for_bid');
        $tpl_vars['is_auctions'] = geoMaster::is('auctions');
        $tpl_vars['amount'] = $planItem->get('amount', '1.00');
        $tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
        $tpl_vars['account_balance_possible'] = geoPC::is_ent();
        $tpl_vars['apply_to_balance'] = $planItem->get('apply_to_balance');

        $tpl->assign($tpl_vars);
        return $tpl->fetch('order_items/verify_account/settings.tpl');
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
        $settings = $_POST['verify_account'];

        $planItem->setEnabled(isset($settings['enabled']) && $settings['enabled']);
        $planItem->set('amount', geoNumber::deformat($settings['amount']));
        $planItem->set('require_for_listing', isset($settings['require_for_listing']) && $settings['require_for_listing']);
        if (geoMaster::is('auctions')) {
            $planItem->set('require_for_bid', isset($settings['require_for_bid']) && $settings['require_for_bid']);
        }

        $planItem->set('apply_to_balance', isset($settings['apply_to_balance']) && $settings['apply_to_balance']);

        return true;
    }

    public static function geoCart_cartCheckVars()
    {
        $cart = geoCart::getInstance();
        $verifyItem = geoPlanItem::getPlanItem('verify_account', $cart->price_plan['price_plan_id']);

        if (self::_enabled() && $verifyItem->get('require_for_listing') && !geoUser::isVerified($cart->user_data['id']) && $cart->cart_variables['order_item'] != -1) {
            //require verified account to place listings, but account is not verified..
            //do one more check, whether account will be auto-verified.

            $db = DataAccess::getInstance();
            $totalCost = $cart->getCartTotal();

            if ($totalCost <= 0 || !$db->get_site_setting('auto_verify_with_payment')) {
                //Either there is no cart cost, or there is a cost but settings
                //are set to NOT auto-verify account when payment is received

                $url = $cart->db->get_site_setting('classifieds_url') . '?a=cart&amp;action=new&amp;main_type=verify_account';

                $cart->addError()
                    ->addErrorMsg('verify_account', $cart->site->messages[501661] . '<a href="' . $url . '">' . $cart->site->messages[501662] . '</a>');
            }
        }
    }


    /**
     * Optional, but required if displayInAdmin() returns true.
     * Used: in admin, display items awaiting approval (only for main items, not for sub-items)
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        //not much to display for the "item" so just display icon
        $txt = DataAccess::getInstance()->get_text(true, 59);
        $title = "<img src=\"../" . geoTemplate::getUrl('', $txt[500952]) . "\" alt=\"\" />";
        return array(
            'type' => 'Verify Account',
            'title' => $title
        );
    }

    /**
     * Optional.
     * Used: In admin, when displaying the order item type for a particular item, used
     * in various places in the admin.
     *
     * @return string
     */
    public function getTypeTitle()
    {
        return 'Verify Account';
    }

    /**
     * Optional.
     * Used: mainly in geoCart::initItem() but can be called elsewhere.
     *
     * Used when no one is logged in, to determine if anonymous sessions
     * are allowed to use this item type.
     *
     * If this function is not defined, it will be assumed that this item
     * is NOT allowed with anonymous sessions, and will not allow this item
     * to be used without first logging in.
     *
     * @return bool Need to return true if item allowed to be used in an
     *  anonymous environment, false otherwise.
     */
    public static function anonymousAllowed()
    {
        return false;
    }

    /**
     * Required.
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        //get steps from children as well.  Children items are not called automatically, to allow parent items to
        //have more control over "children" items.
        $children = geoOrderItem::getChildrenTypes('_template');
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    /**
     * Required.
     *
     * @return bool True to force creating "parellel" cart just
     *  for this item, false otherwise.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //Force the verify account item into outside cart so that discount codes
        //can't apply to the verify account cost, especially since
        //the cost could be going toward account balance later
        return true;
    }

    /**
     * Required.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        //for "parent" order item, returne empty string.
        return array();
    }

    public function geoCart_initItem_new()
    {
        if (!self::_enabled()) {
            return false;
        }
        $cart = geoCart::getInstance();
        $db = DataAccess::getInstance();
        if ($db->get_site_setting('auto_verify_with_payment') && $cart->getCartTotal() > 0) {
            //don't do it if already stuff that costs money in the cart, and it is
            //set to auto-verify with a payment
            return false;
        }
        $price_plan_id = (!geoMaster::is('classifieds')) ? $cart->user_data['auction_price_plan_id'] : $cart->user_data['price_plan_id'];
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan_id);
        if (!$planItem->getEnabled()) {
            return false;
        }
        $this->setCost($planItem->get('amount', '1.00'));
        return true;
    }

    /**
     * Required.
     * Used: Throughout the software, wherever order details are displayed.
     *
     * Used to get display details about item, and any child items as well, both in the main
     * cart view, and other places where the order details are displayed, including within
     * the admin.  Should return an associative array, that follows:
     * array(
     *  'css_class' => string, //leave empty string for default class, only applies in cart view
     *  'title' => string,
     *  'canEdit' => bool, //whether can edit it or not, only applies in cart view
     *  'canDelete' => bool, //whether can remove from cart or not, only applies in cart view
     *  'canPreview' => bool, //whether can preview the item or not, only applies in cart view
     *  'priceDisplay' => string, //price to display
     *  'cost' => double, //amount this adds to the total, what getCost returns but positive
     *  'total' => double, //amount this AND all children adds to the total
     *  'children' => array(), //optional, should be array of child items, with the index
     *                          //being the item's ID, and the contents being associative array like
     *                          //this one.  Careful not to get into any infinite loops...
     * )
     *
     * @param bool $inCart True if this is being called from inside the cart, false otherwise. Note: do NOT
     *  try to use the geoCart object if $inCart is false.
     * @return array|bool Either an associative array as documented above, or boolean false to hide this
     *  item from view.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, 10202);

        $return = array (
            'title' => $msgs[500958],
            'canEdit' => false,
            'canDelete' => true,
            'canPreview' => false,
            'canAdminEditPrice' => true,
            'priceDisplay' => geoString::displayPrice($this->getCost(), false, false, 'cart'),
            'cost' => $this->getCost(),
            'total' => $this->getCost(),
            'children' => array()
        );

        //add icon to end
        $return['title'] .= '<br /><img src="' . ((defined('IN_ADMIN')) ? '../' : '') . geoTemplate::getUrl('', $msgs[500952]) . '" alt="" />';
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
     * Required.
     *
     * @return bool True to add other_details to steps, false otherwise.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
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
        if (self::isAnonymous() || !self::_enabled()) {
            //it is anon or verify accounts disabled
            return;
        }
        $cart = geoCart::getInstance();
        if ($cart->user_data['verified'] == 'yes') {
            //user already verified (check not done above since did not pass in user ID)
            return;
        }

        //make sure order item not disabled
        $price_plan_id = (!geoMaster::is('classifieds')) ? $cart->user_data['auction_price_plan_id'] : $cart->user_data['price_plan_id'];
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan_id);
        if (!$planItem->getEnabled()) {
            //order item disabled
            return;
        }

        //go ahead and display the button...
        $msgs = DataAccess::getInstance()->get_text(true);
        if ($inModule) {
            //return module style
            return array ('label' => $msgs[500959]);
        }
        if (!isset($msgs[500960])) {
            //haven't gotten text for this page yet -- get it explicitly from cart main
            $msgs = DataAccess::getInstance()->get_text(true, 10202);
        }
        return $msgs[500960];
    }

    /**
     * Optional.
     * Used: in my_account_links module
     *
     * Used only for "parent" items, this should return an associative array:
     * array (
     *  'label' => 'Link Text',
     *  'icon' => '<img src="image.jpg" alt="new something" style="vertical-align: middle;" />'
     * )
     *
     * @return array
     */
    public static function my_account_links_newButton()
    {
        //let other method do all the checks and stuff
        return self::geoCart_cartDisplay_newButton(true);
    }

    /**
     * Optional.
     * Used: In the admin when admin activates order or item, or on client side when payment is
     * made and settings are such that it does not need admin approval to activate the item.
     *
     * If this is not implemented here, the parent class will do common stuff for you, like call
     * child items and actually set the status
     *
     * This is responsible for actually changing the status of the item, as well as anything such
     * as activating/deactivating a listing depending on what the previous status is, and what it is
     * being changed to.  Use template function as a guide, and add customization where comments
     * specify to.  Remember to call children where appropriate if you decide not to call the parent
     * to do it for you.
     *
     * It can be assumed that if this function is called, all the checks as to whether the item should be
     * pending or not have already been done, however there may be other custom checks you wish to do.
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
            //verify the user

            $order = $this->getOrder();
            if (!$order) {
                //can't do anything
                return;
            }
            $user_id = (int)$order->getBuyer();
            if ($user_id) {
                //set user to verified
                $user = geoUser::getUser($user_id);
                $user->verified = 'yes';
                if (!$this->get('applied_to_balance')) {
                    $this->set('applied_to_balance', 1);
                    $planItem = geoPlanItem::getPlanItem(self::type, $this->getPricePlan());
                    if ($planItem->get('apply_to_balance')) {
                        //apply amount to balance
                        account_balanceOrderItem::adjustUserBalance($this->getCost(), $user_id, $order->getId());
                    }
                }
            }
        } elseif (!$activate && $already_active) {
            //don't do anything when de-activating, since user verified status could
            //have gotten that way another way.
        }
    }

    /**
     * Optional
     * Used: in User_management_information::display_user_data()
     *
     * Use this to display info on the user info page.  Stuff like displaying
     * current account balance, tokens remaining, etc.  This will appear below
     * the price plan info
     *
     * @return array Associative array, with
     *  the structure array ('label' => 'Left side','value' => 'Right side')
     */
    public static function User_management_information_display_user_data()
    {
        if (!self::_enabled()) {
            //account verify not enabled
            return;
        }
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, 37);

        $verify_icon = "<img src=\"" . geoTemplate::getUrl('', $msgs[500952]) . "\" alt='' />";

        $not_verified = $msgs[500955];

        $user = geoUser::getUser(geoSession::getInstance()->getUserId());

        $price_plan_id = (!geoMaster::is('classifieds')) ? $user->auction_price_plan_id : $user->price_plan_id;
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan_id);

        if ($msgs[500956] && $planItem->getEnabled()) {
            $verify_link = $db->get_site_setting('classifieds_file_name') . "?a=cart&amp;action=new&amp;main_type=verify_account";

            $not_verified .= "<br /><a href=\"{$verify_link}\">{$msgs[500956]}</a>";
        }
        $value = (geoUser::isVerified($user->id)) ? $verify_icon : $not_verified;

        return array('label' => $msgs[500954], 'value' => $value);
    }

    private static function _enabled($user_id = null)
    {
        $db = DataAccess::getInstance();
        if (!$db->get_site_setting('verify_accounts')) {
            //disabled
            return false;
        }

        if ($user_id) {
            $user = geoUser::getUser($user_id);
            return ($user->verified === 'yes');
        }
        return true;
    }
}
