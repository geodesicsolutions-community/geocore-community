{* 7.6.1-19-g6764bb2 *}
<fieldset>
	<legend>Itemized Shared Fee Payments Due For User: <style color='green'>{$username} </style>({$firstname} {$lastname})</legend>
	<div id='table_settings'>
		<table class='header'>
			<tr class='col_hdr'>
				<th>Type of transaction</th>
				<th>listing(id)</th>
				<th>date placed</th>
				<th>date ended</th>
				<th>shared fee amount</th>
				<th>mark as paid</th>
				<th>pay thru paypal</th>
			</tr>
			{foreach $due_out_transactions as $row}
			<tr class="{cycle values='row_color1,row_color2'}">
				<td>{$row['type_of_fee']}</td>
				<td>{$row['listing_title']}({$row['listing_id']})</td>
				<td>{$row['listing_started']}</td>
				<td>{$row['listing_ends']} ({$row['expired']})</td>
				<td>{$precurrency}{$row['cost']}{$postcurrency}</td>
				<td><a href=index.php?page=shared_fee_payments&mc=addon_cat_share_fees&specific_user={$userid}&manually_pay={$row['itemid']}>mark as paid out</td>
				<td>{$row['paypal_link']}</td>
			</tr>
			{/foreach}	
			<tr class='col_ftr'>
				<td colspan=4 style="text-align: right; padding-right: 20px;">total of payments due</td>
				<td colspan=3>{$precurrency}{$user_total}{$postcurrency}</td>
			</tr>
			{if (isset($paypal_id) && $paypal_id != 0)}
			<tr class='col_ftr'>
				<td colspan=4>click link to pay by paypal</td>
				<td colspan=3>{if (isset($paypal_id) && $paypal_id != 0)}click to pay total fees for this user{else}no paypal id{/if}</td>
			</tr>	
			{/if}
			
		</tbody>
		</table>
	</div>
	
	
	<div id='table_settings'>
		<table class='header'>
			<tr class='col_hdr'>
				<td colspan=6>Total Shared Fees Paid Out To {$username} ({$firstname} {$lastname})</td>
			</tr>
			{if isset($paid_out_transactions)}
			<tr class='col_hdr'>
				<th>Type of transaction</th>
				<th>listing(id)</th>
				<th>date placed</th>
				<th>date ended</th>
				<th>paid amount</th>
				<th>date paid</th>
			</tr>
			{foreach $paid_out_transactions as $row}
			<tr class="{cycle values='row_color1,row_color2'}">
				<td>{$row['type_of_fee']}</td>
				<td>{$row['listing_title']}({$row['listing_id']})</td>
				<td>{$row['listing_started']}</td>
				<td>{$row['listing_ends']}</td>
				<td>{$precurrency}{$row['cost']}{$postcurrency}</td>
				<td>{$row['paid_out_date']}</td>
			</tr>
			{/foreach}	
			<tr class='col_ftr'>
				<td colspan=5 style="text-align: right; padding-right: 20px;">total paid out:</td>
				<td colspan=1>{$precurrency} {$total_paid_out} {$postcurrency}</td>
			</tr>	
			{else}
			<tr><td colspan=6>{$no_payments_made_yet}</td></tr>
			{/if}
			<tr class='col_ftr'><td colspan=7 style="text-align: center;"><a href="index.php?page=shared_fee_payments&mc=addon_cat_share_fees">back to all fees list</a></td></tr>
			<tr class='col_ftr'>
				<td colspan='6' style="text-align: center; "><a href=index.php?page=shared_export_fee_payments_due&mc=addon_cat_share_fees&export_shared_fees=1&mark_as_paid_out=1&specific_user=[$specific_user]>export all shared fees due in system for {$username} and mark as paid in system</a><br><br>-or-<br><br><a href=index.php?page=export_shared_fee_payments_due&mc=addon_cat_share_fees&export_shared_fees=1&mark_as_paid_out=0&specific_user={$specific_user}>export all shared fees due in system for {$username}</a> </td>
			</tr>			
		</tbody>
		</table>
	</div>	
</fieldset>