{* 6.0.7-3-gce41f93 *}
{if $writeTD}
	<td class="thumbnail_td" style="text-align: center; vertical-align: middle;{if $thumbMaxWidth} width: {$thumbMaxWidth};{/if}">
{/if}

{if $popup}
	<a href="get_image.php?id={$imageID}" class="lightUpLink" onclick="return false;">
{else}
	<a href="{$link}">
{/if}
{$imgTag}
</a>

{if $writeTD}
</td>
{/if}
