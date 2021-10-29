<?php
//cashu.php
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
## ##    17.07.0-20-g8d4acde
## 
##################################

# CashU Payment Gateway
# see https://www.cashu.com

class cashuPaymentGateway extends geoPaymentGateway
{
	public $name = 'cashu';
	public $type = 'cashu';
	const gateway_name = 'cashu';
	
	/**
	 * The host to submit the transactin info to, which will return transaction code
	 * @var string
	 */
	private $_host_wsdl = 'https://secure.cashu.com/payment.wsdl';
	
	/**
	 * The site to send the user to once we have transaction code
	 * @var string
	 */
	private $_host_payment = 'https://www.cashu.com/cgi-bin/payment/pcashu.cgi';
	
	/**
	 * The URL to send "notification response" to, to let gateway know we got the notice.
	 * @var string
	 */
	private $_host_merchant_response = 'https://www.cashu.com/cgi-bin/notification/MerchantFeedBack.cgi';
	//Test version:
	//private $_host_merchant_response = 'https://www.cashu.com/cgi-bin/test_notification/MerchantFeedBack.cgi';
	
	
	/**
	 * currencies accepted by cashU, according to documentation PDF
	 * @param array
	 */
	protected static $_currencies = array(
		'USD' => 'U.S. Dollar',
		'CSH' => 'cashU Points',
		'AED' => 'UAE Dirham',
		'EUR' => 'Euro',
		'JOD' => 'Jordanian Dinar',
		'EGP' => 'Egyptian Pound',
		'SAR' => 'Saudi Riyal',
		'DZD' => 'Algerian Dinar',
		'LBP' => 'Lebanese Pound',
		'MAD' => 'Moroccan Dirham',
		'QAR' => 'Qatar Riyal',
		'TRY' => 'Turkish Lira',
	);
	
	/**
	 * Array of error responses possible when initially trying to set up a
	 * transaction using SOAP with cashU gateway.
	 * 
	 * @var array
	 */
	protected static $_soapErrors = array (
		'INSECURE_REQUEST' => 'Gateway connection not through HTTPS.',
		'SYSTEM_NOT_AVAILABLE' => 'cashU servers are not responding.  Please try again later.',
		'INVALID_PARAMETER' => 'Possible payment gateway mis-configuration, please contact site admin.',
		'INACTIVE_MERCHANT' => 'The Merchant account is inactive, please contact the site admin.',
		'TOKEN_CHECK_FAILURE' => 'Possible payment gateway mis-configuration, please contact site admin.',
		'GENERAL_SYSTEM_ERROR' => 'General payment gateway error happened while processing the transaction.',
	);
	
	public static function admin_display_payment_gateways ()
	{
		$return = array (
			'name' => self::gateway_name,
			'title' => 'cashU',//how it's displayed in admin
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
		$db = DataAccess::getInstance();
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		
		$tpl_vars = array();
		
		$tpl_vars['payment_type'] = self::gateway_name;
		$tpl_vars['commonAdminOptions'] = $this->_showCommonAdminOptions(true,false);
		$tpl_vars['merchantId'] = $this->get('merchantId');
		$tpl_vars['encryptionKey'] = $this->get('encryptionKey');
		$tpl_vars['service_name'] = $this->get('service_name');
		$tpl_vars['currency_type'] = $this->get('currency_type');
		$tpl_vars['currency_rate'] = $this->get('currency_rate','1.0');
		$tpl_vars['currencies'] = self::$_currencies;
		if ($db->get_site_setting('classifieds_ssl_url')) {
			$tpl_vars['transactionUrl'] = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=cashu", $db->get_site_setting("classifieds_ssl_url"));
			$tpl_vars['sslWarn'] = false;
		} else {
			$url = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=cashu", $db->get_site_setting("classifieds_url"));
			$url = str_replace('http:','https:',$url);
			$tpl_vars['transactionUrl'] = $url;
			$tpl_vars['sslWarn'] = true;
		}
		
		//Requirements tests
		$tpl_vars['checks']['openssl'] = self::_checkOpenSSL();
		$tpl_vars['checks']['soap'] = self::_checkSoap();
		
		$tpl_vars['gatewayLanguages'] = $this->get('gatewayLanguages', array());
		//send site languages
		$tpl_vars['languages'] = $db->Execute("SELECT * FROM ".geoTables::pages_languages_table." WHERE `active`=1");
		
		$tpl->assign($tpl_vars);
		
		return $tpl->fetch('payment_gateways/cashu.tpl');
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
		if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0) {
			$admin = geoAdmin::getInstance();
			
			$settings = $_POST[self::gateway_name];
			
			$this->_updateCommonAdminOptions($settings,false);
			
			$this->set('merchantId',trim($settings['merchantId']));
			$this->set('encryptionKey',$settings['encryptionKey']);
			$this->set('service_name',(($settings['service_name'])? $settings['service_name'] : false));
			
			//make sure valid currency type
			$currency_type = (isset(self::$_currencies[$settings['currency_type']]))? $settings['currency_type'] : 'USD';
			$this->set('currency_type',$currency_type);
			$this->set('currency_rate',round($settings['currency_rate'],4));
			
			$this->set('gatewayLanguages', $settings['gatewayLanguages']);
		}
		return true;
	}
	
