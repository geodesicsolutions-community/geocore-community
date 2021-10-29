<?php 
//user_management_information.php
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
## ##    17.10.0-20-g9ae085a
## 
##################################

require_once(CLASSES_DIR . 'site_class.php');

class User_management_information extends geoSite
{
	var $registration_configuration;
	var $debug_info = 0;
	var $search_array;
	var $field_tpl;
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	public function __construct ()
	{
		parent::__construct();

		$this->get_registration_configuration_data();
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_user_data($db)
	{
		$this->page_id = 37;
		$this->get_text();
		$tpl = new geoTemplate('system','user_management');
		$tpl->assign('msgs', $this->messages);
		
		if (!$this->userid) return false;	
		
		$user_data = $this->get_user_data();
		if (!$user_data) return false;
			
		//display this users information
						
		//Keep us from repeating the same structure over and over and over...
		
		$data = array();
		$i = 'username';
		
		$data[$i]['label'] = $this->messages[556];
		$data[$i]['value'] = $user_data->USERNAME;
		
		if (($this->registration_configuration->USE_REGISTRATION_FIRSTNAME_FIELD) || ($this->registration_configuration->USE_REGISTRATION_LASTNAME_FIELD)) {
			$i = 'fullname';
			$data[$i]['label'] = $this->messages[$user_data->USERNAME_LABEL];
			$data[$i]['value'] = $user_data->FULL_NAME;
		}
		$i = 'email';
		$data[$i]['label'] = $this->messages[$user_data->EMAIL_LABEL];
		$data[$i]['value'] = $user_data->EMAIL;

		if ($this->registration_configuration->USE_REGISTRATION_EMAIL2_FIELD) {
			$i = 'email2';
			$data[$i]['label'] = $this->messages[$user_data->EMAIL2_LABEL];
			$data[$i]['value'] = $user_data->EMAIL2;
		}

		if ($this->registration_configuration->USE_REGISTRATION_BUSINESS_TYPE_FIELD) {
			$i = 'business_type';
			$data[$i]['label'] = $this->messages[561];
			$data[$i]['value'] = $this->messages[$user_data->BUSINESS_TYPE_LABEL];
		}

		if ($this->registration_configuration->USE_REGISTRATION_COMPANY_NAME_FIELD && strlen(trim($user_data->COMPANY_NAME)) > 0) {
			$i = 'company_name';
			$data[$i]['label'] = $this->messages[560];
			$data[$i]['value'] = stripslashes($user_data->COMPANY_NAME);
		}

		$levels = geoRegion::getLevelsForOverrides();
		$userRegions = geoRegion::getRegionsForUser($this->userid);
		
		$citySwitch = ($levels['city']) ? $this->db->get_site_setting('registration_use_region_level_'.$levels['city']) : $this->registration_configuration->USE_REGISTRATION_CITY_FIELD;
		
		if (($this->registration_configuration->USE_REGISTRATION_ADDRESS_FIELD)
		|| ($citySwitch)
		|| ($this->registration_configuration->USE_REGISTRATION_ZIP_FIELD)
		|| ($this->db->get_site_setting('registration_use_region_level_'.$levels['country']))
		|| ($this->db->get_site_setting('registration_use_region_level_'.$levels['state']))) {
			$address_display = 0;
			$vals = array();
			if ($this->registration_configuration->USE_REGISTRATION_ADDRESS_FIELD) {
				$vals[] = stripslashes($user_data->ADDRESS);
			}
			if (($this->registration_configuration->USE_REGISTRATION_ADDRESS2_FIELD) && (strlen(trim($user_data->ADDRESS_2)) > 0)) {
				$vals[] = stripslashes($user_data->ADDRESS_2);
			}
			$this_line = '';
			$mainLoc = array();
			if($citySwitch) {
				if($levels['city'] && $this->db->get_site_setting('registration_use_region_level_'.$levels['city'])) {
					$mainLoc[] .= geoRegion::getNameForRegion($userRegions[$levels['city']]);
				} elseif(!$levels['city'] && $this->registration_configuration->USE_REGISTRATION_CITY_FIELD) {
					$mainLoc[] .= stripslashes($user_data->CITY);
				}
			}
			if($this->db->get_site_setting('registration_use_region_level_'.$levels['state'])) {
				$mainLoc[] .= geoRegion::getNameForRegion($userRegions[$levels['state']]);
			}
			if($this->db->get_site_setting('registration_use_region_level_'.$levels['country'])) {
				$mainLoc[] .= geoRegion::getNameForRegion($userRegions[$levels['country']]);
			}
			$this_line = implode(', ',$mainLoc);
			
			if ($this->registration_configuration->USE_REGISTRATION_ZIP_FIELD) {
				$this_line .= " ".stripslashes($user_data->ZIP);
			}
			if (strlen($this_line) > 0){
				$vals[] = $this_line;
			}
			$val = implode('<br />',$vals);
			unset($vals);
			$i = 'address';
			$data[$i]['label'] = $this->messages[562];
			$data[$i]['value'] = $val;
		}

		if (($this->registration_configuration->USE_REGISTRATION_PHONE_FIELD) && (strlen(trim($user_data->PHONE)) > 0)) {
			$i = 'phone';
			$data[$i]['label'] = geoString::fromDB($this->messages[563]);
			$data[$i]['value'] = stripslashes($user_data->PHONE);
		}
		if (($this->registration_configuration->USE_REGISTRATION_PHONE2_FIELD) && (strlen(trim($user_data->PHONE2)) > 0)) {
			$i = 'phone2';
			$data[$i]['label'] = geoString::fromDB($this->messages[564]);
			$data[$i]['value'] = stripslashes($user_data->PHONE2);
		}

		if (($this->registration_configuration->USE_REGISTRATION_FAX_FIELD) && (strlen(trim($user_data->FAX)) > 0)) {
			$i = 'fax';
			$data[$i]['label'] = geoString::fromDB($this->messages[565]);
			$data[$i]['value'] = stripslashes($user_data->FAX);
		}
			
		if (($this->registration_configuration->USE_REGISTRATION_URL_FIELD) && (strlen(trim($user_data->URL)) > 0)) {
			$i = 'url';
			$data[$i]['label'] = geoString::fromDB($this->messages[566]);
			$data[$i]['value'] = stripslashes($user_data->URL);
		}
		if (geoPC::is_ent()) {
			$txt_id = 1241;
			for ($opts=1; $opts<=10; $opts++){
				//go through each reg optional field
				$use_reg_opt_field = "USE_REGISTRATION_OPTIONAL_{$opts}_FIELD";
				$opt_field = "OPTIONAL_FIELD_{$opts}";
				if ($this->registration_configuration->$use_reg_opt_field) {
					$label = geoString::fromDB($this->messages[$txt_id]);
					$val = stripslashes($user_data->$opt_field);
					$i = 'optional_field_'.$opts;
					$data[$i]['label'] = $label;
					$data[$i]['value'] = $val;
				}
				$txt_id++;
			}
		}
		$i = 'date_joined';
		$data[$i]['label'] = geoString::fromDB($this->messages[567]);
		$data[$i]['value'] = date($this->db->get_site_setting('entry_date_configuration'),$user_data->DATE_JOINED);
		
		$this->sql_query = "SELECT * FROM ".$this->user_groups_price_plans_table." WHERE id =?";
		$user_group_result = $this->db->Execute($this->sql_query,array($this->userid));
			
		if (!$user_group_result) {
			return false;
		}
		$show_user_stuff = $user_group_result->FetchNextObject();

		$this->sql_query = "select * from ".$this->groups_table." where group_id = ".$show_user_stuff->GROUP_ID;
		$group_result = $this->db->Execute($this->sql_query);
		if (!$group_result) {
			//echo $this->sql_query."<br />";
			return false;
		}
		$group_stuff = $group_result->FetchNextObject();
		
			
			
		//Make a call to order items to display anything they need to on this page
		$itemData = geoOrderItem::callDisplay('User_management_information_display_user_data', null, 'array',null,true);
		if ($itemData) {
			foreach ($itemData as $item_name => $iData) {
				//prepend key with addon_ in case any addons have same name as something
				if (isset($iData['label']) || isset($iData['value'])) {
					//This is just a single entry...
					$data['addon_item_'.$item_name] = $iData;
					continue;
				}
				//This is an array of items to add
				foreach ($iData as $key => $entry) {
					//prepend with item name and key
					$data['addon_item_'.$item_name.'_'.$key] = $entry;
				}
			}
		}
		//make core event call so addons not using order items can add to page as well
		$addonData = geoAddon::triggerDisplay('User_management_information_display_user_data', null, geoAddon::ARRAY_ARRAY);
		if ($addonData) {
			foreach ($addonData as $addon_name => $iData) {
				//prepend key with addon_ in case any addons have same name as something
				if (isset($iData['label']) || isset($iData['value'])) {
					//This is just a single entry...
					$data['addon_core_'.$addon_name] = $iData;
					continue;
				}
				//This is an array of items to add
				foreach ($iData as $key => $entry) {
					//prepend with addon name and key
					$data['addon_core_'.$addon_name.'_'.$key] = $entry;
				}
			}
		}
		
		//check shared fee use
		$share_fees = geoAddon::getUtil('share_fees');
		if (($share_fees) && ($share_fees->active)) {
			$which_attachtype = $share_fees->checkAttachedorAttaching($this->userid);
			$tpl->assign('feeshare_attachment_type', $which_attachtype);
			$share_fee_text =& geoAddon::getText('geo_addons','share_fees');
			if (($which_attachtype == 1) && ($share_fees->use_attached_messages)) {
				//user attached to so display the shared message
				$data[$i]['label'] = geoString::fromDB($share_fee_text['share_fees_share_message_label']);
				$shared_message = $share_fees->GetAttachedMessage($this->userid);
				if (strlen(trim($shared_message)) == 0)
					$shared_message = geoString::fromDB($share_fee_text['no_share_fees_share_message']);
				$data[$i]['value'] = $shared_message;
			} elseif ($which_attachtype == 2) {
				//display the user this use is attached to here
				$attached_to_user_id = $share_fees->getUserAttachedTo ($this->userid);
				if ($attached_to_user_id != 0) {
					//display information of user attached to
					$i = 'feeshareattachment';
					$data[$i]['label'] = geoString::fromDB($share_fee_text['share_fees_share_user_attached_to_label']);
					$data[$i]['value'] = $share_fees->displayNameAttachedUser($attached_to_user_id);
				} else {
					//no user attachment
					//if attachment active display message that
					$i = 'feeshareattachment';
					$data[$i]['label'] = geoString::fromDB($share_fee_text['share_fees_share_user_attached_to_label']);
					$data[$i]['value'] = geoString::fromDB($share_fee_text['share_fees_share_user_not_attached']);
				}
			}
		}		
		
		$tpl->assign('data', $data);
		
		//edit info button
		$tpl->assign('editInfoLink', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=4");
			
		// Save default auctions and classifieds price plan id
		$class_price_plan = $group_stuff->PRICE_PLAN_ID;
		$auction_price_plan = $group_stuff->AUCTION_PRICE_PLAN_ID;
			
		// Price Plan information
			
		$sbInfo = '';
		$ppInfo = '';
		$showPricePlanInfo = ($group_stuff->RESTRICTIONS_BITMASK & 1) ? true : false;
		if(geoMaster::is('auctions')) {
			//auction price plan info
				
			if (geoPC::is_ent()) {
				//display seller/buyer settings
				$this->head_font_stuff .= geoSellerBuyer::callDisplay('displayUserDetailsHeader',array('price_plan_id' => $auction_price_plan, 'user_id' => $this->userid));
				$sb_html = geoSellerBuyer::callDisplay('displayUserDetails',array('price_plan_id' => $auction_price_plan, 'user_id' => $this->userid));
				if (strlen(trim($sb_html)) > 0){
					$sbInfo = $sb_html;
				}
			}
			if (geoMaster::is('site_fees') && $showPricePlanInfo) {
				//only show price plan info if charging for listings
				$ppInfo .= $this->display_price_plan_info($db, $auction_price_plan);
			}

		}
		if($showPricePlanInfo && geoMaster::is('site_fees') && geoMaster::is('classifieds')) {
			//classified price plan info
			$ppInfo .= $this->display_price_plan_info($db, $class_price_plan);
		}
		
		$tpl->assign('sellerBuyerInfo', $sbInfo);
		$tpl->assign('pricePlanInfo', $ppInfo);
			
		/**
		 * Addon core_ event:
		 * name: core_User_management_information_display_user_data_plan_information
		 * vars: array (this => Object) (this is the instance of class that called.
		 */
		$tpl->assign('addonPlanInfo', geoAddon::triggerDisplay('User_management_information_display_user_data_plan_information', array('this'=>$this,'user_data' =>$user_data)));
		
		$tpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name')."?a=4");

		$this->body = $tpl->fetch('information/user_data.tpl');
		$this->display_page();
		return true;
	} //end of function display_user_data
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	
	public function cancelSubscription($recurringId = 0)
	{
		if (!geoPC::is_ent()) {
			return;
		}
		$userId = (int)$this->userid;
		$recurringId = $recurringIdUse = (int)$recurringId;
		if (!$userId) {
			//should not get here, it should stop them in index.php file
			return;
		}
		$msgs = $this->db->get_text(true, 37);
		$tpl = new geoTemplate('system','user_management');
		$tpl->assign('recurringId', $recurringId);
		if (isset($_GET['confirm']) && $_GET['confirm']) {
			$tpl->assign('showConfirm', false);
			
			//do the cancelation
			//first figure out the recurring billing for the user
			if (!$recurringId) {
				//assume it's a subscription, get recurring that way.
				$sql = "SELECT `recurring_billing` FROM ".geoTables::user_subscriptions_table." WHERE `user_id`=?";
				$row = $this->db->GetRow($sql, array($userId));
				if (!$row || !$row['recurring_billing']) {
					//no subscription
					return;
				}
				$recurringIdUse = (int)$row['recurring_billing'];
			}
			$recurring = geoRecurringBilling::getRecurringBilling($recurringIdUse);
			if (!$recurring) {
				//can not cancel if could not get
				return;
			}
			//make sure recurring is valid
			if (!$recurring->getId() || $recurring->getUserId() != $userId || ($recurringId && $recurring->getId() != $recurringId)) {
				//invalid user or recurring ID
				return;
			}
			$result = $recurring->cancel($msgs[500737]);
			if (!$result) {
				//Oops!  See if there is a message
				$tpl->assign('failed', true);
				//if the gateway was smart, it would have set a user message giving more info.
				$failedMessage = $recurring->getUserMessage();
				if (!$failedMessage) {
					//but if not, use default error message
					
					$failedMessage = $msgs[500755];
				}
				$tpl->assign('failedMessage', $failedMessage);
			}
		} else {
			$tpl->assign('showConfirm',true);
		}
		$tpl->assign('accountInfoUrl', $this->db->get_site_setting('classifieds_file_name').'?a=4&amp;b=3');
		$tpl->assign('confirmCancelUrl', $this->db->get_site_setting('classifieds_file_name').'?a=4&amp;b=24&amp;confirm=1&amp;recurring_id='.$recurringId);
		echo $tpl->fetch('information/cancel_subscription.tpl');
	}
	
	function display_price_plan_info($db, $price_plan_id)
	{
		$this->sql_query = "select * from ".$this->price_plans_table." where price_plan_id = ".$price_plan_id;
		$price_plan_result = $this->db->Execute($this->sql_query);
		if (!$price_plan_result) {
			return false;
		} elseif ($price_plan_result->RecordCount() == 1) {
			$tpl = new geoTemplate('system','user_management');
			$tpl->assign('msgs', $this->messages);
			
			$base_price_plan = $price_plan_result->FetchNextObject();

			//echo $base_price_plan->TYPE_OF_BILLING." is type of billing<br />\n";
			//echo $credits->CREDIT_COUNT." is the credit count<br />\n";

			//current price plan
			if($base_price_plan->APPLIES_TO == 1) {
				$tpl->assign('pageTitle', $this->messages[730]);
				$tpl->assign('pageDescription', $this->messages[745]);
			} elseif($base_price_plan->APPLIES_TO == 2) {
				$tpl->assign('pageTitle', $this->messages[200006]);
				$tpl->assign('pageDescription', $this->messages[200007]);
			}
			

			$data = array();
			$i = 0; //index
			if ($base_price_plan->TYPE_OF_BILLING == 1) {
				//charged per ad
				
				$data[$i]['label'] = $this->messages[733];
				$data[$i]['value'] = $this->messages[732];
				$i++;
				
				//charge per listing
				$data[$i]['label'] = $this->messages[1419];
				
				if ($base_price_plan->CHARGE_PER_AD_TYPE == 0) {
					//flat fee per ad
					$display_amount = geoString::displayPrice($base_price_plan->CHARGE_PER_AD);
					
					$data[$i]['value'] = $display_amount;
					
				} elseif ($base_price_plan->CHARGE_PER_AD_TYPE == 1 && $base_price_plan->APPLIES_TO == 1) {
					//fee based on price field
					$data[$i]['value'] = $this->messages[1480];
				} elseif ($base_price_plan->CHARGE_PER_AD_TYPE == 2) {
					//fee based on length of ad
					$data[$i]['value'] = $this->messages[1481];
				}
				$i++;
				
			} elseif ($base_price_plan->TYPE_OF_BILLING == 2) {
				//charge by subscription -- display when expire
				$data[$i]['label'] = $this->messages[733];
				$data[$i]['value'] = $this->messages[731];
				$i++;
				
				$subscription = $this->get_user_subscription();
				$recurring = false;
				if ($subscription && geoPC::is_ent()) {
					$recurring = ($subscription['recurring_billing'])? geoRecurringBilling::getRecurringBilling($subscription['recurring_billing']) : false;
					if ($recurring && (!$recurring->getId() || $recurring->getItemType() != 'subscription' || $recurring->getUserId() != $this->userid)) {
						//recurring not for this item, or not valid
						$recurring = false;
						//unset the recurring billing column if it is not valid
						$sql = "UPDATE ".geoTables::user_subscriptions_table." SET `recurring_billing`=0 WHERE `user_id` = ".(int)$this->userid;
						$this->db->Execute($sql);
					}
					if ($recurring && $recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
						$data[$i]['label'] = $this->messages[500726];
						$duration = floor($recurring->getCycleDuration()/(60*60*24));
						$price = geoString::displayPrice($recurring->getPricePerCycle());
						$data[$i]['value'] = "$price {$this->messages[500727]} $duration {$this->messages[500728]}";
						$i++; 
						
						$data[$i]['label'] = $this->messages[500729];
						$data[$i]['value'] = date($this->db->get_site_setting('entry_date_configuration'), $subscription['subscription_expire']);
						$i++; 
					} else {
						$data[$i]['label'] = $this->messages[501621];
						$data[$i]['value'] = date($this->db->get_site_setting('entry_date_configuration'), $subscription['subscription_expire']);
						
						$i++;
					}
				}

				if ($recurring && $recurring->getStatus() != geoRecurringBilling::STATUS_CANCELED) {
					//Link to cancel
					$data[$i]['label'] = $this->messages[500730];
					$data[$i]['value'] = $this->messages[500731];
					
					$data[$i]['link'] = $this->db->get_site_setting('classifieds_file_name').'?a=4&amp;b=24&amp;recurring_id='.$recurring->getId();
					$data[$i]['linkClass'] = 'lightUpLink';
					$i++;
				} else {
					if ($this->db->get_site_setting('use_ssl_in_sell_process')) {
						$link = trim($this->db->get_site_setting('classifieds_ssl_url'));
					} else {
						$link = trim($this->db->get_site_setting('classifieds_file_name'));
					}
					$link .= "?a=24";
					//TODO: don't believe these two messages exist anymore. seems they used to be a "cancel subscription" link
					//is there any use for such a link? should they be put back in?
					$data[$i]['label'] = $this->messages[1649];
					$data[$i]['value'] = $this->messages[1650];
					$data[$i]['link'] = $link;
					$i++;
				}
				
				if ($subscription) {
					//get current live listing count
					$this->sql_query = "select COUNT(*) as total from ".geoTables::classifieds_table." where live = 1 and seller = ".$this->userid;
					$listing_count_result = $this->db->Execute($this->sql_query);
					if (!$listing_count_result) {
						//bad count
					} else {
						$show_current_listing_count = $listing_count_result->FetchRow();
						$data[$i]['label'] = $this->messages[500229];
						$data[$i]['value'] = $show_current_listing_count["total"];
						$i++;					
					}
					//display max number of listings allowed under subscription
					$data[$i]['label'] = $this->messages[500230];
					$data[$i]['value'] = $base_price_plan->MAX_ADS_ALLOWED;
					$i++;
				}
			}
			
			//show extra feature costs
			if ($base_price_plan->CHARGE_PER_PICTURE > 0) {
				//charge per picture
				$data[$i]['label'] = $this->messages[734];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->CHARGE_PER_PICTURE);
				if($base_price_plan->NUM_FREE_PICS > 0) {
					$data[$i]['value'] .= ' ('.$base_price_plan->NUM_FREE_PICS.$this->messages[500780].')';
				}
				$i++;
			}

			if (($this->db->get_site_setting('use_bolding_feature')) && ($base_price_plan->USE_BOLDING)) {
				//bolding
				$data[$i]['label'] = $this->messages[735];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->BOLDING_PRICE);
				$i++;
			}

			if (($this->db->get_site_setting('use_better_placement_feature')) && ($base_price_plan->USE_BETTER_PLACEMENT)) {
				//better placement
				$data[$i]['label'] = $this->messages[736];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->BETTER_PLACEMENT_CHARGE);
				$i++;
			}

			if (($this->db->get_site_setting('use_featured_feature')) && ($base_price_plan->USE_FEATURED_ADS)) {
				//featured ad
				$data[$i]['label'] = $this->messages[737];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->FEATURED_AD_PRICE);
				$i++;
			}

