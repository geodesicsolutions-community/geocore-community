<?php

//payment_gateways/payscape.php


require_once CLASSES_DIR . 'payment_gateways/_cc.php';

# Template CC payment gateway handler

class payscapePaymentGateway extends _ccPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = 'payscape';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = 'payscape';

    /**
     * Sugested, specify the "testing" or "sandbox" URL here so it can
     * easily be updated later if needed.
     *
     * @var string
     */
    private static $_submitUrlTesting = 'https://secure.payscapegateway.com/api/transact.php';

    /**
     * Suggested, specify the "live" URL to process payments through the
     * gateway here so it can easily be updated later if needed.
     *
     * @var string
     */
    private static $_submitUrl = 'https://secure.payscapegateway.com/api/transact.php';

    /**
     * Optional.
     * Used in admin, in paymentGatewayManage::getGatewayTable() which is used in both ajax calls,
     * and to initially display the gateway page.
     *
     * Expects to return an array:
     * array (
     *  'name' => $gateway->name,
     *  'title' => 'What to display in list of gateways', //should be pre-pended with "CC - " so it is easy
     *   //to figure out it's a credit card gateway
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
            'title' => 'CC - Payscape',//how it's displayed in admin
            'head_html' => "<script type='text/javascript'>
		 	Style[1]=[\"white\",\"#000099\",\"\",\"\",\"\",,\"black\",\"#e8e8ff\",\"\",\"\",\"\",,,,2,\"#000099\",2,,,,,\"\",3,,,];
			var TipId = \"tiplayer\";
			var FiltersEnabled = 1;
			//mig_clay();
			var mig_clay_run_already = false;
			function run_mig_clay(){
				if (!mig_clay_run_already){
					mig_clay_run_already = true;
					mig_clay();
				}
			}
			</script>"//optional, if specified,
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
        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());

        $tooltips = array();
        $tooltips['username'] = geoHTML::showTooltip('Payscape Username', 'Enter the username of your Payscape account');
        $tooltips['password'] = geoHTML::showTooltip('Payscape Password', 'Enter the password of your Payscape account');
        $tpl->assign('tooltips', $tooltips);

        $values = array();
        $values['username'] = $this->get('username');
        $values['password'] = $this->get('password');
        $tpl->assign('values', $values);


        return $tpl->fetch('payment_gateways/payscape.tpl');
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
        if (isset($_POST['payscape']) && is_array($_POST['payscape']) && count($_POST['payscape']) > 0) {
            $settings = $_POST['payscape'];
            $this->_updateCommonAdminOptions($settings);

            $this->set('username', $settings['username']);
            $this->set('password', $settings['password']);
            $this->save();
        }
        return true;
    }

    /**
     * Required.
     * Used: in geoCart::payment_choicesDisplay()
     *
     * Defined in parent, need to call the parent and pass
     * an instance of the gateway object for this gateway.
     *
     * Also, need to have the gateway setting "use_cvv2" set
     * to true/false.
     *
     * @return array Results of the call to the parent.
     *
     */
    public static function geoCart_payment_choicesDisplay($gateway = null)
    {
        //Most CC gateways: use this function exactly as-is

        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
        return parent::geoCart_payment_choicesDisplay($gateway);
    }

    /**
     * Required.
     * Used: in geoCart::payment_choicesCheckVars()
     *
     * Defined in parent, need to call the parent and pass
     * an instance of the gateway object for this gateway.
     *
     * Also may need to do any additional input var checking
     * specific to this gateway (but that is not typical)
     *
     */
    public static function geoCart_payment_choicesCheckVars($gateway = null, $skip_checks = null)
    {
        //Most CC gateways: use this function exactly as-is

        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
        return parent::geoCart_payment_choicesCheckVars($gateway);
    }

    /**
     * Required.
     * Used: in geoCart::payment_choicesProcess()
     *
     * This function is where the CC is processed, and is specific to this gateway.
     *
     * Note that this is only called if this payment gateway is the one that was chosen, and there were no errors
     * generated by geoCart_payment_choicesCheckVars().
     *
     * This is where you would create a transaction that would pay for the order, add it to the invoice,
     * connect to the CC to charge it, etc.
     *
     */
    public static function geoCart_payment_choicesProcess()
    {
        /**
         * The things that are normally done by all gateways:
         * parent::_getInfo() - returns array of info of user-input like cc num, exp date, etc.
         *  with certain things already cleaned (see docs on function for which specific things
         *  are already cleaned)
         * parent::_createNewTransaction($order,$gateway,$info) - creates and returns a new
         *  transaction, with the CC number already encrypted.  May need to add info specific
         *  to this gateway using $transaction->set('name','value')
         * parent::_success($order, $transaction, $gateway) - Call to do common things for when the
         *  payment went through successfully.
         * parent::_failure($transactin, $failure_code, $failure_msg) - call to do common things
         *  when the payment was not successful.
         */


        //get the cart
        $cart = geoCart::getInstance();

        //get the gateway since this is a static function
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);

        //get invoice on the order
        $invoice = $cart->order->getInvoice();
        $invoice_total = $invoice->getInvoiceTotal();

        if ($invoice_total >= 0) {
            //DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
            return ;
        }
        //BUILD DATA TO SEND TO GATEWAY TO COMPLETE THE TRANSACTION
        $info = parent::_getInfo();

        //create initial transaction
        try {
            //let parent create a new transaction, since it does all that common stuff
            //for us.
            $transaction = self::_createNewTransaction($cart->order, $gateway, $info);

            //Add the transaction to the invoice
            $transaction->setInvoice($invoice);
            $invoice->addTransaction($transaction);

            //save it so there is an id
            $transaction->save();
        } catch (Exception $e) {
            //catch any error thrown by _createNewTransaction
            trigger_error('ERROR TRANSACTION CART PAYSCAPE: Exception thrown when attempting to create new transaction.');
            return;
        }

        //******************************************************************************
        // PROCESS TRANSACTION HERE

        //URL TO SUBMIT TRANSACTIONS TO (assuming the gateway setting testing_mode is set to 1 to signify using
        // the test url)
        if ($gateway->get("testing_mode") == 1) {
            $url = self::$_submitUrlTesting;
            /*
             * alernate test card numbers:
             * Visa 4111111111111111
             * MasterCard 5431111111111111
             * DiscoverCard 6011601160116611
             * American Express 341111111111111
             *
             * To cause a declined message, pass an amount less than 1.00.
             * To trigger a fatal error message, pass an invalid card number.
             * To simulate an AVS Match, pass 888 in the address1 field, 77777 for zip.
             * To simulate a CVV Match, pass 999 in the cvv field.
             *
             */


            //testing values for a valid Visa
            $ccnumber = '4111111111111111';
            $cvv = '999';
            $ccexp = '1010';
            $amount = '2.00'; // set < 1.00 to simulate a declined transaction

            $username = 'demo';
            $password = 'password';
        } else {
            $url = self::$_submitUrl;
            $ccnumber = $info["cc_number"];
            $cvv = $info["cvv2_code"];  // 123
            $ccexp = sprintf("%02d", $info['exp_date']['Date_Month']) . sprintf("%02d", $info['exp_date']['Date_Year']);
            $amount = number_format($transaction->getAmount(), 2, '.', '');

            $username = $gateway->get('username');
            $password = $gateway->get('password');
        }

        // Billing Details Example
        $firstname = urlencode($info['firstname']);
        $lastname = urlencode($info['lastname']);
        $email = urlencode($info['email']);
        $address1 = urlencode($info['address']);
        $address2 = urlencode($info['address_2']);
        $city = urlencode($info['city']);
        $state = urlencode($info['state']);
        $zip = urlencode($info['zip']);
        $country = urlencode($info['country']); // 3-digits ISO code
        $ipaddress = $_SERVER['REMOTE_ADDR'];


        ##  Create Connection to Gateway Here

        //...

        $post = "type=sale";
        $post .= "&username=" . $username;
        $post .= "&password=" . $password;
        $post .= "&ccnumber=" . $ccnumber;
        $post .= "&ccexp=" . $ccexp;
        $post .= "&amount=" . $amount;
        $post .= "&cvv=" . $cvv;
        $post .= "&orderid=" . $cart->order->getId();
        $post .= "&ipaddress=" . $ipaddress;
        $post .= "&firstname=" . $firstname;
        $post .= "&lastname=" . $lastname;
        $post .= "&address1=" . $address1;
        if (strlen($address2)) {
            $post .= "&address2=" . $address2;
        }
        $post .= "&city=" . $city;
        $post .= "&state=" . $state;
        $post .= "&zip=" . $zip;
        $post .= "&country=" . $country;
        $post .= "&email=" . $email;

        trigger_error('DEBUG PAYSCAPE: About to connect to Payscape. POST URL: ' . $url . '?' . $post);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_REFERER, $cart->db->get_site_setting("classifieds_url"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (($values['verify_peer']) ? 1 : 0));

        $payscape_result = curl_exec($ch);
        curl_close($ch);

        //payscape returns a querystring-style list of param/value pairs:
        //e.g. response=1&responsetext=SUCCESS&authcode=123456&transactionid=848665123&avsresponse=N&cvvresponse=&orderid=260&type=sale&response_code=100

        //rework their response into a useable format
        $pairs = explode('&', $payscape_result);
        $response = array();
        foreach ($pairs as $joined) {
            $split = explode('=', $joined);
            $response[$split[0]] = $split[1];
        }

        trigger_error('DEBUG PAYSCAPE: RESULT FROM CURL: <pre>' . print_r($payscape_result, 1) . '</pre>');

        ##  Process results of gateway here

        /*
         * $responseAction:
         * 1 - approved
         * 2 - declined
         * 3 - error
         */
        $responseAction = $response['response'];
        $responseText = $response['responsetext']; //textual explanation of results
        $responseCode = $response['response_code']; //numeric response code -- see Payscape docs to interpret

        //...

        ## Interpret the results here

        //...



        if ($responseAction == 1) {
            //ACCEPTED
            trigger_error('DEBUG PAYSCAPE: no errors, payment good!');

            //Let the parent do the common stuff for when the transaction was a success
            return self::_success($cart->order, $transaction, $gateway);
        } elseif ($responseAction == 2) {
            //DECLINED
            $message = $responseText;
            trigger_error('DEBUG PAYSCAPE: ' . "DECLINED (Reponse Code $responseCode) " . $message);

            //Let the parent do the common stuff for when the transaction was a failure.
            return self::_failure($transaction, self::FAIL_BANK_DECLINED, $message);
        } else {
            //TRANSACTION ERROR
            $message = $responseText;
            trigger_error('DEBUG PAYSCAPE: ' . "ERROR FROM GATEWAY (Reponse Code $responseCode) " . $message);
            return self::_failure($transaction, self::FAIL_CHECK_GATEWAY_SETTINGS, $message);
        }
    }
}
