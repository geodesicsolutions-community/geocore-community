{* 7.5.3-36-gea36ae7 *}



<div class="content_box">
	<h1 class="title">{$messages.102466}</h1>
	<p class="page_instructions">{$messages.102467}</p>
	
	{if $no_bids}
		<div class="note_box">
			{$messages.102472}
		</div>
	{else}
		<table style="width: 100%;">
			<tr class="column_header">
				<td>{$messages.102469}</td>
				<td>{$messages.102470}</td>
				{if $is_dutch}<td>{$messages.103043}</td>{/if}
				<td>{$messages.102471}</td>
			</tr>
			{foreach from=$bids item=bid}
				<tr class="{cycle values="row_even,row_odd"}">
					<td>{$bid.time_of_bid}</td>
					<td>{$bid.bid_amount}</td>
					{if $is_dutch}<td>{$bid.quantity}</td>{/if}
					<td><a href="{$bid.bidder_feedback_link}">{$bid.bidder_name}</a> {if $show_bidder_email}({$bid.bidder_email}){/if}</td>
				</tr>
			{/foreach}
		</table>
	{/if}
</div>
<br />

<div class="center">
	<a href="{$auctionLink}" class="button">{$messages.102468}</a>
</div>