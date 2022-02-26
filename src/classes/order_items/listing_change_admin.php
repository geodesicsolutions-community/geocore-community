<?php

//order_items/listing_change_admin.php

require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';

class listing_change_adminOrderItem extends _listing_placement_commonOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = "listing_change_admin";
    public $renew_upgrade = 1;
    const type = 'listing_change_admin';//for convenience, to access from private functions

    /**
     * Needs to be the order that this item will be processed.
     *
     * @var int
     */
    protected $defaultProcessOrder = 25;
    const defaultProcessOrder = 25;//for convenience, to access from private functions

    protected static $_debug_renewals = 0;

    public static function init($listing, $session_variables, $all_vars)
    {
        if (!defined('IN_ADMIN') || !$listing) {
            //this should only happen from the admin, and only if listing is good!
            return false;
        }
        parent::$_type = self::type;
        $isLive = $listing->live;
        $userId = $listing->seller;
        $cart = geoCart::getInstance();
        $cart->init(true, $userId);

        //must set cart order manually now with how things work
        //create new order for this cart
        //create order object
        $order = new geoOrder();

        //set up order's info, if the individual order item wants to change any of these, they can.
        $order->setSeller(0);//seller is 0, it is the site doing the "selling", selling the ability to place the listing.
        $order->setBuyer($userId); //set buyer to be this user
        $order->setParent(0);//this is main listing order
        $order->setCreated(geoUtil::time());

        $cart->order = $order;

        $cart->item = null;
        $cart->main_type = 'cart';

        //Ok now, pay attention here, we're doing some fancy magical stuff so try to keep up!
        //create an item
        $item = geoOrderItem::getOrderItem(self::type);
        $item->setStatus('pending');

        $cart->item = $item;

        if ($listing->live == 0 && $listing->item_type == 2) {
            //need to copy over to a new listing
            $listingId = $listing->id;
            parent::_copyListing($listingId, 2, false, $item);

            //get the new listing
            $listing = geoListing::getListing($item->get('listing_id'));

            if ($listing->id == $listingId) {
                //somthing is wrong!
                geoAdmin::m('Internal Error:  Auction copy to new listing failed, aborting changes.', geoAdmin::ERROR);
                return false;
            }

            if ($listing->order_item_id == $item->getId()) {
                //do some voodoo to create a new order item of the correct type for
                //this listing...
                $listing->order_item_id = 0;
                self::_createItemForLegacyListing($listing->id, false);
            }
            //be sure to set start time to now.
            if (!isset($session_variables['date'])) {
                $session_variables['date'] = geoUtil::time();
            }
            //let the admin know
            geoAdmin::m('The auction was renewed by creating a copy of the original auction, below you can further alter the details for the new auction.', geoAdmin::NOTICE);
            $_REQUEST['b'] = $listing->id;
        } elseif ($listing->live == 0) {
            //listing is currently expired, so set start date to now
            if (!isset($session_variables['date'])) {
                $session_variables['date'] = geoUtil::time();
            }
        }
        $item->set('listing_id', $listing->id);
        if ($all_vars['remove_current_bids']) {
            //let item remember it
            $item->set('remove_current_bids', 1);
        }

        //special case workaround for the workaround for clearing payment_options on listing edits
        //to make it not wipe payment options during an admin listing change
        $session_variables['payment_options_admin'] = true;

        //save the differences only, for easy "undoing"
        parent::_saveSessionVarsDiff($item, $session_variables);

        //apply changes, do NOT use "differences" to apply changes, to make
        //sure that stuff like auction min bid gets reset if needed.
        parent::_insertListingFromSessionVars($session_variables, $listing->id);

        //save stuff not normally done with session vars
        $listing->expiration_notice = 0;
        $listing->expiration_last_sent = 0;
        //Reset the viewed count
        if (isset($all_vars['reset_viewed_count']) && $all_vars['reset_viewed_count']) {
            $listing->viewed = 0;
        }
        //make it live of course
        $listing->live = 1;

        if ($listing->item_type == 2 && $isLive && isset($all_vars['remove_current_bids']) && $all_vars['remove_current_bids']) {
            //specifically for auctions that need current bids removed
            // Remove current bids
            $db = DataAccess::getInstance();
            $sql = "DELETE FROM " . geoTables::bid_table . " WHERE `auction_id` = {$listing->id}";
            $result = $db->Execute($sql);
            if (!$result) {
                return false;
            }
            // Remove current autobids
            $sql = "DELETE FROM " . geoTables::autobid_table . " WHERE `auction_id`= {$listing->id}";
            $result = $db->Execute($sql);
            if (!$result) {
                return false;
            }
        }
        //make item active, but don't go through normal processStatusChange, since we just did
        //that work above.
        $cart->order->addItem($item);
        $item->setOrder($cart->order);
        $item->setStatus('active');

        $cart->order->processStatusChange('active');

        $cart->order->save();

        //always update the category count, just to be on the safe side.
        geoCategory::updateListingCount($listing->category);


        //now create an order to stuff the item in so it's not just all free willy, so it will
        //be displayed in the admin


        return $item;
    }

    public function getTypeTitle()
    {
        return 'Listing Changed by Admin';
    }

    public function adminDetails()
    {
        $session_variables = parent::_getSessionVarsFromListing($this->get('listing_id'));
        $listing_id = $this->get('listing_id');
        //die ('session vars: <pre>'.print_r($session_variables,1));
        $title = $titleHover = $session_variables['classified_title'];

        if (strlen($title) > 25) {
            $title = geoString::substr($title, 0, 22) . '...';
        }
        if ($listing_id) {
            $titleHover .= " (Listing # $listing_id)";
        }
        $title = "<span title=\"$titleHover\">$title</span>";

        return array(
            'type' => ucwords(str_replace('_', ' ', $this->getType())),
            'title' => $title
        );
    }

    /**
     * Used: in geoCart::initItem()
     *
     * Used when creating a new item.  This gets the old listing's data and sets up the stuff for it to be renewed/upgraded
     */
    public function geoCart_initItem_new($item_type)
    {
        //this item is only used in the admin
        return false;
    }

    public function geoCart_initItem_restore()
    {
        //this item is only used in the admin
        return false;
    }

    public static function geoCart_payment_choicesProcess($sell_type)
    {
        //this item is only used in the admin
        return ;
    }

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        //TODO: Give admin edit it's own text
        trigger_error('DEBUG CART: Here');
        $this->renew_upgrade = $this->get('renew_upgrade');
        $price = 0; //people expect numbers to be positive...

        $msgs = DataAccess::getInstance()->get_text(true, 10202);
        if ($this->renew_upgrade == self::renewal) {
            $title = $msgs[500329];
            $can_preview = true;
        } else {
            $title = $msgs[500330];
            $can_preview = false;
        }
        $listing = geoListing::getListing($this->get('listing_id'));
        if (is_object($listing)) {
            $l_title = geoString::fromDB($listing->title);
        }

        //shorten the title to...  20 chars
        if (strlen(trim($l_title)) > 20) {
            $l_title = geoString::substr($l_title, 0, 20) . "...";
        }

        $title .= " - $l_title";

        $return = array (
            'title' => $title,
            'canEdit' => false, //whether can edit it or not
            'canDelete' => false, //whether can remove from cart or not
            'canPreview' => false, //whether can preview the item or not
            'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price, //amount this and all children adds to the total
            'children' => false
        );

        return $return;
    }

    public function getCostDetails()
    {
        //this should never have costs
        return false;
    }


    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false, $skipToParent = false)
    {
        //Note:  $skipToParent is just a param used by parent, not by here
        if ($newStatus == $this->getStatus()) {
            parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount, true);
            return;
        }
        trigger_error('DEBUG CART: Here');

        if ($newStatus == 'active' || $this->getStatus() == 'active') {
            //activating or de-activating
            $activate = ($newStatus == 'active') ? true : false;

            $db = DataAccess::getInstance();

            //do the renewal thing
            $listing = geoListing::getListing($this->get('listing_id'));
            if (!is_object($listing)) {
                trigger_error('DEBUG CART TRANSACTION: listing_change_admin:transaction_process() - could not get listing object for id ' . $this->get('listing_id'));
                //go ahead and set item to active, even though we don't have anything to do for it.
                parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount, true);
                return;
            }

            //correct for mis-behaving children, make sure session vars only contain changes
            parent::_saveSessionVarsDiff($this, $this->get('session_variables'));

            $current_status = $this->getStatus();
            if ($current_status == 'active') {
                $this->setStatus('temp_disable');
            }

            $old = parent::_getSessionVarsFromListing($listing->id, false, true);
            $this->setStatus($current_status); //set status back

            if (!isset($old['ends']) || !isset($old['date'])) {
                //this listing was placed before we started
                //recording ends and date as a session var, so fix it on
                //the original session var.
                $old_item = geoOrderItem::getOrderItem($listing->order_item_id);
                if ($old_item) {
                    $session_vars = $old_item->get('session_variables');
                    if ($session_vars) {
                        //get the current ends from the listing and store it as a
                        //session var.

                        if (!isset($old['ends'])) {
                            $session_vars['ends'] = $old['ends'] = $listing->ends;
                        }
                        if (!isset($old['date'])) {
                            $session_vars['date'] = $old['date'] = $listing->date;
                        }
                        $old_item->set('session_variables', $session_vars);
                        $old_item->save();
                    }
                }
            }

            $new = array_merge($old, $this->get('session_variables'));

            $vars = ($activate) ? $new : $old;

            //now figure out if live should be changing and change it if needed.
            $liveChanged = false;
            $live = ($listing->live) ? true : false;
            if (!$live && $activate && $vars['ends'] > geoUtil::time()) {
                //if not currently live, and activating, and ends is in the future,
                //then make it live.
                $listing->live = 1;
                $liveChanged = true;
            } elseif ($live && !$activate && $vars['ends'] < geoUtil::time()) {
                //Setting to not be active, and it is currently active, and this is a result of a new listing or
                //the expire time is before the current time
                $listing->live = 0;
                $liveChanged = true;
            }

            //apply changes to listing
            self::_insertListingFromSessionVars($vars, $this->get('listing_id'));

            if ($updateCategoryCount && $liveChanged) {
                geoCategory::updateListingCount($this->getCategory());
            }
        }

        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount, true);
    }

    function getTransactionDescription()
    {
        return 'Listing Change by Admin';
    }


    public static function getParentTypes()
    {
        trigger_error('DEBUG CART: Here');
        //this is main order item, no parent types
        //return array(0, 'classified', 'auction', 'dutch_auction');
        return array ();
    }

    public static function geoCart_initSteps($allPossible = false)
    {
        //this item is only used in the admin
    }
    public static function geoCart_initItem_forceOutsideCart()
    {
        //this item is only used in the admin
        return false;
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
        //this item is only used in the admin
        return false; //this item has stuff to display on other_details step.
    }

    public static function geoCart_other_detailsCheckVars()
    {
        //this item is only used in the admin
    }

    public static function geoCart_other_detailsProcess()
    {
        //this item is only used in the admin
    }

    /**
     * Returns data to be displayed on listing cost and features section
     *
     * @return array of data that is processed and used to display the listing cost box
     */
    public static function geoCart_other_detailsDisplay()
    {
        //this item is only used in the admin
    }

    public static function geoCart_other_detailsLabel()
    {
        //never used
        return 'extras';
    }

    public function processRemove()
    {
        trigger_error('DEBUG UNLOCK: in procRemove');
        //remove edit lock on listing
        $listing = geoListing::getListing($this->get('listing_id'));
        if ($listing) {
            $listing->setLocked(false);
        }

        if ($this->get('listing_copy_id')) {
            //this is a new (copied) listing, so remove the listing
            $orig = geoListing::getListing($this->get('listing_copy_id'));
            if ($orig) {
                $orig->setLocked(false);
            }
            return (parent::processRemove());
        } elseif ($this->getStatus() == 'active') {
            //re-using code is fun :)
            $this->processStatusChange('pending', false, true);
        }
        return true;
    }

    public static function adminItemDisplay($item_id)
    {
        if (!$item_id) {
            return '';
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() != self::type) {
            return '';
        }

        $info = '';

        $changes = 0;
        if ($item->get('listing_copy_id')) {
            $info .= geoHTML::addOption('Item Type', 'Auction Renewal in Admin (copy of original)');
            $info .= geoHTML::addOption('Original Auction ID', "# " . $item->get('listing_copy_id'));
            $changes++;
        } else {
            $info .= geoHTML::addOption('Item Type', 'Listing changed in Admin');
        }
        $session_variables = $item->get('session_variables');

        $all_vars = self::_getSessionVarsFromListing($item->get('listing_id'));

        $info .= geoHTML::addOption('Listing', "# " . $item->get('listing_id') . ' - ' . $all_vars['classified_title']);

        if (isset($session_variables['date'])) {
            $info .= geoHTML::addOption('New start date', date('M d, Y G:i:s', $session_variables['date']));
            $changes++;
        }
        if (isset($session_variables['ends'])) {
            $info .= geoHTML::addOption('New end date', date('M d, Y G:i:s', $session_variables['ends']));
            $changes++;
        }

        //info about prices
        if ($item->get('remove_current_bids')) {
            $info .= geoHTML::addOption('All Bids were Reset', '(this change is permanent)');

            $changes++;
        }

        $possible_extras = array (
            'featured_ad',
            'featured_ad_2',
            'featured_ad_3',
            'featured_ad_4',
            'featured_ad_5',
            'bolding',
            'better_placement'
        );
        $extras = array();
        foreach ($possible_extras as $extra) {
            if (isset($session_variables[$extra])) {
                $extras[] = ucwords(str_replace('_', ' ', $extra)) . ' <strong>' . (($session_variables[$extra]) ? 'Added' : 'Removed') . '</strong>';
                $changes++;
            }
        }
        if (isset($session_variables['attention_getter'])) {
            if (!$session_variables['attention_getter']) {
                $extras[] = "Attention Getter <strong>Removed</strong>";
            } else {
                $extras[] = "Attention Getter <img src='../{$session_variables['attention_getter_url']}' src='' /> <strong>Added</strong>";
            }
        }
        if ($extras) {
            $info .= geoHTML::addOption('Listing Extras Changed', implode('<br />', $extras));
        }

        if (!$changes) {
            $info .= geoHTML::addOption('Listing Changes', 'N/A - no changes were made');
        }

        return $info;
    }
}
