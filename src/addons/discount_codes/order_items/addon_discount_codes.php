<?php
//discount_codes/order_items/addon_discount_codes.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.6.3-73-gda0dfd6
## 
##################################
 
# discount_codes addon

class addon_discount_codesOrderItem extends geoOrderItem {
	
	/**
	 * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
	 *
	 * @var string
	 */
	protected $type = 'addon_discount_codes';
	const type = 'addon_discount_codes';
	
	/**
	 * Needs to be the order that this item will be processed.
	 * Making it so that it appears after the "subtotal" item, 
	 * and before the "tax" item.
	 *
	 * @var int
	 */
	protected $defaultProcessOrder = 10050;
	const defaultProcessOrder = 10050;
	
	public function displayInAdmin()
	{
		return false;
	}
	
	/**
	 * Required by interface.
	 * Used: in geoCart::initSteps() (and possibly other locations)
	 * 
	 * 
	 */
	public static function geoCart_initSteps($allPossible=false){
		//discount code doesn't have it's own page.
		
		$children = geoOrderItem::getChildrenTypes(self::type);
		geoOrderItem::callUpdate('geoCart_initSteps',$allPossible,$children);
	}
	
	/**
	 * Required by interface.
	 * Used: in geoCart::initItem()
	 * 
	 * Whether or not a seperate cart should be used just for this order
	 * item or not.  The alternate cart would be in addition to a "primary" cart
	 * that may have things in it already, and this item would be the ONLY thing
	 * in the cart.
	 * 
	 * It is typical to not use this (return false), an example of when this may want
	 * to be used, is to allow adding to a site balance so that a user can pay for the 
	 * rest of their cart.
	 * 
	 * @return boolean True to force creating "parellel" cart just 
	 *  for this item, false otherwise.
	 */
	public static function geoCart_initItem_forceOutsideCart() {
		//most need to return false.
		return false;
	}
	
