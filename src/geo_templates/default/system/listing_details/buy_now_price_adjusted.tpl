{* 7.4beta1-261-g47f14d1 *}
{strip}
{$listing.precurrency|fromDB}
<span data-base-cost="{$listing.buy_now}" id="listing-buy-now-price-{$listing.id}">
	{$listing.buy_now|displayPrice:'':''}
</span>
{if !$hide_postcurrency} {$listing.postcurrency|fromDB}{/if}
{/strip}