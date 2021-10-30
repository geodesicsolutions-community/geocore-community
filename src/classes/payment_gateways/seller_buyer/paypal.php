<?php

//paypal.php



class paypalSellerBuyerGateway
{
    //Main paypal server:
    private $_paypal_payment_url = 'https://www.paypal.com/cgi-bin/webscr';
    private $_paypal_host = 'www.paypal.com';

    //Sandbox paypal server: to use, uncomment the lines, and comment out the ones above
    //private $_paypal_payment_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    //private $_paypal_host = 'www.sandbox.paypal.com';

    var $paypal_logo =
    "
	<!-- PayPal Logo -->
	<a href='#' onclick=\"javascript:window.open('https://www.paypal.com/us/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');\">
	<img  src='https://www.paypal.com/en_US/i/logo/PayPal_mark_50x34.gif' border='0' alt='Acceptance Mark'></a>
	<!-- PayPal Logo -->";



    /**
     * Display admin settings on page Transactions > Seller to Buyer
     *
     * @return string Text to display, keep in mind it will be displayed along with other
     *  seller to buyer payment types.
     */
    public function adminDisplaySettings()
    {
        $db = DataAccess::getInstance();

        $allow_paypal_check = ($db->get_site_setting('paypal_allow_sb') == 1) ? "checked" : "";

        $html = "
		<div class='col_hdr'>Paypal</div>
		<div class='form-group'>
			<label class='control-label col-xs-12 col-sm-5'>Enable Paypal Seller-Buyer Gateway " . geoHTML::showTooltip('Enable Paypal Seller-Buyer Gateway', "This allows winning auction bidders to pay the seller directly via a PayPal link") . "</label>
			<div class='col-xs-12 col-sm-6'>
				<input type=\"checkbox\" name=\"paypal_allow_sb\" value=\"1\" $allow_paypal_check/>
			</div>
		</div>
		<div class='form-group'>
			<label class='control-label col-xs-12 col-sm-5'>Official Paypal Images (use in templates or text)</label>
			<div class='col-xs-12 col-sm-6 vertical-form-fix'><a href='https://www.paypal.com/us/cgi-bin/webscr?cmd=xpt/cps/general/AcceptanceMarkLogos-outside' target='_blank'>Click to view Images</a></div>
		</div>
";
        //TODO: hide these settings when main allow_sb setting is off
        $checked = $db->get_site_setting('pp_chain_enable') ? "checked='checked'" : '';
        $html .= '
		<div class="col_hdr">Advanced Paypal Settings</div>
		<div class="form-group">
			<label class="control-label col-xs-12 col-sm-5">Enable Chain Payments</label>
			<div class="col-xs-12 col-sm-6"><input type="checkbox" name="pp_chain_enable" value="1" onclick="jQuery(\'#chainSettings\').toggle(this.checked);" ' . $checked . ' /></div>
		</div>
		<div id="chainSettings" ' . (!$checked ? 'style="display: none;"' : '') . '>
			<div class="page_note" style="text-align: center;">This requires at least a Paypal Business account, and special configuration/approval from Paypal. Most sites will not need to use this.</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Developer Username</label>
				<div class="col-xs-12 col-sm-6"><input type="text" name="pp_chain_username" class="form-control col-md-7 col-xs-12" value="' . $db->get_site_setting('pp_chain_username') . '" /></div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Developer Password</label>
				<div class="col-xs-12 col-sm-6"><input type="text" name="pp_chain_password" class="form-control col-md-7 col-xs-12" value="' . $db->get_site_setting('pp_chain_password') . '" /></div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Developer Signature</label>
				<div class="col-xs-12 col-sm-6"><input type="text" name="pp_chain_signature" class="form-control col-md-7 col-xs-12" value="' . $db->get_site_setting('pp_chain_signature') . '" /></div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Application ID</label>
				<div class="col-xs-12 col-sm-6"><input type="text" name="pp_chain_appid" class="form-control col-md-7 col-xs-12" value="' . $db->get_site_setting('pp_chain_appid') . '" placeholder="leave blank for development"/></div>
			</div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">User ID to receive payments (final fees) on behalf of site<br />(This user must have a Paypal account configured for use with seller-buyer)</label>
				<div class="col-xs-12 col-sm-6"><input type="number" name="pp_chain_site_recipient" class="form-control col-md-7 col-xs-12" value="' . $db->get_site_setting('pp_chain_site_recipient') . '" /></div>
			</div>
		</div>
		
		';
        //TODO: make pp_chain_site_recipient a auto-search field on usernames
        return $html;
    }

