{* 7.4.3-11-g745410f *}
{* NOTE: this template is used for the internals of the current_low_bidder and outbid emails *}
{$titleLabel} {$title}<br />
<br />
{$currentBidLabel} {$currentBid}<br />
{if $maxBid}
	<br />
	{$maxBidLabel} {$maxBid}<br />
{/if}
<br />
{$endDateLabel} {$endDate}<br />
<br />
{$listingLinkLabel} <a href="{$listingLink}">{$listingLink}</a><br />