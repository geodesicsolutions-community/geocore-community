{* 16.09.0-105-ga458f5f *}

<div class='table-responsive'>
	<table class='table table-hover table-striped table-bordered'>
		<thead>
			<tr>
				<th><input type="checkbox" id="checkAllLoad" /></th>
				<th>Name</th>
				<th>File Exported</th>
				<th>Created</th>
				<th>Last Updated</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$loadSettings item=row}
				<tr>
					<th><input type="checkbox" class="deleteLoadCheckbox" name="delete_settings[]" value="{$row.name|fromDB|escape}" /></th>
					<td>{$row.name|fromDB|escape}</td>
					<td>{$row.filename|fromDB|escape}.{$row.export_type}</td>
					<td>{$row.created|format_date}</td>
					<td>{$row.last_updated|format_date}</td>
					<td>
						<input type="hidden" value="{$row.name|fromDB|escape}" />
						<button class="mini_button loadButtons">Load</button>
					</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="5"><div class="page_note_error">No saved export settings found.</div></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
{if $loadSettings}
	<input type="submit" value="Delete Selected" class="mini_cancel" id="submitDelete" />
{/if}