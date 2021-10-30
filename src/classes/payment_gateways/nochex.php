<?php

//nochex.php


# Nochex payment gateway handler

class nochexPaymentGateway extends geoPaymentGateway
{

    public $name = 'nochex';//make it so that name is known.
    const gateway_name = 'nochex';
    public $type = 'nochex';

    /**
     * Expects to return an array:
     * array (
     *  '' => ''
     * )
     *
     */
    function admin_display_payment_gateways()
    {
        $return = array (
            'name' => self::gateway_name,
            'title' => 'Nochex',
        );

        return $return;
    }

    /**
     * Called NON-STATIC (using $gateway->function_name() )
     *
     * If this function exists, it will be used to display custom
     * settings specific for this gateway.  If the function does not
     * exist, no settings button will be displayed beside the gateway.
     *
     * @return HTML to display below gateway when user clicked the settings button
     */
    function admin_custom_config()
    {
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';





        $tpl = new geoTemplate('admin');
        $tpl->assign('payment_type', self::gateway_name);

        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());

        $tooltips['logo_path'] = geoHTML::showTooltip('Logo Path', "Place the path to the logo you want NOCHEX to display when the customer is redirected to the NOCHEX website during their payment process.");
        $tooltips['email'] = geoHTML::showTooltip('Email', 'This must be the email on file with your NOCHEX account.');
        $tpl->assign('tooltips', $tooltips);

        $values['geo_path'] = GEO_BASE_DIR;
        $values['logo_path'] = geoString::specialChars($this->get('logo_path'));
        $values['email'] =  geoString::specialChars($this->get('email'));
        $tpl->assign('values', $values);

        $settings .= geoHTML::addOption('Path to Geo Install', GEO_BASE_DIR);
        $settings .= geoHTML::addOption('Logo Path' . $tooltip1, '<input type="text" name="nochex[logo_path]" value="' . geoString::specialChars($logo_path) . '" />');
        $settings .= geoHTML::addOption('Email' . $tooltip2, '<input type="text" name="nochex[email]" value="' . geoString::specialChars($email) . '" />');

        $tpl->assign('settings', $settings);
        $html = $tpl->fetch('payment_gateways/nochex.tpl');

