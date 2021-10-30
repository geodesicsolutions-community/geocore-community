<?php

//payment_gateways/beanstream.php
/**
 * This is a "developer template" for creating a new payment gateway that
 * accepts CC number, a developer would use this file as a starting point for
 * creating such a new payment gateway.
 *
 * @package System
 * @since Version 4.0.0
 */

/**
 * This extends the _ccPaymentGateway class, so need to include that file.
 */

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

/**
 * Template CC payment gateway handler, a developer would use this as a starting
 * point if one wished to create a payment gateway that accepts credit cards.
 * @package System
 * @since Version 4.0.0
 */
class beanstreamPaymentGateway extends _ccPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = 'beanstream';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = 'beanstream';

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
            'title' => 'CC - Beanstream',//how it's displayed in admin
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
     * exist, no configure button will be displayed beside the gateway.
     *
     * @return string HTML to display below gateway when user clicked the settings button
     */
    public function admin_custom_config()
    {
        $db = DataAccess::GetInstance();

        $tpl = new geoTemplate('admin');

        $tpl->assign('payment_type', self::gateway_name);

        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(false));

        $tooltips['merchant_id'] = geoHTML::showTooltip('Merchant ID', 'This was emailed to you when you created your BeanStream account, or may be found in your BeanStream Member Area');
        $tooltips['api_passcode'] = geoHTML::showTooltip('API Passcode', 'Login to your Beanstream Member Area, then navigate to Administration -> Account -> Order Settings. Locate the API access passcode field and copy the passcode. If one is not there, you can generate a new one by hitting the "Generate New Code" button');

        $tpl->assign('tooltips', $tooltips);

        $values = array(
            'merchant_id' => $this->get('merchant_id'),
            'api_passcode' => $this->get('api_passcode')
        );
        $tpl->assign('values', $values);

        return $tpl->fetch('payment_gateways/beanstream.tpl');
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

        if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0) {
            $settings = $_POST[self::gateway_name];

            //save common settings
            $this->_updateCommonAdminOptions($settings);

            //save non-common settings
            $this->set('merchant_id', trim($settings['merchant_id']));
            $this->set('api_passcode', trim($settings['api_passcode']));
            //always use cvv2 code
            $this->set('use_cvv2', true);
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
     * @param null $gateway This var will always be null here, this method must
     *   generate the value and pass it into the parent method
     * @return array Results of the call to the parent.
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
     * @param null $gateway Will never be passed in, this var must be generated
     *   and passed to the parent.
     * @param null $skip_checks Will never be passed in, if applicable this should
     *   be populated when passing to parent.  See parent docs for more info.
     * @return array Results of the call to the parent.
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
            trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: Exception thrown when attempting to create new transaction.');
            return;
        }

        //******************************************************************************
        // PROCESS TRANSACTION HERE


        $url = 'https://www.beanstream.com/api/v1/payments';

        $merchantId = $gateway->get('merchant_id');
        $passcode = $gateway->get('api_passcode');

        $params = array();
        $params['merchant_id'] = $merchantId;
        $params['passcode'] = $passcode;
        $params['order_number'] = $cart->order->getId();
        $params['amount'] = number_format($transaction->getAmount(), 2, '.', '');
        $params['payment_method'] = 'card';
        $params['card'] = array(
            'name' => $info['firstname'] . ' ' . $info['lastname'],
            'number' => $info["cc_number"],
            'expiry_month' => str_pad($info['exp_month'], 2, '0', STR_PAD_LEFT),
            'expiry_year' => substr($info['exp_year'], 2),
            'cvd' => $info["cvv2_code"]
        );
        $params['username'] = 'thisisausername';
        $params['password'] = 'thisisapassword';
        if ($info['firstname']) {
            $params['billing']['name'] = $info['firstname'];
        }
        if ($info['lastname']) {
            $params['billing']['name'] .= ' ' . $info['lastname'];
        }
        if ($info['address']) {
            $params['billing']['address_line1'] = $info['address'];
        }
        if ($info['address_2']) {
            $params['billing']['address_line2'] = $info['address_2'];
        }
        if ($info['city']) {
            $params['billing']['city'] = $info['city'];
        }
        if ($info['state']) {
            $params['billing']['province'] = $info['state'];
        }
        if ($info['country']) {
            $params['billing']['country'] = $info['country'];
        }
        if ($info['zip']) {
            $params['billing']['postal_code'] = $info['zip'];
        }
        if ($info['email']) {
            $params['billing']['email_address'] = $info['email'];
        }
        if ($info['phone']) {
            $params['billing']['phone_number'] = $info['phone'];
        }
        $params['ref1'] = $transaction->getId();


        $params = json_encode($params); //post needs to be JSON for this gateway

        $auth = base64_encode($merchantId . ":" . $passcode);

        $headers = array(
            'Content-Type: application/json', //required for this gateway; note that urlPostContents sets application/x-www-form-urlencoded, but this being here seems sufficient to override
            'Authorization: Passcode ' . $auth
        );

        ##  Create Connection to Gateway Here
        $response = geoPC::urlPostContents($url, $params, 30, $headers);

        $data = json_decode($response);

        if ($data->approved && $data->approved == 1) {
            //TRANSACTION SUCCESSFUL!!
            trigger_error('DEBUG TRANSACTION CART ' . self::gateway_name . ': no errors, payment good!');

            //Let the parent do the common stuff for when the transaction was a success
            return self::_success($cart->order, $transaction, $gateway);
        } else {
            $message = $data->message ? $data->message : 'Unknown Error';
            if ($data->details) {
                foreach ($data->details as $detail) {
                    $message .= '<br />' . $detail->message;
                }
            }

            trigger_error('ERROR TRANSACTION CART ' . self::gateway_name . ': ' . $message);

            //Let the parent do the common stuff for when the transaction was a failure.
            return self::_failure($transaction, -1, $message);
        }
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
     * Most CC gateways can leave this un-defined, as it is handled by the parent.
     *
     */
    public static function geoCart_process_orderDisplay()
    {
        //use to display some success/failure page, if that applies to this type of gateway.

        //most can just leave it up to the parent to do, since this is pretty standard.
        return parent::geoCart_process_orderDisplay();
    }
}
