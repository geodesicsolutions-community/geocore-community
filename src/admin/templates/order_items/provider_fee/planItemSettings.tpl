{* 16.05.0-14-g09aceb3 *}
<div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn"><input type="checkbox" name="provider_fee[enabled]" value="1" {if $enabled}checked="checked"{/if} /></div>
		<div class="rightColumn">Enable Item</div>
		<div class="clearColumn"></div>
	</div>
	<div class="{cycle values='row_color1,row_color2'}">
		<div class="leftColumn">Percentage to add as a fee to all transactions</div>
		<div class="rightColumn"><input type="text" name="provider_fee[percentage]" value="{$percentage}" /></div>
		<div class="clearColumn"></div>
	</div>
</div>