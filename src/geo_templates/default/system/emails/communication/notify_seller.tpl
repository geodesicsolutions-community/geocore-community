{* 7.5.3-36-gea36ae7 *}
{$introduction} {$salutation},<br />
<br />
{$listingPreface}<br />
<a href="{$listingURL}">{$listingURL}</a><br />
<br />
{if $senderName}
{$senderNameLabel} {$senderName}<br />
{/if}
{if $senderPhone}
{$senderPhoneLabel} {$senderPhone}<br />
{/if}
<br />
{if $senderComments}
{$senderCommentsLabel}<br />
{$senderComments}<br />
{/if}
<br />
{if $replyLink}
{$replyLinkInstructions}<br />
<br />
<a href="{$replyLink}">{$replyLink}</a><br />
{/if}
<br />
{$senderIP} : {$senderHost}