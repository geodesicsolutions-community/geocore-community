<?php

//order_items/_listing_placement_common.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    17.07.0-31-g2541e61
##
##################################

require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';

abstract class _listing_placement_commonOrderItem extends geoOrderItem
{

    protected static $listing_vars_to_update = array (
        'seller' => 'int',
        //Do NOT auto-update live setting, it will mess things up when changing
        //statuses back and forth.  To change live, alter the listing manually.
        //'live' => 'int',
        'title' => 'toDB',
        'date' => 'int',
        'description' => 'toDB',
        'language_id' => 'int',
        'precurrency' => 'toDB',
        'price' => 'float',
        //This is actually an enum (item,lot)
        'price_applies' => 'string',
        'postcurrency' => 'toDB',
        'conversion_rate' => 'float',
        'image' => 'int',
        'offsite_videos_purchased' => 'int',
        'additional_regions_purchased' => 'int',
        'duration' => 'int',
        'location_city' => 'toDB',
        'location_zip' => 'toDB',
        'ends' => 'int',
        'search_text' => 'toDB',
        'viewed' => 'int',
        'responded' => 'int',
        'forwarded' => 'int',
        'bolding' => 'bool',
        //better placement needs to manually be set, due to "rotation" the value
        //changes over time.  But it is set further in the "restore"
        //'better_placement' => 'int',
        'featured_ad' => 'bool',
        'featured_ad_2' => 'toDB',
        'featured_ad_3' => 'toDB',
        'featured_ad_4' => 'toDB',
        'featured_ad_5' => 'toDB',
        'attention_getter' => 'toDB',
        'attention_getter_url' => 'string',
        'expiration_notice' => 'bool',
        'expiration_last_sent' => 'int',
        'sold_displayed' => 'bool',
        'business_type' => 'int',
        'optional_field_1' => 'toDB',
        'optional_field_2' => 'toDB',
        'optional_field_3' => 'toDB',
        'optional_field_4' => 'toDB',
        'optional_field_5' => 'toDB',
        'optional_field_6' => 'toDB',
        'optional_field_7' => 'toDB',
        'optional_field_8' => 'toDB',
        'optional_field_9' => 'toDB',
        'optional_field_10' => 'toDB',
        'one_votes' => 'int',
        'two_votes' => 'int',
        'three_votes' => 'int',
        'vote_total' => 'int',
        'email' => 'toDB',
        'expose_email' => 'bool',
        'phone' => 'toDB',
        'phone2' => 'toDB',
        'fax' => 'toDB',
        'mapping_location' => 'toDB',
        'renewal_length' => 'int',
        'optional_field_11' => 'toDB',
        'optional_field_12' => 'toDB',
        'optional_field_13' => 'toDB',
        'optional_field_14' => 'toDB',
        'optional_field_15' => 'toDB',
        'optional_field_16' => 'toDB',
        'optional_field_17' => 'toDB',
        'optional_field_18' => 'toDB',
        'optional_field_19' => 'toDB',
        'optional_field_20' => 'toDB',
        'discount_id' => 'int',
        'discount_amount' => 'float',
        'discount_percentage' => 'float',
        'url_link_1' => 'toDB',
        'url_link_2' => 'toDB',
        'url_link_3' => 'toDB',
        'price_plan_id' => 'int',
        'auction_type' => 'int',
        'quantity' => 'int',
        'quantity_remaining' => 'int',
        'final_fee' => 'bool',
        'minimum_bid' => 'float',
        'starting_bid' => 'float',
        'reserve_price' => 'float',
        'buy_now' => 'float',
        'current_bid' => 'float',
        'final_price' => 'float',
        'high_bidder' => 'string',
        'start_time' => 'int',
        'payment_options' => 'payment_options',
        'end_time' => 'int',
        'buy_now_only' => 'bool',
        'item_type' => 'int',
        'storefront_category' => 'int',
        'hide' => 'int',
        'reason_ad_ended' => 'toDB',
        'location_address' => 'toDB',
        'seller_buyer_data' => 'toDB',
        //Like "live" - do not allow changing automatically as it messes things
        //up when old removed order items come into play
        //'order_item_id' => 'int',
        'show_contact_seller' => 'yesno',
        'show_other_ads' => 'yesno',
    );

    protected static $listing_vars_to_restore = array (
        //Treat better placement as a bool so it gets converted back to 1
        'better_placement' => 'bool',
        );

    protected static $session_to_listing_key_map = array(
        'classified_title' => 'title',
        'classified_length' => 'duration',
        'address' => 'location_address',
        'city' => 'location_city',
        'zip_code' => 'location_zip',
        'email_option' => 'email',
        'phone_1_option' => 'phone',
        'phone_2_option' => 'phone2',
        'fax_option' => 'fax',
        'auction_quantity' => 'quantity', //NOTE: this intentionally does not update quantity_remaining. See Bug #1228
        'auction_minimum' => array('starting_bid','minimum_bid'),
        'auction_reserve' => 'reserve_price',
        'auction_buy_now' => 'buy_now',
        'sell_type' => 'item_type'
    );

    private static $editing;
    private static $isAdmin;

    /**
     * Because of the nature of static functions and abstract classes, for each function that needs to use this
     * var in the abstract class, the class that extends this class will need to declare the function, and
     * at the top of the function, set parent::$_type = 'type', then parent::function_name() or something like that.
     *
     * @var string
     */
    protected static $_type;

    public $renew_upgrade = 0;
    /**
     * So we don't have to remember what # is for renewal, and what is for upgrade.
     * renewal is 1.
     *
     */
    const renewal = 1;
    /**
     * Upgrade is 2
     *
     */
    const upgrade = 2;

    /**
     * Can be used by addons to add vars to the array of
     * listing vars to update, so that if that var exists
     * in session_variables, it will be populated in the
     * geodesic_classifieds table when copying changes over.
     *
     * @param string $key
     * @param string $filter Either int, float, bool, yesno, date, or toDB.  This is
     *  what type of field it is, which dictates how the var will be cleaned
     *  prior to being inserted into the listing table.  (note: yesno option added
     *  in version 7.4.0)
     */
    public static function addListingVar($key, $filter)
    {
        $allowedFilters = array ('int', 'float', 'bool', 'yesno','toDB', 'date');
        $key = trim($key);
        if (in_array($filter, $allowedFilters) && strlen($key)) {
            self::$listing_vars_to_update[$key] = $filter;
        }
    }

    /**
     * Can be used by addons to add vars to the array of
     * listing vars to "restore" when generating session vars based on entry in
     * the database.  Very similar to addListingVar, difference is that vars added
     * this way are NOT used to generate a listing based on session vars.  This is
     * ONLY used the other way, generating session vars based on a listing.
     *
     * Note that the other option, addListingVar, those vars are used for both
     * generating the listing, and backwards, generating the session vars, so it
     * is NOT necessary to add a var using both methods.
     *
     * @param string $key
     * @param string $filter Either int, float, bool, yesno, date, or toDB.  This is
     *  what type of field it is, which dictates how the var will be cleaned
     *  prior to being inserted into the session vars
     * @since Version 7.4.0
     */
    public static function addListingRestoreVar($key, $filter)
    {
        $allowedFilters = array ('int', 'float', 'bool', 'yesno','toDB', 'date');
        $key = trim($key);
        if (in_array($filter, $allowedFilters) && strlen($key)) {
            self::$listing_vars_to_restore[$key] = $filter;
        }
    }

