<?php
//payment_gateways/_cc_template.php
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

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

# Template CC payment gateway handler

class linkpointPaymentGateway extends _ccPaymentGateway{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'linkpoint';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'linkpoint';
	
	/**
	 * Sugested, specify the "testing" or "sandbox" URL here so it can
	 * easily be updated later if needed.
	 *
	 * @var string
	 */
	private static $_submitUrlTesting = 'https://staging.linkpt.net:1129/lpc/servlet/lppay';
	
	/**
	 * Suggested, specify the "live" URL to process payments through the
	 * gateway here so it can easily be updated later if needed.
	 *
	 * @var string
	 */
	private static $_submitUrl = 'https://secure.linkpt.net:1129/';
	
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
			'title' => 'CC - LinkPoint',//how it's displayed in admin
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
		
		$tpl = new geoTemplate('admin');
		$tpl->assign('payment_type', self::gateway_name);
		
		$cert_path_tooltip = geoHTML::showTooltip('Certificate File Path','This is the full path to the <em>PEM</em> file Linkpoint has you create.<br /><br />Enter the server root path to the <strong>storenumber.pem</strong> that Linkpoint sends to you to verify your communications with them.<br /><br />They will provide this file to you, request that you rename it to your store number, and upload it to your site.');
		//$testing_tooltip = geoHTML::showTooltip('Account Status','If you wish to test your linkpoint account to make sure everything is working correctly click <strong>testing mode</strong> to the right.  If you wish to go live click <strong>live</strong> to the right.  Placing in <strong>testing mode</strong> sends the exact same data but to the Linkpoint test server.');
		$cvv2_tooltip = geoHTML::showTooltip('Require CVV2 Code','The CVV2 code is the 3 or 4 digit number printed on the front or back of the credit card.<br /><br /><strong>Recommended to require the CVV2 code for better fraud prevention.</strong><br /><br />The CVV2 code is also known as the CVM code.');
		$tpl->assign('tooltips', array("cert_path" => $cert_path_tooltip, "cvv2" => $cvv2_tooltip));
		
