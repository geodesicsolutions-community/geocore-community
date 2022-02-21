{* 6.0.7-3-gce41f93 *}

<div class="payment_item">
	{if $payment_choice.help_box}
		{$payment_choice.help_box}
	{else}
		{include file="system/cart/payment_choices/help_box.tpl"}
	{/if}
	{if $payment_choice.radio_box}
		{$payment_choice.radio_box}
	{else}
		{include file="system/cart/payment_choices/radio_box.tpl"}
	{/if}
	{if $payment_choice.title_box}
		{$payment_choice.title_box}
	{else}
		{include file="system/cart/payment_choices/title_box.tpl"}
	{/if}
	{if $payment_choice.user_agreement.box}
		{$payment_choice.user_agreement.box}
	{elseif $payment_choice.user_agreement}
		{include file="system/cart/payment_choices/gateway_agreement.tpl"}
	{/if}
</div>
