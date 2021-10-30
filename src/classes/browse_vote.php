<?php

//browse_vote.php


if (!geoPC::is_ent()) {
    return false;
}

class browse_vote extends geoSite
{
    var $error_vote = 0;
    var $debug_vote = 0;

//########################################################################

    function Browse_vote($db = 0, $classified_user_id, $language_id, $category_id = 0, $page = 0, $classified_id = 0, $filter_id = 0, $product_configuration = 0)
    {
        if ($category_id) {
            $this->site_category = (int)$category_id;
        } elseif ($classified_id) {
            $classified_id = (int)$classified_id;
            $show = $this->get_classified_data($classified_id);
            $listing = geoListing::getListing($classified_id);
            if ($listing && $listing->category) {
                $this->site_category = (int)$listing->category;
            }
            $this->classified_id = $classified_id;
        } else {
            $this->site_category = 0;
        }
        if ($limit) {
            $this->browse_limit = (int)$limit;
        }

        $db = $this->db = DataAccess::getInstance();

        $this->get_ad_configuration($db);

        parent::__construct();

        if ($page) {
            $this->page_result = (int)$page;
        } else {
            $this->page_result = 1;
        }

        $this->filter_id = (int)$filter_id;
    } //end of function Browse_vote

//###########################################################

    function voting_form($db, $classified_id = 0)
    {
        $classified_id = (int)$classified_id;
        if (!$classified_id || !$this->db->get_site_setting('voting_system')) {
            return false;
        }
        $this->page_id = 116;
        $this->get_text();
        $tpl = new geoTemplate('system', 'voting');
        $tpl->assign('backToCurrentAdLink', $this->db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $classified_id);
        if (geoSession::getInstance()->getUserID()) {
            $classified_data = $this->get_classified_data($classified_id);
            $user_data = $this->get_user_data($classified_data->SELLER);

            if ($classified_data->SELLER == geoSession::getInstance()->getUserID()) {
                //can't vote on your own ads!
                $tpl->assign('noVoteReason', urldecode($this->messages[500076]));
            } else {
                $tpl->assign('formTarget', $this->db->get_site_setting('classifieds_url') . "?a=26&b=" . $classified_id);


                if ($this->error_vote > 0) {
                    $tpl->assign('error', true);
                }
                $tpl->assign('title', stripslashes(urldecode($classified_data->TITLE)));
            }
        } else {
            //must be logged in to vote
            $tpl->assign('noVoteReason', urldecode($this->messages[1995]));
        }
        $this->body = $tpl->fetch('voting_form.tpl');
        $this->display_page();
        return true;
    }

//########################################################################

