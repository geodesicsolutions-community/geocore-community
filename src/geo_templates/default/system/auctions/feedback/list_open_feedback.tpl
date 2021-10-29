{* 7.6.3-149-g881827a *}

<div class="content_box">
	<h1 class="title">{$messages.102423}</h1>
	
	{if $no_open_feedbacks} 
		<div class="field_error_box">{$messages.102424}</div>
	{else} 
		<p class="page_instructions">{$messages.102425}</p>
		
		<table style="width: 100%;">
			<tr class="column_header">
				<td class="center">{$messages.102501}</td>
				<td class="center">{$messages.102426}</td>
				<td class="center">{$messages.102427}</td>
				<td class="center">{$messages.102428}</td>
				<td class="center">{$messages.102429}</td>
				<td class="center"></td>
			</tr>
			{foreach from=$feedbacks item=feedback}
				<tr class="{cycle values="row_even,row_odd"}">
					<td class="center">{$feedback.title} ({$feedback.final_price})</td>
					<td class="center">{$feedback.startDate}</td>
					<td class="center">{$feedback.endDate}</td>
					<td class="center">{$feedback.rated_user} ({if $feedback.rated_is_seller}{$messages.102430}{else}{$messages.102431}{/if}) {$feedback.rated_email}</td>
					<td class="center"><a href="{$feedback.reply_link}" class="button">{$messages.102432}</a></td>
					<td class="center"><a href="{$feedback.auction_link}" class="button">{$messages.102433}</a></td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="6">
						<div class="field_error_box">{$messages.102424}</div>
					</td>
				</tr>
			{/foreach}
		</table>
	{/if}
</div>

<br />

<div class="center">
	<a href="{$feedback_home_link}" class="button">{$messages.102804}</a>
	<a href="{$user_management_home_link}" class="button">{$messages.102803}</a>
</div>
