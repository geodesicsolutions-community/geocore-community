<?php
//Admin.class.php
/**
 * Handles loading the page for the admin side.
 *
 * Note: This file was previously named admin_page_loader.php,
 * with main class name of geoAdmin.  Please update
 * any 3rd party references to the old location/class as needed.
 *
 * @package Admin
 * @since Version 4.0.0 (Renamed, used to be AdminPageAutoload.php)
 */

##########GIT Build Data##########
##
## File Changed In GIT Commit:
##
##    16.09.0-105-ga458f5f
##
##################################
//the new page and menu auto loader.


/**
 * Loads the admin pages, and can generate the menu for those
 * pages.
 *
 * @package Admin
 */
class geoAdmin {
/**
 * NOTE:  The below documentation is meant for Geodesic Solution developers, there should be no need to
 *  edit this page.  This page does make it easier to modify where things show up in the admin menu,
 *  but changing anything on this page is not supported.  If you do anything to this file, BACKUP
 *  YOUR FILES AND DB FIRST!!  Again, MODIFICATION IS NOT SUPPORTED!!!
 *
 * This is the new admin page loader.  Here is how it works:  We set menu category objects,
 * and page menu objects which reference those menu categories for the menu tree.  Then we figure
 * out which of those pages to display according to page=page_name in the url.
 *
 * To add a page to the autoloader:
 * 	1. Add a new menu_page object in the constructor (located at the top of the geoAdmin class).
 * 		- The index of the page serves 2 purposes:
 * 			a) it is how the page is referenced in the url (so pages->index == 'admin_email' is
 * 				accessed by &page=admin_email in the url)
 * 			b) there should be a function named 'display_' plus the page array index, so for
 * 			 	pages->index == 'admin_email' there should be a function named display_admin_email()
 * 				inside of the class (see below for info on the class)
 *      - Use the existing page declarations as examples of how to do it.  The addPage function
 * 			is well documented.
 * 		-
 *  2. How to set up your file: (the file specified by filename when you set up the object)
 * 		- Needs to have a class specified by classname.  The constructor is where you put all the
 * 			stuff that needs to be done, that would normally be done in the switch statement in the
 * 			old way of doing things.
 * 		- The class needs to have a function that displays the page.  You can have one file and
 * 			class handle multiple pages(one addPage for each page) by having different display_
 * 			functions, see admin_email_config.php for examples of this.
 * 			- The display function should get an instance of the site class, and use the old methods
 * 				of displaying the page, like using $site->body .= 'text'; and $site->display_page();
 * 				at the end.  Again, use admin_email_config.php for an example of how to do it.
 * 			- There is also the ability to have any forms auto-saved for you, all you need to do is
 * 				have a post var named auto_save and the function $classname->update_$page() will be called.
 * 				If you need to manually load the update function, just don't have any vars named auto_save,
 * 				then just call the custom update function from the class constructor.
 * 		- The display function does not need to worry about displaying the image, or the title at the top of the
 * 			page, both are automatically handled by the auto loading script.  You can, however, overwrite the title
 * 			if you do not want to display the default title, which will be something like
 * 				"Menu Category 1 > Menu Sub-Category 1 > Page Title"
 * 		- If you are having trouble, see the admin_email_config.php for an example
 */

	/**
	 * Used internally
	 * @access private
	 */
	public $menu_html;
	// Use user*() and getUserMessages() functions
	private $_notices = array();
	private $_errors = array();
	private $_successes = array();
	protected $_instance;
	const ERROR = 1;
	const NOTICE = 2;
	const SUCCESS = 3;
	/**
	 * Get an instance of the geoView object.  Uses singleton method.
	 *
	 * @return geoAdmin
	 */
	public static function getInstance ()
	{
		return Singleton::getInstance('geoAdmin');
	}

	public function __construct(){
		//constructor.
		//For now these are done with hard coded arrays,
		//but in the future, if we want we can put them in the database, then admin
		//can change stuff around if they feal like it.

		//set up the categories.
		/*
		 * Pre-defined categories:  (set the parent of a category or page as one of these to have it show up in the pre-defined category):
		 *
		 * what to set parent to -> category name that it will appear under
		 * 0 -> Base Category // Use numerical, 0 to show up at top of category list, 1 to show up after 1st pre-defined category, etc...
		 * site_setup -> Site Setup
		 * registration_setup -> Registration Setup
		 * listing_setup -> Listing Setup
		 * email_setup -> Email Setup //This category uses new way of doing things, but we need to have it in order, so it is a base category
		 * feedback -> Feedback
		 * categories -> Categories
		 * user_groups -> Users / User Groups
		 * pricing -> Pricing
		 *  discount_codes -> Discount Codes
		 * payments -> Payments
		 *  payment_types -> Payment Types
		 *   credit_cart_setup -> Credit Card Setup
		 * transactions -> Transactions
		 *  invoice_balance_system -> Invoice Balance System
		 * geographic_setup -> Geographic Setup
		 * pages_management -> Pages Management
		 *  sections -> Sections
		 * page_modules -> Page Modules
		 *  view_modules -> View Modules
		 * templates -> Templates
		 * languages -> Languages
		 * admin_tools -> Admin Tools & Settings
		 *  messaging -> Messaging
		 *  global_css_mgmt -> Global CSS Managment
		 */

		#Syntax: (see documentation for addMenuCategory for more info)
		#menu_category::addMenuCategory(index, parent, title, image, filename, classname [, head])

		#example of how to create sub-category (following line actually works):
		#menu_category::addMenuCategory('email_config_sub_category', 'email_config', 'E-Mail Sub-Category', '','admin_email_config.php', 'Email_configuration'); //sample sub-category

		//set up the pages
		#syntax: (see documentation for addPage for more info)
		#menu_page::addPage(index, parent, title, image, filename, classname [, type])


		//example of sub-page, to view use the url index.php?mc=email_setup&page=sample_sub_page

		//menu_page::addPage('sample_sub_page','email_general_config','Sample Sub Page','','admin_email_config.php','Email_configuration','sub_page');

		if (geoPC::is_print()) {
			//use print menu
			include ADMIN_DIR . 'menus/print_admin.php';
		} elseif(geoPC::geoturbo_status() === 'on') {
			//stripped-down menu for GT normal
			include ADMIN_DIR . 'menus/adplotter_admin.php';
		} else {
			//TODO:  Add way for addon to load the menus.
			//use normal menu
			include ADMIN_DIR . 'menus/core_admin.php';
		}
	}

