<?php

//order_items/listing_edit.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.07.0-6-gc04a964
##
##################################

require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';

class listing_editOrderItem extends _listing_placement_commonOrderItem
{

    /**
     * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
     *
     * @var string
     */
    protected $type = "listing_edit";

    /**
     * Optional, use this as a hassle-free way to determine the type without having to hard-code
     * the type everywhere else, instead use self::type
     *
     */
    const type = 'listing_edit';

    /**
     * Needs to be the order that this item will be processed.
     *
     * @var int
     */
    protected $defaultProcessOrder = 10;
    /**
     * Optional, use this as a hassle-free way to determine the process order without having to hard-code
     * the # everywhere else, instead use self::defaultProcessOrder
     *
     */
    const defaultProcessOrder = 10;

    private $_listingID = 0;

    private static $_builtInSteps = array (
        'category','media','details','continue','select'
    );

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
        $session_variables = $this->get('session_variables');
        if (!isset($session_variables['classified_title'])) {
            $session_variables = parent::_getSessionVarsFromListing($this->get('listing_id'));
        }
        $title = "Edit Listing: " . $session_variables['classified_title'];
        $titleHover = $session_variables['classified_title'];
        $listing_id = $this->get('listing_id');

        if (strlen($title) > 40) {
            $title = geoString::substr($title, 0, 37) . '...';
        }
        if ($listing_id) {
            $titleHover .= " (Listing # $listing_id)";
        }
        $title = "<span title=\"$titleHover\">$title</span>";

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
        if (!$item_id) {
            return '';
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() != self::type) {
            return '';
        }
        self::fixStepLabels();
        //Call children and let them display info about themselves as well
        $children = geoOrderItem::getChildrenTypes(self::type);
        $html .= geoOrderItem::callDisplay('adminItemDisplay', $item_id, '', $children);

        $item = geoOrderItem::getOrderItem($item_id);

        //just TEMPORARILY, set status to pending so we can get session vars without this
        $current_status = $item->getStatus();
        if ($current_status == 'active') {
            $item->setStatus('temp_disable');
        }
        //DO get archived one if needed, so that admin can still view info long after listing is archived
        $old_vars = parent::_getSessionVarsFromListing($item->get('listing_id'), true, true);
        //set status back
        $item->setStatus($current_status);

        $new_vars = $item->get('session_variables');
        $html .= geoHTML::addOption('Listing:', $item->get('listing_id') . ' - ' . $old_vars['classified_title']);

