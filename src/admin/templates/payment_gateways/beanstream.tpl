{* 7.4.2-35-gb499e5b *}
	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Merchant ID {$tooltips.merchant_id}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[merchant_id]" value="{$values.merchant_id}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			API Passcode {$tooltips.api_passcode}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[api_passcode]" value="{$values.api_passcode}" />
		</div>
		<div class='clearColumn'></div>
	</div>