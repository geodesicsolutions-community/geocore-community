{* 16.09.0-101-g4ea07d5 *}
{$adminMessages}
<form action="" method="post" class="form-horizontal">
	<fieldset>
		<legend>Enabled Sharing Methods</legend>
		<div class="x_content">
			{foreach $methods as $method => $enabled}
				<div class="form-group">
					<label class="control-label col-xs-12 col-sm-5">{$method|replace:'_':' '|capitalize}</label>
					<div class="col-xs-12 col-sm-6"><input type="checkbox" value="1" name="enabled[{$method}]" {if $enabled}checked="checked"{/if} /></div>
				</div>
			{/foreach}
		</div>
	</fieldset>
	<fieldset>
		<legend>Facebook Settings</legend>
		<div>
			<div class="form-group">
				<label class="control-label col-xs-12 col-sm-5">Facebook App Id</label>
				<div class="col-xs-12 col-sm-6"><input type="text" name="fb_app_id" value="{$fb_app_id}" placeholder="Leave this blank if you don't have an App ID" size="50"/></div>
			</div>
		</div>
	</fieldset>
	<div style="text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
</form>