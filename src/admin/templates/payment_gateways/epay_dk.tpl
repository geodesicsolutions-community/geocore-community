{* 7.4.2-57-gc8cc0f7 *}

	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Merchant Number {$tooltips.merchantnumber}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[merchantnumber]" value="{$values.merchantnumber}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Hash Secret {$tooltips.hash_secret}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[hash_secret]" value="{$values.hash_secret}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			<a href="http://tech.epay.dk/en/currency-codes" onclick="window.open(this.href); return false;">Currency Code</a> {$tooltips.currency_code}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[currency_code]" value="{$values.currency_code}" />
		</div>
		<div class='clearColumn'></div>
	</div>