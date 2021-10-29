<?php
//addons/multi_admin/admin.php
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
## ##    16.09.0-106-ge989d1f
## 
##################################

# multi_admin Addon

class addon_multi_admin_admin {
	//function to initialize pages, to let the page loader know the pages exist.
	//this will only get run if the addon is installed and enabled.
	function init_pages () {				
		//Add an admin category, under the Addon category.
	
		//group manage
		menu_page::addonAddPage('addon_multi_admin_groups','','Admin Groups','multi_admin','fa-users');
			menu_page::addonAddPage('addon_multi_admin_group_edit','addon_multi_admin_groups', 'Edit Group Permissions','multi_admin','fa-users','sub_page');
			menu_page::addonAddPage('addon_multi_admin_group_delete','addon_multi_admin_groups', 'Delete Group','multi_admin','fa-users','sub_page');

		//add extra config
		menu_page::addonAddPage('addon_multi_admin_users','','Admin Users','multi_admin','fa-users');
			menu_page::addonAddPage('addon_multi_admin_user_edit','addon_multi_admin_users', 'Edit User Permissions','multi_admin','fa-users','sub_page');
			menu_page::addonAddPage('addon_multi_admin_user_delete','addon_multi_admin_users', 'Remove Admin Rights','multi_admin','fa-users','sub_page');
	}
	
	function update_addon_multi_admin_groups(){
		$db = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		if (isset($_POST['group_add']) && strlen(trim($_POST['group_add'])) > 0){
			//adding a new group.
			$sql = 'INSERT INTO `geodesic_addon_multi_admin_groups` (`group_id`, `name`, `display`, `update`) VALUES ( NULL, ?, ?, ? )';
			$query_data = array(trim($_POST['group_add']), serialize(array()), serialize(array()));
			$result = $db->Execute($sql, $query_data);
			if (!$result){
				geoAdmin::m('Error creating group, db query error.',geoAdmin::ERROR);
				return false;
			}
			geoAdmin::m('Added new group.  Click on the group to start editing permissions.',geoAdmin::SUCCESS);
			return true;
		}
		geoAdmin::m('Must enter a group name.',geoAdmin::ERROR);
		
		return false;
	}
	function display_addon_multi_admin_groups(){
		$db = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
		//get group info
		$sql = 'SELECT `group_id`, `name` FROM `geodesic_addon_multi_admin_groups`';
		$rows = $db->GetAll($sql);
		if ($rows === false){
			trigger_error('ERROR ADDON SQL: Sql: '.$sql.' Error: '.$db->ErrorMsg());
			geoView::getInstance()->addBody('Database query error, please try again.');
			return false;
		}
		$tpl_vars = array();
		$tpl_vars['delete_button'] = geoHTML::addButton('Delete','index.php?page=addon_multi_admin_group_delete&amp;group_id=(GROUP)&amp;auto_save=1', false, '', 'lightUpLink mini_cancel');
		
		$groups = array();
		
		foreach ($rows as $row) {
			$sql = 'SELECT count(`user_id`) as count FROM `geodesic_addon_multi_admin_users` WHERE `group_id` = '.$row['group_id'];
			$result_users = $db->GetRow($sql);
			$groups[$row['group_id']] = $row;
			$groups[$row['group_id']]['user_count'] = intval($result_users['count']);
			$html .= '
		<tr class="medium_font" style="background-color: '.$row_color.'">
			<td><a href="index.php?page=addon_multi_admin_group_edit&group_id='.$row['group_id'].'">'.$row['name'].'</a></td>
			<td align=center>'.$count.'</td>
			<td>'.geoHTML::addButton('Delete','index.php?page=addon_multi_admin_group_delete&group_id='.$row['group_id'].'&auto_save=1', false, '', 'lightUpLink mini_cancel').'</td>
		</tr>';
		}
		$tpl_vars['groups'] = $groups;
		$admin->v()->setBodyTpl('list_groups.tpl','multi_admin')
			->setBodyVar($tpl_vars);
		return;
	}
	