	/**
	 * Depreciated!!!  It is still functional, but the prefered method is to
	 * do is set your template file for mainbody using setBodyTpl, and optionally
	 * add any html needed in the head using getView()->head_html .= 'html',
	 * then all you need to do is nothing!  the page loader automatically does the
	 * rest if you do not call display_page().
	 *
	 * Notice that this function can be called statically or as part of an object
	 *
	 * @param string $body What the {body_html} gets replaced with, it is either the template filename, or HTML
	 * @param string $title
	 * @param string $image relative to admin/ folder.
	 * @param string $extra_head_html No longer used
	 * @param string $additional_head_html html to be inserted in head tag
	 * @param string $additional_body_tag_attributes
	 * @deprecated 6/30/3008 - see docs above.  Do not remove for a while, but do not
	 *  use this for new functionality.
	 */
	public static function display_page($body, $title = '', $image = '', $extra_head_html = '', $additional_head_html = '', $additional_body_tag_attributes = '') {
		$db = DataAccess::getInstance();
		$admin = geoAdmin::getInstance();
		$view = $admin->getView();
		$view->addBody($body);

		$view->_head_html .= $extra_head_html . $additional_head_html;

		//bad way to do things...
		//$view->body_tag_html = $additional_body_tag_attributes;

		//set home page if aplicable.
		$admin_last_page_viewed = (defined('DEMO_MODE'))? 'home': $db->get_site_setting('admin_last_page_viewed');

		if (isset($_GET['page'])){
			$page_name = $_GET['page'];
		} elseif ($admin_last_page_viewed) {
			//set page to last page viewed.
			$page_name = $admin_last_page_viewed;
		}
		$view->getTemplateObject()->setAdmin();
		echo $admin->initViewDefault($page_name, $title, $image, $extra_head_html, $additional_head_html);
	}

	/**
	 * Use this to set what template will be used to render the body of the admin
	 * page.  This returns an instance of this to make it easy to chain commands.
	 *
	 * Note: this is an alias of geoView->setBodyTpl()
	 *
	 * @param string $tpl_file
	 * @return geoAdmin
	 */
	public function setBodyTpl($tpl_file, $addon_name = '', $system_resource = '')
	{
		$this->getView()->setBodyTpl($tpl_file, $addon_name, $system_resource);
		return $this;
	}

	public function toString ()
	{
		return $this->getView()->toString().'';
	}

	public function __toString()
	{
		return $this->getView()->toString().'';
	}

	/**
	 * Gets the view object
	 *
	 * @return geoView
	 */
	public function getView()
	{
		return geoView::getInstance();
	}

	/**
	 * Gets the view object.
	 *
	 * Convienience alias of geoAdmin->getView().  Note that this just returns
	 * geoView::getInstance() so if you don't already have an instance of geoAdmin
	 * lying around, it's better to call the geoView::getInstance function directly.
	 *
	 * @return geoView
	 */
	public function v ()
	{
		return geoView::getInstance();
	}

	private $_geoTemplatesDir;
	/**
	 * Gets the top level directory name used for geo_templates, for use when
	 * displaying the geo_templates part of something like:
	 * geo_templates/[template set]/external/<input ... />
	 *
	 * For instance, this is used on category edit.  This should NOT be used for
	 * anything other that displaying to admin panel.
	 *
	 * @return string
	 * @since Version 6.0.0
	 */
	public function geo_templatesDir ()
	{
		if (!isset($this->_geoTemplatesDir)) {
			$this->_geoTemplatesDir = array_pop(explode('/',trim(GEO_TEMPLATE_DIR,'/'))).'/';
		}
		return $this->_geoTemplatesDir;
	}


	private $link;
	/**
	 * generates the end string to a link in the admin menu, without the
	 * page and menu category (mc) specified.
	 *
	 * @return string
	 */
	public function genMenuLink(){
		//generates the end string to a link in the admin menu
		if (isset($this->link)){
			return $this->link;
		}
		$link = array();
		$noadd = array('page','a','mc');
		foreach ($_GET as $key => $val){
			if (!in_array($key, $noadd)){
				$link [] = $key.'='.$val;
			}
		}
		if (count($link)){
			//theres a link to generate, add it yo
			$this->link = '&amp;'.implode('&amp;',$link);
		} else {
			$this->link = '';
		}
		return $this->link;
	}


