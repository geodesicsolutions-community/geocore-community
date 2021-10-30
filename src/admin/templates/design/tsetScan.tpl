{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">Re-Scan Template Attachments</div>

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
	<form style="display:block; margin: 15px;" action="index.php?page=design_sets_scan" method="post">
		<input type="hidden" name="auto_save" value="1" />
		<input type="hidden" name="t_set" value="{$t_set|escape}" />
		<strong>Re-Scan Template Set:</strong> {$t_set|escape}
		<br />
		<p>
			{if $t_set=='default'&&!$canEditDefault}
				For the default template set, this will re-apply all the default<br />
				addon page attachments.  If you delete and re-upload<br />
				the default template set, use this to ensure all the addon pages<br />
				have default templates attached.
			{else}				
				This will clear all current modules-to-template attachments, and<br />
				re-generate them by scanning each main page template.
			{/if}
		</p>
		<div class="templateToolButtons">
			<input type="submit" class="mini_button" value="Re-Scan Templates for Attachments" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
