{* 7.5.3-36-gea36ae7 *}

{if !$skipUl}<nav class="breadcrumb geographic_navigation_breadcrumb">{/if}
	<div class="highlight">{$msgs.currentRegion}</div>
	{if $msgs.allRegions}<a href="{$base_url}region=0">{$msgs.allRegions}</a>{/if}
	
	{foreach from=$breadcrumb item=region name=regionTree}
		{if $region@last}<div class="active{if $region.onlyRegionOnLevel} onlyRegionOnLevel{/if}">{else}<a href="{$region.link}"{if $region.onlyRegionOnLevel} class="onlyRegionOnLevel"{/if}>{/if}
			{$region.label}
		{if not $region@last}</a>{else}</div>{/if}
	{/foreach}
{if !$skipUl}</nav>{/if}