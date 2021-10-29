<?php
//RecurringBilling.class.php
/**
 * Holds the geoRecurringBilling object class.
 * 
 * @package System
 * @since Version 4.1.0
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
## ##    7.6.3-142-g79032f3
## 
##################################


/**
 * The geoRecurringBilling object, an object representation of a recurring
 * billing in the system.
 * 
 * One geoRecurringBilling object per item that has recurring billing to auto
 * renew (or auto whatever) an item based on if auto payments are kept active
 * or not.
 * 
 * @package System
 * @since Version 4.1.0
 */
class geoRecurringBilling
{
	/**
	 * Use this to indicate that the current status is active for setStatus.
	 * @var string
	 */
	const STATUS_ACTIVE = 'active';
	
	/**
	 * Indicates a canceled recurring billing, which means it cannot be resumed
	 * so no further processing is done for that recurring billing.
	 * @var string
	 */
	const STATUS_CANCELED = 'canceled';
	
	
	/**
	 * The internal ID for this recurring billing.
	 * 
	 * @var int
	 */
	private $_id;
	
	/**
	 * A secondary ID to identify according to some external ID, needs to be
	 * unique between all recurring billing's with the same gateway.
	 * 
	 * @var string
	 */
	private $_secondaryId;
	
	/**
	 * The order this recurring item is attached to.  The order will hold a lot
	 * of the data, such as the user.
	 * 
	 * @var geoOrder
	 */
	private $_order;
	
	/**
	 * The registry for this recurring billing, holds misc. data needed for
	 * specific gateways.
	 * 
	 * @var geoRegistry
	 */
	private $_registry;
	
	/**
	 * Each recurring billing is married to a specific gateway, this is that
	 * gateway. 
	 * @var geoPaymentGateway
	 */
	private $_gateway;
	
	/**
	 * Used internally to remember whether there has been changes to the
	 * recurring billing since it was last serialized.  If there is not changes,
	 * when serialize is called, nothing will be done.
	 *
	 * @var boolean
	 */
	private $_pendingChanges;
	
	/**
	 * Date that the first payment was made on for this recurring billing.
	 * @var int
	 */
	private $_startDate;
	
	/**
	 * Date that this recurring payment has been paid until.
	 * @var int
	 */
	private $_paidUntil;
	
	/**
	 * Current status of the recurring billing, or last known status.
	 * @var string
	 */
	private $_status;
	
	/**
	 * The price charged for every cycle.
	 * @var float
	 */
	private $_pricePerCycle;
	
	/**
	 * The cycle duration in seconds
	 * @var int
	 */
	private $_cycleDuration;
	
	/**
	 * The order item type, since the order won't always be around need to know
	 * who to process this guy through later down the road.
	 * @var string
	 */
	private $_itemType;
	
	/**
	 * The user id this recurring billing is "for", if applicable.
	 * @var int
	 */
	private $_userId;
	
	/**
	 * An array of all the transactions in this recurring billing item
	 * @var array Array of geoTransaction objects (or id's for transactions)
	 */
	private $_transactions;
	
	/**
	 * Used to save a "user message" that can be set and retrieved to allow
	 * gateways to specify specific messages in certain scenarios.
	 * @var string
	 */
	private $_userMessage;
	
	
	/**
	 * Array of recurring billing objects that have been retrieved in this page
	 * load.
	 * @var array
	 */
	private static $_recurringBillings;
	
	/**
	 * Gets the ID for this recurring billing.
	 * @return int
	 */
	public function getId ()
	{
		return $this->_id;
	}
	
	/**
	 * Sets the internal ID for this recurring billing.
	 * @param int $id
	 */
	private function setId ($id)
	{
		$id = (int)$id;
		if ($id) {
			$this->_id = $id;
			$this->touch();
		}
	}
	
	/**
	 * Gets the cycle duration in seconds.
	 * @return int The cycle duration in seconds.
	 */
	public function getCycleDuration ()
	{
		return $this->_cycleDuration;
	}
	
