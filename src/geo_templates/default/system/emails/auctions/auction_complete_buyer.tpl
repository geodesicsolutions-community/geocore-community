{* 7.5.3-36-gea36ae7 *}
{if $salutation}{$salutation},<br />{/if}
{$messageBody}<br />
<br />
{$sellerInfo.firstname} {$sellerInfo.lastname}<br />
{$sellerInfo.email}<br />
<br />
{$listingTitle}<br />
<br />
<a href="{$listingURL}">{$listingURL}</a><br />
<br />
{$finalBidLabel} {$finalBid}<br />
<br />
{if $additionalFees}
	<strong>{$messages.500041}</strong><br />
	{foreach $additionalFees.formatted as $key => $fee}
		{if $key!=='total'}
			{$fee}<br />
		{/if}
	{/foreach}
	<strong>{$messages.500042}</strong> {$additionalFees.formatted.total}
	<br /><br />
	<strong>{$messages.500043}</strong> {$additionalFees.grandTotal}
	<br /><br />
	{$messages.500044}
	<br /><br />
{/if}
{if $sellerBuyerInfo}
{$sellerBuyerInfo}
{/if}