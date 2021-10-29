{* 7.1beta1-1050-g1a796be *}

<div class="{$current_color}">
	<div class="leftColumn">
		Additional Regions
	</div>
	<div class="rightColumn">
		{foreach $additional_regions as $region_tree}
			{foreach $region_tree as $region}
				{$region.name}
				{if !$region@last}&gt;{/if}
			{/foreach}
			{if !$region_tree@last}<br />{/if}
		{/foreach}
	</div>
	<div class="clearColumn"></div>
</div>