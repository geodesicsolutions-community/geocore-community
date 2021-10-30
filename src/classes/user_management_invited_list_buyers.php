<?php

class Invited_list_buyers extends geoSite
{

    var $item_id;
    var $user_id;
    var $feedback_messages;
    var $user_data;
    var $search_error_message;

    // Debug variables
    var $filename = "user_management_invited_list_buyers.php";
    var $function_name;

    var $debug_invited = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function __construct()
    {
        parent::__construct();
        $this->user_id = geoSession::getInstance()->getUserId();
        $this->users = $this->classified_user_id;
        $this->user_data = $this->get_user_data($this->user_id);
    } //end of function Invited_list_buyers

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function list_search_invited_buyers_results($db, $search = 0)
    {
        if (!$this->user_id) {
            return false;
        }
        $this->page_id = 10184;
        $msgs = $this->db->get_text(true, $this->page_id);
        $tpl = new geoTemplate('system', 'user_management');

        if ($search && ($search["text_to_search"] != "0" || $search['field_type'] == 3)) {
            $tpl->assign('search', true);
            //display the results from the search
            $this->sql_query = "select id,username, email, feedback_score from " . $this->userdata_table . " where level = 0 and ";
            $this->sql_query .= "id != " . $this->user_id . " and ";
            $this->select_query = "select user_id from " . $this->invitedlist_table . " where seller_id =" . $this->user_id . " ";
            $select_result = $db->Execute($this->select_query);
            if ($this->debug_invited) {
                echo $this->sql_query . "<br />\n";
            }
            if ($this->debug_invited) {
                echo $this->select_query . "<br />\n";
            }
            if ($select_result) {
                $records = 0;
                if ($select_result->RecordCount() > 0) {
                    $records = $select_result->RecordCount();
                    $this->sql_query .= " id NOT IN (" . $this->user_id . " , ";
                    for ($i = 0; $i  < $records - 1; $i++) {
                        $select_list = $select_result->FetchNextObject();
                        $this->sql_query .= $select_list->USER_ID . ", ";
                    }
                    $select_list = $select_result->FetchNextObject();
                    $this->sql_query .= $select_list->USER_ID . ") and ";
                }
            }

            $search["text_to_search"] = str_replace("%", "", $search["text_to_search"]);

            $anon = geoAddon::getRegistry('anonymous_listing');
            if ($anon) {
                //if anonymous is on, don't let the Anonymous user show up in search results
                $this->sql_query .= " `id` <> '" . $anon->get('anon_user_id') . "' AND ";
            }

            if (strlen(trim($search["text_to_search"])) != 0) {
                $query_data = array();
                if ($search["field_type"] == 3) {
                    $query_data[] = intval($search['text_to_search']);
                    $this->sql_query .= " feedback_score >= ? order by feedback_score ";
                } elseif ($search["field_type"] == 2) {
                    $query_data[] = '%' . str_replace('%', '', trim($search['text_to_search'])) . '%';
                    $this->sql_query .= " email LIKE ? order by feedback_score ";
                } elseif ($search["field_type"] == 1) {
                    $query_data[] = '%' . trim($search['text_to_search']) . '%';
                    $this->sql_query .= " username LIKE ? order by feedback_score ";
                }

                $invitedlist_result = $db->Execute($this->sql_query, $query_data);
                if (!$invitedlist_result) {
                    $this->site_error();
                    return false;
                } elseif ($invitedlist_result->RecordCount() > 0) {
                    if ($this->db->get_site_setting('display_email_invite_black_list')) {
                        $tpl->assign('showEmail', true);
                    }
                    $tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=20&amp;c=2");

                    $count = 0;
                    $users = array();
                    while ($show_list = $invitedlist_result->FetchNextObject()) {
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
        $tpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name') . "?a=4");
        $this->body = $tpl->fetch('whitelist/search_results.tpl');

        $searchTpl = new geoTemplate('system', 'user_management');
        $searchTpl->assign('searchFormTarget', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=20&amp;c=1");
        $searchTpl->assign('searchError', $this->search_error_message);
        $searchTpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name') . "?a=4");
        $this->body .= $searchTpl->fetch('whitelist/search_form.tpl');

        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function update_invited_users($db, $users = 0)
    {
        if (!$users) {
            $this->site_error();
            return false;
        }

        $insertCount = (int)$users['insertcount'];
        $updateCount = (int)$users['updatecount'];
        if ($insertCount != 0) {
            for ($i = 0; $i <= $insertCount; $i++) {
                $users['user_id'][$i] = (int)$users['user_id'][$i];
                if ($users['user_id'][$i]) {
                    $this->sql_query = "select * from " . $this->invitedlist_table . " where seller_id = " . $this->user_id . " and user_id = " . $users['user_id'][$i];
                    $check_result = $db->Execute($this->sql_query);
                    if (!$check_result) {
                        $this->site_error();
                        return false;
                    } elseif ($check_result->RecordCount() == 0) {
                        $this->sql_query = "insert into " . $this->invitedlist_table . " 
							(seller_id,user_id)
							values 
							(" . $this->user_id . ", " . $users['user_id'][$i] . ")  ";
                        $insert_result = $db->Execute($this->sql_query);
                        if (!$insert_result) {
                            $this->site_error();
                            return false;
                        }
                    }
                }
            }
        } elseif ($updateCount != 0) {
            for ($i = 0; $i < $updateCount; $i++) {
                $users['user_id'][$i] = (int)$users['user_id'][$i];
                if ($users['user_id'][$i]) {
                    $this->delete_query = "delete from " . $this->invitedlist_table . " where seller_id =" . $this->user_id . " and user_id = " . $users['user_id'][$i] . "  ";
                    $delete_result = $db->Execute($this->delete_query);
                    if (!$delete_result) {
                        $this->site_error();
                        return false;
                    }
                }
            }
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function list_buyers($db, $search = 0)
    {
        if ($this->user_id) {
            $this->page_id = 10184;
            $this->get_text();
            $this->advanced_user_search($db);
            $this->function_name = "list_buyers";
            if (!$search["text_to_search"] && $search["text_to_search"] != "0") {
                $this->list_invited_buyers($db);
                $this->advanced_user_search($db);
            }
        } else {
            return false;
        }
    } //end of function list_buyers


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function list_invited_buyers($db)
    {
        if (!$this->user_id) {
            return false;
        }

        $feedback_score = 1;
        $this->page_id = 10184;
        $this->get_text();
        $tpl = new geoTemplate('system', 'user_management');

        $this->sql_query = "select user_id from " . $this->invitedlist_table . " where seller_id = " . $this->user_id;
        $invitedlist_result = $db->Execute($this->sql_query);

        if (!$invitedlist_result) {
            $this->site_error();
            return false;
        } elseif ($invitedlist_result->RecordCount() > 0) {
            $tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=20&amp;c=2");
            $tpl->assign('showUsers', true);

            $users = array();
            $count = 0;
            while ($show_list = $invitedlist_result->FetchNextObject()) {
                $this->sql_query = "select id,username,email,feedback_score from " . $this->userdata_table . " where id = " . $show_list->USER_ID;
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
            //there are no invited buyers for this seller
            $tpl->assign('showUsers', false);
        }

        $this->body = $tpl->fetch('whitelist/whitelisted_buyers.tpl');

        $searchTpl = new geoTemplate('system', 'user_management');
        $searchTpl->assign('searchFormTarget', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=20&amp;c=1");
        $searchTpl->assign('searchError', $this->search_error_message);
        $searchTpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name') . "?a=4");
        $this->body .= $searchTpl->fetch('whitelist/search_form.tpl');

        $this->display_page($db);
        return true;
    } //end of function list_invited_buyers

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} // end of Invited_list_buyers
