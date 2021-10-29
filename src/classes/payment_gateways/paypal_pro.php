<?php
//payment_gateways/paypal_pro.php
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
## ##    7.4.4-28-g2f5cc34
## 
##################################

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

# Template CC payment gateway handler

class paypal_proPaymentGateway extends _ccPaymentGateway{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'paypal_pro';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'paypal_pro';
	
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
			'title' => 'CC - Paypal Pro',//how it's displayed in admin
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
	
	public function isRecurring ()
	{
		//yes we do handle recurring payments, thank you.
		return $this->get('recurring');
	}
	
	public function getRecurringAgreement ()
	{
		$msgs = DataAccess::getInstance()->get_text(true, 10203);
		if ($msgs[500739]) {
			return array ('label' => $msgs[500738], 'text' => $msgs[500739]);
		}
		return false;
	}
	
	public function recurringUpdateStatus ($recurring)
	{
		if (!$recurring) {
			//oops, what's going on?
			return false;
		}
		
		$recurringProfileId = $recurring->getSecondaryId();
		if (!$recurringProfileId) {
			//can't do anything without the profile id
			return;
		}
		
		$profile = $this->_initPaymentProfile();
		if (!$profile) {
			//what?  something went wrong, settings wrong or something
			trigger_error('ERROR RECURRING: could not init paypal pro profile.');
			return;
		}
		
		//--------------------------------------------------
		$recurring_details_request = PayPal::getType('GetRecurringPaymentsProfileDetailsRequestType');
		if (PayPal::isError($recurring_details_request)) {
			//oops
			return;
		}
		$recurring_details_request->setVersion('51.0');
		$recurring_details_request->setProfileId($recurringProfileId);
		
		$caller = PayPal::getCallerServices($profile);
		
		$response = $caller->GetRecurringPaymentsProfileDetails($recurring_details_request);
		if (PayPal::isError($response)) {
			//connection failed
			//echo 'Connection failed? <pre>'.print_r($response,1).'</pre>';
			return;
		}
		switch($response->getAck()) {
			case 'Success':
			case 'SuccessWithWarning':
				// Extract the parameters
				$recurringResponse = $response->getGetRecurringPaymentsProfileDetailsResponseDetails();
				
				$status = $recurringResponse->getProfileStatus();
				if ($status !== 'ActiveProfile') {
					//that's not good! Don't do any updates
					if ($status) {
						if ($status === 'CancelledProfile' || $status === 'SuspendedProfile') $status = geoRecurringBilling::STATUS_CANCELED;
						$recurring->cancel('Not Active');
						$recurring->save();
					}
					return;
				}
				
				//figure out how long this is paid until
				$recurringPaymentSummary = $recurringResponse->getRecurringPaymentsSummary();
				
				$nextBillingDate = $recurringPaymentSummary->getNextBillingDate();
				$lastPaymentDate = $recurringPaymentSummary->getLastPaymentDate();
				
				$stillDueBasic = $recurringPaymentSummary->getOutstandingBalance();
				
				//Wow I feel dirty accessing the value like this, but I don't see another way...
				$stillDue = $stillDueBasic->_value;
				$stillDue = number_format($stillDue,2,'.','');
				
				$starts = null;
				if (!$lastPaymentDate && $stillDue == 0.00) {
					//see when it starts
					$details = $recurringResponse->getRecurringPaymentsProfileDetails();
					$starts = $details->getBillingStartDate();
				}
				
				if ($stillDue == 0.00 && $nextBillingDate && ($lastPaymentDate || $starts)) {
					//nothing due right now, can rely on it
					//we know we're good until the next billing date
					$paidUntil = strtotime($nextBillingDate);
					//Note: Even if billing has not started yet, in that case 
					//nextbillingdate will be the first billing date, so can be
					//used as the paidUntil.
					if ($paidUntil > geoUtil::time()) {
						//only bother updating if it's paid up
					
						//echo "next billing date: $nextBillingDate<br />paid until: $paidUntil<br />last payment: $lastPaymentDate<br />";
						$recurring->setPaidUntil($paidUntil);
						$recurring->setStatus('active');
						$recurring->save();
					}
				}
				
				//echo "GetRecurringPaymentsProfileDetailsRequestType Completed Successfully, status : $status debug: <br /><pre>" . print_r($stillDueBasic, true).'</pre><br />';
				break;
				
			default:
				//connection error
				//exit('GetBalance failed: ' . print_r($response, true));
				
		}
	}
	
