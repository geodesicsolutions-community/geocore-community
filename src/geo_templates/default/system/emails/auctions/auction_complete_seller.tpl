{* 7.5.3-36-gea36ae7 *}
{if $salutation}{$salutation},<br />{/if}
{$messageBody}<br />
<br />
{if $isDutch}
	{$dutchSeparator}
	{$dutchResults}
{elseif $auctionSuccess}
	{$highBidderInfo.firstname} {$highBidderInfo.lastname}<br />
	{$highBidderInfo.email}<br />
	<br />
	{$listingTitle}<br />
	<br />
	{$finalBidLabel} {$finalBid}<br />
	<br />
	{if $additionalFees}
		<strong>{$messages.500037}</strong><br />
		{foreach $additionalFees.formatted as $key => $fee}
			{if $key!=='total'}
				{$fee}<br />
			{/if}
		{/foreach}
		<strong>{$messages.500040}</strong> {$additionalFees.formatted.total}
		<br /><br />
		<strong>{$messages.500038}</strong> {$additionalFees.grandTotal}
		<br /><br />
		{$messages.500039}
		<br /><br />
	{/if}
{/if}
<br />
<a href="{$listingURL}">{$listingURL}</a>
{if $isDutch}
	<br />
	{$dutchFooter}
{/if}
