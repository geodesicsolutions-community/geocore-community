<?php 
//module_total_live_users.php
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

 	// Debug variables
	$filename = "module_total_registered_users.php";
	$function_name = "module_total_registered_users";

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

// This will get all users other than the admin
$page->sql_query = "select count(geodesic_logins.id) as counter from geodesic_logins,geodesic_userdata where geodesic_logins.id > 1 and geodesic_logins.status = 1 and geodesic_logins.id = geodesic_userdata.id";
$registered_result = $this->Execute($page->sql_query);
if($page->configuration_data['debug_modules']) {
	$page->debug_display($page->sql_query, $db, $filename, $function_name, "pages_table", "get count of registered users");
}
if (!$registered_result) {
	return false;
} else {
	$count = $registered_result->FetchRow();
	$tpl_vars['text'] = $page->messages[2459];
	$tpl_vars['count'] = $count['counter'];
	$view->setModuleTpl($show_module['module_replace_tag'],'index')
		->setModuleVar($show_module['module_replace_tag'],'tpl_vars',$tpl_vars);
}
