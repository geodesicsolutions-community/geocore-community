{* 6.0.7-3-gce41f93 *}
<span class="inline">
	{if $payment_choice.radio_tag}
		{$payment_choice.radio_tag}
	{else}
		<input id="{$payment_choice.label_name}" type='radio' name="{if $payment_choice.radio_name}{$payment_choice.radio_name}{else}c[payment_type]{/if}" {if $payment_choice.checked || $force_checked || $force_use_gateway}checked="checked" {/if}value="{$payment_choice.radio_value}" />
	{/if}
</span>