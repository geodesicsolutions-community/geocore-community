{* 16.09.0-79-gb63e5d8 *}

<div class="closeBoxX"></div>

<div style="width: 400px;">
	<div class="col_hdr">
		<i class="fa fa-users"></i>
		Change User Group
	</div>
	<div class="page_note_error"><strong>Warning:</strong> Any un-saved changes on the current page will be lost!</div>
	
	<form action="index.php" method="get" class="center" style="padding: 5px;">
		<strong>Edit Fields to Use for Group: </strong><br />
		<input type="hidden" name="page" value="fields_to_use" />
		<input type="hidden" name="categoryId" value="{$categoryId}" />
		
		<select name="groupId">
			<option value="0">Site Defaults</option>
			{foreach from=$groups item=name key=id}
				<option value="{$id}"{if $groupId===$id} selected="selected"{/if}>{$name} ({$id})</option>
			{/foreach}
		</select>
		<br /><br /><br />
		<div class="right">
			<input type="submit" value="Change Group" class="mini_button" />
			<a href="" class="closeLightUpBox mini_button">Cancel</a>
		</div>
	</form>
	
</div>