<?php
//Invoice.class.php
/**
 * Holds the geoInvoice class.
 * 
 * @package System
 * @since Version 4.0.0
 */


require_once CLASSES_DIR . PHP5_DIR . 'Order.class.php';
require_once CLASSES_DIR . PHP5_DIR . 'Transaction.class.php';

/**
 * This is the invoice object representing an invoice in the system.
 * 
 * In the grand scheme of things, there is 1 invoice attached to an order.  It
 * is actually "coded" to allow multiple invoices to be attached to a single
 * order, but that functionality has not been fully implemented or tested, and
 * will not be until there is an actual reason to use that functionality.
 * 
 * An invoice can have multiple transactions {@link geoTransaction} attached to
 * it.  The "invoice total" is calculated by adding up the amounts on all
 * active transactions attached to the invoice.  If the balance is negative,
 * that means the buyer owes the seller money.  If the balance is positive, that
 * means the seller owes the buyer money, but that part is not accounted
 * for in the system, when that is the case, most of the time it is treated as
 * if the balance is 0 meaning the buyer has paid the seller and nothing more
 * is owed.
 * 
 * @package System
 */
class geoInvoice {
	/**
	 * ID of invoice
	 *
	 * @var int
	 */
	private $id;
	/**
	 * Not used (yet), this allows there to be heiarchy of invoices for a single order
	 *  to make it easy to add functionality later.
	 *
	 * @var geoInvoice object
	 */
	private $parent;
	/**
	 * Order this invoice is attached to
	 *
	 * @var geoOrder
	 */
	private $order;
	/**
	 * Date this order was created.
	 *
	 * @var int unix timestamp
	 */
	private $created;
	/**
	 * Date this order is due
	 *
	 * @var int unix timestamp
	 */
	private $due;
	
	/**
	 * Array of transaction objects attached to this invoice
	 *
	 * @var array
	 */
	private $transactions;
	
	/**
	 * User ID (not saved for the invoice itself, just stored if retrieved from
	 * order or from user)
	 * @var int
	 */
	private $user_id;
	
	/**
	 * Static array of all the invoices that have been created or retrieved during this session.
	 *
	 * @var array
	 */
	private static $invoices;
	
	/**
	 * Array of settings to handle all the "misc" stuff for this order item.
	 *
	 * @var array
	 */
	private $registry;
	
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
	public function __construct(){
		$this->id = 0;
		//brand new, at this point, this order has not been serialized or restored from db.
		$this->_pendingChanges = true;
		$this->created = geoUtil::time();
		$this->due = geoUtil::time();
		$this->transactions = array();
		
		//set up blank registry
		$this->registry = new geoRegistry();
		$this->registry->setName('invoice');
	}
	
	/**
	 * Gets the id for this transaction, or 0 if this is a new transaction that has not been saved yet
	 *
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * Sets the id for this invoice, using internally only.
	 *
	 * @param int $val
	 */
	private function setId($val){
		$this->touch(); //there are now pending changes
		$this->id = $val;
	}
	
	/**
	 * Not used yet, this can be used in the future to make heirarchy of invoices for an order,
	 * if that is needed.
	 *
	 * @return geoInvoice
	 */
	public function getParent(){
		return $this->parent;
	}
	/**
	 * Set the parent invoice (not used much yet)
	 *
	 * @param mixed $val
	 */
	public function setParent($val){
		$this->touch(); //there are now pending changes
		$this->parent = $val;
	}
	
	/**
	 * Get the order this invoice is attached to
	 *
	 * @return geoOrder
	 */
	public function getOrder(){
		if (is_numeric($this->order) && $this->order > 0){
			$this->order = geoOrder::getOrder($this->order);
		}
		return $this->order;
	}
	/**
	 * Set the order this invoice is attached to.
	 *
	 * @param geoOrder|int $val Int id or object of the order
	 */
	public function setOrder($val){
		if (!is_object($val)){
			$val = intval($val); //if not object, it needs to be int
		}
		$this->order = $val;
		$this->touch(); //there are now pending changes
	}
	
