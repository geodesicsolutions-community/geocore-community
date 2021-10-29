<?php

//addons/sharing/pages.php
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
## ##    7.2.2-9-gecca232
##
##################################

# sharing Addon

class addon_sharing_pages
{

    public function main()
    {
        $userId = geoSession::getInstance()->getUserId();

        $db = DataAccess::getInstance();
        $view = geoView::getInstance();
        $view->msgs = geoAddon::getText('geo_addons', 'sharing');

        if ($_REQUEST['share']) {
            //sharing a specific, predetermined listing
            $shareThis = intval($_REQUEST['share']);
            $view->shareSpecificListing = $shareThis;
            $listing = geoListing::getListing($shareThis);
            $view->id = $listing->id;
            $view->title = geoString::fromDB($listing->title);
        } elseif ($userId) {
            //show user a list of his listings, to pick one to be shared
            $sql = "SELECT `id`, `title` FROM " . geoTables::classifieds_table . " WHERE seller = ? AND live = 1 ORDER BY `id` DESC";
            $result = $db->Execute($sql, array($userId));
            if (!$result || $result->RecordCount() == 0) {
                //show a page that says there's nothing to share
                $view->setBodyTpl('no_listings.tpl', 'sharing');
                return;
            }
            $listings = array();
            while ($listing = $result->FetchRow()) {
                $listings[] = array(
                    'id' => $listing['id'],
                    'title' => geoString::fromDB($listing['title'])
                );
            }
            $view->listings = $listings;
        }



        $view->addCssFile(geoTemplate::getUrl('css', 'addon/sharing/sharing.css'));
        $view->setBodyTpl('main.tpl', 'sharing');
    }

    /**
     *  Generic wrapper function for all of this addon's AJAX calls, to make sure that they're all uniform and nothing gets called that shouldn't be
     */
    public function ajax()
    {
        geoView::getInstance()->setRendered(true); //don't print anything to the main view
        $util = geoAddon::getUtil('sharing');
        $function = $_GET['function'];
        if ($function === 'getMethodsForListing') {
            $return = $util->getMethodsForListing($_POST['listing']);
        } elseif ($function === 'getOptionsForMethod') {
            $return = $util->getOptionsForMethod($_POST['method']);
        } elseif ($function === 'processOptionsForm') {
            $return = $util->processOptionsForMethod($_POST['chosenMethod']);
        } else {
            $return =  '';
        }
        echo $return;
    }



    public function craigslist_output()
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
