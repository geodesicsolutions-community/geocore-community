<?php
//OrderItem.class.php
/**
 * Holds the geoOrderItem class.
 * 
 * @package System
 * @since Version 4.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.02.1-12-g223ffae
## 
##################################

/**
 * The class that all order items must extend.
 * 
 * This has a lot of "default" methods
 * that the order items will inherit and can overwrite if needed, along with some
 * abstract methods that each order item is forced to implement.
 * 
 * This extends an interface so that there are also static methods added by
 * the interface that must also be implemented by each order item.  Failure to
 * implement any of the stuff will result in a PHP fatal error.
 * 
 * This also has a few final static methods that are used system-wide.
 * 
 * @package System
 * @since Version 4.0.0
 */
abstract class geoOrderItem implements iOrderItem
{
	/**
	 * ID for this item
	 *
	 * @var int
	 */
	protected $id;
	
	/**
	 * Status of order item, built-in ones used by system:
	 * - active
	 * - pending
	 * - pending_admin
	 * 
	 * All rest are treated as pending.
	 *
	 * @var string
	 */
	protected $status;
	
	/**
	 * Order this item is attached to.
	 *
	 * @var geoOrder object
	 */
	protected $order;
	/**
	 * Parent order item, or null if this is main order item.
	 * 
	 * @var geoOrderItem object
	 */
	protected $parent;
	/**
	 * Type of order item, goes to one of the order item types.
	 *
	 * @var string
	 */
	protected $type;
	/**
	 * Cost for this item.
	 *
	 * @var float
	 */
	protected $cost;
	
	/**
	 * Date this item was added to order.
	 *
	 * @var int unix timestamp
	 */
	protected $created;
	
	/**
	 * This will be set according to the process order set by the plan item.
	 *
	 * @var int
	 */
	protected $processOrder = 0;
	
	/**
	 * This should be set by each order item type.  It is used when initially creating
	 * the plan order item to set the default process order for that plan item.
	 *
	 * @var int
	 */
	protected $defaultProcessOrder = 5;
	
	/**
	 * This is the price plan this item is created under.
	 *
	 * @var int
	 */
	protected $pricePlan;
	
	/**
	 * This is the category this item was created under, or 0 if
	 * no category.
	 *
	 * @var int
	 */
	protected $category = 0;
	
	/**
	 * This is the user id used in the shared fee feature to note who will collect shared fees on this order it
	 *
	 * @var int
	 */
	protected $paidOutTo = 0;	
	
	/**
	 * Array of settings to handle all the "misc" stuff for this order item.
	 *
	 * @var array
	 */
	protected $registry;
	
	/**
	 * Static array of all the order items that have been unserialized (or newly created)
	 *
	 * @var array
	 */
	private static $orderItems;
	
	/**
	 * Used internally to remember whether there has been changes to the order since it was last
	 *  serialized.  If there is not changes, when serialize is called, nothing will be done.  This is
	 *  set in geoOrderItem::touch()
	 *
	 * @var boolean
	 */
	protected $_pendingChanges;
	
	/**
	 * Associative array of order types, that follows this syntax:
	 * $name => array( 'class_name' => $order_type_class_name, 'parents' => array( $array_of_parent_order_item_types)
	 * //TODO: add it so that these are added in order of processOrder.
	 * 
	 * Where $name is the name, that would be used in the db and would be the file-name, and 
	 * class name (value) is going to be $name.'OrderItem'.
	 *
	 * @var array
	 */
	private static $orderTypes;
	
	/**
	 * Used to store ID's and whether they exist or not.
	 * @var array
	 */
	private static $_itemsExist = array();
	
	/**
	 * Constructor, initializes stuff.
	 *
	 */
	public function __construct ()
	{
		$this->id = 0;
		$this->created = geoUtil::time();
		
		//brand new, at this point, this order has not been serialized or restored from db.
		$this->_pendingChanges = true;
		
		//set up blank registry
		$this->registry = new geoRegistry();
		$this->registry->setName('order_item');
	}
	
	/**
	 * Gets the id for this order item.
	 *
	 * @return int Will be 0 if this item has not been serialized yet (if it is a new item)
	 */
	public function getId ()
	{
		return $this->id;
	}
	
	/**
	 * Sets the id for this order item, only used internally.
	 *
	 * @param int $val
	 */
	private function setId ($val)
	{
		$this->touch(); //there are new pending changes
		$this->id = $val;
	}
	
	/**
	 * Gets the status of this order item.
	 *
	 * @return string
	 */
	public function getStatus ()
	{
		return $this->status;
	}
	
	/**
	 * Set the status of the order item.
	 * @param string $status
	 */
	public function setStatus ($status)
	{
		$status = trim($status);
		if ($this->getStatus() === $status) {
			return;
		}
		$this->touch();
		$this->status = $status;
	}
	
	/**
	 * Gets the geoOrder object this order item is attached to.
	 *
	 * @return geoOrder
	 */
	public function getOrder ()
	{
		if (isset($this->order) && is_numeric($this->order) && $this->order > 0) {
			//order object was never gotten, so get it
			$this->order = geoOrder::getOrder($this->order);
		}
		return $this->order;
	}
	/**
	 * Sets the order this item is attached to.  Can set it to the order id, or by the order object.
	 *
	 * @param mixed $val
	 */
	public function setOrder ($val)
	{
		if (!is_object($val)) {
			//must be an id, clean it
			$val = intval($val);
			$id = $val;
		} else {
			$id = $val->getId();
		}
		if (is_object($this->getOrder()) && $this->getOrder()->getId() == $id) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		
		$this->order = $val;
	}
	
	/**
	 * Gets the geoOrderItem object for the parent order item this is attached to, or null if not attached.
	 *
	 * @return geoOrderItem
	 */
	public function getParent ()
	{
		if (isset($this->parent) && is_numeric($this->parent) && $this->parent > 0) {
			//order object was never gotten, so get it
			$this->parent = geoOrderItem::getOrderItem($this->parent);
		}
		return $this->parent;
	}
	/**
	 * Sets the order this item is attached to.  Can set it to the order id, or by the order object.
	 *
	 * @param mixed $val
	 */
	public function setParent ($val)
	{
		if (!is_object($val)){
			//must be an id, clean it
			$val = intval($val);
			$id = $val;
		} else {
			$id = $val->getId();
		}
		if (is_object($this->getParent()) && $this->getParent()->getId() == $id) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		
		$this->parent = $val;
	}
	
	/**
	 * Get the cost for this item.
	 *
	 * @return double
	 */
	public function getCost ()
	{
		return $this->cost;
	}
	/**
	 * Set the cost for this item.
	 *
	 * @param double $val
	 */
	public function setCost ($val)
	{
		if ($this->getCost() === $val) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		$val = round(floatval($val), 4);//force cost to be float, 4 decimal places
		$this->cost = $val;
	}
	
	/**
	 * Gets the date this order item was created, as unix timestamp
	 *
	 * @return int
	 */
	public function getCreated ()
	{
		return $this->created;
	}
	/**
	 * Sets the date this item was created.
	 *
	 * @param int $val
	 */
	public function setCreated ($val)
	{
		if ($this->getCreated() === $val) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		$val = intval($val);//it should be an int
		$this->created = $val;
	}
	
