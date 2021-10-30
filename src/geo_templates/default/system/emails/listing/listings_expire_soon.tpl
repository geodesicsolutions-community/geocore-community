{* 7.5.2-17-geda3003 *}
{$introduction} {$salutation},<br />
<br />
{$expirationMessage}<br />
<br />
{foreach $expiringListings as $id => $info}
	<a href="{$listingURLs.$id}">{$info.title}</a><br />[ <a href="{$listingURLs.$id}">{$listingURLs.$id}</a> ]<br />
	{if $expireLabel}
		{$expireLabel} {$info.ends|format_date}<br />
	{/if}
	{if $renewables.$id}
		{$renewLabel} <a href="{$classifieds_url}?a=cart&amp;action=new&amp;main_type=listing_renew_upgrade&amp;listing_id={$id}&amp;r=1">{$classifieds_url}?a=cart&action=new&main_type=listing_renew_upgrade&listing_id={$id}&r=1</a>
		<br />
	{/if}
	<br />
{/foreach}
