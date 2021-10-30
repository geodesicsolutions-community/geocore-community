{* 7.5.3-36-gea36ae7 *}
{$messageBody}<br />
<br />
{$fromLabel} {$messageFromUsername}{if $messageFromEmail} {$messageFromEmail}{/if}<br />
<br />
{if $showReplyLink}
{$privateCommMessage}<br />
<br />
<a href="{$privateReplyLink}">{$privateReplyLink}</a><br />
{/if}
<br />
<br />
{$senderIP} : {$senderHost}