		//recurring billing disabled for now, this gateway isn't fully capable
		//$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(true, true));
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());
		
		$tpl->assign('store_number', geoString::specialChars($this->get('store_number')));
		$tpl->assign('cert_path', geoString::specialChars($this->get('cert_path')));
		$tpl->assign('cvv2_checked', ($this->get('use_cvv2')) ? 'checked="checked" ': '');
		
		//cvv2 = cvm_value		
		
		$tpl->assign('settings', $html);
		
		
		return $tpl->fetch('payment_gateways/linkpoint.tpl');
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
		if (isset($_POST['linkpoint']) && is_array($_POST['linkpoint']) && count($_POST['linkpoint']) > 0){
			$settings = $_POST['linkpoint'];
			//recurring billing disabled for now, this gateway isn't fully capable
			//$this->_updateCommonAdminOptions($settings, true);
			$this->_updateCommonAdminOptions($settings);
			$this->set('store_number',trim($settings['store_number']));
			//TODO: Check cert path
			$this->set('cert_path', trim($settings['cert_path']));
			$this->set('use_cvv2',((isset($settings['use_cvv2']) && $settings['use_cvv2'])? 1: 0));
			
			$this->serialize();
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
	 * @return array Results of the call to the parent.
	 *
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
			$startDate = $recurring->getStartDate();
			
			//Don't forget the rest of the processing needed specific for this gateway!
		}
		
		//BUILD DATA TO SEND TO GATEWAY TO COMPLETE THE TRANSACTION
		$info = parent::_getInfo();
		
		//create initial transaction
		try {
			//let parent create a new transaction, since it does all that common stuff
			//for us.
			$transaction = self::_createNewTransaction($cart->order,$gateway, $info);
			
			if ($recurring) $recurring->addTransaction($transaction);
			
			//Add the transaction to the invoice
			$transaction->setInvoice($invoice);
			$invoice->addTransaction($transaction);
			
			//save it so there is an id
			$transaction->save();
		} catch (Exception $e){
			//catch any error thrown by _createNewTransaction
			trigger_error('ERROR TRANSACTION CART LINKPOINT: Exception thrown when attempting to create new transaction.');
			return;
		}
		
		//******************************************************************************
		// PROCESS TRANSACTION HERE
		
		if ($gateway->get('testing_mode')) {
			//TESTING MODE
			$result_setting = "live";
			$url = self::$_submitUrlTesting;
		} else {
			$result_setting = "LIVE";
			$url = self::$_submitUrl;
		}
		
		if ($gateway->get('use_cvv2')) {
			//cvv2 = cvmvalue in linkpoint
			$cvv2 = "
				<cvmvalue>{$info['cvv2_code']}</cvmvalue>";
		} else {
			$cvv2 = "";
		}
		$amount = number_format($transaction->getAmount(),2,'.','');
		$exp_year = substr($info['exp_year'],2,2);
		$xml = "
		<order>
			<billing>
				<name>{$info['firstname']} {$info['lastname']}</name>
				<address1>{$info['address']}</address1>
				<address2>{$info['address_2']}</address2>
				<company>{$info['company_name']}</company>
				<city>{$info['city']}</city>
				<state>{$info['state']}</state>
				<zip>{$info['zip']}</zip>
				<country>{$info['country']}</country>
				<email>{$info['email']}</email>
				<phone>{$info['phone']}</phone>
			</billing>
			<orderoptions>
				<result>$result_setting</result>
				<ordertype>SALE</ordertype>
			</orderoptions>
			<merchantinfo>
				<configfile>".$gateway->get('store_number')."</configfile>
			</merchantinfo>
			<creditcard>
				<cardnumber>{$info['cc_number']}</cardnumber>
				<cardexpmonth>{$info['exp_month']}</cardexpmonth>
				<cardexpyear>{$exp_year}</cardexpyear>$cvv2
			</creditcard>
			<payment>
				<chargetotal>$amount</chargetotal>
			</payment>";
		
		if ($recurring) {
			//figure out start date to send, in format YYYYMMDD
			//or text IMMEDIATELY if starts now.
			$startDate = ($startDate == geoUtil::time())? 'IMMEDIATELY' : date('Ymd',$startDate);
			
			//figure out "periodicity"
			$days = $interval / (60 * 60 * 24);
			if ($days > 99) {
				trigger_error('ERROR TRANSACTION: Interval days is '.$days.' but Linkpoint must
				be less than 99 days.');
				return self::_failure($transaction,self::FAIL_GENERAL_ERROR,"Duration more than 99 days.");
			}
			$xml .= "
			<periodic>
				<startdate>{$startDate}</startdate>
				<installments>99</installments>
				<periodicity>d{$days}</periodicity>
				<action>SUBMIT</action>
				<threshold>3</threshold>
			</periodic>";
		}
		
		$xml .= "
		</order>";
		
		##  Create Connection to Gateway Here
		$cert = $gateway->get('cert_path');
		# use PHP built-in curl functions
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $xml); # the string we built above
		curl_setopt ($ch, CURLOPT_SSLCERT, $cert);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		if ($gateway->get('testing_mode')) {
			//// optional - verbose debug output
			// not for production use
			curl_setopt ($ch, CURLOPT_VERBOSE, 1);
		}
		
		$linkpoint_result = curl_exec ($ch);
		curl_close($ch);
		
		##  Process results of gateway here
		if (!$linkpoint_result) {
			//returned false, connection problem?
			$message = 'Unable to receive response from Linkpoint server, please report this error to the Site Admin.  Verify Gateway configuration, and host URL of '.$url.' and check for firewall/proxy issues.';
			trigger_error('ERROR TRANSACTION CART LINKPOINT: '.$message);
			return self::_failure($transaction,self::FAIL_GATEWAY_CONNECTION,$message);
		}
		//convert XML to an array - there is no embeded XML elements so can use simple routine
		preg_match_all ("/<(.*?)>(.*?)\</", $linkpoint_result, $outarr, PREG_SET_ORDER);
	
		$n = 0;
		$temp = "";
		$linkpoint = array();
		foreach ($outarr as $data) {
			$linkpoint[$data[1]] = $data[2];
		}
		trigger_error('DEBUG TRANSACTION CART '.self::gateway_name.': Raw results from linkpoint: '.$linkpoint_result.' - Processed: <pre>'.print_r($linkpoint_result,1).'</pre>');
		$transaction->set('linkpoint_response',$linkpoint_result);
		
		## Interpret the results here
		
		if ($linkpoint["r_approved"] == "APPROVED") {
			//TRANSACTION SUCCESSFUL!!
			trigger_error('DEBUG TRANSACTION CART '.self::gateway_name.': no errors, payment good!');
			
			if ($recurringItem) {
				die ("response: <pre>".print_r($linkpoint,1)."</pre><br /><strong>Raw:</strong><pre>".print_r($linkpoint_result,1));
				//Set the paidUntil date
				$paidUntil = $interval + geoUtil::time();
				$recurring->setPaidUntil((int)$paidUntil);
				$recurring->setStatus('active');
				$recurring->setSecondaryId($linkpoint['r_ordernum']);
			}
			
			//Let the parent do the common stuff for when the transaction was a success
			return self::_success($cart->order,$transaction, $gateway);
		} else if ($linkpoint['r_approved'] == "DECLINED") {
			//TRANSACTION FAILED!!!
			$message = 'Transaction Declined: Error message from Linkpoint: '.$linkpoint['r_error'].
						' Message Received (if any): '.$linkpoint['r_message'];
			
			trigger_error('ERROR TRANSACTION CART '.self::gateway_name.': '.$message);
			
			//Let the parent do the common stuff for when the transaction was a failure.
			//Linkpoint has no failure codes, they use messages which may change, so just
			//specify a general error.
			return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$message);
		} else if ($linkpoint['r_approved'] == 'BLOCKED') {
			//TRANSACTION BLOCKED!!!  (because of fraud)
			$message = 'Transaction Blocked: Error message from Linkpoint: '.$linkpoint['r_error'].
						' Message Received (if any): '.$linkpoint['r_message'];
			
			trigger_error('ERROR TRANSACTION CART '.self::gateway_name.': '.$message);
			
			//Let the parent do the common stuff for when the transaction was a failure.
			//Linkpoint has no failure codes, they use messages which may change, so just
			//specify a general error.
			return self::_failure($transaction,self::FAIL_DETECTED_FRAUD,$message);
		} else {
			//UNEXPECTED RESULT
			$message = 'Unexpected Results from Linkpoint, r_approved not approved, declined, or blocked!  Linkpoint Results: <pre>'.print_r($linkpoint,1).'</pre><br />raw:'.htmlspecialchars($linkpoint_result);
			
			trigger_error('ERROR TRANSACTION CART '.self::gateway_name.': '.$message);
			
			//Let the parent do the common stuff for when the transaction was a failure.
			//Linkpoint has no failure codes, they use messages which may change, so just
			//specify a general error.
			return self::_failure($transaction,self::FAIL_GATEWAY_CONNECTION,$message);
		}
	}
	
	
	public function isRecurring ()
	{
		return $this->get('recurring');
	}
	
	public function getRecurringAgreement ()
	{
		//TODO: TEXT
		return array ('label' => 'Check if you agree.', 'text' => 'Agreement text.');
	}
	
	public function recurringUpdateStatus ($recurring)
	{
		//Up to each gateway to implement
		//TODO: implement
	}
	
	/**
	 * Called to cancel the recurring billing, to stop payments.  
	 * Gateway should do whatever is needed to cancel the payment status, and
	 * update the details on the recurring billing.
	 * 
	 * @param geoRecurringBilling $recurring
	 * @param string $reason The reason for the recurring billing cancelation.
	 * @return bool Return true to say to cancel recurring payment, false to block
	 *  canceling the recurring payment.
	 */
	public function recurringCancel ($recurring, $reason = '')
	{
		//TODO: Implement
		//Up to each gateway to implement
		return true;
	}
	
}