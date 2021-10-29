{* 17.07.0-15-g52acd14 *}

<div style="max-width: 100%;">
	<form action="#" method="post" style="padding: 1em; max-width: 600px;" class="cost-options-edit-form">
		<p class="note_box">
			{$messages.502267}
		</p>
		<table>
			<thead>
				<tr style="border-bottom: thin solid #000;">
					{foreach $combined_options as $group}
						<th style="padding-right: 1em;">{$group.label|fromDB}</th>
					{/foreach}
					<th>{$messages.502268}</th>
					<th></th>
				</tr>
			</thead>
			<tbody class="cost-options-combined-tbody">
				<tr class="cost-options-new-combined-row" style="display: none;">
					{foreach $combined_options as $group}
						<td>
							<select class="cost-options-combined-selection">
								{foreach $group.options as $option}
									<option value="{$option.comb_id}"{if $option@first} selected="selected"{/if}>{$option.label|fromDB}</option>
								{/foreach}
							</select>
						</td>
					{/foreach}
					<td>
						<input type="number" class="cost-options-number" name="cost_options_quantity_" min="0" max="99999999" size="4" placeholder="0">
					</td>
					<td>
						<a href="#" class="cancel cost_options_remove_option">{$messages.502269}</a>
					</td>
				</tr>
				{foreach $cost_options_quantity as $hash => $quantity}
					<tr>
						{foreach $combined_options as $group}
							<td>
								<select class="cost-options-combined-selection">
									{foreach $group.options as $option}
										<option value="{$option.comb_id}"{if $hash|strstr:$option.comb_id} selected="selected"{/if}>{$option.label|fromDB}</option>
									{/foreach}
								</select>
							</td>
						{/foreach}
						<td>
							<input type="number" class="cost-options-number" name="cost_options_quantity[{$hash|escape}]" min="0" max="99999999" size="4" placeholder="0" value="{$quantity}">
						</td>
						<td>
							<a href="#" class="cancel cost_options_remove_option">{$messages.502269}</a>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		<div class="cntr">
			<a href="#" class="cost-option-add-combined-quantity">{$messages.502270}</a>
			<br />
		</div>
	</form>
</div>


<script>
	jQuery('.cost-options-edit-form').submit(geoListing.costOptions.setCombinedQuantitySubmit);
	jQuery('.cost-option-add-combined-quantity').click(geoListing.costOptions.addCombinedQuantity);
	jQuery('.cost-options-combined-selection').change(geoListing.costOptions.quantitySelectChange);
	jQuery('.cost_options_remove_option').click(geoListing.costOptions.remOption);
</script>