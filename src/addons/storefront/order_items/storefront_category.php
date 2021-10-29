<?php

//order_items/storefront_category.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.02.1-12-g223ffae
##
##################################

# Storefront addon

class storefront_categoryOrderItem extends geoOrderItem
{
    protected $type = "storefront_category";
    const type = 'storefront_category';
    protected $defaultProcessOrder = 20;
    const defaultProcessOrder = 20;


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
        return false;
    }

    /**
     * Required.
     *
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        //get steps from children as well.  Children items are not called automatically, to allow parent items to
        //have more control over "children" items.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    /**
     * Required.
     *
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }

    /**
     * Required.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        return array(
            'classified',
            'classified_recurring',
            'auction',
            'renew_upgrade',
            'listing_edit',
            'listing_change_admin',
            );
    }

    public static function listing_edit_getChoices()
    {
        $cart = geoCart::getInstance();
        $user_has_storefront = geoAddon::getUtil('storefront')->userHasCurrentSubscription($cart->user_data['id']);

        $planItem = geoPlanItem::getPlanItem('storefront_subscription', $cart->item->getPricePlan(), 0);
        if ((!$planItem->getEnabled()) || ($user_has_storefront == 0)) {
            //check that the user is not attached to another user
            $share_fees = geoAddon::getUtil('share_fees');
            if (($share_fees) && ($share_fees->active) && ($share_fees->store_category_display)) {
                $user_attached_to = $share_fees->getUserAttachedTo($cart->user_data['id']);
                if ($user_attached_to != 0) {
                    $attached_to_user_info = geoUser::getUser($user_attached_to);
                    $attacheduserplanItem = geoPlanItem::getPlanItem('storefront_subscription', $attached_to_user_info->auction_price_plan_id, 0);
                    $attached_user_has_storefront = geoAddon::getUtil('storefront')->userHasCurrentSubscription($user_attached_to);
                    if ((!$attacheduserplanItem->getEnabled()) || ($attached_user_has_storefront == 0)) {
                        //attached to user price plan didn't have storefront ability either
                        //echo "attached user does not have storefronts either<br>\n";
                        return;
                    }
                } else {
                    return;
                }
            } else {
                return;
            }
        }
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        return array ('storefront_category:editCategory' => $msgs['edit_category_txt']);
    }

    public static function editCategoryCheckVars()
    {
        $cart = geoCart::getInstance();
        self::geoCart_other_detailsCheckVars($cart->site->session_variables);
    }

    public static function editCategoryProcess()
    {
        self::geoCart_other_detailsProcess();
    }

    public static function editCategoryDisplay()
    {
        $cart = geoCart::getInstance();
        listing_editOrderItem::fixStepLabels();
        $cart->displaySingleOtherDetails(self::type);
    }

    public static function editCategoryLabel()
    {
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        return $msgs['edit_category_step'];
    }

    /**
     * Required.
     *
     * @return array An associative array as described above.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $msgs = geoAddon::getText('geo_addons', 'storefront');
        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => $msgs['storefront_category_cart_title'],//text that is displayed for this item in list of items purchased.
            'canEdit' => true, //show edit button for item?
            'priceDisplay' => '&nbsp;', //Price as it is displayed
            'cost' => 0, //amount this adds to the total, what getCost returns
            'total' => 0, //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()
        );
        $return['title'] .= " - {$this->get('category_name')}";
        if ($this->getParent()->getType() == 'listing_edit') {
            //do NOT allow edit for listing edits, it will screw up
            //the whole session diff thing.
            $return['canEdit'] = false;
        }
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

    public function processStatusChange($newStatus, $sendEmailNotices = false, $updateCategoryCount = false)
    {
        //set to be active
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
        $parent = $this->getParent();
        if ($parent->getType() == 'listing_edit') {
            //need to actually apply changes to listing, it's not done by listing edit!
            $listing = geoListing::getListing($parent->get('listing_id'));
            if (!$listing) {
                //something went wrong, abort! abort!
                return false;
            }
            //get storefront category we saved to the order item earlier
            $newCat = $this->get('storefront_category');
            $listing->storefront_category = $newCat;
        }
    }
    public static function geoCart_other_detailsCheckVars($c_data = array())
    {
        $cart = geoCart::getInstance();
        //do checking of vars here

        //Can remove check once this addon is meant for working ONLY in 4.1
        $parents = (is_callable(array('geoOrderItem','getParentTypesFor'))) ? geoOrderItem::getParentTypesFor(self::type) : self::getParentTypes();
        if ($cart->main_type != self::type && !in_array($cart->main_type, $parents)) {
            //item being added does not have anything to do with this item, so no need to check vars.
            return;
        }

        $user_has_storefront = geoAddon::getUtil('storefront')->userHasCurrentSubscription($cart->user_data['id']);

        $planItem = geoPlanItem::getPlanItem('storefront_subscription', $cart->item->getPricePlan(), 0);

        if (($user_has_storefront == 0) || (!$planItem->getEnabled())) {
            //user placing listing does not have a storefront
            //or the price plan they are attached to does not have storefront_subscriptions
            //storefront subscriptions may be OFF for the attaching user but not the attached to user
            //if share fees check the price plan of the client attached to
            $share_fees = geoAddon::getUtil('share_fees');
            if (($share_fees) && ($share_fees->active) && ($share_fees->store_category_display)) {
                //check if user is attached to another user
                $user_attached_to = $share_fees->getUserAttachedTo($cart->user_data['id']);
                if ($user_attached_to != 0) {
                    $attached_to_user_info = geoUser::getUser($user_attached_to);
                    $attacheduserplanItem = geoPlanItem::getPlanItem('storefront_subscription', $attached_to_user_info->auction_price_plan_id, 0);
                    $attached_user_has_storefront = geoAddon::getUtil('storefront')->userHasCurrentSubscription($user_attached_to);
                    if ((!$attacheduserplanItem->getEnabled()) || ($attached_user_has_storefront == 0)) {
                        //attached to user price plan didn't have storefront ability either
                        //echo "attached user does not have storefronts either<br>\n";
                        return;
                    } else {
                        //echo "attached to user has storefronts ability in price plan while the attaching user does not<br>\n";
                    }
                } else {
                    return;
                }
            } else {
                //echo "neither attached to user nor attaching user has storefront ability<br>\n";
                return;
            }
        }
        $cat_id = 0;
        if (isset($_POST['c']['storefront_category']) || isset($c_data['storefront_category'])) {
            $cat_id = intval($_POST['c']['storefront_category']);
            if (!$cat_id && isset($c_data['storefront_category'])) {
                $cat_id = intval($c_data['storefront_category']);
            }

            //make sure it is valid category
            $table = geoAddon::getUtil('storefront')->tables();
            $sql = "SELECT * FROM `$table->categories` WHERE `category_id` = ? AND `owner` = ?";
            $row = $cart->db->GetRow($sql, array($cat_id, $cart->user_data['id']));
            if ($row === false) {
                die($db->ErrorMsg() . "<br /> $sql");
            }
            if (!isset($row['category_name'])) {
                //check the attached to user for storefront category if share fees addon is in use.
                $share_fees = geoAddon::getUtil('share_fees');
                if (($share_fees) && ($share_fees->active) && ($share_fees->store_category_display)) {
                    //echo "checking to see if storefront is one of attached to users storefronts<br>\n";
                    //share fees addon is in use.  Check that storefront could be the attached to user's storefront category
                    //check if user is attached to another user
                    $user_attached_to = $share_fees->getUserAttachedTo($cart->user_data['id']);
                    //echo $user_attached_to." is the user attached to when checking attached to users storefront<br>\n";
                    //echo "<p>";
                    //var_dump($user_attached_to);
                    //echo "</p>";
                    //echo $table->categories." is the table to check<br>\n";
                    if ($user_attached_to != 0) {
                        $attached_to_sql = "SELECT * FROM `" . $table->categories . "` WHERE `category_id` = ? and owner = ? ";
                        //echo $attached_to_sql." is the sql checking the attached to users category<br>\n";
                        $check_attached_store_categories = $cart->db->GetRow($attached_to_sql, array($cat_id, $user_attached_to));
                        //echo $check_attached_store_categories['category_name']." is returned category name<bR>\n";
                        if (!isset($check_attached_store_categories['category_name'])) {
                            //echo "storefront it is not attached to user's either<bR>\n";
                            //echo "setting store cat to 0<bR>\n";
                            $cart->addError()->addErrorMsg('storefront', 'Invalid Storefront Category specified.');
                            $cat_id = 0;
                        } else {
                            //echo "this is a category of attached to user's storefront<Br>\n";
                            $attached_to_user_info = geoUser::getUser($user_attached_to);
                            $cat_name = $check_attached_store_categories['category_name'] . "(" . $attached_to_user_info->username . ")";
                        }
                    } else {
                        //echo "setting store cat to 0<bR>\n";
                        $cat_id = 0;
                    }
                } elseif ($cat_id != 0) {
                    $cart->addError()
                        ->addErrorMsg('storefront', 'Invalid Storefront Category specified.');

                    $cat_id = 0;
                }
            } else {
                $cat_name = $row['category_name'];
            }
            $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());

            //get current attached bolding, if exists..
            if ($cart->item->getType() == self::type) {
                $order_item = $cart->item;
            } else {
                $items = $cart->order->getItem(self::type);
                $order_item = false;
                if (is_array($items)) {
                    foreach ($items as $i => $val) {
                        if (is_object($val) && is_object($val->getParent())) {
                            $p = $val->getParent();
                            if ($p->getId() == $cart->item->getId()) {
                                //parent is main item, the type is bolding, so whoohoo...
                                $order_item = $val;
                                break;
                            }
                        }
                    }
                }
            }
            if (!$cat_id) {
                if ($order_item) {
                    $id = $order_item->getId();
                    geoOrderItem::remove($id);
                    $cart->order->detachItem($id);
                }
            } else {
                if (!$order_item) {
                    $order_item = new storefront_categoryOrderItem();
                    $order_item->setParent($cart->item);//this is a child of the parent

                    $order_item->setOrder($cart->order);

                    $order_item->save();//make sure it's serialized
                    $cart->order->addItem($order_item);
                }
                $order_item->setCost(0);
                $order_item->setCreated($cart->order->getCreated());
                $order_item->setPricePlan($cart->item->getPricePlan());

                //set id of listing, if known
                if ($cart->item->get('listing_id', 0) > 0) {
                    $order_item->set('listing_id', $cart->item->get('listing_id'));
                }
                $order_item->set('category_name', $cat_name);
                $order_item->set('storefront_category', $cat_id);
                if ($cart->item != $order_item) {
                    $cart->site->session_variables['storefront_category'] = $cat_id;
                } else {
                    //manually set session vars
                    $parent = $order_item->getParent();
                    if ($parent) {
                        $session_variables = $parent->get('session_variables');
                        $session_variables['storefront_category'] = $cat_id;
                        $parent->set('session_variables', $session_variables);
                    }
                }
                if ($order_item->getParent()->getType() == 'listing_edit') {
                    //listing edit, most likely nothing is done to save changes to session vars
                    $cart->item->set('storefront_category', $cat_id); //for easy access when making changes live later
                    listing_editOrderItem::saveSessionVars();
                    //die ('saved session vars!  these session vars: <pre>'.print_r(listing_editOrderItem::getSessionVars(),1).'</pre><br />session vars: <pre>'.print_r($cart->site->session_variables,1).'<br />'.print_r($order_item->getParent(),1));
                }
            }
        }

        //make sure to call check vars for children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);
    }

    /**
     * Optional.
     * Used: in geoCart::other_detailsProcess()
     *
     * Used by items that are displayed & processed at the built-in other details step, or
     * items that may have children at this step.  Things like adding or removing an item
     * based on a checkbox selection should be done here.
     *
     * Note that this is called for all order items, so need to check to see if main type
     * warrents it processing for that main type first.
     *
     * This can be used as a template for other Process functions for specific not-built-in steps
     *
     */
    public static function geoCart_other_detailsProcess()
    {

        //get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
    }

    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();

        //echo $cart->user_data['id']." is the user id<bR>\n";
        $get_storefront_categories_from = $cart->user_data['id'];
        $user_has_storefront = geoAddon::getUtil('storefront')->userHasCurrentSubscription($get_storefront_categories_from);
        //echo $user_has_storefront." is user_has_storefront<br>\n";
        if ($user_has_storefront == 0) {
            //current user does not have a storefront of their own so see if there is an attached user
            //check if attached to another user to see if should display those storefront categories
            $share_fees = geoAddon::getUtil('share_fees');
            //if (($share_fees) && ($share_fees->active) && ($share_fees->store_category_display) && (geoMaster::is('auctions')) && ($cart->main_type == "auction" || $cart->main_type == "listing_edit")) {
            if ((($share_fees) && ($share_fees->active) && ($share_fees->store_category_display)) && ($cart->main_type == "auction" || $cart->main_type == "classified" || $cart->main_type == "listing_edit")) {
                //check if user is attached to another user
                $user_attached_to = geoAddon::getUtil('share_fees')->getUserAttachedTo($get_storefront_categories_from);
                if ($user_attached_to != 0) {
                    $current_attached_user = geoUser::getUser($user_attached_to);
                    //show storefront category choices for attached to store even though they may have their storefront off
                    //$current_attached_user_onhold = (is_object($current_attached_user))? $current_attached_user->storefront_on_hold : 0;
                    $attached_has_storefront = geoAddon::getUtil('storefront')->userHasCurrentSubscription($user_attached_to);
                    if (($attached_has_storefront == 0) || (!$share_fees->store_category_display)) {
                        //either the attached user has no storefront or admin does not allow attachment to attached to user storefront to display listings in their storefront.
                        //echo "either the attached user has no storefront or admin does not allow attachment to attached to user storefront to display listings in their storefront.<br>";
                        return;
                    } else {
                        $get_storefront_categories_from = $user_attached_to;
                    }
                } else {
                    //echo "user attached to is 0<br>";
                    return;
                }
            } else {
                //storefront expired and no storefront attached to
                //echo "storefront expired and no storefront attached to<br>\n";
                return;
            }
        } else {
            //echo "user has a storefront<br>\n";
            //current user has there own storefront...so do checks for it

            //Can remove check once this addon is meant for working ONLY in 4.1
            $parents = (is_callable(array('geoOrderItem','getParentTypesFor'))) ? geoOrderItem::getParentTypesFor(self::type) : self::getParentTypes();

            if ($cart->main_type != self::type && !in_array($cart->item->getType(), $parents)) {
                //not something we're interested in.
                //echo self::type." is self type<br>\n";
                //echo $cart->item->getType()." is the getType<br>\n";
                //var_dump($parents);
                //echo "<br>\n";
                return;
            }

            $cart->setPricePlan($cart->item->getPricePlan(), 0);
            $planItem = geoPlanItem::getPlanItem('storefront_subscription', $cart->item->getPricePlan(), 0);

            if (!$planItem->getEnabled()) {
                return;
            }
        }

        $return = array (
            'checkbox_name' => '', //no checkbox display
            'title' => 'Subscription',
            'display_help_link' => '',//if 0, will display no help icon thingy
            'price_display' => '',
            //templates - over-write mini-template to do things like set margine or something:
            'entire_box' => '',
            'left' => '',
            'right' => '',
            'checkbox' => '',
            'checkbox_hidden' => ''
        );
        $table = geoAddon::getUtil('storefront')->tables();

        $sql = "SELECT * FROM `$table->categories` WHERE `owner` = ? AND `parent` = 0 ORDER BY `display_order` ASC";
        $cats = $cart->db->GetAll($sql, array($get_storefront_categories_from));
        $getSubs = $cart->db->Prepare("SELECT * FROM `$table->categories` WHERE `owner` = ? AND `parent` = ? ORDER BY `display_order` ASC");
        foreach ($cats as $key => $cat) {
            //add subcategories
            $cats[$key]['subcategories'] = $cart->db->GetAll($getSubs, array($get_storefront_categories_from, $cat['category_id']));
        }


        $msgs = geoAddon::getText('geo_addons', 'storefront');
        $tpl = new geoTemplate('addon', 'storefront');
        $tpl->assign('storefront_messages', $msgs);
        $tpl->assign('cats', $cats);
        $tpl->assign('error', $cart->getErrorMsg('storefront'));
        //set selected
        $item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);

        $selected = (is_object($item) && $item->get('storefront_category')) ? $item->get('storefront_category') : 0;

        if (!$selected && $cart->site->session_variables['storefront_category']) {
            $selected = $cart->site->session_variables['storefront_category'];
        }

        $tpl->assign('selected', $selected);
        $return['entire_box'] = $tpl->fetch('category_selection.tpl');

        if ($cart->main_type == self::type || $cart->main_type == 'listing_edit') {
            //set the title, sub-title, and buttons
            //text on page

            $return ['page_title1'] = $cart->site->messages[482];//assume it is on edit listing
            $return ['page_title2'] = $msgs['category_sub_title'];
            $return ['page_desc'] = $msgs['category_desc'];
            $return ['submit_button_text'] = $msgs['category_submit_button_text'];
            $return ['cancel_text'] = $msgs['category_cancel_text'];
        }

        return $return;
    }

    /**
     * Optional.
     * Used: in geoCart::deleteProcess()
     *
     * The back-end already removes the item, all all children from the cart.  Use this function to do
     * any additional things needed, such as delete uploaded images, or if you expect that any children
     * may need to be called, as they will not be auto called from the system.  Can assume
     * $cart->item is the item that is being deleted, which will be the same type as this is.
     *
     */
    public static function geoCart_deleteProcess()
    {
        $cart = geoCart::getInstance();

        //Do this FIRST: Go through any children, and call geoCart_deleteProcess for them...
        $original_id = $cart->item->getId();//need to keep track of what the ID of the item originally being deleted is.
        $items = $cart->order->getItem();
        foreach ($items as $k => $item) {
            if (is_object($item) && $item->getId() != $cart->item->getId() && is_object($item->getParent()) && $item->getParent()->getId() == $cart->item->getId()) {
                //$item is a child of this item...
                //Set the cart's main item to be $item, so that the deleteProcess gets
                //what it is expecting...
                $cart->initItem($item->getId(), false);
                //now call deleteProcess
                geoOrderItem::callUpdate('geoCart_deleteProcess', null, $item->getType());
            }
        }
        if ($cart->item->getId() != $original_id) {
            //change the item back to what it was originally, if it was changed.
            $cart->initItem($original_id);
        }

        $parent = $cart->item->getParent();
        if (is_object($parent)) {
            $session_vars = $parent->get('session_variables');
            $session_vars['storefront_category'] = 0;
            $parent->set('session_variables', $session_vars);
            $parent->save();
        }
    }


    /**
     * Required.
     *
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        //don't add this step if the user doesn't have a current subscription
        $util = geoAddon::getUtil('storefront');
        $user = geoCart::getInstance()->user_data['id'];
        $expTime = $util->userHasCurrentSubscription($user);
        if ($expTime === 1) {
            //user in a "free storefronts" price plan
            //ensure that the default data has been created before proceeding
            $util->addStoreDataIfNeeded($user);
            return true;
        } elseif ($expTime > 1) {
            //normal storefront is active :: proceed as normal
            return true;
        } else {
            //not currently subscribed to storefront :: do not show category selection
            return false;
        }
    }

    public static function copyListing()
    {
        $cart = geoCart::getInstance();

        if ($cart->site->session_variables['storefront_category']) {
            //Can remove check once this addon is meant for working ONLY in 4.1
            $parents = (is_callable(array('geoOrderItem','getParentTypesFor'))) ? geoOrderItem::getParentTypesFor(self::type) : self::getParentTypes();

            if (!in_array($cart->main_type, $parents)) {
                //do not show thingy for bolding
                return '';
            }
            $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());
            if (geoPC::is_ent() && !$cart->price_plan['use_bolding']) {
                //turned off per price plan
                return '';
            }
            //let other function do rest of work
            self::geoCart_other_detailsCheckVars($cart->site->session_variables);
        }
    }

    public static function getActionName($vars)
    {
        //give it to parent to take care of
        $cart = geoCart::getInstance();
        $parent = $cart->item->getParent();
        if ($parent) {
            return geoOrderItem::callDisplay('getActionName', $vars, '', $parent->getType());
        }
    }
}
