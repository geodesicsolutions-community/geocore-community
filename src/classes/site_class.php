<?php
//site_class.php
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
## ##    16.09.0-59-g1cb1d15
## 
##################################


class geoSite
{
	/**
	 * The DataAccessor object
	 *
	 * @var DataAccess
	 */
	public $db;
	//tables within the database
	var $classifieds_table = "geodesic_classifieds";
	var $classifieds_expired_table = "geodesic_classifieds_expired";
	var $classified_sell_questions_table = "geodesic_classifieds_sell_questions";
	var $classified_extra_table = "geodesic_classifieds_ads_extra";
	var $categories_table = "geodesic_categories";
	var $categories_languages_table = "geodesic_categories_languages";
	var $logins_table = "geodesic_logins";
	var $configuration_table = "geodesic_configuration";
	var $sell_choices_table = "geodesic_classifieds_sell_question_choices";
	var $sell_choices_types_table = "geodesic_classifieds_sell_question_types";
	var $questions_table = "geodesic_classifieds_sell_questions";
	var $states_table = "geodesic_states";
	var $countries_table = "geodesic_countries";
	var $text_message_table = "geodesic_text_messages";
	var $text_languages_table = "geodesic_text_languages";
	var $text_languages_messages_table = "geodesic_text_languages_messages";
	var $text_page_table = "geodesic_text_pages";
	var $text_subpages_table = "geodesic_text_subpages";
	var $confirm_table = "geodesic_confirm";
	var $confirm_email_table = "geodesic_confirm_email";
	var $userdata_table = "geodesic_userdata";
	var $badwords_table = "geodesic_text_badwords";
	var $ad_configuration_table = "geodesic_classifieds_ad_configuration";
	var $userdata_history_table = "geodesic_userdata_history";
	var $html_allowed_table = "geodesic_html_allowed";
	var $ad_filter_table = "geodesic_ad_filter";
	var $ad_filter_categories_table = "geodesic_ad_filter_categories";
	var $user_communications_table = "geodesic_user_communications";
	var $site_configuration_table = "geodesic_classifieds_configuration";

	//no longer exists!
	//var $site_auction_configuration_table = "geodesic_auctions_configuration";
	var $choices_table = "geodesic_choices";
	var $images_urls_table = "geodesic_classifieds_images_urls";
	var $favorites_table = "geodesic_favorites";
	var $file_types_table = "geodesic_file_types";
	var $groups_table = "geodesic_groups";
	var $group_questions_table = "geodesic_classifieds_group_questions";
	var $price_plans_table = "geodesic_classifieds_price_plans";
	var $price_plans_categories_table = "geodesic_classifieds_price_plans_categories";
	var $price_plans_increments_table = "geodesic_classifieds_price_increments";
	var $user_groups_price_plans_table = "geodesic_user_groups_price_plans";
	var $expirations_table = "geodesic_classifieds_expirations";
	var $credit_choices = "geodesic_classifieds_credit_choices";
	var $user_subscriptions_table = "geodesic_classifieds_user_subscriptions";
	var $subscription_choices = "geodesic_classifieds_subscription_choices";
	var $font_page_table = "geodesic_font_pages";
	var $font_sub_page_table = "geodesic_font_subpages";
	var $font_element_table = "geodesic_font_elements";
	var $paypal_transaction_table = "geodesic_paypal_transactions";
	var $cc_choices = "geodesic_credit_card_choices";
	var $sell_questions_table = "geodesic_classifieds_sell_session_questions";
	var $registration_table = "geodesic_registration_session";
	var $currency_types_table = "geodesic_currency_types";
	var $worldpay_configuration_table = "geodesic_worldpay_settings";
	var $worldpay_transaction_table = "geodesic_worldpay_transactions";
	var $registration_configuration_table = "geodesic_registration_configuration";
	var $registration_choices_table = "geodesic_registration_question_choices";
	var $registration_choices_types_table = "geodesic_registration_question_types";
	var $price_plan_lengths_table = "geodesic_price_plan_ad_lengths";
	var $subscription_holds_table = "geodesic_classifieds_user_subscriptions_holds";
	var $voting_table = "geodesic_classifieds_votes";
	var $attached_price_plans = "geodesic_group_attached_price_plans";
	var $balance_transactions = "geodesic_balance_transactions";
	var $balance_transactions_items = "geodesic_balance_transactions_items";
	var $invoices_table = "geodesic_invoices";
	var $nochex_transaction_table = "geodesic_nochex_transactions";
	var $nochex_settings_table = "geodesic_nochex";
	var $auction_payment_types_table = "geodesic_payment_types";
    var $auctions_expired_table = "geodesic_auctions_expired";

	var $email_queue_table = "geodesic_email_queue";

	var $site_settings_table = "geodesic_site_settings";

    var $subscription_renewal = 0;
    var $account_balance = 0;

	var $pages_table = "geodesic_pages";
	var $pages_sections_table = "geodesic_pages_sections";
	var $pages_text_table = "geodesic_pages_messages";
	var $pages_text_languages_table = "geodesic_pages_messages_languages";
	var $pages_languages_table = "geodesic_pages_languages";
	var $block_email_domains = "geodesic_email_domains";

	var $final_fee_table = "geodesic_auctions_final_fee_price_increments";
	var $bid_table = "geodesic_auctions_bids";
	var $autobid_table = "geodesic_auctions_autobids";
	
	var $auctions_feedbacks_table = "geodesic_auctions_feedbacks";
	var $auctions_feedback_icons_table = "geodesic_auctions_feedback_icons";
	var $blacklist_table = "geodesic_auctions_blacklisted_users";
	var $invitedlist_table = "geodesic_auctions_invited_users";

	var $very_large_font_tag;
	var $large_font_tag;
	var $medium_error_font_tag;
	var $medium_font_tag;
	var $small_font_tag;
	var $row_color_black = "#000000";
	var $row_color1;
	var $row_color2;
	var $menu_bar_font_tag;

	var $background_color_light = "#eeeeee";
	var $background_color_dark = "#dddddd";

	var $data_missing_error_message = "Your request could not be completed: missing data";
	var $internal_error_message = "There was an internal error";
	var $data_error_message = "Not enough data to complete request";
	var $page_text_error_message = "No text connected to this page";
	var $no_pages_message = "No pages to list";
	var $basic_error_message = "There has been a error processing your request.<br />Please try again.";

	var $error_message;

	var $debug = 0;
	var $debug_affiliate = 0;

	var $site_error_message;
	var $sql_query;
	var $row_count;
	var $configuration_data;
	var $ad_configuration_data;
	var $category_configuration;
	var $field_configuration_data;
	var $userid, $classified_user_id;
	var $stage;
	var $language_id;
	var $classified_variables;
	var $site_category = 0;
	var $page_result = 1;
	var $page_id;
	var $module_id;
	var $body;
	var $module_body;
	var $font_stuff;
	var $template;
	var $product;
	var $count_images;
	var $images_captured;
	var $images_error;
	var $first_image_filled = 0;

	var $messages = array();
	var $category_tree_array = array();
	var $category_dropdown_name_array = array();
	var $category_dropdown_id_array = array();
	var $category_dropdown_settings_array = array();
	var $subcategory_array = array();
	var $images_to_display = array();
	var $image_file_types_icon = array();
	var $image_file_types_extension = array();
	var $image_counter = 0;

	var $category_questions = array();
	var $category_explanation = array();
	var $category_choices = array();
	var $category_other_box = array();
	var $category_display_order = array();
	var $category_dropdown_array = array();

	var $image_file_types = array();

	var $site_name;

	var $message_category;
	var $multiple_languages;

	var $affiliate_id = 0;
	var $affiliate_page_type = 0;
	var $affiliate_group_id = 0;

	var $filter_id = 0;
	var $state_filter = "";
	var $zip_filter = "";
	var $zip_filter_distance = "";
	var $postal_code_table = "geodesic_zip_codes";

	var $uk_postcodes = array();
	/**
	 * 
	 *
	 * @var geoPC
	 */
	var $product_configuration = 0;
	var $auction_configuration_data;
	var $popup_image_debug = 0;

	var $sell_type = 0;
	var $debug_detail_check = 0;
	var $debug_sell = 0;
	var $debug_email = "";
	var $withAjax = false;

	var $head_font_stuff;
	protected static $head_stuff;
	
	var $onload_cat_id = 0;

	var $body_tag_attributes;

	var $mtime;
	var $starttime;
	var $last_time;

	var $html_disallowed_string;

	var $addon_name = "";
	var $using_addon = 0;
	var $using_extra = 0;
	var $this_module;
	
	public function __construct ()
	{
		//throw new Exception ('Called.');
		trigger_error('DEBUG STATS: New SITE CLASS!!!');
		$this->db = DataAccess::getInstance();
		$this->setLanguage();
		$this->userid = $this->classified_user_id = geoSession::getInstance()->getUserID();
		trigger_error( 'DEBUG SITE_CLASS: About to get_configuration_data()');
		//get configuration data
		$this->get_configuration_data();
		trigger_error('DEBUG SITE_CLASS: Finished get_configuration_data()');
		$this->product_configuration = geoPC::getInstance();
		if (isset($this->classified_id) && $this->classified_id) {
			//set seller username if on page with classified id set.
			$listing = geoListing::getListing($this->classified_id);
			if ($listing) {
				geoView::getInstance()->seller_username = geoUser::userName($listing->seller);
			}
		}
	} //end of function Site
	
	public function setLanguage($language_id=null)
	{
		if(!$language_id) $language_id = $this->db->getLanguage();
		if ($language_id) {
			$db = DataAccess::getInstance();
			$sql= "SELECT * FROM ".$this->pages_languages_table." WHERE language_id=?";
			$r = $db->getrow($sql,array($language_id));
			if ($r===false) return false;
			 $this->language_id  = (!empty($r))? $language_id:1;
		} else {
			$this->language_id = 1;
		}
	}
	
