{* 7.5.3-36-gea36ae7 *}
{if $salutation}{$salutation},<br />{/if}
{$messageBody}<br />
<br />
{if $auctionSuccess}
	{$lowBidderInfo.firstname} {$lowBidderInfo.lastname}<br />
	{$lowBidderInfo.email}<br />
	<br />
	{$listingTitle}<br />
	<br />
	{$finalBidLabel} {$finalBid}<br />
	<br />
	{$additionalFeeInfo}<br />
{/if}
<br />
<a href="{$listingURL}">{$listingURL}</a>