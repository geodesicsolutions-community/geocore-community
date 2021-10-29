{* 17.10.0-5-g8bc0dc0 *}
{* failsafe in case entered something odd for column *}
{if $columns <= 0}
	{$columns=1}
{/if}


{$count=0}
{$column=0}
{$col_width=floor(100/$columns)}
{if !$showSubs&&$regionCount>$topCount}{$regionCount=$topCount}{/if}
<div class="category_navigation_box">
{strip}
	{if $tree==compact}
		{if $breadcrumb}
			<div class="compact_geographic_navigation_breadcrumb">
				{foreach from=$breadcrumb item=region name=geoNav}
					{if $smarty.foreach.geoNav.last}
						{$region.label}
						&nbsp;<a href='{$base_url}region=0'>{$msgs.clearRegions}</a>
					{/if}
				{/foreach}
			</div>
		{/if}
	{elseif $tree==full}
		{include file='breadcrumb.tpl'}
	{/if}
{/strip}
{foreach $regions as $region}
	{if $across_columns}
		{* For sites that want to order across the columns, use table format to avoid
			long names making it newline *}
		{if $region@first}
			<table class="geographic_navigation_column"><tbody><tr>
		{/if}
		{if $region@iteration>$columns && $region@iteration%$columns==1}
			<tr>
		{/if}
		<td class="element geographic_{$region.id}" style="width: {$col_width}%;">
	{else}
		{if $column<$columns && ($region@first || $count>=$regionCount/$columns)}
			{$column=$column+1}
			{if !$region@first}
				{if ($regionCount/$columns) < 1}
					{* prevent division-by-zero when that quantity gets cast to an int for mod *}
					{$count = 0}
				{else}
					{$count = $count % ($regionCount/$columns)}
				{/if}
			{/if}
			<div class="geographic_navigation_column" style="width: {$col_width}%;">
				<ul>
		{/if}
		<li class="element geographic_{$region.id}">
	{/if}
		{strip}<a href="{$region.link}">
			{$region.label}
		</a>{/strip}
		{if $countFormat}
			<span class="listing_counts">
				{if $countFormat=='ca'||$countFormat=='c'}
					({$region.listing_counts.classifieds})
				{/if}
				{if $countFormat=='ca'||$countFormat=='a'||$countFormat=='ac'}
					({$region.listing_counts.auctions})
				{/if}
				{if $countFormat=='ac'}
					({$region.listing_counts.classifieds})
				{/if}
				{if $countFormat=='all'}
					({$region.listing_counts.all})
				{/if}
			</span>
		{/if}
		{if $showSubs&&$region.sub_regions}
			{include file='navigation/subregions.tpl' sub_regions=$region.sub_regions}
			{$count=$count+$region.subregion_count}
		{/if}
	{if $across_columns}
		</td>
		{if $region@last||($region@iteration>=$columns && $region@iteration%$columns==0)}
			</tr>
		{/if}
		{if $region@last}
			</tbody></table>
		{/if}
	{else}
		</li>
		{$count=$count+1}
		{if $region@last||($column<$columns && $count>=$regionCount/$columns)}
				</ul>
			</div>
		{/if}
	{/if}
{foreachelse}
	{if $msgs.noRegions}
		<div class="no_results_box">{$msgs.noRegions}</div>
	{/if}
{/foreach}
</div>
<div class="clearfix"></div>

{if $current_region}
	<div class="center"> 
		<a href='{$base_url}region=0' class='button'>{$msgs.clearSelectionButton}</a>
	</div>
{/if}
