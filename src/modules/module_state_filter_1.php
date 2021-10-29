<?php 
//module_display_state_filters.php
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
## ##    7.5.3-36-gea36ae7
## 
##################################

if (geoPC::is_print() && $this->get_site_setting('disableAllBrowsing')) {
	//browsing disabled, do not show module contents
	return;
}
if (isset($_POST['set_state_filter']) && $_POST["set_state_filter"]) {
	if (($_POST["set_state_filter"] != "clear state") && ($_POST["clear_zip_filter"] != "clear localizer")) {
		$state_filter = $_POST["set_state_filter"];
	} else if (($_POST["set_state_filter"] == "clear state") || ($_POST["clear_zip_filter"] == "clear localizer")) {
		$state_filter = "";
	} else {
		$state_filter = "";
	}
} else if (isset($_COOKIE["state_filter"]) && $_COOKIE["state_filter"]) {
	$state_filter = $_COOKIE["state_filter"];
} else {
	$state_filter = 0;
}

$overrides = geoRegion::getLevelsForOverrides();
$stateLevel = $overrides['state'];
if(!$stateLevel) {
	//no level is set as "state"
	return false;
}
$page->sql_query = "SELECT r.id as id, l.name as name FROM ".geoTables::region." as r, ".geoTables::region_languages." as l WHERE r.id=l.id AND `level` = ? AND `language_id` = ? AND r.enabled='yes'";
$state_result = $this->Execute($page->sql_query, array($stateLevel, $this->getLanguage()));

if (!$state_result)
{
	return false;
}

if (!$state_filter || ($state_filter === "0")) {
	$tpl_vars['first_opt_selected'] = true;
} else {
	$tpl_vars['clear_opt'] = true;
}

$opts = array();

for ($i=0; $show_state = $state_result->FetchRow(); $i++) {
	$opts[$i]['value'] = $show_state["id"];
	$opts[$i]['name'] = geoString::fromDB($show_state["name"]);
	
	if ($state_filter && $state_filter == $show_state['id']) {
		$opts[$i]['sel'] = true;
	}
}


if(geoPC::is_ent()) {
	$view->setModuleTpl($show_module['module_replace_tag'],'index')
		->setModuleVar($show_module['module_replace_tag'],'tpl_vars',$tpl_vars)
		->setModuleVar($show_module['module_replace_tag'],'opts',$opts);
}