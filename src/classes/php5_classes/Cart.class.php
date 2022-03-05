<?php
//Cart.class.php
/**
 * Holds the geoCart class.
 *
 * @package System
 * @since Version 4.0.0
 */


require_once(CLASSES_DIR.'site_class.php');

//Temporary site class, to be used to make refactoring the site and sell class easier
require_once(CLASSES_DIR .'order_items/_site_class_temp.php');

/**
 * This class is behind the cart, loading all the order items and such and displaying,
 * and processing all the different pages.
 *
 * @package System
 * @since Version 4.0.0
 */
class geoCart
{
	//Constants used in addStep call.
	const FIRST_STEP = 'first';
	const LAST_STEP = 'last';
	const AFTER_STEP = 'after';
	const BEFORE_STEP = 'before';
	const REPLACE_STEP = 'replace';

	/**
	 * This is the "main type" that is currently being "worked on" in the cart.
	 * If not working on any specific item type, will be set to 'cart'.
	 *
	 * @var string
	 */
	public $main_type;

	/**
	 * The DataAccess object for easy access.
	 * @var DataAccess
	 */
	public $db;

	/**
	 * The geoSession object for easy access.
	 * @var geoSession
	 */
	public $session;

	/**
	 * The cart vars, used to hold vars specific to the current "cart session".
	 *
	 * @var array
	 */
	public $cart_variables;

	/**
	 * The current user's data.
	 * @var array
	 */
	public $user_data;

	/**
	 * An array of all the current price plan settings.
	 * @var array
	 */
	public $price_plan;

	/**
	 * Associative array of actions and what item types those actions were performed
	 * on if applicable.
	 *
	 * Useful for telling if a particular item type was just canceled, in order to do
	 * something special at the time the cart is being displayed.
	 *
	 * @var array
	 */
	public $actions_performed = array();
	/**
	 * Order attached to the cart
	 *
	 * @var geoOrder
	 */
	public $order;

	/**
	 * The current step the cart is on.
	 * @var string
	 */
	public $current_step;
	/**
	 * Order item, the type will match main_type.
	 *
	 * @var geoOrderItem
	 */
	public $item;

	/**
	 * Stores all of the steps
	 * @var array
	 */
	protected $all_steps = array();

	/**
	 * Stores all of the "standard" steps "would be" without combining them
	 *
	 * @var array
	 */
	protected $all_steps_standard = array();

	/**
	 * Number of errors currently accumulated, if there are any number more than
	 * 0 the cart will not proceed to the next step automatically.  Don't modify
	 * this directly, instead use {@link geoCart::addError()}
	 *
	 * @var int
	 */
	public $errors = 0;

	/**
	 * An array of error messages.
	 * @var array
	 */
	public $error_msgs = array();
	/**
	 * Site (temporary till we move everything in site to other classes)
	 *
	 * @var tempSiteClass
	 */
	public $site;
	/**
	 * The built in steps
	 * @var array
	 */
	private $built_in_steps = array(
		'delete', //delete item, special step
		'preview', //preview item, special step
		'combined', //combined several steps
		'other_details',
		'cart',
		'payment_choices',
		'process_order' //processing is done in payment_choicesProcess...
	);

	/**
	 * Works like {@link geoCart::$all_steps}, but this is the combined steps
	 * that will be part of the combined step.
	 *
	 * @var array
	 */
	private $combined_steps = array();

	/**
	 * Steps that are not able to be combined, like the JIT or anonymous steps,
	 * are kept track of here.
	 * @var array
	 */
	private $uncombined_steps = array();

	/**
	 * Used internally
	 * @internal
	 */
	private $action = 'cart', $actionSpecial = '', $registry;
	/**
	 * Instance of geoCart
	 * @var geoCart
	 * @internal
	 */
	private static $_instance;
	/**
	 * Used internally to remember whether there has been changes to the order since it was last
	 *  serialized.  If there is not changes, when serialize is called, nothing will be done.
	 *
	 * @var boolean
	 */
	private $_pendingChanges;

	/**
	 * Used internally
	 * @internal
	 */
	private $_doProcess, $_skipInitSteps = false, $_skipNextStep = false;

	/**
	 * Whether or not the cart has been "fully" initialized or not.
	 * @var bool
	 */
	public $initialized_full = false;

	/**
	 * Whether or not the cart has been partially inited or not.
	 * @var bool
	 */
	public $initialized_onlyItems = false;

	/**
	 * Used as the prefix for setting names, used to store values on plan items
	 * that deal with combined steps.
	 *
	 * @var string
	 * @since Version 7.2.0
	 */
	const COMBINED_PREFIX = '_system_combine:';

	/**
	 * Only valid way to get an instance of the geoCart.
	 *
	 * You would do something like:
	 *
	 * $cart = geoCart::getInstance();
	 *
	 * @return geoCart Instance of geoCart
	 */
	public static function getInstance ()
	{
		if (!(isset(self::$_instance) && is_object(self::$_instance))){
    		$c = __class__;
    		self::$_instance = new $c();
    	}
    	return self::$_instance;
	}

	/**
	 * Do not create new cart object, instead use geoCart::getInstance()
	 */
	private function __construct ()
	{
		$this->errors = 0;
		//let app_bottom know it needs to save the cart
		define ('geoCart_LOADED',1);
	}

	/**
	 * Initializes the cart for the first time in the page load.
	 *
	 * @param bool $onlyInitItems if true, will only initialize the cart order
	 *  items, and not do any of the other stuff.
	 * @param int $userId If set, will create the cart for the given user instead
	 *  of the user from the current session.
	 * @param bool $renderPage Only used if $onlyInitItems is false, if this is
	 *   true, the page will be displayed, if false, everything will be done
	 *   except any processing or checkvars calls, or the last step of
	 *   displaying the page.
	 */
	public function init ($onlyInitItems = false, $userId = null, $renderPage = true)
	{
		//TODO: everywhere that returns false in this init, instead display an error or something
		//set up session

		trigger_error('DEBUG CART: START');

		if ($this->initialized_full) {
			//we've already done a full init
			return;
		} else if($this->initialized_onlyItems && $onlyInitItems) {
			//only want to init items, and have already init'd items
			return;
		}

		//let anyone who cares know that we've run init()
		if ($onlyInitItems) {
			$this->initialized_onlyItems = true;
		} else {
			$this->initialized_full = true;
		}

		$this->session = geoSession::getInstance();

		//init main class vars
		$this->db = DataAccess::getInstance();

		//NOTE: to allow anonymous listings, the check to see if a user is logged in
		//is now handled in the initItem process.

		//set default, do not checkvars/process
		$this->_doProcess = false;


		//TODO: Remove this once everything has been moved out of site class!
		$this->site = Singleton::getInstance('tempSiteClass');

		$this->site->inAdminCart = (defined('IN_ADMIN'));

		if (!$this->_getUserData($userId)){
			return false; //initialize $this->user_data
		}
		if (!$this->setPricePlan()){
			return false; //initialize $this->price_plan
		}

		geoPaymentGateway::setGroup($this->user_data['group_id']); //let payment gateway know which group to get payment gateway settings for

		//Initialize session
		if (!$this->initSession(0, $onlyInitItems)) {
			//something went wrong with session
			trigger_error('DEBUG CART: Returning false, init session came back false.');
			return false;
		}
		if ($onlyInitItems) {
			//this is probably some 3rd party or special case, who is only
			//interested in initializing up to the point of getting the items set up.

			//oh be sure to populate text though
			$this->site->messages = $this->db->get_text(true);

			return true;
		}
		if (isset($_GET['action_special']) && $_GET['action_special'] == 'cancel_and_go') {
			//first, cancel current process, they clicked on the cancel and continue link.
			$this->performAction('cancel');
			//set the action, main type, and current step all to "cart".
			$this->action = $this->main_type = $this->cart_variables['main_type']
				= $this->current_step = $this->cart_variables['step']
				= 'cart';
			//save changes, just to make sure this happens before user is
			//re-directed to page
			$this->save();
			//Now re-direct to new page, so that things from "parent" order items
			//do not bleed over
			$url = str_replace('&amp;','&',$this->getCartBaseUrl());

			$ignore = array('a','action_special');
			foreach ($_GET as $key => $value) {
				if (!in_array($key,$ignore)) {
					$url .= "&{$key}=$value";
				}
			}

			header('Location: '.$url);
			require GEO_BASE_DIR.'app_bottom.php';
			exit;
		}
		//If there is an action, run action
		$actions_outside_cart = array(
			'cancel',
			'process'
		);
		if (isset($_GET['action']) && strlen(trim($_GET['action'])) > 0) {
			if ($this->main_type == 'cart' || (in_array($_GET['action'],$actions_outside_cart))) {
				if ($this->main_type == 'cart' && $_GET['action'] == 'process' && isset($_GET['step']) && !in_array($_GET['step'], array('cart','payment_choices','process_order'))) {
					//It is in a main step of a cart, the action is process, but the step is not one of the steps
					//you would do here, so they are probably hitting refresh right after approving the last
					//step in adding an item.

					//nothing to do in this case.

				} else {
					//perform an action, only call if not currently in middle of adding item to cart
					$this->performAction(trim($_GET['action']));
				}
			} else if ($_GET['action'] == 'forcePreview' && $this->cart_variables['order_item'] != -1) {
				//action is preview, and we're in the middle of something...
				//allow previewing no matter where we are in the cart.
				$currentCartVars = $this->cart_variables;

				$this->performAction('preview');
				$this->displayStep();

				//put it back where it was at before
				$this->cart_variables = $currentCartVars;
				trigger_error('DEBUG CART: END');
				return true;
			} else {
				//user is attempting to start something new, but we are in the middle of something.
				//so show them that message.

				return $this->displayCartInterruption();
			}
		}
		if (strlen($this->main_type) == 0) {
			//if main type is not set, set it to "cart"
			$this->main_type = $this->cart_variables['main_type'] = 'cart';
		}

		if (!$this->_skipInitSteps) {
			//initialize the steps
			$this->initSteps();
			trigger_error('DEBUG CART: Init steps.  Steps: '.print_r($this->all_steps,1));
		} else {
			trigger_error('DEBUG CART: Init steps SKIPPED!');
		}

		//If process is set, and the number of steps between the URL step and the session step
		//is less than 2, and renderPage is true, then process this step.
		if ($this->_doProcess && $renderPage) {
			//Need to do work some work
			//First, Check Vars for this step

			if ($this->checkVars()) {
				//check vars was good, now call process
				//note that process will make the current step be incremented by one.
				$this->processStep();
			}
		}
		//Now display, if display affects anything to do with the cart it better save itself...
		if ($renderPage) {
			$this->displayStep();
		} else {
			//call the main cart display, but have it do everything except
			//for actually displaying the page.
			$this->cartDisplay($renderPage);
		}

		//NOTE: Cart is saved in app_bottom.php so as long as the application exits properly, the cart session should be saved.

		trigger_error('DEBUG CART: END');
	}