	/**
	 * Called to cancel the recurring billing, to stop payments.  Gateway should
	 * do whatever is needed to cancel the payment status.
	 * 
	 * @param geoRecurringBilling $recurring
	 */
	public function recurringCancel ($recurring, $reason = '')
	{
		if (!$recurring) {
			//oops, what's going on?
			trigger_error('ERROR RECURRING: recurring object not valid.');
			return false;
		}
		$reason = trim($reason);
		
		$recurringProfileId = $recurring->getSecondaryId();
		if (!$recurringProfileId) {
			//can't do anything without the profile id
			trigger_error('ERROR RECURRING: Could not retrieve recurring profile ID to process cancelation.');
			return false;
		}
		$profile = $this->_initPaymentProfile();
		if (!$profile) {
			//sanity check, must have something wrong with gateway's settings
			trigger_error('ERROR RECURRING: Could not init payment gateway profile, settings must be wrong or something.');
			return false;
		}
		
		//first set up the details
		$details = PayPal::getType('ManageRecurringPaymentsProfileStatusRequestDetailsType');
		if (PayPal::isError($details)) {
			//oops
			trigger_error('ERROR RECURRING: Manage request was error.');
			return false;
		}
		$details->setProfileId($recurringProfileId);
		$details->setAction('Cancel');
		if ($reason) {
			$details->setNote($reason);
		}
		$manageRequest = PayPal::getType('ManageRecurringPaymentsProfileStatusRequestType');
		if (PayPal::isError($manageRequest)) {
			//oops
			trigger_error('ERROR RECURRING: Manage request was error.');
			return false;
		}
		$manageRequest->setManageRecurringPaymentsProfileStatusRequestDetails($details);
		
		$caller = PayPal::getCallerServices($profile);
		if (PayPal::isError($caller)) {
			//oops
			return false;
		}
		$response = $caller->ManageRecurringPaymentsProfileStatus($manageRequest);
		//echo 'Connection failed? <pre>'.print_r($caller,1).'</pre>';
		if (PayPal::isError($response)) {
			//connection failed
			trigger_error('ERROR RECURRING: Connection failed? Debug: Response object: <pre>'.print_r($response,1).'</pre>');
			return false;
		}
		switch($response->getAck()) {
			case 'Success':
			case 'SuccessWithWarning':
				// Extract the parameters
				$recurringResponse = $response->getManageRecurringPaymentsProfileStatusResponseDetails();
				
				$returnedProfileId = $recurringResponse->getProfileId();
				//Update any recurring vars that need updating
				
				//echo "Completed Successfully, profile ID returned : $returnedProfileId";
				//it was successful, return true
				trigger_error('DEBUG RECURRING: cancelation seems to be successful, returning true!');
				return true;
				
				break;
				
			default:
				//connection error
				//exit('GetBalance failed: ' . print_r($response, true));
				trigger_error('ERROR RECURRING: Response returned error: '.$response->getAck());
				
				//failed, return false
				return false;
				break;
				
		}
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
		$tpl->assign('requirements', $this->requirement_check());
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(true, true));
		