	/**
	 * Gets the paid_out_to for this order item, user id of user paid out to
	 *
	 * @return int
	 */
	public function getPaidOutTo ()
	{
		return $this->paidOutTo;
	}
	/**
	 * Sets the date this item was created.
	 *
	 * @param int $val
	 */
	public function setPaidOutTo ($val)
	{
		if ($this->getPaidOutTo() === $val) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		$val = intval($val);//it should be an int
		$this->paidOutTo = $val;
	}	
	
	/**
	 * Gets the type of order item
	 *
	 * @return string
	 */
	public function getType ()
	{
		return $this->type;
	}
	
	/**
	 * Title displayed in the admin
	 * @return string
	 */
	public function getTypeTitle ()
	{
		return ucwords(str_replace('_',' ',$this->getType()));
	}
	
	/**
	 * Sets the type of orderitem this is
	 *
	 * @param string $val
	 */
	public function setType ($val)
	{
		if ($this->getType() === $val) {
			return;
		}
		$this->touch(); //there are new pending changes
		$this->type = $val;
	}
	
	/**
	 * Gets the price plan
	 *
	 * @return int
	 */
	public function getPricePlan ()
	{
		return $this->pricePlan;
	}
	
	/**
	 * Sets the price plan.  This will also validate the price plan (if not setting to 0 to reset) to make
	 * sure the specified price plan exists.  If it does not exist, it will attempt to find and set the
	 * price plan to the default price plan for the seller attached to the order that this order
	 * item is attached to.
	 *
	 * @param int $val The price plan ID.
	 * @param int $userId If set, will check to make sure the price plan is a
	 *  valid one for the specified user.  Otherwise will asume calling method
	 *  is doing it's own checks.
	 * @since The $userId var was added Version 4.1.0
	 */
	public function setPricePlan ($val, $userId = 0)
	{
		if (!$userId && (int)$this->getPricePlan() === (int)$val) {
			//already set to this value, and not needing to double check it is valid for a user
			return;
		}
		//validate the price plan
		$pricePlanId = (int)$val;
		if (!$pricePlanId) {
			//special case, allow the reset of the price plan ID to
			//0 if someone really wants to..
			$this->pricePlan = 0;
			$this->touch();
			return;
		}
		$userId = (int)$userId;
		if (geoPlanItem::isValidPricePlan($pricePlanId)) {
			//The price plan is valid, next see if it is valid for the user..
			if (!$userId || geoPlanItem::isValidPricePlanFor ($userId, $pricePlanId)) {
				//passed (or skipped) the user checks
				$this->touch(); //there are new pending changes
				$this->pricePlan = intval($val);
				return;
			}
		}
		
		if (!isset($this->pricePlan) || !$this->pricePlan) {
			//The price plan attempting to set to is not valid, and the current price
			//plan is not set yet, so set the price plan to be the default one for the
			//user (if the user is known) or the default for the site (if the user is not known)
			if (!$userId) {
				//attempt to get it from the order
				$order = $this->getOrder();
				if (!$order) {
					//order not set for this item, attempt to get listing ID if set
					$listingId = (int)$this->get('listing_id');
					if ($listingId) {
						$listing = geoListing::getListing($listingId);
						if ($listing && $listing->id) {
							//get user ID from seller on listing we found.
							$userId = (int)$listing->seller;
						}
					}
				} else {
					$userId = $order->getBuyer();
				}
			}
			$pricePlanId = geoPlanItem::getDefaultPricePlan($userId);
			if ($pricePlanId) {
				//we were able to retrieve the default price plan for this
				//user, so set it.
				$this->pricePlan = $pricePlanId;
				$this->touch();
			}
		}
	}
	
	/**
	 * Gets the category (will be 0 if no category)
	 *
	 * @return int
	 */
	public function getCategory ()
	{
		return $this->category;
	}
	/**
	 * Sets the category (set to 0 for no specific category)
	 *
	 * @param int $val
	 */
	public function setCategory ($val)
	{
		if ($this->getCategory() === $val) {
			//prevent un-necessary setting
			return;
		}
		$this->touch(); //there are new pending changes
		$this->category = intval($val);
	}	
	
	/**
	 * Gets the order in which this order item is to be processed.  Note that the process order
	 * only applies to other process orders at the same "level" as this one.
	 *
	 * @return int
	 */
	public function getProcessOrder ()
	{
		if (isset($this->processOrder) && $this->processOrder) {
			return $this->processOrder;
		} else {
			return $this->defaultProcessOrder;
		}
	}
	
	/**
	 * Gets the geoPlanItem for this order item according to the order item's
	 * currently set price plan and category.
	 * 
	 * @return geoPlanItem
	 */
	public function getPlanItem ()
	{
		//NOTE: Function not final, to allow order items to customize this if they need.
		$pricePlan = $this->getPricePlan();
		$category = $this->getCategory();
		
		//just to be sure, validate the price plan ID
		if (!$pricePlan || !geoPlanItem::isValidPricePlan($pricePlan)) {
			$order = $this->getOrder();
			if (!$order) {
				//order not set for this item, so can't get user, so
				//set user ID to 0 so it get's default price plan for site
				$userId = 0;
			} else {
				$userId = $order->getBuyer();
			}
			$pricePlanId = geoPlanItem::getDefaultPricePlan($userId);
			if ($pricePlanId) {
				//we were able to retrieve the default price plan for this
				//user, so use it.
				$pricePlan = $pricePlanId;
			}
		}
		
		$planItem = geoPlanItem::getPlanItem($this->getType(), $pricePlan, $category);
		return $planItem;
	}
	
	/**
	 * Gets the specified item from the registry, or if item is one of the "main" items it gets
	 *  that instead.
	 *
	 * @param string $item
	 * @param mixed $default What to return if the item is not set.
	 * @return Mixed the specified item, or false if item is not found.
	 */
	public function get ($item, $default=false)
	{
		if (method_exists($this, 'get'.ucfirst($item))) {
			$methodName = 'get'.ucfirst($item);
			return $this->$methodName();
		}
		$this->initRegistry();
		return $this->registry->get($item, $default);
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
		return $this->registry->set($item, $value);
	}
	
