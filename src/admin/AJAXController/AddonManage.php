<?php
if(class_exists( 'admin_AJAX' ) or die());

class ADMIN_AJAXController_AddonManage extends admin_AJAX {
	
	public $addons, $actions_exclusive;
	
	function _action()
	{		
		$addon = $_GET['addon'];
		$task = $_GET['task'];
		$this->addon = geoAddon::getInstance();
		
		//make sure to clear the cache
		$cache = geoCacheSetting::getInstance();
		$cache->expire('addons_installed');
		$cache->expire('addon_text');
		
		define ('GEO_ADDON_SETUP', $addon);
		
		if (defined('DEMO_MODE')) {
			$callResult = "Addon Status cannot be changed on this demo.";
		} else {
			switch($task)
			{
				case 'install':
					$callResult = $this->install($addon);
					break;
					
				case 'uninstall':
					$callResult = $this->uninstall($addon);
					break;
					
				case 'enable':
					$callResult = $this->enable($addon);
					break;
					
				case 'disable':
					$callResult = $this->disable($addon);
					break;
					
				case 'upgrade':
					$callResult = $this->upgrade($addon);
					break;
					
			}
		}
		$admin = geoAdmin::getInstance();
		$reload = true;
		if ($callResult) {
			//see if there are any messages already
			if ($admin->getMessageCount() > 0) {
				//there were messages, don't auto re-load
				$reload = false;
			}
			
			$admin->message($callResult);
		} else {
			$admin->message('Addon '.$task.' failed!',geoAdmin::ERROR);
			$reload = false;
		}
		
		$section = '';
		if($task =='install') {
			$section = "#{$addon}_enable";
		} else if($task=='uninstall') {
			$section = "#{$addon}_disabled";
		}
		if($reload) {
			//return nothing, so that the auto-reloader knows to do its thing
			return '';
		} else {
			//return some specific message; js will NOT auto-reload
			$message = 'Once you have read the above notices, <a href="index.php?page=addon_tools&mc=addon_management">refresh the admin panel</a> to reflect Addon changes.';
			geoAdmin::m($message, geoAdmin::SUCCESS);
			$all = geoAdmin::m();
			//clean up admin messages to look nicer inside gjUtil.addMessage() dialog
			$all = str_replace('userMessage','',$all);
			return $all;
		}
			
		
		
	}
	
	function uninstall( $addon_name )
	{
		$db = DataAccess::getInstance();
		$admin = geoAdmin::getInstance();
		
		if ($this->addon->isEnabled($addon_name)){
			//cant uninstall if it is still enabled.
			return true;
		}
		if (is_file(ADDON_DIR.$addon_name.'/setup.php')){
			include_once (ADDON_DIR.$addon_name.'/setup.php');
			if (!class_exists('addon_'.$addon_name.'_setup',false)){
				$admin->userError('Error un-installing addon, addon is mis-configured: the file'.ADDON_DIR.$addon_name.'/setup.php exists but class addon_'.$addon_name.'_setup not found.');
				return false;
			}
			$setup = Singleton::getInstance('addon_'.$addon_name.'_setup');
			if (method_exists($setup,'uninstall') && !$setup->uninstall()){
				//uninstall failed, don't continue.
				$admin->message('Error un-installing addon, un-install script failed.',geoAdmin::ERROR);
				return false;
			}
		}
		
		//remove any templates previously copied over to default template set
		if (!$this->_removeTemplates($addon_name)) {
			$admin->message('Error removing templates from default template set, un-install failed.', geoAdmin::ERROR);
			return false;
		}
		
		//expire the cache
		geoCacheSetting::expire('addons_installed');
		
		//remove registry settings
		geoRegistry::remove('addon',$addon_name);
		
		//remove any text from the addons table, too...
		if (is_file(ADDON_DIR.$addon_name.'/info.php')){
			include_once (ADDON_DIR.$addon_name.'/info.php');
			if (!class_exists('addon_'.$addon_name.'_info',false)){
				$admin->message('Error removing addon text, class addon_'.$addon_name.'_info could not be found.',geoAdmin::ERROR);
				return false;
			}
			$info =& Singleton::getInstance('addon_'.$addon_name.'_info');
			$sql = 'DELETE FROM '.$db->geoTables->addon_text_table.' WHERE auth_tag = ? AND addon = ?';
			$result =& $db->Execute($sql, array($info->auth_tag, $addon_name));
			if (!$result){
				trigger_error('ERROR SQL ADDON: Removal of text failed.  Message: '.$db->ErrorMsg());
			}
		}
		
		//remove in db
		$sql = 'DELETE FROM '.$db->geoTables->addon_table.' WHERE name=?';
		$result = $db->Execute($sql, array ($addon_name));
		if (!$result){
			$admin->message('Error un-installing addon, DB Query error.',geoAdmin::ERROR);
			return false;
		}
		
		return  "Addon <b>Uninstalled</b>";
	}
	
