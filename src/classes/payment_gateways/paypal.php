<?php

//paypal.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    16.09.0-69-g3e7263f
##
##################################

# Paypal payment gateway handler

class paypalPaymentGateway extends geoPaymentGateway
{
    public $name = 'paypal';
    const gateway_name = 'paypal';
    public $type = 'paypal';

    //------Paypal specific vars-------

    //Main paypal server:
    protected $_paypal_host = 'www.paypal.com';

    //Sandbox paypal server, used if settings are set to "test mode"
    protected $_paypal_host_testing = 'www.sandbox.paypal.com';

    //The URL to the cancel subscription instructions on Paypal
    protected $_paypal_cancel_instructions = 'https://www.paypal.com/au/selfhelp/article/how-do-i-view,-modify,-or-cancel-my-pre-approved-payments-faq1916';

    /**
     * currencies accepted by paypal, obtained from:
     * https://www.paypal.com/us/cgi-bin/webscr?cmd=p/sell/mc/mc_wa-outside
     * @param array
     */
    protected static $_currencies = array(
        'AUD' => 'Australian Dollar',
        'CAD' => 'Canadian Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'JPY' => 'Japanese Yen',
        'USD' => 'U.S. Dollar',
        'NZD' => 'New Zealand Dollar',
        'CHF' => 'Swiss Franc',
        'HKD' => 'Hong Kong Dollar',
        'SGD' => 'Singapore Dollar',
        'SEK' => 'Swedish Krona',
        'DKK' => 'Danish Krone',
        'PLN' => 'Polish Zloty',
        'NOK' => 'Norwegian Krone',
        'HUF' => 'Hungarian Forint',
        'CZK' => 'Czech Koruna',
        'ILS' => 'Israeli New Shekel',
        'MXN' => 'Mexican Peso',
        'BRL' => 'Brazilian Real',
        'MYR' => 'Malasyian Ringgit',
        'PHP' => 'Philippine Peso',
        'TWD' => 'New Taiwan Dollar',
        'THB' => 'Thai Baht',
        'TRY' => 'Turkish Lira',
        'RUB' => 'Russian Ruble'
    );

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
            'title' => 'Paypal',
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
        //the tooltips to use
        $tooltips = array();
        $tooltips['paypal_id'] = geoHTML::showTooltip('PayPal E-mail Account', 'This is the email address id of your PayPal business or Premier PayPal account.');
        $tooltips['paypal_image_url'] = geoHTML::showTooltip('URL of Company Logo', 'This image will appear on the paypal payment form as your company logo. The url you enter must be an absolute https url, not a relative url, e.g. https://www.yoursite.com/image/someimage.jpg. Make sure the url is https so your users will not receive the non-secure page error while at paypal.com.');
        $tooltips['paypal_item_label'] = geoHTML::showTooltip('Title of Item Sent to PayPal', 'This is the title that will be sent to Paypal, and displayed on Paypal when the user is paying for anything using Paypal.');
        $tooltips['currency_type'] = geoHTML::showTooltip('Currency Type You Accept at PayPal', 'This is the currency you accept at PayPal. PayPal only accepts 5 different currencies at this time. You can specify which one of those you accept here if that happens to be different from the currency specified on your site.');
        $tooltips['currency_rate'] = geoHTML::showTooltip('Currency Multiplier', 'This is the multiplier your total will by multiplied by to get the PayPal Currency Total for the current transaction. For example, if your site accepts Mexican Pesos, but you can only accept US Dollars through your PayPal account, you must enter a rate multiplier to find the cost of the listing in US dollars. Round the exchange multiplier to 4 decimal places (e.g. 11.1111).');
        $tooltips['language_code'] = geoHTML::showTooltip('Language Code', 'This is a code transmitted to PayPal that tells them what language to display on their site. The default value is: <strong>US</strong>');
        $tooltips['no_shipping'] = geoHTML::showTooltip('"No Shipping" variable', 'This value is sent to paypal with each transaction, as the variable "no_shipping." <strong>In most cases, you do not need to set this.</strong> Consult Paypal\'s documentation for further information on the workings of this setting.');
        $tooltips['godaddy'] = geoHTML::showTooltip('Hosting Company', 'This is the hosting company the Geo software is installed on.  If your hosting company is Godaddy, or a subsidiary of Godaddy, you will need to select Godaddy, otherwise select "All Others".
		<br /><br />This setting will determine the method that the software uses to communicate with Paypal, because communications with Paypal are done differently on Godaddy servers.<br /><br />
		If you are not sure, select "All Others".');

        $tpl = new geoTemplate('admin');
        $tpl->assign('payment_type', self::gateway_name);
        //make it use common options WITH recurring setting
        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(true, true));

        $tpl->assign('tooltip', $tooltips);

