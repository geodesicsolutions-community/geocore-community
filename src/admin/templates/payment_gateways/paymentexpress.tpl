{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			PxPayUserId {$tooltips.user_id}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[user_id]" value="{$values.user_id}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			PxPayKey {$tooltips.access_key}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[access_key]" value="{$values.access_key}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Email Address to Receive Payment Receipt Notifications {$tooltips.receipt_email}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[receipt_email]" value="{$values.receipt_email}" />
		</div>
		<div class='clearColumn'></div>
	</div>	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Currency Codes {$tooltips.currency_codes}
			<p class="small_text" style="font-weight: normal;">Be sure to check the paymentexpress.com website for information on which banks support certain currency types. 
								  You will need to choose a bank (supported through paymentexpress) that will support your currency type.</p>
		</div>
		<div class='rightColumn'>
			<select name="{$payment_type}[currency_codes]">
				{$currencyOptions}
			</select>
		</div>
		<div class='clearColumn'></div>
	</div>		

<div class="col_hdr_left">PaymentExpress Testing Instructions</div>
<div>

			<p><strong>The Account Status switch above has no effect for transactions using PaymentExpress.</strong><br />
			Testing PaymentExpress transactions requires the creation of a Test Account, which is separate from your standard Merchant Account. Contact PaymentExpress to acquire a Test Account.
			</p>
</div>


<br />
	
</fieldset>