<?php

//browse_displays_sellers_ads.php


class Browse_display_sellers_ads extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();
    var $seller_id = 0;

//########################################################################

    public function __construct($db, $classified_user_id, $language_id, $category_id = 0, $page = 0, $seller_id = 0, $product_configuration = 0)
    {
        if ($category_id) {
            $this->site_category = (int)$category_id;
        } else {
            $this->site_category = 0;
        }
        if ($limit) {
            $this->browse_limit = (int)$limit;
        }

        $db = $this->db = DataAccess::getInstance();

        $this->get_ad_configuration($db);

        $this->page_result = ($page) ? (int)$page : 1;
        $this->seller_id = (int)$seller_id;
        parent::__construct();
    } //end of function Browse_display_sellers_ads

//###########################################################

    function browse()
    {
        $this->page_id = 55;
        $this->get_text();
        $view = geoView::getInstance();
        $tpl_vars = array();

        //browse the auctions in this category that are open

        $seller = geoUser::getUser($this->seller_id);
        if (!$seller) {
            $this->browse_error();
            return false;
        }
        $tpl_vars['seller_data_raw'] = $seller->toArray();


        //make seller username available as main template variable
        $tpl_vars['username'] = $view->seller_username = $seller->username;
        //$seller_data = $this->get_user_data($this->seller_id);

        $exposed = array();

        if ($seller->expose_email) {
            $exposed[] = array(
                'label' => $this->messages[1575],
                'value' => $seller->email
            );
        }

        if ($seller->expose_company_name) {
            $exposed[] = array(
                'label' => $this->messages[1576],
                'value' => $seller->company_name
            );
        }

        if ($seller->expose_firstname || $seller->expose_lastname) {
            $exposed[] = array(
                'label' => $this->messages[1577],
                'value' => $seller->firstname . ' ' . $seller->lastname
            );
        }

        if ($seller->expose_address) {
            $exposed[] = array(
                'label' => $this->messages[1579],
                'value' => $seller->address
            );
        }

        $location = '';
        if ($seller->expose_city) { //all of these now use the "expose_city" checkbox
            $locations = array();
            if ($seller->city) {
                $locations[] = $seller->city;
            }
            if ($seller->state) {
                $locations[] = $seller->state;
            }
            if ($seller->country) {
                $locations[] = $seller->country;
            }
            $location .= implode(', ', $locations);
        }
        if ($seller->expose_zip) {
            $location .= ' ' . $seller->zip;
        }
        $location = trim($location);
        if (strlen($location)) {
            $exposed[] = array(
                'label' => $this->messages[1580],
                'value' => $location
            );
        }

        if ($seller->expose_phone) {
            $exposed[] = array(
                'label' => $this->messages[1584],
                'value' => geoNumber::phoneFormat($seller->phone)
            );
        }

        if ($seller->expose_phone2) {
            $exposed[] = array(
                'label' => $this->messages[1585],
                'value' => geoNumber::phoneFormat($seller->phone2)
            );
        }

        if ($seller->expose_fax) {
            $exposed[] = array(
                'label' => $this->messages[1586],
                'value' => geoNumber::phoneFormat($seller->fax)
            );
        }

        if ($seller->expose_url) {
            $exposed[] = array(
                'label' => $this->messages[1587],
                'value' => $seller->url
            );
        }

        //REGISTRATION optional fields (only 10 instead of 20)
        for ($i = 1; $i <= 10; $i++) {
            $exp = 'expose_optional_' . $i;
            $data = 'optional_field_' . $i;
            if ($seller->$exp) {
                $exposed[] = array(
                    'label' => $this->messages[1587 + $i],
                    'value' => $seller->$data
                );
            }
        }

        $tpl_vars['exposed'] = $exposed;

        $db = DataAccess::getInstance();

        //Start with "empty" query, when showing seller's other ads it should NOT
        //filter by anything.
        $query = new geoTableSelect(geoTables::classifieds_table);
        if ($db->get_site_setting('hide_sold') && !$db->get_site_setting('show_sold_sellers_other_ads')) {
            //add filter to hide any that are sold
            $query->where(geoTables::classifieds_table . ".`sold_displayed`=0", 'sold');
        }
        $classTable = geoTables::classifieds_table;
        $query->where("$classTable.seller = " . $this->seller_id)
            ->where("$classTable.live = 1")
            ->order("better_placement desc, date desc")
            ->limit((($this->page_result - 1) * $this->configuration_data['number_of_ads_to_display']), $this->configuration_data['number_of_ads_to_display']);
        $total_returned = $db->GetOne('' . $query->getCountQuery());
        $result = $db->Execute('' . $query);

        $numPages = max(1, ceil($total_returned / $this->configuration_data['number_of_ads_to_display']));
        if ($this->page_result > $numPages) {
            //trying to access a page that doesn't exist
            //(could be a search engine crawling an outdated url)
            $this->error_message = "<span class=\"error_message\">" . urldecode($this->messages[65]) . "</span>";
            $this->browse_error();
            return false;
        }

        //display_browse_result sets up its vars in the view class, but leaves it to this function to actually display stuff
        $this->display_browse_result($result);

        if ($this->configuration_data['number_of_ads_to_display'] < $total_returned) {
            $tpl_vars['current_page'] = $this->page_result;
            $tpl_vars['total_pages'] = $numPages;

            $url = $this->configuration_data['classifieds_url'] . "?a=6&amp;b=" . $this->seller_id . "&amp;page=";
            $css = "sellers_ads_more_results";
            $tpl_vars['pagination'] = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
        } else {
            $tpl_vars['pagination'] = false;
        }

        $view->setBodyTpl('sellers_other_ads.tpl', '', 'browsing')->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    }

