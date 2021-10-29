<?php

//payment_gateways/_cc.php
/**
 * This holds the abstract CC payment gateway class, meant to be extended
 * by a payment gateway wishing to collect CC info to have a lot of the common
 * tasks handled in this parent class.
 *
 * @package System
 * @since Version 4.0.0
 */

##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    17.05.0-2-g9d1bf0c
##
##################################

/**
 * Needs the {@link geoPaymentGateway} class.
 */
require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

/**
 * Have your payment gateway extend this class, to have a lot of the more mundane
 * tasks handled for you that are common to most payment gateways that need
 * to collect CC info.
 * @package System
 * @since Version 4.0.0
 */
abstract class _ccPaymentGateway extends geoPaymentGateway
{
    /**
     * All gateways that are CC gateways need to have a
     * type of cc.
     *
     * @var unknown_type
     */
    public $type = 'cc';

    protected static $gateway_name;

    /**
     * Error code signifying there was some error processing a CC transaction.  Should be used
     * for any error that do not fall into any other specific categories, or unknown errors.
     *
     */
    const FAIL_GENERAL_ERROR = 1;

    /**
     * Error code signifying that there was some error when attempting to connect to the gateway's
     * server to process a CC order.
     *
     */
    const FAIL_GATEWAY_CONNECTION = 2;

    /**
     * Error code signifying that something is wrong with the gateway's setting in the admin.  On the
     * front side, this is treated the same as a general error.
     *
     */
    const FAIL_CHECK_GATEWAY_SETTINGS = 3;

    /**
     * Error code signifying that the bank declined the transaction for some reason.
     *
     */
    const FAIL_BANK_DECLINED = 4;

    /**
     * Error code signifying that the CC info provided was invalid.  For security, there is no
     * distinction between whether it's an invalid CC number, CC date, CVV2 code, etc. to help
     * prevent fraud.
     *
     */
    const FAIL_INVALID_CC_INFO = 5;

    /**
     * Error code signifying that the fraudulant activity was detected.
     *
     */
    const FAIL_DETECTED_FRAUD = 6;

    /**
     * Error code signifying further action is needed to process the order, usually voice authorization.
     *
     */
    const PENDING_NEED_VOICE_AUTHORIZATION = 50;

    /**
     * The different card types.
     *
     */
    const CARD_TYPE_AMEX = 'amex';
    const CARD_TYPE_DISCOVER = 'discover';
    const CARD_TYPE_MC = 'mc';
    const CARD_TYPE_VISA = 'visa';
    const CARD_TYPE_UNKNOWN = 'unknown';

    /**
     * Used internally to store cleaned info from the user.
     *
     * @var array
     */
    private static $_info;

