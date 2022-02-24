<?php

class Browse_ads extends geoBrowse
{
    public $browsing_options = array();
    private static $_cfg, $_headers, $_fields, $_text;

    public function __construct(
        $classified_user_id = 0,
        $language_id = null,
        $category_id = 0,
        $page = 0,
        $classified_id = 0
    ) {
        $db = $this->db = DataAccess::getInstance();
        $user_id = geoSession::getInstance()->getUserId();
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
        if (isset($limit)) {
            $this->browse_limit = (int)$limit;
        }

        if ($page) {
            $this->page_result = (int)$page;
        } else {
            $this->page_result = 1;
        }


        if (isset($_REQUEST['o']) && is_numeric($_REQUEST['o'])) {
            $this->browsing_options['choice'] = (int)$_REQUEST['o'];
        }

        parent::__construct();
    }


    /**
     * Displays a category's listings
     * @param int $browse_type The numeric category ID
     */
    function browseCategory($browse_type = 0)
    {
        $this->page_id = 3;
        $this->get_text();
        $browseType = array();

        if (geoPC::is_print() && $this->db->get_site_setting('disableAllBrowsing')) {
            //browsing disabled, do not show browsing contents
            $this->display_page();
            return true;
        }

        if ($browse_type) {
            $browseType["param"] = $browse_type;
        }

        $view = geoView::getInstance();
        $tpl_vars = array();

        if (!$this->site_category) {
            if (!$this->browse_main()) {
                $this->error_message = "<span class=\"error_message\">" . urldecode($this->messages[65]) . "</span>";
                return false;
            }
            return true;
        }
        // site_category is just the ID of whatever category the user wants to view

        if (!geoCategory::getBasicInfo($this->site_category)) {
            //user put in an invalid category id
            //(probably an outdated link from a search engine to a deleted category)
            //show the home page
            $this->error_message = "<span class=\"error_message\">" . urldecode($this->messages[65]) . "</span>";
            $this->browse_error();
            return false;
        }

        // $browseType["param"] holds the value of the $browse_type parameter of this function
        // which, more importantly, is the "c" GET parameter. If it is set, it should take precedence over any settings.
        if ($browseType["param"] == 0) {
            if (geoPC::is_ent()) {
                //the default display order can be set on a category by category basis
                //and can be inherited from parent categories

                $categorySettings = geoCategory::getCategoryConfig($this->site_category, true);

                //make note if we should use category-specific settings later
                $useCategorySettings = ($categorySettings['what_fields_to_use'] != 'site');

                if ($useCategorySettings) {
                    //only assign category-specific browse type if using category settings, to save a bit of time otherwise
                    $browseType['category'] = $categorySettings['default_display_order_while_browsing_category'];
                }
            }

            if ($this->db->get_site_setting('default_display_order_while_browsing') != -1) {
                //this setting used if site has been updated from pre-3.1, but admin hasn't set the class/auc-specific settings yet
                $browseType["legacy"] = $this->db->get_site_setting('default_display_order_while_browsing');
            } else {
                $browseType["classified"] = $this->db->get_site_setting('default_classified_order_while_browsing');
                $browseType["auction"] = $this->db->get_site_setting('default_auction_order_while_browsing');
            }
        }
        //Note:  we are getting a clone so that we do not manipulate the original,
        //
        $query = $this->db->getTableSelect(DataAccess::SELECT_BROWSE, true);
        $classTable = geoTables::classifieds_table;
        $listCatTable = geoTables::listing_categories;

        $orderBy = array();
        foreach ($browseType as $key => $value) {
            $orderBy[$key] = $this->getOrderByString($value, $this->site_category);
        }

        $this->whereCategory($query, $this->site_category);

        $query->where("$classTable.`live`=1", 'live');

        //do browsing options
        $this->_setBrowsingOptions($query);

        //allow addons to add to or modify the where clause
        geoAddon::triggerUpdate('Browse_ads_generate_query', array('this' => $this, 'query' => $query));

        //set up display order
        $this->browse_type = (isset($browseType["param"])) ? $browseType["param"] : 0;

        if (isset($browseType["param"]) && strlen(trim($browseType["param"])) > 0) {
            //if function is called with a parameter
            $classifieds_order = $auctions_order = $orderBy["param"];
        } elseif (isset($orderBy["category"]) && $orderBy["category"] && $useCategorySettings && geoPC::is_ent()) {
            //if we're supposed to be following a specific category's settings
            $classifieds_order = $auctions_order = $orderBy["category"];
        } elseif (isset($orderBy["legacy"]) && $orderBy["legacy"]) {
            //if site admin has not yet run admin > browse form, we are in legacy display mode
            $classifieds_order = $auctions_order = $orderBy["legacy"];
        } else {
            //follow site defaults
            $classifieds_order = $orderBy["classified"];
            $auctions_order = $orderBy["auction"];
        }

        //how many ads to show on this page?
        $adsToShow = $this->db->get_site_setting('number_of_ads_to_display');
        $query->limit((($this->page_result - 1) * $adsToShow), $adsToShow);

        //set up to grab featured listings
        /*
        if (($this->db->get_site_setting('use_featured_feature')) && ($this->page_result == 1) && ($this->db->get_site_setting('number_of_featured_ads_to_display'))) {
            $show_featured = true;
            $seed = rand();
            $featuredQuery = clone $query;

            $featuredQuery->orWhere("$classTable.featured_ad = 1",'featured_ad')
                ->orWhere("$classTable.featured_ad_2 = 1",'featured_ad')
                ->orWhere("$classTable.featured_ad_3 = 1",'featured_ad')
                ->orWhere("$classTable.featured_ad_4 = 1",'featured_ad')
                ->orWhere("$classTable.featured_ad_5 = 1",'featured_ad')
                ->order("rand($seed)", true)
                ->limit($this->db->get_site_setting('number_of_featured_ads_to_display'));
        } else {
            $show_featured = false;
        }
        */
        //It no longer makes sense to include featured results "above" main results,
        //as it is confusing since the sorting does not apply to featured results...
        $show_featured = false;

        //figure out whether to show classifieds, auctions, or both
        $opt_type = $this->browsing_options['type']; //from browsing options module
        $show_auctions = (!geoMaster::is('auctions') || $opt_type == 1 || geoCategory::categoryIsExcludedFromListingType($this->site_category, 'auctions')) ? false : true;
        $show_classifieds = (!geoMaster::is('classifieds') || $opt_type == 2 || $opt_type == 4 || geoCategory::categoryIsExcludedFromListingType($this->site_category, 'classifieds')) ? false : true;

        if ($show_classifieds) {
            $classQuery = clone $query;
            $classQuery->where("$classTable.item_type = 1")
                ->order($classifieds_order);

            //get the count
            $total_returned_ads = $this->db->GetOne('' . $classQuery->getCountQuery());

            $result = $this->db->Execute('' . $classQuery);

            if ($show_featured) {
                $classFeaturedQuery = clone $featuredQuery;
                $classFeaturedQuery->where("$classTable.item_type = 1");

                $featured_classifieds_result = $this->db->Execute('' . $classFeaturedQuery);
                if ($featured_classifieds_result && $featured_classifieds_result->RecordCount() > 0) {
                    $tpl_vars['show_featured_classifieds'] = true;
                }
                unset($classFeaturedQuery);
            }
            unset($classQuery);//done with classifieds query
        }
        if ($show_auctions) {
            $auctionQuery = clone $query;
            $aWhere = "($classTable.item_type = 2)";

            $auctionQuery->where($aWhere)
                ->order($auctions_order);

            //get the count
            $total_returned_auctions = $this->db->GetOne('' . $auctionQuery->getCountQuery());

            //get the results
            $result_auctions = $this->db->Execute('' . $auctionQuery);

            if ($show_featured) {
                $auctionFeaturedQuery = clone $featuredQuery;
                $auctionFeaturedQuery->where($aWhere);

                $featured_auctions_result = $this->db->Execute('' . $auctionFeaturedQuery);
                if ($featured_auctions_result && $featured_auctions_result->RecordCount() > 0) {
                    $tpl_vars['show_featured_auctions'] = true;
                }
                unset($auctionFeaturedQuery);//done with this query, free up memory
            }
            unset($auctionQuery);//done with auctions query
        }
        unset($query, $featuredQuery); //free up memory from stuff we're done with

        if (!$result && !$result_auctions) {
            $this->error_message = '<span class="error_message">' . $this->messages[65] . '</span>';
            return false;
        }

        if (!$this->db->get_site_setting('display_sub_category_ads') && $this->db->get_site_setting('place_ads_only_in_terminal_categories')) {
            //if ads can only be placed in terminal categories, and upper-level categories don't show ads in child categories
            //check to make sure there aren't any ads errantly in this category, and if there are none, don't display any of the headers having to do
            //with there being ads in this category (since there are no ads to show in this category)
            if ($total_returned_ads == 0) {
                $show_classifieds = false;
            }
            if ($total_returned_auctions == 0) {
                $show_auctions = false;
            }
        }

        //set up total_returned as the larger of the two, so that pagination goes all the way to the end
        $total_returned = max($total_returned_ads, $total_returned_auctions);

        //total number of pages to make available
        $numPages = max(1, ceil($total_returned / $adsToShow));
        if ($this->page_result > $numPages) {
            //trying to access a page that doesn't exist
            //(could be a search engine crawling an outdated url)
            $this->error_message = "<span class=\"error_message\">" . urldecode($this->messages[65]) . "</span>";
            $this->browse_error();
            return false;
        }

        $tpl_vars['options_qs'] = $this->browsing_options['query_string']; //o= parameter of links that need to preserve browsing module setting

        //get this category's name
        if ($this->site_category) {
            $current_category_name = geoCategory::getName($this->site_category);
            $parent_id = $this->site_category;
        }

        $tpl_vars['current_category_name'] = $current_category_name->CATEGORY_NAME;
        $tpl_vars['category_id'] = $this->site_category;

        $tpl_vars['category_cache'] = $this->categoryBrowsing();

        $tpl_vars['featured_links'] = true;

        if ($show_classifieds) {
            $tpl_vars['show_classifieds'] = true;
            if ($show_featured) {
                $tpl_vars['featured_classifieds'] = $this->display_browse_result($featured_classifieds_result, 0, 1);
            }
            $tpl_vars['classified_browse_result'] = $this->display_browse_result($result, 0, 0);
        }
        if ($show_auctions) {
            $tpl_vars['show_auctions'] = true;
            if ($show_featured) {
                $tpl_vars['featured_auctions'] = $this->display_browse_result($featured_auctions_result, 1, 1);
            }
            $tpl_vars['auction_browse_result'] = $this->display_browse_result($result_auctions, 1, 0);
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

        if ($adsToShow < $total_returned) {
            if ($browseType['param']) {
                $c = $browseType['param'];
            } else {
                $c = 0;
            }

            $url = $this->db->get_site_setting('classifieds_file_name') . "?a=5&amp;b=" . $this->site_category . $this->browsing_options['query_string'] . "&amp;c=" . $c . "&amp;page=";
            $css = "browsing_result_page_links";
            $tpl_vars['pagination'] = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
        }

        if ($this->page_result > 1) {
            //see if there is template for 3_secondary
            $tpl = $view->getTemplateAttachment('3_secondary', $this->language_id, $this->site_category, false);
            if (strlen($tpl)) {
                //there is a 3_secondary template to use, use that instead of one
                //assigned for page 3
                $this->page_id = '3_secondary';
            }
        }

        //figure out whether to use gallery or not
        $browse_view = $this->getCurrentBrowseView();
        //page 3 $this->messages[0]
        $sort_dropdown_txt = array (
            0 => $this->messages[501701], //Relevance (AKA no sorting applied)
            1 => $this->messages[501670], //price - cheapest first
            2 => $this->messages[501671], //price - expensive first
            3 => $this->messages[501705], //date - oldest first
            4 => $this->messages[501669], //date - newest first
            5 => $this->messages[501709], //title - a first
            6 => $this->messages[501713], //title - z first
            7 => $this->messages[501717], //location_city - a first
            8 => $this->messages[501721], //location_city - z first
            13 => $this->messages[501725], //zip - 0 first
            14 => $this->messages[501729], //zip - 9 first
            15 => $this->messages[501733], //optional field 1
            16 => $this->messages[501737], //optional field 1 reverse
            17 => $this->messages[501741], //optional field 2
            18 => $this->messages[501745], //optional field 2 reversed
            19 => $this->messages[501749], //optional field 3
            20 => $this->messages[501753], //optional field 3 reversed
            21 => $this->messages[501757], //optional field 4
            22 => $this->messages[501761], //optional field 4 reverse
            23 => $this->messages[501765], //optional field 5
            24 => $this->messages[501769], //optional field 5 reversed
            25 => $this->messages[501773], //optional field 6
            26 => $this->messages[501777], //optional field 6 reversed
            27 => $this->messages[501781], //optional field 7
            28 => $this->messages[501785], //optional field 7 reverse
            29 => $this->messages[501789], //optional field 8
            30 => $this->messages[501793], //optional field 8 reversed
            31 => $this->messages[501797], //optional field 9
            32 => $this->messages[501801], //optional field 9 reversed
            33 => $this->messages[501805], //optional field 10
            34 => $this->messages[501809], //optional field 10 reverse
            45 => $this->messages[501813], //optional field 11
            46 => $this->messages[501817], //optional field 11 reverse
            47 => $this->messages[501821], //optional field 12
            48 => $this->messages[501825], //optional field 12 reversed
            49 => $this->messages[501829], //optional field 13
            50 => $this->messages[501833], //optional field 13 reversed
            51 => $this->messages[501837], //optional field 14
            52 => $this->messages[501841], //optional field 14 reverse
            53 => $this->messages[501845], //optional field 15
            54 => $this->messages[501849], //optional field 15 reversed
            55 => $this->messages[501853], //optional field 16
            56 => $this->messages[501857], //optional field 16 reversed
            57 => $this->messages[501861], //optional field 17
            58 => $this->messages[501865], //optional field 17 reverse
            59 => $this->messages[501869], //optional field 18
            60 => $this->messages[501873], //optional field 18 reversed
            61 => $this->messages[501877], //optional field 19
            62 => $this->messages[501881], //optional field 19 reversed
            63 => $this->messages[501885], //optional field 20
            64 => $this->messages[501889], //optional field 20 reverse
            43 => $this->messages[501893], //business type
            44 => $this->messages[501897], //business type reversed
            69 => $this->messages[501901], //ends (soon)
            70 => $this->messages[501905], //ends (most time left)
            71 => $this->messages[501909], //listings without images first
            72 => $this->messages[501913], //listings with images first
            );
        $sort_dropdown_txt = $this->getSortOptions($this->fields->getDisplayLocationFields('browsing'), $sort_dropdown_txt);

        $tpl_vars['browse_mode_txt'] = array (
            'sort_by' => $this->messages[501668],
            'sort' => $sort_dropdown_txt,
            'view' => array(
                'grid' => $this->messages[501691],
                'list' => $this->messages[501666],
                'gallery' => $this->messages[501667],
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
        $tpl_vars['browse_sort_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=5&amp;b=" . $this->site_category . $this->browsing_options['query_string'] . "&amp;c=";
        $tpl_vars['browse_view_url'] = $tpl_vars['browse_sort_url'] . $this->browse_type . '&amp;browse_view=';

        $view->setBodyTpl('main.tpl', '', 'browsing')
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
        $overload = geoAddon::triggerDisplay('overload_Browse_ads_display_browse_result', array ('browse_result' => $browse_result, 'featured' => $featured, 'auction' => $auction, 'this' => $this_copy), geoAddon::OVERLOAD);
        if ($overload !== geoAddon::NO_OVERLOAD) {
            return $overload;
        }

        $tpl_vars = array();
        if ($browse_result->RecordCount() < 1) {
            //no listings in this category
            if ($auction) {
                $tpl_vars['no_listings'] = $this->messages[100017];
            } else {
                $tpl_vars['no_listings'] = $this->messages[17];
            }
        } else {
            $cfg = $listings = $headers = array();

            $fields = $this->fields->getDisplayLocationFields('browsing');

            //set up header view vars
            $headers['css'] = 'browsing_result_table_header';

            $cfg['sort_links'] = ($featured) ? false : true; //featured displays don't pay attention to sort order, so no need to show the links
            $cfg['browse_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=5&amp;b=" . $this->site_category . $this->browsing_options['query_string'] . "&amp;c=";
            $cfg['listing_url'] = $this->db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=";

            $cfg['cols']['business_type'] = (bool)$fields['business_type'];
            $headers['business_type'] = array(
                'css' => 'business_type_column_header',
                'text' => $this->messages[1262],
                'label' => $this->messages[501917],
            );
            if ($this->browse_type == 43) {
                $headers['business_type']['reorder'] = 44;
            } elseif ($this->browse_type == 44) {
                $headers['business_type']['reorder'] = 0;
            } else {
                $headers['business_type']['reorder'] = 43;
            }

            $cfg['cols']['image'] = (bool)$fields['photo'];
            $headers['image'] = array(
                'css' => 'photo_column_header',
                'text' => $this->messages[23],
                //NO LABEL
            );

            $cfg['cols']['title'] = (bool)$fields['title'];
            $headers['title'] = array(
                'css' => 'title_column_header',
                'text' => $this->messages[19],
                'label' => $this->messages[501921],
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
            $cfg['description_under_title'] = ($fields['description'] && $this->configuration_data['display_ad_description_where']);

            $cfg['cols']['description'] = ($fields['description'] && !$cfg['description_under_title']);
            $headers['description'] = array(
                'css' => 'description_column_header',
                'text' =>  $this->messages[21],
                'label' => $this->messages[501925],
            );

            //Listing tags column
            $cfg['cols']['tags'] = (bool)$fields['tags'];
            $headers['tags'] = array(
                'css' => 'tags_column_header',
                'text' =>  $this->messages[500875],
                'label' => $this->messages[501929],
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
                        'text' => (($i <= 10) ? $this->messages[921 + $i] : $this->messages[1685 + $i]),
                        'label' => $this->messages[501965 + (($i - 1) * 4)],
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

            //optional 1: 501965
            //optional 2: 501969

            $cfg['cols']['address'] = (bool)$fields['address'];
            $headers['address'] = array(
                'css' => 'address_column_header',
                'text' => $this->messages[500167],
                'label' => $this->messages[501933],
            );

            $cfg['cols']['city'] = (bool)$fields['city'];
            $headers['city'] = array(
                'css' => 'city_column_header',
                'text' => $this->messages[1199],
                'label' => $this->messages[501937],
            );
            if ($this->browse_type == 7) {
                $headers['city']['reorder'] = 8;
            } elseif ($this->browse_type == 8) {
                $headers['city']['reorder'] = 0;
            } else {
                $headers['city']['reorder'] = 7;
            }


            $cfg['cols']['location_breadcrumb'] = (bool)$fields['location_breadcrumb'];
            $headers['location_breadcrumb'] = array(
                'css' => 'location_breadcrumb_column_header',
                'text' => $this->messages[501623],
                'label' => $this->messages[501941],
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

            $cfg['cols']['zip'] = (bool)$fields['zip'];
            $headers['zip'] = array(
                'css' => 'zip_column_header',
                'text' => $this->messages[1202],
                'label' => $this->messages[501945],
            );
            if ($this->browse_type == 13) {
                $headers['zip']['reorder'] = 14;
            } elseif ($this->browse_type == 14) {
                $headers['zip']['reorder'] = 0;
            } else {
                $headers['zip']['reorder'] = 13;
            }

            $cfg['cols']['price'] = (bool)$fields['price'];
            $headers['price'] = array(
                'css' => 'price_column_header',
                'text' => $this->messages[27],
                'label' => $this->messages[501949],
            );
            if ($this->browse_type == 1) {
                $headers['price']['reorder'] = 2;
            } elseif ($this->browse_type == 2) {
                $headers['price']['reorder'] = 0;
            } else {
                $headers['price']['reorder'] = 1;
            }


            $cfg['cols']['num_bids'] = ($auction && $fields['num_bids']);
            $headers['num_bids'] = array(
                'css' => 'number_bids_header',
                'text' => $this->messages[103041],
                'label' => $this->messages[501953],
            );


            $cfg['cols']['entry_date'] = ((!$auction && $fields['classified_start']) || ($auction && $fields['auction_start']));
            $headers['entry_date'] = array(
                'css' => 'price_column_header',
                'text' => $this->messages[22],
                'label' => $this->messages[501957],
            );
            if ($this->browse_type == 4) {
                $headers['entry_date']['reorder'] = 3;
            } elseif ($this->browse_type == 3) {
                $headers['entry_date']['reorder'] = 0;
            } else {
                $headers['entry_date']['reorder'] = 4;
            }

            $cfg['cols']['time_left'] = ((!$auction && $fields['classified_time_left']) || ($auction && $fields['auction_time_left']));
            $headers['time_left'] = array(
                'css' => 'price_column_header',
                'text' => $this->messages[103008],
                'label' => $this->messages[501961],
            );
            if ($this->browse_type == 70) {
                $headers['time_left']['reorder'] = 69;
            } elseif ($this->browse_type == 69) {
                $headers['time_left']['reorder'] = 0;
            } else {
                $headers['time_left']['reorder'] = 70;
            }

            $cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL));
            $headers['edit'] = array(
                'css' => 'price_column_header',
                'text' => 'edit',
                //NO LABEL
            );

            $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL));
            $headers['delete'] = array(
                'css' => 'price_column_header',
                'text' => 'delete',
                //NO LABEL
            );

            /**
             * Addon core event:
             * name: Browse_ads_display_browse_result_addHeader
             * vars: array (this => Object) (this is the instance of $this.
             * return: array (class => string (CSS Class), text => string (what should be displayed)
             */
            $tpl_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addHeader', array('this' => $this, 'browse_fields' => $fields, 'auction' => $auction, 'featured' => $featured), geoAddon::ARRAY_ARRAY);

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
                'business_type' => array(
                    1 => $this->messages[1263],
                    2 => $this->messages[1264],
                ),
                'time_left' => array(
                    'weeks' => $this->messages[103003],
                    'days' => $this->messages[103004],
                    'hours' => $this->messages[103005],
                    'minutes' => $this->messages[103006],
                    'seconds' => $this->messages[103007],
                    'closed' => $this->messages[100051]
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
                $listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addRow', array('this' => $this,'show_classifieds' => $row, 'browse_fields' => $fields, 'auction' => $auction, 'featured' => $featured), geoAddon::ARRAY_ARRAY);
            }
            $tpl_vars['listings'] = $listings;
        }
        return $tpl_vars;
    }




//####################################################################################

    public function main()
    {
        $this->page_id = 2;
        $this->get_text();
        if (!$this->db->get_site_setting('no_home_bodyhtml')) {
            //populate the body_html on home page

            if (strlen(trim($this->messages[29] . $this->messages[30]))) {
                $this->body .= "<table style=\"border:none; width:100%\">\n";
                if (strlen(trim($this->messages[29])) > 0) {
                    $this->body .= "<tr class=\"main_page_title\">\n\t<td style=\"height:20px;\">" . urldecode($this->messages[29]) . "</td>\n</tr>\n";
                }
                if (strlen(trim($this->messages[30])) > 0) {
                    $this->body .= "<tr class=\"main_page_message\">\n\t<td style=\"height:20px;\">" . urldecode($this->messages[30]) . "</td>\n</tr>\n";
                }
                $this->body .= "<tr>\n\t<td >\n\t";
            }
            $this->body .= $this->categoryBrowsing(array(), '', true);

            if (strlen(trim($this->messages[29] . $this->messages[30]))) {
                $this->body .= "</td>\n</tr>\n";
                $this->body .= "</table>\n";
            }
        }
        $this->display_page();
        return true;
    } //end of function main

//####################################################################################

    function browse_main()
    {
        $html = '';
        if ($this->db->get_site_setting('display_category_navigation')) {
            //for simplicity...
            $this->body .= $this->categoryBrowsing();
        }
        return true;
    } //end of function main


//###################################################################################

    function admin_delete_classified($db, $classified_id = 0)
    {
        $classified_id = (int)$classified_id;
        if (!$classified_id) {
            $this->error_message = urldecode($this->messages[81]);
            trigger_error('Listing ID not given');
            return false;
        }

        $sql = "select * from " . $this->classifieds_table . " where id = ?";
        $get_ad_result = $this->db->Execute($sql, array($classified_id));

        if (false === $get_ad_result || !$get_ad_result->RecordCount()) {
            $this->error_message = urldecode($this->messages[81]);
            trigger_error('Could not find listing');
            return false;
        }

        $show = $get_ad_result->FetchRow();
        $category_string = $this->get_category_string($db, $show['category']);

        if ((strlen(trim($show['duration'])) == 0) || is_null($show['duration'])) {
            $show['duration'] = 0;
        }

        $sql = "REPLACE " . $this->classifieds_expired_table . "
			(id,seller,title,date,description,category,
			duration,location_zip,ends,search_text,ad_ended,reason_ad_ended,viewed,
			bolding,better_placement,featured_ad,precurrency,price,postcurrency,
			business_type,optional_field_1,optional_field_2,optional_field_3,optional_field_4,optional_field_5,
			optional_field_6,optional_field_7,optional_field_8,optional_field_9,optional_field_10,
			optional_field_11,optional_field_12,optional_field_13,optional_field_14,optional_field_15,
			optional_field_16,optional_field_17,optional_field_18,optional_field_19,optional_field_20,phone,phone2,fax,email,auction_type,
			final_fee,final_price,item_type)
			VALUES
			(" . $show['id'] . ",
			\"" . $show['seller'] . "\",
			\"" . $show['title'] . "\",
			\"" . $show['date'] . "\",
			\"" . $show['description'] . "\",
			\"" . $category_string . "\",
			" . $show['duration'] . ",
			\"" . $show['location_zip'] . "\",
			\"" . $show['ends'] . "\",
			\"" . urlencode($show['search_text']) . "\",
			" . geoUtil::time() . ",
			\"expired\",
			" . $show['viewed'] . ",
			\"" . $show['bolding'] . "\",
			\"" . $show['better_placement'] . "\",
			\"" . $show['featured_ad'] . "\",
			\"" . $show['precurrency'] . "\",
			\"" . $show['price'] . "\",
			\"" . $show['postcurrency'] . "\",
			\"" . $show['business_type'] . "\",
			\"" . $show['optional_field_1'] . "\",
			\"" . $show['optional_field_2'] . "\",
			\"" . $show['optional_field_3'] . "\",
			\"" . $show['optional_field_4'] . "\",
			\"" . $show['optional_field_5'] . "\",
			\"" . $show['optional_field_6'] . "\",
			\"" . $show['optional_field_7'] . "\",
			\"" . $show['optional_field_8'] . "\",
			\"" . $show['optional_field_9'] . "\",
			\"" . $show['optional_field_10'] . "\",
			\"" . $show['optional_field_11'] . "\",
			\"" . $show['optional_field_12'] . "\",
			\"" . $show['optional_field_13'] . "\",
			\"" . $show['optional_field_14'] . "\",
			\"" . $show['optional_field_15'] . "\",
			\"" . $show['optional_field_16'] . "\",
			\"" . $show['optional_field_17'] . "\",
			\"" . $show['optional_field_18'] . "\",
			\"" . $show['optional_field_19'] . "\",
			\"" . $show['optional_field_20'] . "\",
			\"" . $show['phone'] . "\",
			\"" . $show['phone2'] . "\",
			\"" . $show['fax'] . "\",
			\"" . $show['email'] . "\",
			\"" . $show['auction_type'] . "\",
			\"" . $show['final_fee'] . "\",
			\"" . $show['final_price'] . "\",
			\"" . $show["item_type"] . "\")";

        if (false === $this->db->Execute($sql)) {
            $this->error_message = urldecode($this->messages[81]);
            trigger_error('Could not move listing');
            return false;
        }

        $listing = geoListing::getListing($show['id']);
        $category = $listing->category;
        unset($listing);
        $delete_ad_result = geoListing::remove($show['id']);

        if (!$delete_ad_result) {
            $this->error_message = urldecode($this->messages[81]);
            trigger_error('Could not listing for deletion');
            return false;
        }
        geoCategory::updateListingCount($category);
        header("Location: " . $this->db->get_site_setting('classifieds_url') . "?a=5&amp;b=" . $category);
        return true;
    } //end of function admin_delete_classified
}