	/**
	 * Function to automatically load the page.  For this to work, the
	 * page must be added in the constructor, or added in an addon's
	 * admin init function, see the addPage documentation
	 * on how to properly add a page.
	 *
	 * This will also display the page, as long as you have a
	 * display_PageName function, and that function sets which template
	 * to use for the mainbody.
	 *
	 * Note that this is automatically called from the index.
	 */
	public function load_page(){
		$db = $addon = $session = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		if ($session->getUserId() == 0){
			//user not logged in
			return false;
		}
		$adminLandingPage = $db->get_site_setting('adminLandingPage');
		if ($adminLandingPage == 'home') {
			$admin_last_page_viewed = 'home';
		} else if ($adminLandingPage == 'checklist') {
			$admin_last_page_viewed = 'checklist';
		} else {
			$admin_last_page_viewed = (defined('DEMO_MODE'))? 'home': $db->get_site_setting('admin_last_page_viewed');
			if($admin_last_page_viewed === 'addon_bulk_uploader_main_config') {
				//special case -- user quit admin in the middle of the bulk uploader somewhere
				//can't trust bulk uploader session data here -- go home to prevent corrupted uploads
				$admin_last_page_viewed = 'home';
			}
		}

		if (isset($_GET['page'])){
			$page_name = $_GET['page'];
		} elseif ($admin_last_page_viewed) {
			//set page to last page viewed.
			$page_name = $admin_last_page_viewed;
		} else {
			$page_name = 'home';
		}

		$is_cat_page = false;
		$isCat = true;
		if (isset($page_name) && strlen($page_name) > 0){
			//get the data for this page.
			$page_data = menu_page::getPage($page_name,false);
			if (!is_object($page_data)){
				$page_data = menu_category::getMenuCategory($page_name,false);
				$is_cat_page=true;
			}
			if (is_object($page_data)){
				$page_data->current = true;
				$is_cat_page=false;
			}
		}
		if (!$is_cat_page && isset($_GET['mc'])){
			$menu_name = $_GET['mc'];
			$current_cat = menu_category::getMenuCategory($menu_name,false);
			if (!$current_cat){
				//if it is a sub-page
				$current_cat = menu_page::getPage($menu_name,false);
				$isCat = false;
			}
		}
		if (is_object($page_data) && (!isset($menu_name) || !is_object($current_cat) || !in_array($page_name, $current_cat->children_pages))) {
			//either parent was not specified, or page was not found in parent's children pages.
			//so set parent automatically.
			if ($is_cat_page){
				$menu_name = $page_data->index;
				$current_cat = $page_data;
				$isCat = false;
			} else {
				$menu_name = $page_data->parent;
				$current_cat = menu_category::getMenuCategory($menu_name,false);
			}

			if (!$current_cat){
				//if it is a sub-page
				$isCat = false;
				$current_cat = menu_page::getPage($menu_name,false);
			}
		}

		if (is_object($current_cat)){
			$current_cat->current = true;
			//mark all parent categories as current too
			$menu_category_data = $current_cat;
			//$page_data->breadcrumb_title = $page_data->title;
			$page_data->breadcrumb_title = '';

			//generate the breadcrumb
			$linky = $this->genMenuLink();
			if (isset($current_cat->title) && strlen($current_cat->title)){
				$title = $current_cat->title;

				//quick hack to make the addon names show as a page subtitle (but NOT on the edit text (Addon_Manage) page (where it uses a different variable))
				if($current_cat->parent === 'addon_management' && $current_cat->classname !== 'Addon_Manage') {
					$this->v()->addon_title = $title;
				}

				if (((isset($current_cat->type) && ($current_cat->type == 'main_page' || $current_cat->type == 'main_page_nosave')) || !isset($current_cat->type)) && isset($current_cat->filename,$current_cat->classname) && strlen($current_cat->filename) && strlen($current_cat->classname)){
					//make it a link.
					$title = '<a href="index.php?mc='.$current_cat->parent.'&amp;page='.$current_cat->index.$linky.'" class="pg_title1">'.$title.'</a>';
				}

				$title = '<span>'.$title.'</span>';
				$page_data->breadcrumb_title = $title.'<span> <i class="fa fa-chevron-right"> </i> </span>'.$page_data->breadcrumb_title;

				$page_data->wiki_uri = $this->cleanWikiTitle($current_cat->title).';';
			}

			while(isset($current_cat->parent)){
				$parent = $current_cat->parent;
				$isCat = true;
				$current_cat = menu_category::getMenuCategory($parent,false);
				if (!$current_cat){
					//if it is a sub-page
					$current_cat = menu_page::getPage($parent,false);
					$isCat = false;
				}
				$current_cat->current = true;
				if (strlen($current_cat->title)){
					$title = $current_cat->title;
					if (isset($current_cat->filename,$current_cat->classname) && strlen($current_cat->filename) && strlen($current_cat->classname)){
						//make it a link.
						$title = '<a href="index.php?mc='.$current_cat->parent.'&amp;page='.$current_cat->index.$linky.'" class="pg_title1">'.$title.'</a>';
					}
					$title = '<span>'.$title.'</span>';
					//generate the breadcrumb, adding links where appropriate.
					//make it so that it only wraps with "> title" at beginning of next line...
					$page_data->breadcrumb_title = $title.'<span> <i class="fa fa-chevron-right"> </i> </span>'.$page_data->breadcrumb_title;

					$page_data->wiki_uri = $this->cleanWikiTitle($current_cat->title).';'.$page_data->wiki_uri;
				}
			}
		}

		if (isset($page_data) && is_object($page_data)) {
			//include the file, if it is given.
			if ($page_data->filename && $page_data->filename != 'addon'){
				if (file_exists($page_data->filename)){
					require_once($page_data->filename);
				} else {
					//file was not found, show an error.
					geoAdmin::display_page('<h2>Error: File Not Found for this page.</h2>Please make sure this file is uploaded to the proper folder, then refresh the page: <strong>'.$page_data->filename.'</strong>');
					return false;
				}
			}
			//constructor should then take over and run anything it needs to,
			//like geoAdmin::display_page()
			$classname = $page_data->classname;
			if (!(strlen($classname) && class_exists($classname))){
				geoAdmin::display_page('<h2>Error: Could not load requested page.</h2>Check the link and try again.  If this link is for a specific addon, make sure that addon is installed & enabled.');
				return false;
			}
			$page = Singleton::getInstance($classname);
			$function_display = 'display_'.$page_name;
			$function_update = 'update_'.$page_name;
			//set the URL for this current page
			$this->currentPageUrl = "index.php?page=$page_name&amp;mc=$menu_name";


			//if there is a post var named auto_save, attempt to save the settings.
			//To use this auto-updater, name your submit button form field auto_save, and make a function
			//named update_$index (replace $index of course) that returns true if all settings were saved correctly, or false otherwise.
			if ((isset($_POST['auto_save']) || isset($_REQUEST['auto_save_ajax'])) && !defined('DEMO_MODE') && geoAddon::triggerDisplay('auth_admin_update_page', $page_name, geoAddon::NOT_NULL)!==false){
				//then update the data.
				//echo 'Saved?';
				if (isset($_GET['auto_save_ajax']) && count($_POST) == 0){
					//cjax used, so set _POST = _GET
					$_POST = $_GET;
				}

				if ((method_exists($page, $function_update) || method_exists($page,'__call')) && $page->$function_update()) {
					//calling update function was successful
					if ((count($this->_errors) + count($this->_notices) + count($this->_successes)) == 0) {
						$this->userSuccess('Settings saved.');
					}
				} else {
					if ((count($this->_errors) + count($this->_notices) + count($this->_successes)) == 0){
						$this->userError('Settings NOT saved, check for errors in any fields.');
					}
				}
			} else if (isset($_GET['auto_save']) && !defined('DEMO_MODE')) {
				//show confirm message
				$html = '';
				$parts = array();

				switch($_GET['auto_save']) {
					case 1:
						$css = 'confirm_popup_delete';
						break;
					case 2:
						//break intentionally ommited
					default:
						$css = 'confirm_popup';
				}
				//temporary until all uses of this are using lightbox ajax
				$closeMethod = (geoAjax::isAjax()) ? 'class="closeLightUpBox"' : 'onclick="history.go(-1);"';

				if (geoAddon::triggerDisplay('auth_admin_update_page', $page_name, geoAddon::NOT_NULL)!==false) {
					//they have permission to do this...
					foreach($_GET as $key => $value){
						if (is_array($value)){
							foreach ($value as $sub_key => $sub_val){
								$html .= '<input type="hidden" name="'.$key.'['.$sub_key.']" value="'.$sub_val.'" />
	';
								$parts[] = $key.'['.$sub_key.']='.$sub_val;
								$form_page .= '';
							}
						} else {
							$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />
	';
							if ($key != 'auto_save') $parts[] = $key.'='.$value;
						}
					}

					$html = '<div style="text-align: center; border: 1px solid #FFF;"><div class="'.$css.' lightUpMover">Are you sure?</div>

	<form action="index.php?'.implode('&amp;',$parts).'" method="post">
	'.$html;

					$html .= '<input style="text-align=center" type="submit" name="auto_save" value="Yes" />
						 <input style="text-align=center" type="button" '.$closeMethod.' value="No" />
						</div></form>';
				} else {
					$html .= '<div style="text-align: center; border: 1px solid #FFF;"><div class="'.$css.' lightUpMover">Access Denied</div>
					<div class="page_note">This user does not have access to<br />perform this action.</div>
					<input style="text-align=center" type="button" '.$closeMethod.' value="Close" />';
				}
				if(geoAjax::isAjax()) {
					echo $html;
				} else {
					geoAdmin::display_page($html,' (Confirm Action)');
				}
				return true;
			}

			//display the page.
			$page->$function_display();
			$page_name = $page_data->index;
		} else if (isset($menu_category_data->filename) && $menu_category_data->filename!='') {
			//the filename for the category is set, so we should display that category page.
			include_once($menu_category_data->filename);
			$classname = $menu_category_data->classname;
			$page = new $classname();
			$function = 'display_'.$menu_name;
			$page->$function();
			$page_name = $menu_category_data->index;
		} else {
			if (menu_page::getPage('home',false)) {
				//load default page
				include_once(ADMIN_DIR . 'home.php');
				$page = new geoAdminHome;
				$page->display_home();
				$page_name = 'home';
			} else {
				//do not have permission to load the home page, so load the
				//admin map - that is the one page that will not have any sensitive
				//info on it, as it only shows other pages the user has permission to view
				include_once(ADMIN_DIR.'map.php');
				$page = new geoAdminMap;
				$page->display_site_map();
				$page_name = 'site_map';
			}
		}
		//render the page, if it is set to do so
		if (!$this->isRendered()) {
			$this->v()->getTemplateObject()->setAdmin();
			if (!isset($this->v()->geo_inc_files['body_html']) && !isset($this->v()->body_html)) {
				$this->setBodyTpl('no_body_template.tpl');
			}
			echo $this->initViewDefault($page_name)->toString();
		}
	}

