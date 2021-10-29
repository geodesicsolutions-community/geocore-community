<?php

//user_management_ad_filters.php
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
## ##    17.07.0-12-g183f06e
##
##################################

class User_management_ad_filters extends geoSite
{

    public function display_all_ad_filters()
    {
        $user = geoUser::getUser(geoSession::getInstance()->getUserID());
        if (!$user) {
            return false;
        }

        $this->page_id = 27;
        $msgs = $this->db->get_text(true, $this->page_id);
        $tpl = new geoTemplate('system', 'user_management');
        $tpl->assign('helpLink', $this->display_help_link(376));


        if (isset($_POST['alert_frequency'])) {
            //save alert frequency
            $frequency = $_POST['alert_frequency'];
            $days = (is_numeric($frequency) && $frequency > 0) ? $frequency : 0;
            if ($days) {
                $user->new_listing_alert_gap = $days * 86400;
                $tpl->assign('frequencySaved', true);
            }
        }

        //note: be sure this is assigned AFTER the form value is saved
        $tpl->assign('frequencySetting', $user->new_listing_alert_gap / 86400);

        $sql = "select * from " . geoTables::ad_filter_table . " where user_id = " . geoSession::getInstance()->getUserID() . " order by date_started desc";
        $result = $this->db->Execute($sql);
        if (!$result) {
            $this->site_error($this->db->ErrorMsg());
            return false;
        } elseif ($result->RecordCount() > 0) {
            $tpl->assign('table_description', $msgs[375]);
            $tpl->assign('showFilters', true);

            $tpl->assign('addonColumnHeaders', geoAddon::triggerDisplay('show_listing_alerts_table_headers', null, geoAddon::ARRAY_STRING));

            $filters = array();
            foreach ($result as $show) {
                if (!$show['category_id']) {
                    $category_name = $msgs[2313];
                } else {
                    $category_name = geoCategory::getName($show['category_id'], true);
                }
                $filters[$show['filter_id']]['category_name'] = $category_name;
                if ($show['sub_category_check']) {
                    $filters[$show['filter_id']]['sub_cat_check'] = true;
                }
                $filters[$show['filter_id']]['search_terms'] = geoString::fromDB($show['search_terms']);
                $filters[$show['filter_id']]['date'] = date($this->db->get_site_setting('entry_date_configuration'), $show['date_started']);
                $filters[$show['filter_id']]['link'] = $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=9&amp;c=2&amp;d=" . $show['filter_id'];

                $filters[$show['filter_id']]['addonColumns'] = geoAddon::triggerDisplay('show_listing_alerts_table_body', $show['filter_id'], geoAddon::ARRAY_STRING);
            }
            $tpl->assign('filters', $filters);
            $tpl->assign('addRemoveFilterLink2', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=9&amp;c=3");
        } else {
            //there are no ad filters for this user
            $tpl->assign('table_description', $msgs[377]);
            $tpl->assign('showFilters', false);
        }

        $tpl->assign('addRemoveFilterLink', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=9&amp;c=1");
        $tpl->assign('userManagementHomeLink', $this->db->get_site_setting('classifieds_file_name') . "?a=4");
        $tpl->assign('formTarget', $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=9");

        $this->body = $tpl->fetch('ad_filters/display_all_filters.tpl');
        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function delete_ad_filter($filter_id = 0)
    {
        $user_id = geoSession::getInstance()->getUserID();
        if ($user_id && $filter_id) {
            $sql = "delete from " . geoTables::ad_filter_table . " where filter_id = ? AND user_id = ?";
            $result = $this->db->Execute($sql, array($filter_id, $user_id));
            if (!$result) {
                $this->site_error($this->db->ErrorMsg());
                return false;
            }

            if ($this->db->Affected_Rows() == 0) {
                //requested filter ID to delete doesn't belong to current user (or doesn't exist)
                return false;
            }

            geoAddon::triggerUpdate('delete_listing_alert', $filter_id);

            return true;
        } else {
            //not enough info
            $this->error_message = $this->data_missing_error_message;
            return false;
        }
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function add_new_filter_form()
    {
        $this->page_id = 28;
        $this->get_text();
        if (!geoSession::getInstance()->getUserID()) {
            return false;
        }
        $tpl_vars = array();
        $tpl_vars['formTarget'] = $this->db->get_site_setting('classifieds_file_name') . "?a=4&amp;b=9&amp;c=4";
        $tpl_vars['categoryDDL'] = $this->get_category_dropdown("d[category_id]", 0, 0, 0, $this->messages[500244], 2);
        $tpl_vars['userManagementHomeLink'] = $this->db->get_site_setting('classifieds_file_name') . "?a=4";

        $tpl_vars['addonFilters'] = geoAddon::triggerDisplay('display_add_listing_alert_field', null, geoAddon::ARRAY_STRING);

        $view = geoView::getInstance();
        $view->setBodyTpl('ad_filters/add_filter_form.tpl', '', 'user_management')
            ->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function insert_new_filter($filter_info = 0)
    {
        $userId = (int)$this->userid;
        if (!$userId || !$filter_info) {
            $this->error_message = $this->data_missing_error_message;
            return false;
        }
        $db = DataAccess::getInstance();

        $search_terms_array = explode(",", $filter_info["search_terms"]);

        foreach ($search_terms_array as $value) {
            $sql = "INSERT INTO " . geoTables::ad_filter_table . " (user_id,search_terms,date_started,category_id,sub_category_check) VALUES (?,?,?,?,?)";
            $queryData = array($userId, geoString::toDB($value), geoUtil::time(), intval($filter_info["category_id"]), intval($filter_info["subcategories_also"]));
            $insert_filter_result = $db->Execute($sql, $queryData);
            if (!$insert_filter_result) {
                $this->error_message = $this->internal_error_message;
                return false;
            }

            $filter_id = $db->Insert_ID();

            geoAddon::triggerUpdate('update_add_listing_alert_field', array('filter_id' => $filter_id, 'info' => $filter_info));
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function clear_ad_filters()
    {
        $user_id = geoSession::getInstance()->getUserID();
        if (!$user_id) {
            return false;
        }
        $db = DataAccess::getInstance();
        $sql = "select `filter_id` from " . geoTables::ad_filter_table . " where user_id = ?";
        $result = $db->Execute($sql, array($user_id));
        foreach ($result as $f) {
            if (!$this->delete_ad_filter($f['filter_id'])) {
                return false;
            }
        }
        return true;
    }

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    /**
     * checks a given user's filters and returns data on matched listings timestamped since last sent.
     * @param unknown_type $user_id
     * @return array of stuff
     */
    public function checkUserFilters($user_id)
    {
        $user = geoUser::getUser($user_id);
        if (!$user) {
            //no user? can't continue
        }
        $cron = geoCron::getInstance();
        $db = DataAccess::getInstance();

        if ($cron) {
            $cron->log('checking filters for user ' . $user_id, __LINE__);
        }

        //get all listings since the last time this user was checked (only live listings not belonging to this seller)
        $sql = "SELECT `id` FROM " . geoTables::classifieds_table . " WHERE `date` >= ? AND `live` = '1' AND `seller` <> ?";
        $result = $db->Execute($sql, array($user->new_listing_alert_last_sent, $user->id));

        $listingsMatched = array();
        foreach ($result as $l) {
            if ($cron) {
                $cron->log('checking listing ' . $l['id'], __LINE__);
            }

            //get searchable text for this listing
            $searchString = $this->getSearchString($l['id']);

            if ($cron) {
                $cron->log('relevant listing text is: ' . $searchString, __LINE__);
            }

            //loop through each of this user's filters and look for one that matches this listing
            $sql = "SELECT * FROM " . geoTables::ad_filter_table . " WHERE `user_id` = ? ORDER BY `search_terms` DESC";
            $userFilters = $db->Execute($sql, array($user->id));

            foreach ($userFilters as $filterToCheck) {
                $matchedSomething = false;
                if ($cron) {
                    $cron->log('checking filter ' . $filterToCheck['filter_id'], __LINE__);
                }

                //check search text (using long-form logic here and specifying conditions with no action because this can get a little complicated)
                if (strlen($filterToCheck['search_terms']) > 0) {
                    if ($this->checkFilterString($filterToCheck['search_terms'], $searchString)) {
                        //string matches. keep checking this filter
                        $matchedSomething = true;
                    } else {
                        //this filter does NOT match this listing. proceed to next filter
                        continue;
                    }
                } else {
                    //no search string for this filter. nothing conclusive, so keep going
                    $noSearchString = true;
                }

                //check categories
                if ($filterToCheck['category_id']) {
                    $filterToCheck['category_id'] = (int)$filterToCheck['category_id'];

                    $catSql = "SELECT COUNT(*) FROM " . geoTables::listing_categories . " WHERE `listing`=?  AND `category`=?";
                    if ($filterToCheck['sub_category_check'] != 1) {
                        $catSql .= " AND `is_terminal`='yes'";
                    }
                    $in_cat = (int)$db->GetOne(
                        $catSql,
                        array($l['id'],$filterToCheck['category_id'])
                    );
                    if ($in_cat > 0) {
                        //category matches, keep checking this filter
                        $matchedSomething = true;
                    } else {
                        //this filter does NOT match this listing.  proceed to next filter.
                        continue;
                    }
                } else {
                    //no category set for this filter. nothing conclusive, so keep going
                    $noCategory = true;
                }

                //check addons. if one returns a non-match, CONTINUE to next filter
                $addonCheck = geoAddon::triggerDisplay('check_listing_alert', array('listing_id' => $l['id'], 'filter_id' => $filterToCheck['filter_id']), geoAddon::ARRAY_STRING);
                foreach ($addonCheck as $addonName => $result) {
                    if ($result === 'NO_DATA') {
                        //this addon not in use for this filter
                    } elseif ($result === 'MATCH') {
                        $matchedSomething = true;
                    } elseif ($result === 'NO_MATCH') {
                        continue 2;
                    }
                }

                $catchAll = (bool)($noSearchString && $noCategory); //if both the search string and category are blank, this filter should hit ALL listings

                if ($matchedSomething || $catchAll) {
                    //all filters match. flag for inclusion in email
                    $listingsMatched[$l['id']] = $filterToCheck['filter_id'];
                    break; //don't check any further filters for this listing, since we've found one that works
                }
            }
        }
        if (count($listingsMatched) > 0) {
            //at least one listing matched a filter -- send this person an email
            $this->sendAlertEmail($user, $listingsMatched);
        }
    }

    /**
     * Takes the raw data and creates/sends an email from it
     * @param geoUser $to
     * @param Array $listingsMatched. array in the form of ("id of matched listing" => "id of filter that matched it")
     */
    private function sendAlertEmail($to, $listingsMatched)
    {
        $db = DataAccess::getInstance();
        $this->page_id = 29;
        $this->get_text();

        $data = array();

        foreach ($listingsMatched as $listingId => $filterId) {
            //per client request, add in the URL of the lead image for each listing to template vars
            $listing = geoListing::getListing($listingId);
            $data[$listingId]['lead_image_url'] = $db->GetOne("SELECT `image_url` FROM " . geoTables::images_urls_table . " WHERE `classified_id` = ? ORDER BY `display_order` ASC", array($listingId));
            $data[$listingId]['filter_info'] = $this->getFilterInfo($filterId);
            $data[$listingId]['title'] = geoString::fromDB($listing->title);
            $data[$listingId]['url'] = $listing->getFullUrl();
        }

        $tpl = new geoTemplate('system', 'emails');
        $tpl->assign('data', $data);
        $tpl->assign('messageBody', $this->messages[502067]);
        $tpl->assign('filterLabel', $this->messages[502068]);
        $tpl->assign('categoryLabel', $this->messages[502069]);
        $tpl->assign('titleLabel', $this->messages[502070]);
        $tpl->assign('linkLabel', $this->messages[502071]);
        $message = $tpl->fetch('filter_matched.tpl');
        $subject = $this->messages[1318];
        geoEmail::getInstance()->addQueue($to->email, $subject, $message, 0, 0, 0, 'text/html');

        $cron = geoCron::getInstance();
        if ($cron) {
            $cron->log('sent this email: ' . $message, __LINE__);
        }
    }

    /**
     * Returns data on a filter's parameters in human-readable / email-friendly format
     * @param int $filterId
     * @return string
     */
    public function getFilterInfo($filterId)
    {
        $db = DataAccess::getInstance();
        $this->page_id = 29;
        $this->get_text();

        $info = array();

        $sql = "SELECT * FROM " . geoTables::ad_filter_table . " WHERE `filter_id` = ?";
        $filterData = $db->GetRow($sql, array($filterId));
        if ($filterData) {
            if ($filterData['search_terms']) {
                $info['string'] = geoString::fromDB($filterData['search_terms']);
            }
            if ($filterData['category_id']) {
                $info['category'] = geoCategory::getName($filterData['category_id'], true);
            }
            $info['addons'] = geoAddon::triggerDisplay('show_listing_alert_filter_data', $filterId, geoAddon::ARRAY_STRING);
        }
        return $info;
    }

    /**
     * Gets the filter search string for a listing
     * @param int $listing_id
     * @return string
     */
    private function getSearchString($listing_id)
    {
        //Include the listing ID as first part, for addon filters to see what
        //listing is being filtered.
        $listing = geoListing::getListing($listing_id);
        $searchIn = "$listing_id:: $listing->search_text $listing->title $listing->description $listing->location_city";

        for ($i = 1; $i <= 20; $i++) {
            $field = "optional_field_$i";
            $searchIn .= " " . $listing->$field;
        }


        //let addons alter text to search through when checking filters
        $searchIn = geoAddon::triggerDisplay('filter_check_ad_filter_listing_text', $searchIn, geoAddon::FILTER);

        //remove the listing ID from what is being searched; that was just used for addon benefit
        $searchIn = str_replace("$listing_id:: ", '', $searchIn);

        //decode everything
        $searchIn = geoString::fromDB($searchIn);
        return $searchIn;
    }

    private function checkFilterString($searchFor, $searchIn)
    {
        $searchFor = trim(geoString::fromDB($searchFor));
        $decode = geoString::specialCharsDecode($searchFor);
        if ($decode && $decode != $searchFor) {
            //also look for the "decoded" version for utf-8 characters to match
            $searchFor .= ',' . $decode;
        }

        if ($searchFor) {
            if (stripos($searchFor, ",") !== false) {
                //break into multiple searches on a comma
                $termList = explode(",", $searchFor);
            } else {
                //no commas in search_terms -- only one search to do
                $termList = array($searchFor);
            }

            $foundSearchTerm = false;
            foreach ($termList as $term) {
                if (stripos($searchIn, trim($term)) !== false) {
                    return true;
                }
            }
        }
        //didn't find the string
        return false;
    }
}
