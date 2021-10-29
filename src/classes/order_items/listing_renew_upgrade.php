<?php

//order_items/listing_renew_upgrade.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.12.0-4-gc32f69b
##
##################################

require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';

class listing_renew_upgradeOrderItem extends _listing_placement_commonOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = "listing_renew_upgrade";
    const type = 'listing_renew_upgrade';//for convenience, to access from private functions

    /**
     * Needs to be the order that this item will be processed.
     *
     * @var int
     */
    protected $defaultProcessOrder = 25;
    const defaultProcessOrder = 25;//for convenience, to access from private functions

    protected static $_debug_renewals = 0;

    public function getTypeTitle()
    {
        return 'Listing Renew/Upgrade';
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
        $admin = geoAdmin::getInstance();
        $db = DataAccess::getInstance();

        $tpl_vars = array();
        $tpl_vars['renew_reset_start'] = $planItem->get('renew_reset_start');
        $tpl_vars['upgrade_reset_start'] = $planItem->get('upgrade_reset_start');
        $tpl_vars['no_live_downgrade'] = $planItem->get('no_live_downgrade');

        $tpl = new geoTemplate(geoTemplate::ADMIN);

        $tpl->assign($tpl_vars);

        return $tpl->fetch('order_items/listing_renew_upgrade/settings.tpl');
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
        $settings = $_POST['listing_renew_upgrade'];

        if (is_array($settings)) {
            $renew_restart = (isset($settings['renew_reset_start']) && $settings['renew_reset_start']) ? 1 : false;
            $upgrade_restart = (isset($settings['upgrade_reset_start']) && $settings['upgrade_reset_start']) ? 1 : false;
            $no_live_downgrade = (isset($settings['no_live_downgrade']) && $settings['no_live_downgrade']) ? 1 : false;
            $planItem->set('renew_reset_start', $renew_restart);
            $planItem->set('upgrade_reset_start', $upgrade_restart);
            $planItem->set('no_live_downgrade', $no_live_downgrade);
        }

        return true;
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
        $type = ($this->get('renew_upgrade') == self::renewal) ? "<span style='color: green;'>[Renew]</span> " : "<span style='color: blue'>[Upgrade]</span> ";
        $title = $type . $title;
        return array(
            'type' => 'Listing Renew/Upgrade',
            'title' => $title
        );
    }

    protected function initPricePlan($listing)
    {
        $cart = geoCart::getInstance();
        //Now get the price plan to use
        $listing_price_plan_id = 0;
        $listing_price_plan_type_of_billing = 0;
        $this->setCategory($listing->category);
        if ($listing->price_plan_id) {
            //check to see if price plan attached to classified ad exists
            $sql = "SELECT `type_of_billing` FROM " . geoTables::price_plans_table . " WHERE `price_plan_id` = ? LIMIT 1";
            $price_plan_check = $cart->db->GetRow($sql, array($listing->price_plan_id));

            if (isset($price_plan_check['type_of_billing'])) {
                //there is a price plan attached to the listing ID
                $listing_price_plan_id = $listing->price_plan_id;
                $listing_price_plan_type_of_billing = $price_plan_check['type_of_billing'];
            }
        }

        if (
            ($listing_price_plan_id) && ($cart->price_plan['type_of_billing'] != 2) && $listing_price_plan_id != $cart->price_plan['price_plan_id']
            && $listing_price_plan_type_of_billing == $cart->price_plan['type_of_billing']
        ) {
            //set the current price plan to the price plan connected to the classified ad
            //a price plan attached to the classified ad overrides the default price plan
            //attached to the user.
            //this price plan was chosen at the time the ad was placed.

            //check the user attached price plan type to make sure it matches the
            //price plan type used within the classified ad.  If the two types do not
            //match the type for the user will take precedence.

            //NOTE: setPricePlan will check the price plan for us, and even check to make sure
            //the price plan is valid for that user..
            $this->setPricePlan($listing_price_plan_id, $listing->seller);
            //set cart's price plan
            $cart->setPricePlan($this->getPricePlan(), $this->getCategory());
        } else {
            //need to set price plan according to category.
            if ($this->get('item_type') == 1) {
                $planId = $cart->user_data['price_plan_id'];
            } else {
                $planId = $cart->user_data['auction_price_plan_id'];
            }

            $this->setPricePlan($planId, $listing->seller);
            $cart->setPricePlan($this->getPricePlan(), $this->getCategory());
        }
    }

    /**
     * Used: in geoCart::initItem()
     *
     * Used when creating a new item.  This gets the old listing's data and sets up the stuff for it to be renewed/upgraded
     */
    public function geoCart_initItem_new($item_type = null)
    {
        trigger_error('DEBUG CART: Here');
        $listing_id = intval($_REQUEST['listing_id']);
        if (!$listing_id) {
            //invalid id specified
            return false;
        }
        parent::$_type = self::type;
        $cart = geoCart::getInstance();
        //make sure text is there for errors
        $cart->site->messages = $cart->db->get_text(true, 10202);

        //make sure they are using a valid id

        $cart->site->classified_id = $listing_id;
        $renew_upgrade = $cart->site->renew_upgrade = intval($_REQUEST['r']);

        $listing = geoListing::getListing($listing_id);
        if (!is_object($listing) || $listing->id !== $listing_id || $listing->seller != $cart->user_data['id'] || !in_array($renew_upgrade, array(self::renewal,self::upgrade)) || geoListing::isRecurring($listing->id)) {
            //not valid listing, or not attached to user
            $cart->addErrorMsg('listing_renew_upgrade', "Error renewing or upgrading listing, invalid data.");
            return false;
        }

        //make sure the order item for this listing is still Active
        //This prevents listings from being Declined by an admin but then renewed by their owners anyway
        if ($listing->order_item_id && geoOrderItem::itemExists($listing->order_item_id)) {
            $listingItem = geoOrderItem::getOrderItem($listing->order_item_id);
            if ($listingItem->getStatus() !== 'active') {
                //item for this listing is not active. Can't get here through normal use of the software, so just show a generic error message and leave
                $cart->addErrorMsg('listing_renew_upgrade', "Invalid data error.");
                return false;
            }
        }

        //check for locks on this listing
        if ($listing->isLocked()) {
            //cannot renew/upgrade, alteration already affecting listing
            $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500614]);
            return false;
        }

        //lock listing
        $listing->setLocked();

        $this->renew_upgrade = $renew_upgrade;
        $this->set('item_type', $listing->item_type);
        $this->set('renew_upgrade', $renew_upgrade);
        $this->set('live', $listing->live);
        if ($renew_upgrade == self::renewal && $listing->item_type != 1) {
            //Renewal of something that is not a classified
            //create new listing, basically act just like copy a listing, but
            //don't allow it to be copied from archive table

            //need to set price plan info
            $this->initPricePlan($listing);

            //so that things work that rely on this item being in the order,
            //temporarily add this item to the order.
            $cart->order->addItem($this);
            $this->setOrder($cart->order);

            parent::_copyListing($listing_id, $listing->item_type, false, $this);
            //record old end time for use when checking the user is still in renewal period time
            $this->set('ends_for_periodCheck', $listing->ends);

            $oldListing = $listing;

            //get the new listing
            $listing = geoListing::getListing($this->get('listing_id'));
            if ($oldListing->price_applies == 'item') {
                //update the quantity remaining
                $quantity = (int)$oldListing->quantity_remaining;
                if ($quantity <= 0) {
                    //use quantity
                    $quantity = (int)$oldListing->quantity;
                }
                if ($quantity <= 0) {
                    //If it still doesn't have quantity, set quantity to 1
                    $quantity = 1;
                }
                //set the quantity / quantity remaining to the quantity we figured out
                $listing->quantity = $listing->quantity_remaining = $quantity;
            }

            //reset "viewed" counts
            $listing->viewed = $listing->responded = $listing->forwarded = 0;

            //done with the old listing
            unset($oldListing);

            if ($listing->order_item_id == $this->getId()) {
                //do some voodoo to create a new order item of the correct type for
                //this listing...
                $listing->order_item_id = 0;
                self::_createItemForLegacyListing($listing->id, false);
            }

            $this->save();
        }

        //need to set "parent session vars" so children will know which of
        //themselves is already added.  Note that parent session vars are not
        //set in the DB or anything.
        $currentStatus = $this->getStatus();
        if ($currentStatus == 'active') {
            $this->setStatus('temp_disable');
        }
        $cart->site->parent_session_variables = parent::_getSessionVarsFromListing($listing->id);
        $this->setStatus($currentStatus);

        //this is brand spankin new, so far there are not any differences.
        $this->set('session_variables', array());
        //but session vars in cart site get all of them
        $cart->site->session_variables = parent::_getSessionVarsFromListing($listing->id, 0);
        $this->set('listing_id', $listing->id);
        $this->set('renew_upgrade', $this->renew_upgrade);
        $this->setCategory($listing->category);
        $this->set('item_type', $listing->item_type);

        $this->initPricePlan($listing);
        if (!self::_checkWithinPeriod()) {
            $listing = geoListing::getListing($this->get('listing_id'));
            if ($listing) {
                $listing->setLocked(false);
            }
            return false;
        }
        $this->setCost(self::_getCost());

        $cart->site->classified_id = $this->get('listing_id');
        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->category_id = $this->getCategory();
        $cart->site->price_plan_id = $cart->price_plan['price_plan_id'];
        $this->save();
        if ($this->renew_upgrade == self::renewal && !$listing->live) {
            //not currently live, so make sure max listings is ok
            $limitReturn = self::_checkMaximumListingLimit();
            if (!$limitReturn) {
                $listing->setLocked(false);
                return false;
            } else {
                return true;
            }
        }
        return true;
    }

    public function geoCart_initItem_restore()
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();
        parent::$_type = self::type;
        $this->renew_upgrade = $cart->site->renew_upgrade = $this->get('renew_upgrade');


        //need to set "parent session vars" so children will know which of
        //themselves is already added.  Note that parent session vars are not
        //set in the DB or anything.
        $currentStatus = $this->getStatus();
        if ($currentStatus == 'active') {
            $this->setStatus('temp_disable');
        }
        $cart->site->parent_session_variables = parent::_getSessionVarsFromListing($this->get('listing_id'));
        $this->setStatus($currentStatus);


        $cart->site->classified_id = $cart->site->listing_id = $this->get('listing_id');

        $cart->setPricePlan($this->getPricePlan(), $this->getCategory());
        $cart->site->session_variables = array_merge(parent::_getSessionVarsFromListing($this->get('listing_id')), $this->get('session_variables'));

        if ($cart->getAction() != 'delete' && !self::_checkWithinPeriod()) {
            //oops, went outside of time before placed the listing!

            //error message will have been set by checkWithinPeriod.

            //remove edit lock before failing
            $listing = geoListing::getListing($this->get('listing_id'));
            if ($listing) {
                $listing->setLocked(false);
            }
            return false;
        }
        $cart->site->classified_id = $this->get('listing_id');
        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->category_id = $this->getCategory();
        $cart->site->price_plan_id = $cart->price_plan['price_plan_id'];

        return true;
    }

    protected static function _checkWithinPeriod()
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();
        //make sure text is there for errors
        $cart->site->messages = $cart->db->get_text(true, 10202);

        $listing = geoListing::getListing($cart->item->get('listing_id'));
        if (!is_object($listing)) {
            //just to double check.. no error message needed
            trigger_error('ERROR CART: could not get listing!  returning false.');
            return false;
        }
        if ($cart->item->renew_upgrade == self::renewal && in_array($cart->price_plan['type_of_billing'], array(1,2))) {
            //renewal, check to make sure within renewal window

            //if ends_for_periodCheck is set, this is an auction renewal -- that value is the 'ends' from the original listing
            //in that case, $listing here is the new, copied listing, and $listing->ends will always be Now
            //if we didn't set that value earlier, this is a classified, so go ahead and check against $listing->ends
            $endTime = ($cart->item->get('ends_for_periodCheck')) ? $cart->item->get('ends_for_periodCheck') : $listing->ends;

            if (!is_numeric($endTime)) {
                //if we still don't have an end time, something's wrong
                //give them the generic error.
                $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500611]);
                trigger_error('DEBUG CART: Endtime not numeric, something wrong.' . $endTime);
                return false;
            }
            $renew_cutoff = ($endTime - ($cart->db->get_site_setting('days_to_renew') * 86400));
            $renew_postcutoff = ($endTime + ($cart->db->get_site_setting('days_to_renew') * 86400));
            if (!$cart->db->get_site_setting('days_to_renew') || (geoUtil::time() <= $renew_cutoff) || (geoUtil::time() >= $renew_postcutoff)) {
                if (!$cart->db->get_site_setting('days_to_renew')) {
                    //not allowed to ever renew
                    $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500612]);
                } else {
                    $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500613]);
                }
                trigger_error('DEBUG CART: not within allowed time, cannot renew/upgrade.');
                return false;
            }
        }
        if ($cart->item->renew_upgrade == self::upgrade) {
            if (!($cart->db->get_site_setting('days_can_upgrade') && geoPC::is_ent())) {
                trigger_error('DEBUG CART: not within allowed time, cannot renew/upgrade.');
                //give them the generic error.
                $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500611]);

                return false;
            }
            if (!$listing->live) {
                //can't upgrade a non-live listing!  (note: live will be 1 even if it's an auction that hasn't started yet,
                //as long as payment has been made and approved by admin (if applicable)
                trigger_error('DEBUG CART: not within allowed time, cannot renew/upgrade.');
                //give them the generic error.
                $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500611]);
                return false;
            }
            $upgrade_cutoff = ($listing->date + ($cart->db->get_site_setting('days_can_upgrade') * 86400));
            if (geoUtil::time() >= $upgrade_cutoff) {
                trigger_error('DEBUG CART: not within allowed time, cannot renew/upgrade.');
                //give them the generic error.
                $cart->addErrorMsg('listing_renew_upgrade', $cart->site->messages[500611]);
                return false;
            }
        }
        //get this far, it must be within time frame
        trigger_error('DEBUG CART: return true here.');
        return true;
    }
    /**
     * Optional.  Required if in getDisplayDetails ($inCart) you returned true for the array index of canPreview
     *
     */
    public function geoCart_previewDisplay($sell_type = null)
    {
        trigger_error('DEBUG CART: Here');
        parent::$_type = self::type;
        parent::geoCart_previewDisplay($this->get('item_type'));
    }

    public static function geoCart_payment_choicesProcess($sell_type = null)
    {
        trigger_error('DEBUG CART: Here');
        return ;

        parent::$_type = self::type;
        parent::geoCart_payment_choicesProcess();
    }


    /**
     * Update Functions : called from main software using geoOrderItem::callUpdate(), and that
     * function calls the one here if the function exists.  To avoid name conflicts, if you need
     * custom functions specific for this orderItem, prepend the var or function name with an
     * underscore.
     */

    public function getDisplayDetails($inCart, $inEmail = false)
    {
        trigger_error('DEBUG CART: Here');
        $this->renew_upgrade = $this->get('renew_upgrade');
        $price = $this->getCost(); //people expect numbers to be positive...

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
            $title .= " - " . $this->_listingTitleDisplay(geoString::fromDB($listing->title));
        }

        $return = array (
            'title' => $title,
            'canEdit' => true, //whether can edit it or not
            'canDelete' => true, //whether can remove from cart or not
            'canPreview' => $can_preview, //whether can preview the item or not
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price, //amount this and all children adds to the total
            'children' => false
        );

        //go through children...
        $order = $this->getOrder();
        $items = $order->getItem();
        $children = array();
        foreach ($items as $i => $item) {
            if (is_object($item) && is_object($item->getParent())) {
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

    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false, $skipToParent = null)
    {
        if ($newStatus == $this->getStatus()) {
            return;
        }
        trigger_error('DEBUG CART: Here');
        if ($newStatus != 'pending') {
            //make sure to unlock listing if needed
            $listing = geoListing::getListing($this->get('listing_id'));
            //unlock listing
            if ($listing && $listing->isLocked()) {
                $listing->setLocked(false);
            }
            //Note: we do NOT re-lock a listing if going from non-pending to pending, as that
            //is just a silly admin user doing that, they most likely don't mean to lock
            //up the listing.

            //this is a copied listing (or an auction renewal) -- need to unlock the original
            if ($this->get('listing_copy_id')) {
                //this is a new (copied) listing, so remove the listing
                $orig = geoListing::getListing($this->get('listing_copy_id'));
                if ($orig && $orig->isLocked()) {
                    $orig->setLocked(false);
                }
            }
        }

        if ($newStatus == 'active' || $this->getStatus() == 'active') {
            //activating or de-activating
            $activate = ($newStatus == 'active') ? true : false;

            $db = DataAccess::getInstance();

            //do the renewal thing
            $listing = geoListing::getListing($this->get('listing_id'));
            if (!is_object($listing)) {
                trigger_error('DEBUG CART TRANSACTION: listing_renew_upgrade:transaction_process() - could not get listing object for id ' . $this->get('listing_id'));
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

            if ($activate) {
                //this is a special case where we update session vars during
                //activation of renewal/upgrade set start/end times
                $planItem = $this->getPlanItem();
                if ($planItem) {
                    $reset_start_renew = $planItem->get('renew_reset_start');
                    $reset_start_upgrade = $planItem->get('upgrade_reset_start');
                } else {
                    //fallback...  default to not reset start time if plan item
                    //could not be retrieved
                    $reset_start_renew = $reset_start_upgrade = false;
                }
                if ($this->get('renew_upgrade') == self::renewal) {
                    $new_ends = $this->get('new_ends');
                    if (!$new_ends) {
                        //first time this has been activated, so calculate
                        //ends time.
                        if ($orig && $orig->ends) {
                            //this is an auction renewal. because sessvars are not updated when an auction closes early (e.g. is sold),
                            //they are unreliable for determining the correct time to begin the renewal
                            //in this case, use the later of 'ends' or now from the actual listing data
                            $start = (int)(($orig->ends > geoUtil::time()) ? $orig->ends : geoUtil::time());
                        } else {
                            //otherwise, it's fine to use the sessvars to figure out when to start from
                            $start = (int)(($old['ends'] > geoUtil::time()) ? $old['ends'] : geoUtil::time());
                        }
                        if ($this->get('renewal_length') > 0) {
                            //Normal way length will be set
                            $length = $this->get('renewal_length') * 86400;
                        } elseif ($new['renewal_length'] > 0) {
                            //old way, need to still check this in case it was
                            //renewed prior to when we started doing the above
                            $length = $new['renewal_length'] * 86400;
                        } elseif ($old['renewal_length'] > 0) {
                            //old way, need to still check this in case it was
                            //renewed prior to when we started doing the above
                            $length = $old['renewal_length'] * 86400;
                        } else {
                            //old way, need to still check this in case it was
                            //renewed prior to when we started doing the above

                            //if renewal length not set, use duration from listing
                            $length = $old['classified_length'] * 86400;
                            if (!$length) {
                                $length = $listing->duration;//last resort, get duration from listing directly
                            }
                        }
                        if ($length == 0) {
                            //renewing into an Unlimited Duration listing
                            $new_ends = 0;
                        } else {
                            $new_ends = $start + $length;
                        }
                        //the first time we activate this renewal, save the new ends time.  Then in the future, if it is undone then re-done,
                        //when it is re-done the expire will not be added onto a bunch.
                        $this->set('new_ends', $new_ends);
                        if (($old['ends'] > 0 && $old['ends'] < geoUtil::time()) || $reset_start_renew) {
                            //either the listing has already expired, or the setting
                            //to reset the start time for renewals is on,
                            //and this is the first time this renewal has been
                            //activated, so set the start date that will be used
                            //this and every subsequent time this item is acivated
                            $this->set('new_date', geoUtil::time());
                        }
                    }
                    $new['ends'] = $new_ends;
                    $new_date = $this->get('new_date');
                    if ($new_date) {
                        //restore the date, the first time this was activated the listing was already expired
                        //so the date was reset.
                        $new['date'] = $new_date;
                    }
                } elseif ($reset_start_upgrade) {
                    //this is an upgrade, and we should reset start and end times

                    //need to reset start time to current time of first activation
                    $new_date = $this->get('new_date');
                    if (!$new_date) {
                        //only do it first time activated, in order
                        //to prevent resetting of everything if this
                        //order item is much later deactivated/activated
                        $new_date = geoUtil::time();
                        $this->set('new_date', $new_date);
                    }
                    $new['date'] = $new_date;
                    if ($old['ends'] > $old['date']) {
                        //reset the end date as well as long as old start and end times
                        //aren't messed up.
                        $new_ends = $this->get('new_ends');
                        if (!$new_ends) {
                            //calculate the new ends
                            //Note: Do NOT change this to use the classified_duration, as it is not
                            //accurate.  For example, there are many scenarios where
                            //using a duration will result in the end date
                            //actually being less than it currently is!

                            $length = $old['ends'] - $old['date'];
                            $start = $new_date;
                            $new_ends = $start + $length;
                            //the first time we activate this upgrade, save the new ends time.  Then in the future, if it is undone then re-done,
                            //when it is re-done the expire will not be added onto a bunch.
                            $this->set('new_ends', $new_ends);
                        }
                        $new['ends'] = $new_ends;
                    }
                }

                //Reset stuff that needs to be reset..
                //NOTE: attached items DO specify when something is off if it is a renewal by setting the session_variable for that
                //item to 0.  (This is different behavior than if an upgrade)
                $new['expiration_notice'] = 0;
                $new['expiration_last_sent'] = 0;

                //save the changes to new session vars
                self::_saveSessionVarsDiff($this, $new);
            }

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
            $vars['liveChanged'] = $liveChanged;

            if ($this->get('renew_upgrade') == self::upgrade && $listing->item_type == 2) {
                //active bidding on an auction doesn't touch the sessvars, so if Upgrading an auction, make sure not to overwrite bid info with older SV data
                //just unset any relevant fields here, so that their current values don't change in _insertListingFromSessionVars()
                if ($vars['auction_minimum']) {
                    unset($vars['auction_minimum']);
                }
                if ($vars['minimum_bid']) {
                    unset($vars['minimum_bid']);
                }
                if ($vars['current_bid']) {
                    unset($vars['current_bid']);
                }
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
        trigger_error('DEBUG CART: Here');
        return 'Listing Renewal/Upgrade';
    }


    public static function getParentTypes()
    {
        trigger_error('DEBUG CART: Here');
        //this is main order item, no parent types
        //return array(0, 'classified', 'auction', 'dutch_auction');
        return array ();
    }

    protected static function _getCost()
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();
        if (!geoMaster::is('site_fees')) {
            return 0;
        }
        $cost = 0;
        if ($cart->item->renew_upgrade == self::upgrade) {
            //upgrade, there is no cost
            return $cost;
        }
        //get session vars with session vars specific to this item on top
        $session_variables = parent::_getSessionVarsFromListing($cart->item->get('listing_id'), false);
        $session_variables = array_merge($session_variables, $cart->item->get('session_variables', array()));
        if ($cart->db->get_site_setting('always_use_user_price_plan_when_renewing')) {
            if ($cart->item->get('item_type') == 1) {
                $planId = $cart->user_data['price_plan_id'];
            } else {
                $planId = $cart->user_data['auction_price_plan_id'];
            }

            $cart->setPricePlan($planId, $session_variables['category']);

            //do we need to set for item as well?
            if (isset($cart->item) && $cart->item->getType() == self::type) {
                //yes we do! (set it to whatever we just derived in the cart)
                $cart->item->setPricePlan($cart->price_plan['price_plan_id']);
            }
        }
        switch ($cart->price_plan['charge_per_ad_type']) {
            case 1:
                //get the charge based on the price field
                $sql = "SELECT `renewal_charge` FROM " . geoTables::price_plans_increments_table . " WHERE 
					`price_plan_id` = ? AND `category_id` = ? AND `low` <= ? AND item_type = ? ORDER BY `low` DESC LIMIT 1";
                //TODO: Remove this once we have changed it so that there is always at least one "increment" with 0 for the low.
                $price = (floatval($session_variables["price"]) > 0) ? floatval($session_variables["price"]) : 0.01;
                $query_data = array(
                    $cart->price_plan['price_plan_id'],
                    ((isset($cart->price_plan['category_id']) && $cart->price_plan['category_id']) ? $cart->price_plan['category_id'] : 0),
                    $price,
                    $cart->item->get('item_type')
                );
                $increment_result = $cart->db->Execute($sql, $query_data);
                if (!$increment_result || $increment_result->RecordCount() != 1) {
                    $cost = $cart->price_plan['ad_renewal_cost'];
                } else {
                    $show_increment = $increment_result->FetchRow();
                    $cost = $show_increment['renewal_charge'];
                }
                break;

            case 2:
                //get the charge based on duration

                if ($session_variables["renewal_length"]) {
                    $sql = "SELECT `renewal_charge` FROM " . geoTables::price_plan_lengths_table . "
							WHERE `length_of_ad` = ?
							AND `price_plan_id` = ? AND `category_id` = ?";

                    $query_data = array(
                        $session_variables["renewal_length"],
                        $cart->price_plan['price_plan_id'],
                        ((isset($cart->price_plan['category_id']) && $cart->price_plan['category_id']) ? $cart->price_plan['category_id'] : 0)
                    );
                    $length_result = $cart->db->Execute($sql, $query_data);
                    if (!$length_result || $length_result->RecordCount() != 1) {
                        $cost = $cart->price_plan['ad_renewal_cost'];
                    } else {
                        $show_length_cost = $length_result->FetchRow();
                        $cost = $show_length_cost['renewal_charge'];
                    }
                }
                break;
            case 0:
                //break ommited on purpose

            default:
                //normal, charge flat rate
                $cost = $cart->price_plan['ad_renewal_cost'];
                break;
        }

        return $cost;
    }

    protected static function _display_basic_duration_dropdown($renewal_cost = '-')
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();

        //Get session vars, with session vars for this item on top.
        $session_variables = parent::_getSessionVarsFromListing($cart->item->get('listing_id'), 0);
        $session_variables = array_merge($session_variables, $cart->item->get('session_variables', array()));

        //check for category specific dropdown lengths first
        $current_category = $cart->item->getCategory();
        do {
            $sql = "SELECT `length_of_ad`, `display_length_of_ad` FROM " . geoTables::price_plan_lengths_table . " WHERE `category_id` = ? AND `price_plan_id` = 0 ORDER BY `length_of_ad` asc";
            $category_duration_result = $cart->db->Execute($sql, array($current_category));
            if (self::$_debug_renewals) {
                echo $sql . "<br />\n";
            }
            if (!$category_duration_result) {
                trigger_error('ERROR SQL RENEW: Sql: ' . $sql . ' Error msg: ' . $cart->db->ErrorMsg());
                return false;
            } elseif ($category_duration_result->RecordCount() == 0) {
                //get parent category
                $sql = "SELECT `parent_id` FROM " . geoTables::categories_table . " WHERE `category_id` = ?";
                $parent_result = $cart->db->Execute($sql, array($current_category));
                if (self::$_debug_renewals) {
                    echo $sql . "<br />\n";
                }
                if (!$parent_result) {
                    trigger_error('ERROR SQL RENEW: Sql: ' . $sql . ' Error msg: ' . $cart->db->ErrorMsg());
                    return array();
                } elseif ($parent_result->RecordCount() == 1) {
                    $show_parent = $parent_result->FetchRow();
                    $current_category = $show_parent['parent_id'];
                } else {
                    return array();
                }
            }
        } while (($current_category != 0) && ($category_duration_result->RecordCount() == 0));

        if ($category_duration_result->RecordCount() > 0) {
            $dropdown = array();
            while ($show_durations = $category_duration_result->FetchRow()) {
                $dropdown[] = array(
                    'length_of_ad' => $show_durations['length_of_ad'],
                    'selected' => (($session_variables["classified_length"]  == $show_durations['length_of_ad']) ? 1 : 0),
                    'display_length_of_ad' => $show_durations['display_length_of_ad'],
                    'display_amount' => ''
                );

                $cart->site->duration_prices[$show_durations['length_of_ad']] = $renewal_cost;
            }
            return $dropdown;
        } else {
            $sql = "SELECT `display_value`, `numeric_value` FROM " . geoTables::choices_table . " WHERE `type_of_choice` = 1
			  AND `language_id`=" . $cart->session->getLanguage() . " ORDER BY `numeric_value`";
            $duration_result = $cart->db->Execute($sql);
            if (!$duration_result) {
                trigger_error('ERROR SQL RENEW: Sql: ' . $sql . ' Error msg: ' . $cart->db->ErrorMsg());
                return array();
            } elseif ($duration_result->RecordCount() > 0) {
                $dropdown = array();
                while ($show_durations = $duration_result->FetchRow()) {
                    $dropdown[] = array(
                        'length_of_ad' => $show_durations['numeric_value'],
                        'selected' => (($session_variables["classified_length"] == $show_durations['numeric_value']) ? 1 : 0),
                        'display_length_of_ad' => $show_durations['display_value'],
                        'display_amount' => $cart->site->messages[546]
                    );
                    $cart->site->duration_prices[$show_durations['numeric_value']] = $renewal_cost;
                }
                return $dropdown;
            }
            return array();
        }
    }

    function _stopScript()
    {
        trigger_error('DEBUG CART: Here');
        trigger_error('DEBUG TRANSACTION: listing_renew_upgrade:_stopScript()  ------  End of process listing_renew_upgrade payment!');
        //do all normal end of app stuff
        $listing = geoListing::getListing($this->get('listing_id'));
        if (is_object($listing) && $listing->isLocked()) {
            $listing->setLocked(false);
        }
        require GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }
    public static function geoCart_initSession_new($call_children = null, $item_type = null)
    {
        trigger_error('DEBUG CART: Here');
    }
    public static function geoCart_initSession_update()
    {
        trigger_error('DEBUG CART: Here');
    }
    public static function geoCart_initSteps($allPossible = false)
    {
        trigger_error('DEBUG CART: Here');
    }
    public static function geoCart_initItem_forceOutsideCart()
    {
        trigger_error('DEBUG CART: Here');
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
        trigger_error('DEBUG CART: Here');
        return true; //this item has stuff to display on other_details step.
    }

    public static function geoCart_other_detailsCheckVars()
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();
        if ($cart->main_type != self::type) {
            //not right type, so not concerned about this one.
            return ;
        }

        $cart->site->page_id = 56;
        $cart->site->get_text();
        if (!self::_checkWithinPeriod()) {
            trigger_error('ERROR CART: Listing not valid,outside of renew/upgrade period!');
            $cart->deleteProcess();
            $cart->current_step = $cart->cart_variables['step'] = 'cart';
            $cart->addError();
            return false;
        }
        $listing_id = $cart->item->get('listing_id');
        $listing = geoListing::getListing($listing_id);
        if (!is_object($listing)) {
            trigger_error('ERROR CART: Listing not valid!  Can\'t renew with no listing to renew!');
            $cart->deleteProcess();
            $cart->current_step = $cart->cart_variables['step'] = 'cart';
            $cart->addError();
            return false;
        }
        //Get session vars, with session vars for this item on top.
        $session_variables = parent::_getSessionVarsFromListing($cart->item->get('listing_id'), 0);
        $session_variables = array_merge($session_variables, $cart->item->get('session_variables', array()));

        if ($cart->item->renew_upgrade == self::renewal) {
            $length = (isset($_POST['c']["renewal_length"])) ? intval($_POST['c']["renewal_length"]) : 0;
            //NOTE: 0 is now a valid value for $length

            if ($listing->price_applies == 'item') {
                $quantity = (isset($_POST['c']['renewal_quantity'])) ? (int)$_POST['c']['renewal_quantity'] : 0;
                if (!$quantity) {
                    $cart->addError()
                        ->addErrorMsg('quantity', $cart->site->messages[502139]);
                    return;
                }
                $session_variables['auction_quantity'] = $quantity;
            }

            //check to see if chosen and cost of renewal
            if (($cart->price_plan['charge_per_ad_type'] == 2) && ($cart->price_plan['type_of_billing'] == 1)) {
                //pull price plan specific
                $cat_id = ($cart->price_plan['category_id']) ? intval($cart->price_plan['category_id']) : 0;
                $sql = "SELECT count(`length_id`) as count FROM " . geoTables::price_plan_lengths_table . " WHERE
					`price_plan_id` = {$cart->price_plan['price_plan_id']} AND `category_id` = {$cat_id} AND `length_of_ad` = $length";

                $length_result = $cart->db->GetRow($sql);
                if ($length_result === false) {
                    $basicCheck = true;
                } elseif (!isset($length_result['count']) || $length_result['count'] == 0) {
                    //duration selected is not found
                    $cart->addError()
                        ->addErrorMsg('choose', $cart->site->messages[836]);
                    $cart->site->error_variables["choose"] = $cart->site->messages[836];
                    return;
                }
            }
            if ($cart->price_plan['charge_per_ad_type'] != 2 || $basicCheck) {
                //make sure it's a valid selection
                self::_display_basic_duration_dropdown();
                if (!isset($cart->site->duration_prices[$length])) {
                    $cart->addError()
                        ->addErrorMsg('choose', $cart->site->messages[836]);
                    $cart->site->error_variables["choose"] = $cart->site->messages[836];
                    return;
                }
            }

            $session_variables['classified_length'] = $session_variables['renewal_length'] = $length;
            //save it too
            $cart->item->set('renewal_length', $length);
        }
        $cart->site->session_variables = $session_variables;
        //load all children check vars
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);

        if ($cart->item->renew_upgrade == self::upgrade) {
            //if upgrade, make sure we do not have any session vars that are false, blank, null, etc since
            //upgrades should ONLY be adding stuff, not specifying what stuff is not added...
            foreach ($cart->site->session_variables as $key => $value) {
                if (!$value) {
                    //upgrades only save things being added in session vars
                    unset($cart->site->session_variables[$key]);
                }
            }
        }
        //save session vars
        parent::_saveSessionVarsDiff($cart->item, $cart->site->session_variables);

        if (($cart->item->get('renew_upgrade') == self::upgrade && count($cart->item->get('session_variables')) > 0) || $cart->item->get('renew_upgrade') == self::renewal) {
            //if an upgrade, and there is stuff in session variables (which means theres stuff being upgraded), or if this is a renewal,
            //then set cost and proceed.
            $cart->item->setCost($cart->item->_getCost());
        } else {
            //Error!  Nothing upgraded or renewed!
            $cart->addError();
            $cart->addErrorMsg('renew_upgrade', $cart->site->messages[836]);
        }

        //do not call save form variables, as that does not take differences only
    }

    public static function geoCart_other_detailsProcess()
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();
        if ($cart->main_type != self::type) {
            //not right type, so not concerned about this one.

            return ;
        }

        $cart->item->setCost($cart->item->_getCost());
        //But children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
        self::saveFormVariables();
    }

    /**
     * Returns data to be displayed on listing cost and features section
     *
     * @return array of data that is processed and used to display the listing cost box
     */
    public static function geoCart_other_detailsDisplay()
    {
        trigger_error('DEBUG CART: Here');
        $cart = geoCart::getInstance();
        //See if this is a classified or not (as opposed to auction).
        if ($cart->main_type != self::type) {
            //not classified, so not concerned about this one.
            return ;
        }

        //this is classified, figure out what to display.
        $tpl = new geoTemplate();
        $return = array (
            'checkbox_name' => 'classified', //no checkbox display
            'title' => 'Classified',
            'display_help_link' => '',//if 0, will display no help icon thingy
            'price_display' => '',
            //templates - over-write mini-template to do things like set margine or something:
            'entire_box' => '',
            'left' => '',
            'right' => '',
            'checkbox' => '',
            'checkbox_hidden' => ''
        );

        $cart->site->page_id = 56;
        $cart->site->get_text();

        $cart->site->msgs['a_index'] = "7&amp;r=" . intval($cart->site->renew_upgrade);
        $cart->site->msgs['page_title'] = '';//$cart->site->messages[2497];//err no header (yet)?
        $cart->site->msgs['page_desc'] = '';//$cart->site->messages[2498];//no overall page desc (yet)?
        $cart->site->msgs['choices_section_title'] = $cart->site->messages[792];
        $cart->site->msgs['choices_section_desc'] = $cart->site->messages[793];

        $type = $cart->item->renew_upgrade;

        if (!($cart->site->classified_id && $cart->user_data['id'] && $cart->site->price_plan_id)) {
            //listing id, user id, or price plan id not known
            //12/15/16: used to also check for category id here, but that gets in the way of some of the newer category stuff, and I think can go away without breaking anything (I hope...)
            //echo 'test: '.$cart->site->classified_id .' : '. $cart->user_data['id'] .' : '. $cart->site->category_id .' : '. $cart->site->price_plan_id;
            return false;
        }
        // RENEWAL AND UPGRADE USE ALMOST SAME CODE

        //SHOW AD RENEWAL COST AND ADDITIONAL FEATURES
        //check to see if in the last few days of the ad to display renewal
        $i = ($type == self::renewal) ? 1412 : 1413;

        $cart->site->msgs['transaction_details_header'] = $cart->site->messages[$i];

        //error for top
        $cart->site->error_variables['entire_box'] = $cart->site->error_variables['choose'];
        $listing = geoListing::getListing($cart->item->get('listing_id'));
        $entire_box = ' ';
        if ($type == self::renewal) {
            //renewal only: show box for renewal cost and duration of listing
            $cost = self::_getCost();
            $tpl = new geoTemplate('system', 'order_items');

            if (($cart->price_plan['type_of_billing'] == 1) || ($cart->price_plan['type_of_billing'] == 2)) {
                //if ends_for_periodCheck is set, this is an auction renewal -- that value is the 'ends' from the original listing
                //in that case, $listing here is the new, copied listing, and $listing->ends will always be Now
                //if we didn't set that value earlier, this is a classified, so go ahead and check against $listing->ends
                $endTime = ($cart->item->get('ends_for_periodCheck')) ? $cart->item->get('ends_for_periodCheck') : $listing->ends;

                if (!is_numeric($endTime)) {
                    //if we still don't have an end time, something's wrong
                    return false;
                }
                $renew_cutoff = ($endTime - ($cart->db->get_site_setting('days_to_renew') * 86400));
                $renew_postcutoff = ($endTime + ($cart->db->get_site_setting('days_to_renew') * 86400));
                if (($cart->db->get_site_setting('days_to_renew')) && (geoUtil::time() > $renew_cutoff) && (geoUtil::time() < $renew_postcutoff)) {
                    $tpl->assign('show_dropdown', 1);
                    $renewal_dropdown = array();
                    $renewable = 1;
                    //get price plan specifics
                    //fee based billing
                    //display the ad cost for renewing the ad
                    if (!geoMaster::is('site_fees')) {
                        $display_amount = '';
                    } elseif ($cart->price_plan['charge_per_ad_type'] != 2) {
                        $display_amount = geoString::displayPrice($cost, false, false, 'cart');
                    } else {
                        //get the list of costs
                        $display_amount = '-';
                    }

                    //listing  renewal duration dropdown
                    $tpl->assign('text_1399', geoString::fromDB($cart->site->messages[1399]));
                    if (($cart->price_plan['charge_per_ad_type'] == 2) && ($cart->price_plan['type_of_billing'] == 1)) {
                        //pull price plan specific
                        $cat_id = ($cart->price_plan['category_id']) ? intval($cart->price_plan['category_id']) : 0;
                        $sql = "SELECT `renewal_charge`,`length_of_ad`, `display_length_of_ad` FROM " . geoTables::price_plan_lengths_table . " WHERE
							`price_plan_id` = {$cart->price_plan['price_plan_id']} AND `category_id` = {$cat_id} ORDER BY `length_of_ad` ASC";

                        $length_result = $cart->db->Execute($sql);
                        if (!$length_result) {
                            $renewal_dropdown = self::_display_basic_duration_dropdown();
                        } elseif ($length_result->RecordCount() > 0) {
                            $length = intval($cart->site->session_variables["classified_length"]);
                            if (!$length) {
                                $length = intval($listing->duration);
                            }
                            while ($show_lengths = $length_result->FetchRow()) {
                                $this_display_amount = geoString::displayPrice($show_lengths['renewal_charge'], false, false, 'cart');
                                $selected = ($length == $show_lengths['length_of_ad']) ? 1 : 0;
                                if (!geoMaster::is('site_fees')) {
                                    $this_display_amount = '';
                                } else {
                                    if ($selected || $display_amount == '-') {
                                        $display_amount = $this_display_amount;
                                    }
                                }
                                $renewal_dropdown[] = array(
                                    'length_of_ad' => $show_lengths['length_of_ad'],
                                    'selected' => $selected,
                                    'display_length_of_ad' => $show_lengths['display_length_of_ad'],
                                    'display_amount' => $this_display_amount
                                );
                                $cart->site->duration_prices[$show_lengths['length_of_ad']] = $this_display_amount;
                            }
                        } else {
                            $renewal_dropdown = self::_display_basic_duration_dropdown($display_amount);
                        }
                    } else {
                        $renewal_dropdown = self::_display_basic_duration_dropdown($display_amount);
                    }
                    $tpl->assign('display_amount', $display_amount);
                    $tpl->assign('renew_dropdown', $renewal_dropdown);
                    $tpl->assign('text_794', geoString::fromDB($cart->site->messages[794]));
                    $tpl->assign('text_795', geoString::fromDB($cart->site->messages[795]));

                    if ($listing->price_applies == 'item') {
                        //quantity matters...
                        $tpl->assign('price_applies', 'item');

                        $remaining = $listing->quantity_remaining;
                        if ($remaining <= 0) {
                            //start it at starting quantity
                            $remaining = $listing->quantity;
                        }

                        //the quantity
                        $tpl->assign('quantity_remaining', $remaining);
                    }

                    //make price update using javascript if a new selection dropdown is selected.
                    $tpl->assign('duration_array', $cart->site->duration_prices);
                } else {
                    $tpl->assign('show_dropdown', 0);

                    if ($cart->db->get_site_setting('days_to_renew')) {
                        $tpl->assign('text_799_or_830', $cart->site->messages[799]);
                    } else {
                        $tpl->assign('text_799_or_830', $cart->site->messages[830]);
                    }
                }
            }
            $entire_box = $tpl->fetch('listing_renew_upgrade/other_details.item_box.tpl');
        }
        $return['entire_box'] = $entire_box;
        if ($type == self::renewal) {
            $return ['page_title1'] = $cart->site->messages[500434];
            $return ['page_title2'] = $cart->site->messages[500435];
            $return ['page_desc'] = $cart->site->messages[500436];
        } else {
            $return ['page_title1'] = $cart->site->messages[500429];
            $return ['page_title2'] = $cart->site->messages[500430];
            $return ['page_desc'] = $cart->site->messages[500431];
        }

        $return ['submit_button_text'] = $cart->site->messages[500432];
        $return ['cancel_text'] = $cart->site->messages[500433];

        return $return;
    }

    public static function geoCart_other_detailsLabel()
    {
        $cart = geoCart::getInstance();
        if (isset($cart->item) && $cart->item->get('renew_upgrade') == self::upgrade) {
            return $cart->site->messages[500509];
        }
        //if renewal, or not known:
        return $cart->site->messages[500508];
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
        $renew_upgrade = $item->get('renew_upgrade');
        $type = ($renew_upgrade == self::renewal) ? 'Renewal' : 'Upgrade';
        $info .= geoHTML::addOption('Item Type', 'Listing ' . $type);

        //if upgrade, show what was upgraded
        if ($renew_upgrade == self::upgrade) {
            $upgradeNames = array();

            //get order this item is attached to
            $order = $item->getOrder();
            //get all items attached to that order
            $allItems = $order->getItem();
            //find out which of those items are children of this item
            $children = array();

            foreach ($allItems as $attached) {
                if (is_object($attached) && is_object($attached->getParent())) {
                    $p = $attached->getParent();
                    if ($p->getId() == $item->getId()) {
                        //$attached is a child of this item
                        $upgradeNames[] = $attached->friendlyName();
                    }
                }
            }

            foreach ($upgradeNames as $name) {
                $info .= geoHTML::addOption('Attached Upgrade', $name);
            }
        } else {
            //if renewed, display renewal period
            if ($item->get('listing_copy_id')) {
                $info .= geoHTML::addOption('Original Auction ID', "# " . $item->get('listing_copy_id'));
            }
            if ($item->get('renewal_length')) {
                $info .= geoHTML::addOption('Renewal Length', $item->get('renewal_length') . ' days');
            } else {
                $session_variables = $item->get('session_variables');
                $info .= geoHTML::addOption('Renewal Length', $session_variables['renewal_length'] . ' days');
            }
            if ($item->get('new_date')) {
                $info .= geoHTML::addOption('New start date', date('r', $item->get('new_date')));
            }
            if ($item->get('new_ends')) {
                $info .= geoHTML::addOption('New end date', date('r', $item->get('new_ends')));
            }
        }

        parent::$_type = self::type;
        $info .= parent::adminItemDisplay($item_id);
        return $info;
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
        //this will be "cart", or the action attempting to be run.
        //if it's cart, then it's the current item/step/action being interupted.
        $action = $vars['action'];
        //The step
        $step = $vars['step'];
        $cart = geoCart::getInstance();

        if ($action == 'interrupted') {
            //this is the one being interrupted

            $listingId = ($cart->item) ? $cart->item->get('listing_id') : false;
        } elseif ($step == 'my_account_links') {
            //in my account links, need to return something short
            if ($cart->item && $cart->item->get('renew_upgrade') == self::renewal) {
                //Renew listing
                return $cart->site->messages[500641];
            } else {
                //Upgrade Listing
                return $cart->site->messages[500642];
            }
        } else {
            $listingId = intval($_GET['listing_id']);
        }
        $title = '';//set default to empty string
        if ($listingId) {
            $listing = geoListing::getListing($listingId);
            $title = "( $listingId - " . geoString::fromDB($listing->title) . " )";
        }
        //text: "editing existing listing"
        return $cart->site->messages[500395] . ' ' . $title;
    }

    /**
     * Figures out if the renewal setting to not allow downgrade of extras is turned on.
     * If it is, and it is a classified, and live, alter the $return as necessary
     * to block down-grading the extra (un-checking)
     *
     * this is meant to be used by listing extras in geoCart_other_detailsDisplay
     * see the bolding extra for example of usage.  Also used in getDisplayDetails
     * to remove the delete button when needed
     *
     * @param array $return The return array to possibly alter
     * @param string|bool $extra The column name for the extra such as "bolding",
     *   or boolean true to assume the live listing already has extra enabled
     * @return array The return var possibly altered if needed.
     * @since Version 6.0.4
     */
    public function checkNoDowngrade($return, $extra)
    {
        $listing = geoListing::getListing($this->get('listing_id'));

        if (!$listing) {
            //could not get details of listing...
            return $return;
        }

        if ($extra !== true) {
            //since can be used by 3rd party addon, so just a failsafe to clean name:
            $extra = preg_replace('/[^a-zA-Z_0-9]*/', '', $extra);
            if (!$extra) {
                //extra no good!
                return $return;
            }
        }

        if ($listing->item_type == 1 && $this->get('renew_upgrade') == self::renewal && ($extra === true || $listing->$extra) && $listing->live) {
            //if listing is still live, and it's a classified ad, and this is
            //a renewal, and the specified extra is currently "on" for the live listing

            //NOTE:  we check the listing directly for whether the extra is used or not since session
            //vars would force extra to be used if user checks box, would not be able to edit renewal to un-check.
            $planItem = $this->getPlanItem();
            if ($planItem->get('no_live_downgrade')) {
                //setting set to not allow downgrading a live listing, so force checkbox to be che
                if (isset($return['canDelete'])) {
                    //being called by getDisplayDetails so make changes for that
                    $return ['canDelete'] = false;
                } else {
                    //being called by other page thingy
                    $return ['checked'] .= ' disabled="disabled" readonly="readonly"';
                    $return ['checkbox_hidden'] = "<input name='{$return['checkbox_name']}' value='1' type='hidden' />";
                }
            }
        }
        return $return;
    }
}
