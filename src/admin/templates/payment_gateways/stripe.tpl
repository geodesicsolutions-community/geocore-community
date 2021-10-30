{* 16.09.0-39-g2d19902 *}
	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Publishable Key<br />
			(<a href="https://dashboard.stripe.com/account/apikeys">Find your keys here</a>)
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[public_key]" value="{$values.public_key}" size="50" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Secret Key<br />
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[api_key]" value="{$values.api_key}" size="50" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Currency you accept payment in
		</div>
		<div class='rightColumn'>
			<select name="{$payment_type}[currency_type]">
				{foreach $currencies as $code => $name}
					<option value="{$code}" {if $values.currency_type === $code}selected="selected"{/if}>{$name}</option>
				{/foreach}
			</select>
		</div>
		<div class='clearColumn'></div>
	</div>