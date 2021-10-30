<?php 
//ChainPayment.class.php
/**
 * Holds the geoChainPayment class.
 *
 * @package System
 * @since Version 16.05.0
 */

/**
 * geoChainPayment is a structure to support creating and processing Chain Payments via Paypal's Adaptive Payments API.
 * A chain payment has one main recipient, along with one or more secondary recipients who are essentially invisible to the payer.
 *
 * In a geoChainPayment, the constructor configures the main target and the total amount to be paid over all recipients. Then, additional targets can be added.
 * Additional targets can receive either a percentage of the total or a flat amount, in order of their set Priority.
 *
 * Priority is used internally-only in determining the actual dollar amounts to give to each recipient. Paypal's API takes only static amounts for each payment.
 * The "main target" does not hold a payment amount on its own; it receives the total payment and pays out to the secondaries, essentially keeping whatever is left over
 *
 * If a "flat amount" payment is added that causes the remaining "total" funds to be more-than-depleted, it should use whatever it can and skip any later-priority targets
 * 
 * 
 */

class geoChainPayment
{
	private $_payerId;
	private $_mainTarget;
	private $_secondaryTargets;
	private $_total;
	private $_listingId;
	
	private $_cpId; // database primary key for chain payment save table
	
	public $payKey;
	public $status;
		
	public function __construct($mainTargetUserId, $total, $listingId)
	{
		if(!DataAccess::getInstance()->get_site_setting('pp_chain_enable')) {
			trigger_error('ERROR CHAINPAYMENT: Chain Payments not enabled in admin');
			throw new Exception('Chain Payments are not enabled');
		}
		$user = geoUser::getUser($mainTargetUserId);
		if(!$user) {
			trigger_error('ERROR CHAINPAYMENT: Attempting Chain Payment to non-user');
			throw new Exception('Attempting Chain Payment to non-user');
		}
		$total = (float)$total;
		if($total <= 0.00) {
			trigger_error('ERROR CHAINPAYMENT: Invalid total');
			throw new Exception('Invalid total for Chain Payment');
		}
		$listing = geoListing::getListing($listingId);
		if(!$listing) {
			trigger_error('DEBUG CHAINPAYMENT: Invalid listing specified');
			throw new Exception('Invalid listing specified');
		}
		$this->_listingId = $listing->id;
		$this->_mainTarget = $user->id;
		$this->_total = $this->_moneyRoundDown($total);
		$this->_payerId = geoSession::getInstance()->getUserId();
		$this->status = 'READY';
	}
	
	public function addSecondaryTarget($userId, $priority, $percentage, $flatAmount, $orderItemId=null)
	{
		$user = geoUser::getUser($userId);
		if(!$user) {
			trigger_error('ERROR CHAINPAYMENT: cannot add non-user as secondary target');
			return false;
		}
		$sb = geoSellerBuyer::getInstance(); 
		if(!$sb->getUserSetting($userId, 'paypal_id')) {
			trigger_error('ERROR CHAINPAYMENT: secondary target '.$userId.' does not have a Paypal address');
			return false; 
		}
		if($this->_secondaryTargets[$priority]['user']) {
			trigger_error('ERROR CHAINPAYMENT: secondary target already exists for priority '.$priority);
			return false;
		}
		if($percentage && $flatAmount) {
			trigger_error('ERROR CHAINPAYMENT: cannot use both percentage and flat amount');
			return false;
		}
		if($percentage && ($percentage < 0 || $percentage > 100)) {
			trigger_error('ERROR CHAINPAYMENT: secondary payment percentage out-of-bounds');
			return false;
		}
		if(count($this->_secondaryTargets) == 5) {
			trigger_error('ERROR CHAINPAYMENT: no more than 5 secondary targets are allowed!');
			return false;
		}
		
		$this->_secondaryTargets[$priority] = array(
			'user' => $user->id,
			'percentage' => $percentage,
			'flat' => $flatAmount,
			'orderItemId' => $orderItemId //track the ID of an order item that should be marked as paid when this payment completes
		);
		
		return $this->_validate();
	}
	
