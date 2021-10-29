{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Path to Geo Install
		</div>
		<div class='rightColumn'>
			{$values.geo_path}
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Logo Path {$tooltips.logo_path}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[logo_path]" value="{$values.logo_path}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Email {$tooltips.email}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[email]" value="{$values.email}" />
		</div>
		<div class='clearColumn'></div>
	</div>