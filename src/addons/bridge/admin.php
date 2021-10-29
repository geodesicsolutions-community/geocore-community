<?php
//addons/example/info.php
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
## ##    16.09.0-96-gf3bd8a1
## 
##################################

# Bridge

class addon_bridge_admin extends addon_bridge_info {
	function init_pages(){
		menu_page::addonAddPage('bridge_manage', '', 'Bridges', 'bridge', 'fa-compress');
			menu_page::addonAddPage('bridge_add', 'bridge_manage', 'Add Bridge', 'bridge', 'fa-compress','sub_page');
			menu_page::addonAddPage('bridge_edit','bridge_manage','Edit Bridge','bridge','fa-compress','sub_page');
			menu_page::addonAddPage('bridge_delete','bridge_manage','Delete Bridge','bridge','fa-compress','sub_page');
			menu_page::addonAddPage('bridge_sync','bridge_manage','Sync Users','bridge','fa-compress','sub_page');
			menu_page::addonAddPage('bridge_test','bridge_manage','Test Bridge Settings','bridge','fa-compress','sub_page');
			menu_page::addonAddPage('bridge_install_type_info','bridge_manage','Bridge Type Information','bridge','fa-compress','sub_page');
	}

	function display_bridge_manage(){
		$db = $admin = true;
		include(GEO_BASE_DIR.'get_common_vars.php');

		$head='';
		//get all the current installations.
		$html = $admin->getUserMessages().'
<fieldset>
	<legend>Current Bridge Installations</legend><div>
		';
		$active_installs = array();
		$sql = 'SELECT `id`,`active`,`type`,`name` FROM `geodesic_bridge_installations`';
		$result = $db->Execute($sql);
		if (!$result){
			$admin->userError('DB Error, '.$db->ErrorMsg());
		} elseif ($result->RecordCount() > 0) {

			$html .= '
	<form method="POST" action="" id="activeBridgeForm" class="form-horizontal form-label-left">
	<table style="border:none; padding: 2px; margin-bottom:10px;" class="medium_font">
		<thead>
			<tr>
				<th class="col_hdr">Active</th>
				<th class="col_hdr_left">Name</th>
				<th class="col_hdr_left">Type</th>
				<th class="col_hdr_left">Status</th>
				<th class="col_hdr">Test Installation</th>
				<th class="col_hdr">Edit</th>
				<th class="col_hdr">Delete</th>
			</tr>
		</thead>
		<tbody>';
			$color = 'row_color1';
			while ($row = $result->FetchRow()){
				$color=($color=='row_color1')? 'row_color2': 'row_color1';
				$type = $row['type'];
				$status = $row['active'];
				$id = intval($row['id']);
				$test = ' --';
				//check inputs

				$filename = ADDON_DIR.'bridge/bridges/'.$type.'.php';
				if (!file_exists($filename)){
					$status = 'Inactive<br />(Bridge file not found! )';
					$type = $filename;
				} else {
					include_once($filename);
					if (!class_exists('bridge_'.$type)){
						$status = 'Inactive<br />(Bridge file mis-configured! )';
						$type = $filename;
					} else {
						$classname = 'bridge_'.$type;
						$install = new $classname();
						if ($status && (method_exists($install, 'importUsers') || method_exists($install, 'exportUsers'))){
							//add to list of stuff we can sync with
							$active_installs[$id] = $row['name'];
						}
						if (method_exists($install, 'test_settings')){
							$test = geoHTML::addButton('Test','index.php?page=bridge_test&id='.$id);
						}
						$status = ($status)? 'Active': 'Inactive';
						$type = $install->name;

					}
				}



				$html .= "
			<tr class='$color'>
				<td>
					<input type=\"checkbox\" name=\"active[{$id}]\" value=\"1\" ".(($status == 'Active')? 'checked="checked" ': '').(($status == 'Active' || $status == 'Inactive')? 'onchange="javascript:document.getElementById(\'activeBridgeForm\').submit()" ': 'disabled="disabled" ')."/>
				</td>
				<td>{$row['name']}</td>
				<td>{$type}</td>
				<td>{$status}</td>
				<td style='text-align:center;'>{$test}</td>
				<td style='text-align:center;'>".geoHTML::addButton('Edit',"index.php?page=bridge_edit&id={$id}")."</td>
				<td style='text-align:center;'>".geoHTML::addButton('Delete',"index.php?page=bridge_delete&id={$id}&auto_save=1", false, '', 'lightUpLink mini_cancel')."</td>
			</tr>";
			}
			$html .= '
		</tbody>
	</table>
	<div style="text-align:left;">'.geoHTML::addButton('Add New Installation','index.php?page=bridge_add').'
		<input type="hidden" name="auto_save" value="Save Changes" class="medium_font" />
	</div>
	</form>';

		} else {
			$html .= '<div class="page_note_error">No bridge installations found.</div>
<div style="text-align:center;"><a href="index.php?page=bridge_add" class="mini_button">Add new installation</a></div>';
		}

		$html .= '
</div></fieldset>';

		//show import/export section
		$html .= '
<fieldset>
	<legend>Sync Users</legend><div>';
		if (count($active_installs)){
			$head .= '
<script type="text/javascript">
function gotoSync(){
	var syncId = document.getElementById("syncDropDown");
	if (syncId.value == "Choose Installation"){
		return 0;
	}
	window.location="index.php?page=bridge_sync&install_id="+syncId.value;
}
</script>';
			$html .= '
	<div class="leftColumn">Installation to sync users with:</div>
	<div class="rightColumn">
		<select name="id" onchange="javascript:gotoSync()" id="syncDropDown">
			<option>Choose Installation</option>';//</div>';
			foreach ($active_installs as $key=>$value){
				$html .= '
			<option value="'.$key.'">'.$value.'</option>';
			}
			$html .= '
		</select>
	</div>';
		} else {
			$html .= '<div class="page_note_error">No active bridge installations found that have Sync capabilities.</div>';
		}


		$html .= '
</div></fieldset>';

		//installation type info
		$html .= '
<fieldset>
	<legend>Available Bridge Types to Set Up</legend><div class="table-responsive">
	<table style="margin-bottom:10px;" class="table table-hover table-striped table-bordered" cellpadding="0" cellspacting="0" class="medium_font" >
		<thead>
			<tr class="col_hdr_top">
				<th class="col_hdr">Bridge Type</th>
				<th class="col_hdr">Import Users <em>FROM</em> Bridge</th>
				<th class="col_hdr">Export Users <em>TO</em> Bridge</th>
				<th class="col_hdr">Create User</th>
				<th class="col_hdr">Edit User</th>
				<th class="col_hdr">User Login</th>
				<th class="col_hdr">User Logout</th>
			</tr>
		</thead>
		<tbody>
		'.$this->getBridgeTable().'
		</tbody>
	</table>
</div></fieldset><div class="clearColumn"></div>
';

		//API Installations found
		$html .= $this->api_settings();
		$admin->v()->addBody($html)->addTop($head);
	}

