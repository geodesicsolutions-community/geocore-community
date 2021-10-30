{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>

<div class="lightUpTitle">Restore Default Template</div>
{if $errors}
	<div class="errorBoxMsgs">
		<br />
		<strong>Unable to perform action here:</strong><br />
		{$errors}
		<br /><br />
		<div class="templateToolButtons">
			<input type="button" class="closeLightUpBox mini_button" value="Ok" />
		</div>
		<div class="clearColumn"></div>
	</div>
{else}
	
	<form action="index.php?page=page_attachments_restore_template&amp;file={$file|escape}" method="post" style="width: 300px; padding: 5px;">
		<input type="hidden" name="auto_save" value="1" />
		<input type="hidden" name="t_set" value="{$t_set|escape}" />
		<input type="hidden" name="file" value="{$file|escape}" />
		Restore Default Template: <strong class="text_green">{$file}</strong><br />
		To Template Set: <strong class="text_green">{$t_set}</strong>
		<br /><br />
		<div class="templateToolButtons">
			<input type="submit" value="Restore Default Template" class="mini_button" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
