{* 16.09.0-46-g852be8b *}

<div class="content_box">
	<h1 class="title my_account">
		{if $enabledAddons.profile_pics}
			<a href="{$classifieds_url}?a=4&amp;b=3">{addon author='geo_addons' addon='profile_pics' tag='show_pic' height="43"}</a>
		{/if}
		{$messages.500793}
	</h1>
</div>

{counter print=false start=0 assign=box_count}
{foreach from=$boxes item=box}
	<div class="content_box highlight_links">
		{if $box_count % 2 == 0}
		<h2 class="title" style="font-size: 0.9em; padding-left: 1em;">{$box.title}</h2>
		{else}
			<h3 class="title" style="font-size: 0.9em; padding-left: 1em;">{$box.title}</h3>
		{/if}
		
		{foreach from=$box.rows item=row}
			{cycle values='row_even,row_odd' assign='cellCSS'}
			{if $row.table}
				<span style="font-size: 0.8em; font-weight: bold; padding-left:1.5em;">{$row.label}</span>
				{foreach from=$row.table item=tableRow}
					<div class="{$cellCSS}" style="font-size: 0.8em; padding-left: 2em;">
						<a href="{$tableRow.link}">{$tableRow.title}</a>
						{if $tableRow.link2}<a href="{$tableRow.link2}" class="mini_button">{$tableRow.link2text}</a>{/if}
					</div>
				{/foreach}
			{else}
		<div class="{$cellCSS}" style="padding-left: 2em;">{$row.label} {if $row.link}<a href="{$row.link}" class="mini_button">{/if}{$row.data}{if $row.link}</a>{/if}</div>
			{/if}
		{/foreach}
	</div>

	{counter print=false assign=box_count}
{/foreach}
