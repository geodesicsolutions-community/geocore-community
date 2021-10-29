<?php 
//user_management_current_ads.php
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
## ##    16.02.1-19-g1167771
## 
##################################

class User_management_current_ads extends geoSite
{
	var $debug_remove_ad = 0;
	var $debug_current = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	function User_management_current_ads ($db,$language_id, $classified_user_id=0, $page=0, $product_configuration=0)
	{
		parent::__construct();
		$page = (int)$page;
		$this->page_result = ($page) ? $page : 1;
	}
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	public function parseQuery($q)
	{
		//un-special char it
		$q = geoString::specialCharsDecode($q);
		//careful, $q is not html encoded anymore!
		
		$ors = array();
		if (is_numeric($q)) {
			//see if it is listing ID
			$ors[] = "`id` = ".intval($q);
		}
		//title will be special chared
		$title = geoString::specialChars($q);
		
		$search = array ('%','_');
		$replace = array ('\%','\_');
		
		$ors[] = "`title` like '%".str_replace($search,$replace,geoString::toDB($title))."%'";
		//also search category-specific questions
		$ors[] = "`search_text` like '%".str_replace($search,$replace,geoString::toDB($title))."%'";
		
		if(geoPC::is_ent()) {
			for($i = 1; $i <= 20; $i++) {
				$ors[] = "`optional_field_".$i."` like '%".str_replace($search,$replace,geoString::toDB($title))."%'";
			}
		}
		
		//description won't be special chared
		$ors[] = "`description` like '%".str_replace($search,$replace,geoString::toDB($q))."%'";
		
		
		
		return '('.implode (' OR ',$ors).')';
	}
	
