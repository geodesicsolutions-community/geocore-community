{* 7.6.3-149-g881827a *}
{foreach $region_trees.0 as $region}
	{$region.name}{if !$region@last} &gt;{/if}
{/foreach}