<?php

class addon_anonymous_listing_util
{
    private $db;

    public function __construct()
    {
        $this->db = DataAccess::getInstance();
    }

    /*  add anonymous edit header, if user is not logged in */
    public function core_Browse_ads_display_browse_result_addHeader($vars)
    {
        if (geoSession::getInstance()->getUserId() != 0) {
            //this user is logged in, and hence is not anonymous -- nothing to do here
            return false;
        }
        $text = geoAddon::getText('geo_addons', 'anonymous_listing');
        $header = $text['browseHeader'];
        //expected to be array of arrays
        $columns[] = array ('text' => $header,'label' => $header);
        return $columns;
    }

    /*  this function adds edit buttons seen when browsing ads and not logged in,
     *  so an anonymous user may click to edit a listing
     */

    public function core_Browse_ads_display_browse_result_addRow($vars)
    {
        $show_classifieds = $vars['show_classifieds'];

        if (geoSession::getInstance()->getUserId() != 0) {
            //someone logged in, don't display data
            return false;
        }

        if ($this->isAnonymous($show_classifieds['id'])) {
            $db = DataAccess::getInstance();
            $html = "<a href=\"" . $db->get_site_setting('classifieds_file_name')
                . "?a=cart&amp;action=new&amp;main_type=listing_edit&amp;listing_id=" . $show_classifieds['id'] . "\">
						<img src=\"" . geoTemplate::getUrl('', 'images/buttons/listing_edit.gif') . "\" />
					</a>";
        } else {
            $html = '&nbsp;';
        }
        //expected to be array of texts
        return array ($html);
    }

    public function isAnonymous($listing_id)
    {
        $sql = "select listing_id, password from geodesic_addon_anonymous_listing where listing_id = ?";
        $result = $this->db->Execute($sql, array($listing_id));

        if ($result->RecordCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkPass($listing_id, $password)
    {
        $sql = "select listing_id, password from geodesic_addon_anonymous_listing where listing_id = ?";
        $result = $this->db->Execute($sql, array($listing_id));

        $line = $result->FetchRow();

        if (strcmp($line['password'], $password) === 0) {
            //passwords match
            return true;
        } else {
            return false;
        }
    }

    public function createPassword()
    {
        return substr(md5(uniqid(rand(), true)), 0, 10);
    }
}
