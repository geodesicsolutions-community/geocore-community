<?php

//auction_bid_class.php
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
## ##    17.10.0-18-g80bd7ca
##
##################################

class Auction_bid extends geoSite
{

    var $auction_id;
    var $classified_user_id;
    var $bid_error = 0;
    var $bid_success = 0;
    var $auction;
    var $bidder;
    var $bid_quantity;
    var $dutch_bidders;
    var $winning_dutch_bidder = 0;
    var $dutch_bidder_quantity = 0;
    var $DEBUG_BID = 0;
    var $filename = "auction_bid_class.php";
    var $function_name;

    public function __construct($auction_id)
    {
        parent::__construct();
        $this->auction_id = (int)$auction_id;

        $this->auction = geoListing::getListing($this->auction_id);
        trigger_error("DEBUG STATS: userid" . $this->userid . " auction id:" . $this->auction_id);
    } // end of function Auction_bid

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function bid_setup($buy_now = 0, $bid_amount = 0, $aff_id = 0)
    {
        $this->page_id = 10163;
        $this->get_text();

        $view = geoView::getInstance();

        $verification_check = $_REQUEST["e"];

        //show the form to bid on this auction
        if (!$this->userid || !$this->auction_id) {
            return false;
        }

        //get auction information
        $this->bidder = $this->get_user_data($this->userid);

        // Find out if buy now auction only
        $buy_now_only = $this->auction->buy_now_only;

        if (($this->db->get_site_setting('black_list_of_buyers')) && ($this->db->get_site_setting('invited_list_of_buyers'))) {
            $invited = $this->check_invitedlist(0, $this->auction->seller, $this->userid);
            $banned = $this->check_blacklist(0, $this->auction->seller, $this->userid);
            if ($invited == 1) {
                $can_bid = 1;
            } else {
                if ($banned) {
                    $can_bid = 0;
                } elseif ($invited == 2) {
                    $can_bid = 1;
                }
            }
        } elseif ($this->db->get_site_setting('black_list_of_buyers')) {
            //check black list only
            if ($this->check_blacklist($this->db, $this->auction->seller, $this->userid)) {
                $can_bid = 0;
            } else {
                $can_bid = 1;
            }
        } elseif ($this->db->get_site_setting('invited_list_of_buyers')) {
            //check invited only
            if ($this->check_invitedlist(0, $this->auction->seller, $this->userid)) {
                //this user is on the invited list
                $can_bid = 1;
            }
        } else {
            //no checks on who the bidder is, is needed, so they can probably bid.
            $can_bid = 1;
        }

        if ($buy_now_only && !$buy_now) {
            // If not buying now and auction is buy now only dont allow to bid
            $can_bid = 0;
        }

        if ($can_bid && $this->db->get_site_setting('verify_accounts') && !geoUser::isVerified($this->userid)) {
            //check to see if required to have verified account...
            $bidder = geoUser::getUser($this->userid);
            $price_plan_id = ($bidder->auction_price_plan_id) ? $bidder->auction_price_plan_id : $bidder->price_plan_id;
            $category = $this->auction->category;
            $planItem = geoPlanItem::getPlanItem('verify_account', $price_plan_id, $category);
            if ($planItem->get('require_for_bid')) {
                //required for bid!!!  Give a special error...
                $can_bid = 0;
                $this->bid_error = 9;
            }
        }

        if ($can_bid && $this->db->get_site_setting('bidding_requires_subscription') && geoAddon::getInstance()->isEnabled('subscription_pricing')) {
            //subscription required to bid. if no subscription, redirect to subscription page
            $sql = "select * from " . geoTables::user_subscriptions_table . " where subscription_expire > ? and user_id = ?";
            $result = $this->db->Execute($sql, array(geoUtil::time(), $this->userid));
            if (!$result || $result->RecordCount() < 1) {
                $subscribe = $this->db->get_site_setting('classifieds_url') . '?a=cart&action=new&main_type=subscription';
                header('Location: ' . $subscribe);
                require_once(GEO_BASE_DIR . 'app_bottom.php');
                exit();
            }
        }

        $bid_amount['bid_amount'] = geoNumber::deformat($bid_amount['bid_amount']);
        if (!$can_bid) {
            return false;
        }

        if ($this->auction->auction_type == 2) {
            // Dutch Auctions
            $sql = "select * from " . geoTables::bid_table . " where auction_id=? order by bid desc,time_of_bid asc";
            $bid_result = $this->db->Execute($sql, array($this->auction->id));
            if (!$bid_result) {
                return false;
            } elseif ($bid_result->RecordCount() > 0) {
                $total_quantity = $show_final_fee->QUANTITY;
                $final_dutch_bid = 0;
                $total_quantity_sold = 0;
                $show_bidder = $bid_result->FetchNextObject();
                if ($bid_result->RecordCount() > 0) {
                    $total_quantity = $show_final_fee->QUANTITY;
                    $final_dutch_bid = 0;
                    $total_quantity_sold = 0;
                    $show_bidder = $bid_result->FetchNextObject();
                    do {
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
                            $dutch_bidder_bid = $show_bidder->BID;
                        }
                        $total_quantity_sold = $total_quantity_sold + $quantity_bidder_receiving;
                    } while (($show_bidder = $bid_result->FetchNextObject()) && ($total_quantity != 0) && ($final_dutch_bid == 0));
                    if ($final_dutch_bid == 0) {
                        $bid_to_show = $dutch_bidder_bid;
                    } else {
                        $bid_to_show = $final_dutch_bid;
                    }
                } else {
                    $bid_to_show = $this->get_minimum_bid();
                }
            } elseif ($bid_amount != 0) {
                $bid_to_show = $bid_amount;
            } else {
                $bid_to_show = $this->get_minimum_bid();
            }
        }

        if (!$this->auction || !$this->bidder || !$this->auction->live == 1) {
            return false;
        }

        if ($this->auction->seller == $this->bidder->ID) {
            $this->bid_error = 4;
            return false;
        }
        $view->listing_id = $this->auction->id;
        $view->verify = ($verification_check == "verify") ? true : false;
        $verify = ($verification_check == "verify") ? 'verified' : 'verify';
        $aff = ($aff_id) ? '&amp;aff=' . $aff_id : '';
        $view->formTarget = $this->db->get_site_setting('classifieds_file_name') . "?a=1029&b=" . $this->auction_id . "&e=" . $verify . $aff;
        $view->title = geoString::fromDB($this->auction->title);
        //what we will use to get full amount bidder would pay...
        $quantity_multiplier = 1;
        if ($buy_now && $this->auction->buy_now) {
            //buy now auction
            $view->auction_type = 'buy_now';
            $price = $this->auction->buy_now;

            $view->max_quantity = $this->auction->quantity_remaining;

            $starting_quantity = $this->auction->quantity_remaining;

            if ($this->auction->price_applies == 'item') {
                $starting_quantity = 1;

                if (isset($bid_amount['bid_quantity'])) {
                    $starting_quantity = (int)$bid_amount['bid_quantity'];
                    if ($starting_quantity > $this->auction->quantity_remaining) {
                        //don't let it go above amount
                        $starting_quantity = $this->auction->quantity_remaining;
                    }
                }
            }

            $view->quantity = $quantity_multiplier = $starting_quantity;
            $view->price_applies = $this->auction->price_applies;
        } elseif ($this->auction->auction_type == 2) {
            //dutch auction
            $view->auction_type = 'dutch';
            $price = $bid_amount['bid_amount'];
            $view->quantity = $quantity_multiplier = $bid_amount['bid_quantity'];
            $view->bid_to_show = geoNumber::format($bid_to_show);
        } elseif ($this->auction->auction_type == 3) {
            //reverse auction
            $view->auction_type = 'reverse';
            $price = $bid_amount['bid_amount'];
            $view->bid_to_show = geoNumber::format($this->get_maximum_bid());
        } else {
            //standard auction
            $view->auction_type = 'standard';
            $price = $bid_amount['bid_amount'];
            $view->bid_to_show = geoNumber::format($this->get_minimum_bid());
        }
        $view->hidden_price = geoNumber::format($price);
        $view->price = $this->show_money($price, $this->auction->precurrency, $this->auction->postcurrency, 1);

        $view->cost_options =  $costOptions = geoListing::getCostOptions($this->auction->id);
        if ($costOptions['hasCombined']) {
            $view->combined_json = json_encode($costOptions['combined']);
        }
        if ($costOptions['groups']) {
            $raw_cost_options = false;
            $cost_options_cost = 0;
            if (isset($_POST['cost_options'])) {
                $raw_cost_options = $_POST['cost_options'];
            } elseif (isset($_GET['cost_options'])) {
                $raw_cost_options = $_GET['cost_options'];
            }
            if ($raw_cost_options) {
                $cost_options_selected = array();
                //sort groups by ID for easy looking
                $groups = array();
                foreach ($costOptions['groups'] as $group) {
                    $options = array();
                    foreach ($group['options'] as $option) {
                        $options[$option['id']] = $option;
                    }
                    $group['options'] = $options;
                    $groups[$group['id']] = $group;
                }
                $combo_options = array();
                foreach ($raw_cost_options as $group_id => $value) {
                    $group_id = (int)$group_id;
                    $value = (int)$value;
                    if (!$group_id || !$value) {
                        continue;
                    }
                    if (!isset($groups[$group_id]) || !isset($groups[$group_id]['options'][$value])) {
                        //not valid
                        continue;
                    }
                    $cost_options_selected[$group_id] = $value;
                    if ($groups[$group_id]['quantity_type'] == 'combined') {
                        $combo_options[$value] = $value;
                    }
                }
                //make sure selection is valid for combined...
                if ($combo_options) {
                    //loop through and see if one has all of these in the combo
                    $validCombo = false;
                    foreach ($costOptions['combined'] as $combo) {
                        if ($combo['quantity_remaining'] < 1) {
                            //no more of this one
                            continue;
                        }
                        foreach ($combo_options as $option_id) {
                            if (!in_array($option_id, $combo['options'])) {
                                //one of the options was not found....
                                continue(2);
                            }
                        }
                        //this one matches and has at least 1...
                        $validCombo = true;
                    }
                    if (!$validCombo) {
                        //not a valid combination...
                        //TODO:show error maybe that they just ran out?
                        $cost_options_selected = false;
                    }
                }
                $view->cost_options_selected = $cost_options_selected;
                if ($view->verify && (!$cost_options_selected || count($cost_options_selected) !== count($groups))) {
                    //does not match up, do not proceed to verify step
                    $view->verify = false;
                }
                if ($view->verify) {
                    //count up the cost options
                    foreach ($cost_options_selected as $group_id => $option_id) {
                        $option = $groups[$group_id]['options'][$option_id];
                        if ($option['cost_added'] > 0) {
                            $cost_options_cost += $option['cost_added'];
                        }
                    }
                }
            } elseif ($view->verify) {
                //I don't think so!  no going to next step until options are selected
                $view->verify = false;
                //no need to do error message, it has JS in place to prevent this, this is just the
                //back-end enforcement.
            }
            $view->cost_options_cost = $cost_options_cost;
        }


