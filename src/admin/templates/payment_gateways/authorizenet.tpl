{* 7.3beta2-12-g081126e *}
{$commonAdminOptions}
{if $is_ent}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			If Recurring Billing subscription payment<br />signal cannot be linked to account:
		</div>
		<div class='rightColumn'>
			<label><input type="checkbox" name="{$payment_type}[arb_payment_nolink_email]" value="1"{if $arb_payment_nolink_email} checked="checked"{/if} /> Send Admin an E-mail</label><br />
			<label><input type="checkbox" name="{$payment_type}[arb_payment_nolink_cancel]" value="1"{if $arb_payment_nolink_cancel} checked="checked"{/if} /> Automatically Cancel with Authorize.net</label><br />	
		</div>
		<div class='clearColumn'></div>
	</div>
{/if}
{if $finalFees}
	<div class='row_color{cycle values="1,2"}' id="finalFees">
		<div class='leftColumn'>
			Auto-charge Auction Final Fees
		</div>
		<div class='rightColumn'>
			<input type="checkbox" id="{$payment_type}[charge_final_fees]" name="{$payment_type}[charge_final_fees]" value="1" {if $values.charge_final_fees}checked="checked"{/if}
				onclick="if (this.checked) $('{$payment_type}use_no_free_cart').show(); else $('{$payment_type}use_no_free_cart').hide();" />
		</div>
		<div class='clearColumn'></div>
		<div id='{$payment_type}use_no_free_cart'{if !$values.charge_final_fees} style='display: none;'{/if}>
			<div class='leftColumn'>
				Collect CC info on Free Orders
			</div>
			<div class='rightColumn'>
				<input type="checkbox" name="{$payment_type}[use_no_free_cart]" value="1" {if $values.use_no_free_cart}checked="checked"{/if} />
			</div>
			<div class='clearColumn'></div>
		</div>
	</div>
{/if}
<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Choose a Gateway {$tooltips.merchant_type}
	</div>
	<div class='rightColumn'>
		<select id="gateway_dropdown" name="{$payment_type}[merchant_type]" onclick="showAuthNetHelp(this.value);">
			<option value="1" {if $values.merchant_type == 1}selected="selected"{/if}>Authorize.net</option>
			<option value="5" {if $values.merchant_type == 5}selected="selected"{/if}>Paytrace (AIM only)</option>
			<option value="6" {if $values.merchant_type == 6}selected="selected"{/if}>eProcessingNetwork.com (AIM only)</option>
		</select>
		<script type="text/javascript">
		showAuthNetHelp = function(val) {
			if(val == 1) {
				//authorize.net main
				jQuery('#anet').show();
				jQuery('#paytrace').hide();
				jQuery('#eProc').hide();
				jQuery('#sim_radio').prop('disabled',false);
				jQuery('#finalFees').show();
			} else if(val == 5) {
				jQuery('#anet').hide();
				jQuery('#paytrace').show();
				jQuery('#eProc').hide();
				jQuery('#finalFees').hide();
			} else if(val == 6) {
				jQuery('#anet').hide();
				jQuery('#paytrace').hide();
				jQuery('#eProc').show();
				jQuery('#finalFees').show();
			}
			
			if(val == 5 || val == 6) {
				//AIM only for this gateway
				jQuery('#sim_radio').prop('disabled',true);
				jQuery('#aim_radio').prop('checked',true);
			}
		}
		</script>
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Connection Type {$tooltips.connection_type}<br />
		<span class="small_font">For SIM, consult the <a href="http://mhash.sourceforge.net">mhash</a> site and your internet host for more information.<br />
								 For AIM, consult the <a href="http://curl.haxx.se">cURL</a> site and your internet host for more information.</span>
	</div>
	<div class='rightColumn'>
		<input type="radio" name="{$payment_type}[connection_type]" {if $values.connection_type == "1"}checked="checked"{/if} {if $values.merchant_type == 5} disabled="disabled"{/if} id="sim_radio" value="1" /> SIM [Requires MHASH - {if $mhash}<span style="color:green;">INSTALLED</span>{else}<span style="color:red;">NOT INSTALLED</span>{/if}]<br />
		<input type="radio" name="{$payment_type}[connection_type]" {if $values.connection_type == "2" or $values.merchant_type == 5}checked="checked"{/if} id="aim_radio" value="2" /> AIM [Requires cURL - {if $curl}<span style="color:green;">INSTALLED</span>{else}<span style="color:red;">NOT INSTALLED</span>{/if}]
		
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Verify Peer Within Transaction {$tooltips.verify_peer}
	</div>
	<div class='rightColumn'>
		<input type="checkbox" name="{$payment_type}[verify_peer]" value="1" {if $values.verify_peer}checked="checked"{/if} />
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Merchant Login (API Key) {$tooltips.merchant_login}
	</div>
	<div class='rightColumn'>
		<input type="text" name="{$payment_type}[merchant_login]" value="{$values.merchant_login}" />
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Transaction Key {$tooltips.transaction_key}
	</div>
	<div class='rightColumn'>
		<input type="text" name="{$payment_type}[transaction_key]" value="{$values.transaction_key}" />
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Currency Codes {$tooltips.currency_code}
	</div>
	<div class='rightColumn'>
		<select name="{$payment_type}[currency_code]">
		{$currencyOptions}
		</select>
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Send Gateway Email to Customer {$tooltips.email_customer}
	</div>
	<div class='rightColumn'>
		<input type="radio" name="{$payment_type}[email_customer]" {if $values.email_customer == 1}checked="checked"{/if} value="1" /> yes<br />
		<input type="radio" name="{$payment_type}[email_customer]" {if $values.email_customer == 0}checked="checked"{/if} value="0" /> no
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Send Gateway Email to Admin {$tooltips.email_admin}
	</div>
	<div class='rightColumn'>
		<input type="radio" name="{$payment_type}[email_admin]" {if $values.email_admin == 1}checked="checked"{/if} value="1" /> yes<br />
		<input type="radio" name="{$payment_type}[email_admin]" {if $values.email_admin == 0}checked="checked"{/if} value="0" /> no
	</div>
	<div class='clearColumn'></div>
