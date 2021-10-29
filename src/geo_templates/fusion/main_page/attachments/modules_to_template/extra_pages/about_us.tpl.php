<?php
###########################################
## Auto-generated file
## It is not recommended to edit
## this file directly, but you can
## if you want.
## 
## Generated: Feb  4, 2016 21:32:21
###########################################
$return = (isset($return))? $return: array();

###########################################
## Module Attachments
###########################################
/**
 * Module Attachment(s)  Syntax:
 * $return['modules'][module_id(int)] = tag (string)
 * tag: the module's tag, something like "display_username"
 */
//No attached modules

###########################################
## Addon Tag Attachments
###########################################
/**
 * Addon Tag Attachment(s)  Syntax:
 * $return['addons'][auth_tag(string)][addon_name(string)][tag(string)] = tag (string)
 * auth_tag: from addon_info->auth_tag
 * addon_name: from addon_info->name
 * tag: entry in addon_info->tags array
 */
 
//No attached addon tags

###########################################
## Sub-Page/Sub-Template Attachments
###########################################
/**
 * Sub-page Attachment(s)  Syntax:
 * $return['sub_pages'][filename(string)] = filename (string)
 *  filename: relative to modules_to_template/ dir and w/o .php extension
 */
$process_sub_pages = 1;
//No sub-page attachments. (comment out line below if
// sub pages are added manually)
$process_sub_pages = 0;

###########################################
## DO NOT EDIT BELOW THIS LINE!!!
## 
##  The rest of this file is used
##  to automatically load sub-pages
###########################################

$return ['already_attached']['extra_pages/about_us.tpl'] = 1;

$depth = (isset($depth))? $depth: 0;

$depth ++;

if ($process_sub_pages && is_array($return['sub_pages']) && !(isset($skip_sub_pages) && $skip_sub_pages)) {
	$sub_pages = $return['sub_pages'];
	foreach ($sub_pages as $sub_page) {
		if (!isset($return['already_attached'][$sub_page])) {
			//prevent infinite recursion, make sure we only do each one once
			$return['already_attached'][$sub_page] = 1;
			$file = geoTemplate::getFilePath('main_page','attachments',"modules_to_template/{$sub_page}.php",false);
			if ($file !== "modules_to_template/{$sub_page}.php") {
				require $file;
			}
		}
	}
}

$depth --;

if (!$depth) {
	return $return;
}
