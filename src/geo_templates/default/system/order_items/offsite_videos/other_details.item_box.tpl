{* 6.0.7-3-gce41f93 *}

<div class="listing_extra_item">
	<label>
		{$messages.500942} {$help_link}
	</label>

	<div class="listing_extra_cost">
		<select name="c[new_offsite_videos]" class="field">
			{foreach from=$vid_dropdown item="price" key="i"}
				<option value="{$i}"{if $i eq $current} selected="selected"{/if}>
					{$i} {if $i == 1}{$messages.500944}{else}{$messages.500945}{/if} - {$price}
				</option>
			{/foreach}
		</select>
	</div>
	<div class="clr"></div>
</div>
