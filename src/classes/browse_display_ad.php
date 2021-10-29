<?php
//browse_display_ad.php
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
## ##    17.05.0-22-g4435795
## 
##################################

class Display_ad extends geoBrowse {
	var $subcategory_array = array();
	var $notify_data = array();
	
	var $ad_id;
	var $offsite_videos = null;
	var $offsite_videos_from_db = true;

	var $debug_ad_display = 0;
	var $debug_ad_display_time = 0;
	var $query_link = null;

//########################################################################

	public function __construct ($page=0, $classified_id=0)
	{
		$db = $this->db = DataAccess::getInstance();
		
		$classified_id = (int)$classified_id;
		if ($classified_id) {
			$listing = geoListing::getListing($classified_id);
			if ($listing && $listing->category) {
				$this->site_category = (int)$listing->category;
			}
		} else {
			$this->site_category = 0;
		}
		
		$this->get_ad_configuration();
		if ($page) {
			$this->page_result = (int)$page;
		} else {
			$this->page_result = 1;
		}

		$this->ad_id = $this->classified_id = $classified_id;
		parent::__construct();
	} //end of function Display_ad

//###########################################################

	public function display_classified($id=0,$return=false,$preview = false, $autoDisplay = true, $session_vars = null, $printFriendly = false)
	{
		$db = DataAccess::getInstance();		
		$view = geoView::getInstance();
		if ($printFriendly) {
			//if print friendly, use page 69 and will also have to use
			//text specific to that page.
			$this->page_id = 69;
		} else {
			$this->page_id = 1;
		}
		
		if ($this->query_link === null) {
			//set query link, basically the URL that would go before query values
			if ($view->isAffiliatePage && $view->affiliate_id) {
				$this->query_link = $this->db->get_site_setting('affiliate_url').'?aff='.$view->affiliate_id.'&amp;';
			} else {
				$this->query_link = $this->db->get_site_setting('classifieds_file_name').'?';
			}
		}
		
		$this->get_text();
		$id = intval($id);
		$listing = geoListing::getListing($id);
		
		//what is user ID of person viewing this listing...
		$browser_user_id = geoSession::getInstance()->getUserId();
		
		if (!$id || !$listing) {
			$this->browse_error();
			return true;
		}
		$view->listing_id = $id;
		
		if (!$browser_user_id) {
			require_once(CLASSES_DIR.'authenticate_class.php');
		}
		
		//this (beta) setting has no effect if the Subscription Pricing addon is not in use
		$must_have_subscription = (geoAddon::getInstance()->isEnabled('subscription_pricing')) ? $this->db->get_site_setting('must_have_subscription_to_view_ad_detail') : false;
		if ($must_have_subscription && $browser_user_id==1) {
			//main admin user, always let this user view listing
			$must_have_subscription=false;
		}
		
		if(($this->configuration_data['subscription_to_view_or_bid_ads'] || $must_have_subscription) && !$browser_user_id) {
			//user not logged in!
			include_once("authenticate_class.php");
			$auth = new Auth($db,$this->language_id);
  			$auth->login_form($db, "", "", "a*is*2*and*b*is*".$id, 3);
			return true;
		}
		
		if ($must_have_subscription && $browser_user_id != $listing->seller) {
			if (!$this->check_user_subscription()) {
				//user logged in, but doesn't have subscription, when using the setting
				//that user must have subscription to view listings.  Take them to subscription purchase page.
				//NOTE: sellers can always view their own listings.
				header('Location: '.geoFilter::getBaseHref() . $this->db->get_site_setting('classifieds_file_name').'?a=cart&action=new&main_type=subscription');
				include GEO_BASE_DIR . 'app_bottom.php';
				exit;
			}
		}
		
		$show = $listing->toArray();
		$isPreview = false;
		if ($preview == 'preview_only') {
			$view->preview_text = $preview_text = $this->messages[500402];
			//let custom whatever's know this is a preview of a listing
			$view->preview_listing = $isPreview = 1;
			if ($session_vars) {
				//let listingdisplay class know
				geoListingDisplay::addSessionVars($session_vars);
				
				//merge session vars onto show, by emulating what is done
				//at the time the listing is inserted into the DB
				require_once CLASSES_DIR . 'order_items/_listing_placement_common.php';
				$session_to_listing_key_map = _listing_placement_commonOrderItem::getSessionToListingKeyMap();
				$listing_vars_to_update = _listing_placement_commonOrderItem::getListingVarsToUpdate();
				
				foreach ($session_vars as $i => $val) {
					$keys = (isset($session_to_listing_key_map[$i]))? $session_to_listing_key_map[$i]: $i;
					$keys = (is_array($keys))? $keys : array($keys);
					//loop through each translation and set it, this allows one session var to be assigned to multiple
					//listing rows.
					foreach ($keys as $key) {
						if (isset($listing_vars_to_update[$key])) {
							
							//encode value according to what type it is
							switch($listing_vars_to_update[$key]){
								case 'toDB':
									if (is_array($val) && $key == 'seller_buyer_data' && geoPC::is_ent()) {
										//special case
										$val = serialize($val);
									}
									$show[$key] = trim(geoString::toDB($val));
									break;
								case 'int':
									$show[$key] = intval($val);
									break;
								case 'float':
									$show[$key] = floatval($val);
									break;
								case 'bool':
									$show[$key] = (($val)? 1: 0);
									break;
								default:
									//not altered, for fields like "date"
									
									$show[$key] = $val;
									break;
							}
						}
					}
					//fun hack: pretend the listing vars for the rest of the
					//page load are the real values, now anything that uses
					//geoListing will use the preview values...
					geoListing::addDataSet(array($show));
				}
			}
		} else if (geoPC::is_print() && $this->db->get_site_setting('disableAllBrowsing')) {
			//not supposed to show listing details on front side (but do allow
			//if previewing)
			$this->display_page();
			return true;
		}
		if ($db->get_site_setting('pre_populate_listing_tags')) {
			//set all the {listing tag='tag_name'} as template vars like {$tag_name}
			//for backwards compatibility
			geoListingDisplay::preParseAllTags();
		}
		
		//lets clean up a few common vars so we don't do it over and over
		$precurrency = $show['precurrency'];
		$postcurrency = $show['postcurrency'];
		
		if ($this->debug_ad_display) {
			echo "data has been retrieved<br>";
			echo $listing->item_type." is the item_type<br>\n";	
		}
			
		if (geoMaster::is('auctions')) {
			if ($show['start_time']) {
				$bid_start_date = date(trim($this->configuration_data['entry_date_configuration']),$show['start_time']);
				$time = $show['start_time'];
			} else {
				$bid_start_date = date(trim($this->configuration_data['entry_date_configuration']),$show['date']);
				$time = $show['date'];
			}
			$view->in_bidding_preview = $time > geoUtil::time() ? true : false; 
		}
		if (!(($show['live']==1 && $show['item_type']==1) || $db->get_site_setting('show_expired_classifieds') || $show['item_type']==2 || $preview === 'preview_only')) {
			$this->browse_error();
			return true;
		}
		
		if ($this->debug_ad_display) {
			echo "listing is live<br>";
		}
		
		if(!$this->site_category) {
			//if category not set, might be in storefront or something
			//try to pull category from listing data
			$this->site_category = $show["category"];
		}
		
		$this->browsing_configuration = $this->ad_configuration_data;
		$catCfg = geoCategory::getCategoryConfig($this->site_category, true);
		if($catCfg && $catCfg['what_fields_to_use'] != 'site') {
			$this->browsing_configuration = array_merge($this->browsing_configuration, $catCfg);
		}
		
		if ($this->debug_ad_display_time) echo $this->get_end_time()." after template gotten<br/>\n";
		
		$can_bid = false;
		//figure out if can bid or not...  Default to off so only need to figure out
		//when on..
		if ($show["live"] && geoMaster::is('auctions') && $show['item_type']==2 && $time <= geoUtil::time() && ($show['ends']>geoUtil::time() || $show['ends'] == 0 || $show["delayed_start"])) {
			if ($browser_user_id) {
				//set single $can_bid variable here instead of checking invited and blacklist several times in function
				if ($this->debug_ad_display) {
					echo $this->configuration_data['black_list_of_buyers']." is to check blacklist<br/>\n";
					echo $this->configuration_data['invited_list_of_buyers']." is to check invited<br/>\n";
				}
				if ($this->configuration_data['invited_list_of_buyers'] && $this->configuration_data['black_list_of_buyers']) {
					//both invite and blacklist are enabled, check both
					$invited = $this->check_invitedlist($db,$show["seller"],$browser_user_id);
					$banned = $this->check_blacklist($db,$show["seller"],$browser_user_id);
					
					if ($invited==1) {
						//browsing user is actually on invited list, expired list does not matter.
						$can_bid = true;
					} else if ($banned) {
						//browsing user is on ban list
						$error_msg = $this->messages[102861];
					} else if ($invited == 2) {
						//browsing user is not on any list, but invited list is not
						//populated so allow them
						$can_bid = true;
					}
				} else if ($this->configuration_data['invited_list_of_buyers']) {
					//check invited only
					if ($this->check_invitedlist($db,$show["seller"],$browser_user_id)>0) {
						//either they are invited, or invited list is empty, either way
						//they are allowed to bid.
						$can_bid = 1;
					} else {
						//not on invited list
						if ($this->debug_ad_display) {
							echo "not on the invited list so cannot bid<br/>\n";
						}
						$error_msg = $this->messages[102862];
					}
				} else if ($this->configuration_data['black_list_of_buyers']) {
					//check black list only
					if ($this->debug_ad_display) {
						echo "checking only black list of buyers<br/>\n";
					}
					if ($this->check_blacklist($db,$show["seller"],$browser_user_id)) {
						//check_blacklist returned true, meaning user is on blacklist
						//and cannot bid
						if ($this->debug_ad_display) {
							echo "this buyer is on the blacklist 2<br/>";
						}
						$error_msg = $this->messages[102861];
					} else {
						//browsing user not on blacklist, so can bid
						$can_bid = true;
					}
				} else {
					//there are no restrictions on whether can bid or not..
					if ($this->debug_ad_display) {
						echo "there are no lists to check<br/>";
					}
					$can_bid = true;
				}
			} else {
				//we do not have the user id to check against so cannot say whether can bid or not
				$can_bid = true;
				
				//go ahead and include auth class so can generate login links
				//using Auth::generateEncodedVars()
				require_once CLASSES_DIR.'authenticate_class.php';
			}
		}

		if ($this->debug_ad_display_time) echo $this->get_end_time()." after live check<br/>\n";

		//if auctions start is delayed till first bid then no start and end date is displayed.
		if ($show["delayed_start"] == 0) {
			$end_date = date(trim($this->configuration_data['entry_date_configuration']), $show["ends"]);
			$start_date = date(trim($this->configuration_data['entry_date_configuration']), $show["date"]);
		} else {
			$end_date = $this->messages[500225];//TODO: print text
			$start_date = $this->messages[500225];
		}
		$seller_data = ($show['seller'])? geoUser::getUser($show['seller']) : false;
		
		foreach ($this->images_to_display as $imgData) {
			//find first image that is normal and would display in slideshow
			if (!strlen($imgData['icon']) && $imgData['id']) {
				//found it!
				$view->image_slideshow_link = "<a href='get_image.php?id=".(int)$imgData['id']."' class='lightUpLink autoStartSlideshow' onclick=\"return false;\">".$this->messages[500883]."</a>";
				break;
			}
		}
		
		//go ahead and make the raw image data populated for templates to use if they want
		$view->listing_images_raw = geoListing::getImages($id);

		if ($this->debug_ad_display_time) echo $this->get_end_time()." after lead picture placed<br/>\n";
		

		if (strlen($this->messages[500798]) >0) {
			if ($show["sold_displayed"]) {
				$title = '<img src="'.geoTemplate::getUrl('',$this->messages[500798]).'" alt="" /> '.geoString::fromDB($show["title"]);
			} else {
				$title = geoString::fromDB($show["title"]);
			}
		} else {
			$title = geoString::fromDB($show["title"]);
		}

		//make sure auction is not a delayed start as "ends" = 0, not expired and is an auction
		// before displaying the closed message...classifieds do not need a closed message 
		if ($preview != 'preview_only' && $show["item_type"] == 2 && $show['ends'] > 0 && $show["ends"] <= geoUtil::time() && $show["delayed_start"] == 0) {
			$title .= "&nbsp;<span class=\"closed_label\">{$this->messages[103369]}</span>";//TODO: print text
		}
		$view->title = $title;
		
		if ($this->fields->tags->is_enabled) {
			if ($isPreview && isset($session_vars['tags'])) {
				//loaded from session vars since it is preview
				$tags = explode(', ', $session_vars['tags']);
				//be sure to keep track of them!
				$listing->e_listing_tags = $tags;
			} else {
				//get tags from db
				
				$tags = geoListing::getTags($id);
			}
			//Add the array of tags for template designers to play with
			$view->listing_tags_array = $tags;
			
			if (count($tags) > 0) {
				$view->listing_tags_label = $this->messages[500869];
			} else {
				$view->listing_tags_label = '';
			}
		}
		
		if ($this->fields->mapping_location->is_enabled && $show["mapping_location"]) {
			$view->mapping_location = $view->mapping_address = geoString::fromDB($show["mapping_location"]);
		}

		$view->time_remaining_label = ($printFriendly)? $this->messages[103325]: $this->messages[102705];
				
		$view->date_ended = $end_date;
		$view->date_ended_label = ($printFriendly)? $this->messages[103088]: $this->messages[102701];
		
		if($show['ends'] == 0) {
			//unlimited duration listing. do not show end data
			$view->time_remaining_label = $view->date_ended = $view->date_ended_label = false;
		}
		
		if ( geoPC::is_ent() ) {
			$txt_id = ($printFriendly)? 1090: 912;
			for ($i = 1; $i <= 20; $i++) {
				$v_item = "optional_field_{$i}";
				
				$val = geoString::fromDB($show[$v_item]);
				if ($this->fields->$v_item->is_enabled && trim($val)) {
					$field_type = $this->fields->$v_item->field_type;
					if ($field_type=='cost') {
						//adds cost
						$val = geoString::displayPrice($val,$precurrency, $postcurrency, 'listing');
					} else if ($field_type=='date') {
						//date field
						$val = geoCalendar::display($val);
					}
					$label = "optional_field_{$i}_label";
					$view->$label = $this->messages[$txt_id];
					$view->$v_item = $val;
				}
				$txt_id++;
				if (($printFriendly && $txt_id == 1100) || $txt_id == 922) {
					$txt_id = ($printFriendly)? 1706: 1726;
				}
			}
		}
		if ($this->fields->address->is_enabled && $show["location_address"]) {
			$view->address_label = $this->messages[500163];//TODO: Print friendly text
			$view->address_data = ucwords(geoString::fromDB($show["location_address"]));
		}
		
		
		if ($this->fields->zip->is_enabled && $show["location_zip"]) {
			$view->zip_label = ($printFriendly)? $this->messages[1479]: $this->messages[1216];
			$view->zip_data = geoString::fromDB($show["location_zip"]);
		}
		
		//assign listing's regions to vars for potential display
		
		$overrides = geoRegion::getLevelsForOverrides();
		$view->regionLevelNames = $overrides;
		
		$enabledRegions = array();
		for($i = geoRegion::getLowestLevel(); $i > 0; $i--) {
			$field = 'region_level_'.$i;
			$enabledRegions[$i] = $this->fields->$field->is_enabled;
		}
		//put enabled array in order, mostly for sanity
		ksort($enabledRegions);

		//populate state and country tags so that older templates still work
		$stateName = geoRegion::getStateNameForListing($show['id']);
		$countryName = geoRegion::getCountryNameForListing($show['id']);
		if($enabledRegions[$overrides['state']] && $stateName) {
			$view->state_label = geoRegion::getLabelForLevel($overrides['state']);
			$view->state_data = $stateName;
		}
		if($enabledRegions[$overrides['country']] && $countryName) {
			$view->country_label = geoRegion::getLabelForLevel($overrides['country']);
			$view->country_data = $countryName;
		}
		
		//also override city data if needed
		if($overrides['city'] && $enabledRegions[$overrides['city']]) {
			//do city the new way
			$view->city_label = geoRegion::getLabelForLevel($overrides['city']);
			$view->city_data = geoRegion::getNameForListingLevel($show['id'], $overrides['city']);
		}
		if (!$view->city_data && $this->fields->city->is_enabled && $show["location_city"]) {
			//didn't get a city name the new way, but the old way might work!
			$view->city_label = ($printFriendly)? $this->messages[1468]: $this->messages[1213];
			$view->city_data = ucwords(geoString::fromDB($show["location_city"]));
		}
		
		$regions = array();
		foreach($enabledRegions as $level => $enabled) {
			if(!$enabled) {
				//region disabled -- skip it!
				continue;
			}
			$regions[$level]['name'] = geoRegion::getNameForListingLevel($show['id'], $level);
			$regions[$level]['levelLabel'] = geoRegion::getLabelForLevel($level);
			$lastLevel = $level;
		}
		$view->regions = $regions;
		$view->allRegions = geoRegion::displayRegionsForListing($show['id'], $lastLevel);
		
		$view->classified_id_label = ($printFriendly)? $this->messages[1082]: $this->messages[8];
		$view->classified_id = $show["id"];
		$view->viewed_count_label = ($printFriendly)? $this->messages[1084]: $this->messages[10];
		$view->viewed_count = $show["viewed"] + 1;
		
		
		
		if ($seller_data) {			
			$view->seller_label = ($printFriendly)? $this->messages[1078]: $this->messages[3];
		}
		
		if (geoMaster::is('auctions') && $bid_start_date) {
			$view->bid_start_date_label = ($printFriendly)? $this->messages[103326]: $this->messages[102819];
			$view->bid_start_date = $bid_start_date;
		}
		$view->date_started_label = ($printFriendly)? $this->messages[1079]: $this->messages[4];
		$view->date_started = $start_date;
		
		$view->description_label = ($printFriendly)? $this->messages[1081]: $this->messages[7];
		$view->description = geoString::fromDB($show["description"]);

		if ($this->debug_ad_display_time) echo $this->get_end_time()." after most fields placed<br/>\n";

		//classauctions details
		if ($show["item_type"]==1) {
			if ($this->fields->price->is_enabled) {
				$view->price_label = ($printFriendly)? $this->messages[1085]: $this->messages[15];
				$display_amount = geoString::displayPrice($show["price"],$precurrency,$postcurrency, 'listing');
				$view->price = $display_amount;
			}
		}

		if (geoMaster::is('auctions') && $seller_data) {
			// Feedback
			// Will display on both the auctions and classifieds
			$view->seller_rating_label = ($printFriendly)? $this->messages[103092]: $this->messages[102704];
			
			$view->seller_number_rates_label = ($printFriendly)? $this->messages[103093]: $this->messages[102714];
			
			if (!$printFriendly) $view->seller_rating_scale_explanation = $this->display_help_link(102826);
		}

		//auctions details
		$reverse_auction = ($show['auction_type']==3);
		if ($show["item_type"] == 2) {
			if ($reverse_auction) {
				//reverse is backwords
				if ($show["minimum_bid"] > $show["starting_bid"]) {
					$minimum_bid = $show["starting_bid"];
				} else {
					$minimum_bid = $show["minimum_bid"];
				}
			} else {
				if ($show["minimum_bid"] < $show["starting_bid"]) {
					$minimum_bid = $show["starting_bid"];
				} else {
					$minimum_bid = $show["minimum_bid"];
				}
			}

			//check reserve price
			if ($show["reserve_price"] != 0.00 && !$show["buy_now_only"]) {
				$reserve_met = false;
				//figure out if reserve is met
				if (!$reverse_auction && $show["current_bid"] >= $show["reserve_price"]) {
					//reserve met
					$reserve_met = true;
				} else if ($reverse_auction && $show["current_bid"] <= $show["reserve_price"] && $show["current_bid"] != 0.00) {
					//reserve met for reverse auction
					$reserve_met = true;
				}
			 
				if ($reserve_met) {
					//reserve is met
					$view->reserve = ($printFriendly)? $this->messages[103072]: $this->messages[102694];
				} else {
					//reserve not yer met
					$view->reserve = ($printFriendly)? $this->messages[103073]: $this->messages[102695];
				}
			} else {
			 	//NOTE: when the reserve price is not set, $reserve_met is neither true nor false and should not be relied upon
			 	$reserve_met = null;
			}
			
			if (!$printFriendly) {
				$view->auction_type_help = $this->display_help_link(103056);
			}

			if ($show["auction_type"] == 1) {
				$type_of_auction = ($printFriendly)? $this->messages[200130]: $this->messages[102707];
			} else if ($show['auction_type']==2) {
				$type_of_auction = ($printFriendly)? $this->messages[200131]: $this->messages[102708];
			} else if ($show['auction_type'] == 3) {
				$type_of_auction = ($printFriendly)? $this->messages[500982] : $this->messages[500981];
			}

			if (!$show["buy_now_only"]) {
				$view->num_bids_label = ($printFriendly)? $this->messages[103083]: $this->messages[102696];
				if ($show['auction_type']==3) {
					$view->high_bidder_label = ($printFriendly)? $this->messages[500986] : $this->messages[500985];
				} else {
					$view->high_bidder_label = ($printFriendly)? $this->messages[103112]: $this->messages[102697];
				}
				
				if ($show["current_bid"] == 0 && $show["auction_type"] != 2) {
					//no bids recieved
					$view->num_bids = ($printFriendly)? $this->messages[103113]: $this->messages[103002];
					//no high bidder
					$view->high_bidder = ($printFriendly)? $this->messages[103113]: $this->messages[103002];
				} else {
					$view->num_bids = geoListing::bidCount($id);
					$view->high_bidder = $this->get_high_bidder_username(0,$id,$reverse_auction);
				}
			}
			if ($this->debug_ad_display) {
				echo $show["buy_now"]." is \$show[\"buy_now\"]<br>\n";
				echo $show["buy_now_only"]." is buy_now_only<br>\n";
				echo $show["current_bid"]." is \$show[\"current_bid\"]<bR>\n";
				echo $show["auction_type"]." is \$show[\"auction_type\"]<br>\n";
			}
			$buy_now_label_txt = ($printFriendly)? $this->messages[200133]: $this->messages[102698];
			if ($show["auction_type"]!=2 && $show['buy_now']>0) {
				//NOT dutch auction, see if should show buy now
				$show_buy_now = false;
				if ($show['buy_now_only']) {
					//it's buy now only, of course show it...
					$show_buy_now = true;
				} else if ($show['current_bid']==0) {
					//there are no bids yet, so show buy now option
					$show_buy_now = true;
					} else if ($show['current_bid']!=0 && $this->configuration_data['buy_now_reserve']==1 && $show['reserve_price'] > 0 && !$reserve_met) {
					//there is a bid, but it is set to allow buy now until reserve is met
					//and reserve is not met yet
					$show_buy_now = true;
				}
				if ($show_buy_now) {
					$view->buy_now_label = $buy_now_label_txt;
					$view->buy_now_data = geoString::displayPrice($show["buy_now"],$precurrency,$postcurrency, 'listing');
				}
			} else if ($show['auction_type']==2) {
				//dutch auction
				if ($show["buy_now_only"]) {
					//failsafe, should not happen with normally entered listings
					return false;
				}
				
				if ($printFriendly) {
					//for some reason it uses different label depending on if there
					//are any dutch bidders, but ONLY when print friendly page
					//populate the label
					$sql = "SELECT COUNT(*) FROM ".geoTables::bid_table." WHERE `auction_id`=".$show["id"];
					$bid_count = $this->db->GetOne($sql);
					
					if ($bid_count > 0) {
						$view->winning_dutch_bidders_label = $this->messages[103081];
					} else {
						$view->winning_dutch_bidders_label = $this->messages[103082];
					}
				} else {
					$view->winning_dutch_bidders_label = $this->messages[102709];
				}
			}
			
			if ($this->debug_ad_display_time) echo $this->get_end_time()." after bidding info placed<br/>\n";
			if ($show['quantity']>1 && $show['price_applies']=='item') {
				$view->quantity_label = $this->messages[502104];
				$view->quantity = $show['quantity_remaining'];
				$view->starting_quantity = $show['quantity'];
			} else {
				$view->quantity_label = ($printFriendly)? $this->messages[103084]: $this->messages[102699];
				$view->quantity = $show["quantity"];
			}
			
			$view->auction_type_label = ($printFriendly)? $this->messages[103087]: $this->messages[102700];
			$view->auction_type_data = $type_of_auction;
			
			if (!$show['buy_now_only']) {
				if ($show['auction_type']==3) {
					$view->minimum_label = ($printFriendly)? $this->messages[500984] : $this->messages[500983];
				} else {
					$view->minimum_label = ($printFriendly)? $this->messages[103089]: $this->messages[102702];
				}
				$view->minimum_bid = geoString::displayPrice($minimum_bid,$precurrency,$postcurrency, 'listing');
				
				$view->starting_label = ($printFriendly)? $this->messages[103091]: $this->messages[102703];
				$view->starting_bid = geoString::displayPrice($show["starting_bid"],$precurrency,$postcurrency, 'listing');
				
				//make the current bid available to use
				$view->current_bid = geoString::displayPrice($show['current_bid'], $precurrency, $postcurrency, 'listing');
			}

			if ($browser_user_id == $show["seller"]) {
				$view->reserve_label = ($printFriendly)? $this->messages[103090]: $this->messages[102966];
				$view->reserve_bid = geoString::displayPrice($show["reserve_price"],$precurrency,$postcurrency, 'listing');
			}
			
			if ($this->debug_ad_display_time) echo $this->get_end_time()." after pricing placed<br/>\n";
		}
		
		//payment_types
		if ($this->fields->payment_types->is_enabled) {
			//payment_types - both on and off-site combined (for now)
			//in future may seperate them if there is a demand, for now they
			//are displayed in the same list.
			$payment_options = array();
			if ($show["item_type"] == 2 && geoPC::is_ent()) {
				//on-site payment options
				$vars = array (
					'listing_id' => $id,
				);
				$this_payment_options = geoSellerBuyer::callDisplay('displayPaymentTypesListing', $vars, ', ');
				if (strlen($this_payment_options) > 0) {
					$payment_options[0] = $this_payment_options;
				}
			}
			
			
			//off-site payment options
			$show["payment_options"] = geoString::fromDB($show["payment_options"]);
			$this_payment_options = str_replace("||",", ",$show["payment_options"]);
			if (strlen($this_payment_options) > 0){
				$payment_options[1] = $this_payment_options;
			}
			if (trim(implode(' ',$payment_options))) {
				$view->payment_options_label = ($printFriendly)? $this->messages[103086]: $this->messages[102853];
				$view->payment_options = implode(', ',$payment_options);
			}
		}
		
		$member_since = ($seller_data)? date(trim($this->configuration_data['member_since_date_configuration']), $seller_data->date_joined) : '';
		$view->member_since = $member_since;
		
		if ($this->fields->email->type_data == 'reveal' && ($show["expose_email"]) && $show["email"]) {
			$view->public_email_label = ($printFriendly)? $this->messages[1474]: $this->messages[1344];
			$view->public_email = geoString::fromDB($show["email"]);
		}

		if ($this->fields->phone_1->is_enabled && $show["phone"]) {
			$view->phone_label = ($printFriendly)? $this->messages[1475]: $this->messages[1347];
			$formatted_phone_number = $this->format_phone_data(geoString::fromDB($show["phone"]));
			$view->phone_data = $formatted_phone_number;
		}

		if ($this->fields->phone_2->is_enabled && $show["phone2"]) {
			$view->phone2_label = ($printFriendly)? $this->messages[1476]: $this->messages[1348];
			$formatted_phone_number = $this->format_phone_data(geoString::fromDB($show["phone2"]));
			$view->phone2_data = $formatted_phone_number;
		}

		if ($this->fields->fax->is_enabled && $show["fax"]) {
			$view->fax_label = ($printFriendly)? $this->messages[1477]: $this->messages[1349];
			$formatted_phone_number = $this->format_phone_data(geoString::fromDB($show["fax"]));
			$view->fax_data = $formatted_phone_number;
		}

		if ($this->fields->url_link_1->is_enabled) {
			if (strlen(trim($show["url_link_1"])) > 0) {
				$url = trim(geoString::fromDB($show['url_link_1']));
				
				if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
					$url = 'http://'.$url;
				}
				
				$view->url_link_1_href = $url;
			}
		}

