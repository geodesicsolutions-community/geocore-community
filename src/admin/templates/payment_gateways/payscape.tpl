{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Payscape Username {$tooltips.username}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[username]" value="{$values.username}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Payscape Password {$tooltips.password}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[password]" value="{$values.password}" />
		</div>
		<div class='clearColumn'></div>
	</div>