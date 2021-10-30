<?php

//payment_gateways/mollie.php


/**
 * This requires the geoPaymentGateway class, so include it just to be on the
 * safe side.
 */

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

/**
 * this implements the iDEAL payment method from the mollie.nl gateway
 *
 * @package System
 * @since Version 7.4.1
 */
class molliePaymentGateway extends geoPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = 'mollie';

    /**
     * Required, Usually the same as the name, this can be used as a means
     * to warn the admin that they may be using 2 gateways that
     * are the same type.  Mostly used to distinguish CC payment gateways
     * (by using type of 'cc'), but can be used for other things as well.
     *
     * @var string
     */
    public $type = 'mollie';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = 'mollie';

    /**
     * Optional.
     * Used in admin, in paymentGatewayManage::getGatewayTable() which is used in both ajax calls,
     * and to initially display the gateway page.
     *
     * Expects to return an array:
     * array (
     *  'name' => $gateway->name,
     *  'title' => 'What to display in list of gateways',
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
            'title' => 'iDEAL via Mollie.nl',//how it's displayed in admin
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

        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(false));

        $tooltips['api_key'] = geoHTML::showTooltip('Mollie.nl API Key', 'Either your Live API Key (begins with "live_") or Test API Key (begins with "test_") from mollie.nl. Note that using the Test API Key will automatically invoke Testing Mode.');

        $tpl->assign('tooltips', $tooltips);

        $values = array(
            'api_key' => $this->get('api_key')
        );
        $tpl->assign('values', $values);

        return $tpl->fetch('payment_gateways/mollie.tpl');
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
            $this->set('api_key', trim($settings['api_key']));
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
     * @param array $vars Array of info, see source of method for further documentation.
     * @return array Associative Array as specified above.
     *
     */
    public static function geoCart_payment_choicesDisplay($vars)
    {
        $cart = geoCart::getInstance(); //get cart to use the display_help_link function

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[502287],
            'title_extra' => '',//usually make this empty string.
            'label_name' => self::gateway_name,
            'radio_value' => self::gateway_name,//should be same as gateway name
            'help_link' => '',//$cart->site->display_help_link(3240),
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
        //nothing to do here
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
        $transaction->setStatus(0); //for now, turn off until it comes back from gateway
        $transaction->setAmount(-1 * $due);//set amount that it affects the invoice
        $msgs = $cart->db->get_text(true, 183);

        $transaction->setDescription($msgs[502286]);

        $transaction->setInvoice($invoice);

        $transaction->save();

        $testing = $gateway->get('testing_mode');

        //for this gateway, we send a GET request with some basic info, and they return the target URL to send the user to

        $amount = number_format($transaction->getAmount(), 2, '.', ''); //get the price to two decimal places
        //old api: $amount = str_replace('.','',$amount); // this gateway wants the price in cents as an integer

        if ($amount < 1.20) {
            //below the minimum transaction amount for iDEAL. Cannot use this gateway
            return false;
        }

        $url = "https://api.mollie.nl/v1/payments";
        $auth = "Authorization: Bearer " . $gateway->get('api_key');
        $params = array(
            'amount' => $amount,
            'redirectUrl' => geoFilter::getBaseHref() . 'transaction_process.php?gateway=mollie&action=return&trans=' . $transaction->getId(),
            'webhookUrl' => geoFilter::getBaseHref() . 'transaction_process.php?gateway=mollie&action=update',
            'description' => $transaction->getDescription(),
            'method' => 'ideal'
        );
        $response = geoPC::urlPostContents($url, $params, 30, array($auth));
        //response is JSON -- parse it
        $response = json_decode($response);


        $redirect_to = $response->links->paymentUrl;

        if (!$redirect_to) {
            $cart->addError();
            $cart->addErrorMsg('mollie', 'A gateway error occurred: ' . ($response->error->message) ? $response->error->message : 'Unknown error');
            return false;
        }

        $transaction->setGatewayTransaction($response->id);
        $transaction->set('isMollieTransaction', 1); //just an easy way to identify this later
        $transaction->save();

        //add transaction to invoice
        $invoice->addTransaction($transaction);

        //set order to pending
        $cart->order->setStatus('pending');

        if (!geoSession::getInstance()->getUserId()) {
            //this is an anonymous user. flag the transaction as such and clear the cart now, since we won't be able to reacquire later
            $transaction->set('isAnonymous', 1);
            $cart->removeSession();
        } else {
            //getting a little fancy with this gateway. Instead of clearing the cart before the user leaves, save the cart ID
            //that way, if the user cancels payment, we can still show him his item
            //and we also have a hook to clear the cart contents on success
            $transaction->set('cart_id', $cart->cart_variables['id']);
        }

        header("Location: " . $redirect_to);
        include_once GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    /**
     * Optional.
     * Used:  In transaction_process.php to allow processing of "signals" back
     * from a payment processor.
     *
     * Called from file /transaction_process.php - this function should
     * be used when expecting some sort of processing to take place where
     * the external gateway needs to contact the software back (like Paypal IPN)
     *
     * It is up to the function to verify everything, and make any changes needed
     * to the transaction/order.
     *
     * Note that this is NOT where normal payment processing would happen when someone
     * clicks the payment button, this is only called by transaction_process.php
     * when a payment signal for this gateway is received.  To use, you would specify
     * the url:
     *
     * https://example.com/transaction_process.php?gateway=mollie
     *
     * As the "signal/notification URL" to send notifications to (obviously would need
     * to adjust for the actual payment gateway and actual site's URL).  Don't
     * forget to authenticate the signal in some way, to validate it is indeed
     * coming from the payment processor!
     */
    public function transaction_process()
    {
        if ($_GET['action'] === 'return') {
            //this is the user coming back. figure out order status so we show the correct page, but DON'T actually update anything in the database (do that on the webhook 'update' call)

            $session = geoSession::getInstance();
            $session->initSession();


            $trans_id = $_GET['trans'];
            $transaction = geoTransaction::getTransaction($trans_id);
            $validate = $transaction->get('isMollieTransaction');

            if (!$session->getUserId() && $transaction->get('isAnonymous') && $validate) {
                //this is an anonymous transaction. the cart has already been cleared, so skip places below that try to clear it
                $clearCart = false;
            } elseif ($transaction->getUser() != $session->getUserId()) {
                //This is not anonymous, but THIS ISN'T YOUR TRANSACTION! Go away!
                $validate = $clearCart = false;
            } else {
                //ok, we can clear stuff!
                $cart_id = $transaction->get('cart_id');
                $clearCart = true;
            }

            if ($validate == 1) {
                //ask Mollie for information about this transaction
                $gateway_trans_id = $transaction->getGatewayTransaction();

                $url = "https://api.mollie.nl/v1/payments/$gateway_trans_id";
                $auth = "Authorization: Bearer " . $this->get('api_key');
                $response = geoPC::urlGetContents($url, 30, array($auth));
                $response = json_decode($response);

                $status = $response->status;
                if ($status === 'paid' || $status === 'paidout') {
                    //payment is done. clear cart and then show success page
                    if ($clearCart) {
                        $this->_clearCart($cart_id);
                    }
                    self::_successFailurePage(true, 'active', true, $transaction->getInvoice(), $transaction);
                } elseif ($status === 'pending' || $status === 'open') {
                    //complete, but not processed yet
                    if ($clearCart) {
                        $this->_clearCart($cart_id);
                    }
                    self::_successFailurePage(true, 'pending');
                } elseif ($status === 'cancelled') {
                    //user didn't complete payment (used "Return to Website" link)
                    //note: DO NOT clear cart here. Send the user back to it, instead
                    header("Location: " . DataAccess::getInstance()->get_site_setting('classifieds_url') . "?a=cart");
                    include_once GEO_BASE_DIR . 'app_bottom.php';
                    exit();
                } else {
                    //probably something got declined. show failure page
                    //don't need to clear the cart here, either, to make it that much easier for user to use a valid payment method
                    self::_successFailurePage(false);
                }
            } else {
                //invalid transaction...shouldn't be here
                header("Location: " . DataAccess::getInstance()->get_site_setting('classifieds_url') . "?a=cart");
                include_once GEO_BASE_DIR . 'app_bottom.php';
                exit();
            }
        } elseif ($_GET['action'] === 'update') {
            //backend "webhook" notification from the gateway
            $trans_id = $_POST['id'];
            $transaction = geoTransaction::getTransaction($trans_id);

            $validate = $transaction->get('isMollieTransaction');
            if ($validate != 1) {
                //something's wrong
                self::_failure($transaction, -1, "Failed to re-acquire transaction");
                return false;
            }

            if ($transaction->getStatus() == 1) {
                //this transaction is already live, which means this request is the mollie API telling us something we don't really care about
                //such as the payment clearing the bank or some such
                //no need to bother with the bounceback to check status...just kill this process and move on with life
                return true;
            }

            //gateway's webhook just told us "something changed" with order having given ID
            //it's now our job to ask for an update
            $url = "https://api.mollie.nl/v1/payments/$trans_id";
            $auth = "Authorization: Bearer " . $this->get('api_key');
            $response = geoPC::urlGetContents($url, 30, array($auth));
            $response = json_decode($response);

            $status = $response->status;


            if ($status === 'paid' || $status === 'paidout') {
                $order = $transaction->getInvoice()->getOrder();
                self::_success($order, $transaction, $this, true);
            } else {
                self::_failure($transaction, $status, "Failed processing with status: " . $status);
            }
        }
    }

    private function _clearCart($cart_id)
    {
        $cart = geoCart::getInstance();
        $cart->removeSession($cart_id); //still clear the cart here; it's all done and just waiting
        define('geoCart_skipSave', 1); //make sure we don't re-save the cart after clearing it!
    }
}