	/**
	 * Cleans text to make it usable in the URL for the wiki link.
	 *
	 * This gets rid of tags, replaces non asci/numeric chars
	 * with spaces, and gets rid of multiple spaces.
	 *
	 * @param string $title
	 * @return string
	 */
	public function cleanWikiTitle ($title)
	{
		$title = strtolower($title);
		//get rid of html tags
		$title = strip_tags ($title);
		//convert things like &amp; to &
		$title = geoString::specialCharsDecode($title);
		//get rid of any non-asci/numeric vars, replaced them with space
		$title = preg_replace('/[^a-z0-9]+/',' ',$title);

		//trim extra space around it
		$title = trim($title);

		//get rid of multiple spaces together, replace with _
		$title = preg_replace('/[\s]+/','_',$title);

		return $title;
	}

	/**
	 * Initializes common things that pretty much every page load needs
	 * to be able to display the page.
	 *
	 * @param string $page_name The page index for the current page.
	 * @return geoAdmin for easy chaining
	 */
	public function initViewDefault ($page_name = '', $title='', $image='', $extra_head_html='', $additional_head_html='')
	{
		$view = $this->getView();
		$db = DataAccess::getInstance();
		if (isset($page_name) && strlen($page_name) > 0){
			//get the data for this page.
			$page_data = menu_page::getPage($page_name,false);

			if (is_object($page_data)) {
				if (isset($page_data->breadcrumb_title)) {
					//the rest of the breadcrumb was generated in load_page()
					$view->breadcrumb_title = $page_data->breadcrumb_title.'<span>'.$page_data->title.$title.'</span>';
				}

				$page_data->wiki_uri .= $this->cleanWikiTitle($page_data->title).';start';

				$view->wiki_uri = $page_data->wiki_uri;
				$view->page_title = $page_data->title.$title;
				$view->image = $page_data->image;
				$view->image_fa  = $page_data->image_fa;
				if($view->image_fa && strpos($view->image, '/') !== false) {
					//remove "admin_images/" from filename
					$view->image = substr($view->image,strrpos($view->image,'/')+1);
				}
				if ($page_data->type == 'main_page' && $page_data->index != $admin_last_page_viewed) {
					//set last viewed page.
					$db->set_site_setting('admin_last_page_viewed',$page_name);
				}
				//auto load js file with same name as the file name for this page
				$js_filename = 'js/'.str_replace('.php','.js',$page_data->filename);
				if (file_exists(ADMIN_DIR . $js_filename)) {
					//include js file
					$view->addJScript($js_filename);
				}
			}
		}
		$type = 'Core';
		$typeDisplay = 'GeoCore';

		if (geoPC::license_only()) {
			$typeDisplay .= ' '.ucfirst(geoPC::license_only());
			$view->license_only = geoPC::license_only();
		} else {
			$typeDisplay .= ' MAX';
		}

		if (geoPC::is_trial()) {
			$typeDisplay .= ' (Trial)';
		}
		if (geoPC::is_leased()) {
			$typeDisplay .= ' (Leased)';
		}

		if($gt = geoPC::geoturbo_status()) {
			$typeDisplay = "GeoTurbo";
			if($gt === 'plus') $typeDisplay .= " Plus";
		}

		$view->product_type = $type;
		$view->product_typeDisplay = $typeDisplay;
		$view->is_beta = (stripos(geoPC::getVersion(),'beta')!==false) ? true : false;
		$view->is_rc = (stripos(geoPC::getVersion(),'rc')!==false) ? true : false;
		$view->white_label = geoPC::is_whitelabel();

		$view->product_version = geoPC::getVersion();
		require_once ADMIN_DIR . PHP5_DIR . 'Notifications.class.php';

		$view->notifications  = Notifications::getNotifications();

		$view->page_structure = $this->getMenuTemplateVars();
		//echo '<pre>'.print_r($view->page_structure,1).'</pre>';
		//make sure the default css and js files are loaded that are loaded on
		//every page.
		//add to array one by one to allow checks for each one.. also to preserve
		//exact order, since certain things need to be loaded in certain order

		$jsScripts = $cssScripts = array ();
		$jsScripts[] = 'js/settings.js';
		$jsScripts[] = geoView::JS_LIB_JQUERY;
		$jsScripts[] = geoView::JS_LIB_JQUERY_UI;
		$cssScripts[] = geoView::CSS_LIB_JQUERY_UI;
		$jsScripts[] = geoView::JS_LIB_PROTOTYPE;
		//see if need to add wysiwyg
		if ($view->editor && $db->get_site_setting('use_admin_wysiwyg') == 'TinyMCE') {
			$jsScripts[] = "//cdn.tinymce.com/4/tinymce.min.js";
		}
		$jsScripts[] = geoView::JS_LIB_SCRIPTACULOUS;
		//load the main.js file from default template set only
		$jsScripts[] = '../'.geoTemplate::getUrl('js','main.js', false, true);
		$jsScripts[] = '../'.geoTemplate::getUrl('js','gjmain.js', false, true);
		//load all the custom jquery plugins
		$plugins = array('utility','simpleCarousel','lightbox','imageFade','progress','tabs','jQueryRotate');
		foreach ($plugins as $plugin) {
			$jsScripts[] = '../'.geoTemplate::getURL('js',"plugins/{$plugin}.js", false, true);
		}
		$jsScripts[] = 'js/general.js';
		$jsScripts[] = 'js/side_menu.js';
		$jsScripts[] = 'js/tooltip.js';
		$jsScripts[] = 'js/fieldset_toggle.js';

		$cssScripts[] = 'css/body_html.css';
		$cssScripts[] = 'css/index.css';
		$cssScripts[] = 'css/side_menu.css';
		$cssScripts[] = 'css/head_html.css';

		//prepend them, so if someone added their own, they will be added
		//after these so prototype and all that will be loaded already.
		$view->addCssFile($cssScripts,'prepend')
			 ->addJScript($jsScripts, 'prepend');

		if(defined('DEVELOPER_MODE')) {
			$view->developer_mode = DEVELOPER_MODE;
		}

		return $this;
	}
	/**
	 * Whether or not the page has been rendered or not.
	 *
	 * Note: This is an alias of {@link geoView::isRendered()}
	 *
	 * @return bool
	 */
	public function isRendered()
	{
		return geoView::getInstance()->isRendered();
	}

