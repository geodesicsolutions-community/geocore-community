<?php
//function.external.php


//this smarty plugin is for displayPrice modifier

function smarty_function_external ($params, Smarty_Internal_Template $smarty)
{
	$file = geoFile::cleanPath($params['file']);
	$g_resource = isset($params['g_resource'])? $params['g_resource']: '';
	
	$forceDefault = (isset($params['forceDefault']) && $params['forceDefault']);
	
	return geoTemplate::getUrl($g_resource, $file, false, $forceDefault);
}