        return $html;
    }

    /**
     * Called NON-STATICALLY
     *
     * Optional function, should update any settings if applicable.
     *
     * Note that this is done IN ADDITION TO the normal "back-end" stuff such as enabling or disabling the
     * gateway and serializing any changes.  If this returns false however, that additional stuff
     * will not be done.
     *
     * @return boolean True to continue with rest of update stuff, false to prevent saving rest of settings
     *  for this gateway.
     */
    function admin_update_payment_gateways()
    {
        $admin = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        //whether allowed to enable this type or not
        $can_enable = true;
        $is_enabled = (isset($_POST['enabled_gateways'][self::gateway_name]) && $_POST['enabled_gateways'][self::gateway_name]);

        if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0) {
            $settings = $_POST[self::gateway_name];

            $this->_updateCommonAdminOptions($settings);

            $this->set('logo_path', $settings['logo_path']);
            $this->set('email', trim($settings['email']));
            $this->serialize();
        }
        return true;
    }


    public static function geoCart_payment_choicesDisplay()
    {
        $cart = geoCart::getInstance();
        //TODO: checks for using balance

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[500287],
            'title_extra' => '',
            'label_name' => self::gateway_name,
            'radio_value' => self::gateway_name,//should be same as gateway name
            'help_link' => $cart->site->display_help_link(3276),
            'checked' => false,

            //Items below will be auto generated if left blank string.
            'radio_name' => '',
            'choices_box' => '',
            'help_box' => '',
            'radio_box' => '',
            'title_box' => '',
            'radio_tag' => '',

        );

        return $return;
    }

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

        $transaction = new geoTransaction();
        $transaction->setAmount(-1 * $due);//balance out the amount due on invoice
        $transaction->setDate(geoUtil::time());
        $msgs = $cart->db->get_text(true, 183);
        $transaction->setDescription($msgs[500577]);
        $transaction->setGateway($gateway);
        $transaction->setInvoice($invoice);
        $transaction->setStatus(0);//since payment is automatic, do it automatically.
        $transaction->setUser($cart->user_data['id']);

        $transaction->save();//save changes

        $invoice->addTransaction($transaction);
        $cart->order->setStatus('pending');

        trigger_error("DEBUG NOCHEX: STARTS NOCHEX URL TRANSFER");
        $url = array();

        $total = $invoice->getInvoiceTotal();
        $nochex_test = $gateway->get('testing_mode');

        $responder_url = str_replace($cart->db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=nochex", $cart->db->get_site_setting("classifieds_url"));

        $url['amount'] = abs($total);
        $url['logo'] = $gateway->get('logo_path');
        $url['email'] = ($nochex_test == 1) ? 'test1@nochex.com' : $gateway->get('email');
        //not really sure what this setting is for...
        //$url['receiver_email'] = $cart->db->get_site_setting('nochex_id');
        $url['returnurl'] = $cart->getProcessFormUrl();
        $url['responderurl'] = $responder_url;
        $url['firstname'] = $cart->user_data['firstname'];
        $url['lastname'] = $cart->user_data['lastname'];
        $url['firstline'] = $cart->user_data['address'] . ' ' . $cart->user_data['address_2'];
        $url['town'] = $cart->user_data['city'];
        $url['county'] = $cart->user_data['state'];
        $url['postcode'] = $cart->user_data['zip'];

        if ($cart->db->get_site_setting('joe_edwards_discountLink')) {
            //special case to "hijack" seller email based on chosen discount code
            // (only used for Authorize.net and Nochex)

            //find the active discount_codes item, if there is one
            $items = $cart->order->getItem();
            $discount_item = null;
            foreach ($items as $item) {
                if ($item->getType() == 'addon_discount_codes') {
                    $discount_item = $item;
                    break;
                }
            }
            $je_result = $discount_item->joe_edwards_getEmail();
            if ($je_result && strlen($je_result) > 0) {
                $cart->user_data['email'] = $je_result;
            }
        }

        $url['email_address_sender'] = $cart->user_data['email'];


        $url['description'] = $transaction->getDescription();
        $url['ordernumber'] = $transaction->getId();

        //echo '<pre>'.print_r($url,1).'</pre><br /><br />';
        foreach ($url as $key => $value) {
            $new_url[] = "{$key}=" . urlencode($value);
        }
        $cc_url = implode('&', $new_url);
        $cc_url = 'https://www.nochex.com/nochex.dll/checkout?' . $cc_url;


        //set order status to Pending to prep for sending data to gateway
        $cart->order->setStatus('pending');

        //stop the cart session
        $cart->removeSession();

        require GEO_BASE_DIR . 'app_bottom.php';
        //go to 2checkout to complete
        header("Location: " . $cc_url);
        exit;
    }

    public static function geoCart_process_orderDisplay()
    {
    }

    public function transaction_process()
    {

        $response = $_POST;

        $transaction = geoTransaction::getTransaction($_POST['ordernumber']);
        $order = $transaction->getInvoice()->getOrder();
        $gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);

        //TODO: first, create way for admin to run APC verify script thingy.  Then, finish this function.
        //check to see if the info is valid?
        if (function_exists('curl_init')) {
            if (!function_exists('curl_ini')) {
                //something's wrong
                return false;
            }

            $url = "https://www.nochex.com/nochex.dll/apc/apc";
            $reffer = '';

            /*$post['transaction_id']  = '604373';
            $post['transaction_date']=  '15/02/2008 23:16:24';
            $post['order_id']='999999';
            $post['amount']='1.99';
            $post['from_email'] = 'test1@nochex.com';
            $post['to_email'] = 'test2@nochex.com';
            $post['security_key']='37899';*/

            foreach ($response as $key => $value) {
                $post_params[] = "{$key}=$value";
            }

            //save server's response
            $transaction->set('nochex_response', $response);

            //check with nochex again to make sure transaction is authorized
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $post_params));
            curl_setopt($ch, CURLOPT_REFERER, $reffer);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (!ini_get('open_basedir')) {
                //cannot use FOLLOWLOCATION if open_basedir is set in ini
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }
            $result = curl_exec($ch);
            curl_close($ch);

            //save auth response
            $transaction->set('nochex_auth_response', $result);
            $transaction->save();

            if ($result == 'AUTHORISED') {
                //do all the stuff kinda like paypal
                self::_success($order, $transaction, $gateway);
            } else {
                //failure
                self::_failure($transaction, $result, "NOCHEX: Transaction not authorized: " . $result);
            }
        }
    }
}
