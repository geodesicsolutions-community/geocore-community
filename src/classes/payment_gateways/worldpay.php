<?php
//worldpay.php
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
## ##    7.5.3-162-g43f8bd7
## 
##################################

/*//availabel settings ($this->get(setting_name); (handy)
worldpay_installation_id
worldpay_currency_type
worldpay_test_mode
worldpay_callback_password*/
require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Cash payment gateway handler

class worldpayPaymentGateway  extends geoPaymentGateway{
	
	const gateway_name = 'worldpay';
	public $name = self::gateway_name;//make it so that name is known.
	public $type = 'worldpay';
	
	//There is no "sandbox" url, but still define URL here to make gateways the same..
	const worldpay_url = 'https://select.worldpay.com/wcc/purchase';
	
	//Currency list, pulled from:
	//http://www.worldpay.com/support/integrations/pro/help/spig12300.html
	//With duplicates removed (on page it goes by country)
	private static $_currency = array (
		'AFA' => 'Afghani',
		'ALL' => 'Lek',
		'DZD' => 'Algerian Dinar',
		'AON' => 'New Kwanza',
		'ARS' => 'Argentine Peso',
		'AWG' => 'Aruban Guilder',
		'AUD' => 'Australian Dollar',
		'EUR' => 'Euro',
		'BSD' => 'Bahamian Dollar',
		'BHD' => 'Bahraini Dinar',
		'BDT' => 'Taka',
		'BBD' => 'Barbados Dollar',
		'BZD' => 'Belize Dollar',
		'BMD' => 'Bermudian Dollar',
		'BOB' => 'Boliviano',
		'BAD' => 'Bosnian Dinar',
		'BWP' => 'Pula',
		'BRL' => 'Real',
		'BND' => 'Brunei Dollar',
		'BGL' => 'Lev',
		'XOF' => 'CFA Franc BCEAO',
		'BIF' => 'Burundi Franc',
		'KHR' => 'Cambodia Riel',
		'XAF' => 'CFA Franc BEAC',
		'CAD' => 'Canadian Dollar',
		'CVE' => 'Cape Verde Escudo',
		'KYD' => 'Cayman Islands Dollar',
		'CLP' => 'Chilean Peso',
		'CNY' => 'Yuan Renminbi',
		'COP' => 'Colombian Peso',
		'KMF' => 'Comoro Franc',
		'CRC' => 'Costa Rican Colon',
		'HRK' => 'Croatian Kuna',
		'CUP' => 'Cuban Peso',
		'CYP' => 'Cyprus Pound',
		'CZK' => 'Czech Koruna',
		'DKK' => 'Danish Krone',
		'DJF' => 'Djibouti Franc',
		'XCD' => 'East Caribbean Dollar',
		'DOP' => 'Dominican Peso',
		'TPE' => 'Timor Escudo',
		'ECS' => 'Ecuador Sucre',
		'EGP' => 'Egyptian Pound',
		'SVC' => 'El Salvador Colon',
		'EEK' => 'Kroon',
		'ETB' => 'Ethiopian Birr',
		'FKP' => 'Falkland Islands Pound',
		'FJD' => 'Fiji Dollar',
		'XPF' => 'CFP Franc',
		'GMD' => 'Dalasi',
		'GHC' => 'Cedi',
		'GIP' => 'Gibraltar Pound',
		'GTQ' => 'Quetzal',
		'GNF' => 'Guinea Franc',
		'GWP' => 'Bissau Peso',
		'GYD' => 'Guyana Dollar',
		'HTG' => 'Gourde',
		'HNL' => 'Lempira',
		'HKD' => 'Hong Kong Dollar',
		'HUF' => 'Forint',
		'ISK' => 'Iceland Krona',
		'INR' => 'Indian Rupee',
		'IDR' => 'Rupiah',
		'IRR' => 'Iranian Rial',
		'IQD' => 'Iraqi Dinar',
		'ILS' => 'Shekel',
		'JMD' => 'Jamaican Dollar',
		'JPY' => 'Yen',
		'JOD' => 'Jordanian Dinar',
		'KZT' => 'Tenge',
		'KES' => 'Kenyan Shilling',
		'KRW' => 'Won',
		'KPW' => 'North Korean Won',
		'KWD' => 'Kuwaiti Dinar',
		'KGS' => 'Som',
		'LAK' => 'Kip',
		'LVL' => 'Latvian Lats',
		'LBP' => 'Lebanese Pound',
		'LSL' => 'Loti',
		'LRD' => 'Liberian Dollar',
		'LYD' => 'Libyan Dinar',
		'LTL' => 'Lithuanian Litas',
		'MOP' => 'Pataca',
		'MKD' => 'Denar',
		'MGF' => 'Malagasy Franc',
		'MWK' => 'Kwacha',
		'MYR' => 'Malaysian Ringitt',
		'MVR' => 'Rufiyaa',
		'MTL ' => 'Maltese Lira',
		'MRO' => 'Ouguiya',
		'MUR' => 'Mauritius Rupee',
		'MXN' => 'Mexico Peso',
		'MNT' => 'Mongolia Tugrik',
		'MAD' => 'Moroccan Dirham',
		'MZM' => 'Metical',
		'MMK' => 'Myanmar Kyat',
		'NAD' => 'Namibian Dollar',
		'NPR' => 'Nepalese Rupee',
		'ANG' => 'Netherlands Antilles Guilder',
		'NZD' => 'New Zealand Dollar',
		'NIO' => 'Cordoba Oro',
		'NGN' => 'Naira',
		'NOK' => 'Norwegian Krone',
		'OMR' => 'Rial Omani ',
		'PKR' => 'Pakistan Rupee',
		'PAB' => 'Balboa',
		'PGK' => 'New Guinea Kina',
		'PYG' => 'Guarani',
		'PEN' => 'Nuevo Sol',
		'PHP' => 'Philippine Peso',
		'PLN' => 'New Zloty',
		'QAR' => 'Qatari Rial',
		'ROL' => 'Leu',
		'RUB' => 'Russian Ruble',
		'RWF' => 'Rwanda Franc',
		'WST' => 'Tala',
		'STD' => 'Dobra',
		'SAR' => 'Saudi Riyal',
		'SCR' => 'Seychelles Rupee',
		'SLL' => 'Leone',
		'SGD' => 'Singapore Dollar',
		'SKK' => 'Slovak Koruna',
		'SIT' => 'Tolar',
		'SBD' => 'Solomon Islands Dollar',
		'SOS' => 'Somalia Shilling',
		'ZAR' => 'Rand',
		'LKR' => 'Sri Lanka Rupee',
		'SHP' => 'St Helena Pound',
		'SDP' => 'Sudanese Pound',
		'SRG' => 'Suriname Guilder',
		'SZL' => 'Swaziland Lilangeni',
		'SEK' => 'Sweden Krona',
		'CHF' => 'Swiss Franc',
		'SYP' => 'Syrian Pound',
		'TWD' => 'New Taiwan Dollar',
		'TJR' => 'Tajik Ruble',
		'TZS' => 'Tanzanian Shilling',
		'THB' => 'Baht',
		'TOP' => 'Tonga Pa\'anga',
		'TTD' => 'Trinidad &amp; Tobago Dollar',
		'TND' => 'Tunisian Dinar',
		'TRY' => 'Turkish Lira',
		'UGX' => 'Uganda Shilling',
		'UAH' => 'Ukrainian Hryvnia',
		'AED' => 'United Arab Emirates Dirham',
		'GBP' => 'Pounds Sterling',
		'USD' => 'US Dollar',
		'UYU' => 'Uruguayan Peso',
		'VUV' => 'Vanuatu Vatu',
		'VEB' => 'Venezuela Bolivar',
		'VND' => 'Viet Nam Dong',
		'YER' => 'Yemeni Rial',
		'YUM' => 'Yugoslavian New Dinar',
		'ZRN' => 'New Zaire',
		'ZMK' => 'Zambian Kwacha',
		'ZWD' => 'Zimbabwe Dollar',
	);
	
