<?php

//payment_gateways/paymentexpress.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.4.6-19-ga99ec3d
##
##################################

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';


# Template payment gateway handler

class paymentexpressPaymentGateway extends geoPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = 'paymentexpress';

    /**
     * Required, Usually the same as the name, this can be used as a means
     * to warn the admin that they may be using 2 gateways that
     * are the same type.  Mostly used to distinguish CC payment gateways
     * (by using type of 'cc'), but can be used for other things as well.
     *
     * @var string
     */
    public $type = 'paymentexpress';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = 'paymentexpress';

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
            'title' => 'Payment Express',//how it's displayed in admin
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

        $tpl = new geoTemplate('admin');
        $tpl->assign('payment_type', self::gateway_name);
        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(false));

        $tooltips['user_id'] = geoHTML::showTooltip('PxPayUserId', 'User ID assigned on account setup by PaymentExpress');
        $tooltips['access_key'] = geoHTML::showTooltip('PxPayKey', 'Your 3DES encryption key assigned on account setup by PaymentExpress');
        $tooltips['receipt_email'] = geoHTML::showTooltip('Email Address to Receive Payment Receipt Notifications', 'Email Address of admin who will receive Paymentexpress notification emails');
        $tooltips['currency_codes'] = geoHTML::showTooltip('Currency Codes', 'Possible currencies that PaymentExpress will accept. Choose the currency you accept payments in from this list.');
        $tpl->assign('tooltips', $tooltips);

        $values['user_id'] = $this->get('user_id');
        $values['access_key'] = $this->get('access_key');
        $values['receipt_email'] = $this->get('receipt_email');
        $values['currency_codes'] = $this->get('currency_codes', 'USD');
        $tpl->assign('values', $values);


        //set up currency options dropdown
        $currencies = array(
                            'CAD' => 'Canadian Dollar',
                            'CHF' => 'Swiss Franc',
                            'DKK' => 'Danish Krone',
                            'EUR' => 'Euro',
                            'FRF' => 'French Franc',
                            'GBP' => 'United Kingdom Pound',
                            'HKD' => 'Hong Kong Dollar',
                            'JPY' => 'Japanese Yen',
                            'NZD' => 'New Zealand Dollar',
                            'SGD' => 'Singapore Dollar',
                            'THB' => 'Thai Bhat',
                            'USD' => 'United States Dollar',
                            'ZAR' => 'Rand',
                            'AUD' => 'Australian Dollar',
                            'WST' => 'Samoan Tala',
                            'VUV' => 'Vanuatu Vatu',
                            'TOP' => "Tongan Pa'anga",
                            'SBD' => 'Solomon Islands Dollar',
                            'PGK' => 'Papua New Guinea Kina',
                            'MYR' => 'Malaysian Ringgit',
                            'KWD' => 'Kuwaiti Dinar',
                            'FJD' => 'Fiji Dollar');
        $currencyOptions = '';
        foreach ($currencies as $code => $name) {
            $currencyOptions .= '<option value=' . $code . ' ' . (($values['currency_codes'] == $code) ? 'selected="selected"' : '') . ' >' . $name . '</option>\r\n';
        }
        $tpl->assign('currencyOptions', $currencyOptions);

        return $tpl->fetch('payment_gateways/paymentexpress.tpl');
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
        if (isset($_POST['paymentexpress']) && is_array($_POST['paymentexpress']) && count($_POST['paymentexpress']) > 0) {
            $settings = $_POST['paymentexpress'];
            $this->_updateCommonAdminOptions($settings);

            $this->set('user_id', $settings['user_id']);
            $this->set('access_key', $settings['access_key']);
            $this->set('mac_key', $settings['mac_key']);
            $this->set('receipt_email', $settings['receipt_email']);
            $this->set('currency_codes', $settings['currency_codes']);

            $this->save();
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
    public static function geoCart_payment_choicesDisplay()
    {
        $cart = geoCart::getInstance(); //get cart to use the display_help_link function

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[500288],
            'title_extra' => '',//usually make this empty string.
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
            $user_data = $cart->user_data;
            $db = $cart->db;
            $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);


            //get invoice on the order
            $invoice = $cart->order->getInvoice();

            if ($cart->order->getOrderTotal() <= 0) {
                //DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
                $cart->addError();
                return false;
            }

            //create initial transaction
            try {
                $transaction = new geoTransaction();
                $transaction->setGateway(self::gateway_name);
                $transaction->setUser($cart->user_data['id']);
                $transaction->setStatus(0); //for now, turn off until it comes back fromgateway
                $transaction->setAmount($cart->order->getOrderTotal());//set amount that it affects the invoice
                $transaction->setInvoice($invoice);

                //save it so there is an id
                $transaction->save();
            } catch (Exception $e) {
                $cart->addError()->addErrorMsg('transaction', 'Internal Error');
                trigger_error('ERROR TRANSACTION CART PAYMENTEXPRESS: Exception thrown when attempting to create new transaction.');
                return false;
            }

            $requestUrl = "https://sec.paymentexpress.com/pxpay/pxaccess.aspx";

            $http_host = getenv("HTTP_HOST");
            $request_uri = getenv("SCRIPT_NAME");
            $server_url = "http://$http_host";

            //NOTE: paymentexpress does not allow query strings in $returnURL
            //so it has its own dedicated landing page which sets $_GET['gateway'] and includes the main one
            $returnURL = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process_paymentexpress.php", $db->get_site_setting("classifieds_url"));

            $Address1 = urlencode($user_data['address'] . " " . $user_data['address_2']);
            $Address2 = urlencode($user_data['city']) . " " . urlencode($user_data['state']) . " " . urlencode($user_data['country']) . " " . urlencode($user_data['zip']);

            $xml = "<GenerateRequest>
			  			<PxPayUserId>" . $gateway->get('user_id') . "</PxPayUserId>
						<PxPayKey>" . $gateway->get('access_key') . "</PxPayKey>
						<AmountInput>" . sprintf("%01.2f", $transaction->getAmount()) . "</AmountInput>
						<CurrencyInput>" . $gateway->get('currency_codes') . "</CurrencyInput>
						<MerchantReference>" . $transaction->getId() . "</MerchantReference>
						<EmailAddress>" . $gateway->get('receipt_email') . "</EmailAddress>
						<TxnData1>" . $transaction->getDescription() . "</TxnData1>
						<TxnData2>$Address1</TxnData2>
						<TxnData3>$Address2</TxnData3>
						<TxnType>Purchase</TxnType>
						<TxnId>" . $transaction->getId() . "</TxnId>
						<UrlSuccess>$returnURL</UrlSuccess>
						<UrlFail>$returnURL</UrlFail>
					</GenerateRequest>";

            //send initial request to gateway
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $requestUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //for maximum compatibility
            $result = curl_exec($ch);
            curl_close($ch);

            if (!$result) {
                //something's wrong
                $cart->addError()->addErrorMsg('gateway', 'Failed to contact gateway');
                //kill transaction before leaving
                geoTransaction::remove($transaction->getId());
                return false;
            }

            //$result is an xml string that contains whether the setup was valid, and a URL to redirect to in order to complete payment
            $request = new SimpleXMLElement($result);

            $valid = $request['valid'];
            if ($valid != 1) {
                //setup invalid -- something's wrong
                $cart->addError()->addErrorMsg('gateway', 'Invalid request');
                //kill transaction before leaving
                geoTransaction::remove($transaction->getId());
                return false;
            }
            $redirect = $request->URI;
            $cart->order->set('paymentexpress_redirect', $redirect);
            return true;
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

        $cart = geoCart::getInstance();

        //get the URL to redirect to
        $redirect = $cart->order->get('paymentexpress_redirect');

        if (!$redirect) {
            //somthing's very wrong...
            return false;
        }

        //set order status to Pending to prep for sending data to gateway
        $cart->order->setStatus('pending');

        //stop the cart session
        $cart->removeSession();

        require GEO_BASE_DIR . 'app_bottom.php';
        //redirect to the given URL, where payment will be accepted
        header("Location: $redirect");
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
    }

    public function transaction_process()
    {

        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);

        $encryptedResponse = $_REQUEST['result'];

        //curl encryptedResponse to paymentexpress for decryption. this step ensures that the response is valid
        $xml = "<ProcessResponse>
					<PxPayUserId>" . $gateway->get('user_id') . "</PxPayUserId>
					<PxPayKey>" . $gateway->get('access_key') . "</PxPayKey>
					<Response>$encryptedResponse</Response>
				</ProcessResponse>";


        $requestUrl = "https://sec.paymentexpress.com/pxpay/pxaccess.aspx";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //for maximum compatibility
        $result = curl_exec($ch);
        curl_close($ch);

        if (!$result) {
            //something's wrong
            trigger_error('DEBUG TRANSACTION: no curl result');
        }

        $response = new SimpleXMLElement($result);


        $transactionId = intval(trim($response->MerchantReference));
        trigger_error('DEBUG TRANSACTION: The transaction ID is: ' . $transactionId);
        $transaction = geoTransaction::getTransaction($transactionId);
        $success = $response->Success;

        //save transaction data
        $transaction->set('paymentexpress_response', $result);
        $transaction->save();


        if (!$response['valid']) {
            trigger_error('DEBUG TRANSACTION: (FAILURE) Gateway said this was invalid (Connection error?)');
            self::_failure($transaction, 0, "PaymentExpress: Connection Error");
        } elseif ($success) {
            trigger_error('DEBUG TRANSACTION: (SUCCESS) Gateway says transaction is OK');
            $order = $transaction->getInvoice()->getOrder();
            self::_success($order, $transaction, $gateway);
        } else {
            trigger_error('DEBUG TRANSACTION: (FAILURE) Unknown error or payment declined');
            self::_failure($transaction, 0, "PaymentExpress: Declined");
        }
    }
}
