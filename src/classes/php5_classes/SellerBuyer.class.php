<?php

/**
 * This is the main "back end system" for the seller/buyer payment gateways.
 *
 * Handles back-end for the seller/buyer payment gateways, to allow the buyer
 * to more easily pay the seller for a won auction.  This handles the underlying
 * system, but the actual work is done by individual seller/buyer gateways, for
 * instance the Paypal buy-now functionality.
 *
 * This is actually the first class that started using callDisplay and
 * callUpdate type functions, at least in the way they are used today.  Since
 * this is the first one, it is a little more primitive than "new" stuff like
 * payment gateways or order items, but it does what it needs to do just fine.
 *
 * @package System
 * @since Version 4.0.4
 */
class geoSellerBuyer
{
	/**
	 * Internal use
	 * @internal
	 */
	protected $_listing_settings, $_session_settings, $_user_settings, $_currency_settings, $_types;
	/**
	 * Instance of geoSellerBuyer
	 * @internal
	 */
	private static $_instance;

	/**
	 * Gets an instance of geoSellerBuyer
	 *
	 * @return geoSellerBuyer
	 */
	public static function getInstance()
	{
		if (!isset(self::$_instance) || !is_object(self::$_instance)) {
			$c = __class__;
			self::$_instance = new $c;
		}
		return self::$_instance;
	}

	/**
	 * Use geoSellerBuyer::getInstance(), not new geoSellerBuyer.
	 *
	 */
	private function __construct ()
	{

	}

	/**
	 * Set a setting for a specific listing, useful for instance, to set whether
	 * a certain listing is using a particular payment type.
	 *
	 * @param int $listing_id
	 * @param string $setting The setting name to be set.
	 * @param mixed $value The value to set, can be a string, int, or array,
	 *  although use of array is discourages for large arrays as it will take
	 *  up a lot more space in the DB.
	 */
	public function setListingSetting ($listing_id, $setting, $value)
	{
		$listing_id = intval($listing_id);
		if (!$listing_id) {
			return false;
		}

		$listing = geoListing::getListing($listing_id);

		//make sure settings for this listing are at least an empty array
		$this->getListingSetting($listing_id,$setting);
		$this->_listing_settings[$listing_id][$setting] = $value;

		//save
		$listing->seller_buyer_data = geoString::toDB(serialize($this->_listing_settings[$listing_id]));
	}

	/**
	 * Set a setting on the current "main item" that is in the cart.  If no
	 * cart item is currently initialized, will not set anything.
	 *
	 * @param string $setting The setting to set.
	 * @param mixed $value The value to set, can be a string, int, or array,
	 *  although use of array is discourages for large arrays as it will take
	 *  up a lot more space in the DB.
	 */
	public function setCartItemSetting($setting, $value)
	{
		$cart = geoCart::getInstance();

		if (!is_object($cart->item)) {
			//oops
			return false;
		}
		//use session variables from cart->site if that is set, otherwise get it from cart item.
		$session_variables = (isset($cart->site->session_variables) && count($cart->site->session_variables))? $cart->site->session_variables : $cart->item->get('session_variables', array());

		$settings = (isset($session_variables['seller_buyer_data']))? $session_variables['seller_buyer_data']: array();
		if(!is_array($settings)) {
			//during certain cases of listing edit only, this is serialized, and it needs to not be in order to add things to it...
			$settings = unserialize($settings);
		}
		$settings[$setting] = $value;
		$session_variables['seller_buyer_data'] = $cart->site->session_variables['seller_buyer_data'] = $settings;
		$cart->item->set('session_variables',$session_variables);
	}

	/**
	 * Set a setting for a specific user, for instance a setting needed to
	 * know who to send money to (like the paypal ID)
	 *
	 * @param int $user_id
	 * @param string $setting The setting name to be set.
	 * @param mixed $value The value to set, can be a string, int, or array,
	 *  although use of array is discourages for large arrays as it will take
	 *  up a lot more space in the DB.
	 */
	public function setUserSetting($user_id, $setting, $value)
	{
		$user_id = intval($user_id);
		if (!$user_id){
			return false;
		}
		$user = geoUser::getUser($user_id);
		if (!is_object($user)) {
			return false;
		}

		//make sure settings for this user are at least an empty array
		$this->getUserSetting($user_id,$setting);
		$this->_user_settings[$user_id][$setting] = $value;
		$user->seller_buyer_data = geoString::toDB(serialize($this->_user_settings[$user_id]));
	}