	function update_bridge_manage(){
		$db = $admin = true;
		include (GEO_BASE_DIR.'get_common_vars.php');

		//first, get all the current bridge installations.
		if (isset($_POST['active']) && is_array($_POST['active']) && count($_POST['active'])){
			$active = array();
			foreach ($_POST['active'] as $id => $value){
				$active[intval($id)] = '?';
			}
			$sql = 'UPDATE `geodesic_bridge_installations` SET `active`=1 WHERE `id` in ('.implode(', ',$active).')';
			$result = $db->Execute($sql, array_keys($active));
			if (!$result){
				$admin->userError('Db error, '.$db->ErrorMsg());
				return false;
			}

			$sql = 'UPDATE `geodesic_bridge_installations` SET `active`=0 WHERE `id` NOT IN ('.implode(', ',$active).')';
			$result = $db->Execute($sql, array_keys($active));
			if (!$result){
				$admin->userError('Db error, '.$db->ErrorMsg());
				return false;
			}
		} else {
			$sql = 'UPDATE `geodesic_bridge_installations` SET `active`=0';
			$result = $db->Execute($sql);
			if (!$result){
				$admin->userError('Db error, '.$db->ErrorMsg());
				return false;
			}
		}
		$admin->userSuccess('Updated active status on installations.');
		return true;
	}

