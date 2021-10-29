<?php

//browse_featured_pic_ads.php
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## 
##    7.5.3-36-gea36ae7
##
##################################


class Browse_featured_pic_ads extends geoBrowse
{
    var $subcategory_array = array();
    var $notify_data = array();

//########################################################################

    public function __construct($db, $classified_user_id, $language_id, $category_id = 0, $page = 0, $filter_id = 0, $state_filter = 0, $zip_filter = 0, $zip_filter_distance = 0, $product_configuration = 0)
    {
        if (!geoPC::is_ent()) {
            return;
        }
        if ($category_id) {
            $this->site_category = (int)$category_id;
        } else {
            $this->site_category = 0;
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
            return;
        }

        if (!geoCategory::getBasicInfo($this->site_category)) {
            //invalid category
            $this->browse_error();
        }

        $db = DataAccess::getInstance();
        $this->page_id = 62;
        $this->get_text();
        if (geoPC::is_print() && $this->db->get_site_setting('disableAllBrowsing')) {
            //browsing disabled, do not show browsing contents
            $this->display_page();
            return true;
        }
        $view = geoView::getInstance();

        $query = $this->db->getTableSelect(DataAccess::SELECT_BROWSE, true);

        $classT = geoTables::classifieds_table;

        $query->join(array('img' => geoTables::images_urls_table), "$classT.id = img.classified_id", '*')
            ->where("img.display_order = 1")
            ->where("$classT.featured_ad = 1 OR $classT.featured_ad_2 = 1 OR $classT.featured_ad_3 = 1 OR $classT.featured_ad_4 = 1 OR $classT.featured_ad_5 = 1")
            ->where("$classT.image > 0")
            ->where("$classT.live = 1", 'live');
        if ($this->site_category) {
            $this->whereCategory($query, $this->site_category);
        }

        //allow addons to add to or modify the where clause
        geoAddon::triggerUpdate('Browse_featured_pic_generate_query', array('this' => $this, 'query' => $query));

        $adsPerPage = $this->db->get_site_setting('featured_ad_page_count');

        $query->order("$classT.better_placement DESC, $classT.date DESC")
            ->limit((($this->page_result - 1) * $adsPerPage), $adsPerPage);

        if (geoMaster::is('classifieds')) {
            $classQuery = clone $query;
            $classQuery->where("$classT.item_type = 1");

            $total_returned_ads = $this->db->GetOne('' . $classQuery->getCountQuery());
            $result = $this->db->Execute('' . $classQuery);
            unset($classQuery);
        }
        if (geoMaster::is('auctions')) {
            $auctionQuery = clone $query;
            $auctionQuery->where("$classT.item_type = 2");

            $total_returned_auctions = $this->db->GetOne('' . $auctionQuery->getCountQuery());
            $result_auctions = $this->db->Execute('' . $auctionQuery);
            unset($auctionQuery);
        }

        unset($query);//done with that, so free up memory

        if (!$result && !$result_auctions) {
            $this->error_message = '<span class="error_message">' . urldecode($this->messages[65]) . '</span>' . $this->db->ErrorMsg();
            return false;
        }

        //set up total_returned as the larger of the two, so that pagination goes all the way to the end
        $total_returned = max($total_returned_ads, $total_returned_auctions);

        $numPages = max(1, ceil($total_returned / $adsPerPage));
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
            $current_category_name->CATEGORY_NAME = urldecode($this->messages[870]);
            $parent_id = 0;
        }
        $view->current_category_name = $current_category_name->CATEGORY_NAME;
        $text = array(
            'back_to_normal_link' => $this->messages[875],
            'tree_label' => $this->messages[1367],
            'main_category' => $this->messages[1368],
            'no_subcats' => $this->messages[869]
        );

        $view->category_cache = $category_cache = $this->categoryBrowsing($text, 'featured_pic_');
        $show_auctions = (!geoMaster::is('auctions') || $opt_type == 1 || geoCategory::categoryIsExcludedFromListingType($this->site_category, 'auctions')) ? false : true;
        $show_classifieds = (!geoMaster::is('classifieds') || $opt_type == 2 || $opt_type == 4 || geoCategory::categoryIsExcludedFromListingType($this->site_category, 'classifieds')) ? false : true;

        if ($show_classifieds) {
            //featured ads
            $view->show_classifieds = true;
            if ($result->RecordCount() > 0) {
                $view->classified_result = $this->display_results($result);
            } else {
                $view->no_classifieds = $this->messages[868];
            }
        }
        if ($show_auctions) {
            //featured auctions
            $view->show_auctions = true;
            if ($result_auctions->RecordCount() > 0) {
                $view->auction_result = $this->display_results($result_auctions, 1);
            } else {
                $view->no_auctions = $this->messages[100868];
            }
        }

        if ($adsPerPage < $total_returned) {
            $url = $this->db->get_site_setting('classifieds_url') . "?a=8&amp;c=" . (($this->browse_type) ? $this->browse_type : '0') . "&amp;b=" . $this->site_category . "&page=";
            $css = "browsing_result_page_links";
            $view->pagination = geoPagination::getHTML($numPages, $this->page_result, $url, $css);
        }

        $view->setBodyTpl('featured_pic_ads.tpl', '', 'browsing');
        $this->display_page();
        return true;
    } //end of function browse

//####################################################################################

    function display_results($browse_results, $auction = 0)
    {
        $results = array();
        $results['column_count'] = $this->db->get_site_setting('featured_pic_ad_column_count') ? $this->db->get_site_setting('featured_pic_ad_column_count') : 5;
        $results['column_width'] = floor(100 / $results['column_count']);
        $results['popup'] = $this->configuration_data['popup_while_browsing'];
        $results['popup_width'] = $this->configuration_data['popup_while_browsing_width'];
        $results['popup_height'] = $this->configuration_data['popup_while_browsing_height'];

        while ($show = $browse_results->FetchRow()) {
            $id = $show['id'];
            $results['listings'][$id]['thumbnail'] = geoImage::display_thumbnail($id, 0, 0, 1);
            $results['listings'][$id]['title'] = geoString::fromDB($show['title']);

            $precurrency = $show['precurrency'];
            $postcurrency = $show['postcurrency'];
            if ($show['item_type'] == 1) {
                $displayAmount = geoString::displayPrice($show['price'], $precurrency, $postcurrency);
            } elseif ($show['buy_now_only'] == 1) {
                $displayAmount = geoString::displayPrice($show['buy_now'], $precurrency, $postcurrency);
            } elseif ($show['minimum_bid'] > $show['starting_bid']) {
                $displayAmount = geoString::displayPrice($show['minimum_bid'], $precurrency, $postcurrency);
            } else {
                $displayAmount = geoString::displayPrice($show['starting_bid'], $precurrency, $postcurrency);
            }
            $results['listings'][$id]['price'] = $displayAmount;
        }
        return $results;
    }
}
