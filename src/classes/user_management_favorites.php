<?php

//user_management_favorites.php


class User_management_favorites extends geoSite
{
    var $debug_favorites = 0;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function display_all_favorites()
    {
        if (!$this->userid) {
            return false;
        }
        $this->page_id = 30;
        $this->get_configuration_data();
        $msgs = $this->db->get_text(true, $this->page_id);
        $this->browsing_configuration = $this->db->get_site_settings(true);
        //use browsing for now
        $fields = $this->fields->getDisplayLocationFields('browsing');

        $tpl = new geoTemplate('system', 'user_management');
        $tpl->assign('fields', $fields);

        $tpl->assign('use_time_left', ((geoMaster::is('auctions') && $fields['auction_time_left']) || (geoMaster::is('classifieds') && $fields['classified_time_left'])));

        $this->sql_query = "select * from " . $this->classifieds_table . "," . $this->favorites_table . " where " . $this->favorites_table . ".classified_id = " . $this->classifieds_table . ".id and " . $this->favorites_table . ".user_id = " . $this->userid . " order by " . $this->classifieds_table . ".ends asc";
        $result = $this->db->Execute($this->sql_query);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() > 0) {
            $tpl->assign('showFavorites', true);
            $tpl->assign('helpLink', $this->display_help_link(354));

            $favs = array();
            for ($i = 0; $show_list = $result->FetchNextObject(); $i++) {
                $this->sql_query = "select * from " . $this->classifieds_table . " where id = " . $show_list->CLASSIFIED_ID . " order by ends asc";
                $classified_result = $this->db->Execute($this->sql_query);
                if (!$classified_result) {
                    $this->site_error($this->db->ErrorMsg());
                    return false;
                } elseif ($classified_result->RecordCount() == 1) {
                    $show_classified = $classified_result->FetchNextObject();
                    $favs[$i]['link']['href'] = $this->configuration_data['classifieds_file_name'] . "?a=2&amp;b=" . $show_list->CLASSIFIED_ID;
                    $favs[$i]['link']['text'] = $show_classified->TITLE;
                    $favs[$i]['listing_id'] = $show_list->CLASSIFIED_ID;

                    if ($show_classified->ITEM_TYPE == 2) {
                        if (strlen($this->messages[500799]) > 0) {
                            $current_bid = $show_classified->CURRENT_BID;
                            $number_of_bids = geoListing::bidCount($show_classified->ID);
                            if (($show_classified->BUY_NOW != 0) && (($show_classified->CURRENT_BID == 0) || ($this->db->get_site_setting('buy_now_reserve') && $show_classified->CURRENT_BID < $show_classified->RESERVE_PRICE))) {
                                $favs[$i]['images'][] = geoTemplate::getUrl('', $this->messages[500799]);
                            }
                        }
                        if (strlen($this->messages[500800]) > 0) {
                            if ($show_classified->RESERVE_PRICE != 0) {
                                $current_bid = $show_classified->CURRENT_BID;
                                if (($show_classified->AUCTION_TYPE != 3 && $current_bid >= $show_classified->RESERVE_PRICE) || ($show_classified->AUCTION_TYPE == 3 && $current_bid <= $show_classified->RESERVE_PRICE)) {
                                    $favs[$i]['images'][] = geoTemplate::getUrl('', $this->messages[500800]);
                                }
                            }
                        }
                        if (strlen($this->messages[501665]) && $show_classified->RESERVE_PRICE > 0) {
                            $current_bid = $show_classified->CURRENT_BID;
                            if (($show_classified->AUCTION_TYPE != 3 && $current_bid < $show_classified->RESERVE_PRICE) || ($show_classified->AUCTION_TYPE == 3 && $current_bid > $show_classified->RESERVE_PRICE)) {
                                $favs[$i]['images'][] = geoTemplate::getUrl('', $this->messages[501665]);
                            }
                        }
                        if (strlen($this->messages[500802]) > 0) {
                            if ($show_classified->RESERVE_PRICE == 0.00 && !$show_classified->BUY_NOW_ONLY) {
                                $favs[$i]['images'][] = geoTemplate::getUrl('', $this->messages[500802]);
                            }
                        }
                    }
                    $description = '';
                    if (!$this->browsing_configuration['display_all_of_description'] || !$this->browsing_configuration['auctions_display_all_of_description']) {
                        $cleaned_desc = trim(strip_tags(preg_replace('/<BR[[:space:]]*\/?[[:space:]]*>/i', " \n", stripslashes(urldecode($show_classified->DESCRIPTION)))));
                        if (strlen($cleaned_desc) > $this->browsing_configuration['length_of_description']) {
                            $small_string = geoString::substr($cleaned_desc, 0, $this->browsing_configuration['length_of_description']);
                            $position = strrpos($small_string, " ");
                            $smaller_string = geoString::substr($small_string, 0, $position);
                            $description = $smaller_string . "...";
                        } else {
                            $description = $cleaned_desc;
                        }
                    } else {
                        //Set to show full description, do not strip tags.
                        $description = stripslashes(urldecode($show_classified->DESCRIPTION));
                    }
                    $favs[$i]['description'] = $description;

                    // display price result
                    $price = '';
                    if ($fields['price']) {
                        if (($show_classified->ITEM_TYPE == 1)) {
                            $price = geoString::displayPrice($show_classified->PRICE, $show_classified->PRECURRENCY, $show_classified->POSTCURRENCY);
                        } elseif ($show_classified->ITEM_TYPE == 2 && ($show_classified->BUY_NOW_ONLY == 1)) {
                            //this is a buy now only auction
                            $price = geoString::displayPrice($show_classified->BUY_NOW, $show_classified->PRECURRENCY, $show_classified->POSTCURRENCY);
                        } elseif ($show_classified->ITEM_TYPE == 2 && ($show_classified->MINIMUM_BID != 0)) {
                            if ($show_classified->MINIMUM_BID < $show_classified->STARTING_BID) {
                                $show_classified->MINIMUM_BID = $show_classified->STARTING_BID;
                            }
                            $price = geoString::displayPrice($show_classified->MINIMUM_BID, $show_classified->PRECURRENCY, $show_classified->POSTCURRENCY);
                        } elseif ($show_classified->ITEM_TYPE == 2 && ($show_classified->STARTING_BID != 0)) {
                            $price = geoString::displayPrice($show_classified->STARTING_BID, $show_classified->PRECURRENCY, $show_classified->POSTCURRENCY);
                        }
                    }
                    $favs[$i]['price'] = $price;
                    $favs[$i]['date_inserted'] = date($this->configuration_data['entry_date_configuration'], $show_list->DATE_INSERTED);
                    $favs[$i]['date'] = date($this->configuration_data['entry_date_configuration'], $show_list->DATE);
                    $favs[$i]['ends'] = date($this->configuration_data['entry_date_configuration'], $show_list->ENDS);

                    $time_left = '';
                    if (($fields['auction_time_left'] && $show_classified->ITEM_TYPE == 2) || ($fields['classified_time_left'] && $show_classified->ITEM_TYPE == 1)) {
                        $weeks = $this->DateDifference('w', geoUtil::time(), $show_classified->ENDS);
                        $remaining_weeks = ($weeks * 604800);

                        // Find days left
                        $days = $this->DateDifference('d', (geoUtil::time() + $remaining_weeks), $show_classified->ENDS);
                        $remaining_days = ($days * 86400);

                        // Find hours left
                        $hours = $this->DateDifference('h', (geoUtil::time() + $remaining_days), $show_classified->ENDS);
                        $remaining_hours = ($hours * 3600);

                        // Find minutes left
                        $minutes = $this->DateDifference('m', (geoUtil::time() + $remaining_hours), $show_classified->ENDS);
                        $remaining_minutes = ($minutes * 60);

                        // Find seconds left
                        $seconds = $this->DateDifference(s, (geoUtil::time() + $remaining_minutes), $show_classified->ENDS);
                        if ($weeks > 0) {
                            $time_left = $weeks . " " . stripslashes(urldecode($msgs[500219])) . ", " . $days . " " . stripslashes(urldecode($msgs[500220]));
                        } elseif ($days > 0) {
                            $time_left = $days . " " . stripslashes(urldecode($msgs[500220])) . ", " . $hours . " " . stripslashes(urldecode($msgs[500221]));
                        } elseif ($hours > 0) {
                            $time_left = $hours . " " . stripslashes(urldecode($msgs[500221])) . ", " . $minutes . " " . stripslashes(urldecode($msgs[500222]));
                        } elseif ($minutes > 0) {
                            $time_left = $minutes . " " . stripslashes(urldecode($msgs[500222])) . ", " . $seconds . " " . stripslashes(urldecode($msgs[500223]));
                        } elseif ($seconds > 0) {
                            $time_left = $seconds . " " . stripslashes(urldecode($msgs[500223]));
                        } else {
                            $time_left = '-';
                        }
                    } elseif ($fields['auction_time_left'] || $fields['classified_time_left']) {
                        $time_left = '-';
                    }
                    $favs[$i]['time_left'] = $time_left;
                    $favs[$i]['removeLink'] = $this->configuration_data['classifieds_file_name'] . "?a=4&amp;b=10&amp;c=1&amp;d=" . $show_list->FAVORITE_ID;
                }
            }
            $tpl->assign('favs', $favs);
        } else {
            //there are no favorites for this user
            $tpl->assign('showFavorites', false);
        }
        $tpl->assign('userManagementHomeLink', $this->configuration_data['classifieds_file_name'] . "?a=4");
        $this->body = $tpl->fetch('favorites/display_all.tpl');
        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function delete_favorite($db, $favorite_id)
    {
        if ($this->userid) {
            if ($favorite_id) {
                $this->sql_query = "delete from " . $this->favorites_table . " where favorite_id = " . $favorite_id;
                $result = $this->db->Execute($this->sql_query);
                if ($this->debug_favorites) {
                    echo $this->sql_query . "<br />\n";
                }
                if (!$result) {
                    if ($this->debug_favorites) {
                        echo $this->sql_query . "<br />\n";
                    }
                    return false;
                }
                return true;
            } else {
                //no communication id
                $this->error_message = $this->data_missing_error_message;
                return false;
            }
        } else {
            return false;
        }
    } //end of function delete_favorite

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function insert_favorite($db, $favorite_id)
    {
        $favorite_id = (int)$favorite_id;
        if (!$this->userid || !$favorite_id) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }

