<?php

class Auction_list_bids extends geoSite
{

    var $auction_id;
    var $auction_user_id;
    var $feedback_messages;
    var $user_data;

    // Debug variables
    var $filename = "user_management_list_bids_auctions.php";
    var $function_name;

    var $debug_bids = 0;
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function Auction_list_bids($db = null, $language_id = null, $auction_user_id = null, $production_configuration = null)
    {
        parent::__construct();
        $this->auction_user_id = geoSession::getInstance()->getUserId();
    } //end of function Auction_feedback

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    /**
     * Normally, drives page body for My Current Bids page.
     * @param bool $returnCountOnly If true, returns a count of active bids, and writes no content directly (useful to My Account Links module)
     * @return bool|int
     */
    public function list_auctions_with_your_bid($returnCountOnly = false)
    {
        if (!$this->auction_user_id) {
            return false;
        }
        $db = DataAccess::getInstance();
        $this->page_id = 10175;
        $msgs = $db->get_text(true, $this->page_id);
        $tpl = new geoTemplate('system', 'user_management');

        $sql = "SELECT * FROM " . geoTables::bid_table . " WHERE `bidder` = ? ORDER BY `time_of_bid` DESC, `auction_id`  DESC";
        $bid_result = $db->Execute($sql, array((int)$this->auction_user_id));
        if (!$bid_result) {
            $this->site_error($db->ErrorMsg());
            return false;
        }
        if ($bid_result->RecordCount() > 0) {
            $tpl->assign('showAuctions', true);

            $auctions = array();
            $last_auction = 0;
            foreach ($bid_result as $row) {
                if ($row['auction_id'] === $last_auction) {
                    //already ran this one...  (note that last_auction is only set
                    //when the auction should not be displayed more than once...
                    continue;
                }

                $listing = geoListing::getListing($row['auction_id']);
                if (!$listing) {
                    //could not get details of this listing...
                    continue;
                }
                if ($listing->price_applies == 'lot') {
                    //applies to lot so not inventory item so only show a single
                    //bid from this
                    $last_auction = $row['auction_id'];
                }
                $auction = $listing->toArray();
                $auction['link'] = $this->configuration_data['classifieds_file_name'] . "?a=2&amp;b=" . $row['auction_id'];
                if ($auction['live'] == 0) {
                    $auction['expired'] = true;
                }
                //convert ends date
                $auction['ends'] = date(trim($this->configuration_data['entry_date_configuration']), $auction['ends']);
                if ($auction['auction_type'] == 1 || $auction['auction_type'] == 3) {
                    //standard/reverse auction specifics

                    $auction['display_amount'] = geoString::displayPrice($row['bid'], $auction['precurrency'], $auction['postcurrency']);
                    if ($auction['price_applies'] == 'lot') {
                        $sql = "select maxbid,time_of_bid from " . $this->autobid_table . " where bidder = " . $this->auction_user_id . " and auction_id = " . $row['auction_id'];
                        $user_maxbid_result = $db->Execute($sql);

                        if (!$user_maxbid_result) {
                            $this->site_error($db->ErrorMsg());
                            return false;
                        } elseif ($user_maxbid_result->RecordCount() == 1) {
                            $show_maxbid = $user_maxbid_result->FetchNextObject();
                            $maxbid = $show_maxbid->MAXBID;
                            $maxbid = geoString::displayPrice($maxbid, $auction['precurrency'], $auction['postcurrency']);
                        } else {
                            $maxbid = false;
                        }


                        $auction['maxbid'] = $maxbid;
                        $current_high_bidder = $this->get_high_bidder($db, $row['auction_id']);

                        if ($current_high_bidder["bidder"] == $this->userid) {
                            $payment_link = '';
                            if (geoPC::is_ent()) {
                                //get any possible purchase buttons
                                if ($auction['live'] == 0 && ($auction['reserve_price'] <= $auction['final_price'])) {
                                    $sb = geoSellerBuyer::getInstance();
                                    $vars = array (
                                            'listing_id' => $row['auction_id'],
                                            'winning_bidder_id' => $this->auction_user_id,
                                            'listing_details' => $auction,
                                            'final_price' => $row['bid'],
                                    );
                                    $payment_link = geoSellerBuyer::callDisplay('displayPaymentLinkCurrentBids', $vars, '<br />');
                                    if (strlen($payment_link) > 0) {
                                        $payment_link = '<br />' . $payment_link;
                                    }
                                }
                            }

                            $auction['payment_link'] = (($auction['type'] == 3) ? $msgs[501014] : $msgs[102796]) . $payment_link;
                        } else {
                            $auction['payment_link'] = $msgs[102797];
                        }
                    } else {
                        //this is price applies = item...  which works a little differently
                        $auction['quantity'] = $row['quantity'];

                        $payment_link = '';
                        //get any possible purchase buttons

                        $sb = geoSellerBuyer::getInstance();
                        $vars = array (
                            'listing_id' => $row['auction_id'],
                            'winning_bidder_id' => $this->auction_user_id,
                            'listing_details' => $auction,
                            'final_price' => $row['bid'],
                            'bid_quantity' => $row['quantity'],
                        );
                        $payment_link = geoSellerBuyer::callDisplay('displayPaymentLinkCurrentBids', $vars, '<br />');
                        if (strlen($payment_link) > 0) {
                            $payment_link = '<br />' . $payment_link;
                        }

                        $auction['payment_link'] = $msgs[102796] . $payment_link;
                    }
                } else {
                    //dutch auction specifics

                    $sql = "select bid,time_of_bid,quantity from " . $this->bid_table . " where bidder = " . $this->auction_user_id . " and auction_id = " . $row['auction_id'];
                    $user_bid_result = $db->Execute($sql);

                    if (!$user_bid_result) {
                        if ($this->debug_bids) {
                            echo $sql . "<br />\n";
                        }
                        $this->site_error($db->ErrorMsg());
                        return false;
                    } elseif ($user_bid_result->RecordCount() == 1) {
                        $show_last_bid = $user_bid_result->FetchNextObject();
                    }

                    $display_amount = $this->show_money($show_last_bid->BID, $auction['precurrency'], $auction['postcurrency']);
                    $auction['display_amount'] = $display_amount;
                    $auction['quantity'] = $show_last_bid->QUANTITY;

                    //check to see if winning anything
                    $sql = "select * from " . $this->bid_table . " where auction_id=" . $row['auction_id'] . " order by bid desc,time_of_bid asc";
                    $dutch_bid_result = $db->Execute($sql);

                    if (!$dutch_bid_result) {
                        return false;
                    } elseif ($dutch_bid_result->RecordCount() > 0) {
                        $total_quantity = $auction['quantity'];

                        $final_dutch_bid = 0;
                        $quantity_winning = 0;
                        $seller_report = "";
                        $show_bidder = $dutch_bid_result->FetchNextObject();
                        do {
                            $quantity_bidder_receiving = 0;
                            if ($show_bidder->QUANTITY <= $total_quantity) {
                                $quantity_bidder_receiving = $show_bidder->QUANTITY;
                                $total_quantity = $total_quantity - $quantity_bidder_receiving;
                            } else {
                                $quantity_bidder_receiving = $total_quantity;
                                $total_quantity = 0;
                            }
                            if ($quantity_bidder_receiving) {
                                if ($this->auction_user_id == $show_bidder->BIDDER) {
                                    $quantity_winning = $quantity_bidder_receiving;
                                    $bid_made = $show_bidder->BID;
                                    $final_dutch_bid = $show_bidder->BID;
                                    break;
                                }
                            }
                        } while (($show_bidder = $dutch_bid_result->FetchNextObject()) && ($total_quantity != 0));


                        if ($quantity_winning) {
                            $auction['quantity_winning'] = $quantity_winning . " " . $msgs[102798];
                        } else {
                            $auction['quantity_winning'] = $msgs[102799];
                        }
                    }
                }
                $auctions[] = $auction;
            }
            $tpl->assign('auctions', $auctions);
        } else {
            //there are no auction filters for this user
            $tpl->assign('showAuctions', false);
        }

        if ($returnCountOnly) {
            //used by My Account Links module to create a badge for number of open bids
            return count($auctions) ? count($auctions) : 0;
        }

        $tpl->assign('userManagementHomeLink', $this->configuration_data['classifieds_file_name'] . "?a=4");
        $this->body = $tpl->fetch('list_bids/auctions_with_users_bid.tpl');
        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} // end of Auction_list_bids
