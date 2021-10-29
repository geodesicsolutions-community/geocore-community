<?php
//payment_gateways/internetsecure.php
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

# Template payment gateway handler

class internetsecurePaymentGateway extends geoPaymentGateway{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'internetsecure';
	
	/**
	 * Required, Usually the same as the name, this can be used as a means
	 * to warn the admin that they may be using 2 gateways that
	 * are the same type.  Mostly used to distinguish CC payment gateways
	 * (by using type of 'cc'), but can be used for other things as well.
	 *
	 * @var string
	 */
	public $type = 'internetsecure';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'internetsecure';
	
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
			'title' => 'Internet Secure',//how it's displayed in admin
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
		
		$tpl = new geoTemplate('admin');
		$db = DataAccess::getInstance();
		$tpl->assign('payment_type', self::gateway_name);
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());
		
		$tooltips['tax_method'] = geoHTML::showTooltip('Canadian Tax Method', 'Choose which, if any, type of Canadian tax you want to charge.');
		$tooltips['language'] = geoHTML::showTooltip('Language to Display', 'This will determine the language that is displayed as the user continues his transaction on the Internet Secure website.');
		$tooltips['pst'] = geoHTML::showTooltip('Provincial Sales Tax', 'current tax rates for each of the Canadian Provinces and Territories');
		$tpl->assign('tooltips', $tooltips);
		
		$values['account_num'] = $this->get('account_num');
		$values['tax_method'] = $this->get('tax_method', 0);
		$values['language'] = $this->get('language', "English");
		$tpl->assign('values', $values); 
		
		$returnURL = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=internetsecure",$db->get_site_setting("classifieds_url"));
		$protocol = explode('//', $returnURL); // pull off the http:// for a sec
		$first = strpos($protocol[1], '/'); // find first slash
		$domain = substr($protocol[1], 0, $first); // get everything before first slash
		$processURL = substr($protocol[1], $first); // get everything after first slash (inclusive)
		$baseURL = $protocol[0] . '//' . $domain;
		$tpl->assign('baseURL', $baseURL);
		$tpl->assign('processURL', $processURL);
		
		
		
		return $tpl->fetch('payment_gateways/internetsecure.tpl');
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
		if (isset($_POST['internetsecure']) && is_array($_POST['internetsecure']) && count($_POST['internetsecure']) > 0){
			$settings = $_POST['internetsecure'];
			$this->_updateCommonAdminOptions($settings);
			
			$this->set('account_num', $settings['account_num']);
			$this->set('tax_method', $settings['tax_method']);
			$this->set('language', $settings['language']);
			
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
			'title' => $msgs[500286],
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
	 * Used: in geoCart::payment_choicesCheckVars()
	 * 
	 * Called no matter what selection is made when selecting payment type, so before doing
	 * any checks you need to make sure the payment type selected (in var $_POST['c']['payment_type'])
	 * matches this payment gateway.  If there are any problems, use $cart->addError() to specify
	 * that it should not go onto the next step, processing the order (aka geoCart_payment_choicesProcess())
	 *
	 */
	public static function geoCart_payment_choicesCheckVars (){
		$cart = geoCart::getInstance();
		
		if (isset($_POST['c']['payment_type']) && $_POST['c']['payment_type'] == self::gateway_name){
			//the selected gateway is this one, so check everything for any errors.
			//$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
			
		}
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
		$user_data = $cart->user_data;
		$db = $cart->db;
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);


		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $due = $invoice->getInvoiceTotal();

		if ($due >= 0){
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}

		//create initial transaction
		try {	
			$transaction = new geoTransaction();
			$transaction->setGateway(self::gateway_name);
			$transaction->setUser($cart->user_data['id']);
			$transaction->setStatus(0); //for now, turn off until it comes back from paypal IPN.
			$transaction->setAmount(-1 * $due);//set amount that it affects the invoice
			$msgs = $cart->db->get_text(true,183);
			$transaction->setDescription($msgs[500580]);
			$transaction->setGatewayTransaction($cart->session->getSessionId());
			$transaction->setInvoice($invoice);
			$invoice->addTransaction($transaction);
			$transaction->save();
		} catch (Exception $e){
			trigger_error('ERROR TRANSACTION CART PAYFLOW_PRO: Exception thrown when attempting to create new transaction.');
			return;
		}
		
		$taxMethod = $gateway->get('tax_method');
		
		$returnURL = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=internetsecure",$db->get_site_setting("classifieds_url"));
		
		//build redirect
		$cc_url = "https://secure.internetsecure.com/process.cgi?";
		$cc_url .= "MerchantNumber=".$gateway->get('account_num');
		$cc_url .= "&language=".$gateway->get('language');
		$cc_url .= "&ReturnURL=".$returnURL;
		$cc_url .= "&Products=".urlencode("Price::Qty::Code::Description::Flags");
		$cc_url .= urlencode("|".$transaction->getAmount()."::1::".$transaction->getId()."::classified ad::");
		if ($gateway->get('testing_mode')){
			$cc_url .= urlencode("{TEST}");
		}
		if ($taxMethod != "0"){
			$cc_url .= '{'.urlencode(trim($taxMethod)).'}';
		}
		$cc_url .= "&xxxName=".urlencode($user_data['firstname']." ".$user_data['lastname']);
		$cc_url .= "&xxxAddress=".urlencode($user_data['address']." ".$user_data['address_2']);
		$cc_url .= "&xxxCity=".urlencode($user_data['city']);
		$cc_url .= "&xxxProvince=".urlencode($user_data['state']);
		$cc_url .= "&xxxPostal=".urlencode($user_data['zip']);
		$cc_url .= "&xxxCountry=".urlencode($user_data['country']);
		$cc_url .= "&xxxEmail=".urlencode($user_data['email']);
		$cc_url .= "&xxxPhone=".urlencode($user_data['phone']);

		//set order status to Pending to prep for sending data to gateway
		$cart->order->setStatus('pending');
		
		//stop the cart session
		$cart->removeSession();
		
		require GEO_BASE_DIR . 'app_bottom.php';
		//go to 2checkout to complete
		header("Location: ".$cc_url);
		exit;
	}
	
	
	public static function geoCart_process_orderDisplay(){
		//use to display some success/failure page, if that applies to this type of gateway.
	}
	
	public function transaction_process(){
		/*
		VARIABLES RECEIVED
		//variables passed back from Internet Secure Export Script
		xxxName
		xxxCompany
		xxxAddress
		xxxCity
		xxxProvince
		xxxCountry
		xxxPostal
		xxxEmail
		xxxPhone
		xxxcard_name
		xxxCCType
		xxxAmount
		CustomerName
		CustomerCompany
		CustomerAddress
		CustomerCity
		CustomerProvince
		CustomerCountry
		CustomerPostalCode
		CustomerEmail
		CustomerPhone
		Cardholder
		MerchantNumber
		Currency
		Amount
		SalesOrderNumber
		receiptnumber
		ApprovalCode
		Verbage
		NiceVerbage
		CVV2Result
		AVSResponseCode
		Products
		DoubleColonProducts
		Language
		KeySize
		SecretKeySize
		UserAgent
		EntryTimeStamp
		UnixTimeStamp
		TimeStamp
		Live
		RefererURL
		ip_address
		ReturnURL
		ReturnCGI
		xxxVar1
		xxxVar2
		xxxVar3
		xxxVar4
		xxxVar5
		*/
		$referrer_address = $_SERVER["HTTP_REFERER"];
		
		//$internetsecure_variables = $_REQUEST;
		$internetsecure_variables = array_merge($_GET, $_POST);
		
		
		//get transaction id from DoubleColonProducts variable
		list($price,$quantity,$transaction_id,$description,$flags) = explode("::",$internetsecure_variables["DoubleColonProducts"],5);
		
		//get transaction
		$transaction = geoTransaction::getTransaction($transaction_id);
		$gateway = $transaction->getGateway();
		$order = $transaction->getInvoice()->getOrder();
		
		//store transaction data to registry
		
		$transaction->set('internetsecure_response', $internestsecure_variables);
		$transaction->save();
		
		if (($internetsecure_variables["Verbage"] == "Test Approved") || ($internetsecure_variables["Verbage"] == "Approved")){
			//success
			self::_success($order, $transaction, $gateway);
		} else {
			//fail
			self::_failure($transaction, $internetsecure_variables['ApprovalCode'], $internetsecure_variables['Verbage']);
		}
		
		
		
	}
}