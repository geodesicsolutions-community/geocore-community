<?php
{* git-info intentionally omitted to mitigate leading whitespace issues *}
###########################################
## Auto-generated file
## It is not recommended to edit
## this file directly, but you can
## if you want.
## 
## Generated: {$smarty.now|date_format:"%b %e, %Y %H:%M:%S"}
###########################################


/**
 * To manually add your own template set, use the syntax:
 * 
 * geoTemplate::addTemplateSet('folder_name');
 * 
 * Where folder_name is the directory name, relative to this current directory.
 * NOTE: Default template set is always added by system as the last template set
 *  to load.  You do not need to add the default tempalte set here.
 * 
 * If you do not wish manual changes to be overwritten next time admin makes a
 * change, be sure to put them in the "custom section" below.
 */

if (!defined('IN_ADMIN')) {ldelim}
# [CUSTOM SECTION] #{$custom_section}# [/CUSTOM SECTION] #
}

{foreach $t_sets as $t_set}
geoTemplate::addTemplateSet('{$t_set.name}', {$t_set.language_id}{if $t_set.device}, {$t_set.device}{/if});
{foreachelse}
//No template sets to add, will be using only the default template set.
{/foreach}
{* ?> Just making the eclipse HTML parser happy... *}