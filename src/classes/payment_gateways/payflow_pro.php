<?php
//payment_gateways/payflow_pro.php
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

# Payflow Pro gateway handler

class payflow_proPaymentGateway extends _ccPaymentGateway{
	public $name = 'payflow_pro';
	const gateway_name = 'payflow_pro';
	
	private static $_submitUrlTesting = 'https://pilot-payflowpro.paypal.com';
	private static $_submitUrl = 'https://payflowpro.paypal.com';
	private static $_currencies = array(
			'USD' => 'US Dollar',
			'EUR' => 'Euro',
			'GBP' => 'UK pound',
			'CAD' => 'Canadian dollar',
			'JPY' => 'Japanese Yen',
			'AUD' => 'Australian dollar'
		);
	
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
			'title' => 'CC - Payflow Pro',//how it's displayed in admin
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
			</script>"
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
	public function admin_custom_config (){
		
		$tpl = new geoTemplate('admin');
		$tpl->assign('payment_type', self::gateway_name);
		
		$tpl->assign('tooltips', $tooltips);
		
		//TODO: encrypt payflow pro user data
		
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());
		$tooltips['partner'] = geoHTML::showTooltip('Partner','This is the Verisign partner. The default value (VeriSign) should be used for the test account, or if you signed up yourself for your account.');
		$tooltips['vendor'] = geoHTML::showTooltip('Vendor','This is your vendor name, defined at registration time at Verisign.');
		$tooltips['user'] = geoHTML::showTooltip('User','This is your user name, defined at registration time at Verisign.  If you do not place anything in this box, then the Vendor name will be used.');
		$tooltips['password'] = geoHTML::showTooltip('Password','This is your password, defined at registration time at Verisign.');
		$tooltips['testing_cc'] = geoHTML::showTooltip('Test CC Numbers','Use these cc numbers to test transactions within the demo mode.');
		$tooltips['testing_cc_reactions'] = geoHTML::showTooltip('Test CC Reactions','If you want to test the credit card transaction results use the amount testing mechanism listed here.');
		$tpl->assign('tooltips', $tooltips);
		
		$values['partner'] = geoString::specialChars($this->get('partner'));
		$values['vendor'] = geoString::specialChars($this->get('vendor'));
		$values['user'] = geoString::specialChars($this->get('user'));
		$values['password'] = geoString::specialChars($this->get('password'));
		$tpl->assign('values', $values);
		
		$currency = $this->get('currency');
		if (!$currency){
			$this->set('currency','USD');
			$currency = 'USD';
		}
		//Valid currencies accepted by payflow pro, currency_code => currency name
		
		foreach (self::$_currencies as $code => $name){
			$selected = (($currency == $code)? ' selected="selected"': '');
			$currency_options .= "
				<option value='$code'$selected>$code ($name)</option>";
		}
		$tpl->assign('currency_options', $currency_options);
		
