{* 6.0.7-3-gce41f93 *}
<{literal}?php{/literal}
###########################################
## Auto-generated file
## It is not recommended to edit
## this file directly, but you can
## if you want.
## 
## Generated: {$smarty.now|date_format:"%b %e, %Y %H:%M:%S"}
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
{foreach from=$modules item="module" key="id"}
$return ['modules'] [{$id}] = '{$module}';
{foreachelse}
//No attached modules
{/foreach}

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
 
{foreach from=$addons key="author" item="author_info"}
{foreach from=$author_info key="addon" item="addon_data"}
{foreach from=$addon_data item="tag"}
$return ['addons'] ['{$author}'] ['{$addon}'] ['{$tag}'] = '{$tag}';
{/foreach}
{/foreach}
{foreachelse}
//No attached addon tags
{/foreach}

###########################################
## Sub-Page/Sub-Template Attachments
###########################################
/**
 * Sub-page Attachment(s)  Syntax:
 * $return['sub_pages'][filename(string)] = filename (string)
 *  filename: relative to modules_to_template/ dir and w/o .php extension
 */
$process_sub_pages = 1;
{foreach from=$sub_pages item="sub_page" key="id"}
$return ['sub_pages'] ['{$sub_page}'] = '{$sub_page}';
{foreachelse}
//No sub-page attachments. (comment out line below if
// sub pages are added manually)
$process_sub_pages = 0;
{/foreach}

###########################################
## DO NOT EDIT BELOW THIS LINE!!!
## 
##  The rest of this file is used
##  to automatically load sub-pages
###########################################

$return ['already_attached']['{$filename}'] = 1;

$depth = (isset($depth))? $depth: 0;

$depth ++;

if ($process_sub_pages && is_array($return['sub_pages']) && !(isset($skip_sub_pages) && $skip_sub_pages)) {ldelim}
	$sub_pages = $return['sub_pages'];
	foreach ($sub_pages as $sub_page) {ldelim}
		if (!isset($return['already_attached'][$sub_page])) {ldelim}
			//prevent infinite recursion, make sure we only do each one once
			$return['already_attached'][$sub_page] = 1;
			$file = geoTemplate::getFilePath('main_page','attachments',"modules_to_template/{ldelim}$sub_page}.php",false);
			if ($file !== "modules_to_template/{ldelim}$sub_page}.php") {ldelim}
				require $file;
			}
		}
	}
}

$depth --;

if (!$depth) {ldelim}
	return $return;
}
