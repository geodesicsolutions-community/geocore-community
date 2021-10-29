<?php
//compiler.head_html.php
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
## 
##    7.3beta2-102-g09573a0
## 
##################################

//This fella takes care of {head_html}
function smarty_function_head_html ($params, Smarty_Internal_Template $smarty)
{
	if ($smarty->getTemplateVars('_inside_head_html')) {
		//already in body var, prevent infinite recursion
		return '{head_html}';
	}
	
	//figure out the file to use
	$file = '';
	$geo_inc_files = $smarty->getTemplateVars('geo_inc_files');
	
	if (isset($params['file'])) {
		//use file
		$file = $params['file'];
	} else if (isset($geo_inc_files['head_html'])) {
		$file = $geo_inc_files['head_html'];
	}
	
	if (!$file) {
		//no main page file to use for template...
		return ''.$smarty->getTemplateVars('_head_html');
	}
	$tpl_vars = (array)$smarty->getTemplateVars('head_vars');
	
	$tpl_vars['_inside_head_html']=1;
	
	$g_type = $g_resource = null;
	if (isset($geo_inc_files['head_html_addon'])) {
		$g_type = geoTemplate::ADDON;
		$g_resource = $geo_inc_files['head_html_addon'];
	} else if (isset($geo_inc_files['head_html_system'])) {
		$g_type = geoTemplate::SYSTEM;
		$g_resource = $geo_inc_files['head_html_system'];
	}
	//unlike {body_html}, anything in head html is displayed first so can be over-written by
	//stuff in template
	$pre = ''.$smarty->getTemplateVars('_head_html');
	
	return geoTemplate::loadInternalTemplate($params, $smarty, $file, $g_type, $g_resource, $tpl_vars, $pre);
}
