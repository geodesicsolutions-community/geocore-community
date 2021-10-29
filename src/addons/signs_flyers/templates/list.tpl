{* 7.5.3-36-gea36ae7 *}

<div class="content_box">
	<h1 class="title">{$messages.1172}</h1>
	<h1 class="subtitle">{$messages.1173}</h1>

	
	{if $no_current_listings}
		<div class="field_error_box">
			{$messages.1181}
		</div>	
	{else}
		<p class="page_instructions">
			{$messages.1174}
		</p>

		<table cellpadding="2" cellspacing="1" width="100%">
			<tr class="column_header">
				<td>{$messages.1175}</td>
				<td>{$messages.1176}</td>
				<td>{$messages.1177}</td>
			</tr>
			
			{foreach from=$listings key=id item=listing}
				<tr class="{cycle values="row_odd,row_even"}">
					<td width="100%"><a href="{$listing.listing_url}">{$listing.title}</a></td>
					<td class="nowrap"><a href="{$listing.sign_url}" class="mini_button">{$messages.1178}</a></td>
					<td class="nowrap"><a href="{$listing.flyer_url}" class="mini_button">{$messages.1179}</a></td>
				</tr>
			{/foreach}
		
		</table>
	{/if}
</div>

{if !$no_current_listings && $pagination}
	{$pagination}
{/if}
<br />
<div class="center">
	<a href="{$user_management_home_link}" class="button">{$messages.1180}</a>
</div>