		$tooltips['api_username'] = geoHTML::showTooltip('Paypal API Username', "Your API Username, which is automatically generated by Paypal when you apply for a digital certificate to use the PayPal Web Services API. You can see this value on https://www.paypal.com in your profile under <strong>API Access > API Certificate Information");
		$tooltips['api_password'] = geoHTML::showTooltip('Paypal API Password', "Your API Password, which you specify when you apply for a digital certificate to use the PayPal Web Services API.");
		$tooltips['certfile'] = geoHTML::showTooltip("Path to Certificate", "The FULL path (including the file name) to your PayPal-issued digital certificate. To ensure security for your customers and your business, a public certificate and private key issued by PayPal are required for use of the PayPal Web Services API. The certificate file is in PEM format and contains both your private key and your public certificate. To obtain a PayPal Web Services API username, password, and digital certificate, you must first create a Business or Premier account and apply online at <b>https://www.paypal.com.</b>");
		$tooltips['recommended'] = geoHTML::showTooltip("Recommended Path","For convenience, a directory is already set aside for your PayPal-issued digital certificate.  After placing the certificate in the recommended directory listed to the right, copy and paste the file path in the text box above and make sure the name of the file at the end of the file path matches the name of your PayPal-issued digital certificate.");
		$tooltips['currency_id'] = geoHTML::showTooltip("PayPal Currency Codes","PayPal-Supported Currencies and their Maximum Transaction Amounts<br><b>AUD</b> Australian Dollar 12,500 AUD<br><b>CAD</b> Canadian Dollar 12,500 CAD<br><b>EUR</b> Euro 8,000 EUR<br><b>GBP</b> Pound Sterling 5,500 GBP<br><b>JPY</b> Japanese Yen 1,000,000 JPY<br><b>USD</b> U.S. Dollar 10,000 USD<br>You must manually set any ");
		$tooltips['charset'] = geoHTML::showTooltip("Character Set","A character set is a computer representation of all the individual possible letterforms or word symbols of a language. Listed are PayPal-Supported character sets:<br><b>ISO 8859-1</b> - West European languages (Latin-1) ISO 8859-1 is currently the most widely used.<br><b>US ASCII</b> - This set of 128 English characters were established by ANSI X3.4-1986 and is slowly being phased out due to it's limitations to the English language.<br><b>UTF-8</b> - Unicode Transformation Format-8. It is an octet (8-bit) lossless encoding of Unicode characters.");
		$tooltips['required_fields'] = geoHTML::showTooltip("Required Fields","Paypal requires these fields to be sent along with the credit card number and expiration date.  Therefore you must require these variables during the registration process.");
		
		$values['api_username'] = $this->get('api_username');
		$values['api_password'] = $this->get('api_password');
		$values['certfile'] = $this->get('certfile');
		$values['signature'] = $this->get('signature');
				
		$recommendedPath = GEO_BASE_DIR."classes/PEAR/PayPal/cert/cert_key_pem.txt";
		$values['recommended'] = $recommendedPath;
		
		$values['currency_id'] = $this->get('currency_id', "USD");
		$values['charset'] = $this->get('charset', "utf-8");
		
		$tooltips['max_failed_payments'] = geoHTML::showTooltip('Max Failed Recurring Payments', 'Paypal will allow this many recurring payments to fail before it automatically suspends the recurring profile.');
		$values['max_failed_payments'] = $this->get('max_failed_payments',1);
		