	function list_current_ads ()
	{
		$this->page_id = 22;
		$this->get_text();
		if (!$this->db->get_site_setting('number_of_active_ads_to_display')){
			//if number_of_active_ads_to_display is not set yet, set it from the setting
			//number_of_ads_to_display.
			$this->db->set_site_setting('number_of_active_ads_to_display',$this->db->get_site_setting('number_of_ads_to_display'));
		}
		if (!$this->userid)
		{
			//no user id
			$this->error_message = $this->data_missing_error_message;
			return false;
		}
		$view = geoView::getInstance();
		$tpl = new geoTemplate('system','user_management');
	  	
		$q = (isset($_GET['q']))? trim($_GET['q']): '';
		$whereClauses = array ();
		
		$whereClauses[] = "`seller` = ".intval($this->userid);
		
		$whereClauses[] = "`live` = 1";
		
		if ($q) {
			$whereClauses[] = $this->parseQuery($q);
			$view->q = $q;
		}
		
		$sql = "SELECT * FROM ".geoTables::classifieds_table." WHERE ".implode(' AND ',$whereClauses)." ORDER BY `date` DESC LIMIT ";
		if($this->page_result != 1){
			$sql .= (($this->page_result-1) * $this->db->get_site_setting('number_of_active_ads_to_display')).", ";
		}
		$sql .= $this->db->get_site_setting('number_of_active_ads_to_display');
		
		$view->allow_copying_new_listing = $this->db->get_site_setting('allow_copying_new_listing');
	  	$view->file_name = $this->db->get_site_setting('classifieds_file_name');
	  	$view->ssl_url = ($this->db->get_site_setting('use_ssl_in_sell_process'))? $this->db->get_site_setting('classifieds_ssl_url'): $this->db->get_site_setting('classifieds_file_name');
	  	$view->days_to_renew = $this->db->get_site_setting('days_to_renew') * 86400;
	  	$view->shifted_time = geoUtil::time();
	  	$date_format = $this->db->get_site_setting('entry_date_configuration');
	  	$bump_feature = $this->db->get_site_setting('bump_feature');
	  		  	
	  	$view->date_format = $date_format;
	  	$view->sold_image = (geoPC::is_ent() && $this->messages[500798])? geoTemplate::getUrl('',$this->messages[500798]): '';

		$view->is_ca = $view->bothListingTypes = (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? true : false;
		$view->is_a = geoMaster::is('auctions');
		$view->is_e = geoPC::is_ent();
		$listings = $this->db->GetAll($sql);
		//set up logic for if to show each part
		$this->get_ad_configuration();
		

		//get reference to sharing addon, if it's enabled
		$sharing = geoAddon::getUtil('sharing');
		
		$force_edit = ($this->userid == 1) ? true : geoAddon::triggerDisplay('auth_listing_edit',null,geoAddon::NOT_NULL);
		$force_delete = ($this->userid == 1) ? true : geoAddon::triggerDisplay('auth_listing_delete',null,geoAddon::NOT_NULL);
		$pricePlans = array();
		foreach ($listings as $key => $listing){
			//go through each one, and detect if we should show the renew and/or upgrade link for each one.
			$listings[$key]['show_renew_link'] = 0; //default to no
			$listings[$key]['show_upgrade_link'] = 0; //default to no
			$listings[$key]['show_edit_link'] = 0; //default to no
			$listings[$key]['show_remove_link'] = 0; //default to no
			$listings[$key]['bump_access'] = 0; //default to no
			
			$listings[$key]['thumbnail'] = geoImage::display_thumbnail($listing['id'], 0, 0, 1, 0, 'aff', 0, true);
			
			$listings[$key]['addon_buttons'] = geoAddon::triggerDisplay('current_listings_add_action_button', array('listingId' => $listing['id']));

			
			//see if we can renew
			if ($listing['item_type'] == 1){
				//show edit and delete button for all classifieds
				$listings[$key]['show_delete_link'] = 1;
				$listings[$key]['show_edit_link'] = 1;
				if($listing['ends'] && !geoListing::isRecurring($listing['id'])) {
					//listings without an 'ends' value have unlimited duration and may not be renewed
					//recurring listings automatically renew, and may not be manually renewed! 
					$renew_cutoff = ($listing['ends'] - ($this->db->get_site_setting('days_to_renew') * 86400));
					$renew_postcutoff = ($listing['ends'] + ($this->db->get_site_setting('days_to_renew') * 86400));
					if (($this->db->get_site_setting('days_to_renew')) && (geoUtil::time() > $renew_cutoff) && (geoUtil::time() < $renew_postcutoff)){
						$listings[$key]['show_renew_link'] = 1; //can renew for this listing
					}
				}
			} else {
				//if auction, only show edit/delete if criteria is met
				$bidCount = (int)geoListing::bidCount($listing['id']);
				if($force_edit || (($this->db->get_site_setting('edit_begin') == 0) && ($bidCount == 0)) || ($listing['buy_now_only'] && $this->db->get_site_setting('edit_begin_bno') == 1)){
					$listings[$key]['show_edit_link'] = 1;
				}
				if($force_delete || ($this->db->get_site_setting('admin_only_removes_auctions') == 0 && ((($this->db->get_site_setting('edit_begin') == 0) && ($bidCount == 0)) || ($listing['buy_now_only'] && $this->db->get_site_setting('edit_begin_bno') == 1)))){
					$listings[$key]['show_delete_link'] = 1;
				}
				if ($listing['price_applies']=='item') {
					$bids = $this->db->Execute("SELECT * FROM ".geoTables::bid_table." WHERE `auction_id`=? AND `buy_now_bid`=1 ORDER BY `time_of_bid` DESC",
							array($listing['id']));
					$listings[$key]['bids'] = array();
					
					//figure out additional fees
					$additional_fees = geoListing::getAuctionAdditionalFees($listing['id']);
					
					$listings[$key]['additional_fees'] = ($additional_fees)? $additional_fees['formatted']['total'] : '';
					$add = ($additional_fees)? $additional_fees['raw']['total'] : 0;
					foreach ($bids as $bid) {
						//add user info
						$userInfo = geoUser::getUser($bid['bidder']);
						$bid['bidder_info'] = $userInfo->toArray();
						
						$price = $bid['bid']+$add;
						
						$bid['price_per'] = geoString::displayPrice($price, $listing['precurrency'], $listing['postcurrency']);
						$bid['total_due'] = geoString::displayPrice($price*$bid['quantity'], $listing['precurrency'], $listing['postcurrency']);
						
						$listings[$key]['bids'][] = $bid;
						unset($userInfo);
					}
					unset($bids);
				}
			}
			
			$recurringId = $this->db->GetOne("SELECT `recurring_id` FROM ".geoTables::listing_subscription." WHERE `listing_id` = ?", array($listing['id']));
			if($recurringId) {
				//this listing is on a recurring subscription. It cannot be "renewed," regardless of what happened above
				$listings[$key]['show_renew_link'] = 0;
				//TODO: show some kind of "subscription status" page?
			}
			
			if ($bump_feature) {
				//bump feature to allow start date reset is on
				//check to see if the bump feature can be used on this listing
				if ((($bump_feature * 86400) + $listing['date']) < geoUtil::time()) {
					//display the bump link for this listing
					$listings[$key]['bump_access'] = 1;
				}
			}
			
			//see if we can upgrade
			if($this->db->get_site_setting('days_can_upgrade') && geoPC::is_ent())
			{
				$attention_getters = geoAddon::getUtil('attention_getters'); //util object if attention getters addon is enabled, false otherwise
				$pricePlanId = $listing['price_plan_id'];
				if (!geoPlanItem::isValidPricePlan($pricePlanId)) {
					$pricePlanId = geoPlanItem::getDefaultPricePlan($listing['seller']);
				}
				if (!isset($pricePlans[$pricePlanId])) {
					$pp_sql = "SELECT * FROM ".geoTables::price_plans_table." WHERE `price_plan_id` = ".$pricePlanId;
					$pricePlans[$pricePlanId] = $this->db->GetRow($pp_sql);
				}
				$pricePlan = $pricePlans[$pricePlanId];
				$chargingPerPicture = ($pricePlan["charge_per_picture"] > 0) ? true : false;
				$planItem = geoPlanItem::getPlanItem('images',$pricePlanId,$listing['category']);
				$canUpgradePhotos = ($listing['image'] < $planItem->get('max_uploads',20)) ? true : false;
				
				//check offsite videos
				$planItem = geoPlanItem::getPlanItem('offsite_videos',$pricePlanId,$listing['category']);
				$canUpgradeVideos = $planItem->get('costPerVideo')>0 && ($listing['offsite_videos_purchased'] < $planItem->get('offsite_video_slots',0));
				
				//check for additional regions
				$planItem = geoPlanItem::getPlanItem('additional_regions',$pricePlanId,$listing['category']);
				$canUpgradeRegions = $planItem->get('cost')>0 && $listing['additional_regions_purchased'] < $planItem->get('max',0);
				
				$upgrade_cutoff = ($listing['date'] + ($this->db->get_site_setting('days_can_upgrade') * 86400));
				
				if (($this->db->get_site_setting('days_can_upgrade') && (geoUtil::time() < $upgrade_cutoff)))
				{	
					if (($this->db->get_site_setting('use_bolding_feature') && $listing['bolding'] == 0 && $pricePlan['use_bolding']) ||
						($this->db->get_site_setting('use_better_placement_feature') && $listing['better_placement'] == 0 && $pricePlan['use_better_placement']) ||
						($this->db->get_site_setting('use_featured_feature') && $listing['featured_ad'] == 0 && $pricePlan['use_featured_ads']) ||
						($this->db->get_site_setting('use_featured_feature_2') && $listing['featured_ad_2'] == 0 && $pricePlan['use_featured_ads_level_2']) ||
						($this->db->get_site_setting('use_featured_feature_3') && $listing['featured_ad_3'] == 0 && $pricePlan['use_featured_ads_level_3']) ||
						($this->db->get_site_setting('use_featured_feature_4') && $listing['featured_ad_4'] == 0 && $pricePlan['use_featured_ads_level_4']) ||
						($this->db->get_site_setting('use_featured_feature_5') && $listing['featured_ad_5'] == 0 && $pricePlan['use_featured_ads_level_5']) ||
						($attention_getters && $this->db->get_site_setting('use_attention_getters') && $listing['attention_getter'] == 0 && $pricePlan['use_attention_getters']) ||
						($chargingPerPicture && $canUpgradePhotos) ||
						$canUpgradeVideos || $canUpgradeRegions)
					{

						$listings[$key]['show_upgrade_link'] = 1;//can upgrade for this listing.
					}
				}
			}
			
			//get the number of favorites for this listing
			$listings[$key]['favorited'] = $this->db->GetOne("SELECT COUNT(user_id) FROM ".geoTables::favorites_table." WHERE classified_id = ? OR auction_id = ?", array($listing['id'], $listing['id']));
		}
		$view->listings = $listings;
		
		// Get the number of ads
		$sql = "SELECT count(id) as number_listings FROM ".geoTables::classifieds_table." WHERE ".implode(' AND ',$whereClauses);
		$total = $this->db->GetRow($sql);
		$total_returned = (isset($total['number_listings']))? $total['number_listings']: 0;
		$pagination_txt = '';
		if ($this->db->get_site_setting('number_of_active_ads_to_display') < $total_returned) {
			$view->show_pagination = true;
			$totalPages = ceil($total_returned / $this->db->get_site_setting('number_of_active_ads_to_display'));
			$qUrl = ($q)? '&amp;q='.urlencode($q):'';
			$pageUrl = $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=1{$qUrl}&amp;page=";
			$view->pagination = geoPagination::getHTML($totalPages, $this->page_result, $pageUrl, 'browsing_result_page_Links');
		} else {
			$view->show_pagination = false;
		}
		
		//get listings waiting for renewal
		$sql = "SELECT oi.`id` as order_item_id FROM ".geoTables::order_item." as oi, ".geoTables::order." as o 
			WHERE o.`buyer` = ? AND 
			oi.order = o.id AND
			oi.type in ('classified', 'auction', 'listing_renew_upgrade') AND
			oi.status = 'pending' AND
			o.status in ('pending', 'active', 'pending_admin')";
		$all = $this->db->GetAll($sql, array($this->userid));
		$pending = array();
		$i = 0;
		foreach ($all as $row) {
			$item = geoOrderItem::getOrderItem($row['order_item_id']);
			if (!$item) {
				//something wrong with order item
				continue;
			}
			$listing_id = $item->get('listing_id');
			$listing = geoListing::getListing($listing_id);
			if (!$listing || $listing->live) {
				continue;
			}
			$pending[$i]['title'] = geoString::fromDB($listing->title);
			$pending[$i]['id'] = $listing->id;
			
			if ($item->get('renew_upgrade') == 1) {
				$pending[$i]['upgrade_icon'] = $this->messages[834];
			} else if ($item->get('renew_upgrade') == 2) {
				$pending[$i]['upgrade_icon'] = $this->messages[835];
			}
			$pending[$i]['description'] = geoFilter::listingShortenDescription(geoFilter::listingDescription($listing->description, true),100);
			
			//get the full cost
			$cost = $item->getCost();
			$order = $item->getOrder();
			if ($order) {
				$allItems = $order->getItem();
				foreach ($allItems as $child) {
					if ($child && $child->getParent() && $child->getParent()->getId() == $item->getId()) {
						//child of this
						$cost += $child->getCost();
					}
				}
			}
			
			$pending[$i]['amount'] = geoString::displayPrice($cost);
			$i++;
		}
		$view->pending = $pending;
		
		geoAddon::triggerUpdate('current_listings_end');

		$view->setBodyTpl('current_ads/list.tpl','','user_management');
		$this->display_page();
		return true;
	} //end of function list_current_ads

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function remove_current_ad($db,$classified_info=0)
	{
		
		if (($classified_info) && ($this->userid) && ($classified_info["id"] != "") && ($classified_info["id"] != 0))
		{
			if(geoListing::isRecurring($classified_id)) {
				$this->cancelRecurringListing($classified_id);
			}
			
			$sql = "select * from ".$this->classifieds_table." where id = ".(int)$classified_info["id"]." and seller = ".$this->userid;
			$remove_result = $this->db->Execute($sql);
			if ($this->debug_remove_ad) echo $sql." is the query<br />\n";
			if (!$remove_result)
			{
				//echo $sql."<br />";
				$this->error_message = urldecode($this->messages[81]);
				return false;
			}
			elseif ($remove_result->RecordCount() == 1)
			{
				$show = $remove_result->FetchNextObject();
				//check to make sure user can remove this ad.
				if ($show->ITEM_TYPE == 2 && $this->userid != 1 && ($this->db->get_site_setting('admin_only_removes_auctions')  && ((($this->db->get_site_setting('edit_begin') == 0) && ($listing['current_bid'] == 0.00)) || ($listing['buy_now_only'] == 1 && $this->db->get_site_setting('edit_begin_bno'))) )) {
					//user is not admin, and only admin can remove auctions, so not allowed to remove auction.
					return false;
				}
		
				$listing = geoListing::getListing($classified_info["id"]); // so that we can get category data the new way
				
				$sql = "REPLACE ".$this->classifieds_expired_table."
					(id,seller,title,date,description,category, 
					duration,location_zip,ends,search_text,ad_ended,reason_ad_ended,viewed,
					bolding,better_placement,featured_ad,precurrency,price,postcurrency,
					business_type,optional_field_1,optional_field_2,optional_field_3,optional_field_4,optional_field_5,
					optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10,
					optional_field_11,optional_field_12,optional_field_13,optional_field_14,optional_field_15,
					optional_field_16,optional_field_17,optional_field_18,optional_field_19,optional_field_20,phone,phone2,fax,email,
					url_link_1,url_link_2,url_link_3,item_type";
				if($show->ITEM_TYPE==2)
				{
					$sql .= ",auction_type,final_fee,final_price,high_bidder";
				}
				$sql .= ")
					VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,
							?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,
							?,?";
				
				//get text
				$this->page_id = 22;
				$this->get_text();
				
				$info = array(
					$show->ID.'',
					$show->SELLER.'',
					$show->TITLE.'',
					$show->DATE.'',
					$show->DESCRIPTION.'',
					$listing->category.'',
					$show->DURATION.'',
					$show->LOCATION_ZIP.'',
					geoUtil::time().'',
					urlencode($show->SEARCH_TEXT).'',
					geoUtil::time().'',
					$this->messages[501663].$classified_info["reason_for_removal"],
					$show->VIEWED.'',
					$show->BOLDING.'',
					$show->BETTER_PLACEMENT.'',
					$show->FEATURED_AD.'',
					$show->PRECURRENCY.'',
					$show->PRICE.'',
					$show->POSTCURRENCY.'',
					$show->BUSINESS_TYPE.'',
					$show->OPTIONAL_FIELD_1.'',
					$show->OPTIONAL_FIELD_2.'',
					$show->OPTIONAL_FIELD_3.'',
					$show->OPTIONAL_FIELD_4.'',
					$show->OPTIONAL_FIELD_5.'',
					$show->OPTIONAL_FIELD_6.'',
					$show->OPTIONAL_FIELD_7.'',
					$show->OPTIONAL_FIELD_8.'',
					$show->OPTIONAL_FIELD_9.'',
					$show->OPTIONAL_FIELD_10.'',
					$show->OPTIONAL_FIELD_11.'',
					$show->OPTIONAL_FIELD_12.'',
					$show->OPTIONAL_FIELD_13.'',
					$show->OPTIONAL_FIELD_14.'',
					$show->OPTIONAL_FIELD_15.'',
					$show->OPTIONAL_FIELD_16.'',
					$show->OPTIONAL_FIELD_17.'',
					$show->OPTIONAL_FIELD_18.'',
					$show->OPTIONAL_FIELD_19.'',
					$show->OPTIONAL_FIELD_20.'',
					$show->PHONE.'',
					$show->PHONE2.'',
					$show->FAX.'',
					$show->EMAIL.'',
					$show->URL_LINK_1.'',
					$show->URL_LINK_2.'',
					$show->URL_LINK_3.'',
					$show->ITEM_TYPE.''		
				);
				
				if($show->ITEM_TYPE==2)
				{
					$sql .= ",?,?,?,?";
					$info[] = $show->AUCTION_TYPE.'';
					$info[] = $show->FINAL_FEE.'';
					$info[] = $show->FINAL_PRICE.'';
					$info[] = $show->HIGH_BIDDER.'';
				}
				$sql .= ")";

				$insert_expired_result = $this->db->Execute($sql, $info);
				if ($this->debug_remove_ad) echo $sql." is the query<br />\n";
				if (!$insert_expired_result)
				{
					if ($this->debug_remove_ad) echo $sql." is the query<br />\n";
					$this->error_message = urldecode($this->messages[81]);
					return false;
				}
				
				if (!geoListing::remove($show->ID, true)) {
					$this->error_message = urldecode($this->messages[81]);
					return false;
				}
				geoCategory::updateListingCount($listing->category);
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	} //end of function remove_current_ad

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function verify_remove_current_ad($db,$classified_id=0)
	{
		$classified_id = (int)$classified_id;
		if ((!$classified_id) || (!$this->userid)) {
			return false;
		}
		
		$this->page_id = 36;
		$tpl = new geoTemplate('system','user_management');
		$msgs = $this->db->get_text(true, $this->page_id);
		 
		$tpl->assign('messages', $msgs);
		
		$sql = "select * from ".geoTables::classifieds_table." where id = ? and seller = ?";
		$remove_result = $this->db->Execute($sql, array($classified_id, $this->userid));
		if (!$remove_result) {
			$this->error_message = $msgs[81];
			return false;
		}
		elseif ($remove_result->RecordCount() == 1)
		{
			$tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=6");
			$tpl->assign('classifiedId', $classified_id);
			$tpl->assign('currentAdsLink', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=1");
			$this->body = $tpl->fetch('current_ads/verify_remove.tpl');
			$this->display_page();
			return true;
		}
		return false;
		
	} //end of function verify_remove_current_ad

	public function cancelRecurringListing($listingId)
	{
		if(!$listingId || !geoListing::isRecurring($listingId)) {
			return false;
		}
		//get the recurring billing object for this listing by first finding its recurring ID
		$db = DataAccess::getInstance();
		$recurringId = $db->GetOne("SELECT `recurring_id` FROM ".geoTables::listing_subscription." WHERE `listing_id` = ?", array($listingId));
		if($recurringId) {
			$recurringObj = geoRecurringBilling::getRecurringBilling($recurringId);
			//call the cancel function and let the recurringBilling system handle the heavy lifting
			$recurringObj->cancel('Canceled by user');
		}
	}
	
	
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function verify_remove_success()
	{
		$this->page_id = 36;
		$this->get_text();
		$tpl = new geoTemplate('system','user_management');
		
		$tpl->assign('currentAdsLink', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=1");
		
		$this->body = $tpl->fetch('current_ads/verify_remove_success.tpl');
		$this->display_page();
		return true;
	} //end of function verify_remove_success

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function change_sold_sign_status($db,$classified_id)
	{
		if ((!$classified_id) || (!$this->userid)) {
			return false;
		}
		$listing = geoListing::getListing($classified_id);

		// Check for it not being a classified ad
		if($listing->item_type != 1) {
			return false;
		}
		$msgs = $this->db->get_text(true, 59);
		if (($listing->seller == $this->userid) && strlen($msgs[500798]) >0) {
			$listing->sold_displayed = ($listing->sold_displayed)? 0 : 1;
			
			geoAddon::triggerUpdate('notify_sold_sign_status_changed', array('listingId' => $classified_id, 'new_status' => $listing->sold_displayed));
			
			return true;
		} else {
			return false;
		}
	}
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function bump_listing($listing_id=0)
	{
		$listing_id = (int)$listing_id;
		
		if ((!$listing_id) || (!$this->userid)) {
			return false;
		}

		$db = DataAccess::getInstance();
		
		$minBumpDays = $db->get_site_setting('bump_feature');
		$listing = geoListing::getListing($listing_id);
		if($listing->date + ($minBumpDays * 86400) >= geoUtil::time()) {
			//cannot bump this listing yet. UI doesn't allow access here, so probably a direct link
			return false;
		}
		
		if($listing->seller != $this->userid) {
			//this is not your listing!
			return false;
		}
		
		//the actual bump is just setting the listing's start time to "now"
		$listing->date = geoUtil::time();
		
		return true;
	}
}
