{* 6.0.7-3-gce41f93 *}

{if $recurring}<input type="hidden" name="recurringId" id="recurringId" value="{$recurring->getId()}" />{/if}
<fieldset>
	<legend>Recurring Details for Recurring Billing{if $recurring} #{$recurring->getId()}{/if}</legend>
	<div id ='frm_order_details'>
{if $recurring}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Internal ID</div>
		<div class="rightColumn">{$recurring->getId()}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">ID for {$gateway}</div>
		<div class="rightColumn">{$recurring->getSecondaryId()}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">User</div>
		<div class="rightColumn">
			<a href="{$links.user}{$recurring->getUserId()}">
				{$username} ({$recurring->getUserId()})
			</a>
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">First Billing Date</div>
		<div class="rightColumn">{$recurring->getStartDate()|date_format}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Recurring Billing Paid Until</div>
		<div class="rightColumn"><span id="paidUntilValue">{$recurring->getPaidUntil()|date_format}</span></div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Payment Gateway Used</div>
		<div class="rightColumn">{$gateway}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">ID in Payment Gateway</div>
		<div class="rightColumn">{$recurring->getSecondaryId()}</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Price Details</div>
		<div class="rightColumn">{$recurring->getPricePerCycle()|displayPrice} every {$days} days</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Last Known Status</div>
		<div class="rightColumn">
			<span id="statusValue">{if $altStatus}{$altStatus}{else}{$recurring->getStatus()|capitalize}{/if}</span>
			{if !$altStatus}{include file="HTML/add_button.tpl" link_is_really_javascript="1" link=$links.refresh label="Status Refresh"}{/if}
		</div>
		<div class="clearColumn"></div>
	</div>
	{if $recurring->get('cancel_reason')}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">Recorded Cancel "Reason"</div>
			<div class="rightColumn">{$recurring->get('cancel_reason')}</div>
			<div class="clearColumn"></div>
		</div>
	{/if}
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Order</div>
		<div class="rightColumn">
			<a href="{$links.order}{$recurring->getOrderId()}">
				{$recurring->getOrderId()}
			</a>
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">
			Invoice for First Payment
		</div>
		<div class="rightColumn">
			{if $invoice}
				<a href="{$links.invoice}{$invoice}" class="lightUpLink">{$invoice}</a>
			{else}
				-
			{/if}
		</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Item Type</div>
		<div class="rightColumn">{$itemType}</div>
		<div class="clearColumn"></div>
	</div>
	{* display data specific to the order item type, if any *}
	{foreach from=$typeDetails item="eachType"}
		{foreach from=$eachType item="details"}
			{if $details.entire_box}
				{$details.entire_box}
			{else}
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn">{$details.label}</div>
					<div class="rightColumn">{$details.value}</div>
					<div class="clearColumn"></div>
				</div>
			{/if}
		{/foreach}
	{/foreach}
	{* display data specific to the gateway, if any *}
	{foreach from=$gatewayDetails item="eachGateway"}
		{foreach from=$eachGateway item="details"}
			{if $details.entire_box}
				{$details.entire_box}
			{else}
				<div class="{cycle values='row_color1,row_color2'}">
					<div class="leftColumn">{$details.label}</div>
					<div class="rightColumn">{$details.value}</div>
					<div class="clearColumn"></div>
				</div>
			{/if}
		{/foreach}
	{/foreach}
	
	{if $recurring->getStatus()!='canceled' && $canCancel}
		<div class='centercolumn' style='position:relative; text-align:center'>
			{include file="HTML/add_button.tpl" link_is_really_javascript="1" link=$links.cancel label="Cancel Recurring Billing"}
		</div>
	{/if}
		
{else}
	Invalid recurring billing, or recurring billing is missing needed data.
{/if}
	</div>
</fieldset>

<fieldset>
	<legend>Transactions &amp; Payment Signals Recieved</legend>
	<div>
		<p class="medium_font">
			<strong>Note:</strong> Not all payment gateways will generate 
			recurring billing transactions listed below each time a payment is made.
			<br /><br />See the user manual for more information. 
		</p>
		<table>
			<thead>
				<tr>
					<th class="col_hdr_left">Description</th>
					<th class="col_hdr_left">Date</th>
					<th class="col_hdr_left">Status</th>
					<th class="col_hdr_left">Amount</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$transactions item="transaction" key="transaction_id"}
					{cycle values="row_color1,row_color2" assign="row_color"}
					<tr>
						<td class="{$row_color}">
							{$transaction.desc}
						</td>
						<td class="{$row_color}">
							{$transaction.date|date_format}
						</td>
						<td class="{$row_color}">
							{if $transaction.status}Valid{else}Inactive / Not Valid{/if}
						</td>
						<td class="{$row_color} price">
							{if $transaction.amount==0.00}
								-
							{else}
								<span class="{$transaction.amount_class}">{$transaction.amount|displayPrice}</span>
							{/if}
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan="5">
							No transactions found for this invoice.
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</fieldset>


{if !$recurring}
	{include file="orders/get_order_form.tpl" ent=1}
{/if}