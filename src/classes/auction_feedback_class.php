<?php

//auction_feedback_class.php


class Auction_feedback extends geoSite
{
    var $auction_id;
    var $auction_user_id;
    var $feedback_messages;
    var $user_data;

    var $debug_feedback = 0;

    // Debug variables
    var $filename = "auction_feedback_class.php";
    var $function_name;
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function Auction_feedback($db, $language_id, $auction_user_id, $page = 0, $product_configuration = 0)
    {
        parent::__construct();
        $this->auction_user_id = $auction_user_id;
        $this->user_data = $this->get_user_data($this->auction_user_id);

        $page = (int)$page;
        $this->page_result = ($page) ? $page : 1;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function feedback_home()
    {
        //12/16/2015: removed the old "feedback home" page which was pretty useless and didn't have any info that wasn't already on the "feedback_about_user" page anyway
        $this->feedback_about_user(geoSession::getInstance()->getUserId());
        return true;
    }

//####################################################################################

    function feedback_about_user($user_id, $auction_id = 0, $page = 1)
    {
        //clean user input
        $user_id = (int)$user_id;
        $auction_id = (int)$auction_id;
        $page = (int)$page;

        if (!$user_id) {
            return false;
        }

        $db = DataAccess::getInstance();
        $this->page_id = 10158;
        $this->get_text();

        $db = DataAccess::getInstance();
        $view = geoView::getInstance();

        $sql = "SELECT `username`, `feedback_count`, `feedback_score`, `feedback_positive_count`, `date_joined` FROM " . geoTables::userdata_table . " WHERE `id` = ?";
        $rated_result = $db->Execute($sql, array($user_id));
        if (!$rated_result || $rated_result->RecordCount() != 1) {
            return false;
        }
        $show_rated = $rated_result->FetchNextObject();

        $view->rated_user_name = geoUser::userName($user_id);
        //just so template has this info if needed...
        $view->rated_user_id = $user_id;

        //$rated_username = $show_rated->USERNAME;
        $sql = "SELECT COUNT(*) AS total_feedbacks FROM " . geoTables::auctions_feedbacks_table . "
			WHERE `rated_user_id` = ? AND `done` = 1 ORDER BY `date` DESC ";
        $total_feedbacks = $db->GetOne($sql, array($user_id));


        if ($total_feedbacks == 0) {
            $view->no_feedbacks = true;
        }

        $feedbacksPerPage = $this->db->get_site_setting('number_of_feedbacks_to_display');
        $limit = ($page) ? (($page - 1) * $feedbacksPerPage) . ',' . $feedbacksPerPage : $feedbacksPerPage;
        if (!$limit) {
            //sanity check
            $limit = 10;
        }
        $feedbackTable = geoTables::auctions_feedbacks_table;
        $sql = "SELECT * FROM " . $feedbackTable . " WHERE `rated_user_id` = ? AND `done` = 1 ORDER BY `date` DESC LIMIT " . $limit;
        $feedback_result = $db->Execute($sql, array($user_id));
        if (!$feedback_result) {
            return false;
        }

        /*
         *  Get current time and calculate out 1, 6, and 12 month times
         */
        $current_time = geoUtil::time();
        $month = 60 * 60 * 24 * 30;
        $one_month_time = $current_time - $month;
        $six_month_time = $current_time - ($month * 6);
        $twelve_month_time = $current_time - ($month * 12);

        //***Past 1 month
        //positive
        $sql = "SELECT COUNT(*) AS one_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $one_month_time . " AND rate = 1 AND done = 1";
        $view->one_month_pos = $db->GetOne($sql, array($user_id));
        //neutral
        $sql = "SELECT COUNT(*) AS one_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $one_month_time . " AND rate = 0 AND done = 1";
        $view->one_month_neu = $db->GetOne($sql, array($user_id));
        //negative
        $sql = "SELECT COUNT(*) AS one_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $one_month_time . " AND rate = -1 AND done = 1";
        $view->one_month_neg = $db->GetOne($sql, array($user_id));

        //***Past 6 months
        //positive
        $sql = "SELECT COUNT(*) AS six_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $six_month_time . " AND rate = 1 AND done = 1";
        $view->six_month_pos = $db->GetOne($sql, array($user_id));
        //neutral
        $sql = "SELECT COUNT(*) AS six_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $six_month_time . " AND rate = 0 AND done = 1";
        $view->six_month_neu = $db->GetOne($sql, array($user_id));
        //negative
        $sql = "SELECT COUNT(*) AS six_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $six_month_time . " AND rate = -1 AND done = 1";
        $view->six_month_neg = $db->GetOne($sql, array($user_id));

        //***Past 12 months
        //positive
        $sql = "SELECT COUNT(*) AS twelve_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $twelve_month_time . " AND rate = 1 AND done = 1";
        $view->twelve_month_pos = $db->GetOne($sql, array($user_id));
        //neutral
        $sql = "SELECT COUNT(*) AS twelve_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $twelve_month_time . " AND rate = 0 AND done = 1";
        $view->twelve_month_neu = $db->GetOne($sql, array($user_id));
        //negative
        $sql = "SELECT COUNT(*) AS twelve_month_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND date >= " . $twelve_month_time . " AND rate = -1 AND done = 1";
        $view->twelve_month_neg = $db->GetOne($sql, array($user_id));

        // Get the total negative scores from the table
        $sql = "SELECT COUNT(*) AS neg_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND rate = -1 AND done = 1";
        $view->neg_count = $neg_count = $db->GetOne($sql, array($user_id));

        // Get the total positive scores from the table
        $sql = "SELECT COUNT(*) AS pos_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND rate = 1 AND done = 1";
        $view->pos_count = $pos_count = $db->GetOne($sql, array($user_id));

        // Get the total neutral scores from the table
        $sql = "SELECT COUNT(*) AS neu_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND rate = 0 AND done = 1";
        $view->neu_count = $neu_count = $db->GetOne($sql, array($user_id));

        // Get the total feedbacks count from the table
        $sql = "SELECT COUNT(*) AS total_feedback_count FROM " . $feedbackTable . " WHERE rated_user_id = ? AND done = 1";
        $view->total_count = $total_count = $db->GetOne($sql, array($user_id));

        // Get the total feedbacks score from the table
        $sql = "SELECT sum(rate) AS feedback_score FROM " . $feedbackTable . " WHERE rated_user_id = ? AND done = 1";
        $view->feedback_score = $feedback_score = $db->GetOne($sql, array($user_id));

        // Set member since
        $view->member_since = date($db->get_site_setting('member_since_date_configuration'), $show_rated->DATE_JOINED);

        $view->feedback_percentage = $feedback_percentage = ($show_rated->FEEDBACK_COUNT) ? sprintf("%01.0f", (($pos_count / $total_count) * 100)) : '0';

        // Get the number of feedbacks
        $total_returned = $feedback_result->RecordCount();

        $display_feedbacks = array();
        for ($i = 0; $show = $feedback_result->FetchRow(); $i++) {
            $auction_data = geoListing::getListing($show['auction_id'], true, true);
            $fb = array();
            if ($auction_data) {
                $auction_data = $auction_data->toArray();
            } else {
                //not available...
                $auction_data = array (
                    'seller' => 0,
                    'title' => '-',
                    );
                //let template know the original listing is gone
                $fb['listing_gone'] = true;
            }

            $user = geoUser::getUser($show['rater_user_id']);

            $fb['rater_username'] = ($user) ? $user->username : $show['rater_user_id'];
            //let template know the rated user ID even if not used by default, just
            //in case needed by custom design.
            $fb['rater_user_id'] = $show['rater_user_id'];

            if ($auction_data['seller'] != $show['rater_user_id']) {
                $fb['user_is_seller'] = true;
            }
            $fb['title'] = geoString::fromDB($auction_data['title']);
            $fb['auction_id'] = $show['auction_id'];

            if ($show['rate'] == 1) {
                $fb['rating'] = geoString::fromDB($this->messages[103363]);
            } elseif ($show['rate'] == 0) {
                $fb['rating'] = geoString::fromDB($this->messages[103364]);
            } elseif ($show['rate'] == -1) {
                $fb['rating'] = geoString::fromDB($this->messages[103365]);
            }

            $fb['date'] = date($this->db->get_site_setting('entry_date_configuration'), $show['date']);
            $fb['feedback'] = geoString::fromDB($show['feedback']);
            $display_feedbacks[$i] = $fb;
        }


        $view->display_feedbacks = $display_feedbacks;

        if ($total_count > 0) { //sanity check, because dividing by 0 is bad.
            $view->score_percentage = sprintf("%01.2f", (($feedback_score / $total_count) * 100));
        }

        if ($db->get_site_setting('number_of_feedbacks_to_display') < $total_count) {
            $totalPages = ceil($total_count / $db->get_site_setting('number_of_feedbacks_to_display'));
            $auction_str = ($auction_id) ? '&amp;b=' . $auction_id : '';
            $link = $db->get_site_setting('classifieds_url') . "?a=1030" . $auction_str . "&amp;d=" . $user_id . "&amp;p=";
            $css = 'browsing_result_page_links';
            $view->pagination = geoPagination::getHTML($totalPages, $page, $link, $css);
        }

        if ($user_id == $this->auction_user_id) {
            $view->feedback_home_link = $db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=22";
        }
        if ($auction_id) {
            $view->auction_link = $db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=" . $auction_id;
        }

        $view->setBodyTpl('feedback/feedback_about_user.tpl', '', 'auctions');

        $this->display_page($db);
        return true;
    }

//####################################################################################

    function list_open_feedback($db, $user_id = 0)
    {
        $this->page_id = 10159;
        $this->get_text();
        $user_id = intval($user_id);
        if (!$user_id) {
            return false;
        }

        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $sql = "select * from " . geoTables::auctions_feedbacks_table . " where rater_user_id=? AND done=0";
        $result = $db->Execute($sql, array($user_id));

        if (!$result) {
            return false;
        } elseif ($result->RecordCount() == 0) {
            //no feedbacks open
            $view->no_open_feedbacks = true;
        } else {
            //there are auctions this user can leave feedback for

            $feedbacks = array();
            for ($i = 0; $show = $result->FetchNextObject(); $i++) {
                //pull from the feedback table with this users criteria
                //if nothing comes back then this user has not rated this auction yet
                //if something comes back this user has already rated this auction
                $sql = "SELECT * FROM " . geoTables::classifieds_table . " WHERE id = ?";
                $auction_result = $db->Execute($sql, array($show->AUCTION_ID));

                if (!$auction_result) {
                    return false;
                }
                if ($auction_result->RecordCount() == 1) {
                    $show_auction = $auction_result->FetchNextObject();
                    $unarchived = true;
                } elseif ($auction_result->RecordCount() == 0) {
                    $unarchived = false;
                    //get auction data from expired table
                    $sql = "SELECT * FROM " . geoTables::classifieds_expired_table . " WHERE `id` = ?";
                    $auction_result = $db->Execute($sql, array($show->AUCTION_ID));

                    if ($auction_result->RecordCount() == 1) {
                        $show_auction = $auction_result->FetchNextObject();
                    }
                }

                if (!$show_auction) {
                    //can't find this auction -- don't show it
                    continue;
                }

                $user = geoUser::getUser($show->RATED_USER_ID);
                $feedbacks[$i]['rated_user'] = ($user) ? $user->username : $show->RATED_USER_ID;
                $feedbacks[$i]['rated_email'] = ($user) ? $user->email : '';

                $feedbacks[$i]['title'] = geoString::fromDB($show_auction->TITLE);
                $price = $show_auction->FINAL_PRICE;
                if ($price <= 0 && $show_auction->BUY_NOW_ONLY && $show_auction->PRICE_APPLIES == 'item') {
                    //use buy now price.. note that the quantity purchased is not saved,
                    //and not possible to "look up" based on what is saved (there can
                    //be multiple purchases by the same buyer even for same item so not
                    //able to accurately look it up from bid table)
                    //so solution: just add "per item" to price text.
                    $price = $show_auction->BUY_NOW;
                    $feedbacks[$i]['final_price'] = geoString::displayPrice($price, $show_auction->PRECURRENCY, $show_auction->POSTCURRENCY) . $this->messages[502197];
                } else {
                    //normal price, don't have to qualify with "per item"
                    $feedbacks[$i]['final_price'] = geoString::displayPrice($price, $show_auction->PRECURRENCY, $show_auction->POSTCURRENCY);
                }
                $feedbacks[$i]['startDate'] = date($db->get_site_setting('entry_date_configuration'), $show_auction->DATE);
                $feedbacks[$i]['endDate'] = date($db->get_site_setting('entry_date_configuration'), $show_auction->ENDS);

                $this->body .= "<td align=\"center\">";
                if ($show_auction->SELLER == $show->RATED_USER_ID) {
                    $feedbacks[$i]['rated_is_seller'] = true;
                    $feedbacks[$i]['reply_link'] = $db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=22&amp;c=2&amp;d=" . $show->AUCTION_ID;
                } else {
                    $feedbacks[$i]['reply_link'] = $db->get_site_setting('classifieds_file_name') . "?a=4&b=22&c=2&d=" . $show->AUCTION_ID . "&f=" . $show->RATED_USER_ID;
                }

                $feedbacks[$i]['auction_link'] = $db->get_site_setting('classifieds_file_name');
                if ($unarchived) {
                    $feedbacks[$i]['auction_link'] .= "?a=2&amp;b=" . $show->AUCTION_ID;
                } else {
                    $feedbacks[$i]['auction_link'] .= "?a=4&amp;b=2&amp;c=" . $show->AUCTION_ID;
                }
            }//end of while
            $view->feedbacks = $feedbacks;
        }
        $view->feedback_home_link = $db->get_site_setting('classifieds_file_name') . "?a=4&b=22";
        $view->user_management_home_link = $db->get_site_setting('classifieds_file_name') . "?a=4";

        $view->setBodyTpl('feedback/list_open_feedback.tpl', '', 'auctions');

        $this->display_page();
        return true;
    }

//####################################################################################

    function leave_feedback($db = 0, $user_id = 0, $auction_id = 0, $info = 0, $rated_id = 0)
    {
        $this->page_id = 10160;
        $this->get_text();

        $auction_id = (int)$auction_id;
        if (!$user_id || !$auction_id) {
            $this->error_message = urldecode($this->messages[102519]);
            return false;
        }

        $db = DataAccess::getInstance();
        $view = geoView::getInstance();

        $rated_id = intval($rated_id);
        if (!$rated_id && isset($info['rated_id'])) {
            //rated id not sent through URL, see if it is set in info array
            $rated_id = intval($info['rated_id']);
        }

        $sql = "SELECT * FROM " . geoTables::classifieds_table . " WHERE `id` = ?";
        $result = $db->Execute($sql, array($auction_id));


        if (!$result) {
            return false;
        } elseif ($result->RecordCount() == 1) {
            $show_auction = $result->FetchNextObject();
        } elseif ($result->RecordCount() == 0) {
            //get auction data from expired table
            $sql = "SELECT * FROM " . $this->classifieds_expired_table . " WHERE id = ?";
            $result = $this->db->Execute($sql, array($auction_id));

            if ($result->RecordCount() == 1) {
                $show_auction = $result->FetchNextObject();
            } else {
                return false;
            }
        }

        $sql = "select * from " . geoTables::auctions_feedbacks_table . " where rater_user_id = ? and auction_id = ?";
        if ($show_auction->AUCTION_TYPE == 2) {
            //special cases for dutch auctions
            if ($rated_id) {
                $sql .= " and rated_user_id = " . intval($rated_id);
            } elseif ($show_auction->SELLER != $user_id) {
                $rated_id = $show_auction->SELLER;
            }
        }
        //there can be multiple feedback for same auction, so make sure we get one
        //that is not done yet if there is one
        $sql .= " ORDER BY `done` ASC";

        $result = $this->db->Execute($sql, array($user_id,$auction_id));

        if (!$result) {
            return false;
        }

        $show = $result->FetchNextObject();

        if ($show->DONE == 1) {
            //already left feedback for this auction
            $view->already_feedbacked = true;
        }

        $id = ($show_auction->AUCTION_TYPE == 2) ? $rated_id : $show->RATED_USER_ID;
        $view->username = geoUser::userName($id);

        $view->formTarget = $db->get_site_setting('classifieds_file_name') . "?a=4&b=22&c=2&d=" . $auction_id;
        if (($show_auction->AUCTION_TYPE == 2) && ($rated_id)) {
            $view->hidden_rated_id = $rated_id;
        }

        $view->auction_id = $auction_id;
        $view->title = geoString::fromDB($show_auction->TITLE);

        $view->startDate = date($db->get_site_setting('entry_date_configuration'), $show_auction->DATE);
        $view->endDate = date($db->get_site_setting('entry_date_configuration'), $show_auction->ENDS);

        if (strlen(trim($this->feedback_messages["rating"])) > 0) {
            $view->rating_error = $this->feedback_messages["rating"];
        }
        if (strlen(trim($this->feedback_messages["feedback"])) > 0) {
            $view->feedback_error = $this->feedback_messages["feedback"];
        }

        $view->feedback = $info["feedback"];

        $view->setBodyTpl('feedback/leave_feedback.tpl', '', 'auctions');

        $this->display_page($db);
        return true;
    }

//####################################################################################

    function check_feedback($db, $auction_id = 0, $user_id = 0, $info = 0)
    {
        $db = DataAccess::getInstance();
        $this->page_id = 10160;
        $this->get_text();
        $error = 0;

        $feedback = ltrim(chop($info["feedback"]));
        if (strlen(trim($feedback)) == 0) {
            $error++;
            $this->feedback_messages["feedback"] = urldecode($this->messages[102521]);
        }

        if (empty($info["rating"])) {
            $error++;
            $this->feedback_messages["rating"] = urldecode($this->messages[102522]);
        }
        if ($error > 0) {
            return false;
        }

        $sql = "SELECT * FROM " . geoTables::classifieds_table . " WHERE `id` = ?";
        $auction_result = $db->Execute($sql, array($auction_id));

        if (!$auction_result) {
            return false;
        } else {
            if ((!$auction_result) || ($auction_result->RecordCount() == 0)) {
                $sql = "SELECT * FROM " . geoTables::classifieds_expired_table . " WHERE `id` = ?";
                $auction_result = $db->Execute($sql, array($auction_id));

                if ((!$auction_result) || ($auction_result->RecordCount() == 0)) {
                    //auction not in classifieds or expired table -- probably purged
                    //don't allow adding new feedback, since we can't get the data for it
                    return false;
                }
            }

            $show_auction = $auction_result->FetchNextObject();

            if ($show_auction->AUCTION_TYPE == 1 || $show_auction->AUCTION_TYPE == 2 || $show_auction->AUCTION_TYPE == 3) {
                //this is a dutch auction
                //check to see if bidder or seller
                if ($show_auction->SELLER != $user_id) {
                    $sql = "SELECT * FROM " . geoTables::auctions_feedbacks_table . " WHERE `rater_user_id` = ? AND `auction_id` = ?";
                    $bidder_result = $db->Execute($sql, array($user_id, $auction_id));
                    if (!$bidder_result) {
                        return false;
                    } elseif ($bidder_result->RecordCount() == 1) {
                        //this buyer can rate the seller of this auction
                        return true;
                    }
                } else {
                    //the seller is rating the buyer
                    return true;
                }
            } else {
                return false;
            }
        }

        if ($error > 0) {
            return false;
        } else {
            return true;
        }
    }

//####################################################################################

    function save_feedback($db = 0, $auction_id = 0, $user_id = 0, $info = 0)
    {
        // there are no errors in the feedback field
        // lets enter it into the database
        if (!$auction_id) {
            return false;
        }
        $db = DataAccess::getInstance();

        $sql = "select * from " . geoTables::classifieds_table . " where id = ?";
        $auction_result = $db->Execute($sql, array($auction_id));

        if (!$auction_result) {
            trigger_error('ERROR FEEDBACK SQL: Query: ' . $sql . ' Error: ' . $this->db->ErrorMsg());
            return false;
        } elseif ((!$auction_result) || ($auction_result->RecordCount() == 0)) {
            $sql = "select * from " . geoTables::classifieds_expired_table . " where id = ?";
            $auction_result = $db->Execute($sql, array($auction_id));

            if ((!$auction_result) || ($auction_result->RecordCount() == 0)) {
                trigger_error('ERROR FEEDBACK SQL: Query: ' . $sql . ' Error: ' . $this->db->ErrorMsg());
                return false;
            }
        }
        $show_auction = $auction_result->FetchNextObject();


        // Check for bad words and bad html
        $this->get_badword_array($db);
        $this->get_html_disallowed_array($db);
        $info["feedback"] = geoFilter::replaceDisallowedHtml($info["feedback"]);
        $info["feedback"] = $this->check_for_badwords($info["feedback"]);

        //echo $info["rating"] . ' is the rating<br>';
        if ($info["rating"] == "a") {
            $rating = -1;
        } elseif ($info["rating"] == "b") {
            $rating = 0;
        } elseif ($info["rating"] == "c") {
            $rating = 1;
        }

        //echo $show_auction->AUCTION_TYPE." is auction_type 2<bR>\n";
        if (!($user_id) && ($info)) {
            return false;
        }

        if ($show_auction->AUCTION_TYPE == 1 || $show_auction->AUCTION_TYPE == 3) {
            $sql = "UPDATE " . geoTables::auctions_feedbacks_table . " SET `feedback` = ?, `rate` = ?,
				`date` = ?,	`done` = 1 WHERE `auction_id` = ? AND `rater_user_id` = ? AND `done`=0 LIMIT 1";
            $inputs = array($info["feedback"], $rating, geoUtil::time(),  $auction_id, $user_id);
        } elseif ($show_auction->AUCTION_TYPE == 2) {
            $sql = "UPDATE " . geoTables::auctions_feedbacks_table . " SET `feedback` = ?, `rate` = ?,
				`date` = ?,	`done` = 1 WHERE `auction_id` = ? AND `rater_user_id` = ? AND `rated_user_id` = ?";
            $user1 = $user_id;
            $user2 = $show_auction->SELLER;
            if ($user1 == $user2) {
                //seller leaving feedback for buyer
                $user2 = $info['rated_id'];
            }
            $inputs = array($info["feedback"], $rating, geoUtil::time(),  $auction_id, $user1, $user2);
        } else {
            return false;
        }
        $result = $db->Execute($sql, $inputs);

        if (!$result) {
            return false;
        }

        if ($show_auction->AUCTION_TYPE == 1 || $show_auction->AUCTION_TYPE == 3) {
            $sql = "SELECT `rated_user_id` FROM " . geoTables::auctions_feedbacks_table . " 
					WHERE `auction_id` = ? AND `rater_user_id` = ? AND `date`=? AND `done`=1";
            $rated_user_id = $db->GetOne($sql, array($auction_id, $user_id, geoUtil::time()));
        } else {
            $rated_user_id = $info["rated_id"];
        }

        $sql = "SELECT SUM(`rate`) AS feedback_score, COUNT(`rate`) AS feedback_count FROM " . geoTables::auctions_feedbacks_table . " WHERE `rated_user_id` = ? AND `done` = 1";
        $result = $db->Execute($sql, array($rated_user_id));

        //Get the count of positive scores from the database
        $sql = "SELECT COUNT(*) AS feedback_positive_count FROM " . geoTables::auctions_feedbacks_table . " WHERE `rated_user_id` = ? AND `rate` = 1 AND `done` = 1";
        $positive_result = $db->Execute($sql, array($rated_user_id));
        $new_result = $positive_result->FetchNextObject();

        if (!$result || !$positive_result) {
            // error in getting the rated user id
            $sql = "UPDATE " . geoTables::auctions_feedbacks_table . " SET `feedback` = '', `rate` = 0, `done` = 0
				WHERE `auction_id` = ? AND `rater_user_id` = ?";
            $result = $db->Execute($sql, array($auction_id, $user_id));
            return false;
        } elseif ($result->RecordCount() == 1) {
            $show_ratings = $result->FetchNextObject();

            // Check to make sure we dont need to update the feedback icon
            $sql = "SELECT `filename` FROM " . geoTables::auctions_feedback_icons_table . " WHERE `begin` <= ? AND `end` >= ?";
            $filename = $db->GetOne($sql, array($show_ratings->FEEDBACK_SCORE, $show_ratings->FEEDBACK_SCORE));


            $sql = "UPDATE " . geoTables::userdata_table . " SET `feedback_score` = ?, `feedback_count` = ?, `feedback_positive_count` = ?, `feedback_icon` = ? 
				WHERE `id` = ?";
            $query_data = array($show_ratings->FEEDBACK_SCORE, $show_ratings->FEEDBACK_COUNT, $new_result->FEEDBACK_POSITIVE_COUNT, $filename, $rated_user_id);
            $result = $db->Execute($sql, $query_data);
            if (!$result) {
                return false;
            }
        } else {
            //no ratings yet for this user
            //when there should have been
            $sql = "UPDATE " . geoTables::auctions_feedbacks_table . " SET `feedback` = '', `rate` = 0, `done` = 0
				WHERE `auction_id` = ? AND `rater_user_id` = ?";
            $result = $db->Execute($sql, array($auction_id, $user_id));
            return false;
        }
        return true;
    }

//####################################################################################

    function feedback_thank_you($db = 0)
    {
        $this->page_id = 10161;
        $this->get_text();
        $db = DataAccess::getInstance();

        $view = geoView::getInstance();
        $view->feedback_home_link = $db->get_site_setting('classifieds_file_name') . "?a=4&b=22";

        $view->setBodyTpl('feedback/feedback_thank_you.tpl', '', 'auctions');

        $this->display_page();
        return true;
    } //end of function feedback_thank_you

//####################################################################################

    function feedback_error()
    {
        $this->page_id = 10162;
        $this->get_text();

        $view = geoView::getInstance();
        $view->errors = $this->error_message;

        $view->setBodyTpl('feedback/feedback_error.tpl', '', 'auctions');

        $this->display_page();
    } //end of function feedback_error

//####################################################################################
}
