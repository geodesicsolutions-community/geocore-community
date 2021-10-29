{if !$invoiceOnly}
<div class="content_box">
	{if !$in_admin}
		<h1 class="title">{$messages.3173}</h1>
		<h3 class="subtitle">{$messages.3174}</h3>
	{else}
		<h1 class="title">Invoice</h1>
	{/if}
{/if}

<div class="{if $in_admin && !$print}printBox_lightbox{elseif !$in_admin && !$print}printBox{else}printBox_printFriendly{/if}">
	
	{if $in_admin && !$print}
		{* this is the admin lightbox popup -- add a close button *}
		<a href="javascript:void(0);" class="mini_button closeLightUpBox">
			Close
		</a>
	{/if}
	
	<a href="javascript:void(0);" class="mini_button" onclick="{if $print}window.print();{else}window.open('{$printUrl}');{/if}">
		{$messages.501151}
	</a>
</div>
	
	<div class="invoice_shell">
		<a href="index.php" id="header_logo"> </a>
		<div class="clear"></div>
		
		<div class='invoice_title'>{$messages.502445}</div>
	
		<!-- START RIGHT COLUMN -->
		<div class="invoice_right_column">

			<div class="invoice_box">
				<div class="invoice_row_odd">
					<strong class="right"># {$invoice.invoice_id}</strong> {$messages.3176}
				</div>
				<div class="invoice_row_odd">
					<strong class="right"># {$invoice.order_id}</strong> {$messages.500515}
				</div>
			</div>

			<div class="invoice_box">
				<div class="invoice_row_odd">
					<strong class="right">{$invoice.invoice_date|format_date:$short_date_format}</strong> {$messages.3203}
				</div>
				<div class="invoice_row_odd">
					<strong class="right">{$invoice.invoice_due_date|format_date:$short_date_format}</strong> {$messages.500516}
				</div>
			</div>
			<div class="invoice_box">
				<div class="invoice_row_even invoice_extra_pad">
					<strong class="amount_due right">{$invoice.pay_amount|displayPrice}</strong> {$messages.500517}
				</div>
			</div>

		</div>
		<!-- END RIGHT COLUMN -->
	
	
		<!-- START LEFT COLUMN -->
		<div class="invoice_left_column">
			<div class="invoice_label">{$messages.500518}</div>
			<div class="invoice_box">
				<div class="invoice_row_odd">
					<strong>{$site_name}</strong><br />
					{$invoice.company_address}
				</div>
			</div>

			<div class="invoice_label">{$messages.500519}</div>
			<div class="invoice_box">
				<div class="invoice_row_even">
					{if $invoice.client.firstname OR $invoice.client.lastname}
						<strong>{$invoice.client.firstname} {$invoice.client.lastname}</strong><br />
					{/if}
					{if $invoice.client.address OR $invoice.client.address_2}
						{$invoice.client.address} {$invoice.client.address_2}<br />
					{/if}
					{if $invoice.client.city OR $invoice.client.state OR $invoice.client.zip OR $invoice.client.country}
						{* |fromDB the state and country because they come from geoRegion selectors unlike the rest of the fields, which should be plaintext *}
						{$invoice.client.city}, {$invoice.client.state|fromDB}, {$invoice.client.country|fromDB} {$invoice.client.zip}
					{/if}
					<br /><br />
					{if $invoice.client.phone}
						<strong>{$messages.500520}</strong> {$invoice.client.phone}<br />
					{/if}
					{if $invoice.client.phone2}
						<strong>{$messages.500521}</strong> {$invoice.client.phone2}<br />
					{/if}
					{if $invoice.client.email}
						<strong>{$messages.500522}</strong> {$invoice.client.email}<br />
					{/if}
					{if $invoice.client.email2}
						<strong>{$messages.500523}</strong> {$invoice.client.email2}<br />
					{/if}
					{if $invoice.client.company_name}
						<strong>{$messages.500524}</strong> {$invoice.client.company_name}
					{/if}
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<!-- END LEFT COLUMN -->
		<div class="invoice_label">{$messages.3178}</div>
		<div class="invoice_box">
			<table>
				<thead>
					<tr>
						<th>{$messages.500525}</th>
						<th>{$messages.500526}</th>
						{* uncomment to show gateway name on invoice: <th>{$messages.500527}</th> *}
						<th>{$messages.500528}</th>
						<th>{$messages.500529}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$invoice.transactions item="trans" key="transaction_id"}
						{cycle values="invoice_row_even,invoice_row_odd" assign="row_color"}
						<tr>
							<td class="{$row_color}">
								[{$transaction_id}] {$trans.desc}
							</td>
							<td class="{$row_color}">
								{$trans.date|format_date:$short_date_format}
							</td>
							{* uncomment to show gateway name on invoice
							<td class="{$row_color}">
								{$trans.type} {if $trans.amount < 0}{$messages.500530}{elseif $trans.amount >0}{$messages.500531}{/if}
							</td>
							*}
							<td class="{$row_color}">
								{if $trans.status}{$messages.500532}{else}{$messages.500533}{/if}
							</td>
							<td class="{$row_color} price">
								<span class="{$trans.amount_class}">{$trans.amount|displayPrice}</span>
							</td>
						</tr>
					{foreachelse}
						<tr>
							<td colspan="5">
								{$messages.500534}
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	
		<div class="invoice_total">
			<div class="invoice_box">
				<div class="invoice_row_title">
					<strong class="right amount_{if $invoice.invoice_amount > 0}paid{elseif $invoice.invoice_amount < 0}due{else}zero{/if}">{$invoice.invoice_amount|displayPrice}</strong>
					<strong>{$messages.3177}</strong>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		{if $messages.500535|strip}
			<div class="invoice_box">
				<div class="invoice_row_even invoice_end">
					<strong>{$messages.500535}</strong>
					<div class="clear"></div>
				</div>
			</div>
		{/if}
	</div>
	<br />
{if !$invoiceOnly}
</div>
<br />
{/if}