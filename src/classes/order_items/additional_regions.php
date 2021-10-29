<?php
//order_items/additional_regions.php
/**
 * This file holds the additional_regions order item, which is responsible for
 * keeping track of, and charging for, additional regions in the listing.
 * 
 * @package System
 * @since Version 7.1.0
 */
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
## 
##    16.02.1-12-g223ffae
## 
##################################


/**
 * Additional regions order item
 * 
 * @package System
 * @since Version 7.1.0
 */
class additional_regionsOrderItem extends geoOrderItem
{
	protected $type = "additional_regions";
	const type = 'additional_regions';
	const renewal = 1; //easier way to access what is renew/upgrade
	const upgrade = 2;
	
	protected $defaultProcessOrder = 20;
	const defaultProcessOrder = 20;
	
	
	/**
	 * Required.
	 * Used: in admin, PricePlanItemManage class in various places.
	 * 
	 * Return true to display this order item planItem settings in the admin, 
	 * or false to hide it in the admin.
	 *
	 * @return bool
	 */
	public function displayInAdmin ()
	{
		//show it if multiple listings is enabled
		return self::_isEnabled();
	}
	
	/**
	 * Optional.
	 * Used: In admin, during ajax call to display config settings for a particular
	 * price plan item.
	 * 
	 * If this method exists, a config button will be displayed beside the item, and when
	 * the config button is pressed, whatever this function returns will be displayed
	 * below the item using an ajax call.
	 *
	 * @param geoPlanItem $planItem
	 * @return string
	 */
	public function adminPlanItemConfigDisplay ($planItem)
	{
		$tpl_vars = array();
		
		$tpl_vars['max'] = $planItem->get('max',1);
		$tpl_vars['free'] = $planItem->get('free',0);
		$tpl_vars['cost'] = $planItem->get('cost', 0);
		
		$db = DataAccess::getInstance();
		
		$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
		$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		$tpl->assign($tpl_vars);
		
		return $tpl->fetch('order_items/additional_regions/settings.tpl');
	}
	
	/**
	 * Optional.
	 * Used: In admin, during ajax call to update config settings for a particular
	 * price plan item.
	 * 
	 * This is only used if adminPlanItemConfigDisplay() is used.
	 *
	 * @param geoPlanItem $planItem
	 * @return bool If return true, message "settings saved" will be displayed, if return
	 *  false, message "settings not saved" will be displayed.
	 */
	public function adminPlanItemConfigUpdate ($planItem)
	{
		$settings = $_POST[self::type];
		
		$max = (int)$settings['max'];
		$cost = geoNumber::deformat($settings['cost']);
		$free = (int)$settings['free'];
		
		if ($max <= 0) {
			//failsafe: make sure they don't try to do something funny...
			$max = 0;
		}
		if ($free <= 0) {
			//failsafe: don't let them do something weird
			$free = 0;
		}
		
		if ($free > $max) {
			//they are trying to allow more free than max allowed...
			geoAdmin::m('You cannot have more free additional regions ('.$free.') than the max number ('.$max.')!', geoAdmin::ERROR);
			return false;
		}
		
		$planItem->max = $max;
		$planItem->free = $free;
		$planItem->cost = $cost;
		
		return true;
	}
	
	/**
	 * Optional.
	 * Used: In admin, when displaying an order item's details
	 * 
	 * Return HTML for displaying or editing any information about this item, to 
	 * be displayed in the admin.  Should also call any children of this item.
	 * 
	 * The other function that should work with this one, is adminItemUpdate.
	 *
	 * @param int $item_id
	 * @return string
	 */
	public static function adminItemDisplay ($item_id)
	{
		if (!$item_id){
			return '';
		}
		$parent = geoOrderItem::getOrderItem($item_id);
		if (!is_object($parent)) {
			return '';
		}
		$item = geoOrderItem::getOrderItemFromParent($parent,self::type);
		if (!is_object($item)) {
			//no videos attached
			return '';
		}
		$tpl_vars = array();
		$session_vars = $parent->get('session_variables');
		if (!isset($session_vars['additional_regions'])) {
			//no additional regions
			return '';
		}
		$additional_regions = self::_getEndRegions($session_vars);
		
		$region = geoRegion::getInstance();
		
		$tpl_vars['additional_regions'] = array();
		foreach ($additional_regions as $region_id) {
			$info = $region->getParents($region_id,true);
			$tpl_vars['additional_regions'][] = $info;
		}
		$tpl_vars['current_color'] = geoHTML::adminGetRowColor();
		
		$tpl = new geoTemplate(geoTemplate::ADMIN);
		$tpl->assign($tpl_vars);
		$html .= $tpl->fetch('order_items/additional_regions/item_details.tpl');
		
		//Call children and let them display info about themselves as well
		$children = geoOrderItem::getChildrenTypes(self::type);
		$html .= geoOrderItem::callDisplay('adminItemDisplay',$item_id,'',$children);
		
		return $html;
	}
	