	public function display_page ($preview_mode = false)
	{
		$view = geoView::getInstance();
		
		if ($view->bypass_display_page) {
			//bypass display page - used by system to combine steps together
			return;
		}
		
		if (is_object($preview_mode)) {
			//fix for anywhere that still passes in db object
			$preview_mode = false;
		}
		if (defined('GEO_PAGE_DISPLAYED')) {
			//un-comment next line to track down why a particular page is displaying twice
			//throw new Exception ('Displayed!');
		}
		define('GEO_PAGE_DISPLAYED',1);
		
		$jsLibs=$jsLibsNoCombine=array();
		
		//jquery needs to be loaded first...
		$jsLibs[] = geoView::JS_LIB_JQUERY;
		$jsLibs[] = geoView::JS_LIB_JQUERY_UI;
		//Temporary?  Until touch events supported by jquery-ui
		$jsLibs[] = 'js/jquery.ui.touch-punch.min.js';
		
		//NOTE: modernizr needs to load in very specific order in the head so
		//is not auto-added by {head_html} like other JS libraries
		
		//need prototype for tinymce and lightbox, and need it to be loaded BEFORE tinymce
		//and AFTER jquery...  Note that these 2 will eventually be replaced by jQuery
		//$jsLibs[]=geoView::JS_LIB_PROTOTYPE;
		//$jsLibsNoCombine[]=geoView::JS_LIB_SCRIPTACULOUS;
		
		if ($view->editor && ($view->forceEditor || $this->db->get_site_setting('use_rte'))) {
			$tpl = new geoTemplate('system','tinymce');
			$tpl_vars = array();
			$tpl_vars['use_gzip'] = $gzip = ($this->db->get_site_setting('use_wysiwyg_compression'))? 1: 0;
			$tpl_vars['blank_screen_fix'] = $this->db->get_site_setting('wysiwyg_blank_screen_fix');
			$tpl_vars['width'] = intval($this->db->get_site_setting('desc_wysiwyg_width'));
			$tpl_vars['height'] = intval($this->db->get_site_setting('desc_wysiwyg_height'));
			
			//load tinymce js
			$jsLibsNoCombine[] = "//cdn.tinymce.com/4/tinymce.min.js";
			
			if ($this->inAdminCart) {
				$tpl_vars['inAdmin'] = true;
			}
			
			$tpl->assign($tpl_vars);
			$view->addTop($tpl->fetch('index'));
		}
		
		if ($this->inAdminCart) {
			//in admin cart, don't actually display...
			$view->addBody($this->body);
			return;
		}
		
		//for addons to do things before the page is displayed, stuff
		//like add stuff to the head of the document
		geoAddon::triggerUpdate('notify_display_page',array('this'=>$this,'preview_mode'=>$preview_mode));
		
		trigger_error( "DEBUG SITE_CLASS: TOP OF DISPLAY_PAGE");
		//if (($this->page_id < 135) || ($this->page_id > 154))
		$addon = geoAddon::getInstance();
		if (!is_object($this->db)) $this->db = DataAccess::getInstance();
		
		$language_id = $this->db->getLanguage();
		
		if ($this->db->get_site_setting("site_on_off") && !geoUtil::isAllowedIp()) {
			$userid = geoSession::getInstance()->getUserId();
			if ($userid != 1) {
				ob_clean();
				header("Location: ".$this->db->get_site_setting("disable_site_url"));
				require GEO_BASE_DIR . 'app_bottom.php';
				exit;
			}
		}
		if (!is_numeric($this->page_id)){
			$this->using_addon = 1;
		}
		$this->head_font_stuff .= self::$head_stuff;
	
		//let templates know what category they are in
		$view->setCategory((isset($this->site_category))? $this->site_category: 0);
		$view->setLanguage($this->db->getLanguage());
		
		$view->setPage($this);
		$view->loadModules($this->page_id, $this->using_extra);
		
		if ($view->isAffiliatePage) {
			$affiliate_id = (int)$view->affiliate_id;
			if ($affiliate_id) {
				//populate affiliate_info
				$info = $this->db->GetOne("SELECT `affiliate_html` FROM ".geoTables::userdata_table
					." WHERE `id`=?", array($affiliate_id));
				$info = trim($info);
				if ($info) {
					$view->affiliate_info = $info;
				}
				//TODO: see if there is user group settings for this page, and if so, use those
				
			}
		}
		
		//Add the non-combined as well
		$view->addJScript($jsLibsNoCombine, 'prepend', false, false, false);
		
		//Add these to the top
		$view->addJScript($jsLibs, 'prepend');
		
		//bootstrap.js
		$view->addCssFile(geoTemplate::getUrl('css',"bootstrap.css"))->addJScript(geoTemplate::getUrl('js',"bootstrap.min.js"));
		
		//css for jquery UI
		$view->addCssFile(geoView::CSS_LIB_JQUERY_UI);
		
		//css for the overall page
		$view->addCssFile(geoTemplate::getUrl('css/page',$this->page_id.'.css', true));
		
		//Load the NEW gjmain.js that has the jquery JS in it
		$view->addJScript(geoTemplate::getUrl('js','gjmain.js'));
		//load all the plugins
		$plugins = array ('utility','simpleCarousel','lightbox','imageFade','progress','tabs','jQueryRotate');
		foreach ($plugins as $plugin) {
			$view->addJScript(geoTemplate::getURL('js',"plugins/{$plugin}.js"));
		}
		if (trim($this->head_font_stuff)) {
			$view->addTop($this->head_font_stuff);
		}
		$extra = '';
		if (!$this->db->get_site_setting('cron_disable_heartbeat')) {
			//run the heartbeat so "turn it on" in the JS
			$extra .= "gjUtil.runHeartbeat = true;";
		}
		//NOTE: semi-minimal to preserve bandwidth.. This inits normal jquery init,
		//then init once window is loaded (once images are done loading),
		//and finally initializes the old-school prototype stuff.  That last one
		//will be removed once all prototype based JS is converted to jQuery
		$initJs = "
<script>
//<![CDATA[
jQuery(function () { $extra gjUtil.ready(); });
jQuery(window).load(gjUtil.load);
//]]>
</script>";
		$view->addTop($initJs);
		if ($preview_mode === "preview_only"){
			$view->preview_mode = 'preview_only';
		}
		//add stuff for the category.
		if ($this->site_category) {
			$view->addTop(geoCategory::getHeaderHtml($this->site_category,$this->page_id));
		}
		
		if ($this->body) {
			$view->addBody($this->body);
		}
		
		return $view->render($this->page_id);
	} //end of function display_page

	function get_text($current_page_id=0)
	{
		$page_id = ($current_page_id)? $current_page_id: $this->page_id;
		$this->messages = $this->db->get_text(true,$page_id);
		return $this->messages;
	}
	
	public $fields;
	
	/**
	 * USed to get config data.  Don't use this, use get_site_setting() instead.
	 *
	 * @deprecated Going away once all places that use this are converted to use get_site_setting
	 *  and all teh settings in that old table have been moved to be saved in site settings using
	 *  the upgrade.
	 */
	function get_configuration_data()
	{
		$catId = (int)$this->site_category;
		//gotta get the group
		$groupId = 0;
		if ($this->userid) {
			$user = geoUser::getUser($this->userid);
			if ($user) {
				$groupId = (int)$user->group_id;
			}
		}
		trigger_error('DEBUG STATS:  Before get fields instance');
		$this->fields = geoFields::getInstance($groupId, $catId);
		trigger_error('DEBUG STATS: After get fields instance');
		
		if (isset($this->configuration_data)) return true;
	 	$this->configuration_data = $this->db->get_site_settings(true);
	 	return true;
	}

	function site_error()
	{
		$this->page_id = 59;
		$this->get_text();
		//check to see if debugging
		$this->body ="<table cellpadding=\"3\" cellspacing=\"1\" border=\"0\" style=\"width:100%;\">\n";
		$this->body .="<tr>\n\t<td class=\"site_error_page_title\">\n\t".$this->messages[908]." \n\t</td>\n</tr>\n";
		$this->body .="<tr>\n\t<td class=\"site_error_page_description\">\n\t".$this->messages[859]." \n\t</td>\n</tr>\n";
		$this->body .="<tr>\n\t<td class=\"site_error_page_description\">\n\t".$this->site_error_message." \n\t</td>\n</tr>\n";
		$this->body .="</table>\n";
		$this->display_page();
		require GEO_BASE_DIR . 'app_bottom.php';
		exit;

	} //end of function site_error

	function get_row_color($special=0)
	{
		if (($this->row_count % 2) == 0) {
			switch ($page_id) {
				case 2:
				//search page results
					if ($special)
						return "main_result_table_body_even_bold";
					else
						return "main_result_table_body_even";
					break;

				case 3:
				//search page results
					if ($special)
						return "browsing_result_table_body_even_bold";
					else
						return "browsing_result_table_body_even";
					break;
			}
		} else {
			switch ($page_id) {
				case 2:
				//search page results
					if ($special)
						return "main_result_table_body_odd_bold";
					else
						return "main_result_table_body_odd";
					break;

				case 3:
				//search page results
					if ($special)
						return "browsing_result_table_body_odd_bold";
					else
						return "browsing_result_table_body_edd";
					break;
			}
		}
		return $row_color;
	} //end of function get_row_color

//##################################################################################


	function get_category_dropdown($name,$category_id=0,$no_main=0,$css_control=0,$all_cat_text='',$return_type=1, $max_depth=-1, $id=false)
	{
		$all_cat_text = (strlen($all_cat_text)>0) ? $all_cat_text : "All Categories";
		$content = "";

		if (!in_array( $name, $this->category_dropdown_settings_array) ||
			!in_array( $max_depth, $this->category_dropdown_settings_array) ||
			!in_array( $no_main, $this->category_dropdown_settings_array) )
		{
			// Empty the arrays if it is new values
			$this->category_dropdown_settings_array = array_slice($this->category_dropdown_settings_array,0,0);
			$this->category_dropdown_name_array = array_slice($this->category_dropdown_name_array,0,0);
			$this->category_dropdown_id_array = array_slice($this->category_dropdown_name_array,0,0);
		}

		if (empty($this->category_dropdown_settings_array))
		{
			// Add settings if array is empty
			array_push($this->category_dropdown_settings_array, $name);
			array_push($this->category_dropdown_settings_array, $no_main);
			array_push($this->category_dropdown_settings_array, $max_depth);
		}


		//echo count($this->category_dropdown_id_array)." is the count of category_dropdown_id_array<br />\n";
		if (!$no_main)
		{
			if (!in_array(0,$this->category_dropdown_id_array) )
			{
				array_push($this->category_dropdown_name_array, $all_cat_text);
				array_push($this->category_dropdown_id_array,0);
			}
		}

		//echo count($this->category_dropdown_id_array)." is the count of category_dropdown_id_array<br />\n";

		if ((count($this->category_dropdown_id_array) == 0) || (count($this->category_dropdown_id_array) == 1))
		{
			//echo "building categories array<br />\n";
			$this->get_all_subcategories_for_dropdown(0,0,$max_depth);
		}
		else
		{
			//echo "resetting categories array<br />\n";
			reset($this->category_dropdown_name_array);
			reset($this->category_dropdown_id_array);
		}

		$tpl = new geoTemplate('system', 'classes');
		$tpl->assign('name', $name);
		$tpl->assign('css', $css_control);
		$tpl->assign('id', $id);
		$options = array();
		foreach($this->category_dropdown_name_array as $key => $value)
		{
			$options[$key]['value'] = $this->category_dropdown_id_array[$key];
			$options[$key]['label'] = geoString::fromDB($value);
			if ($this->category_dropdown_id_array[$key] == $category_id) {
				$options[$key]['selected'] = true;
			}
		}
		$tpl->assign('options', $options);
		$content = $tpl->fetch('Category/category_dropdown.tpl');
		if ($return_type == 2) {
			return $content;
		} elseif ($return_type == 3) {
			return $options;
		} else {
			$this->body .= $content;
			return true;
		}
	} //end of function get_category_dropdown