	/**
	 * Sets whether the admin page has been rendered.  If set to true, when it gets time
	 * to auto render the page, it won't auto render.  This is handy if you need to display
	 * the page in a non-standard way, like if you just want to echo something out..
	 *
	 * Note: This is an alias of {@link geoView::setRendered}
	 *
	 * @param bool $is_rendered
	 * @return geoAdmin
	 */
	public function setRendered($is_rendered)
	{
		geoView::getInstance()->setRendered($is_rendered);
		return $this;
	}

	private $_menu_vars;
	/**
	 * Gets the currently initialized menus and pages in an associative array format.  Handy
	 * for displaying menus or whatever.
	 *
	 * @param string $top_level If specified, will only get it starting with a top level
	 *  of whatever this is set to.
	 * @return array
	 */
	public function getMenuTemplateVars ($top_level = null)
	{
		if ($top_level === null && isset($this->_menu_vars) && is_array($this->_menu_vars)) {
			return $this->_menu_vars;
		}

		/**
		 * This will be an array like so:
		 * array(
		 * 	'index_item1' => array (
		 * 		'title' => 'title',
		 * 		'link' => 'full_link.php',
		 * 		'type' => 'sub_page|full_page|category',
		 * 		'children' => array (
		 * 			'index_item2'...
		 * 		)
		 * 	)
		 * )
		 */
		if ($top_level === null) {
			$top_categories = menu_category::getAllParentCats();
		} else {
			$top_categories = array ($top_level);
		}
		$menu_items = array();
		$skip = array('children_pages', 'children_categories');
		foreach ($top_categories as $category) {
			$obj = menu_category::getMenuCategory($category,false);

			//whether or not this has children or not to show,
			//this allows us to not show categories that are empty
			$show_level = false;

			if (!is_object($obj)) {
				$obj = menu_page::getPage($category,false);
				if (!is_object($obj)) {
					//could not get it, most likely invalid or something
					return array();
				}
				//this is a page, and a page can't be empty since it is what fills stuff...
				$show_level = true;
			}

			if (isset($obj->children_categories) && count($obj->children_categories) > 0) {
				//recursevly get all children categories
				foreach ($obj->children_categories as $sub_cat) {
					$sub = $this->getMenuTemplateVars($sub_cat);
					if (count($sub) > 0) {
						//a sub-category is not empty, so show this parent category too
						$show_level = true;
						$menu_items[$category]['children_categories'][$sub_cat] = $sub;
					}
				}
			}

			if (isset($obj->children_pages) && count($obj->children_pages) > 0) {
				//recursively get all children pages
				foreach ($obj->children_pages as $sub_page) {
					$sub = $this->getMenuTemplateVars($sub_page);
					if (count($sub) > 0) {
						//a sub-page is not empty, so show this parent category too
						$show_level = true;
						$menu_items[$category]['children_pages'][$sub_page] = $sub;
					}
				}
			}

			if (!$show_level) {
				//This is a category and is empty, don't show it.
				continue;
			}

			//set all the details about this category or page
			foreach ($obj as $i => $val) {
				if (!in_array($i,$skip)) {
					$menu_items[$category][$i] = $val;
				}
			}
			//generate the breadcrumb
			$breadcrumb = $obj->title;
			$wiki = $this->cleanWikiTitle($obj->title).';start';

			$menu_name = $obj->index;
			$main_title = $obj->title;
			$current_cat = $obj;

			while(isset($current_cat->parent)){
				//generate breadcrumb
				$parent = $current_cat->parent;
				$current_cat = menu_category::getMenuCategory($parent,false);
				if (!$current_cat) {
					//if it is a sub-page
					$current_cat = menu_page::getPage($parent,false);
				}
				if (strlen($current_cat->title)){
					$title = $current_cat->title;
					//generate the breadcrumb, adding links where appropriate.
					$breadcrumb = $title.' &gt; '.$breadcrumb;
					$wiki = $this->cleanWikiTitle($title).';'.$wiki;
				}
			}

			$menu_items[$category]['breadcrumb'] = $breadcrumb;
			$menu_items[$category]['wiki_uri'] = $wiki;
		}
		if (count($menu_items) == 0) {
			//this is empty, return empty array
			return array();
		}
		if ($top_level === null) {
			//this is the top level, so return the entire array
			$this->_menu_vars = $menu_items;
			return $menu_items;
		}
		//this is not the top level, return the info to the parent that called this
		return $menu_items[$top_level];
	}

	/**
	 * Adds an error for the user to see at the top of admin pages
	 * Use for stuff like SQL errors, site errors, user-input errors
	 * Do NOT use for debugging, or technical error messages (use trigger_error instead)
	 *
	 * @param string $str
	 * @return geoAdmin
	 * @since 3.1.0
	 */
	public function userError( $str ) {
		if( null === $str ) {
			$str = "Internal error. Please contact <a href='http://www.geodesicsolutions.com/support/index.htm'>support</a>.";
		}
		$this->_errors[] = $str;

		// Let everything else know about this
		geoAddon::triggerUpdate('notify_user',$str);
		return $this;
	}

	/**
	 * Adds a notice for the user to see at the top of admin pages
	 * Use for informative messages other than errors and successes
	 * Do NOT use for debugging (use trigger_error instead)
	 *
	 * @param string $str
	 * @return geoAdmin
	 * @since 3.1.0
	 */
	public function userNotice( $str ) {
		$this->_notices[] = $str;

		// Let everything else know about this
		geoAddon::triggerUpdate('notify_user',$str);
		return $this;
	}

