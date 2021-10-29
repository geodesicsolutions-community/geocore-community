<?php

//order_items/twitter_feed.php
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
## ##    16.05.0-17-g5113ae1
##
##################################

// used by the Twitter Feed addon to allow a user to specify a Twitter username to grab the feed for in a listing

class twitter_feedOrderItem extends geoOrderItem
{
    protected $type = "twitter_feed";
    const type = 'twitter_feed';
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
        return array('classified','classified_recurring','auction','renew_upgrade','listing_edit','listing_change_admin',);
    }

    public static function listing_edit_getChoices()
    {
        $msgs = geoAddon::getText('geo_addons', 'twitter_feed');
        return array ('twitter_feed:editTwitterName' => $msgs['edit_step_button_widget']);
    }

    public static function editTwitterNameCheckVars()
    {
        $cart = geoCart::getInstance();
        self::geoCart_other_detailsCheckVars($cart->site->session_variables);
    }

    public static function editTwitterNameProcess()
    {
        self::geoCart_other_detailsProcess();
    }

    public static function editTwitterNameDisplay()
    {
        $cart = geoCart::getInstance();
        listing_editOrderItem::fixStepLabels();
        $cart->displaySingleOtherDetails(self::type);
    }

    public static function editTwitterNameLabel()
    {
        $msgs = geoAddon::getText('geo_addons', 'twitter_feed');
        return $msgs['edit_step_label_widget'];
    }

    /**
     * Required.
     *
     * @return array An associative array as described above.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $msgs = geoAddon::getText('geo_addons', 'twitter_feed');
        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => $msgs['cart_title'],//text that is displayed for this item in list of items purchased.
            'canEdit' => true, //show edit button for item?
            'priceDisplay' => '&nbsp;', //Price as it is displayed
            'cost' => 0, //amount this adds to the total, what getCost returns
            'total' => 0, //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()
        );
        $return['title'] .= " - {$this->get('twitter_name')}";
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
        if ($newStatus == $this->getStatus()) {
            //nothing changed
            return;
        }
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
        $parent = $this->getParent();

        $listing_id = $this->get('listing_id', $parent->get('listing_id', 0));
        trigger_error('DEBUG TWITTER: listing ID is: ' . $listing_id);
        $href = $this->get('twitter_href', '');
        trigger_error('DEBUG TWITTER: href is: ' . $href);
        $data_id = $this->get('twitter_data_id', '');
        trigger_error('DEBUG TWITTER: data_id is: ' . $data_id);
        if (!$listing_id) {
            //listing id not set, try to find it in the parent item
            trigger_error('ERROR TWITTER: Failed while trying to processStatusChange without a listing id');
            return false;
        }


        if ($newStatus == 'active') {
            $status = 1;
        } else {
            $status = 0;
        }
        $db = DataAccess::getInstance();
        if ($href) { //NOTE: this now works without data_id
            $sql = "REPLACE INTO `geodesic_addon_twitter_feed_timelines` (listing_id, href, data_id, active) VALUES (?,?,?,?)";
            $result = $db->Execute($sql, array($listing_id, $href, $data_id, $status));
        } else {
            //no name given -- user is trying to erase this record (this should already be done, but no harm in checking for sanity here)
            $sql = "DELETE FROM `geodesic_addon_twitter_feed_timelines` WHERE `listing_id` = ?";
            $result = $db->Execute($sql, array($listing_id));
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

        $addon_text = geoAddon::getText('geo_addons', 'twitter_feed');

        $href = '';
        if (isset($_POST['c']['twitter_feed_href']) || isset($c_data['twitter_feed_href'])) {
            $href = $_POST['c']['twitter_feed_href'];
            if (!$href && isset($c_data['twitter_feed_href'])) {
                trigger_error('DEBUG TWITTER: getting href from c_data');
                $href = $c_data['twitter_feed_href'];
            }
        }

        if ($href && strpos(geoString::fromDB($href), "https://twitter.com/") !== 0) {
            //not an expected value
            trigger_error('ERROR TWITTER: bad href input');
            $cart->addError()->addErrorMsg('twitter_feed', $addon_text['parse_error']);
            return false;
        }

        $data_id = '';
        if (isset($_POST['c']['twitter_feed_data_id']) || isset($c_data['twitter_feed_data_id'])) {
            $data_id = $_POST['c']['twitter_feed_data_id'];
            if (!$data_id && isset($c_data['twitter_feed_data_id'])) {
                trigger_error('DEBUG TWITTER: getting data_id from c_data');
                $data_id = $c_data['twitter_feed_data_id'];
            }
        }
        if ($data_id && !is_numeric($data_id)) {
            //not what we're looking for
            trigger_error('ERROR TWITTER: bad data id input');
            $cart->addError()->addErrorMsg('twitter_feed', $addon_text['parse_error']);
            return false;
        }

        //get current attached order item, if exists..
        $order_item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);

        if (!$href) {
            //no data given. Remove any existing data for this order item

            if ($cart->item->get('listing_id')) {
                //if listing id is known at this point, this is an edit
                //go ahead and pull it from the db here (processStatusChange will re-add it if they later decide to put it back during this edit)
                $sql = "DELETE FROM `geodesic_addon_twitter_feed_timelines` WHERE `listing_id` = ?";
                $result = $cart->db->Execute($sql, array($cart->item->get('listing_id')));
            }
            if ($order_item) {
                //timeline already exists for this item, so remove it
                $order_item->set('twitter_href', false);
                $order_item->set('twitter_data_id', false);
                $order_item->save();
                //remove this item from the order
                $id = $order_item->getId();
                geoOrderItem::remove($id);
                $cart->order->detachItem($id);
            }
        } else {
            //data is valid, so save it to the item
            if (!$order_item) {
                $order_item = new twitter_feedOrderItem();
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
            $order_item->set('twitter_name', false); //clear out any remnants of the "old" way of doing Twitter Feed
            $order_item->set('twitter_href', $href);
            $order_item->set('twitter_data_id', $data_id);
            $order_item->save();
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
        $db = DataAccess::getInstance();

        //Can remove check once this addon is meant for working ONLY in 4.1
        $parents = (is_callable(array('geoOrderItem','getParentTypesFor'))) ? geoOrderItem::getParentTypesFor(self::type) : self::getParentTypes();

        if ($cart->main_type != self::type && !in_array($cart->item->getType(), $parents)) {
            //not something we're interested in.
            return;
        }

        $return = array (
            'checkbox_name' => '', //no checkbox display
            'title' => 'Twitter Feed',
            'display_help_link' => '',//if 0, will display no help icon thingy
            'price_display' => '',
            //templates - over-write mini-template to do things like set margine or something:
            'entire_box' => '',
            'left' => '',
            'right' => '',
            'checkbox' => '',
            'checkbox_hidden' => ''
        );

        $msgs = geoAddon::getText('geo_addons', 'twitter_feed');
        $tpl = new geoTemplate('addon', 'twitter_feed');
        $tpl->assign('addon_text', $msgs);
        $tpl->assign('error', $cart->getErrorMsg('twitter_feed'));

        $iconText = $db->get_text(true, 59);
        $tpl->assign('helpIcon', $iconText[500797]);

        //set selected
        $order_item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);

        $href = (is_object($order_item) && $order_item->get('twitter_href')) ? $order_item->get('twitter_href') : '';
        $data_id = (is_object($order_item) && $order_item->get('twitter_data_id')) ? $order_item->get('twitter_data_id') : '';
        if (!$value) {
            //this could be an edit of an existing widget. check the DB for the value
            $listingId = $cart->item->get('listing_id');
            if ($listingId) {
                $sql = "SELECT `href`, `data_id` FROM `geodesic_addon_twitter_feed_timelines` WHERE `listing_id` = ?";
                $result = $db->GetRow($sql, array($listingId));
                if ($result) {
                    $href = $result['href'];
                    $data_id = $result['data_id'];
                }
            }
        }
        $tpl->assign('prevalue_href', $href);
        $tpl->assign('prevalue_data_id', $data_id);

        $return['entire_box'] = $tpl->fetch('widget_code_input.tpl');

        if ($cart->main_type == self::type || $cart->main_type == 'listing_edit') {
            //set the title, sub-title, and buttons
            //text on page

            $return ['page_title1'] = $cart->site->messages[482];//assume it is on edit listing
            $return ['page_title2'] = $msgs['edit_sub_title'];
            $return ['page_desc'] = $msgs['edit_desc_widget'];
            $return ['submit_button_text'] = $msgs['edit_submit_button_text'];
            $return ['cancel_text'] = $msgs['edit_cancel_text'];
        }

        geoView::getInstance()->addJScript(geoTemplate::getUrl('js', 'addon/twitter_feed/widget_code_input.js'))
        ->addCssFile(geoTemplate::getUrl('css', 'addon/twitter_feed/twitter_feed.css'));

        return $return;
    }

    public static function copyListing($parentItem)
    {
        //get old listing id
        $sv = $parentItem->get('session_variables');
        $listingId = $sv['classified_id'];
        $db = DataAccess::getInstance();
        //get name from the old listing
        $sql = "SELECT `href`, `data_id` FROM `geodesic_addon_twitter_feed_timelines` WHERE `listing_id` = ?";
        $result = $db->GetRow($sql, array($listingId));
        if ($result) {
            $href = $result['href'];
            $data_id = $result['data_id'];
        }
        //make otherDetails function do all the heavy lifting
        self::geoCart_other_detailsCheckVars(array('twitter_feed_href' => $href, 'twitter_feed_data_id' => $data_id));
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

        $order_item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);
        if ($order_item) {
            $order_item->set('twitter_name', false); //old pre-2.0 value. no reason not to clear it if it's still here
            $order_item->set('twitter_href', false);
            $order_item->set('twitter_data_id', false);
            $order_item->save();
        }
    }


    /**
     * Required.
     *
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        //always want to add this if addon is enabled
        return true;
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
