{* 16.09.0-79-gb63e5d8 *}
<div class="count {if $is_latest}green{else}red{/if}">{$version}</div>
<span class="count_bottom">
{if $white_label}&nbsp;
{else}
	{if $is_latest}Up to date!
	{else}<a href="https://geodesicsolutions.org/update-instructions/?product=GeoCore&amp;version={$version}" target="_blank">{$latestVersion} is available</a>
	{/if}
{/if}
</span>
