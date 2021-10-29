<?php
//order_items/auction_final_fees_tableDisplay.php
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
## ##    7.6.3-73-gda0dfd6
## 
##################################


class auction_final_fees_tableDisplayOrderItem extends geoOrderItem {
	protected $type = "auction_final_fees_tableDisplay";
	const type = 'auction_final_fees_tableDisplay';
	protected $defaultProcessOrder = 50;
	const defaultProcessOrder = 50;
	
	
	/**
	 * Required.
	 * 
	 * @return bool
	 */
	public function displayInAdmin() {
		return true;
	}
	public function adminDetails ()
	{
		$title = 'Possible Final Fees';
		
		return array(
			'type' => ucwords(str_replace('_',' ',self::type)),
			'title' => $title
		);
	}
	
	public static function adminItemDisplay($item_id)
	{
		$html = '';
		
		$item = geoOrderItem::getOrderItem($item_id);
		
		if (!is_object($item) || $item->getType() != self::type) {
			return '';
		}
		
		$listing_id = $item->get('listing');
		$listing = geoListing::getListing($listing_id);
		$l_info = ($listing)? '# '.$listing->id.' : '.geoString::fromDB($listing->title): '# '.$listing_id;
		
		$html .= geoHTML::addOption('Listing',$l_info);
		$final_bid = $item->get('final_bid');
		
		if ($listing) {
			$final_bid = geoString::displayPrice($final_bid, $listing->precurrency, $listing->postcurrency);
		} else {
			$final_bid = geoString::displayPrice($final_bid);
		}
		
		$html .= geoHTML::addOption('Final Bid',$final_bid);
		if ($item->get('conversion_rate') != 1) {
			//also show bid converted
			$converted = geoString::displayPrice($item->get('converted_final_bid'));
			$html .= geoHTML::addOption('Final Bid Converted to Site Currency',$converted);
		}
		$percent = $item->get('final_fee_percentage');
		if ($percent > 0) {
			$cost = ceil($percent * $item->get('converted_final_bid'))/100;
			$cost = geoString::displayPrice($cost);
			$html .= geoHTML::addOption('Percentage Charge', "$cost ({$percent}%)");
		}
		$fixed = $item->get('final_fee_fixed');
		if ($fixed > 0) {
			$fixed = geoString::displayPrice($fixed);
			$html .= geoHTML::addOption('Fixed Charge',$fixed);
		}
		$total = geoString::displayPrice($item->getCost());
		$html .= geoHTML::addOption('Total Final Fee Charge',$total);
		
		//Call children and let them display info about themselves as well
		$children = geoOrderItem::getChildrenTypes(self::type);
		$html .= geoOrderItem::callDisplay('adminItemDisplay',$item_id,'',$children);
		
		return $html;
	}
	
	/**
	 * Optional.
	 * Used: in Admin_site::display_user_data() (in file admin/admin_site_class.php)
	 * 
	 * Can be used to display or gather information for a specific user, when viewing the user's details
	 * inside the admin.  Useful for things like displaying a site balance, for example.
	 *
	 * @param int $user_id
	 * @return string Text to add to page.
	 */
	public static function Admin_site_display_user_data ($user_id){
		$db = DataAccess::getInstance();
		
		//figure out if use has any final fees
		$sql = "SELECT DISTINCT o.`id` FROM ".geoTables::order." as o, ".geoTables::order_item." as oi WHERE oi.order=o.id AND oi.type='auction_final_fees_tableDisplay' AND o.`buyer`=? AND o.status != 'active'";
		
		$rows = $db->GetAll($sql, array(intval($user_id)));
		if ($rows === false) {
			trigger_error('ERROR SQL: Sql: '.$sql.' Error msg: '.$db->ErrorMsg());
			return '';
		}
		if (count($rows) == 0) {
			//nothing found
			return '';
		}
		$messages = array();
		$final_fees = 'Total (Fixed final fee + % of adjusted final bid) [link to auction]<br />';
		$base_url = $db->get_site_setting('classifieds_url').'?a=2&amp;b=';
		foreach ($rows as $row) {
			$order = geoOrder::getOrder($row['id']);
			$items = $order->getItem(self::type);
			$allItems = $order->getItem();
			$moreInCart = (count($allItems) > count($items));
			foreach ($items as $item) {
				$listing = $item->get('listing');
				$total = geoString::displayPrice($item->getCost());
				$fixed = geoString::displayPrice($item->get('final_fee_fixed'));
				$percent = $item->get('final_fee_percentage').'%';
				$final_bid = geoString::displayPrice($item->get('final_bid'));
				$conversion_rate = $item->get('conversion_rate');
				$adjusted_bid = geoString::displayPrice($item->get('converted_final_bid'));
				
				$listing = "<a href='$base_url{$listing}' target='_new'>[ View Auction ]</a>";
				
				$final_fees .= "$total ($fixed + $percent of $adjusted_bid) $listing<br />";
			}
		}
		//TODO: clean up and add some way to process final fees
		$html = geoHTML::addOption('Un-paid Auction Final Fees:',$final_fees);
		return $html;
	}
	
