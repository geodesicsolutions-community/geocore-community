<?php
//addons/log_license_db/admin.php
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
####    7.5.1-8-gacdaa11
##
##################################

# Log license activities Addon

class addon_log_license_db_admin extends addon_log_license_db_info {

	var $tables;
	function addon_log_license_db_admin () {
		$this->tables = new addon_log_license_db_tables();
	}
	//function to initialize pages, to let the page loader know the pages exist.
	//this will only get run if the addon is installed and enabled.
	function init_pages () {
		//menu_page::addonAddPage($index, $parent, $title, $addon_name, $image, $type);

		menu_page::addonAddPage('addon_log_license_db_view_log','','License Log','log_license_db','fa-bar-chart');
		Notifications::addCheck(array ('addon_log_license_db_admin','checkNewImportantMsgs'));
	}

	function checkNewImportantMsgs(){
		$db = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$sql = 'SELECT COUNT(log_id) as count FROM `geodesic_license_log` WHERE `need_attention` = 1 AND `log_type` != \'notice_local\'';
		$result = $db->Execute($sql);
		if (!$result){
			//die('ERROR SQL: Sql:'.$sql.' Msg: '.$db->ErrorMsg());
			return false;
		}
		if ($result->RecordCount() > 0){
			$row = $result->FetchRow();
			if ($row['count'] > 0){
				return 'There are new <a href="?mc=admin_tools_settings&page=addon_log_license_db_view_log">License log entries</a> that might need your attention.  (Use "Mark all entries as read" to clear this notification)';
			}
		}
		return false;
	}

	//display functions, to display the admin settings.
	//Function name must be display_INDEX () where INDEX is the index specified when addonAddPage() is called.
	function display_addon_log_license_db_view_log () {
		$link = $_REQUEST;

		$resultsPerPage = 30;
		$currentPage = ($link['pg']) ? $link['pg'] : 1;
		$start = ($currentPage-1) * $resultsPerPage;


		$sql_count = 'SELECT COUNT(log_id) as count FROM '.$this->tables->license_log_table.' WHERE `need_attention` = 1';

		$db = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$total = $db->GetOne($sql_count);

		$sql = 'SELECT `log_id`,`time`,`log_type`,`message` FROM '.$this->tables->license_log_table.' WHERE `need_attention` = 1 ORDER BY `log_id` DESC LIMIT '.$start.','.$resultsPerPage;
		$result = $db->Execute($sql);
		if (!$result){
			die ('Error, sql:'.$sql.' Error: '.$db->ErrorMsg());
		}
		if ($total == 0){
			$body = '<span class="medium_font">No new license log entries.</span>';
		} else {
			$body = '<form action="" method="POST"><input type="submit" name="auto_save" value="Mark all entries as read" onclick="return confirm(\'Are you sure you want to mark all log entries as read? This will make the entries not appear anymore, but they will remain in the database.\')" /></form>';
			$body .= '<br />
<div class="table-responsive">
<table class="table table-hover table-striped table-bordered">
	<thead>
		<tr class="col_hdr_top">
			<th class="col_hdr"><strong>Log&nbsp;ID</strong></th>
			<th class="col_hdr"><strong>Date</strong></th>
			<th class="col_hdr"><strong>Log&nbsp;Type</strong></th>
			<th class="col_hdr"><strong>Connection&nbsp;Used</strong></th>
			<th class="col_hdr"><strong>Message</strong></th>
		</tr>
	</thead>
	<tbody>
	';
			$show_remote=false;
			while ($row = $result->FetchRow()){
				$row_color = ($row_color==' class="row_color1"')? ' class="row_color2"':' class="row_color1"';
				switch ($row['log_type']){
					case 'notice_local':
						$level='Notice';
						$remote='Local';
						break;
					case 'notice_remote':
						$level='Notice';
						$remote='<span style="color:red;"><sup>*</sup>&nbsp;Remote</span>';
						$show_remote=true;
						break;
					case 'error_local':
						$level='<span style="color:red;">Error</span>';
						$remote='Local';
						break;
					case 'error_remote':
						$level='<span style="color:red;">Error</span>';
						$remote='<span style="color:red;"><sup>*</sup>&nbsp;Remote</span>';
						$show_remote=true;
						break;
				}
				$body .= "
		<tr$row_color>
			<td class=\"medium_font\">{$row['log_id']}</td>
			<td class=\"medium_font\">".str_replace(' ','&nbsp;',date('m-d-Y H:i:s',$row['time']))."</td>
			<td class=\"medium_font\">{$level}</td>
			<td class=\"medium_font\">{$remote}</td>
			<td class=\"medium_font\">".preg_replace('/\[[^]]+\]/','',$row['message'])."</td>
		</tr>
	";
			}


			$totalPages = ceil($total/$resultsPerPage);
			$link = 'index.php?page=addon_log_license_db_view_log&mc=addon_cat_log_license_db&pg=';
			$pagination = ($totalPages > 1) ? geoPagination::getHTML($totalPages, $currentPage, $link) : '';

			if ($show_remote){
				$body .= '
		<tr>
			<td colspan="5"><span style="font-weight: bold; color: #FF0000;">* Remote:</span> A connection to the '.(!geoPC::is_whitelabel() ? "geodesicsolutions.com" : "master").' license server was necessary for this action.</td>
		</tr>
';
			}
			$body .= '
	</tbody>
</table>
</div>
<p class="medium_font">'.$pagination.'</p>
	';
		}
		//render the whole page.
		if (class_exists('geoView')) {
			$view = geoView::getInstance();
			$view->addBody($body);
		} else {
			adminPageAutoload::display_page($body);
		}
	}

	function update_addon_log_license_db_view_log () {
		$db = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$sql = 'UPDATE '.$this->tables->license_log_table.' SET `need_attention` = 0';
		$result = $db->Execute($sql);
		if (!$result){
			die ('Error sql:'.$sql.' Error Msg: '.$db->ErrorMsg());
		}
	}
}