{* 7.4beta1-44-gf13fba3 *}

{if !$view_only && $geo_mini_cart_displayed!==1}
	{*  Show the add new items buttons, but only if the my account links module
		has not already done so as part of the mini-cart, which it would 
		specify by defining GEO_MINI_CART_DISPLAYED *}
	<div id="left_column">
		{include file="display_cart/new_buttons_box.tpl"}
	</div>
{/if}


{if !$view_only}<div id="cart_content_column"><div class="content_box">{/if}
	{if !$view_only}
		<h1 class="title">{if $allFree}{$messages.500404}{else}{$messages.500249}{/if}</h1>
		
		
		{foreach from=$error_msgs item=err_msg}
			<div class="field_error_box">{$err_msg}</div>
		{/foreach}
	{/if}
	{if !$view_only}<form method="post" action="{$process_form_url}">{/if}
		{assign var='no_use_checkout' value=0}
		
		{foreach from=$items key=k item=item}
			{include file="display_cart/item.tpl" is_sub=0}
		{foreachelse}
			{assign var='no_use_checkout' value=1}
			<div class="note_box">
				<strong>{if $allFree}{$messages.500405}{else}{$messages.500248}{/if}</strong>
			</div>
		{/foreach}

		{if !$view_only && $no_use_checkout ne 1}
			<div class="checkout_button">
				<input type="hidden" name="checkout_clicked" value="0" id="checkout_clicked" />
				<button type="submit" class="button" onclick="jQuery('#checkout_clicked').val('click');">{if $allFree}{$messages.500406}{else}{$messages.500250}{/if}</button>
			</div>
		{/if}
	{if !$view_only}</form>{/if}

{if !$view_only}</div></div>{/if}