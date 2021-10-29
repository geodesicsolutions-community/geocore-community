<?php

//addons/storefront/pages.php
/**************************************************************************
Addon Created by Geodesic Solutions, LLC
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/

##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    17.10.0-12-g5eb40eb
##
##################################

# Storefront Addon

class addon_storefront_pages extends addon_storefront_info
{
    private $_storeId,$storeID, $_storeTemplateId, $_categories, $_pages, $_pageId, $_listingId, $_homelinkTxt;

    public function home()
    {
        $store_id = $this->storeID = $this->init();
        $db = true;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $util = geoAddon::getUtil('storefront');

        $user_has_storefront = $util->userHasCurrentSubscription($store_id);
        if ($user_has_storefront == 0) {
            $url = geoFilter::getBaseHref() . $db->get_site_setting('classifieds_file_name');
            if ($store_id == geoSession::getInstance()->getUserId()) {
                $url .= "?a=cart&action=new&main_type=storefront_subscription&storefront_need_sub=1";
            } else {
                $url .= "?a=6&b=$store_id";
            }
            header("Location: $url");
        }

        $on_hold = geoUser::getData($store_id, 'storefront_on_hold');
        if ($on_hold == 1 && $store_id != geoSession::getInstance()->getUserId()) {
            //user has turned store off and this is not the store owner
            //redirect to seller's other listings
            $url = geoFilter::getBaseHref() . $db->get_site_setting('classifieds_file_name') . '?a=6&b=' . $store_id;
            header("Location: " . $url);
        }

        $util->setStoreId($store_id);

        $this->processTraffic($store_id);

        $this->update();


        if (!$this->_initView($store_id)) {
            return;
        }

        $view = geoView::getInstance();

        $view->storefront_text = geoAddon::getText($this->auth_tag, $this->name);

        $view->is_owner = ($store_id == geoSession::getInstance()->getUserId()) ? true : false;

        $view->owner_data = geoUser::getUser($store_id)->toArray();

        //figure out what to display on the main page.
        if ($this->_pageId) {
            //displaying a page
            $view->addBody(geoString::fromDB($this->_pages[$this->_pageId]['body']));
        } elseif ($this->_listingId) {
            //display specific listing

            if (!geoSession::getInstance()->getUserId() && $db->get_site_setting('subscription_to_view_or_bid_ads')) {
                //viewer is not logged in, but must be to view listings
                //this bit is normally taken care of by browse_display_ad.php,
                //but due to some weirdness with the way storefront template assignments work,
                //short-circuit that and just redirect to the login page now.
                header('Location: ' . $db->get_site_setting('classifieds_url') . '?a=10');
                include_once GEO_BASE_DIR . 'app_bottom.php';
                exit();
            }

            $tree = '<nav class="breadcrumb">';
            $tree .= $this->_homelink;

            $listing = geoListing::getListing($this->_listingId);

            if ($listing && $listing->storefront_category) {
                $tree .= "<a href='{$this->_categories[$listing->storefront_category]['url']}'>{$this->_categories[$listing->storefront_category]['category_name']}</a>";
            }
            $tree .= '</nav>';
            $view->category_tree = $tree;

            //find other ads by this seller in this category
            //and adjust the next/previous links accordingly
            //(do it here instead of using LockSetVarNewOnly because we want the text from the main page)
            $share_fees = geoAddon::getUtil('share_fees');
            if (($share_fees) && ($share_fees->active) && ($share_fees->store_category_display)) {
                //select listings that are in this storefront category and from any user
                //since the home page select all from the classifieds table that include a storefront category from the current store
                $table = geoAddon::getUtil('storefront')->tables();
                $category_in_statement = $share_fees->getStoreCategoryInStatement($store_id, $table->categories);

                $sql = "select id from " . geoTables::classifieds_table . " where live = 1 " . $category_in_statement . " order by id ASC";
                $result = $db->Execute($sql, array());
            } else {
                //select listings from the storefront owner only
                $sql = "select id from " . geoTables::classifieds_table . " where storefront_category = ? and live = 1 and seller = ? order by id ASC";
                $result = $db->Execute($sql, array($listing->storefront_category, $store_id));
            }
            $catListings = array();
            if ($result && $result->RecordCount() > 0) {
                $i = 0;
                while ($line = $result->FetchRow()) {
                    $catListings[$i++] = $line['id'];
                }
                foreach ($catListings as $key => $id) {
                    if ($id == $this->_listingId) {
                        $previous_link = ($catListings[$key - 1]) ? $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;listing=' . $catListings[$key - 1] : '';
                        $next_link = ($catListings[$key + 1]) ? $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;listing=' . $catListings[$key + 1] : '';
                        break;
                    }
                }
            }
            $msgs = $db->get_text(true, 1);
            $view->previous_ad_link = ($previous_link) ? '<a href="' . $previous_link . '" class="button">' . $msgs[787] . '</a>' : '';
            $view->next_ad_link = ($next_link) ? '<a href="' . $next_link . '" class="button">' . $msgs[786] . '</a>' : '';

            require_once CLASSES_DIR . 'browse_display_ad.php';
            $browse = new Display_ad();
            $view->getListingVarsOnly = true; //tell browse_display_ad.php to skip doing template stuff, 'cause we're gonna do it separately here
            $view->lockSetVarNewOnly();//make it so that the category tree can't be over-written

            //Use new Smarty templates, find which template is assigned to this page

            $view->setLanguage($db->getLanguage());
            $view->setCategory($listing->category);

            //also set the old-school category variable, then force it to re-get Fields
            $browse->site_category = $listing->category;
            $browse->get_configuration_data(); //do this to make sure the Storefront listing display has the correct category Fields to Use set

            $page_id = 'addons/storefront/';
            $page_id .= ($listing->item_type == 1) ? 'classifieds_details_sub_template' : 'auctions_details_sub_template';
            $tpl_file = $view->getTemplateAttachment($page_id);
            $tpl_vars['user_data'] = geoUser::getUser($store_id)->toArray();

            //get site class to use in modules
            $view->setPage($browse);

            $view->loadModules($page_id);

            $view->setBodyTpl($tpl_file);

            $browse->display_classified($this->_listingId, false, false, false);
            $view->unLockSetVarNewOnly(); //un-lock to prevent any lasting damage..
        } else {
            //display listing list
            $this->_displayListings();
        }

        //Set the category ID to be the template selected
        if ($this->_storeTemplateId) {
            $view->setCategory($this->_storeTemplateId);
            //need to set category ID in site class as well or it over-writes what we set
            $site = Singleton::getInstance('geoSite');
            $site->site_category = $this->_storeTemplateId;
        }
    }


    function processTraffic($store_id)
    {
        if (!$store_id) {
            return false;
        }

        $db = DataAccess::getInstance();
        $util = geoAddon::getUtil('storefront');
        $tables = $util->tables();
        //unix time stamp of current date
        $currentDate =  $util->timeToDate(geoUtil::time());

        $sql = "INSERT INTO $tables->traffic_cache SET
		owner=?,
		ip=?,
		time=?";
        $r = $db->Execute($sql, array($store_id,getenv('REMOTE_ADDR'),geoUtil::time()));
        if ($r === false) {
            //die($db->ErrorMsg()."<br />");
        }
        if (geoUser::getData($store_id, 'storefront_traffic_processed_at') >= $currentDate) {
            return false;
        }

        $sql = "SELECT * FROM $tables->traffic_cache WHERE `owner`=? AND `time` < $currentDate";
        $all = $db->GetAll($sql, array($store_id));

        $ips = $days = array();

        foreach ($all as $row) {
            $day = $util->timeToDate($row['time']);
            if (!isset($days[$day])) {
                $days[$day] = 0;
            }
            $days[$day] ++;
            $ips[$day][$row['ip']] = $row['ip'];
        }

        foreach ($days as $day => $tvisits) {
            $uvisits = count($ips[$day]);
            $sql = "INSERT INTO $tables->traffic SET
				owner=?, 
				time=?, 
				uvisits=?,
				tvisits=?";
            $r = $db->Execute($sql, array($store_id,$day,$uvisits,$tvisits));
        }

        $sql = "DELETE FROM $tables->traffic_cache
		WHERE time < $currentDate AND 
		owner = " . $store_id;
        $result = $db->Execute($sql);
        if (!$result) {
            return false;
        }

        $user = geoUser::getUser($store_id);
        if ($user) {
            $user->storefront_traffic_processed_at = time();
        }

        return true;
    }

    /**
     * rolls back timestamps to midnight
     *
     * @param integer $time
     * @return integer
     */
    function timeToDate($time)
    {
        return trim(mktime(0, 0, 0, date("n", $time), date("j", $time), date("y", $time)));
    }

    /**
     * gets storefront subscribers
     *
     * @param object $db ADODB database object
     * @return array email addresses
     */
    function getSubscribers()
    {
        $db = DataAccess::getInstance();
        $util = geoAddon::getUtil('storefront');
        $tables = $util->tables();
        $sql = "SELECT * FROM " . $tables->users . "
		WHERE store_id = " . $util->isOwner() . "";
        $result = $db->Execute($sql);
        if ($result === false) {
            die('Error:' . $db->ErrorMsg());
        }

        $storefrontSubscribers = array();
        while ($emailAddress = $result->FetchRow()) {
            array_push($storefrontSubscribers, $emailAddress["user_email"]);
        }

        return $storefrontSubscribers;
    }


    function init()
    {
        //Do NOT intval the store, it can be a username.
        $storeId = isset($_GET['store']) ? trim($_GET['store']) : 0;

        if (!$storeId) {
            //no store id given, so nothing to do here
            return;
        }

        $util = geoAddon::getUtil('storefront');
        $storeId = $util->storeIdFromString($storeId);
        if (!$storeId) {
            //no ID number found
            $util->exitStore();
            return false;
        }
        $util->addStoreDataIfNeeded($storeId);
        return $storeId;
    }

    function logo()
    {
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();
        $tables = $util->tables();

        static $logo;
        if (!is_object($logo)) {
            $logo = new stdClass();
        }

        $sql = "SELECT logo,logo_width width,logo_height height, logo_list_width, logo_list_height FROM $tables->user_settings WHERE owner=?";
        $r = $db->getrow($sql, array($util->getStoreId()));
        $logo->logo = $r['logo'];
        $logo->htmlSize = "style='width:{$r['width']}px; max-height:{$r['height']}px; max-width: 100%;'";
        $logo->width = $r['width'];
        $logo->height = $r['height'];
        $logo->list_width = $r['logo_list_width'];
        $logo->list_height = $r['logo_list_height'];

        return $logo;
    }


    function control_panel()
    {
        $user_id = geoSession::getInstance()->getUserId();
        if (!$user_id) {
            //user not logged in
            //TODO: force auth page
            return false;
        }
        //make sure this user has a current subscription
        $util = geoAddon::getUtil('storefront');
        if ($util->userHasCurrentSubscription($user_id) == 0) {
            return false;
        }

        //if this is a "free_storefront," make sure its data is initialized
        $util->addStoreDataIfNeeded($user_id);

        //let templates know what {$storefront_id} is in control panel
        $view = geoView::getInstance();
        $view->storefront_id = $user_id;

        require_once('control_panel.php');
        $cp = new geoStoreCP();
        $action = ($_GET['action'] === 'update') ? 'update' : 'display';

        $validActions = array('pages','customize','newsletter','main');
        $action_type = (in_array($_GET['action_type'], $validActions)) ? $_GET['action_type'] : 'main';

        $function = $action . '_' . $action_type;

        $data = ($_POST['data']) ? $_POST['data'] : null;



        $result = $cp->$function($data);

        if ($function === 'update_main' && isset($data['fromPage'])) {
            //special case, just updated main on/off switch
            //return to whatever page we were on before
            $display_function = "display_" . $data['fromPage'];
        } else {
            $display_function = "display_" . $action_type;
        }
        if ($action === 'update') {
            if ($result === false) {
                //failed to update
                $cp->$display_function(false);
            } else {
                //update OK
                $cp->$display_function(true);
            }
        }

        return '';
    }

    /**
     * Initializes the view and assigns all the stuff to it that is needed
     *
     */
    private function _initView($store_id)
    {
        $view = geoView::getInstance();
        $db = DataAccess::getInstance();
        $util = geoAddon::getUtil('storefront');
        $tables = $util->tables();

        //set up settings
        $setting = geoAddon::getRegistry('storefront');
        if ($setting) {
            $view->storefront = $setting->toArray();
        }

        $sql = "SELECT * FROM $tables->user WHERE store_id=?";
        $r = $db->Getrow($sql, $store_id);

        //figure out what storefront template to use
        $user = geoUser::getUser($store_id);
        if (!is_object($user)) {
            $util->exitStore();
            return false;
        }
        //let templates know $storefront_id
        $this->_storeId = $view->storefront_id = $store_id;

        //get template ID to use, in case it has changed on this pageload
        $this->_storeTemplateId = $user->storefront_template_id;

        $tables = $util->tables();
        //get the storefront categories
        $sql = "SELECT `category_id`, `category_name` FROM " . $tables->categories . " WHERE `owner` = ? AND `parent` = 0 ORDER BY `display_order`";
        $categories = $db->GetAll($sql, array($store_id));

        $sql = "SELECT `category_id`, `category_name` FROM " . $tables->categories . " WHERE `owner` = ? AND `parent` = ? ORDER BY `display_order`";
        $getSubcategories = $db->Prepare($sql);

        //$allCats is a bit of a hack to make the new subcategories work with the old code. make sure category navigation still works if you change anything here
        $cats = $allCats = array();
        foreach ($categories as $cat) {
            $allCats[$cat['category_id']] = $cats[$cat['category_id']] = array(
                'url' => $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;category=' . $cat['category_id'],
                'category_name' => $cat['category_name'],
                'category_id' => $cat['category_id']
            );

            $subs = $db->Execute($getSubcategories, array($store_id, $cat['category_id']));
            foreach ($subs as $sub) {
                $allCats[$sub['category_id']] = $cats[$cat['category_id']]['subcategories'][$sub['category_id']] = array(
                    'url' => $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;category=' . $sub['category_id'],
                    'category_name' => $sub['category_name'],
                    'category_id' => $sub['category_id']
                );
            }
        }
        if (count($cats)) {
            $view->storefront_categories = $cats;
            $this->_categories = $allCats; //$this->_categories needs everything on the same "level"
        }
        $view->storefront_category_count = count($cats);

        //get the storefront pages
        $sql = "SELECT `page_id`, `page_link_text`, `page_name`, `page_body` FROM " . $tables->pages . " WHERE `owner` = ? ORDER BY `display_order`";
        $pages = $db->GetAll($sql, array($store_id));
        $storefront_pages = array();

        foreach ($pages as $page) {
            $storefront_pages[$page['page_id']] = array(
                'url' => $db->get_site_setting('classifieds_file_name') . '?a=ap&amp;addon=storefront&amp;page=home&amp;store=' . $store_id . '&amp;p=' . $page['page_id'],
                'link_text' => $page['page_link_text'],
                'page_id' => $page['page_id'],
                'name' => $page['page_name'],
                'body' => $page['page_body']
            );
        }
        if (count($storefront_pages)) {
            $view->storefront_pages = $this->_pages = $storefront_pages;
            if (isset($_GET['p']) && $_GET['p'] && isset($storefront_pages[$_GET['p']])) {
                $view->storefront_page = $storefront_pages[$_GET['p']];
                $this->_pageId = $storefront_pages[$_GET['p']]['page_id'];
            } elseif ($_GET['p'] == 'home' || $_GET['category'] || $_GET['listing']) {
                //looking for a specific page, so don't use default page
            } elseif ($util->default_page) {
                //show the owner's choice of default Page
                $this->_pageId = $util->default_page;
            }
        }
        $listingId = intval((isset($_GET['listing']) && $_GET['listing']) ? $_GET['listing'] : 0);
        if ($listingId) {
            //verify
            $listing = geoListing::getListing($listingId);
            if (!is_object($listing)) {
                $listingId = 0;
            }
        }
        $this->_listingId = $listingId;

        $view->storefront_messages = geoAddon::getInstance()->getText('geo_addons', 'storefront');


        $logo = $this->logo();

        //replace storefront logo
        $view->storefront_logo = '';
        if ($logo->logo) {
            $view->storefront_logo = "<img src='addons/storefront/images/{$logo->logo}' id='logo' alt='{$util->storefront_name}' {$logo->htmlSize}/>";

            $view->logo_width = $logo->width;
            $view->logo_height = $logo->height;
            $view->logo_list_width = $logo->list_width;
            $view->logo_list_height = $logo->list_height;
            $view->logo2 = "<img src='addons/storefront/images/{$logo->logo}' id='logo' alt='{$util->storefront_name}' style='width:50px'/>";
        } else {
            //no logo, so use default logo
            $view->storefront_logo = '<img src="addons/storefront/images/addon_storefront_logo.gif" alt="Your logo here!" />';
        }

        $view->storefront_name = ($util->storefront_name) ? $util->storefront_name : $user->username;

        //replace storefront welcome note
        $view->storefront_welcome_note = $util->user_welcome_message;

        $view->home_link = ($util->user_home_link) ? $util->user_home_link : $user->username;
        $view->storefront_home_url = $db->get_site_setting('classifieds_url') . "?a=ap&amp;addon=storefront&amp;page=home&amp;store=$store_id&amp;p=home";
        $view->storefront_homelink = $this->_homelink = "<a href='" . $view->storefront_home_url . "'>{$view->home_link}</a>";

        //storefront e-mail added
        $view->storefront_email_added = (isset($_COOKIE["emailAdded_" . $store_id]) && $_COOKIE["emailAdded_" . $store_id]) ? true : false;

        $view->display_newsletter = $util->display_newsletter;

        //{storefront_manager} is a special tag
        $tpl = $view->getTemplateObject();
        $tpl->registerPlugin('function', 'storefront_manager', array('addon_storefront_util','displayStorefrontManager'));

        //need to assign to storefront manager old-school slow way, since using old DB-based templates
        $tpl = new geoTemplate('addon', 'storefront');
        $tpl->assign($view->getAllAssignedVars());

        #die(print_r($manager,1));
        $view->storefront_manager = ($util->isOwner()) ? $tpl->fetch('manager.tpl') : '';

        return true;
    }

    public function list_stores()
    {
        $reg = geoAddon::getRegistry('storefront');
        $stores_per_page = $reg->get('list_max_stores', 25);

        $page_num =  ( isset($_GET['p']) ? intval($_GET['p']) : 1);
        $db = DataAccess::getInstance();

        $tpl_vars = array();
        $tpl_vars['text'] = $text = geoAddon::getText('geo_addons', 'storefront');
        $util = geoAddon::getUtil('storefront');

        $table = $util->tables();

        $query = new geoTableSelect();

        $subTable = $table->subscriptions;
        $userTable = geoTables::userdata_table;
        $userRegionsTable = geoTables::user_regions;
        $userUserGroupsPricePlans = geoTables::user_groups_price_plans_table;

        $price_plans_with_free_storefronts = $util->getPricePlansWithFreeStorefronts();
        if (count($price_plans_with_free_storefronts) > 0) {
            //put value within in statement for query
            $in_statement = "(";
            foreach ($price_plans_with_free_storefronts as $id => $value) {
                if ($id == 0) {
                    $in_statement .= $value;
                } else {
                    $in_statement .= "," . $value;
                }
            }
            $in_statement .= ")";

            $query->from($userUserGroupsPricePlans, array('`user_id`' => '`id`'))
            ->join($userTable, "$userUserGroupsPricePlans.`id` = $userTable.`id`", array('`username`'))
            ->join($subTable, "$userTable.`id` = $subTable.`user_id`", array(), "left")
            ->where("($subTable.`expiration` > " . geoUtil::time() . " OR ($userUserGroupsPricePlans.`price_plan_id` in " . $in_statement . " OR $userUserGroupsPricePlans.`auction_price_plan_id` in " . $in_statement . " ))")
            ->where("$userTable.`storefront_on_hold`=0")
            ->order("$userTable.username")
            ->limit(($page_num - 1) * $stores_per_page, $stores_per_page);
        } else {
            $query->from($subTable, array('`user_id`'))
            ->join($userTable, "$subTable.`user_id` = $userTable.`id`", array('`username`'))
            ->where("$subTable.`expiration` > " . geoUtil::time())
            ->where("$userTable.`storefront_on_hold`=0")
            ->order("$userTable.username")
            ->limit(($page_num - 1) * $stores_per_page, $stores_per_page);
        }

        //echo $query." is the list stores query<br>\n";

        //figure out what level 'state' is on
        $regionOverrides = geoRegion::getLevelsForOverrides();
        $stateLevel = $regionOverrides['state'] ? $regionOverrides['state'] : false;

        if ($_POST['storefront_state_filter']) {
            $filter_state = $_POST['storefront_state_filter'];
            if ($filter_state == -1) {
                //unset cookie
                setcookie('storefront_state_filter', '-', 1, '/'); //1 to expire cookie now
                $filter_state = false;
            } else {
                setcookie('storefront_state_filter', $filter_state, 0, '/'); //0 to expire at end of browser session
            }
        } elseif ($_COOKIE['storefront_state_filter']) {
            //filter set in cookie from a previous pageload
            $filter_state = $_COOKIE['filter_state'];
        }
        if ($filter_state && $stateLevel) {
            $query->join($userRegionsTable, "$subTable.`user_id` = $userRegionsTable.`user`")
                ->where("$userRegionsTable.`level` = " . $stateLevel)
                ->where($db->quoteInto("$userRegionsTable.`region` = ?", $filter_state));
        }
        $tpl_vars['filter_state'] = $filter_state;

        if ($reg->geonav_filter_storefronts && geoView::getInstance()->geographic_navigation_region) {
            //figure out how to filter...
            $geo = geoAddon::getUtil('geographic_navigation');
            $geo->applyFilterUser($query, geoView::getInstance()->geographic_navigation_region);
        }

        //per 3rd-party dev request, let addons play with this query
        $query = geoAddon::triggerDisplay('filter_storefront_list_stores_query', $query, geoAddon::FILTER);
        $rs = $db->Execute('' . $query);

        $total_row = (int)$db->GetOne('' . $query->getCountQuery());

        //pull a list of all the "states" of active stores for the "filter" dropdown
        //NOTE: pay attention to the join order here, as it needs to include "price plan free" users who haven't visited their storefront at all yet
        //that is to say: LEFT JOIN the subscription tables at the end so that the rest of the query still operates for users where that table is missing a row
        $sql = "SELECT DISTINCT r.region
				FROM " . geoTables::userdata_table . " as user				
				INNER JOIN " . geoTables::user_regions . " as r ON user.id = r.user AND r.level = " . $stateLevel .
                ($price_plans_with_free_storefronts ? " LEFT JOIN " . geoTables::user_groups_price_plans_table . " as ugpp ON user.id = ugpp.id " : '') . "  
				LEFT JOIN " . $table->subscriptions . " as sub ON user.id = sub.user_id
				WHERE " .
                ($price_plans_with_free_storefronts ? "(sub.expiration > " . geoUtil::time() . " OR ugpp.`price_plan_id` in " . $in_statement . " OR ugpp.`auction_price_plan_id` in " . $in_statement . ")" : "sub.expiration > " . geoUtil::time()) .
                " AND user.storefront_on_hold = 0";
        $state_result = $db->Execute($sql);
        $states = array();
        while ($state_result && $line = $state_result->FetchRow()) {
            $states[$line['region']] = geoRegion::getNameForRegion($line['region']);
        }
        $tpl_vars['states'] = $states;

        //normal assign type thingies

        if (!$rs) {
            trigger_error('ERROR STATS SQL: Sql: ' . $sql . ' Error Msg: ' . $db->ErrorMsg() . ' - list stores failed.');
            return 'Storefront List Error.';
        }
        $stores = array();
        while ($user = $rs->FetchRow()) {
            $user_name = $user["username"];
            $user_id = intval($user["user_id"]);

            //make sure data is present for "free_storefronts" users
            $util->addStoreDataIfNeeded($user_id);

            $count_sql = "SELECT count(*) as count FROM " . geoTables::classifieds_table . " WHERE seller = " . $user_id . " AND `live`=1";
            $count_row = $db->GetRow($count_sql);
            $count = $count_row['count'];

            $user_sql = "SELECT username, city, state, zip FROM " . geoTables::userdata_table . " WHERE id = " . $user_id;
            $user_row = $db->GetRow($user_sql);

            $store_sql = "SELECT logo, logo_width, logo_list_width, logo_height, logo_list_height, welcome_message from geodesic_addon_storefront_user_settings where owner = " . $user_id;
            $store_row = $db->GetRow($store_sql);

            //if "list" width/height values aren't set yet for logo, fallback is "normal" values
            $width = ($store_row['logo_list_width']) ? $store_row['logo_list_width'] : $store_row['logo_width'];
            $height = ($store_row['logo_list_height']) ? $store_row['logo_list_height'] : $store_row['logo_height'];
            //if, for some reason, those values exceed admin-configured max, for the admin setting
            if ($reg) {
                $max_width = $reg->max_logo_width;
                $max_height = $reg->max_logo_height;
                $width = ($max_width && ($width > $max_width || !$width)) ? $max_width : $width;
                $height = ($max_height && ($height > $max_height || !$height)) ? $max_height : $height;
            }
            $description_length = $db->get_site_setting('length_of_description') ? $db->get_site_setting('length_of_description') : 20;

            $sql = "SELECT `storefront_name` FROM `geodesic_addon_storefront_user_settings` WHERE `owner` = ?";
            $store_name = $db->GetOne($sql, array($user_id));

            if (!$store_name) {
                //storefront name not set -- default to username
                $store_name = $user_name;
            }

            //get state (and optionally city) from region info
            $state = geoRegion::getStateNameForUser($user_id);
            if ($regionOverrides['city']) {
                $userRegions = geoRegion::getRegionsForUser($user_id);
                $city = geoRegion::getNameForRegion($userRegions[$regionOverrides['city']]);
            } else {
                $city = $user_row['city'];
            }

            $stores[] = array (
                "image" => "<img src='addons/storefront/images/" . (($store_row['logo']) ? $store_row["logo"] : 'addon_storefront_logo.gif') . "' alt='' style=\"width: " . $width . "px; height: " . $height . "px;\" />",
                "title" => $user_row['username'],
                "userid" => $user_id,
                "name" => $store_name,
                "items" => $count,
                "desc" => geoFilter::listingShortenDescription(geoFilter::replaceDisallowedHtml($store_row['welcome_message'], 1), $reg->get('list_description_length', 30)),
                "city" => $city,
                "state" => $state,
                "zip" => $user_row['zip']
            );
        }
        $tpl_vars['stores'] = $stores;

        $switches = array (
            'logo' => $reg->list_show_logo,
            'title' => $reg->list_show_title,
            'num_items' => $reg->list_show_num_items,
            'description' => $reg->list_show_description,
            'city' => $reg->list_show_city,
            'state' => $reg->list_show_state,
            'zip' => $reg->list_show_zip
        );
        $tpl_vars['switches'] = $switches;

        // pagination
        if ($stores_per_page < $total_row) {
            $tpl_vars['show_pagination'] = true;

            $tpl_vars['totalPages'] = $totalPages = ceil($total_row / $stores_per_page);
            $tpl_vars['currentPage'] = $page_num;
            $link = $db->get_site_setting('classifieds_file_name') . "?a=ap&amp;addon=storefront&amp;page=list_stores&amp;p=";

            $css = "";
            $tpl_vars['pagination'] = geoPagination::getHTML($totalPages, $page_num, $link, $css);
            $body .= $tpl_vars['pagination'];
        }
        geoView::getInstance()->setBodyTpl('list_all_stores.tpl', 'storefront')
            ->setBodyVar($tpl_vars);
        return '';
    }


    public function generic()
    {
        $this->home();
    }

    /**
     * receives AJAX request from control panel "pages" settings
     * and passes it on to process function
     *
     */
    public function control_panel_ajax()
    {
        $user_id = geoSession::getInstance()->getUserId();
        if (!$user_id) {
            //user not logged in
            return false;
        }
        //make sure this user has a current subscription
        $util = geoAddon::getUtil('storefront');
        if ($util->userHasCurrentSubscription($user_id) == 0) {
            return false;
        }

        require_once('control_panel.php');
        $cp = new geoStoreCP();

        $cp->doAjax($_POST);
        geoView::getInstance()->setRendered(true);
    }

    public function check_name_ajax()
    {
        //so the view class doesn't try and print anything
        geoView::getInstance()->setRendered(true);

        $user_id = geoSession::getInstance()->getUserId();
        if (!$user_id) {
            //user not logged in
            return false;
        }
        //make sure this user has a current subscription
        $util = geoAddon::getUtil('storefront');
        if ($util->userHasCurrentSubscription($user_id) == 0) {
            //echo 'there';
            return false;
        }

        $userid = geoSession::getInstance()->getUserId();

        $name = trim($_POST['name_to_check']);
        //remove any HTML
        $name = strip_tags(geoString::specialCharsDecode($name));

        if (!$name) {
            //echo 'here';
            return false;
        }

        $db = DataAccess::getInstance();
        //preliminary username check (before cleaning badwords)
        if (is_numeric($name)) {
            //pure-numeric store names won't fly
            exit('INVALID');
        }

        $sql = "select username from geodesic_userdata where username = ? and id <> ?";
        $result = $db->Execute($sql, array($name, $userid));
        if ($result->RecordCount() > 0) {
            //this is someone else's username
            exit('INVALID');
        }

        $site = Singleton::getInstance('geoSite');
        $original = $name;
        $name = $site->check_for_badwords($name);

        //check username again now that badwords are gone, but only if name has changed
        if ($original !== $name) {
            $sql = "select username from geodesic_userdata where username = ? and id <> ?";
            $result = $db->Execute($sql, array($name, $userid));
            if ($result->RecordCount() > 0) {
                //this is someone else's username
                exit('INVALID');
            }
        }

        //now clean for URLs
        $name = preg_replace("/[^a-zA-Z0-9_]+/", ' ', $name); //replace any invalid characters with whitespace
        $name = preg_replace("/\s+/", '-', $name); //replace any whitespace with hyphens

        //check cleaned name against other names already stored in the DB.
        $sql = "select seo_name from geodesic_addon_storefront_user_settings where seo_name = ? AND owner <> ?";
        $result = $db->Execute($sql, array($name, $userid));
        if ($result->RecordCount() > 0) {
            //name already in use
            exit('IN_USE');
        }

        //so far, so good -- allow submission of this name
        exit('OK');
    }


    function getSubscription()
    {
        $db = DataAccess::getInstance();
        $table = geoAddon::getutil("storefront")->tables();
        $sql = "SELECT * FROM $table->subscriptions WHERE user_id ='$this->store_id'";
        $r = $db->getrow($sql);
        if (!$r) {
            return false;
        }
        $expiresAt = $r['expiration'];
        if (geoUtil::time() >= $expiresAt) {
            return false;
        }
        return true;
    }

    private function _displayListings()
    {
        $db = DataAccess::getInstance();
        $site = Singleton::getInstance('geoSite');
        $tables = geoAddon::getUtil('storefront')->tables();
        $reg = geoAddon::getRegistry('storefront');
        $body = '';
        $category_id = intval((isset($_GET['category'])) ? $_GET['category'] : 0);
        if ($category_id && !isset($this->_categories[$category_id])) {
            //invalid category id
            $category_id = 0;
        }
        $listing_id = intval((isset($_GET['listing'])) ? $_GET['listing'] : 0);
        $listing = 0;
        if ($listing_id) {
            $listing = geoListing::getListing($listing_id);
            if (!is_object($listing)) {
                $listing_id = $listing = 0;
            }
        }
        $page = intval((isset($_GET['page_result'])) ? $_GET['page_result'] : 1);
        if ($page <= 0) {
            $page = 1;
        }
        $store_id = intval($this->_storeId);
        if (!$store_id) {
            //can't do anything without a store id.
            return false;
        }
        $tpl_vars = array();
        //TODO: Move these to use storefront text eventually, when done be sure
        //to remove the old page 10003 from system as it isn't used elsewhere
        $msgs = $db->get_text(true, 3) + $db->get_text(true, 10003);


        if (isset($_GET['c']) && $_GET['c'] != 0) {
            $sort_type = intval($_GET['c']);
            $use_default_orders = false;
        } else {
            $auctions_default_order = ($db->get_site_setting('default_auction_order_while_browsing')) ? $db->get_site_setting('default_auction_order_while_browsing') : 0;
            $class_default_order = ($db->get_site_setting('default_classifed_order_while_browsing')) ? $db->get_site_setting('default_classifed_order_while_browsing') : 0;
            $use_default_orders = true;
            $sort_type = 0;
        }
        if ($sort_type < 0) {
            $sort_type = 0;
        }

        $tpl_vars['sort_type'] = $sort_type;

        $query = $db->getTableSelect(DataAccess::SELECT_BROWSE, true);
        $classTable = geoTables::classifieds_table;

            $share_fees = geoAddon::getUtil('share_fees');
        if (($share_fees) && ($share_fees->active) && ($share_fees->store_category_display)) {
            //get categories for this storefront
            $storefront_categories_in_statement = $share_fees->getStoreCategoryInStatement($store_id, $tables->categories);
            if (strlen(trim($storefront_categories_in_statement)) == 0) {
                //backup is to find all the users attached to this user
                $users_attached_array = $share_fees->getUsersAttachedToUser($store_id);
                if (count($users_attached_array) > 0) {
                    $first_one = 1;
                    $user_in_statement = " IN (";
                    foreach ($users_attached_array as $attached_user) {
                        if ($first_one == 1) {
                            $user_in_statement .= $attached_user;
                        } else {
                            $user_in_statement .= "," . $attached_user;
                        }
                        $first_one = 0;
                    }
                    $user_in_statement .= ")";
                    $query->where("$classTable.`live`=1", 'live')
                    ->where("$classTable.`seller` " . $user_in_statement)
                    ->where("$classTable.`ends` > " . geoUtil::time() . " OR $classTable.`ends` = 0");
                } else {
                    //worst case where no users are attached is to just display this stores listings
                    $query->where("$classTable.`live`=1", 'live')
                    ->where("$classTable.`seller`=$store_id")
                    ->where("$classTable.`ends` > " . geoUtil::time() . " OR $classTable.`ends` = 0");
                }
            } else {
                //find all the users attached to this user
                $query->where("$classTable.`live`=1", 'live')
                ->where("$classTable.`storefront_category` " . $storefront_categories_in_statement)
                ->where("$classTable.`ends` > " . geoUtil::time() . " OR $classTable.`ends` = 0");
            }
        } else {
            $query->where("$classTable.`live`=1", 'live')
                ->where("$classTable.`seller`=$store_id")
                ->where("$classTable.`ends` > " . geoUtil::time() . " OR $classTable.`ends` = 0");
        }

        if ($category_id) {
            //category_id is a cleaned int
            $query->where("$classTable.`storefront_category` IN (SELECT `category_id` FROM {$tables->categories} WHERE `category_id` = $category_id OR `parent` = $category_id)");
        }

        $results_per_page = $db->get_site_setting('number_of_ads_to_display');
        $limit = ($page - 1) * $results_per_page;
        $query->limit($limit, $results_per_page);
        $listingsA = array();

        if (geoMaster::is('classifieds')) {
            $classQuery = clone $query;

            $classQuery->where("$classTable.`item_type`=1");
            if ($use_default_orders) {
                $this->_getOrderByClause($class_default_order, $classQuery);
            } else {
                //default ordering
                $this->_getOrderByClause($sort_type, $classQuery);
            }
            $listingsA[1] = $db->GetAll('' . $classQuery);

            $tpl_vars['display_classifieds'] = 1;
        } else {
            $tpl_vars['display_classifieds'] = 0;
        }
        if (geoMaster::is('auctions')) {
            $auctionQuery = clone $query;

            $auctionQuery->where("$classTable.`item_type`=2");
            if ($use_default_orders) {
                $this->_getOrderByClause($auctions_default_order, $auctionQuery);
            } else {
                //default ordering
                $this->_getOrderByClause($sort_type, $auctionQuery);
            }

            $listingsA[2] = $db->GetAll('' . $auctionQuery);

            $tpl_vars['display_auctions'] = 1;
        } else {
            $tpl_vars['display_auctions'] = 0;
        }
        //we're done with the query now
        unset($query);

        $countClass = $tpl_vars['total_classifieds'] = $classQuery ? $db->GetOne('' . $classQuery->getCountQuery()) : 0;
        $countAuc = $tpl_vars['total_auctions'] = $auctionQuery ? $db->GetOne('' . $auctionQuery->getCountQuery()) : 0;
        $count = $countClass + $countAuc;

        //cleanup query objects, now that the counting is done.
        if (isset($classQuery)) {
            unset($classQuery);
        }
        if (isset($auctionQuery)) {
            unset($auctionQuery);
        }

        $countMax = $tpl_vars['total_all'] = (($countClass > $countAuc) ? $countClass : $countAuc);
        $tpl_vars['page_num'] = $page;

        if ($countMax > $db->get_site_setting('number_of_ads_to_display')) {
            //set up pagination if all the ads don't fit on one page.
            $totalPages = $tpl_vars['total_pages'] = ceil($countMax / $db->get_site_setting('number_of_ads_to_display'));
            $link = $db->get_site_setting('classifieds_url') . "?a=ap&amp;addon=storefront&amp;page=home&amp;store={$store_id}&amp;category={$category_id}&amp;c={$sort_type}" . (($_GET['p']) ? '&amp;p=' . $_GET['p'] : '') . "&amp;page_result=";
            $pagination = geoPagination::getHTML($totalPages, $page, $link);
            $tpl_vars['pagination'] = $pagination;
        }

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $cfg = $listings = $headers = array();

        $cfg['sort_links'] = true;
        $cfg['browse_url'] = $db->get_site_setting('classifieds_url') . "?a=ap&amp;addon=storefront&amp;page=home&amp;store=$store_id&amp;" . ($category_id ? "category={$category_id}" : "p=home") . '&amp;c=';
        //storefront-specific listing links
        $cfg['listing_url'] = $db->get_site_setting('classifieds_url') . "?a=ap&amp;addon=storefront&amp;page=home&amp;store=$store_id&amp;listing=";


        $cfg['cols']['type'] = ($reg->display_business_type) ? true : false;
        $headers['type'] = array(
            'text' => $msgs[1262], 'label' => $msgs[1262],
        );
        if ($sort_type == 43) {
            $headers['type']['reorder'] = 44;
        } elseif ($sort_type == 44) {
            $headers['type']['reorder'] = 0;
        } else {
            $headers['type']['reorder'] = 43;
        }

        $cfg['cols']['image'] = ($reg->display_photo_icon) ? true : false;
        $headers['image'] = array(
            'text' => $msgs[23], 'label' => $msgs[23],
        );

        $cfg['cols']['title'] = ($reg->display_ad_title) ? true : false;
        $headers['title'] = array(
            'text' => $msgs[19], 'label' => $msgs[19],
        );
        if ($sort_type == 5) {
            $headers['title']['reorder'] = 6;
        } elseif ($sort_type == 6) {
            $headers['title']['reorder'] = 0;
        } else {
            $headers['title']['reorder'] = 5;
        }
        $cfg['description_under_title'] = ($reg->display_ad_description && $reg->display_ad_description_where) ? true : false;

        $cfg['cols']['description'] = ($reg->display_ad_description && !$reg->display_ad_description_where) ? true : false;
        $headers['description'] = array(
            'text' => $msgs[21], 'label' => $msgs[21],
        );

        $text = geoAddon::getText('geo_addons', 'storefront');
        for ($i = 1; $i <= 20; $i++) {
            $setting = "display_optional_field_$i";
            if ($reg->$setting) {
                $cfg['cols']['optionals'][$i] = true;
                $headers['optionals'][$i] = array(
                    'text' => $text["listings_opt_{$i}_column"], 'label' => $text["listings_opt_{$i}_column"]
                );

                $browse1 = ($i <= 10) ? ( 2 * ($i - 1) + 15 ) : ( 2 * ($i - 11) + 45 ) ; //15, 17, 19, ... : 45, 47, 49, ...
                $browse2 = $browse1 + 1;
                if ($sort_type == $browse1) {
                    $headers['optionals'][$i]['reorder'] = $browse2;
                } elseif ($sort_type == $browse2) {
                    $headers['optionals'][$i]['reorder'] = 0;
                } else {
                    $headers['optionals'][$i]['reorder'] = $browse1;
                }
            } else {
                $cfg['cols']['optionals'][$i] = false;
            }
        }

        $cfg['cols']['city'] = ($reg->display_browsing_city_field) ? true : false;
        $headers['city'] = array(
            'text' => $msgs[1199], 'label' => $msgs[1199],
        );
        if ($sort_type == 7) {
            $headers['city']['reorder'] = 8;
        } elseif ($sort_type == 8) {
            $headers['city']['reorder'] = 0;
        } else {
            $headers['city']['reorder'] = 7;
        }

        $cfg['cols']['zip'] = ($reg->display_browsing_zip_field) ? true : false;
        $headers['zip'] = array(
                'text' => $msgs[1202], 'label' => $msgs[1202],
        );
        if ($sort_type == 13) {
            $headers['zip']['reorder'] = 14;
        } elseif ($sort_type == 14) {
            $headers['zip']['reorder'] = 0;
        } else {
            $headers['zip']['reorder'] = 13;
        }


        $enabledRegions = array();
        $maxLocationDepth = 0;
        $low = geoRegion::getLowestLevel();
        if ($reg->display_browsing_country_field && $low >= 1) {
            $enabledRegions[] = 1;
            $maxLocationDepth = 1;
        }
        if ($reg->display_browsing_state_field && $low >= 2) {
            $enabledRegions[] = 2;
            $maxLocationDepth = 2;
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

        $cfg['cols']['price'] = ($reg->display_price) ? true : false;
        $headers['price'] = array(
            'text' => $msgs[27], 'label' => $msgs[27],
        );
        if ($sort_type == 1) {
            $headers['price']['reorder'] = 2;
        } elseif ($sort_type == 2) {
            $headers['price']['reorder'] = 0;
        } else {
            $headers['price']['reorder'] = 1;
        }

        $cfg['cols']['num_bids'] = ($reg->display_number_bids) ? true : false;
        $headers['num_bids'] = array(
                'text' => $msgs[103042], 'label' => $msgs[103042],
        );

        $cfg['cols']['edit'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_edit', true, geoAddon::NOT_NULL)) ? true : false;
        $headers['edit'] = array(
            'text' => 'edit',
            //NO LABEL
        );

        $cfg['cols']['delete'] = (geoSession::getInstance()->getUserID() == 1 || geoAddon::triggerDisplay('auth_listing_delete', true, geoAddon::NOT_NULL)) ? true : false;
        $headers['delete'] = array(
            'text' => 'delete',
            //NO LABEL
        );

        //a couple last-minute config settings before we go ahead and give $cfg to the view class
        $cfg['icons'] = array(
            'sold' => (($msgs[500798]) ? geoTemplate::getUrl('', $msgs[500798]) : ''),
            'buy_now' => (($msgs[500799]) ? geoTemplate::getUrl('', $msgs[500799]) : ''),
            'reserve_met' => (($msgs[500800]) ? geoTemplate::getUrl('', $msgs[500800]) : ''),
            'reserve_not_met' => (($msgs[501665]) ? geoTemplate::getUrl('', $msgs[501665]) : ''),
            'no_reserve' => (($msgs[500802]) ? geoTemplate::getUrl('', $msgs[500802]) : ''),
            'verified' => (($msgs[500952]) ? geoTemplate::getUrl('', $msgs[500952]) : ''),
            'addon_icons' => geoAddon::triggerDisplay('use_listing_icons', null, geoAddon::BOOL_TRUE),
        );

        $cfg['empty'] = '-';

        $i_vars = array(); //internal template vars
        //already in an addon, so not hooking addons
        //$i_vars['addonHeaders'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addHeader', array('this'=>$this, 'browse_fields'=>$fields), geoAddon::ARRAY_ARRAY);


        //now set up all the listing data

        //common text
        $text = array(
            'item_type' => array(
                'classified' => $msgs[200010],
                'auction' => $msgs[200009]
            ),
            'time_left' => array(
                'weeks' => $msgs[3284],
                'days' => $msgs[3285],
                'hours' => $msgs[3286],
                'minutes' => $msgs[3287],
                'seconds' => $msgs[3288],
                'closed' => $msgs[100051]
            )
        );

        $listingData = array();

        foreach ($listingsA as $browse_type => $listingsOfType) {
            //listing-type-specific settings

            if ($browse_type == 1 && count($listingsOfType) == 0) {
                $i_vars['no_listings'] = $msgs[17];
            } elseif ($browse_type == 2 && count($listingsOfType) == 0) {
                $i_vars['no_listings'] = $msgs[100017];
            } else {
                $i_vars['no_listings'] = false;
            }

            $listings = array();
            $count = 0;

            $cfg['cols']['entry_date'] = (($browse_type == 1 && $reg->display_entry_date) || ($browse_type == 2 && $reg->auction_entry_date)) ? true : false;
            $headers['entry_date'] = array(
                    'text' => $msgs[756], 'label' => $msgs[756],
            );
            if ($sort_type == 4) {
                $headers['entry_date']['reorder'] = 3;
            } elseif ($sort_type == 3) {
                $headers['entry_date']['reorder'] = 0;
            } else {
                $headers['entry_date']['reorder'] = 4;
            }

            $cfg['cols']['time_left'] = (($browse_type == 1 && $reg->classified_time_left) || ($browse_type == 2 && $reg->display_time_left)) ? true : false;
            $headers['time_left'] = array(
                    'text' => $msgs[102546], 'label' => $msgs[102546],
            );
            if ($sort_type == 70) {
                $headers['time_left']['reorder'] = 69;
            } elseif ($sort_type == 69) {
                $headers['time_left']['reorder'] = 0;
            } else {
                $headers['time_left']['reorder'] = 70;
            }

            $i_vars['cfg'] = $cfg;
            $i_vars['headers'] = $headers;

            $browse = new geoBrowse();
            $browse->messages = $msgs;
            foreach ($listingsOfType as $row) {
                $id = $row['id']; //template expects $listings to be keyed by classified id

                $row['regionInfo'] = array('maxDepth' => $maxLocationDepth, 'enabledLevels' => $enabledRegions);

                $row['is_storefront'] = true;

                //use the common geoBrowse class to do all the common heavy lifting
                $listings[$id] = $browse->commonBrowseData($row, $text);

                //css is different enough to not include in the common file
                $listings[$id]['css'] = 'row_' . (($count++ % 2 == 0) ? 'even' : 'odd') ;

                //probably don't need to hook to storefront links from within a storefront...
                //$listings[$id]['addonData'] = geoAddon::triggerDisplay('Browse_ads_display_browse_result_addRow', array('this'=>$this,'show_classifieds' => $row, 'browse_fields' => $fields), geoAddon::ARRAY_ARRAY);
            }
            $i_vars['listings'] = $listings;
            $tpl = new geoTemplate('system', 'browsing');
            $tpl->assign($i_vars);
            $listingData[$browse_type] = $tpl->fetch('common/grid_view.tpl'); //TODO: userland switching between browse templates
        }

        $tpl_vars['listingData'] = $listingData;

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        geoView::getInstance()->setBodyTpl('listings.tpl', 'storefront')
            ->setBodyVar($tpl_vars);

        return;
    }

    private function _getOrderByClause($sort_type, $query)
    {
        $classTable = geoTables::classifieds_table;
        if (!$sort_type) {
            $query->order("$classTable.`better_placement` DESC")
                ->order("$classTable.`date` DESC");
            return;
        }

        $sort_types = array (
            1 => array('price','minimum_bid', 'buy_now'),
            3 => 'date',
            5 => 'title',
            7 => 'location_city',
            //9 => 'location_state',
            //11 => 'location_country',
            13 => 'location_zip',
            15 => 'optional_field_1',
            17 => 'optional_field_2',
            19 => 'optional_field_3',
            21 => 'optional_field_4',
            23 => 'optional_field_5',
            25 => 'optional_field_6',
            27 => 'optional_field_7',
            29 => 'optional_field_8',
            31 => 'optional_field_9',
            33 => 'optional_field_10',
            //35 => 'location_city',
            //37 => 'location_state',
            //39 => 'location_country',
            //41 => 'location_zip',
            43 => 'business_type',
            45 => 'optional_field_11',
            47 => 'optional_field_12',
            49 => 'optional_field_13',
            51 => 'optional_field_14',
            53 => 'optional_field_15',
            55 => 'optional_field_16',
            57 => 'optional_field_17',
            59 => 'optional_field_18',
            61 => 'optional_field_19',
            63 => 'optional_field_20',
            //65 => '',  ////***65/66 - reserved cases, default for some SEO pages***
            69 => 'ends',
            //67 => 'date',
            71 => 'image > 0', //this is valid mysql: "ORDER BY image > 0 DESC" means "show listings with at least one image first"
        );
        //fix ones where odd version is desc, and even version is asc (backwards of normal)
        $asc_backwards = array (
            1, 2
        );
        if (in_array($sort_type, $asc_backwards)) {
            $asc_desc = ($sort_type % 2) ? 'DESC' : 'ASC';
        } else {
            $asc_desc = ($sort_type % 2) ? 'ASC' : 'DESC';
        }
        //Goal: if it's an even number, get it to be 1 less.
        $sort_type = (($sort_type % 2) ? $sort_type : $sort_type - 1);
        $sort_fields = (is_array($sort_types[$sort_type])) ? $sort_types[$sort_type] : array($sort_types[$sort_type]);
        $sort = array();
        foreach ($sort_fields as $field) {
            if (!$field) {
                //probably a setting that doesn't do anything anymore
                continue;
            }
            $query->order("$classTable.$field $asc_desc");
        }
        $query->order("$classTable.`better_placement` DESC");
    }

    /**
     * updater for forms in the actual storefront
     * (right now, that's just the subscribe-to-newsletter form)
     *
     * @return unknown
     */
    function update()
    {
        $util = geoAddon::getUtil('storefront');
        $db = DataAccess::getInstance();
        $tables = $util->tables();
        $store_id = $util->getStoreId();
        $text = geoAddon::getText('geo_addons', 'storefront');

        if (isset($_POST['email']) && geoString::isEmail($_POST['email'])) {
            $sql = "SELECT user_email FROM $tables->users WHERE user_email=? and store_id=?";
            $r = $db->GetOne($sql, array($_POST['email'], $store_id));

            if ($r) {
                //user already subscribed...
                $newsletter_result = $text['newsletter_subscribe_bad'];
            } else {
                $sql = "INSERT INTO $tables->users SET
				store_id=?,
				user_email=?
				";


                $r = $db->Execute($sql, array($store_id,$_POST['email']));
                if ($r === false) {
                    die($db->ErrorMsg());
                }
                setcookie('emailAdded_' . $store_id, '1');
                $_COOKIE['emailAdded_' . $store_id] = 'true';
                $newsletter_result = $text['newsletter_subscribe_good'];
            }
            geoView::getInstance()->updateResult = $newsletter_result;
            return true;
        }

        if (isset($_POST['contact']) && $_POST['contact']) {
            $user = geoUser::getUser($util->owner);
            if ($user) {
                $data = $_POST['contact'];
                foreach ($data as $key => $datum) {
                    //undo clean_inputs (and trim while we're at it)
                    if ($key == 'extra') {
                        //user-defined extra fields
                        foreach ($datum as $dKey => $extra) {
                            $data[$key][$dKey] = geoString::specialCharsDecode(trim($extra));
                        }
                        continue;
                    }
                    $data[$key] = geoString::specialCharsDecode(trim($datum));
                }
                if (!$data['email'] || !$data['subject'] || !$data['name'] || !$data['message']) {
                    $result = $text['contact_email_missing_info'];
                } elseif (!geoString::isEmail($data['email'])) {
                    $result = $text['contact_email_bad_email'];
                } else {
                    $from = $data['email'];
                    $subject = $text['contact_email_subject'] . $data['subject'];
                    $tpl = new geoTemplate('addon', 'storefront');
                    $tpl->assign('introduction', $text['contact_email_greeting']);
                    $tpl->assign('salutation', $user->getSalutation());
                    $tpl->assign('senderName', $data['name']);
                    $tpl->assign('text1', $text['contact_email_text1']);
                    $tpl->assign('message', nl2br($data['message']));

                    //info from user-defined extra fields
                    if (count($data['extra']) > 0) {
                        $extra = array();
                        foreach ($data['extra'] as $key => $e) {
                            $extra[] = ucwords(str_replace('_', ' ', $key)) . ': ' . $e;
                        }
                        $tpl->assign('extra', $extra);
                    }
                    $message = $tpl->fetch('email_contact_store.tpl');

                    geoEmail::sendMail($user->email, $subject, $message, $from, 0, 0, 'text/html');
                    $result = $text['contact_email_result_good'];
                }
            } else {
                $result = $text['contact_email_result_bad'];
            }

            geoView::getInstance()->updateResult = $result;
            return true;
        }
    }

    public function classifieds_details_sub_template()
    {
        $this->_pageNotUsed();
    }
    public function auctions_details_sub_template()
    {
        $this->_pageNotUsed();
    }

    private function _pageNotUsed()
    {
        echo '<h1 style="color: red;">Internal Use Only</h1>';
        include GEO_BASE_DIR . 'app_bottom.php';
        exit;
    }
}
