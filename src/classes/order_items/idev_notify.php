<?php
//order_items/idev_notify.php
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
## ##    7.1.2-36-g991a1d7
## 
##################################

class idev_notifyOrderItem extends geoOrderItem {
	
	/**
	 * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
	 *
	 * @var string
	 */
	protected $type = "idev_notify";
	const type = 'idev_notify';
	
	/**
	 * This is the whole reason for this order item, to be notified when an order goes active,
	 * so that we can send it the money info to idev affiliate system.
	 *
	 * @param array $vars - Associative array with structure: array('order_ite' => int, 'new_transaction_id' => int)
	 */
	public static function Order_processStatusChange($order){
		if (!self::_useIdev()){
			//not using idev affiliates, don't do anything
			return;
		}
		
		if (!is_object($order) || $order->getStatus() !== 'active'|| $order->get('IDEV_PROCESSED')) {
			//Do not do it for this order, either the status is not active, or its already processed for this order.
			return;
		}
		
		$payment_type = $order->get('payment_type');
		if($payment_type === "account_balance") {
			//idev stuff happens when putting money into the account balance, but not when using the balance as a form of payment
			//TODO: make a more generic method for seeing if a gateway should use idev stuff?
			return;
		}
		
		//add up the cost of all items with a process order less than the one for idev
		$cost = $order->getOrderTotal(self::defaultProcessOrder);
		
		if ($cost <= 0.10){
			//don't care about orders that don't cost anything, or are less than
			//0.10 cents, as IDEV says to make sure the amount is more than that.
			return;
		}
		$order->set('IDEV_PROCESSED',1);
		
		//manually set the cookies that were present at the time the user checked out
		//TODO: Is cookie restore necessary, or does it only need remote_addr?
		$rememberCurrentCookiesGeo123 = $_COOKIE;
		
		$cookies = $order->get('idev_cookies', array());
		if ($cookies) {
			foreach ($cookies as $key => $value) {
				//Just setting it strait like $_COOKIE = $cookies may not work
				//on some sites, not entirely sure so needs to be tested if changed.
				$_COOKIE[$key] = $value;
			}
		}
		
		//manually set the remote addr since that is another way a user is tied to
		//an affiliate.
		$rememberServer = $_SERVER;
		
		$server = $order->get('idev_server');
		if ($server) {
			foreach ($server as $key => $val) {
				$_SERVER[$key] = $val;
			}
		}
		//path to the sale.php file.
		$salePath = self::_getIdevPath().'sale.php';
		
		$GLOBALS['idev_geoce_1'] = $idev_geoce_1 = $cost;
		$GLOBALS['idev_geoce_2'] = $idev_geoce_2 = 'order-'.$order->getId();//pass order id so that it can be tracked back to geo
		DataAccess::getInstance()->Close(); //close db connection to prevent cross connections
		include($salePath);
		
		if ($cookies) {
			//reset cookies back to normal for rest of page load
			//first clear them all
			$keys = array_keys($_COOKIE);
			foreach ($keys as $key) {
				unset($_COOKIE[$key]);
			}
			
			//now set the originals
			foreach ($rememberCurrentCookiesGeo123 as $key => $val) {
				$_COOKIE[$key] = $val;
			}
		}
		if ($server) {
			//restore remote addr and others back to normal for rest of page load
			foreach ($server as $key => $val) {
				$_SERVER[$key] = $rememberServer[$key];
			}
		}
	}
	
