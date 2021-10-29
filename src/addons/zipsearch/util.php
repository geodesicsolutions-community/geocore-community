<?php

//addons/zipsearch/app_top.php
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
## ##    17.04.0-3-g481bf8b
##
##################################

class addon_zipsearch_util extends addon_zipsearch_info
{
    private $_origin = array();

    /**
     * For UK type postal codes, we only actually match the outward part (the stuff
     * before the space), as using the full postal code would result in millions
     * of entries to look for.  Some post codes actually narrow down to the actual
     * address of one house!
     *
     * @param string $postcode
     */
    public function getOutwardPostal($postcode)
    {
        $postcode = trim($postcode);
        $postcode = str_replace(' ', '', $postcode);
        if (strlen($postcode) < 5) {
            //they must have only entered first part, return as-is
            return $postcode;
        }

        //figure out how many characters to chop off the end
        $reg = geoAddon::getRegistry($this->name);
        $hierarchical_trim = $reg->hierarchical_trim;
        $hierarchical_trim = -1 * $hierarchical_trim; //we want this as a negative number to send to substr()
        //return with last n chars stripped off.
        return substr($postcode, 0, $hierarchical_trim);
    }

    public function getZipsInRange($originZip, $distance)
    {
        //sanity trims, to be absolutely sure there are no extra spaces that might mess with stuff below
        $originZip = trim($originZip);
        $distance = trim($distance);

        if (!$originZip || !$distance) {
            //nowhere to start from -- can't proceed
            return false;
        }

        $zipsInRange = array();

        //get registry settings
        $reg = geoAddon::getRegistry('zipsearch');
        $searchMethod = $reg->search_method;
        $units = $reg->units;

        if ($searchMethod == 'hierarchical') {
            //Only need outward part of the postal code (first part of it)
            $originZip = $this->getOutwardPostal($originZip);
        }

        //get the longitude and latitude of the zip code entered
        $db = DataAccess::getInstance();
        $sql = "SELECT * FROM " . geoTables::postal_code_table . " WHERE `zipcode` = ? LIMIT 1";
        $origin = $db->GetRow($sql, array($originZip));

        if (!$origin) {
            //not a valid origin
            return false;
        }
        $this->_origin = $origin;
        /*
         * Alright, here's where the magic starts. The basic idea is:
         * We have an origin zipcode and a distance away from it to search.
         * RadiusAssistant() gets the four points that are "distance" away from the origin along cardinal directions
         * Those points define a "box" containing the target area.
         * Then, use some math to further weed out the corners of the box that are actually outside the radial distance we're looking in
         *
         */

        geoRegion::RadiusAssistant($origin['latitude'], $origin['longitude'], $distance, $units);

        if (geoRegion::$min_longitude > geoRegion::$max_longitude) {
            //this box crosses the "international date line"
            //(contains longitudes at both the very top and very bottom of the valid range, and not the middle)
            //we want to find all longitudes that are between $min and 180 OR between -180 and max
            $longitude_sql = "((`longitude` >= " . geoRegion::$min_longitude . " AND `longitude` <= 180) OR (`longitude` >= -180 AND longitude` <= " . geoRegion::$max_longitude . ")";
        } else {
            $longitude_sql = "(`longitude` >= " . geoRegion::$min_longitude . " AND `longitude` <= " . geoRegion::$max_longitude . ")";
        }

        //get all the zipcodes that fall inside the bounding box
        $sql = "SELECT DISTINCT(`zipcode`), `latitude`, `longitude` FROM " . geoTables::postal_code_table . " WHERE
					(
						(`latitude` >= " . geoRegion::$min_latitude . " AND `latitude` <= " . geoRegion::$max_latitude . ")
						AND
						" . $longitude_sql . "
					)";
        $rangeZips = $db->Execute($sql);
        while ($target = $rangeZips->FetchRow()) {
            //loop through the zips in the bounds box
            //throw out any that aren't within $distance of the $origin
            $checkDist = geoNumber::distanceBetweenPoints($origin['latitude'], $origin['longitude'], $target['latitude'], $target['longitude'], $units);
            if ($checkDist <= $distance) {
                //this zipcode is in range!
                if (!in_array($target['zipcode'], $zipsInRange)) { //get rid of any duplicates
                    $zipsInRange[] = $target['zipcode'];
                }
            }
        }

        return $zipsInRange;
    }

