{* 7.2beta1-40-g74ee254 *}
<div class="{cycle values="row_color1,row_color2"}">
	<div class="leftColumn">Enabled</div>
	<div class="rightColumn">
		<input type="checkbox" name="charitable_badge[enabled]"	id="charitable_badge_enabled" value="1"	{if $enabled}checked="checked"{/if}	onclick="jQuery('#charitable_badge_pricing').toggle();" />
	</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values="row_color1,row_color2"}" id="charitable_badge_pricing" {if !$enabled}style="display: none;"{/if}>
	<div class="leftColumn">Charitable Badge Price</div>
	<div class="rightColumn">
		{$pre}<input type="text" name="charitable_badge[price]" value="{$price}" />{$post}
	</div>
	<div class="clearColumn"></div>
</div>