	/**
	 * Set a setting for a specific price plan and category, usually used in admin.
	 *
	 * @param int $price_plan_id The price plan ID.
	 * @param int $category The category ID.
	 * @param string $setting The setting name to be set.
	 * @param mixed $value The value to set, can be a string, int, or array,
	 *  although use of array is discourages for large arrays as it will take
	 *  up a lot more space in the DB.
	 */
	public function setPlanSetting ($price_plan_id, $category, $setting, $value)
	{
		$price_plan_id = intval($price_plan_id);
		$category = intval($category);

		if (!$price_plan_id){
			return false;
		}
		$planItem = geoPlanItem::getPlanItem('seller_buyer_data',$price_plan_id,$category, true);
		$planItem->set($setting, $value);
		$planItem->save();
	}

	/**
	 * Set setting for specific currency type.
	 * @param int $currency_type_id
	 * @param string $setting
	 * @param mixed $value
	 * @since Version 6.0.0
	 */
	public function setCurrencySetting ($currency_type_id, $setting, $value)
	{
		$currency_type_id = intval($currency_type_id);
		if (!$currency_type_id){
			return false;
		}

		//make sure settings for this user are at least an empty array
		$this->getCurrencySetting($currency_type_id,$setting);
		$this->_currency_settings[$currency_type_id][$setting] = $value;

		$db = DataAccess::getInstance();

		$seller_buyer_data = geoString::toDB(serialize($this->_currency_settings[$currency_type_id]));

		$db->Execute("UPDATE ".geoTables::currency_types_table." SET `seller_buyer_data`=? WHERE `type_id`=?", array ($seller_buyer_data, $currency_type_id));
	}

	/**
	 * Sets an array of default settings for the given price plan and category.
	 *
	 * @param int $price_plan_id
	 * @param int $category
	 * @param array $settings An associative array of settings to be set for the
	 *  given price plan and category.
	 */
	public function setDefaultPlanSettings($price_plan_id, $category, $settings)
	{
		$price_plan_id = intval($price_plan_id);
		$category = intval($category);

		if (!$price_plan_id){
			return false;
		}
		if (!is_array($settings)){
			return false;
		}
		$planItem = geoPlanItem::getPlanItem('seller_buyer_data',$price_plan_id,$category,true);
		foreach ($settings as $i => $val) {
			$planItem->set($i, $val);
		}
		$planItem->save();
	}

	/**
	 * Gets a setting for the given listing.
	 *
	 * @param int $listing_id The listing ID to get the setting for.
	 * @param string $setting The setting to get.
	 * @param mixed $default_value If the setting is not found for the given
	 *  listing ID, this is what is returned (default is false).
	 * @return mixed The setting asked for.
	 */
	public function getListingSetting($listing_id, $setting, $default_value = false)
	{
		$listing_id = intval($listing_id);
		if (!$listing_id){
			return $default_value; //listing id 0?
		}
		if (!is_array($this->_listing_settings)) {
			$this->_listing_settings = array();
		}
		if (!isset($this->_listing_settings[$listing_id])){
			//setting for listing not yet retrieved, so retrieve it.
			$listing = geoListing::getListing($listing_id);
			$data = unserialize(geoString::fromDB($listing->seller_buyer_data));
			if (!is_array($data)) {
				$data = array();
			}
			$this->_listing_settings[$listing_id] = $data;//set to empty array to start.
		}
		if (isset($this->_listing_settings[$listing_id][$setting])){
			return $this->_listing_settings[$listing_id][$setting];
		}
		return $default_value; //settings for listing id is retrieved, but setting not set, so return false.
	}

	/**
	 * Gets a setting for the current main item in the cart right now.
	 *
	 * @param string $setting The setting to get.
	 * @param mixed $default_value If the setting is not found for the given
	 *  listing ID, this is what is returned (default is false).
	 * @return mixed The setting asked for.
	 */
	public function getCartItemSetting($setting, $default_value = false)
	{
		$cart = geoCart::getInstance();

		if (!is_object($cart->item)) {
			//oops
			return $default_value;
		}
		//use session variables from cart->site if that is set, otherwise get it from cart item.
		$session_variables = (isset($cart->site->session_variables) && count($cart->site->session_variables))? $cart->site->session_variables : $cart->item->get('session_variables', array());

		$settings = (isset($session_variables['seller_buyer_data']))? $session_variables['seller_buyer_data']: array();
		if(!is_array($settings)) {
			//sometimes this isn't unserialized yet, for certain cases of listing edit
			$settings = unserialize($settings);
			$cart->site->session_variables['seller_buyer_data'] = $settings; //go ahead and write the unserialized version to sessvars, just to make life easier...
		}
		if (isset($settings[$setting])) {
			return $settings[$setting];
		}
		return $default_value;
	}

