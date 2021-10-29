<?php
//payment_gateways/_cc_template.php
/**
 * This is a "developer template" for creating a new payment gateway that
 * accepts CC number, a developer would use this file as a starting point for
 * creating such a new payment gateway.
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
## ##    7.3rc1-3-ge430767
## 
##################################

/**
 * This extends the _ccPaymentGateway class, so need to include that file.
 */
require_once CLASSES_DIR . 'payment_gateways/_cc.php';

/**
 * Template CC payment gateway handler, a developer would use this as a starting
 * point if one wished to create a payment gateway that accepts credit cards.
 * @package System
 * @since Version 4.0.0
 */
class _cc_templatePaymentGateway extends _ccPaymentGateway{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = '_cc_template';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = '_cc_template';
	
	/**
	 * Sugested, specify the "testing" or "sandbox" URL here so it can
	 * easily be updated later if needed.
	 *
	 * @var string
	 */
	private static $_submitUrlTesting = 'https://sandbox.gateway.com';
	
	/**
	 * Suggested, specify the "live" URL to process payments through the
	 * gateway here so it can easily be updated later if needed.
	 *
	 * @var string
	 */
	private static $_submitUrl = 'https://gateway.com';
	
	/**
	 * Optional.
	 * Used in admin, in paymentGatewayManage::getGatewayTable() which is used in both ajax calls,
	 * and to initially display the gateway page.
	 * 
	 * Expects to return an array:
	 * array (
	 * 	'name' => $gateway->name,
	 * 	'title' => 'What to display in list of gateways', //should be pre-pended with "CC - " so it is easy 
	 *   //to figure out it's a credit card gateway 
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
			'title' => 'CC - Template Gateway',//how it's displayed in admin
			'head_html' => "<script type='text/javascript'>
		 	Style[1]=[\"white\",\"#000099\",\"\",\"\",\"\",,\"black\",\"#e8e8ff\",\"\",\"\",\"\",,,,2,\"#000099\",2,,,,,\"\",3,,,];
			var TipId = \"tiplayer\";
			var FiltersEnabled = 1;
			//mig_clay();
			var mig_clay_run_already = false;
			function run_mig_clay(){
				if (!mig_clay_run_already){
					mig_clay_run_already = true;
					mig_clay();
				}
			}
			</script>"//optional, if specified, 
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
	public function admin_custom_config (){
		$html = 'Settings for _cc_template gateway!';
		
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
	public function admin_update_payment_gateways(){
		//Do checks or additional setting save here.
		
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
	public static function geoCart_payment_choicesDisplay ($gateway=null)
	{
		//Most CC gateways: use this function exactly as-is
		
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		return parent::geoCart_payment_choicesDisplay($gateway);
	}
	
	/**
	 * Required.
	 * Used: in geoCart::payment_choicesCheckVars()
	 * 
	 * Defined in parent, need to call the parent and pass
	 * an instance of the gateway object for this gateway.
	 * 
	 * Also may need to do any additional input var checking
	 * specific to this gateway (but that is not typical)
	 * 
	 * @param null $gateway Will never be passed in, this var must be generated
	 *   and passed to the parent.
	 * @param null $skip_checks Will never be passed in, if applicable this should
	 *   be populated when passing to parent.  See parent docs for more info.
	 * @return array Results of the call to the parent.
	 */
	public static function geoCart_payment_choicesCheckVars ($gateway=null, $skip_checks=null)
	{
		//Most CC gateways: use this function exactly as-is
		
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		return parent::geoCart_payment_choicesCheckVars($gateway);
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
	public static function geoCart_payment_choicesProcess(){
		//Sample of how things could be done:
		/**
		 * NOTE: This is an EXAMPLE ONLY :: The below was modeled loosely
		 * after payflow pro's implementation, but will need to be changed 
		 * to match how each gateway processes transactions!
		 * 
		 * The things that are normally done by all gateways:
		 * parent::_getInfo() - returns array of info of user-input like cc num, exp date, etc.
		 *  with certain things already cleaned (see docs on function for which specific things
		 *  are already cleaned)
		 * parent::_createNewTransaction($order,$gateway,$info) - creates and returns a new
		 *  transaction, with the CC number already encrypted.  May need to add info specific
		 *  to this gateway using $transaction->set('name','value')
		 * parent::_success($order, $transaction, $gateway) - Call to do common things for when the 
		 *  payment went through successfully.
		 * parent::_failure($transactin, $failure_code, $failure_msg) - call to do common things
		 *  when the payment was not successful.
		 */
		  
		
		//get the cart
		$cart = geoCart::getInstance();
		
		//get the gateway since this is a static function
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		
		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $invoice->getInvoiceTotal();
		
		if ($invoice_total >= 0){
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}
		//BUILD DATA TO SEND TO GATEWAY TO COMPLETE THE TRANSACTION
		$info = parent::_getInfo();
		
		//create initial transaction
		try {
			//let parent create a new transaction, since it does all that common stuff
			//for us.
			$transaction = self::_createNewTransaction($cart->order,$gateway, $info);
			
			//Add the transaction to the invoice
			$transaction->setInvoice($invoice);
			$invoice->addTransaction($transaction);
			
			//save it so there is an id
			$transaction->save();
		} catch (Exception $e){
			//catch any error thrown by _createNewTransaction
			trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: Exception thrown when attempting to create new transaction.');
			return;
		}
		
		//******************************************************************************
		// PROCESS TRANSACTION HERE
		
		//URL TO SUBMIT TRANSACTIONS TO (assuming the gateway setting testing_mode is set to 1 to signify using
		// the test url)
		$url = ($gateway->get("testing_mode") == 1)? self::$_submitUrlTesting : self::$_submitUrl;
		
		$card_num = $info["cc_number"];
		$cvv2 = $info["cvv2_code"];	// 123
		$expiry = $info['exp_month'].substr($info['exp_year'], 2);  // Example if gateway uses 2-digit year.
		$amount = number_format($transaction->getAmount(),2,'.','');  // Example to force certain format for amount
		
		// Billing Details Example
		$fname = urlencode($info['firstname']);
		$lname = urlencode($info['lastname']);
		$email = urlencode($info['email']);
		$street = urlencode("{$info['address']} {$info['address_2']}");
		$city = urlencode($info['city']);
		$state = urlencode($info['state']);
		$zip = urlencode($info['zip']);
		$country = urlencode($info['country']);	// 3-digits ISO code
		
		// Other information
		$ipaddr = $_SERVER['REMOTE_ADDR'];
		if ($show_payflow_pro["testing_mode"] == 1){
			$custom = 'Testing Only';
		}
		
		##  Create Connection to Gateway Here
		
		//...
		
		##  Process results of gateway here
		
		//...
		
		## Interpret the results here
		
		//...
		
		if ($transaction_successful) {
			//TRANSACTION SUCCESSFUL!!
			trigger_error('DEBUG TRANSACTION CART '.self::gateway_name.': no errors, payment good!');
			
			//Let the parent do the common stuff for when the transaction was a success
			return self::_success($cart->order,$transaction, $gateway);
		} else {
			//TRANSACTION FAILED!!!
			$message = 'Transaction Failed: Enter a human-readable explanation of why '.
					 'the transaction may have failed, this will be viewable only to people'.
					 'debugging.';
			$message .= 'Response message received from gateway:  '.$result['RESPMSG'];
			
			trigger_error('ERROR TRANSACTION CART '.self::gateway_name.': '.$message);
			
			//Let the parent do the common stuff for when the transaction was a failure.
			return self::_failure($transaction,self::FAIL_CHECK_GATEWAY_SETTINGS,$message);
		}
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
	 * Most CC gateways can leave this un-defined, as it is handled by the parent.
	 *
	 */
	public static function geoCart_process_orderDisplay(){
		//use to display some success/failure page, if that applies to this type of gateway.
		
		//most can just leave it up to the parent to do, since this is pretty standard.
		return parent::geoCart_process_orderDisplay();
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
	public static function auction_final_feesOrderItem_cron_close_listings ($vars)
	{
		//vars is an associative array, with the listing being closed and the order
		//containing auction final fees.
		$listing = $vars['listing'];
		$order = $vars['order'];
		
		//do stuff here.
		//NOTE: If you are auto-paying the order here, BE SURE TO:
		//$order->set('payment_type',self::gateway_name);
	}
}