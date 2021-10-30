<?php



$redirectURL = 'aff.php';
require_once ('app_top.main.php');

$debug = 0;

function go_to_classifieds($redirect_from = 0) {
	$db = DataAccess::getInstance();
	$classifieds_url = $db->get_site_setting('classifieds_url');
	if ($redirect_from) {
		header("Location: " .
		$classifieds_url . "?" . $_SERVER["QUERY_STRING"] . "&redirect_from=" . $redirect_from . "&aff_redirect_id=" . $_REQUEST["aff"]);
	} else {
		header("Location: " .
		$classifieds_url . "?" . $_SERVER["QUERY_STRING"]);
	}
	include GEO_BASE_DIR . 'app_bottom.php';
	exit;
} //end of function go_to_classifieds

if (isset ($_REQUEST["aff"])) {
	$sql_query = "select * from geodesic_user_groups_price_plans where id = ?";
	$aff_group_result = $db->Execute($sql_query, array (
		$_REQUEST['aff']
	));
	if (!$aff_group_result) {
		if ($debug)
			echo $sql_query . "<br>\n";
		return false;
	} elseif ($aff_group_result->RecordCount() == 1) {
		$show_group = $aff_group_result->FetchRow();
		$sql_query = "select * from geodesic_groups where group_id = ?";
		if ($debug)
			echo $sql_query . " is the query<br>\n";
		$group_result = $db->Execute($sql_query, array ($show_group["group_id"]));
		if (!$group_result) {
			if ($debug)
				echo $sql_query . "<br>\n";
			return false;
		} elseif ($group_result->RecordCount() == 1) {
			$show_affiliate = $group_result->FetchRow();
			if ($show_affiliate["affiliate"]) {
				//init the session
				$session = geoSession::getInstance();

				$session->cleanSessions();
				$session->initSession();
				
				$affiliate_id = $_REQUEST["aff"];
				$affiliate_group_id = $show_group["group_id"];
			} else {
				go_to_classifieds( 5);
			}
		} else {
			go_to_classifieds( 6);
		}
	} else {
		go_to_classifieds( 7);
	}
} else {
	go_to_classifieds( 8);
}

//if it gets this far, it is valid AFF page, so let view class know
$view = geoView::getInstance();
$view->isAffiliatePage = 1;
$view->affiliate_id = (int)$affiliate_id;
$view->affiliate_group_id = (int)$affiliate_group_id;

//echo $affiliate_group_id." is group id in aff<Br>\n";