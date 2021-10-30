{* 16.09.0-79-gb63e5d8 *}

{$adminMsgs}

<form action="index.php?page=uploads_new_type" method="post" enctype="multipart/form-data" class='form-horizontal form-label-left'>
	<fieldset>
		<legend>New File Type Allowed in Listing</legend>
		<div class='x_content'>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>File Type Name: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="b[type_name]" class='form-control col-md-7 col-xs-12' />
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>File's Mime Type: {$toolTip.mime_type}</label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="b[mime_type]" class='form-control col-md-7 col-xs-12' />
				<br /><strong>OR</strong><br />
				upload a file to pull the mime-type from<br />
			  <input type="file" name="c" />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Icon to Use: {$toolTip.icon}<br />
					<span class="small_font">[Required if not an image]</span></label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <span class='vertical-form-fix'>{$geo_templatesDir}[Template Set]/external/<input type="text" name="b[icon_to_use]" /></span>
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Extension of File Type: {$toolTip.type}</label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type="text" name="b[extension]" class='form-control col-md-7 col-xs-12' />
			  </div>
			</div>

			<div class="center">
				<input type="submit" name="auto_save" value="Add Type" class="mini_button" />
			</div>
		</div>
	</fieldset>
</form>