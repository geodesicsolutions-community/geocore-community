{* 16.09.0-106-ge989d1f *}
{$admin_messages}
<form action="index.php?page=addon_multi_admin_groups" method="post" class='form-horizontal'>
<fieldset>
	<legend>Admin Groups</legend>
	<div class='table-responsive'>
		<table class='table table-bordered table-hover table-striped'>
			<thead>
				<tr>
					<th>Group Name</th>
					<th># Users</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$groups item="group"}
					<tr>
						<td>
							<a href="index.php?page=addon_multi_admin_group_edit&amp;group_id={$group.group_id}">{$group.name}</a>
						</td>
						<td class="center">
							{$group.user_count}
						</td>
						<td class="center">
							{$delete_button|replace:"(GROUP)":$group.group_id}
						</td>
					</tr>
				{foreachelse}
					<tr><td colspan="3" align="center"><div class="page_note_error">No Existing Groups</div></td></tr>
				{/foreach}
				<tr>
					<td class="col_ftr center" colspan="3">
						<input type="text" name="group_add" />
						<input type="submit" name="auto_save" value="Create New Admin Group"  />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</fieldset>
</form>