        $html .= "<table><tr><td colspan=\"3\" style='text-align: center;'>Changes in this edit are shown below</td></tr>";
        $html .= "<tr><td><strong>Field Name</strong></td><td><strong>Old Value</strong></td><td><strong>New Value</strong></td></tr>";
        foreach ($new_vars as $key => $value) {
            if (trim($old_vars[$key]) === trim($value)) {
                //don't show if no change
                //continue;
            }
            if ($key == 'description' || strlen($old_vars[$key]) > 100 || strlen($value) > 100) {
                $old_vars[$key] = '<textarea disabled="disabled" style="overflow: auto; height: 200px; width: 450px;">' . $old_vars[$key] . '</textarea>';
                $value = '<textarea disabled="disabled" style="overflow: auto; height: 200px; width: 450px;">' . $value . '</textarea>';
            }
            if (is_array($old_vars[$key])) {
                $old_vars[$key] = '<pre>' . print_r($old_vars[$key], 1) . '</pre>';
            }
            if (is_array($value)) {
                $value = '<pre>' . print_r($value, 1) . '</pre>';
            }
            $class = 'row_color' . (($i++ % 2) + 1);
            $html .= "<tr class='$class'><td>" . $key . "</td><td>" . $old_vars[$key] . "</td><td>" . $value . "</td></tr>";
        }
        $html .= "</table>";


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
        return "Edit Listing";
    }

    /**
     * Optional.
     * Used: in geoCart::initItem()
     *
     * Used to initialize an item that already exists.
     * @return boolean Need to return true if it's ok to restore item, false otherwise
     */
    public function geoCart_initItem_restore()
    {
        $cart = geoCart::getInstance();
        parent::$_type = self::type;
        $cart->site->classified_id = $cart->site->listing_id = $this->get('listing_id');

        $cart->setPricePlan($this->getPricePlan(), $this->getCategory());

        //merge session vars with our changes
        $cart->site->session_variables = array_merge(parent::_getSessionVarsFromListing($this->get('listing_id')), $this->get('session_variables'));

        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->category_id = $this->getCategory();
        $cart->site->price_plan_id = $cart->price_plan['price_plan_id'];

        if (!$this->isEditable()) {
            $listing = geoListing::getListing($cart->site->listing_id);
            if (is_object($listing)) {
                $listing->setLocked(false);
            }
            if ($cart->getAction() != 'delete') {
                //only return false if the action is not to be deleted.
                //make sure text is there
                $cart->site->messages = $cart->db->get_text(true, 10202);
                $cart->addErrorMsg('listing_edit', $cart->site->messages[500606]);
                return false;
            }
        }
        return true;
    }

    /**
     * Used: in geoCart::initItem()
     *
     * initialize a new order item of type listing_edit
     *
     * @return boolean true if ok to create new item, otherwise false
     */
    public function geoCart_initItem_new($item_type = null)
    {
        $cart = geoCart::getInstance();
        //listing id we want to edit
        $listingID = intval($_REQUEST['listing_id']);
        if (!$listingID) {
            $cart->addErrorMsg('listing_edit', "ERROR: Edit: Invalid ID");
            return false;
        }
        parent::$_type = self::type;
        //make sure text is there for errors
        $cart->site->messages = $cart->db->get_text(true, 10202);

        //get listing and its vars
        $listing = geoListing::getListing($listingID);

        //make sure it's a good listing
        if (!is_object($listing) || $listingID !== $listing->id) {
            //not valid listing
            $cart->addErrorMsg('listing_edit', $cart->site->messages[500607]);
            return false;
        }
        //check for locks on this listing
        if ($listing->isLocked()) {
            //already altering listing, can't edit
            $cart->addErrorMsg('listing_edit', $cart->site->messages[500610]);

            return false;
        }

        //not locked so save listing ID
        $this->set('listing_id', $listing->id);
        $this->save();

        //make sure user can edit this listing
        if (!$this->isEditable()) {
            $cart->addErrorMsg('listing_edit', $cart->site->messages[500608]);
            return false;
        }

        //did admin choose to allow user-editing of live auctions?
        //NOTE: cart->item->get('adminEdit') is set during this->isEditable() above, when appropriate
        if (!$cart->item->get('adminEdit') && $listing->item_type == 2 && $listing->live == 1) {
            //this is a live auction -- is a non-admin allowed to edit it?
            if ($cart->db->get_site_setting('edit_begin')) {
                //no live auctions may be edited
                //(NOTE: edit_begin setting uses inverted logic -- will be true if editing is disallowed)
                $cart->addErrorMsg('listing_edit', $cart->site->messages[500609]);
                return false;
            } elseif ($listing->current_bid > 0) {
                //this auction already has a bid, so it MAY NOT be edited...
                if ($listing->buy_now_only == 1 && $cart->db->get_site_setting('edit_begin_bno') == 1) {
                    //...UNLESS it is a buy-now-only auction and BNOs are set to always allow editing
                    //(don't throw an error here)
                } else {
                    $cart->addErrorMsg('listing_edit', $cart->site->messages[500609]);
                    return false;
                }
            }
        }


        //lock listing
        $listing->setLocked();

        //get original session vars
        $cart->site->session_variables = parent::_getSessionVarsFromListing($listing->id, false, true);



        //save listing data
        $this->setCategory($listing->category);

        $price_plan = (int)$listing->price_plan_id;
        if ($price_plan && !geoPlanItem::isValidPricePlanFor($listing->seller, $price_plan)) {
            //not valid price plan for this user
            $price_plan = 0;
        }
        if (!$price_plan) {
            $item_type = $listing->item_type;
            $setting = ($item_type == 1) ? 'price_plan_id' : 'auction_price_plan_id';
            $price_plan = (int)$cart->user_data[$setting];
        }
        $this->setPricePlan($price_plan);

        $this->set('original_category', $listing->category);
        $this->set('live', $listing->live);
        $this->set('item_type', $listing->item_type);
        $this->set('seller', $listing->seller);
        //no changes to session vars yet, session vars starts out empty array
        $this->set('session_variables', array());

        if (!$cart->site->session_variables['location']) {
            //the saved session vars for this listing don't have the new way of doing location
            //get the new array out of the db and set it into sessvars so that the defaults appear correctly
            $regions = geoRegion::getRegionsForListing($listing->id);
            if ($regions) {
                $cart->site->session_variables['location'] = $regions;
                //in this special case, we're force-pushing an edit to the location sessvar, so go ahead and set it on the item
                //the location will show up as changed on the first edit of the item, but that's okay
                $this->set('session_variables', array('location' => $regions));
            }
        }

        //save cart data
        $cart->site->classified_id = $this->get('listing_id');
        $cart->site->classified_user_id = $cart->user_data['id'];
        $cart->site->category_id = $this->getCategory();
        $cart->site->price_plan_id = $cart->price_plan['price_plan_id'];
        $this->save();

        return true;
    }


    /**
     * Required.
     * Used: in geoCart::initSteps() (and possibly other locations)
     *
     * If this order item has any of it's own steps it wants to display or process as
     * part of the sell process, it needs to add them to the cart here, by getting an
     * instance of the cart, and $cart->addStep('item_name:step_name');.
     *
     * It also needs to call any children order items to do the same, as only parents
     * are called by the Cart system.
     *
     * Format of steps:
     * <ORDER_ITEM_NAME>:<STEP_NAME>
     *
     * Example:
     * listing_edit:details
     *
     * When the process gets to the step listing_edit:details, if $_REQUEST['process'] is
     * defined, then it will make the following static method calls:
     * listing_edit::<STEP_NAME>CheckVars(); - if return true, then:
     * listing_edit::<STEP_NAME>Process(); - if return true, then it will continue on to next step
     *
     * If $_REQUEST['process'] is NOT defined, or <STEP_NAME>CheckVars() or <STEP_NAME>Process()
     *  either return false, then it will call:
     * listing_edit::<STEP_NAME>Display();
     *
     * That display function is responsible for displaying the page, then including app_bottom.php,
     * then exiting.  If it does not exit, the system will display a site error.
     *
     * (Of course, above you would replace <STEP_NAME> with "details" if your step was "listing_edit:details")
     */
    public static function geoCart_initSteps($allPossible = false)
    {
        $cart = geoCart::getInstance();

        $anon = geoAddon::getUtil('anonymous_listing');
        if ($anon && !defined('IN_ADMIN')) {
            //anonymous addon enabled
            //find out if this is anonymous.
            //if it is, make user input password (unless this is admin)
            if ($anon->isAnonymous($cart->item->get('listing_id')) && !$cart->item->get('adminEdit')) {
                $cart->addStep(self::type . ':password');
            }
        }
        $cart->site->page_id = 31;
        $cart->site->get_text();
        $choices = array();
        if (self::categoryIsEditable($cart->item->get('listing_id'))) {
            $choices['category'] = $cart->site->messages[493];
        }
        $choices['details'] = $cart->site->messages[491];

        //figure out if we should show media step or not
        $children = geoOrderItem::getChildrenTypes(self::type);

        if (geoOrderItem::callDisplay('addMedia', null, 'bool_true', $children)) {
            $choices['media'] = $cart->site->messages[492];
        }

        /*
         * Expected each order item to return array:
         * array ( step => link text )
         */

        $moreChoices = geoOrderItem::callDisplay('listing_edit_getChoices', null, 'array', $children);
        foreach ($moreChoices as $childName => $data) {
            foreach ($data as $step => $link) {
                $choices[$step] = $link;
            }
        }

        $cart->item->set('choices', $choices);
        $step = $cart->item->get('edit_step');
        if (in_array($step, self::$_builtInSteps)) {
            switch ($cart->item->get('edit_step')) {
                case 'category':
                    if (self::categoryIsEditable($cart->item->get('listing_id'))) {
                        $cart->addStep(self::type . ':category');
                    }
                    break;
                case 'details':
                    $cart->addStep(self::type . ':details');
                    break;
                case 'media':
                    if (isset($choices['media'])) {
                        $cart->addStep(self::type . ':media');
                    }
                    break;
                default:
                    //see if it's one of the steps.
                    break;
            }
        } else {
            if (array_key_exists($step, $choices)) {
                $cart->addStep($step);
            }
        }

        $cart->addStep(self::type . ':select');

        $cart->item->save();
    }

    /**
     * find out if anonymous listings are allowed for this item type
     *
     * @return bool true if anonymous allowed, false otherwise
     */

    protected static function anonymousAllowed()
    {
        return ((geoAddon::getUtil('anonymous_listing')) ? true : false);
    }

    /**
     * Required for interface
     */
    public static function geoCart_initItem_forceOutsideCart()
    {
        //most need to return false.
        return false;
    }

    /**
     * Find out if this user may edit this listing
     * (Yes if he posted it or has admin rights)
     *
     * @return bool true if can edit listing, false otherwise
     */

    protected function isEditable()
    {
        $addon = geoAddon::getInstance();
        $listing = geoListing::getListing($this->get('listing_id'));

        $cart = geoCart::getInstance();
        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        $userID = $cart->user_data['id'];

        if (self::isAnonymous()) {
            //user not logged in
            /**
             * NOTE: Checking anonymous here is special case for edit listings.
             * Normally it would be blocked by cart at item creation stage when
             * it sees that anonymousAllowed returns false...
             *
             * In this case however, we do not know what listing is attempting
             * to be edited at time that anonymousAllowed function is called,
             * so we have to do extra checks here.
             */

            $anon = geoAddon::getUtil('anonymous_listing');
            if (!$anon xor !$anon->isAnonymous($cart->item->get('listing_id'))) {
                //this is not an anonymous listing -- kill the item and prompt for login
                geoOrderItem::remove($cart->item->getId());

                //force login page
                require_once(CLASSES_DIR . "authenticate_class.php");
                $encodedUri = (($cart->getAction() == 'new') ? Auth::generateEncodedVars() : 'a*is*cart');

                if (self::enforceAnonymous(null, $enforceUri)) {
                    include GEO_BASE_DIR . 'app_bottom.php';
                    exit;
                }
                return false;
            } else {
                //this IS an anonymous listing, but don't return yet
                //because we still need to do auction-start checks after this if-ladder
            }
        } elseif (!($listing->seller == $userID || $userID == 1 || geoAddon::triggerDisplay('auth_listing_edit', null, geoAddon::NOT_NULL))) {
            //this is not seller or admin
            return false;
        } elseif ($userID == 1 || geoAddon::triggerDisplay('auth_listing_edit', null, geoAddon::NOT_NULL)) {
            //this is an admin user
            $cart->item->set('adminEdit', 1);

            //set this one to inform the order to use this for the
            //need admin approval setting, instead of looking at the
            //plan item setting
            //(in other words, this is an admin doing the edit, so don't need to be approved by the admin)
            $cart->item->set('needAdminApproval', '0');
            $cart->item->save();

            return true; //return here instead of checking below, because admin can edit even if a bid has been made on an auction
        }

        $db = DataAccess::getInstance();

        if ($listing->item_type == 1 || ($listing->item_type == 2 && (($db->get_site_setting('edit_begin') == 0 && geoListing::bidCount($listing->id) == 0) || ($db->get_site_setting('edit_begin_bno') && $listing->buy_now_only)))) {
            //classified
            //or auction with no bids and editing enabled
            //or buy-now-only auction with editing enabled
            return true;
        }
        return false;
    }

    /**
     * Find out if the category of this listing may be edited
     * (No if there exist category-specific price plans)
     *
     * @return bool true if can edit category, false otherwise
     */
    protected static function categoryIsEditable($listingID)
    {
        //find out if this is an admin
        if (defined('IN_ADMIN') || geoSession::getInstance()->getUserId() == 1 || geoAddon::triggerDisplay('auth_listing_edit', null, geoAddon::NOT_NULL)) {
            //this is admin -- can always edit category
            return true;
        }

        //check for category-specific price plans
        $db = DataAccess::getInstance();

        //find price plan this listing was created under
        $sql = "select `price_plan_id` from " . geoTables::classifieds_table . " where id = " . $listingID;
        $pricePlanId = $db->GetOne($sql);
        if ($pricePlanId) {
            //find out if it has category specific price plans
            $sql = "select `category_price_plan_id` from " . geoTables::price_plans_categories_table . " where price_plan_id = " . $pricePlanId;
            $catPpId = $db->GetOne($sql);
            if ($catPpId) {
                //there are category specific prices in this price plan...cannot change category
                return false;
            } else {
                //category specific prices not being used for this price plan. OK to edit category.
                return true;
            }
        } else {
            //listing doesn't have a price plan...something's wrong
            return false;
        }
    }

    protected static function imagesEditable()
    {
        $cart = geoCart::getInstance();

        $category = $cart->item->getCategory();
        $price_plan = $cart->item->getPricePlan();
        $planItem = geoPlanItem::getPlanItem(self::type, $price_plan, $category);

        return ($planItem->get('max_uploads', 20));
    }


    /**
     * display category form
     *
     */

    public static function categoryDisplay($listing_types_allowed = null)
    {
        $cart = geoCart::getInstance();
        $id = $cart->item->get('listing_id');
        $listing = geoListing::getListing($id);
        if ($listing) {
            $item_type = $listing->item_type;
        } else {
            //fallback in case something doesn't work right
            //allow all categories
            $item_type = 'c.listing_types_allowed';
        }

        parent::$_type = self::type;
        $tpl_vars = parent::categoryDisplay($item_type);

        $view = geoView::getInstance();

        //set text that is specific to edit listings
        //500357 = "Edit My Listing"
        $tpl_vars['title1'] = $cart->site->messages[500357];
        //Cancel link text
        $tpl_vars['cancel_txt'] = $cart->site->messages[500358];

        $view->setBodyTpl('listing_edit/category_choose/links.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);
        self::fixStepLabels();
        $cart->site->display_page();
    }

    public static function categoryCheckVars($listing_types_allowed = null, $cat_id = 0)
    {
        $cart = geoCart::getInstance();
        $id = $cart->item->get('listing_id');
        $listing = geoListing::getListing($id);
        if ($listing) {
            $item_type = $listing->item_type;
        } else {
            //fallback in case something doesn't work right
            //allow all categories
            $item_type = '`listing_types_allowed`';
        }
        parent::$_type = self::type;
        parent::categoryCheckVars($item_type);
    }

    public static function categoryProcess()
    {
        $cart = geoCart::getInstance();

        //at this point, the setting checker should have set the cat id as a session var.
        $cat_id = $cart->item->get('terminal_category');

        $cart->item->setCategory($cat_id);

        $session_variables = $cart->item->get('session_variables', array());
        $session_variables['category'] = $cat_id;

        $session_variables = parent::_saveSessionVarsDiff($cart->item, $session_variables);
        $cart->site->session_variables = array_merge(parent::_getSessionVarsFromListing($cart->item->get('listing_id')), $session_variables);
    }


    /**
     *  details collection form
     */
    public static function detailsDisplay()
    {
        parent::$_type = self::type;
        //figure out what sell type
        $cart = geoCart::getInstance();
        if (is_object($cart->item)) {
            $cart->site->sell_type = $cart->item->get('item_type');
        }
        $view = geoView::getInstance();

        $tpl_vars = parent::detailsDisplay();

        if ($cart->site->sell_type == 2) {
            //find out if Buy Now is enabled
            $category = $cart->item->getCategory();
            $price_plan = $cart->item->getPricePlan();
            $planItem = geoPlanItem::getPlanItem('auction', $price_plan, $category);
            if ($planItem) {
                $tpl_vars['allow_buy_now'] = $planItem->get('allow_buy_now', 1);
                $tpl_vars['allow_buy_now_only'] = $planItem->get('allow_buy_now_only', 1);
                $tpl_vars['allow_reverse'] = $planItem->get('allow_reverse');
                $tpl_vars['allow_reverse_buy_now'] = $planItem->get('allow_reverse_buy_now');

                //figure out if there is a choice for auction type
                $choices = 0;
                if ($tpl_vars['field_config']['allow_standard'] && $tpl_vars['pricePlan']['buy_now_only']) {
                    //only one choice if allow standard and buy now only...  and template already knows
                    //what to do with it...
                    $choices++;
                } else {
                    if ($tpl_vars['field_config']['allow_standard']) {
                        //standard!
                        $choices++;
                    }
                    if ($tpl_vars['field_config']['allow_dutch']) {
                        //dutch!
                        $choices++;
                    }
                    if ($tpl_vars['allow_reverse']) {
                        //reverse!
                        $choices++;
                    }
                }
                $tpl_vars['auction_choices_count'] = $choices;
            }

            //store initial quantity (special case. See Bugzilla #1228 and implementation of "quantityDelta" throughout this file)
            if (is_object($cart->item)) {
                $cart->item->set('initialQuantity', $tpl_vars['session_variables']['auction_quantity']);
            }
        } elseif ($cart->site->sell_type == 1) {
            $tpl_vars['force_single_quantity'] = true;
        }

        //500365 = Edit My Listing
        $tpl_vars['txt1'] = $cart->site->messages[500365];
        //500366 = "Listing Details"
        $tpl_vars['title1'] = $cart->site->messages[500366];
        //500367 = ""
        $tpl_vars['desc1'] = $cart->site->messages[500367];
        //500368 = "Continue >>"
        $tpl_vars['submit_button_txt'] = $cart->site->messages[500368];
        //500369 = Cancel Edit image
        $tpl_vars['cancel_txt'] = $cart->site->messages[500369];
        $tpl_vars['listing_process_count_columns'] = $cart->db->get_site_setting('listing_process_count_columns');

        $view->setBodyTpl('listing_edit/listing_collect_details.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);
        self::fixStepLabels();
        $cart->site->display_page();
    }

    public static function detailsCheckVars($save_session_vars = null)
    {
        parent::$_type = self::type;
        $cart = geoCart::getInstance();
        if (is_object($cart->item)) {
            $cart->site->sell_type = $cart->item->get('item_type');
        }
        parent::detailsCheckVars('skipSave');
        //save session vars
    }

    public static function detailsProcess($noSetCost = null)
    {
        //call parent, tell it to not set price, since edit is free
        $cart = geoCart::getInstance();
        if (is_object($cart->item)) {
            $cart->site->sell_type = $cart->item->get('item_type');
        }
        parent::detailsProcess(true);

        //special case: currency_type is extrapolated during _insertListing() for new listing creation
        //but that doesn't happen for edits, so do it here!
        if ($cart->site->session_variables["currency_type"]) {
            trigger_error('DEBUG CART: Setting currency type..');
            $sql = "SELECT `precurrency`, `postcurrency`, `conversion_rate` FROM " . geoTables::currency_types_table . " WHERE `type_id` = ? LIMIT 1";
            $show_currency = $cart->db->GetRow($sql, array($cart->site->session_variables["currency_type"]));

            if ($show_currency === false) {
                trigger_error('ERROR CART SQL: sql ERROR! sql: ' . $sql . ' Error: ' . $cart->db->ErrorMsg());
                return false;
            }
            if (is_array($show_currency) && count($show_currency)) {
                $cart->site->session_variables['precurrency'] = $show_currency['precurrency'];
                $cart->site->session_variables['postcurrency'] = $show_currency['postcurrency'];
                $cart->site->session_variables['conversion_rate'] = $show_currency['conversion_rate'];
            }
        }

        //handle editing quantities for BNO auctions (special case. See Bugzilla #1228)
        if ($cart->site->session_variables['buy_now_only'] == 1 && isset($cart->site->session_variables['auction_quantity'])) {
            if (is_object($cart->item)) {
                $newQuantity = $cart->site->session_variables['auction_quantity'];
                $oldQuantity = $cart->item->get('initialQuantity');
                $quantityDelta = $newQuantity - $oldQuantity; //NOTE: this CAN (and will) be negative if the user is decreasing the available quantity
                if ($quantityDelta != 0) {
                    $cart->item->set('quantityDelta', $quantityDelta);
                }
            } else {
                //something funky going on here, but can't do much about it...
            }
        }

        parent::_saveSessionVarsDiff($cart->item, $cart->site->session_variables);
    }

    public static function mediaCheckVars()
    {
        parent::$_type = self::type;
        parent::mediaCheckVars();
    }

    public static function mediaProcess()
    {
        parent::$_type = self::type;
        parent::mediaProcess();
    }

    public static function mediaDisplay()
    {
        $cart = geoCart::getInstance();

        $cart->site->page_id = 10;
        $cart->site->get_text();

        $tpl_vars = $cart->getCommonTemplateVars();
        $tpl_vars['title1'] = $cart->site->messages[500372];
        $tpl_vars['title2'] = $cart->site->messages[500373];
        $tpl_vars['page_description'] = $cart->site->messages[500906];
        $tpl_vars['cancel_txt'] = $cart->site->messages[500378];

        geoView::getInstance()->setBodyVar($tpl_vars)
            ->addJScript(geoTemplate::getUrl('js', 'listing_placement.js'));

        parent::$_type = self::type;

        parent::mediaDisplay();
    }

    /**
     * Handy function for order items that are dynamically adding themself to this order
     * item, to set something in $cart->site->session_variables then save those session
     * vars using this function.
     *
     * This only works when called and listing_edit is the main type.  Otherwise you have
     * un-predictable results.
     *
     */
    public static function saveSessionVars()
    {
        $cart = geoCart::getInstance();

        parent::_saveSessionVarsDiff($cart->item, $cart->site->session_variables);
        //save changes to DB
        $cart->item->save();
    }

    /**
     * Get the session vars (all of them, not just the differences).  Handy for times when
     * you don't know if the item will be initialized or not (and thus having cart->site->session_variables
     * already started up).
     *
     * @param geoOrderItem $edit_item If specified, the listing edit order item to get session vars for.
     * @param bool $forceEvenFromEditItem If false, will only get session vars for the listing for all active
     *  order items.  If true (default), will include changes from the $edit_item order item, even if the
     *  order item is not yet active.
     * @return array
     */
    public static function getSessionVars($edit_item = null, $forceEvenFromEditItem = true)
    {
        if (!$edit_item && class_exists('geoCart', false)) {
            $cart = geoCart::getInstance();
            if (isset($cart->site->session_variables) && $cart->main_type == self::type) {
                //session vars would have already been initialized
                return $cart->site->session_variables;
            }
        }
        if ($edit_item) {
            $listing_id = $edit_item->get('listing_id');
            $session_variables = parent::_getSessionVarsFromListing($listing_id);
            if ($forceEvenFromEditItem) {
                $session_variables = array_merge($session_variables, $edit_item->get('session_variables', array()));
            }
            return $session_variables;
        }
        return false;
    }

    public static function selectDisplay()
    {
        $cart = geoCart::getInstance();

        $cart->site->page_id = 31;

        $linkURL = $cart->getProcessFormUrl();
        $cart->db->get_text(false, 31);

        $children = geoOrderItem::getChildrenTypes(self::type);

        /**
         * Expects return of array like:
         * array (
         *  array ('step_name' => 'link_text')
         * step_name does NOT include the item type
         *
         * @var unknown_type
         */
        $allSteps = geoOrderItem::callDisplay('listing_edit_geoCart_initSteps', null, $children);
        $tpl_vars = $cart->getCommonTemplateVars();
        $tpl_vars['nextPage'] = $linkURL;
        if ($cart->item) {
            $tpl_vars['previewUrl'] = $tpl_vars['cart_url'] . '&amp;action=forcePreview&amp;item=' . $cart->item->getId();
            $tpl_vars['choices'] = $cart->item->get('choices');
        } else {
            //something is very wrong. Throw a generic error (if there's not one coming in from elsewhere...)
            if (!isset($tpl_vars['error_msgs']['cart_error'])) {
                $tpl_vars['error_msgs']['cart_error'] = $cart->site->messages[495];
            }
        }


        $view = geoView::getInstance();
        self::fixStepLabels();
        $view->setBodyTpl('listing_edit/edit_choices.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);

        $cart->site->display_page();
    }

    public static function selectCheckVars()
    {
        return true;
    }

    public static function selectProcess()
    {
        $cart = geoCart::getInstance();
        $builtIn = array ('category','details','media','continue');
        trigger_error('DEBUG CART: selectProcess() top!  doStep: ' . $_GET['doStep']);
        if (in_array($_GET['doStep'], $builtIn)) {
            //built in step
            switch ($_GET['doStep']) {
                case 'category':
                    if (self::categoryIsEditable($cart->item->get('listing_id'))) {
                        $cart->item->set('edit_step', 'category');
                        $cart->insertStep(self::type . ':category');
                    } else {
                        //user trying to modify url to do things he shouldn't. return to select menu.
                        $cart->addError();
                        return false;
                    }
                    break;
                case 'details':
                    $cart->item->set('edit_step', 'details');
                    $cart->insertStep(self::type . ':details');
                    break;
                case 'media':
                    $children = geoOrderItem::getChildrenTypes(self::type);

                    if (geoOrderItem::callDisplay('addMedia', null, 'bool_true', $children)) {
                        $cart->item->set('edit_step', 'media');
                        $cart->insertStep('listing_edit:media');
                    } else {
                        //user trying to modify url to do things he shouldn't. return to select menu.
                        $cart->addError();
                        return false;
                    }
                    break;
                case 'continue':
                    //short circuit if nothing else in cart yet, and price is 0
                    trigger_error('DEBUG EDIT: selectProcess() continue top');
                    //Use skipCart() method..  It requires current step being set to cart though.
                    $currentStep = $cart->getCurrentStep();
                    $cart->setCurrentStep('cart');

                    //need to get this while it's still accessible, before skipping the cart
                    $listingId = $cart->item->get('listing_id');

                    if ($cart->skipCart(false, false)) {
                        //Skiping the cart succeeded!  Display results...
                        //do the actual display here as well, as we are using different text than normal
                        //the whole cart was free!

                        $cart->site->page_id = 10204;
                        $msgs = $cart->db->get_text(true, $cart->site->page_id);
                        //We do the work here as we use different text here specific
                        //to listing edits
                        $title = $msgs[500484];
                        $desc = $msgs[500485];
                        $tpl_vars = $cart->getCommonTemplateVars();
                        $tpl_vars['page_title'] = $title;
                        $tpl_vars['page_desc'] = $desc;
                        $tpl_vars['success_failure_message'] = '';
                        $tpl_vars['my_account_url'] = $cart->db->get_site_setting('classifieds_file_name') . '?a=4';
                        $tpl_vars['my_account_link'] = $msgs[500305];
                        $tpl_vars['edited_listing_id'] = $listingId;
                        //note:  do not get invoice link for listing edits.

                        trigger_error('DEBUG EDIT: here...');
                        geoView::getInstance()->setBodyTpl('shared/transaction_approved.tpl', '', 'payment_gateways')
                            ->setBodyVar($tpl_vars);

                        $cart->site->display_page();
                        $cart->removeSession();
                        trigger_error('DEBUG EDIT: after remove session');
                        //we are done here.  Don't let it go on.  Pull the plug.  Put it on ice. etc.
                        //NOTE:  normally we would not want to exit
                        //as that would mess up when in admin panel.
                        //OK to exit here, since we already confirmed not in admin.
                        trigger_error('DEBUG CART EDIT: Stoping cart short, to short-circuit stuff for listing edit.');
                        include GEO_BASE_DIR . 'app_bottom.php';
                        trigger_error('DEBUG EDIT: end of stuff.');
                        exit;
                    } else {
                        trigger_error('DEBUG EDIT: selectProcess() could not skip the cart for some reason...');
                        //set the step back to what it was before, so it can update properly.
                        $cart->setCurrentStep($currentStep);
                    }
                    trigger_error('DEBUG EDIT: end of stuff.');
                    return true;
                default:
                    $cart->addError();
                    return false;
            }
        } else {
            //not a built in step
            $steps = $cart->item->get('choices');
            $step = trim($_GET['doStep']);
            if (array_key_exists($step, $steps)) {
                //set the step to be processed next
                $cart->item->set('edit_step', $step);
                $cart->insertStep($step);
            } else {
                $cart->addError();
                //echo "step $step not in array <pre>".print_r($steps,1)."</pre><br />";
                return false;
            }
        }
        $cart->item->save();
        return true;
    }


    public static function passwordDisplay()
    {
        //TODO: Move this and like bits to an order item that is part of
        //the anon listing addon.
        $msgs = geoAddon::getText('geo_addons', 'anonymous_listing');

        $cart = geoCart::getInstance();

        $cart->site->page_id = 31;
        $cart->site->messages = $cart->db->get_text(true, 31);
        self::fixStepLabels();

        $tpl_vars = $cart->getCommonTemplateVars();

        $tpl_vars['nextPage'] = $cart->getProcessFormUrl();
        $tpl_vars['msgs'] = $msgs;
        $tpl_vars['error'] = $cart->getErrorMsg('anonPass');
        $tpl_vars['passFromURL'] = $_GET['anonPass'] ? $_GET['anonPass'] : '';

        $tpl = new geoTemplate('system', 'order_items');

        geoView::getInstance()->setBodyTpl('shared/anonymous_password_form.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);
        $cart->site->display_page();

        //unset the edit lock for this page only, so that unauthorized/malicious users can't lock out a listing without the password
        geoListing::getListing($cart->item->get('listing_id'))->setLocked(false);
    }

    public static function passwordCheckVars()
    {
        $cart = geoCart::getInstance();
        $pass = $_POST['anonPass'];

        $anon = geoAddon::getUtil('anonymous_listing');
        $result = ($anon) ? $anon->checkPass($cart->item->get('listing_id'), $pass) : false;

        if (!$result) {
            $msgs = geoAddon::getText('geo_addons', 'anonymous_listing');
            $cart->addError();
            $cart->addErrorMsg('anonPass', $msgs['passwordError']);
            return false;
        } else {
            //password checks out -- restore the edit lock, and then enter the listing process proper
            geoListing::getListing($cart->item->get('listing_id'))->setLocked(true);
            return true;
        }
    }

    public static function passwordProcess()
    {
        //nothing to do here
        return true;
    }



    /**
     * Required.
     * Used: various locations.
     *
     * This should return an array of the different order items that this
     * order item is a child of.  If this is a main order item type, it
     * should return an empty array.
     *
     * @return array
     */
    public static function getParentTypes()
    {
        //for "parent" order item, return empty array.
        return array();
    }

    /**
     * Required.
     * Used: in geoCart::cartDisplay()
     *
     * Used to get display details about item, and any child items as well.  Should return an associative
     * array, that follows:
     * array(
     *  'css_class' => string,//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
     *  'title' => string, //text that is displayed for this item in list of items purchased.
     *  'canEdit' => bool, //whether can edit it or not
     *  'canDelete' => bool, //whether can remove from cart or not
     *  'canPreview' => bool, //whether can preview the item or not
     *  'priceDisplay' => string, //price to display, should have precurrency and all that
     *  'cost' => double, //amount this adds to the total, what getCost returns
     *  'total' => double, //amount this AND all children adds to the total
     *  'children' => array(), //should be array of child items, with the index
     *                          //being the item's ID, and the contents being associative array like
     *                          //this one.  If no children, it should be an empty array.  (Careful
     *                          //not to get into any infinite recursion)
     * )
     * @return array An associative array as described above.
     */
    public function getDisplayDetails($inCart, $inEmail = false)
    {
        $l_title = '';
        if ($inCart) {
            //if possible, grab the most recent title from sessvars, because it may be the title itself that is being edited
            $cart = geoCart::getInstance();
            $l_title = $cart->site->session_variables['classified_title'];
        } else {
            //otherwise, check the listing data itself
            $listing = geoListing::getListing($this->get('listing_id'));
            if (is_object($listing)) {
                $l_title = $listing->title;
            }
        }
        $l_title = $this->_listingTitleDisplay(geoString::fromDB($l_title));


        $msgs = DataAccess::getInstance()->get_text(true, 10202);

        $title = $msgs[500321] . " - " . $l_title;

        $price = $this->getCost();

        $return = array (
            'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
            'title' => $title,
            'canEdit' => false, //NOTE: cannot "edit" a listing edit, because it makes the cart steps go wonky
                                //NOTE 2: there's normally no way to get to that edit button anyway, so it doesn't really matter
            'canDelete' => true, //show delete button for item?
            'canPreview' => true, //show preview button for item?
            'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
            'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //Price as it is displayed -- this value is dynamically overridden below
            'cost' => $price, //amount this adds to the total, what getCost returns
            'total' => $price, //amount this AND all children adds to the total (will add to it as we parse the children)
            'children' => array()   //should be array of child items, with the index
                                    //being the item's ID, and the contents being associative array like
                                    //this one.  If no children, it should be an empty array.  (Careful
                                    //not to get into any infinite recursion)
        );

        //done stepping through edit process, so reset steps vars
        //this way, clicking Edit button in cart will go to select screen instead of last used step
        //NOTE 3: this is no longer needed since the final Cart page is short-circuited for Listing Edits, and it breaks things with the new design
        //$this->set('edit_step', false);

        //THIS PART IMPORTANT:  Need to keep this part to make the item able to have children

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

        //$return['priceDisplay'] = geoString::displayPrice($return['total']);

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

    public static function categoryLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500502];
    }

    public static function detailsLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500503];
    }

    public static function selectLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500504];
    }

    public static function passwordLabel()
    {
        $msgs = geoAddon::getText('geo_addons', 'anonymous_listing');
        return $msgs['stepEditLabel'];
    }

    public static function geoCart_other_detailsCheckVars()
    {

        //do checkvars for any children
        $children = geoOrderItem::getChildrenTypes('listing_edit');
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);
    }


    public static function geoCart_other_detailsProcess()
    {

        //get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::type);
        geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $children);
    }


    public static function geoCart_other_detailsDisplay()
    {
        //Don't need to call children, as children are always called.
    }

    /**
     * Item-specific stuff for previewing the item
     *
     */
    public function geoCart_previewDisplay($sell_type = null)
    {
        $cart = geoCart::getInstance();

        $cart->site->session_id = $cart->item->getId();

        $cart->site->site_category = $cart->item->getCategory();
        //make sure stuff is done
        $items = $cart->order->getItem();
        foreach ($items as $item) {
            if (is_object($item) && is_object($item->getParent())) {
                $p = $item->getParent();
                if ($p->getId() == $this->getId()) {
                    //child of mine!
                    if (method_exists($item, 'geoCart_previewDisplay')) {
                        $item->geoCart_previewDisplay();
                    }
                }
            }
        }
        //set listing
        $cart->site->display_classified($cart->site->classified_id, false, $this->get('session_variables'));
    }





    /**
     * Functionality for if user clicks Cancel button while editing listing
     * -Releases the edit lock on the listing
     *
     * ***DO NOT*** call the parent's copy of this function from here!
     * If you do, clicking any "cancel" button within the edit process will DELETE the listing being edited
     *
     */

    public function processRemove()
    {
        //***see above comment about NOT calling parent's processRemove()

        //but DO make this guy pending first, that way if it's active that will
        //un-do the edit before deleting it
        if ($this->getStatus() == 'active') {
            //re-using code is fun :)
            $this->processStatusChange('pending', false, false);
        }

        //release edit lock
        $listing = geoListing::getListing($this->get('listing_id'));
        if (!is_object($listing)) {
            //can't find the listing, so there's nothing to unlock
            return true;
        }
        $listing->setLocked(false);

        //parent expects this to return true on success
        if (!$listing->isLocked()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Required.
     * Used: in geoCart::initSteps()
     *
     * Determine whether or not the other_details step should be added to the steps of adding this item
     * to the cart.  This should also check any child items if it does not need other_details itself.
     *
     * @return boolean True to add other_details to steps, false otherwise.
     */
    public static function geoCart_initSteps_addOtherDetails()
    {
        //normally we'd check with the children on this one, but
        //listing edit is designed to NOT use other details, so not
        //going to.
        return false;
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
    public function processStatusChange($newStatus, $sendEmailNotices = false, $updateCategoryCount = false, $skipToParent = null)
    {
        if ($newStatus == $this->getStatus()) {
            //the status hasn't actually changed, so nothing to do
            return;
        }
        $activate = ($newStatus == 'active') ? true : false;

        $already_active = ($this->getStatus() == 'active') ? true : false;

        //get categories



        //don't really want to do parent's stuff here, so pass 4th parameter as true to bounce to grandparent
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount, true);

        //after taking any kind of status-change action (activating OR deactivating (as in an admin declining to approve an edit)),
        //it's safe to assume the listing should be unlocked for editing -- make sure that it is so
        //(this is a change from 7.3.4 and earlier where it would only unlock on activation)
        $listing = geoListing::getListing($this->get('listing_id'));
        if ($listing) {
            $listing->setLocked(false);
        }

        if ($activate) {
            //update category
            $this->doCategoryChange();
            $this->doDetailsChange();
            $this->activateImages();
            $db = DataAccess::getInstance();
            if ($sendEmailNotices) {
                if ($db->get_site_setting('notify_user_edit_approved')) {
                    //Send e-mail notice
                    //make sure to send the e-mail AFTER the status has been set and everything saved, in case the e-mail causes problems...
                    $order = $this->getOrder();
                    if (!is_object($order)) {
                        //where's the order for this thing?
                        return;
                    }
                    $order->save();

                    $msgs = $db->get_text(true, 10206);
                    $subject = $msgs[500340];
                    $user = geoUser::getUser($order->getBuyer());
                    $listing_id = $this->get('listing_id');
                    if ($user) {
                        $tpl = new geoTemplate('system', 'emails');
                        $tpl->assign('introduction', $msgs[500900]);
                        $tpl->assign('salutation', $user->getSalutation());
                        $tpl->assign('messageBody', $msgs[500341]);
                        $tpl->assign('listingURL', $listing->getFullUrl());
                        if ($listing) {
                            $tpl->assign('listingTitle', strip_tags(geoString::fromDB($listing->title)));
                        }
                        $message = $tpl->fetch('listing/listing_edit_complete.tpl');

                        geoEmail::sendMail($user->email, $subject, $message, 0, 0, 0, 'text/html');
                    } else {
                        //editing an anonymous listing
                        //sending mail here would be an exploitable spam hole
                    }
                }
                if ($db->get_site_setting('admin_email_edit') && geoPC::is_ent()) {
                    $listing_id = $this->get('listing_id');
                    $subject = "A listing has been edited - #" . $listing_id;
                    $message = "The below listing has been edited:\n\n";
                    $message .= $db->get_site_setting('classifieds_url') . "?a=2&b={$listing_id}";
                    if ($listing) {
                        $message .= ' (' . geoString::fromDB($listing->title) . ')';
                    }
                    $message .= "\n\n";
                    $message .= "View item details:  Log into your admin panel, then use the direct link below to view
					the edit listing details for order-item #" . $this->getId() . ":\n\n";
                    $message .= dirname($db->get_site_setting('classifieds_url')) . '/' . ADMIN_LOCAL_DIR . 'index.php?page=orders_list_items_item_details&item_id=' . $this->getId();
                    geoEmail::sendMail($db->get_site_setting('site_email'), $subject, $message, 0, 0, 0, 'text/plain');
                }
            }
        } elseif (!$activate && $already_active) {
            //admin de-activation. revert to original data
            $this->doCategoryChange(true);
            $this->doDetailsChange(true);
            $this->deactivateImages();
        }
        //NOTE: do not need to call children, parent does that for us :)
    }

    protected function activateImages()
    {
        $images = $this->get('images_captured');
        if (!$images) {
            $images = $this->get('all_images_captured');
        }
        $listing_id = (int) $this->get('listing_id');
        $db = DataAccess::getInstance();
        if (count($images) > 0) {
            //images were captured
            //link them to their ads
            foreach ($images as $key => $value) {
                $displayOrder = (int)$key;
                $sql = "UPDATE " . geoTables::images_urls_table . " SET
					`classified_id` = $listing_id,
					`display_order` = $displayOrder
					WHERE `image_id` = {$value["id"]}";
                $image_result = $db->Execute($sql);
                if (!$image_result) {
                    trigger_error('DEBUG IMG: SQL Error: ' . $db->ErrorMsg());
                    return false;
                }
            }
        }
        return $this->updateImageCount();
    }

    protected function deactivateImages()
    {
        $images = $this->get('revertState');
        $listing_id = $this->get('listing_id');
        $db = DataAccess::getInstance();

        //delink all images attached to listing
        $sql = "update " . geoTables::images_urls_table . " set	classified_id = 0 WHERE classified_id = " . $listing_id;
        $result = $db->Execute($sql);

        //relink old images
        foreach ($images as $key => $value) {
            $displayOrder = (int)$key;
            $sql = "update " . geoTables::images_urls_table . " set	classified_id = " . $listing_id . ", `display_order`=$displayOrder WHERE image_id = " . intval($value["id"]);
            $image_result = $db->Execute($sql);
            if (!$image_result) {
                trigger_error('DEBUG IMG: SQL Error: ' . $db->ErrorMsg());
                return false;
            }
        }

        return $this->updateImageCount();
    }

    /**
     * easy way to update the classifieds table when changing the number of images attached to a listing
     * split off into its own function to combat world hunger, code duplication, and myriad other maladies
     *
     * @return bool success
     *
     */
    protected function updateImageCount()
    {
        $db = DataAccess::getInstance();
        $listing = geoListing::getListing($this->get('listing_id'));
        if (!$listing) {
            trigger_error('DEBUG IMG: failed to get listing item in updateImageCount()');
            return false;
        }

        //find current number of images
        $sql = "select count(image_id) as count from " . geoTables::images_urls_table . " where classified_id = " . $listing->id;

        $activeImages = $db->GetOne($sql);
        $previousImages = $listing->image;

        if ($activeImages > $previousImages) {
            //adding to the number of images to display
            $listing->image = $activeImages;
        }
        return true;
    }

    /**
     * database query to change the category of a listing
     *
     * @param Int $newCat the new category, or 0 to revert to original
     */
    protected function doCategoryChange($revert = false)
    {
        //now done as part of doDetailsChange, for simplicity
        return true;
    }

    protected function doDetailsChange($revert = false)
    {
        $db = DataAccess::getInstance();

        $listing_id = intval($this->get('listing_id'));

        if (!$listing_id) {
            //something wrong, can't proceed
            return false;
        }

        //Session vars to allow to update.
        //Don't use full array from parent, since we only need
        //to update session vars set during details collection.
        $allowedVars = array(
            'seller',
            'date',
            'phone_1_option',
            'phone_2_option',
            'fax_option',
            'address',
            'city',
            'state',
            'country',
            'zip_code',
            'mapping_location',
            'classified_length',
            'classified_title',
            'description',
            'category',
            'price',
            'email_option',
            'expose_email',
            'buy_now_only',
            'sell_type',
            'precurrency',
            'postcurrency',
            'conversion_rate',
            'payment_options',
            'url_link_1',
            'url_link_2',
            'url_link_3',
            'payment_options',
            'seller_buyer_data',
            'auction_quantity',
            'auction_minimum',
            'auction_reserve',
            'auction_buy_now',
            'location',
            'price_applies',
            'show_contact_seller',
            'show_other_ads'

        );
        if (geoPC::is_ent()) {
            for ($i = 1; $i < 21; $i++) {
                $allowedVars[] = 'optional_field_' . $i;
            }
        }
        //allow items to add to the update array
        //for order items that use detailsProcess_getMoreDetails
        $more = geoOrderItem::callDisplay('detailsEdit_getMoreDetails_vars', null, 'array', null, true);
        if ($more && is_array($more)) {
            //loop through them and add them to the vars we are going to update
            foreach ($more as $vars) {
                foreach ($vars as $field) {
                    if (!in_array($field, $allowedVars) && isset(parent::$listing_vars_to_update[$field])) {
                        $allowedVars[] = $field;
                    }
                }
            }
        }

        $current_status = $this->getStatus();
        if ($current_status == 'active') {
            $this->setStatus('temp_disable');
        }

        $old = parent::_getSessionVarsFromListing($listing_id, false, true);
        $this->setStatus($current_status); //set status back

        $new = $this->get('session_variables');

        if ($new['cost_options'] || $revert) {
            parent::insertCostOptions($this, $listing_id, ($revert ? $old : $new));
        }

        //handle javascript pre/post currency thingy
        if (isset($new['currency_type'])) {
            $sql = "SELECT `precurrency`, `postcurrency`, `conversion_rate` FROM " . geoTables::currency_types_table . " WHERE `type_id` = ? LIMIT 1";
            $show_currency = $db->GetRow($sql, array($new["currency_type"]));

            if ($show_currency === false) {
                trigger_error('ERROR CART SQL: sql ERROR! sql: ' . $sql . ' Error: ' . $cart->db->ErrorMsg());
                return false;
            }
            if (is_array($show_currency) && count($show_currency)) {
                $new['precurrency'] = $show_currency['precurrency'];
                $new['postcurrency'] = $show_currency['postcurrency'];
                $new['conversion_rate'] = $show_currency['conversion_rate'];
                unset($new['currency_type']);
            }
        }

        //special case stuff to handle BNO quantity changes. See Bugzilla #1228
        $quantityDelta = (int)$this->get('quantityDelta');
        if ($quantityDelta) {
            $listing = geoListing::getListing($listing_id);
            if ($revert) {
                $quantityDelta *= -1; //if reverting, invert the sign of the delta
            }
            $listing->quantity_remaining += $quantityDelta;
            //since we're doing things special, make sure the quantity field doesn't update the "normal" way
            $key = array_search('auction_quantity', $allowedVars);
            if ($key !== false) {
                unset($allowedVars[$key]);
            }
        }

        $updateSearch = false;

        //category questions & tags



        $qSearch = $tSearch = '';
        $extraSv = ($revert) ? $old : array_merge($old, $new); //special-case array of session vars for these next two things
        $skipQuestions = false;

        if ($extraSv['question_value'] == $old['question_value'] && $extraSv['question_value_other'] == $old['question_value_other']) {
            //this is a special case: we're not clearing these, but nothing has changed
            //so skip deleting and replacing altogether, changing nothing!
            $skipQuestions = true;
        } else {
            //remove old questions -- even if there are no new ones set (for the case where we're intentionally removing all previously-selected options)
            $db->Execute('DELETE FROM ' . geoTables::classified_extra_table . ' WHERE `classified_id` = ' . $listing_id);
            if (isset($new['question_value']) || isset($new['question_value_other'])) {
                //insert questions and tags and update the search text according to them
                $qSearch = self::insertCatQuestions($listing_id, $extraSv);
                if ($qSearch === false) {
                    trigger_error('ERROR CART TRANSACTIONS: Error when inserting category questions, so error when iserting listing.');
                    $cart->site->error_message = $cart->site->messages[57];
                    return false;
                }
            }
        }

        if (isset($new['tags'])) {
            $tSearch = self::updateTags($listing_id, $extraSv['tags']);
            if ($tSearch === false) {
                trigger_error('ERROR CART TRANSACTIONS: Error when inserting category questions, so error when iserting listing.');
                return false;
            }
        }

        if (!$skipQuestions) {
            $search_text = $qSearch . $tSearch;
            //make sure search values are updated
            $sql = "UPDATE " . geoTables::classifieds_table . " SET	`search_text` = ? WHERE `id` = ?";

            $result = $db->Execute($sql, array(geoString::toDB($search_text . ''),$listing_id));
            if (!$result) {
                trigger_error('ERROR SQL TRANSACTION: sql - ' . $sql . ' error: ' . $cart->db->ErrorMsg());
                return false;
            }
        }

        //reset contents of listing_regions table
        $location = ($revert) ? $old['location'] : $new['location'];
        if ($location) {
            geoRegion::setListingRegions($listing_id, $location);

            $geographicOverrides = geoRegion::getLevelsForOverrides();

            //if any specific levels are in use (city/state/country), swap in those values to their specific sessvars
            if ($geographicOverrides['country']) {
                $country = $location[$geographicOverrides['country']];
                $new['country'] = geoRegion::getNameForRegion($country);
            }
            if ($geographicOverrides['state']) {
                $state = $location[$geographicOverrides['state']];
                $new['state'] = geoRegion::getNameForRegion($state);
            }
            if ($geographicOverrides['city']) {
                $city = $location[$geographicOverrides['city']];
                $new['city'] = geoRegion::getNameForRegion($city);
            }
            parent::_saveSessionVarsDiff($this, $new); //save changes sessvars
        }

        if ($revert) {
            //if reverting, then do things the easy way and just go back to whatever the old values are
            geoLeveledField::setListingValues($listing_id, $old['leveled']);
        } else {
            //otherwise, if $new['leveled'] is blank, nothing has changed, so preserve current values
            if ($new['leveled']) {
                //but if it has things, merge them into existing and set as the total value
                geoLeveledField::setListingValues($listing_id, array_merge($old['leveled'], $new['leveled']));
            }
            //TODO: there's not a good way to completely clear a value by editing, once set
            //      this is, I think, a weakness of the way the "diff" values are saved -- there's nothing in the data we have here that indicates that case
        }

        $category = ($revert) ? $old['category'] : $new['category'];
        if ($category) {
            geoCategory::setListingCategory($listing_id, $category);
            //force-update category counts for both the old and new categories
            if ($old['category']) {
                geoCategory::updateListingCount($old['category']);
            }
            if ($new['category'] && $new['category'] != $old['category']) {
                geoCategory::updateListingCount($new['category']);
            }
        }

        $parts = $queryData = array();
        if ($db->get_site_setting('edit_reset_date') == 1 && !$revert) {
            //reset listing start date on edit
            $new['date'] = geoUtil::time();
            parent::_saveSessionVarsDiff($this, $new); //save the change of date
        }
        $use = ($revert) ? $old : $new;
        foreach ($use as $i => $val) {
            if (!in_array($i, $allowedVars)) {
                //this isn't a setting to be saved to db
                continue;
            }
            $keys = (isset(parent::$session_to_listing_key_map[$i])) ? parent::$session_to_listing_key_map[$i] : $i;
            $keys = (is_array($keys)) ? $keys : array($keys);
            //loop through each translation and set it, this allows one session var to be assigned to multiple
            //listing rows.
            foreach ($keys as $key) {
                if (isset(parent::$listing_vars_to_update[$key])) {
                    $parts [] = "`$key` = ?";
                    //encode value according to what type it is
                    switch (parent::$listing_vars_to_update[$key]) {
                        case 'toDB':
                            if (is_array($val) && $key == 'seller_buyer_data' && geoPC::is_ent()) {
                                //special case
                                $val = serialize($val);
                            }
                            $queryData [] = trim(geoString::toDB($val));
                            break;
                        case 'int':
                            $queryData [] = intval($val);
                            break;
                        case 'float':
                            $queryData [] = floatval($val);
                            break;
                        case 'bool':
                            $queryData [] = (($val) ? 1 : 0);
                            break;
                        default:
                            //not altered, for fields like "date"
                            $queryData [] = $val;
                            break;
                    }
                }
            }
        }

        if (count($parts) == 0) {
            //err nothing to change
            return true;
        }
        //now build the array
        $sql = "UPDATE " . geoTables::classifieds_table . " SET " . implode(', ', $parts) . " WHERE `id` = '$listing_id' LIMIT 1";
        $result = $db->Execute($sql, $queryData);
        //category changed - update counts
        if ($result && isset($new['category'])) {
            //category changed, update counts for old and new category
            geoCategory::updateListingCount($old['category']);
            geoCategory::updateListingCount($new['category']);
        }
        return $result;
    }

    public static function geoCart_payment_choicesProcess($sell_type = null)
    {
        //required so parent doesn't screw up
        return;
    }

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
            //change the item back to what it was originally.
            $cart->initItem($original_id);
        }
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
            if ($cart->item) {
                $listingId = $cart->item->get('listing_id');
            }
        } elseif ($step == 'my_account_links') {
            //in my account links, need to return something short
            return $cart->site->messages[500640];
        } else {
            $listingId = intval($_GET['listing_id']);
        }
        $title = '';//set default to empty string
        if ($listingId) {
            $listing = geoListing::getListing($listingId);
            $title = "( $listingId - " . geoString::fromDB($listing->title) . " )";
        }
        //text: "editing existing listing"
        return $cart->site->messages[500393] . ' ' . $title;
    }

    public static function geoCart_getCartItemDetails()
    {
        $cart = geoCart::getInstance();
        foreach ($cart->actions_performed as $action) {
            if (isset($action['cancel']) && $action['cancel'] == self::type && !defined('IN_ADMIN')) {
                //see if there are other items in the cart

                if (count($cart->order->getItem()) == 0) {
                    //if there are not, then re-direct to active listings
                    $url = geoFilter::getBaseHref() . $cart->db->get_site_setting('classifieds_file_name') . '?a=4&b=1';
                    header('Location: ' . $url);
                    include GEO_BASE_DIR . 'app_bottom.php';
                    exit;
                }
            }
        }
    }

    public static function fixStepLabels()
    {
        $view = geoView::getInstance();

        //lets "fix" steps
        $currentSteps = $view->cartSteps;
        if ($currentSteps) {
            unset($currentSteps['cart']);
        }
        $view->cartSteps = $currentSteps;
    }
}
