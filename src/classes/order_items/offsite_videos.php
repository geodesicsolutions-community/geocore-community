<?php

//Order item that adds ability to have youtube videos

class offsite_videosOrderItem extends geoOrderItem
{
    protected $type = "offsite_videos";
    const type = 'offsite_videos';
    const renewal = 1; //easier way to access what is renew/upgrade
    const upgrade = 2;

    protected $defaultProcessOrder = 31;
    const defaultProcessOrder = 31;


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

        $tpl->assign('minVideos', $planItem->get('minVideos', 0));
        $tpl->assign('maxVideos', $planItem->get('maxVideos', 0));
        $tpl->assign('precurrency', $db->get_site_setting('precurrency'));
        $tpl->assign('postcurrency', $db->get_site_setting('postcurrency'));

        $cost = $planItem->get('costPerVideo', 0);
        if (!$cost) {
            $cost = '0.00';
        }
        $tpl->assign('costPerVideo', $cost);
        $tpl->assign('is_ent', geoPC::is_ent());
        if (geoPC::is_ent()) {
            $tpl->assign('freeVideos', $planItem->get('freeVideos', 0));
        }

        return $tpl->fetch('order_items/offsite_videos/item_settings.tpl');
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
        $settings = $_POST['offsite_videos'];

        $min = (int)$settings['minVideos'];
        $max = (int)$settings['maxVideos'];
        $cost = geoNumber::deformat($settings['costPerVideo']);
        if (geoPC::is_ent()) {
            $free = (int)$settings['freeVideos'];
        }

        if ($max <= 0) {
            //make sure they don't try to do something silly like set to negative.
            //Also make it easy to turn it off by setting max to 0 and not change min
            $max = $min = 0;
        }

        if ($min > $max) {
            geoAdmin::m('The max allowed videos can not be less than the minimum number of required videos!', geoAdmin::ERROR);
            return false;
        }

        if (geoPC::is_ent()) {
            if ($free <= 0) {
                $free = 0;
            }
            if ($free > $max) {
                geoAdmin::m('You cannot have more free videos (' . $free . ') than the max number (' . $max . ')!', geoAdmin::ERROR);
                return false;
            }

            $planItem->freeVideos = $free;
        }

        $planItem->maxVideos = $max;
        $planItem->minVideos = $min;
        $planItem->costPerVideo = $cost;

        return true;
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
        if (!$item_id) {
            return '';
        }
        $parent = geoOrderItem::getOrderItem($item_id);
        if (!is_object($parent)) {
            return '';
        }
        $item = geoOrderItem::getOrderItemFromParent($parent, self::type);
        if (!is_object($item)) {
            //no videos attached
            return '';
        }

        $videos = $item->get('video_slots');

        if (!$videos) {
            return '';
        }

        $tpl_vars = array();

        $tpl_vars['videos'] = $videos;
        $tpl_vars['current_color'] = geoHTML::adminGetRowColor();

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign($tpl_vars);
        $html .= $tpl->fetch('order_items/offsite_videos/item_details.tpl');

        //Call children and let them display info about themselves as well
        $children = geoOrderItem::getChildrenTypes(self::type);
        $html .= geoOrderItem::callDisplay('adminItemDisplay', $item_id, '', $children);

