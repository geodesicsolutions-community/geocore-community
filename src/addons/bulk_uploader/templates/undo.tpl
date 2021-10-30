<table style="width:100%;">
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="group_price_hdr">Undo a Bulk Upload</td>
	</tr>
	<tr>
		<td>
			<table class="form_table" style='width:100%'>
				<tr class='step_label'>
					<td class="col_hdr_left">Listing IDs</td>
					<td class="col_hdr_left">Inserted at time</td>
					<td class="col_hdr_left">Undo?</td>
				</tr>
				{if $fail eq 1}
					<tr class="error_row"><td>Error getting log of previous bulk uploads</td></tr>
				{elseif $empty eq 1}
					<tr class="odd_row"><td>There are no previous logs</td></tr>
				{else}
					{foreach $r as $obj}
						<tr class="even_row">
							<td>{$obj.listing_id_list|substr:0:30}...</td>
							<td>{$obj.insert_time|date_format:"%A, %B %e, %Y   %I:%M %p"}</td>
							<td>
								<a href="{$self_path}&deleteLog={$obj.log_id}" onClick="return confirm('Are you sure you want to delete all listings that were inserted by the bulk upload session?');">Delete</a>
							</td>
						</tr>
					{/foreach}
				{/if}
			</table>
		</td>
	</tr>
</table>
	