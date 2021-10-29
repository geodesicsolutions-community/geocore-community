<?php
//addons/attention_getters/order_items/addon_attention_getter.php
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
## 
##    16.09.0-96-gf3bd8a1
## 
##################################

# Attention Getters Addon

require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';

class addon_attention_getterOrderItem extends geoOrderItem {
	var $defaultProcessOrder = 45;
	protected $type = 'addon_attention_getter';
	const type = 'addon_attention_getter';
	/**
	 * Update Functions : called from main software using geoOrderItem::callUpdate(), and that
	 * function calls the one here if the function exists.  To avoid name conflicts, if you need
	 * custom functions specific for this orderItem, prepend the var or function name with an
	 * underscore.
	 */
	public function displayInAdmin()
	{
		return false;
	}

	/**
	 * used in admin to show which upgrades are attached to a Listing Renewal item
	 *
	 * @return String "user-friendly" name of this item
	 */
	public function friendlyName() {
		return 'Attention Getter';
	}
	
	/**
	 * Optional.
	 * Used: in geoCart::initItem()
	 * 
	 * Used when initiailizing an item, when the item already exists.
	 */
	public function geoCart_initItem_restore (){
		trigger_error('DEBUG CART: Top of restore item for attention getters.');
		$cart = geoCart::getInstance();
		$parent = $this->getParent();
		
		//TODO: make the attention getter selection be stored in the attention getter item...
		$cart->site->session_variables = $parent->get('session_variables'); //get session vars attached to it.
		//make sure if price plan id is set, to use that price plan when getting prices!
		if (isset($cart->site->session_variables['price_plan_id'])){
			$cart->setPricePlan($cart->site->session_variables['price_plan_id']);
		}
		return true;
	}
	
	public static function geoCart_other_detailsCheckVars(){
		$cart = geoCart::getInstance();
		//Can remove check once this addon is meant for working ONLY in 4.1
		$parents = (is_callable(array('geoOrderItem','getParentTypesFor')))? geoOrderItem::getParentTypesFor(self::type) : self::getParentTypes();
		if (!($cart->main_type == self::type || in_array ($cart->main_type,$parents)) || !$cart->db->get_site_setting('use_attention_getters'))
		{
			//do not show thingy for attention_getter
			return ;
		}
		if (isset($_POST['c'])){
			if ($cart->item->getType() == self::type){
				$item = $cart->item->getParent();
			} else {
				$item = $cart->item;
			}
			$cart->setPricePlan($item->getPricePlan(),$item->getCategory());
			if (geoPC::is_ent() && !$cart->price_plan['use_attention_getters']){
				//turned off in price plan
				return ;
			}
			$use_attention_getter = ((isset($_POST['c']['attention_getter']) && $_POST['c']['attention_getter'])? 1: 0);
			$attention_getter_choice = ((isset($_POST['c']['attention_getter_choice'])) && $_POST['c']['attention_getter_choice'])? intval($_POST['c']['attention_getter_choice']): 0;
			$cart->site->session_variables['attention_getter'] = $use_attention_getter;
			$cart->site->session_variables['attention_getter_choice'] = $attention_getter_choice;
			if ($attention_getter_choice){
				$util = geoAddon::getUtil('attention_getters');
				$cart->site->session_variables['attention_getter_url'] = $util->get_attention_getter_url($attention_getter_choice);
			} else {
				$cart->site->session_variables['attention_getter_url'] = '';
			}
			
			$item->set('session_variables',$cart->site->session_variables);
			$item->save();
			if ($use_attention_getter && !$attention_getter_choice){
				$cart->addError();
				$text = geoAddon::getText('geo_addons','attention_getters');
				$cart->site->error_variables["attention_getter"] = geoString::fromDB($text['no_choice_error']);
			}
			//get current attached attention_getter, if exists..
			$order_item = geoOrderItem::getOrderItemFromParent($item, self::type);
			
			if (!$use_attention_getter || !$attention_getter_choice){
				if ($order_item){
					$id = $order_item->getId();
					geoOrderItem::remove($id);
					$cart->order->detachItem($id);
				}
			} else {
				if (!$order_item){
					$order_item = new addon_attention_getterOrderItem;
					$order_item->setParent($cart->item);//this is a child of the parent
					$order_item->setOrder($cart->order);
					
					$already_attached = false;
					$order_item->save();//make sure it's serialized
					$cart->order->addItem($order_item);
					trigger_error('DEBUG CART: Adding attention_getter: <pre>'.print_r($order_item,1).'</pre>');
				} else {
					trigger_error('DEBUG CART: attention_getter already attached: <pre>'.print_r($order_item,1).'</pre>');
					$cart->order->addItem($order_item);
					$already_attached = true;
				}
				//get the price for attention_getter
				$cost = (!geoMaster::is('site_fees'))? 0:$cart->price_plan['attention_getter_price'];
				$order_item->setCost($cost);
				$order_item->setCreated($cart->order->getCreated());
				
				$order_item->set('attention_getter_url', $attention_getter_url);
				
				//set details specific to bolding
				
				//set id of listing, if known
				if (isset($cart->site->classified_id) && $cart->site->classified_id > 0){
					$order_item->set('listing_id',$cart->site->classified_id);
				}
				
				//serialize so it will be available right away.
				//$order_item->serialize();
			}
			trigger_error('DEBUG CART: attention_getter: '.$cart->site->session_variables['attention_getter']);
		}
		
		//but children might, get steps from children as well.
		$children = geoOrderItem::getChildrenTypes('addon_attention_getter');
		geoOrderItem::callUpdate('geoCart_other_detailsCheckVars',null,$children);
	}
	
