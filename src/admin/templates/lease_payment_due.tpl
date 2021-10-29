{* 6.0.7-3-gce41f93 *}

{if $license_overdue_invoice}
	<div style="background-color: #FFF0F4; padding: 5px; border: #CC0033 3px solid; margin: 10px;">
		<strong class="text_blue" style="font-size: medium;">Lease Payment Due:</strong><br /><br />
		
		Our records indicate that you have a lease payment due for this leased
		license, that was due on <span class="text_blue">{$license_overdue_invoice.oldest_due|date_format:'F j, Y'}</span>.
		You are currently in the grace period, please pay this invoice right away to <strong>avoid interruptions in service</strong>.
		Note that if you have auto-pay set up, there is no further action needed, once the auto-payment
		is received this notice will be removed automatically.
		<br /><br />
		This notice will not show once we have received payment.  If you have already paid this invoice, and this notice still displays, please <strong>contact us immediately</strong>.
		
		<div class="center" style="padding: 10px;">
			<a class="button" href="{$license_overdue_invoice.oldest_link}" onclick="window.open(this.href); return false;">View/Pay Invoice</a>
		</div>
		<div class="clear"></div>
	</div>
{/if}