	/**
	 * Get the creation date for this invoice
	 *
	 * @return int unix timestamp
	 */
	public function getCreated(){
		return $this->created;
	}
	/**
	 * Set the creation date for this invoice
	 *
	 * @param int $val unix timestamp
	 */
	public function setCreated($val){
		$this->touch(); //there are now pending changes
		$val = intval($val);
		$this->created = $val;
	}
	
	/**
	 * Get the due date for this invoice
	 *
	 * @return int unix timestamp
	 */
	public function getDue(){
		return $this->due;
	}
	/**
	 * Set the due date for this invoice
	 *
	 * @param int $val unix timestamp
	 */
	public function setDue($val){
		$this->touch(); //there are now pending changes
		$val = intval($val);
		$this->due = $val;
	}
	
	/**
	 * Gets a transaction object attached to this invoice, specified by the ID,
	 * or an array of all the transaction objects attached to this invoice if no ID
	 * is specified.
	 *
	 * @param int $id
	 * @return geoTransaction|array(geoTransaction) object if ID is valid, or array of
	 *  all transaction objects if id is 0, or null if id is not valid.
	 */
	public function getTransaction($id=0){
		//ID can only be int or the string "new" (to return array of new transactions that don't have an ID yet)
		$id = ($id == 'new')? $id: intval($id);
		
		//if ID is 0, they must want all items.
		if ($id === 0){
			$array_keys = array_keys($this->transactions);
			foreach ($array_keys as $key){
				//prevent infinite recursion, do not get item of index 0
				//also, skip new transactions because they will always be objects since they
				//are still new.
				if ($key != 0 && $key != 'new'){
					//Make sure all of the items are expanded into objects
					$this->transactions[$key] = $this->getTransaction($key);
				}
			}
			//return full array of transactions
			return $this->transactions;
		}
		
		//if the transaction they want is numeric, the transaction hasn't been retrieved yet.
		if (isset($this->transactions[$id]) && is_numeric($this->transactions[$id]) && $this->transactions[$id] > 0){
			$this->transactions[$id] = geoTransaction::getTransaction($id);
		}
		if (!isset($this->transactions[$id])){
			//no such item, return null
			return null;
		}
		//return the item
		return $this->transactions[$id];
	}
	
	/**
	 * Adds up the amount of all active transactions currently attached to the
	 * invoice, to get the invoice balance.  A negative amount indicates that
	 * the buyer still owes the seller money.
	 * 
	 * @return float
	 */
	public function getInvoiceTotal ()
	{
		//make sure all transactions are unserialized.
		$this->getTransaction();
		
		$invoice_total = 0;
		//go through each transaction, if it is active add it to total.
		$keys = array_keys($this->transactions);
		foreach ($keys as $key){
			if (is_object($this->transactions[$key]) && $this->transactions[$key]->getStatus()){
				$invoice_total += $this->transactions[$key]->getAmount();
			}
		}
		//floating point work-around: if adding multiple numbers with decimal, and
		//it adds up to 0 (so at least one is - number), total ends up
		//being 7.1054273576E-15 instead of 0.  Unless we do this workaround.
		$invoice_total = round($invoice_total,4);
		return $invoice_total;
	}
	
	/**
	 * Attaches a transaction that already exists to this invoice, given the
	 * transaction's ID.  Requires an ID to set.  If there is no ID yet, use geoInvoice::addTransaction() instead.
	 * This method cannot be used to add new transactions that do not have an ID yet.
	 *
	 * @param int $id
	 * @param int|geoTransaction Either ID for transaction, or transaction object.
	 */
	public function setTransaction($id, $value){
		$this->touch(); //there are now pending changes
		$id = intval($id);
		if ($id == 0){
			//don't set item with id of 0, it's invalid.  ("new" is not valid, need to use addItem for
			//new items)
			return false;
		}
		$this->items[$id] = $value;
		return true;
	}
	
	/**
	 * Adds a transaction to this invoice.  The transaction CAN be not serialized yet, if that is the case
	 * it will be added to the new array inside of the transactions array.
	 *
	 * @param geoTransaction $transaction
	 * @return boolean true if add was successful, false otherwise.
	 */
	public function addTransaction($transaction){
		if (!is_object($transaction)){
			//item that is being added needs to be an object.
			return false;
		}
		$id = $transaction->getId();
		if ($id > 0){
			//item already has it's own id, so save in items array by it's item.
			$this->transactions[$id] = $transaction;
		} else {
			//this is a brand new item, and doesn't have it's own id yet.  It will
			//get an ID once this thing is serialized for the first time, but for now
			//add it to the new item array.
			$this->transactions['new'][] = $transaction;
		}
		//let child transaction know who's boss!
		$transaction->setInvoice($this);
		//invoice has changed
		$this->touch();
		return true;
	}
	
