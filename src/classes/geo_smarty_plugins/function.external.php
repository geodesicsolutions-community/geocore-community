<?php
//function.external.php
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
## ##    6.0.7-2-gc953682
## 
##################################

//this smarty plugin is for displayPrice modifier

function smarty_function_external ($params, Smarty_Internal_Template $smarty)
{
	$file = geoFile::cleanPath($params['file']);
	$g_resource = isset($params['g_resource'])? $params['g_resource']: '';
	
	$forceDefault = (isset($params['forceDefault']) && $params['forceDefault']);
	
	return geoTemplate::getUrl($g_resource, $file, false, $forceDefault);
}