    function collect_vote($classified_id = 0, $info = 0)
    {
        $classified_id = (int)$classified_id;
        if (!($classified_id && $info)) {
            trigger_error('DEBUG VOTE: Not enough information to process the vote');
            return false;
        }
        $db = DataAccess::getInstance();
        if (!$db->get_site_setting('voting_system')) {
            trigger_error('DEBUG VOTE: Can\'t collect vote -- Voting system turned off in admin?');
            return false;
        }

        $this->page_id = 116;
        $this->get_text();
        $listing = geoListing::getListing($classified_id);
        if (!$listing) {
            trigger_error('DEBUG VOTE: failed getting listing');
        }


        if ($listing->seller == geoSession::getInstance()->getUserID()) {
            //can't vote on your own listing!
            trigger_error('DEBUG VOTE: User tried to vote on her own listing.');
            $this->error_vote++;
            return false;
        }
        if (!(strlen(trim($info["vote_title"])) > 0 && strlen(trim($info["vote_comments"])) > 0)) {
            //vote data incomplete
            trigger_error('DEBUG VOTE: Missing vote text and/or title.');
            $this->error_vote++;
            return false;
        }
        if (!in_array($info['vote'], array(1,2,3))) {
            //invalid vote
            trigger_error('DEBUG VOTE: Invalid numerical vote.');
            $this->error_vote++;
            return false;
        }

        if ($db->get_site_setting('voting_system') == 1) {
            $sql = "select * from " . geoTables::voting_table . " where classified_id =" . $classified_id . " and voter_ip = \"" . $_SERVER["REMOTE_ADDR"] . "\"";
        } elseif ($db->get_site_setting('voting_system') == 2) {
            $sql = "select * from " . geoTables::voting_table . " where classified_id =" . $classified_id . " and user_id = " . geoSession::getInstance()->getUserID();
        } elseif ($db->get_site_setting('voting_system') == 3) {
            $sql = "select * from " . geoTables::voting_table . " where classified_id =" . $classified_id . " and ((voter_ip = \"" . $_SERVER["REMOTE_ADDR"] . "\") || (user_id = " . geoSession::getInstance()->getUserID() . "))";
        }


        $number_of_votes_result = $db->Execute($sql);
        if (!$number_of_votes_result) {
            $this->error_message = $this->messages[1998];
            return false;
        }

        $view = geoView::getInstance();

        if ($number_of_votes_result->RecordCount() > 0) {
            //user has already voted on this ad -- show failure page

            $view->page_title = geoString::fromDB($this->messages[1984]);
            $view->success_fail_message = geoString::fromDB($this->messages[1997]);
            $back['url'] = $db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $classified_id;
            $back['text'] = geoString::fromDB($this->messages[1996]);
            $view->backToCurrentAdLink = $back;

            $view->setBodyTpl('vote_results.tpl', '', 'voting');
            $this->display_page();
            return true;
        }

        //vote looks good -- add it
        $this->get_badword_array($db);
        $info["vote_title"] = $this->check_for_badwords($info["vote_title"]);
        $info["vote_comments"] = $this->check_for_badwords($info["vote_comments"]);
        $sql = "insert into " . geoTables::voting_table . "
					(classified_id,user_id,voter_ip,vote,vote_title,vote_comments,date_entered)
					values (?,?,?,?,?,?,?)";
        $queryData = array($classified_id, geoSession::getInstance()->getUserID(), getenv('REMOTE_ADDR'), $info['vote'], geoString::toDB($info['vote_title']), geoString::toDB($info['vote_comments']), geoUtil::time() );
        $register_vote_result = $db->Execute($sql, $queryData);
        if (!$register_vote_result) {
            $this->error_message = $this->messages[80];
            return false;
        }
        //update the stats in the classified ad itself
        if ($info["vote"] == 1) {
            $listing->one_votes = $listing->one_votes + 1;
        } elseif ($info["vote"] == 2) {
            $listing->two_votes = $listing->two_votes + 1;
        } elseif ($info["vote"] == 3) {
            $listing->three_votes = $listing->three_votes + 1;
        }
        $listing->vote_total = $listing->vote_total + 1;


        //display success page
        $view->page_title = geoString::fromDB($this->messages[1984]);
        $view->success_fail_message = geoString::fromDB($this->messages[1999]);
        $back['url'] = $db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $classified_id;
        $back['text'] = geoString::fromDB($this->messages[1996]);
        $view->backToCurrentAdLink = $back;

        $view->setBodyTpl('vote_results.tpl', '', 'voting');
        $this->display_page();
        return true;
    }

//########################################################################

