<?php

//addons/storefront/tags.php
/**
 * Optional file.  Used for addon tags on the client side.
 *
 * Remember to rename the class name, replacing "storefront" with
 * the folder name for your addon.
 *
 * Also see the file php5_files/tags.php (in the package storefront_addon_php5)
 *
 * @author Geodesic Solutions, LLC
 * @package storefront_addon
 */


# storefront Addon

//Tag replacement file, for storefront module.
//This file needs to contain class: addon_ADDON_NAME_tags
//ADDON_NAME is the same as the folder name for the addon.

/**
 * Expects one function for each tag.  Function name should be the same as
 * the tag name.  Can also have a constructor if anything needs to be constructed.
 *
 * @author Geodesic Solutions, LLC
 * @version 7.5.2-30-ge4c3af1
 * @copyright Copyright (c) 2001-2009 Geodesic Solutions, LLC
 * @package storefront_addon
 */
class addon_storefront_tags extends addon_storefront_info
{

    function client_menu()
    {
        $db = 1;
        include GEO_BASE_DIR . 'get_common_vars.php';

        $link = $db->get_site_setting('classifieds_file_name') . "?a=ap&amp;addon=storefront&amp;page=home&amp;store=" . geoSession::getInstance()->getUserId();
        $msgs = geoAddon::getText($this->auth_tag, $this->name);
        $alt_text = $text = $msgs['my_storefront_label'];
        return '<div><a href="' . $link . '"><span class="user_links">' . $text . '</span></a></div>';
    }

    function control_panel_link()
    {
        $db = DataAccess::getInstance();

        $link = $db->get_site_setting('classifieds_file_name') . "?a=ap&amp;addon=storefront&amp;page=control_panel";
        $msgs = geoAddon::getText($this->auth_tag, $this->name);
        $alt_text = $text = $msgs['cp_link_text'];
        return '<div><a href="' . $link . '"><span class="user_links">' . $text . '</span></a></div>';
    }

    function storefront_name()
    {
        if ($_REQUEST['a'] != 'ap' || $_REQUEST['addon'] != 'storefront' || $_REQUEST['page'] != 'home' || !$_REQUEST['store']) {
            //not a valid storefront page -- nothing to do here
            return '';
        }

        $db = DataAccess::getInstance();

        $store = $_REQUEST['store'];
        $util = geoAddon::getUtil('storefront');
        $store = $util->storeIdFromString($store);

        $sql = "SELECT `storefront_name` FROM `geodesic_addon_storefront_user_settings` WHERE `owner` = ?";
        $name = $db->GetOne($sql, array($store));

        if (!$name) {
            //storefront name not set -- default to username
            $name = geoUser::userName($store);
        }

        return $name;
    }

    public function list_stores_link($params, Smarty_Internal_Template $smarty)
    {
        $msgs = geoAddon::getText($this->auth_tag, $this->name);

        $tpl_vars = array('tab_name' => $msgs['store_tab_name']);
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'list_stores_link.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }

    public function storefront_link($params, Smarty_Internal_Template $smarty)
    {
        $listing = geoListing::getListing($params['listing_id']);
        if (!$listing) {
            //just a failsafe
            return '';
        }
        $util = geoAddon::getUtil($this->name);
        if (!$util) {
            //another failsafe
            return '';
        }
        if (!$listing->seller || ($util->userHasCurrentSubscription($listing->seller) == 0)) {
            //no subscription for seller
            return '';
        }
        $tpl_vars = array (
            'seller' => $listing->seller,
            'msgs' => geoAddon::getText($this->auth_tag, $this->name),
            );
        return geoTemplate::loadInternalTemplate(
            $params,
            $smarty,
            'listing_tags/storefront_link.tpl',
            geoTemplate::ADDON,
            $this->name,
            $tpl_vars
        );
    }
}
