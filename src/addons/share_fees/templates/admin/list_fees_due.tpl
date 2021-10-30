{* 7.6.1-19-g6764bb2 *}
<fieldset>
	<legend>Shared Fee Payments Due By User</legend>
	
	{if $no_payments_due_out}
	<div><span style="color: red">{$no_payments_due_out}</span></div>
	{else}
	<div id='table_settings'>
		<table class='header'>
			<tr class='col_hdr'>
				<td>Username</th>
				<td>Name</th>
				<td>Last Transaction Date</th>
				<td>Number of Transactions</th>
				<td>User Total</th>
				<td>Pay Thru Paypal</th>
				<td>View User Transactions</th>
			</tr>
			{foreach $due_out_transactions as $row}
			<tr  class="{cycle values='row_color1,row_color2'}">
				<td>{$row['username']}</td>
				<td>{$row['firstname']} {$row['lastname']}</td>
				<td>{$row['last_transaction_date']}</td>
				<td>{$row['transaction_total']}</td>
				<td>{$precurrency}{$row['user_total']}{$postcurrency}</td>
				<td>{$row['paypal_link']}</td>
				<td><a href=index.php?page=shared_fee_payments&mc=addon_cat_share_fees&specific_user={$row['userid']}>view user details</a></td>
			</tr>
			{/foreach}	
			<tr class='col_ftr'>
				<td colspan='6' style="text-align: right; padding-right: 20px;">total of all payments due</td>
				<td>{$precurrency} {$all_user_total} {$postcurrency}</td>
			</tr>
			<tr class='col_ftr'>
				<td colspan='6' style="text-align: right; padding-right: 20px;">total # transactions for all users</td>
				<td>{$all_transaction_total}</td>
			</tr>	
			<tr class='col_ftr'>
				<td colspan='7' style="text-align: center; "><a href=index.php?page=export_shared_fee_payments_due&mc=addon_cat_share_fees&export_shared_fees=1&mark_as_paid_out=1>export all shared fees due in system and mark as paid in system</a><br><br>-or-<br><br><a href=index.php?page=export_shared_fee_payments_due&mc=addon_cat_share_fees&export_shared_fees=1&mark_as_paid_out=0>export all shared fees due in system</a> </td>
			</tr>			
		</tbody>
		</table>
	</div>
	{/if}
</fieldset>