	public function showFinalFeesTable ()
	{
		$db = DataAccess::getInstance();
		
		$sql = "SELECT * FROM ".geoTables::final_fee_table." WHERE `price_plan_id` = ".$this->getPricePlan()." order by low asc";
		$result = $db->Execute($sql);
		
		$ffRows = array();
		$tpl = new geoTemplate('system','user_management');
		for($r = 0; $result && $show = $result->FetchRow(); $r++) {
			$ffRows[$r]['low'] = $show['low'];
			$ffRows[$r]['high'] = ($show['high'] == 100000000) ? geoString::fromDB($this->messages[200122]) : $show["high"];
			$ffRows[$r]['charge'] = $show['charge'];
			$ffRows[$r]['fixed'] = $show['charge_fixed'];
		}
		$tpl->assign('ffRows', $ffRows);
		return $tpl->fetch('information/final_fee_table.tpl');
	}
	
	/**
	 * Required.
	 */
	public static function geoCart_initSteps($allPossible=false){
		if ($allPossible) {
			//just loading all possible steps, don't use this as hook for adding
			//table to the thingy
			return;
		}
		
		//attach/un-attach here
		$cart = geoCart::getInstance();
		
		if (!$cart->item->getPricePlan()) {
			//price plan not known yet
			return;
		}
		
		$parent = $cart->item;
		
		//just to be sure price plan is current
		$cart->setPricePlan($parent->getPricePlan(), $parent->getCategory());
		
		$use = ($cart->price_plan['charge_percentage_at_auction_end'])? true: false;
		
		//now see if there are any items in the order that might be getting final fees:
		$item = $cart->getChildItem(self::type);
		if ($use) {
			$cart->site->session_variables['final_fee'] = 1;
			if (!$item) {
				//create item
				$item = geoOrderItem::getOrderItem(self::type);
				$item->setOrder($cart->order);
				$item->setParent($parent);
				$cart->order->addItem($item);
			}
			$item->setPricePlan($parent->getPricePlan());
		} else {
			$cart->site->session_variables['final_fee'] = 0;
			if ($item) {
				//kill item
				$id = $item->getId();
				
				geoOrderItem::remove($id);
				$cart->order->detachItem($id);
			}
		}
	}
	
	/**
	 * Required.
	 */
	public static function geoCart_initItem_forceOutsideCart() {
		//most need to return false.
		return false;
	}
	public function geoCart_initItem_new ()
	{
		
		return false;
	}
	/**
	 * Required.
	 * 
	 * @return array
	 */
	public static function getParentTypes(){
		return array('auction','reverse_auctions');
	}
	
