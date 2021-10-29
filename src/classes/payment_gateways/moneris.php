<?php
//payment_gateways/moneris.php
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
## ##    7.3beta2-102-g09573a0
## 
##################################

require_once CLASSES_DIR . 'payment_gateways/_cc.php';

//Moneris provides their own class file for connecting to their server.
require_once GEO_BASE_DIR.'mpgClasses.php';

# Template CC payment gateway handler

class monerisPaymentGateway extends _ccPaymentGateway{
	/**
	 * Required, the name of this gateway, should be the same as the file name without the .php
	 *
	 * @var string
	 */
	public $name = 'moneris';
	
	/**
	 * For convenience, should be same as $name
	 *
	 */
	const gateway_name = 'moneris';
	
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
			'title' => 'CC - Moneris',//how it's displayed in admin
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
	 * exist, no settings button will be displayed beside the gateway.
	 *
	 * @return string HTML to display below gateway when user clicked the settings button
	 */
	public function admin_custom_config (){
		$tpl = new geoTemplate('admin');
		$tpl->assign('payment_type', self::gateway_name);
		$tpl->assign('commonAdminOptions', $this->_showCommonAdminOptions());
		
		$tooltips['store_id'] = geoHTML::showTooltip('Store ID', 'Enter the Store ID that corresponds to your Moneris account');
		$tooltips['api_token'] = geoHTML::showTooltip('API Token', 'Enter the API Token that corresponds to your Moneris account');
		$tooltips['crypttype'] = geoHTML::showTooltip('Crypt Type', 'Enter the value of the crypttype variable that will be used for Moneris transactions. <strong>In most cases, this value needs to be 7</strong>.');
		$tpl->assign('tooltips', $tooltips);
		
		$values['store_id'] = $this->get('store_id');
		$values['api_token'] = $this->get('api_token');
		$values['crypttype'] = $this->get('crypttype', 7);
		$tpl->assign('values', $values);
		
		return $tpl->fetch('payment_gateways/moneris.tpl');
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
		if (isset($_POST['moneris']) && is_array($_POST['moneris']) && count($_POST['moneris']) > 0){
			$settings = $_POST['moneris'];
			$this->_updateCommonAdminOptions($settings);
			$this->set('store_id', $settings['store_id']);
			$this->set('api_token', $settings['api_token']);
			$this->set('crypttype', $settings['crypttype']);
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
		//get vars
		$cart = geoCart::getInstance();
		$user_data = $cart->user_data;
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		$invoice = $cart->order->getInvoice();
		$invoice_total = $invoice->getInvoiceTotal();
		
		$testing = $gateway->get('testing_mode');

		if ($invoice_total >= 0){
			//DO NOT PROCESS!  Nothing to process, no charge (or returning money?)
			return ;
		}
		//BUILD DATA TO SEND TO GATEWAY TO COMPLETE THE TRANSACTION
		$info = parent::_getInfo();

		//create initial transaction
		try {
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

		$store_id = $gateway->get('store_id');
		$customerid = $user_data['id'];
		$api_token = $gateway->get('api_token');
		$order_id = "TRANSID: ".$transaction->getId()." SEED: ".microtime();
		$pan = $info["cc_number"];
		$amount = $transaction->getAmount();
		$expirydate = substr(sprintf("%02d",$info['exp_date']['Date_Year']),2,2).sprintf("%02d",$info['exp_date']['Date_Month']);
		
		//amount needs to be always sent with 2 decimal places
		$amount = sprintf("%01.2f",$amount);
		
		if($testing) {
			$pan = "5454545454545454";
			$expirydate = date('ym', (time() + 31536000));
		}
		
		$crypttype = $gateway->get('crypttype');

		$txnArray = array(
					'type' => 'purchase',
					'order_id' => $order_id,
					'cust_id' => $customerid,
					'amount' => $amount,
					'pan' => $pan,
					'expdate' => $expirydate,
					'crypt_type' => $crypttype
		);

		$mpgTxn = new mpgTransaction($txnArray);
		$mpgRequest = new mpgRequest($mpgTxn);
		
		trigger_error('DEBUG MONERIS: txnArray is: '.print_r($txnArray, true));
		
		$mpgHttpPost = new mpgHttpsPost($store_id,$api_token,$mpgRequest);
		$mpgResponse = $mpgHttpPost->getMpgResponse();

		$response = array(
					'card_type' => $mpgResponse->getCardType(),
					'trans_amount' => $mpgResponse->getTransAmount(),
					'txn_number' => $mpgResponse->getTxnNumber(),
					'receipt_id' => $mpgResponse->getReceiptId(),
					'trans_type' => $mpgResponse->getTransType(),
					'reference_num' => $mpgResponse->getReferenceNum(),
					'response_code' => $mpgResponse->getResponseCode(),
					'iso' => $mpgResponse->getISO(),
					'message' => $mpgResponse->getMessage(),
					'auth_code' => $mpgResponse->getAuthCode(),
					'complete' => $mpgResponse->getComplete(),
					'trans_date' => $mpgResponse->getTransDate(),
					'trans_time' => $mpgResponse->getTransTime(),
					'ticket' => $mpgResponse->getTicket(),
					'timed_out' => $mpgResponse->getTimedOut()
		);
		
		//store transaction data to registry
		$transaction->set('moneris_response', $response);
		$transaction->save();

		if((($response['response_code'] == NULL) || ($response['response_code'] === false)))
		{
			//Failure -- no response from gateway
			$message = 'Transaction Failed: No response from server.';
			trigger_error('ERROR TRANSACTION CART '.self::gateway_name.': '.$message);
				
			return self::_failure($transaction,self::FAIL_GATEWAY_CONNECTION,$message);
		}

		if ($response['response_code'] !== 'null' && $response['response_code'] >= 0 && $response['response_code'] < 50){
			//Success
			trigger_error('DEBUG TRANSACTION CART '.self::gateway_name.': no errors, payment good!');
			
			return self::_success($cart->order,$transaction, $gateway);
		} else {
			//Failure -- Declined by gateway
			$message = 'Transaction Failed with code '.$response['response_code'].'. Response message from gateway: '.$response['message'];
			trigger_error('ERROR TRANSACTION CART '.self::gateway_name.': '.$message);
			if ($response['response_code'] == 'null') {
				$message = 'Possible gateway mis-configuration, please contact the site admin. '.$message;
			}
			return self::_failure($transaction,self::FAIL_BANK_DECLINED,$message);
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
}