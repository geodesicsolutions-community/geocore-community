<?php

//payment_gateways/stripe.php
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
## ##    16.09.0-39-g2d19902
##
##################################

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

class stripePaymentGateway extends _ccPaymentGateway
{
    /**
     * Required, the name of this gateway, should be the same as the file name without the .php
     *
     * @var string
     */
    public $name = 'stripe';

    /**
     * For convenience, should be same as $name
     *
     */
    const gateway_name = 'stripe';


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
            'title' => 'CC - Stripe',//how it's displayed in admin
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

        $tpl = new geoTemplate('admin');
        $tpl->assign('payment_type', self::gateway_name);

        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(false));

        $values = array(
            'public_key' => $this->get('public_key'),
            'api_key' => $this->get('api_key'),
            'currency_type' => $this->get('currency_type', 'USD')
        );
        $tpl->assign('values', $values);

        //https://support.stripe.com/questions/which-currencies-does-stripe-support#currencygroup1
        $currencies = array(
            "AED" => "United Arab Emirates Dirham",
            "ALL" => "Albanian Lek",
            "ANG" => "Netherlands Antillean Gulden",
            "ARS" => "Argentine Peso",
            "AUD" => "Australian Dollar",
            "AWG" => "Aruban Florin",
            "BBD" => "Barbadian Dollar",
            "BDT" => "Bangladeshi Taka",
            "BIF" => "Burundian Franc",
            "BMD" => "Bermudian Dollar",
            "BND" => "Brunei Dollar",
            "BOB" => "Bolivian Boliviano",
            "BRL" => "Brazilian Real",
            "BSD" => "Bahamian Dollar",
            "BWP" => "Botswana Pula",
            "BZD" => "Belize Dollar",
            "CAD" => "Canadian Dollar",
            "CHF" => "Swiss Franc",
            "CLP" => "Chilean Peso",
            "CNY" => "Chinese Renminbi Yuan",
            "COP" => "Colombian Peso",
            "CRC" => "Costa Rican Colon",
            "CVE" => "Cape Verdean Escudo",
            "CZK" => "Czech Koruna",
            "DJF" => "Djiboutian Franc",
            "DKK" => "Danish Krone",
            "DOP" => "Dominican Peso",
            "DZD" => "Algerian Dinar",
            "EGP" => "Egyptian Pound",
            "ETB" => "Ethiopian Birr",
            "EUR" => "Euro",
            "FJD" => "Fijian Dollar",
            "FKP" => "Falkland Islands Pound",
            "GBP" => "British Pound",
            "GIP" => "Gibraltar Pound",
            "GMD" => "Gambian Dalasi",
            "GNF" => "Guinean Franc",
            "GTQ" => "Guatemalan Quetzal",
            "GYD" => "Guyanese Dollar",
            "HKD" => "Hong Kong Dollar",
            "HNL" => "Honduran Lempira",
            "HRK" => "Croatian Kuna",
            "HTG" => "Haitian Gourde",
            "HUF" => "Hungarian Forint",
            "IDR" => "Indonesian Rupiah",
            "ILS" => "Israeli New Sheqel",
            "INR" => "Indian Rupee",
            "ISK" => "Icelandic Krona",
            "JMD" => "Jamaican Dollar",
            "JPY" => "Japanese Yen",
            "KES" => "Kenyan Shilling",
            "KHR" => "Cambodian Riel",
            "KMF" => "Comorian Franc",
            "KRW" => "South Korean Won",
            "KYD" => "Cayman Islands Dollar",
            "KZT" => "Kazakhstani Tenge",
            "LAK" => "Lao Kip",
            "LBP" => "Lebanese Pound",
            "LKR" => "Sri Lankan Rupee",
            "LRD" => "Liberian Dollar",
            "MAD" => "Moroccan Dirham",
            "MDL" => "Moldovan Leu",
            "MNT" => "Mongolian Togrog",
            "MOP" => "Macanese Pataca",
            "MRO" => "Mauritanian Ouguiya",
            "MUR" => "Mauritian Rupee",
            "MVR" => "Maldivian Rufiyaa",
            "MWK" => "Malawian Kwacha",
            "MXN" => "Mexican Peso",
            "MYR" => "Malaysian Ringgit",
            "NAD" => "Namibian Dollar",
            "NGN" => "Nigerian Naira",
            "NIO" => "Nicaraguan Cordoba",
            "NOK" => "Norwegian Krone",
            "NPR" => "Nepalese Rupee",
            "NZD" => "New Zealand Dollar",
            "PAB" => "Panamanian Balboa",
            "PEN" => "Peruvian Nuevo Sol",
            "PGK" => "Papua New Guinean Kina",
            "PHP" => "Philippine Peso",
            "PKR" => "Pakistani Rupee",
            "PLN" => "Polish Zloty",
            "PYG" => "Paraguayan Guaran??",
            "QAR" => "Qatari Riyal",
            "RUB" => "Russian Ruble",
            "SAR" => "Saudi Riyal",
            "SBD" => "Solomon Islands Dollar",
            "SCR" => "Seychellois Rupee",
            "SEK" => "Swedish Krona",
            "SGD" => "Singapore Dollar",
            "SHP" => "Saint Helenian Pound",
            "SLL" => "Sierra Leonean Leone",
            "SOS" => "Somali Shilling",
            "STD" => "Sao Tome and Principe Dobra",
            "SVC" => "Salvadoran Colon",
            "SZL" => "Swazi Lilangeni",
            "THB" => "Thai Baht",
            "TOP" => "Tongan Pa'anga",
            "TTD" => "Trinidad and Tobago Dollar",
            "TWD" => "New Taiwan Dollar",
            "TZS" => "Tanzanian Shilling",
            "UAH" => "Ukrainian Hryvnia",
            "UGX" => "Ugandan Shilling",
            "USD" => "United States Dollar",
            "UYU" => "Uruguayan Peso",
            "UZS" => "Uzbekistani Som",
            "VND" => "Vietnamese Dong",
            "VUV" => "Vanuatu Vatu",
            "WST" => "Samoan Tala",
            "XAF" => "Central African Cfa Franc",
            "XOF" => "West African Cfa Franc",
            "XPF" => "Cfp Franc",
            "YER" => "Yemeni Rial",
            "ZAR" => "South African Rand",
            "AFN" => "Afghan Afghani",
            "AMD" => "Armenian Dram",
            "AOA" => "Angolan Kwanza",
            "AZN" => "Azerbaijani Manat",
            "BAM" => "Bosnia & Herzegovina Convertible Mark",
            "BGN" => "Bulgarian Lev",
            "CDF" => "Congolese Franc",
            "GEL" => "Georgian Lari",
            "KGS" => "Kyrgyzstani Som",
            "LSL" => "Lesotho Loti",
            "MGA" => "Malagasy Ariary",
            "MKD" => "Macedonian Denar",
            "MZN" => "Mozambican Metical",
            "RON" => "Romanian Leu",
            "RSD" => "Serbian Dinar",
            "RWF" => "Rwandan Franc",
            "SRD" => "Surinamese Dollar",
            "TJS" => "Tajikistani Somoni",
            "TRY" => "Turkish Lira",
            "XCD" => "East Caribbean Dollar",
            "ZMW" => "Zambian Kwacha",
        );
        $tpl->assign('currencies', $currencies);

        return $tpl->fetch('payment_gateways/stripe.tpl');
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
            $this->set('public_key', trim($settings['public_key']));
            $this->set('api_key', trim($settings['api_key']));
            $this->set('currency_type', trim($settings['currency_type']));

            //zero-decimal currencies
            //  https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
            $zdc = array('BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','VND','VUV','XAF','XOF','XPF');
            if (in_array($settings['currency_type'], $zdc)) {
                $this->set('currency_type_zero_decimal', 1);
            } else {
                $this->set('currency_type_zero_decimal', 0);
            }

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


    public static function geoCart_payment_choicesCheckVars($gateway = null, $skip_checks = null)
    {
        //just return true here; all of Stripe's error checking should catch everything either before or after this point
        return true;
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

        //add Stripe API files
        require_once(CLASSES_DIR . 'payment_gateways/includes/stripe/init.php');

        //create initial transaction
        try {
            $transaction = self::_createNewTransaction($cart->order, $gateway, $info);
            //Add the transaction to the invoice
            $transaction->setInvoice($invoice);
            $invoice->addTransaction($transaction);
            //save it so there is an id
            $transaction->save();
        } catch (Exception $e) {
            //catch any error thrown by _createNewTransaction
            trigger_error('ERROR TRANSACTION CART: Exception thrown when attempting to create new transaction.');
            return;
        }

        //act entirely on the "token" created by stripe.js
        $token = $_POST['stripeToken'];

        //use secret API key
        $apiKey = $gateway->get('api_key');
        \Stripe\Stripe::setApiKey($apiKey);

        //send amount in "cents" for most currencies, but not for certain zero-decimal currencies
        $amountToCharge = $gateway->get('currency_type_zero_decimal') ? $transaction->getAmount() : $transaction->getAmount() * 100;

        //process the charge
        try {
            $charge = \Stripe\Charge::create(array(
                "amount" => (int)$amountToCharge,
                "currency" => $gateway->get('currency_type', 'usd'),
                "source" => $token,
            ));
        } catch (\Stripe\Error\Card $e) {
            // The card has been declined
            trigger_error('DEBUG TRANSACTION: Stripe transaction failed with message: ' . $e->getMessage());
            return self::_failure($transaction, $e->getStripeCode(), $e->getMessage());
        }

        //if we got here, the charge is OK
        trigger_error('DEBUG TRANSACTION: Stripe Transaction approved');
        return self::_success($cart->order, $transaction, $gateway);
    }
}
