<?php
//PlanItem.class.php
/**
 * This holds the geoPlanItem class.
 * 
 * @package System
 * @since Version 4.0.0
 */


/**
 * A container to hold settings for a particular order item type, according to
 * price plan ID, and optionally category ID for category specific settings.
 * 
 * This class is not designed to be extended (like the geoOrderItem class is),
 * but rather to just be used to save settings for order items on a price plan 
 * by price plan basis.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoPlanItem
{
	/**
	 * Internal use.
	 * @internal
	 */
	private $_orderItem;
	/**
	 * Internal use.
	 * @internal
	 */
	private $_pricePlan;
	/**
	 * Internal use.
	 * @internal
	 */
	private $_category;
	/**
	 * Internal use.
	 * @internal
	 */
	private $_processOrder;
	/**
	 * Internal use.
	 * @internal
	 */
	private $_needAdminApproval;
	/**
	 * Internal use.
	 * @internal
	 */
	private $_enabled;
	/**
	 * Internal use.
	 * @internal
	 */
	private $_pendingChanges;
	
	/**
	 * Internal use.
	 * @internal
	 */
	private $_registry;
	
	/**
	 * Internal use.
	 * @internal
	 */
	private static $_planItems;
	
	/**
	 * Local cache for this page load, of values already retrieved by _getCatPricePlan()
	 * @var array
	 */
	private static $_catPricePlans = array();
	
	/**
	 * Constructor, private so that it forces using geoPlanItem::getPlanItem()
	 * 
	 */
	private function __construct()
	{
		
	}
	
	/**
	 * Gets the order item
	 *
	 * @return string
	 */
	public function getOrderItem()
	{
		return $this->_orderItem;
	}
	
	/**
	 * Sets the order item type for this plan item, for instance 'classified'.
	 * 
	 * @param string $item The item type to set.
	 */
	public function setOrderItem ($item)
	{
		if (is_object($item)) {
			//Go ahead and let them pass in an order item object.
			$item = $item->getType();
		}
		$item = trim($item);
		if (strlen($item) == 0) {
			//oops
			return false;
		}
		if ($item === $this->_orderItem) {
			//nothing changed...
			return;
		}
		
		$this->_orderItem = $item;
		$this->touch();
	}
	
	/**
	 * Gets the price plan ID currently set.
	 * @return int
	 */
	public function getPricePlan ()
	{
		return $this->_pricePlan;
	}
	
	/**
	 * Sets the price plan id for the plan item.  Does NOT validate the price plan ID, so
	 * that needs to be done prior to calling this.
	 * 
	 * @param int $price_plan_id
	 */
	public function setPricePlan($price_plan_id)
	{
		$price_plan_id = intval($price_plan_id);
		if ($this->_pricePlan === $price_plan_id) {
			//nothing changed...
			return;
		}
		$this->_pricePlan = $price_plan_id;
		$this->touch();
	}
	
	/**
	 * Gets the category ID for this plan item, or 0 if not category specific.
	 * @return int
	 */
	public function getCategory()
	{
		return $this->_category;
	}
	
	/**
	 * Sets the category ID for this plan item.
	 * 
	 * @param int $category_id
	 */
	public function setCategory($category_id)
	{
		$category_id = intval($category_id);
		if ($this->_category === $category_id) {
			//nothing changed...
			return;
		}
		$this->_category = $category_id;
		$this->touch();
	}
	
	/**
	 * Get the "process order" for the current plan item.
	 * 
	 * Note that a process
	 * order, is the "sequential" order in which things are processed. 
	 * @return int
	 */
	public function getProcessOrder ()
	{
		return $this->_processOrder;
	}
	
	/**
	 * Sets the "process order" for the current plan item.
	 * 
	 * Note that a process
	 * order, is the "sequential" order in which things are processed. 
	 * 
	 * @param int $process_order
	 */
	public function setProcessOrder($process_order)
	{
		$process_order = intval($process_order);
		if ($this->_processOrder === $process_order) {
			//nothing to change...
			return;
		}
		$this->_processOrder = $process_order;
		$this->touch();
	}
	
	/**
	 * Does this plan need admin approval to activate items of this type?
	 * @return bool
	 */
	public function getNeedAdminApproval()
	{
		return $this->_needAdminApproval;
	}
	
	/**
	 * Alias of {@link geoPlanItem::getNeedAdminApproval()}
	 * @return bool
	 */
	public function needAdminApproval()
	{
		return $this->_needAdminApproval;
	}
	
	/**
	 * Sets whether or not the plan item needs admin approval.
	 * 
	 * @param bool $need_admin_approval
	 */
	public function setNeedAdminApproval ($need_admin_approval)
	{
		$need_admin_approval = ($need_admin_approval)? true: false;
		if ($this->_needAdminApproval === $need_admin_approval) {
			//no changes needed...
			return;
		}
		$this->_needAdminApproval = $need_admin_approval;
		$this->touch();
	}
	
	/**
	 * Whether this plan item enabled or not.
	 * 
	 * Note that this is not currently used by the system, but in a future release
	 * we plan to add the ability to enable/disable order item types, for instance
	 * to allow admin to "disallow" placing a new classified by disabling the
	 * classified plan item.
	 * 
	 * @return bool
	 */
	public function getEnabled ()
	{
		return $this->_enabled;
	}
	
	/**
	 * Alias of {@link geoPlanItem::getEnabled()}
	 * @return bool
	 */
	public function isEnabled ()
	{
		return $this->_enabled;
	}
	
	/**
	 * Sets whether this plan item is enabled or not.
	 * 
	 * Note that the enabled
	 * setting "works" as in you can enable/disable a plan item in the code,
	 * but the setting is not currently used for anything.  In a future release
	 * we plan to add the ability to enable/disable each plan item as an easy
	 * way to turn on/off the ability for users to use that order item type.
	 * 
	 * @param bool $enabled Whether it is enabled or not.
	 */
	public function setEnabled ($enabled)
	{
		$enabled = ($enabled)? true: false;
		$this->_enabled = $enabled;
		$this->touch();
	}
	
	/**
	 * Initializes the registry, mostly used internally.
	 * 
	 */
	public function initRegistry ()
	{
		if (isset($this->_registry) && is_object($this->_registry)) {
			//already done
			return;
		}
		//Unserialize registry
		$this->_registry = new geoRegistry();
		$this->_registry->setId("{$this->_orderItem}:{$this->_pricePlan}:{$this->_category}");
		$this->_registry->setName('plan_item');
		$this->_registry->unSerialize();
	}
	
	/**
	 * Serializes the current plan item (saves the settings in the DB).
	 * 
	 * Also automatically serializes any objects attached to it that are not already serialized,
	 * like the plan item's registry.
	 *
	 */
	public function serialize ()
	{
		if (!$this->_pendingChanges){
			//no pending changes, no need to serialize.
			return;
		}
		
		$db = DataAccess::getInstance();
		
		//Relies on primary key being set properly, which should be
		//PRIMARY KEY (`order_item`,`price_plan`,`category`)
		$sql = "REPLACE INTO ".geoTables::plan_item." (`order_item`,"
		     . " `price_plan`, `category`, `process_order`,"
		     . " `need_admin_approval`, `enabled`) VALUES (?, ?, ?, ?, ?, ?)";
		//make sure data is correct data type to insert into database
		$sql_data = array();
		$sql_data[] = '' . $this->_orderItem; //needs to be string
		$sql_data[] = intval($this->_pricePlan); //needs to be int
		$sql_data[] = intval($this->_category); //needs to be int
		$sql_data[] = intval($this->_processOrder); //needs to be int
		$sql_data[] = ($this->_needAdminApproval)? 1: 0; //needs to be 1 or 0
		$sql_data[] = ($this->_enabled)? 1: 0; //needs to be 1 or 0
		
		$result = $db->Execute($sql, $sql_data);
		
		if (!$result){
			trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
			return false;
		}
		
		//add to plan item registry
		geoPlanItem::$_planItems[$this->_orderItem][$this->_pricePlan][$this->_category] = $this;
		
		//Serialize plan item registry
		if (isset($this->_registry) && is_object($this->_registry)){
			$this->_registry->setId("{$this->_orderItem}:{$this->_pricePlan}:{$this->_category}");
			$this->_registry->setName('plan_item');
			$this->_registry->serialize();//serialize registry
		}
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
	}
	
	/**
	 * Unserializes the plan item, either using vars if they are passed in, or using already set object params if
	 * variables are not passed.
	 * 
	 * @param string $order_item_type
	 * @param int $price_plan
	 * @param int $category_id
	 * @param bool $force_category If true, will force it to get the category
	 *  specific setting for the plan item.  Otherwise, it will only get the
	 *  category specific settings if there are any category specific settings
	 *  for the category specified, if there are no cat specific settings then
	 *  the category in the plan item will be set to 0.
	 */
	public function unSerialize($order_item_type = '', $price_plan = 0, $category_id = 0, $force_category = false)
	{
		$price_plan = intval($price_plan);
		$category_id = intval($category_id);
		if (!$price_plan && isset($this->_pricePlan)){
			//id set using setId()
			$price_plan = $this->_pricePlan;
		}
		
		if (!$order_item_type && isset($this->_orderItem)) {
			$order_item_type = $this->_orderItem;
		}
		if (!$category_id && isset($this->_category)) {
			$category_id = $this->_category;
		}
		
		$db = DataAccess::getInstance();
		
		//Get the main data
		
		$cat_extra = ($category_id == 0 || $force_category)? '': ' OR `category` = 0';
		$sql = "SELECT `process_order`, `need_admin_approval`, `enabled`, `category` FROM ".geoTables::plan_item." WHERE `order_item` = ? AND `price_plan` = ? AND (`category` = ?$cat_extra) ORDER BY `CATEGORY` DESC LIMIT 1";
		$row = $db->GetRow($sql, array($order_item_type, $price_plan, $category_id));
		if ($row === false){
			trigger_error('ERROR SQL: ERror unserializing plan item: '.$db->ErrorMsg());
			return ;
		}
		$this->_orderItem = $order_item_type;
		$this->_pricePlan = $price_plan;
		$this->_category = (isset($row['category']))? $row['category']: $category_id;
		$this->_processOrder = (isset($row['process_order']))? $row['process_order']: 0;
		//default to false if not found in DB
		$this->_needAdminApproval = (isset($row['need_admin_approval']) && $row['need_admin_approval'])? true: false;
		//default to true if not found in DB
		$this->_enabled = (!isset($row['enabled']) || $row['enabled'])? true: false;
		
		//add it to array of orders we have
		geoPlanItem::$_planItems[$this->_orderItem][$this->_pricePlan][$this->_category] = $this;
		
		//we just unserialized, so there are no longer pending changes.
		if (count($row) > 0) {
			$this->_pendingChanges = false;
		} else {
			//did not get it from the db
			$this->touch();
		}
	}
	
	/**
	 * Alias of geoPlanItem::serialize() - see that method for details.
	 * 
	 */
	public function save()
	{
		return $this->serialize();
	}
	
	/**
	 * Used in admin panel to determine if the given price plan has category
	 * specific pricing for the given category.
	 * 
	 * @param int $pricePlanId
	 * @param int $category
	 * @return bool
	 * @since Version 5.0.0
	 */
	public static function useCatSpecificPlan ($pricePlanId, $category)
	{
		return ($category == self::_getCatPricePlan($pricePlanId, $category));
	}
	
	/**
	 * Used locally to get the first category that has cat specific pricing for the
	 * given category and price plan.
	 * 
	 * @param int $pricePlanId
	 * @param int $category
	 * @return int The category ID of a category with cat specific pricing, or 0 if none.
	 */
	private static function _getCatPricePlan ($pricePlanId, $category)
	{
		$pricePlanId = intval($pricePlanId);
		$category = intval($category);
		
		if (!$pricePlanId || !$category) {
			return 0;
		}
		if (isset(self::$_catPricePlans[$pricePlanId][$category])) {
			return self::$_catPricePlans[$pricePlanId][$category];
		}
		
		$db = DataAccess::getInstance();
		
		$pricePlan = $db->GetRow("SELECT * FROM ".geoTables::price_plans_table." WHERE `price_plan_id` = ? LIMIT 1", array($pricePlanId));
		if (!$pricePlan || $pricePlan['type_of_billing'] != 1 || !(geoPC::is_ent() || geoPC::is_premier())) {
			self::$_catPricePlans[$pricePlanId][$category] = 0;
			return 0;
		}
		
		$stmt_cat_plan = $db->Prepare("SELECT count(price_plan_id) as count FROM ".geoTables::price_plans_categories_table." WHERE `price_plan_id` = ? AND `category_id` = ?");
		$stmt_get_parent = $db->Prepare("SELECT `parent_id` FROM ".geoTables::categories_table." WHERE `category_id` = ? LIMIT 1");
		$origCat = $category;
		do {
			$show_price_plan = $db->GetRow($stmt_cat_plan, array($pricePlanId,$category));
			if ($show_price_plan === false) {
				trigger_error('ERROR CART SQL: Error msg: '.$this->db->ErrorMsg());
				return false;
			}
			
			if ($show_price_plan['count'] == 1) {
				//found the category price plan to use..
				self::$_catPricePlans[$pricePlanId][$origCat] = $category;
				return $category;
			}
			
			$show_price_plan = 0;
			
			//get category parent
			$show_category = $db->GetRow($stmt_get_parent, array($category));
			if ($show_category === false) {
				trigger_error('ERROR CART SQL: Sql: '.$sql.' Error msg: '.$db->ErrorMsg());
				return false;
			}
			
			if (isset($show_category['parent_id'])) {
				//parent category found
				$category = intval($show_category['parent_id']);
				continue;
			}
			trigger_error('DEBUG CART: Unable to get category price plan, category not found.');
			return false;
			
			//check all the way to the main category
		} while ($category != 0);
		self::$_catPricePlans[$pricePlanId][$origCat] = 0;
		return 0;
	}
	
	/**
	 * Gets a plan item to get settings and such, depending on the given item type, price plan, and category id.
	 *
	 * @param string $item_type
	 * @param int $price_plan_id
	 * @param int $category_id
	 * @param bool $forceCat If true, will enforce using the given category ID even if no such category is
	 *  already existing for cat specific pricing.  (used by admin to create new cat specific pricing)
	 * @return geoPlanItem
	 */
	public static function getPlanItem ($item_type, $price_plan_id, $category_id = 0, $forceCat = false)
	{
		$item_type = trim($item_type);
		if (strlen($item_type) == 0) {
			$item_type = '0';
		}
		$price_plan_id = intval($price_plan_id);
		$category_id = intval(((geoPC::is_ent() || geoPC::is_premier())? $category_id: 0));
		
		if (!$forceCat && $category_id) {
			$category_id = self::_getCatPricePlan($price_plan_id, $category_id);
		}
		if (!isset(self::$_planItems[$item_type][$price_plan_id][$category_id]) || !is_object(self::$_planItems[$item_type][$price_plan_id][$category_id])) {
			$item = new geoPlanItem;
			$item->_pricePlan = $price_plan_id;
			$item->_orderItem = $item_type;
			$item->_category = $category_id;
			$item->unSerialize($item_type, $price_plan_id, $category_id, $forceCat);
			self::$_planItems[$item_type][$price_plan_id][$category_id] = $item;
			//echo "self::\$_planItems[$item_type][$price_plan_id][$category_id]<br />";
		}
		
		return self::$_planItems[$item_type][$price_plan_id][$category_id];
	}
	
	/**
	 * Gets an array of all the plan items for the given price plan and category.
	 * 
	 * @param int $price_plan_id
	 * @param int $category_id
	 * @return array|bool an array of plan item objects, or false if failure retrieving.
	 */
	public static function getPlanItems($price_plan_id, $category_id = 0)
	{
		$price_plan_id = intval($price_plan_id);
		$category_id = intval($category_id);
		
		$db = DataAccess::getInstance();
		$sql = "SELECT `order_item`, `price_plan`,`category` FROM ".geoTables::plan_item." WHERE `price_plan` = ? AND `category` = ? ORDER BY `process_order`";
		$all = $db->GetAll($sql,array($price_plan_id,$category_id));
		if ($all === false) {
			trigger_error('ERROR SQL: Sql: '.$sql.' Error Msg: '.$db->ErrorMsg());
			return false;
		}
		$return = array();
		foreach ($all as $row) {
			$return [] = self::getPlanItem($row['order_item'],$row['price_plan'],$row['category'], true);
		}
		return $return;
	}
	
	/**
	 * Gets the specified item from the registry, or if item is one of the "main" items it gets
	 * that instead.
	 *
	 * @param string $item
	 * @param mixed $default What to return if the item is not set.
	 * @return Mixed the specified item, or the value of $default if the setting is not set.
	 */
	public function get ($item, $default = false)
	{
		$this->initRegistry();
		if (method_exists($this, 'get'.ucfirst($item))){
			$methodName = 'get'.ucfirst($item);
			return $this->$methodName();
		}
		
		return $this->_registry->get($item, $default);
	}
	
	/**
	 * Sets the given item to the given value.  If item is one of built-in items, it sets that instead
	 * of something from the registry.
	 *
	 * @param string $item
	 * @param mixed $value
	 */
	public function set($item, $value)
	{
		$this->initRegistry();
		
		if (method_exists($this, 'set'.ucfirst($item))){
			$methodName = 'set'.ucfirst($item);
			return $this->$methodName($value);
		}
		
		$this->touch(); //there are now pending changes
		
		return $this->_registry->set($item, $value);
	}
	
	/**
	 * Used in admin, to remove all current price plan items for the given price plan and category.
	 * 
	 * @param int $price_plan
	 * @param int $category
	 * @return bool true on success, false on error.
	 */
	public static function deletePlanItems ($price_plan, $category = 0)
	{
		$price_plan = intval($price_plan);
		$category = intval($category);
		
		if (!$price_plan) {
			return false;
		}
		$db = DataAccess::getInstance();
		//get all the plan items that match that price plan and category.
		$plan_items = self::getPlanItems($price_plan,$category);
		foreach ($plan_items as $item) {
			if ($item->getCategory() != $category) {
				//we are only removing items strictly for this category,
				//so need to make sure it didn't get the parent category item
				continue;
			}
			$sql = "DELETE FROM ".geoTables::plan_item." WHERE `order_item` = ? AND `price_plan` = ? AND `category` = ? LIMIT 1";
			$db->Execute($sql, array($item->getOrderItem().'', $price_plan, $category));
			
			//also delete all the registry items
			$sql = "DELETE FROM ".geoTables::plan_item_registry." WHERE `plan_item` = ?";
			$db->Execute($sql, array("{$item->getOrderItem()}:{$price_plan}:{$category}"));
		}
		
		return true;
	}
	
	/**
	 * For copying plan item from one category to another.
	 * 
	 * @param int $price_plan
	 * @param int $from_category
	 * @param int $to_category
	 * @return boolean True if successful, false otherwise
	 */
	public static function copyPlanItems ($price_plan, $from_category, $to_category)
	{
		$db = DataAccess::getInstance();
		
		$price_plan = (int)$price_plan;
		$from_category = (int)$from_category;
		$to_category = (int)$to_category;
		
		if (!$price_plan || !$from_category || !$to_category) {
			//not valid, this is only for copying plan item from one category to another
			return false;
		}
		
		$plans = $db->Execute("SELECT * FROM ".geoTables::plan_item." WHERE `category`=? AND `price_plan`=?",
				array($from_category, $price_plan));
		
		if (!$plans || !$plans->RecordCount()) {
			//nothing to do, none to copy
			return true;
		}
		//prevent duplicates
		$db->Execute("DELETE FROM ".geoTables::plan_item." WHERE `price_plan`=? AND `category`=?", array($price_plan, $to_category));
		$db->Execute("DELETE FROM ".geoTables::plan_item_registry." WHERE `plan_item` LIKE '%:{$price_plan}:{$to_category}'");
		foreach ($plans as $plan) {
			$query_data = array ($plan['order_item'],$price_plan,$to_category,$plan['process_order'],$plan['need_admin_approval'],
				$plan['enabled']);
			$db->Execute("INSERT INTO ".geoTables::plan_item." SET `order_item`=?, `price_plan`=?,
					`category`=?, `process_order`=?, `need_admin_approval`=?, `enabled`=?",
					$query_data);
		}
		
		$regs = $db->Execute("SELECT * FROM ".geoTables::plan_item_registry." WHERE `plan_item` LIKE '%:{$price_plan}:{$to_category}'");
		foreach ($regs as $reg) {
			$plan_item = explode(':',$reg['plan_item']);
			$plan_item = "{$plan_item[0]}:$price_plan:$to_category";
			$query_data = array(
				$reg['index_key'],$plan_item,$reg['val_string'],$reg['val_text'],
				$reg['val_complex']
				);
			$db->Execute("INSERT INTO ".geoTables::plan_item_registry." SET `index_key`=?, `plan_item`=?, `val_string`=?,
					`val_text`=?, `val_complex`=?", $query_data);
		}
		return true;
	}
	
	/**
	 * Remove plan items, being careful with resources.  Designed to be used
	 * when something like category is removed or price plan is removed.
	 * 
	 * Use default of "null" value for price plan or category if desired, to
	 * specify to remove all plan items in any category or price plan.  Either
	 * category or price plan must be specified, cannot call this with no inputs
	 * to remove all plan items, it just won't allow that.  Price plan id of
	 * 0 is considered "invalid" but category ID of 0 IS valid.
	 * 
	 * @param int|null $pricePlan
	 * @param int|null $categoryId
	 * @return bool
	 * @since Version 5.0.0
	 */
	public static function remove ($pricePlan = null, $categoryId = null)
	{
		$pricePlan = ($pricePlan === null)? '%': (int)$pricePlan;
		$categoryId = ($categoryId === null)? '%': (int)$categoryId;
		
		if ($pricePlan === '%' && $categoryId === '%') {
			//both can't be null!
			return false;
		}
		
		$wheres = array ();
		if ($pricePlan !== '%' && $pricePlan) {
			$wheres[] = "`price_plan`={$pricePlan}";
		}
		if ($categoryId !== '%') {
			$wheres[] = "`category`={$categoryId}";
		}
		if (!count($wheres)) {
			//still not specifying either one!
			return false;
		}
		
		$db = DataAccess::getInstance();
		
		//delete all the registry items
		$sql = "DELETE FROM ".geoTables::plan_item_registry." WHERE `plan_item` LIKE '%:{$pricePlan}:{$categoryId}'";
		$result = $db->Execute($sql);
		if (!$result) {
			trigger_error('ERROR SQL: Error removing stuff for plan item, using sql: '.$sql.', Error: '.$db->ErrorMsg());
			return false;
		}
		
		//delete main plan item
		
		$sql = "DELETE FROM ".geoTables::plan_item." WHERE ".implode(' AND ', $wheres);
		$result = $db->Execute($sql);
		if (!$result) {
			trigger_error('ERROR SQL: Error removing stuff for plan item, using sql: '.$sql.', Error: '.$db->ErrorMsg());
			return false;
		}
		return true;
	}
	
	/**
	 * Internal use.
	 * @internal
	 * @var mixed $_validPricePlans
	 */
	private static $_validPricePlans = array();
	
	/**
	 * Sees if the given price plan ID is a valid one.
	 * 
	 * @param int $pricePlanId
	 * @return bool
	 */
	public static function isValidPricePlan ($pricePlanId)
	{
		$pricePlanId = (int)$pricePlanId;
		if (isset(self::$_validPricePlans[$pricePlanId])) {
			//we've already determined if it is valid or not
			return self::$_validPricePlans[$pricePlanId];
		}
		if (!$pricePlanId) {
			//0 is not a valid price plan
			return false;
		}
		$db = DataAccess::getInstance();
		$row = $db->GetRow("SELECT `price_plan_id` FROM ".geoTables::price_plans_table." WHERE `price_plan_id`=$pricePlanId");
		self::$_validPricePlans[$pricePlanId] = (isset($row['price_plan_id']) && $row['price_plan_id'] == $pricePlanId);
		return self::$_validPricePlans[$pricePlanId];
	}
	
	/**
	 * Checks to see if the given price plan is valid for the specified user,
	 * valid meaning either the default price plan, or one of the alternate
	 * "attached" price plans, for the user.
	 * 
	 * @param int $userId
	 * @param int $pricePlanId
	 * @return bool
	 * @since Version 4.1.0
	 */
	public static function isValidPricePlanFor ($userId, $pricePlanId)
	{
		//clean input
		$userId = (int)$userId;
		$pricePlanId = (int)$pricePlanId;
		
		if ($userId<=0 || $pricePlanId <= 0) {
			//not valid input
			return false;
		}
		
		//first see if it's even a real price plan
		if (!self::isValidPricePlan($pricePlanId)) {
			//not valid price plan ID
			return false;
		}
		$user = geoUser::getUser($userId);
		if (!$user) {
			//not valid user, can't check price plan
			return false;
		}
		if ((int)$user->price_plan_id == $pricePlanId || (int)$user->auction_price_plan_id == $pricePlanId) {
			//matches one of defaults
			return true;
		}
		//check the "attached":
		$db = DataAccess::getInstance();
		$altMatches = $db->GetOne("SELECT COUNT(*) FROM ".geoTables::attached_price_plans." 
			WHERE `group_id` = $user->group_id AND `price_plan_id` = $pricePlanId");
		if ($altMatches == 1) {
			//there was an alternate price plan that matches, so it is valid
			return true;
		}
		//not attached anywhere!
		return false;
	}
	
	/**
	 * Gets the price plan for the given user.  It will also validate that the
	 * price plan is valid, if it is not valid, or if the user is not valid or
	 * set to 0, it will get the default price plan for the site.
	 * 
	 * @param $userId
	 * @return int
	 */
	public static function getDefaultPricePlan ($userId = 0)
	{
		$userId = (int)$userId;
		$user = ($userId)? geoUser::getUser($userId): false;
		
		if (!$user) {
			//return the default price plan for the site
			return ((geoMaster::is('classifieds'))? 1: 5);
		}
		$pricePlanId = ((geoMaster::is('classifieds'))? $user->price_plan_id: $user->auction_price_plan_id);
		if (!self::isValidPricePlan($pricePlanId)) {
			return ((geoMaster::is('classifieds'))? 1: 5);
		}
		return $pricePlanId;
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
	public function touch()
	{
		$this->_pendingChanges = true; //there are now pending changes
		
		//touch anything this object is "attached" to
		
		//.. except this isn't attached to anything currently
	}
	
	/**
	 * Magic method to allow using $planItem->var instead of $planItem->get('var')
	 * 
	 * @param string $name
	 * @return mixed
	 * @since Version 4.1.0
	 */
	public function __get ($name)
	{
		return $this->get($name);
	}
	
	/**
	 * Magic method to allow using $planItem->var = 'val' instead of $planItem->set('var','val')
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return mixed
	 * @since Version 4.1.0
	 */
	public function __set ($name, $value)
	{
		return $this->set($name, $value);
	}
}