</div>

<div class='row_color{cycle values="1,2"}'>
	<div class='leftColumn'>
		Secret Key ("MD5 Hash" value) {$tooltips.secret}<br />
		<span class="small_font">(for Recurring Billing only)</span>
	</div>
	<div class='rightColumn'>
		<input type="text" name="{$payment_type}[secret]" value="{$values.secret}" />
	</div>
	<div class='clearColumn'></div>
</div>

<fieldset id="recurring_instructions"><legend>Automatic Recurring Billing Configuration</legend>
<p>You can set up site subscriptions as Authorize.net Automatic Recurring Billing (ARB) transactions. This will only work with Authorize.net's AIM method.</p>
<p>Again, <span style="color: #FF0000;"><strong>DO NOT USE</strong> recurring billing with the SIM method or with the Paytrace gateway!</span> Those are currently unsupported, and things WILL break.</p>
<p>To use ARB with <strong>Authorize.net AIM</strong> requires some additional configuration:</p>
<ol>
	<li>Check the "Recurring Billing Enabled" box above</li>
	<li>In the ARB section of your Authorize.net Merchant Interface, find the setting for Silent POST URL. Set it to: <strong>{$silentPOST}</strong></li>
	<li>Also find the setting for "MD5 Hash value" -- this can be set to anything you want, but it MUST be EXACTLY the same as the "Secret Key" you enter here. 
		This secret value is used to authenticate recurring payment signals and verify that they did, indeed, originate from Authorize.net</li>
</ol> 
</fieldset>

<fieldset id="authorizenet_transaction_test">
<legend>Transaction Test</legend>
{$transactionTest}
</fieldset>

