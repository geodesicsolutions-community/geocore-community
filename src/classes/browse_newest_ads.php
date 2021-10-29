<?php

//browse_newest_ads.php
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

class Browse_newest_ads extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();
    var $sort_type;
    var $lookback;

//########################################################################

    public function __construct($category_id = 0, $page = 0, $browse_type = 0)
    {
        $this->site_category = (int)$category_id;

        $this->db = DataAccess::getInstance();

        $this->get_ad_configuration();

        $this->page_result = ($page) ? (int)$page : 1;
        $this->browse_type = (int)$browse_type;

        parent::__construct();
    } //end of function Browse_newest_ads

//###########################################################

    public function browse($sort_type = 0, $lookback = 0)
    {
        if (!geoCategory::getBasicInfo($this->site_category)) {
            //invalid category
            $this->browse_error();
        }

        $this->browse_type = (int)$sort_type;
        $this->lookback = (int)$lookback;
        $this->page_id = 64;
        $this->get_text();

        $view = geoView::getInstance();
        $tpl_vars = array();

        $classTable = geoTables::classifieds_table;

        switch ($this->lookback) {
            case 2:
                //last 2 weeks
                $cutoff_time = (geoUtil::time() - (86400 * 14));
                break;

            case 3:
                //last 3 weeks
                $cutoff_time = (geoUtil::time() - (86400 * 21));
                break;

            case 4:
                //last 24 hours
                $cutoff_time = (geoUtil::time() - 86400);
                break;

            case 1:
                //break ommitted on purpose

            default:
                //last 1 week
                $cutoff_time = (geoUtil::time() - (86400 * 7));
                break;
        }

        //add to original (instead of copy like normal), so that category counts dynamically retrieved,
        //and category cache is not used
        $this->db->getTableSelect(DataAccess::SELECT_BROWSE)->where("`date` > $cutoff_time", 'browsing_newest_ads');

        //that is the only part we add to the "original", the rest we do to a copy...
        $query = $this->db->getTableSelect(DataAccess::SELECT_BROWSE, true);

        if (($this->browse_type == 0) && ($this->db->get_site_setting('default_display_order_while_browsing'))) {
            $this->browse_type = $this->db->get_site_setting('default_display_order_while_browsing');
        }
        $query->order($this->getOrderByString(false, $this->site_category));

        if ($this->site_category) {
            $this->whereCategory($query, $this->site_category);
        }

        //NOTE:  where date > ## is added using "original" tableSelect above, don't need to add here too...
        $query->where("$classTable.`live` = 1", 'live');

        //allow addons to add to or modify the where clause
        geoAddon::triggerUpdate('Browse_newest_ads_generate_query', array('this' => $this, 'query' => $query));

        $adsToShow = $this->db->get_site_setting('number_of_ads_to_display');
        $query->order("$classTable.date DESC")
            ->limit((($this->page_result - 1) * $adsToShow), $adsToShow);

        if (geoMaster::is('classifieds')) {
            $classQuery = clone $query;
            $classQuery->where("$classTable.item_type = 1");

            //get the count
            $total_returned_ads = $this->db->GetOne('' . $classQuery->getCountQuery());

            $result = $this->db->Execute('' . $classQuery);

            unset($classQuery);//done with classifieds query
        }
        if (geoMaster::is('auctions')) {
            $auctionQuery = clone $query;

            $auctionQuery->where("$classTable.item_type = 2");

            //get the count
            $total_returned_auctions = $this->db->GetOne('' . $auctionQuery->getCountQuery());

            //get the results
            $result_auctions = $this->db->Execute('' . $auctionQuery);

            unset($auctionQuery);//done with auctions query
        }

        if (!$result && !$result_auctions) {
            $this->error_message = '<span class="error_message">' . urldecode($this->messages[65]) . '</span>';
            return false;
        }

        //set up total_returned as the larger of the two, so that pagination goes all the way to the end
        $total_returned = max($total_returned_ads, $total_returned_auctions);

        $numPages = max(1, ceil($total_returned / $this->db->get_site_setting('number_of_ads_to_display')));
        if ($this->page_result > $numPages) {
            //trying to access a page that doesn't exist
            //(could be a search engine crawling an outdated url)
            $this->error_message = "<span class=\"error_message\">" . urldecode($this->messages[65]) . "</span>";
            $this->browse_error();
            return false;
        }

        //get this category's name
        if ($this->site_category) {
            $current_category_name = geoCategory::getName($this->site_category);
            $parent_id = $this->site_category;
        } else {
            $current_category_name = new stdClass();
            $current_category_name->CATEGORY_NAME = urldecode($this->messages[904]);
            $parent_id = 0;
        }
        $tpl_vars['current_category_name'] = $current_category_name->CATEGORY_NAME;

        $text = array(
            'back_to_normal_link' => $this->messages[888],
            'tree_label' => $this->messages[890],
            'main_category' => $this->messages[891],
            'no_subcats' => $this->messages[889]
        );
        $tpl_vars['category_cache'] = $category_cache = $this->categoryBrowsing($text, 'newest_');
        $show_classifieds = $show_auctions = false;
        if (geoMaster::is('classifieds')) {
            //featured ads
            $tpl_vars['show_classifieds'] = $show_classifieds = true;
            $tpl_vars['classified_browse_result'] = $this->display_browse_result($result);
        }
        if (geoMaster::is('auctions')) {
            //featured auctions
            $tpl_vars['show_auctions'] = $show_auctions = true;
            $tpl_vars['auction_browse_result'] = $this->display_browse_result($result_auctions, 1);
        }

        if ($this->db->get_site_setting('number_of_ads_to_display') < $total_returned) {
            $c_str = ($this->browse_type) ? '&amp;c=' . $this->browse_type : '';
            $url = $this->db->get_site_setting('classifieds_file_name') . "?a=11&amp;b=" . $this->site_category . $c_str . "&amp;d=" . $this->lookback . "&amp;page=";
            $css = "browsing_result_page_links";
            $tpl_vars['pagination'] = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
        }

        $browse_view = $this->getCurrentBrowseView();

        if ($show_classifieds && $show_auctions) {
            if ($this->db->get_site_setting('disable_browsing_tabs')) {
                //disable tabs...
                $tpl_vars['disable_browsing_tabs'] = true;
            } elseif ($this->db->get_site_setting('display_all_tab_browsing')) {
                //for the all tab
                $tpl_vars['display_all_tab_browsing'] = true;
                $view->addJScript(geoTemplate::getUrl('js', 'system/browsing/all_tab.js'));
            }
        }
        //page 64
        $sort_dropdown_txt = array (
            0 => $this->messages[501703], //Relevance (AKA no sorting applied)
            1 => $this->messages[501677], //price - cheapest first
            2 => $this->messages[501678], //price - expensive first
            3 => $this->messages[501707], //date - oldest first
            4 => $this->messages[501676], //date - newest first
            5 => $this->messages[501711], //title - a first
            6 => $this->messages[501715], //title - z first
            7 => $this->messages[501719], //location_city - a first
            8 => $this->messages[501723], //location_city - z first
            13 => $this->messages[501727], //zip - 0 first
            14 => $this->messages[501731], //zip - 9 first
            15 => $this->messages[501735], //optional field 1
            16 => $this->messages[501739], //optional field 1 reverse
            17 => $this->messages[501743], //optional field 2
            18 => $this->messages[501747], //optional field 2 reversed
            19 => $this->messages[501751], //optional field 3
            20 => $this->messages[501755], //optional field 3 reversed
            21 => $this->messages[501759], //optional field 4
            22 => $this->messages[501763], //optional field 4 reverse
            23 => $this->messages[501767], //optional field 5
            24 => $this->messages[501771], //optional field 5 reversed
            25 => $this->messages[501775], //optional field 6
            26 => $this->messages[501779], //optional field 6 reversed
            27 => $this->messages[501783], //optional field 7
            28 => $this->messages[501787], //optional field 7 reverse
            29 => $this->messages[501791], //optional field 8
            30 => $this->messages[501795], //optional field 8 reversed
            31 => $this->messages[501799], //optional field 9
            32 => $this->messages[501803], //optional field 9 reversed
            33 => $this->messages[501807], //optional field 10
            34 => $this->messages[501811], //optional field 10 reverse
            45 => $this->messages[501815], //optional field 11
            46 => $this->messages[501819], //optional field 11 reverse
            47 => $this->messages[501823], //optional field 12
            48 => $this->messages[501827], //optional field 12 reversed
            49 => $this->messages[501831], //optional field 13
            50 => $this->messages[501835], //optional field 13 reversed
            51 => $this->messages[501839], //optional field 14
            52 => $this->messages[501843], //optional field 14 reverse
            53 => $this->messages[501847], //optional field 15
            54 => $this->messages[501851], //optional field 15 reversed
            55 => $this->messages[501855], //optional field 16
            56 => $this->messages[501859], //optional field 16 reversed
            57 => $this->messages[501863], //optional field 17
            58 => $this->messages[501867], //optional field 17 reverse
            59 => $this->messages[501871], //optional field 18
            60 => $this->messages[501875], //optional field 18 reversed
            61 => $this->messages[501879], //optional field 19
            62 => $this->messages[501883], //optional field 19 reversed
            63 => $this->messages[501887], //optional field 20
            64 => $this->messages[501891], //optional field 20 reverse
            43 => $this->messages[501895], //business type
            44 => $this->messages[501899], //business type reversed
            69 => $this->messages[501903], //ends (soon)
            70 => $this->messages[501907], //ends (most time left)
            71 => $this->messages[501911], //listings without images first
            72 => $this->messages[501915], //listings with images first
        );
        $sort_dropdown_txt = $this->getSortOptions($this->fields->getDisplayLocationFields('browsing'), $sort_dropdown_txt);

        $tpl_vars['browse_mode_txt'] = array (
            'sort_by' => $this->messages[501675],
            'sort' => $sort_dropdown_txt,
            'view' => array (
                'grid' => $this->messages[501692],
                'list' => $this->messages[501679],
                'gallery' => $this->messages[501680],
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
        $tpl_vars['browse_sort_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=11&amp;b=" . $this->site_category . "&amp;d=" . $this->lookback . "&amp;c=";
        $tpl_vars['browse_view_url'] = $tpl_vars['browse_sort_url'] . $this->browse_type . '&amp;browse_view=';

        $view->setBodyTpl('newest_ads.tpl', '', 'browsing')
            ->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    } //end of function browse

//####################################################################################

    function display_browse_result($browse_result, $auction = 0)
    {
        $db = DataAccess::getInstance();
        $tpl_vars = array();

        if ($browse_result->RecordCount() < 1) {
            //no listings in this category
            if ($auction) {
                $tpl_vars['no_listings'] = $this->messages[100898];
            } else {
                $tpl_vars['no_listings'] = $this->messages[898];
            }
        } else {
            $cfg = $listings = $headers = array();
            //use main browsing display settings for now
            $fields = $this->fields->getDisplayLocationFields('browsing');

            //set up header view vars
            $headers['css'] = 'browsing_result_table_header';

            $cfg['sort_links'] = true;
            $cfg['browse_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=11&amp;b=" . $this->site_category . "&amp;d=" . $this->lookback . "&amp;c=";
            $cfg['listing_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";

            $cfg['cols']['business_type'] = ($fields['business_type']) ? true : false;
            $headers['business_type'] = array(
                'css' => 'business_type_column_header',
                'text' => $this->messages[1404],
                'label' => $this->messages[501919],
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
                'text' => $this->messages[893],
                //NO LABEL
            );

            $cfg['cols']['title'] = ($fields['title']) ? true : false;
            $headers['title'] = array(
                'css' => 'title_column_header',
                'text' => $this->messages[894],
                'label' => $this->messages[501923],
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
                'text' =>  $this->messages[895],
                'label' => $this->messages[501927],
            );

            //Listing tags column
            $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
            $headers['tags'] = array(
                'css' => 'tags_column_header',
                'text' =>  $this->messages[500878],
                'label' => $this->messages[501931],
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
                        'text' => (($i <= 10) ? $this->messages[968 + $i] : $this->messages[2280 + $i]),
                        'label' => $this->messages[501967 + (($i - 1) * 4)],
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
            //optional 1: 501967
            //optional 2: 501971

            $cfg['cols']['address'] = false;
            //oops...  If we ever add address field, it has text already for label,
            //text ID 501935

            $cfg['cols']['city'] = ($fields['city']) ? true : false;
            $headers['city'] = array(
                'css' => 'city_column_header',
                'text' => $this->messages[1335],
                'label' => $this->messages[501939],
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
                'text' => $this->messages[501626],
                'label' => $this->messages[501943],
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
                'text' => $this->messages[1338],
                'label' => $this->messages[501947],
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
                'text' => $this->messages[896],
                'label' => $this->messages[501951],
            );
            if ($this->browse_type == 1) {
                $headers['price']['reorder'] = 2;
            } elseif ($this->browse_type == 2) {
                $headers['price']['reorder'] = 0;
            } else {
                $headers['price']['reorder'] = 1;
            }


            $cfg['cols']['num_bids'] = ($auction && $fields['num_bids']) ? true : false;
            $headers['num_bids'] = array(
                'css' => 'newest_bids_column_header',
                'text' => $this->messages[102537],
                'label' => $this->messages[501955],
            );


            $cfg['cols']['entry_date'] = ((!$auction && $fields['classified_start']) || ($auction && $fields['auction_start'])) ? true : false;
            $headers['entry_date'] = array(
                'css' => 'entry_date_column_header',
                'text' => $this->messages[897],
                'label' => $this->messages[501959],
            );
            if ($this->browse_type == 4) {
                $headers['entry_date']['reorder'] = 3;
            } elseif ($this->browse_type == 3) {
                $headers['entry_date']['reorder'] = 0;
            } else {
                $headers['entry_date']['reorder'] = 4;
            }

            $cfg['cols']['time_left'] = ((!$auction && $fields['classified_time_left']) || ($auction && $fields['auction_time_left'])) ? true : false;
            $headers['time_left'] = array(
                'css' => 'newest_time_left_column_header',
                'text' => $this->messages[102538],
                'label' => $this->messages[501963],
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
                'css' => 'time_left_column_header',
                'text' => 'edit',
                //NO LABEL
            );

            $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
            $headers['delete'] = array(
                'css' => 'time_left_column_header',
                'text' => 'delete',
                //NO LABEL
            );

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

            $cfg['empty'] = '-';

            $tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addHeader', array('this' => $this, 'browse_fields' => $fields), geoAddon::ARRAY_ARRAY);
            $tpl_vars['cfg'] = $cfg;
            $tpl_vars['headers'] = $headers;

            //now set up all the listing data

            //common text
            $text = array(
                'business_type' => array(
                    1 => $this->messages[1405],
                    2 => $this->messages[1406],
                ),
                'time_left' => array(
                    'weeks' => $this->messages[102539],
                    'days' => $this->messages[102540],
                    'hours' => $this->messages[102541],
                    'minutes' => $this->messages[102542],
                    'seconds' => $this->messages[102543],
                    'closed' => $this->messages[100051]
                )
            );

            while ($row = $browse_result->FetchRow()) {
                $id = $row['id']; //template expects $listings to be keyed by classified id

                $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

                //use the common geoBrowse class to do all the common heavy lifting
                $listings[$id] = $this->commonBrowseData($row, $text);

                //css is different enough to not include in the common file
                $listings[$id]['css'] = 'browsing_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');

                //also do addons separately
                //TODO: make this a separate hook, instead of the one from main browsing?
                $listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addRow', array('this' => $this,'show_classifieds' => $row, 'browse_fields' => $fields), geoAddon::ARRAY_ARRAY);
            }
            $tpl_vars['listings'] = $listings;
        }
        return $tpl_vars;
    }
}