	/**
	 * Used internally, to initialize the cart session data.
	 *
	 * @param int $trys Number of times session was inited.
	 * @param bool $restoreOnly If true, will not create a new session, or something
	 *  like that...
	 * @return bool True if successful, false otherwise.
	 */
	protected function initSession ($trys = 0, $restoreOnly = false)
	{
		$allowNew = false;
		if (defined('IN_ADMIN')) {
			$adminId = (int)$this->session->getUserId();
			if (!$adminId) {
				//something wrong, we're in admin but there is no admin ID, this shouldn't happen
				return false;
			}
			$userId = (int)$this->user_data['id'];
			$sessionId = 0;
		} else {
			$adminId = 0;
			$userId = (int)$this->session->getUserId();
			$sessionId = ($userId)? 0: $this->session->getSessionId();
		}
		//let our site class know what the user id is.
		$this->site->userid = $this->site->classified_user_id = $userId;

		$sql = "SELECT * FROM ".geoTables::cart." WHERE `user_id` = ? AND `admin_id` = ? AND `session` = ? ORDER BY `order_item` ASC, `id` LIMIT 1";
		$query_data = array($userId, $adminId, $sessionId);
		$result = $this->db->GetRow($sql, $query_data);
		if ($result === false) {
			trigger_error('ERROR ORDER SQL: Sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
			trigger_error('DEBUG CART: Return False');
			return false;
		}
		if ($result) {
			//get order and make sure it's valid
			$order = ($result['order'])? geoOrder::getOrder($result['order']): 0;
			if (!is_object($order) || $order->getId() != $result['order']) {
				if ($result['order']) {
					//order id is set, but order is no good.  This could be because the entry in the order table (or
					//even the entire table) has been wiped, or it could be some sort of DB error.
					trigger_error('ERROR CART: Init session, getting order, order is no good! going to start a new cart session and inform the user.');
					//show an error message to the user, if this is being caused by some sort of DB error, we don't want
					//to be silent about it as it would make for a very hard problem to troubleshoot.
					$this->addErrorMsg('generic','Internal Error: Your cart contents have been reset, as we could not retrieve the details of your cart.  If you continually see this error message,
					please inform the site admin.');

					//kill the cart from the DB
					$this->db->Execute ("DELETE FROM ".geoTables::cart." WHERE `user_id` = ? AND `admin_id` = ? AND `session` = ? LIMIT 1", $query_data);
				}
				//make it create a new cart
				$result = $order = false;
			}
		}

		if ($result) {
			trigger_error('DEBUG CART: Existing cart session, restoring session.');
			//pre-existing session, initialize common vars.
			$this->cart_variables = $result;

			$this->order = $order;
			//re-set order buyer, in case switching from anon. to logged in
			$this->order->setBuyer($userId);
			//re-set order admin, in case something weird happened..
			$this->order->setAdmin($adminId);

			//set main type
			if (strlen($this->cart_variables['main_type'])){
				//force the main type if set in session, can't be switching around now!
				$this->main_type = $this->cart_variables['main_type'];
			}

			//get item
			$this->item = 0;
			if ($this->cart_variables['order_item'] > 0) {
				if (!$this->initItem($this->cart_variables['order_item'],false) || ($this->main_type != 'cart' && $this->item->getType() != $this->main_type)) {
					//what the.. order item is no good?
					trigger_error('DEBUG CART: Item not to be set, item: <pre>'.print_r($this->item,1).'</pre> main type: '.$this->main_type);
					$item = 0;
				}
			} elseif ($this->cart_variables['order_item'] == -1) {
				//this is a stand-alone cart, so the item is going to be the only item in the order.
				$items = $this->order->getItem();
				if (is_array($items)){
					foreach ($items as $item){
						if (is_object($item)){
							//since there may be other stuff in that array, like "sorted", go through each one and the first one that is
							//an object, that must be the only item attached to this order.
							$this->item = $item;
							break;
						}
					}
				}
				if ($trys == 0 && !is_object($this->item)) {
					//could not get item?  something went wrong, kill cart and try to init session again

					$this->removeSession();
					return $this->initSession(1, $restoreOnly);
				}
			}

			//touch the session, this will be used when session vars are saved..
			$this->cart_variables['last_time'] = geoUtil::time();

			$vars = null; //add vars here if needed to be passed
			//let the main type initialize anything else, including
			//making any calls to sub-types or whatever...
			geoOrderItem::callUpdate('geoCart_initSession_update',$vars);
		} else if (!$restoreOnly) {
			//create new
			if (!geoCart::createNewCart($userId, $adminId, $sessionId)) {
				//something went wrong with creation of cart
				echo "something went wrong!<br />";
				return false;
			}

			//NOTE: current step is not known here, and that is on purpose...

			$vars = null; //set vars here if needed.
			geoOrderItem::callUpdate('geoCart_initSession_new',$vars);
		} else {
			//just restoring, but nothing to restore...
			$this->order = $this->item = false;
		}
		if (!$restoreOnly && $this->order) {
			$billing_info = $this->order->getBillingInfo();
			if ($billing_info && is_array($billing_info)) {
				//set billing info
				$this->user_data['billing_info'] = $billing_info;
			}
		}

		//it gets this far, the cart session is started up.
		return true;
	}

	/**
	 * Way to create a brand new cart (and attached order).  This is used by init
	 * process and a few cart management tools in admin panel, it should not be
	 * called directly.
	 *
	 * @param int $userId
	 * @param int $adminId
	 * @param string $sessionId
	 * @param bool $useCart If true, will assign the normal cart vars as is
	 *   needed during the init process.
	 * @return int The cart ID for the created cart, or false on failure.
	 * @since Version 6.0.0
	 */
	public static function createNewCart ($userId, $adminId, $sessionId, $useCart = true)
	{
		$userId = (int)$userId;
		$adminId = (int)$adminId;
		$sessionId = trim($sessionId);

		trigger_error('DEBUG CART: No existing cart session, creating new session.');
		//sell session data not there yet...start over

		//create new order for this cart
		//create order object
		$order = new geoOrder();

		//set up order's info, if the individual order item wants to change any of these, they can.
		$order->setSeller(0);//seller is 0, it is the site doing the "selling", selling the ability to place the listing.
		$order->setBuyer($userId); //set buyer to be this user
		$order->setAdmin($adminId); //set admin ID on order
		$order->setParent(0);//this is main listing order

		$order->setCreated(geoUtil::time());

		if ($useCart) {
			$cart = geoCart::getInstance();

			$cart->order = $order;

			$cart->item = null;
			$cart->main_type = 'cart';
		}

		//serialize so there is an order id
		$order->serialize();
		$orderId = $order->getId();

		//insert into session table, so that we have a session ID
		$sql = "INSERT INTO ".geoTables::cart." (`session`, `user_id`, `admin_id`, `order`, `main_type`, `order_item`, `last_time`, `step`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$query_data = array($sessionId, $userId, $adminId, $orderId,'cart',0, geoUtil::time(),'');
		$result = DataAccess::getInstance()->Execute($sql, $query_data);

		if (!$result) {
			trigger_error('ERROR ORDER SQL: data: <pre>'.print_r($query_data, 1).'</pre> Sql: '.$sql.' Error Msg: '.DataAccess::getInstance()->ErrorMsg());
			trigger_error('DEBUG CART: Return False');
			return false;
		}
		$cartId = DataAccess::getInstance()->Insert_Id();
		if ($useCart) {
			//make sure session vars are set..
			$cart->cart_variables['id'] = $cartId;
			$cart->cart_variables['session'] = $sessionId;
			$cart->cart_variables['user_id'] = $userId;
			$cart->cart_variables['admin_id'] = $adminId;
			$cart->cart_variables['order'] = $orderId;
			$cart->cart_variables['main_type'] = $cart->main_type;
			$cart->cart_variables['order_item'] = 0;
			$cart->cart_variables['last_time'] = geoUtil::time();
			$cart->cart_variables['step'] = ''; //step not known yet, it's calculated later.
		}

		//NOTE: current step is not known here, and that is on purpose...
		return $cartId;
	}

	/**
	 * Performs the given action, usually this is done in init()
	 *
	 * @param string $action
	 */
	public function performAction ($action)
	{
		trigger_error('DEBUG CART: Top of performAction('.$action.')');
		switch ($action) {
			case 'process':
				$this->action = 'process'; //process a cart page
				$this->_doProcess = true;
				break;

			case 'cancel':
				if (($this->main_type != 'cart' || $this->isStandaloneCart()) && is_object($this->item)) {
					//need to cancel adding the current item, so remove it from the cart
					//by running deleteProcess() which will remove the current item
					$this->action = 'cancel';
					$this->actions_performed[]['cancel'] = $this->item->getType();
					$this->deleteProcess();
				}

				break;

			case 'new':
				$main_type = false;

				if (isset($_GET['main_type']) && strlen($_GET['main_type']) > 0){
					$main_type = trim($_GET['main_type']);
				} else {
					//see if there is only one "button" that shows up, if there is,
					//use that button
					$buttons = geoOrderItem::callDisplay('geoCart_cartDisplay_newButton', null, 'string_array');
					if (count($buttons) == 1) {
						$types = array_keys($buttons);
						$main_type = $types[0];
					}
				}
				if ($main_type) {
					//see if it needs it's own cart

					$this->main_type = trim($main_type);

					//figure out login vars in case we need to pass them to login form
					require_once CLASSES_DIR . 'authenticate_class.php';
					$encodedUri = Auth::generateEncodedVars();

					if ($this->initItem(0, true, $encodedUri)){
						//initialized new item
						$this->cart_variables['main_type'] = $this->main_type;
						$this->action = 'new';
						$this->actions_performed[]['new'] = $this->main_type;
					} else {
						trigger_error('DEBUG CART: action new: init failed.');
						//init failed, set main type to cart
						$this->main_type = $this->cart_variables['main_type'] = 'cart';
						if ($this->cart_variables['order_item'] > 0){
							//do not set if 0 or -1 which is special case for items that require to be the only one in the checkout.
							$this->cart_variables['order_item'] = 0;
						}
						if ($this->cart_variables['order_item'] == 0){
							//only if the order item is not -1
							$this->item = null;
						}
					}
				}
				break;

			case 'edit':
				if (isset($_GET['item']) && is_numeric($_GET['item'])){
					$item_id = intval($_GET['item']);
					if ($item_id && $this->initItem($item_id,false)){
						$this->action = 'edit';
						$this->main_type = $this->cart_variables['main_type'] = $this->actions_performed[]['edit'] = $this->item->getType();
					}
				}
				break;

			case 'delete':
				if (isset($_GET['item']) && is_numeric($_GET['item'])){
					$item_id = intval($_GET['item']);
					$old_action = $this->action;
					$this->action = 'delete';
					$this->actions_performed[]['delete'] = $this->main_type;
					if ($item_id && $this->initItem($item_id)){
						$this->main_type = $this->cart_variables['main_type'] = $this->item->getType();
						$this->all_steps = array('delete', 'cart');
						$this->_skipInitSteps = true;
						$this->current_step = $this->cart_variables['step'] = 'delete';
						$this->_doProcess = true;//process deleting
					} else {
						$this->action = $old_action;
					}
				}
				break;

			case 'preview':
				if (isset($_GET['item']) && is_numeric($_GET['item'])){
					$item_id = intval($_GET['item']);
					//preserve current item

					if ($item_id){
						if ($this->item) {
							$oldItemId = $this->item->getId();
						}
						if ($this->initItem($item_id,false)) {
							$this->action = 'preview';
							$this->main_type = $this->cart_variables['main_type'] = $this->actions_performed[]['preview'] = $this->item->getType();
							$this->all_steps = array('preview','cart');
							$this->_skipInitSteps = true;
							$this->current_step = $this->cart_variables['step'] = 'preview';
							$this->_doProcess = false;
						}
					}
				}
				break;

			default:

				break;
		}
		trigger_error('DEBUG CART: End of performAction, action: '.$this->action);
		return true;
	}

	/**
	 * Initializes an item, either creating a new one or restoring an existing one
	 * in the cart, and sets {@link geoCart::item} to the item.
	 *
	 * @param int $item_id The item id to restore, or if 0, will create a new item
	 *  with the type specified by the currently set {@link geoCart::main_type}
	 * @param bool $force_parent if true, will make sure the item is a parent.
	 * @param bool|string $enforceAnon Only used if creating a new item.
	 *  If true (strict), will call {@link geoOrderItem::enforceAnonymous} passing
	 *   "a*is*cart" as first param.
	 *  If string, will call {@link geoOrderItem::enforceAnonymous} passing $enforceAnon
	 *   as first param.  Note that the empty string or null is allowed.
	 *  If false (strict), will not perform any anonymous checks.
	 * @return bool true if item was initialized, false otherwise.
	 */
	public function initItem ($item_id = 0, $force_parent = true, $enforceAnon = false)
	{
		//clean vars
		$item_id = intval($item_id);
		//create default order item
		$this->item = null;
		if ($item_id > 0) {
			//get item based on existing item id
			$existing_items = $this->order->getItem();
			foreach ($existing_items as $item) {
				if (is_object($item) && $item->getId() == $item_id) {
					//matches item in current order
					$this->item = $item;
					if (method_exists($item,'geoCart_initItem_restore')) {
						if (!$this->item->geoCart_initItem_restore()) {
							//the function called above must have decided to end it's own life early, so
							//don't proceed with initializing it.
							$this->item = null;
							$this->main_type = $this->cart_variables['main_type'] = 'cart';
							$this->cart_variables['order_item'] = 0;
							return false;
						}
					}
					if ($this->item->geoCart_initItem_forceOutsideCart()) {
						$this->cart_variables['order_item'] = -1;
					} else {
						$this->cart_variables['order_item'] = $this->item->getId();
					}
					//if price plan and category are set, set the cart's price plan and category
					$price_plan = intval($this->item->getPricePlan());
					if ($price_plan) {
						$category = intval($this->item->getCategory());
						$this->setPricePlan($price_plan, $category);
					}
					return true;
				}
			}
			trigger_error('DEBUG CART: Init Item Return False, item id: '.$item_id.' order items: <pre>'.print_r($existing_items,1).'</pre>');
			return false;
		}
		//id not set, so must be new item
		if ($this->cart_variables['order_item'] == -1) {
			trigger_error('DEBUG CART: Returning false in initItem, cannot create new item if cart is set to order_item = -1.');
			return false;
		}
		if (!$this->main_type || $this->main_type == 'cart') {
			//main type not set, can't create a new of nothing!
			trigger_error('DEBUG CART: Returning false in initItem, cannot create new item if main type not set or set to cart.');
			return false;
		}
		//enforce whether needs to be logged in
		if ($enforceAnon !== false) {
			//NOTE: DO NOT change surrounding if statement to "if ($enforceAnon)", as
			//this will not allow an empty string to be used to pass to the enforce
			//anonymous function!

			//if enforceAnon is not a strict true, use that to pass to enforceAnonymous call,
			//otherwise use a*is*cart
			$loginVar = (($enforceAnon===true)? 'a*is*cart' : ''.$enforceAnon);

			if (geoOrderItem::enforceAnonymous($this->main_type, $loginVar)) {
				//no-anonymous allow was just enforced, login page was displayed,
				//so we need to exit.  Do not pass go.  Do not collect 200.
				include GEO_BASE_DIR . 'app_bottom.php';
				exit;
			}
		}

		$item = geoOrderItem::getOrderItem($this->main_type);
		if (!(is_object($item) && $item->getType() == $this->main_type && ($force_parent && count(geoOrderItem::getParentTypesFor($this->main_type)) == 0))) {
			trigger_error('DEBUG CART: Init Item Return False, main type: '.$this->main_type.' item: <pre>'.print_r($item,1).'</pre>');
			return false;
		}

		$this->item = $item;
		if (method_exists($this->item,'geoCart_initItem_new')) {
			if (!$this->item->geoCart_initItem_new()) {
				//the function called above must have decided to end it's own life early, so
				//don't proceed with initializing it.
				$id = $this->item->getId();
				if ($id) {
					geoOrderItem::remove($id);
					//detach from order, just in case it was added by something
					//internal to the order item
					$this->order->detachItem($id);
				}
				$this->item = null;
				$this->main_type = $this->cart_variables['main_type'] = 'cart';
				$this->cart_variables['order_item'] = 0;
				return false;
			}
		}
		//item is good, attach new blank item to order and set up all teh stuff for it.
		if ($this->item->geoCart_initItem_forceOutsideCart()) {
			//this is a new item, and the item specifies to be the only one...
			return $this->_initNewStandaloneCart($item);
		}
		//attach it to the order
		$item->setOrder($this->order);
		$this->order->addItem($item);
		$this->order->save();
		$this->cart_variables['order_item'] = $this->item->getId();
		return true;
	}
	/**
	 * Displays the message saying that they are already in the middle of doing something, and gives
	 * them the option to either continue with it, or to cancel and remove it, and start on the new
	 * thing they are trying to do.
	 *
	 */
	public function displayCartInterruption ()
	{
		/*$msgs = $this->db->get_text(true, 10202);
		$this->addErrorMsg('cart_error',$msgs[500258]);
		*/

		$this->site->page_id = 10202;
		$this->site->get_text();

		$view = geoView::getInstance();
		$tpl_vars = $this->getCommonTemplateVars();

		$vars = array('action' => 'interrupted', 'step' => $this->cart_variables['step']);
		$action = geoOrderItem::callDisplay('getActionName',$vars,'',$this->main_type);

		$tpl_vars['interrupted_action'] = ($action)? $action: $cart->site->messages[500572];

		$vars = array ('action' => $_GET['action'], 'step' => $_GET['step']);
		if (isset($_GET['main_type']) && strlen(trim($_GET['main_type'])) > 0) {
			$action = geoOrderItem::callDisplay('getActionName',$vars,'',$_GET['main_type']);
		} else {
			$action = '';
		}
		//figure out the URL for the new action
		if (defined('IN_ADMIN')) {
			$url = 'index.php?page=admin_cart&amp;userId='.$this->user_data['id'].'&amp;action_special=cancel_and_go';
		} else {
			$url = $this->db->get_site_setting('classifieds_file_name').'?a=cart&amp;action_special=cancel_and_go';
		}
		foreach ($_GET as $key => $value) {
			$ignore = array('a','action_special');
			if (!in_array($key,$ignore)) {
				$url .= "&amp;{$key}=$value";
			}
		}


		$tpl_vars['new_action'] = ($action)? $action: $cart->site->messages[500571];
		$tpl_vars['new_action_url'] = $url;

		$view->setBodyTpl('display_cart/action_interrupted.tpl','','cart')
			->setBodyVar($tpl_vars);

		$this->site->display_page();
		return;
	}
	/**
	 * Used internally to initialize a new standalone cart
	 *
	 * @param geoOrderItem $item
	 * @return boolean
	 */
	private function _initNewStandaloneCart ($item)
	{
		//make sure the current order has nothing in it.
		trigger_error('DEBUG CART: Top of initNewStandaloneCart.');
		if ($this->cart_variables['order_item'] == -1) {
			trigger_error('DEBUG CART: Returning false, current order_item is -1 so can\'t add to this cart..');
			return false;
		}

		if (!is_object($this->order)) {
			trigger_error('DEBUG CART: Returning false, order is not an object.');
			return false;
		}

		//need to create new cart!  Duplicate the initSession but with a few changes specific for stand-alone carts
		$this->order = null;
		trigger_error('DEBUG CART: Creating new stand-along cart session.');

		//create new order for this cart
		//create order object
		$order = new geoOrder();

		//set up order's info, if the individual order item wants to change any of these, they can.
		$order->setSeller(0);//seller is 0, it is the site doing the "selling", selling the ability to place the listing.
		$order->setBuyer($this->user_data['id']); //set buyer to be this user
		$order->setParent(0);//this is main listing order
		$order->setCreated(geoUtil::time());

		//serialize so there is an order id
		$order->serialize();
		$order_id = $order->getId();

		$this->order = $order;

		$this->item = $item;
		$this->item->setOrder($this->order);
		$this->order->addItem($this->item);
		//set main type
		$this->main_type = $this->item->getType();//'cart';
		$userId = $this->user_data['id'];
		$adminId = (defined('IN_ADMIN'))? $this->session->getUserId() : 0;
		$sessionId = ($userId > 0 || defined('IN_ADMIN'))? 0: $this->session->getSessionId();

		//insert into session table, so that we have a session ID
		$sql = "INSERT INTO ".geoTables::cart." (`session`, `user_id`, `admin_id`, `order`, `main_type`, `order_item`, `last_time`, `step`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		//set order_item to -1 to indicate this is stand-alone.
		$query_data = array($sessionId, $userId, $adminId, $this->order->getId(),'cart',-1, geoUtil::time(),'');
		$result = $this->db->Execute($sql, $query_data);

		if (!$result) {
			trigger_error('ERROR ORDER SQL: data: <pre>'.print_r($query_data, 1).'</pre> Sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
			trigger_error('DEBUG CART: Return False');
			return false;
		}

		//make sure session vars are set..
		$this->cart_variables['id'] = $this->db->Insert_ID();
		$this->cart_variables['session'] = $sessionId;
		$this->cart_variables['user_id'] = $userId;
		$this->cart_variables['order'] = $order_id;
		$this->cart_variables['main_type'] = $this->main_type;
		$this->cart_variables['last_time'] = geoUtil::time();
		$this->cart_variables['step'] = '';
		$this->cart_variables['order_item'] = -1;
		return true;
	}

	/**
	 * Best way to tell if the cart is currently in "standalone" mode, meaning
	 * there can only be 1 thing in the cart right now, and that thing is already
	 * in there.
	 *
	 * @return bool
	 */
	public function isStandaloneCart ()
	{
		return ($this->cart_variables['order_item'] == -1);
	}

	/**
	 * Returns whether or not the cart is specifically a recurring cart or not.
	 *
	 * @return bool
	 * @since Version 4.1.0
	 */
	public function isRecurringCart ()
	{
		return ($this->isRecurringPossible() && $this->isStandaloneCart() && $this->item && $this->item->isRecurring());
	}

	/**
	 * Checks to see if recurring billing is even possible given the current
	 * enabled payment gateways and whether any of them can handle processing
	 * recurring billing.
	 *
	 * @return bool
	 * @since Version 4.1.0
	 */
	public function isRecurringPossible ()
	{
		//checks to see if there are any payment gateways that are recurring
		$gateways = geoPaymentGateway::getPaymentGatewayOfType('recurring');
		return ($gateways && count($gateways) > 0);
	}

	/**
	 * Whether or not the "current" step (or step specified in parameter) is
	 * being combined with others or not.  If this is "true" then your display
	 * step should not exit on it's own...
	 *
	 * @param string $step The step to see if is combined, step defaults to the current
	 *   step if none is set currently.
	 * @return boolean
	 * @since Version 7.2.0
	 */
	public function isCombinedStep ($step = null)
	{
		if ($step===null) {
			$step = $this->current_step;
		}
		return ($step == 'combined' || in_array($step, $this->combined_steps));
	}

	/**
	 * Whether the step is one of the steps currently loaded.  Note that this will
	 * return false if the step is a combined step, if wish to check that as well,
	 * use {@link geoCart::isCombinedStep()}
	 *
	 * @param string $step
	 * @return boolean
	 * @since Version 7.2.0
	 */
	public function isStep ($step)
	{
		return in_array($step, $this->all_steps);
	}

	/**
	 * Initializes the steps of the cart
	 * @return boolean
	 */
	protected function initSteps ()
	{
		//make sure step is good
		$session_step = $this->cart_variables['step'];

		//initialize all steps if not already started.
		$this->all_steps = (isset($this->all_steps) && is_array($this->all_steps))? $this->all_steps: array();
		if (($this->action == 'edit' || $this->action == 'new' || $session_step !== 'cart') && $this->main_type !== 'cart' && is_object($this->item)) {
			//let the main order set the steps, it must get an instance of geoCart and set $cart->all_steps = array ()
			trigger_error('DEBUG CART: Steps being set by item.');

			geoOrderItem::callUpdate('geoCart_initSteps',false,$this->item->getType());

			$this->all_steps = $this->_validateStep($this->all_steps); //make sure all steps set are valid
			if (!is_array($this->all_steps)) {
				//Doh!  didn't return expected result...
				if (strlen($this->all_steps) && $this->_validateStep($this->all_steps)) {
					$this->all_steps = array ($this->all_steps); //all_steps was set to a string, but string is valid step so just convert it to array
				} else {
					$this->all_steps =array(); //just in case, init all steps to have no non-built in steps
				}
			}
			if (geoOrderItem::callDisplay('geoCart_initSteps_addOtherDetails',null,'bool_true',$this->main_type)) {
				$this->all_steps[] = 'other_details';	//collect misc. settings, built-in step
			}
			//keep track of "standard" steps as they may change for combined...
			$this->all_steps_standard = $this->all_steps;

			//now combine steps if needed
			if (geoOrderItem::callDisplay('geoCart_canCombineSteps',null,'bool_true',$this->main_type)) {
				//for simplicity, logic in it's own method...  Combine steps that
				//need to be combined.
				$this->_combineSteps();
			}

			//last step of process, the cart view.
			$this->all_steps[] = 'cart'; //display contents of cart
		} else {
			trigger_error('DEBUG CART: Not doing anything with item, using built in cart steps.');

			$this->main_type = $this->cart_variables['main_type'] = 'cart';
			$this->all_steps = array();
			//main type is cart, make sure item is not still set
			if ($this->cart_variables['order_item'] != -1) {
				$this->item = null;
			}
			$this->cart_variables['order_item'] = (($this->cart_variables['order_item'] >= 0)? 0: -1);
			//for these steps, main_type = cart
			//add built-in steps - in cart, so first step is cart view
			$this->all_steps[] = 'cart'; //display contents of cart
			if ($this->getCartTotal() > 0 || $this->get('no_free_cart')) {
				//only if there is something to pay for
				$this->all_steps[] = 'payment_choices'; //enter details of payment
			}
			if (is_array($this->order->getItem()) && count($this->order->getItem())) {
				$this->all_steps[] = 'process_order'; //sends payment through payment gateway to process, only if there are items in the cart.
			}
		}
		if ($this->action == 'edit' || $this->action == 'new') {
			//if editing or adding new item, it has to be the first step.
			$this->current_step = $this->cart_variables['step'] = $this->all_steps[0];
			$this->_doProcess = false;
			$this->initStepsView();
			return true;
		}
		if (!in_array($session_step, $this->all_steps) && $this->isCombinedStep($session_step)) {
			//session step is combined...
			$session_step = 'combined';
		}
		if (!in_array($session_step,$this->all_steps)) {
			//just in case session gets weird
			$session_step = $this->all_steps[0];
		}
		//step = Calculate current step.

		//Allow for linking to previous steps to edit things

		$force = (isset($_GET['step']))? $_GET['step']: 0;
		$step = 0;
		$force_used = 0;
		if (!$force) {
			//no need to go through all that if there is nothing set in the URL
			$step = $session_step;
		} else {
			//go through each of the allowed steps, and when we get to either the one set in the
			//url, or the one set in the session, make that one be the one used.
			//Since it goes in order, it will prevent people trying to go forward and skipping steps,
			//they will only be able to go backwards from what is set for their session.
			foreach ($this->all_steps as $s){
				if ($session_step == $s){
					$step = ($force_used)? $step: $s;
					break; //step from session found, don't go further
				}
				if ($force_used){
					//count how many far back
					$forced_used ++;
				} elseif ($force == $s){
					//the forced step needs to be before the current step set in the session
					//and it is, if it got to this
					$step = $s;
					$force_used = 1;
					//Keep going in loop, to see how far back this is from the main one.
				}
			}
		}
		if (!$step) {
			//Err step not found?  This should be impossible to get to, there has to be some logic problem...
			$this->initStepsView(); //still let view know current steps...
			return false;
		}
		$this->current_step = $this->cart_variables['step'] = $step;
		//figure out whether or not to check vars and process, or to just display..
		if ($this->action == 'process' && (!$force || $force == $session_step || ($force_used && $force_used < 2) )) {
			//only checkVars/process if:
			//action == process AND
			//(
			//	step not set in URL OR
			//	step in URL is same as in session OR
			//	(
			//		step in URL is step being used AND
			//		step in URL is less than 2 before the step set in session
			//	)
			//)
			$this->_doProcess = true;
		}
		$this->initStepsView();
		return true;
	}

	/**
	 * Used internally to combine the steps that need to be combined into a single
	 * built-in step to load all the combined steps.
	 */
	private function _combineSteps ()
	{
		//can combine steps, see if we do actually combine them
		$planItem = geoPlanItem::getPlanItem($this->main_type,0,0);
		if (!$planItem) {
			//failsafe, can't do anything
			return;
		}
		$pre = geoCart::COMBINED_PREFIX;

		$combine = $planItem->get($pre.'combine', 'none');

		if ($combine=='none') {
			//do not combine anything
			return;
		}
		$combined = array();
		//reset all steps, then it will re-populate "all possible steps"
		$this->all_steps = array();
		//re-load all steps but with option to load all...
		geoOrderItem::callUpdate('geoCart_initSteps',true,$this->item->getType());

		$this->all_steps = $this->_validateStep($this->all_steps); //make sure all steps set are valid
		if (!is_array($this->all_steps)) {
			//Doh!  didn't return expected result...
			if (strlen($this->all_steps) && $this->_validateStep($this->all_steps)) {
				$this->all_steps = array ($this->all_steps); //all_steps was set to a string, but string is valid step so just convert it to array
			} else {
				$this->all_steps =array(); //just in case, init all steps to have no non-built in steps
			}
		}
		//we are not checking "if" other details should show, this is loading the
		//"possibility" for them to load combined in case it changes based on category
		$this->all_steps[] = 'other_details';

		if ($combine == 'all') {
			//combine ALL of the steps!
			$combined = $this->all_steps;
		} else if ($combine == 'selected') {
			//only combine the specific ones...
			$combined = (array)$planItem->get($pre.'combined');
		}
		if (!count($combined)) {
			//nothing saved in combined?  restore it to the "normal" steps
			$this->all_steps = $this->all_steps_standard;
			//nothing else to do if there are no steps to combine...
			return;
		}

		$all_steps = $combined_steps = array();
		$cStarted = $cEnded = false;

		//block all built in steps, except for the other_details...
		$block_combine = array_diff($this->built_in_steps, array('other_details'));

		foreach ($this->all_steps as $step) {
			//NOTE: at this point, $this->all_steps contain even steps that may not
			//normally be used for this category / group...
			if ($cEnded) {
				//just add the rest to the all_steps array, since it is after ones
				//that are combined
				if (in_array($step, $this->all_steps_standard)) {
					//this is one of the normal steps...  If it failed this check,
					//it is not normally loaded based on current category or other factors
					$all_steps[] = $step;
				}
				continue;
			}
			if (in_array($step, $block_combine)) {
				if ($cStarted) {
					//this is cart page, and started combining them... make sure it is over
					$cEnded = true;
				}
				if (in_array($step, $this->all_steps_standard)) {
					//this is one of the normal steps...  If it failed this check,
					//it is not normally loaded based on current category or other factors
					$all_steps[] = $step;
				}
				continue;
			}
			if ($cStarted && in_array($step, $this->uncombined_steps)) {
				//This is a special case...  cannot combine this step, but the step
				//can be pushed to be after the combined step, without ending the
				//combined steps
				if (in_array($step, $this->all_steps_standard)) {
					$all_steps[] = $step;
				}
				continue;
			}
			if (in_array($step, $combined)) {
				//make sure it knows combined is started
				if (!$cStarted) {
					//it hasn't started yet!
					//insert combined step here
					$all_steps[] = 'combined';
					//now remember that it is started
					$cStarted = true;
				}
				//NOTE: this adds steps to combined even if they are not loaded for
				//the current category or other details... That way if those details
				//change (like user selects category that allows media), the step
				//can be populated
				$combined_steps[] = $step;
				continue;
			}
			if ($cStarted) {
				//it was started... but this one is not being combined, so now
				//it is over...
				$cEnded = true;

				//insert the step in normal array
				if (in_array($step, $this->all_steps_standard)) {
					//this is one of the normal steps...  If it failed this check,
					//it is not normally loaded based on current category or other factors
					$all_steps[] = $step;
				}
				continue;
			}
			//not started yet, add it to steps
			if (in_array($step, $this->all_steps_standard)) {
				//this is one of the normal steps...  If it failed this check,
				//it is not normally loaded based on current category or other factors
				$all_steps[] = $step;
			}
		}
		$this->all_steps = $all_steps;
		$this->combined_steps = $combined_steps;
	}

	/**
	 * Used internally
	 * @var array
	 * @internal
	 */
	private $_stepLabels = array();
	/**
	 * Sets the steps info in the geoView class, so that templates can display
	 * current step and progress.
	 *
	 * @param bool $returnSteps If true, instead of setting steps on the view object,
	 *   will return the steps in an array. {@since Version 7.2.0}
	 * @param bool $include_uncombined If false, will skip steps that are "not combined"
	 *   {@since Version 7.2.0}
	 * @return array|null If $returnSteps is true, returns an array, otherwise
	 *   does not return anything.
	 */
	public function initStepsView ($returnSteps = false, $include_uncombined = true)
	{
		//Loop through all the steps
		$viewSteps = array();
		//get text for main cart, most items have text for steps set in this page
		$this->site->messages = $this->db->get_text(true,10202);
		foreach ($this->all_steps as $key => $step) {
			if (!$include_uncombined && in_array($step, $this->uncombined_steps)) {
				//this one not combined, skip it
				continue;
			}
			$viewSteps[$step] = $this->labelStep($step);
		}
		if ($returnSteps) {
			return $viewSteps;
		}
		$view = geoView::getInstance();
		$view->cartSteps = $viewSteps;
		$view->currentCartStep = $this->current_step;
	}

	/**
	 * Add a step to the cart system, with the option to insert the step
	 * before or after an already added step, or at the beginning of all
	 * the currently added steps.
	 *
	 * Designed to be used in order items, in the init steps function.
	 *
	 * @param string $step The step to add, in the format item_name:step_name
	 * @param string $where
	 * @param string $otherStep
	 * @param bool $canCombine If false, will not be able to combine this step
	 *   with others.  Will be moved to be "after" all combined steps, so needs
	 *   to be able to work "after" all combinable steps.  Also item needs to be
	 *   able to be previewed without this step taking place. {@since Version 7.2.0}
	 */
	public function addStep ($step, $where = self::LAST_STEP, $otherStep = null, $canCombine=true)
	{
		if ($this->_validateStep($step)) {
			switch ($where) {
				case self::FIRST_STEP:
					//push step to the front of the steps
					array_unshift($this->all_steps, $step);
					break;

				case self::AFTER_STEP:
					//insert step after specified $otherStep, if $otherStep is
					//already added.
					if (in_array($otherStep, $this->all_steps)) {
						$all = array();
						foreach ($this->all_steps as $key => $val) {
							$all [] = $val;
							if ($val == $otherStep) {
								$all[] = $step;
							}
						}
						$this->all_steps = $all;
					}
					break;

				case self::BEFORE_STEP:
					//insert step before specified $otherStep, if $otherStep is
					//already added.
					if (in_array($otherStep, $this->all_steps)) {
						$all = array();
						foreach ($this->all_steps as $key => $val) {
							if ($val == $otherStep) {
								$all[] = $step;
							}
							$all [] = $val;
						}
						$this->all_steps = $all;
					}
					break;

				case self::REPLACE_STEP:
					//replaces an existing step
					if (in_array($otherStep, $this->all_steps)) {
						$all = array();
						foreach ($this->all_steps as $key => $val) {
							$all[] = ($val == $otherStep)? $step : $val;
						}
						$this->all_steps = $all;
					}
					break;

				case self::LAST_STEP:
					//break ommited on purpose

				default:
					//Normal case, just add step to the end of the list
					$this->all_steps[] = $step;
					break;
			}
			if (!$canCombine) {
				//cannot combine this one, keep track of it..
				$this->uncombined_steps[] = $step;
			}
		}
	}

	/**
	 * Determines whether a given step is in-use during the current cart process.
	 *
	 * @param String $name The name of the step to check
	 * @return bool true if the step is active, false otherwise
	 * @since Version 6.0.7
	 */
	public function stepIsActive($name)
	{
		return in_array($name, $this->all_steps);
	}

	/**
	 * Retrieve the current step set for the cart.
	 * @return string
	 * @since Version 7.2.0
	 */
	public function getCurrentStep ()
	{
		return $this->current_step;
	}

	/**
	 * Gets the next step after the current one.
	 *
	 * @param string $currentStep If specified, will check the next step after this one,
	 *   otherwise will default to current step set in the cart.  {@since Version 7.2.0}
	 * @param bool $skipUncombined If true, skip over all steps that are set to
	 *   not be "combinable" (like JIT step or anon pass step).  {@since Version 7.2.0}
	 * @return string
	 */
	public function getNextStep ($currentStep = null, $skipUncombined = false)
	{
		if ($currentStep === null) {
			$currentStep = $this->current_step;
		}

		$run_next = 0;
		foreach ($this->all_steps as $s){
			if ($run_next) {
				//this is the next step in the list.
				if ($skipUncombined && in_array($s, $this->uncombined_steps)) {
					//this is uncombined step, skip this one
					continue;
				}
				return ($s);
			}
			if ($s == $currentStep){
				//this is current step, so the next one is the one to actually display
				$run_next = 1;
				trigger_error('DEBUG CART: next step is it..'.$s);
			}
		}
		//no next step, return current step?
		return $currentStep;
	}

	/**
	 * Gets the previous step before the current one.  Note that this will not
	 * work if cart has switched from process of "adding" something to the cart,
	 * to the built-in cart steps.
	 *
	 * If there is no step before the current one, or the current step is not in
	 * the list of steps, will return null.
	 *
	 * @param string $currentStep Will use this as the current step, defaults to
	 *   the cart's current step if not specified.
	 * @return string|null The step before the currentStep, or null if there is
	 *   a problem or the current step is the first one.
	 * @since Version 7.2.3
	 */
	public function getPreviousStep ($currentStep = null)
	{
		if ($currentStep === null) {
			$currentStep = $this->current_step;
		}
		$prev_step = null;
		$foundCurrent = false;
		foreach ($this->all_steps as $step) {
			if ($step == $currentStep) {
				$foundCurrent = true;
				break;
			}
			$prev_step = $step;
		}
		return ($foundCurrent)? $prev_step : null;
	}

	/**
	 * NOT Meant for use in initSteps functions in order items, use
	 * addStep for that.
	 *
	 * Inserts a step right before the current one, then sets it up
	 * so that the inserted step will be the next called step when
	 * geoCart::getNextStep() is called.
	 *
	 * Designed to be used in order items in the check vars or process steps
	 * in order to change what step is actually displayed.
	 *
	 * @param string $step
	 */
	public function insertStep ($step)
	{
		$steps = $this->all_steps;
		$current = $this->current_step;

		//find index of current step
		$location = array_keys($steps, $current);
		$current_key = $location[0];

		//save everything after it
		$end = array_splice($steps, $current_key);


		//if array now empty, pad it so we don't break things
		if(!count($steps)) {
			$steps[] = "cart";
		}

		//append new step
		$steps[] = $step;

		//append saved stuff
		$steps = array_merge($steps, $end);

		//make changes to class vars
		$this->all_steps = $steps;

		//make the system think it just did the step before the one it actually did
		//so it getNextStep's into the one we just added
		$current_key--;
		if($current_key < 0) {
			$current_key = 0;
		}
		$this->current_step = $steps[$current_key];

		$this->cart_variables['step'] = $this->current_step;
		$this->save();
	}

	/**
	 * Gets the array of all the steps for the current item.
	 *
	 * @return array
	 * @since Version 7.2.0
	 */
	public function getAllSteps ()
	{
		return $this->all_steps;
	}

	/**
	 * Clears all the steps.  This is used mainly in the admin panel setting pages.
	 *
	 * @since Version 7.2.0
	 */
	public function clearAllSteps ()
	{
		$this->all_steps = $this->_stepLabels = array();
	}

	/**
	 * Used during checkVars to signify there is a problem, so do not proceed to next step yet.
	 *
	 * Can also be used in process section, but discouragede except in situations like when charging
	 * a credit card, and the transaction doesn't go through
	 *
	 * @return geoCart For easy chaining.
	 */
	public function addError ()
	{
		//throw new Exception('yo'); //see where is adding an error
		$this->errors++;
		return $this;
	}

	/**
	 * Add an error message that can be used in other areas.  Typically used in checkVars step to record
	 * what the error was, so that in display step it can display an appropriate message.
	 *
	 * Note that using this function alone DOES NOT prevent it from proceeding to the next step, you will
	 * need to use addError() to keep it from proceeding to the next step, and addErrorMsg to let the display
	 * step know what is wrong.
	 *
	 * @param string $error_name How error message is accessed, handy to specify an error message to be displayed
	 *  next to a specific input field
	 * @param string $msg
	 */
	public function addErrorMsg ($error_name,$msg)
	{
		$this->error_msgs[$error_name] = $msg;
	}

	/**
	 * Gets the specified item from the Cart's registry.  Should only be used for settings that
	 * are global to the entire cart.  This is not typical, usually you would set the setting
	 * on an individual order item, not the entire cart.
	 *
	 * @param string $setting
	 * @param mixed $default If setting not set for cart, return this value instead
	 * @return Mixed the specified setting, or the $default if setting not found
	 */
	public function get ($setting, $default = false)
	{
		$this->_initReg();
		return $this->registry->get($setting, $default);
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
		$this->_initReg();
		return $this->registry->set($item, $value);
	}

	/**
	 * Checks the input vars for problems, like if user didn't fill in a required field.
	 *
	 * Acutally it leaves it up to each order item to do it.
	 *
	 * @param string $step
	 * @return bool true if everything is good, false otherwise.
	 */
	public function checkVars ($step = '')
	{
		if (strlen($step) > 0 && $this->_validateStep($step)) {
			//set current step to the step
			$this->current_step = $step;
		}

		if (in_array($this->current_step,$this->built_in_steps)) {
			//built in step
			$function_name = $this->current_step . 'CheckVars';
			$this->$function_name();
		} else {
			//Check vars specific to the current step
			$parts = $this->_getStepParts($this->current_step);
			$function_name = $parts['step'].'CheckVars';
			$item_name = $parts['item_name'];
			geoOrderItem::callUpdate($function_name,null,$item_name);
		}
		if (isset($this->site->error) && $this->site->error > 0) {
			$this->addError();
		}
		if ($this->errors > 0) {
			//errors generated, return false.
			return false;
		}
		//no errors generated, return true.
		return true;
	}

	/**
	 * Processes a step, or rather, calls the process step for the current order
	 * item.
	 *
	 * @param string $step The step to process (will override the current step)
	 */
	public function processStep ($step = '')
	{
		trigger_error('DEBUG CART: processStep');
		if (strlen($step) > 0 && $this->_validateStep($step)) {
			//set current step to the step
			$this->current_step = $step;
		}
		if (in_array($this->current_step,$this->built_in_steps)) {
			$function_name = $this->current_step . 'Process';
			$this->$function_name();
		} else {
			//process specific to the current step
			$parts = $this->_getStepParts($this->current_step);
			$function_name = $parts['step'].'Process';
			$item_name = $parts['item_name'];

			geoOrderItem::callUpdate($function_name,null,$item_name);
		}

		//Check to see if doing inline preview, if so the method called will add an
		//error to prevent continuing to next step
		$this->showPreviewBox(true);

		//After processing, set step to be next step, as long as there is no errors
		//NOTE:  it is BAD PRACTICE to do var checking in process step, it only
		// checks for errors for things that could only go wrong when processing, for
		// instance if an error happens when processing a CC order.
		if ($this->errors == 0 && !$this->_skipNextStep) {
			$this->current_step = $this->getNextStep();
			if ($this->current_step == 'cart') {
				if ($step !== 'cart' && $this->main_type !== 'cart') {
					//we are transitioning from an item's steps to displaying the
					//cart, see if we should actually skip the cart...
					$this->skipCart();
				}
				//make sure to reset the steps, mostly for the benifit of the
				//display of the current steps in the cart
				$this->all_steps = array();
				//add built-in steps - in cart, so first step is cart view
				$this->all_steps[] = 'cart'; //display contents of cart
				if ($this->getCartTotal() > 0) {
					//only if there is something to pay for, add payment choices automatically
					$this->all_steps[] = 'payment_choices'; //enter details of payment
				}
				if (is_array($this->order->getItem()) && count($this->order->getItem())) {
					$this->all_steps[] = 'process_order'; //sends payment through payment gateway to process, only if there are items in the cart.
				}
			}
			if (in_array($this->current_step,array_diff($this->built_in_steps, array('other_details','combined')))) {
				//this is a built in step with no main item needed
				$this->main_type = 'cart';
				$this->cart_variables['main_type'] = 'cart';
				$this->cart_variables['order_item'] = (($this->cart_variables['order_item'] >= 0)? 0: -1);
			}
			//make sure the step is set for the cart vars
			$this->cart_variables['step'] = $this->current_step;
		}
	}

	/**
	 * Set the current step for the cart.  This is usually only used in special
	 * cases, most of the time should let the cart set the step automatically.
	 *
	 * @param string $step
	 * @since Version 7.2.0
	 */
	public function setCurrentStep ($step)
	{
		$step = trim($step);
		if (!$this->_validateStep($step)) {
			//invalid step
			return;
		}
		$this->current_step = $this->cart_variables['step'] = $step;
	}

	/**
	 * Displays the current step, if a step is for an order item, hands it
	 * over to that order item to display the step.
	 */
	public function displayStep ()
	{
		if ($this->current_step != 'preview') $this->initStepsView();
		if (in_array($this->current_step,$this->built_in_steps)) {
			//call the built in steps locally
			$function_name = $this->current_step . 'Display';

			$this->$function_name();

			//echo '<h1>Build in step: '.$this->current_step.'</h1><a href="'.$this->getProcessFormUrl().'">Next ></a><pre>'.print_r($this->order,1);
			return;
		}

		$parts = $this->_getStepParts($this->current_step);

		$function_name = $parts['step'].'Display';

		geoOrderItem::callUpdate($function_name, null, $parts['item_name']);
	}

	/**
	 * Returns the label for the current step, or the passed in step if provided.
	 * @param string $step If provided, will get the label for this step rather
	 *   than the current step on the cart.
	 * @return string The label for the current step or step passed in $step
	 * @since Version 7.2.0
	 */
	public function labelStep ($step=null)
	{
		$label = false;
		$step = ($step)? $step : $this->current_step;

		if (isset($this->_stepLabels[$step])) {
			$label = $this->_stepLabels[$step];
		} else {
			if (in_array($step, $this->built_in_steps)) {
				$call = $step.'Label';
				$label = $this->$call();
			} else {
				$parts = $this->_getStepParts($step);
				$name = $parts['item_name'];
				$realStep = $parts['step'];

				$label = geoOrderItem::callDisplay($realStep.'Label',null,'not_null',$name);
				if ($label === null) {
					//was not defined, or did not return anything, so reset label
					//to false, so that it will be auto-generated as a fallback
					$label = false;
				}
			}
			if ($label === false) {
				$parts = $this->_getStepParts($step);
				$name = $parts['item_name'];
				$realStep = $parts['step'];
				$label = ucwords(str_replace('_',' ',$realStep));
			}
			$this->_stepLabels[$step] = $label;
		}
		return $label;
	}

	/**
	 * Special built-in step of "other details" aka listing extras, or any other
	 * misc. data needing to be collected, this checks the vars by calling
	 * each order item and letting it check the vars specific to it.
	 *
	 */
	public function other_detailsCheckVars ()
	{
		trigger_error('DEBUG CART: Running other_detailsCheckVars');
//		$this->site->page_id = 12;
//		$this->site->get_text();

		$specific_item = null;
		if (count(geoOrderItem::getParentTypesFor($this->item->getType())) > 0) {
			//this is a child which won't be auto called by parent,
			//so force it to call
			$specific_item = $this->item->getType();
		}
		geoOrderItem::callUpdate('geoCart_other_detailsCheckVars', null, $specific_item);
		//$this->errors ++;//force there to be errors
	}

	/**
	 * Special built-in step of "other details" aka listing extras, or any other
	 * misc. data needing to be collected, this processes the vars by calling
	 * each order item and letting it process the vars specific to it.
	 *
	 */
	public function other_detailsProcess ()
	{
		trigger_error('DEBUG CART: Running other_detailsProcess');

		$specific_item = null;
		if (count(geoOrderItem::getParentTypesFor($this->item->getType())) > 0) {
			//this is a child which won't be auto called by parent,
			//so force it to call
			$specific_item = $this->item->getType();
		}
		geoOrderItem::callUpdate('geoCart_other_detailsProcess', null, $specific_item);
	}

	/**
	 * Special built-in step of "other details" aka listing extras, or any other
	 * misc. data needing to be collected, this displays the step by calling
	 * each order item and letting it send in stuff to be displayed on the page.
	 *
	 * See the _template order item for further documentation.
	 *
	 * @param bool $return if true, will return rendered display for other details
	 * @return Mixed
	 */
	public function other_detailsDisplay ($return = false)
	{
		//---------- LISTING COST AND FEATURES -------------

		/**
		 * Expects each one to return an associative array, like so:
		 * array (
		 *  'checkbox_name' => 'string', //if empty string, no checkbox is displayed
		 * 	'title' => 'string',
		 * 	'display_help_link' => 'string', //the help link returned by display_help_link($link_id);
		 * 	'price_display' => 'string', //IGNORED if charge for listings is turned off.
		 * 	//templates - over-write mini-template to do things like set margine or something:
		 * 	'entire_box' => 'inside of box actually',
		 * 	'left' => 'left side part',
		 * 	'right' => 'right side part',
		 * 	'checkbox' => 'checkbox tag',
		 * 	'checkbox_hidden' => 'hidden input tag',
		 *  //THE FOLLOWING are only needed by itens that will potentially be
		 *  // the "main_type" when this page is loading.  Such as any items
		 *  // that are parents, or any items that use this page to set settings
		 *  // and have ability to edit (like attention getters)
		 *  'page_title1' => 'string', //title with lines on it
		 *  'page_title2' => 'string', //blue title
		 *  'page_desc' => 'string',
		 *  'submit_button_text' => 'string',
		 *  'cancel_text' => 'string',
		 * )
		 */
		if ($return) {
			$this->site->messages = $msgs = $this->db->get_text(true, 10205);
		} else {
			$this->site->page_id = 10205;
			$this->site->get_text();
			$msgs = $this->db->get_text(true, $this->site->page_id);
		}

		$data_raw = geoOrderItem::callDisplay('geoCart_other_detailsDisplay',null,'array','',true);
		if (count($data_raw) > 0) {
			$tpl_vars = $this->getCommonTemplateVars();
			$mainData = (isset($data_raw[$this->main_type]))? $data_raw[$this->main_type]: array();
			//$tpl = new geoTemplate('system','cart');
			$tpl_vars['page_title1'] = (isset($mainData['page_title1']))? $mainData['page_title1']: $msgs[500311];
			$tpl_vars['page_title2'] = (isset($mainData['page_title2']))? $mainData['page_title2']: $msgs[500311];

			$tpl_vars['page_desc'] = (isset($mainData['page_desc']))? $mainData['page_desc']: $msgs[500312];

			$tpl_vars['submit_button_text'] = (isset($mainData['submit_button_text']))? $mainData['submit_button_text']: $msgs[500397];
			$tpl_vars['preview_button_txt'] = (isset($mainData['preview_button_txt']))? $mainData['preview_button_txt']: $msgs[502087];
			$tpl_vars['cancel_text'] = (isset($mainData['cancel_text']))? $mainData['cancel_text']: $msgs[500310];

			$tpl_vars['form_url'] = $this->getProcessFormUrl();
			$tpl_vars['cancel_url'] = $tpl_vars['cart_url'].'&amp;action=cancel';

			$tpl_vars['items'] = $data_raw;
			$tpl_vars['error_msgs'] = $this->getErrorMsgs();

			$this->addPreviewTemplateVars($tpl_vars);

			if ($return) {
				$tpl = new geoTemplate('system','cart');
				$tpl->assign($tpl_vars);
				$tpl->assign('full_step', 1);
				return $tpl->fetch('other_details/index.tpl');
			} else {
				geoView::getInstance()->setBodyTpl('other_details/index.tpl','','cart')
					->setBodyVar($tpl_vars);
			}

			$this->site->display_page();
		} else {
			//it just so happens nothing is supposed to display

			//if something wanted to still do the page and force it to be done,
			//it would need to return something, otherwise it assumes that
			//we really didn't want to do this step.

			if ($return) {
				return '';
			}

			$this->current_step = $this->cart_variables['step'] = $this->getNextStep();
			if ($this->current_step != 'other_details') {
				//re-display step
				$this->displayStep();
			} else {
				trigger_error("DEBUG CART: shouldn't be here -- something's wrong.");
			}
		}
		return true;
	}

	/**
	 * Mimics the other details step, but this is only used to display the info
	 * for specific item.
	 *
	 * Designed so that you put your main settings to be set in other details
	 * step, then use this function if that info needs to be displayed on another
	 * step as well.
	 *
	 * This function calls display_page if everything goes well.
	 *
	 * @param string $item_type - item type to call on to get the data for displaying
	 *  on the other details page.
	 * @return unknown_type
	 */
	public function displaySingleOtherDetails ($item_type)
	{
		$this->site->page_id = 10205;
		$this->site->get_text();
		$msgs = $this->db->get_text(true, $this->site->page_id);
		$data = geoOrderItem::callDisplay('geoCart_other_detailsDisplay',null,'array',$item_type);
		if (count($data) > 0) {
			$tpl_vars = $this->getCommonTemplateVars();
			$mainData = (isset($data[$item_type]))? $data[$item_type]: array();
			//$tpl = new geoTemplate('system','cart');
			$tpl_vars['page_title1'] = (isset($mainData['page_title1']))? $mainData['page_title1']: $msgs[500311];
			$tpl_vars['page_title2'] = (isset($mainData['page_title2']))? $mainData['page_title2']: $msgs[500311];

			$tpl_vars['page_desc'] = (isset($mainData['page_desc']))? $mainData['page_desc']: $msgs[500312];

			$tpl_vars['submit_button_text'] = (isset($mainData['submit_button_text']))? $mainData['submit_button_text']: $msgs[500397];
			$tpl_vars['cancel_text'] = (isset($mainData['cancel_text']))? $mainData['cancel_text']: $msgs[500310];

			$tpl_vars['form_url'] = $this->getProcessFormUrl();

			$tpl_vars['cancel_url'] = $tpl_vars['cart_url'].'&amp;action=cancel';

			$tpl_vars['items'] = $data;
			$tpl_vars['error_msgs'] = $this->getErrorMsgs();
			geoView::getInstance()->setBodyTpl('other_details/index.tpl','','cart')
				->setBodyVar($tpl_vars);

			$this->site->display_page();
			return true;
		} else {
			//it just so happens nothing is supposed to display
			//return false to let em know nothing happened.
			return false;
		}
	}

	/**
	 * Checks the vars (or lets the order items check any vars they want to) for
	 * the built-in main cart display page.
	 *
	 */
	public function cartCheckVars ()
	{
		//make sure checkout button was clicked...
		if ($_POST['checkout_clicked']!=='click') {
			//checkout button was not clicked, do not proceed.
			$this->addError();
			return;
		}

		//set text first for anything that might need text for error messages or something
		$this->site->page_id = 10202;
		$this->site->get_text();
		geoOrderItem::callUpdate('geoCart_cartCheckVars');

		if ($this->getCartTotal() < 0) {
			//total is < 0, do not allow to proceed.
			$this->addError()
				->addErrorMsg('cart_error',$this->site->messages[500946]);
		}
	}

	/**
	 * Processes the vars (or lets the order items do any processing they want to) for
	 * the built-in main cart display page.
	 *
	 */
	public function cartProcess ()
	{
		geoOrderItem::callUpdate('geoCart_cartProcess');

		if (defined('IN_ADMIN')) {
			//pre-approvals on items that require admin approval
			$skipApprovals = (array)$_POST['needAdminApproval_skip'];
			$items = $this->order->getItem('parent');

			foreach ($items as $item) {
				if (!$item || !$item->getId()) {
					//something wrong with this one...
					continue;
				}
				$itemId = (int)$item->getId();
				if ($item->get('needAdminApproval',null)!==null || isset($skipApprovals[$itemId])) {
					//either need admin approval override is already set, or check-box
					//for pre-approving is checked, either way needAdminApproval needs to be
					//updated for the item.

					//if skip approval, set needAdminApproval to 0.. if not skip, set needAdminApproval to false,
					//which will actually un-set it with the way things work.
					$needApproval = (isset($skipApprovals[$itemId]) && $skipApprovals[$itemId])? 0 : false;
					$item->set('needAdminApproval', $needApproval);
					$item->save();
				}
			}
		}

		if ($this->errors > 0) {
			//don't "short circuit" payment page if something threw an error...
			return;
		}
		if ($this->getCartTotal() == 0 && !$this->get('no_free_cart')) {
			//there will be no payment details page, need to do things at this step.
			$this->set('free_cart',1);
			$this->payment_choicesProcess(true);
		} else {
			if ($this->get('free_cart')) {
				$this->set('free_cart', false);
			}
		}
	}
	/**
	 * Displays the main built-in cart display page.
	 * @param bool $renderPage If false, will skip actually displaying the page.
	 *   Useful if needing to set everything up for a cart to be displayed,
	 *   without actually displaying the cart.
	 */
	public function cartDisplay ($renderPage = true)
	{
		//Display the cart...

		if ($renderPage) {
			//make sure type and all that are reset
			$this->main_type = $this->cart_variables['main_type'] = 'cart';
			$this->cart_variables['order_item'] = (($this->cart_variables['order_item'] >= 0)? 0: -1);
		}

		//assign id of old choose listing type page, for now...
		$this->site->page_id = 10202;
		$this->site->get_text();
		$tpl_vars = $this->getCommonTemplateVars();
		//for each item that is main type, call to let that item specify what to display
		$cart_items = $this->_getCartItemDetails($tpl_vars['allFree']);

		if ($this->cart_variables['order_item'] != -1) {
			$new_item_buttons = geoOrderItem::callDisplay('geoCart_cartDisplay_newButton',null,'string_array');
		} else {
			$new_item_buttons = array();
		}
		if ($renderPage) {
			if (count($cart_items) == 0 && count($new_item_buttons) == 0) {
				//no items in cart, and no add buttons to display, so make sure user logged in by enforceAnonymous
				if (geoOrderItem::enforceAnonymous()) {
					if (defined('IN_ADMIN')) {
						//force re-direct to choose user
						header ("Location: index.php?page=admin_cart_select_user");
					}
					include GEO_BASE_DIR . 'app_bottom.php';
					exit;
				}
			}
			$tpl_vars['items'] = $cart_items;
			$tpl_vars['new_item_buttons'] = $new_item_buttons;
			$tpl_vars['error_msgs'] = $this->error_msgs;
			if (defined('IN_ADMIN')) {
				//add help thingy for auto-approval of items
				$tpl_vars['admin_auto_approve_help'] = $this->site->display_help_link(500948);
			}

			geoView::getInstance()->setBodyTpl('display_cart/index.tpl','','cart')
				->setBodyVar($tpl_vars);

			$this->site->display_page();
		}
	}
	/**
	 * Gets the label to use for the cart.
	 * @return string
	 */
	public function cartLabel ()
	{
		if (!geoMaster::is('site_fees')) {
			//free, use "queue" text
			return $this->site->messages[500511];
		}
		return $this->site->messages[500510];
	}

	/**
	 * Used to skip the cart and checkout steps.
	 *
	 * This will only skip the cart / checkout steps, if the cart total
	 * is $0 and there is only one parent item in the cart.  In addition, prior
	 * to calling this, the current step must be set to "cart", so be aware of that
	 * if attempting to call this inside of a step processing.
	 *
	 * Note that this is not typically used directly by an order item, it should
	 * only be used in special cases.  This is primarily used by the cart system
	 * directly.
	 *
	 * @param bool $checkSettings If false, will skip the setting check for whether
	 *   to skip the cart or not (set in admin in the listing placement steps page),
	 *   defaults to true. Parameter {@since Version 7.2.3}
	 * @param bool $showOrderComplete If false, will do everything leading up to actually
	 *   displaying the order complete page.  If true (default), will also display
	 *   the order complete page and stop further script execution.  Parameter {@since Version 7.2.3}
	 * @return bool Returns bool false if it did not skip the cart for some reason.
	 *   Returns bool true if $showOrderComplete is false and the stuff was able to
	 *   skip the cart and checkout pages successfully.  Does not return at all
	 *   if skiping cart is successful and $showOrderComplete is true, as that will
	 *   make it show order complete page and stop further script execution early.
	 * @since Version 7.2.0
	 */
	public function skipCart ($checkSettings = true, $showOrderComplete = true)
	{
		if (defined('IN_ADMIN') || $this->getCartTotal() > 0 || $this->current_step !== 'cart' || $this->action === 'delete') {
			//do not skip cart...
			trigger_error('DEBUG CART: skipCart - in admin, cart total is > 0, or just deleted something; not skipping cart');
			return false;
		}

		//make sure there is only one thing in the cart...

		$items = $this->order->getItem('parent');

		if (count($items) !== 1) {
			trigger_error('DEBUG CART: skipCart - number items is not 1, not skipping');
			return false;
		}

		if ($checkSettings) {
			//now check the planItem settings to see what is up, see if we CAN skip the
			//cart or not...
			$planItem = geoPlanItem::getPlanItem($this->main_type,0,0);
			if (!$planItem) {
				//failsafe, can't do anything
				return;
			}
			$pre = geoCart::COMBINED_PREFIX;
			if (!$planItem->get($pre.'skip_cart')) {
				trigger_error("DEBUG CART: skipCart - item set to not skip the cart...");
				return false;
			}
		}
		trigger_error('DEBUG CART: skipCart() all checks are good, proceeding with skipping the cart!');

		//this is only item in the cart,
		//go ahead and short-circuit
		$this->item = null;

		//do check vars...  pretend click is set in post vars
		$_POST['checkout_clicked']='click';

		//let it set all the stuff to switch over to the cart side, plus do any
		//hooks for things like checking if user has subscription
		if ($this->checkVars('cart')) {
			$this->processStep('cart');
		}

		//make sure nothing objects...
		if ($this->errors) {
			//oops, do not proceed...  there are errors
			trigger_error('DEBUG CART: skipCart - there were errors when processing cart, cannot skip the cart.');
			return false;
		}

		//process the order
		//NOTE: payment_choicesProcess has already been called by cartProcess. No need to do it here.
		//$this->payment_choicesProcess(true);

		if (!$showOrderComplete) {
			//do not show order complete page and exit... just return true to indicate it succeeded
			return true;
		}

		//do the actual display here as well
		$this->process_orderDisplay();

		trigger_error('DEBUG CART: skipCart() - successfully skiped the cart, so exiting to short-circuit rest of page.');
		require GEO_BASE_DIR . 'app_bottom.php';
		exit;
	}

	/**
	 * Used internally to see if the "next step" is going to be the cart or not.
	 *
	 * @param string $currentStep If specified, will test if the step after this one
	 *   is the cart.  If not specified, will use the current step of the cart.
	 * @return boolean
	 */
	private function _isNextStepCart ($currentStep=null)
	{
		if ($currentStep===null) {
			$currentStep = $this->current_step;
		}

		if ($this->getNextStep($currentStep)==='cart') {
			//easy, answer is yes
			return true;
		}
		if ($currentStep !== 'combined' && !in_array($currentStep, $this->uncombined_steps)) {
			//not combined, and not an "uncombined" step...  no way the next step is the cart
			return false;
		}
	}

	/**
	 * See if should show the preview button on the current step.  Checks to see
	 * if the step is the last one before the cart step, and if the current item
	 * is set to skip the cart, or set to "always" show the preview (if not
	 * skipping the cart, and not set to "always" preview, there is a preview shown
	 * on the cart so no need to show preview during the process of adding the
	 * item)
	 *
	 * @return bool True if should show the preview button, false otherwise.
	 * @since Version 7.2.0
	 */
	public function showPreviewButtonOnStep ()
	{
		if (!$this->item || $this->main_type == 'cart' || $this->current_step == 'cart' || defined('IN_ADMIN')) {
			//not currently adding an item, OR it's in admin panel, so no
			return false;
		}
		if ($this->getNextStep($this->current_step, true) !== 'cart') {
			//the next step is not the cart..
			//So should not show preview
			return false;
		}
		if ($this->isCombinedStep() && $this->current_step !== 'combined') {
			//do not show preview button on individual steps that are combined
			return false;
		}

		$planItem = geoPlanItem::getPlanItem($this->main_type,0,0);
		if (!$planItem) {
			return false;
		}
		$pre = self::COMBINED_PREFIX;
		if (!$planItem->get($pre.'always_preview') && !$planItem->get($pre.'skip_cart')) {
			//Not set to skip the cart.  And the cart has it's own preview button.
			//So don't show the preview button.
			return false;
		}
		//if it gets this far, it should indeed show the preview button.
		return true;
	}

	/**
	 * If this returns true, the normal "continue" button should not be displayed
	 * on the step, ONLY the preview button should be shown.  Making the only way
	 * to continue, is to preview the item, then click "accept and submit" button.
	 *
	 * @return bool
	 * @since Version 7.2.2
	 */
	public function forcePreviewOnStep ()
	{
		if (!$this->showPreviewButtonOnStep()) {
			//don't need to force if not showing preview to begin with!
			return false;
		}
		//NOTE: We don't need to do "all" the checks, as most of them are done
		//by the showPreviewButtonOnStep() method.

		$planItem = geoPlanItem::getPlanItem($this->main_type,0,0);
		if (!$planItem) {
			return false;
		}
		$pre = self::COMBINED_PREFIX;
		return (bool)$planItem->get($pre.'force_preview');
	}

	/**
	 * Check to see if it should be showing an inline preview box.  This is used
	 * in 2 places, first in the process step automatically called by the cart,
	 * and second by {@see geoCart::addPreviewTemplateVars()} to let templates
	 * know to display the preview box.
	 *
	 * @param bool $inProcess Only set to true if in the process step and should
	 *   be adding an error to prevent going to next step.  Note that in that case
	 *   it is automatically called by the cart, no need for individual items to call
	 *   this during process step.
	 * @return boolean
	 * @since Version 7.2.0
	 */
	public function showPreviewBox ($inProcess = false)
	{
		if (!$this->showPreviewButtonOnStep()) {
			//if shouldn't show button step, of course shouldn't show the preview
			//box itself
			return false;
		}
		if (!isset($_POST['forcePreview'])) {
			//forcePreview is not displayed...
			return false;
		}

		if ($inProcess) {
			if ($this->errors > 0) {
				//there are errors...
				return false;
			}
			//gets this far, we are indeed showing the preview.  Let system know to not
			//proceed automatically
			$this->_skipNextStep=true;
		} else {
			//on display step, if there are errors show a generic "there was error" message
			if ($this->errors > 0) {
				//there are errors
				$msgs = $this->db->get_text(true, 10202);
				$this->addErrorMsg('preview_error',$msgs[502091]);
				return false;
			}
		}
		return true;
	}
	/**
	 * Use this to add the tpl vars needed for preview button and box, on steps
	 * that could potentially be the last step and may need to add a preview button.
	 *
	 * @param array $tpl_vars The array of template vars to add the preview vars
	 *   to, note that this is passed by reference
	 * @since Version 7.2.0
	 */
	public function addPreviewTemplateVars (&$tpl_vars)
	{
		//should show the preview button on the details?
		$tpl_vars['showPreviewButton'] = $this->showPreviewButtonOnStep();
		if ($tpl_vars['showPreviewButton']) {
			//keep from doing extra checks...  we know preview box / force preview
			//will not show if not showing preview button...
			$tpl_vars['forcePreviewButtonOnly'] = $this->forcePreviewOnStep();
			//pass false to indicate this is on a display step
			$tpl_vars['showPreviewBox'] = $this->showPreviewBox(false);
			if ($tpl_vars['showPreviewBox']) {
				//need to let the box know what the item ID is
				$tpl_vars['preview_item_id'] = $this->item->getId();
			}
		} else {
			$tpl_vars['forcePreview'] = $tpl_vars['showPreviewBox'] = false;
		}
	}

	/**
	 * The delete label, used for the delete step (is just a blank string)
	 * @return string
	 */
	public function deleteLabel ()
	{
		return '';
	}
	/**
	 * gets the label for the preview step, is just a blank string.
	 * @return string
	 */
	public function previewLabel ()
	{
		return '';
	}

	/**
	 * Gets the label for the built in step of other details (aka extras).
	 * @return string
	 */
	public function other_detailsLabel ()
	{
		$label = 'Extras';
		if ($this->item) {
			$type = $this->item->getType();
			$label = geoOrderItem::callDisplay('geoCart_other_detailsLabel',null,'not_null',$type);
			if ($label !== null) {
				return $label;
			}
		}
		return $label;
	}

	/**
	 * Gets the label for the payment choices step.
	 * @return string
	 */
	public function payment_choicesLabel ()
	{
		if (!geoMaster::is('site_fees') || ($this->getCartTotal() == 0 && !$this->get('no_free_cart'))) {
			//don't display this step, it will be skipped!
			return '';
		}
		//Verify/Payment Details
		return $this->site->messages[500512];
	}

	/**
	 * Gets the label for the process order step.
	 * @return string
	 */
	public function process_orderLabel ()
	{
		if (!geoMaster::is('site_fees')) {
			return $this->site->messages[500514];
		}
		//Verify/Payment Details
		return $this->site->messages[500513];
	}

	/**
	 * Used internally to get the cart item details
	 *
	 * @param bool $noTotal
	 * @param bool $inCart
	 * @return array|bool
	 */
	private function _getCartItemDetails ($noTotal = false, $inCart = true)
	{
		//allow special case items such as sub-total to update themself if needed,
		//based on contents of the cart at this step.
		geoOrderItem::callUpdate('geoCart_getCartItemDetails',null,'',true);

		$items = $this->order->sortItems()
			->getItem();
		//for each item that is main type, call to let that item specify what to display
		$cart_items = array();
		foreach ($items as $i => $item) {
			if (is_object($item)) {
				if (!is_object($item->getParent())) {
					//this is parent
					$result = $item->getDisplayDetails($inCart);
					if ($result !== false) {
						if (defined('IN_ADMIN') && $inCart && !isset($result['needAdminApproval'])) {
							$approvalOverride = $item->get('needAdminApproval',null);
							if ($approvalOverride === null) {
								//This is the normal case, the item has not specified whether
								//or not it needs admin approval, so we check the order
								//setting.  Most items will be like this.
								$plan_item = geoPlanItem::getPlanItem($item->getType(), $item->getPricePlan(), $item->getCategory());
								$result['needAdminApproval'] = $plan_item->needAdminApproval();
								$result['needAdminApproval_skip'] = false;
								unset($plan_item);
							} else {
								//This is a special case, the order has set needAdminApproval
								//meaning it says to ignore the planitem setting and use this
								//instead.
								$result['needAdminApproval'] = 1;
								$result['needAdminApproval_skip'] = (!$approvalOverride);
							}
						}
						$cart_items[$item->getId()] = $result;
					}
				}
			}
		}
		if (!$noTotal && count($cart_items)) {
			//add total
			$cart_items[] = array(
				'css_class' => 'total_cart_item', //css class
				'title' => $this->site->messages[500403],
				'canEdit' => false, //show edit button for item?
				'canDelete' => false, //show delete button for item?
				'canPreview' => false, //show preview button for item?
				'priceDisplay' => geoString::displayPrice($this->getCartTotal()), //Price as it is displayed
				'cost' => 0, //amount this adds to the total, what getCost returns
				'total' => $this->getCartTotal(), //amount this AND all children adds to the total (will add to it as we parse the children)
				'children' => array()
			);
		}
		return $cart_items;
	}

	/**
	 * This is the checkvars method for the built-in delete step.
	 */
	public function deleteCheckVars ()
	{
		trigger_error('DEBUG CART: Running deleteCheckVars');

		geoOrderItem::callUpdate('geoCart_deleteCheckVars', null, $this->item->getType());
	}

	/**
	 * This is the process method for the built-in delete step.
	 */
	public function deleteProcess ()
	{
		trigger_error('DEBUG CART: Running deleteProcess, about to remove item '.$this->item->getId().' from this order.');

		//allow items to do special removal, if needed.
		geoOrderItem::callUpdate('geoCart_deleteProcess',null,$this->item->getType());

		//remove the item
		$id = $this->item->getId();
		geoOrderItem::remove($id);
		$this->order->detachItem($id);
		if ($this->cart_variables['order_item'] == -1) {
			//Delete the stand-alone cart
			$this->removeSession();
			//init main cart
			$this->initSession();
			return true;
		}
		$this->item = $this->cart_variables['order_item'] = 0;
	}

	/**
	 * This is the display method for the built-in delete step.
	 */
	public function deleteDisplay ()
	{
		$this->cartDisplay();
	}

	/**
	 * CheckVars method for the combined step, just loops through all the steps
	 * combined, and runs the checkVars for each
	 *
	 * @since Version 7.2.0
	 */
	public function combinedCheckVars ()
	{
		foreach ($this->combined_steps as $step) {
			if ($step=='combined') {
				//failsafe, prevent infinite recurrsion
				continue;
			}
			if (in_array($step, $this->all_steps_standard)) {
				$this->checkVars($step);
			}

			//reset for next round...
			$this->site->error = 0;
		}
		$this->current_step = 'combined';
	}
	/**
	 * Process step for the combined step that is built in step for the cart
	 *
	 * @since Version 7.2.0
	 */
	public function combinedProcess ()
	{
		//first, set it to prevent things from going forward in built-in
		//thingy...
		$beforeSkip = $this->_skipNextStep;
		$this->_skipNextStep = true;
		foreach ($this->combined_steps as $step) {
			if ($step=='combined') {
				//failsafe, prevent infinite recurrsion
				continue;
			}
			if (in_array($step, $this->all_steps_standard)) {
				$this->processStep($step);
			}
		}
		$this->current_step = 'combined';
		//now set it to allow going to next step
		$this->_skipNextStep = $beforeSkip;

		if (!(isset($_POST['combined_submit']) || isset($_POST['forcePreview'])) || geoAjax::isAjax()) {
			//not using actual submit or preview button, keep it from progressing...
			//OR this is an ajax call to update the combined step
			$this->addError();
		}
	}

	/**
	 * Gets the label to use for the combined step.
	 *
	 * @return string
	 * @since Version 7.2.0
	 */
	public function combinedLabel ()
	{
		//figure out what to use as the label...
		$label = '';
		foreach ($this->combined_steps as $step) {
			if ($step=='combined') {
				//failsafe, prevent infinite recurrsion
				continue;
			}
			if (!in_array($step, $this->all_steps_standard)) {
				//this one isn't normally displayed based on category or other criteria, skip it
				continue;
			}
			if (!strlen($label) || preg_match('/\:details$/', $step)) {
				//This results in either the "first step" being used for the label,
				//or if there is a details step, that label is used.
				$stepLabel = $this->labelStep($step);
				if ($stepLabel) {
					$label=$stepLabel;
				}
			}
		}
		return $label;
	}

	/**
	 * Built-in step, that goes through list of combined steps and displayes all of them
	 *
	 * @since Version 7.2.0
	 */
	public function combinedDisplay ()
	{
		//just to get things going...
		$view = geoView::getInstance();

		$tpl_vars = $this->getCommonTemplateVars();

		//tell site class to not display yet
		$view->bypass_display_page = true;

		$section_changed = '';
		if (geoAjax::isAjax() && isset($_POST['ajax_section_changed'])) {
			//keep track of which section was changed, and don't bother updating
			//that section's contents...
			$section_changed = trim($_POST['ajax_section_changed']);
			//since it is section ID remove the extra part added...
			//combined_
			$section_changed = substr($section_changed,9);
		}
		//let anything set listing_types_allowed
		$listing_types_allowed = 0;
		foreach ($this->combined_steps as $step) {
			if ($step=='combined') {
				//failsafe, prevent infinite recurrsion
				continue;
			}
			if ($section_changed && str_replace(':','-',$step)==$section_changed) {
				//this section is the one that had a change in it, do not bother
				//displaying it.  If some ajax needs to be run to update stuff in
				//that same section, that needs to be coded special.
				continue;
			}
			$this->current_step = $step;
			$this->displayStep();

			if (!$view->geo_inc_files) {
				//don't do anything for this one
				continue;
			}
			$body_vars = $view->body_vars;
			$is_details = preg_match('/\:details$/', $step);

			//need to populate text vars, use ones set on the steps if they are set
			$txt_find = array ('submit_button_txt','txt1','cancel_txt','preview_button_txt');
			//loop through each txt var we want to populate and see if it is set in that body vars...
			foreach ($txt_find as $txt) {
				if (!isset($tpl_vars[$txt]) || $is_details) {
					if (isset($body_vars[$txt]) && strlen(trim($body_vars[$txt]))) {
						//populate the text with this one
						$tpl_vars[$txt] = $body_vars[$txt];
					}
				}
			}

			if (isset($body_vars['listing_types_allowed'])) {
				$listing_types_allowed = (int)$body_vars['listing_types_allowed'];
			}

			$tpl_vars['step_tpls'][$step] = array (
				'geo_inc_files' => $view->geo_inc_files,
				'body_vars' => $body_vars,
				'label' => $this->labelStep($step),
				);

			//reset for next round...
			unset($view->geo_inc_files);
			unset($view->body_vars);
			unset($body_vars);
		}
		//listing_types_allowed used by category selection
		$tpl_vars['listing_types_allowed'] = $listing_types_allowed;

		//set defaults
		$msgs = $this->db->get_text(true,10202);
		//$txt_find = array ('submit_button_txt','txt1','cancel_txt','preview_button_txt');

		if (!isset($tpl_vars['submit_button_txt'])) {
			$tpl_vars['submit_button_txt'] = $msgs[502095];
		}
		if (!isset($tpl_vars['txt1'])) {
			$tpl_vars['txt1'] = $msgs[502098];
		}
		if (!isset($tpl_vars['cancel_txt'])) {
			$tpl_vars['cancel_txt'] = $msgs[502096];
		}
		if (!isset($tpl_vars['preview_button_txt'])) {
			$tpl_vars['preview_button_txt'] = $msgs[502097];
		}

		//Let it show the page now
		$view->bypass_display_page = false;

		$this->current_step = 'combined';

		//reset steps in view after going through all those
		$this->initStepsView();

		//let sub-templates know they are being combined...
		$tpl_vars['steps_combined'] = true;

		if (geoAjax::isAjax()) {
			//let combinedDisplayAjax() do the work...
			return $this->combinedDisplayAjax($tpl_vars);
		}
		//now display combined steps normally
		//add vars for previewing things
		$this->addPreviewTemplateVars($tpl_vars);

		$tpl_vars['error_msgs'] = $this->error_msgs;

		geoView::getInstance()->setBodyTpl('combined/index.tpl','','cart')
			->setBodyVar($tpl_vars);

		if (!$this->site->page_id) {
			//probably combined steps that don't get displayed... Set a
			//page ID to prevent errors.
			$this->site->page_id = 9;
		}

		$this->site->display_page();
	}

	/**
	 * Does the ajax portion of combined display step, when steps are being loaded
	 * via AJAX call.
	 *
	 * @param array $tpl_vars The tpl vars as loaded in the main combinedDisplay method.
	 * @since Version 7.2.0
	 */
	public function combinedDisplayAjax ($tpl_vars)
	{
		$ajax = geoAjax::getInstance();

		$return = array();

		$tpl_vars = array_merge($tpl_vars, geoView::getInstance()->getAllAssignedVars());

		//let them know it is an ajax call...
		$tpl_vars['is_ajax_combined'] = true;

		//can re-use the tpl object over and over...  We may need to create new
		//template for each one if it turns out this allows "bleeding" of vars
		//that shouldn't be bleeding...  As long as that is not the case however,
		//it should help with memory and such to re-use same object.
		$tpl = new geoTemplate(geoTemplate::SYSTEM,'cart');
		if ($tpl_vars['cartSteps']) {
			//first, update the steps section just in case
			$tpl->assign('cartSteps', $tpl_vars['cartSteps']);
			$return['sections']['stepsBreadcrumb'] = $tpl->fetch('cart_steps.tpl');
			//don't need the cart steps set anymore
			unset($tpl_vars['cartSteps']);
			$tpl->assign('cartSteps', null);
		}

		//now loop through every step and render contents
		$step_tpls = $tpl_vars['step_tpls'];
		//don't need step_tpls inside tpl_vars...
		unset($tpl_vars['step_tpls']);

		$tpl->assign($tpl_vars);
		foreach ($step_tpls as $step => $step_info) {
			$tpl->assign(array('step' => $step, 'step_info' => $step_info));

			$section_name = str_replace(':','-',$step);
			$return['sections'][$section_name] = $tpl->fetch('combined/step_section.tpl');
		}
		//We are going to echo JSON...
		$ajax->jsonHeader();
		echo $ajax->encodeJSON($return);
		//make sure the page does not display normally
		geoView::getInstance()->setRendered(true);
	}


	/**
	 * This is the display method for the built-in preview step.
	 */
	public function previewDisplay ()
	{
		$this->item->geoCart_previewDisplay();
		//set everything back so that next time cart is viewed, it's in cart mode
		$this->cart_variables['step'] = $this->current_step = $this->cart_variables['main_type'] = 'cart';
		if ($this->cart_variables['order_item'] != -1) {
			$this->item = null;
		}
		$this->cart_variables['order_item'] = (($this->cart_variables['order_item'] >= 0)? 0: -1);
	}
	/**
	 * Payment choices check vars method
	 */
	public function payment_choicesCheckVars ()
	{
		$this->site->page_id = 10203;
		$msgs = $this->site->messages = $this->db->get_text(true, $this->site->page_id);


		//make sure payment choice is made..
		if (!isset($_POST['c']['payment_type']) || !$_POST['c']["payment_type"] || !is_object(geoPaymentGateway::getPaymentGateway($_POST['c']["payment_type"]))) {
			//payment type not send, or set to something invalid.
			$this->addError();
			$this->error_variables["choices_box"] = geoString::fromDB($msgs[500308]);
		}

		if (isset($_POST['c']) && is_array($_POST['c'])) {
			//set billing info so it is used the next time the page
			//is loaded, if something has failed.  (so user does not
			//have to re-enter info every time)

			$this->order->setBillingInfo($_POST['c']);
			$this->user_data['billing_info'] = $_POST['c'];

			//make sure e-mail is valid...
			if (!isset($_POST['c']['email']) || !$_POST['c']['email'] || !geoString::isEmail($_POST['c']['email'])) {
				$this->addError()
					->addErrorMsg('billing_email',$msgs[500309]);
			}
			if (isset($_POST['c']['payment_type']) && $this->isRecurringCart()) {
				$gateway = geoPaymentGateway::getPaymentGateway($_POST['c']['payment_type']);
				if ($gateway && $gateway->isRecurring() && $gateway->getRecurringAgreement()) {
					//make sure the recurring agreement was agreed upon
					if (!isset($_POST['c']['user_agreement']) || $_POST['c']['user_agreement'] != $_POST['c']['payment_type']) {
						//user did not agree!
						$this->addError()
							->addErrorMsg('gateway_recurring_agreement', $msgs[500766]);
					}
				}
			}
		}
		geoPaymentGateway::callUpdate('geoCart_payment_choicesCheckVars');
	}

	/**
	 * This is the process method for the built in payment_choices step, it
	 * is the step that the invoice is created, site fees added, etc.
	 *
	 * @param bool $free_cart If true, treats it as if there is nothing owed
	 *  for the cart.
	 */
	public function payment_choicesProcess ($free_cart = false)
	{
		if (isset($_POST['c']['payment_type']) && is_object(geoPaymentGateway::getPaymentGateway($_POST['c']['payment_type']))) {
			//set the payment type
			$this->order->set('payment_type',$_POST['c']['payment_type']);
		} elseif ($free_cart) {
			$this->order->set('payment_type','free');
		}
		//create an invoice, if there isn't one already
		$invoice = $this->order->getInvoice();
		if (!is_object($invoice)) {
			$invoice = new geoInvoice;
			$invoice->setOrder($this->order);
			$invoice->save(); //so it has an ID

			$this->order->setInvoice($invoice);
		}
		$invoice->setCreated(geoUtil::time());
		$invoice->setDue(geoUtil::time());
		$gateway = geoPaymentGateway::getPaymentGateway('site_fee');
		if (!is_object($gateway)) {
			trigger_error('ERROR CART: Unable to get gateway for site fee, not able to process.');
			return false;
		}
		//do the built-in transaction for the entire amount
		$trans = $invoice->getTransaction();
		$transaction = null;
		if (count($trans) > 0) {
			foreach ($trans as $tran) {
				if (is_object($tran) && $tran->getGateway()->getName() == 'site_fee') {
					$transaction = $tran;
					break;
				}
			}
		}
		if (!is_object($transaction)) {
			$transaction = new geoTransaction;
			$transaction->setGateway($gateway);
			$transaction->setInvoice($invoice);
			$transaction->save();
			$invoice->addTransaction($transaction);
		}

		$msgs = $this->db->get_text(true, 10202);

		//the cart total is a positive amount for how much the user owes us, so to convert to
		//a transaction amount it needs to be negative, kind of like taking away from a bank account
		$transaction->setAmount(-1 * $this->getCartTotal());
		$transaction->setGateway($gateway);
		$transaction->setDate(geoUtil::time());
		$transaction->setDescription($msgs[500259].$this->order->getId());
		$transaction->setInvoice($invoice);
		$transaction->setStatus(1);//turn on
		$transaction->setUser($this->user_data['id']);

		//save changes, should remove this once the app_bottom auto-save is done.

		$transaction->save();
		$invoice->save();
		//first let order items get ahold of it, even child order items...
		geoOrderItem::callUpdate('geoCart_payment_choicesProcess',null,'',true);


		if ($free_cart) {
			//it's free, so let all the order items know theres a new transaction.

			$this->order->processStatusChange('active');
		} else {
			//let gateways do their thing, but only if there is money involved
			geoPaymentGateway::callUpdate('geoCart_payment_choicesProcess',null,$this->order->get('payment_type'));
		}
	}

	/**
	 * Displays the payment choices page.
	 */
	public function payment_choicesDisplay ()
	{
		//get the text for cart page, since displaying a mini-cart within the payment details page
		$this->site->page_id = 10202;
		$this->site->get_text();

		$this->site->page_id = 10203;
		$this->site->get_text();

		$tpl_vars = $this->getCommonTemplateVars();

		$tpl_vars['error_msgs'] = $this->error_msgs;
		$tpl_vars['items'] = $this->_getCartItemDetails(false, false);

		//$tpl_vars['user'] = $this->user_data;
		if (isset($this->error_variables)) {
			$tpl_vars['errors'] = $this->error_variables;
		} else {
			$tpl_vars['errors'] = array();
		}
		$payment_choices = geoPaymentGateway::callDisplay('geoCart_payment_choicesDisplay',array('itemCostDetails'=>$this->order->getItemCostDetails()),'array');
		if ($this->get('no_free_cart') && $this->getCartTotal() == 0) {
			$tpl_vars['no_free_cart'] = 1;

			//go through each gateway and see which ones will still be displayed
			foreach ($payment_choices as $type => $vals) {
				if (!geoPaymentGateway::callDisplay('geoCart_payment_choicesDisplay_freeCart', null, 'bool_true', $type)) {
					//this payment gateway doesn't want to be displayed when it's a free cart
					unset($payment_choices[$type]);
				}
			}
		} else if ($this->isRecurringCart()) {
			//this is a recurring cart, see if any of the payment gateways is
			//recurring
			$recurring_choices = array ();
			foreach ($payment_choices as $type => $vals) {
				//isRecurring is not static, have to call it directly
				$gateway = geoPaymentGateway::getPaymentGateway($type);
				if ($gateway->isRecurring()) {
					$vals['user_agreement'] = $gateway->getRecurringAgreement();
					$recurring_choices[$type] = $vals;
				}
				unset($gateway);
			}
			if (count($recurring_choices)) {
				//there is at least 1 recurring gateway choice, so only show those
				//gateway(s) that are recurring
				$payment_choices = $recurring_choices;
			}
		}

		$force_checked = false;
		if (count($payment_choices) == 1) {
			$force_checked = true;
		} elseif (isset($_POST['c']['payment_type']) && isset($payment_choices[$_POST['c']['payment_type']])) {
			//set checked to true, for one that is selected.
			$payment_choices[$_POST['c']['payment_type']]['checked'] = true;
		} elseif ($this->order->get('payment_type') && isset($payment_choices[$this->order->get('payment_type')])) {
			//set as cart variable, probably selected in previous attempt
			$payment_choices[$this->order->get('payment_type')]['checked'] = true;
		} else {
			//go through each one, if it's the default then set it
			$foundChecked = false;
			foreach ($payment_choices as $type => $vals) {
				$gateway = geoPaymentGateway::getPaymentGateway($type);
				if (is_object($gateway) && $gateway->getDefault()) {
					//this is the default one, so make it checked
					$payment_choices[$type]['checked'] = true;
					$foundChecked = true;
					break;
				}
			}

			if (!$foundChecked) {
				//make sure something is checked!  This is for fallback purposes, in case admin has not selected a default.
				$force_checked = true;//just make them all checked...
			}
		}
		$tpl_vars['populate_billing_info'] = $populate_billing_info = $this->db->get_site_setting('populate_billing_info');


		$userLocation = geoRegion::getRegionsForUser($this->user_data['id']);
		$regions = geoRegion::billingRegionSelector('c',$userLocation);
		$tpl_vars['countries'] = $regions['countries'];
		$tpl_vars['states'] = $regions['states'];



		$tpl_vars['cart'] = $this->user_data;
		$tpl_vars['payment_choices'] = $payment_choices;
		$tpl_vars['force_checked'] = $force_checked;
		$this->addPreviewTemplateVars($tpl_vars);

		if (defined('IN_ADMIN')) {
			//replace link to cart view to be correct in order summary description text
			$tpl_vars['order_summary_desc'] = str_replace($this->db->get_site_setting('classifieds_file_name').'?a=cart', $tpl_vars['cart_url'], $this->site->messages[500265]);
		} else {
			$tpl_vars['order_summary_desc'] = $this->site->messages[500265];
		}

		geoView::getInstance()->setBodyTpl('payment_choices/index.tpl','','cart')
			->setBodyVar($tpl_vars);

		$this->site->display_page();
		return true;
	}

	/**
	 * The built in display method for process order step.
	 */
	public function process_orderDisplay ()
	{
		//get the text for cart page, since displaying a mini-cart within the payment details page
		$this->site->messages = $this->db->get_text(true,10202);

		geoView::getInstance()->cart_items = $this->_getCartItemDetails(!geoMaster::is('site_fees'),false);

		//A way for manual type gateways to display a page, or used as success/failure page
		if ($this->get('free_cart')) {
			//the whole cart was free!
			$this->site->page_id = 10204;
			$msgs = $this->db->get_text(true, $this->site->page_id);

			$tpl_vars = $this->getCommonTemplateVars();
			$tpl_vars['page_title'] = ($tpl_vars['allFree'])? $msgs[500417]: $msgs[500306];
			$tpl_vars['page_desc'] = ($tpl_vars['allFree'])? $msgs[500418]: $msgs[500307];
			$tpl_vars['success_failure_message'] = '';
			$tpl_vars['my_account_url'] = $this->db->get_site_setting('classifieds_file_name').'?a=4';
			$tpl_vars['my_account_link'] = $msgs[500305];
			if (!$tpl_vars['allFree']) {
				$invoice = $this->order->getInvoice();
				if (is_object($invoice) && $invoice->getId()) {
					$tpl_vars['invoice_url'] = geoInvoice::getInvoiceLink($invoice->getId(), false, defined('IN_ADMIN'));
				}
			}

			geoView::getInstance()->setBodyTpl('shared/transaction_approved.tpl','','payment_gateways')
				->setBodyVar($tpl_vars);

			$this->site->display_page();
			$this->removeSession();
			return;
		}

		geoPaymentGateway::callUpdate('geoCart_process_orderDisplay',null,$this->order->get('payment_type'));
	}

	/**
	 * Saves the cart, the order attached to the cart, and all the stuff in that
	 * order.  This is normally done by app_bottom but will need to be done
	 * manually if you are calling removeSession as otherwise, the final order
	 * changes won't be able to be saved.
	 *
	 * @return geoCart Returns instance of geoCart to allow method chaining
	 */
	public function save ()
	{
		trigger_error('DEBUG CART: Saving cart TOP');
		if (!$this->cart_variables['id']) {
			//nothing to save...
			trigger_error('DEBUG CART: NOTHING to save, no ID!');
			return $this;
		}
		$sql = "UPDATE ".geoTables::cart." SET `session`=?, `user_id`=?, `admin_id`=?, `order`=?, `main_type`=?, `order_item`=?, `last_time`=?, `step`=? WHERE `id`=? LIMIT 1";
		$query_data =array(
			''.$this->cart_variables['session'],
			(int)$this->cart_variables['user_id'],
			(int)$this->cart_variables['admin_id'],
			(int)$this->cart_variables['order'],
			''.$this->cart_variables['main_type'],
			(int)$this->cart_variables['order_item'],
			(int)$this->cart_variables['last_time'],
			''.$this->cart_variables['step'],
			(int)$this->cart_variables['id'],
		);
		$result = $this->db->Execute($sql, $query_data);
		if (!$result){
			trigger_error('ERROR SQL CART: Sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
		}
		$this->_initReg();
		$this->registry->save();

		if (is_object($this->item)){
			trigger_error('DEBUG CART: Saving item');
			$this->item->save();
		}
		if (is_object($this->order)){
			trigger_error('DEBUG CART: Saving order, kinda weird that we save order after item...');
			$this->order->save();
		}
		return $this;
	}

	/**
	 * Static function ideal for getting rid of a particular cart, without
	 * having to instantiate the cart class.
	 *
	 * @param int $id
	 */
	public static function remove ($id)
	{
		$id = intval($id);//clean input
		if (!$id) {
			return false;
		}

		//remove registry items too
		geoRegistry::remove('cart',$id);

		$db = DataAccess::getInstance();

		$sql = "DELETE FROM ".geoTables::cart." WHERE `id` = ? LIMIT 1";
		$result = $db->Execute($sql, array($id));
	}

	/**
	 * Used to remove a cart session, this is used when a cart is done, paid for,
	 * and needs to be cleared out to allow for more junk to be added to.
	 *
	 * @param int $id If set, will remove the cart for the specified ID instead
	 *  of the one for the current user.
	 * @param bool $saveOrder Added in Version 4.0.9: if true (default) and no ID passed in, it will
	 *  automatically save the order attached to the cart before removing the
	 *  cart.
	 */
	public function removeSession ($id = 0, $saveOrder = true)
	{
		if (!$id) {
			$id = $this->cart_variables['id'];
			if ($saveOrder && is_object($this->order)) {
				//save the order before we remove the session
				$this->order->save();
			}
			//make it not save in app_bottom
			$this->cart_variables['id'] = 0;
		}
		$id = intval($id);//clean input

		self::remove($id);

		//allow order items to do things specific to order item...
		geoOrderItem::callUpdate('geoCart_removeSession');

		//allow from payment gateways too
		geoPaymentGateway::callUpdate('geoCart_removeSession');
	}

	/**
	 * Gets the child order item as specified by the type name.  Gets the one that is a child of the current main order item.
	 * Requires that order and order items are serialized so that they have an ID already.
	 *
	 * Returns null if no child item by that name could be found.
	 *
	 * @param string $item_name
	 * @return geoOrderItem
	 */
	public function getChildItem ($item_name)
	{
		return geoOrderItem::getOrderItemFromParent($this->item, $item_name);
	}

	/**
	 * Generates the URL to be used in a form tag, that would allow the form to submit so that the current page gets processed.
	 * Note that this is ONLY the URL, it is not the entire FORM tag. It automatically accounts for the SSL setting in the
	 * admin turned on or off, as long as $ssl is left to be true.  If $ssl is set to false, it will use the non-ssl URL
	 * even if SSL is turned on in the admin.
	 *
	 * @param boolean $ssl
	 * @param boolean $onlyCart If true, will only return base URL plus a=cart.
	 * @return string
	 */
	public function getProcessFormUrl ($ssl = true, $onlyCart = false)
	{
		//preserve sub-domain
		$base = geoFilter::getBaseHref();

		$vars = array();

		if (defined('IN_ADMIN')) {
			$url = $base . ADMIN_LOCAL_DIR . 'index.php';
			$vars[] = 'page=admin_cart';
			$vars[] = 'userId='.(int)$this->user_data['id'];
		} else {
			//figure out if should be in SSL or not...

			//If on combined step, and no_ssl_force=1, it needs to NOT post to SSL
			//as that can break errors and such.
			$comboNoSsl = ($this->isCombinedStep() && isset($_GET['no_ssl_force']));

			$http = ($ssl && !$comboNoSsl && $this->db->get_site_setting('use_ssl_in_sell_process'))? 'https' : 'http';
			$url = preg_replace('/^https?/', $http, $base).$this->db->get_site_setting('classifieds_file_name');

			$vars[] = 'a=cart';
			if ($comboNoSsl) {
				//Add the no_ssl_force to parameters
				$vars[] = 'no_ssl_force=1';
			}
		}

		if (!$onlyCart) {
			$vars[] = 'action=process';
			if (isset($this->main_type)) {
				$vars[] = 'main_type='.urlencode($this->main_type);
			}
			if ($this->isCombinedStep()) {
				$vars[] = 'step=combined';
			} else if (isset($this->current_step)) {
				$vars[] = 'step='.urlencode($this->current_step);
			}
		}
		//put the URL together
		$url .= '?'.implode('&amp;',$vars);
		return $url;
	}

	/**
	 * Generates the base URL of a=cart without any other parameters.  Handy for instances where you need to
	 * create a url for a certain action, or to link to the cart, just append the extra parameters to the end
	 * of the url returned by this function.  It automatically accounts for the SSL setting in the
	 * admin turned on or off, as long as $ssl is left to be true.  If $ssl is set to false, it will use
	 * the non-ssl URL even if SSL is turned on in the admin.
	 *
	 * @param boolean $ssl
	 * @return string
	 */
	public function getCartBaseUrl ($ssl = true)
	{
		return $this->getProcessFormUrl($ssl, true);
	}

	/**
	 * Gets the action for the page.
	 * @return string
	 */
	public function getAction ()
	{
		return $this->action;
	}

	/**
	 * Gets the cost of all the items in the cart so far, by adding up all the getCost() values.  If
	 * $up_to_process_order is not 0 (default value), it will only add up items who's process
	 * order is less than the process order specified.
	 *
	 * @param int $up_to_process_order
	 * @return float
	 */
	public function getCartTotal ($up_to_process_order = 0)
	{
		return $this->order->getOrderTotal($up_to_process_order);
	}
	/**
	 * Initializes the registry for the cart
	 */
	private function _initReg ()
	{
		if (is_object($this->registry)) {
			return;
		}
		$this->registry = new geoRegistry;
		$this->registry->setName('cart');
		$this->registry->setId($this->cart_variables['id']);
		$this->registry->unSerialize();
	}

	/**
	 * Gets the user data and stores it in this->user_data in array format.
	 *
	 * @param $userId
	 * @return unknown
	 */
	private function _getUserData ($userId = null)
	{
		if (!defined('IN_ADMIN') && $userId === null) {
			$userId = $this->session->getUserId();
		}
		$anonUser = false;
		if (!$userId) {
			//return false;
			$anonReg = geoAddon::getRegistry('anonymous_listing');
			if($anonReg) {
				$userId = $anonReg->get('anon_user_id',0);
				$anonUser = true;
			}
		}
		$user = geoUser::getUser($userId);
		if (!$user) {
			//fallback to make sure user data is never empty handed, this will
			//happen whenever user is not logged in, and anon addon is not enabled
			$this->user_data = $this->db->GetRow("SELECT * FROM `geodesic_groups` WHERE `default_group` = 1 LIMIT 1");
			$this->user_data['id'] = 0;
			return true;
		}

		$this->user_data = $user->toArray();
		if ($anonUser) {
			//set user ID to 0
			$this->user_data['id'] = 0;
		}

		return true;
	}
	/**
	 * Internally used by Internal
	 * @internal
	 */
	private $_price_plans = array(), $_default_price_plan;
	/**
	 * Gets the price plan for the user, and stores it in the cart variable
	 *
	 * @param int $pricePlanId
	 * @param int $category
	 * @return bool True if price plan was retrieved, false otherwise
	 */
	public function setPricePlan ($pricePlanId = 0, $category = 0)
	{
		$pricePlanId = intval($pricePlanId);
		$category = intval($category);

		if (!$pricePlanId || !geoPlanItem::isValidPricePlan($pricePlanId)) {
			if ($this->_default_price_plan) {
				$pricePlanId = $this->_default_price_plan;
			} else {
				//see if we can get the price plan id
				$pricePlanId = $this->_default_price_plan = ((geoMaster::is('classifieds'))? $this->user_data["price_plan_id"]: $this->user_data["auction_price_plan_id"]);
			}
			if (!geoPlanItem::isValidPricePlan($pricePlanId)) {
				//one we got is not valid!  Use default values as failsafe
				$pricePlanId = (geoMaster::is('classifieds'))? 1 : 5;
			}
		}

		if (!$pricePlanId) {
			//there was no price plan ID?
			//echo "no price plan id<Br>\n";
			return false;
		}

		//make sure the price plan is not already set...
		if (isset($this->_price_plans[$pricePlanId][$category])) {
			//price plan already retrieved before, don't need to keep getting it.
			$this->price_plan = $this->site->price_plan = $this->_price_plans[$pricePlanId][$category];
			//make sure the site class knows about the price plan change
			$this->site->users_price_plan = $this->price_plan['price_plan_id'];
			geoOrderItem::reorderTypes($pricePlanId,$category);
			return true;
		}
		//get price plan specifics

		$sql = "SELECT * FROM ".geoTables::price_plans_table." WHERE `price_plan_id` = ? LIMIT 1";
		$price_plan_result = $this->db->GetRow($sql, array($pricePlanId));
		if (!$price_plan_result) {
			trigger_error('ERROR CART SQL: Sql: '.$sql.' Error msg: '.$this->db->ErrorMsg());
			return false;
		}
		$this->price_plan = $price_plan_result;
		$original_category = $category;
		if ($category && $this->price_plan['type_of_billing'] == 1 && (geoPC::is_ent() || geoPC::is_premier())) {
			$stmt_cat_plan = $this->db->Prepare("SELECT * FROM ".geoTables::price_plans_categories_table." WHERE `price_plan_id` = ? AND `category_id` = ? LIMIT 1");
			$stmt_get_parent = $this->db->Prepare("SELECT `parent_id` FROM ".geoTables::categories_table." WHERE `category_id` = ? LIMIT 1");
			do {
				$show_price_plan = $this->db->GetRow($stmt_cat_plan, array($pricePlanId,$category));
				if ($show_price_plan === false) {
					trigger_error('ERROR CART SQL: Error msg: '.$this->db->ErrorMsg());
					return false;
				}

				if (count($show_price_plan) > 0) {
					//found the category price plan to use..
					break;
				}

				$show_price_plan = 0;

				//get category parent
				$show_category = $this->db->GetRow($stmt_get_parent, array($category));
				if ($show_category === false) {
					trigger_error('ERROR CART SQL: Sql: '.$sql.' Error msg: '.$this->db->ErrorMsg());
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

			if (isset($show_price_plan) && is_array($show_price_plan)) {
				//merge the category specific results with the price plan.
				$this->price_plan = array_merge($this->price_plan, $show_price_plan);
			}
		}
		//allow order items to add to the price plan
		//(price plans tied to order items, while user groups tied to payment gateways)
		geoOrderItem::callUpdate('geoCart_setPricePlan',array('price_plan_id'=>$pricePlanId,'category'=>$category));

		//cache it so we don't keep re-doing it for price plans/categories
		$this->_price_plans[$pricePlanId][$original_category] = $this->price_plan;
		//make sure the site knows about the price plan change
		$this->site->price_plan = $this->price_plan;
		$this->site->users_price_plan = $this->price_plan['price_plan_id'];

		geoOrderItem::reorderTypes($pricePlanId,$original_category);
		return true;
	}

	/**
	 * Gets an error message previously set (on same page load) using
	 * geoCart::setErrorMsg()
	 *
	 * @param string $name
	 * @return string The error message, or an empty string if the error message is not found.
	 */
	public function getErrorMsg ($name)
	{
		if (isset($this->error_msgs[$name])) {
			return $this->error_msgs[$name];
		}
		return '';
	}

	/**
	 * Gets all the error messages in an associative array.
	 *
	 * @return array
	 */
	public function getErrorMsgs ()
	{
		return $this->error_msgs;
	}

	/**
	 * Whether or not the current cart is "in the middle of something", in other
	 * words, if you were to attempt to add something new to the cart, would it
	 * be interrupting something?  Be sure the cart is init before calling this,
	 * even if only items are inited.
	 *
	 * @return bool True if it's in the middle of something, false otherwise.
	 * @since Version 4.1.0
	 */
	public function isInMiddleOfSomething ()
	{
		if (($this->isStandaloneCart() || ($this->main_type && $this->main_type != 'cart')) && $this->getAction() != 'cancel' && is_object($this->item)) {
			//it's in the middle of something all right!
			//note that if it is standalone cart, it's always in middle of something...
			return true;
		}
		return false;
	}
	/**
	 * Validate that the step is valid
	 * @param string $name
	 * @return bool
	 */
	private function _validateStep ($name)
	{
		if (is_array($name)) {
			//if array, go through each item and remove it from the array if it is not a valid step.
			foreach ($name as $k => $v) {
				if (!$this->_validateStep($v)) {
					unset($name[$k]);
				}
			}
			return $name;
		}
		if (in_array($name,$this->built_in_steps)) {
			return true;
		}
		$parts = explode(':',$name);
		if (count($parts) != 2) {
			//invalid!
			return false;
		}
		$item = geoOrderItem::getOrderItem($parts[0]);
		if (!is_object($item) || $item->getType() != $parts[0]) {
			//invalid!
			return false;
		}
		if (!method_exists($item,$parts[1].'CheckVars') || !method_exists($item, $parts[1].'Process') || !method_exists($item, $parts[1].'Display')) {
			//one of the required methods was not found, so invalid step,
			//all 3 are required for any step.
			trigger_error('DEBUG CART: eek!! required method not found!');
			return false;
		}
		unset($item);
		//got through all the checks, so must be a good step.
		return true;
	}

	/**
	 * Returns an array with the different parts of the step, like so:
	 * array(
	 * 	item_name => 'item_name',
	 * 	'step' => 'step'
	 * )
	 *
	 * Assumes step has already been checked for validity.
	 *
	 * @param string $step
	 * @return array
	 */
	private function _getStepParts ($step)
	{
		if (in_array($step,$this->built_in_steps)) {
			//item is going to be the main item type
			$item_name = $this->main_type;
			$step_name = $step;
		} else {
			$parts = explode(':',$step);
			trigger_error('DEBUG CART: Parts exploded: <pre>'.print_r($parts,1).'</pre>');
			$item_name = $parts[0];
			$step_name = $parts[1];
		}
		$return = array(
			'item_name' => $item_name,
			'step' => $step_name
		);
		return ($return);
	}

	/**
	 * Gets an array of common template variables that are needed on most
	 * cart pages.
	 *
	 * @return array
	 * @since Version 5.2.0
	 */
	public function getCommonTemplateVars ()
	{
		$tpl_vars = array ();

		$tpl_vars['cart_url'] = $this->getCartBaseUrl();
		$tpl_vars['process_form_url'] = $this->getProcessFormUrl();
		$tpl_vars['in_admin'] = defined('IN_ADMIN');
		$tpl_vars['cart_user_id'] = (int)$this->user_data['id'];
		$tpl_vars['allFree'] = !geoMaster::is('site_fees');

		return $tpl_vars;
	}
}