	/**
	 * Gets a setting for the given user.
	 *
	 * @param int $user_id
	 * @param string $setting The setting to get.
	 * @param mixed $default_value If the setting is not found for the given
	 *  listing ID, this is what is returned (default is false).
	 * @return mixed The setting asked for.
	 */
	public function getUserSetting($user_id, $setting, $default_value=false)
	{
		$user_id = intval($user_id);
		if (!$user_id){
			return $default_value; //user id 0?
		}

		if (!is_array($this->_user_settings)){
			$this->_user_settings = array();
		}
		if (!isset($this->_user_settings[$user_id])){
			$user = geoUser::getUser($user_id);
			if (!is_object($user)) {
				//oops
				return $default_value;
			}
			$this->_user_settings[$user_id] = unserialize(geoString::fromDB($user->seller_buyer_data));
			if (!is_array($this->_user_settings[$user_id])) {
				$this->_user_settings[$user_id] = array();
			}
		}
		if (isset($this->_user_settings[$user_id][$setting])){
			return $this->_user_settings[$user_id][$setting];
		}
		return $default_value; //settings for user id is retrieved, but setting not set, so return false.
	}

	/**
	 * Gets a setting for the price plan/category.
	 *
	 * @param int $price_plan_id
	 * @param int $category The category ID (or 0 for not category specific)
	 * @param string $setting The setting to get.
	 * @param mixed $default_value If the setting is not found for the given
	 *  listing ID, this is what is returned (default is false).
	 * @param $forceCat
	 * @return mixed The setting asked for.
	 */
	public function getPlanSetting($price_plan_id, $category, $setting, $default_value = false, $forceCat = false)
	{
		$price_plan_id = intval($price_plan_id);
		$category = intval($category);

		if (!$price_plan_id){
			return $default_value; //listing id 0?
		}
		$planItem = geoPlanItem::getPlanItem('seller_buyer_data',$price_plan_id,$category, $forceCat);
		return $planItem->get($setting,$default_value);
	}

	/**
	 * Gets a setting for the price plan/category.
	 *
	 * @param int $currency_type_id
	 * @param string $setting The setting to get.
	 * @param mixed $default_value If the setting is not found for the given
	 *   listing ID, this is what is returned (default is null).
	 * @return mixed The setting asked for.
	 * @since Version 6.0.0
	 */
	public function getCurrencySetting($currency_type_id, $setting, $default_value=null)
	{
		$currency_type_id = (int)$currency_type_id;

		if (!$currency_type_id) {
			return $default_value;
		}

		if (!is_array($this->_currency_settings)){
			$this->_currency_settings = array();
		}
		if (!isset($this->_currency_settings[$currency_type_id])){
			$db = DataAccess::getInstance();

			$row=$db->GetRow("SELECT `seller_buyer_data` FROM ".geoTables::currency_types_table." WHERE `type_id`=$currency_type_id");

			if ($row===false) {
				//error, may just need to set up db
				$this->initTableStructure();
			}

			if (!$row) {
				return $default_value;
			}

			$this->_currency_settings[$currency_type_id] = unserialize(geoString::fromDB($row['seller_buyer_data']));

			if (!is_array($this->_currency_settings[$currency_type_id])) {
				$this->_currency_settings[$currency_type_id] = array();
			}
		}
		if (isset($this->_currency_settings[$currency_type_id][$setting])){
			return $this->_currency_settings[$currency_type_id][$setting];
		}
		return $default_value;
	}


	/**
	 * Initializes the needed table structure changes, that way
	 * only sites that use the feature will use this.
	 *
	 */
	public function initTableStructure ()
	{
		$db = true;
		include GEO_BASE_DIR.'get_common_vars.php';

		//add settings column to geodesic_userdata, to store things like the user's token.
		$sqls[] = 'ALTER TABLE `geodesic_userdata` ADD `seller_buyer_data` TEXT NULL';
		//add column to classified table, to save things like the buy now link, or the status of the listing.
		$sqls[] = 'ALTER TABLE `geodesic_classifieds` ADD `seller_buyer_data` TEXT NULL';
		//add column to currencies table (added in 6.0)
		$sqls[] = "ALTER TABLE ".geoTables::currency_types_table." ADD `seller_buyer_data` TEXT NULL";

		foreach ($sqls as $sql) {
			if (!$db->Execute($sql)) {
				trigger_error('DEBUG SQL SELLER_BUYER: SQL: '.$sql.' : possible problem, or maybe column already exists, so ignoring SQL query error: '.$db->ErrorMsg());
			}
		}
	}

