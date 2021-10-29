{* 16.09.0-79-gb63e5d8 *}
<strong>Addon Name: </strong>{$info->title}{if $info->info_url  && !$white_label} [ <a href="{$info->info_url}">More Info</a> ]{/if}<br />
{if !$white_label}<strong>Author: </strong>{$info->author} {/if}{if $info->author_url && !$white_label} [ <a href="{$info->author_url}">Author's Site</a> ]<br />{/if}
{if $info->auth_tag && !$white_label}
<div style="margin-left: 30px;">
	<strong>Author's <em>tag name</em></strong>: {$info->auth_tag}
</div>
{/if}
<strong>File Version: </strong>{$info->version}<br />
<strong>DB Version: </strong>{if $info_db.version}{$info_db.version}{else}N/A{/if}<br />
{if $info_db.version && $info->version != $info_db.version}
<strong>Needs DB Upgrade!</strong><br />
{elseif $info_db.version && $info->upgrade_url && !$white_label}
[ <a href="{$info->upgrade_url}">Check Site for Upgrades</a> ]<br />
{/if}
<strong>Addon Folder Location:</strong> addons/{$info->name}/<br />
<strong>Tags available in templates:</strong> {if $info->tags && $info->tag_info_url} [ <a href="{$info->tag_info_url}">Tag Details</a> ]{/if}<br />
{if $info->tags}
{foreach from=$info->tags item="tag_name"}
<span style="white-space: nowrap;">{ldelim}addon author='{$info->auth_tag}' addon='{$info->name}' tag='{$tag_name}'{rdelim}</span><br />
{/foreach}
<br />
{else}
N/A<br />
{/if}
{if $info->listing_tags}
<strong>Listing Info Tags available in templates:</strong> {if $info->tags && $info->tag_info_url} [ <a href="{$info->tag_info_url}">Tag Details</a> ]{/if}<br />
{foreach $info->listing_tags as $tag}
<span style="white-space: nowrap;">{ldelim}listing addon='{$info->name}' tag='{$tag_name}'{rdelim}</span><br />
{/foreach}
<br />
{/if}
{if $info->pages}
<strong>Addon Pages Available:</strong>{if $info->page_url}  [ <a href="{$info->page_url}">Page Details</a> ]{/if}
<br />
{foreach item="page" from=$info->pages}
<a href="{$classifieds_url}?a=ap&amp;addon={$info->name}&amp;page={$page}" target="_blank">
	{if $info->pages_info.$page.title}
		{$info->pages_info.$page.title}
	{else}
		{$page|replace:'_':' '|capitalize:true}
	{/if}
</a><br />
{/foreach}  
{/if}
{if $conflicts}
<br />
<strong style="color:red;">Incompatible Addons Found:</strong><br />
These addons cannot be enabled at the same time, because they are not compatible with each other.

<ul style="margin-top: 2px; margin-bottom: 2px; color:red;">
{foreach from=$conflicts item="conflict"}
<li>{$conflict}</li>
{/foreach}
</ul>
{/if}
<br /><strong>Full Addon Description:</strong><br />
{$info->description}