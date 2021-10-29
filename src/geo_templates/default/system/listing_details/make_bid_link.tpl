{* 7.0.3-314-g84cfb39 *}{strip}
	{if $query}
		<a href="{$classifieds_file_name}{$query}" class="button make_bid_link">
			{$messages.102719}
		</a>
	{else}
		{if $is_banned}{$messages.102861}{else}{$messages.102862}{/if}
		&nbsp;{$messages.102863}
	{/if}
{/strip}