{* 7.5.3-125-gf5f0a9a *}

{if $balance_count == 0}
	<div class="content_box">
		<h1 class="title my_account">{$messages.3215}</h1>
		<h3 class="subtitle">{$messages.3216}</h3>
		<p class="page_instructions">{$messages.3235}</p>
	</div>
	<div class="center">
		<a href="{$classifieds_file_name}?a=4" class="button">{$messages.3236}</a>
	</div>
{else}
	<div class="content_box">
		<h1 class="title my_account">{$messages.3214}</h1>
		<h3 class="subtitle">{$messages.3215}</h3>
		<p class="page_instructions">{$messages.3216}</p>
		
		<div class="center">
			<strong>{$messages.3217}{$account_balance|displayPrice}</strong><br /><br />
			<a href="{$classifieds_file_name}?a=cart&amp;action=new&amp;main_type=account_balance" class="button">{$messages.3218}</a>
		</div>
	</div>
	<br />
	<div class="content_box">
		<table style="margin: 0 auto; text-align: center; width: 100%; border: none;">
			<tr class="column_header">
				<td>{$messages.3219}</td>
				<td>{$messages.3220}</td>
				<td>{$messages.3221}</td>
				<td>{$messages.3222}</td>
				<td>{$messages.3223}</td>
			</tr>
			{foreach from=$transactions item=data}
				<tr class="{cycle values='row_odd,row_even'}">
					<td valign="top">{$data.id}</td>
					<td valign="top">{$data.date}</td>
					<td valign="top" class="highlight_links">
						{if $data.adjustment}
							{if $data.orderId}
								{if $data.amount > 0}
									{$messages.3261}
								{else}
									{$messages.500439}
								{/if}
							{else}
								{$messages.500440} {$data.adminNote}
							{/if}
						{else}
							{$messages.500441}
							{if $data.invoice}
								<a href="{$invoice_url}{$data.invoice}">{$data.invoice}</a>
							{/if}
						{/if}
					</td>
					<td valign="top">
						{if $data.status}
							{$messages.3263}
						{else}
							{$messages.3262}
						{/if}
					</td>
					<td valign="top">{$data.amount|displayPrice}</td>
				</tr>
			{/foreach}
		</table>
	</div>
	
	{if $pagination}
		{$pagination}
	{/if}
	<div class="center">
		<a href="{$classifieds_file_name}?a=4" class="button">{$messages.3236}</a>
	</div>
{/if}