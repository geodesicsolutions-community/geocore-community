{* 7.5.3-36-gea36ae7 *}
{$introduction}{if $salutation} {$salutation}{/if},<br />
{$messageBody}<br />
<br />
{$orderIdLabel} {$orderId}<br />
{$orderStatusActive}<br />
<br />
{$line}<br />
{$infoHeader}<br />
{foreach $itemInfos as $info}
	{$info}<br />
{/foreach}
{$line}<br />
<br />
{$orderTotalLabel} {$orderTotal}<br />
{$fullPaymentReceived}
{if $invoiceLink}
	<br />
	<br />
	<a href="{$invoiceLink}">{$invoiceLink}</a>
{/if}