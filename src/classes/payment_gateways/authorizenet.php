<?php
//payment_gateways/authorizenet.php
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
## ##    7.5.3-18-gdc2a17a
## 
##################################

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

# Authorize.net and "other" gateways that use same connection type

/**
 * Internal note: for internal testing purposes, the following "test api only" account can be used:
 * (This account will NOT work in "live" mode as it is for testing only)
 * 
 * Developer integration center:  http://developer.authorize.net/
 * 
 * API Login: 2Sht9m67PD
 * Transaction Key:  35J2rq5d6LtJ2Yg6
 *
 */

class authorizenetPaymentGateway extends _ccPaymentGateway{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'authorizenet';
	
	/**
	 * Required, Usually the same as the name, this can be used as a means
	 * to warn the admin that they may be using 2 gateways that
	 * are the same type.  Mostly used to distinguish CC payment gateways
	 * (by using type of 'cc'), but can be used for other things as well.
	 *
	 * @var string
	 */
	public $type = 'cc';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'authorizenet';
	
	/**
	 * Sugested, specify the "testing" or "sandbox" URL here so it can
	 * easily be updated later if needed.
	 *
	 * @var string
	 */
	private static $_submitUrlTesting = 'https://test.authorize.net/gateway/transact.dll';
	
	/**
	 * Suggested, specify the "live" URL to process payments through the
	 * gateway here so it can easily be updated later if needed.
	 *
	 * NOTE: using the paytrace gateway will override this setting
	 * 
	 * @var string
	 */
	private static $_submitUrl = 'https://secure2.authorize.net/gateway/transact.dll';
	
	private static $_submitUrlPaytrace = 'https://paytrace.com/api/gateway.pay';
	
	private static $_submitUrlEProcessingNetwork = "https://www.eProcessingNetwork.Com/cgi-bin/an/transact.pl";
	
	protected static $resultFields = Array(
						"x_response_code",
						"x_response_subcode",
						"x_response_reason_code",
						"x_response_reason_text",
						"x_auth_code",
						"x_avs_code",
						"x_trans_id",
						"x_invoice_num",
						"x_description",
						"x_amount",
						"x_method",
						"x_type",
						"x_cust_id",
						"x_first_name",
						"x_last_name",
						"x_company",
						"x_address",
						"x_city",
						"x_state",
						"x_zip",
						"x_country",
						"x_phone",
						"x_fax",
						"x_email",
						"x_ship_to_first_name",
						"x_ship_to_last_name",
						"x_ship_to_company",
						"x_ship_to_address",
						"x_ship_to_city",
						"x_ship_to_state",
						"x_ship_to_zip",
						"x_ship_to_country",
						"x_tax",
						"x_duty",
						"x_freight",
						"x_tax_exempt",
						"x_po_num",
						"x_md5_hash",
						//new response fields, added 12/23/2008
						"x_card_code_response",
						"x_cardholder_authentication_verification_response",
					);
	
		/**
	 * Optional, used in various places, if return true then you signify that
	 * this payment gateway has recurring billing capabilities.  If method not
	 * implemented, the superclass will return false (not recurring) by default.
	 * 
	 * @return bool
	 */
	public function isRecurring ()
	{
		//most gateways should do it like so:
		if($this->get('connection_type') == 2 && $this->get('merchant_type') == 1) {
			//must use authorize.net AIM for recurring stuff
			return $this->get('recurring');
		} else {
			return false;
		}
	}
	
	/**
	 * Optional, used on payment selection page, this will be the recurring
	 * billing user agreement label and text, it should return an array.
	 * Only used if isRecurring returns true and it is recurring payment.  If 
	 * implemented by payment gateway, the superclass will return false which
	 * indicates no user agreement.
	 * 
	 * @return array|bool Either bool false if no agreement shown, or an array 
	 *   like: array ('label' => 'label text', 'text' => 'text in agreement box.')
	 */
	public function getRecurringAgreement ()
	{
		$msgs = DataAccess::getInstance()->get_text(true, 10203);
		if ($msgs[500765]) {
			return array ('label' => $msgs[500764], 'text' => $msgs[500765]);
		}
		return false;
	}
	
