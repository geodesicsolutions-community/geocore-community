{* 6.0.7-3-gce41f93 *}

<p>===== <strong>Automatic Admin Notice</strong> =====</p>
<p><strong>For Site: </strong><a href="{$classifieds_url}">{$classifieds_url}</a></p>


<p>
	There are new order items awaiting approval in the admin panel at <strong>Orders &gt; Manage Items</strong>.  Below is a list
	of those items.
</p>
<p>
	{foreach $items as $item}
		{* Note:  The title is meant for display in HTML already so should already
			be cleaned to prevent XSS, so no need to filter here. *}
		- {$item.title} (#{$item@key})<br /><br />
	{/foreach}
</p>