{* 16.09.0-79-gb63e5d8 *}
		<div class='table-responsive'>
			<table class='table table-hover table-striped table-bordered'>
				<thead>
				<tr class='col_hdr_top'>
					<td class='col_hdr default_column'>Default</th>
					<td class='col_hdr enabled_column'>Enabled</th>
					<td class='col_hdr name_column'>Gateway Name</th>
					<td class='col_hdr order_column'>Order</th>
					<td class='col_hdr action_column'>Action </th>
				</tr>
				</thead>
			</table>
			<ul class="expandable_list">
				{foreach $gateways as $row}
					<li id='row_for{$row.name}'>
						<input type="hidden" value="{$row.name}" class="gateway_name" />
						<table class="body">
							<tr>
								<td class='default_column'>
									<input type='radio' id='ena_radio_{$row.name}' class="defaultGateway" name='default_gateway' value='{$row.name}' {if $row.default}checked='checked' {elseif !$row.enabled}style='display: none;' {/if}/>
								</td>
								<td class='enabled_column'>
									<input type='checkbox' id='enabled_gateways_{$row.name}' class="enabledGateway" name='enabled_gateways[{$row.name}]' value='1' {if $row.enabled}checked='checked' {/if} />
								</td>
								<td class='name_column'>{$row.title}</td>
								<td class='order_column'>
									<a onclick="return false;" href="AJAX.php?controller=PaymentGateways&amp;action=position&amp;move=up&amp;item={$row.name}&amp;order={$row.display_order}&amp;group={$group}" class='mini_button' title='move up'{if $row@first} style="visibility: hidden;"{/if}>&nbsp;&and;&nbsp;</a>
									<a onclick="return false;" href="AJAX.php?controller=PaymentGateways&amp;action=position&amp;move=down&amp;item={$row.name}&amp;order={$row.display_order}&amp;group={$group}" class='mini_button' title='move down'{if $row@last} style="visibility: hidden;"{/if}>&nbsp;&or;&nbsp;</a>
								</td>
								<td class='action_column'>
									{if $row.show_config}
										<div id="update_config_{$row.name}">
											<a href="#configure_{$row.name}" class="mini_button gatewayConfigure">
												Configure
											</a>
											<a href="#save_{$row.name}" class="mini_button gatewaySave" style="display: none;">Save</a> 
											<a href="#cancel_{$row.name}" class="mini_cancel gatewayCancel" style="display: none;">Cancel</a>	
										</div>
									{/if}
								</td>

							</tr>
						</table>
						{if $row.show_config}
							<div id='container_{$row.name}' style='display: none;'></div>
						{/if}
					</li>
				{/foreach}	
			</ul>
		</div>
	</tbody>
</table>