	/**
	 * Optional, used to get an updated status for the recurring billing to see
	 * if it is current and paid, and if so update the recurring data's info.
	 * 
	 * Called to query the gateway to see the status of the recurring billing,
	 * and update the recurring billing's paidUntil status, update main status
	 * (for gateways that choose to use that), add a recurring billing transaction
	 * if applicable, etc.
	 * 
	 * @param geoRecurringBilling $recurring
	 */
	public function recurringUpdateStatus ($recurring)
	{
		$recurringId = substr($recurring->getSecondaryId(), 5);
		
		if (!$recurringId) {
			//what the...  this one has no valid subscription?
			trigger_error("DEBUG RECURRING ARB: No valid subscription ID, canceling recurring billing.");
			$recurring->setStatus(geoRecurringBilling::STATUS_CANCELED);
			$recurring->save();
			return true;
		}
		$request = 
		"<?xml version=\"1.0\" encoding=\"utf-8\"?>
<ARBGetSubscriptionStatusRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
  <merchantAuthentication>
    <name>{$this->get('merchant_login')}</name>
    <transactionKey>{$this->get('transaction_key')}</transactionKey>
  </merchantAuthentication>
  <refId>" . substr(md5(rand()), 0, 20) . "</refId>
  <subscriptionId>$recurringId</subscriptionId>
</ARBGetSubscriptionStatusRequest>";
		
		$xml = self::_sendSimpleRequest($request, $this);
		
		if ($xml && $xml->status) {
			if ($xml->status.'' === 'active') {
				//It is active!  Since authorize.net currently does not have anything
				//to figure out when next billing cycle starts, nothing more we can do..
				
				if ($recurring->getPaidUntil() <= geoUtil::time()) {
					//paid until is in the past, but we know the subscription is
					//still active, so set "paid until" for tomorrow...
					trigger_error("DEBUG RECURRING ARB:  We detected that the subscription
							is active, but no way to tell for how long, so marking it as paid
							until this time tommorrow.");
					
					//log it in a transaction..
					$transaction = self::_createNewRecurringLogTransaction($recurring, $this);
					$transaction->setDescription("Subscription status came back as active, so
							setting paid until to tomorrow");
					
					$recurring->setPaidUntil(geoUtil::time()+(60*60*24));
				}
				
				$recurring->setStatus(geoRecurringBilling::STATUS_ACTIVE);
			} else {
				//canceled, but recurring billing is active?  cancel it!
				//create transaction to log the process
				if ($recurring->getStatus() === geoRecurringBilling::STATUS_ACTIVE) {
					trigger_error("DEBUG RECURRING ARB: recurring is currently active, status check
							came back as: ".$xml->status." so canceling recurring billing.");
					$transaction = self::_createNewRecurringLogTransaction($recurring, $this);
					
					$transaction->setDescription('Subscription status came back as: '.$xml->status.' when validating status of subscription with gateway.');
					$transaction->setRecurringBilling($recurring);
					$transaction->save();
					$recurring->cancel('Status reported as '.$xml->status, true);
					$recurring->addTransaction($transaction);
				} else {
					//not active right now, don't need to do lot of hoop-la... just cancel directly
					$recurring->setStatus(geoRecurringBilling::STATUS_CANCELED);
				}
			}
			$recurring->save();
		}
		trigger_error("DEBUG RECURRING ARB: xml response for status request:\n".print_r($xml,1));
		
		//nothing to do here, since authorize.net is pretty gimp
		$recurring->set('refreshExtraInfo', 'NOTE: <strong style="color: red;">Authorize.net status refresh is limited</strong> -- the "paid until" date is not able to be accurately refreshed this way,
				only the status itself is accurate.');
		return true;
	}
	
	private static function _sendSimpleRequest ($content, $gateway, $return_xml = true)
	{
		$host = $gateway->get('testing_mode') ? 'apitest.authorize.net' : 'api.authorize.net';
		$path = "/xml/v1/request.api";
		
		$posturl = "https://" . $host . $path;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		$response = curl_exec($ch);
		
		curl_close($ch);
		if (!$return_xml) {
			return $response;
		}
		$xml = simplexml_load_string($response);
		return $xml;
	}
	
	
	/**
	 * Optional, called to cancel the recurring billing, to stop payments.  
	 * Gateway should do whatever is needed to cancel the payment status, and
	 * update the details on the recurring billing.
	 * 
	 * @param geoRecurringBilling $recurring
	 * @param string $reason The reason for the recurring billing cancelation.
	 */
	public function recurringCancel ($recurring, $reason = '')
	{
		trigger_error('DEBUG RECURRING ARB:  Recurring billing cancel requested.  Reason: '.$reason);
		$recurringId = substr($recurring->getSecondaryId(), 5);
		if(!$recurringId) {
			//can't find recurring ID -- this is probably already cancelled, or was never set up right in the first place
			return true;
		}
		
		$content =
        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
        "<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
	        "<merchantAuthentication>".
		        "<name>" . $this->get('merchant_login') . "</name>".
		        "<transactionKey>" . $this->get('transaction_key') . "</transactionKey>".
	        "</merchantAuthentication>".
			"<refId>" . substr(md5(rand()), 0, 20) . "</refId>". //this is really just a debugging checksum -- just make it a random string
	        "<subscriptionId>" . $recurringId . "</subscriptionId>".
        "</ARBCancelSubscriptionRequest>";

		trigger_error('DEBUG RECURRING ARB: full XML of request is: <pre>'.htmlspecialchars($content).'</pre>');
		
		$xml = self::_sendSimpleRequest($content, $this);
		
		trigger_error('DEBUG RECURRING ARB: parsed XML: <pre>'.print_r($xml,1).'</pre>');
		
		//whether it is successful or not, log a transaction for the attempt...
		$transaction = self::_createNewRecurringLogTransaction($recurring, $this);
		
		//this is the way it SHOULD work, according to auth.net docs
		//if resultCode is 'Ok,' transaction is good. Or if error code is E00038 means can't
		//be canceled because already is canceled/terminated..
		$success = ($xml->messages->resultCode.'' === 'Ok' || $xml->messages->message->code.'' === 'E00038');
		
		//these nodes give more detail as to why the transaction succeeded/failed
		//log them into the DB
		$code = $xml->messages->message->code;
		$text = $xml->messages->message->text;
		$resultString = "$code :: $text";
		
		$by = (defined('IN_ADMIN'))? 'admin':'user';
		$transaction->setDescription('Cancelation initiated, reason: '.$reason.' - response from gateway: '.$resultString);
		
		$recurring->save();

		return $success;		
	}
	
					
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
			'title' => 'CC - Authorize.net / Paytrace / eProcessingNetwork',//how it's displayed in admin
			'head_html' => ""//optional, if specified, 
		);
		
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		if(!$gateway->get('merchant_login')) {
			$return['title'] .= " <a href='http://reseller.authorize.net/application/?resellerId=27535' class='mini_button' onclick='window.open(this.href); return false;'>Create an Authorize.net account</a>";
		}
		
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
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions(true, true));
		$tpl->assign('is_ent', geoPC::is_ent());
		
		if (geoPC::is_ent()) {
			$tpl->assign('arb_payment_nolink_email', $this->get('arb_payment_nolink_email'));
			$tpl->assign('arb_payment_nolink_cancel', $this->get('arb_payment_nolink_cancel'));
		}
		
		$tooltips['merchant_type'] = geoHTML::showTooltip('Choose a gateway', 'You may use either the Authorize.net gateway, or one of some other gateways that emulate its functionality.');
		$tooltips['connection_type'] = geoHTML::showTooltip('Connection Type','Connection to Authorize.net requires the use of one of two methods. AIM is the most secure, but requires that your server have a non-standard PHP "plugin" called cURL installed and configured. If cURL is not available on your server, you may use the SIM method, which makes use of the mhash "plugin." If you\'re using the Paytrace gateway (not Authorize.net), the AIM method and cURL are required.');
		$tooltips['verify_peer'] = geoHTML::showTooltip('Verify Peer within Transaction','Only applicable for AIM connections.');
		$tooltips['merchant_login'] = geoHTML::showTooltip('Merchant Login','Merchant login id you were given to use within the ADC Relay Response message sent to the gateway in the live credit card transaction environment.');
		$tooltips['transaction key'] = geoHTML::showTooltip('Connection Type','<strong>Only necessary if you are using the AIM Connection Type.</strong> The gateway rejects all transactions that do not have a transaction key or that include an invalid key. The transaction key can be obtained from your merchant interface at the gateway\'s website.');
		$tooltips['currency_code'] = geoHTML::showTooltip('Currency Code','This is the currency you accept paymnts in');
		$tooltips['email_customer'] = geoHTML::showTooltip('Send Gateway Email to Customer','Choosing "yes" will have the gateway send the customer a reciept of the transaction. You can customize the header and footer of that email in the customer email header and customer email footer sections below.');
		$tooltips['email_admin'] = geoHTML::showTooltip('Send Gateway Email to Admin','Choosing "yes" will have the gateway send an email to the admin address set as the site email everytime a transaction is completed. RECOMMENDED "YES" AT FIRST TO MAKE SURE THAT YOUR TRANSACTIONS ARE COMPLETED CORRECTLY.');
		$tooltips['secret'] = geoHTML::showTooltip('Secret Key (MD5 Hash value)','This value is used to authenticate signals from the ARB (Automatic Recurring Billing) system. If you are using Recurring Billing, this will need to match the "MD5 Hash" value as configured in your Authorize.net control panel. <strong>If not using recurring billing, you may leave this blank.</strong>');
		$tpl->assign('tooltips', $tooltips);

		$values['merchant_type'] = $this->get('merchant_type', 1);
		$values['charge_final_fees'] = $this->get('charge_final_fees');
		$values['use_no_free_cart'] = $this->get('use_no_free_cart');
		$tpl->assign('finalFees',(geoMaster::is('auctions')));
		$values['connection_type'] = $this->get('connection_type', 2);
		$values['verify_peer'] = $this->get('verify_peer');
		$values['merchant_login'] = $this->get('merchant_login');
		$values['transaction_key'] = $this->get('transaction_key');
		$values['currency_code'] = $this->get('currency_code', "USD");
		$values['email_customer'] = $this->get('email_customer', 0);
		$values['email_admin'] = $this->get('email_admin', 0);
		$values['secret'] = $this->get('secret');
		$tpl->assign('values', $values);

		//get currency codes
		$db = DataAccess::getInstance();
		$sql = "select value, display_value from geodesic_choices where type_of_choice = 75";
		$result = $db->Execute($sql);
		$options = "";
		while($line = $result->FetchRow())
		{
			$selected = ($values['currency_code'] == $line['value']) ? "selected=\"selected\"" : ""; 
			$options .= "<option value=\"".$line['value']."\" ".$selected.">".$line['display_value']."</option>\r\n";
		}
		$tpl->assign('currencyOptions', $options);
		
		//get status of SIM/AIM installs
		$tpl->assign('mhash', (function_exists('mhash') ? true : false));
		$tpl->assign('curl', (function_exists('curl_version') ? true : false));
		 
		$tpl->assign('transactionTest', $this->test_authorizenet());
		
		//URL for gateway replies
		$baseUrl = dirname($db->get_site_setting('classifieds_url')).'/';
		$tpl->assign('responseURL', "{$baseUrl}transaction_process.php?gateway=authorizenet");
		$tpl->assign('silentPOST', "{$baseUrl}recurring_process.php?gateway=authorizenet");
		
		return $tpl->fetch('payment_gateways/authorizenet.tpl');
	}
	
	
	/**
	 * Initiates a test connection to the authorize.net or paytrace servers
	 *
	 * @return String containing formatted response from server
	 */
	private function test_authorizenet()
	{
		if($this->get('connection_type') == 1){
			//using SIM method. can't cURL test
			return "<p>Please see below for instructions on testing the SIM connection.</p>";
		}
		
		$username = $this->get('merchant_login');
		$trans_key = $this->get('transaction_key');

		if ($username == '' || $trans_key == ''){
			$result = 'Empty Fields.  Be sure to fill in the fields above and <strong>click "save"</strong>.  See the <strong>setup instructions</strong> below for more information.<br />';
			return $result;
		}
		

		switch($show['merchant_type']) {
			case 5: //PayTrace
				$card_num = "5454545454545454";
				$url = self::$_submitUrlPaytrace;
				break;
			case 6: //eProcessingNetwork.com
				$card_num = "4007000000027";
				$url = self::$_submitUrlEProcessingNetwork;
			case 1:
				//authorize.net
				//break ommited on purpose
				
			default:
				//authorize.net - default
				$card_num = "4007000000027";
				if ($this->get('testing_mode')) {
					$url = self::$_submitUrlTesting;
				} else {
					$url = self::$_submitUrl;
				}
		}

		//randomize test data
		srand((double)microtime()*1000000);
		$x_PO_Num = rand(1000000,9999999);
		$x_Invoice_Num = rand(1000000,9999999);
		$x_Amount = rand(1,20);
		$x_Exp_Date = date('my', time()+31536000); // set test CC to expire one year from today
		
		$cc_url = "&x_First_Name=Joe";
		$cc_url .= "&x_Last_Name=Mama";
		$cc_url .= "&x_Company=Geodesic+Solutions";
		
		$cc_url .= "&x_Address=address+";
		$cc_url .= "&x_City=city";
		$cc_url .= "&x_State=state";
		$cc_url .= "&x_Country=country";
		$cc_url .= "&x_Zip=zipcode";
		$cc_url .= "&x_Phone=phone";
		
		$cc_url .= "&x_delim_data=true";
		$cc_url .= "&x_Delim_Char=|";
		$cc_url .= "&x_Relay_Response=FALSE";
		//eprocessingnetwork.com settings
		//$cc_url .= "&x_Relay_Response=false&x_Relay_URL=false&x_Delim_Data=true&x_Delim_Char=|";
		
		$cc_url .= "&x_Email=geoclassifieds%40geodesicsolutions.com";
		$cc_url .= "&x_Customer_Organization_Type=B";
		
		$cc_url .= "&x_Type=AUTH_CAPTURE";
		
		$cc_url .= "&x_Description=classified+ad+placement";
		$cc_url .= "&x_Version=3.1";
		$cc_url .= "&x_Method=CC";
		$cc_url .= "&x_PO_Num=$x_PO_Num";
		$cc_url .= "&x_Invoice_Num=$x_Invoice_Num";
		$cc_url .= "&x_Customer_IP=".getenv("REMOTE_ADDR");
		$cc_url .= "&x_Tran_Key=$trans_key";
		$cc_url .= "&x_Amount=$x_Amount";
		$cc_url .= "&x_Login=$username";
		$cc_url .= "&x_Cust_ID=1";
		$cc_url .= "&x_Card_Num=$card_num";
		$cc_url .= "&x_card_code=123";
		$cc_url .= "&x_Exp_Date=$x_Exp_Date";
		//------------------
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_REFERER, $_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"]);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $cc_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (($this->get('verify_peer')) ? 1 : 0));
		

		/* this is for godaddy
		 this may need to be worked into the base code
		 curl_setopt ($ch, CURLOPT_HTTPPROXYTUNNEL, TRUE);
		 curl_setopt ($ch, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
		 curl_setopt ($ch, CURLOPT_PROXY, "http://64.202.165.130:3128");
		 curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		 */

		//curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$authorizenet_result = curl_exec($ch);

		curl_close($ch);

		if ($authorizenet_result) {
			$result = "<!--THIS IS THE RAW CURL OUTPUT::\n".$authorizenet_result."\n\n";
			$result .= strlen($authorizenet_result)." is the string length of the data returned from curl\n-->";
			$result .= "<span style=\"color:green\">Success:</span> Connection with test data seems to be successful.<br />\n";
		} else {
			$result = "No result returned from the gateway. Make sure you entered the correct info in the fields above<br />\n";
			return $result;
		}


		$resultarray = explode("|", $authorizenet_result);

		if (count($resultarray) > 0) {
			foreach ($resultarray as $key => $value) {
				//$transaction_results[$resultFields[$key]] = $value;
				$temp .= self::$resultFields[$key]." = ".$value."<br>";
			}
			$result .= "This is the gateway's response:<br />\n".$temp."<br />\n";
		} else {
			$result = "Nothing returned from the gateway<BR />\n";
		}
		
		return $result;
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
		
		if (isset($_POST['authorizenet']) && is_array($_POST['authorizenet']) && count($_POST['authorizenet']) > 0){
			
			$set = $_POST['authorizenet'];
			
			$this->_updateCommonAdminOptions($set, true);
			$this->set('merchant_type', $set['merchant_type']);
			$this->set('connection_type', $set['connection_type']);
			if (geoMaster::is('auctions')) {
				$charge_final_fees = (isset($set['charge_final_fees']) && $set['charge_final_fees'])? 1: false;
				//first make sure they can charge final fees with selections
				if ($charge_final_fees && !$this->canAutoChargeFinalFees()) {
					geoAdmin::m('Cannot auto-charge final fees with the selected options.', geoAdmin::ERROR);
					$charge_final_fees = false;
				}
				
				$this->set('charge_final_fees', $charge_final_fees);
				if ($charge_final_fees) {
					$this->set('use_no_free_cart',((isset($set['use_no_free_cart']) && $set['use_no_free_cart'])? 1: false));
				}
			}
			if (geoPC::is_ent()) {
				$this->set('arb_payment_nolink_email', ((isset($set['arb_payment_nolink_email']) && $set['arb_payment_nolink_email'])? 1 : false));
				$this->set('arb_payment_nolink_cancel', ((isset($set['arb_payment_nolink_cancel']) && $set['arb_payment_nolink_cancel'])? 1 : false));
			}
			
			$this->set('verify_peer', ((isset($set['verify_peer']) && $set['verify_peer'])? 1: false));
			
			$this->set('merchant_login', $set['merchant_login']);
			//these 2 settings no longer used, be sure ot set them to false to remove them
			$this->set('send_password', false);
			$this->set('merchant_password', false);
			$this->set('transaction_key', $set['transaction_key']);
			$this->set('currency_code', $set['currency_code']);
			$this->set('email_customer', $set['email_customer']);
			$this->set('email_admin', $set['email_admin']);
			$this->set('secret', $set['secret']);
			//always use cvv2 code
			$this->set('use_cvv2', true);

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
		$merchant_type = $gateway->get('merchant_type');
		$connection_type = $gateway->get('connection_type');
		if($merchant_type==1 && $connection_type==1) {
			//using SIM method -- not direct CC processing
			$cart = geoCart::getInstance(); //get cart to use the display_help_link function
			//TODO: text
			$return = array(
				//Items that don't auto generate if left blank
				'title' => 'Authorize.net',
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
				'radio_tag' => ''
				);
			return $return;
		}
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
		//check for authorize.net SIM and don't do CC number checks if it's found
		if($gateway->get('merchant_type') == 1 && $gateway->get('connection_type') == 1) {
			return true;
		}
		
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
	public static function geoCart_payment_choicesProcess ()
	{
		trigger_error('DEBUG RECURRING ARB: top of proc');
		$cart = geoCart::getInstance();
		$db = $cart->db;
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		
		//get CC number and stuff
		$info = parent::_getInfo();
		
		if ($cart->db->get_site_setting('joe_edwards_discountLink')) {
			//special case to "hijack" seller email based on chosen discount code
			// (only used for Authorize.net and Nochex)
			
			//find the active discount_codes item, if there is one
			$items = $cart->order->getItem();
			$discount_item = null;
			foreach($items as $item) {
				if($item->getType() == 'addon_discount_codes') {
					$discount_item = $item;
					break;
				}
			}
			if($discount_item) {
				$je_result = $discount_item->joe_edwards_getEmail();
			}
			if($je_result && strlen($je_result) > 0) {
				$info['email'] = $je_result;
			}
		}
		
		//figure out if this is a recurring billing
		$recurringItem = ($gateway->isRecurring() && $cart->isRecurringCart())? $cart->item : false;
		$recurring = false;//start out false, will get set if needed.
		
		//get invoice on the order
		$invoice = $cart->order->getInvoice();
		$invoice_total = $due = $invoice->getInvoiceTotal();
		if ($due >= 0 && (!$gateway->get('charge_final_fees') || !$gateway->get('use_no_free_cart'))) {
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
			$startDate = $recurringItem->getRecurringStartDate();
		}
		
		$values['connection_type'] = $gateway->get('connection_type');
		
		if ($values["connection_type"] == 1) {
			if ($due >= 0) {
				//can't use this method with no charge
				return ;
			}
			$transaction = self::_createNewTransaction($cart->order, $gateway, $info);
		
			$msgs = $cart->db->get_text(true,183);
			$transaction->setDescription($msgs[500583]);
			
			$transaction->save();
			
			$values['merchant_type'] = $gateway->get('merchant_type');
			$values['verify_peer'] = $gateway->get('verify_peer');
			$values['merchant_login'] = $gateway->get('merchant_login');
			$values['transaction_key'] = $gateway->get('transaction_key');
			$values['currency_code'] = $gateway->get('currency_code');
			$values['email_customer'] = $gateway->get('email_customer');
			$values['email_admin'] = $gateway->get('email_admin');
			
			if ($gateway->get('testing_mode')) {
				//TESTING MODE
				$url = self::$_submitUrlTesting;
			} else {
				$url = self::$_submitUrl;
			}
			
			//do the SIM connection
			$currenttime = geoUtil::time();
			srand(geoUtil::time());
			$sequence = rand(1, 1000);

			$data = $values["merchant_login"] ."^". $sequence ."^". $currenttime ."^". $transaction->getAmount() ."^".$values['currency_code'];
			$key = $values["transaction_key"];
			$fingerprint = bin2hex(mhash(MHASH_MD5, $data, $key));
			$return_url = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=authorizenet",$db->get_site_setting("classifieds_url"));

			$cc_url = $url;
			$cc_url .= "?x_FP_Hash=".$fingerprint;
			$cc_url .= "&x_FP_Sequence=".$sequence;
			$cc_url .= "&x_FP_Timestamp=".$currenttime;
			$cc_url .= "&x_version=3.1";
			$cc_url .= "&x_Relay_Response=TRUE";
			$cc_url .= "&x_show_form=PAYMENT_FORM";
			$cc_url .= "&x_login=".$values['merchant_login'];
			$cc_url .= "&x_Relay_URL=".geoString::specialChars($return_url);
			$cc_url .= "&x_First_Name=".geoString::specialChars($info['firstname']);
			$cc_url .= "&x_Last_Name=".geoString::specialChars($info['lastname']);
			if (strlen(trim($info['company_name'])) > 0){
				$cc_url .= "&x_Company=".geoString::specialChars($info['company_name']);
			}
			$cc_url .= "&x_Address=".geoString::specialChars($info['address'].(($info['address2'])?' '.$info['address2']:''));
			$cc_url .= "&x_City=".geoString::specialChars($info['city']);
			$cc_url .= "&x_State=".geoString::specialChars($info['state']);
			$cc_url .= "&x_Country=".geoString::specialChars($info['country']);
			$cc_url .= "&x_Zip=".geoString::specialChars($info['zip']);
			$cc_url .= "&x_Phone=".geoString::specialChars($info['phone']);
			if ($info['business_type'] == 1) {
				$cc_url .= "&x_Customer_Organization_Type=I";
			} else {
				$cc_url .= "&x_Customer_Organization_Type=B";
			}
			if (strlen(trim($info["fax"])) > 0)
			$cc_url .= "&x_Fax=".geoString::specialChars($info["fax"]);
			$cc_url .= "&x_Cust_ID=".$transaction->getUser();
			if ($values["send_email_customer"])
			$cc_url .= "&x_Email_Customer=TRUE";
			if ($values["send_email_merchant"])
			$cc_url .= "&x_Merchant_Email=".geoString::specialChars($db->get_site_setting("site_email"));
			$cc_url .= "&x_PO_Num=".$invoice->getId();
			$cc_url .= "&x_Invoice_Num=".$transaction->getId();
			$cc_url .= "&x_Description=".geoString::specialChars($info["ad_type"]);
			$cc_url .= "&x_Amount=".$transaction->getAmount();
			$cc_url .= "&x_currency_code=".$values["currency_code"];
			//should already be figured into the base cost at this point...
			//$cc_url .= "&x_Tax=".$this->tax;
			$cc_url .= "&x_Email=".geoString::specialChars($info['email']);
			$cc_url .= "&x_customer_ip=".getenv("REMOTE_ADDR");
						
			$cart->order->setStatus('pending');
			
			//stop the cart session
			$cart->removeSession();
			
			require GEO_BASE_DIR . 'app_bottom.php';
			//go to 2checkout to complete
			header("Location: ".$cc_url);
			exit;
		} else if ($values["connection_type"] == 2) {
			//do the AIM connection
			
			if ($due >= 0) {
				//trying to charge final fees on a previously-completed auction
				
				if ($gateway->get('merchant_type') != 1) {
					//not able to auto charge final fees unless merchant type is 1
					
					return;
				}
				//run a authorize.net authorization, but don't charge anything
				$result = self::_processAim($info, $cart->order, true);
			} else {
				//normal, one-shot, seller-present transaction
				$result = self::_processAim($info, $cart->order, false, $recurring);
			}
			//processAim doesn't display the page, so we have to display page
			//here depending on what result was.
			if ($result) {
				//initial transaction successful!
				
				//now see if we want to make a recurring thingy
				//if we got here, we know the card is valid, because the one-shot transaction was good
				if ($recurringItem) {
					trigger_error('DEBUG RECURRING ARB: using Recurring!');
					//this is a recurring transaction
					//note: the isRecurring() function takes care of checking to see if we're actually using authorize.net and AIM
					
					//common stuff to most payment gateways:
					
					trigger_error('DEBUG RECURRING ARB: common recurring item ready. now goto ARB-specific stuff');
					//set up Automated Recurring Billing (requires a separate, cURL'd XML call)
					$result = self::_processARB($recurring, $gateway, $recurringItem, $info, $startDate);
					if (!$result) {
						//ARB initialization failed -- show failure page
						self::_successFailurePage(false, $cart->order->getStatus(), true, $invoice);
						return false;
					}
				} 
				
				//show success page
				self::_successFailurePage(true, $cart->order->getStatus(), true, $invoice, $transaction);
			} else {
				//failed!
				//_processAim already ran the failure page, don't need to show it again here
				return false;
			}
		} else {
			//it should not get here, unless settings are wrong.
			$transaction = self::_createNewTransaction($cart->order, $gateway, $info);
			self::_failure($transaction,self::FAIL_GENERAL_ERROR,"Internal Error: Gateway type not known.");
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
	 */
	public static function geoCart_process_orderDisplay(){
		//use to display some success/failure page, if that applies to this type of gateway.
		return parent::geoCart_process_orderDisplay();
	}
	
	public static function geoCart_payment_choicesDisplay_freeCart ()
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		if (!$gateway || !$gateway->get('charge_final_fees') || !$gateway->get('use_no_free_cart')) {
			//charge final fees turned off, or not forcing to auto-charge,
			//so don't auto charge final fees
			return false;
		}
		return true;
	}
	
	public static function auction_final_feesOrderItem_canAutoCharge ()
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		if (!$gateway->get('charge_final_fees') || !$gateway->get('use_no_free_cart')) {
			//charge final fees turned off, or not forcing to auto-charge,
			//so don't auto charge final fees
			return;
		}
		
		//make sure it is a good method
		if(!$gateway->canAutoChargeFinalFees()) {
			//wrong gateway type for doing this
			return;
		}
		
		//make it through all the checks, so can use this type
		return true;
	}
	/**
	 * Handle response from server (SIM method only)
	 */
	function transaction_process()
	{
		$response = $_POST;
		$transaction = geoTransaction::getTransaction($response['x_invoice_num']);
		$gateway = $transaction->getGateway();
		$order = $transaction->getInvoice()->getOrder();

		//store transaction data to registry
		$transaction->set("authorizenet_response", $response);
		$transaction->save();
		
		
		if($response['x_response_code'] == 1){
			//approved
			self::_success($order, $transaction, $gateway);
		} else {
			//declined or error
			self::_failure($transaction, $response['x_response_code'], $response['x_response_reason_text']);
		}
		
	}
	
	public static function auction_final_feesOrderItem_cron_close_listings ($vars)
	{
		$cron = geoCron::getInstance();
		$listing = $vars['listing'];
		if (!is_object($listing)) {
			//can't get cc info for original payment, so can't proceed...
			$cron->log('Cannot get listing object, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		$order = $vars['order'];
		if (!is_object($order)) {
			//could not get order info
			$cron->log('Cannot get order object, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		$invoice = $order->getInvoice();
		if (!is_object($invoice) || $invoice->getInvoiceTotal() >= 0) {
			//could not get invoice, or final fees appear to be paid for?
			$cron->log('Cannot get invoice object, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		//Note: no need to check if enabled, as this gateway would only be called
		//if it were already enabled.
		if (!$gateway->get('charge_final_fees')) {
			//charge final fees turned off, don't auto charge final fees
			$cron->log('Authorize.net does not process final fees, not proceeding further.',__line__.' - '.__file__);
			return;
		}
		
		if (!$gateway->canAutoChargeFinalFees()) {
			//not authorize.net or not aim.  If other merchant types can safely charge, can add the checks
			//for those here as well, but for now it only allows authorize.net.
			$cron->log('Wrong merchant or connection type, cannot process using authorize.net.',__line__.' - '.__file__);
			return;
		}
		$orig_item = ($listing->order_item_id)? geoOrderItem::getOrderItem($listing->order_item_id): 0;
		if (!$orig_item) {
			//could not get original order item
			$cron->log('Cannot get original order item, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		
		$orig_order = $orig_item->getOrder();
		if (!$orig_order) {
			//could not get original order
			$cron->log('Cannot get original order object, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		
		$orig_invoice = $orig_order->getInvoice();
		if (!$orig_invoice) {
			//could not get original invoice
			$cron->log('Cannot get original invoice object, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		//try to find transaction using our same gateway
		$transactions = $orig_invoice->getTransaction();
		$orig_transaction = null;
		foreach ($transactions as $transaction) {
			if (!is_object($transaction)) {
				continue;
			}
			if (!$transaction->getStatus()) {
				//we're not concerned about this disabled transaction
				continue;
			}
			$tGateway = $transaction->getGateway();
			
			if ($tGateway && $tGateway->getName() == self::gateway_name) {
				//this is the transaction we want!
				$orig_transaction = $transaction;
				break;
			}
		}
		if (!$orig_transaction) {
			//could not find original transaction, or perhaps original transaction was
			//not through authorize.net
			$cron->log('Cannot get original transaction object, vars: '.print_r($vars,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		
		//If this far, then we will be attempting to auto charge the CC for any final fees owed.
		
		$info = $orig_transaction->get('billing_info');
		$info['cc_number'] = self::_getCcNumber($orig_transaction);
		if (!self::validateCC($info['cc_number'])) {
			//possible problem getting CC number decrypted, not able to auto charge
			//the CC for final fees.
			$cron->log('Cannot get CC number, or number not valid, info: '.print_r($info,1)."\n not able to use authorize.net to process.",__line__.' - '.__file__);
			return;
		}
		$info['cvv2_code'] = $orig_transaction->get('cvv2_code');
		$cron->log('About to process order using aim, lets hope it goes through!',__line__.' - '.__file__);
		//un-comment for mroe debugging if needed, but it will output CC number so not a 
		//good idea to leave un-commented
		//$cron->log('Info sending to aim: '.print_r($info,1),__line__.' - '.__file__);
		return self::_processAim($info, $order);
	}
	
	private static function _processAim ($info, $order, $authOnly = false, $recurring = false)
	{
		$db = DataAccess::getInstance();
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		$storeCC = ($gateway->get('charge_final_fees'));
		$transaction = self::_createNewTransaction($order, $gateway, $info, $authOnly, $storeCC);
		
		if ($recurring) {
			//even though this is straight up payment, still attach the transaction
			//to the recurring billing so the initial payment shows for the recurring.
			$recurring->addTransaction($transaction);
		}
		
		$msgs = $db->get_text(true,183);
		if ($transaction->getAmount() == 0 && $authOnly) {
			//this would be an authorization only, so use description that notes that
			$transaction->setDescription($msgs[500633]);
		} else {
			//this is a "normal" transaction
			$transaction->setDescription($msgs[500583]);
		}

		$transaction->save();
		
		$values['merchant_type'] = $gateway->get('merchant_type');
		$values['connection_type'] = $gateway->get('connection_type');
		$values['verify_peer'] = $gateway->get('verify_peer');
		$values['merchant_login'] = $gateway->get('merchant_login');
		$values['transaction_key'] = $gateway->get('transaction_key');
		$values['currency_code'] = $gateway->get('currency_code');
		$values['email_customer'] = $gateway->get('email_customer');
		$values['email_admin'] = $gateway->get('email_admin');
		
		//do the AIM connection
		//post urls
		//TODO: Allow processing through any of the other "gateway URL's" with settings
		//to switch between different ones
		//authorize.net -- https://secure.authorize.net/gateway/transact.dll
		//planet payment -- https://secure.planetpayment.com/gateway/transact.dll
		//quickcommerce -- https://secure.quickcommerce.net/gateway/transact.dll
		
		if($values['merchant_type'] == 5){
			$post_url = self::$_submitUrlPaytrace;
		} elseif($values['merchant_type'] == 6) {
			$post_url = self::$_submitUrlEProcessingNetwork;
		} else {
			if ($gateway->get('testing_mode')) {
				//TESTING MODE
				$post_url = self::$_submitUrlTesting;
			} else {
				$post_url = self::$_submitUrl;
			}
		}
		
		$cc_url = "&x_First_Name=".urlencode($info['firstname']);
		$cc_url .= "&x_Last_Name=".urlencode($info['lastname']);
		if (strlen(trim($info['company_name'])) > 0){
			$cc_url .= "&x_Company=".urlencode($info['company_name']);
		}
		$cc_url .= "&x_Address=".urlencode($info['address']." ".$info['address2']);
		$cc_url .= "&x_City=".urlencode($info['city']);
		$cc_url .= "&x_State=".urlencode($info['state']);
		$cc_url .= "&x_Country=".urlencode($info['country']);
		$cc_url .= "&x_Zip=".urlencode($info['zip']);
		$cc_url .= "&x_Phone=".urlencode($info['phone']);
		
		$cc_url .= "&x_delim_data=TRUE";
		$cc_url .= "&x_Delim_Char=|";
		$cc_url .= "&x_Relay_Response=FALSE";
		//eprocessingnetwork.com settings
		//$cc_url .= "&x_Relay_Response=false&x_Relay_URL=false&x_Delim_Data=true&x_Delim_Char=|";

		
		$cc_url .= "&x_Email=".urlencode($info['email']);
		if ($info['business_type'] == 1) {
			$cc_url .= "&x_Customer_Organization_Type=I";
		} else {
			$cc_url .= "&x_Customer_Organization_Type=B";
		}
		if ($authOnly) {
			//this is just authorizing a charge
			$cc_url .= "&x_Type=AUTH_ONLY";
		} else {
			$cc_url .= "&x_Type=AUTH_CAPTURE";
		}
		if ($values["send_email_customer"] && !$authOnly) {
			$cc_url .= "&x_Email_Customer=TRUE";
		}
		if ($values["send_email_merchant"]) {
			$cc_url .= "&x_Merchant_Email=".urlencode($db->get_site_setting("site_email"));
		}
		$cc_url .= "&x_Description=".urlencode($info["ad_type"]);
		$cc_url .= "&x_Version=3.1";
		$cc_url .= "&x_Method=CC";
		$cc_url .= "&x_PO_Num=".$info["trans_id"];
		$cc_url .= "&x_Invoice_Num=".$transaction->getId();
		$cc_url .= "&x_Customer_IP=".getenv("REMOTE_ADDR");
		$cc_url .= "&x_Tran_Key=".$values["transaction_key"];
		if ($authOnly && $transaction->getAmount() == 0) {
			//we are authorizing only, try to authorize $1.00
			$amount = 1.00;
		} else {
			$amount = $transaction->getAmount();
		}
		$cc_url .= "&x_Amount=".$amount;
		$cc_url .= "&x_Login=".$values["merchant_login"];
		$cc_url .= "&x_Cust_ID=".$transaction->getUser();
		$cc_url .= "&x_Card_Num=".$info["cc_number"];
		$cc_url .= "&x_card_code=".$info["cvv2_code"];
		$cc_url .= "&x_Exp_Date=".sprintf("%02d",$info['exp_date']['Date_Month']).sprintf("%02d",$info['exp_date']['Date_Year']);
		
		trigger_error('DEBUG TRANSACTION AIM: About to do AIM connection. POST URL: '.$post_url.'?'.$cc_url);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_REFERER, $db->get_site_setting("classifieds_url"));
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $cc_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (($values['verify_peer']) ? 1 : 0));
		
		$authorizenet_result = curl_exec($ch);
		curl_close ($ch);
		trigger_error('DEBUG TRANSACTION: Raw results from authorize.net: '.$authorizenet_result);
		$notifyCart = (class_exists('geoCart',false))? true: false;
		if (!$authorizenet_result) {
			//let parent handle common failure stuffs
			$message = "No response from gateway server";
			self::_failure($transaction,self::FAIL_GENERAL_ERROR,$message, $notifyCart);
			return false;
		} else {
			//response returned -- process the results
			
			if($values['merchant_type'] == 5) {
				//how paytrace rolls
				$resultarray = explode(",", $authorizenet_result);
			} else {
				$resultarray = explode("|", $authorizenet_result);
			}
			if (count($resultarray) > 0) {

				foreach ($resultarray as $key => $value) {
					if (isset(self::$resultFields[$key])) {
						$transaction_results[self::$resultFields[$key]] = $value;
						$temp .= $resultFields[$key]." = ".$value."<br>\n";
					}
				}
				trigger_error('DEBUG TRANSACTION AIM: After AIM Connection. Response String: '.$temp);
				
				//store transaction data to registry for debugging later
				$transaction->set('transaction_results',$transaction_results);
				
				$transaction->save();
				
				if ($transaction_results["x_response_code"] == 1) {
					//Successful
					if ($authOnly) {
						//this was an authorization only, void it
						self::_processVoid($transaction_results['x_trans_id']);
					}
					
					//set payment_type - usually already set for us, unless inside final fees
					//which is possible since this is called to process final fees
					$order->set('payment_type',self::gateway_name);
					self::_success($order, $transaction, $gateway, true);
					return true;
				} else {
					$handler_error_response = ($transaction_results["x_response_reason_text"]) ? $transaction_results["x_response_reason_text"] : "INTERNAL FAILURE";
					self::_failure($transaction, $transaction_results['x_response_code'], $handler_error_response, $notifyCart);
					return false;
				}
			}
			
			//something's wrong if we get here
			$error = "Internal Error";
			if ($gateway->get('testing_mode')) {
				$error .= ", Error reported by gateway: $authorizenet_result";
			}
			self::_failure($transaction,self::FAIL_GENERAL_ERROR,$error, $notifyCart);
			return false;
		}
	}
	
	/**
	 * Use to void a auth-only AIM transaction, this is used as void-and-forget
	 * as the results are not checked.
	 * 
	 * @param string $trans_id the transaction ID for authorize.net to void
	 */
	private static function _processVoid($trans_id)
	{
		$db = DataAccess::getInstance();
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		
		//do the AIM connection
		if($gateway->get('merchant_type') == 5){
			$post_url = self::$_submitUrlPaytrace;
		} elseif($gateway->get('merchant_type') == 6) {
			$post_url = self::$_submitUrlEProcessingNetwork;
		} else {
			if ($gateway->get('testing_mode')) {
				//TESTING MODE
				$post_url = self::$_submitUrlTesting;
			} else {
				$post_url = self::$_submitUrl;
			}
		}
		
		$cc_url = "&x_type=VOID";
		$cc_url .= "&x_trans_id=".urlencode(trim($trans_id));
		$cc_url .= "&x_login=".$gateway->get('merchant_login');
		$cc_url .= "&x_tran_key=".$gateway->get('transaction_key');
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_REFERER, $db->get_site_setting("classifieds_url"));
		curl_setopt($ch, CURLOPT_URL, $post_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $cc_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (($gateway->get('verify_peer')) ? 1 : 0));
		
		$authorizenet_result = curl_exec($ch);
		curl_close ($ch);
	}

	/**
	 * Make the XML call to Authorize.net's Automated Recurring Billing process
	 * //TODO for consideration: make this be a common function for all calls to that API? (start/update/cancel)
	 *
	 * @param geoRecurringBilling $recurring the recurring billing item
	 * @param geoPaymentGateway $gateway reference to the gateway being used (do we really need this? dunno yet...)
	 * @param geoOrderItem $recurringItem the item that is recurring
	 * @param Array $info standard Geo billing data
	 */
	private static function _processARB($recurring, $gateway, $recurringItem, $info, $startDate)
	{
		ini_set('display_errors','stdout');
		trigger_error('DEBUG RECURRING ARB: top of ARB');
		
		//make a transaction for this subscription
		$cart = geoCart::getInstance();
		
		$msgs = $cart->db->get_text(true,183);
			
				
		$interval = $recurring->getCycleDuration(); //returns a value in seconds
		trigger_error('DEBUG RECURRING ARB: interval is: '.$interval.' (or '.($interval/60/60/24).' days)');
		$days = $interval / 60 / 60 / 24; //convert to days for gateway to accept it
		 //gateway accepts recurring period of at most 1 year and at least 1 week
		 //TODO: should probably try to add ability to make longer subscriptions
		if($days > 365) {
			$days = 365;
		} elseif($days < 7) {
			$days = 7;
		}
		trigger_error('DEBUG RECURRING ARB: going to recur every '.$days.' days');
		
		
		$time = geoUtil::time(); //get the time once, right now, so it doesn't change through calcs below
		$startDate = ($startDate && $startDate > geoUtil::time())? (int)$startDate: $time;
		$recurring->setStartDate($startDate);
		
		//The start date we send to authorize.net should have interval added,
		//since first payment processed using normal right-away payment
		$sendStartDate = $startDate + $interval;
		
		//ARB system validates times as US Mountain Time (-0600/-0700, depending on daylight savings)
		//need to convert local server time to that timezone
		
		$converter = new DateTime(date('r',$sendStartDate));
		$converter->setTimezone(new DateTimeZone('America/Denver'));
		$mountain_time = $converter->format('U');
		//having to do this conversion at all is pretty lame...lazy auth.net devs...I hope this works ^_*
		trigger_error('DEBUG RECURRING ARB: (mountain) start date we are sending is: '.date('r',$mountain_time).'<br />vs. current time: '.date('r',geoUtil::time()));
		
		$content =
        "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
        "<ARBCreateSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
	        "<merchantAuthentication>".
		        "<name>" . $gateway->get('merchant_login') . "</name>".
		        "<transactionKey>" . $gateway->get('transaction_key') . "</transactionKey>".
	        "</merchantAuthentication>".
			"<refId>" . substr(md5(rand()), 0, 20) . "</refId>". //this is really just a debugging checksum -- just make it a random string
	        "<subscription>".
		        //optional: "<name>" . $name . "</name>".
		        "<paymentSchedule>".
			        "<interval>".
				        "<length>". $days ."</length>".
				        "<unit>days</unit>".
			        "</interval>".
			        "<startDate>" . date('Y-m-d',$mountain_time) . "</startDate>".
			        "<totalOccurrences>9999</totalOccurrences>".
			        //optional (not implemented in Geo): "<trialOccurrences>". $trialOccurrences . "</trialOccurrences>".
		        "</paymentSchedule>".
		        "<amount>". $recurring->getPricePerCycle() ."</amount>".
		        //conditional (requires <trialOccurences> above): "<trialAmount>" . $trialAmount . "</trialAmount>".
		        "<payment>".
			        "<creditCard>".
				        "<cardNumber>" . $info['cc_number'] . "</cardNumber>".
				        "<expirationDate>" . sprintf("%04d",$info['exp_date']['Date_Year']) . '-'. sprintf("%02d",$info['exp_date']['Date_Month']) . "</expirationDate>".
			        "</creditCard>".
		        "</payment>".
		        "<customer>".
		        	"<id>{$recurring->getUserId()}</id>".
		        "</customer>".
		        "<billTo>".
			        "<firstName>". $info['firstname'] . "</firstName>".
			        "<lastName>" . $info['lastname'] . "</lastName>".
					//TODO: add more billing params (optional as far as the gateway is concerned, but may as well, since we've got the data...)
		        "</billTo>".
	        "</subscription>".
        "</ARBCreateSubscriptionRequest>";
		
		trigger_error('DEBUG RECURRING ARB: full XML of request is: <pre>'.htmlspecialchars($content).'</pre>');
		
		//send the xml via curl
		$host = $gateway->get('testing_mode') ? 'apitest.authorize.net' : 'api.authorize.net';
		$path = "/xml/v1/request.api";

		$posturl = "https://" . $host . $path;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		
		//don't care about HTTP headers, so shorten the response to only be the xml string
		$response = substr($response, stripos($response, '<?xml'));
		$xml = simplexml_load_string($response);
		
		trigger_error('DEBUG RECURRING ARB: raw response string: '.$response);
		trigger_error('DEBUG RECURRING ARB: parsed XML: '.print_r($xml,1));
		
		//this is the way it SHOULD work, according to auth.net docs
		//if resultCode is 'Ok,' transaction is good.
		$success = ($xml->messages->resultCode.'' === 'Ok');
		
		trigger_error("DEBUG RECURRING ARB: result of 'documented' success check: ".$success." resutl code: {$xml->messages->resultCode}");
		
		//this is the way we're hacking it to make it ACTUALLY work
		//since auth.net doesn't do things the way they say they do
		//NOTE: this removed, not sure why thought it didn't work...
		//$success = $xml->messages->message->code == 'I00001' ? true : false;
		
		
		//these nodes give more detail as to why the transaction succeeded/failed
		//log them into the DB
		$code = $xml->messages->message->code;
		$text = $xml->messages->message->text;
		$resultString = "$code :: $text";
		
		$recurring->set('result_string', $resultString);
		$recurring->set('xml_init_response', $response);
		
		//this is the ID to use in future communication with the ARB servers to identify this subscription
		//(prefix with anet_ because it has to be a string due to setSecondaryId limitations)
		if ($xml->subscriptionId) {
			$recurring->setSecondaryId('anet_'.$xml->subscriptionId);
		}
		
		if ($success) {
			//successfully created!
			$paidUntil = $interval + $startDate;
			$recurring->setPaidUntil((int)$paidUntil);
			$recurring->setStatus(geoRecurringBilling::STATUS_ACTIVE);
		}
		$recurring->save();
		
		return true;

	}
	
	/**
	 * 
	 * Listens for and processes Silent POST messages that come in nightly for the day's ARB transactions.
	 * note: messages will be received for ALL transactions (including non-ARB)
	 *       need to look for the subscriptionId field to make sure there's stuff to do
	 */
	
	public function recurring_process ()
	{
		$post = array();
		foreach($_POST as $key => $value) {
			//urldecode the response, so it's meaningful
			//NOT geoString::fromDB, because that may change independant of auth.net response values
			$post[$key] = urldecode($value);
		}
		if(!isset($post['x_subscription_id']) || !$post['x_subscription_id']) {
			//this is not an ARB transaction -- nothing to do here
			trigger_error("DEBUG RECURRING ARB: no x_subscription_id so this is most likely not ARB transaction.");
			return;
		}
		
		$trans_id = $post['x_trans_id'];
		
		if (!self::_validateHash($this, $trans_id, $post['x_amount'], $post['x_MD5_Hash'])) {
			//md5 check failed -- this request probably did not originate from auth.net (or things are misconfigured)
			trigger_error("DEBUG RECURRING ARB: Secret Key does not match!  Ignoring signal, either this is fake signal or settings not set correctly.");
			return;
		}
		
		trigger_error("DEBUG RECURRING ARB: hash check validated, proceeding to log the transaction
				and such..");
		
		$recurring = geoRecurringBilling::getRecurringBilling('anet_'.$post['x_subscription_id']);
		
		$db = DataAccess::getInstance();
		if ((!$recurring || $recurring->getId() == 0) && $post['x_subscription_id']) {
			//nothing we can do without the recurring item
			trigger_error('ERROR RECURRING ARBEXP: Could not retrieve recurring item with ID '.$post['x_subscription_id']."\n\nFull info for non-found recurring billing:\n".print_r($post,1));
			
			if ($post['x_response_code'] == 1 && !in_array($post['x_type'], array('AUTH_ONLY','VOID'))) {
				//This is a valid payment on something we are not able to link to
				//something within the software!
				if ($this->get('arb_payment_nolink_email')) {
					//e-mail the admin
					trigger_error("DEBUG RECURRING ARBEXP: e-mailing admin about this, as per settings.");
					$subject = "AUTOMATED NOTICE:  Recurring billing not linked!";
					$tpl = new geoTemplate(geoTemplate::ADMIN);
					$tpl->assign('gateway', $this->getName());
					$tpl->assign('post', $post);
					$contents = $tpl->fetch('emails/nonlinked_recurring_billing.tpl');
					$to = $db->get_site_setting('site_email');
					geoEmail::sendMail($to, $subject, $contents,0,0,0,'text/html');
					unset($tpl, $contents);
				}
				
				if ($this->get('arb_payment_nolink_cancel')) {
					//send signal to cancel the subscription automatically
					trigger_error("DEBUG RECURRING ARBEXP: auto-canceling the subscription, as per settings.");
					$content =
					"<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
					"<ARBCancelSubscriptionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
					"<merchantAuthentication>".
					"<name>" . $this->get('merchant_login') . "</name>".
					"<transactionKey>" . $this->get('transaction_key') . "</transactionKey>".
					"</merchantAuthentication>".
					"<refId>" . substr(md5(rand()), 0, 20) . "</refId>". //this is really just a debugging checksum -- just make it a random string
					"<subscriptionId>" . $post['x_subscription_id'] . "</subscriptionId>".
					"</ARBCancelSubscriptionRequest>";
					
					$xml = self::_sendSimpleRequest($content, $this);
					trigger_error("DEBUG RECURRING ARBEXP: result of cancel signal: \n".print_r($xml,1));
				}
			}
			
			return;
		}
		
		$transaction = new geoTransaction;
		$transaction->setDate(geoUtil::time());
		$transaction->setGateway($recurring->getGateway());
		$transaction->setUser($recurring->getUserId());
		$transaction->setRecurringBilling($recurring);
		
		$transaction->setGatewayTransaction('anet_'.$trans_id);
		
		//gateway-specific stuff
		$transaction->set('x_response_code',$post['x_response_code']);
		$transaction->set('x_auth_code', $post['x_auth_code']);
		$transaction->set('x_type', $post['x_type']);
		//it should always match up with user ID but don't check it, just set it for debugging
		$transaction->set('x_cust_id', $post['x_cust_id']);
		$transaction->set('x_test_request', $post['x_test_request']);
		
		if ($post['x_response_code'] == 1 && !in_array($post['x_type'], array('AUTH_ONLY','VOID'))) {
			//payment good!  Set stuff specific to when payment made...
			trigger_error("DEBUG RECURRING ARB: recurring billing successful!  Marking it as such.");
			
			$transaction->setAmount($post['x_amount']);
			$transaction->setStatus(1);
			
			$msgs = $db->get_text(true,183);
				
			$transaction->setDescription($msgs[500749]);
			$transaction->set('x_subscription_paynum', $post['x_subscription_paynum']);
			
			$transaction->save();
			$recurring->processPayment($transaction);
		} else if ($post['x_response_code'] != 1) {
			//recurring failed!
			trigger_error("DEBUG RECURRING ARB: recurring billing failed!  response code: {$post['x_response_code']} full post info: \n".print_r($post,1)."\n-------------------\n\n");
			$codes = array (
					2 => 'Declined',
					3 => 'Error',
					4 => 'Held for Review',
					);
			$transaction->setDescription("Recieved  signal {$post['x_response_code']} ({$codes[$post['x_response_code']]}), reason: {$post['x_response_reason_code']} - {$post['x_response_reason_text']}");
			$transaction->setStatus(0);
			
			$transaction->save();
			$recurring->addTransaction($transaction);
			
			//cancel recurring...
			$reason = 'ended by gateway with code: '.$post['x_response_reason_code'].' :: '.$post['x_response_reason_text'];
			$recurring->cancel($reason, true);
		}
		$recurring->save();
		return true;
	}
	
	/**
	 * Returns based on ability of current gateway and connection type to auto-charge Final Fees
	 * @return bool
	 * @since 7.3.0
	 */
	private function canAutoChargeFinalFees()
	{
		$gateway = $this->get('merchant_type');
		$method = $this->get('connection_type');
		if(($gateway == 1 && $method == 2) || $gateway == 6) {
			//this is Authorize.NET AIM or eProcessingNetwork
			return true;
		} else {
			//cannot auto-charge final fees for this gateway and method
			return false;
		}
	}
	
	/**
	 * Validates the hash passed in from the silent post
	 * 
	 * @param authorizenetPaymentGateway $gateway
	 * @param string $trans_id
	 * @param string $amount
	 * @param string $md5_hash
	 * @return boolean true if hash is valid, false otherwise
	 */
	private static function _validateHash ($gateway, $trans_id, $amount, $md5_hash)
	{
		$secret = $gateway->get('secret');
		
		$amount = number_format($amount, 2, '.', '');
		
		return (strtoupper(md5($secret.$trans_id.$amount)) === ''.$md5_hash);
	}
}
