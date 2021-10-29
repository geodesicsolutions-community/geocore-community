{* 7.4.0-15-g3f9f575 *}

	<div style="text-align: center; color: #980000; font-size: 12px;">
		<strong>Note:</strong> The minimum amount for an iDEAL transaction is &euro;1,20. The gateway will not appear as an option for payment of amounts less than this.<br />
	</div>

	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Mollie.nl API Key {$tooltips.api_key}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[api_key]" value="{$values.api_key}" />
		</div>
		<div class='clearColumn'></div>
	</div>