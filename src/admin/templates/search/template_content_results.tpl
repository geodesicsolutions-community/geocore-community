{* 16.09.0-79-gb63e5d8 *}

{if $results}
<div class="table-responsive tabbed-div-bg">
	<table class="table table-hover table-striped table-bordered">
		<thead>
			<tr class="col_hdr_top">
				<td></td>
				<td>File Name</td>
				<td>Matching Text</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$results item=row}
				<tr class="{cycle values='row_color1,row_color2'}">
					<td style="text-align: center; width: 150px;">
						<a href="index.php?page=design_edit_file&amp;file={$row.filename|escape}" class="mini_button">View/Edit Template</a>
					</td>
					<td>
						{$row.filename}
					</td>
					<td>
						{$row.text}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{else}
	<div class="page_note_error">No template contents found matching search criteria.</div>
{/if}