    function browse_vote_comments($classified_id = 0)
    {
        $classified_id = (int)$classified_id;
        if (!$classified_id) {
            return false;
        }

        $view = geoView::getInstance();
        $db = DataAccess::getInstance();
        $this->page_id = 115;
        $this->get_text();

        $listing = geoListing::getListing($classified_id);
        if (!$listing) {
            trigger_error('DEBUG VOTE: failed to get listing');
            return false;
        }
        $view->listing = $listing->toArray();

        $sql = "select * from " . geoTables::voting_table . " where classified_id = " . $classified_id . "
		     order by date_entered desc 
		     limit " . (($this->page_result - 1) * $db->get_site_setting('number_of_vote_comments_to_display')) . "," . $db->get_site_setting('number_of_vote_comments_to_display');
        $result = $db->Execute($sql);
        if ((!$result)) {
            $this->error_message = urldecode($this->messages[33]);
            return false;
        }

        $sql_count = "select count(vote) from " . geoTables::voting_table . " where classified_id = " . $classified_id . " order by date_entered desc";
        $totalVotes = $db->GetOne($sql_count);
        if (!$totalVotes) {
            $totalVotes = 0;
        }


        if ($totalVotes) {
            $percent = ' %';
            $view->oneVotesPercentage = round(($listing->one_votes / $totalVotes) * 100) . $percent;
            $view->twoVotesPercentage = round(($listing->two_votes / $totalVotes) * 100) . $percent;
            $view->threeVotesPercentage = round(($listing->three_votes / $totalVotes) * 100) . $percent;
        } else {
            $noVotes = '--';
            $view->oneVotesPercentage = $noVotes;
            $view->twoVotesPercentage = $noVotes;
            $view->threeVotesPercentage = $noVotes;
        }
        $view->totalVotes = $totalVotes;

        $votesToDisplay = $db->get_site_setting('number_of_vote_comments_to_display');
        $totalPages = ceil($totalVotes / $votesToDisplay);

        $votes = array();
        for ($v = 0; $show_comment = $result->FetchNextObject(); $v++) {
            if ($show_comment->VOTE == 1) {
                $votes[$v]['voteType'] = urldecode($this->messages[2009]);
            } elseif ($show_comment->VOTE == 2) {
                $votes[$v]['voteType'] = urldecode($this->messages[2010]);
            } elseif ($show_comment->VOTE == 3) {
                $votes[$v]['voteType'] = urldecode($this->messages[2011]);
            }
            $votes[$v]['voter'] = geoUser::userName($show_comment->USER_ID);
            $votes[$v]['voter_id'] = $show_comment->USER_ID;
            $votes[$v]['title'] = geoString::fromDB($show_comment->VOTE_TITLE);
            $votes[$v]['comment'] = geoString::fromDB($show_comment->VOTE_COMMENTS);
            $votes[$v]['date'] = date($db->get_site_setting('entry_date_configuration'), $show_comment->DATE_ENTERED);
            $votes[$v]['id'] = $show_comment->VOTE_ID;
        }
        $view->votes = $votes;


        if ($votesToDisplay < $totalVotes) {
            $view->showPagination = true;
            $totalPages = ceil($totalVotes / $votesToDisplay);
            $url = $db->get_site_setting('classifieds_file_name') . "?a=27&amp;b=" . $classified_id . "&amp;page=";
            $css = "comment_result_page_links";
            $view->pagination = geoPagination::getHTML($totalPages, $this->page_result, $url, $css);
        }

        $view->backToCurrentAdLink = $db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $classified_id;

        $view->canDeleteVotes = (geoSession::getInstance()->getUserId() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;

        $view->setBodyTpl('browse_votes.tpl', '', 'voting');
        $this->display_page();
        return true;
    }

    function delete_vote($vote_id)
    {
        if (!$vote_id || !is_numeric($vote_id)) {
            return false;
        }
        $listing = geoListing::getListing($_REQUEST['b']);

        $db = DataAccess::getInstance();
        //get vote data
        $sql = "SELECT * FROM " . geoTables::voting_table . " WHERE vote_id = ?";
        $vote = $db->GetRow($sql, array($vote_id));

        $sql = "DELETE FROM " . geoTables::voting_table . " WHERE vote_id = ?";
        $result = $db->Execute($sql, array($vote_id));
        if (!$result) {
            return false;
        }

        //update classifieds table to match
        switch ($vote['vote']) {
            case 1:
                $listing->one_votes = $listing->one_votes - 1;
                break;
            case 2:
                $listing->two_votes = $listing->two_votes - 1;
                break;
            case 3:
                $listing->three_votes = $listing->three_votes - 1;
                break;
            default:
                break;
        }
        return true;
    }
}
