{* 6.0.7-3-gce41f93 *}

<div style="min-width: 300px;">
	<div class="closeBoxX"></div>
	<div class="lightUpTitle">Confirm Pre-Value Dropdown Deletion</div>
	<br /><br />
	<form action="index.php?page=delete_dropdown&amp;d={$dropdown_id}" method="post">
		Delete the dropdown <span class="text_blue">{$show_dropdown.type_name}</span> and all attached values?
		<input type='hidden' name='auto_save' value='1' />
		{if $choices}
			<div class="page_note_error" style="text-align: left;">
				Warning:  This dropdown is used in the categories listed below:
				<ul>
					{foreach from=$choices item=choice}
						<li>{$choice.category_name}</li>
					{/foreach}
				</ul>
			</div>
		{else}
			<br />
		{/if}
		<br />
		<div style="text-align: right;">
			<input type="submit" name="auto_save" value="Delete Dropdown" class="mini_button" />
			<input type="button" value="cancel" class="mini_button closeLightUpBox" value="Cancel" />
		</div>
	</form>
</div>