	/**
	 * Optional.
	 * Used: mainly in geoCart::initItem() but can be called elsewhere.
	 * 
	 * Used when no one is logged in, to determine if anonymous sessions
	 * are allowed to use this item type.
	 * 
	 * If this function is not defined, it will be assumed that this item
	 * is NOT allowed with anonymous sessions, and will not allow this item
	 * to be used without first logging in.
	 * 
	 * @return bool Need to return true if item allowed to be used in an
	 *  anonymous environment, false otherwise.
	 */
	public static function anonymousAllowed ()
	{
		return true;
	}
	
	/**
	 * Required.
	 * Used: in geoCart::initSteps() (and possibly other locations)
	 * 
	 * If this order item has any of it's own steps it wants to display or process as
	 * part of the sell process, it needs to add them to the cart here, by getting an
	 * instance of the cart, and $cart->addStep('item_name:step_name');.  
	 * 
	 * It also needs to call any children order items to do the same, as only parents
	 * are called by the Cart system.
	 * 
	 * Format of steps:
	 * <ORDER_ITEM_NAME>:<STEP_NAME>
	 * 
	 * Example:
	 * additional_regions:details
	 * 
	 * When the process gets to the step additional_regions:details, if $_REQUEST['process'] is
	 * defined, then it will make the following static method calls:
	 * additional_regions::<STEP_NAME>CheckVars(); - if return true, then:
	 * additional_regions::<STEP_NAME>Process(); - if return true, then it will continue on to next step
	 * 
	 * If $_REQUEST['process'] is NOT defined, or <STEP_NAME>CheckVars() or <STEP_NAME>Process()
	 *  either return false, then it will call:
	 * additional_regions::<STEP_NAME>Display();
	 * 
	 * That display function is responsible for displaying the page, then including app_bottom.php, 
	 * then exiting.  If it does not exit, the system will display a site error.
	 * 
	 * If the below optional method exists, it will also call that method to determine the "label"
	 * for the step, to be displayed in templates that show the progress.  The method below
	 * should return a string to display as the name of the step, or an empty string if you
	 * wish to hide the step from the user:
	 * additional_regions::<STEP_NAME>Label();
	 * 
	 * (Of course, above you would replace <STEP_NAME> with "details" if your step was "additional_regions:details")
	 */
	public static function geoCart_initSteps ($allPossible=false)
	{
		//no additional steps needed, this uses the classified / auction / edit step
	}
	
	/**
	 * Checks the vars for the listing details page, and throws cart error if there
	 * are any problems found.
	 */
	public static function detailsCheckVars_getMoreDetailsLocation ()
	{
		$cart = geoCart::getInstance();
		if (!self::_isEnabled()) {
			//not enabled...  Make sure it does not have any session vars set for it
			unset($cart->site->session_variables['additional_regions']);
			return;
		}
		self::_updateSessionVars();
		
		//now check for duplicates
		self::_checkForDuplicates();
	}
	
	/**
	 * Processes the data for additional regions on the listing details page
	 */
	public static function detailsProcess_getMoreDetailsLocation ()
	{
		if (!self::_isEnabled()) {
			//nothing to do here, it's not turned on...
			return;
		}
		$cart = geoCart::getInstance();
		
		$category = $cart->item->getCategory();
		$price_plan = $cart->item->getPricePlan();
		$planItem = geoPlanItem::getPlanItem(self::type,$price_plan,$category);
		
		//figure out what to set the amount purchased to...
		$count = count($cart->site->session_variables['additional_regions']);
		$purchased = $count;
		
		if ($cart->item->getType()=='listing_edit') {
			//account for if there is previous amount already purchased
			$already_purchased = self::_getAlreadyPurchased();
			
			$purchased = max($purchased, $already_purchased);
		}
		//if there are free...  just set the number to the number that is free.
		$purchased = max($purchased, $planItem->get('free',0));
		$cart->site->session_variables['additional_regions_purchased'] = (int)$purchased;
		
		$order_item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);
		
		$removeItem = ($count <= 0 && !self::_getAlreadyPurchased());
		
		if ($removeItem && $order_item) {
			//there is an item when there should not be.
			$id = $order_item->getId();
			geoOrderItem::remove($id);
			$cart->order->detachItem($id);
			$order_item = null;
		} else if (!$removeItem && !$order_item) {
			//there is not an item yet but we need one
			$order_item = self::addNewItem();
		}
		
		if (!$order_item) {
			//nothing else to do
			return;
		}

		$cost = self::_getCost();
		
		$freeCount = min($count, $planItem->get('free',0));
		$paidCount = $count - min($count, $freeCount);
		
		$order_item->setCost($cost);
		$order_item->setCreated($cart->order->getCreated());
		