	/**
	 * Check secondary target sums and things...make sure we're not over 100% or anything funky like that
	 */
	private function _validate()
	{
		//first, sort by priority
		ksort($this->_secondaryTargets);
		
		$percentage = 0;
		$total = 0;
		
		foreach($this->_secondaryTargets as $t) {
			$percentage += $t['percentage'];
			$flat += ($t['percentage']) ? $this->total * ($t['percentage']/100) : $t['flat'];
		}
		
		if($percentage < 0 || $percentage > 100) {
			trigger_error('ERROR CHAINPAYMENT: Failed validation. Total payment percentage out-of-bounds');
			return false;
		}
		
		if($total > $this->_total) {
			//NOTE: this is NOT an error case, but raise a debug indication, since it's a nice thing to know
			trigger_error('DEBUG CHAINPAYMENT: total of secondary payments is MORE THAN the total available amount');
		}
		return true;
		
	}
	
	private function _processPaymentAmounts()
	{
		
		$total = $this->_total;
		foreach($this->_secondaryTargets as $priority => $target) {
			if($total == 0) {
				//no funds left for this target
				$payment = 0;
			} elseif($target['percentage']) {
				$payment = $total * $target['percentage'] / 100;
				
			} elseif($target['flat']) {
				$payment = min($total, $target['flat']);
			}
			$payment = $this->_moneyRoundDown($payment);
			
			$this->_secondaryTargets[$priority]['paymentAmount'] = $payment;
			trigger_error('DEBUG CHAINPAYMENT: secondary payment with priority '.$priority.': '.$payment);
			$total -= $payment;
		}
	} 
	
	private function _moneyRoundDown($amount)
	{
		$amount = round($amount, 2);
		return sprintf("%01.2f", $amount);
		//below may be useful: quick way to truncate (rounds down) everything after the second decimal place
		return (floor($amount * $fig) / $fig);
	}
	
	/**
	 * submit all info to Paypal's API and actually make the payment
	 */
	public function process($returnURL=null)
	{
		$sb = geoSellerBuyer::getInstance(); //to get paypal addresses and currency settings
		
		$endpoint = "https://svcs.sandbox.paypal.com/AdaptivePayments/Pay";
		
		$db = DataAccess::getInstance();
		//if a production AppId is set in admin (provided by PP at go-live time), use it; otherwise, use the generic development ID
		$appId = ($db->get_site_setting('pp_chain_appid')) ? $db->get_site_setting('pp_chain_appid') : "APP-80W284485P519543T";
		
		$headers = array(
				"X-PAYPAL-SECURITY-USERID: ".$db->get_site_setting('pp_chain_username'),
				"X-PAYPAL-SECURITY-PASSWORD: ".$db->get_site_setting('pp_chain_password'),
				"X-PAYPAL-SECURITY-SIGNATURE: ".$db->get_site_setting('pp_chain_signature'),
				"X-PAYPAL-REQUEST-DATA-FORMAT: NV",
				"X-PAYPAL-RESPONSE-DATA-FORMAT: NV",
				"X-PAYPAL-APPLICATION-ID: $appId"
		);
		
		$params = array();
		$params['actionType'] = 'PAY'; //use PAY_PRIMARY to delay secondary payments until manually approved (at paypal.com by the primary recipient)
		$params['clientDetails.applicationId'] = $appId;
		$params['partnerName'] = $db->get_site_setting('pp_chain_partner');
		
		//this is used in the example in the docs, but no mention of WHICH ip address it's supposed to be (server? buyer?)
		//so far, it seems to work okay without this, so probably not needed at all
		//$params['clientDetails.ipAddress'] = "127.0.0.1";
		
		
		$params['currencyCode'] = $sb->getListingSetting($this->_listingId, 'paypal_currency', 'USD'); //get listing's chosen SB currency (or default to USD if not set)
		
		//who will cover PayPal transaction fees?
		//EACHRECEIVER is the default; each target pays fees on the money he receives
		//PRIMARYRECEIVER puts all fees on the main target
		//SECONDARYRECEIVER puts all fees on the secondary target, but is only valid when there is a single secondary target
		//SENDER is an invalid option for chain payments
		$params['feesPayer'] = "EACHRECEIVER";
		
		$params['requestEnvelope.errorLanguage'] = "en_US"; //required; must be en_US
				
		
		$this->save(); //we'll save this again later; doing it here now to generate a auto-increment ID for the transaction
		
		if(!$returnURL) {
			$return_url = $db->get_site_setting('classifieds_url').'?a=sb_transaction&action=paypal_cp_result';
			if($this->_listingId) {
				$return_url .= '&l_id='.$this->_listingId;
			}
		} else {
			$return_url = $returnURL;
		}
		$return_url .= '&cpid='.(int)$this->_cpId;
		
		$params['returnUrl'] = $params['cancelUrl'] = $return_url;

		//TODO: IPN validation
		//$params['ipnNotificationUrl'] = "";
		
		$listing = geoListing::getListing($this->_listingId);
		if($listing) {
			$params['memo'] = $db->get_site_setting('friendly_site_name').' :: '.geoString::fromDB($listing->title).' ('.$listing->id.')';
		}
		
		$params['receiverList.receiver(0).amount'] = $this->_total;
		$params['receiverList.receiver(0).email'] = $sb->getUserSetting($this->_mainTarget, 'paypal_id');
		$params['receiverList.receiver(0).primary'] = "true";
		
		//convert all the secondary payment data into usable amounts
		$this->_processPaymentAmounts();
		
		foreach($this->_secondaryTargets as $priority => $data) {
			$params['receiverList.receiver('.$priority.').amount'] = $data['paymentAmount'];
			$params['receiverList.receiver('.$priority.').email'] = $sb->getUserSetting($data['user'], 'paypal_id');
			$params['receiverList.receiver('.$priority.').primary'] = "false";
		}
		
		//paypal can't handle the parameters being sent as an array over cURL for some reason
		//so we have to encode them all into a single string first
		$p_str = self::_createPaypalString($params);

		$response = array();
		parse_str(geoPC::urlPostContents($endpoint, $p_str, 30, $headers), $response);

		
		//die('<pre>'.print_r($response,1).'</pre>');
		if(stripos($response['responseEnvelope_ack'], 'Failure') !== false) {
			trigger_error('ERROR CHAINPAYMENT: PAY request failed');
			geoView::getInstance()->addBody('An unknown payment error occured. Please try again or contact the site owner.');
			return false;
		}
		
		$this->payKey = $response['payKey'];
		$redirUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=".$this->payKey;
		
		$this->save(); //now that we have the payKey, save again!
		
		header("Location: ".$redirUrl);
		include_once('app_bottom.php');
		exit();
	}
	
