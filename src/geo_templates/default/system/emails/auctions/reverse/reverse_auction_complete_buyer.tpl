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
{$additionalFeeInfo}<br />
{if $sellerBuyerInfo}
<br />
{$sellerBuyerInfo}
{/if}