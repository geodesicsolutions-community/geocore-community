{* 7.1beta1-1034-ga8c6b0e *}

<div class="listing_extra_item">
	<label>
		{$messages.502059} {$help_link}
	</label>

	<div class="listing_extra_cost">
		<select name="c[new_additional_regions]" class="field">
			{foreach $region_dropdown as $i => $price}
				<option value="{$i}"{if $i eq $current} selected="selected"{/if}>
					{$i} {if $i == 1}{$messages.502061}{else}{$messages.502062}{/if} - {$price}
				</option>
			{/foreach}
		</select>
	</div>
	<div class="clr"></div>
</div>
