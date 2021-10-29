<?php
//order_items/provider_fee.php
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
## ##    17.01.0-14-g07e2a62
## 
##################################


class provider_feeOrderItem extends geoOrderItem {
	
	/**
	 * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
	 *
	 * @var string
	 */
	protected $type = "provider_fee";
	
	/**
	 * Optional, use this as a hassle-free way to determine the type without having to hard-code
	 * the type everywhere else, instead use self::type
	 *
	 */
	const type = 'provider_fee';
	
	/**
	 * Needs to be the order that this item will be processed.
	 *
	 * @var int
	 */
	protected $defaultProcessOrder = 9000; //subtotal is 10,000, tax is 20,000, total is handled by system.
	const defaultProcessOrder = 9000; //needs to be same as normal process order.
	
	
	public function displayInAdmin() {
		return true;
	}
	
	public function adminPlanItemConfigDisplay ($planItem)
	{
		$tpl = new geoTemplate('admin');
		$tpl->assign('enabled', $planItem->getEnabled());
		$tpl->assign('percentage', $planItem->get('provider_fee_percentage'));
		return $tpl->fetch('order_items/provider_fee/planItemSettings.tpl');
	}
	
	public function adminPlanItemConfigUpdate ($planItem)
	{
		$settings = $_POST['provider_fee'];
		$planItem->setEnabled($settings['enabled']);
		$percentage = $settings['percentage'];
		if(!is_numeric($percentage)) {
			geoAdmin::m('Percentage must be a number',geoAdmin::ERROR);
			return false;
		}
		$planItem->set('provider_fee_percentage', $percentage);
		return true;
	}
	
	public static function geoCart_initSteps($allPossible=false){
		
	}
	
	public static function geoCart_initItem_forceOutsideCart() {
		//most need to return false.
		return false;
	}
	public static function geoCart_getCartItemDetails ()
	{
		if (!geoMaster::is('site_fees')) {
			return;
		}
		$cart = geoCart::getInstance();
		if ($cart->cart_variables['order_item'] == -1 && !$cart->isRecurringCart()){
			//this is a stand-alone cart, don't auto-add ourself to it!
			return;
		}
		
		$planItem = geoPlanItem::getPlanItem(self::type,$cart->price_plan['price_plan_id'],$cart->price_plan['category_id']);
		if(!$planItem || !$planItem->isEnabled() || !$planItem->get('provider_fee_percentage')) {
			//nothing to do!
			return;
		}
		
		$order = $cart->order;
		
		$items = $order->getItem(self::type);
		if (!is_array($items)){
			$items = array();
		}
		$this_item = null;
		$total = $order->getOrderTotal(self::defaultProcessOrder);
		
		foreach ($items as $k => $item){
			if (is_object($item)){
				if (is_object($this_item)){
					//multiple tax items?  remove this one
					$id = $item->getId();
					geoOrderItem::remove($id);
					$order->detachItem($id);
				} else {
					$this_item = $item;
				}
			}
		}
		
		$fee_amount = $total * ($planItem->get('provider_fee_percentage') / 100);
		
		if ($fee_amount > 0) {
			//make sure to add tax item to order, and set the price on it.
			if (!is_object($this_item)){
				$this_item = new provider_feeOrderItem;
				$this_item->setOrder($order);
				$order->addItem($this_item);
			}
			$this_item->setCost($fee_amount);
			if ($cart && $cart->order && $cart->isRecurringCart() && $cart->item) {
				$this_item->setParent($cart->item);
			}
			$this_item->save();
		} elseif (is_object($this_item)) {
			//taxes are none, so remove the tax item if it exists
			$id = $this_item->getId();
			geoOrderItem::remove($id);
			$order->detachItem($id);
		}
	}
	
	public static function getParentTypes(){
		//no parents!
		return array();
	}
	
	
	public function getDisplayDetails ($inCart,$inEmail=false)
	{
		$cost = $this->getCost();
		$msgs = DataAccess::getInstance()->get_text(true, 10202);
		$return = array (
			'css_class' => 'tax_cart_item', //css class
			'title' => 'Provider Fee', //TODO: soften text
			'canEdit' => false, //show edit button for item?
			'canDelete' => false, //show delete button for item?
			'canPreview' => false, //show preview button for item?
			'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
			'priceDisplay' => geoString::displayPrice($cost), //Price as it is displayed
			'cost' => $cost, //amount this adds to the total, what getCost returns
			'total' => $cost,
			'children' => array() 	//should be array of child items, with the index
	 								//being the item's ID, and the contents being associative array like
	 								//this one.  If no children, it should be an empty array.  (Careful 
									//not to get into any infinite recursion)
		);
		
		//do not bother with children, items should not be attaching themself to this
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
	
	public static function geoCart_initSteps_addOtherDetails(){
		return false;
	}
	
	
}