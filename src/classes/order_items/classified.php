<?php
//order_items/classified.php
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
## ##    16.03.0-11-g49ce181
## 
##################################

require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';
require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';


class classifiedOrderItem extends _listing_placement_commonOrderItem {
	var $defaultProcessOrder = 10;
	protected $type = 'classified';
	const type = 'classified';
	public function displayInAdmin() {
		if (!geoMaster::is('classifieds')) { return false; }
		return true;
	}
	
	public function getTypeTitle ()
	{
		$type = parent::getTypeTitle();
		if (!geoMaster::is('classifieds')) {
			//show warning
			$type .= ' <span style="color: red;">!Disabled by License or Master Switch!</span>';
		}
		return $type;
	}
	
	public static function geoCart_initSteps($allPossible=false, $subtype=null){
		if (!geoMaster::is('classifieds')) { return; }
		
		$typeToUse = ($subtype) ? $subtype : self::type;
		
		parent::$_type = $typeToUse;
		parent::geoCart_initSteps($allPossible);
		
		$anon = false;
		$cart = geoCart::getInstance();
		$db = DataAccess::getInstance();
		if ($allPossible || self::isAnonymous()) {
			if (geoAddon::getUtil('anonymous_listing')) {
				$anon = true;
				$cart->addStep($typeToUse.':anonymous',null,null,false);
			}
		}
		if (!$anon && $db->get_site_setting('jit_registration') && !defined('IN_ADMIN')) {
			//JIT registration turned on...  either add jit or jit_process depending on if
			//already logged in or not.
			if (self::isAnonymous()) {
				$cart->addStep($typeToUse.':jit',null,null,false);
			} else {
				//the "after finished" step
				$cart->addStep($typeToUse.':jit_after',null,null,false);
			}
		}
	}
	

	public static function geoCart_initSteps_addOtherDetails(){
		$children = geoOrderItem::getChildrenTypes(self::type);
		//can call directly, since this function is required.
		if (geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails',null,'bool_true',$children)){
			//one of the children want to display it, so return true.
			return true;
		}
		
		return false; //nothing to show here. return false
	}
	
	/**
	 * find out if anonymous listings are allowed for this item type
	 *
	 * @return bool true if anonymous allowed, false otherwise 
	 */
	protected static function anonymousAllowed ()
	{
		
		trigger_error('DEBUG ANON: using classified anonymousAllowed()');
		if (!geoMaster::is('classifieds')) { return false; }
		
		$db = DataAccess::getInstance();
		
		if(geoAddon::getUtil('anonymous_listing')) {
			//anonymous addon enabled
			
			$sql = "SELECT pp.type_of_billing FROM ".geoTables::groups_table." as g, ".geoTables::price_plans_table." as pp WHERE 
					g.default_group = 1 AND g.price_plan_id = pp.price_plan_id";
			$defaultBillingType = $db->GetOne($sql);
			if($defaultBillingType == 2) {
				//subscription-based default
				//can't subscribe anonymously, so don't allow anonymity
				return false;
			}
			return true;
		}
		
		if($db->get_site_setting('jit_registration')) {
			return true;
		}
		
		return false;
	}
	
	public static function choose_planCheckVars ($applies_to=null)
	{
		if (!geoMaster::is('classifieds')) { return; }
		parent::choose_planCheckVars(1,'');
	}
	
	public static function choose_planDisplay ($applies_to=null)
	{
		if (!geoMaster::is('classifieds')) { return; }
		parent::$_type = self::type;
		parent::choose_planDisplay(1, '');
	}
	