    /**
     * Save the settings in the admin
     *
     * @return boolean true if successful, false otherwise
     */
    public function adminUpdateSettings()
    {
        $db = DataAccess::getInstance();
        $sb = geoSellerBuyer::getInstance();

        //paypal - save settings


        $go_ahead = (isset($_POST['paypal_allow_sb']) && $_POST['paypal_allow_sb']) ? 1 : false;


        //check inputs

        if ($go_ahead && !$db->get_site_setting('paypal_allow_sb')) {
            $sb->initTableStructure();//make sure table structure is initialized.
            //turn on paypal for all price plans
            $plans = $this->_getAuctionPricePlans();
            foreach ($plans as $plan_id) {
                //set main price plan default settings
                $sb->setDefaultPlanSettings($plan_id, 0, array('paypal_allow_sb' => true));

                //get any cat price plans for this price plan, and set default settings
                $cat_plans = $this->_getAuctionPricePlans($plan_id);
                foreach ($cat_plans as $cat_plan_id) {
                    //set cat price plan default settings
                    $sb->setDefaultPlanSettings(0, $cat_plan_id, array('paypal_allow_sb' => true));
                }
            }
        }
        $db->set_site_setting('paypal_allow_sb', $go_ahead);

        $db->set_site_setting('pp_chain_enable', $_POST['pp_chain_enable']);
        $db->set_site_setting('pp_chain_username', $_POST['pp_chain_username']);
        $db->set_site_setting('pp_chain_password', $_POST['pp_chain_password']);
        $db->set_site_setting('pp_chain_signature', $_POST['pp_chain_signature']);
        $db->set_site_setting('pp_chain_appid', $_POST['pp_chain_appid']);

        $sitePP = $_POST['pp_chain_site_recipient'];
        if ($sitePP && !$sb->getUserSetting($sitePP, 'paypal_id')) {
            geoAdmin::m('Selected user is not a valid user OR does NOT have a registered paypal email', geoAdmin::ERROR);
        }

        $db->set_site_setting('pp_chain_site_recipient', $_POST['pp_chain_site_recipient']);

        return true;
    }


    /**
     * Display settings for a specific price plan, specified by $vars['price_plan_id'] or category
     * price plan $vars['category']
     *
     * @param array $vars Associative array of price plan or category price plan
     * @return string Text to display, keep in mind there will be a list of different seller
     *  to buyer payment types
     */
    function adminDisplayPricePlanSettings($vars)
    {
        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }
        if (!$this->_paypalAllowed()) {
            return ''; //main setting turned off, nothing to display...
        }
        $price_plan_id = $vars['price_plan_id'];
        $category = $vars['category'];