	function update_addon_multi_admin_group_edit(){
		$admin = geoAdmin::getInstance();
		if (!isset($_GET['group_id']) || !$_GET['group_id']){
			$admin->userError('Error: No group specified.');
			return false;
		}
		$group_id = intval($_GET['group_id']);

		$db = true;
		$session = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		if ($session->getUserId() != 1 && !geoAddon::triggerDisplay('auth_admin_display_page','SPECIAL_su', geoAddon::NOT_NULL)){
			//only main admin or Super Users can edit special settings.
			
			//Get groups current settings
			$sql = 'SELECT `display`,`update` FROM `geodesic_addon_multi_admin_groups` WHERE `group_id` = '.$group_id;
			$result = $db->Execute($sql);
			if (!$result){
				trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
				return false;
			}
			if ($result->RecordCount() != 1){
				$admin->userError('Error: Could not find group!');
				return false;
			}
			$display = array();
			$update = array();
			$blockedPages = array ('addon_multi_admin_groups','addon_multi_admin_group_edit','addon_multi_admin_group_delete',
								'addon_multi_admin_users','addon_multi_admin_user_edit','addon_multi_admin_user_delete');
			
			$row = $result->FetchRow();
			//go through fields already set in db
			foreach (unserialize($row['display']) as $key => $value){
				if (!in_array($key, $blockedPages) && menu_page::getPage($key, false)){
					//user is only allowed to add permissions for pages they can display.
					if (array_key_exists($key, $_POST['display'])){
						$display[$key] = 1;
						unset($_POST['display'][$key]);
					}
				} else {
					//user cannot see page, so they can neither add nor remove this setting.
					//it just stays the same.
					$display[$key] = $value;
				}
			}
			foreach (unserialize($row['update']) as $key => $value){
				if (!in_array($key, $blockedPages) && menu_page::getPage($key, false) && geoAddon::triggerDisplay('auth_admin_update_page',$key,geoAddon::NOT_NULL)){
					//user is only allowed to add permissions for pages they can update.
					if (array_key_exists($key, $_POST['update'])){
						$update[$key] = 1;
						unset($_POST['update'][$key]);
					}
				} else {
					//user cannot see page, so they can neither add nor remove this setting.
					//it just stays the same.
					$update[$key] = $value;
				}
			}
			
			//go through each new permission that wasn't set before.
			if (isset($_POST['display']) && is_array($_POST['display'])){
				foreach ($_POST['display'] as $key => $value){
					if (!in_array($key, $blockedPages) && menu_page::getPage($key, false)){
						$display[$key] = 1;
					}
				}
			}
			if (isset($_POST['update']) && is_array($_POST['update'])){
				foreach ($_POST['update'] as $key => $value){
					if (!in_array($key, $blockedPages) && menu_page::getPage($key, false) && geoAddon::triggerDisplay('auth_admin_update_page',$key,geoAddon::NOT_NULL)){
						$update[$key] = 1;
					}
				}
			}
			//serialize to put into db
			$display = serialize($display);
			$update = serialize($update);
		} else {
			//User is main admin, or a super user, so use the settings directly as they've been entered.
			//serialize to put into db
			$display = (isset($_POST['display']) && is_array($_POST['display']))? serialize($_POST['display']): serialize(array());
			$update = (isset($_POST['update']) && is_array($_POST['update']))?serialize($_POST['update']): serialize(array());
		}
		
		
		
		$sql = 'UPDATE `geodesic_addon_multi_admin_groups` SET `display`=?, `update`=? WHERE `group_id` = '.$group_id;
		$result = $db->Execute($sql, array($display, $update));
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess('Saved changes.');
		
		return true;
	}
	function edit_permissions($group_data){
		$db = $session = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		
		if (isset($group_data['name'])){
			$group_name = $group_data['name'];
		} else {
			$group_name = $group_data['username'];
		}
		$group_display = unserialize($group_data['display']);
		$group_update = unserialize($group_data['update']);
		
		$page_loader = geoAdmin::getInstance();
		
		$tpl = new geoTemplate('addon','multi_admin');
		
		$tpl->assign('top_categories', $page_loader->getMenuTemplateVars());
		$tpl->assign('update_permissions', $group_update);
		$tpl->assign('display_permissions', $group_display);
		//the special cases...
		
		if ($session->getUserId() != 1) {
			//this is not the main user.
			//only allowed to edit special permissions if main admin.
			$tpl->assign('special', false);
		} else {
			$tpl->assign('special', true);
			
			$special_pages_list = array (
				'SPECIAL_su'=>'Super User',
				//'SPECIAL_demo' => 'Demo User',
				'SPECIAL_edit_listings_client_side' => 'Edit Listings (client side)',
				'SPECIAL_delete_listings_client_side' => 'Delete Listings (client side)'
			);
			if (geoPC::is_ent()) {
				$special_pages_list['SPECIAL_admin_user_login'] = 'Log in as Any User (client side)';
			}
			$tpl->assign('special_pages', $special_pages_list);
			
			//figure out if there are pages that are not known about
			$no_pages = array();
			foreach ($group_display as $index => $val){
				if (!key_exists($index, $special_pages_list) && menu_page::getPage($index, false) == false){
					$no_pages[] = $index;
				}
			}
			$tpl->assign('no_pages', $no_pages);
		}
		
		
		return $tpl->fetch('permissions_table.tpl');
	}
	function display_addon_multi_admin_group_edit(){
		if (!isset($_GET['group_id']) || !$_GET['group_id']){
			geoView::getInstance()->addBody('Error: No group specified.');
			return true;
		}
		$group_id = intval($_GET['group_id']);
		$db = true;
		$session = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
		$sql = 'SELECT `name`, `display`, `update` FROM `geodesic_addon_multi_admin_groups` WHERE `group_id` = '.$group_id;
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			geoView::getInstance()->addBody('Error:  Problem with db query, please try again.');
			return true;
		}
		if ($result->RecordCount() != 1){
			//err what?
			$admin->v()->addBody('Error: Can not find the specified group.');
			return true;
		}
		$group_data = $result->FetchRow();
		$html = $admin->getUserMessages().'
<form method="post" action="" id="privForm">
<div class="group_price_hdr" style="padding: 5px;">Permissions for Admin Group: '.$group_data['name'].'
</div>
<fieldset id="AdminPermissions"><legend>Admin Group Permissions</legend><div>
<div class="page_note"><strong>Important:</strong> After setting the privileges for this Admin Group, please log in to the Admin Panel as an Admin User of this Group to ensure that the permissions settings you have specified are working as you intended.<br /><br />
Also note that changes made here <strong>affect all admin users attached to this group</strong>.</div>
'.$this->edit_permissions($group_data).'
</div></fieldset></form>';
		//use display page so we can easily customize breadcrumb title
		geoAdmin::display_page($html, ' ('.$group_data['name'].')' );
	}
	
	function display_addon_multi_admin_users(){
		$db = true;
		$session = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
		//get group info
		$thisAddon = geoAddon::getUtil('multi_admin');
		$sql = 'SELECT multi.user_id, multi.group_id, multi.display, multi.update, user.username FROM `geodesic_addon_multi_admin_users` as `multi`, `geodesic_userdata` as `user` WHERE multi.user_id = user.id';
		$groupDropdown = 0;
		if ($session->getUserId != 1){
			$thisAddon->init();
			if (isset($thisAddon) && !in_array('SPECIAL_su',$thisAddon->display)){
				//not super user, add restriction for groups that can be added.
				$groupDropdown = $thisAddon->group_id;
			}
		}
		$rows = $db->GetAll($sql);
		if ($rows === false) {
			trigger_error('ERROR ADDON SQL: Sql: '.$sql.' Error: '.$db->ErrorMsg());
			geoAdmin::display_page('Database query error, please try again.');
			return false;
		}
		$tpl_vars = array();
		$tpl_vars['delete_button'] = geoHTML::addButton('Delete','index.php?page=addon_multi_admin_user_delete&amp;user_id=(USER)&amp;auto_save=1', false, '', 'lightUpLink mini_cancel');
		$users = array();
		
		$group_name = array(0=>'None');
		foreach ($rows as $row) {
			if (!isset($group_name[$row['group_id']])){
				$sql = 'SELECT `name` FROM `geodesic_addon_multi_admin_groups` WHERE `group_id` = '.$row['group_id'];
				$row_gname = $db->GetRow($sql);
				if ($row_gname === false){
					trigger_error('ERROR ADDON SQL: Sql: '.$sql.' Error: '.$db->ErrorMsg());
					geoAdmin::display_page('Database query error, please try again.<br />'.$db->ErrorMsg());
					return false;
				}
				$group_name[$row['group_id']] = $row_gname['name'];
			}
			$users [$row['user_id']] = $row;
			$users [$row['user_id']]['group_name']= $group_name[$row['group_id']];
		}
		$tpl_vars['users'] = $users;
		$tpl_vars['group_dropdown'] = $this->createGroupDropdown(0, $groupDropdown);
		$tpl_vars['admin_messages'] = geoAdmin::m();
		$view = geoView::getInstance();
		$view->setBodyTpl('list_users','multi_admin');
		$view->setBodyVar($tpl_vars);
	}
	function createGroupDropdown($group_id = 0, $onlyUse = null){
		$db = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
		//create group dropdown
		$sql = 'SELECT `group_id`,`name` FROM `geodesic_addon_multi_admin_groups`';
		if ($onlyUse!==null){
			$sql .= ' WHERE `group_id` = '.$onlyUse;
		}
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR ADDON SQL: Sql: '.$sql.' Error: '.$db->ErrorMsg());
			geoAdmin::display_page('Database query error, please try again.');
			return false;
		}
		$group_dropdown = '