<div id="anet"{if $values.merchant_type != 1} style="display:none;"{/if}>
<div id="authorizenet_sim_header" class="col_hdr_left2">Authorize.net Setup Instructions<br>Configurations for use with <strong>SIM</strong> connection method.</div>
<div id="authorizenet_sim_body">
			With the SIM integration you must first obtain a Transaction Key from your Authorize.net Merchant Interface following the steps below.<br><br>
			<ol>
			<li>Log into the Merchant Interface</li>
			<li>Select Settings from the Main Menu</li>
			<li>Click on the Obtain Transaction Key in the Security section</li>
			<li>Type in the answer to your secret question (The secret question and answer is setup
			during account activation. It is required to authenticate the merchant before the
			transaction key is generated.)</li>
			<li>Click Submit</li>
			<li>Insert the transaction key obtained into the transaction key space above.<br /><br />
			
			By completing the next set of instructions you are allowing GeoClassifieds to talk to your Authorize.net account asking for a transaction approval against a user's account.<br /></li>
			
			<li>Log into the Merchant Interface (if you haven't already)</li>
			<li>Select Settings from the Main Menu</li>
			<li>Click on the Response /Receipt URL link in the Transaction Response section</li>
			<li>Click on the Add URL link</li>
			<li>Add the url on the next line<br />
			<strong>{$responseURL}</strong></li>
			<li>Click Submit.<br />
			<br />
			The following setting allows Authorize.net to communicate results of the transaction back to GeoClassifieds with information approving or disproving the users transaction (thus turning it "on" automatically.<br /></li>
			
			<li>Insert your merchant login id in the field above.</li>
			<li>Log into the Merchant Interface</li>
			<li>Select Settings from the Main Menu</li>
			<li>Click on Relay Response in the Transaction Response section</li>
			<li>Enter the URL below so that Authorize.net will respond to GeoClassifieds when the transaction is approved.<br />
			<strong>{$responseURL}</strong></li>
			<li>You can test the process by placing your account in test mode. 
			In the "General" section after clicking the "Settings" menu link you will find a "Test Mode" link, click it. 
			Click the submit button until your account is in test mode. 
			You can then run test transactions to make sure everything works on your site. Use the test credit card numbers below if you need:<br />
			mastercard 5424000000000015<br />
			visa 4007000000027<br />
			discover 6011000000000012<br />
			amex 370000000000002<br />
			These card numbers will return declined if used in live mode.</li>
			<li><strong>When you are satisfied that everything is working properly make sure to turn the test mode off in your Authorize.net account</strong></li>
			</ol>
</div>

<div id="authorizenet_aim_header" class="col_hdr_left2">Authorize.net Setup Instructions<br>Configurations for use with <strong>AIM</strong> connection method.</div>
<div id="authorizenet_aim_body">
			<ol>
			<li>If you had used another connection method before using
			the AIM method remove all of those settings from your Merchant Interface.  The AIM Method only needs one settings for it to work
			properly.  Remove all Relay Response, Response/Receipt URLS, Weblink (if visible) and Receipt Page settings.  These will cause conflicts.</li>

			<li>Click Submit</li>

			<li>Make sure you enter your merchant account password in the proper field above.</li>

			<li>Instructions for obtaining a transaction key from Authorize.net follow:<br />
				To obtain the transaction key from the Merchant Interface<br />
				<ol>
				<li>Go to wwww.authorize.net and log into the Merchant Interface</li>
				<li>Select Settings from the Main Menu</li>
				<li>Click on Obtain Transaction Key in the Security section</li>
				<li>Type in the answer to the secret question configured on setup</li>
				<li>Click Submit.</li>
				</ol>
			<br />
			It is strongly recommended that the merchant periodically change the transaction key. The merchant will have to disable the old key and generate a new key. 
			The old key will be valid for 24 hours before it expires. To disable the old key on the Merchant Interface:</li>
			<ol>
			<li>Log into the Merchant Interface</li>
			<li>Select Settings from the Main Menu</li>
			<li>Click on Obtain Transaction Key in the Security section</li>
			<li>Type in the answer to the secret question configured on setup</li>
			<li>Check the box that says Disable Old Key</li>
			<li>Click Submit<br /></li>
			</ol>

			<li>You can test the Authorize.net process by placing your account in test mode.  In the "General" section after clicking the "Settings"
			menu link you will find a "Test Mode" link, click it.   Click the submit button until your account is in test mode.  You can
			then run test transactions to make sure everything works on your site.  Use the test credit card numbers below if you need:<br><br>
			<strong>mastercard:</strong> 5424000000000015<br>
			<strong>visa:</strong> 4007000000027<br>
			<strong>discover:</strong> 6011000000000012<br>
			<strong>amex:</strong> 370000000000002<br><br>
			NOTE: These card numbers will return declined if used in "live" mode.</li>
			</ol>
</div>
</div>

<div id="paytrace" {if $values.merchant_type != 5}style="display:none;"{/if}>
	<div id="paytrace_header" class="col_hdr_left">Paytrace (AIM Method only) Setup Instructions<br></div>
	<div id="paytrace_body">
				<ol>
				<li>If you had used another connection method before using
				the AIM method remove all of those settings from your Merchant Interface.  The AIM Method only needs one settings for it to work
				properly.  Remove all Relay Response, Response/Receipt URLS, Weblink (if visible) and Receipt Page settings.  These will cause conflicts.<br>
				<li>If you are using the AIM method of connection click on the direct response and make sure "Delimited Response" is set to "Yes"
				and the "Default Field Separator" is set to "| (pipe)".  Leave the "Field Encapsulation Character" field empty.</li>
				<li>Click Submit</li>
				<li>Make sure you enter your merchant account password in the proper field above.</li>
				<li><strong>THE TESTING MODE SWITCH ABOVE HAS NO EFFECT IF USING PAYTRACE.</strong> To put PayTrace into testing mode, you must enter the following login/password/transaction key above and use the given card number in the transaction:<br />
				<strong>username:</strong> demo123<br />
				<strong>password:</strong> demo123<br />
				<strong>transaction key:</strong> demo123<br />
				<strong>card number:</strong> 5454545454545454</li>
				</ol>
	</div>
</div>

<div id="eProc" {if $values.merchant_type != 6}style="display:none;"{/if}>
	<div id="eProc_header" class="col_hdr_left">eProcessing Network (AIM Method only) Setup Instructions<br></div>
	<div id="eProc_body">
				<ol>
				<li>The eProcessingNetwork.com emulates Authorize.net's AIM method.</li>
				<li>In the settings above, enter your eProcessing Network <strong>account number</strong> as the "Merchant Login" and your
					<strong>RestrictKey</strong> as the "Transaction Key"</li>
				<li>Note that eProcessing Network does not use the "Testing Mode" switch</li>
				</ol>
	</div>
</div>
