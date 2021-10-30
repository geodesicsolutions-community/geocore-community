{* 6.0.7-3-gce41f93 *}

<div class="closeBoxX"></div>
<div class="lightUpTitle">
	{if $advMode}
		Change Which Template Sets to Edit
	{else}
		Change What Template Set to Edit
	{/if}
</div>
<p class="page_note" style="width: 300px;">
	This will change the template set{if $advMode}s{/if} you are currently editing in the admin panel.
</p>
<form action="index.php?page=design_change_editing&json=1" method="post" id="switchEditForm">
	{foreach from=$t_sets item=t_set}
		{assign var=tsetDisplayed value=1}
		<div class="{cycle values='row_color1,row_color2'}">
			<label>
				<input type="{if $advMode}checkbox{else}radio{/if}" name="workWith[]" value="{$t_set}"
					{if in_array($t_set,$workWithList)}checked="checked"{/if} />
				{$t_set}
			</label>
		</div>
	{/foreach}
	
	{if !$tsetDisplayed}
		<p class="page_note_error">
			You need to create your main template set before you can start editing the design of your site.
		</p>
	{/if}
	<br />
	<div style="text-align: right;">
		<input type="submit" name="auto_save" class="mini_button" value="Change edited Template Set{if $advMode}s{/if}" />
		<input type="button" value="Cancel" class="mini_cancel closeLightUpBox" />
	</div>
</form>


<script type="text/javascript">
	$('switchEditForm').observe('submit',geoDesignManage.changeModeSubmit);
</script>