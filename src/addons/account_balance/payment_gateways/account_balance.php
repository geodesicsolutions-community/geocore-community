<?php
//account_balance.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########SVN Build Data##########
##                              ##
## This File's Revision:        ##
##  $Rev::                    $ ##
## File last change date:       ##
##  $Date::                   $ ##
##                              ##
##################################

require_once CLASSES_DIR . PHP5_DIR . 'PaymentGateway.class.php';

# Cash payment gateway handler

class account_balancePaymentGateway  extends geoPaymentGateway{
	
	public $name = 'account_balance';//make it so that name is known.
	const gateway_name = 'account_balance';
	public $type = 'account_balance';
	/**
	 * Expects to return an array:
	 * array (
	 * 	'' => ''
	 * )
	 *
	 */
	function admin_display_payment_gateways (){
		if (!geoPC::is_ent()) {
			return '';
		}
		$return = array (
			'name' => self::gateway_name,
			'title' => 'Account Balance',
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
	function admin_custom_config (){
		if (!geoPC::is_ent()) {
			return '';
		}
		$db = DataAccess::getInstance();
		
		$tpl = new geoTemplate('admin');
		$tpl->assign('payment_type', self::gateway_name);
		$tpl->assign('charge_final_fees', $this->get('charge_final_fees'));
		$tpl->assign('use_no_free_cart', $this->get('use_no_free_cart'));
		$tpl->assign('finalFees', geoMaster::is('auctions'));
		$tpl->assign('min_add',geoString::displayPrice(($this->get('min_add_to_balance') === false)? '5.00': $this->get('min_add_to_balance'),'',''));
		$tpl->assign('precurrency',$db->get_site_setting('precurrency'));
		$tpl->assign('postcurrency',$db->get_site_setting('postcurrency'));
		$tpl->assign('positive_check', ($this->get('allow_positive'))? 'checked="checked" ': '');
		$tpl->assign('negative_check',($this->get('allow_negative'))? 'checked="checked" ': '');
		$tpl->assign('negative_time',(($this->get('negative_time') === false)? '90': $this->get('negative_time')));
		$tpl->assign('negative_max',geoString::displayPrice((($this->get('negative_max') === false)? '100': $this->get('negative_max')), '', ''));
		$tpl->assign('force_check', ($this->get('force_use'))? 'checked="checked" ': '');
				
		return $tpl->fetch('payment_gateways/account_balance.tpl');
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
		if (!geoPC::is_ent()) {
			return true;
		}
		
		$admin = true;
		include GEO_BASE_DIR.'get_common_vars.php';
		
		
		if (isset($_POST[self::gateway_name]) && is_array($_POST[self::gateway_name]) && count($_POST[self::gateway_name]) > 0){
			$settings = $_POST[self::gateway_name];
			
			$min = geoNumber::deformat($settings['min_add_to_balance']);
			if ($min < 0){
				$min = 0;
			}
			$negative_time = intval($settings['negative_time']);
			if ($negative_time < 0){
				$negative_time = 0;
			}
			$negative_max = abs(geoNumber::deformat($settings['negative_max']));
			$allow_pos = ((isset($settings['allow_positive']) && $settings['allow_positive'])? 1: false);
			$allow_neg = ((isset($settings['allow_negative']) && $settings['allow_negative'])? 1: false);
			$charge_final_fees = ((isset($settings['charge_final_fees']) && $settings['charge_final_fees'])? 1: false);
			if ($charge_final_fees) {
				$use_no_free_cart = ((isset($settings['use_no_free_cart']) && $settings['use_no_free_cart'])? 1: false);
			} else {
				$use_no_free_cart = false;
			}
			if (!$allow_pos && !$allow_neg){
				//Should we do anything here?  I think probably not, what if site owner decides to not use account balance any more, but
				//still wants people to pay off their negative balance...  this can only be accomplished by setting pos and neg to no.
			}
			$force_use = (isset($settings['force_use']) && $settings['force_use'])? 1: false;
			
			$this->set('charge_final_fees', $charge_final_fees);
			$this->set('use_no_free_cart', $use_no_free_cart);
			$this->set('allow_positive',$allow_pos);
			$this->set('allow_negative',$allow_neg);
			$this->set('min_add_to_balance',$min);
			$this->set('negative_time',$negative_time);
			$this->set('negative_max',$negative_max);
			$this->set('force_use',$force_use);
			
			$this->serialize();
		}
		return true;
	}
	public static function geoCart_payment_choicesDisplay_freeCart ()
	{
		if (!geoPC::is_ent()) {
			return false;
		}
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		if (!$gateway || !$gateway->get('charge_final_fees') || !$gateway->get('use_no_free_cart')) {
			//charge final fees turned off, or not forcing to auto-charge,
			//so don't auto charge final fees
			return false;
		}
		return true;
	}
	
	public static function geoCart_payment_choicesDisplay(){
		if(!geoPC::is_ent()) {
			return;
		}
		$cart = geoCart::getInstance();
		//make sure, if item is account_balance then don't allow!

		$forbiddenTypes = array('account_balance','verify_account'); // things that CANNOT be paid for with account balance
		if (is_object($cart->item) && (in_array($cart->item->getType(),$forbiddenTypes))) {
			//do not show this as a payment option!
			return false;
		}
		//also check all other items attached to the order (also works around a case where trying to add a new instance of account balance makes the old one show up here)
		$allItems = $cart->order->getItem();
		foreach($allItems as $i) {
			if(is_object($i) && (in_array($i->getType(),$forbiddenTypes))) {
				return false;
			}
		}
		
		$msgs = $cart->db->get_text(true, 10203);
		$return = array(
			//Items that don't auto generate if left blank
			'title' => $msgs[500292],
			'title_extra' => $msgs[500293],
			'label_name' => self::gateway_name,
			'radio_value' => self::gateway_name,//should be same as gateway name
			'help_link' => $cart->site->display_help_link(3240),
			'checked' => false,
			
			//Items below will be auto generated if left blank string.
			'radio_name' => '',
			'choices_box' => '',
			'help_box' => '',
			'radio_box' => '',
			'title_box' => '',
			'radio_tag' => '',
		
		);
		$display_amount = geoString::displayPrice($cart->user_data['account_balance']);
		$return['title_extra'] .= ' '.$display_amount;
		if (isset($cart->error_msgs["account_balance"]) && (strlen(trim($cart->error_msgs["account_balance"])) > 0)){
			$return['title_extra'] .= "
	<br />
	<span class=\"error_message\">
		{$cart->error_msgs["account_balance"]}
	</span>";
		}
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		if ($gateway->get('force_use')) {
			//forcing to use only paypal, let view class know to ignore
			//other payment gateways
			geoView::getInstance()->force_use_gateway = self::gateway_name;
		}
		return $return;
	}


	
	
	public static function geoCart_payment_choicesCheckVars (){
		$cart = geoCart::getInstance();
		
		
		if (isset($_POST['c']['payment_type']) && $_POST['c']['payment_type'] == self::gateway_name){
			$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
			if ($cart->user_data['balance_freeze'] > 0){
				//Account FROZEN!!!  Do not let them pay using account balance!!!
				$cart->addError();
				$amount_to_pay_off = abs($cart->user_data['account_balance']);
				//TODO: text
				if ($cart->user_data['balance_freeze'] == 1){
					//frozen only until they pay off their balance.
					$msg = $cart->site->messages[500539].'<a href="'.self::_getAddToBalanceLink($amount_to_pay_off).'">'.$cart->site->messages[500540].'</a>'.$cart->site->messages[500541];
				} elseif ($cart->user_data['balance_freeze'] == 2) {
					//can only add to balance, admin has to un-freeze.
					$msg = $cart->site->messages[500590].'<a href="'.self::_getAddToBalanceLink().'">'.$cart->site->messages[500591].'</a>'.$cart->site->messages[500592];
				} else { //balance_freeze = 3
					//frozen all the way, cannot add to or take away from account balance.
					$msg = $cart->site->messages[500593];
				}
				$cart->addErrorMsg("account_balance", $msg);
				return;
			}
			if (($cart->user_data['account_balance'] < 0) || ($cart->getCartTotal() > $cart->user_data['account_balance']))
			{
				if (!$gateway->get('allow_negative')){
					$amount = abs($cart->user_data['account_balance'] - $cart->getCartTotal());
					$cart->addError();
					$cart->addErrorMsg("account_balance", $cart->site->messages[500594]."<a href='".self::_getAddToBalanceLink($amount)."'>".$cart->site->messages[500595]."</a>.");
					return;
				}
				if ($cart->user_data['account_balance'] < 0 && $cart->user_data['date_balance_negative'] > 0 && ((geoUtil::time() - $cart->user_data['date_balance_negative']) > ($gateway->get('negative_time') * 86400))){
					$amount = abs($cart->user_data['account_balance']);
					$cart->addError();
					$cart->addErrorMsg('account_balance',$cart->site->messages[500596]."<a href='".self::_getAddToBalanceLink($amount)."'>".$cart->site->messages[500597]."</a>".$cart->site->messages[500598]);
					return;
				}
				if (abs($cart->user_data['account_balance'] - $cart->getCartTotal()) > $gateway->get('negative_max')){
					$cart->addError();
					$cart->addErrorMsg('account_balance',$cart->site->messages[500599]);
				}
			}
		}
	}
	
	public static function geoCart_payment_choicesProcess(){
		$cart = geoCart::getInstance();
		
		$new_balance = self::_processOrder($cart->order);
		$cart->user_data['account_balance'] = $new_balance;
	}
	
	public static function geoCart_process_orderDisplay(){
		$cart = geoCart::getInstance();
		
		self::_successFailurePage(true, $cart->order->getStatus(),true, $cart->order->getInvoice());
		
		//gateway is last thing to be called, so it needs to be the one that clears the session...
		$cart->removeSession();
	}
	
	public static function User_management_home_body ($vars)
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		if (!$gateway->getEnabled() || (!$gateway->get('allow_positive') && !$gateway->get('allow_negative'))) {
			return;
		}
		$db = DataAccess::getInstance();
		$view = geoView::getInstance();
		//this gateway enabled, display details
		$user = geoUser::getUser(geoSession::getInstance()->getUserId());
		$msgs = $db->get_text(true);
		$display_amount = geoString::displayPrice($user->account_balance);
		
		
		if(!isset($msgs[500486])) {
			//this code left in for now to support people not using the module yet
	        $balance = "<span class=\"user_links\">{$msgs[2549]}{$display_amount}</span>";
			$add_to_account = $msgs[2548];
			$view->add_money_with_balance = $balance;
			$view->add_money = $add_to_account;
			$view->balance_transactions = $msgs[3213];
			$view->show_account_balance = true;
			return true;
		} //end "legacy" code
		
		$currentBalance = $history = $addToBalance = array();
		
		$currentBalance['link'] = false;
		$currentBalance['icon'] = false;
		$currentBalance['label'] = $msgs[500486].$display_amount;
		
		$history['link'] = $vars['url_base'] . '?a=4&amp;b=18';
		$history['label'] = $msgs[500487];
		$history['icon'] = $msgs[500488];
		$history['active'] = ($_REQUEST['a'] == 4 && $_REQUEST['b'] == 18) ? true : false;
		
		if($gateway->canAddToBalance($user)) {
			$addToBalance['link'] = $vars['url_base'] . '?a=29';
			$addToBalance['label'] = $msgs[500489];
			$addToBalance['icon'] = $msgs[500490];
			$addToBalance['active'] = ($_REQUEST['a'] == 'cart' && $_REQUEST['main_type'] == self::gateway_name) ? true : false;
			$addToBalance['needs_attention'] = ($user->account_balance < 0) ? true : false; //highlight if balance negative
		}
		
		$paymentGatewayLinks = $view->paymentGatewayLinks;
		$paymentGatewayLinks[] = $currentBalance;
		$paymentGatewayLinks[] = $history;
		$paymentGatewayLinks[] = $addToBalance;
		$view->paymentGatewayLinks = $paymentGatewayLinks;
		
	}
	public static function auction_final_feesOrderItem_cron_close_listings ($vars)
	{
		if (!geoPC::is_ent()) {
			return;
		}
		$cron = geoCron::getInstance();
		$listing = $vars['listing'];
		$order = $vars['order'];
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		//Note: no need to check if enabled, as this gateway would only be called
		//if it were already enabled.
		if (!$gateway->get('charge_final_fees')) {
			//charge final fees turned off, don't auto charge final fees
			return;
		}
		$user_id = $order->getBuyer();
		if (!$user_id) {
			//something wrong with user?
			return;
		}
		$user = geoUser::getUser($user_id);
		if (!$user) {
			//something wrong with user?
			return;
		}
		if ($user->balance_freeze > 0){
			//Account FROZEN!!!  Do not let them pay using account balance!!!
			return;
		}
		if (!$order->getInvoice()) {
			//error getting invoice, can't go without an invoice
			return;
		}
		$total = $order->getInvoice()->getInvoiceTotal();
		$newBalance = ($total + $user->account_balance);
		if (($newBalance) < 0 && $user->account_balance < 0 && $user->date_balance_negative > 0 && ((geoUtil::time() - $user->date_balance_negative) > ($gateway->get('negative_time') * 86400))){
			//NOT using balance, they have been negative too long
			return;
		}
		if ($gateway->get('allow_negative') || ($newBalance) >= 0) {
			if ($newBalance < 0 && abs($newBalance) > $gateway->get('negative_max')){
				//at the max amount allowed for negative, don't charge to account balance.
				return;
			}
			//if it gets here, then all the checks are go, so charge the final fees
			//to the account balance
			self::_processOrder($order);
		}
	}
	
	private static function _getAddToBalanceLink($amount = 0){
		$cart = geoCart::getInstance();
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		$url = $cart->getCartBaseUrl();
		$url .= '&amp;action=new&amp;main_type=account_balance';
		if ($amount > 0 && $amount > $gateway->get('min_add_to_balance')){
			$url .= '&amp;account_balance_add='.floatval($amount);
		}
		return $url;
	}
	
	private static function _processOrder ($order)
	{
		$gateway = geoPaymentGateway::getPaymentGateway(self::gateway_name);
		//get invoice on the order
		$invoice = $order->getInvoice();
		if (!is_object($invoice)){
			trigger_error('DEBUG CART: Unable to process, no invoice attached to order??');
			return false;
		}
		$due = $invoice->getInvoiceTotal();
		
		if ($due >= 0) {
			//DO NOT PROCESS!  Nothing to process, no charge (or returning $$$?)
			return ;
		}
		//set payment_type - usually already set, unless processing order with
		//only final fees.
		$order->set('payment_type',self::gateway_name);
		
		trigger_error('DEBUG CART TRANSACTION: Top of '.self::gateway_name.': '.__function__.'() - processing');
		
		$user = geoUser::getUser($order->getBuyer());
		
		//if it gets this far, then we've already checked that the user has enough and that they aren't over some limit.
		//so, just subtract the amount from their balance and call it a day.
		
		$new_balance = $user->account_balance + $due; //due is negative, so this is actually subtracting the amount the invoice costed.
		if ($new_balance < 0 && ($user->account_balance >= 0 || $user->date_balance_negative < 10)){
			//we just crossed from positive (or 0) to negative, or we are already negative but the 
			//dabe_balance_negative is not set yet, so set the "date_balance_negative"
			$user->date_balance_negative = geoUtil::time();
		}
		$user->account_balance = $new_balance;
		//also add transaction
		$transaction = new geoTransaction;
		$transaction->setAmount(-1 * $due);//balance out the amount due on invoice
		$transaction->setDate(geoUtil::time());
		$msgs = DataAccess::getInstance()->get_text(true,183);
		$transaction->setDescription($msgs[500584]);
		$transaction->setGateway($gateway);
		$transaction->setInvoice($invoice);
		$transaction->setStatus(1);//since payment is automatic, do it automatically.
		$transaction->setUser($user->id);
		$transaction->save();//save changes
		
		$invoice->addTransaction($transaction);
		self::_success($order,$transaction,$gateway, true);
		return $new_balance;
	}
	
	/**
	 * find out if a given user can add to his account balance
	 *
	 * @param int $user id of user to check
	 * @return bool
	 */
	public function canAddToBalance($user=0)
	{
		if(!$user) {
			//no user to check
			return false;
		}		
		if(!$this->getEnabled()) {
			//account balance not enabled
			return false;
		}
		$userData = geoUser::getData($user);
		$freeze = $userData['balance_freeze'];
		$user_balance = $userData['account_balance'];
		
		if(!$this->get('allow_positive') && $user_balance >= 0) {
			//balance not allowed to be positive, and is already at or above 0 
			return false;
		}
		
		if($freeze == 3) {
			//adding to balance frozen
			return false;
		}
		
		return true;
		
	}
}