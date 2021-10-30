<?php

//payment_gateways/twocheckout.php


require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Template payment gateway handler

class twocheckoutPaymentGateway extends geoPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = 'twocheckout';

    /**
     * Required, Usually the same as the name, this can be used as a means
     * to warn the admin that they may be using 2 gateways that
     * are the same type.  Mostly used to distinguish CC payment gateways
     * (by using type of 'cc'), but can be used for other things as well.
     *
     * @var string
     */
    public $type = 'twocheckout';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = 'twocheckout';

    const ORDER_SEP = ':tran:';

    /**
     * Used during return signal processing so we don't have to keep merging GET and POST
     * @var array
     */
    private $_post_vars;

    /**
     * Optional.
     * Used in admin, in paymentGatewayManage::getGatewayTable() which is used in both ajax calls,
     * and to initially display the gateway page.
     *
     * Expects to return an array:
     * array (
     *  'name' => $gateway->name,
     *  'title' => 'What to display in list of gateways',
     *  'head_html' => 'Will be inserted into the head section of the page.'
     * )
     *
     * Note: if need extra settings besides just being turned on or not,
     *  see the method admin_custom_config()
     * @return array
     *
     */
    public static function admin_display_payment_gateways()
    {
        $return = array (
            'name' => self::gateway_name,
            'title' => '2Checkout',//how it's displayed in admin
            'head_html' => ""//optional, if specified,
        );

        return $return;
    }

    /**
     * Optional.
     * Used: in admin, on payment gateway pages, to see if should show configure button,
     * and to display the contents if that button is clicked.
     *
     * If this function exists, it will be used to display custom
     * settings specific for this gateway using ajax.  If the function does not
     * exist, no settings button will be displayed beside the gateway.
     *
     * @return string HTML to display below gateway when user clicked the settings button
     */
    public function admin_custom_config()
    {

        $db = DataAccess::GetInstance();

        $tpl = new geoTemplate('admin');

        $tpl->assign('payment_type', self::gateway_name);

        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());

        $values['sid'] = geoString::specialChars($this->get('sid'));
        $values['secret'] = geoString::specialChars($this->get('secret'));
        $tpl->assign('values', $values);

        $responseURL = self::_getResponseURL();
        $tpl->assign('responseURL', $responseURL);
        //store the response url in the registry so it only has to be derived once
        $this->set('responseURL', $responseURL);
        $this->serialize();

        return $tpl->fetch('payment_gateways/twocheckout.tpl');
    }

    private static function _getResponseURL()
    {
        $db = DataAccess::getInstance();
        return str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=" . self::gateway_name, $db->get_site_setting("classifieds_url"));
    }

    /**
     * Optional.
     * Used: in admin, in paymentGatewayManage::update_payment_gateways()
     *
     * Use this function to save any additional settings.  Note that this is done IN ADDITION TO the
     * normal "back-end" stuff such as enabling or disabling the gateway and serializing any changes.
     * If this returns false however, that additional stuff will not be done.
     *
     * @return boolean True to continue with rest of update stuff, false to prevent saving rest of settings
     *  for this gateway.
     */
    public function admin_update_payment_gateways()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        //whether allowed to enable this type or not
        $can_enable = true;
        $is_enabled = (isset($_POST['enabled_gateways'][self::gateway_name]) && $_POST['enabled_gateways'][self::gateway_name]);

        if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0) {
            $settings = $_POST[self::gateway_name];

            //save common settings
            $this->_updateCommonAdminOptions($settings);

            //save non-common settings
            $this->set('sid', trim($settings['sid']));
            $this->set('secret', trim($settings['secret']));
            $this->serialize();
        }


        return true;
    }



    /**
     * Optional.
     * Used: in geoCart::payment_choicesDisplay()
     *
     * Should return an associative array that is structured as follows:
     * array(
     *  'title' => string,
     *  'title_extra' => string,
     *  'label_name' => string, //needs to be: self::gateway_name,
     *  'radio_value' => string, //should be self::gateway_name
     *  'help_link' => string, //entire link including a tag and link text, example: $cart->site->display_help_link(3240),
     *  'checked' => boolean, //leave false to let system determine if it is checked or not, true to force being checked
     *  //Items below will be auto generated if left as empty string.
     *  'radio_name' => string,//usually c[self::gateway_name] - this set by system if left as empty string.
     *  'choices_box' => string,//use custom stuff for the entire choice box.
     *  'help_box' => string,//use custom stuff for help link and box surrounding it.
     *  'radio_box' => string,//use custom box for radio
     *  'title_box' => string,//use custom box for title
     *  'radio_tag' => string//use custom tag for radio tag
     * )
     *
     * @return array Associative Array as specified above.
     *
     */
    public static function geoCart_payment_choicesDisplay($vars)
    {
        $cart = geoCart::getInstance(); //get cart to use the display_help_link function

        $itemCostDetails = $vars['itemCostDetails'];

        foreach ($itemCostDetails as $costDetails) {
            if ($costDetails['type'] == 'account_balance') {
                //account balance, check to see if ending balance is positive
                if ($costDetails['extra'] == 'positive_balance') {
                    //2CO cannot be used to add a positive amount onto an account balance,
                    //as that would essentially "pre-pay" for something, which 2CO does not
                    //allow.
                    trigger_error('DEBUG TRANSACTION CART: Not using 2CO gateway, as add to account balance was detected in
					cart which would result in balance going positive, which is not allowed by 2CO terms.');
                    return false;
                }
            }
        }

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
        //Items that don't auto generate if left blank
            'title' => $msgs[500290],
            'title_extra' => '',
            'label_name' => self::gateway_name,
            'radio_value' => self::gateway_name,//should be same as gateway name
            'help_link' => '',
            'checked' => false,//let system figure out if it is checked or not

        //Items below will be auto generated if left blank string.
            'radio_name' => '',//normally you leave all these blank.
            'choices_box' => '',
            'help_box' => '',
            'radio_box' => '',
            'title_box' => '',
            'radio_tag' => '',

        );
        return $return;
    }

    /**
     * Optional.
     * Used: in geoCart::payment_choicesCheckVars()
     *
     * Called no matter what selection is made when selecting payment type, so before doing
     * any checks you need to make sure the payment type selected (in var $_POST['c']['payment_type'])
     * matches this payment gateway.  If there are any problems, use $cart->addError() to specify
     * that it should not go onto the next step, processing the order (aka geoCart_payment_choicesProcess())
     *
     */
    public static function geoCart_payment_choicesCheckVars()
    {
        $cart = geoCart::getInstance();

        if (isset($_POST['c']['payment_type']) && $_POST['c']['payment_type'] == self::gateway_name) {
            //the selected gateway is this one, so check everything for any errors.
            $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
            //          if (!$gateway->get('allow_negative') && $cart->getCartTotal() > $cart->user_data['account_balance'])
            //          {
            //              //example of generating an error, taken from the account balance payment gateway.
            //              $cart->addError();
            //              $cart->error_variables["account_balance"] = $cart->site->messages[2543];
            //          }
        }
    }

    /**
     * Optional.
     * Used: in geoCart::payment_choicesProcess()
     *
     * This function is where any processing is done, and is also where things like re-directing to an external
     * payment site would be done, or updating account balance, etc.
     *
     * Note that this is only called if this payment gateway is the one that was chosen, and there were no errors
     * generated by geoCart_payment_choicesCheckVars().
     *
     * This is where you would create a transaction that would pay for the order, into the invoice.
     *
     */
    public static function geoCart_payment_choicesProcess()
    {
        //VARIABLES TO SEND
        //sid
        //product_id
        //quantity
        //merchant_order_id
        //demo
        trigger_error('DEBUG TRANSACTION: Top of process 2checkout.');
        $cart = geoCart::getInstance();
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
        $user_data = $cart->user_data;

        //get invoice on the order
        $invoice = $cart->order->getInvoice();
        $invoice_total = $due = $invoice->getInvoiceTotal();

        if ($due >= 0) {
            //DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
            return ;
        }

        $transaction = new geoTransaction();
        $transaction->setGateway(self::gateway_name);
        $transaction->setUser($cart->user_data['id']);
        $transaction->setStatus(0); //for now, turn off until it comes back from paypal IPN.
        $transaction->setAmount(-1 * $due);//set amount that it affects the invoice
        $msgs = $cart->db->get_text(true, 183);
        $transaction->setDescription($msgs[500575]);

        $transaction->setInvoice($invoice);

        $transaction->save();

        $testing = $gateway->get('testing_mode');
        $sid = geoString::specialChars($gateway->get('sid'));
        $responseURL = self::_getResponseURL();


        //build redirect
        $formdata = $cart->user_data['billing_info'];
        $cc_url = "https://www.2checkout.com/2co/buyer/purchase?";

        $cc_url .= "sid=" . $sid;
        $cc_url .= "&fixed=Y";
        $cc_url .= "&x_receipt_link_url=" . urlencode($responseURL);
        $cc_url .= "&cart_order_id=" . $transaction->getId();
        $cc_url .= "&total=" . sprintf("%01.2f", $transaction->getAmount());
        if ($testing) {
            $cc_url .= "&demo=Y";
        }
        $cc_url .= "&card_holder_name=" . urlencode($formdata['firstname'] . " " . $formdata['lastname']);
        $cc_url .= "&street_address=" . urlencode($formdata['address'] . " " . $formdata['address_2']);
        $cc_url .= "&city=" . urlencode($formdata['city']);
        $cc_url .= "&state=" . urlencode($formdata['state']);
        $cc_url .= "&zip=" . urlencode($formdata['zip']);
        $cc_url .= "&country=" . urlencode($formdata['country']);
        $cc_url .= "&email=" . urlencode($formdata['email']);
        $cc_url .= "&phone=" . urlencode($formdata['phone']);
        $cc_url .= "&merchant_order_id=" . $cart->order->getId() . self::ORDER_SEP . $transaction->getId();

        //remember URL for debugging if needed
        $transaction->set('cc_url', $cc_url);
        $transaction->save();

        //add transaction to invoice
        $invoice->addTransaction($transaction);

        //set order to pending
        $cart->order->setStatus('pending');

        //stop the cart session
        $cart->removeSession();
        trigger_error('DEBUG TRANSACTION: 2checkout URL: ' . $cc_url);
        require GEO_BASE_DIR . 'app_bottom.php';
        //go to 2checkout to complete
        header("Location: " . $cc_url);
        exit;
    }

    /**
     * Optional.
     * Used: in geoCart::process_orderDisplay()
     *
     * This is a good place to do things like display a message that the listing has been placed on hold until
     * payment is received, or place to display other similar messages.
     *
     * Note that there is no process_orderCheckVars() or process_orderProcess() since this page is only meant
     * for display purposes, for any processing that needs to be done, needs to go in geoCart::payment_choicesProcess()
     *
     */
    public static function geoCart_process_orderDisplay()
    {
        //use to display some success/failure page, if that applies to this type of gateway.


        //build response for user
        $cart = geoCart::getInstance();
        $db = DataAccess::getInstance();
        $messages = $db->get_text(true, 180);

        $tpl = new geoTemplate('system', 'payment_gateways');
        $tpl->assign($cart->getCommonTemplateVars());
        $tpl->assign('page_title', $messages[3142]);
        $tpl->assign('page_desc', $messages[3143]);
        $tpl->assign('success_failure_message', $messages[3167]);
        $tpl->assign('my_account_url', $db->get_site_setting('classifieds_file_name') . '?a=4&amp;b=3');
        $tpl->assign('my_account_link', $messages[3169]);

        $invoice = $cart->order->getInvoice();
        if (is_object($invoice) && $invoice->getId()) {
            $tpl_vars['invoice_url'] = geoInvoice::getInvoiceLink($invoice->getId(), false, defined('IN_ADMIN'));
        }

        $html = $tpl->fetch('shared/transaction_approved.tpl');
        $cart->site->body .= $html;
        $cart->site->display_page();

        return $html;
    }

    /**
     * called by transaction_process.php
     *
     * This is where we handle the response from the gateway's server
     *
     */
    public function transaction_process()
    {
        $this->_post_vars = array_merge($_GET, $_POST);

        if (!isset($this->_post_vars['cart_order_id'])) {
            //process as an INS signal
            return $this->_processINS();
        }
        //Need to add <base ...> tag so it displays correctly
        geoView::getInstance()->addBaseTag = true;

        //VARIABLES PASSED-BACK
        //order_number - 2Checkout order number
        //card_holder_name
        //street_address
        //city
        //state
        //zip
        //country
        //email
        //phone
        //cart_order_id
        //credit_card_processed
        //total
        //ship_name
        //ship_street_address
        //ship_city
        //ship_state
        //ship_country
        //ship_zip
        trigger_error('DEBUG TRANSACTION: Top of transaction_process.');

        if (!$this->get('testing_mode')) {
            //check the hash
            $hash = $this->_genHash(false);
            if (!$hash || ($this->_post_vars['key'] !== $hash)) {
                //NOTE:  if testing mode turned on, it will skip the normal demo mode checks.
                trigger_error('DEBUG TRANSACTION: Payment failure, secret word/MD5 hash checks failed.');
                self::_failure($transaction, 2, "No response from server, check vendor settings");
                return;
            }
            //gets this far, the md5 hash check passed, so safe to proceed.
        }

        //true if $_SERVER['HTTP_REFERER'] is blank or contains a value from $referer_array
        trigger_error('DEBUG TRANSACTION: MD5 hash check was successful.');

        trigger_error('DEBUG TRANSACTION: 2checkout vars: ' . print_r($this->_post_vars, 1));
        //get objects
        $transaction = geoTransaction::getTransaction($this->_post_vars['cart_order_id']);
        if (!$transaction || $transaction->getID() == 0) {
            //failed to reacquire the transaction, or transaction does not exist
            trigger_error('DEBUG TRANSACTION: Could not find transaction using: ' . $this->_post_vars['cart_order_id']);
            self::_failure($transaction, 2, "No response from server");
            return;
        }
        $invoice = $transaction->getInvoice();
        $order = $invoice->getOrder();

        //store transaction data
        $transaction->set('twocheckout_response', $this->_post_vars);
        //transaction will be saved when order is saved.

        if (($this->_post_vars["order_number"]) && ($this->_post_vars["cart_order_id"])) {
            //if ($this->_post_vars["credit_card_processed"] == "Y")
            if (strcmp($this->_post_vars["credit_card_processed"], "Y") == 0) {
                //CC processed ok, now do stuff on our end
                //Might want to add further checks, like to check MD5 hash (if possible),
                //or check that the total is correct.
                trigger_error('DEBUG TRANSACTION: Payment success!');
                //let the objects do their thing to make this active
                self::_success($order, $transaction, $this);
            } else {
                //error in transaction, possibly declined
                trigger_error('DEBUG TRANSACTION: Payment failure, credit card not processed.');
                self::_failure($transaction, $this->_post_vars["credit_card_processed"], "2Checkout: Card not approved");
            }
        } else {
            trigger_error('DEBUG TRANSACTION: Payment failure, no order number or cart order ID.');
            self::_failure($transaction, 2, "No response from server");
        }
    }

    private function _processINS()
    {
        //NOTE:  We do NOT need to display a page on success/failure for INS signals,
        //like is needed for normal signal...
        if (!isset($this->_post_vars['message_type']) || $this->_post_vars['message_type'] !== 'ORDER_CREATED') {
            //don't care about this signal, it is not related to order created.
            trigger_error("DEBUG TRANSACTION:  Ignoring signal, appears to be INS but NOT the order_created signal.");
            return;
        }

        trigger_error("DEBUG TRANSACTION:  Processing signal as INS signal.  Var details:\n" . print_r($this->_post_vars, 1));

        //make sure it is valid...
        $hash = $this->_genHash(true);
        if (!$hash || $hash !== $this->_post_vars['md5_hash']) {
            trigger_error('DEBUG TRANSACTION:  Authentication check failed, MD5 hash did not match.  Check secret word settings for gateway.');
            return;
        }

        if (!isset($this->_post_vars['vendor_order_id'])) {
            trigger_error("DEBUG TRANSACTION:  No vendor_order_id present, not able to process signal any further.");
            return;
        }
        //get the transaction...
        $parts = explode(self::ORDER_SEP, $this->_post_vars['vendor_order_id']);
        if (!isset($parts[1])) {
            trigger_error("DEBUG TRANSACTION:  vendor_order_id does not contain transaction id, cannot process INS signal.  Value is {$this->_post_vars['vendor_order_id']}.");
            return;
        }
        $transaction = geoTransaction::getTransaction((int)$parts[1]);
        if (!$transaction) {
            trigger_error("DEBUG TRANSACTION:  Problem getting transaction for ID {$parts[1]}");
            return;
        }
        if ($transaction->getStatus()) {
            trigger_error("DEBUG TRANSACTION:  Transaction already active!  Nothing is needed for this INS signal as the main signal already processed successfully.");
            return;
        }
        $order = geoOrder::getOrder($parts[0]);
        if (!$order) {
            trigger_error("DEBUG TRANSACTION:  First part of vendor_order_id, for the order ID, not able to get order from it.  Value: {$parts[0]}");
            return;
        }
        trigger_error("DEBUG TRANSACTION:  INS Approved!  Activating transaction, INS payment signal is valid and for transaction not already approved.");
        $transaction->set('ins_data', $post_vars);
        self::_success($order, $transaction, $this, true);
    }

    private function _genHash($insSignal = false)
    {
        $secret = $this->get('secret');
        $vendor = $this->get('sid');
        if (!$secret || !$vendor) {
            //cannot calculate MD5 hash without secret word and vendor number
            return false;
        }
        //what to use for the hash is different depending on if INS signal or just return thingy
        if ($insSignal) {
            //calculate using INS version
            $hash_this = $this->_post_vars['sale_id'] . $vendor . $this->_post_vars['invoice_id'] . $secret;
        } else {
            //calculate using normal non-ins version
            $hash_this = $secret . $vendor . $this->_post_vars['order_number'] . $this->_post_vars['total'];
        }
        return strtoupper(md5($hash_this));
    }
}
