{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Store ID {$tooltips.store_id}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[store_id]" value="{$values.store_id}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			API Token {$tooltips.api_token}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[api_token]" value="{$values.api_token}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Encryption Type {$tooltips.crypttype}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[crypttype]" value="{$values.crypttype}" />
		</div>
		<div class='clearColumn'></div>
	</div>	

<div class="col_hdr_left">Moneris Testing Instructions</div>
<div>

			<p>Automatic testing: Using the Account Status switch above to set Testing Mode will cause all Moneris transactions to automatically use valid "testing" data.</p> 
			<p>Manual testing: Using any of the below credit card numbers with any future expiration date will cause Moneris to treat the transaction as a test, simulating approval conditions, though no money will change hands.<br />

			mastercard 5454545454545454<br />
			visa 4242424242424242<br />
			amex 373599005095005 (Amex will approve on .37 and .70)<br />
			diners 36462462742008<br /></p>
</div>