	function install( $addon_name ) 
	{
		if(!$this->version_check($addon_name)) {
			//addon not compatible with this version of Geo base
			return false;
		}
		
		$db = DataAccess::getInstance();
		$admin = geoAdmin::getInstance();
		
		//install the addon.
		if ($this->addon->isInstalled($addon_name)) {
			//don't install if it is already installed
			return '';
		}
		
		if (!include_once(ADDON_DIR.$addon_name.'/info.php')) {
			//need the info page.
			$admin->userError('Error installing addon, info.php was not found for addon.');
			return false;
		}
		if (file_exists(ADDON_DIR.$addon_name.'/setup.php')) {
			//echo 'test:'.ADDON_DIR.$addon_name.'/setup.php';
			
			include_once (ADDON_DIR.$addon_name.'/setup.php');
			if (!class_exists('addon_'.$addon_name.'_setup',false)){
				$admin->userError('Error installing addon, addon is mis-configured: file '.ADDON_DIR.$addon_name.'/setup.php exists but class addon_'.$addon_name.'_setup not found.');
				return false;
			}
			$setup = Singleton::getInstance('addon_'.$addon_name.'_setup');
			if (method_exists($setup, 'install') && !$setup->install()){
				//install failed, don't continue.
				$admin->userError('Error installing addon, installation script failed.');
				return false;
			}
		}
		//see if there are pages or templates to add, or templates to assign
		if (!$this->addon->updateTemplates($addon_name)) {
			$admin->userError('Error installing addon, addon templates failure.');
			return false;
		}
		
		//install in db
		$info = Singleton::getInstance('addon_'.$addon_name.'_info');
		$sql = 'INSERT INTO '.$db->geoTables->addon_table.' SET name = ?,version = ?,enabled = ?';
		$query_data = array($info->name, $info->version, $info->type, '0');
		$result = $db->Execute($sql, array ($info->name,$info->version,'0'));
		if (!$result) {
			trigger_error('ERROR SQL ADDON: already installed.'.$db->ErrorMsg());
			$admin->userError('Error installing addon, SQL query failed.');
			return false;
		}
					
		//see if there are any text to add.
		$this->_updateText($addon_name);
		
		
		
		return "Addon Installed.";
	}
	
	public function enable ($addon_name)
	{
		if(!$this->version_check($addon_name)) {
			//addon not compatible with this version of Geo base
			return false;
		}
		
		$addon = geoAddon::getInstance();
		$db = DataAccess::getInstance();
		$admin = geoAdmin::getInstance();
		//make sure it is enabled.
		if ($addon->isEnabled($addon_name)){
			//don't enable if it is already enabled.
			return 'Addon already enabled!';
		}
		if (!$addon->isInstalled($addon_name)){
			//needs to be installed to enable!
			return 'Addon not installed yet!';
		}
		$this->_getAddonDetails();
		
		if (isset($this->addons[$name]['info']->exclusive) && $this->addons[$name]['info']->exclusive && isset($this->addons[$name]['info']->core_events) && count($this->addons[$name]['info']->core_events)){
			//make sure there is no enabled addons that would conflict.
			foreach ($this->addons[$name]['info']->core_events as $action){
				if (count($this->actions_exclusive[$action])>1){
					foreach ($this->actions_exclusive[$action] as $other_addon){
						if ($other_addon->name != $this->addons[$name]['info']->name){
							//if it is also enabled, go eeek!!!
							if ($this->addons[$other_addon->name]['db']['enabled']){
								$menu_loader->userError('Error enabling addon, a conflict with another addon was found.');
								return false;
							}
						}
					}
				}
			}
		}
		
		
		if (is_file(ADDON_DIR.$addon_name.'/setup.php')){
			include_once (ADDON_DIR.$addon_name.'/setup.php');
			if (class_exists('addon_'.$addon_name.'_setup',false)){
				$setup = Singleton::getInstance('addon_'.$addon_name.'_setup');
				if (method_exists($setup,'enable') && !$setup->enable()){
					//enable failed, don't continue.
					$admin->userError('Error enabling addon, enable script failed.');
					return false;
				}
			}
		}
		//enable in db
		$sql = 'UPDATE '.geoTables::addon_table.' SET `enabled`=1 WHERE `name` = ?';
		$result = $db->Execute($sql, array ($addon_name));
		//expire the cache
		geoCacheSetting::expire('addons_installed');
		if (!$result){
			$admin->userError('Error enabling addon, DB Query failed.');
			return false;
		}
		
		return 'Addon Enabled.';
	}
	
