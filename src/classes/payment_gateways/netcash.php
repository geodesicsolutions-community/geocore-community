<?php
//payment_gateways/netcash.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

//docs: http://www.sagepay.co.za/sagepay/partners_developers-technical_documents-pay_now.asp

# Template payment gateway handler

class netcashPaymentGateway extends geoPaymentGateway
{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'netcash';

	/**
	 * Required, Usually the same as the name, this can be used as a means
	 * to warn the admin that they may be using 2 gateways that
	 * are the same type.  Mostly used to distinguish CC payment gateways
	 * (by using type of 'cc'), but can be used for other things as well.
	 *
	 * @var string
	 */
	public $type = 'netcash';

	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'netcash';

	/**
	 * Optional.
	 * Used in admin, in paymentGatewayManage::getGatewayTable() which is used in both ajax calls,
	 * and to initially display the gateway page.
	 *
	 * Expects to return an array:
	 * array (
	 * 	'name' => $gateway->name,
	 * 	'title' => 'What to display in list of gateways',
	 *  'head_html' => 'Will be inserted into the head section of the page.'
	 * )
	 *
	 * Note: if need extra settings besides just being turned on or not,
	 *  see the method admin_custom_config()
	 * @return array
	 *
	 */
	public static function admin_display_payment_gateways (){
		$return = array (
			'name' => self::gateway_name,
			'title' => 'Netcash.co.za (Sage Pay)',//how it's displayed in admin
			'head_html' => ""//optional, if specified, 
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
	public function admin_custom_config (){

		$db = DataAccess::GetInstance();

		$tpl = new geoTemplate('admin');
		
		$tpl->assign('payment_type', self::gateway_name);

		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());

		$tooltips['username'] = geoHTML::showTooltip('Username','Also referred to as the Pay Now Service Key');
		$tooltips['password'] = geoHTML::showTooltip('Password','Also referred to as the Vendor Number or Software Vendor Key');
		$tpl->assign('tooltips', $tooltips);
		
		$values = array(
			'username' => $this->get('username'),
			'password' => $this->get('password', '24ade73c-98cf-47b3-99be-cc7b867b3080'),
		);
		$tpl->assign('values', $values);

		
		$tpl->assign('responseURL', str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php", $db->get_site_setting("classifieds_url")));
		
		return $tpl->fetch('payment_gateways/netcash.tpl');
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
	public function admin_update_payment_gateways(){
		$admin = true;
		include GEO_BASE_DIR.'get_common_vars.php';
		//whether allowed to enable this type or not
		$can_enable = true;
		$is_enabled = (isset($_POST['enabled_gateways'][self::gateway_name]) && $_POST['enabled_gateways'][self::gateway_name]);

		if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0){
			$settings = $_POST[self::gateway_name];

			//save common settings
			$this->_updateCommonAdminOptions($settings);

			//save non-common settings
			$this->set('username',trim($settings['username']));
			$this->set('password',trim($settings['password']));
			$this->serialize();
		}


		return true;
	}



	/**
	 * Optional.
	 * Used: in geoCart::payment_choicesDisplay()
	 *
	 * Should return an associative array that is structured as follows:
	 * array(
	 * 	'title' => string,
	 * 	'title_extra' => string,
	 * 	'label_name' => string, //needs to be: self::gateway_name,
	 * 	'radio_value' => string, //should be self::gateway_name
	 * 	'help_link' => string, //entire link including a tag and link text, example: $cart->site->display_help_link(3240),
	 * 	'checked' => boolean, //leave false to let system determine if it is checked or not, true to force being checked
	 * 	//Items below will be auto generated if left as empty string.
	 * 	'radio_name' => string,//usually c[self::gateway_name] - this set by system if left as empty string.
	 * 	'choices_box' => string,//use custom stuff for the entire choice box.
	 * 	'help_box' => string,//use custom stuff for help link and box surrounding it.
	 * 	'radio_box' => string,//use custom box for radio
	 * 	'title_box' => string,//use custom box for title
	 * 	'radio_tag' => string//use custom tag for radio tag
	 * )
	 *
	 * @return array Associative Array as specified above.
	 *
	 */
	public static function geoCart_payment_choicesDisplay(){
		$cart = geoCart::getInstance(); //get cart to use the display_help_link function
		
		$msgs = $cart->db->get_text(true, 10203);
		$return = array(
		//Items that don't auto generate if left blank
			'title' => $msgs[500781],
			'title_extra' => '',
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
	public static function geoCart_payment_choicesCheckVars (){
		
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
	public static function geoCart_payment_choicesProcess(){
		trigger_error('DEBUG TRANSACTION: Top of process netcash.');
		$cart = geoCart::getInstance();
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		$user_data = $cart->user_data;

		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $due = $invoice->getInvoiceTotal();

		if ($due >= 0){
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}
		
		$transaction = new geoTransaction();
		$transaction->setGateway(self::gateway_name);
		$transaction->setUser($cart->user_data['id']);
		$transaction->setStatus(0); //for now, turn off until it comes back from paypal IPN.
		$transaction->setAmount(-1 * $due);//set amount that it affects the invoice
		$msgs = $cart->db->get_text(true,183);
		
		$transaction->setDescription($msgs[500782]);
		
		$transaction->setInvoice($invoice);

		$transaction->save();

		$testing = $gateway->get('testing_mode');
		


		//build redirect
		$formdata = $cart->user_data['billing_info'];
		
		$post_fields = array(
			'm1' => $gateway->get('username'),
			'm2' => $gateway->get('password'),
			'p2' => $transaction->getId(),
			'p3' => $transaction->getDescription(),
			'p4' => number_format($transaction->getAmount(),2,'.',''),
			'Budget' => 'N', //I really haven't the foggiest idea what this is supposed to do, but it's marked as required in the docs, so I'm guessing 'N' for now!
			'm9' => $formdata['email'],
			'm10' => 'gateway=netcash'
		);
		$transaction->set('debug_fields',$post_fields);
		
		$post_url = 'https://paynow.sagepay.co.za/site/paynow.aspx';
		
		
		$transaction->save();
		
		//add transaction to invoice
		$invoice->addTransaction($transaction);
		
		//set order to pending
		$cart->order->setStatus('pending');
		
		//stop the cart session
		$cart->removeSession();
		
		$gateway->_submitViaPost($post_url, $post_fields);
	}

	/**
	 * called by transaction_process.php
	 * 
	 * This is where we handle the response from the gateway's server 
	 * 
	 */
	public function transaction_process()
	{
		$data = array_merge($_GET, $_POST);
		$trans_id = $data['Reference'];
		$reason = $data['Reason'];
		$success = ($data['TransactionAccepted'] === 'true');
		$transaction = geoTransaction::getTransaction($trans_id);
		if($transaction->getID() == 0) {
			//failed to reacquire transaction
			trigger_error('DEBUG TRANSACTION: failed to reacquire transaction #'.$trans_id);
			self::_failure($transaction, 2, "No response from server");
			return;
		}
		
		if($success) {
			//payment successful -- do common success stuff
			$order = $transaction->getInvoice()->getOrder();
			self::_success($order, $transaction, $this);
		} else {
			self::_failure($transaction, $reason, "Denied by Netcash with reason: ".$reason);
		}
	}
}