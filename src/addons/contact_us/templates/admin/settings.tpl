{* 16.09.0-96-gf3bd8a1 *}

{$adminMsgs}

<form action="" method="post" class='form-horizontal form-label-left'>
	<fieldset>
		<legend>Contact Us Form Settings</legend>
		<div class='x_content'>
			<div class="page_note">
				Note that each "department name" can be changed on this addon's
				<a href="index.php?page=edit_addon_text&amp;addon=contact_us">edit text page</a>.
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'></label>
			  <div class='col-md-7 col-sm-7 col-xs-12'>
			    <input type="checkbox" name="show_ip" id="show_ip" value="1"{if $show_ip} checked="checked"{/if} />&nbsp;
			    Include Sender's IP Address
			  </div>
			</div>	
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>E-Mail Subject Prefix: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="subject_prefix" class='form-control col-md-7 col-xs-12' value="{$subject_prefix|escape}" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Department 1 Send to e-mail(s): <br />
					<span class="small_font">({$msgs.dept_1})</span> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <textarea name="dept_1_email" class="form-control" style="width: 300px; height: 100px;" />{$dept_1_email}</textarea>
					<span class="small_font">Multiple e-mails separated by Comma:<span class="color-primary-one" style="font-size: 1.4em; font-weight: bold;"> , </span></span>
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Department 2 Send to e-mail(s): <br />
					<span class="small_font">({$msgs.dept_2})</span> </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <textarea name="dept_2_email" class="form-control" style="width: 300px; height: 100px;" />{$dept_2_email}</textarea>
					<span class="small_font">Multiple e-mails separated by Comma:<span class="color-primary-one" style="font-size: 1.4em; font-weight: bold;"> , </span></span>
			  </div>
			</div>

			<div class="center">
				<input type="submit" name="auto_save" value="Save" class="button" />
			</div>
		</div>
	</fieldset>
</form>