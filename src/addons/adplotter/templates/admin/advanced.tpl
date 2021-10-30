{* 16.09.0-92-gefaf632 *}
{$adminMsgs}
<form action="" method="post" class='form-horizontal'>
	<fieldset>
		<legend>AdPlotter Advanced Settings</legend>
		<div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Default User Group for users created by AdPlotter</label>
				<div class="col-xs-12 col-sm-6">
					<select name="default_group" class='form-control'>
						<option value="0">None -- Use Software Default</option>
						{foreach $groups as $id => $name}
							<option value="{$id}"{if $default_group == $id} selected="selected"{/if}>{$name}</option>
						{/foreach}
					</select>
				</div>
			</div>	
		</div>
	</fieldset>
	<div style="text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
</form>