//####################################################################################

    function display_browse_result($browse_result)
    {
        $db = DataAccess::getInstance();
        $view = geoView::getInstance();
        $tpl_vars = array();

        if ($browse_result->RecordCount() < 1) {
            $tpl_vars['no_listings'] = $this->messages[751];
        } else {
            $cfg = $listings = $headers = array();
            //use main browsing display settings for now
            $fields = $this->fields->getDisplayLocationFields('browsing');

            //set up header view vars
            $headers['css'] = 'seller_result_table_header';

            $cfg['sort_links'] = $cfg['browse_url'] = false; //can't reorder this table by clicking headers
            if ($this->affiliate_id) {
                $cfg['listing_url'] = $this->configuration_data['affiliate_url'] . "?aff=" . $this->affiliate_id . "&amp;a=2&amp;b=";
            } else {
                $cfg['listing_url'] = $db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";
            }

            $cfg['cols']['type'] = (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? true : false;
            $headers['type'] = array(
                'css' => 'seller_title_column_header',
                'text' => $this->messages[200008], 'label' => $this->messages[200008],
            );

            $cfg['cols']['image'] = ($fields['photo']) ? true : false;
            $headers['image'] = array(
                'css' => 'seller_photo_column_header',
                'text' => $this->messages[753], 'label' => $this->messages[753],
            );

            $cfg['cols']['title'] = ($fields['title']) ? true : false;
            $headers['title'] = array(
                'css' => 'seller_title_column_header',
                'text' => $this->messages[752], 'label' => $this->messages[752],
            );
            if (!$fields['title']) {
                $cfg['cols']['icons'] = (bool)$fields['icons'];
            }
            $cfg['description_under_title'] = ($fields['description'] && $this->configuration_data['display_ad_description_where']) ? true : false;

            $cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']) ? true : false;
            $headers['description'] = array(
                'css' => 'seller_description_column_header',
                'text' => (($cfg['description_under_title']) ? $this->messages[21] : $this->messages[754]),
                'label' => (($cfg['description_under_title']) ? $this->messages[21] : $this->messages[754]),
            );

            //Listing tags column
            $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
            $headers['tags'] = array(
                'css' => 'tags_column_header',
                'text' => $this->messages[500876], 'label' => $this->messages[500876],
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
                    $levelInfo = $lField->getLevel($lev_id, $i, $db->getLanguage());
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
                        'text' => (($i <= 10) ? $this->messages[1048 + $i] : $this->messages[1705 + $i]),
                        'label' => (($i <= 10) ? $this->messages[1048 + $i] : $this->messages[1705 + $i]),
                    );
                } else {
                    $cfg['cols']['optionals'][$i] = false;
                }
            }

            $cfg['cols']['address'] = false;

            $cfg['cols']['city'] = ($fields['city']) ? true : false;
            $headers['city'] = array(
                'css' => 'city_column_header',
                'text' => $this->messages[1415], 'label' => $this->messages[1415],
            );

            $cfg['cols']['location_breadcrumb'] = ($fields['location_breadcrumb']) ? true : false;
            $headers['location_breadcrumb'] = array(
                'css' => 'location_breadcrumb_column_header',
                'text' => $this->messages[501624], 'label' => $this->messages[501624],
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
                'text' => $this->messages[1418], 'label' => $this->messages[1418],
            );

            $cfg['cols']['price'] = ($fields['price']) ? true : false;
            $headers['price'] = array(
                'css' => 'seller_price_column_header',
                'text' => $this->messages[755], 'label' => $this->messages[755],
            );

            $cfg['cols']['num_bids'] = false; //num_bids not shown here

            $cfg['cols']['entry_date'] = ((geoMaster::is('classifieds') && $fields['classified_start']) || (geoMaster::is('auctions') && $fields['auction_start'])) ? true : false;
            $headers['entry_date'] = array(
                'css' => 'seller_entry_date_column_header',
                'text' => $this->messages[756], 'label' => $this->messages[756],
            );

            $cfg['cols']['time_left'] = ((geoMaster::is('classifieds') && $fields['classified_time_left']) || (geoMaster::is('auctions') && $fields['auction_time_left'])) ? true : false;
            $headers['time_left'] = array(
                'css' => 'seller_time_left_column_header',
                'text' => $this->messages[102546], 'label' => $this->messages[102546],
            );

            $cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL)) ? true : false;
            $headers['edit'] = array(
                'css' => 'seller_time_left_column_header',
                'text' => 'edit',
                //NO LABEL
            );

            $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
            $headers['delete'] = array(
                'css' => 'seller_time_left_column_header',
                'text' => 'delete',
                //NO LABEL
            );

            //a couple last-minute config settings before we go ahead and give $cfg to the view class
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
                'item_type' => array(
                    'classified' => $this->messages[200010],
                    'auction' => $this->messages[200009]
                ),
                'time_left' => array(
                    'weeks' => $this->messages[3284],
                    'days' => $this->messages[3285],
                    'hours' => $this->messages[3286],
                    'minutes' => $this->messages[3287],
                    'seconds' => $this->messages[3288],
                    'closed' => $this->messages[100051]
                )
            );

            $listings = array();
            $count = 0;

            while ($row = $browse_result->FetchRow()) {
                $id = $row['id']; //template expects $listings to be keyed by classified id

                $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

                //use the common geoBrowse class to do all the common heavy lifting
                $listings[$id] = $this->commonBrowseData($row, $text);

                //css is different enough to not include in the common file
                $listings[$id]['css'] = 'seller_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');

                //also do addons separately
                //TODO: make this a separate hook, instead of the one from main browsing?
                $listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addRow', array('this' => $this,'show_classifieds' => $row, 'browse_fields' => $fields), geoAddon::ARRAY_ARRAY);
            }
            $tpl_vars['listings'] = $listings;
        }
        $view->setBodyVar($tpl_vars);
    }
}
