{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Partner {$tooltips.partner}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[partner]" value="{$values.partner}" />
		</div>
		<div class='clearColumn'></div>
	</div>
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
			Currency
		</div>
		<div class='rightColumn'>
			<select name="{$payment_type}[currency]">
				{$currency_options}
			</select>
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Test CC Numbers {$tooltips.testing_cc}
		</div>
		<div class='rightColumn'>
			<ul>
			<li><strong>American Express</strong> 378282246310005</li>
			<li><strong>Amex Corporate</strong> 378734493671000</li>
			<li><strong>Australian BankCard</strong> 5610591081018250</li>
			<li><strong>Diners Club</strong> 30569309025904</li>
			<li><strong>Discover</strong> 6011111111111117</li>
			<li><strong>JCB</strong> 3530111333300000</li>
			<li><strong>MasterCard</strong> 5555555555554444</li>
			<li><strong>Visa</strong> 4111111111111111</li>
			<li><strong>Switch/Solo (Paymentech)</strong> 6331101999990016</li>
			</ul>
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Test CC Reactions {$tooltips.testing_cc_reactions}
		</div>
		<div class='rightColumn'>
			<table cellpadding=3 cellspacing=1 border=0>
			<tr>
				<td class='col_hdr'>Amount of Transaction</td><td class='col_hdr'>Type of Result Testing for</td>
			</tr>
			<tr><td class='medium_font' align='center'>$0-$1000</td><td class='medium_font' align='center'>Approved</td></tr>
			<tr>
			</tr>
			<tr class='row_color2'>
				<td class='medium_font' align='center'>$2001+</td><td class='medium_font' align='center'>Declined</td>
			</tr>
		</table>
		</div>
		<div class='clearColumn'></div>
	</div>