	function display_bridge_edit(){
		$admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		if (!isset($_GET['id'])){
			$admin->userError('Invalid selection!');
			return $this->display_bridge_manage();
		}
		$html = $admin->getUserMessages().$this->settingsForm($_GET['id']);
		$admin->v()->addBody($html);
	}
	function update_bridge_edit(){
		//save changes...
		return ($this->update_bridge_add());
	}

	function display_bridge_add(){
		$admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		if (!isset($_POST['name'])){
			$html = $admin->getUserMessages().$this->typeForm();
		} else {
			$html = $admin->getUserMessages().$this->settingsForm();
		}
		$admin->v()->addBody($html);
	}

	function update_bridge_add(){
		$db = $admin = true;
		include (GEO_BASE_DIR.'get_common_vars.php');

		$name = (isset($_POST['name']))? $_POST['name']: '';
		$type = (isset($_POST['type']))? $_POST['type']: '';
		$id = (isset($_POST['id']))? intval($_POST['id']): '';
		$email = (isset($_POST['email']))? trim($_POST['email']): '';
		//check inputs
		if (strlen(trim($name)) == 0){
			$admin->userError('Please re-enter a valid <em>Installation Name</em>.');
			return $this->typeForm();
		}
		$filename = ADDON_DIR.'bridge/bridges/'.$type.'.php';
		if (!file_exists($filename)){
			$admin->userError('Invalid installation type!');
			return $this->typeForm();
		}
		include_once($filename);
		if (!class_exists('bridge_'.$type)){
			$admin->userError('Installation type syntax error!');
			return $this->typeForm();
		}
		$classname = 'bridge_'.$type;
		$install = new $classname();

		//validate all the setings entered...

		$settings = $_POST['settings'];

		foreach ($install->settings as $key => $val){
			//make sure all checkboxes that are un-checked still get set, to empty string.
			if ($val == 'checkbox' && !isset($settings[$key])){
				$settings[$key] = '';//if not checked, set setting to be blank.
			}
		}
		//make sure settings are ok'd by bridge
		if (method_exists($install, 'checkSettings')){
			if (!$install->checkSettings($settings)){
				//if checkSettings exists and returns false, settings are invalid...
				return false;
			}
		}
		//serialize settings
		$set = serialize($settings);
		$active = (isset($_POST['active']) && $_POST['active'])? 1: 0;
		if ($id){
			//edit
			$sql = 'UPDATE `geodesic_bridge_installations` SET `active`=?, `name`=?, `email`=?, `settings`=? WHERE `id`=? LIMIT 1';
			$query_data = array($active,$name,$email,$set, $id);
		} else {
			//insert new
			$sql = 'INSERT INTO `geodesic_bridge_installations`  ( `id` , `active` , `type` , `name` , `email`, `settings` )
			VALUES (?, ?, ?, ?, ?, ?)';
			$query_data = array('',$active,$type,$name,$email, $set);
		}

		$result = $db->Execute($sql,$query_data);
		if (!$result){
			$admin->userError('DB Query error, '.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess(($id)? 'Settings saved!': 'Installation added!');
		if (!$id) {
			unset($_POST['name']); //unset name so the list will be shown.
		}
		return true;
	}
	function display_bridge_delete(){
		$this->display_bridge_manage();
	}
	function update_bridge_delete(){
		$db = $admin = true;
		include (GEO_BASE_DIR.'get_common_vars.php');

		//verify inputs
		$id = intval($_POST['id']);
		if ($id < 1){
			//nogo on id
			$admin->userError("Invalid ID!");
			return false;
		}
		$sql = "DELETE FROM `geodesic_bridge_installations` WHERE `id`=? LIMIT 1";
		$result = $db->Execute($sql, array($id));
		if (!$result){
			$admin->userError('DB Error, '.$db->ErrorMsg());
			return false;
		}
		$admin->userSuccess('Bridge Installation Removed.');
		return true;
	}
	function display_bridge_sync(){
		$db = $admin = true;
		include (GEO_BASE_DIR.'get_common_vars.php');

		$id = (isset($_GET['install_id']))? intval($_GET['install_id']): '';
		if (!$id){
			$admin->userError('Invalid bridge installation!');
			$this->display_bridge_manage();
		}

		$util =& geoAddon::getUtil('bridge');

		$bridge =& $util->getInstall($id);
		if (!$bridge){
			$admin->userError('Error, invalid bridge install.');
		} else {
			$type = $bridge->install_info['type'];
			$name = $bridge->install_info['name'];
			$status = $bridge->install_info['active'];
			//check inputs

			$filename = ADDON_DIR.'bridge/bridges/'.$type.'.php';
			if (!file_exists($filename)){
				$admin->userError('File for bridge type not found!');
				return $this->display_bridge_manage();
			} else {

				$install =& $bridge;
				if ($status && (method_exists($install, 'importUsers') || method_exists($install, 'exportUsers'))){
					//valid
					$type = $install->name;
					$import=$export=false;
					if (method_exists($install, 'importUsers')){
						$import=true;
					}
					if (method_exists($install, 'exportUsers')){
						$export = true;
					}
					$exportCount=$importCount=$syncCount='Unknown';
					if (method_exists($install,'exportUserCount')){
						$exportCount = $install->exportUserCount();
					}
					if (method_exists($install,'importUserCount')){
						$importCount = $install->importUserCount();
					}
					//if import, and import count
					if (is_integer($exportCount) || is_integer($importCount)){
						$syncCount = 0;
						if (is_integer($exportCount)){
							$syncCount += $exportCount;
						}
						if (is_integer($importCount)){
							$syncCount += $importCount;
						}
					}
				} else {
					//not valid
					$admin->userError('No sync capabilities for bridge installation type, or installation marked inactive. Debug info: s'.$status.'i' .method_exists($install, 'importUsers').'e' .method_exists($install, 'exportUsers'));
					return $this->display_bridge_manage();
				}
			}
		}
		if ($import&&$export){
			$cap = 'Import &amp; Export';
		} elseif ($import){
			$cap = 'Import only';
		} elseif ($export){
			$cap = 'Export only';
		} else {
			$cap = 'No Sync Abilities';
		}
		$html = $admin->getUserMessages().'
<fieldset>
	<legend>Sync User Information</legend><div>
	<div class="leftColumn">Bridge Name</div>
	<div class="rightColumn">'.$name.'</div>

	<div class="leftColumn">Type</div>
	<div class="rightColumn">'.$type.'</div>

	<div class="leftColumn">Sync Capabilities</div>
	<div class="rightColumn">'.$cap.'</div>
	<br style="clear:both;" /><br />
	<div class="leftColumn">Out of sync Users:</div><div class="rightColumn">(Number of users found in one<br />installation but not the other)</div>
	<br style="clear:both;" />
	<div class="leftColumn">Bridge installation</div>
	<div class="rightColumn">'.$importCount.'</div>

	<div class="leftColumn">Local</div>
	<div class="rightColumn">'.$exportCount.'</div>

	<div class="leftColumn">Total out of sync users:</div>
	<div class="rightColumn">'.$syncCount.'</div>';

		$html .= '
</div></fieldset>
<fieldset>
	<legend>Sync Controls</legend><div>
	<div class="leftColumn">What to sync</div>
	<form action="" method="post" class="form-horizontal form-label-left">
	<div class="rightColumn">';
		if ($import){
			$count = '';
			if (is_integer($importCount)){
				$count = ' ('.$importCount.')';
			}
			$html .= '
		<label><input type="checkbox" name="runImport" value="1" /> '.$name.' <strong> >> </strong> Local'.$count.'</label><br />';
		}
		if ($export){
			$count = '';
			if (is_integer($exportCount)){
				$count = ' ('.$exportCount.')';
			}
			$html .= '
		<label><input type="checkbox" name="runExport" value="1" /> '.$name.' <strong> << </strong> Local'.$count.'</label><br />';
		}
		$html .= '
	</div>
	<input type="submit" name="auto_save" value="Sync Now" class="medium_font" />
	</form>
</div></fieldset>
';
		$admin->v()->addBody($html);
	}
	function update_bridge_sync(){
		$admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';

		$util =& geoAddon::getUtil('bridge');

		$install = $util->getInstall($_GET['install_id']);
		if (!$install){
			$admin->userError('Could not get install object, check ID.');
			return false;
		}
		if (isset($_POST['runImport']) && $_POST['runImport'] && method_exists($install, 'importUsers')){
			$install->importUsers();
		}
		if (isset($_POST['runExport']) && $_POST['runExport'] && method_exists($install, 'exportUsers')){
			$install->exportUsers();
		}

		return true;
	}

	function display_bridge_test(){
		$db = $admin = true;
		include (GEO_BASE_DIR.'get_common_vars.php');

		$id = (isset($_GET['id']))? intval($_GET['id']): '';

		if (!$id){
			$admin->userError('Installation ID invalid!');
			return $this->display_bridge_manage();
		}

		$util =& geoAddon::getUtil('bridge');
		$install =& $util->getInstall($id);
		if (!$install){
			$admin->userError('Installation ID invalid!  (Must be active to test)');
			return $this->display_bridge_manage();
		}
		if (!method_exists($install,'test_settings')){
			$admin->userError('Installation type not capable of testing settings (requires function test_settings())');
			return $this->display_bridge_manage();
		}
		if ($install->test_settings()){
			$admin->userSuccess('All test(s) for this installation Passed.');
		} else {
			$admin->userError('One or more Tests for this installation failed.');
		}
		$html = '
<fieldset>
	<legend>Test Installation Results</legend><div>
	'.$admin->getUserMessages()
	.geoHTML::addButton('Back to Bridges','index.php?page=bridge_manage')
	.geoHTML::addButton('Edit Configuration','index.php?page=bridge_edit&id='.$id)
	.'
</div></fieldset>';
		$admin->v()->addBody($html);
	}
	function update_bridge_test(){

	}

	function display_bridge_install_type_info($b_type = false){
		$type=($b_type)? $b_type: $_GET['type'];
		$admin = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		$good = true;
		if(strlen(trim($type))==0){
			$admin->userError('Error: no bridge type specified, please try again.');
			$good = false;
		} elseif ($type == '_template' || dirname(ADDON_DIR.'bridge/bridges/'.$type.'.php') != dirname(ADDON_DIR.'bridge/bridges/_template.php')){
			$admin->userError('Error getting information for requested bridge type, please try again.');
			$good=false;
		} elseif (!file_exists(ADDON_DIR.'bridge/bridges/'.$type.'.php')) {
			$admin->userError('File not found or permission is denied for bridge type: '.ADDON_DIR.'bridge/bridges/'.$type.'.php');
			$good=false;
		}
		if ($good){
			include_once(ADDON_DIR.'bridge/bridges/'.$type.'.php');
			if (!class_exists('bridge_'.$type)){
				$admin->userError('Configuration for file not correct, class not found in file: '.ADDON_DIR.'bridge/bridges/'.$type.'.php');
				$good=false;
			}
		}

		if (!$b_type){
			$html = $admin->getUserMessages();
		} else {
			$html = '';
		}

		if ($good){
			$bridge = 'bridge_'.$type;
			$bridge = new $bridge();
			$cap = array();
			if (method_exists($bridge,'importUsers')){
				$cap [] = 'User Sync - Import users from Bridge';
			}
			if (method_exists($bridge,'exportUsers')){
				$cap [] = 'User Sync - Export users to Bridge';
			}
			if (method_exists($bridge,'user_register')){
				$cap[] = 'Register New User';
			}
			if (method_exists($bridge,'user_edit')){
				$cap[] = 'Update user details';
			}
			if (method_exists($bridge,'session_login')){
				$cap[] = 'Log-In';
			}
			if (method_exists($bridge,'session_logout')){
				$cap[] = 'Log-Out';
			}
			if (count($cap) == 0){
				$cap[] = 'None???'; //not a very capabile bridge type...
			}
			$cap = 'This bridge can: <br /> - '.implode('<br /> - ',$cap);
			$details = (method_exists($bridge, 'getDescription'))? $bridge->getDescription(): 'N/A';
			$html .= '
<fieldset>
	<legend>Bridge Type Information</legend><div>
	<div class="row_color1">
		<div class="leftColumn">Installation Type</div>
		<div class="rightColumn">'.$bridge->name.'</div>
		<div class="clearColumn"></div>
	</div>
	<div class="row_color2">
		<div class="leftColumn">Built-in Capabilities</div>
		<div class="rightColumn">'.$cap.'</div>
		<div class="clearColumn"></div>
	</div>
	<div class="col_hdr" style="clear: both; text-align: left; margin-top: 10px;">More Details:</div>
	<div style="text-align: left;" class="medium_font">'.$details.'</div>
</div></fieldset>';
		}
		if (!$b_type){
			$admin->v()->addBody($html);
		} else {
			return $html;
		}
	}

	function getBridgeDropdown(){
		$dir = opendir(ADDON_DIR.'bridge/bridges/');
		$bridges = array();
		$html = '';
		while ($filename = readdir($dir)){
			if ($filename !='.' && $filename != '..' && strpos($filename,'_') !== 0 && file_exists(ADDON_DIR.'bridge/bridges/'.$filename)){
				require_once(ADDON_DIR.'bridge/bridges/'.$filename);
				$type = str_replace('.php','', $filename);
				if (class_exists('bridge_'.$type)){
					$classname = 'bridge_'.$type;
					$bridge = new $classname();
					$html .= '<option value="'.$type.'">'.$bridge->name.'</option>
';
				} else {
					echo 'name: '.$type;
				}
			}
		}
		if (strlen($html) == 0){
			geoAdmin::m('Could not find any installation types.  Be sure you have uploaded the entire <em>addons/bridge/bridges/</em> directory, and that none
of the files have been corrupted.', geoAdmin::NOTICE);
			$html = '<option value="0">No Installation Types Found</option>';
		}
		return $html;
	}

	function getBridgeTable(){
		$dir = opendir(ADDON_DIR.'bridge/bridges/');
		$bridges = array();
		$html = '';
		$row = 'row_color2';
		while ($filename = readdir($dir)){
			if ($filename !='.' && $filename != '..' && strpos($filename,'_') !== 0 && file_exists(ADDON_DIR.'bridge/bridges/'.$filename)){
				$row = ($row=='row_color2')? 'row_color1': 'row_color2';
				require_once(ADDON_DIR.'bridge/bridges/'.$filename);
				$type = str_replace('.php','', $filename);
				if (class_exists('bridge_'.$type)){
					$classname = 'bridge_'.$type;
					$bridge = new $classname();
					$import = (method_exists($bridge,'importUsers'))? 'Yes': '--';
					$export = (method_exists($bridge,'exportUsers'))? 'Yes': '--';
					$create = (method_exists($bridge,'user_register'))? 'Yes': '--';
					$update = (method_exists($bridge,'user_edit'))? 'Yes': '--';
					$login = (method_exists($bridge,'session_login'))? 'Yes': '--';
					$logout = (method_exists($bridge,'session_logout'))? 'Yes': '--';
					$html .= "
				<tr>
					<td class=\"$row\"><a href=\"index.php?page=bridge_install_type_info&amp;type={$type}\">{$bridge->name}</a><td class=\"$row\" style=\"text-align:center;\">$import</td><td class=\"$row\" style=\"text-align:center;\">$export</td><td class=\"$row\" style=\"text-align:center;\">$create</td><td class=\"$row\" style=\"text-align:center;\">$update</td><td class=\"$row\" style=\"text-align:center;\">$login</td><td class=\"$row\" style=\"text-align:center;\">$logout</td>
				</tr>";
				}
			}
		}
		if (strlen($html) == 0){
			geoAdmin::m('Could not find any installation types.  Be sure you have uploaded the entire <em>addons/bridge/bridges/</em> directory, and that none
of the files have been corrupted.', geoAdmin::NOTICE);
			$html = '<option value="0">No Installation Types Found</option>';
		}
		return $html;
	}

	function typeForm(){
		$name = (isset($_POST['name']))? $_POST['name']: '';
		$html = '
<fieldset>
	<legend>New Bridge Installation</legend><div>
	<form method="POST" action="" class="form-horizontal form-label-left">
		<div class="x_content">
			<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Installation Name: </label>
			  <div class="col-md-6 col-sm-6 col-xs-12">
			  <input name="name" class="form-control col-md-7 col-xs-12" value="'.geoString::specialChars($name).'" type="text" />
			  </div>
			</div>

			<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Installation Type: </label>
			  <div class="col-md-6 col-sm-6 col-xs-12">
			  <select class="form-control col-md-7 col-xs-12" name="type">'.$this->getBridgeDropdown().'</select>
			  </div>
			</div>

			<div class="center"><input type="submit" name="submit" value="Next" /></div>
		</div>
	</form>
</div></fieldset>
';
		return $html;
	}

	function settingsForm($id = null){
		$admin = $db = 1;
		include GEO_BASE_DIR . 'get_common_vars.php';
		if (!is_null($id)){
			$id = intval($id);
			$sql = 'SELECT * FROM `geodesic_bridge_installations` WHERE `id`=?';
			$result = $db->Execute($sql, array($id));
			if (!$result){
				$admin->userError('Db error, '.$db->ErrorMsg());
				return $admin->getUserMessages();
			}
			if ($result->RecordCount() != 1){
				$admin->userError('Could not find installation in database.');
				return $admin->getUserMessages();
			}
			$row = $result->FetchRow();
			$name = $row['name'];
			$type = $row['type'];
			$active = $row['active'];
			$email = $row['email'];
			$settings = unserialize($row['settings']);
		} else {
			$name = $_POST['name'];
			$type = $_POST['type'];
			$active = (isset($_POST['active']))? $_POST['active']: true;
			$email = $_POST['email'];
			$settings = $_POST['settings'];
		}
		//check inputs
		if (strlen(trim($name)) == 0){
			$admin->userError('No installation name entered!  Please enter a valid <em>Installation Name</em>.');
			if (!is_null($id)){
				$admin->userNotice('You may need to remove the installation and start over, as the information may have been corrupted.');
				return $admin->getUserMessages().'<a class="medium_font" href="index.php?page=bridge_manage">Back</a>';
			}
			return $this->typeForm();
		}
		$filename = ADDON_DIR.'bridge/bridges/'.$type.'.php';
		if (!file_exists($filename)){
			$admin->userError('Invalid installation type!');
			if (!is_null($id)){
				$admin->userNotice('You may need to remove the installation and start over, as the information may have been corrupted.');
				return $admin->getUserMessages().'<a style="border:thin solid black; padding: 5px; color: blue; text-decoration:none; " class="medium_font" href="index.php?page=bridge_manage">Back</a>';
			}
			return $this->typeForm();
		}
		include_once($filename);
		if (!class_exists('bridge_'.$type)){
			$admin->userError('Installation type syntax error!');
			if (!is_null($id)){
				$admin->userNotice('You may need to remove the installation and start over, as the information may have been corrupted.');
				return $admin->getUserMessages().'<a style="border:thin solid black; padding: 5px; color: blue; text-decoration:none; " class="medium_font" href="index.php?page=bridge_manage">Back</a>';
			}
			return $this->typeForm();
		}
		$classname = 'bridge_'.$type;
		$install = new $classname();
		$html .= $this->display_bridge_install_type_info($type);

		$html .= '
<fieldset>
	<legend>Installation Settings</legend><div>
	<form method="POST" class="form-horizontal form-label-left" action="">'.((is_null($id))? '': '<input type="hidden" name="id" value="'.$id.'" />' ).'
		<div class="x_content">
        	<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Installation Name: </label>
			  <div class="col-md-6 col-sm-6 col-xs-12">
			  <input name="name" class="form-control col-md-7 col-xs-12" value="'.geoString::specialChars($name).'" type="text" />
			  </div>
			</div>

			<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Installation Type: </label>
			  <div class="col-md-6 col-sm-6 col-xs-12">
			  <span class="vertical-form-fix">'.$install->name.'<input name="type" value="'.$type.'" type="hidden" /></span>
			  </div>
			</div>

			<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12"></label>
			  <div class="col-md-7 col-sm-7 col-xs-12">
				<input type="checkbox" name="active" '.(($active)? 'checked="checked" ': '').'value="1" />&nbsp;
				Activate Bridge
			  </div>
			</div>

        	<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">Admin E-Mail: <br /><span class="small_font">E-Mail to send any bridge installation errors to.</span></label>
			  <div class="col-md-6 col-sm-6 col-xs-12">
			 <input type="text" name="email" class="form-control col-md-7 col-xs-12" value="'.$email.'" />
			  </div>
			</div>
			';

			foreach ($install->settings as $key => $val){
				$rowColor = ($rowColor == 'row_color2')? 'row_color1': 'row_color2';
				if ($val == 'checkbox'){
					$default_val = (isset($install->setting_desc[$key]['checked']))? '<br /><strong>Default is Checked</strong>': '<br /><strong>Default is Un-Checked</strong>';
				} else {
					$default_val = (isset($install->setting_desc[$key]['value']))? '<br /><strong>Default Value: '.geoString::specialChars($install->setting_desc[$key]['value']).'</strong>':'';
				}
				$html .= '

        	<div class="form-group">
			<label class="control-label col-md-5 col-sm-5 col-xs-12">'.$install->setting_desc[$key]['name'].': <br /><span class="small_font" style="font-weight: normal;">'.$install->setting_desc[$key]['desc'].$default_val.'</span></label>
			  <div class="col-md-6 col-sm-6 col-xs-12">';
				switch ($val){
					case 'input':
						$use_val = (isset($settings[$key]))? $settings[$key]: '';
						$use_val = (!$id && $use_val=='' && isset($install->setting_desc[$key]['value']))? $install->setting_desc[$key]['value']: $use_val;
						//do not clean $key, it is up to each bridge to make sure the key name is clean..
						//Only clean value since that is
						//user input.
						$html .= '<input type="text" name="settings['.$key.']" class="form-control col-md-7 col-xs-12" value="'.geoString::specialChars($use_val).'" />';
						break;
					case 'checkbox':
						$use_val = (isset($install->setting_desc[$key]['value']))? $install->setting_desc[$key]['value']: '1';
						$checked = (isset($settings[$key]) && $settings[$key])? 'checked="checked" ': '';
						$checked = (!$id && $checked == '' && isset($install->setting_desc[$key]['checked']))? 'checked="checked" ':$checked;
						$html .= '<input type="checkbox" name="settings['.$key.']" class="form-control col-md-7 col-xs-12" value="'.geoString::specialChars($use_val).'" '.$checked.'/>';
						break;
					default:
						$html .= 'Unknown setting type '.$val;
				}
			$html .= '
			  </div>
			</div>
			';
			}
			$html .= '
			<br style="clear:both;" />
			<div class="center"><input type="submit" name="auto_save" value="'.((is_null($id))? 'Create': 'Save').' Bridge" /></div>
			<br style="clear:both;" /><br />
			<div class="center">'.((is_null($id))? geoHTML::addButton('Start Over','index.php?page=bridge_add'): geoHTML::addButton('Back to Bridges','index.php?page=bridge_manage')).((!is_null($id) && $active)? '</div><div class="rightColumn"> &nbsp; '.geoHTML::addButton('Check Settings','index.php?page=bridge_test&id='.$id): '').'</div>
		</div>
	</form>
</div></fieldset><div class="clearColumn"></div>
';

		return $html;
	}
	function api_settings(){
		$html = '';
		include (GEO_BASE_DIR.'config.default.php');
		if (!isset($api_db_host) || strlen(trim($api_db_host)) == 0){
			//no api in config.php
			return '';
		}
		//TODO: finish this
		//return 'API found.';
	}
}