	/**
	 * Expects to return an array:
	 * array (
	 * 	'' => ''
	 * )
	 *
	 */
	function admin_display_payment_gateways (){
		$return = array (
			'name' => self::gateway_name,
			'title' => 'WorldPay',
		);
		return $return;
	}
	
	/**
	 * Called NON-STATIC (using $gateway->function_name() )
	 * 
	 * If this function exists, it will be used to display custom
	 * settings specific for this gateway.  If the function does not
	 * exist, no settings button will be displayed beside the gateway.
	 *
	 * @return HTML to display below gateway when user clicked the settings button
	 */
	function admin_custom_config ()
	{
		$db = DataAccess::getInstance();
		
		$options = '';
		$current_currency = $this->get('currency');
		if (!$current_currency) {
			$current_currency = 'USD';
			$this->set('currency','USD');//default to USD
		}
		$count = 0;
		ksort(self::$_currency);
		foreach (self::$_currency as $key=> $currency) {
			$selected = ($current_currency == $key)? ' selected="selected"': '';
			$options .= "
				<option value='$key'$selected>$key - $currency</option>";
		}
		
		$tpl = new geoTemplate('admin');
		$tpl->assign('payment_type', self::gateway_name);
		
		$tooltips['installation_id'] = geoHTML::showTooltip('Worldpay Installation ID','This the id you were given by Worldpay. This id will identify a user you submit to Worldpay to collect funds from.');
		$tooltips['callback_password'] = geoHTML::showTooltip('Worldpay Callback Password','This is the password that you can optionally set at Worldpay to verify that Worldpay is the one returning an authorization. You can leave this field blank and the password will not be checked on any Worldpay callback procedures.');
		$tpl->assign('tooltips', $tooltips);
		
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());
		