    /**
     * Each CC is required to iplement this, it is not optional like with
     * non-CC payment gateways.
     *
     */
    public static function geoCart_payment_choicesProcess()
    {
        throw new Exception('Error: geoCart_payment_choicesProcess() needs to be implemented for 
		every CC gateway.');
    }

    /**
     * Optional.
     * Used: in geoCart::process_orderDisplay()
     *
     * Displays results of transaction.  Usually this is only reached if transaction was a success, or
     * transaction requires further action (such as voice authorization)
     *
     */
    public static function geoCart_process_orderDisplay()
    {
        $cart = geoCart::getInstance();

        if ($cart->getErrorMsg('result_code') == self::PENDING_NEED_VOICE_AUTHORIZATION) {
            //special case!
            //TODO: Do not hard-code this
            $msg = 'Your order has been placed on hold, as this transaction will need to be processed manually, somebody may be contacting you shortly.
			If you have any questions, please contact the Site Admin.  Please do NOT place the order again (unless instructed to do so by the site admin)
			 or your credit card may be charged multiple times.';
            $result = false;
        } else {
            $msg = '';
            //assume it was a success
            $result = true;
        }
        self::_successFailurePage($result, $cart->order->getStatus(), true, $cart->order->getInvoice());

        $cart->removeSession();
    }

    /**
     * Make it so the title in the admin is pre-pended with "CC - "
     *
     * @return string
     */
    public function getTitle()
    {
        return 'CC - ' . parent::getTitle();
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
     * @param geoPaymentGateway $gateway
     * @return array Associative Array as specified above.
     */
    public static function geoCart_payment_choicesDisplay($gateway)
    {
        $cart = geoCart::getInstance(); //get cart to use the display_help_link function

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[500294],
            'title_extra' => '',//usually make this empty string.
            'label_name' => $gateway->getName(),
            'radio_value' => $gateway->getName(),//should be same as gateway name
            'help_link' => $cart->site->display_help_link(210),
            'checked' => false,//let system figure out if it is checked or not

            //Items below will be auto generated if left blank string.
            'radio_name' => '',//normally you leave all these blank.
            'choices_box' => '',
            'help_box' => '',
            'radio_box' => '',
            'title_box' => '',
            'radio_tag' => '',

        );
        $use_cvv2 = $gateway->get('use_cvv2');
        //if (($show_cc_choice->CC_ID == 1) || ($show_cc_choice->CC_ID == 3) || ($show_cc_choice->CC_ID == 4) || ($show_cc_choice->CC_ID >= 6 && $show_cc_choice->CC_ID != 10))
        //{
        //this card proccessor lets us gather card data here, so build form to do so

        $tpl = new geoTemplate('system', 'payment_gateways');
        $tpl->assign('use_cvv2', $use_cvv2);
        $tpl->assign('error_msgs', $cart->getErrorMsgs());

        if ($gateway->getName() === 'stripe') {
            //some special overrides for Stripe, because it does things a bit funny
            $tpl->assign('stripe_public_key', $gateway->get('public_key')); //testing value: pk_test_6pRNASCoBOKtIshFeQd4XMUh
            $return['title_extra'] = $tpl->fetch('stripe/payment_details.cc_form.tpl');
        } else {
            $return['title_extra'] = $tpl->fetch('shared/payment_details.cc_form.tpl');
        }

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
     * @param geoPaymentGateway $gateway
     * @param array $skip_checks Array of inputs to skip the check for, to allow a gateway to check
     *  the var in a way specific to that gateway.
     */
    public static function geoCart_payment_choicesCheckVars($gateway, $skip_checks = array())
    {
        $cart = geoCart::getInstance();

        if (!(isset($_POST['c']['payment_type']) && $_POST['c']['payment_type'] == $gateway->getName())) {
            //not our gateway...
            return;
        }
        //check vars

        self::$_info = $_POST['c'];

        //clean up the CC number
        $search = array(' ','-');
        self::$_info['cc_number'] = str_replace($search, '', self::$_info["cc_number"]);

        //set month and year
        self::$_info['exp_month'] = intval(trim(self::$_info['exp_date']['Date_Month']));
        self::$_info['exp_year'] = intval(trim(self::$_info['exp_date']['Date_Year']));
        if (!isset(self::$_info['firstname'])) {
            //Temporary, until we have it set up to allow user to input billing info seperate from their account info
            //TODO: Collect billing info and allow it to be different than user info
            $cart = geoCart::getInstance();
            self::$_info = array_merge(self::$_info, $cart->user_data);
        }

        if (!in_array('cc_number', $skip_checks)) {
            //do cc number checks
            if (strlen(self::$_info['cc_number']) == 0 || !self::validateCC(self::$_info['cc_number'])) {
                //CC num is blank, or is invalid (probably just entered the number wrong)
                $cart->addError();
                $cart->addErrorMsg('cc_number', $cart->site->messages[500630]);
            }
        }

        if (!in_array('exp_date', $skip_checks)) {
            //check exp date
            $Y = date('Y');
            if ($Y > self::$_info['exp_year'] || ($Y == self::$_info['exp_year'] && date('m') > self::$_info['exp_month'])) {
                //exp date is old
                $cart->addError();
                $cart->addErrorMsg('exp_date', $cart->site->messages[500631]);
            }
        }

        if (!in_array('cvv2_code', $skip_checks) && $gateway->get('use_cvv2')) {
            //check cvv2 code
            if (strlen(trim(self::$_info['cvv2_code'])) == 0) {
                $cart->addError();
                //TODO: Don't hard-code this text
                $cart->addErrorMsg('cvv2_code', $cart->site->messages[500632]);
            }
        }

        //TODO: Check billing info as well!
    }

    /**
     * Creates a new transaction and inserts the common data into it that is normally used for all
     * credit card types, like the encrypted CC number, the exp date, etc.
     *
     * @param geoOrder $order
     * @param array $vars
     * @throws Throws an exception if the total on the invoice is already >= 0
     * @return geoTransaction
     */
    protected static function _createNewTransaction($order, $gateway, $vars, $skipTotalCheck = false, $storeCC = false)
    {
        $cvv2_code = ($gateway->get('use_cvv2')) ? $vars["cvv2_code"] : "";
        $db = DataAccess::getInstance();
        $invoice = $order->getInvoice();
        $total = $invoice->getInvoiceTotal();
        if ($total >= 0 && !$skipTotalCheck) {
            //nothing to do, don't charge 0 bucks
            throw new Exception('Cannot create new transaction, the current invoice total is greater than or equal to 0.');
        }
        //data used to encrypt CC number
        $data = array();
        $data['amount'] = (-1 * $total);
        $data['date'] = geoUtil::time();
        $data['gateway'] = $gateway->getName();
        $data['buyer_id'] = $order->getBuyer();
        $data['seller_id'] = $order->getSeller();
        //just to throw any casual cracker off that only has DB access, have something named decryption_key
        //so they spend their time messing with it ;)
        $unique_key = substr(md5(uniqid(rand(), 1)), 0, strlen($vars["cc_number"]));
        $data['decryption_key'] = $unique_key;
        $crypt = new geoCrypt();
        $crypt->setKeyData($data); //array of data needs to be the same when decrypting later on
        if ($storeCC) {
            //encrypt CC number using the array of data
            $crypt->setPlainText($vars['cc_number']);
        } else {
            //store gibberish
            $crypt->setPlainText('AYB');
        }
        $encrypted_card_num = $crypt->getEncryptedText();
        $transaction = new geoTransaction();
        $transaction->setStatus(0); //turn off until it's verified
        $transaction->setAmount($data['amount']);
        $transaction->setDate($data['date']);
        $transaction->setGateway($gateway);
        $transaction->setUser($order->getBuyer());
        $msgs = $db->get_text(true, 183);
        $transaction->setDescription($msgs[500585]);
        $transaction->set('card_num', $encrypted_card_num);
        $transaction->set('decryption_key', $unique_key);
        $transaction->set('cypher', $crypt->getCypherUsed());
        if ($gateway->get('use_cvv2')) {
            $transaction->set('cvv2_code', $cvv2_code);
        }
        $transaction->set('exp_date', $vars['exp_month'] . '/' . $vars['exp_year']);
        $transaction->set('seller_id', $order->getSeller());
        //set the rest of the billing info in an array
        $billing_info = array (
            'firstname' => $vars['firstname'],
            'lastname' => $vars['lastname'],
            'address' => $vars['address'],
            'address_2' => $vars['address_2'],
            'city' => $vars['city'],
            'state' => $vars['state'],
            'country' => $vars['country'],
            'zip' => $vars['zip'],
            'phone' => $vars['phone'],
            'email' => $vars['email'],
            'exp_date' => $vars['exp_date'],
        );
        $transaction->set('billing_info', $billing_info);
        $transaction->setInvoice($invoice);
        trigger_error('DEBUG CART TRANSACTION: Bottom of cc _preProcess');
        return $transaction;
    }

    /**
     * Gets user inputed data, cleans up the CC number (removes dashes and spaces), and sets the exp_month and exp_year
     * so you don't have to figure out what they are really named (which is ['exp_date']['Date_Month'] and ['exp_date']['Date_Year']
     * by the way)
     *
     * @return array
     */
    protected static function _getInfo()
    {
        return self::$_info;
    }

    /**
     * Sets the info, typically this would be used in a custom geoCart_payment_choicesCheckVars() function.
     *
     * @param array $info
     */
    protected static function _setInfo($info)
    {
        if (is_array($info)) {
            self::$_info = $info;
        }
    }

    /**
     * Gets the un-encrypted CC number given a transaction created by _createNewTransaction()
     *
     * @param geoTransaction $transaction
     * @return string
     */
    protected static function _getCcNumber($transaction)
    {
        $encryptedNum = $transaction->get('card_num');
        if (!$encryptedNum) {
            //nothing to decrypt. admin has probably erased this card number manually.
            return false;
        }
        //data used to encrypt CC number
        $data = array();
        $data['amount'] = floatval($transaction->getAmount());
        $data['date'] = $transaction->getDate();
        $data['gateway'] = $transaction->getGateway()->getName();
        $data['buyer_id'] = intval($transaction->getUser());
        $data['seller_id'] = intval($transaction->get('seller_id'));
        $data['decryption_key'] = $transaction->get('decryption_key');

        $cypher = new geoCrypt();
        $cypher->setKeyData($data);
        $cypher->setCypherUsed($transaction->get('cypher'));
        $cypher->setEncryptedText($transaction->get('card_num'));

        return $cypher->getPlainText();
    }

    /**
     * Does common tasks when CC transaction failed.
     *
     * @param geoTransaction $transaction
     * @param enum( self::FAIL_GENERAL_ERROR,
     *              self::FAIL_GATEWAY_CONNECTION,
     *              self::FAIL_CHECK_GATEWAY_SETTINGS,
     *              self::FAIL_BANK_DECLINED,
     *              self::FAIL_INVALID_CC_INFO,
     *              self::PENDING_NEED_VOICE_AUTHORIZATION ) $result_code
     * @param string $result_message
     * @param boolean $notify_cart If true, will pass error message to cart
     * @param null $skipDisplay Not used here.
     */
    protected static function _failure($transaction, $result_code, $result_message = '', $notify_cart = true, $skipDisplay = null)
    {
        //NOTE:  There is more "common" code than this, but the other common code should stay in each
        //gateway so that debugging is easier.
        $transaction->setStatus(0); //should already be set to 0, but re-set just to be sure
        $transaction->set('result', $result_code);
        $transaction->set('failed_reason', $result_message);
        $transaction->save();

        if ($notify_cart) {
            $cart = geoCart::getInstance();
            $cart->addError(); //will not go to next step now
            //$cart->addErrorMsg('result_code',$result_code);

            if ($result_code == self::FAIL_CHECK_GATEWAY_SETTINGS) {
                //some sort of problem with gateway settings
                //Text hard-coded, since this should only happen if settings are mis-configured.

                $cart->addErrorMsg('error_gateway_settings', 'Possible payment gateway mis-configuration, please contact site admin.');
            }
            //Add additional error msgs specific to different fail types, if they become needed.

            if ($result_message) {
                $cart->addErrorMsg('cc_result_message', $result_message);
            }
            if ($cart->site->messages[500656]) {
                $cart->addErrorMsg('cc_declined', $cart->site->messages[500656]);
            }
        }
    }

    public static function validateCC($cc_num)
    {
        //make sure theres no dashes or spaces in it
        $search = array(' ','-');
        $cc_num = trim(str_replace($search, '', $cc_num));

        //make sure the number is valid.

        $len = strlen($cc_num);
        $j = 0;
        $part1 = '';
        for ($i = ($len - 2); $i >= 0; $i -= 2) {
            $part1 .= $cc_num[$i] * 2;
            $j++;
        }

        $validate = 0;
        for ($i = 0; $i < strlen($part1); $i++) {
            $validate += $part1[$i];
        }
        for ($i = ($len - 1); $i >= 0; $i -= 2) {
            $validate += $cc_num[$i];
        }

        if (substr($validate, -1) == '0') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Figures out the type of card given the cc number.
     *
     * @param string $cc_num The card number, without spaces or dashes, just numbers
     * @return enum(self::CARD_TYPE_AMEX,
     *              self::CARD_TYPE_DISCOVER,
     *              self::CARD_TYPE_MC,
     *              self::CARD_TYPE_VISA,
     *              self::CARD_TYPE_UNKNOWN)
     */
    public static function getCardType($cc_num)
    {
        $len = strlen($cc_num);
        if ($len == 15 && substr($cc_num, 0, 1) == '3') {
            return self::CARD_TYPE_AMEX;
        }
        if ($len == 16) {
            if (substr($cc_num, 0, 4) == '6011') {
                return self::CARD_TYPE_DISCOVER;
            }
            if (substr($cc_num, 0, 1) == '5' || (substr($cc_num, 0, 6) >= 222100 && substr($cc_num, 0, 6) <= 272099)) {
                //updated 05/15/17 to add "Series 2" MC cards
                return self::CARD_TYPE_MC;
            }
            if (substr($cc_num, 0, 1) == '4') {
                return self::CARD_TYPE_VISA;
            }
        }
        //does not match any card we know about
        return self::CARD_TYPE_UNKNOWN;
    }
}
