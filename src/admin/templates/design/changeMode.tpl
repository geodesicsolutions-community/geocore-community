{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">Switch to {if $advMode}Standard{else}Advanced{/if} Mode</div>

<div style="width: 400px;">
	{if !$advMode}
		{* switching to adv mode *}
		<p class="page_note">
			Advanced Mode is ideal for <strong>template designers</strong> that want <strong>more control
			and options</strong>, but can be a little overwhelming at first. Be sure to
			consult the user manual to see what everything does when in advanced mode.
		</p>
	{/if}
	<p class="page_note_error">Warning:  Any un-saved changes on the page will be lost!</p>
	<form action="index.php?page=design_change_mode&json=1" method="post" id="switchModeForm">
		<div style="text-align: right;">
			<input type="hidden" name="auto_save" value="1" />
			<input type="submit" value="Switch to {if $advMode}Standard{else}Advanced{/if} Mode" class="mini_button" />
			<input type="button" value="Cancel" class="mini_cancel closeLightUpBox" />
		</div>
	</form>
</div>

<script type="text/javascript">
	$('switchModeForm').observe('submit',geoDesignManage.changeModeSubmit);
</script>