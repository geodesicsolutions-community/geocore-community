<?php

/**
* Creates the next-level region for geographic selectors.
* Call with parent=0 for level 1
* prints nothing if parent has no children
*/

// DON'T FORGET THIS
if( class_exists( 'classes_AJAX' ) or die());

class CLASSES_AJAXController_RegionSelect extends classes_AJAX
{
	public function getChildElements ()
	{
		//because clean inputs are happy inputs!
		$parent = intval($_POST['parent']); 
		$fieldName = $_POST['fieldName'];
		$maxLevel = intval($_POST['maxLevel']);
		$skipEmptyRegions = intval($_POST['skipEmptyRegions']);
		
		//do all the stuff needed to make the prevalue JSON string back into a PHP array so it plays nice with the region function
		$prevalue = array();
		$arr = (array)geoAjax::getInstance()->decodeJSON(geoString::specialCharsDecode($_POST['prevalue']));
		//at this point, array keys are Strings. make them ints so they compare right with values later
		foreach($arr as $key => $value) {
			$prevalue[intval($key)] = intval($value);
		}
		
		$tpl = new geoTemplate('system','classes');

		
		$tpl_vars = geoRegion::getRegionsFromParent($fieldName, $parent,$prevalue,$maxLevel,$skipEmptyRegions);
		if($tpl_vars === false) {
			//nothing to show for next level
			return;
		}
		$tpl->assign($tpl_vars);
		
		$out = $tpl->fetch('Region/ajax_region_select.tpl');
		echo $out;
	}
	
	public function getChildStatesForBilling ()
	{
		$country = geoRegion::getRegionIdFromAbbreviation($_POST['country']);
		$name = $_POST['name'];
		echo geoRegion::billingStateSelector($name, $country);
	}
	
	/**
	 * For adding more regions, mostly used in listing details
	 */
	public function addAdditionalRegion ()
	{
		//because clean inputs are happy inputs!
		$regionIndex = intval($_POST['regionIndex']);
		$fieldName = $_POST['fieldName']."[{$regionIndex}]";
		
		echo geoRegion::regionSelector($fieldName);
		return;
	}
}
