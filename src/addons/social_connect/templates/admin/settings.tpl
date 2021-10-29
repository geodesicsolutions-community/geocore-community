{* 16.09.0-96-gf3bd8a1 *}
{$admin_msgs}

<form action="" method="post" class='form-horizontal form-label-left'>
	<fieldset>
		<legend>Facebook App/Site Settings</legend>
		<div class='x_content'>
			<p class="page_note">
				To use Facebook Connect, you need to register your website with Facebook using
				a verified Facebook account.  You can do so <a href="http://developers.facebook.com/setup" onclick="window.open(this.href); return false;">here</a>.  Once you have
				successfully registered your website, it will tell you the App ID and App Secret,
				which you will enter in the fields below.
			</p>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Facebook App ID: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="fb_app_id" class='form-control col-md-7 col-xs-12' value="{$fb_app_id|escape}" />
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Facebook App Secret: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="fb_app_secret" class='form-control col-md-7 col-xs-12' value="{$fb_app_secret|escape}" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Default Group: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
				<select name="default_group" class='form-control col-md-7 col-xs-12'>
					{foreach $groups as $group}
						<option value="{$group.group_id}"{if $default_group==$group.group_id} selected="selected"{/if}>{$group.name}</option>
					{/foreach}
				</select>
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="fb_logout" value="1"{if $fb_logout} checked="checked"{/if} />&nbsp;
			   Logout of Facebook <br><span class="small_font">(causes endless redirect on some sites when logging out)</span>
			  </div>
			</div>	

		</div>
	</fieldset>
	
	<div class="center">
		<input type="submit" name="auto_save" value="Save" class="mini_button" />
	</div>
</form>