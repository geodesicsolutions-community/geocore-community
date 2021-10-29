{* 16.09.0-96-gf3bd8a1 *}
{$adminMessages}
<form action="" method="post" class='form-horizontal form-label-left'>
	<fieldset>
		<legend>Force Subscriptions</legend>
		<div class='x_content'>
			<div class='form-group'>
			<label class='control-label col-md-4 col-sm-4 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" value="1" name="require_sub_all_users" {if $require_sub_all_users}checked="checked"{/if} />&nbsp;
			    Require all registered users to have an active subscription before accessing any pages
			  </div>
			</div>	
		</div>
	</fieldset>
	<div style="text-align: center;"><input type="submit" name="auto_save" value="Save" /></div>
</form>