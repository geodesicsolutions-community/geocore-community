<?php
// admin_payment_management_class.php
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
##
## File Changed In GIT Commit:
## ##    16.09.0-109-g68fca00
## 
##################################

class Payment_management extends Admin_site {
	function currency_designation_form($db)
	{
		$this->sql_query = "select precurrency,postcurrency from ".$this->site_configuration_table;
		$result = $db->Execute($this->sql_query);
		if (!$result)
		{
			//echo $this->sql_query."<br>\n";
			$this->error_message = $this->internal_error_message;
			return false;
		}
		else
		{
			

			$show=$result->FetchRow();
			if (!$this->admin_demo()) {
				$this->body .= "<form action=index.php?mc=payments&page=payments_currency_designation method=post class='form-horizontal form-label-left'>";
			} else {
				$this->body .= "<div class='form-horizontal'>";
			}
			$this->body .= "<fieldset id='CurrencyDesig'><legend>Currency Type You Accept from Sellers</legend><div class='x_content'>";
			
			$this->body .= "<p class='page_note'>Additional currency settings may be found on <a href='index.php?page=main_browsing_settings&mc=site_setup'>this page</a></p>";

			$this->body .= "
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Pre-Currency Symbol \"Before\": </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=h[precurrency] class='form-control col-md-7 col-xs-12' value=\"".$show["precurrency"]."\">
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Post-Currency Type \"After\": </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'><input type=text name=h[postcurrency] class='form-control col-md-7 col-xs-12' value=\"".$show["postcurrency"]."\">
			  </div>
			</div>";

			if (!$this->admin_demo()) $this->body .= "<div class='center'><input type=submit name='auto_save' value=\"Save\"></div>";

			$this->body .= "<div class='page_note'><strong>Note:</strong> Currency symbols must be specified in their ASCII code format in order to be
			displayed properly.  Please reference the ASCII codes below for your desired currency symbol. There is no special ASCII code to enter for the dollar ($) symbol.
				<div style='padding-top:20px;'><strong>Common Currency ASCII Codes:</strong>
					<ul>
						<li>&pound; British Pounds - ASCII CODE: <span class='help-note'><strong>&amp;pound;</strong></span></li>
						<li>&euro; European Euro - ASCII CODE: <span class='help-note'><strong>&amp;euro;</strong></span></li>
						<li>&yen; Japanese Yen - ASCII CODE: <span class='help-note'><strong>&amp;yen;</strong></span></li>
					</ul>
				</div>
			</div>";
			$this->body .= "</div></fieldset>";
			$this->body .= ($this->admin_demo()) ? '</div>' : '</form>';
			return true;
		}

	} //end of function currency_designation_form

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function update_currency_designation($db,$currency_type_info=0)
	{
		if ($currency_type_info)
		{
			$this->sql_query = "update ".$this->site_configuration_table." set
				precurrency = \"".$currency_type_info["precurrency"]."\",
				postcurrency = \"".$currency_type_info["postcurrency"]."\"";
			//echo $this->sql_query."<br>\n";
			$result = $db->Execute($this->sql_query);
			//clear the settings cache
			geoCacheSetting::expire('configuration_data');
			if (!$result)
			{
				//echo $this->sql_query."<br>\n";
				$this->error_message = $this->internal_error_message;
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return false;
		}
	} //end of function update_currency_designation

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	function display_payments_currency_designation()
	{
		if (PHP5_DIR) $menu_loader = geoAdmin::getInstance();
		else $menu_loader =& geoAdmin::getInstance();
		$this->body .= $menu_loader->getUserMessages();

		if (!$this->currency_designation_form($this->db))
			return false;
		$this->display_page();
	}

	function update_payments_currency_designation()
	{
		return $this->update_currency_designation($this->db,$_REQUEST["h"]);
	}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	/**
	 * pulls a report of incoming revenue given a user group and time period
	 * @param $apiData Array Supplies inputs to $data from an external source. param added in 7.3.0
	 */

	public function display_payments_revenue_report($apiData=false)
	{
		$tpl_vars = array();
		$db = DataAccess::getInstance();

		//get a list of all the user groups
		$groups = array();
		$sql = "SELECT group_id, name FROM `geodesic_groups`";
		$result = $db->Execute($sql);
		while($group = $result->FetchRow()) {
			$groups[$group['group_id']] = $group['name'];
		}
		$tpl_vars['groups'] = $groups;

		$data = false;
		if($apiData) {
			$data = $apiData;
		} elseif(isset($_REQUEST['d']) && $_REQUEST['d']) {
			$data = $_REQUEST['d'];
		}

		if($data) {

			//convert dates to ticktime
			$startTime = strtotime($data['start_date']);
			$endTime = strtotime($data['end_date']);
			if($startTime === false || $endTime === false) {
				if(!$apiData) {
					geoAdmin::m('Invalid date range specified! Reporting revenue from the last 30 days.',geoAdmin::NOTICE);
				}
				$endTime = geoUtil::time();
				$startTime = $endTime - (60*60*24*30);
			}
			//Need to make endTime at end of day to cover things happened that day
			$endTime = mktime(23,59,59,date('n',$endTime), date('j',$endTime),date('Y',$endTime));

			$tpl_vars['report_start'] = date("Y-m-d",$startTime);
			$tpl_vars['report_end'] = date("Y-m-d",$endTime);

			if(count($data['usergroups']) < 1) {
				if(!$apiData) {
					geoAdmin::m('No user group(s) selected! Reporting revenue from ALL user groups.',geoAdmin::NOTICE);
				}
				$groupsToReport = array();
				foreach($groups as $id => $g) {
					$groupsToReport[] = $id;
				}
			} else {
				$groupsToReport = $data['usergroups'];
			}

			$getGroupTransactions = $db->Prepare("SELECT t.amount, t.gateway FROM `geodesic_transaction` as t, `geodesic_user_groups_price_plans` as ugpp
				WHERE t.gateway <> 'site_fee' AND t.status = 1 AND t.date >= ? AND t.date <= ? AND t.user = ugpp.id AND ugpp.group_id = ?");
			$getGroupListings = $db->Prepare("SELECT COUNT(c.`id`) FROM `geodesic_classifieds` as c, `geodesic_user_groups_price_plans` as ugpp
				WHERE c.date >= ? AND c.date <= ? AND c.seller = ugpp.id AND ugpp.group_id = ?");

			//get the number of new users who joined during this timeframe
			$getGroupNewUsers = $db->Prepare("SELECT count(u.id) FROM `geodesic_userdata` as u, `geodesic_user_groups_price_plans` as ugpp
				WHERE u.id = ugpp.id AND u.`date_joined` >= ? AND u.`date_joined` <= ? AND ugpp.group_id = ?");
			$totalNewUsers = 0;

			$report = array();
			foreach($groupsToReport as $groupId) {

				$result = $db->Execute($getGroupTransactions, array($startTime, $endTime, $groupId));

				$total = 0;
				while($transaction = $result->FetchRow()) {
					if($transaction['gateway'] === 'account_balance') {
						//account balance does things a little differently, so it gets a special case
						if($transaction['amount'] < 0) {
							//this is an instance of someone ADDING money to his balance. This IS revenue, but needs to be made positive
							$total += abs($transaction['amount']);
						} else {
							//this is an instance of someone SPENDING money from his balance, and NOT revenue for the site.
							//do NOT add this to the total
						}
					} else {
						//this is somebody spending money on the site like normal. Add to the total.
						$total += $transaction['amount'];
					}
				}

				$totalListings = (int)$db->GetOne($getGroupListings, array($startTime, $endTime, $groupId));

				$newUsers = (int)$db->GetOne($getGroupNewUsers, array($startTime, $endTime, $groupId));
				$totalNewUsers += $newUsers;

				$report[] = array('name' => $groups[$groupId], 'total' => geoString::displayPrice($total), 'numListings' => $totalListings, 'newUsers' => $newUsers);
			}

			//sum all the other totals to get an overall total, as well
			$total = $totalNumListings = 0;
			foreach($report as $r) {
				$total += geoNumber::deformat($r['total'], true);
				$totalNumListings += $r['numListings'];
			}
			$report[] = array('name' => '<strong>Total from all selected user groups</strong>', 'total' => geoString::displayPrice($total), 'numListings' => $totalNumListings, 'newUsers' => $totalNewUsers);
			$tpl_vars['report'] = $report;
		} else {
			$tpl_vars['report'] = false;
		}


		if(!$tpl_vars['report'] && $apiData) {
			return('Error: no report available for this data');
		}

		if($data['as_csv'] == 1 && $tpl_vars['report']) {
			$csv = '';
			//header row
			$csv .= '"User Group", "Revenue", "Number of Listings", "New Registrations"'."\n";

			//make final (total) row's text a bit more friendly for the CSV
			$tpl_vars['report'][count($tpl_vars['report'])-1]['name'] = "Total for date range {$tpl_vars['report_start']} to {$tpl_vars['report_end']}";

			foreach($tpl_vars['report'] as $g) {
				$csv .= "\"{$g['name']}\",\"{$g['total']}\",\"{$g['numListings']}\",\"{$g['newUsers']}\"\n";
			}

			if($apiData) {
				//save file to server and return filename
				@unlink(ADMIN_DIR.'revenueReport.csv');
				$fp = fopen(ADMIN_DIR.'revenueReport.csv', 'w');
				if($fp === false) {
					return("Error: could not open ".ADMIN_DIR.'revenueReport.csv for writing. Check directory permissions.');
				}
				fwrite($fp,$csv);
				fclose($fp);
				return(ADMIN_DIR.'revenueReport.csv');
			} else {
				//output as CSV directly
				header('Content-type: text/csv');
				header('Content-disposition: attachment;filename=groupReport.csv');
				echo $csv;
				require GEO_BASE_DIR . 'app_bottom.php';
				exit();
			}
		} else {
			$tpl_vars['admin_msgs'] = geoAdmin::m();
			geoView::getInstance()->setBodyTpl('revenue_report.tpl')
				->setBodyVar($tpl_vars);
		}
	}


} //end of class Payment_management
