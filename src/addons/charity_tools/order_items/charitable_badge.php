<?php

//addons/charity_tools/order_items/charitable_badge.php
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


require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';

class charitable_badgeOrderItem extends geoOrderItem
{
    var $defaultProcessOrder = 45;
    protected $type = 'charitable_badge';
    const type = 'charitable_badge';

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
        $admin = $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $reg = geoAddon::getRegistry('charity_tools');

        $tpl_vars = array();
        $tpl_vars['enabled'] = $planItem->getEnabled();
        $tpl_vars['price'] = $planItem->get('price', 0.00);
        $tpl_vars['pre'] = $db->get_site_setting('precurrency');
        $tpl_vars['post'] = $db->get_site_setting('postcurrency');

        $tpl = new geoTemplate('addon', 'charity_tools');
        $tpl->assign($tpl_vars);
        return $tpl->fetch('admin/plan_settings.tpl');
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
        $settings = (isset($_GET['charitable_badge'])) ? $_GET['charitable_badge'] : $_POST['charitable_badge'];

        if (is_array($settings)) {
            $enabled = (isset($settings['enabled']) && $settings['enabled']) ? 1 : false;

            if ($enabled) {
                $planItem->set('price', floatval($settings['price']));
            }

            $planItem->setEnabled($enabled);
        }

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
        $title = 'Charity Tools - Charitable Badge';

