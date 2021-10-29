{* 7.5.3-36-gea36ae7 *}

{$commonAdminOptions}

<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		Hosting Company {$tooltip.godaddy}
	</div>
	<div class="rightColumn">
		<label><input type="radio" name="{$payment_type}[godaddy]" value="1" {if $godaddy}checked="checked"{/if} /> Godaddy Hosting or Subsidiary of Godaddy</label>
		<br />
		<label><input type="radio" name="{$payment_type}[godaddy]" value="0" {if !$godaddy}checked="checked"{/if} /> All Others (default)</label>
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		PayPal E-mail Account(s) {$tooltip.paypal_id}
	</div>
	<div class="rightColumn">
		<label>Primary E-Mail: <input type="text" name="{$payment_type}[paypal_id]" value="{$paypal_id|escape}" /></label>
		<br /><br />
		<em><strong>Advanced Options:</strong></em>
		<br />
		<label><input type="checkbox" name="{$payment_type}[use_micro]" value="1" {if $use_micro}checked="checked"{/if}/> Use Micro-Payment account</label>
		<br />
		<label>For transactions <em>less than</em>: {$precurrency}<input type="text" name="{$payment_type}[micro_limit]" value="{$micro_limit|displayPrice:'':''}" size="4" /> {$postcurrency}</label>
		<br />
		<label>Micro-Payment E-Mail: <input type="text" name="{$payment_type}[micro_id]" value="{$micro_id|escape}" /></label>
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		URL of Company Logo {$tooltip.paypal_image_url}
	</div>
	<div class="rightColumn">
		<input type="text" name="{$payment_type}[paypal_image_url]" value="{$paypal_image_url|escape}">
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		Title of Item Sent to PayPal {$tooltip.paypal_item_label}
	</div>
	<div class="rightColumn">
		<input type="text" name="{$payment_type}[paypal_item_label]" value="{$paypal_item_label|escape}" />
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		Currency Type you Accept at PayPal {$tooltip.currency_type}
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
		Currency Multiplier  {$tooltip.currency_rate}
	</div>
	<div class="rightColumn">
		<input type="text" name="{$payment_type}[currency_rate]" value="{$currency_rate}" />
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		Language Code  {$tooltip.language_code}
	</div>
	<div class="rightColumn">
		<input type="text" name="{$payment_type}[language_code]" value="{$language_code|escape}" />
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		"No Shipping" variable  {$tooltip.no_shipping}
	</div>
	<div class="rightColumn">
		<select name="{$payment_type}[no_shipping]">
			<option value="">-</option>
			<option value="0" {if $no_shipping!=''&&$no_shipping==0} selected="selected"{/if}>0</option>
			<option value="1" {if $no_shipping=='1'} selected="selected"{/if}>1</option>
			<option value="2" {if $no_shipping=='2'} selected="selected"{/if}>2</option>
		</select>
	</div>
	<div class="clearColumn"></div>
</div>