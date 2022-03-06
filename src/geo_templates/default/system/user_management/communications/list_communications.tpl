{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title my_account">{$messages.624}</h1>
	<h3 class="subtitle">{$messages.387} {$helpLink}</h3>
	<p class="page_instructions">{$messages.388}</p>
	
	{if $showCommunications}
		{if $communications.received|count > 0}
			<table style="width: 100%;">
				<tr class="column_header">
					<td class="nowrap">{$messages.391}</td>
					<td class="title">{$messages.392}</td>
					<td class="nowrap">{$messages.393}</td>
					<td class="nowrap"></td>
					<td class="nowrap"></td>
				</tr>
				{foreach from=$communications.received item=comm}
					<tr class="{cycle values='row_odd,row_even'}{if !$comm.read}_highlight unread{/if}">
						<td class="nowrap">
							{if $comm.sender_id}<a href="{$classifieds_file_name}?a=6&amp;b={$comm.sender_id}">{/if}
							{$comm.sender}
							{if $comm.sender_id}</a>{/if}
						</td>
						<td>
							{if $comm.listingId}<a href="{$classifieds_url}?a=2&b={$comm.listingId}">{/if}
							{$comm.listingTitle}
							{if $comm.listingId}</a>{/if}
						</td>
						<td><span style="white-space: nowrap;">{$comm.dateSent}</span></td>
						<td><a href="{$comm.viewLink}" class="mini_button">{$messages.394}</a></td>
						<td><a href="{$comm.deleteLink}" class="mini_button">{$messages.395}</a></td>
					</tr>
				{/foreach}
			</table>
		{/if}
		
		
		{if $communications.sent|count > 0}
			<table style="width: 100%;">
				<tr class="column_header">
					<td class="nowrap">{$messages.502298}</td>
					<td class="title">{$messages.392}</td>
					<td class="nowrap">{$messages.502299}</td>
					<td class="nowrap"></td>
					<td class="nowrap"></td>
				</tr>
				{foreach from=$communications.sent item=comm}
					<tr class="{cycle values='row_odd,row_even'}">
						<td>
							{if $comm.receiver_id}<a href="{$classifieds_file_name}?a=6&amp;b={$comm.receiver_id}">{/if}
							{$comm.receiver}
							{if $comm.receiver_id}</a>{/if}
						</td>
						<td>
							{if $comm.listingId}<a href="{$classifieds_url}?a=2&b={$comm.listingId}">{/if}
							{$comm.listingTitle}
							{if $comm.listingId}</a>{/if}
						</td>
						<td><span style="white-space: nowrap;">{$comm.dateSent}</span></td>
						<td><a href="{$comm.viewLink}" class="mini_button">{$messages.394}</a></td>
						<td><a href="{$comm.deleteLink}" class="mini_button">{$messages.395}</a></td>
					</tr>
				{/foreach}
			</table>
		{/if}
	{else}
		{* no communications for this user *}
		<div class="note_box">{$messages.390}</div>
	{/if}
</div>
<div class="center">
	<a href="{$commConfigLink}" class="button">{$messages.396}</a>
	<a href="{$userManagementHomeLink}" class="button">{$messages.397}</a>
</div>