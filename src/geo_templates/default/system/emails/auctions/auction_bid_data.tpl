{* 7.5.3-36-gea36ae7 *}
{* NOTE: this template is used for the internals of the current_high_bidder and outbid emails *}
{$titleLabel} {$title}<br />
{if $currentBid}
	<br />
	{$currentBidLabel} {$currentBid}<br />
{/if}
{if $maxBid}
	<br />
	{$maxBidLabel} {$maxBid}<br />
{/if}
<br />
{$endDateLabel} {$endDate}<br />
<br />
{$listingLinkLabel} <a href="{$listingLink}">{$listingLink}</a><br />