{* 7.2beta1-119-ge57f35b *}

<strong>{$messages.502124}</strong>
<br />

{foreach $listing.bids as $bid}
	{if $bid@iteration<4}
		{$bid.bidder_info.username} ({$bid.bidder_info.email}) - {$bid.quantity} @ {$bid.price_per} {$messages.502125}
		<br />
	{/if}
{/foreach}
<a href="#" class="mini_button showAllBidsButton">{$messages.502126}</a>

<div class="showAllBids" id="bidTable{$listing.id}" style="display: none;">
	<div>
		<div class="closeBoxX"></div>
		<h2 class="lightUpTitle title">{$messages.502127}</h2>
		<div style="padding: 15px;">
			{$listing.title|fromDB} ({$listing.id})
			<br /><br />
			{$messages.502122} {$listing.quantity}<br />
			{$messages.502123} {$listing.quantity_remaining}<br /><br />
		</div>
		<table>
			<thead>
				<tr class="column_header">
					<th>
						{$messages.502128}
					</th>
					<th>
						{$messages.502129}
					</th>
					<th>
						{$messages.502130}
					</th>
					<th>
						{$messages.502131}
					</th>
					<th>
						{$messages.502132}
					</th>
				</tr>
			</thead>
			<tbody>
				{foreach $listing.bids as $bid}
					<tr class="{cycle values='row_odd,row_even'}">
						<td>
							{$bid.bidder_info.username}
							({$bid.bidder_info.email})
						</td>
						<td class="cntr">{$bid.quantity}</td>
						<td>{$bid.price_per}</td>
						<td>{$bid.total_due}</td>
						<td>{$bid.time_of_bid|format_date}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
</div>