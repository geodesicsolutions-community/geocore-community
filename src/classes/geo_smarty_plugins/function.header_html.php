<?php
//function.header_html.php
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
## ##    7.3beta2-102-g09573a0
## 
##################################

//This fella takes care of {header_html} - for backwards compatibility
function smarty_function_header_html ($params, Smarty_Internal_Template $smarty)
{
	require_once CLASSES_DIR . 'geo_smarty_plugins/function.head_html.php';
	return smarty_function_head_html($params, $smarty);
}