	/**
	 * Required.
	 * Used: in geoCart::cartDisplay()
	 * 
	 * Used to get display details about item, and any child items as well.  Should return an associative
	 * array, that follows:
	 * array(
	 * 	'css_class' => string,//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
	 * 	'title' => string, //text that is displayed for this item in list of items purchased.
	 * 	'canEdit' => bool, //whether can edit it or not
	 * 	'canDelete' => bool, //whether can remove from cart or not
	 * 	'canPreview' => bool, //whether can preview the item or not
	 * 	'priceDisplay' => string, //price to display, should have precurrency and all that
	 * 	'cost' => double, //amount this adds to the total, what getCost returns
	 * 	'total' => double, //amount this AND all children adds to the total
	 * 	'children' => array(), //should be array of child items, with the index
	 * 							//being the item's ID, and the contents being associative array like
	 * 							//this one.  If no children, it should be an empty array.  (Careful 
	 * 							//not to get into any infinite recursion)
	 * )
	 * @return array An associative array as described above.
	 */
	public function getDisplayDetails ($inCart,$inEmail=false)
	{
		$db = DataAccess::getInstance();
		$msgs = $db->get_text(true, 10202);
		$return = array (
			'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
			'title' => $msgs[500626],//text that is displayed for this item in list of items purchased.
			'canEdit' => false, //show edit button for item?
			'canDelete' => false, //show delete button for item?
			'canPreview' => false, //show preview button for item?
			'canAdminEditPrice' => false, //show edit price button for item, if displaying in admin panel cart?
			'priceDisplay' => $msgs[500628], //Price as it is displayed
			'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
			'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
			'children' => array() 	//should be array of child items, with the index
	 								//being the item's ID, and the contents being associative array like
	 								//this one.  If no children, it should be an empty array.  (Careful 
									//not to get into any infinite recursion)
		);
		
		$sv = $this->getParent()->get('session_variables');
		if($sv['auction_type'] == 3) {
			//special case: this is a reverse auction.
			//If charging final fees for reverse auctions is not enabled, kill the item and return false to not show anything here
			$planItem = geoPlanItem::getPlanItem('auction', $this->getPricePlan());
			if(!$planItem->charge_reverse_final_fees) {
				$id = $this->getId();
				geoOrderItem::remove($id);
				return false;
			}
		}
		
		
		$return['title'] .= '<a href="'.$db->get_site_setting('classifieds_url').'?a=4&amp;b=3#FF'.$this->getPricePlan().'" onclick="window.open(this.href); return false;">'.$msgs[500627].'</a>';
		
		
		//THIS PART IMPORTANT:  Need to keep this part to make the item able to have children
		
		//go through children...
		$order = $this->getOrder();//get the order
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
	
	/**
	 * Required.
	 * Used: By payment gateways to see what types of items are in the cart.
	 *
	 * This is very similar to {@see _templateOrderItem::getDisplayDetails()} except that the
	 * information is used by payment gateways and is specifically for information about what
	 * the "cost" of something is for.
	 *
	 * Should return an associative array, that follows:
	 * array(
	 * 	'type' => string, //The order item type, should always be $this->getType()
	 * 	'extra' => mixed, //used to convey to payment gateways "custom information" that
	 * 						may be needed by the gateway.  Most can set this to null.
	 *  'cost' => double, //amount this adds to the total, what getCost returns
	 * 	'total' => double, //amount this AND all children adds to the total
	 * 	'children' => array(), //optional, should be array of child items, with the index
	 * 							//being the item's ID, and the contents being associative array like
	 * 							//this one.  Careful not to get into any infinite loops...
	 * )
	 *
	 * @return array|bool Either an associative array as documented above, or boolean false if
	 *   this item has no cost (positive or negative, including children).
	 */
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
	
	/**
	 * Required.
	 * Used: in geoCart::initSteps()
	 * 
	 * Determine whether or not the other_details step should be added to the steps of adding this item
	 * to the cart.  This should also check any child items if it does not need other_details itself.
	 *
	 * @return boolean True to add other_details to steps, false otherwise.
	 */
	public static function geoCart_initSteps_addOtherDetails(){
		//Possible enhancement: perhaps show final fee table on other detail page?
		return false;
	}
}