	/**
	 * Sets the cycle duration for this recurring billing.
	 * @param int $cycleDuration The cycle duration in seconds.
	 */
	public function setCycleDuration ($cycleDuration)
	{
		$cycleDuration = (int)$cycleDuration;
		
		if ($cycleDuration && $this->_cycleDuration != $cycleDuration) {
			$this->_cycleDuration = $cycleDuration;
			$this->touch();
		}
	}
	
	/**
	 * Gets the payment gateway used for this recurring billing.
	 * @return geoPaymentGateway
	 */
	public function getGateway ()
	{
		if ($this->_gateway && !is_object($this->_gateway)) {
			//get the gateway object
			$gateway = geoPaymentGateway::getPaymentGateway($this->_gateway);
			if ($gateway) {
				$this->_gateway = $gateway;
			}
		}
		return $this->_gateway;
	}
	
	/**
	 * Sets the payment gateway.
	 * @param geoPaymentGateway|string $gateway Either the geoPaymentGateway object,
	 *  or string of the gateway name.
	 */
	public function setGateway ($gateway)
	{
		if ($gateway && !is_object($gateway) && geoPaymentGateway::gatewayExists($gateway)) {
			$gateway = geoPaymentGateway::getPaymentGateway($gateway);
		}
		if (is_object($gateway) && (!$this->_gateway || $this->_gateway->getName() != $gateway->getName())) {
			$this->_gateway = $gateway;
			$this->touch();
		}
	}
	
	/**
	 * Gets the item type, since a recurring billing can out-live the order item,
	 * need to have reference to original order item type.
	 * 
	 * @return string
	 */
	public function getItemType ()
	{
		return $this->_itemType;
	}
	
	/**
	 * Sets the item type.
	 * @param string $itemType
	 */
	public function setItemType ($itemType)
	{
		$itemType = trim($itemType);
		
		if ($itemType && $this->_itemType != $itemType) {
			$this->_itemType = $itemType;
			$this->touch();
		}
	}
	
	/**
	 * Gets the user ID this recurring billing is for, if that is applicable
	 * for this recurring billing.  Will be 0 if not applicable.
	 * 
	 * @return int
	 */
	public function getUserId ()
	{
		return (int)$this->_userId;
	}
	
	/**
	 * Sets the user ID for this recurring billing.
	 * @param int $userId
	 */
	public function setUserId ($userId)
	{
		$userId = (int)$userId;
		if ($userId !== (int)$this->_userId) {
			$this->_userId = $userId;
			$this->touch();
		}
	}
	
	/**
	 * Gets the main order item attached to the order for this recurring billing,
	 * or boolean false if not..
	 * @return geoOrderItem
	 */
	public function getOrderItem ()
	{
		if (!is_object($this->getOrder())) {
			//no order, can't get order item for order
			return false;
		}
		$order = $this->getOrder();
		if ($order) {
			return $order->getItem('recurring');
		}
		//no main order item found
		return false;
	}
	
	/**
	 * Gets the original order ID, can be used even if order is not around any
	 * more.  Where getOrder might return null, this might return the order ID.
	 * 
	 * @return int
	 */
	public function getOrderId ()
	{
		if (is_object($this->_order)) {
			return $this->_order->getId();
		}
		return (int)$this->_order;
	}
	
	/**
	 * Gets the order this recurring billing is attached to.
	 * @return geoOrder
	 */
	public function getOrder ()
	{
		if (isset($this->_order) && is_numeric($this->_order) && $this->_order > 0){
			//order object was never gotten, so get it
			$id = (int)$this->_order;
			$this->_order = geoOrder::getOrder($id);
			if (!$this->_order) {
				//order data must have been removed, return null and do not change the
				//order ID
				$this->_order = $id;
				return null;
			}
		}
		return $this->_order;
	}
	
	/**
	 * Set the order attached to this recurring billing.
	 * 
	 * @param geoOrder|int $order Either the order object or the order id.
	 */
	public function setOrder ($order)
	{
		if (!is_object($order)){
			//must be an id, clean it
			$order = (int)$order;
			$id = $order;
		} else {
			$id = $order->getId();
		}
		if (is_object($this->getOrder()) && $this->getOrder()->getId() == $id) {
			//prevent un-necessary setting
			return;
		}
		$this->_order = $order;
		if (is_object($order)) {
			//automatically set the order item type
			$item = $order->getItem('recurring');
			if ($item) {
				$this->setItemType($item->getType());
			}
		}
		$this->touch();
	}
	
