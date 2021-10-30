{* 16.09.0-96-gf3bd8a1 *}
{$adminMessages}
<fieldset>
	<legend>Auto-Load Images</legend>
	<div class='form-horizontal form-label-left'>
		<div class='x_content'>
			<form action="" method="post">
				<div class='form-group'>
				<label class='control-label col-md-5 col-sm-5 col-xs-12'>Add Images in Directory: </label>
				  <div class='col-md-6 col-sm-6 col-xs-12'>
				  <input type="text" name="autoLoadDir" size="40" class='form-control col-md-7 col-xs-12' value="addons/attention_getters/images/banners/" />
				  </div>
				</div>

				<div class='form-group'>
				<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
				  <div class='col-md-7 col-sm-7 col-xs-12'>
				    <input type="checkbox" name="clearExisting" value="1" />&nbsp;
				    Remove ALL Existing (Total Reset)
				  </div>
				</div>	

				<div class="center">
					<input type="submit" name="auto_save" value="Auto-Load Images" />
				</div>
			</form>
		</div>
	</div>
</fieldset>