{* 7.6.3-149-g881827a *}
{foreach $region_trees as $region_order => $levels}
	{if $levels@first}{continue}{/if}
	{foreach $levels as $region}
		{$region.name}{if !$region@last} &gt;{/if}
	{/foreach}
	{if !$levels@last}<br />{/if}
{/foreach}