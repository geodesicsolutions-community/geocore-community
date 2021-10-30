{* 6.0.7-3-gce41f93 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Upload File</div>

{if $errorMsgs}
	<div class="errorBoxMsgs">
		<br />
		<strong>Unable to perform action here:</strong><br />
		{$errorMsgs}
		<br /><br />
		<div class="templateToolButtons">
			<input type="button" class="closeLightUpBox mini_button" value="Ok" />
		</div>
		<div class="clearColumn"></div>
	</div>
{else}
	<form enctype="multipart/form-data" style="display:block; margin: 15px;" action="index.php?page=design_upload_file&amp;location={$location|escape}" method="post">
		<input type="hidden" name="auto_save" value="1" />
		<strong>Upload file in:</strong> {$location|escape}
		<br /><br />
		<input type="file" name="contents" id="designUploadFileInput" />
		<br />
		<label><strong>Upload As:</strong> <input type="text" id="designUploadNameInput" size="20" name="name" /></label>
		<br /><br />
		<strong style="color: red;">Warning:</strong> This will overwrite any existing file's contents!
		<br />
		<div class="templateToolButtons">
			<input type="submit" value="Upload" class="mini_button" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}


<script tyle="text/javascript">
//<![CDATA[
{literal}
if ($('designUploadFileInput')) {
	$('designUploadFileInput').observe('change', function () {
		//be sure to remove anything before / or \ (remove stuff like c:\fakepath\ like IE likes to do)
		$('designUploadNameInput').setValue(this.getValue().replace(/^.*[\/\\]+/g,''));
	});
}
{/literal}
//]]>
</script>
