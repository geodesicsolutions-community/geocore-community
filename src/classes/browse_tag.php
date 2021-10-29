<?php

//browse_tag.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################

class Browse_tag extends geoBrowse
{
    var $config = array();
    private static $listing_config = array();
    var $subcategory_array = array();
    var $notify_data = array();
    var $debug_browse = 0;
    var $lcv = 0;
    var $browsing_options = array();
    private $sql;
    private $filters;
    private $query_data = array();
    private static $smarty_object_are_registered;
    private $tag;

    public function __construct($tag, $page = 0)
    {
        $this->db = DataAccess::getInstance();
        $user_id = geoSession::getInstance()->getUserId();
        $this->site_category = 0;

        if ($page) {
            $this->page_result = (int)$page;
        } else {
            $this->page_result = 1;
        }

        if (isset($_REQUEST['o']) && is_numeric($_REQUEST['o'])) {
            $this->browsing_options['choice'] = (int)$_REQUEST['o'];
        }
        $this->tag = geoFilter::cleanListingTag($tag);

        parent::__construct();
    }


    /**
     * Displays a tag's listings
     *
     * @param int $browse_type not sure what this is for really...
     */
    public function browseTag($browse_type = 0)
    {
        $this->page_id = 10210;
        $this->get_text();
        $browseType = array('param' => 0);
        if (geoPC::is_print() && $this->db->get_site_setting('disableAllBrowsing')) {
            //browsing disabled, do not show browsing contents
            $this->display_page();
            return;
        }
        if ($browse_type) {
            $browseType["param"] = $browse_type;
        }

        $view = geoView::getInstance();
        $tpl_vars = array();

        // $browseType["param"] holds the value of the $browse_type parameter of this function
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

        $query = $this->db->getTableSelect(DataAccess::SELECT_BROWSE, true);

        $classTable = geoTables::classifieds_table;
        $tTable = geoTables::tags;

        $query->join($tTable, "$tTable.`listing_id` = $classTable.`id`", '*')
            ->where($this->db->quoteInto("$tTable.`tag` = ?", $this->tag, DataAccess::TYPE_STRING_TODB))
            ->where("$classTable.`live`=1", 'live');

        $this->_setBrowsingOptions($query);

        //set up display order
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

        //allow addons to add to or modify the where clause
        geoAddon::triggerUpdate('Browse_tag_generate_query', array('this' => $this, 'query' => $query));

        //how many ads to show on this page?
        $adsToShow = (int)$this->db->get_site_setting('number_of_ads_to_display');
        if (!$adsToShow) {
            $adsToShow = 1;//need to show at least one per page...
        }

        $adsStart = (($this->page_result - 1) * $adsToShow);

        $query->limit($adsStart, $adsToShow);

        //figure out whether to show classifieds, auctions, or both
        $opt_type = $this->browsing_options['type']; //from browsing options module

        $total_returned = (int)$this->db->GetOne('' . $query->getCountQuery());

        $result = $this->db->Execute('' . $query);
        unset($query);//done with that now

        if (!$result) {
            $this->error_message = '<span class="error_message">' . $this->messages[500820] . '</span>';
            return false;
        }

        //total number of pages to make available
        $numPages = max(1, ceil($total_returned / $adsToShow));

        if ($this->page_result > $numPages) {
            //trying to access a page that doesn't exist
            //(could be a search engine crawling an outdated url)
            $this->error_message = "<span class=\"error_message\">" . $this->messages[500820] . "</span>";
            $this->browse_error();
            return false;
        } elseif ($total_returned == 0) {
            //no results found, don't use error, but do cause a 404 is applicable
            self::pageNotFound();
        }

        $tpl_vars['options_qs'] = $this->browsing_options['query_string']; //o= parameter of links that need to preserve browsing module setting

        //get this category's name
        if ($this->site_category) {
            $current_category_name = geoCategory::getName($this->site_category);
            $parent_id = $this->site_category;
        }
        //let the tag accessible to root level of templates
        $view->tag = $this->tag;

        $this->display_browse_result($result);

        if ($adsToShow < $total_returned) {
            if ($browseType['param']) {
                $c = $browseType['param'];
            } elseif ($browseType['category']) {
                $c = $browseType['category'];
            } else {
                $c = 0;
            }
            $c = ($c) ? "&amp;c=$c" : '';
            $url = $this->db->get_site_setting('classifieds_file_name') . "?a=tag&amp;tag=" . geoString::specialChars($this->tag) . $this->browsing_options['query_string'] . "$c&amp;page=";
            $css = "browsing_result_page_links";
            $tpl_vars['pagination'] = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
        }


        //figure out whether to use gallery or not
        $browse_view = $this->getCurrentBrowseView();
        //page 10210
        $sort_dropdown_txt = array (
            0 => $this->messages[501704], //Relevance (AKA no sorting applied)
            1 => $this->messages[501684], //price - cheapest first
            2 => $this->messages[501685], //price - expensive first
            3 => $this->messages[501708], //date - oldest first
            4 => $this->messages[501683], //date - newest first
            5 => $this->messages[501712], //title - a first
            6 => $this->messages[501716], //title - z first
            7 => $this->messages[501720], //location_city - a first
            8 => $this->messages[501724], //location_city - z first
            13 => $this->messages[501728], //zip - 0 first
            14 => $this->messages[501732], //zip - 9 first
            15 => $this->messages[501736], //optional field 1
            16 => $this->messages[501740], //optional field 1 reverse
            17 => $this->messages[501744], //optional field 2
            18 => $this->messages[501748], //optional field 2 reversed
            19 => $this->messages[501752], //optional field 3
            20 => $this->messages[501756], //optional field 3 reversed
            21 => $this->messages[501760], //optional field 4
            22 => $this->messages[501764], //optional field 4 reverse
            23 => $this->messages[501768], //optional field 5
            24 => $this->messages[501772], //optional field 5 reversed
            25 => $this->messages[501776], //optional field 6
            26 => $this->messages[501780], //optional field 6 reversed
            27 => $this->messages[501784], //optional field 7
            28 => $this->messages[501788], //optional field 7 reverse
            29 => $this->messages[501792], //optional field 8
            30 => $this->messages[501796], //optional field 8 reversed
            31 => $this->messages[501800], //optional field 9
            32 => $this->messages[501804], //optional field 9 reversed
            33 => $this->messages[501808], //optional field 10
            34 => $this->messages[501812], //optional field 10 reverse
            45 => $this->messages[501816], //optional field 11
            46 => $this->messages[501820], //optional field 11 reverse
            47 => $this->messages[501824], //optional field 12
            48 => $this->messages[501828], //optional field 12 reversed
            49 => $this->messages[501832], //optional field 13
            50 => $this->messages[501836], //optional field 13 reversed
            51 => $this->messages[501840], //optional field 14
            52 => $this->messages[501844], //optional field 14 reverse
            53 => $this->messages[501848], //optional field 15
            54 => $this->messages[501852], //optional field 15 reversed
            55 => $this->messages[501856], //optional field 16
            56 => $this->messages[501860], //optional field 16 reversed
            57 => $this->messages[501864], //optional field 17
            58 => $this->messages[501868], //optional field 17 reverse
            59 => $this->messages[501872], //optional field 18
            60 => $this->messages[501876], //optional field 18 reversed
            61 => $this->messages[501880], //optional field 19
            62 => $this->messages[501884], //optional field 19 reversed
            63 => $this->messages[501888], //optional field 20
            64 => $this->messages[501892], //optional field 20 reverse
            43 => $this->messages[501896], //business type
            44 => $this->messages[501900], //business type reversed
            69 => $this->messages[501904], //ends (soon)
            70 => $this->messages[501908], //ends (most time left)
            71 => $this->messages[501912], //listings without images first
            72 => $this->messages[501916], //listings with images first
        );
        $sort_dropdown_txt = $this->getSortOptions($this->fields->getDisplayLocationFields('tags'), $sort_dropdown_txt);

        $tpl_vars['browse_mode_txt'] = array (
            'sort_by' => $this->messages[501682],
            'sort' => $sort_dropdown_txt,
            'view' => array(
                'grid' => $this->messages[501693],
                'list' => $this->messages[501686],
                'gallery' => $this->messages[501687],
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
        $tpl_vars['browse_sort_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=tag&amp;tag=" . geoString::specialChars($this->tag) . $this->browsing_options['query_string'] . "&amp;c=";
        $tpl_vars['browse_view_url'] = $tpl_vars['browse_sort_url'] . $this->browse_type . '&amp;browse_view=';

        $view->setBodyTpl('tag.tpl', '', 'browsing')
            ->setBodyVar($tpl_vars);
        $this->display_page();
        return true;
    }

    public function _setBrowsingOptions($query)
    {
        $choice_option = (isset($this->browsing_options['choice'])) ? $this->browsing_options['choice'] : 0;

        switch ($choice_option) {
            //add appropriate conditions to sql for option selected from category_browsing_options module
            //$this->browsing_options['query_string'] holds the value to add to querystring of links on the page to perpetuate the selection
            //$this->browsing_options['type']: 1 for classifieds, 2 for auctions, 0 for both
            case 1:
                //listings ending in next 24 hours
                $query->where(geoTables::classifieds_table . ".ends < " . (geoUtil::time() + 86400));
                $this->browsing_options['query_string'] = "&amp;o=1";
                $this->browsing_options['type'] = 0;
                break;

            case 2:
                //auctions using buy now
                if (geoMaster::is('auctions')) {
                    $query->where(geoTables::classifieds_table . ".buy_now > 0");
                    $this->browsing_options['query_string'] = "&amp;o=2";
                    $this->browsing_options['type'] = 2;
                }
                break;

            case 3:
                //auctions using buy now only
                if (geoMaster::is('auctions')) {
                    $query->where(geoTables::classifieds_table . ".buy_now_only != 0");
                    $this->browsing_options['query_string'] = "&amp;o=3";
                    $this->browsing_options['type'] = 2;
                }
                break;

            case 4:
                //auctions with bids
                if (geoMaster::is('auctions')) {
                    $query->where(geoTables::classifieds_table . ".minimum_bid > " . geoTables::classifieds_table . ".starting_bid");
                    $this->browsing_options['query_string'] = "&amp;o=4";
                    $this->browsing_options['type'] = 2;
                }
                break;

            case 5:
                //auctions without bids
                if (geoMaster::is('auctions')) {
                    $query->where(geoTables::classifieds_table . ".minimum_bid = " . geoTables::classifieds_table . ".starting_bid");
                    $this->browsing_options['query_string'] = "&amp;o=5";
                    $this->browsing_options['type'] = 2;
                }
                break;

            case 6:
                //listings with pictures
                $query->where(geoTables::classifieds_table . ".image > 0");
                $this->browsing_options['query_string'] = "&amp;o=6";
                $this->browsing_options['type'] = 0;
                break;

            case 7:
                //browse by classifieds only (CA-only feature)
                if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                    //nothing to add to sql...
                    $this->browsing_options['query_string'] = "&amp;o=7";
                    $this->browsing_options['type'] = 1;
                }
                break;

            case 8:
                //browse by auctions only (CA-only feature)
                if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                    //nothing to add to sql...
                    $this->browsing_options['query_string'] = "&amp;o=8";
                    $this->browsing_options['type'] = 2;
                }
                break;

            case 9:
                //browse by auctions only (CA-only feature)
                if (geoMaster::is('classifieds') && geoMaster::is('auctions')) {
                    //nothing to add to sql...
                    $this->browsing_options['query_string'] = "&amp;o=9";
                    $this->browsing_options['type'] = 4;
                }
                break;

            default:
                //nothing to add to sql...
                $this->browsing_options['query_string'] = "";
                $this->browsing_options['type'] = 0;
                break;
        }
    }

    public function display_browse_result($browse_result, $auction = 0, $featured = 0)
    {
        $this_copy =& $this;
        $overload = geoAddon::triggerDisplay('overload_Browse_tag_display_browse_result', array ('browse_result' => $browse_result, 'featured' => $featured, 'auction' => $auction, 'this' => $this_copy), geoAddon::OVERLOAD);
        if ($overload !== geoAddon::NO_OVERLOAD) {
            return $overload;
        }

        $tpl_vars = array();

        if ($browse_result->RecordCount() < 1) {
            //no listings in this tag
            $tpl_vars['no_listings'] = $this->messages[500821];
        } else {
            $cfg = $listings = $headers = array();

            $fields = $this->fields->getDisplayLocationFields('tags');

            //set up header view vars
            $headers['css'] = 'browsing_result_table_header';

            $cfg['sort_links'] = true;
            $cfg['browse_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=tag&amp;tag=" . geoString::specialChars($this->tag) . $this->browsing_options['query_string'] . "&amp;c=";
            $cfg['listing_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";

            $cfg['cols']['type'] = (geoMaster::is('classifieds') && geoMaster::is('auctions')) ? true : false;
            $headers['type'] = array(
                'css' => 'item_type_column_header',
                'text' => $this->messages[500868],
                //NO LABEL
            );

            $cfg['cols']['business_type'] = ($fields['business_type']) ? true : false;
            $headers['business_type'] = array(
                'css' => 'business_type_column_header',
                'text' => $this->messages[500822],
                'label' => $this->messages[501920],
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
                'text' => $this->messages[500823],
                //NO LABEL
            );

            $cfg['cols']['title'] = ($fields['title']) ? true : false;
            $headers['title'] = array(
                'css' => 'title_column_header',
                'text' => $this->messages[500824],
                'label' => $this->messages[501924],
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
                'text' =>  $this->messages[500825],
                'label' => $this->messages[501928],
            );

            //Listing tags column
            $cfg['cols']['tags'] = ($fields['tags']) ? true : false;
            $headers['tags'] = array(
                'css' => 'tags_column_header',
                'text' =>  $this->messages[500879],
                'label' => $this->messages[501932],
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
                        'text' => $this->messages[500825 + $i],
                        'label' => $this->messages[501968 + (($i - 1) * 4)],
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
            //optional 1: 501968
            //optional 2: 501972

            $cfg['cols']['address'] = ($fields['address']) ? true : false;
            $headers['address'] = array(
                'css' => 'address_column_header',
                'text' => $this->messages[500846],
                'label' => $this->messages[501936],
            );

            $cfg['cols']['city'] = ($fields['city']) ? true : false;
            $headers['city'] = array(
                'css' => 'city_column_header',
                'text' => $this->messages[500847],
                'label' => $this->messages[501940],
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
                'text' => $this->messages[501627],
                'label' => $this->messages[501944],
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
                'text' => $this->messages[500850],
                'label' => $this->messages[501948],
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
                'text' => $this->messages[500851],
                'label' => $this->messages[501952],
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
                'text' => $this->messages[500852],
                'label' => $this->messages[501956],
            );


            $cfg['cols']['entry_date'] = ((geoMaster::is('classifieds') && $fields['classified_start']) || (!geoMaster::is('classifieds') && $fields['auction_start'])) ? true : false;
            $headers['entry_date'] = array(
                'css' => 'price_column_header',
                'text' => $this->messages[500853],
                'label' => $this->messages[501960],
            );
            if ($this->browse_type == 4) {
                $headers['entry_date']['reorder'] = 3;
            } elseif ($this->browse_type == 3) {
                $headers['entry_date']['reorder'] = 0;
            } else {
                $headers['entry_date']['reorder'] = 4;
            }

            $cfg['cols']['time_left'] = ((geoMaster::is('classifieds') && $fields['classified_time_left']) || (!geoMaster::is('classifieds') && $fields['auction_time_left'])) ? true : false;
            $headers['time_left'] = array(
                'css' => 'price_column_header',
                'text' => $this->messages[500854],
                'label' => $this->messages[501964],
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
            $tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_tag_display_browse_result_addHeader', array('this' => $this, 'tag_fields' => $fields), geoAddon::ARRAY_ARRAY);

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
                    'classified' => $this->messages[500867],
                    'auction' => $this->messages[500866]
                ),
                'business_type' => array(
                    1 => $this->messages[500855],
                    2 => $this->messages[500856],
                ),
                'time_left' => array(
                    'weeks' => $this->messages[500857],
                    'days' => $this->messages[500858],
                    'hours' => $this->messages[500859],
                    'minutes' => $this->messages[500860],
                    'seconds' => $this->messages[500861],
                    'closed' => $this->messages[500862]
                )
            );

            while ($row = $browse_result->FetchRow()) {
                $id = $row['id']; //template expects $listings to be keyed by classified id

                $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

                //use the common geoBrowse class to do all the common heavy lifting
                $listings[$id] = $this->commonBrowseData($row, $text, $featured);

                //css is different enough to not include in the common file
                $listings[$id]['css'] = 'browsing_result_table_body_' . (($count++ % 2 == 0) ? 'even' : 'odd') . (($row['bolding']) ? '_bold' : '');

                //also do addons separately
                $listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_tag_display_browse_result_addRow', array('this' => $this,'show_classifieds' => $row, 'tag_fields' => $fields), geoAddon::ARRAY_ARRAY);
            }
            $tpl_vars['listings'] = $listings;
        }
        geoView::getInstance()->setBodyVar($tpl_vars);
    }
}
