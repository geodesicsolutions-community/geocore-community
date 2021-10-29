<?php

//search_class.php
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
## ##    17.03.0-32-gff2db1f
##
##################################

class Search_classifieds extends geoBrowse
{
    var $category_name;
    var $criteria;
    var $search_criteria;
    var $started;
    /**
     * This is no longer used, at least once I'm used to it.
     * @var string
     * @deprecated
     */
    var $where_clause;
    var $search_sql_query;
    var $search_page_results;
    var $optional_fields;
    var $browse_type;
    var $original_search_term;

    var $debug = 0;
    var $debug_search = 0;
    var $debug_display_results = 0;
    var $testing = 0;
    var $test_name = "";
    var $canadian_zip = 0;
    var $total_returned = 0;
    var $search_text;
    var $search_link;

    //set this to true to enable faster search when searching by "whole word only",
    //at the expense of returning possibly less results
    var $faster_search = false;

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    public function __construct($db = 0, $language_id, $auth, $category_id = 0)
    {
        $this->site_category = (int)$category_id;
        parent::__construct();
    } //end of function Search

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function search_form($db = 0, $search = 0)
    {
        //IMPORTANT: $search is $_REQUEST['b'] -- the inputs from a previous search, but potentially unclean. Sanitize inputs from there before use

        $this->page_id = 44;
        $this->get_text();
        $this->category_name = geoCategory::getName($this->site_category);
        $this->get_ad_configuration();
        //use site-wide settings
        $groupId = 0;
        if ($this->userid) {
            $user = geoUser::getUser($this->userid);
            if ($user) {
                $groupId = (int)$user->group_id;
            }
        }
        //Need to use site-wide settings, category specific fields will be loaded
        //dynamically by call to get cat thingies.
        $fields = $this->fields->getDisplayLocationFields('search_fields');

        if ($this->configuration_data['use_search_form']) {
            $this->display_page();
            return true;
        }

        //always make sure calendar stuff is at top, for when calendar stuff is
        //needed from ajax
        geoCalendar::init();

        $this->CountOptionalFields();

        //check for category-specific settings (getCategoryConfig handles checking for is_ent())
        $this->field_configuration_data = $this->ad_configuration_data;
        $catCfg = geoCategory::getCategoryConfig($this->site_category, true);
        if ($catCfg && $catCfg['what_fields_to_use'] != 'site') {
            $this->field_configuration_data = array_merge($this->field_configuration_data, $catCfg);
        }

        $tpl_vars = array();

        if ((strlen(trim($this->search_sql_query)) > 0)) {
            $tpl_vars['search_sql_query'] = 1;
        }
        //get category dropdown and checkbox
        $this->withAjax = false;

        $this->onload_cat_id = true;
        //if ($this->site_category)$this->onload_cat_id = $this->site_category;

        //***use LeveledFields for category selection***
        $parent_category = (int)$_REQUEST['c'] ? (int)$_REQUEST['c'] : 0;
        $cat_ids = array();

        //always do this, because it sets up for Optional Fields a little later on, and they can be added by ajax if not initially present
        $leveled = geoLeveledField::getInstance();
        geoView::getInstance()->addCssFile(geoTemplate::getUrl('css', 'system/order_items/shared/leveled_fields.css'));

        while ($parent_category > 0 && !in_array($parent_category, $cat_ids)) {
            $cat_ids[] = $parent_category;
            $parent_category = (int)$this->db->GetOne("SELECT `parent_id` FROM " . geoTables::categories_table . " WHERE `category_id`=?", array($parent_category));
        }

        //we now have array of $cat_ids with "top level" at bottom.. so just loop and pop top one off list each time
        $entry = array();

        //let it know what it is
        $entry['leveled_field'] = 'cat';

        $maxLevel = 1;
        $canEditLeveled = $entry['can_edit'] = true;
        $prevParent = 0;
        $i = 1;
        do {
            $selected = ($cat_ids) ? array_pop($cat_ids) : 0;
            $level_i = "leveled_cat_{$i}";

            $page = 1;
            $value_info = geoCategory::getCategoryLeveledValues($prevParent, $listing_types_allowed, $selected, $page, null, $i);
            if (count($value_info['values']) < 1) {
                //no values at this level
                break;
            }
            $maxLevel = $i;
            if ($value_info['maxPages'] > 1) {
                //pagination
                $pagination_url = "AJAX.php?controller=LeveledFields&amp;action=getLevel&amp;leveled_field=cat&amp;cat=1&amp;parent={$prevParent}&amp;selected={$selected}&amp;listing_types_allowed=$listing_types_allowed&amp;page=";
                $value_info['pagination'] = geoPagination::getHTML($value_info['maxPages'], $value_info['page'], $pagination_url, 'leveled_pagination', '', false, false);
            }
            $entry['levels'][$i]['can_edit'] = true;
            $entry['levels'][$i]['value_info'] = $value_info;
            $entry['levels'][$i]['level'] = array('level' => $i);
            $entry['levels'][$i]['page'] = $value_info['page'];
            $prevParent = $selected;
            $i++;
        } while ($prevParent > 0);

        $entry['maxLevel'] = $maxLevel;
        $tpl_vars['cats'] = $entry;

        if (count($tpl_vars['cats']) > 0) {
            //Add CSS for leveled fields
            geoView::getInstance()->addCssFile($externalPre . geoTemplate::getUrl('css', 'system/order_items/shared/leveled_fields.css'));
        }
        //***end category selection code***

        // Special case for joe edwards
        $tpl_vars['je_search_setting'] = $this->db->get_site_setting('je_search_setting');

        $tpl_vars['show_close_24_hours'] = geoMaster::is('auctions');

        if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            $tpl_vars['is_auction'] = 1;
            $tpl_vars['listing_type_allowed'] = '';
        } else {
            $tpl_vars['is_auction'] = 0;
            //if auctions or classified listings are turned off, then insert a hidden field to filter by the admin settings.
            if (!geoMaster::is('classifieds')) {
                $tpl_vars['listing_type_allowed'] = 2;
            } elseif (!geoMaster::is('auctions')) {
                $tpl_vars['listing_type_allowed'] = 1;
            }
        }

        $tpl_vars['addonCriteria'] = geoAddon::triggerDisplay('Search_classifieds_search_form', array ('this' => $this, 'search_fields' => $fields), geoAddon::ARRAY_ARRAY);


        //TODO: finish moving to zipsearch addon files
        $zipSettings = geoAddon::getRegistry('zipsearch');

        if ($zipSettings && $zipSettings->enabled == 1) {
            $tpl_vars['use_zip_distance_calculator'] = 1;
            $zipText = geoAddon::getText('geo_addons', 'zipsearch');
            $tpl_vars['default_distance_text'] = ($zipSettings->units == 'M') ? $zipText['default_distance_mi'] : $zipText['default_distance_km'];

            if ($this->db->get_site_setting('zipsearch_by_location_name') == 1) {
                //beta setting: replace zipcode with city name
                $tpl_vars['zipsearch_by_location'] = true;
                $tpl_vars['zipsearchByLocation_html'] = geoSearchUtils::zipsearchByLocation();
            }

            $tpl_vars['zip_filter'] = ($this->zip_filter) ? $this->zip_filter : false;

            $basic_distances = array(5,10,15,20,25,30,40,50,75,100,200,300,400,500);
            $tpl_vars['basic_distances'] = $basic_distances;
            $tpl_vars['zip_filter_distance'] = $this->zip_filter_distance;
        }

        $maxLocationDepth = 0;
        for ($r = geoRegion::getLowestLevel(); $r > 0; $r--) {
            $field = 'region_level_' . $r;
            if ($fields[$field]) {
                $maxLocationDepth = $r;
                break;
            }
        }
        $locationPrevalue = array();
        $currentRegion = 0;
        if (isset($search['search_location'])) {
            $search_location = (array)$search['search_location'];
            while ($search_location && !$currentRegion) {
                $currentRegion = (int)array_pop($search_location);
            }
        }
        if (!$currentRegion && isset($_COOKIE['region'])) {
            //set by cookie
            $currentRegion = (int)$_COOKIE['region'];
        }
        if ($currentRegion) {
            $locationPrevalue = geoRegion::getRegionWithParents($currentRegion);
        }
        $tpl_vars['region_selector'] = geoRegion::regionSelector('b[search_location]', $locationPrevalue, $maxLocationDepth, false, $this->db->get_site_setting('advSearch_skipEmptyRegions'));