			if (($this->db->get_site_setting('use_featured_feature_2')) && ($base_price_plan->USE_FEATURED_ADS_LEVEL_2)) {
				//featured ad
				$data[$i]['label'] = $this->messages[2346];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->FEATURED_AD_PRICE_2);
				$i++;
			}

			if (($this->db->get_site_setting('use_featured_feature_3')) && ($base_price_plan->USE_FEATURED_ADS_LEVEL_3)) {
				//featured ad
				$data[$i]['label'] = $this->messages[2347];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->FEATURED_AD_PRICE_3);
				$i++;
			}

			if (($this->db->get_site_setting('use_featured_feature_4')) && ($base_price_plan->USE_FEATURED_ADS_LEVEL_4)) {
				//featured ad
				$data[$i]['label'] = $this->messages[2348];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->FEATURED_AD_PRICE_4);
				$i++;
			}

			if (($this->db->get_site_setting('use_featured_feature_5')) && ($base_price_plan->USE_FEATURED_ADS_LEVEL_5)) {
				//featured ad
				$data[$i]['label'] = $this->messages[2349];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->FEATURED_AD_PRICE_5);
				$i++;
			}

			$ag =& geoAddon::getUtil('attention_getters');
			
			if ($ag && ($this->db->get_site_setting('use_attention_getters')) && ($base_price_plan->USE_ATTENTION_GETTERS)) {
				//attention getters
				$data[$i]['label'] = $this->messages[744];
				$data[$i]['value'] = geoString::displayPrice($base_price_plan->ATTENTION_GETTER_PRICE);
				$i++;
			}
			$tpl->assign('data', $data);

			//check to see if final fees should be displayed...only when charging final fees
			if ($base_price_plan->CHARGE_PERCENTAGE_AT_AUCTION_END) {
				$this->sql_query = "select * from ".$this->final_fee_table." where price_plan_id = ".$base_price_plan->PRICE_PLAN_ID." order by low asc";
				$result = $this->db->Execute($this->sql_query);
				$tpl->assign('price_plan_id', $base_price_plan->PRICE_PLAN_ID);
				if (!$result) {
					return false;
				} elseif ($result->RecordCount() > 0) {
					$ffRows = array();
					for($r = 0; $show = $result->FetchRow(); $r++) {
						$ffRows[$r]['low'] = geoString::displayPrice($show['low']);
						$ffRows[$r]['high'] = ($show['high'] == 100000000) ? geoString::fromDB($this->messages[200122]) : geoString::displayPrice($show["high"]);
						$ffRows[$r]['charge'] = $show['charge'];
						$ffRows[$r]['fixed'] = geoString::displayPrice($show['charge_fixed']);
					}
					$tpl->assign('ffRows', $ffRows);
				}
			}

			//get subcategory pricing
			
			//can only be "inside" one item type's pagination at a time -- make sure this is the right one
			$planIsPaginated = (isset($_GET['plan']) && $_GET['plan'] == $price_plan_id) ? true : false;
			$resultsPerPage = 20;
			$currentPage = ($planIsPaginated) ? intval($_GET['page']) : 1;
			$firstRecord = ($currentPage-1) * $resultsPerPage;
			$limit = " LIMIT $firstRecord, $resultsPerPage ";
			
			
			$this->sql_query = "select * from ".geoTables::price_plans_categories_table." where price_plan_id = ".$price_plan_id." ORDER BY price_plan_id ASC $limit";
			$category_price_plan_result = $this->db->Execute($this->sql_query);
			if (!$category_price_plan_result) {
				return false;
			}
			$totalResults = $this->db->GetOne("SELECT COUNT(price_plan_id) FROM ".geoTables::price_plans_categories_table." WHERE price_plan_id = ?",array($price_plan_id));
			
			if($totalResults > $resultsPerPage) {
				//more than 20 cat-specific pricing schemes. Do pagination.
				$totalPages = ceil($totalResults / $resultsPerPage);
				$link = $this->db->get_site_setting('classifieds_file_name').'?a=4&amp;b=3&amp;plan='.$price_plan_id.'&amp;page=';
				$pagination = geoPagination::getHTML($totalPages, $currentPage, $link,'category_specific_pagination','#plan'.$price_plan_id);
				$tpl->assign('pagination', $pagination);
				$tpl->assign('plan', $price_plan_id);
			}
			
			$categories = array();
			for($cat = 0; $show_category = $category_price_plan_result->FetchNextObject(); $cat++) {
				$category_name = geoCategory::getName($show_category->CATEGORY_ID);
				$categories[$cat]['name'] = $category_name->CATEGORY_NAME.' '.$this->messages[740];
				
				$categories[$cat]['rows'] = array(); //rows of the display table
				$row = 0; //index
				if ($base_price_plan->TYPE_OF_BILLING == 1) {
					//charged per ad -- check for credits
					$categories[$cat]['rows'][$row]['label'] = $this->messages[733];
					$categories[$cat]['rows'][$row]['value'] = $this->messages[732];
					$row++;

					//charge per ad
					$categories[$cat]['rows'][$row]['label'] = $this->messages[1419];
					
					if ($show_category->CHARGE_PER_AD_TYPE == 0) {
						//flat fee per ad
						$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->CHARGE_PER_AD);
					} elseif ($show_category->CHARGE_PER_AD_TYPE == 1 && $base_price_plan->APPLIES_TO == 1) {
						//fee based on price field
						$categories[$cat]['rows'][$row]['value'] = $this->messages[1480];
					} elseif ($show_category->CHARGE_PER_AD_TYPE == 2) {
						//fee based on length of ad
						$categories[$cat]['rows'][$row]['value'] = $this->messages[1481];
					}
					
					$row++;
				} elseif ($base_price_plan->TYPE_OF_BILLING == 2) {
					//charge by subscription -- display when expire
					$categories[$cat]['rows'][$row]['label'] = $this->messages[733];
					$categories[$cat]['rows'][$row]['value'] = $this->messages[731];
					$row++;
				}
				if ($show_category->CHARGE_PER_PICTURE > 0) {
					//charge per picture
					$categories[$cat]['rows'][$row]['label'] = $this->messages[734];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->CHARGE_PER_PICTURE);
					$row++;
				}

				if ($this->db->get_site_setting('use_bolding_feature') && $show_category->USE_BOLDING) {
					//bolding
					$categories[$cat]['rows'][$row]['label'] = $this->messages[735];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->BOLDING_PRICE);
					$row++;
				}

				if ($this->db->get_site_setting('use_better_placement_feature') && $show_category->USE_BETTER_PLACEMENT) {
					//better placement
					$categories[$cat]['rows'][$row]['label'] = $this->messages[736];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->BETTER_PLACEMENT_CHARGE);
					$row++;
				}

				if ($this->db->get_site_setting('use_featured_feature') && $show_category->USE_FEATURED_ADS) {
					//featured ad
					$categories[$cat]['rows'][$row]['label'] = $this->messages[737];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->FEATURED_AD_PRICE);
					$row++;
				}

				if ($this->db->get_site_setting('use_featured_feature_2') && $show_category->USE_FEATURED_ADS_LEVEL_2) {
					//featured ad
					$categories[$cat]['rows'][$row]['label'] = $this->messages[2346];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->FEATURED_AD_PRICE_2);
					$row++;
				}

				if ($this->db->get_site_setting('use_featured_feature_3') && $show_category->USE_FEATURED_ADS_LEVEL_3) {
					//featured ad
					$categories[$cat]['rows'][$row]['label'] = $this->messages[2347];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->FEATURED_AD_PRICE_3);
					$row++;
				}

				if ($this->db->get_site_setting('use_featured_feature_4') && $show_category->USE_FEATURED_ADS_LEVEL_4) {
					//featured ad
					$categories[$cat]['rows'][$row]['label'] = $this->messages[2348];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->FEATURED_AD_PRICE_4);
					$row++;
				}

				if ($this->db->get_site_setting('use_featured_feature_5') && $show_category->USE_FEATURED_ADS_LEVEL_5) {
					//featured ad
					$categories[$cat]['rows'][$row]['label'] = $this->messages[2349];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->FEATURED_AD_PRICE_5);
					$row++;
				}

				if ($this->db->get_site_setting('use_attention_getters') && $show_category->USE_ATTENTION_GETTERS) {
					//attention getters
					$categories[$cat]['rows'][$row]['label'] = $this->messages[744];
					$categories[$cat]['rows'][$row]['value'] = geoString::displayPrice($show_category->ATTENTION_GETTER_PRICE);
					$row++;
				}
			}
			$tpl->assign('categories', $categories);
			
			return $tpl->fetch('information/price_plan_info.tpl');
		}
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function edit_user_form($db,$info=0)
	{
		$this->page_id = 38;
		$this->get_text();
		if (!$this->userid) {
			return false;
		}
		$user = geoUser::getUser(geoSession::getInstance()->getUserId()); //TODO: make function use this object
		$this->sql_query = "select * from ".$this->userdata_table." where id = ".$this->userid;
		
		$result = $this->db->Execute($this->sql_query);

		if (!$result || $result->RecordCount() != 1) {
			$this->site_error();
			return false;
		}
		
		$tpl = new geoTemplate('system','user_management');
		
		$show = $result->FetchNextObject();

		if (!$info) {
			$info = $user->toArray();
		}
		
		if($this->userid == 1) {
			//this is the main admin user, who cannot edit info here!
			$tpl->assign('errorAdminUser', true);
		}

		//get this users info and show the form
		$tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=4");
		$tpl->assign('show', $show);
		$tpl->assign('info', $info);
		$tpl->assign('error', $this->error);
		$tpl->assign('rc', $rc = $this->registration_configuration);
		
		if ($this->db->get_site_setting('info_edit_require_pass')) {
			//current password
			$tpl->assign('requirePass', true);
		}
		
		$tpl->assign('regionOverrides', geoRegion::getLevelsForOverrides());
		$prevalue = $_REQUEST['geoRegion_location']; //first, see if there's a value from a previous attempt at this form
		if(!$prevalue) {
			//if no previous value, load from userdata
			$prevalue = geoRegion::getRegionsForUser($this->userid);
		}
		$maxLocationDepth = 0;
		$styleAsRequired = false;
		for($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
			if($this->db->get_site_setting('registration_use_region_level_'.$r)) {
				if ($this->db->get_site_setting('registration_require_region_level_'.$r)) {
					$styleAsRequired = true;
				}
				if(!$maxLocationDepth) {
					$maxLocationDepth = $r;
				}
				//CHANGED: no "break" here so that we can find the "required" level even if low levels are used but not required
			}
		}
		$tpl->assign('regionSelector', geoRegion::regionSelector('geoRegion_location', $prevalue,$maxLocationDepth, $styleAsRequired));

		$optionals = array();
		for($i=1; $i<=10; $i++) {
			//registration optional fields
			$name = "REGISTRATION_OPTIONAL_".$i;
			$use = "USE_".$name."_FIELD";
			$require = "REQUIRE_{$name}_FIELD";
			$type = $name."_FIELD_TYPE";
			$other = $name."_OTHER_BOX";
			$maxlen = "OPTIONAL_".$i."_MAXLENGTH";
			if($rc->$use) {
				$optionals[$i]['label'] = geoString::fromDB($this->messages[1250+$i]);
				$optionals[$i]['info'] = $info["optional_field_".$i];
				$optionals[$i]['required'] = $rc->$require;
				$optionals[$i]['maxlen'] = $rc->$maxlen;
				
				if(!$rc->$type) {
					$optionals[$i]['type'] = 'text';
				} elseif($rc->$type == 1) {
					$optionals[$i]['type'] = 'area';
				} else {
					$sql = "select * from ".$this->registration_choices_table." where type_id = ".$rc->$type." order by display_order, value";
					$type_result = $this->db->Execute($sql);
					$matched = 0;
					if(!$type_result) {
						return false;
					} elseif($type_result->RecordCount() > 0) {
						$optionals[$i]['type'] = 'select';
						$optionals[$i]['options'] = array();
						for($o = 0; $dropdown = $type_result->FetchRow(); $o++) {
							$optionals[$i]['options'][$o]['value'] = $dropdown['value'];
							if($info["optional_field_".$i] == $dropdown['value']) {
								$optionals[$i]['options'][$o]['selected'] = true;
								$matched = 1;
							}
						}
					} else {
						$optionals[$i]['type'] = 'text';
					}
					if($rc->$other && $rc->$type) {
						$optionals[$i]['useOther'] = true;
						$optionals[$i]['matched'] = $matched;
					}
				}
			
				if(isset($this->error['optional_field_'.$i])) {
					$optionals[$i]['error'] = $this->error['optional_field_'.$i];
				}
				if($show->EXPOSE_OPTIONAL_1) {
					$optionals[$i]['exposeChecked'] = true;
				}
			}
		}
		$tpl->assign('optionals', $optionals);
		
		$this->get_configuration_data();
		if($this->fields->mapping_location->is_enabled) {
			$tpl->assign('using_mapping_fields', true);
		}
		
		//userdata fields added by order items
		$vars = array('user_id' => $this->userid, 'info' => $info, 'this' => $this);
		$tpl->assign('orderItemFields', geoOrderItem::callDisplay('user_information_edit_form_display', $vars, 'array',null,true));
		
		//userdata fields added by addons
		$addonFields = geoAddon::triggerDisplay('user_information_edit_form_display', $vars, geoAddon::ARRAY_ARRAY);
		foreach ($addonFields as $addon => $fields) {
			if (isset($fields['label'])) {
				//put it inside an array
				$addonFields[$addon] = array($fields);
			}
		}
		$tpl->assign('addonFields', $addonFields);
		
		$tpl->assign('backToUserData', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=3");
		$tpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name')."?a=4");
		
		//make sure this is not the demo's admin user
		$canEditPassword = true;
		if ($this->db->get_site_setting('info_edit_password_no_edit') || (defined('DEMO_MODE') && geoSession::getInstance()->getUserName() == 'admin')) {
			$canEditPassword = false;
		}
		$tpl->assign('canEditPassword', $canEditPassword);
		$tpl->assign('demo', defined('DEMO_MODE'));

		//if user doesn't have permission to create listings, don't show "seller's other listings" checkboxes
		$sql = "select restrictions_bitmask from ".geoTables::groups_table." where group_id = ".$user->group_id;
		$restrictions = $db->GetOne($sql);
		$tpl->assign('showCheckboxes', ($restrictions & 1) ? true : false);
		
		//check fee sharing
		$share_fees = geoAddon::getUtil('share_fees');
		$tpl->assign('feeshare_active', $share_fees->active);
		$share_fee_text =& geoAddon::getText('geo_addons','share_fees');
		if (($share_fees) && ($share_fees->active)) {
			//display the user this use is attached to here
			$which_attachtype = $share_fees->checkAttachedorAttaching($this->userid);
			$tpl->assign('feeshare_attachment_type',$which_attachtype);
			if (($which_attachtype == 1) && ($share_fees->use_attached_messages)) {
				//this user can be attached to so can leave a message for users attached to them
				//display the textbox to accept the message
				$tpl->assign('feeshare_share_message', $info['attached_user_message']);
				$tpl->assign('feeshare_attachtouserlabel', geoString::fromDB($share_fee_text['share_fees_message_to_share_edit_label']));
			} elseif ($which_attachtype == 2) {
				//this user can only do attaching so can't pass a message
				$attached_to_user_id = $share_fees->getUserAttachedTo ($this->userid);
				if ($attached_to_user_id != 0) {
					//set user attached to
					$tpl->assign('attachedtouser', $attached_to_user_id);
				}
				$tpl->assign('feeshare_attachtouserlabel', geoString::fromDB($share_fee_text['share_fees_user_management_choice_label']));
				$users_can_attach_to = $share_fees->attachableUsers();
				$tpl->assign('feeshare_userattachmentchoices', $users_can_attach_to);
				$tpl->assign('feeshareattachment_required', $share_fees->required);
			}
		
		}		
		
		$this->body = $tpl->fetch('information/edit_user_form.tpl');
		$this->display_page();
		return true;
		
	} //end of function edit_user_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function update_user($db,$info=0,$expose_info=0, $skip_addon_call = 0)
	{
		if (($info) && ($this->userid)) {
			$sql_query = "SELECT * FROM ".$this->db->geoTables->logins_table."
				WHERE `id` = ".$this->userid;
			$result = $this->db->Execute($sql_query);
			if ($this->debug) echo $sql_query." is the query<br />";
			
			if (!$result) {
				return false;
			} elseif ($result->RecordCount() == 1) {
				$showLogins = $result->FetchRow();
			} else {
				return false;
			}
			
			$this->sql_query = "select * from ".$this->db->geoTables->userdata_table." where id = ".$this->userid;
			$result = $this->db->Execute($this->sql_query);
			//echo $this->sql_query."<br />";
			if (!$result) {
				return false;
			} elseif ($result->RecordCount() == 1) {
				$show = $result->FetchNextObject();
				$this->sql_query = "insert into ".$this->userdata_history_table."
					(date_of_change,id,username,email,company_name,business_type,firstname,lastname,
					address,address_2,zip,city,phone,phone2,fax,url,optional_field_1,
					optional_field_2,optional_field_3,optional_field_4,optional_field_5,optional_field_6,optional_field_7,
					optional_field_8,optional_field_9,optional_field_10)
					values
					(".geoUtil::time().",
					".$show->ID.",
					\"".$show->USERNAME."\",
					\"".$show->EMAIL."\",
					\"".addslashes($show->COMPANY_NAME)."\",
					\"".$show->BUSINESS_TYPE."\",
					\"".addslashes($show->FIRSTNAME)."\",
					\"".addslashes($show->LASTNAME)."\",
					\"".addslashes($show->ADDRESS)."\",
					\"".addslashes($show->ADDRESS_2)."\",
					\"".addslashes($show->ZIP)."\",
					\"".addslashes($show->CITY)."\",
					\"".addslashes($show->PHONE)."\",
					\"".addslashes($show->PHONE2)."\",
					\"".addslashes($show->FAX)."\",
					\"".addslashes($show->URL)."\",
					\"".addslashes($show->OPTIONAL_FIELD_1)."\",
					\"".addslashes($show->OPTIONAL_FIELD_2)."\",
					\"".addslashes($show->OPTIONAL_FIELD_3)."\",
					\"".addslashes($show->OPTIONAL_FIELD_4)."\",
					\"".addslashes($show->OPTIONAL_FIELD_5)."\",
					\"".addslashes($show->OPTIONAL_FIELD_6)."\",
					\"".addslashes($show->OPTIONAL_FIELD_7)."\",
					\"".addslashes($show->OPTIONAL_FIELD_8)."\",
					\"".addslashes($show->OPTIONAL_FIELD_9)."\",
					\"".addslashes($show->OPTIONAL_FIELD_10)."\")";
				$result = $this->db->Execute($this->sql_query);
				//echo $this->sql_query."<br />";
				if (!$result) {
					return false;
				}
				
				//save location data, and override old fields where appropriate
				$regionOverrides =  geoRegion::getLevelsForOverrides();
				$geographicRegions = $_REQUEST['geoRegion_location'];
				if($geographicRegions) {
					geoRegion::setUserRegions($this->userid, $geographicRegions);
					$info['city'] = ($regionOverrides['city']) ? geoRegion::getNameForRegion($geographicRegions[$regionOverrides['city']]) : $info['city'];
				}
				
				$sql = "UPDATE ".geoTables::userdata_table." SET 
					email = ?, company_name = ?, business_type = ?, firstname = ?, lastname = ?, address = ?, address_2 = ?, 
					city = ?, zip = ?, phone = ?, phone2 = ?, fax = ?, url = ?, optional_field_1 = ?, optional_field_2 = ?, 
					optional_field_3 = ?, optional_field_4 = ?, optional_field_5 = ?, optional_field_6 = ?, optional_field_7 = ?, 
					optional_field_8 = ?, optional_field_9 = ?, optional_field_10 = ? WHERE `id` = ?";
				$result = $this->db->Execute($sql, array(
					trim($info['email']),
					addslashes($info['company_name']),
					$info['business_type'],
					addslashes($info['firstname']),
					addslashes($info['lastname']),
					addslashes($info['address']),
					addslashes($info['address_2']),
					addslashes($info['city']),
					addslashes($info['zip']),
					addslashes($info['phone']),
					addslashes($info['phone2']),
					addslashes($info['fax']),
					addslashes($info['url']),
					addslashes($info['optional_field_1']),
					addslashes($info['optional_field_2']),
					addslashes($info['optional_field_3']),
					addslashes($info['optional_field_4']),
					addslashes($info['optional_field_5']),
					addslashes($info['optional_field_6']),
					addslashes($info['optional_field_7']),
					addslashes($info['optional_field_8']),
					addslashes($info['optional_field_9']),
					addslashes($info['optional_field_10']),
					$this->userid
				));
				
				
				$info['user_id'] = $this->userid;
				$this->updatedInfo = $info;
				$info['this'] = $this;
				geoOrderItem::callUpdate('user_information_edit_form_update', $info,null,true);
				//to let addons manipulate info
				geoAddon::triggerUpdate('user_information_edit_form_update', $info);
				$info = $this->updatedInfo;
				if ( $info['apply_to_all_email'] ){
					$class_sql_query = "UPDATE ".$this->classifieds_table." SET email = ? WHERE seller = ".$this->userid;
					$class_result = $this->db->Execute($class_sql_query, array($info['email']));
					if (!$class_result){
						//echo $class_sql_query." is the classifieds email update query<br />";
						return false;
					}
				}
				if ( $info['apply_to_all_listings'] ){
					$class_sql_query = "UPDATE ".geoTables::classifieds_table." SET
						location_address = ?, location_city = ?, location_zip = ?, phone = ?, phone2 = ?, fax = ?, url_link_1 = ?
						WHERE seller = ?";
					$class_result = $this->db->Execute($class_sql_query, array(
						geoString::toDB(trim($info['address'])),
						geoString::toDB(trim($info['city'])),
						geoString::toDB(trim($info['zip'])),
						geoString::toDB(trim($info['phone'])),
						geoString::toDB(trim($info['phone2'])),
						geoString::toDB(trim($info['fax'])),
						geoString::toDB(trim($info['url'])),
						$this->userid
					));
					if (!$class_result){
						//echo $class_sql_query." is the classifieds listing update query<br />";
						return false;
					}
					
					//now update regions for all user's listings
					//begin by finding all listings belonging to this seller
					$allListings = $this->db->Execute("SELECT `id` FROM ".geoTables::classifieds_table." WHERE `seller` = ?", array($this->userid));
					while($allListings && $listing = $allListings->FetchRow()) {
						geoRegion::setListingRegions($listing['id'], $geographicRegions);						
					}
					
				}
				if ( $info['apply_to_mapping'] ){
					$mapping_location = array();
					if($info['address']) {
						$mapping_location[] = $info['address']; 
					}
					if($info['city']) {
						$mapping_location[] = $info['city'];
					}
					if($geographicRegions) {
						$mapping_location[] = geoRegion::getStateNameForUser($this->userid);
						$mapping_location[] = geoRegion::getCountryNameForUser($this->userid);
					}
					if($info['zip']) {
						$mapping_location[] = $info['zip'];
					}
					$mapping_location = implode(" ", $mapping_location);
					$class_sql_query = "UPDATE ".$this->classifieds_table." SET mapping_location = ? WHERE seller = ?";
					$class_result = $this->db->Execute($class_sql_query, array(geoString::toDB($mapping_location), $this->userid));
					if (!$class_result){
						//echo $class_sql_query." is the classifieds mapping update query<br />";
						return false;
					}
				}
				//echo $this->sql_query."<br />";
				if (!$result) {
					return false;
				} else {
					//echo $this->sql_query." - 1<br />";
					$password = trim(geoString::specialCharsDecode($info['password']));
					if (strlen($password) > 0) {
						//update the password
						$username = geoUser::userName($this->userid);
						$hash_type = $this->db->get_site_setting('client_pass_hash');
						$salt = '';
						$hashed_password = $this->product_configuration->get_hashed_password($username, $password,$hash_type);
						if (is_array($hashed_password)) {
							$salt = ''.$hashed_password['salt'];
							$hashed_password = ''.$hashed_password['password'];
						}
						$sql = "UPDATE ".geoTables::logins_table." SET
							`password` = ?, `hash_type`=?, `salt`=?
							WHERE `id` = ?";
						$result = $this->db->Execute($sql, array($hashed_password, $hash_type, $salt, $this->userid));
						//echo $this->sql_query."<br />";
						if (!$result) {
							//$this->body .=$this->sql_query." is the query<br />";
							return false;
						}
					}
					
					//check shared fees use
					$share_fees = geoAddon::getUtil('share_fees');
					if (($share_fees) && ($share_fees->active)) {
						$which_attachtype = $share_fees->checkAttachedorAttaching($this->userid);
						if (($which_attachtype == 1) && ($share_fees->use_attached_messages)) {
							//This user is attached to and only needs to save the share message
							$share_fees->SaveAttachedMessage($this->userid, $info['attached_user_message']);
						} elseif ($which_attachtype == 2) {
							//this user does the attaching
							//insert user attachment if there is an attachment
							if ($info["user_attachment_id"]) {
								//if attachment check valid user attached to and that registrant can be attached to attached user
								if ($share_fees->checkUserToAttachment ($this->userid, $info['user_attachment_id'])) {
									//remove current and insert neew OR update current
									$share_fees->insertUserAttachment ($this->userid, $info['user_attachment_id'],1);
								}
							} elseif (($info["user_attachment_id"] == 0) || (strlen(trim($info["user_attachment_id"])) == 0)) {
								//remove any attachment from database
								$share_fees->removeUserAttachedTo ($this->userid);
							}
						}
					}					
					
					//reset and update whether to expose personal data
					$this->sql_query = "update ".$this->userdata_table." set
						expose_email = 0,
						expose_company_name = 0,
						expose_firstname = 0,
						expose_lastname = 0,
						expose_address = 0,
						expose_city = 0,
						expose_state = 0,
						expose_country = 0,
						expose_zip = 0,
						expose_phone = 0,
						expose_phone2 = 0,
						expose_fax = 0,
						expose_url = 0,
						expose_optional_1 = 0,
						expose_optional_2 = 0,
						expose_optional_3 = 0,
						expose_optional_4 = 0,
						expose_optional_5 = 0,
						expose_optional_6 = 0,
						expose_optional_7 = 0,
						expose_optional_8 = 0,
						expose_optional_9 = 0,
						expose_optional_10 = 0
						where id =".$this->userid;
					$result = $this->db->Execute($this->sql_query);
					//echo $this->sql_query." is the query<br />";
					if (!$result) {
						//echo $this->sql_query." is the query<br />";
						return false;
					} else {
						//echo $expose_info." is expose_info<br />";
						if (is_array($expose_info)) {
							
							//these are no longer used
							$expose_info['expose_state'] = $expose_info['expose_country'] = 0;
							
							$validFields = array(
								'expose_email',
								'expose_company_name',
								'expose_firstname',
								'expose_lastname',
								'expose_address',
								'expose_city',
								'expose_state',
								'expose_country',
								'expose_zip',
								'expose_phone',
								'expose_phone2',
								'expose_fax',
								'expose_url',
								'expose_optional_1',
								'expose_optional_2',
								'expose_optional_3',
								'expose_optional_4',
								'expose_optional_5',
								'expose_optional_6',
								'expose_optional_7',
								'expose_optional_8',
								'expose_optional_9',
								'expose_optional_10'
							);
							
							foreach ($expose_info as $key => $value) {
								if ($value == 1 && in_array($key, $validFields)) {
									$this->sql_query = "update ".$this->userdata_table." set ".
										$key." = 1
										where id =".$this->userid;
									$result = $this->db->Execute($this->sql_query);
									//echo $this->sql_query." is the query<br />";
									if (!$result) {
										return false;
									}
								}
							}
						}
						if (!$skip_addon_call){
							$info["old_username"] = $showLogins["username"];
							$info["old_password"] = $showLogins["password"];
							//make sure the username is not able to be changed.
							$info["username"] = geoSession::getInstance()->getUserName();
							//un-do encoding on password, as that is how it is saved in db
							$info['password'] = trim(geoString::specialCharsDecode($info['password']));
							geoAddon::triggerUpdate('user_edit',$info);
						}
						
						//this will send an email to the admin notifying them of changes to a
						//users information
						if ($this->db->get_site_setting('admin_email_edit') && geoPC::is_ent()) {
							$subject = "User details have been edited for ".$show->USERNAME;
							$message = "Below is the new user information:

";
							$message .= "email = ".$info["email"]."
";
							$message .= "company_name = ".$info["company_name"]."
";
							$message .= "business_type = ".$info["business_type"]."
";
							$message .= "firstname = ".$info["firstname"]."
";
							$message .= "lastname = ".$info["lastname"]."
";
							$message .= "address = ".$info["address"]."
";
							$message .= "address_2 = ".$info["address_2"]."
";
							$message .= "city = ".$info["city"]."
";
							$message .= "zip = ".$info["zip"]."
";
							$message .= "phone = ".$info["phone"]."
";
							$message .= "phone2 = ".$info["phone2"]."
";
							$message .= "fax = ".$info["fax"]."
";
							$message .= "url = ".$info["url"]."
";
							$message .= "optional_field_1 = ".$info["optional_field_1"]."
";
							$message .= "optional_field_2 = ".$info["optional_field_2"]."
";
							$message .= "optional_field_3 = ".$info["optional_field_3"]."
";
							$message .= "optional_field_4 = ".$info["optional_field_4"]."
";
							$message .= "optional_field_5 = ".$info["optional_field_5"]."
";
							$message .= "optional_field_6 = ".$info["optional_field_6"]."
";
							$message .= "optional_field_7 = ".$info["optional_field_7"]."
";
							$message .= "optional_field_8 = ".$info["optional_field_8"]."
";
							$message .= "optional_field_9 = ".$info["optional_field_9"]."
";
							$message .= "optional_field_10 = ".$info["optional_field_10"]."
";

							$this->sendMail($this->db->get_site_setting('site_email'),$subject,$message);
						}

						return true;
					}
				
				}
			} else {
				return false;
			}
		} else {
			return false;
		}

	} //end of function update_user

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	public function get_user_subscription()
	{
		$sql = "SELECT * FROM ".geoTables::user_subscriptions_table." WHERE `user_id` = ".(int)$this->userid;
		
		return $this->db->GetRow($sql);
	} // end of function check_user_subscription

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function get_registration_configuration_data()
	{
		$this->sql_query = "SELECT * FROM ".$this->registration_configuration_table;
		//echo $this->sql_query."<br />";
		$result = $this->db->Execute($this->sql_query);
		if (!$result) {
			return false;
		} else {
			$this->registration_configuration = $result->FetchNextObject();
		}
		return true;
	} //end of function get_registration_configuration_data

//########################################################################

	function check_info($db,$info=0)
	{
		$this->page_id = 38;
		$this->get_text();
		$this->error = array();
		$this->error_found = 0;
		
		//first verify that the current password is correct.
		$currentPass = trim(geoString::specialCharsDecode($info['currentP']));
		$username = geoSession::getInstance()->getUsername();
		$product_configuration = geoPC::getInstance();
		if ($this->db->get_site_setting('info_edit_require_pass') && (!strlen($currentPass) || !$product_configuration->verify_credentials($username,$currentPass,false,false,true))) {
			//current pass does not match up...
			$this->error_found++;
			$this->error['currentP'] = $this->messages[500234];
		}
		
		if ($this->registration_configuration->USE_REGISTRATION_COMPANY_NAME_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_COMPANY_NAME_FIELD) {
				if (strlen(trim($info['company_name'])) == 0) {
					$this->error['company_name'] =urldecode($this->messages[535]);
					$this->error_found++;
				}
			}
		}
		if ($this->registration_configuration->USE_REGISTRATION_FIRSTNAME_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_FIRSTNAME_FIELD) {
				if (strlen(trim($info['firstname'])) == 0) {
					$this->error['firstname'] =urldecode($this->messages[536]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_LASTNAME_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_LASTNAME_FIELD) {
				if (strlen(trim($info['lastname'])) == 0 ) {
					$this->error['lastname'] =urldecode($this->messages[537]);
					$this->error_found++;
		  		}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_ADDRESS_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_ADDRESS_FIELD) {
				if (strlen(trim($info['address']))== 0 ) {
					$this->error['address'] =urldecode($this->messages[538]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_ADDRESS2_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_ADDRESS2_FIELD) {
				if (strlen(trim($info['address_2']))== 0 ) {
					$this->error['address_2'] =urldecode($this->messages[538]);
					$this->error_found++;
				}
			}
		}

		$info['email'] = trim($info['email']);
		if (strlen($info['email']) > 0) {
			if (geoString::isEmail($info['email'])) {
				if ( geoPC::is_ent() || geoPC::is_premier() ) {
					//check for black/whitelisted email domains
					$user_domain = explode('@',$info['email'] );
					$domain = $user_domain[1];
					$domain_parts = explode('.', $domain);
					$len = sizeof($domain_parts) - 1;
					$foundEmailDomainInDB = false;
					
					$sql = "SELECT * FROM ".$this->block_email_domains. " WHERE domain LIKE '%.".$domain_parts[$len]."' OR domain LIKE '%.*'";
					$result = $this->db->Execute($sql);
					//echo $sql." is sql<bR>";
					$tlds = array();
					while($line = $result->FetchRow())
						$tlds[] = $line['domain']; //grab all db entries with same top level domain as input

						//step through pieces of input, narrowing match options each time
					for($i = $len-1; $i >= 0; $i--) {
						$new_tlds = array();
						foreach($tlds as $tld) {
							$pattern = ".+\.".$domain_parts[$i]."\..*";
							$star_pattern = ".+\.\*.*"; // look for literal * in db entry
							if($i==0) {
								$pattern = "^".$domain_parts[$i]."\..*";
								$star_pattern = "\*\..*";
							}
							if(preg_match("/$pattern/i", $tld) || preg_match("/$star_pattern/i", $tld)) {
								$new_tlds[] = $tld;
							}
						}
							$tlds = $new_tlds;
					}
					if(sizeof($tlds) > 0) {
						$foundEmailDomainInDB = true;
					}
				}
				
				$email_restriction = $this->db->get_site_setting("email_restriction"); //defaults to "blocked"
				if( (!$foundEmailDomainInDB && $email_restriction == "blocked") || ($foundEmailDomainInDB && $email_restriction == "allowed") || $email_restriction === false) {
					$this->sql_query = "select id from ".$this->userdata_table." where email = ?";
					$result = $this->db->Execute($this->sql_query, array($info['email']));
					if (!$result) {
						$this->error['email'] =urldecode($this->messages[539]);
						return false;
					} elseif ($result->RecordCount() == 1) {
						$show_id = $result->FetchNextObject();
						if ($show_id->ID != $this->userid) {
							$this->error['email'] =urldecode($this->messages[539]);
							$this->error_found++;
						}
					} elseif ($result->RecordCount() > 1) {
						//email already in use
						//is it this user?
						$this->error['email'] =urldecode($this->messages[539]);
						$this->error_found++;
					} else {
						/* this is the base "everything is working" case through all this mess
						 * non-error states will end up here
						 */ 
					}
				} else {
					$this->error['email'] = urldecode($this->messages[540]);
					$this->error_found++;
		  		}
		  	} else {
				$this->error['email'] = urldecode($this->messages[540]);
				$this->error_found++;
	  		}
		} else {
			$this->error['email'] =urldecode($this->messages[541]);
			$this->error_found++;
		}

		$overrides = geoRegion::getLevelsForOverrides();
		if (!$overrides['city'] && $this->registration_configuration->USE_REGISTRATION_CITY_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_CITY_FIELD) {
				if (strlen(trim($info['city'])) == 0 ) {
					$this->error['city'] =urldecode($this->messages[542]);
					$this->error_found++;
				}
			}
		}

		//check regions
		$locations = $_REQUEST['geoRegion_location'];
		$lowestEnabledRegion = false;
		for($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
			if($this->db->get_site_setting('registration_use_region_level_'.$r)) {
				$lowestEnabledRegion = $r;
				break;
			}
		}
		if($lowestEnabledRegion) {
			//regions are in use. see if any are required
			$lowestRequiredRegion = false;
			for($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
				if($this->db->get_site_setting('registration_require_region_level_'.$r)) {
					$lowestRequiredRegion = $r;
					break;
				}
			}
			if($lowestRequiredRegion) {
				if(!$locations) {
					//at least one region level is required, but there are no regions here!
					$this->error_found++;
					$this->error['location'] = $this->messages[501630];
				} else {
					//check for branches that don't extend all the way down to the lowest-level required
					//(i.e. if level 3 is required, but some level 2 region has no children, that's okay -- behave as if level 2 is required)
					for ($i = $lowestRequiredRegion; $i > 0; $i--) {
						if (isset($locations[$i]) && !$locations[$i]) {
							//this level is present but not set, and is the lowest required or higher. generate an error.
							$this->error_found++;
							$this->error['location'] =  $this->messages[501630];
							//no need to keep going once we have at least one error
							break;
						}
					}
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_ZIP_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_ZIP_FIELD) {
				if (strlen(trim($info['zip'])) == 0 ) {
					$this->error['zip'] =urldecode($this->messages[545]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_PHONE_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_PHONE_FIELD) {
				if (strlen(trim($info['phone'])) == 0 ) {
					$this->error['phone'] =urldecode($this->messages[546]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_PHONE2_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_PHONE2_FIELD) {
				if (strlen(trim($info['phone2'])) == 0 ) {
					$this->error['phone2'] =urldecode($this->messages[548]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_FAX_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_FAX_FIELD) {
				if (strlen(trim($info['fax'])) == 0 ) {
					$this->error['fax'] =urldecode($this->messages[547]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_URL_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_URL_FIELD) {
				if (strlen(trim($info['url'])) == 0 ) {
					$this->error['url'] =urldecode($this->messages[549]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_1_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_1_FIELD) {
				if (strlen(trim($info['optional_field_1'])) == 0 ) {
					$this->error['optional_field_1'] =urldecode($this->messages[1266]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_2_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_2_FIELD) {
				if (strlen(trim($info['optional_field_2'])) == 0 ) {
					$this->error['optional_field_2'] =urldecode($this->messages[1267]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_3_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_3_FIELD) {
				if (strlen(trim($info['optional_field_3'])) == 0 ) {
					$this->error['optional_field_3'] =urldecode($this->messages[1268]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_4_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_4_FIELD) {
				if (strlen(trim($info['optional_field_4'])) == 0 ) {
					$this->error['optional_field_4'] =urldecode($this->messages[1269]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_5_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_5_FIELD) {
				if (strlen(trim($info['optional_field_5'])) == 0 ) {
					$this->error['optional_field_5'] =urldecode($this->messages[1270]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_6_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_6_FIELD) {
				if (strlen(trim($info['optional_field_6'])) == 0 ) {
					$this->error['optional_field_6'] =urldecode($this->messages[1271]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_7_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_7_FIELD) {
				if (strlen(trim($info['optional_field_7'])) == 0 ) {
					$this->error['optional_field_7'] =urldecode($this->messages[1272]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_8_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_8_FIELD) {
				if (strlen(trim($info['optional_field_8'])) == 0 ) {
					$this->error['optional_field_8'] =urldecode($this->messages[1273]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_9_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_9_FIELD) {
				if (strlen(trim($info['optional_field_9'])) == 0 ) {
					$this->error['optional_field_9'] =urldecode($this->messages[1274]);
					$this->error_found++;
				}
			}
		}

		if ($this->registration_configuration->USE_REGISTRATION_OPTIONAL_10_FIELD) {
			if ($this->registration_configuration->REQUIRE_REGISTRATION_OPTIONAL_10_FIELD) {
				if (strlen(trim($info['optional_field_10'])) == 0 ) {
					$this->error['optional_field_10'] =urldecode($this->messages[1275]);
					$this->error_found++;
				}
			}
		}
		
		$share_fees = geoAddon::getUtil('share_fees');
		if (($share_fees) && ($share_fees->active)) {
			$thisUserId = geoSession::getInstance()->getUserId();
			$which_attachtype = $share_fees->checkAttachedorAttaching($thisUserId);
			if (($which_attachtype == 1) && ($share_fees->use_attached_messages)) {
				//nothing to check as the only data could be the share message
			} elseif ($which_attachtype == 2) {
				$share_fee_text =& geoAddon::getText('geo_addons','share_fees');
				if (($share_fees->required) && ($info['user_attachment_id'] == 0)) {
					$this->error['feeshareattachmenterror'] = $share_fee_text['share_fees_attachment_required_edit'];
					$this->error_found++;
				} elseif ($info['user_attachment_id'] != 0) {
		
					if (!$share_fees->checkUserToAttachment ($thisUserId, $info['user_attachment_id'])) {
						$this->error['feeshareattachmenterror'] = $share_fee_text['share_fees_attachment_error_edit'];
						$this->error_found++;
					} else {
						//everythings good and attached
					}
				}
			}
		}		
		
		$password = trim(geoString::specialCharsDecode($info['password']));
		$password_verify = trim(geoString::specialCharsDecode($info['password_verify']));
		if ($password == $password_verify && strlen($password) > 0) {
			//$this->body .="passwords match<br />";
			$password_length = strlen($password);
			
			if ((($password_length > $this->db->get_site_setting('max_pass_length')) || ($password_length < $this->db->get_site_setting('min_pass_length')) || ($password_length == 0))) {
				//wrong string length.
				$this->error["password"] = $this->messages[550];
				$this->error_found++;
			} else if ($password == trim($username)) {
				//password cannot match username
				$this->error['password'] = $this->messages[500232];
				$this->error_found++;
			}
		} else if ($password != $password_verify) {
			//password verify not match password
			$this->error['password'] = urldecode($this->messages[550]);
			$this->error_found++;
		}
		
		//allow items to do error checking
		$itemVars = array ('info' => $info, 'this' => $this);
		geoOrderItem::callUpdate('user_information_edit_form_check_info', $itemVars, null, true);
		//also allow addons to check info
		geoAddon::triggerUpdate('user_information_edit_form_check_info', $itemVars);
		//echo $this->error_found." is the error count<br />";
		//reset($this->error);
		//foreach ($this->error as $key => $value)
		//	echo $key." is the key to ".$value."<br />";
		return $this->error_found == 0;
	} //end of function check_info($info)



} // end