		$tpl->assign('tooltips', $tooltips);
		$tpl->assign('values', $values);
		return $tpl->fetch('payment_gateways/paypal_pro.tpl');
	}
	
	/**
	 * Function that checks for requirements needed by the PayPal Websites Payment Pro
	 * 
	 * @return String Returns string of HTML that displays the results of the test.
	 */
	private function requirement_check(){

		$results = array();

		//These are the pass or fail messages to display.
		$pass="\n".'<li><span style="color:green;">PASSED</span>';
		$fail = "\n".'<li><span style="color:red;">FAILED</span>';

		//Check that perl compatible regular expression extension is installed
		@ preg_match('[est]', 'This is a simple Test', $regtest);

		if ($regtest[0]=='est'){
			$results[0.5]=true;
		}
		//Check for cURL
		if (@ function_exists('curl_version') ){
			$results[1]=true;
		}
		//check for openSSL
		if (@ function_exists ('openssl_sign')){
			$results[1.5] = true;
		}
		//Check for main PEAR libraries
		if (geoUtil::includePEAR('PEAR.php')) {
			$results[2]=true;
			//check for Net_URL
			if (geoUtil::includePEAR('Net/URL.php')) {
				$results[3]=true;
			}
			//check for Net_Socket
			if (geoUtil::includePEAR('Net/Socket.php')){
				$results[4]=true;
			}
			//check for HTTP_Request
			if (geoUtil::includePEAR('HTTP/Request.php')){
				$results[5]=true;
			}
			//Check for Log
			if (geoUtil::includePEAR('Log.php')){
				$results[6]=true;
			}
		}

		//Now output the results in a nice way.
		
		$return_string = "1.Enable the following extensions:".geoHTML::showTooltip("Enabling Extensions","If you are unsure how to enable any of these extensions, see your server administrator.");
		$return_string .= "<ul>";

		//check regular expression extension
		if ($results[0.5]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}
		$return_string .= ' -- PHP Perl Compatible Regular Expressions extension for PHP 4.3.0+ and higher</li>';

		if ($results[1]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}

		$return_string .= "\n -- PHP cURL extension for PHP 4.3.0+ and higher with SSL support</li>";

		//openssl test
		if ($results[1.5]){
			$return_string .= $pass;
		}
		else {
			$return_string .= $fail;
		}
		$return_string .= ' -- PHP OpenSSL extension for PHP 4.3.0+ and higher (for digital certificate transcoding)</li>';

		//test for pear libraries
		$return_string .= "</ul>2. Ensure that PEAR has the following required packages:".geoHTML::showTooltip("Installing PEAR packages","If you are unsure how to install any of these packages, see your server administrator.");
		$return_string .= "<ul>";

		if ($results[2]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}
		$return_string .= " -- Main PEAR Library</li>";
		//check rest of pear stuff here

		//check net_URL
		if ($results[3]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}
		$return_string .= " -- PEAR Net_URL</li>";

		//check net_socket
		if ($results[4]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}
		$return_string .= " -- PEAR Net_Socket</li>";

		//check HTTP_Request
		if ($results[5]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}
		$return_string .= " -- PEAR HTTP_Request</li>";

		//check Log
		if ($results[6]){
			$return_string .= $pass;
		} else {
			$return_string .= $fail;
		}
		$return_string .= " -- PEAR Log</li></ul>";

		return $return_string;
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
		if (isset($_POST['paypal_pro']) && is_array($_POST['paypal_pro']) && count($_POST['paypal_pro']) > 0){
			$settings = $_POST['paypal_pro'];
			$this->_updateCommonAdminOptions($settings, true);
			$this->set('api_username',trim($settings['api_username']));
			$this->set('api_password',trim($settings['api_password']));
			$certfile = ($settings['certfile'])? trim($settings['certfile']): false;
			$sig = ($settings['signature'])? trim($settings['signature']): false;
			if ($certfile && $sig) {
				geoAdmin::m('Cannot use both the API Signature AND the api cert file as only
					one or the other is used for authentication.', geoAdmin::ERROR);
				
			} else {
				$this->set('certfile',$certfile);
				$this->set('signature',$sig);
			}
			
			$this->set('currency_id',$settings['currency_id']);
			$this->set('charset',$settings['charset']);
			//always use cvv2 code
			$this->set('use_cvv2', true);
			$this->set('max_failed_payments', $settings['max_failed_payments']);
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
		
		//figure out if this is a recurring billing
		$recurringItem = ($gateway->isRecurring() && $cart->isRecurringCart())? $cart->item : false;
		
		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $invoice->getInvoiceTotal();
		
		if ($invoice_total >= 0 && !$recurringItem){
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}
		if ($recurringItem) {
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
		
		//BUILD DATA TO SEND TO GATEWAY TO COMPLETE THE TRANSACTION
		$info = parent::_getInfo();
		
		//create initial transaction
		try {
			//let parent create a new transaction, since it does all that common stuff
			//for us.
			$transaction = self::_createNewTransaction($cart->order,$gateway, $info);
			
			//Add the transaction to the invoice
			$invoice->addTransaction($transaction);
			
			if ($recurringItem) {
				//add the transaction to the recurring billing object too
				$recurring->addTransaction($transaction);
			}
			
			//save it so there is an id
			$transaction->save();
		} catch (Exception $e){
			//catch any error thrown by _createNewTransaction
			trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: Exception thrown when attempting to create new transaction.');
			return;
		}
		
		//******************************************************************************
		// PROCESS TRANSACTION HERE
		
		$cc_number = $info["cc_number"];
		$cvv2_code = $info["cvv2_code"];	// 123
		$cc_exp_month = $info['exp_month'];
		$cc_exp_year = $info['exp_year'];
		
		$currency_id = $gateway->get("currency_id");
		$charset = $gateway->get('charset');
		$transactionAmount = number_format($transaction->getAmount(),2,'.','');  // Example to force certain format for amount
		
		// Billing Details Example
		$fname = $info['firstname'];
		$lname = $info['lastname'];
		$email = (geoString::isEmail($info['email']))? $info['email']: false;
		//$street = "{$info['address']} {$info['address_2']}";
		$street1 = $info['address'];
		$city = $info['city'];
		$state = $info['state'];
		$zip = $info['zip'];
		$country = $info['country'];
		
		// Other information
		$ip = $_SERVER['REMOTE_ADDR'];
		
		//*****************************************************************************
		//SELLER'S DATA
		
		$profile = $gateway->_initPaymentProfile();
		if (!$profile) {
			return self::_failure($transaction,self::FAIL_CHECK_GATEWAY_SETTINGS,$additional_msg);
		}
		
		$Address = PayPal::getType('AddressType');
		if (PayPal::isError($Address)) {
			return self::_failure($transaction,self::FAIL_CHECK_GATEWAY_SETTINGS,$additional_msg);
		}
		
		//*****************************************************************************
		//BUYER'S DATA
		$basic_amount = PayPal::getType('BasicAmountType');
		if (PayPal::isError($basic_amount)) {
			return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
		}
		$basic_amount->setattr('currencyID', $currency_id);
		$basic_amount->setval($transactionAmount, $charset);
		if ($recurringItem) {
			$startDate = $recurringItem->getRecurringStartDate();
			$startDate = ($startDate && $startDate > geoUtil::time())? (int)$startDate: geoUtil::time();
			
			$recurring->setStartDate($startDate);
			
			$startDate = date(DATE_ATOM, $startDate);//"2009-9-6T0:0:0"
			
			$billingPeriod = "Day";				// or "Month", "Day", "Week", "SemiMonth", "Year"
			$days = ceil($interval/(60*60*24));
			$billingFreq = $days;
			$recurringAmount = number_format($recurringAmount,2,'.','');
			//$recurringAmount = $recurringAmount;
			//Make it set initial amount and recurring amount seperately,
			//if the 2 are different
			if ($recurringAmount != $transactionAmount) {
				//Can't do it this way, it causes a connection timeout with paypal every
				//single time for some reason
				
				/*
				//set basic amount to the recurring value
				$basic_amount->setval($recurringAmount, $charset);
				//now set us up activation details, this should be the initial
				//charge
				
				$activation_details = PayPal::getType('ActivationDetailsType');
				$activation_details->setInitialAmount($transaction_amount);
				*/
			}
		} else {
			$PaymentDetails = PayPal::getType('PaymentDetailsType');
			if (PayPal::isError($PaymentDetails)) {
				return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
			}
			$PaymentDetails->setOrderTotal($basic_amount);
		}
		$PayerName = PayPal::getType('PersonNameType');
		if (PayPal::isError($PayerName)) {
			return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
		}
		$PayerName->setLastName($lname, $charset);
		$PayerName->setFirstName($fname, $charset);
		$CardOwner = PayPal::getType('PayerInfoType');
		if (PayPal::isError($CardOwner)) {
			return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
		}
		$Address->setPostalCode($zip, $charset);
		$Address->setCountry($country, $charset);
		$Address->setStateOrProvince($state, $charset);
		$Address->setCityName($city, $charset);
		$Address->setStreet1($street1, $charset);
		$CardOwner->setAddress($Address);
		$CardOwner->setPayerCountry($country, $charset);
		$CardOwner->setPayerName($PayerName);
		if ($email) {
			//payer = payer's e-mail (no wonder it wasn't used before, stupid name)
			$CardOwner->setPayer($email, $charset);
		}
		
		$CreditCard = PayPal::getType('CreditCardDetailsType');
		if (PayPal::isError($CreditCard)) {
			return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
		}
		
		$cc_type = self::getCardType($cc_number);
		//convert it into a string paypal understands
		switch ($cc_type) {
			case self::CARD_TYPE_AMEX:
				$cc_type = 'Amex';
				break;
				
			case self::CARD_TYPE_DISCOVER:
				$cc_type = 'Discover';
				break;
				
			case self::CARD_TYPE_MC:
				$cc_type = 'MasterCard';
				break;
				
			case self::CARD_TYPE_VISA:
				$cc_type = 'Visa';
				break;
				
			default:
				//unknown type, can't process
				return self::_failure($transaction,self::FAIL_INVALID_CC_INFO,$additional_msg);
				break;
				
		}
		
		$CreditCard->setCreditCardType($cc_type);
		
		$CreditCard->setCardOwner($CardOwner);
		$CreditCard->setExpYear($cc_exp_year, $charset);
		$CreditCard->setExpMonth($cc_exp_month, $charset);
		$CreditCard->setCreditCardNumber($cc_number, $charset);
		$CreditCard->setCVV2($cvv2_code, $charset);

		//******************************************************************************
		//PROCESS DATA
		$caller = PayPal::getCallerServices($profile);
		if (PayPal::isError($caller)) {
			//ERROR here
			return self::_failure($transaction,self::FAIL_CHECK_GATEWAY_SETTINGS,$additional_msg);
		}
		//PROXY SERVER SETTINGS - SET THE PROXY URL AND UNCOMMENT
		//$caller->setOpt('curl', CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		//$caller->setOpt('curl', CURLOPT_PROXY, 'http://11.11.11.11:1111');
		//MAY OR MAY NOT BE NEEDED - CHOOSE TRUE OR FALSE AND SET THE 'time' BEFORE UNCOMMENTING
		//$caller->setOpt('curl', CURLOPT_SSL_VERIFYPEER, TRUE/FALSE);
		//$caller->setOpt('curl', CURLOPT_STATSOUT, time);
		
		if ($recurringItem) {
			//recurring billing
			$recurring_payments_request = PayPal::getType('CreateRecurringPaymentsProfileRequestType');
			$recurring_payments_request->setVersion("51.0");
			
			$recurring_payments_request_details = PayPal::getType('CreateRecurringPaymentsProfileRequestDetailsType');
			//$recurring_payments_request_details->setToken($token);
			$recurring_payments_request_details->setCreditCard($CreditCard);
			
			$recurring_payments_details = PayPal::getType('RecurringPaymentsProfileDetailsType');
			$recurring_payments_details->setBillingStartDate($startDate);
			
			$recurring_payments_request_details->setRecurringPaymentsProfileDetails($recurring_payments_details);
			
			$schedule_details = PayPal::getType('ScheduleDetailsType');
			
			$billing_period_details = PayPal::getType('BillingPeriodDetailsType');
			if (PayPal::isError($billing_period_details)) {
				return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
			}
			$billing_period_details->setBillingPeriod($billingPeriod);
			$billing_period_details->setBillingFrequency($billingFreq);
			
			$billing_period_details->setAmount($basic_amount);
			
			$schedule_details->setPaymentPeriod($billing_period_details);
			
			$schedule_details->setDescription($recurringItem->getRecurringDescription());
			if (isset($activation_details) && $activation_details) {
				//This is currently not working
				$schedule_details->setActivationDetails($activation_details, $charset);
			}
			
			$schedule_details->setMaxFailedPayments($gateway->get('max_failed_payments',1));
						
			$recurring_payments_request_details->setScheduleDetails($schedule_details);
			
			$recurring_payments_request->setCreateRecurringPaymentsProfileRequestDetails($recurring_payments_request_details);
			
			// Execute SOAP request.
			$response = $caller->CreateRecurringPaymentsProfile($recurring_payments_request);
		} else {
			//direct payment
			$DoDirectPaymentRequestDetails = PayPal::getType('DoDirectPaymentRequestDetailsType');
			if (PayPal::isError($DoDirectPaymentRequestDetails)) {
				return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
			}
			$DoDirectPaymentRequestDetails->setIPAddress($ip, $charset);
			$DoDirectPaymentRequestDetails->setCreditCard($CreditCard);
			$DoDirectPaymentRequestDetails->setPaymentDetails($PaymentDetails);
			$DoDirectPaymentRequestDetails->setPaymentAction('Sale', $charset);
			$DoDirectPayment = PayPal::getType('DoDirectPaymentRequestType');
			if (PayPal::isError($DoDirectPayment)) {
				return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$additional_msg);
			}
			$DoDirectPayment->setDoDirectPaymentRequestDetails($DoDirectPaymentRequestDetails);
			
			//make the connection and get a response
			$response = $caller->DoDirectPayment($DoDirectPayment);
		}
		
		if (PayPal::isError($response)) {
			//connection failed
			return self::_failure($transaction,self::FAIL_GATEWAY_CONNECTION,$additional_msg);
		}
		$result = $response->getAck();
		if ($result == 'Success' || $result == 'SuccessWithWarning') {
			//got here, it was successful!
			// Extract the response details.
			if ($recurringItem) {
				$recurring_payments_response_details = $response->getCreateRecurringPaymentsProfileResponseDetails();
				$profileID = $recurring_payments_response_details->getProfileID();
				$recurring->setSecondaryId($profileID);
				//Set the paidUntil date
				$paidUntil = $interval + geoUtil::time();
				$recurring->setPaidUntil((int)$paidUntil);
				$recurring->setStatus('active');
			}
			//record the paypal transaction ID so it is easy for the admin to
			//find an associated transaction based on transaction ID in paypal
			//NB: $response->getCorrelationID() is more generic, and may be a suitable substitute for both of the below calls
			$transactionId = ($recurringItem) ? $recurring_payments_response_details->getTransactionId() : $response->getTransactionId();
			if ($transactionId) {
				$transaction->setGatewayTransaction($transactionId);
			}
			return self::_success($cart->order,$transaction, $gateway);
		} else {
			//Error processing
			if (is_array($response->Errors)) {
				foreach ($response->Errors as $value) {
					$error_short_msg .= $value->ShortMessage.", ";
					$error_long_msg .= $value->LongMessage.", ";
					$error_code .= $value->ErrorCode.", ";
					$severity_code .= $value->SeverityCode.", ";
				}
			} else {
					$error_short_msg = $response->Errors->ShortMessage;
					$error_long_msg = $response->Errors->LongMessage;
					$error_code = $response->Errors->ErrorCode;
					$severity_code = $response->Errors->SeverityCode;
			}
			if ($response->getAck()||$error_long_msg) {
				$handler_error_response = $response->getAck()."<br>".$error_long_msg;
			} else {
				$handler_error_response =  "INTERNAL FAILURE";
			}
			return self::_failure($transaction,self::FAIL_GENERAL_ERROR,$handler_error_response);
		}
	}
	/**
	 * Used internally to do the most common parts of creating a connection to
	 * paypal, and return the $profile needed to process things.
	 * @return APIProfile|bool If failure, it returns false (such as settings
	 *   set incorrectly)
	 */
	public function _initPaymentProfile ()
	{
		geoUtil::includePEAR('PayPal.php', true);
		geoUtil::includePEAR('PayPal/Profile/Handler/Array.php');
		geoUtil::includePEAR('PayPal/Profile/API.php');
		
		$environment = ($this->get('testing_mode'))? 'sandbox' : 'live';	// or 'beta-sandbox' or 'live'
		$apiusername = $this->get('api_username');
		$apipassword = $this->get('api_password');
		$certfile = $this->get('certfile',null);
		$sig = $this->get('signature');
		//--------------------------------------------------
		// PROFILE
		//--------------------------------------------------
		/**
		 *                    W A R N I N G
		 * Do not embed plaintext credentials in your application code.
		 * Doing so is insecure and against best practices.
		 *
		 * Your API credentials must be handled securely. Please consider
		 * encrypting them for use in any production environment, and ensure
		 * that only authorized individuals may view or modify them.
		 */
		
		$handler = ProfileHandler_Array::getInstance(array(
		            'username' => $apiusername,
		            'certificateFile' => $certfile,
		            'subject' => null,
		            'environment' => $environment));
		if (PayPal::isError($handler)) {
			return false;
		}
		
		$pid = ProfileHandler::generateID();
		
		$profile = new APIProfile($pid, $handler);
		if (PayPal::isError($profile)) {
			return false;
		}
		// Set up your API credentials, PayPal end point, and API version.
		$profile->setAPIUsername($apiusername);
		$profile->setAPIPassword($apipassword);
		if ($sig) {
			$profile->setSignature($sig);
		} else {
			$profile->setCertificateFile($certfile);
		}
		
		$profile->setEnvironment($environment);
		
		return $profile;
	}
}