        if (!$this->max_optional_fields) {
            $this->CountOptionalFields();
        }

        //Need to use site-wide settings, fields only enabled by category will be loaded
        //dynamically by call to get cat thingies. (used by leveled and optionals)
        $siteFields = geoFields::getInstance($groupId, 0)->getDisplayLocationFields('search_fields');

        //Leveled fields
        $leveled_ids = $leveled->getLeveledFieldIds();
        if ($leveled_ids) {
            $tpl_vars['leveled_fields'] = array();
            $tpl_vars['leveled_clear_selection_text'] = $this->messages[502065];
            foreach ($leveled_ids as $lev_id) {
                $level_1 = "leveled_{$lev_id}_1";
                if ($fields[$level_1] && $siteFields[$level_1]) {
                    $entry = array();
                    //put together each of the indexes, it's easier to do in PHP
                    //than in smarty
                    $entry['level_1'] = $level_1;

                    //let it know what it is
                    $entry['leveled_field'] = $lev_id;

                    $maxLevelEver = $leveled->getMaxLevel($lev_id, true);
                    $maxLevel = 1;
                    //can edit just applies to listing placement/editing
                    $canEditLeveled = $entry['can_edit'] = true;
                    $prevParent = 0;

                    for ($i = 1; $i <= $maxLevelEver; $i++) {
                        $level_i = "leveled_{$lev_id}_{$i}";
                        if ($fields[$level_i] && $siteFields[$level_i]) {
                            $maxLevel = $i;
                        } else {
                            //we reached limit to enabled ones
                            break;
                        }
                        //(currently) no pre-selected values
                        $selected = 0;
                        //populate the first level

                        //page is always 1 starting out
                        $page = 1;
                        if ($i > 1) {
                            //Nothing past first level is going to be populated yet...
                            $value_info = array(
                                'values' => array(),
                                'maxPages' => 1);
                        } else {
                            $value_info = $leveled->getValues($lev_id, $prevParent, $selected, $page);
                        }
                        if ($value_info['maxPages'] > 1) {
                            //pagination
                            $pagination_url = "AJAX.php?controller=LeveledFields&amp;action=getLevel&amp;leveled_field=$lev_id&amp;parent={$prevParent}&amp;selected=0&amp;page=";
                            $value_info['pagination'] = geoPagination::getHTML($value_info['maxPages'], $value_info['page'], $pagination_url, 'leveled_pagination', '', false, false);
                        }
                        $entry['levels'][$i]['can_edit'] = true;
                        $entry['levels'][$i]['leveled_field'] = $lev_id;
                        $entry['levels'][$i]['value_info'] = $value_info;
                        $prevParent = $selected;

                        $entry['levels'][$i]['level'] = $leveled->getLevel($lev_id, $i, $this->db->getLanguage());
                    }
                    $entry['maxLevel'] = $maxLevel;
                    $tpl_vars['leveled_fields'][$lev_id] = $entry;
                }
            }
        }

        if (geoPC::is_ent() && $this->max_optional_fields > 0) {
            $show_header = true;
            $optionals = array();

            for ($i = 1; $i <= $this->max_optional_fields; $i++) {
                $fieldName = 'optional_field_' . $i;
                if ($fields[$fieldName] && $siteFields[$fieldName]) {
                    $optionals[$i]['field_number'] = $i;
                    if ($i == 1) {
                        $optionals[$i]['label'] = geoString::fromDB($this->messages[1457]);
                    } elseif ($i <= 10) {
                        $optionals[$i]['label'] = geoString::fromDB($this->messages[(1458 + ($i - 1))]);
                    } elseif ($i <= 20) {
                        $optionals[$i]['label'] = geoString::fromDB($this->messages[(1933 + ($i - 11))]);
                    } elseif ($i <= 35) {
                        $optionals[$i]['label'] = geoString::fromDB($this->messages[(2778 + ($i - 21))]);
                    }
                    $field_type = $this->fields->$fieldName->field_type;
                    if ($field_type == 'number' || $field_type == 'cost') {
                        //if numbers only - produce a upper and lower limit
                        $optionals[$i]['type'] = 'numbers';
                    } elseif ($field_type == 'date') {
                        //date type
                        $optionals[$i]['type'] = 'date';
                    } elseif ($field_type != 'dropdown') {
                        //default to text input
                        $optionals[$i]['type'] = 'text';
                    } else {
                        $sql = "select * from " . $this->sell_choices_table . " where type_id = " . intval($this->fields->$fieldName->type_data) . " order by display_order,value";
                        $type_result = $this->db->Execute($sql);

                        if ($type_result && $type_result->RecordCount() > 0) {
                            $optionals[$i]['type'] = 'select';

                            $optionals[$i]['dropdown'][0]['value'] = '0';
                            $optionals[$i]['dropdown'][0]['label'] = '';

                            for ($d = 1; $show_dropdown = $type_result->FetchNextObject(); $d++) {
                                $optionals[$i]['dropdown'][$d]['value'] = $show_dropdown->VALUE;
                                $optionals[$i]['dropdown'][$d]['label'] = $show_dropdown->VALUE;
                                if ($this->classified_variables["optional_field_" . $i] == $show_dropdown->VALUE) {
                                    $optionals[$i]['dropdown'][$d]['selected'] = true;
                                }
                            }
                        } else {
                            //no options available -- fallback to text box
                            $optionals[$i]['type'] = 'text';
                        }
                    }
                    if (strpos($this->fields->$fieldName->type_data, ':use_other') !== false && intval($this->fields->$fieldName->type_data)) {
                        $optionals[$i]['other_box'] = true;
                    }
                }
            }
            if (count($optionals) > 0) {
                $tpl_vars['show_optionals'] = true;
                $tpl_vars['optionals'] =  $optionals;
            }
        }

        $tooltip[1] = $this->display_help_link(585);
        $tooltip[2] = $this->display_help_link(574);
        $tooltip[3] = $this->display_help_link(1951);
        $tooltip[4] = $this->display_help_link(586);
        $tpl_vars['tooltip'] = $tooltip;
        $tpl_vars['category_dropdown'] = $category_dropdown;
        $tpl_vars['queryFields'] = $fields;


        //body for errors because any error messages from a failed Search()
        //will already be held in $this->body.
        //(probably need to rework that, but quickhack to make it work for now)
        $tpl_vars['errors'] = $this->body;
        $this->body = '';

        $share_fees = geoAddon::getUtil('share_fees');
        $tpl_vars['feeshare_active'] = $share_fees->active;
        if (($share_fees) && ($share_fees->active)) {
            //display storefront to search choices here
            $users_can_attach_to = $share_fees->attachableUsers();
            $share_fee_text =& geoAddon::getText('geo_addons', 'share_fees');
            $tpl_vars['feeshare_userattachmentchoices'] = $users_can_attach_to;
            $tpl_vars['feeshare_attachtouserlabel'] = $share_fee_text['share_fees_search_choices'];
        }

        geoView::getInstance()->setBodyTpl('details_form.tpl', '', 'search_class')
            ->setBodyVar($tpl_vars);

