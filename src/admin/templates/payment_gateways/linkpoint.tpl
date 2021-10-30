{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Store Number
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[store_number]" value="{$store_number}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Absolute Server Path to Linkpoint PEM file {$tooltips.cert_path}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[cert_path]" value="{$cert_path}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Require CVV2 Code
		</div>
		<div class='rightColumn'>
			<input type='hidden' name="{$payment_type}[use_cvv2]" value="0" />
			<input type='checkbox' name="{$payment_type}[use_cvv2]" value="1" {$cvv2_checked} />
		</div>
		<div class='clearColumn'></div>
	</div>