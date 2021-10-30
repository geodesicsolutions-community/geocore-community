<?php

//browse_featured_text_ads.php



class Browse_featured_text_ads extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();

    var $debug_featured_text = 0;

//########################################################################

    public function __construct($db, $classified_user_id, $language_id, $category_id = 0, $page = 0, $filter_id = 0, $state_filter = 0, $zip_filter = 0, $zip_filter_distance = 0, $product_configuration = 0)
    {
        if (!geoPC::is_ent()) {
            return;
        }
        if ($category_id) {
            $this->site_category = (int)$category_id;
        } elseif ($classified_id) {
            $listing = geoListing::getListing($classified_id);
            if ($listing && $listing->category) {
                $this->site_category = (int)$listing->category;
            }
        } else {
            $this->site_category = 0;
        }
        if ($limit) {
            $this->browse_limit = (int)$limit;
        }

        $db = $this->db = DataAccess::getInstance();

        $this->get_ad_configuration($db);

        $this->page_result = ($page) ? (int)$page : 1;
        $this->affiliate_group_id = (int)$affiliate_group_id;
        $this->affiliate_id = 0;

        parent::__construct();
    }

//###########################################################

    function browse()
    {
        if (!geoPC::is_ent()) {
            return false;
        }

        if (!geoCategory::getBasicInfo($this->site_category)) {
            //invalid category
            $this->browse_error();
        }

        $db = DataAccess::getInstance();
        $this->page_id = 63;
        $this->get_text();

        if (geoPC::is_print() && $this->db->get_site_setting('disableAllBrowsing')) {
            //browsing disabled, do not show browsing contents
            $this->display_page();
            return true;
        }

        $view = geoView::getInstance();
        $tpl_vars = array();


        $query = $this->db->getTableSelect(DataAccess::SELECT_BROWSE, true);

        $classTable = geoTables::classifieds_table;

        $query->where("$classTable.`live` = 1", 'live')
            ->where("$classTable.featured_ad = 1 OR $classTable.featured_ad_2 = 1 OR $classTable.featured_ad_3 = 1 OR $classTable.featured_ad_4 = 1 OR $classTable.featured_ad_5 = 1");
        if ($this->site_category) {
            $this->whereCategory($query, $this->site_category);
        }

        $adsPerPage = $this->db->get_site_setting('featured_ad_page_count');

        $query->order("$classTable.better_placement DESC, $classTable.date DESC")
            ->limit((($this->page_result - 1) * $adsPerPage), $adsPerPage);

        if (geoMaster::is('classifieds')) {
            $classQuery = clone $query;
            $classQuery->where("$classTable.item_type = 1");

            //get the count
            $total_returned_ads = $this->db->GetOne('' . $classQuery->getCountQuery());

            $result = $this->db->Execute('' . $classQuery);
            unset($classQuery);
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
        unset($query);//done with the query

        if (!$result && !$result_auctions) {
            //echo $this->db->ErrorMsg();
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
            $current_category_name->CATEGORY_NAME = urldecode($this->messages[1334]);
            $parent_id = 0;
        }
        $tpl_vars['current_category_name'] = $current_category_name->CATEGORY_NAME;
        $text = array(
            'back_to_normal_link' => $this->messages[876],
            'tree_label' => $this->messages[878],
            'main_category' => $this->messages[879],
            'no_subcats' => $this->messages[877]
        );
        $tpl_vars['category_cache'] = $category_cache = $this->categoryBrowsing($text, 'featured_text_');

        if (geoMaster::is('classifieds') && $result->RecordCount() > 0) {
            //featured ads
            $tpl_vars['show_classifieds'] = $show_classifieds = true;
            $tpl_vars['classified_browse_result'] = $this->display_browse_result($result);
        }
        if (geoMaster::is('auctions') && $result_auctions->RecordCount() > 0) {
            //featured auctions
            $tpl_vars['show_auctions'] = $show_auctions = true;
            $tpl_vars['auction_browse_result'] = $this->display_browse_result($result_auctions, 1);
        }

        if ($this->db->get_site_setting('number_of_ads_to_display') < $total_returned) {
            $url = $this->db->get_site_setting('classifieds_url') . "?a=9&amp;b=" . $this->site_category . "&page=";
            $css = "browsing_result_page_links";
            $tpl_vars['pagination'] = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
        }

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

        $view->setBodyTpl('featured_text_ads.tpl', '', 'browsing')
            ->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    } //end of function browse

//####################################################################################

    function display_browse_result($browse_result, $auction = 0)
    {
        $tpl_vars = array();
        if ($browse_result->RecordCount() < 1) {
            //no listings in this category
            if ($auction) {
                $tpl_vars['no_listings'] = $this->messages[100885];
            } else {
                $tpl_vars['no_listings'] = $this->messages[885];
            }
        } else {
            $cfg = $listings = $headers = array();
            $fields = $cfg['cols'] = $this->fields->getDisplayLocationFields('browsing');

            //set up header view vars
            $headers['css'] = 'browsing_result_table_header';

            $cfg['sort_links'] = $cfg['browse_url'] = false; //can't reorder this table by clicking headers
            if ($this->affiliate_id) {
                $cfg['listing_url'] = $this->configuration_data['affiliate_url'] . "?aff=" . $this->affiliate_id . "&amp;a=2&amp;b=";
            } else {
                $cfg['listing_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";
            }

            $headers['business_type'] = array(
                'css' => 'business_type_column_header',
                'text' => $this->messages[1262], 'label' => $this->messages[1262],
            );

            //browsing by text -- no images to show
            $cfg['cols']['image'] = false;

            $headers['title'] = array(
                'css' => 'title_column_header',
                'text' => (($auction) ? $this->messages[100881] : $this->messages[881])
            );
            $cfg['description_under_title'] = ($fields['description'] && $this->configuration_data['display_ad_description_where']) ? true : false;

            $cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']) ? true : false;
            $headers['description'] = array(
                'css' => 'description_column_header',
                'text' => $this->messages[882], 'label' => $this->messages[882],
            );

            //Listing tags column
            $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
            $headers['tags'] = array(
                'css' => 'tags_column_header',
                'text' => $this->messages[500877], 'label' => $this->messages[500877],
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
                if (geoPC::is_ent() && $fields['optional_field_' . $i]) {
                    $cfg['cols']['optionals'][$i] = true;
                    $headers['optionals'][$i] = array(
                        'css' => 'optional_field_header_' . $i,
                        'text' => (($i <= 10) ? $this->messages[958 + $i] : $this->messages[1846 + $i]),
                        'label' => (($i <= 10) ? $this->messages[958 + $i] : $this->messages[1846 + $i]),
                    );
                } else {
                    $cfg['cols']['optionals'][$i] = false;
                }
            }

            $headers['city'] = array(
                'css' => 'city_column_header',
                'text' => $this->messages[1407], 'label' => $this->messages[1407],
            );


            $cfg['cols']['location_breadcrumb'] = ($fields['location_breadcrumb']) ? true : false;
            $headers['location_breadcrumb'] = array(
                'css' => 'location_breadcrumb_column_header',
                'text' => $this->messages[501625], 'label' => $this->messages[501625],
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

            $headers['zip'] = array(
                'css' => 'zip_column_header',
                'text' => $this->messages[1410], 'label' => $this->messages[1410],
            );

            $headers['price'] = array(
                'css' => 'price_column_header',
                'text' => $this->messages[883], 'label' => $this->messages[883],
            );

            $cfg['cols']['num_bids'] = ($auction) ? $cfg['cols']['num_bids'] : false;
            $headers['num_bids'] = array(
                'css' => 'number_bids_header',
                'text' => $this->messages[102529], 'label' => $this->messages[102529],
            );

            $cfg['cols']['entry_date'] = ((!$auction && $fields['classified_start']) || ($auction && $fields['auction_start'])) ? true : false;
            $headers['entry_date'] = array(
                'css' => 'entry_date_column_header',
                'text' => $this->messages[884], 'label' => $this->messages[884],
            );

            $cfg['cols']['time_left'] = ((!$auction && $fields['classified_time_left']) || ($auction && $fields['auction_time_left'])) ? true : false;
            $headers['time_left'] = array(
                'css' => 'time_left_column_header',
                'text' => $this->messages[102530], 'label' => $this->messages[102530],
            );

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
            $tpl_vars['cfg'] =  $cfg;
            $tpl_vars['headers'] =  $headers;

            //now set up all the listing data

            //common text
            $text = array(
                //the rest of the code for business_type is here, but the text entries are missing...
                'business_type' => array(
                    1 => '',
                    2 => ''
                ),
                'time_left' => array(
                    'weeks' => $this->messages[102532],
                    'days' => $this->messages[102533],
                    'hours' => $this->messages[102534],
                    'minutes' => $this->messages[102535],
                    'seconds' => $this->messages[102535],
                    'closed' => $this->messages[100053]
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
            $tpl_vars['listings'] =  $listings;
        }
        return $tpl_vars;
    }
}