        $tpl->assign('godaddy', $this->get('godaddy'));
        $tpl->assign('currency_type', $this->get('currency_type', 'USD'));
        $tpl->assign('currencies', self::$_currencies);
        $tpl->assign('paypal_id', $this->get('paypal_id'));
        $tpl->assign('use_micro', $this->get('use_micro'));
        //value is displayPrice filtered in template
        $tpl->assign('micro_limit', $this->get('micro_limit', '12.00'));
        $db = DataAccess::getInstance();
        $tpl->assign('precurrency', $db->get_site_setting('precurrency'));
        $tpl->assign('postcurrency', $db->get_site_setting('postcurrency'));
        $tpl->assign('micro_id', $this->get('micro_id'));
        $tpl->assign('paypal_image_url', $this->get('paypal_image_url'));
        $tpl->assign('paypal_item_label', $this->get('paypal_item_label'));
        $tpl->assign('currency_rate', $this->get("currency_rate", '0.0000'));
        $tpl->assign('language_code', $this->get('language_code', 'US'));
        $tpl->assign('no_shipping', $this->get('no_shipping', ''));

        $html = $tpl->fetch('payment_gateways/paypal.tpl');

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
        if (isset($_POST['paypal']) && is_array($_POST['paypal']) && count($_POST['paypal']) > 0) {
            $admin = geoAdmin::getInstance();

            $settings = $_POST['paypal'];
            //save common settings
            $this->_updateCommonAdminOptions($settings, true);

            $this->set('godaddy', ((isset($settings['godaddy']) && $settings['godaddy']) ? 1 : false));
            $paypal_id = trim($settings['paypal_id']);

            if (!strlen($paypal_id)) {
                $admin->userError("Paypal ID field should not be left blank!  Enter your Paypal e-mail.");
                //go ahead and save it, to allow admin to "blank" the field if they want
                $this->set('paypal_id', $paypal_id);
            } elseif (!geoString::isEmail($paypal_id)) {
                //they entered something that is not a valid email address!
                $admin->userError("Paypal ID entered is not a valid e-mail address.");
            } else {
                //it is probably valid
                $this->set('paypal_id', $paypal_id);
            }
            $use_micro = ((isset($settings['use_micro']) && $settings['use_micro']) ? 1 : false);
            $this->set('use_micro', $use_micro);
            $this->set('micro_limit', geoNumber::deformat($settings['micro_limit']));

            $micro_id = trim($settings['micro_id']);

            if (!strlen($micro_id)) {
                if ($use_micro) {
                    //it is blank, and use_micro checked, show an error
                    $admin->userError("Micro-Payment E-Mail field should not be left blank if use micro-payment account is checked!");
                }
                //go ahead and save it, to allow admin to "blank" the field if they want
                $this->set('micro_id', $micro_id);
            } elseif (!geoString::isEmail($micro_id)) {
                //they entered something that is not a valid email address!  Always show
                //error, even if they don't have use_micro checked
                $admin->userError("Micro-Payment E-Mail entered is not a valid e-mail address.");
            } else {
                //it is probably valid
                $this->set('micro_id', $micro_id);
            }
            //should be a URL, may want to add some sort of check on it
            $this->set('paypal_image_url', trim($settings['paypal_image_url']));
            if (trim($settings['paypal_image_url']) && substr(trim($settings['paypal_image_url']), 0, 5) !== 'https') {
                //not using https, produce warning about it
                $admin->userError("Paypal image URL specified does not use secure location, users will get a warning in their browser using an image without HTTPS.");
            }
            $this->set('paypal_item_label', trim($settings['paypal_item_label']));
            if (isset(self::$_currencies[$settings['currency_type']])) {
                $this->set('currency_type', $settings['currency_type']);
            } else {
                //should only happen, in theory, if they are trying something goofy
                $admin->userError('Invalid currency type specified!');
            }

            $this->set('currency_rate', floatval($settings['currency_rate']));
            //hopefully admin knows what to enter for this one..
            $this->set('language_code', trim($settings['language_code']));
            //no shipping is either an int (1 2 or 3), or blank...
            $this->set('no_shipping', $settings['no_shipping']);
        }

