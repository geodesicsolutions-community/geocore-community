{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>

<div class="lightUpTitle">Apply Default Page Attachments to Template Set</div>
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
	
	<form action="index.php?page=page_attachments_apply_defaults&amp;pageId={$pageId|escape}" method="post" style="width: 300px; padding: 5px;">
		<input type="hidden" name="auto_save" value="1" />
		<input type="hidden" name="t_set" value="{$t_set|escape}" />
		<input type="hidden" name="pageId" value="{$pageId|escape}" />
		Apply default template attachments for
		{if $addonTitle}<strong class="text_green">{$addonTitle}</strong>'s addon page <strong class="text_green">{$pageName}</strong>?
		{else}page <strong class="text_green">{$info.name}</strong>?{/if}
		<br /><br />Default Template: <strong class="text_green">{$info.defaults.1.0}</strong>
		<br /><br />
		<div class="templateToolButtons">
			<input type="submit" value="Apply Default Attachments" class="mini_button" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