	/**
	 * Get the start date, that is the first time the recurring billing was
	 * charged.
	 * @return int The timestamp this recurring billing started on.
	 */
	public function getStartDate ()
	{
		return (int)$this->_startDate;
	}
	
	/**
	 * Sets the timestamp for when this recurring billing started on.
	 * @param int $timestamp
	 */
	public function setStartDate ($timestamp)
	{
		$timestamp = (int)$timestamp;
		
		if ($timestamp && $this->_startDate != $timestamp) {
			$this->_startDate = $timestamp;
			$this->touch();
		}
	}
	
	/**
	 * Get the paid until timestamp currently set.
	 * @return int The timestamp this recurring billing is paid through
	 */
	public function getPaidUntil ()
	{
		return (int)$this->_paidUntil;
	}
	
	/**
	 * Sets the timestamp for when this recurring billing is currently paid
	 * through.
	 * @param int $timestamp
	 */
	public function setPaidUntil ($timestamp)
	{
		$timestamp = (int)$timestamp;
		
		if ($timestamp && $this->_paidUntil != $timestamp) {
			$this->_paidUntil = $timestamp;
			$this->touch();
		}
	}
	
	/**
	 * Get the current status for the recurring billint.  Statuses recognized by system:
	 * geoRecurringBilling::STATUS_ACTIVE = recurring billing was paid up and active at last check.
	 * geoRecurringBilling::STATUS_CANCELED = do not automatically check status of recurring billing 
	 *   after paidUntil is past, or do any other processing.
	 * Any others - considered "in between" status, it's not active but it could
	 *   become active at a later date.  Can be specific to payment gateway, and
	 *   may be displayed to client and/or admin.
	 * 
	 * @return string
	 */
	public function getStatus ()
	{
		return $this->_status;
	}
	
	/**
	 * Sets the status of this recurring billing.  Statuses recognized by system:
	 * geoRecurringBilling::STATUS_ACTIVE = recurring billing was paid up and active at last check.
	 * geoRecurringBilling::STATUS_CANCELED = do not automatically check status of recurring billing 
	 *   after paidUntil is past, or do any other processing.
	 * Any others - considered "in between" status, it's not active but it could
	 *   become active at a later date.  Can be specific to payment gateway, and
	 *   may be displayed to client and/or admin.
	 * @param string $status
	 */
	public function setStatus ($status)
	{
		$status = trim($status);
		
		if ($status && $this->_status != $status) {
			$this->_status = $status;
			$this->touch();
		}
	}
	
	/**
	 * Gets the price charge per cycle.
	 * @return float
	 */
	public function getPricePerCycle ()
	{
		return $this->_pricePerCycle;
	}
	
	/**
	 * Sets the price per cycle for this recurring billing.
	 * @param float $price
	 */
	public function setPricePerCycle ($price)
	{
		if ($this->getPricePerCycle() === $price) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		$price = round(floatval($price), 4);//force cost to be float, 4 decimal places
		$this->_pricePerCycle = $price;
	}
	
	/**
	 * Get the secondary ID for this recurring billing.
	 * 
	 * @return string
	 */
	public function getSecondaryId ()
	{
		return $this->_secondaryId;
	}
	
	/**
	 * This is REQUIRED for every recurring payment, or your gateway will only
	 * be able to have 1 recurring payment.
	 * 
	 * Sets the secondary ID for this recurring billing, needs to be unique
	 * for this recurring billing's gateway or it will fail upon saving.
	 * Also, note that this CANNOT be an int, or it will not be set. 
	 * If you wish to use an int, prepend it with a string like "myid_###" or
	 * similar.  Even if there is any chance it could be interpreted as a numeric
	 * value, prepend it with a string.
	 * 
	 * Max length for secondaryID is 255, if you need something longer it is
	 * recommended to set this to the first 255 chars, then set the full to
	 * registry like $recurring->set('fullId', $fullId); but remember the first
	 * 255 chars must be unique 
	 * 
	 * @param string $id
	 */
	public function setSecondaryId ($id)
	{
		$id = trim($id);
		
		if ($id && $this->_secondaryId != $id) {
			$this->_secondaryId = $id;
			$this->touch();
		}
	}
	