	/**
	 * Gets all the categories in one swoop, instead of recursively getting each level of categories.
	 */
	function get_all_subcategories_for_dropdown($category_id = 0, $get_all=0,$max_depth=-1){
		trigger_error('DEBUG STATS ADMIN_SITE_CLASS: Top of get_all_subcategories_for_dropdown');
		$restrictParent = '';
		$numLevels = (defined('IN_ADMIN')?$this->db->get_site_setting('levels_of_categories_displayed_admin') : $this->db->get_site_setting('levels_of_categories_displayed'));
		if ((int)$numLevels) {
			$restrictParent = " AND ".geoTables::categories_table.".level <= ".(int)$numLevels.' ';
		}
		
		$this->sql_query = 'SELECT '.$this->categories_table.".category_id as category_id,
			".$this->categories_table.".parent_id as parent_id,".$this->categories_languages_table.".category_name as category_name
			FROM ".$this->categories_table.",".$this->categories_languages_table.
			" WHERE ".$this->categories_table.".category_id = ".$this->categories_languages_table.".category_id " .
			"AND ".$this->categories_languages_table.".language_id = ".$this->language_id." ".
			" AND ".$this->categories_table.".enabled = 'yes' ".
			$restrictParent.
			'ORDER BY '.$this->categories_table.'.parent_id, '.$this->categories_table.'.display_order, '.$this->categories_languages_table.".category_name";
		$results = $this->db->Execute($this->sql_query);
		if (!$results){
			trigger_error('ERROR SQL ADMIN_SITE_CLASS: Query: '.$this->sql_query.' Error: '.$this->db->ErrorMsg());
			return false;
		}
		trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After sql executed, before data gotten.');
		$categories = array();
		while ($row = $results->FetchRow()){
			$categories[$row['parent_id']][$row['category_id']]['category_name']=$row['category_name'];
			//$categories[$row['parent_id']][$row['category_id']]['category_id']=$row['category_id'];
		}
		trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After data gotten, Before dropdown array generated.');
		$this->add_sub_categories_for_dropdown($categories, $category_id, $get_all, $max_depth);
		trigger_error('DEBUG STATS ADMIN_SITE_CLASS: After dropdown array generated.');
	}
	
	function add_sub_categories_for_dropdown(&$show_category,$parent,$get_all,$max_depth=-1){
		$ids = array_keys($show_category[$parent]);
		foreach ($ids as $id){
			$pre_stage = "";
			for ($i=1;$i<=$this->stage;$i++)
			{
				$pre_stage .= "&nbsp;&nbsp;&nbsp;";
			}
			array_push($this->category_dropdown_name_array, $pre_stage.urldecode(stripslashes($show_category[$parent][$id]["category_name"])));
			array_push($this->category_dropdown_id_array,$id);
			if ($max_depth == -1)
			{
				if (isset($show_category[$id]) &&
					((($this->stage + 1) < $this->db->get_site_setting('levels_of_categories_displayed')) ||
						($this->db->get_site_setting('levels_of_categories_displayed') == 0 || $get_all)))
				{
					$this->stage++; $this->add_sub_categories_for_dropdown($show_category,$id,$get_all,$max_depth); $this->stage--;
				}
			}
			else
			{
				if (isset($show_category[$id]) && (($this->stage) < $max_depth ))
				{
					$this->stage++;
					$this->add_sub_categories_for_dropdown($show_category,$id,$get_all,$max_depth);
					$this->stage--;
				}
			}
		}
	}
//##################################################################################

	function get_subcategories_for_dropdown($db,$category_id=0,$get_all=0)
	{
		trigger_error('DEBUG SITE_CLASS: Top of get_subcategories_for_dropdown()');
		if ((($this->stage + 1) <= $this->db->get_site_setting('levels_of_categories_displayed'))
			|| ($this->db->get_site_setting('levels_of_categories_displayed') == 0))
		{
			$this->sql_query = "select ".$this->categories_table.".category_id as category_id,
				".$this->categories_table.".parent_id as parent_id,".$this->categories_languages_table.".category_name as category_name
				from ".$this->categories_table.",".$this->categories_languages_table."
				where ".$this->categories_table.".category_id = ".$this->categories_languages_table.".category_id
				and ".$this->categories_table.".parent_id = ".$category_id."
				AND ".$this->categories_table.".enabled = 'yes' 
				and ".$this->categories_languages_table.".language_id = ".$this->language_id." order by ".$this->categories_table.".display_order,".$this->categories_languages_table.".category_name";
			$category_result =  $this->db->Execute($this->sql_query);
			if (!$category_result)
			{
				//$this->body .=$this->sql_query." is the query<br />\n";
				$this->error_message = $this->messages[2052];
				return false;
			}
			elseif ($category_result->RecordCount() > 0)
			{
				$this->stage++;
				while ($show_category = $category_result->FetchNextObject())
				{
					$pre_stage = "";
					for ($i=1;$i<=$this->stage;$i++)
					{
						$pre_stage .= "&nbsp;&nbsp;&nbsp;";
					}
					if ($category_id != 0)
					{
						array_push($this->category_dropdown_name_array, $pre_stage.urldecode(stripslashes($show_category->CATEGORY_NAME)));
						array_push($this->category_dropdown_id_array,$show_category->CATEGORY_ID);
					}
					else
					{
						array_push($this->category_dropdown_name_array, urldecode(stripslashes($show_category->CATEGORY_NAME)));
						array_push($this->category_dropdown_id_array,$show_category->CATEGORY_ID);
					}
					$this->get_subcategories_for_dropdown($db,$show_category->CATEGORY_ID,$get_all);
				}
				$this->stage--;
			}
		}
		trigger_error('DEBUG SITE_CLASS: bottom of get_sub_categories_for_dropdown()');
		return;
	} //end of function get_subcategories_for_dropdown

//##################################################################################
	public $questions;
	public function get_questions($category, $group)
	{
		$group = (geoPC::is_ent())? intval($group): 0;
		$category = intval($category);
		if (!$group && !$category) {
			//can't get questions w/o category or group
			return false;
		}
		
		$where = array();
		while ($category != 0) {
			//get all the parent categories.
			$where[] = "`category_id` = $category";
			$sql = "SELECT `parent_id` FROM ".geoTables::categories_table." WHERE `category_id` = ?";
			$row = $this->db->GetRow($sql, array($category));
			$category = $row['parent_id'];
		}
		
		if ($group) {
			$where[] = "`group_id` = $group";
		}
		
		//get the questions for this group/category
		$sql = "SELECT * FROM ".$this->classified_sell_questions_table." WHERE ".implode(' OR ',$where)." ORDER BY `display_order`";
		$questions = $this->db->GetAll($sql);
		if ($questions === false) {
			$this->site_error($this->db->ErrorMsg());
			return false;
		}
		$this->questions = array();
		if (count($questions) == 0) {
			return;
		}
		foreach ($questions as $key => $row) {
			$sql = "SELECT * FROM ".geoTables::questions_languages." WHERE `question_id` = ? AND `language_id` = ?";
			$lang_row = $this->db->GetRow($sql,array($row['question_id'],$this->language_id));
			if ($lang_row) {
				$questions[$key] = array_merge($row, $lang_row);
			}
		}
		$this->questions = $questions;
	}
	
	function get_category_questions($db=0,$category_id=0)
	{
		//get sell questions specific to this category
		//echo $category_id." is category_id in get_category_questions<br />\n";
		
		while ($category_id != 0)
		{
			//get the questions for this category
			$this->sql_query = "SELECT * FROM ".$this->classified_sell_questions_table." WHERE category_id = ".$category_id." ORDER BY display_order";
			$result = $this->db->Execute($this->sql_query);
			if (!$result)
			{
				return false;
			}

			if ($result->RecordCount() > 0)
			{
				//$this->body .="hello from inside a positive results<br />\n";
				while ($get_questions = $result->FetchNextObject())
				{
					//get all the questions for this category and store them in the category_questions variable
					//also get the language specific name and explanation
					$this->sql_query = "SELECT * FROM `geodesic_classifieds_sell_questions_languages` WHERE question_id = ? and language_id = ?";
					$language_specific_result = $this->db->Execute($this->sql_query, array($get_questions->QUESTION_ID,$this->language_id));					
					if ((!$language_specific_result) || ($language_specific_result->RecordCount() != 1))
					{
						//set the default language text from the classified_sell_questions_table
						//as the upgrade may have failed or not been run
						$this->category_questions[$get_questions->QUESTION_ID] = $get_questions->NAME;
						$this->category_explanation[$get_questions->QUESTION_ID] = $get_questions->EXPLANATION;
						$this->category_choices[$get_questions->QUESTION_ID] = $get_questions->CHOICES;
					}
					else
					{
						$question_name_and_explanation = $language_specific_result->FetchRow();
						$this->category_questions[$get_questions->QUESTION_ID] = $question_name_and_explanation["name"];
						$this->category_explanation[$get_questions->QUESTION_ID] = $question_name_and_explanation["explanation"];
						$this->category_choices[$get_questions->QUESTION_ID] = $question_name_and_explanation["choices"];
					}
					
					$this->category_other_box[$get_questions->QUESTION_ID] = $get_questions->OTHER_INPUT;
					$this->category_display_order[$get_questions->QUESTION_ID] = $get_questions->DISPLAY_ORDER;
					$this->category_url_icon[$get_questions->QUESTION_ID] = (isset($get_questions->URL_ICON) ? $get_questions->URL_ICON : '');

					//$this->body .=$get_questions->CHOICES." is the choices for ".$get_questions->QUESTION_ID."<br />\n\t";
				} //end of while $get_questions = mysql_fetch_array($result)
			} //end of if ($result)

			//get this_cat_id parent category
			$this->sql_query = "SELECT parent_id FROM ".$this->categories_table." WHERE category_id = ".$category_id;
			//echo $this->sql_query."<br />\n";
			$result = $this->db->Execute($this->sql_query);
			//$this->body .=$this->sql_query." is the query<br />\n";
			if (!$result)
			{
				return false;
			}
			elseif ($result->RecordCount() == 1)
			{
				$show_category = $result->FetchNextObject();
				$category_id = $show_category->PARENT_ID;
			}
			else
			{
				//$this->body .=$this->sql_query." is the query where count is not 1<br />\n";
				return false;
			}

		} //end of if ($category_id != 0)

	} //end of function get_category_questions

//##################################################################################

