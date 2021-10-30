{* 7.3beta5-51-g3b2f722 *}

{* Just a check to make sure settings are submitted *}
<input type="hidden" name="auction[form_submitted]" value="1" />
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn"><input type="checkbox" name="auction[allow_buy_now]" value="1"{if $allow_buy_now} checked="checked"{/if}
		onclick="if (jQuery(this).prop('checked')) { jQuery('#buy_now_box').show('fast'); } else { jQuery('#buy_now_box').hide('fast'); }" /></div>
	<div class="rightColumn">Enable "Buy Now" Auctions</div>
	<div class="clearColumn"></div>
</div>
<div id="buy_now_box"{if !$allow_buy_now} style="display: none;"{/if}>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="auction[allow_buy_now_only]" value="1"{if $allow_buy_now_only} checked="checked"{/if}
			onclick="if (jQuery(this).prop('checked')) { jQuery('#buy_now_only_box').show('fast'); } else { jQuery('#buy_now_only_box').hide('fast'); }" /></div>
		<div class="rightColumn">Enable "Buy Now ONLY"</div>
		<div class="clearColumn"></div>
	</div>
	<div id="buy_now_only_box"{if !$allow_buy_now_only} style="display: none;"{/if}>
		<div class="{cycle values='row_color1,row_color2'}">
			<div class="leftColumn">When All "Buy Now Only" Items Sold:</div>
			<div class="rightColumn">
				<label><input type="radio" name="auction[buy_now_only_none_left]" value="close"{if $buy_now_only_none_left=='close'} checked="checked"{/if} /> Close Auction Early (Normal buy-now behavior)</label><br />
				<label><input type="radio" name="auction[buy_now_only_none_left]" value="sold"{if $buy_now_only_none_left=='sold'} checked="checked"{/if} /> Leave Auction Open - Show "Sold" Sign</label>
			</div>
			<div class="clearColumn"></div>
		</div>
	</div>
</div>

<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn"><input type="checkbox" name="auction[allow_reverse]" value="1"{if $allow_reverse} checked="checked"{/if}
		onclick="$('reverse_box')[(this.checked)? 'show':'hide']();" /></div>
	<div class="rightColumn">Enable Reverse Auctions</div>
	<div class="clearColumn"></div>
</div>
<div id="reverse_box"{if !$allow_reverse} style="display: none;"{/if}>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="auction[allow_reverse_buy_now]" value="1"{if $allow_reverse_buy_now} checked="checked"{/if} /></div>
		<div class="rightColumn">Enable "Buy Now" for Reverse Auctions</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="auction[charge_reverse_final_fees]" value="1"{if $charge_reverse_final_fees} checked="checked"{/if} /></div>
		<div class="rightColumn">Charge Final Fees for Reverse Auctions</div>
		<div class="clearColumn"></div>
	</div>
</div>

<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn"><input type="checkbox" name="auction[force_single_quantity]" value="1"{if $force_single_quantity} checked="checked"{/if} /></div>
	<div class="rightColumn">Allow Only Single-Quantity Auctions</div>
	<div class="clearColumn"></div>
</div>