	/**
	 * Gets the "temporary" user message previously set by setUserMessage earlier
	 * in the page load, if any was set.
	 * 
	 * @return string
	 */
	public function getUserMessage ()
	{
		return ''.$this->_userMessage;
	}
	
	/**
	 * Sets a user message for this page load and this recurring billing that
	 * can later be retrieved.
	 * 
	 * @param string $message
	 */
	public function setUserMessage ($message)
	{
		$this->_userMessage = trim($message);
	}
	
	/**
	 * Get a recurring billing object according to it's ID
	 * @param int|string $id Either the internal ID or the secondary ID
	 * @return geoRecurringBilling
	 */
	public static function getRecurringBilling ($id)
	{
		if (!geoPC::is_ent()) return false;
		//see if transaction exists in array of transactions.
		if (is_numeric($id) && isset(self::$_recurringBillings[$id])) {
			return self::$_recurringBillings[$id];
		}
		//see if it is a secondary ID
		if (isset(self::$_recurringBillings['secondaryId'][$id])) {
			return self::$_recurringBillings['secondaryId'][$id];
		}
		//see if transaction exists in db
		$recurringBilling = new geoRecurringBilling();
		//Note: unserialize method should add itself to the static array of transactions itself.
		$recurringBilling->unSerialize($id);
		
		//If they specified 0 or an invalid ID, they will get a blank recurring billing back
		//from the unSerialize function.
		return $recurringBilling;
	}
	
	/**
	 * Gets an array of all the recurring billing objects for the specified user.
	 * 
	 * @param int $userId The user id.  If passing in 0, must set $skipUserCheck
	 *   to true to get any results.
	 * @param bool $returnObjects if false, only return an array of recurring billing ID's
	 * @param bool $skipUserCheck If true, will skip check on user ID and get
	 *   all recurring billings even if user id is 0. 
	 * @return array An array of recurring billing objects, or recurring ID's, depending on
	 *   $returnObjects is true or false
	 * @since Version 4.1.2
	 */
	public static function getAllForUser ($userId, $returnObjects = true, $skipUserCheck = false)
	{
		if (!geoPC::is_ent()) return false;
		
		$userId = (int) $userId;
		if (!$userId && !$skipUserCheck) {
			//user ID invalid (0), and not skiping user check, so don't get all
			return array();
		}
		$db = DataAccess::getInstance();
		$sql = "SELECT `id` FROM ".geoTables::recurring_billing." WHERE `user_id`=?";
		$all = $db->GetAll($sql, array($userId));
		$return = array();
		foreach ($all as $row) {
			if ($returnObjects) {
				$recurring = self::getRecurringBilling($row['id']);
				if ($recurring->getId() == $row['id']) {
					$return[$row['id']] = $recurring;
				}
				unset($recurring);
			} else {
				$return[$row['id']] = $row['id'];
			}
		}
		return $return;
	}
	
	/**
	 * Removes specified recurring billing given the ID
	 * @param int $id
	 * @param bool $removeAttached
	 */
	public static function remove ($id, $removeAttached = true)
	{
		$id = intval($id);
		if (!$id){
			return false;
		}
		$db = DataAccess::getInstance();
		
		//let payment gateway know
		$sql = "SELECT `gateway`, `status` FROM ".geoTables::recurring_billing." WHERE `id`=?";
		$row = $db->GetRow($sql, array($id));
		if (!$row) {
			//not found to begin with, nothing to remove?
			return false;
		}
		
		$gateway = geoPaymentGateway::getPaymentGateway($row['gateway']);
		if ($gateway && $gateway->isRecurring()) {
			//notify gateway recurring billing is being removed
			
			//but first, see if status is not canceled
			if ($row['status'] != self::STATUS_CANCELED) {
				//process cancelation
				$gateway->recurringCancel(self::getRecurringBilling($id),'remove');
			}
			
			geoPaymentGateway::callUpdate('recurring_remove',$id, $gateway->getName());
		}
		
		if ($removeAttached) {
			//remove all stuff attached to this.
			
			//remove any transactions attached.
			$sql = 'SELECT `id` FROM '.geoTables::transaction.' WHERE `recurring_billing` = ?';
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
		}
		
		$sql = 'DELETE FROM '.geoTables::recurring_billing.' WHERE `id` = ?';
		$result = $db->Execute($sql, array($id));
		if (!$result) {
			trigger_error('ERROR SQL: Error trying to remove recurring billing for id: '.$id.' - error: '.$db->ErrorMsg());
			//do not hault on db error, keep going
		}
		if (isset(self::$_recurringBillings[$id])){
			//remove it from list of recurring billings if it is there
			unset (self::$_recurringBillings[$id]);
		}
			
		//last, remove all registry for this recurring billing
		geoRegistry::remove('recurring_billing', $id);
	}
	
