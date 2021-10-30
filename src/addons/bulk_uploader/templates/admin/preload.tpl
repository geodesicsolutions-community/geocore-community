<div>{$adminMsgs}</div>
<fieldset>
	<legend>Input Source File</legend>
	<div class='x_content'>
		<form action='' method='POST' enctype='multipart/form-data' class='form-horizontal form-label-left'>
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Upload a CSV File: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type='file' id='file_name' name='csvfile' />
			  </div>
			</div>

			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Delimiter: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type='text' name='delimiter' class='form-control col-md-7 col-xs-12' size='5' value=','> e.g.,&nbsp;&nbsp; <b>, (comma)</b>  or <b>. (dot)</b>  or <b>| (pipe)</b> ...etc
			  </div>
			</div>
			
			<div class='form-group'>
			<label class='control-label col-md-5 col-sm-5 col-xs-12'>Encapsulation: </label>
			  <div class='col-md-6 col-sm-6 col-xs-12'>
			  <input type='text' name='encapsulation' class='form-control col-md-7 col-xs-12' size='5' value='"'> e.g.,&nbsp;&nbsp; <b>"</b>
			  </div>
			</div>

			<div class='center'>
				<input type='submit' name='auto_save' value='Start' />
			</div>
		</form>
	</div>
</fieldset>
<div class='clearColumn'></div>