<?php

//payment_gateways/paypal_advanced.php


require_once CLASSES_DIR . 'payment_gateways/_cc.php';

# Payflow Pro gateway handler

class paypal_advancedPaymentGateway extends geoPaymentGateway
{
    public $name = 'paypal_advanced';
    const gateway_name = 'paypal_advanced';

    private static $_submitUrlTesting = 'https://pilot-payflowpro.paypal.com';
    private static $_submitUrl = 'https://payflowpro.paypal.com';

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
            'title' => 'PayPal Payments Advanced',//how it's displayed in admin
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
     * @return HTML to display below gateway when user clicked the settings button
     */
    public function admin_custom_config()
    {

        $tpl = new geoTemplate('admin');
        $tpl->assign('payment_type', self::gateway_name);

        $tpl->assign('adminMsgs', geoAdmin::m());

        $tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());
        $tooltips['vendor'] = geoHTML::showTooltip('Vendor', 'This is your vendor name, defined at registration time at Paypal Payments Advanced.');
        $tooltips['user'] = geoHTML::showTooltip('User', 'This is your user name, defined at registration time at Paypal Payments Advanced. If you do not place anything in this box, then the Vendor name will be used.');
        $tooltips['password'] = geoHTML::showTooltip('Password', 'This is your password, defined at registration time at Paypal Payments Advanced.');
        $tooltips['layout'] = geoHTML::showTooltip('Layout', 'The "Layout" type selected in your Paypal Payments Advanced Manager.');
        $tpl->assign('tooltips', $tooltips);

        $values['vendor'] = geoString::specialChars($this->get('vendor'));
        $values['user'] = geoString::specialChars($this->get('user'));
        $values['password'] = geoString::specialChars($this->get('password'));
        $values['layout'] = geoString::specialChars($this->get('layout'), 'C');

        $tpl->assign('values', $values);

