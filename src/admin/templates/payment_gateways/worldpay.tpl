{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Installation ID {$tooltips.installation_id}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[installation_id]" value="{$values.installation_id}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Currency Type
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
			Callback Password {$tooltips.callback_password}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[callback_password]" value="{$values.callback_password}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<table width=100% align=center>
		<tr>
			<td colspan=2 class=col_hdr_left>Worldpay Setup Instructions
			</td>
		</tr>
		<tr>
			<td colspan=2 class=medium_font>To use Worldpay as a form of payment please review the information on WorldPay's website. 
			Additional notes:<br> 
			</td>
		</tr>
		<tr>
			<td colspan=2 valign=top class=medium_font><b>Country codes for Worldpay:</b><Br>
				Make sure you use the correct country abbreviations for the countries you enter into your this application.
				Worldpay uses the 1994 version of ISO-3166 along with the two letter codes used in that specification.
				Get the countrys two letter codes that you use within your site from the following Worldpay country codes chart.
				<a href=http://support.worldpay.com/kb/integration_guides/junior/integration/help/appendicies/sjig_10300.html>Worldpay Country Codes</a><br>
				Enter the two letter code for any specific country as the abbreviation for that country within this application's Country Admin. <br><br>
	
				<b>The Worldpay process to complete a transaction:</b><br>
				The registrant enters their ad through the &quot;listing&quot; process.  Approves their listing, adds any extra features,
				and chooses Worldpay as their form of payment.  When they accept final approval of the listing costs they are taken to
				Worldpay with specific payment information for your site appearing in the form.  They complete the transaction through
				the Worldpay form.  Once the funds hit your account a &quot;callback&quot; message is sent to your site.
				This application verifies the transaction information internally and makes the listing is made &quot;active&quot; if the
				payment was approved through Worldpay.
			</td>
		</tr>
	</table>