	/**
	 * Used for recurring billing payment signals
	 *
	 * @param array $vars - Associative array with structure: array('recurring' => geoRecurringBilling, 'transaction' => geoTransaction)
	 */
	public static function RecurringBilling_processPayment ($vars)
	{
		if (!self::_useIdev()){
			//not using idev affiliates, don't do anything
			return;
		}
		$recurring = $vars['recurring'];
		$transaction = $vars['transaction'];
		
		if (!is_object($recurring) || $recurring->getStatus() !== geoRecurringBilling::STATUS_ACTIVE|| $transaction->get('IDEV_PROCESSED')) {
			//Do not do it for this order, either the status is not active, or its already processed for this order.
			return;
		}
		
		$payment_type = $transaction->getGateway();
		if ($payment_type === "account_balance") {
			//idev stuff happens when putting money into the account balance, but not when using the balance as a form of payment
			//TODO: make a more generic method for seeing if a gateway should use idev stuff?
			return;
		}
		
		//add up the cost of all items with a process order less than the one for idev
		$cost = $transaction->getAmount();
		
		if ($cost <= 0.10){
			//don't care about orders that don't cost anything, or are less than
			//0.10 cents, as IDEV says to make sure the amount is more than that.
			return;
		}
		$transaction->set('IDEV_PROCESSED',1);
		
		//manually set the cookies that were present at the time the user checked out
		//TODO: Is cookie restore necessary, or does it only need remote_addr?
		$rememberCurrentCookiesGeo123 = $_COOKIE;
		
		$order = $recurring->getOrder();
		if ($order) {
			$cookies = $order->get('idev_cookies', array());
			if ($cookies) {
				foreach ($cookies as $key => $value) {
					//Just setting it strait like $_COOKIE = $cookies may not work
					//on some sites, not entirely sure so needs to be tested if changed.
					$_COOKIE[$key] = $value;
				}
			}
		}
		
		//manually set the remote addr since that is another way a user is tied to
		//an affiliate.
		$rememberServer = $_SERVER;
		if ($order) {
			$server = $order->get('idev_server');
		}
		if ($server) {
			foreach ($server as $key => $val) {
				$_SERVER[$key] = $val;
			}
		}
		//path to the sale.php file.
		$salePath = self::_getIdevPath().'sale.php';
		
		$GLOBALS['idev_geoce_1'] = $idev_geoce_1 = $cost;
		$GLOBALS['idev_geoce_2'] = $idev_geoce_2 = 'recurring-'.$recurring->getId().'-transaction-'.$transaction->getId();//pass order id so that it can be tracked back to geo
		DataAccess::getInstance()->Close(); //close db connection to prevent cross connections
		include($salePath);
		
		if ($cookies) {
			//reset cookies back to normal for rest of page load
			//first clear them all
			$keys = array_keys($_COOKIE);
			foreach ($keys as $key) {
				unset($_COOKIE[$key]);
			}
			
			//now set the originals
			foreach ($rememberCurrentCookiesGeo123 as $key => $val) {
				$_COOKIE[$key] = $val;
			}
		}
		if ($server) {
			//restore remote addr and others back to normal for rest of page load
			foreach ($server as $key => $val) {
				$_SERVER[$key] = $rememberServer[$key];
			}
		}
	}
	
	public static function geoCart_payment_choicesProcess ()
	{
		$cart = geoCart::getInstance();
		
		//remember the current cookies, to manually set them at time IDEV integration
		//takes place.
		
		if ($cart->order) {
			$cart->order->set('idev_cookies', $_COOKIE);
			//remember the IP address...
			$server = array();
			
			//These are the possible $_SERVER vars that IDEV could be using
			//to track the IP address, so save any of them that are set on this
			//server.
			$possibleKeys = array ('REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 
				'HTTP_CLIENT_IP', 'HTTP_X_CLUSTER_CLIENT_IP');
			
			foreach ($possibleKeys as $key) {
				if (isset($_SERVER[$key]) && $_SERVER[$key]) {
					$server[$key] = $_SERVER[$key];
				}
			}
			if ($server) {
				//if at least one of those was set, save them
				$cart->order->set('idev_server', $server);
			}
		}
	}
	
	/**
	 * Whether or not to use idev integration
	 *
	 * @return boolean
	 */
	protected static function _useIdev(){
		$db = DataAccess::getInstance();
		return $db->get_site_setting('idevaffiliate') && geoMaster::is('site_fees');
	}
	
	/**
	 * Gets the path to idev affiliate, as set in admin
	 *
	 * @return string
	 */
	protected static function _getIdevPath(){
		$db = DataAccess::getInstance();
		return $db->get_site_setting('idev_path');
	}
	
	public function displayInAdmin() {
		return false;
	}
	
	/**
	 * Process order, only items that have process order less than this one will be
	 * included in the price sent to idev.
	 * 
	 * Note on current process orders:
	 * ...
	 * 10,000 = sub total display item
	 * 15,000 = idev notify item (this item)
	 * 20,000 = tax item
	 * 
	 * To make an item not be processed by idev, change this process order to be less than or equal to
	 * that item, and arrange the process orders of the other items so that everything you do want processed
	 * by idev has a process order less than it.
	 *
	 * @var unknown_type
	 */
	protected $defaultProcessOrder = 15000;
	const defaultProcessOrder = 15000;
	
	/**
	 * Required by interface
	 *
	 */
	public static function geoCart_initSteps($allPossible=false){
		
	}
	
	/**
	 * Required by interface.
	 * 
	 */
	public static function geoCart_initItem_forceOutsideCart() {
		//most need to return false.
		return false;
	}
	
	/**
	 * Required by interface.
	 * 
	 */
	public static function getParentTypes(){
		//for "parent" order item, returne empty string.
		return array();
	}
	
	/**
	 * Required by interface.
	 * 
	 */
	public function getDisplayDetails ($inCart,$inEmail=false)
	{
		//no display for idev affiliate
		return false;
	}
	
	/**
	 * Required by interface.
	 */
	public function getCostDetails ()
	{
		//this adds no cost ever, it is just notifying idev
		return false;
	}
	
	
	/**
	 * Required by interface.
	 * 
	 * @return boolean false.
	 */
	public static function geoCart_initSteps_addOtherDetails(){
		return false;
	}
	
	public function geoCart_initItem_new ()
	{
		//this is not added to cart!
		
		return false;
	}
}