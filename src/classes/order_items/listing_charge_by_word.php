<?php
//order_items/listing_charge_by_word.php
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
##    17.03.0-1-g484722c
## 
##################################


class listing_charge_by_wordOrderItem extends geoOrderItem
{
	
	/**
	 * Set this to match the filename and the class name.  If not set here, need to set it in constructor.
	 *
	 * @var string
	 */
	protected $type = "listing_charge_by_word";
	const type = 'listing_charge_by_word';
	
	
	/**
	 * Needs to be the order that this item will be processed.  This is the default 
	 * 
	 * for example:  when computing tax the "tax function" (tax.php, defaultProcessOrder of 20,000) 
	 * will get all "totals" of all orderitems with a $defaultProcessOrder below 20,000 to get the 
	 * total amount to charge the tax on.
	 * 
	 * System order item #'s:
	 * < 1000 - "normal" order item (such as listing)
	 * 10,000 - subtotal order item
	 * 20,000 - tax order item
	 * (total is handled by system, always at very bottom)
	 *
	 * note: different items can have the same defaultProcessOrder value.  Different criteria
	 * then determine order like alphabetical
	 * 
	 * @var int
	 */
	
	
	protected $defaultProcessOrder = 30;
	//do this before getting the subtotal (subtotal is 10000)
	const defaultProcessOrder = 30;
	
	
	/**
	 * Required.
	 * Used: in admin, PricePlanItemManage class in various places.
	 * 
	 * Return true to display this order item planItem settings in the admin, 
	 * or false to hide it in the admin.
	 *
	 * @return bool
	 */
	public function displayInAdmin()
	{
		return true;
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
		$tpl = new geoTemplate('admin');
		
		$db = DataAccess::getInstance();
		$tpl->assign('pre', $db->get_site_setting('precurrency'));
		$tpl->assign('post', $db->get_site_setting('postcurrency'));
		
		$tpl->assign('enabled', $planItem->getEnabled());
		$tpl->assign('charge_type', $planItem->get('charge_type', 1));
		$tpl->assign('word_cost', $planItem->get('word_cost', 0.05));
		$tpl->assign('renewal_cost', $planItem->get('renewal_cost', 0.05));
		$tpl->assign('count_whitespace', $planItem->get('count_whitespace',0));
		
		//NOTE: since items default to enabled, make sure these switches default to off, so a user has to conciously turn on this functionality.
		$tpl->assign('count_title', $planItem->get('count_title'));
		$tpl->assign('count_description', $planItem->get('count_description'));
		$tpl->assign('count_optionals', $planItem->get('count_optionals'));
		
		$tpl->assign('skip_words', $planItem->get('skip_words',0));
		
		
		$html = $tpl->fetch('order_items/listing_charge_by_word_settings.tpl');
		return $html;
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
		$settings = $_POST['listing_charge_by_word'];
		
		
		$planItem->setEnabled((bool)$settings['enabled']);
		$planItem->set('charge_type', $settings['charge_type']);
		$planItem->set('word_cost', geoNumber::deformat($settings['word_cost']));
		$planItem->set('renewal_cost', geoNumber::deformat($settings['renewal_cost']));
		$planItem->set('count_whitespace', (int)$settings['count_whitespace']);
		$planItem->set('count_title', (int)$settings['count_title']);
		$planItem->set('count_description', (int)$settings['count_description']);
		$planItem->set('count_optionals', (int)$settings['count_optionals']);
		$planItem->set('skip_words', (int)$settings['skip_words']);
		
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
		//nothing to do here, but check with children for completeness
		 		
		//Call children and let them display info about themselves as well
		$children = geoOrderItem::getChildrenTypes(self::type);
		$html = geoOrderItem::callDisplay('adminItemDisplay',$item_id,'',$children);
		
		return $html;
	}
	
	
	/**
	 * Optional.
	 * Used: In admin, when displaying the order item type for a particular item, used
	 * in various places in the admin.
	 * 
	 * @return string
	 */
	public function getTypeTitle ()
	{
		return "Charge by word";
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
	 * listing_charge_by_word:details
	 * 
	 * When the process gets to the step listing_charge_by_word:details, if $_REQUEST['process'] is
	 * defined, then it will make the following static method calls:
	 * listing_charge_by_word::<STEP_NAME>CheckVars(); - if return true, then:
	 * listing_charge_by_word::<STEP_NAME>Process(); - if return true, then it will continue on to next step
	 * 
	 * If $_REQUEST['process'] is NOT defined, or <STEP_NAME>CheckVars() or <STEP_NAME>Process()
	 *  either return false, then it will call:
	 * listing_charge_by_word::<STEP_NAME>Display();
	 * 
	 * That display function is responsible for displaying the page, then including app_bottom.php, 
	 * then exiting.  If it does not exit, the system will display a site error.
	 * 
	 * If the below optional method exists, it will also call that method to determine the "label"
	 * for the step, to be displayed in templates that show the progress.  The method below
	 * should return a string to display as the name of the step, or an empty string if you
	 * wish to hide the step from the user:
	 * listing_charge_by_word::<STEP_NAME>Label();
	 * 
	 * (Of course, above you would replace <STEP_NAME> with "details" if your step was "listing_charge_by_word:details")
	 */
	public static function geoCart_initSteps ($allPossible=false)
	{
		//no steps to add, but check with any children, just in case
		$children = geoOrderItem::getChildrenTypes(self::type);
		geoOrderItem::callUpdate('geoCart_initSteps',$allPossible,$children);
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
		//example of how to make this a child of all listing order items (would need to
		//comment out the first return for it to reach this one)

		//TODO: do we need to add auction or others?
		//if so, just add them to this array
		return array('classified', 'renew_upgrade', 'listing_edit');
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
	 * @return array|bool Either an associative array as documented above, or boolean false to hide this
	 *  item from view.
	 */
	public function getDisplayDetails ($inCart)
	{
		//NOTE: This function is sometimes called "outside" of the cart environment (when $inCart is
		//false), so it is best to not rely on geoCart object for anything.

		$db = DataAccess::getInstance();
		$msgs = $db->get_text(true, 10202);
		
		$type_text = $this->get('charge_type') == 1 ? $msgs[502199] : $msgs[502200];
		$title = $msgs[502198].' '.$type_text.' ('.$this->get('numChargeables').' x '.geoString::displayPrice($this->get('word_cost')).')';
		
		
		$return = array (
			'css_class' => '',//empty string to use default CSS class in the HTML, otherwise a string containing the css class name.
			'title' => $title,//text that is displayed for this item in list of items purchased.
			'canEdit' => false, //show edit button for item, if displaying in cart?
			'canDelete' => false, //show delete button for item, if displaying in cart?
			'canPreview' => false, //show preview button for item, if displaying in cart?
			'priceDisplay' => geoString::displayPrice($this->getCost()), //Price as it is displayed
			'cost' => $this->getCost(), //amount this adds to the total, what getCost returns
			'total' => $this->getCost(), //amount this AND all children adds to the total (will add to it as we parse the children)
			'children' => array() 	//should be array of child items, with the index
	 								//being the item's ID, and the contents being associative array like
	 								//this one.  If no children, it should be an empty array.  (Careful 
									//not to get into any infinite recursion)
		);
		
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
					$displayResult = $item->getDisplayDetails($inCart);
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
	
	
	public static function detailsProcess_getMoreDetailsEnd()
	{
		$cart = geoCart::getInstance();
		$planItem = geoPlanItem::getPlanItem(self::type, $cart->price_plan['price_plan_id'], $cart->price_plan['category_id']);
		$use = true;
		if(!$planItem || !$planItem->isEnabled()) {
			$use = false;
		}
		if(!$planItem->get('count_title') && !$planItem->get('count_description') && !$planItem->get('count_optionals')) {
			//admin hasn't turned on counting for anything
			//no need to do the rest of this stuff
			$use = false;
		}
		//get the item attached to the main item
		$order_item = $cart->getChildItem(self::type);
		
		$parentType = $cart->item->getType();
		if($parentType === 'listing_renew_upgrade' && $cart->item->renew_upgrade == 2) {
			//this is an upgrade of an existing listing -- no need to charge for words again
			trigger_error('DEBUG CART: this is an upgrade. not charging per word!');
			$use = false;
		}
		
		if (!$use) {
			if ($order_item) {
				//kill the order item if it's already been made
				$id = $order_item->getId();
				geoOrderItem::remove($id);
				$cart->order->detachItem($id);
			}
			//nothing more to do here
			return;
		}

		if (!$order_item) {
			$order_item = new listing_charge_by_wordOrderItem;
			$order_item->setType(self::type);
			$order_item->setParent($cart->item);//this is a child of the parent
			$order_item->setOrder($cart->order);
				
			$already_attached = false;
			$order_item->save();//make sure it's serialized
			$cart->order->addItem($order_item);
			trigger_error('DEBUG CART: Adding new charge-per-word');
		} else {
			trigger_error('DEBUG CART: charge-per-word already attached');
			$cart->order->addItem($order_item);
			$already_attached = true;
		}
		//calculate the cost
		$priceToUse = ($parentType === 'listing_renew_upgrade' && $cart->item->renew_upgrade == 1) ? 'renewal' : 'main';
		$cost = $order_item->_getCPWCost($planItem, $cart->site->session_variables, $priceToUse);
		if(!$cost) {
			//nothing to charge (probably already paid for all these words previously)
			//kill the item and exit
			$id = $order_item->getId();
			geoOrderItem::remove($id);
			$cart->order->detachItem($id);
			return;
		}
		$order_item->setCost($cost);
		$order_item->setCreated($cart->order->getCreated());

		//set id of listing, if known
		if (isset($cart->site->classified_id) && $cart->site->classified_id > 0){
			$order_item->set('listing_id',$cart->site->classified_id);
		}

		if (!$already_attached) {
			//attach order item to order
			$cart->order->addItem($order_item);
		}
	}
	
	
	/**
	 * Get the cost for words
	 * 
	 * @param geoPlanItem $planItem
	 * @param array $sessVars
	 * @return float
	 */
	public function _getCPWCost ($planItem, $sessVars, $priceToUse='main')
	{
		if (!$planItem || !$sessVars) {
			trigger_error('DEBUG PRINT: missing plan item or sessvars. setting cost to 0');
			return 0;
		}
		
		trigger_error('DEBUG PRINT: sessVars are: <pre>'.print_r($sessVars,1).'</pre>');
		
		$db = DataAccess::getInstance();
		$cost = 0;
		
		$cfg = array(
			'charge_type' => $planItem->get('charge_type'),
			'count_whitespace' => $planItem->get('count_whitespace'),
			'word_cost' => $planItem->get('word_cost'),
			'renewal_cost' => $planItem->get('renewal_cost'),
			'count_title' => $planItem->get('count_title'),
			'count_description' => $planItem->get('count_description'),
			'count_optionals' => $planItem->get('count_optionals'),
			'skip_words' => $planItem->get('skip_words'),
		);
		
		trigger_error('DEBUG PRINT: cfg array is: <pre>'.print_r($cfg,1).'</pre>');
		
		$numChargeables = 0;
		
		if($cfg['charge_type'] == 1) {
			//charge by word
			if($cfg['count_title'] && strlen($sessVars['classified_title']) > 0) {
				$numChargeables += $this->_countWords($sessVars['classified_title'], $cfg);				
			}
			if($cfg['count_description'] && strlen($sessVars['description']) > 0) {
				$numChargeables += $this->_countWords($sessVars['description'], $cfg);
			}
			if($cfg['count_optionals']) {
				for($i=1; $i<=20; $i++) {
					if(strlen($sessVars['optional_field_'.$i]) > 0) {
						$numChargeables += $this->_countWords($sessVars['optional_field_'.$i], $cfg);
					}
				}
			}
		} elseif($cfg['charge_type'] == 2) {
			//charge by character
			if ($cfg['count_title'] && strlen($sessVars['classified_title']) > 0) {
				$numChargeables += $this->_countChars($sessVars['classified_title'], $cfg);
			}
			if ($cfg['count_description'] && strlen($sessVars['description']) > 0) {
				$numChargeables += $this->_countChars($sessVars['description'], $cfg);
			}
			if ($cfg['count_optionals']) {
				for($i=1; $i<=20; $i++) {
					if(strlen($sessVars['optional_field_'.$i]) > 0) {
						$numChargeables += $this->_countChars($sessVars['optional_field_'.$i], $cfg);
					}
				}
			}
		}
		
		//if this is an edit that is adding words to the listing, only charge for the new ones
		//get the chargeables that have already been paid for
		$listing_id = $this->getParent()->get('listing_id');
		if($listing_id) {
			$alreadyCharged = (int)$db->GetOne("SELECT `charge_per_word_count` FROM ".geoTables::classifieds_table." WHERE `id` = ?", array($listing_id));
		} else {
			$alreadyCharged = 0;
		}

		
		$newToCharge = max(($numChargeables - $alreadyCharged), 0); //charge the greater of the number of new words or 0
		if(!$newToCharge) {
			//nothing new -- nothing to do!
			return 0;
		}
		
		$price = ($priceToUse === 'renewal') ? $cfg['renewal_cost'] : $cfg['word_cost'];
		$cost = $newToCharge * $price;
		trigger_error('DEBUG PRINT: number of chargeables we found is: '.$numChargeables);
		trigger_error('DEBUG PRINT: Of those, '.$newToCharge.' have not been charged yet');
		trigger_error('DEBUG PRINT: The cost of new chargeables is: '.$cost);
		
		//save the important stuff to the registry for easy access later
		$this->set('charge_type',$cfg['charge_type']);
		$this->set('numChargeables', $numChargeables);
		$this->set('price_to_use', $priceToUse);
		$this->set('word_cost', $price);
		
		$this->save();
		
		return $cost;
	}
	
	private function _countWords($field, $config)
	{
		//strip HTML first, to prevent inflated counts
		$field = strip_tags($field);
		//replace all newlines and spaces in the field with an easily-countable code (don't forget HTML non-breaking spaces!)
		$find = array ("\r\n", "\r", "\n", "\t", ' ', '&nbsp');
		$field = str_replace($find,'<SP>', $field);
		$field = preg_replace('/&#[0]+160;/', '<SP>', $field); //also replace all possible variants of &#0160; / &#00160; / etc (the numeric code for &nbsp;)
		
		//explode and count array elements
		$array = explode('<SP>',$field);
		
		$words = 0;
		foreach ($array as $value) {
			if (strlen($value) > 0) {
				//don't count empty "words"
				$words++;
			}
		}
		if ($config['skip_words']) {
			//don't return a value less than 0
			return max(0, ($words - $config['skip_words']));
		}
		return $words;
	}
	
	private function _countChars ($field, $config)
	{
		//strip html before counting characters, or people will freak at inflated counts
		$field = strip_tags($field);
		
		if ($config['count_whitespace']) {
			if ($config['skip_words']) {
				return max(0, (strlen($field) - $config['skip_words']));
			}
			return strlen($field);
		}
				
		//remove whitespace, then count
		$field = preg_replace("/[\s]+/", '', $field);
		
		if ($config['skip_words']) {
			return max(0, (strlen($field) - $config['skip_words']));
		}
		return strlen($field);
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
		
		//allow parent to do common things, like set the status and call children items
		parent::processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
		
		if ($activate) {
			$listing_id = $this->get('listing_id');
			if(!$listing_id) {
				//if listing id isn't set on this item, check the parent (which should be a main listing type and should have it by now)
				$listing_id = $this->getParent()->get('listing_id');
				$this->set('listing_id',$listing_id);
				$this->save();
			}
			$db=DataAccess::getInstance();
			//mark these chargeables as paid -- BUT only if there are more now than there were before!
			$alreadyCharged = (int)$db->GetOne("SELECT `charge_per_word_count` FROM ".geoTables::classifieds_table." WHERE `id` = ?", array($listing_id));
			$newTotalCharged = $this->get('numChargeables');
			if($alreadyCharged > $newTotalCharged) {
				$db->Execute("UPDATE ".geoTables::classifieds_table." SET `charge_per_word_count` = ?  WHERE `id` = ?", array($newTotalCharged, $listing_id));
			}
			
		} else if (!$activate && $already_active) {
			//there's not really anything to do on deactivation for this...is there?
			
		}
	}
}
