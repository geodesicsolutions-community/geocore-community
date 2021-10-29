{* 7.5.3-36-gea36ae7 *}

	{$adminMsgs}
	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Vendor {$tooltips.vendor}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[vendor]" value="{$values.vendor}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			User {$tooltips.user}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[user]" value="{$values.user}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Password {$tooltips.password}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[password]" value="{$values.password}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Layout {$tooltips.layout}
		</div>
		<div class='rightColumn'>
			<select name="{$payment_type}[layout]">
				<option value="A"{if $values.layout == 'C'} selected="selected"{/if}>Layout A</option>
				<option value="B"{if $values.layout == 'C'} selected="selected"{/if}>Layout B</option>
				<option value="C"{if $values.layout == 'C'} selected="selected"{/if}>Layout C</option>
			</select>
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div style="text-align: center; color: #980000; font-size: 12px;">
		<strong>Configuration Note:</strong> You MUST set the "Enable Secure Token" option to "Yes" within the PayPal Manager's Set Up section.<br />
	</div>
	