    /**
     * Gets the SQL snippet required to search for zipcodes within a distance from a given origin zip
     * @param $originZip String the zipcode at the center of the search
     * @param $distance int the distance from the origin zip to look for other zips
     * @return String sql snippet that can be plugged into a search query
     */
    public function getSearchSql($originZip, $distance)
    {
        $db = DataAccess::getInstance();

        $classTable = geoTables::classifieds_table;

        if (!$distance) {
            //no distance given, so just look at this zipcode
            return $db->quoteInto("$classTable.`location_zip` = ?", $originZip, DataAccess::TYPE_STRING_TODB);
        }

        //Sweden (and possibly others?) use internal spaces even in non-hierarchical zipcodes
        //BUT, their import data is typically un-spaced, and users are inconsistent in entering data one way or the other
        //let's go ahead and pull out any internal spaces, and that'll just be the norm.
        $originZip = str_replace(' ', '', $originZip);

        $settings = geoAddon::getRegistry('zipsearch');
        $zipsInRange = $this->getZipsInRange($originZip, $distance);

        if (!$zipsInRange) {
            //the origin could not be found, or no zips were in range
            //in either case, do an exact match on this zipcode
            return $db->quoteInto("$classTable.`location_zip` = ?", $originZip, DataAccess::TYPE_STRING_TODB);
        } elseif ($settings->search_method == 'hierarchical') {
            //only filtering on first three characters, but listings may use all 6 or 7
            //set the search to use all zips beginning with these characters
            foreach ($zipsInRange as $zip) {
                $statements[] = "$classTable.`location_zip` LIKE '" . geoString::toDB($zip) . "%'";
            }
            $zip_sql = '(' . implode(' OR ', $statements) . ')';
        } else {
            //using non-hierarchical codes, so don't need to do a LIKE...just check the exact codes to see if they're there or not
            //-- as in the above comments about Sweden, do a REPLACE() here to make sure we're looking for the expected data
            $zip_sql = "REPLACE($classTable.`location_zip`, ' ', '') IN ('" . implode("', '", $zipsInRange) . "')";
        }
        return $zip_sql;
    }

    public function getLastOriginLocation()
    {
        return $this->_origin;
    }

    public function core_Search_classifieds_generate_query($vars)
    {
        $searchClass = $vars['this'];

        $originZip = $searchClass->search_criteria["by_zip_code"] ? $searchClass->search_criteria["by_zip_code"] : $_COOKIE['zip_filter'];
        $distance = $searchClass->search_criteria["by_zip_code_distance"] ? $searchClass->search_criteria["by_zip_code_distance"] : $_COOKIE['zip_distance_filter'];
        if (!$originZip || !$distance) {
            //don't do nothin, just let default behavior kick in if needed
            return;
        }

        $query = $searchClass->db->getTableSelect(DataAccess::SELECT_SEARCH);

        $zip_sql = $this->getSearchSql($originZip, $distance);
        if ($zip_sql) {
            //if we have distance query, replace the built-in where with this one.
            $query->where($zip_sql, 'location_zip');
        }
    }

    public function core_Search_classifieds_BuildResults_addHeader($vars)
    {
        $searchClass = $vars['this'];

        $headers = array();

        if (($searchClass->search_criteria["by_zip_code"]) && ($searchClass->search_criteria["by_zip_code_distance"])) {
            $zipSettings = geoAddon::getRegistry($this->name);

            $zipText = geoAddon::getText('geo_addons', 'zipsearch');
            $headers[] = array(
                'text' => ($zipSettings->units == 'M') ? $zipText['tbl_head_distance_mi'] : $zipText['tbl_head_distance_km'],
            );
        }
        return $headers;
    }

