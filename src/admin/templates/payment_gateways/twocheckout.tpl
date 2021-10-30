{* 7.5.3-36-gea36ae7 *}
{$commonAdminOptions}
	
<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		2Checkout Account Number
	</div>
	<div class='rightColumn'>
		<input type='text' name="{$payment_type}[sid]'" value="{$values.sid}" />
	</div>
	<div class='clearColumn'></div>
</div>
<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		2Checkout Secret Word
		<br />
		<span class="small_font">
			{capture assign='secretLink'}<a href="http://www.2checkout.com/blog/knowledge-base/suppliers/tech-support/3rd-party-carts/md5-hash-checking/where-do-i-set-up-the-secret-word" onclick="window.open(this.href); return false;">Info Here</a>{/capture}
			{$secretLink}
		</span>
	</div>
	<div class='rightColumn'>
		<input type='text' name="{$payment_type}[secret]'" value="{$values.secret}" />
	</div>
	<div class='clearColumn'></div>
</div>
<div class="col_hdr">Setup Instructions</div>
<p>Follow the instructions below to ensure that 2 Checkout is configured correctly
to process payments.</p>
<ol>
	<li>Log into 2Checkout Vendor area at <a href="https://www.2checkout.com/va/" onclick="window.open(this.href); return false;">https://www.2checkout.com/va/</a>.</li>
	<li>In the tabs at the top, navigate to <em>Account &gt; Site Management</em>.</li>
	<li>In the section <em>Direct Return</em>, where is says something
	similar to "After completing an order, buyers should be:", change the selected
	option to "<em>Immediately returned to my website</em>" if it is not
	already.</li>
	<li>In the same section, enter a unique <em>Secret Word</em> ({$secretLink}).
	This setting is used to verify that "payment signals" originate from 2Checkout;
	without it payments will not automatically go through within the Geo software.</li>
	<li>Click <em>Save Changes</em> at the bottom of the page.</li>
	<li>Still within the 2Checkout Vendor area, in the tabs at the top, navigate to <em>Notifications &gt; Settings</em>.</li>
	<li>In the section <em>Order Created</em>, check the box for <em>Enable</em> if it is not already checked.  For the URL, enter:
		<p class="page_note">{$responseURL}</p>
	</li>
	<li>Click <em>Save Settings</em> at the bottom of the page.</li>
	<li>Within the 2checkout settings in the Geo admin panel (the settings above these instructions), make sure
	that the <em>2Checkout Account Number</em> and <em>2Checkout Secret Word</em> fields are both filled in
	correctly (along with the other settings), then click <em>Save</em> button in the top right corner of the 2checkout settings.
	<br /><br />
	Failure to fill in the account number correctly will result in payments not able to be made.  Failure to fill
	in the secret word will result in none of the payments automatically being approved within the Geo software, as
	there will be no way to verify that a payment signal originated from 2checkout.
</ol>