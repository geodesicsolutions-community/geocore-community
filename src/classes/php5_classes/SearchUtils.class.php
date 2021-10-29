<?php
//SearchUtils.class.php
/**
 * Holds the geoSearchUtils class.
 * 
 * @package System
 * @since Version 4.0.0
 */
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    7.1.0-45-gd416b12
## 
##################################

/**
 * Class for search utilities.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoSearchUtils
{
	
	/**
	 * utility functions for doing extra stuff with the Search page
	 * 
	 * these get their own class so they can be shared between the main, "advanced" search
	 * and the search box module without duplicating code
	 */
	
	/**
	 * Shows the state/city values of the geodesic_zip_codes table in a dropdown,
	 * linked to the corresponding zipcode, which gets submitted as a radial zipsearch
	 * 
	 * does nothing if beta switch: zipsearch_by_location_name is not on
	 * 
	 * has the potential to massively slow down the site
	 * and/or break layouts if the database table is not set up to expect its use
	 * 
	 * @param bool $showResetButton
	 * @return String html for inclusion in search form.
	 * @deprecated 5.2.2 1/28/2011 (will no longer work with new imported zip data)
	 */
	public static function zipsearchByLocation($showResetButton=false)
	{
		$db = DataAccess::getInstance();
		if($db->get_site_setting('zipsearch_by_location_name') != 1) {
			return '';
		}
				
		$tpl = new geoTemplate('system','search_class');
		
		$msgs = $db->get_text(true, 44); // use search page text
		
		$tpl->assign('distanceLabel', $msgs[1950]);
		$tpl->assign('allStatesLabel', $msgs[500586]);
		$tpl->assign('allCitiesLabel', $msgs[500587]);
		
		if($showResetButton) {
			$tpl->assign('resetButtonLabel', $msgs[500588]);
			$tpl->assign('showResetButton', true);
		}
		
		$sql = "select state, city, zipcode from geodesic_zip_codes order by city asc";
		$result = $db->Execute($sql);
		if($result->RecordCount() == 0) {
			return '';
		}
		$locations = array();
		while($line = $result->FetchRow()) {
			$locations[$line['state']][$line['city']] = $line['zipcode'];
		}
		$tpl->assign('locations', $locations);
		
		$tpl->assign('basic_distances', array(1,2,3,4,5,6,7,8,9,10));
		$tpl->assign('default_distance', 10); //distance to search if no distance specified
		
		$return = $tpl->fetch('utils/zipsearch_by_location.tpl');
		return $return;
		

	}
}