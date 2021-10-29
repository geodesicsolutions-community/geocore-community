<?php

// DON'T FORGET THIS
if(class_exists( 'admin_AJAX' ) or die());

class ADMIN_AJAXController_bidIncrements extends admin_AJAX {
	public function editIncrement ()
	{
		$db = DataAccess::getInstance();
		
		$low = ''.$_POST['low'];

		if (isset($_POST['newLow']) && geoNumber::deformat($_POST['newLow']) > 0.00) {
			//setting new minimum
			$checkVar = 'low';
			$value = geoNumber::deformat($_POST['newLow']);
			
			if ($value != $low) {
				//needs to be unique
				$matches = (int)$db->GetOne("SELECT COUNT(*) FROM ".geoTables::increments_table." WHERE `low`=?", array($value));
				if ($matches > 0) {
					echo "Duplicate increment bracket found!  Cannot change bracket starting at ".geoString::displayPrice($low,'','')." to ".geoString::displayPrice($value,'','');
					return;
				}
			}
			$message = "Successfully changed/moved bracket starting at ".geoString::displayPrice($low,'','')." to start at ".geoString::displayPrice($value,'','');
			//when low changes, will need to re-load the table...
		} else if (isset($_POST['newIncrement'])) {
			$checkVar = 'increment';
			$value = geoNumber::deformat($_POST['newIncrement']);
			if ($value <= 0.00) {
				$value = 0.01;
			}
			$message = geoString::displayPrice($value,'','');
		} else {
			//just a failsafe
			echo 'Invalid Data, Refresh Page and Try Again.';
			return;
		}
		
		$sql = "UPDATE ".geoTables::increments_table." SET `{$checkVar}`=? WHERE `low`=? LIMIT 1";

		$result = $db->Execute($sql, array($value, $low));

		if (!$result) {
			echo "DB Error!";//.$db->ErrorMsg();
			return;
		}
		$this->_fixLowestBracket();
		echo $message;
	}

	public function updateTable ()
	{
		$tpl = new geoTemplate(geoTemplate::ADMIN);

		$db = DataAccess::getInstance();

		$tpl_vars = array();
		if (isset($_GET['message'])) {
			geoAdmin::m($_GET['message'], geoAdmin::NOTICE);
			$tpl_vars['adminMsgs'] = geoAdmin::m();
		}
		$tpl_vars['increments'] = $db->GetAll("SELECT * FROM ".geoTables::increments_table." ORDER BY `low`");

		$tpl_vars['precurrency'] = $db->get_site_setting('precurrency');
		$tpl_vars['postcurrency'] = $db->get_site_setting('postcurrency');
		
		$tpl_vars['inAjax'] = true;
		
		$tpl->assign($tpl_vars);
		
		echo $tpl->fetch('bid_increments.tpl');
	}
	
	private function _fixLowestBracket ()
	{
		$db = DataAccess::getInstance();
		
		$currentLowest = $db->GetOne ("SELECT MIN(`low`) FROM `geodesic_auctions_increments`");
		
		if ($currentLowest === null || $currentLowest === false) {
			//insert increment, somehow the table is empty
			$db->Execute("INSERT INTO `geodesic_auctions_increments` (`low`, `increment`) VALUES (0.00, 5.00)");
		} else if ($currentLowest != 0.00) {
			$db->Execute("UPDATE `geodesic_auctions_increments` SET `low`=0.00 WHERE `low`='{$currentLowest}' LIMIT 1");
		}
	}
}