	/**
	 * Optional, used in various places, if return true then you signify that
	 * this payment gateway has recurring billing capabilities.  If method not
	 * implemented, the superclass will return false (not recurring) by default.
	 * 
	 * @return bool
	 */
	public function isRecurring ()
	{
		//return false here to signify this payment gateway is not able to process
		//recurring billing.  Note that recurring billing is Enterprise only.
		return false;
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
		
		//make sure checks are passed...
		if (!geoPaymentGateway::getPaymentGateway(self::gateway_name)->_checks()) {
			//do not add this gateway, either server doesn't meet requirements or settings not set
			return false;
		}
		
		//if there are any types of things that this gateway cannot pay for, loop through the $itemCostDetails array
		//to see if it is in there, and if so simply return false to avoid showing this gateway as a payment choice.
		
		$msgs = $cart->db->get_text(true, 10203);
		$return = array(
			//Items that don't auto generate if left blank
			'title' => $msgs[501429],
			'title_extra' => '',//usually make this empty string.
			'label_name' => self::gateway_name,
			'radio_value' => self::gateway_name,//should be same as gateway name
			'help_link' => '',//$cart->site->display_help_link(3240),
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
	public static function geoCart_payment_choicesProcess ()
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
		
		if (!$gateway->_checks()) {
			//Gateway checks failed, cannot process payment..  Should not even show
			//the gateway choice on the payment gateway pages, this is just a failsafe
			return;
		}
		
		//Now, create a new transaction attached to the invoice, but set status to 0 meaning
		//is is not active yet.  We'll activate the transaction when we get the IPN signal later..
		$transaction = new geoTransaction;
		$transaction->setAmount(-1 * $due);//balance out the amount due on invoice
		
		$transaction->setDate(geoUtil::time());
		//we need text from main cart page for the text to send to cashU
		$cart->db->get_text(false, 10203);
		//and also from invoice page
		$msgs = $cart->db->get_text(true,183);
		$transaction->setDescription($msgs[501430]);
		$transaction->setGateway($gateway);
		$transaction->setInvoice($invoice);
		$transaction->setStatus(0);
		$transaction->setUser($cart->user_data['id']);
		
		$mult = ($gateway->get("currency_rate", 0) != 0)? $gateway->get("currency_rate"): 1;
		$mult = ($mult * -1);//due is going to be negative, need to convert to positive to process by cashu
		$amount_converted = sprintf("%01.2f",round(($mult * $due), 2));
		//Set this data in transaction for debug purposes later on
		$transaction->set('cashU_amount',$amount_converted); //amount sent to paypal		
		
		$invoice->addTransaction($transaction);
		
		$transaction->save();//save changes
		
		$test_mode = ($gateway->get('testing_mode'))? 1 : 0;
		
		trigger_error('DEBUG TRANSACTION: Inserting new transaction for cashu payment.');
		
		$soap = new SoapClient($gateway->_host_wsdl);
		
		$token = $gateway->_genToken($amount_converted);
		
		$transaction->set('token', $token);
		
		$result = $soap->DoPaymentRequest($gateway->get('merchantId'), $token,
			$msgs[501431], $gateway->get('currency_type'), $amount_converted, $gateway->_getLanguage(), $transaction->getId(),
			$msgs[501431], '', '', '', '', $test_mode, ''.$gateway->get('service_name'));
		
		//Check for errors...
		
		if (!strlen($result) || isset(self::$_soapErrors[$result])) {
			//empty result, or result is one of the errors
			$gateway->_soapFailure($transaction, $result);
			return;
		}
		
		//break up the result
		$parts = explode('=',$result);
		
		if (!isset($parts[0]) || $parts[0]!=='Transaction_Code') {
			//Result is not as expected, return failure
			$gateway->_soapFailure($transaction, $result);
			return;
		}
		$transactionCode = $parts[1];
		
		trigger_error("DEBUG TRANSACTION: cashU initial getting transaction code successful, code is $transactionCode");
		
		$transaction->setGatewayTransaction($transactionCode);
		
		//re-save transaction to save paypal url.
		$transaction->save();
		
		//set order status to Pending to prep for sending data to gateway
		$cart->order->setStatus('pending');
		//Close the cart session so user can't come back and change something after payment
		$cart->removeSession();
		
		$post_fields = array(
			'Transaction_Code' => $transactionCode,
			'test_mode' => $test_mode,
		);
		
		$gateway->_submitViaPost($gateway->_host_payment, $post_fields);
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
	 */
	public static function geoCart_process_orderDisplay()
	{
		//use to display some success/failure page, if that applies to this type of gateway.
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
	 * https://example.com/transaction_process.php?gateway=_template
	 * 
	 * As the "signal/notification URL" to send notifications to (obviously would need
	 * to adjust for the actual payment gateway and actual site's URL).  Don't
	 * forget to authenticate the signal in some way, to validate it is indeed
	 * coming from the payment processor!
	 */
	public function transaction_process ()
	{
		trigger_error("DEBUG TRANSACTION: Top of transaction_process for cashU payment gateway.");
		
		if (isset($_GET['success_page']) && $_GET['success_page']=='success') {
			//show fake success page
			trigger_error("DEBUG TRANSACTION:  This is success page, not the payment signal.");
			return $this->_showFakeSuccessPage();
		}
		
		if (isset($_POST['trn_id']) && $_POST['trn_id']) {
			//weird bug that doesn't always submit the second GET parameter to return URL...
			//BUT it does POST the trn_id so that is way to tell this is return url
			trigger_error("DEBUG TRANSACTION:  This is success page (since it has trn_id), not the payment signal.");
			return $this->_showFakeSuccessPage();
		}
		
		//parse the XML
		$xml = $_POST['sRequest'];
		if (!strlen($xml)) {
			trigger_error("ERROR TRANSACTION: No XML posted, nothing to do.");
			return;
		}
		$transactionId = (int)$this->_getXmlValue('session_id',$xml);
		trigger_error("DEBUG TRANSACTION: transaction ID extracted from data sent: $transactionId");
		if (!strlen($transactionId)) {
			trigger_error("ERROR TRANSACTION: No transaction ID (session_id) found in XML, here is XML posted: \n".$xml);
			return;
		}
		$transaction = geoTransaction::getTransaction($transactionId);
		
		if (!is_object($transaction) || !$transaction->getId()) {
			trigger_error('ERROR TRANSACTION: Invalid transaction ID: '.$transactionId.", object: \n".print_r($transaction,1)."\nfull XML: \n".$xml);
			return;
		}
		if (!$transaction->getGateway() || $transaction->getGateway()->getType()!=self::gateway_name) {
			trigger_error("ERROR TRANSACTION: Transaction gateway does not match cashu!  Transaction object:\n".print_r($transaction,1)."\nfull XML:\n$xml");
			return;
		}
		if ($transaction->get('notice_received')) {
			//transaction already good, nothing more to do but bask in glory.
			trigger_error("DEBUG TRANSACTION:  Already received notice for this transaction.");
			return;
		}
		
		$responseCode = $this->_getXmlValue('responseCode', $xml);
		if ($responseCode!='OK') {
			//Error with notice...
			trigger_error("DEBUG TRANSACTION: Response code is not OK so not activating order.  Full XML returned:\n".$xml);
			return;
		}
		
		if (!$this->_validateToken($xml)) {
			//the returned cashUToken not valid!
			trigger_error("ERROR TRANSACTION: cashUToken not valid!  Full XML:\n".$xml);
			return;
		}
		
		trigger_error("DEBUG TRANSACTION: cashU transaction_process successful!  Now activating order and notifying gateway that notice was received.");
		//remember that we received the notice, so we don't process the same thing multiple times.
		$transaction->set('notice_received', 1);
		
		//get the invoice
		$invoice = $transaction->getInvoice();
		
		//get the order
		$order = $invoice->getOrder();
		self::_success($order,$transaction,$this, true);
		
		//let the gateway know we got it, do very last in case error happens in middle of saving
		//order changes to DB or something odd
		$this->_sendNoticeResponse($xml);
		
		return true;
	}
	
	private function _sendNoticeResponse ($xml)
	{
		$xmlParts = array();
		
		$xmlParts['merchant_id'] = $this->get('merchantId');
		$xmlParts['cashU_trnID'] = $this->_getXmlValue('cashU_trnID',$xml);
		$xmlParts['cashUToken'] = $this->_getXmlValue('cashUToken',$xml);
		$xmlParts['responseCode'] = 'OK';
		$xmlParts['responseDate'] = gmdate('Y-m-d H:i:s');
		
		$xmlResponse = '<cashUTransaction>';
		foreach ($xmlParts as $name => $value) {
			$xmlResponse .= "<$name>$value</$name>";
		}
		$xmlResponse .= '</cashUTransaction>';
		
		$result = geoPC::urlPostContents($this->_host_merchant_response, array('sRequest'=>$xmlResponse));
		trigger_error("DEBUG TRANSACTION: Sending merchant response to payment notice.  Result:\n".$result);
	}
	
	private function _genToken ($amount)
	{
		$currency = $this->get('currency_type');
		$merchant_id = $this->get('merchantId');
		$encryption_key = $this->get('encryptionKey');
		
		return md5(strtolower("$merchant_id:$amount:$currency:").$encryption_key);
	}
	
	private function _validateToken ($xml)
	{
		$cashUToken = $this->_getXmlValue('cashUToken',$xml);
		
		$cashU_trnID = $this->_getXmlValue('cashU_trnID',$xml);
		$merchantId = $this->get('merchantId');
		$encryptionKey = $this->get('encryptionKey');
		
		if (!$cashU_trnID||!$cashUToken||!$merchantId||!$encryptionKey) {
			//some of info not here, can't validate
			return false;
		}
		
		$hash = md5(strtolower("$merchantId:$cashU_trnID:$encryptionKey"));
		
		if ($hash !== $cashUToken) {
			//Documentation not consistent for whether encryption key is lowercase
			//or not.
			$hash = md5(strtolower("$merchantId:$cashU_trnID:").$encryptionKey);
		}
		
		return ($hash===$cashUToken);
	}
	
	private function _getLanguage ()
	{
		$langId = geoSession::getInstance()->getLanguage();
		
		$langs = $this->get('gatewayLanguages', array());
		
		//default to en
		return (isset($langs[$langId]))? $langs[$langId] : false;
	}
	
	private static function _checkOpenSSL ()
	{
		return extension_loaded('openssl');
	}
	
	private static function _checkSoap ()
	{
		return extension_loaded('soap');
	}
	
	private function _checkSettings ()
	{
		if (!$this->get('merchantId') || !$this->get('encryptionKey') || !$this->_getLanguage()) {
			return false;
		}
		return true;
	}
	
	/**
	 * Figures out if site is capable of using cashU (whether settings are set, and
	 * whether server requirements are met)
	 * 
	 * @return bool
	 */
	private function _checks ()
	{
		if (!self::_checkOpenSSL() || !self::_checkSoap() || !$this->_checkSettings()) {
			//Open SSL or Soap not loaded
			return false;
		}
		
		return true;
	}
	
	private function _soapFailure ($transaction, $result)
	{
		/**
		 * NOTE:  All of these errors are result of something wrong with initial communication
		 * with the gateway, so only happen when site is mis-configured somehow, so no need
		 * to use translated text.
		 */
		$transaction->setStatus(0);
		$transaction->set('result', $result);
		$transaction->save();
		
		$message = (isset(self::$_soapErrors[$result]))? self::$_soapErrors[$result]." [$result]" : 'Error when communicating with gateway, no response or did not understand response from server.';
		
		//show error message instead of generic error
		return geoCart::getInstance()->addError()->addErrorMsg('gateway_error',$message);
		
		self::_failure($transaction, $result, $message);
	}
	
	private function _getXmlValue ($name, $xml)
	{
		if (!strlen($xml)) {
			return null;
		}
		
		$start = "<{$name}>";
		$end = "</{$name}>";
		
		if (strpos($xml, $start)===false || strpos($xml, $end)===false) {
			//start/end tags not found
			return null;
		}
		
		$parts = explode($start, $xml);
		
		$parts = explode($end,$parts[1]);
		
		return $parts[0];
	}
	
	private function _showFakeSuccessPage ()
	{
		$session = geoSession::getInstance();
		$session->initSession();
		
		$view = geoView::getInstance();
		$view->addBaseTag = true;
		
		self::_successFailurePage(true, 'active');
	}
}