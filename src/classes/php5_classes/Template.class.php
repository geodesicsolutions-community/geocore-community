<?php
//Template.class.php
/**
 * Holds the geoTemplate class.
 * 
 * @package System
 * @since Version 4.0.0
 */


/**
 * Smarty could be included via the autoloader in app_top.common.php, but still
 * requiring it here anyways so it's slightly less magical and less confusion for
 * people trying to troubleshoot.
 */
require_once CLASSES_DIR.PHP5_DIR.'smarty/Smarty.class.php';

/**
 * Template object that extends the Smarty class (a 3rd party library) to enable
 * using templates to display things.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoTemplate extends Smarty {
	/**
	 * G-Type:  Addon
	 */
	const ADDON = 'addon';
	
	/**
	 * G-Type:  Admin
	 */
	const ADMIN = 'admin';
	
	/**
	 * G-Type:  Module
	 */
	const MODULE = 'module';
	
	/**
	 * G-Type:  System
	 */
	const SYSTEM = 'system';
	
	/**
	 * G-Type:  External
	 */
	const EXTERNAL = 'external';
	
	/**
	 * G-Type:  Main_page
	 */
	const MAIN_PAGE = 'main_page';
	
	/**
	 * Used to specify a template set that is loaded for all devices.
	 * @var string
	 */
	const DEVICE_ANY = 'any';
	
	
	/**
	 * What this is set to determines the search behavior and where the
	 * template file is located.
	 *
	 * @var string One of: self::ADMIN, self::MODULE, self::SYSTEM, self::ADDON,
	 *  self::MAIN_PAGE, or self::EXTERNAL
	 */
	protected $_g_template_type;
	
	/**
	 * This should be "where" the template is used from.  For example, if
	 * using a template in the file classes/Cart.class.php the resource might be "cart".
	 *
	 * @var string
	 */
	protected $_g_resource;
	
	/**
	 * The page currently being loaded.  Mostly used when the template type is "main_page"
	 *
	 * @var int
	 */
	protected static $_g_page;
	
	/**
	 * The current language ID.
	 *
	 * @var int
	 */
	protected static $_g_language;
	
	/**
	 * The current category id.
	 *
	 * @var int
	 */
	protected static $_g_category;
	
	/**
	 * An array of "template sets" that will be searched through when looking
	 * for a template.  The array is sorted by key prior to doing template searching,
	 * so a key of "default" will always be last when the rest of the keys are
	 * numeric.
	 *
	 * @var array
	 */
	protected static $_g_template_sets = array('default' => 'default');
	/**
	 * USed internally for somthing important.
	 * @internal
	 */
	protected static $_g_template_sets_meta = array();
	
	/**
	 * Array of reserved names, any template sets using these names will be ignored
	 * @var array
	 */
	private static $_invalidTSetNames = array(
		'all',
		'active',
		'merged',
		'_temp'
	);
	
	/**
	 * Adds a template set to the template sets that it will retrieve templates from.
	 *
	 * @param string $name The same as the folder name, only alpha-numeric plus -_ and . (so no spaces)
	 * @param int $language_id If specified, the template set will only be used
	 *   if the current user is using the specified language ID. (param added in
	 *   version 6.0.0)
	 * @param string $device What device to load the template set on, default is
	 *   all devices.  Can set to any of the geoSession::DEVICE_* constants, for
	 *   instance geoSession::DEVICE_MOBILE to only use for mobile devices.  Parameter
	 *   added {@since Version 7.3.0}
	 */
	public static function addTemplateSet($name, $language_id = 0, $device = self::DEVICE_ANY)
	{
		$name = self::cleanTemplateSetName($name);
		if (!$name) {
			//not a valid tempalte set name.
			return;
		}
		$language_id = (int)$language_id;
		$device = trim($device);
		self::$_g_template_sets_meta[$name] = array (
			'language_id' => $language_id,
			'device' => $device,
		);
		if (!defined('IN_ADMIN')) {
			//not in admin panel, only load if special stuff matches up
			$session = geoSession::getInstance();
			if ($language_id > 0 && $language_id !== $session->getLanguage()) {
				//only load template set for specific language, and user is not using
				//that language currently
				return;
			}
			if ($device !== self::DEVICE_ANY && $device !== $session->getDevice()) {
				//only load template set for specific device, and user is not using
				//that device
				return;
			}
		}
		//add it to the array numerically.  Later the array will be sorted, so since the
		//default has index of "default", it will be pushed to the end of the array.
		if (!in_array($name,self::$_g_template_sets)) {
			self::$_g_template_sets[] = $name;
		}
	}
	
	/**
	 * Cleans the template set name given and makes sure it is "valid", if not
	 * valid it returns an empty string.  If it starts with _ or . or is in
	 * the list of invalid names, the name is considered invalid.
	 * 
	 * @param string $name
	 * @return string Returns empty string if string given is "ivalid".
	 */
	public static function cleanTemplateSetName ($name)
	{
		$name = preg_replace('/[^-a-zA-Z0-9_\.]+/','',$name);
		if (in_array($name, self::$_invalidTSetNames) || in_array(substr($entry,0,1), array ('_','.'))) {
			//invalid name, either it is reserved, or it starts with . or _
			return '';
		}
		return $name;
	}
	
	/**
	 * Gets the array of template sets in the order they are used
	 *
	 * @return array
	 */
	public static function getTemplateSets()
	{
		self::loadTemplateSets();
		return self::$_g_template_sets;
	}
	
	/**
	 * Gets an array of template sets for the keys and the language ID to use
	 * for the values.  Will not include any template sets that are not set to
	 * a specific language.
	 * 
	 * @return array
	 * @since Version 6.0.0
	 * @deprecated in Version 7.3.0, use geoTemplate::getTemplateSetsMeta() instead.
	 */
	public static function getTemplateSetsLanguages ()
	{
		self::loadTemplateSets();
		$meta_info = self::$_g_template_sets_meta;
		$tset_languages = array();
		foreach ($meta_info as $name => $meta) {
			if ($meta['language_id']>0) {
				$tset_languages[$name] = $meta['language_id'];
			}
		}
		return $tset_languages;
	}
	
	/**
	 * Get all of the template sets and the meta information for each one, which
	 * is the language_id and the device the template set is loaded with.
	 * 
	 * @return array
	 * @since Version 7.3.0
	 */
	public static function getTemplateSetsMeta ()
	{
		self::loadTemplateSets();
		return self::$_g_template_sets_meta;
	}
	
	/**
	 * Gets a list of template set names that are "reserved names" which will be
	 * ignored by the template system.
	 * @return array
	 * @since Version 5.0.0
	 */
	public static function getInvalidSetNames ()
	{
		//TODO: Add some way for addons to add invalid tempalte set names perhaps?
		return self::$_invalidTSetNames;
	}
	
	/**
	 * Parses the text for any {external file='url'} type tags, and replaces
	 * them with the URL, then returns the parsed text.  This is good for
	 * allowing the use of {external ...} tags inside of text.
	 * 
	 * @param string $text
	 * @return string
	 * @since Version 5.0.0
	 */
	public static function parseExternalTags ($text)
	{
		if (strpos($text, '{external') === false) {
			//nothing found
			return $text;
		}
		//replace {external file="TEXT"} with call to geoTemplate::getUrl('', 'TEXT');
		$text = preg_replace_callback('/\{external [^\}]*?file=(\'|")([^\'"]+)(\'|")[^}]*\}/i', function($matches){return geoTemplate::getUrl('',$matches[2]);}, $text);
		return $text;
	}
	
	/**
	 * Specifies that the current template is an admin template.  The admin templates are a much
	 * simplified version of the front side, in that there is no "resource" to specify, and there
	 * is no template sets, all templates are in the admin/templates/ directory, and when
	 * calling fetch() the filename should be relative to that dir.
	 *
	 * @return geoTemplate Returns an instance of itself to allow chaining
	 */
	public function setAdmin()
	{
		$this->_g_template_type = self::ADMIN;
		//allow chaining
		return $this;
	}
	
	/**
	 * This template is for the specified module, and will be located under the module/module_name/ directory.
	 *
	 * @param string $module_filename The filename of the module (usually without the module_ before it)
	 * @return geoTemplate|bool Returns itself if successful, false otherwise.
	 */
	public function setModule ($module_filename)
	{
		//take off the end .php
		$name = str_replace('.php','',$module_filename);
		//make sure nothing invalid...
		$name = preg_replace('/[^a-zA-Z0-9-_\.]+/','',$name);
		
		if (strlen($name) == 0) {
			//invalid name
			return false;
		}
		$this->_g_template_type = self::MODULE;
		$this->_g_resource = $name;
		//allow chaining
		return $this;
	}
	
	/**
	 * This is a system template, used somewhere like browse_ads or the cart or somewhere.
	 *
	 * @param string $system_filename The system file's filename, without the .class and all lowercase.  If
	 *  the file resides in a sub-directory of the classes folder (for instance, one of the payment gateways
	 *  or order items), use the folder name instead (example: "order_item")
	 * @return boolean True if successful, false otherwise.
	 */
	public function setSystem ($system_filename)
	{
		//take off the end .php
		$name = str_replace('.php','',$system_filename);
		//make sure nothing invalid...
		$name = preg_replace('/[^a-zA-Z0-9-_\.]+/','',$name);
		
		if (strlen($name) == 0) {
			//invalid name
			return false;
		}
		$this->_g_template_type = self::SYSTEM;
		$this->_g_resource = $name;
		//allow chaining
		return $this;
	}
	
	/**
	 * This template is for the specified addon, and is located in one of 2 places:
	 * - GEO_TEMPLATE_DIR/template_set_name/addon/addon_name/ directory (searched first, to allow designers to include templates that override an addon's defaults)
	 * - addons/addon_name/templates/ directory
	 * 
	 * Note that for optimization reasons, it does not verify that the specified addon exists and is enabled, since
	 * typically only the addon itself will be using templates for the addon.
	 *
	 * @param string $addon_name The addon name, same as the addon's folder name and as set in the addon's info class.
	 * @return geoTemplate|bool Returns itself if successful (to allow chaining), false if addon name is not valid
	 */
	public function setAddon($addon_name)
	{
		//Don't bother checking if an addon is enabled, just make sure the name is valid
		//to prevent any funny business, like escaping the directory tree
		$addon_name = preg_replace('/[^a-zA-Z0-9-_]+/','',$addon_name);
		
		if (strlen($addon_name) == 0) {
			//name no good
			return false;
		}
		
		//if setting the addon, that means the type is addon
		$this->_g_template_type = self::ADDON;
		
		$this->_g_resource = $addon_name;
		return $this;
	}
	
	/**
	 * Set the page ID used for displaying a template in the main_page.
	 * 
	 * @param int|string $page_id
	 * @return geoTemplate Returns itself for easy chaining.
	 */
	public function setMainPage ($page_id)
	{
		self::setPage($page_id);
		$this->_g_template_type = self::MAIN_PAGE;
		//allow chaining
		return $this;
	}
	
	/**
	 * Not used yet.  Or is it?
	 *
	 * @param int|string $page_id
	 */
	public static function setPage($page_id)
	{
		$page_id = trim($page_id);
		
		self::$_g_page = $page_id;
	}
	
	/**
	 * Used to set the language ID (going to a Geo language ID) for templates.
	 *
	 * @param int $lang_id
	 */
	public static function setLanguage($lang_id)
	{
		$lang_id = intval($lang_id);
		if (!$lang_id) {
			$lang_id = 1; //default to 1
		}
		self::$_g_language = $lang_id;
	}
	
	/**
	 * Used to set category ID if displaying something specific for a category.
	 *
	 * @param int $category_id
	 */
	public static function setCategory ($category_id)
	{
		$category_id = intval($category_id);
		self::$_g_category = $category_id;
	}
	
	/**
	 * Function to make it easy for things loaded through smarty functions, to
	 * use sub-template, and allow that sub-template to to work easy.
	 * 
	 * @param array $params
	 * @param Smarty_Internal_Template $smarty
	 * @param string $file
	 * @param string $g_type
	 * @param string $g_resource
	 * @param array $tpl_vars
	 * @param string $pre Stug on beginning
	 * @param string $post Stuck on the end
	 * @return string The value to return as a custom smarty function.
	 * @since Geo version 7.1.0
	 */
	public static function loadInternalTemplate ($params, $smarty, $file, $g_type=null, $g_resource=null, $tpl_vars=array(), $pre='',$post='')
	{
		//Need to merge params passed in with template vars, so that the params
		//over-ride template vars.
		
		//But FIRST, create version of params with the "built in" vars removed,
		//we don't want the built-in params to actually be set as template vars.
		$remove = array('g_type','g_resource','assign','file');
		$params_merge = $params;
		foreach ($remove as $key) {
			unset($params_merge[$key]);
		}
		
		$vars = array_merge($tpl_vars, $params_merge);
		
		//even allow file to be over-ridden in params
		$file = (isset($params['file']))? trim($params['file']) : $file;
		
		$_template = $smarty->createTemplate($file, $smarty);
		
		$_template->assign($vars);
		
		//Allow g_type and g_resource to be over-ridden in params
		$g_type = (isset($params['g_type']))? $params['g_type'] : $g_type;
		$g_resource = (isset($params['g_resource']))? $params['g_resource'] : $g_resource;
		
		if ($g_type!==null || $g_resource!==null) {
			//Only set g_type and g_resource if they are specified
			$g_type = $_template->gType($g_type);
			$g_resource = $_template->gResource($g_resource);
			
			//Make sure g_type / g_resource don't persist from higher up parents
			if ($_template->getTemplateVars('g_type')!==null) {
				//g_type is set in parent, so set it here as well so it will over-write
				//one from parent, on any further includes
				$_template->assign('g_type', $g_type);
			}
			if ($_template->getTemplateVars('g_resource')!==null) {
				//g_resource is set in parent, so set it here as well so it will over-write
				//one from parent, on any further includes
				$_template->assign('g_resource', $g_resource);
			}
		}
		$_return = $pre.$_template->fetch().$post;
		
		if ($params['assign']) {
			//assign it to param
			$smarty->assign($params['assign'], $_return);
			return '';
		}
		return $_return;
	}
	
	/**
	 * Creates a new template object, and assigns values for things that the majority of templates can use,
	 * like text messages assigned to smarty variable messages.  It also registers the following modifiers:
	 * fromDb: filters text that is coming from the database
	 * displayPrice: converts a number be formatted according to pre and post currency settings
	 * format_date: takes unix timestamp, and the specified format, and does date on it.
	 *
	 * @param string $template_type If specified, one of: ("module","system","addon","admin")
	 * @param string $resource Only used if template_type is specified and valid, and is not "admin".  
	 *  It is The resource that this template is used from, for example if using this template inside
	 *  classes/browse_ads.php the resource would be "browse_ads".
	 */
	public function __construct ($template_type = null, $resource = null)
	{
		parent::__construct();
		if ($template_type===null && defined('IN_ADMIN')) {
			$template_type = self::ADMIN;
		}
		
		//load all the template sets to choose from
		self::loadTemplateSets();
		if (isset($template_type)) {
			switch ($template_type) {
				case self::MODULE:
					$this->setModule($resource);
					break;
					
				case self::SYSTEM:
					$this->setSystem($resource);
					break;
					
				case self::ADDON:
					$this->setAddon($resource);
					break;
					
				case self::ADMIN:
					$this->setAdmin();
					break;
					
				case self::MAIN_PAGE:
					$this->setMainPage($resource);
					
				default:
					break;
			}
		}
		//set compile dir, be sure to take off the ending slash
		$this->setCompileDir(GEO_TEMPLATE_COMPILE_DIR);
		
		$this->addPluginsDir(CLASSES_DIR . 'geo_smarty_plugins');
		
		$this->default_resource_type = 'geo_tset';
		
		$this->debug_tpl = 'file:'.SMARTY_DIR . 'debug.tpl';
		
		/**
		 * Assign common variables that will be accessible from
		 * all templates.
		 */
		$db = DataAccess::getInstance();
		$session = geoSession::getInstance();
		$addon = geoAddon::getInstance();
		
		$this->assign('messages',$db->get_text(true));
		$this->assign('common_text',$db->get_text(true,10214));
		
		//let templates know if logged in or not
		$logged_in = ($session->getUserId() > 0)? 1: 0;
		$this->assign('logged_in',$logged_in);
		
		//also let them know about common user info
		$user = array();
		$user['id'] = $session->getUserId();
		if ($logged_in) {
			//if logged in, also do the username
			$user['username'] = $session->getUserName();
			//go ahead and give access to all the fun data about this user
			$userData = geoUser::getUser($user['id']);
			if ($userData) {
				$user['user_data'] = $userData->toArray();
			}
			unset($userData);
		}
		$user['detected_robot'] = $session->is_robot();
		$user['is_mobile'] = geoSession::isMobile();
		$user['is_tablet'] = geoSession::isTablet();
		$this->assign('user',$user);
		
		//let template know the current language too
		if (isset(self::$_g_language)) {
			$this->assign('language_id',self::$_g_language);
		} else {
			//grab the language from the session object instead
			$this->assign('language_id',$session->getLanguage());
		}
		
		if (isset(self::$_g_page)) {
			//current page if already set
			$this->assign('page_id',self::$_g_page);
		}
		
		if (isset(self::$_g_category)) {
			//current category if already set
			$this->assign('category_id',self::$_g_category);
			$catId = self::$_g_category;
			//get all the parents
			$parents = array ();
			$sql = "SELECT `parent_id` FROM ".geoTables::categories_table." WHERE `category_id`=?";
			
			while ($catId) {
				$row = $db->GetRow($sql, array($catId));
				if ($row && $row['parent_id']) {
					if (isset($parents[$row['parent_id']])) {
						//stop potential infinite loop on mis-configured sites
						break;
					}
					$parents[$row['parent_id']] = $row['parent_id'];
				}
				$catId = (int)$row['parent_id'];
			}
			$this->assign('parent_categories', $parents);
		}
		$this->assign('classifieds_url',$db->get_site_setting('classifieds_url'));
		$this->assign('site_base_url',self::getBaseUrl());
		$this->assign('classifieds_file_name',$db->get_site_setting('classifieds_file_name'));
		if (geoPC::is_ent()) {
			$this->assign('affiliate_url', $db->get_site_setting('affiliate_url'));
		}
		if($site_name = $db->get_site_setting('friendly_site_name')) {
			$this->assign('site_name', $site_name);
		} else {
			$this->assign('site_name', geoPC::cleanHostName($_SERVER['HTTP_HOST']));
		}
		
		//let templates know what addons are enabled
		$this->assign('enabledAddons', $addon->getEnabledList());
		
		//expose geoturbo status to all templates
		$this->assign('geoturbo_status',geoPC::geoturbo_status());
		
		/**
		 * Register modifiers, filters, and functions to be used in
		 * the smarty templates...
		 */
		
		//Filter: if in demo mode, filter out all e-mail addresses
		if (defined('DEMO_MODE')) {
			$view = geoView::getInstance();
			if (!$view->allowEmail && defined('IN_ADMIN')) {
				$this->loadFilter('output','strip_emails');
			}
		}
		if (geoPC::is_trial()) {
			if (!defined('IN_ADMIN')) {
				//special case, don't put it in seperate file, keep folks from
				//being able to remove it from trials
				$this->registerFilter('output',array('geoTemplate','addPoweredBy'));
				
				$t_sets = self::getTemplateSets();
				if (count($t_sets) > 1) {
					//turn on security, prevent arbitrary code execution
					$security_policy = new Smarty_Security($this);
					//don't allow calling static classes
					$security_policy->static_classes = null;
					//disable all streams
					$security_policy->streams = null;
					
					//add "floor" to default allowed PHP functions, since that is used
					//by default module template..
					//see http://www.smarty.net/docs/en/advanced.features.tpl for
					//list of php functions allowed by default.
					$security_policy->php_functions[] = 'floor';
					//also allow explode modifier, used by cost options template
					$security_policy->php_modifiers[] = 'explode';
					//nl2br is in the default allowed functions, but for some reason gets treated as a modifier sometimes...
					$security_policy->php_modifiers[] = 'nl2br';
					
					//Add security locations
					$secure_dirs = array();
					if (defined('IN_ADMIN')) {
						$secure_dirs[] = ADMIN_DIR.'templates/';
					}
					$secure_dirs[] = GEO_TEMPLATE_DIR;
					
					//add addon template locations
					$list = $addon->getEnabledList();
					foreach ($list as $name => $data) {
						if (file_exists(ADDON_DIR . $name . '/templates/')) {
							$secure_dirs[] = ADDON_DIR . $name . '/templates/';
						}
					}
					
					$security_policy->secure_dir = $secure_dirs;
					$this->enableSecurity($security_policy);
				}
			}
			$this->assign('isTrial',1);
		} else {
			$this->assign('isTrial',0);
		}
		
		if (!defined('IN_ADMIN') && (geoPC::force_powered_by() || !$db->get_site_setting('remove_powered_by'))) {
			$this->registerFilter('output',array('geoTemplate','addPoweredBy'));
		}
		
		//filters: Make addon filter_display_page and filter_display_page_nocache work
		if (!defined('IN_ADMIN') && !defined('IN_GEO_RSS_FEED')) {
			if ($addon->coreEventCount('filter_display_page') > 0) {
				$this->loadFilter('output','filter_page');
			}
			if ($addon->coreEventCount('filter_display_page_nocache') > 0) {
				$this->loadFilter('output','filter_page_nocache');
			}
			//for internal use: demo box
			if (defined('DEVELOPER_MODE')) {
				$this->loadFilter('output','demo_box');
			}
			//see if we need to add base tag automatically
			if ((!$this->_g_template_type || $this->_g_template_type == self::MAIN_PAGE) && geoView::getInstance()->addBaseTag) {
				//only add base tag filter if this is "main page" (or not set) template
				//and not in admin and not in RSS feed
				$this->loadFilter('output','add_basetag');
			}
		}
		
		if (defined('DEMO_MODE') && defined('IN_ADMIN') && geoSession::getInstance()->getUserId > 0) {
			//set up a post filter that strips all form tags
			$this->loadFilter('output','strip_forms');
		}
		//Need to get used tags when compiling
		$this->get_used_tags = true;
		$this->loadFilter('post','process_tags');
		
		if($db->get_site_setting('filter_trimwhitespace') && !defined('IN_ADMIN')) {
			//do not run this in the admin because:
			//1) it doesn't matter
			//2) it chokes on really long forms
			$this->loadFilter('output','trimwhitespace');
		}
		
		if (!defined('IN_ADMIN')) {
			//load any fancy smarty plugins in any of the template sets
			foreach (self::$_g_template_sets as $t_set) {
				if ($t_set==='default') {
					//not going to find in default
					break;
				}
				$smartydir = GEO_TEMPLATE_DIR . "$t_set/smarty/";
				if (file_exists($smartydir) && is_dir($smartydir)) {
					$this->addPluginsDir($smartydir);
					if (file_exists($smartydir."loader.php")) {
						require $smartydir."loader.php";
					}
				}
			}
		}
	}
	
	/**
	 * Loads the template resource handler
	 * @param unknown $resource_type
	 * @return unknown
	 */
	protected function loadTemplateResourceHandler ($resource_type)
	{
		if (in_array($resource_type, 'geotset')) {
			$_resource_class = 'Smarty_Internal_Resource_' . ucfirst($resource_type);
			return new $_resource_class($this->smarty);
		} else {
			return parent::loadTemplateResourceHandler($resource_type);
		}
	}
	
	/**
	 * Used internally, to both get and set the template type at the compiled
	 * template level.  If nothing passed in, it gets the current value, if
	 * something is passed in, it sets the value to that and then returns it.
	 * 
	 * @param string|null $set_value If set and valid, it will set the g_type
	 *   and return that type.  If invalid it will be ignored.
	 * @return string
	 * @since Version 6.0.0
	 */
	public function gType ($set_value = null)
	{
		$valid_types = array (self::MAIN_PAGE, self::SYSTEM, self::ADDON,
			self::MODULE, self::ADMIN);
		if ($set_value !== null && in_array($set_value, $valid_types)) {
			$this->_g_template_type = $set_value;
		}
		
		return $this->_g_template_type;
	}
	
	/**
	 * Used internally, to both get and set the template resource at the compiled
	 * template level.  If nothing passed in, it gets the current value, if
	 * something is passed in, it sets the value to that and then returns it.
	 * 
	 * @param string|null $set_value If set, g_resource will be set to this value
	 *   and returned as the return value.
	 * @return string
	 * @since Version 6.0.0
	 */
	public function gResource ($set_value = null)
	{
		if ($set_value !== null) {
			$this->_g_resource = ''.$set_value;
		}
		return $this->_g_resource;
	}
	
	/**
	 * Convenience method, to get the base URL based on settings set in the
	 * admin.  If currently using SSL connection, it uses SSL URL from admin
	 * settings.
	 * 
	 * @return string
	 */
	public static function getBaseUrl ()
	{
		$urlSetting = (geoSession::isSSL())? 'classifieds_ssl_url' : 'classifieds_url';
		return dirname(DataAccess::getInstance()->get_site_setting($urlSetting)).'/';
	}
	
	/**
	 * Used internally
	 * @var bool
	 * @internal
	 */
	protected static $_g_loadTemplateSetsRun = false;
	
	/**
	 * Loads the template sets to use by including the file GEO_TEMPLATE_DIR/t_sets.php
	 * 
	 * That file should be generated by the admin template control panel
	 *
	 * @param bool $force_reload If true, will force it to re-load the template sets even
	 *  if it was already run in this page load.
	 */
	public static function loadTemplateSets($force_reload = false)
	{
		if (!self::$_g_loadTemplateSetsRun || $force_reload) {
			//current language needs to be set first!
			
			if ($force_reload) {
				//reset the sets, usually this only happens when the sets are
				//being changed, like in the admin.
				self::$_g_template_sets = array ('default' => 'default');
			}
			if (file_exists(GEO_TEMPLATE_DIR . 't_sets.php')) {
				include GEO_TEMPLATE_DIR . 't_sets.php';
			}
			
			self::$_g_loadTemplateSetsRun = true;
			//allow addons to add template sets if they wish
			geoAddon::triggerUpdate('notify_geoTemplate_loadTemplateSets', array('force_reload'=>$force_reload));
		}
		unset(self::$_g_template_sets['default']);
		//be sure to sort the array by key so that default always ends up last
		ksort(self::$_g_template_sets, SORT_NUMERIC);
		//add default after sort is done so it is at end
		self::$_g_template_sets['default'] = 'default';
	}
	
	/**
	 * Used by "custom" smart tag {addon ...} to display something for an addon
	 * tag.
	 * 
	 * @param array $params
	 * @param geoTemplate $smarty
	 * @return string
	 */
	public static function getAddonHtml ($params, $smarty)
	{
		$return = '';
		//check to make sure all the parts are there
		if (!isset($params['author'], $params['addon'], $params['tag'])) {
			//tag not specified
			return '{addon tag syntax error}';
		}
		$auth = $params['author'];
		$addon = $params['addon'];
		$tag = $params['tag'];
		
		if (isset($smarty->tpl_vars['geo_inc_files']['addons'][$auth][$addon][$tag])) {
			//include template
			$vars = $params;
			$params['smarty_include_tpl_file'] = $smarty->tpl_vars['geo_inc_files']['addons'][$auth][$addon][$tag];
			
			if (isset($smarty->tpl_vars['addon_vars'][$auth][$addon][$tag])) {
				//vars set in TPL file over-write vars as set in PHP file...
				$vars = array_merge($smarty->tpl_vars['addon_vars'][$auth][$addon][$tag], $vars);
			}
			$vars['g_type'] = (isset($vars['g_type']))? $vars['g_type'] : self::ADDON;
			$vars['g_resource'] = (isset($vars['g_resource']))? $vars['g_resource'] : $addon;
			
			$params['smarty_include_vars'] = $vars;
			
			$smarty_template_vars = $smarty->tpl_vars;
			$smarty->_smarty_include($params);
			$smarty->tpl_vars = $smarty_template_vars;
		} 
		if (isset($smarty->tpl_vars['addons'][$auth][$addon][$tag]['body'])) {
			//In addition to (not instead of), if module text is set, append that too
			$return .= $smarty->tpl_vars['addons'][$auth][$addon][$tag]['body'];
		}
		return $return;
	}
	
	/**
	 * Run-time cache used so we don't have to scan for a certain template more than once in a page load.
	 *
	 * @var array
	 */
	protected static $_filePaths;
	
	/**
	 * Gets the absolute path to the template file based on the template type, the resource, and the template filename.
	 * It will go through each of the template sets until it finds the file, if it doesn't find it in one template set,
	 * it looks at the next template set in the list, ending with the default template set.  If the template type is
	 * addon, and it hasn't found the template in any of the template sets under template_set/addon/addon_name/template_filename
	 * it then looks in addons/addon_name/templates/template_name 
	 *
	 * @param string $g_type The type of template, either system, addon, admin, or module
	 * @param string $g_resource Typically the file name that the template is used in
	 * @param string $template_filename The filename, if none specified it assumes index.tpl.  Note that the
	 *  template_filename can include directories, but must be relative to the resources directory.  If set to
	 *  index, and it finds the file with the resource name.tpl it will use that.
	 * @param bool $dieOnError If false, will NOT die if the file path is not found, instead
	 *   it would continue and just return the path location that is attempting to be found.
	 * @param bool $falseOnError If true and dieOnError is false, will return false if file not found.
	 * @return string The absolute path to the template file, or the original tempalte_filename specified
	 *  if the absolute path could not be found.
	 */
	public static function getFilePath($g_type, $g_resource, $template_filename = 'index', $dieOnError = true, $falseOnError = false)
	{
		self::loadTemplateSets();
		if (!isset(self::$_filePaths)) {
			//attempt to load cached file paths from geo cache system.
			$file_cache = geoCacheSetting::getInstance()->process('smarty_template_paths');
			if (!is_array($file_cache)) {
				$file_cache = array();
			}
			self::$_filePaths = $file_cache;
		}
		
		if ($g_type != self::EXTERNAL && strpos($template_filename,'.tpl') === false && preg_match ('/\.[a-zA-Z]{3,4}$/',$template_filename) == 0) {
			//add the .tpl to the end if it's not there, and it does not use another 3 or 4 letter extension
			//such as .php, or .html
			$template_filename .= '.tpl';
		}
		//Use string of template sets loaded for caching, so cache still works
		//when the template sets loaded change for different users
		$tsets_loaded = implode('#',self::$_g_template_sets);
		
		if (isset(self::$_filePaths[$tsets_loaded][$g_type][$g_resource.'/'.$template_filename])) {
			//path already known!  We don't have to do a bunch of scanning for files now...
			//Note that it does NOT call file_exists() on purpose, in order to speed
			//things up.  This may result in situation where the cache is out of date
			//and has old locations for files, in that case the cache needs to be reset in
			//the admin.
			return self::$_filePaths[$tsets_loaded][$g_type][$g_resource.'/'.$template_filename];
		}
		
		$path_to_use = false;
		if ($g_type == self::ADMIN) {
			//all admin templates are in the admin/templates/ directory.  There is no seperate template set.
			$path_to_use = ADMIN_DIR . "templates/$template_filename";
		} else {
			$t_set = self::whichTemplateSet($g_type, $g_resource, $template_filename);
			$template_filename = self::cleanFilename($template_filename);
			if ($t_set && ($g_type !== self::ADDON || $t_set !== 'default')) {
				//if the t_set was found, and the type was not addon, or if
				//it was addon, the tset is not default (don't pull any files
				//from default/addon/ to make things easier on addon developers)
				$g_resource_dir = (strlen($g_resource) > 0)? "$g_resource/": '';
				$resource = GEO_TEMPLATE_DIR . "$t_set/$g_type/{$g_resource_dir}$template_filename";
				if (!file_exists($resource)) {
					//if it doesn't exist, it must be one of them fancy ones
					$resource = GEO_TEMPLATE_DIR . "$t_set/$g_type/{$g_resource}.tpl";
				}
				$path_to_use = $resource;
			} else if ($g_type === self::ADDON) {
				//This is an addon, and the template wasn't found in any of the
				//template sets, so see if the file exists in:
				//addons/addon_name/templates/template_filename.tpl
				$resource = ADDON_DIR . $g_resource . "/templates/$template_filename";
				if (file_exists($resource)) {
					$path_to_use = $resource;
				}
			}
		}
		if (!$path_to_use) {
			//path couldn't be found, return the original template filename passed in
			$msg = "ERROR TEMPLATE: Template could not be found for: g_type: $g_type g_resource: $g_resource template_filename: $template_filename";
			//die($msg); //un-comment to easily find when templates dont work.
			trigger_error($msg);
			
			if ($dieOnError) {
				//go ahead and display an error on the page, instead of just causing
				//a fatal error.
				if ($g_resource) $g_type .= '/'.$g_resource;
				self::template404("$g_type/$template_filename");
			}
			if ($falseOnError) {
				return false;
			}
			$path_to_use = $template_filename;
		}
		
		//remember it for the rest of the page load, so if the same template is used
		//again we don't have to re-scan everything
		self::$_filePaths[$tsets_loaded][$g_type][$g_resource.'/'.$template_filename] = $path_to_use;
		
		if (geoCache::get('use_cache')) {
			//If cache is turned on, cache the location too so we don't have to scan after this.
			geoCacheSetting::getInstance()->update('smarty_template_paths',self::$_filePaths);
		}
		
		return $path_to_use;
	}
	
	/**
	 * Run-time cache used so we don't have to scan for a certain template more than once in a page load.
	 *
	 * @var array
	 */
	protected static $_fileSets;
	
	/**
	 * Mostly used internally, to strip off [tset] part off of a filename like [tset]filename.tpl
	 * 
	 * @param string $filename
	 * @return string
	 */
	public static function cleanFilename ($filename)
	{
		if (preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $filename, $match)) {
			//matches [t_set]filename.tpl
			$filename = substr($filename, strpos($filename, ']') + 1);
		}
		return $filename;
	}
	
	/**
	 * Gets which template set the the template file is in based on the template type, the resource, and the template filename.
	 * It will go through each of the template sets until it finds the file, if it doesn't find it in one template set,
	 * it looks at the next template set in the list, ending with the default template set.
	 * 
	 * If the file can not be found in any template sets, returns empty string.
	 *
	 * @param string $g_type The type of template, either self::SYSTEM, self::ADDON, self::MODULE, 
	 *  self::MAIN_PAGE, or self::EXTERNAL
	 * @param string $g_resource Typically the file name that the template is used in
	 * @param string $template_filename The filename, if none specified it assumes index.tpl.  Note that the
	 *  template_filename can include directories, but must be relative to the resource's directory.  If set to
	 *  index, and it finds the file with the resource name.tpl it will use that.
	 * @return string The name for the template set where the template was found, or empty string if not
	 *  found.
	 */
	public static function whichTemplateSet ($g_type, $g_resource, $template_filename = 'index')
	{
		self::loadTemplateSets();
		if (!isset(self::$_fileSets)) {
			//attempt to load cached file paths from geo cache system.
			$file_cache = geoCacheSetting::getInstance()->process('smarty_template_file_sets');
			if (!is_array($file_cache) || defined('THEME_PRIMARY') || defined('THEME_SECONDARY')) {
				//either it isn't an array, or we are using demo theme selector so don't want to use cache for this
				$file_cache = array();
			}
			self::$_fileSets = $file_cache;
		}
		
		if ($g_type != self::EXTERNAL && strpos($template_filename,'.tpl') === false && preg_match ('/\.[a-zA-Z]{3,4}$/',$template_filename) == 0) {
			//add the .tpl to the end if it's not there
			$template_filename .= '.tpl';
		}
		
		$template_sets = self::$_g_template_sets;
		
		//Use string of template sets loaded for caching, so cache still works
		//when the template sets loaded change for different users
		$tsets_loaded = implode('#',$template_sets);
		
		if (isset(self::$_fileSets[$tsets_loaded][$g_type][$g_resource.'/'.$template_filename])) {
			//path already known!  We don't have to do a bunch of scanning for files now...
			//Note that it does NOT call file_exists() on purpose, in order to speed
			//things up.  This may result in situation where the cache is out of date
			//and has old locations for files, in that case the cache needs to be reset in
			//the admin.
			return self::$_fileSets[$tsets_loaded][$g_type][$g_resource.'/'.$template_filename];
		}
		
		//$filename is the filename without any [tset] parts.
		$filename = $template_filename;
		if (preg_match('#^\[(?P<key>[^\]]+)\](?P<file>.+)$#', $template_filename, $match)) {
			//matches [t_set]filename.tpl
			
			if (in_array($match['key'], $template_sets)) {
				//this is one of the template sets, make it the only template set to look in.
				
				$template_sets = array($match['key']);
				$filename = substr($template_filename, strpos($template_filename, ']') + 1);
			}
		}
		//now $filename is actual file name, $template_filename is what to use for index purposes
		
		$res_ends = array();
		//Typically, the template will be name something like this:
		//type/resource/template_name.tpl
		$g_resource_dir = (strlen($g_resource) > 0)? "$g_resource/": '';
		$res_ends [] = "/{$g_type}/{$g_resource_dir}$filename";
		if ($filename == 'index.tpl') {
			//To cut down on un-necessary directories that only have 1 file in them,
			//if the resource is index - also allow it to be accessed using path similar to:
			//type/resource.tpl
			//instead of
			//type/resource/index.tpl
			$res_ends[] = "/{$g_type}/{$g_resource}.tpl";
		}
		$set_to_use = '';
		foreach ($template_sets as $template_set) {
			foreach ($res_ends as $res_end) {
				//This is rather expensive, especially the more "cache set" levels
				//the admin has specified.  We offset this by once we find the location
				//of a template, we remember where it is and save that using geoCache
				//if the geoCache is turned on.
				$resource = GEO_TEMPLATE_DIR . $template_set . $res_end;
				if (file_exists($resource)) {
					//this is the template set
					$set_to_use = $template_set;
					break 2;
				}
			}
		}
		
		//remember it for the rest of the page load, so if the same template is used
		//again we don't have to re-scan everything
		self::$_fileSets[$tsets_loaded][$g_type][$g_resource.'/'.$template_filename] = $set_to_use;
		
		if (geoCache::get('use_cache') && !defined('THEME_PRIMARY') && !defined('THEME_SECONDARY')) {
			//If cache is turned on, cache the location too so we don't have to scan after this.
			//(Don't cache if using demo's theme selector though)
			geoCacheSetting::getInstance()->update('smarty_template_file_sets',self::$_fileSets);
		}
		
		return $set_to_use;
	}
	
	/**
	 * Gets the URL for "external" template files, meaning things like css or js files that are located
	 * in the template set.
	 * 
	 * @param string $g_resource Typically the file name that the template is used in
	 * @param string $filename The filename, if none specified it returns the url to the
	 *  base of the templates directory, where all the template sets are located
	 * @param bool $emptyOnFailure If true, and not able to find specified file, will return
	 *   empty string rather than relative path without tset.
	 * @param bool $forceDefault If true, will only use the url from the default
	 *   template set (mainly used in admin panel), it will also skip checks to make
	 *   sure file exists first.  This param added in version 6.0.0
	 * @return string The URL for where the file exists inside TEMPLATE_SET/external/resource/filename
	 *  or just resource/filename if file could not be found.
	 */
	public static function getUrl ($g_resource='', $filename = '', $emptyOnFailure = false, $forceDefault = false)
	{
		$pre = (defined('IN_ADMIN'))? '' : trim(DataAccess::getInstance()->get_site_setting('external_url_base'));
		if (strlen($filename) == 0) {
			return $pre.GEO_TEMPLATE_LOCAL_DIR;
		}
		if (strpos($filename, 'http://') === 0 || strpos($filename, 'https://') === 0) {
			//it has http:// at front.. don't even insert the prefix
			return $filename;
		}
		
		//search for what template set the file is in
		//Note: No need to cache in geoCache since whichTemplate already does
		$t_set = ($forceDefault)? 'default' : self::whichTemplateSet(self::EXTERNAL,$g_resource,$filename);
		$g_resource = ($g_resource)? $g_resource.'/': '';
		if (!$t_set) {
			//could not find template set, return relative
			return ($emptyOnFailure)? '' : $g_resource.$filename;
		}
		$filename=self::cleanFilename($filename);
		return $pre.GEO_TEMPLATE_LOCAL_DIR . "$t_set/" . self::EXTERNAL . "/{$g_resource}$filename";
	}
	
	/**
	 * For internal use only, used to add "Powered By" text to trial installations.
	 * 
	 * @param $tpl_source
	 * @param $smarty
	 * @return string
	 * @access private
	 */
	public static function addPoweredBy ($tpl_source, $smarty)
	{
		$js = '<script>if (top.location != location){top.location.href = document.location.href;}</script>';
		if (isset($_GET['action']) && $_GET['action'] === 'forcePreview') {
			//don't force the top location on the preview, since that uses a frame
			//when used on combined step
			$js = '';
		}
		$trial = ' :: <span style="color: red;">TRIAL VERSION</span> :: ';
		
		$classOnly = 'http://geodesicsolutions.com/software/classifieds-only.html';
		$auctionOnly = 'http://geodesicsolutions.com/software/auctions-only.html';
		$home = 'http://geodesicsolutions.com/';
		
		$poweredBy = "$js<div style='text-align: center; background-color: white; color: black; font-weight: bold; margin: 10px; border: 1px solid black; padding: 5px;'>
		$trial<a href='$classOnly'>Classified Ad Software</a> and <a href='$auctionOnly'>Auction Software</a> Powered by <a href='$home'>Geodesic Solutions, LLC</a>$trial</div>";
		
		if (!geoPC::is_trial()) {
			//just normal powered by text with embedded logo image
			
			//set to true to load from image file instead of here.
			$load_from_image = false;
			
			//embed image in this file so it cannot be easily removed/changed on leased
			//or trial packages (which are fully encoded).
			$source = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAY'
				.'AAABy6+R8AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwS'
				.'FlzAAALEgAACxIB0t1+/AAAAAd0SU1FB9sHGxQzIrsxZboAAAEBSURBVCjPddI'
				.'xSkNREAXQ8z9RUBB0BRaalBYWNoK4hgjP0gUoau0G7DRCGjvb/y3iKmzcgT/uw'
				.'EKwEEESm3nwDHjhMbw7XGbmzlRN0yhwgTNsocIcbxjjDlJKeoXgFX1/UWEbI5x'
				.'gF+p/BHN8RMz/65yso6UsmGES/EbECY7xWIpOiwpPGObeA8MsyFzVNM0sev/BU'
				.'k62bZtwj/fFWXshgM+FCiOsxzvHTna1LoZdg7Zts+ihMOEl3DvEQY1pUfWyEF5'
				.'hH5vYizxM61hcxk24mfGMI9wW3LiKi+hiiRlf+MYyVgu+wyAvtx9ExkoYUAqmG'
				.'EBduDWI1rqFS+jKA0gp+QVqAEH+iaAnXgAAAABJRU5ErkJggg==';
			
			if ($load_from_image) {
				//load it from logo_powered_by.png in base folder to allow to change
				//it easier.
				$source = 'data:image/png;base64,'.base64_encode(file_get_contents(GEO_BASE_DIR."logo_powered_by.png"));
			}
			
			$img = '<img src="'.$source.'" alt="" style="vertical-align: -2px; width: 13px; height: 13px;" />';
			$poweredBy = "<div style=\"clear: both; text-align: center; color: #a9a9a9; font-size: 10px; font-weight: bold; margin:33px 10px 10px 10px; padding:5px;\"><a href=\"$home\" style=\"color: #a9a9a9; text-decoration: none;\" onclick=\"window.open(this.href); return false;\" rel=\"nofollow\">{$img} Powered by Geodesic Solutions, LLC</a></div>";
		}
		
		$search = array('/<\/body[^>]*\>/i','/<body([^>]*)\>/i');
		$replace = array ($poweredBy.'</body>', '<body\\1>'.$poweredBy);
		if (true || !geoPC::is_trial()) {
			//6/9/2016: remove top bar from trials too, because it futzes with new templates / mobile display
			//only at bottom
			unset($search[1], $replace[1]);
		}
		return preg_replace($search, $replace, $tpl_source);
	}
	
	/**
	 * Displays an error and stops the rest of the page from loading.  Used when
	 * a template file cannot be found in any template sets.
	 * 
	 * @param string $tplFile The file that could not be found
	 * @param string $longMsg Optional additional info, if supplied it will be
	 *   displayed to the user.
	 * @since Version 5.0.0
	 */
	public static function template404 ($tplFile, $longMsg='')
	{
		//Die on error to prevent fatal errors from displaying.
		include GEO_BASE_DIR . 'app_bottom.php';
		//echo '<pre>';throw new Exception ('darn...');
		if (strpos($tplFile,'.php') !== false) {
			$longMsg .= '<br /><br />The Admin may be able to fix this by using the <em>Re-Scan Attachments</em> tool for the template set.';
		}
		die ("<div style='border: 1px black solid; padding: 15px;'>
		<strong style='color:red;'>Template Error:</strong> Template file not found!<br /><br />
		The template file <strong>{$tplFile}</strong> could not be found in any
		of the template sets currently loaded.
		<br /><br />
		{$longMsg}
		</div>");
	}
	/**
	 * Used internally
	 * @var array
	 * @internal
	 */
	private static $_loggedTemplates = array();
	
	/**
	 * Log a template that is used on the current page load.  Normally used internally
	 * only.
	 * 
	 * @param string $t_set
	 * @param string $type Either main_page, external, system, module, or addon
	 * @param string $file The template file, including g_resource.  So, relative
	 *   to that template set and template type's folder.
	 */
	public static function logTemplateUsed ($t_set, $type, $file)
	{
		self::$_loggedTemplates[] = array (
			't_set' => $t_set,
			'type' => $type,
			'file' => $file,
		);
	}
	
	/**
	 * Gets an array of what templates have been logged (used) so far.
	 * 
	 * @return array
	 */
	public static function getLoggedTemplates ()
	{
		return self::$_loggedTemplates;
	}
	

}