	public static function geoCart_other_detailsProcess(){
		$cart = geoCart::getInstance();
		if (!($cart->main_type == self::type || in_array ($cart->main_type,geoOrderItem::getParentTypesFor(self::type))) || !$cart->db->get_site_setting('use_attention_getters'))
		{
			//do not show thingy for attention_getter
			return '';
		}
		
		//everything is done at checkvars step to prevent stuff
		
		//get steps from children as well.
		$children = geoOrderItem::getChildrenTypes('addon_attention_getter');
		if (count($children)){
			//don't actually do extra steps unless there are child thingies potentially
			if ($cart->item->getType() == self::type){
				$item = $cart->item->getParent();
			} else {
				$item = $cart->item;
			}	
			$cart->setPricePlan($item->get('price_plan'),$item->get('category'));
			if (geoPC::is_ent() && !$cart->price_plan['use_attention_getters']){
				//turned off in price plan
				return ;
			}
			geoOrderItem::callUpdate('geoCart_other_detailsProcess',null,$children);
		}
	}
	public function getDisplayDetails ($inCart,$inEmail=false)
	{
		$text =& geoAddon::getText('geo_addons','attention_getters');
		$title = $text['AG_label'];
		$price = $this->getCost(); //people expect numbers to be positive...
		$return = array (
			'css_class' => '',
			'title' => $title,
			'canEdit' => true, //whether can edit it or not
			'canDelete' => true, //whether can remove from cart or not
			'canPreview' => false, //whether can preview the item or not
			'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
			'cost' => $price, //amount this adds to the total, what getCost returns
			'total' => $price,
			'children' => false
		);
		
		//display the attention getter choice
		$p = $this->getParent();
		if (!is_object($p)){
			//parent went away?  thats not good...
			$id = $this->getId();
			geoOrderItem::remove($id);
			$this->getOrder()->detachItem($id);
			return false;
		}
		$session_variables = $p->get('session_variables');
		$ag = geoAddon::getUtil('attention_getters');
		
		//during listing renewals, this is used on the cart summary page, AFTER the sessvars have been condensed to only contain changes
		//if the sessvar value isn't present here, it means the ag value hasn't changed, and we should show the old one, even though it's not in sessvars
		$choice = $session_variables['attention_getter_choice'] ? $session_variables['attention_getter_choice'] : $p->get('previous_ag_choice');
		
		$return['title'] .= " <img src='".(defined('IN_ADMIN')?'../':'').$ag->get_attention_getter_url($choice)."' alt='' />";
		
		//go through children...
		$order = $this->getOrder();
		$items = $order->getItem();
		$children = array();
		foreach ($items as $i => $item){
			if (is_object($item) && is_object($item->getParent()) && $item->getType() !== self::type){
				$p = $item->getParent();
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
		$parent = $this->getParent();
		if ($parent && $parent->getType() === 'listing_renew_upgrade') {
			$return = $parent->checkNoDowngrade($return, 'attention_getter');
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
	
	/**
	 * Returns data to be displayed on listing cost and features section
	 *
	 * @return array of data that is processed and used to display the listing cost box
	 */
	public static function geoCart_other_detailsDisplay (){
		$cart = geoCart::getInstance();
		if (!($cart->main_type == self::type || in_array ($cart->main_type,geoOrderItem::getParentTypesFor(self::type))) || !$cart->db->get_site_setting('use_attention_getters'))
		{
			//do not show thingy for attention_getter
			return '';
		}
	
		if (isset($cart->item->renew_upgrade) && $cart->item->renew_upgrade == listing_renew_upgradeOrderItem::upgrade){
			//this is an upgrade, need to see if parent already has item
			if ($cart->site->parent_session_variables['attention_getter']){
				//already exists on parent, do not allow adding
				return '';
			}
		}
		if ($cart->item->getType() == self::type){
			$item = $cart->item->getParent();
		} else {
			$item = $cart->item;
		}
		$cart->setPricePlan($item->getPricePlan(),$item->getCategory());
		$ag = geoAddon::getUtil('attention_getters');
		
		if (!$cart->price_plan['use_attention_getters']){
			//turned off in price plan
			if ($cart->isCombinedStep()) {
				//if addon is enabled, always add the CSS to the page, so that
				//it works on combined page which may be loaded before category
				$pre = (defined('IN_ADMIN'))? '../' : '';
				geoView::getInstance()->addTop($ag->attention_getter_javascript())
					->addCssFile($pre.geoTemplate::getUrl('css','addon/attention_getters/listing_placement.css'));
			}
			return ;
		}
		
		$ag_params = array('checked' => '', 'checkbox_name' => 'c[attention_getter]');
		$ag_params["price_plan"] = $cart->price_plan;
		$price = (!geoMaster::is('site_fees'))? 0: $cart->price_plan['attention_getter_price'];
		$ag_params["cost"] = geoString::displayPrice($price,false, false, 'cart');
		if (isset($cart->site->error_variables["attention_getter"]) && (strlen($cart->site->error_variables["attention_getter"]) > 0)) {
			$ag_params["error"] = $cart->site->error_variables["attention_getter"];
		}
		$ag_params["toggle"] = $cart->site->session_variables["attention_getter"];
		$ag_params["choice"] = $cart->site->session_variables["attention_getter_choice"];
		
		if($ag_params['choice'] && !$item->get('previous_ag_choice')) {
			//workaround for a case where if the attention getter isn't changed during a renewal, it won't be in the sessvars on the final cart page
			//(and thus would otherwise show a broken image) 
			$item->set('previous_ag_choice',$ag_params['choice']);
			$item->save();	
		}
		
		$parent = ($cart->item->getType()===self::type)? $cart->item->getParent() : $cart->item;
		if ($parent->getType()==='listing_renew_upgrade') {
			//template should know what to do with alterations...
			
			$ag_params = $parent->checkNoDowngrade($ag_params, 'attention_getter');
		}
		
		$attention_getter_html = $ag->display_attention_getter_choices($ag_params);
		

		geoView::getInstance()->addTop($ag->attention_getter_javascript());
		
		
		
		$return = array (
			'checkbox_name' => '', //manually created checkbox
			'title' => '',
			'help_id' => 0,//manually created
			'price_display' => '',
			//templates - over-write mini-template to do things like set margine or something:
			'entire_box' => $attention_getter_html, 	
		);
		
		return $return;
	}
	
	public static function getParentTypes(){
		//this is attached to classifieds, auctions, and 
		//dutch auctions.
		return array(
			'classified',
			'classified_recurring',
			'auction',
			'listing_renew_upgrade',
			'dutch_auction',
			'job_posting',
			'reverse_auctions',
		);
	}
	
	public function getRecurringSubCost()
	{
		$cart = geoCart::getInstance();
		return (!geoMaster::is('site_fees'))? 0: $cart->price_plan['attention_getter_price'];
	}
	
	public static function geoCart_initSteps ($allPossible=false) {
		
	}
	public static function geoCart_initItem_forceOutsideCart () {
		return false;
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
		$db = DataAccess::getInstance();
		if (!$db->get_site_setting('use_attention_getters')) {
			//do not show thingy for bolding
			return false;
		}
		
		return true; //this item has stuff to display on other_details step.
	}
	
	public static function geoCart_deleteProcess(){
		//Remove from the session_variables
		$cart = geoCart::getInstance();
		
		$parent = $cart->item->getParent();
		if (is_object($parent)){
			$session_vars = $parent->get('session_variables');
			$session_vars['attention_getter'] = 0;
			$session_vars['attention_getter_choice'] = 0;
			$parent->set('session_variables',$session_vars);
			$parent->save();
		}
	}
	
	public static function listing_placement_common_createItemForLegacyListing ($vars)
	{
		$item = $vars['item'];
		$listing = $vars['listing'];
		
		if ($listing->attention_getter && $listing->attention_getter_url) {
			$url = $listing->attention_getter_url;
			$db = 1;
			include GEO_BASE_DIR . 'get_common_vars.php';
			$row = $db->GetRow("SELECT `choice_id` FROM ".geoTables::choices_table."
				WHERE `type_of_choice`=10 AND `value`=?", array($url));
			if (isset($row['choice_id'])) {
				$session_variables = $item->get('session_variables');
				$session_variables['attention_getter_choice'] = intval($row['choice_id']);
				$item->set('session_variables',$session_variables);
			}
		}
	}
	
	public static function getActionName ($vars)
	{
		//give it to parent to take care of
		$cart = geoCart::getInstance();
		$parent = $cart->item->getParent();
		if ($parent) {
			return geoOrderItem::callDisplay('getActionName',$vars,'',$parent->getType());
		}
	}
}