	function get_ad_configuration($db=0,$by_array=0)
	{
		$this->sql_query = "select * from ".$this->ad_configuration_table;
		$result = $this->db->Execute($this->sql_query);
		if (!$result)
		{
			$this->error_message = $this->messages[57];
			return false;
		}
		elseif ($result->RecordCount() == 1)
		{
			if ($by_array == 0)
			{
				$this->ad_configuration_data = $result->FetchNextObject();
			}
			else
			{
				$this->ad_configuration_data = $result->FetchRow();
			}
			return true;
		}
		else
		{
			$this->html_disallowed_list = 0;
			return true;
		}

	} //function get_ad_configuration
	
//##################################################################################
	// two user_id params so that addons from older plantform are compatible.  althought it is not really necesary,
	/// it can be this way for now, just a prevention
	function get_user_data($user_id=0,$userid=0)
	{
		if(is_object($user_id)) {
			$user_id = $userid;
		}
		if (!$user_id && $this->userid) $user_id = $this->userid;
		if (!$user_id) return false;
		
		$db = DataAccess::getInstance();
		
		if ($db->get_site_setting('use_mambo')) {
			//get user data from mambo installation instead

		} else {
			$user_id = (int)$user_id;
			$this->sql_query = "select * from ".$this->userdata_table.",".$this->user_groups_price_plans_table." where
				".$this->userdata_table.".id = ".$this->user_groups_price_plans_table.".id and ".$this->userdata_table.".id = ".$user_id;
			$user_data_result = $db->Execute($this->sql_query);
			//echo $this->sql_query." is the get_user_data query<br />\n";
			if (!$user_data_result) {
				//$this->body .=$this->sql_query." is the state query<br />\n";
				//echo $db->ErrorMsg()." is the error in get_user_data<br />\n";
				//echo "bad get_user_data query<br />\n";
				trigger_error('ERROR SQL SITE_CLASS: Query: '.$this->sql_query.' Error: '.$this->db->ErrorMsg());
				return false;
			} elseif ($user_data_result->RecordCount() == 1) {
				$show_user = $user_data_result->FetchNextObject();
				
				$show_user->FULL_NAME = stripslashes($show_user->FIRSTNAME)." ".stripslashes($show_user->LASTNAME);
				
				$show_user->USERNAME_LABEL = 557;
				
				if($show_user->BUSINESS_TYPE ==1) {
					$show_user->BUSINESS_TYPE_LABEL = 1401;
				} else if($show_user->BUSINESS_TYPE ==2) {
					$show_user->BUSINESS_TYPE_LABEL = 558;
				} else {
					$show_user->BUSINESS_TYPE_LABEL = 1402;
				}
				//E-mail is not saved using geoString::toDB() so don't do following
				//if($show_user->EMAIL) $show_user->EMAIL = geoString::fromDB($show_user->EMAIL);
				//if($show_user->EMAIL2) $show_user->EMAIL2 = geoString::fromDB($show_user->EMAIL2);
				
				$show_user->EMAIL_LABEL = 559;
				$show_user->EMAIL2_LABEL = 1403;
				return $show_user;
			} else {
				$this->error_message = $this->data_error_message;
				trigger_error('ERROR SQL SITE_CLASS:There seems to be more than one record returned, or no records at all.');
				return false;
			}
			
			return true;
		}
		
	} //end of function get_user_data

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_classified_data($classified_id=0,$array=0)
	{
		if ($classified_id) {
			$db = DataAccess::getInstance();
			
			$this->sql_query = "select * from ".$this->classifieds_table." where id = ".$classified_id;
			$result = $db->Execute($this->sql_query);
			if (!$result){
				//$this->body .=$this->sql_query." is the query<br />\n";
				return false;
			} elseif ($result->RecordCount() != 1 ) {
				//more than one auction matches
				//$this->body .=$this->sql_query." is the query<br />\n";
				return false;
			}
			if($array) {
				$show = $result->FetchRow();
				return $show;
			} else {
				$show = $result->FetchNextObject();
				return $show;
			}
		} else {
			return false;
		}

	} //end of function get_classified_data

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function choose_language_form()
	{
		$db = DataAccess::getInstance();
		$sql = "SELECT * FROM $this->pages_languages_table WHERE `active` = ? ORDER BY `language` ASC";
		$result = $db->Execute($sql,array(1));
		if (!$result) {
			return false;
		}
			$this->page_id = 42;
			$this->get_text();
			
			$view = geoView::getInstance();
			
			//get current language. check set_language_cookie first to see if it was set on this pageload, otherise use the cookie
			$currentLanguage = ($_REQUEST['set_language_cookie']) ? $_REQUEST['set_language_cookie'] : $_COOKIE['language_id']; 
			
			$languages = array();
			while($lang = $result->FetchRow()) {
				$languages[] = array(
						'name' => geoString::fromDB($lang['language']), 
						'id' => $lang['language_id'], 
						'selected' => (($lang['language_id'] == $currentLanguage)?true:false)
				);
			}
			$view->languages = $languages;
			
			$view->setBodyTpl('choose_language_form.tpl','','other');
			$this->display_page();
			return true;
		
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_help_link($help_id=0,$type=0,$link_text=0,$question_id=0)
	{
		//make sure we have icons messages
		$this->messages = $this->db->get_text(true, 59);
		$pre = (defined('IN_ADMIN'))? '../' : '';
		if ($question_id)
		{
			$help_link =  "<a href=\"{$pre}show_help.php?a=1&amp;c=$question_id\" class='lightUpLink' onclick=\"return false;\"><img src=\"{$pre}".geoTemplate::getUrl('',$this->messages[500797])."\" alt=\"\" class='help_icon' /></a>";
		}
		elseif (($help_id) &&($link_text))
		{
			$help_link =  "<a href=\"{$pre}show_help.php?a=$help_id&amp;l={$this->language_id}\" class='lightUpLink' onclick=\"return false;\"><span class=\"medium_font\">".geoString::fromDB($this->messages[$link_text])."</span></a>";
		}
		elseif ($help_id)
		{
			//make sure text is set
			$txt_msgs = $this->db->get_text(true);
			if (isset($txt_msgs[$help_id]) && strlen(trim(geoString::fromDB($txt_msgs[$help_id]))) > 0){
				if ($type == 1)
				{
					$help_link =  "<a href=\"{$pre}show_help.php?a=$help_id&amp;b=1&amp;l={$this->language_id}\" class='lightUpLink' onclick=\"return false;\"><img src=\"{$pre}".geoTemplate::getUrl('',$this->messages[500797])."\" alt=\"\" class='help_icon' /></a>";
				}
				else
				{
					$help_link =  "<a href=\"{$pre}show_help.php?a=$help_id&amp;l={$this->language_id}\" class='lightUpLink' onclick=\"return false;\"><img src=\"{$pre}".geoTemplate::getUrl('',$this->messages[500797])."\" alt=\"\" class='help_icon' /></a>";
				}
			} else {
				return ''; //text is blank, do not give a help link
			}
		}
		else
		{
			//no user id
			return false;
		}
		return $help_link;
	} //end of function display_help_link

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_ad_images($classified_id=0)
	{
		if (!$classified_id) {
			return false;
		}
		
		$db = DataAccess::getInstance();
		$tpl = new geoTemplate('system', 'listing_details');
		$tpl_vars = array();
		
		$this->get_ad_configuration($db);
		$this->get_image_data($db,$classified_id);
		$image_count = count($this->images_to_display);
		$image_table = '';

		//if we don't have enough images to fill up all the possible columns
		$tpl_vars['columns'] = $columns = ($image_count < $this->ad_configuration_data->PHOTO_COLUMNS) ? $image_count : $this->ad_configuration_data->PHOTO_COLUMNS;
		$tpl_vars['width'] = $width = floor(100/$columns);
		$tpl_vars['width_percentage'] = $width_tag = $width . '%';
		
		
				
		if ((!is_array($this->images_to_display)) || (count($this->images_to_display) == 0)) {
			//no images to show
			return '';
		}
		
		ksort($this->images_to_display);
		reset($this->images_to_display);
		
		$galleryStyle = trim($db->get_site_setting('gallery_style'));
		//make sure it is set to something good...
		$galleryStyle = (strlen($galleryStyle)>1)? $galleryStyle : 'gallery';
		
		if ($galleryStyle == 'classic') {
			$images = array();
			foreach($this->images_to_display as $show_image) {
				$images[] = $this->display_image_tag($db, $show_image, false, 'small', $tpl);
			}
			$tpl_vars['images'] = $images;
		} else {
			//gallery or filmstrip views... both are similar to each other as far
			//as tpl vars needed
			
			//get the first image in "big" mode
			$image = current($this->images_to_display);
			$bigImage = array(
				'tag' => $this->display_image_tag($db,$image,true,'big', $tpl),
				'url' => $image['url'],
				'title' => $image['image_text'],
			);
			
			$tpl_vars['bigImage'] = $bigImage;
			
			//get the rest of the images
			$this->image_counter = 0;
			$images = array();
			foreach($this->images_to_display as $image) {
				$images[] = array(
					//$this->image_counter is incremented in display_image_tag
					//so make sure 'count' gets assigned before 'tag'!!
					'count' => $this->image_counter, 
					'tag' => $this->display_image_tag($db, $image, true, 'small', $tpl),
					'url' => $image['url']
				);				
			}
			$tpl_vars['images'] = $images;
			
			$dimensions = array(
				'max_width' => (($this->ad_configuration_data->MAXIMUM_IMAGE_WIDTH)?$this->ad_configuration_data->MAXIMUM_IMAGE_WIDTH:250),
				'max_height' => (($this->ad_configuration_data->MAXIMUM_IMAGE_HEIGHT)?$this->ad_configuration_data->MAXIMUM_IMAGE_HEIGHT:250),
				'max_thumb_width' => (($this->get_site_setting('maximum_thumb_width'))?$this->get_site_setting('maximum_thumb_width'):75),
				'max_thumb_height' => (($this->get_site_setting('maximum_thumb_height'))?$this->get_site_setting('maximum_thumb_height'):75),
				'max_full_width' => ($this->ad_configuration_data->MAXIMUM_FULL_IMAGE_WIDTH),
				'max_full_height' => ($this->ad_configuration_data->MAXIMUM_FULL_IMAGE_HEIGHT),
				'max_gallery_main_width' => (($this->get_site_setting('gallery_main_width'))?$this->get_site_setting('gallery_main_width'):500),
				'max_gallery_main_height' => (($this->get_site_setting('gallery_main_height'))?$this->get_site_setting('gallery_main_height'):500),
			);
			$tpl_vars['dimensions'] = $dimensions;

			$tpl_vars['baseHREF'] = $baseHREF = str_replace($this->get_site_setting('classifieds_file_name'),"",$this->get_site_setting('classifieds_url'));
			
		}
		$tpl->assign($tpl_vars);
		return $tpl->fetch("image_block/$galleryStyle.tpl");
	 }

//####################################################################################

	function display_image_tag($db,$value,$for_gallery=0, $size='small', &$tpl)
	{
		if($value['icon'] && !$value['thumb_url']) {
			//non-image file for which we want to display an icon
			//so set the thumbnail to be that icon
			$value['thumb_url'] = geoTemplate::getUrl('', $value['icon']);
		}
		
		//this is double-encoding image text...I put this here earlier because the text wasn't being encoded at all
		//now it's being done somewhere else, somehow, so pulling this out
		//if things start breaking, look at putting it back in and fixing the other place
		//if($value['image_text']) {
		//	$value['image_text'] = geoString::specialChars($value['image_text'], null, ENT_QUOTES);
		//}
		
		if(!$value['image_width'] || !$value['image_height'] || !$value['mime_type']) {
			//don't have image dimensions -- try to get them!
			$dims = geoImage::getRemoteDims($value['id']);
			$value['image_width'] = $dims['width'];
			$value['image_height'] = $dims['height'];
			$value['mime_type'] = $dims['mime'];
		}
		
		
		if ( $for_gallery ) {
			$max_thumb_width = ($this->get_site_setting('maximum_thumb_width'))?$this->get_site_setting('maximum_thumb_width'):75;
			$max_thumb_height = ($this->get_site_setting('maximum_thumb_height'))?$this->get_site_setting('maximum_thumb_height'):75;
			$max_width = (($this->get_site_setting('gallery_main_width'))?$this->get_site_setting('gallery_main_width'):240);
			$max_height = (($this->get_site_setting('gallery_main_height'))?$this->get_site_setting('gallery_main_height'):240);

			$dimBig = geoImage::getScaledSize($value['image_width'],$value['image_height'],$max_width, $max_height);
			$is_icon = (int)((bool)$value['icon']);
			if ( $size == 'big' ) {
				$tag = "
						<script>
							//<![CDATA[
							images[$this->image_counter] = new galleryAddImage( '{$value["id"]}', '".$value['url']."', '".$value['thumb_url']."', ".$dimBig['height'].", ".$dimBig['width'].", '".$value['image_text']."', $is_icon);
							//]]>
						</script>
						";
				$tag .= "<a href='{$value['url']}'>
						<img src=\"".(($value['thumb_url'])?$value['thumb_url']:$value['url'])."\" width=\"".$dimBig['width']."\" height=\"".$dimBig['height']."\" alt=\"".$value['image_text']."\" /></a>";
				$tpl->assign('bigImageWidth', $dimBig['width']);
			} else {
				$dim = geoImage::getScaledSize($value['image_width'],$value['image_height'],$max_thumb_width, $max_thumb_height);
				$tag = "
					<script>
						//<![CDATA[
						images[$this->image_counter] = new galleryAddImage( '{$value["id"]}', '{$value['url']}', '{$value['thumb_url']}', {$dimBig['height']}, {$dimBig['width']}, '{$value['image_text']}', $is_icon);
						//]]>
					</script>
					";
				$tag .= "<img src='".(($value['thumb_url'])?$value['thumb_url']:$value['url'])."' height='{$dim['height']}' width='{$dim['width']}' alt='".$value['image_text']."' title='".$value['image_text']."' />";
			}


			$this->image_counter++;
			return $tag;
		} else {
			$max_width = ($this->ad_configuration_data->MAXIMUM_IMAGE_WIDTH)?$this->ad_configuration_data->MAXIMUM_IMAGE_WIDTH:200;
			$max_height = ($this->ad_configuration_data->MAXIMUM_IMAGE_HEIGHT)?$this->ad_configuration_data->MAXIMUM_IMAGE_HEIGHT:200;
			
			$dim = geoImage::getScaledSize($value['image_width'],$value['image_height'],$max_width, $max_height);
			
			//echo $value["image_text"]." is image text2<br />\n";
			$link = false;
			if ($value["type"] == 1) {
				//display the url
				if (strlen(trim($value["icon"])) > 0) {
					$lin = true;
					$tag = "<a href=\"".$value["url"]."\" onclick='window.open(this.href); return false;'>";
					$tag .=  "<img src=\"".geoTemplate::getUrl('',$value["icon"])."\" alt=\"\" /></a>";
				} else {
					if ($dim['width'] != $value["original_image_width"]) {
						$link = true;
						if ($this->db->get_site_setting('image_link_destination_type')) {
							if ($this->affiliate_id) {
								$tag = "<a href=\"".$this->db->get_site_setting('affiliate_url')."?a=15&amp;b=".$value["classified_id"]."\" class=\"zoom_link\">";
							} else {
								$tag = "<a href=\"".$this->db->get_site_setting('classifieds_url')."?a=15&amp;b=".$value["classified_id"]."\" class=\"zoom_link\">";
							}
						} else {
							$tag = "<a href=\"get_image.php?id={$value["id"]}\" class=\"lightUpLink\" onclick=\"return false;\">";
						}
					}
					if ($value['thumb_url']) {
						$url = $value['thumb_url'];
						$width = $dim['width'];
						$height = $dim['height'];
					} else {
						$url = $value['url'];
						$width = $dim['width'];
						$height = $dim['height'];
					}
	
					$tag .= geoImage::display_image($url, $width, $height, $value['mime_type']);
				}
			}
	
			if ((strlen($value["image_text"]) > 0) && ($this->ad_configuration_data->MAXIMUM_IMAGE_DESCRIPTION)) {
				if (strlen($value["image_text"]) <= $this->ad_configuration_data->MAXIMUM_IMAGE_DESCRIPTION) {
					$tag .= "<br /><span class=\"zoom_link\">".$value["image_text"]."</span>";
				} else {
					$small_string = geoString::substr($value["image_text"],0,$this->ad_configuration_data->MAXIMUM_IMAGE_DESCRIPTION);
					$position = strrpos($small_string," ");
					$smaller_string = geoString::substr($small_string,0,$position);
					$tag .= "<br /><span class=\"zoom_link\">".$smaller_string."...</span>";
				}
			}
	
			if ($dim['width'] != $value["original_image_width"]) {
				$tag .= "<br /><span class=\"zoom_link\">".$this->messages[339]."</span><span class=\"zoom_link\">".$this->messages[12]."</span>";
			}
			if($link) {
				$tag .= "</a>";
			}
			return $tag;
		}
	} //end of function display_image_tag

//####################################################################################

	function get_image_data($db=0,$classified_id=0,$large=0)
	{
		if (!$classified_id) return false;
		
		if (($this->ad_configuration_data->NUMBER_OF_PHOTOS_IN_DETAIL) && (!$large)) {
			
			$photo_limit = " order by display_order limit ".$this->ad_configuration_data->NUMBER_OF_PHOTOS_IN_DETAIL;
		} else {
			$photo_limit = " order by display_order";
		}
		$this->sql_query = "select * from ".$this->images_urls_table." where classified_id = ".$classified_id.$photo_limit;
		//echo $this->sql_query."<br />\n";
		$result = $this->db->Execute($this->sql_query);

		if (!$result) {
			return false;
		} elseif ($result->RecordCount() > 0) {
			while ($show_urls = $result->FetchRow()) {
				$this->images_to_display[$show_urls['display_order']]["type"] = 1;
				$this->images_to_display[$show_urls['display_order']]["id"] = $show_urls['image_id'];
				$this->images_to_display[$show_urls['display_order']]["image_width"] = $show_urls['image_width'];
				$this->images_to_display[$show_urls['display_order']]["image_height"] = $show_urls['image_height'];
				$this->images_to_display[$show_urls['display_order']]["original_image_width"] = $show_urls['original_image_width'];
				$this->images_to_display[$show_urls['display_order']]["original_image_height"] = $show_urls['original_image_height'];
				$this->images_to_display[$show_urls['display_order']]["url"] = $show_urls['image_url'];
				$this->images_to_display[$show_urls['display_order']]["classified_id"] = $show_urls['classified_id'];
				$this->images_to_display[$show_urls['display_order']]["image_text"] = $show_urls['image_text'];
				$this->images_to_display[$show_urls['display_order']]["thumb_url"] = $show_urls['thumb_url'];
				$this->images_to_display[$show_urls['display_order']]["icon"] = $show_urls['icon'];
				$this->images_to_display[$show_urls["display_order"]]["mime_type"] = $show_urls['mime_type'];
			}
		}
	} //end of function get_image_data
//############################################################################

	function get_html_disallowed_array()
	{
		$this->sql_query = "select * from ".$this->html_allowed_table." where tag_status = 1";
		$html_result = $this->db->Execute($this->sql_query);
		//$this->body .=$this->sql_query."<br />\n";
		if (!$html_result)return false;
		
		if ($html_result->RecordCount() > 0) {
			$this->row_count = 0;
			while ($show_html = $html_result->FetchNextObject()) {
				//$this->body .=$show_html->TAG_NAME." is the tag name<br />\n";
				if ($show_html->USE_SEARCH_STRING) {
					//$this->html_open_disallowed_list[$this->row_count] = str_replace("+++++",$show_html->TAG_NAME,$this->html_disallowed_string);
					//$this->html_closed_disallowed_list[$this->row_count] = str_replace("+++++",$show_html->TAG_NAME,$this->html_disallowed_string);
					$this->html_disallowed_list[$this->row_count] = str_replace("+++++",$show_html->TAG_NAME,$this->html_disallowed_string);
					//$this->body .=$this->html_disallowed_list[$this->row_count]." is html disallowed ".$this->row_count."<br />\n";
				} else {					
					$this->html_disallowed_list[$this->row_count] = "'".$show_html->TAG_NAME."'i";
				}
				$this->html_disallowed_replacement[$this->row_count] = $show_html->REPLACE_WITH;
				//$this->body .=$this->html_disallowed_list[$this->row_count]." is html disallowed ".$this->row_count."<br />\n";
				$this->row_count++;
			}
			return true;
		} else {
			$this->html_disallowed_list = 0;
			return true;
		}
	} //end of function get_html_disallowed_array

//#########################################################################

	/**
	 * filter valid html, function shall be removed soon
	 *
	 * @param unknown_type $db
	 * @param unknown_type $text
	 * @param unknown_type $remove_all
	 * @deprecated 
	 * @return unknown
	 */
	function replace_disallowed_html($db,$text,$remove_all=0)
	{
		return geoFilter::replaceDisallowedHtml($text,$remove_all);
	}

//#########################################################################
	/**
	 * @depricated 
	 */
	function get_badword_array()
	{
		return true;
	} //end of function get_badword_array

//#########################################################################
	
	/**
	 * checks for badwords.
	 * 
	 * Use geoFilter::badword($text) instead!
	 *
	 * @param string $text
	 * @return string
	 */
	function check_for_badwords($text)
	{
		return geoFilter::badword($text);
	} //end of function check_for_badwords

//#########################################################################

	function get_image_file_types_array()
	{
		$this->sql_query = "select * from ".$this->file_types_table." where accept = 1";
		$type_result = $this->db->Execute($this->sql_query);
		if (!$type_result) {
			return false;
		} elseif ($type_result->RecordCount() > 0) {
			while ($show = $type_result->FetchNextObject()) {
				array_push($this->image_file_types,$show->MIME_TYPE);
				array_push($this->image_file_types_icon,$show->ICON_TO_USE);
				array_push($this->image_file_types_extension,$show->EXTENSION);
			}
		}
		return true;
	} //end of get_image_file_types_array

//#########################################################################

	function image_accepted_type($type)
	{
		reset($this->image_file_types);
		foreach ($this->image_file_types as $key => $value) {
			if (strstr($type,$value)) {
				$image_accepted_type = 1;
				$this->current_file_type_icon = $this->image_file_types_icon[$key];
				$this->current_file_type_extension = $this->image_file_types_extension[$key];
				return true;
			}
		}
		return false;
	} //end of function image_accepted_type

//#########################################################################

	function get_category_string($db,$category)
	{
		$category_tree = $this->category_tree_array = geoCategory::getTree($category);
		reset ($this->category_tree_array);

		if ($category_tree) {
			//category tree
			$category_string = urldecode($this->messages[79])." > ";
			if (is_array($this->category_tree_array)) {
				$i = 0;
				foreach($this->category_tree_array as $cat) {
					//display all the categories
					$category_string .= $cat[$i]["category_name"];
					if(++$i != count($this->category_tree_array)) {
						$category_string .= " > ";
					}
				}
			} else {
				$this->body .=$category_tree;
			}
		}
	}

//##################################################################################

	function check_extra_questions()
	{
		$num_questions = isset($this->classified_variables["question_value"]) ? count($this->classified_variables["question_value"]) : 0;
		//$this->body .=$num_questions." is the num of questions remembered<br />\n";
		if ($num_questions > 0 ) {
			while (list($key,$value) = each($this->classified_variables["question_value"])) {
				 if (strlen(trim($value)) > 0) {
					if (isset($this->classified_variables["question_value_other"][$key]) && strlen(trim($this->classified_variables["question_value_other"][$key])) > 0) {
						//check other value
						//wordrap
						$this->classified_variables["question_value_other"][$key] = geoString::breakLongWords($this->classified_variables["question_value_other"][$key],$this->db->get_site_setting('max_word_width'), " \n");
						//check the value for badwords
						$this->classified_variables["question_value_other"][$key] = $this->check_for_badwords($this->classified_variables["question_value_other"][$key]);
						//check the value for disallowed html
						$this->classified_variables["question_value_other"][$key] = geoFilter::replaceDisallowedHtml($this->classified_variables["question_value_other"][$key],0);

					} else {
						//check dropdown or input box value
						//wordrap
						//$this->classified_variables["question_value"][$key] = wordwrap($this->classified_variables["question_value"][$key],$this->db->get_site_setting('max_word_width'), " \n",1);
						$this->classified_variables["question_value"][$key] = geoString::breakLongWords($this->classified_variables["question_value"][$key],$this->db->get_site_setting('max_word_width'), " \n");
						//check the value for badwords
						$this->classified_variables["question_value"][$key] = $this->check_for_badwords($this->classified_variables["question_value"][$key]);
						//check the value for disallowed html
						$this->classified_variables["question_value"][$key] = geoFilter::replaceDisallowedHtml($this->classified_variables["question_value"][$key],0);
					}
				} // end of if
			}//end of while
		}// end of if num_questions > 0
	} //end of function check_extra_questions
	

//################################################################################

	function in_array_key($key, $array, $value = false)
	{
		if (is_array($array)) {
			while(list($k, $v) = each($array)) {
				if($key == $k) {
					if($value && $value == $v)
						return true;
					elseif($value && $value != $v)
						return false;
					else
						return true;
				}
			}
		}
		return false;
	} //end of function in_array_key

//#################################################################################
	/**
	 * @deprecated 9/26/2006 Is this used any more?  If not,remove function
	 */
	function expire_groups_and_plans($db)
	{
		//it has moved to bookkeeping file.
		include_once('./bookkeeping.php');
		if (strlen(PHP5_DIR) > 0){
			$bookkeeping = Singleton::getInstance('bookkeeping');
		} else {
			$bookkeeping =& Singleton::getInstance('bookkeeping');
		}
		$bookkeeping->expire_groups_and_plans();
		return true;

	} //end of function expire_groups_and_plans


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_ad_count_for_category($db,$category_id=0)
	{
		if ($category_id)
		{
			//get the count for this category
			$count = 0;

			$this->sql_query = "select category_id from ".$this->categories_table." where parent_id = ".$category_id;
			$category_id_result = $this->db->Execute($this->sql_query);
			//$this->body .=$this->sql_query."<br />";
			if (!$category_id_result)
			{
				//$this->body .=$this->sql_query." is the query<br />\n";
				$this->error_message = $this->messages[2524];
				return false;
			}
			elseif ($category_id_result->RecordCount() > 0)
			{
				while ($show_category = $category_id_result->FetchNextObject())
				{
					$returned_count = $this->get_ad_count_for_category($db,$show_category->CATEGORY_ID);
					if ($returned_count)
						$count += $returned_count;

					//$this->body .=$count." is count returned for category ".$category_id."<br />\n";
				}
			}

			$count += $this->get_ad_count_this_category($db,$category_id);
			return $count;
		}
		else
		{
			//category_id is missing
			return false;
		}

	} //end of function get_ad_count_for_category

//##################################################################################

	function get_ad_count_this_category($db,$category_id=0)
	{
		if ($category_id)
		{
			//get the count for this category
			$count = 0;

			$this->sql_query = "select count(*) as total from ".$this->classifieds_table." where live = 1 and category = ".$category_id;
			$count_result = $this->db->Execute($this->sql_query);
			//$this->body .=$this->sql_query."<br />\n";
			if (!$count_result)
			{
				//$this->body .=$this->sql_query." is the query<br />\n";
				$this->error_message = $this->messages[2524];
				return false;
			}
			elseif ($count_result->RecordCount() == 1)
			{
				$show = $count_result->FetchNextObject();
				return $show->TOTAL;
			} else {
				return 0;
			}
		}
		else
		{
			//category_id is missing
			return false;
		}

	} //end of function get_ad_count_this_category

//##################################################################################
	
	/**
	 * Use geoCategory::updateListingCount() instead of this!
	 * @param unknown $db
	 * @param number $category_id
	 * @return boolean
	 * @deprecated Do not use!  Deprecated on Jan 30, 2014 - version 7.4.0 - will 
	 *   be removed in future version
	 */
	function update_category_count($db,$category_id=0)
	{
		return geoCategory::updateListingCount($category_id);
	} //end of function update_category_count

	/**
	 *	Displays the formatted category count
	 *	@param $db
	 *	@param int $category_id
	 *	@param int $browsing_count_format format that the count will be printed in
	 *	@param string $link the link for all the pages
	 *	@param array $css the css tags that will be used for each part of the count keyed by listing_count, ad_count, and auction_count
	 *	@return string text that contains the count or counts
	 */
	function display_category_count($db=0, $category_id=0, $browsing_count_format=-1, $link=0, $css=0, $category_count=0)
	{
		if((!is_array($category_count)) || $this->db->getTableSelect(DataAccess::SELECT_BROWSE)->hasWhere()) {
			//force get category count if what was provided is not valid or if there
			//are currently site filters applied
			$category_count = geoCategory::getListingCount($category_id);
		}

		// Check for css and build the link tag from there
		if($link)
		{
			if($css)
			{
				$link_css["listing_count"] = str_replace(">", " class=\"".$css['listing_count']."\">", $link);
				$link_css["ad_count"] = str_replace(">", " class=\"".$css['ad_count']."\">", $link);
				$link_css["auction_count"] = str_replace(">", " class=\"".$css['auction_count']."\">", $link);
			}
			else
			{
				$link_css["listing_count"] = $link;
				$link_css["ad_count"] = $link;
				$link_css["auction_count"] = $link;
			}
		}
		elseif($css && is_array($css))
		{
			$link_css["listing_count"] = "<span class=\"".$css['listing_count']."\">";
			$link_css["ad_count"] = "<span class=\"".$css['ad_count']."\">";
			$link_css["auction_count"] = "<span class=\"".$css['auction_count']."\">";
		}

		// It will only use the passed in variable when called from a module
		if($browsing_count_format == -1) $browsing_count_format = $this->db->get_site_setting('browsing_count_format');
		
		$this->product_configuration = geoPC::getInstance();
		if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
			switch ($browsing_count_format)
			{
				case -1:
					if($link)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</a>";
					elseif($css)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</span>";
					else
						return "(".$category_count['listing_count'].")";
					break;
				case 0:
					if($link)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</a>";
					elseif($css)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</span>";
					else
						return "(".$category_count['listing_count'].")";
					break;
				case 1:
					if($link)
						return $link_css['auction_count']."(".$category_count['auction_count'].")</a>";
					elseif($css)
						return $link_css['auction_count']."(".$category_count['auction_count'].")</span>";
					else
						return "(".$category_count['auction_count'].")";
					break;
				case 2:
					if($link)
						return $link_css['ad_count']."(".$category_count['ad_count'].")</a>";
					elseif($css)
						return $link_css['ad_count']."(".$category_count['ad_count'].")</span>";
					else
						return "(".$category_count['ad_count'].")";
					break;
				case 3:
					if($link)
						return $link_css['auction_count']."(".$category_count['auction_count'].")</a>".$link_css['ad_count']."(".$category_count['ad_count'].")</a>";
					elseif($css)
						return $link_css['auction_count']."(".$category_count['auction_count'].")</span>".$link_css['ad_count']."(".$category_count['ad_count'].")</span>";
					else
						return "(".$category_count['auction_count'].")(".$category_count['ad_count'].")";
					break;
				case 4:
					if($link)
						return $link_css['ad_count']."(".$category_count['ad_count'].")</a>".$link_css['auction_count']."(".$category_count['auction_count'].")</a>";
					elseif($css)
						return $link_css['ad_count']."(".$category_count['ad_count'].")</span>".$link_css['auction_count']."(".$category_count['auction_count'].")</span>";
					else
						return "(".$category_count['ad_count'].")(".$category_count['auction_count'].")";
					break;
				case 5:
					if($link)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</a>";
					elseif($css)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</span>";
					else
						return "(".$category_count['listing_count'].")";
					break;
				default:
					if($link)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</a>";
					elseif($css)
						return $link_css['listing_count']."(".$category_count['listing_count'].")</span>";
					else
						return "(".$category_count['listing_count'].")";
					break;
			}
		}
		elseif (geoMaster::is('auctions'))
		{
			if($link)
				return $link_css['auction_count']."(".$category_count['auction_count'].")</a>";
			elseif($css)
				return $link_css['auction_count']."(".$category_count['auction_count'].")</span>";
			else
				return "(".$category_count['auction_count'].")";
		}
		elseif (geoMaster::is('classifieds'))
		{
			if($link)
				return $link_css['ad_count']."(".$category_count['ad_count'].")</a>";
			elseif($css)
				return $link_css['ad_count']."(".$category_count['ad_count'].")</span>";
			else
				return "(".$category_count['ad_count'].")";
		}
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function send_communication ($to=0,$message_data=0)
	{
		$db = DataAccess::getInstance();
		
		//sanity checks to make sure we have enough data to send a message
		if (!$message_data || !strlen(trim($message_data['message'])) || !strlen(trim($message_data['from']))) {
			//missing critical message data -- cannot proceed
			return false;
		}
		
		$regarding_ad = (int)$message_data["regarding_ad"] ? (int)$message_data["regarding_ad"] : (int)$message_data['classified_id'];
		if (!$regarding_ad) {
			//all messages must be associated with a listing!
			return false;
		}
		
		//if a subject was passed in, use it. otherwise, use the listing's title
		$subject = strlen(trim($message_data['subject'])) ? trim($message_data['subject']) : geoListing::getTitle($regarding_ad); 
		
		if($message_data["replied_to_this_messages"] && $to == "reply") {
			//this is a reply
			//pull the recipient from the original message
			$sql = "SELECT `message_from` FROM ".geoTables::user_communications_table." WHERE message_id = ?";
			$to = $db->GetOne($sql, array($message_data["replied_to_this_messages"]));
		}
		
		$to_user = geoUser::getUser($to);
		
		if($to_user) {
			$messageTarget = $to_user->email;
			$message_to = $to_user->id;
		} else {
			//if we don't have a user object to send to, this is a reply to a non-user
			//find the email address to reply to
			$sql = "SELECT `message_from_non_user` FROM ".geoTables::user_communications_table." WHERE message_id = ?";
			$messageTarget = $db->GetOne($sql, array($message_data["replied_to_this_messages"]));
			$message_to = 0;
		}
		
		$this->page_id = 25;
		$this->get_text();
		
		//un-do input cleaning before sending message, so single quotes don't appear as &#039;
		//also strip any html, for security, since emails are all sent in HTML mode now
		$message_data['message'] = strip_tags(geoString::specialCharsDecode($message_data['message']));
		
		$tpl = new geoTemplate('system','emails');
		
		$ip = $_SERVER['REMOTE_ADDR'];
		$host = gethostbyaddr($ip);
		$tpl->assign('senderIP', $ip);
		$tpl->assign('senderHost', $host);
		
		$tpl->assign('fromLabel',$this->messages[412]);
		$tpl->assign('messageBody', $message_data['message']);
					
		//send an email
		if ($from_user = geoUser::getUser(geoSession::getInstance()->getUserId())) {
			$tpl->assign('messageFromUsername', $from_user->username);
			$fromField = 'message_from';
			$fromId = geoSession::getInstance()->getUserId();
			if ($from_user->communication_type == 1) {
				//"public" communication type (expose "from" email address)
				$tpl->assign('messageFromEmail', $from_user->email);
				$message_from = $from_user->email;
			} else {
				//"private" communication type (add reply link to email)
				$message_from = $this->db->get_site_setting('site_email');
			}
		} else {
			$tpl->assign('messageFromUsername', $message_data["from"]);
			$fromField = 'message_from_non_user';
			$fromId = $message_from = $message_data["from"]; 
		}
			
		$message = $tpl->fetch('communication/user_message.tpl');
		
		
		//if this is a public answer to a public question, note such in the db
		$isPublicAnswer = ($message_data['public_answer'] == 1) ? 1 : 0;
		//$bodyText is used by the public questions/answers to hold the "pure" text of the message, minus things like "Hello {user},..."
		$bodyText = geoString::toDB($message_data['message']);
			
		$replyToMessage = ($message_data['replied_to_this_messages']) ? $message_data['replied_to_this_messages'] : 0;
		
		$sql = "INSERT INTO ".geoTables::user_communications_table." (message_to, $fromField, regarding_ad, date_sent, message, replied_to_this_message, body_text, public_answer) VALUES (?,?,?,?,?,?,?,?)";
		$result = $db->Execute($sql, array($message_to, $fromId, $regarding_ad, geoUtil::time(), geoString::toDB($message), $replyToMessage, $bodyText, $isPublicAnswer));
		if (!$result) {
			return false;
		}

		//if needed, fetch message again, adding reply link
		if ($from_user && $from_user->communication_type != 1) {
			//NOTE: We are going to re-render the message contents, so that the
			//contents sent in the e-mail includes the reply link in it. We don't
			//want the reply link showing when user views the message on the site.
			$tpl->assign('privateCommMessage', $this->messages[1198]);
			$tpl->assign('privateReplyLink', $this->db->get_site_setting('classifieds_url')."?a=3&amp;b=".$from_user->id."&amp;c=".$regarding_ad);
			$tpl->assign('showReplyLink', true);
			$message = $tpl->fetch('communication/user_message.tpl');
		}
			
		//send mail after adding to db in case of mail errors
		geoEmail::sendMail($messageTarget, $subject, $message, $message_from, $message_from, 0, 'text/html');
		return true;
	} //end of function send_communication

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	function check_user_subscription()
	{
		$this->function_name = "check_user_subscription";
		$user_id = (isset($this->auction_user_id))? $this->auction_user_id: $this->userid;
		$sql = "select * from ".$this->user_subscriptions_table." where subscription_expire > ".geoUtil::time()." and user_id = ".$user_id;
		$get_subscriptions_results = $this->db->Execute($sql);
		
		if (!$get_subscriptions_results)
		{
			trigger_error('ERROR SQL: sql: '.$sql.' Error Msg: '.$this->db->ErrorMsg());
			return false;
		}
		elseif ($get_subscriptions_results->RecordCount() == 0)
		{
			return false;
		}
		elseif ($get_subscriptions_results->RecordCount() > 0)
		{
			return true;
		}
	} // end of function check_user_subscription

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	/**
	 * @deprecated 6/9/2008 Is this used any more?  If not,remove function
	 */
	function update_balance_approval($db,$transaction_id=0,$cc_transaction_id=0)
	{
		if ($transaction_id)
		{
			$this->sql_query = "update ".$this->balance_transactions." set
				approved = 1,
				cc_transaction_id = ".$cc_transaction_id."
				where transaction_id = ".$transaction_id;
			$update_balance_transaction_result = $this->db->Execute($this->sql_query);
			if (!$update_balance_transaction_result)
				return false;
			else
				return true;
		}
		else
			return false;
	} //end of function update_balance_approval

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_price_plan_from_group($db,$group_id=0,$auctions=0)
	{
		if ($group_id)
		{
			$this->sql_query = "select * from ".$this->groups_table." where group_id = ".$group_id;
			$group_price_plan_result = $this->db->Execute($this->sql_query);
			//echo $this->sql_query." is the query<br />\n";
			//$this->body .=$this->sql_query." is get_price_plan query<br />\n";
			if (!$group_price_plan_result)
			{
				$this->error_message = $this->internal_error_message;
				$this->site_error($this->sql_query,$this->db->ErrorMsg());
				return false;
			}
			elseif ($group_price_plan_result->RecordCount() == 1)
			{
				$show_group_price_plan = $group_price_plan_result->FetchNextObject();

				if($auctions)
					$this->sql_query = "select * from ".$this->price_plans_table." where price_plan_id = ".$show_group_price_plan->AUCTION_PRICE_PLAN_ID;
				else
					$this->sql_query = "select * from ".$this->price_plans_table." where price_plan_id = ".$show_group_price_plan->PRICE_PLAN_ID;
				$price_plan_result = $this->db->Execute($this->sql_query);
				//echo $this->sql_query." is the query<br />\n";
				//$this->body .=$this->sql_query." is get_price_plan query<br />\n";
				if (!$price_plan_result)
				{
					$this->error_message = $this->internal_error_message;
					$this->site_error($this->sql_query,$this->db->ErrorMsg());
					return false;
				}
				elseif ($price_plan_result->RecordCount() == 1)
				{
					$show_price_plan = $price_plan_result->FetchNextObject();
					return $show_price_plan;
				}
				else
				{
					return false;
				}
			}
			else
			{
				//just display the user_id
				return false;
			}
		}
		else
		{
			$this->error_message = $this->internal_error_message;
			return false;
		}
	} //end of function get_price_plan_from_group

//########################################################################

	function get_auctions_price_plan_from_group($db,$group_id=0)
	{
		if ($group_id)
		{
			$this->sql_query = "select * from ".$this->groups_table." where group_id = ".$group_id;
			$group_price_plan_result = $this->db->Execute($this->sql_query);
			//echo $this->sql_query." is the query<br />\n";
			//$this->body .=$this->sql_query." is get_price_plan query<br />\n";
			if (!$group_price_plan_result)
			{
				$this->error_message = $this->internal_error_message;
				$this->site_error($this->sql_query,$this->db->ErrorMsg());
				return false;
			}
			elseif ($group_price_plan_result->RecordCount() == 1)
			{
				$show_group_price_plan = $group_price_plan_result->FetchNextObject();
				$this->sql_query = "select * from ".$this->auctions_price_plans_table." where price_plan_id = ".$show_group_price_plan->AUCTION_PRICE_PLAN_ID;
				$price_plan_result = $this->db->Execute($this->sql_query);
				//echo $this->sql_query." is the query<br />\n";
				//$this->body .=$this->sql_query." is get_price_plan query<br />\n";
				if (!$price_plan_result)
				{
					$this->error_message = $this->internal_error_message;
					$this->site_error($this->sql_query,$this->db->ErrorMsg());
					return false;
				}
				elseif ($price_plan_result->RecordCount() == 1)
				{
					$show_price_plan = $price_plan_result->FetchNextObject();
					return $show_price_plan;
				}
				else
				{
					return false;
				}
			}
			else
			{
				//just display the user_id
				return false;
			}
		}
		else
		{
			$this->error_message = $this->internal_error_message;
			return false;
		}
	} //end of function get_auctions_price_plan_from_group

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function expand_array($a)
	{
		ob_start();
		print_r($a);
		$t= ob_get_contents();
		ob_end_clean();
		for($c=10;$c>=1;$c--)
		{
			$search="\n ".str_repeat(" ",4*$c-1);
			$replace="<br />\n".str_repeat("&nbsp;",8*$c);
			$t= str_replace($search,$replace,$t);
		}
		//Final adjustment which takes care of the single last closing parenthesis
		$t= str_replace("\n\n)","<br />\n)",$t);

		return $t;
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	/**
	 * Gets number of bids.  Do not use, use geoListing::bidCount() instead.
	 * @param null $db
	 * @param int $auction_id
	 * @deprecated In version 6.0.0, will be removed in future release, use geoListing::bidCount()
	 *   instead.
	 */
	public function get_number_of_bids ($db=0,$auction_id)
	{
		return geoListing::bidCount($auction_id);
	} //end of function get_number_of_bids
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function DateDifference ($interval, $date1,$date2)
	{
		$difference =  $date2 - $date1;
		switch ($interval)
		{
			case "w":
				$returnvalue  =$difference/604800;
				break;
				
			case "d":
				$returnvalue  = $difference/86400;
				break;
				
			case "h":
				$returnvalue = $difference/3600;
				break;
				
			case "m":
				$returnvalue  = $difference/60;
				break;
				
			case "s":
				$returnvalue  = $difference;
				break;
    	}
    	return intval($returnvalue);
	} //end of function DateDifference

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

  /**
   * Returns formated money according to switch set in admin
   *
   * @param float $value
   * @param string $precurrency
   * @param string $postcurrency
   * @param integer/boolean $show_zero
   * @param integer/boolean $chop_zero_decimals
   * @return string="money" | char='-'meaning nothing
   * @deprecated  this function is safe to removed as long as a workspace wide search for it has been placed, and instead can you 
   * the new functio showMoney in geoListing Class
   */
	function show_money($value=0,$precurrency='',$postcurrency='',$show_zero=0, $chop_zero_decimals=0)
	{
		$value = (is_numeric($value)) ? $value : trim(stripslashes(urldecode($value)));
		$value = (strlen($value)<=0 || $value==0) ? 0 : $value;

		//append with space
		$pre = !$precurrency ? '' : stripslashes(urldecode($precurrency)).' ';
		//prepend with space
		$post = !$postcurrency ? '' : ' '.stripslashes(urldecode($postcurrency));

		$precision = (floor($value)==$value && $chop_zero_decimals) ? 0 : 2;
		//if there is a value, show everything
		if ($value<=0) {
			//only show postcurrency if it is provided and the other two are not
			if (strlen($pre)==0 && strlen($post)>0 && $value > 0)
				return geoNumber::format($value).$post;
			elseif (strlen($pre)==0 && strlen($post)>0 && $value == 0)
				return $post;	
			elseif (!$show_zero)
				return '-';
			//else do everything below
		}
		$display_amount = $pre.geoNumber::format($value).' '.$post;
		return $display_amount;
	} //end of show_money
//########################################################################
	/**
	 * @deprecated 6/9/2008 Is this used any more?  If not,remove function
	 */
	function is_class_auctions()
	{
		return (geoMaster::is('classifieds') && geoMaster::is('auctions'));
	}

//########################################################################
	/**
	 * @deprecated 6/9/2008 Is this used any more?  If not,remove function
	 */
	function is_auctions()
	{
		return geoMaster::is('auctions');
	}

//########################################################################
	/**
	 * @deprecated 6/9/2008 Is this used any more?  If not,remove function
	 */
	function is_classifieds()
	{
		return geoMaster::is('classifieds');
	}

//########################################################################
	/**
	 * @deprecated 6/9/2008 Is this used any more?  If not,remove function
	 */
	function set_type($type)
	{
		if (!isset($this->product_configuration) || !is_object($this->product_configuration)){
			if (strlen(PHP5_DIR)){
				$this->product_configuration = geoPC::getInstance();
			} else {
				$this->product_configuration =& geoPC::getInstance();
			}
		}
		$this->product_configuration->set_type($type);
	}
	
	function get_time($hour,$min,$month,$day,$year)
	{
		return mktime($hour, $min, 0, $month, $day, $year);
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_high_bidder_username ($db=0,$auction_id,$reverse_auction = false)
	{
		if ($this->debug) {
			echo "<br>TOP OF GET_HIGH_BIDDER_USERNAME<Br>\n";
		}
		$auction_id = (int)$auction_id;
		if (!$auction_id) {
			return false;
		}
		
		$sql = "SELECT `bidder` FROM ".geoTables::bid_table." WHERE `auction_id`='$auction_id'
			ORDER BY `bid` ".(($reverse_auction)? 'ASC' : 'DESC').", `time_of_bid` ASC";
		$user_id = $this->db->GetOne($sql);
		
		if ($user_id===false) {
			trigger_error("ERROR SQL: sql: $sql Error: ".$this->db->ErrorMsg());
			return false;
		}
		$user_id = (int)$user_id;
		if (!$user_id) {
			//no results found
			return false;
		}
		$user = geoUser::getUser($user_id);
		return ($user)? $user->username : '';
	} //end of function get_high_bidder_username

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	/**
	 * DEPRECATED - do not use!
	 * 
	 * @deprecated on nov. 27, 2012 (version 7.1.0), use geoListing::getHighBidder()
	 *   instead!  This will be removed in future version.
	 */
	function get_high_bidder($db,$auction_id=0)
	{
		if ($this->debug)
		{
			echo "<br>TOP OF GET_HIGH_BIDDER<Br>\n";
		}
		return geoListing::getHighBidder($auction_id);
	}

//#######################################################################

	function item_price($item_type_passed=1)
	{
		if($item_type_passed == 2)
		{
			return "minimum_bid";
		}
		else
		{
			return "price";
		}
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function check_blacklist($db,$seller_id,$auction_user_id)
	{
		if (($seller_id) && ($auction_user_id)) {
			$this->sql_query = "select * from ".$this->blacklist_table." where seller_id =".intval($seller_id)." and user_id =".intval($auction_user_id)." ";
			if ($this->debug_display_auction)
				echo $this->sql_query." is query 2 <bR>";
			$blacklist_result = $this->db->Execute($this->sql_query);
			if($blacklist_result && $blacklist_result->RecordCount() > 0){
				return true;
			}
		}
		return false;
	} //end of function check_blacklist

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function check_invitedlist($db,$seller_id=0,$auction_user_id=0)
	{
		if (($seller_id) && ($auction_user_id))
		{
			//check to see if there are any in the invited table for this seller
			$this->sql_query = "select * from ".$this->invitedlist_table." where seller_id =".$seller_id;
			if ($this->debug_display_auction) echo $this->sql_query." is query checking if any invited list <bR>";
			$any_invitedlist_result = $this->db->Execute($this->sql_query);
			if (!$any_invitedlist_result)
			{
				return 0;
			}
			elseif ($any_invitedlist_result->RecordCount() > 0)
			{
				//check to see if this auction_user_id in invited list attached with this seller
				$this->sql_query = "select * from ".$this->invitedlist_table." where seller_id =".$seller_id." and user_id =".$auction_user_id." ";
				if ($this->debug_display_auction)
					echo $this->sql_query." is <bR>";
				$invitedlist_result = $this->db->Execute($this->sql_query);
				if(!$invitedlist_result)
				{
					return 0;
				}
				else if($invitedlist_result->RecordCount() > 0 )
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				//there are no invited buyers in this sellers list
				//this is treated as if all buyers are invited
				return 2;
			}
		}
	} //end of function check_invitedlist


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_year_dropdown($variable,$return = "")
	{
		
		$date = getdate();

		$display_year .= "<select name=\"".$variable."\">";
		for ($i=0;$i<20;$i++)
		{
			$display_year .= "<option value=\"".sprintf("%02d",($date['year']+$i))."\">".sprintf("%02d",($date['year']+$i))."</option>";
		}
		
		$display_year .= "</select>";
		
		
		if ($return)
		{
			return $display_year;
		}
		else
		{
			$this->body .= $display_year;
		}
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_end_time()
	{
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = ($endtime - $this->starttime);
		return $totaltime;
	} // end of function get_end_time

	/**
	 * Alias of geoEmail::sendMail(), use that instead of this method.
	 *
	 * @param string $to
	 * @param string $subject
	 * @param string $content
	 * @param string $from pass zero for site default
	 * @param string $replyTo pass zero for site default
	 * @param string $charset pass zero for site default
	 * @param string $type pass zero for site default
	 * @deprecated 3/23/2009
	 */
	function sendMail($to,$subject,$content,$from=0,$replyTo=0,$charset=0,$type=0) {
		geoEmail::sendMail($to,$subject,$content,$from,$replyTo,$charset,$type);
		return true;
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	 /**
	 * Gets a particular setting, returns false if the setting is not found.
	 * @param string setting The setting you wish to get.
	 * @return mixed The value for the setting, or false if the setting is not set.
	 */
	function get_site_setting($setting){
		return $this->db->get_site_setting($setting);
	}

	function insert_favorite($db,$favorite_id)
	{
		if ($this->userid)
		{
			if ($favorite_id)
			{
				$this->sql_query = "select * from ".$this->favorites_table."
					where classified_id = ".$favorite_id." and user_id = ".$this->userid;
				$result = $db->Execute($this->sql_query);
				if ($this->debug_favorites) echo $this->sql_query."<br />\n";
				if (!$result)
				{
					if ($this->debug_favorites) echo $this->sql_query."<br />\n";
					return false;
				}
				if ($result->RecordCount() == 0)
				{
					$this->sql_query = "insert into ".$this->favorites_table."
						(user_id,classified_id,date_inserted)
						values
						(".$this->userid.",".$favorite_id.",".geoUtil::time().")";
					$result = $db->Execute($this->sql_query);
					if ($this->debug_favorites) echo $this->sql_query."<br />\n";
					if (!$result)
					{
						if ($this->debug_favorites) echo $this->sql_query."<br />\n";
						return false;
					}
				}
				return true;
			}
			else
			{
				//no favorite_id
				$this->error_message = $this->data_missing_error_message;
				return false;
			}
		}
		else
		{
			$this->error_message = urldecode($this->messages[359]);
			return false;
		}

	} //end of function insert_favorite	
} //end of class geoSite