<select name="group_id" class="form-control">
	<option value="0">None</option>
';
		while ($row = $result->FetchRow()){
			$group_dropdown .= '
	<option value="'.$row['group_id'].'"'.(($row['group_id'] == $group_id)? ' selected="selected"':'').'>'.$row['name'].'</option>';
			
		}
		$group_dropdown .= '
</select>
';
		return $group_dropdown;
	}
	function update_addon_multi_admin_users(){
		$db = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
		$user_id = (isset($_POST['user_add']))? trim($_POST['user_add']): 0;
		if (!$user_id || strlen($user_id) == 0) {
			$admin->userError('User\'s username or user ID required.');
			return false;
		}
		$user = geoUser::getUser($_POST['user_add']);
		$user_id = (is_object($user))? $user->id: 0;
		if ($user_id == 0) {
			$admin->userError('The username/ID entered could not be found.  Note that the user must first exist as a "normal" user, see the manual for more details.');
			return false;
		}
		
		//adding a new user.
		$sql = 'REPLACE INTO `geodesic_addon_multi_admin_users` (`user_id`, `group_id`, `display`, `update`) VALUES ( ?, ?, ?, ? )';
		$query_data = array($user_id, intval($_POST['group_id']), serialize(array()), serialize(array()));
		$result = $db->Execute($sql, $query_data);
		if (!$result){
			$admin->userError('DB Query Error, please try again.'.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess('Saved changes.');
		
		return true;
	}
	function display_addon_multi_admin_user_edit(){
		if (!isset($_GET['user_id']) || !$_GET['user_id']){
			geoAdmin::display_page('Error: No user specified.');
			return true;
		}
		$user_id = intval($_GET['user_id']);
		$db = true;
		$session = true;
		$addon = true;
		include (GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
				
		$sql = 'SELECT user.username, multi.`display`, multi.`update`, multi.`group_id` FROM `geodesic_addon_multi_admin_users` as multi, `geodesic_userdata` as user WHERE multi.`user_id` = '.$user_id.' AND multi.user_id = user.id';
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			geoAdmin::display_page('Error:  Problem with db query, please try again.'.$db->ErrorMsg());
			return true;
		}
		if ($result->RecordCount() != 1){
			//err what?
			geoAdmin::display_page('Error: Can not find the specified user.');
			return true;
		}
		$user_data = $result->FetchRow();
		if ($session->getUserId() == 1 || geoAddon::triggerDisplay('auth_admin_display_page','SPECIAL_su', geoAddon::NOT_NULL) ){
			$dropdown = '
			<fieldset>
			<legend>Admin Group</legend><div>
			<div class="leftColumn">Inherit Permissions from Admin Group:</div>
			<div class="rightColumn">'.$this->createGroupDropdown($user_data['group_id']).'</div>
			<div style="float: right;"><input type="submit" name="auto_save" value="Save Changes" /></div>
			</div></fieldset>';
		} else {
			$dropdown = '';
		}
		$html = $admin->getUserMessages().'
<form method="post" action="" id="privForm">
<div class="group_price_hdr" style="padding: 5px;">Individual Permissions for User: '.$user_data['username'].' <span class="medium_font_light">(In addition to group permissions)</span></div>
<br />
'.$dropdown.'
<fieldset id="AdminPermissions"><legend>Admin User Permissions</legend><div>
<div class="page_note" style="text-align: left; padding: 3px;"><strong>IMPORTANT:</strong> Unless the Admin Group is set to <em>None</em> above, all user permissions set below are <strong>in addition to</strong> the permissions set for the Admin Group. If you want this Admin User
to have the Admin Group default settings, make sure all User Permissions below are <strong>unchecked</strong>.</div>
'.$this->edit_permissions($user_data).'
</div></fieldset>
</form>';
		//use display page so we can easily add to breadcrumb title.
		geoAdmin::display_page($html, ' ('.$user_data['username'].')');
	}
	function update_addon_multi_admin_user_edit(){
		if (!isset($_GET['user_id']) || !$_GET['user_id']){
			geoAdmin::display_page('Error: No user specified.');
			return true;
		}
		$user_id = intval($_GET['user_id']);
		
		$db = true;
		$session = true;
		$addon = true;
		include(GEO_BASE_DIR.'get_common_vars.php');
		$admin = geoAdmin::getInstance();
		if ($session->getUserId() != 1 && !geoAddon::triggerDisplay('auth_admin_display_page','SPECIAL_su', geoAddon::NOT_NULL)){
			//only main admin or Super Users can edit special settings.
			$group_sql = '';
			//Get groups current settings
			$sql = 'SELECT `display`,`update`, `group_id` FROM `geodesic_addon_multi_admin_users` WHERE `user_id` = '.$user_id;
			$result = $db->Execute($sql);
			if (!$result){
				trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
				return false;
			}
			if ($result->RecordCount() != 1){
				$admin->userError('Error: Could not find user!');
				return false;
			}
			$display = array();
			$update = array();
			$blockedPages = array ('addon_multi_admin_groups','addon_multi_admin_group_edit','addon_multi_admin_group_delete',
								'addon_multi_admin_users','addon_multi_admin_user_edit','addon_multi_admin_user_delete');
			$row = $result->FetchRow();
			//go through fields already set in db
			foreach (unserialize($row['display']) as $key => $value){
				if (!in_array($key, $blockedPages) && menu_page::getPage($key, false)){
					//user is only allowed to add permissions for pages they can display.
					if (array_key_exists($key, $_POST['display'])){
						$display[$key] = 1;
						unset($_POST['display'][$key]);
					}
				} else {
					//user cannot see page, so they can neither add nor remove this setting.
					//it just stays the same.
					$display[$key] = $value;
				}
			}
			foreach (unserialize($row['update']) as $key => $value){
				if (!in_array($key, $blockedPages) && menu_page::getPage($key, false) && geoAddon::triggerDisplay('auth_admin_update_page',$key,geoAddon::NOT_NULL)){
					//user is only allowed to add permissions for pages they can update.
					if (array_key_exists($key, $_POST['update'])){
						$update[$key] = 1;
						unset($_POST['update'][$key]);
					}
				} else {
					//user cannot see page, so they can neither add nor remove this setting.
					//it just stays the same.
					$update[$key] = $value;
				}
			}
			
			//go through each new permission that wasn't set before.
			if (isset($_POST['display']) && is_array($_POST['display'])){
				foreach ($_POST['display'] as $key => $value){
					if (!in_array($key, $blockedPages) && menu_page::getPage($key, false)){
						$display[$key] = 1;
					}
				}
			}
			if (isset($_POST['update']) && is_array($_POST['update'])){
				foreach ($_POST['update'] as $key => $value){
					if (!in_array($key, $blockedPages) && menu_page::getPage($key, false) && geoAddon::triggerDisplay('auth_admin_update_page',$key,geoAddon::NOT_NULL)){
						$update[$key] = 1;
					}
				}
			}
			//serialize to put into db
			$display = serialize($display);
			$update = serialize($update);
			$group_id = $row['group_id'];
			$query_data = array($display, $update);
		} else {
			//User is main admin, or a super user, so use the settings directly as they've been entered.
			//serialize to put into db
			$group_sql = ', `group_id`=?';
			$display = (isset($_POST['display']) && is_array($_POST['display']))? serialize($_POST['display']): serialize(array());
			$update = (isset($_POST['update']) && is_array($_POST['update']))? serialize($_POST['update']): serialize(array());
			$group_id = (isset($_POST['group_id']))? $_POST['group_id']: 0;
			$query_data = array ($display, $update, $group_id);
		}
		
		$sql = 'UPDATE `geodesic_addon_multi_admin_users` SET `display`=?, `update`=?'.$group_sql.' WHERE `user_id` = '.$user_id;
		$result = $db->Execute($sql, $query_data);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess('Saved change.');
		return true;
	}
	
	//delete functions

	function display_addon_multi_admin_group_delete (){
		$this->display_addon_multi_admin_groups();
	}
	function update_addon_multi_admin_group_delete (){
		$db = true;
		$group_id = intval($_POST['group_id']);
		if ($group_id == 0){
			return false;
		}
		include GEO_BASE_DIR.'get_common_vars.php';
		$admin = geoAdmin::getInstance();
		$sql = 'DELETE FROM `geodesic_addon_multi_admin_groups` WHERE `group_id` = '.$group_id;
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			return false;
		}
		
		$sql = 'UPDATE `geodesic_addon_multi_admin_users` SET `group_id`= 0 WHERE `group_id` = '.$group_id;
		$db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess('Removed admin group, and un-assigned group from any users attached to the admin group.');
		return true;
	}
	function display_addon_multi_admin_user_delete (){
		$this->display_addon_multi_admin_users();
	}
	function update_addon_multi_admin_user_delete (){
		$db = true;
		$user_id = intval($_POST['user_id']);
		if ($user_id == 0){
			return false;
		}
		include GEO_BASE_DIR.'get_common_vars.php';
		$admin = geoAdmin::getInstance();
		$sql = 'DELETE FROM `geodesic_addon_multi_admin_users` WHERE `user_id` = '.$user_id;
		$result = $db->Execute($sql);
		if (!$result){
			trigger_error('ERROR SQL ADDON: Sql: '.$sql.' ERROR: '.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess('Removed user\'s admin rights.');
		return true;
	}
}