        return $tpl->fetch('payment_gateways/paypal_advanced.tpl');
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
        if (isset($_POST['paypal_advanced']) && is_array($_POST['paypal_advanced']) && count($_POST['paypal_advanced']) > 0) {
            $settings = $_POST['paypal_advanced'];
            $this->_updateCommonAdminOptions($settings);

            $this->set('vendor', trim($settings['vendor']));
            $this->set('user', trim($settings['user']));
            $this->set('password', trim($settings['password']));
            $this->set('layout', trim($settings['layout']));

            $this->serialize();
        }
        return true;
    }
    public static function geoCart_payment_choicesDisplay($vars)
    {
        $cart = geoCart::getInstance();
        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[502294],
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

        //get invoice on the order
        $invoice = $cart->order->getInvoice();
        $transaction = new geoTransaction();
        $transaction->save(); //so that it has an ID
        $invoice->addTransaction($transaction->getId());

        $due = $invoice->getInvoiceTotal();

        if ($due >= 0) {
            //DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
            $cart->addError()->addErrorMsg('payment_error', 'invalid amount');
            return;
        }

        $testing = $gateway->get('testing_mode') ? 1 : 0;
        $url = $testing ? self::$_submitUrlTesting : self::$_submitUrl;

        //start a transaction

        $transaction->setGateway(self::gateway_name);
        $transaction->setUser($cart->user_data['id']);
        $transaction->setInvoice($invoice);
        $transaction->setAmount(-1 * $due);
        $transaction->setDescription('Payment Via Paypal Advanced');
        $transaction->setStatus(0);


        $cart->order->set('transaction_id', $transaction->getId());
        $cart->order->save();

        $tokenId = md5($transaction->getId() . geoUtil::time());

        $amount = number_format($transaction->getAmount(), 2, '.', ''); //get the price to two decimal places

        $info = $cart->user_data['billing_info'];

        //get secure token
        $params = array(
            'PARTNER' => 'PayPal',
            'VENDOR' => $gateway->get('vendor'),
            'USER' => ($gateway->get('user') ? $gateway->get('user') : $gateway->get('vendor')),
            'PWD' => $gateway->get('password'),
            'TRXTYPE' => 'S',
            'AMT' => $amount,
            'CREATESECURETOKEN' => 'Y',
            'SECURETOKENID' => $tokenId,
            'BILLTOFIRSTNAME' => $info['firstname'],
            'BILLTOLASTNAME' => $info['lastname'],
            'BILLTOSTREET' => $info['address'],
            'BILLTOSTREET2' => $info['address_2'],
            'BILLTOCITY' => $info['city'],
            'BILLTOSTATE' => $info['state'],
            'BILLTOZIP' => $info['zip'],
            'BILLTOCOUNTRY' => $info['country'],
            'BILLTOPHONENUM' => $info['phone'],
            'BILLTOEMAIL' => $info['email'],
// shipping data probably isn't needed, but could be added here
//          'SHIPTOFIRSTNAME' => $info['firstname'],
//          'SHIPTOLASTNAME' => $info['lastname'],
//          'SHIPTOSTREET' => $info['address'],
//          'SHIPTOSTREET2' => $info['address_2'],
//          'SHIPTOCITY' => $info['city'],
//          'SHIPTOSTATE' => $info['state'],
//          'SHIPTOZIP' => $info['zip'],
//          'SHIPTOCOUNTRY' => $info['country'],
//          'SHIPTOPHONENUM' => $info['phone'],
//          'SHIPTOEMAIL' => $info['email'],
            'RETURNURL' => geoFilter::getBaseHref() . 'transaction_process.php?gateway=paypal_advanced',
            'CANCELURL' => geoFilter::getBaseHref() . 'transaction_process.php?gateway=paypal_advanced',
            'ERRORURL' => geoFilter::getBaseHref() . 'transaction_process.php?gateway=paypal_advanced',
        );

        $paramList = array();
        foreach ($params as $key => $val) {
            if (strlen($val)) {
                $paramList[] = "{$key}[" . strlen($val) . "]={$val}";
            }
        }
        $paramStr = implode('&', $paramList);

        $response = geoPC::urlPostContents($url, $paramStr);
        parse_str($response, $vars);
        if ($vars['RESULT'] != 0) {
            //error getting secure token
            $cart->addError()->addErrorMsg('paypal_adv_error', 'Error getting secure token from PayPal: ' . $vars['RESPMSG'] . ' (' . $vars['RESULT'] . ')');
            return false;
        }

        //save the token ID to use in reacquiring the transaction later
        $transaction->setGatewayTransaction($tokenId);
        $transaction->set('cart_id', $cart->cart_variables['id']);
        $transaction->save();

        //note: this URL uses a different subdomain
        $target = 'https://payflowlink.paypal.com' . '?SECURETOKEN=' . $vars['SECURETOKEN'] . '&SECURETOKENID=' . $tokenId . ($testing ? '&MODE=TEST' : '');

        if ($gateway->get('layout') === 'C') {
            if (geoSession::isMobile()) {
                $target .= '&template=mobile';
            }
            $tpl_vars = array('iframeTarget' => $target);
            geoView::getInstance()->setBodyTpl('paypal_advanced/iframe.tpl', '', 'payment_gateways')
                ->setBodyVar($tpl_vars);
            $cart->site->display_page();
        } else {
            header('Location: ' . $target);
        }
        include_once GEO_BASE_DIR . 'app_bottom.php';
        exit();
    }

    public function transaction_process()
    {

        $tokenId = $_POST['SECURETOKENID'];
        if (!$tokenId) {
            //invalid transaction...shouldn't be here
            header("Location: " . DataAccess::getInstance()->get_site_setting('classifieds_url') . "?a=cart");
            include_once GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }

        $transaction = geoTransaction::getTransaction($tokenId);
        if ($transaction->getGatewayTransaction() != $tokenId) {
            //this is not the transaction we're looking for. something's wrong
            header("Location: " . DataAccess::getInstance()->get_site_setting('classifieds_url') . "?a=cart");
            include_once GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }

        //now that we know we're looking at a good transaction, try to get the current user's Session
        $session = geoSession::getInstance();
        $session->initSession();
        if ($session->getUserId()) {
            //this is the user returning to the site (not just an IPN-ish notification)
            //retrieve the Cart contents
            $cart = geoCart::getInstance();
            $cart_id = $transaction->get('cart_id');
            //with this, we can clear the cart a bit further down if the transaction was successful (if it was not, no need to clear anything)
        }

        if ($_POST['RESULT'] != 0) {
            //processing a "failed" message, so just accept that result and move on
            $this->failure($transaction, $_POST['RESULT'], $_POST['RESPMSG']);
            return;
        }

        //this SAYS it's okay, but don't trust it yet! Submit an Inquiry request

        $params = array(
            'TRXTYPE' => 'I',
            'SECURETOKEN' => $_POST['SECURETOKEN'],
            'PARTNER' => 'PayPal',
            'VENDOR' => $this->get('vendor'),
            'USER' => ($this->get('user') ? $this->get('user') : $this->get('vendor')),
            'PWD' => $this->get('password'),
        );
        $testing = $this->get('testing_mode') ? 1 : 0;
        $url = $testing ? self::$_submitUrlTesting : self::$_submitUrl;

        $paramList = array();
        foreach ($params as $key => $val) {
            if (strlen($val)) {
                $paramList[] = "{$key}[" . strlen($val) . "]={$val}";
            }
        }
        $paramStr = implode('&', $paramList);
        $response = geoPC::urlPostContents($url, implode('&', $paramList));
        parse_str($response, $vars);

        //note: skip display from _success()/_failure() since this does things a little different
        if (isset($vars['ORIGRESULT']) && $vars['ORIGRESULT'] == 0) {
            if ($cart && $cart_id) {
                //everything's done and paid for, so clear out the Cart
                $cart->removeSession($cart_id);
                define('geoCart_skipSave', 1); //make sure we don't re-save the cart after clearing it!
            }
            $this->success($transaction);
        } else {
            //Inquiry says this failed/is bad
            $this->failure($transaction, $vars['ORIGRESULT'], $vars['RESPMSG']);
        }
    }

    /**
     * custom wrapper for _success() for PayPal Advanced, to let it do some fancy stuff with breaking out of iframes when needed
     * @param geoTransaction $transaction
     */
    private function success($transaction)
    {
        $isLayoutC = ($this->get('layout') === 'C') ? true : false;
        $order = $transaction->getInvoice()->getOrder();
        self::_success($order, $transaction, $this, $isLayoutC);
        if ($isLayoutC) {
            //in an iframe, so we can't use the built-in display stuff!
            $transaction->save();
            if ($order) {
                $order->save();
            }
            geoView::getInstance()->setRendered(true); //so we don't try anything funny like printing template stuff right here
            $goto = geoFilter::getBaseHref() . 'transaction_result.php?transaction=' . $transaction->getId();
            echo '<script>window.top.location.href="' . $goto . '";</script>';
            include_once GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }
    }

    /**
     * custom wrapper for _failure() for PayPal Advanced, to let it do some fancy stuff with breaking out of iframes when needed
     * @param geoTransaction $transaction
     */
    private function failure($transaction, $code, $message)
    {
        $isLayoutC = ($this->get('layout') === 'C') ? true : false;
        self::_failure($transaction, $code, $message, $isLayoutC);
        if ($isLayoutC) {
            //in an iframe, so we can't use the built-in display stuff!
            $transaction->save();
            geoView::getInstance()->setRendered(true); //so we don't try anything funny like printing template stuff right here
            $goto = geoFilter::getBaseHref() . 'transaction_result.php?transaction=' . $transaction->getId();
            echo '<script>window.top.location.href="' . $goto . '";</script>';
            include_once GEO_BASE_DIR . 'app_bottom.php';
            exit();
        }
    }
}
