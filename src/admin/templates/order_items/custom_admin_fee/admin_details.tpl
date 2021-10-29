{* 7.0.2-319-g05e6409 *}

{include file="cart_steps.tpl" g_type="system" g_resource="cart"}

<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Item Label</div>
	<div class="rightColumn">{$label}</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Cost</div>
	<div class="rightColumn">{$cost|displayPrice}</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">
		Notify E-mail(s) when Activated
		<br />
		<span class="small_font">(When payment is received)</span>
	</div>
	<div class="rightColumn">{$notify}</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Item Removable by Client?</div>
	<div class="rightColumn">{if $removable}Yes{else}No{/if}</div>
	<div class="clearColumn"></div>
</div>
<div class="{cycle values='row_color1,row_color2'}">
	<div class="leftColumn">Fee Created by Admin</div>
	<div class="rightColumn">{$admin}</div>
	<div class="clearColumn"></div>
</div>