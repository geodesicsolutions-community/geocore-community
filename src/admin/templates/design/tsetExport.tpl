{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">Export Pre-5.0 Design to Template Set</div>

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
	<form style="display:block; margin: 15px;" action="index.php?page=design_sets_export" method="post">
		<input type="hidden" name="auto_save" value="1" />
		<p class="page_note_error">This will over-write any templates that have already been
		exported, do you want to continue?</p>
		<div class="templateToolButtons">
			<input type="submit" value="Export Pre-5.0 Design" class="mini_button" />
			<input type="button" class="closeLightUpBox mini_cancel" value="Cancel" />
		</div>
	</form>
{/if}
