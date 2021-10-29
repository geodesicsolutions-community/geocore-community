{* 7.5.3-36-gea36ae7 *}
	{$commonAdminOptions}
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Paypal API Username: {$tooltips.api_username}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[api_username]" value="{$values.api_username}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Paypal API Password: {$tooltips.api_password}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[api_password]" value="{$values.api_password}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Paypal API Signature:
		</div>
		<div class='rightColumn'>
			<input type="text" name="{$payment_type}[signature]" value="{$values.signature}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			-OR- Path to certificate: {$tooltips.certfile}
		</div>
		<div class='rightColumn'>
			<input type='text' name="{$payment_type}[certfile]" value="{$values.certfile}" />
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			<em>Recommended Path:</em> {$tooltips.recommended}
		</div>
		<div class='rightColumn'>
			{$values.recommended}
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Paypal Currency Codes: {$tooltips.currency_id}
		</div>
		<div class='rightColumn'>
			<select name="{$payment_type}[currency_id]">
				<option value="AUD"{if $values.currency_id == "AUD"} selected="selected"{/if}>AUD - Australian Dollar</option>
				<option value="CAD"{if $values.currency_id == "CAD"} selected="selected"{/if}>CAD - Canadian Dollar</option>
				<option value="EUR"{if $values.currency_id == "EUR"} selected="selected"{/if}>EUR - Euro</option>
				<option value="GBP"{if $values.currency_id == "GBP"} selected="selected"{/if}>GBP - Pound Sterling</option>
				<option value="JPY"{if $values.currency_id == "JPY"} selected="selected"{/if}>JPY - Japanese Yen</option>
				<option value="USD"{if $values.currency_id == "USD"} selected="selected"{/if}>USD - U.S. Dollar</option>
			</select>
			
		</div>
		<div class='clearColumn'></div>
	</div>
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Character Set: {$tooltips.charset}
		</div>
		<div class='rightColumn'>
			<select name="{$payment_type}[charset]">
				<option value="us-ascii"{if $values.charset == "us-ascii"} selected="selected"{/if}>US-ASCII</option>
				<option value="utf-8"{if $values.charset == "utf-8"} selected="selected"{/if}>UTF-8</option>
				<option value="iso-8859-1"{if $values.charset == "iso-8859-1"} selected="selected"{/if}>ISO-8859-1</option>
			</select>
		</div>
		<div class='clearColumn'></div>
	</div>
	
	<div class='row_color{cycle values="1,2"}'>
		<div class='leftColumn'>
			Max Failed Recurring Payments: {$tooltips.max_failed_payments}
		</div>
		<div class='rightColumn'>
			<input name="{$payment_type}[max_failed_payments]" value="{$values.max_failed_payments}" size="1" />
		</div>
		<div class='clearColumn'></div>
	</div>

	<div class="col_hdr_left" style="width: 100%;">User Registration Requirements <a href=index.php?mc=registration_setup&page=register_general_settings>Click here to edit user registration requirements.</a></div>
	<div style="width: 100%;">
		Information required by PayPal Pro: {$tooltips.required_fields}
		<ul>
			<li>First Name</li>
			<li>Last Name</li>
			<li>Street Address</li>
			<li>City</li>
			<li>State/Province</li>
			<li>Country</li>
			<li>Zip</li>
		</ul>
	</div>

	<div class="col_hdr_left" style="width: 100%;">User Registration Requirements <a href=index.php?mc=registration_setup&page=register_general_settings>Click here to edit user registration requirements.</a></div>
	<div style="width: 100%;">
		<p>Besides pulling your user's address information, PayPal Pro will also be pulling the user's country code which is based on 
		the country codes (spefically their abbreviations) to be sent.  This must be a two letter abbreviation and only for those 
		countries listed below.  Please arrange your geographic abbreviations to accommodate the following:</p>
		<div style='float:left'>
			<ul>
				<li>Anguilla AI</li>
	                <li>Argentina AR</li>
	                <li>Australia AU</li>
	                <li>Austria AT</li>
	                <li>Belgium BE</li>
	                <li>Brazil BR</li>
	                <li>Canada CA</li>
	                <li>Chile CL</li>
	                <li>China CN</li>
	                <li>Costa Rica CR</li>
	                <li>Denmark DK</li>
	                <li>Dominican Republic DO</li>
	        </ul>
		</div>  
		<div style='float:left'>
			<ul>
				<li>Ecuador EC</li>
				<li>Finland FI</li>
				<li>France FR</li>
				<li>Germany DE</li>
				<li>Greece GR</li>
				<li>Hong Kong HK</li>
				<li>Iceland IS</li>
				<li>India IN</li>
				<li>Ireland IE</li>
				<li>Israel IL</li>
				<li>Italy IT</li>
				<li>Jamaica JM</li>
			</ul> 
		</div>
		<div style='float:left'>  
			<ul>
				<li>Japan JP</li>
				<li>Luxembourg LU</li>
				<li>Malaysia MY</li>
				<li>Mexico MX</li>
				<li>Monaco MC</li>
				<li>Netherlands NL</li>
				<li>Switzerland CH</li>
				<li>Thailand TH</li>
				<li>Taiwan TW</li>
				<li>New Zealand NZ</li>
				<li>Norway NO</li>
				<li>Portugal PT</li>
			</ul>
		</div>  
		<div style='float:left'>
			<ul>  
				<li>Singapore SG</li>
				<li>South Korea KR</li>
				<li>Spain ES</li>
				<li>Sweden SE</li>
				<li>Turkey TR</li>
				<li>United Kingdom GB</li>
				<li>United States US</li>
				<li>Uruguay UY</li>
				<li>Venezuela VE</li>
			</ul>
		</div>
	</div>

	<div class="col_hdr_left" style="clear: left; width: 100%;">Server Setup Procedures</div>
	<div style="width: 100%;">
		{$requirements}
	</div>
	
	
	