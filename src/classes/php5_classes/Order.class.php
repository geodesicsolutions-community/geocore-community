<?php
//Order.class.php
/**
 * Holds the geoOrder object class.
 * 
 * @package System
 * @since Version 4.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
## 
##################################

require_once CLASSES_DIR . PHP5_DIR . 'Registry.class.php';
require_once CLASSES_DIR . PHP5_DIR . 'Invoice.class.php';
require_once CLASSES_DIR . PHP5_DIR . 'OrderItem.class.php';

/**
 * The geoOrder object, an object representation of an order in the system.
 * 
 * One geoOrder object per order, so there can be many geoOrder objects in one
 * page load.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoOrder {
	/**
	 * ID of this order, used to uniquely identify this order.  If this is newly created order that hasn't been
	 * serialized yet, the order id will not be known.
	 *
	 * @var int
	 */
	private $_id;
	
	/**
	 * Status of order.  One of following:
	 * - pending (waiting on payment)
	 * - pending_admin (waiting on admin approval)
	 * - suspended
	 * - canceled
	 * - fraud
	 * - active
	 *
	 * @var string
	 */
	private $_status;
	
	/**
	 * Parent order object, or null if no parent.
	 *
	 * @var geoOrder
	 */
	private $_parent;
	
	/**
	 * Buyer - User ID for person the order is for.
	 *
	 * @var int
	 */
	private $_buyer;
	
	/**
	 * Seller - will be 0 if this is an order from site to a seller.
	 *
	 * @var int
	 */
	private $_seller;
	
	/**
	 * Admin - will be 0 if order was created on front side, or admin ID for admin
	 * that created the order
	 * @var int
	 */
	private $_admin;
	
	/**
	 * Date this order was created.
	 *
	 * @var int unix timestamp
	 */
	private $_created;
	
	/**
	 * Invoice for this order - will be null if there is no invoice.
	 *
	 * @var mixed
	 */
	private $_invoice;
	
	/**
	 * Array of info, can be used for different stuff.
	 *
	 * @var array
	 */
	private $_registry;
	
	/**
	 * Array of item objects attached to this order.
	 *
	 * @var array
	 */
	private $_items;
	
	/**
	 * The recurring billing set for this order.
	 * @var geoRecurringBilling
	 */
	private $_recurringBilling;
		
	/**
	 * Static array of orders that have been retrieved (keep track of them so we don't have to 
	 * keep getting the same one over and over)
	 *
	 * @var array
	 */
	private static $_orders;
	
	/**
	 * Used internally to remember whether there has been changes to the order since it was last
	 *  serialized.  If there is not changes, when serialize is called, nothing will be done.
	 *
	 * @var boolean
	 */
	private $_pendingChanges;
	
	/**
	 * Constructor, initializes stuff.
	 *
	 */
	public function __construct ()
	{
		$this->_id = 0;
		//brand new, at this point, this order has not been serialized or restored from db.
		$this->_pendingChanges = true;
		//set status to incomplete by default
		$this->_status = 'incomplete';
		//set arrays to initially be empty
		$this->_items = array();
		$this->_created = geoUtil::time();
		
		//set up blank registry
		$this->_registry = new geoRegistry();
		$this->_registry->setName('order');
	}
		
	/**
	 * Get the current status of the order.  Note that default value (if not set yet) is pending.
	 *
	 * @return string
	 */
	public function getStatus ()
	{
		return $this->_status;
	}
	/**
	 * Set the status of this order
	 *
	 * @param string $value
	 */
	public function setStatus ($value)
	{
		//Do we need input checking here?  Should be able to set status to whatever...
		$this->_status = $value;
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Call this bad boy to make things happen.  If pushToItems is false, you might as well be
	 * calling setStatus instead.  But if it is true, it will check the settings for each parent
	 * item in this order, and if it does not need admin approval to activate, it will call
	 * that item's processStatusChange() function, passing in either active or pending.
	 * 
	 * If the parent item has a category set, it will also call the update count for that category
	 * automatically.
	 *
	 * @param string $newStatus Set to active to activate it, anything else to deactivate it and
	 *  everything in this order.
	 * @param bool $pushToItems If false, this will basically act just like calling setStatus()
	 * @param bool $sendEmailNotices This var will be passed along to any items if changing the
	 *  status of the item is warrented.  The item should then act accordingly, and if false no e-mails
	 *  will be sent out.
	 * @return geoOrder for easy chaining of commands :)
	 */
	public function processStatusChange ($newStatus, $pushToItems = true, $sendEmailNotices = true)
	{
		trigger_error('DEBUG ORDER: Top of processStatusChange.');
		$sendOrderActiveEmail = false;
		$db = DataAccess::getInstance();
		if ($newStatus == $this->getStatus()){
			//nothing to do for order, but still going to process order items
			trigger_error('DEBUG ORDER: New status same as old.  New: '.$newStatus.' old: '.$this->getStatus());
			if (!$pushToItems) {
				//there really is nothing to do!
				return $this;
			}
		} else {
			//update the order's status
			trigger_error('DEBUG ORDER: Updating orders status, old status: '.$this->getStatus().' new status: '.$newStatus);
			
			if ($sendEmailNotices && $newStatus == 'active' && $this->getStatus() != 'active' && $this->getOrderTotal() > 0) {
				//this order is being activated and there was cost for the order...
				if ($db->get_site_setting('notify_user_order_approved')) {
					//send notification e-mail if sending e-mail notices, the order is being activated, 
					//the setting to notify the user when the order is approved, and the total is > 0.
					$sendOrderActiveEmail = true;
				}
				if ($db->get_site_setting('auto_verify_with_payment') && $this->getBuyer()) {
					//auto-verify user
					$user = geoUser::getUser($this->getBuyer());
					if ($user && $user->verified=='no') {
						//verify the user, they just paid for something!
						$user->verified = 'yes';
					}
				}
			}
			//don't forget, set the status to whatever
			$this->setStatus($newStatus);
		}
		//save the changes on the order, in case any order items rely on the status being updated in the DB (such as
		//the listing edit change storefront category)
		$this->save();
		
		if ($pushToItems) {
			trigger_error('DEBUG ORDER: Pushing changes to order items.');
			if($newStatus === 'active') {
				$itemStatus = 'active';
			} elseif(in_array($newStatus, array('pending', 'pending_admin'))) {
				$itemStatus = 'pending';
			} else {
				$itemStatus = 'declined';
			}
			
			$categories = array();
			$items = $this->getItem('parent'); //make sure all items are unserialized.
			$required_verify = false;
			if ($db->get_site_setting('verify_accounts') && $db->get_site_setting('nonverified_require_approval')) {
				$user = geoUser::getUser($this->getBuyer());
				if (!$user || $user->verified=='no') {
					//this is not a verified user, and not verified users require admin approval...
					$required_verify = true;
				}
			}
			$approvalItems = array();
			foreach ($items as $item) {
				if (!is_object($item)) {
					//skip non-objects or sub-items
					trigger_error('DEBUG ORDER: Item not object, so skipping.');
					continue;
				}
				if ($itemStatus == 'active') {
					//we are activating, check to see if needs admin approval first
					
					$approvalOverride = $item->get('needAdminApproval',null);
					if ($approvalOverride === null) {
						//This is the normal case, the item has not specified whether
						//or not it needs admin approval, so we check the order
						// setting.  Most items will be like this.
						$plan_item = geoPlanItem::getPlanItem($item->getType(), $item->getPricePlan(), $item->getCategory());
						$needsApproval = $plan_item->needAdminApproval();
						if (!$needsApproval && $required_verify && $item->displayInAdmin()) {
							//make it need approval anyways since this account is not verified.
							$needsApproval = true;
						}
						trigger_error('DEBUG ORDER: override not set, so seeing if plan item needs admin approval. approval: '.$needsApproval);
					} else {
						//This is a special case, the order has set needAdminApproval
						//meaning it says to ignore the planitem setting and use this
						//instead.
						trigger_error('DEBUG ORDER: order item approval is over-ride, approval: '.$approvalOverride);
						$needsApproval = $approvalOverride;
					}
					if ($needsApproval) {
						//don't notify this guy, he needs special permission
						//from the admin to be activated
						trigger_error('DEBUG ORDER: Item needs approval, so not processing.  Item#: '.$item->getId());
						if ($db->get_site_setting('admin_notice_item_approval') && $item->getStatus()=='pending' && $item->displayInAdmin()) {
							$approvalItems[] = $item;
						}
						continue;
					}
				}
				//it got here, then we process status change
				trigger_error('DEBUG ORDER: Processing order item # '.$item->getId().', setting status to: '.$itemStatus);
				$item->processStatusChange($itemStatus, $sendEmailNotices,false);
				$category = $item->getCategory();
				if ($category) {
					//so we can update all the categories at once and we don't
					//re-count the same category over and over
					$categories[$category] = $category;
				}
			}
			if (count($categories) > 0) {
				//update each category
				foreach ($categories as $cat) {
					geoCategory::updateListingCount($cat);
				}
			}
		}
		//allow order items to be notified when the order status changes
		//NOTE:  This is for special cases only!  Do NOT use this call to do things
		//related to when items in an order are activated, for that see geoOrderItem::processStatusChange()
		geoOrderItem::callUpdate('Order_processStatusChange',$this);
		//save the changes on the order in case anything changed.
		$this->save();
		trigger_error('DEBUG ORDER: After processing items.');
		if ($sendOrderActiveEmail && $newStatus == 'active') {
			//make sure to send the e-mail AFTER the status has been set and everything saved, in case the e-mail causes problems...
			trigger_error('DEBUG ORDER: sending order e-mails');
			$msgs = $db->get_text(true, 10207);
			$txt1 = $msgs[500346];

			$email = new geoTemplate('system','emails');
			$user = geoUser::getUser($this->getBuyer());
			$email->assign('introduction', $msgs[500950]);
			$email->assign('salutation', $user->getSalutation());
			$email->assign('messageBody',$msgs[500346]);
			$email->assign('orderIdLabel', $msgs[500350]);
			$email->assign('orderId', $this->getId());
			$email->assign('orderStatusActive', $msgs[500351]);
			$email->assign('orderTotalLabel', $msgs[500352]);
			$email->assign('orderTotal', geoString::displayPrice($this->getOrderTotal()));
			$email->assign('fullPaymentReceived', $msgs[500353]);
			
			$itemInfos = array();
			//Display info about items in the order
			$item_info = '';
			$items = $this->getItem('parent');
			foreach ($items as $item) {
				$info = trim($item->geoOrder_processStatusChange_emailItemInfo());
				if (strlen($info)) {
					$itemInfos[] = $info;
				}
			}
			if($itemInfos) {
				$email->assign('itemInfos',$itemInfos);
				$email->assign('infoHeader',$msgs[500349]);
			}
			$email->assign('line', $msgs[500348]);
			
			//Add invoice link
			$invoice = $this->getInvoice();
			if ($invoice) {
				$invoiceId = $invoice->getId();
				$db = DataAccess::getInstance();
				$link = $db->get_site_setting('classifieds_url').'?a=4&b=18&invoiceId='.$invoiceId;
				//TODO: Add some text above link saying what it's for!  Maybe...  Not sure it is needed...
				$email->assign('invoiceLink',$link);
			}
			
			$subject = $msgs[500347];
			$message = $email->fetch('order_complete.tpl');
			geoEmail::sendMail($user->email,$subject,$message, 0, 0, 0, 'text/html');
		}
		if ($sendEmailNotices && $db->get_site_setting('admin_notice_item_approval') && $approvalItems) {
			//send e-mail to admin about items awaiting approval
			$tpl_vars = array();
			foreach ($approvalItems as $item) {
				$tpl_vars['items'][$item->getId()] = $item->getDisplayDetails(false);
			}
			$tpl = new geoTemplate(geoTemplate::ADMIN);
			$tpl->assign($tpl_vars);
		
			geoEmail::sendMail($db->get_site_setting('site_email'), 'New order items awaiting Admin Approval', $tpl->fetch('emails/items_awaiting_approval.tpl'), 0, 0, 0, 'text/html');
		}
		trigger_error('DEBUG ORDER: Bottom of processStatusChange.');
		//return this for easy method chaining :)
		return $this;
	}
	
	/**
	 * Get the ID for this order.
	 *
	 * @return mixed Int if ID is set, or null if this is a new order that has
	 *  not been serialized (saved to database) yet
	 */
	public function getId ()
	{
		return $this->_id;
	}
	
	/**
	 * Sets the ID for this order.  Can only be used internally.
	 *
	 * @param int $value
	 */
	private function setId ($value)
	{
		$this->_id = intval($value);
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the parent order, or returns null if there is no parent
	 *
	 * @return mixed Either geoOrder object, or null if no parent exists
	 */
	public function getParent ()
	{
		if (isset($this->_parent) && is_numeric($this->_parent) && $this->_parent > 0){
			$this->_parent = geoOrder::getOrder($this->_parent);
			if (!$this->_parent->getId()){
				//parent doesn't exist, set parent to be null
				$this->_parent = null;
			}
		}
		return $this->_parent;
	}
	/**
	 * Set the parent.
	 *
	 * @param mixed $value Either the ID for the parent order, or a geoOrder object for an
	 *  order that has already been serialized (so it has an ID)
	 */
	public function setParent ($value)
	{
		if (!is_object($value)){
			//this must be an int, so force it to be an int
			$value = intval($value);
		}
		$this->_parent = $value;
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the buyer ID.
	 *
	 * @return int
	 */
	public function getBuyer ()
	{
		return $this->_buyer;
	}
	/**
	 * Sets the buyer ID.
	 *
	 * @param int $value
	 */
	public function setBuyer ($value)
	{
		//buyer is always int.
		$this->_buyer = intval($value);
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the seller (order's creator) ID, or 0 if this is order from 
	 *
	 * @return int
	 */
	public function getSeller ()
	{
		return $this->_seller;
	}
	/**
	 * Sets the seller (order's creator) ID.
	 *
	 * @param int $value Seller's ID, or 0 if order is for listing charge.
	 */
	public function setSeller ($value)
	{
		$this->_seller = intval($value);
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the admin (order's admin creator) ID, or 0 if this is order was
	 * placed on client side 
	 *
	 * @return int
	 * @since Version 5.2.0
	 */
	public function getAdmin ()
	{
		return $this->_admin;
	}
	/**
	 * Sets the admin (order's admin creator) ID.
	 *
	 * @param int $value Admin's ID, or 0 if order is created on client side.
	 * @since Version 5.2.0
	 */
	public function setAdmin ($value)
	{
		$this->_admin = intval($value);
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the date this order item was created, as unix timestamp
	 *
	 * @return int
	 */
	public function getCreated ()
	{
		return $this->_created;
	}
	/**
	 * Sets the date this item was created.
	 *
	 * @param int $val
	 */
	public function setCreated ($val)
	{
		$this->_created = intval($val);
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the geoInvoice object attached to this order.
	 *
	 * @return geoInvoice
	 */
	public function getInvoice ()
	{
		if (isset($this->_invoice) && is_numeric($this->_invoice) && $this->_invoice > 0){
			$this->_invoice = geoInvoice::getInvoice($this->_invoice);
			if (!$this->_invoice->getId()){
				//invoice doesn't exist, set invoice to be null
				$this->_invoice = null;
			}
		}
		return $this->_invoice;
	}
	/**
	 * Set the invoice attached to this order
	 *
	 * @param Mixed $value Invoice ID attached to this order, or geoInvoice object for invoice
	 *  that has already been serialized.
	 */
	public function setInvoice ($value)
	{
		if (!is_object($value)){
			//this must be an int, so force it to be an int
			$value = intval($value);
		} else {
			//show the invoice who's boss
			$value->setOrder($this);
		}
		$this->_invoice = $value;
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Gets the total of all the items currently attached to this order.
	 *
	 * @param int $up_to_process_order If specified and not 0, will only add up items
	 *  with a process order less than the amount set here.
	 * @return float
	 */
	public function getOrderTotal ($up_to_process_order = 0)
	{
		$total = 0.00;
		//make sure all items are un-serialized.
		$this->getItem();
		foreach ($this->_items as $item) {
			if (is_object($item) && (!$up_to_process_order || $item->getProcessOrder() < $up_to_process_order)) {
				$total += $item->getCost();
			}
		}
		//floating point work-around: if adding multiple numbers with decimal, and
		//it adds up to 0 (so at least one is - number), total ends up
		//being 7.1054273576E-15 instead of 0.  Unless we do this workaround.
		$total = round($total,4);
		return $total;
	}
	/**
	 * Gets an item object attached to this order, specified by the ID,
	 * or an array of all the item objects attached to this order if no ID
	 * is specified.
	 *
	 * @param mixed int|string Either id for order item, or string for the item type name, or "new" 
	 *  to mean items that are new on this page load and have not been saved to the DB yet,
	 *  or 'parent' for items with no parents (so they are the parent), 'recurring' to get recurring item
	 * @return geoOrderItem|array geoOrderItem object if ID is int and valid, or array of
	 *  all order item objects if id is 0, or the first order item that matches the given
	 *  type if id is a string, or null if id is not valid.
	 */
	public function getItem ($id=0)
	{
		$type = 'id';
		if (is_numeric($id)){
			$id = intval($id);
		} else if (strlen(trim($id)) > 0){
			$type = trim($id);
		}
		if (!is_array($this->_items)) {
			$this->_items = array();
		}
		//if ID is 0, they must want all items.
		if ($type == 'id' && $id === 0){
			$array_keys = array_keys($this->_items);
			foreach ($array_keys as $key){
				//prevent infinite recursion, do not get item of index 0
				//also, skip new items because they will always be objects since they
				//are still new.
				if ($key != 0 && $key != 'new'){
					//Make sure all of the items are expanded into objects
					$this->_items[$key] = $this->getItem($key);
				}
			}
			//return full array of items
			return $this->_items;
		} else if ($type == 'parent' || $type == 'recurring') {
			$items = array();
			if ($type == 'recurring' && !geoPC::is_ent()) return false;
			foreach($this->_items as $key => $item) {
				if ($key != 'new' && is_numeric($this->_items[$key]) && $key > 0){
					//it's numeric, need to unserialize it so we can find the type
					$this->_items[$key] = $item = geoOrderItem::getOrderItem($key);
				}
				if (is_object($item) && !$item->getParent()) {
					if ($type == 'recurring' && $item->isRecurring()) {
						//this is the one to return
						return $item;
					}
					if ($type == 'parent') {
						$items [$item->getId()] = $item;
					}
				}
			}
			if ($type =='recurring') {
				//not recurring item in the mix
				return false;
			}
			return $items;
		} elseif ($type != 'new'){
			//figure out an ID for the first item who's type matches
			$found = array();
			
			foreach ($this->_items as $key => $item){
				if ($key != 'new' && is_numeric($this->_items[$key]) && $key > 0){
					//it's numeric, need to unserialize it so we can find the type
					$this->_items[$key] = geoOrderItem::getOrderItem($key);
				}
				if ($key != 'new' && is_object($this->_items[$key]) && $this->_items[$key]->getType() == $type){
					//type of order item was found.
					$found[$this->_items[$key]->getId()] = $this->_items[$key];
				}
			}
			
			if (count($found) > 0){
				//return array of items that have matching type
				return $found;
			}
			if (!$found && isset($this->_items['new']) && is_array($this->_items['new']) && count($this->_items['new']) > 0){
				//not found in serialized items, see if there is one in not-serialized list
				$keys = array_keys($this->_items['new']);
				foreach ($this->_items['new'] as $key=>$item){
					//since in new array, there is no need to unserialize.
					if ($key != 'new' && is_object($this->_items['new'][$key]) && $this->_items['new'][$key]->getType() == $type){
						//classified order item was found.
						$found = true;
						//set order item
						$id = $this->_items['new'][$key]->getId();
						break;
					}
				}
			}
		}
		
		//if the item they want is numeric, the item hasn't been retrieved yet.
		if (isset($this->_items[$id]) && is_numeric($this->_items[$id]) && $this->_items[$id] > 0){
			$this->_items[$id] = geoOrderItem::getOrderItem($id);
		}
		if (!isset($this->_items[$id])){
			//no such item, return null
			return null;
		}
		//return the item
		return $this->_items[$id];
	}
	
	/**
	 * Returns an array of info from all the items in the order, by calling getCostDetails() for each
	 * item.  This is primarily meant for payment gateways to use to see if the cart may contain something
	 * that the gateway is restricted from being used for.
	 * 
	 * @return array
	 * @since Version 6.0.0
	 */
	public function getItemCostDetails ()
	{
		$this->sortItems();
		$items = $this->getItem('parent');
		
		$details = array();
		foreach ($items as $item) {
			$costDetails = $item->getCostDetails();
			if ($costDetails) {
				$details[$item->getId()] = $costDetails;
			}
		}
		return $details;
	}
	
	/**
	 * Sets an item.  Requires an ID to set.  If there is no ID yet, use geoOrder::addItem() instead.
	 * This method cannot be used to add new items that do not have an ID yet.
	 *
	 * @param int $id
	 * @param Mixed $value Either ID for item, or item object.
	 */
	public function setItem ($id, $value)
	{
		$id = intval($id);
		if ($id == 0){
			//don't set item with id of 0, it's invalid.  ("new" is not valid, need to use addItem for
			//new items)
			return false;
		}
		$this->_items[$id] = $value;
		$this->touch(); //there are now pending changes
		return true;
	}
	
	/**
	 * Gets the recurring billing object for this order, or false if none was
	 * found or this is not a recurring billing.
	 * 
	 * @return geoRecurringBilling
	 * @since Version 4.1.0
	 */
	public function getRecurringBilling ()
	{
		if (!geoPC::is_ent()) {
			return false;
		}
		if (isset($this->_recurringBilling)) {
			return $this->_recurringBilling;
		}
		
		//first make sure this is a recurring order
		$item = $this->getItem('recurring');
		
		if (!$item) {
			//item invalid or is not recurring
			return false;
		}
		$db = DataAccess::getInstance();
		$sql = "SELECT `id` FROM ".geoTables::recurring_billing." WHERE `order_id`=? LIMIT 1";
		$row = $db->GetRow($sql, array((int)$this->getId()));
		if (!isset($row['id'])) {
			$this->_recurringBilling = false;
		} else {
			$this->_recurringBilling = geoRecurringBilling::getRecurringBilling($row['id']);
			if (!$this->_recurringBilling || $this->_recurringBilling->getId() == 0) {
				$this->_recurringBilling = false;
			}
		}
		return $this->_recurringBilling;
	}
	
	/**
	 * Sets recurring billing for this order.
	 * @param geoRecurringBilling $recurring
	 * @since Version 4.1.0
	 */
	public function setRecurringBilling ($recurring)
	{
		if (!geoPC::is_ent()) return false;
		
		if (!is_object($recurring) && $recurring) {
			$recurring = geoRecurringBilling::getRecurringBilling($recurring);
		}
		if (is_object($recurring)) {
			if ($this->getId()) {
				$recurring->setOrder($this->getId());
			}
			$this->_recurringBilling = $recurring;
			$this->touch();
		}
	}
	
	/**
	 * Sorts all the items in the order, good if you want to display stuff in 
	 * the right order, and some of the items have been recently added on this
	 * page load.
	 * 
	 * @return geoOrder For chaining functions
	 */
	public function sortItems ()
	{
		$all_items = $this->_items;
		
		//don't bother with sorting new items, just
		//plumk em down where they go
		$new = (isset($all_items['new']))? $all_items['new']: false;
		
		$sorted = array();
		foreach ($all_items as $key => $item) {
			if (!is_object($item)) {
				//probably new array
				continue;
			}
			$sorted[$item->getProcessOrder()][] = $item;
		}
		ksort($sorted);
		$items = array();
		foreach ($sorted as $processOrder) {
			foreach ($processOrder as $item) {
				$items[$item->getId()] = $item;
			}
		}
		if ($new) $items['new'] = $new;
		$this->_items = $items;
		return $this;
	}
	
	/**
	 * Adds an item to this order.  The item can be not serialized yet, if that is the case
	 * it will be added to the new array inside of the items array.
	 *
	 * @param geoOrderItem $item
	 * @return boolean true if add was successful, false otherwise.
	 */
	public function addItem ($item)
	{
		if (!is_object($item)){
			//item that is being added needs to be an object.
			return false;
		}
		$id = $item->getId();
		
		if (!$id){
			//serialize
			$item->serialize();
			$id = $item->getId();
		}
		if ($id > 0){
			//item already has it's own id, so save in items array by it's item.
			$this->_items[$id] = $item;
		} else {
			//this is a brand new item, and doesn't have it's own id yet.  It will
			//get an ID once this thing is serialized for the first time, but for now
			//add it to the new item array.
			$this->_items['new'][] = $item;
		}
			
		$this->touch(); //there are now pending changes
		return true;
	}
	
	/**
	 * Detaches an item from this order object.
	 *
	 * @param int $item_id
	 */
	public function detachItem ($item_id)
	{
		trigger_error('DEBUG CART: DETACH : '.$item_id.' FROM '.$this->getId());
		$item_id = intval($item_id);
		if (!$item_id || !isset($this->_items[$item_id])){
			return;
		}
		
		//first, detach all children
		foreach ($this->_items as $key => $item) {
			if (is_object($item) && is_object($item->getParent()) && $item->getParent()->getId() == $item_id) {
				//this is a child of the item being detached
				unset($this->_items[$key]);
			}
		}
		//now detach main one
		unset ($this->_items[$item_id]);
	}
	
	/**
	 * Convienence method, sets the billing info on the invoice of this order,
	 * if no invoice currently exists, it creates a blank one.
	 * 
	 * See {@link geoInvoice::setBillingInfo()} for more details.
	 * 
	 * @param $info
	 * @return bool
	 * @since Version 4.0.5
	 */
	public function setBillingInfo ($info)
	{
		if (!is_array($info) || !count($info)) {
			//needs to be an array that is not empty
			return false;
		}
		
		//make sure array exists, if not create a new one and at the very least,
		//set the created date so it doesn't get deleted right away.
		$invoice = $this->getInvoice();
		if (!is_object($invoice)) {
			$invoice = new geoInvoice;
			$invoice->setOrder($this);
			$invoice->save(); //so it has an ID
			
			$this->setInvoice($invoice);
			$created = $this->getCreated();
			$invoice->setCreated((($created)? $created: geoUtil::time()));
		}
		return $invoice->setBillingInfo($info);
	}
	
	/**
	 * Convienence method, gets the billing info on the invoice of this order,
	 * if no invoice currently exists, returns empty array.
	 * 
	 * See {@link geoInvoice::getBillingInfo()} for more details.
	 * 
	 * @return bool
	 * @since Version 4.0.5
	 */
	public function getBillingInfo ()
	{
		$invoice = $this->getInvoice();
		if (!is_object($invoice)) {
			return array();
		}
		return $invoice->getBillingInfo();
	}
	
	/**
	 * Gets the specified item from the registry, or if item is one of the "main" items it gets
	 *  that instead.
	 *
	 * @param string $item
	 * @param mixed $default What to return if the item is not set.
	 * @return Mixed the specified item, or false if item is not found.
	 */
	public function get ($item, $default = false)
	{
		if (method_exists($this, 'get'.ucfirst($item))){
			$methodName = 'get'.ucfirst($item);
			return $this->$methodName();
		}
		
		return $this->_registry->get($item, $default);
	}
	
	/**
	 * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
	 *  of something from the registry.
	 *
	 * @param string $item
	 * @param mixed $value
	 */
	public function set ($item, $value)
	{
		$this->touch(); //there are now pending changes
		if (method_exists($this, 'set'.ucfirst($item))){
			$methodName = 'set'.ucfirst($item);
			return $this->$methodName($value);
		}
		
		return $this->_registry->set($item, $value);
	}
	
	/**
	 * Alias of geoOrder::serialize() - see that method for details.
	 * 
	 */
	public function save ()
	{
		return $this->serialize();
	}
	
	
	/**
	 * Serializes the current order (saves changes in the database, or creates new order if the
	 *  order's id is not set.  If it is a new order, it will set the order ID after it has been
	 *  inserted into the database.
	 * 
	 * Also automatically serializes any objects attached to it that are not already serialized. 
	 *
	 */
	public function serialize ()
	{
		$db = DataAccess::getInstance();
		if (!$this->_pendingChanges){
			//no pending changes, no need to serialize.
			return;
		}
		//make sure data is correct data type to insert into database
		$id = $this->_id;
		$status = (strlen($this->_status))? $this->_status: '';
		//if parent is object, set parent to id
		$parent = (is_object($this->_parent))? $this->_parent->getId(): intval($this->_parent);
		$buyer = intval( $this->_buyer);
		$seller = intval($this->_seller);
		$admin = (int)$this->_admin;
		$created = (intval($this->_created))? intval($this->_created): geoUtil::time();
		if (isset($this->_id) && $this->_id > 0){
			//update info
			$sql = "UPDATE ".$db->geoTables->order." SET `status` = ?, `parent` = ?, `buyer` = ?, `seller` = ?, `admin`=?, `created` = ? WHERE `id`=? LIMIT 1";
			
			$query_data = array($status, $parent, $buyer, $seller, $admin, $created, $id);
			
			$result = $db->Execute($sql, $query_data);
			if (!$result){
				trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
		} else {
			//Insert into DB
			$sql = "INSERT INTO ".$db->geoTables->order." (`id`, `status`, `parent`, `buyer`, `seller`, `admin`, `created`) 
					VALUES (NULL, ?, ?, ?, ?, ?, ?)";
			
			$query_data = array($status, $parent, $buyer, $seller, $admin, $created);
			$result = $db->Execute($sql, $query_data);
			if (!$result) {
				trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
			//set id
			$this->_id = $db->Insert_Id();
			//add to orders registry
			self::$_orders[$this->_id] = $this;
		}
		
		//Serialize registry
		$this->_registry->setId($this->_id);
		$this->_registry->setName('order');//make sure name did not get lost or something
		$this->_registry->serialize();//serialize registry
		
		//Serialize Invoice
		if (isset($this->_invoice) && is_object($this->_invoice)){
			$this->_invoice->setOrder($this->_id); //make sure the order is set to this order's id
			$this->_invoice->serialize(); //serialize the invoice
			trigger_error('DEBUG ORDER STATS: Just serialized invoice: <pre>'.print_r($this->_invoice, 1).'</pre>');
		} else {
			trigger_error('DEBUG ORDER STATS: No invoice to serialize: <pre>'.print_r($this->_invoice,1).'</pre>');
		}
		
		//Serialize Order Items
		$item_array_keys = array_keys($this->_items);
		foreach ($item_array_keys as $key){
			if (is_object($this->_items[$key])){
				$this->_items[$key]->setOrder($this->_id); //set the order id to this order's id
				$this->_items[$key]->serialize(); //serialize it
			}
		}
		//serialize recurring billing
		$recurring = $this->getRecurringBilling();
		if ($recurring) {
			$recurring->serialize();
		}
		
		//serialize all new stuff too
		if (isset($this->_items['new']) && is_array($this->_items['new']) && count($this->_items['new']) > 0){
			$item_array_keys = array_keys($this->_items['new']);
			foreach ($item_array_keys as $key){
				if (is_object($this->_items['new'][$key])){
					if (!($this->_items['new'][$key]->getId() > 0 && isset($this->_items[$this->_items['new'][$key]->getId()]))){
						//only serialize ones if they do not also exist as "normal" order item (not new)
						$this->_items['new'][$key]->setOrder($this->_id); //set the order id to this order's id
						$this->_items['new'][$key]->serialize(); //serialize it
						$id = $this->_items['new'][$key]->getId();
						//take out of new array
						unset($this->_items['new'][$key]);
						//add it to normal array
						$this->_items[$id] = $id;
					}
				}
			}
		}
		
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	/**
	 * Unserializes the object for the given ID and applies parameters to this object.
	 *
	 * @param int $id
	 */
	public function unSerialize ($id=0)
	{
		$id = intval($id);
		if (!$id && isset($this->_id)){
			//id set using setId()
			$id = $this->_id;
		}
		if (!$id){
			//can't unserialize without an id!
			return;
		}
		
		$db = DataAccess::getInstance();
		
		//Get the main data
		$sql = "SELECT * FROM ".$db->geoTables->order." WHERE `id`=$id LIMIT 1";
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR CART SQL: ERror unserializing order: '.$db->ErrorMsg());
			return ;
		}
		if ($result->RecordCount() != 1){
			//no order by that id...
			return ;
		}
		//reset all settings except for ID and static array
		$settings = get_class_vars(__class__);
		$skip_settings = array ('_id', '_orders');
		foreach ($settings as $var => $default_val){
			if (!in_array($var, $skip_settings)){
				$this->$var = $default_val;
			}
		}
		$row = $result->FetchRow();
		foreach ($row as $key => $value){
			if (!is_numeric($key)){
				//only process non-numeric rows
				$func = 'set'.ucfirst($key);
				$this->$func( $value );
			}
		}
		if (!$this->_id){
			//something went wrong with unserializing main values
			return ;
		}
		
		//add it to array of orders we have
		self::$_orders[$this->_id] = $this;
		
		//Unserialize registry
		if (!is_object($this->_registry)){
			$this->_registry = new geoRegistry();
		}
		$this->_registry->setName('order');
		$this->_registry->setId($this->_id);
		$this->_registry->unSerialize();
		
		//unserialize invoice
		//get the invoice attached to this order
		$sql = "SELECT `id` FROM ".$db->geoTables->invoice." WHERE `order`={$this->_id} LIMIT 1"; //only coded to get one invoice per order
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL: ERror unserializing order: '.$db->ErrorMsg());
			return ;
		}
		if ($result->RecordCount() > 0){
			$row = $result->FetchRow();
			//only set ID at this point, if invoice is ever needed, it will be unserialized
			$this->_invoice = $row['id'];
		}
		
		//Unserialize order items
		//get the order items attached to this order
		$sql = "SELECT `id` FROM ".$db->geoTables->order_item." WHERE `order`={$this->_id} ORDER BY `process_order`";
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL: ERror unserializing order: '.$db->ErrorMsg());
			return ;
		}
		$this->_items = array();
		
		while ($row = $result->FetchRow()){
			//only set ID at this point, if item is ever needed, it will be unserialized
			$this->_items[$row['id']] = $row['id'];
		}
	}
	
	/**
	 * Static function that removes an order as specified by ID, and also recursively
	 * removes everything attached to it. (this includes child orders, order items, invoices,
	 * transactions, etc).
	 * 
	 * If the order no longer exists, but there are still "ghost" items attached to this order,
	 * this function will remove those ghost items.
	 *
	 * @param int $id
	 * @param bool $removeAttached if false, it will not remove stuff attached to this order like
	 *  registry, order items, and invoice(s)
	 * @param bool $removeAttachedOrders if false, will not remove any child orders of this order.
	 */
	public static function remove ($id, $removeAttached = true, $removeAttachedOrders = true)
	{
		$id = intval($id);
		if (!$id){
			return false;
		}
		$db = DataAccess::getInstance();
		if ($removeAttachedOrders) {
			//remove all stuff attached to this first.
			
			//first, remove all child orders
			$sql = 'SELECT `id` FROM '.geoTables::order.' WHERE `parent` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove child orders for id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0){
				while ($row = $result->FetchRow()){
					if ($row['id'] && $row['id'] != $id){
						//stop infinite recursion ^
						if (!self::remove($row['id'], $removeAttached, $removeAttachedOrders)) {
							trigger_error('ERROR ORDER CART: Returning false, Error when removing order ('.$id.') because child order ('.$row['id'].') returned false when removing.');
							return false;
						}
					}
				}
			}
		}
		if ($removeAttached) {
			//next, remove any order items attached, start with parents
			$sqls[] = 'SELECT `id` FROM '.geoTables::order_item.' WHERE `order` = ? AND `parent`=0';
			//then move on to any order items in this order
			$sqls[] = 'SELECT `id` FROM '.geoTables::order_item.' WHERE `order` = ?';
			foreach ($sqls as $sql) {
				//this way we avoid duplicate code, first run through the loop remove main parents,
				//which will in turn remove the child items, then just to be sure
				//on the second pass through the loop, nuke anything that moves
				//attached to this order..
				$result = $db->Execute($sql, array($id));
				if (!$result){
					trigger_error('ERROR SQL: Error trying to remove attached order items for id: '.$id.' - error: '.$db->ErrorMsg());
					//DO hault on db error, DO NOT keep going
					return false;
				}
				if ($result && $result->RecordCount() > 0){
					while ($row = $result->FetchRow()){
						if ($row['id']){
							if (!geoOrderItem::remove($row['id'])) {
								trigger_error('ERROR ORDER CART: Returning false, Error when removing order ('.$id.') because attached order item ('.$row['id'].') returned false when removing.');
								return false;
							}
						}
					}
				}
			}
			
			//next, remove any invoices attached
			$sql = 'SELECT `id` FROM '.geoTables::invoice.' WHERE `order` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove attached invoices for order id: '.$id.' - error: '.$db->ErrorMsg());
				//DO hault on db error, DO NOT keep going
				return false;
			}
			if ($result && $result->RecordCount() > 0){
				while ($row = $result->FetchRow()){
					if ($row['id']) {
						//DO bother failing if invoice fails to remove.
						if (!geoInvoice::remove($row['id'])) {
							trigger_error('ERROR ORDER CART: Returning false, Error when removing order ('.$id.') because attached invoice ('.$row['id'].') returned false when removing.');
							return false;
						}
					}
				}
			}
			if (geoPC::is_ent()) {
				//now, remove any recurring billing attached
				$sql = "SELECT `id` FROM ".geoTables::recurring_billing." WHERE `order_id`=?";
				$row = $db->GetRow($sql, array($id));
				if ($row && $row['id']) {
					geoRecurringBilling::remove($row['id']);
				}
			}
			
			//remove all registry for this order
			geoRegistry::remove('order', $id);
		}
		
		//lastly, remove the main order.
		$sql = 'DELETE FROM '.$db->geoTables->order.' WHERE `id` = ?';
		$result = $db->Execute($sql, array($id));
		if (!$result){
			trigger_error('ERROR SQL: Error trying to remove order for id: '.$id.' - error: '.$db->ErrorMsg());
			//DO hault on db error, DO NOT keep going
			return false;
		}
		if (isset(self::$_orders[$id])){
			//remove it from list of orders if it is there
			unset(self::$_orders[$id]);
		}
		return true;
	}
	
	/**
	 * Works just like geoOrder::remove() except that this is meant to not affect any listings
	 * or other data that may still be "live", this is used to remove data once it becomes old,
	 * in order to clear up space.
	 *
	 * @param int $id
	 * @param bool $removeAttached if false, it will not remove stuff attached to this order like
	 *  registry, order items, and invoice(s)
	 * @param bool $removeAttachedOrders if false, will not remove any child orders of this order.
	 */
	public static function removeData ($id, $removeAttached = true, $removeAttachedOrders = true)
	{
		$id = intval($id);
		if (!$id){
			return false;
		}
		$db = DataAccess::getInstance();
		
		if ($removeAttachedOrders) {
			//remove all stuff attached to this first.
			
			//first, remove all child orders
			$sql = 'SELECT `id` FROM '.geoTables::order.' WHERE `parent` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove child orders for id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0){
				while ($row = $result->FetchRow()){
					if ($row['id'] && $row['id'] != $id){
						//stop infinite recursion ^
						if (!self::removeData($row['id'], $removeAttached, $removeAttachedOrders)) {
							trigger_error('ERROR ORDER CART: Returning false, Error when removing order ('.$id.') because child order ('.$row['id'].') returned false when removing.');
							return false;
						}
					}
				}
			}
		}
		if ($removeAttached) {
			//next, remove any order items attached.
			$sql = 'SELECT `id` FROM '.geoTables::order_item.' WHERE `order` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove attached order items for id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0){
				while ($row = $result->FetchRow()){
					if ($row['id']){
						if (!geoOrderItem::removeData($row['id'])) {
							trigger_error('ERROR ORDER: Returning false, Error when removing order ('.$id.') because attached order item ('.$row['id'].') returned false when removing.');
							return false;
						}
					}
				}
			}
			
			//next, un-attach any invoices attached, do NOT delete invoices as that is done
			//at a different time
			$sql = 'UPDATE '.geoTables::invoice.' SET `order`=0 WHERE `order` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove attached invoices for order id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			
			//first, remove all registry for this order, registry is always just affecting data
			//so call normal remove for registry.
			geoRegistry::remove('order', $id);
		}
		
		//next, remove the main order.
		$sql = 'DELETE FROM '.$db->geoTables->order.' WHERE `id` = ?';
		$result = $db->Execute($sql, array($id));
		if (!$result){
			trigger_error('ERROR SQL: Error trying to remove order for id: '.$id.' - error: '.$db->ErrorMsg());
			//do not hault on db error, keep going
		}
		if (isset(self::$_orders[$id])){
			//remove it from list of orders if it is there
			unset(self::$_orders[$id]);
		}
	}
	
	/**
	 * Gets the order specified by the ID and returns the geoOrder object for
	 * that order, or a new blank order if the id is 0 or not a valid ID.
	 * 
	 * Should be called statically (like geoOrder::getOrder($id) )
	 *
	 * @param int $id If 0 or invalid ID, Object returned is for a new blank order.
	 * @return geoOrder
	 */
	public static function getOrder ($id=0)
	{
		$id = intval($id); //id should be integer.
				
		//see if order exists in array of orders.
		if ($id > 0 && isset(self::$_orders[$id]) && is_object(self::$_orders[$id])){
			return self::$_orders[$id];
		}
		
		//see if order exists in db
		$order = new geoOrder();
		//Note: unserialize method should add itself to the static array of orders itself.
		$order->unSerialize($id);
		
		//If they specified 0 or an invalid ID, they will get a blank order back
		//from the unSerialize function.
		return $order;
	}
	
	/**
	 * Use when this object, or one of it's child objects, has been changed, so that when it is serialized, it 
	 * will know there are changes that need to be serialized.
	 * 
	 * This also recursevly touches all "parent" objects that this one is attached to.
	 * 
	 * Note that this is automatically called internally when any of the set functions are used.
	 *
	 */
	public function touch ()
	{
		$this->_pendingChanges = true; //there are now pending changes
		
		//touch anything this object is "attached" to
		if (is_object($this->_parent)){
			$this->_parent->touch();
		}
	}
}