        $sb = geoSellerBuyer::getInstance();
        $allow_paypal_check = ($sb->getPlanSetting($price_plan_id, $category, 'paypal_allow_sb')) ? 'checked="checked" ' : '';
        $html = "
				<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-5'>Enable Paypal Seller-Buyer Gateway " . geoHTML::showTooltip('Enable Paypal Seller-Buyer Gateway', "This allows winning auction bidders to pay the seller directly via a PayPal link") . "</label>
					<div class='col-xs-12 col-sm-6'>
						<input type=\"checkbox\" name=\"paypal_allow_sb\" value=\"1\" $allow_paypal_check/>
					</div>
				</div>";
        return $html;
    }


    /**
     * Save settings specific for a price plan.
     *
     * @param array $vars Associative array, with price plan and category price plan id's
     * @return boolean true if successful, false otherwise
     */
    function adminUpdatePricePlanSettings($vars)
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        $price_plan_id = $vars['price_plan_id'];
        $category = $vars['category'];

        if (!$db->get_site_setting('paypal_allow_sb')) {
            return true; //main setting turned off, nothing to save...
        }

        if (PHP5_DIR) {
            $menu_loader = geoAdmin::getInstance();
        } else {
            $menu_loader =& geoAdmin::getInstance();
        }

        $sb = geoSellerBuyer::getInstance();

        //BIDPAY - save settings
        if (isset($_POST['paypal_allow_sb']) && $_POST['paypal_allow_sb']) {
            $sb->setPlanSetting($price_plan_id, $category, 'paypal_allow_sb', true);

            return true;
        }
        //un-checked, turn off
        $sb->setPlanSetting($price_plan_id, $category, 'paypal_allow_sb', false);
        return true;
    }

    /**
     * This one is a little different.  You return an array of currencies that
     * the gateway is able to use, using this format:
     * array (
     *  'USD' => 'United States Dollar',
     * )
     * @return array
     * @since Version 6.0.0
     */
    public function adminDisplayCurrencyTypes_choices()
    {
        //just make sure paypal gateway is included, so we can use it to get currencies
        geoPaymentGateway::getPaymentGateway('paypal');

        //get currencies from main gateway, luckily it uses same format as we need
        return paypalPaymentGateway::getPaypalCurrencies();
    }

    public function adminDisplayCurrencyTypes_header()
    {
        return 'Paypal Seller/Buyer Currency';
    }

    public function adminDisplayCurrencyTypes_type_value($type_id)
    {
        $sb = geoSellerBuyer::getInstance();

        //the current value
        return $sb->getCurrencySetting($type_id, 'paypal', 'USD');
    }

    /**
     * Save the value for this seller/buyer type, responsible for checking ot make
     * sure that value being changes is for this sb type.
     * @param array $return
     * @return array The $return with any changes needed to reflect stuff.
     */
    public function adminDisplayCurrencyTypes_update($return)
    {
        if ($return['sb_type'] != 'paypal') {
            //this isn't our value to save, let it keep going
            return $return;
        }

        $currencies = $this->adminDisplayCurrencyTypes_choices();

        if (!isset($currencies[$return['value']])) {
            $return['error'] = 'Value not valid currency type.';
            return $return;
        }
        //the value to display
        $return['value_display'] = $currencies[$return['value']];

        if (!$return['type_id']) {
            //type ID not valid?
            $return['error'] = 'Invalid currency type, try refreshing the page.';
            return $return;
        }

        //set the value
        $sb = geoSellerBuyer::getInstance();
        $sb->setCurrencySetting($return['type_id'], 'paypal', $return['value']);
        $return['message'] = 'Paypal currency updated.';

        return $return;
    }

    /**
     * Display on client side, in user details, any settings that are needed from the user.
     *
     * @param array $vars Associative array, with user id and price plan id.
     */
    public function displayUserDetails($vars)
    {
        //see if we already have user token
        $sb = geoSellerBuyer::getInstance();
        $view = geoView::getInstance();
        $user_id = intval($vars['user_id']);
        $price_plan_id = intval($vars['price_plan_id']);

        if (!$user_id || !$price_plan_id) {
            return '';
        }
        if (!$this->_paypalAllowed()) {
            return '';
        }

        $paypal_id = '' . $sb->getUserSetting($user_id, 'paypal_id');
        //make sure text for page 37 is loaded up
        $this->_getText(37);

        $tpl = new geoTemplate('system', 'payment_gateways');

        $tpl->assign('paypal_id', $paypal_id);

        $html = $tpl->fetch('seller_buyer/paypal/user_details.tpl');
        return $html;
    }

    /**
     * On client side, when placing a new auction, displays the checkbox of whether the
     * auction can be paid for using paypal.
     *
     * @param array $vars associative array, with user_id, category, price_plan_id, and sell_session_id
     * @return string Text to display, or empty string, keeping in mind there may be multiple
     *  "on site payments" besides this one to display.
     */
    function listings_placement_common_detailsDisplay()
    {
        $cart = geoCart::getInstance();
        $item = $cart->item;
        $sb = geoSellerBuyer::getInstance();
        if (!is_object($item)) {
            //something weird
            return;
        }

        //make sure paypal is turned on for price plan
        if (!$sb->getPlanSetting($item->getPricePlan(), $item->getCategory(), 'paypal_allow_sb')) {
            return '';
        }

        //make sure user has paypal e-mail set
        $email = $sb->getUserSetting($cart->order->getBuyer(), 'paypal_id');
        $messages = $this->_getText(9);
        $tpl = new geoTemplate('system', 'payment_gateways');
        $tpl->assign('msgs', $messages);

        $tpl->assign('email', $email);
        //see if setting is set in session already
        $paypal_allow_sb = $sb->getCartItemSetting('paypal_allow_sb');
        $tpl->assign('checked', ($paypal_allow_sb) ? true : false);

        //No paypal ID set, show text link to my info page
        $tpl->assign('myInfoLink', $cart->db->get_site_setting('classifieds_url') . "?a=4&amp;b=3#paypal_field");

        return $tpl->fetch('seller_buyer/paypal/payment_choices_checkbox.tpl');
    }

    /**
     * On client side, when placing a new auction, called to check any vars set when item type is an
     * auction.
     *
     */
    function listings_placement_common_detailsCheckVars()
    {
        $cart = geoCart::getInstance();
        $item = $cart->item;
        $sb = geoSellerBuyer::getInstance();
        if (!is_object($item)) {
            //something weird
            return;
        }

        //make sure paypal is turned on for price plan
        if (!$sb->getPlanSetting($item->getPricePlan(), $item->getCategory(), 'paypal_allow_sb')) {
            return;
        }

        //make sure user has paypal e-mail set
        $email = $sb->getUserSetting($cart->order->getBuyer(), 'paypal_id');
        if (strlen($email) > 0) {
            $paypal_allow_sb = (isset($_POST['paypal_allow_sb']) && $_POST['paypal_allow_sb']) ? 1 : 0;

            //update item info
            $sb->setCartItemSetting('paypal_allow_sb', $paypal_allow_sb);
            //save currency type
            $sb->setCartItemSetting('paypal_currency', $sb->getCurrencySetting($cart->site->session_variables['currency_type'], 'paypal', 'USD'));
        }
    }

    /**
     * Shows text on page when user is looking at listing approval page, I think..
     *
     * @param array $vars
     * @return string
     */
    function displayPaymentTypesApprove($vars)
    {
        //return 'displayPaymentTypesApprove vars: <pre>'.print_r($vars,1).'</pre>';
        //check inputs
        $session_id = $vars['sell_session_id'];
        $session = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $user_id = $session->getUserId();
        if (strlen($session_id) != 32) {
            //neither is set!
            return '';
        }

        if (!$this->_paypalAllowed()) {
            return '';
        }

        $sb = geoSellerBuyer::getInstance();
        $email = $sb->getUserSetting($user_id, 'paypal_id');

        //if listing id is set, see if there are current settings already set for the listing

        if (strlen($session_id) > 0) {
            //see if setting is set in session already
            $paypal_allow_sb = $sb->getCartItemSetting('paypal_allow_sb');
            //Possible future feature:
            //expand on ability to force user to be set up in paypal before allowed to bid or buy now
            $paypal_force = $sb->getCartItemSetting('paypal_force');
            $html = '';
            if ($paypal_allow_sb) {
                $messages = $this->_getText(11);
                $html = geoString::fromDB($messages[500187]) . " ($email)";
            }


            return $html;

            //return "info passed: <pre>".print_r($vars,1)."</pre>";
        }

        //sell session not known?
        return '';
    }

    /**
     * On client side, my current ads, when displaying details about a certain listing
     *
     * @param array $vars
     * @return string
     */
    function displayPaymentTypesEdit($vars)
    {
        //return 'displayPaymentTypesEdit vars: <pre>'.print_r($vars,1).'</pre>';
        //check inputs
        $listing_id = intval($vars['listing_id']);

        if (!$listing_id) {
            //neither is set!
            return '';
        }
        if (!$this->_paypalAllowed()) {
            return '';
        }
        $sb = geoSellerBuyer::getInstance();

        //if listing id is set, see if there are current settings already set for the listing

        //see if setting is set in session already
        $paypal_allow_sb = $sb->getListingSetting($listing_id, 'paypal_allow_sb');
        //Possible future feature:
        //expand on ability to force user to be set up in paypal before allowed to bid or buy now
        $paypal_force = $sb->getListingSetting($listing_id, 'paypal_force');
        $html = '';
        if ($paypal_allow_sb) {
            $messages = $this->_getText(31);
            $html = geoString::fromDB($messages[500189]);
        }

        return $html;
    }

    /**
     * On client side, when viewing details of listing, displays in comma seperated list
     *
     * @param array $vars
     * @return string that looks good as part of comma seperated list
     */
    function displayPaymentTypesListing($vars)
    {
        //return 'displayPaymentTypesListing vars: <pre>'.print_r($vars,1).'</pre>';
        //check inputs
        $listing_id = intval($vars['listing_id']);

        if (!$listing_id) {
            //neither is set!
            return '';
        }
        if (!$this->_paypalAllowed()) {
            return '';
        }

        $sb = geoSellerBuyer::getInstance();

        //see if setting is set in session already
        $paypal_allow_sb = $sb->getListingSetting($listing_id, 'paypal_allow_sb');
        //Possible future feature:
        //expand on ability to force user to be set up in paypal before allowed to bid or buy now
        $paypal_force = $sb->getListingSetting($listing_id, 'paypal_force');
        $html = '';
        if ($paypal_allow_sb) {
            $messages = $this->_getText(1);
            $html = geoString::fromDB($messages[500190]);
        }

        return $html;


        //sell session not known?
        return '';
    }

    /**
     * On client side, when user uses buy now link, should display the link to pay for the
     * item.
     *
     * @param array $vars
     * @return string
     */
    function displayPaymentLinkBuyNowSuccess($vars)
    {
        $messages = $this->_getText(10165);
        $payment_text = array(
            'payment_link' => geoString::fromDB($messages[500191]),
            'already_paid' => geoString::fromDB($messages[500196])
        );
        return $this->_getPaymentLink($vars, $payment_text);
    }
    /**
     * On client side, when user wins the bid, this should display the link to pay for that
     * listing.
     *
     * @param array $vars
     * @return string
     */
    function displayPaymentLinkListing($vars)
    {
        $messages = $this->_getText(1);
        $payment_text = array(
            'payment_link' => $messages[500192],
            'already_paid' => $messages[500197]
        );
        return $this->_getPaymentLink($vars, $payment_text);
    }

    /**
     * On client side, when viewing current bids won, display link to pay for bid.
     *
     * @param string $vars
     * @return string
     */
    function displayPaymentLinkCurrentBids($vars)
    {
        $messages = $this->_getText(10175);
        $payment_text = array(
            'payment_link' => $messages[500193],
            'already_paid' => $messages[500198]
        );
        return $this->_getPaymentLink($vars, $payment_text);
    }

    /**
     * Link to be used in the buy now e-mail sent to person buying now
     *
     * @param array $vars
     * @return string
     */
    function displayPaymentLinkBuyNowEmail($vars)
    {
        $messages = $this->_getText(10167);
        $payment_text = array(
            'email_text' => geoString::fromDB($messages[500206])
        );
        return $this->_getPaymentLink($vars, $payment_text);
    }

    /**
     * Link for the e-mail sent to winning bidder.
     *
     * @param array $vars
     * @return string
     */
    function displayPaymentLinkWinningBidderEmail($vars)
    {
        $messages = $this->_getText(10174);
        $payment_text = array(
            'email_text' => geoString::fromDB($messages[500207])
        );
        return $this->_getPaymentLink($vars, $payment_text);
    }

    /**
     * Happens whenever the form with stuff in it is saved during place a listing process, or
     * when updating a listing's details
     *
     * @param array $vars
     * @return string
     */
    function saveFormVars($vars)
    {
        //check inputs
        $user_id = intval($vars['user_id']);
        $category = intval($vars['category']);
        $price_plan_id = intval($vars['price_plan_id']);
        $listing_id = intval($vars['listing_id']);
        $session_id = $vars['sell_session_id'];

        if (!$user_id || !$price_plan_id) {
            //invalid input
            return '';
        }

        if (strlen($session_id) != 32 && !$listing_id) {
            return ;//something wrong with session id or listing id
        }
        if (isset($_POST['paypal_allow_sb'])) {
            $sb = geoSellerBuyer::getInstance();

            if (!$this->_paypalAllowed()) {
                return '';
            }

            //make sure paypal is turned on for price plan
            if (!$sb->getPlanSetting($price_plan_id, $category, 'paypal_allow_sb')) {
                return ;
            }

            //make sure user has paypal token
            if (strlen(trim($sb->getUserSetting($user_id, 'paypal_id'))) == 0) {
                //user token not set
                return ;
            }

            $paypal_allow_sb = (isset($_POST['paypal_allow_sb']) && $_POST['paypal_allow_sb']) ? 1 : 0;

            if ($listing_id > 0) {
                //listing id given, so update the listing data as well
                $sb->setListingSetting($listing_id, 'paypal_allow_sb', $paypal_allow_sb);
            }
            if (strlen($session_id) == 32) {
                //update session info
                $sb->setCartItemSetting('paypal_allow_sb', $paypal_allow_sb);
            }
        }
    }

    /**
     * Happens when a new listing is placed.
     *
     * @param array $vars
     */
    function insertNewListing($vars)
    {
        //check inputs
        $listing_id = intval($vars['listing_id']);

        if (!$listing_id) {
            return ;
        }

        if (!$this->_paypalAllowed()) {
            return ;
        }

        //just copy whats in the session, to be whats in the listing.  We've already checked all the settings
        //when we saved (or didn't save) settings in the session.

        $sb = geoSellerBuyer::getInstance();

        $paypal_allow_sb = $sb->getCartItemSetting('paypal_allow_sb');

        if ($paypal_allow_sb) {
            //save setting for listing
            $sb->setListingSetting($listing_id, 'paypal_allow_sb', $paypal_allow_sb);
            if ($vars['currency_type']) {
                //save paypal currency to use
                $sb->setListingSetting($listing_id, 'paypal_currency', $sb->getCurrencySetting($vars['currency_type'], 'paypal', 'USD'));
            }
        }
    }

    /**
     * For displaying it's own page, when index.php?a=sb_transaction happens...
     *
     * @param array $vars
     * @return string
     */
    function sellerBuyerPage($vars)
    {
        //vars is not used.
        //called when a=sb_transaction is used.
        if (!isset($_GET['action']) || substr($_GET['action'], 0, 6) != 'paypal') {
            //this is not meant for us.
            return '';
        }

        if ($_GET['action'] === 'paypal_payment') {
            return $this->_actionPaypalPayment();
        } elseif ($_GET['action'] === 'paypal_p_result') {
            return $this->_actionPResult();
        } elseif ($_GET['action'] === 'paypal_cp_result') {
            return geoChainPayment::checkResult((int)$_GET['cpid'], true);
        } else {
            //not a valid thingy
            return 'Site Error.  Please try again.';
        }
    }

    function _actionPaypalPayment()
    {
        //re-direct them to paypal to pay for something
        //validation!!!
        $listing_id = isset($_GET['listing_id']) ? intval($_GET['listing_id']) : 0;


        if (!$listing_id) {
            //invalid input
            return '';
        }

        $sb = geoSellerBuyer::getInstance();

        $winning_bidders = $sb->getListingSetting($listing_id, 'winning_bidder_ids', array());
        //get old way of saving them just in case
        $winning_bidder_id = intval($sb->getListingSetting($listing_id, 'winning_bidder_id'));
        if ($winning_bidder_id) {
            $winning_bidders[] = $winning_bidder_id;
        }
        if (!count($winning_bidders)) {
            //winning bidder(s) not known.
            return '';
        }

        //check to make sure current logged in user is valid.
        $db = true;
        $session = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $current_session_id = $session->getUserID();

        if ($current_session_id == 0) {
            //Should not get to this, in the index it should send them to login screen.
            //this is just a double-security precaution...
            return 'You must be logged in to do this.';
        }

        if (!in_array($current_session_id, $winning_bidders)) {
            if ($current_session_id == 1) {
                //admin user
                //Hard-coded:  Only the admin user will ever see this text!
                return '<h2>Payment Disabled for Admin User</h2>
						The Paypal Payment link was shown on the previous page so that you as the <strong>site admin</strong>
						can more easily design the templates for that page.  <strong>Only the winning bidder</strong> is allowed to actually
						go through the payment process, however.';
            }
            //not valid, winning bidder does not match.
            return '';
        }


        //get listing details.
        $sql = 'SELECT * FROM ' . $db->geoTables->classifieds_table . ' WHERE `id`=' . $listing_id . ' LIMIT 1';

        $result = $db->Execute($sql);
        if (!$result) {
            trigger_error('ERROR SQL: Sql: ' . $sql . ' Error: ' . $db->ErrorMsg());
            return '';
        }

        $listing_details = $result->FetchRow();
        if (!is_array($listing_details) || count($listing_details) == 0) {
            //something is wrong..
            return 'Internal Error, listing not found.';
        }
        $seller_id = $listing_details['seller'];
        $final_price = $sb->getListingSetting($listing_id, 'final_price');

        $quantity = 1;
        if ($listing_details['price_applies'] == 'item') {
            if (isset($_GET['quantity']) && $_GET['quantity'] > 1) {
                $quantity = (int)$_GET['quantity'];
            }
            //make sure this is valid bid
            $bid_result = geoListing::getBid($listing_id, $current_session_id, $quantity);
            if (!$bid_result) {
                //invalid, no bids found matching, and this is apply to item so quantity matters...
                trigger_error('DEBUG PAYPAL: Could not verify this bid exists.');
                return '';
            }
            $final_price = $bid_result['bid'];
        }

        //check for Cost Options on the bid that need to add to the total price
        $add = geoListing::getCostOptionsPriceFromBid($listing_id, $current_session_id);
        if ($add) {
            $final_price += $add;
        }

        if (!$final_price) {
            trigger_error('DEBUG BIDPAY: Final price not attached to listing id, have to get it manually!');
            return 'Internal Error, final price not known.';
        }

        $timeout = 10;
        $messages = $this->_getText(10201);
        if ($sb->getListingSetting($listing_id, 'paypal_listing_paid')) {
            return '<div class="paypal_listing_paid_text">' . geoString::fromDB($messages[500199]) . '</div>';
        }

        //see if we need to do a Chain Payment instead
        if ($db->get_site_setting('pp_chain_enable') == 1) {
            try {
                $initial_price = $final_price; //hold onto unmodified final_price to restore it later if needed
                if ($quantity > 1) {
                    //chainpayments treats everything as 1 quantity, so multiply it into the price here
                    //(for "normal" seller-buyer, paypal is sent the quantity, and THEY multiply it)
                    $final_price = $final_price * $quantity;
                }
                //similarly, any "additional fees" such as shipping add to the main cost here
                //(they're kept in the separate "handling" field for normal seller-buyer)
                $additional_fees = geoListing::getAuctionAdditionalFees($listing_id);
                if ($additional_fees) {
                    $final_price += $additional_fees['raw']['total'];
                }
                $cp = new geoChainPayment($seller_id, $final_price, $listing_id);
            } catch (Exception $e) {
                require GEO_BASE_DIR . 'app_bottom.php';
                die("Error: " . $e->getMessage());
            }

            $priority = 1;

            //see below comments about support for a custom addon that is NOT part of the software
            $addon = geoAddon::getInstance();
            if ($addon->isEnabled('theo_richter_charity')) {
                $tr_reg = geoAddon::getRegistry('theo_richter_charity');
                $target = $tr_reg->provider_fee_target;
                $percentage = $tr_reg->provider_fee_percentage;
                if ($target && $percentage) {
                    $cp->addSecondaryTarget($target, $priority, $percentage, 0);
                    $priority++;
                }
            }


            //if this auction has final fees, set them up as a secondary payment to the site's configured PP user
            $siteRecipient = $db->get_site_setting('pp_chain_site_recipient');

            if ($siteRecipient) {
                //figure out if any final fees are owed
                $finalFee = 0;
                $sql = "SELECT i.`id` FROM " . geoTables::order_item . " AS i, " . geoTables::order_item_registry . " AS r WHERE i.`id`=r.`order_item` 
						AND r.`index_key`='listing' AND r.`val_string` = ? AND i.`type`='auction_final_fees' AND i.`status`='pending'";
                $finalFeeItemId = $db->GetOne($sql, array($listing_id));
                if ($finalFeeItemId) {
                    //an unpaid final fee exists for this listing
                    $item = geoOrderItem::getOrderItem($finalFeeItemId);
                    if ($item) {
                        $finalFee = $item->getCost();
                    }
                }
                if ($finalFee) {
                    $cp->addSecondaryTarget($siteRecipient, $priority, 0, $finalFee, $finalFeeItemId);
                    $priority++;
                }
            }

            $tr_custom = $sb->getListingSetting($listing_id, 'tr_donate_data');
            /*
             * This section exists to support some custom projects that are explicitly NOT part of the core software.
            * Most is handled in a separate addon, but this next couple of calls are directly here to simplify things a bit
            * $tr_custom is an array as follows:
            * type => either "percentage" or "flat"
            * names => array
            * one of:
            * percentages => array of ints
            * flats => array of floats
            *
            * the trick is to match up keys of either the "percentages" or "flats" array with the "names" array and use the value from
            *   * "names" as the recipient for the chain-payment secondary
            */

            if ($tr_custom['type'] === 'percentage') {
                foreach ($tr_custom['percentages'] as $key => $p) {
                    if (!$p) {
                        //no payment to this target
                        continue;
                    }
                    $target = false;
                    if ($key === 'buyer') {
                        $u = geoAddon::getUtil('theo_richter_charity');
                        $target = $u->getCurrentTargetForListing($listing_id);
                    }
                    if (!$target) {
                        $target = $tr_custom['names'][$key];
                    }
                    $cp->addSecondaryTarget($target, $priority, $p, 0);
                    $priority++;
                }
            } elseif ($tr_custom['type'] === 'flat') {
                foreach ($tr_custom['flats'] as $key => $f) {
                    if (!$f) {
                        //no payment to this target
                        continue;
                    }
                    $target = false;
                    if ($key === 'buyer') {
                        $u = geoAddon::getUtil('theo_richter_charity');
                        $target = $u->getCurrentTargetForListing($listing_id);
                    }
                    if (!$target) {
                        $target = $tr_custom['names'][$key];
                    }
                    $cp->addSecondaryTarget($target, $priority, 0, $f);
                    $priority++;
                }
            }

            //if $priority is still "1" at this point, it means we did not add any Secondary Targets
            //without at least one of those, there is no point in doing a Chain Payment, so skip that and treat this as a normal Seller-Buyer transaction
            if ($priority > 1) {
                $cp->process(); //this redirects to paypal and exits
                return; //won't get here, but for sanity!
            } elseif ($initial_price) {
                //undo things done to final_price for chainpayments so that they work right for the normal thing
                $final_price = $initial_price;
            }
        }

        $link = $this->_getPaymentUrl($listing_id, $seller_id, $winning_bidder_id, $listing_details, $final_price, $timeout, $quantity);
        if (strlen($link) > 0) {
            //cool, looks like we got the link good.  Re-direct to paypal.
            header('Location: ' . $link);
            //the end user should never see the below message unless the re-direct above didn't work.
            return '
			<div class="paypal_start_payment_text">
				' . geoString::fromDB($messages[500200]) . '
				<a href="' . $link . '" class="paypal_start_payment_text_link">' . geoString::fromDB($messages[500201]) . '</a>
			</div>';
        } else {
            //there was an error with paypal, either timeout, or some error returned from Paypal.
            return '
			<div class="paypal_start_payment_error">
				' . geoString::fromDB($messages[500202]) . '
			</div>';
        }
    }

    function _actionPResult()
    {
        //validate it
        $listing_id = (isset($_GET['l_id'])) ? intval($_GET['l_id']) : 0;

        if (!$listing_id) {
            return 'Site Error';
        }
        $session = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $this_user_id = $session->getUserId();

        $sb = geoSellerBuyer::getInstance();

        //make sure stuff is all valid
        $is_valid = true;
        $winning_bidder_id = intval($sb->getListingSetting($listing_id, 'winning_bidder_id'));
        if (!$winning_bidder_id) {
            //winning bidder not known.
            $is_valid = false;
        }

        if ($winning_bidder_id != $this_user_id) {
            //user id does not match!
            $is_valid = false;
        }

        //see if listing is currently in progress of a transaction:
        $paypal_transaction = $sb->getListingSetting($listing_id, 'paypal_TransactionID');

        if (!is_array($paypal_transaction) && count($paypal_transaction) == 0) {
            //listing is not currently in midle of transaction!
            $is_valid = false;
        }

        if ($is_valid) {
            //bidder matches, listing is in middle of transaction, etc. so it looks like this is valid.
            //go ahead and save that the listing has been paid for.  This is only for quickness sake when
            //displaying the purchase button to the user, for the seller we are going to make an API call
            //to get the status of the transaction, just in case.
            $sb->setListingSetting($listing_id, 'paypal_listing_paid', 1);
        }

        //Always show the "payment finished" message, just in case so the user doesn't try to pay for it again
        //for instances like if the session for the user has expired...
        $messages = $this->_getText(10201);
        return '<div class="paypal_payment_success_text">' . geoString::fromDB($messages[500203]) . '</div>';
    }

    function _getTokenLink($user_id, $reset = false)
    {
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $sb = geoSellerBuyer::getInstance();
        if (!$db->get_site_setting('paypal_allow_sb')) {
            return '';
        }

        $return_url = $db->get_site_setting('paypal_site_url') . '?a=4&b=3';

        if ($reset) {
            $messages = $this->_getText(37);
            return ' (<a href="' . $return_url . '&bp_clear=1">' . geoString::fromDB($messages[500194]) . '</a>)';
        }
        $api_username = $db->get_site_setting('paypal_site_username');
        $api_pass = $db->get_site_setting('paypal_site_pass', true);


        if (strlen($api_username) == 0 || strlen($api_pass) == 0) {
            return ''; //username or pass not set
        }

        //get a reference number and keep track of it.
        $ref_num = $sb->getUserSetting($user_id, 'paypal_tok_ref_num');
        if (strlen($ref_num) != 40) {
            $ref_num = sha1('a ref num randomly generated.' . $user_id . time() . rand());

            //save the setting
            $sb->setUserSetting($user_id, 'paypal_tok_ref_num', $ref_num);
        }

        $url_parts['ApiUsername'] = $api_username;
        $url_parts['ReferenceNumber'] = $ref_num;
        $url_parts['ReturnURLAccept'] = urlencode($return_url . '&bp_accept=1');
        $url_parts['ReturnURLReject'] = urlencode($return_url . '&bp_accept=0');
        $parts = array();
        foreach ($url_parts as $key => $value) {
            $parts[] = $key . '=' . $value;
        }
        $messages = $this->_getText(37);
        $link_txt = geoString::fromDB($messages[500195]);
        return '<a href="' . $this->sellerTokenUrl . '?' . implode('&amp;', $parts) . '">' . $link_txt . '</a>';
    }

    function _getPaymentLink($vars, $link_text)
    {
        //check inputs
        $listing_id = intval($vars['listing_id']);
        $winning_bidder_id = intval($vars['winning_bidder_id']);

        $final_price = $vars['final_price'];


        $timeout = 10; //this is called as part of a page load, so set timeout to be not so long

        if (!$listing_id || !$winning_bidder_id) {
            //input vars not good!
            return '';
        }
        if (!$this->_paypalAllowed()) {
            return '';
        }
        if (!$final_price) {
            trigger_error('DEBUG BIDPAY: Final price 0 or not specified, so can\'t be processed by paypal...');
            return '';
        }
        $listing = geoListing::getListing($listing_id);
        $quantity = 1;
        if ($listing->price_applies == 'item' && isset($vars['bid_quantity'])) {
            $quantity = max(1, (int)$vars['bid_quantity']);
        }

        $sb = geoSellerBuyer::getInstance();

        //see if setting is set in session already
        $paypal_allow_sb = $sb->getListingSetting($listing_id, 'paypal_allow_sb');

        //make sure the seller token is still good
        $seller = $listing->seller;
        $seller_token = $sb->getUserSetting($seller, 'paypal_id');

        //Possible future feature:
        //expand on ability to force user to be set up in paypal before allowed to bid or buy now
        $paypal_force = $sb->getListingSetting($listing_id, 'paypal_force');
        $html = '';
        if ($paypal_allow_sb && $seller_token) {
            $db = DataAccess::getInstance();

            //see if already paid for
            if ($sb->getListingSetting($listing_id, 'paypal_listing_paid')) {
                return $link_text['already_paid'];
            }
            //create link to local install

            $link = $db->get_site_setting('classifieds_url');
            if (strlen($link) == 0) {
                return '';
            }
            $link .= "?a=sb_transaction&amp;action=paypal_payment&amp;listing_id={$listing_id}";
            if ($quantity > 1) {
                $link .= '&amp;quantity=' . $quantity;
            }
            //save the rest as details of the listing, do NOT pass it as part of the url, that would be crazy!
            $winning_bidders = $sb->getListingSetting($listing_id, 'winning_bidder_ids', array());
            if (!in_array($winning_bidder_id, $winning_bidders)) {
                $winning_bidders[] = $winning_bidder_id;
            }
            //save as an array, because buy now auctions could have multiple winners
            //if it's set so the price applies per item

            $sb->setListingSetting($listing_id, 'winning_bidder_ids', $winning_bidders);
            $sb->setListingSetting($listing_id, 'final_price', $final_price);

            if (strlen($link) > 0) {
                if (isset($link_text['payment_link'])) {
                    if (isset($vars['smarty']) && $vars['smarty']) {
                        //New 7.1 code... use a sub-template
                        $params = $vars['params'];
                        $smarty = $vars['smarty'];
                        $listing_tag = $vars['listing_tag'];
                        $tpl_vars = array (
                            'listing_id' => $listing_id,
                            'action' => 'paypal_payment',
                            'link_text' => $link_text,
                            'quantity' => $quantity,
                        );
                        return geoTemplate::loadInternalTemplate(
                            $params,
                            $smarty,
                            $listing_tag . '.tpl',
                            geoTemplate::SYSTEM,
                            'listing_details',
                            $tpl_vars
                        );
                    } else {
                        //old-school way: hard-code link in PHP file
                        $html = "<a href=\"{$link}\">{$link_text['payment_link']}</a>";
                    }
                } else {
                    //must be e-mail... which now uses HTML...
                    $link = "<a href=\"{$link}\">{$link}</a>";
                    $html = "{$link_text['email_text']}<br />\n{$link}";
                }
            }
        }

        return $html;
    }


    function _getPaymentUrl($listing_id, $seller_id, $winning_bidder_id, $listing_details, $final_price, $timeout, $quantity = 1)
    {
        $sb = geoSellerBuyer::getInstance();
        $db = DataAccess::getInstance();

        $linky = '';//$sb->getListingSetting($listing_id,'paypal_PaymentURL');
        if (strlen($linky) > 0) {
            //link already clicked once..
            return $linky;
        }

        //NOTE:  we no longer use conversion rate to apply to the final price, as
        //we send the same currency to paypal that was selected during listing placement

        //Get the currency to use in Paypal, default use USD if none set
        $currency = $sb->getListingSetting($listing_id, 'paypal_currency', 'USD');

        //allow for different implementations of the paypal connection, to allow for things like
        //connecting using PEAR soap, or PHP5 soap, or nuSoap, or whatever.
        $paypal_id = $sb->getUserSetting($seller_id, 'paypal_id');
        $return_url = $db->get_site_setting('classifieds_url') . '?a=sb_transaction&action=paypal_p_result&l_id=' . $listing_id;//my current bids page

        $winner = geoUser::getUser(geoSession::getInstance()->getUserId());

        $paypal_url = $this->_paypal_payment_url . "?";
        $paypal_url .= "receiver_email=" . urlencode($paypal_id);
        $paypal_url .= "&return=" . urlencode($return_url);
        //TODO: add Paypal IPN verification
        $paypal_url .= "&notify_url=" . urlencode($return_url);
        $paypal_url .= "&business=" . urlencode($paypal_id);
        $paypal_url .= "&cmd=_xclick";
        $paypal_url .= "&item_name=" . urlencode(geoString::fromDB($listing_details['title']));
        $paypal_url .= "&image_url=" . urlencode('');
        $paypal_url .= "&item_number=$listing_id";
        $paypal_url .= "&quantity=" . (int)$quantity;
        $paypal_url .= "&shipping=0";
        $paypal_url .= "&handling=" . floatval($this->_getAdditionalFees($listing_details, $quantity));
        $paypal_url .= "&currency_code=" . $currency;
        $paypal_url .= "&amount=" . $final_price;
        $paypal_url .= "&invoice=" . $trans_id;
        $paypal_url .= "&num_cart_items=1";
        if ($winner) {
            $paypal_url .= "&first_name=" . urlencode($winner->firstname);
            $paypal_url .= "&last_name=" . urlencode($winner->lastname);
            $paypal_url .= "&address_street=" . urlencode(trim($winner->address . ' ' . $winner->address_2));
            $paypal_url .= "&address_city=" . urlencode($winner->city);
            $paypal_url .= "&address_state=" . urlencode(geoRegion::getStateNameForUser($winning_bidder_id));
            $paypal_url .= "&address_zip=" . urlencode($winner->zip);
            $paypal_url .= "&payer_email=" . urlencode($winner->email);
        }
        $paypal_url .= "&payer_id=" . urlencode(geoSession::getInstance()->getUserId());
        $paypal_url .= "&custom=" . urlencode($custom_id);
        return $paypal_url;
    }
    /**
     * Function to get additional fee text for bidder.
     */
    function _getAdditionalFees(&$show, $quantity = 1)
    {
        //display any optional fields that add to the cost.
        $quantity = (int)$quantity;

        $additional_fees = geoListing::getAuctionAdditionalFees($show['id']);
        if (!$additional_fees) {
            return 0;
        }
        return ($quantity * $additional_fees['raw']['total']);
    }
    function _getAuctionPricePlans($vars)
    {
        $db = true;
        include(GEO_BASE_DIR . 'get_common_vars.php');

        if ($vars == null) {
            $for = 0;
        }
        $for = intval($for);

        $sql = "SELECT `price_plan_id` as `id` FROM `geodesic_classifieds_price_plans` WHERE `applies_to` = 2";
        if ($for > 0) {
            //$for is cleaned at this point, by intval()
            $sql = "SELECT `category_price_plan_id` as `id` FROM `geodesic_classifieds_price_plans_categories` WHERE `price_plan_id` = $for";
        }

        $result = $db->Execute($sql);
        if (!$result) {
            return array();//empty array, query failed
        }
        $plans = array();
        while ($row = $result->FetchRow()) {
            $plans[] = $row['id'];
        }
        return $plans;
    }

    function _getText($page_id)
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';
        return $db->get_text(true, $page_id);
    }

    private function _paypalAllowed()
    {
        return DataAccess::getInstance()->get_site_setting('paypal_allow_sb');
    }
}
