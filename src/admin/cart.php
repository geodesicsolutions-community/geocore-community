<?php

//cart.php


//File used for admin-side cart sessions

class AdminCart
{
    private $cart, $db, $userId;

    public function __construct()
    {
        $this->cart = geoCart::getInstance();
        $this->db = DataAccess::getInstance();

        $this->userId = null;

        if (isset($_GET['userId'])) {
            $this->userId = (int)$_GET['userId'];
            if ($this->userId) {
                //validate user
                $user = geoUser::getUser($this->userId);
                if (!$user) {
                    //not valid user ID
                    geoAdmin::m('Could not start an order, the user (' . $this->userId . ') is not valid.', geoAdmin::ERROR);
                    $this->userId = null;
                }
            }
        }
    }

    private function initCart($onlyInitItems = false)
    {
        if ($this->userId) {
            $user = geoUser::getUser($this->userId);
            if (!$user->status) {
                //User is suspended!  can't do anything for this user...

                //...UNLESS it is the anonymous user...
                $anon = geoAddon::getRegistry('anonymous_listing');
                if (!$anon || $anon->get('anon_user_id') != $this->userId) {
                    //...which, in this case, it is not
                    return false;
                } else {
                    //admin cart expects anonymous orders to have userId of 0 (NOT anon_user_id)
                    $this->userId = 0;
                }
            }
        }

        $this->cart->init($onlyInitItems, $this->userId);
        return true;
    }

    public function display_admin_cart()
    {
        if ($this->userId === null) {
            //User ID not known, need to redirect to page to select user
            header("Location: index.php?page=admin_cart_select_user");
            return;
        }

        if (!$this->initCart()) {
            header("Location: index.php?page=admin_cart_select_user&invalid_user=1");
            return;
        }

        $tpl_vars = $this->cart->getCommonTemplateVars();

        $tpl_vars['adminMsgs'] = geoAdmin::m();

        //what would normally be used to display the body?
        $view = geoView::getInstance();
        $view_vars = $view->getAllAssignedVars();

        $tpl_vars['cart_tpl_files'] = (isset($view_vars['geo_inc_files'])) ? $view_vars['geo_inc_files'] : array();
        $tpl_vars['cart_body'] = (isset($view_vars['body_html'])) ? $view_vars['body_html'] : '';
        if (isset($view_vars['body_html'])) {
            //reset body html so it isn't displayed twice
            $view->body_html = '';
        }
        $tpl_vars['cartUserId'] = $this->userId;
        $tpl_vars['cartUsername'] = $this->cart->user_data['username'];

        /*
         * CART Setup
         *
         */

        //get text for my account links since a lot of the text will be in there
        $this->cart->site->messages = $this->db->get_text(true, 10208);

        //don't display add to cart buttons since they are displayed by us
        $tpl_vars['geo_mini_cart_displayed'] = 1;

        $cartNumItems = 0;
        if ($this->cart->order) {
            foreach ($this->cart->order->getItem('parent') as $item) {
                //get the number of "main" order items (no parent, processOrder < 1000)
                $processOrder = $item->getProcessOrder();
                if ($processOrder < 1000) {
                    //anything with process order less than 1000 is considered "normal"
                    $cartNumItems++;
                }
            }
        }
        //cart data/link display
        $tpl_vars['cartItemCount'] = $cartNumItems;
        $tpl_vars['cartTotal'] = ($this->cart->order) ? $this->cart->getCartTotal() : 0;

        //cart "action"

        $cartLinks = array();

        $cartActionIndex = $tpl_vars['cartActionIndex'] = $this->cart->getAction();
        $tpl_vars['cartStepIndex'] = $this->cart->current_step;

        if ($this->cart->isInMiddleOfSomething()) {
            //In middle of something
            //get the text that will have actions
            $vars = array('action' => '', 'step' => 'my_account_links');
            //use getType as that will work even if in "stand alone" cart.
            $itemType = $this->cart->item->getType();
            $currentAction = $tpl_vars['cartAction'] = geoOrderItem::callDisplay('getActionName', $vars, '', $itemType);
            //let the template know whether it is a stand-alone cart or not.
            $tpl_vars['isStandalone'] = $this->cart->isStandaloneCart();
        } else {
            //not adding normal item to cart, so must be on main cart page (or checking out)
            //so show all buttons
            $cartLinks = geoOrderItem::callDisplay('my_account_links_newButton', null, 'array');
            foreach ($cartLinks as $a_name => $ldata) {
                if (!$ldata['label']) {
                    //this item doesn't want to show a link here!
                    continue;
                }
                if (!isset($ldata['link'])) {
                    //automatically set all the links so order items don't have to bother with
                    //that part, but if they do, don't set it here.
                    $cartLinks[$a_name]['link'] = $tpl_vars['cart_url'] . "&amp;action=new&amp;main_type=$a_name";
                }
            }
        }

        $tpl_vars['cartLinks'] = $cartLinks;
        //get all the carts for this admin user
        $tpl_vars['ordersInProgress'] = $this->getOrdersInProgress($this->userId);

        geoView::getInstance()->addCssFile('css/cart.css')
            ->setBodyVar($tpl_vars)
            ->setBodyTpl('cart/index.tpl');
    }