	public function disable ($addon_name)
	{
		$addon = geoAddon::getInstance();
		//make sure it is enabled.
		if (!$addon->isEnabled($addon_name)){
			//don't disable if it is already disabled.
			return '';
		}
		if (is_file(ADDON_DIR.$addon_name.'/setup.php')){
			include_once (ADDON_DIR.$addon_name.'/setup.php');
			if (!class_exists('addon_'.$addon_name.'_setup',false)){
				$menu_loader->userError('Error disabling addon, addon mis-configured: File'.ADDON_DIR.$addon_name.'/setup.php found, but missing class addon_'.$addon_name.'_setup.');
				return false;
			}
			
			$setup = Singleton::getInstance('addon_'.$addon_name.'_setup');
			if (method_exists($setup,'disable') && !$setup->disable()){
				//enable failed, don't continue.
				$menu_loader->userError('Error disabling addon, disable script failed.');
				return false;
			}
		}
		//enable in db
		$db = DataAccess::getInstance();
		$sql = 'UPDATE '.geoTables::addon_table.' SET `enabled`=0 WHERE `name` = ? LIMIT 1';
		$result = $db->Execute($sql, array ($addon_name));
		//expire the cache
		geoCacheSetting::expire('addons_installed');
		if (!$result){
			$menu_loader->userError('Error disabling addon, DB Query failed.');
			return false;
		}
		return 'Addon disabled.';
	}
	
