<?php
//payment_gateways/commweb.php
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
## ##    16.09.0-37-gc0c86a6
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
class commwebPaymentGateway extends geoPaymentGateway
{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'commweb';
	
	/**
	 * Required, Usually the same as the name, this can be used as a means
	 * to warn the admin that they may be using 2 gateways that
	 * are the same type.  Mostly used to distinguish CC payment gateways
	 * (by using type of 'cc'), but can be used for other things as well.
	 *
	 * @var string
	 */
	public $type = 'commweb';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'commweb';
	
	protected static $submit_url = 'https://migs.mastercard.com.au/vpcpay?';
	
	/**
	 * Optional.
	 * Used in admin, in paymentGatewayManage::getGatewayTable() which is used in both ajax calls,
	 * and to initially display the gateway page.
	 * 
	 * Expects to return an array:
	 * array (
	 * 	'name' => $gateway->name,
	 * 	'title' => 'What to display in list of gateways',
	 * )
	 * 
	 * Note: if need extra settings besides just being turned on or not,
	 *  see the method admin_custom_config()
	 * @return array
	 *
	 */
	public static function admin_display_payment_gateways ()
	{
		$return = array (
			'name' => self::gateway_name,
			'title' => 'CommWeb VPC',//how it's displayed in admin
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
	public function admin_custom_config ()
	{
		$db = DataAccess::GetInstance();
		$tpl = new geoTemplate('admin');
		$tpl->assign('payment_type', self::gateway_name);
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(false));

		$tooltips['merchant_id'] = geoHTML::showTooltip('Merchant ID','Supplied by CommWeb');
		$tooltips['access_code'] = geoHTML::showTooltip('Access Code','Supplied by CommWeb');
		$tooltips['hash_secret'] = geoHTML::showTooltip('Secure Hash Secret','Supplied by CommWeb');

		$tpl->assign('tooltips', $tooltips);
		
		$values = array(
			'merchant_id' => $this->get('merchant_id'),
			'access_code' => $this->get('access_code'),
			'hash_secret' => $this->get('hash_secret'),
		);
		$tpl->assign('values', $values);
		
		return $tpl->fetch('payment_gateways/commweb.tpl');
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
	public function admin_update_payment_gateways ()
	{
		if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0){
			$settings = $_POST[self::gateway_name];
		
			//save common settings
			$this->_updateCommonAdminOptions($settings);
		
			//save non-common settings
			$this->set('merchant_id',trim($settings['merchant_id']));
			$this->set('access_code',trim($settings['access_code']));
			$this->set('hash_secret',trim($settings['hash_secret']));
			//$this->set('use_cvv2', true);
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
	 * @param array $vars Array of info, see source of method for further documentation.
	 * @return array Associative Array as specified above.
	 *
	 */
	public static function geoCart_payment_choicesDisplay ($vars)
	{
		$cart = geoCart::getInstance(); //get cart to use the display_help_link function
		
		$msgs = $cart->db->get_text(true, 10203);
		$return = array(
			//Items that don't auto generate if left blank
			'title' => $msgs[502381],
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
		trigger_error ('DEBUG TRANSACTION: Top of '.self::gateway_name.': geoCart_payment_choicesProcess() - processing');
		
		$cart = geoCart::getInstance();
		
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		
		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $due = $invoice->getInvoiceTotal();
		
		if ($due >= 0) {
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}
		
		$amount = -1 * $due; //amount to charge, as a positive dollar value
		$amountCents = ceil($amount*100); //commweb expects amount expressed as an integer of cents
		
		//do standard transaction stuff
		$transaction = new geoTransaction;
		$transaction->setAmount($amount);//balance out the amount due on invoice
		$transaction->setDate(geoUtil::time());
		$msgs = $cart->db->get_text(true,183);
		$transaction->setDescription($msgs[502382]);		
		$transaction->setGateway($gateway);
		$transaction->setInvoice($invoice);
		$transaction->setStatus(0);
		$transaction->setUser($cart->user_data['id']);
		$invoice->addTransaction($transaction);
		$transaction->save(); //save this here, so that it gets an ID
		
		$url = self::$submit_url;

		$fields['vpc_AccessCode'] = $gateway->get('access_code');
		$fields['vpc_Amount'] = $amountCents;
		$fields['vpc_Command'] = "pay";
		$fields['vpc_Locale'] = "en";
		$fields['vpc_Merchant'] = $gateway->get('merchant_id');
		$fields['vpc_MerchTxnRef'] = $transaction->getId();
		$fields['vpc_OrderInfo'] = $invoice->getOrder()->getId();
		$fields['vpc_ReturnURL'] = geoFilter::getBaseHref().'transaction_process.php?gateway=commweb';
		$fields['vpc_Version'] = "1";
		
		ksort($fields); //put fields in alphabetical order by key, for hashing
		
		//now string all the fields out into a full URL, and redirect to it
		$i=0;
		$hashinput = '';
		foreach($fields as $key => $value) {
			if($i++ != 0) {
				$url .= '&';
				$hashinput .= '&';
			}
			$url .= urlencode($key) . '=' . urlencode($value);
			if(strlen($value) > 0) {
				$hashinput .= $key .'='. $value;
			}
		}
		
		//compute hash and add it in
		$fields['vpc_SecureHash'] = strtoupper(hash_hmac('SHA256', $hashinput, pack('H*',$gateway->get('hash_secret'))));
		$fields['vpc_SecureHashType'] = 'SHA256';
		
		$url .= '&vpc_SecureHash=' . $fields['vpc_SecureHash'] . '&vpc_SecureHashType=' . $fields['vpc_SecureHashType'];    
		
		//remember what the final URL is, for debugging
		$transaction->set('commweb_url',$url);
		$transaction->save();
		
		//set order to pending and kill the cart session
		$cart->order->setStatus('pending');
		$cart->removeSession();
		
		//redirect user to payment page
		header('Location: '.$url);
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
	 * https://example.com/transaction_process.php?gateway=commweb
	 * 
	 * As the "signal/notification URL" to send notifications to (obviously would need
	 * to adjust for the actual payment gateway and actual site's URL).  Don't
	 * forget to authenticate the signal in some way, to validate it is indeed
	 * coming from the payment processor!
	 */
	public function transaction_process ()
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		$merchantId = $gateway->get('merchant_id');
		$hashSecret = $gateway->get('hash_secret');
		
		$r = $_GET;
				
		//first, validate the hash
		
		//to do so, sort all incoming vpc fields and leave out any with no value
		$hashMe = '';
		foreach($r as $key => $value) {
			if($key !== 'vpc_SecureHash' && $key !== 'gateway' && $key !== 'vpc_SecureHashType' && strlen($value) > 0) {
				if($i++ != 0) {
					$hashMe .= '&';
				}
				$hashMe .= $key . '=' . $value;
			}
		}
		
		$check = hash_hmac('SHA256', $hashMe, pack('H*',$hashSecret));

		//make sure hash matches
		if(strtoupper($check) != strtoupper($r['vpc_SecureHash'])) {
			trigger_error('ERROR TRANSACTION: secret hash did not match');
			return false;	
		}
		
		//may as well doublecheck the merchant ID, too
		if($merchantId != $r['vpc_Merchant']) {
			trigger_error('ERROR TRANSACTION: what kind of transaction goes best with cheese? Nacho Transaction!');
			return false;
		}
		
		//now reacquire the transaction
		$transaction = geoTransaction::getTransaction($r['vpc_MerchTxnRef']);
		if(!$r['vpc_MerchTxnRef'] || ($r['vpc_MerchTxnRef'] != $transaction->getId())) {
			trigger_error('ERROR TRANSACTION: could not reacquire transaction');
			return false;
		}
		
		//if an error state, this will be 1-7, or the letter E
		$success = is_numeric($r['vpc_TxnResponseCode']) && $r['vpc_TxnResponseCode'] == 0 ? true : false;
		
		//start up the session, so the result page doesn't show the not-logged-in version
		geoSession::getInstance()->initSession();
		
		if($success) {
			self::_success($transaction->getInvoice()->getOrder(), $transaction, $gateway);
		} else {
			self::_failure($transaction, $r['vpc_TxnResponseCode'], $r['vpc_Message']);
		}
		return true;
	}
	
}