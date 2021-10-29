<?php
//function.addon.php
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
## ##    7.1beta1-1082-ge7cf0e5
## 
##################################

//This fella takes care of {addon ...}

function smarty_function_addon ($params, Smarty_Internal_Template $smarty)
{
	//check to make sure all the parts are there
	if (!isset($params['tag'])) {
		//tag not specified
		return '{addon tag syntax error}';
	}
	//for now, only valid tag_type is core or addon
	$tagType = (isset($params['tag_type']) && $params['tag_type']=='core')? 'core' : 'addon';
	if ($tagType=='addon' && !isset($params['addon'])) {
		//addon not specified, and this is normal addon type
		return '{addon tag syntax error}';
	}
	$addonName = (isset($params['addon']))? $params['addon'] : 'core';
	$tag = $params['tag'];
	
	//don't need to send those params to the template
	unset($params['author'],$params['addon'],$params['tag'],$params['tag_type']);
	
	return geoAddon::getInstance()->smartyDisplayTag($params, $smarty, $addonName, $tag, $tagType);
}