    public function displayInAdmin()
    {
        return true;
    }
    /**
     * Optional
     * Used: in admin, display items awaiting approval (only for main items, not for sub-items)
     *
     * @return array Associative array, in the form array ('type' => string, 'title' => string)
     */
    public function adminDetails()
    {
        $session_variables = $this->get('session_variables');
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
            'type' => $this->getTypeTitle(),
            'title' => $title
        );
    }

    public static function geoCart_initSteps($allPossible = false)
    {
        $cart = geoCart::getInstance();
        $applies_to = null;
        if (self::$_type == 'classified') {
            $applies_to = 1;
        } elseif (self::$_type == 'auction') {
            $applies_to = 2;
        } elseif ((bool)$allPossible !== $allPossible) {
            //all possible is not bool...  something must have passed it in as the applies
            //to var
            $applies_to = $allPossible;
            //and if it's not bool, most likely it should be false
            $allPossible = false;
        }
        $cart->addStep(self::$_type . ':splash');
        if (!$allPossible) {
            $choose_plan = self::_choosePricePlan($applies_to);

            if (!$choose_plan && $cart->price_plan['price_plan_id'] && is_object($cart->item) && $cart->item->getType() == self::$_type && !$cart->item->getPricePlan()) {
                //set price plan to default to keep from looking it up each time
                $cart->item->setPricePlan($cart->price_plan['price_plan_id']);
            } elseif ($choose_plan) {
                $cart->addStep(self::$_type . ':choose_plan');
            }
        } else {
            //showing all possible, so add choose plan
            $cart->addStep(self::$_type . ':choose_plan');
        }
        $cart->addStep(self::$_type . ':category');
        $cart->addStep(self::$_type . ':details');

        //get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::$_type);

        if ($allPossible || geoOrderItem::callDisplay('addMedia', null, 'bool_true', $children)) {
            $cart->addStep(self::$_type . ':media');
        }

        geoOrderItem::callUpdate('geoCart_initSteps', $allPossible, $children);
    }

    protected static $_choose_price_plan = array();
    protected static function _choosePricePlan($applies_to)
    {
        if (isset(self::$_choose_price_plan[$applies_to])) {
            return self::$_choose_price_plan[$applies_to];
        }
        $cart = geoCart::getInstance();

        //check to see if we should show price plan choice form
        $sql = "SELECT `price_plan_id` FROM " . geoTables::attached_price_plans . " WHERE `group_id` = " . intval($cart->user_data['group_id']) . " AND `price_plan_id` > 0 AND `applies_to` = $applies_to";
        $multiple_price_plan_result = $cart->db->Execute($sql);
        if ($multiple_price_plan_result && $multiple_price_plan_result->RecordCount() > 1) {
            self::$_choose_price_plan[$applies_to] = 1;
            return true;
        }
        self::$_choose_price_plan[$applies_to] = 0;
        return false;
    }

    public static function choose_planCheckVars($applies_to)
    {
        $cart = geoCart::getInstance();

        $price_plan = intval((isset($_GET['price_plan'])) ? $_GET['price_plan'] : 0);
        if (!$price_plan && isset($_POST['price_plan'])) {
            //let price plan set by post
            $price_plan = (int)$_POST['price_plan'];
        }
        if (!$price_plan) {
            if ($cart->isCombinedStep() && !self::_choosePricePlan(self::$_type)) {
                //just in combined step
                return;
            }
            $cart->addError();

            return;
        }

        //check that price plan exists and attached to this group

        $sql = "SELECT * FROM " . geoTables::attached_price_plans . " WHERE `group_id` = ? AND `price_plan_id` = ? AND `applies_to` = $applies_to";
        $check_price_plan_result = $cart->db->Execute($sql, array($cart->user_data['group_id'], $price_plan));
        if (!$check_price_plan_result || $check_price_plan_result->RecordCount() != 1) {
            $cart->addError();
            $cart->site->setup_error = $cart->site->messages[453];

            return false;
        }
        if ($cart->isCombinedStep()) {
            //go ahead and set price plan now on combined step since that affects
            //other things that might be loaded...
            self::choose_planProcess();
        }
        return true;
    }
    public static function choose_planProcess()
    {
        $cart = geoCart::getInstance();

        //set the price plan, it was already checked by checkVars...

        $price_plan = intval((isset($_GET['price_plan'])) ? $_GET['price_plan'] : 0);
        if (!$price_plan && isset($_POST['price_plan'])) {
            $price_plan = (int)$_POST['price_plan'];
        }

        if (!$price_plan && $cart->isCombinedStep()) {
            //don't really need to do anything
            return;
        }

        $cart->site->users_price_plan = $cart->site->session_variables['price_plan_id'] = $cart->site->session_variables['users_price_plan'] = $price_plan;
        $cart->item->set('session_variables', $cart->site->session_variables);
        $cart->item->setPricePlan($price_plan);
        $cart->setPricePlan($price_plan, 0);

        //check to make sure they havn't reached the max allowed for this plan
        self::_checkMaximumListingLimit();
    }
    public static function choose_planDisplay($applies_to)
    {
        $cart = geoCart::getInstance();
        $cart->site->users_group = $cart->user_data['group_id'];
        $cart->site->sell_type = $applies_to;

        $sql = "SELECT * FROM " . geoTables::attached_price_plans . " WHERE `group_id` = ? AND `price_plan_id` > 0 AND `applies_to` = $applies_to ORDER BY `name`";
        $attached_result = $cart->db->Execute($sql, array($cart->site->users_group));

        if (!$attached_result || $attached_result->RecordCount() == 0) {
            if ($cart->isCombinedStep()) {
                //just a combined step, which always calls this one...
                return;
            }
            $cart->site->setup_error = 1;
            //returned false, just go with default one and skip to next step.
            $current_step = $cart->current_step;
            $cart->current_step = $cart->cart_variables['current_step'] = $cart->getNextStep();
            if ($cart->current_step !== $current_step) {
                //made sure we don't do infinite loop
                return $cart->displayStep();
            }
            //should not get here unless weird error.. this just fallback so use doesn't see blank page
            if (!$cart->isCombinedStep()) {
                return self::categoryDisplay();
            }
        }

        //$this->check_user_subscription();
        //the user does not currently have a subscription...and the price plan has not been set
        $cart->site->page_id = 8;
        $cart->site->get_text();

        //$tpl = new geoTemplate('system','order_items');
        $tpl_vars = $cart->getCommonTemplateVars();
        $tpl_vars['msgs'] = $cart->site->messages;
        $tpl_vars['price_plans'] = $attached_result->GetAll();
        $tpl_vars['error_msgs'] = $cart->getErrorMsgs();
        $price_plan = ($cart->site->session_variables['price_plan_id']) ? $cart->site->session_variables['price_plan_id'] : $cart->item->getPricePlan();
        //die('<pre>'.print_r($cart->site->session_variables,1));
        if (!$price_plan && $cart->isCombinedStep()) {
            //combined step, need to have price plan set to a default value...
            $cart->item->setPricePlan($cart->price_plan['price_plan_id']);
            $price_plan = $cart->item->getPricePlan();
            $cart->item->save();
        }
        $tpl_vars['price_plan_id'] = $price_plan;

        geoView::getInstance()->setBodyTpl('shared/choose_price_plan.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars);

        $cart->site->display_page();
        return $tpl_vars;
    }

    public static function geoCart_initItem_forceOutsideCart()
    {
        return false;
    }

    public function geoCart_initItem_new($item_type)
    {
        self::initSessionVars($item_type);
        $cart = geoCart::getInstance();
        if (!defined('IN_ADMIN') && geoPC::is_print() && $cart->db->get_site_setting('disableClientPlaceListings')) {
            //oops, not allowed to place listing on client side
            //get text, since text won't be there at this point
            $cart->site->messages = $cart->db->get_text(true, 10202);

            $cart->addErrorMsg('no_listings', $cart->site->messages[500895]);

            return false;
        }
        //check to make sure max listing count has not been met, adding 1 to the count since the one we are adding right now is not
        //counted yet...  But only check if they do NOT have multiple listings...
        if (!self::_choosePricePlan($item_type) && !self::_checkMaximumListingLimit(1)) {
            //oops, they have reached it
            return false;
        }

        //make sure they don't have restriction in place
        if (!($cart->user_data['restrictions_bitmask'] & 1)) {
            //not allowed to place a new listing, as per user group restrictions!
            return false;
        }

        if (geoAddon::triggerDisplay('prevent_new_listing', $cart->user_data, geoAddon::BOOL_TRUE)) {
            return false;
        }

        if (!defined('DID_COPY') && isset($_REQUEST['copy_id']) && $_REQUEST['copy_id']) {
            //attempt to copy the listing over
            define('DID_COPY', 1);
            trigger_error('DEBUG CART: Copy Listing Here');
            self::_copyListing(intval($_REQUEST['copy_id']), $item_type);

            //reset the price plan, to keep it from being set to "old" price plan
            $setting = ($item_type == 1) ? 'price_plan_id' : 'auction_price_plan_id';
            $this->setPricePlan($cart->user_data[$setting]);
        }
        if ($cart->user_data['id'] == 1) {
            $cart->addErrorMsg('no_admin', 'The admin user is incapable of placing listings for security reasons. Log in as a normal user to continue.');
            return false;
        }
        return true;
    }

    public function initSessionVars($item_type)
    {
        $cart = geoCart::getInstance();

        //this is classifieds
        $cart->site->sell_type = $item_type;
        if ($this->get('session_variables')) {
            //get variables from db and save in local variables
            trigger_error('DEBUG CART: Used -- session vars already set.');
            self::set_sell_variables($item_type);

            $cart->setPricePlan($this->getPricePlan(), $this->getCategory());
            if ($this->getPricePlan() && !self::_checkMaximumListingLimit()) {
                //reached limit of how many listings there can be!
                return false;
            }
            // Set the price plan
            $cart->site->price_plan = $cart->price_plan;
        } else {
            trigger_error('DEBUG CART: brand new -- session vars NOT set.');
            //create new sell session
            //do NOT encode at this time, the data is encoded when going from session
            //to the classifieds table, it is not encoded in the session vars
            $mapping_location = array();
            if ($cart->user_data['address']) {
                $mapping_location[] = $cart->user_data['address'];
            }
            if ($cart->user_data['city']) {
                $mapping_location[] = $cart->user_data['city'];
            }
            if ($cart->user_data['state']) {
                $mapping_location[] = $cart->user_data['state'];
            }
            if ($cart->user_data['zip']) {
                $mapping_location[] = $cart->user_data['zip'];
            }
            if ($cart->user_data['country']) {
                $mapping_location[] = $cart->user_data['country'];
            }
            $mapping_location = implode(" ", $mapping_location);

            //get Fields here, and only populate from userdata the fields that are enabled
            $fields = geoFields::getInstance($cart->user_data['group_id'], $this->getCategory());

            $cart->site->session_variables = array (
                'seller' => $cart->user_data['id'],
                //Once we make language ID saved for user, use the saved language ID instead of from session
                'language_id' => geoSession::getInstance()->getLanguage(),
                'time_started' => geoUtil::time(),
                'phone_1_option' => ($fields->phone_1->is_enabled) ? $cart->user_data['phone'] : '',
                'phone_2_option' => ($fields->phone_2->is_enabled) ? $cart->user_data['phone2'] : '',
                'fax_option' => ($fields->fax->is_enabled) ? $cart->user_data['fax'] : '',
                'address' => ($fields->address->is_enabled) ? trim($cart->user_data['address'] . ' ' . $cart->user_data['address_2']) : '', //trim cleans things up if addr2 not present
                'city' => ($fields->city->is_enabled) ? $cart->user_data['city'] : '',
                'zip_code' => ($fields->zip->is_enabled) ? $cart->user_data['zip'] : '',
                'url_link_1' => ($fields->url_link_1->is_enabled) ? $cart->user_data['url'] : '',
                'mapping_location' => ($fields->mapping_location->is_enabled) ? $mapping_location : '',
                'users_group' => $cart->user_data['group_id'],
                'location' => ($fields->region_level_1->is_enabled) ? geoRegion::getRegionsForUser($cart->user_data['id']) : ''
            );

            //since this is a new item, do not need to check for max listing limit, as it is done for us in init_itemNew
        }


        $this->set('session_variables', $cart->site->session_variables);
        trigger_error('DEBUG CART: End of classified: new session');
    }


    public static function splashCheckVars()
    {
    }
    public static function splashProcess()
    {
    }

    public static function splashDisplay()
    {
        $cart = geoCart::getInstance();

        $sql = "SELECT `place_an_ad_splash_code` as `splash` FROM " . geoTables::groups_table . " WHERE `group_id` = ? LIMIT 1";
        $result = $cart->db->Execute($sql, array($cart->user_data['group_id']));
        if (!$result || $result->RecordCount() == 0) {
            //error getting the splash code
            trigger_error('ERROR SQL CART: Sql: ' . $sql . ' Error Msg: ' . $cart->db->ErrorMsg());
            return false;
        }
        //we only display the splash page once, if at all

        $show = $result->FetchRow();
        if (strlen(trim($show['splash'])) > 0) {
            //display the splash code

            $cart->site->page_id = 8;
            $cart->site->get_text();
            $tpl_vars = $cart->getCommonTemplateVars();
            $tpl_vars['splash'] = geoString::fromDB($show['splash']);
            $tpl_vars['next_text'] = $cart->site->messages[905];
            $tpl_vars['item_name'] = $cart->main_type;

            geoView::getInstance()->setBodyTpl('shared/display_splash.tpl', '', 'order_items')
                ->setBodyVar($tpl_vars);

            $cart->site->display_page();
            return;
        }
        if (!$cart->isCombinedStep()) {
            //no splash code there-- move on by manually calling the display for
            //the next step

            $current_step = $cart->current_step;
            $cart->current_step = $cart->cart_variables['step'] = $cart->getNextStep();
            if ($cart->current_step !== $current_step) {
                //made sure we don't do infinite loop
                return $cart->displayStep();
            }
        }
    }


    public static function categoryCheckVars($listing_types_allowed, $cat_id = 0)
    {
        $cart = geoCart::getInstance();

        if (!$cat_id && isset($_GET['b']) && !is_array($_GET['b'])) {
            //check to see if b is a terminal category, or if user selected to use current category
            $cat_id = (isset($_GET['b'])) ? intval($_GET['b']) : 0;
        }
        if (!$cat_id && isset($_POST['b']['leveled']['cat'])) {
            //Will be an array of selected categories, we want the one
            //furthest "down" that is set, so just keep poping values off the
            //array until we get to a valid value
            $cats = (array)$_POST['b']['leveled']['cat'];
            while ($cats && !$cat_id) {
                $cat_id = (int)array_pop($cats);
            }
        }

        if (!$cat_id && $cart->isCombinedStep()) {
            //if combined step, category selected may be set in order item
            //rather than passed in GET var
            $cat_id = $cart->item->getCategory();
        } elseif ($cat_id && $cart->isCombinedStep() && geoAjax::isAjax()) {
            //for combined step, make sure to set category on the item so subsequent steps
            //can benifit
            $cart->item->setCategory($cat_id);
        }

        if (!$cat_id) {
            if (stripos($_POST['ajax_section_changed'], 'choose_plan') !== false) {
                //this is an ajax event triggered by changing price plans, which occurs higher-up in the form than categpry selection
                //no need to throw an error yet, because user more than likely simply hasn't gotten that far
                //so just silently ignore the fact that no category is selected, and move on
                return;
            }
            //if not in the above case, though, we're trying to validate the form and no category is selected, so show an error
            $msgs = $cart->db->get_text(true, 8);
            $cart->addError()->addErrorMsg('category', $msgs[502092]);
            return;
        }
        if ($cart->db->get_site_setting('place_ads_only_in_terminal_categories') && geoCategory::hasChildren($cat_id)) {
            //this category has children, so set it as the parent and don't allow to continue
            //since admin settings require using a terminal category
            $cart->item->set('parent_category', $cat_id);
            $cart->item->set('terminal_category', false);
            $cart->addError();
            if ($cart->isCombinedStep()) {
                //If it is combined step, add an error
                $msgs = $cart->db->get_text(true, 8);
                $cart->addErrorMsg('category', $msgs[502093]);
            }
            return;
        }
        if (!$cart->isCombinedStep() && (!isset($_GET['c']) || $_GET['c'] !== 'terminal') && geoCategory::hasChildren($cat_id)) {
            //This is the "non-combined" category step using links..  don't proceed until
            //either a category is selected that has no children, or c=terminal_category is set
            //meaning the seller clicked on button to just place listing in that category
            $cart->addError();
            $cart->item->set('parent_category', $cat_id);
            $cart->item->set('terminal_category', false);
            return;
        }

        //made it this far, the category wants to be terminal.  Make sure it's a real category.
        $sql = "SELECT `category_id` FROM " . geoTables::categories_table . " WHERE `category_id`={$cat_id} AND (`listing_types_allowed`=0 OR `listing_types_allowed`=$listing_types_allowed) LIMIT 1";
        $row = $cart->db->getRow($sql);
        if (!$row) {
            //category not found?  set parent cat to 0 and don't allow to continue
            $cart->item->set('parent_category', false);
            $cart->item->set('terminal_category', false);
            $cart->addError();
            return;
        }
        $cart->item->set('terminal_category', $cat_id);
        $cart->item->save();
        if ($cart->isCombinedStep()) {
            //need to set category / price plan in this step, just to be sure...
            //set the category in the item.
            $cart->item->setCategory($cat_id); //set the cat ID as a setting on the main item, so that any other items can access it easy.
            //update the price plan
            $cart->setPricePlan($cart->item->getPricePlan(), $cat_id);
            //just so rest of steps know what to do...
            $cart->site->terminal_category = $cat_id;
            $cart->site->session_variables['category'] = $cat_id;
        }
    }
    public static function categoryProcess()
    {
        $cart = geoCart::getInstance();

        //at this point, the setting checker should have set the cat id as a session var.
        $cat_id = $cart->item->get('terminal_category');

        if (!$cat_id) {
            //hmm, maybe it was set earlier...
            $cat_id = $cart->item->getCategory();
        }

        //category found, must be allowed to place classified in category, category is valid, and is allowed to have listings in it.
        $cart->item->set('parent_category', false); //unset parent, we don't need it any more.
        $cart->item->set('terminal_category', false);

        //set the category in the item.
        $cart->item->setCategory($cat_id); //set the cat ID as a setting on the main item, so that any other items can access it easy.
        //update the price plan
        $cart->setPricePlan($cart->item->getPricePlan(), $cat_id);

        $cart->site->terminal_category = $cat_id;
        $cart->site->session_variables['category'] = $cat_id;
        self::saveFormVariables();
    }

    public static function categoryDisplay($listing_types_allowed, $onlyRecurringClassifieds = false)
    {
        $cart = geoCart::getInstance();

        $cart->site->page_id = 8;
        $cart->site->get_text();

        $tpl_vars = $cart->getCommonTemplateVars();

        $tpl_vars['title2'] = $cart->site->messages[83];

        $recurringClassPricePlan = ($onlyRecurringClassifieds) ? $cart->price_plan['price_plan_id'] : false;

        //TODO: add setting to let it use dropdowns even when not combined
        if ($cart->isCombinedStep()) {
            //set it up for dropdowns...
            $parent_category = $cart->item->getCategory();
            $cat_ids = array();
            $leveled = geoLeveledField::getInstance();
            while ($parent_category > 0 && !in_array($parent_category, $cat_ids)) {
                $cat_ids[] = $parent_category;
                $parent_category = (int)$cart->db->GetOne("SELECT `parent_id` FROM " . geoTables::categories_table . " WHERE `category_id`=?", array($parent_category));
            }

            $externalPre = (defined('IN_ADMIN')) ? '../' : '';
            //we now have array of $cat_ids with "top level" at bottom.. so just loop and pop top one off list each time
            $entry = array();

            //let it know what it is
            $entry['leveled_field'] = 'cat';

            $maxLevel = 1;
            $canEditLeveled = $entry['can_edit'] = true;
            $prevParent = 0;
            $i = 1;
            do {
                $selected = ($cat_ids) ? array_pop($cat_ids) : 0;
                $level_i = "leveled_cat_{$i}";

                $page = (isset($cart->site->session_variables['leveled_page']['cat'][$i])) ? (int)$cart->site->session_variables['leveled_page']['cat'][$i] : 1;
                //failsafe make sure it's at least one
                $page = max($page, 1);
                $value_info = geoCategory::getCategoryLeveledValues($prevParent, $listing_types_allowed, $selected, $page, null, $i, $recurringClassPricePlan);
                if (count($value_info['values']) < 1) {
                    //no values at this level
                    break;
                }
                $maxLevel = $i;
                if ($value_info['maxPages'] > 1) {
                    //pagination
                    $pagination_url = $externalPre . "AJAX.php?controller=LeveledFields&amp;action=getLevel&amp;leveled_field=cat&amp;cat=1&amp;parent={$prevParent}&amp;selected={$selected}&amp;listing_types_allowed=$listing_types_allowed&amp;page=";
                    $value_info['pagination'] = geoPagination::getHTML($value_info['maxPages'], $value_info['page'], $pagination_url, 'leveled_pagination', '', false, false);
                }
                $entry['levels'][$i]['can_edit'] = true;
                $entry['levels'][$i]['value_info'] = $value_info;
                $entry['levels'][$i]['level'] = array('level' => $i);
                $entry['levels'][$i]['page'] = $value_info['page'];
                $prevParent = $selected;
                $i++;
            } while ($prevParent > 0);

            $entry['maxLevel'] = $maxLevel;
            $tpl_vars['cats'] = $entry;
            //die ('entry:<pre>'.print_r($entry,1));

            if (count($tpl_vars['cats']) > 0) {
                //Add CSS for leveled fields
                geoView::getInstance()->addCssFile($externalPre . geoTemplate::getUrl('css', 'system/order_items/shared/leveled_fields.css'));
            }

            $tpl_vars['text1'] = $cart->site->messages[77];

            $tpl_vars['text2'] = $cart->site->messages[80];
            $tpl_vars['help_link'] = $cart->site->display_help_link(84);
            $tpl_vars['desc1'] = $cart->site->messages[76];
            $tpl_vars['desc2'] = '';
            $tpl_vars['listings_only_in_terminal'] = 1;
            $tpl_vars['listing_types_allowed'] = $listing_types_allowed;
            $tpl_vars['recurringClassPricePlan'] = ($recurringClassPricePlan) ? $recurringClassPricePlan : false;
        } else {
            //parent category set by check_vars function
            $parent_category = (int)$cart->item->get('parent_category');

            $categories = self::getCategories($parent_category, $listing_types_allowed, $recurringClassPricePlan);

            $colspan = $cart->db->get_site_setting('sell_category_column_count');
            if (!$colspan) {
                $colspan = 1;
            }

            $tpl_vars['parent_cat_id'] = $parent_category;

            $column_width = floor(100 / $colspan) . '%';
            $tpl_vars['colspan'] = $colspan;
            $tpl_vars['column_width'] = $column_width;
            $tpl_vars['display_cat_image'] = $cart->db->get_site_setting('display_cat_image_listing_process');
            $tpl_vars['display_cat_description'] = $cart->db->get_site_setting('display_cat_description_listing_process');
            $tpl_vars['main_type'] = $cart->main_type;
            $tpl_vars['step'] = $cart->current_step;

            if ($cart->db->get_site_setting('cat_alpha_across_columns')) {
                $tpl_vars['cat_data'] =  $categories;
            } else {
                //Need to re-arrange everything so it's in the different order.

                $categories_x = array_values($categories); // convert associative to numeric array
                $num_cols = $cart->db->get_site_setting('sell_category_column_count');
                $k = 0;
                $num_filled = 0;
                $cats = array();

                $num_cells = count($categories_x);
                $num_rows = (int)($num_cells / $num_cols);
                $num_full_cols = ($num_cells % $num_cols) ? $num_cells % $num_cols : $num_cols;
                if ($num_full_cols != $num_cols) {
                    $num_rows++;
                }
                for ($i = 0; $i < $num_rows; $i++) {
                    $k = $i;
                    for ($j = 0; $j < $num_cols; $j++) {
                        if ($k < $num_cells) {
                            $cats[] = $categories_x[$k];
                            $num_filled++;
                            if ($num_filled >= $num_cells) {
                                break 2;
                            }
                        }
                        $k += ($j < $num_full_cols) ? $num_rows : $num_rows - 1;
                    }
                }
                $tpl_vars['cat_data'] =  $cats;
            }


            //specific to when selecting the sub-category, still assign to give ability to still use in
            //template, even though default template only uses these on sub-categories
            $tpl_vars['text1'] = $cart->site->messages[77];
            $tpl_vars['num_cats'] = count($categories);
            $tpl_vars['text2'] = $cart->site->messages[80];
            if ($parent_category) {
                //specific to only when displaying sub-categories.
                $parent_name = geoCategory::getBasicInfo($parent_category);
                $tpl_vars['help_link'] = $cart->site->display_help_link(85);
                $tpl_vars['desc1'] = $cart->site->messages[79];
                $tpl_vars['parent_cat_name'] = $parent_name['category_name'];

                $tpl_vars['desc2'] = $cart->site->messages[78];
                $tpl_vars['listings_only_in_terminal'] = $cart->db->get_site_setting('place_ads_only_in_terminal_categories');

                //need to display this category as a choice here
                if (!$cart->db->get_site_setting('place_ads_only_in_terminal_categories')) {
                    $tpl_vars['text3'] = $cart->site->messages[82];
                }
            } else {
                //specific to when displaying the main category
                $tpl_vars['help_link'] = $cart->site->display_help_link(84);
                $tpl_vars['desc1'] = $cart->site->messages[76];
                $tpl_vars['desc2'] = '';
                $tpl_vars['listings_only_in_terminal'] = 1;//since main category..
            }
        }

        $tpl_vars['error_msgs'] = $errors = $cart->getErrorMsgs();

        return $tpl_vars;
    }

    /**
     * Used by categoryDisplay to get list of categories for the given parent
     * category.
     *
     * Note that this calls an addon hook, and does things specially
     * for the listing process, so should probably not be moved into geoCategory
     * class.  Or at least if it is, make sure the addon hook is still called and
     * that it is specifically designated as being "for listing process".
     *
     * @param int $parent_category
     * @param int $listing_types_allowed
     * @param int|bool $recurringClassPricePlan either a price plan ID to only show those categories (and their children) for which Recurring Classifieds are enabled on that plan, or bool false to disregard
     * @param int $page The page to get
     * @return array|bool
     */
    public static function getCategories($parent_category, $listing_types_allowed, $recurringClassPricePlan = false)
    {
        $cart = geoCart::getInstance();

        //get the categories in a geoTableSelect query, so we can pass it to an addon hook
        $catTbl = geoTables::categories_table;
        $langTbl = geoTables::categories_languages_table;
        $exclusionTbl = geoTables::category_exclusion_list;
        $query = new geoTableSelect($catTbl);
        $query->from($catTbl, "$catTbl.category_id");



        $cat_img = ($cart->db->get_site_setting('display_cat_image_listing_process')) ? ", $langTbl.category_image" : '';
        $order_by = ($cart->db->get_site_setting('order_choose_category_by_alpha')) ? "$langTbl.category_name" : "$catTbl.display_order, $langTbl.category_name";

        $query->join($langTbl, "$catTbl.category_id = $langTbl.category_id", "$langTbl.category_name{$cat_img}, $langTbl.description")
            ->where("$catTbl.`parent_id` = '$parent_category'", 'parent_id')
            ->where("$langTbl.language_id = '" . $cart->db->getLanguage() . "'", 'language_id');

        if ($listing_types_allowed == 1) {
            $listingType = 'classifieds';
        } elseif ($listing_types_allowed == 2) {
            $listingType = 'auctions';
        } else {
            $listingType = false;
        }
        if ($listingType) {
            //we only want categories that DO NOT have an exclusion for this listing type
            $query->where("NOT EXISTS(SELECT `category_id` FROM $exclusionTbl WHERE $exclusionTbl.`category_id` = $catTbl.`category_id` AND $exclusionTbl.`listing_type` = '$listingType')");
        }

        if ($recurringClassPricePlan) {
            $validCats = geoCategory::getRecurringClassifiedCategoriesForPricePlan($recurringClassPricePlan);
            if (count($validCats) > 0) {
                $query->where("$catTbl.category_id IN (" . implode(',', $validCats) . ")", 'recurring_classified_categories');
            }
        }

        $query->where("$catTbl.`enabled` = 'yes'")
            ->order($order_by);

        //kick the query over to any addons that care to modify which categories are shown
        geoAddon::triggerDisplay('filter_listing_placement_category_query', $query, geoAddon::FILTER);


        $sub_result = $cart->db->Execute('' . $query);
        if (!$sub_result) {
            //echo $sql." is the query<br />\n";
            trigger_error('ERROR SQL CART: Sql: ' . $sql . ' Error Msg: ' . $cart->db->ErrorMsg());
            $cart->site->error_message = $cart->site->messages[57];
            return false;
        }

        return $sub_result->GetAll();
    }

    public static function detailsCheckVars($save_session_vars = true)
    {
        $cart = geoCart::getInstance();

        if (!(isset($_REQUEST['b']) && is_array($_REQUEST['b']))) {
            //nothing submitted
            trigger_error('DEBUG CART: B not set or not an array, so cannot check vars.');
            $cart->addError();
            return;
        }
        $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());

        $cart->site->error = 0;
        $cart->site->terminal_category = $cart->item->getCategory();
        $cart->site->get_badword_array();
        $cart->site->get_html_disallowed_array();

        $cart->site->get_form_variables($_REQUEST["b"]);

        $cart->site->field_configuration_data = self::getFieldConfig();

        //save geonav values
        //since these are in a separate array from everything else, make sure they're actually set before changing things
        if (isset($_REQUEST['geoRegion_location'])) {
            $geographicOverrides = geoRegion::getLevelsForOverrides();
            $geographicRegions = $_REQUEST['geoRegion_location'];
            $cart->site->session_variables['location'] = $geographicRegions;
            //if it's set, save the 'city' value from regions in its own field
            if ($geographicOverrides['city']) {
                $city = $geographicRegions[$geographicOverrides['city']];
                $cart->site->session_variables['city'] = geoRegion::getNameForRegion($city);
            }
        }

        //Let order items run their own checkvars here
        geoOrderItem::callUpdate('detailsCheckVars_getMoreDetails', self::$_type, null, true);
        //let "end" also happen
        geoOrderItem::callUpdate('detailsCheckVars_getMoreDetailsEnd', self::$_type, null, true);
        //also for location section
        geoOrderItem::callUpdate('detailsCheckVars_getMoreDetailsLocation', self::$_type, null, true);
        geoAddon::triggerUpdate('listing_placement_moreDetailsLocation_append_checkVars', self::$_type);

        //make sure min bid is > 0
        if ($cart->site->session_variables['auction_minimum'] <= 0) {
            $cart->site->session_variables['auction_minimum'] = 0.01;
        }
        $cart->site->get_category_questions(0, $cart->item->getCategory());
        $cart->site->check_extra_questions();

        //pull business type from userdata
        $cart->site->session_variables['business_type'] = geoUser::getData($cart->order->getBuyer(), 'business_type');

        self::checkCostOptions();


        self::saveFormVariables();
        if (!$cart->site->classified_detail_check(0, $cart->site->terminal_category, ($save_session_vars === 'skipSave'))) {
            trigger_error('DEBUG CART:classified detail check returned false. errors: <pre>' . print_r($cart->site->field_configuration_data, 1) . '</pre>');
            $cart->addError();
            //$cart->site->save_form_variables($db);
            //$cart->site->display_classified_detail_form();
        } else {
            trigger_error('DEBUG CART: No errors found, should not be a problem.');
            //save any changes made in detail check
            if ($save_session_vars !== 'skipSave') {
                $cart->item->set('session_variables', $cart->site->session_variables);
            }
        }

        $secure = geoAddon::getUtil('security_image');
        $anon = geoAddon::getUtil('anonymous_listing');
        if ($secure && (((!self::isAnonymous() || (self::isAnonymous() && !$anon && !$secure->check_setting('login'))) && $secure->check_setting('listing')) || (self::isAnonymous() && $secure->check_setting('listing_anon')) )) {
            if (!$secure->check_security_code($_REQUEST['b']["securityCode"])) {
                $security_text =& geoAddon::getText('geo_addons', 'security_image');
                $cart->addErrorMsg('securityCode', $security_text['error']);
                $cart->addError();
            }
        }

        if (geoPC::is_ent() && $cart->site->sell_type == 2) {
            //On-Site Payment Types
            geoSellerBuyer::callUpdate('listings_placement_common_detailsCheckVars');
        }
    }
    public static function detailsProcess($noSetCost = false)
    {
        $cart = geoCart::getInstance();
        $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());
        if (!$noSetCost) {
            $cart->item->setCost(self::getListingCost());
        }
        if ($cart->site->sell_type == 2) {
            //On-Site Payment Types
            geoSellerBuyer::callUpdate('listings_placement_common_detailsProcess');
        }
        //Let order items run their own checkvars here
        geoOrderItem::callUpdate('detailsProcess_getMoreDetails', self::$_type, null, true);
        //let "end" also happen
        geoOrderItem::callUpdate('detailsProcess_getMoreDetailsEnd', self::$_type, null, true);
        //also for location
        geoOrderItem::callUpdate('detailsProcess_getMoreDetailsLocation', self::$_type, null, true);
        geoAddon::triggerUpdate('listing_placement_moreDetailsLocation_append_process', self::$_type);

        self::saveFormVariables();
        return false;
    }

    public static function getFieldConfig()
    {
        //TODO: Don't get settings not used any more
        $cart = geoCart::getInstance();

        $cart->site->site_category = $catId = $cart->item->getCategory();
        $groupId = 0;
        if ($cart->user_data['group_id']) {
            $groupId = (int)$cart->user_data['group_id'];
        }

        $cart->site->fields = geoFields::getInstance($groupId, $catId);

        $cart->site->category_configuration = $cat = geoCategory::getCategoryConfig($catId, true);
        $cart->site->get_ad_configuration();

        //for easy access to edit switches
        $config = $cart->site->ad_configuration_data;

        $field_config = $cart->db->GetRow("SELECT * FROM " . geoTables::ad_configuration_table);
        $site_settings_get = array ('allow_standard', 'allow_dutch',
            'user_set_auction_start_times','user_set_auction_end_times');

        if ($cat && $cat['what_fields_to_use'] != 'site') {
            //using category-specific fields settings
            $field_config = array_merge($field_config, $cat);
        }

        foreach ($site_settings_get as $setting) {
            $field_config[$setting] = $cart->db->get_site_setting($setting);
        }

        return $field_config;
    }

    public static function detailsDisplay()
    {
        $cart = geoCart::getInstance();
        $view = geoView::getInstance();

        $cart->site->page_id = 9;
        $cart->site->get_text();
        $cart->site->terminal_category = $cart->item->getCategory();

        $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());

        $field_config = $cart->site->field_configuration_data = self::getFieldConfig();
        $tpl_vars = $cart->getCommonTemplateVars();
        //Do stuff for view class
        $externalPre = (defined('IN_ADMIN')) ? '../' : '';
        $view->addJScript($externalPre . geoTemplate::getUrl('js', 'listing_placement.js')); //load js functions for this page

        $cart->site->get_currency_info();
        if (self::$_type == 'listing_edit') {
            self::$editing = true;
            self::$isAdmin = $cart->item->get('adminEdit', false);
        }

        $tpl_vars['field_config'] = $field_config;
        $fields = geoFields::getInstance($cart->user_data['group_id'], $cart->item->getCategory());

        //if using JIT, email is always present and required, regardless of admin settings
        //check here to make sure the field appears -- site_class_temp::classified_detail_check makes it always required
        if ($cart->user_data['id'] == 0 && $cart->db->get_site_setting('jit_registration')) {
            $fields->email->is_enabled = true;
        }
        $tpl_vars['fields'] = $fields;

        //add js to top for script that displays num of chars left
        $view->addTop("
<script type='text/javascript'>
	var max_length = {$fields->description->text_length};
</script>");
        $tpl_vars['max_length_description'] = $fields->description->text_length;

        $tpl_vars['txt2'] = $cart->site->messages[640];
        $tpl_vars['txt3'] = $cart->site->messages[639];
        $tpl_vars['is_ent'] = geoPC::is_ent();
        $tpl_vars['user_data'] = $cart->user_data;
        if (!($cart->isCombinedStep() && geoAjax::isAjax())) {
            //Set error messages, IF this is not combined step ajax call...
            //Don't want to show errors just because category was set and they haven't
            //gotten to setting the required fields yet.

            $errors = $cart->getErrorMsgs();
            $errors = array_merge($errors, $cart->site->error_variables);
            $tpl_vars['error_msgs'] = $errors;
        }

        $tpl_vars['userId'] = (int)$cart->user_data['id'];
        $tpl_vars['adminId'] = (defined('IN_ADMIN')) ? geoSession::getInstance()->getUserId() : 0;

        $tpl_vars['category_tree'] = geoCategory::getTree($cart->item->getCategory());

        //give info about category
        $tpl_vars['category_data'] = geoCategory::getBasicInfo($cart->item->getCategory());

        $tpl_vars['sell_type'] = $cart->site->sell_type;
        $tpl_vars['editCheck'] = self::_editCheck(false);
        $tpl_vars['pricePlan'] = $cart->price_plan;
        $tpl_vars['session_variables'] = $cart->site->session_variables;

        $tpl_vars['use_auto_title'] = ((isset($cart->site->category_configuration['use_auto_title']) && $cart->site->category_configuration['use_auto_title']) || $cart->db->get_site_setting('use_sitewide_auto_title'));

        if ($cart->site->sell_type == 1 && self::_editCheck(false) && !$cart->site->recurring_classified_details) {
            //classified duration
            if ($cart->price_plan['charge_per_ad_type'] == 2) {
                $tpl_vars['duration_dropdown'] = $cart->site->display_charge_by_duration_dropdown(true);
            } else {
                $tpl_vars['duration_dropdown'] = $cart->site->display_basic_duration_dropdown(true);
            }
        } else {
            $tpl_vars['duration_dropdown'] = '';
        }
        $tpl_vars['use_textarea_in_title'] = $cart->db->get_site_setting('use_textarea_in_title');
        $tpl_vars['display_description_last_in_form'] = $cart->db->get_site_setting('display_description_last_in_form');
        $width = $cart->db->get_site_setting("desc_wysiwyg_width");
        $height = $cart->db->get_site_setting('desc_wysiwyg_height');
        $tpl_vars['desc_wysiwyg_width'] = ($width) ? $width : '700';//default to 700px
        $tpl_vars['desc_wysiwyg_height'] = ($height) ? $height : '280';//default to 280px

        $tpl_vars['use_rte'] = $cart->db->get_site_setting('use_rte');

        //signal to use editor, if turned on
        $view->editor = 1;

        $textareawrap = (is_object($cart->site->ad_configuration_data)) ? $cart->site->ad_configuration_data->TEXTAREA_WRAP : $cart->site->ad_configuration_data['textarea_wrap'];
        if ($textareawrap) {
            $desc_clean = geoString::specialChars(preg_replace('/<br[\s]*\/?>/i', " \n", $cart->site->session_variables["description"]));
        } else {
            $desc_clean = geoString::specialChars($cart->site->session_variables["description"]);
        }
        $tpl_vars['desc_clean'] = $desc_clean;

        if ($fields->tags->is_enabled) {
            //help text for tags
            $tpl_vars['tags_help_link'] = $cart->site->display_help_link(500864);
        }

        //if editing an auction, find out if we can edit price fields
        $editAuctionPrices = true;
        if (self::$editing && $cart->site->sell_type == 2) {
            //find bids
            $sql = "SELECT count(*) FROM `geodesic_auctions_bids` WHERE `auction_id` = " . $cart->site->session_variables['listing_id'];
            $bidsExist = ($cart->db->GetOne($sql) > 0);

            $listing = geoListing::getListing($cart->site->session_variables['listing_id']);

            if ($listing->live == 1 && ($bidsExist || !$cart->db->get_site_setting('edit_auction_prices'))) {
                $editAuctionPrices = false;
                if ($cart->db->get_site_setting('edit_auction_prices_bno') && $listing->buy_now_only) {
                    $editAuctionPrices = true;
                }
            }
        }
        $tpl_vars['editAuctionPrices'] = $editAuctionPrices;

        $tpl_vars['currency_type'] = $cart->site->session_variables['currency_type'];
        $tpl_vars['currencies'] = $cart->db->GetAll("SELECT `type_id`, `precurrency`, `postcurrency` FROM " . geoTables::currency_types_table . " ORDER BY `display_order`");
        $tpl_vars['currencies_count'] = count($tpl_vars['currencies']);

        if ($cart->site->sell_type == 2 && $editAuctionPrices) {
            $tpl_vars['auction_type_help_link'] = $cart->site->display_help_link(200172);

            $current_time = geoUtil::time();
            if ($cart->db->get_site_setting('user_set_auction_start_times') && self::_editCheck(false)) {
                if ($cart->site->session_variables["start_time"] < $current_time) {
                    $current_start_time = $current_time;
                } else {
                    $current_start_time = $cart->site->session_variables["start_time"];
                }
                //TODO: Convert this to smarty
                $cart->site->return_value = true;
                $tpl_vars['date_select_start_time'] = $cart->site->get_date_select("b[start_time][start_year]", "b[start_time][start_month]", "b[start_time][start_day]", "b[start_time][start_hour]", "b[start_time][start_minute]", $current_start_time);
            }

            // auction end time
            if ($cart->db->get_site_setting('user_set_auction_end_times') && $cart->price_plan['charge_per_ad_type'] != 2 && self::_editCheck(false)) {
                if ($cart->site->session_variables["end_time"] < $current_time) {
                    $current_end_time = $current_time + 86400; //default end-time to tomorrow
                } else {
                    $current_end_time = $cart->site->session_variables["end_time"];
                }
                //TODO: Convert this to smarty
                if (is_array($current_end_time)) {
                    //need to convert end time from array to ticktime before passing to get_date_select()
                    $current_end_time = $cart->site->get_time($current_end_time['end_hour'], $current_end_time['end_minute'], $current_end_time['end_month'], $current_end_time['end_day'], $current_end_time['end_year']);
                }
                $cart->site->return_value = true;
                $tpl_vars['date_select_end_time'] = $cart->site->get_date_select("b[end_time][end_year]", "b[end_time][end_month]", "b[end_time][end_day]", "b[end_time][end_hour]", "b[end_time][end_minute]", $current_end_time, 0, 0, 0, 0, 0, true);
            }

            //auction duration
            if (self::_editCheck(false)) {
                trigger_error('DEBUG CART: About to get auction dropdown');
                if ($cart->price_plan['charge_per_ad_type'] == 2) {
                    $tpl_vars['auction_duration_dropdown'] = $cart->site->display_charge_by_duration_dropdown(true);
                } else {
                    $tpl_vars['auction_duration_dropdown'] = $cart->site->display_basic_duration_dropdown(true);
                }
            }
            if (!$cart->price_plan['buy_now_only']) {
                //BIDDING

                $tpl_vars['bno'] = (($cart->price_plan['buy_now_only'] || $cart->site->session_variables['buy_now_only']) && geoPC::is_ent()) ? 1 : 0;
                $tpl_vars['is_dutch'] = ($cart->site->session_variables['auction_type'] == 2) ? 1 : 0;

                //minimum bid
                if (!$cart->site->session_variables['auction_minimum']) {
                    $cart->site->session_variables['auction_minimum'] = $tpl_vars['session_variables']['auction_minimum'] = 0.01;
                }
            }

            if ($fields->cost_options->is_enabled) {
                //cost options limits
                $cost_option_limits = explode('|', $fields->cost_options->type_data);

                if (count($cost_option_limits) == 2) {
                    $tpl_vars['cost_option_max_groups'] = (int)$cost_option_limits[0];
                    $tpl_vars['cost_option_max_options'] = (int)$cost_option_limits[1];
                    //it needs to know how many (if any) images...
                    $tpl_vars['maxImages'] = imagesOrderItem::getMaxImages();
                } else {
                    $tpl_vars['cost_option_max_groups'] = $tpl_vars['cost_option_max_options'] = 0;
                }
            }
        } // end auctions price edit section

        if ($cart->site->sell_type == 2 && geoPC::is_ent()) {
            //On-Site Payment Types
            $tpl_vars['on_site_html'] = geoSellerBuyer::callDisplay('listings_placement_common_detailsDisplay', null, '<br />');
        }

        // Off-Site payment types
        if ($fields->payment_types->is_enabled) {
            $sql = "SELECT * FROM " . geoTables::auction_payment_types_table . " ORDER BY `display_order`";
            $tpl_vars['payment_options'] = $cart->db->GetAll($sql);

            if (!is_array($cart->site->session_variables["payment_options"])) {
                $cart->site->session_variables["payment_options"] = $tpl_vars['session_variables']['payment_options'] = explode("||", $cart->site->session_variables["payment_options"]);
            }
        }

        //leveled fields
        $leveled = geoLeveledField::getInstance();

        $leveled_ids = $leveled->getLeveledFieldIds();
        if ($leveled_ids) {
            $tpl_vars['leveled_fields'] = array();
            foreach ($leveled_ids as $lev_id) {
                $level_1 = "leveled_{$lev_id}_1";
                if ($fields->$level_1->is_enabled) {
                    $entry = array();
                    //put together each of the indexes, it's easier to do in PHP
                    //than in smarty
                    $entry['level_1'] = $level_1;
                    $entry['error'] = "leveled_{$lev_id}";

                    //let it know what it is
                    $entry['leveled_field'] = $lev_id;

                    $maxLevelEver = $leveled->getMaxLevel($lev_id, true);
                    $maxLevel = 1;
                    $canEditLeveled = $entry['can_edit'] = self::_editCheck($fields->$level_1->can_edit);
                    $prevParent = 0;

                    for ($i = 1; $i <= $maxLevelEver; $i++) {
                        $level_i = "leveled_{$lev_id}_{$i}";
                        if ($fields->$level_i->is_enabled) {
                            $maxLevel = $i;
                        } else {
                            //we reached limit to enabled ones
                            break;
                        }
                        $selected = (isset($cart->site->session_variables['leveled'][$lev_id][$i])) ? $cart->site->session_variables['leveled'][$lev_id][$i] : 0;
                        //now then, if can edit it...
                        if ($canEditLeveled && ($i == 1 || $selected)) {
                            //can edit, so populate the first level
                            $page = (isset($cart->site->session_variables['leveled_page'][$lev_id][$i])) ? (int)$cart->site->session_variables['leveled_page'][$lev_id][$i] : 1;
                            $page = max($page, 1);
                            $value_info = $leveled->getValues($lev_id, $prevParent, $selected, $page);
                            if ($value_info['maxPages'] > 1) {
                                //pagination
                                $pagination_url = $externalPre . "AJAX.php?controller=LeveledFields&amp;action=getLevel&amp;leveled_field=$lev_id&amp;parent={$prevParent}&amp;selected=0&amp;page=";
                                $value_info['pagination'] = geoPagination::getHTML($value_info['maxPages'], $value_info['page'], $pagination_url, 'leveled_pagination', '', false, false);
                            }
                            $entry['levels'][$i]['can_edit'] = true;
                            $entry['levels'][$i]['value_info'] = $value_info;
                            $entry['levels'][$i]['page'] = $value_info['page'];
                            $prevParent = $selected;
                        } elseif (!$canEditLeveled) {
                            //show the value instead of letting them change it, get "just" the selected
                            $value = $leveled->getValueInfo($selected);
                            $value['selected'] = true;
                            $entry['levels'][$i]['can_edit'] = false;
                            $entry['levels'][$i]['value_info']['values'][$value['id']] = $value;
                            if (!$value['name']) {
                                //this is an edit, but this field cannot be edited
                                //further, no pre-existing value is selected.
                                //therefore, skip this field entirely, to avoid showing empty boxen
                                continue 2;
                            }
                            //page will always be 1... go ahead and set just for completeness / consistency
                            $entry['levels'][$i]['page'] = 1;
                        }
                        $entry['levels'][$i]['level'] = $leveled->getLevel($lev_id, $i, $cart->db->getLanguage());
                    }
                    $entry['maxLevel'] = $maxLevel;
                    $tpl_vars['leveled_fields'][$lev_id] = $entry;
                }
            }
            if (count($tpl_vars['leveled_fields']) > 0) {
                //Add CSS for leveled fields
                $view->addCssFile($externalPre . geoTemplate::getUrl('css', 'system/order_items/shared/leveled_fields.css'));
                $lvlText = DataAccess::getInstance()->get_text(true, 44);
                $tpl_vars['leveled_clear_selection_text'] = $lvlText[502065] ? $lvlText[502065] : false;
            }
        }


        //init the new geographic selector
        $maxLocationDepth = 0;
        for ($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
            $field = 'region_level_' . $r;
            if ($fields->$field && $fields->$field->is_enabled) {
                $maxLocationDepth = $r;
                break;
            }
        }
        $tpl_vars['region_selector'] = geoRegion::regionSelector('geoRegion_location', $cart->site->session_variables['location'], $maxLocationDepth);
        //also pass in which levels correspond to city/state/country
        $tpl_vars['geographicOverrides'] = geoRegion::getLevelsForOverrides();


        if (geoPC::is_ent()) {
            $tpl_vars['add_cost_at_top'] = $cart->db->get_site_setting('add_cost_at_top');

            $tpl_vars['opt_field_info'] = array(
            1 => array('label' => $cart->site->messages[909],'error' => $cart->site->messages[910],'or' => $cart->site->messages[133], 'field' => $fields->optional_field_1),
            2 => array('label' => $cart->site->messages[941], 'error' => $cart->site->messages[942], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_2),
            3 => array('label' => $cart->site->messages[943], 'error' => $cart->site->messages[944], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_3),
            4 => array('label' => $cart->site->messages[945], 'error' => $cart->site->messages[946], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_4),
            5 => array('label' => $cart->site->messages[947], 'error' => $cart->site->messages[948], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_5),
            6 => array('label' => $cart->site->messages[949], 'error' => $cart->site->messages[950], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_6),
            7 => array('label' => $cart->site->messages[951], 'error' => $cart->site->messages[952], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_7),
            8 => array('label' => $cart->site->messages[953], 'error' => $cart->site->messages[954], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_8),
            9 => array('label' => $cart->site->messages[955], 'error' => $cart->site->messages[956], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_9),
            10 => array('label' => $cart->site->messages[957], 'error' => $cart->site->messages[958], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_10),
            11 => array('label' => $cart->site->messages[1903], 'error' => $cart->site->messages[1904], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_11),
            12 => array('label' => $cart->site->messages[1905], 'error' => $cart->site->messages[1906], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_12),
            13 => array('label' => $cart->site->messages[1907], 'error' => $cart->site->messages[1908], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_13),
            14 => array('label' => $cart->site->messages[1909], 'error' => $cart->site->messages[1910], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_14),
            15 => array('label' => $cart->site->messages[1911], 'error' => $cart->site->messages[1912], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_15),
            16 => array('label' => $cart->site->messages[1913], 'error' => $cart->site->messages[1914], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_16),
            17 => array('label' => $cart->site->messages[1915], 'error' => $cart->site->messages[1916], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_17),
            18 => array('label' => $cart->site->messages[1917], 'error' => $cart->site->messages[1918], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_18),
            19 => array('label' => $cart->site->messages[1919], 'error' => $cart->site->messages[1920], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_19),
            20 => array('label' => $cart->site->messages[1921], 'error' => $cart->site->messages[1922], 'or' => $cart->site->messages[133], 'field' => $fields->optional_field_20)
            );
            $insertedDate = false;
            for ($i = 1; $i < 21; $i++) {
                if (!$errors['optional_field_' . $i]) {
                    //no error to show for this field -- clear the tpl var
                    $tpl_vars['opt_field_info'][$i]['error'] = '';
                }
                $tpl_vars['opt_field_info'][$i]['value'] = $cart->site->session_variables["optional_field_" . $i];
                $tpl_vars['opt_field_info'][$i]['other_box'] = (strpos($tpl_vars['opt_field_info'][$i]['field']->type_data, ':use_other') !== false);

                $field_type = $tpl_vars['opt_field_info'][$i]['field']->field_type;
                if ($field_type == 'textarea') {
                    //format the textarea to display newlines and things correctly.
                    $tpl_vars['opt_field_info'][$i]['value'] = preg_replace('/<br[\s]*\/?>/i', " \n", $tpl_vars['opt_field_info'][$i]['value']);
                } elseif ($field_type == 'date') {
                    $tpl_vars['opt_field_info'][$i]['value'] = geoCalendar::toInput($tpl_vars['opt_field_info'][$i]['value']);
                    if (!$insertedDate) {
                        //add calendar library
                        geoCalendar::init();
                        $insertedDate = true;
                    }
                } elseif ($field_type == 'dropdown') {
                        //type_data is in form ##:use_other - so inval of it will take off :use_other and just
                        //leave the # which is the dropdown type number.
                        $type = intval($tpl_vars['opt_field_info'][$i]['field']->type_data);
                        $sql = "SELECT * FROM " . geoTables::sell_choices_table . " WHERE `type_id` = " . $type . " ORDER BY `display_order`,`value`";
                        $tpl_vars['optional_types'][$i] = $cart->db->GetAll($sql);
                }
            }
        }
        if (self::_editCheck($field_config['editable_category_specific'])) {
            //get and display category questions
            $cart->site->get_questions($cart->site->terminal_category, $cart->user_data['group_id']);
            $unordered_questions = $cart->site->questions;

            //get them to be in order...
            $questions = array();
            foreach ($unordered_questions as $question) {
                $key = $question['question_id'];
                $display_order = $question['display_order'];
                $name = $question['name'];
                $choices = array();
                $help_link = '';
                if (is_numeric($question['choices']) && $question['choices']) {
                    $sql = "SELECT * FROM " . geoTables::sell_choices_table . " WHERE `type_id` = ? ORDER BY display_order,value";
                    $choices = $cart->db->GetAll($sql, array($question['choices']));
                }
                if ($question['choices'] == 'textarea' && $textareawrap) {
                    $question_val = (isset($tpl_vars['session_variables']['question_value'][$key])) ? $tpl_vars['session_variables']['question_value'][$key] : '';
                    $cart->site->session_variables['question_value'][$key] = $tpl_vars['session_variables']['question_value'][$key] = preg_replace('/<br[\s]*\/?>/i', " \n", $question_val);
                    //let tpl special char it
                }
                if ($question['choices'] == 'date') {
                    $question_val = (isset($tpl_vars['session_variables']['question_value'][$key])) ? geoCalendar::toInput($tpl_vars['session_variables']['question_value'][$key]) : '';
                    $tpl_vars['session_variables']['question_value'][$key] = $question_val;
                    geoCalendar::init();
                }
                if ($question['explanation']) {
                    $help_link = $cart->site->display_help_link(0, 0, 0, $key);
                }
                $questions[$display_order][] = array (
                    'key' => $key,
                    'name' => $name,
                    'type' => $question['choices'],
                    'choices' => $choices,
                    'other_box' => $question['other_input'],
                    'help' => $help_link
                );
            }
            ksort($questions);
            $tpl_vars['questions'] = $questions;
        }


        $secure = geoAddon::getUtil('security_image');
        $anon = geoAddon::getUtil('anonymous_listing');
        if ($secure && (((!self::isAnonymous() || (self::isAnonymous() && !$anon && !$secure->check_setting('login'))) && $secure->check_setting('listing')) || (self::isAnonymous() && $secure->check_setting('listing_anon')) )) {
            //if anonymous addon is in use, look for listing_anon
            //if anonymous addon is not in use but user isAnonymous (not logged in), this is JIT -- use "normal" listing setting UNLESS the login image is on
            $section = ($anon && self::isAnonymous()) ? 'listing_anon' : 'listing';
            $tpl_vars['security_image'] = $secure->getHTML($errors['securityCode'], null, $section, false);
            $view->addTop($secure->getJs());
        }

        //allow insert right below description
        $tpl_vars['moreDetails'] = geoOrderItem::callDisplay('detailsDisplay_getMoreDetails', self::$_type, 'array', null, true);
        //insert at end, right before next step and cancel buttons.
        $tpl_vars['moreDetailsEnd'] = geoOrderItem::callDisplay('detailsDisplay_getMoreDetailsEnd', self::$_type, 'array', null, true);
        //For the location section
        $tpl_vars['moreDetailsLocation'] = geoOrderItem::callDisplay('detailsDisplay_getMoreDetailsLocation', self::$_type, 'array', null, true);
        $tpl_vars['moreDetailsLocation'] += geoAddon::triggerDisplay('listing_placement_moreDetailsLocation_append', self::$_type, geoAddon::ARRAY_ARRAY);
        //allow addons to add to pricing section
        $tpl_vars['moreDetailsPricing'] = geoAddon::triggerDisplay('listing_placement_moreDetailsPricing_append', self::$_type, geoAddon::ARRAY_ARRAY);

        $cart->addPreviewTemplateVars($tpl_vars);

        return $tpl_vars;
    }

    public static function mediaCheckVars()
    {
        if (!isset($_POST['media_submit_form'])) {
            //form not submitted, cannot continue process.
            $cart = geoCart::getInstance();
            $cart->addError();
            if (!isset($_GET['f']) && !isset($_GET['g'])) {
                //Do NOT stop children from being called if f and g are set, as
                //that is special case for legacy uploader, to delete an image
                return;
            }
        }

        $children = geoOrderItem::getChildrenTypes(self::$_type);
        geoOrderItem::callUpdate('mediaCheckVars', null, $children);
    }

    public static function mediaProcess()
    {
        $children = geoOrderItem::getChildrenTypes(self::$_type);
        geoOrderItem::callUpdate('mediaProcess', null, $children);
    }

    public static function mediaDisplay()
    {
        $cart = geoCart::getInstance();

        $cart->site->page_id = 10;
        $cart->site->get_text();

        $tpl_vars = $cart->getCommonTemplateVars();

        //set all the common vars that media thingies might need to know
        $tpl_vars['main_type'] = $cart->main_type;

        //let all the different media order items do their thing for this step
        $children = geoOrderItem::getChildrenTypes(self::$_type);
        $tpl_vars['mediaTemplates'] = geoOrderItem::callDisplay('mediaDisplay', 'tpl', 'array', $children);
        //die ('tpls: <pre>'.print_r($tpl_vars['mediaTemplates'],1));

        $tpl_vars['error_msgs'] = $cart->getErrorMsgs();
        $cart->addPreviewTemplateVars($tpl_vars);

        geoView::getInstance()->setBodyTpl('shared/media.tpl', '', 'order_items')
            ->setBodyVar($tpl_vars)
            ->addJScript(geoTemplate::getUrl('js', 'listing_placement.js'));
        $cart->site->display_page();
    }

    public static function mediaLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500501];
    }

    /**
     * easy way to find out if a field may be edited
     *
     * @return bool true if good to show field, false if not
     */
    protected static function _editCheck($fieldToCheck)
    {
        if (!self::$editing) {
            return true;
        } elseif (self::$isAdmin) {
            return true;
        }
        return $fieldToCheck;
    }

    public static function geoCart_other_detailsCheckVars()
    {
        $cart = geoCart::getInstance();
        if ($cart->main_type != self::$_type) {
            //not right type, so not concerned about this one.

            return ;
        }

        //but children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::$_type);
        //echo 'running for type '.self::$_type.'<br />';
        geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $children);

        self::saveFormVariables();
    }

    public static function geoCart_other_detailsProcess()
    {
        $cart = geoCart::getInstance();
        if ($cart->main_type != self::$_type) {
            //not right type, so not concerned about this one.

            return ;
        }

        //But children might, get steps from children as well.
        $children = geoOrderItem::getChildrenTypes(self::$_type);
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
        $cart = geoCart::getInstance();

        //See if this is a classified or not (as opposed to auction).
        if ($cart->main_type != self::$_type) {
            //not classified, so not concerned about this one.

            return ;
        }
        $cart->setPricePlan($cart->item->getPricePlan(), $cart->item->getCategory());

        //figure out if we should show this one or not
        if (
            !$cart->site->debug_show_all_options && (!geoMaster::is('site_fees')
            || !($cart->price_plan['type_of_billing'] == 1 || !(geoPC::is_ent() || geoPC::is_premier())))
        ) {
            //not not concerned about displaying anything for this one.
            return '';
        }

        //this is classified, figure out what to display.
        $tpl = new geoTemplate('system', 'order_items');
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

        $return['title'] = ($cart->site->messages[197]);
        $return ['price_display'] = geoString::displayPrice(self::getListingCost(), false, false, 'cart');

        $tpl->assign('title', $return['title']);
        $return['left'] = $tpl->fetch('shared/other_details.left.tpl');
        $cart->item->setCost(self::getListingCost());
        return $return;
    }


    /**
     * Optional.  Required if in getDisplayDetails() you returned true for the array index of canPreview
     *
     */
    public function geoCart_previewDisplay($sell_type)
    {
        $cart = geoCart::getInstance();

        $cart->site->session_id = $cart->item->getId();

        if ($cart->item->renew_upgrade != self::upgrade && !$cart->item->get('live')) {
            self::_insertListing($cart->item, $sell_type);
        }
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

        $cart->site->display_classified($cart->site->classified_id);
    }

    public static function geoCart_payment_choicesProcess($sell_type)
    {
        $cart = geoCart::getInstance();

        $items = $cart->order->getItem(self::$_type);
        if (!is_array($items) || !count($items)) {
            //no classifieds in order
            return;
        }

        foreach ($items as $item) {
            if (is_object($item)) {
                $cart->initItem($item->getId());
                if ($cart->item->renew_upgrade != self::upgrade && !$cart->item->get('live')) {
                    self::_insertListing($item, $sell_type);
                }
            }
        }
        //un-do initItem
        $cart->cart_variables['order_item'] = 0;
        $cart->item = null;
        $cart->main_type = $cart->cart_variables['main_type'] = 'cart';
    }


    public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false, $skipToParent = false)
    {
        if ($skipToParent) {
            //really just want to skip on to the parent, don't doddle and do normal listing stuff
            return parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
        }
        if ($newStatus == $this->getStatus()) {
            return;
        }

        $db = DataAccess::getInstance();

        trigger_error('DEBUG CART: Top of processStatusChange');

        $before = $this->getStatus();

        $session_variables = $this->get('session_variables');

        $current_time = geoUtil::time();
        //figure out when ad ends
        if ($session_variables['end_mode'] == 1) {
            //end time set explicitly
            //IMPORTANT: this must be 'end_time' (the user-entered time) and NOT 'ends' (which hasn't always been set yet, at this point)
            $ends = $session_variables['end_time'];
        } else {
            //end time set by duration
            $duration = intval($session_variables['classified_length']);
            if (!$duration && isset($session_variables['duration'])) {
                $duration = intval($session_variables['duration']);
            }
            $length_of_ad = intval($duration * 86400);

            if ($duration == 0) {
                //unlimited duration. set end time to 0 to represent this
                $ends = 0;
            } elseif ($session_variables['start_time'] > $current_time) {
                //this is an auction with "start time" set in the future
                //add duration to the actual auction start instead of whenever this happens to go live
                $ends = $session_variables['start_time'] + $length_of_ad;
            } else {
                //just add duration to now, as normal
                $ends = $current_time + $length_of_ad;
            }
            $session_variables['ends'] = $ends;
        }


        trigger_error('DEBUG TRANSACTION: classified - length of ad: ' . $length_of_ad . " current time: " . $current_time);
        $live = ($newStatus == 'active') ? 1 : 0;

        if (!$this->get('listing_id')) {
            //FAILSAFE: weird, listing ID not set so listing not created yet
            //(this is not typical)... Go ahead and create it now
            $this->set('session_variables', $session_variables);
            self::_insertListing($this, (($this->getType() == 'classified') ? 1 : 2));
            $session_variables = $this->get('session_variables');
        }

        //use geoListing to set these values, so that the filter check has the correct info later
        $listing = geoListing::getListing($this->get('listing_id'));
        $listing->live = $live;
        $listing->date = $current_time;
        $listing->ends = $ends;
        //don't manipulate start_time or end_time here, because they represent user selections, not computations!

        //save the info we just set in session vars
        $session_variables['ends'] = $ends;
        $session_variables['date'] = $current_time;
        //NOTE: we don't save live setting in session vars when placing the listing.
        $this->set('session_variables', $session_variables);

        if ($this->get('is_recurring')) {
            //initialize recurring billing
            $order = $this->getOrder();
            $recurringId = 0;
            if ($order && $order->getRecurringBilling()) {
                $recurring = $order->getRecurringBilling();
                //save the user id
                $recurring->setUserId($order->getBuyer());
                $recurring->save();
                $recurringId = $recurring->getId();
                $db->Execute("REPLACE INTO " . geoTables::listing_subscription . " (`recurring_id`, `listing_id`) VALUES (?,?)", array($recurringId, $listing->id));
            }
        }


        trigger_error('DEBUG CART TRANSACTION: classified:processStatusChange');
        if ($updateCategoryCount) {
            geoCategory::updateListingCount($this->getCategory());
        }

        geoAddon::triggerUpdate('listing_placement_processStatusChange', array('listing' => $listing, 'session_variables' => $session_variables));

        trigger_error('DEBUG CART TRANSACTION: classified:processStatusChange() - after update cat count');
        //call parent before trying to send e-mails in case there are problems with e-mails
        parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
        if ($sendEmailNotices && $newStatus == 'active') {
            //Send an e-mail to the user and the admin
            self::_sellSuccessEmail($this->get('listing_id'));
            trigger_error('DEBUG TRANSACTION: classified:processStatusChange() - after send e-mail');
        }

        trigger_error('DEBUG CART: Finished processing order item ' . $this->getId());
        return;
    }

    protected static function getListingCost($recurringShowChildren = true)
    {
        $cart = geoCart::getInstance();

        if (!geoMaster::is('site_fees')) {
            return 0;
        }
        //make sure price plan is good...
        if (!$cart->price_plan) {
            trigger_error('ERROR: Price plan must be set first');
            return false;
        }
        $cost = 0;

        if ($cart->isRecurringCart()) {
            //do special stuff to handle recurring listings
            $item = $cart->order->getItem('recurring');
            $cost = $item->getRecurringPrice($recurringShowChildren);
            return $cost;
        }

        if ($cart->price_plan['type_of_billing'] == 1 || !(geoPC::is_ent() || geoPC::is_premier())) {
            // Fee-based

            switch ($cart->price_plan['charge_per_ad_type']) {
                case 1: //get the charge based on the price field
                    //get all increments where the low value is lower or = to the price, but only take the highest low value
                    $sql = "SELECT `charge` FROM " . geoTables::price_plans_increments_table . " WHERE
						`price_plan_id` = ? AND `category_id` = ?
						AND `low` <= ?
						 ORDER BY `low` DESC LIMIT 1";
                    $query_data = array(
                        $cart->site->users_price_plan,
                        ((isset($cart->price_plan['category_id']) && $cart->price_plan['category_id']) ? $cart->price_plan['category_id'] : 0),
                        $cart->site->session_variables["price"] . ''
                    );

                    $increment_result = $cart->db->Execute($sql, $query_data);
                    if (!$increment_result || $increment_result->RecordCount() != 1) {
                        $cost = $cart->price_plan['charge_per_ad'];
                    } else {
                        $show_increment = $increment_result->FetchRow();
                        $cost = $show_increment['charge'];
                    }
                    break;

                case 2: //get the charge based on price range charge
                    $sql = "SELECT `length_charge` FROM " . geoTables::price_plan_lengths_table . "
						WHERE `length_of_ad` = ?
						and `price_plan_id` = ? and `category_id` = ? LIMIT 1";
                    $query_data = array(
                        $cart->site->session_variables["classified_length"],
                        $cart->site->users_price_plan,
                        ((isset($cart->price_plan['category_id']) && $cart->price_plan['category_id']) ? $cart->price_plan['category_id'] : 0)
                    );

                    $length_result = $cart->db->Execute($sql, $query_data);
                    if (!$length_result || $length_result->RecordCount() != 1) {
                        $cost = $cart->price_plan['charge_per_ad'];
                    } else {
                        $show_length_cost = $length_result->FetchRow();
                        $cost = $show_length_cost['length_charge'];
                    }
                    break;

                default:
                    $cost = $cart->price_plan['charge_per_ad'];
                    break;
            } //end of switch
        }

        return $cost;
    }

    public static function getFormVariables()
    {
        $cart = geoCart::getInstance();

        $cart->site->session_variables = $cart->item->get('session_variables');
    }

    public static function saveFormVariables()
    {
        $cart = geoCart::getInstance();

        //force trim email addy here, for convenience
        //could do the same to any other fields that need it, but email is prolly the big one
        $cart->site->session_variables['email_option'] = trim($cart->site->session_variables['email_option']);

        $cart->item->set('session_variables', $cart->site->session_variables);
    }


    public static function set_sell_variables($sell_type)
    {
        //TODO: make it so that everywhere that still uses these old vars no longer uses them, so that this function is no longer needed.
        $cart = geoCart::getInstance();

        $session_variables = $cart->site->session_variables = $cart->item->get('session_variables');
        $cart->site->terminal_category = $cart->item->getCategory();
        $cart->site->users_group = $cart->user_data['group_id'];
        $cart->site->users_price_plan = $cart->item->getPricePlan();
        if (!$cart->site->users_price_plan) {
            $cart->site->users_price_plan = (isset($cart->site->session_variables['price_plan_id'])) ? $cart->site->session_variables['price_plan_id'] : $cart->user_data['price_plan_id'];
        }

        $cart->site->user_currently_subscribed = (isset($session_variables['user_currently_subscribed'])) ? $session_variables['user_currently_subscribed'] : 0;//$show->USER_CURRENTLY_SUBSCRIBED;
        $cart->site->classified_id = (isset($session_variables['classified_id'])) ? $session_variables['classified_id'] : 0;//$show->CLASSIFIED_ID;
        $cart->site->sell_type = $cart->site->session_variables['sell_type'] = $sell_type; //$show->TYPE;
        $cart->site->final_fee = (isset($session_variables['final_fee'])) ? $session_variables['final_fee'] : 0;//$show->FINAL_FEE;
        $cart->site->auction_price_plan_id = (isset($session_variables['auction_price_plan_id'])) ? $session_variables['auction_price_plan_id'] : 0;//$show->AUCTION_PRICE_PLAN_ID;
        $cart->site->session_variables = $session_variables;
        //following moved to each different item type
/*
        $cart->site->session_variables["buy_now_only"]= 0;//classifieds
        //$cart->site->session_variables["buy_now_only"] = (isset($cart->site->session_variables["buy_now_only"]))? $cart->site->session_variables["buy_now_only"]: 0;
        if ($cart->site->session_variables["buy_now_only"]=='on' || $cart->site->session_variables["buy_now_only"]==1){
            $cart->site->session_variables["buy_now_only"] = 1;
        } else {
            $cart->site->session_variables["buy_now_only"] = 0;
        }
        if ($cart->site->session_variables["buy_now_only"])
        {
            $cart->site->session_variables["auction_minimum"] = null;
            $cart->site->session_variables["auction_reserve"] = null;
        }*/

        //set all of vars

        $cart->site->session_variables['order_item_id'] = $cart->item->getId();
        $cart->item->set('session_variables', $cart->site->session_variables);
    }

    protected static function _saveSessionVarsDiff($item, $new_session_variables)
    {
        if (!is_object($item)) {
            trigger_error('ERROR CART: saveSessionVarsDiff relies on item to be passed, or to be in cart->item, but can\'t get it so returning false.');
            return false;
        }
        //just TEMPORARILY, set status to pending so we can get session vars without this
        $current_status = $item->getStatus();
        if ($current_status == 'active') {
            $item->setStatus('temp_disable');
        }
        //DO get archived one if needed, so that admin can still view info long after listing is archived
        $old_session_variables = self::_getSessionVarsFromListing($item->get('listing_id'), true, true);
        //set status back
        $item->setStatus($current_status);

        $diff = array();
        foreach ($new_session_variables as $key => $value) {
            if (!isset($old_session_variables[$key]) || $old_session_variables[$key] != $value) {
                $diff[$key] = $value;
            }
        }

        if (!$new_session_variables['payment_options'] && $old_session_variables['payment_options']) {
            //special case: explicity set payment options to blank if they've all been removed.
            $diff['payment_options'] = '';
        } elseif ($new_session_variables['payment_options'] === true) {
            //special case on the special case. this is coming from "listing change admin," so there's never any need to jack with payment options
            unset($diff['payment_options']);
        }

        $item->set('session_variables', $diff);
        return $diff;
    }

    private static $_stopMaxLimitRecursion = false;
    protected static function _checkMaximumListingLimit($add_to_listing_count = 0)
    {
        if (self::$_stopMaxLimitRecursion) {
            //we're already in the middle of removing this item due to being over the limit
            //but part of that sometimes means "initializing" children items again, which can get us into a nasty infinite loop
            //
            //since we already know we're over the limit and are acting on it, we don't care about this function's results
            //so just return true and let things go on about their business
            self::$_stopMaxLimitRecursion = false;
            return true;
        }
        $cart = geoCart::getInstance();
        trigger_error('DEBUG CART: Top of checkMaximumListingLImit()');

        if (self::isAnonymous()) {
            //anonymous listing not bound to a user, so can't hit a user's limit
            //no need to do the rest of this
            return true;
        }

        $cart->site->page_id = 8;
        $cart->site->get_text();

        //check to see if this user has reached their maximum ad count
        $sql = "SELECT count(*) AS `total_listings` FROM " . geoTables::classifieds_table . " WHERE `seller` = ? AND `live` = 1";
        $show_total_ads = $cart->db->GetRow($sql, array($cart->user_data['id']));
        if (!$show_total_ads) {
            $cart->site->setup_error = $cart->site->messages[86];
            return false;
        }
        $total_listings = ($show_total_ads['total_listings']);
        //count up any that are in the current cart as well:
        $classifieds = $cart->order->getItem('classified');
        $auctions = $cart->order->getItem('auction');
        $total_listings += (is_array($classifieds)) ? count($classifieds) : 0;
        $total_listings += (is_array($auctions)) ? count($auctions) : 0;
        $renew_upgrades = $cart->order->getItem('listing_renew_upgrade');
        if (is_array($renew_upgrades) && count($renew_upgrades) > 0) {
            foreach ($renew_upgrades as $item) {
                if (is_object($item)) {
                    if (!$item->get('live')) {
                        //renewal or upgrade not currently live, so it is going to be adding 1 to count
                        $total_listings++;
                    }
                }
            }
        }
        $total_listings += $add_to_listing_count;

        //If any more listing item types are added, need to do that too.

        if ($total_listings <= $cart->price_plan['max_ads_allowed']) {
            //note that the total listings count includes this current listing!
            return true;
        }
        trigger_error('ERROR CART: Max ads reached, not allowed to add any more listings!');

        $cart->site->messages = $cart->db->get_text(true, 10202);
        $cart->addErrorMsg('cart_display', $cart->site->messages[500615] . $cart->price_plan['max_ads_allowed']);

        //kinda dirty, but it works:  set the action to delete and remove the listing, then display the main cart view
        self::$_stopMaxLimitRecursion = true; //let later instances of ourself know we're already working on this!
        $cart->deleteProcess();
        self::$_stopMaxLimitRecursion = false;
        $cart->current_step = $cart->cart_variables['step'] = 'cart';
        $cart->cartDisplay();
        require GEO_BASE_DIR . 'app_bottom.php';
        exit;
    }

    protected static function _insertListing($order_item, $sell_type)
    {
        //TODO: Make this use session vars more carefully
        $cart = geoCart::getInstance();
        $cart->init(true);

        $db = 0;//just for passing to old functions

        $cart->site->get_ad_configuration();
        $current_time = $listing_starts = geoUtil::time();

        $cart->site->session_variables = $order_item->get('session_variables');
        trigger_error('DEBUG CART: Session Vars: <pre>' . print_r($cart->site->session_variables, 1) . '</pre>');
        $cart->site->session_variables['sell_type'] = $sell_type;
        $cart->site->classified_id = $order_item->get('listing_id');
        $cart->site->terminal_category = $order_item->getCategory();

        if ($sell_type == 1) {
            //this is a classified ad
            //set a generic expiration time to use in case the listing placement never completes
            //we will use the duration to set the life of the ad once the ad is approved and actually goes live by admin or credit card purchase
            $listing_ends =  $cart->site->DateAdd("d", $listing_starts, $cart->db->get_site_setting('expire_unfinished_period'));
        } elseif ($sell_type == 2) {
            //Finding auction_ends based on start_time, end_time and classified_length
            if ($cart->site->session_variables['end_mode'] == 1) {
                //end time set explicitly
                if (is_array($cart->site->session_variables['end_time'])) {
                    //still saved as an array from dropdowns -- need to convert to ticktime
                    $ends = $cart->site->session_variables["end_time"];
                    $listing_ends = $cart->site->get_time($ends['end_hour'], $ends['end_minute'], $ends['end_month'], $ends['end_day'], $ends['end_year']);
                } else {
                    //a scalar value in session variables means we've already converted to ticktime somewhere else, so just use it
                    $listing_ends = $cart->site->session_variables['end_time'];
                }
            } else {
                //end time set by duration
                if ($cart->site->session_variables["classified_length"] == 0) {
                    //unlimited duration
                    $listing_ends = 0;
                } elseif ($cart->site->session_variables["start_time"] == 0) {
                    //we have a duration, but not a start time; use "now" for the start
                    $listing_ends = $cart->site->DateAdd("d", $current_time, $cart->site->session_variables["classified_length"]);
                } else {
                    //user entered a start time and a duration
                    $listing_ends = $cart->site->DateAdd("d", $cart->site->session_variables["start_time"], $cart->site->session_variables["classified_length"]);
                }
            }
            $listing_starts = $current_time;
            //final fee
            if ($cart->price_plan['charge_percentage_at_auction_end']) {
                $cart->site->session_variables['final_fee'] = 1;
            } else {
                $cart->site->session_variables['final_fee'] = 0;
            }
        } else {
            trigger_error('ERROR CART: sell_type not 1 or 2, its ' . $sell_type);
            throw new Exception('Error:  sell_type value (' . $sell_type . ') is not valid, it needs to be 1 or 2.');
        }


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

        if (strlen(trim($cart->site->session_variables["email_option"])) == 0) {
            //get the sellers default email address
            $cart->site->session_variables["email_option"] = $cart->user_data['email'];
        }
        //use order from order item not cart in case this is used without full cart
        $order = $order_item->getOrder();
        if ($order) {
            $order->setCreated($listing_starts);
            $cart->site->session_variables['seller'] = $order->getBuyer();
        }

        if (!$cart->site->session_variables['seller']) {
            //no seller id = anonymous -- get anon user id
            $anonReg = geoAddon::getRegistry('anonymous_listing');
            if ($anonReg) {
                $anon_id = $anonReg->get('anon_user_id', 0);
                $cart->site->session_variables['seller'] = $anonReg->get('anon_user_id', 0);

                //set order to have anonymous id
                //TODO: there's probably a better place to put this...
                $order->setBuyer($anon_id);
                $order->save();
            }
        }


        $cart->site->session_variables['order_item_id'] = $order_item->getId();
        $cart->site->session_variables['date'] = $current_time;
        $cart->site->session_variables['ends'] = $listing_ends;
        if ($sell_type == 2 && is_array($cart->site->session_variables['end_time'])) {
            //NOTE: ONLY set end_time in situations where the seller selects the end time
            $cart->site->session_variables['end_time'] = $listing_ends;
        }
        $cart->site->session_variables['category'] = $order_item->getCategory();
        $cart->site->session_variables['price_plan_id'] = $order_item->getPricePlan();

        $listing_id = (isset($cart->site->classified_id)) ? $cart->site->classified_id : 0;
        $new_id = self::_insertListingFromSessionVars($cart->site->session_variables, $listing_id, $order_item);
        if (!$new_id) {
            //error inserting data into db:
            trigger_error('ERROR CART TRANSACTIONS: Error inserting listing into db.');
            return false;
        }

        if (!$listing_id) {
            //This is a new listing (not an update of a previously-tried one),
            //so set up id and session vars

            $order_item->set('listing_id', $new_id);
            $cart->site->classified_id = $cart->site->session_variables['classified_id'] = $cart->site->session_variables['listing_id'] = $new_id;
            //make sure the new listing ID is saved, to prevent instances where a new listing is created
            //but an error prevents it from getting to the step where it remembers the listing id
            $order_item->set('session_variables', $cart->site->session_variables);
            $order_item->save();
        }

        if (geoPC::is_ent()) {
            //TODO: Will need to make this work with the new system
            //Seller buyer transactions, allow them to be dynamically called.
            $vars = array(
            'listing_id' => $cart->site->classified_id,
            'currency_type' => $cart->site->session_variables["currency_type"]
            );
            geoSellerBuyer::callUpdate('insertNewListing', $vars);
        }

        //Listing inserted!
        return true;
    }

    protected static function _insertListingFromSessionVars($session_variables, $listing_id = 0, $order_item = null)
    {
        $db = DataAccess::getInstance();
        //Parts of insert query
        $name_parts = array();
        $val_parts = array();
        //parts of update query
        $parts = array();


        //query data the same for insert or update
        $query_data = array();
        //Use the same loop to generate the different query parts, since code duplication is a no-no.
        foreach ($session_variables as $i => $val) {
            $keys = (isset(self::$session_to_listing_key_map[$i])) ? self::$session_to_listing_key_map[$i] : $i;
            $keys = (is_array($keys)) ? $keys : array($keys);
            //loop through each translation and set it, this allows one session var to be assigned to multiple
            //listing rows.
            foreach ($keys as $key) {
                if (isset(self::$listing_vars_to_update[$key])) {
                    $name_parts [] = "`$key`";
                    $val_parts[] = "?";

                    $parts [] = "`$key` = ?";
                    //encode value according to what type it is
                    switch (self::$listing_vars_to_update[$key]) {
                        case 'toDB':
                            if (is_array($val) && $key == 'seller_buyer_data' && geoPC::is_ent()) {
                                //special case
                                $val = serialize($val);
                            }
                            $query_data [] = trim(geoString::toDB($val));
                            break;
                        case 'int':
                            $query_data [] = intval($val);
                            break;
                        case 'float':
                            $query_data [] = floatval($val);
                            break;
                        case 'bool':
                            $query_data [] = (($val) ? 1 : 0);
                            break;
                        case 'yesno':
                            //special case, either it is yes or it is no...  (enum field
                            //in the database)
                            $query_data [] = ($val && $val === 'yes') ? 'yes' : 'no';
                            break;
                        case 'payment_options':
                            //special case, it "might be" an array...
                            if (is_array($val)) {
                                $val = implode('||', $val);
                            }
                            //break ommited on purpose
                        default:
                            //not altered, for fields like "date"
                            $query_data [] = $val;
                            break;
                    }
                }
            }
        }

        if ($listing_id) {
            //THIS IS AN UPDATE OF PREVIOUSLY ENTERED DATA

            //Use the $parts generated for the parts of the query.
            $sql = "UPDATE " . geoTables::classifieds_table . " SET " . implode(", ", $parts) . " WHERE `id` = ? LIMIT 1";
            $query_data[] = $listing_id;
            $result = $db->Execute($sql, $query_data);

            if (!$result) {
                trigger_error("ERROR SQL CART TRANSACTIONS: SQL: $sql \n Error Msg: {$db->ErrorMsg()}");
                return false;
            }
            $isNew = false;
            $newID = $listing_id;
        } else {
            //THIS IS NOT AN UPDATE OF A PREVIOUSLY TRIED CLASSIFIED AD

            //Since this is first time inserted, set the order item ID
            if (isset($session_variables['order_item_id']) && $session_variables['order_item_id']) {
                //only set order item ID first time it is inserted into DB, after that
                //if the order item ID has to change, it must be done so by manually
                //changing it directly on the listing itself.
                $name_parts[] = "`order_item_id`";
                $val_parts[] = "?";
                $query_data[] = (int)$session_variables['order_item_id'];
            }

            //Use the $name_parts and $val_parts for this query, generated above the if/else
            $sql = "INSERT INTO " . geoTables::classifieds_table . " ( " . implode(", ", $name_parts) . " ) VALUES ( " . implode(", ", $val_parts) . " )";
            $result = $db->Execute($sql, $query_data);

            if (!$result) {
                trigger_error('ERROR CART SQL: sql ERROR! sql: ' . $sql . ' Error: ' . $db->ErrorMsg());
                return false;
            }
            $isNew = true;
            $newID = $db->Insert_ID();
        }
        //insert category
        geoCategory::setListingCategory($newID, $session_variables['category']);

        $qSearch = $tSearch = null;
        //make sure category questions are removed before re-inserting them (do this even if question_value isn't set, because we might then be clearing EVERYTHING [e.g. in an edit])
        $db->Execute("DELETE FROM " . geoTables::classified_extra_table . " WHERE `classified_id` = $newID");

        if (isset($session_variables['question_value'])) {
            //insert questions and tags and update the search text according to them
            $qSearch = self::insertCatQuestions($newID, $session_variables);
            if ($qSearch === false) {
                trigger_error('ERROR CART TRANSACTIONS: Error when inserting category questions, so error when iserting listing.');
                return false;
            }
        }
        if (isset($session_variables['tags'])) {
            $tSearch = self::updateTags($newID, $session_variables['tags']);
            if ($tSearch === false) {
                trigger_error('ERROR CART TRANSACTIONS: Error when inserting category questions, so error when iserting listing.');
                return false;
            }
        }

        //update search text, even if it's blank
        $search_text = $qSearch . $tSearch;
        $sql = "UPDATE " . geoTables::classifieds_table . " SET	`search_text` = ? WHERE `id` = ?";
        $result = $db->Execute($sql, array(geoString::toDB($search_text . ''),$newID));
        if (!$result) {
            trigger_error('ERROR SQL: sql - ' . $sql . ' error: ' . $db->ErrorMsg());
            return false;
        }


        if (isset($session_variables['anonymous_password']) && geoAddon::getUtil('anonymous_listing')) {
            //insert anonymous password into table
            $sql = "INSERT INTO `geodesic_addon_anonymous_listing` (`listing_id`, `password`, `ip_address`) VALUES (?, ?, ?)";
            $result = $db->Execute($sql, array($newID, $session_variables['anonymous_password'], getenv('REMOTE_ADDR')));
            if (!$result) {
                trigger_error('ERROR CART SQL: sql ERROR! sql: ' . $sql . ' Error: ' . $db->ErrorMsg());
                return false;
            }
        }

        if (isset($session_variables['location']) && (!isset($session_variables['additional_regions_purchased']) || $session_variables['additional_regions_purchased'] == 0 || count($session_variables['additional_regions']) == 0)) {
            //ONLY set the primary region if there are no additional regions.  If
            //there are additional regions, let the additional region order item
            //take care of adding all of them.
            geoRegion::setListingRegions($newID, $session_variables['location']);
        }
        if (isset($session_variables['leveled'])) {
            //Add the leveled fields for the listing
            geoLeveledField::setListingValues($newID, $session_variables['leveled']);
        }

        if (isset($session_variables['cost_options'])) {
            //NOTE:  Special case, this applies changes to the session_vars to
            //map back group and option ID's.  Which should be fine as this method
            //is currently only called from a context that
            self::insertCostOptions($order_item, $newID);
        }

        //Set the quanitity remaining on auctions that "might" use it...  just set
        //to same value as quantity
        $quantity = 0;
        if (isset($session_variables['quantity'])) {
            $quantity = (int)$session_variables['quantity'];
        } elseif (isset($session_variables['auction_quantity'])) {
            $quantity = (int)$session_variables['auction_quantity'];
        }
        if ($quantity > 0) {
            //set quantity_remaining based on main quantity
            $sql = "UPDATE " . geoTables::classifieds_table . " SET
				`quantity_remaining` = ?
				WHERE `id` = ?";
            //echo $sql." is the query<br />\n";
            $result = $db->Execute($sql, array($quantity,$newID));
            if (!$result) {
                trigger_error('ERROR SQL: sql - ' . $sql . ' error: ' . $db->ErrorMsg());
                return false;
            }
        }

        //let order items have at it
        geoOrderItem::callUpdate('listing_insertListingFromSessionVars', array ('session_variables' => $session_variables, 'listing_id' => $newID), null, true);

        return $newID;
    }

    public static function geoCart_deleteProcess()
    {
        //Remove from the session_variables
        $cart = geoCart::getInstance();

        //go through each child, and call deleteProcess
        $original_id = $cart->item->getId();
        $items = $cart->order->getItem();
        foreach ($items as $k => $item) {
            if (is_object($item) && $item->getId() != $cart->item->getId() && is_object($item->getParent()) && $item->getParent()->getId() == $cart->item->getId()) {
                //this is a child of this item...
                //Set the cart's main item to be this item, so that the deleteProcess gets
                //what it is expecting...
                $cart->initItem($item->getId(), false);
                geoOrderItem::callUpdate('geoCart_deleteProcess', null, $item->getType());
            }
        }
        if ($cart->item->getId() != $original_id) {
            //change the item back to what it was originally.
            $cart->initItem($original_id);
        }

        //Nothing to do specifically for main listing item, everything that is done is done in children.
    }

    public function processRemove()
    {
        if ($this->get('listing_id')) {
            //delete the listing
            geoListing::remove($this->get('listing_id'));
            geoCategory::updateListingCount($this->getCategory());
        }
        return true;
    }

    public function processRemoveData()
    {
        if (!$this->getId()) {
            return true; //just to be sure the ID is known
        }

        //set any listings that use this order ID as main order id to 0
        $db = DataAccess::getInstance();
        $db->Execute("UPDATE `geodesic_classifieds` SET `order_item_id`=0 WHERE `order_item_id`=?", array($this->getId()));
        return true;
    }

    protected static function _copyListing($listing_id, $item_type, $allow_archive = true, $item = null)
    {
        trigger_error('DEBUG CART: Copy Listing Here');
        if ($item === null) {
            $cart = geoCart::getInstance();
            $item = $cart->item;
        }
        if (!is_object($item)) {
            return false;
        }
        $listing_id = intval($listing_id);
        if (!$listing_id) {
            return false;
        }
        //get the listing data
        $session_variables = self::_getSessionVarsFromListing($listing_id, $allow_archive);
        if (!$session_variables) {
            //echo __line__.' error<br />';
            return false;
        }
        //echo 'session:<pre>'.print_r($session_variables,1).'</pre>';
        $session_variables['listing_copy_id'] = $listing_id;
        //force it to create new listing
        $session_variables['listing_id'] = $session_variables['live'] = false;
        if (isset($cart)) {
            $cart->site->listing_id = $cart->site->classified_id = false;
        }

        //reset current/final bids to 0 for the new listing
        $session_variables['current_bid'] = $session_variables['final_price'] = 0;
        //also make sure to reset "viewed" stats
        $session_variables['viewed'] = $session_variables['responded'] = $session_variables['forwarded'] = 0;

        $item->set('listing_id', false);
        $item->set('live', false);

        //let itself know it's a copy, besides in the session vars
        $item->set('listing_copy_id', $listing_id);

        //set the category, but make sure it exists first!
        $category = $session_variables['category'];
        if (!geoCategory::getBasicInfo($category)) {
            //if session variable category is not found, then the listing table was changed directly somehow, but it holds the "correct" value
            //this seems to happen in rare cases where the admin edits categories, but the sessvars for expired listings are not affected
            $oldListing = geoListing::getListing($listing_id);
            $category = $oldListing->category;
        }
        $item->setCategory($category);

        $price_plan = (isset($session_variables['price_plan'])) ? $session_variables['price_plan'] : $session_variables['price_plan_id'];
        $item->setPricePlan($price_plan, $session_variables['seller']);

        $item->set('session_variables', $session_variables);
        $item->save();
        if (isset($cart)) {
            $cart->site->session_variables = $session_variables;
        }

        //allow other items such as images to also copy
        trigger_error('DEBUG CART: Type: ' . self::$_type . ' - ' . $item->getType());
        $children = geoOrderItem::getChildrenTypes(self::$_type);
        geoOrderItem::callUpdate('copyListing', $item, $children);

        //now insert the listing...
        self::_insertListing($item, $item_type);
        return true;
    }

    /**
     * Public access method that gets session vars array, as result of the session
     * vars combined from all order items used for that session var.
     *
     * @param int $listingId
     * @return array
     * @since Version 5.1.0
     */
    public static function getCombinedSessionVars($listingId)
    {
        return self::_getSessionVarsFromListing($listingId, false, true);
    }

    private static $_sessionVarsFromListing = array();
    protected static function _getSessionVarsFromListing($listing_id, $allow_archive = true, $force_refresh = false)
    {
        $allow_archive = intval($allow_archive);
        $listing_id = intval($listing_id);
        if (!$listing_id) {
            //throw new Exception ('listing ID not set!');
            trigger_error('ERROR CART: Listing ID not specified, cant get session vars for listing.');
            return false;
        }
        if (!$force_refresh && isset(self::$_sessionVarsFromListing[$listing_id][$allow_archive])) {
            //so it can be called multiple times, and only do it once
            return self::$_sessionVarsFromListing[$listing_id][$allow_archive];
        }
        $db = DataAccess::getInstance();

        $listing = geoListing::getListing($listing_id, true, $allow_archive);
        //get the listing data

        if (!$listing) {
            trigger_error('ERROR CART: Error getting listing details!');
            return false;
        }

        $item_id = $listing->order_item_id;
        //make sure the item id exists
        $itemTest = ($item_id) ? geoOrderItem::getOrderItem($item_id) : false;
        if (!$itemTest || $item_id == 0 || ($itemTest && $itemTest->getType() == 'listing_renew_upgrade')) {
            //Legacy listing, create a new order item based on legacy settings
            $item_id = self::_createItemForLegacyListing($listing_id, $allow_archive);
        }
        if (!$item_id) {
            trigger_error("ERROR CART: original order item id not known, returning false.");
            return false;
        }
        $session_variables = array();

        $all_items = $listing->getAllOrderItems();
        $first = true;
        foreach ($all_items as $row) {
            if (!$first && $row == $item_id) {
                //first item managed to get itself in there twice, probably an artifact of order item
                //in the system prior to these changes to how it works.
                //just skip it.
                continue;
            }
            $item = geoOrderItem::getOrderItem($row);

            if (is_object($item) && ($first || $item->getStatus() == 'active')) {
                //item is currently active (need to check status from object, not from DB, in case any items have changed
                //status but not serialized yet)

                //add the session vars
                $vars = $item->get('session_variables');
                if (is_array($vars)) {
                    $session_variables = array_merge($session_variables, $vars);
                }
            }
            $first = false;
        }
        self::$_sessionVarsFromListing[$listing_id][$allow_archive] = $session_variables;
        return $session_variables;
    }

    protected static function _sellSuccessEmail($listing_id = 0)
    {
        trigger_error('DEBUG EMAIL: in sellSuccessEmail()');

        //TODO: Only send 1 e-mail for a group of listings, don't need to be sending 10 e-mails if user buys 10 listings at once!
        $listing_id = intval($listing_id);
        if (!$listing_id) {
            return false;
        }
        $db = DataAccess::getInstance();
        $listing = geoListing::getListing($listing_id, false);
        if (!is_object($listing)) {
            trigger_error('ERROR CART: Listing not an object, can not send e-mail.');
            return false;
        }
        if ($db->get_site_setting('send_successful_placement_email')) {
            $msgs = $db->get_text(true, 51);

            $anonReg = geoAddon::getRegistry('anonymous_listing');
            $anon_user_id = ($anonReg) ? $anonReg->get('anon_user_id', false) : false;

            $tpl = new geoTemplate('system', 'emails');

            if ($listing->seller != 0 && $listing->seller != $anon_user_id) {
                //Nonymous listing
                $user = geoUser::getUser($listing->seller);
                $mailTo = $user->email;
                $tpl->assign('salutation', $user->getSalutation());
                $tpl->assign('userdata', $user->toArray());
            } else {
                //Anonymous listing
                $mailTo = geoString::fromDB($listing->email);
                $item = geoOrderItem::getOrderItem($listing->order_item_id);
                $tpl->assign('anonymousEditPassword', $item->get('anonPass'));
                $anonText = geoAddon::getText('geo_addons', 'anonymous_listing');
                $tpl->assign('anonymousEmailText', $anonText['emailText']);
                $tpl->assign('isAnonymousListing', true);
                $tpl->assign('editLinkLabel', $anonText['emailEditLinkLabel']);
                $tpl->assign('editLink', $db->get_site_setting('classifieds_url') . '?a=cart&action=new&main_type=listing_edit&listing_id=' . $listing->id . '&anonPass=' . $item->get('anonPass'));
            }

            $subject = $msgs[712];

            $tpl->assign('introduction', $msgs[713]);

            $tpl->assign('messageBody', $msgs[714]);
            $tpl->assign('listingURL', $listing->getFullUrl());

            $message = $tpl->fetch('listing/listing_placement_successful.tpl');

            $vars = array('content' => $message, 'listing' => $listing);
            $vars = geoAddon::triggerDisplay('sell_success_email_content', $vars, geoAddon::FILTER);
            $message = $vars['content'];

            geoEmail::sendMail($mailTo, $subject, $message, 0, 0, 0, 'text/html');
        }
        if ($db->get_site_setting('send_admin_placement_email')) {
            $order_id = geoOrderItem::getOrderItem($listing->order_item_id)->getOrder()->getId();
            $transaction = geoOrderItem::getOrderItem($listing->order_item_id)->getOrder()->getInvoice()->getTransaction();
            $category_id = (int)$db->GetOne("SELECT `category` FROM " . geoTables::listing_categories . " WHERE `listing`=? AND `is_terminal`='yes' AND `category_order`=0", array($listing_id));
            $admin_location = ADMIN_LOCAL_DIR . "index.php";
            $admin_url = str_replace($db->get_site_setting("classifieds_file_name"), $admin_location, $db->get_site_setting("classifieds_url"));
            $sql = "select category_name from " . geoTables::categories_languages_table . " where category_id = " . $category_id . " and language_id = 1";
            $r = $db->getrow($sql);
            if (!$r) {
                return false;
            }
            $category_name = geoString::fromDB($r['category_name']);
            $subject = "ADMIN: New Listing placed -- " . $category_name . " #" . $listing_id;
            $message = "Hello Admin,\n";
            $message .= "A listing has been placed on your site. See below for details.\n\n";

            $message .= "category: " . geoString::fromDB($category_name) . " [" . $db->get_site_setting('classifieds_url') . "?a=5&b=" . $category_id . "]\n";
            $message .= "title: " . geoString::fromDB($listing->title) . "\n";
            if ($listing->seller != 0 && $listing->seller != $anon_user_id) {
                $user = geoUser::getUser($listing->seller);
                $message .= "username: " . $user->username . "\n";
                $message .= "email: " . $user->email . "\n";
            } else {
                //anonymous listing
                $mailTo = geoString::fromDB($listing->email);
                $message .= "username: anonymous\n";
                $message .= "email: " . $mailTo . "\n";
            }
            $message .= "    order id: " . $order_id . "[" . $admin_url . "?page=orders_list_order_details&order_id=" . $order_id . "]\n";
            if (count($transaction) > 0) {
                foreach ($transaction as $tran) {
                    if (is_object($tran)) {
                        continue;
                    }
                }
                $gateway_name = $tran->getGateway()->getName();
                $message .= "gateway used: " . $gateway_name . "\n";
                $transaction_id = $tran->getID();
                $message .= "transaction id: " . $transaction_id . "\n";
            }
            $message .= "user ip: " . $_SERVER['REMOTE_ADDR'] . "\n";
            $message .= "user host: " . @gethostbyaddr($_SERVER['REMOTE_ADDR']) . "\n";
            //link used in E-MAIL:  DO NOT NEED TO CONVERT TO W3C!
            $message .= "link to ad: " . $db->get_site_setting('classifieds_url') . "?a=2&b=" . $listing_id . "\n";
            $message .= "description(html stripped): \n" . strip_tags(geoString::fromDB($listing->description)) . "\n";
            geoEmail::sendMail($db->get_site_setting('site_email'), $subject, $message, 0, 0, 0, 'text/plain');
        }
        return true;
    }
    /**
     *
     * This is deprecated,  Use geoUser::getSalutation() instead.
     *
     * @param array $person
     * @deprecated
     */
    protected static function _getSalutation($person)
    {
        if (is_object($person) && $person->ID) {
            return geoUser::getUser($person->ID)->getSalutation();
        } elseif (is_array($person) && $person['id']) {
            //use array notation.
            return geoUser::getUser($person['id'])->getSalutation();
        }
        //it is not an array, and not an object, who knows what it is.
        return '';
    }

    public static function adminItemDisplay($item_id)
    {
        if (!$item_id) {
            return '';
        }
        $item = geoOrderItem::getOrderItem($item_id);
        if (!is_object($item) || $item->getType() != self::$_type) {
            return '';
        }

        $info = '';
        $db = DataAccess::getInstance();
        $session_variables = $item->get('session_variables');
        $listing_id = $item->get('listing_id');
        if (self::$_type == 'listing_renew_upgrade' && $listing_id) {
            $session_variables = array_merge(self::_getSessionVarsFromListing($listing_id), $session_variables);
        }

        $listing = geoListing::getListing($listing_id);
        $listingLocked = false;
        if (is_object($listing) && $listing->isLocked()) {
            $listingLocked = true;
            $info .= geoHTML::addOption('Modification in Progress', 'Further modifications locked.' . geoHTML::showTooltip('Modification in Progress', 'Modifications to this listing are currently locked pending completion of an edit, renewal, or upgrade in progress. Use this if you need to manually unlock the listing.') . '<br />' . geoHTML::addButton('Force Unlock', '?page=orders_list_items_item_unlock&item_id=' . $item_id));
        }
        $listingIdTitle = $listing_id;

        if ($listing && !$listing->isExpired()) {
            $viewLink = 'index.php?page=users_view_ad&b=' . $listing_id;
            $listingIdTitle .= " <a href='$viewLink' class='mini_button'>View/Edit Listing Details</a> ";
        } elseif ($listing_id) {
            $listingIdTitle .= " (Listing seems to be expired or removed)";
        }


        $info .= geoHTML::addOption('Listing ID', $listingIdTitle);
        $info .= geoHTML::addOption('Title', geoString::specialChars($session_variables['classified_title']));
        $info .= geoHTML::addOption('Description', '<textarea disabled="disabled" style="border: 2px solid #88AACC; overflow: auto; height: 200px; width: 450px; padding: 10px;">' . geoString::specialChars($session_variables['description']) . '</textarea>');
        //Call children and let them display info about themselves as well
        $children = geoOrderItem::getChildrenTypes(self::$_type);
        $info .= geoOrderItem::callDisplay('adminItemDisplay', $item_id, '', $children);

        $overview = 'This is a brief overview only of the basic listing info applied by this order item.  ';
        if ($listing && !$listing->isExpired()) {
            $overview .= 'There may have been edits applied that are not reflected here, you can see the latest full details on <a href="' . $viewLink . '">this page</a>.';
        } else {
            $overview .= 'The listing affected by this order item no longer exists, it may have expired or been removed, or the order item or order might have been canceled.';
        }

        $info .= geoHTML::addOption('View full details/Edit Details', $overview);

        $extrasURL = '?mc=users&page=users_restart_ad&b=' . $listing_id;
        $info .= geoHTML::addOption('Listing Extras', geoHTML::addButton('Add/Remove Listing Extras', $extrasURL));
        //$info .= '<pre>'.print_r($session_variables,1).'</pre>';
        return $info;
    }

    /**
     * Function that creates an initial order item for a specified listing, for the purpose of setting up session vars for that listing.
     *
     * This only sets up the bare bones item, it doesn't attach stuff like bolding and stuff.  This is only to establish the base session vars.
     *
     * @param int $listing_id
     */
    public static function _createItemForLegacyListing($listing_id, $allow_archive = true)
    {
        //clean vars
        $listing_id = intval($listing_id);
        if (!$listing_id) {
            trigger_error('ERROR CART: Invalid listing ID, returning false.');
            return false;
        }
        //This is an old listing placed before the cart system, or was not able to get session vars for some reason
        //aka a legacy listing.
        //Generate the session variables based on the listing info

        $listing = geoListing::getListing($listing_id, true, $allow_archive);

        if (!is_object($listing)) {
            trigger_error('ERROR CART: Copy id for listing ' . $listing_id . ' could not be found in main table, object for listing doesn\'t work.');
            return false;
        }
        if ($listing->order_item_id) {
            //see if initial order item is good
            $orderItem = geoOrderItem::getOrderItem($listing->order_item_id);
            if ($orderItem && $orderItem->getId() && $orderItem->getType() !== 'listing_renew_upgrade') {
                //initial item already exists!  (Don't count if item is listing_renew_upgrade to
                //account for bug in previous versions where order item ID got set incorrectly
                return $orderItem->getId();
            }
        }
        $data = $listing->toArray();
        //echo 'raw data: <pre>'.print_r($data,1).'</pre><br />';

        //first reverse-engineer the translation
        $translations = array();
        foreach (self::$session_to_listing_key_map as $to => $val) {
            $val = (is_array($val)) ? $val : array ($val);
            foreach ($val as $from) {
                $translations[$from] = $to;
            }
        }
        $session_variables = array();
        foreach ($data as $key => $val) {
            if (is_numeric($key) || (!isset(self::$listing_vars_to_update[$key]) && !isset(self::$listing_vars_to_restore[$key]))) {
                //ignore
                continue;
            }
            $type = (isset(self::$listing_vars_to_update[$key])) ? self::$listing_vars_to_update[$key] : self::$listing_vars_to_restore[$key];
            switch ($type) {
                case 'toDB':
                    if (is_array($val) && $key == 'seller_buyer_data' && geoPC::is_ent()) {
                        //special case
                        $val = unserialize($val);
                    }
                    $val = geoString::fromDB($val);
                    break;
                case 'int':
                    $val = intval($val);
                    break;
                case 'float':
                    $val = floatval($val);
                    break;
                case 'bool':
                    $val = (($val) ? true : false);
                    break;
                case 'yesno':
                    //special case, either it is yes or it is no...  (enum field
                    //in the database)
                    $val = ($val && $val === 'yes') ? 'yes' : 'no';
                    break;
                default:
                    //not altered, following fields do not need to be changed:
                    //date, payment_options (those cases are ommited on purpose)
                    break;
            }
            if (array_key_exists($key, $translations)) {
                $key = $translations[$key];
            }
            $session_variables[$key] = $val;
        }
        $session_variables['classified_id'] = $session_variables['listing_id'] = $listing_id;
        $db = DataAccess::getInstance();
        //echo ('session vars: <pre>'.print_r($session_variables,1).'</pre><br />');

        /**
         * Special cases for session vars
         */

        //Set the category_id
        if (!isset($session_variables['category']) || !$session_variables['category']) {
            //get the terminal category, only need the terminal ID so no need
            //to get full cat info for the listing using geoListing::getCategories()
            $session_variables['category'] = (int)$db->GetOne("SELECT `category` FROM " . geoTables::listing_categories . " WHERE `listing`=$listing_id AND `is_terminal`='yes'");
        }

        //Populate extra questions
        $sql = "SELECT * FROM " . geoTables::classified_extra_table . " WHERE `classified_id` = $listing_id ORDER BY `display_order`";
        $extras = $db->GetAll($sql);
        foreach ($extras as $row) {
            $session_variables['question_value'][$row['question_id']] = geoString::fromDB($row['value']);
            $session_variables['question_display_order'][$row['question_id']] = $row['display_order'];
        }

        $session_variables['legacy_expired'] = ($listing->isExpired()) ? 1 : 0;
        if ($session_variables['buy_now_only']) {
            $session_variables['auction_minimum'] = null;
        } elseif ($listing->isExpired()) {
            $session_variables['auction_minimum'] = 0.01;
        } else {
            $session_variables['auction_minimum'] = (floatval($data['starting_bid']) <= 0.01) ? 0.01 : floatval($data['starting_bid']);
        }

        //get tags
        $tags = geoListing::getTags($listing_id);
        if ($tags) {
            $session_variables['tags'] = implode(', ', $tags);
        }

        $co = geoListing::getCostOptions($listing_id); //this has all the right information, but not in quite the format that is expected here...
        foreach ($co['groups'] as $key => $group) {
            $co['groups'][$key]['listing_id'] = $group['listing'];
            $co['groups'][$key]['group_id'] = $group['options'][0]['group'];
            $groupIds[$co['groups'][$key]['group_id']] = $co['groups'][$key]['group_id'];
            foreach ($co['groups'][$key]['options'] as $optKey => $opt) {
                $co['groups'][$key]['options'][$optKey]['option_id'] = $opt['id'];
                $optionIds[$opt['id']] = $opt['id'];
            }
        }
        $co['groups']['group_ids'] = $groupIds;
        $co['groups']['option_ids'] = $optionIds;
        $session_variables['cost_options'] = $co['groups'];

        $session_variables['legacy_listing'] = 'legacy_listing'; //let anyone who cares know that this is started from a legacy listing

        //now figure out what type of item to create
        $type = null;
        switch ($data['item_type']) {
            case 2:
                $type = 'auction';
                break;

            case 3:
                $type = 'job_posting';
                break;

            case 1:
                //break ommitted on purpose
            default:
                $type = 'classified';
                break;
        }

        //create a new item
        $item = geoOrderItem::getOrderItem($type);
        if (!is_object($item)) {
            //something went wrong
            trigger_error('ERROR CART: When creating item for legacy listing, item false.');
            return false;
        }

        if (!$listing->isExpired()) {
            //not archived, so we can use the following info
            $item->setCategory($data['category']);
            $item->setPricePlan($data['price_plan_id']);
            //make sure price plan ID is still good
        } else {
            //don't know the price plan or category if it has expired
        }

        $item->setCreated($data['date']);
        $item->setStatus('active');
        $item->set('session_variables', $session_variables);
        $item->set('legacy_listing', 'legacy_listing');
        $item->set('legacy_expired', $listing->isExpired());
        $item->set('listing_id', $listing_id);

        //allow children items to do needed stuff for the item
        $children = geoOrderItem::getChildrenTypes($type);
        geoOrderItem::callUpdate(
            'listing_placement_common_createItemForLegacyListing',
            array('item' => $item, 'listing' => $listing),
            $children
        );

        $item->save();
        $item_id = $item->getId();
        if ($item_id) {
            //set the order item ID manually here
            $listing->order_item_id = $item_id;
        }

        return $item_id;
    }
    /**
     * Used to get array of session vars to listing key map
     * outside of this class
     *
     * @return array
     */
    public static function getSessionToListingKeyMap()
    {
        return self::$session_to_listing_key_map;
    }

    /**
     * Used to get array of listing vars and how each one should be
     * treated outside of this class
     * @return array
     */
    public static function getListingVarsToUpdate()
    {
        return self::$listing_vars_to_update;
    }

    public static function jitLabel()
    {
        $cart = geoCart::getInstance();
        return $cart->site->messages[500768];
    }

    public static function jitDisplay()
    {
        $view = geoView::getInstance();
        $cart = geoCart::getInstance();

        $cart->site->page_id = 10202;
        $cart->site->get_text();

        $tpl_vars = array();
        $tpl_vars['errorMsg'] = $cart->error_msgs['jit'];

        //check to see if the email address they gave matches one in the db already
        $email = $cart->site->session_variables['email_option'];
        $sql = "SELECT `email` FROM " . geoTables::userdata_table . " WHERE `email` = ?";
        $result = $cart->db->GetOne($sql, array($email));
        if ($result) {
            //email exists
            $tpl_vars['emailExists'] = true;
        }
        $tpl_vars['email'] = $cart->site->session_variables['email_option'];

        $tpl_vars['allow_user_pass'] = $cart->db->get_site_setting('jit_allow_user_pass') ? true : false;
        $tpl_vars['require_email_confirmation'] = $cart->db->get_site_setting('jit_require_email_confirmation') ? true : false;

        if (!$tpl_vars['emailExists'] && $tpl_vars['require_email_confirmation'] && ($_GET['resend'] == 1 || !$tpl_vars['errorMsg'])) { //only do this if no errors or "resend" chosen
            //need to confirm this new email address. send an email with the confirmation code
            self::jitSendConfirmEmail($cart->site->session_variables['email_option']);
            if ($_GET['resend'] == 1) {
                $tpl_vars['didResend'] = true;
            }
        }


        $tpl_vars['max_user_length'] = $cart->db->get_site_setting('max_user_length');
        $tpl_vars['max_pass_length'] = $cart->db->get_site_setting('max_pass_length');

        //id of cart session -- remember this, so that after logging in, we can re-assign it to this user
        $cart_id = $cart->cart_variables['id'];

        $procURL = $cart->getProcessFormUrl();
        $tpl_vars['loginURL'] = $procURL . '&amp;jit=login';

        $cart->session->set('jit_suspend', $cart_id); //remember cart id across login

        //keep track of what the group was before...
        $cart->item->set('jit_group_id', $cart->user_data['group_id']);

        if ($cart->stepIsActive('other_details')) {
            //find out if 'other_details' step is active for this setup -- if it is, go back to it after the login
            setcookie('jit_details_active', 1, 0, '/');
        }
        $tpl_vars['continueURL'] = $procURL . '&amp;jit=continue';

        $type = $cart->item->getType();
        $prevStep = $cart->getPreviousStep();
        $tpl_vars['backURL'] = $cart->db->get_site_setting('classifieds_url') . '?a=cart&amp;main_type=' . $type . '&amp;step=' . $prevStep;

        //add security image, if it's turned on for login
        $secure = geoAddon::getUtil('security_image');
        if ($secure && $secure->check_setting('login')) {
            $security_text =& geoAddon::getText('geo_addons', 'security_image');
            $error = $cart->error_msgs['securityCode'];
            $section = "login";
            $tpl_vars['securityImageHTML'] = $secure->getHTML($error, $security_text, $section, false);
            $view->addTop($secure->getJs());
        }

        $view->setBodyTpl('shared/jit_login_form.tpl', '', 'order_items')->setBodyVar($tpl_vars);

        $cart->site->display_page();
    }

    public static function jitSendConfirmEmail($to)
    {
        $cart = geoCart::getInstance();
        $cart->site->page_id = 10202;
        $cart->site->get_text();

        //create a random confirmation code
        $code = substr(md5(rand()), 0, 5);

        //save the code to the database
        $cart->db->Execute('REPLACE INTO ' . geoTables::jit_confirm . ' (`email`,`code`) VALUES (?,?)', array(geoString::toDB($to), $code));

        //send an email with the code
        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('messageBody', $cart->site->messages[502203]);
        $tpl->assign('code', $code);
        $message = $tpl->fetch('registration/jit_confirmation_code.tpl');
        $subject = $cart->site->messages[502202];
        geoEmail::sendMail($to, $subject, $message, 0, 0, 0, 'text/html');
    }

    public static function jitCheckVars()
    {
        $jit = $_REQUEST['jit'];
        $cart = geoCart::getInstance();

        if ($_GET['resend'] == 1) {
            //not proceeding with login/registration -- just re-sending confirmation code (which will be handled in jitDisplay())
            //for here, just throw an error so that it resets
            $cart->addError();
            return;
        }

        $cart->site->page_id = 10202;
        $cart->site->get_text();

        if ($cart->db->get_site_setting('jit_require_email_confirmation')) {
            //match the confirmation code
            $email = $cart->site->session_variables['email_option'];
            $givenCode = trim($_REQUEST['confirmation_code']);
            $savedCode = $cart->db->GetOne('SELECT `code` FROM ' . geoTables::jit_confirm . ' WHERE `email` = ?', array(geoString::toDB($email)));
            if ($givenCode != $savedCode) {
                $cart->addError()->addErrorMsg('jit', $cart->site->messages[502201]);
                return;
            } else {
                //code matches -- clean up the db
                $cart->db->Execute('DELETE FROM ' . geoTables::jit_confirm . ' WHERE `email` = ?', array(geoString::toDB($email)));
            }
        }


        //get an Auth object, for logging in later
        include_once(CLASSES_DIR . "authenticate_class.php");
        $auth = new Auth(0, $cart->db->getLanguage(), geoPC::getInstance());

        if ($jit === 'login') {
            //user wants to login -- go ahead and show login page
            $auth->login_form(0, '', '');
            require GEO_BASE_DIR . 'app_bottom.php';
            exit(); //don't go on with cart process after showing login page
        } else {
            //user not logging in, so do stealth registration:
            //create a randomized username/password
            //insert them into the db
            //email them to the user
            //log the user in automatically

            //first things first, if there is a security image, check it for validity so that we can throw a cart error and stop everything below it it's bad
            $secure = geoAddon::getUtil('security_image');
            if ($secure && $secure->check_setting('login')) {
                if (!$secure->check_security_code($_REQUEST['b']['securityCode'])) {
                    //security image failed. send error
                    $cart->addErrorMsg('securityCode', '1'); //addon handles the error text...just needs something present
                    $cart->addError();
                    return;
                }
            }

            if ($cart->item->get('newUser')) {
                //already done this once, user probably refreshed or something
                $username = $cart->item->get('newUser');
                $password = $cart->item->get('newPass');
            } else {
                if ($cart->db->get_site_setting('jit_allow_user_pass')) {
                    $username = $_POST['username'];
                    $password = $_POST['password'];
                    $confirm = $_POST['confirm'];

                    if (!$username || !$password || !$confirm) {
                        $cart->addErrorMsg('jit', $cart->site->messages[500784]);
                        $cart->addError();
                        return;
                    }
                    if ($username == $password) {
                        //username and password cannot be the same
                        $cart->addErrorMsg('jit', $cart->site->messages[500783]);
                        $cart->addError();
                        return;
                    }
                    if ($password != $confirm) {
                        //username and password cannot be the same
                        $cart->addErrorMsg('jit', $cart->site->messages[500785]);
                        $cart->addError();
                        return;
                    }

                    $max_user = $cart->db->get_site_setting('max_user_length');
                    $min_user = $cart->db->get_site_setting('min_user_length');
                    $max_pass = $cart->db->get_site_setting('max_pass_length');
                    $min_pass = $cart->db->get_site_setting('min_pass_length');
                    if (strlen($username) < $min_user) {
                        $cart->addErrorMsg('jit', $cart->site->messages[500885] . $min_user);
                        $cart->addError();
                        return;
                    }
                    if (strlen($username) > $max_user) {
                        $cart->addErrorMsg('jit', $cart->site->messages[500886] . $max_user);
                        $cart->addError();
                        return;
                    }
                    if (strlen($password) < $min_pass) {
                        $cart->addErrorMsg('jit', $cart->site->messages[500887] . $min_pass);
                        $cart->addError();
                        return;
                    }
                    if (strlen($password) > $max_pass) {
                        $cart->addErrorMsg('jit', $cart->site->messages[500888] . $max_pass);
                        $cart->addError();
                        return;
                    }


                    $sql = "select * from " . geoTables::logins_table . " where username = ?";
                    $result = $cart->db->Execute($sql, array($username));
                    if ($result && $result->RecordCount() > 0) {
                        //username already exists
                        $cart->addErrorMsg('jit', $cart->site->messages[500786]);
                        $cart->addError();
                        return;
                    }
                } else {
                    //create unique username

                    //(start with a random 4-digit number and add 1 digit at a time until a unique name is found or the limit is exceeded)
                    $username = 'user' . rand(1000, 9999);
                    do {
                        if (strlen($username) >= $cart->db->get_site_setting('max_user_length')) {
                            //it's statistically improbable we'll ever get to this point, but just in case:
                            //if we ever reach this, start over
                            $username = 'user' . rand(1000, 9999);
                        }
                        $username .= rand(0, 9); //add another digit each pass to minimize runtime and add extensibility
                        $sql = "select * from geodesic_logins where username=?";
                        $result = $cart->db->Execute($sql, array($username));
                    } while ($result->RecordCount() > 0);

                    //generate password
                    $password = substr(md5(uniqid(rand(), true)), 0, $cart->db->get_site_setting('max_pass_length'));
                }

                //add to logins table, get new user ID
                $sql = "INSERT INTO " . geoTables::logins_table . " (username, password) VALUES (?, ?)";
                $cart->db->Execute($sql, array($username, $password));

                $newId = $cart->db->Insert_Id();


                //grab sessvars
                $session_variables = $cart->site->session_variables;

                //update sessvars to be sold by this user
                $session_variables['seller'] = $newId;

                //add user into userdata table, stealing info from listing and making up some of the rest

                $sql = "INSERT INTO " . geoTables::userdata_table . " (
				`id`,`username`,`email`,`firstname`,`lastname`,
				`address`,`zip`,`city`,`state`,`country`,
				`phone`,`phone2`,`fax`,`date_joined`,`communication_type`,`new_listing_alert_last_sent`
				) VALUES (
				?,?,?,?,?,
				?,?,?,?,?,
				?,?,?,?,?,?
				)";

                $userInfo = array(
                    'id' => $newId, 'username' => $username, 'email' => $session_variables['email_option'] . '',
                    'firstname' => '', 'lastname' => '', 'address' => $session_variables['address'] . '',
                    'zip' => $session_variables['zip_code'] . '', 'city' => $session_variables['city'] . '', 'state' => $session_variables['state'] . '',
                    'country' => $session_variables['country'] . '', 'phone' => $session_variables['phone_1_option'] . '', 'phone2' => $session_variables['phone_2_option'] . '',
                    'fax' => $session_variables['fax_option'] . '', 'date_joined' => geoUtil::time(), 'communication_type' => $cart->db->get_site_setting('default_communication_setting'),
                    'new_listing_alert_last_sent' => geoUtil::time()
                );

                $result = $cart->db->Execute($sql, $userInfo);
                if (!$result) {
                    trigger_error('ERROR JIT: failed to insert into userdata table<br />sql: ' . $sql . '<br />error: ' . $cart->db->ErrorMsg());
                    $cart->addErrorMsg('jit', $cart->site->messages[500787]);
                    $cart->addError();
                    return false;
                }

                //add user regions
                if ($session_variables['location']) {
                    geoRegion::setUserRegions($newId, $session_variables['location']);
                }

                //add to usergroupspriceplans table, too

                //find out the default group and priceplans
                $sql = "SELECT `group_id`, `price_plan_id`, `auction_price_plan_id` FROM " . geoTables::groups_table . " WHERE `default_group` = 1";
                $group = $cart->db->GetRow($sql);

                //insert with default data
                $sql = "INSERT INTO " . geoTables::user_groups_price_plans_table . " (`id`, `group_id`, `price_plan_id`, `auction_price_plan_id`) VALUES (?,?,?,?)";
                $result = $cart->db->Execute($sql, array($newId, $group['group_id'], $group['price_plan_id'], $group['auction_price_plan_id']));
                if (!$result) {
                    trigger_error('ERROR JIT: failed to insert into usergroupspriceplans table<br />sql: ' . $sql . '<br />error: ' . $cart->db->ErrorMsg());
                    $cart->addErrorMsg('jit', $cart->site->messages[500787]);
                    $cart->addError();
                    return false;
                }

                //add the starting account balance, if present
                //TODO: this, along with the copy of it in register_class, should probably be moved into an addon event call
                $initialBalance = $cart->db->GetOne("SELECT `initial_site_balance` FROM " . geoTables::price_plans_table . " WHERE `price_plan_id` = ?", array($cart->item->getPricePlan()));
                $sql = "UPDATE " . geoTables::userdata_table . " SET `account_balance` = ? WHERE `id` = ?";
                if (!$cart->db->Execute($sql, array($initialBalance, $newId))) {
                    trigger_error('ERROR JIT SQL: Failed inserting initial account balance');
                    $cart->addErrorMsg('jit', $cart->site->messages[500787]);
                    $cart->addError();
                    return false;
                }

                $userInfo['password'] = $password;
                geoAddon::triggerUpdate('user_register', $userInfo);
                geoAddon::triggerUpdate('registration_add_field_update', array('user_id' => $newId)); //used by Tokens addon to add starting tokens to a user

                //save changes to sessvars
                $cart->site->session_variables = $session_variables;
            }

            //make a note here: we've already created a user, so don't make a new one if the enduser does something silly like refreshes the page
            $cart->item->set('newUser', $username);
            $cart->item->set('newPass', $password);

            //send an email to the user with his new login information
            $subject = $cart->site->messages[500775];
            $tpl = new geoTemplate('system', 'emails');
            $tpl->assign('messageBody', $cart->site->messages[500776]);
            $tpl->assign('url', $cart->db->get_site_setting('classifieds_url'));
            $tpl->assign('usernameLabel', $cart->site->messages[500777]);
            $tpl->assign('username', $username);
            $tpl->assign('passwordLabel', $cart->site->messages[500778]);
            $tpl->assign('password', $password);
            $tpl->assign('messageFooter', $cart->site->messages[500779]);
            $body = $tpl->fetch('registration/jit_complete.tpl');
            geoEmail::sendMail($session_variables['email_option'], $subject, $body, 0, 0, 0, 'text/html');

            //while we're in the business of being sneaky, let's go ahead and log the user in with the account we've just created
            //have to go through the validate form or login won't work quite right...
            $loginInfo = array('username' => $username, 'password' => $password);

            if (isset($_REQUEST['b']['securityCode'])) {
                //pass the security image along, as well
                $loginInfo['securityCode'] = $_REQUEST['b']['securityCode'];
            }

            $login_result = $auth->validate_login_form($loginInfo, 0);
            require GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }
    }

    public static function jitProcess()
    {
        //nothing to do here
        return true;
    }

    public static function jit_afterCheckVars()
    {
        //nothign to do
    }

    public static function jit_afterProcess()
    {
        //nothing to do..
    }

    public static function jit_afterDisplay()
    {
        //First, figure out where we should be going "next"
        $cart = geoCart::getInstance();

        //Failsafe: make sure we don't end up calling display step over and over
        $stepBefore = $cart->getCurrentStep();

        if ($cart->item->get('jit_group_id')) {
            //user group before is set...  see if should start at beginning...
            $jit_group_id = (int)$cart->item->get('jit_group_id');
            //reset value to prevent doing this over and over
            $cart->item->set('jit_group_id', false);

            //check if current session group ID is different
            if ((int)$cart->user_data['group_id'] !== $jit_group_id) {
                //does not match!
                $type = $cart->item->getType();
                $msgs = $cart->db->get_text(true, 10202);
                $cart->addError()
                    ->addErrorMsg('cart_error', $msgs[502094]);

                if ($cart->isStep($type . ':choose_plan')) {
                    //start them off at choosing a plan
                    $cart->setCurrentStep($type . ':choose_plan');
                } elseif ($cart->isCombinedStep($type . ':choose_plan') || $cart->isCombinedStep($type . ':details')) {
                    //use combined step
                    $cart->setCurrentStep('combined');
                    if (!$cart->isCombinedStep($type . ':choose_plan')) {
                        //reset price plan and such
                        self::_setDefaultPriceplan();
                    }
                } else {
                    //start them off at details...  the category selected should not need
                    //to be changed due to different priceplan...
                    $cart->setCurrentStep($type . ':details');
                    //reset price plan and such
                    self::_setDefaultPriceplan();
                }
                if ($stepBefore !== $cart->getCurrentStep()) {
                    //display to complete the short-circuit...
                    return $cart->displayStep();
                } else {
                    //failsafe, at least prevent infinite loops
                    echo 'Internal Error: Invalid step order, could not process step.';
                    return;
                }
            }
        }
        //gets this far, it should just short-circuit this step..  Be sure to go
        //through process step so that the "auto skip cart", and other things,
        //work properly.

        $cart->processStep();

        if ($stepBefore !== $cart->getCurrentStep()) {
            //display to complete the short-circuit...
            return $cart->displayStep();
        }
        //failsafe... shouldn't get this far...  Unless something is wrong with steps
        echo 'Internal Error: Invalid step order, cannot process JIT step.';
    }

    private static function _setDefaultPriceplan()
    {
        $cart = geoCart::getInstance();
        //set the price plan / category stuff
        $price_plan = (geoMaster::is('auctions') && $cart->item->getType() == 'auction') ? 'auction_price_plan_id' : 'price_plan_id';
        $price_plan = $cart->user_data[$price_plan];
        $cat = $cart->item->getCategory();
        $cart->item->setPricePlan($price_plan, $cart->user_info['id']);
        $cart->setPricePlan($price_plan, $cat);
    }

    public static function jit_afterLabel()
    {
        //the "after" step is hidden
        return '';
    }

    public static function removeTags($listingId)
    {
        $db = DataAccess::getInstance();

        $listingId = (int)$listingId;

        if (!$listingId) {
            //not valid listing ID
            return false;
        }

        //first remove existing tags for listing
        $sql = "DELETE FROM " . geoTables::tags . " WHERE `listing_id`=$listingId";
        if (!$db->Execute($sql)) {
            trigger_error('ERROR SQL: Error running ' . $sql . ' : error: ' . $db->ErrorMsg());
            return false;
        }
        return true;
    }

    public static function updateTags($listingId, $tags)
    {
        $db = DataAccess::getInstance();

        $listingId = (int)$listingId;
        $tags = (is_array($tags)) ? $tags : explode(', ', $tags);
        //remove any blank tags, tags will be cleaned already but sometimes an
        //empty string for a tag can get through, this should stop it.
        $tags = array_diff($tags, array (''));

        if (!$listingId) {
            //not valid listing ID
            return false;
        }

        if (!self::removeTags($listingId)) {
            return false;
        }

        $sql = $db->Prepare("INSERT INTO " . geoTables::tags . " (`listing_id`, `tag`) VALUES (?, ?)");
        $search_text = '';

        foreach ($tags as $tag) {
            //tags should already be cleaned at this point
            $result = $db->Execute($sql, array($listingId, geoString::toDB($tag)));
            if (!$result) {
                trigger_error('ERROR SQL: Error inserting tag for listing.  Error: ' . $db->ErrorMsg());
                return false;
            }
            //add tag to search text
            $search_text .= str_replace('-', ' ', $tag) . ' - ';
        }
        return $search_text;
    }

    /**
     * Used in detailsCheckVars to check for any errors with cost options, and
     * trigger any errors within the cart class if so.
     */
    public static function checkCostOptions()
    {
        $cart = geoCart::getInstance();

        //check cost options for any errors...
        $max_cost_option_groups = $max_cost_options = 0;
        if ($cart->site->sell_type == 2 && $cart->site->fields->cost_options->is_enabled) {
            if ($cart->site->fields->cost_options->is_required && !count(array_diff_key($cart->site->session_variables['cost_options'], array('group_ids' => 1,'option_ids' => 1)))) {
                //make sure they have at least one cost option group...
                $cart->addError()
                    ->addErrorMsg('cost_options', $cart->site->messages[502208]);
                return;
            }
            $type_data = explode('|', $cart->site->fields->cost_options->type_data);
            $max_cost_option_groups = (int)$type_data[0];
            $max_cost_options = (int)$type_data[1];

            //make sure cost options don't go over the max
            if (isset($cart->site->session_variables['cost_options'])) {
                $cost_options = array_diff_key($cart->site->session_variables['cost_options'], array('group_ids' => 1,'option_ids' => 1));
                if ($max_cost_option_groups && count($cost_options) > $max_cost_option_groups) {
                    //over the max allowed cost option groups for this category

                    $cart->addError()
                        ->addErrorMsg('cost_options', $cart->site->messages[502209] . $max_cost_option_groups);
                    return;
                }
                $hasCombined = 0;
                $quantityCheck = (int)$cart->site->session_variables['auction_quantity'];
                foreach ($cost_options as $group) {
                    if (isset($group['error'])) {
                        //error for group...
                        $cart->addError()
                            ->addErrorMsg('cost_options', $cart->site->messages[502210]);
                        return;
                    }
                    if ($group['quantity_type'] == 'combined') {
                        $hasCombined++;
                    }
                    $quantity = 0;
                    foreach ($group['options'] as $option) {
                        if (isset($option['error'])) {
                            //error on individual option

                            $cart->addError()
                                ->addErrorMsg('cost_options', $cart->site->messages[502210]);
                            return;
                        }
                        if ($group['quantity_type'] === 'individual') {
                            $quantity += (int)$option['ind_quantity_remaining'];
                        }
                    }
                    if ($group['quantity_type'] === 'individual' && $quantityCheck !== $quantity) {
                        $cart->addError()
                            ->addErrorMsg('cost_options', $cart->site->messages[502211]);
                        return;
                    }
                }
                if ($hasCombined) {
                    //Do checks for combined quantities
                    if ($hasCombined < 2) {
                        //Only have 1 selections set to use combined quantity
                        $cart->addError()
                            ->addErrorMsg('cost_options', $cart->site->messages[502212]);
                        return;
                    }

                    if (!count($cart->site->session_variables['cost_options_quantity'])) {
                        //simple check, they have one set to use combined but no actual
                        //combined quantities set...  More complex checks will be done in a sec.
                        //(maybe move this to be part of those other checks?)

                        $cart->addError()
                            ->addErrorMsg('cost_options', $cart->site->messages[502213]);
                        return;
                    }

                    //NOTE: "no" cost option groups accounted for, by not adding the entries
                    //to the database.

                    //Check the combined quantities, make sure they match up to the quantity set...
                    //(the quantity should be auto-updated with JS, so this would happen if
                    // seller changed quantity from number it sets)

                    $quantity = array_sum($cart->site->session_variables['cost_options_quantity']);
                    if ($quantityCheck !== $quantity) {
                        $cart->addError()
                            ->addErrorMsg('cost_options', $cart->site->messages[502214]);
                        return;
                    }
                }
            }
        }
    }

    /**
     * Insert the cost options set for a listing based on session vars.  Special
     * Case:  need to be able to update session vars to keep track of group and
     * option ID's.
     *
     * @param geoOrderItem $order_item
     * @param int $listingId
     * @param array $vars_to_use Used during Listing Edit to specify the set of session_variables that should overwrite existing data
     */
    public static function insertCostOptions($order_item, $listingId, $vars_to_use = null)
    {
        $db = DataAccess::getInstance();

        $listingId = (int)$listingId;
        $listing = geoListing::getListing($listingId);
        $seller = $listing->seller;
        if (!$seller) {
            //failsafe... this only for auctions so this should not happen
            return false;
        }
        if ($vars_to_use) {
            //for edits, we pass in the sessvars array, because this could be reverting an edit or doing a new one
            $session_variables = $vars_to_use;
        } else {
            //just grab sessvars from the cart as normal
            $cart = geoCart::getInstance();
            $session_variables = $cart->site->session_variables;
        }
        $costOptions = $session_variables['cost_options'];

        //keep track, just a straight up array of option ID's used in this listing,
        //so can easily "remove" any that no longer exist
        $group_ids = (isset($costOptions['group_ids'])) ? $costOptions['group_ids'] : array();
        $costOptions['group_ids'] = array();

        $option_ids = (isset($costOptions['option_ids'])) ? $costOptions['option_ids'] : array();
        $costOptions['option_ids'] = array();

        $changes = $reset_ids = false;
        $option_map = array();
        foreach ($costOptions as $group_display_order => $group) {
            if (in_array($group_display_order, array('group_ids','option_ids'), true)) {
                //skip this one, not a normal group
                continue;
            }

            if ((isset($group['listing_id']) || isset($group['group_id'])) && (int)$group['listing_id'] !== $listingId) {
                //either a listing copy or an edit
                if ($cart) {
                    //listing copy (initial save, pre-changes)
                    $reset_ids = $changes = true;
                    $isCopy = true;
                    $group['listing_id'] = $listingId;
                } else {
                    //listing edit; nothing to do here...just change existing stuff instead of rewriting
                }
            }
            if ($reset_ids) {
                unset($group['group_id']);
            }

            $group['listing_id'] = $listingId;

            $group_display_order = (int)$group_display_order;
            if (!isset($group['group_id']) || !$group['group_id']) {
                //insert new
                $sql = "INSERT INTO " . geoTables::listing_cost_option_group . "
						SET `listing`=?, `label`=?, `seller`=?, `quantity_type`=?, `display_order`=?";
                //NOTE: label is already db-encoded
                $query_data = array($listingId, $group['label'], $seller, $group['quantity_type'],
                    $group_display_order);
                $result = $db->Execute($sql, $query_data);
                if (!$result) {
                    trigger_error("ERROR SQL: error inserting new cost option group!");
                    return false;
                }
                $group['group_id'] = (int)$db->Insert_Id();
                if (!$group['group_id']) {
                    trigger_error("ERROR SQL: error getting new option group ID!");
                    return false;
                }

                //there were changes to the session vars that need to be saved into session vars
                $changes = true;
            } else {
                //already set group ID so just make sure all the stuff is updated
                $sql = "UPDATE " . geoTables::listing_cost_option_group . " SET 
						`listing`=?, `label`=?, `quantity_type`=?, `display_order`=? WHERE `id`=?";
                //NOTE: label is already db-encoded
                $query_data = array($listingId, $group['label'], $group['quantity_type'],
                    $group_display_order,(int)$group['group_id']);
                $db->Execute($sql, $query_data);
            }
            $costOptions['group_ids'][(int)$group['group_id']] = (int)$group['group_id'];
            foreach ($group['options'] as $option_display_order => $option) {
                if ($reset_ids) {
                    unset($option['option_id']);
                }
                //file slot is just set to be empty if not set
                $file_slot = ($option['file_slot'] > 0) ? (int)$option['file_slot'] : '';
                if (!isset($option['option_id'])) {
                    //insert new
                    $sql = "INSERT INTO " . geoTables::listing_cost_option . "
						SET `group`=?, `label`=?, `cost_added`=?, `file_slot`=?, `ind_quantity_remaining`=?, `display_order`=?";
                    //NOTE: label is already db-encoded
                    $query_data = array($group['group_id'], $option['label'], $option['cost_added'], $file_slot, $option['ind_quantity_remaining'],
                        $option_display_order);
                    $result = $db->Execute($sql, $query_data);
                    if (!$result) {
                        trigger_error("ERROR SQL: error inserting new cost option!");
                        return false;
                    }
                    $option['option_id'] = (int)$db->Insert_Id();
                    if (!$option['option_id']) {
                        trigger_error("ERROR SQL: error getting new option ID!");
                        return false;
                    }
                    $group['options'][$option_display_order] = $option;
                    //there were changes to the session vars that need to be saved into session vars
                    $changes = true;
                } else {
                    $sql = "UPDATE " . geoTables::listing_cost_option . " SET 
							`label`=?, `cost_added`=?, `file_slot`=?, `ind_quantity_remaining`=?, `display_order`=? WHERE `id`=?";
                    //NOTE: label is already db-encoded
                    $query_data = array ($option['label'], $option['cost_added'],
                        $file_slot, $option['ind_quantity_remaining'],$option_display_order, (int)$option['option_id']);
                    $db->Execute($sql, $query_data);
                }

                if ($group['quantity_type'] == 'combined' && isset($option['comb_id'])) {
                    //map the option maps...
                    $option_map[$option['comb_id']] = (int)$option['option_id'];
                }
                $costOptions['option_ids'][(int)$option['option_id']] = (int)$option['option_id'];
            }
            //apply any changes
            $costOptions[$group_display_order] = $group;
        }
        $cost_options_quantity = (isset($session_variables['cost_options_quantity'])) ? $session_variables['cost_options_quantity'] : array();
        //Unlike the options themselves, the "combined quantities" do not need to maintain the
        //same ID in the database, as they are not referenced by any bids etc. - so just
        //clear out any existing combined quantities, and add it fresh.
        if ($listingId) {
            if (!$isCopy) { //don't remove old values if we're copying this to a new listing!
                $db->Execute("DELETE FROM " . geoTables::listing_cost_option_quantity . " WHERE `listing`=?", array($listingId));
                $db->Execute("DELETE FROM " . geoTables::listing_cost_option_q_option . " WHERE NOT EXISTS
						(SELECT * FROM " . geoTables::listing_cost_option_quantity . " 
						WHERE " . geoTables::listing_cost_option_quantity . ".`id`=" . geoTables::listing_cost_option_q_option . ".`combo_id`)");
            }

            //now add them
            foreach ($cost_options_quantity as $hash => $quantity) {
                $option_parts = explode('_', $hash);
                $sql = "INSERT INTO " . geoTables::listing_cost_option_quantity . " SET `listing`=?, `quantity_remaining`=?";
                $result = $db->Execute($sql, array($listingId, (int)$quantity));
                if (!$result) {
                    trigger_error("ERROR SQL: query failed, cannot insert combo option.  error: " . $db->ErrorMsg());
                    continue;
                }
                $combo_id = (int)$db->Insert_Id();
                if (!$combo_id) {
                    //something wrong, possibly DB structure auto_increment or something
                    trigger_error("ERROR SQL: failed to get quantity ID!  Can't insert options for it.");
                    continue;
                }

                foreach ($option_parts as $part) {
                    $option_id = (int)$option_map[$part];
                    if (!$option_id) {
                        //failsafe, make sure if something goes wrong it doesn't populate
                        //db with bunch of 0 for id's
                        trigger_error("DEBUG CART: skipping this part $part as there is nothing in option map: <pre>" . print_r($option_map, 1) . "</pre>");
                        break;
                    }
                    $result = $db->Execute(
                        "INSERT INTO " . geoTables::listing_cost_option_q_option . " SET `combo_id`=?, `option_id`=?",
                        array($combo_id, $option_id)
                    );
                    if (!$result) {
                        trigger_error("ERROR SQL: error inserting q option! error: " . $db->ErrorMsg());
                    }
                }
            }
        }

        //Now, the main groups and options DO preserve the ID so that bid selections will
        //always track back to the correct thing.  Be sure to remove any old ones
        //that have been removed since it doesn't just wipe them out to begin with.

        if (!$isCopy) { //don't remove old values if we're copying this to a new listing!
            $oldGroups = array_diff($group_ids, $costOptions['group_ids']);
            $oldOptions = array_diff($option_ids, $costOptions['option_ids']);
            if ($oldGroups) {
                $changes = true;
                $db->Execute("DELETE FROM " . geoTables::listing_cost_option_group . " WHERE `id` IN (" . implode(',', $oldGroups) . ")");
                //also remove any options in those removed groups
                $db->Execute("DELETE FROM " . geoTables::listing_cost_option . " WHERE `group` IN (" . implode(', ', $oldGroups) . ")");
                //note: combined quantities are reset every time anyways, no need to hand-remove individual entries.
            }
            //OK now see if there are any options that were removed that previously were set in db...
            if ($oldOptions) {
                //there are old ones to remove
                $changes = true;
                $db->Execute("DELETE FROM " . geoTables::listing_cost_option . " WHERE `id` IN (" . implode(', ', $oldOptions) . ")");
            }
        }

        if ($changes) {
            $session_variables['cost_options'] = $costOptions;
            if ($cart) {
                $cart->site->session_variables['cost_options'] = $costOptions;
            }
            if ($order_item) {
                //save session variables so that the id associations don't get lost
                $order_item->set('session_variables', $session_variables);
            }
        }
    }

    public static function insertCatQuestions($listingId, $session_variables)
    {
        $db = DataAccess::getInstance();

        $session_variables["question_value"] = isset($session_variables["question_value"]) ? $session_variables["question_value"] : array();
        $num_questions = count($session_variables["question_value"]);

        $search_text = '';

        if ($num_questions > 0) {
            foreach ($session_variables["question_value"] as $key => $value) {
                if ((strlen(trim($value)) > 0) || (strlen(trim($session_variables["question_value_other"][$key])) > 0)) {
                    //there is a value in this questions so put it in the db
                    $sql = "SELECT * FROM " . geoTables::classified_sell_questions_table . " WHERE `question_id` = ?";
                    $question_result = $db->Execute($sql, array($key));

                    if (!$question_result) {
                        trigger_error('ERROR SQL: sql - ' . $sql . ' error: ' . $db->ErrorMsg());

                        return false;
                    } elseif ($question_result->RecordCount() == 1) {
                        $show = $question_result->FetchNextObject();
                        $use_this_value = (strlen(trim($session_variables["question_value_other"][$key])) > 0) ? $session_variables["question_value_other"][$key] : $value;
                        if ($show->CHOICES == "check") {
                            $checkbox = 1;
                        } elseif ($show->CHOICES == "url") {
                            $checkbox = 2;
                        } else {
                            $checkbox = 0;
                        }
                        $use_this_value = str_replace("\n", " ", $use_this_value);
                        $sql = "insert into " . geoTables::classified_extra_table . "
							(classified_id,name,question_id,value,explanation,checkbox,display_order)
							values
							(" . $listingId . ",\"" . geoString::toDB($show->NAME) . "\",\"" . $key . "\",\"" . geoString::toDB($use_this_value) . "\",
							\"" . geoString::toDB($show->EXPLANATION) . "\"," . $checkbox . "," . $show->DISPLAY_ORDER . ")";
                        $current_insert_result = $db->Execute($sql);

                        if (!$current_insert_result) {
                            trigger_error('ERROR SQL: sql - ' . $sql . ' error: ' . $db->ErrorMsg());

                            return false;
                        }
                        $search_text .= $use_this_value . " - ";
                    }
                } // end of if
            } // end of for $i
        }// end of if num_questions > 0
        $session_variables["group_value"] = isset($session_variables["group_value"]) ? $session_variables["group_value"] : array();
        $num_group_questions = count($session_variables["group_value"]);

        if ($num_group_questions > 0) {
            foreach ($session_variables["group_value"] as $key => $value) {
                if ((strlen(trim($value)) > 0) || (strlen(trim($session_variables["group_value_other"][$key])) > 0)) {
                    //there is a value in this questions so put it in the db
                    $sql = "SELECT * FROM " . geoTables::classified_sell_questions_table . " WHERE question_id = \"" . $key . "\"";
                    $question_result = $db->Execute($sql);

                    if (!$question_result) {
                        trigger_error('ERROR SQL: sql - ' . $sql . ' error: ' . $db->ErrorMsg());

                        return false;
                    } elseif ($question_result->RecordCount() == 1) {
                        $show = $question_result->FetchNextObject();
                        $use_this_value = (strlen(trim($session_variables["question_value_other"][$key])) > 0) ? $session_variables["question_value_other"][$key] : $value;
                        if ($show->CHOICES == "check") {
                            $checkbox = 1;
                        } elseif ($show->CHOICES == "url") {
                            $checkbox = 2;
                        } else {
                            $checkbox = 0;
                        }
                        $sql = "insert into " . geoTables::classified_extra_table . "
							(classified_id,name,question_id,value,explanation,checkbox,group_id)
							values
							(?,?,?,?,?,?,?)";
                        $sql_array = array($listingId,geoString::toDB($show->NAME),$key,geoString::toDB($use_this_value),
                            geoString::toDB($show->EXPLANATION),$checkbox,$show->GROUP_ID);
                        $insert_result = $db->Execute($sql, $sql_array);

                        if (!$insert_result) {
                            trigger_error('ERROR SQL: sql - ' . $sql . ' error: ' . $db->ErrorMsg());

                            return false;
                        }
                        $search_text .= $use_this_value . " - ";
                    }
                } // end of if
            } // end of for $i
        }

        return $search_text;
    }

    protected function _listingTitleDisplay($title, $inEmail = false)
    {
        //shorten the title to...  40 chars
        if (strlen(trim($title)) > 40) {
            $title = geoString::substr($title, 0, 40) . "...";
        }

        //Make the title link to the listing if the listing is live and this order item
        //is active.
        $listing_id = (int)$this->get('listing_id');
        if ($listing_id && $this->getStatus() == 'active') {
            $listing = geoListing::getListing($listing_id);
            if ($listing && $listing->live) {
                //if it is live, link to the listing
                if ($inEmail) {
                    $link = DataAccess::getInstance()->get_site_setting('classifieds_url') . '?a=2&amp;b=' . $listing_id;
                } else {
                    $link = ((defined('IN_ADMIN')) ? '../' : '') . DataAccess::getInstance()->get_site_setting('classifieds_file_name') . '?a=2&amp;b=' . $listing_id;
                }
                $title = "<a href=\"$link\" onclick=\"window.open(this.href); return false;\" class=\"obvious\">$title</a>";
            }
        }
        return $title;
    }
}