        $this->display_page();
        return true;
    }

    public function generateQuery($search_criteria)
    {
        $query = $this->db->getTableSelect(DataAccess::SELECT_SEARCH);
        $classTable = geoTables::classifieds_table;
        if (!$search_criteria) {
            //nothing to search by...
            return;
        }

        //search only works with live listings
        $query->where("$classTable.`live`=1", 'live');

        $this->original_search_term = $search_criteria["search_text"];
        $this->search_criteria = $search_criteria;
        $this->get_ad_configuration();
        $this->site_category = (int)$_REQUEST['c'];
        if (strlen(trim($this->site_category)) == 0) {
            $this->site_category = 0;
        }
        if ($this->debug_search) {
            echo "this->site_category set to " . $this->site_category . "<br />\n";
        }

        if ($this->debug_search) {
            echo $this->search_criteria["search_text"] . " is search_criteria[search_text]<br />\n";
            echo $this->search_text . " is search_text<br />\n";
        }


        $this->get_category_questions(0, $this->site_category);

        //check for category-specific settings (getCategoryConfig handles checking for is_ent())
        $this->field_configuration_data = $this->ad_configuration_data;
        $catCfg = geoCategory::getCategoryConfig($this->site_category, true);
        if ($catCfg && $catCfg['what_fields_to_use'] != 'site') {
            $this->field_configuration_data = array_merge($this->field_configuration_data, $catCfg);
        }

        // Search ID only and exit if only searching for ad id
        if ($this->search_criteria["whole_word"] == 2) {
            if ($this->debug_search) {
                echo "searching as if search term were an ad id<br />\n";
                echo "Searching for id " . $this->search_criteria["search_text"] . "<br />";
            }
            if ((strlen(trim($this->search_criteria["search_text"])) > 0) && (is_numeric($this->search_criteria["search_text"]))) {
                $query->where("$classTable.`id` = " . (int)$this->search_criteria['search_text']);
            } else {
                //invalid ID specified, so do a query for something that should not exist
                $query->where("$classTable.`id` = 0");
            }

            // Build the query to run
            $result = $this->db->Execute($query);
            if (!$result) {
                if ($this->debug_search) {
                    echo $this->db->ErrorMsg() . " is the error<br />\n";
                    echo $sql . '<br />';
                }
                return false;
            } else {
                // Send user to correct ad
                if ($result->RecordCount() == 1) {
                    if ($this->testing == 1) {
                        return $result;
                    }

                    // Send user to correct ad
                    $returned_result = $result->FetchNextObject();
                    if ($this->debug_search) {
                        echo "redirecting to id " . $returned_result->ID . " in the id search<br />\n";
                    }
                    header("Location: " . $this->configuration_data['classifieds_url'] . "?a=2&b=" . $returned_result->ID);
                    exit;
                } else {
                    // No results returned
                    return false;
                }
                return true;
            }
        }

        // Generate whole or partial word match
        if (strlen(trim($this->search_criteria["search_text"])) > 0) {
            if ($this->debug_search) {
                echo "<br />TOP OF SEARCH_TEXT > 0<br />\n";
                echo " search_text contained text<br />\n";
                echo "about to search for this search_text - " . $this->search_criteria["search_text"] . "<br />\n";
            }
            // Notes:
            // 0 is partial
            // 1 is whole

            //set up title/description restrictions to play nicely with both search forms
            if ($this->search_criteria['search_by_field'] == 'title_only') {
                $this->search_criteria['search_titles'] = 1;
                $this->search_criteria['search_descriptions'] = 0;
            } elseif ($this->search_criteria['search_by_field'] == 'description_only') {
                $this->search_criteria['search_titles'] = 0;
                $this->search_criteria['search_descriptions'] = 1;
            }
            //figure out which columns to look for the search terms in
            $cols = array();
            if ($this->search_criteria['search_titles'] || !$this->search_criteria['search_descriptions']) {
                $cols[] = 'title';
            }
            if ($this->search_criteria['search_descriptions'] || !$this->search_criteria['search_titles']) {
                $cols[] = 'description';
                //if searching description, also add search for search_text
                $cols[] = 'search_text';
                //also add optionals
                if (!$this->max_optional_fields) {
                    $this->CountOptionalFields();
                }
            }

            //add optionals
            for ($i = 1; $i <= (int)$this->max_optional_fields; $i++) {
                $cols[] = "optional_field_$i";
            }

            if (stripos($this->search_criteria['search_by_field'], 'optional_field_') === 0) {
                //searching on ONLY a certain optional field
                //search_by_field is user input, so make VERY certain it is clean!
                $fieldNum = intval(substr($this->search_criteria['search_by_field'], 15));
                if ($fieldNum >= 1 && $fieldNum <= 20) {
                    //reset cols array to just this column
                    $cols = array('optional_field_' . $fieldNum);
                } else {
                    //not a valid optional field number -- search on everything
                }
            }
            $this->processString($this->search_criteria['search_text'], $cols, 'main_search_terms');

            if ($this->debug_search) {
                echo $query . " <br />is the sql clause after adding title,description and search_text clause<br />\n";
            }
        }//end of if search_text

        if ($this->search_criteria["classified_auction_search"] && geoMaster::is('classifieds') && geoMaster::is('auctions')) {
            //default is to search both....if classified_auction_search is empty do not limit the search to a type

            if ($this->search_criteria["classified_auction_search"] == 1) {
                //search only classifieds
                $query->where("$classTable.`item_type`=1", 'item_type');
            } elseif ($this->search_criteria["classified_auction_search"] == 2 || $this->search_criteria["classified_auction_search"] == 3) {
                //search only auctions, or only buy now auctions
                $query->where("$classTable.`item_type` = 2", 'item_type');
            }

            if ($this->search_criteria["classified_auction_search"] == 3) {
                //search buy now auctions, make sure buy now is set, and that there are no bids
                $query->where("$classTable.`buy_now`>0", 'buy_now')
                    ->orWhere("$classTable.`current_bid` < $classTable.`starting_bid`", 'buy_now_no_bids')
                    ->orWhere("$classTable.`current_bid` = 0", 'buy_now_no_bids');
            }
        }
        if ($this->debug_search) {
            echo $query . "<br />is query after search_text > 0<br />\n";
            echo "<br />ABOUT TO DO CATEGORY SPECIFIC<br />\n";
            echo $this->site_category . " is site_category before category specific<br />";
            if (is_array($search_criteria["question_value"])) {
                reset($search_criteria["question_value"]);
                foreach ($search_criteria["question_value"] as $key => $value) {
                    echo $key . " is key to " . $value . "<br />";
                }
            } else {
                echo $search_criteria["question_value"] . ' is $search_criteria[\'question_value\']<br />';
            }
        }

        // Search specific category-specific questions based on input values
        if (($this->site_category > 0 && isset($search_criteria["question_value"])) && !in_array($this->search_criteria['search_by_field'], array('description_only','title_only'))) {
            //there is category questions, and not searching by title or description only
            if ($this->debug_search) {
                echo "going through question_value<br />\n";
            }

            $category_question_list = array();

            $questionTable = geoTables::classified_extra_table;
            foreach ($search_criteria["question_value"] as $key => $value) {
                if (!is_array($value)) {
                    if (!strlen(trim($value))) {
                        //nothing entered in blank text box
                        continue;
                    }
                    $value = array ('other' => $value);
                }

                //this is lower/higher numeric search, OR a drop-down selection search thingy
                $value_used = false;
                $subQuery = new geoTableSelect($questionTable);

                $subQuery->where("$questionTable.`classified_id` = $classTable.`id`")
                    ->where("$questionTable.`question_id`=" . (int)$key);

                foreach ($value as $val_key => $val) {
                    if ($val_key === 'low_date' || $val_key === 'high_date') {
                        //low or high date, clean/convert it to YYYYMMDD format
                        $val = geoCalendar::fromInput($val);
                    }
                    if (!strlen(trim($val))) {
                        //value not set here
                        continue;
                    }
                    $value_used = true;
                    if ($val_key === 'lower') {
                        $lower = geoNumber::deformat($val);
                        $subQuery->where($this->db->quoteInto("$questionTable.`value` >= ?", $lower, DataAccess::TYPE_FLOAT));
                        unset($lower);
                    } elseif ($val_key === 'higher') {
                        $higher = geoNumber::deformat($val);
                        $subQuery->where($this->db->quoteInto("$questionTable.`value` <= ?", $higher, DataAccess::TYPE_FLOAT));
                        unset($higher);
                    } elseif ($val_key === 'low_date') {
                        //value already cleaned above
                        $subQuery->where("$questionTable.`value` >= '$val'");
                    } elseif ($val_key === 'high_date') {
                        //value already cleaned above
                        $subQuery->where("$questionTable.`value` <= '$val'")
                            ->where("$questionTable.`value` != ''");
                    } elseif ($val_key === 'other') {
                        //force it to use "or" if there are already orWhere checks
                        //on the subquery, as it means the other isn't the only option
                        //selected and must be forced to be a or check
                        $force_or = (bool)$subQuery->getOrWhere('checks');
                        $this->processString($val, array('value'), 'checks', $subQuery, null, $force_or);
                    } else {
                        //do a straight "=" check on the URL encoded value, this is likely
                        //a pre-valued selection
                        $subQuery->orWhere($this->db->quoteInto("$questionTable.`value` = ?", urlencode($val)), 'checks');
                    }
                }
                if ($value_used) {
                    //Use EXISTS, it will be true when the sub-query returns results
                    if ($this->debug_search) {
                        echo "adding sub-query for question $key: <pre>$subQuery</pre><br /><br />";
                    }
                    $query->where("EXISTS ($subQuery)", 'extra_question_#' . $key);
                }
                unset($subQuery);
            }

            if ($this->debug_search) {
                echo $query . " is the where_clause after field_items build where statement<br />\n";
            }
        }

        if ($this->debug_search) {
            echo $query . ' is the where_clause at the end of category specific<br />';
            echo "<br />ABOUT TO SEARCH SITE WIDE OPTIONAL FIELDS<br />\n";
        }

        // Find all optional fields
        if (!$this->max_optional_fields && !in_array($this->search_criteria['search_by_field'], array('description_only','title_only'))) {
            $this->CountOptionalFields();
        }

        for ($i = 1; $i <= (int)$this->max_optional_fields; $i++) {
            //note:  if max_optional_fields is 0, it never makes it into this for loop
            $value = $this->search_criteria["optional_field_" . $i];
            if (!is_array($value)) {
                if (!strlen(trim($value))) {
                    continue;
                }
                $value = array ('other' => $value);
            }
            foreach ($value as $v_type => $val) {
                if ($val && ($v_type === 'low_date' || $v_type === 'high_date')) {
                    $val = geoCalendar::fromInput($val);
                }
                if (!strlen(trim($val))) {
                    //nothing entered to search for this one
                    continue;
                }
                if ($v_type === 'lower') {
                    //by lower limit, use and
                    $query->where($this->db->quoteInto("$classTable.`optional_field_{$i}` >= ?", geoNumber::deformat($val), DataAccess::TYPE_FLOAT));
                } elseif ($v_type === 'higher') {
                    //by upper limit, use and
                    $query->where($this->db->quoteInto("$classTable.`optional_field_{$i}` <= ?", geoNumber::deformat($val), DataAccess::TYPE_FLOAT));
                } elseif ($v_type === 'low_date') {
                    //by low date limit, value will already be "cleaned"
                    $query->where("$classTable.`optional_field_{$i}` >= '$val'");
                } elseif ($v_type === 'high_date') {
                    //by high date limit, value will already be "cleaned"
                    //make sure date is not blank if high limit is entered...
                    $query->where("$classTable.`optional_field_{$i}` <= '$val'")
                        ->where("$classTable.`optional_field_{$i}` != ''");
                } elseif ($v_type === 'other') {
                    //just do text search, either normal input or multiple selected dropdown values
                    $force_or = (bool)$query->getOrWhere('optional_field_' . $i);
                    $this->processString($val, array('optional_field_' . $i), 'optional_field_' . $i, null, null, $force_or);
                } else {
                    //multiple selected dropdown values, do a straight = check, and
                    //use orWhere as we want to match "any" of the checked options
                    $query->orWhere($this->db->quoteInto("$classTable.`optional_field_{$i}` = ?", urlencode($val)), 'optional_field_' . $i);
                }
            }
            if ($this->debug_search) {
                echo "sql after optional $i: <pre>$query</pre><br /><br />";
            }
        }

        if ($this->debug_search) {
            echo $query . "<br /> is the where_clause after site wide optional fields<br />";
        }

        if ($this->search_criteria['leveled']) {
            //do leveled fields
            $lField = geoLeveledField::getInstance();
            $levT = geoTables::listing_leveled_fields;

            //use the same sub-query instead of creating bunch of them...
            $subQuery = new geoTableSelect($levT);
            $subQuery->where("$levT.`listing` = $classTable.`id`");
            foreach ($this->search_criteria['leveled'] as $lev_id => $levels) {
                $levels = (array)$levels;
                $lev_id = (int)$lev_id;
                if (!$lev_id) {
                    //invalid input (or this is "cat" and we're not interested in categories here)
                    continue;
                }
                //only interested in the furthest down one...
                $leveled_selected = 0;
                while (!$leveled_selected && $levels) {
                    $leveled_selected = array_pop($levels);
                }
                if (!$leveled_selected) {
                    //continue on, no actual selections made here...
                    continue;
                }

                //validate the value
                $valueInfo = $lField->getValueInfo($leveled_selected);
                if (!$valueInfo || $valueInfo['enabled'] != 'yes' || $valueInfo['leveled_field'] != $lev_id) {
                    //value not valid
                    continue;
                }
                unset($valueInfo);
                //leveled_selected has been validated, so add a check
                $subQuery->where("$levT.`leveled_field`=$lev_id", 'leveled_field')
                    ->where("$levT.`field_value`=$leveled_selected", 'field_value');
                $query->where("EXISTS ($subQuery)", 'leveled_' . $lev_id);
            }

            //we're done with this subquery now
            unset($subQuery);
        }

        //ending today
        if ($this->search_criteria["ending_today"]) {
            $timeToLookAhead = (60 * 60 * 24) + geoUtil::time(); // only option is 24 hours for now
            $query->where("$classTable.`ends` < $timeToLookAhead", 'ending_today');
        } elseif ($this->search_criteria["end_date"]) {
            $timeToLookAhead = (60 * 60 * 24 * (int)$this->search_criteria["end_date"]) + geoUtil::time();
            $query->where("$classTable.`ends` < $timeToLookAhead", 'ending_today');
        }

        if ($this->search_criteria['start_date']) {
            $startedAfter = geoUtil::time() - (int)$this->search_criteria['start_date'] * 24 * 60 * 60;
            $query->where("$classTable.`date` >= " . $startedAfter, 'start_date');
        }

        // Do price range checking
        if ($this->search_criteria["by_price_lower"]) {
            $lowPrice = geoNumber::deformat($this->search_criteria['by_price_lower']);
            if (geoMaster::is('classifieds')) {
                $query->orWhere("$classTable.`item_type`=1 AND $classTable.`price` >= $lowPrice", 'price_low_check');
            }
            if (geoMaster::is('auctions')) {
                //min bid
                $query->orWhere("$classTable.`item_type`=2 AND $classTable.`minimum_bid` >= $lowPrice", 'price_low_check');
                //buy now only price
                $query->orWhere("$classTable.`buy_now_only`=1 AND $classTable.`buy_now` >= $lowPrice", 'price_low_check');
            }
        }
        if ($this->search_criteria["by_price_higher"]) {
            $highPrice = geoNumber::deformat($this->search_criteria['by_price_higher']);
            if (geoMaster::is('classifieds')) {
                $query->orWhere("$classTable.`item_type`=1 AND $classTable.`price` <= $highPrice", 'price_high_check');
            }
            if (geoMaster::is('auctions')) {
                //min bid - note, since checking that value is "less than" entered amount, need
                //to make sure that it doesn't match against "buy now only" auctions as those will
                //always have minimum bid set to 0.
                $query->orWhere("$classTable.`item_type`=2 AND $classTable.`buy_now_only`=0 AND $classTable.`minimum_bid` <= $highPrice", 'price_high_check');
                //buy now only price
                $query->orWhere("$classTable.`buy_now_only`=1 AND $classTable.`buy_now` <= $highPrice", 'price_high_check');
            }
        }

        $share_fees = geoAddon::getUtil('share_fees');
        if (($share_fees) && ($share_fees->active)) {
            //see if a specific storefront was passed to the search by.
            if ($this->search_criteria["attached_user_search_id"]) {
                //check that storefront passed can be attached to
                if ($share_fees->checkAttachableUser($this->search_criteria["attached_user_search_id"])) {
                    //get the storefront categories for this storefront
                    $storefront_table = geoAddon::getUtil('storefront')->tables();
                    $attached_user_search_where_clause = $share_fees->getStoreCategoryInStatement($this->search_criteria["attached_user_search_id"], $storefront_table->categories);
                    $query->where("$classTable.`storefront_category` $attached_user_search_where_clause ", 'attached_store_search');
                } else {
                    //user cannot be attached to
                }
            }
        }

        //*****Zipcode Searching*****
        //this default will be over-ridden by zipsearch addon (or any addon that wants to)
        if ($this->search_criteria["by_zip_code"]) {
            $value = geoString::toDB($this->search_criteria["by_zip_code"]);
            $value = str_replace(array('%','_'), array('\%','\_'), $value);
            $query->where("$classTable.`location_zip` LIKE '%$value%'", 'location_zip');
        }

        $search_locations = (array)$this->search_criteria['search_location'];
        $tbl = geoTables::listing_regions;
        if ($search_locations) {
            //only really interested in searching by the lowest-level region given
            ksort($search_locations);
            do {
                //find the lowest-level valid region in the array
                $search_location = intval(array_pop($search_locations));
            } while (!$search_location && $search_locations);
            if ($search_location) {
                $query->join($tbl, "$tbl.`listing`=$classTable.`id`");
                $query->where("$tbl.`region` = $search_location", 'search_location');
            }
        }

        if ($this->search_criteria["by_city"] && $this->search_criteria["by_city"] != "none") {
            $value = geoString::toDB($this->search_criteria["by_city"]);
            $value = str_replace(array('%','_'), array('\%','\_'), $value);

            $query->where("$classTable.`location_city` LIKE '%$value%'", 'location_city');
        }

        // Do check for business type
        if ($this->search_criteria["by_business_type"]) {
            $value = ($this->search_criteria["by_business_type"] == 1) ? 1 : 2;

            $query->where("$classTable.`business_type` = $value", 'business_type');
        }

        // Put in category selecting
        if ($this->site_category != 0) {
            //set whether to include subcategories by the search criteria
            $this->configuration_data['display_sub_category_ads'] = $this->search_criteria["subcategories_also"];
            $this->whereCategory($query, $this->site_category);
        }
    }

    /**
     * Adds to the geoTableSelect query by breaking up $search_text into parts
     * to allow it to search for "any" of the keywords...  Also allows for
     * seperating parts by " and " to force an "all keyword search" on the parts
     * or use " or " to force "or search".
     *
     * @param string $search_text the un-encoded search text
     * @param array $cols Array of columns
     * @param string $named The name to use for the orwhere query.  Ignored if it
     *   ends up using "where" query depending on search text
     * @param geoTableSelect $query
     * @param int $whole_word Set to 1 for search by whole word, 2 to do partial
     *   match, or null to use value specified in search query.
     * @param bool $force_or If true, will force use named "orWhere", will skip "and"
     *   checks, and if there are spaces, will still do "or" with different terms.
     *   Param {@since Version 7.2.0}
     */
    public function processString($search_text, $cols, $named, $query = null, $whole_word = null, $force_or = false)
    {
        if ($query === null) {
            //assume we are using main query...
            $query = $this->db->getTableSelect(DataAccess::SELECT_SEARCH);
        }

        if ($whole_word === null) {
            $whole_word = $this->search_criteria['whole_word'];
        }

        $classTable = $query->getTable();

        $search_text = urlencode(trim($search_text));
        $search_id = false;
        if (!$force_or && strpos($search_text, '+and+') !== false) {
            $where = 'where';
            $named = null;
            $all_search_terms = explode("+and+", $search_text);
        } elseif (strpos($search_text, '+or+') !== false) {
            $where = 'orWhere';
            $all_search_terms = explode("+or+", $search_text);
        } elseif (strpos($search_text, '%2C') !== false) {
            $where = 'orWhere';
            $all_search_terms = explode("%2C", $search_text);
        } elseif (strpos($search_text, '+') !== false) {
            if ($force_or) {
                //still use "or" check
                $where = 'orWhere';
            } else {
                //do normal where check
                $where = 'where';
                $named = null;
            }
            $all_search_terms = explode('+', $search_text);
        } else {
            $where = 'orWhere';
            $all_search_terms = array($search_text);
            if (intval($search_text) == $search_text && $search_text > 0) {
                //special case, see if ID matches...
                $search_id = (int)$search_text;
            }
        }

        $boundary = '\\\+|%7E|%60|%21|%40|%23|%24|%25|%5E|%26|%2A|%28|%29|_|-|%2B|%2C|%3D|%5B|%5D|%7B|%7D|%3C|%3E|%2F|%7C';
        if (false) {
            /**
             * To modify "boundries" the easy way, change the false above to
             * true so it enters into this if block.  Then manipulate the boundry
             * string below for the "pre encoded" word boundries.  Then do
                * a search, and it will echo the new value to use for $boundary
             * right above this if block.  Don't forget to change the above
             * back to "false" when done.
             */

            //word boundries "pre-encoded"
            $boundary = str_split(' ~`!@#$%^&*()_-+,=[]{}<>/|');
            foreach ($boundary as $key => $val) {
                if ($val === ' ') {
                    //special case, + needs to be escaped to work in regex in mysql
                    $boundary[$key] = '\\\+';
                } else {
                    $boundary[$key] = geoString::toDB($val);
                }
            }
            //now put back together split up by |
            $boundary = implode('|', $boundary);

            echo "boundries: <pre>" . print_r($boundary, 1) . "</pre>";
        }
        foreach ($all_search_terms as $key => $value) {
            //remove any spaces
            $value = urlencode(trim(geoString::fromDB($value)));

            //escape mysql's search characters
            if ($whole_word == 1) {
                //to be used for REGEX
                $value = preg_quote($value);
            } else {
                //to be used for LIKE
                $value = str_replace(array('%','_'), array('\%','\_'), $value);
            }
            $miniWhere = array();//since we are potentially "or'ing" these together, can't just use orWhere
            $useId = false;
            foreach ($cols as $column_name) {
                if ($search_id && ($column_name == 'title' || $column_name == 'description')) {
                    //yes, it "should" use search as a ID number
                    $useId = true;
                }
                if ($whole_word == 1) {
                    //whole word version, much more complicated...
                    $miniWhere[] = "($classTable.`$column_name` REGEXP '(^|$boundary)$value($|$boundary)')";
                } else {
                    //not "whole word", this one a little easier to do
                    $miniWhere[] = "($classTable.`$column_name` LIKE '%$value%')";
                }
            }
            if ($search_id && $useId) {
                //add check for the ID
                $miniWhere[] = "($classTable.`id` = $search_id)";
            }

            //put the miniWhere together for a normal-sized where...
            //$where is set to "where" or "orWhere" further up, to be used as which
            //where function to use (either join together using "or" or "and")
            $query->$where(implode(' OR ', $miniWhere), $named);

            unset($miniWhere);//done with it on this round
        }
    }

    public function Search($search_criteria = 0, $change = 0, $browse_type = 0)
    {
        if ($this->debug_search) {
            echo "<br />TOP OF SEARCH<br />\n";
            echo $search_criteria . " is search_criteria<br />\n";
            echo $search_criteria["whole_word"] . " is search_criteria[whole_word]<br />\n";
            echo $search_criteria["search_text"] . " is the search text<br />\n";
            echo count($search_criteria["question_value"]) . " is the count of search_criteria[question_value]<br />\n";
        }
        $this->page_id = 44;
        $this->get_text();

        $view = geoView::getInstance();
        $tpl_vars = array();

        $this->search_text = $search_criteria["search_text"];
        //remove extra white space
        $this->search_text = preg_replace('/\s+/', ' ', $this->search_text);
        //escape exclamation
        $this->search_text = preg_replace('/!/', '\%21', $this->search_text);

        // Check if user changed category
        if ($change == 1) {
            if ($this->debug_search) {
                echo "category changed ...displaying form<br />\n";
            }
            return false;
        }
        if ($this->debug_search) {
            highlight_string(print_r($search_criteria, 1));
        }
        //get the original, so we modify the main search tableSelect since there
        //is only going to be one search results on the page
        $query = $this->db->getTableSelect(DataAccess::SELECT_SEARCH);
        $classTable = geoTables::classifieds_table;
        if ($search_criteria) {
            if ($this->debug_search) {
                echo "inside of if (search_criteria)<br />\n";
                echo $this->search_criteria["page"] . " is the b[page] value<br />\n";
            }

            $this->generateQuery($search_criteria);

            //allow addons to add to the where clause
            geoAddon::triggerUpdate('Search_classifieds_generate_query', array('this' => $this));

            $browseType = array();
            if ($browse_type) {
                $browseType["param"] = (int)$browse_type;
            }
            if ($browseType["param"] == 0) {
                if ($this->db->get_site_setting('default_display_order_while_browsing') != -1) {
                    //this setting used if site has been updated from pre-3.1, but admin hasn't set the class/auc-specific settings yet
                    $browseType["legacy"] = $this->db->get_site_setting('default_display_order_while_browsing');
                } else {
                    $browseType["classified"] = $this->db->get_site_setting('default_classified_order_while_browsing');
                    $browseType["auction"] = $this->db->get_site_setting('default_auction_order_while_browsing');
                }
            }

            $orderBy = array();
            foreach ($browseType as $key => $value) {
                $orderBy[$key] = $this->getOrderByString($value);
            }

            $this->browse_type = (isset($browseType["param"])) ? $browseType["param"] : 0;

            if (isset($orderBy["param"]) && strlen(trim($orderBy["param"])) > 0) {
                //if function is called with a parameter
                $query->order($orderBy["param"]);
            } elseif (isset($orderBy["legacy"]) && $orderBy["legacy"]) {
                //if site admin has not yet run admin > browse form, we are in legacy display mode
                $query->order($orderBy["legacy"]);
            } else {
                //follow site defaults
                if (!geoMaster::is('classifieds')) {
                    $query->order($orderBy['auction']);
                } else {
                    //use classified if classified only or classauctions
                    $query->order($orderBy['classified']);
                }
            }
            $page = $this->page_result = (isset($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1;
            $adsToShow = (int)$this->configuration_data['number_of_ads_to_display'];
            $start = ($page - 1) * $adsToShow;

            $query->limit($start, $adsToShow);

            $tpl_vars['total_search_results'] = $show_total = $this->total_returned = (int)$this->db->GetOne('' . $query->getCountQuery());

            if ($show_total) {
                $result = $this->db->Execute('' . $query);
            } else {
                $result = false;
            }

            if (!$result) {
                $this->body .= "<table><tr class=\"search_page_instructions\">\n\t<td colspan=\"4\">{$this->messages[592]}</td>\n</tr></table>";
                return false;
            }

            //total number of pages to make available
            $numPages = max(1, ceil($this->total_returned / $adsToShow));

            $this->BuildResults($result);
            if ($adsToShow < $this->total_returned) {
                if ($browseType['param']) {
                    $c = $browseType['param'];
                } elseif ($browseType['category']) {
                    $c = $browseType['category'];
                } else {
                    $c = 0;
                }
                $c = ($c) ? "&amp;order=$c" : '';
                $url = $this->search_link . "$c&amp;page=";
                $css = "browsing_result_page_links";
                $tpl_vars['pagination'] = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
            }

            //figure out whether to use gallery or not
            $browse_view = $this->getCurrentBrowseView();
            //page 44
            $sort_dropdown_txt = array (
                0 => $this->messages[501702], //Relevance (AKA no sorting applied)
                1 => $this->messages[501696], //price - cheapest first
                2 => $this->messages[501697], //price - expensive first
                3 => $this->messages[501706], //date - oldest first
                4 => $this->messages[501695], //date - newest first
                5 => $this->messages[501710], //title - a first
                6 => $this->messages[501714], //title - z first
                7 => $this->messages[501718], //location_city - a first
                8 => $this->messages[501722], //location_city - z first
                13 => $this->messages[501726], //zip - 0 first
                14 => $this->messages[501730], //zip - 9 first
                15 => $this->messages[501734], //optional field 1
                16 => $this->messages[501738], //optional field 1 reverse
                17 => $this->messages[501742], //optional field 2
                18 => $this->messages[501746], //optional field 2 reversed
                19 => $this->messages[501750], //optional field 3
                20 => $this->messages[501754], //optional field 3 reversed
                21 => $this->messages[501758], //optional field 4
                22 => $this->messages[501762], //optional field 4 reverse
                23 => $this->messages[501766], //optional field 5
                24 => $this->messages[501770], //optional field 5 reversed
                25 => $this->messages[501774], //optional field 6
                26 => $this->messages[501778], //optional field 6 reversed
                27 => $this->messages[501782], //optional field 7
                28 => $this->messages[501786], //optional field 7 reverse
                29 => $this->messages[501790], //optional field 8
                30 => $this->messages[501794], //optional field 8 reversed
                31 => $this->messages[501798], //optional field 9
                32 => $this->messages[501802], //optional field 9 reversed
                33 => $this->messages[501806], //optional field 10
                34 => $this->messages[501810], //optional field 10 reverse
                33 => $this->messages[501805], //optional field 10
                34 => $this->messages[501810], //optional field 10 reverse
                45 => $this->messages[501814], //optional field 11
                46 => $this->messages[501818], //optional field 11 reverse
                47 => $this->messages[501822], //optional field 12
                48 => $this->messages[501826], //optional field 12 reversed
                49 => $this->messages[501830], //optional field 13
                50 => $this->messages[501834], //optional field 13 reversed
                51 => $this->messages[501838], //optional field 14
                52 => $this->messages[501842], //optional field 14 reverse
                53 => $this->messages[501846], //optional field 15
                54 => $this->messages[501850], //optional field 15 reversed
                55 => $this->messages[501854], //optional field 16
                56 => $this->messages[501858], //optional field 16 reversed
                57 => $this->messages[501862], //optional field 17
                58 => $this->messages[501866], //optional field 17 reverse
                59 => $this->messages[501870], //optional field 18
                60 => $this->messages[501874], //optional field 18 reversed
                61 => $this->messages[501878], //optional field 19
                62 => $this->messages[501882], //optional field 19 reversed
                63 => $this->messages[501886], //optional field 20
                64 => $this->messages[501890], //optional field 20 reverse
                43 => $this->messages[501894], //business type
                44 => $this->messages[501898], //business type reversed
                69 => $this->messages[501902], //ends (soon)
                70 => $this->messages[501906], //ends (most time left)
                71 => $this->messages[501910], //listings without images first
                72 => $this->messages[501914], //listings with images first
            );
            $sort_dropdown_txt = $this->getSortOptions($this->fields->getDisplayLocationFields('search_results'), $sort_dropdown_txt);

            $tpl_vars['browse_mode_txt'] = array (
                'sort_by' => $this->messages[501694],
                'sort' => $sort_dropdown_txt,
                'view' => array(
                    'grid' => $this->messages[501698],
                    'list' => $this->messages[501699],
                    'gallery' => $this->messages[501700],
                ),
            );
            $types = array('grid','list','gallery');
            foreach ($types as $type) {
                if ($this->db->get_site_setting('display_browse_view_link_' . $type)) {
                    $tpl_vars['display_browse_view_links'][] = $type;
                }
            }
            $tpl_vars['browse_tpl'] = 'common/' . $browse_view . '_view.tpl';
            $tpl_vars['browse_view'] = $browse_view;

            $tpl_vars['gallery_columns'] = $this->db->get_site_setting('browse_gallery_number_columns');
            //for backwards compatibility in templates
            $tpl_vars['gallery_percent'] = round((100 / max(1, $tpl_vars['gallery_columns'])), 2);
            $tpl_vars['browse_sort_c'] = $this->browse_type;
            $tpl_vars['browse_sort_dropdown_display'] = $this->db->get_site_setting('browse_sort_dropdown_display');

            if ($browse_view == 'gallery') {
                $tpl_vars['main_page_gallery_sub_template'] = $view->getTemplateAttachment('3_gallery', $this->language_id, $this->site_category, false);
            } elseif ($browse_view == 'list') {
                $tpl_vars['main_page_list_sub_template'] = $view->getTemplateAttachment('3_list', $this->language_id, $this->site_category, false);
            } elseif ($browse_view == 'grid') {
                $tpl_vars['main_page_grid_sub_template'] = $view->getTemplateAttachment('3_grid', $this->language_id, $this->site_category, false);
            }
            $tpl_vars['browse_sort_url'] = $this->search_link . "&amp;order=";
            $tpl_vars['browse_view_url'] = $tpl_vars['browse_sort_url'] . $this->browse_type . '&amp;browse_view=';


            $view->setBodyTpl('search.tpl', '', 'browsing')
                ->setBodyVar($tpl_vars);
            $this->display_page();
            return true;
        } else {
            // No search criteria
            //no results
            $this->body .= "<table><tr class=\"search_page_instructions\">\n\t<td colspan=\"4\">\n\t" . geoString::fromDB($this->messages[592]) . "\n\t</td>\n</tr></table>\n";
            return false;
        }
    }

    public function display_browse_result($browse_result)
    {
        return $this->BuildResults(0, $browse_result);
    }

    public function BuildResults($result = 0)
    {
        $this_copy =& $this;
        $overload = geoAddon::triggerDisplay('overload_Search_classifieds_BuildResults', array ('result' => $result, 'this' => $this_copy), geoAddon::OVERLOAD);
        if ($overload !== geoAddon::NO_OVERLOAD) {
            return $overload;
        }
        geoListing::addDataSet($result);
        $tpl_vars = array();

        $cfg = $listings = $headers = array();

        $fields = $this->fields->getDisplayLocationFields('search_results');

        //set up header view vars
        $headers['css'] = 'browsing_result_table_header';

        $this->search_link = $this->configuration_data['classifieds_file_name'] . "?a=19";
        if ($this->site_category) {
            //category
            $this->search_link .= "&amp;c=" . $this->site_category;
        }

        foreach ($this->search_criteria as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $sub_key => $sub_val) {
                    if (!is_array($sub_val) && strlen(trim($sub_val)) > 0) {
                        //Note:  values already html entitied by clean_inputs.php
                        $this->search_link .= '&amp;b[' . $key . '][' . $sub_key . ']=' . $sub_val;
                    } elseif (is_array($sub_val) && count($sub_val)) {
                        foreach ($sub_val as $sub_sub_key => $sub_sub_val) {
                            if (strlen(trim($sub_sub_val)) > 0) {
                                //NOTE:  Don't bother doing some fancy recursion, since
                                //at most it should only go 3 levels deep
                                $this->search_link .= '&amp;b[' . $key . '][' . $sub_key . '][' . $sub_sub_key . ']=' . $sub_sub_val;
                            }
                        }
                    }
                }
            } elseif ($key != 'page' && strlen(trim($val)) > 0 || $key === 'search_text') {
                //special: always include search_text in link, to allow searching for "nothing" to consistently work across pagination
                if ($key == 'by_country') {
                    //special case - country needs to always be urlencoded
                    $val = geoString::toDB($val);
                }
                //Note:  values already html entitied by clean_inputs.php
                $this->search_link .= '&amp;b[' . $key . ']=' . $val;
            }
        }

        $cfg['sort_links'] = true;
        $cfg['browse_url'] = $this->search_link . "&amp;order=";
        $cfg['listing_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";

        $cfg['cols']['type'] = (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? true : false;
        $headers['type'] = array(
            'css' => 'item_type_column_header',
            'text' => $this->messages[200024],
            //NO LABEL
        );

        $cfg['cols']['business_type'] = ($fields['business_type']) ? true : false;
        $headers['business_type'] = array(
            'css' => 'business_type_column_header',
            'text' => $this->messages[500245],
            'label' => $this->messages[501918],
        );
        if ($this->browse_type == 43) {
            $headers['business_type']['reorder'] = 44;
        } elseif ($this->browse_type == 44) {
            $headers['business_type']['reorder'] = 0;
        } else {
            $headers['business_type']['reorder'] = 43;
        }

        $cfg['cols']['image'] = ($fields['photo']) ? true : false;
        $headers['image'] = array(
            'css' => 'photo_column_header',
            'text' => $this->messages[594],
            //NO LABEL
        );

        $cfg['cols']['title'] = ($fields['title']) ? true : false;
        $headers['title'] = array(
            'css' => 'title_column_header',
            'text' => $this->messages[595],
            'label' => $this->messages[501922],
        );
        if (!$fields['title']) {
            $cfg['cols']['icons'] = (bool)$fields['icons'];
        }
        if ($this->browse_type == 5) {
            $headers['title']['reorder'] = 6;
        } elseif ($this->browse_type == 6) {
            $headers['title']['reorder'] = 0;
        } else {
            $headers['title']['reorder'] = 5;
        }
        $cfg['description_under_title'] = ($fields['description'] && $this->configuration_data['display_ad_description_where']) ? true : false;

        $cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']) ? true : false;
        $headers['description'] = array(
            'css' => 'description_column_header',
            'text' =>  $this->messages[596],
            'label' => $this->messages[501926],
        );

        //Listing tags column
        $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
        $headers['tags'] = array(
            'css' => 'tags_column_header',
            'text' =>  $this->messages[500880],
            'label' => $this->messages[501930],
        );

        //Leveled fields
        $lField = geoLeveledField::getInstance();
        $leveled_field_ids = $lField->getLeveledFieldIds();
        foreach ($leveled_field_ids as $lev_id) {
            //go through each level, see if that level should be displayed
            $maxLevels = $lField->getMaxLevel($lev_id, true);
            for ($i = 1; $i <= $maxLevels; $i++) {
                if (!$fields['leveled_' . $lev_id . '_' . $i]) {
                    //this level not set to show...
                    continue;
                }

                //show this region
                $levelInfo = $lField->getLevel($lev_id, $i, $this->db->getLanguage());
                $headers['leveled'][$lev_id][$i] = array (
                    'css' => 'leveled_' . $lev_id . '_' . $i,
                    'text' => $levelInfo['label'],
                    'label' => $levelInfo['label'],
                    );
                $cfg['cols']['leveled'][$lev_id][$i] = true;
            }
        }

        for ($i = 1; $i <= 20; $i++) {
            if ($fields['optional_field_' . $i]) {
                $cfg['cols']['optionals'][$i] = true;
                $headers['optionals'][$i] = array(
                    'css' => 'optional_field_header_' . $i,
                    'text' => $this->messages[(($i <= 10) ? 1442 + $i : 1922 + ($i - 10))],
                    'label' => $this->messages[501966 + (($i - 1) * 4)],
                );
                $browse1 = ($i <= 10) ? ( 2 * ($i - 1) + 15 ) : ( 2 * ($i - 11) + 45 ) ; //15, 17, 19, ... : 45, 47, 49, ...
                $browse2 = $browse1 + 1;
                if ($this->browse_type == $browse1) {
                    $headers['optionals'][$i]['reorder'] = $browse2;
                } elseif ($this->browse_type == $browse2) {
                    $headers['optionals'][$i]['reorder'] = 0;
                } else {
                    $headers['optionals'][$i]['reorder'] = $browse1;
                }
            } else {
                $cfg['cols']['optionals'][$i] = false;
            }
        }
        //optional 1: 501966
        //optional 2: 501970

        $cfg['cols']['address'] = ($fields['address']) ? true : false;
        $headers['address'] = array(
            'css' => 'address_column_header',
            'text' => $this->messages[500246],
            'label' => $this->messages[501934],
        );

        $cfg['cols']['city'] = ($fields['city']) ? true : false;
        $headers['city'] = array(
            'css' => 'city_column_header',
            'text' => $this->messages[1453],
            'label' => $this->messages[501938],
        );
        if ($this->browse_type == 7) {
            $headers['city']['reorder'] = 8;
        } elseif ($this->browse_type == 8) {
            $headers['city']['reorder'] = 0;
        } else {
            $headers['city']['reorder'] = 7;
        }



        $cfg['cols']['location_breadcrumb'] = ($fields['location_breadcrumb']) ? true : false;
        $headers['location_breadcrumb'] = array(
            'css' => 'location_breadcrumb_column_header',
            'text' => $this->messages[501628],
            'label' => $this->messages[501942],
        );
        $enabledRegions = array();
        $maxLocationDepth = 0;
        for ($r = 1; $r <= geoRegion::getLowestLevel(); $r++) {
            if ($fields['region_level_' . $r]) {
                $enabledRegions[] = $r;
                $maxLocationDepth = $r;
            }
        }
        $cfg['maxLocationDepth'] = $maxLocationDepth;
        foreach ($enabledRegions as $level) {
            $cfg['cols']['region_level_' . $level] = true;
            $headers['region_level_' . $level] = array(
                'css' => 'region_level_' . $level . '_column_header',
                'text' => $label = geoRegion::getLabelForLevel($level),
                'label' => $label,
            );
        }

        $cfg['cols']['zip'] = ($fields['zip']) ? true : false;
        $headers['zip'] = array(
            'css' => 'zip_column_header',
            'text' => $this->messages[1456],
            'label' => $this->messages[501946],
        );
        if ($this->browse_type == 13) {
            $headers['zip']['reorder'] = 14;
        } elseif ($this->browse_type == 14) {
            $headers['zip']['reorder'] = 0;
        } else {
            $headers['zip']['reorder'] = 13;
        }

        $cfg['cols']['price'] = ($fields['price']) ? true : false;
        $headers['price'] = array(
            'css' => 'price_column_header',
            'text' => $this->messages[597],
            'label' => $this->messages[501950],
        );
        if ($this->browse_type == 1) {
            $headers['price']['reorder'] = 2;
        } elseif ($this->browse_type == 2) {
            $headers['price']['reorder'] = 0;
        } else {
            $headers['price']['reorder'] = 1;
        }


        $cfg['cols']['num_bids'] = (geoMaster::is('auctions') && $fields['num_bids']) ? true : false;
        $headers['num_bids'] = array(
            'css' => 'number_bids_header',
            'text' => $this->messages[500247],
            'label' => $this->messages[501954],
        );


        $cfg['cols']['entry_date'] = ((geoMaster::is('classifieds') && $fields['classified_start']) || (geoMaster::is('auctions') && $fields['auction_start'])) ? true : false;
        $headers['entry_date'] = array(
            'css' => 'price_column_header',
            'text' => $this->messages[598],
            'label' => $this->messages[501958],
        );
        if ($this->browse_type == 4) {
            $headers['entry_date']['reorder'] = 3;
        } elseif ($this->browse_type == 3) {
            $headers['entry_date']['reorder'] = 0;
        } else {
            $headers['entry_date']['reorder'] = 4;
        }

        $cfg['cols']['time_left'] = ((geoMaster::is('classifieds') && $fields['classified_time_left']) || (geoMaster::is('auctions') && $fields['auction_time_left'])) ? true : false;
        $headers['time_left'] = array(
            'css' => 'price_column_header',
            'text' => $this->messages[500092],
            'label' => $this->messages[501962],
        );
        if ($this->browse_type == 70) {
            $headers['time_left']['reorder'] = 69;
        } elseif ($this->browse_type == 69) {
            $headers['time_left']['reorder'] = 0;
        } else {
            $headers['time_left']['reorder'] = 70;
        }

        $cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL)) ? true : false;
        $headers['edit'] = array(
            'css' => 'price_column_header',
            'text' => 'edit',
            //NO LABEL
        );

        $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
        $headers['delete'] = array(
            'css' => 'price_column_header',
            'text' => 'delete',
            //NO LABEL
        );

        /**
         * Addon core event:
         * name: Browse_tag_display_browse_result_addHeader
         * vars: array (this => Object) (this is the instance of $this.
         * return: array (class => string (CSS Class), text => string (what should be displayed)
         */
        $tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Search_classifieds_BuildResults_addHeader', array('this' => $this, 'search_fields' => $fields), geoAddon::ARRAY_ARRAY);

        if ($this->configuration_data['popup_while_browsing']) {
            $cfg['popup'] = true;
            $cfg['popup_width'] = $this->configuration_data['popup_while_browsing_width'];
            $cfg['popup_height'] = $this->configuration_data['popup_while_browsing_height'];
        } else {
            $cfg['popup'] = false;
        }
        $cfg['icons'] = array(
            'sold' => (($this->messages[500798]) ? geoTemplate::getUrl('', $this->messages[500798]) : ''),
            'buy_now' => (($this->messages[500799]) ? geoTemplate::getUrl('', $this->messages[500799]) : ''),
            'reserve_met' => (($this->messages[500800]) ? geoTemplate::getUrl('', $this->messages[500800]) : ''),
            'reserve_not_met' => (($this->messages[501665]) ? geoTemplate::getUrl('', $this->messages[501665]) : ''),
            'no_reserve' => (($this->messages[500802]) ? geoTemplate::getUrl('', $this->messages[500802]) : ''),
            'verified' => (($this->messages[500952]) ? geoTemplate::getUrl('', $this->messages[500952]) : ''),
            'addon_icons' => geoAddon::triggerDisplay('use_listing_icons', null, geoAddon::BOOL_TRUE),
        );

        $cfg['empty'] = $this->messages[501619];

        $tpl_vars['cfg'] = $cfg;
        $tpl_vars['headers'] = $headers;

        //now set up all the listing data

        //common text
        $text = array(
            'item_type' => array(
                'classified' => $this->messages[200026],
                'auction' => $this->messages[200025]
            ),
            'business_type' => array(
                1 => $this->messages[500400],
                2 => $this->messages[500401],
            ),
            'time_left' => array(
                'weeks' => $this->messages[500087],
                'days' => $this->messages[500088],
                'hours' => $this->messages[500089],
                'minutes' => $this->messages[500090],
                'seconds' => $this->messages[500091],
                'closed' => $this->messages[500093]
            ),
            //cheat and use this array to pass in a couple of settings specifically for mixed-type results
            'mixed_settings' => array(
                    'hide_entry_date_classified' => !$fields['classified_start'],
                    'hide_entry_date_auction' => !$fields['auction_start'],
                    'hide_time_left_classified' => !$fields['classified_time_left'],
                    'hide_time_left_auction' => !$fields['auction_time_left'],
            )
        );

        while ($row = $result->FetchRow()) {
            $id = $row['id']; //template expects $listings to be keyed by classified id

            $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

            //use the common geoBrowse class to do all the common heavy lifting
            $listings[$id] = $this->commonBrowseData($row, $text);

            //css is different enough to not include in the common file
            $listings[$id]['css'] = 'browsing_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');

            //also do addons separately
            $listings[$id]['addonData'] = geoAddon::triggerDisplay('Search_classifieds_BuildResults_addRow', array('this' => $this,'listing_id' => $id, 'search_fields' => $fields), geoAddon::ARRAY_ARRAY);
        }
        $tpl_vars['listings'] = $listings;
        geoView::getInstance()->setBodyVar($tpl_vars);
        return true;
    } // end function BuildResults

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    function CountOptionalFields()
    {
        $count = 0;
        for ($i = 1; $i <= 20; $i++) {
            $field = 'optional_field_' . $i;
            if ($this->fields->$field->is_enabled) {
                $count = $i;
            }
        }

        $this->max_optional_fields = $count;
    }
} //end of class Search_classifieds
