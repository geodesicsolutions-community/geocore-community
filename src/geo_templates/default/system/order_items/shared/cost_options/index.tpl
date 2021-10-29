{* 17.07.0-15-g52acd14 *}

{if !$ajax}
	<div style="display: inline-block; vertical-align: top;">
		<a href="#" class="button" id="add_buyer_option_button">{$messages.502231}</a>
	</div>
	<div id="add-cost-dialog-box"></div>
{/if}

{if !$ajax}<div style="vertical-align: top;" id="cost_options_box" class="clearfix">{/if}
	<div class="cost-options-box-sortbox ui-sortable clearfix">
		{foreach $session_variables.cost_options as $group_id => $option_group}
			{if $option_group.options|count > 0}
				{$option_total=0}
				<div class="cost-option-box{if $option_group.quantity_type=='combined'} cost-option-quantity-combined{/if}" id="costgroup-{$group_id}">
					<a href="#{$group_id}" class="cost_options_del_group" style="float: right;">
						<img src="{external file='images/buttons/delete.png'}" alt="{$messages.502232}" style="width: 1.5em;">
					</a>
					<strong class='cost-option-group-label'>
						<span class="cost-options-sort-icon ui-icon ui-icon-arrow-4"></span>
						{$option_group.label|fromDB}
					</strong>
					<a href="#{$group_id}" class="cost_options_edit_group">
						<img src="{external file='images/buttons/edit.png'}" alt="edit" style="width: 1.2em;">
					</a>
					<br />
					{if $option_group.error}
						<div class="error_message clearfix">{$option_group.error}</div>
					{/if}
					{if $option_group.quantity_type=='combined'}
						<span class="cost-options-combined-quantity-label">{$messages.502233}</span>
					{/if}
					<hr>
					<table class="cost-option-table">
						<thead>
							<tr style="border-bottom: thin solid #000;">
								<th style="padding-right: 1em; white-space: nowrap;">{$messages.502234}</th>
								<th style="padding-right: 1em; white-space: nowrap;">{$messages.502235}</th>
								{if $option_group.quantity_type=='individual'}
									<th style="padding-right: 1em; white-space: nowrap;">{$messages.502236}</th>
								{/if}
								{if $maxImages>0}
									<th style="padding-right: 1em; white-space: nowrap;">{$messages.502237}</th>
								{/if}
							</tr>
						</thead>
						<tbody>
							{foreach $option_group.options as $option}
								{if $option.comb_id}{$temp_options_hash.{$option.comb_id}=$option.label}{/if}
								<tr>
									<td>
										<span title="{$option.label}">{$option.label|fromDB}</span>
										{if $option.error}
											<div class="error_message" title="{$option.error|escape}">{$option.error}</div>
										{/if}
									</td>
									<td>
										<span class="precurrency">{$precurrency}</span>
										{$option.cost_added|displayPrice:'':''}
									</td>
									{if $option_group.quantity_type=='individual'}
										{$option_total=$option_total+$option.ind_quantity_remaining}
										<td>{$option.ind_quantity_remaining}</td>
									{/if}
									{if $maxImages>0}
										<td class="cntr">
											{if $option.file_slot>0}
												{$option.file_slot}
											{else}
												-
											{/if} 
										</td>
									{/if}
								</tr>
							{/foreach}
							{if $option_group.quantity_type=='individual'}
								<tr>
									<td colspan="2" style="text-align: right;">{$messages.502238}</td>
									<td>{$option_total}</td>
								</tr>
							{/if}
						</tbody>
					</table>
				</div>
			{/if}
		{/foreach}
	</div>
	{if $session_variables.cost_options_quantity}
		{$cost_option_quantity_total=0}
		<label class="field_label">{$messages.502239}</label>
		<div class="cost-option-combined-quantity-box">
			<table class="cost-option-table">
				<thead>
					<tr>
						<th>{$messages.502240}</th>
						<th>{$messages.502241}</th>
					</tr>
				</thead>
				<tbody>
					{foreach $session_variables.cost_options_quantity as $hash => $quantity}
						{$cost_option_quantity_total=$cost_option_quantity_total+$quantity}
						<tr>
							<td>
								{foreach '_'|explode:$hash as $option}
									{$temp_options_hash.$option}{if !$option@last} <span class="cost-options-combined-option-sep">+</span> {/if}
								{/foreach}
							</td>
							<td>{$quantity}</td>
						</tr>
					{/foreach}
					<tr>
						<td style="text-align: right;">{$messages.502242}</td>
						<td>{$cost_option_quantity_total}</td>
					</tr>
				</tbody>
			</table>
		</div>
	{/if}
{if !$ajax}</div>{/if}

{if !$ajax}
<br />
<div class="cntr">
	<a href="#" class="button" id="cost-options-set-combined-quantity">{$messages.502243}</a>
</div>
<div id="dialog-confirm-cost-options-delete" title="Delete Selection?" style="display: none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{$messages.502244}</p>
</div>
<div id="dialog-confirm-cost-options-reset-combined" title="Reset Combined Quantity?" style="display: none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{$messages.502245}</p>
</div>

{add_footer_html}
<script>
	jQuery(function () {
		geoListing._adminId = {$adminId};
		geoListing._userId = {$userId};
		
		//limits
		geoListing.costOptions._limits = {
			label_length : {$fields->cost_options->text_length},
			max_groups : {$cost_option_max_groups},
			max_options_per_group : {$cost_option_max_options}
		};
		
		//messages
		//TODO text
		geoListing.costOptions._msgs.cancel = '{$messages.502246|escape_js}';
		geoListing.costOptions._msgs.ok = '{$messages.502247|escape_js}';
		
		jQuery('#dialog-confirm-cost-options-delete').dialog({
			modal: true,
			autoOpen: false,
			buttons: {
				'Delete Selection' : function () {
					jQuery(this).dialog('close');
					//delete
					geoListing.costOptions.deleteGroupClick();
				},
				'Cancel' : function () {
					jQuery(this).dialog('close');
				}
			}
		});
		
		jQuery('#add-cost-dialog-box').dialog({ autoOpen : false});
		geoListing.costOptions.init();
	});
</script>
{/add_footer_html}{/if}