	public function getDisplayDetails ($inCart, $inEmail=false)
	{
		if (!geoMaster::is('classifieds')) { return; }
		$price = $this->getCost(); //people expect numbers to be positive...
		$msgs = DataAccess::getInstance()->get_text(true, 10202);
		
		$return = array (
			'css_class' => '',
			'title' => $msgs[500318],
			'canEdit' => true, //whether can edit it or not
			'canDelete' => true, //whether can remove from cart or not
			'canPreview' => true, //whether can preview the item or not
			'canAdminEditPrice' => true, //whether price can be edited in admin panel cart
			'priceDisplay' => geoString::displayPrice($price, false, false, 'cart'), //price to display
			'cost' => $price, //amount this adds to the total, what getCost returns
			'total' => $price, //amount this and all children adds to the total
			'children' => false
		);
		$session_variables = $this->get('session_variables');
		$title = $this->_listingTitleDisplay($session_variables['classified_title'], $inEmail);
		if($title) {
			$return['title'] .= " - $title";
		}
		
		//go through children...
		$order = $this->getOrder();
		$items = $order->getItem();
		$children = array();
		foreach ($items as $i => $val){
			if (is_object($val) && is_object($val->getParent())){
				$p = $val->getParent();
				if ($p->getId() == $this->getId()){
					//This is a child of mine...
					$displayResult = $val->getDisplayDetails($inCart, $inEmail);
					if ($displayResult !== false) {
						//only add if they do not return bool false
						$children[$val->getId()] = $displayResult;
						$return['total'] += $children[$val->getId()]['total']; //add to total we are returning.
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
	
	public function geoCart_initItem_new ($item_type=null, $subtype=null)
	{
		if (!geoMaster::is('classifieds')) { return false; }
		$typeToUse = ($subtype) ? $subtype : self::type;
		parent::$_type = $typeToUse;
		return parent::geoCart_initItem_new(1);
	}
	
	public function geoCart_initItem_restore ()
	{
		self::initSessionVars(1);
		return true;
	}
	
	public static function categoryCheckVars ($listing_types_allowed=null, $cat_id=null)
	{
		if (!geoMaster::is('classifieds')) { return; }
		return parent::categoryCheckVars(1);
	}
	
	
	public static function categoryDisplay ($listing_type_allowed=null, $onlyRecurringClassifieds=false)
	{
		if (!geoMaster::is('classifieds')) { return; }
		$tpl_vars = parent::categoryDisplay(1,$onlyRecurringClassifieds);
		
		$cart = geoCart::getInstance();
		$view = geoView::getInstance();
		
		//set text that is specific to auctions
		//500354 = "List an Item"
		$tpl_vars['title1'] = $tpl_vars['txt1'] = $cart->site->messages[606];
		//Cancel link text
		$tpl_vars['cancel_txt'] = $cart->site->messages[500356];
		
		//TODO: Add setting to default to always use dropdowns
		$tpl = 'links.tpl';
		
		if ($cart->isCombinedStep()) {
			$tpl = 'dropdowns.tpl';
		}
		
		$view->setBodyTpl('classified/category_choose/'.$tpl,'','order_items')
			->setBodyVar($tpl_vars);
		
		$cart->site->display_page();
	}
	
	/**
	 * displays the meat of the listing details collection form
	 * @param bool $delayRender if true, do not render the page here because it is being subtyped; caller will handle it
	 * @return array
	 */
	public static function detailsDisplay($delayRender=false){
		if (!geoMaster::is('classifieds')) { return; }
		$cart = geoCart::getInstance();
		$cart->site->sell_type = 1;
		$view = geoView::getInstance();
		
		$tpl_vars = parent::detailsDisplay();
		
		//608 = List an Item
		$tpl_vars['txt1'] = $cart->site->messages[608];
		//108 = "Listing Details"
		$tpl_vars['title1'] = $cart->site->messages[108];
		//111 = ""
		$tpl_vars['desc1'] = $cart->site->messages[111];
		//641 = "Next Step >>"
		$tpl_vars['submit_button_txt'] = $cart->site->messages[641];
		// "Preview" button
		$tpl_vars['preview_button_txt'] = $cart->site->messages[502083];
		//113 = "<img src=..."
		$tpl_vars['cancel_txt'] = $cart->site->messages[113];
		
		//easy way to make the quantity field not appear for classifieds
		$tpl_vars['force_single_quantity'] = true;
		$tpl_vars['listing_process_count_columns'] = $cart->db->get_site_setting('listing_process_count_columns');
		
		if(!$delayRender) {
			$view->setBodyTpl('classified/listing_collect_details.tpl','','order_items')
				->setBodyVar($tpl_vars);
			
			$cart->site->display_page();
		}
		return $tpl_vars;
	}
	
	public static function mediaCheckVars ()
	{
		parent::$_type = self::type;
		parent::mediaCheckVars();
	}
	
	public static function mediaProcess()
	{
		parent::$_type = self::type;
		parent::mediaProcess();
	}
	
	public static function mediaDisplay ()
	{
		$cart = geoCart::getInstance();
		
		$cart->site->page_id = 10;
		$cart->site->get_text();
		
		$tpl_vars = $cart->getCommonTemplateVars();
		$tpl_vars['title1'] = $cart->site->messages[610];
		$tpl_vars['title2'] = $tpl_vars['txt1'] = $cart->site->messages[161];
		$tpl_vars['page_description'] = $cart->site->messages[500904];
		// "Preview" button
		$tpl_vars['preview_button_txt'] = $cart->site->messages[502085];
		$tpl_vars['submit_button_txt'] = $cart->site->messages[500757];
		$tpl_vars['cancel_txt'] = $cart->site->messages[165];
		
		geoView::getInstance()->setBodyVar($tpl_vars);
		
		parent::$_type = self::type;
		
		parent::mediaDisplay();
	}
	
	/**
	 * Returns data to be displayed on listing cost and features section
	 *
	 * @return array of data that is processed and used to display the listing cost box
	 */
	public static function geoCart_other_detailsDisplay ($subtype=null) {
		$cart = geoCart::getInstance();
		if (!$cart->item || (!$subtype && $cart->item->getType() != self::type)) {
			return '';
		}
		
		$typeToUse = ($subtype) ? $subtype : self::type;
		parent::$_type = $typeToUse;
		$return = parent::geoCart_other_detailsDisplay();
		if (!$return) {
			//probably not supposed to show this item
			//but still need to set title and stuff if there
			//are others to display
			$return = array('entire_box' => ' ');
		}
		
		$return ['page_title1'] = $cart->site->messages[500424];
		$return ['page_title2'] = $cart->site->messages[500425];
		$return ['page_desc'] = $cart->site->messages[500426];
		$return ['submit_button_text'] = $cart->site->messages[500427];
		$return ['preview_button_txt'] = $cart->site->messages[502087];
		$return ['cancel_text'] = $cart->site->messages[500428];
		
		return $return;
	}
	
	public static function geoCart_other_detailsCheckVars($subtype=null){
		if (!geoMaster::is('classifieds')) { return; }
		$typeToUse = ($subtype) ? $subtype : self::type;
		parent::$_type = $typeToUse;
		return parent::geoCart_other_detailsCheckVars();
	}
	
	public static function geoCart_other_detailsProcess($subtype=null){
		if (!geoMaster::is('classifieds')) { return; }
		$typeToUse = ($subtype) ? $subtype : self::type;
		parent::$_type = $typeToUse;
		return parent::geoCart_other_detailsProcess();
	}
	/**
	 * Optional.  Required if in getDisplayDetails ($inCart) you returned true for the array index of canPreview
	 *
	 */
	public function geoCart_previewDisplay ($sell_type=null)
	{
		if (!geoMaster::is('classifieds')) { return; }
		parent::geoCart_previewDisplay(1);
	}
	
	public static function geoCart_payment_choicesProcess ($sell_type=null,$subtype=null)
	{
		if (!geoMaster::is('classifieds')) { return; }
		parent::$_type = ($subtype) ? $subtype : self::type;
		parent::geoCart_payment_choicesProcess(1);
	}
	public static function splashLabel()
	{
		$cart = geoCart::getInstance();
		return $cart->site->messages[500493];
	}
	
	public static function choose_planLabel()
	{
		$cart = geoCart::getInstance();
		return $cart->site->messages[500495];
	}
	
	public static function categoryLabel()
	{
		$cart = geoCart::getInstance();
		return $cart->site->messages[500497];
	}
	
	public static function detailsLabel()
	{
		$cart = geoCart::getInstance();
		return $cart->site->messages[500499];
	}
	
	public static function anonymousLabel ()
	{
		$msgs = geoAddon::getText('geo_addons','anonymous_listing');
		return $msgs['stepLabel'];
	}
	
	public static function geoCart_other_detailsLabel()
	{
		$cart = geoCart::getInstance();
		return $cart->site->messages[500505];
	}
	
	function getTransactionDescription(){
		if (!geoMaster::is('classifieds')) { return; }
		return 'Classified Listing';
	}
	
	
	public static function getParentTypes(){
		//this is main order item, no parent types
		return array();
	}	
	
	/**
	 * Optional.
	 * Used: in geoCart::cartDisplay()
	 * 
	 * Used only for "parent" items, this should return the text to use for the new button displayed
	 * in the cart view, for instance something like "Add New Classified".
	 *
	 */	
	public static function geoCart_cartDisplay_newButton($inModule = false)
	{
		if (!geoMaster::is('classifieds') || (self::isAnonymous() && !(self::anonymousAllowed() || DataAccess::getInstance()->get_site_setting('jit_registration'))) ) return '';
		
		//see if allowed to by check
		$cart = geoCart::getInstance();
		if (!($cart->user_data['restrictions_bitmask'] & 1)) {
			//listing placement not allowed
			return '';
		}
		
		//check to make sure creating new classifieds is turned on (separate from the geoPC::is_classifieds master switch)
		if(!$cart->db->get_site_setting('allow_new_classifieds')) {
			return '';
		}
		
		if (!defined('IN_ADMIN') && geoPC::is_print() && $cart->db->get_site_setting('disableClientPlaceListings')) {
			//oops, not allowed to place listing on client side
			
			return false;
		}
		
		//see if max ads allowed is 0
		$maxAllowed = $cart->db->GetOne("SELECT `max_ads_allowed` FROM ".geoTables::price_plans_table." WHERE `price_plan_id`=".(int)$cart->user_data['price_plan_id']);
		if ($cart->user_data['price_plan_id'] && $maxAllowed == 0) {
			//max number of listings is 0, don't display add button
			return '';
		}
		$msgs = DataAccess::getInstance()->get_text(true);
		if ($inModule) {
			//really being called by my_account_links_newButton - same logic, different return value
			return array (
				'icon' => $msgs[500637],
				'label' => $msgs[500638]
			);
		} else {
			if(!$msgs) {
				//haven't gotten text for this page yet -- get it explicitly from cart main
				$msgs = DataAccess::getInstance()->get_text(true, 10202);
			}
			return $msgs[500252];
		}
	}
	
	public static function my_account_links_newButton ()
	{
		return self::geoCart_cartDisplay_newButton(true);
	}
	
	public static function adminItemDisplay ($item_id, $subtype=null)
	{
		if (!geoMaster::is('classifieds')) { return; }
		if (!$item_id){
			return '';
		}
		$item = geoOrderItem::getOrderItem($item_id);
		if (!is_object($item) || (!$subtype && $item->getType() != self::type) || ($subtype && $item->getType() == self::type)) {
			//prevent showing this when it's not the right order type, since this is called statically
			return '';
		}
		
		$info = '';
		$label = ($subtype === 'classified_recurring') ? 'Recurring Classified Listing' : 'Classified Listing';
		$info .= geoHTML::addOption('Item Type',$label);
		parent::$_type = self::type;
		$info .= parent::adminItemDisplay($item_id);
		return $info;
	}
	
	public static function anonymousDisplay()
	{
		if (!geoMaster::is('classifieds')) { return; }
		$cart = geoCart::getInstance();
		$view = geoView::getInstance();
		
		//set page ID
		$cart->site->page_id = "addons/anonymous_listing/anon_pass";
		
		//if password has been generated for this item, use it. otherwise, create a random password
		$randomPassword = ($cart->item->get('anonPass')) ? $cart->item->get('anonPass') : geoAddon::getUtil('anonymous_listing')->createPassword();
		
		//save password
		$cart->item->set('anonPass', $randomPassword);
		
		$cart->site->session_variables['anonymous_password'] = $randomPassword;
		$cart->item->set('session_variables', $cart->site->session_variables);
		
		$cart->item->save();
		
		$tpl_vars = $cart->getCommonTemplateVars();
		$tpl_vars['newPass'] = $randomPassword;
		$tpl_vars['msgs'] = geoAddon::getText('geo_addons','anonymous_listing');
		$tpl_vars['error'] = $cart->getErrorMsg('anonymous_eula');
		
		//find out if EULA is to be used
		$reg = geoAddon::getRegistry('anonymous_listing');
		if($reg && $reg->use_eula == 1) {
			$tpl_vars['use_eula'] = true;
			$registrationText = $cart->db->get_text(true, 15);
			$tpl_vars['eula_text'] = $registrationText[768];
			if(strlen($tpl_vars['eula_text']) == 0) {
				$tpl_vars['eula_type'] = 'hide';
			} else {
				$tpl_vars['eula_type'] = preg_match('/\<[^>]+\>/',$tpl_vars['eula_text']) ? 'div' : 'area';
			}
		}
		
		$view->setBodyTpl('shared/anonymous_data.tpl','','order_items')
			->setBodyVar($tpl_vars);
		$cart->site->display_page();
	}
	
	public static function anonymousCheckVars()
	{
		$reg = geoAddon::getRegistry('anonymous_listing');
		if($reg && $reg->use_eula == 1) {
			if($_POST['eula'] != 1) {
				$cart = geoCart::getInstance();
				$msgs = geoAddon::getText('geo_addons','anonymous_listing');
				$cart->addError()->addErrorMsg('anonymous_eula', $msgs['eulaError']);
				return false;
			}
		}
		return true;
	}
	
	public static function anonymousProcess()
	{
		//nothing to do here
		return true;		
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
		$msgs = DataAccess::getInstance()->get_text(true);
		if ($vars['step'] == 'my_account_links') {
			//short version
			return $msgs[500639];
		} else {
			//action interupted text
			//text "placing new classified"
			return $msgs[500392];
		}
	}
	
	/**
	 * Easy way to identify items that are Listing parent items
	 * (i.e. classified and auction items)
	 * 
	 * superclass returns false, so only items that directly represent listings need this function
	 */
	public function isListingItem()
	{
		return true;
	}
	
	public static function geoCart_canCombineSteps ()
	{
		return geoMaster::is('classifieds');
	}
}