        //get any extra fees for this auction (shipping, etc)
        $view->additional_fees = $additional = $this->get_additional_fees($quantity_multiplier);
        //the grand total...
        $total = $price;
        if ($additional) {
            //add additional to total
            $total += $additional['raw']['total'];
        }
        $view->baseTotal = $total;
        if ($cost_options_cost) {
            //add item options to total
            $total += $cost_options_cost;
        }
        $total = $view->grandTotalRaw = $total * $quantity_multiplier;

        $view->grandTotal = geoString::displayPrice($total, $this->auction->precurrency, $this->auction->postcurrency);

        $view->auctionLink = $this->db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=" . $this->auction_id;

        $view->precurrency = geoString::fromDB($this->auction->precurrency);
        $view->postcurrency = geoString::fromDB($this->auction->postcurrency);
        $view->hide_postcurrency = $this->db->get_site_setting('hide_postcurrency');

        if (!$view->verify) {
            $view->addon_bid_extra = geoAddon::triggerDisplay('bid_setup_extra_info', array('listing' => $this->auction), geoAddon::ARRAY_STRING);
        } else {
            geoAddon::triggerUpdate('bid_setup_extra_info_process', array('listing' => $this->auction));
        }

        $view->setBodyTpl('bidding/bid_setup.tpl', '', 'auctions');

        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function bid_error()
    {
        $this->page_id = 10164;
        $this->get_text();

        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $reverse = ($this->auction && $this->auction->auction_type == 3);

        //not enough information to bid
        switch ($this->bid_error) {
            case 1:
                //current bidder is the high bidder
                if ($reverse) {
                    $error = 500997;
                } else {
                    $error = 102458;
                }

                break;

            case 2:
                //raise bid
                if ($reverse) {
                    $error = 500998;
                } else {
                    $error = 102459;
                }
                break;

            case 3:
                //unrecognizable data for bid amount
                $error = 102460;
                break;

            case 4:
                //seller cannot make a bid on their own auction
                $error = 102462;
                break;

            case 5:
                //dutch bid quantity error
                $error = 102463;
                break;

            case 6:
                //raise dutch bid amount...you are not in the money
                $error = 102464;
                break;

            case 7:
                //you cannont lower your dutch bid amount or dutch bid quantity
                $error = 102465;
                break;

            case 8:
                //cannot bid before start time
                $error = 102817;
                break;

            case 9:
                //verified account is required to bid and user does not have verified account.
                $error = 502063;
                break;

            case 0:
                //break intentionally omitted
            default:
                //internal bidding error
                $error = 102461;
                break;
        }
        $view->bid_error = $this->messages[$error];
        $view->categoryLink = $db->get_site_setting('classifieds_file_name') . "?a=5&b=" . $this->auction->category;
        $view->auctionLink = $db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $this->auction_id;

        $view->setBodyTpl('bidding/bid_error.tpl', '', 'auctions');
        $this->display_page();
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function bid_successful($db = 0, $aff_id = 0)
    {
        $this->page_id = 10165;
        $this->get_text();

        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        //refresh auction data
        $view->title = geoString::fromDB($this->auction->title);

        $view->reverse_auction = $reverse = ($this->auction && $this->auction->auction_type == 3);

        if ($this->auction->auction_type == 2) {
            $view->is_dutch = true;
            //first check to see if the bid could possibly win
            //dutch auction
            $sql = "select * from " . geoTables::bid_table . " where auction_id = ? and bidder = ? order by time_of_bid desc limit 1";
            $dutch_bid_result = $db->Execute($sql, array($this->auction_id, $this->userid));

            if (!$dutch_bid_result || $dutch_bid_result->RecordCount() != 1) {
                $this->error_message = $this->messages[81];
                return false;
            } elseif ($dutch_bid_result->RecordCount() == 1) {
                $show_dutch_bid = $dutch_bid_result->FetchNextObject();
            }
            $view->quantity = $show_dutch_bid->QUANTITY;
            $view->price = $this->show_money($show_dutch_bid->BID, $this->db->get_site_setting('precurrency'), $this->db->get_site_setting('postcurrency'));
        } else {
            switch ($this->bid_success) {
                case 1:
                    //you are current high bidder,  your high bid is saved
                    if ($reverse) {
                        $text = $this->messages[501000];
                    } else {
                        $text = $this->messages[102448];
                    }
                    break;

                case 3:
                    //bid received but you have been outbid
                    if ($reverse) {
                        $text = $this->messages[501001];
                    } else {
                        $text = $this->messages[102449];
                    }
                    break;

                case 4:
                    //buy now bid accepted
                    $text = $this->messages[102456];
                    $view->auction_type = 'buy_now';

                    $view->price_applies = $this->auction->price_applies;

                    if ($this->auction->price_applies == 'item') {
                        //price is per item...
                        //figure out quantity
                        $sql = "select * from " . geoTables::bid_table . " where auction_id = ? and bidder = ? order by time_of_bid desc limit 1";
                        $row = $db->GetRow($sql, array($this->auction_id, $this->userid));

                        $view->quantity = $row['quantity'];
                    } else {
                        $view->quantity = $this->auction->quantity;
                    }


                    //on-site payment text
                    if (geoPC::is_ent()) {
                        //seller/buyer
                        $vars = array (
                            'listing_id' => $this->auction_id,
                            'winning_bidder_id' => $this->userid,
                            'listing_details' => $this->auction,
                            'final_price' => $this->auction->buy_now,
                            'bid_quantity' => $this->bid_quantity,
                        );
                        $text .= geoSellerBuyer::callDisplay('displayPaymentLinkBuyNowSuccess', $vars);
                    }
                    break;

                default:
                    //internal bidding error
            }
            $view->successText = $text;

            $additional_fees = geoListing::getAuctionAdditionalFees($this->auction_id);
            if ($additional_fees && $additional_fees['raw']['total'] > 0) {
                $this->get_text(10163);
                $view->additional_fees = geoString::displayPrice($additional_fees['raw']['total'], $this->auction->precurrency, $this->auction->postcurrency);
            }

            if ($this->bid_success != 4) {
                $money_source = $this->auction->current_bid;
            } else {
                $money_source = $this->auction->buy_now;
            }
            $view->price = $this->show_money($money_source, $this->auction->precurrency, $this->auction->postcurrency);
        }
        if ($this->bid_success == 3) {
            //click here to rebid
            $view->rebidLink = $this->db->get_site_setting('classifieds_file_name') . "?a=1029&b=" . $this->auction_id;
        }
        $view->categoryLink = $db->get_site_setting('classifieds_file_name') . "?a=5&b=" . $this->auction->category;
        $view->auctionLink = $db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $this->auction_id;

        $this->insert_favorite($this->db, $this->auction_id);

        $view->setBodyTpl('bidding/bid_successful.tpl', '', 'auctions');
        $this->display_page($db);
    } // end of function bid_successful

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function insert_bid($insert_bid_amount, $bid_amount)
    {
        $reverse = ($this->auction->auction_type == 3);
        $current_time = geoUtil::time();
        $quantity = 1;

        $increment = $this->get_increment($insert_bid_amount);
        $min = ($reverse) ? ($insert_bid_amount - $increment) : ($insert_bid_amount + $increment);

        $set_minimum_result = $this->update_minimum_current_and_price($min, $insert_bid_amount, $insert_bid_amount);
        if (!$set_minimum_result) {
            trigger_error("ERROR BID: set min resulted false");
            return false;
        }

        $insert_bid_result = $this->insert_into_bid_table($insert_bid_amount, $current_time, $quantity);
        if (!$insert_bid_result) {
            //put things back
            $reset_current_price_result = $this->set_current_and_price_to_zero();
            return false;
        }

        if ((!$reverse && $bid_amount > $insert_bid_amount) || ($reverse && $bid_amount < $insert_bid_amount)) {
            //insert bid into the autobid table because it was greater then minimum_bid
            //minimum_bid is the current minimum because there were no previous bids
            $insert_autobid_result = $this->insert_into_autobid_table($bid_amount, $current_time, $quantity);
            if (!$insert_autobid_result) {
                trigger_error("ERROR BID: set autobid resulted false");
                return false;
            }
        }
        $this->auction_extension_check(geoUtil::time());
        $this->send_current_high_bidder_email($db, $this->bidder->ID);
        $this->bid_success = 1;
        $this->start_delayed_auction();
        return true;
    }

    public function process_bid($bid_info = 0, $aff_id = 0)
    {
        trigger_error("DEBUG BID: TOP OF PROCESS BID
			{$bid_info['bid_amount']} is bid amount at top<br />
			 $this->auction_id is the auction id <br />
			 {$this->auction->seller} is the seller<br />
			 $this->userid is the bidder <br />
			 {$this->db->get_site_setting('number_format')} is NUMBER_FORMAT");

        $bid_info['bid_quantity'] = trim($bid_info['bid_quantity']);
        $bid_info['bid_amount'] = trim($bid_info['bid_amount']);

        $bid_amount = geoNumber::deformat($bid_info['bid_amount']);

        if (!preg_match('/^[0-9]{1,10}.?[0-9]{0,2}$/', $bid_amount)) {
            $this->bid_error = 3;
            trigger_error("ERROR BID: bid error 3");
            return false;
        }

        $seller = geoUser::getUser($this->auction->seller);
        $this->bidder = $this->get_user_data($this->userid);

        if (!($bid_amount && $this->auction && $this->bidder && $seller && $this->auction->live == 1 && ($this->auction->ends > geoUtil::time() || $this->auction->ends == 0 || $this->auction->delayed_start == 1))) {
            //one of prereqs is messed up
            return false;
        }

        if ($seller->id == $this->bidder->ID) {
            $this->bid_error = 4;
            trigger_error("ERROR BID: bid error 4");
            return false;
        }

        $quantity = 1;
        if ($this->DEBUG_BID) {
            echo "quantity is set to 1<BR>\n";
        }
        if ($this->auction->auction_type == 2) {
            trigger_error("DEBUG BID:  This is dutch auction");
            //dutch auction
            //no autobidding on dutch auctions
            //no reserve price ?
            //save the quantity and the bid amount
            //all the hard work is at the close of a dutch auction
            $bid_info['bid_quantity'] = trim($bid_info['bid_quantity']);

            //$bid_quantity = $this->show_money($bid_info[bid_quantity],0,0,1);
            $bid_quantity = $bid_info['bid_quantity'];

            if (!preg_match('/^[0-9]{1,10}$/', $bid_quantity)) {
                $this->bid_error = 5;
                trigger_error("ERROR BID: bid error 5");
                return false;
            }

            if ($bid_quantity > $this->auction->quantity) {
                $bid_quantity = $this->auction->quantity;
            }
            //check to see if above the minimum bid
            if ($bid_amount < $this->auction->starting_bid) {
                $this->bid_error = 2;
                trigger_error("ERROR BID: bid error 2");
                return false;
            }
            trigger_error("DEBUG BID: $bid_amount is the amount for this dutch auction, $bid_quantity is the quantity.");

            $sql = "select * from " . geoTables::bid_table . " where auction_id = ? and bidder = ?";
            $get_bid_result = $this->db->Execute($sql, array($this->auction_id, $this->bidder->ID));

            if (!$get_bid_result) {
                //No record was found
                trigger_error("ERROR SQL BID: $sql Error: " . $this->db->ErrorMsg());
                return false;
            } elseif ($get_bid_result->RecordCount() == 1) {
                //update the bid
                $show_current_dutch_bid = $get_bid_result->FetchNextObject();
                if (($show_current_dutch_bid->BID < $bid_amount) && ($show_current_dutch_bid->QUANTITY <= $bid_quantity )) {
                    //Bid amount and quantity are greater
                    $sql = "update " . geoTables::bid_table . " set bid = ?, time_of_bid = ?, quantity = ? where auction_id = ? and bidder = ?";
                    $query_data = array($bid_amount, geoUtil::time(), $bid_quantity, $this->auction_id, $this->bidder->ID);
                    $insert_bid_result = $this->db->Execute($sql, $query_data);

                    if (!$insert_bid_result) {
                        if ($this->DEBUG_BID) {
                            echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
                        }
                        return false;
                    }
                } else {
                    //cannot lower your bid amount or quantity
                    $this->bid_error = 7;
                    trigger_error("ERROR BID:  bid error 7");
                    return false;
                }
            } elseif ($get_bid_result->RecordCount() == 0) {
                //insert the bid
                $insert_bid_result = $this->insert_into_bid_table($bid_amount, geoUtil::time(), $bid_quantity);
                if (!$insert_bid_result) {
                    trigger_error("ERROR BID: insert into bid table failed");
                    return false;
                }
            } else {
                trigger_error("ERROR BID: Found more than one bid for this user in the table, so error");
                return false;
            }

            //check to see if this dutch bid is in the money
            $sql = "select * from " . geoTables::bid_table . " where auction_id=? order by bid desc,time_of_bid asc";
            $bid_result = $this->db->Execute($sql, array($this->auction_id));

            if (!$bid_result) {
                trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                return false;
            }
            if ($bid_result->RecordCount() > 0) {
                $total_quantity = $this->auction->quantity;
                $final_dutch_bid = 0;
                $bid_count = 0;
                $show_bidder = $bid_result->FetchNextObject();
                do {
                    $quantity_bidder_receiving = 0;
                    if ($show_bidder->QUANTITY <= $total_quantity) {
                        //Show bidder quantity is less than total quantity
                        $quantity_bidder_receiving = $show_bidder->QUANTITY ;
                        if ($show_bidder->QUANTITY == $total_quantity) {
                            $final_dutch_bid = $show_bidder->BID;
                        }
                        $total_quantity = $total_quantity - $quantity_bidder_receiving;
                    } else {
                        //Show bidder quantity is not less than total quantity
                        $quantity_bidder_receiving = $total_quantity;
                        $total_quantity = 0;
                        $final_dutch_bid = $show_bidder->BID;
                    }
                    if ($quantity_bidder_receiving) {
                        //save this bidder as an in the money bidder
                        //send an email
                        $this->dutch_bidders[$bid_count]["bidder"] = $show_bidder->BIDDER;
                        $this->dutch_bidders[$bid_count]["quantity"] = $quantity_bidder_receiving;
                        $this->dutch_bidders[$bid_count]["bid"] =  $show_bidder->BID;
                        //$bid_count++;
                        if ($show_bidder->BIDDER == $this->bidder->ID) {
                            //this bidder is in the money
                            $this->winning_dutch_bidder = 1;
                            $this->dutch_bidder_quantity = $quantity_bidder_receiving;
                            $this->winning_dutch_bidder_count = $bid_count;
                        }
                        $bid_count++;
                    }
                } while (($show_bidder = $bid_result->FetchNextObject()) && ($total_quantity != 0));
                if ($final_dutch_bid == 0) {
                    $final_dutch_bid = $this->dutch_bidders[$bid_count - 1]["bid"];
                }
                if (($bid_result->RecordCount() > $bid_count) && ($this->winning_dutch_bidder)) {
                    $this->email_dutch_bidders_new_bid($db, $bid_count, $aff_id);
                }

                if ($this->winning_dutch_bidder == 1) {
                    //update auction info
                    $this->page_id = 10168;
                    $this->get_text();

                    if ($total_quantity == 0) {
                        //all dutch items are bid on...set current/minimum bid to the final dutch bid
                        //value as the minimum bid to win an item
                        $bid_result = $this->update_minimum_current_and_price($final_dutch_bid, $final_dutch_bid, $final_dutch_bid);
                        if (!$bid_result) {
                            trigger_error("ERROR BID: update min thingy failed.");
                            return false;
                        }
                    } else {
                        //all dutch items have not been bid on...leave the current/minimum alone
                    }
                    $this->auction_extension_check(geoUtil::time());
                    $this->email_dutch_bidder_successful_bid($db, $bid_amount, $aff_id);
                    $this->start_delayed_auction();
                    return true;
                } else {
                    $this->email_dutch_bidder_not_successful_bid($db, $bid_amount, $aff_id);
                    $this->start_delayed_auction();
                    return false;
                }
            } else {
                $this->auction_extension_check(geoUtil::time());
                $this->email_only_dutch_bidder($db, $bid_amount, $aff_id, $bid_quantity);
                $this->start_delayed_auction();
                return true;
            }
        } else {
            //regular auction
            //EXTENDED STATS ON BIDS?
            $current_time = geoUtil::time();
            if (isset($bid_info["buy_now_bid"]) && $bid_info["buy_now_bid"] && $this->auction->buy_now) {
                $this->page_id = 10167;
                $this->get_text();

                $bid_quantity = $bid_info['bid_quantity'];

                if (!preg_match('/^[0-9]{1,10}$/', $bid_quantity)) {
                    $this->bid_error = 5;
                    trigger_error("ERROR BID: bid error 5");
                    return false;
                }
                $bid_quantity = (int)$bid_quantity;

                if ($bid_quantity < 1 || $bid_quantity > $this->auction->quantity_remaining) {
                    //should not get here, just a failsafe
                    $this->bid_error = 5;
                    trigger_error("ERROR BID: bid error 5");
                    return false;
                }

                if ($this->auction->price_applies == 'lot' && $bid_quantity != $this->auction->quantity) {
                    //failsafe, make sure quantity is not pushed in through form
                    //manipulation
                    $this->bid_error = 5;
                    trigger_error("ERROR BID: bid error 5");
                    return false;
                }

                //check bid options
                $allCostOptions = geoListing::getCostOptions($this->auction->id);
                //keep track of which quantities will need to be changed as well
                $costOptionsQuantity = array();
                $cost_options = 0;
                if ($allCostOptions['groups']) {
                    //there are cost options, make sure they are set properly
                    $cost_options_raw = $_POST['cost_options'];
                    $groups = $cost_options = array();
                    //listing class does not return referenced by id, do that now for faster processing
                    foreach ($allCostOptions['groups'] as $group) {
                        foreach ($group['options'] as $option) {
                            $group['options'][$option['id']] = $option;
                        }
                        $groups[$group['id']] = $group;
                    }

                    //make sure the cost options are valid
                    foreach ($cost_options_raw as $group_id => $option_id) {
                        $group_id = (int)$group_id;
                        $option_id = (int)$option_id;
                        if (!$group_id || !$option_id) {
                            $this->bid_error = 'cost_options';
                            trigger_error("ERROR BID: bid error with cost options - invalid input");
                            return false;
                        }
                        if (!isset($groups[$group_id]['options'][$option_id])) {
                            $this->bid_error = 'cost_options';
                            trigger_error("ERROR BID: bid error with cost options - invalid input");
                            return false;
                        }
                        $option = $groups[$group_id]['options'][$option_id];
                        if ($groups[$group_id]['quantity_type'] == 'individual') {
                            if ($option['ind_quantity_remaining'] < $bid_quantity) {
                                //not enough
                                $this->bid_error = 'cost_options';
                                trigger_error('ERROR BID: not enough remaining for one of the options');
                                return false;
                            }
                            $costOptionsQuantity['ind'][$option['id']] = $option['ind_quantity_remaining'];
                        }

                        $cost_options[$group_id] = $option_id;
                        //TODO: keep track of combined quantity
                        //TODO: keep track of cost
                    }

                    //validate there are enough combined quantities
                    if ($allCostOptions['hasCombined']) {
                        foreach ($allCostOptions['combined'] as $combo) {
                            foreach ($combo['options'] as $option_id) {
                                if (!in_array($option_id, $cost_options)) {
                                    //not this combination
                                    continue(2);
                                }
                            }
                            //all the options matched up, this is the one
                            $costOptionsQuantity['comb'][$combo['id']] = (int)$combo['quantity_remaining'];
                        }
                    }
                }

                //insert buy now bid
                $insert_bid_result = $this->insert_into_bid_table($this->auction->buy_now, $current_time, $bid_quantity, 1, 0, $cost_options);
                if (!$insert_bid_result) {
                    //put things back
                    $reset_current_price_result = $this->set_current_and_price_to_zero();
                    return false;
                }
                $this->bid_quantity = $bid_quantity;

                $sql = "insert into " . $this->auctions_feedbacks_table . "
					(rated_user_id,rater_user_id,date,auction_id)
					values (?, ?, ?, ?)";
                $query_data = array ($this->auction->seller,$this->bidder->ID,geoUtil::time(),$this->auction_id);
                $insert_feedback_result = $this->db->Execute($sql, $query_data);

                if (!$insert_feedback_result) {
                    trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                    $this->error_message = $this->messages[81];
                    return false;
                }

                $sql = "insert into " . $this->auctions_feedbacks_table . "
					(rated_user_id,rater_user_id,date,auction_id)
					values (?, ?, ?, ?)";
                $query_data = array ($this->bidder->ID,$this->auction->seller,geoUtil::time(),$this->auction_id);
                $insert_feedback_result = $this->db->Execute($sql, $query_data);

                if (!$insert_feedback_result) {
                    trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                    $this->error_message = $this->messages[81];
                    return false;
                }

                //adjust the remaining quantity
                $this->auction->quantity_remaining = $this->auction->quantity_remaining - $bid_quantity;

                if ($costOptionsQuantity) {
                    //update quantity remaining...
                    foreach ($costOptionsQuantity as $type => $quantities) {
                        if ($type == 'ind') {
                            $table = geoTables::listing_cost_option;
                            $field = '`ind_quantity_remaining`';
                        } else {
                            $table = geoTables::listing_cost_option_quantity;
                            $field = '`quantity_remaining`';
                        }
                        foreach ($quantities as $id => $quantity) {
                            $quantity = max(0, $quantity - $bid_quantity);
                            $this->db->Execute(
                                "UPDATE $table SET $field=? WHERE `id`=?",
                                array($quantity, $id)
                            );
                        }
                    }
                }

                //hook for buy now decrease quantity
                $vars = array (
                    'listing' => $this->auction,
                    'bid_quantity' => $bid_quantity,
                    );
                geoOrderItem::callUpdate('buy_now_decrease_quantity', $vars);
                $close_auction = false;
                if ($this->auction->quantity_remaining <= 0) {
                    $close_auction = true;
                    if ($this->auction->buy_now_only) {
                        $price_plan_id = ($seller->auction_price_plan_id) ? $seller->auction_price_plan_id : $seller->price_plan_id;
                        $category = $this->auction->category;
                        $planItem = geoPlanItem::getPlanItem('auction', $price_plan_id, $category);
                        if ($planItem->get('buy_now_only_none_left', 'close') === 'sold') {
                            //Settings set to NOT close auction, just set it to sold
                            $close_auction = false;
                        }
                    }
                    if ($close_auction) {
                        //this user is closing the auction by choosing the buy now option
                        $this->auction->live = 0;
                        //also update end date
                        $this->auction->ends = $current_time;
                    } else {
                        //does NOT close auction, instead set it to show sold sign
                        $this->auction->sold_displayed = 1;

                        geoAddon::triggerUpdate('notify_sold_sign_status_changed', array('listingId' => $this->auction->id, 'new_status' => 1, 'is_auction' => 1));
                    }
                    $this->auction->delayed_start = 0;
                    $this->auction->current_bid = $this->auction->price = $this->auction->minimum_bid =
                        $this->auction->final_price = $this->auction->buy_now;

                    geoOrderItem::callUpdate('buy_now_close', $vars);
                }

                //send emails to seller and buy now bidder
                //to buy now bidder
                $this->email_buy_now_bidder_and_seller($aff_id, $seller, $bid_quantity, $cost_options);
                if ($close_auction) {
                    //update category count if the auction is closed.
                    geoCategory::updateListingCount($this->auction->category);
                }
                $this->bid_success = 4;
                return true;
            }

            $reverse = ($this->auction->auction_type == 3);
            $allow_proxy_bids = $this->db->get_site_setting('allow_proxy_bids');

            //get minimum bid
            $minimum_bid = $this->get_minimum_bid();
            if ($reverse) {
                //use different var name to make easier to read, but it will actually
                //be the same as min bid since that function accounts for reverse auctions
                $maximum_bid = $minimum_bid;
            }
            if (!$minimum_bid) {
                return false;
            }
            if ((!$reverse && $bid_amount < $minimum_bid) || ($reverse && $bid_amount > $maximum_bid)) {
                //bid_amount not enough
                //raise your bid
                $this->bid_error = 2;
                trigger_error("ERROR BID: bid error 2, bid not meet minimum");
                return false;
            }

            //If it gets this far, bid was good enough to meet or beat the min bid

            //check to make sure the current bidder is not winning already

            //get the information on the current high bid
            $sql = "SELECT * FROM " . geoTables::bid_table . " WHERE `auction_id`=? ORDER BY `bid` " . (($reverse) ? 'ASC' : 'DESC') . ", `time_of_bid` ASC LIMIT 1";
            $high_bid = $this->db->GetRow($sql, array($this->auction_id));

            $auto_bid = $this->db->GetRow("SELECT * FROM " . geoTables::autobid_table . " WHERE `auction_id` = ?", array($this->auction_id));

            if ($high_bid && $high_bid['bidder'] == $this->bidder->ID) {
                //high bidder is self
                //check to see if the current high bidder can update their bid

                if (!$this->db->get_site_setting('allow_bidding_against_self')) {
                    $this->bid_error = 1;
                    trigger_error("ERROR BID:  bid error 1 - not allowed to edit own bid");
                    return false;
                }
                //this will allow the client to add to their bid...
                //add a proxy bid if they do not have one or...
                //add to their current proxy bid if they have on on this auction

                if (!$allow_proxy_bids) {
                    //no proxy bidding, update the seller's bid

                    //insert the new bid
                    return $this->insert_bid($bid_amount, $bid_amount);
                }

                //check to see if there is a current proxy bid

                if (!$auto_bid) {
                    //there is no current proxy bid
                    //enter this bid as the current proxy bid for this bidder
                    //need to check against the reserve price
                    if ($this->auction->reserve_price != 0) {
                        //there is a reserve...check that it has been met
                        if ((!$reverse && $minimum_bid > $this->auction->reserve_price) || ($reverse && $maximum_bid < $this->auction->reserve_price)) {
                            //the reserve has been met...insert this bid
                            //as the proxy for this bidder on this auction
                            //no need to update the auction as none of that will change
                            //only the proxy will be added

                            $insert_autobid_result = $this->insert_into_autobid_table($bid_amount, $current_time, $quantity);
                            if (!$insert_autobid_result) {
                                trigger_error("ERROR BID:  insert autobid failed.");
                                return false;
                            }
                        } else {
                            //the reserve has not been met
                            //check that the bid_amount beats the reserve or not
                            if ((!$reverse && $bid_amount > $this->auction->reserve_price) || ($reverse && $bid_amount < $this->auction->reserve_price)) {
                                //enter the current bid to the reserve prise
                                //enter any amount above the reserve price as the proxy bid for this bidder
                                $amount_to_insert = $this->auction->reserve_price;
                                $reserve_met = true;
                            } else {
                                //$bid_amount <= $this->auction->reserve_price
                                //enter the bid_amount as the current bid only
                                $amount_to_insert = $bid_amount;
                                $reserve_met = false;
                            }
                            $min = ($reverse) ? ($amount_to_insert - $increment) : ($amount_to_insert + $increment);
                            $set_minimum_result = $this->update_minimum_current_and_price($min, $amount_to_insert, $amount_to_insert);
                            if (!$set_minimum_result) {
                                trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                                return false;
                            }

                            $insert_bid_result = $this->insert_into_bid_table($amount_to_insert, $current_time, $quantity);
                            if (!$insert_bid_result) {
                                //put things back
                                $reset_current_price_result = $this->set_current_and_price_to_zero();
                                return false;
                            }

                            if ($reserve_met) {
                                $a_bid = $this->auction->reserve_price;
                                //insert bid into the autobid table because it was greater then minimum_bid
                                //minimum_bid is the current minimum because there were no previous bids
                                $insert_autobid_result = $this->insert_into_autobid_table($bid_amount, $current_time, $quantity);
                                if (!$insert_autobid_result) {
                                    trigger_error("ERROR BID:  insert autobid resulted false");
                                    return false;
                                }
                            }
                        }
                    } else {
                        //there is no current proxy and
                        //there is no reserve so insert the bid_amount
                        //as a proxy bid for this bidder on this auction
                        $insert_autobid_result = $this->insert_into_autobid_table($bid_amount, $current_time, $quantity);
                        if (!$insert_autobid_result) {
                            trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                            return false;
                        }
                    }
                    //if it gets this far, bid was inserted and was successful
                    $this->auction_extension_check(geoUtil::time());
                    $this->send_current_high_bidder_email($db, $this->bidder->ID);
                    $this->bid_success = 1;
                    $this->start_delayed_auction();
                    return true;
                } else {
                    //this bidder has a current proxy bid
                    //check to see current proxy bid is higher than amount just bid or not
                    if ((!$reverse && $bid_amount > $auto_bid['maxbid']) || ($reverse && $bid_amount < $auto_bid['maxbid'])) {
                        //update the current proxy bid with the current bid
                        $a_bid = $bid_amount;
                        $sql = "UPDATE " . geoTables::autobid_table . " SET
							`time_of_bid` = ?,
							`quantity` = ?,
							`maxbid` = ?
							WHERE `auction_id` = ? AND `bidder` = ?";
                        $query_data = array ($current_time, $quantity, $bid_amount, $this->auction_id, $this->bidder->ID);
                        $insert_autobid_result = $this->db->Execute($sql, $query_data);

                        if (!$insert_autobid_result) {
                            trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                            return false;
                        }
                        $this->auction_extension_check(geoUtil::time());
                        $this->send_current_high_bidder_email($db, $this->bidder->ID);
                        $this->bid_success = 1;
                        $this->start_delayed_auction();
                        return true;
                    } else {
                        //the bid_amount was equal to or lesser than the current proxy bid
                        //so do nothing
                        $this->auction_extension_check(geoUtil::time());
                        return true;
                    }
                }
            }

            if (!$allow_proxy_bids) {
                //no proxy bidding, so bidding is simple... do straight bidding
                if ($high_bid) {
                    $this->send_outbid_email($db, $high_bid['bidder']);
                }
                return $this->insert_bid($bid_amount, $bid_amount);
            }

            if (!$high_bid) {
                //got here because there are no bids yet on this item
                //set current bid as the minimum bid in auction table

                if ($this->auction->reserve_price > 0) {
                    if ((!$reverse && $bid_amount >= $this->auction->reserve_price) || ($reverse && $bid_amount <= $this->auction->reserve_price)) {
                        //there is a reserve and the bid amount beats the reserve
                        $insert_bid_amount = $this->auction->reserve_price;
                    } else {
                        //there is reserve and bid does not match it yet
                        if ($allow_proxy_bids == 'reserve_met') {
                            $insert_bid_amount = $bid_amount;
                        } else {
                            $insert_bid_amount = $this->auction->starting_bid;
                        }
                    }
                } else {
                    //first bid, and no reserve price
                    $insert_bid_amount = $this->auction->starting_bid;
                }

                return $this->insert_bid($insert_bid_amount, $bid_amount);
            }
            //got here because this auction already has a bid on it
            //this bid is above the minimum bid so at least some bid activity will take place.
            //check to see if there is a current autobid

            if (!$auto_bid) {
                //there is no proxy bid for this auction
                //this bid is above the minimum
                //this bid is the highest bid so far

                //check to see if reserve is above current minimum bid
                if ($this->auction->reserve_price > 0 && ((!$reverse && $bid_amount < $this->auction->reserve_price) || ($reverse && $bid_amount > $this->auction->reserve_price))) {
                    //bid amount is less than reserve
                    //the reserve exists
                    //the minimum is less that the reserve
                    //the bid amount is at least the minimum bid

                    if ($allow_proxy_bids == 'reserve_met') {
                        //this bid will be entered directly as a bid...no proxy
                        $insert_bid_amount = $bid_amount;
                    } else {
                        $insert_bid_amount = $minimum_bid;
                    }
                } elseif ($this->auction->reserve_price > 0 && ((!$reverse && $minimum_bid <= $this->auction->reserve_price) || ($reverse && $minimum_bid >= $this->auction->reserve_price))) {
                    //bid is more than reserve
                    //the reserve exists
                    //the minimum is less than the reserve
                    $insert_bid_amount = $this->auction->reserve_price;
                } else {
                    //already met reserve or there is no reserve
                    //there is no proxy bid
                    $insert_bid_amount = $minimum_bid;
                }
                $this->send_outbid_email($db, $high_bid['bidder']);
                return $this->insert_bid($insert_bid_amount, $bid_amount);
            }
            //there is proxy bid for this auction
            //there already is a price in the autobid table higher than the current price
            //pull the price from the autobid table and test it against this bid

            //get increment for the maxbid range
            $increment = $this->get_increment($auto_bid['maxbid']);
            $maxbid_increment = ($reverse) ? $auto_bid['maxbid'] - $increment : $auto_bid['maxbid'] + $increment;
            if ((!$reverse && $bid_amount > $auto_bid['maxbid']) || ($reverse && $bid_amount < $auto_bid['maxbid'])) {
                //current bid is greater than maxbid of other user
                //we have a new high bid
                if (
                    (!$reverse && $auto_bid['maxbid'] >= $this->auction->reserve_price) || ($reverse && $auto_bid['maxbid'] <= $this->auction->reserve_price) ||
                    ($this->auction->reserve_price == 0) ||
                    (!$reverse && $bid_amount < $this->auction->reserve_price) || ($reverse && $bid_amount > $this->auction->reserve_price)
                ) {
                    //Max bid set is equal to the reserve price or bid amount is less than reserve price
                    //current bid is less than the reserve but greater than proxy bid
                    if ((!$reverse && $bid_amount > $maxbid_increment) || ($reverse && $bid_amount < $maxbid_increment)) {
                        $increment = $this->get_increment($maxbid_increment);
                        //Maxbid_increment becomes the new current bid for this bidder
                        //enter maxbid_increment into bid table as the current bid of the current bidder
                        //update autobid enter bid as the maxbid for this table
                        //update auctions table set maxbid_increment as the current bid
                        $min = ($reverse) ? ($maxbid_increment - $increment) : ($increment + $maxbid_increment);
                        $update_result = $this->update_minimum_current_and_price($min, $maxbid_increment, $maxbid_increment);
                        if (!$update_result) {
                            trigger_error("ERROR BID: update min failed");
                            return false;
                        }
                        $insert_bid_result = $this->insert_into_bid_table($auto_bid['maxbid'], $current_time, $auto_bid['quantity'], 0, $auto_bid['bidder']);
                        if (!$insert_bid_result) {
                            trigger_error("ERROR BID: insert bid failed");
                            return false;
                        }

                        $insert_bid_result = $this->insert_into_bid_table($maxbid_increment, $current_time, $quantity);
                        if (!$insert_bid_result) {
                            trigger_error("ERROR BID: insert bid failed");
                            return false;
                        }

                        $sql = "update " . geoTables::autobid_table . "  set
							maxbid = ?,
							bidder = ?
							where auction_id = ?";
                        $update_autobid_result = $this->db->Execute($sql, array($bid_amount, $this->bidder->ID, $this->auction_id));

                        if (!$update_autobid_result) {
                            trigger_error("ERROR SQL BID: sql: $sql error: " . $this->db->ErrorMsg());
                            return false;
                        }

                        $a_bid = $maxbid_increment;
                        //bidder is current high bidder
                        $this->bid_success = 1;
                        $this->auction_extension_check(geoUtil::time());
                        $this->send_current_high_bidder_email($db, $this->bidder->ID);
                        $this->send_outbid_email($db, $high_bid['bidder']);
                        return true;
                    } elseif ($bid_amount == $maxbid_increment) {
                        //maxbid_increment becomes the new current bid
                        //enter maxbid_increment into the bid table as the current bid for this bidder
                        //update auctions table set maxbid_increment as the current bid
                        //delete this auction from the autobid table

                        $increment = $this->get_increment($maxbid_increment);
                        $min = ($reverse) ? ($maxbid_increment - $increment) : ($increment + $maxbid_increment);
                        $update_result = $this->update_minimum_current_and_price($min, $maxbid_increment, $maxbid_increment);
                        if (!$update_result) {
                            trigger_error("ERROR BID: update min failed");
                            return false;
                        }

                        $insert_bid_result = $this->insert_into_bid_table($auto_bid['maxbid'], $current_time, $auto_bid['quantity'], 0, $auto_bid['bidder']);
                        if (!$insert_bid_result) {
                            trigger_error("ERROR BID: insert bid failed");
                            return false;
                        }

                        $insert_bid_result = $this->insert_into_bid_table($maxbid_increment, $current_time, $quantity);
                        if (!$insert_bid_result) {
                            trigger_error("ERROR BID: insert bid failed");
                            return false;
                        }

                        $delete_autobid_result = $this->delete_from_autobid_table();
                        if (!$delete_autobid_result) {
                            if ($this->DEBUG_BID) {
                                echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
                            }
                            return false;
                        }
                        $this->auction_extension_check(geoUtil::time());
                        $this->bid_success = 1; //bid made but not high bid
                        $this->send_current_high_bidder_email($db, $this->bidder->ID);
                        $this->send_outbid_email($db, $high_bid['bidder']);
                        return true;
                    } else {
                        //bid is greater than the current maxbid in the autobid table
                        //but not bigger than the incremented autobid value
                        //enter bid into the bid table as the bid for this bidder
                        //update auctions table current bid equals bid
                        //delete this auction from the autobid table

                        $increment = $this->get_increment($maxbid_increment);
                        $min = ($reverse) ? ($bid_amount - $increment) : ($bid_amount + $increment);
                        $update_result = $this->update_minimum_current_and_price($min, $bid_amount, $bid_amount);
                        if (!$update_result) {
                            trigger_error("ERROR BID: update failed");
                            return false;
                        }

                        $insert_bid_result = $this->insert_into_bid_table($auto_bid['maxbid'], $current_time, $auto_bid['quantity'], 0, $auto_bid['bidder']);
                        if (!$insert_bid_result) {
                            trigger_error("ERROR BID: insert failed");
                            return false;
                        }

                        $insert_bid_result = $this->insert_into_bid_table($bid_amount, $current_time, $quantity);
                        if (!$insert_bid_result) {
                            trigger_error("ERROR BID: insert failed");
                            return false;
                        }

                        $delete_autobid_result = $this->delete_from_autobid_table();
                        if (!$delete_autobid_result) {
                            if ($this->DEBUG_BID) {
                                echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
                            }
                            return false;
                        }
                        $this->auction_extension_check(geoUtil::time());
                        $this->send_current_high_bidder_email($db, $this->bidder->ID);
                        $this->send_outbid_email($db, $high_bid['bidder']);
                        $this->bid_success = 1;
                        return true;
                    }
                } else {
                    //bid amount is equal to or greater than reserve price
                    //got here because the max proxy bid was less than reserve
                    //and there is a reserve price
                    //and the current bid is greater than the reserve price

                    //set the new minimum bid = reserve price
                    //set proxy bid amount if necessary
                    //remove old proxy bid
                    $increment = $this->get_increment($this->auction->reserve_price);
                    $min = ($reverse) ? ($this->auction->reserve_price - $increment) : ($this->auction->reserve_price + $increment);
                    $set_minimum_result = $this->update_minimum_current_and_price($min, $this->auction->reserve_price, $this->auction->reserve_price);
                    if (!$set_minimum_result) {
                        trigger_error("ERROR BID: set min failed");
                        return false;
                    } else {
                        $delete_autobid_result = $this->delete_from_autobid_table();
                        if (!$delete_autobid_result) {
                            trigger_error("ERROR BID: delete autobid failed");
                            return false;
                        }

                        $insert_bid_result = $this->insert_into_bid_table($this->auction->reserve_price, $current_time, $quantity);
                        if (!$insert_bid_result) {
                            if ($this->DEBUG_BID) {
                                echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
                            }
                            //put things back
                            $reset_current_price_result = $this->set_current_and_price_to_zero();
                            return false;
                        } else {
                            if ((!$reverse && $bid_amount > $this->auction->reserve_price) || ($reverse && $bid_amount < $this->auction->reserve_price)) {
                                //insert bid into the autobid table because it was greater then minimum_bid
                                //minimum_bid is the current minimum because there were no previous bids
                                $insert_autobid_result = $this->insert_into_autobid_table($bid_amount, $current_time, $quantity);
                                if (!$insert_autobid_result) {
                                    trigger_error("ERROR BID: insert autobid failed");
                                    return false;
                                }
                            }
                            $this->send_outbid_email($db, $high_bid['bidder']);
                            $this->send_current_high_bidder_email($db, $this->bidder->ID);
                            $this->bid_success = 1;
                        }
                        $this->auction_extension_check(geoUtil::time());
                        return true;
                    }
                }
            } elseif ($bid_amount == $auto_bid['maxbid']) {
                //Bid amount is equal to the max bid by another user
                //the old bid stands as the new current bid
                //show_autobid[maxbid] is entered into the bid table as a bid for the old bidder
                //remove this auction from the autobid table

                $increment = $this->get_increment($bid_amount);
                $min = ($reverse) ? ($bid_amount - $increment) : ($bid_amount + $increment);
                $update_result = $this->update_minimum_current_and_price($min, $bid_amount, $bid_amount);
                if (!$update_result) {
                    trigger_error("ERROR BID: update failed");
                    return false;
                }

                $insert_bid_result = $this->insert_into_bid_table($auto_bid['maxbid'], $current_time, $auto_bid['quantity'], 0, $auto_bid['bidder']);
                if (!$insert_bid_result) {
                    trigger_error("ERROR BID: update failed");
                    return false;
                }

                $insert_bid_result = $this->insert_into_bid_table($bid_amount, ($current_time + 1), $quantity);
                if (!$insert_bid_result) {
                    trigger_error("ERROR BID: insert failed");
                    return false;
                }

                $delete_autobid_result = $this->delete_from_autobid_table();
                if (!$delete_autobid_result) {
                    trigger_error("ERROR BID: delete failed");
                    return false;
                }

                $this->bid_success = 3;
                $this->send_outbid_email($db, $this->bidder->ID);
                $this->auction_extension_check(geoUtil::time());
                return true;
            } else {
                //Bid is less than the max bid
                $increment = $this->get_increment($bid_amount);
                $incremented_bid = ($reverse) ? ($bid_amount - $increment) : ($increment + $bid_amount);
                if ((!$reverse && $auto_bid['maxbid'] > $incremented_bid) || ($reverse && $auto_bid['maxbid'] < $incremented_bid)) {
                    //show_autobid["maxbid"] remains the same in the autobid table
                    //bid is entered into the bid table as a bid for the current bidder
                    //incremented bid becomes the new bid for the autobid bidder in the bid table at the same time
                    //incremented_bid becomes the current bid in the auction table

                    $insert_bid_result = $this->insert_into_bid_table($bid_amount, $current_time, $quantity);
                    if (!$insert_bid_result) {
                        trigger_error("ERROR BID: insert failed");
                        return false;
                    }

                    $insert_bid_result = $this->insert_into_bid_table($incremented_bid, $current_time, $quantity, 0, $auto_bid['bidder']);
                    if (!$insert_bid_result) {
                        trigger_error("ERROR BID: insert failed");
                        return false;
                    }

                    $increment = $this->get_increment($incremented_bid);
                    $min = ($reverse) ? ($incremented_bid - $increment) : ($incremented_bid + $increment);
                    $update_result = $this->update_minimum_current_and_price($min, $incremented_bid, $incremented_bid);
                    if (!$update_result) {
                        trigger_error("ERROR BID: update failed");
                        return false;
                    }
                    $this->auction_extension_check(geoUtil::time());
                    $this->bid_success = 3;
                    $this->send_outbid_email($db, $this->bidder->ID);
                    return true;
                } elseif ($auto_bid['maxbid'] == $incremented_bid) {
                    // the autobid and the incremented bid are equal
                    //the old show_autobid[maxbid] is the new current bid in the auction table
                    //show_autobid[maxbid] is entered into bid table as the current bid for the show_autobid[bidder]
                    //this auction is removed from the autobid table
                    //current bid is entered into the bid table for the current bidder first the old bid is entered

                    $insert_bid_result = $this->insert_into_bid_table($incremented_bid, $current_time, $quantity, 0, $auto_bid['bidder']);
                    if (!$insert_bid_result) {
                        trigger_error("ERROR BID: insert failed");
                        return false;
                    }

                    $insert_bid_result = $this->insert_into_bid_table($bid_amount, $current_time, $quantity);
                    if (!$insert_bid_result) {
                        trigger_error("ERROR BID: insert failed");
                        return false;
                    }

                    $increment = $this->get_increment($incremented_bid);
                    $min = ($reverse) ? ($incremented_bid - $increment) : ($incremented_bid + $increment);
                    $update_result = $this->update_minimum_current_and_price($min, $incremented_bid, $incremented_bid);
                    if (!$update_result) {
                        trigger_error("ERROR BID: update failed");
                        return false;
                    }

                    $delete_autobid_result = $this->delete_from_autobid_table();
                    if (!$delete_autobid_result) {
                        trigger_error("ERROR BID: delete failed");
                        return false;
                    }
                    $this->auction_extension_check(geoUtil::time());
                    $this->bid_success = 3;
                    $this->send_outbid_email($db, $this->bidder->ID);
                    return true;
                } else {
                    //show_autobid[maxbid] is greater than bid but not bigger than incremented bid
                    //show_autobid[maxbid] becomes the current bid in the auction table
                    //bid is entered into the bid table as a bid for the current bidder
                    //show_autobid[maxbid] is entered into the bid table as a bid for the autobid bidder
                    //this auction is removed from the autobid table

                    $insert_bid_result = $this->insert_into_bid_table($bid_amount, $current_time, $quantity);
                    if (!$insert_bid_result) {
                        trigger_error("ERROR BID: insert failed");
                        return false;
                    }

                    $insert_bid_result = $this->insert_into_bid_table($auto_bid['maxbid'], $current_time, $quantity, 0, $auto_bid['bidder']);
                    if (!$insert_bid_result) {
                        trigger_error("ERROR BID: insert failed");
                        return false;
                    }

                    $increment = $this->get_increment($auto_bid['maxbid']);
                    $min = ($reverse) ? ($auto_bid['maxbid'] - $increment) : ($auto_bid['maxbid'] + $increment);
                    $update_result = $this->update_minimum_current_and_price($min, $auto_bid['maxbid'], $auto_bid['maxbid']);
                    if (!$update_result) {
                        trigger_error("ERROR BID: update failed");
                        return false;
                    }

                    $delete_autobid_result = $this->delete_from_autobid_table();
                    if (!$delete_autobid_result) {
                        trigger_error("ERROR BID: delete failed");
                        return false;
                    }
                    $this->auction_extension_check(geoUtil::time());
                    $this->bid_success = 3;
                    $this->send_outbid_email($db, $this->bidder->ID);
                    return true;
                }
            }
        }
    }

    public function get_minimum_bid()
    {
        if (floatval($this->auction->current_bid) < 0.01 && floatval($this->auction->starting_bid) >= 0.01) {
            //echo floatval($this->auction->current_bid) . ' <= 0 AND ' . floatval($this->auction->starting_bid) . ' > 0';
            return $this->auction->starting_bid;
        } elseif (floatval($this->auction->current_bid) <= 0.00 && floatval($this->auction->starting_bid) <= 0.00) {
            //check minimum bid in case a value was placed there but not
            //in the starting bid
            if (floatval($this->auction->minimum_bid) > 0) {
                return $this->auction->minimum_bid;
            } else {
                return 0.01;
            }
        } else {
            //get bid increment
            $increment = $this->get_increment($this->auction->current_bid);
            if ($increment) {
                if ($this->auction->auction_type == 3) {
                    //reverse auction, subtract the increment instead of raise...
                    return $this->auction->current_bid - $increment;
                }
                return $this->auction->current_bid + $increment;
            } else {
                return false;
            }
        }
    }

    /**
     * Actually just calls $this->get_minimum_bid() since that function automatically
     * accounts for if auction is reverse auction.
     */
    public function get_maximum_bid()
    {
        return $this->get_minimum_bid();
    }

    public function get_increment($amount)
    {
        $function_name = "get_increment";

        $sql = "select `increment` from " . geoTables::increments_table . " where
			`low` <= ? ORDER BY `low` DESC limit 1";
        $increment_result = $this->db->Execute($sql, array($amount));

        if (!$increment_result) {
            if ($this->DEBUG_BID) {
                echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
            }
            $this->error_message = urldecode($this->messages[100081]);
            return 1;
        } elseif ($increment_result->RecordCount() == 1) {
            $show_increment = $increment_result->FetchNextObject();
            if ($this->DEBUG_BID) {
                echo $show_increment->INCREMENT . " is \$show_increment->INCREMENT<Br>\n";
            }
            if ($show_increment->INCREMENT == 0) {
                //it messes things up if the increment is 0, so make it $1 instead.
                return 1;
            }
             return $show_increment->INCREMENT;
        } else {
            return 1;
        }
    } //end of function get_increment

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function send_current_high_bidder_email($db, $bidder_id)
    {
        if (!$bidder_id || !$this->auction) {
            return false;
        }

        $bidder = geoUser::getUser($bidder_id);
        $seller = geoUser::getUser($this->auction->seller);

        $this->page_id = 10168;
        $this->get_text();

        $reverse = ($this->auction->auction_type == 3);

        $subject = ($reverse) ? $this->messages[501003] : $this->messages[102480];

        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('salutation', $bidder->getSalutation());
        $tpl->assign('messageBody', $this->messages[(($reverse) ? 501004 : 102481)]);
        $tpl->assign('auctionData', $this->show_email_auction_specs($bidder_id));
        $tpl->assign('bidder', $bidder->toArray());
        $tpl->assign('seller', $seller->toArray());
        $tpl->assign('auction', $this->auction->toArray());
        $body = $tpl->fetch('auctions/auction_current_high_bidder.tpl');
        geoEmail::sendMail($bidder->email, $subject, $body, 0, 0, 0, 'text/html');

        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function send_outbid_email(&$db, $bidder_id)
    {
        $bidder = geoUser::getUser($bidder_id);
        $seller = geoUser::getUser($this->auction->seller);

        if (!$bidder || !$this->auction) {
            return false;
        }

        $reverse = ($this->auction->auction_type == 3);

        $this->page_id = 10169;
        $this->get_text();

        $subject = ($reverse) ? $this->messages[501006] : $this->messages[102473];

        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('salutation', $bidder->getSalutation());
        $tpl->assign('messageBody', $this->messages[(($reverse) ? 501007 : 102474)]);
        $tpl->assign('auctionData', $this->show_email_auction_specs());
        $tpl->assign('bidder', $bidder->toArray());
        $tpl->assign('seller', $seller->toArray());
        $tpl->assign('auction', $this->auction->toArray());
        $body = $tpl->fetch('auctions/auction_outbid.tpl');
        geoEmail::sendMail($bidder->email, $subject, $body, 0, 0, 0, 'text/html');

        return true;
    }


//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function show_email_auction_specs($showCurrentBidFor = 0)
    {
        $db = DataAccess::getInstance();
        $msgs = $this->messages = $db->get_text(true, 10170);

        $reverse = ($this->auction->auction_type == 3);

        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('titleLabel', $msgs[102475]);
        $tpl->assign('title', geoString::fromDB($this->auction->title));

        if ($showCurrentBidFor) {
            $currentBid = geoString::displayPrice($this->auction->current_bid, $this->auction->precurrency, $this->auction->postcurrency);
            $tpl->assign('currentBidLabel', $msgs[102482]);
            $tpl->assign('currentBid', $currentBid);

            $sql = "select `maxbid` from " . geoTables::autobid_table . " where auction_id = ? and bidder = ?";
            $maxBid = $db->GetOne($sql, array($this->auction->id, $showCurrentBidFor));
            if ($maxBid) {
                $maxBid = geoString::displayPrice($maxBid, $this->auction->precurrency, $this->auction->postcurrency);
                $tpl->assign('maxBidLabel', $msgs[(($reverse) ? 501005 : 102483)]);
                $tpl->assign('maxBid', $maxBid);
            }
        }

        $tpl->assign('endDateLabel', $msgs[102478]);
        $tpl->assign('endDate', date($this->db->get_site_setting('entry_date_configuration'), $this->auction->ends));
        $tpl->assign('listingLinkLabel', $this->messages[102479]);
        $tpl->assign('listingLink', geoListing::getListing($this->auction->id)->getFullUrl());

        $return = $tpl->fetch('auctions/auction_bid_data.tpl');

        return $return;
    }

//##############################################################################

    function get_bid_history($db, $auction_id, $aff_id = 0)
    {
        $this->page_id = 10171;
        $this->get_text();

        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $auction_id = (int)$auction_id;
        if (!$auction_id) {
            return false;
        }

        $listing = geoListing::getListing($auction_id);
        if ($listing->auction_type != 3) {
            $orderBy = 'bid DESC, time_of_bid DESC';
        } elseif ($listing->auction_type == 3) {
            $orderBy = 'bid ASC, time_of_bid DESC';
        }

        $sql = "select * from " . geoTables::bid_table . " where auction_id = ? order by " . $orderBy;
        $bid_history_result = $db->Execute($sql, array($auction_id));

        if (!$bid_history_result) {
            return false;
        } elseif ($bid_history_result->RecordCount() > 0) {
            $current_auction = $this->get_classified_data($auction_id);
            //there are bids on this auction and show them

            if ($current_auction->AUCTION_TYPE == 2) {
                $view->is_dutch = true;
            }

            $view->show_bidder_email = ($this->db->get_site_setting('view_email_after_auction') && ($this->userid == $current_auction->SELLER)) ? true : false;

            $bids = array();
            for ($i = 0; $show_bid = $bid_history_result->FetchNextObject(); $i++) {
                $bids[$i]['time_of_bid'] = date($this->db->get_site_setting('entry_date_configuration'), $show_bid->TIME_OF_BID);
                $bids[$i]['bid_amount'] = $this->show_money($show_bid->BID, $current_auction->PRECURRENCY, $current_auction->POSTCURRENCY);
                if ($current_auction->AUCTION_TYPE == 2) {
                    // If dutch auctions show quantity
                    $bids[$i]['quantity'] = $show_bid->QUANTITY;
                }
                $user = geoUser::getUser($show_bid->BIDDER);
                $bids[$i]['bidder_name'] = $user->username;
                $bids[$i]['bidder_email'] = $user->email;
                $bids[$i]['bidder_feedback_link'] = $db->get_site_setting('classifieds_file_name') . '?a=1030&amp;b=' . $auction_id . '&amp;d=' . $user->id;
            }
            $view->bids = $bids;
        } else {
            //there were no bids for this auction
            $view->no_bids = true;
        }
        $view->auctionLink = $db->get_site_setting('classifieds_file_name') . "?a=2&b=" . $auction_id;
        $view->setBodyTpl('bidding/bid_history.tpl', '', 'auctions');
        $this->display_page();
        return true;
    }

//##############################################################################
    /**
     * Emails all the dutch bidders that have just lost a bid
     */
    function email_dutch_bidders_new_bid(&$db, &$bid_count, &$aff_id)
    {
        $this->page_id = 10166;
        $this->get_text();

        $listing = geoListing::getListing($this->auction->id);
        $subject = $this->messages[102488];
        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('messageBody', $this->messages[102491]);
        $tpl->assign('quantityIncLabel', $this->messages[102489]);
        $tpl->assign('pastBidLabel', $this->messages[102490]);
        $tpl->assign('listingTitle', geoString::fromDB($listing->title));
        $tpl->assign('listingLink', $listing->getFullUrl());
        $tpl->assign('sellerInfo', geoUser::getUser($listing->seller)->toArray());

        for ($i = $this->winning_dutch_bidder_count; $i < $bid_count; $i++) {
            $current_bidder = geoUser::getUser($this->dutch_bidders[$i]["bidder"]);
            $tpl->assign('salutation', $current_bidder->getSalutation());
            $tpl->assign('quantityInc', $this->dutch_bidders[$i]['quantity']);
            $tpl->assign('pastBid', geoString::displayPrice($this->dutch_bidders[$i]['bid'], $listing->precurrency, $listing->postcurrency));
            $body = $tpl->fetch('auctions/dutch/auction_dutch_outbid.tpl');
            geoEmail::sendMail($current_bidder->email, $subject, $body, 0, 0, 0, 'text/html');
        }
    }
    //##############################################################################
    /**
     * Emails the dutch bidder if they made a successful bid
     */
    function email_dutch_bidder_successful_bid(&$db, &$bid_amount, &$aff_id)
    {
        $this->page_id = 10166;
        $this->get_text();

        $subject = $this->messages[102485];
        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('salutation', geoUser::getUser($this->bidder->ID)->getSalutation());
        $tpl->assign('messageBody', $this->messages[102484]);
        $tpl->assign('quantityLabel', $this->messages[102455]);
        $tpl->assign('quantity', $this->dutch_bidder_quantity);
        $tpl->assign('bidLabel', $this->messages[102482]);
        $tpl->assign('bid', geoString::displayPrice($bid_amount, $this->auction->precurrency, $this->auction->postcurrency));
        $tpl->assign('listingTitle', geoString::fromDB($this->auction->title));
        $tpl->assign('listingLink', geoListing::getListing($this->auction_id)->getFullUrl());
        $tpl->assign('sellerInfo', geoUser::getUser($this->auction->seller)->toArray());
        $body = $tpl->fetch('auctions/dutch/auction_dutch_bid_successful.tpl');

        geoEmail::sendMail($this->bidder->EMAIL, $subject, $body, 0, 0, 0, 'text/html');
    }

    //##############################################################################

    /**
     * Emails the dutch bidder if they made an unsuccessful bid
     */
    function email_dutch_bidder_not_successful_bid(&$db, &$bid_amount, &$aff_id)
    {

        $this->page_id = 10166;
        $this->get_text();

        $subject = $this->messages[102486];
        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('salutation', geoUser::getUser($this->bidder->ID)->getSalutation());
        $tpl->assign('messageBody', $this->messages[102487]);
        $tpl->assign('bidLabel', $this->messages[102482]);
        $tpl->assign('bid', geoString::displayPrice($bid_amount, $this->auction->precurrency, $this->auction->postcurrency));
        $tpl->assign('listingTitle', geoString::fromDB($this->auction->title));
        $tpl->assign('listingLink', geoListing::getListing($this->auction_id)->getFullUrl());
        $tpl->assign('sellerInfo', geoUser::getUser($this->auction->seller)->toArray());
        $body = $tpl->fetch('auctions/dutch/auction_dutch_bid_not_successful.tpl');

        geoEmail::sendMail($this->bidder->EMAIL, $subject, $body, 0, 0, 0, 'text/html');

        $this->bid_error = 6;
    }
    //##############################################################################

    /**
     * Emails if they are the only dutch bidder
     */
    function email_only_dutch_bidder(&$db, &$bid_amount, &$aff_id, &$bid_quantity)
    {
        $msgs = DataAccess::getInstance()->get_text(true);

        $subject = $msgs[102485];
        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('salutation', geoUser::getUser($this->bidder->ID)->getSalutation());
        $tpl->assign('messageBody', $msgs[102484]);
        $tpl->assign('quantityLabel', $msgs[102455]);
        $tpl->assign('quantity', $bid_quantity);
        $tpl->assign('bidLabel', $msgs[102482]);
        $tpl->assign('bid', geoString::displayPrice($bid_amount, $this->auction->precurrency, $this->auction->postcurrency));
        $tpl->assign('listingTitle', geoString::fromDB($this->auction->title));
        $tpl->assign('listingLink', geoListing::getListing($this->auction_id)->getFullUrl());
        $tpl->assign('sellerInfo', geoUser::getUser($this->auction->seller)->toArray());
        $body = $tpl->fetch('auctions/dutch/auction_dutch_bid_only_bidder.tpl');

        geoEmail::sendMail($this->bidder->EMAIL, $subject, $body, 0, 0, 0, 'text/html');
    }

    //##############################################################################

    /**
     * generates text for additional fees
     *
     * @deprecated Version 7.2.0 (april 3, 2013), will be removed in future version,
     *   see new {@link geoListing::getAuctionAdditionalFees()} method or new
     *   method get_additional_fees() in this class.
     */
    function get_additional_fee_text()
    {
        //display any optional fields that add to the cost.
        $additional_costs = array ( 'total' => 0);
        $message_data = '';
        $userId = $this->auction->seller;
        $groupId = ($userId) ? geoUser::getUser($userId)->group_id : 0;

        $fields = geoFields::getInstance($groupId, $this->auction->category);
        for ($i = 1; $i < 21; $i++) {
            //go through all the optional fields, see if they add cost, and if they do,
            //see if the value actually adds any cost (not 0 or blank field)
            $option = 'OPTIONAL_FIELD_' . $i;
            $fieldName = 'optional_field_' . $i;

            if ($fields->$fieldName->field_type == 'cost' && $this->auction->$option > 0) {
                //this optional field needs to be displayed.
                $additional_costs[$i] = $this->show_money($this->auction->$option, $this->auction->precurrency, $this->auction->postcurrency);
                $additional_costs['total'] += $this->auction->$option;
            }
        }
        if ($additional_costs['total'] > 0) {
            //there are additional costs to display!
            $message_data .= urldecode($this->messages[500033]) . "\n";
            foreach ($additional_costs as $key => $cost) {
                //go through all the additional costs and display them
                if ($key != 'total') { //don't display the total twice!
                    $message_data .= $cost . "\n";
                }
            }
            //display the additional fee total.
            $message_data .= urldecode($this->messages[500035]) . $this->show_money($additional_costs['total'], $this->auction->precurrency, $this->auction->postcurrency) . "\n\n";
            //display the grand total
            $grand_total = $this->auction->buy_now + $additional_costs['total'];
            $grand_total = $this->show_money($grand_total, $this->auction->precurrency, $this->auction->postcurrency);
            $message_data .= urldecode($this->messages[500036]) . $grand_total . "\n\n";
            //display the additional fee disclaimer
            $message_data .= urldecode($this->messages[500034]) . "\n\n";
        }
        return $message_data;
    }

    /**
     * Gets additional fees, and adds the "grandTotal" for buy now auctions
     *
     * @param int $bid_quantity Parameter added in {@since Version 7.2.0}
     * @return array
     * @since Version 7.2.0
     */
    public function get_additional_fees($bid_quantity = 1)
    {
        $additional_fees = geoListing::getAuctionAdditionalFees($this->auction->id);

        if ($additional_fees) {
            //figure out the grand total
            $total = $additional_fees['raw']['total'] + $this->auction->buy_now;
            $additional_fees['grandTotal'] = $additional_fees['grandGrandTotal'] = geoString::displayPrice($total, $this->auction->precurrency, $this->auction->postcurrency);
            if ($this->auction->price_applies == 'item') {
                //the "GRAND" grand total is multiplied by quantity
                $grandTotal = $total * $bid_quantity;
                $additional_fees['grandGrandTotal'] = geoString::displayPrice($grandTotal, $this->auction->precurrency, $this->auction->postcurrency);
            }
        }
        return $additional_fees;
    }
    //##############################################################################

    /**
     * Emails buy-now bidder and seller
     */
    public function email_buy_now_bidder_and_seller($aff_id, $seller, $bid_quantity, $cost_options)
    {
        $db = DataAccess::getInstance();
        $msgs = $db->get_text(true);
        $listing = geoListing::getListing($this->auction->id);
        $buyer = geoUser::getUser($this->bidder->ID);

        $additional_fees = $this->get_additional_fees($bid_quantity);
        $option_details = $option_group_details = false;
        $cost_options_cost = 0;
        if ($cost_options) {
            //most likely the option info is cached so get the info this way
            $listing_options = geoListing::getCostOptions($this->auction->id);
            $option_details = $option_group_details = array();

            if ($listing_options['hasFileSlot']) {
                $listing_images = geoListing::getImages($this->auction->id);
                //go ahead and figure out parts of the URL once here
                $site_url = $this->db->get_site_setting('classifieds_url');
                $site_url_parts = parse_url(dirname($site_url));
            }

            foreach ($listing_options['groups'] as $group) {
                $option_group_details[$group['id']] = $group;
                //don't need the options as part of the group, redundant
                unset($option_group_details[$group['id']]['options']);
                foreach ($group['options'] as $option) {
                    if (in_array($option['id'], $cost_options)) {
                        if ($option['cost_added'] > 0) {
                            $option['cost_added_pretty'] = geoString::displayPrice($option['cost_added'], $this->auction->precurrency, $this->auction->postcurrency, 'listing');
                        }
                        if ($option['file_slot'] > 0 && isset($listing_images[$option['file_slot']])) {
                            $img = $listing_images[$option['file_slot']];
                            $image_url = '';
                            if ($img['icon']) {
                                //it is an icon
                                $image_url = $img['icon'];
                            } else {
                                $image_url = $img['thumb_url'];
                            }

                            if ($image_url) {
                                //need to add full domain name...
                                $url_prefix = '';
                                if (strpos($image_url, '/') === 0) {
                                    //This starts with / so only use the host prefix
                                    $url_prefix = 'http://' . $site_url_parts['host'];
                                } elseif (strpos($image_url, '://') === false && strpos($image_url, 'http://') > 5) {
                                    //image URL does NOT include full URL
                                    $url_prefix = 'http://' . $site_url_parts['host'] . $site_url_parts['path'] . '/';
                                }
                                $option['image_url'] = $url_prefix . $image_url;
                            }
                        }
                        $option_details[$option['id']] = $option;
                        $cost_options_cost += $option['cost_added'];
                    }
                }
            }
        }
        if ($cost_options_cost > 0) {
            //adjust grand total
            $total = $additional_fees['raw']['total'] + $this->auction->buy_now + $cost_options_cost;
            $additional_fees['grandTotal'] = $additional_fees['grandGrandTotal'] = geoString::displayPrice($total, $this->auction->precurrency, $this->auction->postcurrency);
            if ($this->auction->price_applies == 'item') {
                //the "GRAND" grand total is multiplied by quantity
                $grandTotal = $total * $bid_quantity;
                $additional_fees['grandGrandTotal'] = geoString::displayPrice($grandTotal, $this->auction->precurrency, $this->auction->postcurrency);
            }
            $cost_options_cost_pretty = geoString::displayPrice($cost_options_cost, $this->auction->precurrency, $this->auction->postcurrency);
        }

        //figure out grand (really grand) total...
        if ($additional_fees) {
            if ($additional_fees['grandGrandTotal']) {
                $grandTotal = $additional_fees['grandGrandTotal'];
            } else {
                $grandTotal = $additional_fees['grandTotal'];
            }
        } else {
            $grandTotal = $this->auction->buy_now + $cost_options_cost;
            if ($this->auction->price_applies == 'item') {
                $grandTotal = $grandTotal * $bid_quantity;
            }
            $grandTotal = geoString::displayPrice($grandTotal, $this->auction->precurrency, $this->auction->postcurrency, 'listing');
        }

        //************** TO SELLER ********************
        $subject = $this->messages[102495];
        $toSeller = new geoTemplate('system', 'emails');
        $toSeller->assign('listing_id', $this->auction->id);
        $toSeller->assign('salutation', $seller->getSalutation());
        $toSeller->assign('sellerInfo', $seller->toArray());
        if ($this->auction->quantity_remaining > 0) {
            $toSeller->assign('messageBody', $msgs[502113]);
        } else {
            $toSeller->assign('messageBody', $msgs[102496]);
        }
        $toSeller->assign('finalBidLabel', $msgs[102494]);
        $toSeller->assign('finalBid', geoString::displayPrice($listing->buy_now, $listing->precurrency, $listing->postcurrency));
        $toSeller->assign('price_applies', $listing->price_applies);
        $toSeller->assign('quantity', $bid_quantity);
        $toSeller->assign('cost_options', $cost_options);
        $toSeller->assign('cost_option_details', $option_details);
        $toSeller->assign('cost_option_group_details', $option_group_details);
        $toSeller->assign('cost_options_cost', $cost_options_cost);
        $toSeller->assign('cost_options_cost_pretty', $cost_options_cost_pretty);
        if ($option_details) {
            $toSeller->assign('cost_options_hasFileSlot', $listing_options['hasFileSlot']);
            if ($listing_options['hasFileSlot']) {
                //will need to send in the images to reference
                $toSeller->assign('cost_options_files', geoListing::getImages($this->auction->id));
            }
        }
        $toSeller->assign('grandTotal', $grandTotal);
        if ($listing->price_applies == 'item') {
            $toSeller->assign('quantity_remaining', $listing->quantity_remaining);
            $toSeller->assign('quantity_starting', $listing->quantity);
        }
        $toSeller->assign('auction', $this->auction->toArray());
        $toSeller->assign('additionalFees', $additional_fees);
        $toSeller->assign('highBidderInfo', $buyer->toArray());
        $toSeller->assign('listingTitle', geoString::fromDB($listing->title));
        $toSeller->assign('listingLink', $listing->getFullUrl());
        $body = $toSeller->fetch('auctions/auction_complete_buy_now_seller.tpl');
        trigger_error("DEBUG BID: Email Body:<br /><br /><hr />$body");
        geoEmail::sendMail($seller->email, $subject, $body, 0, 0, 0, 'text/html');

        $subject = $body = ''; //for sanity...

        //************** TO BUYER ********************
        $subject = $this->messages[102492];
        //start from seller template vars set, so we only have to set values
        //that are different...
        $toBuyer = $toSeller;
        $toBuyer->assign('salutation', $buyer->getSalutation());
        $toBuyer->assign('messageBody', $msgs[102493]);
        $toBuyer->assign('sellerInfo', $seller->toArray());
        $toBuyer->assign('auction', $this->auction->toArray());
        $toBuyer->assign('highBidderInfo', $buyer->toArray());

        //see if there should be seller to buyer text
        $vars = array(
            'listing_id' => $this->auction->id,
            'winning_bidder_id' => $this->bidder->ID,
            'listing_details' => $this->auction,
            'final_price' => $this->auction->buy_now,
            'bid_quantity' => $bid_quantity,
        );
        $sb_links = geoSellerBuyer::callDisplay('displayPaymentLinkBuyNowEmail', $vars);
        if (strlen($sb_links) > 0) {
            $toBuyer->assign('sellerBuyerInfo', $sb_links);
        }
        $body = $toBuyer->fetch('auctions/auction_complete_buy_now_buyer.tpl');
        geoEmail::sendMail($buyer->email, $subject, $body, 0, 0, 0, 'text/html');
    }

//##############################################################################

    function start_delayed_auction()
    {
        if ($this->auction->delayed_start == 1) {
            //set the date (start date) to now
            //set the ends (end date) to the current start date plus the duration
            //set delayed_start to 0

            $date = geoUtil::time();
            $auction_length = $this->auction->duration * 86400;
            $ends = geoUtil::time() + $auction_length;
            $sql = "UPDATE " . geoTables::classifieds_table . " SET 
				`date` = ? ,
				`ends` = ? ,
				`delayed_start` = 0
				WHERE `id` = ? ";
            $start_auction_result = $this->db->Execute($sql, array($date, $ends, $this->auction_id));
            if (!$start_auction_result) {
                echo $this->db->ErrorMsg() . " is the errormsg<br>\n";
                echo $sql . " is the sql<br>\n";
                echo $date . " is the \$date<br>\n";
                echo $ends . " is the \$ends<br>\n";
                echo $this->auction_id . " is the \$this->auction_id<br>\n";
                return false;
            } else {
                //successfully started auction from delayed start state
                return true;
            }
        } else {
            //this is not a delayed start auction
            return true;
        }
    } //end of function start_delayed_auction

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_into_bid_table($bid = 0, $current_time = 0, $quantity = 0, $buy_now_bid = 0, $bidder = 0, $cost_options = 0)
    {
        if ($bidder == 0) {
            //use the current bidder logged in
            $bidder = $this->bidder->ID;
        } else {
            //use the bidder passed in...could be from proxy
        }
        if ($bid && $current_time && $quantity) {
            $sql = "insert into " . geoTables::bid_table . "
				(auction_id,bidder,bid,time_of_bid,quantity,buy_now_bid,cost_options)
				values (?, ?, ?, ?, ?, ?, ?)";
            $cost_options = ($cost_options) ? json_encode($cost_options) : '';
            $query_data = array ($this->auction_id, $bidder, $bid,$current_time,$quantity,$buy_now_bid, $cost_options);
            $insert_bid_result = $this->db->Execute($sql, $query_data);
            if ($this->DEBUG_BID) {
                echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
            }
            if ($this->db->get_site_setting('debug_bid')) {
                $this->debug_display($sql, $db, $this->filename, $function_name, "bid_table", "insert data into bid table");
            }
            if ($insert_bid_result) {
                //notify any addons that care
                $addon_vars = array(
                    'bid' => $bid,
                    'current_time' => $current_time,
                    'quantity' => $quantity,
                    'buy_now_bid' => $buy_now_bid,
                    'bidder' => $bidder,
                    'cost_options' => $cost_options,
                    'this' => $this,
                );
                geoAddon::triggerUpdate('notify_new_bid_success', $addon_vars);
                return true;
            } else {
                if ($this->DEBUG_BID) {
                    echo 'LINE ' . __LINE__ . ' ' . $sql . " error: " . $this->db->ErrorMsg() . "<br/>\n";
                }
                return false;
            }
        } else {
            return false;
        }
    } //end of function insert_into_bid_table

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_into_autobid_table($bid = 0, $current_time = 0, $quantity = 0, $cost_options = 0)
    {
        if ($bid && $current_time && $quantity) {
            $sql = "insert into " . geoTables::autobid_table . "
				(auction_id,bidder,maxbid,time_of_bid,quantity,cost_options)
				values (?, ?, ?, ?, ?, ?)";
            $cost_options = ($cost_options) ? json_encode($cost_options) : '';
            $query_data = array ($this->auction_id, $this->bidder->ID, $bid, $current_time, $quantity, $cost_options);
            $insert_autobid_result = $this->db->Execute($sql, $query_data);
            if ($this->DEBUG_BID) {
                echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
            }
            if ($this->db->get_site_setting('debug_bid')) {
                $this->debug_display($sql, $db, $this->filename, $function_name, "bid_table", "insert data into auctions table");
            }
            if ($insert_autobid_result) {
                return true;
            } else {
                if ($this->DEBUG_BID) {
                    echo 'LINE ' . __LINE__ . ' ' . $sql . " error: " . $this->db->ErrorMsg() . "<br/>\n";
                    echo $this->auction_id . " is \$this->auction_id<br>\n";
                    echo $this->bidder->ID . " is \$this->bidder->ID<br>\n";
                    echo $bid . " is \$bid<br>\n";
                    echo $current_time . " is \$current_time<br>\n";
                    echo $quantity . " is \$quantity<br>\n";
                }
                return false;
            }
        } else {
            return false;
        }
    } //end of function insert_into_autobid_table

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_from_autobid_table()
    {
        $sql = "delete from " . geoTables::autobid_table . "  where auction_id = ?";
        $delete_autobid_result = $this->db->Execute($sql, array($this->auction_id));
        if ($this->DEBUG_BID) {
            echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
        }

        if ($delete_autobid_result) {
            return true;
        } else {
            if ($this->DEBUG_BID) {
                echo 'LINE ' . __LINE__ . ' ' . $sql . " error: " . $this->db->ErrorMsg() . "<br/>\n";
            }
            return false;
        }
    } //end of function insert_into_bid_table

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function update_minimum_current_and_price($minimum, $current, $price)
    {
        $this->auction->minimum_bid = $minimum;
        $this->auction->current_bid = $current;
        $this->auction->price = $price;
        return true;
        $sql = "update " . $this->classifieds_table . " set
			minimum_bid = ?,
			current_bid = ?,
			price = ?
			where id=?";
        $bid_result = $this->db->Execute($sql, array( $minimum, $current, $price, $this->auction_id));
        if ($this->DEBUG_BID) {
            echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
        }
        if ($this->db->get_site_setting('debug_bid')) {
            $this->debug_display($sql, $db, $this->filename, $function_name, "classifieds_table", "update data in auctions table by auction id");
        }
        if ($bid_result) {
            return true;
        } else {
            if ($this->DEBUG_BID) {
                echo 'LINE ' . __LINE__ . ' ' . $sql . " error: " . $this->db->ErrorMsg() . "<br/>\n";
            }
            return false;
        }
    } //end of function update_current_price_minimum

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function set_current_and_price_to_zero()
    {
        $sql = "update " . $this->classifieds_table . "
			set current_bid = 0,
			set price = 0
			where id = ?";
        $set_minimum_result = $this->db->Execute($sql, array($this->auction_id));
        if ($this->DEBUG_BID) {
            echo 'LINE ' . __LINE__ . ' ' . $sql . "<br/>\n";
        }
        if (!$set_minimum_result) {
            if ($this->DEBUG_BID) {
                echo 'LINE ' . __LINE__ . ' ' . $sql . " error: " . $this->db->ErrorMsg() . "<br/>\n";
            }
            return false;
        }

        if ($this->db->get_site_setting('debug_bid')) {
            $this->debug_display($sql, $db, $this->filename, $function_name, "classifieds_table", "update data in auction table by auction id");
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function auction_extension_check($current_time)
    {
        if ($this->db->get_site_setting('auction_extension_check') > 0) {
            //Fetch the time of the current bid
            //Check current_bid time is greater than auction_ends - auction_extension_check and less than
            // auction_ends
            //if true, add auction_extension to auction_ends and update the data
            //else do nothing
            if (
                ($current_time >= ($this->auction->ends - $this->db->get_site_setting('auction_extension_check') * 60))
                && ($current_time <= $this->auction->ends)
            ) {
                $this->auction->ends = $this->auction->ends + $this->db->get_site_setting('auction_extension') * 60;
                //echo $this->auction->ends." is the new end time <br/>";
            } else {
                //echo "Time is not in auction extension check range<br/>";
            }
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
} //end of class Auction_bid
