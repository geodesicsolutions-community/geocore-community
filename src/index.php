<?php 
//root file(index)
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
## ##    16.02.1-1-gbb9ee07
## 
##################################

//main loader

//set a constant to show that we've started from the main entry point
define('GEO_INDEX', 1);

require_once('app_top.main.php');

//so that we do not have an undefined index below.
$_REQUEST['a']=isset($_REQUEST['a']) ? $_REQUEST['a'] : '';

//inputs should not be cleaned here, they need to be cleaned at the actual location
//that they are used.
switch ($_REQUEST["a"]) {
	case 'ap':
		//ap = addon page
		if ( isset( $_GET['addon'] ) && isset( $_GET['page'] ) ) {
			// setup the site
			$site = Singleton::getInstance('geoSite');
			$site->classified_user_id = $session->getUserId();
			$site->language_id = $db->getLanguage();
			
			// get the variables
			$addon_name = $site->addon_name = trim($_GET['addon']);
			
			$page = trim($_GET['page']);
			$site->page_id = "addons/{$addon_name}/{$page}";

			// do the addon stuff
			$pagesObj = geoAddon::getInstance()->getPagesClass($addon_name);
			if (is_object($pagesObj) && (method_exists($pagesObj, $page) || method_exists($pagesObj, '__call'))) {
				//Note that if using __call magic method in the addon's pages
				//class, any security concerns related to if the requested page
				//is valid or not is left to the __call method to do in the
				//addon.
				$html = $pagesObj->$page();
				$view = geoView::getInstance();
				if (!$view->isRendered()) {
					$view->addBody($html);
					
					$site->display_page();
				}
				include GEO_BASE_DIR . 'app_bottom.php';
			} else if (defined('IAMDEVELOPER')) {
				//display debug output, but only if IAMDEVELOPER is defined (hint: in your config.php file)
				echo '<strong>Error:  </strong> Page not found, or addon not installed/enabled.
				<br /><br /><strong>Note:</strong> You can see this
				message because the site admin has defined IAMDEVELOPER, which is used for debugging purposes.
				<br /><br /><strong>Debug Information:</strong><br />$page = '.$page.'<br />$pagesObj = <pre>'.print_r($pagesObj,1).'</pre>';
			}
		}
		break;
		
	case 'cart':
		$cart = geoCart::getInstance();		
		$cart->init();
		break;
		
	case 'sb_transaction':
		if (!geoPC::is_ent()){
			include_once(CLASSES_DIR."browse_ads.php");
			//set the defaults...
			if (!isset($_REQUEST['page']))$_REQUEST['page']=0; //make default page = 0
			if (!isset($_REQUEST['b']) || !is_numeric($_REQUEST['b']))$_REQUEST['b']=0;
			$browse = new Browse_ads($user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"]);
			$browse->main();
			break;
		}
		
		if (!$user_id){
			//must be logged in to view this page!
  			if (!$auth)
  			{
	  			include_once(CLASSES_DIR."authenticate_class.php");
				$auth = new Auth();
  			}
			$parts = array();
			foreach ($_GET as $key => $val){
				$parts[] = $key.'*is*'.$val;
			}
			$encoded = implode('*and*',$parts);
  			$auth->login_form($db, "", "", $encoded);
  			break;
		}
		//Seller Buyer transaction
		
		$html = geoSellerBuyer::callDisplay('sellerBuyerPage');
		
		$site = Singleton::getInstance('geoSite');
		$site->body .= $html;
		$site->page_id = 10201;
		
		$site->display_page();
		break;
	
	case 'tag':
		//browse a tag
		require_once(CLASSES_DIR."browse_tag.php");
		$page = (isset($_GET['page']))? (int)$_GET['page'] : 0;
		$tag = (isset($_GET['tag']))? trim($_GET['tag']) : '';
		$browse_type = (isset($_GET['c']))? (int)$_GET['c'] : 0;
		
		$browse = new Browse_tag($tag, $page);
		
		$browse->browseTag($browse_type);
		
		break;
	
	case 1:
		//place new listing - redirect to adding to cart
		if (!geoMaster::is('auctions')) {
			header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart&action=new&main_type=classified");
		} else if (!geoMaster::is('classifieds')) {
			header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart&action=new&main_type=auction");
		} else {
			header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart");
		}
		break;
		
	case 7:
		//renew/upgrade - redirect to add to cart
		header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart&action=new&main_type=listing_renew_upgrade&listing_id=".intval($_GET['b'])."&r=".intval($_GET['r']));
		break;
		
	case 24:
		//subscription - redirect to add to cart
		header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart&action=new&main_type=subscription");
		break;
		
	case 29:
		//Add to balance - redirect to add to cart
		header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart&action=new&main_type=account_balance");
		break;
	case 98:
		//end sell process - redirect to cancel from cart
		header("Location: ".$db->get_site_setting('classifieds_url')."?a=cart&action=cancel");
		break;
		
	case 200:
		//store--front  - redirect to add to store --front
		header("Location: ".geoFilter::getBaseHref().$db->get_site_setting('classifieds_file_name')."?a=cart&action=new&main_type=storefront_subscription");
		break;
		
	case 2:
		//display a classified
		if (isset($get_execution_time)&&$get_execution_time) get_end_time($starttime);
		include_once(CLASSES_DIR."browse_display_ad.php");
		if (isset($_REQUEST['amp;b'])&&!isset($_REQUEST['b'])) $_REQUEST['b']=$_REQUEST['amp;b'];
		if (!isset($_REQUEST['b']) || $_REQUEST['b']==0) $_REQUEST['b']=-1;
		$_REQUEST['page'] = isset($_REQUEST['page'])? $_REQUEST['page'] : 0;
		
		$browse = new Display_ad($_REQUEST["page"], $_REQUEST["b"]);
		//$browse->classified_close($db);
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"]))) {
			if ($browse->classified_exists($_REQUEST["b"])) {
				if (!$browse->display_classified($_REQUEST["b"]))
					$browse->browse_error();
			} else {
				$browse->browse_error();
			}
		} else {
			//display the home page
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"]);
			if (!$browse->main())
				$browse->browse_error();
		}
		if ($get_execution_time) get_end_time($starttime);
		break;
			
	case 3:
		//send communication
		if (!$user_id) {
			//force log-in
			if (!$auth) {
	  			include_once(CLASSES_DIR."authenticate_class.php");
				$auth = new Auth();
  			}
  			$parts = array ('a*is*3');
  			if (isset($_GET['b'])) {
  				$parts[] = 'b*is*'.$_GET['b'];
  			}
  			if (isset($_GET['c'])) {
  				$parts[] = 'c*is*'.$_GET['c'];
  			}
			$auth->login_form($db, "", "", implode('*and*',$parts));
			//break early, don't do rest of stuff
			break;
		}
		include_once(CLASSES_DIR."user_management_communications.php");
		$communication = new User_management_communications();
		if (($_REQUEST["b"]) && ($_REQUEST["d"])) {
			if (!$communication->send_communication($_REQUEST["b"],$_REQUEST["d"])) {
				$communication->site_error();
			} else {
				if (!$communication->communication_success())
					$communication->site_error();
			}
		} elseif (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"]))) {
			//display the home page
			if (!$communication->send_communication_form($_REQUEST["b"],$_REQUEST["c"]))
				$communication->site_error();
		} else {
			$communication->site_error();
		}
		break;

	case 4:
		//user management
		
		if (!$user_id) {
			//no user id
  			if (!$auth)
  			{
	  			include_once(CLASSES_DIR."authenticate_class.php");
				$auth = new Auth();
  			}
  			if ($_GET['b'] == 18 && is_numeric($_GET['invoiceId']) && $_GET['invoiceId']) {
  				//special case for invoice view, take them to view the invoice after they log in, this way links
  				//from e-mail work.
  				$vars = array ('a' => 4, 'b' => 18, 'invoiceId' => intval($_GET['invoiceId']));
  				//But do not send full $_GET as param, that way any extra params are not included, to prevent
  				//any possibility for XSS attacks.
  				$encoded = Auth::generateEncodedVars($vars);
  			} else if ($_GET['b'] == 8 && $_GET['c'] == 1) {
  				//communications
  				//special case for communication view, take them to view the communication they log in, this way links
  				//from e-mail work.
  				$vars = array ('a' => 4, 'b' => 8, 'c' => 1, 'd' => intval($_GET['d']));
  				//But do not send full $_GET as param, that way any extra params are not included, to prevent
  				//any possibility for XSS attacks.
  				$encoded = Auth::generateEncodedVars($vars);
  			} else {
  				//After login, just take them to the my account home page, to prevent any possible XSS related attacks.
  				$encoded = 'a*is*4';
  			}
			$auth->login_form($db, "", "", $encoded);
			break;
		}
		//b is the secondary switch within user management
		
		$_REQUEST['b'] = isset($_REQUEST['b']) ? $_REQUEST['b'] : 0;
		switch ($_REQUEST["b"]) {
			case 1:
				//show current ads
				include_once(CLASSES_DIR."user_management_current_ads.php");
				$_REQUEST['page'] = (isset($_REQUEST['page']))? $_REQUEST['page']: '';
				$user_management = new User_management_current_ads($db,$language_id,$user_id, $_REQUEST['page'], $product_configuration);
								
				if (isset($_REQUEST['bump_id'])) {
					//reset the start date on this listing
					if(!$user_management->bump_listing($_REQUEST['bump_id'])) {
						$user_management->site_error();
					}
				}
				

				if (!$user_management->list_current_ads($db))
					$user_management->site_error();
				break;
				
			case 2:
				//show past ads
				include_once(CLASSES_DIR."user_management_expired_ads.php");
				$user_management = new User_management_expired_ads($db,$language_id,$user_id,$_REQUEST['page'],$product_configuration);
				
				if (($_REQUEST["d"]) && (is_numeric($_REQUEST["d"]))) {
					// "delete" an expired ad (hide from client)
				
					//ask user for removal reason
					if (!$user_management->verify_remove_expired_ad($db,$_REQUEST["d"]))
						$user_management->site_error();
				} else if (($_REQUEST["c"]) && ($_REQUEST["z"])) {
					if (!$user_management->hide_expired_ad($db, $_REQUEST["c"]))
						$user_management->site_error();
					if (!$user_management->list_expired_ads($db))
						$user_management->site_error();
				} else if (($_REQUEST["c"]) && (is_numeric($_REQUEST["c"]))) {
					if (!$user_management->show_expired_ad($db,$_REQUEST["c"]))
						$user_management->site_error();
				}
				elseif (!$user_management->list_expired_ads($db))
					$user_management->site_error();
				break;
				
			case 3:
				//show user info
				include_once(CLASSES_DIR."user_management_information.php");
				$user_management = new User_management_information($db,$language_id,$user_id,$product_configuration);
				if (!$user_management->display_user_data($db))
					$user_management->site_error();
				break;
				
			case 4:
				//edit user info
				include_once(CLASSES_DIR."user_management_information.php");
				$user_management = new User_management_information($db,$language_id,$user_id,$product_configuration);
				if ($_REQUEST["c"]) {
					//update the current user_info
					if ($user_management->check_info($db,$_REQUEST["c"])) {
						$user_management->update_user($db,$_REQUEST["c"],$_REQUEST["d"]);
						
						if (!$user_management->display_user_data($db))
							$user_management->site_error();
					} elseif (!$user_management->edit_user_form($db,$_REQUEST["c"])) {
						$user_management->site_error();
					}
				} else {
					//show edit form
					if (!$user_management->edit_user_form($db))
						$user_management->site_error();
				}

				break;
				
			//case 5: //edit listing - moved to cart

			case 6:
				//delete a classified ad
				include_once(CLASSES_DIR."user_management_current_ads.php");
				$user_management = new User_management_current_ads($db,$language_id,$user_id,$_REQUEST['page'],$product_configuration);
				if (($_REQUEST["c"]) && ($_REQUEST["z"]))
				{
					//go ahead and delete
					if (!$user_management->remove_current_ad($db,$_REQUEST["c"]))
						$user_management->site_error();
					else
						if (!$user_management->verify_remove_success($db))
							$user_management->site_error();
				}
				elseif (is_numeric($_REQUEST["c"]))
				{
					if (!$user_management->verify_remove_current_ad($db,$_REQUEST["c"]))
						$user_management->site_error();
				}
				else
					$user_management->site_error();
				break;

			case 7:
				//communication configuration
				include_once(CLASSES_DIR."user_management_communications.php");
				$user_management = new User_management_communications();
				if (($_REQUEST["c"]) && ($_REQUEST["z"]))
				{
					//go ahead and delete
					if (!$user_management->update_communication_configuration($_REQUEST["c"]))
						$user_management->site_error();
					else
						if (!$user_management->list_communications($db))
							$user_management->site_error();
				}
				else
				{
					if (!$user_management->communications_configuration())
						$user_management->site_error();
				}
				break;

			case 8:
				//communication management and viewing
				include_once(CLASSES_DIR."user_management_communications.php");
				$user_management = new User_management_communications();
				switch ($_REQUEST["c"])
				{
					case 1:
						//view message
						if (is_numeric($_REQUEST["d"]))
						{
							if(!geoSession::getInstance()->getUserId()) {
								//user trying to view a private message, but isn't logged in
								//show login page
								include_once(CLASSES_DIR."authenticate_class.php");
								$auth = new Auth($db,$language_id,$product_configuration);
							} elseif (!$user_management->view_this_communication($db,$_REQUEST["d"])) {
								$user_management->site_error();
							}
						}
						else
							$user_management->site_error();
						break;
					case 2:
						//delete message
						if (is_numeric($_REQUEST["d"]))
						{
							if (!$user_management->delete_this_communication($db,$_REQUEST["d"]))
									$user_management->site_error();
							elseif (!$user_management->list_communications($db))
									$user_management->site_error();
						}
						else
							$user_management->site_error();
						break;
					default:
						//show communications list
						if (!$user_management->list_communications($db))
							$user_management->site_error();
						break;
				}
				break;

			case 9:
				//view, edit and update ad filters
				include_once(CLASSES_DIR."user_management_ad_filters.php");
				$user_management = new User_management_ad_filters($db,$language_id,$user_id,$product_configuration);
				switch ($_REQUEST["c"])
				{
					case 1:
						//ad filter form
						if (!$user_management->add_new_filter_form())
							$user_management->site_error();
						break;
					case 2:
						//delete filter
						if (is_numeric($_REQUEST["d"]))
						{
							if (!$user_management->delete_ad_filter($_REQUEST["d"]))
								$user_management->site_error();
							else
								if (!$user_management->display_all_ad_filters())
									$user_management->site_error();
						}
						else
							if (!$user_management->display_all_ad_filters())
								$user_management->site_error();
						break;
					case 3:
						//remove all ad filters
						if (!$user_management->clear_ad_filters())
							$user_management->site_error();
						else
							if (!$user_management->display_all_ad_filters())
								$user_management->site_error();
						break;
					case 4:
						//insert an ad filter
						if (!$user_management->insert_new_filter($_REQUEST["d"]))
							$user_management->site_error();
						else
							if (!$user_management->display_all_ad_filters())
								$user_management->site_error();
						break;
					default:
						//view all filters
						if (!$user_management->display_all_ad_filters())
							$user_management->site_error();
				}
				break;

			case 10:
				//view and delete favorite
				include_once(CLASSES_DIR."user_management_favorites.php");
				$user_management = new User_management_favorites();
				$user_management->expire_old_favorites($db);
				switch ($_REQUEST["c"])
				{
					case 1:
						//delete favorite
						if (is_numeric($_REQUEST["d"]))
						{
							if (!$user_management->delete_favorite($db,$_REQUEST["d"]))
								$user_management->site_error();
							elseif (!$user_management->display_all_favorites($db))
								$user_management->site_error();
						}
						elseif (!$user_management->display_all_favorites($db))
							$user_management->site_error();
						break;

					default:
						
						//view all filters
						if (!$user_management->display_all_favorites($db))
							$user_management->site_error();
				}
				break;

			case 11:
				//change sold sign display
				include_once(CLASSES_DIR."user_management_current_ads.php");
				$user_management = new User_management_current_ads($db,$language_id,$user_id,$_REQUEST['page'],$product_configuration);
				if (($_REQUEST["c"]) && (is_numeric($_REQUEST["c"])))
				{
					//change sold sign display status
					$user_management->change_sold_sign_status($db,$_REQUEST["c"]);
					if (!$user_management->list_current_ads($db))
						$user_management->site_error();
				}
				else
				{
					//display current ads
					if (!$user_management->list_current_ads($db))
						$user_management->site_error();
				}
				break;

			case 12:
				//display sellers sign
				include_once(CLASSES_DIR."user_management_current_ads.php");
				$user_management = new User_management_current_ads($db,$language_id,$user_id,$_REQUEST['page'],$product_configuration);
				if (($_REQUEST["c"]) && ($_REQUEST["d"]))
				{
					//display sellers sign
					$signs = geoAddon::getUtil('signs_flyers');
					if ($signs)
					{
						$signs->setSite($user_management);
						$signs->signsDisplay($_REQUEST["c"]);
					}
					else
					{
						header("Location: ".$home_page.$db->get_site_setting('classifieds_file_name')."index.php?a=4");
					}
				
				}
				elseif (($_REQUEST["c"]) && (is_numeric($_REQUEST["c"])))
				{
					//form for sellers sign
					$signs = geoAddon::getUtil('signs_flyers');
					if ($signs)
					{
						$signs->setSite($user_management);
						$signs->signsForm($_REQUEST["c"]);
					}
					else
					{
						header("Location: ".$home_page.$db->get_site_setting('classifieds_file_name')."?a=4");
					}
						
					
				}
				else
				{
					$signs = geoAddon::getUtil('signs_flyers');
					if ($signs) {
						$signs->setSite($user_management);
						if (!$signs->signs_and_flyers_list()) {
							$user_management->site_error();
						}
					} else {
						header("Location: ".$home_page.$db->get_site_setting('classifieds_file_name')."?a=4");
					}
				}
				break;

			case 13:
				//display sellers flyer
				include_once(CLASSES_DIR."user_management_current_ads.php");
				$user_management = new User_management_current_ads($db,$language_id,$user_id,$_REQUEST['page'],$product_configuration);
				if (($_REQUEST["c"]) && ($_REQUEST["d"]))
				{
					//display sellers flyer
					$signs = geoAddon::getUtil('signs_flyers');
					if ($signs)
					{
						$signs->setSite($user_management);
						$signs->flyersDisplay($_REQUEST["c"]);
					}
					else
					{
						header("Location: ".$home_page.$db->get_site_setting('classifieds_file_name')."?a=4");
					}
					
				}
				elseif (($_REQUEST["c"]) && (is_numeric($_REQUEST["c"])))
				{
					//display flyer form
					$signs = geoAddon::getUtil('signs_flyers');
					if ($signs)
					{
						$signs->setSite($user_management);
						$signs->flyersForm($_REQUEST["c"]);
					}
					else
					{
						header("Location: ".$home_page.$db->get_site_setting('classifieds_file_name')."?a=4");
					}
					
				}
				else
				{
					$signs = geoAddon::getUtil('signs_flyers');
					if ($signs) {
						$signs->setSite($user_management);
						if (!$signs->signs_and_flyers_list($db)) {
							$user_management->site_error();
						}
					} else {
						header("Location: ".$home_page.$db->get_site_setting('classifieds_file_name')."?a=4");
					}
				}
				break;
			case 14:
				//edit users filter
				//this is no longer a thing, as of Geo 7.3.0
				break;

			
			case 18:
				//show balance transactions
				if (!(geoPC::is_ent() || geoPC::is_premier() || geoPC::is_basic())) {
					return false;
				}
				include_once(CLASSES_DIR."user_management_balance_transactions.php");
				$user_management = new User_management_balance();
				
				if (isset($_GET['invoiceId']) && $_GET['invoiceId']) {
					$printFriendly = ($_GET['print'] == 1) ? true : false;
					if (!$user_management->showInvoice($_GET["invoiceId"], $printFriendly)) {
						$user_management->site_error();
					}
				} else {
					if (!$user_management->show_past_balance_transactions($_REQUEST["c"])) {
						$user_management->site_error();
					}
				}
				break;

			case 19:
				//show black listed buyers
				include_once(CLASSES_DIR."user_management_black_list_buyers.php");
				$blacklist_buyers = new Black_list_buyers($db,$language_id,$user_id,$product_configuration);
				switch(($_REQUEST["c"])){
					case 1:
						if(($_REQUEST["d"])){
							if (!$blacklist_buyers->list_search_blacklisted_buyers_results($db,$_REQUEST["d"]))
								$blacklist_buyers->site_error();
						}
						else
						{
							if (!$blacklist_buyers->list_blacklisted_buyers())
								$blacklist_buyers->site_error();
						}
						break;
					case 2:
						if(($_REQUEST["d"])){
							$blacklist_buyers->update_blacklisted_users($db,$_REQUEST["d"]);
							if(!$blacklist_buyers->list_blacklisted_buyers())
								$blacklist_buyers->site_error();
						}
						else
						{
							if(!$blacklist_buyers->list_blacklisted_buyers())
								$blacklist_buyers->site_error();
						}
						break;
					default:
						if (!$blacklist_buyers->list_blacklisted_buyers())
							$blacklist_buyers->site_error();

				}//switch
				break;

			case 20:
				//show invited listed buyers
				include_once(CLASSES_DIR."user_management_invited_list_buyers.php");
				$invitedlist_buyers = new Invited_list_buyers();
				switch(($_REQUEST["c"]))
				{
					case 1: //search for invited buyers
						if(($_REQUEST["d"]))
						{
							if (!$invitedlist_buyers->list_search_invited_buyers_results($db,$_REQUEST["d"]))
								$invitedlist_buyers->site_error();
						}
						else
						{
							if (!$invitedlist_buyers->list_invited_buyers($db))
								$invitedlist_buyers->site_error();
						}
						break;
					case 2: //add/update invited buyers
						if(($_REQUEST["d"]))
						{
							$invitedlist_buyers->update_invited_users($db,$_REQUEST["d"]);
							if(!$invitedlist_buyers->list_invited_buyers($db))
								$invitedlist_buyers->site_error();
						}
						else
						{
							if(!$invitedlist_buyers->list_invited_buyers($db))
								$invitedlist_buyers->site_error();
						}
						break;
					default:
						if (!$invitedlist_buyers->list_invited_buyers($db))
							$invitedlist_buyers->site_error();
				}//switch
				break;

			case 21:
				//view current bids
				//auctions you currently have bids on
				include_once(CLASSES_DIR."user_management_list_bids.php");
				$list_bids = new Auction_list_bids($db, $language_id, $user_id,$product_configuration);
				if (!$list_bids->list_auctions_with_your_bid())
					$list_bids->site_error();
				break;

			case 22:
				//view and leave feedback
				include_once(CLASSES_DIR."auction_feedback_class.php");
				$feedback = new Auction_feedback($db,$language_id,$user_id, $_REQUEST['page'], $product_configuration);
				switch ($_REQUEST["c"])
				{
					case 1:
						//list open feedback
						if (!$feedback->list_open_feedback($db,$user_id))
							$feedback->site_error();
						break;

					case 2:
						//feedback form
						if (($_REQUEST["d"]) && ($_REQUEST["e"]))
						{
							if ($feedback->check_feedback($db,$_REQUEST["d"],$user_id,$_REQUEST["e"]))
							{
								trigger_error('DEBUG FEEDBACK: Passed check_feedback');
								if (!$feedback->save_feedback($db,$_REQUEST["d"],$user_id,$_REQUEST["e"]))
								{
									trigger_error('DEBUG FEEDBACK: Failed save_feedback');
									if (!$feedback->leave_feedback($db,$user_id,$_REQUEST["d"],$_REQUEST["e"]))
										$feedback->site_error();
								}
								else
								{
									trigger_error('DEBUG FEEDBACK: Passed save_feedback');
									if (!$feedback->feedback_thank_you($db))
										$feedback->site_error();
								}
							}
							else
							{
								trigger_error('DEBUG FEEDBACK: Failed check_feedback');
								if (!$feedback->leave_feedback($db,$user_id,$_REQUEST["d"],$_REQUEST["e"]))
									$feedback->site_error();
							}
						}
						elseif ($_REQUEST["d"])
						{
							if (!$feedback->leave_feedback($db,$user_id,$_REQUEST["d"],0,$_REQUEST["f"]))
								$feedback->site_error();
						}
						else
						{
							if (!$feedback->feedback_home())
								$feedback->site_error();
						}
						break;

					case 3:
						//review feedback
						if($_REQUEST["z"])
						{
							// This one is for showing feedback to other users
							if(!$feedback->feedback_about_user($_REQUEST["z"],$_REQUEST["p"]))
								$feedback->site_error();
						}
						elseif (!$feedback->feedback_about_user($user_id,$_REQUEST["p"]))
							$feedback->site_error();
						break;

					default:
						//feedback home
						if (!$feedback->feedback_home())
							$feedback->site_error();
				}
				break;
				
			case 24:
				//cancel subscription
				include_once(CLASSES_DIR."user_management_information.php");
				$user_management = new User_management_information();
				$recurringId = (int)$_GET['recurring_id'];
				$user_management->cancelSubscription($recurringId);
				
				break;

			default:
				//Account Settings
				//display user management home
				include_once(CLASSES_DIR."user_management_home.php");
				$user_management = new User_management_home();
				if (!$user_management->menu())
					$user_management->site_error();
		}		
		break;

	case 5:
		//display a category
		//b will contain the category id
		include_once(CLASSES_DIR."browse_ads.php");
		if (!isset($_REQUEST['page']))$_REQUEST['page']=0;
		
		$browse = new Browse_ads($user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"]);
		//$browse->classified_close($db);
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
			if (!isset($_REQUEST['c']))$_REQUEST['c']=0;
			if (!$browse->browseCategory($_REQUEST["c"]))
			{
				$browse->browse_error();
			}
		}
		else
		{
			if (!$browse->main())
			{
				$browse->browse_error();
			}
		}
		break;

	case 6:
		//display sellers other ads
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
			include_once(CLASSES_DIR."browse_displays_sellers_ads.php");
			$browse = new Browse_display_sellers_ads($db,$user_id,$language_id,0,$_REQUEST["page"],$_REQUEST["b"],$product_configuration);
			if (!$browse->browse($db))
				$browse->browse_error();
		}
		else
		{
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"]);
			//$browse->classified_close($db);
			if (!$browse->main())
			{
				$browse->browse_error();
			}
		}
		break;

	case 8:
		if(!geoPC::is_ent()) {
			break;
		}
		//display a featured ad pics in this category
		//b will contain the category id
		include_once(CLASSES_DIR."browse_featured_pic_ads.php");
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
			$browse = new Browse_featured_pic_ads($db,$user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"],$filter_id,$state_filter,$zip_filter,$zip_distance_filter,$product_configuration);
		else
			$browse = new Browse_featured_pic_ads($db,$user_id,$language_id,0,$_REQUEST["page"],$filter_id,$state_filter,$zip_filter,$zip_distance_filter,$product_configuration);
		if (!$browse->browse($db))
			$browse->browse_error();
		break;

	case 9:
		if(!geoPC::is_ent()) {
			break;
		}
		//display a featured ad text only in this category
		//b will contain the category id
		include_once(CLASSES_DIR."browse_featured_text_ads.php");
		$browse = new Browse_featured_text_ads($db,$user_id,$language_id,(($_REQUEST['b'])?$_REQUEST['b']:0),$_REQUEST["page"],$filter_id,$state_filter,$zip_filter,$zip_distance_filter,$product_configuration);
		if (!$browse->browse()) {
			$browse->browse_error();
		}
		break;

	case 10:
		//login
  		if (!isset($auth) || !is_object($auth)) {
	 		include_once(CLASSES_DIR."authenticate_class.php");
			$auth = new Auth();
  		}
		if (!$user_id) {
			
			if($_GET['login_trackback'] == 1) {
				//want to go back to referring page once login complete -- store trackback url in a cookie
				setcookie('login_trackback', geoString::toDB($_SERVER['HTTP_REFERER']), 0, '/');
			}
			
			if (isset($_POST["b"]) && (is_array($_POST["b"])) && isset($_POST['b']['sessionId'])) {
				$authorized = $auth->login($_POST['b']);
				if ($authorized) {
					//redirect to storefront page they are attached to if set
					$share_fees = geoAddon::getUtil('share_fees');
					if (($share_fees) && ($share_fees->active) && ($share_fees->post_login_redirect)) {
						$user_to_redirect_to = $share_fees->getUserAttachedTo($authorized);
						if ($user_to_redirect_to !=0 ) {
							$redirect_to_storefront = $share_fees->checkStorefrontUse($user_to_redirect_to);
						}						
					}
					Auth::redirectAfterLogin($user_to_redirect_to,$redirect_to_storefront);
				} else {
					$auth->login_form($db,$_REQUEST["b"]['username'], $_REQUEST["b"]['password'], urldecode($_REQUEST["c"]));
				}
			} else if (isset($_POST["b"]) && is_array($_POST["b"])) {
				$auth->validate_login_form($_POST['b'], urldecode($_REQUEST["c"]));
			} else {
				$auth->login_form($db,0,0,urldecode($_REQUEST["c"]));
			}
		} else {
			$auth->already_logged_in($db);
		}
		break;

	case 11:
		//display the newest ads only in this category
		//b will contain the category id
		include_once(CLASSES_DIR."browse_newest_ads.php");
		$b = (isset($_REQUEST['b']))? (int)$_REQUEST['b'] : 0;
		$browse = new Browse_newest_ads($b,$_REQUEST["page"],$_REQUEST["c"]);
		
		if (!$browse->browse($_REQUEST["c"],$_REQUEST["d"])) {
				$browse->browse_error();
		}
		break;

	case 12:
		//notify a friend
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
			if (($_REQUEST["b"]) && ($_REQUEST["c"]))
			{
				include_once(CLASSES_DIR."browse_notify_friend.php");
				$browse = new Notify_friend($user_id,$language_id,0,0,0,0,$product_configuration);
				if ($browse->verify_notify_friend($_REQUEST["b"],$_REQUEST["c"]))
				{
					if ($browse->notify_friend_($_REQUEST["b"],$_REQUEST["c"]))
						$browse->notify_success($_REQUEST["b"]);
					else
						$browse->site_error();
				}
				elseif (!$browse->notify_friend_form($_REQUEST["b"]))
					$browse->site_error();
			}
			elseif ($_REQUEST["b"])
			{
				include_once(CLASSES_DIR."browse_notify_friend.php");
				$browse = new Notify_friend($user_id,$language_id,0,0,0,0,$product_configuration);
				$browse->notify_friend_form($_REQUEST["b"]);
			}
			else
			{
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,0,$_REQUEST["page"]);
				if (!$browse->main())
				{
					$browse->browse_error();
				}
			}
		}
		else
		{
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,$_REQUEST["page"]);
			if (!$browse->main())
			{
				$browse->browse_error();
			}
		}
		break;

	case 13:
		//send a message to seller
		/**
		 * DUPLICATE any changes on aff.php!
		 */
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
			if (($_REQUEST["b"]) && ($_REQUEST["c"]))
			{
				include_once(CLASSES_DIR."browse_notify_seller.php");
				$browse = new Notify_seller($user_id,$language_id,0,0,0,0,$product_configuration);
				if ($browse->notify_seller_($_REQUEST["b"],$_REQUEST["c"]))
					$browse->notify_seller_success($_REQUEST["b"]);
				elseif (!$browse->send_a_message_to_seller_form($_REQUEST["b"]))
					$browse->site_error();
			}
			elseif ($_REQUEST["b"])
			{
				include_once(CLASSES_DIR."browse_notify_seller.php");
				$browse = new Notify_seller($user_id,$language_id,0,0,0,0,$product_configuration);
				$browse->send_a_message_to_seller_form($_REQUEST["b"]);
			}
			else
			{
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,0,$_REQUEST["page"]);
				if (!$browse->main())
				{
					$browse->browse_error();
				}
			}
		}
		else
		{
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,$_REQUEST["page"]);
			if (!$browse->main())
			{
				$browse->browse_error();
			}
		}
		break;

	case 14:
		//display a classified in print friendly format
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"]))) {
			require_once(CLASSES_DIR."browse_display_ad.php");
			$browse = new Display_ad ($_REQUEST["page"],$_REQUEST["b"]);
			if ($browse->classified_exists($_REQUEST["b"])) {
				if (!$browse->display_classified($_REQUEST["b"], false, false, true, null, true))
					$browse->browse_error();
			} else {
				$browse->browse_error();
			}
		} else {
			//display the home page
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,$_REQUEST["page"]);
			if (!$browse->main())
				$browse->browse_error();
		}
		break;

	case 15:
		//display a classified images in full size format
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
			include_once(CLASSES_DIR."browse_display_ad_full_images.php");
			$browse = new Display_ad_full_images($db,$user_id,$language_id,0,$_REQUEST["page"],$_REQUEST["b"],$affiliate_id,$product_configuration);
			if (!$browse->display_classified_full_images($_REQUEST["b"]))
			{
				$browse->browse_error();
			}
		}
		else
		{
			//display the home page
			include_once(CLASSES_DIR."browse_ads.php");
			$browse_ads = new Browse_ads($user_id,$language_id,0,$_REQUEST["page"]);
			if (!$browse_ads->main())
				$browse_ads->browse_error($db);
		}
		break;

	case 17:
		//log this user out
		$session->logOut();
		unset ($_GET['a']);
		unset ($_REQUEST['a']);
		
		header("Location: ".$db->get_site_setting('classifieds_file_name'));
		break;
	case 18:
		//lost password
  		if (!isset($auth))
  		{
  			include_once(CLASSES_DIR."authenticate_class.php");
			$auth = new Auth();
		}
		if (!$user_id)
		{
			if (isset($_REQUEST["b"]) && (is_array($_REQUEST["b"])))
			{
				$success = $auth->lostpassword($db,$_REQUEST["b"]) ? 1 : 0;
				$auth->lostpassword_form($db,$success);
			}
			else
			{
				//show the lost password form
				$auth->lostpassword_form($db);
			}
		}
		else
		{
			//show the edit userdata form
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,0);
			if (!$browse->main())
				$browse->browse_error();
		}
		break;

	case 19:
		//search
		if (!isset($search_the_classifieds) || !is_object($search_the_classifieds)) {
			include_once(CLASSES_DIR."search_class.php");
			$search_the_classifieds = new Search_classifieds($db,$language_id,$user_id,$_REQUEST["c"],$filter_id,$state_filter,$zip_filter,$zip_distance_filter,$product_configuration);
		}
		if($_REQUEST["b"] && $_REQUEST["order"])
		{
			if(!$search_the_classifieds->Search($_REQUEST["b"], $_REQUEST["change"], $_REQUEST["order"]))
			{
				if (!$search_the_classifieds->search_form($db,$_REQUEST["b"]))
					$search_the_classifieds->site_error();
			}
		}
		elseif($_REQUEST["b"])
		{
			if(!$search_the_classifieds->Search($_REQUEST["b"], $_REQUEST["change"]))
			{
				if (!$search_the_classifieds->search_form($db,$_REQUEST["b"]))
					$search_the_classifieds->site_error();
			}
		}
		else
		{
			if (!$search_the_classifieds->search_form($db, $_REQUEST["b"], $_REQUEST["change"]))
				$search_the_classifieds->site_error();
		}
		break;

	case 20:
		//add to favorites
		if ($user_id)
		{
			if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
			{
				include_once(CLASSES_DIR."user_management_favorites.php");
				$user_management = new User_management_favorites();
				if (!$user_management->insert_favorite($db,$_REQUEST["b"]))
					$user_management->site_error();
				elseif (!$user_management->display_all_favorites($db))
					$user_management->site_error();
			}
			else
			{
				//show the edit userdata form
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,0,0);
				if (!$browse->main())
					$browse->browse_error();
			}
		}
		else
		{
  			if (!$auth)
  			{
	  			include_once(CLASSES_DIR."authenticate_class.php");
				$auth = new Auth();
  			}
			if (($_REQUEST["b"]) && (is_array($_REQUEST["b"])))
				$auth->login_form($db,0,0,"a=20&amp;b=".$_REQUEST["b"]);
			else
				$auth->login_form($db);
		}
		break;

	case 21:
		//choose languages
		$site = new geoSite();
		//show the edit userdata form
		if (!$site->choose_language_form()) {
			$site->site_error();			
		}
		break;

	case 22:
		//extra page
		$site = new geoSite();
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"]))) {
			if (!$site->extra_page($db,$_REQUEST["b"])) {
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,0,0);
				if (!$browse->main()) {
					$browse->browse_error();					
				}
			}
		} else {
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,0);
			if (!$browse->main()) {
				$browse->browse_error();				
			}
		}
		break;
	case 25:
		//display sellers within a category
		//b will contain the category id
		include_once(CLASSES_DIR."browse_sellers.php");
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
			$browse = new Browse_sellers($db,$user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"],0,$product_configuration);
		else
			$browse = new Browse_sellers($db,$user_id,$language_id,0,$_REQUEST["page"],0, $product_configuration);
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
			if (!$browse->browse($db,$_REQUEST["c"]))
			{
				$browse->browse_error();
			}
		}
		else
		{
			if (!$browse->main())
			{
				$browse->browse_error();
			}
		}
		break;

	case 26:
		if(geoPC::is_ent()) {
			//classified voting
			include_once(CLASSES_DIR."browse_vote.php");
			$vote = new Browse_vote($db,$user_id,$language_id,0,$_REQUEST["page"],$_REQUEST["b"],0,$product_configuration);
			if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])) && ($_REQUEST["c"]) && (is_array($_REQUEST["c"])))
			{
				//collect the vote and go back to classified id
				if (!$vote->collect_vote($_REQUEST["b"],$_REQUEST["c"]))
				{
					//show the voting form
					if (!$vote->voting_form($db,$_REQUEST["b"]))
					{
						include_once(CLASSES_DIR."browse_display_ad.php");
						$browse = new Display_ad($_REQUEST["page"],$_REQUEST["b"]);
						if ($browse->classified_exists($_REQUEST["b"]))
						{
							if (!$browse->display_classified($_REQUEST["b"]))
								$browse->browse_error();
						}
						else
						{
							$browse->browse_error();
						}
					}
				}
			}
			elseif (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
			{
				//show the voting form
				if (!$vote->voting_form($db,$_REQUEST["b"]))
				{
					include_once(CLASSES_DIR."browse_display_ad.php");
					$browse = new Display_ad($_REQUEST["page"],$_REQUEST["b"]);
					if ($browse->classified_exists($_REQUEST["b"]))
					{
						if (!$browse->display_classified($_REQUEST["b"]))
							$browse->browse_error();
					}
					else
					{
						$browse->browse_error();
					}
				}
			}
			else
			{
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,0,0);
				if (!$browse->main())
					$browse->browse_error();
			}
		}
		break;

	case 27:
		if(geoPC::is_ent()) {
			//classified vote browsing
			include_once(CLASSES_DIR."browse_vote.php");
			$vote = new Browse_vote($db,$user_id,$language_id,0,$_REQUEST["page"],$_REQUEST["b"],0,$product_configuration);
			if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
			{
				if($_REQUEST['d'] && is_numeric($_REQUEST['d'])) {
					//delete this vote, then return to the browse votes page
					$vote->delete_vote($_REQUEST['d']);
				}
				
				//collect the vote and go back to classified id
				if (!$vote->browse_vote_comments($_REQUEST["b"]))
				{
					$vote->site_error();
				}
			}
			else
			{
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($language_id,0,0);
				if (!$browse->main())
					$browse->browse_error();
			}
		}
		break;

	case 28:
		//display extra page
		include_once(CLASSES_DIR.'extra_pages.php');
		$extra_page = new extra_page($db, $_REQUEST['b'], $language_id, $user_id, $product_configuration);
		if ($debug) echo $_REQUEST["b"]." is request b in extra page<BR>\n";
		if ($extra_page->page_id)
		{
			$extra_page->setup_filters($filter_id, $state_filter, $zip_filter, $zip_distance_filter);

			//collect the vote and go back to classified id
			if (!$extra_page->display_extra_page($db))
			{
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,0,0);
				if (!$browse->main())
					$browse->browse_error();
			}
		}
		else
		{
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,0);
			if (!$browse->main())
				$browse->browse_error();
		}
		break;
		
	//case 31: //Case removed as it is not being used any more, probably never used.	

	case 1029:
		//bid on auction
		
		require_once(CLASSES_DIR."auction_bid_class.php");
		$bid = new Auction_bid ($_REQUEST["b"]);
		
		if ($user_id) {
			$aff_id = ($_REQUEST["aff"]) ? (int)$_REQUEST["aff"] : 0;
			
			if (($_REQUEST["b"]) && ($_REQUEST["c"] && ($_REQUEST["e"] == "verified"))) {
				//process the bid
				if (!$bid->process_bid($_REQUEST["c"],$_REQUEST["d"],$aff_id)) {
					$bid->bid_error();
				} else {
					$bid->bid_successful($db,$aff_id);
				}
			} elseif (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"]))) {
				//show the bid form
				if (!$bid->bid_setup($_REQUEST["d"],$_REQUEST["c"],$aff_id)) {
					$bid->bid_error();
				}
			} else {
				//show the error
				//*** DOESN'T EXIST IN ANY PRODUCT ***
				include_once(CLASSES_DIR."browse_ads.php");
				$browse = new Browse_ads($user_id,$language_id,$_REQUEST["b"],$_REQUEST["page"]);
				if (!$browse->main())
					$browse->browse_error();
			}
		} else {
  			if (!$auth) {
	  			include_once(CLASSES_DIR."authenticate_class.php");
				$auth = new Auth();
  			}
			$auth->login_form($db,0,0,0,$_REQUEST["c"]);
		}
		break;

	case 1030:
		//user feedback
		if ($debug)
		{
			echo "TOP OF CASE 1030<br>";
			echo $_REQUEST["d"]." is d<br>\n";
			echo $_REQUEST["b"]." is b<br>\n";
			echo $_REQUEST["p"]." is p<br>\n";
		}
		include_once(CLASSES_DIR."auction_feedback_class.php");
		if (!isset($_REQUEST['page'])) $_REQUEST['page']=0;
		$feedback = new Auction_feedback($db,$language_id,$user_id, $_REQUEST['page'], $product_configuration);
		if ($_REQUEST["d"])
		{
			if (!isset($_REQUEST['p'])) $_REQUEST['p']=1;
			$feedback->feedback_about_user($_REQUEST["d"],$_REQUEST["b"],$_REQUEST["p"]);
		}
		else
		{
			//back to main browse
			include_once(CLASSES_DIR."browse_ads.php");
			$browse = new Browse_ads($user_id,$language_id,0,0);
			if (!$browse->main())
				$browse->browse_error();
		}
		break;

	case 1031:
		//displays bid history
		include_once(CLASSES_DIR."auction_bid_class.php");
		$bid = new Auction_bid($_REQUEST["b"]);
		$aff_id = ($_REQUEST["aff"]) ? (int)$_REQUEST["aff"] : 0;
		if (($_REQUEST["b"]) && (is_numeric($_REQUEST["b"])))
		{
		  if(!$bid->get_bid_history($db,$_REQUEST["b"],$aff_id))
			{
				$bid->bid_error();
			}
		}
		else
			$bid->bid_error();
		break;

		

	case 99:
		//this is the admin
		//trying to delete a listing
		include_once(CLASSES_DIR."browse_ads.php");
		$browse = new Browse_ads($user_id,$language_id,$_REQUEST["c"],0);
		if ($user_id == 1  || geoAddon::triggerDisplay('auth_listing_delete',null,geoAddon::NOT_NULL))
		{
			if ($_REQUEST["b"])
			{
				if ($browse->admin_delete_classified($db,$_REQUEST["b"]))
				{
					if (!$browse->main())
						$browse->site_error();
				}
				else
				{
					$browse->site_error();
				}
			}
			else
			{
				if (!$browse->main())
					$browse->site_error();
			}
		}
		else
		{
			if (!$browse->main())
				$browse->site_error();
		}
		break;
	default:
		if (isset($_GET['a']) && !geoView::getInstance()->isRendered()) {
			//invalid link?  someone may have made a wrong link, or someone is
			//just making links up... Anyways this is an invalid link.
			header ('HTTP/1.0 404 Not Found', 404);
			//TODO: create a built-in 404 page
			echo '<meta http-equiv="refresh" content="5;URL='.$db->get_site_setting('classifieds_url').'">';
			echo '404 Not Found';
			break;
		}
		include_once(CLASSES_DIR."browse_ads.php");
		//set the defaults...
		
		$browse = new Browse_ads($user_id,$language_id);
		$browse->main();
		
		break;

} //end of switch ($_REQUEST["a"])

//finish things up
require GEO_BASE_DIR . 'app_bottom.php';