        return array(
            'type' => ucwords(str_replace('_', ' ', self::type)),
            'title' => $title
        );
    }

    public function displayInAdmin()
    {
        if ($_GET['page'] == 'pricing_category_costs') {
            //not a category specific setting!
            return false;
        }
        return true;
    }

    /**
     * used in admin to show which upgrades are attached to a Listing Renewal item
     *
     * @return String "user-friendly" name of this item
     */
    public function friendlyName()
    {
        return 'Charitable Badge';
    }

    /**
     * Optional.
     * Used: in geoCart::initItem()
     *
     * Used when initiailizing an item, when the item already exists.
     */
    public function geoCart_initItem_restore()
    {
        trigger_error('DEBUG CART: Top of restore item for attention getters.');
        $cart = geoCart::getInstance();
        $parent = $this->getParent();

        $cart->site->session_variables = $parent->get('session_variables'); //get session vars attached to it.
        //make sure if price plan id is set, to use that price plan when getting prices!
        if (isset($cart->site->session_variables['price_plan_id'])) {
            $cart->setPricePlan($cart->site->session_variables['price_plan_id']);
        }
        return true;
    }

    /**
     * Returns data to be displayed on listing cost and features section
     *
     * @return array of data that is processed and used to display the listing cost box
     */
    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();
        if (!($cart->main_type == self::type || in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type)))) {
            //not the right type of item...
            return '';
        }

        if ($cart->item->getType() == self::type) {
            $item = $cart->item->getParent();
        } else {
            $item = $cart->item;
        }
        $planItem = geoPlanItem::getPlanItem('charitable_badge', $item->getPricePlan());
        if (!$planItem || !$planItem->isEnabled()) {
            //plan item is not enabled. nothing to do here
            return '';
        }

        if (isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade == listing_renew_upgradeOrderItem::upgrade) {
            //this is an upgrade, need to see if parent already has item
            //print_r($item);
            if ($item->get('charitable_badge_choice')) {
                //already exists on parent, do not allow adding
                return '';
            }
            //also check DB directly
            $listingId = $item->get('listing_id');
            $sql = "SELECT `purchased_badge` FROM `geodesic_addon_charity_tools_charitable_purchases` WHERE `listing` = ?";
            $badgeId = $cart->db->GetOne($sql, array($listingId));
            if ($badgeId) {
                //this listing already has a charitable badge selected, so cannot upgrade
                return '';
            }
        }

        $tpl = new geoTemplate('addon', 'charity_tools');
        $tpl_vars = array();

        $tpl_vars['error'] = strlen($cart->site->error_variables['charitable_badge']) > 0 ? $cart->site->error_variables['charitable_badge'] : false;

        $tpl_vars['allFree'] = !geoMaster::is('site_fees');
        $tpl_vars['price'] = $tpl_vars['allFree'] ? 0 : $planItem->get('price');
        $tpl_vars['price'] = geoString::displayPrice($tpl_vars['price']);

        $preChoice = $item->get('charitable_badge_choice');
        $tpl_vars['toggle'] = (bool)$preChoice;
        $tpl_vars['choice'] = $preChoice;

        $msgs = geoAddon::getText('geo_addons', 'charity_tools');
        $tpl_vars['toggleLabel'] = $msgs['charitable_badge_label'];

        $iconText = $cart->db->get_text(true, 59);
        $tpl->assign('helpIcon', $iconText[500797]);

        //only want the badges that apply to this region, this zipcode, or have no data for either
        $currentRegion = 0;
        $location = $cart->site->session_variables['location'];
        while (!$currentRegion && $location) {
            $currentRegion = array_pop($location);
        }
        $regionsToCheck = $currentRegion ? geoRegion::getRegionWithParents($currentRegion) : array();
        $regionsToCheck = implode(',', $regionsToCheck);
        $checkRegions = ($regionsToCheck) ? " `region` IN ($regionsToCheck) OR " : "";

        $sql = "SELECT * FROM `geodesic_addon_charity_tools_charitable` WHERE $checkRegions `zipcode` = ? OR (`region` = 0 AND `zipcode` = '')";
        $result = $cart->db->Execute($sql, array($cart->site->session_variables['zip_code']));
        foreach ($result as $badge) {
            $tpl_vars['badges'][$badge['id']] = array(
                'name' => geoString::fromDB($badge['name']),
                'region' => $badge['region'],
                'image' => geoTemplate::getUrl('images', 'addon/charity_tools/' . $badge['image']),
                'show_tooltip' => ($msgs['tooltip_charitable_description_' . $badge['id']]) ? true : false
            );
        }

        if (!count($tpl_vars['badges'])) {
            //no badges active for this region. nothing to show
            return '';
        }
        $tpl->assign($tpl_vars);

        geoView::getInstance()->addCssFile(geoTemplate::getUrl('css', 'addon/charity_tools/listing_placement.css'));

        $return = array (
            'checkbox_name' => '', //manually created checkbox
            'title' => '',
            'help_id' => $tooltip,//manually created
            'price_display' => '',
            //templates - over-write mini-template to do things like set margine or something:
            'entire_box' => $tpl->fetch('charitable_badge_choices.tpl'),
        );

        return $return;
    }

    public static function geoCart_other_detailsCheckVars()
    {
        $cart = geoCart::getInstance();
        if (!($cart->main_type == self::type || in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type)))) {
            //not the right type of item...
            return;
        }

        if (!isset($_POST['c'])) {
            //no data
            return;
        }

        if ($cart->item->getType() == self::type) {
            $item = $cart->item->getParent();
        } else {
            $item = $cart->item;
        }
        $planItem = geoPlanItem::getPlanItem('charitable_badge', $item->getPricePlan());
        if (!$planItem || !$planItem->isEnabled()) {
            //plan item is not enabled. nothing to do here
            return '';
        }

        $toggle = (bool)$_POST['c']['charitable_badge_toggle'];
        $choice = intval($_POST['c']['charitable_badge_choice']);
        if ($toggle && !$choice) {
            $cart->addError();
            $msgs = geoAddon::getText('geo_addons', 'charity_tools');
            $cart->site->error_variables['charitable_badge'] = $msgs['charitable_badge_selection_error'];
            return;
        }

        //get current attached badge, if exists..
        $badge_item = geoOrderItem::getOrderItemFromParent($item, self::type);

        if (!$toggle || !$choice) {
            //nothing selected, or not a valid selection. Destroy the item if it exists
            if ($badge_item) {
                $id = $badge_item->getId();
                geoOrderItem::remove($id);
                $cart->order->detachItem($id);
            }
        } else {
            if (!$badge_item) {
                //item for this doesn't exist yet -- make one!
                $badge_item = new charitable_badgeOrderItem();
                $badge_item->setParent($cart->item);//this is a child of the parent
                $badge_item->setOrder($cart->order);

                $badge_item->save();//make sure it's serialized
                $cart->order->addItem($badge_item);
            } else {
                //item exists -- just make sure it's on the order
                $cart->order->addItem($badge_item);
            }

            //save the price and selection
            $cost = (!geoMaster::is('site_fees')) ? 0 : $planItem->get('price', 0);
            $badge_item->setCost($cost);
            $badge_item->setCreated($cart->order->getCreated());

            $badge_item->set('choice', $choice);
            //$item->set('charitable_badge_choice', false);

            //set id of listing, if known
            if (isset($cart->site->classified_id) && $cart->site->classified_id > 0) {
                $badge_item->set('listing_id', $cart->site->classified_id);
            }

            //let sessvars know we've added something
            $cart->site->session_variables['charitable_badge'] = 1;

            $badge_item->save();
        }



        //just in case this ever has children for some weird reason...
        $children = geoOrderItem::getChildrenTypes('charitable_badge');
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);
    }

    public static function geoCart_other_detailsProcess()
    {
        $cart = geoCart::getInstance();
        if (!($cart->main_type == self::type || in_array($cart->main_type, geoOrderItem::getParentTypesFor(self::type)))) {
            //not the right type of item...
            return;
        }

        //everything is saved in checkvars. nothing to process at this point (will do that when the item goes live)

        //for now, just make sure any mythical children don't get left out
        $children = geoOrderItem::getChildrenTypes('charitable_badge');
        if (count($children)) {
            //don't actually do extra steps unless there are child thingies potentially
            if ($cart->item->getType() == self::type) {
                $item = $cart->item->getParent();
            } else {
                $item = $cart->item;
            }
            $planItem = geoPlanItem::getPlanItem('charitable_badge', $item->getPricePlan());
            if (!$planItem || !$planItem->isEnabled()) {
                //plan item is not enabled. nothing to do here
                return;
            }
            geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
        }
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $text =& geoAddon::getText('geo_addons', 'charity_tools');
        $title = $text['charitable_badge_cart_title'];
        $price = $this->getCost(); //people expect numbers to be positive...

        $choice = $this->get('choice');
        $db = DataAccess::getInstance();
        $badge = $db->GetRow("SELECT `image`,`name` FROM `geodesic_addon_charity_tools_charitable` WHERE `id` = ?", array($choice));
        $image = $badge['image'];
        $name = geoString::fromDB($badge['name']);
        $image = geoTemplate::getUrl('images', 'addon/charity_tools/' . $image);

        $return = array (
            'css_class' => '',
            'title' => $title . ' - ' . $name . ' <img src="' . $image . '" alt="" />',
            'canEdit' => true, //whether can edit it or not
            'canDelete' => true, //whether can remove from cart or not
            'canPreview' => false, //whether can preview the item or not
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price,
            'children' => false
        );

        //display the attention getter choice
        $p = $this->getParent();
        if (!is_object($p)) {
            //parent went away?  thats not good...
            $id = $this->getId();
            geoOrderItem::remove($id);
            $this->getOrder()->detachItem($id);
            return false;
        }

        //go through children...
        $order = $this->getOrder();
        $items = $order->getItem();
        $children = array();
        foreach ($items as $i => $item) {
            if (is_object($item) && is_object($item->getParent()) && $item->getType() !== self::type) {
                $p = $item->getParent();
                if ($p->getId() == $this->getId()) {
                    //This is a child of mine...
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
            $return['children'] = $children;
        }

        return $return;
    }

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

        $db = DataAccess::getInstance();

        $listing = $this->get('listing_id');
        if (!$listing) {
            //this item somehow doesn't know the listing id...does the parent?
            $parent = $this->getParent();
            $listing = $parent->get('listing_id');
        }

        if ($activate) {
            //do activate actions here, such as setting listing to live
            $choice = $this->get('choice');
            $price = $this->getCost();
            $sql = "INSERT INTO `geodesic_addon_charity_tools_charitable_purchases` (`listing`, `time`, `purchased_badge`, `price`) VALUES (?,?,?,?)";
            $result = $db->Execute($sql, array($listing, geoUtil::time(), $choice, $price));
        } elseif (!$activate && $already_active) {
            //making inactive
            $sql = "DELETE FROM `geodesic_addon_charity_tools_charitable_purchases` WHERE `listing` = ?";
            $result = $db->Execute($sql, array($listing));
        }
    }

    public static function geoCart_initSteps_addOtherDetails()
    {
        $cart = geoCart::getInstance();
        if (!$cart->item) {
            //this is most likely the admin "combine steps" tool checking to see if the other_details step is active
            //without an item, can't check the price plan setting, so just assume it's on
            return true;
        }
        $planItem = geoPlanItem::getPlanItem('charitable_badge', $cart->item->getPricePlan(), 0);
        return (bool)$planItem->isEnabled();
    }


    //******** below here is mostly boilerplate stuff that shouldn't need to be modified much

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
    public static function getParentTypes()
    {
        return array(
            'classified',
            'classified_recurring',
            'auction',
            'listing_renew_upgrade',
            'dutch_auction',
            'job_posting',
            'reverse_auctions',
        );
    }
    public function getRecurringSubCost()
    {
        $cart = geoCart::getInstance();
        if ($cart->item->getType() == self::type) {
            $item = $cart->item->getParent();
        } else {
            $item = $cart->item;
        }
        $planItem = geoPlanItem::getPlanItem('charitable_badge', $item->getPricePlan());
        $price = $planItem->get('price', 0);
        return (!geoMaster::is('site_fees')) ? 0 : $price;
    }
    public static function geoCart_initSteps($allPossible = false)
    {
    }
    public static function geoCart_initItem_forceOutsideCart()
    {
        return false;
    }
    public static function geoCart_deleteProcess()
    {
    }
}
