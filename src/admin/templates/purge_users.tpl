{* 16.09.0-79-gb63e5d8 *}

{$admin_msgs}

{if !$confirm}
	<form action="index.php?page=users_confirm_purge&mc=users" method="post" class="form-horizontal form-label-left">
		<fieldset>
			<legend>Purge Inactive Users</legend>
			<div class="x_content">
				<p>This tool allows you to purge (remove) inactive users from the database.</p>
				<p>All users who have not logged in since the date selected below will be deleted.
				You will have a chance to review and confirm once you click 'Submit'.</p>

				<div class='form-group'>
					<label class='control-label col-md-5 col-sm-5 col-xs-12'>Date Select: </label> 
					<div class='dateInputOuter col-md-6 col-sm-6 col-xs-12'>
						<i class="fa fa-calendar"></i>
						<input type="text" name="purgeBefore" id="purgeBefore" class="form-control dateInput" />
					</div>
				</div>
			</div>
		</fieldset>
		<div class="center"><input type="submit" class="button" value="Submit" /></div>
	</form>
{else}
	<form action="" method="post">
		<fieldset>
			<legend>Confirm Purge</legend>
			<div class="center">
				<p>The following users will be removed from your database. Click the "Confirm Purge" button to continue.<p>
				<p style="font-weight: bold; color: #FF0000; font-size: 14px;">THIS CANNOT BE UNDONE.</p>
				
				{foreach $to_purge as $u}
					<div class="{cycle values="row_color1,row_color2"}">
						#{$u.id}: {$u.username} {if $u.firstname or $u.lastname}({$u.firstname} {$u.lastname}){/if}
					</div>
				{/foreach}
				
				<input type="hidden" name="doPurgeOn" value="{$purgeBefore}" />
				
			</div>
		</fieldset>
		<div class="center"><input type="submit" class="button" value="Confirm Purge" name="auto_save" /></div>
	</form>
{/if}