        return $html;
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
        return 'Off-Site (Youtube) Videos';
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
        return true;
    }

    /**
     * Required.
     * Used: in geoCart::initSteps() (and possibly other locations)
     *
     * This adds a step if detects that the images item is being edited using
     * edit link in cart
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        $cart = geoCart::getInstance(); //get instance of cart

        if ($cart->main_type != self::type) {
            //don't add any steps, since we will be called by parent
            return;
        }

        if (self::addMedia()) {
            //Only add step if videos are allowed
            trigger_error('DEBUG CART: adding video step in offsite_videos.php.');
            $cart->addStep('offsite_videos:media');
        }
    }

    /**
     * Required.
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }

    /**
     * Required.
     */
    public static function getParentTypes()
    {
        return array (
            'classified',
            'classified_recurring',
            'auction',
            'listing_renew_upgrade',
            'listing_edit',
            'listing_change_admin',
        );
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
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $price = $this->getCost();
        //Figure out how many photos, how many are being charged, etc.
        $renew_upgrade = (($this->getParent()) ? $this->getParent()->get('renew_upgrade') : false);

        //can edit if not renewing/upgrading and not editing listing
        $can_edit = !($renew_upgrade > 0 || ($this->getParent() && $this->getParent()->getType() == 'listing_edit'));
        //if can't edit, don't allow to delete either, it could mess things up
        $can_delete = $can_edit;
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, 10202);

        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => $msgs[500928],//text that is displayed for this item in list of items purchased.
            'canEdit' => $can_edit, //show edit button for item, if displaying in cart?
            'canDelete' => $can_delete, //show delete button for item, if displaying in cart?
            'canPreview' => false, //show preview button for item, if displaying in cart?
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //Price as it is displayed
            'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
            'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        $total = count($this->get('video_slots'));
        $total_paid = $this->get('number_cost_videos', 0);
        //subtract pre-existing images from total number of free images displayed, to make it less confusing
        $free = intval($this->get('number_free_videos'));
        if ($total < 0) {
            $total = 0;
        }
        if ($total_paid < 0) {
            $total_paid = 0;
        }

        $free = ($free > 0) ? $free . $msgs[500929] : '';

        $display_per_video_cost = ($total_paid > 0) ? ($this->getCost() / $total_paid) : 0; //take care not to divide by 0
        $display_per_video_cost = geoString::displayPrice($display_per_video_cost);
        $ts = ($total > 1) ? 's' : '';
        if (geoMaster::is('site_fees')) {
            $title = " ($free {$total_paid} X $display_per_video_cost )";
            $return['title'] .= $title;
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
     * Optional.  Required if in getDisplayDetails() you returned true for the array index of canPreview.
     * Used: in geoCart::previewDisplay()
     *
     * Display a preview of the item.
     */
    public function geoCart_previewDisplay()
    {
        $cart = geoCart::getInstance();
        //we let the site class know about the offsite videos saved in the order item
        $cart->site->offsite_videos = $this->get('video_slots');
        $cart->site->offsite_videos_from_db = false;
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

        //DO Any custom stuff needed here.
        $parent = $cart->item->getParent();
        if (is_object($parent)) {
            //note that this would not be called from listing edit or renewal
            $session_vars = $parent->get('session_variables');
            $session_vars['offsite_videos_purchased'] = 0;
            $parent->set('session_variables', $session_vars);
            $parent->save();
            $cart->site->session_variables['offsite_videos_purchased'] = 0;
        }
        //main part will remove the order item and item settings for us
    }


    /**
     * Required.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        return false;
    }

    public static function geoCart_other_detailsCheckVars()
    {
        $cart = geoCart::getInstance();
        if (!(isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade > 0)) {
            //this is not a renewal or upgrade, and we only display on other details for renew/upgrade
            return;
        }
        if (!self::addMedia() || !geoMaster::is('site_fees')) {
            return;
        }

        //get plan item
        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        if ($planItem->get('costPerVideo', 0) == 0 || ($cart->item->renew_upgrade == self::upgrade && $cart->site->parent_session_variables['offsite_videos_purchased'] >= $planItem->maxVideos)) {
            //either we do not charge for videos, or this is an upgrade and the user already has the max number of images they can.
            trigger_error('DEBUG CART: Here in videos.');
            return ;
        }
        if (geoPC::is_ent() && $planItem->freeVideos >= $planItem->maxVideos) {
            //number of free videos is same as number of max videos allowed
            return;
        }
        trigger_error('DEBUG CART: Here in vids.');
        $renew_upgrade = $cart->item->renew_upgrade;
        $numVideos = intval($_POST['c']['new_offsite_videos']);

        if ($numVideos > $planItem->maxVideos) {
            //do not allow more than the max allowed photos, to prevent invalid user input
            $numVideos = intval($planItem->maxVideos);
        }

        $free = ((geoPC::is_ent()) ? $planItem->freeVideos : 0);

        if ($renew_upgrade == self::upgrade) {
            //only count number of videos added beyond whats already been purchased
            //so, add the number that is already added that has been purchased to the "free" count
            $numOldVideos = (int)$cart->site->parent_session_variables['offsite_videos_purchased'];
            if ($free >= $numOldVideos) {
                $free = $free;
            } else {
                $free = $numOldVideos;
            }
        } else {
            //renewal, figure out minimum image count
            if ($cart->item->get('listing_copy_id')) {
                //this is a copy of auction being renewed, images won't have
                //ID's set for listing yet, get count different way

                //if there are videos, there will be a video item
                $videoItem = geoOrderItem::getOrderItemFromParent($cart->item, self::type);
                if ($videoItem) {
                    $count = count($videoItem->get('video_slots', array()));
                } else {
                    $count = 0;
                }
            } else {
                //get image count regular way
                $sql = "SELECT count(*) FROM " . geoTables::offsite_videos . " WHERE `listing_id` = " . intval($cart->item->get('listing_id'));
                $count = (int)$cart->db->GetOne($sql);
            }

            $force_min = $count;
            if ($numVideos < $force_min) {
                $numVideos = $force_min;
            }
        }
        $purchased = (($numVideos - $free > 0) ? ($numVideos - $free) : 0);
        $amountPaid = $purchased * $planItem->costPerVideo;

        $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());

        $order_item = $cart->getChildItem(self::type);

        if (!$purchased) {
            //no new images purchased

            //find out if this is a copy
            $parent = $cart->item->getParent();
            if (!$parent) {
                $parent = $cart->item;
            }
            if ($parent) {
                $isCopy = $parent->get('listing_copy_id');
            }

            //mark item for removal unless this is a copy with videos
            $removeItem = ($isCopy && $numVideos) ? false : true;
        } else {
            //new videos have been purchased -- don't remove the item
            $removeItem = false;
        }

        if ($removeItem) {
            if ($order_item) {
                $id = $order_item->getId();
                geoOrderItem::remove($id);
                $cart->order->detachItem($id);
            }
        } else {
            if (!$order_item) {
                $order_item = self::addNewItem();
            } else {
                trigger_error('DEBUG CART: videos already attached: <pre>' . print_r($order_item, 1) . '</pre>');
                $cart->order->addItem($order_item);
            }
            $order_item->setCreated($cart->order->getCreated());
            $order_item->setCost($amountPaid);

            //set details specific to videos
            $order_item->set('number_free_videos', $free);
            $order_item->set('number_cost_videos', $purchased);

            //set id of listing, if known
            if (isset($cart->site->classified_id) && $cart->site->classified_id > 0) {
                $order_item->set('listing_id', $cart->site->classified_id);
            }

            $order_item->set('renew_upgrade', $renew_upgrade);
            if ($renew_upgrade == self::renewal && $force_min > 0) {
                $order_item->set('force_no_remove', 1);
            }
            $order_item->save();

            $session_variables = $cart->item->get('session_variables');
            $session_variables['offsite_videos_purchased'] = $numVideos;
            $cart->item->set('session_variables', $session_variables);

            $cart->item->save();
        }
    }
    public static function geoCart_other_detailsProcess()
    {
        //everything done in check vars...
    }
    public static function geoCart_other_detailsDisplay()
    {
        $cart = geoCart::getInstance();
        trigger_error('DEBUG CART: Here in videos.');
        if (!(isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade > 0)) {
            //this is not a renewal or upgrade, and we only display on other details for renew/upgrade
            return '';
        }
        if (!self::addMedia() || !geoMaster::is('site_fees')) {
            return '';
        }

        //get plan item
        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        if ($planItem->get('costPerVideo', 0) == 0 || ($cart->item->renew_upgrade == self::upgrade && $cart->site->parent_session_variables['offsite_videos_purchased'] >= $planItem->maxVideos)) {
            //either we do not charge for videos, or this is an upgrade and the user already has the max number of images they can.
            trigger_error('DEBUG CART: Here in videos.');
            return '';
        }
        if (geoPC::is_ent() && $planItem->freeVideos >= $planItem->maxVideos) {
            //number of free videos is same as number of max videos allowed
            return;
        }

        $renew_upgrade = $cart->item->renew_upgrade; //easier way to access var

        //check current videos attached to this listing versus what is already purchased
        //the $this->classified_data->IMAGE variable contains the count of images paid for
        //the current listing.  The renewal costs will be based off what is actually
        //attached to the listing currently.  Do not need to do this for upgrade.

        $tpl_vars = $cart->getCommonTemplateVars();
        $tpl_vars['current'] = $tpl_vars['maxToBuy'] = 0;
        //number of free pics
        $tpl_vars['free'] = intval((geoPC::is_ent()) ? $planItem->freeVideos : 0);

        if ($renew_upgrade == self::renewal) {
            //count the actual number of videos for this listing, not the number or previously purchased videos
            if ($cart->item->get('listing_copy_id')) {
                //this is a copy of auction being renewed, images won't have
                //ID's set for listing yet, get count different way

                //if there are images, there will be an image item
                $videoItem = geoOrderItem::getOrderItemFromParent($cart->item, self::type);
                if ($videoItem) {
                    $count = count($videoItem->get('video_slots', array()));
                } else {
                    $count = 0;
                }
            } else {
                //get image count regular way
                $sql = "SELECT count(*) FROM " . geoTables::offsite_videos . " WHERE `listing_id` = " . intval($cart->item->get('listing_id'));
                $count = (int)$cart->db->GetOne($sql);
            }
            $tpl_vars['current'] = $count;
            $tpl_vars['maxToBuy'] = $maxToBuy = $planItem->maxVideos;
            $tpl_vars['start'] = $start = $tpl_vars['free'];
            trigger_error("DEBUG CART: current: {$tpl_vars['current']} start: $start");
        } else {
            //upgrade, the current is the number already recorded.
            if (isset($cart->site->parent_session_variables['offsite_videos_purchased'])) {
                $count = (int)$cart->site->parent_session_variables['offsite_videos_purchased'];
            } else {
                //Just in case listing session vars doesn't have number of videos purchased, set it by
                //the number we count in the DB for the listing.
                //get image count regular way
                $sql = "SELECT count(*) FROM " . geoTables::offsite_videos . " WHERE `listing_id` = " . intval($cart->item->get('listing_id'));
                $count = (int)$cart->db->GetOne($sql);
            }
            //force count to be as big or bigger than number of free.
            if ($count < $tpl_vars['free']) {
                $count = $tpl_vars['free'];
            }

            $tpl_vars['current'] = $current = $count;
            $maxToBuy = ($planItem->maxVideos - $current);
            $tpl_vars['maxToBuy'] = $maxToBuy = (($maxToBuy > 0) ? $maxToBuy : 0);
            $tpl_vars['start'] = $start = ($tpl_vars['free'] > $current) ? $tpl_vars['free'] : $current;
        }

        $cart->site->page_id = 56;
        $cart->site->get_text();

        $tpl = new geoTemplate('system', 'order_items');
        $tpl->assign($tpl_vars);
        $vid_dropdown = array();

        for ($i = intval($start); $i <= ($start + $maxToBuy); $i++) {
            //build array to use in smarty template for image drop down
            if (($renew_upgrade == self::renewal && $i >= $tpl_vars['current']) || $renew_upgrade == self::upgrade) {
                $price = 0;
                if (($renew_upgrade == self::upgrade && ($tpl_vars['current'] + $i) > $tpl_vars['free']) || ($renew_upgrade == self::renewal && $i > $tpl_vars['free'])) {
                    $mult = ($i - $start);
                    $price = ($planItem->costPerVideo * $mult);
                }
                $vid_dropdown[$i] = geoString::displayPrice($price, false, false, 'cart');
            }
        }
        $tpl->assign('vid_dropdown', $vid_dropdown);

        $tpl->assign('help_link', $cart->site->display_help_link(500943));
        $tpl->assign('renew_upgrade', $renew_upgrade); //not used in default smarty template, but handy to know for custimization to template
        return array ('entire_box' => $tpl->fetch('offsite_videos/other_details.item_box.tpl'));
    }

    /**
     * Optional.
     * Used: In listing order items such as classifiedOrderItem or auctionOrderItem
     *
     * NOT part of built-in cart system.
     *
     * Special case, functionality built into individual order items.
     *
     * This can be used to copy or re-create anything needed to duplicate
     * the original listing.  See other order items that are children to get
     * some examples of how this can be utilized.  The one that does the most
     * stuff is the images order item.
     */
    public static function copyListing()
    {
        $cart = geoCart::getInstance();

        if ($cart->site->session_variables['offsite_videos_purchased']) {
            //before this is called, the $cart->site->session_variables are populated with the
            //session vars as they were on the original listing.  This is a good way to see if
            //the item was attached to the original order item..

            //do stuff to copy things from the old listing to the new one here.

            $listing_id = $cart->site->session_variables['listing_copy_id'];

            if (!$listing_id) {
                //can't do a thing without old listing ID
                return;
            }
            $allVids = $cart->db->GetAll("SELECT * FROM " . geoTables::offsite_videos . " WHERE `listing_id`=$listing_id ORDER BY `slot`");
            $videos = array();
            if ($allVids) {
                foreach ($allVids as $row) {
                    $videos[$row['slot']] = array (
                        'video_id' => $row['video_id'],
                        'media_content_url' => $row['media_content_url'],
                        'media_content_type' => $row['media_content_type'],
                        'video_type' => 'youtube',
                    );
                }
            }

            if ($videos) {
                //create a new order item
                $order_item = self::addNewItem();
                if (!$order_item) {
                    //just sanity check
                    return;
                }
                $order_item->set('video_slots', $videos);
                $order_item->set('copy_listing_id', $listing_id);
                $order_item->save();
            }
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

        if ($activate) {
            //do activate actions here
            $db = DataAccess::getInstance();

            $listing_id = (int)$this->getParent()->get('listing_id');

            if (!$listing_id) {
                //can't do anything without listing ID
                trigger_error('DEBUG VIDEO CART: Listing ID not known, nothing that can be done.');
                return;
            }

            //make sure listing ID is saved on ourselves
            $this->set('listing_id', $listing_id);
            $this->save();

            $slots = $this->get('video_slots');
            if (!$slots && $this->get('renew_upgrade') > 0 && !$this->get('listing_copy_id')) {
                //there are no slots found, it is a renewal or upgrade and it is not a copy of
                //another listing.  So don't apply slots or all of the slots will be
                //removed.
                trigger_error('DEBUG VIDEO CART: No video slots and this is renew/upgrade, nothing to do.');
                return;
            }
            self::_updateVideos($slots, $listing_id);
        } elseif (!$activate && $already_active) {
            //do de-activate actions here, such as setting listing to not be live any more.
            //This is what would happen if an admin changes their mind
            //and later decides to change an item from being active to being pending.
            if ($this->getParent()->getType() == 'listing_edit') {
                //restore the old videos, by getting most recent active video order item

                //Get all the order items using this listing.
                $listing_id = (int)$this->get('listing_id');
                if ($listing_id) {
                    //If you change this query or logic, TEST ON INSTALL WITH LARGE NUMBERS OF LISTINGS for speed!

                    $sql = "SELECT `item`.`id` from " . geoTables::order_item . " as item, " . geoTables::order_item_registry . " as regi
								WHERE regi.index_key='listing_id' AND regi.val_string='$listing_id' AND item.id = regi.order_item
								AND item.id != {$this->getId()} AND item.status='active'
								AND item.type='offsite_videos' ORDER BY item.created DESC";

                    $items = DataAccess::getInstance()->GetAll($sql);
                    $item = null;
                    foreach ($items as $row) {
                        $itemCheck = geoOrderItem::getOrderItem($row['id']);
                        if ($itemCheck && ($itemCheck->getParent()->getType() != 'listing_renew_upgrade' || $itemCheck->get('listing_copy_id') > 0)) {
                            $item = $itemCheck;
                            break;
                        }
                    }

                    if ($item) {
                        //insert the junk for the most recent active offsite video item.
                        $slots = $item->get('video_slots');
                        self::_updateVideos($slots, $listing_id);
                    }
                }
            }
        }
        //NOTE: do not need to call children, parent does that for us :)
    }

    /**
     * Optional.
     * Used: from geoOrderItem::remove() when removing an order item.
     *
     * Usint this to remove videos
     *
     * @return bool True to proceed with removing the item, false to stop the removal of the item.
     */
    public function processRemove()
    {
        if ($this->getParent() && in_array($this->getParent()->getType(), array('listing_edit', 'listing_renew_upgrade'))) {
            //don't auto-remove slots for edits or renewal/upgrades

            if ($this->getStatus() == 'active') {
                //re-using code is fun :)  This will make it set videos to what they
                //were before hand.
                $this->processStatusChange('pending', false, false);
            }

            return true;
        }

        $listing_id = $this->get('listing_id');

        if (!$listing_id) {
            //nothing to remove
            return true;
        }

        //remove all videos for this item
        self::_deleteVideos($listing_id);

        return true;
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
        //give it to parent to take care of
        $cart = geoCart::getInstance();
        $parent = $cart->item->getParent();
        return geoOrderItem::callDisplay('getActionName', $vars, '', $parent->getType());
    }

    public static function addMedia()
    {
        //figure out if should add media or not
        $cart = geoCart::getInstance();

        //get plan item
        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        return ($planItem->maxVideos > 0);
    }

    public static function mediaCheckVars()
    {
        //Because we want to always update the order item, we will be doing both
        //the input checking, and the processing from mediaProcess...
    }

    public static function mediaProcess()
    {
        if (!self::addMedia()) {
            //no youtube videos on media page
            return;
        }
        $cart = geoCart::getInstance();

        $youtubeSlotsRaw = $_POST['offsite_video_slots'];

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        $min = (int)$planItem->minVideos;
        $max = (int)self::getMaxVideos();

        $order_item = $cart->getChildItem(self::type);

        $slotUrls = array();
        for ($i = 1; $i <= $max; $i++) {
            if (isset($youtubeSlotsRaw[$i]) && strlen(trim($youtubeSlotsRaw[$i])) > 0) {
                $slotUrls[$i] = trim($youtubeSlotsRaw[$i]);
            }
        }

        if (count($slotUrls) < $min && !geoAjax::isAjax()) {
            //hey!  whats the deal here, minimum is not met!
            $msgs = $cart->db->get_text(true, 10);
            $cart->addError()
                ->addErrorMsg('offsite_videos', $msgs[500930]);
            //But, don't stop from doing rest of processing...
        }

        //whether or not to remove the item
        $removeItem = (count($slotUrls) <= 0 && $cart->item->getType() != 'listing_edit');

        if ($removeItem && $order_item) {
            //there is an item when there shoudl not be.
            $id = $order_item->getId();
            geoOrderItem::remove($id);
            $cart->order->detachItem($id);
            $order_item = null;
        } elseif (!$removeItem && !$order_item) {
            //there is not an item yet but we need one
            $order_item = self::addNewItem();
        }

        if (!$order_item) {
            //nothing else to do
            return;
        }

        $order_item->setCreated($cart->order->getCreated());
        //NOTE:  cost is set later on as long as there are no errors.

        //set id of listing, if known
        if (isset($cart->site->classified_id) && $cart->site->classified_id > 0) {
            $order_item->set('listing_id', $cart->site->classified_id);
        }
        $order_item->setPricePlan($price_plan, $cart->user_data['id']);
        $order_item->setCategory($category);

        self::processSlots($order_item, $slotUrls);

        $order_item->save();
    }

    public static function mediaDisplay($type)
    {
        $view = geoView::getInstance();
        $cart = geoCart::getInstance();

        self::getPreExistingVideos();

        $tpl_vars = $headerVars = $cart->getCommonTemplateVars();
        //set order item specific vars in a sub-var to help prevent var name collisions between order items
        $offsite_videos = array();

        if ($type) {
            $cart->site->messages = $cart->db->get_text(true, 10);
        } else {
            $cart->site->page_id = 10;
            $cart->site->get_text();
        }

        //set the text
        if ($cart->main_type == 'listing_edit') {
            //set text for edit
            $offsite_videos['section_title'] = $cart->site->messages[500912];
            $offsite_videos['description'] = $cart->site->messages[500915];
        } elseif ($cart->main_type == 'auction' || $cart->main_type == 'reverse_auction') {
            //set text for auction placement
            $offsite_videos['section_title'] = $cart->site->messages[500911];
            $offsite_videos['description'] = $cart->site->messages[500914];
        } elseif ($cart->main_type == self::type) {
            //have to set all the settings, use text for classifieds
            $offsite_videos['section_title'] = $cart->site->messages[500969];
            $offsite_videos['description'] = $cart->site->messages[500970];

            //set these as well
            $tpl_vars['title1'] = $cart->site->messages[500971];
            $tpl_vars['title2'] = $cart->site->messages[500972];
            $tpl_vars['page_description'] = $cart->site->messages[500973];
            $tpl_vars['cancel_txt'] = $cart->site->messages[500974];
        } else {
            //set text for normal classified (or unknown type) placement
            $offsite_videos['section_title'] = $cart->site->messages[500910];
            $offsite_videos['description'] = $cart->site->messages[500913];
        }

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        $offsite_videos['maxVideos'] = $max = (int)self::getMaxVideos();
        $offsite_videos['minVideos'] = $min = (int)$planItem->minVideos;
        $offsite_videos['freeVideos'] = $free = (geoPC::is_ent()) ? (int)$planItem->freeVideos : 0;
        $offsite_videos['costPerVideo'] = $cost = $planItem->get('costPerVideo', 0);

        if ($max <= 0 && !$cart->isCombinedStep()) {
            //oops there are no allowed youtube videos
            return;
        }

        if (isset($_GET['no_ssl_force']) && $_GET['no_ssl_force'] && isset($_GET['media_submit_form_ssl']) && $_GET['media_submit_form_ssl']) {
            //there was something preventing media page from continuing, make
            //sure any errors are registered..
            $order_item = $cart->getChildItem(self::type);

            if (!$order_item) {
                $existing = 0;
            } else {
                $existing = count($order_item->get('video_slots'));
            }
            if ($existing < $min) {
                $cart->addErrorMsg('offsite_videos', $cart->site->messages[500930]);
            }
        }

        //because of how templates work, need to create an array holding data for each video, set or not set.
        $offsite_videos['slots'] = array();

        $order_item = $cart->getChildItem(self::type);
        $uploadSlots = $slotErrors = array();
        $editSlot = 1;
        if ($order_item) {
            $uploadSlots = (array)$order_item->get('video_slots');
            $slotErrors = (array)$order_item->get('slot_errors');
        } elseif ($cart->item->getType() == 'listing_edit') {
            $uploadSlots = (array)$cart->item->get('previous_offsite_videos');
        }
        $parentType = $cart->item->getType();
        for ($i = 1; $i <= $max; $i++) {
            if (isset($uploadSlots[$i])) {
                $slot = $uploadSlots[$i];
                $slot['empty'] = false;
                $editSlot = $i + 1;
            } else {
                $slot = array();
                $slot['empty'] = true;
            }
            $slot['required'] = ($i <= $min);

            if ($cost && $parentType != 'listing_edit') {
                $slot['cost'] = ($free >= $i) ? $cart->site->messages[500927] : geoString::displayPrice($cost);
            }

            if (isset($slotErrors[$i])) {
                $slot['error'] = $slotErrors[$i];
            }

            $offsite_videos['slots'][$i] = $slot;
        }
        if ($editSlot <= $max) {
            $offsite_videos['slots'][$editSlot]['editing'] = true;
        }

        $tpl_vars['offsite_videos'] = $offsite_videos;
        unset($offsite_videos);

        if (geoAjax::isAjax() && !$view->bypass_display_page) {
            //in an AJAX call and NOT ajax for entire combined step

            $tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');
            $tpl->assign('in_ajax', true);
            $tpl->assign($tpl_vars);
            return array ('edit_slot' => $editSlot, 'upload_slots_html' => $tpl->fetch('offsite_videos/upload_videos.tpl'));
        }
        $pre = (defined('IN_ADMIN')) ? '../' : '';

        if ($type == 'tpl' || $cart->item->getType() == 'listing_edit' || $cart->main_type == self::type) {
            $tpl = new geoTemplate(geoTemplate::SYSTEM, 'order_items');

            $headerVars['currentSlot'] = $tpl_vars['currentSlot'] = $editSlot;
            $headerVars['adminId'] = (defined('IN_ADMIN')) ? geoSession::getInstance()->getUserId() : 0;
            $headerVars['userId'] = (int)$cart->user_data['id'];

            $tpl->assign($headerVars);

            if ($cart->main_type != self::type) {
                $view->setBodyVar($tpl_vars)
                    //->addCssFile($pre.geoTemplate::getUrl('css','system/order_items/offsite_videos/upload_videos.css'))
                    ->addJScript($pre . geoTemplate::getUrl('js', 'system/order_items/offsite_videos/upload_ajax.js'))
                    ->addTop($tpl->fetch('offsite_videos/upload_videos_head.tpl'));
                if ($max <= 0) {
                    //don't actually display...
                    return;
                }
                return array (
                    'file' => 'offsite_videos/upload_videos.tpl',
                    'g_type' => 'system',
                    'g_resource' => 'order_items',
                );
            }
        }

        if ($cart->main_type == self::type) {
            //- Editing video part by clicking edit next to videos in cart
            $tpl_vars ['mediaTemplates']['offsite_videos'] = array (
                'file' => 'offsite_videos/upload_videos.tpl',
                'g_type' => 'system',
                'g_resource' => 'order_items',
            );
            //note:  header tpl is already done above, but rest of this stuff isn't
            $view->setBodyTpl('shared/media.tpl', '', 'order_items')
                ->setBodyVar($tpl_vars)
                //->addCssFile($pre.geoTemplate::getUrl('css','system/order_items/offsite_videos/upload_videos.css'))
                ->addJScript($pre . geoTemplate::getUrl('js', 'system/order_items/offsite_videos/upload_ajax.js'))
                ->addTop($tpl->fetch('offsite_videos/upload_videos_head.tpl'));
            $cart->site->display_page();
            return;
        }
    }

    /**
     * Calculates the cost of the given video order item, assuming slots have
     * already been processed.
     * @param offsite_videosOrderItem $order_item
     */
    private static function _calculateCost($order_item)
    {
        $category = $order_item->getCategory();
        $price_plan = $order_item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        $min = (int)$planItem->minVideos;
        $max = (int)$planItem->maxVideos;
        $cost = $planItem->costPerVideo;
        $free = (int)(geoPC::is_ent()) ? $planItem->freeVideos : 0;

        $vidSlots = $order_item->get('video_slots');

        $vidCountCost = $vidCountFree = 0;
        for ($i = 1; $i <= $max; $i++) {
            if (isset($vidSlots[$i])) {
                if ($free >= $i) {
                    //this is free one
                    $vidCountFree++;
                } else {
                    $vidCountCost++;
                }
            }
        }

        $fullCount = $vidCountCost + $vidCountFree;

        $order_item->set('number_free_videos', $vidCountFree);
        $order_item->set('number_cost_videos', $vidCountCost);
        $total = $vidCountCost * $cost;
        $order_item->setCost($total);

        //need to remember how many are purchased in parent
        $parent = $order_item->getParent();
        if ($parent) {
            //make sure image count is also set in session variables
            if ($parent->getType() != 'listing_edit' || $cost == 0 || $parent->get('offsite_video_slots') < $free) {
                //either this is a normal listing placement, or this is a listing edit and
                //the number of slots open is less than the number of free videos, or there is no charge for video.
                trigger_error('DEBUG CART: Video count being added to session vars, count: ' . $fullCount);
                $session_variables = $parent->get('session_variables');
                $session_variables['offsite_videos_purchased'] = $fullCount;
                $parent->set('session_variables', $session_variables);
                $parent->save();
                if (is_array($cart->site->session_variables)) {
                    $cart->site->session_variables['offsite_videos_purchased'] = $fullCount;
                }
            }
            if ($parent->getType() == 'listing_edit' && $fullCount <= $parent->get('offsite_video_slots')) {
                //no charge, they already paid for the extra image slots!
                $order_item->setCost(0);
            }
        }
    }

    public static function processSlots($order_item, $slotUrls)
    {
        $existingSlots = $order_item->get('video_slots');

        $newSlots = $slotErrors = $slotChanges = array();

        //we are starting "fresh" so clear any slot errors for order item.
        $order_item->set('slot_errors', false);
        $order_item->set('latest_changes', false);

        $msgs = DataAccess::getInstance()->get_text(true, 10);

        $videoIds = array();
        foreach ($slotUrls as $slotId => $url) {
            if (isset($existingSlots[$slotId]) && $existingSlots[$slotId]['video_id'] == $url) {
                //exactly same as already entered, no need to re-process.
                $newSlots[$slotId] = $existingSlots[$slotId];
                $videoIds[] = $existingSlots[$slotId]['video_id'];
                continue;
            }
            //anything past this point, considered a "change" to the box, so need
            //to update in ajax if applicable
            $slotChanges[$slotId] = $slotId;

            //extract the youtube ID
            $videoId = self::_getYoutubeId($url);
            if (!$videoId) {
                //URL was probably invalid, add an error
                $slotErrors[$slotId] = $msgs[500931];
                continue;
            }
            if (in_array($videoId, $videoIds)) {
                //this is duplicate video, don't go forward with it
                $slotErrors[$slotId] = $msgs[500934];
                continue;
            }
            $videoIds[] = $videoId;

            //Make sure it is valid youtube video, if not continue and add error for slot.
            $details = self::_getYoutubeData($videoId);
            if (!$details) {
                $slotErrors[$slotId] = $msgs[500932];
                continue;
            }

            //add to the slot array
            $newSlots[$slotId] = $details;
        }

        //mark any that were "blanked" as being changed
        foreach ($existingSlots as $slotNum => $slot) {
            if (!isset($newSlots[$slotNum])) {
                //changed
                $slotChanges[$slotNum] = $slotNum;
            }
        }

        if (count($slotErrors) > 0) {
            //don't fill in blanks to leave room for errors
            $order_item->set('slot_errors', $slotErrors);
            $order_item->set('video_slots', $newSlots);
            $order_item->set('latest_changes', $slotChanges);
            $cart = geoCart::getInstance();
            $cart->addError()
                ->addErrorMsg('offsite_videos', $msgs[500933]);

            return $newSlots;
        } else {
            //Final step: get rid of empty slots
            $slots = array();
            $slotId = 1;
            foreach ($newSlots as $oldSlotId => $slot) {
                if ($oldSlotId != $slotId) {
                    //slot ID changing, so changes to both slots
                    $slotChanges[$oldSlotId] = $oldSlotId;
                    $slotChanges[$slotId] = $slotId;
                }
                $slots[$slotId] = $slot;
                $slotId++;
            }
            $order_item->set('video_slots', $slots);
            $order_item->set('latest_changes', $slotChanges);
            //oh yes, and calculate new order item cost
            self::_calculateCost($order_item);
            return $slots;
        }
    }

    public static function getMaxVideos()
    {
        $cart = geoCart::getInstance();
        if (!$cart->item) {
            //only works when working on the item in the cart
            return 0;
        }
        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        $parentItem = ($cart->item->getType() == self::type) ? $cart->item->getParent() : $cart->item;

        $free = (geoPC::is_ent()) ? (int)$planItem->freeVideos : 0;

        if ($planItem->costPerVideo > 0 && $parentItem->getType() == 'listing_edit') {
            //editing -- cannot add new "charged" slots, but make sure to always show at least as many videos as already exist
            $slotsPurchased = (int)$parentItem->get('offsite_video_slots');
            if ($slotsPurchased >= $free) {
                $slotsAvailable = $slotsPurchased;
            } else {
                $slotsAvailable = $free;
            }
        } else {
            $slotsAvailable = (int)$planItem->maxVideos;
        }
        return $slotsAvailable;
    }

    public static function addNewItem()
    {
        $cart = geoCart::getInstance();
        $order_item = new offsite_videosOrderItem();
        $order_item->setParent($cart->item);//this is a child of the parent
        $order_item->setOrder($cart->order);

        $order_item->save();//make sure it's serialized
        $cart->order->addItem($order_item);
        return $order_item;
    }

    /**
     * This attempts to extract the video ID from the given URL
     * @param string $url The youtube URL (or youtube ID) to extract youtube ID
     *   from.
     * @return bool|string The youtube ID string on success, bool false if seems to be invalid.
     */
    private static function _getYoutubeId($url)
    {
        $url = trim($url);
        $idLen = 11;

        $pregClean = '/[^-_a-zA-Z0-9]+/';

        if (strlen($url) <= $idLen) {
            //must be the video ID by itself, or too short to be a valid URL

            //get rid of the invalid chars
            $vidId = preg_replace($pregClean, '', $url);

            return (strlen($vidId) == $idLen) ? $vidId : false;
        }

        //remove www. from the URL to make processing a little easier
        $url = str_replace('www.', '', $url);

        //break up the URL

        $url_parts = parse_url($url);
        if ($url_parts['path'] == $url) {
            //they didn't specify http, so add it so that parse url works
            $url_parts = parse_url('http://' . $url);
        }

        if ($url_parts['host'] == 'youtu.be') {
            //short version, id will be in path
            $vidId = ltrim($url_parts['path'], '/');
        } elseif (strpos($url_parts['host'], 'youtube') !== false) {
            //youtube is somewhere in host part of URL
            if (strpos($url_parts['path'], '/v/') === 0) {
                //direct link in format youtube.com/v/VIDEO_ID
                $vidId = substr($url_parts['path'], 3, $idLen);
            } else {
                //appears to be youtube's main site, so get it from query params
                parse_str($url_parts['query'], $query);
                $vidId = $query['v'];
            }
        }

        //clean ID up, we know it is only alpha-num chars
        $vidId = preg_replace($pregClean, '', $vidId);

        return (strlen($vidId) == $idLen) ? $vidId : false;
    }

    private static function _getYoutubeData($videoId)
    {
        if (!$videoId) {
            //just sanity check
            return false;
        }

        /*********************************************************************************************
         the API we were using to verify video data is deprecated / no longer exists as of 5/7/15
         until a replacement can be written, this is a dirty hack to just assume everything is correct and jam the data in
         TODO: FIX THIS!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         *********************************************************************************************/
        $data = array(
                'media_content_url' => "//www.youtube.com/v/{$videoId}",
                'media_content_type' => 'application/x-shockwave-flash',
                'video_id' => $videoId,
                'video_type' => 'youtube'
            );
        return $data;

        //*********************** END UGLY HACKAROUND *******************


        $youtubeApiUrl = 'http://gdata.youtube.com/feeds/api/videos/' . $videoId;

        $response = geoPC::urlGetContents($youtubeApiUrl);

        if ($response == 'Invalid id') {
            //invalid ID specified.
            return false;
        } elseif (strlen($response) < 100) {
            //there is no way a valid response would be under 100 chars, so must be an error
            return false;
        }

        //parse the XML to get
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_set_element_handler($parser, array('geoYoutubeXmlResponse', 'tagStart'), array('geoYoutubeXmlResponse', 'tagEnd'));
        xml_set_character_data_handler($parser, array ('geoYoutubeXmlResponse', 'contents'));
        xml_parse($parser, $response);

        $responseObj = geoYoutubeXmlResponse::$topTags[0];

        //go ahead and free up the parser
        xml_parser_free($parser);

        if (!$responseObj || !is_object($responseObj) || $responseObj->down('yt:noembed')) {
            //something wrong with response, OR it has yt:noembed which signifies no embedding the video
            //echo "$response\n\n";
            //kill the objects (hopefully this will do it good)
            geoYoutubeXmlResponse::$currentTag = geoYoutubeXmlResponse::$topTags = null;
            return false;
        }

        //now travel into the response object and extract the data we need
        //echo $responseObj;
        $data = array();
        //figure out media content URL
        $mediaContent = $responseObj->down('media:group')->down('media:content');

        while ($mediaContent && $mediaContent->attributes['yt:format'] != 5 && $mediaContent->next('media:content')) {
            //loop through until we get to media content with yt:format of 5
            $mediaContent = $mediaContent->next('media:content');
        }
        $data['media_content_url'] = $mediaContent->attributes['url'];
        //remove http: from front so it uses SSL or not SSL correctly
        $data['media_content_url'] = preg_replace('/^https?:/', '', $data['media_content_url']);
        $data['media_content_type'] = $mediaContent->attributes['type'];
        if (!strlen($data['media_content_url']) || !strlen($data['media_content_type'])) {
            //something wrong!
            //echo "$response\n\n";
            //kill the objects (hopefully this will do it good)
            geoYoutubeXmlResponse::$currentTag = geoYoutubeXmlResponse::$topTags = null;
            return false;
        }
        $data['video_id'] = $videoId;
        $data['video_type'] = 'youtube';

        //kill the objects (hopefully this will do it good)
        geoYoutubeXmlResponse::$currentTag = geoYoutubeXmlResponse::$topTags = null;

        return $data;
    }

    public static function getYoutubeDataForVideoId($youtubeId)
    {
        //clean the id
        $youtubeId = self::_getYoutubeId($youtubeId);
        //get the important data
        return ($youtubeId) ? self::_getYoutubeData($youtubeId) : false;
    }

    private static function _deleteVideos($listingId)
    {
        $db = DataAccess::getInstance();

        $listingId = (int)$listingId;

        if (!$listingId) {
            //sanity check
            return;
        }

        $db->Execute("DELETE FROM " . geoTables::offsite_videos . " WHERE `listing_id`=$listingId");
    }

    public static function getPreExistingVideos()
    {
        $cart = geoCart::getInstance();

        if ($cart->item->getType() != 'listing_edit') {
            //we only get pre existing videos from listing edit.
            return false;
        }

        if ($cart->item->get('existingOffsiteVideos', false)) {
            //we've already done this -- don't do it again
            return $cart->item->get('previous_offsite_videos');
        }
        //remember that we've already done this at least once.
        $cart->item->set('existingOffsiteVideos', 1);

        $listing_id = (int)$cart->item->get('listing_id', false);
        if (!$listing_id) {
            //something not right, not able to get existing videos
            return;
        }

        //get number of video slots available
        $listing = geoListing::getListing($listing_id);
        if (is_object($listing) && $listing->id > 0) {
            $slots = (int)$listing->offsite_videos_purchased;
            $cart->item->set('offsite_video_slots', $slots);
        }
        //get the videos from the listing
        $video_rows = $cart->db->GetAll("SELECT * FROM " . geoTables::offsite_videos . " WHERE `listing_id`=$listing_id");
        $videos = array();
        foreach ($video_rows as $row) {
            $videos[$row['slot']] = array (
                'video_id' => $row['video_id'],
                'media_content_url' => $row['media_content_url'],
                'media_content_type' => $row['media_content_type'],
                'video_type' => 'youtube',
            );
        }

        if ($videos) {
            $cart->item->set('previous_offsite_videos', $videos);
        }
        return $videos;
    }

    /**
     * Used to insert/update videos given the video slot data and listing id, for
     * a particular listing.
     *
     * @param array $video_slots
     * @param int $listing_id
     */
    private static function _updateVideos($video_slots, $listing_id)
    {
        //force input to be correct
        $video_slots = (array)$video_slots;
        $listing_id = (int)$listing_id;

        if (!$listing_id) {
            //can't apply anything if we don't have valid listing ID
            return false;
        }

        $db = DataAccess::getInstance();

        $videosAltered = array();

        foreach ($video_slots as $slot => $video) {
            //figure out if already exists for listing
            $query_data = array ($listing_id, '' . $video['video_id']);
            $existing = (int)$db->GetOne("SELECT `id` FROM " . geoTables::offsite_videos . " WHERE `listing_id`=? AND `video_id`=? AND `video_type`='youtube' LIMIT 1", $query_data);

            if ($existing) {
                //Update the thing
                $query_data = array ($slot, $existing);
                $db->Execute("UPDATE " . geoTables::offsite_videos . " SET `slot`=?, `video_type`='youtube' WHERE `id`=?", $query_data);
                $videosAltered[$existing] = $existing;
            } else {
                //insert it
                $query_data = array($listing_id, $slot, '' . $video['video_id'], '' . $video['media_content_url'], '' . $video['media_content_type']);
                $db->Execute("INSERT INTO " . geoTables::offsite_videos . " SET `listing_id`=?, `slot`=?, `video_type`='youtube', `video_id`=?, `media_content_url`=?, `media_content_type`=?", $query_data);
                $vidId = (int)$db->Insert_Id();
                if ($vidId) {
                    //inserted successfully
                    $videosAltered[$vidId] = $vidId;
                }
            }
        }

        //now wipe out any videos that are no longer around
        $in = ($videosAltered) ? " AND `id` NOT IN (" . implode(', ', $videosAltered) . ")" : '';

        $db->Execute("DELETE FROM " . geoTables::offsite_videos . " WHERE `listing_id`=$listing_id $in");
    }
}

class geoYoutubeXmlResponse
{
    public $name, $contents, $attributes, $parent, $prev, $next, $children;

    public static $currentTag, $topTags;

    public static function tagStart($parser, $name, $attribs)
    {
        $tag = new geoYoutubeXmlResponse();
        $tag->name = $name;
        $tag->attributes = $attribs;
        $tag->parent = (self::$currentTag) ? self::$currentTag : null;

        if (self::$currentTag) {
            if ($tag->parent->children) {
                //set next and prev
                $lastIndex = count($tag->parent->children) - 1;
                $tag->prev = $tag->parent->children[$lastIndex];
                $tag->prev->next = $tag;
            }

            self::$currentTag->children[] = $tag;
        } else {
            self::$topTags[] = $tag;
        }
        self::$currentTag = $tag;
    }

    public static function tagEnd($parser, $name)
    {
        if (self::$currentTag && self::$currentTag->name == $name) {
            self::$currentTag = (self::$currentTag->parent) ? self::$currentTag->parent : null;
        }
    }

    public static function contents($parser, $data)
    {
        if (!self::$currentTag) {
            //just a sanity check
            return;
        }
        self::$currentTag->contents .= '' . $data;
    }

    public function down($tagName = '')
    {
        if (!$this->children) {
            return null;
        }

        if (!$tagName || $this->children[0]->name == $tagName) {
            return $this->children[0];
        }
        return $this->children[0]->next($tagName);
    }

    public function next($tagName = '')
    {
        if (!$this->next) {
            return null;
        }

        $current = $this->next;
        while ($tagName && $current->next && $current->name != $tagName) {
            $current = $current->next;
        }
        if (!$tagName || $current->name == $tagName) {
            return $current;
        }
        return null;
    }

    public function previous($tagName = '')
    {
        if (!$this->previous) {
            return null;
        }

        $current = $this->previous;
        while ($tagName && $current->previous && $current->name != $tagName) {
            $current = $current->previous;
        }
        if (!$tagName || $current->name == $tagName) {
            return $current;
        }
        return null;
    }

    /**
     * For debugging purposes only, this will output a string with XML basically re-created.
     * do NOT count on this to create valid XML, it is just a way to see the xml object
     * as a string that is somewhat easy to follow.
     *
     * @param $tabs Used internally, don't need to specify this.
     */
    public function toString($tabs = '')
    {
        $attribs = array();
        foreach ($this->attributes as $key => $value) {
            $attribs[] = "$key=\"" . htmlspecialchars($value) . "\"";
        }
        $attribs = implode(' ', $attribs);
        if ($attribs) {
            $attribs = ' ' . $attribs;
        }
        $children = array();
        if ($this->contents) {
            $children[] = $tabs . "\t" . $this->contents;
        }

        foreach ($this->children as $childs) {
            foreach ($childs as $child) {
                $children[] = $child->toString($tabs . "\t");
            }
        }

        $children = implode("\n", $children);
        if ($children) {
            $children .= "\n";
        }

        return "{$tabs}<{$this->name}{$attribs}>\n{$children}{$tabs}</{$this->name}>";
    }

    public function __toString()
    {
        return $this->toString();
    }
}
