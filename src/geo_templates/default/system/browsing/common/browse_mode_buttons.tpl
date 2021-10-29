{* 7.6.3-149-g881827a *}

<div class="browse_mode_buttons">
	{if $browse_sort_dropdown_display=='always'||($browse_sort_dropdown_display=='gallery_only'&&$browse_view=='gallery')}
		{$browse_mode_txt.sort_by}
		<a href="{$browse_sort_url}" style="display: none;"></a>
		<select name="c" class="browse_sort_dropdown field">
			{foreach $browse_mode_txt.sort as $sort_id => $sort_label}
				<option value="{$sort_id}"{if $browse_sort_c==$sort_id} selected="selected"{/if}>{$sort_label}</option>
			{/foreach}
		</select>
	{/if}
	{if $display_browse_view_links}
		&nbsp;
		{strip}
			{foreach $display_browse_view_links as $type}
				<a href="{$browse_view_url}{$type}" id="browse_view_btn_{$type}" class="view_mode_link{if $browse_view==$type} active{/if}" title="{$browse_mode_txt.view.$type|escape}">
					<img src='{external file="images/icon_{$type}.png"}' alt="{$browse_mode_txt.view.$type|escape}" />
				</a>
			{/foreach}
		{/strip}
	{/if}
</div>