    public function display_admin_cart_swap()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_admin_cart();
        }

        $direction = trim($_GET['direction']);
        if (!in_array($direction, array('from','to'))) {
            geoAdmin::m('Invalid user ID specified, cannot perform action.', geoAdmin::ERROR);
            echo geoAdmin::m();
            geoView::getInstance()->setRendered(true);
            return;
        }

        if (!$this->userId) {
            geoAdmin::m('Invalid user ID specified, cannot perform action.', geoAdmin::ERROR);
            echo geoAdmin::m();
            geoView::getInstance()->setRendered(true);
            return;
        }

        //display "are you sure" custom message
        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $user = geoUser::getUser($this->userId);
        $tpl->assign('username', ($user) ? $user->username : 'Unknown');
        $tpl->assign('direction', $direction);
        $tpl->assign('userId', $this->userId);

        echo $tpl->fetch('cart/cart_swap_tool.tpl');
        geoView::getInstance()->setRendered(true);
    }

    public function update_admin_cart_swap()
    {
        if (!$this->userId) {
            //cannot do anything..
            geoAdmin::m('Invalid user ID, cannot swap items with client-side cart.', geoAdmin::ERROR);
            return false;
        }

        $direction = trim($_GET['direction']);
        if (!in_array($direction, array ('from','to'))) {
            geoAdmin::m('Invalid input, cannot process.', geoAdmin::ERROR);
            return false;
        }

        $adminId = (int)geoSession::getInstance()->getUserId();
        if (!$adminId) {
            //just in case, sanity check
            geoAdmin::m('Invalid/unknown admin ID, check the admin users data for problems.', geoAdmin::ERROR);
            return false;
        }

        $clientCart = $this->db->GetRow("SELECT * FROM " . geoTables::cart . " WHERE `user_id`=? AND `admin_id`=0 AND `order_item`!='-1'", array($this->userId));

        $adminCart = $this->db->GetRow("SELECT * FROM " . geoTables::cart . " WHERE `user_id`=? AND `admin_id`=? AND `order_item`!='-1'", array($this->userId, $adminId));

        //make sure "from" cart has something to copy over
        $fromCart = ($direction == 'to') ? $adminCart : $clientCart;
        $toCart = ($direction == 'to') ? $clientCart : $adminCart;

        $fromOrder = ($fromCart['order']) ? geoOrder::getOrder($fromCart['order']) : false;
        if (!$fromCart || !$fromCart['order'] || !$fromOrder) {
            geoAdmin::m('Could not retrieve "from" order.', geoAdmin::ERROR);
            return false;
        }
        if ($fromOrder->getStatus() != 'incomplete') {
            geoAdmin::m('Cannot move items, the order status is not incomplete.', geoAdmin::ERROR);
            return false;
        }
        if (count($fromOrder->getItem()) == 0) {
            geoAdmin::m('Nothing found that can be moved!', geoAdmin::ERROR);
            return false;
        }
        $toOrderId = (int)$toCart['order'];
        if ($direction === 'to' && (!$toCart || !$toOrderId)) {
            //make sure the "to" cart exists for the client (if "to" is admin, it should already be created)
            //if it does not, create it (and the order)
            $cartId = (int)geoCart::createNewCart($this->userId, 0, 0, false);
            if (!$cartId) {
                geoAdmin::m("Error when attempting to create cart session, cannot swap items.", geoAdmin::ERROR);
                return false;
            }
            $toCart = $this->db->GetRow("SELECT * FROM " . geoTables::cart . " WHERE `id`=$cartId");
            $toOrderId = (int)$toCart['order'];
        }
        if (!$toCart || !$toOrderId) {
            geoAdmin::m("Error creating new cart session, cannot swap items.", geoAdmin::ERROR);
            return false;
        }
        if ($toCart['step'] == 'process_order') {
            geoAdmin::m("Cannot alter cart, it is already to the process order step.", geoAdmin::ERROR);
            return false;
        }

        //go through each item in "from" order, and change order to "to" order.
        //but skip "special cases"
        $skipTypes = array ('subtotal_display','tax','auction_final_fees');
        $items = $fromOrder->getItem();
        $count = 0;
        foreach ($items as $item) {
            if (!is_object($item) || in_array($item->getType(), $skipTypes)) {
                continue;
            }
            $count++;
            $item->setOrder($toOrderId);
            $item->save();
        }
        if (!$count) {
            geoAdmin::m("Error:  Nothing found that could be moved!", geoAdmin::ERROR);
            return false;
        }
        //reset the items attached
        $fromOrder->unserialize();

        if ($direction == 'to' && $toCart['step'] == 'payment_choices') {
            //if direction is "to" (client) and step is on process payment page, change step
            //back to cart so user isn't able to process a payment before they see the
            //updated cart.
            $this->db->Execute("UPDATE " . geoTables::cart . " SET `step`='cart' WHERE `id`=? LIMIT 1", array($toCart['id']));
        } elseif ($direction == 'from' && strpos($fromCart['step'], ':') !== false && !in_array($fromCart['main_type'], $skipTypes)) {
            //if direction is "from" (client) and from step is one with : in it, set the
            //from step in admin cart to be same, and also match up other stuff
            $query_data = array(
                $clientCart['step'],
                $clientCart['order_item'],
                $clientCart['main_type'],
                $adminCart['id'],
            );
            $this->db->Execute("UPDATE " . geoTables::cart . " SET `step`=?, `order_item`=?, main_type=? WHERE id=?", $query_data);
            $this->db->Execute("UPDATE " . geoTables::cart . " SET `step`='cart', `order_item`=0, `main_type`='cart' WHERE id=?", array($clientCart['id']));
        }
        //touch the last time on the cart
        $this->db->Execute("UPDATE " . geoTables::cart . " SET `last_time`=" . geoUtil::time() . " WHERE `id` IN (" . (int)$fromCart['id'] . ", " . (int)$toCart['id'] . ") LIMIT 2");
        geoAdmin::m("Items moved successfully!");
        return true;
    }

    public function display_admin_cart_edit_price()
    {
        if (!geoAjax::isAjax()) {
            return $this->display_admin_cart();
        }
        //input checks...
        $item_id = (int)$_GET['item'];
        if (!$item_id) {
            geoAdmin::m('Invalid item ID specified, cannot perform action.', geoAdmin::ERROR);
            echo geoAdmin::m();
            geoView::getInstance()->setRendered(true);
            return;
        }

        if (!$this->initCart()) {
            geoAdmin::m('Invalid user ID specified, cannot perform action.', geoAdmin::ERROR);
            echo geoAdmin::m();
            geoView::getInstance()->setRendered(true);
            return;
        }

        $item = $this->cart->order->getItem($item_id);

        if (!$item) {
            geoAdmin::m('Could not retrieve item details, cannot perform action.', geoAdmin::ERROR);
            echo geoAdmin::m();
            geoView::getInstance()->setRendered(true);
            return;
        }
        $itemDetails = $item->getDisplayDetails(true);
        if (!$itemDetails['canAdminEditPrice'] || !geoMaster::is('site_fees')) {
            geoAdmin::m('You do not have permission to edit the price of this item.', geoAdmin::ERROR);
            echo geoAdmin::m();
            geoView::getInstance()->setRendered(true);
            return;
        }
        //display "are you sure" custom message
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($this->cart->getCommonTemplateVars());

        $tpl->assign('item_id', $item_id);

        $tpl->assign('cost', $item->getCost());
        $tpl->assign('precurrency', $this->db->get_site_setting('precurrency'));
        $tpl->assign('postcurrency', $this->db->get_site_setting('postcurrency'));

        $tpl->assign('itemDetails', $itemDetails);

        echo $tpl->fetch('cart/edit_price.tpl');
        geoView::getInstance()->setRendered(true);
    }

    public function update_admin_cart_edit_price()
    {
        //input checks...
        $item_id = (int)$_GET['item'];
        if (!$item_id) {
            geoAdmin::m('Invalid item ID specified, cannot perform action.', geoAdmin::ERROR);
            return false;
        }

        if (!$this->initCart(true)) {
            geoAdmin::m('Invalid user ID specified, cannot perform action.', geoAdmin::ERROR);
            return false;
        }

        $item = $this->cart->order->getItem($item_id);

        if (!$item) {
            geoAdmin::m('Could not retrieve item details, cannot perform action.', geoAdmin::ERROR);
            return false;
        }

        $itemDetails = $item->getDisplayDetails(true);
        if (!$itemDetails['canAdminEditPrice'] || !geoMaster::is('site_fees')) {
            geoAdmin::m('You do not have permission to edit the price of this item.', geoAdmin::ERROR);
            return false;
        }

        $cost = geoNumber::deformat($_POST['cost'], true);
        $item->setCost($cost);
        $this->cart->order->save();

        return true;
    }

    private function getOrdersInProgress($notUser = null)
    {
        $sql = "SELECT `user_id`, `last_time` FROM " . geoTables::cart . " WHERE `admin_id`=?";
        $query_data = array ((int)geoSession::getInstance()->getUserId());
        if ($notUser !== null) {
            $sql .= " AND `user_id`!=?";
            $query_data[] = (int)$notUser;
        }

        $rows = $this->db->Execute($sql, $query_data);
        $orders = array();
        foreach ($rows as $row) {
            $order = $row;
            $userId = $row['user_id'];
            if ($row['user_id'] == 0) {
                //get anon user
                $anonReg = geoAddon::getRegistry('anonymous_listing');
                if ($anonReg) {
                    $userId = $anonReg->get('anon_user_id', 0);
                }
            }
            $user = geoUser::getUser($userId);
            if ($user) {
                $order['username'] = $user->username;
            }
            $order['link'] = 'index.php?page=admin_cart&amp;userId=' . $row['user_id'];
            $orders[] = $order;
            unset($order, $user);
        }
        return $orders;
    }

    public function display_admin_cart_select_user()
    {
        $admin = geoAdmin::getInstance();

        $tpl_vars = array();

        if (isset($_GET['invalid_user'])) {
            //invalid user was specified, that is why they are showing this page
            geoAdmin::m('Invalid user specified for order, please select a valid user.', geoAdmin::ERROR);
        } elseif (isset($_GET['cart_deleted'])) {
            //cart removed, add note about it
            geoAdmin::m('The in-progress order you started for that user has been successfully deleted.', geoAdmin::SUCCESS);
        }

        //get all the carts for this admin user
        $tpl_vars['ordersInProgress'] = $this->getOrdersInProgress();

        $tpl_vars['adminMsgs'] = geoAdmin::m();

        //figure out if should show anon button or not
        if (DataAccess::getInstance()->get_site_setting('jit_registration')) {
            //do not allow button when jit is enabled
            $tpl_vars['allow_anon'] = false;
        } else {
            $tpl_vars['allow_anon'] = geoOrderItem::callDisplay('anonymousAllowed', null, 'bool_true');
        }

        if ($this->db->get_site_setting('verify_accounts')) {
            $txt = $this->db->get_text(true, 59);
            $tpl_vars['verify_url'] = $txt[500952];
        }

        $admin->setBodyTpl('cart/select_user.tpl')
            ->v()->setBodyVar($tpl_vars);
    }

    public function update_admin_cart_select_user()
    {
        if (!geoAjax::isAjax()) {
            if (isset($_POST['userId'])) {
                //user ID retrieved
                header("Location: index.php?page=admin_cart&userId=" . (int)$_POST['userId']);
                geoAdmin::getInstance()->setRendered(true);
                return;
            }
        }
        geoAdmin::getInstance()->setRendered(true);

        $userSearch = $origSearch = '' . $_POST['userSearch'];

        if (!strlen(trim($userSearch))) {
            return;
        }

        $userSearch = $this->db->qstr($userSearch . '%');


        $where = array();
        if ('' . (int)$origSearch == $origSearch) {
            $where [] = "ud.`id` LIKE $userSearch";
        }
        $where [] = "ud.`username` LIKE $userSearch";
        if (!$_POST['only_username']) {
            $where[] = "ud.`email` LIKE $userSearch";
            $where[] = "ud.`email2` LIKE $userSearch";
            $where[] = "ud.`company_name` LIKE $userSearch";
            $where[] = "ud.`firstname` LIKE $userSearch";
            $where[] = "ud.`lastname` LIKE $userSearch";
            $where[] = "ud.`address` LIKE $userSearch";
            $where[] = "ud.`phone` LIKE $userSearch";
        }

        $all = $this->db->GetAll("SELECT ud.*, l.status FROM " . geoTables::logins_table . " as l, " . geoTables::userdata_table . " as ud 
			WHERE ud.id=l.id AND ud.`id`!=1 AND l.status > 0 AND (" . implode(' OR ', $where) . ") ORDER BY username LIMIT 10");

        $ajax = geoAjax::getInstance();
        $ajax->jsonHeader();
        echo $ajax->encodeJSON($all);
    }

    public function display_admin_cart_delete()
    {
        header("Location: index.php?page=admin_cart_select_user&cart_deleted=1");
    }

    public function update_admin_cart_delete()
    {
        $userId = (int)$_GET['userId'];

        //get the cart ID
        $cartId = (int)$this->db->GetOne("SELECT `id` FROM " . geoTables::cart . " WHERE `user_id`=? AND `admin_id`=? ORDER BY `order_item` ASC", array ($userId, (int)geoSession::getInstance()->getUserId()));

        geoCart::remove($cartId);

        return true;
    }
}