	/**
	 * Touch this recurring billing object, indicating that there are changes
	 * to be saved on serialize.
	 */
	public function touch ()
	{
		$this->_pendingChanges = true;
		//touch anything this object is "attached" to
		$order = $this->getOrder();
		if ($order) {
			$order->touch();
		}
	}
	
	/**
	 * Calls upon the payment gateway used for this recurring billing to get an
	 * updated status, and to refresh the paid until var for this billing.
	 * 
	 * @param bool $gatewayUpdates If false, will skip over alerting the payment
	 *   gateway to update the status on the recurring billing.
	 * @param bool $itemUpdates If false, will skip over alerting the order item
	 *   that the status on the recurring billing may have just been changed.
	 */
	public function updateStatus ($gatewayUpdates = true, $itemUpdates = true)
	{
		if ($gatewayUpdates) {
			//first let gateway update the recurring billing status
			$gateway = $this->getGateway();
			if (!$gateway) {
				//Oops! Something wrong
				return false;
			}
			$gateway->recurringUpdateStatus($this);
		}
		if ($itemUpdates) {
			$itemType = $this->getItemType();
			//let the order item update things
			if ($itemType) {
				geoOrderItem::callUpdate('recurringBilling_updateStatus',$this,$itemType);
			}
		}
	}
	
	/**
	 * This calls on the gateway to cancel the recurring billing, and then
	 * calls the order item to let it know the recurring billing has been
	 * canceled so it can act accordingly.
	 * 
	 * @param string $reason If specified, will pass to payment gateway as reason
	 *   recurring billing was canceled, but it depends on each payment gateway
	 *   whether it will be used or not.  Example of use might be "user canceled".
	 * @param bool $alreadyCanceledInGateway If true, signifies that the recurring billing
	 *   is already known to be canceled by the payment gateway, so it will skip
	 *   notifying the payment gateway of the cancelation.  True is a special
	 *   case that should only be used by the gateway itself.
	 * @return bool True if call to payment gateway to cancel recurring is good,
	 *   false if there was a problem at the payment gateway stage.
	 */
	public function cancel ($reason = '', $alreadyCanceledInGateway = false)
	{
		//first let gateway update the recurring billing status
		$gateway = $this->getGateway();
		if (!$gateway) {
			//Oops! Something wrong
			return false;
		}
		if (!$alreadyCanceledInGateway) {
			$result = $gateway->recurringCancel($this, $reason);
			if (!$result) {
				trigger_error('ERROR RECURRING: Payment gateway returned false, cancelation had a problem so not proceeding.');
				return false;
			}
		}
		$itemType = $this->getItemType();
		//let the order item update things
		if ($itemType) {
			geoOrderItem::callUpdate('recurringBilling_cancel',$this,$itemType);
		}
		$this->setStatus(self::STATUS_CANCELED);
		$this->save();
		return true;
	}
	
