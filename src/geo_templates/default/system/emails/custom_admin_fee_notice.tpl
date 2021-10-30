{* $Rev: *}
{* NOTE:  This template used in e-mail, so must keep HTML simple enough to work in most e-mail clients *}

<p>
	This is an automated notice to inform you that the below custom fee status
	is now <strong>{$status}</strong>.
</p>
<p>
Custom Fee Description: <strong>{$label|escape}</strong> [Item #{$order_item_id}]<br />
Cost: <strong>{$cost|displayPrice}</strong><br />
Order: #<strong>{$order_id}</strong><br />
Buyer: <strong>{$buyer}</strong><br />
Created by Admin: <strong>{$admin}</strong><br />
On Website: <a href="{$classifieds_url}">{$classifieds_url}</a><br />
Updated Status: <strong>{$status}</strong>
</p>
<p>
	This notice was sent to you because the e-mail <strong>{$email|escape}</strong> was entered 
	by the site admin to notify you once the above custom fee's status went active (meaning
	that payment was received).  If the status on this custom fee item changes again,
	you will be notified.
</p>
<p>
	Note that a status of <em>active</em> indicates that payment has been received.
	Any status other than active means that the item was already active, but was 
	changed to the status specified.  The status can only be "de-activated" by
	the site admin.
</p>