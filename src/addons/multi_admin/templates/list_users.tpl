{* 16.09.0-106-ge989d1f *}
{$admin_messages}
<form action="index.php?page=addon_multi_admin_users" method="post" class='form-horizontal'>
<fieldset>
	<legend>Admin Users</legend>
	<div class='table-responsive'>
		<table class='table table-bordered table-hover table-striped'>
			<thead>
				<tr>
					<th>Admin User</th>
					<th>Admin Group</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$users item="user"}
					<tr>
						<td>
							<a href="index.php?page=addon_multi_admin_user_edit&amp;user_id={$user.user_id}">{$user.username}</a>
						</td>
						<td>
							{if $user.group_id}<a href="index.php?page=addon_multi_admin_group_edit&group_id={$user.group_id}">{$user.group_name}</a>{else}None{/if}
						</td>
						<td style="text-align:center;">
							{$delete_button|replace:"(USER)":$user.user_id}
						</td>
					</tr>
				{foreachelse}
					<tr><td colspan="3" class="medium_font" align="center"><div class="page_note_error">No Admin Users</div></td></tr>
				{/foreach}
				<tr>
					<td class="col_ftr">
						<div class='form-group'>
							<label class='control-label col-xs-5'>New User Name:</label>
							<div class='col-xs-7'>
								<input type="text" name="user_add" class='form-control' />
							</div>
						</div>
					</td>
					<td class="col_ftr">
						<div class='form-group'>
							<label class='control-label col-xs-5'>Add to Group:</label>
							<div class='col-xs-7'>
								{$group_dropdown}
							</div>
						</div>
					</td>
					<td class="col_ftr center">
						<input type="submit" name="auto_save" value="Create New Admin User"  />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</fieldset>
</form>