		$values['installation_id'] = geoString::specialChars($this->get('installation_id'));
		$tpl->assign('currency_options',$options);
		$values['callback_password'] = geoString::specialChars($this->get('callback_password'));
		$tpl->assign('values', $values);
		
				
		return $tpl->fetch('payment_gateways/worldpay.tpl');
	}
	
	/**
	 * Called NON-STATICALLY
	 * 
	 * Optional function, should update any settings if applicable.
	 * 
	 * Note that this is done IN ADDITION TO the normal "back-end" stuff such as enabling or disabling the
	 * gateway and serializing any changes.  If this returns false however, that additional stuff 
	 * will not be done.
	 *
	 * @return boolean True to continue with rest of update stuff, false to prevent saving rest of settings
	 *  for this gateway.
	 */
	function admin_update_payment_gateways(){
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
			$this->set('installation_id',trim($settings['installation_id']));
			$this->set('currency', $settings['currency']);
			$this->set('callback_password',trim($settings['callback_password']));
			$this->serialize();
		}
	
		
		return true;
	}
	
	public static function geoCart_payment_choicesDisplay(){
		$cart = geoCart::getInstance();
		//TODO: checks for using balance
		
		$msgs = $cart->db->get_text(true, 10203);
		$return = array(
			//Items that don't auto generate if left blank
			'title' => $msgs[500291],
			'title_extra' => '',
			'label_name' => self::gateway_name,
			'radio_value' => self::gateway_name,//should be same as gateway name
			'help_link' => $cart->site->display_help_link(865),
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
	
	public static function geoCart_payment_choicesProcess(){
		
		$cart = geoCart::getInstance();
		$user_data = $cart->user_data;
		
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		
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
		$transaction->setDescription($msgs[500574]);
		$transaction->setGatewayTransaction($cart->session->getSessionId());
		$transaction->setInvoice($invoice);
			
		$transaction->save();
		
		$db =& DataAccess::getInstance();
		
		$return_url = str_replace($db->get_site_setting("classifieds_file_name"), "transaction_process.php?gateway=".self::gateway_name,$db->get_site_setting("classifieds_url"));
		$return_url = str_replace("http://", "",$return_url);
		
				
		$worldpay_url = self::worldpay_url."?";
		$worldpay_url .=  "instId=".$gateway->get("installation_id");
		$worldpay_url .=  "&cartId=".$transaction->getId();
		$worldpay_url .=  "&M_sessionId=".$transaction->getGatewayTransaction();
		$worldpay_url .=  "&M_customerId=".$transaction->getUser();
		$worldpay_url .=  "&MC_callback=".$return_url;
		$worldpay_url .=  "&name=".urlencode($user_data['firstname']." ".$user_data['lastname']);
		if (strlen(trim($user_data['address'])) > 0)
			$worldpay_url .=  "&address=".urlencode($user_data['address']." ".$user_data['address_2']);
		if (strlen(trim($user_data['country'])) > 0)
			$worldpay_url .=  "&country=".urlencode($user_data['country']);
		if (strlen(trim($user_data['zip'])) > 0)
			$worldpay_url .=  "&postcode=".urlencode($user_data['zip']);
		if (strlen(trim($user_data['phone'])) > 0)
			$worldpay_url .=  "&tel=".urlencode($user_data['phone']);
		if (strlen(trim($user_data['email'])) > 0)
			$worldpay_url .=  "&email=".urlencode($user_data['email']);
		$worldpay_url .=  "&amount=".number_format($transaction->getAmount(), 2, '.', '');
		$worldpay_url .=  "&currency=".urlencode($gateway->get("currency"));
		$worldpay_url .=  "&desc=".urlencode($ad_type);
		if ($gateway->get("testing_mode"))
			$worldpay_url .=  "&testMode=100";

			
		//remember what the URL we sent them to was, for debugging if needed
		$transaction->set('worldpay_url',$worldpay_url);
		$transaction->save();
		
		//add transaction to invoice
		$invoice->addTransaction($transaction);
		
		//set order to pending
		$cart->order->setStatus('pending');
		
		//stop the cart session
		$cart->removeSession();
		
		require GEO_BASE_DIR . 'app_bottom.php';
		header("Location: ".$worldpay_url);
		exit;
	}
	

	/**
	 * Called NON-STATICALLY
	 * 
	 * Called from file /transaction_process.php - this function should
	 * be used when expecting some sort of processing to take place where
	 * the external gateway needs to contact the software back (like Paypal IPN)
	 * 
	 * It is up to the function to verify everything.
	 *
	 */
	public function transaction_process(){
		//treat as a robot, to avoid redirection or cookie issues.
		//shouldn't need to do this anymore
		//define('IS_ROBOT',true);		
		
		//transId
		//transStatus
		//	Y - successful
		// 	C - cancelled
		//transTime
		//authAmount
		//authCurrency
		//authAmountString
		//rawAuthMessage
		//rawAuthCode
		//callbackPW
		//cardType
		//countryString
		//countryMatch
		//	Y - match
		//	N - no match
		//	B - comparison not available
		//	I - contact country not supplied
		//	S - card issue country not available
		//AVS
		//	1234
		//	1 - card verification
		//	2 - postcode AVS check
		//	3 - address AVS check
		//	4 - country comparison check
		//	values
		//		0 - not supported
		//		1 - not checked
		//		2 - matched
		//		4 - not matched
		//		8 - partially matched
		//cartId
		//M_sessionId
		//M_customerId
		//name
		//address
		//postcode
		//country
		//tel
		//fax
		//email
		//amount
		//currency
		//description
		
		trigger_error('DEBUG TRANSACTION: start worldpay transaction process');
		
		$response = $_POST;
		
		//Check to make sure this is valid
		if (!($response["cartId"]) && ($response["M_customerId"]))
		{
			//Not stuff returned
			return;
		}
		
		if (strlen(trim($this->get("callback_password"))) > 0)
		{
			if ($this->get("callback_password") != $response["callbackPW"])
			{
				//password does not match
				return false;
			}
		}
		
		//transaction id is saved by "cartId"
		$trans_id = intval($response["cartId"]);
		$transaction =& geoTransaction::getTransaction($trans_id);
		trigger_error('DEBUG TRANSACTION: paypal:transaction_process() - right AFTER - transaction: '.print_r($transaction,1));
		
		//save response data
		$transaction->set('worldpay_response', $response);
		$transaction->save();
		
		//make sure all of transaction info matches with what was passed back.
		if ($transaction->getUser() != $response["M_customerId"]){
			//something is wrong, do not proceed
			trigger_error('ERROR TRANSACTION: Invalid user set for transaction: '.$trans_id);
			return;
		}
		if ($transaction->getGatewayTransaction() != $response["M_sessionId"]){
			//something is wrong, do not proceed
			trigger_error('ERROR TRANSACTION: Invalid session id set for transaction: '.$trans_id);
			return;
		}
		if ($transaction->getAmount() != $response["authAmount"] || $transaction->getStatus() ){
			//something is wrong, do not proceed
			trigger_error('ERROR TRANSACTION: Invalid transaction data returned for transaction: '.$trans_id);
			return;
		}
		
		//worldpay transloads whatever result page is shown onto their own server, and displays it without CSS for "security."
		//it *does* complete this POST, though, so we can go ahead right now and mark the transaction as success/failed in the database
		//but set our normal success/failure functions to not render the page -- instead echo just a redirect to transaction_result.php to return the user fully to the local site
		
		if ($response["transStatus"] == "C") {
			//cancelled -- fail
			self::_failure($transaction, $response["transStatus"], "Worldpay said: ".$response['rawAuthMessage'], true);
		}
		elseif ($response["transStatus"] != "Y") {
			//fail
			self::_failure($transaction, $response["transStatus"], "Worldpay said: ".$response['rawAuthMessage'], true);
		} else {
			//success
			self::_success($transaction->getInvoice()->getOrder(), $transaction, geoPaymentGateway::getPaymentGateway(self::getType()), true);
		}		
		
		$db = DataAccess::getInstance();
		$target = str_replace($db->get_site_setting('classifieds_file_name'), 'transaction_result.php?transaction='.$transaction->getId(), $db->get_site_setting('classifieds_url'));
		echo '<meta http-equiv="refresh" content="1; url='.$target.'">';
	}
	
}