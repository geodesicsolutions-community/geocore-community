{* 7.5.3-36-gea36ae7 *}
{if $salutation}{$salutation},<br />{/if}
{$messageBody}<br />
<br />
{$quantityWonLabel}{$quantityWon} @ {$finalBid}<br />
{$totalBidLabel}{$totalBid}<br />
<br />
{if $additionalFees}
	<strong>{$messages.500045}</strong><br />
	{foreach $additionalFees.formatted as $key => $fee}
		{if $key!=='total'}
			{$fee}<br />
		{/if}
	{/foreach}
	<strong>{$messages.500046}</strong> {$additionalFees.formatted.total}<br />
	<strong>{$messages.500052}</strong> {$additionalFees.total_per_item}
	<br /><br />
	<strong>{$messages.500047}</strong> {$additionalFees.grandTotal}
	<br /><br />
	{$messages.500048}
	<br /><br />
{/if}
{$sellerInfoLabel}<br />
{$sellerInfo.firstname} {$sellerInfo.lastname}<br />
{$sellerInfo.username} ( {$sellerInfo.email} )<br />
<br />
<a href="{$listingURL}">{$listingURL}</a><br />
<br />
{$footerText}