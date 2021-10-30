{* 7.2beta1-63-gb90fc35 *}{strip}
{if $quantity==1 || $price_applies=='item'}
	{$messages.502102}
{elseif $messages.502103}
	{$messages.502103} {$quantity}
{/if}
{/strip}