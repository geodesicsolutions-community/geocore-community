{* 7.6.3-149-g881827a *}
{* Displays the regions for a listing, used in the listing_regions tag for this addon. *}
{if $regions}{strip}
	{foreach from=$regions item=thisRegion name=listingRegionLoop}
		{$thisRegion}{if !$smarty.foreach.listingRegionLoop.last} &gt; {/if}
	{/foreach}
{/strip}{/if}