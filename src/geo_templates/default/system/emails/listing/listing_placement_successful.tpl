{* 7.5.3-36-gea36ae7 *}
{$introduction}{if $salutation} {$salutation}{/if},<br />
<br />
{$messageBody}<br />
<br />
<br />
<a href="{$listingURL}">{$listingURL}</a><br />
{if $isAnonymousListing}
<br />
{$anonymousEmailText} {$anonymousEditPassword}<br />
<br />
{$editLinkLabel}<br />
<a href="{$editLink}">{$editLink}</a>
{/if}