	private static function _createPaypalString($params)
	{
		$first = 0;
		$p_str = '';
		foreach($params as $key => $value) {
			if($first++ > 0) $p_str .= '&';
			$p_str .= urlencode($key) .'='. urlencode($value);
		}
		return $p_str;
	}
	
	public function markSecondariesPaid()
	{
		//look through secondaries for any that have an order item ID set. if found, mark it as paid and set it's parent order to paid also if applicable
		foreach($this->_secondaryTargets as $priority => $target) {
			if(!$target['orderItemId']) {
				continue;
			}
			$item = geoOrderItem::getOrderItem($target['orderItemId']);
			$item->setStatus('active');
			$item->save();
			$order = $item->getOrder();
			if($order->getStatus() !== 'active') {
				$allItems = $order->getItem();
				$allActive = true;
				foreach($allItems as $i) {
					if($i->getStatus() !== 'active') {
						$allActive = false;
						break;
					}
				}
				if($allActive) {
					$order->setStatus('active');
				}
				$order->save();
			}
		}
	}
	
	
	/**
	 * Store this object to DB for later retrieval/validation/confirmation
	 */
	public function save()
	{
		$db = DataAccess::getInstance();
		if(!$this->_cpId) {
			//if auto id not set, this hasn't been added to the db yet
			$sql = "INSERT INTO `geodesic_chain_payments` (`payKey`,`sender`,`primary_receiver`, `listing_id`, `total`,`secondary_receiver_data`,`creation_time`) VALUES (?,?,?,?,?,?,?)";
			$result = $db->Execute($sql, array($this->payKey.'', $this->_payerId, $this->_mainTarget, $this->_listingId, $this->_total, serialize($this->_secondaryTargets), geoUtil::time()));
			if(!$result) {
				trigger_error('ERROR CHAINPAYMENT: Failed writing to db');
				return false;
			}
			$this->_cpId = $db->Insert_Id();
		} else {
			$sql = "UPDATE `geodesic_chain_payments` SET `payKey` = ?, `sender` = ?, `primary_receiver` = ?, `listing_id` = ?, `total` = ?, `secondary_receiver_data` = ?, `status` = ? WHERE `id` = ?";
			$result = $db->Execute($sql, array($this->payKey.'', $this->_payerId, $this->_mainTarget, $this->_listingId, $this->_total, serialize($this->_secondaryTargets), $this->status, $this->_cpId));
			if(!$result) {
				trigger_error('ERROR CHAINPAYMENT: Failed updating db');
				return false;
			}
		}
	}
	
