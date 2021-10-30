{* 17.07.0-15-g52acd14 *}

<div style="max-width: 100%;">
	<form action="#" method="post" style="padding: 1em; max-width: 600px;" class="cost-options-edit-form">
		<p class="note_box">
			{$messages.502248}
		</p>
		{if $new}
			<input type="hidden" name="new" value="1">
		{else}
			<input type="hidden" name="group_id" value="{$group_id}">
		{/if}
		{if $db_group_id}
			<input type="hidden" name="db_group_id" value="{$db_group_id}">
		{/if}
		<strong>{$messages.502249}</strong>
		<input type="text" name="label" value="{$info.label|fromDB}" placeholder="{$messages.502250}" maxlength="{$fields->cost_options->text_length}">
		<br /><br />
		<label><input type="checkbox" id="cost-options-quantity-checkbox"{if $quantity_type!='none'} checked="checked"{/if}> {$messages.502251}</label>
			<span id="cost-options-quantity-combined-span"{if $quantity_type!='combined'} style="display: none;"{/if}>{$messages.502252}</span>
			<span id="cost-options-quantity-individual-span"{if $quantity_type!='individual'} style="display: none;"{/if}>{$messages.502253}</span>
		
		<input type="hidden" id="cost-options-quantity-type" name="quantity_type" value="{$quantity_type}">
		
		<div id="cost-options-combine-option-box" title="Use Combined Quantity?" style="display: none;">
			<p class="note_box">{$messages.502254}</p>
			<br />
			<div class="cntr">
				<a href="#" onclick="jQuery('#cost-options-combined-examples').dialog({ width: 'auto'}); return false;">{$messages.502255}</a>
			</div>
			<div id="cost-options-combined-examples" style="display: none; white-space: nowrap; font-size: .75rem;">
				{$messages.502256}
			</div>
		</div>
		{if $maxImages>0}
			<div id="cost-options-image-slot-help" title="File Slot #" style="display: none;">
				<p>{$messages.502257}</p>
			</div>
		{/if}
		<br />
		<hr />
		
		
		
		<div style="overflow: auto;">
			<table>
				<thead>
					<tr style="border-bottom: thin solid #000;">
						<th></th>
						<th style="padding-right: 1em;">{$messages.502258}</th>
						<th style="padding-right: 1em;">{$messages.502259}</th>
						<th class="cost-options-individual-quantity" style="padding-right: 1em;{if $quantity_type!='individual'} display: none;{/if}">{$messages.502260}</th>
						{if $maxImages>0}
							<th style="padding-right: 1em;">
								<a href="#" id="cost-options-image-slot-help-link" style="cursor: help;">{$messages.502261}</a>
							</th>
						{/if}
						<th></th>
					</tr>
				</thead>
				<tbody class="cost_options_edit_tbody">
					<tr class="cost_options_new_row" style="display: none;">
						<td>
							<span class="cost-options-sort-icon ui-icon ui-icon-arrowthick-2-n-s"></span>
						</td>
						<td>
							<input type="text" name="options[new][label][]" placeholder="{$messages.502262}" value="" maxlength="{$fields->cost_options->text_length}">
						</td>
						<td style="white-space: nowrap;">
							<span class="precurrency">{$precurrency}</span> <input type="text" name="options[new][cost_added][]" placeholder="{$messages.502263}" size="5">
						</td>
						<td class="cost-options-individual-quantity"{if $quantity_type!='individual'} style="display: none;"{/if}>
							<input type="number" name="options[new][ind_quantity_remaining][]" value="1" min="0" max="10000000" size="3">
						</td>
						{if $maxImages>0}
							<td>
								<input type="number" name="options[new][file_slot][]" placeholder="{$messages.502264}" min="1" max="{$maxImages}" size="4">
							</td>
						{/if}
						<td>
							<a href="#new" class="cancel cost_options_remove_option">{$messages.502265}</a>
						</td>
					</tr>
					{foreach $info.options as $option_id => $option}
						<tr class="cost_options_row">
							<td>
								<span class="cost-options-sort-icon ui-icon ui-icon-arrowthick-2-n-s"></span>
							</td>
							<td>
								<input type="text" name="options[{$option_id}][label]" value="{$option.label|fromDB}" placeholder="{$messages.502262}" value="">
								{if $option.option_id}
									{* this has a pre-existing db id that needs to be preserved (probably because this is a listing edit *}
									<input type="hidden" name="options[{$option_id}][option_id]" value="{$option.option_id}" />
								{/if}
							</td>
							<td style="white-space: nowrap;">
								<span class="precurrency">{$precurrency}</span> <input type="text" name="options[{$option_id}][cost_added]" value="{$option.cost_added|displayPrice:'':''}" placeholder="{$messages.502263}" size="5">
							</td>
							<td class="cost-options-individual-quantity"{if $quantity_type!='individual'} style="display: none;"{/if}>
								<input type="number" name="options[{$option_id}][ind_quantity_remaining]" value="{$option.ind_quantity_remaining}" min="0" max="10000000" size="3">
							</td>
							{if $maxImages>0}
								<td>
									<input type="number" name="options[{$option_id}][file_slot]" value="{if $option.file_slot>0}{$option.file_slot}{/if}" placeholder="{$messages.502264}" min="1" max="{$maxImages}" size="4">
								</td>
							{/if}
							<td>
								<a href="#" class="cancel cost_options_remove_option">{$messages.502265}</a>
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="cntr">
			<a href="#" class="cost_option_add_option">{$messages.502266}</a>
			<br />
		</div>
	</form>
</div>
<script>
	jQuery('.cost-options-edit-form').submit(geoListing.costOptions.editSubmit);
	
	jQuery('.cost_option_add_option').click(geoListing.costOptions.addOption);
	
	jQuery('.cost_options_remove_option').click(geoListing.costOptions.remOption);
	
	//make the add selection button work
	jQuery('#cost-options-quantity-checkbox').click(geoListing.costOptions.checkQuantity);
	
	jQuery('#cost-options-image-slot-help').dialog({ autoOpen : false, modal: true});
	jQuery('#cost-options-image-slot-help-link').click(function (e) {
		e.preventDefault();
		jQuery('#cost-options-image-slot-help').dialog('open');
	});
	
	jQuery('.cost_options_edit_tbody').sortable({
		handle: '.cost-options-sort-icon'
	});
	
	if (jQuery('.cost_options_row').length >= geoListing.costOptions._limits.max_options_per_group) {
		//reached max number of options, hide the button
		jQuery('.cost_option_add_option').hide();
	}
	
	{if $new}
		//start off with at least 2 entries for new options
		jQuery('.cost_option_add_option').click()
			.click();
	{/if}
	gjUtil.updateCurrencies();
</script>