{* 6.0.7-3-gce41f93 *}

<p>===== <strong>Automatic Admin Notice</strong> =====</p>
<p><strong>For Site: </strong><a href="{$classifieds_url}">{$classifieds_url}</a></p>


<p>
	The system recieved a payment notice for {$gateway} recurring billing, that
	could not be linked to a recurring billing in the site!  Below are the
	details of the transaction.
</p>
<p>
	{foreach $post as $key => $value}
		{if $value}
			<strong>{$key|escape}</strong> : {$value|escape}<br /><br />
		{/if}
	{/foreach}
</p>