<?php

//addons/signs_flyers/util.php
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
## ##    16.07.0-92-g7985953
##
##################################

# Signs & Flyers Addon

class addon_signs_flyers_util extends addon_signs_flyers_info
{

    var $site;
    var $db;
    public function __construct()
    {
        $this->db = DataAccess::getInstance();
    }

    function setSite($site_class = null)
    {
        if ($site_class) {
            $this->site = $site_class;
        } elseif (!isset($this->site) || !is_object($this->site)) {
            include_once CLASSES_DIR . 'site_class.php';
            $this->site = Singleton::getInstance('geoSite');
        }
    }

    function signsForm($classified_id)
    {
        $classified_id = (int)$classified_id;
        include_once CLASSES_DIR . 'site_class.php';
        $site = Singleton::getInstance('geoSite');
        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $site->page_id = 71;
        $site->get_text();
        $listing = geoListing::getListing($classified_id);
        $user_id = geoSession::getInstance()->getUserId();
        $user = geoUser::getUser($user_id);

        if (!$listing || !$user || $listing->seller != $user_id) {
            $site->site_error();
            return false;
        }

        $sql = "select * from " . geoTables::pages_table . " where page_id = 71";
        $result = $db->Execute($sql);
        if (!$result) {
            return false;
        }
        $view->pageOptions = $pageOptions = $result->FetchRow();

        //show the form to begin editing this classified ad details
        $view->formTarget = $db->get_site_setting('classifieds_file_name') . "?a=4&b=12&d=" . $classified_id;
        $view->listing_id = $classified_id;

        $pre = $listing->precurrency;
        $post = $listing->postcurrency;

        //set up image choices
        $sql = "select * from " . geoTables::choices_table . " where type_of_choice = 14";
        $sign_image_result = $db->Execute($sql);
        if (!$sign_image_result) {
            return false;
        }
        $imageChoices = array();
        while ($choice = $sign_image_result->FetchRow()) {
            $imageChoices[$choice['choice_id']] = $choice['display_value'];
        }
        $view->imageChoices = $imageChoices;

        $view->title = geoString::fromDB($listing->title);
        $view->price = geoString::displayPrice($listing->price, $pre, $post);
        $view->name = $user->firstname . ' ' . $user->lastname;
        $view->phone = geoString::fromDB($listing->phone);
        $view->phone2 = geoString::fromDB($listing->phone2);
        $view->address = geoString::fromDB($listing->location_address);
        $view->city = geoString::fromDB($listing->location_city);
        $view->state = geoRegion::getStateNameForListing($listing->id);
        $view->zip = geoString::fromDB($listing->location_zip);
        $view->description = geoString::fromDB($listing->description);


        if (geoPC::is_ent()) {
            $optionals = array();
            for ($i = 1; $i <= 20; $i++) {
                if ($pageOptions['module_display_optional_field_' . $i]) {
                    $field = 'optional_field_' . $i;
                    $optionals[$i]['value'] = geoString::fromDB($listing->$field);
                    if ($site->fields->$field->field_type == 'cost') {
                        $optionals[$i]['value'] = geoString::specialChars(geoString::displayPrice($optionals[$i]['value'], $pre, $post));
                    }
                    $txtId = (($i > 10) ? 1836 : 1287) + $i;
                    $optionals[$i]['label'] = $site->messages[$txtId];
                }
            }
            $view->optionals = $optionals;
        }

        $view->backLink = $this->db->get_site_setting('classifieds_file_name') . "?a=4";

        $view->setBodyTpl('sign_form.tpl', 'signs_flyers');
        $site->display_page();
        return true;
    }

    function signsDisplay($sign_info)
    {
        $this->setSite();
        $this->site->page_id = 74;
        $this->site->get_text();


        $view = geoView::getInstance();

        $sql = "select * from " . $this->site->pages_table . " where page_id = 71";
        $page_result = $this->db->Execute($sql);

        $show_page = $page_result->FetchNextObject();

        if ($show_page->MODULE_USE_IMAGE) {
            if ($sign_info["image"] != 0) {
                $sql = "select value from " . $this->site->choices_table . " where choice_id = ?";
                $image_value = $this->db->GetOne($sql, array((int)$sign_info["image"]));
                if ($image_value) {
                    $image_tag = '<img src="' . geoTemplate::getUrl('', $image_value) . '" alt="" />';
                } else {
                    $image_tag = $this->get_signs_and_flyers_user_image($sign_info["classified_id"], "sign");
                }
            } else {
                $image_tag = $this->get_signs_and_flyers_user_image($sign_info["classified_id"], "sign");
            }
            $view->image = $image_tag;
        } else {
            $view->image = '';
        }


        $sql = "select * from " . $this->site->classifieds_table . " where id = " . $sign_info["classified_id"];
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        }
        $listing = $result->FetchRow();


