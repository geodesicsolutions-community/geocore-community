{* 7.3.3-155-g57ba80e *}{strip}
	{if $anon}
		{$anon_username}
	{else}
		{strip}
			{if $show_contact_seller!='no'}<a href="{$classifieds_file_name}?a=13&amp;b={$listing_id}" class="display_ad_value">{/if}
				{$seller_data.username}
			{if $show_contact_seller!='no'}</a>{/if}
		{/strip}
	{/if}
{/strip}