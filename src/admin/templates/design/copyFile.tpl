{* 6.0.7-3-gce41f93 *}


<div class="closeBoxX"></div>
<div class="lightUpTitle">{if $actionType==copy}Copy{else}Cut{/if} & Paste Folders/Files</div>
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
	<form style="display:block; margin: 15px;" action="index.php?page=design_copy_files&amp;location={$location|escape}" method="post">
		<input type="hidden" name="fromFolder" value="{$fromFolder|escape}" />
		<input type="hidden" name="toFolder" value="{$toFolder|escape}" />
		<input type="hidden" name="actionType" value="{$actionType}" />
		<strong>{if $actionType==copy}Copy{else}Cut{/if}ing From: </strong> {$fromFolder}<br />
		<strong>Pasting To: </strong> {$toFolder}<br />
		<strong>Files being {if $actionType==copy}copied{else}moved{/if}:</strong><br />
		
		{foreach from=$files item=file}
			{$file}<br />
			<input type="hidden" name="files[]" value="{$file|escape}" />
		{/foreach}
		<input type="hidden" name="auto_save" value="1" />
		
		<p style="width: 350px;">
			<strong style="color: red;">Warning: </strong> Any existing file(s) will be over-written by the {if $actionType==copy}copied{else}moved{/if} file(s).
		</p>
		
		<div class="templateToolButtons">
			<input type="submit" value="Paste Files" class="mini_button" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