    public function core_Search_classifieds_BuildResults_addRow($vars)
    {
        $zipSettings = geoAddon::getRegistry($this->name);
        $searchClass = $vars['this'];

        $searchZip = $searchClass->search_criteria['by_zip_code'];

        if (!$searchZip) {
            //no search by zip code
            return false;
        }

        $listing = geoListing::getListing($vars['listing_id']);

        $listingZip = geoString::fromDB($listing->location_zip);

        if ($zipSettings->search_method == 'hierarchical') {
            $listingZip = $this->getOutwardPostal($listingZip);
        }
        $sql = "select latitude,longitude from " . geoTables::postal_code_table . " where zipcode = ? limit 1";
        $listingOrigin = $searchClass->db->GetRow($sql, array($listingZip));
        $searchOrigin = $this->_origin;
        $distance = 0;
        if ($listingOrigin && $searchOrigin) {
            $distance = geoNumber::distanceBetweenPoints($listingOrigin['latitude'], $listingOrigin['longitude'], $searchOrigin['latitude'], $searchOrigin['longitude'], $zipSettings->units);
        }

        //use a template to run sprintf() on the distance, so the format can be changed
        $tpl = new geoTemplate('addon', 'zipsearch');
        $tpl->assign('distance', $distance);

        $viewMode = $searchClass->getCurrentBrowseView();
        if ($viewMode === 'gallery' || $viewMode === 'list') {
            //write a label so we don't just have an unexplained number hanging out in the middle of nowhere
            $zipText = geoAddon::getText('geo_addons', 'zipsearch');
            $tpl->assign('label', (($zipSettings->units == 'M') ? $zipText['browse_label_distance_mi'] : $zipText['browse_label_distance_km']));
        }

        return array($tpl->fetch('distance_format.tpl'));
    }

    public function core_show_listing_alerts_table_headers()
    {
        $msgs = geoAddon::getText($this->auth_tag, $this->name);
        return $msgs['listing_alert_basic_distance_header'];
    }
    public function core_show_listing_alerts_table_body($filter_id)
    {
        $reg = geoAddon::getRegistry($this->name);
        $center = "listing_filter_center_" . $filter_id;
        $distance = "listing_filter_distance_" . $filter_id;

        if (!$reg->$center) {
            //no zipsearch exists for this filter
            return '&nbsp;';
        }

        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $tpl->assign('units', $reg->units);
        $tpl->assign('msgs', geoAddon::getText($this->auth_tag, $this->name));
        $tpl->assign('center', $reg->$center);
        $tpl->assign('distance', $reg->$distance);
        return $tpl->fetch('listing_alerts_write_distance.tpl');
    }
    public function core_display_add_listing_alert_field()
    {
        $tpl = new geoTemplate(geoTemplate::ADDON, $this->name);
        $reg = geoAddon::getRegistry($this->name);
        $tpl->assign('units', $reg->units);
        $tpl->assign('msgs', geoAddon::getText($this->auth_tag, $this->name));
        return $tpl->fetch('listing_alerts_form_fields.tpl');
    }
    public function core_update_add_listing_alert_field($vars)
    {
        $filter_id = $vars['filter_id'];
        $info = $vars['info'];
        $reg = geoAddon::getRegistry($this->name);
        $center = "listing_filter_center_" . $filter_id;
        $distance = "listing_filter_distance_" . $filter_id;

        if (is_numeric($info['zip_distance']) && $info['zip_center']) {
            $reg->$center = $info['zip_center'];
            $reg->$distance = $info['zip_distance'];
        }

        $reg->save();
    }
    public function core_delete_listing_alert($filter_id)
    {
        $reg = geoAddon::getRegistry($this->name);
        $center = "listing_filter_center_" . $filter_id;
        $distance = "listing_filter_distance_" . $filter_id;
        $reg->$center = false;
        $reg->$distance = false;
        $reg->save();
    }
    public function core_check_listing_alert($vars)
    {
        $listing_id = $vars['listing_id'];
        $filter_id = $vars['filter_id'];

        $reg = geoAddon::getRegistry($this->name);
        $center = "listing_filter_center_" . $filter_id;
        $distance = "listing_filter_distance_" . $filter_id;


        if (!$reg->$center) {
            return 'NO_DATA';
        }

        $inRange = $this->getZipsInRange($reg->$center, $reg->$distance);

        $listingZip = DataAccess::getInstance()->GetOne("SELECT `location_zip` FROM " . geoTables::classifieds_table . ' WHERE `id` = ?', array($listing_id));

        if (in_array($listingZip, $inRange)) {
            //this listing IS in range of the filter's search
            return 'MATCH';
        } else {
            //filter does not match this listing
            return 'NO_MATCH';
        }
    }
    public function core_show_listing_alert_filter_data($filter_id)
    {
        $reg = geoAddon::getRegistry($this->name);
        $center = "listing_filter_center_" . $filter_id;
        if ($reg->$center) {
            return $this->core_show_listing_alerts_table_headers() . ': ' . $this->core_show_listing_alerts_table_body($filter_id);
        }
        return '';
    }
}
