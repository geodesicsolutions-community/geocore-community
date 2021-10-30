{* 16.09.0-79-gb63e5d8 *}
<div class="count {if $is_latest}green{else}red{/if}">{$version}</div>
<span class="count_bottom">
{if $white_label}&nbsp;
{else}
	{if $is_latest}Up to date!
	{else}<a href="http://geodesicsolutions.com/support/updates/?product=GeoCore&amp;version={$version}" onclick="window.open(this.href); return false;">{$latestVersion} is available</a>
	{/if}
{/if}
</span>
