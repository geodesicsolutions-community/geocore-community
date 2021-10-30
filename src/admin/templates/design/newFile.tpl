{* 6.0.7-3-gce41f93 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle" id="newConfirmTitle">Create New File</div>

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
	<form style="display:block; margin: 15px;" action="index.php?page=design_new_file&amp;location={$location|escape}" method="post">
		<input type="hidden" name="auto_save" value="1" />
		<strong>Create new file in:</strong> {$location|escape}
		<br />
		<label><strong>Create new:</strong>
		<select name="fileType" id="fileType">
			{if $locationInfo.type=='main_page'}
				<option value=".tpl">Template</option>
			{else}
				<option value=".css">CSS File</option>
				<option value=".js">Javascript File</option>
			{/if}
		</select></label>
		<br />
		<label><strong>Name:</strong> <input type="text" size="20" name="name" /></label><span id="fileExtension"></span>
		<br />
		<div class="templateToolButtons">
			<input type="submit" value="Create File" class="mini_button" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}

<script type="text/javascript">
//<![CDATA[
{literal}
var newFileExtension = function () {
	var selected = $('fileType');
	var ext = $('fileExtension');
	if (selected && ext) {
		var extText = selected.getValue();

		//add some spaces after it if it is short
		var len = extText.length;
		for (; len<=4; len++) {
			extText += '&nbsp;';
		}
		ext.update(extText);
	}
}
newFileExtension();
if ($('fileType')) {
	$('fileType').observe('change', newFileExtension);
}

{/literal}
//]]>
</script>