	/**
	 * Detaches a transaction from this invoice object.
	 *
	 * @param int $transaction_id
	 */
	public function detachTransaction($transaction_id){
		$transaction_id = intval($transaction_id);
		if (!$transaction_id){
			return;
		}
		if (isset($this->transactions[$transaction_id])){
			unset ($this->transactions[$transaction_id]);
		}
	}
	
	/**
	 * Sets the billing info according to an array of data.  Will only set
	 * data using keys of specific names (to prevent accidentally saving CC
	 * or other sensitive data un-encrypted in the DB):
	 * 
	 * firstname, lastname, address, address_2, city, country, state, zip, phone,
	 * email, payment_type
	 * 
	 * @param array $info
	 * @return bool True if saving info succeeded, false if something went wrong
	 *  or data passed in didn't contain any savable info
	 * @since Version 4.0.5
	 */
	public function setBillingInfo ($info)
	{
		if (!is_array($info) || !count($info)) {
			//needs to be an array that is not empty
			return false;
		}
		
		//This is the array of allowed stuff to store.  If it's not on this list,
		//DO NOT store that key as it might be something sensitive like a CC num.
		$allowedKeys = array (
			'firstname',
			'lastname',
			'address',
			'address_2',
			'city',
			'country',
			'state',
			'zip',
			'phone',
			'email',
			'payment_type'
		);
		
		$stored = array ();
		foreach ($info as $key => $value) {
			if (in_array($key, $allowedKeys)) {
				$stored[$key] = $value;
			}
		}
		
		if (!count($stored)) {
			//nothing to store
			return false;
		}
		
		$this->set('billingInfoData',$stored);
		$this->touch();
		return true;
	}
	
	/**
	 * Gets the billing info previously set using {@link geoInvoice::setBillingInfo()}
	 * 
	 * If no info, returns empty array.
	 * 
	 * @return array
	 * @since Version 4.0.5
	 */
	public function getBillingInfo ()
	{
		return $this->get('billingInfoData', array());
	}
	
	/**
	 * Gets the specified item from the registry, or if item is one of the "main" items it gets
	 *  that instead.
	 *
	 * @param string $item
	 * @param mixed $default What to return if the item is not set.
	 * @return Mixed the specified item, or false if item is not found.
	 */
	public function get($item, $default = false){
		if (method_exists($this, 'get'.ucfirst($item))){
			$methodName = 'get'.ucfirst($item);
			return $this->$methodName();
		}
		
		return $this->registry->get($item, $default);
	}
	
	/**
	 * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
	 *  of something from the registry.
	 *
	 * @param string $item
	 * @param mixed $value
	 */
	public function set($item, $value){
		$this->touch(); //there are now pending changes
		if (method_exists($this, 'set'.ucfirst($item))){
			$methodName = 'set'.ucfirst($item);
			return $this->$methodName($value);
		}
		
		return $this->registry->set($item, $value);
	}
	
	/**
	 * Gets the invoice specified by the ID and returns the geoInvoice object for
	 * that invoice, or a new blank invoice if the id is 0 or not a valid ID.
	 * 
	 * Should be called statically (like geoInvoice::getInvoice($id) )
	 *
	 * @param int $id If 0 or invalid ID, Object returned is for a new blank invoice.
	 * @return geoInvoice
	 */
	public static function getInvoice($id){
		$id = intval($id); //id should be integer.
				
		//see if invoice exists in array of orders.
		if (isset(self::$invoices[$id])){
			return self::$invoices[$id];
		}
		
		//see if order exists in db
		$invoice = new geoInvoice();
		//Note: unserialize method should add itself to the static array of orders itself.
		$invoice->unSerialize($id);
		
		//If they specified 0 or an invalid ID, they will get a blank order back
		//from the unSerialize function.
		return $invoice;
	}
	