	/**
	 * Adds a success message for the user to see at the top of admin pages
	 * Use for messages like "Settings saved", "Item created", "Item removed", etc
	 * Do NOT use for debugging (use trigger_error instead)
	 *
	 * @param string $str
	 * @return geoAdmin
	 * @since 3.1.0
	 */
	public function userSuccess( $str )
	{
		$this->_successes[] = $str;

		// Let everything else know about this
		geoAddon::triggerUpdate('notify_user',$str);
		return $this;
	}

	/**
	 * Gets the number of notice, success, and error messages.
	 *
	 * @param enum(self::SUCCESS, self::NOTICE, self::ERROR) $message_type If specified,
	 *  will return count of only the type of message specified.
	 * @return int
	 * @since 4.0.0
	 */
	public function getMessageCount ($message_type = null)
	{
		if ($message_type !== null) {
			switch ($message_type) {
				case self::SUCCESS:
					return count($this->_successes);
					break;

				case self::NOTICE:
					return count($this->_notices);
					break;

				case self::ERROR:
					return count($this->_errors);
					break;

				default:
					//let it fall through to return count
					//of all message types.
					break;
			}
		}
		return count($this->_errors) + count($this->_notices) + count($this->_successes);
	}

	/**
	 * Gets the user messages, formatted in HTML
	 *
	 * @return string
	 * @since 3.1.0
	 */
	public function getUserMessages() {
		$messages = "";
		if( count( $this->_errors ) ) {
			$messages .= "
			<div class='userMessage error'>
				<ul>
					<li>" . implode( "</li><li>", $this->_errors ) . "
					</li>
				</ul>
			</div>";
		}
		if( count( $this->_notices ) ) {
			$messages .= "
			<div class='userMessage notice'>
				<ul>
					<li>" . implode( "</li><li>", $this->_notices ) . "
					</li>
				</ul>
			</div>";
		}
		if( count( $this->_successes ) ) {
			$messages .= "
			<div class='userMessage success'>
				<ul>
					<li>" . implode( "</li><li>", $this->_successes ) . "
					</li>
				</ul>
			</div>";
		}
		return $messages;
	}

	/**
	 * Get the messages in an array, this is handy when needing to get the messages
	 * for something like JSON output.
	 *
	 * @return array Array with index's of "errors", "notices", and "successes",
	 *   if the entry has no rows then there aren't any messages of that type
	 * @since Version 7.2.0
	 */
	public function getMessagesArray ()
	{
		return array(
			'errors' => $this->_errors,
			'notices' => $this->_notices,
			'successes' => $this->_successes,
			);

	}

	/**
	 * Alias of userNotice, userError, userSuccess, and getUserNotices functions,
	 * all wrapped up in this nice tidy wrapper.
	 *
	 * @param string|null $str If null, will act like getUserMessages(), otherwise will add a user
	 *  message.
	 * @param enum(self::ERROR, self::NOTICE, self::SUCCESS) $type
	 * @return geoAdmin|string
	 */
	public function message ($str=null, $type = geoAdmin::SUCCESS)
	{
		if($str===null) return $this->getUserMessages();

		switch ($type) {
			case self::SUCCESS:
				// succes message display a cool message
				$this->_successes[] = $str;
				break;

			case self::NOTICE:
				$this->_notices[] = $str;
				break;

			default:
				//break ommited on purpose
			case self::ERROR:
				$this->_errors[] = $str;
				break;

		}

		geoAddon::triggerUpdate('notify_user',$str);
		return $this;
	}

	/**
	 * Conienience function, to make easy way to call {@link geoAdmin::message} without
	 * haveing to get an instance first.
	 *
	 * @param string|null $str If null, will act like getUserMessages(), otherwise will add a user
	 *  message.
	 * @param enum(self::ERROR, self::NOTICE, self::SUCCESS) $type
	 * @param bool $is_cjax_msg If true, will send a CJAX message (for use in ajax
	 *  return messages), need to set string and type to use this.
	 * @param int $cjax_time Used when $is_cjax_msg = true, Set this to the time in
	 *  seconds to keep the message on the screen, or 0 to keep on screen.
	 * @return geoAdmin|string
	 */
	public static function m ($str=null, $type = geoAdmin::SUCCESS, $is_cjax_msg = false, $cjax_time = 3)
	{
		if ($is_cjax_msg) {
			//auto display it to cjax
			$cjax_time = intval($cjax_time);
			$cjax_time = ($cjax_time)? $cjax_time: '';

			$cjax = geoCJAX::getInstance();
			$cjax->message(geoAdmin::getInstance()->message($str, $type)->message(),$cjax_time);
			return geoAdmin::getInstance();
		}
		return geoAdmin::getInstance()->message($str, $type);
	}

	/**
	 * Sees whether or not the current admin user is allowed to either display
	 * or update a specified page.
	 *
	 * @param string $page The page index
	 * @param string $access Either "display" or "update", if it is neither it will return false
	 * @return bool
	 */
	public function isAllowed($page, $access='display')
	{
		if ($access == 'display') {
			return geoSession::getInstance()->getUserId() == 1 || geoAddon::triggerDisplay('auth_admin_display_page',geoAddon::NOT_NULL) !== false;
		} else if ($access=='update') {
			return geoSession::getInstance()->getUserId() == 1 || geoAddon::triggerDisplay('auth_admin_update_page',geoAddon::NOT_NULL) !== false;
		}
		//what they want???
		return false;
	}
}

/**
 * Menu category object, holds information about an admin category.
 *
 * @package Admin
 */
class menu_category {
	/**
	 * Index for category
	 *
	 * @var string
	 */
	public $index;

	/**
	 * Parent index
	 *
	 * @var string
	 */
	public $parent;

	/**
	 * Is this the current page that is being displayed?
	 *
	 * @var boolean
	 */
	public $current;

	/**
	 * Title, used in menu and breadcrumb
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Generated automatically when page is loaded, this is a breadcrumb
	 * that is linked to pages.
	 *
	 * @var string
	 */
	public $breadcrumb_title;

	/**
	 * The end part of a wiki link to info for this page.
	 * @var string
	 */
	public $wiki_uri;

	/**
	 * Filename containing display functions, or empty string for no display
	 * page for the category
	 *
	 * @var string
	 */
	public $filename;

	/**
	 * The class name that the display or update functions appear in.
	 *
	 * @var string
	 */
	public $classname;

	/**
	 * The image, used on the admin site map and in title at the top when displaying page.
	 *
	 * @var string
	 */
	public $image;

	/**
	 * Flag to represent that the "image" property is the name of a font-awesome glyph instead of an image file
	 * @var boolean
	 */
	public $image_fa = false;

	/**
	 * An array of indexes for children categories of this category.
	 *
	 * @var array
	 */
	public $children_categories=array();