        $view->title = (!$show_page->MODULE_DISPLAY_TITLE) ? $sign_info["title"] : '';
        $view->address = ($show_page->MODULE_DISPLAY_ADDRESS) ? $sign_info["address"] : '';
        $view->city = ($show_page->MODULE_DISPLAY_CITY) ? $sign_info["city"] : '';
        $view->state = ($show_page->MODULE_DISPLAY_STATE) ? $sign_info["state"] : '';
        $view->zip = ($show_page->MODULE_DISPLAY_ZIP) ? $sign_info["zip"] : '';
        $view->optional_field_1 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_1) ? $sign_info["optional_field_1"] : '';
        $view->optional_field_2 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_2) ? $sign_info["optional_field_2"] : '';
        $view->optional_field_3 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_3) ? $sign_info["optional_field_3"] : '';
        $view->optional_field_4 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_4) ? $sign_info["optional_field_4"] : '';
        $view->optional_field_5 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_5) ? $sign_info["optional_field_5"] : '';
        $view->optional_field_6 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_6) ? $sign_info["optional_field_6"] : '';
        $view->optional_field_7 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_7) ? $sign_info["optional_field_7"] : '';
        $view->optional_field_8 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_8) ? $sign_info["optional_field_8"] : '';
        $view->optional_field_9 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_9) ? $sign_info["optional_field_9"] : '';
        $view->optional_field_10 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_10) ? $sign_info["optional_field_10"] : '';
        $view->optional_field_11 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_11) ? $sign_info["optional_field_11"] : '';
        $view->optional_field_12 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_12) ? $sign_info["optional_field_12"] : '';
        $view->optional_field_13 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_13) ? $sign_info["optional_field_13"] : '';
        $view->optional_field_14 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_14) ? $sign_info["optional_field_14"] : '';
        $view->optional_field_15 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_15) ? $sign_info["optional_field_15"] : '';
        $view->optional_field_16 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_16) ? $sign_info["optional_field_16"] : '';
        $view->optional_field_17 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_17) ? $sign_info["optional_field_17"] : '';
        $view->optional_field_18 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_18) ? $sign_info["optional_field_18"] : '';
        $view->optional_field_19 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_19) ? $sign_info["optional_field_19"] : '';
        $view->optional_field_20 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_20) ? $sign_info["optional_field_20"] : '';
        $view->price = ($show_page->MODULE_DISPLAY_PRICE) ? $sign_info["price"] : '';
        $view->contact = ($show_page->MODULE_DISPLAY_CONTACT) ? $sign_info["contact"] : '';
        $view->phone_1 = ($show_page->MODULE_DISPLAY_PHONE1) ? $sign_info["phone_1"] : '';
        $view->phone_2 = ($show_page->MODULE_DISPLAY_PHONE2) ? $sign_info["phone_2"] : '';
        $view->classified_id = ($show_page->MODULE_DISPLAY_CLASSIFIED_ID) ? $sign_info["classified_id"] : '';
        $view->auction_id = ($show_page->MODULE_DISPLAY_AUCTION_ID) ? $sign_info["classified_id"] : '';
        $view->description = ($show_page->MODULE_DISPLAY_AD_DESCRIPTION) ? stripslashes(stripslashes(urldecode($sign_info["description"]))) : '';
        $view->buy_now_price = (($listing['buy_now'] != 0.00) && ($listing['item_type'] == 2)) ? geoString::fromDB($auction->BUY_NOW) : '';
        $view->starting_bid = ($listing['item_type'] == 2) ? geoString::fromDB($listing->STARTING_BID) : '';
        $view->url_1 = geoString::fromDB($listing['url_link_1']);
        $view->url_2 = geoString::fromDB($listing['url_link_2']);
        $view->url_3 = geoString::fromDB($listing['url_link_3']);

        $view->addon_text = geoAddon::getText($this->auth_tag, $this->name);

        //Use Smarty templates
        $view->setLanguage($this->db->getLanguage());
        $page_id = 74;

        $tpl_file = $view->getTemplateAttachment($page_id);
        //FIXME: Not really the best way to do things...
        $view->forceTemplateAttachment($tpl_file);

        echo $view->render($page_id, true);
    }

    function flyersForm($classified_id)
    {
        $classified_id = (int)$classified_id;
        include_once CLASSES_DIR . 'site_class.php';
        $site = Singleton::getInstance('geoSite');
        $view = geoView::getInstance();
        $db = DataAccess::getInstance();

        $site->page_id = 70;
        $site->get_text();
        $listing = geoListing::getListing($classified_id);
        $user_id = geoSession::getInstance()->getUserId();
        $user = geoUser::getUser($user_id);

        if (!$listing || !$user || $listing->seller != $user_id) {
            $site->site_error();
            return false;
        }

        $sql = "select * from " . geoTables::pages_table . " where page_id = 70";
        $result = $db->Execute($sql);
        if (!$result) {
            return false;
        }
        $view->pageOptions = $pageOptions = $result->FetchRow();

        //show the form to begin editing this classified ad details
        $view->formTarget = $db->get_site_setting('classifieds_file_name') . "?a=4&b=13&d=" . $classified_id;
        $view->listing_id = $classified_id;


        $pre = $listing->precurrency;
        $post = $listing->postcurrency;

        //set up image choices
        $sql = "select * from " . geoTables::choices_table . " where type_of_choice = 13";
        $sign_image_result = $db->Execute($sql);
        if (!$sign_image_result) {
            return false;
        }
        $imageChoices = array();
        while ($choice = $sign_image_result->FetchRow()) {
            $imageChoices[$choice['choice_id']] = $choice['display_value'];
        }
        $view->imageChoices = $imageChoices;

        $view->title = geoString::fromDB($listing->title);
        $view->price = geoString::displayPrice($listing->price, $pre, $post);
        $view->name = $user->firstname . ' ' . $user->lastname;
        $view->phone = geoString::fromDB($listing->phone);
        $view->phone2 = geoString::fromDB($listing->phone2);
        $view->address = geoString::fromDB($listing->location_address);
        $view->city = geoString::fromDB($listing->location_city);
        $view->state = geoRegion::getStateNameForListing($listing->id);
        $view->zip = geoString::fromDB($listing->location_zip);
        $view->description = geoString::fromDB($listing->description);


        if (geoPC::is_ent()) {
            $optionals = array();
            for ($i = 1; $i <= 20; $i++) {
                if ($pageOptions['module_display_optional_field_' . $i]) {
                    $field = 'optional_field_' . $i;
                    $optionals[$i]['value'] = geoString::fromDB($listing->$field);
                    if ($site->fields->$field->$field->field_type == 'cost') {
                        $optionals[$i]['value'] = geoString::specialChars(geoString::displayPrice($optionals[$i]['value'], $pre, $post));
                    }
                    $txtId = (($i > 10) ? 1816 : 1301) + $i;
                    $optionals[$i]['label'] = $site->messages[$txtId];
                }
            }
            $view->optionals = $optionals;
        }

        $view->backLink = $this->db->get_site_setting('classifieds_file_name') . "?a=4";

        $view->setBodyTpl('flyer_form.tpl', 'signs_flyers');
        $site->display_page();
        return true;
    }

    function flyersDisplay($flyer_info)
    {
        $this->setSite();
        $this->site->page_id = 73;
        $this->site->get_text();


        $view = geoView::getInstance();

        $sql = "select * from " . $this->site->pages_table . " where page_id = 70";
        $page_result = $this->db->Execute($sql);

        $show_page = $page_result->FetchNextObject();

        if ($show_page->MODULE_USE_IMAGE) {
            if ($flyer_info["image"] != 0) {
                $sql = "select value from " . $this->site->choices_table . " where choice_id = ?";
                $image_value = $this->db->GetOne($sql, array((int)$flyer_info["image"]));
                if ($image_value) {
                    $image_tag = '<img src="' . geoTemplate::getUrl('', $image_value) . '" alt="" />';
                } else {
                    $image_tag = $this->get_signs_and_flyers_user_image($flyer_info["classified_id"], "sign");
                }
            } else {
                $image_tag = $this->get_signs_and_flyers_user_image($flyer_info["classified_id"], "sign");
            }
            $view->image = $image_tag;
        } else {
            $view->image = '';
        }


        $sql = "select * from " . $this->site->classifieds_table . " where id = " . $flyer_info["classified_id"];
        $result = $this->db->Execute($sql);
        if (!$result) {
            return false;
        }
        $listing = $result->FetchRow();


        $view->title = (!$show_page->MODULE_DISPLAY_TITLE) ? $flyer_info["title"] : '';
        $view->address = ($show_page->MODULE_DISPLAY_ADDRESS) ? $flyer_info["address"] : '';
        $view->city = ($show_page->MODULE_DISPLAY_CITY) ? $flyer_info["city"] : '';
        $view->state = ($show_page->MODULE_DISPLAY_STATE) ? $flyer_info["state"] : '';
        $view->zip = ($show_page->MODULE_DISPLAY_ZIP) ? $flyer_info["zip"] : '';
        $view->optional_field_1 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_1) ? $flyer_info["optional_field_1"] : '';
        $view->optional_field_2 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_2) ? $flyer_info["optional_field_2"] : '';
        $view->optional_field_3 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_3) ? $flyer_info["optional_field_3"] : '';
        $view->optional_field_4 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_4) ? $flyer_info["optional_field_4"] : '';
        $view->optional_field_5 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_5) ? $flyer_info["optional_field_5"] : '';
        $view->optional_field_6 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_6) ? $flyer_info["optional_field_6"] : '';
        $view->optional_field_7 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_7) ? $flyer_info["optional_field_7"] : '';
        $view->optional_field_8 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_8) ? $flyer_info["optional_field_8"] : '';
        $view->optional_field_9 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_9) ? $flyer_info["optional_field_9"] : '';
        $view->optional_field_10 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_10) ? $flyer_info["optional_field_10"] : '';
        $view->optional_field_11 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_11) ? $flyer_info["optional_field_11"] : '';
        $view->optional_field_12 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_12) ? $flyer_info["optional_field_12"] : '';
        $view->optional_field_13 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_13) ? $flyer_info["optional_field_13"] : '';
        $view->optional_field_14 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_14) ? $flyer_info["optional_field_14"] : '';
        $view->optional_field_15 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_15) ? $flyer_info["optional_field_15"] : '';
        $view->optional_field_16 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_16) ? $flyer_info["optional_field_16"] : '';
        $view->optional_field_17 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_17) ? $flyer_info["optional_field_17"] : '';
        $view->optional_field_18 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_18) ? $flyer_info["optional_field_18"] : '';
        $view->optional_field_19 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_19) ? $flyer_info["optional_field_19"] : '';
        $view->optional_field_20 = ($show_page->MODULE_DISPLAY_OPTIONAL_FIELD_20) ? $flyer_info["optional_field_20"] : '';
        $view->price = ($show_page->MODULE_DISPLAY_PRICE) ? $flyer_info["price"] : '';
        $view->contact = ($show_page->MODULE_DISPLAY_CONTACT) ? $flyer_info["contact"] : '';
        $view->phone_1 = ($show_page->MODULE_DISPLAY_PHONE1) ? $flyer_info["phone_1"] : '';
        $view->phone_2 = ($show_page->MODULE_DISPLAY_PHONE2) ? $flyer_info["phone_2"] : '';
        $view->classified_id = ($show_page->MODULE_DISPLAY_CLASSIFIED_ID) ? $flyer_info["classified_id"] : '';
        $view->auction_id = ($show_page->MODULE_DISPLAY_AUCTION_ID) ? $flyer_info["classified_id"] : '';
        $view->description = ($show_page->MODULE_DISPLAY_AD_DESCRIPTION) ? stripslashes(stripslashes(urldecode($flyer_info["description"]))) : '';
        $view->buy_now_price = (($listing['buy_now'] != 0.00) && ($listing['item_type'] == 2)) ? geoString::fromDB($auction->BUY_NOW) : '';
        $view->starting_bid = ($listing['item_type'] == 2) ? geoString::fromDB($listing->STARTING_BID) : '';

        $view->url_1 = geoString::fromDB($listing['url_link_1']);
        $view->url_2 = geoString::fromDB($listing['url_link_2']);
        $view->url_3 = geoString::fromDB($listing['url_link_3']);

        $view->addon_text = geoAddon::getText($this->auth_tag, $this->name);

        $view->setLanguage($this->db->getLanguage());

        $page_id = 73;

        $tpl_file = $view->getTemplateAttachment($page_id);
        //FIXME: Not really the best way to do things...
        $view->forceTemplateAttachment($tpl_file);

        echo $view->render($page_id, true);
    }

    function get_signs_and_flyers_user_image($classified_id = 0, $type = 0)
    {
        $classified_id = (int)$classified_id;
        if ((!$classified_id) || (!$type)) {
            return false;
        }
        $db = DataAccess::getInstance();
        $sql = "SELECT sign_maximum_image_width AS sw,
					   sign_maximum_image_height AS sh,
					   flyer_maximum_image_width AS fw, 
					   flyer_maximum_image_height AS fh 
					   FROM geodesic_classifieds_ad_configuration LIMIT 1";
        $data = $db->GetRow($sql);
        if ($type == "sign") {
            $max_width = $data['sw'];
            $max_height = $data['sh'];
        } elseif ($type == "flyer") {
            $max_width = $data['fw'];
            $max_height = $data['fh'];
        }
        return geoImage::display_thumbnail($classified_id, $max_width, $max_height, 1, 0, '', 1);
    }

    function signs_and_flyers_list()
    {
        $site = Singleton::getInstance('geoSite');
        $view = geoView::getInstance();
        $db = DataAccess::getInstance();
        $site->page_id = 72;
        $site->get_text();

        $userid = geoSession::getInstance()->getUserId();
        if (!$userid) {
            return false;
        }

        $listingsPerPage = 10;
        $currentPage = (is_numeric($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
        $startListing = $listingsPerPage * ($currentPage - 1);

        $sql = "select * from " . geoTables::classifieds_table . " where seller = ? and live = 1 order by `date` desc LIMIT {$startListing}, {$listingsPerPage}";

        $result = $db->Execute($sql, array($userid));
        if (!$result) {
            return false;
        } elseif ($result->RecordCount() > 0) {
            $listings = array();
            while ($show = $result->FetchRow()) {
                $listings[$show['id']]['title'] = geoString::fromDB($show['title']);
                $listings[$show['id']]['listing_url'] = $db->get_site_setting('classifieds_file_name') . "?a=2&amp;b=" . $show['id'];
                $listings[$show['id']]['sign_url'] = $db->get_site_setting('classifieds_url') . "?a=4&amp;b=12&amp;c=" . $show['id'];
                $listings[$show['id']]['flyer_url'] = $db->get_site_setting('classifieds_url') . "?a=4&amp;b=13&amp;c=" . $show['id'];
            }
            $view->listings = $listings;

            $count_sql = "select count(id) as count from " . $this->site->classifieds_table . " where seller = ? and live = 1";
            $listingsCount = $this->db->GetOne($count_sql, array($userid));
            $totalPages = ceil($listingsCount / $listingsPerPage);
            if ($totalPages > 1) {
                $link = $db->get_site_setting('classifieds_url') . "?a=4&amp;b=12&amp;page=";
                $css = 'browsing_result_page_links';
                $view->pagination = geoPagination::getHTML($totalPages, $currentPage, $link, $css);
            }
        } else {
            //there are no current ads for this user
            $view->no_current_listings = true;
        }
        $view->user_management_home_link = $this->db->get_site_setting('classifieds_file_name') . "?a=4";

        $view->setBodyTpl('list.tpl', 'signs_flyers');
        $site->display_page();
        return true;
    }

    /**
     * Respond to core event to add link to My Account Links module
     *
     * @param $vars variables passed in from core software
     * @return array array of data to be added to core links array
     */
    function core_my_account_links_add_link($vars)
    {
        $link = array();

        $link['link'] = $vars['url_base'] . "?a=4&amp;b=12";
        $link['active'] = ($_REQUEST['a'] == 4 && ($_REQUEST['b'] == 12 || $_REQUEST['b'] == 13)) ? true : false;

        $text = geoAddon::getText('geo_addons', 'signs_flyers');
        $link['label'] = $text['my_account_links_label'];

        if (DataAccess::getInstance()->get_site_setting('show_addon_icons')) {
            $link['icon'] = $text['my_account_links_icon'];
        }

        //signs&flyers never really "needs attention" but putting this here for documentation purposes
        //setting this to true would make the link text red (assuming default CSS)
        $link['needs_attention'] = false;

        $return['signs_flyers'] = $link;

        return $return;
    }
}