	/**
	 * Get the invoice link URL for the given invoice ID (does not check for 
	 * valid invoice ID).
	 * 
	 * @param int $invoiceId The invoice ID to get the the link for.
	 * @param bool $inEmail If true, will include full link including domain, and
	 *   will not use html entities.
	 * @param bool $inAdmin If true, will use link usable from the admin panel.
	 * @return string
	 * @since Version 6.0.0
	 */
	public static function getInvoiceLink ($invoiceId, $inEmail = false, $inAdmin = false)
	{
		$db = DataAccess::getInstance();
		$invoiceId = (int)$invoiceId;
		
		if (!$invoiceId) {
			return '';
		}
		
		if ($inEmail) {
			return $db->get_site_setting('classifieds_url').'?a=4&b=18&invoiceId='.$invoiceId;
		}
		if ($inAdmin) {
			return 'AJAX.php?controller=Invoice&amp;action=getInvoice&amp;invoice_id='.$invoiceId;
		}
		return $db->get_site_setting('classifieds_file_name').'?a=4&amp;b=18&amp;invoiceId='.$invoiceId;
	}
	
	/**
	 * Serializes the current invoice (saves changes in the database, or creates new invoice if the
	 *  id is not set.  If it is a new invoice, it will set the invoice ID after it has been
	 *  inserted into the database.
	 * 
	 * Also automatically serializes any objects attached to it that are not already serialized. 
	 *
	 */
	public function serialize(){
		trigger_error('DEBUG INVOICE STATS: Top of serialize()');
		$db = DataAccess::getInstance();
		if (!$this->_pendingChanges){
			//no pending changes, no need to serialize.
			return;
		}
		$id = $this->id;
		//if parent is object, set parent to id
		$parent = intval((is_object($this->parent))? $this->parent->getId(): $this->parent);
		$order = intval((is_object($this->order))? $this->order->getId(): $this->order);
		$created = intval($this->created);
		$due = intval($this->due);
		if (isset($this->id) && $this->id > 0){
			//update info
			$sql = "UPDATE ".$db->geoTables->invoice." SET `parent` = ?, `order` = ?, `created` = ?, `due` = ? WHERE `id`=? LIMIT 1";
			$query_data = array($parent, $order, $created, $due, $id);
			
			$result = $db->Execute($sql, $query_data);
			if (!$result){
				trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
		} else {
			//Insert into DB
			$sql = "INSERT INTO ".$db->geoTables->invoice." (`id`, `parent`, `order`, `created`, `due`) VALUES (NULL, ?, ?, ?, ?)";
			//if parent is object, set parent to id
			$query_data = array($parent, $order, $created, $due);
			
			$result = $db->Execute($sql, $query_data);
			if (!$result){
				trigger_error('ERROR INVOICE SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
			//set id
			$this->id = $db->Insert_Id();
			//add to invoice registry
			self::$invoices[$this->id] = $this;
		}
		
		if (intval($this->id) == 0){
			//something weird happened, ID is not known, can't proceed without an ID
			trigger_error('ERROR INVOICE: Just serialized invoice to DB, but ID is not known!  Not able to finish serialize invoice.');
			return false;
		}
		trigger_error('DEBUG INVOICE: serialize() - about to serialize registry');
		
		//Serialize registry
		$this->registry->setId($this->id);
		$this->registry->setName('invoice');//make sure name did not get lost or something
		$this->registry->serialize();//serialize registry
		
		trigger_error('DEBUG INVOICE: serialize() - about to serialize transactions');
		
		//Serialize Transactions
		$transaction_array_keys = array_keys($this->transactions);
		trigger_error('DEBUG INVOICE: Transactions attached: <pre>'.print_r($this->transactions,1).'<pre>');
		foreach ($transaction_array_keys as $key){
			if (is_object($this->transactions[$key])){
				$this->transactions[$key]->setInvoice($this->id); //set the invoice id to this invoice's id
				$this->transactions[$key]->serialize(); //serialize it
			}
		}
		//serialize all new stuff too
		if (isset($this->transactions['new']) && is_array($this->transactions['new']) && count($this->transactions['new']) > 0){
			$item_array_keys = array_keys($this->transactions['new']);
			foreach ($item_array_keys as $key){
				if (is_object($this->transactions['new'][$key])){
					if (!($this->transactions['new'][$key]->getId() > 0 && isset($this->transactions[$this->transactions['new'][$key]->getId()]))){
						//only serialize ones if they do not also exist as "normal" transaction (not new)
						$this->transactions['new'][$key]->setInvoice($this->id); //set the invoice id to this invoice's id
						$this->transactions['new'][$key]->serialize(); //serialize it
						$id = $this->transactions['new'][$key]->getId();
						//take out of new array
						unset($this->transactions['new'][$key]);
						//add it to normal array
						$this->transactions[$id] = $id;
					}
				}
			}
		}
		trigger_error('DEBUG INVOICE: Bottom of serialize()');
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	/**
	 * Unserializes the object for the given ID and applies parameters to this object.
	 *
	 * @param int $id
	 */
	public function unSerialize($id=0){
		$id = intval($id);
		if (!$id && isset($this->id)){
			//id set using setId()
			$id = $this->id;
		}
		if (!$id){
			//can't unserialize without an id!
			return;
		}
		
		$db = DataAccess::getInstance();
		
		//Get the main data
		$sql = "SELECT * FROM ".$db->geoTables->invoice." WHERE `id`=$id LIMIT 1";
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL: Error unserializing invoice: '.$db->ErrorMsg());
			return ;
		}
		if ($result->RecordCount() != 1){
			//nothing by that id...
			return ;
		}
		//reset all settings except for ID and static array
		$settings = get_class_vars(__class__);
		$skip_settings = array ('id', 'invoices');
		foreach ($settings as $var => $default_val){
			if (!in_array($var, $skip_settings)){
				$this->$var = $default_val;
			}
		}
		$row = $result->FetchRow();
		foreach ($row as $key => $value){
			if (!is_numeric($key)){
				//only process non-numeric rows
				$this->$key = $value;
			}
		}
		if (!$this->id){
			//something went wrong with unserializing main values
			return ;
		}
		
		//add it to array of invoices we have
		self::$invoices[$this->id] = $this;
		
		//Unserialize registry
		$this->registry = new geoRegistry();
		$this->registry->setName('invoice');
		$this->registry->setId($this->id);
		$this->registry->unSerialize();
		
		//Unserialize transactions
		$this->transactions = array();
		//get the transactions attached to this order
		$sql = "SELECT `id` FROM ".geoTables::transaction." WHERE `invoice`={$this->id} ORDER BY `id`"; //only coded to get one invoice per order
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL: ERror unserializing order: '.$db->ErrorMsg());
			return ;
		}
		while ($row = $result->FetchRow()){
			//only set ID at this point, if transaction is ever needed, it will be unserialized
			$this->transactions[$row['id']] = $row['id'];
		}
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	
	/**
	 * Alias of geoInvoice::serialize() - see that method for details.
	 * 
	 */
	public function save(){
		return $this->serialize();
	}
	
	/**
	 * Sets up the view class to be displayed (by setting mainbody template and vars),
	 * but does NOT do the final step of actually displaying the page or setting the
	 * page ID, those should be done outside of this method, if this method returns
	 * true.  The normal page ID for displaying invoice is 183.
	 * 
	 * @param bool $showOrderDetails if true, displays the order info (if available)
	 *  at the bottom of the page.  Note that some of the order text will be from
	 *  page #10202 (retrieved automatically if needed). If the order is no longer
	 *  available, will fall back and still render invoice, but without order details
	 *  section (new behavior as of Version 7.3.4).
	 * @param bool $printPage Not implemented yet, but once it is added, if true it
	 *  will display a print friendly page, by using a generic overall template for
	 *  the entire page so that the invoice is the only thing on the page (that is the
	 *  plan anyways)
	 * @return bool True if successful and page ready to be displayed, false otherwise
	 */
	public function render ($showOrderDetails = true, $printPage = false)
	{
		//do some error checking first
		if ($showOrderDetails) {
			//get details of order items
			$order = $this->getOrder();
			if (!$order) {
				trigger_error('ERROR TRANSACTION: Error getting order details, so going
						to fall back and attempt to render without showing order information.');
				return $this->render(false, $printPage);
			}
		}
		
		$userid = (int)geoSession::getInstance()->getUserId();
		if (!defined('IN_ADMIN')) {
			//make sure the user ID matches...  Don't bother getting attached user
			//unless there "is" a logged in user
			$invoiceUser = ($userid)? $this->getUser() : 0;
			if (!$invoiceUser || $userid!==$invoiceUser) {
				trigger_error('ERROR STATS INVOICE: Incorrect user, not allowed to view details.');
				return false;
			}
		}
		
		//If we got through all that, then this must be a valid transaction, so display
		//order info and invoice info
		$db = DataAccess::getInstance();
		
		$messages = $db->get_text(true, 183);
		
		$tpl_vars = array();
		$tpl_vars['showOrderDetails'] = $showOrderDetails;
		if ($showOrderDetails) {
			//TODO: Move this to geoOrder object!??
			$tpl_vars['order_items'] = array();
			
			$items = $order->getItem('parent');
			foreach ($items as $i => $item) {
				if (is_object($item)) {
					$result = $item->getDisplayDetails(false);
					if ($result !== false) {
						$tpl_vars['order_items'][$item->getId()] = $result;
					}
				}
			}
			
			if (count($tpl_vars['order_items'])) {
				//add total
				$messages = $db->get_text(true,10202);
				$tpl_vars['order_items'][] = array(
					'css_class' => 'total_order_item', //css class	
					'title' => $messages[500403],
					'priceDisplay' => geoString::displayPrice($order->getOrderTotal()), //Price as it is displayed
					'cost' => 0, //amount this adds to the total, what getCost returns
					'total' => $order->getOrderTotal(), //amount this AND all children adds to the total (will add to it as we parse the children)
					'children' => array()
				);
			}
		}
		
		$tpl_vars['invoice'] = $this->detailsArray();
		
		$tpl_vars['short_date_format'] = $db->get_site_setting('date_field_format_short');
		
		$tpl_vars['invoiceOnly'] = $printPage ? true : false;
		
		if(defined('IN_ADMIN')) {
			$tpl_vars['printUrl'] = 'AJAX.php?controller=Invoice&action=getInvoice&invoice_id='.$this->getId().'&print=1';
			$tpl_vars['in_admin'] = 1;
		} else {
			$tpl_vars['printUrl'] = DataAccess::getInstance()->get_site_setting('classifieds_file_name').'?a=4&amp;b=18&amp;invoiceId='.$this->getId().'&amp;print=1';
			$tpl_vars['in_admin'] = 0;
		}
		
		
		if($printPage || defined('IN_ADMIN')) {
			//this is either the front-end print friendly view, or EITHER of the template views in the admin
			//get the template HTML without all the extra basic-page junk around it
			$tpl_vars['print'] = $printPage; 
			$tpl = new geoTemplate('system','invoices');
			$tpl->assign($tpl_vars);
			
			$html = $tpl->fetch('invoice_standalone.tpl');
			return $html;
		} else {
			//this is the My Account invoice view, so let the View class do its thing and add the page shell and everything
			geoView::getInstance()->setBodyVar($tpl_vars)
				->setBodyTpl('invoice.tpl','','invoices')
				->addCssFile(geoTemplate::getUrl('css','system/invoices/invoice_styles.css'));
			return true;
		}
	}
	
	/**
	 * Gets details of this invoice in an associative array, including details about
	 * things attached to this invoice like transactions.  Suitable for using
	 * to display info in a template.
	 * 
	 * @return array Keys of the array: order_id, invoice_id, order_amount, invoice_amount,
	 *  pay_amount (amount a user must pay to pay off the amount due), invoice_date,
	 *  invoice_due_date, company_address, client (associative array of user details,
	 *  fields are NOT cleaned from DB), transactions (arrays of each transaction details)
	 */
	public function detailsArray ()
	{
		$order = $this->getOrder();
		
		$order_id = ($order)? $order->getId() : 0;
		$details = array();
		
		$details['order_id'] = $order_id;
		$details['invoice_id'] = $this->getId();
		$details['order_amount'] = ($order)? $order->getOrderTotal() : 0;
		$details['invoice_amount'] = $this->getInvoiceTotal();
		$details['pay_amount'] = $this->getInvoiceTotal() * -1;
		$details['invoice_date'] = $this->getCreated();
		$details['invoice_due_date'] = $this->getDue();
		
		$details['company_address'] = nl2br(trim(DataAccess::getInstance()->get_site_setting('company_address',1)));
		$user_id = $this->getUser();
		$user = ($user_id)? geoUser::getUser($user_id) : 0;
		if ($user) {
			$details['client'] = $user->toArray();
		}
		$billingInfo = $this->getBillingInfo();
		foreach ($billingInfo as $key => $val) {
			$details['client'][$key] = $val;
		}
		//set up transactions
		$transactions = $this->getTransaction();
		$tVals = array();
		foreach ($transactions as $transaction) {
			if (is_object($transaction)) {
				//$transaction = geoTransaction::getTransaction($transaction->getId());
				$id = $transaction->getId();
				if ($transaction->getStatus()){
					$class = ($transaction->getAmount() > 0)? 'payment': 'due';
				} else {
					$class = 'pending';
				}
				$tVals[$id] = array (
					'desc' => $transaction->getDescription(),
					'date' => $transaction->getDate(),
					'type' => $transaction->getGateway()->getTitle(),
					'status' => $transaction->getStatus(),
					'amount' => $transaction->getAmount(),
					'amount_class' => $class
				);
			}
		}
		$details['transactions'] = $tVals;
		return $details;
	}
	
	/**
	 * Static function that removes an invoice as specified by ID, and also recursively
	 * removes everything attached to it. (this includes child invoices, transactions, etc).
	 * 
	 * If the invoice no longer exists, but there are still "ghost" items attached to this invoice,
	 * this function will remove those ghost items.
	 *
	 * @param int $id
	 * @param bool $remove_attached if false, it will not remove stuff attached to this order.
	 */
	public static function remove ($id, $remove_attached = true){
		$id = intval($id);
		if (!$id){
			return false;
		}
		$db = DataAccess::getInstance();
		
		if ($remove_attached){
			//remove all stuff attached to this.
			
			//first, remove all child invoices
			$sql = 'SELECT `id` FROM '.$db->geoTables->invoice.' WHERE `parent` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove child invoices for id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0){
				while ($row = $result->FetchRow()){
					if ($row['id'] && $row['id'] != $id){
						//stop infinite recursion ^
						self::remove($row['id']);
					}
				}
			}
			
			//next, remove any transactions attached.
			$sql = 'SELECT `id` FROM '.$db->geoTables->transaction.' WHERE `invoice` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				trigger_error('ERROR SQL: Error trying to remove attached transactions for invoice id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0){
				while ($row = $result->FetchRow()){
					if ($row['id']){
						geoTransaction::remove($row['id']);
					}
				}
			}
			
			//last, remove all registry for this order
			geoRegistry::remove('invoice', $id);
		}
		
		//last, remove the main invoice.
		$sql = 'DELETE FROM '.geoTables::invoice.' WHERE `id` = ?';
		$result = $db->Execute($sql, array($id));
		if (!$result){
			trigger_error('ERROR SQL: Error trying to remove invoice for id: '.$id.' - error: '.$db->ErrorMsg());
			//do not hault on db error, keep going
		}
		if (isset(self::$invoices[$id])){
			//remove it from list of invoices if it is there
			unset(self::$invoices[$id]);
		}
		return true;
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
	public function touch(){
		$this->_pendingChanges = true; //there are now pending changes
		
		//touch anything this object is "attached" to
		if (is_object($this->order)){
			$this->order->touch();
		}
		if (is_object($this->parent)){
			$this->parent->touch();
		}
	}
	
	/**
	 * Gets the user ID, either from the order (if the order still exists) or from
	 * the first transaction that has the user set.  Returns 0 if user cannot be
	 * found.
	 * 
	 * @return int User Id
	 * @since Version 7.3.4
	 */
	public function getUser ()
	{
		if (!isset($this->user_id)) {
			$this->user_id = 0;
			$order = $this->getOrder();
			if ($order) {
				$this->user_id = (int)$order->getBuyer();
			} else {
				$transactions = $this->getTransaction();
				foreach ($transactions as $transaction) {
					$this->user_id = (int)$transaction->getUser();
					if ($this->user_id) {
						break;
					}
				}
			}
		}
		return $this->user_id;
	}
}