<?php

//order_items/custom_admin_fee.php
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


/**
 * Blank item, allows admin user to add a custom fee to an admin created order
 * to charge for whatever admin wants.
 * @since Version 6.0.0
 */
class custom_admin_feeOrderItem extends geoOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = "custom_admin_fee";

    /**
     * Optional, use this as a hassle-free way to determine the type without having to hard-code
     * the type everywhere else, instead use self::type
     *
     */
    const type = 'custom_admin_fee';

    /**
     * Needs to be the order that this item will be processed.  This is the default
     *
     * for example:  when computing tax the "tax function" (tax.php, defaultProcessOrder of 20,000)
     * will get all "totals" of all orderitems with a $defaultProcessOrder below 20,000 to get the
     * total amount to charge the tax on.
     *
     * System order item #'s:
     * < 1000 - "normal" order item (such as listing)
     * 10,000 - subtotal order item
     * 20,000 - tax order item
     * (total is handled by system, always at very bottom)
     *
     * note: different items can have the same defaultProcessOrder value.  Different criteria
     * then determine order like alphabetical
     *
     * @var int
     */
    protected $defaultProcessOrder = 15;
    /**
     * Optional, use this as a hassle-free way to determine the process order without having to hard-code
     * the # everywhere else, instead use self::defaultProcessOrder
     *
     */
    const defaultProcessOrder = 15;


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
        return true;
    }

    /**
     * Optional, but required if displayInAdmin() returns true.
     * Used: in admin, display items awaiting approval (only for main items, not for sub-items)
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        $title = $this->get('label');
        if (strlen($title) > 35) {
            $title = '<span title="' . htmlspecialchars($title) . '">' . geoString::substr($title, 0, 32) . '...' . '</span>';
        }

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    /**
     * Optional.
     * Used: In admin, when displaying an order item's details
     *
     * Return HTML for displaying or editing any information about this item, to
     * be displayed in the admin.  Should also call any children of this item.
     *
     * The other function that should work with this one, is adminItemUpdate.
     *
     * @param int $item_id
     * @return string
     */
    public static function adminItemDisplay($item_id)
    {
        $tpl_vars = array();

        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() !== self::type) {
            //not a custom admin fee
            return '';
        }

        $tpl_vars['label'] = $item->get('label');
        $tpl_vars['cost'] = $item->getCost();
        $tpl_vars['notify'] = $item->get('notify');
        if (!$tpl_vars['notify']) {
            $tpl_vars['notify'] = 'N/A';
        }
        $adminId = (int)$item->get('adminId');
        if ($adminId) {
            $adminUser = geoUser::getUser($adminId);
            if ($adminUser) {
                $adminUsername = $adminUser->username;
            } else {
                $adminUsername = 'Unknown';
            }
            $tpl_vars['admin'] = $adminUsername . " (#{$adminId})";
        } else {
            //shouldn't happen...
            $tpl_vars['admin'] = 'Unknown!';
        }

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);

        $html = $tpl->fetch('order_items/custom_admin_fee/admin_details.tpl');

        //Call children and let them display info about themselves as well
        $children = geoOrderItem::getChildrenTypes(self::type);
        $html .= geoOrderItem::callDisplay('adminItemDisplay', $item_id, '', $children);

        return $html;
    }

    /**
     * Optional.
     * Used: in geoCart::initItem()
     *
     * Used when creating a new item.  Usually for use when adding a new item to the cart, so will
     * usually only be called if this is a main order item with no parents.
     * @return bool Need to return true if it's ok to create item, false otherwise
     */
    public function geoCart_initItem_new()
    {
        if (!defined('IN_ADMIN')) {
            //do not allow creating on client side
            return false;
        }

        //set default price
        $this->setCost(5.00);

        return true;
    }

    /**
     * Optional.
     * Used: mainly in geoCart::initItem() but can be called elsewhere.
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
     * Used: in geoCart::initSteps() (and possibly other locations)
     *
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        if (!defined('IN_ADMIN')) {
            //do not add any steps when outside of the admin
            return;
        }
        $cart = geoCart::getInstance(); //get instance of cart
        $cart->addStep(self::type . ':details');

        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    public static function detailsCheckVars()
    {
        $cart = geoCart::getInstance();

        //check inputs..
        $settings = (array)$_POST['custom_admin_fee'];

        if (!$settings) {
            $cart->addError()
                ->addErrorMsg('cart_error', 'No settings received, cannot continue.');
            return;
        }
        if (!strlen(trim($settings['label']))) {
            $cart->addError()
                ->addErrorMsg('label', 'Item Label is required.');
        }
        $notify = trim($settings['notify']);
        if ($notify) {
            $notify = explode(',', $notify);
            foreach ($notify as $email) {
                $email = trim($email);
                if ($email && !geoString::isEmail($email)) {
                    $cart->addError()
                        ->addErrorMsg('notify', 'Invalid e-mail specified!');
                }
            }
        }

        if ($cart->errors) {
            //go ahead and set, so that even though some may not be valid, they are
            //still remembered...
            $cart->item->set('label', trim($settings['label']));
            $cart->item->setCost(geoNumber::deformat($settings['cost']));
            $cart->item->set('notify', trim($settings['notify']));
            $removable = (isset($settings['removable']) && $settings['removable']) ? 1 : false;
            $cart->item->set('removable', (isset($settings['removable']) && $settings['removable']));
        }
    }

    public static function detailsProcess()
    {
        $cart = geoCart::getInstance();

        $settings = (array)$_POST['custom_admin_fee'];

        //for consistency, set them here...
        $cart->item->set('label', trim($settings['label']));
        $cart->item->setCost(geoNumber::deformat($settings['cost']));

        $notify = trim($settings['notify']);
        if ($notify) {
            $emails = explode(',', $notify);
            $clean = array();
            foreach ($emails as $email) {
                $email = trim($email);
                if (!in_array($email, $clean) && geoString::isEmail($email)) {
                    //make double sure it is an actual email, and that there are no
                    //duplicate e-mails in list
                    $clean[] = $email;
                }
            }
            $notify = implode(', ', $clean);
        }
        $cart->item->set('notify', $notify);
        $removable = (isset($settings['removable']) && $settings['removable']) ? 1 : false;
        $cart->item->set('removable', (isset($settings['removable']) && $settings['removable']));
        //save which admin ID did this, since item could be passed off to client
        $cart->item->set('adminId', (int)$cart->session->getUserId());
    }

    public static function detailsLabel()
    {
        return 'Custom Fee Details';
    }

    public static function detailsDisplay()
    {
        $cart = geoCart::getInstance();
        $tpl_vars = $cart->getCommonTemplateVars();

        $tpl_vars['precurrency'] = $cart->db->get_site_setting('precurrency');
        $tpl_vars['postcurrency'] = $cart->db->get_site_setting('postcurrency');

        $tpl_vars['cost'] = $cart->item->getCost();
        //default to "Admin-created custom fee" for item label, for when item first created
        $tpl_vars['label'] = $cart->item->get('label', 'Admin-created custom fee');
        $tpl_vars['notify'] = $cart->item->get('notify');
        $tpl_vars['removable'] = $cart->item->get('removable');

        $tpl_vars['error_msgs'] = $cart->getErrorMsgs();

        geoView::getInstance()->setBodyTpl('order_items/custom_admin_fee/details.tpl')
            ->setBodyVar($tpl_vars);
    }

    /**
     * Required.
     * Used: in geoCart::initItem()
     *
     * @return bool True to force creating "parellel" cart just
     *  for this item, false otherwise.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }

    public static function geoCart_getCartItemDetails()
    {
        self::_initCustomFeeCart();
    }
    public static function geoCart_initSession_update()
    {
        //do stuff to make sure any custom fees are added
        self::_initCustomFeeCart();
    }

    /**
     * Required.
     * Used: In geoOrderItem class when loading the order item types, to get the
     * defailt parent types.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        return array();
    }

    /**
     * Required.
     * Used: Throughout the software, wherever order details are displayed.
     *
     * @param bool $inCart True if this is being called from inside the cart, false otherwise. Note: do NOT
     *  try to use the geoCart object if $inCart is false.
     * @return array|bool Either an associative array as documented above, or boolean false to hide this
     *  item from view.
     */
    public function getDisplayDetails($inCart)
    {
        $canDelete = (defined('IN_ADMIN') || $this->get('removable'));
        $return = array (
            'css_class' => '',
            'title' => '',
            'canEdit' => defined('IN_ADMIN'),
            'canDelete' => $canDelete,
            'canPreview' => false,
            'canAdminEditPrice' => true,
            'priceDisplay' => geoString::displayPrice($this->getCost()),
            'cost' => $this->getCost(),
            'total' => $this->getCost(),
            'children' => array()
        );

        if (defined('IN_ADMIN') && $inCart) {
            $return['title'] .= 'Custom Fee: ';
        }
        $return['title'] .= $this->get('label');

        if (defined('IN_ADMIN') && $inCart) {
            $return['title'] .= "<br />";
            $return['title'] .= "Can client remove: " . (($this->get('removable')) ? 'yes' : 'no') . " | ";
            $return['title'] .= "Send Notice to: " . (($this->get('notify')) ? $this->get('notify') : 'N/A');
        }

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
                    $displayResult = $item->getDisplayDetails($inCart);
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
     * Used: in geoCart::initSteps()
     *
     * Determine whether or not the other_details step should be added to the steps of adding this item
     * to the cart.  This should also check any child items if it does not need other_details itself.
     *
     * @return bool True to add other_details to steps, false otherwise.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        //if not true, you would still need to check children items like this:
        $children = geoOrderItem::getChildrenTypes(self::type);

        //can call directly, since this function is required.
        return geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails', null, 'bool_true', $children);
    }

    /**
     * Optional.
     * Used: in my_account_links module
     *
     * @return array
     */
    public static function my_account_links_newButton()
    {
        if (!defined('IN_ADMIN')) {
            //Only add button in admin panel
            return false;
        }
        return array (
            'label' => 'Add Custom Fee',
            'icon' => '',
        );
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
        if ($vars['step'] == 'my_account_links') {
            //short version
            return 'Custom Fee';
        } else {
            //action interupted text
            //text "placing new classified"
            return 'Adding a Custom Fee';
        }
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
        if (!$sendEmailNotices || !$this->get('notify')) {
            //nothing to actually do
            return;
        }


        if ($activate || $already_active) {
            //either activating, or going from active status to non-active...
            //either way, send out a notice to all e-mails that are entered in
            //notify field

            $notify = $this->get('notify');
            $notify = explode(',', $notify);

            //map of statuses to "alternate" versions of what to display
            $statuses = array (
                'pending' => 'Pending Payment',
                'pending_admin' => 'Pending',
            );

            $tpl_vars = array();
            $tpl_vars['label'] = $this->get('label');
            $tpl_vars['cost'] = $this->getCost();
            $tpl_vars['status'] = (isset($statuses[$newStatus])) ? $statuses[$newStatus] : ucwords($newStatus);
            $tpl_vars['order_item_id'] = $this->getId();
            $tpl_vars['order_id'] = 0;//default to 0 in case could not retrieve order
            $order = $this->getOrder();
            if ($order) {
                $tpl_vars['order_id'] = $order->getId();
                $buyerId = $order->getBuyer();
                $user = geoUser::getUser($buyerId);
                if ($user) {
                    $username = $user->username;
                } else {
                    $username = 'Unknown';
                }
                $tpl_vars['buyer'] = $username . " (#{$buyerId})";
            } else {
                $tpl_vars['buyer'] = 'Unknown (order data not available)';
            }

            $adminId = (int)$this->get('adminId');
            if ($adminId) {
                $adminUser = geoUser::getUser($adminId);
                if ($adminUser) {
                    $adminUsername = $adminUser->username;
                } else {
                    $adminUsername = 'Unknown';
                }
                $tpl_vars['admin'] = $adminUsername . " (#{$adminId})";
            } else {
                //shouldn't happen...
                $tpl_vars['admin'] = 'Unknown!';
            }

            $subject = '[Custom Fee Notice : ' . $tpl_vars['status'] . '] ' . $this->get('label');

            //send each notice seperately, so that to address only has single address,
            //and so that "email" displayed in e-mail contents is correct
            foreach ($notify as $email) {
                $tpl_vars['email'] = $email = trim($email);
                if (!geoString::isEmail($email)) {
                    //sanity check, just to be tripple sure...
                    continue;
                }
                $tpl = new geoTemplate(geoTemplate::SYSTEM, 'emails');
                $tpl->assign($tpl_vars);

                $contents = $tpl->fetch('custom_admin_fee_notice.tpl');

                geoEmail::sendMail($email, $subject, $contents, 0, 0, 0, 'text/html');

                unset($tpl);
            }
        }

        //NOTE: do not need to call children, parent does that for us :)
    }

    public function processRemove()
    {
        //this should, in theory, move this item to a "custom fee" specific order,
        //then next time the cart is attempting to be removed, it will be because
        //this item will no longer be attached.
        return $this->processRemoveData();
    }

    public function processRemoveData()
    {
        //Block removing custom fees if this isn't paid for!

        $order = $this->getOrder();
        if (!$order) {
            //something wrong with order, go ahead and allow removal
            return true;
        }

        if ($order->getStatus() == 'active' || $this->getStatus() == 'active') {
            //order is active, so custom fees paid for, so allow removal.
            return true;
        }
        if ($order->getAdmin()) {
            //Go ahead and let admin orders disapear...
            return true;
        }

        if ($order->get('custom_admin_fee')) {
            //this is special order, update the time on it
            $order->setCreated(geoUtil::time());

            $this->setCreated(geoUtil::time());

            $order->save();
        } else {
            //move this order item over to a custom admin fee order item!
            //find an order that is specifically for admin custom fees for this user.
            $db = DataAccess::getInstance();
            $sql = "SELECT o.`id` FROM " . geoTables::order . " as o, " . geoTables::order_registry . " as oreg WHERE o.`buyer` = ? AND o.`admin` = 0 AND o.`status` = 'pending' AND oreg.order=o.id AND oreg.index_key = 'custom_admin_fee' AND oreg.val_string = '1' LIMIT 1";
            $row = $db->GetRow($sql, array($listing->seller));

            $nOrder = false;
            if (isset($row['id']) && $row['id']) {
                $nOrder = geoOrder::getOrder($row['id']);
            }

            if (!$nOrder || !is_object($nOrder)) {
                $nOrder = new geoOrder();
                //identify itself as being for final fees only
                $nOrder->set('custom_admin_fee', '1');
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

    private static $_initCF_called = false;
    private static function _initCustomFeeCart()
    {
        if (self::$_initCF_called || defined('IN_ADMIN')) {
            //only init once
            return;
        }
        self::$_initCF_called = true;

        $cart = geoCart::getInstance();
        if ($cart->cart_variables['order_item'] == -1 || $cart->user_data['id'] == 0) {
            //this is a stand-alone cart, don't auto-add ourself to it!
            return;
        }

        //see if there are any orders for auction final fees
        $sql = "SELECT o.`id` FROM " . geoTables::order . " as o, " . geoTables::order_registry . " as oreg WHERE o.`buyer` = ? AND o.`admin` = 0 AND o.`status` = 'pending' AND oreg.order=o.id AND oreg.index_key = 'custom_admin_fee' AND oreg.val_string = '1' LIMIT 1";
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
        }
    }
}