	public function upgrade ($addon_name)
	{
		if(!$this->version_check($addon_name)) {
			//addon not compatible with this version of Geo base
			return false;
		}
		
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		
		//make sure it is enabled.
		if (!is_file(ADDON_DIR.$addon_name.'/info.php')) {
			//info file could not be found.
			$admin->userError('Error upgrading addon, addon mis-configured: file '.ADDON_DIR.$addon_name.'/info.php not found.');
			return false;
		}
		include_once (ADDON_DIR.$addon_name.'/info.php');
		if (!class_exists('addon_'.$addon_name.'_info',false)){
			$admin->userError('Error upgrading addon, addon mis-configured: class addon_'.$addon_name.'_info not found.');
			return false;
		}
		$info = Singleton::getInstance('addon_'.$addon_name.'_info');
		if (!isset($info->version) || !strlen($info->version)){
			$admin->userError('Error upgrading addon, addon mis-configured: version not set in info.php.');
			return false;
		}
		
		$sql = 'SELECT `version` FROM '.geoTables::addon_table.' WHERE `name`=?';
		$row = $db->GetRow($sql, array($addon_name));
		if ($row === false){
			$admin->userError('Error upgrading addon, DB Query error.');
			return false;
		}
		if ($row['version'] == $info->version){
			//version does not need upgrading.
			return '';
		}
		
		if (is_file(ADDON_DIR.$addon_name.'/setup.php')){
			include_once (ADDON_DIR.$addon_name.'/setup.php');
			if (!class_exists('addon_'.$addon_name.'_setup',false)){
				$admin->userError('Error upgrading addon, addon mis-configured: file '.ADDON_DIR.$addon_name.'/setup.php exists, but class addon_'.$addon_name.'_setup not found.');
				return false;
			}
			$setup = Singleton::getInstance('addon_'.$addon_name.'_setup');
			if (method_exists($setup,'upgrade') && !$setup->upgrade($row['version'])){
				//upgrade failed, don't continue.
				$admin->userError('Error upgrading addon, upgrade script failed.');
				return false;
			}
		}
		$this->_getAddonDetails();
		
		//see if there is default text that needs to be set.
		//see if there are any text to add.
		$this->_updateText($addon_name, true);
		
		//see if there are pages or templates to add, or templates to assign
		if (!$this->addon->updateTemplates($addon_name)) {
			return false;
		}
		
		//enable in db
		$sql = 'UPDATE '.$db->geoTables->addon_table.' SET `version`=? WHERE `name` = ?';
		$result = $db->Execute($sql, array ($info->version, $addon_name));
		//expire the cache
		geoCacheSetting::expire('addons_installed');
		if (!$result){
			$admin->userError('Error upgrading addon, DB Query error.');
			return false;
		}
		
		return 'Addon version updated.';
	}
	function _getAddonDetails()
	{
		if (isset($this->addons) && is_array($this->addons)){
			//we already ran this once.
			return;
		}
		$dir = opendir(ADDON_DIR);
		$this->addons = array();
		$this->actions_exclusive = array();
		$addon_obj = geoAddon::getInstance();
		while ($filename = readdir($dir)){
			if ($filename == '.' || $filename == '..' || !is_dir(ADDON_DIR.$filename)) {
				//not an addon dir
				continue;
			}
			
			//only include if it isn't an enabled addon...	
			$filename = $addon_obj->getRealName($filename);
			
			$addon_dir = realpath(ADDON_DIR.$filename);
			if (!file_exists($addon_dir.'/info.php')) {
				//info file not found
				continue;
			}
			
			include_once $addon_dir . '/info.php';
			if (!class_exists('addon_'.$filename.'_info',false)){
				//class doesn't exist
				continue;
			}
			$this->addons [$filename]['info'] = Singleton::getInstance('addon_'.$filename.'_info');
			$this->addons [$filename]['db'] = $addon_obj->getInstalledInfo($filename);
			//check to see if exclusive and valid set.
			if ( isset($this->addons[$filename]['info']->exclusive) && isset($this->addons[$filename]['info']->core_events) && count($this->addons[$filename]['info']->core_events)) {
				//it attached to one or more core events, and is exclusive, so
				//remember it.
				foreach ($this->addons[$filename]['info']->core_events as $event_name){
					//remember which one it is exclusive to.
		
					$core_exclusive = false;
					//break up the if stmt so that it's easier to understand..
					if (!is_array($this->addons[$filename]['info']->exclusive) && $this->addons[$filename]['info']->exclusive == true){
						//it is not an array, it means all core events for
						//this addon are exclusive
						$core_exclusive = true;
					} elseif (is_array($this->addons[$filename]['info']->exclusive) && isset($this->addons[$filename]['info']->exclusive[$event_name])
						&& $this->addons[$filename]['info']->exclusive[$event_name] == true){
						//the current core event is exclusive
						$core_exclusive = true;
					} 
					if ($core_exclusive){
						//remember core events that are exclusive
						$this->actions_exclusive[$event_name][] = $this->addons[$filename]['info'];
					}
				}
			}
		}
	}
	private function _updateText($addonName,$onlyNew = false)
	{
		$db = DataAccess::getInstance();
		$php5_dir = (file_exists(ADDON_DIR.$addonName.'/php5_files/admin.php'))? 'php5_files/': '';
		
		if (file_exists(ADDON_DIR."{$addonName}/{$php5_dir}admin.php")) {
			include_once(ADDON_DIR."$addonName/{$php5_dir}admin.php");
		}
		$info = Singleton::getInstance("addon_{$addonName}_info");
		if (class_exists('addon_'.$addonName.'_admin',false)) {
			$admin = Singleton::getInstance('addon_'.$addonName.'_admin');
			if (method_exists($admin, 'init_text')){
				//go through each text entry and enter the default value.
				$sql = 'SELECT `language_id` FROM '.geoTables::pages_languages_table;
				
				$languages = $db->GetAll($sql);
				foreach ($languages as $row) {
					$text = $admin->init_text($row['language_id']);
					
					if ($onlyNew) {
						$existing = $this->addon->getText($info->auth_tag,$info->name,$row['language_id']);
					}
					//go through each one and add it.
					foreach ($text as $text_id => $data) {
						if ($onlyNew && isset($existing[$text_id])) {
							//don't set this one.
							continue;
						}
						
						if (!$this->addon->setText($info->auth_tag, $info->name, $text_id, $data['default'], $row['language_id'])) {
							trigger_error( 'ERROR ADDON: could not set text. '.$db->ErrorMsg());
						}
					}
				}
			}
		}
	}
	