	public static function geoCart_getCartItemDetails ()
	{
		$cart = geoCart::getInstance();
		
		self::_init (false, $cart->order, $cart->cart_variables);
	}
	/**
	 * Required by interface.
	 * Used: various locations.
	 * 
	 * This should return an array of the different order items that this
	 * order item is a child of.  If this is a main order item type, it 
	 * should return an empty array.
	 * 
	 * @return array
	 */
	public static function getParentTypes(){
		//for "parent" order item, returne empty string.
		return array();
	}
	private static $_discount_code_error = '';
	public function getDisplayDetails ($inCart,$inEmail=false)
	{
		$order = $this->getOrder();
		$cartVars = array();
		if ($inCart) {
			$cart = geoCart::getInstance();
			$cartVars = $cart->cart_variables;
		} else {
			if (!$this->get('discount_code')) {
				return false;
			}
		}
		self::_init(true, $order, $cartVars); //make sure amount is still good
		
		$return = array (
			'css_class' => 'subtotal_cart_item',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
			'title' => 'Discount Code',//text that is displayed for this item in list of items purchased.
			'canEdit' => false, //show edit button for item?
			'canDelete' => false, //show delete button for item?
			'canPreview' => false, //show preview button for item?
			'priceDisplay' => geoString::displayPrice($this->getCost()), //Price as it is displayed
			'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
			'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
			'children' => array() 	//should be array of child items, with the index
	 								//being the item's ID, and the contents being associative array like
	 								//this one.  If no children, it should be an empty array.  (Careful 
									//not to get into any infinite recursion)
		);
		
		$tpl = new geoTemplate('addon','discount_codes');
		if ($inCart) {
			$tpl->assign($cart->getCommonTemplateVars());
		}
		$tpl->assign('msgs',self::$_msgs);
		$tpl->assign('percent',floatval($this->get('discount_percentage')));
		$tpl->assign('static',floatval($this->get('discount_static')));
		$tpl->assign('cart_total',$order->getOrderTotal());
		$tpl->assign('cost',$this->getCost());
		$tpl->assign('error',self::$_discount_code_error);
		$tpl->assign('inCart', $inCart);
		//Note: Don't need to fromDB or toDB stuff when using get and set methods, as those do it for you
		$tpl->assign('discount_code', geoString::specialChars($this->get('discount_code')));
		
		$return['priceDisplay'] = $tpl->fetch('price.tpl');
		$return['title'] = $tpl->fetch('title.tpl');
		
		//go through children...
		$items = $order->getItem();//get all the items in the order
		$children = array();
		foreach ($items as $i => $item){
			if (is_object($item) && $item->getType() != $this->getType() && is_object($item->getParent())){
				$p = $item->getParent();//get parent
				if ($p->getId() == $this->getId()){
					//Parent is same as me, so this is a child of mine, add it to the array of children.
					//remember the function is not static, so cannot use callDisplay() or callUpdate(), need to call
					//the method directly.
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
			//add children to the array
			$return['children'] = $children;
		}
		return $return;
	}
	
	public function getCostDetails ()
	{
		//Most use this exactly AS-IS...
	
		$return = array (
					'type' => $this->getType(),
					'extra' => null,
					'cost' => $this->getCost(),
					'total' => $this->getCost(),
					'children' => array(),
		);
	
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
	
	public static function geoCart_deleteCheckVars(){
		//TODO: Remove the discount code entered, and set cost to 0
		
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
		$children = geoOrderItem::getChildrenTypes(self::type);
		//can call directly, since this function is required by interface.
		if (geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails',null,'bool_true',$children)){
			//one of the children want to display it, so return true.
			return true;
		}
		
		return false;
	}
	
	private static $_msgs = array();
	
	private static $_init_run = false;
	private static function _init($force_run = false, $order = null, $cart_variables = array()){
		if (self::$_init_run && !$force_run){
			return;
		}
		if (!$order) {
			return;
		}
		self::$_init_run = true;
		
		$db = DataAccess::getInstance();
		
		if (!geoMaster::is('site_fees')) {
			return;
		}
		
		$items = $order->getItem(self::type);
		
		if ($cart_variables['order_item'] == -1|| $order->getOrderTotal(self::defaultProcessOrder) == 0 || !self::_check_discount_code_use($order)){
			//this is a stand-alone cart, don't auto-add ourself to it!
			if (is_array($items)){
				foreach ($items as $item){
					if (is_object($item)){
						//remove the sub-total from the order!
						$id = $item->getId();
						geoOrderItem::remove($id);
						$order->detachItem($id);
					}
				}
			}
			return;
		}
		
		self::$_msgs = geoAddon::getText('geo_addons','discount_codes');
		$discount_item = null;
		if (!is_array($items) || !count($items)){
			//no discount code attached yet, attach one
			$discount_item = new addon_discount_codesOrderItem;
			$discount_item->setOrder($order);
			$order->addItem($discount_item);
			$order->save();
		} else {
			$c=0;
			foreach ($items as $k => $item){
				if (!$c){
					$discount_item = $item;
					$c++;
					continue;//leave the first one
				}
				//more than one discount code, how did that happen?
				$id = $item->getId();
				geoOrderItem::remove($id);
				$order->detachItem($id);
				unset($items[$k]);
			}
		}
		if (is_object($discount_item)){
			//make sure amount is correct
			if (isset($_REQUEST['discount_code'])){
				if (strlen($_REQUEST['discount_code']) == 0){
					$discount_item->set('discount_code','');
					$discount_item->set('discount_id', false);
				} else {
					$code = geoString::specialCharsDecode($_REQUEST['discount_code']);
					$data = self::_getData($code, $order);
					if (isset($data['discount_code']) && geoString::fromDB($data['discount_code']) == $code){
						//user specified a discount code to use
						$discount_item->set('discount_code',geoString::fromDB($data['discount_code']));
						$discount_item->set('discount_id', $data['discount_id']);
					} else {
						//error retrieving discount code
						self::$_discount_code_error = true;
					}
					unset($data);
				}
			}
			$data = null;
			
			if ($discount_item->get('discount_code')) {
				$data = self::_getData($discount_item->get('discount_code'), $order);
			}
			if ($data) {
				if ($db->get_site_setting('joe_edwards_discountLink')) {
					//make sure this code gives a valid target
					$target = $discount_item->joe_edwards_getCrossDebitTarget();
					if($target !== false) {
						$discount_static = 0;
						//get number/cost of base listings
						$listingItems = $discount_item->GetListingItemData();
						foreach($listingItems as $cost) {
							$discount_static += $cost;
						}
					} else {
						$discount_static = 0;
					}
				} else {
					//might could use this later to allow admin-specified static discounts instead of percentages
					$discount_static = 0;
				}			
				//calculate cost, make sure it is updated whenever price is changed.
				
				//be sure to not apply percentage discount to the portion of the total removed by the static discount
				$discount_percentage = ($order->getOrderTotal(self::defaultProcessOrder) - $discount_static) * (.01 * $data['discount_percentage']);
				
				$discount_amount = ($discount_static + $discount_percentage) * -1;
				
				$discount_item->setCost($discount_amount);
				$discount_item->set('discount_percentage',$data['discount_percentage']); //save as a displayable percentage
				$discount_item->set('discount_static',$discount_static);
			} else {
				//no discount!
				$discount_item->setCost(0);
				$discount_item->set('discount_percentage',0);
				$discount_item->set('discount_static',0);
				$discount_item->set('discount_code',false);
				$discount_item->set('discount_id',false);
			}
			$discount_item->save();
			
			$cart = geoCart::getInstance();
			if ($cart && $cart->order) {
				//if we don't have a cart here, there's no major problem...we just can't log quite as perfectly
				//if we DO have a cart, get its items, figure out which ones are listings, and mark those as using this discount code
				$sql = "select discount_id from ".addon_discount_codes_info::DISCOUNT_TABLE." where discount_code = ?";
				$discount_id = $discount_item->get('discount_id');
				$discount_percentage = $discount_item->get('discount_percentage');
				
				$items = $cart->order->getItem();
				foreach($items as $item) {
					if ($item->isListingItem()) {
						$sv = $item->get('session_variables');
						$sv['discount_id'] = $discount_id;
						$sv['discount_percentage'] = $discount_percentage;
						$cost = ($item->getCost()) ? $item->getCost() : 0;
						$sv['discount_amount'] = ($cost * $discount_percentage / 100 );
						$item->set('session_variables',$sv);
						$item->save();
					}
				}
			} else {
				//no cart -- probably the admin doing something weird
				//nothing to do here
			}
		}
	}
	
	/**
	 * Only used by the joe_edwards cross-debit system to subtract tokens/balance from target other user when the
	 * order goes live. All other uses of discount codes can ignore this function
	 *
	 * the cross-debit system allows a user to place a listing using a discount code that is "linked" to another user (the "target" user)
	 * when this happens, the cost of the base listing (but not any Extras) comes out of the target user's tokens or account balance
	 * 
	 * to enable cross-debiting, the "undocumented" site setting 'joe_edwards_discountLink' must be turned on
	 * and the discount code to be used must then have a target user id 'attached' to it through the admin
	 * 
	 */
	public function processStatusChange($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
	{
		//let parent do it's thing (setting status and all that)
		parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
		
		if ($newStatus == $this->getStatus()) {
			return;
		}
		$db = DataAccess::getInstance();
		if (!$db->get_site_setting('joe_edwards_discountLink')) {
			//only need to worry about this function if doing joe_edwards stuff
			//otherwise, the cost has already been modified
			return;
		}
		$target = $this->joe_edwards_getCrossDebitTarget();
		if ($target === false) {
			//no target found
			return;
		}
		
		if ($newStatus == 'active') {
			//do the cross-debit
			
			
			//make sure we haven't already debited for this item
			$alreadyDone = $this->get('didCrossDebit',0);
			if ($alreadyDone) {
				//probably re-activating this after an admin deactivation
				//no need to debit again! 
				return;
			}			
			
			$listingItems = $this->GetListingItemData();
			
			if (!$listingItems) {
				//not charging for any listings
				//nothing to do here
				return; 
			}
			
			
			$numItems = count($listingItems);
			//figure out whether to crossdebit from tokens or balance
			if ($numItems <= $target['tokens']) {
				//all from tokens
				$tokensUsed = $numItems;
				$balanceUsed = 0;
			} else if ($target['tokens'] > 0) {
				//use all tokens first
				$tokensUsed = $target['tokens'];
				//the remainder comes out of balance
				$balanceUsed = 0;
				for($i = $tokensUsed; $i < $numItems; $i++) {
					//skip over any listings we used tokens for
					//($listingItems is already rsort()'d, so tokens get used for the most expensive listings first)
					$balanceUsed += $listingItems[$i];
				}
			} else {
				//all from balance
				$tokensUsed = 0;
				$balanceUsed = 0;
				foreach($listingItems as $cost) {
					$balanceUsed += $cost;
				}
			}
			
			
			if ($tokensUsed > 0) {
				//debit the target's tokens
				$tokensDebited = 0;
				$sql = "select * from ".geoTables::user_tokens." where user_id = ? and token_count > 0 and expire > ? order by expire ASC";
				$result = $db->Execute($sql, array($target['id'], geoUtil::time()));
				
				//affected tokens may span multiple db rows, so loop through until all are removed
				do {
					$token_row = $result->FetchRow();
					
					//number of tokens on this DB row
					$token_count = $token_row['token_count'];
					
					//number of tokens to remove from this DB row
					$removeFromRow = ($token_count < $tokensUsed) ? $token_count : $tokensUsed;
					
					//new count of tokens on this row
					$tokensLeftOnRow = $token_count - $removeFromRow;
					
					$sql = "update ".geoTables::user_tokens." set token_count = ? where id = ?";
					$update = $db->Execute($sql, array($tokensLeftOnRow, $token_row['id']));
					
					
					if($update) {
						$tokensDebited += $removeFromRow;
					}
					
				} while ($tokensDebited < $tokensUsed);
			}
			
			if ($balanceUsed > 0) {
				//debit the target's balance
				$targetUser = geoUser::getUser($target['id']);
				$targetBalance = $targetUser->account_balance;
				$newBalance = $targetBalance - $balanceUsed; //with apologies to the shoe company ;)
				$targetUser->account_balance = $newBalance;
				
				//make a new transaction, so things are logged
				$transaction = new geoTransaction;
				$transaction->setAmount($balanceUsed);
				$transaction->setDate(geoUtil::time());

				$description = "Cross-debit from user: ".$this->getOrder()->getBuyer();
				$transaction->setDescription($description);
				$transaction->setGateway('account_balance');
				//$transaction->setInvoice($invoice);
				$transaction->setStatus(1);//since payment is automatic, do it automatically.
				$transaction->setUser($target['id']);
				$transaction->save();//save changes
			}
			
			//mark this as done, so we don't do it again
			$this->set('didCrossDebit',1);
			$this->save();			
		}
	}
	
	/**
	 * Custom for Joe Edwards. Finds data for the target of a cross-debit
	 * 
	 * the cross-debit system allows a user to place a listing using a discount code that is "linked" to another user (the "target" user)
	 * when this happens, the cost of the base listing (but not any Extras) comes out of the target user's tokens or account balance
	 * 
	 * to enable cross-debiting, the "undocumented" site setting 'joe_edwards_discountLink' must be turned on
	 * and the discount code to be used must then have a target user id 'attached' to it through the admin
	 *
	 * @return Array the data to use, or boolean false if no target found
	 */
	public function joe_edwards_getCrossDebitTarget ()
	{
		$db = DataAccess::getInstance();
		if (!$db->get_site_setting('joe_edwards_discountLink')) {
			return false;
		}
		
		//get the code in use
		$code = $this->get('discount_code');
		
		$target = array();
		
		//find the associated user data
		$data = self::_getData($code, $this->getOrder());
		$target['id'] = ($data)? (int)$data['user_id'] : 0;
		
		if (!$target['id']) {
			//no target found
			return false;
		}
		
		//get target's tokens
		$target['tokens'] = 0;
		$sql = "select * from ".geoTables::user_tokens." where user_id = ? and token_count > 0 and expire > ?";
		$result = $db->Execute($sql, array($target['id'], geoUtil::time()));
		while($line = $result->FetchRow()) {
			$target['tokens'] += $line['token_count'];
		}
		
		trigger_error('DEBUG CROSSDEBIT: found the target. ID: '.$target['id']. ' tokens: '.$target['tokens']);
		
		return $target;
	}
	
	/**
	 * Find the number and cost of "main," listing items (e.g. Classified items)
	 * mainly for use in the "joe edwards" stuff, to know how many tokens or how
	 * much balance to cross-debit 
	 * 
	 * potentially has uses outside of the cross-debit system, so doesn't require the beta switch be set to run
	 *
	 * @return Array
	 */
	private function GetListingItemData()
	{
		//must have cart to do this
		$cart = geoCart::getInstance();
		
		$listingItems = array();
		
		$items = $cart->order->getItem();
		foreach($items as $item) {
			if($item->isListingItem()) {
				//this is a listing "parent" item
				if($item->getCost() > 0) {
					$listingItems[] = $item->getCost();
				}
			}
		}
		
		if(count($listingItems) == 0) {
			return false;
		}
		
		//sort by cost, highest first
		rsort($listingItems, SORT_NUMERIC);
		
		return $listingItems;
	}
	
	private static $_check_discount_code_use;
	
	/**
	 * 
	 * Enter description here ...
	 * @param geoOrder $order
	 */
	private static function _check_discount_code_use($order)
	{
		if (!isset(self::$_check_discount_code_use)) {
			$db = DataAccess::getInstance();
			
			$groupId = self::_getGroupId($order);
			
			$sql = "SELECT `discount_id`, `is_group_specific` FROM ".addon_discount_codes_info::DISCOUNT_TABLE." WHERE `active` = 1 AND `starts`<=? AND (`ends`=0 OR `ends`>=?) AND `apply_normal`=1";
			if (!$groupId) {
				$sql .= " AND `is_group_specific`=0";
			}
			$all = $db->GetAll($sql, array(geoUtil::time(), geoUtil::time()));
			
			foreach ($all as $row) {
				if ($row['is_group_specific']==0 || self::_inAttachedGroup($groupId, $row['discount_id'])) {
					//all good
					self::$_check_discount_code_use = 1;
					break;
				}
			}
			if (!isset(self::$_check_discount_code_use)) {
				//didn't find any
				self::$_check_discount_code_use = 0;
			}
		}
		return self::$_check_discount_code_use;
	}
	
	private static function _inAttachedGroup ($groupId, $discount_id)
	{
		$db = DataAccess::getInstance();
		$groupId = (int)$groupId;
		$discount_id = (int)$discount_id;
		if (!$groupId || !$discount_id) {
			//nope
			return false;
		}
		$count = $db->GetOne("SELECT COUNT(*) FROM ".addon_discount_codes_info::DISCOUNT_GROUPS_TABLE."
			WHERE `group_id`=$groupId AND `discount_id`={$discount_id}");
		
		return ($count > 0);
	}
	
	private static function _getGroupId ($order)
	{
		$groupId = 0;
		$userId = $order->getBuyer();
		
		
		if (!$userId) {
			$anon = geoAddon::getUtil('anonymous_listing');
			if ($anon) {
				$anonReg = geoAddon::getRegistry('anonymous_listing');
				if($anonReg) {
					$userId = $anonReg->get('anon_user_id',0);
				}
			}
		}
		
		if ($userId) {
			$user = geoUser::getUser($userId);
			
			if ($user) {
				$groupId = (int)$user->group_id;
			}
		}
		return $groupId;
	}
	
	private static $_code_data = array();
	private static function _getData ($discount_code, $order)
	{
		if (!isset(self::$_code_data[$discount_code])) {
			$db = DataAccess::getInstance();
			
			$sql = "SELECT * FROM ".addon_discount_codes_info::DISCOUNT_TABLE." WHERE `discount_code` = ? AND `active`=1 AND `starts`<=? AND (`ends`=0 OR `ends`>=?) AND `apply_normal`=1 LIMIT 1";
			$data = $db->GetRow($sql, array(geoString::toDB($discount_code), geoUtil::time(), geoUtil::time()));
			
			if ($data['is_group_specific']==1) {
				//check group
				$groupId = self::_getGroupId($order);
				if (!self::_inAttachedGroup($groupId, $data['discount_id'])) {
					//oops, group no matchy, not able to use this discount code!
					$data = null;
				}
			}
			self::$_code_data[$discount_code] = $data;
		}
		
		return self::$_code_data[$discount_code];
	}
}