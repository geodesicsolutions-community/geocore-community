{* 7.5.3-36-gea36ae7 *}

	<div style="text-align: center; color: #980000; font-size: 14px;">
		<strong>Important Note:</strong> This is an implementation of the <strong>netcash.com</strong> gateway, which is NOT <strong>netcash.co.za</strong>.<br />
	</div>

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
			Currency Type
		</div>
		<div class='rightColumn'>
			<div class='rightColumn'>
				<select name="{$payment_type}[currency_type]">
					<option value="USD" {if $values.currency_type === 'USD'}selected="selected"{/if}>USD</option>
					<option value="EUR" {if $values.currency_type === 'EUR'}selected="selected"{/if}>EUR</option>
				</select>
			</div>
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Secret Key {$tooltips.secret_key}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[secret_key]" value="{$values.secret_key}" />
		</div>
		<div class='clearColumn'></div>
	</div>