	private function _templateUnlink ($name, $type='main_page', $sub = '')
	{
		$templateFile = geoFile::getInstance(geoFile::TEMPLATES);
		$addonFile = geoFile::getInstance(geoFile::ADDON);
		
		$list = array_diff(scandir($addonFile->absolutize("$name/templates/$type/$sub")), array('.','..','attachments'));
		
		foreach ($list as $entry) {
			//copy each file over
			$from = $addonFile->absolutize("$name/templates/$type/{$sub}$entry");
			$to = $templateFile->absolutize("default/$type/{$sub}$entry");
			
			//remove attachments
			if (is_dir($from)) {
				//it is folder, need to scan contents of folder and do the same
				return $this->_templateUnlink($name, $type, $sub.$entry.'/');
			} else if ($type == 'main_page') {
				//delete attachments for it
				$templateFile->unlink("default/main_page/attachments/modules_to_template/{$sub}$entry.php");
			}
			//remove the file
			$templateFile->unlink("default/$type/{$sub}$entry");
		}
		return true;
	}
	
	private function _removeTemplates ($name)
	{
		$templateFile = geoFile::getInstance(geoFile::TEMPLATES);
		$addonFile = geoFile::getInstance(geoFile::ADDON);
		
		//clear out any current default addon templates
		$templateFile->unlink("default/addon/$name/");
		
		//clear out any attachments
		$templateFile->unlink("default/main_page/attachments/templates_to_page/addons/$name/");
		
		//see if there are any main page templates, and if there are, delete them
		if (is_dir($addonFile->absolutize("$name/templates/main_page/"))) {
			$this->_templateUnlink($name, 'main_page');
		}
		if (is_dir($addonFile->absolutize("$name/templates/external/"))) {
			$this->_templateUnlink($name,'external');
		}
		
		return true;
	}

	private function version_check($addon_name) {
		$admin = geoAdmin::getInstance();
		$db = DataAccess::getInstance();
		if (!is_file(ADDON_DIR.$addon_name.'/info.php')) {
			//info file could not be found.
			$admin->userError('Error upgrading addon, addon mis-configured: file '.ADDON_DIR.$addon_name.'/info.php not found.');
			return false;
		}
		include_once (ADDON_DIR.$addon_name.'/info.php');
		if (!class_exists('addon_'.$addon_name.'_info',false)){
			$admin->userError('Error upgrading addon, addon mis-configured: class addon_'.$addon_name.'_info not found.');
			return false;
		}
		$info = Singleton::getInstance('addon_'.$addon_name.'_info');
		
		if (!isset($info->core_version_minimum)) {
			//not set, so we don't check for them.
			return true;
		}
		$minimumVersion = $info->core_version_minimum;
		if(!$minimumVersion) {
			//no minimum version is set -- continue with whatever we were doing
			return true;
		}
		
		//get current base version
		$currentVersion = geoPC::getVersion();
		
		if(version_compare($currentVersion, $minimumVersion, ">=") === true) {
			//current version is at least the minimum version
			//ok to proceed
			return true;
		} else {
			$admin->userError("This version (".$info->version.") of the {$addon_name} addon requires version {$minimumVersion} of the Geodesic base software. 
			You are currently using Geodesic version {$currentVersion}, which is insufficient to run this addon.");
			return false;
		}		
	}
}