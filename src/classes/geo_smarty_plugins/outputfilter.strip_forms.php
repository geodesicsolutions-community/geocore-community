<?php
//outputfilter.strip_forms.php
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
## ##    16.09.0-22-g019d772
## 
##################################

//this smarty plugin is nice

function smarty_outputfilter_strip_forms ($output, Smarty_Internal_Template $smarty)
{
	return preg_replace_callback('/\<form[^>]*\>/i',function($matches){return _replaceFormTag($matches[0]);},$output);
}

function _replaceFormTag ($form_tag)
{
	if (strpos($form_tag,'id="switch_product"') === false) {
		//this is not the switch product form tag.
		$form_tag = '';
	}
	return $form_tag;
}
