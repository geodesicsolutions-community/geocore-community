<?php 
//user_management_list_bids_auctions.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

class Black_list_buyers extends geoSite {

	var $auction_id;
	var $auction_user_id;
	var $feedback_messages;
	var $user_data;
	var $search_error_message;

	// Debug variables
	var $filename = "user_management_list_bids_auctions.php";
	var $function_name;
	
	var $debug_blacklist = 0;
	
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function Black_list_buyers($db,$language_id,$auction_user_id,$production_configuration=0)
	{
		parent::__construct();
		$this->auction_user_id = $auction_user_id;
		$this->user_data = $this->get_user_data($this->auction_user_id);
	} //end of function Auction_feedback

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function list_search_blacklisted_buyers_results($db,$search=0)
	{
		if (!$this->auction_user_id) {
			return false;
		}
		$this->page_id = 10183;
		$msgs = $this->db->get_text(true, $this->page_id);
		$tpl = new geoTemplate('system', 'user_management');
		
		if($search && ($search["text_to_search"] != "0" || $search['field_type'] == 3))
		{
			$tpl->assign('search', true);
			$this->sql_query = "select id,username, email, feedback_score from ".$this->userdata_table." where level = 0 and ";
			
			$this->select_query = "select user_id from ".$this->db->geoTables->blacklist_table." where seller_id =".$this->auction_user_id."
					and user_id != ".$this->auction_user_id;
			$select_result = $this->db->Execute($this->select_query);
			$this->sql_query .=" id NOT IN (".$this->auction_user_id;

			if($select_result) {
				$records = 0;
				if ($select_result->RecordCount() > 0) {
					$records = $select_result->RecordCount();
					for($i = 0 ; $i  < $records-1; $i++) {
						$select_list = $select_result->FetchNextObject();
						$this->sql_query .= ",".$select_list->USER_ID;
					}
					$select_list = $select_result->FetchNextObject();
					$this->sql_query .= ",".$select_list->USER_ID;
				}
			}
			$this->sql_query .= ") and ";
			
			$anon = geoAddon::getRegistry('anonymous_listing');
			if($anon) {
				//if anonymous is on, don't let the Anonymous user show up in search results
				$this->sql_query .= " `id` <> '".$anon->get('anon_user_id')."' AND ";
			}
			
			if (strlen(trim($search["text_to_search"]))) {
				$query_data = array();
				if($search["field_type"] == 3) {
					$query_data[] = intval($search['text_to_search']);
					$this->sql_query .= " feedback_score <= ? order by feedback_score LIMIT 20";
				} else if($search["field_type"] == 2) {
					$query_data[] = '%'.str_replace('%','',trim($search['text_to_search'])).'%';
					$this->sql_query .= " email LIKE ? order by feedback_score LIMIT 20";
				} else if($search["field_type"] == 1) {
					$query_data[] = '%'.trim($search['text_to_search']).'%';
					$this->sql_query .= " username LIKE ? order by feedback_score LIMIT 20";
				}
									
				$blacklist_result = $this->db->Execute($this->sql_query, $query_data);
				if (!$blacklist_result) {
					$this->site_error();
					return false;
				} else if ($blacklist_result->RecordCount() > 0) {
					if ($this->db->get_site_setting('display_email_invite_black_list')) {
						$tpl->assign('showEmail', true);
					}
					$tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=19&amp;c=2");
					
					$count = 0;
					$users = array();
					while($show_list = $blacklist_result->FetchNextObject())
					{
						$users[$count]['username'] = $show_list->USERNAME;
						if ($this->db->get_site_setting('display_email_invite_black_list')) {
							$users[$count]['email'] = $show_list->EMAIL;
						}
						$users[$count]['feedback'] = $show_list->FEEDBACK_SCORE;
						$users[$count]['id'] = $show_list->ID;
						$count++;
					}
					$tpl->assign('users', $users);
					$tpl->assign('count', $count);
					
					$this->search_error_message = '';
				}
			}
		} else {
			$this->search_error_message = $msgs[102983];
		}
		$tpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name')."?a=4");
		$this->body = $tpl->fetch('blacklist/search_results.tpl');
		
		$searchTpl = new geoTemplate('system','user_management');
		$searchTpl->assign('searchFormTarget', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=19&amp;c=1");
		$searchTpl->assign('searchError', $this->search_error_message);
		$searchTpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name')."?a=4");
		$this->body .= $searchTpl->fetch('blacklist/search_form.tpl');
		
		$this->display_page();
		return true;

	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function update_blacklisted_users($db,$users=0)
	{
		if ($users)
		{
			$insertCount = (int)$users['insertcount']; 
			$updateCount = (int)$users['updatecount'];
			if($insertCount > 0) {
				for($i = 0; $i < $insertCount; $i++) {
					$users['user_id'][$i] = (int)$users['user_id'][$i];
					if($users['user_id'][$i]) {
						$this->insert_query = "select * from ".$this->blacklist_table." where seller_id = ".$this->auction_user_id." and user_id = ".$users['user_id'][$i];
						$check_result = $this->db->Execute($this->insert_query);
						if (!$check_result) {
							$this->site_error();
							return false;
						} elseif ($check_result->RecordCount() == 0) {
							$this->insert_query = "insert into ".$this->blacklist_table." 
								(seller_id,user_id) 
								values 
								(".$this->auction_user_id.", ".$users['user_id'][$i].")  ";
							$insert_result = $this->db->Execute($this->insert_query);
							if (!$insert_result) {
								$this->site_error();
								return false;
							}
						}
					}
				}
			} else if($updateCount != 0) {
				for($i = 0; $i < $updateCount; $i++) {
					$users['user_id'][$i] = (int)$users['user_id'][$i];
					if($users['user_id'][$i]) {
						$this->delete_query = "delete from ".$this->blacklist_table." where seller_id =".$this->auction_user_id." and user_id = ".$users['user_id'][$i]."  ";
						$delete_result = $this->db->Execute($this->delete_query);
						if (!$delete_result) {
							$this->site_error();
							return false;
						}
					}
				}
			}
		}
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function list_blacklisted_buyers()
	{
		if (!$this->auction_user_id) {
			return false;
		}
		
		$feedback_score = 1;
		$this->page_id = 10183;
		$this->get_text();
		$tpl = new geoTemplate('system', 'user_management');
		
		$this->sql_query = "select user_id from ".$this->blacklist_table." where seller_id = ".$this->auction_user_id." ";
		$blacklist_result = $this->db->Execute($this->sql_query);
		if (!$blacklist_result) {
			$this->site_error();
			return false;
		} elseif ($blacklist_result->RecordCount() > 0) {
			$tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=19&amp;c=2");
			$tpl->assign('showUsers', true);
			
			$count = 0;
			$users = array();
			while($show_list = $blacklist_result->FetchNextObject())
			{
				$this->sql_query = "select id,username,email,feedback_score from ".$this->userdata_table." where id = ".$show_list->USER_ID;
				$result = $this->db->Execute($this->sql_query);
				if (!$result) {
					$this->site_error();
					return false;
				} elseif ($result->RecordCount() == 1) {
					$show_user = $result->FetchNextObject();
					$users[$count]['username'] = $show_user->USERNAME;
					$users[$count]['email'] = $show_user->EMAIL;
					$users[$count]['feedback'] = $show_user->FEEDBACK_SCORE;
					$users[$count]['id'] = $show_user->ID;
				}
				$count++;
			}
			$tpl->assign('users', $users);
			$tpl->assign('count', $count);
		} else {
			//there are no auction filters for this user
			$tpl->assign('showUsers', false);
		}
		
		$this->body = $tpl->fetch('blacklist/blacklisted_buyers.tpl');
		
		$searchTpl = new geoTemplate('system','user_management');
		$searchTpl->assign('searchFormTarget', $this->db->get_site_setting('classifieds_file_name')."?a=4&amp;b=19&amp;c=1");
		$searchTpl->assign('searchError', $this->search_error_message);
		$searchTpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name')."?a=4");
		$this->body .= $searchTpl->fetch('blacklist/search_form.tpl');
		
		$this->display_page();
		return true;

	}
}