		if ($this->fields->url_link_2->is_enabled) {
			if (strlen(trim($show["url_link_2"])) > 0) {
				$url = trim(geoString::fromDB($show['url_link_2']));
				
				if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
					$url = 'http://'.$url;
				}
				
				$view->url_link_2_href = $url;
			}
		}

		if ($this->fields->url_link_3->is_enabled) {
			if (strlen(trim($show["url_link_3"])) > 0) {
				$url = trim(geoString::fromDB($show['url_link_3']));
				
				if (stripos($url, 'http://') !== 0 && stripos($url, 'https://') !== 0) {
					$url = 'http://'.$url;
				}
				
				$view->url_link_3_href = $url;
			}
		}

		if ($seller_data) {
			//Give raw seller's data
			$view->seller_data_raw = $seller_data->toArray();
			$view->seller_username = $seller_data->username;
			$view->seller_first_name = $seller_data->firstname;
			$view->seller_last_name = $seller_data->lastname;
			if($seller_data->url) {
				if (stristr(stripslashes($seller_data->url), urldecode("http://"))) {
					$url_current_line = "<a href=\"".$seller_data->url."\" onclick=\"window.open(this.href); return false;\">".$seller_data->url."</a>";
				} else {
					$url_current_line = "<a href=\"http://".$seller_data->url."\" onclick=\"window.open(this.href); return false;\">".$seller_data->url."</a>";
				}
				$view->seller_url = $url_current_line;
			}
			$view->seller_address = $seller_data->address." ".$seller_data->address_2;
			$view->seller_city = $seller_data->city;
			$view->seller_state = geoRegion::getStateNameForUser($seller_data->id);
			$view->seller_country = geoRegion::getCountryNameForUser($seller_data->id);
			$view->seller_zip = $seller_data->zip;
			$view->seller_phone = geoNumber::phoneFormat($seller_data->phone);
			$view->seller_phone2 = geoNumber::phoneFormat($seller_data->phone2);
			$view->seller_fax = geoNumber::phoneFormat($seller_data->fax);
			$view->seller_company_name = $seller_data->company_name;
			$view->seller_optional_1 = $seller_data->optional_field_1;
			$view->seller_optional_2 = $seller_data->optional_field_2;
			$view->seller_optional_3 = $seller_data->optional_field_3;
			$view->seller_optional_4 = $seller_data->optional_field_4;
			$view->seller_optional_5 = $seller_data->optional_field_5;
			$view->seller_optional_6 = $seller_data->optional_field_6;
			$view->seller_optional_7 = $seller_data->optional_field_7;
			$view->seller_optional_8 = $seller_data->optional_field_8;
			$view->seller_optional_9 = $seller_data->optional_field_9;
			$view->seller_optional_10 = $seller_data->optional_field_10;
		}

		if ($this->debug_ad_display_time) echo $this->get_end_time()." after seller info placed<br/>\n";
		
		if (!$printFriendly) {
			if ($this->debug_ad_display_time) echo $this->get_end_time()." after images and info placed<br/>\n";
			for ($i = 1; $i <= 20; $i++) {
				//Set additional text 1-20
				$view_var = "additional_text_{$i}";
				$txt = $this->messages[(500052 + $i)];
				$view->$view_var = $txt;
			}
		}
		
		//offsite video data
		if ($isPreview && !$this->offsite_videos_from_db) {
			$offsite_videos = $this->offsite_videos;
			if ($offsite_videos) {
				//data from order item, not from database, so must add in vars
				//that are in the DB data
				foreach ($offsite_videos as $key => $video) {
					//slot is the key
					$offsite_videos[$key]['slot'] = $key;
					//so far, every video is going to be youtube
					$offsite_videos[$key]['video_type'] = 'youtube';
				}
				//save it as extra data for listing object, that way the offsite video
				//block can pull up the info even though it's just a preview
				$listing->e_offsite_videos = $offsite_videos;
			}
		}
		
		$view->offsite_videos_title = $this->messages[500935];
		
		//seller ID
		$view->seller_id = (int)$show['seller'];
		
		//replace/remove some text for anonymous listings
		$anon = geoAddon::getRegistry('anonymous_listing');
		$listingIsAnon = false;
		if($anon) {
			$anon_user_id = $anon->get('anon_user_id',false);
			$anon_user_name = $anon->get('anon_user_name','Anonymous');
		} else {
			$anon_user_id = false;
		}
		if ($anon && ($show["seller"] == $anon_user_id)) {
			//this is anonymous -- don't show seller-specific stuff
			//$view->seller_label = '';
			$view->seller = $anon_user_name; //pull name from addon setting
			$view->member_since = '';
			$view->additional_text_17 = ''; //member_since label
			$listingIsAnon = true;
		}
		
		
		//whether or not to show edit and delete buttons
		
		//can edit if user logged in and seller = user, or user is admin user, or
		//user permitted because of addon authorization (such as multi-admin)
		//also show edit button if this is an anonymous listing (and the user is not logged in)
		$view->can_edit = !$isPreview && (($browser_user_id && $browser_user_id == $show['seller']) || $browser_user_id == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL) || ($listingIsAnon && $browser_user_id == 0));
		
		//can delete if user is admin user, or user permitted because of addon 
		//authorization (such as multi-admin)
		$view->can_delete = !$isPreview && ($browser_user_id == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL));
		
		
		//Make all listing "raw" data available
		$view->listing_data_raw = $show;
		
		if($this->db->get_site_setting('gallery_style') === 'photoswipe') {
			//add photoswipe library files
			geoView::getInstance()
				->addCssFile(geoTemplate::getUrl('css','system/photoswipe/photoswipe.css'))
				->addCssFile(geoTemplate::getUrl('js','system/photoswipe/default-skin/default-skin.css')) //NOTE: this css file is intentionally in the 'js' folder
				->addJScript(geoTemplate::getUrl('js','system/photoswipe/photoswipe.js'))
				->addJScript(geoTemplate::getUrl('js','system/photoswipe/photoswipe-ui-default.js'));
			define('GEO_PHOTOSWIPE_LOADED',1);
		}

		//increase view count
		$sql = "update ".$this->classifieds_table." set
			viewed = ".($show["viewed"] + 1)." where id = ".$id;
		$viewed_result = $this->db->Execute($sql);
		if ($this->debug_ad_display) echo $sql."<br/>\n";
		if (!$viewed_result) {
			if ($this->debug_ad_display) echo $sql."<br/>\n";
			$this->error_message = $this->messages[81];
			return false;
		}
		$view->item_type = $show['item_type'];
		
		if (!$browser_user_id && geoPC::is_ent()) {
			//user not logged in, see if there is any fields to "hide"
			$regHidden = geoAddon::getRegistry('_core',true);
			$hiddenFields = $regHidden->hiddenFields;
			if ($hiddenFields) {
				$hiddenFields = array_keys($hiddenFields);
				foreach ($hiddenFields as $hideField) {
					if (isset($view->$hideField)) {
						//unset that field so it is not displayed.
						unset($view->$hideField);
					}
				}
			}
		}
		
		$publicQuestionLimit = $this->db->get_site_setting('public_questions_to_show');
		if ($publicQuestionLimit > 0) {
			$publicQuestions = array();
			//get all questions for this listing, most recent first
			$sql = "SELECT * FROM ".geoTables::user_communications_table." WHERE `regarding_ad` = ? and `public_question` = 1 ORDER BY `date_sent` DESC";
			$pq_result = $this->db->Execute($sql, array($id));
			while($pq_result && $message = $pq_result->FetchRow()) {
				$publicQuestions[$message['message_id']] = array(
					'question' => geoString::fromDB($message['body_text']),
					'asker' => geoUser::userName($message['message_from']),
					'asker_id' => $message['message_from'],
					'time' => date($this->db->get_site_setting('entry_date_configuration'),$message['date_sent']),
					'answer' => false //populate this below
				);
			}
			//get answers for those questions, stopping if we reach the admin-set limit of questions to show
			$numAnswers = 0;
			foreach($publicQuestions as $question_id => $question) {
				$sql = "SELECT `body_text` FROM ".geoTables::user_communications_table." WHERE `replied_to_this_message` = ? AND `public_answer` = 1 order by `date_sent` DESC";
				$answer = geoString::fromDB($this->db->GetOne($sql, array($question_id)));
				if($answer) {
					$publicQuestions[$question_id]['answer'] = $answer;
					if(++$numAnswers >= $publicQuestionLimit) {
						//don't add any more
						break; 
					}
				}
			}
			$view->publicQuestionsLabel = $this->messages[500894];
			$view->publicQuestions = ($numAnswers > 0) ? $publicQuestions : false;
			$view->noPublicQuestions = $this->messages[500901];
			$view->askAQuestionText = $this->messages[500902];
			$view->usePublicQuestions = true;
		} else {
			$view->usePublicQuestions = false;
		}
		
		if (geoPC::is_ent()) {
			//pull in some meta-data about votes on this listing, and make it available to show
			$voteText = $this->db->get_text(true, 115);
			$voteSummary = array();
			$totalVotes = $show['one_votes'] + $show['two_votes'] + $show['three_votes'];
			if ($totalVotes == 0) {
				//no votes yet!
				$voteSummary = array(
					'votes' => 0,
					'percent' => 0, 
					'text' => $voteText[500903]
				);
			} else {
				//figure out which vote category has the most votes
				if($show['two_votes'] >= $show['three_votes'] && $show['two_votes'] > $show['one_votes']) {
					//plurality of votes are neutral. 
					//use >= to prefer this over level 3 in a tie, and > to prefer level 1 over this in a tie
					$voteSummary = array(
						'votes' => $show['two_votes'],
						'percent' => round($show['two_votes'] / $totalVotes * 100),
						'text' => $voteText[2010]
					);
				} elseif($show['three_votes'] > $show['two_votes'] && $show['three_votes'] > $show['one_votes']) {
					//plurality of votes are negative
					$voteSummary = array(
						'votes' => $show['three_votes'],
						'percent' => round($show['three_votes'] / $totalVotes * 100),
						'text' => $voteText[2011]
					);
				} else {
					//plurality of votes are positive
					$voteSummary = array(
						'votes' => $show['one_votes'],
						'percent' => round($show['one_votes'] / $totalVotes * 100),
						'text' => $voteText[2009]
					);
				}
			}
			$voteSummary['total'] = $totalVotes;
			$view->voteSummary = $voteSummary;
		}
		
		
		geoAddon::triggerUpdate('notify_Display_ad_display_classified_after_vars_set',array('id'=> $id,'return'=>$return,'preview'=>$preview, 'autoDisplay'=>$autoDisplay));
		if($view->getListingVarsOnly) {
			//easy way for addons to short-circuit the template code
			//so they can display a listing in their own template without duplicating all that code above
			return true;
		}
		
		//Use new Smarty templates
		if (!isset($view->geo_inc_files['body_html_addon']) && !isset($view->geo_inc_files['body_html_system'])) {
			$view->setLanguage($this->db->getLanguage());
			$view->setCategory($show["category"]);
			if ($printFriendly) {
				$page_id = '69';
			} else {
				$page_id = '1';
			}
			if ($show["item_type"] == 1) {
				$page_id .= '_classified';
			} else {
				$page_id .= '_auction';
			}
			
			$tpl_file = $view->getTemplateAttachment($page_id);
			$view->setPage($this);
			$view->loadModules($page_id);
			
			if ($printFriendly) {
				$view->forceTemplateAttachment($tpl_file);
			} else {
				$view->setBodyTpl($tpl_file);
			}
		}
		if ($return) {
			return $view->render($page_id,true);
		}
		
		if ($autoDisplay) {
			$this->display_page($preview);
		}
		return true;
	} //end of function display_classifed

//#################################################################################


	function format_phone_data($phone_number=0)
	{
		return geoNumber::phoneFormat($phone_number);
	} //end of function format_phone_data

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

} // end of class Display_ad

