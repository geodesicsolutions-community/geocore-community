<?php

//payment_gateways/_template.php
/**
 * This is the "developer template" that documents most of what a payment
 * gateway can do in the system.
 *
 * @package System
 * @since Version 4.0.0
 */
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/

##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    7.2beta3-76-gc9a512f
##
##################################

/**
 * This requires the geoPaymentGateway class, so include it just to be on the
 * safe side.
 */
require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

/**
 * This is the "developer template" payment gateway handler, a developer could use
 * this file as a starting point for creating a new payment gateway in the system.
 *
 * @package System
 * @since Version 4.0.0
 */
class _templatePaymentGateway extends geoPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = '_template';

    /**
     * Required, Usually the same as the name, this can be used as a means
     * to warn the admin that they may be using 2 gateways that
     * are the same type.  Mostly used to distinguish CC payment gateways
     * (by using type of 'cc'), but can be used for other things as well.
     *
     * @var string
     */
    public $type = '_template';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = '_template';

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
            'title' => 'Template Gateway',//how it's displayed in admin
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
        $html = 'Settings for _template gateway!';

        return $html;
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
        //Do checks or additional setting save here.

        return true;
    }

    /**
     * Optional, used in various places, if return true then you signify that
     * this payment gateway has recurring billing capabilities.  If method not
     * implemented, the superclass will return false (not recurring) by default.
     *
     * @return bool
     */
    public function isRecurring()
    {
        //return false here to signify this payment gateway is not able to process
        //recurring billing.  Note that recurring billing is Enterprise only.
        return false;

        //most gateways should do it like so:
        return $this->get('recurring');
    }

    /**
     * Optional, used on payment selection page, this will be the recurring
     * billing user agreement label and text, it should return an array.
     * Only used if isRecurring returns true and it is recurring payment.  If
     * implemented by payment gateway, the superclass will return false which
     * indicates no user agreement.
     *
     * @return array|bool Either bool false if no agreement shown, or an array
     *   like: array ('label' => 'label text', 'text' => 'text in agreement box.')
     */
    public function getRecurringAgreement()
    {
        //Can return false, or an array like below.  Note that the text value can
        //be left blank or ommitted and there will only be a checkbox with the
        //label, with no agreement text.  Or if label text is blank, no agreement
        //will be used.
        return array ('label' => 'Check if you agree.', 'text' => 'Agreement text.');
    }

    /**
     * Optional, used to get info on a particular recurring billing specific
     * to this gateway in the admin panel when viewing a recurring billing's
     * details.
     *
     * @param geoRecurringBilling $recurring
     * @return array An array of things to display as specified in-line comments
     * @since Version 4.1.0
     */
    public static function adminRecurringDisplay($recurring)
    {
        //if there is any important info specific to this gateway type that
        //should be displayed, do it here.

        //a lot of gateways don't have anything specific to display. If that is
        //the case, either return false, or remove this method all together.
        return false;

        //To display something matching the general format of the rest of the
        //fields:
        $return [] = array (
            'label' => 'Info Label',
            'value' => 'Info Value',
        );

        //Or if you want to display the entire box for some reason:
        $return [] = array (
            'entire_box' => '<div>Entire box</div>',
        );
        //Can add as many different settings as needed, just keep adding to the array
        //and the 2 formats above can be mixed in the return.
        return $return;
    }

    /**
     * Optional, used to get an updated status for the recurring billing to see
     * if it is current and paid, and if so update the recurring data's info.
     *
     * Called to query the gateway to see the status of the recurring billing,
     * and update the recurring billing's paidUntil status, update main status
     * (for gateways that choose to use that), add a recurring billing transaction
     * if applicable, etc.
     *
     * @param geoRecurringBilling $recurring
     */
    public function recurringUpdateStatus($recurring)
    {
        //Up to each gateway to implement
    }

    /**
     * Optional, called to cancel the recurring billing, to stop payments.
     * Gateway should do whatever is needed to cancel the payment status, and
     * update the details on the recurring billing.
     *
     * @param geoRecurringBilling $recurring
     * @param string $reason The reason for the recurring billing cancelation.
     * @return bool Return true to say to cancel recurring payment, false to block
     *  canceling the recurring payment.
     */
    public function recurringCancel($recurring, $reason = '')
    {
        //Up to each gateway to implement
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

        /**
         * An array of "cost details" is passed in, this is what each order item returns in 'getCostDetails'
         * if that item is affecting the cart total (is not zero).  This allows order items to NOT
         * display themselves if they see something in the cart that the payment gateway should not pay
         * for, due to user agreement or other reason.  Example of this is 2CO gateway is not able to pay
         * for when user is adding to account balance, effectively "pre-paying" which is not allowed in 2CO policies.
         */
        $itemCostDetails = $vars['itemCostDetails'];

        //if there are any types of things that this gateway cannot pay for, loop through the $itemCostDetails array
        //to see if it is in there, and if so simply return false to avoid showing this gateway as a payment choice.

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => 'Template Gateway',//$msgs[######]
            'title_extra' => 'Text below Template Gateway Title',//usually make this empty string.
            'label_name' => self::gateway_name,
            'radio_value' => self::gateway_name,//should be same as gateway name
            'help_link' => $cart->site->display_help_link(3240),
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
            if (!$gateway->get('allow_negative') && $cart->getCartTotal() > $cart->user_data['account_balance']) {
                //example of generating an error, taken from the account balance payment gateway.
                $cart->addError();
                $cart->error_variables["account_balance"] = $cart->site->messages[2543];
            }
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

    /**
     * Optional.
     * Used: in auction_final_feesOrderItem::cron_close_listings
     *
     * Not part of main cart system.
     *
     * This is a special case, for giving the ability for a gateway to pay for
     * auction final fees.
     *
     * @param array $vars see docs in this function
     *
     */
    public static function auction_final_feesOrderItem_cron_close_listings($vars)
    {
        //vars is an associative array, with the listing being closed and the order
        //containing auction final fees.
        $listing = $vars['listing'];
        $order = $vars['order'];

        //do stuff here.
        //NOTE: If you are auto-paying the order here, BE SURE TO:
        //$order->set('payment_type',self::gateway_name);
    }

    /**
     * Optional.
     * Used: in auction_final_feesOrderItem::geoCart_cartProcess
     *
     * Not part of main cart system.
     *
     * This is a special case, for giving the ability for a gateway to pay for
     * auction final fees.  This function would return true in order to display
     * billing info page even when cart total is $0, in order to collect payment
     * details.
     *
     * @return bool True to always display billing info, false otherwise.
     */
    public static function auction_final_feesOrderItem_canAutoCharge()
    {
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
        if (!$gateway->get('charge_final_fees')) {
            //charge final fees turned off, don't auto charge final fees
            return;
        }

        //make it through all the checks, so can use this type
        return true;
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
     * https://example.com/transaction_process.php?gateway=_template
     *
     * As the "signal/notification URL" to send notifications to (obviously would need
     * to adjust for the actual payment gateway and actual site's URL).  Don't
     * forget to authenticate the signal in some way, to validate it is indeed
     * coming from the payment processor!
     */
    public function transaction_process()
    {
    }

    /**
     * Optional.
     * Used:  In recurring_process.php to allow processing of "signals" back
     * from a payment processor regarding recurring payments.
     *
     * Typical usage is on payment gateways that use recurring billing, and send
     * a signal back to the site to notify when payments are made, or when recurring
     * billing is canceled by the user, etc.
     *
     * To use, you would specify the following url for the recurring billing notifications:
     *
     * https://example.com/recurring_process.php?gateway=_template
     *
     * (Adjust site URL and gateway value) It would be up to this method to determine what
     * "transaction/recurring billing" this signal is for, to do
     * any "security checks" to make sure the signal is authentic, and to determine
     * what actions should be made for the recurring billing.  See other payment gateway
     * usage (like in paypal.php) for examples on how to do this.
     */
    public function recurring_process()
    {
    }
}