	/**
	 * An array of indexes for children pages of this category.
	 *
	 * @var array
	 */
	public $children_pages=array();

	/**
	 * The top level category index for this category, usually 0, 1, or 2, but
	 * the top level categories are dynamic, so in theory another category
	 * could be created, to display a menu that is not part of the main menu.
	 *
	 * @var string
	 */
	public $head;

	/**
	 * Add a new menu category.  To have the menu category show up in the main menu, it must be a sub_category of 0, although it can be
	 * burried several layers.
	 *
	 * @param String index The index of the menu category.  If a filename is specified, the index is used to access the category
	 *  in the URL and is also used as part of the function name that is called to display the category, it is assumed there is
	 *  a class function named display_$index in the class specified by classname.
	 * @param String parent The index for the parent category.  Set to 0 (number, not string) to have it as a main menu category.
	 * @param String title The text shown on the menu.  In the future, this might also be automatically used to generate
	 *  the head text, if the category has a category page.
	 * @param String image this is the image filename to use on the main admin page, and in the head for the admin category page
	 *  if this menu category has its own page.  The filename should be relative to the admin_images folder.
	 * @param String filename the filename to include if the category has its own page (for instance, a page to show all the sub-pages
	 *  in the category).  Set to an empty string if this category has no main category page.
	 * @param String classname the class name to instantiate for this page, set to empty string if this category has no main category page.
	 * @param String The head category for this group of menu categories.  Allows us to have different menus!  Leave this as 0 to make
	 *  the head the normal category.
	 * @return menu_category
	 */
	public static function addMenuCategory($index, $parent, $title, $image, $filename, $classname, $head = 0){
		//if the parent is numerical, assume the parent is the head.
		if (is_numeric($parent)){
			$head=$parent;
		}
		$menu_cat = menu_category::getMenuCategory($index);
		$menu_cat->parent = $parent;
		$menu_cat->title = $title;
		$menu_cat->image = (strlen($image)>1)? $image: 'admin_images/menu_bullet.gif'; //no default for categories!
		if(stripos($menu_cat->image, "fa-") !== false) {
			//mark this as a font-awesome glyph instead of an image filename
			$menu_cat->image_fa = true;
		}
		$menu_cat->filename = $filename;
		$menu_cat->classname = $classname;
		$menu_cat->head = $head;
		$parent_cat = menu_category::getMenuCategory($parent);
		$parent_cat->children_categories[]=$menu_cat->index;
		return $menu_cat;
	}
	private static $registry = array();

	/**
	 * Gets an instance of a category, or false if failed or does not exist.
	 *
	 * @param string $index
	 * @param boolean $create_new if false, will not create a new category
	 *  if the category could not be found.
	 * @return menu_category
	 */
	public static function getMenuCategory($index, $create_new = true){

		if (!isset(self::$registry[$index])&&$create_new){
			self::$registry[$index] = new menu_category();
			self::$registry[$index]->index = $index;
		} else if (!$create_new && !isset(self::$registry[$index])){
			return false;
		}
		return self::$registry[$index];
	}

	/**
	 * Used to get an instance of each top level category, this depends on the
	 * $head var being set properly for categories.
	 *
	 * @return array Array of indexes for head categories
	 */
	public static function getAllParentCats(){
		$parent_cats = array();
		$indexes = array_keys(self::$registry);
		foreach ($indexes as $index){
			if (!in_array(self::$registry[$index]->head,$parent_cats)){
				$parent_cats[] = self::$registry[$index]->head;
			}
		}
		return $parent_cats;
	}
}

/**
 * Object for an admin page.
 *
 * @package Admin
 */
class menu_page {
	/**
	 * Index of page, this is used in the URL to access the page.
	 *
	 * @var string
	 */
	public $index;
	/**
	 * Index of parent to this page, can be a category or another page.
	 *
	 * @var string
	 */
	public $parent;
	/**
	 * Is this the page that is currently being loaded?
	 *
	 * @var boolean
	 */
	public $current;
	/**
	 * The title, as displayed in menu and in breadcrumb for page.
	 *
	 * @var string
	 */
	public $title;
	/**
	 * Generated automatically when page is loaded, this is a breadcrumb
	 * that is linked to pages.
	 *
	 * @var string
	 */
	public $breadcrumb_title;

	/**
	 * The end part of a wiki link to info for this page.
	 * @var string
	 */
	public $wiki_uri;

	/**
	 * The image.  This is used when loading the page.
	 *
	 * @param string
	 */
	public $image;

	/**
	 * Flag to represent that the "image" property is the name of a font-awesome glyph instead of an image file
	 * @var boolean
	 */
	public $image_fa = false;

	/**
	 * Filename that the display and update functions are in to load this page.
	 *
	 * @var string
	 */

