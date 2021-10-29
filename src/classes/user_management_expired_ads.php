<?php

//user_management_expired_ads.php
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

class User_management_expired_ads extends geoSite
{
    var $debug_expired = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function User_management_expired_ads($db, $language_id, $classified_user_id = 0, $page = 0, $product_configuration = 0)
    {
        parent::__construct();

        $page = (int)$page;
        $this->page_result = ($page) ? $page : 1;
    } //end of function User_management

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function list_expired_ads()
    {
        if (!$this->userid) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }
        $this->page_id = 23;
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true, $this->page_id);
        $tpl = new geoTemplate('system', 'user_management');

        // This is where we set up to not show listings older than the admin-defined cutoff
        // db holds cutoff value in days -- convert to seconds for compatibility with ticktime
        $ticktime_cutoff = ($db->get_site_setting("expired_cutoff")) ? ($db->get_site_setting("expired_cutoff") * 24 * 60 * 60) : 0;
        $usingCutoff = true;
        if ($ticktime_cutoff == 0) {
            $usingCutoff = false;
        }
        $cutoff_point = geoUtil::time() - $ticktime_cutoff;

        // First we need the number of elements to be returned
        if ($usingCutoff) {
            $this->sql_query = "select count(id) as count from " . $this->classifieds_table . " where seller = " . $this->userid . " and live = 0 and hide = 0 and ends < " . geoUtil::time() . " AND ends >= " . $cutoff_point . " order by ends desc ";
        } else {
            $this->sql_query = "select count(id) as count from " . $this->classifieds_table . " where seller = " . $this->userid . " and live = 0 and hide = 0 and ends < " . geoUtil::time() . " order by ends desc ";
        }

        $class_result = $db->Execute($this->sql_query);
        if (!$class_result) {
            $this->error_message = $this->internal_error_message;
            return false;
        } else {
            $class_count = $class_result->FetchRow();
        }

        $this->sql_query = "select count(id) as count from " . $this->classifieds_expired_table . " where seller = " . $this->userid . " and hide = 0 order by ad_ended desc ";
        $expired_result = $db->Execute($this->sql_query);
        if (!$expired_result) {
            $this->debug_message = "no user data returned";
            $this->error_message = $this->internal_error_message;
            return false;
        } else {
            $expired_count = $expired_result->FetchRow();
        }

        // Record the total count
        $total_returned = $class_count['count'] + $expired_count['count'];

        // Now run the actual queries
        $ends = ($usingCutoff) ? 'ends >= ' . $cutoff_point : 'ends < ' . geoUtil::time();
        $this->sql_query = "select * from " . $this->classifieds_table . " where seller = " . $this->userid . " and live = 0 and hide = 0 and ends < " . geoUtil::time() . " AND $ends order by ends desc ";

        if ($this->page_result != 1) {
            if (($this->page_result - 1) * $this->configuration_data['number_of_ads_to_display'] < $class_count['count']) {
                $start = ($this->page_result - 1) * $this->configuration_data['number_of_ads_to_display'];
                $num_return = $this->configuration_data['number_of_ads_to_display'];
                $this->sql_query .= "limit " . $start . ", " . $num_return;
            } else {
                $this->sql_query .= "limit 0,0";
            }
        } else {
            $this->sql_query .= "limit " . $this->configuration_data['number_of_ads_to_display'];
        }

        $newly_closed_result = $db->Execute($this->sql_query);
        if (!$newly_closed_result) {
            $this->debug_message = "no user data returned";
            $this->error_message = $this->internal_error_message;
            return false;
        }

        if ($newly_closed_result->RecordCount() < $this->configuration_data['number_of_ads_to_display']) {
            $this->sql_query = "select * from " . $db->geoTables->classifieds_expired_table . " where seller = " . $this->userid . " AND hide = 0 order by ad_ended desc ";
            if ($this->page_result != 1) {
                if ($newly_closed_result->RecordCount() > 0) {
                    // Display a few from previous table and from the beginning of this table
                    $start = 0;
                    $end = $this->configuration_data['number_of_ads_to_display'] - $newly_closed_result->RecordCount();
                } else {
                    // Calculate the number of listings off of the number_of_ads_to_display variable we are
                    $offset = $class_count['count'] % $this->configuration_data['number_of_ads_to_display'];

                    // Calculate the number of pages used in the first query
                    $first_pages = ceil($class_count['count'] / $this->configuration_data['number_of_ads_to_display']);

                    // Find how many pages deep we are into the second query
                    $current_page = $this->page_result - $first_pages;

                    $start = ($current_page * $this->configuration_data['number_of_ads_to_display']) - ($offset);
                    if ($offset == 0) {
                        $start = ($current_page - 1) * $this->configuration_data['number_of_ads_to_display'];
                    }

                    $end = $this->configuration_data['number_of_ads_to_display'];
                }
                $this->sql_query .= "limit " . $start . ", " . $end;
            } else {
                $this->sql_query .= "limit 0," . ($this->configuration_data['number_of_ads_to_display'] - $newly_closed_result->RecordCount());
            }
            $result = $db->Execute($this->sql_query);
            if (!$result) {
                $this->debug_message = "no user data returned";
                $this->error_message = $this->internal_error_message;
                return false;
            }
        }
        if (($result && ($result->RecordCount() > 0)) || ($newly_closed_result && ($newly_closed_result->RecordCount() > 0))) {
            $tpl->assign('showExpiredAds', true);
            $tpl->assign('is_ca', geoMaster::is('classifieds') && geoMaster::is('auctions'));
            $tpl->assign('bothListingTypes', (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? true : false);
            $allow_copying_new_listing = $db->get_site_setting('allow_copying_new_listing');
            if ($allow_copying_new_listing) {
                $tpl->assign('allow_copy', true);
            }

            $expired = array();
            $i = 0;
            if ($newly_closed_result && $newly_closed_result->RecordCount() > 0) {
                while ($show_closed = $newly_closed_result->FetchNextObject()) {
                    $renew_cutoff = ($show_closed->ENDS - ($this->configuration_data['days_to_renew'] * 86400));
                    $renew_postcutoff = ($show_closed->ENDS + ($this->configuration_data['days_to_renew'] * 86400));

                    $expired[$i]['css'] = ($i % 2 == 0) ? 'result_set_even_rows' : 'result_set_odd_rows';
                    $expired[$i]['type'] = $show_closed->ITEM_TYPE;
                    $expired[$i]['link'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;c=" . $show_closed->ID;
                    $expired[$i]['title'] = $show_closed->TITLE;
                    $expired[$i]['id'] = $show_closed->ID;

                    $expired[$i]['start_date'] = date($this->configuration_data['entry_date_configuration'], $show_closed->DATE);
                    $expired[$i]['end_date'] = date($this->configuration_data['entry_date_configuration'], $show_closed->ENDS);

                    if (($this->configuration_data['days_to_renew']) && (geoUtil::time() > $renew_cutoff) && (geoUtil::time() < $renew_postcutoff)) {
                        $link = ($this->configuration_data['use_ssl_in_sell_process']) ? $this->configuration_data['classifieds_ssl_url'] : $this->configuration_data['classifieds_file_name'];
                        $link = trim($link);
                        $link .= "?a=cart&amp;action=new&amp;main_type=listing_renew_upgrade&amp;listing_id={$show_closed->ID}&amp;r=1";
                        $expired[$i]['renewLink'] = $link;
                    }

                    if ($show_closed->ITEM_TYPE == 2) {
                        //only auctions can be viewed after they're closed.
                        $expired[$i]['detailsLink'] = $this->configuration_data['classifieds_file_name'] . "?a=2&amp;b=" . $show_closed->ID;
                    } else {
                        $expired[$i]['detailsLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;c=" . $show_closed->ID;
                    }

                    if ($allow_copying_new_listing) {
                        $expired[$i]['copyLink'] = $this->configuration_data['classifieds_file_name'] . "?a=cart&amp;action=new&amp;main_type=" . (($show_closed->ITEM_TYPE == 1) ? 'classified' : 'auction') . "&amp;copy_id=" . $show_closed->ID;
                    }

                    $expired[$i]['deleteLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;d=" . $show_closed->ID . "&amp;page=" . $_GET['page'];
                    //only show delete button for listings that have been moved to expired table

                    // If not auctions or classauctions then lets move on
                    // did this so that the indentation woudlnt be so deep below
                    if (!geoMaster::is('auctions')) {
                        $this->row_count++;
                        $i++;
                        continue;
                    }

                    // Winning bidders
                    // Note: Only gets here if it is an auction
                    if ($show_closed->CURRENT_BID >= $show_closed->RESERVE_PRICE) {
                        //find any fields that add cost (such as shipping/handling)
                        $groupId = geoUser::getData($this->userid, 'group_id');
                        $fields = geoFields::getInstance($groupId, geoListing::getListing($show_closed->ID, false, true)->category);
                        $extraCosts = 0;
                        for ($c = 1; $c <= 20; $c++) {
                            $fieldName = 'optional_field_' . $c;
                            $fieldName_uc = 'OPTIONAL_FIELD_' . $c;
                            if ($fields->$fieldName->is_enabled && $fields->$fieldName->field_type == 'cost') {
                                //this is a "cost" field
                                $extraCosts += $show_closed->$fieldName_uc;
                            }
                        }

                        if ($show_closed->AUCTION_TYPE == 1 || $show_closed->AUCTION_TYPE == 3) {
                            //display auction winner
                            $high_bidder = $this->get_high_bidder($db, $show_closed->ID);
                            if ($high_bidder) {
                                $expired[$i]['showStandardWinner'] = true;
                                $user_info = $this->get_user_data($high_bidder['bidder']);


                                $price = $high_bidder['bid'] + $extraCosts;

                                $expired[$i]['amount'] = geoString::displayPrice($price, $show_closed->PRECURRENCY, $show_closed->POSTCURRENCY);
                                $expired[$i]['winner'] = $user_info->USERNAME;
                                $expired[$i]['winnerMail'] = $user_info->EMAIL;
                            }
                        } else {
                            //display dutch auction winners

                            $this->sql_query = "select * from " . $this->bid_table . " where auction_id=" . $show_closed->ID . " order by bid desc,time_of_bid asc";
                            $bid_result = $db->Execute($this->sql_query);
                            if (!$bid_result) {
                                return false;
                            } elseif ($bid_result->RecordCount() > 0) {
                                $total_quantity = $show_closed->QUANTITY;
                                $final_dutch_bid = 0;
                                $seller_report = "";
                                $this->dutch_bidders = array();
                                $show_bidder = $bid_result->FetchNextObject();
                                $winners = array();
                                $w = 0;
                                do {
                                    if ($show_bidder->BID > $show->RESERVE_PRICE) {
                                        $quantity_bidder_receiving = 0;
                                        if ($show_bidder->QUANTITY <= $total_quantity) {
                                            $quantity_bidder_receiving = $show_bidder->QUANTITY ;
                                            if ($show_bidder->QUANTITY == $total_quantity) {
                                                $final_dutch_bid = $show_bidder->BID;
                                            }
                                            $total_quantity = $total_quantity - $quantity_bidder_receiving;
                                        } else {
                                            $quantity_bidder_receiving = $total_quantity;
                                            $total_quantity = 0;
                                            $final_dutch_bid = $show_bidder->BID;
                                        }
                                        if ($quantity_bidder_receiving) {
                                            $bidder_info = $this->get_user_data($show_bidder->BIDDER);
                                            $price = $high_bidder['bid'] + $extraCosts;
                                            $display_amount = geoString::displayPrice($price, $show_closed->PRECURRENCY, $show_closed->POSTCURRENCY);

                                            $winners[$w]['quantity'] = $quantity_bidder_receiving;
                                            $winners[$w]['username'] = $bidder_info->USERNAME;
                                            $winners[$w]['email'] = $bidder_info->EMAIL;
                                            $winners[$w]['amount'] =  $display_amount;
                                            $w++;
                                        }
                                    }
                                } while (($show_bidder = $bid_result->FetchNextObject()) && ($total_quantity != 0) && ($final_dutch_bid == 0));
                                if ($final_dutch_bid == 0) {
                                    $final_dutch_bid = $this->dutch_bidders[$local_key]["bid"];
                                }
                                if (strlen(trim($seller_report)) > 0) {
                                    $expired[$i]['showDutchWinner'] = true;
                                    $expired[$i]['winners'] = $winners;
                                }
                            }
                        }
                    }
                    if ($this->configuration_data['number_of_ads_to_display'] && ($this->configuration_data['number_of_ads_to_display'] == $i)) {
                        //printed all we need out of this table -- skip the other one
                        $skip_second = 1;
                        break;
                    }
                    $i++;
                }
            }

            if (!isset($skip_second) && $result && $result->RecordCount() > 0) {
                //grab data from the other table
                while ($show = $result->FetchNextObject()) {
                    $expired[$i]['css'] = ($i % 2 == 0) ? 'result_set_even_rows' : 'result_set_odd_rows';
                    $expired[$i]['type'] = $show->ITEM_TYPE;
                    $expired[$i]['link'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;c=" . $show->ID;
                    $expired[$i]['title'] = $show->TITLE;
                    $expired[$i]['id'] = $show->ID;
                    $expired[$i]['start_date'] = date($this->configuration_data['entry_date_configuration'], $show->DATE);
                    $expired[$i]['end_date'] = date($this->configuration_data['entry_date_configuration'], $show->ENDS);
                    $expired[$i]['detailsLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;c=" . $show->ID;
                    if ($allow_copying_new_listing) {
                        //$expired[$i]['copyLink'] = $this->configuration_data['classifieds_file_name']."?a=cart&amp;copy_id=".$show->ID;
                        $expired[$i]['copyLink'] = $this->configuration_data['classifieds_file_name'] . "?a=cart&amp;action=new&amp;main_type=" . (($show->ITEM_TYPE == 1) ? 'classified' : 'auction') . "&amp;copy_id=" . $show->ID;
                    }
                    $expired[$i]['deleteLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;d=" . $show->ID . "&amp;page=" . $_GET['page'];
                    $i++;
                }
            }
            if ($this->configuration_data['number_of_ads_to_display'] < $total_returned) {
                $totalPages = ceil($total_returned / $this->configuration_data['number_of_ads_to_display']);
                $url = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;page=";
                $css = "browsing_result_page_links";
                $tpl->assign('pagination', geoPagination::getHTML($totalPages, $this->page_result, $url, $css));
            }
            $tpl->assign('expired', $expired);
        } else {
            $tpl->assign('showExpiredAds', false);
        }


        //renewed listings awaiting admin approval
        $sql = "SELECT oi.`id` as order_item_id FROM " . geoTables::order_item . " as oi, " . geoTables::order . " as o 
			WHERE o.`buyer` = ? AND 
			oi.order = o.id AND
			oi.type = 'listing_renew_upgrade' AND
			oi.status = 'pending' AND
			o.status in ('pending', 'active', 'payment_admin')";

        $all = $db->GetAll($sql, array($this->userid));
        $pending = array();
        $p = 0;
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

            $pending[$p]['link'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;c=" . $listing_id;
            $pending[$p]['title'] = geoString::fromDB($listing->title);

            if ($listing->ends) {
                $pending[$p]['ends'] = date($this->configuration_data['entry_date_configuration'], $listing->ends);
            } else {
                $pending[$p]['ends'] = date($this->configuration_data['entry_date_configuration'], geoUtil::time());
            }
            if ($listing->date) {
                $pending[$p]['date'] = date($this->configuration_data['entry_date_configuration'], $listing->date);
            } else {
                $pending[$p]['date'] = date($this->configuration_data['entry_date_configuration'], geoUtil::time());
            }
            $p++;
        }
        $tpl->assign('pending', $pending);

        // final fee table
        // Displays the final fees for auctions underneath the expired auctions
        $sql = "SELECT oi.`id` as order_item_id FROM " . geoTables::order_item . " as oi, " . geoTables::order . " as o 
			WHERE o.`buyer` = ? AND 
			oi.order = o.id AND
			oi.type = 'auction_final_fees' AND
			oi.status = 'pending'";

        $all = $db->GetAll($sql, array($this->userid));
        $finalFees = array();
        $ff = 0;
        foreach ($all as $row) {
            $item = geoOrderItem::getOrderItem($row['order_item_id']);
            if (!$item) {
                //something wrong with order item
                continue;
            }
            $listing_id = $item->get('listing');
            $listing = geoListing::getListing($listing_id);
            if (!$listing || $listing->live) {
                //die ('listing: '.$listing_id.' listing:<pre> '.print_r($item,1));
                continue;
            }


            $finalFees[$ff]['link'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2&amp;c=" . $listing_id;
            $finalFees[$ff]['title'] = geoString::fromDB($listing->title);
            $finalFees[$ff]['date'] = date($this->configuration_data['entry_date_configuration'], $listing->date);
            $finalFees[$ff]['ends'] = date($this->configuration_data['entry_date_configuration'], $listing->ends);
            $finalFees[$ff]['amount'] = geoString::displayPrice($item->getCost());//$this->show_money($final_fee_total,$show_final_fee["precurrency"],$show_final_fee["postcurrency"]);
            $ff++;
        }

        $tpl->assign('finalFees', $finalFees);

        $tpl->assign('userManagementHomeLink', $this->configuration_data['classifieds_file_name'] . "?a=4");
        $this->body = $tpl->fetch('expired_ads/list.tpl');
        $this->display_page();
        return true;
    } //end of function list_expired_ads

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    function hide_expired_ad($db, $hideThis = 0)
    {
            $id = (int)$hideThis['id'];
            $query = "update " . $this->db->geoTables->classifieds_expired_table . " set hide = 1 WHERE id=?";
        if (!$this->db->Execute($query, array($id))) {
            return false;
        }
            $query = "update " . $this->db->geoTables->classifieds_table . " set hide = 1 WHERE id=? and live = 0";
        if (!$this->db->Execute($query, array($id))) {
            return false;
        }
            return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%


    function verify_remove_expired_ad($db, $classified_id = 0)
    {
        if (!$classified_id) {
            return false;
        }
        $this->page_id = 36;
        $this->get_text();
        $tpl = new geoTemplate('system', 'user_management');
        $tpl->assign('classifiedId', $classified_id);
        $tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=2");
        $tpl->assign('expiredAdsLink', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=2");

        $this->body = $tpl->fetch('expired_ads/verify_remove.tpl');
        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_expired_ad($db, $classified_id = 0)
    {
        $classified_id = (int)$classified_id;
        if (!$classified_id) {
            return false;
        }
        $this->page_id = 35;
        $db = DataAccess::getInstance();
        $this->get_text();

        $this->sql_query = "select * from " . $this->classifieds_expired_table . " where id = " . $classified_id;
        $result = $db->Execute($this->sql_query);
        if (!$result || $result->RecordCount() > 1) {
            $this->error_message = urldecode($this->messages[81]);
            return false;
        } elseif ($result->RecordCount() <= 0) {
            //check the live table
            $this->sql_query = "select * from " . $this->classifieds_table . " where id = " . $classified_id;
            $result = $db->Execute($this->sql_query);
            if (!$result || $result->RecordCount() != 1) {
                $this->error_message = urldecode($this->messages[81]);
                return false;
            }
        }

        //found an ad -- display it
        $tpl = new geoTemplate('system', 'user_management');
        $show = $result->FetchNextObject();
        $tpl->assign('ad', $show);

        $this->get_ad_configuration($db);
        $tpl->assign('config', $this->ad_configuration_data);

        $listing = geoListing::getListing($classified_id, false, true);
        if (is_numeric($listing->category)) {
            $category_tree = $this->category_tree_array = geoCategory::getTree($listing->category);
            reset($this->category_tree_array);

            if ($category_tree) {
                $tpl->assign('category_tree', true);
                $tpl->assign('categoriesLink', $this->configuration_data['classifieds_file_name'] . "?a=5");
                //category tree
                if (is_array($this->category_tree_array)) {
                    $tree = array();
                    for ($i = 0; $i < count($this->category_tree_array); $i++) {
                        //display all the categories
                        $tree[$i]['link'] = $this->configuration_data['classifieds_file_name'] . "?a=5&amp;b=" . $this->category_tree_array[$i]["category_id"];
                        $tree[$i]['name'] = $this->category_tree_array[$i]["category_name"];
                    }
                    $tpl->assign('tree', $tree);
                } else {
                    $tpl->assign('tree', $category_tree);
                }
            }
        }

        $tpl->assign('start_date', date($this->configuration_data['entry_date_configuration'], $show->DATE));
        if ($show->AD_ENDED) {
            $ended_date = date($this->configuration_data['entry_date_configuration'], $show->AD_ENDED);
        } else {
            $ended_date = date($this->configuration_data['entry_date_configuration'], $show->ENDS);
        }
        $tpl->assign('end_date', $ended_date);

        if ($show->ITEM_TYPE == 2) {
            //get bid history if any
            $this->sql_query = "select * from " . $this->bid_table . " where auction_id = " . $show->ID;
            if ($show->PRICE_APPLIES == 'item' && $this->userid != $show->SELLER) {
                //do NOT expose all the people that purchased buy-now to anyone
                //except the seller...  Only show the person their own bids
                $this->sql_query .= " AND bidder=" . (int)$this->userid;
            }
            $this->sql_query .= " order by time_of_bid asc";
            $bid_history_result = $db->Execute($this->sql_query);
            if (!$bid_history_result) {
                return false;
            } elseif ($bid_history_result->RecordCount() > 0) {
                //there are bids on this auction and show them
                $this->row_count = 0;
                $bids = array();
                $high_bidder = $this->get_high_bidder(0, $show->ID);
                for ($i = 0; $show_bid = $bid_history_result->FetchNextObject(); $i++) {
                    $bids[$i]['time'] = date($this->configuration_data['entry_date_configuration'], $show_bid->TIME_OF_BID);
                    $bids[$i]['amount'] = $this->show_money($show_bid->BID, $this->configuration_data['precurrency'], $this->configuration_data['postcurrency']);
                    if ($show->AUCTION_TYPE == 2 || $show->PRICE_APPLIES == 'item') {
                        // If dutch auctions, show quantity
                        $bids[$i]['quantity'] = $show_bid->QUANTITY;
                    }
                    $bidder_data = $this->get_user_data($show_bid->BIDDER);
                    $bids[$i]['username'] = $bidder_data->USERNAME;
                    if ($this->userid == $show->SELLER && (($high_bidder && $high_bidder['bidder'] == $show_bid->BIDDER) || $show->AUCTION_TYPE == 2 || $show->PRICE_APPLIES == 'item')) {
                        //person looking at page is seller, AND
                        //this is the high bidder, OR this is a dutch auction
                        $bids[$i]['email'] = $bidder_data->EMAIL;
                    }
                }
                $tpl->assign('bids', $bids);
            }
        }
        $tpl->assign('fields', $this->fields);
        $tpl->assign('expiredAdsLink', $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=2");
        $tpl->assign('userManagementHomeLink', $this->configuration_data['classifieds_file_name'] . "?a=4");
        $this->body = $tpl->fetch('expired_ads/expired_ad_details.tpl');
        $this->display_page();
        return true;
    }
}
