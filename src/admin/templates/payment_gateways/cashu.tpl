{* 7.5.3-36-gea36ae7 *}


<fieldset>
	<legend>Gateway Settings</legend>
	<div>
		{$commonAdminOptions}
		
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				Merchant ID
			</div>
			<div class="rightColumn">
				<input type="text" name="{$payment_type}[merchantId]" value="{$merchantId|escape}" />
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				Encryption Keyword
			</div>
			<div class="rightColumn">
				<input type="text" name="{$payment_type}[encryptionKey]" value="{$encryptionKey|escape}" />
			</div>
			<div class="clearColumn"></div>
		</div>
		
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				Service/Product Name<br />
				(optional)
			</div>
			<div class="rightColumn">
				<input type="text" name="{$payment_type}[service_name]" value="{$service_name|escape}" />
			</div>
			<div class="clearColumn"></div>
		</div>
		
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				Currency Type for Gateway
			</div>
			<div class="rightColumn">
				<select name="{$payment_type}[currency_type]">
					{foreach $currencies as $c_id => $c_item}
						<option value="{$c_id}"{if $currency_type==$c_id} selected="selected"{/if}>{$c_item}</option>
					{/foreach}
				</select>
			</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				Currency Multiplier
			</div>
			<div class="rightColumn">
				<input type="text" name="{$payment_type}[currency_rate]" value="{$currency_rate}" />
			</div>
			<div class="clearColumn"></div>
		</div>
		
		<div class="col_hdr_top">Site Language to Gateway Language</div>
		{foreach $languages as $language}
			<div class="{cycle values='row_color1,row_color2'}">
				<div class="leftColumn">
					{$language.language}
				</div>
				<div class="rightColumn">
					<select name="{$payment_type}[gatewayLanguages][{$language.language_id}]">
						<option value="en"{if $gatewayLanguages[$language.language_id]=='en'} selected="selected"{/if}>English (en)</option>
						<option value="ar"{if $gatewayLanguages[$language.language_id]=='ar'} selected="selected"{/if}>Arabic (ar)</option>
						<option value="ir"{if $gatewayLanguages[$language.language_id]=='ir'} selected="selected"{/if}>Farsi (ir)</option>
					</select>
				</div>
				<div class="clearColumn"></div>
			</div>
		{/foreach}
	</div>
</fieldset>

<fieldset>
	<legend>Requirements</legend>
	<div>
		<p class="page_note">
			This payment gateway requires a few extensions to be loaded in PHP to work properly.  
			If these extensions are not loaded (as indicated below), this gateway will not function.
		</p>
		
		{if !$checks.openssl||!$checks.soap}
			<p class="page_note_error">
				<strong>Error:</strong>  As you can see below, some of the requirements are not met.
				You must <strong>contact your host</strong> to install any missing requirements
				on your server, this payment gateway will not function until all tests below pass.
			</p>
		{/if}
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				{if $checks.openssl}
					<span style="color: green;">Installed</span>
				{else}
					<span style="color: red;">NOT Installed</span>
				{/if}
			</div>
			<div class="rightColumn">OpenSSL PHP Extension</div>
			<div class="clearColumn"></div>
		</div>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">
				{if $checks.soap}
					<span style="color: green;">Installed</span>
				{else}
					<span style="color: red;">NOT Installed</span>
				{/if}
			</div>
			<div class="rightColumn">SOAP PHP Extension</div>
			<div class="clearColumn"></div>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>Gateway Instructions</legend>
	<div>
		<ol style="font-weight: normal;">
			<li>Log into your merchant account at <a href="http://cashu.com">cashu.com</a>.</li>
			<li>Go to <strong>My Account &gt; Payment Security</strong> and choose either "Single Checkout" or "Multiple Checkout" depending on your needs.  (Most sites will use "Single Checkout", see cashU documentation on the differences between those two.)</li>
			<li>If "Encryption Keyword" field is blank, fill it out now.</li>
			<li>For the <em>Notification URL</em> enter <strong>{$transactionUrl}</strong>
				{if $sslWarn}<br /><span style="color: red;"><strong>Warning:</strong> Notification URL must use secure HTTPS connection.  If your site is not
				set up to work using secure HTTPS connection, the notifications will 
				not be sent and you will need to manually approve each order within 
				the software once you receive payment in cashU.</span>{/if}
			</li>
			<li>For the <em>Return URL</em>, enter <strong>{$transactionUrl}&amp;success_page=success</li>
			<li>You can leave the <em>Sorry URL</em> blank.</li>
			<li>Save the settings on cashu.com, then make sure to fill out and save the settings above.  Note that the <em>Merchange ID</em>, <em>Encryption Keyword</em>, and <em>Service/Product Name</em>
				are the same values inside the cashu.com website control panel settings.</li>
		</ol>
	</div>
</fieldset>
