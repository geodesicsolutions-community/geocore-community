{* 7.2.1-28-gdd79f6b *}
{literal}<?php{/literal}
###########################################
## Auto-generated file
## It is not recommended to edit
## this file directly, but you can
## if you want.
## 
## Generated: {$smarty.now|date_format:"%b %e, %Y %H:%M:%S"}
###########################################

$return = array();

###########################################
## Template Attachments
###########################################

/**
 * Template Attachment(s)  Syntax:
 * 
 * Category (or site-wide/default) Attachments:
 * $return [language_id(int)][category_id(int)] = template filename (string);
 * 
 * Affiliate Group-Specific Template Attachments:
 * $return ['affiliate_group'] [language_id(int)] [group_id(int)] = template filename (string);
 * 
 * Extra Page {ldelim}main_body} sub-template attachments:
 * $return ['extra_page_main_body'] [language_id(int)] [0] = template filename (string);
 * 
 * Note: Template assignment for language 1 category 0 should always be specified,
 * in order to avoid template not found errors.
 */

{foreach from=$page_attachments item="lang_attach" key="lang_id"}
{foreach from=$lang_attach item="template" key="cat_id"}
{if is_array($template)}
{foreach from=$template item="value" key="i"}
$return [{$lang_id}] [{$cat_id}] [{$i}] = '{$value}';
{/foreach}
{else} 
$return [{$lang_id}] [{$cat_id}] = '{$template}';
{/if}
{/foreach}
{/foreach}

return $return;
{* ?> Just making the eclipse HTML parser happy... *}