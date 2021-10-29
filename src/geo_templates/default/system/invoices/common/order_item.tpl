{* 6.0.7-3-gce41f93 *}

<div class="{if $item.css_class}{$item.css_class}{else}cart_item{if $is_sub eq 1}_child{/if}{/if}">
	<div class="cart_item_cost">
		{$item.priceDisplay}
	</div>
	<div class="cart_item_label">
		{$item.title}
	</div>
	<div class="clr"></div>
</div>

{if $item.children ne false}
	{foreach from=$item.children key=sk item=sub_item}
		{include file="common/order_item.tpl" k=$sk item=$sub_item is_sub=1}
	{/foreach}

	{if !$is_sub}
		<div class="cart_item_subtotal">
			{$messages.500263} {$item.total|displayPrice}
		</div>
	{/if}
{/if}