	/**
	 * Gets the order item specified by the ID and returns the order item object (either
	 * this class, or a class specific for that order item that extends this class) for
	 * that order, or a new blank order item if the id is 0 or not a valid ID.
	 * 
	 * Should be called statically (like geoOrderItem::getOrderItem($id) )
	 *
	 * @param mixed $id If 0 or invalid ID, Object returned is for a new blank order item. If string, returns empty order item whos type matches string, if it is valid order item type
	 * @param bool $skipParentCheck If set to true, and id is int, it will not check to make sure the parent item (if set) exists.
	 * @return geoOrderItem
	 */
	final public static function getOrderItem ($id, $skipParentCheck = false)
	{
		if (!is_numeric($id)) {
			//treat it as a name for an order item...
			self::loadTypes();
			//trigger_error('DEBUG CART: order item is string: '.$id);
			if (array_key_exists($id,self::$orderTypes)){
				$item = new self::$orderTypes[$id]['class_name'];
				return $item;
			}
		}
		
		$id = intval($id); //id should be integer.
		
		if ($id == 0) {
			//error: not valid id.  TODO: turn this into throwing an exception
			return null;
		}
		if (!is_array(geoOrderItem::$orderItems)) {
			geoOrderItem::$orderItems = array();
		}
		
		//see if order exists in array of orders.
		
		foreach (geoOrderItem::$orderItems as $p_order => $items) {
			if (isset($items[$id])) {
				//found object, this item has already been retrieved this page load..
				return $items[$id];
			}
		}
		
		//this is different from others, because each order item type should over-load this class,
		//so this function can be used to get any order item type, and an object of that order item
		//type will be returned.
		$db = DataAccess::getInstance();
		//see what order type to use
		$sql = "SELECT `type`, `parent` FROM ".geoTables::order_item." WHERE `id`=? LIMIT 1";
		$result = $db->Execute($sql, array($id));
		if (!$result) {
			trigger_error('ERROR SQL: Error with query when attempting to get order type.  Error msg: '.$db->ErrorMsg());
			return null;
		}
		if ($result->RecordCount() == 0) {
			//none match
			return null;
		}
		geoOrderItem::loadTypes(); //load all available types
		
		$row = $result->FetchRow();
		
		$type = $row['type'];
		
		//sanity check, make sure if it is supposed to have a parent, the parent is alive and kicking
		if (!$skipParentCheck && $row['parent'] && !self::itemExists($row['parent'])) {
			//NOT good, parent could not be retrieved!  This is an orphaned order
			//item, it is a rare species indeed.
			trigger_error("ERROR CART TRANSACTION: Order item #$id supposed to have parent #{$row['parent']} but parent not found!  Not killing order item, but not putting it into action either.");
			return null;
		}
		
		//see if type is valid.
		if (isset(geoOrderItem::$orderTypes[$type])) {
			//it is valid!
			$className = geoOrderItem::$orderTypes[$type]['class_name'];
		} else {
			return null;
		}
		$orderItem = new $className ();
		$orderItem->unSerialize($id);
		
		
		
		return $orderItem;
	}
	
	
	/**
	 * Quick way to see if an item with a specified ID exists or not, without
	 * all the overhead of creating a new object and all that just to check
	 * to see if the item exists.
	 * 
	 * @param int $id
	 * @return bool
	 * @since Version 4.0.9
	 */
	public static function itemExists ($id)
	{
		$id = (int)$id;
		if (!$id) {
			//well duh it doesn't exist, ID's don't go down to 0 floor!
			return false;
		}
		if (isset(self::$_itemsExist[$id])) {
			//we already know it exists!
			return self::$_itemsExist[$id];
		}
		$db = DataAccess::getInstance();
		//see if it's in the database
		$sql = "SELECT `id` FROM ".geoTables::order_item." WHERE `id`=$id";
		$row = $db->GetRow($sql);
		if (isset($row['id']) && $row['id'] == $id) {
			//found it!
			self::$_itemsExist[$id] = true;
			return true;
		}
		//Oops, it does not exist!
		self::$_itemsExist[$id] = false;
		return false;
	}
	
	/**
	 * Gets an associative array of all the different order item types found in the system.
	 * 
	 * @param bool $onlyParents If true, will only return order item types that
	 *   do not have any parent item types themselves, meaning they are parent item
	 *   types.  {@since Version 7.2.0}
	 * @return array
	 */
	public static function getOrderItemTypes ($onlyParents = false)
	{
		self::loadTypes();
		
		if ($onlyParents) {
			//only return types with no parents
			$types = array();
			foreach (self::$orderTypes as $type => $details) {
				if (count($details['parents']) == 0) {
					$types[$type] = $details;
				}
			}
			return $types;
		}
		//return all types
		return self::$orderTypes;
	}
	
	/**
	 * Way to get a specific type of child already attached to a parent.  If you have a specific order item, and
	 * want to get a child of that order item of a specific type, this is the function to use.
	 * 
	 * If parent's type is the type attempting to find, will just return the parent.
	 * 
	 * If not found, returns null.
	 *
	 * @param geoOrderItem|int $parent Either the parent order item, the item already matching the type, or the id of the parent order item.
	 * @param string $item_type The item type attempting to find.
	 * @return geoOrderItem|null Returns the child item who's parent is the parent given, or null if none found.
	 */
	public static function getOrderItemFromParent ($parent, $item_type)
	{
		if (!is_object($parent)){
			$parent = intval($parent);
			if ($parent){
				$parent = geoOrderItem::getOrderItem($parent);
			}
			//if still not object
			if (!is_object($parent)){
				//no parent, no item to get.
				return null;
			}
		}
		
		if ($parent->getType() == $item_type){
			return ($parent);
		}
		if (!is_object($parent->getOrder())){
			return null;
		}
		//order keeps track of items attached to order
		$order = $parent->getOrder();
		$items = $order->getItem($item_type);
		if (is_array($items)){
			foreach ($items as $i => $val){
				if (is_object($val) && is_object($val->getParent())){
					$p = $val->getParent();
					if ($p->getId() == $parent->getId()){
						//parent is equal to parent, so whoohoo, we found it...
						return ($val);
					}
				}
			}
		}
		//so sad, item not found :(
		return null;
	}
	
	/**
	 * The "new" way to get parent types for a given order item, you should not
	 * call the getParentTypes static method directly.
	 * 
	 * @param string $itemType
	 * @return array The array of parent types, or an empty array if anything wrong
	 *   or if item has no parents (is a parent itself)
	 * @since Version 4.1.0
	 */
	final public static function getParentTypesFor ($itemType)
	{
		$itemType = trim($itemType);
		if (!$itemType) {
			//sanity check, return empty array since expects to always return array
			return array();
		}
		
		//make sure the types are loaded
		self::loadTypes();
		
		//make sure the requested item is real
		if (!isset(self::$orderTypes[$itemType])) {
			//can't tell what the parent types are if item type not known
			return array();
		}
		
		//all of the parent types will be set in there, if that key is set.
		return self::$orderTypes[$itemType]['parents'];
	}
	
	/**
	 * Add a parent type to the given "child" order item.  Note that a parent can
	 * only be added to a "child" order item that already has at least 1 parent
	 * order item.
	 * 
	 * @param string $childType The item type to add the parent type to
	 * @param string $parentType The parent type to be added to the list of parent types
	 * @return bool returns true if parent was added successfully, false otherwise.
	 * @since Version 4.1.0
	 */
	final public static function addParentTypeFor ($childType, $parentType)
	{
		$childType = trim($childType);
		$parentType = trim($parentType);
		
		if (!$childType || !$parentType || $childType == $parentType) {
			//are we sane today?
			return false;
		}
		
		self::loadTypes();
		if (!isset(self::$orderTypes[$childType],self::$orderTypes[$parentType])) {
			//the parent or the item is not valid
			return false;
		}
		
		if (count(self::$orderTypes[$childType]['parents']) == 0) {
			//no parents to start out with?  Block ability to specify a parent to
			//an item that wants to be a parent itself.
			return false;
		}
		//make sure type is not already in there
		if (in_array($parentType, self::$orderTypes[$childType]['parents'])) {
			//already added, return true signifying it is a parent now
			return true;
		}
		//add parent type
		self::$orderTypes[$childType]['parents'][] = $parentType;
		return true;
	}
	/**
	 * Initialize the registry for the order item
	 */
	public function initRegistry ()
	{
		if (isset($this->registry) && is_object($this->registry)) {
			return;
		}
		//Unserialize registry
		$this->registry = new geoRegistry();
		$this->registry->setName('order_item');
		$this->registry->setId($this->id);
		$this->registry->unSerialize();
	}
	
