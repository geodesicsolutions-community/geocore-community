{* 7.2beta1-131-ga967274 *}

{if $show_dropdown eq 1}
	<div class="listing_extra_item">
		<div class="listing_extra_cost">
			{$display_amount}
		</div>
		{$messages.794}
		{$help_link_795}
		<div class='clear'></div>
	</div>

	<div class="listing_extra_item_child">
		{$messages.1399}
		<select class="field" name="c[renewal_length]" id='renewal_length' onchange="updatePrice(this.value)">
			{foreach from=$renew_dropdown key=k item=i}
				<option value='{$i.length_of_ad}'{if $i.selected eq 1} selected="selected"{/if}>
					{$i.display_length_of_ad} {$i.display_amount}
				</option>
			{/foreach}
		</select>
		<div class='clear'></div>
	</div>
	{if $price_applies=='item'}
		<div class="listing_extra_item_child">
			{* need to select starting quantity *}
			{$messages.502138}
			<input type="text" name="c[renewal_quantity]" value="{$quantity_remaining}" class="field" size="7" />
		</div>
	{/if}
	
	<input type='hidden' name='c[ad_renewal]' value='1' />
	
	<script type='text/javascript'>
		//<![CDATA[
		var durations = new Array();
		{foreach from=$duration_array key=key item=val} 
			durations['{$key}'] = '{$val}';
		{/foreach}
		{literal}
			function updatePrice(newPrice){
				//alert('price: '+durations[newPrice]);
				if (document.getElementById('listing_renew_price')){
					var priceField = document.getElementById('listing_renew_price');
					priceField.innerHTML = durations[newPrice];
					updatePriceStyle(priceField);
				}
			}
			function updatePriceStyle(obj){
				if (document.getElementById('use_credit_for_renewal')){
					//alert('checked: '+document.getElementById('use_credit_for_renewal').checked);
					var inner = obj.innerHTML;
					obj.innerHTML = inner;
					
					if (document.getElementById('use_credit_for_renewal').checked){
						obj.className='subtotal_data_value disabled';
					} else {
						obj.className='subtotal_data_value';
					}
					obj.innerHTML = inner;
					//alert('class: '+obj.className);
				}
			}
		{/literal}
		//]]>
	</script>
	
{else}
	<div class="listing_extra_item">
		{$text_799_or_830}
	</div>
{/if}