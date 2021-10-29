{* 7.6.3-149-g881827a *}
{foreach $region_trees as $levels}
	{foreach $levels as $region}
		{$region.name}{if !$region@last} &gt;{/if}
	{/foreach}
	{if !$levels@last}<br />{/if}
{/foreach}