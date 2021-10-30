{* 16.09.0-79-gb63e5d8 *}
{$adminMsgs}
<form action="" method="post" enctype="multipart/form-data" class="form-horizontal form-label-left">
	<fieldset>
		<legend>File Upload</legend>
		<div class="x_content">
		        <div class='form-group'>
		          <label class='control-label col-md-5 col-sm-5 col-xs-12'>Import Source File: </label>
		            <div class='col-md-6 col-sm-6 col-xs-12'>
				<input type="file" name="source" />
		            </div>
         		</div>
         		
         		{* only user importing and csv files are implemented as of now. might add listings and xml eventually... *}
         		<input type="hidden" name="import_type" value="user" />
         		<input type="hidden" name="filetype" value="csv" />
		</div>
	</fieldset>

	<fieldset id="csv_settings" class="type_settings">	
		<legend>CSV Settings</legend>
		<div class="x_content">
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>First row contains column headers? </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			    <input type="radio" value="1" name="csv_skipfirst" /> Yes<br />
			    <input type="radio" value="0" name="csv_skipfirst" checked="checked" /> No
			  </div>
			</div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Field Delimiter Character: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'><input type="text" name="csv_delimiter" value="," size="1" /> default: comma (,)
			  </div>
			</div>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Field Encapsulation Character: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'><input type="text" name="csv_encapsulation" value='"' size="1" /> default: double-quote (")
			  </div>
			</div>
		</div>
	</fieldset>

	{* Not implemented::
	<fieldset id="xml_settings" class="type_settings" style="display: none;">
		<legend>XML Settings</legend>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Root element name (NOTE: XML not implemented yet!): </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			    <input type="text" name="root" />
			  </div>
			</div>
	</fieldset>
	:: /Not implemented *}
	
	<fieldset id="general_settings" class="type_settings">
		<legend>General Settings</legend>
		<div class="x_content">
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Base Image Path {$base_image_tooltip}: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			    <input type="text" name="base_image_path" class="form-control col-md-7 col-xs-12" />
			  </div>
			</div>
		</div>
	</fieldset>
	
	<div class="center"><input type="submit" value="Continue" name="auto_save" id="continue_btn" /></div>
</form>

{* Not implemented, because only one filetype is implemented :: 
<script>
	jQuery('#filetype_ddl').change(function() {
		var type = jQuery('#filetype_ddl').val();
		
		jQuery('.type_settings').hide(); //hide any choices showing from before
		jQuery('#continue_btn').hide(); //only reveal the continue button when a valid set of settings is also shown
		jQuery('#general_settings').hide();
		if(type == 'csv') {
			jQuery('#csv_settings').show();
			jQuery('#continue_btn').show();
			jQuery('#general_settings').show();
		} else if(type == 'xml') {
			jQuery('#xml_settings').show();
			jQuery('#continue_btn').show();
			jQuery('#general_settings').show();
		}
	});
	//fire the event manually to make sure everything's showing right as the page (re)loads
	jQuery('#filetype_ddl').change();
</script>
:: /Not implemented *}