{* 7.5.3-36-gea36ae7 *}
{if $salutation}{$salutation},<br />{/if}
{$messageBody}<br />
<br />
{if $price_applies=='item'}
	<strong>{$messages.502114}</strong> {$quantity} @ {$finalBid} {$messages.502115}
{else}
	<strong>{$finalBidLabel}</strong> {$finalBid}
{/if}
<br /><br />
{if $additionalFees}
	<strong>{if $price_applies=='item'}{$messages.502116}{else}{$messages.500033}{/if}</strong><br />
	{foreach $additionalFees.formatted as $key => $fee}
		{if $key!=='total'}
			{$fee}<br />
		{/if}
	{/foreach}
	{if $price_applies=='item'}
		<strong>{$messages.502117}</strong> {$additionalFees.formatted.total}
		<br /><br />
		<strong>{$messages.502118}</strong> {$additionalFees.grandTotal}
		<br /><br />
		<strong>{$messages.500036}</strong> {$additionalFees.grandGrandTotal}
	{else}
		<strong>{$messages.500035}</strong> {$additionalFees.formatted.total}
		<br /><br />
		<strong>{$messages.500036}</strong> {$additionalFees.grandTotal}
	{/if}
	<br /><br />
	{$messages.500034}
	<br /><br />
{elseif $price_applies=='item'}
	<strong>{$messages.500036}</strong> {$grandTotal}
{/if}
{$sellerInfo.firstname} {$sellerInfo.lastname}<br />
{$sellerInfo.email}<br />
<br />
{$listingTitle}<br />
<a href="{$listingURL}">{$listingURL}</a><br />
{if $sellerBuyerInfo}
	<br />
	{$sellerBuyerInfo}
{/if}