	/**
	 * Loads all of the seller-buyer objects into an array.
	 *
	 * @param string $dirname Leave this blank, used internally to recursively load
	 *   types from different folders.
	 */
	public function loadTypes($dirname = '')
	{
		if (is_array($this->_types) && strlen($dirname) == 0) {
			//already loaded
			return ;
		}
		if (!isset($this->_types)) $this->_types = array();
		if (strlen($dirname) == 0) {
			//load addon's too
			$addon = geoAddon::getInstance();
			$addons = $addon->getSellerBuyerAddons();
			foreach ($addons as $addon_name){
				$this->loadTypes(ADDON_DIR.$addon_name.'/payment_gateways/seller_buyer/');
			}
			//load the normal directory now
			$dirname = CLASSES_DIR.'payment_gateways/seller_buyer/';
		}

		//echo 'Adding dir: '.$dirname.'<br />';
		$dir = opendir($dirname);
		while ($filename = readdir($dir)) {
			if ($filename !='.' && $filename != '..' && strpos($filename,'_') !== 0 && strpos($filename,'.php') !== false && file_exists($dirname.$filename)){
				//echo '<strong>Adding: '.$methodname.'.'.str_replace('.php','',$filename).'</strong><br />';
				require_once($dirname.$filename);
				$name = str_replace('.php','',$filename);
				if (strlen($name)>0 && class_exists($name.'SellerBuyerGateway')){
					$this->_types[$name] = Singleton::getInstance($name.'SellerBuyerGateway');
				}
			}
		}
		closedir($dir);
	}

	/**
	 * Calls the specified update function for all of the seller/buyer types, and seperates the returned
	 * responses from each of the order items by $seperater
	 *
	 * IMPORTANT: This leaves it up to each seller/buyer type to make sure that type is turned on and all that,
	 *  and that input is cleaned.
	 *
	 * NOTE: Unlike geoOrderItem or geoPaymentGateway callDisplay functions, this calls the function
	 * NON-Statically (meaning $item->call_name($vars) instead of Item::call_name($vars).  In other
	 * words, it works more like triggerDisplay() for an addon core event.
	 *
	 * @param string $call_name
	 * @param mixed $vars
	 * @param string $separator What string to use as glue, or one of these special cases:
	 *  "array": return results of each in an array
	 *  "bool_true": if any return true, then return true.  otherwise return false. (strict match)
	 *  "bool_false": if any return false, then return false.  otherwise return true. (strict match)
	 * @return mixed Usually a string of each result seperated by seperater, or if seperater is special case,
	 *  returns whatever that special case is for.
	 */
	final public static function callDisplay($call_name, $vars=null, $separator = ''){
		$sb = geoSellerBuyer::getInstance();
		$sb->loadTypes();
		$items = $sb->_types;

		$parts = array();
		foreach ($items as $key => $item) {
			if (method_exists($item,$call_name)) {
				//call it statically
				//trigger_error('DEBUG CART: calling display object '.self::$orderTypes[$key]['class_name'].' method '.$call_name);
				$this_html = $item->$call_name($vars);
				switch ($separator) {
					case 'filter':
						$vars = $this_html;
						break;

					case 'array' :
						if (is_array($this_html) && count($this_html) > 0){
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
					case 'string_array':
						//break ommited on purpose
					default:
						if (strlen($this_html) > 0) {
							$parts[$key] = $this_html;
						}
						break;
				}
			}
		}
		switch ($separator) {
			case 'filter':
				//just "filter" vars sent in
				return $vars;
				break;

			case 'array':
				//omited on purpose, break was.
			case 'string_array':
				//return the parts
				return $parts;
				break;

			case 'bool_true':
				//none returned true, so return false
				return false;
				break;

			case 'bool_false':
				//none returned false, so return true
				return true;
				break;

			default:
				$html = '';
				if (count($parts) > 0) {
					$html .= implode($separator,$parts);
				}
				return $html;
		}
	}

	/**
	 * Calls the specified update function for all of the seller/buyer types.
	 *
	 * IMPORTANT: This leaves it up to each seller/buyer type to make sure that type is turned on and all that,
	 *  and that input is cleaned.
	 *
	 * NOTE: Unlike geoOrderItem or geoPaymentGateway callUpdate functions, this calls the function
	 * NON-Statically (meaning $item->call_name($vars) instead of Item::call_name($vars),
	 * kind of like geoAddon::triggerUpdate() works for core events.
	 *
	 * @param string $call_name
	 * @param mixed $vars
	 */
	final public static function callUpdate ($call_name, $vars=null)
	{
		$sb = geoSellerBuyer::getInstance();
		$sb->loadTypes();

		foreach ($sb->_types as $type){
			if (method_exists($type,$call_name)){
				//call the update function statically
				//echo '<h2>Calling '.self::$orderTypes[$key]['class_name'].'::'.$call_name.'()</h2>';
				//trigger_error('DEBUG CART: calling update object '.self::$orderTypes[$key]['class_name'].' method '.$call_name);
				$type->$call_name($vars);
			}
		}
	}
}
