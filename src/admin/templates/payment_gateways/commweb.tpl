{* 7.5.3-139-gcd6e71c *}
	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Merchant ID {$tooltips.merchant_id}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[merchant_id]" value="{$values.merchant_id}" size="50" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Access Code {$tooltips.access_code}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[access_code]" value="{$values.access_code}" size="50" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Secure Hash Secret {$tooltips.hash_secret}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[hash_secret]" value="{$values.hash_secret}" size="50" />
		</div>
		<div class='clearColumn'></div>
	</div>