	public $filename;
	/**
	 * Class name that the member functions are in to display and update.
	 *
	 * @var string
	 */
	public $classname;
	/**
	 * Is this a main page or sub page?
	 *
	 * @var string either main_page or sub_page
	 */
	public $type;
	/**
	 * Array of indexes for any children pages, like sub pages.
	 *
	 * @var array
	 */
	public $children_pages = array();
	/**
	 * Add a page to a category, or as a sub page of another page.  If you want to have a page show up in multiple categories,
	 * just run addPage again with the second categorie's index as the parent.  All instances of the attached page will have
	 * its title, image, filename, and classname overwritten by the last addPage that is run for that index.  Attaching a page
	 * multiple times as different types will produce un-desired results, so don't do it.
	 *
	 * If you do not want a page to show up in any auto-generated menus, just attach it to a dummy category.  It will be able to be accessed
	 * by the url manually, but will not show up under any categories on the menu.
	 *
	 * If you want a page to not show up in the auto generated sub menu, but you do want the page's parent to be highlighted
	 * when visiting the sub page, you can add the page as a sub_page of another main_page. When creating the link, be sure to add
	 * the value for the parent page's mc (menu_category) in the url or the parent page will not get highlighted.
	 *
	 * @param String $index The index of the page.  The index is used to access the page in the URL and is also used as part
	 *  of the function name that is called to display the page, it is assumed there is a class function named display_$index
	 *  in the class specified by classname.
	 * @param String $parent If this page is shown on the main menu, the parent should be the index for the category to attach
	 *  this page to.  If this page is a sub-page of another, and does not show on the main menu, the parent is the index of
	 *  the parent page that appears in the menu.
	 * @param String $title The text shown on the main menu.  In the future, this might also be automatically used to generate
	 *  the head text.
	 * @param String $image this is the image filename to use in the header.  the filename should be relative to the admin_images
	 *  folder.
	 * @param String $filename the filename to include in order to show this page.
	 * @param String $classname the class name to instantiate for this page.
	 * @param String(optional) $type default is 'main_page', this only needs to be changed if this page does not show on the main menu.  If this
	 *  page is a sub-page of another page, set type = sub_page, and set parent = the index of the parent page.  You can have sub-pages of
	 *  sub pages, but the buried sub pages will not make the main page get highlighted in the menu.  You can also use main_page_nosave, which
	 *  makes it so that when this page is visited, it will not be saved as the last page viewed.
	 * @param Boolean(optional) $replace_existing if true, will replace an existing addon't location instead of creating
	 *  a duplicate entry.  Note that the other vars still need to be set correctly.
	 * @return menu_page
	 */
	public static function addPage ($index, $parent, $title, $image, $filename, $classname, $type = 'main_page', $replace_existing=false){
		$page = menu_page::getPage($index);
		if ($page === false){
			return false;
		}
		$page->parent = $parent;
		$page->title = $title;
		$page->image = ($image=='') ? 'fa-question' : "admin_images/$image";
		if(!strlen($image)) {
			//if image is left blank, set it to a default one
			$page->image = $image = 'fa-question';
		}
		if(stripos($page->image, "fa-") !== false) {
			//mark this as a font-awesome glyph instead of an image filename
			$page->image_fa = true;
		} else {
			//old-school image. needs admin_images/ in front
			$page->image = "admin_images/$image";
		}
		$page->filename = $filename;
		$page->classname = $classname;
		$page->type = $type;
		if (!$replace_existing){
			$parent_obj = menu_category::getMenuCategory($parent,false);
			if (!$parent_obj){
				$parent_obj = menu_page::getPage($parent,false);
			}
			$parent_obj->children_pages[]=$index;
		}
		return $page;
	}
	/**
	 * Add an addon configuration page to a category, or as a sub page of another page.  If you want to have a page show up in multiple categories,
	 * just run addonAddPage again with the second categorie's index as the parent.  All instances of the attached page will have
	 * its title, image, filename, and classname overwritten by the last addPage that is run for that index.  Attaching a page
	 * multiple times as different types will produce un-desired results, so don't do it.
	 *
	 * If you do not want a page to show up in any auto-generated menus, just attach it to a dummy category.  It will be able to be accessed
	 * by the url manually, but will not show up under any categories on the menu.
	 *
	 * If you want a page to not show up in the auto generated sub menu, but you do want the page's parent to be highlighted
	 * when visiting the sub page, you can add the page as a sub_page of another main_page. When creating the link, be sure to add
	 * the value for the parent page's mc (menu_category) in the url or the parent page will not get highlighted.
	 *
	 * @param string $index The index of the page.  The index is used to access the page in the URL and is also used as part
	 *  of the function name that is called to display the page, it is assumed there is a class function named display_$index
	 *  in the class addon_addon_name_admin.  Be sure to name index something unique (maybe prepend with addon name) to avoid
	 *  name conflicts with other addons or built in pages.
	 * @param string $parent If this page is shown on the main menu, the parent should be the index for the category to attach
	 *  this page to.  If this page is a sub-page of another, and does not show on the main menu, the parent is the index of
	 *  the parent page that appears in the menu.
	 * @param string $title The text shown on the main menu.  In the future, this might also be automatically used to generate
	 *  the head text.
	 * @param string $addon_name name of addon, should be same as addon folder.
	 * @param string $image this is the image filename to use in the header.  the filename should be relative to the addon
	 *  folder.
	 * @param string $type default is 'main_page', this only needs to be changed if this page does not show on the main menu.  If this
	 *  page is a sub-page of another page, set type = sub_page, and set parent = the index of the parent page.  You can have sub-pages of
	 *  sub pages, but the buried sub pages will not make the main page get highlighted in the menu. You can also use main_page_nosave, which
	 *  makes it so that when this page is visited, it will not be saved as the last page viewed.
	 * @param bool $replace_existing if true, will replace an existing addon't location instead of creating
	 *  a duplicate entry.  Note that the other vars still need to be set correctly.
	 * @return menu_page
	 */
	public static function addonAddPage($index, $parent, $title, $addon_name, $image='', $type='main_page', $replace_existing=false){
		$page = menu_page::getPage($index);
		if ($page === false){
			return false;
		}
		$page->parent = $parent;
		$page->title = $title;

		if(stripos($image, "fa-") === false) {
			//for addons, if a fontawesome glyph isn't set in place of the old icons, go ahead and force one so that it looks okay in the new design
			$image = 'fa-plug';
		}
		$page->image_fa = true;
		$page->image = $image;

		//addon admin.php is already included at this point.
		$page->filename = 'addon';
		$page->classname = "addon_{$addon_name}_admin";
		$page->type = $type;
		if (!$replace_existing){
			if ($type == 'main_page' || $type == 'main_page_nosave'){
				//this is a main page.
				$menu_category = menu_category::getMenuCategory($parent);
				$menu_category->children_pages[]=$index;

				//also add a copy to the addon management folder, create a new category for it..

				if ($parent != 'addon_cat_'.$addon_name){
					$addon_cat = menu_category::getMenuCategory('addon_cat_'.$addon_name);
					$addon_info = Singleton::getInstance('addon_'.$addon_name.'_info');
					$addon_cat->title = $addon_info->title;
					$addon_cat->parent = 'addon_management';
					//$addon_cat->image = 'admin_images/menu_bullet.gif';
					$main_cat = menu_category::getMenuCategory('addon_management');
					if (!in_array($addon_cat->index, $main_cat->children_categories)){
						//add category to addon management category.
						$main_cat->children_categories[] = $addon_cat->index;
					}
					if (!in_array($index, $addon_cat->children_pages)){
						//add page to addon cat.
						$addon_cat->children_pages[] = $index;
						if (strlen($page->parent) == 0){
							//if parent not specified, set it to parent of addon
							$page->parent = $addon_cat->index;
						}
					}
				}
			} else {
				//this is a sub page of another page, so attach it to that page, not a category.
				$main_page = menu_page::getPage($parent);
				$main_page->children_pages[]=$index;
			}
		}
		return $page;
	}
	private static $registry = array();
	/**
	 * Gets an instance of the given page, or false if page doesn't exist and
	 * $create_new is set to false.
	 *
	 * @param string $index
	 * @param boolean $create_new
	 * @return menu_page
	 */
	public static function getPage ($index, $create_new = true){
		if ($create_new && !isset(self::$registry[$index])){
			$addon = geoAddon::getInstance();
			if (geoAddon::triggerDisplay('auth_admin_display_page', $index, geoAddon::NOT_NULL) !== false){
				self::$registry[$index] = new menu_page();
				self::$registry[$index]->index = $index;
			}
		}
		if (!isset(self::$registry[$index])){
			return false;
		}
		return self::$registry[$index];
	}
}
