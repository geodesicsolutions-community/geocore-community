<?php
###########################################
## Auto-generated file
## It is not recommended to edit
## this file directly, but you can
## if you want.
## 
## Generated: Jan  4, 2010 14:28:59
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
$return ['modules'] [170] = 'module_total_live_users';
$return ['modules'] [169] = 'module_total_registered_users';
$return ['modules'] [66] = 'newest_ads_link';
$return ['modules'] [78] = 'newest_ads_link_1';
$return ['modules'] [79] = 'newest_ads_link_2';
$return ['modules'] [80] = 'newest_ads_link_3';
$return ['modules'] [100] = 'main_classified_navigation_1';
$return ['modules'] [89] = 'module_featured_pic_1';
$return ['modules'] [172] = 'module_hottest_ads';
$return ['modules'] [60] = 'newest_ads_1';
$return ['modules'] [46] = 'featured_ads_1';

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
$return ['sub_pages'] ['head.tpl'] = 'head.tpl';
$return ['sub_pages'] ['header.tpl'] = 'header.tpl';
$return ['sub_pages'] ['footer.tpl'] = 'footer.tpl';

###########################################
## DO NOT EDIT BELOW THIS LINE!!!
## 
##  The rest of this file is used
##  to automatically load sub-pages
###########################################

$return ['already_attached']['front_page.tpl'] = 1;

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