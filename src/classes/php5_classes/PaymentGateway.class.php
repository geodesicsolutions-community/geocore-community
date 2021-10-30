<?php
//PaymentGateway.class.php
/**
 * Holds the geoPaymentGateway class.
 * 
 * @package System
 * @since Version 4.0.0
 */


/**
 * Requires the registry class.
 */
require_once CLASSES_DIR . PHP5_DIR . 'Registry.class.php';

# This should be extended by each gateway

/**
 * This class should be extended by each different payment gateway, this will
 * have some default functions that each gateway will inherit, and also has
 * a few final static methods used by the system to affect all order items.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoPaymentGateway
{
	/**
	 * The gateway name.  Do NOT access directly, use set/get methods instead.
	 * @var string
	 */
	public $name;
	/**
	 * The gateway type.  Do NOT access directly, use set/get methods instead.
	 * @var string
	 */
	public $type;
	/**
	 * The display order.  Do NOT access directly, use set/get methods instead.
	 * @var int
	 */
	public $displayOrder;
	/**
	 * Whether enabled or not.  Do NOT access directly, use set/get methods instead.
	 * @var bool
	 */
	public $enabled;
	/**
	 * Whether or not this gateway is the default gateway or not
	 * @var bool
	 */
	public $default;
	/**
	 * The current user's group ID
	 * @var int
	 */
	public static $group = 0;
	/**
	 * The gateway registry
	 * @var geoRegistry
	 */
	public $registry;
	/**
	 * Whether or not there are changes that would need to be saved
	 * @var bool
	 */
	protected $_pendingChanges;
	
	/**
	 * Array of gateways
	 * @var array(geoPaymentGateway)
	 */
	private static $gateways;
	
	/**
	 * Whether or not success/failure page has shown yet or not
	 * @var bool
	 */
	private static $successFailShown = false;
	
	/**
	 * Constructor for gateway
	 */
	public function __construct ()
	{
		$this->_pendingChanges = true;
	}
	
	/**
	 * Gets the name of the payment gateway.
	 * @return string
	 */
	public function getName ()
	{
		return $this->name;
	}
	
	/**
	 * Needs to be over-loaded to return a title for this gateway, to be displayed in the
	 * admin or other areas.  For instance the "name" might be "account_balance" but the 
	 * title might be "Account Balance"
	 * 
	 * @return string
	 */
	public function getTitle()
	{
		$title = $this->name;
		$title = str_replace('_',' ',$title);
		$title = ucwords($title);
		
		return $title;
	}
	
	/**
	 * Set the name for the payment gateway.  Probably not used much, isntead
	 * the $name should just be set to the name for each gateway.
	 * 
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->_pendingChanges = true;
		$this->name = $name;
	}
	
	/**
	 * Gets the payment gateway type.
	 * @return string
	 */
	public function getType ()
	{
		return $this->type;
	}
	
	/**
	 * Sets the payment gateway type, but for this page load only, recommended
	 * to just set the $type var directly.
	 * 
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->_pendingChanges = true;
		$this->type = $type;
	}
	
	/**
	 * Gets the display order.
	 * 
	 * @param bool $skipAutoAdd If true, it will not automatically add the payment
	 *  gateway to the settings in the admin.
	 * @return int
	 */
	public function getDisplayOrder($skipAutoAdd = false)
	{
		if (!isset($this->displayOrder) && !$skipAutoAdd){
			$this->displayOrder = (geoPaymentGateway::getMaxDisplayOrder() + 1); //if not set, set to be at end of list.
		}
		return $this->displayOrder;
	}
	
	/**
	 * Sets the display order.
	 * @param int $val
	 */
	public function setDisplayOrder($val)
	{
		$this->_pendingChanges = true;
		$val = intval($val);
		//remove ourself from the old display order first
		if (isset(self::$gateways['sorted'][$this->displayOrder][$this->name])){
			//remove it from that location...  the serialize will add it back to the new location.
			unset(self::$gateways['sorted'][$this->displayOrder][$this->name]);
		}
		$this->displayOrder = $val;
	}
	
	/**
	 * Gets whether this payment gateway is enabled or not.
	 * 
	 * @return bool
	 */
	public function getEnabled()
	{
		return $this->enabled;
	}
	
	/**
	 * Set whether the gateway is enabled or not.
	 * @param bool $val
	 */
	public function setEnabled($val)
	{
		$this->_pendingChanges = true;
		$this->enabled = ($val)? 1: 0;
	}
	
	/**
	 * Gets whether this is the default payment gateway or not.
	 * @return bool
	 */
	public function getDefault()
	{
		return $this->default;
	}
	
	/**
	 * Sets whether this is the default payment gateway or not.
	 * @param bool $val
	 */
	public function setDefault($val)
	{
		$this->_pendingChanges = true;
		$this->default = ($val)? 1: 0;
	}
	
	/**
	 * Whether or not the gateway handles recurring payments, if not implemented
	 * the superclass defaults to return false.
	 * 
	 * @return bool
	 */
	public function isRecurring ()
	{
		return false;
	}
	
	/**
	 * Called to query the gateway to see the status of the recurring billing,
	 * and update the recurring billing's paidUntil status, update main status
	 * (for gateways that choose to use that), add a recurring billing transaction
	 * if applicable, etc.
	 * 
	 * @param geoRecurringBilling $recurring
	 */
	public function recurringUpdateStatus ($recurring)
	{
		//Up to each gateway to implement
		
	}
	
	/**
	 * Called to cancel the recurring billing, to stop payments.  Gateway should
	 * do whatever is needed to cancel the payment status.
	 * 
	 * @param geoRecurringBilling $recurring
	 * @param string $reason The reason for the cancelation
	 */
	public function recurringCancel ($recurring, $reason = '')
	{
		//Up to each gateway to implement
		
	}
	
	/**
	 * The recurring billing user agreement label and text, should return an array.
	 * Only used if isRecurring returns true and it is recurring payment.
	 * 
	 * @return array|bool Either bool false if no agreement shown, or an array 
	 *   like: array ('label' => 'label text', 'text' => 'text in agreement box.')
	 */
	public function getRecurringAgreement ()
	{
		//return array ('label' => 'Check if you agree.', 'text' => 'Agreement text.');
		return false;
	}
	
	/**
	 * Gets the current group set for this page load.
	 * @return int
	 */
	final public static function getGroup()
	{
		return self::$group;
	}
	
	/**
	 * Set the group specific setting for the rest of this page load (until it
	 * is changed again anyways).
	 * 
	 * @param int $group
	 * @param bool $force_new If group specific setting is not found, and this is
	 *  true, will create group specific settings for this group.  Otherwise it would
	 *  just default to default settings.
	 * @return unknown_type
	 */
	final public static function setGroup($group, $force_new = false)
	{
		//first make sure group is good
		$group = intval($group);
		
		$use_default = true;
		if (!$force_new && $group > 0){
			//make sure group is ok
			$db = DataAccess::getInstance();
			$sql = 'SELECT count(`group`) as `count` FROM '.geoTables::payment_gateway.' WHERE `group` = ?';
			$row = $db->GetRow($sql, array($group));
			if ($row['count'] > 0){
				$use_default = false;
			}
		} elseif ($group > 0) {
			$use_default = false;
		}
		
		if ($use_default){
			$group = 0;
		}
		if (self::$group !== $group){
			//changing groups, so make sure to reset everything we've retrieved so far, in case this is called in middle of doing stuff...
			self::$gateways = null;
		}
		self::$group = $group;
	}
	
	/**
	 * Removes the specified group specific settings, usualyl called from admin.
	 * 
	 * @param int $group
	 * @return bool true on success, false on failure.
	 */
	final public static function removeGroupPaymentGateways($group)
	{
		$group = intval($group);
		if ($group == 0){
			return false;
		}
		$db = DataAccess::getInstance();
		
		$sql = "DELETE FROM ".geoTables::payment_gateway." WHERE `group` = ?";
		$db->Execute($sql,array($group));
		
		$sql = "DELETE FROM ".geoTables::payment_gateway_registry." WHERE `payment_gateway` LIKE '%:$group'";
		$db->Execute($sql);
		return true;
	}
	
	/**
	 * Gets the specified item from the registry, or if item is one of the "main" items it gets
	 *  that instead.
	 *
	 * @param string $item
	 * @param mixed $default What to return if the item is not set.
	 * @return Mixed the specified item, or false if item is not found.
	 */
	public function get($item, $default = false)
	{
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
	public function set($item, $value)
	{
		$this->initRegistry();
		$this->_pendingChanges = true;
		return $this->registry->set($item, $value);
	}
	
	/**
	 * Serializes the current payment gateway settings (saves changes in the database, or creates new transaction if the
	 *  id is not set.  If it is a new order, it will set the order item ID after it has been
	 *  inserted into the database.
	 * 
	 * Also automatically serializes any objects attached to it that are not already serialized. 
	 *
	 */
	public function serialize()
	{
		//trigger_error('DEBUG TRANSACTION: Top of serialize()');
		$db = DataAccess::getInstance();
		if (!$this->_pendingChanges){
			//no pending changes, no need to serialize.
			return;
		}
		$name = $this->name;
		$type = $this->type.'';
		$group = intval(self::$group);
		$display_order = intval($this->displayOrder);
		if (!$display_order) {
			//get the current max display order
			$sql = "SELECT MAX(display_order) as max FROM ".geoTables::payment_gateway." WHERE `group`=$group";
			$row = $db->GetRow($sql);
			$display_order = intval($row['max']+1);
		}
		$enabled = ($this->enabled)? 1:0;
		$default = ($this->default)? 1:0;
		
		//update info
		//type = gateway_type in db table, since type is reserved
		$sql = "REPLACE INTO ".geoTables::payment_gateway." (`name`, `gateway_type`, `display_order`, `enabled`, `default`, `group`) 
				VALUES (?, ?, ?, ?, ?, ?)";
			
		$query_data = array($name, $type, $display_order, $enabled, $default, $group);
		
		$result = $db->Execute($sql, $query_data);
		if (!$result){
			trigger_error('ERROR SQL: Error with query when serialize object to db.  Error msg: '.$db->ErrorMsg());
			return false;
		}
		//add to gateway registry
		self::$gateways[$this->name] = $this;
		//add to sorted list
		self::$gateways['sorted'][$this->displayOrder][$this->name] = $this;
		
		//Serialize gateway registry
		if (isset($this->registry) && is_object($this->registry)){
			//only serialize if it is inited
			$this->registry->setId($this->name . ':' . self::$group);
			$this->registry->setName('payment_gateway');//make sure name did not get lost or something
			$this->registry->serialize();//serialize registry
		}
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
		return true;
	}
	
	/**
	 * Unserializes the object for the given gateway name and applies parameters to this object.
	 *
	 * @param string $name The gateway name
	 */
	public function unSerialize($name=0)
	{
		if (!$name && isset($this->name)){
			//id set using setId()
			$name = $this->name;
		}
		if (!$name){
			//can't unserialize without an name!
			return;
		}
		$group = intval(self::$group);
		$db = DataAccess::getInstance();
		
		//Get the main data
		
		$sql = "SELECT * FROM ".$db->geoTables->payment_gateway." WHERE `name` = ? AND `group` = ? LIMIT 1";
		$result = $db->Execute($sql, array($name, $group));
		if (!$result){
			trigger_error('ERROR SQL: ERror unserializing transaction: '.$db->ErrorMsg());
			return ;
		}
		if ($result->RecordCount() != 1){
			//nothing by that name...
			return ;
		}
		
		$row = $result->FetchRow();
		$skip_rows = array ('group');
		$key_translation = array (
			'gateway_type'=>'type',
			'display_order' => 'displayOrder'
		);
		foreach ($row as $key => $value){
			if (!is_numeric($key) && !in_array($key, $skip_rows)){
				//only process non-numeric rows that aren't in skip list
				if (array_key_exists($key,$key_translation)){
					$key = $key_translation[$key];
				}
				$this->$key = $value;
			}
		}
		if (!$this->name){
			//something went wrong with unserializing main values
			return ;
		}
		//add to gateways list
		self::$gateways[$this->name] = $this;
		//also add to sorted gateways by display_order
		if (!$this->displayOrder){
			$this->displayOrder = (geoPaymentGateway::getMaxDisplayOrder() + 1);//make it at the back of the class
		}
		self::$gateways['sorted'][$this->displayOrder][$this->name] = $this;		
		
		//we just serialized, so there are no longer pending changes.
		$this->_pendingChanges = false;
		return true;
	}
	
	/**
	 * initializes the registry for this payment gateway object.
	 * 
	 */
	public function initRegistry ()
	{
		if (is_object($this->registry)) {
			//already inited
			return;
		}
		//Unserialize registry
		$this->registry = new geoRegistry();
		
		$this->registry->setName('payment_gateway');
		$this->registry->setId($this->name . ':' . self::$group);
		$this->registry->unSerialize();
	}
	
	/**
	 * Gets the current max display order.
	 * @return int
	 */
	final public static function getMaxDisplayOrder()
	{
		$db = DataAccess::getInstance();
		self::loadGateways('',false);
		
		$max_order = 0;
		$sql = "SELECT `display_order` FROM ".geoTables::payment_gateway." ORDER BY `display_order` DESC LIMIT 1";
		$row = $db->GetRow($sql);
		if($row===false){
			trigger_error("DEBUG SQL: ".$db->MsgError());
		}else if (!empty($row)){
			$max_order = intval($row['display_order']);
		}
		//now go through all potentially un-saved gateways to see if there is a gateway not saved with a bigger order...
		$array_keys = array_keys(self::$gateways);
		foreach (self::$gateways as $gateway){
			if (is_object($gateway) && $gateway->getDisplayOrder(true) > $max_order){
				$max_order = $gateway->getDisplayOrder();
			}
		}
		return $max_order;
	}
	
	/**
	 * Alias of geoPaymentGateway::serialize() - see that method for details.
	 * 
	 */
	public function save()
	{
		return $this->serialize();
	}
	
	/**
	 * Gets a gateway object according to the name
	 *
	 * @param string $gateway_name
	 * @return geoPaymentGateway|null will return null if gateway not found.
	 */
	public static function getPaymentGateway ($gateway_name)
	{
		self::loadGateways();
		if (isset(self::$gateways[$gateway_name])){
			return self::$gateways[$gateway_name];
		}
		return null;
	}
	
	/**
	 * Gets an array of gateways matching the given gateway type.
	 *
	 * @param string $type Type of gateway, "recurring" to return gateways
	 *   that handle recurring payments, or "all" to return all enabled gateways, or
	 *   "really_all" to get all gateways, even ones not enabled. (really_all added
	 *   in version 7.2.2)
	 * @return array
	 */
	public static function getPaymentGatewayOfType ($type)
	{
		self::loadGateways();
		if ($type === 'really_all') {
			//really return all of them, even disabled...
			return self::$gateways;
		}
		$gateways = array();
		foreach (self::$gateways as $name => $gateway) {
			if (is_object($gateway) && $gateway->getEnabled()) {
				if (($type == 'recurring' && $gateway->isRecurring()) || $type == 'all' || $gateway->getType() == $type) {
					$gateways[$name] = $gateway;
				}
			}
		}
		return $gateways;
	}
	
	
	/**
	 * Loads all of the gateway objects into an array.
	 * 
	 * @param string $dirname Used internally, just leave it at default.
	 * @param bool $force_serialize Used internally, just leave it at default.
	 */
	final public static function loadGateways ($dirname = '', $force_serialize = true)
	{
		if (is_array(self::$gateways) && strlen($dirname) == 0){
			//already loaded
			return ;
		}
		if (strlen($dirname) == 0){
			self::$gateways = array();
			self::$gateways['sorted'] = array();
			//load addon's too
			$addon = geoAddon::getInstance();
			$addons = $addon->getPaymentGatewayAddons();
			foreach ($addons as $addon_name){
				self::loadGateways(ADDON_DIR.$addon_name.'/payment_gateways/', $force_serialize);
			}
			//load the normal directory now
			$dirname = CLASSES_DIR.'payment_gateways/';
		}
		
		//echo 'Adding dir: '.$dirname.'<br />';
		$dir = opendir($dirname);
		$skip = array('.','..','seller_buyer','includes');
		while ($filename = readdir($dir)){
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
			
			//echo '<strong>Adding: '.$methodname.'.'.str_replace('.php','',$filename).'</strong><br />';
			require_once($dirname.$filename);
			$name = str_replace('.php','',$filename);
			if (strlen($name)>0 && class_exists($name.'PaymentGateway')){
				self::$gateways[$name] = Singleton::getInstance($name.'PaymentGateway');
				//note: gateway responsible for setting it's own name, so name should be set before
				//next line is called...
				if ($force_serialize){
					//in case it was previously done for another group already...
					//unserialize will set pendingchanges to false if it finds settings for it
					self::$gateways[$name]->_pendingChanges = true;
					self::$gateways[$name]->unSerialize(); //restore settings and stuff
					//serialize it, to make sure it's in the db.  Note: does nothing if it's already in db.
					self::$gateways[$name]->serialize();
					
				}
			}
		}
		closedir($dir);
	}
	
	/**
	 * Calls the specified display function for the gateway specified, or all of the gateways
	 * if no specific gateway is specified, and seperates the returned responses from
	 * each of the gateways by $seperater
	 * 
	 * IMPORTANT: This leaves it up to each gateway to make sure that gateway is turned on and all that,
	 *  and that input is cleaned.
	 *
	 * @param string $call_name
	 * @param mixed $vars
	 * @param string $seperater
	 * @param string|array $gateway_name
	 * @param bool $force_enabled If false, gateway does not have to be enabled
	 *   to make this call
	 * @return string
	 */
	final public static function callDisplay($call_name, $vars=null, $seperater = '', $gateway_name = '', $force_enabled = true)
	{
		self::loadGateways();
		if (is_array($gateway_name) && count($gateway_name) > 0) {
			//item type is an array of item types to run
			//make sure all the item types specified are valid
			$keys = array();
			foreach ($gateway_name as $type) {
				if (isset(self::$gateways[$type])) {
					$keys[] = $type;
				}
			}
		} else if (strlen($gateway_name) > 0){
			//gateway name specified, only call the display function for gateway specified
			$keys = array();
			if (isset(self::$gateways[$gateway_name])){
				$keys[] = $gateway_name;
			}
		} else {
			//no gateway name specified, run for all gateways.
			//display type of call, expecting to return text
			$keys = array();
			//go through each display order, and add keys in order...
			$array_keys = array_keys(self::$gateways['sorted']);
			//make sure they are in order
			asort($array_keys);
			foreach ($array_keys as $sorted){
				$array_keys_2 = array_keys(self::$gateways['sorted'][$sorted]);
				foreach ($array_keys_2 as $this_key){
					if (is_object(self::$gateways['sorted'][$sorted][$this_key]) && (!$force_enabled || self::$gateways['sorted'][$sorted][$this_key]->getEnabled())){
						//now keys are in order of display_order!!!
						$keys[] = $this_key;
					}
				}
			}
		} 
		$parts = array();
		foreach ($keys as $key){
			if (method_exists(self::$gateways[$key],$call_name)){
				$this_html = self::$gateways[$key]->$call_name($vars);
				if ($seperater == 'array'){
					if ((is_array($this_html) && count($this_html) > 0) || (strlen($this_html) > 0)){
						$parts[$key] = $this_html;	
					}
				} else if ($seperater == 'bool_true') {
					if ($this_html === true) {
						return true;
					}
				} else if ($seperater == 'bool_false') {
					if ($this_html === false) {
						return false;
					}
				} else if (strlen($this_html) > 0){
					$parts[] = $this_html;
				}
			}
		}
		if ($seperater == 'array'){
			//return array of responses...
			return $parts;
		} else if ($seperater == 'bool_true') {
			return false;
		} else if ($seperater == 'bool_false') {
			return true;
		}
		$html = '';
		if (count($parts) > 0){
			$html .= implode($seperater,$parts);
		}
		return $html;
	}
	
	/**
	 * Calls the specified update function for the gateway specified, or all of the payment gateways if no
	 *  specific gateway is specified.
	 * 
	 * IMPORTANT: This leaves it up to each gateway to make sure that gateway is turned on and all that,
	 *  and that input is cleaned.
	 *
	 * @param string $call_name
	 * @param mixed $vars
	 * @param string|array $gateway_name
	 * @param bool $force_enabled If false, gateway does not have to be enabled
	 *   to make this call
	 */
	final public static function callUpdate($call_name, $vars=null, $gateway_name = '', $force_enabled = true)
	{
		self::loadGateways();
		if (is_array($gateway_name) && count($gateway_name) > 0) {
			//item type is an array of item types to run
			//make sure all the item types specified are valid
			$keys = array();
			foreach ($gateway_name as $type) {
				if (isset(self::$gateways[$type])) {
					$keys[] = $type;
				}
			}
		} else if (strlen($gateway_name) > 0){
			//gateway name specified, only call the display function for gateway specified
			$keys = array();
			if (isset(self::$gateways[$gateway_name])){
				$keys[] = $gateway_name;
			}
		} else {
			//no gateway name specified, run for all gateways.
			//display type of call, expecting to return text
			//don't care about order, order is only for display
			$keys = array_keys(self::$gateways);
		}
		foreach ($keys as $key){
			if (is_object(self::$gateways[$key]) && method_exists(self::$gateways[$key],$call_name) &&
				(!$force_enabled || self::$gateways[$key]->getEnabled())){
				//call the update function
				self::$gateways[$key]->$call_name($vars);
			}
		}
	}
	
	/**
	 * Find whether the given gateway exists or not.
	 * 
	 * @param string $gateway_name
	 * @return boolean
	 */
	final public static function gatewayExists($gateway_name)
	{
		self::loadGateways();
		if (isset(self::$gateways[$gateway_name])){
			return true;
		}
		return false;
	}
	
	/**
	 * Creates a form and submits it with javascript.
	 * Useful when a gateway requires data be sent via non-asynchronous POST (so cURL won't do the trick),
	 * 
	 * Handy when the visitor needs to be sent to the gateway's site but requires data sent via POST instead of
	 * GET.
	 * 
	 * @param string $url the url to submit to
	 * @param array $fields Post fields to include in the submission
	 * @param bool $skipExit true to NOT include app_bottom and exit
	 * 
	 * @since Version 6.0.0
	 */
	protected function _submitViaPost ($url, $fields, $skipExit = false)
	{
		$view = geoView::getInstance();
		$site = Singleton::getInstance('geoSite');
		$site->page_id = 10203;
		$view->post_url = $url;
		$view->post_fields = $fields;
		$view->setBodyTpl('shared/post_form.tpl','','payment_gateways');
		$site->display_page();
		
		if (!$skipExit) {
			require GEO_BASE_DIR . 'app_bottom.php';
			exit();
		}
	}
	
	/**
	 * Use this to get the HTML for the common settings that most gateways will need to
	 * have, such as "Reqire Admin Approval" and "Account Status".
	 * 
	 * Note that this function won't do the saving for you, for that you need to call
	 * _updateCommonAdminOptions()
	 * 
	 * @param bool $canTest false if gateway does not support a switchable "testing mode", otherwise true
	 * @param bool $isRecurring Whether or not should show recurring settings
	 * @return string HTML needed to display the settings
	 */
	protected function _showCommonAdminOptions ($canTest=true, $isRecurring = false)
	{
		$admin_checked = ($this->get('require_admin_approve'))? ' checked="checked"': '';
		$tooltip = geoHTML::showTooltip('Require Admin Approval','If this is checked, then when an order is placed and paid for using this gateway, the status will be set to "pending".  Then the admin user can view the order in "pending orders", and once the admin verifies the payment, they can set the order to active.<br /><br />
		<strong>Note:</strong> This is meant to <strong>verify payments only</strong>.  It is not meant as a way to screen listings or listing edits, for that see the require admin approval for individual order items.');
		
		$html = geoHTML::addOption('Require Admin Approval'.$tooltip,"<input type='checkbox' name='".$this->name."[require_admin_approve]' value='1'$admin_checked />");
		
		$live_checked = ($this->get('testing_mode'))? '': 'checked="checked" ';
		$nolive_checked = ($live_checked)? '': 'checked="checked" ';
		$test_disabled = (!$canTest) ? 'disabled="disabled"' : '';
		
		$tooltip = geoHTML::showTooltip('Account Status','Use test mode to make sure all your settings are correct.  While in test mode, no money will exchange hands.  On some gateways, it will also display extra debug output at the top of the screen when in test mode.<br /><br />
		Once you are done testing, set to Live to start accepting payments online.');
		
		$html .= geoHTML::addOption('Account Status '.$tooltip,"<label><input type='radio' name='{$this->name}[testing_mode]' value=\"0\" {$live_checked}{$test_disabled} /> Live (ready to process payments)</label><br />
																		<label><input type='radio' name='{$this->name}[testing_mode]' value='1' {$nolive_checked}{$test_disabled} /> Testing Mode</label>");
		
		if ($isRecurring && geoPC::is_ent()) {
			$recurring = ($this->get('recurring'))? 'checked="checked" ': '';
			$html .= geoHTML::addOption('Recurring Billing Enabled',"<input type='checkbox' name='".$this->name."[recurring]' value='1'$recurring />");
		}
		
		return $html;
	}
	
	/**
	 * Update the common admin options, the options that are displayed using
	 * {@see geoPaymentGateway::_displayCommonAdminOptions()}
	 * 
	 * @param array $settings
	 * @param bool $isRecurring
	 */
	protected function _updateCommonAdminOptions ($settings, $isRecurring = false)
	{
		$test_mode = (isset($settings['testing_mode']) && $settings['testing_mode'])? 1 : false;
		$this->set('testing_mode',$test_mode);
		
		$admin_approve = (isset($settings['require_admin_approve']) && $settings['require_admin_approve'])? 1 : false;
		$this->set('require_admin_approve',$admin_approve);
		
		if ($isRecurring && geoPC::is_ent()) {
			$recurring = (isset($settings['recurring']) && $settings['recurring'])? 1 : false;
			$this->set('recurring',$recurring);
		}
	}
	
	/**
	 * Does common tasks when a transaction was successful.  Only call if invoice is paid off, because
	 * this will set the order to active (or pending if require admin approve is turned on)
	 *
	 * @param geoOrder $order
	 * @param geoTransaction $transaction
	 * @param geoPaymentGateway $gateway
	 * @param bool $skipDisplay True to skip showing the success page automatically.
	 */
	protected static function _success($order, $transaction, $gateway, $skipDisplay = false)
	{
		//it is confirmed good, so activate it
		$transaction->setStatus(1);
		$transaction->save();
		
		//Now set the status on the order
		if ($gateway->get('require_admin_approve')) {
			$order->processStatusChange('pending_admin');
		} else {
			$order->processStatusChange('active');
		}
		$order->save();
		if (!$skipDisplay) {
			$invoice = $transaction->getInvoice() ? $transaction->getInvoice() : null;
			self::_successFailurePage(true, $order->getStatus(), true, $invoice, $transaction);
		}
	}
	
	/**
	 * Does common stuff for orders that fail
	 *
	 * @param geoTransaction $transaction
	 * @param string $errorCode
	 * @param string $errorMessage
	 * @param bool $skipDisplay True to skip showing the failure page automatically.
	 */
	protected static function _failure ($transaction, $errorCode, $errorMessage, $skipDisplay = false)
	{
		//make sure it's not active
		if (is_object($transaction)) {
			$transaction->setStatus(0);
			$transaction->set('result',$errorCode);
			$transaction->set('failed_reason',$errorMessage);
			$transaction->save();
		}
				
		//failure codes as error for debugging
		//could do more here later with codes, if wanted
		trigger_error('DEBUG FAILURE: Gateway transaction failed with code: '.$errorCode.' and message: '.$errorMessage);
		
		//display failure result
		if (!$skipDisplay) {
			self::_successFailurePage(false);
		}
	}
	
	
	/**
	 * Displays a page informing user of success/failure of payment
	 *
	 * @param bool $success true for success, false for failure.
	 * @param string $status Status of the order, in other words, what would be
	 *   returned by $order->getStatus()
	 * @param bool $render If false, will not actually display the page, will just
	 *   get the view class ready to display
	 * @param geoInvoice $invoice Optional, if provided, will add a link to the invoice
	 *   on the order complete page.  Param added in version 6.0.0 
	 * @param geoTransaction $transaction Optional. If provided, will expose certain transaction data as template variables. Added 8.0.0
	 */
	protected static function _successFailurePage($success=false, $status = 'pending', $render = true, $invoice = null, $transaction = null)
	{	
		if(self::$successFailShown) {
			//if we've already shown the page, don't show it again
			return;
		}
		//set page ID and get text
		$db = DataAccess::getInstance();
		$msgs = $db->get_text(true, 10204);
		
		$active = ($status === 'active') ? $msgs[500303] : $msgs[500302];
		
		
		$tpl_vars = array(
			'page_title' => (($success) ? $msgs[500298] : $msgs[500299]),
			'page_desc' => (($success) ? $msgs[500300] : $msgs[500301]),
			'success_failure_message' => (($success) ? $active : $msgs[500304]),
			'my_account_url' => $db->get_site_setting('classifieds_file_name').'?a=4',
			'my_account_link' => $msgs[500305],
			'success' => $success,
			'in_admin' => defined('IN_ADMIN'),
		);
		
		if (is_object($invoice) && $invoice->getId()) {
			$tpl_vars['invoice_url'] = geoInvoice::getInvoiceLink($invoice->getId(), false, defined('IN_ADMIN'));
		}
		
		if(is_object($transaction)) {
			$tpl_vars['transaction'] = array(
				'id' => $transaction->getId(),
				'amount' => $transaction->getAmount(),
				'description' => $transaction->getDescription(),
				'status' => $transaction->getStatus(),
				'user' => geoUser::getUser($transaction->getUser()) ? geoUser::getUser($transaction->getUser())->toArray() : 0
			);
		}
		
		geoView::getInstance()->setBodyTpl('shared/transaction_approved.tpl','','payment_gateways')
			->setBodyVar($tpl_vars);
		
		if ($render) {
			require_once(CLASSES_DIR . 'site_class.php');
			$site = Singleton::getInstance('geoSite');
			$site->page_id = 10204;
			$site->inAdminCart = (defined('IN_ADMIN')); 
			$site->display_page();
		}
		self::$successFailShown = true;
	}
	
	/**
	 * Can be used by payment gateways to get or create a recurring billing item
	 * for use when processing payment.
	 * 
	 * @param geoOrder $order
	 * @param geoPaymentGateway $gateway
	 * @param geoOrderItem $item
	 * @return geoRecurringBilling
	 */
	protected static function _initRecurring($order, $gateway, $item)
	{
		if (!is_object($order) || !is_object($gateway) || !is_object($item)) {
			//sanity check
			return false;
		}
		$recurring = $order->getRecurringBilling();
		if (!$recurring) {
			//create one!
			$recurring = new geoRecurringBilling();
			$recurring->setGateway($gateway);
			$order->setRecurringBilling($recurring);
			$recurring->setItemType($item->getType());
			$recurring->setStatus('pending');//just so it doesn't start of with no status
		}
		
		$interval = $item->getRecurringInterval();
		$recurringAmount = $item->getRecurringPrice();
		
		if (!$interval || !$recurringAmount) {
			//oops! order item doesn't seem to be fully committed to the idea of
			//being recurring...
			return false;
		}
		$recurring->setCycleDuration($interval);
		$recurring->setPricePerCycle($recurringAmount);
		//set it to be paid until now, basically means it's disabled after
		//now.
		$recurring->setPaidUntil(geoUtil::time());
		//set user
		if ($order->getBuyer()) $recurring->setUserId($order->getBuyer());
		
		return $recurring;
	}
	
	
	/**
	 * Create a new transaction, specifically for logging some event for recurring
	 * billing.  This is meant to be used for transactions that are not for payments,
	 * things like logging when it is canceled or payment attempt made or similar.
	 * 
	 * @param geoRecurringBilling $recurring
	 * @param geoPaymentGateway $gateway
	 * @return geoTransaction
	 * @since Version 6.0.6
	 */
	protected static function _createNewRecurringLogTransaction ($recurring, $gateway)
	{
		$transaction = new geoTransaction();
		$transaction->setStatus(1); //turn on, after all just for logging
		$transaction->setAmount(0);
		$transaction->setDate(geoUtil::time());
		$transaction->setGateway($gateway);
		$transaction->setUser($recurring->getUserId());
		$transaction->setRecurringBilling($recurring);
		$recurring->addTransaction($transaction);
		return $transaction;
	}
}