        $sql = "select * from " . geoTables::favorites_table . " where classified_id = ? and user_id = ?";
        $result = $this->db->Execute($sql, array($favorite_id, $this->userid));
        if (!$result) {
            return false;
        }
        if ($result->RecordCount() == 0) {
            $sql = "insert into " . geoTables::favorites_table . " (user_id,classified_id,date_inserted) VALUES (?,?,?)";
            $result = $this->db->Execute($sql, array($this->userid, $favorite_id, geoUtil::time()));
            if (!$result) {
                return false;
            }
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function expire_old_favorites($db)
    {
        if ($this->userid) {
            $this->sql_query = "select * from " . $this->favorites_table . "
				where user_id = " . $this->userid;
            $result = $this->db->Execute($this->sql_query);
            if ($this->debug_favorites) {
                echo $this->sql_query . "<br />\n";
            }
            if (!$result) {
                if ($this->debug_favorites) {
                    echo $this->sql_query . "<br />\n";
                }
                return false;
            }
            if ($result->RecordCount() > 0) {
                while ($show = $result->FetchNextObject()) {
                    $this->sql_query = "select * from " . $this->classifieds_table . "
						where id = " . $show->CLASSIFIED_ID;
                    $classified_result = $this->db->Execute($this->sql_query);
                    if ($this->debug_favorites) {
                        echo $this->sql_query . "<br />\n";
                    }
                    if (!$classified_result) {
                        if ($this->debug_favorites) {
                            echo $this->sql_query . "<br />\n";
                        }
                        return false;
                    } elseif ($classified_result->RecordCount() == 0) {
                        //expire all favorites with this classified id
                        $this->sql_query = "delete from " . $this->favorites_table . "
							where classified_id = " . $show->CLASSIFIED_ID;
                        $delete_result = $this->db->Execute($this->sql_query);
                        if ($this->debug_favorites) {
                            echo $this->sql_query . "<br />\n";
                        }
                        if (!$delete_result) {
                            if ($this->debug_favorites) {
                                echo $this->sql_query . "<br />\n";
                            }
                            return false;
                        }
                    }
                }
                return true;
            }
        } else {
            $this->error_message = urldecode($this->messages[296]);
            return false;
        }
    } //end of function delete_favorite

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
}
