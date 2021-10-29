{* 7.6.3-132-ga11b5a1 *}
{add_footer_html}
<script type="text/javascript">
//<![CDATA[
	//keep it simple, this little bit of JS changes the pre-currency displayed
	//whenever the currency selected is changed.
	jQuery(document).ready(function () {
		gjUtil.updateCurrencies = function () {
			var pre = '';
			var post = '';
			var currency_id = jQuery('#currency_type').val();
			{foreach $currencies as $currency}
				if (currency_id == {$currency.type_id}) {
					pre='{$currency.precurrency}';
					post='{$currency.postcurrency}';
				}
			{/foreach}
			jQuery('span.precurrency').html(pre);
			jQuery('span.postcurrency').html(post);
		};
		jQuery('#currency_type').change(gjUtil.updateCurrencies);
		//make sure if they hit refresh or something, drop down matches precurrencies.
		gjUtil.updateCurrencies();
	});

//]]>
</script>
{/add_footer_html}
{if $currencies_count>1}
	<select name="b[currency_type]" id="currency_type" class="place_an_ad_details_data field">
		{foreach from=$currencies item=currency}
			<option value="{$currency.type_id}"{if $currency.type_id==$currency_type} selected="selected"{/if}>{$currency.postcurrency}</option>
		{/foreach}
	</select>
{elseif $currencies_count==1}
	<input name="b[currency_type]" id="currency_type" type="hidden" value="{$currencies.0.type_id}" />
{/if}