	/**
	 * Adds a transaction to this recurring billing.  The transaction CAN be not serialized yet, if that is the case
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
		$this->initTransactions();
		
		$id = $transaction->getId();
		if ($id > 0){
			//item already has it's own id, so save in items array by it's item.
			$this->_transactions[$id] = $transaction;
		} else {
			//this is a brand new item, and doesn't have it's own id yet.  It will
			//get an ID once this thing is serialized for the first time, but for now
			//add it to the new item array.
			$this->_transactions['new'][] = $transaction;
		}
		//let child transaction know who's boss!
		$transaction->setRecurringBilling($this);
		//recurring billing has changed
		$this->touch();
		return true;
	}
	
	/**
	 * Use to process a new payment for a recurring billing, for a transaction
	 * that extends the time and costs money.  Also triggers order item hook to
	 * allow "affiliate" type order items hooks to work.
	 * 
	 * @param geoTransaction $transaction The transaction, with all of the details
	 *   of the transaction (other than the attached recurring billing) already
	 *   set.
	 * @param int $paidUntil Timestamp for the "new" paid-util date due to the new
	 *   payment that was received.  If not specified, will assume "now + recurringDuration"
	 * @since Version 7.1.0
	 */
	public function processPayment ($transaction, $paidUntil=0)
	{
		if (!is_object($transaction) || !$transaction->getStatus()) {
			//transaction status not good...
			return;
		}
		if ($transaction->getInvoice()) {
			//transaction tied to an invoice!
			return;
		}
		$this->addTransaction($transaction);
		
		$paidUntil = (int)$paidUntil;
		if (!$paidUntil) {
			//assume it is "now" + the recurring cycle
			$paidUntil = geoUtil::time() + $this->getCycleDuration();
		}
		$this->setPaidUntil($paidUntil);
		if ($paidUntil > geoUtil::time()) {
			//update status to active
			$this->setStatus(self::STATUS_ACTIVE);
			//use updateStatus() since that is how the hooks are called to update
			//any order items
			$this->updateStatus(false);
		}
		
		//Let order items know a payment was made, similar to the Order_processStatusChange
		geoOrderItem::callUpdate('RecurringBilling_processPayment',array('recurring' => $this, 'transaction' => $transaction));
		
		$this->save();
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
		if (isset($this->_transactions[$transaction_id])){
			unset ($this->_transactions[$transaction_id]);
		}
	}
	
	/**
	 * Gets a transaction object attached to this recurring billing, specified by the ID,
	 * or an array of all the transaction objects attached to this invoice if no ID
	 * is specified.
	 *
	 * @param int $id
	 * @return geoTransaction|array(geoTransaction) object if ID is valid, or array of
	 *  all transaction objects if id is 0, or null if id is not valid.
	 */
	public function getTransaction($id=0)
	{
		$this->initTransactions();
		//ID can only be int or the string "new" (to return array of new transactions that don't have an ID yet)
		$id = ($id == 'new')? $id: intval($id);
		
		//if ID is 0, they must want all items.
		if ($id === 0){
			$array_keys = array_keys($this->_transactions);
			foreach ($array_keys as $key){
				//prevent infinite recursion, do not get item of index 0
				//also, skip new transactions because they will always be objects since they
				//are still new.
				if ($key != 0 && $key != 'new'){
					//Make sure all of the items are expanded into objects
					$this->_transactions[$key] = $this->getTransaction($key);
				}
			}
			//return full array of transactions
			return $this->_transactions;
		}
		
		//if the transaction they want is numeric, the transaction hasn't been retrieved yet.
		if (isset($this->_transactions[$id]) && is_numeric($this->_transactions[$id]) && $this->_transactions[$id] > 0) {
			$this->_transactions[$id] = geoTransaction::getTransaction($id);
		}
		if (!isset($this->_transactions[$id])){
			//no such item, return null
			return null;
		}
		//return the transaction
		return $this->_transactions[$id];
	}
	