	/**
	 * restore a geoChainPayment object from saved db data
	 * @param int $listingId
	 * @return geoChainPayment
	 */
	public static function retrieve($cpId)
	{
		$db = DataAccess::getInstance();
		
		$sql = "SELECT * FROM `geodesic_chain_payments` WHERE `id` = ?";
		$data = $db->GetRow($sql, array($cpId));
		
		$userId = geoSession::getInstance()->getUserId();
		if($userId != $data['sender']) {
			//not your payment!
			//TODO: ever a case where the receiver(s) might use this function?
			trigger_error('ERROR CHAINPAYMENT: invalid access');
			return false;
		}

		
		$cp = new geoChainPayment($data['primary_receiver'], $data['total'], $data['listing_id']);
		$cp->payKey = $data['payKey'];
		$secondaries = unserialize($data['secondary_receiver_data']);
		foreach($secondaries as $priority => $s) {
			$cp->addSecondaryTarget($s['user'], $priority, $s['percentage'], $s['flat'], $s['orderItemId']);
		}
		$cp->_setCpId($data['id']);
		
		return $cp;
	}
	
	protected function _setCpId($id)
	{
		$this->_cpId = $id;
	}
	
	public static function checkResult($cpId, $returnSuccessMsg=false)
	{
		$cp = geoChainPayment::retrieve($cpId);
		
		//get payment status based on stored payKey
		$endpoint = "https://svcs.sandbox.paypal.com/AdaptivePayments/PaymentDetails";
		$params = array('payKey' => $cp->payKey, 'requestEnvelope.errorLanguage' => 'en_US');
		$db = DataAccess::getInstance();
		//if a production AppId is set in admin (provided by PP at go-live time), use it; otherwise, use the generic development ID
		$appId = ($db->get_site_setting('pp_chain_appid')) ? $db->get_site_setting('pp_chain_appid') : "APP-80W284485P519543T";
		
		$headers = array(
				"X-PAYPAL-SECURITY-USERID: ".$db->get_site_setting('pp_chain_username'),
				"X-PAYPAL-SECURITY-PASSWORD: ".$db->get_site_setting('pp_chain_password'),
				"X-PAYPAL-SECURITY-SIGNATURE: ".$db->get_site_setting('pp_chain_signature'),
				"X-PAYPAL-REQUEST-DATA-FORMAT: NV",
				"X-PAYPAL-RESPONSE-DATA-FORMAT: NV",
				"X-PAYPAL-APPLICATION-ID: $appId"
		);
		
		
		$p_str = self::_createPaypalString($params);
		$response = array();
		parse_str(geoPC::urlPostContents($endpoint, $p_str, 30, $headers), $response);
		
		if(stripos($response['responseEnvelope_ack'], 'Failure')) {
			trigger_error('ERROR CHAINPAYMENT: Failed to retrieve payment status');
			return false;
		}
		
		$cp->status = $response['status'];
		$cp->save(); //save status to db...can move this to later if other data winds up needing to be changed in this function
		/*
		 * The status of the payment. Possible values are:
		    CREATED � The payment request was received; funds will be transferred once the payment is approved
		    COMPLETED � The payment was successful
		    INCOMPLETE � Some transfers succeeded and some failed for a parallel payment or, for a delayed chained payment, secondary receivers have not been paid
		    ERROR � The payment failed and all attempted transfers failed or all completed transfers were successfully reversed
		    REVERSALERROR � One or more transfers failed when attempting to reverse a payment
		    PROCESSING � The payment is in progress
		    PENDING � The payment is awaiting processing
		 */
		
		//if this had a secondary Final Fees target, mark that Order and Order Item as active/paid
		if($cp->status === 'COMPLETED') {
			$cp->markSecondariesPaid();
		}
		
		if($returnSuccessMsg) {
			//When showing the seller a result page, assume success so he doesn't try to pay again or anything funky like that
			geoSellerBuyer::getInstance()->setListingSetting($listing_id,'paypal_listing_paid',1); //so the "pay now" button doesn't appear if returning to that page
			$messages = $db->get_text(true, 10201);
			return '<div class="paypal_payment_success_text">'.geoString::fromDB($messages[500203]).'</div>';
		}
		return $cp->status;
		
	}
}