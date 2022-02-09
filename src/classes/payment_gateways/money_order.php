<?php

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Cash payment gateway handler

class money_orderPaymentGateway extends geoPaymentGateway
{

    public $name = 'money_order';//make it so that name is known.
    const gateway_name = 'money_order';
    public $type = 'money_order';

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
            'title' => 'Money Order',
        );

        return $return;
    }

    public static function geoCart_payment_choicesDisplay()
    {
        $cart = geoCart::getInstance();
        //TODO: checks for using balance

        $msgs = $cart->db->get_text(true, 10203);
        $return = array(
            //Items that don't auto generate if left blank
            'title' => $msgs[500284],
            'title_extra' => '',
            'label_name' => self::gateway_name,
            'radio_value' => self::gateway_name,//should be same as gateway name
            'help_link' => $cart->site->display_help_link(206),
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

    function payment_choices_getPaymentChoices($vars)
    {
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';
        $force_site_balance_only = $vars['force_site_balance_only'];
        $msgs = $vars['msgs'];
        $err_vars = $vars['err_vars'];
        $charge_percent_at_auction_end = $vars['charge_percent_at_auction_end'];
        $group_id = $vars['group_id'];
        $account_balance = $vars['account_balance'];
        $user_id = $vars['user_id'];

        if ($force_site_balance_only || $charge_percent_at_auction_end) {
            //do not show option, either only site balance options should be shown,
            //or there are ending fees that money_order can't take care of.
            return '';
        }

        //$money_order_payment = $this->payment_choices_row ('money_order_label', 'money_order_help_i', 'money_order', $radio_type, $checked,$type);

        $return = array(
            'title' => geoString::fromDB($msgs[self::gateway_name . '_label']),
            'title_extra' => '',
            'label_name' => self::gateway_name,
            'help_id' => $msgs[self::gateway_name . '_help_i'],//if 0, will not display help link icon
            //'radio_name' => '',//let it be set to default
            'checked' => '',
            'radio_value' => self::gateway_name,//used by payment gateway to identify which one is choosen.
            //following are optional, to over-write internal "mini-templates"
            //'entire_box' => 'template',
            ///'help_box' => 'template',
            //'radio_box' => 'template',
            //'title_box' => 'template',
            //'radio' => 'template',

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
        $transaction->setDescription($msgs[500578]);
        $transaction->setGateway($gateway);
        $transaction->setInvoice($invoice);
        $transaction->setStatus(0);//since payment is automatic, do it automatically.
        $transaction->setUser($cart->user_data['id']);

        $transaction->save();//save changes

        $invoice->addTransaction($transaction);

        $cart->order->setStatus('pending_admin');
    }



    public static function geoCart_process_orderDisplay()
    {
        $cart = geoCart::getInstance();

        self::_successFailurePage(true, $cart->order->getStatus(), true, $cart->order->getInvoice());

        //send email to admin if he wants it

        if ($cart->db->get_site_setting('user_set_hold_email')) {
            //echo $item_sell_class->db->get_site_setting('user_set_hold_email')." is the setting for hold email<br />";
            //echo "email should be sent for ad on hold<br />";
            $subject = "An order has been placed!!";
            $message = "Admin,\n\n";
            $message .= "An order has been placed and is on hold because a " . self::gateway_name . " type was chosen. See the unapproved orders section of the admin.\n\n";
            $message .= "Additional orders may be in the unapproved ads section that you were not sent an email. These will be failed auto pay attempts or if you are approving all ads.\n\n";
            geoEmail::sendMail($cart->db->get_site_setting('site_email'), $subject, $message);
        }

        //gateway is last thing to be called, so it needs to be the one that clears the session...
        $cart->removeSession();
    }
}
