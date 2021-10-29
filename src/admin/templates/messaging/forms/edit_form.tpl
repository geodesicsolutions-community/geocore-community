{* 16.09.0-79-gb63e5d8 *}

<div class='form-group'>
<label class='control-label col-md-5 col-sm-5 col-xs-12'>Content Type: </label>
  <div class='col-md-6 col-sm-6 col-xs-12'>
	<select name="content_type" class='form-control col-md-7 col-xs-12'>
		<option value="text/plain"{if $content_type=='text/plain'} selected="selected"{/if}>Plain Text</option>
		<option value="text/html"{if $content_type=='text/html'} selected="selected"{/if}>HTML</option>
	</select>
  </div>
</div>

<div class='form-group'>
<label class='control-label col-md-5 col-sm-5 col-xs-12'>Form Message Name: </label>
  <div class='col-md-6 col-sm-6 col-xs-12'>
  	<input type="text" name="message_name" class='form-control col-md-7 col-xs-12' value="{$message_name|escape}" />
  </div>
</div>

<div class='form-group'>
<label class='control-label col-md-5 col-sm-5 col-xs-12'>Form Subject: </label>
  <div class='col-md-6 col-sm-6 col-xs-12'>
  	<input type="text" name="subject" class='form-control col-md-7 col-xs-12' value="{$subject|escape}" />
  </div>
</div>

<div class='form-group'>
<label class='control-label col-md-5 col-sm-5 col-xs-12'>Form Message: </label>
  <div class='col-md-6 col-sm-6 col-xs-12'>
    <textarea name="message" cols="50" rows="20" class="form-control" style="width: 100%;">{$message|fromDB|escape}</textarea>
  </div>
</div> 

<div class="center">
	<input type="submit" name="auto_save" value="Save" class="mini_button" />
</div>
