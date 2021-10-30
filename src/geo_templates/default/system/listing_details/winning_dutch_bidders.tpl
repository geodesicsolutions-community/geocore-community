{* 7.0.2-325-gdeba5a4 *}
{if $dutchBidders}
	<table style="width: 100%;">
		<tr class="results_column_header">
			<td>{$col_txt.userColumn}</td>
			<td>{$col_txt.priceColumn}</td>
			<td>{$col_txt.quantityColumn}</td>
		</tr>
		{foreach $dutchBidders as $bid}	
			<tr class="{cycle values='row_even,row_odd'}">
				{* Note to template designers: more user info is available to display in
					the $bid.bidder_info array to allow displaying user by other details 
					such as first or last name if desired.  *}
				<td>{$bid.bidder_info.username}</td>
				<td>{$bid.bid_display}</td>
				<td>{$bid.quantity}</td>
			</tr>
		{/foreach}
	</table>
{else}
	{if $printFriendly}{$messages.103366}{else}{$messages.102710}{/if}
{/if}