		return $tpl->fetch('payment_gateways/payflow_pro.tpl');
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
		if (isset($_POST['payflow_pro']) && is_array($_POST['payflow_pro']) && count($_POST['payflow_pro']) > 0){
			$settings = $_POST['payflow_pro'];
			$this->_updateCommonAdminOptions($settings);
			//echo 'yo'.print_r($settings,1);
			$this->set('partner',trim($settings['partner']));
			$this->set('vendor', trim($settings['vendor']));
			$this->set('user',trim($settings['user']));
			$this->set('password',trim($settings['password']));
			$this->set('currency',((array_key_exists($settings['currency'],self::$_currencies))? $settings['currency']: 'USD'));
			$this->set('use_cvv2',1);//don't give option, payflow pro requires cvv2 code.
			
			$this->serialize();
		}
		return true;
	}
	public static function geoCart_payment_choicesDisplay ($gateway=null)
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		return parent::geoCart_payment_choicesDisplay($gateway);
	}
	
	public static function geoCart_payment_choicesCheckVars ($gateway=null, $skip_checks=null)
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		return parent::geoCart_payment_choicesCheckVars($gateway);
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
		trigger_error('DEBUG TRANSACTION PAYFLOW_PRO: Top of '.self::gateway_name.': Classified_sell_transaction_approved() - processing');
		
		$cart = geoCart::getInstance();

		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		
		
		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $due = $invoice->getInvoiceTotal();
		
		if ($due >= 0){
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}
		//BUILD DATA TO SEND TO PAYPAL TO COMPLETE THE TRANSACTION
		$info = parent::_getInfo();
		
		//create initial transaction
		try {
			$transaction = self::_createNewTransaction($cart->order, $gateway, $info);
			$transaction->setInvoice($invoice);
			$invoice->addTransaction($transaction);
			
			//save it so there is an id
			$transaction->save();
		} catch (Exception $e){
			trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: Exception thrown when attempting to create new transaction.');
			return;
		}
		
		//******************************************************************************
		//NEW PAYFLOW PRO CODE THAT WORKS THROUGH PAYPAL.COM
		
		$payflowUser = $gateway->get("user");
		$vendor = $gateway->get("vendor");
		$partner = $gateway->get("partner");
		$password = $gateway->get("password");
		
		
		//URL TO SUBMIT CURL TRANSACTIONS TO
		$submiturl = ($gateway->get("testing_mode") == 1)? self::$_submitUrlTesting : self::$_submitUrl;
		
		
		//dashes and spaces already removed from cart number
		$card_num = $info["cc_number"];
		$cvv2 = $info["cvv2_code"];	// 123
		$month = $info['exp_month'];
		if ($month < 10){
			//make sure it is 2 digits
			$month = '0' . intval($month);
		}
		$expiry = $month.substr($info['exp_year'], 2);  // We only use a 2-digit year.  Need this due to bug in PHP on the date function.
		$amount = number_format($transaction->getAmount(),2,'.','');  // Payflow requires decimal to be specified, so make sure it has decimal and 2 digits
		
		$currency = $gateway->get('currency');
		if (!array_key_exists($currency,self::$_currencies)){
			//failsafe to make sure currency is set to valid value
			$gateway->set('currency','USD');
			$gateway->save();
			$currency = 'USD';
		}
		// Billing Details
		$fname = urlencode($info['firstname']);
		$lname = urlencode($info['lastname']);
		//FIXME: Verify e-mail address (probably in check_vars)
		$email = urlencode($info['email']);
		$street = urlencode("{$info['address']} {$info['address_2']}");
		$city = urlencode($info['city']);
		$state = urlencode($info['state']);
		$zip = urlencode($info['zip']);
		$country = urlencode($info['country']);	// 3-digits ISO code
		// Other information
		$ipaddr = $_SERVER['REMOTE_ADDR'];
		if ($show_payflow_pro["testing_mode"] == 1){
			$custom = urlencode('Testing Only');
		}
		$transaction_id = $transaction->getId();
		$user = geoUser::getUser($cart->order->getBuyer());
		
		if (!is_object($user)) {
			//anonymous listing
			$user = 'Anonymous, E-mail: '.(($info['email'])? $info['email']: 'n/a');
		} else {
			$user = $user->username.' ('.$user->id.') E-mail: '.$user->email;
			if ($info['email'] && $info['email'] != $user->email) {
				$user .= " Billing E-mail: {$info['email']}";
			}
		}
		
		
		
		$desc = urlencode('Order ID#'.$cart->order->getId().' - User: '.$user);//so that admin can reference back to what order it was for
		$data = "USER=$payflowUser&VENDOR=$vendor&PARTNER=$partner&PWD=$password";	
			
		// C - Direct Payment using credit card, P - Express Checkout using PayPal account
		$data .= '&TENDER=C';
		// A - Authorization, S - Sale
		$data .= '&TRXTYPE=S';		
		$data .= "&ACCT=$card_num&CVV2=$cvv2&EXPDATE=$expiry&AMT=$amount&CURRENCY=$currency";
		$data .= "&FIRSTNAME=$fname&LASTNAME=$lname&STREET=$street&CITY=$city&STATE=$state&ZIP=$zip&COUNTRY=$country";
		$data .= "&EMAIL=$email&CUSTIP=$ipaddr&COMMENT1=$desc&COMMENT2=$custom&INVNUM=$transaction_id&ORDERDESC=$desc";
		// Transaction results (especially values for declines and error conditions) returned by each PayPal-supported
		// processor vary in detail level and in format. The Payflow Verbosity parameter enables you to control the kind
		// and level of information you want returned. 
		// By default, Verbosity is set to LOW. A LOW setting causes PayPal to normalize the transaction result values. 
		// Normalizing the values limits them to a standardized set of values and simplifies the process of integrating 
		// the Payflow SDK.
		// By setting Verbosity to MEDIUM, you can view the processor????????s raw response values. This setting is more ???????verbose????????
		// than the LOW setting in that it returns more detailed, processor-specific information. 
		// Review the chapter in the Developer's Guides regarding VERBOSITY and the INQUIRY function for more details.
		// Set the transaction verbosity to MEDIUM.
		$data .= '&VERBOSITY=MEDIUM';
		    
		// The $order_num field is storing our unique id that we'll use in the request id header.  By storing the id
		// in this manner, we are able to allowing reposting of the form without creating a duplicate transaction.
		$unique_id = $transaction->getId(); 
		
		// get data ready for API
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$headers[] = "Content-Type: text/namevalue"; //or text/xml if using XMLPay.
		// Here I set the server timeout value to 45, but notice below in the cURL section, I set the timeout
		// for cURL to 90 seconds.  You want to make sure the server timeout is less, then the connection.
		$headers[] = "X-VPS-Timeout: 45";
		$headers[] = "X-VPS-Request-ID:$unique_id";
		   	
		// Optional Headers.  If used adjust as necessary.
		$headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  	// What you are using
		
		//remember the data and headers sent for debugging
		$transaction->set('data_sent',$data);
		$transaction->set('headers_sent',$headers);
		
		//TODO: Remove this and all echo'd text once payflow pro is verified to work
		// (replace with use of trigger_error for future debugging)
		if ($gateway->get("testing_mode")) {
			echo $data;
			echo '<br><br>';
			echo '<h4>Processing order</h4>';
		}
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, $submiturl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		curl_setopt($ch, CURLOPT_HEADER, 1); 		// tells curl to include headers in response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 	// return into a variable
		curl_setopt($ch, CURLOPT_TIMEOUT, 90); 		// times out after 90 secs
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 	// this line makes it work under https
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 	//adding POST data
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); 	//verifies ssl certificate
		curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); 	//forces closure of connection when done 
		curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST 
		
		trigger_error("DEBUG TRANSACTION CART PAYFLOW_PRO: Processing Payflow Pro Order!");
		
		
		// Try to submit the transaction up to 3 times with 5 second delay.  This can be used
		// in case of network issues.  The idea here is since you are posting via HTTPS there
		// could be general network issues, so try a few times before you tell customer there 
		// is an issue.
		for ($i = 1; $i <= 3; $i++) {
			$result = curl_exec($ch);
		    $headers = curl_getinfo($ch);
		    trigger_error("DEBUG TRANSACTION CART PAYFLOW_PRO: Headers Received from Payflow Pro:\n".print_r($headers,1));
		    if ($gateway->get("testing_mode"))
		    {
		    	echo 'HEADERS<br />';
		    	print_r($headers);
		    	echo '<br /><br />RESULT RETURNED<br />';
		    	print_r($result);
		    	echo '<br /><br />';
		    }
			if ($headers['http_code'] !== 200) {
				trigger_error('DEBUG TRANSACTION CART PAYFLOW_PRO: Response not 200, sleaping for 5 seconds and try again.');
				if ($gateway->get('testing_mode')){
					echo 'HTTP CODE != 200:  Sleeping for 5 seconds...<br />(http_code = '.$headers['http_code'].')<br /><br />';
				}
				sleep(5);  // Let's wait 5 seconds to see if its a temporary network issue.
			} else if ($headers['http_code'] == 200) {
				// we got a good response, drop out of loop.
				trigger_error('DEBUG TRANSACTION CART PAYFLOW_PRO: Response 200, Proceeding to process response');
				if ($gateway->get('testing_mode')){
					echo 'HTTP CODE = 200:  Proceeding to process results.<br /><br />';
				}
		    	break;
			}
		}
		
		curl_close($ch);
		$transaction->set('headers_returned',$headers);
		$transaction->set('results_returned',$result);
		// Make sure the response is good
		if ($headers['http_code'] != 200) {
			if ($gateway->get("testing_mode")){
				echo '<h2>General Error!</h2>';
			   	echo '<h3>Unable to receive response from Payflow Pro server.</h3><p>';
			    echo "<h4>Verify host URL of $submiturl and check for firewall/proxy issues.</h4>";
			}
			$message = 'Unable to receive response from Payflow Pro server.  Verify host URL of '.$submiturl.' and check for firewall/proxy issues.';
			trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
			return self::_failure($transaction,self::FAIL_GATEWAY_CONNECTION,$message);
		}
		//If it gets this far, we at least got a response.  check that response
		
		trigger_error("DEBUG TRANSACTION CART PAYFLOW_PRO: Results from Payflow Pro:  Headers: ".print_r($headers,1)."\nRaw Data:\n".$result);
		if ($gateway->get("testing_mode")) {
			echo "<br /><br /><h1>RESULTS FROM PAYFLOW PRO</h1>
				<strong>Headers:</strong><pre>".print_r($headers,1)."</pre>
				<br /><strong>Raw Data:</strong><br />$result<br />";
		}
		
		//throw away anything before "result"
		$raw_result = $result;
		$result = strstr($result, "RESULT");
		// echo $result;
		// prepare responses into array
		$payflow_pro_result = array();
		$varval_pairs = explode('&',$result);
		foreach ($varval_pairs as $varval) {
			$parts = explode('=',$varval);
			$payflow_pro_result[$parts[0]] = $parts[1];
		}
		if ($gateway->get('testing_mode')) {
			echo "<strong>Formated Data:</strong><br /><pre>".print_r($payflow_pro_result,1)."</pre><br />";
		}
		trigger_error("DEBUG TRANSACTION CART PAYFLOW_PRO: Results from Payflow Pro:  Formated Data:\n".print_r($payflow_pro_result,1));
		if (!isset($payflow_pro_result["RESULT"]) || is_null($payflow_pro_result['RESULT'])) {
			//failsafe, result was not specified?  Treat it similar to connection problem, but with different message
			$message = 'Error:  RESULT not part of the response from Payflow Pro, expecting RESULT=0 for success or RESULT=## specifying an error code number.  Transaction cannot be processed.  Response received from Payflow Pro:: '.$raw_result;
			trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
			return self::_failure($transaction,self::FAIL_GATEWAY_CONNECTION,$message);
		}
		$result_code = intval($payflow_pro_result['RESULT']);
		switch ($result_code){
			case 0:
				//TRANSACTION SUCCESSFUL!! 0 means no errors
				trigger_error('DEBUG TRANSACTION CART PAYFLOW_PRO: RESULTS is equal to 0, no errors, payment good.');
				if ($gateway->get("testing_mode")){
					echo "<br /><br />RESULTS is equal to 0, no errors, payment good.<br />";
				}
				self::_success($cart->order,$transaction, $gateway);
				break;
				
			case 1:
				//Gateway settings wrong or IP address restricted
				$message = 'RESULT = 1: User authentication failed.  This is usually '.
						 'due to invalid account information or IP restriction on the account.'.
						 'You can You can verify ip restriction by logging into Manager.  '.
						 'See Service Settings >> Allowed IP Addresses.  Lastly it could '.
						 'be you forgot the path "/transaction" on the URL.';
				$message .= 'Response message received from Payflow Pro:  '.$payflow_pro_result['RESPMSG'];
				if ($gateway->get('testing_mode')){
					echo '<br />'.$message;
				}
				trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
				self::_failure($transaction,self::FAIL_CHECK_GATEWAY_SETTINGS,$message);
				break;
				
			case 26:
				//Gateway settings wrong
				$message = 'RESULT = 26:  Most Likely, you did not provide both '.
							 'the &lt;vendor> and &lt;user> fields in the admin.<br />'.
							 'Remember: &lt;vendor> = your merchant (login id), &lt;user> = <vendor> unless '.
							 'you created a separate &lt;user> for Payflow Pro.';
				$message .= 'Response message received from Payflow Pro:  '.$payflow_pro_result['RESPMSG'];
				if ($gateway->get('testing_mode')){
					echo '<br />'.$message;
				}
				trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
				self::_failure($transaction,self::FAIL_CHECK_GATEWAY_SETTINGS,$message);
				break;
			
			case 12:
				//Declined, Hard decline from bank.
				$message = 'RESULT = 12:  Transaction Declined (Hard decline from bank).';
				$message .= 'Response message received from Payflow Pro:  '.$payflow_pro_result['RESPMSG'];
				if ($gateway->get('testing_mode')){
					echo '<br />'.$message;
				}
				trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
				self::_failure($transaction,self::FAIL_BANK_DECLINED,$message);
				break;
				
			case 13:
				//Referral, needs approval with a verbal authorization.
				$message = 'RESULT = 13:  Transaction Pending (Needs voice authorization).';
				$message .= 'Response message received from Payflow Pro:  '.$payflow_pro_result['RESPMSG'];
				if ($gateway->get('testing_mode')){
					echo '<br />'.$message;
				}
				trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
				//special case: close out cart and let them know they need to contact us
				$transaction->setStatus(0); //should already be set to 0, but re-set just to be sure
				$transaction->set('result',self::PENDING_NEED_VOICE_AUTHORIZATION);
				$transaction->set('failed_reason',$result_message);
				$transaction->save();
				
				$cart = geoCart::getInstance();
				//do not add an error, just an error "message"
				$cart->addErrorMsg('process_result',self::PENDING_NEED_VOICE_AUTHORIZATION);
				break;
				
			case 23:
				// break intentionally omitted
			case 24:
				//Invalid CC number or expiration
				$message = 'RESULT = 23 or 24:  Invalid CC Number or Expiration.';
				$message .= 'Response message received from Payflow Pro:  '.$payflow_pro_result['RESPMSG'];
				if ($gateway->get('testing_mode')){
					echo '<br />'.$message;
				}
				trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
				self::_failure($transaction,self::FAIL_INVALID_CC_INFO,$message);
				break;
				
			default:
				//Unknown problem
				$message = 'RESULT = '.$result_code.' (Failed, Unknown Error)';
				$message .= 'Response message received from Payflow Pro:  '.$payflow_pro_result['RESPMSG'];
				if ($gateway->get('testing_mode')){
					echo '<br />'.$message;
				}
				trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: '.$message);
				self::_failure($transaction,self::FAIL_GENERAL_ERROR,$message);
				break;
		}
	}
}