	/**
	 * If there are any changes, this saves the data in this recurring billing
	 * object to the DB.
	 */
	public function serialize ()
	{
		if (!$this->_pendingChanges) {
			//no pending changes, no need to serialize.
			return;
		}
		
		$db = DataAccess::getInstance();
		
		//make sure data is correct data type to insert into database
		$id = (int)$this->_id;
		$gateway = (is_object($this->_gateway))? $this->_gateway->getName() : $this->_gateway.'';
		$order = (is_object($this->_order))? (int)$this->_order->getId(): (int)$this->_order;
		$user = (int)$this->_userId;
		//default to current time if not set, to prevent it from being immediatly deleted by cron
		$paidUntil = ($this->_paidUntil)? (int)$this->_paidUntil : geoUtil::time();
		$startDate = ($this->_startDate)? (int)$this->_startDate : geoUtil::time();
		$status = $this->_status.'';
		$secondaryId = $this->_secondaryId.'';
		$itemType = $this->_itemType.'';
		$price = round(floatval($this->_pricePerCycle), 4);;
		$duration = (int)$this->_cycleDuration;
		
		if (isset($this->_id) && $this->_id > 0){
			//update info
			$sql = "UPDATE ".geoTables::recurring_billing." SET `gateway` = ?, 
			`order_id` = ?, `user_id`=?, `start_date`=?, `paid_until`=?, `status`=?, `secondary_id`=?, `item_type`=?,
			`price_per_cycle`=?, `cycle_duration`=? WHERE `id` = ? LIMIT 1";
			$stmt = $db->Prepare($sql);
			$query_data = array($gateway, $order, $user, $startDate, $paidUntil, $status, $secondaryId,
				$itemType, $price, $duration, $id);
			$result = $db->Execute($stmt, $query_data);
			if (!$result){
				trigger_error('ERROR SQL: Error with query when serialize recurring billing to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
		} else {
			//Insert into DB
			$sql = "INSERT INTO ".geoTables::recurring_billing." (`id`, `gateway`, `order_id`, `user_id`, `start_date`, `paid_until`, `status`, `secondary_id`, `item_type`, `price_per_cycle`, `cycle_duration`) 
					VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			
			$query_data = array($gateway, $order, $user, $startDate, $paidUntil, $status, $secondaryId, $itemType, $price, $duration);
			
			$result = $db->Execute($sql, $query_data);
			if (!$result){
				trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
			//set id
			$this->_id = $id = $db->Insert_Id();
		}
		self::$_recurringBillings[$id] = $this;
		
		if ($this->_secondaryId) {
			self::$_recurringBillings['secondaryId'][$this->_secondaryId] = $this;
		}
		
		//Serialize order item registry
		if (is_object($this->_registry)) {
			$this->_registry->setId($this->_id);
			$this->_registry->setName('recurring_billing');
			$this->_registry->serialize();//serialize registry
		}
		//Serialize Transactions
		
		foreach ($this->_transactions as $key => $transaction){
			if (is_object($transaction)){
				$transaction->setRecurringBilling($this->_id); //set the id to this id
				$transaction->serialize(); //serialize it
			}
		}
		//serialize all new stuff too
		if (isset($this->_transactions['new']) && is_array($this->_transactions['new']) && count($this->_transactions['new']) > 0){
			$item_array_keys = array_keys($this->_transactions['new']);
			foreach ($item_array_keys as $key){
				if (is_object($this->_transactions['new'][$key])){
					if (!($this->_transactions['new'][$key]->getId() > 0 && isset($this->_transactions[$this->_transactions['new'][$key]->getId()]))){
						//only serialize ones if they do not also exist as "normal" transaction (not new)
						$this->_transactions['new'][$key]->setRecurringBilling($this->_id);
						$this->_transactions['new'][$key]->serialize();
						$id = $this->_transactions['new'][$key]->getId();
						//take out of new array
						unset($this->_transactions['new'][$key]);
						//add it to normal array
						$this->_transactions[$id] = $id;
					}
				}
			}
		}
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	/**
	 * Just a wrapper function for serialize()
	 */
	public function save ()
	{
		$this->serialize();
	}
	/**
	 * un-serialize, as in, get the info from the db
	 * @param number $id
	 */
	public function unSerialize ($id=0)
	{
		if (!$id && isset($this->_id)){
			//id set using setId()
			$id = (int)$this->_id;
		} else if (!$id && isset($this->_secondaryId)){
			//allow to unserialize by the gateway transaction string "transparently"
			$id = ''.$this->_secondaryId;
		}
		if (!$id){
			//can't unserialize without an id!
			return;
		}
		//figure out what column to search
		if (is_numeric($id) && $id > 0){
			$column = '`id`';
		} else {
			//if it's in secondary_id, it MUST be a string!  If you want to use this and store int value,
			//prepend it with a string like myGateway_### or something
			$column = '`secondary_id`';
		}
		$db = DataAccess::getInstance();
		
		//Get the main data
		$sql = "SELECT * FROM ".geoTables::recurring_billing." WHERE $column = ? LIMIT 1";
		$row = $db->GetRow($sql, array($id));
		if (!$row) {
			trigger_error('ERROR SQL: ERror unserializing order: '.$db->ErrorMsg());
			return ;
		}
		
		//reset all settings except for ID
		$settings = get_class_vars(__class__);
		$skip_settings = array ('_id', '_recurringBillings');
		foreach ($settings as $var => $default_val) {
			if (!in_array($var, $skip_settings)){
				$this->$var = $default_val;
			}
		}
		$translate = array (
		'start_date' => 'startDate',
		'paid_until' => 'paidUntil',
		'secondary_id' => 'secondaryId',
		'item_type' => 'itemType',
		'price_per_cycle' => 'pricePerCycle',
		'cycle_duration' => 'cycleDuration',
		'order_id' => 'order',
		'user_id' => 'userId'
		);
		
		foreach ($row as $key => $value) {
			if (!is_numeric($key)){
				//only process non-numeric rows
				if (isset($translate[$key])) {
					$key = $translate[$key];
				}
				$key = '_'.$key;
				$this->$key = $value;
			}
		}
		if (!$this->_id) {
			//something went wrong with unserializing main values
			return ;
		}
		
		//add it to array of orders we have
		self::$_recurringBillings[$this->_id] = $this;
		if ($this->_secondaryId) {
			self::$_recurringBillings['secondaryId'][$this->_secondaryId] = $this;
		}
		
		//NOTE: Transactions and registry are un-serialized after the first time
		//they are needed
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	/**
	 * initializes transactions, used internally.
	 */
	public function initTransactions ()
	{
		if (isset($this->_transactions) && is_array($this->_transactions) || !$this->_id) {
			return;
		}
		$db = DataAccess::getInstance();
		//Unserialize transactions
		$this->_transactions = array();
		//get the transactions attached to this order
		$sql = "SELECT `id` FROM ".geoTables::transaction." WHERE `recurring_billing`={$this->_id} ORDER BY `id`";
		$all = $db->GetAll($sql);
		if ($all === false) {
			trigger_error('ERROR SQL: ERror unserializing transactions, sql: '.$sql.', error: '.$db->ErrorMsg());
			return ;
		}
		foreach ($all as $row) {
			//only set ID at this point, if transaction is ever needed, it will be unserialized
			$this->_transactions[$row['id']] = $row['id'];
		}
	}
	/**
	 * Used internally to init registry
	 */
	public function initRegistry ()
	{
		if (isset($this->_registry) && is_object($this->_registry)) {
			return;
		}
		//Unserialize registry
		$this->_registry = new geoRegistry();
		$this->_registry->setName('recurring_billing');
		$this->_registry->setId($this->_id);
		$this->_registry->unSerialize();
	}
	
	/**
	 * Gets the specified item from the registry, or if item is one of the "main" items it gets
	 *  that instead.
	 *
	 * @param string $item
	 * @param mixed $default What to return if the item is not set.
	 * @return Mixed the specified item, or false if item is not found.
	 */
	public function get ($item, $default=false){
		if (method_exists($this, 'get'.ucfirst($item))){
			$methodName = 'get'.ucfirst($item);
			return $this->$methodName();
		}
		$this->initRegistry();
		return $this->_registry->get($item, $default);
	}
	
	/**
	 * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
	 *  of something from the registry.
	 *
	 * @param string $item
	 * @param mixed $value
	 */
	public function set ($item, $value){
		$old_val = $this->get($item);
		if ($old_val === $value) {
			//already set
			return;
		}
		$this->touch(); //there are new pending changes
		if (method_exists($this, 'set'.ucfirst($item))){
			$methodName = 'set'.ucfirst($item);
			return $this->$methodName($value);
		}
		$this->initRegistry();
		return $this->_registry->set($item, $value);
	}
	
	/**
	 * Convenience method to detect if at least one valid gateway has Recurring Billing turned on
	 * @return bool
	 */
	public static function RecurringBillingIsAvailable()
	{
		//easy way to get any active payment gateways
		$r = geoPaymentGateway::getPaymentGatewayOfType('recurring');
		return (bool)(count($r) > 0);
	}
	
}