	/**
	 * Serializes the current order item (saves changes in the database, or creates new order item if the
	 *  id is not set.  If it is a new order, it will set the order item ID after it has been
	 *  inserted into the database.
	 * 
	 * Also automatically serializes any objects attached to it that are not already serialized. 
	 *
	 */
	public function serialize ()
	{
		$db = DataAccess::getInstance();
		if (!$this->_pendingChanges) {
			//no pending changes, no need to serialize.
			return;
		}
		//make sure data is correct data type to insert into database
		$id = intval($this->id);
		$status = trim($this->status);
		$status = (strlen($status) > 0)? $status: 'pending';//default to pending
		$order = (is_object($this->order))? intval($this->order->getId()): intval($this->order);
		$parent = (is_object($this->parent))? intval($this->parent->getId()): intval($this->parent);
		$type = $this->type.'';//make sure it's at least empty string and not null
		$price_plan = intval($this->pricePlan);
		if (!$price_plan) $price_plan = 1;
		$category = intval($this->category);
		$cost = floatval($this->cost);
		$created = (intval($this->created))? intval($this->created): geoUtil::time();
		$processOrder = intval($this->processOrder);
		if (!$processOrder && $this->defaultProcessOrder > 0) {
			$processOrder = intval($this->defaultProcessOrder);
		}
		
		$share_fees = geoAddon::getUtil('share_fees');
		if ($share_fees->active) {
			//get types of fees shared from db
			$fee_types_shared_array = $share_fees->getFeeTypesShared();
			if (in_array($type,$fee_types_shared_array)) {
				//set paid_out to 0 if status is active
				//unless paid_out is already 1
				if (($status == 'active') && ($cost > 0)) {
					if (isset($this->id) && $this->id > 0){
						$sql = "SELECT paid_out,paid_out_to FROM ".$db->geoTables->order_item." WHERE `id` = ? LIMIT 1";
						$paid_out_result = $db->GetRow($sql, array($id));
						if (($paid_out_result['paid_out'] == 0) || ($paid_out_result['paid_out'] == null)) {
							$paid_out = 0;
							$paid_out_to = intval($paid_out_result['paid_out_to']);
						} else {
							$paid_out = 1;
							$paid_out_to = intval($paid_out_result['paid_out_to']);
						}
					} else {
						$paid_out = 0;
						$paid_out_to = intval($this->paidOutTo);
					}
		
				} else {
					//leave as null but set paidoutto
					$paid_out = null;
					$paid_out_to = intval($this->paidOutTo);
				}
			} else {
				//leave value as null
				$paid_out = null;
				$paid_out_to = null;
			}
		} else {
			$paid_out = null;
			$paid_out_to = null;
		}		
		
		if (isset($this->id) && $this->id > 0){
			//update info
			$sql = "UPDATE ".$db->geoTables->order_item." SET `status` = ?, `order` = ?, `parent` = ?, `type` = ?, `price_plan` = ?, `category` = ?, `cost` = ?, `created` = ?, `process_order` = ?, `paid_out` = ?, `paid_out_to` = ? WHERE `id` = ? LIMIT 1";
			$stmt = $db->Prepare($sql);
			$query_data = array($status, $order, $parent, $type, $price_plan, $category, $cost, $created, $processOrder, $paid_out, $paid_out_to, $id);
			$result = $db->Execute($stmt, $query_data);
			if (!$result){
				trigger_error('ERROR SQL: Error with query when serialize object item to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
			if (!isset(self::$orderItems[$processOrder][$this->id])) {
				//process order has changed, find the old location and remove it, then add it to the new location
				foreach (self::$orderItems as $p_order => $items) {
					if (isset($items[$this->id])) {
						//found the old one, take it out
						unset (self::$orderItems[$p_order][$this->id]);
						break;
					}
				}
				//now add it to the new location
				geoOrderItem::$orderItems[$processOrder][$this->id] = $this;
			}
		} else {
			//Insert into DB
			$sql = "INSERT INTO ".$db->geoTables->order_item." (`id`, `status`, `order`, `parent`, `type`, `price_plan`, `category`, `cost`, `created`, `process_order`, `paid_out`, `paid_out_to`) 
					VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			
			$query_data = array($status, $order, $parent, $type, $price_plan, $category, $cost, $created, $processOrder, $paid_out, $paid_out_to);
			
			$result = $db->Execute($sql, $query_data);
			if (!$result){
				trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
				return false;
			}
			//set id
			$this->id = $db->Insert_Id();
			//add to order item registry
			geoOrderItem::$orderItems[$processOrder][$this->id] = $this;
		}
		//add it to the array of items that exist
		self::$_itemsExist[$this->id] = true;
		
		//Serialize order item registry
		if (is_object($this->registry)) {
			$this->registry->setId($this->id);
			$this->registry->setName('order_item');
			$this->registry->serialize();//serialize registry
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
		if (!$id && isset($this->id)) {
			//id set using setId()
			$id = $this->id;
		}
		if (!$id) {
			//can't unserialize without an id!
			return;
		}
		
		$db = DataAccess::getInstance();
		
		//Get the main data
		$sql = "SELECT * FROM ".$db->geoTables->order_item." WHERE `id`=$id LIMIT 1";
		$result = $db->Execute($sql);
		if (!$result) {
			trigger_error('ERROR SQL: ERror unserializing order: '.$db->ErrorMsg());
			return ;
		}
		if ($result->RecordCount() != 1) {
			//nothing by that id...
			//return empty orderitem.
			return null;
		}
		//reset all settings except for ID
		$settings = get_class_vars(__class__);
		$skip_settings = array ('id', 'orderItems', 'orderTypes');
		foreach ($settings as $var => $default_val) {
			if (!in_array($var, $skip_settings)) {
				$this->$var = $default_val;
			}
		}
		$translate = array (
		'price_plan' => 'pricePlan',
		'process_order' => 'processOrder',
		'paid_out_to' => 'paidOutTo'
		);
		$row = $result->FetchRow();
		foreach ($row as $key => $value) {
			if (!is_numeric($key)){
				//only process non-numeric rows
				if (isset($translate[$key])) {
					$t_key = $translate[$key];
					$this->$t_key = $value;
				} else {
					$this->$key = $value;
				}
			}
		}
		if (!$this->id) {
			//something went wrong with unserializing main values
			return ;
		}
		
		//add it to array of orders we have
		geoOrderItem::$orderItems[$this->processOrder][$this->id] = $this;
		
		//also add it ot the array if items that exist
		self::$_itemsExist[$this->id] = true;
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	
	/**
	 * Alias of geoOrderItem::serialize()
	 * 
	 */
	public function save()
	{
		return $this->serialize();
	}
	
	
	/**
	 * Loads order item types.
	 * 
	 * @param string $dirname Usually leave this blank, it will load system and
	 *   addon order item types on it's own
	 */
	private static function loadTypes ($dirname = '')
	{
		if (is_array(geoOrderItem::$orderTypes) && strlen($dirname) == 0) {
			//already loaded
			reset(geoOrderItem::$orderTypes);
			return ;
		}
		if (!is_array(geoOrderItem::$orderTypes)) {
			geoOrderItem::$orderTypes = array();
		}
		$firstCall = false;
		if (strlen($dirname) == 0) {
			//load addon's too
			$addon = true;
			include GEO_BASE_DIR.'get_common_vars.php';
			$addons = $addon->getOrderTypeAddons();
			trigger_error('DEBUG ORDER: Order item types from addon: <pre>'.print_r($addons,1).'</pre>');
			foreach ($addons as $addon_name) {
				if (strlen($addon_name) > 0) {
					geoOrderItem::loadTypes(ADDON_DIR.$addon_name.'/order_items/');
				}
			}
			//load the normal directory now
			$dirname = CLASSES_DIR.'order_items/';
			$firstCall = true;
		}
		
		//echo 'Adding dir: '.$dirname.'<br />';
		$dir = opendir($dirname);
		$skip = array('.','..');
		while ($filename = readdir($dir)) {
			if (in_array($filename, $skip)) {
				//skip this one
				continue;
			}
			if (strpos($filename,'.')===0) {
				//cannot start with .
				continue;
			}
			if (strpos($filename,'_')===0) {
				//cannot start with _
				continue;
			}
			if (substr($filename,-4)!=='.php') {
				//MUST end in .php
				continue;
			}
			if (!file_exists($dirname.$filename)) {
				//double check make sure file exists, which I guess it does not
				continue;
			}
			
			$name = str_replace('.php','',$filename);
			$class_name = $name.'OrderItem';
			if (strlen(trim($name)) == 0 || isset(geoOrderItem::$orderTypes[$name])){
				//already added one named this, block adding second one
				//to prevent fatal errors from the same class name twice
				continue;
			}
			//echo '<strong>Adding: '.$methodname.'.'.str_replace('.php','',$filename).'</strong><br />';
			require_once($dirname.$filename);
			if (class_exists($class_name)){
				//get parent types
				$parents = call_user_func(array($class_name,'getParentTypes'));
				if (!is_array($parents)) {
					//mis-behaving order item!  Bad order item!
					$parents = array ();
				}
				$item = new $class_name();
				
				$processOrder = $item->getProcessOrder();
				//add to array of types
				geoOrderItem::$orderTypes['ordered_items'][$processOrder][$name] = array (
					'class_name' => $class_name,
					'parents' => $parents,
					'process_order' => $processOrder
				);
				unset ($item);
			}
		}
		closedir($dir);
		if ($firstCall && count(geoOrderItem::$orderTypes['ordered_items']) > 0) {
			//move everything from the sorted location to the main directory, and make sure to do it in their order.
			$array = geoOrderItem::$orderTypes['ordered_items'];
			ksort($array);
			foreach ($array as $order_num) {
				foreach ($order_num as $name => $entry) {
					geoOrderItem::$orderTypes[$name] = $entry;
				}
			}
			//get rid of sorted version
			unset(geoOrderItem::$orderTypes['ordered_items']);
			//echo 'Result: <pre>'.print_r(geoOrderItem::$orderTypes,1).'</pre>';
		}
		if ($firstCall) {
			//give order items a standard place to make calls to "addParentTypeFor" (get it?)
			self::callUpdate('geoOrderItem_loadTypes_adoptions');
			
			//give order items a standard place to make calls to "unregisterItemType" (get it?)
			self::callUpdate('geoOrderItem_loadTypes_obituary');
		}
	}
	
	/**
	 * Re-orders the order item types by the given price plan and category settings
	 * as set in plan Item.  Note that this can be "expensive" where there are a lot
	 * of order item types, so use this sparingly.
	 *
	 * @param int $price_plan
	 * @param int $category
	 */
	final public static function reorderTypes ($price_plan, $category = 0)
	{
		$price_plan = intval($price_plan);
		$category = intval($category);
		if (!$price_plan) {
			return;
		}
		//first, make sure the types are loaded to begin with
		self::loadTypes();
		
		//now re-order them
		$ordered = array();
		foreach (self::$orderTypes as $name => $settings) {
			//TODO: if ability to change order in admin is ever fully implemented, use
			//the commented out method below to do so
			/*$planItem = geoPlanItem::getPlanItem($name,$price_plan,$category);
			$process_order = $planItem->getProcessOrder();
			if (!$process_order){
				$process_order = $settings['process_order'];
			}*/
			$process_order = $settings['process_order'];
			$ordered[$process_order][$name] = $settings;
		}
		//now put them back where they go.
		self::$orderTypes = array();
		ksort($ordered);
		foreach ($ordered as $order => $types) {
			foreach ($types as $name => $settings) {
				self::$orderTypes[$name] = $settings;
			}
		}
	}
	
	/**
	 * unregisters the specified order item type as a valid type for the
	 * remainder of the page load.  Perfect place to call this from is order
	 * item call to geoOrderItem_loadTypes as that happens directly after all
	 * the different types have been loaded, so one type can be removed right
	 * away.
	 * 
	 * @param string $itemType The item type to unregister.
	 * @return bool Returns true if removal of item type was successful, false otherwise.
	 * @since Version 4.1.0
	 */
	final public static function unregisterItemType ($itemType)
	{
		$itemType = trim($itemType);
		if (!$itemType || !isset(self::$orderTypes[$itemType])) {
			//could not remove
			return false;
		}
		unset (self::$orderTypes[$itemType]);
		return true;
	}
	
	/**
	 * Removes an order item as specified by ID, and also recursively
	 * removes everything attached to it. (this includes child orders items, etc).
	 * 
	 * If the order item no longer exists, but there are still "orphaned" child items attached
	 * to this order item, this function will kill those poor orphans that no longer have
	 * their parents.  This is a very morbid method, really.
	 *
	 * @param int $id
	 * @param bool $remove_attached if false, it will not remove stuff attached to this order.
	 */
	final public static function remove ($id, $remove_attached = true)
	{
		trigger_error('DEBUG CART: Removing order item! item: '.$id);
		$id = intval($id);
		if (!$id) {
			trigger_error('DEBUG ORDER CART: Invalid ID: '.$id.', remove of order item will not work.');
			return false;
		}
		$db = DataAccess::getInstance();
		if ($remove_attached) {
			//remove all stuff attached to this first, so they have access to parent if 
			//they need it to remove themselves.
			
			//first, remove all child order items
			$sql = 'SELECT `id` FROM '.geoTables::order_item.' WHERE `parent` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result) {
				trigger_error('ERROR SQL: Error trying to remove child order items for id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0) {
				while ($row = $result->FetchRow()) {
					if ($row['id'] && $row['id'] != $id){
						//stop infinite recursion ^
						//do recursively, to get all childrens childrens children...
						//otherwise we could have just done OR parent_id = ? but we want to be
						//able to travel the tree...
						if (!self::remove($row['id'])){
							//error when removing a child, do not proceed with removal.
							trigger_error('ERROR ORDER CART: Error, returning false, when removing item for id: '.$id.', a child order item ('.$row['id'].') returned false when removing it.');
							return false;
						}
					}
				}
			}
		}
		
		$item = geoOrderItem::getOrderItem($id);
		if (is_object($item)) {
			if (!$item->processRemove()) {
				//function returned false (or nothing), do not proceed with removal!
				trigger_error('DEBUG ORDER CART: When removing item for ID '.$id.', processRemove returned false or null, so not removing item.');
				return false;
			}
			unset($item);
		}
		//remove the main order item from the DB
		$sql = 'DELETE FROM '.geoTables::order_item.' WHERE `id` = ? LIMIT 1';
		$result = $db->Execute($sql, array($id));
		if (!$result) {
			trigger_error('ERROR SQL: Error trying to remove order for id: '.$id.' - error: '.$db->ErrorMsg());
			//do not hault on db error, keep going
		}
		foreach (self::$orderItems as $processOrder => $items) {
			if (isset($items[$id])) {
				//remove it from list of order items if it is there
				unset(self::$orderItems[$processOrder][$id]);
			}
		}
		
		if ($remove_attached) {
			//last, remove all registry for this order
			//Do NOT move this to happen earlier, it must be done last
			//or at the very least, after $item->removeProcess() is called.
			geoRegistry::remove('order_item', $id);
		}
		return true;
	}
	
	/**
	 * Works just like geoOrderItem::remove() except this will only affect order item data, not
	 * affect anything live like the listing itself, for example if a listing lasts more than a
	 * year the original order item may be removed for it.
	 *
	 * @param int $id
	 * @param bool $remove_attached if false, it will not remove stuff attached to this order.
	 */
	final public static function removeData ($id, $remove_attached = true)
	{
		trigger_error('DEBUG CART: Removing order item! item: '.$id);
		$id = intval($id);
		if (!$id) {
			trigger_error('DEBUG ORDER CART: Invalid ID: '.$id.', remove of order item will not work.');
			return false;
		}
		$db = DataAccess::getInstance();
		if ($remove_attached) {
			//remove all stuff attached to this first, so they have access to parent if 
			//they need it to remove themselves.
			
			//first, remove all child order items
			$sql = 'SELECT `id` FROM '.geoTables::order_item.' WHERE `parent` = ?';
			$result = $db->Execute($sql, array($id));
			if (!$result) {
				trigger_error('ERROR SQL: Error trying to remove child order items for id: '.$id.' - error: '.$db->ErrorMsg());
				//do not hault on db error, keep going
			}
			if ($result && $result->RecordCount() > 0) {
				while ($row = $result->FetchRow()) {
					if ($row['id'] && $row['id'] != $id){
						//stop infinite recursion ^
						//do recursively, to get all childrens childrens children...
						//otherwise we could have just done OR parent_id = ? but we want to be
						//able to travel the tree...
						if (!self::removeData($row['id'])){
							//error when removing a child, do not proceed with removal.
							trigger_error('ERROR ORDER: Error, returning false, when removing item for id: '.$id.', a child order item ('.$row['id'].') returned false when removing it.');
							return false;
						}
					}
				}
			}
		}
		
		$item = geoOrderItem::getOrderItem($id);
		if (is_object($item)) {
			if (!$item->processRemoveData()) {
				//function returned false (or nothing), do not proceed with removal!
				trigger_error('DEBUG ORDER: When removing item for ID '.$id.', processRemove returned false or null, so not removing item.');
				return false;
			}
			unset($item);
		}
		
		if ($remove_attached) {
			//first, remove all registry for this order
			geoRegistry::remove('order_item', $id);
		}
		//remove the main order item from the DB
		$sql = 'DELETE FROM '.geoTables::order_item.' WHERE `id` = ? LIMIT 1';
		$result = $db->Execute($sql, array($id));
		if (!$result) {
			trigger_error('ERROR SQL: Error trying to remove order for id: '.$id.' - error: '.$db->ErrorMsg());
			//do not hault on db error, keep going
		}
		foreach (self::$orderItems as $processOrder => $items) {
			if (isset($items[$id])) {
				//remove it from list of order items if it is there
				unset(self::$orderItems[$processOrder][$id]);
			}
		}
		
		return true;
	}
	
	/**
	 * Statically calls the specified display function for the order item specified, or all of
	 * the different order items with no parents if no specific order item is specified, and 
	 * seperates the returned responses from each of the order items by $separator
	 * 
	 * IMPORTANT: This leaves it up to each order item to make sure that order item is turned
	 * on and all that, and that input is cleaned, and that all "child" order items are called
	 * if needed.
	 * 
	 * This is similar to the addon method {@link geoAddon::triggerDisplay()} but this one is
	 * a little more simple.
	 *
	 * @param string $call_name method name to call
	 * @param mixed $vars vars that will be passed to the order item(s)
	 * @param string $separator What string to use as glue, or one of these special cases:
	 *  array: returns an array of arrays, each result is a non-empty array.
	 *  string_array: returns an array of strings, each result is a non-empty string
	 *  bool_true: if any return true, then return true.  otherwise return false. (strict match)
	 *  bool_false: if any return false, then return false.  otherwise return true. (strict match)
	 *  not_null: if any return a non-null (strict match) value, that value is returned.
	 * @param string|array $item_type Either a string of the specific item type to call, or an 
	 *  array of item types.
	 * @param bool $run_children If true, will also run children order items (items that have a parent)
	 * @return mixed Usually a string of each result seperated by separator, or if separator
	 *  is special case, returns whatever that special case is for.
	 */
	final public static function callDisplay ($call_name, $vars=null, $separator = '', $item_type = '', $run_children = false)
	{
		self::loadTypes();
		if (!is_array($item_type) && strlen($item_type)) {
			$item_type = array($item_type);
		}
		if (is_array($item_type)) {
			//item type is an array of item types to run
			//make sure all the item types specified are valid
			$items = array();
			foreach ($item_type as $type) {
				if (isset(self::$orderTypes[$type])) {
					$items[$type] = self::$orderTypes[$type];
				}
			}
			//when item types are specified, force run children to be on.
			$run_children = true;
		} else {
			//no order item type name specified, run for all order item types.
			//display type of call, expecting to return text
			$items = self::$orderTypes;
		}
		$parts = array();
		foreach ($items as $key => $item) {
			if (!isset(self::$orderTypes[$key])) {
				//item type must have been recently unregistered.
				continue;
			}
			
			if (method_exists(self::$orderTypes[$key]['class_name'],$call_name) && ($run_children || count(self::$orderTypes[$key]['parents']) == 0)) {
				//call it statically
				trigger_error('DEBUG CART: calling order item display, calling '.self::$orderTypes[$key]['class_name'].'::'.$call_name);
				$this_html = call_user_func(array(self::$orderTypes[$key]['class_name'],$call_name), $vars);
				switch ($separator) {
					case 'array':
						if (is_array($this_html) && count($this_html) > 0){
							//do a strict check for array return, this should
							//be an array return.
							$parts[$key] = $this_html;	
						}
						break;
						
					case 'string_array':
						if (strlen($this_html) > 0) {
							$parts[$key] = $this_html;	
						}
						break;
						
					case 'bool_true':
						//bool_true special case: if any results are true, return true
						if ($this_html === true) {
							return true;
						}
						break;
						
					case 'bool_false':
						//bool_false special case: if any results are true, return true
						if ($this_html === false) {
							return false;
						}
						break;
						
					case 'not_null':
						//not_null special case: if any results are something besides 
						//null (strict), return those results.
						if ($this_html !== null) {
							//is a non-null value, so return that.
							return $this_html;
						}
						
					default:
						//Normal, treat return as a string that will
						//be glued together with other returned strings later.
						if (strlen($this_html) > 0) {
							$parts[] = $this_html;
						}
						break;
				}
			}
		}
		$return = '';
		switch ($separator) {
			case 'array':
				//break ommited on purpose
			case 'string_array':
				$return = $parts;
				break;
				
			case 'bool_true':
				//none returned true, so return false
				$return = false;
				break;
				
			case 'bool_false':
				//none returned false, so return true
				$return = true;
				break;
				
			case 'not_null':
				//none returned a non-null value, so return null
				$return = null;
				break;
				
			default:
				//default (normal) case, treat separator as a separator
				if (count($parts) > 0) {
					$return .= implode($separator,$parts);
				}
				break;
		}
		return $return;
	}
	
	/**
	 * Calls the specified update function for the order item specified, or all of the order
	 * item types with no parents, if no specific order item type is specified.
	 * 
	 * IMPORTANT: This leaves it up to each order item to make sure that order item is being used and
	 *  turned on on and all that, and that input is cleaned, and that all "child" order items are 
	 *  called if needed.
	 *
	 * @param string $call_name
	 * @param mixed $vars
	 * @param string|array $item_type
	 * @param bool $run_children If true, run this call on children item types, not
	 *   just parent items
	 */
	final public static function callUpdate ($call_name, $vars=null, $item_type = '', $run_children = false)
	{
		self::loadTypes();
		if (is_array($item_type)) {
			//item type is an array of item types to run
			//make sure all the item types specified are valid
			$keys = array();
			foreach ($item_type as $type) {
				if (isset(self::$orderTypes[$type])) {
					$keys[$type] = $type;
				}
			}
			$run_children = true;
		} else if (strlen($item_type) > 0) {
			//item type name specified, only call the update function for item type specified
			$keys = array();
			if (isset(self::$orderTypes[$item_type])){
				$keys[] = $item_type;
			}
			$run_children = true;
		} else {
			//no item type name specified, run for all item types.
			//display type of call, expecting to return text
			$keys = array_keys(self::$orderTypes);
		}
		foreach ($keys as $key) {
			if (!isset(self::$orderTypes[$key])) {
				//item type must have been recently unregistered.
				continue;
			}
			if (method_exists(self::$orderTypes[$key]['class_name'],$call_name) && ($run_children || count(self::$orderTypes[$key]['parents']) == 0)){
				//call the update function statically
				//echo '<h2>Calling '.self::$orderTypes[$key]['class_name'].'::'.$call_name.'()</h2>';
				//trigger_error('DEBUG CART: calling update object '.self::$orderTypes[$key]['class_name'].' method '.$call_name);
				call_user_func(array(self::$orderTypes[$key]['class_name'],$call_name), $vars);
			}
		}
	}
	
	/**
	 * Use this to get all children types of the specified order item type, to be used
	 *  to allow recursively calling children.  Even if type name is not valid, will
	 *  still find valid children that declare parent to be what is specified.
	 *
	 * @param string $type_name
	 */
	final public static function getChildrenTypes ($type_name)
	{
		self::loadTypes();
		$keys = array_keys(self::$orderTypes);
		//echo 'Keys:::<pre>'.print_r($keys,1).'</pre>';
		$children = array();
		foreach ($keys as $key){
			if ($key != $type_name && isset(self::$orderTypes[$key]['parents']) && is_array(self::$orderTypes[$key]['parents']) && in_array($type_name,self::$orderTypes[$key]['parents'])){
				//this one is a child.
				$children[] = $key;
			}
		}
		return $children;
	}
	
	/**
	 * Use when this object, or one of it's child objects, has been changed, so that when it
	 * is serialized, it will know there are changes that need to be serialized.
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
		if (is_object($this->order)){
			$this->order->touch();
		}
		if (is_object($this->parent)){
			$this->parent->touch();
		}
	}
	
	/**
	 * Changes the status on an order item.  Built-in statuses are active, pending, and
	 * pending_alter.  Recommended to overwrite this function if the item needs to
	 * do anything at the time it is activated or deactivated.  Even if this is overloaded,
	 * it is recommended to still call the parent function to do common stuff.
	 *
	 * @param string $newStatus either "active", "pending", or "pending_alter"
	 * @param bool $sendEmailNotices If set to false, no e-mail notifications will be
	 *  sent, even if they are supposed to according to settings set in admin.
	 * @param bool $updateCategoryCount If set to true, the category count for this item will
	 *  be updated.  If false, it assumes whoever is calling this will do the updating all
	 *  at once for efficiency.
	 */
	public function processStatusChange ($newStatus, $sendEmailNotices = true, $updateCategoryCount = false)
	{
		if ($newStatus == $this->getStatus()){
			//the status hasn't actually changed, so nothing to do
			//but check with children (to cover a special case or two)
			$order = $this->getOrder();
			$all_items = $order->getItem();
			foreach ($all_items as $item){
				if (is_object($item) && is_object($item->getParent()) && $item->getParent()->getId() == $this->getId()){
					//this is child of mine, call it
					$item->processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
				}
			}
			
			return;
		}
		//remember to set the status on the item!
		$this->setStatus($newStatus);
		//make sure changes to item are saved, in case there are errors that happen after this time
		$this->save();
		
		//call any children
		$order = $this->getOrder();
		$all_items = $order->getItem();
		
		foreach ($all_items as $item){
			if (is_object($item) && is_object($item->getParent()) && $item->getParent()->getId() == $this->getId()){
				//this is child of mine, call it
				$item->processStatusChange($newStatus, $sendEmailNotices, $updateCategoryCount);
			}
		}
	}
	
	/**
	 * Overload this function if anything needs to be done at the time this order item is being
	 * removed from the system. Note that the static function remove() does all the normal
	 * back-end stuff like removing the registry and the order item from the DB, this function
	 * is primarily for any special case stuff, like removing a listing or deleting image files.
	 *
	 * @return bool true to finish removing the item, or false to force item to not be removed.
	 */
	public function processRemove ()
	{
		return true;
	}
	
	/**
	 * Overload this function if anything extra needs to be done at the time the order item's data
	 * is being removed.  This should NOT affect anything "live", all that is happening here is
	 * the order item's data is being removed because it is getting old and needs to be cleared
	 * out to make room.
	 * 
	 * 
	 * @return bool true to finish removing the item's data, or false to force the item to stay put.
	 */
	public function processRemoveData ()
	{
		return true;
	}
	
	/**
	 * Use this to display info about each main item, in the e-mail sent saying the
	 * order has been approved.  To keep consistent, use this format:
	 * 
	 * ITEM TITLE [STATUS] - $COST
	 * 
	 * (cost including sub-items of this)
	 * 
	 * @param string $overrideTitle Can be used by individual order item to let
	 *  super class do most of the work, but allow order item to specify the title,
	 *  in the order item would return parent::geoOrder_processStatusChange_emailItemInfo('my title')
	 * @return string
	 */
	public function geoOrder_processStatusChange_emailItemInfo ($overrideTitle = '')
	{
		$details = $this->getDisplayDetails(false, true);
		$cost = $details['total'];
		//figure out what to say for status
		$db = DataAccess::getInstance();
		$msgs = $db->get_text(true, 10207);
		if ($this->getStatus() == 'active') {
			$status = $msgs[500721];
		} else {
			$status = $msgs[500722];
		}
		$title = ($overrideTitle)? $overrideTitle : $details['title'];
		if (!$title && !$cost) {
			//no cost and no title, probably shouldn't display this one
			return '';
		}
		trigger_error('DEBUG EMAIL: title is '.$title);
		return "$title $status - ".geoString::displayPrice($cost);
		
	}
	
	/**
	 * Whether or not the current session is anonymous or not.  Note that is is
	 * IF current session is anonymous, NOT if anonymous is allowed, for that see
	 * {@link _templateOrderItem::anonymousAllowed()}
	 * 
	 * @return bool
	 */
	final public static function isAnonymous ()
	{
		return (geoCart::getInstance()->user_data['id'] == 0);
	}
	
	/**
	 * If anonymous not allowed for the main type, and current session is
	 * anonymous, shows the login form and returns true to allow caller to
	 * do any cleanup and exit.  Note that if this returns true, the page has
	 * already been displayed and everything, the only thing left to do is
	 * any special cleanup (such as removing an item from the cart if needed),
	 * and then exit.
	 * 
	 * If this returns false, that means everything is OK and it is OK to proceed.
	 * 
	 * So, TRUE = non-anon enforment required, need to exit, and FALSE = ok to proceed.
	 *
	 * @param string|null $itemType If set, will check item specified for anonymousAllowed().
	 *  If null or empty string, will assume anonymous is NOT allowed.
	 * @param string $loginFormParam IF user needs to log in and login form is called,
	 *  this will be passed as the 4th param to the {@link Auth::login_form()} call.
	 * @return bool FALSE: no enforcement needed, it is OK to proceed. 
	 *  TRUE: user not logged in, and anonymous is not allowed, so we just did some
	 *  enforcing by displaying the login page.  Now it is your turn to EXIT.
	 */
	public static function enforceAnonymous ($itemType = null, $loginFormParam = 'a*is*cart')
	{
		trigger_error('DEBUG ANON: enforce anonymous top');
		
		$anonAllowed = false;
		if ($itemType) {
			$anonAllowed = self::callDisplay('anonymousAllowed',null,'bool_true',$itemType);
		}
		
		if (self::isAnonymous() && !$anonAllowed) {
			if (defined('IN_ADMIN')) {
				//Don't do anything here, just let caller figure it out
				return true;
			} else {
				//this is anonymous session and anonymous not allowed
				trigger_error('DEBUG ANON: User not logged in, and anonymous not allowed, so showing
				 login form and returning true.');
				
				//show login page
				include_once(CLASSES_DIR."authenticate_class.php");
				$auth = new Auth(0,DataAccess::getInstance()->getLanguage(),geoPC::getInstance());
				$auth->login_form(0, "", "", $loginFormParam);
				
				//return false, meaning some enforcing was just done.
				return true;
			}
		}
		trigger_error('DEBUG ANON: User is logged in, OR not but anonymous is allowed, so 
		no enforcing needed, returning false.');
		return false;
	}
	
	/**
	 * Optional, used in admin to show which upgrades are attached to a Listing Renewal item
	 * (superclass fallback, so things don't break if a new upgrade type doesn't have a friendlyName)
	 *
	 * @return String "user-friendly" name of this item
	 */
	public function friendlyName ()
	{
		return 'Unknown Type';
	}
	
	/**
	 * Optional, this is used as an easy way to identify items that are Listing
	 * parent items (i.e. classified and auction items).
	 * 
	 * This is the superclass, and returns false,
	 * items that directly represent listings need a copy of this function that
	 * returns true.
	 * 
	 * @return bool
	 */
	public function isListingItem ()
	{
		return false;
	}
	
	/**
	 * Optional, if method not implemented in individual order item the method
	 * in this superclass will return false by default, so order items that
	 * are never parent items in a recurring order do not need to implement
	 * this method.  Note that if there are no payment gateways that handle
	 * recurring billing, this will not matter much.  Also if an item is recurring,
	 * it should not only return true for this method, but also return true
	 * for {@link iOrderItem::geoCart_initItem_forceOutsideCart(}}.
	 * 
	 * @return bool return true if this order item is recurring, false otherwise.
	 * @since Version 4.1.0
	 */
	public function isRecurring ()
	{
		return false;
	}
	/**
	 * Optional, but required if isRecurring() returns true, otherwise it will
	 * default to 0 (basically recurring being off).  This needs to return the
	 * interval for the recurring billing, in seconds.
	 * 
	 * @return int The recurring interval in seconds.
	 * @since Version 4.1.0
	 */
	public function getRecurringInterval ()
	{
		return 0;
	}
	/**
	 * Optional, but required if isRecurring() returns true, otherwise it will
	 * default to 0 (basically recurring being off).  This needs to return the
	 * price for the recurring billing.
	 * 
	 * @return int The recurring interval in seconds.
	 * @since Version 4.1.0
	 */
	public function getRecurringPrice ()
	{
		return 0.00;
	}
	
	/**
	 * Optional, but required if isRecurring() returns true, otherwise the recurring
	 * charge will have no dscription in the payment gateway.
	 * 
	 * @return string
	 */
	public function getRecurringDescription ()
	{
		return 'Recurring Item';
	}
	
	/**
	 * Optional, used if isRecurring() returns true, if order item does not implement
	 * the current time will always be returned.
	 * 
	 * @return int Unix timestamp for when recurring start date should be.
	 */
	public function getRecurringStartDate ()
	{
		return geoUtil::time();
	}
	
	/**
	 * Optional, should return non-zero for child items such as Listing Extras that can add their cost to a recurring item (instead of creating a recurring cost on their own)
	 * @return float the added cost
	 */
	public function getRecurringSubCost()
	{
		//NOTE: this is for things that add their cost to the total recurring price instead of recurring on their own
		//typically, Listing Extras return their price for one subscription period, while other items return 0
		return 0.00;
	}
	
	/**
	 * Required.
	 * Used: By payment gateways to see what types of items are in the order affecting price.
	 * 
	 * Note that for backwards compatibility with older order items, this is implemented
	 * in the parent geoOrderItem class, so if you leave it off it will "work".
	 * It is still highly recommended to implement anyways in each order item, 
	 * simply because it's role will be much more important when the ability to
	 * use the cart between users is implemented down the road.  Most would use
	 * the implementation from the template order item.
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
	 * @since Version 6.0.0
	 */
	public function getCostDetails ()
	{
		//Return false to mean there is no cost... This is implemented here
		//to maintain backwards compatibility with older custom order items.
		return false;
	}
	
	/**
	 * Required, should return true or false, whether or not to display
	 * this order item in the admin.  Most will return true, only special
	 * cases, like "sub total" should return false.
	 *
	 * @return bool True if this item should be displayed in the admin, false 
	 *  otherwise.
	 */
	abstract public function displayInAdmin();
	
	/**
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
	abstract public function getDisplayDetails($inCart);
}

/**
 * You cannot have abstract static functions, so instead need to have an interface to force the given
 * static functions to be defined.
 * 
 * @package System
 * @since Version 4.0.0
 */
interface iOrderItem
{
	/**
	 * Required, even if it just returns an empty array.
	 * 
	 * @param bool $allPossible If true, should initialize ALL steps that are possible
	 *   considering the current "site-wide" settings.
	 */
	public static function geoCart_initSteps($allPossible=false);
	
	/**
	 * Whether or not a seperate cart can be initialized just for this order item or not.
	 * 
	 * @return boolean True to force creating "parellel" cart just for this item, if another cart is already started,
	 *  false otherwise.
	 */
	public static function geoCart_initItem_forceOutsideCart();
	
	/**
	 * Used to determine whether or not to display the other details step.  Should also check the
	 * children items of the item.
	 * 
	 * @return bool true to add the other_details step, false otherwise.
	 */
	public static function geoCart_initSteps_addOtherDetails();
	
	/**
	 * Used from different locations, this should return an array of the different order items that this
	 * order item is a child of.  If this is a main order item type, it should return an empty array.
	 * 
	 * @return array
	 */
	public static function getParentTypes();
	
}