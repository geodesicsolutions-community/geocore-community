<form action='?mc=addon_cat_bulk_uploader&addon_bulk_uploader_main_config&page=addon_bulk_uploader_main_config&b_page=2' method=post enctype='multipart/form-data'>
					<fieldset>
					<legend>Select csv file</legend>
						<div class='row_color1'>
							<div class='leftColumn'>
								Select A File:
							</div>
							<div class='rightColumn'>
								<input id='file_name' name='csvData' type='file'>
							</div>
							<div class='clearColumn'></div>
						</div>
						
						<div class='row_color2'>
							<div class='leftColumn'>
								Preview Rows:
							</div>
							<div class='rightColumn'>
								<select id='bulkData[previewLength]' name='bulkData[previewLength]'>
								<option value='5'>5</option>
								<option value='10'>10</option>
								<option value='15'>15</option>
								</select>
							</div>
							<div class='clearColumn'></div>
						</div>
						
						<div class='row_color1'>
							<div class='leftColumn'>
								File Compression:
							</div>
							<div class='rightColumn'>
								<select id='bulkData[compression]' name='bulkData[compression]'>
								<option value='none' selected>None
								<option value='zip'>zip<option value='gzip'>gzip<option value='bz2'>bz2
								</select>
							</div>
							<div class='clearColumn'></div>
						</div>
						
						<div class='row_color2'>
							<div class='leftColumn'>
								Skip First Row:
							</div>
							<div class='rightColumn'>
								<select id='bulkData[skipfirstrow]' name='bulkData[skipfirstrow]'>
								<option value='0'>NO</option>
								<option value='1'>Yes</option>
								</select>
							</div>
							<div class='clearColumn'></div>
						</div>
					
						<div class='row_color1'>
							<div class='leftColumn'>
								Delimiter:
							</div>
							<div class='rightColumn'>
								<input type='text' id='bulkData[delimeter]' name='bulkData[delimiter]' size='5' value=','> e.g.,&nbsp;&nbsp; <b>, . |</b>
							</div>
							<div class='clearColumn'></div>
						</div>
						
						<div class='row_color2'>
							<div class='leftColumn'>
								Encapsulation:
							</div>
							<div class='rightColumn'>
								<input type='text' id='bulkData[encapsulation]' name='bulkData[encapsulation]' size='5' value='"'> e.g.,&nbsp;&nbsp; <b>''</b> </td>
							</div>
							<div class='clearColumn'></div>
						</div>
					
					</fieldset>
						</form>
					<div style='clear:both'></div>
					
			<fieldset>
				<legend>Delete Preview Uploads</legend>
				<table style='border: 1px solid #88aacc;'>
					<thead>
						<tr>
							<th class='col_hdr'>Listing IDs</th>
							<th class='col_hdr'>Date</th>
							<th class='col_hdr'>Undo</th>
						</tr>
					</thead>
					<tbody id='orders_parent'>
					<tr><td>No previous uploads found!</td></tr>
					</tbody>
				</table>
			</fieldset>



















<table class='step_label'>
	<tr>
		<td class='col_hdr_left'>Bulk Upload Data&nbsp;{$tooltip.0}</td>
	</tr>
</table>

<table class='form_table' id='file_block'>
	<tr class='form_row'>
		<td class='form_label medium_font medium_font'>Upload File:</td>
		<td>
		<input id='file_name' name='csvData' type='file'>
		</td>
	</tr>
	<tr class='form_row'>
		<td class='form_label medium_font medium_font'>Preview Rows:</td>
		<td>
		<select id='bulkData[previewLength]' name='bulkData[previewLength]'>
		<option value='5'>5</option>
		<option value='10'>10</option>
		<option value='15'>15</option>
		</select>
	</td>
	</tr>
	
	<tr class='form_row'>
		<td class='form_label medium_font'></td>
		<td>
		<input type=radio id='bulkData[compression]' name='bulkData[compression]' value='none' checked>None&nbsp;&nbsp;
		{if $zip_read eq 1}
		<input type=radio id='bulkData[compression]' name='bulkData[compression]' value='zip'>'zipped'&nbsp;&nbsp;
		{/if}
		{if $gzopen eq 1}
		<input type=radio id='bulkData[compression]' name='bulkData[compression]' value='gzip'>'gzipped'&nbsp;&nbsp;
		{/if}
	
		{if $bzopen eq 1}
		<input type=radio id='bulkData[compression]' name='bulkData[compression]' value='bz2'>'bz2'&nbsp;&nbsp;
		{/if}
		</td>
	</tr>
</table>

<table class='step_label'>
	<tr>
		<td class='col_hdr_left'>
			CSV config &nbsp;
		</td>
	</tr>
</table>
<table>
<tr class='form_row'>
	<td class='form_label medium_font'>skip row 1:</td>
	<td>
		<input id='skipFirstListings' name='skipFirstListings' type='checkbox'>
		(if first row contains column titles)
	</td>
</tr>
</table>

<table class='step_label'>
	<tr>
	<td class='col_hdr_left'>delimiter & encapsulation&nbsp;{$tooltip.1}</td></tr>
</table>

<table class='form_table'>
<tr class='form_row'>
<td class='form_label medium_font'>delimeter:</td>
<td>
	<input type='text' id='bulkdata[delimeter]' name='bulkdata[delimiter]' size='5' value='{if $delimiter} {$delimiter} {else},{/if}'> e.g.,&nbsp;&nbsp; <b>, . |</b>
</td>
</tr>
<tr class='form_row'>
<td class='form_label medium_font'>encapsulation:</td>
<td>
	<input type='text' id='bulkdata[encapsulation]' name='bulkdata[encapsulation]' size='5' value='{$encapsulation}'> e.g.,&nbsp;&nbsp; <b>''</b> </td>
</tr>
</table>