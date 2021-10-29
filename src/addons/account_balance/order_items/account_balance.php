<?php
//order_items/account_balance.php
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

require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';

class account_balanceOrderItem extends geoOrderItem {
	protected $type = 'account_balance';
	const type = 'account_balance';
	
	
	protected $defaultProcessOrder = 25;
	const defaultProcessOrder = 25;
	
	var $cost_added_to_cost_of_ad = false;
	public function displayInAdmin() {
		return true;
	}
	
	public function adminDetails ()
	{
		$amountAdded = geoString::displayPrice($this->getCost());
		return array(
			'type' => ucwords(str_replace('_',' ',self::type)),
			'title' => 'Add to Account Balance: ' . $amountAdded
		);
	}
	
	public static function Admin_site_display_user_data($user_id){
		$user_id = intval($user_id);
		if (!$user_id){
			return;
		}
		$db = DataAccess::getInstance();
		
		$sql = 'SELECT `account_balance`, `date_balance_negative`, `balance_freeze` FROM '.geoTables::userdata_table.' WHERE `id` = '.intval($user_id);
		$user_data = $db->GetRow($sql);
		
		if ($user_data === false){
			trigger_error('ERROR SQL: Sql: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return;
		}
		$c0 = $c1 = $c2 = $c4 = '';
		if ($user_data['balance_freeze'] > 0){
			switch ($user_data['balance_freeze']){
				case 1:
					//frozen only until they pay off their balance.
					$status = 'Paying with balance: <span style="color: red;">Frozen</span><br />
								Adding to Balance: <span style="color: green;">Active</span><br />
								AUTO Activation: <span style="color: green;">ON</span><br />
								(Once negative balance paid, Paying with Balance will activate)';
								
					$c1 = 'selected="selected"';
					break;
				case 2:
					//can only add to balance, admin has to un-freeze.
					$status = 'Paying with balance: <span style="color: red;">Frozen</span><br />
								Adding to Balance: <span style="color: green;">Active</span><br />
								AUTO Activation: <span style="color: red;">OFF</span><br />
								(Requires admin to change status)';
					$c2 = 'selected="selected"';
					break;
				case 3:
				default:
					$status = 'Paying with balance is <span style="color: red;">Frozen</span><br />
								Adding to Balance is <span style="color: red;">Frozen</span><br />
								AUTO Activation: <span style="color: red;">OFF</span>';
					//frozen all the way, cannot add to or take away from account balance.
					$c3 = 'selected="selected"';
					break;
			}
		} else {
			$status = 'Paying with balance: <span style="color: green;">Active</span><br />
						Adding to Balance: <span style="color: green;">Active</span>';
			$c0 = 'selected="selected"';
		}
		if ($user_data['account_balance'] >= 0){
			$c1 .= " disabled='disabled'";
		}
		$status .= "<br />
		<form action='index.php?page=users_view&amp;b=$user_id' method='post' id='account_balance_status'>
			<select name='account_balance[status]' style='border: 1px solid lightblue;' onchange='document.forms.account_balance_status.submit()'>
				<option value='0' $c0>Fully Active</option>
				<option value='1' $c1>Freeze Draws, Allow Adding, Auto Activate</option>
				<option value='2' $c2>Freeze Draws, Allow Adding</option>
				<option value='3' $c3>Freeze Draws, Freeze Adding</option>
			</select>
			<input type='hidden' name='auto_save' value='1' />
		</form>";
		$html = geoHTML::addOption('Account Balance',geoString::displayPrice($user_data['account_balance']));
		
		if ($user_data['account_balance'] < 0){
			$last_positive = ($user_data['date_balance_negative'] > 10)? date("M d,Y G:i - l",$user_data["date_balance_negative"]): 'Unknown';
			//reset to current time
			$last_positive .= " ".geoHTML::addButton('Reset',"index.php?page=users_view&amp;b=$user_id&amp;account_balance[reset_negative_balance_date]=1&auto_save=1");
			$html .= geoHTML::addOption('Balance Negative Since:',$last_positive);
		}
		
		$html .= geoHTML::addOption('Account Balance Status',$status);
		return $html;
	}
	
	public static function Admin_user_management_update_users_view($user_id){
		$user_id = intval($user_id);
		if (!$user_id){
			return;
		}
		if (!isset($_POST['account_balance']) || !is_array($_POST['account_balance'])){
			//not for us, just return
			return;
		}
		$data = $_POST['account_balance'];
		
		$db = DataAccess::getInstance();
		
		$sql = 'SELECT `account_balance`, `date_balance_negative`, `balance_freeze` FROM '.geoTables::userdata_table.' WHERE `id` = '.$user_id;
		$user_data = $db->GetRow($sql);
		
		if ($user_data === false){
			trigger_error('ERROR SQL: Sql: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return;
		}
		
		if ($user_data['account_balance'] < 0){
			if (isset($data['reset_negative_balance_date']) && $data['reset_negative_balance_date']){
				$sql = "UPDATE ".geoTables::userdata_table." SET `date_balance_negative`=? WHERE `id`=$user_id LIMIT 1";
				$db->Execute($sql, array(geoUtil::time()));
				$user_data["date_balance_negative"] = geoUtil::time();
			}
			//put any other update stuff that requires a negative balance here.
		}
		if (isset($data['status']) && $data['status'] <= 3){
			$sql = "UPDATE ".geoTables::userdata_table." SET `balance_freeze`=? WHERE `id`=$user_id LIMIT 1";
			$db->Execute($sql, array(intval($data['status'])));
		}
	}
	
	public static function Admin_user_management_edit_user_form ($user_id)
	{
		$user_id = intval($user_id);
		if (!$user_id){
			return;
		}
		$db = DataAccess::getInstance();
		$sql = 'SELECT `account_balance`, `date_balance_negative`, `balance_freeze` FROM '.geoTables::userdata_table.' WHERE `id` = '.intval($user_id);
		$user_data = $db->GetRow($sql);
		
		if ($user_data === false){
			trigger_error('ERROR SQL: Sql: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return;
		}
		
		$balance = geoNumber::format($user_data['account_balance'], true);
		$html = "<div class='form-group'>
					<label class='control-label col-xs-12 col-sm-4'>Account Balance</label>
					<div class='col-xs-12 col-xs-6'>
						<div class='input-group'>
							<div class='input-group-addon'>".$db->get_site_setting('precurrency')."</div>
								<input type='text' class='form-control' name='account_balance[balance]' id='accountBalanceInput' value='{$balance}' size='5' />
								<div class='input-group-addon'>".$db->get_site_setting('postcurrency')."</div>
							</div>
						</div>
					</div>";
		
		//admin note
		$html .= "<div class='form-group' id='balanceNoteBox' style='display: none;'>
					<label class='control-label col-xs-12 col-sm-4'>User-Viewable Note for Balance Change: </label>
					<div class='col-xs-12 col-sm-6'>
						<input type='text' name='account_balance[note]' size='6' class='form-control' />
					</div>
				</div>
				<script type='text/javascript'>
					var toggleAccountBalanceNote = function () {
						if (jQuery('#accountBalanceInput').val() != '{$balance}') {
							jQuery('#balanceNoteBox').show();
						} else {
							jQuery('#balanceNoteBox').hide();
						}
					}
					jQuery('#accountBalanceInput').keyup(toggleAccountBalanceNote);
					toggleAccountBalanceNote();
				</script>
		";
		
		if ($user_data['account_balance'] < 0){
			$last_positive = ($user_data['date_balance_negative'] > 10)? date("M d,Y G:i - l",$user_data["date_balance_negative"]): 'Unknown';
			//reset to current time
			$last_positive .= " <a href='index.php?page=users_view&amp;b=$user_id&amp;account_balance[reset_negative_balance_date]=1&auto_save=1' class='btn btn-xs btn-danger'>Reset</a>".geoHTML::addButton('Reset',"");
			$html .= "<div class='form-group'>
						<label class='control-label col-xs-12 col-sm-4'>Balance Negative Since:</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>
							$last_positive
						</div>
						</div>";
		}
		
		$c0 = $c1 = $c2 = $c4 = '';
		if ($user_data['balance_freeze'] > 0){
			switch ($user_data['balance_freeze']){
				case 1:
					//frozen only until they pay off their balance.
					$status = 'Paying with balance: <span style="color: red;">Frozen</span><br />
								Adding to Balance: <span style="color: green;">Active</span><br />
								AUTO Activation: <span style="color: green;">ON</span><br />
								(Once negative balance paid, Paying with Balance will activate)';
								
					$c1 = 'selected="selected"';
					break;
				case 2:
					//can only add to balance, admin has to un-freeze.
					$status = 'Paying with balance: <span style="color: red;">Frozen</span><br />
								Adding to Balance: <span style="color: green;">Active</span><br />
								AUTO Activation: <span style="color: red;">OFF</span><br />
								(Requires admin to change status)';
					$c2 = 'selected="selected"';
					break;
				case 3:
				default:
					$status = 'Paying with balance is <span style="color: red;">Frozen</span><br />
								Adding to Balance is <span style="color: red;">Frozen</span><br />
								AUTO Activation: <span style="color: red;">OFF</span>';
					//frozen all the way, cannot add to or take away from account balance.
					$c3 = 'selected="selected"';
					break;
			}
		} else {
			$status = 'Paying with balance: <span style="color: green;">Active</span><br />
						Adding to Balance: <span style="color: green;">Active</span>';
			$c0 = 'selected="selected"';
		}
		if ($user_data['account_balance'] >= 0){
			$c1 .= " disabled='disabled'";
		}
		$status .= "<br />
		
			<select name='account_balance[status]' class='form-control' onchange='document.forms.account_balance_status.submit()'>
				<option value='0' $c0>Fully Active</option>
				<option value='1' $c1>Freeze Draws, Allow Adding, Auto Activate</option>
				<option value='2' $c2>Freeze Draws, Allow Adding</option>
				<option value='3' $c3>Freeze Draws, Freeze Adding</option>
			</select>
		";
		
		$html .= "<div class='form-group'>
						<label class='control-label col-xs-12 col-sm-4'>Account Balance Status</label>
						<div class='col-xs-12 col-sm-6 vertical-form-fix'>
							$status
						</div>
					</div>";
		return $html;
	}
	
	public static function Admin_user_management_update_user_info ($user_id)
	{
		$user_id = intval($user_id);
		if (!$user_id){
			return;
		}
		if (!isset($_POST['account_balance']) || !is_array($_POST['account_balance'])){
			//not for us, just return
			return;
		}
		$data = $_POST['account_balance'];
		
		$db = DataAccess::getInstance();
		
		$user = geoUser::getUser($user_id);
		if (!$user) {
			return;
		}
		if (isset($data['balance'])) {
			$balance = geoNumber::deformat($data['balance'], true);
			$adjustment = $balance - $user->account_balance; //adjustment = newBalance - oldBalance
			if ($adjustment) {
				//only apply change if there is a change
				$admin_note = $data['note'];
				self::_adjustBalance($adjustment, $user_id, 0, $admin_note);
			}
		}
		if ($user->account_balance < 0){
			if (isset($data['reset_negative_balance_date']) && $data['reset_negative_balance_date']){
				$user->date_balance_negative = geoUtil::time();
			}
			//put any other update stuff that requires a negative balance here.
		}
		if (isset($data['status']) && $data['status'] <= 3){
			$user->balance_freeze = intval($data['status']);
			$sql = "UPDATE ".geoTables::userdata_table." SET `balance_freeze`=? WHERE `id`=$user_id LIMIT 1";
			$db->Execute($sql, array(intval($data['status'])));
		}
	}
	
	public static function geoCart_initSteps($allPossible=false){
		
	}
	public function geoCart_initItem_new(){
		if (!geoMaster::is('site_fees')) {
			return false;
		}
		$cart = geoCart::getInstance();
		$gateway = geoPaymentGateway::getPaymentGateway(self::type);
		$min = $gateway->get('min_add_to_balance');
		//see if user has negative balance, if so set defalt balance to what
		//they owe.
		$current_balance = $cart->user_data['account_balance'];
		
		if ($current_balance < 0 && ($current_balance * -1) > $min) {
			$min = ($current_balance * -1);
		}
		
		if ($min === false || $min < 0){
			//default min is no good, set it to default of $5
			$gateway->set('min_add_to_balance','5.00');
			$gateway->save();
			$min = 5.00;
		}
		if (isset($_GET['account_balance_add']) && floatval($_GET['account_balance_add']) > $min){
			$min = abs(floatval($_GET['account_balance_add']));
		}
		$this->setCost($min);
		return true;
	}
	public static function geoCart_cartCheckVars(){
		$cart = geoCart::getInstance();
		
		if (!is_object($cart->item) || $cart->item->getType() !== self::type){
			return;
		}
		$msgs = $cart->db->get_text(true, 10202);
		$gateway = geoPaymentGateway::getPaymentGateway(self::type);
		$min = $gateway->get('min_add_to_balance');
		if ($cart->user_data['account_balance'] < 0 && $min > abs($cart->user_data['account_balance']) && !$gateway->get('allow_positive')){
			//the amount they owe is less than the min amount they are allowed to add, they are not allowed to go positive
			//so set the min_add to be the amount they owe
			$min = abs($cart->user_data['account_balance']);
		}
		if (isset($_POST['account_balance_add'])){
			$cart->addError(); //they are updating the cost, not clicking on checkout button.
			
			$cost = geoNumber::deformat($_POST['account_balance_add']);
			if ($cost < $min){
				$cart->addErrorMsg('account_balance',$msgs[500342].geoString::displayPrice($min).$msgs[500343]);
				return;
			}
			$cart->item->setCost($cost);
			return;
		}
		if ($cart->item->getCost() < $min){
			//error!  price not good!
			$cart->addError();
			$cart->addErrorMsg('account_balance',$msgs[500342].geoString::displayPrice($min).$msgs[500343]);
			return;
		}
		
		$balance_after = ($cart->user_data['account_balance'] + $cart->item->getCost());
		if ($balance_after > 0 && !$gateway->get('allow_positive')){
			$cart->addError();
			$cart->addErrorMsg('account_balance',$msgs[500344]);
			return;
		}
	}
	public static function geoCart_cartProcess(){
		
	}
	public function getDisplayDetails($inCart,$inEmail=false) {
		$db = DataAccess::getInstance();
		$price = $this->getCost(); //people expect numbers to be positive...
		$gateway = geoPaymentGateway::getPaymentGateway(self::type);
		$min = $gateway->get('min_add_to_balance');
		$msgs = $db->get_text(true, 10202);
		
		$return = array (
			'css_class' => '',
			'title' => $msgs[500313],
			'canEdit' => true, //whether can edit it or not
			'canDelete' => true, //whether can remove from cart or not
			'canPreview' => false, //whether can preview the item or not
			'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
			'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
			'cost' => $price, //amount this adds to the total, what getCost returns
			'total' => $price,
			'children' => false
		);
		
		$tpl = new geoTemplate('system','order_items');
		$tpl->assign('price',geoString::displayPrice($price, '',''));
		if ($inCart) {
			$cart = geoCart::getInstance();
			//let template know about current balance so it can display it in the cart view
			//if it wants to.
			$tpl->assign('current_balance',$cart->user_data['account_balance']);
			
			//see if we should show input field instead of price
			if ($price < $min || $cart->getAction() == 'new' || $cart->getAction() == 'edit'){
				$tpl->assign('edit',1);
				
				
				$return['priceDisplay'] = $msgs[500345];
				$return['canEdit'] = false;
			} else {
				$tpl->assign('edit',0);
			}			
		} else {
			$tpl->assign('edit',0);
		}
		$tpl->assign('precurrency',$db->get_site_setting('precurrency'));
		$tpl->assign('postcurrency',$db->get_site_setting('postcurrency'));
		
		$tpl->assign('inCart',$inCart);
		$tpl->assign('minimum_add_to_balance',$min); //not used by default, but some may want to display this amount
		
		$return['title'] = $tpl->fetch('account_balance/enter_amount.tpl');
		
		//go through children...
		$order = $this->getOrder();
		$items = $order->getItem();
		$keys = array_keys($items);
		$children = array();
		foreach ($keys as $i){
			if (is_object($items[$i]) && is_object($items[$i]->getParent())){
				$p = $items[$i]->getParent();
				if ($p->getId() == $this->getId()){
					//This is a child of mine...
					$displayResult = $item->getDisplayDetails($inCart,$inEmail);
					if ($displayResult !== false) {
						//only add if they do not return bool false
						$children[$item->getId()] = $displayResult;
						$return['total'] += $children[$item->getId()]['total']; //add to total we are returning.
					}
				}
			}
		}
		if (count($children)){
			$return['children'] = $children;
		}
		return $return;
	}
	
	public function getCostDetails ()
	{
		$return = array (
					'type' => $this->getType(),
					'extra' => null,
					'cost' => $this->getCost(),
					'total' => $this->getCost(),
					'children' => array(),
		);
		
		//figure out if the account balance will be negative, zero, or positive after applying
		//the amount from this order item.
		$oldBalance = geoUser::getData($this->getOrder()->getBuyer(), 'account_balance');
		$shoes = $newBalance = $oldBalance + $this->getCost();
		if ($shoes < 0) {
			$return['extra'] = 'negative_balance';
		} else if($shoes == 0) {
			$return['extra'] = 'zero_balance';
		} else {
			$return['extra'] = 'positive_balance';
		}
		
		//call the children and populate 'children'
		$order = $this->getOrder();//get the order
		$items = $order->getItem();//get all the items in the order
		$children = array();
		foreach ($items as $i => $item) {
			if (is_object($item) && $item->getType() != $this->getType() && is_object($item->getParent())) {
				$p = $item->getParent();//get parent
				if ($p->getId() == $this->getId()){
					//Parent is same as me, so this is a child of mine, add it to the array of children.
					//remember the function is not static, so cannot use callDisplay() or callUpdate(), need to call
					//the method directly.
					$costResult = $item->getCostDetails();
					if ($costResult !== false) {
						//only add if they do not return bool false
						$children[$item->getId()] = $costResult;
						$return['total'] += $costResult['total']; //add to total we are returning.
					}
	
				}
			}
		}
		if ($return['total']==0) {
			//total is 0, even after going through children!  no cost details to return
			return false;
		}
		if (count($children)) {
			//add children to the array
			$return['children'] = $children;
		}
		return $return;
	}
	
	public static function geoCart_initItem_forceOutsideCart(){
		return true;
	}
	
	public static function geoCart_cartDisplay_newButton($inModule = false)
	{
		if (self::isAnonymous()) return '';
		
		$cart = geoCart::getInstance();
		
		if (!geoMaster::is('site_fees')) {
			return '';
		}
		
		geoPaymentGateway::setGroup($cart->user_data['group_id']);
		$planItem = geoPaymentGateway::getPaymentGateway('account_balance');//geoPlanItem::getPlanItem('account_balance',$cart->price_plan['price_plan_id'],0);
		
		if (!is_object($planItem) || !$planItem->getEnabled()) {
			return '';
		}
		$msgs = DataAccess::getInstance()->get_text(true);
		if ($inModule) {
			//really being called by my_account_links_newButton - same logic, different return value
			return array (
				'icon' => $msgs[500490],
				'label' => $msgs[500489]
			);
		} else {
			if(!$msgs) {
					//haven't gotten text for this page yet -- get it explicitly from cart main
					$msgs = DataAccess::getInstance()->get_text(true, 10202);
			}
			return $msgs[500255];
		}
	}
	
	public static function my_account_links_newButton ()
	{
		return self::geoCart_cartDisplay_newButton(true);
	}
	
	/**
	 * Required by interface.
	 * Used: in geoCart::initSteps()
	 * 
	 * Determine whether or not the other_details step should be added to the steps of adding this item
	 * to the cart.  This should also check any child items if it does not need other_details itself.
	 *
	 * @return boolean True to add other_details to steps, false otherwise.
	 */
	public static function geoCart_initSteps_addOtherDetails(){
		return false;
	}
	
	/**
	 * Public facing entity to adjust the account balance, and record the transaction
	 * in the system.
	 * 
	 * @param float $adjust_amount
	 * @param int $user_id
	 * @param int $order_id The order ID that was the reason for this adjustment.
	 * @param string $admin_note Used in admin panel
	 * @since Version 6.0.0
	 */
	public static function adjustUserBalance ($adjust_amount, $user_id, $order_id = 0, $admin_note = '')
	{
		return self::_adjustBalance($adjust_amount, $user_id, $order_id, $admin_note);
	}
	
	private static function _adjustBalance ($amount, $userId = 0, $orderId = 0, $adminNote = '')
	{
		if (!$userId) return false;
		if (!$amount) return false;
		
		$user = geoUser::getUser($userId);
		if (!$user) return false;
		
		//create a stand-alone transaction.
		$trans = geoTransaction::getTransaction(0);
		$trans->setAmount($amount * -1);
		$trans->setDate(geoUtil::time());
		$trans->setGateway('account_balance');
		$trans->setDescription('Add to balance');
		
		$trans->setUser($userId);
		$trans->setStatus(1);
		
		$trans->set('adjustment', 1);
		
		if ($orderId) {
			$trans->set('orderId', $orderId);
		} else {
			//admin user changing stuff, save note
			$trans->set('admin_note',$adminNote);
		}
		$trans->save();
		
		//now adjust balance
		$balance = $user->account_balance + $amount;
		self::_updateBalance($userId, $balance);
	}
	
	private static function _updateBalance($userId, $new_balance)
	{
		$db = DataAccess::getInstance();
		$user = geoUser::getUser($userId);
		
		if (!$user) {
			return;
		}
		$mult = ($newStatus == 'active')? 1: -1;
		$activate = ($newStatus == 'active')? 1: 0;
		if ($new_balance >= 0){
			//make sure the date_balance_negative is reset to 0 since account balance is no longer negative.
			$user->date_balance_negative = 0;
			if ($user->balance_freeze == 1){
				//admin has frozen account until they pay it off.  Well, it is payed off now, so un-freeze account.
				$user->balance_freeze = 0;
			}
			//if balance freeze is set to anything besides 1, there is no auto-changing of the account balance.
		} else if ($new_balance < 0 && $user->account_balance >= 0) {
			//Its going from positive to negative balance,
			//so set neg balance date
			$user->date_balance_negative = geoUtil::time();
		}
		$user->account_balance = $new_balance;
	}
	
	/**
	 * Changes the status on an order item.  Built-in statuses are active, pending, and
	 * pending_alter.  Recommended to overwrite this function if the item needs to
	 * do anything at the time it is activated or deactivated.  Even if this is overloaded,
	 * it is recommended to still call the parent function to do common stuff.
	 *
	 * @param string $newStatus either "active", "pending", or "pending_alter"
	 * @param bool $sendEmailNotices If set to false, no e-mail notifications will be
	 *  sent, even if they are supposed to according to settings set in admin.
	 * @param bool $updateCategoryCount If set to true, the category count for this item will
	 *  be updated.  If false, it assumes whoever is calling this will do the updating all
	 *  at once for efficiency.
	 */
	public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false){
		if ($this->getStatus() == $newStatus) {
			return true;
		}
		
		$db = DataAccess::getInstance();
		
		if ($newStatus == 'active' || $this->getStatus() == 'active') {
			
			$order = $this->getOrder();
			$mult = ($newStatus == 'active')? 1: -1;
			
			//adjust the balance accordingly.
			self::_adjustBalance(($this->getCost() * $mult), $order->getBuyer(), $order->getId());
		}
		//let parent do normal stuff
		parent::processStatusChange($newStatus,$sendEmailNotices,$updateCategoryCount);
		return true;
	}
	
	public static function getTransactionDescription(){
		return 'Add to account balance';
	}
	
	
	public static function getParentTypes(){
		//this is main order item, no parent types
		//return array(0, 'classified', 'auction', 'dutch_auction');
		return array ();
	}
	/**
	 * Optional
	 * Used: in User_management_information::display_user_data()
	 * 
	 * Use this to display info on the user info page.  Stuff like displaying
	 * current account balance, tokens remaining, etc.  This will appear below
	 * the price plan info.
	 * 
	 * @return string String to use, recommended to use the same format as other
	 *  info on that page.
	 */
	public static function User_management_information_display_user_data ()
	{
		//see if the user's group is good
		$user_id = geoSession::getInstance()->getUserId();
		
		if (!$user_id) {
			//not logged in??
			return;
		}
		$user = geoUser::getUser($user_id);
		
		geoPaymentGateway::setGroup($user->group_id);
		
		$gateway = geoPaymentGateway::getPaymentGateway('account_balance');
		if ($gateway->getEnabled()) {
			$db = DataAccess::getInstance();
			$msgs = $db->get_text(true);
			$label = $msgs[2538];
			
			$display_amount = geoString::displayPrice($user->account_balance);
			$add_money_link = ($db->get_site_setting('use_ssl_in_sell_process'))? $db->get_site_setting('classifieds_ssl_url'): $db->get_site_setting('classifieds_file_name');
			$add_money_link .= '?a=cart&amp;action=new&amp;main_type=account_balance';
			
			return array ('label' => $label, 'value' => "$display_amount <a href=\"$add_money_link\">{$msgs[2539]}</a>");
		}
	}
	
	public function processRemove()
	{
		if ($this->getStatus() == 'active') {
			//need to remove the account balance from the user,
			//just let process status change do all the hard work for us :)
			return $this->processStatusChange('pending',false);
		} else {
			//balance isn't active, so don't need to remove it
			//probably deleting an inactive item from the admin
			//return true to let admin know it's ok to proceed
			return true;
		}
		
	}
	
	public static function adminItemDisplay ($item_id)
	{
		if (!$item_id){
			return '';
		}
		$item = geoOrderItem::getOrderItem($item_id);
		if (!is_object($item) || $item->getType() != self::type) {
			return '';
		}
		
		$info = '';
		$info .= geoHTML::addOption('Item Type','Adding to Account Balance');
		$children = geoOrderItem::getChildrenTypes(self::type);
		$info .= geoOrderItem::callDisplay('adminItemDisplay',$item_id,'',$children);
		return $info;
	}
	
	/**
	 * Optional.
	 * Used: in geoCart
	 * 
	 * This is used to display what the action is if this order item is the main type.  It should return
	 * something like "adding new listing" or "editing images".
	 * 
	 * @return string
	 */
	public static function getActionName ($vars)
	{
		if ($vars['step'] == 'my_account_links') {
			//short version
			return geoCart::getInstance()->site->messages[501622];
		} else {
			//action interupted text
			//text "placing new auction"
			return geoCart::getInstance()->site->messages[500394];
		}
	}
	
	protected static function _success($order, $transaction, $gateway)
	{
		//echo "hi mom";
	}
}