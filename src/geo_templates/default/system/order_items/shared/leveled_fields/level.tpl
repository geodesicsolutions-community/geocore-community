{* 7.6.3-37-gefea452 *}

{if !$is_ajax}<div class="leveled_level_box leveled_{$lev_field.leveled_field}_{$info.level.level}{if $info.level.always_show} leveled_always_show{/if}"{if $info.level.level>1&&!$info.value_info&&$info.level.always_show!='yes'} style="display: none;"{/if}>{/if}
	{if $info.level.label}
		<div class="leveled_level_label">{$info.level.label}:</div>
		<br />
	{/if}
	<ul class="leveled_values{if $lev_field.leveled_field=='cat'} leveled_cat{/if}">
		{foreach $info.value_info.values as $value}
			{if $value.is_off_page&&$value@last}
				<li>
					<div class="cntr">~</div>
				</li>
			{/if}
			<li class="leveled_value">
				{* Hide the input *}
				<div class="leveledHiddenRadio">
					<input type="radio" class="leveled_radio{if $leveledCatSearch} leveled_cat_search{/if}" name="b[leveled][{$lev_field.leveled_field}][{$info.level.level}]" value="{$value.id}"{if $value.selected} checked="checked"{/if} />
				</div>
				{$value.name}
			</li>
			{if $value.is_off_page&&$value@first}
				<li>
					<div class="cntr">~</div>
				</li>
			{/if}
		{foreachelse}
			<li class="leveled_value_empty">&nbsp;</li>
		{/foreach}
	</ul>
	{if $leveled_clear_selection_text && $lev_field.can_edit}
		<br />
		<a href="#clear" class="leveled_clear obvious">{$leveled_clear_selection_text}</a>
	{/if}
	{$info.value_info.pagination}
	{if $lev_field.can_edit}
		<input type="hidden" name="b[leveled_page][{$lev_field.leveled_field}][{$info.level.level}]" value="{$info.page}" />
	{/if}
{if !$is_ajax}</div>{/if}