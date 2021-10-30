{* 7.5.3-36-gea36ae7 *}
<style>
th,td {
padding: .3em;
}
th {
background-color: #CCC;
}
td {
background-color: #EEE;
}
</style>
<p>{$messages.502155} {$salutation}</p>
<p>
{$messages.502156}
</p>
<table>
	<tr>
		<th>{$messages.502157}</th>
		<th>{$messages.502158}</th>
		<th>{$messages.502159}</th>
		<th>{$messages.502160}</th>
		<th>{$messages.502161}</th>
		<th>{$messages.502162}</th>
	</tr>
	{foreach $final_fees as $fee}
		<tr>
			<td>
				{if $fee.listing_url}
					<a href="{$fee.listing_url}">{$messages.502163}</a>
				{else}
					{$messages.502164}
				{/if}
			</td>
			<td>{$fee.adjusted_bid}</td>
			<td>{$fee.percent}</td>
			<td>{$fee.fixed}</td>
			<td>{$fee.bid_quantity}</td>
			<td>{$fee.total}</td>
		</tr>
	{/foreach}
</table>
<p>{$messages.502165}</p>
<p>
<a href="{$classifieds_url}">{$messages.502166}</a>
</p>
<br />