		$order_item->set('freeCount', $freeCount);
		$order_item->set('paidCount', $paidCount);
		$order_item->set('cost_per', $planItem->get('cost'));
		
		//set id of listing, if known
		if (isset($cart->site->classified_id) && $cart->site->classified_id > 0){
			$order_item->set('listing_id',$cart->site->classified_id);
		}
		$order_item->setPricePlan($price_plan, $cart->user_data['id']);
		$order_item->setCategory($category);
		$order_item->save();
	}
	
	/**
	 * Used to add additional rows to the listing details page, in the "location"
	 * section of the details.
	 * 
	 * @return array
	 */
	public static function detailsDisplay_getMoreDetailsLocation ()
	{
		$db = DataAccess::getInstance();
		if (!self::_isEnabled()) {
			//not enabled!
			return;
		}
		$cart = geoCart::getInstance();
		
		$category = $cart->item->getCategory();
		$price_plan = $cart->item->getPricePlan();
		$planItem = geoPlanItem::getPlanItem(self::type,$price_plan,$category);
		
		$max = (int)$planItem->get('max',1);
		if ($max<=0) {
			//none allowed
			return;
		}
		$cost = (geoMaster::is('site_fees'))? $planItem->cost : 0;
		
		$free = max($planItem->get('free',0), self::_getAlreadyPurchased());
		
		$return = array (
			'section_sub_head' => $cart->site->messages[502050],
			'section_desc' => $cart->site->messages[502051],
			'max' => $max,
			'free' => (int)$free,
			'cost' => $cost,
			'in_admin' => (defined('IN_ADMIN') || $_POST['is_a'] == 1) ? 1 : 0,
			);
		
		//init the new geographic selector
		$preselect_regions = array();
		$cartErrors = $cart->getErrorMsgs();
		$errors = array();
		if (isset($cart->site->session_variables['additional_regions'])) {
			$maxLocationDepth = 0;
			$fields = geoFields::getInstance($cart->user_data['group_id'], $cart->item->getCategory());
			for($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
				$field = 'region_level_'.$r;
				if ($fields->$field && $fields->$field->is_enabled) {
					$maxLocationDepth = $r;
					break;
				}
			}
			foreach ($cart->site->session_variables['additional_regions'] as $index => $location) {
				$preselect_regions[$index] = geoRegion::regionSelector('additional_regions['.$index.']', $location, $maxLocationDepth);
				if ($cartErrors && isset($cartErrors['additional_regions_'.$index])) {
					//error for this one!
					$errors[$index] = $cartErrors['additional_regions_'.$index];
				}
			}
		}
		
		$return['tpl'] = array (
			'g_type' => 'system',
			'g_resource' => 'order_items',
			'file' => 'additional_regions/details_collection.tpl',
			);
		$return['preselect_regions'] = $preselect_regions;
		$return['errors'] = $errors;
		
		return $return;
	}
	
	/**
	 * Required.
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
	 * @return bool True to force creating "parellel" cart just 
	 *  for this item, false otherwise.
	 */
	public static function geoCart_initItem_forceOutsideCart ()
	{
		//most need to return false.
		return false;
	}
	
	/**
	 * Required.
	 * Used: In geoOrderItem class when loading the order item types, to get the
	 * defailt parent types.
	 * 
	 * This should return an array of the different order items that this
	 * order item is a child of.  If this is a main order item type, it 
	 * should return an empty array.
	 * 
	 * Note that more parent types can be added later using geoOrderItem::addParentTypeFor()
	 * but only if this method (getParentTypes) returns an array with at least
	 * one parent type. 
	 * 
	 * @return array
	 */
	public static function getParentTypes ()
	{
		return array('classified','classified_recurring','auction','renew_upgrade', 'listing_edit');
	}
	
	/**
	 * Required.
	 * Used: Throughout the software, wherever order details are displayed.
	 * 
	 * Used to get display details about item, and any child items as well, both in the main
	 * cart view, and other places where the order details are displayed, including within
	 * the admin.  Should return an associative array, that follows:
	 * array(
	 * 	'css_class' => string, //leave empty string for default class, only applies in cart view
	 * 	'title' => string,
	 * 	'canEdit' => bool, //whether can edit it or not, only applies in cart view
	 * 	'canDelete' => bool, //whether can remove from cart or not, only applies in cart view
	 * 	'canPreview' => bool, //whether can preview the item or not, only applies in cart view
	 * 	'priceDisplay' => string, //price to display
	 * 	'cost' => double, //amount this adds to the total, what getCost returns but positive
	 * 	'total' => double, //amount this AND all children adds to the total
	 * 	'children' => array(), //optional, should be array of child items, with the index
	 * 							//being the item's ID, and the contents being associative array like
	 * 							//this one.  Careful not to get into any infinite loops...
	 * )
	 * 
	 * @param bool $inCart True if this is being called from inside the cart, false otherwise. Note: do NOT
	 *  try to use the geoCart object if $inCart is false.
	 * @param bool $inEmail True if this is being used to build the text of an email notification sent by the system, false otherwise. Param added in 6.0.6
	 * @return array|bool Either an associative array as documented above, or boolean false to hide this
	 *  item from view.
	 */
	public function getDisplayDetails ($inCart, $inEmail=false)
	{
		$price = $this->getCost();
		//Figure out how many photos, how many are being charged, etc.
		$renew_upgrade = (($this->getParent())? $this->getParent()->get('renew_upgrade') : false);
		
		//can delete if not renewing/upgrading and not editing listing
		$can_delete = !($renew_upgrade > 0 || ($this->getParent() && $this->getParent()->getType() == 'listing_edit'));
		
		$db = DataAccess::getInstance();
		$msgs = $db->get_text(true, 10202);
		
		$return = array (
			'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
			'title' => $msgs[502057],//text that is displayed for this item in list of items purchased.
			'canEdit' => false, //show edit button for item, if displaying in cart?
			'canDelete' => $can_delete, //show delete button for item, if displaying in cart?
			'canPreview' => false, //show preview button for item, if displaying in cart?
			'canAdminEditPrice' => true, //show edit price button for item, if displaying in admin panel cart?
			'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //Price as it is displayed
			'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
			'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
			'children' => array() 	//should be array of child items, with the index
	 								//being the item's ID, and the contents being associative array like
	 								//this one.  If no children, it should be an empty array.  (Careful 
									//not to get into any infinite recursion)
		);
		
		$total_paid = $this->get('paidCount',0);
		//subtract pre-existing images from total number of free images displayed, to make it less confusing
		$free = intval($this->get('freeCount'));
		
		if ($total_paid < 0){
			$total_paid = 0;
		}
		
		$free = ($free > 0) ? $free.$msgs[502058]: '';
		
		$planItem = geoPlanItem::getPlanItem(self::type, $this->getPricePlan(), $this->getCategory());
		$display_per_region_cost = geoString::displayPrice($planItem->cost);
		
		if (geoMaster::is('site_fees')) {
			$title = " ($free {$total_paid} X $display_per_region_cost )";
			$return['title'] .= $title;
		}
		
		//THIS PART IMPORTANT:  Need to keep this part to make the item is able to have children.
		//You don't want your item to be sterile do you?
		
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
	 * Note that for backwards compatibility with older order items, this is implemented
	 * in the parent geoOrderItem class, so if you leave it off it will "work".
	 * It is still highly recommended to implement anyways in each order item, 
	 * simply because it's role will be much more important when the ability to
	 * use the cart between users is implemented down the road.
	 * 
	 * This is very similar to {@see additional_regionsOrderItem::getDisplayDetails()} except that the
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
	 * @since Version 6.0.0
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
	 * Optional.
	 * Used: in geoCart::deleteProcess()
	 * 
	 * The back-end already removes the item, all all children from the cart.  Use this function to do
	 * any additional things needed, such as delete uploaded images, or if you expect that any children
	 * may need to be called, as they will not be auto called from the system.  Can assume
	 * $cart->item is the item that is being deleted, which will be the same type as this is.
	 *
	 */
	public static function geoCart_deleteProcess ()
	{
		$cart = geoCart::getInstance();
		
		//Do this FIRST: Go through any children, and call geoCart_deleteProcess for them...
		$original_id = $cart->item->getId();//need to keep track of what the ID of the item originally being deleted is.
		$items = $cart->order->getItem();
		foreach ($items as $k => $item){
			if (is_object($item) && $item->getId() != $cart->item->getId() && is_object($item->getParent()) && $item->getParent()->getId() == $cart->item->getId()){
				//$item is a child of this item...
				//Set the cart's main item to be $item, so that the deleteProcess gets
				//what it is expecting...
				$cart->initItem($item->getId(),false);
				//now call deleteProcess
				geoOrderItem::callUpdate('geoCart_deleteProcess',null,$item->getType());
			}
		}
		if ($cart->item->getId() != $original_id){
			//change the item back to what it was originally, if it was changed.
			$cart->initItem($original_id);
		}
		
		
		//DO Any custom stuff needed here.
		$parent = $cart->item->getParent();
		if (is_object($parent)){
			//note that this would not be called from listing edit or renewal
			$session_vars = $parent->get('session_variables');
			$session_vars['additional_regions_purchased'] = 0;
			unset($session_vars['additional_regions']);
			$parent->set('session_variables',$session_vars);
			$parent->save();
			$cart->site->session_variables = $session_vars;
		}
	}
	
	
	/**
	 * Required.
	 * Used: in geoCart::initSteps()
	 * 
	 * Determine whether or not the other_details step should be added to the steps of adding this item
	 * to the cart.  This should also check any child items if it does not need other_details itself.
	 *
	 * @return bool True to add other_details to steps, false otherwise.
	 */
	public static function geoCart_initSteps_addOtherDetails ()
	{
		return false;
	}
	
	public static function detailsEdit_getMoreDetails_vars ()
	{
		if (!self::_isEnabled()) {
			//don't add...
			return;
		}
		return array ('additional_regions_purchased');
	}
	
	/**
	 * The checkVars step for other details page, only used for listing renewal
	 * or upgrade.
	 */
	public static function geoCart_other_detailsCheckVars ()
	{
		$cart = geoCart::getInstance();
		if (!(isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade > 0)){
			//this is not a renewal or upgrade, and we only display on other details for renew/upgrade
			return;
		}
		if (!self::_isEnabled() || !geoMaster::is('site_fees')) {
			return;
		}
		
		//get plan item
		$category = $cart->item->getCategory();
		$price_plan = $cart->item->getPricePlan();
		$planItem = geoPlanItem::getPlanItem(self::type,$price_plan,$category);
		
		$max = $planItem->get('max',1);
		if ($planItem->get('cost',0) == 0 || ($cart->item->renew_upgrade == self::upgrade && $cart->site->parent_session_variables['additional_regions_purchased'] >= $max)) {
			//either we do not charge for additional regions, or this is an upgrade and the user already has the max number of regions they can.
			trigger_error('DEBUG CART: Here in add regions.');
			return ;
		}
		if ($planItem->free >= $max) {
			//number of free is same as number of max allowed
			return;
		}
		trigger_error('DEBUG CART: Here in additional regions.');
		$renew_upgrade = $cart->item->renew_upgrade;
		$numRegions = intval($_POST['c']['new_additional_regions']);
		
		if ($numRegions > $max){
			//do not allow more than the max allowed ergions, to prevent invalid user input
			$numRegions = intval($max);
		}
		
		$free = (int)$planItem->free;
		
		if ($renew_upgrade == self::upgrade){
			//only count number added beyond whats already been purchased
			//so, add the number that is already added that has been purchased to the "free" count
			$numOldRegions = (int)$cart->site->parent_session_variables['additional_regions_purchased'];
			if($free >= $numOldRegions) {
				$free = $free;
			} else {
				$free = $numOldRegions;
			}
		} else {
			//renewal, figure out minimum region count
			$force_min = count($cart->site->parent_session_variables['additional_regions']);
			
			if ($numRegions < $force_min) {
				$numRegions = $force_min;
			}
		}
		$purchased = $numRegions - min($numRegions, $free);
		$amountPaid = $purchased * $planItem->cost;
		
		$order_item = geoOrderItem::getOrderItemFromParent($cart->item, self::type);

		if (!$purchased) {
			//no new images purchased
			
			//find out if this is a copy
			$parent = $cart->item;
			if ($parent) {
				$isCopy = $parent->get('listing_copy_id');
			}
			
			//mark item for removal unless this is a copy with videos
			$removeItem = ($isCopy && $numRegions) ? false : true;
		} else {
			//new regions have been purchased -- don't remove the item
			$removeItem = false;
		}
		
		if ($removeItem){
			if ($order_item){
				$id = $order_item->getId();
				geoOrderItem::remove($id);
				$cart->order->detachItem($id);
			}
		} else {
			if (!$order_item){
				$order_item = self::addNewItem();
			} else {
				trigger_error('DEBUG CART: videos already attached: <pre>'.print_r($order_item,1).'</pre>');
				$cart->order->addItem($order_item);
			}
			$order_item->setCreated($cart->order->getCreated());
			$order_item->setCost($amountPaid);
			
			//set details specific to videos
			$order_item->set('freeCount', $free);
			$order_item->set('paidCount', $purchased);
			$order_item->set('cost_per', $planItem->get('cost'));
			
			//set id of listing, if known
			if (isset($cart->site->classified_id) && $cart->site->classified_id > 0){
				$order_item->set('listing_id',$cart->site->classified_id);
			}
			
			$order_item->set('renew_upgrade',$renew_upgrade);
			if ($renew_upgrade == self::renewal && $force_min > 0) {
				$order_item->set('force_no_remove',1);
			}
			$order_item->save();
			
			$session_variables = $cart->item->get('session_variables');
			$session_variables['additional_regions_purchased'] = $numRegions;
			$cart->item->set('session_variables',$session_variables);
						
			$cart->item->save();
		}
	}
	
	/**
	 * Processes the values for other details page.  Only left here so that it is
	 * documented that all the work is done in checkVars() rather than here.
	 */
	public static function geoCart_other_detailsProcess ()
	{
		//everything done in check vars...
	}
	
	/**
	 * Gets the contents to display in the other details page, used when renewing
	 * or upgrading a listing.
	 * 
	 * @return array|null
	 */
	public static function geoCart_other_detailsDisplay ()
	{
		$cart = geoCart::getInstance();
		trigger_error('DEBUG CART: Here in regions.');
		if (!(isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade > 0)){
			//this is not a renewal or upgrade, and we only display on other details for renew/upgrade
			return;
		}
		if (!self::_isEnabled() || !geoMaster::is('site_fees')) {
			return;
		}
		
		//get plan item
		$category = $cart->item->getCategory();
		$price_plan = $cart->item->getPricePlan();
		$planItem = geoPlanItem::getPlanItem(self::type,$price_plan,$category);
		$max = $planItem->get('max',1);
		if ($planItem->get('cost',0) == 0 || ($cart->item->renew_upgrade == self::upgrade && $cart->site->parent_session_variables['additional_regions_purchased'] >= $max)) {
			//either we do not charge for videos, or this is an upgrade and the user already has the max number of images they can.
			trigger_error('DEBUG CART: Here in regions.');
			return;
		}
		if ($planItem->free >= $max) {
			//number of free videos is same as number of max videos allowed
			return;
		}
		
		$renew_upgrade = $cart->item->renew_upgrade; //easier way to access var
		
		//check current videos attached to this listing versus what is already purchased
		//the $this->classified_data->IMAGE variable contains the count of images paid for
		//the current listing.  The renewal costs will be based off what is actually
		//attached to the listing currently.  Do not need to do this for upgrade.
		
		$tpl_vars = $cart->getCommonTemplateVars();
		$tpl_vars['current'] = $tpl_vars['maxToBuy'] = 0;
		//number of free pics
		$tpl_vars['free'] = $planItem->free;
		
		if ($renew_upgrade == self::renewal){
			//count the actual number of regions for this listing, not the number of previously purchased regions
			$count = count($cart->site->session_variables['additional_regions']);
			$tpl_vars['current'] = $count;
			$tpl_vars['maxToBuy'] = $maxToBuy = $max - $tpl_vars['free'];
			$tpl_vars['start'] = $start = $tpl_vars['free'];
			trigger_error("DEBUG CART: current: {$tpl_vars['current']} start: $start");
		} else {
			//upgrade, the current is the number already recorded.
			$count = (int)$cart->site->parent_session_variables['additional_regions_purchased'];
			
			//force count to be as big or bigger than number of free.
			if ($count < $tpl_vars['free']) $count = $tpl_vars['free'];
			
			$tpl_vars['current'] = $current = $count;
			$maxToBuy = $max - $current;
			$tpl_vars['maxToBuy'] = $maxToBuy = (($maxToBuy > 0)? $maxToBuy : 0);
			$tpl_vars['start'] = $start = $current;
		}
		
		$cart->site->page_id = 56;
		$cart->site->get_text();
		
		$tpl = new geoTemplate('system','order_items');
		$tpl->assign($tpl_vars);
		$region_dropdown = array();
				
		for ($i = intval($start); $i<=($start + $maxToBuy); $i++) {
			//build array to use in smarty template for image drop down
			if (($renew_upgrade == self::renewal && $i >= $tpl_vars['current']) || $renew_upgrade == self::upgrade) {
				$price = 0;
				if (($renew_upgrade == self::upgrade && ($tpl_vars['current']+$i) > $tpl_vars['free']) || ($renew_upgrade == self::renewal && $i > $tpl_vars['free'])) {
					$mult = ($i-$start);
					$price = ($planItem->cost * $mult);
				}
				$region_dropdown[$i] = geoString::displayPrice($price, false, false, 'cart');
			}
		}
		$tpl->assign('region_dropdown',$region_dropdown);
		
		$tpl->assign('help_link',$cart->site->display_help_link(502060));
		$tpl->assign('renew_upgrade',$renew_upgrade); //not used in default smarty template, but handy to know for custimization to template
		return array ('entire_box' => $tpl->fetch('additional_regions/other_details.item_box.tpl'));
	}
	
	/**
	 * Optional.
	 * Used: In the admin when admin activates order or item, or on client side when payment is
	 * made and settings are such that it does not need admin approval to activate the item.
	 * 
	 * If this is not implemented here, the parent class will do common stuff for you, like call
	 * child items and actually set the status
	 * 
	 * This is responsible for actually changing the status of the item, as well as anything such 
	 * as activating/deactivating a listing depending on what the previous status is, and what it is
	 * being changed to.  Use template function as a guide, and add customization where comments
	 * specify to.  Remember to call children where appropriate if you decide not to call the parent
	 * to do it for you.
	 * 
	 * It can be assumed that if this function is called, all the checks as to whether the item should be
	 * pending or not have already been done, however there may be other custom checks you wish to do.
	 *
	 * @param string $newStatus a string of what the new status for the item should be.  The statuses
	 *  built into the system are active, pending, and pending_alter.
	 * @param bool $sendEmailNotifications If set to false, you should not send any e-mail notifications
	 *  like might be normally done.  (if it's false, it will be because this is called 
	 *  from admin and admin said don't send e-mails)
	 */
	public function processStatusChange ($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
	{
		if ($newStatus == $this->getStatus()){
			//the status hasn't actually changed, so nothing to do
			return;
		}
		$activate = ($newStatus == 'active')? true: false;
		
		$already_active = ($this->getStatus() == 'active')? true: false; 
		
		//allow parent to do common things, like set the status and
		//call children items
		parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
		
		$parent = $this->getParent();
		if (!$parent) {
			//Doh!  this should not happen.
			trigger_error('ERROR CART: Count not get parent, this should not happen!');
			return;
		}
		if ($parent->getType() == 'listing_edit') {
			//do things special for editing
				
			$force = ($activate)? true : false;
			$session_variables = listing_editOrderItem::getSessionVars($parent, $force);
			
			geoRegion::setListingEndRegions($parent->get('listing_id'), self::_getEndRegions($session_variables, true));
		} else {
			//either totally add them or totally remove them since we are activating
			//or deactivating a listing
			if ($activate) {
				//do activate actions here, such as setting listing to live
				$session_variables = $parent->get('session_variables');
				
				if (!$session_variables['additional_regions'] && $this->get('renew_upgrade') > 0 && !$this->get('listing_copy_id')) {
					//there are no regions found, it is a renewal or upgrade and it is not a copy of
					//another listing.  So don't apply regions or all of the regions will be
					//removed.
					trigger_error('DEBUG ADDITIONAL_REGION CART: No additional regions and this is renew/upgrade, nothing to do.');
					return;
				}
				geoRegion::setListingEndRegions($parent->get('listing_id'), self::_getEndRegions($session_variables, true));
			} else if (!$activate && $already_active) {
				//do de-activate actions here, such as setting listing to not be live any more.
				//This is what would happen if an admin changes their mind
				//and later decides to change an item from being active to being pending.
				
				//Do NOT remove the additional regions.  Let that happen at the time
				//the listing is removed via geoListing::remove()
				return;
				
				//But if it turns out we DO need to remove them for some reason,
				//this is how the additional regions might be removed...
				if ($this->get('renew_upgrade') > 0 && !$this->get('listing_copy_id')) {
					//either it is renewal / upgrade, or it is copy of listing, don't
					//remove regions
					return;
				}
				//remove the regions set for the listing if there is one
				geoRegion::setListingEndRegions($parent->get('listing_id'), array(), 1);
			}
		}
	}
	
	/**
	 * Updates the session variables on the $cart->site->session_variables, and
	 * takes into account max number of additional regions allowed and things like
	 * that.
	 */
	private static function _updateSessionVars ()
	{
		$cart = geoCart::getInstance();
		
		$additional_regions_raw = $_POST['additional_regions'];
		$use = $_POST['additional_use'];
		
		//make sure we don't go over max allowed
		
		$category = $cart->item->getCategory();
		$price_plan = $cart->item->getPricePlan();
		$planItem = geoPlanItem::getPlanItem(self::type,$price_plan,$category);
		$max = (int)$planItem->get('max',1);
		
		$additional_regions = array();
		
		foreach ($additional_regions_raw as $index => $region) {
			if ($max <= count($additional_regions)) {
				//We are at the max number of additional regions, don't add any more!
				break;
			}
			if (!(int)$use[$index]) {
				//don't use this one!  And since we force them to use them in
				//order, we know none of the others are used either
				
				break;
			}
			$use_level = false;
			foreach ($region as $level => $val) {
				if (strlen($val) && $val > 0) {
					$use_level = true;
					break;
				}
			}
			if (!$use_level) {
				//nothing selected on this one...  skip it
				continue;
			}
			
			$additional_regions[] = $region;
		}
		
		//save teh session vars for additional regions
		$cart->site->session_variables['additional_regions'] = $additional_regions;
	}
	
	/**
	 * Checks the additional regions to see if there are any duplicate regions,
	 * and if there are, it adds the appropriate errors for them.
	 */
	private static function _checkForDuplicates ()
	{
		$cart = geoCart::getInstance();
		
		//This is going to be the array of ALL the different regions, including parents
		$allSelected = $lastRegions = array ();
		
		//add the main regions to the array...
		foreach ($cart->site->session_variables['location'] as $region) {
			if (strlen($region) && $region>0) {
				$region = (int)$region;
				if (isset($allSelected[$region])) {
					$allSelected[$region]++;
				} else {
					$allSelected[$region] = 1;
				}
				$lastRegions[0] = $region;
			}
		}
		
		//now loop through all additional regions and add each to the flat array
		foreach ($cart->site->session_variables['additional_regions'] as $region_order => $regions) {
			foreach ($regions as $region) {
				$region = (int)$region;
				if ($region <= 0) {
					continue;
				}
				$lastRegions[$region_order] = $region;
				if (isset($allSelected[$region])) {
					$allSelected[$region]++;
				} else {
					$allSelected[$region] = 1;
				}
			}
		}
		
		//now go through all the last regions, make sure there is only 1 count for
		//that region
		foreach ($lastRegions as $region_order => $region) {
			if ($allSelected[$region] > 1) {
				//this region matches!
				self::_addDuplicateError($region_order, $region);
			}
		}
	}
	
	/**
	 * Convienience method to add the standard duplicate error, indicating that
	 * a specific region was already set for this listing.
	 * 
	 * @param int $index
	 * @param int $regionId
	 */
	private static function _addDuplicateError ($index, $regionId)
	{
		$cart = geoCart::getInstance();
		if (!isset($cart->site->messages[502056])) {
			$cart->site->page_id = 9;
			$cart->site->get_text();
		}
		$errMsg = geoRegion::getNameForRegion($regionId).' '.$cart->site->messages[502056];
		$cart->addError()
			->addErrorMsg('additional_regions_'.$index, $errMsg);
	}
	
	/**
	 * Get the cost for the additional regions
	 * @return int
	 */
	private static function _getCost ()
	{
		$cart = geoCart::getInstance();
		
		$category = $cart->item->getCategory();
		$price_plan = $cart->item->getPricePlan();
		$planItem = geoPlanItem::getPlanItem(self::type,$price_plan,$category);
		
		$max = (int)$planItem->get('max',1);
		if ($max<=0) {
			//none allowed
			return;
		}
		$cost = (geoMaster::is('site_fees'))? $planItem->cost : 0;
		$free = (int)$planItem->free;
		if ($cost <= 0) {
			//shortcut, less work, no cost
			return 0;
		}
		
		$count = count($cart->site->session_variables['additional_regions']);
		
		if ($cart->item->getType()=='listing_edit') {
			//special case! Take purchased regions into account!
			//if the number of purchased is more than number of free, just do it
			//like the number of free is the number of purchased already
			$already_purchased = self::_getAlreadyPurchased();
			$free = max($already_purchased, $free);
		}
		
		$pay_count = $count - min($count, $free);
		
		return $pay_count * $cost;
	}
	
	/**
	 * Get the number of additional regions are already purchased.
	 * 
	 * @return int
	 */
	private static function _getAlreadyPurchased ()
	{
		$cart = geoCart::getInstance();
		$count = $cart->item->get('additional_regions_purchased',false);
		if ($count===false) {
			$count = 0;
			if ($cart->item->getType()=='listing_edit') {
				$listing_id = (int)$cart->item->get('listing_id',false);
					
				//get number of video slots available
				$listing = ($listing_id)? geoListing::getListing($listing_id) : false;
				if ($listing) {
					//get the number of purchased directly from the listing, as it
					//will be the value "before" this.
					$count = (int)$listing->additional_regions_purchased;
				}
			}
			//so we only have to get it once
			$cart->item->set('additional_regions_purchased', $count);
		}
		return $count;
	}
	
	/**
	 * Adds a new item to the cart for additional regions, and returns the new
	 * item.  This is used internally, we may want to make something like this
	 * available at the geoOrderItem level if it becomes a common method for sub
	 * order items.
	 * 
	 * @return additional_regionsOrderItem
	 */
	public static function addNewItem ()
	{
		$cart = geoCart::getInstance();
		$order_item = new additional_regionsOrderItem;
		$order_item->setParent($cart->item);//this is a child of the parent
		$order_item->setOrder($cart->order);
	
		$order_item->save();//make sure it's serialized
		$cart->order->addItem($order_item);
		return $order_item;
	}
	
	/**
	 * Whether or not additional regions are enabled at the site level.
	 *
	 * @return boolean
	 */
	private static function _isEnabled ()
	{
		$db = DataAccess::getInstance();
		return (bool)$db->get_site_setting('additional_regions_per_listing');
	}
	
	/**
	 * Since we do this operation in a few different places, this is an easy way
	 * to get the "end regions" for additional regions, based on the values as
	 * saved in the session_vars.
	 * 
	 * See {@see geoRegion::getEndREgions()} for info on format of what is returned.
	 * 
	 * @param array $session_vars
	 * @param bool $include_primary If true, will include the "primary" regions
	 *   as the first index in the array
	 * @return array
	 */
	private static function _getEndRegions ($session_vars, $include_primary = false)
	{
		$regions = $session_vars['additional_regions'];
		if ($include_primary && isset($session_vars['location'])) {
			array_unshift($regions, $session_vars['location']);
		}
		
		return geoRegion::getEndRegions($regions);
	}
}
