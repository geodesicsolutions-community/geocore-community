<?php
//outputfilter.strip_forms.php
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## 
##    16.09.0-22-g019d772
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