        return true;
    }

    /**
     * Gets data to let cart know how to display this payment choice.
     */
    public static function geoCart_payment_choicesDisplay()
    {
        $cart = geoCart::getInstance();

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[500289],
            'title_extra' => '',
            'label_name' => self::gateway_name,
            'radio_value' => self::gateway_name,//should be same as gateway name
            'help_link' => $cart->site->display_help_link(212),
            'checked' => false,

            //Items below will be auto generated if left blank string.
            'radio_name' => '',
            'choices_box' => '',
            'help_box' => '',
            'radio_box' => '',
            'title_box' => '',
            'radio_tag' => '',

        );
        if (defined('IN_ADMIN')) {
            //image would not be relative
            $return['title'] = "PayPal";
        }

        return $return;
    }

    /**
     * This is where payment processing is done, where the user is re-directed
     * to paypal to make the payment.
     */
    public static function geoCart_payment_choicesProcess()
    {
        trigger_error('DEBUG TRANSACTION: Top of ' . self::gateway_name . ': Classified_sell_transaction_approved() - processing');

        $cart = geoCart::getInstance();

        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);

        //get invoice on the order
        $invoice = $cart->order->getInvoice();
        $invoice_total = $due = $invoice->getInvoiceTotal();

        if ($due >= 0) {
            //DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
            return ;
        }
        if ($cart->isRecurringCart() && $gateway->isRecurring()) {
            //This is a recurring billing, so process as such.

            //common stuff to most payment gateways:
            $recurringItem = $cart->item;
            $recurring = self::_initRecurring($cart->order, $gateway, $recurringItem);
            if (!$recurring) {
                //something went wrong initializing, such as invalid interval or
                //cost, do not proceed.

                return;
            }
            //Get the interval and price per interval.  Remember, interval will
            //always be in seconds (so it will work with most number of different gateways).
            $interval = $recurring->getCycleDuration();
            $recurringAmount = $recurring->getPricePerCycle();
        }


        do {
            //get custom identifier, and verify that it is unique by attempting to get another transaction
            //with that transaction string.
            $unique_transaction = $gateway->_createID();

            $transaction_check = geoTransaction::getTransaction($unique_transaction);
        } while (is_object($transaction_check) && $transaction_check->getId() > 0);
        unset($transaction_check);

        //create new transaction.

        $type = $gateway->get('paypal_item_label', 'listing fees');
        //make sure the text used is never empty string..
        $type = (strlen($type) > 0) ? $type : 'listing fees';

        $paypal_id = $gateway->get('paypal_id');
        //see if we should be using the alternate e-mail
        if ($gateway->get('use_micro') && ($due * -1) < $gateway->get('micro_limit', '12.00') && strlen($gateway->get('micro_id')) > 1) {
            trigger_error('DEBUG TRANSACTION: Using micro paypal e-mail: ' . $gateway->get('micro_id') . ' , amount due is: ' . ($due * -1) . ' - which is less than ' . $gateway->get('micro_limit', '12.00'));
            $paypal_id = $gateway->get('micro_id');
        } else {
            //just for debugging purposes:
            $debug_extra = ($gateway->get('use_micro')) ? 'micro-payments turned ON - amount greater than ' . $gateway->get('micro_limit', '12.00') : 'micro-payments turned OFF';
            trigger_error('DEBUG TRANSACTION: Using PRIMARY paypal e-mail: ' . $gateway->get('paypal_id') . ' , amount due is: ' . ($due * -1) . ' - ' . $debug_extra);
        }

        //Now, create a new transaction attached to the invoice, but set status to 0 meaning
        //is is not active yet.  We'll activate the transaction when we get the IPN signal later..
        $transaction = new geoTransaction();
        $transaction->setAmount(-1 * $due);//balance out the amount due on invoice
        $transaction->setGatewayTransaction($unique_transaction);

        //use it for recurring too
        if ($recurring) {
            $recurring->setSecondaryId($unique_transaction);
        }

        $transaction->setDate(geoUtil::time());
        $msgs = $cart->db->get_text(true, 183);
        $transaction->setDescription($msgs[500576]);
        $transaction->setGateway($gateway);
        $transaction->setInvoice($invoice);
        $transaction->setStatus(0);
        $transaction->setUser($cart->user_data['id']);

        $mult = ($gateway->get("currency_rate", 0) != 0) ? $gateway->get("currency_rate") : 1;
        if ($recurring) {
            //clean recurring amount as well
            $recurringAmount = sprintf("%01.2f", round(($mult * $recurringAmount), 2));
        }
        $mult = ($mult * -1);//due is going to be negative, need to convert to positive to process by paypal
        $amount_converted = sprintf("%01.2f", round(($mult * $due), 2));
        //Set this data in transaction for debug purposes later on
        $transaction->set('paypal_amount', $amount_converted); //amount sent to paypal
        $transaction->set('receiver_email', $paypal_id);
        $transaction->set('item_name', $type);

        if ($recurring) {
            $recurring->addTransaction($transaction);
        }

        $invoice->addTransaction($transaction);

        $transaction->save();//save changes

        if ($recurringItem && $recurringItem->getRecurringDescription()) {
            $ad_type = $recurringItem->getRecurringDescription();
        } elseif (strlen(trim($gateway->get("paypal_item_label"))) > 0) {
            $ad_type = $gateway->get("paypal_item_label");
        } else {
            $ad_type = $type;
        }
        trigger_error('DEBUG TRANSACTION: Inserting new transaction for paypal payment.');

        //get the transaction id
        $trans_id = $transaction->getGatewayTransaction();
        $host = ($gateway->get('testing_mode')) ? $gateway->_paypal_host_testing : $gateway->_paypal_host;

        $fileName = (($recurring) ? "recurring" : "transaction") . "_process.php?gateway=paypal";
        $notify_url = str_replace($cart->db->get_site_setting("classifieds_file_name"), $fileName, $cart->db->get_site_setting("classifieds_url"));
        $return_url = geoFilter::getBaseHref() . 'transaction_process.php?gateway=paypal&gtxn=' . $transaction->getId(); //"gtxn" used to reacquire transaction when user returns to site
        $cancel_url = $cart->db->get_site_setting('classifieds_url');

        $paypal_url = "https://$host/cgi-bin/webscr?";
        $paypal_url .= "receiver_email=" . urlencode($paypal_id);
        $paypal_url .= "&return=" . urlencode($return_url);
        $paypal_url .= "&notify_url=" . urlencode($notify_url);
        $paypal_url .= "&cancel_return=" . urlencode($cancel_url);
        $paypal_url .= "&rm=2"; //"return method" -- makes the return signal use POST for paypal vars
        $paypal_url .= "&business=" . urlencode($paypal_id);
        $paypal_url .= "&cmd=_ext-enter";
        $cmd = '_xclick' . (($recurring) ? '-subscriptions' : '');
        $paypal_url .= "&redirect_cmd=$cmd";
        $paypal_url .= "&item_name=" . urlencode($ad_type);
        $paypal_url .= "&cpp_header_image=" . urlencode($gateway->get("paypal_image_url"));
        if (!$recurring) {
            $paypal_url .= "&item_number=1";
            $paypal_url .= "&quantity=1";
            $paypal_url .= "&shipping=0";
            $paypal_url .= "&num_cart_items=1";
        }
        $paypal_url .= "&currency_code=" . trim($gateway->get('currency_type', 'USD'));
        if ($recurring) {
            $paypal_url .= "&a3=$recurringAmount";
            $days = $p3 = floor($interval / (60 * 60 * 24));
            $t3 = 'D';
            if ($days > 90) {
                //have to translate to number of weeks, months, or years


                if ($days >= 365 && $days % 365 == 0) {
                    //use years, it translates cleanly
                    $t3 = 'Y';
                } elseif ($days % 7 == 0 && $days / 7 <= 52) {
                    //translates cleanly into weeks, use weeks
                    $t3 = 'W';
                } elseif ($days % 30 == 0 && $days / 30 <= 24) {
                    //translates kind of cleanly into months
                    $t3 = 'M';
                }

                if ($t3 == 'D') {
                    //none of them translate cleanly, so try to get as close as possible
                    if (floor($days / 7) <= 52) {
                        //week is best option, it would get closest to actual number days
                        $t3 = 'W';
                    } elseif (floor($days / 30) <= 24) {
                        $t3 = 'M';
                    } else {
                        //have to use year, this shouldn't be common but it is something more than 2 years,
                        //and only way to represent that is in years
                        $t3 = 'Y';
                    }
                }

                if ($t3 == 'W') {
                    $p3 = floor($days / 7);
                } elseif ($t3 == 'M') {
                    $p3 = floor($days / 30);
                } elseif ($t3 == 'Y') {
                    $p3 = floor($days / 365);
                }
            }
            $paypal_url .= "&p3=$p3";
            $paypal_url .= "&t3=$t3";
            $paypal_url .= "&src=1";
            $paypal_url .= "&no_note=1";
        } else {
            $paypal_url .= "&amount=" . $amount_converted;
        }
        $paypal_url .= "&invoice=" . $trans_id;

        $formdata = $cart->user_data['billing_info'];
        $paypal_url .= "&first_name=" . urlencode($formdata['firstname']);
        $paypal_url .= "&last_name=" . urlencode($formdata['lastname']);
        $paypal_url .= "&address1=" . urlencode($formdata['address']);
        $paypal_url .= "&address2=" . urlencode($formdata['address_2']);
        $paypal_url .= "&city=" . urlencode($formdata['city']);
        if ($formdata['state'] !== "none") {
            $paypal_url .= "&state=" . urlencode($formdata['state']);
        }
        $paypal_url .= "&zip=" . urlencode($formdata['zip']);
        $paypal_url .= "&email=" . urlencode($formdata['email']);
        $paypal_url .= "&payer_id=" . urlencode($cart->user_data['id']);
        $paypal_url .= "&custom=" . urlencode($unique_transaction);
        if ($gateway->get('language_code')) {
            $paypal_url .= "&lc=" . urlencode($gateway->get('language_code'));
        }
        if (is_numeric($gateway->get('no_shipping'))) {
            $paypal_url .= "&no_shipping=" . $gateway->get('no_shipping');
        }

        $transaction->set('paypal_url', $paypal_url);
        //re-save transaction to save paypal url.
        $transaction->save();
        trigger_error('DEBUG TRANSACTION: Saving transaction, it is: ' . print_r($transaction, 1));

        if ($recurring) {
            $recurring->save();
        }
        //set order status to Pending to prep for sending data to gateway
        $cart->order->setStatus('pending');
        //Close the cart session so user can't come back and change something after payment
        $cart->removeSession();

        trigger_error('DEBUG TRANSACTION: Paypal: about to send user to URL: ' . $paypal_url);
        //Send user to Paypal and let app_bottom finalize everything
        header("Location: " . $paypal_url);
        require GEO_BASE_DIR . 'app_bottom.php';
        exit;
    }

    /**
     * Process recurring billing signals sent by the payment processor.
     */
    public function recurring_process()
    {
        trigger_error('DEBUG RECURRING: Top of paypal: recurring_process() post vars: ' . print_r($_POST, 1));

        //Validate the IPN signal is authentic...
        if (!$this->_validateIPN()) {
            //invalid IPN signal, do not do anything else!
            $this->_stopScript();
            return;//acedemic only
        }

        //example POST sent back on recurring billing:
        /*
         POST info: Array
        (
            [transaction_subject] =>
            [payment_date] => 15:03:47 Jul 06, 2009 PDT
            [txn_type] => subscr_payment
            [subscr_id] => S-5FM58218J48423421
            [last_name] => User
            [residence_country] => US
            [item_name] => listing fees
            [payment_gross] => 52.00 (USD ONLY - DO NOT USE)
            [mc_currency] => USD
            [business] => example@geodesicsolutions.com
            [payment_type] => instant
            [protection_eligibility] => Ineligible
            [verify_sign] => Ai-zgZygiC-8K9O0g07Aw4tLForeArklMsapf7LFdUI7WJzsHz.f06FV
            [payer_status] => verified
            [test_ipn] => 1
            [payer_email] => geojon_1246055845_per@geodesicsolutions.com
            [txn_id] => 19375326M9823442S
            [receiver_email] => geojon_1198109697_per@geodesicsolutions.com
            [first_name] => Test
            [invoice] => 4f37b376-8e6f-2d1f-74a1-d0fdc55f8bbc
            [payer_id] => SLTHK7NEH5Q4N
            [receiver_id] => ZE7588TM26PBA
            [payment_status] => Completed
            [payment_fee] => 1.81
            [mc_fee] => 1.81
            [mc_gross] => 52.00
            [custom] => 4f37b376-8e6f-2d1f-74a1-d0fdc55f8bbc
            [charset] => windows-1252
            [notify_version] => 2.8
        )
        */
        $txn_id = $_POST['txn_id'];
        $txn_type = $_POST['txn_type'];

        $tId = $_POST['invoice'];
        $subId = $_POST['subscr_id'];//this is how paypal identifies subscriptions, I think
        $custom = $_POST['custom'];
        $mc_gross = $_POST['mc_gross'];
        $payment_status = $_POST['payment_status'];

        //get the original transaction to activate it
        $origTransaction = geoTransaction::getTransaction($tId);
        if ($origTransaction && !$origTransaction->getId()) {
            //not really transaction
            $origTransaction = null;
        }
        $recurring = geoRecurringBilling::getRecurringBilling($custom);

        if (!$recurring || $recurring->getId() == 0) {
            //nothing we can do without the recurring item
            trigger_error('ERROR RECURRING: Could not retrieve recurring item with ID ' . $custom);
            return;
        }

        trigger_error('DEBUG RECURRING: Processing IPN signal, txn_type: ' . $txn_type);
        //figure out what to do depending on txn_type
        switch ($txn_type) {
            case 'subscr_payment':
                trigger_error('DEBUG RECURRING: Processing recurring payment.');
                //Process recurring payment: if it's a new transaction

                if ($payment_status != 'Completed') {
                    trigger_error('DEBUG RECURRING: Payment status not completed, it is ' . $payment_status);
                    return;
                }
                $processTransaction = false;
                if ($origTransaction && !$origTransaction->get('firstProcessed')) {
                    //orig transaction never done
                    $transaction = $origTransaction;
                    //we do need to process this transaction afterwards
                    $processTransaction = true;
                    //remember that it has been processed.
                    $origTransaction->set('firstProcessed', 1);
                } else {
                    //create new transaction
                    $transaction = new geoTransaction();
                    $transaction->setAmount($mc_gross);
                    $transaction->setDate(geoUtil::time());
                    $db = DataAccess::getInstance();
                    $msgs = $db->get_text(true, 183);

                    $transaction->setDescription($msgs[500749]);
                    $transaction->setGateway($recurring->getGateway());

                    $transaction->setUser($recurring->getUserId());
                    //Save with status of 0 in case there are errors, don't end up
                    //with "active" transactions that are only partially initialized.
                    //Once the "partial" is saved, then change status back to 1 so that
                    //when fully fleshed out transaction is saved, it gets status set to 1
                    $transaction->setStatus(0);
                    $transaction->save();
                    //now update status to active, see note above
                    $transaction->setStatus(1);
                }

                $transaction->set('txn_id', $txn_id);
                $subscr_id = trim($_POST['subscr_id']);
                if ($subscr_id) {
                    //save the subscription ID from paypal
                    $recurring->set('subscr_id', $subscr_id);
                }


                if ($processTransaction) {
                    //run through success.. Do NOT use processPayment as that is for
                    //transactions that are not tied to orders.

                    //assume paid until is now plus duration
                    $paidUntil = geoUtil::time() + $recurring->getCycleDuration();
                    $recurring->setPaidUntil($paidUntil);
                    $recurring->setStatus(geoRecurringBilling::STATUS_ACTIVE);

                    $order = $recurring->getOrder();
                    self::_success($order, $transaction, $this, true);
                } else {
                    //payment transaction, process the payment
                    $recurring->processPayment($transaction);
                }

                $recurring->save();
                trigger_error('DEBUG RECURRING: Finished processing IPN signal.');
                break;

            case 'subscr_signup':
                //nothing to do until first payment is confirmed.
                break;

            case 'subscr_cancel':
                //Cancel recurring billing
                $db = DataAccess::getInstance();
                $msgs = $db->get_text(true, 183);

                //create a transaction so it shows up for the recurring billing
                $transaction = new geoTransaction();
                $transaction->setDate(geoUtil::time());
                //TODO: Text?
                $transaction->setDescription('Cancel Signal Recieved from Paypal');
                $transaction->setGateway($this);
                $transaction->setRecurringBilling($recurring);
                $transaction->setStatus(1);
                $transaction->setUser($recurring->getUserId());
                $recurring->addTransaction($transaction);
                //recurring cancel should save the transaction for us.
                $recurring->cancel($msgs[500750], true);

                break;

            case 'subscr_failed':
                //Subscription payment failure: nothing to do, nothing has changed
                //with regard to how long it's paid until.

                break;

            case 'subscr_eot':
                //Subscription end of term (AKA it's canceled!)
                $db = DataAccess::getInstance();
                $msgs = $db->get_text(true, 183);

                $recurring->cancel($msgs[500750] . ' (EOT)', true);

                break;

            case 'subscr_modify':
                //Subscription modified, what to do?
                //perhaps send notification to admin?  Currently this is not
                //accounted for, if it becomes a problem we may need to do something

                break;

            default:
                //Unknown type
        }
        return true;
    }

    /**
     * Used internally to validate an IPN signal, it assumes that the current
     * page load is the IPN signal.
     */
    private function _validateIPN()
    {
        //validate IPN and return true for valid or false otherwise.
        //treat as a robot, to avoid redirection or cookie issues.
        define('IS_ROBOT', true);

        trigger_error('DEBUG TRANSACTION: Top of paypal: _validateIpn()');
        //**supplied by paypal
        // STEP 1: Read POST data

        // reading posted data from directly from $_POST causes serialization
        // issues with array data in POST
        // reading raw POST data from input stream instead.
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        $get_magic_quotes = (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1);

        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - req : ' . $req);

        $result = '';
        $host = ($this->get('testing_mode')) ? $this->_paypal_host_testing : $this->_paypal_host;
        if ($this->get('godaddy')) {
            //This is a work around for a specific host that does not
            //allow the POST back to paypal.com (godaddy.com)
            $domain = "https://$host/cgi-bin/webscr";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $domain);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            //curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY, "http://proxy.shr.secureserver.net:3128");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $domain);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);

            $result = curl_exec($ch);
            trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - CURL Errors: ' . curl_errno($ch) . ' ' . curl_error($ch) . ' result: ' . $result);
            curl_close($ch);
        } else {
            // post back to PayPal system to validate

            $result = geoPC::urlPostContents('https://' . $host . '/cgi-bin/webscr', $req);
        }
        $resultRaw = $result;
        //just to make sure there aren't any extra white spaces...
        $result = trim($result);
        $payment_status = (isset($_POST['payment_status'])) ? $_POST['payment_status'] : false;

        if (strcmp($result, "VERIFIED") == 0) {
            if ($payment_status && $payment_status != 'Completed') {
                //payment status not completed, return false.
                trigger_error("DEBUG TRANSACTION: payment status not Completed, it is: $payment_status so IPN VERIFY failed.");
                return false;
            }
            $txn_id = (isset($_POST['txn_id'])) ? trim($_POST['txn_id']) : false;
            if ($txn_id) {
                //if there is a TXN ID, make sure we have not already procesed this.
                $db = DataAccess::getInstance();

                $sql = "SELECT * FROM " . geoTables::transaction_registry . " WHERE `index_key` = 'txn_id' AND `val_string` = ?";
                $txn_rows = $db->GetAll($sql, array('' . $txn_id));
                trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - right after execute');

                if ($txn_rows === false) {
                    //DB query error
                    trigger_error('ERROR TRANSACTION SQL: Error running SQL query: ' . $sql . ' - Error msg: ' . $db->ErrorMsg());
                    return false;
                }

                if (count($txn_rows) > 0) {
                    //this transaction has already been processed.
                    trigger_error('DEBUG TRANSACTION: paypal:_validateIPN() - This transaction has already been processed, txn_id matches.  txn_id: ' . $txn_id . ', matches: ' . print_r($txn_rows, 1) . "\n sql: $sql");
                    return false;
                }
            }
            trigger_error('DEBUG TRANSACTION: IPN Check Successful!');
            return true;
        } elseif (strcmp($result, "INVALID") == 0) {
            //Invalid response from paypal...
            trigger_error("DEBUG TRANSACTION: paypal: invalid response.");
            return false;
        } elseif (!strlen($result)) {
            trigger_error('DEBUG TRANSACTION: paypal IPN check failed, no response from
  					server.  Most common cause is a problem with the CURL configuration,
  					specifically with the CA certificate used for CURL.');
            return false;
        } else {
            trigger_error('DEBUG TRANSACTION: result not known, result: ' . $result . "\n\nResult raw: \n$resultRaw");
            return false;
        }
    }

    /**
     * Called from file /transaction_process.php - this function should
     * be used when expecting some sort of processing to take place where
     * the external gateway needs to contact the software back (like Paypal IPN)
     *
     * It is up to the function to verify everything.
     *
     */
    public function transaction_process()
    {
        trigger_error('DEBUG TRANSACTION: paypal:transaction_process() $_POST = ' . print_r($_POST, 1));

        if ($_GET['gtxn']) {
            //gtxn (geoTransaction) variable present means that this is a user returning to the site after payment; NOT an IPN signal
            //first, re-acquire the requested Transaction, and ensure it belongs to the current user
            $txn = (int)$_GET['gtxn'];
            $transaction = geoTransaction::getTransaction($txn);
            $session = geoSession::getInstance();
            $session->initSession();
            $invoice = $transaction->getInvoice();
            if (!$invoice || $invoice->getOrder()->getBuyer() != $session->getUserId()) {
                trigger_error('DEBUG TRANSACTION: asked for transaction details that don\'t exist or don\t belong to current user');
                $this->_stopScript();
                return;
            }
            //ok, now we're good to show the results of the transaction.
            //remember not to do any actual setting things active here...just show results that have already happened!
            trigger_error('DEBUG TRANSACTION: showing results of previous transaction: ' . $invoice->getOrder()->getStatus());
            self::_successFailurePage(true, $invoice->getOrder()->getStatus(), true, $invoice, $transaction);
            $this->_stopScript();
            return;
        }

        //treat as a robot, to avoid redirection or cookie issues.
        if (!$this->_validateIPN()) {
            //invalid IPN signal!
            $this->_stopScript();
            return;
        }

        trigger_error('DEBUG TRANSACTION: Top of paypal: transaction_process()');
        //receiver_email
        //item_name
        //item_number
        //quantity
        //invoice = id
        //custom
        //option_name1
        //option_selection1
        //option_name2
        //option_selection2
        //num_cart_items
        //**payment_status - "Pending","Completed","Failed","Denied"
        //**pending_reason - "echeck","intl","verify", "address","upgrade","unilateral","other"
        //**payment_date - "18:30:30 Jan 1,2000 PST"
        //payment_gross (USD ONLY - DO NOT USE)
        //**payment_fee
        //**txn_id
        //**txn_type - "web_accept","cart","send_money"
        //first_name
        //last_name
        //address_street
        //address_city
        //address_zip
        //address_country
        //address_status - "confirmed","unconfirmed"
        //payer_email
        //payer_id
        //payer_status - "verified","unverified","intl_verified"
        //**payment_type - "echeck","instant"
        //**notify_version - "1.3"
        //**verify_sign
        //**mc_gross ---------- currently NOT unused
        //mc_fee ---------- currently unused
        //mc_currency ------- currently unused
        //**supplied by paypal

        $item_name = $_POST['item_name'];
        $receiver_email = $_POST['receiver_email'];
        $item_number = $_POST['item_number'];
        $invoice = $_POST['invoice'];
        $payment_status = $_POST['payment_status'];
        $mc_gross = $_POST['mc_gross'];
        $txn_id = $_POST['txn_id'];
        $payer_email = $_POST['payer_email'];
        $payer_id = $_POST['payer_id'];
        $pending_reason = $_POST['pending_reason'];
        $payment_date = $_POST['payment_date'];
        $payment_fee = $_POST['payment_fee'];
        $payer_status = $_POST['payer_status'];
        $payment_type = $_POST['payment_type'];
        $notify_version = $_POST['notify_version'];
        $verify_sign = $_POST['verify_sign'];
        $custom = $_POST['custom'];

        //NOTE : Payment status = Completed is checked in _validateIPN()

        // check the payment_status is Completed
        // check that txn_id has not been previously processed
        // check that receiver_email is an email address in your PayPal account
        // process payment
        trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - $payment_status = ' . $payment_status . "\nstrlen = " . strlen($payment_status));

        trigger_error('ERROR TRANSACTION: paypal:transaction_process() - payment status is Completed');

        trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - right before creation');
        $transaction = geoTransaction::getTransaction($invoice);
        //dump the transaction info for debug:
        //trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - right AFTER - transaction: '.print_r($transaction,1));

        if (!is_object($transaction) || !$transaction->getId()) {
            trigger_error('ERROR TRANSACTION: Invalid transaction ID: ' . $invoice);
            $this->_stopScript();
            return; //acedemic only
        }

        if ($transaction->get('txn_id')) {
            //already completed this one!  Shouldn't get here though, since txn_id is checked
            //in IPN checks.
            trigger_error('ERROR TRANSACTION: TXN ID already set, this transaction has already completed!');
            $this->_stopScript();
            return; //acedemic only
        }
        //this transaction exists
        //complete it

        $transaction->set('ipn_received', 'IPN Signal was received and payment verified.');
        //un-comment the following if further info is needed to be saved for debugging.
        /*
        $transaction->set('payment_status',$payment_status);
        $transaction->set('pending_reason',$pending_reason);
        $transaction->set('payment_date',$payment_date);
        $transaction->set('payment_fee',$payment_fee);
        $transaction->set('txn_id',$txn_id);
        $transaction->set('txn_type',$txn_type);
        $transaction->set('payer_status',$payer_status);
        $transaction->set('payment_type',$payment_type);
        $transaction->set('notify_version',$notify_version);
        $transaction->set('verify_sign',$verify_sign);
        */
        //get the invoice
        $invoice = $transaction->getInvoice();

        //get the order
        $order = $invoice->getOrder();
        self::_success($order, $transaction, $this);
        //note: here, we "render" the success page even though no one will see it.
        //this is to help out a couple of clients that trigger external affiliate scripts off the success page, that isn't otherwise shown for PayPal Standard

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
        //There is no agreement, the agreement is made on Paypal.
        return false;
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
        //No way to check status on recurring payment using paypal website
        //payments standard.
    }

    /**
     * Optional, called to cancel the recurring billing, to stop payments.
     * Gateway should do whatever is needed to cancel the payment status, and
     * update the details on the recurring billing.
     *
     * @param geoRecurringBilling $recurring
     * @param string $reason The reason for the recurring billing cancelation.
     */
    public function recurringCancel($recurring, $reason = '')
    {
        $helpUrl = $this->_paypal_cancel_instructions;
        //No way to cancel recurring payments from the site.
        if (defined('IN_ADMIN')) {
            if (isset($_GET['override']) && $_GET['override']) {
                //admin has over-ridden it, meaning they are saying it is alrady canceled in the admin.
                return true;
            }
            $id = (int)$recurring->getId();
            //set an admin message
            $extra = "<br /><br />
			If this recurring billing is already <strong>canceled in Paypal.com</strong>:
			<a href='javascript:void(0);' onclick='recurring.cancelUrlExtra = \"&amp;override=1\"; recurring.statusCancel({$id}); return false;'>Force Cancelation</a>";

            $recurring->setUserMessage("When using Paypal Standard, you must cancel the
			subscription through your account on paypal.com website.  Instructions for that
			can be found at <a href='$helpUrl' onclick='window.open(this.href); return false;'>paypal.com helpcenter</a>.
			$extra");
        } else {
            //set normal user message
            $db = DataAccess::getInstance();
            $msgs = $db->get_text(true, 37);

            $recurring->setUserMessage("<h2>{$msgs[500751]}</h2>
			<p>{$msgs[500752]} <a href='$helpUrl'>{$msgs[500753]}</a>
			{$msgs[500754]}</p>");
        }
        return false;
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
        $subscr_id = $recurring->get('subscr_id');
        $subscr_id = ($subscr_id) ? $subscr_id : 'Unkown';
        $return = array ();
        $return [] = array ('label' => 'Paypal Subscription ID', 'value' => $subscr_id);
        return $return;
    }

    /**
     * Entry point for outside entities to get the currency list for paypal.  This
     * is used by the paypal seller/buyer gateway so there is only one place to
     * keep the list updated.
     */
    public static function getPaypalCurrencies()
    {
        return self::$_currencies;
    }


    /**
     * Internal method, used as easy way to stop the script from going any further.
     */
    protected function _stopScript()
    {
        trigger_error('DEBUG TRANSACTION: paypal:_stopScript()  ------  End of process paypal payment!');
        //do all normal end of app stuff
        require GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    /**
     * Internal method, create a random ID
     * @return string
     */
    protected function _createID()
    {
        $uuid = md5(uniqid(rand(), true));
        $guid =  /*'urn:uuid:'.*/substr($uuid, 0, 8) . "-"
            . substr($uuid, 8, 4) . "-"
            . substr($uuid, 12, 4) . "-"
            . substr($uuid, 16, 4) . "-"
            . substr($uuid, 20, 12);
        return $guid;
    }
}
