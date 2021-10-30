{* 16.09.0-79-gb63e5d8 *}

{if $results}
<div class="table-responsive tabbed-div-bg">
	<table class="table table-hover table-striped table-bordered">
		<thead>
			<tr class="col_hdr_top">
				<td></td>
				<td>Page/Module Name</td>
				<td>Text Label</td>
				<td>Language</td>
				<td>Matching Text</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$results item=row}
				<tr class="{cycle values='row_color1,row_color2'}">
					<td style="text-align: center; width: 10px;">
						<a href="index.php?page=sections_edit_text&amp;b={$row.page_id}&amp;c={$row.text_id}&amp;l={$row.language_id}" class="mini_button">Edit</a>
					</td>
					<td>
						<a href="index.php?page=sections_edit_text&amp;b={$row.page_id}&l={$row.language_id}">{$row.page_name}</a>
					</td>
					<td>
						{$row.label}
					</td>
					<td>
						{$row.language}
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
	<div class="page_note_error">No page or module text results matching search criteria.</div>
{/if}

