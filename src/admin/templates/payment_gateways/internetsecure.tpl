{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Internet Secure Merchant Account Number
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[account_num]" value="{$values.account_num}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Canadian Tax Method {$tooltips.tax_method}
		</div>
		<div class='rightColumn'>
			<input type='radio' name="{$payment_type}[tax_method]" value="0" {if $values.tax_method == 0}checked="checked"{/if} /> None<br />
			<input type='radio' name="{$payment_type}[tax_method]" value="PST" {if $values.tax_method == "PST"}checked="checked"{/if} /> Provincial Sales Tax {$tooltips.pst}<br />
			<input type='radio' name="{$payment_type}[tax_method]" value="GST" {if $values.tax_method == "GST"}checked="checked"{/if} /> Goods and Service Tax<br />
			<input type='radio' name="{$payment_type}[tax_method]" value="HST" {if $values.tax_method == "HST"}checked="checked"{/if} /> Harmonized Sales Tax<br />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Language to Display {$tooltips.language}
		</div>
		<div class='rightColumn'>
			<input type='radio' name="{$payment_type}[language]" value="English" {if $values.language == "English"}checked="checked"{/if} /> English<br />
			<input type='radio' name="{$payment_type}[language]" value="French" {if $values.language == "French"}checked="checked"{/if} /> French<br />
			<input type='radio' name="{$payment_type}[language]" value="Spanish" {if $values.language == "Spanish"}checked="checked"{/if} /> Spanish<br />
			<input type='radio' name="{$payment_type}[language]" value="Portuguese" {if $values.language == "Portuguese"}checked="checked"{/if} /> Portuguese<br />
			<input type='radio' name="{$payment_type}[language]" value="Japanese" {if $values.language == "Japanese"}checked="checked"{/if} /> Japanese<br />
			
		</div>
		<div class='clearColumn'></div>
	</div>	

<div class="col_hdr_left">Internet Secure Setup Instructions</div>
<div>
			<p>You will be using the Internet Secure Export Script to return variables to your site and turn on the classified ad once the transaction has been approved.</p>
			<ol>
				<li>Log into the Merchant Reporting Area of your Internet Secure Account Management Tool. Within the top menu bar select "Export Scripts" and then "Export Script Options."
					Please note that your account must have the "Export Scripts" feature to work seamlessly with Geo software</li>
					
				<li>Within the field called "Server Domain Name," you must enter the following base URL of your site:<br /><strong>{$baseURL}</strong></li>
				
				<li>Within the "Web Page" field, you must enter the following path to the page on your server that will be processing the transaction:<br /><strong>{$processURL}</strong></li>
				
				<li>On the same page, place a check next to "Send Approvals Only"</li>
			</ol>
</div>
